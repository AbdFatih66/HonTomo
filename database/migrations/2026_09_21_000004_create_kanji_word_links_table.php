<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Connects the Kanji module to the existing lesson/vocabulary curriculum
 * (`vocabularies`, `lessons`, `lesson_questions` — all pre-existing, not
 * part of the Kanji module). A word can contain more than one kanji
 * (e.g. 大学 has both 大 and 学), so this is a plain many-to-many pivot,
 * not a column on either side. Populated by `kanji:link-vocabulary`,
 * never by hand — see that command's docblock.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kanji_word_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kanji_id')->constrained('kanjis')->cascadeOnDelete();
            $table->foreignId('vocabulary_id')->constrained('vocabularies')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['kanji_id', 'vocabulary_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanji_word_links');
    }
};
