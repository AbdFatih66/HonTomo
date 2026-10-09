<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $n5 = Level::where('code', 'N5')->firstOrFail();

        $unit1 = Unit::firstOrCreate(
            ['level_id' => $n5->id, 'order' => 1],
            [
                'title_id' => 'Pelajaran 1: Perkenalan',
                'title_en' => 'Lesson 1: Introductions',
                'description_id' => 'Kata ganti orang, profesi, dan nama negara.',
                'description_en' => 'Personal pronouns, professions, and country names.',
                'icon' => 'mdi-account-voice',
                'is_active' => true,
            ]
        );

        Unit::firstOrCreate(
            ['level_id' => $n5->id, 'order' => 2],
            [
                'title_id' => 'Pelajaran 2: Ini Apa?',
                'title_en' => 'Lesson 2: What Is This?',
                'description_id' => 'Kata tunjuk (ini/itu) dan nama benda sehari-hari.',
                'description_en' => 'Demonstratives (this/that) and everyday object names.',
                'icon' => 'mdi-book-open-variant',
                'prerequisite_unit_id' => $unit1->id,
                'is_active' => true,
            ]
        );
    }
}
