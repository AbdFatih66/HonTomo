<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Paket orisinal HonTomo N4 (`n4-test-1`) — baru sesi chokai. Menjaga bentuk bank (susunan
 * 8 + 7 + 5 + 8 soal seperti ujian N4 asli), 39 berkas audio TTS (sama dengan 39 trek rekaman
 * asli, termasuk istirahat), 23 gambar, kunci jawaban, dan bahwa paket tetap nonaktif selama
 * sesi bahasa (mojigoi, bunpou_dokkai) belum ada. Test ini murni membaca berkas.
 */
class JlptMockN4ChokaiTest extends TestCase
{
    private const PACK = 'n4-test-1';

    /** Kunci jawaban (1–4) yang ditulis ulang di sini agar salah ketik di bank ketahuan. */
    private const KEY = [
        'l1-1' => 3, 'l1-2' => 1, 'l1-3' => 1, 'l1-4' => 3, 'l1-5' => 2, 'l1-6' => 3, 'l1-7' => 1, 'l1-8' => 4,
        'l2-1' => 2, 'l2-2' => 4, 'l2-3' => 1, 'l2-4' => 4, 'l2-5' => 2, 'l2-6' => 4, 'l2-7' => 1,
        'l3-1' => 2, 'l3-2' => 1, 'l3-3' => 3, 'l3-4' => 2, 'l3-5' => 3,
        'l4-1' => 2, 'l4-2' => 1, 'l4-3' => 3, 'l4-4' => 2, 'l4-5' => 1, 'l4-6' => 3, 'l4-7' => 2, 'l4-8' => 3,
    ];

    private const EXAMPLE_KEY = [1 => 2, 2 => 3, 3 => 3, 4 => 1];

    private const SIZES = [1 => 8, 2 => 7, 3 => 5, 4 => 8];

    private function bank(): array
    {
        return json_decode(file_get_contents(resource_path('lang-data/jlpt/packs/'.self::PACK.'/chokai.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    private function source(): array
    {
        return json_decode(file_get_contents(resource_path('lang-data/jlpt/sources/'.self::PACK.'.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    /** Semua nilai string di bawah kunci "audio" / "audio_before" (rekursif). */
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
    }

    public function test_bank_has_the_n4_listening_layout(): void
    {
        $bank = $this->bank();

        $this->assertSame(['N4', 'chokai', 35], [$bank['level'], $bank['section'], $bank['minutes']]);
        $this->assertSame(['1', '2', '3', '4'], array_keys($bank['mondai']));
        $this->assertCount(28, $bank['questions']);

        $perMondai = array_count_values(array_column($bank['questions'], 'mondai'));
        ksort($perMondai);
        $this->assertSame(self::SIZES, $perMondai);

        $ids = array_column($bank['questions'], 'id');
        $this->assertSame($ids, array_values(array_unique($ids)), 'id soal harus unik');

        foreach ($bank['questions'] as $q) {
            $id = $q['id'];
            $count = isset($q['choices']) ? count($q['choices']) : $q['choice_count'];

            $this->assertNotEmpty($q['script'], $id);
            $this->assertSame($q['mondai'] <= 2 ? 4 : 3, $count, $id);
            $this->assertGreaterThanOrEqual(1, $q['answer'], $id);
            $this->assertLessThanOrEqual($count, $q['answer'], $id);
            $this->assertSame("l{$q['mondai']}-{$q['no']}", $id);
            if ($q['mondai'] >= 3)
                $this->assertArrayNotHasKey('choices', $q, $id);
            if ($q['mondai'] === 3)
                $this->assertSame(['x' => 50, 'y' => 9], $q['arrow'], "{$id} arrow");
        }

        $this->assertTrue($bank['mondai']['4']['memo']);
        $this->assertSame(['ア', 'イ', 'ウ', 'エ'], array_column(collect($bank['questions'])->firstWhere('id', 'l1-4')['marks'], 'text'));
    }

    public function test_audio_is_the_39_track_structure_of_the_real_recording(): void
    {
        $bank = $this->bank();
        $paths = $this->audioPaths(['audio' => $bank['audio'], 'mondai' => $bank['mondai'], 'questions' => $bank['questions']]);

        // 1 penjelasan umum + 4×(petunjuk + れい) + 28 soal + 1 istirahat + 1 penutup = 39
        $this->assertCount(39, $paths);
        $this->assertSame($paths, array_values(array_unique($paths)), 'tiap berkas audio dipakai sekali');

        foreach ($paths as $p)
            $this->assertMatchesRegularExpression('#^/audio/jlpt-mock/'.self::PACK.'/l(-intro|-outro|\d-(intro|ex|break|\d))\.mp3$#', $p);

        $this->assertSame('/audio/jlpt-mock/n4-test-1/l3-break.mp3', $bank['mondai']['3']['audio_before']);
        $this->assertArrayNotHasKey('audio_before', $bank['mondai']['1']);
    }

    public function test_images_follow_the_prompt_sheet(): void
    {
        $found = [];
        array_walk_recursive($this->bank(), function ($v, $k) use (&$found) {
            if ($k === 'image' && is_string($v))
                $found[] = $v;
        });
        $found = array_values(array_unique($found));
        sort($found);

        $this->assertCount(23, $found);

        $doc = file_get_contents(base_path('docs/jlpt-mock-images-n4.md'));
        foreach ($found as $path) {
            $this->assertMatchesRegularExpression('#^/images/jlpt-mock/n4/(l1-[1256]-c[1-4]|l1-4|l3-(ex|[1-5]))\.png$#', $path);
            $this->assertStringContainsString('`'.basename($path).'`', $doc, "{$path} belum ada di docs/jlpt-mock-images-n4.md");
        }
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
        $this->assertSame(28, $seen);

        // naskah TTS: pembicara valid, tanpa penanda furigana 《》, tiap node punya nama berkas = id
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

        $this->assertCount(39, $nodes);
        foreach ($nodes as $n) {
            $this->assertSame('/audio/jlpt-mock/'.self::PACK."/{$n['id']}.mp3", $n['audio'], $n['id']);
            foreach ($n['audio_text'] as $turn) {
                $this->assertContains($turn['speaker'], ['narrator', 'male', 'female'], $n['id']);
                $this->assertNotSame('', trim($turn['text']), $n['id']);
                $this->assertStringNotContainsString('《', $turn['text'], "{$n['id']}: audio_text tidak boleh berisi furigana");
            }
        }
    }

    public function test_pack_stays_disabled_until_every_n4_section_exists(): void
    {
        $pack = config('jlpt.packs.'.self::PACK);

        $this->assertSame(['N4', 'classic'], [$pack['level'], $pack['format']]);

        if (! $pack['enabled'])
            $this->assertFileExists(resource_path('lang-data/'.$pack['data_dir'].'/chokai.json'));
        else {
            // level N4 mewajibkan semua sesi aktif: pack aktif harus punya semua bank-nya
            foreach (config('jlpt.levels.N4.sections') as $key => $s)
                $this->assertFileExists(resource_path('lang-data/'.$pack['data_dir'].'/'.$s['file']), "pack aktif tapi sesi {$key} belum ada");
        }
    }

    public function test_audio_and_image_files_exist_when_folders_are_present(): void
    {
        $audioDir = public_path('audio/jlpt-mock/'.self::PACK);
        $imageDir = public_path('images/jlpt-mock/n4');

        if (! is_dir($audioDir) && ! is_dir($imageDir))
            $this->markTestSkipped('Folder audio/gambar mock N4 belum ada di lingkungan ini.');

        $bank = $this->bank();
        $missing = [];
        foreach ($this->audioPaths(['a' => $bank['audio'], 'm' => $bank['mondai'], 'q' => $bank['questions']]) as $p)
            if (is_dir($audioDir) && ! is_file(public_path(ltrim($p, '/'))))
                $missing[] = $p;

        $this->assertSame([], $missing, 'Audio belum dibuat: jalankan php artisan jlpt-mock:generate-audio --test='.self::PACK);
    }
}
