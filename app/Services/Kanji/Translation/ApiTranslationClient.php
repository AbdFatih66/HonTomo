<?php

namespace App\Services\Kanji\Translation;

/**
 * A live EN->ID translation backend, used ONLY as a fallback behind the
 * curated static glossary (resources/lang-data/kanji-meaning-glossary.json)
 * — never in front of it. See docs/kanji-module.md section 3 for why the
 * glossary stays the primary source of truth: determinism, no network
 * dependency during normal import, and stability of already-verified
 * N5-N3 translations. This interface exists so the fallback can be
 * swapped between providers (DeepL, Google Cloud Translate, etc.) or
 * disabled entirely (NullTranslationClient) without touching the
 * translator or command code that calls it.
 */
interface ApiTranslationClient
{
    /**
     * Translate a single English phrase to Indonesian.
     *
     * @return string|null the translation, or null if the call failed or
     *                      this client is disabled (e.g. no API key
     *                      configured) — callers must treat null as "could
     *                      not translate", not as an empty-string result.
     */
    public function translate(string $englishText): ?string;

    /**
     * Translate several English phrases in as few round trips as possible
     * (DeepL accepts up to 50 `text` values per request). Returned array
     * is index-aligned with $englishTexts — same length, same order — with
     * null in any slot that failed or wasn't returned, so callers must
     * treat each entry the same way as a translate() null: "could not
     * translate", not an empty string. Passing an empty array returns an
     * empty array without making a request.
     *
     * @param  string[]  $englishTexts
     * @return array<int, string|null>
     */
    public function translateBatch(array $englishTexts): array;
}
