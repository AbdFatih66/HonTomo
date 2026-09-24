<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\User;
use App\Models\UserLesson;
use App\Models\UserQuestionAttempt;
use Illuminate\Support\Facades\DB;

class LessonService
{
    public function __construct(
        private QuestionService $questionService,
        private XpService $xpService,
        private StreakService $streakService,
        private ProgressService $progressService,
    ) {}

    public function start(User $user, Lesson $lesson): UserLesson
    {
        // Opening a lesson by URL (or from a stale screen) must not skip the
        // order of the path: e.g. vocabulary before its Bunpou lesson.
        abort_unless($this->progressService->isUnlocked($user, $lesson), 403, 'Lesson is locked.');

        $userLesson = UserLesson::firstOrNew([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
        ]);

        // Reopening a lesson to review it (e.g. re-reading the bunpou cards)
        // must not demote a lesson that was already completed/mastered back
        // to "in progress" the instant it's opened — that's what made the
        // path jump backwards instead of pointing at the next lesson.
        $alreadyDone = in_array($userLesson->status, [
            UserLesson::STATUS_COMPLETED, UserLesson::STATUS_MASTERED,
        ], true);

        if (! $alreadyDone) {
            $userLesson->status = UserLesson::STATUS_IN_PROGRESS;
        }

        $userLesson->started_at ??= now();
        $userLesson->attempts += 1;
        $userLesson->save();

        return $userLesson;
    }

    /**
     * Submit one answer within a lesson attempt.
     */
    public function submitAnswer(User $user, UserLesson $userLesson, int $questionId, mixed $answer, ?int $timeTakenMs = null): UserQuestionAttempt
    {
        $question = $userLesson->lesson->questions()->findOrFail($questionId);
        $isCorrect = $this->questionService->grade($question, $answer);

        if ($question->vocabulary_id) {
            app(ReviewService::class)->recordAttempt(
                $user,
                $question->vocabulary,
                $isCorrect,
                $timeTakenMs
            );
        }

        return UserQuestionAttempt::create([
            'user_id' => $user->id,
            'lesson_question_id' => $question->id,
            'user_lesson_id' => $userLesson->id,
            'given_answer' => is_scalar($answer) ? $answer : json_encode($answer),
            'is_correct' => $isCorrect,
            'time_taken_ms' => $timeTakenMs,
        ]);
    }

    /**
     * The attempts that count towards THIS run's score.
     *
     * UserQuestionAttempt rows are never cleared between runs, and one
     * UserLesson row is reused every time the lesson is replayed, so a plain
     * `where('user_lesson_id', ...)` returns every answer the user has ever
     * given for this lesson. That produced two visible bugs on the result
     * screen: the score total climbed past the number of questions the lesson
     * actually has, and a run answered perfectly still showed mistakes —
     * wrong answers from an earlier run were still in the pile.
     *
     * Keeping only the LATEST attempt per question fixes both, without a
     * migration: the total becomes the number of distinct questions answered,
     * and each question is scored on how the user answered it most recently.
     * Attempts for questions that are no longer part of the lesson (removed
     * or reordered by a seeder) are dropped as well.
     */
    public function scoredAttempts(UserLesson $userLesson): \Illuminate\Support\Collection
    {
        $liveQuestionIds = $userLesson->lesson
            ->questions()
            ->where('question_type', '!=', 'grammar')
            ->pluck('id');

        return UserQuestionAttempt::where('user_lesson_id', $userLesson->id)
            ->whereIn('lesson_question_id', $liveQuestionIds)
            ->orderBy('id')
            ->get()
            ->keyBy('lesson_question_id') // later rows overwrite earlier ones
            ->values();
    }

    /**
     * Finish a lesson attempt: compute accuracy, award XP, update streak,
     * unlock the next lesson(s).
     */
    public function finish(User $user, UserLesson $userLesson): UserLesson
    {
        return DB::transaction(function () use ($user, $userLesson) {
            $attempts = $this->scoredAttempts($userLesson);
            $total = $attempts->count();
            $correct = $attempts->where('is_correct', true)->count();
            $accuracy = $total > 0 ? (int) round(($correct / $total) * 100) : 0;
            $lesson = $userLesson->lesson;

            // Read-only grammar lessons (e.g. "Tata Bahasa (Bunpou)") have
            // nothing to score: reaching the last card is what completes them.
            $studyOnly = $total === 0 && $lesson->category === 'grammar';
            $alreadyDone = $userLesson->completed_at !== null;
            $previousStatus = $userLesson->status;

            if ($studyOnly) {
                $accuracy = 100;
            }

            $userLesson->best_accuracy = max($userLesson->best_accuracy, $accuracy);

            $newStatus = $studyOnly
                ? UserLesson::STATUS_COMPLETED
                : ($accuracy >= $lesson->required_accuracy
                    ? ($accuracy === 100 ? UserLesson::STATUS_MASTERED : UserLesson::STATUS_COMPLETED)
                    : UserLesson::STATUS_IN_PROGRESS);

            // Replaying a lesson for review must never demote it: if it was
            // already completed/mastered before, keep whichever of the two
            // statuses ranks higher instead of overwriting with this
            // attempt's (possibly weaker) result.
            $rank = [
                UserLesson::STATUS_AVAILABLE => 0,
                UserLesson::STATUS_IN_PROGRESS => 1,
                UserLesson::STATUS_COMPLETED => 2,
                UserLesson::STATUS_MASTERED => 3,
            ];

            $userLesson->status = ($rank[$newStatus] ?? 0) >= ($rank[$previousStatus] ?? 0)
                ? $newStatus
                : $previousStatus;

            if (in_array($userLesson->status, [UserLesson::STATUS_COMPLETED, UserLesson::STATUS_MASTERED], true)) {
                $userLesson->completed_at ??= now();

                // Re-reading a study-only lesson must not farm XP again.
                if (! ($studyOnly && $alreadyDone)) {
                    $xp = $lesson->xp_reward;
                    $userLesson->xp_earned += $xp;

                    $this->xpService->award($user, $xp, 'lesson_completed', $lesson->id);
                }

                $this->streakService->recordActivity($user);
                $this->progressService->unlockNext($user, $lesson);
            }

            $userLesson->save();

            return $userLesson;
        });
    }
}
