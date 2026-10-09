<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildMiniCurriculum(); // firstLesson -> secondLesson 
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function asToken(User $user): static
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($user->createToken('spa')->plainTextToken);
    }

    public function test_role_cannot_be_set_through_mass_assignment(): void
    {
        $user = new User(['name' => 'X', 'email' => 'x@example.com', 'password' => 'Sakura2026', 'role' => 'admin']);

        $this->assertNull($user->role);
    }

    public function test_every_account_can_open_any_lesson(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        $this->asToken($user)->postJson("/api/lessons/{$this->secondLesson->id}/start")->assertOk();
        $this->asToken($admin)->postJson("/api/lessons/{$this->secondLesson->id}/start")->assertOk();
    }

    public function test_the_learning_path_shows_no_locked_lessons_for_an_admin(): void
    {
        $admin = $this->admin();

        $path = $this->asToken($admin)->getJson('/api/learning-path')->assertOk()->json();

        $statuses = collect($path['units'] ?? $path)->pluck('lessons')->flatten(1)->pluck('status');
        $this->assertNotContains('locked', $statuses->all());
    }

    public function test_an_admin_can_promote_another_user_to_admin(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['role' => 'user']);

        $this->asToken($admin)->putJson("/api/users/{$target->id}", [
            'name' => $target->name, 'email' => $target->email, 'role' => 'admin',
        ])->assertOk()->assertJsonPath('role', 'admin');

        $this->assertSame('admin', $target->fresh()->role);
    }

    public function test_a_regular_user_cannot_manage_users(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->asToken($user)->getJson('/api/users')->assertForbidden();
        $this->asToken($user)->putJson("/api/users/{$other->id}", [
            'name' => $other->name, 'email' => $other->email, 'role' => 'admin',
        ])->assertForbidden();

        $this->assertSame('user', $other->fresh()->role);
    }

    public function test_creating_a_user_via_admin_endpoint_sets_the_chosen_role(): void
    {
        $admin = $this->admin();

        $this->asToken($admin)->postJson('/api/users', [
            'name' => 'Baru', 'email' => 'baru@example.com', 'password' => 'Sakura2026', 'role' => 'admin',
        ])->assertCreated()->assertJsonPath('role', 'admin');

        $this->assertSame('admin', User::where('email', 'baru@example.com')->value('role'));
    }
}
