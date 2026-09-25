<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LessonQuestionResource;
use App\Http\Resources\LessonResource;
use App\Models\Lesson;
use App\Models\UserLesson;
use App\Services\LessonService;
use App\Services\ProgressService;
use App\Services\QuestionOptionRepair;
use App\Services\QuestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LessonController extends Controller
{
    public function __construct(
        private LessonService $lessonService,
        private QuestionService $questionService,
        private QuestionOptionRepair $optionRepair,
        private ProgressService $progressService,
    ) {}

    public function show(Request $request, Lesson $lesson)
    {
        // Same lock LessonService::start enforces. Without this, a locked
        // lesson's questions (and answers, via LessonQuestionResource) could
        // be read directly by id, and ensureForLesson() below would write
        // repaired options for a lesson the user isn't supposed to reach yet.
        abort_unless($this->progressService->isUnlocked($request->user(), $lesson), 403, 'Lesson is locked.');

        // Questions that arrive without usable answer options are rebuilt
        // before being sent, so the player never shows an unanswerable card.
        $this->optionRepair->ensureForLesson($lesson);

        $lesson->load(['questions.options']);

        return response()->json([
            'lesson' => new LessonResource($lesson),
            'questions' => LessonQuestionResource::collection(
                $this->withShuffledOptions($this->inPlayOrder($lesson->questions))
            ),
        ]);
    }

    /**
     * Quiz questions come in a fresh random order every time the lesson is
     * opened (or retried), so the learner can't rely on the stored sequence
     * (a, i, u, e, o …). Two things keep their order:
     *  - grammar/Bunpou cards are a lesson to READ in sequence, not a quiz;
     *  - flashcards (introductions) stay ahead of the quiz questions —
     *    each group is shuffled inside itself.
     */
    private function inPlayOrder(Collection $questions): Collection
    {
        $grammar = $questions->where('question_type', 'grammar')->sortBy('order')->values();
        $cards = $questions->where('question_type', 'flashcard')->shuffle()->values();
        $quiz = $questions->whereNotIn('question_type', ['grammar', 'flashcard'])->shuffle()->values();

        return $grammar->concat($cards)->concat($quiz)->values();
    }

    /**
     * Multiple-choice options are re-drawn every time the lesson is opened:
     *  - the wrong options are picked at random from a pool made of the
     *    question's own stored wrong options plus the correct answers of the
     *    OTHER questions of the same lesson (so a "ki" question can never get
     *    a tenten letter, and a vocabulary question only gets words of its own
     *    lesson), and
     *  - the four options are put in random order.
     * The right answer is always present exactly once, and no two options ever
     * share the same text. Options are real rows, so grading (by option id) is
     * unchanged — an option borrowed from another question is simply "wrong".
     */
    private function withShuffledOptions(Collection $questions): Collection
    {
        $choice = fn ($q) => $q->question_type === 'multiple_choice';
        $norm = fn ($o) => mb_strtolower(trim((string) $o->label_id));

        // correct option of every choice question in the lesson
        $correctOptions = $questions->filter($choice)
            ->map(fn ($q) => $q->options->firstWhere('is_correct', true))
            ->filter()
            ->values();

        foreach ($questions->filter($choice) as $question) {
            $correct = $question->options->firstWhere('is_correct', true);

            if (! $correct || $question->options->count() < 2) {
                continue; // nothing sensible to draw from (repaired elsewhere)
            }

            $wanted = min(3, $question->options->count() - 1);

            $wrong = $question->options->where('is_correct', false)
                ->concat($correctOptions->where('lesson_question_id', '!=', $question->id))
                ->reject(fn ($o) => $norm($o) === $norm($correct))
                ->unique($norm)
                ->shuffle()
                ->take($wanted);

            $question->setRelation('options', $wrong->push($correct)->shuffle()->values());
        }

        return $questions;
    }

    public function start(Request $request, Lesson $lesson)
    {
        $userLesson = $this->lessonService->start($request->user(), $lesson);

        return response()->json(['user_lesson_id' => $userLesson->id]);
    }

    public function submitAnswer(Request $request, UserLesson $userLesson)
    {
        $this->ensureOwnedBy($request, $userLesson);

        $data = $request->validate([
            'question_id' => 'required|integer',
            'answer' => 'required',
            'time_taken_ms' => 'nullable|integer',
        ]);

        $attempt = $this->lessonService->submitAnswer(
            $request->user(),
            $userLesson,
            $data['question_id'],
            $data['answer'],
            $data['time_taken_ms'] ?? null,
        );

        // Safe to reveal now: the answer has already been graded and recorded.
        $question = $userLesson->lesson->questions()->find($data['question_id']);

        return response()->json([
            'is_correct' => $attempt->is_correct,
            ...$this->questionService->revealCorrectAnswer($question),
        ]);
    }

    public function finish(Request $request, UserLesson $userLesson)
    {
        $this->ensureOwnedBy($request, $userLesson);

        $userLesson = $this->lessonService->finish($request->user(), $userLesson);

        // Same set the score itself was computed from — one row per question,
        // this run's answer only. Querying the raw attempts here would report
        // a different (inflated) total than the accuracy was based on.
        $attempts = $this->lessonService->scoredAttempts($userLesson);

        return response()->json([
            'status' => $userLesson->status,
            'best_accuracy' => $userLesson->best_accuracy,
            'xp_earned' => $userLesson->xp_earned,
            'total_questions' => $attempts->count(),
            'correct_answers' => $attempts->where('is_correct', true)->count(),
        ]);
    }

    /**
     * A lesson attempt belongs to one user. Without this check any signed-in
     * user could answer or finish someone else's attempt just by guessing its
     * id (changing their progress and score). 404 rather than 403 so ids of
     * other people's attempts cannot be probed.
     */
    private function ensureOwnedBy(Request $request, UserLesson $userLesson): void
    {
        abort_unless($userLesson->user_id === $request->user()->id, 404);
    }
}
