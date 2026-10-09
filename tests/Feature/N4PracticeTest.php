<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Latihan N4 (Pelajaran 1–25): Latihan Soal (mondaishuu), Menyimak (chokai),
 * Percakapan (kaiwa) dan Menulis (kaite-oboeru) memakai kunci progres `n4l{n}`
 * (kaiwa: `n4l{n}-s{k}`) supaya tidak bentrok dengan `l{n}` milik N5.
 */
class N4PracticeTest extends TestCase
{
    use RefreshDatabase;

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

    /** Pelajaran N4 yang punya berkas Chokai dan Kaiwa (nomor = nomor unit N4). */
    private const LESSONS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25];

    /** Skenario lama yang lebih pendek dari aturan 6–8 giliran / 3–4 giliran user; perpanjang lalu hapus dari daftar ini. */
    private const SHORT_SCENARIOS = ['n4l3-s1', 'n4l3-s2', 'n4l3-s4', 'n4l11-s2', 'n4l11-s3'];

    /** @return array<string, array{int, int}> nomor pelajaran => [nomor, jumlah soal Chokai] */
    public static function lessonNumbers(): array
    {
        $data = [];

        foreach (self::LESSONS as $n)
            $data["Pelajaran {$n}"] = [$n, $n === 19 ? 9 : 10];

        return $data;
    }

    /** @dataProvider lessonNumbers */
    public function test_chokai_n4_lesson_is_well_formed(int $n, int $expectedQuestions): void
    {
        $lesson = $this->loadJson("data/chokai/lesson-n4-{$n}.json");

        $this->assertSame("n4-{$n}", $lesson['id']);
        $this->assertCount($expectedQuestions, $lesson['questions']);
        $this->assertLessThanOrEqual(2, count($lesson['kaiwa']['questions']));

        foreach ($lesson['questions'] as $i => $q) {
            $this->assertSame("n4l{$n}-q".($i + 1), $q['id']);
            $this->assertArrayHasKey($q['answer'], $q['choices'], $q['id']);
            $this->assertSame(count($q['choices']), count(array_unique($q['choices'])), $q['id']);
            $this->assertNotSame('', trim($q['translate']['id']), $q['id']);
            $this->assertNotSame('', trim($q['translate']['en']), $q['id']);

            foreach ((array) $q['audio_text'] as $turn) {
                $text = is_array($turn) ? $turn['text'] : $turn;
                $this->assertStringNotContainsString('《', $text, "audio_text tidak boleh berisi furigana: {$q['id']}");
            }
        }
    }

    public function test_kaiwa_n4_manifest_and_scenarios_are_well_formed(): void
    {
        $manifest = $this->loadJson('data/kaiwa/index-n4.json');
        $this->assertSame(self::LESSONS, array_column($manifest['lessons'], 'id'));

        $seen = [];

        foreach ($manifest['lessons'] as $entry) {
            $lesson = $this->loadJson("data/kaiwa/{$entry['file']}");
            $this->assertSame($entry['id'], $lesson['id']);
            $this->assertSame('N4', $lesson['level']);
            $this->assertGreaterThanOrEqual(4, count($lesson['scenarios']));

            foreach ($lesson['scenarios'] as $sc) {
                $this->assertMatchesRegularExpression('/^n4l'.$lesson['id'].'-s\d+$/', $sc['id']);
                $this->assertContains($sc['partner']['voice'], ['male', 'female']);

                $you = array_filter($sc['turns'], fn ($t) => $t['who'] === 'you');
                $minTurns = in_array($sc['id'], self::SHORT_SCENARIOS, true) ? 5 : 6;
                $minYou = in_array($sc['id'], self::SHORT_SCENARIOS, true) ? 2 : 3;
                $this->assertGreaterThanOrEqual($minTurns, count($sc['turns']), $sc['id']);
                $this->assertLessThanOrEqual(8, count($sc['turns']), $sc['id']);
                $this->assertGreaterThanOrEqual($minYou, count($you), $sc['id']);
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

    public function test_kaiwa_accepts_every_n4_lesson_first_scenario(): void
    {
        $user = User::factory()->create();

        foreach (self::LESSONS as $n)
            $this->actingAsToken($user)->postJson("/api/kaiwa/progress/n4l{$n}-s1", ['perfect' => true])->assertOk();
    }

    public function test_kaiwa_accepts_n4_scenario_ids_and_rejects_made_up_ones(): void
    {
        $user = User::factory()->create();

        $this->actingAsToken($user)->postJson('/api/kaiwa/progress/n4l1-s1', ['perfect' => true])
            ->assertOk()->assertJsonPath('xp', 15);

        $this->actingAsToken($user)->postJson('/api/kaiwa/progress/n4l16-s1', ['perfect' => true])->assertOk();
        $this->actingAsToken($user)->postJson('/api/kaiwa/progress/n4l1-s99', ['perfect' => true])->assertNotFound();
        $this->actingAsToken($user)->postJson('/api/kaiwa/progress/n4l7-s1', ['perfect' => true])->assertNotFound();
    }

    public function test_n4_set_keys_get_a_readable_notification_label(): void
    {
        $user = User::factory()->create();

        foreach (['mondaishuu' => 'n4l1', 'chokai' => 'n4l1', 'kaite-oboeru' => 'n4l1', 'kaiwa' => 'n4l1-s2'] as $module => $key) {
            $this->actingAsToken($user)->postJson("/api/{$module}/progress/{$key}", ['perfect' => false])->assertOk();
        }

        $labels = UserNotification::where('user_id', $user->id)->get()
            ->map(fn ($n) => json_decode($n->subtitle, true)['lesson'])->all();

        $this->assertContains('N4 Pelajaran 1', $labels);
        $this->assertContains('N4 Pelajaran 1 · Skenario 2', $labels);
        $this->assertNotContains('Pelajaran 4', $labels); // 'n4l1' tidak boleh terbaca sebagai pelajaran N5
    }
}
