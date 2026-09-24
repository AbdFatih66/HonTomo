<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KanjiStroke extends Model
{
    protected $fillable = [
        'kanji_id', 'strokes', 'medians', 'stroke_count', 'source', 'source_ref',
    ];

    protected $casts = [
        'strokes' => 'array',
        'medians' => 'array',
        'stroke_count' => 'integer',
    ];

    public function kanji(): BelongsTo
    {
        return $this->belongsTo(Kanji::class);
    }

    /**
     * Shape HanziWriter's charDataLoader actually expects: {strokes, medians}.
     * Keeping this here (rather than leaking column names to the frontend)
     * means the storage layout can change without touching the JS side.
     */
    public function toHanziWriterFormat(): array
    {
        return [
            'strokes' => $this->strokes,
            'medians' => $this->medians,
        ];
    }
}
