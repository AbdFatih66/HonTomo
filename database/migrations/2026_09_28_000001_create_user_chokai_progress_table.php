<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persistent per-user progress for the Chokai (listening comprehension)
 * sets. Mirrors user_mondaishuu_progress exactly — the Chokai sets are
 * hardcoded in the frontend (resources/js/pages/chokai/index.vue LESSONS
 * array) rather than rows in a table, so `set_key` (e.g. 'l1') is the
 * frontend's own key and the only identifier both sides share.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_chokai_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('set_key', 20); // e.g. 'l1' .. 'l25'

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
        Schema::dropIfExists('user_chokai_progress');
    }
};
