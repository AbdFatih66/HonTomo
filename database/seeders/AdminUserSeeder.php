<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run with: php artisan db:seed --class=AdminUserSeeder
     * Change the password after first login in a real deployment.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@hontomo.test'],
            [
                'name' => 'Admin',
                'password' => 'password',
                'role' => 'admin',
            ]
        );
    }
}
