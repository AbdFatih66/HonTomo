<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create([
            'email' => 'budi@example.com',
            'password' => 'Sakura2026',
        ]);
    }

    private function resetPayload(User $user, string $token, array $overrides = []): array
    {
        return array_merge([
            'token' => $token,
            'email' => $user->email,
            'password' => 'BaruAman2026',
            'password_confirmation' => 'BaruAman2026',
        ], $overrides);
    }

    // ---------------------------------------------------------- forgot

    public function test_reset_link_is_sent_to_a_registered_email(): void
    {
        Notification::fake();
        $user = $this->user();

        $this->postJson('/api/forgot-password', ['email' => 'budi@example.com'])
            ->assertOk()
            ->assertJsonPath('message', trans('auth.reset_link_sent'));

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_link_points_to_the_spa_reset_page(): void
    {
        Notification::fake();
        $user = $this->user();

        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertOk();

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $n) use ($user) {
            $url = $n->toMail($user)->actionUrl;

            return str_contains($url, '/reset-password?token=')
                && str_contains($url, 'email='.urlencode($user->email));
        });
    }

    public function test_unknown_email_gets_the_same_response_and_no_email(): void
    {
        Notification::fake();
        $this->user();

        $known = $this->postJson('/api/forgot-password', ['email' => 'budi@example.com']);
        $unknown = $this->postJson('/api/forgot-password', ['email' => 'nobody@example.com']);

        $unknown->assertOk();
        $this->assertSame($known->status(), $unknown->status());
        $this->assertSame($known->json(), $unknown->json());

        Notification::assertSentToTimes(User::first(), ResetPassword::class, 1);
        Notification::assertCount(1);
    }

    public function test_forgot_password_validates_email_format(): void
    {
        $this->postJson('/api/forgot-password', ['email' => 'not-an-email'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->postJson('/api/forgot-password', [])->assertUnprocessable();
    }

    public function test_repeated_requests_do_not_send_more_than_one_email_per_throttle_window(): void
    {
        Notification::fake();
        $user = $this->user();

        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertOk();
        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertOk(); // same answer, no leak

        Notification::assertSentToTimes($user, ResetPassword::class, 1);
    }

    public function test_forgot_password_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/forgot-password', ['email' => "u{$i}@example.com"])->assertOk();
        }

        $this->postJson('/api/forgot-password', ['email' => 'again@example.com'])->assertStatus(429);
    }

    public function test_forgot_password_message_follows_the_ui_language(): void
    {
        $this->withHeader('X-UI-Language', 'id')
            ->postJson('/api/forgot-password', ['email' => 'x@example.com'])
            ->assertOk()
            ->assertJsonPath('message', trans('auth.reset_link_sent', [], 'id'));
    }

    // ----------------------------------------------------------- reset

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        $user = $this->user();
        $token = Password::broker()->createToken($user);

        $this->postJson('/api/reset-password', $this->resetPayload($user, $token))
            ->assertOk()
            ->assertJsonPath('message', trans('auth.reset_success'));

        $this->assertTrue(Hash::check('BaruAman2026', $user->fresh()->password));
    }

    public function test_new_password_works_for_login_and_the_old_one_does_not(): void
    {
        $user = $this->user();
        $token = Password::broker()->createToken($user);

        $this->postJson('/api/reset-password', $this->resetPayload($user, $token))->assertOk();

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'BaruAman2026'])->assertOk();
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'Sakura2026'])->assertUnprocessable();
    }

    public function test_reset_token_cannot_be_used_twice(): void
    {
        $user = $this->user();
        $token = Password::broker()->createToken($user);

        $this->postJson('/api/reset-password', $this->resetPayload($user, $token))->assertOk();

        $this->postJson('/api/reset-password', $this->resetPayload($user, $token, [
            'password' => 'LainLagi2026',
            'password_confirmation' => 'LainLagi2026',
        ]))->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->assertTrue(Hash::check('BaruAman2026', $user->fresh()->password));
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = $this->user();

        $this->postJson('/api/reset-password', $this->resetPayload($user, 'not-a-real-token'))
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', trans('auth.reset_invalid'));

        $this->assertTrue(Hash::check('Sakura2026', $user->fresh()->password));
    }

    public function test_expired_token_is_rejected(): void
    {
        $user = $this->user();
        $token = Password::broker()->createToken($user);

        $this->travel(config('auth.passwords.users.expire') + 1)->minutes();

        $this->postJson('/api/reset-password', $this->resetPayload($user, $token))
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', trans('auth.reset_invalid'));

        $this->assertTrue(Hash::check('Sakura2026', $user->fresh()->password));
    }

    public function test_token_for_another_user_is_rejected(): void
    {
        $user = $this->user();
        $other = User::factory()->create(['email' => 'lain@example.com']);
        $token = Password::broker()->createToken($other);

        $this->postJson('/api/reset-password', $this->resetPayload($user, $token))->assertUnprocessable();

        $this->assertTrue(Hash::check('Sakura2026', $user->fresh()->password));
    }

    public function test_unknown_email_and_bad_token_give_the_same_error(): void
    {
        $user = $this->user();
        $token = Password::broker()->createToken($user);

        $badToken = $this->postJson('/api/reset-password', $this->resetPayload($user, 'wrong'));
        $unknownEmail = $this->postJson('/api/reset-password', $this->resetPayload($user, $token, ['email' => 'nobody@example.com']));

        $this->assertSame($badToken->json('errors'), $unknownEmail->json('errors'));
    }

    public function test_password_confirmation_and_strength_are_enforced(): void
    {
        $user = $this->user();
        $token = Password::broker()->createToken($user);

        $this->postJson('/api/reset-password', $this->resetPayload($user, $token, ['password_confirmation' => 'Beda12345']))
            ->assertUnprocessable()->assertJsonValidationErrors('password');

        $this->postJson('/api/reset-password', $this->resetPayload($user, $token, [
            'password' => 'lemah', 'password_confirmation' => 'lemah',
        ]))->assertUnprocessable()->assertJsonValidationErrors('password');

        // a failed attempt must not burn the token
        $this->postJson('/api/reset-password', $this->resetPayload($user, $token))->assertOk();
    }

    public function test_reset_revokes_all_existing_sessions_but_keeps_the_account_data(): void
    {
        $user = User::factory()->create([
            'email' => 'budi@example.com',
            'password' => 'Sakura2026',
            'hearts' => 3,
        ]);
        $user->createToken('spa');
        $user->createToken('spa');
        $this->assertDatabaseCount('personal_access_tokens', 2);

        $token = Password::broker()->createToken($user);
        $this->postJson('/api/reset-password', $this->resetPayload($user, $token))->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $fresh = $user->fresh();
        $this->assertSame($user->id, $fresh->id);
        $this->assertSame(3, (int) $fresh->hearts);
        $this->assertSame('budi@example.com', $fresh->email);
    }

    public function test_reset_password_does_not_log_the_user_in(): void
    {
        $user = $this->user();
        $token = Password::broker()->createToken($user);

        $response = $this->postJson('/api/reset-password', $this->resetPayload($user, $token))->assertOk();

        $response->assertJsonMissingPath('token');
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_reset_password_is_rate_limited(): void
    {
        $user = $this->user();

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/reset-password', $this->resetPayload($user, 'guess'.$i))->assertUnprocessable();
        }

        $this->postJson('/api/reset-password', $this->resetPayload($user, 'guess-again'))->assertStatus(429);
    }
}
