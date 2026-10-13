<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_vocabularies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vocabulary_id')->constrained()->cascadeOnDelete();

            // new | learning | familiar | mastered
            $table->string('mastery_level', 20)->default('new');

            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamp('next_review_at')->nullable();
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('wrong_count')->default(0);
            $table->unsignedInteger('ease_factor')->default(250); // x100, SM-2 style, future use
            $table->unsignedInteger('interval_days')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'vocabulary_id']);
            $table->index('next_review_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_vocabularies');
    }
};
