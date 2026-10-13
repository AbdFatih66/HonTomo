<?php

namespace Tests\Feature\Regression;

use App\Models\Kanji;
use App\Models\KanaCharacter;
use Database\Seeders\KanaSeeder;
use Database\Seeders\KanjiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The auth work must not have broken Hiragana / Katakana / Kanji.
 */
class KanaKanjiRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([KanaSeeder::class, KanjiSeeder::class]);
    }

    /** A brand-new user, straight from the registration endpoint. */
    private function newUserToken(): string
    {
        return $this->postJson('/api/register', [
            'name' => 'Murid Baru', 'email' => 'murid@example.com',
            'password' => 'Sakura2026', 'password_confirmation' => 'Sakura2026',
        ])->assertCreated()->json('token');
    }

    public function test_kana_endpoints_still_require_authentication(): void
    {
        $this->getJson('/api/kana')->assertUnauthorized();
        $this->getJson('/api/kana/quiz')->assertUnauthorized();
        $this->getJson('/api/kana/1')->assertUnauthorized();
    }

    public function test_hiragana_and_katakana_charts_are_complete(): void
    {
        $token = $this->newUserToken();

        foreach (['hiragana', 'katakana'] as $script) {
            $this->withToken($token)->getJson("/api/kana?script={$script}")
                ->assertOk()
                ->assertJsonPath('script', $script)
                ->assertJsonCount(46, 'gojuon')
                ->assertJsonCount(20, 'dakuten')
                ->assertJsonCount(5, 'handakuten')
                ->assertJsonCount(21, 'yoon')
                ->assertJsonCount(15, 'yoon_dakuten');
        }

        $this->assertSame(214, KanaCharacter::count());
    }

    public function test_a_single_kana_character_can_be_opened(): void
    {
        $token = $this->newUserToken();
        $kana = KanaCharacter::first();

        $this->withToken($token)->getJson("/api/kana/{$kana->id}")->assertOk();
    }

    public function test_the_kana_quiz_still_builds_questions(): void
    {
        $token = $this->newUserToken();

        $response = $this->withToken($token)->getJson('/api/kana/quiz?script=hiragana&count=8')->assertOk();

        $questions = $response->json('questions');
        $this->assertNotEmpty($questions);
        $this->assertLessThanOrEqual(8, count($questions));
    }

    public function test_kanji_data_is_intact(): void
    {
        $this->assertGreaterThan(0, Kanji::count());
        $this->assertSame(0, Kanji::whereNull('character')->orWhere('character', '')->count());
    }
}
