<?php

namespace App\Console\Commands;

use App\Services\GoogleTextToSpeechService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Builds the listening audio of the JLPT mock tests via Google Cloud
 * Text-to-Speech — same idea as chokai:generate-audio, but the scripts
 * have three kinds of speaker (narrator / male / female) and short pauses.
 *
 * Reads the server-side bank resources/lang-data/jlpt/packs/{test}/chokai.json
 * (the file is never served publicly), finds every node that has an
 * "audio_text" (mondai intro, example and each listening question),
 * writes public/audio/jlpt-mock/{test}/{id}.mp3 and stores the path in the
 * node's "audio" field. Idempotent: an existing MP3 is skipped unless
 * --force is passed.
 *
 * "audio_text" is an array of turns:
 *   {"speaker":"narrator|male|female","text":"…","pause":1.2}
 * "pause" (seconds, optional) is silence added AFTER that turn.
 *
 * Usage:
 *   php artisan jlpt-mock:generate-audio
 *   php artisan jlpt-mock:generate-audio --test=n5-test-1 --dry-run
 *   php artisan jlpt-mock:generate-audio --force
 *   php artisan jlpt-mock:generate-audio --voice-male=ja-JP-Wavenet-C --voice-female=ja-JP-Wavenet-B --voice-narrator=ja-JP-Wavenet-D
 */
class JlptMockGenerateAudio extends Command
{
    protected $signature = 'jlpt-mock:generate-audio
        {--test= : Only process one test key, e.g. --test=n5-test-1}
        {--voice-male=ja-JP-Wavenet-C : Voice for "male" turns}
        {--voice-female=ja-JP-Wavenet-B : Voice for "female" turns}
        {--voice-narrator=ja-JP-Wavenet-D : Voice for "narrator" turns (question / instructions)}
        {--rate=0.92 : Speaking rate for male/female turns (0.25 - 4.0)}
        {--narrator-rate=1.0 : Speaking rate for the narrator}
        {--dry-run : List what would be generated, without calling Google}
        {--force : Regenerate even if the MP3 already exists}';

    protected $description = 'Generate JLPT mock-test listening audio (narrator + dialogue) via Google Cloud Text-to-Speech';

    private int $generated = 0;

    private int $skipped = 0;

    public function handle(GoogleTextToSpeechService $tts): int
    {
        // Bank soal SERVER (bukan lagi public/data/jlpt-mock): hanya sesi chokai yang punya audio_text.
        $dataDir = rtrim((string) config('jlpt.data_path'), '/').'/jlpt/packs';

        if (! File::isDirectory($dataDir)) {
            $this->error("No such directory: {$dataDir}");

            return self::FAILURE;
        }

        $only = $this->option('test');
        $files = collect(File::directories($dataDir))
            ->filter(fn ($d) => File::exists("{$d}/chokai.json"))
            ->when($only, fn ($c) => $c->filter(fn ($d) => basename($d) === $only))
            ->map(fn ($d) => new \SplFileInfo("{$d}/chokai.json"));

        if ($files->isEmpty()) {
            $this->warn('No matching test JSON files found.');

            return self::SUCCESS;
        }

        $voices = [
            'male' => $this->option('voice-male'),
            'female' => $this->option('voice-female'),
            'narrator' => $this->option('voice-narrator'),
        ];
        $rates = [
            'male' => (float) $this->option('rate'),
            'female' => (float) $this->option('rate'),
            'narrator' => (float) $this->option('narrator-rate'),
        ];
        $force = (bool) $this->option('force');
        $dry = (bool) $this->option('dry-run');

        foreach ($files as $file) {
            $json = json_decode(File::get($file->getPathname()), true);

            if (! is_array($json)) {
                $this->warn("Skipping unreadable file: {$file->getPathname()}");

                continue;
            }

            // id paket = nama folder pack (…/packs/<id>/chokai.json)
            $testId = basename(dirname($file->getPathname()));
            $dir = public_path("audio/jlpt-mock/{$testId}");
            File::ensureDirectoryExists($dir);
            $this->info("Test {$testId}");

            $json['mondai'] = $this->walk($json['mondai'] ?? [], function (array $node) use ($tts, $voices, $rates, $force, $dry, $dir, $testId) {
                $id = $node['id'];
                $mp3 = "{$dir}/{$id}.mp3";
                $public = "/audio/jlpt-mock/{$testId}/{$id}.mp3";

                if (! $force && File::exists($mp3)) {
                    $node['audio'] = $public;
                    $this->skipped++;
                    $this->line("  - {$id}: skipped (already exists)");

                    return $node;
                }

                if ($dry) {
                    $this->line("  - {$id}: would generate (".count($node['audio_text']).' turns)');

                    return $node;
                }

                try {
                    File::put($mp3, $this->synthesize($tts, $node['audio_text'], $voices, $rates));
                    $node['audio'] = $public;
                    $this->generated++;
                    $this->line("  - {$id}: generated");
                } catch (\Throwable $e) {
                    $this->error("  - {$id}: FAILED — {$e->getMessage()}");
                }

                return $node;
            });

            if (! $dry) {
                File::put(
                    $file->getPathname(),
                    json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n"
                );
            }
        }

        $this->newLine();
        $this->info("Done. Generated: {$this->generated}, skipped: {$this->skipped}.");

        return self::SUCCESS;
    }

    /**
     * Depth-first search for every node with both "id" and "audio_text";
     * $handle receives the node and returns it (with "audio" filled in).
     */
    private function walk(array $node, callable $handle): array
    {
        if (isset($node['id'], $node['audio_text']) && is_array($node['audio_text'])) {
            return $handle($node);
        }

        foreach ($node as $key => $value) {
            if (is_array($value)) {
                $node[$key] = $this->walk($value, $handle);
            }
        }

        return $node;
    }

    /**
     * @param  array<int, array{speaker: string, text: string, pause?: float|int}>  $turns
     * @param  array<string, string>  $voices
     * @param  array<string, float>  $rates
     */
    private function synthesize(GoogleTextToSpeechService $tts, array $turns, array $voices, array $rates): string
    {
        $audio = '';

        foreach ($turns as $turn) {
            $text = trim($turn['text'] ?? '');

            if ($text === '') {
                continue;
            }

            $speaker = in_array($turn['speaker'] ?? '', ['male', 'female', 'narrator'], true) ? $turn['speaker'] : 'narrator';
            $pause = (float) ($turn['pause'] ?? 0);

            $audio .= $pause > 0
                ? $tts->synthesizeSsml(
                    '<speak>'.htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8').sprintf('<break time="%dms"/>', (int) round($pause * 1000)).'</speak>',
                    $voices[$speaker], 'ja-JP', $rates[$speaker],
                )
                : $tts->synthesize($text, $voices[$speaker], 'ja-JP', $rates[$speaker]);
        }

        if ($audio === '') {
            throw new \RuntimeException('Script has no non-empty turns.');
        }

        return $audio;
    }
}
