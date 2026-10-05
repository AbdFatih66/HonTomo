<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Menjaga bentuk materi kaiwa (public/data/kaiwa): manifest cocok dengan berkas,
 * id giliran unik global, `speak` hanya berisi kana (agar TTS tidak salah baca),
 * dan tiap skenario punya 6–8 giliran dengan 3–4 giliran user.
 */
class KaiwaContentTest extends TestCase
{
    private function load(string $file): array
    {
        $path = public_path("data/kaiwa/{$file}");
        $this->assertFileExists($path);
        $json = json_decode(file_get_contents($path), true);
        $this->assertIsArray($json, "{$file} bukan JSON valid");

        return $json;
    }

    public function test_manifest_lists_existing_lesson_files_with_matching_ids(): void
    {
        $manifest = $this->load('index.json');

        $this->assertNotEmpty($manifest['lessons']);

        foreach ($manifest['lessons'] as $entry) {
            $lesson = $this->load($entry['file']);
            $this->assertSame($entry['id'], $lesson['id'], "id di {$entry['file']} tidak cocok dengan index.json");
        }
    }

    public function test_every_scenario_is_well_formed(): void
    {
        $seen = [];

        foreach ($this->load('index.json')['lessons'] as $entry) {
            $lesson = $this->load($entry['file']);

            $this->assertGreaterThanOrEqual(4, count($lesson['scenarios']), "Pelajaran {$lesson['id']} minimal 4 skenario");

            foreach ($lesson['scenarios'] as $sc) {
                $this->assertMatchesRegularExpression('/^l'.$lesson['id'].'-s\d+$/', $sc['id']);
                $this->assertContains($sc['partner']['voice'], ['male', 'female']);

                $you = array_filter($sc['turns'], fn ($t) => $t['who'] === 'you');
                $this->assertGreaterThanOrEqual(6, count($sc['turns']), $sc['id']);
                $this->assertLessThanOrEqual(8, count($sc['turns']), $sc['id']);
                $this->assertGreaterThanOrEqual(3, count($you), $sc['id']);
                $this->assertLessThanOrEqual(4, count($you), $sc['id']);

                foreach ($sc['turns'] as $turn) {
                    $this->assertArrayNotHasKey($turn['id'], $seen, "id giliran ganda: {$turn['id']}");
                    $seen[$turn['id']] = true;

                    $this->assertStringStartsWith($sc['id'].'-t', $turn['id']);
                    $this->assertNotSame('', trim($turn['id_text']), $turn['id']);
                    $this->assertDoesNotMatchRegularExpression('/\p{Han}|《/u', $turn['speak'], "speak harus kana: {$turn['id']}");

                    if ($turn['who'] === 'you')
                        $this->assertIsArray($turn['accept'], $turn['id']);

                    // furigana dipasang per rentetan kanji (売《う》り場《ば》, bukan 売り場《うりば》):
                    // RubyText dan registerReadings() hanya mengenali kanji murni sebelum 《
                    $bare = preg_replace('/\p{Han}+《[^》]*》/u', '', $turn['ja']);
                    $this->assertDoesNotMatchRegularExpression('/\p{Han}/u', $bare, "kanji tanpa furigana / furigana salah bentuk: {$turn['id']}");

                    // `speak` harus sama dengan `ja` yang dibaca lewat furigana (abaikan spasi, … dan tanda baca 、。)
                    $reading = preg_replace('/\p{Han}+《([^》]*)》/u', '$1', $turn['ja']);
                    $strip = fn (string $s) => preg_replace('/[\s…、。]/u', '', $s);
                    $this->assertSame($strip($turn['speak']), $strip($reading), "speak tidak cocok dengan furigana ja: {$turn['id']}");
                }
            }
        }
    }

    public function test_situation_sets_are_listed_and_well_formed(): void
    {
        $index = $this->load('index.json');
        $this->assertNotEmpty($index['situations'] ?? [], 'index.json belum memuat `situations`');

        $seen = [];

        foreach ($index['situations'] as $entry) {
            $set = $this->load($entry['file']);

            $this->assertSame($entry['id'], $set['id'], "id di {$entry['file']} tidak cocok dengan index.json");
            $this->assertSame('situation', $set['track']);
            $this->assertContains($set['rounds'] ?? 2, [1, 2]);
            $this->assertNotSame('', trim($set['title_id']));
            $this->assertNotEmpty($set['scenarios']);

            foreach ($set['scenarios'] as $sc) {
                // id dipakai sebagai set_key progres: '{paket}-s{urut}' (mis. mensetsu-s3); NotificationService
                // dan KaiwaController mengenalinya dari pola ini
                $this->assertMatchesRegularExpression('/^'.preg_quote($set['id'], '/').'-s\d+$/', $sc['id']);
                $this->assertContains($sc['partner']['voice'], ['male', 'female']);
                $this->assertNotSame('', trim($sc['goal_id']), $sc['id']);

                $you = array_filter($sc['turns'], fn ($t) => $t['who'] === 'you');
                $this->assertGreaterThanOrEqual(6, count($sc['turns']), $sc['id']);
                $this->assertLessThanOrEqual(10, count($sc['turns']), $sc['id']);
                $this->assertGreaterThanOrEqual(3, count($you), $sc['id']);

                // tips/kata/pertanyaan serupa (opsional di format, wajib di paket Mensetsu)
                foreach (['tips_id', 'words', 'related'] as $key)
                    $this->assertNotEmpty($sc[$key] ?? [], "{$sc['id']}: {$key} kosong");

                foreach (array_merge($sc['words'], $sc['related']) as $item) {
                    $this->assertNotSame('', trim($item['ja']), $sc['id']);
                    $this->assertNotSame('', trim($item['id']), $sc['id']);
                    $this->assertDoesNotMatchRegularExpression('/\p{Han}/u', preg_replace('/\p{Han}+《[^》]*》/u', '', $item['ja']), "kanji tanpa furigana: {$item['ja']}");
                }

                foreach ($sc['turns'] as $turn) {
                    $this->assertArrayNotHasKey($turn['id'], $seen, "id giliran ganda: {$turn['id']}");
                    $seen[$turn['id']] = true;

                    $this->assertStringStartsWith($sc['id'].'-t', $turn['id']);
                    $this->assertNotSame('', trim($turn['id_text']), $turn['id']);
                    $this->assertNotSame('', trim($turn['en_text']), $turn['id']);
                    $this->assertDoesNotMatchRegularExpression('/\p{Han}|《/u', $turn['speak'], "speak harus kana: {$turn['id']}");

                    $bare = preg_replace('/\p{Han}+《[^》]*》/u', '', $turn['ja']);
                    $this->assertDoesNotMatchRegularExpression('/\p{Han}/u', $bare, "kanji tanpa furigana: {$turn['id']}");

                    $reading = preg_replace('/\p{Han}+《([^》]*)》/u', '$1', $turn['ja']);
                    $strip = fn (string $s) => preg_replace('/[\s…、。]/u', '', $s);
                    $this->assertSame($strip($turn['speak']), $strip($reading), "speak tidak cocok dengan furigana ja: {$turn['id']}");

                    // giliran user di paket situasi selalu punya varian yang diterima (kanji dengan/tanpa spasi)
                    if ($turn['who'] === 'you')
                        $this->assertNotEmpty($turn['accept'], $turn['id']);
                }
            }
        }
    }

    public function test_situation_packs_keep_one_consistent_character(): void
    {
        // Jawaban contoh memakai satu tokoh (Budi, 25 tahun, Jakarta, 4 tahun di pabrik). Menjaga agar
        // skenario baru tidak menyisipkan data tokoh lain (mis. kota atau masa kerja yang berbeda).
        $index = $this->load('index.json');

        foreach ($index['situations'] as $entry) {
            $json = json_encode($this->load($entry['file']), JSON_UNESCAPED_UNICODE);

            $this->assertStringNotContainsString('スラバヤ', $json);
            $this->assertStringNotContainsString('三年間《さんねんかん》、会社', $json);
        }
    }
}
