<?php

namespace Tests\Feature;

use App\Models\LessonQuestion;
use App\Models\Level;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vocabulary;
use Database\Seeders\N3Lesson1Seeder;
use Database\Seeders\N3Lesson2Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * Kosakata N3 (Pelajaran 1-2) hidup di samping N5 / N4 (?level=N3).
 */
class N3VocabularyTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildMiniCurriculum();
        $this->seed(N3Lesson1Seeder::class);
        $this->seed(N3Lesson2Seeder::class);

        Sanctum::actingAs(User::factory()->create());
    }

    public function test_seeders_are_idempotent(): void
    {
        $words = Vocabulary::where('jlpt_level', 'N3')->count();
        $questions = LessonQuestion::count();

        $this->seed(N3Lesson1Seeder::class);
        $this->seed(N3Lesson2Seeder::class);

        $this->assertSame($words, Vocabulary::where('jlpt_level', 'N3')->count());
        $this->assertSame($questions, LessonQuestion::count());
        $this->assertSame(1, Level::where('code', 'N3')->count());
        $this->assertSame(2, Unit::whereHas('level', fn ($q) => $q->where('code', 'N3'))->count());
    }

    public function test_chapters_menu_lists_both_lessons_with_a_quiz(): void
    {
        $chapters = collect($this->getJson('/api/vocabulary/chapters?level=N3')->assertOk()->json('data'))->keyBy('order');

        $this->assertSame([1, 2], $chapters->keys()->all());
        $this->assertGreaterThan(0, $chapters[1]['count']);
        $this->assertGreaterThan(0, $chapters[2]['count']);
        $this->assertCount(1, $chapters[1]['quiz']);
        $this->assertSame($chapters[2]['count'], $chapters[2]['quiz'][0]['question_count']);
    }

    public function test_chapter_word_list_only_returns_n3_words(): void
    {
        $words = $this->getJson('/api/vocabulary?chapter=1&level=N3')->assertOk()->json('data');

        $this->assertNotEmpty($words);
        $this->assertContains('湖', array_column($words, 'japanese'));
    }

    public function test_every_n3_word_has_a_quiz_question(): void
    {
        foreach (Vocabulary::where('jlpt_level', 'N3')->get() as $word) {
            $question = LessonQuestion::where('vocabulary_id', $word->id)->first();

            $this->assertNotNull($question, "{$word->romaji} has no quiz question");
            $this->assertSame(1, $question->options()->where('is_correct', true)->count());
        }
    }
}
