<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The navbar bell used to show 5 hardcoded demo notifications (PayPal
 * payment, a new user "Tom Holland", ...) unrelated to this app. This table
 * holds real per-account notifications, created by NotificationService
 * whenever the user actually achieves something (finishes/masters a lesson,
 * hits a streak milestone). See NotificationService for the write side.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('type', 40); // lesson_completed | lesson_mastered | streak
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('icon', 60)->default('tabler-bell');
            $table->string('color', 20)->nullable();

            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
