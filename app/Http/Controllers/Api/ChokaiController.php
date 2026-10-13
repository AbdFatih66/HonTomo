<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserChokaiProgress;
use App\Services\NotificationService;
use App\Services\StreakService;
use App\Services\XpService;
use Illuminate\Http\Request;

class ChokaiController extends Controller
{
    public function __construct(
        private XpService $xpService,
        private StreakService $streakService,
        private NotificationService $notificationService,
    ) {}

    /**
     * All of the current user's Chokai progress, as a flat map keyed by
     * set_key — same shape as Mondaishuu's — so the frontend can drop it
     * straight into its `progress` reactive object.
     * GET /api/chokai/progress
     */
    public function index(Request $request)
    {
        $rows = UserChokaiProgress::where('user_id', $request->user()->id)->get();

        $progress = $rows->mapWithKeys(fn (UserChokaiProgress $row) => [
            $row->set_key => ['done' => $row->done, 'crown' => $row->crown],
        ]);

        return response()->json(['progress' => $progress]);
    }

    /**
     * Records the result of finishing one Chokai set (a lesson's mondai
     * 1/2/3). Upserts by (user_id, set_key) since a set can be replayed
     * any number of times. Same XP/notification/streak wiring as
     * Mondaishuu: 10 XP the first time a set is ever finished, +5 more
     * the first time it's finished perfectly (crown) — even if that
     * happens on a later replay — plus a notification for whichever of
     * those actually happened, and a streak tick either way.
     * POST /api/chokai/progress/{setKey}
     */
    public function store(Request $request, string $setKey)
    {
        $validated = $request->validate(['perfect' => 'required|boolean']);

        $user = $request->user();

        $progress = UserChokaiProgress::firstOrCreate([
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
            $this->xpService->award($user, $xp, 'chokai_completed');

            if (! $wasDone) {
                $progress->crown
                    ? $this->notificationService->chokaiMastered($user, $setKey, $xp)
                    : $this->notificationService->chokaiCompleted($user, $setKey, $xp);
            }
            elseif (! $wasCrown && $progress->crown) {
                $this->notificationService->chokaiMastered($user, $setKey, $xp);
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
