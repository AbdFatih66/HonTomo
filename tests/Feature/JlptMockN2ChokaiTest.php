<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Paket orisinal HonTomo N2 (`n2-test-1`) — baru sesi chokai. Menjaga bentuk bank (susunan
 * 5 + 6 + 5 + 11 + 4 soal seperti ujian N2 asli, tanpa gambar), 42 berkas audio TTS, kunci jawaban,
 * dan bahwa paket tetap nonaktif selama sesi bahasa (mojigoi, bunpou_dokkai) belum ada.
 * Test ini murni membaca berkas.
 */
class JlptMockN2ChokaiTest extends TestCase
{
    private const PACK = 'n2-test-1';

    private const KEY = [
        'l1-1' => 1, 'l1-2' => 4, 'l1-3' => 2, 'l1-4' => 3, 'l1-5' => 1,
        'l2-1' => 2, 'l2-2' => 1, 'l2-3' => 4, 'l2-4' => 3, 'l2-5' => 2, 'l2-6' => 1,
        'l3-1' => 4, 'l3-2' => 1, 'l3-3' => 4, 'l3-4' => 1, 'l3-5' => 2,
        'l4-1' => 2, 'l4-2' => 3, 'l4-3' => 1, 'l4-4' => 2, 'l4-5' => 1, 'l4-6' => 3, 'l4-7' => 2, 'l4-8' => 3, 'l4-9' => 1, 'l4-10' => 2, 'l4-11' => 3,
        'l5-1' => 2, 'l5-2' => 1, 'l5-3' => 4, 'l5-4' => 3,
    ];

    private const EXAMPLE_KEY = [1 => 3, 2 => 3, 3 => 3, 4 => 1];

    private const SIZES = [1 => 5, 2 => 6, 3 => 5, 4 => 11, 5 => 4];

    private function bank(): array
    {
        return json_decode(file_get_contents(resource_path('lang-data/jlpt/packs/'.self::PACK.'/chokai.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    private function source(): array
    {
        return json_decode(file_get_contents(resource_path('lang-data/jlpt/sources/'.self::PACK.'.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    private function audioPaths(array $node): array
    {
        $out = [];
        array_walk_recursive($node, function ($v, $k) use (&$out) {
            if (in_array($k, ['audio', 'audio_before', 'intro', 'outro'], true) && is_string($v) && str_starts_with($v, '/audio/'))
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

        $this->assertArrayNotHasKey('example', $bank['mondai']['5']);
    }

    public function test_bank_has_the_n2_listening_layout(): void
    {
        $bank = $this->bank();

        $this->assertSame(['N2', 'chokai', 50], [$bank['level'], $bank['section'], $bank['minutes']]);
        $this->assertSame(['1', '2', '3', '4', '5'], array_keys($bank['mondai']));
        $this->assertCount(31, $bank['questions']);

        $perMondai = array_count_values(array_column($bank['questions'], 'mondai'));
        ksort($perMondai);
        $this->assertSame(self::SIZES, $perMondai);

        $ids = array_column($bank['questions'], 'id');
        $this->assertSame($ids, array_values(array_unique($ids)), 'id soal harus unik');

        foreach ($bank['questions'] as $q) {
            $id = $q['id'];
            $count = isset($q['choices']) ? count($q['choices']) : $q['choice_count'];

            $this->assertNotEmpty($q['script'], $id);
            $this->assertSame($q['mondai'] === 4 ? 3 : 4, $count, $id);
            $this->assertGreaterThanOrEqual(1, $q['answer'], $id);
            $this->assertLessThanOrEqual($count, $q['answer'], $id);
            $this->assertArrayNotHasKey('image', $q, $id);
            // soal 3ばん (mondai 5) dipecah dua node, jadi id mengikuti urutan node, bukan nomor soal
            if ($q['mondai'] < 5)
                $this->assertSame("l{$q['mondai']}-{$q['no']}", $id);
            // pilihan tercetak hanya di mondai 1, 2 dan 5 (3番); mondai 3 & 4 hanya terdengar
            if ($q['mondai'] === 3 || $q['mondai'] === 4)
                $this->assertArrayNotHasKey('choices', $q, $id);
        }

        $this->assertTrue($bank['mondai']['3']['memo']);
        $this->assertTrue($bank['mondai']['4']['memo']);

        $byId = collect($bank['questions'])->keyBy('id');
        $this->assertArrayNotHasKey('choices', $byId['l5-1']);
        $this->assertArrayNotHasKey('choices', $byId['l5-2']);
        $this->assertCount(4, $byId['l5-3']['choices']);
        $this->assertSame('3ばん 質問1', $byId['l5-3']['label']);
        $this->assertSame('3ばん 質問2', $byId['l5-4']['label']);
    }

    public function test_audio_is_the_42_node_structure(): void
    {
        $bank = $this->bank();
        $paths = $this->audioPaths(['a' => $bank['audio'], 'm' => $bank['mondai'], 'q' => $bank['questions']]);

        // intro + outro + 5 petunjuk + 4 れい + 31 soal = 42
        $this->assertCount(42, $paths);
        $this->assertSame($paths, array_values(array_unique($paths)), 'tiap berkas audio dipakai sekali');

        foreach ($paths as $p)
            $this->assertMatchesRegularExpression('#^/audio/jlpt-mock/'.self::PACK.'/l(-intro|-outro|\d-(intro|ex|\d))\.mp3$#', $p);
    }

    public function test_script_source_and_bank_agree(): void
    {
        $src = $this->source();
        $this->assertSame(self::PACK, $src['id']);

        $bank = collect($this->bank()['questions'])->keyBy('id');
        $seen = 0;

        foreach ($src['sections'][0]['mondai'] as $m) {
            foreach ($m['groups'][0]['questions'] as $q) {
                $seen++;
                $this->assertSame($bank[$q['id']]['answer'], $q['answer'] + 1, "{$q['id']} kunci sumber vs bank");
                $this->assertSame('/audio/jlpt-mock/'.self::PACK."/{$q['id']}.mp3", $q['audio'], $q['id']);
            }
        }
        $this->assertSame(31, $seen);

        $nodes = [];
        $walk = function (array $n) use (&$walk, &$nodes) {
            if (isset($n['id'], $n['audio_text'])) {
                $nodes[] = $n;

                return;
            }
            foreach ($n as $v)
                if (is_array($v))
                    $walk($v);
        };
        $walk($src);

        $this->assertCount(42, $nodes);
        foreach ($nodes as $n) {
            $this->assertSame('/audio/jlpt-mock/'.self::PACK."/{$n['id']}.mp3", $n['audio'], $n['id']);
            foreach ($n['audio_text'] as $turn) {
                $this->assertContains($turn['speaker'], ['narrator', 'male', 'female'], $n['id']);
                $this->assertNotSame('', trim($turn['text']), $n['id']);
                $this->assertStringNotContainsString('《', $turn['text'], "{$n['id']}: audio_text tidak boleh berisi furigana");
            }
        }
    }

    public function test_pack_stays_disabled_until_every_n2_section_exists(): void
    {
        $pack = config('jlpt.packs.'.self::PACK);

        $this->assertSame(['N2', 'classic'], [$pack['level'], $pack['format']]);

        if (! $pack['enabled'])
            $this->assertFileExists(resource_path('lang-data/'.$pack['data_dir'].'/chokai.json'));
        else {
            foreach (config('jlpt.levels.N2.sections') as $key => $s)
                $this->assertFileExists(resource_path('lang-data/'.$pack['data_dir'].'/'.$s['file']), "pack aktif tapi sesi {$key} belum ada");
        }
    }

    public function test_audio_files_exist_when_folder_is_present(): void
    {
        $audioDir = public_path('audio/jlpt-mock/'.self::PACK);

        if (! is_dir($audioDir))
            $this->markTestSkipped('Folder audio mock N2 belum ada di lingkungan ini.');

        $bank = $this->bank();
        $missing = [];
        foreach ($this->audioPaths(['a' => $bank['audio'], 'm' => $bank['mondai'], 'q' => $bank['questions']]) as $p)
            if (! is_file(public_path(ltrim($p, '/'))))
                $missing[] = $p;

        $this->assertSame([], $missing, 'Audio belum dibuat: jalankan php artisan jlpt-mock:generate-audio --test='.self::PACK);
    }
}
