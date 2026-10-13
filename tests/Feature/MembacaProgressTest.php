<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserMembacaProgress;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Membaca (読解): progres per bacaan dengan kunci `n{5|4|3}l{n}` (n = 1–12).
 * Aturan XP/mahkota sama seperti Latihan Menulis: 10 XP pertama kali selesai,
 * +5 pertama kali sempurna.
 */
class MembacaProgressTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsToken(User $user): static
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($user->createToken('spa')->plainTextToken);
    }

    public function test_progress_requires_login(): void
    {
        $this->getJson('/api/membaca/progress')->assertUnauthorized();
        $this->postJson('/api/membaca/progress/n3l1', ['perfect' => true])->assertUnauthorized();
    }

    public function test_empty_progress_for_new_user(): void
    {
        $user = User::factory()->create();

        $this->actingAsToken($user)->getJson('/api/membaca/progress')
            ->assertOk()->assertJsonPath('progress', []);
    }

    public function test_first_perfect_run_gives_crown_and_15_xp(): void
    {
        $user = User::factory()->create();

        $this->actingAsToken($user)->postJson('/api/membaca/progress/n3l1', ['perfect' => true])
            ->assertOk()
            ->assertJsonPath('done', true)
            ->assertJsonPath('crown', true)
            ->assertJsonPath('xp', 15);

        $this->actingAsToken($user)->getJson('/api/membaca/progress')
            ->assertJsonPath('progress.n3l1.crown', true);
    }

    public function test_imperfect_then_perfect_awards_xp_in_two_steps_and_crown_is_never_lost(): void
    {
        $user = User::factory()->create();

        $this->actingAsToken($user)->postJson('/api/membaca/progress/n5l1', ['perfect' => false])
            ->assertOk()->assertJsonPath('crown', false)->assertJsonPath('xp', 10);

        $this->actingAsToken($user)->postJson('/api/membaca/progress/n5l1', ['perfect' => true])
            ->assertOk()->assertJsonPath('crown', true)->assertJsonPath('xp', 5);

        $this->actingAsToken($user)->postJson('/api/membaca/progress/n5l1', ['perfect' => false])
            ->assertOk()->assertJsonPath('crown', true)->assertJsonPath('xp', 0);

        $this->assertSame(3, UserMembacaProgress::where('user_id', $user->id)->value('attempts'));
    }

    public function test_levels_do_not_share_progress(): void
    {
        $user = User::factory()->create();

        foreach (['n5l1', 'n4l1', 'n3l1'] as $key)
            $this->actingAsToken($user)->postJson("/api/membaca/progress/{$key}", ['perfect' => false])->assertOk();

        $this->assertSame(3, UserMembacaProgress::where('user_id', $user->id)->count());
    }

    public function test_rejects_made_up_keys(): void
    {
        $user = User::factory()->create();

        // n3l7 cocok dengan pola route tetapi belum punya bacaan → tidak boleh memberi XP.
        foreach (['l1', 'n2l1', 'n3l0', 'n3l13', 'n4l25', 'n3l1-s1', 'x', 'n3l7'] as $key)
            $this->actingAsToken($user)->postJson("/api/membaca/progress/{$key}", ['perfect' => true])->assertNotFound();

        $this->assertSame(0, UserMembacaProgress::count());
    }

    public function test_requires_perfect_flag(): void
    {
        $user = User::factory()->create();

        $this->actingAsToken($user)->postJson('/api/membaca/progress/n3l1', [])->assertUnprocessable();
    }

    public function test_notification_label_names_level_and_lesson(): void
    {
        $user = User::factory()->create();

        $this->actingAsToken($user)->postJson('/api/membaca/progress/n3l1', ['perfect' => false])->assertOk();
        $this->actingAsToken($user)->postJson('/api/membaca/progress/n4l1', ['perfect' => false])->assertOk();

        $labels = UserNotification::where('user_id', $user->id)->get()
            ->map(fn ($n) => json_decode($n->subtitle, true)['lesson'])->all();

        $this->assertContains('N3 Pelajaran 1', $labels);
        $this->assertContains('N4 Pelajaran 1', $labels);
    }

    public function test_dashboard_roadmap_has_a_membaca_stage_counting_n5_readings(): void
    {
        $user = User::factory()->create();

        $stage = fn () => collect($this->actingAsToken($user)->getJson('/api/dashboard')->assertOk()->json('roadmap'))
            ->firstWhere('key', 'membaca');

        $before = $stage();
        $this->assertSame('membaca', $before['route']);
        $this->assertSame(0, $before['done']);
        $this->assertSame(12, $before['total']); // n5l1–n5l12 yang punya bacaan

        // Bacaan N4/N3 tidak ikut hitungan roadmap N5.
        $this->actingAsToken($user)->postJson('/api/membaca/progress/n3l1', ['perfect' => true])->assertOk();
        \Illuminate\Support\Facades\Cache::forget("dashboard:{$user->id}");
        $this->assertSame(0, $stage()['done']);

        $this->actingAsToken($user)->postJson('/api/membaca/progress/n5l1', ['perfect' => false])->assertOk();
        \Illuminate\Support\Facades\Cache::forget("dashboard:{$user->id}");
        $this->assertSame(1, $stage()['done']);
    }
}
