<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tes JLPT N1 (soal asli, pack 'private-n1', KHUSUS ADMIN). Ketiga sesi aktif: moji-goi (問題1–4, nomor 1–25),
 * bunpou-dokkai (問題5–13, nomor 26–69) dan chokai (聴解, 36 soal; audio = lima rekaman utuh N1-chokai-1…5).
 */
class JlptN1Test extends TestCase
{
    use RefreshDatabase;

    /** Kunci resmi 正答表 N1 言語知識（文字・語彙）, 問題1–4 (nomor 1–25) — ditulis ulang agar salah ketik ketahuan. */
    private const OFFICIAL_MOJIGOI_KEY = [
        1 => 3, 2 => 1, 3 => 2, 4 => 2, 5 => 3, 6 => 4,
        7 => 3, 8 => 2, 9 => 1, 10 => 4, 11 => 3, 12 => 2, 13 => 2,
        14 => 4, 15 => 1, 16 => 4, 17 => 1, 18 => 4, 19 => 2,
        20 => 1, 21 => 3, 22 => 4, 23 => 1, 24 => 2, 25 => 3,
    ];

    /** Jumlah soal per もんだい (1–4). */
    private const MONDAI_SIZES = [1 => 6, 2 => 7, 3 => 6, 4 => 6];

    /** Kunci resmi 正答表 N1 言語知識（文法）・読解, 問題5–13 (nomor 26–69). */
    private const OFFICIAL_BUNPOU_KEY = [
        26 => 4, 27 => 1, 28 => 2, 29 => 4, 30 => 2, 31 => 3, 32 => 2, 33 => 4, 34 => 4, 35 => 1,
        36 => 1, 37 => 1, 38 => 4, 39 => 2, 40 => 3,
        41 => 3, 42 => 2, 43 => 4, 44 => 1, 45 => 1,
        46 => 2, 47 => 4, 48 => 3,
        49 => 4, 50 => 1, 51 => 3, 52 => 2, 53 => 1, 54 => 2, 55 => 3, 56 => 3, 57 => 4,
        58 => 1, 59 => 1, 60 => 2, 61 => 2,
        62 => 3, 63 => 2,
        64 => 4, 65 => 1, 66 => 4, 67 => 1,
        68 => 4, 69 => 1,
    ];

    /** Jumlah soal per もんだい bunpou-dokkai (5–13). */
    private const BUNPOU_MONDAI_SIZES = [5 => 10, 6 => 5, 7 => 5, 8 => 3, 9 => 9, 10 => 4, 11 => 2, 12 => 4, 13 => 2];

    /** Passage → jenisnya. */
    private const BUNPOU_PASSAGES = [
        'g7-1' => 'text', 'g8-1' => 'text', 'g8-2' => 'text', 'g8-3' => 'text',
        'g9-1' => 'text', 'g9-2' => 'text', 'g9-3' => 'text',
        'g10-1' => 'text', 'g11-1' => 'pair', 'g12-1' => 'text', 'g13-1' => 'sheet',
    ];

    /** Kunci resmi 正答表 N1 聴解 (id \"{もんだい}-{no}\"; もんだい 5 nomor 3 = 質問1 / 質問2). */
    private const OFFICIAL_CHOKAI_KEY = [
        '1-1' => 3, '1-2' => 4, '1-3' => 4, '1-4' => 1, '1-5' => 3, '1-6' => 3,
        '2-1' => 4, '2-2' => 2, '2-3' => 3, '2-4' => 3, '2-5' => 1, '2-6' => 3, '2-7' => 2,
        '3-1' => 2, '3-2' => 1, '3-3' => 3, '3-4' => 2, '3-5' => 2, '3-6' => 3,
        '4-1' => 3, '4-2' => 2, '4-3' => 2, '4-4' => 3, '4-5' => 2, '4-6' => 3, '4-7' => 1, '4-8' => 3, '4-9' => 1, '4-10' => 2, '4-11' => 2, '4-12' => 1, '4-13' => 3,
        '5-1' => 2, '5-2' => 2, '5-3-1' => 1, '5-3-2' => 4,
    ];

    /** Jawaban contoh (例) tercetak di 正答表; もんだい 5 tidak punya contoh. */
    private const OFFICIAL_CHOKAI_EXAMPLES = [1 => 3, 2 => 3, 3 => 2, 4 => 3];

    private const CHOKAI_MONDAI_SIZES = [1 => 6, 2 => 7, 3 => 6, 4 => 13, 5 => 4];

    protected function setUp(): void
    {
        parent::setUp();
        config(['jlpt.packs.private-n1.enabled' => true]);
    }

    private function asToken(User $user): static
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($user->createToken('spa')->plainTextToken);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function bank(): array
    {
        return json_decode(file_get_contents(resource_path('lang-data/jlpt/n1/mojigoi.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_bank_matches_the_official_answer_key(): void
    {
        $this->assertSame(self::OFFICIAL_MOJIGOI_KEY, collect($this->bank()['questions'])->pluck('answer', 'id')->all());
    }

    public function test_bank_is_well_formed(): void
    {
        $bank = $this->bank();

        $this->assertSame(['N1', 'mojigoi'], [$bank['level'], $bank['section']]);
        $this->assertSame(config('jlpt.levels.N1.sections.mojigoi.minutes'), $bank['minutes']);
        $this->assertCount(config('jlpt.levels.N1.sections.mojigoi.questions'), $bank['questions']);
        $this->assertSame(array_keys(self::MONDAI_SIZES), array_map('intval', array_keys($bank['mondai'])));

        $ids = array_column($bank['questions'], 'id');
        $this->assertSame(range(1, 25), $ids);

        $perMondai = array_count_values(array_column($bank['questions'], 'mondai'));
        ksort($perMondai);
        $this->assertSame(self::MONDAI_SIZES, $perMondai);

        foreach ($bank['questions'] as $q) {
            $this->assertCount(4, $q['choices'], "soal {$q['id']}");
            $this->assertSame(4, count(array_unique($q['choices'])), "soal {$q['id']}: pilihan kembar");
            $this->assertContains($q['answer'], [1, 2, 3, 4], "soal {$q['id']}");
            $this->assertArrayHasKey((string) $q['mondai'], $bank['mondai'], "soal {$q['id']}");
            $this->assertNotSame('', trim($q['stem']), "soal {$q['id']}");
            $this->assertSame(substr_count($q['stem'], '⟦'), substr_count($q['stem'], '⟧'), "soal {$q['id']}: ⟦⟧ tidak seimbang");
        }

        foreach ($bank['mondai'] as $no => $m)
            $this->assertNotEmpty($m['instruction'], "petunjuk {$no}");

        // lembar soal N1 tidak punya contoh (れい)
        foreach ($bank['mondai'] as $no => $m)
            $this->assertArrayNotHasKey('example', $m, "もんだい {$no}");
    }

    public function test_marking_follows_each_mondai_format(): void
    {
        foreach ($this->bank()['questions'] as $q) {
            $id = "soal {$q['id']}";

            // 1, 3: kata sasaran digarisbawahi di stem
            if (in_array($q['mondai'], [1, 3], true))
                $this->assertSame(1, substr_count($q['stem'], '⟦'), $id);

            // 2: kolom kosong, tanpa garis bawah
            if ($q['mondai'] === 2) {
                $this->assertSame(1, substr_count($q['stem'], '（　　）'), $id);
                $this->assertStringNotContainsString('⟦', $q['stem'], $id);
            }

            // 4: stem = kata polos; tiap pilihan menggarisbawahi kata itu
            if ($q['mondai'] === 4) {
                $this->assertStringNotContainsString('⟦', $q['stem'], $id);
                foreach ($q['choices'] as $c)
                    $this->assertMatchesRegularExpression('/⟦[^⟧]+⟧/u', $c, $id);
            }
        }
    }

    public function test_furigana_appears_only_where_the_printed_test_has_ruby(): void
    {
        // Ruby tercetak di lembar soal: 橋本 (2), 木村 (8), 玄人/大家/巨匠/逸材 (10), 鍋 (18),
        // 中村 (19), 東京 (25), 田中 (25). Selain itu tanpa 《》.
        $withRuby = [];
        foreach ($this->bank()['questions'] as $q)
            if (preg_match('/《[^》]*》/u', $q['stem'].implode('', $q['choices'])))
                $withRuby[] = $q['id'];

        $this->assertSame([2, 8, 10, 18, 19, 25], $withRuby);

        $byId = collect($this->bank()['questions'])->keyBy('id');
        $this->assertSame('橋本《はしもと》選手の活躍で、なんとかピンチを⟦逃れた⟧。', $byId[2]['stem']);
        $this->assertSame(['玄人《くろうと》', '大家《たいか》', '巨匠《きょしょう》', '逸材《いつざい》'], $byId[10]['choices']);
    }

    public function test_level_config_follows_the_official_n1_rules(): void
    {
        $cfg = config('jlpt.levels.N1');

        $this->assertSame([100, 180], [$cfg['pass_total'], $cfg['total_max']]);
        $this->assertSame([120, 38], [$cfg['groups']['language_knowledge']['max'], $cfg['groups']['language_knowledge']['min']]);
        $this->assertSame([60, 19], [$cfg['groups']['listening']['max'], $cfg['groups']['listening']['min']]);
        $this->assertSame([25, 44, 36], array_column($cfg['sections'], 'questions'));

        // 言語知識 N1 = satu sampul 110 menit untuk moji-goi + tata bahasa/membaca
        $this->assertSame(110, $cfg['sections']['mojigoi']['minutes'] + $cfg['sections']['bunpou_dokkai']['minutes']);
        $this->assertSame(60, $cfg['sections']['chokai']['minutes']);

        $this->assertTrue($cfg['sections']['mojigoi']['enabled']);
        $this->assertTrue($cfg['sections']['bunpou_dokkai']['enabled']);
        $this->assertTrue($cfg['sections']['chokai']['enabled']);
    }

    public function test_sections_stay_disabled_until_their_bank_exists(): void
    {
        $dir = resource_path('lang-data/'.config('jlpt.packs.private-n1.data_dir'));

        foreach (config('jlpt.levels.N1.sections') as $key => $s) {
            if ($s['enabled'])
                $this->assertFileExists($dir.'/'.$s['file'], "sesi {$key} aktif tapi bank belum ada");
        }
    }

    public function test_pack_is_admin_only(): void
    {
        $this->assertTrue(config('jlpt.packs.private-n1.admin_only'));

        $user = User::factory()->create();
        $keys = collect($this->asToken($user)->getJson('/api/jlpt-test/packs')->assertOk()->json('packs'))->pluck('key');
        $this->assertFalse($keys->contains('private-n1'));

        $this->asToken($user)->getJson('/api/jlpt-test/packs/private-n1')->assertNotFound();
        $this->asToken($user)->postJson('/api/jlpt-test/packs/private-n1/attempts', ['mode' => 'strict'])->assertNotFound();
        $this->asToken($user)->deleteJson('/api/jlpt-test/packs/private-n1/attempts/current')->assertNotFound();

        $packs = collect($this->asToken($this->admin())->getJson('/api/jlpt-test/packs')->assertOk()->json('packs'))->keyBy('key');
        $this->assertTrue($packs['private-n1']['admin_only']);
        $this->assertSame('N1', $packs['private-n1']['level']);
    }

    public function test_admin_runs_mojigoi_and_bunpou_without_key_leaks(): void
    {
        $admin = $this->admin();

        $attempt = $this->asToken($admin)->postJson('/api/jlpt-test/packs/private-n1/attempts', ['mode' => 'strict'])
            ->assertOk()->assertJsonPath('pack', 'private-n1')->json('attempt_id');

        $started = $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/start")
            ->assertOk()
            ->assertJsonPath('level', 'N1')
            ->assertJsonPath('minutes', config('jlpt.levels.N1.sections.mojigoi.minutes'));

        $this->assertCount(25, $started->json('test.questions'));
        foreach ($started->json('test.questions') as $q)
            $this->assertArrayNotHasKey('answer', $q);

        // urutan dijaga: bunpou-dokkai dan chokai tidak bisa dibuka sebelum sesi sebelumnya dikirim
        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/start")->assertStatus(422);
        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/chokai/start")->assertStatus(422);

        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", ['answers' => self::OFFICIAL_MOJIGOI_KEY])
            ->assertOk()->assertJsonPath('test_completed', false);

        // bunpou-dokkai: 44 soal, tanpa kunci di payload
        $started = $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/start")
            ->assertOk()->assertJsonPath('level', 'N1')->assertJsonPath('minutes', config('jlpt.levels.N1.sections.bunpou_dokkai.minutes'));
        $this->assertCount(44, $started->json('test.questions'));
        $this->assertCount(count(self::BUNPOU_PASSAGES), $started->json('test.passages'));
        foreach ($started->json('test.questions') as $q)
            $this->assertArrayNotHasKey('answer', $q);

        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/submit", ['answers' => self::OFFICIAL_BUNPOU_KEY])
            ->assertOk()->assertJsonPath('test_completed', false);

        // rekap belum terbuka sampai chokai selesai
        $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertStatus(409);

        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/chokai/start")->assertOk();
        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/chokai/submit", ['answers' => self::OFFICIAL_CHOKAI_KEY])
            ->assertOk()->assertJsonPath('test_completed', true);

        $recap = $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertOk();
        $recap->assertJsonPath('level', 'N1')
            ->assertJsonPath('pass_total', 100)
            ->assertJsonPath('provisional', false)
            ->assertJsonPath('passed', true)
            ->assertJsonPath('total_score', 180)
            ->assertJsonPath('sections.0.correct', 25)
            ->assertJsonPath('sections.0.total', 25)
            ->assertJsonPath('sections.1.key', 'bunpou_dokkai')
            ->assertJsonPath('sections.1.correct', 44)
            ->assertJsonPath('sections.1.total', 44)
            ->assertJsonPath('sections.2.key', 'chokai')
            ->assertJsonPath('sections.2.correct', 36)
            ->assertJsonPath('sections.2.total', 36);
        $this->assertStringStartsWith('HT-N1-', $recap->json('certificate_no'));

        // 9 baris rincian bunpou per もんだい (5–13) dan 5 baris chokai, semuanya benar
        $breakdown = $recap->json('sections.1.breakdown');
        $this->assertCount(9, $breakdown);
        foreach ($breakdown as $row)
            $this->assertSame($row['total'], $row['correct']);
        $this->assertCount(5, $recap->json('sections.2.breakdown'));
        $this->assertSame(120, collect($recap->json('groups'))->firstWhere('key', 'language_knowledge')['score']);
        $this->assertSame(60, collect($recap->json('groups'))->firstWhere('key', 'listening')['score']);
    }

    private function bunpou(): array
    {
        return json_decode(file_get_contents(resource_path('lang-data/jlpt/n1/bunpou_dokkai.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_bunpou_bank_matches_the_official_answer_key(): void
    {
        $this->assertSame(self::OFFICIAL_BUNPOU_KEY, collect($this->bunpou()['questions'])->pluck('answer', 'id')->all());
    }

    public function test_bunpou_bank_is_well_formed(): void
    {
        $bank = $this->bunpou();

        $this->assertSame(['N1', 'bunpou_dokkai'], [$bank['level'], $bank['section']]);
        $this->assertSame(config('jlpt.levels.N1.sections.bunpou_dokkai.minutes'), $bank['minutes']);
        $this->assertCount(config('jlpt.levels.N1.sections.bunpou_dokkai.questions'), $bank['questions']);
        $this->assertSame(array_keys(self::BUNPOU_MONDAI_SIZES), array_map('intval', array_keys($bank['mondai'])));
        $this->assertSame(range(26, 69), array_column($bank['questions'], 'id'));

        $perMondai = array_count_values(array_column($bank['questions'], 'mondai'));
        ksort($perMondai);
        $this->assertSame(self::BUNPOU_MONDAI_SIZES, $perMondai);

        foreach ($bank['questions'] as $q) {
            $this->assertCount(4, $q['choices'], "soal {$q['id']}");
            $this->assertSame(4, count(array_unique($q['choices'])), "soal {$q['id']}: pilihan kembar");
            $this->assertContains($q['answer'], [1, 2, 3, 4], "soal {$q['id']}");
            $this->assertArrayHasKey((string) $q['mondai'], $bank['mondai'], "soal {$q['id']}");
            $this->assertSame(substr_count($q['stem'], '⟦'), substr_count($q['stem'], '⟧'), "soal {$q['id']}");

            // もんだい 5–6 tanpa bacaan; 7–13 selalu punya bacaan
            if ($q['mondai'] >= 7)
                $this->assertArrayHasKey($q['passage'], $bank['passages'], "soal {$q['id']}");
            else
                $this->assertArrayNotHasKey('passage', $q, "soal {$q['id']}");

            // もんだい 6: tiga kotak kosong + satu ★
            if ($q['mondai'] === 6) {
                $this->assertSame(1, substr_count($q['stem'], '⟦★⟧'), "soal {$q['id']}");
                $this->assertSame(3, substr_count($q['stem'], '⟦　⟧'), "soal {$q['id']}");
            }
        }

        $this->assertSame(2, $bank['mondai']['6']['example']['answer']);
    }

    public function test_bunpou_passages_cover_the_n1_reading_materials(): void
    {
        $bank = $this->bunpou();

        $this->assertSame(self::BUNPOU_PASSAGES, collect($bank['passages'])->map(fn ($p) => $p['kind'])->all());

        // もんだい 7: kotak 41, 42, 43-a/b, 44, 45-a/b lengkap
        foreach (['41', '42', '43-a', '43-b', '44', '45-a', '45-b'] as $box)
            $this->assertStringContainsString('{{'.$box.'}}', $bank['passages']['g7-1']['body'], $box);

        // もんだい 8: (2) ditulis vertikal
        $this->assertTrue($bank['passages']['g8-2']['vertical']);
        $this->assertSame(['(1)', '(2)', '(3)'], array_map(fn ($k) => $bank['passages'][$k]['label'], ['g8-1', 'g8-2', 'g8-3']));

        // もんだい 9: (3) punya garis bawah bernomor ① ②; (1) garis bawah polos 理想化
        $this->assertStringContainsString('⟦理想化⟧', $bank['passages']['g9-1']['body']);
        foreach (['⟧①', '⟧②'] as $needle)
            $this->assertStringContainsString($needle, $bank['passages']['g9-3']['body']);

        // もんだい 10: ① ② di bacaan
        foreach (['⟦そんな錯覚に捕らえられる⟧①', '⟦これ⟧②'] as $needle)
            $this->assertStringContainsString($needle, $bank['passages']['g10-1']['body']);

        // もんだい 11: bacaan A dan B + catatan
        $this->assertSame(['A', 'B'], array_column($bank['passages']['g11-1']['items'], 'label'));
        $this->assertStringContainsString('（注3）', $bank['passages']['g11-1']['footnote']);

        // もんだい 13: 9 baris pengumuman, tabel hadiah 3 baris
        $sheet = $bank['passages']['g13-1']['data'];
        $this->assertCount(9, $sheet['rows']);
        $prize = collect($sheet['rows'])->firstWhere('head', '賞');
        $this->assertCount(3, $prize['table']);
    }

    // ---------------------------------------------------------------------
    // Chokai (聴解) — satu rekaman utuh per もんだい (N1-chokai-1 … 5)
    // ---------------------------------------------------------------------

    private function chokai(): array
    {
        return json_decode(file_get_contents(resource_path('lang-data/jlpt/n1/chokai.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    private function startAttempt(User $user): int
    {
        return $this->asToken($user)->postJson('/api/jlpt-test/packs/private-n1/attempts', ['mode' => 'strict'])->json('attempt_id');
    }

    /** Mulai lalu kirim satu sesi dengan jawaban yang diberikan. */
    private function runSection(User $user, int $attempt, string $section, array $answers): \Illuminate\Testing\TestResponse
    {
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/{$section}/start")->assertOk();

        return $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/{$section}/submit", ['answers' => $answers])->assertOk();
    }

    public function test_chokai_bank_matches_the_official_answer_key(): void
    {
        $bank = $this->chokai();

        $this->assertSame(self::OFFICIAL_CHOKAI_KEY, collect($bank['questions'])->pluck('answer', 'id')->all());

        foreach (self::OFFICIAL_CHOKAI_EXAMPLES as $mondai => $answer)
            $this->assertSame($answer, $bank['mondai'][(string) $mondai]['example']['answer'], "れい もんだい {$mondai}");

        // もんだい 5 tidak punya latihan
        $this->assertArrayNotHasKey('example', $bank['mondai']['5']);
    }

    public function test_chokai_bank_is_well_formed(): void
    {
        $bank = $this->chokai();

        $this->assertSame(['N1', 'chokai', 60], [$bank['level'], $bank['section'], $bank['minutes']]);
        $this->assertCount(config('jlpt.levels.N1.sections.chokai.questions'), $bank['questions']);
        $this->assertSame(['1', '2', '3', '4', '5'], array_keys($bank['mondai']));

        // audio: tepat LIMA rekaman utuh (satu per もんだい), tanpa audio per soal / contoh / intro / penutup
        $audio = array_map(fn ($m) => $m['audio'], array_values($bank['mondai']));
        $this->assertSame(['N1-chokai-1', 'N1-chokai-2', 'N1-chokai-3', 'N1-chokai-4', 'N1-chokai-5'], $audio);
        $this->assertArrayNotHasKey('audio', $bank);
        foreach ($bank['mondai'] as $no => $m) {
            $this->assertTrue($m['whole_audio'], "もんだい {$no}");
            $this->assertNotEmpty($m['instruction'], "もんだい {$no}");
            $this->assertArrayNotHasKey('audio_before', $m);   // N1 tidak punya istirahat
            $this->assertArrayNotHasKey('answer_seconds', $m); // jeda menjawab sudah ada di dalam rekaman
            if (isset($m['example'])) {
                $this->assertArrayNotHasKey('audio', $m['example']);
                $this->assertNotEmpty($m['example']['script']);
            }
        }

        // lembar soal kosong (メモ): もんだい 3–5; もんだい 1–2 punya pilihan tercetak
        $this->assertSame([false, false, true, true, true], array_map(fn ($m) => (bool) ($m['memo'] ?? false), array_values($bank['mondai'])));

        $ids = array_column($bank['questions'], 'id');
        $this->assertSame($ids, array_values(array_unique($ids)), 'id soal harus unik');

        $perMondai = array_count_values(array_column($bank['questions'], 'mondai'));
        ksort($perMondai);
        $this->assertSame(self::CHOKAI_MONDAI_SIZES, $perMondai);

        foreach ($bank['questions'] as $q) {
            $id = $q['id'];
            $count = isset($q['choices']) ? count($q['choices']) : $q['choice_count'];

            $this->assertArrayNotHasKey('audio', $q, $id);
            $this->assertNotEmpty($q['script'], $id);
            $this->assertStringStartsWith("{$q['mondai']}-{$q['no']}", $id);
            $this->assertGreaterThanOrEqual(1, $q['answer'], $id);
            $this->assertLessThanOrEqual($count, $q['answer'], $id);

            // もんだい 1–3: 4 pilihan, もんだい 4: 3 pilihan; もんだい 5: 4 pilihan (3番 = 4 pilihan tercetak)
            $this->assertSame($q['mondai'] === 4 ? 3 : 4, $count, $id);

            // tercetak: もんだい 1, 2, dan 5 nomor 3; sisanya hanya terdengar (tanpa teks pilihan)
            $printed = $q['mondai'] <= 2 || str_starts_with($id, '5-3-');
            $printed ? $this->assertArrayHasKey('choices', $q, $id) : $this->assertArrayNotHasKey('choices', $q, $id);
            if ($printed)
                $this->assertCount($count, array_unique($q['choices']), "{$id}: pilihan kembar");

            // N1 tidak memakai gambar sama sekali
            $this->assertArrayNotHasKey('image', $q, $id);
        }

        // soal berpasangan もんだい 5 nomor 3: dua pertanyaan, label tampilan berbeda
        $pair = collect($bank['questions'])->whereIn('id', ['5-3-1', '5-3-2'])->values();
        $this->assertSame(['3ばん 質問1', '3ばん 質問2'], $pair->pluck('label')->all());
        $this->assertSame([3, 3], $pair->pluck('no')->all());
    }

    public function test_chokai_resolves_whole_recordings_by_name_with_any_audio_extension(): void
    {
        $root = 'audio-test-n1-'.uniqid();
        $dir = public_path("{$root}/n1");
        mkdir($dir, 0777, true);
        // ekstensi berbeda-beda: bank hanya menulis "N1-chokai-N"
        foreach (['N1-chokai-1.mp3', 'N1-chokai-2.m4a', 'N1-chokai-5.wav'] as $name)
            file_put_contents("{$dir}/{$name}", '');
        config(['jlpt.packs.private-n1.audio_path' => "/{$root}"]);

        try {
            $admin = $this->admin();
            $attempt = $this->startAttempt($admin);
            $this->runSection($admin, $attempt, 'mojigoi', self::OFFICIAL_MOJIGOI_KEY);
            $this->runSection($admin, $attempt, 'bunpou_dokkai', self::OFFICIAL_BUNPOU_KEY);

            $start = $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/chokai/start")->assertOk();
            $resume = $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/sections/chokai")->assertOk();
            $base = "/{$root}/n1/";

            foreach ([$start, $resume] as $res) {
                $this->assertSame(['N1', 60], [$res->json('level'), $res->json('minutes')]);
                $this->assertCount(36, $res->json('test.questions'));

                $this->assertSame($base.'N1-chokai-1.mp3', $res->json('test.mondai.1.audio'));
                $this->assertSame($base.'N1-chokai-2.m4a', $res->json('test.mondai.2.audio'));
                // berkas belum ada → URL tetap berbentuk nama bank (pemutar menampilkan pesan jelas)
                $this->assertSame($base.'N1-chokai-3.mp3', $res->json('test.mondai.3.audio'));
                $this->assertSame($base.'N1-chokai-4.mp3', $res->json('test.mondai.4.audio'));
                $this->assertSame($base.'N1-chokai-5.wav', $res->json('test.mondai.5.audio'));
                $this->assertTrue($res->json('test.mondai.1.whole_audio'));

                foreach ($res->json('test.questions') as $q) {
                    $this->assertArrayNotHasKey('answer', $q);
                    $this->assertArrayNotHasKey('script', $q);
                    $this->assertArrayNotHasKey('audio', $q);
                }
                foreach ($res->json('test.mondai') as $m)
                    $this->assertArrayNotHasKey('script', $m['example'] ?? []);

                // naskah audio tidak boleh bocor di bagian mana pun dari respons
                $this->assertStringNotContainsString('"script"', $res->getContent());
            }
        }
        finally {
            foreach (glob("{$dir}/*") ?: [] as $f)
                unlink($f);
            @rmdir($dir);
            @rmdir(public_path($root));
        }
    }

    public function test_chokai_needs_bunpou_first_and_all_three_sessions_finish_the_test(): void
    {
        $admin = $this->admin();
        $attempt = $this->startAttempt($admin);

        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/chokai/start")->assertStatus(422);
        $this->runSection($admin, $attempt, 'mojigoi', self::OFFICIAL_MOJIGOI_KEY)->assertJsonPath('test_completed', false);
        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/chokai/start")->assertStatus(422);
        $this->runSection($admin, $attempt, 'bunpou_dokkai', self::OFFICIAL_BUNPOU_KEY)->assertJsonPath('test_completed', false);
        $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertStatus(409);

        $this->runSection($admin, $attempt, 'chokai', self::OFFICIAL_CHOKAI_KEY)->assertJsonPath('test_completed', true);

        $recap = $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertOk()->json();
        $this->assertSame(['N1', 100], [$recap['level'], $recap['pass_total']]);
        $this->assertFalse($recap['provisional']);
        $this->assertTrue($recap['passed']);
        $this->assertSame(180, $recap['total_score']);
    }

    public function test_chokai_grades_paired_questions_and_reports_per_mondai(): void
    {
        $admin = $this->admin();
        $attempt = $this->startAttempt($admin);
        $this->runSection($admin, $attempt, 'mojigoi', self::OFFICIAL_MOJIGOI_KEY);
        $this->runSection($admin, $attempt, 'bunpou_dokkai', self::OFFICIAL_BUNPOU_KEY);

        // semua benar kecuali 5-3-1 (salah), 5-3-2 (benar), dan 4-11 (kosong)
        $answers = self::OFFICIAL_CHOKAI_KEY;
        $answers['5-3-1'] = $answers['5-3-1'] === 1 ? 2 : 1;
        unset($answers['4-11']);
        $this->runSection($admin, $attempt, 'chokai', $answers);

        $recap = $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertOk()->json();
        $chokai = collect($recap['sections'])->firstWhere('key', 'chokai');
        $breakdown = collect($chokai['breakdown']);

        $this->assertSame([34, 36], [$chokai['correct'], $chokai['total']]);
        $this->assertSame(12, $breakdown->firstWhere('mondai', 4)['correct']);
        $this->assertSame(1, $breakdown->firstWhere('mondai', 4)['unanswered']);
        $this->assertSame(3, $breakdown->firstWhere('mondai', 5)['correct']);
    }
}
