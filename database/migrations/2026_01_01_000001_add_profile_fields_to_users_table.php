<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ui_language', 5)->default('id')->after('email');
            $table->unsignedBigInteger('current_level_id')->nullable()->after('ui_language');
            $table->unsignedInteger('hearts')->default(5)->after('current_level_id');
            $table->timestamp('hearts_refill_at')->nullable()->after('hearts');
            $table->date('daily_goal_xp')->nullable()->after('hearts_refill_at');
            $table->unsignedInteger('daily_goal_target')->default(20)->after('daily_goal_xp');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'ui_language',
                'current_level_id',
                'hearts',
                'hearts_refill_at',
                'daily_goal_xp',
                'daily_goal_target',
            ]);
        });
    }
};
