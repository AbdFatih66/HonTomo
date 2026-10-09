<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tes JLPT N3 (soal asli, pack 'private-n3', KHUSUS ADMIN). Ketiga sesi (moji-goi,
 * bunpou-dokkai, chokai) aktif, jadi rekap memberi keputusan lulus/tidak. Test yang hanya
 * menguji sebagian sesi mematikan sesi lain lewat disableSections()/onlyMojigoi().
 */
class JlptN3Test extends TestCase
{
    use RefreshDatabase;

    /** Kunci resmi 正答表 N3 言語知識（文字・語彙）, 33 soal — ditulis ulang agar salah ketik ketahuan. */
    private const OFFICIAL_MOJIGOI_KEY = [
        1 => 3, 2 => 2, 3 => 2, 4 => 3, 5 => 1, 6 => 4, 7 => 2, 8 => 4,
        9 => 3, 10 => 4, 11 => 1, 12 => 2, 13 => 2, 14 => 3,
        15 => 2, 16 => 4, 17 => 3, 18 => 3, 19 => 1, 20 => 2, 21 => 3, 22 => 2, 23 => 1,
        24 => 1, 25 => 4, 26 => 3, 27 => 2, 28 => 3,
        29 => 4, 30 => 1, 31 => 4, 32 => 1, 33 => 1,
    ];

    /** Jumlah soal per もんだい (1–5). */
    private const MONDAI_SIZES = [1 => 8, 2 => 6, 3 => 9, 4 => 5, 5 => 5];

    /** Kunci resmi 正答表 N3 言語知識（文法）・読解, 39 soal. */
    private const OFFICIAL_BUNPOU_KEY = [
        1 => 2, 2 => 2, 3 => 4, 4 => 3, 5 => 4, 6 => 1, 7 => 2, 8 => 3, 9 => 4, 10 => 4, 11 => 2, 12 => 3, 13 => 1,
        14 => 2, 15 => 4, 16 => 2, 17 => 1, 18 => 3,
        19 => 3, 20 => 4, 21 => 3, 22 => 2, 23 => 1,
        24 => 3, 25 => 3, 26 => 3, 27 => 4,
        28 => 3, 29 => 4, 30 => 1, 31 => 3, 32 => 2, 33 => 1,
        34 => 1, 35 => 4, 36 => 4, 37 => 2,
        38 => 2, 39 => 3,
    ];

    /** Jumlah soal per もんだい bunpou-dokkai (1–7). */
    private const BUNPOU_MONDAI_SIZES = [1 => 13, 2 => 5, 3 => 5, 4 => 4, 5 => 6, 6 => 4, 7 => 2];

    /** Passage → jenisnya. */
    private const BUNPOU_PASSAGES = [
        'g3-1' => 'text', 'g4-1' => 'note', 'g4-2' => 'note', 'g4-3' => 'text', 'g4-4' => 'text',
        'g5-1' => 'text', 'g5-2' => 'text', 'g6-1' => 'text', 'g7-1' => 'brochure',
    ];

    /** Kunci resmi 正答表 N3 聴解 (id soal = "{mondai}-{no}"), 27 soal. */
    private const OFFICIAL_CHOKAI_KEY = [
        '1-1' => 2, '1-2' => 4, '1-3' => 1, '1-4' => 2, '1-5' => 3, '1-6' => 2,
        '2-1' => 3, '2-2' => 1, '2-3' => 4, '2-4' => 2, '2-5' => 4, '2-6' => 4,
        '3-1' => 2, '3-2' => 2, '3-3' => 4,
        '4-1' => 2, '4-2' => 3, '4-3' => 2, '4-4' => 1,
        '5-1' => 3, '5-2' => 1, '5-3' => 3, '5-4' => 2, '5-5' => 2, '5-6' => 1, '5-7' => 3, '5-8' => 2,
    ];

    /** Jawaban contoh (例) tiap もんだい chokai, tercetak di 正答表. */
    private const OFFICIAL_CHOKAI_EXAMPLES = [1 => 1, 2 => 4, 3 => 1, 4 => 1, 5 => 2];

    /** Jumlah soal per もんだい chokai (1–5). */
    private const CHOKAI_MONDAI_SIZES = [1 => 6, 2 => 6, 3 => 3, 4 => 4, 5 => 8];

    /** Gambar chokai N3 (screenshot dari lembar soal): nama berkas di public/images/jlpt/n3/. */
    private const CHOKAI_IMAGES = ['l1-1', 'l4-ex', 'l4-1', 'l4-2', 'l4-3', 'l4-4'];

    protected function setUp(): void
    {
        parent::setUp();
        config(['jlpt.packs.private-n3.enabled' => true]);
    }

    /** @param list<string> $sections sesi yang dimatikan (config levels.N3.sections.*.enabled) */
    private function disableSections(array $sections): void
    {
        foreach ($sections as $section)
            config(["jlpt.levels.N3.sections.{$section}.enabled" => false]);
    }

    /** Mulai lalu kirim satu sesi dengan jawaban yang diberikan. */
    private function runSection(User $user, int $attempt, string $section, array $answers): \Illuminate\Testing\TestResponse
    {
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/{$section}/start")->assertOk();

        return $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/{$section}/submit", ['answers' => $answers])->assertOk();
    }

    /** Mulai attempt strict baru di pack N3. */
    private function startAttempt(User $user): int
    {
        return $this->asToken($user)->postJson('/api/jlpt-test/packs/private-n3/attempts', ['mode' => 'strict'])->json('attempt_id');
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

    /** Sisakan moji-goi saja (alur satu sesi). */
    private function onlyMojigoi(): void
    {
        $this->disableSections(['bunpou_dokkai', 'chokai']);
    }

    private function bank(string $section = 'mojigoi'): array
    {
        return json_decode(file_get_contents(resource_path("lang-data/jlpt/n3/{$section}.json")), true);
    }

    public function test_bank_matches_the_official_answer_key(): void
    {
        $this->assertSame(self::OFFICIAL_MOJIGOI_KEY, collect($this->bank()['questions'])->pluck('answer', 'id')->all());
    }

    public function test_bank_is_well_formed(): void
    {
        $bank = $this->bank();

        $this->assertSame(['N3', 'mojigoi', 30], [$bank['level'], $bank['section'], $bank['minutes']]);
        $this->assertCount(config('jlpt.levels.N3.sections.mojigoi.questions'), $bank['questions']);
        $this->assertSame(array_keys(self::MONDAI_SIZES), array_map('intval', array_keys($bank['mondai'])));

        $ids = array_column($bank['questions'], 'id');
        $this->assertSame($ids, array_values(array_unique($ids)), 'id soal harus unik');
        $this->assertSame(range(1, 33), $ids);

        $perMondai = array_count_values(array_column($bank['questions'], 'mondai'));
        ksort($perMondai);
        $this->assertSame(self::MONDAI_SIZES, $perMondai);

        foreach ($bank['questions'] as $q) {
            $this->assertCount(4, $q['choices'], "soal {$q['id']}");
            $this->assertSame(4, count(array_unique($q['choices'])), "soal {$q['id']}: pilihan kembar");
            $this->assertContains($q['answer'], [1, 2, 3, 4], "soal {$q['id']}");
            $this->assertArrayHasKey((string) $q['mondai'], $bank['mondai'], "soal {$q['id']}");
            $this->assertNotSame('', trim($q['stem']), "soal {$q['id']}");
        }

        // もんだい 1–2 dan 4 menggaris bawahi kata yang ditanyakan; もんだい 3 punya kolom kosong
        foreach ($bank['questions'] as $q) {
            if (in_array($q['mondai'], [1, 2, 4], true))
                $this->assertStringContainsString('⟦', $q['stem'], "soal {$q['id']}");
            if ($q['mondai'] === 3)
                $this->assertStringContainsString('（　　）', $q['stem'], "soal {$q['id']}");
            if ($q['mondai'] === 5)
                foreach ($q['choices'] as $c)
                    $this->assertStringContainsString('⟦', $c, "soal {$q['id']}");
        }
    }

    public function test_level_config_follows_the_official_n3_rules(): void
    {
        $cfg = config('jlpt.levels.N3');

        $this->assertSame([95, 180], [$cfg['pass_total'], $cfg['total_max']]);
        $this->assertSame([120, 38], [$cfg['groups']['language_knowledge']['max'], $cfg['groups']['language_knowledge']['min']]);
        $this->assertSame([60, 19], [$cfg['groups']['listening']['max'], $cfg['groups']['listening']['min']]);
        $this->assertSame([33, 39, 27], array_column($cfg['sections'], 'questions'));
        $this->assertTrue($cfg['sections']['mojigoi']['enabled']);
        $this->assertTrue($cfg['sections']['bunpou_dokkai']['enabled']);
        $this->assertSame([70, 39], [$cfg['sections']['bunpou_dokkai']['minutes'], $cfg['sections']['bunpou_dokkai']['questions']]);
        $this->assertTrue($cfg['sections']['chokai']['enabled']);
        $this->assertSame([40, 27], [$cfg['sections']['chokai']['minutes'], $cfg['sections']['chokai']['questions']]);
    }

    public function test_pack_is_admin_only(): void
    {
        $this->assertTrue(config('jlpt.packs.private-n3.admin_only'));

        $user = User::factory()->create();
        $keys = collect($this->asToken($user)->getJson('/api/jlpt-test/packs')->assertOk()->json('packs'))->pluck('key');
        $this->assertFalse($keys->contains('private-n3'));

        $this->asToken($user)->getJson('/api/jlpt-test/packs/private-n3')->assertNotFound();
        $this->asToken($user)->postJson('/api/jlpt-test/packs/private-n3/attempts', ['mode' => 'strict'])->assertNotFound();
        $this->asToken($user)->deleteJson('/api/jlpt-test/packs/private-n3/attempts/current')->assertNotFound();

        $packs = collect($this->asToken($this->admin())->getJson('/api/jlpt-test/packs')->assertOk()->json('packs'))->keyBy('key');
        $this->assertTrue($packs['private-n3']['admin_only']);
        $this->assertSame('N3', $packs['private-n3']['level']);
    }

    public function test_admin_runs_mojigoi_and_gets_a_provisional_n3_recap_without_key_leaks(): void
    {
        $this->onlyMojigoi();
        $admin = $this->admin();

        $attempt = $this->asToken($admin)->postJson('/api/jlpt-test/packs/private-n3/attempts', ['mode' => 'strict'])
            ->assertOk()->assertJsonPath('pack', 'private-n3')->json('attempt_id');

        $started = $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/start")
            ->assertOk()
            ->assertJsonPath('level', 'N3')
            ->assertJsonPath('minutes', 30);

        $this->assertCount(33, $started->json('test.questions'));
        foreach ($started->json('test.questions') as $q)
            $this->assertArrayNotHasKey('answer', $q);

        // sesi lain belum tersedia
        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/start")->assertStatus(422);

        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", ['answers' => self::OFFICIAL_MOJIGOI_KEY])
            ->assertOk()->assertJsonPath('test_completed', true);

        $recap = $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertOk();
        $recap->assertJsonPath('level', 'N3')
            ->assertJsonPath('pass_total', 95)
            ->assertJsonPath('provisional', true)
            ->assertJsonPath('passed', null)
            ->assertJsonPath('sections.0.correct', 33)
            ->assertJsonPath('sections.0.total', 33);
        $this->assertStringStartsWith('HT-N3-', $recap->json('certificate_no'));
    }

    public function test_attempt_is_locked_once_user_is_no_longer_admin(): void
    {
        $user = $this->admin();
        $attempt = $this->asToken($user)->postJson('/api/jlpt-test/packs/private-n3/attempts', ['mode' => 'strict'])->json('attempt_id');
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/start")->assertOk();

        $user->forceFill(['role' => 'user'])->save();

        $this->asToken($user)->getJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi")->assertNotFound();
    }

    // ------------------------------------------------------------------
    // Bunpou-Dokkai
    // ------------------------------------------------------------------

    public function test_bunpou_bank_matches_the_official_answer_key(): void
    {
        $this->assertSame(self::OFFICIAL_BUNPOU_KEY, collect($this->bank('bunpou_dokkai')['questions'])->pluck('answer', 'id')->all());
    }

    public function test_bunpou_bank_is_well_formed(): void
    {
        $bank = $this->bank('bunpou_dokkai');

        $this->assertSame(['N3', 'bunpou_dokkai', 70], [$bank['level'], $bank['section'], $bank['minutes']]);
        $this->assertCount(config('jlpt.levels.N3.sections.bunpou_dokkai.questions'), $bank['questions']);
        $this->assertSame(array_keys(self::BUNPOU_MONDAI_SIZES), array_map('intval', array_keys($bank['mondai'])));

        $ids = array_column($bank['questions'], 'id');
        $this->assertSame(range(1, 39), $ids);

        $perMondai = array_count_values(array_column($bank['questions'], 'mondai'));
        ksort($perMondai);
        $this->assertSame(self::BUNPOU_MONDAI_SIZES, $perMondai);

        foreach ($bank['questions'] as $q) {
            $this->assertCount(4, $q['choices'], "soal {$q['id']}");
            $this->assertSame(4, count(array_unique($q['choices'])), "soal {$q['id']}: pilihan kembar");
            $this->assertContains($q['answer'], [1, 2, 3, 4], "soal {$q['id']}");
            $this->assertArrayHasKey((string) $q['mondai'], $bank['mondai'], "soal {$q['id']}");
            substr_count($q['stem'], '⟦') === substr_count($q['stem'], '⟧') || $this->fail("soal {$q['id']}: ⟦⟧ tidak seimbang");

            // もんだい 3–7 selalu punya bacaan; もんだい 1–2 tidak
            if ($q['mondai'] >= 3)
                $this->assertArrayHasKey($q['passage'], $bank['passages'], "soal {$q['id']}");
            else
                $this->assertArrayNotHasKey('passage', $q, "soal {$q['id']}");

            // もんだい 2: tiga kotak kosong + satu ★
            if ($q['mondai'] === 2) {
                $this->assertSame(1, substr_count($q['stem'], '⟦★⟧'), "soal {$q['id']}");
                $this->assertSame(3, substr_count($q['stem'], '⟦　⟧'), "soal {$q['id']}");
            }
        }

        // もんだい 2 punya contoh + cara menjawab; もんだい 3 punya pengantar
        $this->assertSame(4, $bank['mondai']['2']['example']['answer']);
        $this->assertNotEmpty($bank['mondai']['2']['example']['howto']['figure']['order']);
        $this->assertNotEmpty($bank['mondai']['3']['lead']);
    }

    public function test_bunpou_passages_cover_the_n3_reading_materials(): void
    {
        $bank = $this->bank('bunpou_dokkai');

        $this->assertSame(self::BUNPOU_PASSAGES, collect($bank['passages'])->map(fn ($p) => $p['kind'])->all());

        // もんだい 3: judul + penulis, kotak nomor 19–23 lengkap
        $g3 = $bank['passages']['g3-1'];
        $this->assertTrue($g3['boxed']);
        $this->assertNotEmpty($g3['title']);
        $this->assertNotEmpty($g3['author']);
        foreach (range(19, 23) as $n)
            $this->assertStringContainsString('{{'.$n.'}}', $g3['body']);

        // もんだい 4: (1) memo, (2) email berkepala, (3) teks bercatatan, (4) teks
        $this->assertSame(['(1)', '(2)', '(3)', '(4)'], array_map(fn ($k) => $bank['passages'][$k]['label'], ['g4-1', 'g4-2', 'g4-3', 'g4-4']));
        $this->assertSame('mail', $bank['passages']['g4-2']['variant']);
        $this->assertCount(3, $bank['passages']['g4-2']['headers']);
        $this->assertStringContainsString('（注）', $bank['passages']['g4-3']['body']);

        // もんだい 5: garis bawah bernomor ① ②; (2) punya catatan (注)
        $this->assertStringContainsString('⟦それ⟧①', $bank['passages']['g5-1']['body']);
        $this->assertStringContainsString('⟦なつかしい思い出⟧②', $bank['passages']['g5-1']['body']);
        $this->assertStringContainsString('（注）空き地', $bank['passages']['g5-2']['body']);

        // もんだい 6: tiga garis bawah ① ② ③
        foreach (['⟦この状態《じょうたい》⟧①', '⟦いくつかの工夫⟧②', '⟧③'] as $needle)
            $this->assertStringContainsString($needle, $bank['passages']['g6-1']['body']);

        // もんだい 7: daftar tur 4 + 2 baris dengan nomor ①–⑥
        $data = $bank['passages']['g7-1']['data'];
        $this->assertCount(2, $data['sections']);
        $marks = [];
        foreach ($data['sections'] as $sec)
            foreach ($sec['rows'] as $row)
                $marks[] = $row['mark'];
        $this->assertSame(['①', '②', '③', '④', '⑤', '⑥'], $marks);
        $this->assertCount(3, $data['bullets']);
    }

    public function test_two_sessions_without_chokai_give_a_provisional_recap(): void
    {
        $this->disableSections(['chokai']);
        $admin = $this->admin();
        $attempt = $this->asToken($admin)->postJson('/api/jlpt-test/packs/private-n3/attempts', ['mode' => 'strict'])->json('attempt_id');

        // urutan dijaga: bunpou tidak bisa dibuka sebelum moji-goi dikirim
        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/start")->assertStatus(422);

        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/start")->assertOk();
        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", ['answers' => self::OFFICIAL_MOJIGOI_KEY])
            ->assertOk()->assertJsonPath('test_completed', false);

        $started = $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/start")
            ->assertOk()->assertJsonPath('level', 'N3')->assertJsonPath('minutes', 70);

        $this->assertCount(39, $started->json('test.questions'));
        $this->assertCount(count(self::BUNPOU_PASSAGES), $started->json('test.passages'));
        foreach ($started->json('test.questions') as $q)
            $this->assertArrayNotHasKey('answer', $q);

        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/submit", ['answers' => self::OFFICIAL_BUNPOU_KEY])
            ->assertOk()->assertJsonPath('test_completed', true);

        $recap = $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertOk();
        $recap->assertJsonPath('provisional', true)
            ->assertJsonPath('passed', null)
            ->assertJsonPath('sections.1.key', 'bunpou_dokkai')
            ->assertJsonPath('sections.1.correct', 39)
            ->assertJsonPath('sections.1.total', 39);

        // rincian per もんだい: 7 baris, semuanya benar
        $breakdown = $recap->json('sections.1.breakdown');
        $this->assertCount(7, $breakdown);
        foreach ($breakdown as $row)
            $this->assertSame($row['total'], $row['correct']);

        // kelompok bahasa: 72 soal dari 72 → 120 poin
        $language = collect($recap->json('groups'))->firstWhere('key', 'language_knowledge');
        $this->assertSame(120, $language['score']);
    }

    // ------------------------------------------------------------------
    // Chokai (聴解)
    // ------------------------------------------------------------------

    public function test_chokai_bank_matches_the_official_answer_key(): void
    {
        $bank = $this->bank('chokai');

        $this->assertSame(self::OFFICIAL_CHOKAI_KEY, collect($bank['questions'])->pluck('answer', 'id')->all());

        foreach (self::OFFICIAL_CHOKAI_EXAMPLES as $mondai => $answer)
            $this->assertSame($answer, $bank['mondai'][(string) $mondai]['example']['answer'], "れい もんだい {$mondai}");
    }

    public function test_chokai_bank_is_well_formed(): void
    {
        $bank = $this->bank('chokai');
        $tracks = [$bank['audio']['intro'], $bank['audio']['outro']];

        $this->assertSame(['N3', 'chokai', 40], [$bank['level'], $bank['section'], $bank['minutes']]);
        $this->assertCount(config('jlpt.levels.N3.sections.chokai.questions'), $bank['questions']);
        $this->assertSame(['1', '2', '3', '4', '5'], array_keys($bank['mondai']));

        foreach ($bank['mondai'] as $no => $m) {
            $this->assertNotEmpty($m['instruction'], "もんだい {$no}");
            $this->assertIsInt($m['audio'], "もんだい {$no} audio");
            $this->assertIsInt($m['example']['audio'], "れい {$no} audio");
            $this->assertNotEmpty($m['example']['script'], "れい {$no} naskah");
            $this->assertGreaterThan(0, $m['answer_seconds'], "もんだい {$no}");

            array_push($tracks, $m['audio'], $m['example']['audio']);
            if (isset($m['audio_before']))
                $tracks[] = $m['audio_before'];
        }

        // もんだい 3 dan 5: lembar soal kosong (memo); もんだい 4: gambar + ➡ ada di dalam gambar
        $this->assertTrue($bank['mondai']['3']['memo']);
        $this->assertTrue($bank['mondai']['5']['memo']);
        $this->assertArrayNotHasKey('memo', $bank['mondai']['1']);
        $this->assertArrayNotHasKey('memo', $bank['mondai']['4']);

        $ids = array_column($bank['questions'], 'id');
        $this->assertSame($ids, array_values(array_unique($ids)), 'id soal harus unik');

        $perMondai = array_count_values(array_column($bank['questions'], 'mondai'));
        ksort($perMondai);
        $this->assertSame(self::CHOKAI_MONDAI_SIZES, $perMondai);

        foreach ($bank['questions'] as $q) {
            $id = $q['id'];
            $count = isset($q['choices']) ? count($q['choices']) : $q['choice_count'];

            $this->assertSame("{$q['mondai']}-{$q['no']}", $id);
            $this->assertNotEmpty($q['script'], $id);
            $this->assertIsInt($q['audio'], $id);
            $tracks[] = $q['audio'];

            // もんだい 1–2: 4 pilihan bertulis; 3: 4 pilihan hanya terdengar; 4–5: 3 pilihan hanya terdengar
            $this->assertSame($q['mondai'] <= 3 ? 4 : 3, $count, $id);
            $this->assertGreaterThanOrEqual(1, $q['answer'], $id);
            $this->assertLessThanOrEqual($count, $q['answer'], $id);

            if ($q['mondai'] >= 3)
                $this->assertArrayNotHasKey('choices', $q, $id);
            else
                $this->assertCount(4, array_unique($q['choices']), "{$id}: pilihan kembar");

            // ➡ sudah tercetak di gambar hasil screenshot: aplikasi tidak menggambar panah/label lagi
            $this->assertArrayNotHasKey('arrow', $q, $id);
            $this->assertArrayNotHasKey('marks', $q, $id);
        }

        // Nomor trek = nomor awalan berkas audio 01–40: tiap nomor dipakai tepat sekali.
        // Trek 18 = istirahat (ちょっと 休みましょう) sebelum もんだい 3 (audio_before).
        $this->assertSame(18, $bank['mondai']['3']['audio_before']);
        $this->assertSame([2, 10, 19, 24, 30], array_column($bank['mondai'], 'audio'));
        $this->assertSame([3, 11, 20, 25, 31], array_map(fn ($m) => $m['example']['audio'], array_values($bank['mondai'])));
        $this->assertSame(40, $bank['audio']['outro']);
        sort($tracks);
        $this->assertSame(range(1, 40), $tracks);

        // gambar: 1番 もんだい 1 + れい dan 1–4番 もんだい 4, semuanya di /images/jlpt/n3/
        $images = [];
        array_walk_recursive($bank, function ($v, $k) use (&$images) {
            if ($k === 'image' && is_string($v))
                $images[] = $v;
        });
        sort($images);
        $expected = array_map(fn ($n) => "/images/jlpt/n3/{$n}.png", self::CHOKAI_IMAGES);
        sort($expected);
        $this->assertSame($expected, $images);
    }

    public function test_chokai_images_are_documented(): void
    {
        $doc = file_get_contents(base_path('docs/jlpt-real-images-n3.md'));

        foreach (self::CHOKAI_IMAGES as $name)
            $this->assertStringContainsString("`{$name}.png`", $doc, "{$name}.png belum ada di docs/jlpt-real-images-n3.md");
    }

    public function test_chokai_payload_has_audio_urls_but_never_the_key_or_the_script(): void
    {
        $root = 'audio-test-n3-'.uniqid();
        $dir = public_path("{$root}/n3");
        mkdir($dir, 0777, true);
        foreach (['04-mondai1-1ban.mp3', '40-owari.mp3'] as $name)
            file_put_contents("{$dir}/{$name}", '');
        config(['jlpt.packs.private-n3.audio_path' => "/{$root}"]);

        try {
            $admin = $this->admin();
            $attempt = $this->startAttempt($admin);
            $this->runSection($admin, $attempt, 'mojigoi', self::OFFICIAL_MOJIGOI_KEY);
            $this->runSection($admin, $attempt, 'bunpou_dokkai', self::OFFICIAL_BUNPOU_KEY);

            $start = $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/chokai/start")->assertOk();
            $resume = $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/sections/chokai")->assertOk();
            $base = "/{$root}/n3/";

            foreach ([$start, $resume] as $res) {
                $this->assertSame('N3', $res->json('level'));
                $this->assertSame(40, $res->json('minutes'));
                $this->assertCount(27, $res->json('test.questions'));

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

                // berkas yang ada → URL; yang belum ada → pesan jelas
                $this->assertSame($base.'04-mondai1-1ban.mp3', $res->json('test.questions.0.audio'));
                $this->assertSame($base.'05-tidak-ditemukan.mp3', $res->json('test.questions.1.audio'));
                $this->assertSame($base.'40-owari.mp3', $res->json('test.audio.outro'));
                $this->assertSame($base.'01-tidak-ditemukan.mp3', $res->json('test.audio.intro'));
                // trek 18 = istirahat (ちょっと 休みましょう) sebelum もんだい 3
                $this->assertSame($base.'18-tidak-ditemukan.mp3', $res->json('test.mondai.3.audio_before'));

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

        $this->assertSame('N3', $recap['level']);
        $this->assertSame(95, $recap['pass_total']);
        $this->assertFalse($recap['provisional']);
        $this->assertTrue($recap['passed']);
        $this->assertSame(180, $recap['total_score']);
        $this->assertMatchesRegularExpression('/^HT-N3-\d{8}-'.sprintf('%06d', $attempt).'$/', $recap['certificate_no']);

        $chokai = collect($recap['sections'])->firstWhere('key', 'chokai');
        $this->assertSame([27, 27], [$chokai['correct'], $chokai['total']]);
        $this->assertCount(5, $chokai['breakdown']);

        $listening = collect($recap['groups'])->firstWhere('key', 'listening');
        $this->assertSame(60, $listening['score']);
    }

    public function test_chokai_grades_string_question_ids_and_reports_per_mondai(): void
    {
        $admin = $this->admin();
        $attempt = $this->startAttempt($admin);
        $this->runSection($admin, $attempt, 'mojigoi', self::OFFICIAL_MOJIGOI_KEY);
        $this->runSection($admin, $attempt, 'bunpou_dokkai', self::OFFICIAL_BUNPOU_KEY);

        // semua benar kecuali 3-3 (salah) dan 5-6 (kosong)
        $answers = self::OFFICIAL_CHOKAI_KEY;
        $answers['3-3'] = $answers['3-3'] === 1 ? 2 : 1;
        unset($answers['5-6']);
        $this->runSection($admin, $attempt, 'chokai', $answers);

        $recap = $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertOk()->json();
        $chokai = collect($recap['sections'])->firstWhere('key', 'chokai');
        $breakdown = collect($chokai['breakdown']);

        $this->assertSame(25, $chokai['correct']);
        $this->assertSame(27, $chokai['total']);
        $this->assertSame(6, $breakdown->firstWhere('mondai', 1)['correct']);
        $this->assertSame(2, $breakdown->firstWhere('mondai', 3)['correct']);
        $this->assertSame(1, $breakdown->firstWhere('mondai', 5)['unanswered']);
        $this->assertFalse($recap['provisional']);
        $this->assertTrue($recap['passed']);
    }

    public function test_review_exposes_chokai_key_and_script_only_after_the_test_is_completed(): void
    {
        $admin = $this->admin();
        $attempt = $this->startAttempt($admin);
        $this->runSection($admin, $attempt, 'mojigoi', []);
        $this->runSection($admin, $attempt, 'bunpou_dokkai', []);
        // chokai belum dikirim → review masih tertutup
        $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/review")->assertStatus(409);
        $this->runSection($admin, $attempt, 'chokai', []);

        $review = $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/review")->assertOk();
        $this->assertSame(['mojigoi', 'bunpou_dokkai', 'chokai'], array_column($review->json('sections'), 'key'));
        $this->assertSame(27, count($review->json('sections.2.test.questions')));
        $this->assertSame(2, $review->json('sections.2.test.questions.0.answer'));
        $this->assertNotEmpty($review->json('sections.2.test.questions.0.script'));
    }
}
