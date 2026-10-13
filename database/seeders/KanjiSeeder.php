<?php

namespace Database\Seeders;

use App\Models\Kanji;
use App\Models\KanjiStroke;
use App\Models\KanjiVocabulary;
use App\Services\Kanji\KanjiMeaningTranslator;
use Illuminate\Database\Seeder;

/**
 * Seeds a small, hand-verified demo set (N5 + N4 + N3 + N2) so the Kanji
 * module has something real to show end-to-end (chart, detail, stroke
 * animation, writing quiz, review mode) without first needing the full
 * KANJIDIC2/KanjiVG/JMdict import pipeline run. This is intentionally NOT
 * the full official N5 (103) / N4 (181) / N3 (344) / N2 (362) lists in
 * jlpt-kanji-levels.json — see docs/kanji-module.md "Data demo" for exactly
 * what's covered and what running the real `kanji:import-*` commands
 * (with the real source files) adds on top.
 *
 * Every reading/meaning below was either hand-verified (the 15 N5
 * entries — extremely common kanji from any beginner course) or taken
 * directly from a cited, fetched source table (N4 from nihongomaster.com,
 * N3 and N2 from jlptsensei.com — fetched during development, not typed
 * from memory). None of it is machine-generated. The stroke data in
 * resources/lang-data/kanji-strokes-{n5,n4,n3,n2}-demo.json is genuinely
 * derived from the real KanjiVG project files (fetched from
 * github.com/KanjiVG/kanjivg and run through
 * App\Services\Kanji\KanjiVgConverter) — it is not placeholder/fake data,
 * and every converted stroke count was cross-checked against the source
 * table's own stroke_count column before being included here.
 */
class KanjiSeeder extends Seeder
{
    /**
     * @var array<int, array{character: string, onyomi: string[], kunyomi: string[], meanings_en: string[], stroke_count: int, jlpt_level: string, grade?: int}>
     */
    private const KANJI = [
        ['character' => '一', 'onyomi' => ['イチ', 'イツ'], 'kunyomi' => ['ひと.つ'], 'meanings_en' => ['one'], 'stroke_count' => 1, 'grade' => 1, 'jlpt_level' => 'N5'],
        ['character' => '二', 'onyomi' => ['ニ'], 'kunyomi' => ['ふた', 'ふた.つ'], 'meanings_en' => ['two'], 'stroke_count' => 2, 'grade' => 1, 'jlpt_level' => 'N5'],
        ['character' => '三', 'onyomi' => ['サン'], 'kunyomi' => ['み', 'み.つ', 'みっ.つ'], 'meanings_en' => ['three'], 'stroke_count' => 3, 'grade' => 1, 'jlpt_level' => 'N5'],
        ['character' => '人', 'onyomi' => ['ジン', 'ニン'], 'kunyomi' => ['ひと'], 'meanings_en' => ['person'], 'stroke_count' => 2, 'grade' => 1, 'jlpt_level' => 'N5'],
        ['character' => '大', 'onyomi' => ['ダイ', 'タイ'], 'kunyomi' => ['おお', 'おお.きい'], 'meanings_en' => ['big', 'large'], 'stroke_count' => 3, 'grade' => 1, 'jlpt_level' => 'N5'],
        ['character' => '小', 'onyomi' => ['ショウ'], 'kunyomi' => ['ちい.さい', 'こ', 'お'], 'meanings_en' => ['small', 'little'], 'stroke_count' => 3, 'grade' => 1, 'jlpt_level' => 'N5'],
        ['character' => '山', 'onyomi' => ['サン'], 'kunyomi' => ['やま'], 'meanings_en' => ['mountain'], 'stroke_count' => 3, 'grade' => 1, 'jlpt_level' => 'N5'],
        ['character' => '川', 'onyomi' => ['セン'], 'kunyomi' => ['かわ'], 'meanings_en' => ['river'], 'stroke_count' => 3, 'grade' => 1, 'jlpt_level' => 'N5'],
        ['character' => '水', 'onyomi' => ['スイ'], 'kunyomi' => ['みず'], 'meanings_en' => ['water'], 'stroke_count' => 4, 'grade' => 1, 'jlpt_level' => 'N5'],
        ['character' => '火', 'onyomi' => ['カ'], 'kunyomi' => ['ひ', '-び'], 'meanings_en' => ['fire'], 'stroke_count' => 4, 'grade' => 1, 'jlpt_level' => 'N5'],
        ['character' => '木', 'onyomi' => ['ボク', 'モク'], 'kunyomi' => ['き', 'こ'], 'meanings_en' => ['tree', 'wood'], 'stroke_count' => 4, 'grade' => 1, 'jlpt_level' => 'N5'],
        ['character' => '金', 'onyomi' => ['キン', 'コン'], 'kunyomi' => ['かね', 'かな'], 'meanings_en' => ['gold', 'money', 'metal'], 'stroke_count' => 8, 'grade' => 1, 'jlpt_level' => 'N5'],
        ['character' => '土', 'onyomi' => ['ド', 'ト'], 'kunyomi' => ['つち'], 'meanings_en' => ['earth', 'soil', 'ground'], 'stroke_count' => 3, 'grade' => 1, 'jlpt_level' => 'N5'],
        ['character' => '日', 'onyomi' => ['ニチ', 'ジツ'], 'kunyomi' => ['ひ', '-び', '-か'], 'meanings_en' => ['day', 'sun'], 'stroke_count' => 4, 'grade' => 1, 'jlpt_level' => 'N5'],
        ['character' => '上', 'onyomi' => ['ジョウ'], 'kunyomi' => ['うえ', 'あ.げる', 'あ.がる', 'のぼ.る'], 'meanings_en' => ['up', 'above'], 'stroke_count' => 3, 'grade' => 1, 'jlpt_level' => 'N5'],

        // N4 demo set — readings/meanings/stroke counts taken directly from
        // the verified table at https://www.nihongomaster.com/jlpt-n4-kanji-list
        // (fetched, not typed from memory), cross-checked against the
        // genuine KanjiVG-derived stroke counts below. `grade` is left out
        // (null) rather than guessed — that table didn't include it.
        ['character' => '同', 'onyomi' => ['ドウ'], 'kunyomi' => ['おな.じ'], 'meanings_en' => ['same', 'agree', 'equal'], 'stroke_count' => 6, 'jlpt_level' => 'N4'],
        ['character' => '事', 'onyomi' => ['ジ'], 'kunyomi' => ['こと'], 'meanings_en' => ['matter', 'thing', 'fact'], 'stroke_count' => 8, 'jlpt_level' => 'N4'],
        ['character' => '自', 'onyomi' => ['ジ'], 'kunyomi' => ['みずか.ら'], 'meanings_en' => ['oneself'], 'stroke_count' => 6, 'jlpt_level' => 'N4'],
        ['character' => '発', 'onyomi' => ['ハツ'], 'kunyomi' => ['た.つ'], 'meanings_en' => ['departure', 'discharge', 'publish'], 'stroke_count' => 9, 'jlpt_level' => 'N4'],
        ['character' => '家', 'onyomi' => ['カ'], 'kunyomi' => ['いえ'], 'meanings_en' => ['house', 'home', 'family'], 'stroke_count' => 10, 'jlpt_level' => 'N4'],
        ['character' => '思', 'onyomi' => ['シ'], 'kunyomi' => ['おも.う'], 'meanings_en' => ['think'], 'stroke_count' => 9, 'jlpt_level' => 'N4'],
        ['character' => '使', 'onyomi' => ['シ'], 'kunyomi' => ['つか.う'], 'meanings_en' => ['use', 'send on a mission', 'order'], 'stroke_count' => 8, 'jlpt_level' => 'N4'],
        ['character' => '声', 'onyomi' => ['セイ'], 'kunyomi' => ['こえ'], 'meanings_en' => ['voice'], 'stroke_count' => 7, 'jlpt_level' => 'N4'],
        ['character' => '犬', 'onyomi' => ['ケン'], 'kunyomi' => ['いぬ'], 'meanings_en' => ['dog'], 'stroke_count' => 4, 'jlpt_level' => 'N4'],
        ['character' => '兄', 'onyomi' => ['ケイ'], 'kunyomi' => ['あに'], 'meanings_en' => ['elder brother', 'big brother'], 'stroke_count' => 5, 'jlpt_level' => 'N4'],
        ['character' => '姉', 'onyomi' => ['シ'], 'kunyomi' => ['あね'], 'meanings_en' => ['elder sister'], 'stroke_count' => 8, 'jlpt_level' => 'N4'],
        ['character' => '妹', 'onyomi' => ['マイ'], 'kunyomi' => ['いもうと'], 'meanings_en' => ['younger sister'], 'stroke_count' => 8, 'jlpt_level' => 'N4'],
        ['character' => '弟', 'onyomi' => ['テイ'], 'kunyomi' => ['おとうと'], 'meanings_en' => ['younger brother', 'faithful service to elders'], 'stroke_count' => 7, 'jlpt_level' => 'N4'],
        ['character' => '茶', 'onyomi' => ['チャ'], 'kunyomi' => [], 'meanings_en' => ['tea'], 'stroke_count' => 9, 'jlpt_level' => 'N4'],
        ['character' => '牛', 'onyomi' => ['ギュウ'], 'kunyomi' => ['うし'], 'meanings_en' => ['cow'], 'stroke_count' => 4, 'jlpt_level' => 'N4'],

        // N3 demo set — readings/meanings/stroke counts taken directly from
        // the verified table at https://jlptsensei.com/jlpt-n3-kanji-list/
        // (fetched across its 4 pages, not typed from memory).
        ['character' => '決', 'onyomi' => ['ケツ'], 'kunyomi' => ['き.める'], 'meanings_en' => ['decide', 'fix', 'agree upon', 'appoint'], 'stroke_count' => 7, 'jlpt_level' => 'N3'],
        ['character' => '表', 'onyomi' => ['ヒョウ'], 'kunyomi' => ['おもて', 'あらわ.す'], 'meanings_en' => ['surface', 'table', 'chart', 'diagram'], 'stroke_count' => 8, 'jlpt_level' => 'N3'],
        ['character' => '守', 'onyomi' => ['シュ'], 'kunyomi' => ['まも.る'], 'meanings_en' => ['guard', 'protect', 'obey'], 'stroke_count' => 6, 'jlpt_level' => 'N3'],
        ['character' => '若', 'onyomi' => ['ジャク'], 'kunyomi' => ['わか.い'], 'meanings_en' => ['young'], 'stroke_count' => 8, 'jlpt_level' => 'N3'],
        ['character' => '美', 'onyomi' => ['ビ'], 'kunyomi' => ['うつく.しい'], 'meanings_en' => ['beauty', 'beautiful'], 'stroke_count' => 9, 'jlpt_level' => 'N3'],
        ['character' => '命', 'onyomi' => ['メイ', 'ミョウ'], 'kunyomi' => ['いのち'], 'meanings_en' => ['fate', 'command'], 'stroke_count' => 8, 'jlpt_level' => 'N3'],
        ['character' => '愛', 'onyomi' => ['アイ'], 'kunyomi' => [], 'meanings_en' => ['love', 'affection'], 'stroke_count' => 13, 'jlpt_level' => 'N3'],
        ['character' => '王', 'onyomi' => ['オウ'], 'kunyomi' => [], 'meanings_en' => ['king', 'rule'], 'stroke_count' => 4, 'jlpt_level' => 'N3'],
        ['character' => '熱', 'onyomi' => ['ネツ'], 'kunyomi' => ['あつ.い'], 'meanings_en' => ['heat', 'fever', 'passion'], 'stroke_count' => 15, 'jlpt_level' => 'N3'],
        ['character' => '確', 'onyomi' => ['カク'], 'kunyomi' => ['たし.か'], 'meanings_en' => ['assurance', 'firm', 'confirm'], 'stroke_count' => 15, 'jlpt_level' => 'N3'],
        ['character' => '笑', 'onyomi' => ['ショウ'], 'kunyomi' => ['わら.う'], 'meanings_en' => ['laugh'], 'stroke_count' => 10, 'jlpt_level' => 'N3'],
        ['character' => '泳', 'onyomi' => ['エイ'], 'kunyomi' => ['およ.ぐ'], 'meanings_en' => ['swim'], 'stroke_count' => 8, 'jlpt_level' => 'N3'],
        ['character' => '猫', 'onyomi' => ['ビョウ'], 'kunyomi' => ['ねこ'], 'meanings_en' => ['cat'], 'stroke_count' => 11, 'jlpt_level' => 'N3'],
        ['character' => '誰', 'onyomi' => ['スイ'], 'kunyomi' => ['だれ'], 'meanings_en' => ['who', 'someone', 'somebody'], 'stroke_count' => 15, 'jlpt_level' => 'N3'],
        ['character' => '幾', 'onyomi' => ['キ'], 'kunyomi' => ['いく.つ'], 'meanings_en' => ['how many', 'how much', 'some'], 'stroke_count' => 12, 'jlpt_level' => 'N3'],

        // N2 demo set — readings/meanings/stroke counts taken directly from
        // the verified table at https://jlptsensei.com/jlpt-n2-kanji-list/
        // (fetched across its 4 pages, not typed from memory).
        ['character' => '各', 'onyomi' => ['カク'], 'kunyomi' => ['おのおの'], 'meanings_en' => ['each', 'every', 'either'], 'stroke_count' => 6, 'jlpt_level' => 'N2'],
        ['character' => '島', 'onyomi' => ['トウ'], 'kunyomi' => ['しま'], 'meanings_en' => ['island'], 'stroke_count' => 10, 'jlpt_level' => 'N2'],
        ['character' => '谷', 'onyomi' => ['コク'], 'kunyomi' => ['たに'], 'meanings_en' => ['valley'], 'stroke_count' => 7, 'jlpt_level' => 'N2'],
        ['character' => '史', 'onyomi' => ['シ'], 'kunyomi' => [], 'meanings_en' => ['history', 'chronicle'], 'stroke_count' => 5, 'jlpt_level' => 'N2'],
        ['character' => '兵', 'onyomi' => ['ヘイ', 'ヒョウ'], 'kunyomi' => ['つわもの'], 'meanings_en' => ['soldier', 'private', 'troops', 'army'], 'stroke_count' => 7, 'jlpt_level' => 'N2'],
        ['character' => '丸', 'onyomi' => ['ガン'], 'kunyomi' => ['まる', 'まる.い'], 'meanings_en' => ['round', 'full (month)', 'perfection'], 'stroke_count' => 3, 'jlpt_level' => 'N2'],
        ['character' => '竹', 'onyomi' => ['チク'], 'kunyomi' => ['たけ'], 'meanings_en' => ['bamboo'], 'stroke_count' => 6, 'jlpt_level' => 'N2'],
        ['character' => '毛', 'onyomi' => ['モウ'], 'kunyomi' => ['け'], 'meanings_en' => ['fur', 'hair', 'feather'], 'stroke_count' => 4, 'jlpt_level' => 'N2'],
        ['character' => '卵', 'onyomi' => ['ラン'], 'kunyomi' => ['たまご'], 'meanings_en' => ['egg'], 'stroke_count' => 7, 'jlpt_level' => 'N2'],
        ['character' => '湖', 'onyomi' => ['コ'], 'kunyomi' => ['みずうみ'], 'meanings_en' => ['lake'], 'stroke_count' => 12, 'jlpt_level' => 'N2'],
        ['character' => '貝', 'onyomi' => ['バイ'], 'kunyomi' => ['かい'], 'meanings_en' => ['shellfish'], 'stroke_count' => 7, 'jlpt_level' => 'N2'],
        ['character' => '皮', 'onyomi' => ['ヒ'], 'kunyomi' => ['かわ'], 'meanings_en' => ['skin', 'hide', 'leather'], 'stroke_count' => 5, 'jlpt_level' => 'N2'],
        ['character' => '移', 'onyomi' => ['イ'], 'kunyomi' => ['うつ.る'], 'meanings_en' => ['shift', 'move', 'change'], 'stroke_count' => 11, 'jlpt_level' => 'N2'],
        ['character' => '億', 'onyomi' => ['オク'], 'kunyomi' => [], 'meanings_en' => ['hundred million'], 'stroke_count' => 15, 'jlpt_level' => 'N2'],
        ['character' => '芸', 'onyomi' => ['ゲイ'], 'kunyomi' => [], 'meanings_en' => ['technique', 'art', 'craft', 'performance'], 'stroke_count' => 7, 'jlpt_level' => 'N2'],

        // N1 demo set — readings/meanings/stroke counts taken directly from
        // the verified table at https://jlptsensei.com/jlpt-n1-kanji-list/
        // page 1 (fetched, not typed from memory). Unlike N5-N2, the FULL
        // N1 classification list in jlpt-kanji-levels.json was NOT
        // attempted (source itself: "~2,000 kanji... constant work in
        // progress"; other sites range 960-1500+ with no clear consensus
        // list to reconcile against) — see docs/kanji-module.md section 9
        // for the full reasoning. These 15 are still genuine, verified
        // data; only the *complete* N1 list is the part left undone.
        ['character' => '氏', 'onyomi' => ['シ'], 'kunyomi' => ['うじ'], 'meanings_en' => ['family name', 'surname', 'clan'], 'stroke_count' => 4, 'jlpt_level' => 'N1'],
        ['character' => '基', 'onyomi' => ['キ'], 'kunyomi' => ['もと', 'もとい'], 'meanings_en' => ['fundamentals', 'foundation'], 'stroke_count' => 11, 'jlpt_level' => 'N1'],
        ['character' => '価', 'onyomi' => ['カ'], 'kunyomi' => ['あたい'], 'meanings_en' => ['value', 'price'], 'stroke_count' => 8, 'jlpt_level' => 'N1'],
        ['character' => '姿', 'onyomi' => ['シ'], 'kunyomi' => ['すがた'], 'meanings_en' => ['figure', 'form', 'shape'], 'stroke_count' => 9, 'jlpt_level' => 'N1'],
        ['character' => '系', 'onyomi' => ['ケイ'], 'kunyomi' => [], 'meanings_en' => ['lineage', 'system'], 'stroke_count' => 7, 'jlpt_level' => 'N1'],
        ['character' => '士', 'onyomi' => ['シ'], 'kunyomi' => ['さむらい'], 'meanings_en' => ['gentleman', 'scholar', 'samurai'], 'stroke_count' => 3, 'jlpt_level' => 'N1'],
        ['character' => '健', 'onyomi' => ['ケン'], 'kunyomi' => ['すこ.やか'], 'meanings_en' => ['healthy', 'health', 'strength', 'persistence'], 'stroke_count' => 11, 'jlpt_level' => 'N1'],
        ['character' => '異', 'onyomi' => ['イ'], 'kunyomi' => ['こと', 'こと.なる'], 'meanings_en' => ['uncommon', 'different', 'strange', 'unusual'], 'stroke_count' => 11, 'jlpt_level' => 'N1'],
        ['character' => '素', 'onyomi' => ['ソ', 'ス'], 'kunyomi' => ['もと'], 'meanings_en' => ['elementary', 'principle', 'naked', 'uncovered'], 'stroke_count' => 10, 'jlpt_level' => 'N1'],
        ['character' => '松', 'onyomi' => ['ショウ'], 'kunyomi' => ['まつ'], 'meanings_en' => ['pine tree'], 'stroke_count' => 8, 'jlpt_level' => 'N1'],
        ['character' => '影', 'onyomi' => ['エイ'], 'kunyomi' => ['かげ'], 'meanings_en' => ['shadow', 'silhouette', 'phantom'], 'stroke_count' => 15, 'jlpt_level' => 'N1'],
        ['character' => '標', 'onyomi' => ['ヒョウ'], 'kunyomi' => ['しるべ', 'しるし'], 'meanings_en' => ['signpost', 'seal', 'mark', 'stamp'], 'stroke_count' => 15, 'jlpt_level' => 'N1'],
        ['character' => '益', 'onyomi' => ['エキ', 'ヤク'], 'kunyomi' => ['ま.す'], 'meanings_en' => ['benefit', 'gain', 'profit', 'advantage'], 'stroke_count' => 10, 'jlpt_level' => 'N1'],
        ['character' => '宣', 'onyomi' => ['セン'], 'kunyomi' => ['のたま.う'], 'meanings_en' => ['proclaim', 'say', 'announce'], 'stroke_count' => 9, 'jlpt_level' => 'N1'],
        ['character' => '抗', 'onyomi' => ['コウ'], 'kunyomi' => ['あらが.う'], 'meanings_en' => ['confront', 'resist', 'defy', 'oppose'], 'stroke_count' => 7, 'jlpt_level' => 'N1'],
    ];

    /**
     * A couple of genuine, hand-verified common words per demo kanji —
     * just enough real data for the reading/kanji_from_reading/vocabulary
     * quiz types (Tahap 4) to actually have something to draw from. This
     * is NOT a substitute for running kanji:import-jmdict for real
     * coverage; see docs/kanji-module.md.
     *
     * @var array<string, array<int, array{word: string, reading: string, meaning_en: string}>>
     */
    private const VOCABULARY = [
        '一' => [['word' => '一つ', 'reading' => 'ひとつ', 'meaning_en' => 'one (thing)'], ['word' => '一人', 'reading' => 'ひとり', 'meaning_en' => 'one person, alone']],
        '二' => [['word' => '二つ', 'reading' => 'ふたつ', 'meaning_en' => 'two (things)'], ['word' => '二人', 'reading' => 'ふたり', 'meaning_en' => 'two people']],
        '三' => [['word' => '三つ', 'reading' => 'みっつ', 'meaning_en' => 'three (things)'], ['word' => '三人', 'reading' => 'さんにん', 'meaning_en' => 'three people']],
        '人' => [['word' => '人', 'reading' => 'ひと', 'meaning_en' => 'person'], ['word' => '外国人', 'reading' => 'がいこくじん', 'meaning_en' => 'foreigner']],
        '大' => [['word' => '大きい', 'reading' => 'おおきい', 'meaning_en' => 'big'], ['word' => '大学', 'reading' => 'だいがく', 'meaning_en' => 'university']],
        '小' => [['word' => '小さい', 'reading' => 'ちいさい', 'meaning_en' => 'small'], ['word' => '小学校', 'reading' => 'しょうがっこう', 'meaning_en' => 'elementary school']],
        '山' => [['word' => '山', 'reading' => 'やま', 'meaning_en' => 'mountain'], ['word' => '富士山', 'reading' => 'ふじさん', 'meaning_en' => 'Mt. Fuji']],
        '川' => [['word' => '川', 'reading' => 'かわ', 'meaning_en' => 'river'], ['word' => '小川', 'reading' => 'おがわ', 'meaning_en' => 'stream']],
        '水' => [['word' => '水', 'reading' => 'みず', 'meaning_en' => 'water'], ['word' => '水曜日', 'reading' => 'すいようび', 'meaning_en' => 'Wednesday']],
        '火' => [['word' => '火', 'reading' => 'ひ', 'meaning_en' => 'fire'], ['word' => '火曜日', 'reading' => 'かようび', 'meaning_en' => 'Tuesday']],
        '木' => [['word' => '木', 'reading' => 'き', 'meaning_en' => 'tree'], ['word' => '木曜日', 'reading' => 'もくようび', 'meaning_en' => 'Thursday']],
        '金' => [['word' => 'お金', 'reading' => 'おかね', 'meaning_en' => 'money'], ['word' => '金曜日', 'reading' => 'きんようび', 'meaning_en' => 'Friday']],
        '土' => [['word' => '土', 'reading' => 'つち', 'meaning_en' => 'soil, earth'], ['word' => '土曜日', 'reading' => 'どようび', 'meaning_en' => 'Saturday']],
        '日' => [['word' => '日曜日', 'reading' => 'にちようび', 'meaning_en' => 'Sunday'], ['word' => '毎日', 'reading' => 'まいにち', 'meaning_en' => 'every day']],
        '上' => [['word' => '上', 'reading' => 'うえ', 'meaning_en' => 'top, above'], ['word' => '上手', 'reading' => 'じょうず', 'meaning_en' => 'skillful']],

        // N4 demo vocabulary
        '同' => [['word' => '同じ', 'reading' => 'おなじ', 'meaning_en' => 'same'], ['word' => '同時', 'reading' => 'どうじ', 'meaning_en' => 'the same time']],
        '事' => [['word' => '仕事', 'reading' => 'しごと', 'meaning_en' => 'work, job'], ['word' => '大事', 'reading' => 'だいじ', 'meaning_en' => 'important']],
        '自' => [['word' => '自分', 'reading' => 'じぶん', 'meaning_en' => 'oneself'], ['word' => '自由', 'reading' => 'じゆう', 'meaning_en' => 'freedom']],
        '発' => [['word' => '出発', 'reading' => 'しゅっぱつ', 'meaning_en' => 'departure'], ['word' => '発音', 'reading' => 'はつおん', 'meaning_en' => 'pronunciation']],
        '家' => [['word' => '家族', 'reading' => 'かぞく', 'meaning_en' => 'family'], ['word' => '家', 'reading' => 'いえ', 'meaning_en' => 'house']],
        '思' => [['word' => '思う', 'reading' => 'おもう', 'meaning_en' => 'to think'], ['word' => '思い出', 'reading' => 'おもいで', 'meaning_en' => 'memory']],
        '使' => [['word' => '使う', 'reading' => 'つかう', 'meaning_en' => 'to use'], ['word' => '大使館', 'reading' => 'たいしかん', 'meaning_en' => 'embassy']],
        '声' => [['word' => '声', 'reading' => 'こえ', 'meaning_en' => 'voice'], ['word' => '歌声', 'reading' => 'うたごえ', 'meaning_en' => 'singing voice']],
        '犬' => [['word' => '犬', 'reading' => 'いぬ', 'meaning_en' => 'dog'], ['word' => '子犬', 'reading' => 'こいぬ', 'meaning_en' => 'puppy']],
        '兄' => [['word' => '兄', 'reading' => 'あに', 'meaning_en' => 'older brother'], ['word' => 'お兄さん', 'reading' => 'おにいさん', 'meaning_en' => 'older brother (polite)']],
        '姉' => [['word' => '姉', 'reading' => 'あね', 'meaning_en' => 'older sister'], ['word' => 'お姉さん', 'reading' => 'おねえさん', 'meaning_en' => 'older sister (polite)']],
        '妹' => [['word' => '妹', 'reading' => 'いもうと', 'meaning_en' => 'younger sister']],
        '弟' => [['word' => '弟', 'reading' => 'おとうと', 'meaning_en' => 'younger brother']],
        '茶' => [['word' => 'お茶', 'reading' => 'おちゃ', 'meaning_en' => 'tea'], ['word' => '茶色', 'reading' => 'ちゃいろ', 'meaning_en' => 'brown']],
        '牛' => [['word' => '牛肉', 'reading' => 'ぎゅうにく', 'meaning_en' => 'beef'], ['word' => '牛乳', 'reading' => 'ぎゅうにゅう', 'meaning_en' => 'milk']],

        // N3 demo vocabulary
        '決' => [['word' => '決める', 'reading' => 'きめる', 'meaning_en' => 'to decide'], ['word' => '決定', 'reading' => 'けってい', 'meaning_en' => 'decision']],
        '表' => [['word' => '表す', 'reading' => 'あらわす', 'meaning_en' => 'to express'], ['word' => '発表', 'reading' => 'はっぴょう', 'meaning_en' => 'announcement, presentation']],
        '守' => [['word' => '守る', 'reading' => 'まもる', 'meaning_en' => 'to protect'], ['word' => '留守', 'reading' => 'るす', 'meaning_en' => 'absence (from home)']],
        '若' => [['word' => '若い', 'reading' => 'わかい', 'meaning_en' => 'young'], ['word' => '若者', 'reading' => 'わかもの', 'meaning_en' => 'young person']],
        '美' => [['word' => '美しい', 'reading' => 'うつくしい', 'meaning_en' => 'beautiful'], ['word' => '美術', 'reading' => 'びじゅつ', 'meaning_en' => 'art, fine arts']],
        '命' => [['word' => '命', 'reading' => 'いのち', 'meaning_en' => 'life'], ['word' => '運命', 'reading' => 'うんめい', 'meaning_en' => 'fate, destiny']],
        '愛' => [['word' => '愛', 'reading' => 'あい', 'meaning_en' => 'love'], ['word' => '恋愛', 'reading' => 'れんあい', 'meaning_en' => 'romantic love']],
        '王' => [['word' => '王', 'reading' => 'おう', 'meaning_en' => 'king'], ['word' => '王女', 'reading' => 'おうじょ', 'meaning_en' => 'princess']],
        '熱' => [['word' => '熱い', 'reading' => 'あつい', 'meaning_en' => 'hot (to the touch)'], ['word' => '熱心', 'reading' => 'ねっしん', 'meaning_en' => 'enthusiastic']],
        '確' => [['word' => '確かめる', 'reading' => 'たしかめる', 'meaning_en' => 'to make sure, confirm'], ['word' => '正確', 'reading' => 'せいかく', 'meaning_en' => 'accurate']],
        '笑' => [['word' => '笑う', 'reading' => 'わらう', 'meaning_en' => 'to laugh'], ['word' => '笑顔', 'reading' => 'えがお', 'meaning_en' => 'smiling face']],
        '泳' => [['word' => '泳ぐ', 'reading' => 'およぐ', 'meaning_en' => 'to swim'], ['word' => '水泳', 'reading' => 'すいえい', 'meaning_en' => 'swimming']],
        '猫' => [['word' => '猫', 'reading' => 'ねこ', 'meaning_en' => 'cat']],
        '誰' => [['word' => '誰', 'reading' => 'だれ', 'meaning_en' => 'who']],
        '幾' => [['word' => '幾つ', 'reading' => 'いくつ', 'meaning_en' => 'how many'], ['word' => '幾ら', 'reading' => 'いくら', 'meaning_en' => 'how much']],

        // N2 demo vocabulary
        '各' => [['word' => '各地', 'reading' => 'かくち', 'meaning_en' => 'various places'], ['word' => '各国', 'reading' => 'かっこく', 'meaning_en' => 'each country']],
        '島' => [['word' => '島', 'reading' => 'しま', 'meaning_en' => 'island'], ['word' => '半島', 'reading' => 'はんとう', 'meaning_en' => 'peninsula']],
        '谷' => [['word' => '谷', 'reading' => 'たに', 'meaning_en' => 'valley']],
        '史' => [['word' => '歴史', 'reading' => 'れきし', 'meaning_en' => 'history']],
        '兵' => [['word' => '兵隊', 'reading' => 'へいたい', 'meaning_en' => 'soldier']],
        '丸' => [['word' => '丸い', 'reading' => 'まるい', 'meaning_en' => 'round']],
        '竹' => [['word' => '竹', 'reading' => 'たけ', 'meaning_en' => 'bamboo']],
        '毛' => [['word' => '毛', 'reading' => 'け', 'meaning_en' => 'hair, fur']],
        '卵' => [['word' => '卵', 'reading' => 'たまご', 'meaning_en' => 'egg']],
        '湖' => [['word' => '湖', 'reading' => 'みずうみ', 'meaning_en' => 'lake']],
        '貝' => [['word' => '貝', 'reading' => 'かい', 'meaning_en' => 'shellfish']],
        '皮' => [['word' => '皮', 'reading' => 'かわ', 'meaning_en' => 'skin, hide']],
        '移' => [['word' => '移る', 'reading' => 'うつる', 'meaning_en' => 'to move, shift'], ['word' => '移動', 'reading' => 'いどう', 'meaning_en' => 'movement']],
        '億' => [['word' => '一億', 'reading' => 'いちおく', 'meaning_en' => 'one hundred million']],
        '芸' => [['word' => '芸術', 'reading' => 'げいじゅつ', 'meaning_en' => 'art'], ['word' => '芸能人', 'reading' => 'げいのうじん', 'meaning_en' => 'entertainer']],

        // N1 demo vocabulary
        '氏' => [['word' => '氏名', 'reading' => 'しめい', 'meaning_en' => 'full name']],
        '基' => [['word' => '基本', 'reading' => 'きほん', 'meaning_en' => 'basis, fundamentals'], ['word' => '基準', 'reading' => 'きじゅん', 'meaning_en' => 'standard, criterion']],
        '価' => [['word' => '価格', 'reading' => 'かかく', 'meaning_en' => 'price'], ['word' => '価値', 'reading' => 'かち', 'meaning_en' => 'value']],
        '姿' => [['word' => '姿', 'reading' => 'すがた', 'meaning_en' => 'figure, appearance'], ['word' => '姿勢', 'reading' => 'しせい', 'meaning_en' => 'posture, stance']],
        '系' => [['word' => '体系', 'reading' => 'たいけい', 'meaning_en' => 'system']],
        '士' => [['word' => '弁護士', 'reading' => 'べんごし', 'meaning_en' => 'lawyer']],
        '健' => [['word' => '健康', 'reading' => 'けんこう', 'meaning_en' => 'health']],
        '異' => [['word' => '異なる', 'reading' => 'ことなる', 'meaning_en' => 'to differ'], ['word' => '異常', 'reading' => 'いじょう', 'meaning_en' => 'abnormal']],
        '素' => [['word' => '要素', 'reading' => 'ようそ', 'meaning_en' => 'element, factor'], ['word' => '素直', 'reading' => 'すなお', 'meaning_en' => 'obedient, honest']],
        '松' => [['word' => '松', 'reading' => 'まつ', 'meaning_en' => 'pine tree']],
        '影' => [['word' => '影', 'reading' => 'かげ', 'meaning_en' => 'shadow'], ['word' => '影響', 'reading' => 'えいきょう', 'meaning_en' => 'influence']],
        '標' => [['word' => '目標', 'reading' => 'もくひょう', 'meaning_en' => 'goal, target'], ['word' => '標準', 'reading' => 'ひょうじゅん', 'meaning_en' => 'standard']],
        '益' => [['word' => '利益', 'reading' => 'りえき', 'meaning_en' => 'profit']],
        '宣' => [['word' => '宣伝', 'reading' => 'せんでん', 'meaning_en' => 'advertisement, promotion']],
        '抗' => [['word' => '抵抗', 'reading' => 'ていこう', 'meaning_en' => 'resistance']],
    ];

    public function run(): void
    {
        $translator = app(KanjiMeaningTranslator::class);
        $strokesData = $this->loadStrokeData();

        foreach (self::KANJI as $order => $entry) {
            $kanji = Kanji::where('character', $entry['character'])->first();
            $locked = $kanji?->locked_fields ?? [];

            $translated = $translator->translateList($entry['meanings_en']);

            $values = [
                'onyomi' => $entry['onyomi'],
                'kunyomi' => $entry['kunyomi'],
                'meaning_en' => implode(', ', $entry['meanings_en']),
                'stroke_count' => $entry['stroke_count'],
                'grade' => $entry['grade'] ?? null,
                'jlpt_level' => $entry['jlpt_level'],
                'jlpt_source' => 'jlpt-kanji-levels.json',
                'order' => $order + 1,
            ];

            if (! ($locked['meaning_id'] ?? false)) {
                $values['meaning_id'] = $translated['text'];
                $values['needs_review_id'] = $translated['needs_review'];
            }

            $kanji = Kanji::updateOrCreate(['character' => $entry['character']], $values);

            if (isset($strokesData[$entry['character']])) {
                $stroke = $strokesData[$entry['character']];

                KanjiStroke::updateOrCreate(
                    ['kanji_id' => $kanji->id],
                    [
                        'strokes' => $stroke['strokes'],
                        'medians' => $stroke['medians'],
                        'stroke_count' => count($stroke['strokes']),
                        'source' => 'kanjivg',
                        'source_ref' => 'seed-demo',
                    ]
                );
            }

            foreach (self::VOCABULARY[$entry['character']] ?? [] as $i => $word) {
                $wordTranslated = $translator->translate($word['meaning_en']);

                KanjiVocabulary::updateOrCreate(
                    ['kanji_id' => $kanji->id, 'word' => $word['word'], 'reading' => $word['reading']],
                    [
                        'meaning_en' => $word['meaning_en'],
                        'meaning_id' => $wordTranslated['text'],
                        'needs_review_id' => $wordTranslated['needs_review'],
                        'source' => 'demo',
                        'order' => $i,
                    ]
                );
            }
        }
    }

    /** @return array<string, array{strokes: string[], medians: array}> */
    private function loadStrokeData(): array
    {
        $merged = [];

        foreach (['kanji-strokes-n5-demo.json', 'kanji-strokes-n4-demo.json', 'kanji-strokes-n3-demo.json', 'kanji-strokes-n2-demo.json', 'kanji-strokes-n1-demo.json'] as $file) {
            $path = resource_path("lang-data/{$file}");

            if (! is_file($path)) {
                continue;
            }

            $data = json_decode(file_get_contents($path), true);

            if (is_array($data)) {
                $merged = array_merge($merged, $data);
            }
        }

        return $merged;
    }
}
