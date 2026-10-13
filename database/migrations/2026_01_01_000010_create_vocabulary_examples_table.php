<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vocabulary_examples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vocabulary_id')->constrained()->cascadeOnDelete();
            $table->text('sentence_japanese');   // 私はりんごを食べます。
            $table->text('sentence_reading');    // わたしはりんごをたべます。
            $table->text('translation_id');
            $table->text('translation_en');
            $table->foreignId('audio_id')->nullable()
                ->constrained('audios')->nullOnDelete();
            $table->timestamps();

            $table->index('vocabulary_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vocabulary_examples');
    }
};
