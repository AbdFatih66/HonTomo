<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private function asToken(User $user): static
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($user->createToken('spa')->plainTextToken);
    }

    public function test_regular_user_cannot_view_audit_logs(): void
    {
        $this->asToken(User::factory()->create())
            ->getJson('/api/admin/audit-logs')
            ->assertForbidden();
    }

    public function test_admin_can_view_audit_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['email' => 'budi@example.com', 'password' => 'Sakura2026']);

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'Sakura2026']);

        $response = $this->asToken($admin)->getJson('/api/admin/audit-logs')->assertOk();

        $this->assertGreaterThanOrEqual(1, $response->json('total'));
    }

    public function test_audit_logs_can_be_filtered_by_action_and_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['email' => 'budi@example.com', 'password' => 'Sakura2026']);

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'salah12345']);
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'Sakura2026']);

        $onlyFailed = $this->asToken($admin)->getJson('/api/admin/audit-logs?action=login_failed')->json('data');
        $this->assertNotEmpty($onlyFailed);
        $this->assertTrue(collect($onlyFailed)->every(fn ($l) => $l['action'] === 'login_failed'));

        $byEmail = $this->asToken($admin)->getJson('/api/admin/audit-logs?email=budi')->json('data');
        $this->assertNotEmpty($byEmail);
    }

    public function test_actions_list_endpoint_is_admin_only_and_returns_known_actions(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->asToken($user)->getJson('/api/admin/audit-logs/actions')->assertForbidden();

        $actions = $this->asToken($admin)->getJson('/api/admin/audit-logs/actions')->assertOk()->json('actions');
        $this->assertContains('login_succeeded', $actions);
        $this->assertContains('admin_role_changed', $actions);
    }
}
