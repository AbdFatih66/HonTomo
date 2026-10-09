<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserReview;
use App\Models\UserVocabulary;
use App\Models\Vocabulary;
use Carbon\Carbon;

class ReviewService
{
    /**
     * Interval (days) to wait before the next review, per mastery level.
     * Placeholder schedule — swap for SM-2 or similar later.
     */
    private const INTERVALS = [
        UserVocabulary::MASTERY_NEW => 1,
        UserVocabulary::MASTERY_LEARNING => 3,
        UserVocabulary::MASTERY_FAMILIAR => 7,
        UserVocabulary::MASTERY_MASTERED => 21,
    ];

    public function due(User $user, int $limit = 20)
    {
        return UserVocabulary::where('user_id', $user->id)
            ->where(function ($q) {
                $q->whereNull('next_review_at')
                    ->orWhere('next_review_at', '<=', now());
            })
            ->with('vocabulary')
            ->limit($limit)
            ->get();
    }

    public function recordAttempt(User $user, Vocabulary $vocabulary, bool $wasCorrect, ?int $responseTimeMs = null): UserVocabulary
    {
        $userVocab = UserVocabulary::firstOrCreate(
            ['user_id' => $user->id, 'vocabulary_id' => $vocabulary->id],
            ['mastery_level' => UserVocabulary::MASTERY_NEW]
        );

        if ($wasCorrect) {
            $userVocab->correct_count += 1;
            $userVocab->mastery_level = $this->promote($userVocab->mastery_level);
        } else {
            $userVocab->wrong_count += 1;
            $userVocab->mastery_level = $this->demote($userVocab->mastery_level);
        }

        $interval = self::INTERVALS[$userVocab->mastery_level];
        $userVocab->interval_days = $interval;
        $userVocab->last_reviewed_at = now();
        $userVocab->next_review_at = Carbon::now()->addDays($interval);
        $userVocab->save();

        UserReview::create([
            'user_id' => $user->id,
            'user_vocabulary_id' => $userVocab->id,
            'was_correct' => $wasCorrect,
            'response_time_ms' => $responseTimeMs,
        ]);

        return $userVocab;
    }

    private function promote(string $level): string
    {
        return match ($level) {
            UserVocabulary::MASTERY_NEW => UserVocabulary::MASTERY_LEARNING,
            UserVocabulary::MASTERY_LEARNING => UserVocabulary::MASTERY_FAMILIAR,
            UserVocabulary::MASTERY_FAMILIAR, UserVocabulary::MASTERY_MASTERED => UserVocabulary::MASTERY_MASTERED,
            default => UserVocabulary::MASTERY_LEARNING,
        };
    }

    private function demote(string $level): string
    {
        return match ($level) {
            UserVocabulary::MASTERY_MASTERED => UserVocabulary::MASTERY_FAMILIAR,
            UserVocabulary::MASTERY_FAMILIAR => UserVocabulary::MASTERY_LEARNING,
            default => UserVocabulary::MASTERY_NEW,
        };
    }
}
