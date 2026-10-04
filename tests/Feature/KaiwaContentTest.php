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
                }
            }
        }
    }
}
