<?php

namespace App\Services\Kanji;

/**
 * Auto-fills the Indonesian side of a kanji/vocabulary meaning from a
 * curated, offline EN->ID glossary (resources/lang-data/kanji-meaning-glossary.json).
 *
 * Why a static glossary instead of a live translation API: it's
 * deterministic (same input always gives the same output, so re-imports
 * are stable and diffable), needs no API key or network access from the
 * import command, and for N5-level vocabulary — short, common, mostly
 * single/compound-word glosses like "water", "big", "to eat" — a curated
 * word list genuinely covers the large majority of cases accurately. It
 * will cover a shrinking share of glosses as N4->N1 bring in longer,
 * more abstract, multi-clause English definitions; that's fine, because
 * every miss is marked `needs_review_id = true` rather than silently
 * guessing, so nothing wrong reaches the user unflagged. Extend the
 * glossary file as coverage needs grow instead of swapping in a live API.
 */
class KanjiMeaningTranslator implements MeaningTranslator
{
    private array $glossary;

    public function __construct(?string $glossaryPath = null)
    {
        $path = $glossaryPath ?? resource_path('lang-data/kanji-meaning-glossary.json');
        $raw = is_file($path) ? json_decode(file_get_contents($path), true) : [];
        unset($raw['_comment']);
        $this->glossary = is_array($raw) ? $raw : [];
    }

    /**
     * @param  string  $englishGloss  a single English meaning, e.g. "big", "to eat", "mountain"
     * @return array{text: string, needs_review: bool}
     */
    public function translate(string $englishGloss): array
    {
        $normalized = $this->normalize($englishGloss);

        if (isset($this->glossary[$normalized])) {
            return ['text' => $this->glossary[$normalized], 'needs_review' => false];
        }

        // "to eat" / "to be strong" -> strip the leading "to " and retry,
        // since KANJIDIC2/JMdict glosses commonly prefix verbs this way.
        if (str_starts_with($normalized, 'to ')) {
            $withoutTo = substr($normalized, 3);
            if (isset($this->glossary[$withoutTo])) {
                return ['text' => $this->glossary[$withoutTo], 'needs_review' => false];
            }
        }

        // "older brother (polite)" -> translate the core phrase and the
        // parenthetical remark separately. Checked against the RAW gloss
        // (normalize() strips the parens), before the word-fallback below,
        // so a qualifier the glossary doesn't have yet doesn't block the
        // core word — which JMdict/KANJIDIC2 glosses use constantly
        // ("(polite)", "(honorific)", "(archaic)", counters, etc).
        if (preg_match('/^(.*\S)\s*\(([^()]+)\)\s*$/u', trim($englishGloss), $m)) {
            $core = $this->translate($m[1]);
            $remark = $this->translate($m[2]);

            // Remark left in English (rather than blocking on it) if the
            // glossary doesn't have it — still strictly more Indonesian
            // than the pre-fix fallback of the whole gloss in English.
            $remarkText = $remark['needs_review'] ? $m[2] : $remark['text'];

            return [
                'text' => "{$core['text']} ({$remarkText})",
                'needs_review' => $core['needs_review'],
            ];
        }

        // Word-by-word combination for a multi-word gloss WITHOUT an exact
        // glossary entry is deliberately NOT attempted (removed
        // 2026-09-24). It used to translate whatever content words WERE
        // in the glossary and join them in English word order — e.g.
        // "all day long" -> "semua hari panjang" instead of the correct
        // "sepanjang hari". The bigger problem wasn't just the bad
        // grammar: when every individual word happened to exist in the
        // glossary (as in that example), $anyMissing was false, so the
        // result was marked needs_review = false — silently wrong and
        // never flagged for a human to catch. Falling through to the
        // English-gloss-plus-needs_review path below is strictly safer:
        // it's honest about not having a confident translation, and it
        // surfaces the gloss via `kanji:export-needs-review-words` so it
        // can be added as a proper whole-phrase glossary entry (see the
        // "all day long" / "once in a lifetime encounter" pattern in
        // docs/kanji-module.md, section 3). Do not reintroduce word-by-word
        // joining without also fixing the needs_review logic to flag it
        // even when every word matched individually.

        // No confident match at any level: fall back to the English gloss
        // itself so the field is never blank, but flag it for human review.
        return ['text' => $englishGloss, 'needs_review' => true];
    }

    /**
     * Translates a full multi-word meaning field (e.g. KANJIDIC2's
     * "gold, money, metal") by translating each comma-separated gloss
     * independently and flagging the whole field for review if ANY part
     * was not found in the glossary.
     *
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

    private function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));

        return trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $text));
    }
}
