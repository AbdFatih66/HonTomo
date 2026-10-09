<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persistent per-user kanji progress — see App\Models\UserKanjiProgress
 * for the mastery rule this table backs. Deliberately separate from how
 * Kana works (Kana's quiz is fully stateless, nothing persisted) — this
 * was an explicit product decision, not an oversight; see
 * docs/kanji-module.md section 10 before "fixing" Kana to match or
 * simplifying this back to stateless.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_kanji_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kanji_id')->constrained('kanjis')->cascadeOnDelete();

            $table->string('status', 20)->default('new'); // new | learning | mastered — see UserKanjiProgress::applyResult()
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('incorrect_count')->default(0);
            $table->unsignedInteger('current_streak')->default(0);
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamp('mastered_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'kanji_id']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_kanji_progress');
    }
};
