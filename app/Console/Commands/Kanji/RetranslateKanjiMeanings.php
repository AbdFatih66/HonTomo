<?php

namespace App\Console\Commands\Kanji;

use App\Models\Kanji;
use App\Models\KanjiVocabulary;
use App\Services\Kanji\KanjiMeaningTranslator;
use Illuminate\Console\Command;

/**
 * Re-runs KanjiMeaningTranslator against rows already in the database.
 *
 * Why this command exists: ImportKanjidic/ImportJmdict both use
 * firstOrCreate() keyed on (character/word, reading) so re-running the
 * importer is safe and idempotent — but that also means once a row
 * exists, re-running the importer NEVER touches its meaning_id /
 * needs_review_id again, even after the glossary file gains new
 * entries. This command is the deliberate "yes, actually re-translate
 * what's already there" counterpart: it only touches rows still
 * flagged needs_review_id = true, re-translates their meaning_en with
 * the current glossary, and updates meaning_id / needs_review_id in
 * place. Rows that already resolved cleanly (needs_review_id = false)
 * are left untouched, including any that were hand-corrected by a human
 * reviewer — this command never overwrites those.
 * needs_review_id = true are re-translated by default; pass --all once
 * after a translator logic change (like the 2026-09-24 removal of the
 * word-by-word fallback) to also re-check rows already marked false,
 * since the old logic could mark a wrong result as needs_review = false
 * when every individual word happened to match the glossary.
 */
class RetranslateKanjiMeanings extends Command
{
    protected $signature = 'kanji:retranslate-meanings
        {--dry-run : show how many rows would change without saving}
        {--all : also re-check rows where needs_review_id is already false (use once after 2026-09-24 word-by-word fix, to catch rows silently marked false by the old buggy logic)}';

    protected $description = 'Re-translate meaning_id for existing kanji/vocabulary rows still flagged needs_review_id=true, using the current glossary.';

    public function handle(KanjiMeaningTranslator $translator): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $all = (bool) $this->option('all');

        $kanjiUpdated = $this->retranslateKanji($translator, $dryRun, $all);
        $vocabUpdated = $this->retranslateVocabulary($translator, $dryRun, $all);

        $this->info(sprintf(
            '%s%d kanji + %d vocabulary rows re-translated%s.',
            $dryRun ? '[dry-run] would update ' : '',
            $kanjiUpdated,
            $vocabUpdated,
            $dryRun ? '' : ''
        ));

        return self::SUCCESS;
    }

    private function retranslateKanji(KanjiMeaningTranslator $translator, bool $dryRun, bool $all): int
    {
        $this->info('Re-translating kanjis' . ($all ? ' (all rows)' : ' (needs_review_id = true)') . '...');
        $updated = 0;

        Kanji::query()
            ->when(! $all, fn($q) => $q->where('needs_review_id', true))
            ->whereNotNull('meaning_en')
            ->select('id', 'meaning_en', 'meaning_id', 'needs_review_id', 'locked_fields')
            ->chunkById(500, function ($rows) use ($translator, $dryRun, &$updated) {
                foreach ($rows as $row) {
                    // ImportKanjidic never overwrites meaning_id/needs_review_id
                    // once a human has locked that field — mirror that here so
                    // this command can't clobber a hand-corrected review.
                    if ($row->locked_fields['meaning_id'] ?? false) {
                        continue;
                    }

                    // Kanji::meaning_en is stored as "gold, money, metal" —
                    // ImportKanjidic built it with implode(', ', $meaningsEn)
                    // and translated it with translateList($meaningsEn), so
                    // re-translating has to split back into that array and
                    // use translateList() too. Calling translate() on the
                    // joined string instead would strip the commas during
                    // normalize() and translate it word-by-word as one
                    // undifferentiated blob — a worse result than the
                    // original import, not a fix.
                    $meaningsEn = array_map('trim', explode(',', $row->meaning_en));
                    $result = $translator->translateList($meaningsEn);

                    if ($result['text'] === $row->meaning_id && $result['needs_review'] === $row->needs_review_id) {
                        continue; // nothing actually changed, skip the write
                    }

                    $updated++;

                    if (! $dryRun) {
                        $row->forceFill([
                            'meaning_id' => $result['text'],
                            'needs_review_id' => $result['needs_review'],
                        ])->save();
                    }
                }
            });

        return $updated;
    }

    private function retranslateVocabulary(KanjiMeaningTranslator $translator, bool $dryRun, bool $all): int
    {
        $this->info('Re-translating kanji_vocabulary' . ($all ? ' (all rows)' : ' (needs_review_id = true)') . '...');
        $updated = 0;

        KanjiVocabulary::query()
            ->when(! $all, fn($q) => $q->where('needs_review_id', true))
            ->whereNotNull('meaning_en')
            ->select('id', 'meaning_en', 'meaning_id', 'needs_review_id')
            ->chunkById(500, function ($rows) use ($translator, $dryRun, &$updated) {
                foreach ($rows as $row) {
                    // meaning_en here is one JMdict gloss string per row
                    // (ImportJmdict stores $sense->gloss[0] directly, not
                    // a list), so translate() — not translateList() — is
                    // the right call, mirroring the importer.
                    $result = $translator->translate($row->meaning_en);

                    if ($result['text'] === $row->meaning_id && $result['needs_review'] === $row->needs_review_id) {
                        continue;
                    }

                    $updated++;

                    if (! $dryRun) {
                        $row->forceFill([
                            'meaning_id' => $result['text'],
                            'needs_review_id' => $result['needs_review'],
                        ])->save();
                    }
                }
            });

        return $updated;
    }
}
