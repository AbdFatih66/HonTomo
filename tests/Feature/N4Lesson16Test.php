<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\User;
use App\Models\Vocabulary;
use Database\Seeders\N4Lesson16Seeder;
use Database\Seeders\N4Lesson1Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * N4 Pelajaran 16 (bab 41) is the second N4 unit; unit
 * numbers 2-15 do not exist yet, so the N4 menus must simply skip them.
 */
class N4Lesson16Test extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildMiniCurriculum();
        $this->seed(N4Lesson1Seeder::class);
        $this->seed(N4Lesson16Seeder::class);

        Sanctum::actingAs(User::factory()->create());
    }

    public function test_seeder_is_idempotent(): void
    {
        $words = Vocabulary::where('category_id', \App\Models\VocabularyCategory::where('slug', 'n4-pelajaran-16')->value('id'))->count();
        $this->assertSame(59, $words);

        $this->seed(N4Lesson16Seeder::class);

        $this->assertSame(59, Vocabulary::whereHas('category', fn ($q) => $q->where('slug', 'n4-pelajaran-16'))->count());
        $this->assertSame(1, Lesson::where('category', 'grammar')->whereHas('unit', fn ($q) => $q->where('order', 16))->count());
    }

    public function test_n4_path_lists_pelajaran_1_and_16_in_order(): void
    {
        $units = $this->getJson('/api/learning-path?level=N4')->assertOk()->json('units');

        $bunpou = fn (int $order) => Lesson::where('category', 'grammar')
            ->whereHas('unit', fn ($q) => $q->where('order', $order)->whereHas('level', fn ($l) => $l->where('code', 'N4')))
            ->value('id');

        $this->assertCount(2, $units);
        $this->assertSame($bunpou(1), $units[0]['lessons'][0]['id']);
        $this->assertSame($bunpou(16), $units[1]['lessons'][0]['id']);
    }

    public function test_bunpou_lesson_has_six_complete_cards(): void
    {
        $bunpou = Lesson::where('category', 'grammar')->whereHas('unit', fn ($q) => $q->where('order', 16)->whereHas('level', fn ($l) => $l->where('code', 'N4')))->firstOrFail();
        $cards = $bunpou->questions()->orderBy('order')->get();

        $this->assertCount(6, $cards);

        foreach ($cards as $card) {
            $this->assertNotEmpty($card->payload['explanation_id']);
            $this->assertNotEmpty($card->payload['explanation_en']);
            $this->assertNotEmpty($card->payload['examples']);
            $this->assertNotEmpty($card->payload['dialogue']['lines']);
        }
    }

    public function test_kosakata_menu_skips_missing_n4_units(): void
    {
        $chapters = $this->getJson('/api/vocabulary/chapters?level=N4')->assertOk()->json('data');

        $this->assertSame([1, 16], array_column($chapters, 'order'));

        $words = $this->getJson('/api/vocabulary?chapter=16&level=N4')->assertOk()->json('data');
        $this->assertCount(59, $words);
    }

    public function test_every_word_has_a_four_option_quiz_question(): void
    {
        $category = \App\Models\VocabularyCategory::where('slug', 'n4-pelajaran-16')->firstOrFail();

        foreach (Vocabulary::where('category_id', $category->id)->get() as $word) {
            $question = LessonQuestion::where('vocabulary_id', $word->id)->first();

            $this->assertNotNull($question, "{$word->romaji} has no quiz question");
            $this->assertSame(4, $question->options()->count());
            $this->assertSame(1, $question->options()->where('is_correct', true)->count());
        }
    }
}
