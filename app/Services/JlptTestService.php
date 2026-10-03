<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserJlptTestAttempt;
use App\Models\UserJlptTestSection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Simulasi ujian JLPT. Semua aturan ujian (urutan sesi, batas waktu,
 * penilaian, lulus/tidak, XP) ada di sini; controller hanya meneruskan.
 *
 * Konsep:
 *  - PACK  = satu paket soal (config/jlpt.php → packs). Bank soal dibaca per
 *            pack; format bank ada dua (classic / mock), lihat config.
 *  - MODE  = 'strict'   → kondisi ujian: timer server, tanpa umpan balik, XP.
 *            'practice' → tanpa timer, umpan balik per soal (check), tanpa XP.
 *
 * Prinsip:
 *  - Waktu (mode strict) dihitung dari started_at di SERVER. Client hanya
 *    menampilkan sisa waktu; refresh/tutup tab tidak mereset timer.
 *  - Kunci jawaban, pembahasan (exp) dan naskah audio tidak pernah keluar dari
 *    server selama sesi berjalan. Mode strict: tidak ada umpan balik sama
 *    sekali. Mode practice: hanya lewat check() untuk SATU soal yang dijawab.
 *  - Pembahasan lengkap (review) baru terbuka setelah tes selesai.
 *  - Skor per sesi baru terlihat setelah sesi dikirim; keputusan lulus
 *    hanya ada di rekap akhir.
 */
class JlptTestService
{
    public const MODE_STRICT = 'strict';

    public const MODE_PRACTICE = 'practice';

    /** @var array<string, array> bank soal yang sudah dibaca, per "pack.sesi" */
    private array $banks = [];

    /** @var array<string, array<string|int, array>> soal datar (id => soal), per "pack.sesi" */
    private array $flat = [];

    public function __construct(
        private XpService $xpService,
        private StreakService $streakService,
        private NotificationService $notificationService,
    ) {}

    // ------------------------------------------------------------------
    // Config helpers
    // ------------------------------------------------------------------

    public function levelConfig(string $level): array
    {
        $cfg = config("jlpt.levels.{$level}");
        abort_if($cfg === null, 404, 'Level tidak dikenal.');

        return $cfg;
    }

    /** Konfigurasi pack (tanpa memeriksa `enabled` — itu urusan assertPackEnabled). */
    public function packConfig(string $pack): array
    {
        $cfg = config("jlpt.packs.{$pack}");
        abort_if($cfg === null, 404, 'Paket tidak dikenal.');

        return $cfg;
    }

    public function assertPackEnabled(string $pack): array
    {
        $cfg = $this->packConfig($pack);
        abort_unless($cfg['enabled'] ?? false, 404, 'Paket tidak tersedia.');

        return $cfg;
    }

    /** Pack `admin_only` (soal asli) hanya boleh dilihat/dipakai admin. */
    public function canAccessPack(User $user, array $packConfig): bool
    {
        return ! ($packConfig['admin_only'] ?? false) || $user->isAdmin();
    }

    /**
     * Pack harus aktif DAN boleh diakses user ini. Pack khusus admin dijawab 404
     * untuk non-admin (sama seperti pack yang tidak ada) supaya keberadaannya tidak bocor.
     */
    public function assertPackAccessible(User $user, string $pack): array
    {
        $cfg = $this->assertPackEnabled($pack);
        abort_unless($this->canAccessPack($user, $cfg), 404, 'Paket tidak tersedia.');

        return $cfg;
    }

    private function sectionConfig(string $level, string $section): array
    {
        $cfg = $this->levelConfig($level)['sections'][$section] ?? null;
        abort_if($cfg === null, 404, 'Sesi tidak dikenal.');

        return $cfg;
    }

    /** Sesi diurutkan menurut `order`. @return array<string, array> */
    private function orderedSections(string $level): array
    {
        $sections = $this->levelConfig($level)['sections'];
        uasort($sections, fn ($a, $b) => $a['order'] <=> $b['order']);

        return $sections;
    }

    private function allSectionsEnabled(string $level): bool
    {
        foreach ($this->levelConfig($level)['sections'] as $s) {
            if (! $s['enabled'])
                return false;
        }

        return true;
    }

    private function isPractice(UserJlptTestAttempt $attempt): bool
    {
        return $attempt->mode === self::MODE_PRACTICE;
    }

    private function deadline(UserJlptTestSection $row, array $cfg): Carbon
    {
        return $row->started_at->copy()->addMinutes($cfg['minutes']);
    }

    // ------------------------------------------------------------------
    // Bank soal (JSON di server: soal + kunci dalam satu berkas)
    // ------------------------------------------------------------------

    /** Bank lengkap DENGAN kunci — hanya untuk dipakai server, jangan dikirim ke client. */
    private function bank(string $pack, string $section): array
    {
        return $this->banks["{$pack}.{$section}"] ??= $this->readBank($pack, $section);
    }

    private function readBank(string $pack, string $section): array
    {
        $p = $this->packConfig($pack);
        $cfg = $this->sectionConfig($p['level'], $section);
        $path = rtrim((string) config('jlpt.data_path'), '/').'/'.trim($p['data_dir'], '/').'/'.$cfg['file'];
        abort_unless(is_file($path), 500, 'Bank soal tidak ditemukan.');

        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }

    private function format(string $pack): string
    {
        return $this->packConfig($pack)['format'] ?? 'classic';
    }

    /**
     * Soal bernilai satu sesi dalam bentuk datar, tiap soal punya `id`,
     * `mondai` (nomor もんだい, 1-based) dan `answer` (1..4). Contoh (例) tidak
     * termasuk. @return array<string|int, array> id => soal
     */
    private function questions(string $pack, string $section): array
    {
        return $this->flat["{$pack}.{$section}"] ??= (function () use ($pack, $section) {
            $bank = $this->bank($pack, $section);
            $list = [];

            if ($this->format($pack) === 'mock') {
                foreach (array_values($bank['mondai']) as $i => $m) {
                    foreach ($m['groups'] as $g) {
                        foreach ($g['questions'] as $q) {
                            $q['mondai'] = $i + 1;
                            $list[] = $q;
                        }
                    }
                }
            }
            else {
                $list = $bank['questions'];
            }

            $byId = [];
            foreach ($list as $q)
                $byId[$q['id']] = $q;

            return $byId;
        })();
    }

    /** @return array<string|int, int> id soal => nomor jawaban benar */
    private function answerKey(string $pack, string $section): array
    {
        return array_map(fn ($q) => (int) $q['answer'], $this->questions($pack, $section));
    }

    /** Jumlah pilihan sebuah soal (batas atas jawaban yang sah). */
    private function choiceCount(array $q): int
    {
        if (isset($q['choices']) && is_array($q['choices']))
            return max(2, count($q['choices']));

        return (int) ($q['choice_count'] ?? 4);
    }

    /** Jumlah soal: dari bank untuk sesi aktif, dari config untuk sesi yang belum dibuat. */
    private function totalQuestions(string $pack, string $section): int
    {
        $cfg = $this->sectionConfig($this->packConfig($pack)['level'], $section);

        return $cfg['enabled']
            ? count($this->questions($pack, $section))
            : (int) $cfg['questions'];
    }

    /**
     * Isi bank untuk browser. `$view`:
     *  - 'exam'     : tanpa kunci, pembahasan, transkrip, dan teks audio.
     *  - 'practice' : sama, tetapi `audio_text` tetap ada (cadangan suara browser
     *                 bila MP3 belum dibuat; mode latihan tidak punya taruhan).
     *  - 'review'   : lengkap dengan kunci, pembahasan, naskah (hanya setelah tes selesai).
     * `answer` pada `example` (contoh yang memang tercetak di lembar soal)
     * selalu ikut. Referensi audio diubah jadi URL publik.
     */
    private function publicTest(string $pack, string $section, string $view): array
    {
        return $this->format($pack) === 'mock'
            ? $this->publicMock($pack, $section, $view)
            : $this->publicClassic($pack, $section, $view);
    }

    private function publicClassic(string $pack, string $section, string $view): array
    {
        $bank = $this->bank($pack, $section);
        $url = fn (int|string|null $ref) => $this->audioUrl($pack, $ref);
        $review = $view === 'review';

        $mondai = $bank['mondai'];
        foreach ($mondai as &$m) {
            foreach (['audio_before', 'audio'] as $field) {
                if (isset($m[$field]))
                    $m[$field] = $url($m[$field]);
            }
            if (isset($m['example'])) {
                if (! $review)
                    unset($m['example']['script']);
                if (isset($m['example']['audio']))
                    $m['example']['audio'] = $url($m['example']['audio']);
            }
        }
        unset($m);

        $out = [
            'format' => 'classic',
            'title' => $bank['title'],
            'mondai' => (object) $mondai,
            'passages' => (object) ($bank['passages'] ?? []),
            'questions' => array_map(function (array $q) use ($url, $review) {
                if (! $review)
                    unset($q['answer'], $q['script'], $q['exp']);
                if (isset($q['audio']))
                    $q['audio'] = $url($q['audio']);

                return $q;
            }, $bank['questions']),
        ];

        // audio di luar もんだい (mis. penjelasan umum & penutup chokai)
        if (isset($bank['audio']))
            $out['audio'] = array_map($url, $bank['audio']);

        return $out;
    }

    private function publicMock(string $pack, string $section, string $view): array
    {
        $bank = $this->bank($pack, $section);
        $url = fn (int|string|null $ref) => $this->audioUrl($pack, $ref);

        // $isQuestion=false → contoh (例): kunci & pembahasannya memang ditampilkan.
        $scrub = function (array $node, bool $isQuestion) use ($url, $view): array {
            if (isset($node['audio']))
                $node['audio'] = $url($node['audio']);
            if ($view !== 'review') {
                unset($node['transcript']);
                if ($isQuestion)
                    unset($node['answer'], $node['exp']);
            }
            if ($view !== 'practice')
                unset($node['audio_text']);

            return $node;
        };

        foreach ($bank['mondai'] as &$m) {
            if (isset($m['intro']))
                $m['intro'] = $scrub($m['intro'], false);
            if (isset($m['example']))
                $m['example'] = $scrub($m['example'], false);
            foreach ($m['groups'] as &$g) {
                foreach ($g['questions'] as &$q)
                    $q = $scrub($q, true);
                unset($q);
            }
            unset($g);
        }
        unset($m);

        return $bank;
    }

    /**
     * Berkas audio untuk sebuah referensi, relatif terhadap folder audio pack
     * (public/{audio_path}/{level}/):
     *  - int    → NOMOR TREK: berkas yang namanya diawali "NN-" (mis. 4 → "04-….mp3").
     *             Pola ini sengaja longgar: sisa nama berkas bebas (ejaan, tanda
     *             pisah) dan urutan nomor = urutan putar rekaman.
     *  - string → nama berkas persis.
     *
     * @return ?string nama berkas (basename), null bila tidak ada
     */
    public function resolveAudio(string $pack, int|string $ref): ?string
    {
        $p = $this->packConfig($pack);
        if (empty($p['audio_path']))
            return null;

        $dir = public_path(trim((string) $p['audio_path'], '/').'/'.strtolower($p['level']));

        if (is_string($ref))
            return is_file("{$dir}/{$ref}") ? $ref : null;

        // scandir (bukan glob) supaya aman untuk path Windows/Laragon; hasilnya terurut
        // abjad sehingga bila ada dua berkas berawalan sama yang dipakai selalu yang sama.
        $prefix = sprintf('%02d-', $ref);
        foreach (is_dir($dir) ? scandir($dir) : [] as $name) {
            if (str_starts_with($name, $prefix) && is_file("{$dir}/{$name}"))
                return $name;
        }

        return null;
    }

    /**
     * URL publik untuk referensi audio. Path absolut ("/audio/…", dipakai pack
     * mock) dikembalikan apa adanya. Bila berkas pack classic belum ada, tetap
     * dikembalikan URL yang berawalan nomor trek ("…/04-tidak-ditemukan.mp3")
     * agar pemutar menampilkan pesan yang jelas alih-alih diam.
     */
    private function audioUrl(string $pack, int|string|null $ref): ?string
    {
        if ($ref === null)
            return null;
        if (is_string($ref) && str_starts_with($ref, '/'))
            return $ref;

        $p = $this->packConfig($pack);
        if (empty($p['audio_path']))
            return is_string($ref) ? $ref : null;

        $base = rtrim((string) $p['audio_path'], '/').'/'.strtolower($p['level']).'/';
        $file = $this->resolveAudio($pack, $ref)
            ?? (is_int($ref) ? sprintf('%02d-tidak-ditemukan.mp3', $ref) : $ref);

        return $base.rawurlencode($file);
    }

    // ------------------------------------------------------------------
    // Attempt lifecycle
    // ------------------------------------------------------------------

    public function currentAttempt(User $user, string $pack): ?UserJlptTestAttempt
    {
        return UserJlptTestAttempt::where('user_id', $user->id)
            ->where('pack', $pack)
            ->where('status', UserJlptTestAttempt::STATUS_IN_PROGRESS)
            ->latest('id')
            ->first();
    }

    /**
     * Mulai tes baru, atau kembalikan tes yang masih berjalan. Mode tes yang
     * sedang berjalan tidak bisa diganti: user harus menyelesaikan/membatalkannya.
     */
    public function startAttempt(User $user, string $pack, string $mode = self::MODE_STRICT): UserJlptTestAttempt
    {
        abort_unless(in_array($mode, [self::MODE_STRICT, self::MODE_PRACTICE], true), 422, 'Mode tidak dikenal.');
        $p = $this->assertPackAccessible($user, $pack);
        $this->levelConfig($p['level']);

        $current = $this->currentAttempt($user, $pack);
        if ($current !== null) {
            abort_if($current->mode !== $mode, 409, 'Selesaikan atau batalkan tes yang sedang berjalan terlebih dahulu.');

            return $current;
        }

        return UserJlptTestAttempt::create([
            'user_id' => $user->id,
            'level' => $p['level'],
            'pack' => $pack,
            'mode' => $mode,
            'status' => UserJlptTestAttempt::STATUS_IN_PROGRESS,
        ]);
    }

    public function abandon(UserJlptTestAttempt $attempt): void
    {
        if ($attempt->status === UserJlptTestAttempt::STATUS_IN_PROGRESS)
            $attempt->update(['status' => UserJlptTestAttempt::STATUS_ABANDONED]);
    }

    /**
     * Mulai satu sesi. Urutan ujian dijaga: sesi hanya bisa dimulai bila
     * semua sesi aktif sebelumnya sudah dikirim. Memanggil ulang untuk
     * sesi yang sudah berjalan mengembalikan sesi itu apa adanya (timer
     * tidak direset).
     */
    public function startSection(UserJlptTestAttempt $attempt, string $section): UserJlptTestSection
    {
        $this->assertInProgress($attempt);
        $cfg = $this->sectionConfig($attempt->level, $section);
        abort_unless($cfg['enabled'], 422, 'Sesi ini belum tersedia.');

        return DB::transaction(function () use ($attempt, $section) {
            $row = UserJlptTestSection::firstOrCreate(['attempt_id' => $attempt->id, 'section' => $section]);

            if ($row->isSubmitted())
                abort(409, 'Sesi ini sudah diselesaikan.');

            if ($row->started_at !== null)
                return $row;

            foreach ($this->orderedSections($attempt->level) as $key => $other) {
                if ($key === $section)
                    break;
                if (! $other['enabled'])
                    continue;

                $done = UserJlptTestSection::where('attempt_id', $attempt->id)
                    ->where('section', $key)->whereNotNull('submitted_at')->exists();
                abort_unless($done, 422, 'Selesaikan sesi sebelumnya terlebih dahulu.');
            }

            $row->update(['started_at' => now()]);

            return $row->refresh();
        });
    }

    /** Simpan jawaban sementara (autosave). Mode strict: diabaikan bila waktu sudah lewat. */
    public function saveAnswers(UserJlptTestAttempt $attempt, string $section, array $answers): UserJlptTestSection
    {
        $this->assertInProgress($attempt);
        $cfg = $this->sectionConfig($attempt->level, $section);
        $row = $this->runningRow($attempt, $section);

        $open = $this->isPractice($attempt)
            || now()->lessThanOrEqualTo($this->deadline($row, $cfg)->addSeconds(config('jlpt.submit_grace_seconds')));

        if ($open)
            $row->update(['answers' => $this->sanitize($attempt->pack, $section, $answers)]);

        return $row;
    }

    /**
     * Umpan balik SATU soal (hanya mode practice): benar/salah, kunci, pembahasan.
     * Mode strict tidak boleh memakai ini — itu akan membocorkan kunci ujian.
     *
     * @return array{correct: bool, answer: int, exp: ?string}
     */
    public function check(UserJlptTestAttempt $attempt, string $section, string|int $questionId, int $choice): array
    {
        abort_unless($this->isPractice($attempt), 403, 'Umpan balik hanya tersedia di mode latihan.');
        $this->assertInProgress($attempt);
        $this->sectionConfig($attempt->level, $section);
        $this->runningRow($attempt, $section);

        $q = $this->questions($attempt->pack, $section)[$questionId] ?? null;
        abort_if($q === null, 422, 'Soal tidak dikenal.');
        abort_unless($choice >= 1 && $choice <= $this->choiceCount($q), 422, 'Pilihan tidak valid.');

        return [
            'correct' => $choice === (int) $q['answer'],
            'answer' => (int) $q['answer'],
            'exp' => $q['exp'] ?? null,
        ];
    }

    /**
     * Kirim & nilai sesi. Mode strict: jawaban yang datang setelah waktu habis +
     * toleransi tidak dipakai — yang dinilai hanya jawaban autosave terakhir.
     */
    public function submitSection(UserJlptTestAttempt $attempt, string $section, array $answers): UserJlptTestSection
    {
        $this->assertInProgress($attempt);
        $cfg = $this->sectionConfig($attempt->level, $section);
        $practice = $this->isPractice($attempt);

        return DB::transaction(function () use ($attempt, $section, $cfg, $answers, $practice) {
            // Kunci baris sesi: dua submit bersamaan tidak boleh sama-sama lolos
            // (nilai/XP ganda). Yang kedua menunggu, lalu kena 409.
            $row = $this->runningRow($attempt, $section, lock: true);
            $now = now();

            $late = false;
            $timedOut = false;
            if (! $practice) {
                $deadline = $this->deadline($row, $cfg);
                $late = $now->greaterThan($deadline->copy()->addSeconds(config('jlpt.submit_grace_seconds')));
                $timedOut = $now->greaterThan($deadline);
            }

            $final = $late ? ($row->answers ?? []) : $this->sanitize($attempt->pack, $section, $answers);

            [$correct, $total, $breakdown] = $this->grade($attempt->pack, $section, $final);

            $spent = (int) $row->started_at->diffInSeconds($now, true);

            $row->update([
                'answers' => $final,
                'score' => $correct,
                'total' => $total,
                'breakdown' => $breakdown,
                'submitted_at' => $now,
                'timed_out' => $timedOut,
                'time_spent_seconds' => $practice ? $spent : min($spent, $cfg['minutes'] * 60),
            ]);

            $this->streakService->recordActivity($attempt->user);

            // Semua sesi aktif sudah dikirim → tes selesai, rekap terbuka.
            if ($this->isFinished($attempt)) {
                $attempt->update(['status' => UserJlptTestAttempt::STATUS_COMPLETED]);
                $this->awardCompletion($attempt);
            }

            return $row->refresh();
        });
    }

    /**
     * XP bertahap per pack, hanya mode strict: xp_completed saat pack pertama kali
     * selesai, +xp_passed saat pertama kali LULUS (bisa di attempt berikutnya).
     * Attempt hasil migrasi dari Simulasi JLPT ikut dihitung sebagai riwayat.
     */
    private function awardCompletion(UserJlptTestAttempt $attempt): void
    {
        if ($this->isPractice($attempt))
            return;

        $user = $attempt->user;
        $previous = UserJlptTestAttempt::where('user_id', $attempt->user_id)
            ->where('pack', $attempt->pack)
            ->where('mode', self::MODE_STRICT)
            ->where('status', UserJlptTestAttempt::STATUS_COMPLETED)
            ->where('id', '!=', $attempt->id)
            ->get();

        $passedNow = $this->recap($attempt)['passed'] === true;
        $hadPass = $passedNow && $previous->contains(fn ($a) => $this->recap($a)['passed'] === true);

        $xp = 0;
        if ($previous->isEmpty())
            $xp += (int) config('jlpt.xp_completed');
        if ($passedNow && ! $hadPass)
            $xp += (int) config('jlpt.xp_passed');

        if ($xp <= 0)
            return;

        $this->xpService->award($user, $xp, 'jlpt_test_completed', $attempt->id);

        $passedNow && ! $hadPass
            ? $this->notificationService->jlptTestPassed($user, $attempt->pack, $xp)
            : $this->notificationService->jlptTestCompleted($user, $attempt->pack, $xp);
    }

    private function assertInProgress(UserJlptTestAttempt $attempt): void
    {
        abort_unless($attempt->status === UserJlptTestAttempt::STATUS_IN_PROGRESS, 409, 'Tes ini sudah tidak aktif.');
    }

    private function runningRow(UserJlptTestAttempt $attempt, string $section, bool $lock = false): UserJlptTestSection
    {
        $query = UserJlptTestSection::where('attempt_id', $attempt->id)->where('section', $section);
        if ($lock)
            $query->lockForUpdate();
        $row = $query->first();

        if ($row === null || $row->started_at === null)
            throw new HttpException(422, 'Sesi belum dimulai.');
        if ($row->isSubmitted())
            throw new HttpException(409, 'Sesi ini sudah diselesaikan.');

        return $row;
    }

    // ------------------------------------------------------------------
    // Grading
    // ------------------------------------------------------------------

    /** Buang id soal yang tidak dikenal & nilai di luar 1..jumlah pilihan soal itu. */
    private function sanitize(string $pack, string $section, array $answers): array
    {
        $questions = $this->questions($pack, $section);
        $out = [];

        foreach ($answers as $id => $choice) {
            if (! isset($questions[$id]))
                continue;
            if (is_numeric($choice) && (int) $choice >= 1 && (int) $choice <= $this->choiceCount($questions[$id]))
                $out[$id] = (int) $choice;
        }

        return $out;
    }

    /** @return array{0:int,1:int,2:array} [benar, total, rincian per もんだい] */
    private function grade(string $pack, string $section, array $answers): array
    {
        $questions = $this->questions($pack, $section);
        $correct = 0;
        $byMondai = [];

        foreach ($questions as $q) {
            $m = (int) $q['mondai'];
            $byMondai[$m] ??= ['mondai' => $m, 'correct' => 0, 'total' => 0, 'unanswered' => 0];
            $byMondai[$m]['total']++;

            $given = $answers[$q['id']] ?? null;

            if ($given === null)
                $byMondai[$m]['unanswered']++;
            elseif ((int) $given === (int) $q['answer']) {
                $correct++;
                $byMondai[$m]['correct']++;
            }
        }

        ksort($byMondai);

        return [$correct, count($questions), array_values($byMondai)];
    }

    // ------------------------------------------------------------------
    // State / recap payloads
    // ------------------------------------------------------------------

    /** Daftar pack yang aktif beserta ringkasan per user (untuk halaman pilih paket). */
    public function packs(User $user): array
    {
        $out = [];

        foreach (config('jlpt.packs', []) as $key => $p) {
            if (! ($p['enabled'] ?? false) || ! $this->canAccessPack($user, $p))
                continue;

            $attempt = $this->currentAttempt($user, $key);
            $completed = UserJlptTestAttempt::where('user_id', $user->id)
                ->where('pack', $key)
                ->where('status', UserJlptTestAttempt::STATUS_COMPLETED)
                ->get();

            // skor terbaik hanya dari mode strict (latihan tidak dihitung)
            $best = null;
            foreach ($completed->where('mode', self::MODE_STRICT) as $a) {
                $r = $this->recap($a);
                if ($best === null || $r['total_score'] > $best['total_score'])
                    $best = ['total_score' => $r['total_score'], 'total_max' => $r['total_max'], 'passed' => $r['passed']];
            }

            $out[] = [
                'key' => $key,
                'level' => $p['level'],
                'format' => $p['format'] ?? 'classic',
                'title' => $p['title'],
                'admin_only' => (bool) ($p['admin_only'] ?? false),
                'sections' => $this->sectionSummaries($key),
                'in_progress' => $attempt ? ['attempt_id' => $attempt->id, 'mode' => $attempt->mode] : null,
                'attempts' => $completed->count(),
                'best' => $best,
            ];
        }

        return $out;
    }

    /** @return list<array{key:string, minutes:int, total:int, enabled:bool}> */
    private function sectionSummaries(string $pack): array
    {
        $level = $this->packConfig($pack)['level'];
        $rows = [];

        foreach ($this->orderedSections($level) as $key => $s) {
            $rows[] = [
                'key' => $key,
                'minutes' => $s['minutes'],
                'total' => $this->totalQuestions($pack, $key),
                'enabled' => $s['enabled'],
            ];
        }

        return $rows;
    }

    /** Status tes untuk halaman utama sebuah pack (tanpa skor sesi yang belum selesai & tanpa kunci). */
    public function state(User $user, string $pack): array
    {
        $p = $this->assertPackAccessible($user, $pack);
        $level = $p['level'];
        $cfg = $this->levelConfig($level);
        $attempt = $this->currentAttempt($user, $pack);
        $rows = $attempt ? $attempt->sections()->get()->keyBy('section') : collect();
        $practice = $attempt !== null && $this->isPractice($attempt);

        $sections = [];
        $previousDone = true;

        foreach ($this->orderedSections($level) as $key => $s) {
            $row = $rows->get($key);

            $status = ! $s['enabled'] ? 'unavailable'
                : ($row?->isSubmitted() ? 'done'
                : ($row?->isRunning() ? 'running'
                : 'ready'));

            $sections[] = [
                'key' => $key,
                'minutes' => $s['minutes'],
                'total' => $this->totalQuestions($pack, $key),
                'enabled' => $s['enabled'],
                'status' => $status,
                // sesi 'ready' baru boleh dimulai bila sesi sebelumnya beres; sesi 'running'
                // harus selalu bisa DILANJUTKAN (user keluar/refresh di tengah ujian) —
                // startSection() mengembalikan sesi berjalan apa adanya, timer tidak direset.
                'can_start' => $attempt !== null && $s['enabled'] && in_array($status, ['ready', 'running'], true) && $previousDone,
                'remaining_seconds' => $status === 'running' && ! $practice ? $this->remaining($row, $s) : null,
            ];

            if ($s['enabled'] && $status !== 'done')
                $previousDone = false;
        }

        return [
            'pack' => $pack,
            'level' => $level,
            'format' => $p['format'] ?? 'classic',
            'title' => $p['title'],
            'admin_only' => (bool) ($p['admin_only'] ?? false),
            'pass_total' => $cfg['pass_total'],
            'all_sections_available' => $this->allSectionsEnabled($level),
            'attempt_id' => $attempt?->id,
            'mode' => $attempt?->mode,
            'sections' => $sections,
            'history' => $this->history($user, $pack),
        ];
    }

    private function remaining(UserJlptTestSection $row, array $cfg): int
    {
        return max(0, (int) now()->diffInSeconds($this->deadline($row, $cfg), false));
    }

    /** Info sesi yang sedang berjalan (untuk halaman ujian). Tidak berisi kunci. */
    public function sectionPayload(UserJlptTestSection $row, UserJlptTestAttempt $attempt, bool $withTest = false): array
    {
        $cfg = $this->sectionConfig($attempt->level, $row->section);
        $practice = $this->isPractice($attempt);

        return [
            'attempt_id' => $attempt->id,
            'pack' => $attempt->pack,
            'level' => $attempt->level,
            'mode' => $attempt->mode,
            'format' => $this->format($attempt->pack),
            'section' => $row->section,
            'minutes' => $cfg['minutes'],
            // mode latihan tidak punya batas waktu
            'remaining_seconds' => $practice ? null : ($row->isRunning() ? $this->remaining($row, $cfg) : 0),
            'answers' => $row->isRunning() ? ($row->answers ?? new \stdClass) : null,
            'submitted' => $row->isSubmitted(),
            // Soal dikirim hanya saat sesi berjalan (start / lanjut setelah refresh), tanpa kunci.
            'test' => $withTest && $row->isRunning()
                ? $this->publicTest($attempt->pack, $row->section, $practice ? 'practice' : 'exam')
                : null,
        ];
    }

    public function isFinished(UserJlptTestAttempt $attempt): bool
    {
        $enabledKeys = collect($this->levelConfig($attempt->level)['sections'])
            ->filter(fn ($s) => $s['enabled'])->keys();

        $submitted = $attempt->sections()->whereNotNull('submitted_at')->pluck('section');

        return $enabledKeys->isNotEmpty() && $enabledKeys->diff($submitted)->isEmpty();
    }

    /** Rekap nilai. Hanya untuk tes yang semua sesi aktifnya sudah dikirim. */
    public function recap(UserJlptTestAttempt $attempt): array
    {
        abort_unless(
            $attempt->status === UserJlptTestAttempt::STATUS_COMPLETED,
            409,
            'Rekap tersedia setelah semua sesi selesai.',
        );

        $level = $attempt->level;
        $pack = $attempt->pack;
        $cfg = $this->levelConfig($level);
        $rows = $attempt->sections()->whereNotNull('submitted_at')->get()->keyBy('section');
        // Lengkap = SEMUA sesi level ini sudah dikerjakan di tes ini. Tes yang
        // dikerjakan sebelum semua sesi dibuat selamanya tetap 'sementara'.
        $complete = $rows->count() === count($cfg['sections']);

        $sections = [];
        foreach ($this->orderedSections($level) as $key => $s) {
            $row = $rows->get($key);
            $sections[] = [
                'key' => $key,
                'available' => $s['enabled'],
                'correct' => $row?->score,
                'total' => $row?->total ?? $this->totalQuestions($pack, $key),
                'breakdown' => $row?->breakdown ?? [],
                'timed_out' => $row?->timed_out ?? false,
                'time_spent_seconds' => $row?->time_spent_seconds,
                'minutes' => $s['minutes'],
            ];
        }

        $groups = [];
        $totalScore = 0;
        $allMinMet = true;

        foreach ($cfg['groups'] as $gKey => $g) {
            $correct = 0;
            $count = 0;
            foreach ($g['sections'] as $sKey) {
                $count += $this->totalQuestions($pack, $sKey);
                $correct += $rows->get($sKey)?->score ?? 0;
            }

            $score = $count > 0 ? (int) round($g['max'] * $correct / $count) : 0;
            $met = $score >= $g['min'];
            $allMinMet = $allMinMet && $met;
            $totalScore += $score;

            $groups[] = [
                'key' => $gKey,
                'score' => $score,
                'max' => $g['max'],
                'min' => $g['min'],
                'met_minimum' => $met,
                'sections' => $g['sections'],
                'available' => collect($g['sections'])->every(fn ($k) => $cfg['sections'][$k]['enabled']),
            ];
        }

        $finishedAt = $rows->max('submitted_at');

        return [
            'attempt_id' => $attempt->id,
            'pack' => $pack,
            'pack_title' => $this->packConfig($pack)['title'] ?? null,
            'mode' => $attempt->mode,
            'level' => $level,
            // Attempt hasil migrasi Simulasi JLPT: hanya skor, tanpa jawaban per soal.
            'legacy' => $attempt->legacy_mock_id !== null,
            'legacy_duration_seconds' => $attempt->legacy_duration_seconds,
            'review_available' => $attempt->legacy_mock_id === null,
            // Untuk sertifikat simulasi HonTomo (bukan sertifikat resmi JLPT).
            'participant' => $attempt->user?->name,
            'certificate_no' => sprintf(
                'HT-%s-%s-%06d',
                $level,
                ($finishedAt ? Carbon::parse($finishedAt) : now())->format('Ymd'),
                $attempt->id,
            ),
            // Selama masih ada sesi yang belum dibuat, angka di bawah hanya
            // gambaran sementara dan TIDAK ada keputusan lulus/tidak.
            'provisional' => ! $complete,
            'passed' => $complete ? ($totalScore >= $cfg['pass_total'] && $allMinMet) : null,
            'total_score' => $totalScore,
            'total_max' => $cfg['total_max'],
            'pass_total' => $cfg['pass_total'],
            'groups' => $groups,
            'sections' => $sections,
            'finished_at' => $finishedAt,
        ];
    }

    /**
     * Pembahasan lengkap: soal + KUNCI + pembahasan + naskah audio + jawaban user.
     * Hanya untuk tes yang sudah selesai (kunci tidak boleh bocor di tengah ujian)
     * dan bukan hasil migrasi (yang tidak menyimpan jawaban).
     */
    public function review(UserJlptTestAttempt $attempt): array
    {
        abort_unless(
            $attempt->status === UserJlptTestAttempt::STATUS_COMPLETED,
            409,
            'Pembahasan tersedia setelah semua sesi selesai.',
        );
        abort_if($attempt->legacy_mock_id !== null, 404, 'Riwayat lama tidak menyimpan jawaban per soal.');

        $pack = $attempt->pack;
        $rows = $attempt->sections()->whereNotNull('submitted_at')->get()->keyBy('section');

        $sections = [];
        foreach ($this->orderedSections($attempt->level) as $key => $s) {
            $row = $rows->get($key);
            if (! $s['enabled'] || $row === null)
                continue;

            $sections[] = [
                'key' => $key,
                'correct' => $row->score,
                'total' => $row->total,
                'answers' => $row->answers ?? new \stdClass,
                'test' => $this->publicTest($pack, $key, 'review'),
            ];
        }

        return [
            'attempt_id' => $attempt->id,
            'pack' => $pack,
            'pack_title' => $this->packConfig($pack)['title'] ?? null,
            'mode' => $attempt->mode,
            'format' => $this->format($pack),
            'sections' => $sections,
        ];
    }

    /** 5 tes terakhir yang sudah selesai pada sebuah pack (ringkas). */
    private function history(User $user, string $pack): array
    {
        return UserJlptTestAttempt::where('user_id', $user->id)
            ->where('pack', $pack)
            ->where('status', UserJlptTestAttempt::STATUS_COMPLETED)
            ->latest('id')->limit(5)->get()
            ->map(function ($a) {
                $r = $this->recap($a);

                return [
                    'attempt_id' => $a->id,
                    'mode' => $a->mode,
                    'legacy' => $r['legacy'],
                    'total_score' => $r['total_score'],
                    'total_max' => $r['total_max'],
                    'passed' => $r['passed'],
                    'provisional' => $r['provisional'],
                    'finished_at' => $r['finished_at'],
                ];
            })->values()->all();
    }

    public function findOwned(User $user, int $attemptId): UserJlptTestAttempt
    {
        $attempt = UserJlptTestAttempt::where('user_id', $user->id)->findOrFail($attemptId);

        // Attempt pada pack khusus admin tidak boleh diakses lagi bila user bukan (lagi) admin.
        $pack = config("jlpt.packs.{$attempt->pack}");
        abort_if($pack !== null && ! $this->canAccessPack($user, $pack), 404);

        return $attempt;
    }
}
