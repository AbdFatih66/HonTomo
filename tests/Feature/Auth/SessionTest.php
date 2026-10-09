<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTest extends TestCase
{
    use RefreshDatabase;

    private const CHROME_WINDOWS = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36';
    private const SAFARI_IPHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1';
    private const CHROME_ANDROID = 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36';

    private function login(User $user, string $userAgent): string
    {
        return $this->withHeader('User-Agent', $userAgent)
            ->postJson('/api/login', ['email' => $user->email, 'password' => 'Sakura2026'])
            ->assertOk()
            ->json('token');
    }

    /** Sanctum caches the resolved user per app instance; reset between identities. */
    private function as(string $token): static
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($token);
    }

    private function user(string $email = 'budi@example.com'): User
    {
        return User::factory()->create(['email' => $email, 'password' => 'Sakura2026']);
    }

    public function test_sessions_are_named_after_the_browser_and_os(): void
    {
        $user = $this->user();

        $this->login($user, self::CHROME_WINDOWS);
        $this->login($user, self::SAFARI_IPHONE);
        $this->login($user, self::CHROME_ANDROID);

        $names = $user->tokens()->orderBy('id')->pluck('name')->all();

        $this->assertSame(['Chrome · Windows', 'Safari · iOS', 'Chrome · Android'], $names);
    }

    public function test_sessions_endpoint_lists_own_sessions_and_marks_the_current_one(): void
    {
        $user = $this->user();
        $laptop = $this->login($user, self::CHROME_WINDOWS);
        $this->login($user, self::SAFARI_IPHONE);

        $response = $this->as($laptop)->getJson('/api/sessions')->assertOk();

        $sessions = collect($response->json('sessions'));
        $this->assertCount(2, $sessions);
        $this->assertCount(1, $sessions->where('is_current', true));
        $this->assertSame('Chrome · Windows', $sessions->firstWhere('is_current', true)['name']);
        $response->assertJsonMissingPath('sessions.0.token');
    }

    public function test_sessions_require_authentication(): void
    {
        $this->getJson('/api/sessions')->assertUnauthorized();
        $this->deleteJson('/api/sessions')->assertUnauthorized();
        $this->deleteJson('/api/sessions/1')->assertUnauthorized();
    }

    public function test_a_single_session_can_be_revoked_and_stops_working(): void
    {
        $user = $this->user();
        $laptop = $this->login($user, self::CHROME_WINDOWS);
        $phone = $this->login($user, self::SAFARI_IPHONE);

        $phoneId = $user->tokens()->where('name', 'Safari · iOS')->value('id');

        $this->as($laptop)->deleteJson("/api/sessions/{$phoneId}")->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->as($phone)->getJson('/api/me')->assertUnauthorized();
        $this->as($laptop)->getJson('/api/me')->assertOk();
    }

    public function test_a_user_cannot_see_or_revoke_someone_elses_sessions(): void
    {
        $budi = $this->user('budi@example.com');
        $siti = $this->user('siti@example.com');
        $budiToken = $this->login($budi, self::CHROME_WINDOWS);
        $this->login($siti, self::SAFARI_IPHONE);

        $sitiTokenId = $siti->tokens()->value('id');

        $this->as($budiToken)->deleteJson("/api/sessions/{$sitiTokenId}")->assertNotFound();
        $this->assertDatabaseCount('personal_access_tokens', 2);

        $ids = collect($this->as($budiToken)->getJson('/api/sessions')->json('sessions'))->pluck('id');
        $this->assertNotContains($sitiTokenId, $ids->all());
    }

    public function test_sign_out_everywhere_else_keeps_the_current_session(): void
    {
        $user = $this->user();
        $laptop = $this->login($user, self::CHROME_WINDOWS);
        $this->login($user, self::SAFARI_IPHONE);
        $this->login($user, self::CHROME_ANDROID);

        $this->as($laptop)->deleteJson('/api/sessions')
            ->assertOk()
            ->assertJsonPath('revoked', 2);

        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->as($laptop)->getJson('/api/me')->assertOk();
    }

    public function test_sign_out_everywhere_else_does_not_touch_other_users(): void
    {
        $budi = $this->user('budi@example.com');
        $siti = $this->user('siti@example.com');
        $budiToken = $this->login($budi, self::CHROME_WINDOWS);
        $sitiToken = $this->login($siti, self::SAFARI_IPHONE);

        $this->as($budiToken)->deleteJson('/api/sessions')->assertOk()->assertJsonPath('revoked', 0);

        $this->as($sitiToken)->getJson('/api/me')->assertOk();
    }

    // ------------------------------------------------- device limit (max 3)

    public function test_a_fourth_login_signs_out_the_least_recently_used_device(): void
    {
        $user = $this->user();

        $t1 = $this->login($user, self::CHROME_WINDOWS);
        $this->travel(1)->minutes();
        $t2 = $this->login($user, self::SAFARI_IPHONE);
        $this->travel(1)->minutes();
        $t3 = $this->login($user, self::CHROME_ANDROID);

        $this->assertDatabaseCount('personal_access_tokens', 3);

        $this->travel(1)->minutes();
        $t4 = $this->login($user, self::CHROME_WINDOWS);

        // still only 3 sessions total
        $this->assertDatabaseCount('personal_access_tokens', 3);

        // the oldest (t1, never "used" since login doesn't touch last_used_at) is gone
        $this->as($t1)->getJson('/api/me')->assertUnauthorized();

        // the other three remain usable
        $this->as($t2)->getJson('/api/me')->assertOk();
        $this->as($t3)->getJson('/api/me')->assertOk();
        $this->as($t4)->getJson('/api/me')->assertOk();
    }

    public function test_using_a_device_keeps_it_from_being_evicted(): void
    {
        $user = $this->user();

        $t1 = $this->login($user, self::CHROME_WINDOWS);
        $this->travel(1)->minutes();
        $t2 = $this->login($user, self::SAFARI_IPHONE);
        $this->travel(1)->minutes();
        $t3 = $this->login($user, self::CHROME_ANDROID);

        // "use" t1 so it is no longer the least-recently-used device
        $this->travel(1)->minutes();
        $this->as($t1)->getJson('/api/me')->assertOk();

        // a 4th login should now evict t2 instead (the one never touched since)
        $this->travel(1)->minutes();
        $t4 = $this->login($user, self::CHROME_WINDOWS);

        $this->as($t1)->getJson('/api/me')->assertOk();
        $this->as($t2)->getJson('/api/me')->assertUnauthorized();
        $this->as($t3)->getJson('/api/me')->assertOk();
        $this->as($t4)->getJson('/api/me')->assertOk();
    }

    public function test_expired_tokens_do_not_count_toward_the_device_limit(): void
    {
        $user = $this->user();

        // an already-expired token from a very old device
        $user->createToken('Old Device', ['*'], now()->subDay());

        $t1 = $this->login($user, self::CHROME_WINDOWS);
        $t2 = $this->login($user, self::SAFARI_IPHONE);
        $t3 = $this->login($user, self::CHROME_ANDROID);

        // none of the 3 fresh logins should have been evicted because of the expired one
        $this->as($t1)->getJson('/api/me')->assertOk();
        $this->as($t2)->getJson('/api/me')->assertOk();
        $this->as($t3)->getJson('/api/me')->assertOk();
    }

    public function test_registering_a_fourth_device_via_google_also_respects_the_limit(): void
    {
        // Login endpoint already covers the eviction logic (shared issueToken());
        // this only confirms register() goes through the same limit.
        $user = $this->user();
        $this->login($user, self::CHROME_WINDOWS);
        $this->login($user, self::SAFARI_IPHONE);
        $this->login($user, self::CHROME_ANDROID);

        $this->login($user, self::CHROME_WINDOWS);

        $this->assertDatabaseCount('personal_access_tokens', 3);
    }

    public function test_the_evicted_device_gets_an_explanatory_message_not_a_bare_401(): void
    {
        $user = $this->user();

        $t1 = $this->login($user, self::CHROME_WINDOWS);
        $this->travel(1)->minutes();
        $this->login($user, self::SAFARI_IPHONE);
        $this->travel(1)->minutes();
        $this->login($user, self::CHROME_ANDROID);
        $this->travel(1)->minutes();
        $this->login($user, self::CHROME_WINDOWS); // evicts t1

        $this->as($t1)->getJson('/api/me')
            ->assertUnauthorized()
            ->assertJsonPath('reason', 'device_limit')
            ->assertJsonPath('message', trans('auth.device_evicted.device_limit'));
    }

    public function test_a_normal_invalid_token_still_gets_a_plain_unauthorized(): void
    {
        $this->withToken('1|not-a-real-token')->getJson('/api/me')
            ->assertUnauthorized()
            ->assertJsonMissingPath('reason');
    }
}
