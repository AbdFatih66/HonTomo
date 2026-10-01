<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vocabularies', function (Blueprint $table) {
            $table->id();
            $table->string('japanese');       // 食べる
            $table->string('hiragana');       // たべる
            $table->string('romaji');         // taberu
            $table->string('meaning_id');     // Makan
            $table->string('meaning_en');     // Eat
            $table->foreignId('category_id')->nullable()
                ->constrained('vocabulary_categories')->nullOnDelete();
            $table->string('jlpt_level', 10)->default('N5');
            $table->unsignedInteger('difficulty')->default(1); // 1-5
            $table->foreignId('audio_id')->nullable()
                ->constrained('audios')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('jlpt_level');
            $table->index('category_id');
            // SQLite (database test) tidak mendukung fulltext index; MySQL produksi tetap memakainya.
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->fullText(['japanese', 'hiragana', 'romaji', 'meaning_id', 'meaning_en'], 'vocabularies_fulltext');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vocabularies');
    }
};
