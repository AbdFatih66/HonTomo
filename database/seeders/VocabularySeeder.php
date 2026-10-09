<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class VocabularySeeder extends Seeder
{
    /**
     * Minna no Nihongo I — Pelajaran 1 (Lesson 1) core vocabulary.
     * Source: Terjemahan dan Keterangan Tata Bahasa, Bab 1 (Kosa Kata).
     * Word/reading/meaning pairs only — no example sentences or grammar
     * prose copied from the book (see project copyright note in README).
     * Romaji added here for app use.
     */
    /**
     * Words whose Indonesian gloss used to be identical to a near-synonym's,
     * which made a quiz show the same answer text twice. firstOrCreate never
     * touches rows that already exist, so these are corrected explicitly and
     * the fix also lands on databases seeded before this change.
     *
     * @var array<string, array{0: string, 1: string}> japanese => [meaning_id, meaning_en]
     */
    private const MEANING_FIXES = [
        '先生' => ['guru, dosen (untuk orang lain)', 'teacher (used for others)'],
        '教師' => ['guru, dosen (menyebut profesi)', 'teacher (as a profession)'],
        '社員' => ['karyawan perusahaan tertentu', 'employee of a specific company'],
        '会社員' => ['karyawan perusahaan', 'company employee'],
    ];

    public function run(): void
    {
        $people = \App\Models\VocabularyCategory::where('slug', 'people-professions')->first();
        $countries = \App\Models\VocabularyCategory::where('slug', 'countries')->first();

        $peopleWords = [
            ['japanese' => '私', 'hiragana' => 'わたし', 'romaji' => 'watashi', 'meaning_id' => 'saya', 'meaning_en' => 'I / me'],
            ['japanese' => 'あなた', 'hiragana' => 'あなた', 'romaji' => 'anata', 'meaning_id' => 'anda', 'meaning_en' => 'you'],
            ['japanese' => 'あの方', 'hiragana' => 'あのかた', 'romaji' => 'ano kata', 'meaning_id' => 'orang itu (sopan)', 'meaning_en' => 'that person (polite)'],
            ['japanese' => '先生', 'hiragana' => 'せんせい', 'romaji' => 'sensei', 'meaning_id' => 'guru, dosen (untuk orang lain)', 'meaning_en' => 'teacher (used for others)'],
            ['japanese' => '教師', 'hiragana' => 'きょうし', 'romaji' => 'kyoushi', 'meaning_id' => 'guru, dosen (menyebut profesi)', 'meaning_en' => 'teacher (as a profession)'],
            ['japanese' => '学生', 'hiragana' => 'がくせい', 'romaji' => 'gakusei', 'meaning_id' => 'mahasiswa', 'meaning_en' => 'student'],
            ['japanese' => '会社員', 'hiragana' => 'かいしゃいん', 'romaji' => 'kaishain', 'meaning_id' => 'karyawan perusahaan', 'meaning_en' => 'company employee'],
            ['japanese' => '社員', 'hiragana' => 'しゃいん', 'romaji' => 'shain', 'meaning_id' => 'karyawan perusahaan tertentu', 'meaning_en' => 'employee of a specific company'],
            ['japanese' => '銀行員', 'hiragana' => 'ぎんこういん', 'romaji' => 'ginkouin', 'meaning_id' => 'pegawai bank', 'meaning_en' => 'bank employee'],
            ['japanese' => '医者', 'hiragana' => 'いしゃ', 'romaji' => 'isha', 'meaning_id' => 'dokter', 'meaning_en' => 'doctor'],
            ['japanese' => '研究者', 'hiragana' => 'けんきゅうしゃ', 'romaji' => 'kenkyuusha', 'meaning_id' => 'peneliti', 'meaning_en' => 'researcher'],
            ['japanese' => '大学', 'hiragana' => 'だいがく', 'romaji' => 'daigaku', 'meaning_id' => 'universitas', 'meaning_en' => 'university'],
            ['japanese' => '病院', 'hiragana' => 'びょういん', 'romaji' => 'byouin', 'meaning_id' => 'rumah sakit', 'meaning_en' => 'hospital'],
            ['japanese' => '誰', 'hiragana' => 'だれ', 'romaji' => 'dare', 'meaning_id' => 'siapa', 'meaning_en' => 'who'],
            ['japanese' => '歳', 'hiragana' => 'さい', 'romaji' => '-sai', 'meaning_id' => '~ tahun (usia)', 'meaning_en' => '~ years old'],
            ['japanese' => '何歳', 'hiragana' => 'なんさい', 'romaji' => 'nansai', 'meaning_id' => 'umur berapa', 'meaning_en' => 'how old'],
            ['japanese' => 'はい', 'hiragana' => 'はい', 'romaji' => 'hai', 'meaning_id' => 'ya', 'meaning_en' => 'yes'],
            ['japanese' => 'いいえ', 'hiragana' => 'いいえ', 'romaji' => 'iie', 'meaning_id' => 'tidak, bukan', 'meaning_en' => 'no'],
        ];

        $countryWords = [
            ['japanese' => 'アメリカ', 'hiragana' => 'アメリカ', 'romaji' => 'amerika', 'meaning_id' => 'Amerika Serikat', 'meaning_en' => 'United States'],
            ['japanese' => 'イギリス', 'hiragana' => 'イギリス', 'romaji' => 'igirisu', 'meaning_id' => 'Inggris', 'meaning_en' => 'United Kingdom'],
            ['japanese' => 'インド', 'hiragana' => 'インド', 'romaji' => 'indo', 'meaning_id' => 'India', 'meaning_en' => 'India'],
            ['japanese' => 'インドネシア', 'hiragana' => 'インドネシア', 'romaji' => 'indoneshia', 'meaning_id' => 'Indonesia', 'meaning_en' => 'Indonesia'],
            ['japanese' => '韓国', 'hiragana' => 'かんこく', 'romaji' => 'kankoku', 'meaning_id' => 'Korea Selatan', 'meaning_en' => 'South Korea'],
            ['japanese' => 'タイ', 'hiragana' => 'タイ', 'romaji' => 'tai', 'meaning_id' => 'Thailand', 'meaning_en' => 'Thailand'],
            ['japanese' => '中国', 'hiragana' => 'ちゅうごく', 'romaji' => 'chuugoku', 'meaning_id' => 'Cina', 'meaning_en' => 'China'],
            ['japanese' => 'ドイツ', 'hiragana' => 'ドイツ', 'romaji' => 'doitsu', 'meaning_id' => 'Jerman', 'meaning_en' => 'Germany'],
            ['japanese' => '日本', 'hiragana' => 'にほん', 'romaji' => 'nihon', 'meaning_id' => 'Jepang', 'meaning_en' => 'Japan'],
            ['japanese' => 'ブラジル', 'hiragana' => 'ブラジル', 'romaji' => 'burajiru', 'meaning_id' => 'Brasil', 'meaning_en' => 'Brazil'],
        ];

        foreach ($peopleWords as $word) {
            \App\Models\Vocabulary::firstOrCreate(
                ['japanese' => $word['japanese'], 'hiragana' => $word['hiragana']],
                $word + ['category_id' => $people?->id, 'jlpt_level' => 'N5', 'difficulty' => 1, 'is_active' => true]
            );
        }

        foreach ($countryWords as $word) {
            \App\Models\Vocabulary::firstOrCreate(
                ['japanese' => $word['japanese'], 'hiragana' => $word['hiragana']],
                $word + ['category_id' => $countries?->id, 'jlpt_level' => 'N5', 'difficulty' => 1, 'is_active' => true]
            );
        }

        // ---- Pelajaran 2 ----

        $demonstratives = \App\Models\VocabularyCategory::where('slug', 'demonstratives')->first();
        $objects = \App\Models\VocabularyCategory::where('slug', 'everyday-objects')->first();
        $languages = \App\Models\VocabularyCategory::where('slug', 'languages-terms')->first();

        $demonstrativeWords = [
            ['japanese' => 'これ', 'hiragana' => 'これ', 'romaji' => 'kore', 'meaning_id' => 'ini (dekat pembicara)', 'meaning_en' => 'this (near speaker)'],
            ['japanese' => 'それ', 'hiragana' => 'それ', 'romaji' => 'sore', 'meaning_id' => 'itu (dekat lawan bicara)', 'meaning_en' => 'that (near listener)'],
            ['japanese' => 'あれ', 'hiragana' => 'あれ', 'romaji' => 'are', 'meaning_id' => 'itu (jauh dari keduanya)', 'meaning_en' => 'that (far from both)'],
            ['japanese' => 'この', 'hiragana' => 'この', 'romaji' => 'kono', 'meaning_id' => '~ ini (menerangkan benda dekat pembicara)', 'meaning_en' => 'this ~ (near speaker)'],
            ['japanese' => 'その', 'hiragana' => 'その', 'romaji' => 'sono', 'meaning_id' => '~ itu (menerangkan benda dekat lawan bicara)', 'meaning_en' => 'that ~ (near listener)'],
            ['japanese' => 'あの', 'hiragana' => 'あの', 'romaji' => 'ano', 'meaning_id' => '~ itu (menerangkan benda jauh dari keduanya)', 'meaning_en' => 'that ~ (far from both)'],
            ['japanese' => '何', 'hiragana' => 'なん', 'romaji' => 'nan', 'meaning_id' => 'apa', 'meaning_en' => 'what'],
            ['japanese' => 'そう', 'hiragana' => 'そう', 'romaji' => 'sou', 'meaning_id' => 'begitu', 'meaning_en' => 'so / that\'s right'],
        ];

        $objectWords = [
            ['japanese' => '本', 'hiragana' => 'ほん', 'romaji' => 'hon', 'meaning_id' => 'buku', 'meaning_en' => 'book'],
            ['japanese' => '辞書', 'hiragana' => 'じしょ', 'romaji' => 'jisho', 'meaning_id' => 'kamus', 'meaning_en' => 'dictionary'],
            ['japanese' => '雑誌', 'hiragana' => 'ざっし', 'romaji' => 'zasshi', 'meaning_id' => 'majalah', 'meaning_en' => 'magazine'],
            ['japanese' => '新聞', 'hiragana' => 'しんぶん', 'romaji' => 'shinbun', 'meaning_id' => 'koran, surat kabar', 'meaning_en' => 'newspaper'],
            ['japanese' => 'ノート', 'hiragana' => 'ノート', 'romaji' => 'nooto', 'meaning_id' => 'buku tulis, buku catatan', 'meaning_en' => 'notebook'],
            ['japanese' => '手帳', 'hiragana' => 'てちょう', 'romaji' => 'techou', 'meaning_id' => 'buku agenda', 'meaning_en' => 'planner / pocket notebook'],
            ['japanese' => '名刺', 'hiragana' => 'めいし', 'romaji' => 'meishi', 'meaning_id' => 'kartu nama', 'meaning_en' => 'business card'],
            ['japanese' => 'カード', 'hiragana' => 'カード', 'romaji' => 'kaado', 'meaning_id' => 'kartu', 'meaning_en' => 'card'],
            ['japanese' => '鉛筆', 'hiragana' => 'えんぴつ', 'romaji' => 'enpitsu', 'meaning_id' => 'pensil', 'meaning_en' => 'pencil'],
            ['japanese' => 'ボールペン', 'hiragana' => 'ボールペン', 'romaji' => 'boorupen', 'meaning_id' => 'bolpoin', 'meaning_en' => 'ballpoint pen'],
            ['japanese' => 'シャープペンシル', 'hiragana' => 'シャープペンシル', 'romaji' => 'shaapupenshiru', 'meaning_id' => 'pensil mekanik', 'meaning_en' => 'mechanical pencil'],
            ['japanese' => '鍵', 'hiragana' => 'かぎ', 'romaji' => 'kagi', 'meaning_id' => 'kunci', 'meaning_en' => 'key'],
            ['japanese' => '時計', 'hiragana' => 'とけい', 'romaji' => 'tokei', 'meaning_id' => 'jam, arloji', 'meaning_en' => 'clock / watch'],
            ['japanese' => '傘', 'hiragana' => 'かさ', 'romaji' => 'kasa', 'meaning_id' => 'payung', 'meaning_en' => 'umbrella'],
            ['japanese' => '鞄', 'hiragana' => 'かばん', 'romaji' => 'kaban', 'meaning_id' => 'tas', 'meaning_en' => 'bag'],
            ['japanese' => 'CD', 'hiragana' => 'CD', 'romaji' => 'shiidii', 'meaning_id' => 'CD', 'meaning_en' => 'CD'],
            ['japanese' => 'テレビ', 'hiragana' => 'テレビ', 'romaji' => 'terebi', 'meaning_id' => 'televisi', 'meaning_en' => 'television'],
            ['japanese' => 'ラジオ', 'hiragana' => 'ラジオ', 'romaji' => 'rajio', 'meaning_id' => 'radio', 'meaning_en' => 'radio'],
            ['japanese' => 'カメラ', 'hiragana' => 'カメラ', 'romaji' => 'kamera', 'meaning_id' => 'kamera', 'meaning_en' => 'camera'],
            ['japanese' => 'コンピューター', 'hiragana' => 'コンピューター', 'romaji' => 'konpyuutaa', 'meaning_id' => 'komputer', 'meaning_en' => 'computer'],
            ['japanese' => '車', 'hiragana' => 'くるま', 'romaji' => 'kuruma', 'meaning_id' => 'mobil', 'meaning_en' => 'car'],
            ['japanese' => '机', 'hiragana' => 'つくえ', 'romaji' => 'tsukue', 'meaning_id' => 'meja', 'meaning_en' => 'desk'],
            ['japanese' => '椅子', 'hiragana' => 'いす', 'romaji' => 'isu', 'meaning_id' => 'kursi', 'meaning_en' => 'chair'],
            ['japanese' => 'チョコレート', 'hiragana' => 'チョコレート', 'romaji' => 'chokoreeto', 'meaning_id' => 'coklat', 'meaning_en' => 'chocolate'],
            ['japanese' => 'コーヒー', 'hiragana' => 'コーヒー', 'romaji' => 'koohii', 'meaning_id' => 'kopi', 'meaning_en' => 'coffee'],
            ['japanese' => 'お土産', 'hiragana' => 'おみやげ', 'romaji' => 'omiyage', 'meaning_id' => 'oleh-oleh', 'meaning_en' => 'souvenir / gift'],
        ];

        $languageWords = [
            ['japanese' => '英語', 'hiragana' => 'えいご', 'romaji' => 'eigo', 'meaning_id' => 'bahasa Inggris', 'meaning_en' => 'English (language)'],
            ['japanese' => '日本語', 'hiragana' => 'にほんご', 'romaji' => 'nihongo', 'meaning_id' => 'bahasa Jepang', 'meaning_en' => 'Japanese (language)'],
            ['japanese' => '語', 'hiragana' => 'ご', 'romaji' => '-go', 'meaning_id' => 'bahasa ~', 'meaning_en' => 'the ~ language'],
        ];

        foreach ($demonstrativeWords as $word) {
            \App\Models\Vocabulary::firstOrCreate(
                ['japanese' => $word['japanese'], 'hiragana' => $word['hiragana']],
                $word + ['category_id' => $demonstratives?->id, 'jlpt_level' => 'N5', 'difficulty' => 1, 'is_active' => true]
            );
        }

        foreach ($objectWords as $word) {
            \App\Models\Vocabulary::firstOrCreate(
                ['japanese' => $word['japanese'], 'hiragana' => $word['hiragana']],
                $word + ['category_id' => $objects?->id, 'jlpt_level' => 'N5', 'difficulty' => 1, 'is_active' => true]
            );
        }

        foreach ($languageWords as $word) {
            \App\Models\Vocabulary::firstOrCreate(
                ['japanese' => $word['japanese'], 'hiragana' => $word['hiragana']],
                $word + ['category_id' => $languages?->id, 'jlpt_level' => 'N5', 'difficulty' => 1, 'is_active' => true]
            );
        }

        $this->applyMeaningFixes();
    }

    private function applyMeaningFixes(): void
    {
        foreach (self::MEANING_FIXES as $japanese => [$meaningId, $meaningEn]) {
            \App\Models\Vocabulary::where('japanese', $japanese)
                ->update(['meaning_id' => $meaningId, 'meaning_en' => $meaningEn]);
        }
    }
}
