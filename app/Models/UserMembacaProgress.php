<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Progres per-user untuk satu bacaan Membaca, berkunci `set_key` milik
 * frontend (mis. 'n3l1') — lihat UserKaiteOboeruProgress, yang ditiru persis
 * (aturan mahkota sama, alasan tidak memakai lesson_id sama).
 */
class UserMembacaProgress extends Model
{
    protected $table = 'user_membaca_progress';

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
     * `done` bersifat tetap setelah true, dan `crown` hanya bisa didapat
     * (tidak pernah hilang) — mengulang tanpa sempurna tidak menghapusnya.
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
