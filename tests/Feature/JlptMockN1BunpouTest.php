<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Paket orisinal HonTomo N1 (`n1-test-1`) — sesi bunpou_dokkai (言語知識〈文法〉・読解). Menjaga bentuk bank
 * (44 soal, nomor 26–69, もんだい 5–13 = 10 + 5 + 5 + 3 + 9 + 4 + 2 + 4 + 2, sama dengan bank N1 asli), kunci jawaban,
 * bacaan/passage, garis-bawah ①②, dan bahwa isi soal TIDAK menyalin bank asli N1 (`jlpt/n1/bunpou_dokkai.json`).
 * Murni membaca berkas.
 */
class JlptMockN1BunpouTest extends TestCase
{
    private const PACK = 'n1-test-1';

    /** Kunci jawaban (1–4) ditulis ulang di sini agar salah ketik di bank ketahuan. */
    private const KEY = ['g5-26' => 2, 'g5-27' => 3, 'g5-28' => 1, 'g5-29' => 4, 'g5-30' => 2, 'g5-31' => 1, 'g5-32' => 3, 'g5-33' => 4, 'g5-34' => 2, 'g5-35' => 3, 'g6-36' => 3, 'g6-37' => 1, 'g6-38' => 4, 'g6-39' => 2, 'g6-40' => 3, 'g7-41' => 3, 'g7-42' => 1, 'g7-43' => 4, 'g7-44' => 2, 'g7-45' => 1, 'g8-46' => 2, 'g8-47' => 4, 'g8-48' => 1, 'g9-49' => 1, 'g9-50' => 3, 'g9-51' => 2, 'g9-52' => 4, 'g9-53' => 1, 'g9-54' => 4, 'g9-55' => 2, 'g9-56' => 4, 'g9-57' => 1, 'g10-58' => 4, 'g10-59' => 2, 'g10-60' => 1, 'g10-61' => 3, 'g11-62' => 4, 'g11-63' => 1, 'g12-64' => 2, 'g12-65' => 4, 'g12-66' => 3, 'g12-67' => 1, 'g13-68' => 2, 'g13-69' => 3];

    private const SIZES = [5 => 10, 6 => 5, 7 => 5, 8 => 3, 9 => 9, 10 => 4, 11 => 2, 12 => 4, 13 => 2];

    private function bank(string $dir = 'packs/'.self::PACK): array
    {
        return json_decode(file_get_contents(resource_path('lang-data/jlpt/'.$dir.'/bunpou_dokkai.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    /** Semua string (rekursif) di bawah $node, kecuali kunci "exp" (pembahasan Indonesia). */
    private function strings(array $node): array
    {
        $out = [];
        array_walk_recursive($node, function ($v, $k) use (&$out) {
            if (is_string($v) && $k !== 'exp')
                $out[] = $v;
        });

        return $out;
    }

    public function test_answer_key_matches(): void
    {
        $bank = $this->bank();

        $this->assertSame(self::KEY, collect($bank['questions'])->pluck('answer', 'id')->all());
        $this->assertSame(2, $bank['mondai']['6']['example']['answer'], 'もんだい 6 れい');
    }

    public function test_bank_has_the_n1_bunpou_dokkai_layout(): void
    {
        $bank = $this->bank();

        $this->assertSame(['N1', 'bunpou_dokkai', 85], [$bank['level'], $bank['section'], $bank['minutes']]);
        $this->assertSame(range(5, 13), array_map('intval', array_keys($bank['mondai'])));
        $this->assertCount(44, $bank['questions']);

        $perMondai = array_count_values(array_column($bank['questions'], 'mondai'));
        ksort($perMondai);
        $this->assertSame(self::SIZES, $perMondai);

        $ids = array_column($bank['questions'], 'id');
        $this->assertSame($ids, array_values(array_unique($ids)), 'id soal harus unik');
        $this->assertSame(range(26, 69), array_column($bank['questions'], 'no'), 'nomor tampil berurutan 26–69');

        foreach ($bank['questions'] as $q) {
            $this->assertCount(4, $q['choices'], $q['id']);
            $this->assertSame(4, count(array_unique($q['choices'])), "{$q['id']} pilihan kembar");
            $this->assertGreaterThanOrEqual(1, $q['answer'], $q['id']);
            $this->assertLessThanOrEqual(4, $q['answer'], $q['id']);
            $this->assertNotEmpty($q['exp'], "{$q['id']} pembahasan");
            $this->assertSame("g{$q['mondai']}-{$q['no']}", $q['id']);
        }

        foreach ($bank['mondai'] as $no => $m)
            $this->assertNotEmpty($m['instruction'], "petunjuk {$no}");

        // れい hanya untuk もんだい 6 (cara menjawab ★)
        $this->assertSame([6], array_map('intval', array_keys(array_filter($bank['mondai'], fn ($m) => isset($m['example'])))));
        $this->assertNotEmpty($bank['mondai']['6']['example']['howto']['figure']['order']);
    }

    public function test_star_questions_have_four_boxes_and_one_star(): void
    {
        $bank = $this->bank();

        foreach (array_filter($bank['questions'], fn ($q) => $q['mondai'] === 6) as $q) {
            $this->assertSame(1, substr_count($q['stem'], '⟦★⟧'), "{$q['id']} harus punya satu ★");
            $this->assertSame(3, substr_count($q['stem'], '⟦　⟧'), "{$q['id']} harus punya tiga kotak kosong");
        }

        $example = $bank['mondai']['6']['example'];
        $this->assertSame(1, substr_count($example['stem'], '⟦★⟧'));
        $this->assertSame(3, substr_count($example['stem'], '⟦　⟧'));
    }

    public function test_passages_are_wired_to_their_questions(): void
    {
        $bank = $this->bank();

        $this->assertSame(
            ['g7-1' => 'text', 'g8-1' => 'text', 'g8-2' => 'text', 'g8-3' => 'text', 'g9-1' => 'text', 'g9-2' => 'text', 'g9-3' => 'text',
                'g10-1' => 'text', 'g11-1' => 'pair', 'g12-1' => 'text', 'g13-1' => 'sheet'],
            array_map(fn ($p) => $p['kind'], $bank['passages']),
        );
        $this->assertTrue($bank['passages']['g8-2']['vertical'], 'g8-2 ditulis vertikal (縦書き)');

        foreach ($bank['questions'] as $q) {
            if ($q['mondai'] === 5 || $q['mondai'] === 6)
                $this->assertArrayNotHasKey('passage', $q, $q['id']);
            else
                $this->assertArrayHasKey($q['passage'], $bank['passages'], "{$q['id']} bacaan");
        }

        $perPassage = array_count_values(array_column(array_filter($bank['questions'], fn ($q) => isset($q['passage'])), 'passage'));
        $this->assertSame(
            ['g7-1' => 5, 'g8-1' => 1, 'g8-2' => 1, 'g8-3' => 1, 'g9-1' => 3, 'g9-2' => 3, 'g9-3' => 3, 'g10-1' => 4, 'g11-1' => 2, 'g12-1' => 4, 'g13-1' => 2],
            $perPassage,
        );

        // もんだい 7: tujuh kotak (43 dan 45 bersuffiks a/b), lima soal tanpa stem
        $body = $bank['passages']['g7-1']['body'];
        foreach (['41', '42', '43-a', '43-b', '44', '45-a', '45-b'] as $n)
            $this->assertSame(1, substr_count($body, '{{'.$n.'}}'), "kotak {$n}");
        foreach (array_filter($bank['questions'], fn ($q) => $q['mondai'] === 7) as $q)
            $this->assertSame('', $q['stem'], $q['id']);
        foreach ([43, 45] as $no)
            foreach ($bank['questions'][$no - 26]['choices'] as $c)
                $this->assertMatchesRegularExpression('/^a .+　／　b .+$/u', $c, "pilihan {$no}");

        // garis-bawah ①/②: tanda di bacaan dan di soal sejalan
        $marks = [
            'g9-1' => [[50, '①', 'こうした微妙な違い']],
            'g9-2' => [[53, '①', 'そういう読み方']],
            'g9-3' => [[56, '①', 'その言葉']],
            'g10-1' => [[58, '①', 'それ自体は便利なことで、咎めるべきことではない'], [60, '②', 'その間に起こるはずの思考']],
            'g12-1' => [[64, '①', '絶え間ない音は、人から考える余裕を奪う'], [66, '②', '放送を減らせば、事故が増えるのではないか']],
        ];
        foreach ($marks as $pkey => $list) {
            foreach ($list as [$no, $circle, $text]) {
                $this->assertStringContainsString('⟦'.$text.'⟧'.$circle, $bank['passages'][$pkey]['body'], "bacaan {$pkey}");
                $this->assertStringContainsString($circle.'⟦'.$text.'⟧', $bank['questions'][$no - 26]['stem'], "soal {$no}");
            }
        }

        // もんだい 11: dua pendapat A dan B; もんだい 13: lembar pengumuman
        $this->assertSame(['A', 'B'], array_column($bank['passages']['g11-1']['items'], 'label'));
        $this->assertNotEmpty($bank['passages']['g13-1']['data']['rows']);
    }

    public function test_content_is_original_not_copied_from_the_real_n1_bank(): void
    {
        $real = $this->bank('n1');
        $mine = $this->bank();

        $realQ = collect($real['questions']);
        $mineQ = collect($mine['questions']);

        $this->assertSame([], $mineQ->pluck('stem')->filter()->intersect($realQ->pluck('stem'))->values()->all(), 'stem sama dengan soal asli');

        $sets = fn ($qs) => $qs->map(fn ($q) => collect($q['choices'])->sort()->values()->all())->map(fn ($c) => json_encode($c, JSON_UNESCAPED_UNICODE));
        $this->assertSame([], $sets($mineQ)->intersect($sets($realQ))->values()->all(), 'himpunan pilihan sama dengan soal asli');

        $texts = fn ($b) => collect($this->strings($b['passages']))->filter(fn ($s) => mb_strlen($s) >= 10);
        $this->assertSame([], $texts($mine)->intersect($texts($real))->values()->all(), 'teks bacaan sama dengan soal asli');
    }
}
