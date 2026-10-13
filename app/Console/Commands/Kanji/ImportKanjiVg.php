<?php

namespace App\Console\Commands\Kanji;

use App\Models\Kanji;
use App\Models\KanjiStroke;
use App\Services\Kanji\KanjiVgConverter;
use Illuminate\Console\Command;

/**
 * Converts KanjiVG SVG files into HanziWriter-format {strokes, medians}
 * and stores them in kanji_strokes, one row per Kanji already present in
 * the `kanjis` table (run kanji:import-kanjidic first).
 *
 * Source & license: KanjiVG, (c) Ulrich Apel, CC BY-SA 3.0 — this app must
 * credit KanjiVG + link to https://kanjivg.tagaini.net wherever stroke
 * order is shown (see docs/kanji-module.md "Atribusi"). Download a release
 * ("kanjivg-YYYYMMDD-main.zip") from https://github.com/KanjiVG/kanjivg
 * and extract its kanji/*.svg files into storage/app/kanji-import/kanjivg/
 * (or pass a --dir). Files are named by the character's Unicode codepoint
 * in lowercase 5-digit hex, e.g. 05b66.svg for 学 (U+5B66).
 *
 * See App\Services\Kanji\KanjiVgConverter for the actual SVG -> HanziWriter
 * coordinate/format conversion and its documented assumptions.
 */
class ImportKanjiVg extends Command
{
    protected $signature = 'kanji:import-kanjivg
        {--dir=storage/app/kanji-import/kanjivg : directory of KanjiVG *.svg files}
        {--force : re-convert and overwrite strokes that already exist}';

    protected $description = 'Convert KanjiVG SVG stroke data into HanziWriter format for every known Kanji (idempotent).';

    public function handle(KanjiVgConverter $converter): int
    {
        $dir = base_path($this->option('dir'));

        if (! is_dir($dir)) {
            $this->error("Directory not found: {$dir}");
            $this->line('Get the SVGs from https://github.com/KanjiVG/kanjivg (kanji/ folder) and place them there.');

            return self::FAILURE;
        }

        $kanjis = Kanji::query()->whereNotNull('character')->get();
        $converted = 0;
        $skippedExisting = 0;
        $missingFile = [];
        $errors = 0;

        foreach ($kanjis as $kanji) {
            $hasStrokes = KanjiStroke::where('kanji_id', $kanji->id)->exists();

            if ($hasStrokes && ! $this->option('force')) {
                $skippedExisting++;

                continue;
            }

            $hex = $this->codepointHex($kanji->character);
            $file = rtrim($dir, '/').'/'.$hex.'.svg';

            if (! is_file($file)) {
                $missingFile[] = "{$kanji->character} ({$hex}.svg)";

                continue;
            }

            try {
                $data = $converter->convert(file_get_contents($file), sourceRef: $hex);

                if ($data['stroke_count'] === 0) {
                    $this->warn("No strokes extracted for {$kanji->character} ({$hex}.svg) — check the file manually.");

                    continue;
                }

                KanjiStroke::updateOrCreate(
                    ['kanji_id' => $kanji->id],
                    [
                        'strokes' => $data['strokes'],
                        'medians' => $data['medians'],
                        'stroke_count' => $data['stroke_count'],
                        'source' => 'kanjivg',
                        'source_ref' => $hex,
                    ]
                );

                $converted++;
            } catch (\Throwable $e) {
                $errors++;
                $this->warn("Failed to convert {$kanji->character} ({$hex}.svg): {$e->getMessage()}");
            }
        }

        $this->info("KanjiVG import done. Converted: {$converted}, already had strokes: {$skippedExisting}, errors: {$errors}.");

        if (! empty($missingFile)) {
            $this->warn(count($missingFile).' kanji have no matching SVG file in '.$dir.':');
            $this->line(implode(', ', array_slice($missingFile, 0, 20)).(count($missingFile) > 20 ? ', ...' : ''));
        }

        return self::SUCCESS;
    }

    /** e.g. '学' -> '05b66' (lowercase 5-digit hex, matching KanjiVG's file naming). */
    private function codepointHex(string $character): string
    {
        $codepoint = mb_ord($character, 'UTF-8');

        return str_pad(strtolower(dechex($codepoint)), 5, '0', STR_PAD_LEFT);
    }
}
