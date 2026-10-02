<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LevelResource;
use App\Models\Kanji;
use App\Models\KanjiVocabulary;
use App\Models\Level;
use App\Models\User;
use App\Models\UserChokaiProgress;
use App\Models\UserJlptTestAttempt;
use App\Models\UserKaiteOboeruProgress;
use App\Models\UserKanjiProgress;
use App\Models\UserMondaishuuProgress;
use App\Models\UserVocabulary;
use App\Models\Vocabulary;
use App\Services\ProgressService;
use App\Services\XpService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // Jumlah set latihan. Bank soalnya ada di file Vue (bukan di database),
    // jadi totalnya dicatat di sini — samakan bila set latihan bertambah.
    private const MONDAISHUU_SETS = 29; // pelajaran 1–25 + 4 set rangkuman
    private const CHOKAI_SETS = 25;
    private const KAITE_OBOERU_SETS = 25;

    // Kanji hari ini: jumlah kanji per hari & contoh kata per kanji.
    private const KANJI_OF_DAY_COUNT = 5;
    private const KANJI_EXAMPLES = 3;

    public function __construct(
        private ProgressService $progressService,
        private XpService $xpService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $level = $user->currentLevel
            ?? Level::where('is_active', true)->orderBy('order')->first();

        $path = $level ? $this->progressService->learningPath($user, $level) : null;
        $xp = $this->xpService->totalXp($user);
        $streak = $user->streak;

        return response()->json([
            'name' => $user->name,
            'current_level' => $level ? new LevelResource($level) : null,
            'xp' => $xp,
            'player_level' => $this->xpService->levelFromXp($xp),
            'level_progress' => $xp % 100, // matches XpService: 100 XP per level
            'streak' => $streak->current_streak ?? 0,
            'longest_streak' => $streak->longest_streak ?? 0,
            'streak_active_today' => $streak?->last_activity_date !== null
                && Carbon::parse($streak->last_activity_date)->isToday(),
            'daily_goal_target' => $user->daily_goal_target,
            'xp_today' => (int) $user->xpLedger()->whereDate('created_at', today())->sum('amount'),
            'progress' => $this->progressService->summary($user),
            'next_lesson' => $path ? $this->progressService->nextLesson($path) : null,
            'roadmap' => $this->roadmap($user, $path, $level),
            'week' => $this->week($user),
            'review_due' => $this->reviewDue($user),
            'crowns' => $this->crowns($user),
            'recent' => $this->recent($user),
            'kanji_of_day' => $this->kanjiOfDay(),
            'jlpt_exam_date' => $user->jlpt_exam_date?->toDateString(),
            // null = belum pernah tercatat (kunjungan pertama → tanpa notifikasi)
            'seen_badges' => $user->seen_badges,
        ]);
    }

    /**
     * Simpan preferensi dasbor. Kedua field opsional:
     *  - jlpt_exam_date : 'YYYY-MM-DD' (hari ini atau setelahnya) atau null untuk
     *                     kembali ke jadwal resmi.
     *  - seen_badges    : kunci lencana yang sudah dilihat. DIGABUNG dengan data
     *                     yang ada (bukan menimpa), supaya dua perangkat yang
     *                     membuka dasbor bersamaan tidak saling menghapus.
     */
    public function updatePreferences(Request $request)
    {
        $data = $request->validate([
            'jlpt_exam_date' => ['sometimes', 'nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'seen_badges' => ['sometimes', 'array', 'max:100'],
            'seen_badges.*' => ['string', 'max:50'],
        ]);

        $user = $request->user();

        if (array_key_exists('jlpt_exam_date', $data))
            $user->jlpt_exam_date = $data['jlpt_exam_date'];

        if (array_key_exists('seen_badges', $data)) {
            $user->seen_badges = array_values(array_unique([
                ...($user->seen_badges ?? []),
                ...$data['seen_badges'],
            ]));
        }

        $user->save();

        return response()->json([
            'jlpt_exam_date' => $user->jlpt_exam_date?->toDateString(),
            'seen_badges' => $user->seen_badges,
        ]);
    }

    /**
     * Roadmap belajar — urutannya SAMA dengan menu samping:
     * Kana → Belajar → Kosakata → Kanji → Latihan Soal → Latihan Menyimak
     * → Latihan Menulis → Referensi Tata Bahasa → Tes JLPT.
     *
     * Tiap tahap: `done`/`total` (null bila tahap itu tidak punya ukuran
     * progres, mis. Kana & Referensi), `percent`, dan `state`:
     *   done | current | upcoming
     * `current` = tahap terurut pertama yang belum selesai.
     */
    private function roadmap(User $user, ?array $path, ?Level $level): array
    {
        $lessons = $path ? collect($path['units'])->flatMap(fn ($u) => $u['lessons']) : collect();
        $lessonsDone = $lessons->whereIn('status', ['completed', 'mastered'])->count();

        $vocabTotal = Vocabulary::count();
        $vocabMastered = $user->userVocabularies()->where('mastery_level', UserVocabulary::MASTERY_MASTERED)->count();

        $kanjiQuery = Kanji::query();
        if ($level && Kanji::where('jlpt_level', $level->code)->exists())
            $kanjiQuery->where('jlpt_level', $level->code);
        $kanjiTotal = $kanjiQuery->count();
        $kanjiMastered = UserKanjiProgress::where('user_id', $user->id)
            ->where('status', UserKanjiProgress::STATUS_MASTERED)->count();

        $jlptDone = UserJlptTestAttempt::where('user_id', $user->id)
            ->where('status', UserJlptTestAttempt::STATUS_COMPLETED)->count();

        $stages = [
            ['key' => 'kana', 'route' => 'kana', 'icon' => 'tabler-language-katakana', 'done' => null, 'total' => null],
            ['key' => 'learn', 'route' => 'learn', 'icon' => 'tabler-book', 'done' => $lessonsDone, 'total' => $lessons->count()],
            ['key' => 'vocabulary', 'route' => 'kosakata', 'icon' => 'tabler-notebook', 'done' => $vocabMastered, 'total' => $vocabTotal],
            ['key' => 'kanji', 'route' => 'kanji', 'icon' => 'tabler-writing', 'done' => $kanjiMastered, 'total' => $kanjiTotal],
            ['key' => 'mondaishuu', 'route' => 'mondaishuu', 'icon' => 'tabler-pencil-check',
                'done' => UserMondaishuuProgress::where('user_id', $user->id)->where('done', true)->count(), 'total' => self::MONDAISHUU_SETS],
            ['key' => 'chokai', 'route' => 'chokai', 'icon' => 'tabler-headphones',
                'done' => UserChokaiProgress::where('user_id', $user->id)->where('done', true)->count(), 'total' => self::CHOKAI_SETS],
            ['key' => 'kaite_oboeru', 'route' => 'kaite-oboeru', 'icon' => 'tabler-writing-sign',
                'done' => UserKaiteOboeruProgress::where('user_id', $user->id)->where('done', true)->count(), 'total' => self::KAITE_OBOERU_SETS],
            ['key' => 'lampiran', 'route' => 'lampiran', 'icon' => 'tabler-clipboard-list', 'done' => null, 'total' => null],
            ['key' => 'jlpt_test', 'route' => 'jlpt-test', 'icon' => 'tabler-certificate', 'done' => $jlptDone, 'total' => null],
        ];

        $foundDone = $lessonsDone > 0; // Kana dianggap "dilewati" begitu pelajaran pertama dimulai
        $currentFound = false;

        return collect($stages)->map(function (array $s) use (&$currentFound, $foundDone) {
            $hasMeter = $s['total'] !== null && $s['total'] > 0;
            $percent = $hasMeter ? (int) min(100, round($s['done'] / $s['total'] * 100)) : null;

            $isDone = match (true) {
                $s['key'] === 'kana' => $foundDone,
                $hasMeter => $s['done'] >= $s['total'],
                $s['key'] === 'jlpt_test' => $s['done'] > 0,
                default => false, // Referensi: bahan rujukan, tidak pernah "selesai"
            };

            // Referensi tidak menghalangi tahap lain menjadi "current".
            $skippable = $s['key'] === 'lampiran';

            $state = 'upcoming';
            if ($isDone)
                $state = 'done';
            elseif (! $currentFound && ! $skippable) {
                $state = 'current';
                $currentFound = true;
            }

            return $s + ['percent' => $percent, 'state' => $state];
        })->all();
    }

    /** XP per hari untuk 7 hari terakhir (hari ini paling kanan). */
    private function week(User $user): array
    {
        $from = today()->subDays(6);

        $byDay = $user->xpLedger()
            ->where('created_at', '>=', $from)
            ->get(['amount', 'created_at'])
            ->groupBy(fn ($row) => $row->created_at->toDateString())
            ->map(fn ($rows) => (int) $rows->sum('amount'));

        return collect(range(0, 6))->map(function (int $i) use ($from, $byDay) {
            $day = $from->copy()->addDays($i);

            return [
                'date' => $day->toDateString(),
                'weekday' => $day->dayOfWeek, // 0 = Minggu
                'xp' => $byDay[$day->toDateString()] ?? 0,
                'is_today' => $day->isToday(),
            ];
        })->all();
    }

    /** Kosakata yang waktunya diulang (belum pernah diulang atau jadwalnya lewat). */
    private function reviewDue(User $user): int
    {
        return UserVocabulary::where('user_id', $user->id)
            ->where(function ($q) {
                $q->whereNull('next_review_at')->orWhere('next_review_at', '<=', now());
            })->count();
    }

    private function crowns(User $user): int
    {
        return UserMondaishuuProgress::where('user_id', $user->id)->where('crown', true)->count()
            + UserChokaiProgress::where('user_id', $user->id)->where('crown', true)->count()
            + UserKaiteOboeruProgress::where('user_id', $user->id)->where('crown', true)->count();
    }

    /** 5 perolehan XP terbaru. `source` dipetakan ke teks di frontend. */
    private function recent(User $user): array
    {
        return $user->xpLedger()->latest()->limit(5)->get(['amount', 'source', 'created_at'])
            ->map(fn ($row) => [
                'source' => $row->source,
                'amount' => (int) $row->amount,
                'at' => $row->created_at->toIso8601String(),
            ])->all();
    }

    /**
     * Kanji hari ini: KANJI_OF_DAY_COUNT kanji N5/N4 yang berganti setiap hari
     * (deterministik per tanggal, jadi semua user melihat set yang sama pada
     * hari yang sama), masing-masing dengan beberapa contoh kata dari
     * `kanji_vocabulary`. Kanji yang punya contoh kata didahulukan; bila belum
     * ada satu pun, kanji tanpa contoh tetap ditampilkan.
     */
    private function kanjiOfDay(): array
    {
        $pool = Kanji::query()->whereIn('jlpt_level', ['N5', 'N4']);

        $withExamples = (clone $pool)->whereHas('vocabulary');
        $ids = $withExamples->exists()
            ? $withExamples->orderBy('id')->pluck('id')
            : $pool->orderBy('id')->pluck('id');

        if ($ids->isEmpty())
            return [];

        $count = min(self::KANJI_OF_DAY_COUNT, $ids->count());
        $start = (int) floor(today()->timestamp / 86400) * $count % $ids->count();
        $picked = collect(range(0, $count - 1))->map(fn (int $i) => $ids[($start + $i) % $ids->count()]);

        $kanji = Kanji::with(['vocabulary' => fn ($q) => $q->limit(self::KANJI_EXAMPLES)])
            ->whereIn('id', $picked)->get()->keyBy('id');

        return $picked->map(fn ($id) => $kanji[$id])->map(fn (Kanji $k) => [
            'id' => $k->id,
            'character' => $k->character,
            'onyomi' => $k->onyomi ?? [],
            'kunyomi' => $k->kunyomi ?? [],
            'meaning' => $k->meaning(),
            'stroke_count' => $k->stroke_count,
            'jlpt_level' => $k->jlpt_level,
            'examples' => $k->vocabulary->take(self::KANJI_EXAMPLES)->map(fn (KanjiVocabulary $v) => [
                'word' => $v->word,
                'reading' => $v->reading,
                'meaning' => $v->meaning(),
            ])->values()->all(),
        ])->values()->all();
    }
}
