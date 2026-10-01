<?php

namespace App\Console\Commands;

use App\Services\JlptTestService;
use Illuminate\Console\Command;

/**
 * Memeriksa berkas audio Tes JLPT (sesi chokai) pada pack bergaya classic: tiap
 * nomor trek yang dipakai bank soal harus punya berkas berawalan "NN-" di
 * public/<pack.audio_path>/<level>. Juga menampilkan berkas di folder yang TIDAK
 * dipakai. (Pack bergaya mock memakai MP3 per soal; periksa dengan
 * `php artisan jlpt-mock:generate-audio --dry-run`.)
 *
 *   php artisan jlpt:audio-check
 *   php artisan jlpt:audio-check --pack=private
 *
 * Kode keluar 1 bila ada trek yang belum punya berkas.
 */
class JlptAudioCheck extends Command
{
    protected $signature = 'jlpt:audio-check {--pack=private : Pack (format classic) yang diperiksa}';

    protected $description = 'Cek berkas audio sesi chokai Tes JLPT (yang hilang & yang tidak terpakai)';

    public function handle(JlptTestService $service): int
    {
        $pack = (string) $this->option('pack');
        $packCfg = config("jlpt.packs.{$pack}");
        if (! $packCfg || ($packCfg['format'] ?? 'classic') !== 'classic' || empty($packCfg['audio_path'])) {
            $this->error("Pack '{$pack}' tidak ada, atau bukan pack bergaya classic dengan folder audio.");

            return self::FAILURE;
        }

        $level = $packCfg['level'];
        $dir = public_path(trim((string) $packCfg['audio_path'], '/').'/'.strtolower($level));
        $this->line("Folder audio : {$dir}");

        if (! is_dir($dir)) {
            $this->error('Folder audio belum ada.');

            return self::FAILURE;
        }

        $cfg = config("jlpt.levels.{$level}.sections.chokai");
        if (! $cfg || ! is_file($file = rtrim((string) config('jlpt.data_path'), '/').'/'.trim($packCfg['data_dir'], '/').'/'.$cfg['file'])) {
            $this->error("Bank soal chokai untuk {$level} tidak ditemukan.");

            return self::FAILURE;
        }
        $bank = json_decode((string) file_get_contents($file), true);

        // kumpulkan (label => nomor trek) sesuai urutan putar
        $refs = ['Penjelasan umum' => $bank['audio']['intro'] ?? null];
        foreach ($bank['mondai'] as $no => $m) {
            if (isset($m['audio_before']))
                $refs["もんだい {$no} — istirahat"] = $m['audio_before'];
            $refs["もんだい {$no} — petunjuk"] = $m['audio'] ?? null;
            $refs["もんだい {$no} — れい"] = $m['example']['audio'] ?? null;
            foreach ($bank['questions'] as $q) {
                if ((int) $q['mondai'] === (int) $no)
                    $refs["もんだい {$no} — {$q['no']}番"] = $q['audio'] ?? null;
            }
        }
        $refs['Penutup'] = $bank['audio']['outro'] ?? null;

        $rows = [];
        $used = [];
        $missing = 0;
        foreach ($refs as $label => $ref) {
            if ($ref === null)
                continue;
            $name = $service->resolveAudio($pack, $ref);
            $rows[] = [is_int($ref) ? sprintf('%02d', $ref) : $ref, $label, $name ?? '— TIDAK ADA —'];
            $name === null ? $missing++ : $used[$name] = true;
        }
        usort($rows, fn ($a, $b) => strnatcmp($a[0], $b[0]));
        $this->table(['Trek', 'Isi', 'Berkas'], $rows);

        $unused = collect(scandir($dir) ?: [])
            ->filter(fn ($n) => is_file("{$dir}/{$n}") && preg_match('/\.(mp3|m4a|ogg|wav|aac)$/i', $n))
            ->reject(fn ($n) => isset($used[$n]))
            ->sort()->values();

        if ($unused->isNotEmpty()) {
            $this->newLine();
            $this->warn('Berkas di folder yang tidak dipakai soal mana pun:');
            $unused->each(fn ($n) => $this->line("  {$n}"));
        }

        $this->newLine();
        if ($missing > 0) {
            $this->error("{$missing} trek belum punya berkas. Pastikan nama berkas berawalan nomor trek (mis. 04-….mp3).");

            return self::FAILURE;
        }
        $this->info('Semua trek audio ditemukan.');

        return self::SUCCESS;
    }
}
