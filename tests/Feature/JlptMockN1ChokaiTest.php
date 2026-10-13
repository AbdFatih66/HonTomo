<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Paket orisinal HonTomo N1 (`n1-test-1`) — baru sesi chokai. Menjaga bentuk bank (susunan 6 + 7 + 6 + 13 + 4 soal =
 * 36 soal, seperti bank N1 asli), lima rekaman utuh (satu per もんだい), kunci jawaban, dan bahwa paket tetap
 * nonaktif selama sesi bahasa (mojigoi, bunpou_dokkai) belum ada. Test ini murni membaca berkas.
 */
class JlptMockN1ChokaiTest extends TestCase
{
    private const PACK = 'n1-test-1';

    /** Kunci jawaban (1–4) yang ditulis ulang di sini agar salah ketik di bank ketahuan. */
    private const KEY = [
        '1-1' => 4, '1-2' => 3, '1-3' => 1, '1-4' => 2, '1-5' => 4, '1-6' => 3,
        '2-1' => 3, '2-2' => 1, '2-3' => 2, '2-4' => 3, '2-5' => 4, '2-6' => 2,
        '2-7' => 4, '3-1' => 2, '3-2' => 3, '3-3' => 1, '3-4' => 4, '3-5' => 2,
        '3-6' => 3, '4-1' => 2, '4-2' => 1, '4-3' => 3, '4-4' => 2, '4-5' => 1,
        '4-6' => 3, '4-7' => 2, '4-8' => 3, '4-9' => 1, '4-10' => 2, '4-11' => 3,
        '4-12' => 1, '4-13' => 2, '5-1' => 3, '5-2' => 4, '5-3-1' => 3, '5-3-2' => 1,
    ];

    private const SIZES = [1 => 6, 2 => 7, 3 => 6, 4 => 13, 5 => 4];

    private function bank(): array
    {
        return json_decode(file_get_contents(resource_path('lang-data/jlpt/packs/'.self::PACK.'/chokai.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    private function source(): array
    {
        return json_decode(file_get_contents(resource_path('lang-data/jlpt/sources/'.self::PACK.'.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_bank_has_n1_chokai_shape_and_key(): void
    {
        $bank = $this->bank();
        $this->assertSame('N1', $bank['level']);
        $this->assertSame(60, $bank['minutes']);
        $this->assertCount(36, $bank['questions']);

        $sizes = [];
        foreach ($bank['questions'] as $q) {
            $sizes[$q['mondai']] = ($sizes[$q['mondai']] ?? 0) + 1;
            $this->assertSame(self::KEY[$q['id']], $q['answer'], "kunci {$q['id']}");
            $n = isset($q['choices']) ? count($q['choices']) : $q['choice_count'];
            $this->assertGreaterThanOrEqual(1, $q['answer']);
            $this->assertLessThanOrEqual($n, $q['answer']);
        }
        $this->assertSame(self::SIZES, $sizes);
        $this->assertSame(array_keys(self::KEY), array_column($bank['questions'], 'id'));
    }

    public function test_five_whole_recordings_match_audio_source(): void
    {
        $bank = $this->bank();
        $paths = [];
        foreach ($bank['mondai'] as $n => $m) {
            $this->assertTrue($m['whole_audio']);
            $this->assertSame("/audio/jlpt-mock/n1-test-1/n1-chokai-{$n}.mp3", $m['audio']);
            $paths[] = $m['audio'];
        }
        $this->assertCount(5, array_unique($paths));

        $ids = array_column($this->source()['sections'][0]['recordings'], 'id');
        $this->assertSame(['n1-chokai-1', 'n1-chokai-2', 'n1-chokai-3', 'n1-chokai-4', 'n1-chokai-5'], $ids);
        foreach ($this->source()['sections'][0]['recordings'] as $rec) {
            foreach ($rec['audio_text'] as $turn) {
                $this->assertContains($turn['speaker'], ['narrator', 'male', 'female']);
                $this->assertStringNotContainsString('《', $turn['text']);
                $this->assertLessThanOrEqual(10, $turn['pause'] ?? 0);
            }
        }
    }

    public function test_pack_stays_disabled_until_language_sections_exist(): void
    {
        $dir = resource_path('lang-data/jlpt/packs/'.self::PACK);
        $ready = is_file("$dir/mojigoi.json") && is_file("$dir/bunpou_dokkai.json");
        $this->assertSame($ready, (bool) config('jlpt.packs.'.self::PACK.'.enabled'));
    }
}
