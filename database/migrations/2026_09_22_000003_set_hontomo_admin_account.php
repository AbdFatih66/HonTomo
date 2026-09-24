<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

/**
 * Grants the admin role to hontomo.id@gmail.com.
 *
 * Uses updateOrCreate so it is safe to run on a database that already has
 * users (existing rows are only updated, never replaced or deleted) and safe
 * to re-run. If the account doesn't exist yet, it is created without a
 * password — the owner signs in the first time via "Forgot password" (or
 * Google, if the email matches) to set one.
 */
return new class extends Migration
{
    private const ADMIN_EMAIL = 'hontomo.id@gmail.com';

    public function up(): void
    {
        $user = User::where('email', self::ADMIN_EMAIL)->first();

        if ($user) {
            $user->role = 'admin';
            $user->save();

            return;
        }

        $user = User::create([
            'name' => 'HonTomo Admin',
            'email' => self::ADMIN_EMAIL,
            'password' => null,
            'ui_language' => 'id',
        ]);
        $user->role = 'admin';
        $user->save();
    }

    public function down(): void
    {
        // Intentionally does nothing: reverting could demote an account the
        // site owner is actively using, or delete a real user's data.
    }
};
