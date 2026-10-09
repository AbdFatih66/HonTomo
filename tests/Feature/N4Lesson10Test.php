<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\Level;
use App\Models\User;
use App\Models\Vocabulary;
use Database\Seeders\N4Lesson10Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * N4 Pelajaran 10 (bentuk syarat 〜ば／〜なら) sits next to N5 and is chosen
 * with the N5 / N4 selector (?level=N4) on Tata Bahasa and Kosakata.
 */
class N4Lesson10Test extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildMiniCurriculum();
        $this->seed(N4Lesson10Seeder::class);

        Sanctum::actingAs(User::factory()->create());
    }

    private function n4Words()
    {
        return Vocabulary::where('jlpt_level', 'N4')
            ->whereHas('category', fn ($q) => $q->where('slug', 'n4-pelajaran-10'));
    }

    public function test_seeder_is_idempotent(): void
    {
        $words = $this->n4Words()->count();
        $cards = LessonQuestion::where('question_type', 'grammar')->count();
        $quiz = LessonQuestion::where('question_type', 'multiple_choice')->count();

        $this->seed(N4Lesson10Seeder::class);

        $this->assertSame($words, $this->n4Words()->count());
        $this->assertSame($cards, LessonQuestion::where('question_type', 'grammar')->count());
        $this->assertSame($quiz, LessonQuestion::where('question_type', 'multiple_choice')->count());
        $this->assertSame(1, Level::where('code', 'N4')->count());
    }

    public function test_n4_path_has_the_bunpou_lesson(): void
    {
        $n4 = $this->getJson('/api/learning-path?level=N4')->assertOk();

        $this->assertSame('N4', $n4->json('level.code'));
        $this->assertCount(1, $n4->json('units'));
        $this->assertSame('grammar', $n4->json('units.0.lessons.0.category'));
    }

    public function test_bunpou_lesson_has_seven_cards_with_examples_and_dialogue(): void
    {
        $bunpou = Lesson::where('category', 'grammar')->whereHas('unit.level', fn ($q) => $q->where('code', 'N4'))->firstOrFail();
        $cards = $bunpou->questions()->orderBy('order')->get();

        $this->assertCount(7, $cards);

        foreach ($cards as $card) {
            $this->assertNotEmpty($card->payload['explanation_id']);
            $this->assertNotEmpty($card->payload['explanation_en']);
            $this->assertNotEmpty($card->payload['examples']);
            $this->assertNotEmpty($card->payload['dialogue']['lines']);
        }
    }

    public function test_kosakata_chapter_ten_has_words_and_a_quiz(): void
    {
        $chapters = $this->getJson('/api/vocabulary/chapters?level=N4')->assertOk()->json('data');

        $this->assertCount(1, $chapters);
        $this->assertSame(10, $chapters[0]['order']);
        $this->assertGreaterThan(40, $chapters[0]['count']);
        $this->assertCount(1, $chapters[0]['quiz']);

        $words = $this->getJson('/api/vocabulary?chapter=10&level=N4')->assertOk()->json('data');
        $this->assertCount($chapters[0]['count'], $words);
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
}
