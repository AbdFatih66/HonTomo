<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-user mastery tracking for one kanji. This is a deliberate product
 * decision (see docs/kanji-module.md section 10) to differ from the Kana
 * quiz, which stays fully stateless — do not "fix" this to match Kana
 * without checking that doc first.
 *
 * Mastery rule (kept simple on purpose — this is rule-based streak
 * tracking, not a full spaced-repetition scheduler; that's a reasonable
 * upgrade later but out of scope for the first version):
 *  - Every answer updates correct_count/incorrect_count and
 *    last_reviewed_at.
 *  - A correct answer increments current_streak; three in a row
 *    (STREAK_TO_MASTER) marks the kanji 'mastered' and stamps
 *    mastered_at.
 *  - An incorrect answer resets current_streak to 0. If the kanji was
 *    already 'mastered', it demotes back to 'learning' — mastery is not
 *    permanent, the same way most spaced-repetition tools treat a lapse.
 *  - 'new' means no attempt has ever been recorded.
 */
class UserKanjiProgress extends Model
{
    protected $table = 'user_kanji_progress';

    public const STATUS_NEW = 'new';

    public const STATUS_LEARNING = 'learning';

    public const STATUS_MASTERED = 'mastered';

    public const STREAK_TO_MASTER = 3;

    protected $fillable = [
        'user_id', 'kanji_id', 'status', 'correct_count', 'incorrect_count',
        'current_streak', 'last_reviewed_at', 'mastered_at',
    ];

    protected $casts = [
        'last_reviewed_at' => 'datetime',
        'mastered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kanji(): BelongsTo
    {
        return $this->belongsTo(Kanji::class);
    }

    /** Applies one quiz answer's result and persists it. Returns $this for chaining. */
    public function applyResult(bool $correct): self
    {
        $this->last_reviewed_at = now();

        if ($correct) {
            $this->correct_count++;
            $this->current_streak++;

            if ($this->current_streak >= self::STREAK_TO_MASTER && $this->status !== self::STATUS_MASTERED) {
                $this->status = self::STATUS_MASTERED;
                $this->mastered_at = now();
            } elseif ($this->status === self::STATUS_NEW) {
                $this->status = self::STATUS_LEARNING;
            }
        } else {
            $this->incorrect_count++;
            $this->current_streak = 0;

            // A lapse always lands on 'learning' — whether it came from
            // 'new', 'learning', or a demotion from 'mastered' — while
            // keeping the historical mastered_at timestamp intact (not
            // cleared) so "first mastered" stays on record even if the
            // kanji needs review again later.
            $this->status = self::STATUS_LEARNING;
        }

        $this->save();

        return $this;
    }
}
