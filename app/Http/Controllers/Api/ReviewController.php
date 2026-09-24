<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VocabularyResource;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(private ReviewService $reviewService) {}

    public function due(Request $request)
    {
        $due = $this->reviewService->due($request->user());

        return response()->json([
            'due' => $due->map(fn ($uv) => new VocabularyResource($uv->vocabulary)),
        ]);
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'vocabulary_id' => 'required|exists:vocabularies,id',
            'was_correct' => 'required|boolean',
            'response_time_ms' => 'nullable|integer',
        ]);

        $vocabulary = \App\Models\Vocabulary::findOrFail($data['vocabulary_id']);

        $userVocab = $this->reviewService->recordAttempt(
            $request->user(),
            $vocabulary,
            $data['was_correct'],
            $data['response_time_ms'] ?? null,
        );

        return response()->json(['mastery_level' => $userVocab->mastery_level]);
    }
}
