<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserQuestionAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'lesson_question_id', 'user_lesson_id',
        'given_answer', 'is_correct', 'time_taken_ms',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(LessonQuestion::class, 'lesson_question_id');
    }

    public function userLesson(): BelongsTo
    {
        return $this->belongsTo(UserLesson::class);
    }
}
