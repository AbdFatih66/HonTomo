<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_question_id')->constrained()->cascadeOnDelete();
            $table->string('label_id');
            $table->string('label_en');
            $table->text('japanese_text')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index('lesson_question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
