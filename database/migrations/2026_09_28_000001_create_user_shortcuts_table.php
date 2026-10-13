<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The navbar "Shortcuts" grid, persisted per account instead of the
 * hardcoded demo list that used to ship with the template. `page_key`
 * is the route name (e.g. 'learn', 'mondaishuu') from
 * resources/js/navigation/vertical/index.js — the frontend resolves the
 * icon/title/subtitle from that shared list, this table only stores which
 * pages a user picked and in what order (`position`, reorderable by
 * dragging in the UI).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_shortcuts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('page_key', 60);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'page_key']);
            $table->index(['user_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_shortcuts');
    }
};
