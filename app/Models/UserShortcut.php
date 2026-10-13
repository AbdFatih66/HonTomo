<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserShortcut extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'page_key', 'position'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
