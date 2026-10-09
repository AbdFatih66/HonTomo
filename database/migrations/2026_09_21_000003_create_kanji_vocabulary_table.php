<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kanji_vocabulary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kanji_id')->constrained('kanjis')->cascadeOnDelete();

            $table->string('word');           // 学校 (kanji form, as written)
            $table->string('reading');        // がっこう
            $table->string('meaning_en');
            $table->string('meaning_id')->nullable();
            $table->boolean('needs_review_id')->default(false);

            $table->string('part_of_speech', 30)->nullable(); // widened to 255 in 2026_09_23_000003 — kanji:import-jmdict stores JMdict's expanded English pos text (e.g. "expressions (phrases, clauses, etc.)"), not a short code — nullable: not every source provides it
            $table->string('source', 20)->default('jmdict');
            $table->unsignedInteger('order')->default(0); // by JMdict priority/frequency, most common first

            $table->timestamps();

            $table->unique(['kanji_id', 'word', 'reading']);
            $table->index('kanji_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanji_vocabulary');
    }
};
