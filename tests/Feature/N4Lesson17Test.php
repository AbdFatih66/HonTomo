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
use Database\Seeders\N4Lesson17Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * N4 Pelajaran 17 (tujuan, kegunaan, pelaku) berdampingan dengan Pelajaran 1 dan 7
 * dan dipilih lewat selektor N5 / N4.
 */
class N4Lesson17Test extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildMiniCurriculum();
        $this->seed(N4Lesson1Seeder::class);
        $this->seed(N4Lesson7Seeder::class);
        $this->seed(N4Lesson17Seeder::class);

        Sanctum::actingAs(User::factory()->create());
    }

    private function unit7(): Unit
    {
        return Unit::where('order', 17)->whereHas('level', fn ($q) => $q->where('code', 'N4'))->firstOrFail();
    }

    public function test_seeder_is_idempotent_and_does_not_touch_other_lessons(): void
    {
        $words = Vocabulary::where('jlpt_level', 'N4')->count();
        $cards = LessonQuestion::where('question_type', 'grammar')->count();
        $quiz = LessonQuestion::where('question_type', 'multiple_choice')->count();

        $this->seed(N4Lesson17Seeder::class);

        $this->assertSame($words, Vocabulary::where('jlpt_level', 'N4')->count());
        $this->assertSame($cards, LessonQuestion::where('question_type', 'grammar')->count());
        $this->assertSame($quiz, LessonQuestion::where('question_type', 'multiple_choice')->count());
        $this->assertSame(1, Level::where('code', 'N4')->count());
        $this->assertSame(3, Unit::whereHas('level', fn ($q) => $q->where('code', 'N4'))->count());
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

    public function test_n4_path_lists_all_three_lessons(): void
    {
        $n4 = $this->getJson('/api/learning-path?level=N4')->assertOk();

        $this->assertCount(3, $n4->json('units'));
    }

    public function test_kosakata_chapter_17_has_words_and_a_quiz(): void
    {
        $chapters = collect($this->getJson('/api/vocabulary/chapters?level=N4')->assertOk()->json('data'));

        $this->assertSame([1, 7, 17], $chapters->pluck('order')->all());

        $seventeen = $chapters->firstWhere('order', 17);
        $this->assertGreaterThan(45, $seventeen['count']);
        $this->assertCount(1, $seventeen['quiz']);

        $words = $this->getJson('/api/vocabulary?chapter=17&level=N4')->assertOk()->json('data');
        $this->assertCount($seventeen['count'], $words);
    }

    public function test_every_lesson_17_word_has_a_four_option_quiz_question(): void
    {
        $words = Vocabulary::whereHas('category', fn ($q) => $q->where('slug', 'n4-pelajaran-17'))->get();

        $this->assertNotEmpty($words);

        foreach ($words as $word) {
            $question = LessonQuestion::where('vocabulary_id', $word->id)->first();

            $this->assertNotNull($question, "{$word->romaji} has no quiz question");
            $this->assertSame(4, $question->options()->count());
            $this->assertSame(1, $question->options()->where('is_correct', true)->count());
        }
    }

    public function test_lesson_17_content_does_not_name_the_source_book(): void
    {
        $unit = $this->unit7();
        $cards = Lesson::where('unit_id', $unit->id)->where('category', 'grammar')->firstOrFail()->questions()->get();

        $text = json_encode([$unit->title_id, $unit->title_en, $unit->description_id, $unit->description_en, $cards->pluck('payload')]);

        $this->assertStringNotContainsStringIgnoringCase('minna', $text);
    }
}
