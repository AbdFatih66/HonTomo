<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserKaiteOboeruProgress;
use App\Services\NotificationService;
use App\Services\StreakService;
use App\Services\XpService;
use Illuminate\Http\Request;

class KaiteOboeruController extends Controller
{
    public function __construct(
        private XpService $xpService,
        private StreakService $streakService,
        private NotificationService $notificationService,
    ) {}

    /**
     * All of the current user's Kaite Oboeru progress, as a flat map
     * keyed by set_key ({ [key]: { done, crown } }) — same shape as the
     * Mondaishuu endpoint, ready to drop into the frontend's `progress`
     * reactive object.
     * GET /api/kaite-oboeru/progress
     */
    public function index(Request $request)
    {
        $rows = UserKaiteOboeruProgress::where('user_id', $request->user()->id)->get();

        $progress = $rows->mapWithKeys(fn (UserKaiteOboeruProgress $row) => [
            $row->set_key => ['done' => $row->done, 'crown' => $row->crown],
        ]);

        return response()->json(['progress' => $progress]);
    }

    /**
     * Records the result of finishing one writing set. Upserts by
     * (user_id, set_key) since a set can be replayed any number of times.
     * Same XP/notification/streak wiring as Mondaishuu and the Jalur
     * Belajar lessons: 10 XP the first time a set is finished, +5 more
     * the first time it's finished perfectly (crown), a matching
     * notification, and a streak tick.
     * POST /api/kaite-oboeru/progress/{setKey}
     */
    public function store(Request $request, string $setKey)
    {
        $validated = $request->validate(['perfect' => 'required|boolean']);

        $user = $request->user();

        $progress = UserKaiteOboeruProgress::firstOrCreate([
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
            $this->xpService->award($user, $xp, 'kaite_oboeru_completed');

            if (! $wasDone) {
                $progress->crown
                    ? $this->notificationService->kaiteOboeruMastered($user, $setKey, $xp)
                    : $this->notificationService->kaiteOboeruCompleted($user, $setKey, $xp);
            }
            elseif (! $wasCrown && $progress->crown) {
                $this->notificationService->kaiteOboeruMastered($user, $setKey, $xp);
            }
        }

        $this->streakService->recordActivity($user);

        return response()->json([
            'set_key' => $progress->set_key,
            'done' => $progress->done,
            'crown' => $progress->crown,
        ]);
    }
}
