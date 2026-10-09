<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id', 'title_id', 'title_en', 'category', 'order', 'xp_reward',
        'prerequisite_lesson_id', 'required_accuracy', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(LessonQuestion::class)->orderBy('order');
    }

    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'prerequisite_lesson_id');
    }

    public function userLessons(): HasMany
    {
        return $this->hasMany(UserLesson::class);
    }

    public function title(): string
    {
        return app()->getLocale() === 'en' ? $this->title_en : $this->title_id;
    }

    /**
     * Global curriculum position of every lesson: level.order*100000 +
     * unit.order*1000 + lesson.order. Single source of truth for "which
     * lesson comes first" across the whole app — used by
     * `kanji:sync-lesson-order` (kanjis.earliest_lesson_order) and by
     * KanjiController (to find how far a user has actually progressed).
     * Keep both callers pointed at this method rather than each
     * recomputing the same formula, so the two never drift apart.
     *
     * @return \Illuminate\Support\Collection<int, int> lesson_id => global order
     */
    public static function globalOrderMap(): \Illuminate\Support\Collection
    {
        return static::with('unit.level')->get()
            ->mapWithKeys(fn (Lesson $lesson) => [
                $lesson->id => (($lesson->unit->level->order ?? 0) * 100000)
                    + (($lesson->unit->order ?? 0) * 1000)
                    + $lesson->order,
            ]);
    }
}
