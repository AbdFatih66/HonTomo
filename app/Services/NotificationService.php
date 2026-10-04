<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\User;
use App\Models\UserNotification;

/**
 * Writes the navbar's notification feed. Every call here corresponds to a
 * real learning event (a lesson finished/mastered, a streak milestone) —
 * there is no "unread demo data" any more, just what the user actually did.
 */
class NotificationService
{
    public function lessonCompleted(User $user, Lesson $lesson, int $xp): UserNotification
    {
        return UserNotification::create([
            'user_id' => $user->id,
            'type' => 'lesson_completed',
            'title' => 'notifications.lesson_completed_title',
            'subtitle' => json_encode(['key' => 'notifications.lesson_completed_subtitle', 'lesson' => $lesson->title(), 'xp' => $xp]),
            'icon' => 'tabler-circle-check-filled',
            'color' => 'success',
        ]);
    }

    public function lessonMastered(User $user, Lesson $lesson, int $xp): UserNotification
    {
        return UserNotification::create([
            'user_id' => $user->id,
            'type' => 'lesson_mastered',
            'title' => 'notifications.lesson_mastered_title',
            'subtitle' => json_encode(['key' => 'notifications.lesson_mastered_subtitle', 'lesson' => $lesson->title(), 'xp' => $xp]),
            'icon' => 'tabler-crown',
            'color' => 'warning',
        ]);
    }

    /**
     * Only called on milestone days (see StreakService) so the bell doesn't
     * fill up with one entry per day studied.
     */
    public function streakMilestone(User $user, int $days): UserNotification
    {
        return UserNotification::create([
            'user_id' => $user->id,
            'type' => 'streak',
            'title' => 'notifications.streak_title',
            'subtitle' => json_encode(['key' => 'notifications.streak_subtitle', 'days' => $days]),
            'icon' => 'tabler-flame',
            'color' => 'error',
        ]);
    }

    public function mondaishuuCompleted(User $user, string $setKey, int $xp): UserNotification
    {
        return $this->practiceNotification($user, 'mondaishuu', $setKey, $xp, mastered: false);
    }

    public function mondaishuuMastered(User $user, string $setKey, int $xp): UserNotification
    {
        return $this->practiceNotification($user, 'mondaishuu', $setKey, $xp, mastered: true);
    }

    public function kaiteOboeruCompleted(User $user, string $setKey, int $xp): UserNotification
    {
        return $this->practiceNotification($user, 'kaite_oboeru', $setKey, $xp, mastered: false);
    }

    public function kaiteOboeruMastered(User $user, string $setKey, int $xp): UserNotification
    {
        return $this->practiceNotification($user, 'kaite_oboeru', $setKey, $xp, mastered: true);
    }

    public function chokaiCompleted(User $user, string $setKey, int $xp): UserNotification
    {
        return $this->practiceNotification($user, 'chokai', $setKey, $xp, mastered: false);
    }

    public function chokaiMastered(User $user, string $setKey, int $xp): UserNotification
    {
        return $this->practiceNotification($user, 'chokai', $setKey, $xp, mastered: true);
    }

    public function kaiwaCompleted(User $user, string $setKey, int $xp): UserNotification
    {
        return $this->practiceNotification($user, 'kaiwa', $setKey, $xp, mastered: false);
    }

    public function kaiwaMastered(User $user, string $setKey, int $xp): UserNotification
    {
        return $this->practiceNotification($user, 'kaiwa', $setKey, $xp, mastered: true);
    }

    /** $pack = kunci pack Tes JLPT ('private', 'n5-test-1', …). */
    public function jlptTestCompleted(User $user, string $pack, int $xp): UserNotification
    {
        return $this->practiceNotification($user, 'jlpt_test', $pack, $xp, mastered: false);
    }

    public function jlptTestPassed(User $user, string $pack, int $xp): UserNotification
    {
        return $this->practiceNotification($user, 'jlpt_test', $pack, $xp, mastered: true);
    }

    /**
     * Mondaishuu/Kaite Oboeru sets don't have a DB row (their question bank
     * lives in the Vue file — see UserMondaishuuProgress's docblock), so
     * there's no model to pull a localized title from like Lesson::title().
     * `label` is built from the set_key instead: mostly numbers ("Pelajaran
     * 3", "Rangkuman 9-17", "Bab 2"), which read fine in either language,
     * so it's safe to resolve once here rather than needing a translation
     * key round-trip on the frontend.
     */
    private function practiceNotification(User $user, string $module, string $setKey, int $xp, bool $mastered): UserNotification
    {
        $label = $this->setLabel($module, $setKey);

        return UserNotification::create([
            'user_id' => $user->id,
            'type' => $mastered ? 'lesson_mastered' : 'lesson_completed',
            'title' => $mastered ? 'notifications.lesson_mastered_title' : 'notifications.lesson_completed_title',
            'subtitle' => json_encode([
                'key' => $mastered ? 'notifications.lesson_mastered_subtitle' : 'notifications.lesson_completed_subtitle',
                'lesson' => $label,
                'xp' => $xp,
            ]),
            'icon' => $mastered ? 'tabler-crown' : 'tabler-circle-check-filled',
            'color' => $mastered ? 'warning' : 'success',
        ]);
    }

    private function setLabel(string $module, string $setKey): string
    {
        $isEnglish = app()->getLocale() === 'en';

        if ($module === 'mondaishuu') {
            if (str_starts_with($setKey, 'l')) {
                $n = substr($setKey, 1);

                return $isEnglish ? "Lesson {$n}" : "Pelajaran {$n}";
            }

            $range = str_replace('-', '–', substr($setKey, 1));

            return $isEnglish ? "Review {$range}" : "Rangkuman {$range}";
        }

        if ($module === 'chokai') {
            // set_key is always 'l{n}', one of the 25 pelajaran.
            $n = substr($setKey, 1);

            return $isEnglish ? "Lesson {$n}" : "Pelajaran {$n}";
        }

        if ($module === 'kaiwa') {
            // set_key = id skenario: 'l{pelajaran}-s{urut}' (jalur pelajaran) atau kunci jalur situasi.
            if (preg_match('/^l(\d+)-s(\d+)$/', $setKey, $m))
                return $isEnglish ? "Lesson {$m[1]} · Scenario {$m[2]}" : "Pelajaran {$m[1]} · Skenario {$m[2]}";

            return $isEnglish ? 'Conversation practice' : 'Latihan percakapan';
        }

        if ($module === 'jlpt_test') {
            // Pack bank soal asli: 'private' (N5) dan 'private-n4' (N4) — levelnya dari config.
            if (str_starts_with($setKey, 'private')) {
                $level = (string) config("jlpt.packs.{$setKey}.level", 'N5');

                return $isEnglish ? "JLPT {$level} Test" : "Tes JLPT {$level}";
            }

            // Paket orisinal: pack key looks like 'n5-test-1'.
            $level = strtoupper(explode('-', $setKey)[0] ?? 'N5');
            $n = substr($setKey, (int) strrpos($setKey, '-') + 1);

            return $isEnglish ? "JLPT {$level} Mock Test #{$n}" : "Simulasi JLPT {$level} #{$n}";
        }

        // kaite_oboeru: set_key is always 'l{n}' — one of its 5 chapters (bab).
        $n = substr($setKey, 1);

        return $isEnglish ? "Chapter {$n}" : "Bab {$n}";
    }
}
