<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LevelSeeder::class,
            UnitSeeder::class,
            LessonSeeder::class,
            VocabularyCategorySeeder::class,
            VocabularySeeder::class,
            VocabularyExampleSeeder::class,
            ExtendedCurriculumSeeder::class, // Pelajaran 3-25 (units, lessons, vocab)
            MissingVocabularySeeder::class, // fills the Pelajaran 1-5 gaps; must run before the quiz builder
            LessonQuestionSeeder::class,
            QuestionOptionSeeder::class,
            GrammarSeeder::class,
            LessonGrammarQuestionSeeder::class, // weaves grammar cards into the lesson flow
            Lesson1BunpouSeeder::class, // separate "Tata Bahasa" lesson node for Pelajaran 1
            Lesson2to5BunpouSeeder::class, // same treatment for Pelajaran 2-5
            Lesson6to10BunpouSeeder::class, // same treatment for Pelajaran 6-10
            Lesson11to25BunpouSeeder::class, // same treatment for Pelajaran 11-25
            KanjiSeeder::class,
            KanaSeeder::class, // Hiragana & Katakana chart, stroke order, dakuten/handakuten
            AchievementSeeder::class,
        ]);
    }
}
