<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\Level;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vocabulary;
use App\Models\VocabularyCategory;
use Database\Seeders\N4Lesson15Seeder;
use Database\Seeders\N4Lesson1Seeder;
use Database\Seeders\N4Lesson6Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * N4 Pelajaran 15 (kalimat tanya sisipan, 〜てみます, 〜さ, 〜でしょうか).
 * Unit.order 2-5 and 7-14 do not exist yet; the gaps must not break the N4 pages.
 */
class N4Lesson15Test extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildMiniCurriculum();
        $this->seed(N4Lesson1Seeder::class);
        $this->seed(N4Lesson6Seeder::class);
        $this->seed(N4Lesson15Seeder::class);

        Sanctum::actingAs(User::factory()->create());
    }

    private function categoryId(): int
    {
        return VocabularyCategory::where('slug', 'n4-pelajaran-15')->firstOrFail()->id;
    }

    public function test_seeder_is_idempotent(): void
    {
        $words = Vocabulary::where('jlpt_level', 'N4')->count();
        $cards = LessonQuestion::where('question_type', 'grammar')->count();
        $quiz = LessonQuestion::where('question_type', 'multiple_choice')->whereHas('vocabulary', fn ($q) => $q->where('jlpt_level', 'N4'))->count();

        $this->seed(N4Lesson15Seeder::class);

        $this->assertSame($words, Vocabulary::where('jlpt_level', 'N4')->count());
        $this->assertSame($cards, LessonQuestion::where('question_type', 'grammar')->count());
        $this->assertSame($quiz, LessonQuestion::where('question_type', 'multiple_choice')->whereHas('vocabulary', fn ($q) => $q->where('jlpt_level', 'N4'))->count());
        $this->assertSame(1, Level::where('code', 'N4')->count());
        $this->assertSame(1, Unit::where('order', 15)->whereHas('level', fn ($q) => $q->where('code', 'N4'))->count());
    }

    public function test_n4_path_lists_lessons_1_6_and_15(): void
    {
        $n4 = $this->getJson('/api/learning-path?level=N4')->assertOk();

        $this->assertCount(3, $n4->json('units'));
        $this->assertSame('grammar', $n4->json('units.2.lessons.0.category'));
    }

    public function test_bunpou_lesson_has_five_cards_with_examples_and_dialogue(): void
    {
        $unit = Unit::where('order', 15)->whereHas('level', fn ($q) => $q->where('code', 'N4'))->firstOrFail();
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

    public function test_kosakata_chapter_15_has_words_and_a_quiz(): void
    {
        $chapters = collect($this->getJson('/api/vocabulary/chapters?level=N4')->assertOk()->json('data'));

        $this->assertSame([1, 6, 15], $chapters->pluck('order')->all());

        $fifteen = $chapters->firstWhere('order', 15);
        $this->assertGreaterThan(50, $fifteen['count']);
        $this->assertCount(1, $fifteen['quiz']);

        $words = $this->getJson('/api/vocabulary?chapter=15&level=N4')->assertOk()->json('data');
        $this->assertCount($fifteen['count'], $words);
    }

    public function test_every_lesson_15_word_has_a_four_option_quiz_question(): void
    {
        foreach (Vocabulary::where('category_id', $this->categoryId())->get() as $word) {
            $question = LessonQuestion::where('vocabulary_id', $word->id)->first();

            $this->assertNotNull($question, "{$word->romaji} has no quiz question");
            $this->assertSame(4, $question->options()->count());
            $this->assertSame(1, $question->options()->where('is_correct', true)->count());
        }
    }
}
