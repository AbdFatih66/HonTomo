<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Added as a separate migration since `levels` didn't exist yet
    // when the users table was first extended.
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('current_level_id')
                ->references('id')->on('levels')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_level_id']);
        });
    }
};
