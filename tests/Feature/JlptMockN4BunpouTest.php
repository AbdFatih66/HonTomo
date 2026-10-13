<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Paket orisinal HonTomo N4 (`n4-test-1`) — sesi bunpou_dokkai (言語知識〈文法〉・読解). Menjaga bentuk bank
 * (15 + 5 + 5 + 4 + 4 + 2 = 35 soal seperti ujian N4 asli, 60 menit), kunci jawaban, furigana, bahwa isi
 * soal TIDAK menyalin bank asli N4 (`jlpt/n4/bunpou_dokkai.json`), dan bahwa sumber & bank sejalan.
 * Murni membaca berkas.
 */
class JlptMockN4BunpouTest extends TestCase
{
    private const PACK = 'n4-test-1';

    /** Kunci jawaban (1–4) yang ditulis ulang di sini agar salah ketik di bank ketahuan. */
    private const KEY = [
        'g1-1' => 1, 'g1-2' => 2, 'g1-3' => 3, 'g1-4' => 4, 'g1-5' => 3, 'g1-6' => 2, 'g1-7' => 1, 'g1-8' => 3, 'g1-9' => 4, 'g1-10' => 2, 'g1-11' => 1, 'g1-12' => 4, 'g1-13' => 2, 'g1-14' => 3, 'g1-15' => 1,
        'g2-16' => 3, 'g2-17' => 1, 'g2-18' => 4, 'g2-19' => 2, 'g2-20' => 1,
        'g3-21' => 1, 'g3-22' => 3, 'g3-23' => 4, 'g3-24' => 2, 'g3-25' => 3,
        'g4-26' => 3, 'g4-27' => 2, 'g4-28' => 1, 'g4-29' => 4,
        'g5-30' => 2, 'g5-31' => 3, 'g5-32' => 1, 'g5-33' => 4,
        'g6-34' => 2, 'g6-35' => 3,
    ];

    private const EXAMPLE_KEY = [1 => 3, 2 => 4];

    private const SIZES = [1 => 15, 2 => 5, 3 => 5, 4 => 4, 5 => 4, 6 => 2];

    private function bank(string $dir = 'packs/'.self::PACK): array
    {
        return json_decode(file_get_contents(resource_path('lang-data/jlpt/'.$dir.'/bunpou_dokkai.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    private function source(): array
    {
        return json_decode(file_get_contents(resource_path('lang-data/jlpt/sources/'.self::PACK.'.json')), true, 512, JSON_THROW_ON_ERROR);
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

        foreach (self::EXAMPLE_KEY as $mondai => $answer)
            $this->assertSame($answer, $bank['mondai'][(string) $mondai]['example']['answer'], "れい もんだい {$mondai}");
    }

    public function test_bank_has_the_n4_bunpou_dokkai_layout(): void
    {
        $bank = $this->bank();

        $this->assertSame(['N4', 'bunpou_dokkai', 60], [$bank['level'], $bank['section'], $bank['minutes']]);
        // PHP mengubah kunci JSON numerik ("1") menjadi int, jadi bandingkan sebagai int
        $this->assertSame(range(1, 6), array_map('intval', array_keys($bank['mondai'])));
        $this->assertCount(35, $bank['questions']);

        $perMondai = array_count_values(array_column($bank['questions'], 'mondai'));
        ksort($perMondai);
        $this->assertSame(self::SIZES, $perMondai);

        $ids = array_column($bank['questions'], 'id');
        $this->assertSame($ids, array_values(array_unique($ids)), 'id soal harus unik');
        $this->assertSame(range(1, 35), array_column($bank['questions'], 'no'), 'nomor tampil berurutan 1–35');

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

        // れい hanya untuk もんだい 1 dan 2; もんだい 3 punya kalimat pengantar
        $this->assertSame([1, 2], array_map('intval', array_keys(array_filter($bank['mondai'], fn ($m) => isset($m['example'])))));
        $this->assertNotEmpty($bank['mondai']['3']['lead']);
        $this->assertNotEmpty($bank['mondai']['2']['example']['howto']['figure']['order']);
    }

    public function test_star_questions_have_four_boxes_and_one_star(): void
    {
        $bank = $this->bank();

        foreach (array_filter($bank['questions'], fn ($q) => $q['mondai'] === 2) as $q) {
            $this->assertSame(1, substr_count($q['stem'], '⟦★⟧'), "{$q['id']} harus punya satu ★");
            $this->assertSame(3, substr_count($q['stem'], '⟦　⟧'), "{$q['id']} harus punya tiga kotak kosong");
        }

        $example = $bank['mondai']['2']['example'];
        $this->assertSame(1, substr_count($example['stem'], '⟦★⟧'));
        $this->assertSame(3, substr_count($example['stem'], '⟦　⟧'));
    }

    public function test_passages_are_wired_to_their_questions(): void
    {
        $bank = $this->bank();

        $this->assertSame(
            ['g3-1' => 'text', 'g4-1' => 'note', 'g4-2' => 'notice', 'g4-3' => 'note', 'g4-4' => 'text', 'g5-1' => 'text', 'g6-1' => 'poster'],
            array_map(fn ($p) => $p['kind'], $bank['passages']),
        );

        foreach ($bank['questions'] as $q) {
            if ($q['mondai'] === 1 || $q['mondai'] === 2)
                $this->assertArrayNotHasKey('passage', $q, $q['id']);
            else
                $this->assertArrayHasKey($q['passage'], $bank['passages'], "{$q['id']} bacaan");
        }

        // もんだい 3: lima kotak bernomor 21–25 di bacaan, satu soal tiap kotak (soal tanpa stem)
        $body = $bank['passages']['g3-1']['body'];
        foreach (range(21, 25) as $n)
            $this->assertSame(1, substr_count($body, '{{'.$n.'}}'), "kotak {$n}");
        foreach (array_filter($bank['questions'], fn ($q) => $q['mondai'] === 3) as $q)
            $this->assertSame('', $q['stem'], $q['id']);

        // もんだい 4: empat bacaan, masing-masing satu soal; もんだい 5: satu bacaan, empat soal; もんだい 6: satu poster, dua soal
        $perPassage = array_count_values(array_column(array_filter($bank['questions'], fn ($q) => isset($q['passage'])), 'passage'));
        $this->assertSame(['g3-1' => 5, 'g4-1' => 1, 'g4-2' => 1, 'g4-3' => 1, 'g4-4' => 1, 'g5-1' => 4, 'g6-1' => 2], $perPassage);

        // soal garis-bawah もんだい 5: ①/② di bacaan dan di soal sejalan
        $guitar = $bank['passages']['g5-1']['body'];
        $this->assertStringContainsString('⟦もう やめたい⟧①', $guitar);
        $this->assertStringContainsString('⟦これ⟧②', $guitar);
        $stems = collect($bank['questions'])->where('mondai', 5)->pluck('stem')->implode('|');
        $this->assertStringContainsString('⟦①もう やめたい⟧', $stems);
        $this->assertStringContainsString('⟦②これ⟧', $stems);
    }

    public function test_every_kanji_has_furigana(): void
    {
        $bank = $this->bank();
        unset($bank['title']); // judul sesi (言語知識…) memang tanpa furigana

        $bad = [];
        foreach ($this->strings($bank) as $s) {
            $plain = preg_replace('/\p{Han}+《[^》]*》/u', '', $s);
            if (preg_match('/\p{Han}+/u', $plain, $m))
                $bad[] = $m[0].' ← '.mb_substr($s, 0, 30);
        }

        $this->assertSame([], $bad, 'kanji tanpa furigana');
    }

    public function test_content_is_original_not_copied_from_the_real_n4_bank(): void
    {
        $real = $this->bank('n4');
        $mine = $this->bank();

        $realQ = collect($real['questions']);
        $mineQ = collect($mine['questions']);

        $this->assertSame([], $mineQ->pluck('stem')->filter()->intersect($realQ->pluck('stem'))->values()->all(), 'stem sama dengan soal asli');

        $sets = fn ($qs) => $qs->map(fn ($q) => collect($q['choices'])->sort()->values()->all())->map(fn ($c) => json_encode($c, JSON_UNESCAPED_UNICODE));
        $this->assertSame([], $sets($mineQ)->intersect($sets($realQ))->values()->all(), 'himpunan pilihan sama dengan soal asli');

        $texts = fn ($b) => collect($this->strings($b['passages']))->filter(fn ($s) => mb_strlen($s) >= 10);
        $this->assertSame([], $texts($mine)->intersect($texts($real))->values()->all(), 'teks bacaan sama dengan soal asli');
    }

    public function test_source_and_bank_agree(): void
    {
        $src = $this->source();
        $this->assertSame(self::PACK, $src['id']);

        $section = collect($src['sections'])->firstWhere('key', 'grammar');
        $this->assertNotNull($section, 'sumber harus punya sesi grammar');
        $this->assertSame(60, $section['minutes']);

        $bank = collect($this->bank()['questions'])->keyBy('id');
        $seen = 0;

        foreach ($section['mondai'] as $m) {
            foreach ($m['groups'] as $g) {
                foreach ($g['questions'] as $q) {
                    $seen++;
                    $this->assertSame($bank[$q['id']]['answer'], $q['answer'] + 1, "{$q['id']} kunci sumber vs bank");

                    if ($q['type'] === 'star') {
                        // ★ = kotak ketiga susunan benar; susunan memakai keempat kepingan tepat sekali
                        $this->assertSame($q['answer'], $q['order'][2], "{$q['id']} ★");
                        $this->assertSame([0, 1, 2, 3], collect($q['order'])->sort()->values()->all(), "{$q['id']} order");
                    }
                }
            }
        }

        $this->assertSame(35, $seen);
        $this->assertSame(['listening', 'vocab', 'grammar'], array_column($src['sections'], 'key'), 'chokai harus tetap sections[0]');
    }
}
