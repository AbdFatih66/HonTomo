<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanaCharacter extends Model
{
    protected $fillable = [
        'script', 'character', 'romaji', 'type', 'row', 'column',
        'base_character_id', 'stroke_count', 'stroke_order', 'stroke_order_en',
        'usage_note_id', 'usage_note_en', 'order',
    ];

    protected $casts = [
        'stroke_order' => 'array',
        'stroke_order_en' => 'array',
        'column' => 'integer',
        'stroke_count' => 'integer',
        'order' => 'integer',
    ];

    public function base(): BelongsTo
    {
        return $this->belongsTo(self::class, 'base_character_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(self::class, 'base_character_id');
    }

    public function strokeOrder(): array
    {
        return app()->getLocale() === 'en' ? $this->stroke_order_en : $this->stroke_order;
    }

    public function usageNote(): ?string
    {
        return app()->getLocale() === 'en' ? $this->usage_note_en : $this->usage_note_id;
    }
}
