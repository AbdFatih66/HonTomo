<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Paket orisinal HonTomo N1 (`n1-test-1`) — sesi mojigoi (言語知識〈文字・語彙〉). Menjaga bentuk bank
 * (6 + 7 + 6 + 6 = 25 soal seperti ujian N1 asli, 25 menit), kunci jawaban, bahwa isi soal TIDAK menyalin
 * bank asli N1 (`jlpt/n1/mojigoi.json`), dan kata sasaran ditandai ⟦…⟧. Murni membaca berkas.
 */
class JlptMockN1MojigoiTest extends TestCase
{
    private const PACK = 'n1-test-1';

    private const KEY = [
        'v1-1' => 2, 'v1-2' => 4, 'v1-3' => 1, 'v1-4' => 3, 'v1-5' => 4, 'v1-6' => 2,
        'v2-7' => 3, 'v2-8' => 2, 'v2-9' => 4, 'v2-10' => 1, 'v2-11' => 2, 'v2-12' => 3, 'v2-13' => 4,
        'v3-14' => 2, 'v3-15' => 1, 'v3-16' => 4, 'v3-17' => 3, 'v3-18' => 1, 'v3-19' => 3,
        'v4-20' => 3, 'v4-21' => 1, 'v4-22' => 4, 'v4-23' => 2, 'v4-24' => 1, 'v4-25' => 3,
    ];

    private const SIZES = [1 => 6, 2 => 7, 3 => 6, 4 => 6];

    private function bank(string $dir = 'packs/'.self::PACK): array
    {
        return json_decode(file_get_contents(resource_path('lang-data/jlpt/'.$dir.'/mojigoi.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_answer_key_matches(): void
    {
        $this->assertSame(self::KEY, collect($this->bank()['questions'])->pluck('answer', 'id')->all());
    }

    public function test_bank_has_the_n1_mojigoi_layout(): void
    {
        $bank = $this->bank();

        $this->assertSame(['N1', 'mojigoi', 25], [$bank['level'], $bank['section'], $bank['minutes']]);
        $this->assertSame(['1', '2', '3', '4'], array_keys($bank['mondai']));
        $this->assertCount(25, $bank['questions']);

        $perMondai = array_count_values(array_column($bank['questions'], 'mondai'));
        ksort($perMondai);
        $this->assertSame(self::SIZES, $perMondai);
        $this->assertSame(range(1, 25), array_column($bank['questions'], 'no'));

        $ids = array_column($bank['questions'], 'id');
        $this->assertSame($ids, array_values(array_unique($ids)), 'id soal harus unik');

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

    public function test_target_words_are_marked(): void
    {
        foreach ($this->bank()['questions'] as $q) {
            if ($q['mondai'] === 2) {
                $this->assertStringContainsString('（　　）', $q['stem'], $q['id']);

                continue;
            }

            // 1 & 3: garis bawah di stem; 4: garis bawah di tiap pilihan (stem = kata sasaran polos)
            $targets = $q['mondai'] === 4 ? $q['choices'] : [$q['stem']];
            foreach ($targets as $text)
                $this->assertStringContainsString('⟦', $text, "{$q['id']} kata sasaran harus ⟦…⟧");
        }
    }

    public function test_content_is_original_not_copied_from_the_real_n1_bank(): void
    {
        $real = collect($this->bank('n1')['questions']);
        $mine = collect($this->bank()['questions']);

        $this->assertSame([], $mine->pluck('stem')->intersect($real->pluck('stem'))->values()->all(), 'stem sama dengan soal asli');

        $marked = fn ($qs) => $qs->flatMap(fn ($q) => preg_match_all('/⟦(.+?)⟧/u', $q['stem'].implode('', $q['choices']), $m) ? $m[1] : [])->unique();
        $this->assertSame([], $marked($mine)->intersect($marked($real))->values()->all(), 'kata sasaran sama dengan soal asli');
    }
}
