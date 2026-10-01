<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserXp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Tes JLPT (simulasi ujian). Ketiga sesi (moji-goi, bunpou-dokkai, chokai) sudah
 * punya bank soal dan aktif di config.
 *
 * Test mekanik ujian (timer, autosave, XP, dst.) berjalan dengan SATU sesi
 * aktif (moji-goi) lewat setUp() — rekapnya lalu bersifat sementara. Test yang
 * butuh sesi lain mengaktifkannya lagi: enableBunpou(), enableChokai(), atau
 * useFullBanks() untuk ketiganya.
 */
class JlptTestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Kunci resmi 正答表 untuk sesi moji-goi (33 soal), ditulis ulang di sini
     * supaya salah ketik di bank soal JSON ketahuan.
     */
    private const OFFICIAL_MOJIGOI_KEY = [
        1 => 2, 2 => 4, 3 => 1, 4 => 2, 5 => 2, 6 => 3, 7 => 4, 8 => 2, 9 => 2, 10 => 3,
        11 => 1, 12 => 2, 13 => 3, 14 => 4, 15 => 4, 16 => 1, 17 => 3, 18 => 1, 19 => 4, 20 => 3,
        21 => 1, 22 => 1, 23 => 3, 24 => 2, 25 => 1, 26 => 4, 27 => 2, 28 => 4, 29 => 3, 30 => 1,
        31 => 2, 32 => 3, 33 => 4,
    ];

    /**
     * Kunci resmi 正答表 untuk sesi bunpou-dokkai (32 soal), ditulis ulang di sini
     * supaya salah ketik di bank soal JSON ketahuan.
     */
    private const OFFICIAL_BUNPOU_KEY = [
        1 => 2, 2 => 3, 3 => 2, 4 => 4, 5 => 3, 6 => 2, 7 => 3, 8 => 1, 9 => 3, 10 => 3,
        11 => 1, 12 => 1, 13 => 4, 14 => 2, 15 => 4, 16 => 1, 17 => 4, 18 => 1, 19 => 4, 20 => 2,
        21 => 2, 22 => 4, 23 => 2, 24 => 4, 25 => 3, 26 => 1, 27 => 2, 28 => 3, 29 => 4, 30 => 4,
        31 => 1, 32 => 2,
    ];

    /** Kunci resmi 正答表 untuk sesi chokai (id soal = "{mondai}-{no}"). */
    private const OFFICIAL_CHOKAI_KEY = [
        '1-1' => 2, '1-2' => 2, '1-3' => 3, '1-4' => 3, '1-5' => 1, '1-6' => 4, '1-7' => 4,
        '2-1' => 4, '2-2' => 3, '2-3' => 2, '2-4' => 4, '2-5' => 1, '2-6' => 3,
        '3-1' => 1, '3-2' => 3, '3-3' => 2, '3-4' => 3, '3-5' => 2,
        '4-1' => 1, '4-2' => 1, '4-3' => 2, '4-4' => 3, '4-5' => 3, '4-6' => 1,
    ];

    /** Jawaban contoh (例) tiap もんだい chokai, tercetak di 正答表. */
    private const OFFICIAL_CHOKAI_EXAMPLES = [1 => 3, 2 => 3, 3 => 3, 4 => 2];

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'jlpt.levels.N5.sections.bunpou_dokkai.enabled' => false,
            'jlpt.levels.N5.sections.chokai.enabled' => false,
        ]);
    }

    private function enableBunpou(): void
    {
        config(['jlpt.levels.N5.sections.bunpou_dokkai.enabled' => true]);
    }

    private function enableChokai(): void
    {
        config(['jlpt.levels.N5.sections.chokai.enabled' => true]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function asToken(User $user): static
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($user->createToken('spa')->plainTextToken);
    }

    /** @return array<int, int> id soal => jawaban benar, dibaca dari bank JSON moji-goi. */
    private function key(): array
    {
        return $this->keyOf('mojigoi');
    }

    /** @return array<int, int> id soal => jawaban benar, dibaca dari bank JSON sesi. */
    private function keyOf(string $section): array
    {
        return collect($this->bank($section)['questions'])->pluck('answer', 'id')->all();
    }

    private function bank(string $section): array
    {
        return json_decode(file_get_contents(resource_path("lang-data/jlpt/n5/{$section}.json")), true);
    }

    /**
     * Aktifkan ketiga sesi (bank soal asli).
     *
     * @return array<string, array> kunci jawaban per sesi
     */
    private function useFullBanks(): array
    {
        $this->enableBunpou();
        $this->enableChokai();

        return [
            'mojigoi' => $this->key(),
            'bunpou_dokkai' => $this->keyOf('bunpou_dokkai'),
            'chokai' => $this->keyOf('chokai'),
        ];
    }

    private function startMojigoi(User $user): int
    {
        $state = $this->asToken($user)->postJson('/api/jlpt-test/N5/attempts')->assertOk()->json();
        $attempt = $state['attempt_id'];
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/start")->assertOk();

        return $attempt;
    }

    public function test_endpoints_require_authentication(): void
    {
        $this->getJson('/api/jlpt-test/N5')->assertUnauthorized();
        $this->postJson('/api/jlpt-test/N5/attempts')->assertUnauthorized();
    }

    public function test_unknown_level_is_404(): void
    {
        $this->asToken(User::factory()->create())->getJson('/api/jlpt-test/N9')->assertNotFound();
    }

    public function test_state_never_exposes_the_answer_key(): void
    {
        $user = User::factory()->create();
        $json = $this->asToken($user)->getJson('/api/jlpt-test/N5')->assertOk()->getContent();

        $this->assertStringNotContainsString('answers', $json);
        $this->assertStringContainsString('mojigoi', $json);
    }

    public function test_question_bank_matches_the_official_answer_key(): void
    {
        $this->assertSame(self::OFFICIAL_MOJIGOI_KEY, $this->key());
    }

    public function test_question_bank_is_well_formed(): void
    {
        $bank = json_decode(file_get_contents(resource_path('lang-data/jlpt/n5/mojigoi.json')), true);

        $this->assertCount(config('jlpt.levels.N5.sections.mojigoi.questions'), $bank['questions']);
        $ids = array_column($bank['questions'], 'id');
        $this->assertSame($ids, array_values(array_unique($ids)), 'id soal harus unik');

        foreach ($bank['questions'] as $q) {
            $this->assertCount(4, $q['choices'], "soal {$q['id']}");
            $this->assertContains($q['answer'], [1, 2, 3, 4], "soal {$q['id']}");
            $this->assertArrayHasKey($q['mondai'], $bank['mondai'], "soal {$q['id']}");
        }
    }

    public function test_every_section_has_a_bank_matching_the_configured_question_count(): void
    {
        foreach (['mojigoi', 'bunpou_dokkai', 'chokai'] as $section) {
            $bank = $this->bank($section);

            $this->assertCount(config("jlpt.levels.N5.sections.{$section}.questions"), $bank['questions'], $section);
            $this->assertSame($section, $bank['section']);
        }
    }

    public function test_bunpou_question_bank_matches_the_official_answer_key(): void
    {
        $this->assertSame(self::OFFICIAL_BUNPOU_KEY, $this->keyOf('bunpou_dokkai'));
    }

    public function test_bunpou_question_bank_is_well_formed(): void
    {
        $bank = $this->bank('bunpou_dokkai');

        $this->assertSame(32, count($bank['questions']));
        $this->assertSame(range(1, 32), array_column($bank['questions'], 'id'), 'id soal harus 1–32 berurutan');
        $this->assertSame(50, $bank['minutes']);

        foreach ($bank['questions'] as $q) {
            $id = $q['id'];
            $this->assertCount(4, $q['choices'], "soal {$id}");
            $this->assertContains($q['answer'], [1, 2, 3, 4], "soal {$id}");
            $this->assertArrayHasKey($q['mondai'], $bank['mondai'], "soal {$id}");

            // Soal tanpa kalimat (mis. 22–26) harus berdiri di atas bacaan.
            if (($q['stem'] ?? '') === '')
                $this->assertNotEmpty($q['passage'] ?? null, "soal {$id} tanpa stem harus punya bacaan");

            if (isset($q['passage']))
                $this->assertArrayHasKey($q['passage'], $bank['passages'], "soal {$id}");

            if (isset($q['choice_art']))
                $this->assertSame('room', $q['choice_art'], "soal {$id}");

            // もんだい 2 (★): tiga kolom kosong + satu kolom ★ pada tiap soal.
            if ($q['mondai'] === 2) {
                $this->assertSame(1, substr_count($q['stem'], '⟦★⟧'), "soal {$id}");
                $this->assertSame(3, substr_count($q['stem'], '⟦　⟧'), "soal {$id}");
            }
        }

        // Kotak bernomor di bacaan (mondai 3) harus sesuai nomor soal yang memakainya.
        foreach ($bank['questions'] as $q) {
            if ($q['mondai'] !== 3)
                continue;
            $this->assertStringContainsString('{{'.$q['id'].'}}', $bank['passages'][$q['passage']]['body'], "soal {$q['id']}");
        }

        $this->assertNotEmpty($bank['mondai']['2']['example']['howto']);
    }

    public function test_section_payload_has_the_questions_but_never_the_key(): void
    {
        $user = User::factory()->create();
        $attempt = $this->asToken($user)->postJson('/api/jlpt-test/N5/attempts')->json('attempt_id');

        // Sebelum sesi dimulai, soal belum dikirim sama sekali.
        $this->assertStringNotContainsString('questions', $this->asToken($user)->getJson('/api/jlpt-test/N5')->getContent());

        $start = $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/start")->assertOk();
        $resume = $this->asToken($user)->getJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi")->assertOk();

        foreach ([$start, $resume] as $res) {
            $questions = $res->json('test.questions');
            $this->assertCount(33, $questions);
            foreach ($questions as $q) {
                $this->assertArrayNotHasKey('answer', $q);
                $this->assertCount(4, $q['choices']);
            }
            $this->assertNotEmpty($res->json('test.mondai.1.instruction'));
        }

        // Autosave tidak membawa soal; setelah submit, soal tidak dikirim lagi.
        $save = $this->asToken($user)->putJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/answers", ['answers' => []])->assertOk();
        $this->assertArrayNotHasKey('test', $save->json());

        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", ['answers' => $this->key()])->assertOk();
        $this->asToken($user)->getJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi")->assertOk()->assertJsonPath('test', null);
    }

    public function test_only_available_sections_can_be_started_and_in_order(): void
    {
        $user = User::factory()->create();
        $attempt = $this->asToken($user)->postJson('/api/jlpt-test/N5/attempts')->json('attempt_id');

        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/start")
            ->assertStatus(422);
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/nope/start")
            ->assertNotFound();
    }

    public function test_starting_twice_returns_the_same_attempt_and_does_not_reset_the_timer(): void
    {
        $user = User::factory()->create();
        Carbon::setTestNow('2026-09-30 10:00:00');

        $a = $this->startMojigoi($user);
        $b = $this->asToken($user)->postJson('/api/jlpt-test/N5/attempts')->json('attempt_id');
        $this->assertSame($a, $b);

        Carbon::setTestNow('2026-09-30 10:10:00'); // 10 menit berlalu
        $res = $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$a}/sections/mojigoi/start")->assertOk();

        $this->assertEqualsWithDelta(15 * 60, $res->json('remaining_seconds'), 2);
    }

    public function test_submit_grades_on_the_server_without_leaking_the_score(): void
    {
        $user = User::factory()->create();
        $attempt = $this->startMojigoi($user);

        $res = $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", [
            'answers' => $this->key(),
        ])->assertOk();

        $res->assertJsonPath('submitted', true)->assertJsonPath('test_completed', true);
        $this->assertArrayNotHasKey('score', $res->json());
        $this->assertArrayNotHasKey('correct', $res->json());
    }

    public function test_recap_scores_and_is_provisional_while_sections_are_missing(): void
    {
        $user = User::factory()->create();
        $attempt = $this->startMojigoi($user);

        // benar semua kecuali no. 1 (salah) dan no. 2 (kosong)
        $answers = $this->key();
        $answers[1] = $answers[1] === 1 ? 2 : 1;
        unset($answers[2]);

        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", ['answers' => $answers])->assertOk();

        $recap = $this->asToken($user)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertOk()->json();

        $mojigoi = collect($recap['sections'])->firstWhere('key', 'mojigoi');
        $this->assertSame(31, $mojigoi['correct']);
        $this->assertSame(33, $mojigoi['total']);
        $this->assertSame(1, $mojigoi['breakdown'][0]['unanswered']);
        $this->assertTrue($recap['provisional']);
        $this->assertNull($recap['passed']);
        $this->assertSame(180, $recap['total_max']);
    }

    public function test_recap_is_locked_until_the_test_is_finished(): void
    {
        $user = User::factory()->create();
        $attempt = $this->startMojigoi($user);

        $this->asToken($user)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertStatus(409);
    }

    public function test_a_submitted_section_cannot_be_redone_or_resubmitted(): void
    {
        $user = User::factory()->create();
        $attempt = $this->startMojigoi($user);
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", ['answers' => $this->key()])->assertOk();

        // tes sudah selesai → tidak aktif lagi
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", ['answers' => $this->key()])->assertStatus(409);
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/start")->assertStatus(409);
    }

    public function test_answers_sent_long_after_time_is_up_are_ignored_in_favour_of_the_autosave(): void
    {
        $user = User::factory()->create();
        Carbon::setTestNow('2026-09-30 10:00:00');
        $attempt = $this->startMojigoi($user);

        // autosave di menit ke-5: hanya no. 1 terjawab benar
        Carbon::setTestNow('2026-09-30 10:05:00');
        $this->asToken($user)->putJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/answers", [
            'answers' => [1 => $this->key()[1]],
        ])->assertOk();

        // 1 jam kemudian klien mengirim jawaban lengkap — harus ditolak (diabaikan)
        Carbon::setTestNow('2026-09-30 11:00:00');
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", ['answers' => $this->key()])->assertOk();

        $recap = $this->asToken($user)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->json();
        $mojigoi = collect($recap['sections'])->firstWhere('key', 'mojigoi');

        $this->assertSame(1, $mojigoi['correct']);
        $this->assertTrue($mojigoi['timed_out']);
    }

    public function test_submit_within_the_grace_window_still_counts(): void
    {
        $user = User::factory()->create();
        Carbon::setTestNow('2026-09-30 10:00:00');
        $attempt = $this->startMojigoi($user);

        Carbon::setTestNow('2026-09-30 10:25:10'); // 10 detik setelah batas 25 menit
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", ['answers' => $this->key()])->assertOk();

        $recap = $this->asToken($user)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->json();
        $this->assertSame(33, collect($recap['sections'])->firstWhere('key', 'mojigoi')['correct']);
    }

    public function test_answer_values_outside_1_to_4_are_rejected(): void
    {
        $user = User::factory()->create();
        $attempt = $this->startMojigoi($user);

        foreach ([9, 0, 'abc'] as $bad) {
            $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", ['answers' => [1 => $bad]])
                ->assertStatus(422);
            $this->asToken($user)->putJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/answers", ['answers' => [1 => $bad]])
                ->assertStatus(422);
        }

        // Ditolak sebelum diproses, jadi sesi masih berjalan dan bisa dikirim ulang.
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", ['answers' => $this->key()])->assertOk();
    }

    public function test_unknown_question_ids_and_empty_answers_are_dropped(): void
    {
        $user = User::factory()->create();
        $attempt = $this->startMojigoi($user);

        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", [
            'answers' => [1 => null, 999 => 1, 'x' => 2],
        ])->assertOk();

        $recap = $this->asToken($user)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->json();
        $this->assertSame(0, collect($recap['sections'])->firstWhere('key', 'mojigoi')['correct']);
    }

    public function test_another_users_attempt_is_not_reachable(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $attempt = $this->startMojigoi($owner);

        $this->asToken($intruder)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertNotFound();
        $this->asToken($intruder)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", ['answers' => []])->assertNotFound();
    }

    public function test_xp_is_awarded_only_the_first_time_a_section_is_finished(): void
    {
        $user = User::factory()->create();

        $first = $this->startMojigoi($user);
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$first}/sections/mojigoi/submit", ['answers' => $this->key()])->assertOk();

        $second = $this->startMojigoi($user);
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$second}/sections/mojigoi/submit", ['answers' => $this->key()])->assertOk();

        $this->assertSame(1, UserXp::where('user_id', $user->id)->where('source', 'jlpt_section_completed')->count());
    }

    public function test_abandoning_lets_the_user_start_over(): void
    {
        $user = User::factory()->create();
        $first = $this->startMojigoi($user);

        $this->asToken($user)->deleteJson('/api/jlpt-test/N5/attempts/current')->assertOk()->assertJsonPath('attempt_id', null);

        $second = $this->asToken($user)->postJson('/api/jlpt-test/N5/attempts')->json('attempt_id');
        $this->assertNotSame($first, $second);
    }

    public function test_scoring_scale_and_verdict_follow_the_official_structure_once_all_sections_exist(): void
    {
        // Simulasi: semua sesi aktif dan benar semua → lulus 180/180.
        $keys = $this->useFullBanks();
        $user = User::factory()->create();
        $attempt = $this->asToken($user)->postJson('/api/jlpt-test/N5/attempts')->json('attempt_id');

        foreach (['mojigoi', 'bunpou_dokkai', 'chokai'] as $section) {
            $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/{$section}/start")->assertOk();
            $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/{$section}/submit", [
                'answers' => $keys[$section],
            ])->assertOk();
        }

        $recap = $this->asToken($user)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertOk()->json();

        $this->assertFalse($recap['provisional']);
        $this->assertTrue($recap['passed']);
        $this->assertSame(180, $recap['total_score']);

        // Data untuk sertifikat simulasi HonTomo.
        $this->assertSame($user->name, $recap['participant']);
        $this->assertMatchesRegularExpression('/^HT-N5-\d{8}-'.sprintf('%06d', $attempt).'$/', $recap['certificate_no']);
    }

    public function test_a_group_below_its_minimum_fails_even_with_a_passing_total(): void
    {
        $keys = $this->useFullBanks();
        $user = User::factory()->create();
        $attempt = $this->asToken($user)->postJson('/api/jlpt-test/N5/attempts')->json('attempt_id');

        // Bahasa & membaca sempurna (120), menyimak kosong (0) → total 120 ≥ 80 tapi
        // menyimak < 19 → tidak lulus.
        foreach (['mojigoi', 'bunpou_dokkai', 'chokai'] as $section) {
            $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/{$section}/start")->assertOk();
            $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/{$section}/submit", [
                'answers' => $section === 'chokai' ? [] : $keys[$section],
            ])->assertOk();
        }

        $recap = $this->asToken($user)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->json();

        $this->assertSame(120, $recap['total_score']);
        $this->assertFalse($recap['passed']);
    }

    public function test_bunpou_payload_ships_passages_but_never_the_key(): void
    {
        $this->enableBunpou();
        $user = User::factory()->create();
        $attempt = $this->startMojigoi($user);
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", ['answers' => $this->key()])->assertOk();

        $start = $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/start")->assertOk();
        $resume = $this->asToken($user)->getJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai")->assertOk();

        foreach ([$start, $resume] as $res) {
            $this->assertSame(50, $res->json('minutes'));
            $questions = $res->json('test.questions');
            $this->assertCount(32, $questions);
            foreach ($questions as $q)
                $this->assertArrayNotHasKey('answer', $q);

            foreach (['m3-1', 'm3-2', 'm4-1', 'm4-2', 'm4-3', 'm5-1', 'm6-1'] as $passage)
                $this->assertNotEmpty($res->json("test.passages.{$passage}"), $passage);

            $this->assertNotEmpty($res->json('test.mondai.6.instruction'));
        }
    }

    public function test_bunpou_needs_mojigoi_first_and_two_sections_finish_the_test(): void
    {
        $this->enableBunpou();
        $user = User::factory()->create();
        $attempt = $this->asToken($user)->postJson('/api/jlpt-test/N5/attempts')->json('attempt_id');

        // Urutan ujian dijaga: bunpou tidak bisa dimulai sebelum moji-goi dikirim.
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/start")->assertStatus(422);

        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/start")->assertOk();
        $first = $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", ['answers' => $this->key()])->assertOk();
        $first->assertJsonPath('test_completed', false);

        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/start")->assertOk();
        $second = $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/submit", [
            'answers' => $this->keyOf('bunpou_dokkai'),
        ])->assertOk();
        $second->assertJsonPath('test_completed', true);

        // Bahasa & membaca sempurna = 120, tetapi chokai belum ada → rekap sementara.
        $recap = $this->asToken($user)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertOk()->json();
        $bunpou = collect($recap['sections'])->firstWhere('key', 'bunpou_dokkai');

        $this->assertSame(32, $bunpou['correct']);
        $this->assertSame(32, $bunpou['total']);
        $this->assertCount(6, $bunpou['breakdown']);
        $this->assertTrue($recap['provisional']);
        $this->assertNull($recap['passed']);
        $this->assertSame(120, $recap['total_score']);
    }

    public function test_bunpou_scores_only_correct_answers(): void
    {
        $this->enableBunpou();
        $user = User::factory()->create();
        $attempt = $this->startMojigoi($user);
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", ['answers' => $this->key()])->assertOk();
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/start")->assertOk();

        // no. 28 (gambar ruangan) dijawab salah, no. 32 dikosongkan
        $answers = $this->keyOf('bunpou_dokkai');
        $answers[28] = $answers[28] === 1 ? 2 : 1;
        unset($answers[32]);

        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/submit", ['answers' => $answers])->assertOk();

        $recap = $this->asToken($user)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->json();
        $bunpou = collect($recap['sections'])->firstWhere('key', 'bunpou_dokkai');
        $mondai4 = collect($bunpou['breakdown'])->firstWhere('mondai', 4);
        $mondai6 = collect($bunpou['breakdown'])->firstWhere('mondai', 6);

        $this->assertSame(30, $bunpou['correct']);
        $this->assertSame(2, $mondai4['correct']);
        $this->assertSame(1, $mondai6['unanswered']);
    }

    public function test_chokai_question_bank_matches_the_official_answer_key(): void
    {
        $this->assertSame(self::OFFICIAL_CHOKAI_KEY, $this->keyOf('chokai'));

        foreach (self::OFFICIAL_CHOKAI_EXAMPLES as $mondai => $answer)
            $this->assertSame($answer, $this->bank('chokai')['mondai'][(string) $mondai]['example']['answer'], "れい もんだい {$mondai}");
    }

    public function test_chokai_question_bank_is_well_formed(): void
    {
        $bank = $this->bank('chokai');
        $tracks = [$bank['audio']['intro'], $bank['audio']['outro']];

        $this->assertSame(30, $bank['minutes']);
        $this->assertSame(['1', '2', '3', '4'], array_keys($bank['mondai']));

        foreach ($bank['mondai'] as $no => $m) {
            $this->assertNotEmpty($m['instruction'], "もんだい {$no}");
            $this->assertIsInt($m['audio'], "もんだい {$no} audio");
            $this->assertIsInt($m['example']['audio'], "れい {$no} audio");
            $this->assertNotEmpty($m['example']['script'], "れい {$no} naskah");
            array_push($tracks, $m['audio'], $m['example']['audio']);
            if (isset($m['audio_before']))
                $tracks[] = $m['audio_before'];
        }

        foreach ($bank['questions'] as $q) {
            $id = $q['id'];

            $this->assertSame("{$q['mondai']}-{$q['no']}", $id);
            $this->assertNotEmpty($q['script'], $id);
            $this->assertIsInt($q['audio'], $id);
            $tracks[] = $q['audio'];

            // pilihan: teks tercetak (choices) ATAU hanya terdengar (choice_count); gambar opsional
            $count = $q['choices'] ? count($q['choices']) : $q['choice_count'];
            $this->assertContains($count, [3, 4], $id);
            $this->assertLessThanOrEqual($count, $q['answer'], $id);
            $this->assertGreaterThanOrEqual(1, $q['answer'], $id);

            foreach (array_filter([$q['image'] ?? null, isset($q['choice_art']) ? $q['choice_art'].'-1' : null]) as $art)
                $this->assertMatchesRegularExpression('/^(bags|talk-[1-5]|socks-\d|items-\d|acts-\d|food-\d)$/', $art, $id);
        }

        // Nomor trek = nomor awalan berkas audio 01–35, urutan putar rekaman: tiap nomor dipakai tepat sekali.
        sort($tracks);
        $this->assertSame(range(1, 35), $tracks);
    }

    /** Bangun folder audio sementara di public/ (dibersihkan di akhir test) dan arahkan config ke sana. */
    private function fakeAudioFolder(array $files): string
    {
        $root = 'audio-test-'.uniqid();
        $dir = public_path("{$root}/n5");
        mkdir($dir, 0777, true);
        foreach ($files as $name)
            file_put_contents("{$dir}/{$name}", '');
        config(['jlpt.audio_path' => "/{$root}"]);

        return public_path($root);
    }

    private function removeFolder(string $root): void
    {
        foreach (glob("{$root}/n5/*") ?: [] as $f)
            unlink($f);
        @rmdir("{$root}/n5");
        @rmdir($root);
    }

    public function test_audio_track_numbers_resolve_to_files_by_their_prefix(): void
    {
        // ejaan & tanda pisah yang tidak konsisten (modai/mondai, "－") tidak jadi masalah
        $root = $this->fakeAudioFolder(['04-modai1－1bun.mp3', '02-mondai1－setsumei.mp3', '19-chotto-yasumimashou.mp3']);

        try {
            $service = app(\App\Services\JlptTestService::class);

            $this->assertSame('04-modai1－1bun.mp3', $service->resolveAudio('N5', 4));
            $this->assertSame('19-chotto-yasumimashou.mp3', $service->resolveAudio('N5', 19));
            $this->assertNull($service->resolveAudio('N5', 5), 'trek 5 tidak ada');
            $this->assertNull($service->resolveAudio('N5', 40), '"04-" tidak boleh cocok dengan 40');
            $this->assertSame('02-mondai1－setsumei.mp3', $service->resolveAudio('N5', '02-mondai1－setsumei.mp3'));
            $this->assertNull($service->resolveAudio('N5', 'tidak-ada.mp3'));
        }
        finally {
            $this->removeFolder($root);
        }
    }

    public function test_chokai_payload_has_audio_urls_but_never_the_key_or_the_script(): void
    {
        $keys = $this->useFullBanks();
        $root = $this->fakeAudioFolder(['04-modai1－1bun.mp3', '35-owari.mp3']);

        try {
            $user = User::factory()->create();
            $attempt = $this->asToken($user)->postJson('/api/jlpt-test/N5/attempts')->json('attempt_id');

            foreach (['mojigoi', 'bunpou_dokkai'] as $section) {
                $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/{$section}/start")->assertOk();
                $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/{$section}/submit", ['answers' => $keys[$section]])->assertOk();
            }

            $start = $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/chokai/start")->assertOk();
            $resume = $this->asToken($user)->getJson("/api/jlpt-test/attempts/{$attempt}/sections/chokai")->assertOk();
            $base = config('jlpt.audio_path').'/n5/';

            foreach ([$start, $resume] as $res) {
                $this->assertSame(30, $res->json('minutes'));
                $this->assertCount(24, $res->json('test.questions'));

                foreach ($res->json('test.questions') as $q) {
                    $this->assertArrayNotHasKey('answer', $q);
                    $this->assertArrayNotHasKey('script', $q);
                    $this->assertStringStartsWith($base, $q['audio']);
                }

                foreach ($res->json('test.mondai') as $m) {
                    $this->assertStringStartsWith($base, $m['audio']);
                    $this->assertStringStartsWith($base, $m['example']['audio']);
                    $this->assertArrayNotHasKey('script', $m['example']);
                }

                // berkas yang ada → URL ter-encode (tanda "－" tidak boleh mentah); yang belum ada → pesan jelas
                $this->assertSame($base.rawurlencode('04-modai1－1bun.mp3'), $res->json('test.questions.0.audio'));
                $this->assertSame($base.'05-tidak-ditemukan.mp3', $res->json('test.questions.1.audio'));
                $this->assertSame($base.'35-owari.mp3', $res->json('test.audio.outro'));
                $this->assertSame($base.'01-tidak-ditemukan.mp3', $res->json('test.audio.intro'));
                $this->assertSame($base.'19-tidak-ditemukan.mp3', $res->json('test.mondai.3.audio_before'));

                // naskah audio tidak boleh bocor di bagian mana pun dari respons
                $this->assertStringNotContainsString('"script"', $res->getContent());
            }
        }
        finally {
            $this->removeFolder($root);
        }
    }

    public function test_chokai_grades_string_question_ids_and_reports_per_mondai(): void
    {
        $keys = $this->useFullBanks();
        $user = User::factory()->create();
        $attempt = $this->asToken($user)->postJson('/api/jlpt-test/N5/attempts')->json('attempt_id');

        foreach (['mojigoi', 'bunpou_dokkai'] as $section) {
            $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/{$section}/start")->assertOk();
            $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/{$section}/submit", ['answers' => $keys[$section]])->assertOk();
        }

        // semua benar kecuali 3-3 (salah) dan 4-6 (kosong)
        $answers = $keys['chokai'];
        $answers['3-3'] = $answers['3-3'] === 1 ? 2 : 1;
        unset($answers['4-6']);

        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/chokai/start")->assertOk();
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/chokai/submit", ['answers' => $answers])->assertOk();

        $recap = $this->asToken($user)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertOk()->json();
        $chokai = collect($recap['sections'])->firstWhere('key', 'chokai');

        $this->assertSame(22, $chokai['correct']);
        $this->assertSame(24, $chokai['total']);
        $this->assertCount(4, $chokai['breakdown']);
        $this->assertSame(4, collect($chokai['breakdown'])->firstWhere('mondai', 3)['correct']);
        $this->assertSame(1, collect($chokai['breakdown'])->firstWhere('mondai', 4)['unanswered']);
        $this->assertFalse($recap['provisional']);
        $this->assertTrue($recap['passed']);
    }

    public function test_chokai_audio_files_exist_in_the_public_folder(): void
    {
        if (! is_dir(public_path('audio/jlpt/n5')))
            $this->markTestSkipped('Folder public/audio/jlpt/n5 belum ada di lingkungan ini.');

        $bank = $this->bank('chokai');
        $tracks = array_values($bank['audio']);
        foreach ($bank['mondai'] as $m)
            array_push($tracks, $m['audio'], $m['example']['audio'], ...(isset($m['audio_before']) ? [$m['audio_before']] : []));
        foreach ($bank['questions'] as $q)
            $tracks[] = $q['audio'];

        $service = app(\App\Services\JlptTestService::class);
        $missing = array_values(array_filter($tracks, fn (int $n) => $service->resolveAudio('N5', $n) === null));
        sort($missing);

        $this->assertSame([], $missing, 'Nomor trek audio tanpa berkas di public/audio/jlpt/n5 (berawalan "NN-"): '.implode(', ', array_map(fn ($n) => sprintf('%02d', $n), $missing)));
    }
}
