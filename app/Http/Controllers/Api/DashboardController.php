<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LevelResource;
use App\Models\Level;
use App\Services\ProgressService;
use App\Services\XpService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
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

        return response()->json([
            'name' => $user->name,
            'current_level' => $level ? new LevelResource($level) : null,
            'xp' => $xp,
            'player_level' => $this->xpService->levelFromXp($xp),
            'level_progress' => $xp % 100, // matches XpService: 100 XP per level
            'streak' => optional($user->streak)->current_streak ?? 0,
            'daily_goal_target' => $user->daily_goal_target,
            'xp_today' => (int) $user->xpLedger()->whereDate('created_at', today())->sum('amount'),
            'progress' => $this->progressService->summary($user),
            'next_lesson' => $path ? $this->progressService->nextLesson($path) : null,
        ]);
    }
}
