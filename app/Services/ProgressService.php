<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Level;
use App\Models\User;
use App\Models\UserLesson;

class ProgressService
{
    /** The only lesson category shown on the learning path. */
    public const PATH_CATEGORY = 'grammar';

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
     * Every lesson is open to every account — there is no prerequisite gate.
     * (Kept as a method so LessonService / LessonController callers stay valid.)
     */
    public function isUnlocked(User $user, Lesson $lesson): bool
    {
        return true;
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
     * of every lesson for this user. Nothing is locked: a lesson is finished,
     * in progress, or available. Only Bunpou (grammar) lessons are part of
     * the path — vocabulary quizzes live on the Kosakata page and
     * Hiragana/Katakana on the Kana page — and units left empty are dropped.
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

        $units = $level->units->map(function ($unit) use ($statuses, $done, $en) {
            // The path is Bunpou (grammar) only. Vocabulary quizzes live on the
            // Kosakata page; Hiragana/Katakana live on the Kana page.
            $lessons = $unit->lessons
                ->filter(fn (Lesson $lesson) => $lesson->category === self::PATH_CATEGORY);

            if ($lessons->isEmpty()) {
                return null;
            }

            return [
                'id' => $unit->id,
                'title' => $unit->title(),
                'description' => $en ? $unit->description_en : $unit->description_id,
                'icon' => $unit->icon,
                'lessons' => $lessons->map(function (Lesson $lesson) use ($statuses, $done) {
                    $stored = $statuses[$lesson->id] ?? null;

                    $status = match (true) {
                        in_array($stored, $done, true) => $stored,
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
        })->filter()->values();

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
