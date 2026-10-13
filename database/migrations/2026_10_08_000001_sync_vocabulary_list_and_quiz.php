<?php

use App\Services\VocabularyQuizSync;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * One-off: every Kosakata word gets a quiz question and every quiz word is in
 * the Kosakata list. Re-run any time with `php artisan vocabulary:sync-quiz`.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['vocabularies', 'vocabulary_categories', 'lessons', 'lesson_questions', 'question_options', 'units', 'levels'] as $table) {
            if (! Schema::hasTable($table)) {
                return;
            }
        }

        app(VocabularyQuizSync::class)->run();
    }

    public function down(): void
    {
        // Additive data sync; nothing to undo.
    }
};
