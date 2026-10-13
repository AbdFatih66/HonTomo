<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class VocabularyCategorySeeder extends Seeder
{
    /**
     * TODO: fill in real N5 VocabularyCategory data.
     * Kept intentionally empty per project decision \u2014 populate once
     * clean Minna no Nihongo source material (or another vetted source)
     * is available. See the model App\Models\VocabularyCategory for the expected fields.
     */
    public function run(): void
    {
        $categories = [
            ['name_id' => 'Orang & Profesi', 'name_en' => 'People & Professions', 'slug' => 'people-professions'],
            ['name_id' => 'Negara', 'name_en' => 'Countries', 'slug' => 'countries'],
            ['name_id' => 'Kata Tunjuk', 'name_en' => 'Demonstratives', 'slug' => 'demonstratives'],
            ['name_id' => 'Benda Sehari-hari', 'name_en' => 'Everyday Objects', 'slug' => 'everyday-objects'],
            ['name_id' => 'Bahasa & Sebutan', 'name_en' => 'Languages & Terms', 'slug' => 'languages-terms'],
        ];

        foreach ($categories as $category) {
            \App\Models\VocabularyCategory::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
