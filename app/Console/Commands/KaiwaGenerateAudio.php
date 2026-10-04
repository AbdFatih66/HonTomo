<?php

namespace App\Console\Commands;

use App\Services\GoogleTextToSpeechService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Membuat audio kaiwa (percakapan) lewat Google Cloud Text-to-Speech.
 *
 * Membaca public/data/kaiwa/lesson-*.json. Tiap giliran (`turns[]`) punya `speak`
 * (teks yang dibacakan) dan `who` ("partner" | "you"):
 *  - giliran lawan bicara dibacakan dengan suara `scenario.partner.voice`
 *    ("male" / "female");
 *  - giliran user (contoh yang bisa didengar sebelum ditirukan) dibacakan
 *    dengan suara lawan jenis, supaya mudah dibedakan.
 *
 * MP3 disimpan di public/audio/kaiwa/lesson-{n}/{turn-id}.mp3 dan path-nya ditulis
 * ke field `audio` pada JSON. Idempoten: MP3 yang sudah ada dilewati kecuali --force.
 *
 * Peringatan suara berbahasa Jepang (alerts.json) ikut dibuat saat perintah dijalankan tanpa --lesson.
 *
 * Contoh:
 *   php artisan kaiwa:generate-audio
 *   php artisan kaiwa:generate-audio --lesson=1 --force
 */
class KaiwaGenerateAudio extends Command
{
    protected $signature = 'kaiwa:generate-audio
        {--lesson= : Hanya satu pelajaran, mis. --lesson=1}
        {--voice-male=ja-JP-Wavenet-C : Suara "male"}
        {--voice-female=ja-JP-Wavenet-B : Suara "female"}
        {--rate=0.9 : Kecepatan bicara (0.25 - 4.0)}
        {--force : Buat ulang walau MP3 sudah ada}';

    protected $description = 'Generate kaiwa (conversation practice) audio via Google Cloud Text-to-Speech';

    public function handle(GoogleTextToSpeechService $tts): int
    {
        $dataDir = public_path('data/kaiwa');
        $audioBase = public_path('audio/kaiwa');

        if (! File::isDirectory($dataDir)) {
            $this->error("Folder tidak ada: {$dataDir}");

            return self::FAILURE;
        }

        $voices = ['male' => $this->option('voice-male'), 'female' => $this->option('voice-female')];
        $rate = (float) $this->option('rate');
        $force = (bool) $this->option('force');
        $only = $this->option('lesson');

        $files = collect(File::files($dataDir))
            ->filter(fn ($f) => str_starts_with($f->getFilename(), 'lesson-') && $f->getExtension() === 'json')
            ->when($only, fn ($c) => $c->filter(fn ($f) => $f->getFilename() === "lesson-{$only}.json"));

        if ($files->isEmpty()) {
            $this->warn('Tidak ada berkas pelajaran yang cocok.');

            return self::SUCCESS;
        }

        $generated = 0;
        $skipped = 0;

        foreach ($files as $file) {
            $json = json_decode(File::get($file->getPathname()), true);

            if (! is_array($json) || ! isset($json['id'])) {
                $this->warn("Dilewati (tidak terbaca): {$file->getFilename()}");

                continue;
            }

            $lessonId = $json['id'];
            $dir = "{$audioBase}/lesson-{$lessonId}";
            File::ensureDirectoryExists($dir);
            $this->info("Pelajaran {$lessonId}");

            foreach ($json['scenarios'] ?? [] as $si => $scenario) {
                $partnerVoice = ($scenario['partner']['voice'] ?? 'male') === 'female' ? 'female' : 'male';
                $youVoice = $partnerVoice === 'male' ? 'female' : 'male';

                foreach ($scenario['turns'] ?? [] as $ti => $turn) {
                    $id = $turn['id'];
                    $mp3 = "{$dir}/{$id}.mp3";
                    $public = "/audio/kaiwa/lesson-{$lessonId}/{$id}.mp3";

                    if (! $force && File::exists($mp3)) {
                        $json['scenarios'][$si]['turns'][$ti]['audio'] = $public;
                        $skipped++;
                        $this->line("  - {$id}: dilewati (sudah ada)");

                        continue;
                    }

                    $text = trim($turn['speak'] ?? '');

                    if ($text === '') {
                        $this->warn("  - {$id}: tanpa `speak`, dilewati");

                        continue;
                    }

                    $voice = $voices[($turn['who'] ?? 'partner') === 'you' ? $youVoice : $partnerVoice];

                    try {
                        File::put($mp3, $tts->synthesize($text, $voice, 'ja-JP', $rate));
                        $json['scenarios'][$si]['turns'][$ti]['audio'] = $public;
                        $generated++;
                        $this->line("  - {$id}: dibuat");
                    } catch (\Throwable $e) {
                        $this->error("  - {$id}: GAGAL — {$e->getMessage()}");
                    }
                }
            }

            File::put(
                $file->getPathname(),
                json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n"
            );
        }

        // Peringatan suara (public/data/kaiwa/alerts.json): MP3 di public/audio/kaiwa/alerts/{key}.mp3.
        $alertsFile = "{$dataDir}/alerts.json";

        if (! $only && File::exists($alertsFile)) {
            $alerts = json_decode(File::get($alertsFile), true);

            if (is_array($alerts)) {
                $alertDir = "{$audioBase}/alerts";
                File::ensureDirectoryExists($alertDir);
                $this->info('Peringatan suara (alerts.json)');

                foreach ($alerts as $key => $alert) {
                    $mp3 = "{$alertDir}/{$key}.mp3";
                    $public = "/audio/kaiwa/alerts/{$key}.mp3";

                    if (! $force && File::exists($mp3)) {
                        $alerts[$key]['audio'] = $public;
                        $skipped++;
                        $this->line("  - {$key}: dilewati (sudah ada)");

                        continue;
                    }

                    try {
                        File::put($mp3, $tts->synthesize(trim($alert['speak'] ?? ''), $voices['female'], 'ja-JP', $rate));
                        $alerts[$key]['audio'] = $public;
                        $generated++;
                        $this->line("  - {$key}: dibuat");
                    } catch (\Throwable $e) {
                        $this->error("  - {$key}: GAGAL — {$e->getMessage()}");
                    }
                }

                File::put($alertsFile, json_encode($alerts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
            }
        }

        $this->newLine();
        $this->info("Selesai. Dibuat: {$generated}, dilewati: {$skipped}.");

        return self::SUCCESS;
    }
}
