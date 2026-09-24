<?php

namespace Database\Seeders;

use App\Models\Grammar;
use App\Models\Lesson;
use App\Models\LessonQuestion;
use Illuminate\Database\Seeder;

class LessonGrammarQuestionSeeder extends Seeder
{
    /**
     * Turns each Grammar row into a "grammar" question that opens the
     * matching lesson, so a pattern is actually explained before the
     * vocabulary questions test it — instead of the lesson being nothing
     * but "what does this word mean?" flashcards.
     */
    public function run(): void
    {
        $map = [
            // [unit order, lesson order] => [grammar order numbers, in teaching order]
            // Pelajaran 1's grammar (wa/desu, ja arimasen, desu ka, mo, no, san)
            // now lives in its own lesson — see Lesson1BunpouSeeder.
            [[2, 1], [3, 7]], // "no" (#6) is taught in Pelajaran 1 now
            [[2, 2], [9, 10, 11]],
            [[3, 1], [8, 12, 13]],
            [[4, 1], [14, 15, 16]],
            [[5, 1], [17, 18, 19]],
        ];

        foreach ($map as [$titleOrder, $grammarOrders]) {
            $this->attach($titleOrder, $grammarOrders);
        }
    }

    private function attach(array $titleOrder, array $grammarOrders): void
    {
        [$unitOrder, $lessonOrder] = $titleOrder;

        $lesson = Lesson::whereHas('unit', fn ($q) => $q->where('order', $unitOrder))
            ->where('order', $lessonOrder)
            ->first();

        if (! $lesson) {
            return;
        }

        $grammars = Grammar::whereIn('order', $grammarOrders)->get()->keyBy('order');
        $count = 0;

        foreach ($grammarOrders as $position => $order) {
            $grammar = $grammars->get($order);

            if (! $grammar) {
                continue;
            }

            $question = LessonQuestion::firstOrCreate(
                [
                    'lesson_id' => $lesson->id,
                    'grammar_id' => $grammar->id,
                    'question_type' => 'grammar',
                ],
                [
                    'prompt_id' => $grammar->title_id,
                    'prompt_en' => $grammar->title_en,
                    'japanese_text' => $grammar->pattern,
                    'payload' => [
                        'explanation_id' => $grammar->explanation_id,
                        'explanation_en' => $grammar->explanation_en,
                        'example_japanese' => $grammar->example_japanese,
                        'example_reading' => $grammar->example_reading,
                        'example_translation_id' => $grammar->example_translation_id,
                        'example_translation_en' => $grammar->example_translation_en,
                    ],
                    'order' => $position + 1,
                    'difficulty' => 1,
                    'is_active' => true,
                ]
            );

            if ($question->wasRecentlyCreated) {
                $count++;
            }
        }

        // Push the pre-existing vocabulary questions after the new grammar
        // cards so a lesson always opens with "learn the pattern" before
        // "test the words".
        if ($count > 0) {
            LessonQuestion::where('lesson_id', $lesson->id)
                ->where('question_type', '!=', 'grammar')
                ->increment('order', $count);
        }
    }
}
