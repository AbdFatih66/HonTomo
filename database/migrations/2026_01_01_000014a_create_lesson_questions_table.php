<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();

            // multiple_choice | listening | matching | translation | typing
            // sentence_ordering | flashcard | writing | speaking
            $table->string('question_type', 30);

            $table->text('prompt_id')->nullable();
            $table->text('prompt_en')->nullable();

            // Main Japanese content being tested (character, word, sentence)
            $table->text('japanese_text')->nullable();
            $table->string('romaji')->nullable();

            $table->foreignId('vocabulary_id')->nullable()
                ->constrained('vocabularies')->nullOnDelete();
            $table->foreignId('kanji_id')->nullable()
                ->constrained('kanjis')->nullOnDelete();
            $table->foreignId('grammar_id')->nullable()
                ->constrained('grammars')->nullOnDelete();
            $table->foreignId('audio_id')->nullable()
                ->constrained('audios')->nullOnDelete();

            // Free-form JSON payload for type-specific data, e.g.
            // sentence_ordering tokens, matching pairs, typing accepted answers
            $table->json('payload')->nullable();

            $table->string('correct_answer')->nullable();

            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('difficulty')->default(1); // 1-5
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['lesson_id', 'order']);
            $table->index('question_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_questions');
    }
};
