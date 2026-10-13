<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningPathNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_path_id', 'lesson_id', 'node_type',
        'position_x', 'position_y', 'order',
    ];

    public function path(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class, 'learning_path_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
