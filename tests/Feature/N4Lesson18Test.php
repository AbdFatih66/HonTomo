<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\Level;
use App\Models\User;
use App\Models\Vocabulary;
use Database\Seeders\N4Lesson1Seeder;
use Database\Seeders\N4Lesson18Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/** N4 Pelajaran 18 (〜そうです, 〜て来ます, 〜てくれませんか) sits next to Pelajaran 1 under the N4 level. */
class N4Lesson18Test extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildMiniCurriculum();
        $this->seed(N4Lesson1Seeder::class);
        $this->seed(N4Lesson18Seeder::class);

        Sanctum::actingAs(User::factory()->create());
    }

    public function test_seeder_is_idempotent(): void
    {
        $words = Vocabulary::whereHas('category', fn ($q) => $q->where('slug', 'n4-pelajaran-18'))->count();
        $cards = LessonQuestion::where('question_type', 'grammar')->count();

        $this->seed(N4Lesson18Seeder::class);

        $this->assertSame($words, Vocabulary::whereHas('category', fn ($q) => $q->where('slug', 'n4-pelajaran-18'))->count());
        $this->assertSame($cards, LessonQuestion::where('question_type', 'grammar')->count());
        $this->assertSame(1, Level::where('code', 'N4')->count());
    }

    public function test_bunpou_lesson_has_five_cards(): void
    {
        $bunpou = Lesson::where('category', 'grammar')
            ->whereHas('unit', fn ($q) => $q->where('order', 18)->whereHas('level', fn ($l) => $l->where('code', 'N4')))
            ->firstOrFail();
        $cards = $bunpou->questions()->orderBy('order')->get();

        $this->assertCount(5, $cards);

        foreach ($cards as $card) {
            $this->assertNotEmpty($card->payload['explanation_id']);
            $this->assertNotEmpty($card->payload['explanation_en']);
            $this->assertNotEmpty($card->payload['examples']);
            $this->assertNotEmpty($card->payload['dialogue']['lines']);
        }
    }

    public function test_kosakata_chapter_8_lists_words_and_has_a_quiz(): void
    {
        $chapters = collect($this->getJson('/api/vocabulary/chapters?level=N4')->assertOk()->json('data'));
        $chapter = $chapters->firstWhere('order', 18);

        $this->assertNotNull($chapter);
        $this->assertGreaterThan(20, $chapter['count']);
        $this->assertCount(1, $chapter['quiz']);

        $words = $this->getJson('/api/vocabulary?chapter=18&level=N4')->assertOk()->json('data');
        $this->assertCount($chapter['count'], $words);
    }

    public function test_every_word_has_a_four_option_quiz_question(): void
    {
        $words = Vocabulary::whereHas('category', fn ($q) => $q->where('slug', 'n4-pelajaran-18'))->get();

        foreach ($words as $word) {
            $question = LessonQuestion::where('vocabulary_id', $word->id)->first();

            $this->assertNotNull($question, "{$word->romaji} has no quiz question");
            $this->assertSame(4, $question->options()->count());
            $this->assertSame(1, $question->options()->where('is_correct', true)->count());
        }
    }
}
