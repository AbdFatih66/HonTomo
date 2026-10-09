<?php

namespace Tests\Concerns;

use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\Level;
use App\Models\QuestionOption;
use App\Models\Unit;

/**
 * A tiny, self-contained curriculum for regression tests: one level, one unit,
 * two lessons (the second unlocks after the first) and four multiple-choice
 * questions. Much faster than seeding the full curriculum, and it only ever
 * writes to the throw-away test database.
 */
trait BuildsLessonFixtures
{
    protected Lesson $firstLesson;
    protected Lesson $secondLesson;
    protected LessonQuestion $question;
    protected QuestionOption $correctOption;

    protected function buildMiniCurriculum(): void
    {
        $level = Level::create([
            'code' => 'N5', 'name_id' => 'N5', 'name_en' => 'N5', 'order' => 1, 'is_active' => true,
        ]);

        $unit = Unit::create([
            'level_id' => $level->id, 'title_id' => 'Pelajaran 1', 'title_en' => 'Lesson 1',
            'order' => 1, 'is_active' => true,
        ]);

        $this->firstLesson = Lesson::create([
            'unit_id' => $unit->id, 'title_id' => 'Kosakata 1', 'title_en' => 'Vocabulary 1',
            'category' => 'vocabulary', 'order' => 1, 'xp_reward' => 10,
            'required_accuracy' => 60, 'is_active' => true,
        ]);

        $this->secondLesson = Lesson::create([
            'unit_id' => $unit->id, 'title_id' => 'Kosakata 2', 'title_en' => 'Vocabulary 2',
            'category' => 'vocabulary', 'order' => 2, 'xp_reward' => 10,
            'prerequisite_lesson_id' => $this->firstLesson->id,
            'required_accuracy' => 60, 'is_active' => true,
        ]);

        $words = [['neko', 'kucing'], ['inu', 'anjing'], ['tori', 'burung'], ['sakana', 'ikan']];

        foreach ($words as $i => [$romaji, $meaning]) {
            $question = LessonQuestion::create([
                'lesson_id' => $this->firstLesson->id,
                'question_type' => 'multiple_choice',
                'prompt_id' => "Apa arti \"{$romaji}\"?",
                'prompt_en' => "What does \"{$romaji}\" mean?",
                'order' => $i + 1,
                'is_active' => true,
            ]);

            foreach ($words as $j => [, $label]) {
                $option = QuestionOption::create([
                    'lesson_question_id' => $question->id,
                    'label_id' => $label,
                    'label_en' => $label,
                    'is_correct' => $i === $j,
                    'order' => $j + 1,
                ]);

                if ($i === 0 && $j === 0) {
                    $this->question = $question;
                    $this->correctOption = $option;
                }
            }
        }
    }
}
