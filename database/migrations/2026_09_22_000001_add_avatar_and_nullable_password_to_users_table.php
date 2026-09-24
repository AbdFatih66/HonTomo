<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive and safe on existing data:
 *  - users.avatar: new nullable column (profile picture URL, e.g. from Google)
 *  - users.password: becomes nullable so accounts created through Google
 *    sign-in can exist without a password. Existing rows are untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar', 2048)->nullable()->after('email');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar');
        });

        // password stays nullable on rollback: making it NOT NULL again would
        // fail (or require deleting data) if Google-only accounts exist.
    }
};
