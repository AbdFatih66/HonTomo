<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserVocabulary extends Model
{
    use HasFactory;

    public const MASTERY_NEW = 'new';
    public const MASTERY_LEARNING = 'learning';
    public const MASTERY_FAMILIAR = 'familiar';
    public const MASTERY_MASTERED = 'mastered';

    protected $fillable = [
        'user_id', 'vocabulary_id', 'mastery_level', 'last_reviewed_at',
        'next_review_at', 'correct_count', 'wrong_count', 'ease_factor', 'interval_days',
    ];

    protected $casts = [
        'last_reviewed_at' => 'datetime',
        'next_review_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vocabulary(): BelongsTo
    {
        return $this->belongsTo(Vocabulary::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(UserReview::class);
    }
}
