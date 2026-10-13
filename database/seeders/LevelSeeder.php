<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    public function run(): void
    {
        Level::firstOrCreate(
            ['code' => 'N5'],
            [
                'name_id' => 'Pemula (N5)',
                'name_en' => 'Beginner (N5)',
                'description_id' => 'Dasar-dasar Bahasa Jepang: perkenalan, kosakata sehari-hari, dan pola kalimat paling umum.',
                'description_en' => 'Japanese basics: introductions, everyday vocabulary, and the most common sentence patterns.',
                'order' => 1,
                'is_active' => true,
            ]
        );
    }
}
