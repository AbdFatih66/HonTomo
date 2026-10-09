<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VocabularyResource;
use App\Services\VocabularyChapterService;
use Illuminate\Http\Request;

class VocabularyController extends Controller
{
    public function __construct(private VocabularyChapterService $chapters)
    {
    }

    /** JLPT level of the chapter menu / word list (N5 default, N4 optional). */
    private function level(Request $request): string
    {
        $level = strtoupper((string) $request->query('level', 'N5'));

        return in_array($level, ['N5', 'N4'], true) ? $level : 'N5';
    }

    public function index(Request $request)
    {
        $level = $this->level($request);

        $query = \App\Models\Vocabulary::where('is_active', true)
            ->when($request->jlpt_level, fn ($q, $level) => $q->where('jlpt_level', $level))
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->chapter, fn ($q, $chapter) => $q->whereIn('category_id', $this->chapters->categoryIds((int) $chapter, $level)))
            ->orderBy('id');

        // A single chapter (Pelajaran) is a bounded, human-curated word list
        // (a few dozen words at most) meant to be read as one page, not
        // paginated like the general vocabulary browser.
        if ($request->chapter) {
            return VocabularyResource::collection($query->with('examples')->get());
        }

        return VocabularyResource::collection(
            $query->with('examples')->paginate(30)
        );
    }

    /** Curriculum chapters of a level (N5: Pelajaran 1-25; N4: its own units) with their word counts, for the vocabulary-by-chapter menu. */
    public function chapters(Request $request)
    {
        return response()->json(['data' => $this->chapters->chapters($request->user(), $this->level($request))]);
    }

    public function show(\App\Models\Vocabulary $vocabulary)
    {
        return new VocabularyResource($vocabulary->load('examples'));
    }
}
