<?php

namespace Database\Seeders;

use App\Services\VocabularyQuizSync;
use Illuminate\Database\Seeder;

class VocabularyQuizSyncSeeder extends Seeder
{
    public function run(): void
    {
        app(VocabularyQuizSync::class)->run();
    }
}
