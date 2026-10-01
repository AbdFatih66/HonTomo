<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserStreak;
use Carbon\Carbon;

class StreakService
{
    public function __construct(
        private NotificationService $notificationService,
    ) {}

    // Only notify on round numbers — a streak entry every single day would
    // drown out the lesson-completed notifications in the same feed.
    private const MILESTONES = [3, 7, 14, 30, 60, 100, 200, 365];

    /**
     * Call this whenever a user completes any learning activity.
     * Increments the streak if the last activity was yesterday,
     * keeps it if already logged today, resets if a day was missed
     * (unless a streak freeze is available).
     */
    public function recordActivity(User $user): UserStreak
    {
        $streak = UserStreak::firstOrCreate(
            ['user_id' => $user->id],
            ['current_streak' => 0, 'longest_streak' => 0]
        );

        $today = Carbon::today();
        $last = $streak->last_activity_date ? Carbon::parse($streak->last_activity_date) : null;
        $alreadyLoggedToday = $last !== null && $last->isSameDay($today);

        if ($last === null) {
            $streak->current_streak = 1;
        } elseif ($alreadyLoggedToday) {
            // already recorded today, no change
        } elseif ($last->isSameDay($today->copy()->subDay())) {
            $streak->current_streak += 1;
        } elseif ($streak->streak_freeze_available) {
            $streak->streak_freeze_available = false;
            $streak->current_streak += 1;
        } else {
            $streak->current_streak = 1;
        }

        $streak->longest_streak = max($streak->longest_streak, $streak->current_streak);
        $streak->last_activity_date = $today;
        $streak->save();

        if (! $alreadyLoggedToday && in_array($streak->current_streak, self::MILESTONES, true))
            $this->notificationService->streakMilestone($user, $streak->current_streak);

        return $streak;
    }
}
