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

    /** @return array<int, list<string>> id pelajaran (urutan index.json) => id skenario */
    private function lessonIds(): array
    {
        $dir = public_path('data/kaiwa');
        $out = [];

        foreach (json_decode(file_get_contents("{$dir}/index.json"), true)['lessons'] as $entry) {
            $lesson = json_decode(file_get_contents("{$dir}/{$entry['file']}"), true);
            $out[$entry['id']] = array_column($lesson['scenarios'], 'id');
        }

        return $out;
    }

    /** Tandai selesai semua skenario pelajaran 1..$lesson-1 (tanpa XP) supaya pelajaran $lesson terbuka. */
    private function unlockUpTo(User $user, int $lesson): User
    {
        foreach ($this->lessonIds() as $id => $scenarioIds) {
            if ($id >= $lesson)
                break;

            foreach ($scenarioIds as $key)
                UserKaiwaProgress::create(['user_id' => $user->id, 'set_key' => $key, 'done' => true, 'attempts' => 1]);
        }

        return $user;
    }

    public function test_first_completion_awards_ten_xp_and_marks_done(): void
    {
        $user = User::factory()->create();

        $this->actingAsToken($user)
            ->postJson('/api/kaiwa/progress/l1-s1', ['perfect' => false])
            ->assertOk()
            ->assertJson(['set_key' => 'l1-s1', 'done' => true, 'crown' => false, 'xp' => 10]);

        $this->assertEquals(10, $user->xpLedger()->sum('amount'));
        $this->assertSame('kaiwa_completed', $user->xpLedger()->first()->source);
    }

    public function test_perfect_first_run_awards_fifteen_xp_in_one_go(): void
    {
        $user = User::factory()->create();

        $this->actingAsToken($user)
            ->postJson('/api/kaiwa/progress/l1-s2', ['perfect' => true])
            ->assertOk()
            ->assertJson(['done' => true, 'crown' => true, 'xp' => 15]);

        $this->assertEquals(15, $user->xpLedger()->sum('amount'));
    }

    public function test_crown_is_awarded_once_even_on_a_later_replay(): void
    {
        $user = User::factory()->create();
        $client = $this->actingAsToken($user);

        $client->postJson('/api/kaiwa/progress/l1-s3', ['perfect' => false])->assertJson(['xp' => 10]);
        $client->postJson('/api/kaiwa/progress/l1-s3', ['perfect' => false])->assertJson(['xp' => 0, 'crown' => false]);
        $client->postJson('/api/kaiwa/progress/l1-s3', ['perfect' => true])->assertJson(['xp' => 5, 'crown' => true]);
        // sudah dapat mahkota: main ulang tidak menambah XP dan mahkota tidak hilang
        $client->postJson('/api/kaiwa/progress/l1-s3', ['perfect' => true])->assertJson(['xp' => 0]);
        $client->postJson('/api/kaiwa/progress/l1-s3', ['perfect' => false])->assertJson(['xp' => 0, 'crown' => true]);

        $this->assertEquals(15, $user->xpLedger()->sum('amount'));
        $this->assertEquals(5, UserKaiwaProgress::where('user_id', $user->id)->value('attempts'));
    }

    public function test_index_returns_progress_keyed_by_scenario_and_only_for_the_current_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAsToken($user)->postJson('/api/kaiwa/progress/l1-s1', ['perfect' => true])->assertOk();
        $this->actingAsToken($other)->postJson('/api/kaiwa/progress/l1-s2', ['perfect' => false])->assertOk();

        $progress = $this->actingAsToken($user)->getJson('/api/kaiwa/progress')->assertOk()->json('progress');

        $this->assertSame(['l1-s1' => ['done' => true, 'crown' => true]], $progress);
    }

    public function test_notification_label_names_lesson_and_scenario(): void
    {
        $user = User::factory()->create();

        $this->actingAsToken($user)->postJson('/api/kaiwa/progress/l1-s3', ['perfect' => false])->assertOk();

        $subtitle = json_decode(UserNotification::where('user_id', $user->id)->firstOrFail()->subtitle, true);

        $this->assertSame('Pelajaran 1 · Skenario 3', $subtitle['lesson']);
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

    public function test_unknown_scenario_id_is_rejected_and_gives_no_xp(): void
    {
        $user = User::factory()->create();
        $client = $this->actingAsToken($user);

        // format valid menurut route, tetapi tidak ada di materi
        $client->postJson('/api/kaiwa/progress/l99-s1', ['perfect' => true])->assertNotFound();
        $client->postJson('/api/kaiwa/progress/l2-s999', ['perfect' => true])->assertNotFound();

        $this->assertSame(0, UserKaiwaProgress::where('user_id', $user->id)->count());
        $this->assertEquals(0, $user->xpLedger()->sum('amount'));
    }

    public function test_last_scenario_of_the_last_lesson_is_accepted(): void
    {
        $this->actingAsToken($this->unlockUpTo(User::factory()->create(), 25))
            ->postJson('/api/kaiwa/progress/l25-s4', ['perfect' => false])
            ->assertOk()
            ->assertJson(['set_key' => 'l25-s4', 'xp' => 10]);
    }

    public function test_later_lesson_is_locked_until_every_earlier_scenario_is_done(): void
    {
        $user = User::factory()->create();
        $client = $this->actingAsToken($user);

        $client->postJson('/api/kaiwa/progress/l2-s1', ['perfect' => true])
            ->assertForbidden()
            ->assertJson(['blocked_by' => 1]);

        $this->assertSame(0, UserKaiwaProgress::where('user_id', $user->id)->count());
        $this->assertEquals(0, $user->xpLedger()->sum('amount'));

        // semua skenario Pelajaran 1 kecuali satu: masih terkunci
        $ids = $this->lessonIds()[1];
        $last = array_pop($ids);

        foreach ($ids as $key)
            $client->postJson("/api/kaiwa/progress/{$key}", ['perfect' => false])->assertOk();

        $client->postJson('/api/kaiwa/progress/l2-s1', ['perfect' => false])->assertForbidden();

        $client->postJson("/api/kaiwa/progress/{$last}", ['perfect' => false])->assertOk();
        $client->postJson('/api/kaiwa/progress/l2-s1', ['perfect' => false])->assertOk()->assertJson(['xp' => 10]);

        // Pelajaran 3 tetap terkunci: Pelajaran 2 belum tuntas
        $client->postJson('/api/kaiwa/progress/l3-s1', ['perfect' => false])
            ->assertForbidden()
            ->assertJson(['blocked_by' => 2]);
    }

    public function test_admin_is_not_subject_to_the_lesson_lock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAsToken($admin)
            ->postJson('/api/kaiwa/progress/l10-s1', ['perfect' => false])
            ->assertOk()
            ->assertJson(['set_key' => 'l10-s1', 'xp' => 10]);
    }

    public function test_an_already_finished_scenario_can_be_replayed_even_if_an_earlier_lesson_is_incomplete(): void
    {
        $user = User::factory()->create();
        // kasus: skenario baru ditambahkan ke pelajaran lama setelah user tamat pelajaran yang lebih maju
        UserKaiwaProgress::create(['user_id' => $user->id, 'set_key' => 'l3-s1', 'done' => true, 'attempts' => 1]);

        $this->actingAsToken($user)
            ->postJson('/api/kaiwa/progress/l3-s1', ['perfect' => true])
            ->assertOk()
            ->assertJson(['done' => true, 'crown' => true, 'xp' => 5]);
    }

    public function test_situation_scenario_gives_xp_without_any_lesson_unlocked(): void
    {
        $user = User::factory()->create();

        $this->actingAsToken($user)
            ->postJson('/api/kaiwa/progress/mensetsu-s1', ['perfect' => true])
            ->assertOk()
            ->assertJson(['set_key' => 'mensetsu-s1', 'done' => true, 'crown' => true, 'xp' => 15]);

        $this->assertEquals(15, $user->xpLedger()->sum('amount'));

        $progress = $this->actingAsToken($user)->getJson('/api/kaiwa/progress')->assertOk()->json('progress');
        $this->assertSame(['mensetsu-s1' => ['done' => true, 'crown' => true]], $progress);
    }

    public function test_situation_notification_label_names_the_set_and_scenario(): void
    {
        $user = User::factory()->create();

        $this->actingAsToken($user)->postJson('/api/kaiwa/progress/mensetsu-s3', ['perfect' => false])->assertOk();

        $subtitle = json_decode(UserNotification::where('user_id', $user->id)->firstOrFail()->subtitle, true);

        $this->assertSame('Mensetsu (wawancara kerja) · Skenario 3', $subtitle['lesson']);
    }

    public function test_unknown_situation_ids_are_rejected(): void
    {
        $user = User::factory()->create();
        $client = $this->actingAsToken($user);

        $client->postJson('/api/kaiwa/progress/mensetsu-s999', ['perfect' => true])->assertNotFound();
        $client->postJson('/api/kaiwa/progress/karangan-s1', ['perfect' => true])->assertNotFound();

        $this->assertSame(0, UserKaiwaProgress::where('user_id', $user->id)->count());
        $this->assertEquals(0, $user->xpLedger()->sum('amount'));
    }

    public function test_situation_progress_does_not_count_toward_the_lesson_lock(): void
    {
        $user = User::factory()->create();
        $client = $this->actingAsToken($user);

        $client->postJson('/api/kaiwa/progress/mensetsu-s1', ['perfect' => false])->assertOk();

        // menamatkan situasi tidak membuka Pelajaran 2
        $client->postJson('/api/kaiwa/progress/l2-s1', ['perfect' => false])->assertForbidden();
    }
}
