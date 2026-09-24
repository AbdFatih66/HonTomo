<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `users.daily_goal_xp` was created as a DATE column, so its name promised an
 * XP number but it can only hold a date. Nothing in the app ever wrote to it;
 * today's XP is computed from the `user_xp` ledger instead.
 *
 * The column is kept (no data is dropped) and renamed to what it can actually
 * hold: the date the daily goal was last reset/reached. The earlier migration
 * is left untouched because it has already run on existing databases.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'daily_goal_xp') && ! Schema::hasColumn('users', 'daily_goal_date')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('daily_goal_xp', 'daily_goal_date');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'daily_goal_date') && ! Schema::hasColumn('users', 'daily_goal_xp')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('daily_goal_date', 'daily_goal_xp');
            });
        }
    }
};
