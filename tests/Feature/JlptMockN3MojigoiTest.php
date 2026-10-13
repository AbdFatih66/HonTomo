<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Paket orisinal HonTomo N3 (`n3-test-1`) — sesi mojigoi (言語知識〈文字・語彙〉). Menjaga bentuk bank
 * (8 + 6 + 9 + 5 + 5 = 33 soal seperti ujian N3 asli, 30 menit), kunci jawaban, kelengkapan furigana,
 * bahwa isi soal TIDAK menyalin bank asli N3 (`jlpt/n3/mojigoi.json`), serta bahwa pack tetap nonaktif
 * selama sesi lain belum ada. Murni membaca berkas.
 */
class JlptMockN3MojigoiTest extends TestCase
{
    private const PACK = 'n3-test-1';

    /** Kunci jawaban (1–4) yang ditulis ulang di sini agar salah ketik di bank ketahuan. */
    private const KEY = [
        'v1-1' => 2, 'v1-2' => 1, 'v1-3' => 4, 'v1-4' => 3, 'v1-5' => 2, 'v1-6' => 3, 'v1-7' => 4, 'v1-8' => 1,
        'v2-9' => 3, 'v2-10' => 1, 'v2-11' => 4, 'v2-12' => 2, 'v2-13' => 3, 'v2-14' => 1,
        'v3-15' => 3, 'v3-16' => 1, 'v3-17' => 2, 'v3-18' => 4, 'v3-19' => 2, 'v3-20' => 3, 'v3-21' => 1, 'v3-22' => 4, 'v3-23' => 2,
        'v4-24' => 3, 'v4-25' => 2, 'v4-26' => 1, 'v4-27' => 4, 'v4-28' => 3,
        'v5-29' => 4, 'v5-30' => 3, 'v5-31' => 2, 'v5-32' => 1, 'v5-33' => 3,
    ];

    private const SIZES = [1 => 8, 2 => 6, 3 => 9, 4 => 5, 5 => 5];

    private function bank(string $dir = 'packs/'.self::PACK): array
    {
        return json_decode(file_get_contents(resource_path('lang-data/jlpt/'.$dir.'/mojigoi.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    /** Kelas karakter kanji. (`\p{Han}` tidak dipakai: di PCRE2 baru ia ikut mencocokkan 「、」「。」.) */
    private const KANJI = '\x{3400}-\x{9FFF}々';

    /** Teks tanpa furigana 《…》 dan tanpa penanda garis bawah ⟦…⟧ (isinya tetap ikut diperiksa). */
    private function plain(string $s): string
    {
        return str_replace(['⟦', '⟧'], '', preg_replace('/['.self::KANJI.']+《[^》]*》/u', '', $s));
    }

    public function test_answer_key_matches(): void
    {
        $this->assertSame(self::KEY, collect($this->bank()['questions'])->pluck('answer', 'id')->all());
    }

    public function test_bank_has_the_n3_mojigoi_layout(): void
    {
        $bank = $this->bank();

        $this->assertSame(['N3', 'mojigoi', 30], [$bank['level'], $bank['section'], $bank['minutes']]);
        $this->assertSame([1, 2, 3, 4, 5], array_map('intval', array_keys($bank['mondai'])));
        $this->assertCount(config('jlpt.levels.N3.sections.mojigoi.questions'), $bank['questions']);
        $this->assertCount(33, $bank['questions']);

        $perMondai = array_count_values(array_column($bank['questions'], 'mondai'));
        ksort($perMondai);
        $this->assertSame(self::SIZES, $perMondai);

        $ids = array_column($bank['questions'], 'id');
        $this->assertSame($ids, array_values(array_unique($ids)), 'id soal harus unik');
        $this->assertSame(range(1, 33), array_column($bank['questions'], 'no'), 'nomor tampil berurutan 1–33');

        foreach ($bank['questions'] as $q) {
            $this->assertSame("v{$q['mondai']}-{$q['no']}", $q['id']);
            $this->assertCount(4, $q['choices'], $q['id']);
            $this->assertSame(4, count(array_unique($q['choices'])), "{$q['id']} pilihan kembar");
            $this->assertGreaterThanOrEqual(1, $q['answer'], $q['id']);
            $this->assertLessThanOrEqual(4, $q['answer'], $q['id']);
            $this->assertNotEmpty($q['exp'], "{$q['id']} pembahasan");
        }

        foreach ($bank['mondai'] as $no => $m)
            $this->assertNotEmpty($m['instruction'], "petunjuk {$no}");
    }

    public function test_target_words_are_marked(): void
    {
        foreach ($this->bank()['questions'] as $q) {
            if ($q['mondai'] === 3) {
                $this->assertStringContainsString('（　　）', $q['stem'], $q['id']);

                continue;
            }

            // 1, 2, 4: garis bawah di stem; 5: garis bawah di tiap pilihan (stem = kata sasaran polos)
            $targets = $q['mondai'] === 5 ? $q['choices'] : [$q['stem']];
            foreach ($targets as $text)
                $this->assertStringContainsString('⟦', $text, "{$q['id']} kata sasaran harus ⟦…⟧");
        }
    }

    public function test_every_kanji_has_furigana_except_the_tested_ones(): void
    {
        foreach ($this->bank()['questions'] as $q) {
            // もんだい 1: kata yang ditanyakan cara bacanya ditulis tanpa furigana
            $stem = $q['mondai'] === 1 ? preg_replace('/⟦.*?⟧/u', '', $q['stem']) : $q['stem'];
            $this->assertDoesNotMatchRegularExpression('/['.self::KANJI.']/u', $this->plain($stem), "{$q['id']}: kanji tanpa furigana di stem");

            // もんだい 2: pilihan memang kanji tanpa furigana
            if ($q['mondai'] !== 2) {
                foreach ($q['choices'] as $c)
                    $this->assertDoesNotMatchRegularExpression('/['.self::KANJI.']/u', $this->plain($c), "{$q['id']}: kanji tanpa furigana di pilihan");
            }
        }
    }

    public function test_content_is_original_not_copied_from_the_real_n3_bank(): void
    {
        $real = collect($this->bank('n3')['questions']);
        $mine = collect($this->bank()['questions']);

        $this->assertSame([], $mine->pluck('stem')->intersect($real->pluck('stem'))->values()->all(), 'stem sama dengan soal asli');

        $marked = fn ($qs) => $qs->flatMap(fn ($q) => preg_match_all('/⟦(.+?)⟧/u', $q['stem'].implode('', $q['choices']), $m) ? $m[1] : [])
            ->map(fn ($t) => preg_replace('/《[^》]*》/u', '', $t))->unique();
        $this->assertSame([], $marked($mine)->intersect($marked($real))->values()->all(), 'kata sasaran sama dengan soal asli');
    }

    public function test_source_and_bank_agree(): void
    {
        $src = json_decode(file_get_contents(resource_path('lang-data/jlpt/sources/'.self::PACK.'.json')), true, 512, JSON_THROW_ON_ERROR);
        $vocab = collect($src['sections'])->firstWhere('key', 'vocab');
        $bank = collect($this->bank()['questions'])->keyBy('id');
        $seen = 0;

        foreach ($vocab['mondai'] as $m)
            foreach ($m['groups'] as $g)
                foreach ($g['questions'] as $q) {
                    $seen++;
                    $this->assertSame($bank[$q['id']]['answer'], $q['answer'] + 1, "{$q['id']} kunci sumber vs bank");
                }

        $this->assertSame(33, $seen);
        $this->assertSame(['N3', 95], [$src['level'], $src['pass']['total']]);
    }

    public function test_pack_stays_disabled_until_every_n3_section_exists(): void
    {
        $pack = config('jlpt.packs.'.self::PACK);
        $dir = resource_path('lang-data/'.$pack['data_dir']);
        $missing = [];

        foreach (config('jlpt.levels.N3.sections') as $key => $s) {
            if ($s['enabled'] && ! is_file($dir.'/'.$s['file']))
                $missing[] = $key;
        }

        // Level N3 mewajibkan ketiga sesi aktif: pack hanya boleh aktif bila semua bank-nya ada.
        if ($pack['enabled'])
            $this->assertSame([], $missing, 'Pack aktif tetapi bank sesi belum lengkap');
        else
            $this->assertNotSame([], $missing, 'Semua bank sudah ada — pack boleh diaktifkan (enabled => true).');
    }
}
