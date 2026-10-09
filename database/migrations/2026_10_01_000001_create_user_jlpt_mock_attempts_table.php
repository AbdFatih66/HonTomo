<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * History of finished JLPT mock-test attempts (Simulasi JLPT).
 *
 * Like Mondaishuu/Chokai, the question bank is a static file
 * (public/data/jlpt-mock/{test_key}.json) — only the RESULT of each attempt is
 * stored here. Unlike those, a test can be taken many times and every
 * attempt is kept (one row per attempt) so the user can see a history.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_jlpt_mock_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('test_key', 40);              // e.g. 'n5-test-1'
            $table->string('level', 4)->default('N5');

            // raw correct answers per section
            $table->unsignedSmallInteger('vocab_correct');
            $table->unsignedSmallInteger('grammar_correct');
            $table->unsignedSmallInteger('listening_correct');

            // scaled (estimated) scores, computed server-side
            $table->unsignedSmallInteger('score_language'); // 0-120 (vocab + grammar/reading)
            $table->unsignedSmallInteger('score_listening'); // 0-60
            $table->unsignedSmallInteger('score_total');     // 0-180
            $table->boolean('passed')->default(false);

            $table->boolean('strict')->default(true);       // true = exam conditions (timer, audio once)
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->json('detail')->nullable();             // per-mondai correct/total

            $table->timestamp('completed_at')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'test_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_jlpt_mock_attempts');
    }
};
