<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grammars', function (Blueprint $table) {
            $table->id();
            $table->string('title_id');
            $table->string('title_en');
            $table->string('pattern');                 // e.g. 〜は〜です
            $table->text('explanation_id');
            $table->text('explanation_en');
            $table->text('example_japanese')->nullable();
            $table->text('example_reading')->nullable();
            $table->text('example_translation_id')->nullable();
            $table->text('example_translation_en')->nullable();
            $table->string('jlpt_level', 10)->default('N5');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index('jlpt_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grammars');
    }
};
