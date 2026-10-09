<?php

namespace Tests\Feature\Regression;

use App\Models\Kanji;
use Database\Seeders\KanjiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bug: "Hanya yang sudah dipelajari" (scope=learned) on the kanji grid came
 * back empty even for a user who had genuinely practiced kanji (answered
 * quiz questions / traced characters on the kanji detail screen), because
 * the filter only ever looked at kanji linked to a COMPLETED formal Lesson
 * (kanji_word_links + earliest_lesson_order), never at the user's own
 * UserKanjiProgress rows from direct practice. On a fresh install where
 * kanji:link-vocabulary / kanji:sync-lesson-order haven't been run, or
 * where the user never finished a full Lesson, this was guaranteed empty
 * regardless of how much kanji they'd actually practiced.
 */
class KanjiLearnedScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(KanjiSeeder::class);
    }

    private function newUserToken(): string
    {
        return $this->postJson('/api/register', [
            'name' => 'Murid Baru', 'email' => 'murid@example.com',
            'password' => 'Sakura2026', 'password_confirmation' => 'Sakura2026',
        ])->assertCreated()->json('token');
    }

    public function test_directly_practiced_kanji_counts_as_learned_even_with_no_lesson_link(): void
    {
        $token = $this->newUserToken();
        $kanji = Kanji::first();

        // No kanji_word_links / earliest_lesson_order has been synced at all
        // (fresh install), and the user has not completed any Lesson either
        // — the old code guaranteed an empty result here.
        $this->assertNull($kanji->earliest_lesson_order);

        // The user answers one quiz question about this kanji, the same way
        // the kanji quiz / writing-practice screen does.
        $this->withToken($token)
            ->postJson("/api/kanji/{$kanji->id}/progress", ['correct' => true])
            ->assertOk();

        $response = $this->withToken($token)
            ->getJson('/api/kanji?scope=learned')
            ->assertOk();

        $response->assertJsonFragment(['id' => $kanji->id]);
        $this->assertTrue($response->json('learned_meta.has_progress'));
    }

    public function test_learned_scope_is_genuinely_empty_with_no_progress_of_either_kind(): void
    {
        $token = $this->newUserToken();

        $response = $this->withToken($token)
            ->getJson('/api/kanji?scope=learned')
            ->assertOk();

        $this->assertSame([], $response->json('data'));
        $this->assertFalse($response->json('learned_meta.has_progress'));
    }
}
