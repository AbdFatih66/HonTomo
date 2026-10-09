<?php

namespace App\Services\Kanji;

/**
 * Common shape for anything that can turn an English gloss (or list of
 * glosses) into an Indonesian meaning_id + needs_review flag. Implemented
 * by both KanjiMeaningTranslator (glossary-only) and
 * App\Services\Kanji\Translation\ApiFallbackMeaningTranslator
 * (glossary + live-API fallback), so callers like
 * kanji:retranslate-meanings can accept either without caring which.
 */
interface MeaningTranslator
{
    /**
     * @return array{text: string, needs_review: bool}
     */
    public function translate(string $englishGloss): array;

    /**
     * @param  string[]  $englishGlosses
     * @return array{text: string, needs_review: bool}
     */
    public function translateList(array $englishGlosses): array;
}
