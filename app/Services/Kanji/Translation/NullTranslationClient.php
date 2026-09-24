<?php

namespace App\Services\Kanji\Translation;

/**
 * Default/no-op client: always returns null (never translates). This is
 * what the app binds when no API fallback is configured (no key set, or
 * the feature is deliberately left off), so the rest of the pipeline never
 * has to special-case "is the fallback enabled?" — it just gets no result
 * from an ApiTranslationClient and falls through to the English gloss +
 * needs_review = true, exactly like today.
 */
class NullTranslationClient implements ApiTranslationClient
{
    public function translate(string $englishText): ?string
    {
        return null;
    }

    public function translateBatch(array $englishTexts): array
    {
        return array_fill(0, count($englishTexts), null);
    }
}
