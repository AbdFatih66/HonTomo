<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'current_streak', 'longest_streak',
        'last_activity_date', 'streak_freeze_available',
    ];

    protected $casts = [
        'last_activity_date' => 'date',
        'streak_freeze_available' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
