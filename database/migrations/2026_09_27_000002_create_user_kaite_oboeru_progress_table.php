<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persistent per-user progress for the Kaite Oboeru (書いて覚える) writing
 * sets. Same shape as user_mondaishuu_progress and the same reasoning:
 * the 20 chapters are hardcoded in the frontend (resources/js/pages/
 * kaite-oboeru/index.vue LESSONS array), not rows in the `lessons` table,
 * so `set_key` (the frontend's own key, e.g. 'l1'..'l20') is the only
 * identifier both sides share.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_kaite_oboeru_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('set_key', 20); // e.g. 'l1' .. 'l20'

            $table->boolean('done')->default(false);
            $table->boolean('crown')->default(false); // finished with zero wrong answers at least once
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('last_played_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'set_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_kaite_oboeru_progress');
    }
};
