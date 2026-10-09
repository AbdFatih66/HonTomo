<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningPath extends Model
{
    use HasFactory;

    protected $fillable = ['level_id', 'name_id', 'name_en', 'order'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(LearningPathNode::class)->orderBy('order');
    }
}
