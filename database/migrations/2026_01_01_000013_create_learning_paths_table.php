<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Optional explicit ordering / branching layer on top of units+lessons,
        // used to render the visual "path" (nodes + connectors) shown to students.
        Schema::create('learning_paths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            $table->string('name_id');
            $table->string('name_en');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('learning_path_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_path_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('node_type', 20)->default('lesson'); // lesson | checkpoint | chest
            $table->unsignedInteger('position_x')->default(0);
            $table->unsignedInteger('position_y')->default(0);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_path_nodes');
        Schema::dropIfExists('learning_paths');
    }
};
