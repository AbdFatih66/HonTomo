<?php

namespace App\Console\Commands;

use App\Services\KanaLessonFixer;
use Illuminate\Console\Command;

class FixKanaLessons extends Command
{
    protected $signature = 'kana:fix-lessons';

    protected $description = 'Rebuild the Hiragana/Katakana lessons (Dasar, Dakuten, Handakuten, Yoon, Yoon Dakuten) from the kana chart';

    public function handle(KanaLessonFixer $fixer): int
    {
        $s = $fixer->run();

        $this->info("Chart rows corrected/removed: {$s['chart_rows_fixed']} · lessons merged away: {$s['lessons_removed']}");

        foreach ($s['chart'] as $script => $types) {
            $this->line("  chart {$script}: ".collect($types)->map(fn ($n, $t) => "$t=$n")->implode(', '));
        }

        $this->table(['lesson', 'letters in the lesson (by chart type)'], collect($s['per_lesson'])->map(fn ($n, $t) => [$t, $n])->values()->all());

        return self::SUCCESS;
    }
}
