<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KanjiVocabulary extends Model
{
    protected $table = 'kanji_vocabulary';

    protected $fillable = [
        'kanji_id',
        'word',
        'reading',
        'meaning_en',
        'meaning_id',
        'needs_review_id',
        'part_of_speech',
        'source',
        'order',
    ];

    protected $casts = [
        'needs_review_id' => 'boolean',
        'order' => 'integer',
    ];

    public function kanji(): BelongsTo
    {
        return $this->belongsTo(Kanji::class);
    }

    public function meaning(): string
    {
        $localized = app()->getLocale() === 'en' ? $this->meaning_en : $this->meaning_id;

        return $localized ?: $this->meaning_en;
    }
}
