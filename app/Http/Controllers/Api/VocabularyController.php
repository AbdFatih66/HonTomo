<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VocabularyResource;
use Illuminate\Http\Request;

class VocabularyController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Vocabulary::where('is_active', true)
            ->when($request->jlpt_level, fn ($q, $level) => $q->where('jlpt_level', $level))
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id));

        return VocabularyResource::collection(
            $query->with('examples')->paginate(30)
        );
    }

    public function show(\App\Models\Vocabulary $vocabulary)
    {
        return new VocabularyResource($vocabulary->load('examples'));
    }
}
