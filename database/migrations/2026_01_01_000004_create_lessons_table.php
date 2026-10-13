<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->string('title_id');
            $table->string('title_en');
            // hiragana | katakana | vocabulary | grammar | kanji | listening | reading | mixed
            $table->string('category', 30)->default('mixed');
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('xp_reward')->default(10);
            $table->foreignId('prerequisite_lesson_id')->nullable()
                ->constrained('lessons')->nullOnDelete();
            $table->unsignedInteger('required_accuracy')->default(0); // % needed to "master"
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['unit_id', 'order']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
