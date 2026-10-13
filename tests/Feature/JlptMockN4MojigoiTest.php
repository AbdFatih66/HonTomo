<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Paket orisinal HonTomo N4 (`n4-test-1`) — sesi mojigoi (言語知識〈文字・語彙〉). Menjaga bentuk bank
 * (9 + 6 + 9 + 5 + 5 = 34 soal seperti ujian N4 asli, 30 menit), kunci jawaban, bahwa isi soal TIDAK
 * menyalin bank asli N4 (`jlpt/n4/mojigoi.json`), serta bahwa kata sasaran ditandai ⟦…⟧. Murni membaca berkas.
 */
class JlptMockN4MojigoiTest extends TestCase
{
    private const PACK = 'n4-test-1';

    /** Kunci jawaban (1–4) yang ditulis ulang di sini agar salah ketik di bank ketahuan. */
    private const KEY = [
        'v1-1' => 2, 'v1-2' => 3, 'v1-3' => 4, 'v1-4' => 1, 'v1-5' => 3, 'v1-6' => 1, 'v1-7' => 4, 'v1-8' => 2, 'v1-9' => 4, 'v2-10' => 3, 'v2-11' => 4, 'v2-12' => 1, 'v2-13' => 2, 'v2-14' => 3, 'v2-15' => 2, 'v3-16' => 2, 'v3-17' => 3, 'v3-18' => 1, 'v3-19' => 2, 'v3-20' => 4, 'v3-21' => 2, 'v3-22' => 3, 'v3-23' => 4, 'v3-24' => 1, 'v4-25' => 3, 'v4-26' => 2, 'v4-27' => 1, 'v4-28' => 4, 'v4-29' => 2, 'v5-30' => 2, 'v5-31' => 4, 'v5-32' => 3, 'v5-33' => 1, 'v5-34' => 2,
    ];

    private const EXAMPLE_KEY = [1 => 1, 2 => 2, 3 => 1, 4 => 2, 5 => 1];

    private const SIZES = [1 => 9, 2 => 6, 3 => 9, 4 => 5, 5 => 5];

    private function bank(string $dir = 'packs/'.self::PACK): array
    {
        return json_decode(file_get_contents(resource_path('lang-data/jlpt/'.$dir.'/mojigoi.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_answer_key_matches(): void
    {
        $bank = $this->bank();

        $this->assertSame(self::KEY, collect($bank['questions'])->pluck('answer', 'id')->all());

        foreach (self::EXAMPLE_KEY as $mondai => $answer)
            $this->assertSame($answer, $bank['mondai'][(string) $mondai]['example']['answer'], "れい もんだい {$mondai}");
    }

    public function test_bank_has_the_n4_mojigoi_layout(): void
    {
        $bank = $this->bank();

        $this->assertSame(['N4', 'mojigoi', 30], [$bank['level'], $bank['section'], $bank['minutes']]);
        $this->assertSame(['1', '2', '3', '4', '5'], array_keys($bank['mondai']));
        $this->assertCount(34, $bank['questions']);

        $perMondai = array_count_values(array_column($bank['questions'], 'mondai'));
        ksort($perMondai);
        $this->assertSame(self::SIZES, $perMondai);

        $ids = array_column($bank['questions'], 'id');
        $this->assertSame($ids, array_values(array_unique($ids)), 'id soal harus unik');
        $this->assertSame(range(1, 34), array_column($bank['questions'], 'no'), 'nomor tampil berurutan 1–34');

        foreach ($bank['questions'] as $q) {
            $this->assertCount(4, $q['choices'], $q['id']);
            $this->assertSame(4, count(array_unique($q['choices'])), "{$q['id']} pilihan kembar");
            $this->assertGreaterThanOrEqual(1, $q['answer'], $q['id']);
            $this->assertLessThanOrEqual(4, $q['answer'], $q['id']);
            $this->assertNotEmpty($q['exp'], "{$q['id']} pembahasan");
            $this->assertSame("v{$q['mondai']}-{$q['no']}", $q['id']);
        }

        foreach ($bank['mondai'] as $no => $m) {
            $this->assertNotEmpty($m['instruction'], "petunjuk {$no}");
            $this->assertCount(4, $m['example']['choices'], "れい {$no}");
        }
    }

    public function test_target_words_are_marked(): void
    {
        foreach ($this->bank()['questions'] as $q) {
            if ($q['mondai'] === 3) {
                // もんだい 3: kolom kosong （　　）, tidak ada garis bawah
                $this->assertStringContainsString('（　　）', $q['stem'], $q['id']);

                continue;
            }

            // 1, 2, 4: garis bawah di stem; 5: garis bawah di tiap pilihan (stem = kata sasaran polos)
            $targets = $q['mondai'] === 5 ? $q['choices'] : [$q['stem']];
            foreach ($targets as $text)
                $this->assertStringContainsString('⟦', $text, "{$q['id']} kata sasaran harus ⟦…⟧");
        }
    }

    public function test_content_is_original_not_copied_from_the_real_n4_bank(): void
    {
        $real = collect($this->bank('n4')['questions']);
        $mine = collect($this->bank()['questions']);

        $this->assertSame([], $mine->pluck('stem')->intersect($real->pluck('stem'))->values()->all(), 'stem sama dengan soal asli');

        $marked = fn ($qs) => $qs->flatMap(fn ($q) => preg_match_all('/⟦(.+?)⟧/u', $q['stem'].implode('', $q['choices']), $m) ? $m[1] : [])->unique();
        $this->assertSame([], $marked($mine)->intersect($marked($real))->values()->all(), 'kata sasaran sama dengan soal asli');
    }
}
