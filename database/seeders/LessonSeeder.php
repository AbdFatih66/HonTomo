<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    public function run(): void
    {
        $unit1 = Unit::where('order', 1)->whereHas('level', fn ($l) => $l->where('code', 'N5'))->firstOrFail();
        $unit2 = Unit::where('order', 2)->whereHas('level', fn ($l) => $l->where('code', 'N5'))->firstOrFail();

        $lesson1a = Lesson::firstOrCreate(
            ['unit_id' => $unit1->id, 'order' => 1],
            [
                'title_id' => 'Orang & Profesi',
                'title_en' => 'People & Professions',
                'category' => 'vocabulary',
                'xp_reward' => 10,
                'required_accuracy' => 70,
                'is_active' => true,
            ]
        );

        $lesson1b = Lesson::firstOrCreate(
            ['unit_id' => $unit1->id, 'order' => 2],
            [
                'title_id' => 'Negara',
                'title_en' => 'Countries',
                'category' => 'vocabulary',
                'xp_reward' => 10,
                'required_accuracy' => 70,
                'prerequisite_lesson_id' => $lesson1a->id,
                'is_active' => true,
            ]
        );

        $lesson2a = Lesson::firstOrCreate(
            ['unit_id' => $unit2->id, 'order' => 1],
            [
                'title_id' => 'Kata Tunjuk',
                'title_en' => 'Demonstratives',
                'category' => 'vocabulary',
                'xp_reward' => 10,
                'required_accuracy' => 70,
                'prerequisite_lesson_id' => $lesson1b->id,
                'is_active' => true,
            ]
        );

        Lesson::firstOrCreate(
            ['unit_id' => $unit2->id, 'order' => 2],
            [
                'title_id' => 'Benda & Bahasa',
                'title_en' => 'Objects & Languages',
                'category' => 'vocabulary',
                'xp_reward' => 10,
                'required_accuracy' => 70,
                'prerequisite_lesson_id' => $lesson2a->id,
                'is_active' => true,
            ]
        );
    }
}
