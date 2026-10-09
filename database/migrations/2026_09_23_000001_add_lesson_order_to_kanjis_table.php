<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets kanji be sorted/filtered by where the learner actually meets them
 * in the lesson curriculum (Pelajaran/Bab order), not only by JLPT level.
 * Purely additive/nullable — safe on a database that already has kanji
 * rows. Populated by `php artisan kanji:sync-lesson-order` (run it after
 * every `kanji:link-vocabulary`), NOT computed on every request: with
 * 990+ kanji this needs to stay a cheap indexed column, not a live join
 * through kanji_word_links -> vocabularies -> lesson_questions -> lessons.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kanjis', function (Blueprint $table) {
            // Earliest lesson (by global curriculum order) whose vocabulary
            // links to this kanji via kanji_word_links. Null = this kanji
            // hasn't shown up in any seeded lesson vocabulary yet (common
            // for higher JLPT levels before the curriculum reaches them).
            $table->foreignId('earliest_lesson_id')->nullable()->after('order')
                ->constrained('lessons')->nullOnDelete();

            // Denormalized global order (level.order*100000 + unit.order*1000
            // + lesson.order) of earliest_lesson_id, cached here so sorting/
            // filtering kanji by curriculum position is a plain indexed
            // column comparison instead of joining through 3 tables per row.
            $table->unsignedInteger('earliest_lesson_order')->nullable()->after('earliest_lesson_id');

            $table->index('earliest_lesson_order');
        });
    }

    public function down(): void
    {
        Schema::table('kanjis', function (Blueprint $table) {
            $table->dropConstrainedForeignId('earliest_lesson_id');
            $table->dropColumn(['earliest_lesson_order']);
        });
    }
};
