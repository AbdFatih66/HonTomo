<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class QuestionOptionSeeder extends Seeder
{
    /**
     * Intentionally a no-op: options for the generated multiple_choice
     * questions are created inline inside LessonQuestionSeeder (each
     * question needs its options created atomically, right after it).
     * Kept as an empty seeder only so DatabaseSeeder's call list doesn't
     * need to change if this ever needs standalone question-option data.
     */
    public function run(): void
    {
        //
    }
}
