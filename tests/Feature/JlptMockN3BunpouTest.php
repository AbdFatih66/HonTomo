<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Paket orisinal HonTomo N3 (`n3-test-1`) — sesi bunpou_dokkai (言語知識〈文法〉・読解). Menjaga bentuk bank
 * (13 + 5 + 5 + 4 + 6 + 4 + 2 = 39 soal seperti ujian N3 asli, 70 menit), kunci jawaban, furigana, bahwa isi
 * soal TIDAK menyalin bank asli N3 (`jlpt/n3/bunpou_dokkai.json`), bahwa sumber & bank sejalan, dan bahwa
 * pack tetap nonaktif selama sesi lain belum ada. Murni membaca berkas.
 */
class JlptMockN3BunpouTest extends TestCase
{
    private const PACK = 'n3-test-1';

    /** Kunci jawaban (1–4) yang ditulis ulang di sini agar salah ketik di bank ketahuan. */
    private const KEY = [
        'g1-1' => 3, 'g1-2' => 1, 'g1-3' => 4, 'g1-4' => 2, 'g1-5' => 3, 'g1-6' => 1, 'g1-7' => 2, 'g1-8' => 4, 'g1-9' => 2, 'g1-10' => 3, 'g1-11' => 1, 'g1-12' => 4, 'g1-13' => 3,
        'g2-14' => 1, 'g2-15' => 2, 'g2-16' => 4, 'g2-17' => 2, 'g2-18' => 3,
        'g3-19' => 1, 'g3-20' => 3, 'g3-21' => 2, 'g3-22' => 4, 'g3-23' => 3,
        'g4-24' => 2, 'g4-25' => 1, 'g4-26' => 3, 'g4-27' => 4,
        'g5-28' => 2, 'g5-29' => 3, 'g5-30' => 4, 'g5-31' => 3, 'g5-32' => 2, 'g5-33' => 1,
        'g6-34' => 2, 'g6-35' => 4, 'g6-36' => 1, 'g6-37' => 3,
        'g7-38' => 3, 'g7-39' => 4,
    ];

    private const EXAMPLE_KEY = [2 => 3];

    private const SIZES = [1 => 13, 2 => 5, 3 => 5, 4 => 4, 5 => 6, 6 => 4, 7 => 2];

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

    public function test_bank_has_the_n3_bunpou_dokkai_layout(): void
    {
        $bank = $this->bank();

        $this->assertSame(['N3', 'bunpou_dokkai', 70], [$bank['level'], $bank['section'], $bank['minutes']]);
        $this->assertSame(config('jlpt.levels.N3.sections.bunpou_dokkai.minutes'), $bank['minutes']);
        $this->assertSame(range(1, 7), array_map('intval', array_keys($bank['mondai'])));
        $this->assertCount(config('jlpt.levels.N3.sections.bunpou_dokkai.questions'), $bank['questions']);

        $perMondai = array_count_values(array_column($bank['questions'], 'mondai'));
        ksort($perMondai);
        $this->assertSame(self::SIZES, $perMondai);

        $ids = array_column($bank['questions'], 'id');
        $this->assertSame($ids, array_values(array_unique($ids)), 'id soal harus unik');
        $this->assertSame(range(1, 39), array_column($bank['questions'], 'no'), 'nomor tampil berurutan 1–39');

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

        // れい hanya untuk もんだい 2 (★); もんだい 3 punya kalimat pengantar
        $this->assertSame([2], array_map('intval', array_keys(array_filter($bank['mondai'], fn ($m) => isset($m['example'])))));
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
            ['g3-1' => 'text', 'g4-1' => 'note', 'g4-2' => 'note', 'g4-3' => 'text', 'g4-4' => 'text', 'g5-1' => 'text', 'g5-2' => 'text', 'g6-1' => 'text', 'g7-1' => 'brochure'],
            array_map(fn ($p) => $p['kind'], $bank['passages']),
        );

        foreach ($bank['questions'] as $q) {
            if ($q['mondai'] === 1 || $q['mondai'] === 2)
                $this->assertArrayNotHasKey('passage', $q, $q['id']);
            else
                $this->assertArrayHasKey($q['passage'], $bank['passages'], "{$q['id']} bacaan");
        }

        // もんだい 3: lima kotak bernomor 19–23 di bacaan, satu soal tiap kotak (soal tanpa stem); judul + penulis
        $g3 = $bank['passages']['g3-1'];
        foreach (range(19, 23) as $n)
            $this->assertSame(1, substr_count($g3['body'], '{{'.$n.'}}'), "kotak {$n}");
        foreach (array_filter($bank['questions'], fn ($q) => $q['mondai'] === 3) as $q)
            $this->assertSame('', $q['stem'], $q['id']);
        $this->assertTrue($g3['boxed']);
        $this->assertNotEmpty($g3['title']);
        $this->assertNotEmpty($g3['author']);

        // もんだい 4: empat bacaan, satu soal tiap bacaan; (2) = email ber-header (3 baris), tanpa baris "to"
        $mail = $bank['passages']['g4-2'];
        $this->assertSame('mail', $mail['variant']);
        $this->assertCount(3, $mail['headers']);
        $this->assertArrayNotHasKey('to', $mail);

        // もんだい 5: dua bacaan × 3 soal; もんだい 6: satu bacaan × 4 soal; もんだい 7: satu brosur × 2 soal
        $perPassage = array_count_values(array_column(array_filter($bank['questions'], fn ($q) => isset($q['passage'])), 'passage'));
        $this->assertSame(['g3-1' => 5, 'g4-1' => 1, 'g4-2' => 1, 'g4-3' => 1, 'g4-4' => 1, 'g5-1' => 3, 'g5-2' => 3, 'g6-1' => 4, 'g7-1' => 2], $perPassage);

        // soal garis-bawah: ①/② di bacaan dan di soal sejalan (もんだい 5 (1) dan もんだい 6)
        $this->assertStringContainsString('⟧①', $bank['passages']['g5-1']['body']);
        $this->assertStringContainsString('⟧②', $bank['passages']['g5-1']['body']);
        $stems = collect($bank['questions'])->whereIn('mondai', [5, 6])->pluck('stem')->implode('|');
        foreach (['⟦', '①⟦', '②⟦', '③⟦'] as $needle)
            $this->assertStringContainsString($needle, $stems);
        foreach (['⟧①', '⟧②', '⟧③'] as $needle)
            $this->assertStringContainsString($needle, $bank['passages']['g6-1']['body']);

        // もんだい 7: brosur 6 kursus dalam 2 bagian, 3 butir catatan
        $data = $bank['passages']['g7-1']['data'];
        $this->assertCount(3, $data['headers']);
        $this->assertSame([3, 3], array_map(fn ($s) => count($s['rows']), $data['sections']));
        $this->assertCount(3, $data['bullets']);
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

    public function test_content_is_original_not_copied_from_the_real_n3_bank(): void
    {
        $real = $this->bank('n3');
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
        $this->assertSame([self::PACK, 'N3'], [$src['id'], $src['level']]);

        $section = collect($src['sections'])->firstWhere('key', 'grammar');
        $this->assertNotNull($section, 'sumber harus punya sesi grammar');
        $this->assertSame(70, $section['minutes']);

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

        $this->assertSame(39, $seen);
    }

    public function test_pack_stays_disabled_until_every_n3_section_exists(): void
    {
        $pack = config('jlpt.packs.'.self::PACK);

        $this->assertSame(['N3', 'classic'], [$pack['level'], $pack['format']]);

        // level N3 mewajibkan semua sesi aktif: pack aktif harus punya semua bank-nya
        if ($pack['enabled'])
            foreach (config('jlpt.levels.N3.sections') as $key => $s)
                $this->assertFileExists(resource_path('lang-data/'.$pack['data_dir'].'/'.$s['file']), "pack aktif tapi sesi {$key} belum ada");
        else
            $this->assertFileExists(resource_path('lang-data/'.$pack['data_dir'].'/bunpou_dokkai.json'));
    }
}
