<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\QuestionOption;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * DUMMY / PLACEHOLDER DATA ONLY — for exercising the LessonPlayer UI
     * before real N5 content (Minna no Nihongo or another vetted source)
     * is available. Do not treat this as accurate Japanese content.
     *
     * Run with: php artisan db:seed --class=TestDataSeeder
     */
    public function run(): void
    {
        $level = Level::create([
            'code' => 'N5',
            'name_id' => 'Pemula',
            'name_en' => 'Beginner',
            'order' => 1,
        ]);

        $unit = Unit::create([
            'level_id' => $level->id,
            'title_id' => 'Unit Percobaan',
            'title_en' => 'Test Unit',
            'order' => 1,
        ]);

        $lesson = Lesson::create([
            'unit_id' => $unit->id,
            'title_id' => 'Pelajaran Percobaan',
            'title_en' => 'Test Lesson',
            'category' => 'vocabulary',
            'order' => 1,
            'xp_reward' => 10,
        ]);

        $question = LessonQuestion::create([
            'lesson_id' => $lesson->id,
            'question_type' => 'multiple_choice',
            'prompt_id' => 'Apa arti kata ini?',
            'prompt_en' => 'What does this word mean?',
            'japanese_text' => 'こんにちは',
            'romaji' => 'konnichiwa',
            'order' => 1,
        ]);

        QuestionOption::create([
            'lesson_question_id' => $question->id,
            'label_id' => 'Halo',
            'label_en' => 'Hello',
            'is_correct' => true,
            'order' => 1,
        ]);

        QuestionOption::create([
            'lesson_question_id' => $question->id,
            'label_id' => 'Selamat tinggal',
            'label_en' => 'Goodbye',
            'is_correct' => false,
            'order' => 2,
        ]);

        QuestionOption::create([
            'lesson_question_id' => $question->id,
            'label_id' => 'Terima kasih',
            'label_en' => 'Thank you',
            'is_correct' => false,
            'order' => 3,
        ]);
    }
}
