<?php

/**
 * Reports Japanese text in the Bunpou seeders that still contains kanji with
 * no furigana marker attached.
 *
 * Marker syntax (rendered by resources/js/components/learning/RubyText.vue):
 *   漢字《かんじ》
 *
 * Usage:
 *   php scripts/check-furigana.php
 *   php scripts/check-furigana.php database/seeders/Lesson1BunpouSeeder.php
 *
 * Exits 1 when anything is unmarked, so it can be dropped into CI.
 *
 * Only the keys that end up on screen are checked. Placeholders inside a
 * 'pattern' line — (場所), (乗り物), KB1 の KB2 — are notation rather than text
 * the learner reads aloud, so they are skipped by design.
 */

$root = dirname(__DIR__);

$targets = array_slice($argv, 1) ?: [
    'database/seeders/Lesson1BunpouSeeder.php',
    'database/seeders/Lesson2to5BunpouSeeder.php',
];

// Keys whose value is shown to the learner as Japanese.
$checkedKeys = ["'ja' =>", "'speaker' =>", "'notes_id'", "'notes_en'", "'explanation_id'", "'explanation_en'"];

$kanji = '/[\x{3400}-\x{9FFF}]/u';
$marked = '/[\x{3400}-\x{9FFF}\x{3005}]+《[^》]+》/u';

$problems = 0;
$inNotes = false;

foreach ($targets as $relative) {
    $path = $root.'/'.ltrim($relative, '/');

    if (! is_file($path)) {
        fwrite(STDERR, "skip (not found): {$relative}\n");

        continue;
    }

    foreach (file($path) as $number => $line) {
        // notes_id / notes_en are arrays — their items sit on their own lines.
        if (preg_match("/'notes_(id|en)' => \[/", $line)) {
            $inNotes = true;
        } elseif ($inNotes && str_contains($line, '],')) {
            $inNotes = false;
        }

        $relevant = $inNotes;

        foreach ($checkedKeys as $key) {
            if (str_contains($line, $key)) {
                $relevant = true;

                break;
            }
        }

        if (! $relevant) {
            continue;
        }

        $stripped = preg_replace($marked, '', $line);

        if (preg_match_all($kanji, $stripped, $found)) {
            $problems++;
            $chars = implode('', array_unique($found[0]));
            printf("%s:%d  unmarked: %s\n", $relative, $number + 1, $chars);
        }
    }
}

if ($problems === 0) {
    echo "All Japanese text carries furigana.\n";

    exit(0);
}

printf("\n%d line(s) need furigana markers.\n", $problems);

exit(1);
