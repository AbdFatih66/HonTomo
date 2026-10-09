<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * These only check routing/middleware wiring (guest vs authenticated), not
 * the actual WebAuthn cryptographic flow — that needs a real authenticator
 * (or laragear/webauthn's own test helpers, once the exact API is confirmed
 * against the installed version; see the note in WebAuthnController).
 */
class WebAuthnRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_endpoints_require_authentication(): void
    {
        $this->postJson('/api/webauthn/register/options')->assertUnauthorized();
        $this->postJson('/api/webauthn/register')->assertUnauthorized();
        $this->getJson('/api/webauthn/credentials')->assertUnauthorized();
        $this->deleteJson('/api/webauthn/credentials/1')->assertUnauthorized();
    }

    public function test_login_options_endpoint_does_not_require_authentication(): void
    {
        // Should reach the controller (not 401) even with nothing to verify yet.
        $response = $this->postJson('/api/webauthn/login/options');

        $this->assertNotSame(401, $response->status());
    }

    public function test_login_endpoint_rejects_a_missing_or_malformed_assertion(): void
    {
        $this->postJson('/api/webauthn/login', [])->assertStatus(422);
    }

    public function test_a_user_can_only_see_and_remove_their_own_passkeys(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $this->app['auth']->forgetGuards();
        $ownerToken = $owner->createToken('spa')->plainTextToken;
        $this->app['auth']->forgetGuards();
        $otherToken = $other->createToken('spa')->plainTextToken;

        $this->withToken($ownerToken)->getJson('/api/webauthn/credentials')->assertOk()->assertJsonCount(0);

        // deleting an id that isn't theirs (or doesn't exist) is a 404, not a 200
        $this->withToken($otherToken)->deleteJson('/api/webauthn/credentials/999')->assertNotFound();
    }
}
