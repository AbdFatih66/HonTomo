<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Akses paket Tes JLPT:
 *  - paket soal asli ('private') khusus admin (config jlpt.packs.private.admin_only);
 *  - sesi yang sedang berjalan harus bisa dilanjutkan setelah user keluar dari ujian.
 */
class JlptPackAccessTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsToken(User $user): static
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($user->createToken('spa')->plainTextToken);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_non_admin_does_not_see_the_original_pack(): void
    {
        $keys = collect($this->actingAsToken(User::factory()->create())->getJson('/api/jlpt-test/packs')->assertOk()->json('packs'))->pluck('key');

        $this->assertFalse($keys->contains('private'));
        $this->assertTrue($keys->contains('n5-test-1'));
    }

    public function test_admin_sees_the_original_pack_flagged_admin_only(): void
    {
        $packs = collect($this->actingAsToken($this->admin())->getJson('/api/jlpt-test/packs')->assertOk()->json('packs'))->keyBy('key');

        $this->assertTrue($packs->has('private'));
        $this->assertTrue($packs['private']['admin_only']);
        $this->assertFalse($packs['n5-test-1']['admin_only']);
    }

    public function test_non_admin_cannot_open_start_or_abandon_the_original_pack(): void
    {
        $user = User::factory()->create();

        $this->actingAsToken($user)->getJson('/api/jlpt-test/packs/private')->assertNotFound();
        $this->actingAsToken($user)->postJson('/api/jlpt-test/packs/private/attempts', ['mode' => 'strict'])->assertNotFound();
        $this->actingAsToken($user)->deleteJson('/api/jlpt-test/packs/private/attempts/current')->assertNotFound();
    }

    public function test_non_admin_can_still_use_the_other_pack(): void
    {
        $user = User::factory()->create();

        $this->actingAsToken($user)->postJson('/api/jlpt-test/packs/n5-test-1/attempts', ['mode' => 'strict'])->assertOk();
    }

    public function test_admin_can_start_the_original_pack(): void
    {
        $this->actingAsToken($this->admin())->postJson('/api/jlpt-test/packs/private/attempts', ['mode' => 'strict'])
            ->assertOk()
            ->assertJsonPath('pack', 'private')
            ->assertJsonPath('admin_only', true);
    }

    public function test_attempt_on_original_pack_is_locked_once_user_is_no_longer_admin(): void
    {
        $user = $this->admin();
        $attempt = $this->actingAsToken($user)->postJson('/api/jlpt-test/packs/private/attempts', ['mode' => 'strict'])->json('attempt_id');
        $this->actingAsToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/start")->assertOk();

        $user->forceFill(['role' => 'user'])->save();

        $this->actingAsToken($user)->getJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi")->assertNotFound();
        $this->actingAsToken($user)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertNotFound();
    }

    public function test_running_section_can_be_resumed_after_leaving_the_exam(): void
    {
        $user = User::factory()->create();
        $attempt = $this->actingAsToken($user)->postJson('/api/jlpt-test/packs/n5-test-1/attempts', ['mode' => 'strict'])->json('attempt_id');
        $this->actingAsToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/start")->assertOk();

        // user keluar dari ujian → halaman paket dimuat ulang
        $first = collect($this->actingAsToken($user)->getJson('/api/jlpt-test/packs/n5-test-1')->assertOk()->json('sections'))->firstWhere('key', 'mojigoi');

        $this->assertSame('running', $first['status']);
        $this->assertTrue($first['can_start'], 'Sesi yang berjalan harus punya tombol lanjutkan.');

        // melanjutkan = start ulang; tidak boleh mereset timer / menolak
        $this->actingAsToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/start")->assertOk();
    }

    public function test_later_sections_stay_locked_while_an_earlier_one_is_running(): void
    {
        $user = User::factory()->create();
        $attempt = $this->actingAsToken($user)->postJson('/api/jlpt-test/packs/n5-test-1/attempts', ['mode' => 'strict'])->json('attempt_id');
        $this->actingAsToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/start")->assertOk();

        $sections = collect($this->actingAsToken($user)->getJson('/api/jlpt-test/packs/n5-test-1')->json('sections'))->keyBy('key');

        $this->assertFalse($sections['bunpou_dokkai']['can_start']);
        $this->assertFalse($sections['chokai']['can_start']);
    }
}
