<?php

namespace App\Console\Commands;

use App\Models\KanaCharacter;
use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Services\QuestionOptionRepair;
use Illuminate\Console\Command;

/**
 * Lists every Hiragana/Katakana lesson in the learning path (by category,
 * by title, or because its questions show a single kana character) together
 * with its question types and how many choice questions are unanswerable.
 *
 *   php artisan kana:diagnose            # report only
 *   php artisan kana:diagnose --repair   # also rebuild the broken ones now
 */
class DiagnoseKanaLessons extends Command
{
    protected $signature = 'kana:diagnose {--repair : Rebuild missing quizzes / answer options}';

    protected $description = 'Report (and optionally repair) Hiragana/Katakana lessons without quiz options';

    public function handle(QuestionOptionRepair $repair): int
    {
        $kana = KanaCharacter::pluck('character')->all();

        $lessons = Lesson::with(['unit', 'questions.options'])
            ->get()
            ->filter(function (Lesson $lesson) use ($kana) {
                $haystack = mb_strtolower($lesson->category.' '.$lesson->title_id.' '.$lesson->title_en);

                return str_contains($haystack, 'hiragana')
                    || str_contains($haystack, 'katakana')
                    || $lesson->questions->contains(fn (LessonQuestion $q) => in_array(trim((string) $q->japanese_text), $kana, true));
            });

        if ($lessons->isEmpty()) {
            $this->warn('No Hiragana/Katakana lessons found in the learning path.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($lessons as $lesson) {
            $types = $lesson->questions->groupBy('question_type')->map->count();
            $choice = $lesson->questions->reject(fn ($q) => in_array($q->question_type, ['flashcard', 'grammar', 'typing', 'translation', 'matching', 'sentence_ordering', 'writing', 'speaking'], true));
            $broken = $choice->filter(fn ($q) => $q->options->count() < 2 || $q->options->where('is_correct', true)->count() !== 1)->count();

            $rows[] = [
                $lesson->id,
                $lesson->unit?->title_id,
                $lesson->title_id,
                $lesson->category,
                $types->map(fn ($n, $t) => "$t:$n")->implode(' '),
                $choice->count() === 0 ? 'NO QUIZ' : "$broken / {$choice->count()}",
            ];
        }

        $this->table(['id', 'unit', 'lesson', 'category', 'question types', 'unanswerable / choice'], $rows);

        if ($this->option('repair')) {
            $lessons->each(fn (Lesson $lesson) => $repair->ensureForLesson($lesson->fresh()));
            $this->info('Repair pass done — run without --repair to see the new state.');
        }

        return self::SUCCESS;
    }
}
