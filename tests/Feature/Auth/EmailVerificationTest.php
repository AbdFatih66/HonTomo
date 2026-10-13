<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function unverifiedUser(): User
    {
        return User::factory()->unverified()->create(['email' => 'budi@example.com']);
    }

    /** The link exactly as the email would contain it. */
    private function emailLink(User $user): string
    {
        return (new VerifyEmail)->toMail($user)->actionUrl;
    }

    /** What the SPA page does with the link's query string. */
    private function submitLink(string $link): TestResponse
    {
        parse_str((string) parse_url($link, PHP_URL_QUERY), $q);

        return $this->postJson("/api/email/verify/{$q['id']}/{$q['hash']}?expires={$q['expires']}&signature={$q['signature']}");
    }

    // ------------------------------------------------------ sending

    public function test_registering_sends_exactly_one_verification_email(): void
    {
        Notification::fake();

        $this->postJson('/api/register', [
            'name' => 'Budi', 'email' => 'budi@example.com',
            'password' => 'Sakura2026', 'password_confirmation' => 'Sakura2026',
        ])->assertCreated()->assertJsonPath('user.email_verified', false);

        $user = User::where('email', 'budi@example.com')->firstOrFail();
        Notification::assertSentToTimes($user, VerifyEmail::class, 1);
        $this->assertNull($user->email_verified_at); // never assumed verified
    }

    public function test_registration_still_succeeds_when_the_verification_email_cannot_be_sent(): void
    {
        Notification::shouldReceive('send')->andThrow(new \RuntimeException('smtp down'));

        $this->postJson('/api/register', [
            'name' => 'Budi', 'email' => 'budi@example.com',
            'password' => 'Sakura2026', 'password_confirmation' => 'Sakura2026',
        ])->assertCreated();

        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
    }

    public function test_the_link_points_to_the_spa_verify_page(): void
    {
        $link = $this->emailLink($this->unverifiedUser());

        $this->assertStringContainsString('/verify-email?', $link);
        parse_str((string) parse_url($link, PHP_URL_QUERY), $q);
        $this->assertEqualsCanonicalizing(['id', 'hash', 'expires', 'signature'], array_keys($q));
    }

    // ------------------------------------------------------- verifying

    public function test_a_valid_link_verifies_the_email_without_being_logged_in(): void
    {
        $user = $this->unverifiedUser();

        $this->submitLink($this->emailLink($user))
            ->assertOk()
            ->assertJsonPath('already_verified', false);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verifying_twice_is_harmless(): void
    {
        $user = $this->unverifiedUser();
        $link = $this->emailLink($user);

        $this->submitLink($link)->assertOk();
        $verifiedAt = $user->fresh()->email_verified_at;

        $this->travel(5)->minutes();
        $this->submitLink($link)->assertOk()->assertJsonPath('already_verified', true);

        $this->assertEquals($verifiedAt, $user->fresh()->email_verified_at);
    }

    public function test_a_tampered_signature_is_rejected(): void
    {
        $user = $this->unverifiedUser();
        $link = $this->emailLink($user);
        parse_str((string) parse_url($link, PHP_URL_QUERY), $q);

        $this->postJson("/api/email/verify/{$q['id']}/{$q['hash']}?expires={$q['expires']}&signature=deadbeef")
            ->assertForbidden();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_a_link_cannot_be_reused_for_another_user(): void
    {
        $victim = $this->unverifiedUser();
        $attacker = User::factory()->unverified()->create(['email' => 'attacker@example.com']);
        parse_str((string) parse_url($this->emailLink($attacker), PHP_URL_QUERY), $q);

        // swap the id but keep the attacker's signature
        $this->postJson("/api/email/verify/{$victim->id}/{$q['hash']}?expires={$q['expires']}&signature={$q['signature']}")
            ->assertForbidden();

        $this->assertNull($victim->fresh()->email_verified_at);
    }

    public function test_an_expired_link_is_rejected(): void
    {
        $user = $this->unverifiedUser();
        $link = $this->emailLink($user);

        $this->travel(config('auth.verification.expire', 60) + 1)->minutes();

        $this->submitLink($link)->assertForbidden();
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_a_link_is_void_after_the_email_address_changes(): void
    {
        $user = $this->unverifiedUser();
        $link = $this->emailLink($user);

        $user->forceFill(['email' => 'baru@example.com'])->save();

        $this->submitLink($link)->assertUnprocessable()->assertJsonValidationErrors('link');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_verify_messages_follow_the_ui_language(): void
    {
        $user = $this->unverifiedUser();
        parse_str((string) parse_url($this->emailLink($user), PHP_URL_QUERY), $q);

        $this->withHeader('X-UI-Language', 'id')
            ->postJson("/api/email/verify/{$q['id']}/{$q['hash']}?expires={$q['expires']}&signature={$q['signature']}")
            ->assertOk()
            ->assertJsonPath('message', 'Email Anda berhasil diverifikasi. Terima kasih!');
    }

    // --------------------------------------------------------- resend

    public function test_resend_requires_authentication(): void
    {
        $this->postJson('/api/email/verification-notification')->assertUnauthorized();
    }

    public function test_an_unverified_user_can_request_a_new_link(): void
    {
        Notification::fake();
        $user = $this->unverifiedUser();

        $this->withToken($user->createToken('spa')->plainTextToken)
            ->postJson('/api/email/verification-notification')
            ->assertOk()
            ->assertJsonPath('already_verified', false);

        Notification::assertSentToTimes($user, VerifyEmail::class, 1);
    }

    public function test_a_verified_user_gets_no_email(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->withToken($user->createToken('spa')->plainTextToken)
            ->postJson('/api/email/verification-notification')
            ->assertOk()
            ->assertJsonPath('already_verified', true);

        Notification::assertNothingSent();
    }

    public function test_resend_is_rate_limited(): void
    {
        Notification::fake();
        $user = $this->unverifiedUser();
        $token = $user->createToken('spa')->plainTextToken;

        for ($i = 0; $i < 3; $i++) {
            $this->withToken($token)->postJson('/api/email/verification-notification')->assertOk();
        }

        $this->withToken($token)->postJson('/api/email/verification-notification')->assertStatus(429);
        Notification::assertSentToTimes($user, VerifyEmail::class, 3);
    }

    // -------------------------------------------------- optional policy

    public function test_an_unverified_user_can_use_the_app(): void
    {
        $user = $this->unverifiedUser();

        $this->withToken($user->createToken('spa')->plainTextToken)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.email_verified', false);
    }

    public function test_no_api_route_requires_a_verified_email(): void
    {
        foreach (Route::getRoutes() as $route) {
            $this->assertNotContains(
                'verified',
                $route->gatherMiddleware(),
                "Route {$route->uri()} must not require verified email (verification is optional).",
            );
        }
    }
}
