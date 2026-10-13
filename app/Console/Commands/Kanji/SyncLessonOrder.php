<?php

namespace App\Console\Commands\Kanji;

use App\Models\Kanji;
use App\Models\Lesson;
use Illuminate\Console\Command;

/**
 * Fills kanjis.earliest_lesson_order / earliest_lesson_id from
 * kanji_word_links (see Kanji::curriculumWords(), populated by
 * `kanji:link-vocabulary`). This is what lets the Kanji module offer a
 * "sesuai urutan Pelajaran" sort/filter instead of only JLPT level —
 * see docs/kanji-module.md bagian 8 untuk kenapa dua sistem ini
 * (JLPT level vs kurikulum Pelajaran) sebelumnya berjalan terpisah.
 *
 * Idempotent (updateOrSkip per kanji, no destructive step) — safe to
 * re-run any time. **Jalankan ulang setiap kali** vocabulary/lesson
 * kurikulum baru di-seed ATAU `kanji:link-vocabulary` dijalankan ulang,
 * supaya urutan tetap sinkron — sama seperti aturan link-vocabulary
 * sendiri.
 */
class SyncLessonOrder extends Command
{
    protected $signature = 'kanji:sync-lesson-order';

    protected $description = 'Sync kanjis.earliest_lesson_order from kanji_word_links so kanji can be sorted/filtered to match lesson curriculum progress';

    public function handle(): int
    {
        // See Lesson::globalOrderMap() — single source of truth for lesson
        // ordering, shared with KanjiController so the two never drift.
        $lessonOrder = Lesson::globalOrderMap();

        if ($lessonOrder->isEmpty()) {
            $this->warn('No lessons found — nothing to sync. Seed lessons/units/levels first.');

            return self::SUCCESS;
        }

        $updated = 0;
        $cleared = 0;

        Kanji::with('curriculumWords')->chunkById(50, function ($kanjis) use ($lessonOrder, &$updated, &$cleared) {
            foreach ($kanjis as $kanji) {
                // curriculumWords() is the app's own curriculum vocabulary
                // (Minna no Nihongo), not JMdict — see Kanji::curriculumWords()
                // docblock. ->lessons() is a plain query per word (no formal
                // relation — see Vocabulary::lessons() docblock on why),
                // fine at this scale (hundreds of kanji, a handful of words
                // each), same tradeoff already accepted elsewhere in this module.
                $best = $kanji->curriculumWords
                    ->flatMap(fn ($word) => $word->lessons()->pluck('id'))
                    ->unique()
                    ->map(fn ($lessonId) => [$lessonId, $lessonOrder->get($lessonId)])
                    ->filter(fn ($pair) => $pair[1] !== null)
                    ->sortBy(fn ($pair) => $pair[1])
                    ->first();

                if ($best === null) {
                    if ($kanji->earliest_lesson_id !== null) {
                        // No longer linked to any lesson (e.g. link was
                        // removed) — clear rather than leave stale.
                        $kanji->forceFill(['earliest_lesson_id' => null, 'earliest_lesson_order' => null])->save();
                        $cleared++;
                    }

                    continue;
                }

                [$lessonId, $order] = $best;

                if ($kanji->earliest_lesson_id === $lessonId && $kanji->earliest_lesson_order === $order) {
                    continue; // already in sync
                }

                $kanji->forceFill([
                    'earliest_lesson_id' => $lessonId,
                    'earliest_lesson_order' => $order,
                ])->save();

                $updated++;
            }
        });

        $this->info("Synced {$updated} kanji" . ($cleared ? ", cleared {$cleared} stale link(s)." : '.'));

        return self::SUCCESS;
    }
}
