<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id', 'question_type', 'prompt_id', 'prompt_en',
        'japanese_text', 'romaji', 'vocabulary_id', 'kanji_id', 'grammar_id',
        'audio_id', 'payload', 'correct_answer', 'order', 'difficulty', 'is_active',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_active' => 'boolean',
    ];

    public const TYPES = [
        'multiple_choice', 'listening', 'matching', 'translation', 'typing',
        'sentence_ordering', 'flashcard', 'writing', 'speaking', 'grammar',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function vocabulary(): BelongsTo
    {
        return $this->belongsTo(Vocabulary::class);
    }

    public function kanji(): BelongsTo
    {
        return $this->belongsTo(Kanji::class);
    }

    public function grammar(): BelongsTo
    {
        return $this->belongsTo(Grammar::class);
    }

    public function audio(): BelongsTo
    {
        return $this->belongsTo(Audio::class);
    }

    public function prompt(): ?string
    {
        return app()->getLocale() === 'en' ? $this->prompt_en : $this->prompt_id;
    }
}
