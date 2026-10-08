<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserKaiwaProgress;
use App\Services\NotificationService;
use App\Services\StreakService;
use App\Services\XpService;
use Illuminate\Http\Request;

class KaiwaController extends Controller
{
    public function __construct(
        private XpService $xpService,
        private StreakService $streakService,
        private NotificationService $notificationService,
    ) {}

    /**
     * Seluruh progres Kaiwa user, peta datar per set_key (id skenario) —
     * bentuk sama dengan Chokai.
     * GET /api/kaiwa/progress
     */
    public function index(Request $request)
    {
        $rows = UserKaiwaProgress::where('user_id', $request->user()->id)->get();

        $progress = $rows->mapWithKeys(fn (UserKaiwaProgress $row) => [
            $row->set_key => ['done' => $row->done, 'crown' => $row->crown],
        ]);

        return response()->json(['progress' => $progress]);
    }

    /**
     * Catat hasil menamatkan satu skenario. Upsert per (user_id, set_key)
     * karena skenario boleh diulang. XP seperti Chokai: 10 XP saat pertama
     * kali selesai, +5 saat pertama kali selesai sempurna (semua giliran
     * lolos pada percobaan pertama) — walau terjadi pada ulangan berikutnya.
     * POST /api/kaiwa/progress/{setKey}
     */
    public function store(Request $request, string $setKey)
    {
        $validated = $request->validate(['perfect' => 'required|boolean']);

        // Hanya id skenario yang benar-benar ada di materi; tanpa ini format `l99-s1`
        // yang valid menurut regex route bisa dipakai menimbun XP dengan kunci karangan.
        // Dua sumber sah: pelajaran (index.json → lessons, berkunci) dan paket situasi
        // (index.json → situations, mis. `mensetsu-s3`, bebas kunci).
        $lessons = $this->lessonScenarioIds();
        $lessonId = $this->lessonOf($setKey, $lessons);
        abort_if($lessonId === null && ! in_array($setKey, $this->situationScenarioIds(), true), 404);

        $user = $request->user();

        $progress = UserKaiwaProgress::firstOrCreate([
            'user_id' => $user->id,
            'set_key' => $setKey,
        ]);

        $wasDone = $progress->done;
        $wasCrown = $progress->crown;

        $progress->applyResult($validated['perfect']);

        $xp = 0;
        if (! $wasDone)
            $xp += 10;
        if (! $wasCrown && $progress->crown)
            $xp += 5;

        if ($xp > 0) {
            $this->xpService->award($user, $xp, 'kaiwa_completed');

            if (! $wasDone) {
                $progress->crown
                    ? $this->notificationService->kaiwaMastered($user, $setKey, $xp)
                    : $this->notificationService->kaiwaCompleted($user, $setKey, $xp);
            }
            elseif (! $wasCrown && $progress->crown) {
                $this->notificationService->kaiwaMastered($user, $setKey, $xp);
            }
        }

        $this->streakService->recordActivity($user);

        return response()->json([
            'set_key' => $progress->set_key,
            'done' => $progress->done,
            'crown' => $progress->crown,
            'xp' => $xp,
        ]);
    }

    /**
     * Id skenario per pelajaran menurut urutan index.json: [idPelajaran => [id skenario, ...]].
     * Dibaca langsung dari public/data/kaiwa (±100 KB, hanya saat skenario tamat) supaya
     * materi baru langsung sah tanpa cache yang basi.
     *
     * @return array<int, list<string>>
     */
    private function lessonScenarioIds(): array
    {
        $dir = public_path('data/kaiwa');
        $manifest = json_decode((string) @file_get_contents("{$dir}/index.json"), true);
        $lessons = [];

        foreach ($manifest['lessons'] ?? [] as $entry) {
            $lesson = json_decode((string) @file_get_contents("{$dir}/".basename((string) ($entry['file'] ?? ''))), true);

            $lessons[(int) ($entry['id'] ?? 0)] = array_map(
                fn ($scenario) => (string) ($scenario['id'] ?? ''),
                $lesson['scenarios'] ?? [],
            );
        }

        return $lessons;
    }

    /**
     * Id semua skenario paket situasi (index.json → situations), mis. ['mensetsu-s1', ...].
     * Paket situasi tidak berkunci, jadi cukup daftar datar untuk memeriksa keabsahan id.
     *
     * @return list<string>
     */
    private function situationScenarioIds(): array
    {
        $dir = public_path('data/kaiwa');
        $manifest = json_decode((string) @file_get_contents("{$dir}/index.json"), true);
        $ids = [];

        foreach ($manifest['situations'] ?? [] as $entry) {
            $pack = json_decode((string) @file_get_contents("{$dir}/".basename((string) ($entry['file'] ?? ''))), true);

            foreach ($pack['scenarios'] ?? [] as $scenario)
                $ids[] = (string) ($scenario['id'] ?? '');
        }

        return $ids;
    }

    /** @param array<int, list<string>> $lessons */
    private function lessonOf(string $setKey, array $lessons): ?int
    {
        foreach ($lessons as $lessonId => $ids) {
            if (in_array($setKey, $ids, true))
                return $lessonId;
        }

        return null;
    }
}
