<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\Level;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\N3Lesson10BunpouSeeder;
use Database\Seeders\N3Lesson11BunpouSeeder;
use Database\Seeders\N3Lesson12BunpouSeeder;
use Database\Seeders\N3Lesson1BunpouSeeder;
use Database\Seeders\N3Lesson2BunpouSeeder;
use Database\Seeders\N3Lesson3BunpouSeeder;
use Database\Seeders\N3Lesson4BunpouSeeder;
use Database\Seeders\N3Lesson5BunpouSeeder;
use Database\Seeders\N3Lesson6BunpouSeeder;
use Database\Seeders\N3Lesson7BunpouSeeder;
use Database\Seeders\N3Lesson8BunpouSeeder;
use Database\Seeders\N3Lesson9BunpouSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tata Bahasa (Bunpou) N3 Pelajaran 1-12 — kartu, jalur belajar ?level=N3, idempotensi.
 */
class N3GrammarTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, int> */
    private array $expectedCards = [1 => 9, 2 => 6, 3 => 7, 4 => 10, 5 => 10, 6 => 10, 7 => 10, 8 => 7, 9 => 6, 10 => 9, 11 => 10, 12 => 11];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(N3Lesson1BunpouSeeder::class);
        $this->seed(N3Lesson2BunpouSeeder::class);
        $this->seed(N3Lesson3BunpouSeeder::class);
        $this->seed(N3Lesson4BunpouSeeder::class);
        $this->seed(N3Lesson5BunpouSeeder::class);
        $this->seed(N3Lesson6BunpouSeeder::class);
        $this->seed(N3Lesson7BunpouSeeder::class);
        $this->seed(N3Lesson8BunpouSeeder::class);
        $this->seed(N3Lesson9BunpouSeeder::class);
        $this->seed(N3Lesson10BunpouSeeder::class);
        $this->seed(N3Lesson11BunpouSeeder::class);
        $this->seed(N3Lesson12BunpouSeeder::class);

        Sanctum::actingAs(User::factory()->create());
    }

    private function bunpou(int $n): Lesson
    {
        $unit = Unit::where('order', $n)->whereHas('level', fn ($q) => $q->where('code', 'N3'))->firstOrFail();

        return Lesson::where('unit_id', $unit->id)->where('category', 'grammar')->firstOrFail();
    }

    public function test_each_lesson_has_its_cards_with_examples(): void
    {
        foreach ($this->expectedCards as $n => $count) {
            $cards = $this->bunpou($n)->questions()->orderBy('order')->get();

            $this->assertCount($count, $cards, "N3 Pelajaran {$n}");

            foreach ($cards as $card) {
                $this->assertSame('grammar', $card->question_type);
                $this->assertNotEmpty($card->payload['explanation_id']);
                $this->assertNotEmpty($card->payload['explanation_en']);
                $this->assertNotEmpty($card->payload['examples']);
            }
        }
    }

    public function test_every_card_has_a_dialogue(): void
    {
        foreach ($this->expectedCards as $n => $count) {
            $cards = $this->bunpou($n)->questions()->orderBy('order')->get();

            foreach ($cards as $card) {
                $dialogue = $card->payload['dialogue'] ?? [];

                $this->assertGreaterThanOrEqual(3, count($dialogue), "N3 Pelajaran {$n} kartu {$card->order}");

                foreach ($dialogue as $line) {
                    foreach (['speaker', 'ja', 'reading', 'id', 'en'] as $key) {
                        $this->assertNotEmpty($line[$key] ?? null, "N3 Pelajaran {$n} kartu {$card->order}: kolom {$key}");
                    }
                }
            }
        }
    }

    public function test_seeders_are_idempotent(): void
    {
        $cards = LessonQuestion::where('question_type', 'grammar')->count();
        $units = Unit::whereHas('level', fn ($q) => $q->where('code', 'N3'))->count();

        $this->seed(N3Lesson1BunpouSeeder::class);
        $this->seed(N3Lesson9BunpouSeeder::class);
        $this->seed(N3Lesson12BunpouSeeder::class);

        $this->assertSame($cards, LessonQuestion::where('question_type', 'grammar')->count());
        $this->assertSame($units, Unit::whereHas('level', fn ($q) => $q->where('code', 'N3'))->count());
        $this->assertSame(1, Level::where('code', 'N3')->count());
    }

    public function test_learning_path_for_n3_lists_the_twelve_grammar_lessons(): void
    {
        $units = collect($this->getJson('/api/learning-path?level=N3')->assertOk()->json('units'));

        $this->assertSame('N3', $this->getJson('/api/learning-path?level=N3')->json('level.code'));
        $this->assertCount(12, $units);
        $this->assertSame([1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1], $units->map(fn ($u) => count($u['lessons']))->all());
    }

    public function test_vocabulary_lesson_requires_the_grammar_lesson(): void
    {
        $unit = Unit::where('order', 1)->whereHas('level', fn ($q) => $q->where('code', 'N3'))->firstOrFail();
        $vocab = Lesson::where('unit_id', $unit->id)->where('category', 'vocabulary')->firstOrFail();

        $this->assertSame($this->bunpou(1)->id, $vocab->prerequisite_lesson_id);
    }

    public function test_no_user_facing_text_names_the_source_book(): void
    {
        foreach (LessonQuestion::where('question_type', 'grammar')->get() as $card) {
            $text = $card->prompt_id.$card->prompt_en.json_encode($card->payload, JSON_UNESCAPED_UNICODE);

            // Judul buku sumber tidak boleh muncul (romaji みんな biasa saja, jadi cek judul lengkapnya).
            $this->assertStringNotContainsStringIgnoringCase('minna no nihongo', $text);
            $this->assertStringNotContainsString('みんなの日本語', $text);
        }
    }
}
