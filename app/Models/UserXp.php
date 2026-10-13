<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserXp extends Model
{
    use HasFactory;

    protected $table = 'user_xp';

    protected $fillable = ['user_id', 'amount', 'source', 'source_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
