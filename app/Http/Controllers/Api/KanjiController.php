<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kanji;
use App\Models\Lesson;
use App\Models\UserKanjiProgress;
use App\Models\UserLesson;
use Illuminate\Http\Request;

class KanjiController extends Controller
{
    /**
     * Filterable/searchable list for the chart/grid page. Lightweight by
     * design — no readings/vocabulary/strokes here, just enough to render
     * a grid cell. See show()/strokes() for the rest, loaded lazily per
     * character (docs/kanji-module.md, "Skala data").
     *
     * `sort=curriculum` orders by earliest_lesson_order (kanji not yet
     * linked to any lesson sort last) instead of the plain JLPT `order`
     * column. `scope=learned` restricts to kanji the user has actually
     * met so far — either of two independent signals, so the toggle
     * isn't empty just because one of them hasn't been populated yet:
     *   (a) direct practice: any UserKanjiProgress row for this kanji,
     *       from the kanji quiz/writing-practice screens themselves; or
     *   (b) curriculum: kanji linked (kanji_word_links) to a lesson the
     *       user has completed (their furthest completed lesson's global
     *       order — see Lesson::globalOrderMap()), which needs
     *       `kanji:link-vocabulary` + `kanji:sync-lesson-order` to have
     *       been run at least once — before that, earliest_lesson_order
     *       is null for every kanji and only signal (a) applies.
     * The frontend should treat an empty result under scope=learned as
     * "belum ada kanji yang dipelajari sama sekali", not as a real error.
     * GET /api/kanji?jlpt_level=&grade=&search=&page=&per_page=&sort=&scope=
     */
    public function index(Request $request)
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 48)));

        $query = Kanji::query();

        if ($request->query('sort') === 'curriculum') {
            $query->orderByRaw('earliest_lesson_order IS NULL')
                ->orderBy('earliest_lesson_order')
                ->orderBy('order')
                ->orderBy('id');
        } else {
            $query->orderBy('order')->orderBy('id');
        }

        if ($level = $request->query('jlpt_level')) {
            $query->where('jlpt_level', $level);
        }

        if ($grade = $request->query('grade')) {
            $query->where('grade', $grade);
        }

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('character', 'like', "%{$search}%")
                    ->orWhere('meaning_id', 'like', "%{$search}%")
                    ->orWhere('meaning_en', 'like', "%{$search}%")
                    ->orWhere('onyomi', 'like', "%{$search}%")
                    ->orWhere('kunyomi', 'like', "%{$search}%");
            });
        }

        $learnedMeta = null;

        if ($request->query('scope') === 'learned') {
            $threshold = $this->furthestCompletedLessonOrder($request->user()->id);

            $practicedKanjiIds = UserKanjiProgress::where('user_id', $request->user()->id)
                ->pluck('kanji_id');

            $query->where(function ($q) use ($threshold, $practicedKanjiIds) {
                $q->whereIn('id', $practicedKanjiIds);

                if ($threshold !== null) {
                    $q->orWhere(function ($q2) use ($threshold) {
                        $q2->whereNotNull('earliest_lesson_order')
                            ->where('earliest_lesson_order', '<=', $threshold);
                    });
                }
            });

            // Lets the frontend say WHY the list is empty: no progress of
            // either kind yet, vs. progress exists but the other filters
            // (level/grade/search) just don't match any of it.
            $learnedMeta = [
                'has_progress' => $threshold !== null || $practicedKanjiIds->isNotEmpty(),
                'linked_kanji' => Kanji::whereNotNull('earliest_lesson_order')->count(),
            ];
        }

        $paginated = $query->paginate($perPage);

        $progressByKanjiId = UserKanjiProgress::where('user_id', $request->user()->id)
            ->whereIn('kanji_id', collect($paginated->items())->pluck('id'))
            ->pluck('status', 'kanji_id');

        $page = $paginated->through(fn (Kanji $k) => $this->presentSummary($k, $progressByKanjiId->get($k->id)));

        return response()->json([
            'data' => $page->items(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
            'total' => $page->total(),
            'learned_meta' => $learnedMeta,
            // Drives the level filter tabs — only show levels that actually
            // have kanji imported yet, instead of hardcoding all five.
            // Descending on the string ('N5' > 'N4' > ... > 'N1') so the
            // tabs read easiest -> hardest, N5 first. Ascending would put
            // N1 on the far left, which is backwards for a learner.
            'available_jlpt_levels' => Kanji::query()
                ->whereNotNull('jlpt_level')
                ->distinct()
                ->orderByDesc('jlpt_level')
                ->pluck('jlpt_level'),
        ]);
    }

    /**
     * Full detail for the character screen: readings, meaning, and its
     * related vocabulary. Deliberately does NOT include stroke data — the
     * writing/animation widgets fetch that separately via strokes() only
     * once they actually mount, so opening the detail dialog for a
     * character with no writing practice in view yet stays cheap.
     * GET /api/kanji/{kanji}
     */
    public function show(Request $request, Kanji $kanji)
    {
        $kanji->load([
            'vocabulary' => fn ($q) => $q->orderBy('order'),
            'curriculumWords',
        ]);

        $progress = UserKanjiProgress::where('user_id', $request->user()->id)
            ->where('kanji_id', $kanji->id)
            ->first();

        return response()->json($this->presentDetail($kanji, $progress));
    }

    /**
     * HanziWriter-format stroke data only, for KanjiStrokeAnimation /
     * KanjiWritingCanvas's charDataLoader. Separate endpoint (rather than
     * bundling this into show()) so a character list/detail view never
     * pays for this payload unless a writing widget is actually mounted.
     * GET /api/kanji/{kanji}/strokes
     */
    public function strokes(Kanji $kanji)
    {
        $stroke = $kanji->strokeData;

        if (! $stroke) {
            return response()->json([
                'message' => 'Stroke data not yet imported for this character.',
            ], 404);
        }

        return response()->json($stroke->toHanziWriterFormat());
    }

    /**
     * A batch of quiz questions covering all 5 kanji quiz types from the
     * brief, mixed together per ?types=. Each type is only generated for a
     * kanji when enough real data exists for it to be unambiguous — see
     * the per-type comments below, and docs/kanji-module.md if the "why"
     * needs re-explaining to a future contributor.
     * `scope=curriculum` (instead of the default `scope=jlpt`) draws the
     * quiz pool from kanji the user has actually met in their lesson
     * progress so far (earliest_lesson_order <= their furthest completed
     * lesson), ignoring `jlpt_level` entirely — see index()'s docblock
     * for the same mechanism and its `kanji:sync-lesson-order`
     * prerequisite.
     * GET /api/kanji/quiz?jlpt_level=&count=&write_ratio=&types=&scope=
     */
    public function quiz(Request $request)
    {
        $level = $request->query('jlpt_level', 'N5');
        $scope = $request->query('scope', 'jlpt'); // 'jlpt' | 'curriculum'
        $count = max(1, min(30, (int) $request->query('count', 10)));
        $writeRatio = max(0, min(1, (float) $request->query('write_ratio', 0.2)));
        $mode = $request->query('mode', 'practice'); // 'practice' (all) | 'review' (not yet mastered)
        $requestedTypes = array_filter(explode(',', $request->query(
            'types',
            'meaning,reading,kanji_from_reading,vocabulary,write'
        )));

        if ($scope === 'curriculum') {
            $threshold = $this->furthestCompletedLessonOrder($request->user()->id);

            $query = Kanji::whereNotNull('earliest_lesson_order')
                ->where('earliest_lesson_order', '<=', $threshold ?? -1)
                ->with(['vocabulary', 'strokeData']);
        } else {
            $query = Kanji::where('jlpt_level', $level)->with(['vocabulary', 'strokeData']);
        }

        if ($mode === 'review') {
            $masteredIds = UserKanjiProgress::where('user_id', $request->user()->id)
                ->where('status', UserKanjiProgress::STATUS_MASTERED)
                ->pluck('kanji_id');

            $query->whereNotIn('id', $masteredIds);
        }

        $pool = $query->get();

        if ($pool->count() < 4) {
            return response()->json(['questions' => [], 'mode' => $mode, 'pool_size' => $pool->count()]);
        }

        $questions = collect(range(1, $count))
            ->map(fn () => $this->buildQuestion($pool, $requestedTypes, $writeRatio))
            ->filter()
            ->values();

        return response()->json([
            'jlpt_level' => $scope === 'curriculum' ? null : $level,
            'scope' => $scope,
            'mode' => $mode,
            'questions' => $questions,
        ]);
    }

    /**
     * Global order (Lesson::globalOrderMap()) of this user's furthest
     * completed/mastered lesson — the cutoff for "kanji they've actually
     * met so far" used by index()'s scope=learned and quiz()'s
     * scope=curriculum. Null means the user hasn't completed any lesson
     * yet, which both callers must treat as "nothing qualifies", not "no
     * filter".
     */
    private function furthestCompletedLessonOrder(int $userId): ?int
    {
        $completedLessonIds = UserLesson::where('user_id', $userId)
            ->whereIn('status', [UserLesson::STATUS_COMPLETED, UserLesson::STATUS_MASTERED])
            ->pluck('lesson_id');

        if ($completedLessonIds->isEmpty()) {
            return null;
        }

        return Lesson::globalOrderMap()
            ->only($completedLessonIds->all())
            ->max();
    }

    private function buildQuestion($pool, array $requestedTypes, float $writeRatio)
    {
        $target = $pool->random();

        // A type is only "viable" for this specific kanji when there is
        // enough real data behind it to ask an unambiguous question —
        // never fall back to guessing/omitting context (brief, rule #10).
        $viable = array_values(array_filter($requestedTypes, function ($type) use ($target) {
            return match ($type) {
                'meaning' => true, // always answerable from the kanjis row itself
                'write' => $target->strokeData !== null,
                'reading', 'kanji_from_reading', 'vocabulary' => $target->vocabulary->count() >= 1,
                default => false,
            };
        }));

        if (empty($viable)) {
            return null; // this kanji has no data for any requested type — try another draw silently
        }

        // write_ratio only nudges toward the writing type when it's viable;
        // otherwise fall through to the other viable types, mirroring how
        // the Kana quiz degrades a non-viable "write" pick into "choice".
        if (in_array('write', $viable, true) && (mt_rand(1, 100) / 100) <= $writeRatio) {
            $type = 'write';
        } else {
            $type = $viable[array_rand(array_diff($viable, ['write']) ?: $viable)];
        }

        return match ($type) {
            'meaning' => $this->questionMeaning($target, $pool),
            'reading' => $this->questionReading($target, $pool),
            'kanji_from_reading' => $this->questionKanjiFromReading($target, $pool),
            'vocabulary' => $this->questionVocabulary($target, $pool),
            'write' => $this->questionWrite($target),
        };
    }

    /** A) Kanji -> Arti: show the character, pick its meaning. */
    private function questionMeaning(Kanji $target, $pool): array
    {
        $distractors = $pool->where('id', '!=', $target->id)
            ->unique(fn ($k) => $k->meaning())
            ->shuffle()
            ->take(3);

        $options = $distractors->push($target)->map(fn ($k) => $k->meaning())->unique()->shuffle()->values();

        return [
            'question_type' => 'meaning',
            'mode' => 'choice',
            'id' => $target->id,
            'prompt_character' => $target->character,
            'answer' => $target->meaning(),
            'options' => $options,
        ];
    }

    /**
     * B) Kanji -> Cara Baca. Deliberately asks for the reading of a WHOLE
     * WORD from kanji_vocabulary (resolved, unambiguous per JMdict) rather
     * than the bare kanji's on'yomi/kun'yomi — a kanji with multiple
     * readings has no single correct answer to "how do you read this
     * character", so that question is never asked in isolation.
     */
    private function questionReading(Kanji $target, $pool): array
    {
        $word = $target->vocabulary->random();

        $otherReadings = $pool->where('id', '!=', $target->id)
            ->flatMap(fn ($k) => $k->vocabulary)
            ->pluck('reading')
            ->unique()
            ->filter(fn ($r) => $r !== $word->reading)
            ->shuffle()
            ->take(3);

        $options = $otherReadings->push($word->reading)->unique()->shuffle()->values();

        if ($options->count() < 2) {
            return $this->questionMeaning($target, $pool); // not enough distractors yet — degrade gracefully
        }

        return [
            'question_type' => 'reading',
            'mode' => 'choice',
            'id' => $target->id,
            'prompt_word' => $word->word,
            'prompt_meaning' => $word->meaning(),
            'answer' => $word->reading,
            'options' => $options,
        ];
    }

    /** C) Hiragana -> Kanji: show a word's reading (+ meaning), pick the matching kanji word. */
    private function questionKanjiFromReading(Kanji $target, $pool): array
    {
        $word = $target->vocabulary->random();

        $otherWords = $pool->where('id', '!=', $target->id)
            ->flatMap(fn ($k) => $k->vocabulary)
            ->unique('word')
            ->where('word', '!=', $word->word)
            ->shuffle()
            ->take(3)
            ->pluck('word');

        $options = $otherWords->push($word->word)->unique()->shuffle()->values();

        if ($options->count() < 2) {
            return $this->questionMeaning($target, $pool);
        }

        return [
            'question_type' => 'kanji_from_reading',
            'mode' => 'choice',
            'id' => $target->id,
            'prompt_reading' => $word->reading,
            'prompt_meaning' => $word->meaning(),
            'answer' => $word->word,
            'options' => $options,
        ];
    }

    /** D) Kanji -> Kosakata: show the character, pick the word that actually contains it. */
    private function questionVocabulary(Kanji $target, $pool): array
    {
        $correctWord = $target->vocabulary->random();

        $distractorWords = $pool->where('id', '!=', $target->id)
            ->flatMap(fn ($k) => $k->vocabulary)
            // Guard against a distractor that happens to also contain the
            // target character (compound words can share kanji) — that
            // would make the question have two correct answers.
            ->filter(fn ($v) => ! str_contains($v->word, $target->character))
            ->unique('word')
            ->shuffle()
            ->take(3);

        $options = $distractorWords->push($correctWord)->unique('word')->shuffle()->values()
            ->map(fn ($v) => ['word' => $v->word, 'reading' => $v->reading]);

        if ($options->count() < 2) {
            return $this->questionMeaning($target, $pool);
        }

        return [
            'question_type' => 'vocabulary',
            'mode' => 'choice',
            'id' => $target->id,
            'prompt_character' => $target->character,
            'prompt_meaning' => $target->meaning(),
            'answer' => $correctWord->word,
            'options' => $options,
        ];
    }

    /** E) Writing quiz: prompt with the meaning, write the character from memory. */
    private function questionWrite(Kanji $target): array
    {
        return [
            'question_type' => 'write',
            'mode' => 'write',
            'id' => $target->id,
            'prompt_meaning' => $target->meaning(),
            'answer_character' => $target->character,
        ];
    }

    /**
     * Records the outcome of one quiz answer (or a writing-practice
     * attempt on the detail screen) against this kanji for the current
     * user, and returns the updated progress so the frontend can reflect
     * a mastery change immediately without a second round-trip.
     * POST /api/kanji/{kanji}/progress   body: { correct: bool }
     */
    public function recordProgress(Request $request, Kanji $kanji)
    {
        $validated = $request->validate(['correct' => 'required|boolean']);

        $progress = UserKanjiProgress::firstOrCreate([
            'user_id' => $request->user()->id,
            'kanji_id' => $kanji->id,
        ]);

        $progress->applyResult($validated['correct']);

        return response()->json([
            'status' => $progress->status,
            'correct_count' => $progress->correct_count,
            'incorrect_count' => $progress->incorrect_count,
            'current_streak' => $progress->current_streak,
        ]);
    }

    private function presentSummary(Kanji $k, ?string $status = null): array
    {
        return [
            'id' => $k->id,
            'character' => $k->character,
            'meaning' => $k->meaning(),
            'stroke_count' => $k->stroke_count,
            'jlpt_level' => $k->jlpt_level,
            'grade' => $k->grade,
            'status' => $status ?? UserKanjiProgress::STATUS_NEW,
            // Null if kanji:sync-lesson-order hasn't linked this kanji to
            // any lesson yet — the grid should just omit the badge then.
            'in_curriculum' => $k->earliest_lesson_order !== null,
        ];
    }

    private function presentDetail(Kanji $k, ?UserKanjiProgress $progress = null): array
    {
        return [
            'id' => $k->id,
            'character' => $k->character,
            'onyomi' => $k->onyomi ?? [],
            'kunyomi' => $k->kunyomi ?? [],
            'meaning' => $k->meaning(),
            'stroke_count' => $k->stroke_count,
            'jlpt_level' => $k->jlpt_level,
            'grade' => $k->grade,
            'frequency_rank' => $k->frequency_rank,
            'vocabulary' => $k->vocabulary->map(fn ($v) => [
                'id' => $v->id,
                'word' => $v->word,
                'reading' => $v->reading,
                'meaning' => $v->meaning(),
            ]),
            // Curriculum words (Tahap 5): where the learner actually meets
            // this kanji in their lessons, distinct from the JMdict
            // "vocabulary" example list above. One extra query per word
            // for its lesson titles — fine at this scale; if the
            // curriculum grows large, cache this per kanji instead of
            // computing it on every show() call.
            'curriculum_words' => $k->curriculumWords->map(fn ($v) => [
                'id' => $v->id,
                'word' => $v->japanese,
                'reading' => $v->hiragana,
                'meaning' => $v->meaning(),
                'lessons' => $v->lessons()->map(fn ($lesson) => [
                    'id' => $lesson->id,
                    'title' => app()->getLocale() === 'en' ? $lesson->title_en : $lesson->title_id,
                ]),
            ]),
            'progress' => [
                'status' => $progress->status ?? UserKanjiProgress::STATUS_NEW,
                'correct_count' => $progress->correct_count ?? 0,
                'incorrect_count' => $progress->incorrect_count ?? 0,
                'current_streak' => $progress->current_streak ?? 0,
                'mastered_at' => $progress?->mastered_at,
            ],
        ];
    }
}
