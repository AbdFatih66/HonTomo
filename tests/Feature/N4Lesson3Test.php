<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\Level;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vocabulary;
use Database\Seeders\N4Lesson1Seeder;
use Database\Seeders\N4Lesson3Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * N4 Pelajaran 3 sits next to Pelajaran 1 under the N4 level (?level=N4).
 */
class N4Lesson3Test extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildMiniCurriculum();
        $this->seed(N4Lesson1Seeder::class);
        $this->seed(N4Lesson3Seeder::class);

        Sanctum::actingAs(User::factory()->create());
    }

    private function unit3(): Unit
    {
        return Unit::where('order', 3)->whereHas('level', fn ($q) => $q->where('code', 'N4'))->firstOrFail();
    }

    public function test_seeder_is_idempotent(): void
    {
        $words = Vocabulary::where('jlpt_level', 'N4')->count();
        $questions = LessonQuestion::count();

        $this->seed(N4Lesson3Seeder::class);

        $this->assertSame($words, Vocabulary::where('jlpt_level', 'N4')->count());
        $this->assertSame($questions, LessonQuestion::count());
        $this->assertSame(1, Level::where('code', 'N4')->count());
        $this->assertSame(1, Unit::where('order', 3)->whereHas('level', fn ($q) => $q->where('code', 'N4'))->count());
    }

    public function test_path_lists_pelajaran_1_and_3(): void
    {
        $n4 = $this->getJson('/api/learning-path?level=N4')->assertOk();

        $this->assertCount(2, $n4->json('units'));
        $this->assertSame([1, 3], array_column($n4->json('units'), 'order'));
    }

    public function test_bunpou_lesson_has_five_complete_cards(): void
    {
        $bunpou = Lesson::where('unit_id', $this->unit3()->id)->where('category', 'grammar')->firstOrFail();
        $cards = $bunpou->questions()->orderBy('order')->get();

        $this->assertCount(5, $cards);

        foreach ($cards as $card) {
            $this->assertNotEmpty($card->payload['explanation_id']);
            $this->assertNotEmpty($card->payload['explanation_en']);
            $this->assertNotEmpty($card->payload['notes_id']);
            $this->assertNotEmpty($card->payload['examples']);
            $this->assertNotEmpty($card->payload['dialogue']['lines']);

            foreach ($card->payload['examples'] as $example) {
                $this->assertNotEmpty($example['reading']);
                $this->assertNotEmpty($example['id']);
                $this->assertNotEmpty($example['en']);
            }
        }
    }

    public function test_content_does_not_mention_the_source_book_name(): void
    {
        $unit = $this->unit3();
        $text = json_encode([
            $unit->title_id, $unit->title_en, $unit->description_id, $unit->description_en,
            Lesson::where('unit_id', $unit->id)->get()->toArray(),
            LessonQuestion::whereIn('lesson_id', Lesson::where('unit_id', $unit->id)->pluck('id'))->where('question_type', 'grammar')->get()->toArray(),
        ], JSON_UNESCAPED_UNICODE);

        $this->assertStringNotContainsStringIgnoringCase('minna', $text);
    }

    public function test_kosakata_chapter_3_has_words_and_quiz(): void
    {
        $chapters = collect($this->getJson('/api/vocabulary/chapters?level=N4')->assertOk()->json('data'))->keyBy('order');

        $this->assertTrue($chapters->has(3));
        $this->assertGreaterThan(50, $chapters[3]['count']);
        $this->assertCount(1, $chapters[3]['quiz']);

        $words = $this->getJson('/api/vocabulary?chapter=3&level=N4')->assertOk()->json('data');
        $this->assertCount($chapters[3]['count'], $words);

        // Chapter 1 is unchanged and the N5 chapter 3 list does not contain N4 words.
        $this->assertGreaterThan(40, $chapters[1]['count']);
        $this->assertCount(0, $this->getJson('/api/vocabulary?chapter=3')->assertOk()->json('data'));
    }

    public function test_every_chapter_3_word_has_a_four_option_quiz_question(): void
    {
        $words = Vocabulary::whereHas('category', fn ($q) => $q->where('slug', 'n4-pelajaran-3'))->get();
        $this->assertNotEmpty($words);

        foreach ($words as $word) {
            $question = LessonQuestion::where('vocabulary_id', $word->id)->first();

            $this->assertNotNull($question, "{$word->romaji} has no quiz question");
            $this->assertSame(4, $question->options()->count());
            $this->assertSame(1, $question->options()->where('is_correct', true)->count());
        }
    }
}
