<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\Level;
use App\Models\User;
use App\Models\Vocabulary;
use Database\Seeders\N4Lesson1Seeder;
use Database\Seeders\N4Lesson4Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * N4 Pelajaran 4 ("Barang Saya Tertinggal") lives in unit order 4 of the N4
 * level and is reached with the N5 / N4 selector (?level=N4).
 */
class N4Lesson4Test extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildMiniCurriculum();
        $this->seed(N4Lesson1Seeder::class);
        $this->seed(N4Lesson4Seeder::class);

        Sanctum::actingAs(User::factory()->create());
    }

    private function bunpou(): Lesson
    {
        return Lesson::where('category', 'grammar')
            ->whereHas('unit', fn ($q) => $q->where('order', 4)->whereHas('level', fn ($l) => $l->where('code', 'N4')))
            ->firstOrFail();
    }

    public function test_seeder_is_idempotent(): void
    {
        $words = Vocabulary::where('jlpt_level', 'N4')->count();
        $cards = LessonQuestion::where('question_type', 'grammar')->count();
        $quiz = LessonQuestion::where('question_type', 'multiple_choice')->whereHas('vocabulary', fn ($q) => $q->where('jlpt_level', 'N4'))->count();

        $this->seed(N4Lesson4Seeder::class);

        $this->assertSame($words, Vocabulary::where('jlpt_level', 'N4')->count());
        $this->assertSame($cards, LessonQuestion::where('question_type', 'grammar')->count());
        $this->assertSame($quiz, LessonQuestion::where('question_type', 'multiple_choice')->whereHas('vocabulary', fn ($q) => $q->where('jlpt_level', 'N4'))->count());
        $this->assertSame(1, Level::where('code', 'N4')->count());
    }

    public function test_n4_level_keeps_one_row_with_both_units(): void
    {
        $this->assertSame(2, Level::where('code', 'N4')->first()->units()->count());
    }

    public function test_bunpou_lesson_has_six_cards_with_examples_and_dialogue(): void
    {
        $cards = $this->bunpou()->questions()->orderBy('order')->get();

        $this->assertCount(6, $cards);

        foreach ($cards as $card) {
            $this->assertNotEmpty($card->payload['explanation_id']);
            $this->assertNotEmpty($card->payload['explanation_en']);
            $this->assertNotEmpty($card->payload['examples']);
            $this->assertNotEmpty($card->payload['dialogue']['lines']);
        }
    }

    public function test_lesson_text_does_not_name_the_source_book(): void
    {
        $json = json_encode($this->bunpou()->questions()->pluck('payload'), JSON_UNESCAPED_UNICODE);

        $this->assertStringNotContainsStringIgnoringCase('minna', $json);
    }

    public function test_kosakata_chapter_4_lists_fifty_words_and_a_quiz(): void
    {
        $chapters = collect($this->getJson('/api/vocabulary/chapters?level=N4')->assertOk()->json('data'))->keyBy('order');

        $this->assertTrue($chapters->has(4));
        $this->assertSame(50, $chapters[4]['count']);
        $this->assertCount(1, $chapters[4]['quiz']);

        $words = $this->getJson('/api/vocabulary?chapter=4&level=N4')->assertOk()->json('data');
        $this->assertCount(50, $words);
    }

    public function test_every_lesson_4_word_has_a_four_option_quiz_question(): void
    {
        foreach (Vocabulary::whereHas('category', fn ($q) => $q->where('slug', 'n4-pelajaran-4'))->get() as $word) {
            $question = LessonQuestion::where('vocabulary_id', $word->id)->first();

            $this->assertNotNull($question, "{$word->romaji} has no quiz question");
            $this->assertSame(4, $question->options()->count());
            $this->assertSame(1, $question->options()->where('is_correct', true)->count());
        }
    }
}
