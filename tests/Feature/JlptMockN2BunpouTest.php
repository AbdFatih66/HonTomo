<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Paket orisinal HonTomo N2 (`n2-test-1`) — sesi bunpou_dokkai (言語知識〈文法〉・読解, 問題7–14). Menjaga bentuk bank
 * (12 + 5 + 5 + 5 + 9 + 2 + 3 + 2 = 43 soal seperti ujian N2 asli, 75 menit), kunci jawaban, kaitan soal ↔ bacaan,
 * kotak bernomor もんだい 9, soal ★ (kunci = kata ke-3 susunan benar), dan bahwa isi TIDAK menyalin bank asli N2
 * (`jlpt/n2/bunpou_dokkai.json`). Murni membaca berkas.
 */
class JlptMockN2BunpouTest extends TestCase
{
    private const PACK = 'n2-test-1';

    /** Kunci jawaban (1–4) yang ditulis ulang di sini agar salah ketik di bank ketahuan. */
    private const KEY = [
        'g7-33' => 2, 'g7-34' => 4, 'g7-35' => 1, 'g7-36' => 3, 'g7-37' => 4, 'g7-38' => 1, 'g7-39' => 3, 'g7-40' => 2, 'g7-41' => 4, 'g7-42' => 3, 'g7-43' => 1, 'g7-44' => 2, 'g8-45' => 3, 'g8-46' => 1, 'g8-47' => 4, 'g8-48' => 2, 'g8-49' => 3, 'g9-50' => 1, 'g9-51' => 3, 'g9-52' => 2, 'g9-53' => 4, 'g9-54' => 1, 'g10-55' => 2, 'g10-56' => 4, 'g10-57' => 3, 'g10-58' => 1, 'g10-59' => 2, 'g11-60' => 3, 'g11-61' => 1, 'g11-62' => 4, 'g11-63' => 2, 'g11-64' => 3, 'g11-65' => 1, 'g11-66' => 4, 'g11-67' => 2, 'g11-68' => 3, 'g12-69' => 2, 'g12-70' => 4, 'g13-71' => 3, 'g13-72' => 1, 'g13-73' => 4, 'g14-74' => 3, 'g14-75' => 1,
    ];

    private const SIZES = [7 => 12, 8 => 5, 9 => 5, 10 => 5, 11 => 9, 12 => 2, 13 => 3, 14 => 2];

    private const PASSAGES = ['g9-1' => 'text', 'g10-1' => 'text', 'g10-2' => 'note', 'g10-3' => 'text', 'g10-4' => 'text', 'g10-5' => 'text', 'g11-1' => 'text', 'g11-2' => 'text', 'g11-3' => 'text', 'g12-1' => 'pair', 'g13-1' => 'text', 'g14-1' => 'plans'];

    private function bank(string $dir = 'packs/'.self::PACK): array
    {
        return json_decode(file_get_contents(resource_path('lang-data/jlpt/'.$dir.'/bunpou_dokkai.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    private function source(): array
    {
        return json_decode(file_get_contents(resource_path('lang-data/jlpt/sources/'.self::PACK.'.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_answer_key_matches(): void
    {
        $bank = $this->bank();

        $this->assertSame(self::KEY, collect($bank['questions'])->pluck('answer', 'id')->all());
        $this->assertSame(1, $bank['mondai']['8']['example']['answer'], 'contoh ★: kata ke-3 = pilihan 1');
    }

    public function test_bank_has_the_n2_bunpou_dokkai_layout(): void
    {
        $bank = $this->bank();

        $this->assertSame(['N2', 'bunpou_dokkai', 75], [$bank['level'], $bank['section'], $bank['minutes']]);
        $this->assertSame(config('jlpt.levels.N2.sections.bunpou_dokkai.minutes'), $bank['minutes']);
        $this->assertCount(config('jlpt.levels.N2.sections.bunpou_dokkai.questions'), $bank['questions']);
        $this->assertSame(['7', '8', '9', '10', '11', '12', '13', '14'], array_keys($bank['mondai']));

        $perMondai = array_count_values(array_column($bank['questions'], 'mondai'));
        ksort($perMondai);
        $this->assertSame(self::SIZES, $perMondai);

        $ids = array_column($bank['questions'], 'id');
        $this->assertSame($ids, array_values(array_unique($ids)), 'id soal harus unik');
        $this->assertSame(range(33, 75), array_column($bank['questions'], 'no'), 'nomor tampil 33–75 seperti 正答表 N2');

        foreach ($bank['questions'] as $q) {
            $this->assertCount(4, $q['choices'], $q['id']);
            $this->assertSame(4, count(array_unique($q['choices'])), "{$q['id']} pilihan kembar");
            $this->assertGreaterThanOrEqual(1, $q['answer'], $q['id']);
            $this->assertLessThanOrEqual(4, $q['answer'], $q['id']);
            $this->assertNotEmpty($q['exp'], "{$q['id']} pembahasan");
            $this->assertSame("g{$q['mondai']}-{$q['no']}", $q['id']);
            $this->assertSame(substr_count($q['stem'], '⟦'), substr_count($q['stem'], '⟧'), "{$q['id']}: ⟦⟧ tidak seimbang");

            // もんだい 7–8 berdiri sendiri; 9–14 selalu punya bacaan
            if ($q['mondai'] >= 9)
                $this->assertArrayHasKey($q['passage'], $bank['passages'], $q['id']);
            else
                $this->assertArrayNotHasKey('passage', $q, $q['id']);

            if ($q['mondai'] === 7)
                $this->assertSame(1, substr_count($q['stem'], '（　　）'), $q['id']);
        }

        foreach ($bank['mondai'] as $no => $m) {
            $this->assertNotEmpty($m['instruction'], "petunjuk {$no}");
            if ($no !== '8')
                $this->assertArrayNotHasKey('example', $m, "もんだい {$no}");
        }

        // もんだい 8: contoh + cara menjawab; もんだい 9: kalimat pengantar
        $this->assertNotEmpty($bank['mondai']['8']['example']['howto']['figure']['order']);
        $this->assertNotEmpty($bank['mondai']['9']['lead']);
    }

    public function test_passages_are_wired_to_their_questions(): void
    {
        $bank = $this->bank();

        $this->assertSame(self::PASSAGES, collect($bank['passages'])->map(fn ($p) => $p['kind'])->all());

        $used = collect($bank['questions'])->pluck('passage')->filter()->unique()->values()->all();
        $this->assertEqualsCanonicalizing(array_keys($bank['passages']), $used, 'tiap bacaan dipakai, tiap soal menunjuk bacaan yang ada');

        // もんだい 9: kotak bernomor di bacaan = 50–54 = nomor soal もんだい 9
        preg_match_all('/\{\{(\d+)\}\}/', $bank['passages']['g9-1']['body'], $m);
        $this->assertSame(['50', '51', '52', '53', '54'], $m[1]);
        $this->assertSame(range(50, 54), array_column(array_filter($bank['questions'], fn ($q) => $q['mondai'] === 9), 'no'));
        $this->assertTrue($bank['passages']['g9-1']['boxed']);

        // もんだい 10: lima bacaan, satu soal tiap bacaan; (2) = email dengan kalimat pengantar
        $this->assertSame(['g10-1', 'g10-2', 'g10-3', 'g10-4', 'g10-5'], collect($bank['questions'])->where('mondai', 10)->pluck('passage')->all());
        $this->assertSame('mail', $bank['passages']['g10-2']['variant']);

        // もんだい 11: tiga bacaan × tiga soal; ① ② bergaris bawah ada di bacaan
        $this->assertSame(['g11-1' => 3, 'g11-2' => 3, 'g11-3' => 3], collect($bank['questions'])->where('mondai', 11)->countBy('passage')->all());
        foreach (['g11-1', 'g11-2', 'g11-3', 'g13-1'] as $key) {
            $this->assertMatchesRegularExpression('/⟦[^⟧]+⟧①/u', $bank['passages'][$key]['body'], $key);
            $this->assertMatchesRegularExpression('/⟦[^⟧]+⟧②/u', $bank['passages'][$key]['body'], $key);
        }

        // もんだい 12: pasangan A/B; もんだい 14: tabel A社 + diagram B社
        $this->assertSame(['A', 'B'], array_column($bank['passages']['g12-1']['items'], 'label'));
        $this->assertSame(['a', 'b'], array_keys($bank['passages']['g14-1']['data']));
        $this->assertCount(2, $bank['passages']['g14-1']['data']['a']['groups']);
        $this->assertCount(3, $bank['passages']['g14-1']['data']['b']['steps']);
    }

    public function test_star_questions_follow_the_source_order(): void
    {
        $src = collect($this->source()['sections'])->firstWhere('key', 'grammar');
        $this->assertNotNull($src);
        $this->assertSame(7, $src['mondai_start']);

        $bank = collect($this->bank()['questions'])->keyBy('id');
        $stars = 0;

        foreach ($src['mondai'] as $m)
            foreach ($m['groups'] as $g)
                foreach ($g['questions'] as $q) {
                    $this->assertSame($q['answer'] + 1, $bank[$q['id']]['answer'], "{$q['id']} kunci bank ≠ sumber");

                    if (($q['type'] ?? '') !== 'star')
                        continue;

                    $stars++;
                    $this->assertSame($q['order'][2], $q['answer'], "{$q['id']} ★ harus kata ke-3 susunan benar");
                    $this->assertSame([0, 1, 2, 3], collect($q['order'])->sort()->values()->all(), "{$q['id']} order = permutasi");
                    $this->assertNotSame([0, 1, 2, 3], $q['order'], "{$q['id']} urutan tampil tidak boleh sudah benar");
                    $this->assertStringContainsString('⟦★⟧', $bank[$q['id']]['stem']);
                }

        $this->assertSame(5, $stars);
    }

    public function test_content_is_original_not_copied_from_the_real_n2_bank(): void
    {
        $real = $this->bank('n2');
        $mine = $this->bank();

        $texts = fn ($b) => collect($b['questions'])->flatMap(fn ($q) => array_merge([$q['stem']], $q['choices']))->filter()->unique()->values();
        $this->assertSame([], $texts($mine)->intersect($texts($real))->values()->all(), 'stem/pilihan sama dengan soal asli');

        $bodies = fn ($b) => collect($b['passages'])->map(fn ($p) => $p['body'] ?? json_encode($p, JSON_UNESCAPED_UNICODE))->values();
        $this->assertSame([], $bodies($mine)->intersect($bodies($real))->values()->all(), 'bacaan sama dengan soal asli');
    }
}
