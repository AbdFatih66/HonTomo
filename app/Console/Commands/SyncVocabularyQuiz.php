<?php

namespace App\Console\Commands;

use App\Services\VocabularyQuizSync;
use Illuminate\Console\Command;

class SyncVocabularyQuiz extends Command
{
    protected $signature = 'vocabulary:sync-quiz {level=N5 : Kode level JLPT (N5 atau N4)}';

    protected $description = 'Make the Kosakata list and the vocabulary quiz contain the same words (adds what is missing on either side)';

    public function handle(VocabularyQuizSync $sync): int
    {
        $s = $sync->run(strtoupper((string) $this->argument('level')));

        $this->info("Quiz questions added: {$s['questions_added']} · quiz questions reactivated: {$s['questions_reactivated']}");
        $this->info("Words added to the list: {$s['words_added']} · quiz words made visible in the list: {$s['words_fixed']}");

        if ($s['chapters_without_quiz'] !== []) {
            $this->warn('Chapters without a vocabulary quiz lesson: '.implode(', ', $s['chapters_without_quiz']));
        }

        return self::SUCCESS;
    }
}
