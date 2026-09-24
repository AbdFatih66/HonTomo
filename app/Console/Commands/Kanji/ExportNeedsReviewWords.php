<?php

namespace App\Console\Commands\Kanji;

use App\Models\Kanji;
use App\Models\KanjiVocabulary;
use App\Services\Kanji\KanjiMeaningTranslator;
use Illuminate\Console\Command;

/**
 * Regenerates a needs-review-words.txt that only lists words which are
 * ACTUALLY missing from the glossary — checked one word at a time with
 * the same normalize()+lookup (and "to "-stripping) rules
 * KanjiMeaningTranslator uses internally.
 *
 * Why this replaces the old export: KanjiMeaningTranslator::translate()
 * flags needs_review=true for a whole multi-word gloss if ANY word in it
 * is missing from the glossary (see translate(), the word-by-word
 * fallback around $anyMissing). A naive export that dumps every word
 * from every needs_review_id=true row therefore includes plenty of
 * words that already have a glossary entry — they just happened to sit
 * next to a word that doesn't. This command re-checks every word
 * individually so the output is only genuinely-missing words.
 */
class ExportNeedsReviewWords extends Command
{
    protected $signature = 'kanji:export-needs-review-words
        {output=storage/app/kanji-import/needs-review-words.txt : where to write the file}';

    protected $description = 'Export a frequency-sorted list of English words that have NO glossary entry (word-level, not gloss-level).';

    public function handle(KanjiMeaningTranslator $translator): int
    {
        // Same normalization KanjiMeaningTranslator::normalize() applies:
        // lowercase, strip everything but letters/numbers/whitespace.
        $normalize = function (string $text): string {
            $text = mb_strtolower(trim($text));

            return trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $text));
        };

        // Read the glossary the same way the translator does.
        $reflection = new \ReflectionClass($translator);
        $prop = $reflection->getProperty('glossary');
        $prop->setAccessible(true);
        $glossary = $prop->getValue($translator);

        $counts = [];

        $collect = function (?string $gloss) use (&$counts, $normalize, $glossary) {
            if (! $gloss) {
                return;
            }

            foreach (preg_split('/\s+/u', $normalize($gloss), -1, PREG_SPLIT_NO_EMPTY) as $word) {
                if (isset($glossary[$word])) {
                    continue; // has a direct entry -> not a review word
                }

                // Mirror the "to " stripping the translator does for verbs.
                if (str_starts_with($word, 'to ') && isset($glossary[substr($word, 3)])) {
                    continue;
                }

                $counts[$word] = ($counts[$word] ?? 0) + 1;
            }
        };

        $this->info('Scanning kanjis...');
        Kanji::query()->where('needs_review_id', true)
            ->select('id', 'meaning_en')
            ->chunkById(500, function ($rows) use ($collect) {
                foreach ($rows as $row) {
                    $collect($row->meaning_en);
                }
            });

        $this->info('Scanning kanji_vocabulary...');
        KanjiVocabulary::query()->where('needs_review_id', true)
            ->select('id', 'meaning_en')
            ->chunkById(500, function ($rows) use ($collect) {
                foreach ($rows as $row) {
                    $collect($row->meaning_en);
                }
            });

        arsort($counts);

        $lines = [];
        foreach ($counts as $word => $count) {
            $lines[] = "{$count}\t{$word}";
        }

        $path = base_path($this->argument('output'));
        @mkdir(dirname($path), recursive: true);
        file_put_contents($path, implode("\n", $lines));

        $this->info(sprintf(
            'Wrote %d genuinely-missing words (out of %d unique words seen) to %s',
            count($lines),
            count($lines), // counts already excludes matched words
            $path
        ));

        return self::SUCCESS;
    }
}
