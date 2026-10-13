<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends the existing `kanjis` table (created empty in
 * 2026_01_01_000012_create_kanjis_table.php and already referenced by
 * LessonQuestion::belongsTo(Kanji::class)) instead of creating a
 * parallel table. Purely additive — every new column is nullable or has
 * a safe default, so this is safe to run against a database that
 * already has rows in `kanjis`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kanjis', function (Blueprint $table) {
            // Kyouiku/school grade (1-6), null for jouyou-only or unclassified kanji.
            $table->unsignedTinyInteger('grade')->nullable()->after('jlpt_level');

            // KANJIDIC2 frequency rank among newspaper text (lower = more common), null if unranked.
            $table->unsignedInteger('frequency_rank')->nullable()->after('grade');

            // Display/learning order within a level, independent of DB id.
            $table->unsignedInteger('order')->default(0)->after('frequency_rank');

            // Which JLPT-level classification source `jlpt_level` came from —
            // see docs/kanji-module.md: KANJIDIC2's own <jlpt> tag is a stale
            // 1-4 scale and is NOT used as the source of truth here.
            $table->string('jlpt_source', 30)->nullable()->after('jlpt_level');

            // True once meaning_id has been checked/approved by a human;
            // false means it came from the automatic EN->ID glossary and may
            // be imprecise. Surfaced in the admin UI as a "needs review" flag.
            $table->boolean('needs_review_id')->default(false)->after('meaning_id');

            // Per-field manual-override lock, e.g. {"meaning_id": true} —
            // import commands must skip any field named here so a human
            // correction is never silently overwritten by a re-import.
            $table->json('locked_fields')->nullable()->after('needs_review_id');
        });
    }

    public function down(): void
    {
        Schema::table('kanjis', function (Blueprint $table) {
            $table->dropColumn([
                'grade', 'frequency_rank', 'order', 'jlpt_source',
                'needs_review_id', 'locked_fields',
            ]);
        });
    }
};
