<?php

namespace App\Services\Kanji\Translation;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * DeepL-backed fallback. Chosen over Google Cloud Translate as the
 * default implementation because DeepL has a free tier (500,000
 * chars/month as of this writing) that needs no billing account to start
 * with, which matters here since this fallback is meant for a long tail
 * of thousands of rarely-reused glosses (see the "1185 N1 kanji, freq=1
 * tail" discussion in docs/kanji-module.md) — a per-request cost that
 * requires a credit card on file is a real adoption barrier for that use
 * case. Swap in a different ApiTranslationClient implementation if a
 * different provider is preferred; nothing else in the pipeline depends
 * on DeepL specifically (see ApiTranslationClient's docblock).
 */
class DeeplTranslationClient implements ApiTranslationClient
{
    /** DeepL's documented per-request limit on the `text` parameter. */
    private const MAX_BATCH_SIZE = 50;

    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $apiUrl,
    ) {}

    public function translate(string $englishText): ?string
    {
        if (! $this->apiKey) {
            return null;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->connectTimeout(3)
                ->withHeaders(['Authorization' => 'DeepL-Auth-Key '.$this->apiKey])
                ->post($this->apiUrl, [
                    'text' => $englishText,
                    'source_lang' => 'EN',
                    'target_lang' => 'ID',
                ]);

            if (! $response->successful()) {
                Log::warning('DeepL translation request failed', [
                    'status' => $response->status(),
                    'text' => $englishText,
                ]);

                return null;
            }

            $translated = $response->json('translations.0.text');

            return is_string($translated) && $translated !== '' ? $translated : null;
        } catch (Throwable $e) {
            // Network hiccups, timeouts, etc. are expected occasionally for
            // a bulk backfill run over thousands of rows — never let one
            // failed lookup abort the whole retranslate/import command.
            Log::warning('DeepL translation request threw an exception', [
                'message' => $e->getMessage(),
                'text' => $englishText,
            ]);

            return null;
        }
    }

    /**
     * DeepL's /v2/translate accepts the source text as a repeated `text`
     * form field — text=a&text=b&text=c — and returns a `translations`
     * array in the same order, so a single request here replaces up to
     * self::MAX_BATCH_SIZE individual translate() calls. The free/pro
     * tiers both cap this at 50 texts per request; callers (see
     * ApiFallbackMeaningTranslator::primeCache()) are responsible for
     * chunking anything larger than that before calling us — we still
     * defend against an oversized batch here rather than silently
     * truncating it, since that would mis-align the caller's index
     * bookkeeping.
     */
    public function translateBatch(array $englishTexts): array
    {
        $count = count($englishTexts);

        if ($count === 0) {
            return [];
        }

        if (! $this->apiKey) {
            return array_fill(0, $count, null);
        }

        if ($count > self::MAX_BATCH_SIZE) {
            throw new \InvalidArgumentException(sprintf(
                'translateBatch() got %d texts, DeepL allows at most %d per request — chunk before calling.',
                $count,
                self::MAX_BATCH_SIZE
            ));
        }

        try {
            // Http::asForm() would serialize a `text` array as
            // text[0]=..&text[1]=.., which DeepL's API does not
            // understand — it needs the field repeated bare (text=a&text=b).
            // Building the body by hand keeps every other call site
            // (translate() above) untouched.
            $body = collect($englishTexts)
                ->map(fn (string $text) => 'text='.rawurlencode($text))
                ->implode('&');
            $body .= '&source_lang=EN&target_lang=ID';

            $response = Http::timeout(15)
                ->connectTimeout(3)
                ->withHeaders([
                    'Authorization' => 'DeepL-Auth-Key '.$this->apiKey,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ])
                ->withBody($body, 'application/x-www-form-urlencoded')
                ->post($this->apiUrl);

            if (! $response->successful()) {
                Log::warning('DeepL batch translation request failed', [
                    'status' => $response->status(),
                    'count' => $count,
                ]);

                return array_fill(0, $count, null);
            }

            $translations = $response->json('translations') ?? [];

            // Index-align defensively: if DeepL ever returned fewer/more
            // entries than requested, pad/truncate rather than let a
            // shifted array silently mis-map results onto the wrong rows.
            return array_map(
                fn (int $i) => is_string($translations[$i]['text'] ?? null) && $translations[$i]['text'] !== ''
                    ? $translations[$i]['text']
                    : null,
                range(0, $count - 1)
            );
        } catch (Throwable $e) {
            Log::warning('DeepL batch translation request threw an exception', [
                'message' => $e->getMessage(),
                'count' => $count,
            ]);

            return array_fill(0, $count, null);
        }
    }
}
