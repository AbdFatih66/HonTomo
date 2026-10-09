<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fixes a real import bug, not a cosmetic widen: `kanji:import-jmdict` opens
 * JMdict_e.xml with LIBXML_NOENT + XMLReader::SUBST_ENTITIES(true), which
 * deliberately expands JMdict's <pos> entities (&n;, &v1;, etc.) into their
 * full descriptive English text — e.g. "expressions (phrases, clauses,
 * etc.)" (37 chars), "Ichidan verb - kureru special class" (36 chars) — NOT
 * short codes like "n"/"v1" as the original column comment assumed. Almost
 * every <sense> in JMdict carries a <pos> tag, so the old `string(30)`
 * column caused a "Data too long for column" QueryException on nearly
 * every insert attempt — silently swallowed by ImportJmdict's per-entry
 * catch block and reported only as a much smaller "added N vocabulary
 * rows" than expected (in the worst case, effectively 0 new rows once the
 * handful of entries with short enough `pos` text were already imported).
 *
 * 191 comfortably covers every part_of_speech string observed in JMdict's
 * <pos> entity expansions; rounded up to 255 for headroom against any
 * future/uncommon entity JMdict may add.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kanji_vocabulary', function (Blueprint $table) {
            $table->string('part_of_speech', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('kanji_vocabulary', function (Blueprint $table) {
            $table->string('part_of_speech', 30)->nullable()->change();
        });
    }
};
