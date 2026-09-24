<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kanji extends Model
{
    use HasFactory;

    protected $fillable = [
        'character', 'onyomi', 'kunyomi', 'meaning_id', 'meaning_en',
        'needs_review_id', 'locked_fields',
        'stroke_count', 'jlpt_level', 'jlpt_source', 'grade', 'frequency_rank',
        'order', 'stroke_order_svg', 'earliest_lesson_id', 'earliest_lesson_order',
    ];

    protected $casts = [
        'onyomi' => 'array',
        'kunyomi' => 'array',
        'needs_review_id' => 'boolean',
        'locked_fields' => 'array',
        'grade' => 'integer',
        'frequency_rank' => 'integer',
        'order' => 'integer',
        'earliest_lesson_order' => 'integer',
    ];

    public function meaning(): string
    {
        return app()->getLocale() === 'en' ? $this->meaning_en : $this->meaning_id;
    }

    public function strokeData(): HasOne
    {
        return $this->hasOne(KanjiStroke::class);
    }

    public function vocabulary(): HasMany
    {
        return $this->hasMany(KanjiVocabulary::class)->orderBy('order');
    }

    /**
     * Words from the app's own lesson curriculum (`vocabularies`, seeded
     * from Minna no Nihongo — see /database/seeders/VocabularySeeder.php
     * and friends) that contain this character. Distinct from vocabulary()
     * above, which is JMdict example data: this is "where does the
     * learner actually meet this kanji in their lessons", populated by
     * `php artisan kanji:link-vocabulary` (see that command's docblock).
     */
    public function curriculumWords(): BelongsToMany
    {
        return $this->belongsToMany(Vocabulary::class, 'kanji_word_links');
    }

    /**
     * The earliest Pelajaran (by curriculum order) this kanji is met in,
     * per earliest_lesson_id — see `php artisan kanji:sync-lesson-order`.
     * Null means this kanji has no linked curriculum vocabulary yet.
     */
    public function earliestLesson(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'earliest_lesson_id');
    }

    public function lessonQuestions(): HasMany
    {
        return $this->hasMany(LessonQuestion::class);
    }

    public function userProgress(): HasMany
    {
        return $this->hasMany(UserKanjiProgress::class);
    }

    /** True if $field was manually corrected by an admin and must survive re-imports. */
    public function isFieldLocked(string $field): bool
    {
        return (bool) ($this->locked_fields[$field] ?? false);
    }
}
