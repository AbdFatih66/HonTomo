<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tes JLPT N4 (soal asli, pack 'private-n4', KHUSUS ADMIN). Ketiga sesi (moji-goi,
 * bunpou-dokkai, chokai) aktif, jadi rekap memberi keputusan lulus/tidak. Test yang
 * hanya menguji sebagian sesi menonaktifkan sesi lain lewat disableSections(), sehingga
 * rekapnya SEMENTARA.
 */
class JlptN4Test extends TestCase
{
    use RefreshDatabase;

    /** Kunci resmi 正答表 N4 言語知識（文字・語彙）, 34 soal — ditulis ulang agar salah ketik ketahuan. */
    private const OFFICIAL_MOJIGOI_KEY = [
        1 => 1, 2 => 1, 3 => 4, 4 => 2, 5 => 2, 6 => 3, 7 => 1, 8 => 2, 9 => 4, 10 => 1,
        11 => 4, 12 => 3, 13 => 4, 14 => 4, 15 => 1, 16 => 4, 17 => 3, 18 => 2, 19 => 2, 20 => 4,
        21 => 3, 22 => 1, 23 => 2, 24 => 3, 25 => 3, 26 => 2, 27 => 1, 28 => 3, 29 => 2, 30 => 4,
        31 => 3, 32 => 4, 33 => 1, 34 => 2,
    ];

    /** Jumlah soal per もんだい (1–5). */
    private const MONDAI_SIZES = [1 => 9, 2 => 6, 3 => 9, 4 => 5, 5 => 5];

    /** Kunci resmi 正答表 N4 言語知識（文法）・読解, 35 soal. */
    private const OFFICIAL_BUNPOU_KEY = [
        1 => 3, 2 => 4, 3 => 1, 4 => 2, 5 => 4, 6 => 2, 7 => 3, 8 => 1, 9 => 2, 10 => 4,
        11 => 1, 12 => 1, 13 => 3, 14 => 4, 15 => 2, 16 => 3, 17 => 2, 18 => 4, 19 => 3, 20 => 3,
        21 => 2, 22 => 3, 23 => 2, 24 => 1, 25 => 4, 26 => 4, 27 => 3, 28 => 2, 29 => 3, 30 => 2,
        31 => 4, 32 => 4, 33 => 1, 34 => 3, 35 => 2,
    ];

    /** Jumlah soal per もんだい bunpou-dokkai (1–6). */
    private const BUNPOU_MONDAI_SIZES = [1 => 15, 2 => 5, 3 => 5, 4 => 4, 5 => 4, 6 => 2];

    /** @var list<string> kunci passage yang dipakai soal bunpou-dokkai */
    private const BUNPOU_PASSAGES = ['m3-1', 'm4-1', 'm4-2', 'm4-3', 'm4-4', 'm5-1', 'm6-1'];

    /** Kunci resmi 正答表 N4 聴解 (id soal = "{mondai}-{no}"), 28 soal. */
    private const OFFICIAL_CHOKAI_KEY = [
        '1-1' => 1, '1-2' => 4, '1-3' => 3, '1-4' => 4, '1-5' => 3, '1-6' => 2, '1-7' => 2, '1-8' => 1,
        '2-1' => 4, '2-2' => 2, '2-3' => 3, '2-4' => 3, '2-5' => 1, '2-6' => 2, '2-7' => 3,
        '3-1' => 1, '3-2' => 2, '3-3' => 1, '3-4' => 2, '3-5' => 1,
        '4-1' => 2, '4-2' => 3, '4-3' => 2, '4-4' => 1, '4-5' => 2, '4-6' => 3, '4-7' => 3, '4-8' => 1,
    ];

    /** Jawaban contoh (例) tiap もんだい chokai, tercetak di 正答表. */
    private const OFFICIAL_CHOKAI_EXAMPLES = [1 => 4, 2 => 3, 3 => 3, 4 => 3];

    /** Jumlah soal per もんだい chokai (1–4). */
    private const CHOKAI_MONDAI_SIZES = [1 => 8, 2 => 7, 3 => 5, 4 => 8];

    protected function setUp(): void
    {
        parent::setUp();
        config(['jlpt.packs.private-n4.enabled' => true]);
    }

    /** @param list<string> $sections sesi yang dimatikan (config levels.N4.sections.*.enabled) */
    private function disableSections(array $sections): void
    {
        foreach ($sections as $section)
            config(["jlpt.levels.N4.sections.{$section}.enabled" => false]);
    }

    /** Sisakan moji-goi saja (alur satu sesi). */
    private function onlyMojigoi(): void
    {
        $this->disableSections(['bunpou_dokkai', 'chokai']);
    }

    /** Mulai lalu kirim satu sesi dengan jawaban yang diberikan. */
    private function runSection(User $user, int $attempt, string $section, array $answers): \Illuminate\Testing\TestResponse
    {
        $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/{$section}/start")->assertOk();

        return $this->asToken($user)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/{$section}/submit", ['answers' => $answers])->assertOk();
    }

    /** Mulai attempt strict baru di pack N4. */
    private function startAttempt(User $user): int
    {
        return $this->asToken($user)->postJson('/api/jlpt-test/packs/private-n4/attempts', ['mode' => 'strict'])->json('attempt_id');
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
        return json_decode(file_get_contents(resource_path("lang-data/jlpt/n4/{$section}.json")), true);
    }

    public function test_bank_matches_the_official_answer_key(): void
    {
        $this->assertSame(self::OFFICIAL_MOJIGOI_KEY, collect($this->bank()['questions'])->pluck('answer', 'id')->all());
    }

    public function test_bank_is_well_formed(): void
    {
        $bank = $this->bank();

        $this->assertSame('N4', $bank['level']);
        $this->assertSame('mojigoi', $bank['section']);
        $this->assertCount(config('jlpt.levels.N4.sections.mojigoi.questions'), $bank['questions']);

        $ids = array_column($bank['questions'], 'id');
        $this->assertSame($ids, array_values(array_unique($ids)), 'id soal harus unik');

        $perMondai = array_count_values(array_column($bank['questions'], 'mondai'));
        ksort($perMondai);
        $this->assertSame(self::MONDAI_SIZES, $perMondai);

        foreach ($bank['questions'] as $q) {
            $this->assertCount(4, $q['choices'], "soal {$q['id']}");
            $this->assertContains($q['answer'], [1, 2, 3, 4], "soal {$q['id']}");
            $this->assertArrayHasKey($q['mondai'], $bank['mondai'], "soal {$q['id']}");
        }

        foreach ($bank['mondai'] as $m) {
            $this->assertCount(4, $m['example']['choices']);
            $this->assertContains($m['example']['answer'], [1, 2, 3, 4]);
        }
    }

    public function test_level_config_follows_the_official_n4_rules(): void
    {
        $cfg = config('jlpt.levels.N4');

        $this->assertSame(90, $cfg['pass_total']);
        $this->assertSame(180, $cfg['total_max']);
        $this->assertSame([120, 38], [$cfg['groups']['language_knowledge']['max'], $cfg['groups']['language_knowledge']['min']]);
        $this->assertSame([60, 19], [$cfg['groups']['listening']['max'], $cfg['groups']['listening']['min']]);
        $this->assertTrue($cfg['sections']['mojigoi']['enabled']);
        $this->assertTrue($cfg['sections']['bunpou_dokkai']['enabled']);
        $this->assertSame([60, 35], [$cfg['sections']['bunpou_dokkai']['minutes'], $cfg['sections']['bunpou_dokkai']['questions']]);
        $this->assertTrue($cfg['sections']['chokai']['enabled']);
        $this->assertSame([35, 28], [$cfg['sections']['chokai']['minutes'], $cfg['sections']['chokai']['questions']]);
    }

    public function test_pack_is_admin_only(): void
    {
        $this->assertTrue(config('jlpt.packs.private-n4.admin_only'));

        $user = User::factory()->create();
        $keys = collect($this->asToken($user)->getJson('/api/jlpt-test/packs')->assertOk()->json('packs'))->pluck('key');
        $this->assertFalse($keys->contains('private-n4'));

        $this->asToken($user)->getJson('/api/jlpt-test/packs/private-n4')->assertNotFound();
        $this->asToken($user)->postJson('/api/jlpt-test/packs/private-n4/attempts', ['mode' => 'strict'])->assertNotFound();
        $this->asToken($user)->deleteJson('/api/jlpt-test/packs/private-n4/attempts/current')->assertNotFound();

        $packs = collect($this->asToken($this->admin())->getJson('/api/jlpt-test/packs')->assertOk()->json('packs'))->keyBy('key');
        $this->assertTrue($packs['private-n4']['admin_only']);
        $this->assertSame('N4', $packs['private-n4']['level']);
    }

    public function test_admin_runs_mojigoi_and_gets_a_provisional_n4_recap_without_key_leaks(): void
    {
        $this->onlyMojigoi();
        $admin = $this->admin();

        $attempt = $this->asToken($admin)->postJson('/api/jlpt-test/packs/private-n4/attempts', ['mode' => 'strict'])
            ->assertOk()->assertJsonPath('pack', 'private-n4')->json('attempt_id');

        $started = $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/start")
            ->assertOk()
            ->assertJsonPath('level', 'N4')
            ->assertJsonPath('minutes', 30);

        $this->assertCount(34, $started->json('test.questions'));
        foreach ($started->json('test.questions') as $q)
            $this->assertArrayNotHasKey('answer', $q);

        // sesi lain belum tersedia
        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/start")->assertStatus(422);

        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", ['answers' => self::OFFICIAL_MOJIGOI_KEY])
            ->assertOk()->assertJsonPath('test_completed', true);

        $recap = $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertOk();
        $recap->assertJsonPath('level', 'N4')
            ->assertJsonPath('pass_total', 90)
            ->assertJsonPath('provisional', true)
            ->assertJsonPath('passed', null)
            ->assertJsonPath('sections.0.correct', 34)
            ->assertJsonPath('sections.0.total', 34);
        $this->assertStringStartsWith('HT-N4-', $recap->json('certificate_no'));
    }

    public function test_attempt_is_locked_once_user_is_no_longer_admin(): void
    {
        $user = $this->admin();
        $attempt = $this->asToken($user)->postJson('/api/jlpt-test/packs/private-n4/attempts', ['mode' => 'strict'])->json('attempt_id');
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

        $this->assertSame(['N4', 'bunpou_dokkai'], [$bank['level'], $bank['section']]);
        $this->assertSame(60, $bank['minutes']);
        $this->assertCount(config('jlpt.levels.N4.sections.bunpou_dokkai.questions'), $bank['questions']);

        $ids = array_column($bank['questions'], 'id');
        $this->assertSame($ids, array_values(array_unique($ids)), 'id soal harus unik');

        $perMondai = array_count_values(array_column($bank['questions'], 'mondai'));
        ksort($perMondai);
        $this->assertSame(self::BUNPOU_MONDAI_SIZES, $perMondai);

        $usedPassages = [];
        foreach ($bank['questions'] as $q) {
            $this->assertCount(4, $q['choices'], "soal {$q['id']}");
            $this->assertContains($q['answer'], [1, 2, 3, 4], "soal {$q['id']}");
            $this->assertArrayHasKey($q['mondai'], $bank['mondai'], "soal {$q['id']}");

            if (isset($q['passage'])) {
                $this->assertArrayHasKey($q['passage'], $bank['passages'], "soal {$q['id']}");
                $usedPassages[$q['passage']] = true;
            }
            // soal bacaan (mondai 3–6) wajib punya passage; soal bertanda ★ punya empat kolom
            if ($q['mondai'] >= 3)
                $this->assertArrayHasKey('passage', $q, "soal {$q['id']}");
            if ($q['mondai'] === 2)
                $this->assertSame(1, substr_count($q['stem'], '⟦★⟧'), "soal {$q['id']}");
        }

        $this->assertEqualsCanonicalizing(self::BUNPOU_PASSAGES, array_keys($bank['passages']));
        $this->assertEqualsCanonicalizing(self::BUNPOU_PASSAGES, array_keys($usedPassages));

        foreach ($bank['passages'] as $key => $p)
            $this->assertContains($p['kind'], ['text', 'note', 'notice', 'poster'], $key);

        // kotak nomor 21–25 di bacaan もんだい 3 harus lengkap
        foreach (range(21, 25) as $n)
            $this->assertStringContainsString('{{'.$n.'}}', $bank['passages']['m3-1']['body']);
    }

    public function test_two_sessions_without_chokai_give_a_provisional_recap(): void
    {
        $this->disableSections(['chokai']);
        $admin = $this->admin();
        $attempt = $this->asToken($admin)->postJson('/api/jlpt-test/packs/private-n4/attempts', ['mode' => 'strict'])->json('attempt_id');

        // urutan dijaga: bunpou tidak bisa dibuka sebelum moji-goi dikirim
        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/start")->assertStatus(422);

        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/start")->assertOk();
        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", ['answers' => self::OFFICIAL_MOJIGOI_KEY])
            ->assertOk()->assertJsonPath('test_completed', false);

        $started = $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/start")
            ->assertOk()->assertJsonPath('level', 'N4')->assertJsonPath('minutes', 60);

        $this->assertCount(35, $started->json('test.questions'));
        $this->assertCount(count(self::BUNPOU_PASSAGES), $started->json('test.passages'));
        foreach ($started->json('test.questions') as $q)
            $this->assertArrayNotHasKey('answer', $q);

        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/bunpou_dokkai/submit", ['answers' => self::OFFICIAL_BUNPOU_KEY])
            ->assertOk()->assertJsonPath('test_completed', true);

        $recap = $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertOk();
        $recap->assertJsonPath('provisional', true)
            ->assertJsonPath('passed', null)
            ->assertJsonPath('sections.1.key', 'bunpou_dokkai')
            ->assertJsonPath('sections.1.correct', 35)
            ->assertJsonPath('sections.1.total', 35);

        // rincian per もんだい bunpou: 6 baris, semua benar
        $breakdown = $recap->json('sections.1.breakdown');
        $this->assertCount(6, $breakdown);
        foreach ($breakdown as $row)
            $this->assertSame($row['total'], $row['correct']);

        // kelompok bahasa = 69 soal dari 69 → 120 poin
        $language = collect($recap->json('groups'))->firstWhere('key', 'language_knowledge');
        $this->assertSame(120, $language['score']);
    }

    public function test_review_exposes_key_only_after_the_test_is_completed(): void
    {
        $admin = $this->admin();
        $attempt = $this->asToken($admin)->postJson('/api/jlpt-test/packs/private-n4/attempts', ['mode' => 'strict'])->json('attempt_id');
        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/start")->assertOk();
        $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/review")->assertStatus(409);

        $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/mojigoi/submit", ['answers' => []])->assertOk();
        $this->runSection($admin, $attempt, 'bunpou_dokkai', []);
        // chokai belum dikirim → review masih tertutup
        $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/review")->assertStatus(409);
        $this->runSection($admin, $attempt, 'chokai', []);

        $review = $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/review")->assertOk();
        $this->assertSame(['mojigoi', 'bunpou_dokkai', 'chokai'], array_column($review->json('sections'), 'key'));
        $this->assertSame(35, count($review->json('sections.1.test.questions')));
        $this->assertSame(3, $review->json('sections.1.test.questions.0.answer'));

        // di review kunci & naskah chokai ikut dikirim
        $this->assertSame(28, count($review->json('sections.2.test.questions')));
        $this->assertSame(1, $review->json('sections.2.test.questions.0.answer'));
        $this->assertNotEmpty($review->json('sections.2.test.questions.0.script'));
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

        $this->assertSame(['N4', 'chokai'], [$bank['level'], $bank['section']]);
        $this->assertSame(35, $bank['minutes']);
        $this->assertCount(config('jlpt.levels.N4.sections.chokai.questions'), $bank['questions']);
        $this->assertSame(['1', '2', '3', '4'], array_keys($bank['mondai']));

        foreach ($bank['mondai'] as $no => $m) {
            $this->assertNotEmpty($m['instruction'], "もんだい {$no}");
            $this->assertIsInt($m['audio'], "もんだい {$no} audio");
            $this->assertIsInt($m['example']['audio'], "れい {$no} audio");
            $this->assertNotEmpty($m['example']['script'], "れい {$no} naskah");
            $this->assertGreaterThan(0, $m['answer_seconds'], "もんだい {$no} jeda");
            array_push($tracks, $m['audio'], $m['example']['audio']);
            if (isset($m['audio_before']))
                $tracks[] = $m['audio_before'];
        }

        // contoh もんだい 3 = gambar PNG; もんだい 4 punya kolom memo
        $this->assertSame('/images/jlpt/n4/l3-ex.png', $bank['mondai']['3']['example']['image']['image']);
        $this->assertTrue($bank['mondai']['4']['memo']);

        $ids = array_column($bank['questions'], 'id');
        $this->assertSame($ids, array_values(array_unique($ids)), 'id soal harus unik');

        $perMondai = array_count_values(array_column($bank['questions'], 'mondai'));
        ksort($perMondai);
        $this->assertSame(self::CHOKAI_MONDAI_SIZES, $perMondai);

        foreach ($bank['questions'] as $q) {
            $id = $q['id'];

            $this->assertSame("{$q['mondai']}-{$q['no']}", $id);
            $this->assertNotEmpty($q['script'], $id);
            $this->assertIsInt($q['audio'], $id);
            $tracks[] = $q['audio'];

            // もんだい 1–2: empat pilihan; もんだい 3–4: tiga pilihan yang hanya terdengar
            $count = isset($q['choices']) ? count($q['choices']) : $q['choice_count'];
            $this->assertSame($q['mondai'] <= 2 ? 4 : 3, $count, $id);
            $this->assertGreaterThanOrEqual(1, $q['answer'], $id);
            $this->assertLessThanOrEqual($count, $q['answer'], $id);
            if ($q['mondai'] >= 3)
                $this->assertArrayNotHasKey('choices', $q, $id);

            // gambar = berkas PNG { image, alt } di public/images/jlpt/n4
            $pics = [];
            if (isset($q['image']))
                $pics[] = $q['image'];
            foreach ($q['choices'] ?? [] as $c) {
                if (is_array($c))
                    $pics[] = $c;
            }
            $this->assertArrayNotHasKey('choice_art', $q, $id);
            foreach ($pics as $pic) {
                $this->assertMatchesRegularExpression('#^/images/jlpt/n4/(l1-[1256]-c[1-4]|l1-4|l3-[1-5])\.png$#', $pic['image'], $id);
                $this->assertNotEmpty($pic['alt'], $id);
            }

            // もんだい 3: gambar + tanda ➡ di tengah atas (orang yang bicara berada di tengah gambar)
            if ($q['mondai'] === 3) {
                $this->assertCount(1, $pics, $id);
                $this->assertSame(['x' => 50, 'y' => 9], $q['arrow'], "{$id} arrow");
            }
        }

        // soal bergambar: 1–1, 1–2, 1–5, 1–6 (4 gambar pilihan), 1–4 (satu gambar + label ア–エ), 3–1…3–5
        $byId = array_column($bank['questions'], null, 'id');
        foreach (['1-1', '1-2', '1-5', '1-6'] as $id) {
            $this->assertCount(4, $byId[$id]['choices'], $id);
            $this->assertIsArray($byId[$id]['choices'][0], $id);
        }
        $this->assertSame(['ア', 'イ', 'ウ', 'エ'], array_column($byId['1-4']['marks'], 'text'));
        $this->assertSame(['アイ', 'アエ', 'イウ', 'イエ'], $byId['1-4']['choices']);

        // Nomor trek = nomor awalan berkas audio 01–39, urutan putar rekaman: tiap nomor dipakai tepat sekali.
        // Trek 21 = istirahat (ちょっと やすみましょう) sebelum もんだい 3 (audio_before), jadi もんだい 3–4 mulai dari trek 22.
        $this->assertSame(21, $bank['mondai']['3']['audio_before']);
        $this->assertSame([22, 23], [$bank['mondai']['3']['audio'], $bank['mondai']['3']['example']['audio']]);
        $this->assertSame([29, 30], [$bank['mondai']['4']['audio'], $bank['mondai']['4']['example']['audio']]);
        $this->assertSame(39, $bank['audio']['outro']);
        sort($tracks);
        $this->assertSame(range(1, 39), $tracks);
    }

    public function test_chokai_payload_has_audio_urls_but_never_the_key_or_the_script(): void
    {
        $root = 'audio-test-n4-'.uniqid();
        $dir = public_path("{$root}/n4");
        mkdir($dir, 0777, true);
        foreach (['04-mondai1-1ban.mp3', '39-owari.mp3'] as $name)
            file_put_contents("{$dir}/{$name}", '');
        config(['jlpt.packs.private-n4.audio_path' => "/{$root}"]);

        try {
            $admin = $this->admin();
            $attempt = $this->startAttempt($admin);
            $this->runSection($admin, $attempt, 'mojigoi', self::OFFICIAL_MOJIGOI_KEY);
            $this->runSection($admin, $attempt, 'bunpou_dokkai', self::OFFICIAL_BUNPOU_KEY);

            $start = $this->asToken($admin)->postJson("/api/jlpt-test/attempts/{$attempt}/sections/chokai/start")->assertOk();
            $resume = $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/sections/chokai")->assertOk();
            $base = "/{$root}/n4/";

            foreach ([$start, $resume] as $res) {
                $this->assertSame('N4', $res->json('level'));
                $this->assertSame(35, $res->json('minutes'));
                $this->assertCount(28, $res->json('test.questions'));

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

                // berkas yang ada → URL ter-encode; yang belum ada → pesan jelas
                $this->assertSame($base.'04-mondai1-1ban.mp3', $res->json('test.questions.0.audio'));
                $this->assertSame($base.'05-tidak-ditemukan.mp3', $res->json('test.questions.1.audio'));
                $this->assertSame($base.'39-owari.mp3', $res->json('test.audio.outro'));
                $this->assertSame($base.'01-tidak-ditemukan.mp3', $res->json('test.audio.intro'));
                // trek 21 = istirahat (ちょっと やすみましょう) sebelum もんだい 3
                $this->assertSame($base.'21-tidak-ditemukan.mp3', $res->json('test.mondai.3.audio_before'));

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

        $this->assertSame('N4', $recap['level']);
        $this->assertSame(90, $recap['pass_total']);
        $this->assertFalse($recap['provisional']);
        $this->assertTrue($recap['passed']);
        $this->assertSame(180, $recap['total_score']);
        $this->assertSame($admin->name, $recap['participant']);
        $this->assertMatchesRegularExpression('/^HT-N4-\d{8}-'.sprintf('%06d', $attempt).'$/', $recap['certificate_no']);

        $chokai = collect($recap['sections'])->firstWhere('key', 'chokai');
        $this->assertSame([28, 28], [$chokai['correct'], $chokai['total']]);
        $this->assertCount(4, $chokai['breakdown']);

        $listening = collect($recap['groups'])->firstWhere('key', 'listening');
        $this->assertSame(60, $listening['score']);
    }

    public function test_chokai_grades_string_question_ids_and_reports_per_mondai(): void
    {
        $admin = $this->admin();
        $attempt = $this->startAttempt($admin);
        $this->runSection($admin, $attempt, 'mojigoi', self::OFFICIAL_MOJIGOI_KEY);
        $this->runSection($admin, $attempt, 'bunpou_dokkai', self::OFFICIAL_BUNPOU_KEY);

        // semua benar kecuali 3-3 (salah) dan 4-6 (kosong)
        $answers = self::OFFICIAL_CHOKAI_KEY;
        $answers['3-3'] = $answers['3-3'] === 1 ? 2 : 1;
        unset($answers['4-6']);
        $this->runSection($admin, $attempt, 'chokai', $answers);

        $recap = $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertOk()->json();
        $chokai = collect($recap['sections'])->firstWhere('key', 'chokai');
        $breakdown = collect($chokai['breakdown']);

        $this->assertSame(26, $chokai['correct']);
        $this->assertSame(28, $chokai['total']);
        $this->assertSame(8, $breakdown->firstWhere('mondai', 1)['correct']);
        $this->assertSame(4, $breakdown->firstWhere('mondai', 3)['correct']);
        $this->assertSame(1, $breakdown->firstWhere('mondai', 4)['unanswered']);
        $this->assertFalse($recap['provisional']);
        $this->assertTrue($recap['passed']);
    }

    public function test_a_listening_score_below_its_minimum_fails_even_with_a_passing_total(): void
    {
        $admin = $this->admin();
        $attempt = $this->startAttempt($admin);
        $this->runSection($admin, $attempt, 'mojigoi', self::OFFICIAL_MOJIGOI_KEY);
        $this->runSection($admin, $attempt, 'bunpou_dokkai', self::OFFICIAL_BUNPOU_KEY);
        $this->runSection($admin, $attempt, 'chokai', []);

        // bahasa & membaca sempurna (120) + menyimak 0 → total 120 ≥ 90, tetapi menyimak < 19 → tidak lulus
        $recap = $this->asToken($admin)->getJson("/api/jlpt-test/attempts/{$attempt}/recap")->assertOk()->json();

        $this->assertSame(120, $recap['total_score']);
        $this->assertFalse($recap['passed']);
    }

    public function test_chokai_audio_files_exist_in_the_public_folder(): void
    {
        if (! is_dir(public_path('audio/jlpt/n4')))
            $this->markTestSkipped('Folder public/audio/jlpt/n4 belum ada di lingkungan ini.');

        $bank = $this->bank('chokai');
        $tracks = array_values($bank['audio']);
        foreach ($bank['mondai'] as $m)
            array_push($tracks, $m['audio'], $m['example']['audio'], ...(isset($m['audio_before']) ? [$m['audio_before']] : []));
        foreach ($bank['questions'] as $q)
            $tracks[] = $q['audio'];

        $service = app(\App\Services\JlptTestService::class);
        $missing = array_values(array_filter($tracks, fn (int $n) => $service->resolveAudio('private-n4', $n) === null));
        sort($missing);

        $this->assertSame([], $missing, 'Nomor trek audio tanpa berkas di public/audio/jlpt/n4 (berawalan "NN-"): '.implode(', ', array_map(fn ($n) => sprintf('%02d', $n), $missing)));
    }
}
