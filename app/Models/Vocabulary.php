<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vocabulary extends Model
{
    use HasFactory;

    protected $fillable = [
        'japanese', 'hiragana', 'romaji', 'meaning_id', 'meaning_en',
        'category_id', 'jlpt_level', 'difficulty', 'audio_id', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(VocabularyCategory::class, 'category_id');
    }

    public function examples(): HasMany
    {
        return $this->hasMany(VocabularyExample::class);
    }

    public function audio(): BelongsTo
    {
        return $this->belongsTo(Audio::class);
    }

    /** Kanji characters (from the Kanji module) that appear in `japanese`. See Kanji::curriculumWords(). */
    public function kanjis(): BelongsToMany
    {
        return $this->belongsToMany(Kanji::class, 'kanji_word_links');
    }

    /**
     * Which lessons actually use this word, found via LessonQuestion
     * (which already carries both lesson_id and vocabulary_id) rather
     * than a formal hasManyThrough — vocabularies.japanese is written by
     * a human seeder, not queried often enough per-request to be worth a
     * relation that's easy to get backwards.
     */
    public function lessons()
    {
        return Lesson::query()
            ->whereIn('id', LessonQuestion::where('vocabulary_id', $this->id)->pluck('lesson_id'))
            ->get();
    }

    public function meaning(): string
    {
        return app()->getLocale() === 'en' ? $this->meaning_en : $this->meaning_id;
    }
}
