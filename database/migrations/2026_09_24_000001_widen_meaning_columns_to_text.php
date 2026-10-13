<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fixes a real bug, not a cosmetic widen — same class of issue as
 * 2026_09_23_000003_widen_part_of_speech_on_kanji_vocabulary_table.php.
 *
 * `kanjis.meaning_id`/`meaning_en` and `kanji_vocabulary.meaning_id`/
 * `meaning_en` were all created as `string()` (VARCHAR(255)). JMdict
 * glosses are not always short words — some senses are full sentence-
 * length definitions — and KanjiMeaningTranslator's word-by-word output
 * for those can comfortably exceed 255 characters once every token is
 * expanded/translated. That caused:
 *
 *   SQLSTATE[22001]: String data, right truncated: 1406 Data too long
 *   for column 'meaning_id' at row 1
 *
 * from `kanji:retranslate-meanings` (and would hit ImportKanjidic /
 * ImportJmdict on initial import too, just less visibly if those
 * commands swallow per-row exceptions).
 *
 * TEXT (up to 65,535 chars in MySQL) removes the arbitrary cap entirely
 * — there's no natural length limit on a translated dictionary
 * definition, so a fixed VARCHAR size will always be a guess that can
 * eventually be exceeded again.
 *
 * Uses raw SQL (MODIFY COLUMN) instead of Schema::table(...)->change()
 * so this doesn't require the doctrine/dbal package, which this project
 * does not have installed. This is MySQL-specific syntax.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE kanjis MODIFY meaning_id TEXT NOT NULL');
        DB::statement('ALTER TABLE kanjis MODIFY meaning_en TEXT NOT NULL');

        DB::statement('ALTER TABLE kanji_vocabulary MODIFY meaning_id TEXT NULL');
        DB::statement('ALTER TABLE kanji_vocabulary MODIFY meaning_en TEXT NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE kanjis MODIFY meaning_id VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE kanjis MODIFY meaning_en VARCHAR(255) NOT NULL');

        DB::statement('ALTER TABLE kanji_vocabulary MODIFY meaning_id VARCHAR(255) NULL');
        DB::statement('ALTER TABLE kanji_vocabulary MODIFY meaning_en VARCHAR(255) NOT NULL');
    }
};
