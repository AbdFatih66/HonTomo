<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Log of individual review events (for history / analytics),
        // separate from the current-state summary in user_vocabularies.
        Schema::create('user_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_vocabulary_id')->constrained()->cascadeOnDelete();
            $table->boolean('was_correct');
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_reviews');
    }
};
