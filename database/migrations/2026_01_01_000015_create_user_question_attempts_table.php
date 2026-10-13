<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_question_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_lesson_id')->nullable()
                ->constrained('user_lessons')->nullOnDelete();
            $table->text('given_answer')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('time_taken_ms')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'lesson_question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_question_attempts');
    }
};
