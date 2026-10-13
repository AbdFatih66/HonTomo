<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tes JLPT N2 (soal asli, pack 'private-n2', KHUSUS ADMIN). Sesi moji-goi (言語知識〈文字・語彙〉,
 * 問題1–6, 32 soal), bunpou_dokkai (言語知識〈文法〉・読解, 問題7–14, 43 soal) dan chokai (聴解, 31 soal) sudah
 * ada, jadi rekap memberi keputusan lulus/tidak. Test yang hanya menguji sebagian sesi mematikan sesi lain
 * lewat disableSections().
 */
class JlptN2Test extends TestCase
{
    use RefreshDatabase;

    /** Kunci resmi 正答表 N2 言語知識（文字・語彙）, 問題1–6 (nomor 1–32) — ditulis ulang agar salah ketik ketahuan. */
    private const OFFICIAL_MOJIGOI_KEY = [
        1 => 2, 2 => 3, 3 => 1, 4 => 4, 5 => 3,
        6 => 3, 7 => 2, 8 => 1, 9 => 4, 10 => 2,
        11 => 1, 12 => 2, 13 => 1, 14 => 4, 15 => 2,
        16 => 4, 17 => 4, 18 => 1, 19 => 2, 20 => 1, 21 => 1, 22 => 2,
        23 => 3, 24 => 4, 25 => 1, 26 => 3, 27 => 3,
        28 => 4, 29 => 3, 30 => 3, 31 => 2, 32 => 4,
    ];

    /** Jumlah soal per もんだい (1–6). */
    private const MONDAI_SIZES = [1 => 5, 2 => 5, 3 => 5, 4 => 7, 5 => 5, 6 => 5];

    /** Kunci resmi 正答表 N2 言語知識（文法）・読解, 問題7–14 (nomor 33–75), 43 soal. */
    private const OFFICIAL_BUNPOU_KEY = [
        33 => 3, 34 => 2, 35 => 4, 36 => 4, 37 => 1, 38 => 3, 39 => 3, 40 => 3, 41 => 1, 42 => 2, 43 => 2, 44 => 1,
        45 => 2, 46 => 1, 47 => 4, 48 => 3, 49 => 3,
        50 => 2, 51 => 4, 52 => 1, 53 => 3, 54 => 1,
        55 => 3, 56 => 2, 57 => 2, 58 => 1, 59 => 2,
        60 => 3, 61 => 4, 62 => 4, 63 => 3, 64 => 1, 65 => 4, 66 => 3, 67 => 2, 68 => 1,
        69 => 1, 70 => 3,
        71 => 2, 72 => 4, 73 => 4,
        74 => 4, 75 => 1,
    ];

    /** Jumlah soal per もんだい bunpou-dokkai (7–14, nomor cetak di lembar soal). */
    private const BUNPOU_MONDAI_SIZES = [7 => 12, 8 => 5, 9 => 5, 10 => 5, 11 => 9, 12 => 2, 13 => 3, 14 => 2];

    /** Passage → jenisnya. */
    private const BUNPOU_PASSAGES = [
        'g9-1' => 'text',
        'g10-1' => 'text', 'g10-2' => 'note', 'g10-3' => 'text', 'g10-4' => 'text', 'g10-5' => 'text',
        'g11-1' => 'text', 'g11-2' => 'text', 'g11-3' => 'text',
        'g12-1' => 'pair', 'g13-1' => 'text', 'g14-1' => 'plans',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        config(['jlpt.packs.private-n2.enabled' => true]);
    }

    /** Kunci resmi 正答表 N2 聴解 (id soal; もんだい 5 nomor 3 = "5-3-1" dan "5-3-2"), 31 soal. */
    private const OFFICIAL_CHOKAI_KEY = [
        '1-1' => 2, '1-2' => 3, '1-3' => 3, '1-4' => 2, '1-5' => 1,
        '2-1' => 2, '2-2' => 3, '2-3' => 4, '2-4' => 2, '2-5' => 3, '2-6' => 2,
        '3-1' => 1, '3-2' => 4, '3-3' => 3, '3-4' => 1, '3-5' => 2,
        '4-1' => 3, '4-2' => 1, '4-3' => 3, '4-4' => 1, '4-5' => 1, '4-6' => 3, '4-7' => 3, '4-8' => 2, '4-9' => 3, '4-10' => 1, '4-11' => 2,
        '5-1' => 3, '5-2' => 4, '5-3-1' => 1, '5-3-2' => 3,
    ];

    /** Jawaban contoh (例) tercetak di 正答表; もんだい 5 tidak punya contoh. */
    private const OFFICIAL_CHOKAI_EXAMPLES = [1 => 3, 2 => 2, 3 => 4, 4 => 1];

    private const CHOKAI_MONDAI_SIZES = [1 => 5, 2 => 6, 3 => 5, 4 => 11, 5 => 4];

    /** @param list<string> $sections sesi yang dimatikan (config levels.N2.sections.*.enabled) */
    private function disableSections(array $sections): void
    {
        foreach ($sections as $section)
            config(["jlpt.levels.N2.sections.{$section}.enabled" => false]);
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

    private function bank(string $section = 'mojigoi'): array
    {
        return json_decode(file_get_contents(resource_path("lang-data/jlpt/n2/{$section}.json")), true, 512, JSON_THROW_ON_ERROR);
    }

    /** Mulai lalu kirim satu sesi dengan jawaban yang diberikan. */
    private function runSection(User $user, int $attempt, string $section, array $answers): \Illuminate\Testing\TestResponse
    {
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/{$section}/start")->assertOk();

        return $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/{$section}/submit", ['answers' => $answers])->assertOk();
    }

    public function test_bank_matches_the_official_answer_key(): void
    {
        $this->assertSame(self::OFFICIAL_MOJIGOI_KEY, collect($this->bank()['questions'])->pluck('answer', 'id')->all());
    }

    public function test_bank_is_well_formed(): void
    {
        $bank = $this->bank();

        $this->assertSame(['N2', 'mojigoi'], [$bank['level'], $bank['section']]);
        $this->assertSame(config('jlpt.levels.N2.sections.mojigoi.minutes'), $bank['minutes']);
        $this->assertCount(config('jlpt.levels.N2.sections.mojigoi.questions'), $bank['questions']);
        $this->assertSame(array_keys(self::MONDAI_SIZES), array_map('intval', array_keys($bank['mondai'])));

        $ids = array_column($bank['questions'], 'id');
        $this->assertSame($ids, array_values(array_unique($ids)), 'id soal harus unik');
        $this->assertSame(range(1, 32), $ids);

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

        // lembar soal N2 tidak punya contoh (れい)
        foreach ($bank['mondai'] as $no => $m)
            $this->assertArrayNotHasKey('example', $m, "もんだい {$no}");
    }

    public function test_marking_follows_each_mondai_format(): void
    {
        foreach ($this->bank()['questions'] as $q) {
            $id = "soal {$q['id']}";

            // 1, 2, 5: kata sasaran digarisbawahi di stem
            if (in_array($q['mondai'], [1, 2, 5], true))
                $this->assertSame(1, substr_count($q['stem'], '⟦'), $id);

            // 3, 4: kolom kosong, tanpa garis bawah
            if (in_array($q['mondai'], [3, 4], true)) {
                $this->assertSame(1, substr_count($q['stem'], '（　　）'), $id);
                $this->assertStringNotContainsString('⟦', $q['stem'], $id);
            }

            // 6: stem = kata polos; tiap pilihan menggarisbawahi kata itu
            if ($q['mondai'] === 6) {
                $this->assertStringNotContainsString('⟦', $q['stem'], $id);
                foreach ($q['choices'] as $c)
                    $this->assertMatchesRegularExpression('/⟦[^⟧]+⟧/u', $c, $id);
            }
        }
    }

    public function test_furigana_appears_only_where_the_printed_test_has_ruby(): void
    {
        // Ruby tercetak di lembar soal: 携帯電話, 岡田, 街, 山本, 田中, 京都, 新幹線, 利益 (stem 31 + pilihan),
        // 活気/活発/活躍/活動 (18), 効果/状態/流行/緊張 (23), 慎重 (24). Selain itu tanpa 《》.
        $withRuby = [];
        foreach ($this->bank()['questions'] as $q)
            if (preg_match('/《[^》]*》/u', $q['stem'].implode('', $q['choices'])))
                $withRuby[] = $q['id'];

        $this->assertSame([8, 9, 17, 18, 21, 23, 24, 30, 31], $withRuby);

        $q31 = collect($this->bank()['questions'])->firstWhere('id', 31);
        $this->assertSame('利益《りえき》', $q31['stem']);
        $this->assertSame('バスの⟦利益《りえき》⟧は、新幹線《しんかんせん》よりも料金が安いことだ。', $q31['choices'][3]);
    }

    public function test_level_config_follows_the_official_n2_rules(): void
    {
        $cfg = config('jlpt.levels.N2');

        $this->assertSame([90, 180], [$cfg['pass_total'], $cfg['total_max']]);
        $this->assertSame([120, 38], [$cfg['groups']['language_knowledge']['max'], $cfg['groups']['language_knowledge']['min']]);
        $this->assertSame([60, 19], [$cfg['groups']['listening']['max'], $cfg['groups']['listening']['min']]);
        $this->assertSame([32, 43, 31], array_column($cfg['sections'], 'questions'));

        // 言語知識 N2 = satu sampul 105 menit untuk moji-goi + tata bahasa/membaca
        $this->assertSame(105, $cfg['sections']['mojigoi']['minutes'] + $cfg['sections']['bunpou_dokkai']['minutes']);

        $this->assertTrue($cfg['sections']['mojigoi']['enabled']);
        $this->assertTrue($cfg['sections']['bunpou_dokkai']['enabled']);
        $this->assertTrue($cfg['sections']['chokai']['enabled']);
        $this->assertSame([50, 31], [$cfg['sections']['chokai']['minutes'], $cfg['sections']['chokai']['questions']]);
        $this->assertSame([75, 43], [$cfg['sections']['bunpou_dokkai']['minutes'], $cfg['sections']['bunpou_dokkai']['questions']]);
    }

    public function test_sections_stay_disabled_until_their_bank_exists(): void
    {
        $dir = resource_path('lang-data/'.config('jlpt.packs.private-n2.data_dir'));

        foreach (config('jlpt.levels.N2.sections') as $key => $s) {
            if ($s['enabled'])
                $this->assertFileExists($dir.'/'.$s['file'], "sesi {$key} aktif tapi bank belum ada");
        }
    }

    public function test_pack_is_admin_only(): void
    {
        $this->assertTrue(config('jlpt.packs.private-n2.admin_only'));

        $user = User::factory()->create();
        $keys = collect($this->asToken($user)->getJson('/api/jlpt-test/packs')->assertOk()->json('packs'))->pluck('key');
        $this->assertFalse($keys->contains('private-n2'));

        $this->asToken($user)->getJson('/api/jlpt-test/packs/private-n2')->assertNotFound();
        $this->asToken($user)->postJson('/api/jlpt-test/packs/private-n2/attempts', ['mode' => 'strict'])->assertNotFound();
        $this->asToken($user)->deleteJson('/api/jlpt-test/packs/private-n2/attempts/current')->assertNotFound();

        $packs = collect($this->asToken($this->admin())->getJson('/api/jlpt-test/packs')->assertOk()->json('packs'))->keyBy('key');
        $this->assertTrue($packs['private-n2']['admin_only']);
        $this->assertSame('N2', $packs['private-n2']['level']);
    }

    public function test_admin_runs_mojigoi_and_gets_a_provisional_n2_recap_without_key_leaks(): void
    {
        // alur satu sesi: matikan bunpou_dokkai dan chokai supaya moji-goi menjadi sesi terakhir
        $this->disableSections(['bunpou_dokkai', 'chokai']);
        $admin = $this->admin();

        $attempt = $this->asToken($admin)->postJson('/api/jlpt-test/packs/private-n2/attempts', ['mode' => 'strict'])
            ->assertOk()->assertJsonPath('pack', 'private-n2')->json('attempt_id');

        $started = $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/start")
            ->assertOk()
            ->assertJsonPath('level', 'N2')
            ->assertJsonPath('minutes', config('jlpt.levels.N2.sections.mojigoi.minutes'));

        $this->assertCount(32, $started->json('test.questions'));
        foreach ($started->json('test.questions') as $q)
            $this->assertArrayNotHasKey('answer', $q);

        // sesi lain belum tersedia
        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/start")->assertStatus(422);
        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/chokai/start")->assertStatus(422);

        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", ['answers' => self::OFFICIAL_MOJIGOI_KEY])
            ->assertOk()->assertJsonPath('test_completed', true);

        $recap = $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertOk();
        $recap->assertJsonPath('level', 'N2')
            ->assertJsonPath('pass_total', 90)
            ->assertJsonPath('provisional', true)
            ->assertJsonPath('passed', null)
            ->assertJsonPath('sections.0.correct', 32)
            ->assertJsonPath('sections.0.total', 32);
        $this->assertStringStartsWith('HT-N2-', $recap->json('certificate_no'));
    }

    // ------------------------------------------------------------------
    // Bunpou-Dokkai (言語知識〈文法〉・読解, 問題7–14)
    // ------------------------------------------------------------------

    public function test_bunpou_bank_matches_the_official_answer_key(): void
    {
        $this->assertSame(self::OFFICIAL_BUNPOU_KEY, collect($this->bank('bunpou_dokkai')['questions'])->pluck('answer', 'id')->all());
    }

    public function test_bunpou_bank_is_well_formed(): void
    {
        $bank = $this->bank('bunpou_dokkai');

        $this->assertSame(['N2', 'bunpou_dokkai', 75], [$bank['level'], $bank['section'], $bank['minutes']]);
        $this->assertCount(config('jlpt.levels.N2.sections.bunpou_dokkai.questions'), $bank['questions']);
        $this->assertSame(array_keys(self::BUNPOU_MONDAI_SIZES), array_map('intval', array_keys($bank['mondai'])));

        $ids = array_column($bank['questions'], 'id');
        $this->assertSame(range(33, 75), $ids);

        $perMondai = array_count_values(array_column($bank['questions'], 'mondai'));
        ksort($perMondai);
        $this->assertSame(self::BUNPOU_MONDAI_SIZES, $perMondai);

        foreach ($bank['questions'] as $q) {
            $this->assertCount(4, $q['choices'], "soal {$q['id']}");
            $this->assertSame(4, count(array_unique($q['choices'])), "soal {$q['id']}: pilihan kembar");
            $this->assertContains($q['answer'], [1, 2, 3, 4], "soal {$q['id']}");
            $this->assertArrayHasKey((string) $q['mondai'], $bank['mondai'], "soal {$q['id']}");
            $this->assertSame(substr_count($q['stem'], '⟦'), substr_count($q['stem'], '⟧'), "soal {$q['id']}: ⟦⟧ tidak seimbang");

            // もんだい 7–8 berdiri sendiri; 9–14 selalu punya bacaan
            if ($q['mondai'] >= 9) {
                $this->assertArrayHasKey($q['passage'], $bank['passages'], "soal {$q['id']}");
            }
            else {
                $this->assertArrayNotHasKey('passage', $q, "soal {$q['id']}");
                $this->assertNotSame('', trim($q['stem']), "soal {$q['id']}");
            }

            // もんだい 7: tepat satu kolom kosong （　　）
            if ($q['mondai'] === 7)
                $this->assertSame(1, substr_count($q['stem'], '（　　）'), "soal {$q['id']}");

            // もんだい 8: tiga kotak kosong + satu ★
            if ($q['mondai'] === 8) {
                $this->assertSame(1, substr_count($q['stem'], '⟦★⟧'), "soal {$q['id']}");
                $this->assertSame(3, substr_count($q['stem'], '⟦　⟧'), "soal {$q['id']}");
            }
        }

        // もんだい 8 punya contoh + cara menjawab; もんだい 9 punya pengantar
        $this->assertSame(2, $bank['mondai']['8']['example']['answer']);
        $this->assertNotEmpty($bank['mondai']['8']['example']['howto']['figure']['order']);
        $this->assertSame('以下は、雑誌のコラムである。', $bank['mondai']['9']['lead']);
        foreach ($bank['mondai'] as $no => $m)
            $this->assertNotEmpty($m['instruction'], "petunjuk {$no}");
    }

    public function test_bunpou_passages_cover_the_n2_reading_materials(): void
    {
        $bank = $this->bank('bunpou_dokkai');

        $this->assertSame(self::BUNPOU_PASSAGES, collect($bank['passages'])->map(fn ($p) => $p['kind'])->all());

        // もんだい 9: judul, kotak nomor 50–54 lengkap
        $g9 = $bank['passages']['g9-1'];
        $this->assertTrue($g9['boxed']);
        $this->assertSame('日本の鉄道ファン', $g9['title']);
        foreach (range(50, 54) as $n)
            $this->assertStringContainsString('{{'.$n.'}}', $g9['body']);

        // もんだい 10: (1)–(5); (2) = email (variant mail) dengan catatan di luar kotak
        $this->assertSame(['(1)', '(2)', '(3)', '(4)', '(5)'], array_map(fn ($k) => $bank['passages'][$k]['label'], ['g10-1', 'g10-2', 'g10-3', 'g10-4', 'g10-5']));
        $this->assertSame('mail', $bank['passages']['g10-2']['variant']);
        $this->assertNotEmpty($bank['passages']['g10-2']['footnote']);
        $this->assertStringContainsString('（注1）', $bank['passages']['g10-3']['body']);
        $this->assertStringContainsString('（注2）', $bank['passages']['g10-3']['body']);

        // もんだい 11: garis bawah bernomor ① ② di (2) dan (3); (1) menggarisbawahi 追いついてきた
        $this->assertStringContainsString('⟦追いついてきた⟧', $bank['passages']['g11-1']['body']);
        foreach (['⟦いつしか身につけたことのひとつ⟧①', '⟦そこ⟧②'] as $needle)
            $this->assertStringContainsString($needle, $bank['passages']['g11-2']['body']);
        foreach (['⟦身につけることが難しい⟧①', '⟦この場合⟧②'] as $needle)
            $this->assertStringContainsString($needle, $bank['passages']['g11-3']['body']);

        // もんだい 12: A dan B + catatan
        $this->assertSame(['A', 'B'], array_column($bank['passages']['g12-1']['items'], 'label'));
        $this->assertNotEmpty($bank['passages']['g12-1']['footnote']);

        // もんだい 13: garis bawah 好き嫌いがあってはいけない + 5 catatan
        $this->assertStringContainsString('⟦好き嫌いがあってはいけない⟧', $bank['passages']['g13-1']['body']);
        $this->assertStringContainsString('（注5）正当化《せいとうか》する', $bank['passages']['g13-1']['body']);

        // もんだい 14: tabel A社 (プラン①–④) + diagram alur B社 (プランⅠ–Ⅳ)
        $data = $bank['passages']['g14-1']['data'];
        $this->assertSame(['プラン①', 'プラン②', 'プラン③', 'プラン④'], array_merge(...array_column($data['a']['groups'], 'headers')));
        $this->assertCount(3, $data['a']['notes']);
        $results = [];
        foreach ($data['b']['steps'] as $step)
            foreach ($step['options'] as $o)
                if (isset($o['result']))
                    $results[] = $o['result'];
        $this->assertSame(['プランⅠ', 'プランⅡ', 'プランⅢ', 'プランⅣ'], $results);
        $this->assertCount(2, $data['b']['notes']);
    }

    public function test_bunpou_furigana_is_limited_to_printed_ruby(): void
    {
        // Contoh yang tercetak di lembar soal (soal 37 dan 49); soal tanpa ruby tetap polos.
        $byId = collect($this->bank('bunpou_dokkai')['questions'])->keyBy('id');

        $this->assertStringContainsString('山田《やまだ》監督《かんとく》', $byId[37]['stem']);
        $this->assertStringContainsString('発揮《はっき》', $byId[49]['stem']);
        foreach ([33, 36, 38, 40, 41, 42, 44] as $id)
            $this->assertDoesNotMatchRegularExpression('/《[^》]*》/u', $byId[$id]['stem'].implode('', $byId[$id]['choices']), "soal {$id}");
    }

    public function test_two_sessions_without_chokai_give_a_provisional_recap_with_key_free_payload(): void
    {
        $this->disableSections(['chokai']);
        $admin = $this->admin();
        $attempt = $this->asToken($admin)->postJson('/api/jlpt-test/packs/private-n2/attempts', ['mode' => 'strict'])->json('attempt_id');

        // urutan dijaga: bunpou tidak bisa dibuka sebelum moji-goi dikirim
        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/start")->assertStatus(422);

        $this->runSection($admin, $attempt, 'mojigoi', self::OFFICIAL_MOJIGOI_KEY)->assertJsonPath('test_completed', false);

        $started = $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/start")
            ->assertOk()->assertJsonPath('level', 'N2')->assertJsonPath('minutes', 75);

        $this->assertCount(43, $started->json('test.questions'));
        $this->assertCount(count(self::BUNPOU_PASSAGES), $started->json('test.passages'));
        foreach ($started->json('test.questions') as $q)
            $this->assertArrayNotHasKey('answer', $q);

        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/submit", ['answers' => self::OFFICIAL_BUNPOU_KEY])
            ->assertOk()->assertJsonPath('test_completed', true);

        $recap = $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertOk();
        $recap->assertJsonPath('provisional', true)
            ->assertJsonPath('passed', null)
            ->assertJsonPath('sections.1.key', 'bunpou_dokkai')
            ->assertJsonPath('sections.1.correct', 43)
            ->assertJsonPath('sections.1.total', 43);

        // rincian per もんだい: 8 baris (7–14), semuanya benar
        $breakdown = $recap->json('sections.1.breakdown');
        $this->assertCount(8, $breakdown);
        foreach ($breakdown as $row)
            $this->assertSame($row['total'], $row['correct']);

        // kelompok bahasa: 75 soal dari 75 → 120 poin
        $language = collect($recap->json('groups'))->firstWhere('key', 'language_knowledge');
        $this->assertSame(120, $language['score']);
    }

    public function test_attempt_is_locked_once_user_is_no_longer_admin(): void
    {
        $user = $this->admin();
        $attempt = $this->asToken($user)->postJson('/api/jlpt-test/packs/private-n2/attempts', ['mode' => 'strict'])->json('attempt_id');
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/start")->assertOk();

        $user->forceFill(['role' => 'user'])->save();

        $this->asToken($user)->getJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi")->assertNotFound();
    }

    // ------------------------------------------------------------------
    // Chokai (聴解) — satu rekaman utuh per もんだい (N2-chokai-1 … 5)
    // ------------------------------------------------------------------

    private function startAttempt(User $user): int
    {
        return $this->asToken($user)->postJson('/api/jlpt-test/packs/private-n2/attempts', ['mode' => 'strict'])->json('attempt_id');
    }

    public function test_chokai_bank_matches_the_official_answer_key(): void
    {
        $bank = $this->bank('chokai');

        $this->assertSame(self::OFFICIAL_CHOKAI_KEY, collect($bank['questions'])->pluck('answer', 'id')->all());

        foreach (self::OFFICIAL_CHOKAI_EXAMPLES as $mondai => $answer)
            $this->assertSame($answer, $bank['mondai'][(string) $mondai]['example']['answer'], "れい もんだい {$mondai}");

        // もんだい 5 tidak punya latihan
        $this->assertArrayNotHasKey('example', $bank['mondai']['5']);
    }

    public function test_chokai_bank_is_well_formed(): void
    {
        $bank = $this->bank('chokai');

        $this->assertSame(['N2', 'chokai', 50], [$bank['level'], $bank['section'], $bank['minutes']]);
        $this->assertCount(config('jlpt.levels.N2.sections.chokai.questions'), $bank['questions']);
        $this->assertSame(['1', '2', '3', '4', '5'], array_keys($bank['mondai']));

        // audio: tepat LIMA rekaman utuh (satu per もんだい), tanpa audio per soal / contoh / intro / penutup
        $audio = array_map(fn ($m) => $m['audio'], array_values($bank['mondai']));
        $this->assertSame(['N2-chokai-1', 'N2-chokai-2', 'N2-chokai-3', 'N2-chokai-4', 'N2-chokai-5'], $audio);
        $this->assertArrayNotHasKey('audio', $bank);
        foreach ($bank['mondai'] as $no => $m) {
            $this->assertTrue($m['whole_audio'], "もんだい {$no}");
            $this->assertNotEmpty($m['instruction'], "もんだい {$no}");
            $this->assertArrayNotHasKey('audio_before', $m);   // N2 tidak punya istirahat
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

            // N2 tidak memakai gambar sama sekali
            $this->assertArrayNotHasKey('image', $q, $id);
        }

        // soal berpasangan もんだい 5 nomor 3: dua pertanyaan, label tampilan berbeda
        $pair = collect($bank['questions'])->whereIn('id', ['5-3-1', '5-3-2'])->values();
        $this->assertSame(['3ばん 質問1', '3ばん 質問2'], $pair->pluck('label')->all());
        $this->assertSame([3, 3], $pair->pluck('no')->all());
    }

    public function test_chokai_resolves_whole_recordings_by_name_with_any_audio_extension(): void
    {
        $root = 'audio-test-n2-'.uniqid();
        $dir = public_path("{$root}/n2");
        mkdir($dir, 0777, true);
        // ekstensi berbeda-beda: bank hanya menulis \"N2-chokai-N\"
        foreach (['N2-chokai-1.mp3', 'N2-chokai-2.m4a', 'N2-chokai-5.wav'] as $name)
            file_put_contents("{$dir}/{$name}", '');
        config(['jlpt.packs.private-n2.audio_path' => "/{$root}"]);

        try {
            $admin = $this->admin();
            $attempt = $this->startAttempt($admin);
            $this->runSection($admin, $attempt, 'mojigoi', self::OFFICIAL_MOJIGOI_KEY);
            $this->runSection($admin, $attempt, 'bunpou_dokkai', self::OFFICIAL_BUNPOU_KEY);

            $start = $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/chokai/start")->assertOk();
            $resume = $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/sections/chokai")->assertOk();
            $base = "/{$root}/n2/";

            foreach ([$start, $resume] as $res) {
                $this->assertSame(['N2', 50], [$res->json('level'), $res->json('minutes')]);
                $this->assertCount(31, $res->json('test.questions'));

                $this->assertSame($base.'N2-chokai-1.mp3', $res->json('test.mondai.1.audio'));
                $this->assertSame($base.'N2-chokai-2.m4a', $res->json('test.mondai.2.audio'));
                // berkas belum ada → URL tetap berbentuk nama bank (pemutar menampilkan pesan jelas)
                $this->assertSame($base.'N2-chokai-3.mp3', $res->json('test.mondai.3.audio'));
                $this->assertSame($base.'N2-chokai-4.mp3', $res->json('test.mondai.4.audio'));
                $this->assertSame($base.'N2-chokai-5.wav', $res->json('test.mondai.5.audio'));
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

        // urutan dijaga: chokai tidak bisa dibuka sebelum bunpou-dokkai dikirim
        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/chokai/start")->assertStatus(422);

        $this->runSection($admin, $attempt, 'mojigoi', self::OFFICIAL_MOJIGOI_KEY)->assertJsonPath('test_completed', false);
        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/chokai/start")->assertStatus(422);
        $this->runSection($admin, $attempt, 'bunpou_dokkai', self::OFFICIAL_BUNPOU_KEY)->assertJsonPath('test_completed', false);

        // rekap belum terbuka sampai chokai selesai
        $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertStatus(409);

        $this->runSection($admin, $attempt, 'chokai', self::OFFICIAL_CHOKAI_KEY)->assertJsonPath('test_completed', true);

        $recap = $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertOk()->json();

        $this->assertSame(['N2', 90], [$recap['level'], $recap['pass_total']]);
        $this->assertFalse($recap['provisional']);
        $this->assertTrue($recap['passed']);
        $this->assertSame(180, $recap['total_score']);
        $this->assertMatchesRegularExpression('/^HT-N2-\d{8}-'.sprintf('%06d', $attempt).'$/', $recap['certificate_no']);

        $chokai = collect($recap['sections'])->firstWhere('key', 'chokai');
        $this->assertSame([31, 31], [$chokai['correct'], $chokai['total']]);
        $this->assertCount(5, $chokai['breakdown']);

        $listening = collect($recap['groups'])->firstWhere('key', 'listening');
        $this->assertSame(60, $listening['score']);
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

        $this->assertSame([29, 31], [$chokai['correct'], $chokai['total']]);
        $this->assertSame(10, $breakdown->firstWhere('mondai', 4)['correct']);
        $this->assertSame(1, $breakdown->firstWhere('mondai', 4)['unanswered']);
        $this->assertSame(3, $breakdown->firstWhere('mondai', 5)['correct']);
        $this->assertFalse($recap['provisional']);
        $this->assertTrue($recap['passed']);
    }

    public function test_review_exposes_chokai_key_and_script_only_after_the_test_is_completed(): void
    {
        $admin = $this->admin();
        $attempt = $this->startAttempt($admin);
        $this->runSection($admin, $attempt, 'mojigoi', []);
        $this->runSection($admin, $attempt, 'bunpou_dokkai', []);
        // chokai belum dikirim → pembahasan masih tertutup
        $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/review")->assertStatus(409);
        $this->runSection($admin, $attempt, 'chokai', []);

        $review = $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/review")->assertOk();
        $this->assertSame(['mojigoi', 'bunpou_dokkai', 'chokai'], array_column($review->json('sections'), 'key'));
        $this->assertCount(31, $review->json('sections.2.test.questions'));
        $this->assertSame(2, $review->json('sections.2.test.questions.0.answer'));
        $this->assertNotEmpty($review->json('sections.2.test.questions.0.script'));
    }
}
