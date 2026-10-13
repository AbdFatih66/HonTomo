<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kept separate from `kanjis` (rather than a JSON column there) on purpose:
 * this data is only needed on the character-detail/writing-quiz screen, not
 * when listing or quizzing kanji by meaning/reading, so keeping it in its
 * own table means the heavier payload is never pulled in by a plain
 * `Kanji::query()` index/list call — the frontend fetches it lazily per
 * character (see docs/kanji-module.md, "Skala data").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kanji_strokes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kanji_id')->unique()->constrained('kanjis')->cascadeOnDelete();

            // Each: an SVG path `d` string, already transformed into
            // HanziWriter's 1024x1024 / Y-up coordinate space.
            $table->json('strokes');

            // Each: an array of [x, y] points approximating that stroke's
            // centerline, same coordinate space — used by HanziWriter for
            // stroke-direction checking in quiz mode.
            $table->json('medians');

            $table->unsignedInteger('stroke_count');
            $table->string('source', 20)->default('kanjivg');
            $table->string('source_ref', 40)->nullable(); // e.g. KanjiVG file id, for traceability
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanji_strokes');
    }
};
