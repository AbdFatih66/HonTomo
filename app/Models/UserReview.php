<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'user_vocabulary_id', 'was_correct', 'response_time_ms',
    ];

    protected $casts = [
        'was_correct' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userVocabulary(): BelongsTo
    {
        return $this->belongsTo(UserVocabulary::class);
    }
}
