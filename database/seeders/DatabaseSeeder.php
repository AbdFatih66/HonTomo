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
            VocabularyQuizSyncSeeder::class, // list <-> quiz: every word in both
            GrammarSeeder::class,
            LessonGrammarQuestionSeeder::class, // weaves grammar cards into the lesson flow
            Lesson1BunpouSeeder::class, // separate "Tata Bahasa" lesson node for Pelajaran 1
            Lesson2to5BunpouSeeder::class, // same treatment for Pelajaran 2-5
            Lesson6to10BunpouSeeder::class, // same treatment for Pelajaran 6-10
            Lesson11to25BunpouSeeder::class, // same treatment for Pelajaran 11-25
            N4Lesson1Seeder::class, // N4 Pelajaran 1: level, bunpou, kosakata, kuis
            N4Lesson2Seeder::class, // N4 Pelajaran 2: bunpou, kosakata, kuis
            N4Lesson3Seeder::class, // N4 Pelajaran 3: bunpou, kosakata, kuis
            N4Lesson4Seeder::class, // N4 Pelajaran 4: bunpou, kosakata, kuis
            N4Lesson5Seeder::class, // N4 Pelajaran 5: bunpou, kosakata, kuis
            N4Lesson6Seeder::class, // N4 Pelajaran 6: bentuk maksud, niat dan rencana (bunpou, kosakata, kuis)
            N4Lesson7Seeder::class, // N4 Pelajaran 7: bunpou, kosakata, kuis
            N4Lesson8Seeder::class, // N4 Pelajaran 8: perintah/larangan, tanda, pesan; bunpou + kosakata + kuis
            N4Lesson9Seeder::class, // N4 Pelajaran 9: bunpou, kosakata, kuis
            N4Lesson10Seeder::class, // N4 Pelajaran 10: bentuk syarat 〜ば／〜なら
            N4Lesson11Seeder::class, // N4 Pelajaran 11: bunpou, kosakata, kuis
            N4Lesson12Seeder::class, // N4 Pelajaran 12: bunpou, kosakata, kuis
            N4Lesson13Seeder::class, // N4 Pelajaran 13: bunpou, kosakata, kuis
            N4Lesson14Seeder::class, // N4 Pelajaran 14: sebab, perasaan (bunpou, kosakata, kuis)
            N4Lesson15Seeder::class, // N4 Pelajaran 15: kalimat tanya sisipan, 〜てみます, 〜さ (bunpou, kosakata, kuis)
            N4Lesson16Seeder::class, // N4 Pelajaran 16: memberi dan menerima
            N4Lesson17Seeder::class, // N4 Pelajaran 17: bunpou, kosakata, kuis
            N4Lesson18Seeder::class, // N4 Pelajaran 18: 〜そうです, 〜て来ます, 〜てくれませんか; bunpou + kosakata + kuis
            N4Lesson19Seeder::class, // N4 Pelajaran 19: bunpou, kosakata, kuis
            N4Lesson20Seeder::class, // N4 Pelajaran 20: 〜場合は, 〜のに
            N4Lesson21Seeder::class, // N4 Pelajaran 21: bunpou, kosakata, kuis
            N4Lesson22Seeder::class, // N4 Pelajaran 22: bunpou, kosakata, kuis
            N4Lesson23Seeder::class, // N4 Pelajaran 23: kata kerja kausatif; bunpou, kosakata, kuis
            N4Lesson24Seeder::class, // N4 Pelajaran 24: bunpou, kosakata, kuis
            N4Lesson25Seeder::class, // N4 Pelajaran 25: 謙譲語 (merendahkan diri); bunpou + kosakata + kuis
            KanjiSeeder::class,
            KanaSeeder::class, // Hiragana & Katakana chart, stroke order, dakuten/handakuten
            AchievementSeeder::class,
        ]);
    }
}
