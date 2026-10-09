<?php

namespace App\Services\Kanji\Translation;

use App\Services\Kanji\KanjiMeaningTranslator;
use App\Services\Kanji\MeaningTranslator;

/**
 * Decorates KanjiMeaningTranslator with a live-API fallback, kept as a
 * SEPARATE class rather than built into KanjiMeaningTranslator itself so
 * the base translator stays what it has always been: pure, deterministic,
 * offline, glossary-only. Nothing that doesn't explicitly opt into this
 * decorator (see the --api-fallback flag on kanji:retranslate-meanings)
 * changes behavior at all.
 *
 * Lookup order per gloss, matching docs/kanji-module.md section 3's "add
 * as fallback behind the glossary, never in front of it" instruction:
 *   1. The curated glossary (via KanjiMeaningTranslator::translate() /
 *      translateList() — exact match, "to "-stripping, parenthetical
 *      splitting; see that class for details).
 *   2. The API cache file (resources/lang-data/kanji-meaning-api-cache.json)
 *      — previously-fetched API translations, so a repeat run (or a
 *      second gloss with the same English text) never re-hits the API.
 *   3. The live API client (only reached for a genuine cache miss).
 *
 * needs_review_id is left true when the API supplies the translation, ON
 * PURPOSE — an MT-provided phrase gets exactly the same "needs human
 * review" flag as a bare-English fallback did before, because a live
 * translation was never spot-checked by anyone (unlike the hand-curated
 * glossary entries). What changes for the user story here is the
 * meaning_id column no longer sits in English while waiting for review —
 * it holds a usable (if unverified) Indonesian phrase instead. A curator
 * who accepts an API-cached phrase as correct is the one who should flip
 * needs_review_id to false, e.g. by promoting it into the curated
 * glossary (see promoteToGlossary() below) or by locking the field
 * (Kanji::locked_fields) once reviewed.
 */
class ApiFallbackMeaningTranslator implements MeaningTranslator
{
    /** @var array<string, string> */
    private array $cache;

    private readonly string $cachePath;

    /**
     * Consecutive API failures in this run. After
     * self::CIRCUIT_BREAKER_THRESHOLD in a row, we stop calling the API
     * for the rest of this run entirely — without this, a bulk run over
     * thousands of rows against a blocked/unreachable API would retry
     * the same failure thousands of times, each eating the client's
     * timeout, turning a "the network can't reach DeepL" problem into an
     * hours-long hang instead of a fast, visible failure.
     */
    private int $consecutiveFailures = 0;

    private bool $circuitOpen = false;

    private const CIRCUIT_BREAKER_THRESHOLD = 3;

    public function __construct(
        private readonly KanjiMeaningTranslator $base,
        private readonly ApiTranslationClient $apiClient,
        ?string $cachePath = null,
    ) {
        $this->cachePath = $cachePath ?? resource_path('lang-data/kanji-meaning-api-cache.json');
        $raw = is_file($this->cachePath) ? json_decode(file_get_contents($this->cachePath), true) : [];
        $this->cache = is_array($raw) ? $raw : [];
    }

    /**
     * @return array{text: string, needs_review: bool, source: 'glossary'|'api_cache'|'api'|'none'}
     */
    public function translate(string $englishGloss): array
    {
        $fromGlossary = $this->base->translate($englishGloss);

        if (! $fromGlossary['needs_review']) {
            return [...$fromGlossary, 'source' => 'glossary'];
        }

        $key = $this->normalizeForCache($englishGloss);

        if (isset($this->cache[$key])) {
            return ['text' => $this->cache[$key], 'needs_review' => true, 'source' => 'api_cache'];
        }

        if ($this->circuitOpen) {
            return [...$fromGlossary, 'source' => 'none'];
        }

        $apiResult = $this->apiClient->translate($englishGloss);

        if ($apiResult === null) {
            $this->consecutiveFailures++;

            if ($this->consecutiveFailures >= self::CIRCUIT_BREAKER_THRESHOLD) {
                $this->circuitOpen = true;
            }

            // API unavailable/failed — fall back to the glossary's own
            // result (bare English), same as if no fallback existed.
            return [...$fromGlossary, 'source' => 'none'];
        }

        $this->consecutiveFailures = 0;
        $this->cache[$key] = $apiResult;
        $this->persistCache();

        return ['text' => $apiResult, 'needs_review' => true, 'source' => 'api'];
    }

    /**
     * Warms the cache for a batch of glosses in as few API round trips as
     * possible, so a subsequent translate()/translateList() call for each
     * of these glosses hits the cache (step 2) instead of falling through
     * to a one-at-a-time API call (step 3). Callers such as
     * kanji:retranslate-meanings call this once per chunk of DB rows
     * before doing their normal per-row translate() loop — nothing about
     * that per-row loop has to change, it just gets fast because the
     * cache is already warm.
     *
     * Runs the same glossary check (step 1) and cache check (step 2) as
     * translate() so it only ever sends genuine misses to the API, dedupes
     * repeated glosses within the batch so each unique phrase is only sent
     * once, and respects the circuit breaker exactly like translate() does.
     *
     * @param  string[]  $englishGlosses
     */
    public function primeCache(array $englishGlosses, int $batchSize = 50): void
    {
        if ($this->circuitOpen || $englishGlosses === []) {
            return;
        }

        /** @var array<string, string> $pending normalized cache key => original gloss text to send */
        $pending = [];

        foreach ($englishGlosses as $gloss) {
            $fromGlossary = $this->base->translate($gloss);

            if (! $fromGlossary['needs_review']) {
                continue; // glossary already resolved it, nothing to fetch
            }

            $key = $this->normalizeForCache($gloss);

            if (isset($this->cache[$key]) || isset($pending[$key])) {
                continue; // already cached, or already queued by an earlier (near-)duplicate gloss in this batch
            }

            $pending[$key] = $gloss;
        }

        if ($pending === []) {
            return;
        }

        $dirty = false;

        foreach (array_chunk($pending, $batchSize, preserve_keys: true) as $chunk) {
            if ($this->circuitOpen) {
                break;
            }

            $keys = array_keys($chunk);
            $results = $this->apiClient->translateBatch(array_values($chunk));

            $anySucceeded = false;

            foreach ($keys as $i => $key) {
                $text = $results[$i] ?? null;

                if ($text !== null) {
                    $this->cache[$key] = $text;
                    $anySucceeded = true;
                    $dirty = true;
                }
            }

            // Mirror translate()'s circuit breaker: count a whole batch
            // request as one failure if NOTHING in it came back, since
            // that almost always means the request itself failed (bad
            // key, network down) rather than 50 simultaneous per-text
            // misses.
            if ($anySucceeded) {
                $this->consecutiveFailures = 0;
            } else {
                $this->consecutiveFailures++;

                if ($this->consecutiveFailures >= self::CIRCUIT_BREAKER_THRESHOLD) {
                    $this->circuitOpen = true;
                }
            }
        }

        if ($dirty) {
            $this->persistCache();
        }
    }

    /**
     * Whether the circuit breaker has tripped (self::CIRCUIT_BREAKER_THRESHOLD
     * consecutive API failures) — callers such as the retranslate command
     * can check this after a batch to warn the user that the rest of the
     * run fell back to glossary-only, rather than silently under-delivering.
     */
    public function circuitIsOpen(): bool
    {
        return $this->circuitOpen;
    }

    /**
     * @param  string[]  $englishGlosses
     * @return array{text: string, needs_review: bool}
     */
    public function translateList(array $englishGlosses): array
    {
        $translated = [];
        $needsReview = false;

        foreach ($englishGlosses as $gloss) {
            $result = $this->translate($gloss);
            $translated[] = $result['text'];
            $needsReview = $needsReview || $result['needs_review'];
        }

        return ['text' => implode(', ', $translated), 'needs_review' => $needsReview];
    }

    private function normalizeForCache(string $text): string
    {
        $text = mb_strtolower(trim($text));

        return trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $text));
    }

    private function persistCache(): void
    {
        ksort($this->cache);
        file_put_contents(
            $this->cachePath,
            json_encode($this->cache, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
}
