<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\Level;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vocabulary;
use Database\Seeders\N4Lesson1Seeder;
use Database\Seeders\N4Lesson7Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * N4 Pelajaran 7 (saran, dugaan, kesehatan, cuaca) berdampingan dengan Pelajaran 1
 * dan dipilih lewat selektor N5 / N4.
 */
class N4Lesson7Test extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildMiniCurriculum();
        $this->seed(N4Lesson1Seeder::class);
        $this->seed(N4Lesson7Seeder::class);

        Sanctum::actingAs(User::factory()->create());
    }

    private function unit7(): Unit
    {
        return Unit::where('order', 7)->whereHas('level', fn ($q) => $q->where('code', 'N4'))->firstOrFail();
    }

    public function test_seeder_is_idempotent_and_does_not_touch_lesson_1(): void
    {
        $words = Vocabulary::where('jlpt_level', 'N4')->count();
        $cards = LessonQuestion::where('question_type', 'grammar')->count();
        $quiz = LessonQuestion::where('question_type', 'multiple_choice')->count();

        $this->seed(N4Lesson7Seeder::class);

        $this->assertSame($words, Vocabulary::where('jlpt_level', 'N4')->count());
        $this->assertSame($cards, LessonQuestion::where('question_type', 'grammar')->count());
        $this->assertSame($quiz, LessonQuestion::where('question_type', 'multiple_choice')->count());
        $this->assertSame(1, Level::where('code', 'N4')->count());
        $this->assertSame(2, Unit::whereHas('level', fn ($q) => $q->where('code', 'N4'))->count());
    }

    public function test_bunpou_lesson_has_six_cards_with_examples_and_dialogue(): void
    {
        $bunpou = Lesson::where('unit_id', $this->unit7()->id)->where('category', 'grammar')->firstOrFail();
        $cards = $bunpou->questions()->orderBy('order')->get();

        $this->assertCount(6, $cards);

        foreach ($cards as $card) {
            $this->assertNotEmpty($card->payload['explanation_id']);
            $this->assertNotEmpty($card->payload['explanation_en']);
            $this->assertNotEmpty($card->payload['examples']);
            $this->assertNotEmpty($card->payload['dialogue']['lines']);
        }
    }

    public function test_n4_path_lists_both_lessons(): void
    {
        $n4 = $this->getJson('/api/learning-path?level=N4')->assertOk();

        $this->assertCount(2, $n4->json('units'));
    }

    public function test_kosakata_chapter_7_has_words_and_a_quiz(): void
    {
        $chapters = collect($this->getJson('/api/vocabulary/chapters?level=N4')->assertOk()->json('data'));

        $this->assertSame([1, 7], $chapters->pluck('order')->all());

        $seven = $chapters->firstWhere('order', 7);
        $this->assertGreaterThan(55, $seven['count']);
        $this->assertCount(1, $seven['quiz']);

        $words = $this->getJson('/api/vocabulary?chapter=7&level=N4')->assertOk()->json('data');
        $this->assertCount($seven['count'], $words);
    }

    public function test_every_lesson_7_word_has_a_four_option_quiz_question(): void
    {
        $words = Vocabulary::whereHas('category', fn ($q) => $q->where('slug', 'n4-pelajaran-7'))->get();

        $this->assertNotEmpty($words);

        foreach ($words as $word) {
            $question = LessonQuestion::where('vocabulary_id', $word->id)->first();

            $this->assertNotNull($question, "{$word->romaji} has no quiz question");
            $this->assertSame(4, $question->options()->count());
            $this->assertSame(1, $question->options()->where('is_correct', true)->count());
        }
    }

    public function test_lesson_7_content_does_not_name_the_source_book(): void
    {
        $unit = $this->unit7();
        $cards = Lesson::where('unit_id', $unit->id)->where('category', 'grammar')->firstOrFail()->questions()->get();

        $text = json_encode([$unit->title_id, $unit->title_en, $unit->description_id, $unit->description_en, $cards->pluck('payload')]);

        $this->assertStringNotContainsStringIgnoringCase('minna', $text);
    }
}
