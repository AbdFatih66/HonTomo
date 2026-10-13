<?php

namespace Tests\Feature\Regression;

use App\Models\LessonQuestion;
use App\Models\Vocabulary;
use App\Models\VocabularyCategory;
use App\Services\VocabularyQuizSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * The Kosakata list and the vocabulary quiz must hold the same words.
 */
class VocabularyQuizSyncTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private function words(string $slug, array $rows): void
    {
        $category = VocabularyCategory::firstOrCreate(['slug' => $slug], ['name_id' => $slug, 'name_en' => $slug]);

        foreach ($rows as [$jp, $meaning]) {
            Vocabulary::create([
                'japanese' => $jp, 'hiragana' => $jp, 'romaji' => $jp, 'meaning_id' => $meaning,
                'meaning_en' => $meaning, 'category_id' => $category->id, 'jlpt_level' => 'N5',
                'difficulty' => 1, 'is_active' => true,
            ]);
        }
    }

    public function test_every_listed_word_gets_a_quiz_question(): void
    {
        $this->buildMiniCurriculum(); // Pelajaran 1: two vocabulary lessons

        $this->words('people-professions', [['a1', 'm1'], ['a2', 'm2'], ['a3', 'm3'], ['a4', 'm4'], ['a5', 'm5']]);
        $this->words('pelajaran-1-ungkapan', [['u1', 'n1'], ['u2', 'n2']]);
        $this->words('countries', [['c1', 'k1'], ['c2', 'k2'], ['c3', 'k3'], ['c4', 'k4']]);

        $summary = app(VocabularyQuizSync::class)->run();

        $this->assertSame(11, $summary['questions_added']);

        foreach (Vocabulary::all() as $word) {
            $question = LessonQuestion::where('vocabulary_id', $word->id)->first();

            $this->assertNotNull($question, "{$word->japanese} has no quiz question");
            $this->assertSame(4, $question->options()->count());
            $this->assertSame(1, $question->options()->where('is_correct', true)->count());
        }

        // re-running adds nothing
        $this->assertSame(0, app(VocabularyQuizSync::class)->run()['questions_added']);
    }

    public function test_a_quiz_only_word_is_added_to_the_kosakata_list(): void
    {
        $this->buildMiniCurriculum();
        $this->words('people-professions', [['a1', 'm1'], ['a2', 'm2'], ['a3', 'm3'], ['a4', 'm4']]);

        $orphan = LessonQuestion::create([
            'lesson_id' => $this->firstLesson->id, 'question_type' => 'multiple_choice',
            'prompt_id' => 'Apa arti kata ini?', 'prompt_en' => 'What does this word mean?',
            'japanese_text' => 'ねこ', 'romaji' => 'neko', 'correct_answer' => 'kucing',
            'order' => 99, 'is_active' => true,
        ]);

        app(VocabularyQuizSync::class)->run();

        $word = Vocabulary::where('japanese', 'ねこ')->first();
        $this->assertNotNull($word);
        $this->assertSame('kucing', $word->meaning_id);
        $this->assertSame($word->id, $orphan->fresh()->vocabulary_id);

        $user = \App\Models\User::factory()->create();
        $this->app['auth']->forgetGuards();
        $ids = $this->withToken($user->createToken('spa')->plainTextToken)
            ->getJson('/api/vocabulary?chapter=1')->assertOk()->json('data.*.id');

        $this->assertContains($word->id, $ids);
    }

    public function test_the_ungkapan_words_are_part_of_the_kosakata_chapter(): void
    {
        $this->buildMiniCurriculum();
        $this->words('pelajaran-1-ungkapan', [['u1', 'n1']]);

        $user = \App\Models\User::factory()->create();
        $this->app['auth']->forgetGuards();

        $this->withToken($user->createToken('spa')->plainTextToken)
            ->getJson('/api/vocabulary?chapter=1')->assertOk()->assertJsonCount(1, 'data');
    }
}
