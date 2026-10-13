<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLesson extends Model
{
    use HasFactory;

    public const STATUS_LOCKED = 'locked';
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_MASTERED = 'mastered';

    protected $fillable = [
        'user_id', 'lesson_id', 'status', 'best_accuracy', 'attempts',
        'xp_earned', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
