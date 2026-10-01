<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-user progress for one Chokai (listening) set, keyed by the
 * frontend's own `set_key` — the question/audio bank lives in the Vue
 * file, not the database, same approach as UserMondaishuuProgress.
 */
class UserChokaiProgress extends Model
{
    protected $table = 'user_chokai_progress';

    protected $fillable = [
        'user_id', 'set_key', 'done', 'crown', 'attempts', 'last_played_at',
    ];

    protected $casts = [
        'done' => 'boolean',
        'crown' => 'boolean',
        'last_played_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * `done` is sticky once true, and `crown` is only ever earned (never
     * lost) — finishing again without a crown-run doesn't erase an
     * earlier one.
     */
    public function applyResult(bool $perfect): self
    {
        $this->done = true;
        $this->crown = $this->crown || $perfect;
        $this->attempts++;
        $this->last_played_at = now();
        $this->save();

        return $this;
    }
}
