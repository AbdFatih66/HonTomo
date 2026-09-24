<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kanjis', function (Blueprint $table) {
            $table->id();
            $table->string('character', 5);            // 食
            $table->json('onyomi')->nullable();         // ["ショク","シ"]
            $table->json('kunyomi')->nullable();         // ["た.べる","く.う"]
            $table->string('meaning_id');
            $table->string('meaning_en');
            $table->unsignedInteger('stroke_count')->nullable();
            $table->string('jlpt_level', 10)->default('N5');
            $table->string('stroke_order_svg')->nullable(); // path/URL to stroke order asset
            $table->timestamps();

            $table->unique('character');
            $table->index('jlpt_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanjis');
    }
};
