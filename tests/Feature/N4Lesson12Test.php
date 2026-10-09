<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\User;
use App\Models\Vocabulary;
use App\Models\VocabularyCategory;
use Database\Seeders\N4Lesson1Seeder;
use Database\Seeders\N4Lesson12Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * N4 Pelajaran 12 is a unit of the N4 level (order 12), next to Pelajaran 1 and 2.
 */
class N4Lesson12Test extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildMiniCurriculum();
        $this->seed(N4Lesson1Seeder::class);
        $this->seed(N4Lesson12Seeder::class);

        Sanctum::actingAs(User::factory()->create());
    }

    private function lesson2Words()
    {
        $category = VocabularyCategory::where('slug', 'n4-pelajaran-12')->firstOrFail();

        return Vocabulary::where('jlpt_level', 'N4')->where('category_id', $category->id)->get();
    }

    public function test_seeder_is_idempotent(): void
    {
        $words = Vocabulary::where('jlpt_level', 'N4')->count();
        $cards = LessonQuestion::where('question_type', 'grammar')->count();
        $quiz = LessonQuestion::where('question_type', 'multiple_choice')->whereHas('vocabulary', fn ($q) => $q->where('jlpt_level', 'N4'))->count();

        $this->seed(N4Lesson12Seeder::class);

        $this->assertSame($words, Vocabulary::where('jlpt_level', 'N4')->count());
        $this->assertSame($cards, LessonQuestion::where('question_type', 'grammar')->count());
        $this->assertSame($quiz, LessonQuestion::where('question_type', 'multiple_choice')->whereHas('vocabulary', fn ($q) => $q->where('jlpt_level', 'N4'))->count());
    }

    public function test_n4_path_lists_both_units_in_order(): void
    {
        $n4 = $this->getJson('/api/learning-path?level=N4')->assertOk();

        $this->assertCount(2, $n4->json('units'));
        $this->assertSame('grammar', $n4->json('units.1.lessons.0.category'));
    }

    public function test_bunpou_lesson_has_six_cards_with_examples_and_dialogue(): void
    {
        $bunpou = Lesson::where('category', 'grammar')
            ->whereHas('unit', fn ($q) => $q->where('order', 12)->whereHas('level', fn ($l) => $l->where('code', 'N4')))
            ->firstOrFail();
        $cards = $bunpou->questions()->orderBy('order')->get();

        $this->assertCount(6, $cards);

        foreach ($cards as $card) {
            $this->assertNotEmpty($card->payload['explanation_id']);
            $this->assertNotEmpty($card->payload['explanation_en']);
            $this->assertNotEmpty($card->payload['examples']);
            $this->assertNotEmpty($card->payload['dialogue']['lines']);
        }
    }

    public function test_kosakata_chapter_two_has_words_and_a_quiz(): void
    {
        $chapters = collect($this->getJson('/api/vocabulary/chapters?level=N4')->assertOk()->json('data'));
        $chapter = $chapters->firstWhere('order', 12);

        $this->assertNotNull($chapter);
        $this->assertGreaterThan(40, $chapter['count']);
        $this->assertCount(1, $chapter['quiz']);

        $words = $this->getJson('/api/vocabulary?chapter=12&level=N4')->assertOk()->json('data');
        $this->assertCount($chapter['count'], $words);
    }

    public function test_every_lesson_two_word_has_a_four_option_quiz_question(): void
    {
        foreach ($this->lesson2Words() as $word) {
            $question = LessonQuestion::where('vocabulary_id', $word->id)->first();

            $this->assertNotNull($question, "{$word->romaji} has no quiz question");
            $this->assertSame(4, $question->options()->count());
            $this->assertSame(1, $question->options()->where('is_correct', true)->count());
        }
    }
}
