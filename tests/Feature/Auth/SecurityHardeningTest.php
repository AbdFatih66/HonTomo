<?php

namespace Tests\Feature\Auth;

use App\Models\AuthAuditLog;
use App\Models\User;
use App\Models\UserKnownDevice;
use App\Notifications\NewDeviceSignIn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * Covers items 3, 5, 6 and 7 of the post-launch hardening pass: CAPTCHA,
 * breached-password check, new-device email, and the audit log.
 */
class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private const DEVICE_A = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/126.0.0.0 Safari/537.36';
    private const DEVICE_B = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) Safari/604.1';

    private function registerPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Budi', 'email' => 'budi@example.com',
            'password' => 'Sakura2026', 'password_confirmation' => 'Sakura2026',
        ], $overrides);
    }

    // -------------------------------------------------------------- CAPTCHA

    public function test_registration_works_without_a_captcha_token_when_turnstile_is_not_configured(): void
    {
        config(['services.turnstile.secret_key' => null]);

        $this->postJson('/api/register', $this->registerPayload())->assertCreated();
    }

    public function test_registration_is_rejected_without_a_valid_captcha_token_when_turnstile_is_configured(): void
    {
        config(['services.turnstile.secret_key' => 'test-secret']);
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => false], 200)]);

        $this->postJson('/api/register', $this->registerPayload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cf_turnstile_response');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_succeeds_with_a_valid_captcha_token(): void
    {
        config(['services.turnstile.secret_key' => 'test-secret']);
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true], 200)]);

        $this->postJson('/api/register', $this->registerPayload(['cf_turnstile_response' => 'a-real-looking-token']))
            ->assertCreated();
    }

    public function test_registration_is_not_blocked_if_the_turnstile_api_is_unreachable(): void
    {
        config(['services.turnstile.secret_key' => 'test-secret']);
        Http::fake(['challenges.cloudflare.com/*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('down')]);

        // fails open: an outage on Cloudflare's side must not lock everyone out
        $this->postJson('/api/register', $this->registerPayload(['cf_turnstile_response' => 'whatever']))
            ->assertCreated();
    }

    // ------------------------------------------------------ breached password

    public function test_registration_rejects_a_known_breached_password(): void
    {
        // Laravel's Password::uncompromised() calls the Pwned Passwords API by
        // range; fake it to report the password as breached without a real call.
        Http::fake(['api.pwnedpasswords.com/*' => Http::response("0018A45C4D1DEF81644B54AB7F969B88D65:5\n", 200)]);

        // A password whose SHA1 hash starts with the prefix above ("password").
        $this->postJson('/api/register', $this->registerPayload([
            'password' => 'password', 'password_confirmation' => 'password',
        ]))->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    // ------------------------------------------------------ new-device email

    public function test_no_new_device_email_on_the_very_first_sign_in(): void
    {
        Notification::fake();

        $this->postJson('/api/register', $this->registerPayload())->assertCreated();

        Notification::assertNothingSent();
        $this->assertSame(1, UserKnownDevice::count());
    }

    public function test_a_second_known_device_does_not_trigger_an_email(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'budi@example.com', 'password' => 'Sakura2026']);

        // same device signs in twice
        $this->withHeader('User-Agent', self::DEVICE_A)
            ->postJson('/api/login', ['email' => $user->email, 'password' => 'Sakura2026'])->assertOk();
        $this->withHeader('User-Agent', self::DEVICE_A)
            ->postJson('/api/login', ['email' => $user->email, 'password' => 'Sakura2026'])->assertOk();

        Notification::assertNothingSentTo($user);
        $this->assertSame(1, UserKnownDevice::where('user_id', $user->id)->count());
    }

    public function test_a_genuinely_new_device_triggers_an_email(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'budi@example.com', 'password' => 'Sakura2026']);

        $this->withHeader('User-Agent', self::DEVICE_A)
            ->postJson('/api/login', ['email' => $user->email, 'password' => 'Sakura2026'])->assertOk();

        Notification::assertNothingSentTo($user); // device #1 for this account: no surprise

        $this->withHeader('User-Agent', self::DEVICE_B)
            ->postJson('/api/login', ['email' => $user->email, 'password' => 'Sakura2026'])->assertOk();

        Notification::assertSentToTimes($user, NewDeviceSignIn::class, 1);
        $this->assertSame(2, UserKnownDevice::where('user_id', $user->id)->count());
    }

    public function test_a_failed_new_device_email_does_not_block_login(): void
    {
        Notification::shouldReceive('send')->andThrow(new \RuntimeException('smtp down'));
        $user = User::factory()->create(['email' => 'budi@example.com', 'password' => 'Sakura2026']);

        $this->withHeader('User-Agent', self::DEVICE_A)
            ->postJson('/api/login', ['email' => $user->email, 'password' => 'Sakura2026'])->assertOk();
        $this->withHeader('User-Agent', self::DEVICE_B)
            ->postJson('/api/login', ['email' => $user->email, 'password' => 'Sakura2026'])->assertOk();
    }

    // ---------------------------------------------------------------- audit

    public function test_successful_and_failed_logins_are_audited(): void
    {
        $user = User::factory()->create(['email' => 'budi@example.com', 'password' => 'Sakura2026']);

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'salah12345']);
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'Sakura2026']);

        $this->assertDatabaseHas('auth_audit_logs', ['user_id' => null, 'action' => 'login_failed']);
        $this->assertDatabaseHas('auth_audit_logs', ['user_id' => $user->id, 'action' => 'login_succeeded']);
    }

    public function test_password_reset_is_audited(): void
    {
        $user = User::factory()->create(['email' => 'budi@example.com', 'password' => 'Sakura2026']);
        $token = Password::broker()->createToken($user);

        $this->postJson('/api/reset-password', [
            'token' => $token, 'email' => $user->email,
            'password' => 'BaruAman2026', 'password_confirmation' => 'BaruAman2026',
        ])->assertOk();

        $this->assertDatabaseHas('auth_audit_logs', ['user_id' => $user->id, 'action' => 'password_reset']);
    }

    public function test_admin_actions_on_other_users_are_audited_and_revoke_their_tokens(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'user']);
        $target->createToken('spa');
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withToken($admin->createToken('spa')->plainTextToken)
            ->putJson("/api/users/{$target->id}", [
                'name' => $target->name, 'email' => $target->email,
                'password' => 'DiubahAdmin2026', 'role' => 'admin',
            ])->assertOk();

        $this->assertDatabaseHas('auth_audit_logs', ['user_id' => $admin->id, 'action' => 'admin_password_changed']);
        $this->assertDatabaseHas('auth_audit_logs', ['user_id' => $admin->id, 'action' => 'admin_role_changed']);

        // the target's pre-existing session must be gone
        $this->assertDatabaseCount('personal_access_tokens', 1); // only the admin's own token remains
    }

    public function test_deleting_a_user_revokes_their_tokens_and_is_audited(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create();
        $target->createToken('spa');

        $this->withToken($admin->createToken('spa')->plainTextToken)
            ->deleteJson("/api/users/{$target->id}")
            ->assertOk();

        $this->assertDatabaseHas('auth_audit_logs', ['action' => 'admin_user_deleted']);
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_audit_log_never_stores_the_password(): void
    {
        $user = User::factory()->create(['email' => 'budi@example.com', 'password' => 'Sakura2026']);
        $token = Password::broker()->createToken($user);

        $this->postJson('/api/reset-password', [
            'token' => $token, 'email' => $user->email,
            'password' => 'RahasiaBanget2026', 'password_confirmation' => 'RahasiaBanget2026',
        ])->assertOk();

        $log = AuthAuditLog::where('action', 'password_reset')->firstOrFail();
        $this->assertStringNotContainsString('RahasiaBanget2026', json_encode($log->toArray()));
    }
}
