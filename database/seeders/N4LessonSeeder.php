<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\Level;
use App\Models\Unit;
use App\Models\Vocabulary;
use App\Models\VocabularyCategory;
use App\Services\VocabularyQuizSync;
use Illuminate\Database\Seeder;

/**
 * Dasar semua seeder pelajaran N4 (satu subclass per pelajaran).
 *
 * Satu pelajaran N4 = satu Unit di level N4 dengan:
 *   - lesson Bunpou (order 0, category grammar) berisi kartu tata bahasa
 *   - kategori kosakata `n4-pelajaran-{unitOrder}` + kata (jlpt_level N4)
 *   - lesson kosakata (order 1) + kuis (VocabularyQuizSync::run('N4'))
 *
 * Subclass cukup mengisi unitOrder(), unitInfo(), cards() dan words().
 * Aman dijalankan berulang dan pada database yang sudah punya user:
 *   php artisan db:seed --class=N4Lesson16Seeder
 *
 * Kata kunci pencarian SQL memakai ASCII (kategori.slug + romaji), bukan
 * huruf Jepang (lihat docs/AGENTS.md aturan 4). Pasangan kata/bacaan/arti
 * adalah fakta kamus dari daftar kosakata buku; penjelasan, contoh dan dialog
 * ditulis baru untuk aplikasi ini, tidak disalin dari buku.
 */
abstract class N4LessonSeeder extends Seeder
{
    /** Nomor pelajaran di dalam level N4 (= Unit.order). */
    abstract protected function unitOrder(): int;

    /** @return array{title_id: string, title_en: string, description_id: string, description_en: string} */
    abstract protected function unitInfo(): array;

    /** @return array<int, array<string, mixed>> kartu tata bahasa, urut */
    abstract protected function cards(): array;

    /**
     * [japanese, hiragana, romaji, meaning_id, meaning_en]; romaji unik dalam pelajaran.
     *
     * @return array<int, array<int, string>>
     */
    abstract protected function words(): array;

    public function run(): void
    {
        $level = Level::updateOrCreate(
            ['code' => 'N4'],
            [
                'name_id' => 'Dasar Lanjutan (N4)',
                'name_en' => 'Upper Beginner (N4)',
                'description_id' => 'Lanjutan dari N5: percakapan sehari-hari yang lebih natural, meminta penjelasan, meminta tolong dengan sopan, dan bertanya cara melakukan sesuatu.',
                'description_en' => 'Continues from N5: more natural everyday conversation, asking for explanations, polite requests, and asking how to do things.',
                'order' => 2,
                'is_active' => true,
            ]
        );

        $info = $this->unitInfo();

        $unit = Unit::updateOrCreate(
            ['level_id' => $level->id, 'order' => $this->unitOrder()],
            [
                'title_id' => $info['title_id'],
                'title_en' => $info['title_en'],
                'description_id' => $info['description_id'],
                'description_en' => $info['description_en'],
                'icon' => 'mdi-book-open-page-variant',
                'is_active' => true,
            ]
        );

        $bunpou = Lesson::updateOrCreate(
            ['unit_id' => $unit->id, 'order' => 0],
            [
                'title_id' => 'Tata Bahasa (Bunpou)',
                'title_en' => 'Grammar (Bunpou)',
                'category' => 'grammar',
                'xp_reward' => 10,
                'required_accuracy' => 0,
                'prerequisite_lesson_id' => null,
                'is_active' => true,
            ]
        );

        Lesson::updateOrCreate(
            ['unit_id' => $unit->id, 'order' => 1],
            [
                'title_id' => 'Kosakata',
                'title_en' => 'Vocabulary',
                'category' => 'vocabulary',
                'xp_reward' => 10,
                'required_accuracy' => 70,
                'prerequisite_lesson_id' => $bunpou->id,
                'is_active' => true,
            ]
        );

        $this->seedCards($bunpou);
        $this->seedVocabulary();

        // Kuis kosakata: satu soal pilihan ganda per kata (idempotent).
        app(VocabularyQuizSync::class)->run('N4');
    }

    private function seedCards(Lesson $bunpou): void
    {
        foreach (array_values($this->cards()) as $position => $card) {
            LessonQuestion::updateOrCreate(
                [
                    'lesson_id' => $bunpou->id,
                    'question_type' => 'grammar',
                    'order' => $position + 1,
                ],
                [
                    'grammar_id' => null,
                    'prompt_id' => $card['title_id'],
                    'prompt_en' => $card['title_en'],
                    'japanese_text' => $card['pattern'],
                    'payload' => $card['payload'],
                    'difficulty' => 2,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedVocabulary(): void
    {
        $n = $this->unitOrder();

        $category = VocabularyCategory::updateOrCreate(
            ['slug' => "n4-pelajaran-{$n}"],
            [
                'name_id' => "Kosakata N4 Pelajaran {$n}",
                'name_en' => "N4 Lesson {$n} Vocabulary",
            ]
        );

        foreach ($this->words() as [$japanese, $hiragana, $romaji, $meaningId, $meaningEn]) {
            // Kunci ASCII (category + romaji): collation unicode_ci menyamakan か = が = カ.
            Vocabulary::updateOrCreate(
                ['category_id' => $category->id, 'romaji' => $romaji],
                [
                    'japanese' => $japanese,
                    'hiragana' => $hiragana,
                    'meaning_id' => $meaningId,
                    'meaning_en' => $meaningEn,
                    'jlpt_level' => 'N4',
                    'difficulty' => 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
