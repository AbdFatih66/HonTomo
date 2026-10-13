<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Paket orisinal HonTomo N2 (`n2-test-1`) — sesi mojigoi (言語知識〈文字・語彙〉). Menjaga bentuk bank
 * (5 + 5 + 5 + 7 + 5 + 5 = 32 soal seperti ujian N2 asli, 30 menit), kunci jawaban, bahwa isi soal TIDAK
 * menyalin bank asli N2 (`jlpt/n2/mojigoi.json`), penandaan ⟦…⟧ per もんだい, dan bahwa pack tetap nonaktif
 * sampai ketiga sesi N2 ada. Murni membaca berkas.
 */
class JlptMockN2MojigoiTest extends TestCase
{
    private const PACK = 'n2-test-1';

    /** Kunci jawaban (1–4) yang ditulis ulang di sini agar salah ketik di bank ketahuan. */
    private const KEY = [
        'v1-1' => 3, 'v1-2' => 1, 'v1-3' => 4, 'v1-4' => 2, 'v1-5' => 3, 'v2-6' => 2, 'v2-7' => 4, 'v2-8' => 1, 'v2-9' => 3, 'v2-10' => 4, 'v3-11' => 1, 'v3-12' => 2, 'v3-13' => 4, 'v3-14' => 3, 'v3-15' => 1, 'v4-16' => 2, 'v4-17' => 4, 'v4-18' => 1, 'v4-19' => 3, 'v4-20' => 4, 'v4-21' => 2, 'v4-22' => 1, 'v5-23' => 3, 'v5-24' => 1, 'v5-25' => 4, 'v5-26' => 2, 'v5-27' => 3, 'v6-28' => 4, 'v6-29' => 2, 'v6-30' => 1, 'v6-31' => 3, 'v6-32' => 2,
    ];

    private const SIZES = [1 => 5, 2 => 5, 3 => 5, 4 => 7, 5 => 5, 6 => 5];

    private function bank(string $dir = 'packs/'.self::PACK): array
    {
        return json_decode(file_get_contents(resource_path('lang-data/jlpt/'.$dir.'/mojigoi.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_answer_key_matches(): void
    {
        $bank = $this->bank();

        $this->assertSame(self::KEY, collect($bank['questions'])->pluck('answer', 'id')->all());
    }

    public function test_bank_has_the_n2_mojigoi_layout(): void
    {
        $bank = $this->bank();

        $this->assertSame(['N2', 'mojigoi', 30], [$bank['level'], $bank['section'], $bank['minutes']]);
        $this->assertSame(config('jlpt.levels.N2.sections.mojigoi.minutes'), $bank['minutes']);
        $this->assertSame(['1', '2', '3', '4', '5', '6'], array_keys($bank['mondai']));
        $this->assertCount(config('jlpt.levels.N2.sections.mojigoi.questions'), $bank['questions']);

        $perMondai = array_count_values(array_column($bank['questions'], 'mondai'));
        ksort($perMondai);
        $this->assertSame(self::SIZES, $perMondai);

        $ids = array_column($bank['questions'], 'id');
        $this->assertSame($ids, array_values(array_unique($ids)), 'id soal harus unik');
        $this->assertSame(range(1, 32), array_column($bank['questions'], 'no'), 'nomor tampil berurutan 1–32');

        foreach ($bank['questions'] as $q) {
            $this->assertCount(4, $q['choices'], $q['id']);
            $this->assertSame(4, count(array_unique($q['choices'])), "{$q['id']} pilihan kembar");
            $this->assertGreaterThanOrEqual(1, $q['answer'], $q['id']);
            $this->assertLessThanOrEqual(4, $q['answer'], $q['id']);
            $this->assertNotEmpty($q['exp'], "{$q['id']} pembahasan");
            $this->assertSame("v{$q['mondai']}-{$q['no']}", $q['id']);
        }

        foreach ($bank['mondai'] as $no => $m)
            $this->assertNotEmpty($m['instruction'], "petunjuk {$no}");
    }

    public function test_marking_follows_each_mondai_format(): void
    {
        foreach ($this->bank()['questions'] as $q) {
            $id = $q['id'];

            // 1, 2, 5: satu kata sasaran digarisbawahi di stem
            if (in_array($q['mondai'], [1, 2, 5], true))
                $this->assertSame(1, substr_count($q['stem'], '⟦'), $id);

            // 3, 4: satu kolom kosong （　　）, tanpa garis bawah
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

    public function test_mondai_3_choices_are_single_kanji(): void
    {
        foreach ($this->bank()['questions'] as $q)
            if ($q['mondai'] === 3)
                foreach ($q['choices'] as $c)
                    $this->assertSame(1, mb_strlen($c), "{$q['id']}: pilihan awalan/akhiran harus satu kanji");
    }

    public function test_content_is_original_not_copied_from_the_real_n2_bank(): void
    {
        $real = collect($this->bank('n2')['questions']);
        $mine = collect($this->bank()['questions']);

        $this->assertSame([], $mine->pluck('stem')->intersect($real->pluck('stem'))->values()->all(), 'stem sama dengan soal asli');

        $marked = fn ($qs) => $qs->flatMap(fn ($q) => preg_match_all('/⟦(.+?)⟧/u', $q['stem'].implode('', $q['choices']), $m) ? $m[1] : [])->unique();
        $this->assertSame([], $marked($mine)->intersect($marked($real))->values()->all(), 'kata sasaran sama dengan soal asli');
    }

    public function test_pack_stays_disabled_until_every_n2_section_exists(): void
    {
        $pack = config('jlpt.packs.'.self::PACK);

        $this->assertSame(['N2', 'classic'], [$pack['level'], $pack['format']]);

        if (! $pack['enabled'])
            $this->assertFileExists(resource_path('lang-data/'.$pack['data_dir'].'/mojigoi.json'));
        else {
            // level N2 mewajibkan semua sesi aktif: pack aktif harus punya semua bank-nya
            foreach (config('jlpt.levels.N2.sections') as $key => $s)
                $this->assertFileExists(resource_path('lang-data/'.$pack['data_dir'].'/'.$s['file']), "pack aktif tapi sesi {$key} belum ada");
        }
    }
}
