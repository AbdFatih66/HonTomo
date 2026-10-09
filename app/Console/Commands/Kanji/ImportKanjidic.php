<?php

namespace App\Console\Commands\Kanji;

use App\Models\Kanji;
use App\Services\Kanji\KanjiMeaningTranslator;
use Illuminate\Console\Command;
use XMLReader;

/**
 * Imports character/reading/meaning facts from a KANJIDIC2 XML dump.
 *
 * Source & license: KANJIDIC2, (c) The Electronic Dictionary Research and
 * Development Group (Jim Breen et al.), CC BY-SA 4.0 — this app must credit
 * EDRDG wherever this data is shown (see docs/kanji-module.md "Atribusi").
 * Download kanjidic2.xml from https://www.edrdg.org/wiki/index.php/KANJIDIC_Project
 * and place it at storage/app/kanji-import/kanjidic2.xml (or pass a path).
 *
 * IMPORTANT — JLPT classification: KANJIDIC2's own <jlpt> tag uses the OLD
 * 1-4 scale (pre-2010) and was never updated for the current 5-level
 * N5-N1 system, and EDRDG says explicitly not to treat it as authoritative.
 * This command therefore ignores <jlpt> entirely and instead classifies
 * kanji using resources/lang-data/jlpt-kanji-levels.json, a separately
 * maintained character -> "N5".."N1" map. A kanji not listed there is
 * still imported (grade/readings/meanings are useful on their own) but is
 * left without a jlpt_level, so it won't show up in a level-filtered list
 * until that map is extended.
 */
class ImportKanjidic extends Command
{
    protected $signature = 'kanji:import-kanjidic
        {path=storage/app/kanji-import/kanjidic2.xml : path to kanjidic2.xml}
        {--only-mapped : skip characters absent from jlpt-kanji-levels.json entirely (default: import them anyway, without a jlpt_level)}';

    protected $description = 'Import character/reading/meaning data from a KANJIDIC2 XML dump (idempotent).';

    public function handle(KanjiMeaningTranslator $translator): int
    {
        $path = base_path($this->argument('path'));

        if (! is_file($path)) {
            $this->error("File not found: {$path}");
            $this->line('Download kanjidic2.xml from https://www.edrdg.org/wiki/index.php/KANJIDIC_Project and place it there, or pass a path argument.');

            return self::FAILURE;
        }

        $levels = $this->loadJlptLevels();
        $orderWithinLevel = $this->computeOrderWithinLevel($levels);

        $reader = new XMLReader;
        $reader->open($path, null, LIBXML_PARSEHUGE);

        $created = 0;
        $updated = 0;
        $skippedLocked = 0;
        $skippedUnmapped = 0;
        $errors = 0;

        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'character') {
                continue;
            }

            try {
                $dom = new \DOMDocument;
                $imported = $dom->importNode($reader->expand(), true);
                $dom->appendChild($imported);
                $node = simplexml_import_dom($imported);
                $literal = trim((string) $node->literal);

                if ($literal === '') {
                    continue;
                }

                $level = $levels[$literal] ?? null;

                if ($level === null && $this->option('only-mapped')) {
                    $skippedUnmapped++;

                    continue;
                }

                $grade = isset($node->misc->grade) ? (int) $node->misc->grade : null;
                $strokeCount = isset($node->misc->stroke_count) ? (int) $node->misc->stroke_count[0] : null;
                $freq = isset($node->misc->freq) ? (int) $node->misc->freq : null;

                $onyomi = [];
                $kunyomi = [];
                $meaningsEn = [];

                foreach ($node->reading_meaning->rmgroup ?? [] as $group) {
                    foreach ($group->reading ?? [] as $reading) {
                        $type = (string) $reading['r_type'];
                        if ($type === 'ja_on') {
                            $onyomi[] = (string) $reading;
                        } elseif ($type === 'ja_kun') {
                            $kunyomi[] = (string) $reading;
                        }
                    }
                    foreach ($group->meaning ?? [] as $meaning) {
                        // Meanings in another language carry an m_lang attribute;
                        // absence of that attribute means English (KANJIDIC2 spec).
                        if (! isset($meaning['m_lang'])) {
                            $meaningsEn[] = (string) $meaning;
                        }
                    }
                }

                if (empty($meaningsEn)) {
                    // No English gloss at all (rare, e.g. some kokuji) — still
                    // worth having the character/readings on file.
                    $meaningsEn = [$literal];
                }

                $translated = $translator->translateList($meaningsEn);

                $existing = Kanji::where('character', $literal)->first();
                $locked = $existing?->locked_fields ?? [];

                $attributes = ['character' => $literal];
                $values = [
                    'onyomi' => $onyomi,
                    'kunyomi' => $kunyomi,
                    'meaning_en' => implode(', ', $meaningsEn),
                    'stroke_count' => $strokeCount,
                    'grade' => $grade,
                    'frequency_rank' => $freq,
                ];

                if (! ($locked['meaning_id'] ?? false)) {
                    $values['meaning_id'] = $translated['text'];
                    $values['needs_review_id'] = $translated['needs_review'];
                }

                if (! ($locked['jlpt_level'] ?? false)) {
                    // Always set explicitly (including null for characters
                    // absent from jlpt-kanji-levels.json) — never rely on
                    // the column's own default. See migration
                    // 2026_09_23_000002_fix_kanjis_jlpt_level_default.php
                    // for why leaving this key out used to silently mislabel
                    // thousands of non-JLPT kanji as "N5".
                    $values['jlpt_level'] = $level;
                    $values['jlpt_source'] = $level !== null ? 'jlpt-kanji-levels.json' : null;

                    // jlpt-kanji-levels.json preserves the official list's
                    // own order per level (that's the order source sites
                    // like jisho.org/jlptsensei.com teach it in — roughly
                    // frequency/pedagogical, not stroke count or Unicode
                    // code point). Without this, every real-imported kanji
                    // has order=0 and the grid falls back to sorting by
                    // `id`, i.e. plain XML/insertion order — which is why
                    // obscure, complex-looking kanji were showing up before
                    // simple ones within the same level.
                    if (! ($locked['order'] ?? false)) {
                        $values['order'] = $orderWithinLevel[$literal] ?? 0;
                    }
                }

                // Never clobber a field an admin has explicitly locked.
                foreach (array_keys($locked) as $field) {
                    if ($locked[$field]) {
                        unset($values[$field]);
                    }
                }

                Kanji::updateOrCreate($attributes, $values);

                $existing ? $updated++ : $created++;
            } catch (\Throwable $e) {
                $errors++;
                $this->warn("Skipped one <character> entry due to a parse error: {$e->getMessage()}");
            }
        }

        $reader->close();

        $this->info("KANJIDIC2 import done. Created: {$created}, updated: {$updated}, skipped (unmapped): {$skippedUnmapped}, errors: {$errors}.");

        if ($skippedLocked > 0) {
            $this->line("Fields left untouched due to admin locks: {$skippedLocked}.");
        }

        return self::SUCCESS;
    }

    /** @return array<string, string> character => "N5".."N1" */
    private function loadJlptLevels(): array
    {
        $path = resource_path('lang-data/jlpt-kanji-levels.json');

        if (! is_file($path)) {
            $this->warn('resources/lang-data/jlpt-kanji-levels.json not found — importing without JLPT classification.');

            return [];
        }

        $data = json_decode(file_get_contents($path), true);
        unset($data['_comment']);

        return is_array($data) ? $data : [];
    }

    /**
     * @param  array<string, string>  $levels  character => "N5".."N1"
     * @return array<string, int> character => 1-based position within its
     *                             own level, in the JSON file's own order
     */
    private function computeOrderWithinLevel(array $levels): array
    {
        $counters = [];
        $order = [];

        foreach ($levels as $character => $level) {
            $counters[$level] = ($counters[$level] ?? 0) + 1;
            $order[$character] = $counters[$level];
        }

        return $order;
    }
}
