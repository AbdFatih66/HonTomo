<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Append-only: audit rows are never updated, so no `updated_at` column. */
class AuthAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'action', 'ip_address', 'user_agent', 'meta', 'created_at'];

    protected function casts(): array
    {
        return ['meta' => 'array', 'created_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
