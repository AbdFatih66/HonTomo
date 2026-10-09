<?php

namespace Tests\Feature\Auth;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\TestResponse;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
            'app.frontend_url' => 'http://localhost',
        ]);
    }

    // ------------------------------------------------------------ helpers

    private function googleUser(array $overrides = [], array $raw = ['email_verified' => true]): SocialiteUser
    {
        $user = new SocialiteUser;
        $user->map(array_merge([
            'id' => 'google-123',
            'name' => 'Budi Google',
            'email' => 'budi@example.com',
            'avatar' => 'https://lh3.googleusercontent.com/a/avatar',
        ], $overrides));
        $user->setRaw($raw);

        return $user;
    }

    private function mockGoogleReturns(SocialiteUser|\Throwable $result): void
    {
        $provider = Mockery::mock(GoogleProvider::class);

        if ($result instanceof \Throwable) {
            $provider->shouldReceive('user')->andThrow($result);
        } else {
            $provider->shouldReceive('user')->andReturn($result);
        }

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    /** @return array{path:string, query:array} */
    private function location(TestResponse $response): array
    {
        $response->assertRedirect();
        $parts = parse_url($response->headers->get('Location'));
        parse_str($parts['query'] ?? '', $query);

        return ['path' => $parts['path'] ?? '', 'query' => $query];
    }

    private function exchange(string $code): TestResponse
    {
        return $this->postJson('/api/auth/google/exchange', ['code' => $code]);
    }

    // ----------------------------------------------------------- redirect

    public function test_redirect_sends_the_browser_to_google(): void
    {
        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('scopes')->with(['openid', 'profile', 'email'])->andReturnSelf();
        $provider->shouldReceive('with')->with(['prompt' => 'select_account'])->andReturnSelf();
        $provider->shouldReceive('redirect')->andReturn(redirect()->away('https://accounts.google.com/o/oauth2/auth'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get('/api/auth/google/redirect')->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_redirect_reports_unavailable_when_google_is_not_configured(): void
    {
        config(['services.google.client_id' => null]);

        $loc = $this->location($this->get('/api/auth/google/redirect'));

        $this->assertSame('/login', $loc['path']);
        $this->assertSame('unavailable', $loc['query']['oauth_error']);
    }

    // ----------------------------------------------- A: brand-new account

    public function test_callback_creates_a_new_account_and_login_works_through_a_one_time_code(): void
    {
        $this->mockGoogleReturns($this->googleUser());

        $loc = $this->location($this->get('/api/auth/google/callback'));

        $this->assertSame('/auth/callback', $loc['path']);
        $this->assertArrayNotHasKey('token', $loc['query']); // never a token in the URL
        $this->assertSame(64, strlen($loc['query']['code']));

        $user = User::where('email', 'budi@example.com')->firstOrFail();
        $this->assertNull($user->password);
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('user', $user->role);
        $this->assertSame('https://lh3.googleusercontent.com/a/avatar', $user->avatar);
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id, 'provider' => 'google', 'provider_id' => 'google-123',
        ]);

        $response = $this->exchange($loc['query']['code'])
            ->assertOk()
            ->assertJsonPath('user.email', 'budi@example.com')
            ->assertJsonPath('user.has_password', false)
            ->assertJsonPath('user.providers.0', 'google');

        $this->withToken($response->json('token'))->getJson('/api/me')->assertOk();
    }

    public function test_the_one_time_code_only_works_once(): void
    {
        $this->mockGoogleReturns($this->googleUser());
        $code = $this->location($this->get('/api/auth/google/callback'))['query']['code'];

        $this->exchange($code)->assertOk();
        $this->exchange($code)->assertUnprocessable()->assertJsonValidationErrors('code');
    }

    public function test_invalid_or_missing_codes_are_rejected(): void
    {
        $this->exchange(str_repeat('a', 64))->assertUnprocessable();
        $this->postJson('/api/auth/google/exchange', [])->assertUnprocessable();
        $this->exchange('short')->assertUnprocessable();
    }

    public function test_google_account_with_unverified_email_is_refused(): void
    {
        $this->mockGoogleReturns($this->googleUser(raw: ['email_verified' => false]));

        $loc = $this->location($this->get('/api/auth/google/callback'));

        $this->assertSame('/login', $loc['path']);
        $this->assertSame('email_unverified', $loc['query']['oauth_error']);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('social_accounts', 0);
    }

    // ------------------------------------ B: email exists -> never merged

    public function test_existing_email_is_not_merged_automatically(): void
    {
        $existing = User::factory()->create(['email' => 'budi@example.com', 'password' => 'Sakura2026']);
        $passwordHash = $existing->password;
        $this->mockGoogleReturns($this->googleUser());

        $loc = $this->location($this->get('/api/auth/google/callback'));

        $this->assertSame('/login', $loc['path']);
        $this->assertSame('account_exists', $loc['query']['oauth_error']);
        $this->assertArrayNotHasKey('code', $loc['query']);
        $this->assertDatabaseCount('social_accounts', 0);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertSame($passwordHash, $existing->fresh()->password);
    }

    public function test_an_unverified_password_account_cannot_be_taken_over_through_google(): void
    {
        // Attacker pre-registered the victim's address (email never verified).
        User::factory()->unverified()->create(['email' => 'budi@example.com', 'password' => 'AttackerKnows1']);
        $this->mockGoogleReturns($this->googleUser());

        $loc = $this->location($this->get('/api/auth/google/callback'));

        $this->assertSame('account_exists', $loc['query']['oauth_error']);
        $this->assertDatabaseCount('social_accounts', 0);
    }

    // ---------------------------------------------- C: already linked

    public function test_linked_google_id_logs_into_the_matching_account_without_changing_the_profile(): void
    {
        $user = User::factory()->create(['email' => 'lama@example.com', 'name' => 'Nama Pilihan Saya']);
        SocialAccount::create(['user_id' => $user->id, 'provider' => 'google', 'provider_id' => 'google-123']);
        $this->mockGoogleReturns($this->googleUser(['email' => 'email-baru@example.com', 'name' => 'Nama Google Baru']));

        $loc = $this->location($this->get('/api/auth/google/callback'));

        $this->exchange($loc['query']['code'])
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);

        $fresh = $user->fresh();
        $this->assertSame('Nama Pilihan Saya', $fresh->name);
        $this->assertSame('lama@example.com', $fresh->email);
        $this->assertDatabaseCount('users', 1);
    }

    // ------------------------------------------------------- D: failures

    public function test_cancelled_consent_returns_to_login_without_creating_anything(): void
    {
        $loc = $this->location($this->get('/api/auth/google/callback?error=access_denied'));

        $this->assertSame('/login', $loc['path']);
        $this->assertSame('cancelled', $loc['query']['oauth_error']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_invalid_oauth_state_is_rejected_without_creating_an_account(): void
    {
        $this->mockGoogleReturns(new InvalidStateException);

        $loc = $this->location($this->get('/api/auth/google/callback?code=x&state=forged'));

        $this->assertSame('failed', $loc['query']['oauth_error']);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_any_provider_error_ends_in_a_safe_failure(): void
    {
        $this->mockGoogleReturns(new \RuntimeException('boom secret-details'));

        $response = $this->get('/api/auth/google/callback?code=x');

        $loc = $this->location($response);
        $this->assertSame('failed', $loc['query']['oauth_error']);
        $this->assertStringNotContainsString('secret-details', $response->headers->get('Location'));
        $this->assertDatabaseCount('users', 0);
    }

    // ------------------------------------------------------------ linking

    public function test_link_intent_requires_authentication(): void
    {
        $this->postJson('/api/auth/google/link-intent')->assertUnauthorized();
    }

    public function test_link_intent_is_single_use_and_bound_to_the_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('spa')->plainTextToken;

        $url = $this->withToken($token)->postJson('/api/auth/google/link-intent')->assertOk()->json('url');
        parse_str(parse_url($url, PHP_URL_QUERY), $q);

        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('scopes')->andReturnSelf();
        $provider->shouldReceive('with')->andReturnSelf();
        $provider->shouldReceive('redirect')->andReturn(redirect()->away('https://accounts.google.com/o/oauth2/auth'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get('/api/auth/google/redirect?intent='.$q['intent'])
            ->assertRedirect('https://accounts.google.com/o/oauth2/auth')
            ->assertSessionHas('oauth_link_user_id', $user->id);

        // the intent is spent
        $loc = $this->location($this->get('/api/auth/google/redirect?intent='.$q['intent']));
        $this->assertSame('/profile', $loc['path']);
        $this->assertSame('failed', $loc['query']['oauth_error']);
    }

    public function test_signed_in_user_can_link_google_and_keeps_all_data(): void
    {
        $user = User::factory()->create(['email' => 'budi@example.com', 'password' => 'Sakura2026', 'hearts' => 3]);
        $this->mockGoogleReturns($this->googleUser(['email' => 'lain-email@gmail.com']));

        $loc = $this->location(
            $this->withSession(['oauth_link_user_id' => $user->id])->get('/api/auth/google/callback')
        );

        $this->assertSame('/profile', $loc['path']);
        $this->assertSame('google', $loc['query']['linked']);
        $this->assertDatabaseHas('social_accounts', ['user_id' => $user->id, 'provider_id' => 'google-123']);

        $fresh = $user->fresh();
        $this->assertSame($user->id, $fresh->id);
        $this->assertSame('budi@example.com', $fresh->email);
        $this->assertSame(3, (int) $fresh->hearts);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 0); // linking does not log anyone in
    }

    public function test_google_account_linked_to_someone_else_cannot_be_linked_again(): void
    {
        $owner = User::factory()->create();
        SocialAccount::create(['user_id' => $owner->id, 'provider' => 'google', 'provider_id' => 'google-123']);
        $me = User::factory()->create();
        $this->mockGoogleReturns($this->googleUser());

        $loc = $this->location(
            $this->withSession(['oauth_link_user_id' => $me->id])->get('/api/auth/google/callback')
        );

        $this->assertSame('/profile', $loc['path']);
        $this->assertSame('already_linked', $loc['query']['oauth_error']);
        $this->assertDatabaseCount('social_accounts', 1);
    }

    public function test_database_rejects_a_duplicate_provider_id(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        SocialAccount::create(['user_id' => $a->id, 'provider' => 'google', 'provider_id' => 'same-id']);

        $this->expectException(QueryException::class);
        SocialAccount::create(['user_id' => $b->id, 'provider' => 'google', 'provider_id' => 'same-id']);
    }

    // ------------------------------------------------------------- unlink

    public function test_user_with_a_password_can_unlink_google(): void
    {
        $user = User::factory()->create(['password' => 'Sakura2026']);
        SocialAccount::create(['user_id' => $user->id, 'provider' => 'google', 'provider_id' => 'google-123']);

        $this->withToken($user->createToken('spa')->plainTextToken)
            ->deleteJson('/api/auth/google')
            ->assertOk()
            ->assertJsonPath('user.providers', []);

        $this->assertDatabaseCount('social_accounts', 0);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_user_without_a_password_cannot_unlink_google(): void
    {
        $user = User::factory()->create(['password' => null]);
        SocialAccount::create(['user_id' => $user->id, 'provider' => 'google', 'provider_id' => 'google-123']);

        $this->withToken($user->createToken('spa')->plainTextToken)
            ->deleteJson('/api/auth/google')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('provider');

        $this->assertDatabaseCount('social_accounts', 1);
    }

    // -------------------------------------------- password login interplay

    public function test_google_only_account_cannot_log_in_with_a_password(): void
    {
        User::factory()->create(['email' => 'budi@example.com', 'password' => null]);

        $this->postJson('/api/login', ['email' => 'budi@example.com', 'password' => 'apapun12345'])
            ->assertUnprocessable();
    }

    public function test_google_only_account_can_set_a_password_through_reset(): void
    {
        $user = User::factory()->create(['email' => 'budi@example.com', 'password' => null]);
        $token = \Illuminate\Support\Facades\Password::broker()->createToken($user);

        $this->postJson('/api/reset-password', [
            'token' => $token, 'email' => $user->email,
            'password' => 'BaruAman2026', 'password_confirmation' => 'BaruAman2026',
        ])->assertOk();

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'BaruAman2026'])->assertOk();
    }
}
