<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remembers which (IP, browser/OS) combinations have signed in to an account
 * before, so a genuinely new one can trigger a "new device sign-in" email.
 * Additive — safe on a database that already has users.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_known_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('fingerprint', 64); // sha256(ip . '|' . user_agent)
            $table->string('device_name')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'fingerprint']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_known_devices');
    }
};
