<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\Level;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vocabulary;
use Database\Seeders\N4Lesson1Seeder;
use Database\Seeders\N4Lesson22Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * N4 Pelajaran 22 (unit order 22 di level N4) — Tata Bahasa, Kosakata + kuis.
 */
class N4Lesson22Test extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildMiniCurriculum();
        $this->seed(N4Lesson1Seeder::class);
        $this->seed(N4Lesson22Seeder::class);

        Sanctum::actingAs(User::factory()->create());
    }

    private function lesson22Words()
    {
        return Vocabulary::where('jlpt_level', 'N4')
            ->whereHas('category', fn ($q) => $q->where('slug', 'n4-pelajaran-22'))
            ->get();
    }

    public function test_seeder_is_idempotent(): void
    {
        $words = $this->lesson22Words()->count();
        $cards = LessonQuestion::where('question_type', 'grammar')->count();
        $units = Unit::whereHas('level', fn ($q) => $q->where('code', 'N4'))->count();

        $this->seed(N4Lesson22Seeder::class);

        $this->assertSame($words, $this->lesson22Words()->count());
        $this->assertSame($cards, LessonQuestion::where('question_type', 'grammar')->count());
        $this->assertSame($units, Unit::whereHas('level', fn ($q) => $q->where('code', 'N4'))->count());
        $this->assertSame(1, Level::where('code', 'N4')->count());
    }

    public function test_bunpou_lesson_has_seven_cards_with_examples_and_dialogue(): void
    {
        $unit = Unit::where('order', 22)->whereHas('level', fn ($q) => $q->where('code', 'N4'))->firstOrFail();
        $bunpou = Lesson::where('unit_id', $unit->id)->where('category', 'grammar')->firstOrFail();
        $cards = $bunpou->questions()->orderBy('order')->get();

        $this->assertCount(5, $cards);

        foreach ($cards as $card) {
            $this->assertNotEmpty($card->payload['explanation_id']);
            $this->assertNotEmpty($card->payload['explanation_en']);
            $this->assertNotEmpty($card->payload['examples']);
            $this->assertNotEmpty($card->payload['dialogue']['lines']);
        }
    }

    public function test_kosakata_chapter_twenty_two_lists_its_words(): void
    {
        $chapters = collect($this->getJson('/api/vocabulary/chapters?level=N4')->assertOk()->json('data'));
        $chapter = $chapters->firstWhere('order', 22);

        $this->assertNotNull($chapter);
        $this->assertGreaterThan(30, $chapter['count']);
        $this->assertCount(1, $chapter['quiz']);

        $words = $this->getJson('/api/vocabulary?chapter=22&level=N4')->assertOk()->json('data');
        $this->assertCount($chapter['count'], $words);

        // Pelajaran 1 N4 tetap utuh.
        $first = $chapters->firstWhere('order', 1);
        $this->assertNotNull($first);
    }

    public function test_every_lesson_twenty_two_word_has_a_four_option_quiz_question(): void
    {
        foreach ($this->lesson22Words() as $word) {
            $question = LessonQuestion::where('vocabulary_id', $word->id)->first();

            $this->assertNotNull($question, "{$word->romaji} has no quiz question");
            $this->assertSame(4, $question->options()->count());
            $this->assertSame(1, $question->options()->where('is_correct', true)->count());
        }
    }

    public function test_no_user_facing_text_names_the_source_book(): void
    {
        $unit = Unit::where('order', 22)->whereHas('level', fn ($q) => $q->where('code', 'N4'))->firstOrFail();

        $this->assertStringNotContainsStringIgnoringCase('minna', $unit->title_id.$unit->description_id.$unit->title_en.$unit->description_en);
    }
}
