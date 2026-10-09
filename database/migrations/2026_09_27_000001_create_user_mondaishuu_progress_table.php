<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persistent per-user progress for the Mondaishuu quiz sets (Pelajaran
 * 1–25 + the 4 "rangkuman" review sets). Mirrors the shape of
 * user_kanji_progress, but Mondaishuu sets are hardcoded in the frontend
 * (resources/js/pages/mondaishuu/index.vue LESSONS/REVIEWS arrays) rather
 * than rows in the `lessons` table, so there is no lesson_id to key on —
 * `set_key` (e.g. 'l1', 'r9-17') is the frontend's own tab key and is the
 * only identifier both sides share.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_mondaishuu_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('set_key', 20); // e.g. 'l1' .. 'l25', 'r1-8', 'r9-17', 'r18-25', 'r1-25'

            $table->boolean('done')->default(false);
            $table->boolean('crown')->default(false); // finished with zero wrong answers at least once
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('last_played_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'set_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_mondaishuu_progress');
    }
};
