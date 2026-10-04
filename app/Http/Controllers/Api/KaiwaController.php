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
}
