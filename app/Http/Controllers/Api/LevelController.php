<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LevelResource;
use App\Models\Level;
use App\Services\ProgressService;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    public function __construct(private ProgressService $progressService) {}

    public function index()
    {
        return LevelResource::collection(
            Level::where('is_active', true)->orderBy('order')->get()
        );
    }

    public function show(Request $request, Level $level)
    {
        return response()->json($this->progressService->learningPath($request->user(), $level));
    }

    /**
     * Learning path of the user's current level (falls back to the first active level).
     */
    public function path(Request $request)
    {
        $user = $request->user();
        $level = $user->currentLevel
            ?? Level::where('is_active', true)->orderBy('order')->first();

        abort_if(! $level, 404, 'No level configured.');

        return response()->json($this->progressService->learningPath($user, $level));
    }
}
