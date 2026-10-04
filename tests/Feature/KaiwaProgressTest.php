<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserKaiwaProgress;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Progres + XP latihan percakapan (Kaiwa), pola sama dengan Chokai:
 * 10 XP pertama kali selesai, +5 saat pertama kali selesai sempurna.
 */
class KaiwaProgressTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsToken(User $user): static
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($user->createToken('spa')->plainTextToken);
    }

    public function test_first_completion_awards_ten_xp_and_marks_done(): void
    {
        $user = User::factory()->create();

        $this->actingAsToken($user)
            ->postJson('/api/kaiwa/progress/l2-s1', ['perfect' => false])
            ->assertOk()
            ->assertJson(['set_key' => 'l2-s1', 'done' => true, 'crown' => false, 'xp' => 10]);

        $this->assertEquals(10, $user->xpLedger()->sum('amount'));
        $this->assertSame('kaiwa_completed', $user->xpLedger()->first()->source);
    }

    public function test_perfect_first_run_awards_fifteen_xp_in_one_go(): void
    {
        $user = User::factory()->create();

        $this->actingAsToken($user)
            ->postJson('/api/kaiwa/progress/l3-s2', ['perfect' => true])
            ->assertOk()
            ->assertJson(['done' => true, 'crown' => true, 'xp' => 15]);

        $this->assertEquals(15, $user->xpLedger()->sum('amount'));
    }

    public function test_crown_is_awarded_once_even_on_a_later_replay(): void
    {
        $user = User::factory()->create();
        $client = $this->actingAsToken($user);

        $client->postJson('/api/kaiwa/progress/l4-s1', ['perfect' => false])->assertJson(['xp' => 10]);
        $client->postJson('/api/kaiwa/progress/l4-s1', ['perfect' => false])->assertJson(['xp' => 0, 'crown' => false]);
        $client->postJson('/api/kaiwa/progress/l4-s1', ['perfect' => true])->assertJson(['xp' => 5, 'crown' => true]);
        // sudah dapat mahkota: main ulang tidak menambah XP dan mahkota tidak hilang
        $client->postJson('/api/kaiwa/progress/l4-s1', ['perfect' => true])->assertJson(['xp' => 0]);
        $client->postJson('/api/kaiwa/progress/l4-s1', ['perfect' => false])->assertJson(['xp' => 0, 'crown' => true]);

        $this->assertEquals(15, $user->xpLedger()->sum('amount'));
        $this->assertEquals(5, UserKaiwaProgress::where('user_id', $user->id)->value('attempts'));
    }

    public function test_index_returns_progress_keyed_by_scenario_and_only_for_the_current_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAsToken($user)->postJson('/api/kaiwa/progress/l5-s1', ['perfect' => true])->assertOk();
        $this->actingAsToken($other)->postJson('/api/kaiwa/progress/l5-s2', ['perfect' => false])->assertOk();

        $progress = $this->actingAsToken($user)->getJson('/api/kaiwa/progress')->assertOk()->json('progress');

        $this->assertSame(['l5-s1' => ['done' => true, 'crown' => true]], $progress);
    }

    public function test_notification_label_names_lesson_and_scenario(): void
    {
        $user = User::factory()->create();

        $this->actingAsToken($user)->postJson('/api/kaiwa/progress/l5-s3', ['perfect' => false])->assertOk();

        $subtitle = json_decode(UserNotification::where('user_id', $user->id)->firstOrFail()->subtitle, true);

        $this->assertSame('Pelajaran 5 · Skenario 3', $subtitle['lesson']);
        $this->assertEquals(10, $subtitle['xp']);
    }

    public function test_perfect_flag_is_required_and_set_key_format_is_restricted(): void
    {
        $client = $this->actingAsToken(User::factory()->create());

        $client->postJson('/api/kaiwa/progress/l2-s1', [])->assertStatus(422);
        $client->postJson('/api/kaiwa/progress/L2_S1', ['perfect' => true])->assertNotFound();
    }

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/kaiwa/progress')->assertUnauthorized();
        $this->postJson('/api/kaiwa/progress/l2-s1', ['perfect' => true])->assertUnauthorized();
    }
}
