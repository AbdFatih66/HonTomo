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
     * Learning path of the requested level (?level=N5|N4, the Tata Bahasa level
     * selector), else the user's current level (falls back to the first active level).
     */
    public function path(Request $request)
    {
        $user = $request->user();
        $requested = strtoupper((string) $request->query('level', ''));
        $level = ($requested !== '' ? Level::where('is_active', true)->where('code', $requested)->first() : null)
            ?? $user->currentLevel
            ?? Level::where('is_active', true)->orderBy('order')->first();

        abort_if(! $level, 404, 'No level configured.');

        return response()->json($this->progressService->learningPath($user, $level));
    }
}
