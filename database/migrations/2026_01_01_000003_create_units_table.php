<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            $table->string('title_id');
            $table->string('title_en');
            $table->text('description_id')->nullable();
            $table->text('description_en')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->string('icon')->nullable();
            $table->foreignId('prerequisite_unit_id')->nullable()
                ->constrained('units')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['level_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
