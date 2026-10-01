<?php

namespace App\Console\Commands;

use App\Services\GoogleTextToSpeechService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Reads every public/data/chokai/lesson-*.json script, synthesizes audio
 * for the kaiwa (intro dialogue) and each question via Google Cloud
 * Text-to-Speech, saves the MP3s under public/audio/chokai/lesson-{n}/,
 * and writes the resulting "audio" path back into the JSON file so the
 * frontend picks it up on next load.
 *
 * Idempotent by default: an item that already has a non-null "audio"
 * field and whose MP3 file still exists is skipped. Pass --force to
 * regenerate everything (e.g. after editing audio_text or switching
 * voices).
 *
 * Usage:
 *   php artisan chokai:generate-audio
 *   php artisan chokai:generate-audio --force
 *   php artisan chokai:generate-audio --lesson=1
 *   php artisan chokai:generate-audio --voice=ja-JP-Wavenet-B
 *   php artisan chokai:generate-audio --voice-male=ja-JP-Wavenet-D --voice-female=ja-JP-Wavenet-A
 *
 * "audio_text" may be either:
 *   - a plain string            -> read by --voice (single voice), or
 *   - an array of dialogue turns -> [{"speaker":"male","text":"..."},
 *                                    {"speaker":"female","text":"..."}]
 *                                   read by --voice-male / --voice-female.
 */
class ChokaiGenerateAudio extends Command
{
    protected $signature = 'chokai:generate-audio
        {--lesson= : Only process one lesson number, e.g. --lesson=1}
        {--voice=ja-JP-Wavenet-C : Voice for plain-string audio_text (single male speaker)}
        {--voice-male=ja-JP-Wavenet-C : Voice for "male" turns in dialogue audio_text}
        {--voice-female=ja-JP-Wavenet-B : Voice for "female" turns in dialogue audio_text}
        {--rate=0.92 : Speaking rate (0.25 - 4.0, 1.0 = normal)}
        {--force : Regenerate even if an audio file already exists}';

    protected $description = 'Generate Chokai listening audio (kaiwa + questions) via Google Cloud Text-to-Speech';

    public function handle(GoogleTextToSpeechService $tts): int
    {
        $dataDir = public_path('data/chokai');
        $audioBaseDir = public_path('audio/chokai');

        if (! File::isDirectory($dataDir)) {
            $this->error("No such directory: {$dataDir}");

            return self::FAILURE;
        }

        $lessonFilter = $this->option('lesson');
        $voice = $this->option('voice');
        $voices = [
            'male' => $this->option('voice-male'),
            'female' => $this->option('voice-female'),
        ];
        $rate = (float) $this->option('rate');
        $force = (bool) $this->option('force');

        $files = collect(File::files($dataDir))
            ->filter(fn ($f) => str_starts_with($f->getFilename(), 'lesson-') && $f->getExtension() === 'json')
            ->when($lessonFilter, fn ($c) => $c->filter(fn ($f) => $f->getFilename() === "lesson-{$lessonFilter}.json"));

        if ($files->isEmpty()) {
            $this->warn('No matching lesson JSON files found.');

            return self::SUCCESS;
        }

        $generated = 0;
        $skipped = 0;

        foreach ($files as $file) {
            $json = json_decode(File::get($file->getPathname()), true);

            if (! $json) {
                $this->warn("Skipping unreadable file: {$file->getFilename()}");

                continue;
            }

            $lessonId = $json['id'];
            $lessonAudioDir = "{$audioBaseDir}/lesson-{$lessonId}";
            File::ensureDirectoryExists($lessonAudioDir);

            $this->info("Lesson {$lessonId} ({$file->getFilename()})");

            $items = [];
            if (! empty($json['kaiwa'])) {
                $items[] = ['ref' => &$json['kaiwa'], 'label' => 'kaiwa'];
            }
            // Reference the real element inside $json (NOT a foreach-by-reference
            // over `$json['questions'] ?? []`, which iterates a temporary copy and
            // silently drops the "audio" path we write back).
            foreach (array_keys($json['questions'] ?? []) as $i) {
                $items[] = ['ref' => &$json['questions'][$i], 'label' => $json['questions'][$i]['id'] ?? "q{$i}"];
            }

            foreach ($items as $item) {
                $entry = &$item['ref'];
                $label = $item['label'];
                $mp3Path = "{$lessonAudioDir}/{$entry['id']}.mp3";
                $publicPath = "/audio/chokai/lesson-{$lessonId}/{$entry['id']}.mp3";

                if (! $force && File::exists($mp3Path)) {
                    // MP3 already on disk: just make sure the JSON points at it
                    // (no API call, no cost).
                    if (empty($entry['audio'])) {
                        $entry['audio'] = $publicPath;
                        $this->line("  - {$label}: linked existing MP3");
                    } else {
                        $this->line("  - {$label}: skipped (already generated)");
                    }
                    $skipped++;

                    continue;
                }

                if (empty($entry['audio_text'])) {
                    $this->warn("  - {$label}: no audio_text, skipping");

                    continue;
                }

                try {
                    $audio = is_array($entry['audio_text'])
                        ? $tts->synthesizeDialogue($entry['audio_text'], $voices, 'ja-JP', $rate)
                        : $tts->synthesize($entry['audio_text'], $voice, 'ja-JP', $rate);
                    File::put($mp3Path, $audio);
                    $entry['audio'] = $publicPath;
                    $this->line("  - {$label}: generated (".strlen($audio)." bytes)");
                    $generated++;
                }
                catch (\Throwable $e) {
                    $this->error("  - {$label}: FAILED — {$e->getMessage()}");
                }
            }
            unset($entry, $item, $items);

            File::put(
                $file->getPathname(),
                json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n"
            );
        }

        $this->newLine();
        $this->info("Done. Generated: {$generated}, skipped (already had audio): {$skipped}.");

        return self::SUCCESS;
    }
}
