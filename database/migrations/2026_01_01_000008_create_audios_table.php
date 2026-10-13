<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audios', function (Blueprint $table) {
            $table->id();
            $table->string('file_path');
            $table->string('label')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->string('voice')->nullable(); // e.g. male_jp, female_jp
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audios');
    }
};
