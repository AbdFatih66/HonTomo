<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Progres per-user untuk satu skenario Kaiwa, dikunci oleh `set_key`
 * (id skenario di public/data/kaiwa/*.json) — materinya tidak ada di
 * database, sama seperti UserChokaiProgress.
 */
class UserKaiwaProgress extends Model
{
    protected $table = 'user_kaiwa_progress';

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
     * `done` bersifat permanen dan `crown` hanya bisa didapat (tidak hilang
     * bila main ulang tanpa sempurna).
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
