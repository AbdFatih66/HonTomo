<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Tanaka Budi',
            'email' => 'budi@example.com',
            'password' => 'Sakura2026',
            'password_confirmation' => 'Sakura2026',
        ], $overrides);
    }

    public function test_user_can_register_and_receives_a_working_token(): void
    {
        $response = $this->postJson('/api/register', $this->payload());

        $response->assertCreated()
            ->assertJsonStructure(['user' => ['id', 'name', 'email', 'role'], 'token', 'expires_at'])
            ->assertJsonPath('user.email', 'budi@example.com')
            ->assertJsonPath('user.role', 'user')
            ->assertJsonPath('user.email_verified', false);

        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);

        $this->withToken($response->json('token'))
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.email', 'budi@example.com');
    }

    public function test_password_is_hashed_and_never_returned(): void
    {
        $response = $this->postJson('/api/register', $this->payload());

        $user = User::where('email', 'budi@example.com')->firstOrFail();

        $this->assertNotSame('Sakura2026', $user->password);
        $this->assertTrue(Hash::check('Sakura2026', $user->password));
        $this->assertStringNotContainsString('Sakura2026', $response->getContent());
        $response->assertJsonMissingPath('user.password');
    }

    public function test_email_is_normalized_to_lowercase(): void
    {
        $this->postJson('/api/register', $this->payload(['email' => '  Budi@Example.COM ']))->assertCreated();

        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'budi@example.com']);

        $this->postJson('/api/register', $this->payload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_weak_password_is_rejected(): void
    {
        foreach (['short1', 'onlylettersnodigits', '1234567890'] as $weak) {
            $this->postJson('/api/register', $this->payload([
                'email' => "u{$weak}@example.com",
                'password' => $weak,
                'password_confirmation' => $weak,
            ]))->assertUnprocessable()->assertJsonValidationErrors('password');
        }

        $this->assertDatabaseCount('users', 0);
    }

    public function test_password_confirmation_must_match(): void
    {
        $this->postJson('/api/register', $this->payload(['password_confirmation' => 'Different2026']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }

    public function test_name_and_email_are_required(): void
    {
        $this->postJson('/api/register', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_role_cannot_be_injected_through_registration(): void
    {
        $this->postJson('/api/register', $this->payload(['role' => 'admin']))->assertCreated();

        $this->assertSame('user', User::where('email', 'budi@example.com')->value('role'));
    }

    public function test_honeypot_field_blocks_bots(): void
    {
        $this->postJson('/api/register', $this->payload(['website' => 'http://spam.example']))
            ->assertUnprocessable();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_validation_messages_follow_the_ui_language_header(): void
    {
        User::factory()->create(['email' => 'budi@example.com']);

        $this->withHeader('X-UI-Language', 'id')
            ->postJson('/api/register', $this->payload())
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Email ini sudah terdaftar.');

        $this->withHeader('X-UI-Language', 'en')
            ->postJson('/api/register', $this->payload())
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'This email is already registered.');
    }

    public function test_new_user_stores_the_current_ui_language(): void
    {
        $this->withHeader('X-UI-Language', 'en')->postJson('/api/register', $this->payload())->assertCreated();

        $this->assertSame('en', User::where('email', 'budi@example.com')->value('ui_language'));
    }

    public function test_registration_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/register', [])->assertUnprocessable();
        }

        $this->postJson('/api/register', $this->payload())->assertStatus(429);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registering_does_not_touch_existing_users(): void
    {
        $existing = User::factory()->create(['email' => 'lama@example.com', 'hearts' => 3]);
        $before = $existing->only(['name', 'email', 'password', 'role', 'hearts', 'ui_language']);

        $this->postJson('/api/register', $this->payload())->assertCreated();

        $this->assertSame($before, $existing->fresh()->only(['name', 'email', 'password', 'role', 'hearts', 'ui_language']));
    }
}
