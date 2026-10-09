<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserJlptTestSection extends Model
{
    protected $table = 'user_jlpt_test_sections';

    protected $fillable = [
        'attempt_id', 'section', 'started_at', 'submitted_at', 'answers',
        'score', 'total', 'breakdown', 'time_spent_seconds', 'timed_out',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'answers' => 'array',
        'breakdown' => 'array',
        'timed_out' => 'boolean',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(UserJlptTestAttempt::class, 'attempt_id');
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    public function isRunning(): bool
    {
        return $this->started_at !== null && $this->submitted_at === null;
    }
}
