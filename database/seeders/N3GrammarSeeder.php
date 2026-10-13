<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\Level;
use App\Models\Unit;
use Illuminate\Database\Seeder;

/**
 * Dasar seeder Tata Bahasa (Bunpou) N3, satu subclass per pelajaran.
 *
 * Satu pelajaran N3 = unit di level N3 (dibuat oleh N3Lesson{n}Seeder bila belum ada) dengan:
 *   - lesson Bunpou (order 0, category grammar) berisi kartu `grammar`
 *   - lesson kosakata (order 1) otomatis memakai Bunpou sebagai prasyarat
 *
 * Subclass cukup mengisi unitOrder() dan cards(). Aman dijalankan berulang
 * dan pada database yang sudah punya user:
 *   php artisan db:seed --class=N3Lesson1BunpouSeeder
 *
 * Penjelasan, contoh kalimat, dan catatan ditulis baru untuk aplikasi ini.
 * Kunci pencarian SQL memakai ASCII (unit.order + lesson.order + urutan kartu),
 * bukan huruf Jepang (lihat docs/AGENTS.md aturan 4).
 */
abstract class N3GrammarSeeder extends Seeder
{
    /** Nomor pelajaran di dalam level N3 (= Unit.order). */
    abstract protected function unitOrder(): int;

    /** @return array<int, array<string, mixed>> */
    abstract protected function cards(): array;

    /**
     * Dialog opsional per kartu: kunci = nomor kartu (mulai 1), nilai = daftar baris dari dl().
     * Kartu tanpa entri tetap tanpa dialog.
     *
     * @return array<int, array<int, array<string, string>>>
     */
    protected function dialogues(): array
    {
        return [];
    }

    public function run(): void
    {
        $n = $this->unitOrder();

        // Level + unit (dan kosakata) dibuat oleh seeder kosakata pelajaran yang sama.
        $unit = Level::where('code', 'N3')->first()?->units()->where('order', $n)->first();
        if (! $unit) {
            $this->call('Database\\Seeders\\N3Lesson'.$n.'Seeder');
            $unit = Level::where('code', 'N3')->firstOrFail()->units()->where('order', $n)->firstOrFail();
        }

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

        $vocab = Lesson::where('unit_id', $unit->id)->where('order', 1)->first();
        if ($vocab) {
            $vocab->update(['prerequisite_lesson_id' => $bunpou->id]);
        }

        $dialogues = $this->dialogues();

        foreach (array_values($this->cards()) as $position => $card) {
            $payload = $card['payload'];
            if (! empty($dialogues[$position + 1])) {
                $payload['dialogue'] = $dialogues[$position + 1];
            }

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
                    'payload' => $payload,
                    'difficulty' => 3,
                    'is_active' => true,
                ]
            );
        }
    }

    /** Satu baris dialog: penutur (A/B/C), kalimat Jepang (furigana 漢字《かんじ》), romaji, Indonesia, Inggris. @return array<string, string> */
    protected function dl(string $speaker, string $ja, string $reading, string $id, string $en): array
    {
        return ['speaker' => $speaker, 'ja' => $ja, 'reading' => $reading, 'id' => $id, 'en' => $en];
    }

    /** @return array<string, string> */
    protected function ex(string $ja, string $reading, string $id, string $en): array
    {
        return ['ja' => $ja, 'reading' => $reading, 'id' => $id, 'en' => $en];
    }

    /**
     * @param  array<int, string>  $notesId
     * @param  array<int, string>  $notesEn
     * @param  array<int, array<string, string>>  $examples
     * @return array<string, mixed>
     */
    protected function card(
        string $titleId,
        string $titleEn,
        string $pattern,
        string $explanationId,
        string $explanationEn,
        array $notesId,
        array $notesEn,
        array $examples,
    ): array {
        return [
            'title_id' => $titleId,
            'title_en' => $titleEn,
            'pattern' => $pattern,
            'payload' => [
                'explanation_id' => $explanationId,
                'explanation_en' => $explanationEn,
                'notes_id' => $notesId,
                'notes_en' => $notesEn,
                'examples' => $examples,
            ],
        ];
    }
}
