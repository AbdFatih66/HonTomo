<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vocabulary;
use Database\Seeders\N4Lesson19Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * N4 Pelajaran 19 (unit order 19 di level N4) muncul di Tata Bahasa dan Kosakata
 * lewat selektor N5 / N4 (?level=N4). Seeder membuat level N4 sendiri bila belum ada.
 */
class N4Lesson19Test extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private function n4Words()
    {
        return Vocabulary::where('jlpt_level', 'N4')
            ->whereHas('category', fn ($q) => $q->where('slug', 'n4-pelajaran-19'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildMiniCurriculum();
        $this->seed(N4Lesson19Seeder::class);

        Sanctum::actingAs(User::factory()->create());
    }

    public function test_seeder_is_idempotent(): void
    {
        $words = $this->n4Words()->count();
        $cards = LessonQuestion::where('question_type', 'grammar')->count();
        $quiz = LessonQuestion::where('question_type', 'multiple_choice')->whereHas('vocabulary', fn ($q) => $q->where('jlpt_level', 'N4'))->count();

        $this->seed(N4Lesson19Seeder::class);

        $this->assertSame($words, $this->n4Words()->count());
        $this->assertSame($cards, LessonQuestion::where('question_type', 'grammar')->count());
        $this->assertSame($quiz, LessonQuestion::where('question_type', 'multiple_choice')->whereHas('vocabulary', fn ($q) => $q->where('jlpt_level', 'N4'))->count());
        $this->assertSame(1, Unit::whereHas('level', fn ($q) => $q->where('code', 'N4'))->where('order', 19)->count());
    }

    public function test_n4_path_lists_the_grammar_lesson_of_unit_nineteen(): void
    {
        $n4 = $this->getJson('/api/learning-path?level=N4')->assertOk();

        $orders = collect($n4->json('units'))->pluck('order')->all();
        $this->assertContains(19, $orders);
    }

    public function test_bunpou_lesson_has_four_cards_with_examples_and_dialogue(): void
    {
        $unit = Unit::whereHas('level', fn ($q) => $q->where('code', 'N4'))->where('order', 19)->firstOrFail();
        $bunpou = Lesson::where('unit_id', $unit->id)->where('category', 'grammar')->firstOrFail();
        $cards = $bunpou->questions()->orderBy('order')->get();

        $this->assertCount(4, $cards);

        foreach ($cards as $card) {
            $this->assertNotEmpty($card->payload['explanation_id']);
            $this->assertNotEmpty($card->payload['explanation_en']);
            $this->assertNotEmpty($card->payload['examples']);
            $this->assertNotEmpty($card->payload['dialogue']['lines']);
        }
    }

    public function test_kosakata_chapter_nineteen_is_listed_and_has_words(): void
    {
        $chapters = collect($this->getJson('/api/vocabulary/chapters?level=N4')->assertOk()->json('data'));
        $nine = $chapters->firstWhere('order', 19);

        $this->assertNotNull($nine);
        $this->assertGreaterThan(40, $nine['count']);
        $this->assertCount(1, $nine['quiz']);

        $words = $this->getJson('/api/vocabulary?chapter=19&level=N4')->assertOk()->json('data');
        $this->assertCount($nine['count'], $words);

        // N5 chapter 9 must not contain N4 words.
        $n5 = $this->getJson('/api/vocabulary?chapter=19')->assertOk()->json('data');
        $this->assertCount(0, collect($n5)->where('jlpt_level', 'N4'));
    }

    public function test_every_word_has_a_four_option_quiz_question(): void
    {
        foreach ($this->n4Words()->get() as $word) {
            $question = LessonQuestion::where('vocabulary_id', $word->id)->first();

            $this->assertNotNull($question, "{$word->romaji} has no quiz question");
            $this->assertSame(4, $question->options()->count());
            $this->assertSame(1, $question->options()->where('is_correct', true)->count());
        }
    }

    public function test_unit_text_does_not_name_the_source_book(): void
    {
        $unit = Unit::whereHas('level', fn ($q) => $q->where('code', 'N4'))->where('order', 19)->firstOrFail();

        foreach ([$unit->title_id, $unit->title_en, $unit->description_id, $unit->description_en] as $text) {
            $this->assertStringNotContainsStringIgnoringCase('minna', $text);
        }
    }
}
