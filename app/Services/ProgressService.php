<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Level;
use App\Models\User;
use App\Models\UserLesson;

class ProgressService
{
    /**
     * Unlock the first lesson(s) with no prerequisite for a fresh user,
     * or unlock the next lesson after one is completed.
     */
    public function unlockAvailableLessons(User $user): void
    {
        Lesson::where('is_active', true)
            ->whereNull('prerequisite_lesson_id')
            ->get()
            ->each(function (Lesson $lesson) use ($user) {
                UserLesson::firstOrCreate(
                    ['user_id' => $user->id, 'lesson_id' => $lesson->id],
                    ['status' => UserLesson::STATUS_AVAILABLE]
                );
            });
    }

    public function unlockNext(User $user, Lesson $completedLesson): void
    {
        $nextLessons = Lesson::where('prerequisite_lesson_id', $completedLesson->id)
            ->where('is_active', true)
            ->get();

        foreach ($nextLessons as $lesson) {
            UserLesson::firstOrCreate(
                ['user_id' => $user->id, 'lesson_id' => $lesson->id],
                ['status' => UserLesson::STATUS_AVAILABLE]
            );
        }
    }

    /**
     * Whether the learner may open this lesson: it has no prerequisite, the
     * prerequisite is completed/mastered, or the lesson itself is already done
     * (so finished lessons can always be reviewed).
     */
    public function isUnlocked(User $user, Lesson $lesson): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $done = [UserLesson::STATUS_COMPLETED, UserLesson::STATUS_MASTERED];

        $own = UserLesson::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->value('status');

        if (in_array($own, $done, true) || $lesson->prerequisite_lesson_id === null) {
            return true;
        }

        return UserLesson::where('user_id', $user->id)
            ->where('lesson_id', $lesson->prerequisite_lesson_id)
            ->whereIn('status', $done)
            ->exists();
    }

    public function summary(User $user): array
    {
        $lessons = UserLesson::where('user_id', $user->id)->get();

        return [
            'lessons_completed' => $lessons->whereIn('status', [
                UserLesson::STATUS_COMPLETED, UserLesson::STATUS_MASTERED,
            ])->count(),
            'lessons_mastered' => $lessons->where('status', UserLesson::STATUS_MASTERED)->count(),
            'vocabulary_mastered' => $user->userVocabularies()
                ->where('mastery_level', 'mastered')->count(),
            'total_xp' => $user->totalXp(),
            'current_streak' => optional($user->streak)->current_streak ?? 0,
        ];
    }

    /**
     * Build the learning path (units -> lessons) for a level with the status
     * of every lesson for this user. A lesson with no UserLesson row is
     * "available" if it has no prerequisite or its prerequisite is done,
     * otherwise "locked" — so the path is correct even for brand new users
     * and for lessons an admin adds later.
     */
    public function learningPath(User $user, Level $level): array
    {
        $level->load([
            'units' => fn ($q) => $q->where('is_active', true)->orderBy('order'),
            'units.lessons' => fn ($q) => $q->where('is_active', true)->orderBy('order'),
        ]);

        $statuses = UserLesson::where('user_id', $user->id)->pluck('status', 'lesson_id');
        $done = [UserLesson::STATUS_COMPLETED, UserLesson::STATUS_MASTERED];
        $en = app()->getLocale() === 'en';
        $isAdmin = $user->isAdmin();

        $units = $level->units->map(function ($unit) use ($statuses, $done, $en, $isAdmin) {
            return [
                'id' => $unit->id,
                'title' => $unit->title(),
                'description' => $en ? $unit->description_en : $unit->description_id,
                'icon' => $unit->icon,
                'lessons' => $unit->lessons->map(function (Lesson $lesson) use ($statuses, $done, $isAdmin) {
                    $stored = $statuses[$lesson->id] ?? null;

                    // A finished lesson stays finished. Anything else is only
                    // open while its prerequisite is done — a stored
                    // "available" row is not proof of that: rows are created
                    // when an EARLIER lesson is completed, so a lesson that
                    // later gained a new prerequisite (e.g. a Bunpou lesson
                    // slotted in before the vocabulary) would otherwise stay
                    // open ahead of it.
                    $prerequisiteDone = $lesson->prerequisite_lesson_id === null
                        || in_array($statuses[$lesson->prerequisite_lesson_id] ?? null, $done, true);

                    $status = match (true) {
                        $isAdmin && ! in_array($stored, $done, true) => UserLesson::STATUS_AVAILABLE,
                        in_array($stored, $done, true) => $stored,
                        ! $prerequisiteDone => UserLesson::STATUS_LOCKED,
                        $stored === UserLesson::STATUS_IN_PROGRESS => UserLesson::STATUS_IN_PROGRESS,
                        default => UserLesson::STATUS_AVAILABLE,
                    };

                    return [
                        'id' => $lesson->id,
                        'title' => $lesson->title(),
                        'category' => $lesson->category,
                        'xp_reward' => $lesson->xp_reward,
                        'status' => $status,
                    ];
                })->values(),
            ];
        })->values();

        return [
            'level' => ['id' => $level->id, 'code' => $level->code, 'name' => $level->name()],
            'units' => $units,
        ];
    }

    /**
     * First lesson the learner should do next: one in progress, else the first available.
     */
    public function nextLesson(array $path): ?array
    {
        $lessons = collect($path['units'])->flatMap(fn ($u) => $u['lessons']);

        return $lessons->firstWhere('status', UserLesson::STATUS_IN_PROGRESS)
            ?? $lessons->firstWhere('status', UserLesson::STATUS_AVAILABLE);
    }
}
