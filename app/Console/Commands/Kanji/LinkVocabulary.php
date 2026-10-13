<?php

namespace App\Console\Commands\Kanji;

use App\Models\Kanji;
use App\Models\Vocabulary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Scans every word in the existing lesson curriculum (`vocabularies`,
 * seeded by VocabularySeeder/ExtendedCurriculumSeeder from Minna no
 * Nihongo — nothing to do with the Kanji module's own import commands)
 * and links it to every Kanji row whose character appears in that word.
 *
 * This is how Tahap 5 ("Lesson dan vocabulary") connects the two
 * systems: it does NOT create new vocabulary or new kanji, it only
 * records which already-known kanji show up in which already-known
 * lesson words, via `kanji_word_links`. Re-run any time either table
 * grows (new lesson vocabulary seeded, or more kanji imported) — the
 * `updateOrInsert` below makes it a no-op for pairs that already exist.
 *
 * Only characters already present in the `kanjis` table are linked.
 * A curriculum word using a kanji not imported yet is silently left
 * unlinked rather than guessed at — run kanji:import-kanjidic for the
 * relevant level first, then re-run this command.
 */
class LinkVocabulary extends Command
{
    protected $signature = 'kanji:link-vocabulary';

    protected $description = 'Link curriculum vocabulary words (vocabularies.japanese) to the kanji characters they contain (idempotent).';

    public function handle(): int
    {
        $kanjiIdByChar = Kanji::pluck('id', 'character')->all();

        if (empty($kanjiIdByChar)) {
            $this->error('No kanji in the database yet — run kanji:import-kanjidic (or seed KanjiSeeder) first.');

            return self::FAILURE;
        }

        $linked = 0;
        $wordsWithAKnownKanji = 0;

        Vocabulary::whereNotNull('japanese')->chunkById(200, function ($chunk) use ($kanjiIdByChar, &$linked, &$wordsWithAKnownKanji) {
            foreach ($chunk as $vocab) {
                $chars = collect(mb_str_split($vocab->japanese))->unique();
                $matchedThisWord = false;

                foreach ($chars as $char) {
                    $kanjiId = $kanjiIdByChar[$char] ?? null;

                    if ($kanjiId === null) {
                        continue;
                    }

                    $matchedThisWord = true;

                    // updateOrInsert keeps this idempotent without needing
                    // Eloquent's pivot attach()/duplicate-key handling.
                    DB::table('kanji_word_links')->updateOrInsert(
                        ['kanji_id' => $kanjiId, 'vocabulary_id' => $vocab->id],
                        ['updated_at' => now(), 'created_at' => now()]
                    );

                    $linked++;
                }

                if ($matchedThisWord) {
                    $wordsWithAKnownKanji++;
                }
            }
        });

        $this->info("Done. {$linked} kanji<->word links ensured, across {$wordsWithAKnownKanji} vocabulary words.");

        return self::SUCCESS;
    }
}
