<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Level;
use App\Models\Unit;
use App\Models\Vocabulary;
use App\Models\VocabularyCategory;
use App\Services\VocabularyQuizSync;
use Illuminate\Database\Seeder;

/**
 * Dasar seeder kosakata N3 (satu subclass per pelajaran).
 *
 * Satu pelajaran N3 = satu Unit di level N3 dengan:
 *   - kategori kosakata `n3-pelajaran-{unitOrder}` + kata (jlpt_level N3)
 *   - lesson kosakata (order 1) + kuis (VocabularyQuizSync::run('N3'))
 *
 * Belum ada lesson tata bahasa (Bunpou) untuk N3, jadi lesson kosakata tidak
 * punya prasyarat.
 *
 * Subclass cukup mengisi unitOrder() dan words(). Aman dijalankan berulang dan
 * pada database yang sudah punya user:
 *   php artisan db:seed --class=N3Lesson1Seeder
 *
 * Kunci pencarian SQL memakai ASCII (kategori.slug + romaji), bukan huruf
 * Jepang (lihat docs/AGENTS.md aturan 4). Pasangan kata/bacaan/arti adalah fakta
 * kamus dari daftar kosakata buku. Nama diri (penulis, judul buku, nama tempat)
 * sengaja tidak dimasukkan, sama seperti di N5 dan N4.
 */
abstract class N3LessonSeeder extends Seeder
{
    /** Nomor pelajaran di dalam level N3 (= Unit.order). */
    abstract protected function unitOrder(): int;

    /**
     * [japanese, hiragana, romaji, meaning_id, meaning_en]; romaji unik dalam pelajaran.
     *
     * @return array<int, array<int, string>>
     */
    abstract protected function words(): array;

    public function run(): void
    {
        $n = $this->unitOrder();

        $level = Level::updateOrCreate(
            ['code' => 'N3'],
            [
                'name_id' => 'Menengah (N3)',
                'name_en' => 'Intermediate (N3)',
                'description_id' => 'Tingkat menengah: kosakata dan bacaan yang lebih luas untuk kehidupan sehari-hari dan teks yang lebih panjang.',
                'description_en' => 'Intermediate level: broader vocabulary and reading for everyday life and longer texts.',
                'order' => 3,
                'is_active' => true,
            ]
        );

        $unit = Unit::updateOrCreate(
            ['level_id' => $level->id, 'order' => $n],
            [
                'title_id' => "Pelajaran {$n}",
                'title_en' => "Lesson {$n}",
                'description_id' => "Kosakata N3 Pelajaran {$n}.",
                'description_en' => "N3 vocabulary, Lesson {$n}.",
                'icon' => 'mdi-book-open-page-variant',
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
                'prerequisite_lesson_id' => null,
                'is_active' => true,
            ]
        );

        $this->seedVocabulary($n);

        // Kuis kosakata: satu soal pilihan ganda per kata (idempotent).
        app(VocabularyQuizSync::class)->run('N3');
    }

    private function seedVocabulary(int $n): void
    {
        $category = VocabularyCategory::updateOrCreate(
            ['slug' => "n3-pelajaran-{$n}"],
            [
                'name_id' => "Kosakata N3 Pelajaran {$n}",
                'name_en' => "N3 Lesson {$n} Vocabulary",
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
                    'jlpt_level' => 'N3',
                    'difficulty' => 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
