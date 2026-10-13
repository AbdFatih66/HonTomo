<?php

use App\Models\Kanji;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Fixes a bug from the ORIGINAL kanjis table migration (before this Kanji
 * module existed): `jlpt_level` was created with `->default('N5')`. When
 * `kanji:import-kanjidic` runs against the full kanjidic2.xml (~13,000
 * characters) and a character ISN'T in jlpt-kanji-levels.json (i.e. it's
 * genuinely not one of the ~990 official N5-N1 kanji), the command
 * deliberately leaves `jlpt_level` out of the insert so it stays null —
 * but MySQL then silently fills the column's own default, 'N5', instead
 * of null. Result: thousands of obscure/complex kanji get mislabeled
 * "N5" and flood the N5 tab (see the "kenapa kanji N5 aneh-aneh/rumit,
 * 246 halaman" report this fixes).
 *
 * Two parts:
 *  1. Drop the column default so future inserts that omit jlpt_level
 *     correctly get NULL (belt-and-suspenders — ImportKanjidic is also
 *     fixed to always pass jlpt_level explicitly, including null).
 *  2. Backfill: any existing row that was defaulted rather than
 *     genuinely classified has `jlpt_source` NULL (both KanjiSeeder and
 *     ImportKanjidic ALWAYS set jlpt_source together with a real
 *     jlpt_level — see their code) — so `jlpt_level IS NOT NULL AND
 *     jlpt_source IS NULL` identifies exactly the mislabeled rows. Done
 *     in PHP (not raw JSON SQL) so it behaves the same on every DB
 *     driver. Skips anything with `locked_fields->jlpt_level` true, in
 *     case an admin manually corrected/confirmed a level by hand.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kanjis', function ($table) {
            $table->string('jlpt_level', 10)->nullable()->default(null)->change();
        });

        Kanji::whereNotNull('jlpt_level')
            ->whereNull('jlpt_source')
            ->chunkById(500, function ($kanjis) {
                foreach ($kanjis as $kanji) {
                    if ($kanji->isFieldLocked('jlpt_level')) {
                        continue;
                    }

                    $kanji->forceFill(['jlpt_level' => null])->save();
                }
            });
    }

    public function down(): void
    {
        Schema::table('kanjis', function ($table) {
            $table->string('jlpt_level', 10)->nullable(false)->default('N5')->change();
        });
    }
};
