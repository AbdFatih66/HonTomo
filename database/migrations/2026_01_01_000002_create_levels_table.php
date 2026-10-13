<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // e.g. N5, N4
            $table->string('name_id');
            $table->string('name_en');
            $table->text('description_id')->nullable();
            $table->text('description_en')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
