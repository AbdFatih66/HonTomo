<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create([
            'email' => 'budi@example.com',
            'password' => 'Sakura2026',
        ]);
    }

    public function test_user_can_login_with_email_and_password(): void
    {
        $this->user();

        $response = $this->postJson('/api/login', ['email' => 'budi@example.com', 'password' => 'Sakura2026']);

        $response->assertOk()
            ->assertJsonStructure(['user' => ['id', 'email', 'role'], 'token', 'expires_at'])
            ->assertJsonMissingPath('user.password');

        $this->withToken($response->json('token'))->getJson('/api/me')->assertOk();
    }

    public function test_email_is_case_insensitive(): void
    {
        $this->user();

        $this->postJson('/api/login', ['email' => 'BUDI@Example.com', 'password' => 'Sakura2026'])->assertOk();
    }

    public function test_wrong_password_and_unknown_email_return_the_same_error(): void
    {
        $this->user();

        $wrong = $this->postJson('/api/login', ['email' => 'budi@example.com', 'password' => 'Salah12345']);
        $unknown = $this->postJson('/api/login', ['email' => 'nobody@example.com', 'password' => 'Salah12345']);

        $wrong->assertUnprocessable()->assertJsonValidationErrors('email');
        $unknown->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertSame($wrong->json('errors'), $unknown->json('errors'));
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_is_blocked_after_too_many_failed_attempts(): void
    {
        $this->user();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', ['email' => 'budi@example.com', 'password' => 'Salah12345'])
                ->assertUnprocessable();
        }

        // Even the correct password is refused while locked out.
        $this->postJson('/api/login', ['email' => 'budi@example.com', 'password' => 'Sakura2026'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_successful_login_resets_the_failed_attempt_counter(): void
    {
        $this->user();

        for ($i = 0; $i < 4; $i++) {
            $this->postJson('/api/login', ['email' => 'budi@example.com', 'password' => 'Salah12345']);
        }

        $this->postJson('/api/login', ['email' => 'budi@example.com', 'password' => 'Sakura2026'])->assertOk();

        for ($i = 0; $i < 4; $i++) {
            $this->postJson('/api/login', ['email' => 'budi@example.com', 'password' => 'Salah12345'])
                ->assertUnprocessable();
        }

        $this->postJson('/api/login', ['email' => 'budi@example.com', 'password' => 'Sakura2026'])->assertOk();
    }

    public function test_remember_me_issues_a_longer_lived_token(): void
    {
        $this->user();

        $this->postJson('/api/login', ['email' => 'budi@example.com', 'password' => 'Sakura2026'])->assertOk();
        $short = PersonalAccessToken::latest('id')->first()->expires_at;

        $this->postJson('/api/login', ['email' => 'budi@example.com', 'password' => 'Sakura2026', 'remember' => true])->assertOk();
        $long = PersonalAccessToken::latest('id')->first()->expires_at;

        $this->assertTrue($short->lessThanOrEqualTo(now()->addDay()->addMinute()));
        $this->assertTrue($long->greaterThan(now()->addDays(29)));
    }

    public function test_expired_token_is_rejected(): void
    {
        $token = $this->user()->createToken('spa', ['*'], now()->subMinute())->plainTextToken;

        $this->withToken($token)->getJson('/api/me')->assertUnauthorized();
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $this->user();
        $token = $this->postJson('/api/login', ['email' => 'budi@example.com', 'password' => 'Sakura2026'])
            ->json('token');

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_validates_input(): void
    {
        $this->postJson('/api/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_error_follows_the_ui_language_header(): void
    {
        $this->withHeader('X-UI-Language', 'id')
            ->postJson('/api/login', ['email' => 'x@example.com', 'password' => 'Salah12345'])
            ->assertJsonPath('errors.email.0', 'Email atau kata sandi salah.');
    }
}
