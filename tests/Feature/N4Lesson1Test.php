<?php

namespace Tests\Feature;

use App\Models\Kanji;
use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\Level;
use App\Models\User;
use App\Models\UserLesson;
use App\Models\Vocabulary;
use Database\Seeders\KanjiSeeder;
use Database\Seeders\N4Lesson1Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * N4 Pelajaran 1 sits next to N5 and is chosen with
 * the N5 / N4 selector (?level=N4) on Tata Bahasa and Kosakata.
 */
class N4Lesson1Test extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildMiniCurriculum(); // an N5 level with Pelajaran 1 (order 1) already exists
        $this->seed(N4Lesson1Seeder::class);

        Sanctum::actingAs(User::factory()->create());
    }

    public function test_seeder_is_idempotent(): void
    {
        $words = Vocabulary::where('jlpt_level', 'N4')->count();
        $cards = LessonQuestion::where('question_type', 'grammar')->count();
        $quiz = LessonQuestion::where('question_type', 'multiple_choice')->whereHas('vocabulary', fn ($q) => $q->where('jlpt_level', 'N4'))->count();

        $this->seed(N4Lesson1Seeder::class);

        $this->assertSame($words, Vocabulary::where('jlpt_level', 'N4')->count());
        $this->assertSame($cards, LessonQuestion::where('question_type', 'grammar')->count());
        $this->assertSame($quiz, LessonQuestion::where('question_type', 'multiple_choice')->whereHas('vocabulary', fn ($q) => $q->where('jlpt_level', 'N4'))->count());
        $this->assertSame(1, Level::where('code', 'N4')->count());
    }

    public function test_n4_path_has_the_bunpou_lesson_and_n5_is_untouched(): void
    {
        $n4 = $this->getJson('/api/learning-path?level=N4')->assertOk();

        $this->assertSame('N4', $n4->json('level.code'));
        $this->assertCount(1, $n4->json('units'));
        $this->assertSame('grammar', $n4->json('units.0.lessons.0.category'));

        // N5 keeps working: the mini curriculum only has vocabulary lessons, so the grammar-only path is empty.
        $n5 = $this->getJson('/api/learning-path?level=N5')->assertOk();
        $this->assertSame('N5', $n5->json('level.code'));
        $this->assertCount(0, $n5->json('units'));
    }

    public function test_bunpou_lesson_has_six_cards_with_examples_and_dialogue(): void
    {
        $bunpou = Lesson::where('category', 'grammar')->whereHas('unit.level', fn ($q) => $q->where('code', 'N4'))->firstOrFail();
        $cards = $bunpou->questions()->orderBy('order')->get();

        $this->assertCount(6, $cards);

        foreach ($cards as $card) {
            $this->assertNotEmpty($card->payload['explanation_id']);
            $this->assertNotEmpty($card->payload['explanation_en']);
            $this->assertNotEmpty($card->payload['examples']);
            $this->assertNotEmpty($card->payload['dialogue']['lines']);
        }
    }

    public function test_kosakata_chapters_and_words_follow_the_level(): void
    {
        $chapters = $this->getJson('/api/vocabulary/chapters?level=N4')->assertOk()->json('data');

        $this->assertCount(1, $chapters);
        $this->assertSame(1, $chapters[0]['order']);
        $this->assertGreaterThan(40, $chapters[0]['count']);
        $this->assertCount(1, $chapters[0]['quiz']);

        $words = $this->getJson('/api/vocabulary?chapter=1&level=N4')->assertOk()->json('data');
        $this->assertCount($chapters[0]['count'], $words);

        // The N5 chapter 1 list must not contain N4 words.
        $n5 = $this->getJson('/api/vocabulary?chapter=1')->assertOk()->json('data');
        $this->assertCount(0, $n5);
    }

    public function test_every_n4_word_has_a_four_option_quiz_question(): void
    {
        foreach (Vocabulary::where('jlpt_level', 'N4')->get() as $word) {
            $question = LessonQuestion::where('vocabulary_id', $word->id)->first();

            $this->assertNotNull($question, "{$word->romaji} has no quiz question");
            $this->assertSame(4, $question->options()->count());
            $this->assertSame(1, $question->options()->where('is_correct', true)->count());
        }
    }

    public function test_finishing_an_n4_lesson_does_not_mark_n5_kanji_as_met(): void
    {
        $this->seed(KanjiSeeder::class);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        [$n5Kanji, $n4Kanji] = Kanji::take(2)->get();
        $n5Kanji->forceFill(['earliest_lesson_order' => 1 * 100000 + 25 * 1000 + 1])->save(); // an N5 lesson the user never did
        $n4Kanji->forceFill(['earliest_lesson_order' => 2 * 100000 + 1 * 1000 + 1])->save();  // the N4 vocabulary lesson

        $n4Vocab = Lesson::where('category', 'vocabulary')->whereHas('unit.level', fn ($q) => $q->where('code', 'N4'))->firstOrFail();
        UserLesson::create(['user_id' => $user->id, 'lesson_id' => $n4Vocab->id, 'status' => UserLesson::STATUS_COMPLETED]);

        $ids = collect($this->getJson('/api/kanji?scope=learned&per_page=100')->assertOk()->json('data'))->pluck('id');

        $this->assertTrue($ids->contains($n4Kanji->id));
        $this->assertFalse($ids->contains($n5Kanji->id));
    }
}
