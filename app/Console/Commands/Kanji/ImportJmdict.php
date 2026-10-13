<?php

namespace App\Console\Commands\Kanji;

use App\Models\Kanji;
use App\Models\KanjiVocabulary;
use App\Services\Kanji\KanjiMeaningTranslator;
use Illuminate\Console\Command;
use XMLReader;

/**
 * Imports a handful of common vocabulary words per kanji from JMdict, for
 * the character-detail page's "related vocabulary" list and the
 * "Kanji -> Kosakata" quiz type.
 *
 * Source & license: JMdict, (c) The Electronic Dictionary Research and
 * Development Group (Jim Breen et al.), CC BY-SA 4.0 — same attribution
 * requirement as KANJIDIC2 (see docs/kanji-module.md "Atribusi"). Download
 * JMdict_e.xml (English-only glosses; smaller than the multi-lingual
 * JMdict.xml) from https://www.edrdg.org/jmdict/j_jmdict.html and place it
 * at storage/app/kanji-import/JMdict_e.xml.
 *
 * JMdict's DTD declares entities for part-of-speech codes (e.g. &n; for
 * "noun") — LIBXML_NOENT + LIBXML_DTDLOAD below let those resolve instead
 * of throwing, and PARSEHUGE guards against the file's size (JMdict_e.xml
 * is tens of MB).
 */
class ImportJmdict extends Command
{
    protected $signature = 'kanji:import-jmdict
        {path=storage/app/kanji-import/JMdict_e.xml : path to JMdict_e.xml}
        {--per-kanji=8 : max vocabulary entries to keep per kanji}';

    protected $description = 'Import example vocabulary per kanji from a JMdict_e.xml dump (idempotent).';

    public function handle(KanjiMeaningTranslator $translator): int
    {
        $path = base_path($this->argument('path'));

        if (! is_file($path)) {
            $this->error("File not found: {$path}");
            $this->line('Download JMdict_e.xml from https://www.edrdg.org/jmdict/j_jmdict.html and place it there, or pass a path argument.');

            return self::FAILURE;
        }

        $perKanjiLimit = max(1, (int) $this->option('per-kanji'));

        // char => kanji id, and a running per-kanji count so we can stop
        // once a character has enough examples without a separate query.
        $targets = Kanji::pluck('id', 'character')->all();

        if (empty($targets)) {
            $this->error('No kanji in the database yet — run kanji:import-kanjidic first.');

            return self::FAILURE;
        }

        $counts = KanjiVocabulary::selectRaw('kanji_id, count(*) as c')
            ->groupBy('kanji_id')
            ->pluck('c', 'kanji_id')
            ->all();

        $reader = new XMLReader;
        $reader->open($path, null, LIBXML_PARSEHUGE | LIBXML_NOENT | LIBXML_DTDLOAD);
        $reader->setParserProperty(XMLReader::SUBST_ENTITIES, true);

        $entriesScanned = 0;
        $wordsAdded = 0;

        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'entry') {
                continue;
            }

            $entriesScanned++;

            try {
                $dom = new \DOMDocument;
                $imported = $dom->importNode($reader->expand(), true);
                $dom->appendChild($imported);
                $node = simplexml_import_dom($imported);

                $keb = isset($node->k_ele[0]->keb) ? (string) $node->k_ele[0]->keb : null;

                if ($keb === null) {
                    continue; // kana-only entry, nothing for a kanji-vocabulary list
                }

                $reb = isset($node->r_ele[0]->reb) ? (string) $node->r_ele[0]->reb : '';
                $sense = $node->sense[0] ?? null;

                if ($sense === null || empty($sense->gloss)) {
                    continue;
                }

                $glossEn = (string) $sense->gloss[0];
                $pos = isset($sense->pos[0]) ? (string) $sense->pos[0] : null;

                // Which of our target kanji actually appear in this word?
                $charsInWord = array_unique(preg_split('//u', $keb, -1, PREG_SPLIT_NO_EMPTY));
                $relevantKanjiIds = [];

                foreach ($charsInWord as $char) {
                    if (isset($targets[$char]) && ($counts[$targets[$char]] ?? 0) < $perKanjiLimit) {
                        $relevantKanjiIds[] = $targets[$char];
                    }
                }

                if (empty($relevantKanjiIds)) {
                    continue;
                }

                $translated = $translator->translate($glossEn);

                foreach ($relevantKanjiIds as $kanjiId) {
                    $vocab = KanjiVocabulary::firstOrCreate(
                        ['kanji_id' => $kanjiId, 'word' => $keb, 'reading' => $reb],
                        [
                            'meaning_en' => $glossEn,
                            'meaning_id' => $translated['text'],
                            'needs_review_id' => $translated['needs_review'],
                            'part_of_speech' => $pos,
                            'source' => 'jmdict',
                            'order' => $counts[$kanjiId] ?? 0,
                        ]
                    );

                    if ($vocab->wasRecentlyCreated) {
                        $counts[$kanjiId] = ($counts[$kanjiId] ?? 0) + 1;
                        $wordsAdded++;
                    }
                }
            } catch (\Throwable $e) {
                $this->warn("Skipped one <entry> due to a parse error: {$e->getMessage()}");
            }

            if ($entriesScanned % 20000 === 0) {
                $this->line("...scanned {$entriesScanned} entries, added {$wordsAdded} vocabulary rows so far");
            }
        }

        $reader->close();

        $this->info("JMdict import done. Scanned {$entriesScanned} entries, added {$wordsAdded} vocabulary rows.");

        return self::SUCCESS;
    }
}
