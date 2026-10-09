<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VocabularyExample extends Model
{
    use HasFactory;

    protected $fillable = [
        'vocabulary_id', 'sentence_japanese', 'sentence_reading',
        'translation_id', 'translation_en', 'audio_id',
    ];

    public function vocabulary(): BelongsTo
    {
        return $this->belongsTo(Vocabulary::class);
    }

    public function audio(): BelongsTo
    {
        return $this->belongsTo(Audio::class);
    }

    public function translation(): string
    {
        return app()->getLocale() === 'en' ? $this->translation_en : $this->translation_id;
    }
}
