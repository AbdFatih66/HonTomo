<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Latihan N3: Latihan Soal (mondaishuu), Menyimak (chokai), Percakapan (kaiwa) dan Menulis
 * (kaite-oboeru) memakai kunci progres `n3l{n}` (kaiwa: `n3l{n}-s{k}`) supaya tidak bentrok
 * dengan `l{n}` milik N5 maupun `n4l{n}` milik N4.
 */
class N3PracticeTest extends TestCase
{
    use RefreshDatabase;

    /** Pelajaran N3 yang punya berkas Chokai dan Kaiwa (nomor = nomor unit N3). Tambah nomor baru di sini. */
    private const LESSONS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

    private function actingAsToken(User $user): static
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($user->createToken('spa')->plainTextToken);
    }

    private function loadJson(string $path): array
    {
        $file = public_path($path);
        $this->assertFileExists($file);
        $json = json_decode(file_get_contents($file), true);
        $this->assertIsArray($json, "{$path} bukan JSON valid");

        return $json;
    }

    // ---- konten ---------------------------------------------------------

    /** @return array<string, array{int}> */
    public static function lessonNumbers(): array
    {
        $data = [];

        foreach (self::LESSONS as $n)
            $data["Pelajaran {$n}"] = [$n];

        return $data;
    }

    /** @dataProvider lessonNumbers */
    public function test_chokai_n3_lesson_is_well_formed(int $n): void
    {
        $lesson = $this->loadJson("data/chokai/lesson-n3-{$n}.json");

        $this->assertSame("n3-{$n}", $lesson['id']);
        $this->assertSame('N3', $lesson['level']);
        $this->assertCount(10, $lesson['questions']);
        $this->assertLessThanOrEqual(2, count($lesson['kaiwa']['questions']));

        foreach ($lesson['questions'] as $i => $q) {
            $this->assertSame("n3l{$n}-q".($i + 1), $q['id']);
            $this->assertArrayHasKey($q['answer'], $q['choices'], $q['id']);
            $this->assertSame(count($q['choices']), count(array_unique($q['choices'])), $q['id']);
            $this->assertNotSame('', trim($q['translate']['id']), $q['id']);
            $this->assertNotSame('', trim($q['translate']['en']), $q['id']);

            foreach ((array) $q['audio_text'] as $turn) {
                $text = is_array($turn) ? $turn['text'] : $turn;
                $this->assertStringNotContainsString('《', $text, "audio_text tidak boleh berisi furigana: {$q['id']}");
            }

            foreach (array_merge([$q['prompt']], $q['choices']) as $text) {
                $bare = preg_replace('/\p{Han}+《[^》]*》/u', '', $text);
                $this->assertDoesNotMatchRegularExpression('/\p{Han}/u', $bare, "kanji tanpa furigana: {$q['id']}");
            }
        }
    }

    public function test_kaiwa_n3_manifest_and_scenarios_are_well_formed(): void
    {
        $manifest = $this->loadJson('data/kaiwa/index-n3.json');
        $this->assertSame(self::LESSONS, array_column($manifest['lessons'], 'id'));

        $seen = [];

        foreach ($manifest['lessons'] as $entry) {
            $lesson = $this->loadJson("data/kaiwa/{$entry['file']}");
            $this->assertSame($entry['id'], $lesson['id']);
            $this->assertSame('N3', $lesson['level']);
            $this->assertGreaterThanOrEqual(4, count($lesson['scenarios']));

            foreach ($lesson['scenarios'] as $sc) {
                $this->assertMatchesRegularExpression('/^n3l'.$lesson['id'].'-s\d+$/', $sc['id']);
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

                    $bare = preg_replace('/\p{Han}+《[^》]*》/u', '', $turn['ja']);
                    $this->assertDoesNotMatchRegularExpression('/\p{Han}/u', $bare, "kanji tanpa furigana: {$turn['id']}");

                    $reading = preg_replace('/\p{Han}+《([^》]*)》/u', '$1', $turn['ja']);
                    $strip = fn (string $s) => preg_replace('/[\s…、。「」]/u', '', $s);
                    $this->assertSame($strip($turn['speak']), $strip($reading), "speak tidak cocok dengan furigana: {$turn['id']}");

                    if ($turn['who'] === 'you')
                        $this->assertNotEmpty($turn['accept'], $turn['id']);
                }
            }
        }
    }

    // ---- progres --------------------------------------------------------

    public function test_kaiwa_accepts_n3_scenario_ids_and_rejects_made_up_ones(): void
    {
        $user = User::factory()->create();

        $this->actingAsToken($user)->postJson('/api/kaiwa/progress/n3l1-s1', ['perfect' => true])
            ->assertOk()->assertJsonPath('xp', 15);

        $this->actingAsToken($user)->postJson('/api/kaiwa/progress/n3l1-s6', ['perfect' => true])->assertOk();
        $this->actingAsToken($user)->postJson('/api/kaiwa/progress/n3l1-s99', ['perfect' => true])->assertNotFound();
        $this->actingAsToken($user)->postJson('/api/kaiwa/progress/n3l2-s1', ['perfect' => true])->assertNotFound();
    }

    public function test_n3_set_keys_get_a_readable_notification_label(): void
    {
        $user = User::factory()->create();

        foreach (['mondaishuu' => 'n3l1', 'chokai' => 'n3l1', 'kaite-oboeru' => 'n3l1', 'kaiwa' => 'n3l1-s2'] as $module => $key) {
            $this->actingAsToken($user)->postJson("/api/{$module}/progress/{$key}", ['perfect' => false])->assertOk();
        }

        $labels = UserNotification::where('user_id', $user->id)->get()
            ->map(fn ($n) => json_decode($n->subtitle, true)['lesson'])->all();

        $this->assertContains('N3 Pelajaran 1', $labels);
        $this->assertContains('N3 Pelajaran 1 · Skenario 2', $labels);
        $this->assertNotContains('Pelajaran 3', $labels); // 'n3l1' tidak boleh terbaca sebagai pelajaran N5
    }
}
