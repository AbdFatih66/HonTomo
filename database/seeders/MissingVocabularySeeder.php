<?php

namespace Database\Seeders;

use App\Models\Vocabulary;
use App\Models\VocabularyCategory;
use Illuminate\Database\Seeder;

class MissingVocabularySeeder extends Seeder
{
    /**
     * Fills the gaps between the app's word lists for Pelajaran 1-5 and 6-25
     * and the
     * "Kosa Kata" pages of the textbook. Everything here is a word/reading/
     * meaning triple plus romaji for app use — no example sentences or
     * explanatory prose from the book.
     *
     * Two kinds of category are used:
     *   pelajaran-N            existing lists, already wired into the quiz
     *   pelajaran-N-ungkapan   set phrases and greetings, also quizzed
     *                          (registered in LessonQuestionSeeder)
     *   pelajaran-N-nama       proper nouns from the book's cast of
     *                          companies, schools and places. Deliberately
     *                          NOT wired into the quiz: "what does IMC mean"
     *                          is not a useful question.
     *
     * Additive and repeatable — firstOrCreate keyed on (japanese, hiragana),
     * so it never disturbs rows that already exist or any user progress.
     *
     *   php artisan db:seed --class=MissingVocabularySeeder
     */
    public function run(): void
    {
        foreach ($this->groups() as $slug => $group) {
            $category = VocabularyCategory::firstOrCreate(
                ['slug' => $slug],
                ['name_id' => $group['name_id'], 'name_en' => $group['name_en']]
            );

            foreach ($group['words'] as $w) {
                // Exact match done in PHP: MySQL's default collation (utf8mb4_unicode_ci)
                // treats また = まだ (and か = が = カ) as EQUAL, so a plain lookup would
                // find the wrong word and silently skip the new one.
                $exists = Vocabulary::where('japanese', $w[0])->where('hiragana', $w[1])->get()
                    ->contains(fn ($v) => $v->japanese === $w[0] && $v->hiragana === $w[1]);

                if ($exists) {
                    continue;
                }

                Vocabulary::create([
                    'japanese' => $w[0],
                    'hiragana' => $w[1],
                    'romaji' => $w[2],
                    'meaning_id' => $w[3],
                    'meaning_en' => $w[4],
                    'category_id' => $category->id,
                    'jlpt_level' => 'N5',
                    'difficulty' => 1,
                    'is_active' => true,
                ]);
            }
        }

        $this->refineGlosses();
    }

    /**
     * Membedakan arti dua kata yang glossnya identik dalam satu pelajaran
     * (menurut buku: お茶 = teh Jepang/hijau, 紅茶 = teh). Hanya mengubah teks
     * arti; id kata dan progres user tidak tersentuh. LessonQuestionSeeder
     * lalu memperbaiki pilihan jawaban yang labelnya ikut berubah.
     */
    private function refineGlosses(): void
    {
        $glosses = [
            ['お茶', 'おちゃ', 'teh Jepang (teh hijau)', 'Japanese green tea'],
            ['夜', 'よる', 'malam (bentuk lain dari 晩)', 'night (alt. of 晩)'],
        ];

        foreach ($glosses as [$japanese, $hiragana, $meaningId, $meaningEn]) {
            Vocabulary::where('japanese', $japanese)
                ->where('hiragana', $hiragana)
                ->update(['meaning_id' => $meaningId, 'meaning_en' => $meaningEn]);
        }
    }

    private function groups(): array
    {
        return [
            'people-professions' => [
                'name_id' => 'Orang & Profesi',
                'name_en' => 'People & Professions',
                'words' => [
                    ['～さん', 'さん', '-san', 'Bapak ~, Ibu ~, Saudara ~', 'Mr / Ms ~'],
                    ['～ちゃん', 'ちゃん', '-chan', 'panggilan akrab untuk anak', 'affectionate suffix for a child'],
                    ['～人', 'じん', '-jin', 'orang ~ (kebangsaan)', 'a person of ~ nationality'],
                    ['あの人', 'あのひと', 'ano hito', 'orang itu', 'that person'],
                    ['どなた', 'どなた', 'donata', 'siapa (sopan)', 'who (polite)'],
                    ['おいくつ', 'おいくつ', 'oikutsu', 'berumur berapa (sopan)', 'how old (polite)'],
                    ['名前', 'なまえ', 'namae', 'nama', 'name'],
                ],
            ],

            'pelajaran-1-ungkapan' => [
                'name_id' => 'Ungkapan Pelajaran 1',
                'name_en' => 'Lesson 1 Expressions',
                'words' => [
                    ['初めまして', 'はじめまして', 'hajimemashite', 'apa kabar (saat pertama berkenalan)', 'how do you do'],
                    ['～から来ました', '～からきました', '~ kara kimashita', 'saya datang dari ~', 'I come from ~'],
                    ['どうぞよろしく', 'どうぞよろしく', 'douzo yoroshiku', 'senang berkenalan dengan Anda', 'pleased to meet you'],
                    ['失礼ですが', 'しつれいですが', 'shitsurei desu ga', 'maaf, kalau boleh tahu', 'excuse me, but ~'],
                    ['お名前は', 'おなまえは', 'onamae wa', 'nama Anda siapa', 'may I have your name'],
                    ['こちらは～さんです', 'こちらは～さんです', 'kochira wa ~-san desu', 'ini Bapak/Ibu ~ (memperkenalkan)', 'this is Mr/Ms ~'],
                ],
            ],

            'pelajaran-1-nama' => [
                'name_id' => 'Nama dalam Pelajaran 1',
                'name_en' => 'Lesson 1 Proper Nouns',
                'words' => [
                    ['IMC', 'アイエムシー', 'IMC', 'IMC (nama perusahaan fiktif)', 'IMC (fictional company)'],
                    ['パワー電気', 'パワーでんき', 'Pawaa Denki', 'Power Denki (nama perusahaan fiktif)', 'Power Electric (fictional company)'],
                    ['ブラジルエアー', 'ブラジルエアー', 'Burajiru Eaa', 'Brasil Air (nama maskapai fiktif)', 'Brazil Air (fictional airline)'],
                    ['AKC', 'エーケーシー', 'AKC', 'AKC (nama perusahaan fiktif)', 'AKC (fictional company)'],
                    ['神戸病院', 'こうべびょういん', 'Koube Byouin', 'Rumah Sakit Kobe (fiktif)', 'Kobe Hospital (fictional)'],
                    ['さくら大学', 'さくらだいがく', 'Sakura Daigaku', 'Universitas Sakura (fiktif)', 'Sakura University (fictional)'],
                    ['富士大学', 'ふじだいがく', 'Fuji Daigaku', 'Universitas Fuji (fiktif)', 'Fuji University (fictional)'],
                ],
            ],

            'pelajaran-2-ungkapan' => [
                'name_id' => 'Ungkapan Pelajaran 2',
                'name_en' => 'Lesson 2 Expressions',
                'words' => [
                    ['あのう', 'あのう', 'anou', 'anu, hmm (menarik perhatian)', 'um, excuse me'],
                    ['えっ', 'えっ', 'e', 'eh? (terkejut)', 'huh? (surprise)'],
                    ['どうぞ', 'どうぞ', 'douzo', 'silakan', 'please, go ahead'],
                    ['どうもありがとう', 'どうもありがとう', 'doumo arigatou', 'terima kasih banyak', 'thank you very much'],
                    ['そうですか', 'そうですか', 'sou desu ka', 'oh, begitu', 'I see'],
                    ['違います', 'ちがいます', 'chigaimasu', 'bukan, keliru', 'no, that is wrong'],
                    ['あ', 'あ', 'a', 'ah (seru singkat)', 'ah'],
                    ['これからお世話になります', 'これからおせわになります', 'korekara osewa ni narimasu', 'mohon bantuannya mulai sekarang', 'I look forward to your kind help'],
                    ['こちらこそ', 'こちらこそ', 'kochirakoso', 'saya juga (membalas salam)', 'likewise'],
                ],
            ],

            'pelajaran-3' => [
                'name_id' => 'Kosakata Pelajaran 3',
                'name_en' => 'Lesson 3 Vocabulary',
                'words' => [
                    ['靴', 'くつ', 'kutsu', 'sepatu', 'shoes'],
                    ['ネクタイ', 'ネクタイ', 'nekutai', 'dasi', 'necktie'],
                    ['ワイン', 'ワイン', 'wain', 'anggur (minuman)', 'wine'],
                    ['売り場', 'うりば', 'uriba', 'tempat penjualan', 'sales floor / counter'],
                    ['地下', 'ちか', 'chika', 'lantai bawah tanah', 'basement'],
                    ['～階', 'かい', '-kai', 'lantai ke-~', 'the ~th floor'],
                    ['何階', 'なんがい', 'nangai', 'lantai berapa', 'which floor'],
                    ['～円', 'えん', '-en', '~ yen', '~ yen'],
                    ['いくら', 'いくら', 'ikura', 'berapa harganya', 'how much'],
                    ['百', 'ひゃく', 'hyaku', 'seratus', 'hundred'],
                    ['千', 'せん', 'sen', 'seribu', 'thousand'],
                    ['万', 'まん', 'man', 'sepuluh ribu', 'ten thousand'],
                    ['お手洗い', 'おてあらい', 'otearai', 'kamar kecil', 'restroom'],
                    ['すみません', 'すみません', 'sumimasen', 'permisi, maaf', 'excuse me / sorry'],
                    ['どうも', 'どうも', 'doumo', 'terima kasih (singkat)', 'thanks'],
                    ['いらっしゃいませ', 'いらっしゃいませ', 'irasshaimase', 'selamat datang (di toko)', 'welcome (to a shop)'],
                    ['見せてください', 'みせてください', 'misete kudasai', 'tolong perlihatkan', 'please show me'],
                    ['じゃ', 'じゃ', 'ja', 'kalau begitu', 'well then'],
                    ['ください', 'ください', 'kudasai', 'tolong beri saya', 'please give me'],
                    ['イタリア', 'イタリア', 'Itaria', 'Italia', 'Italy'],
                    ['スイス', 'スイス', 'Suisu', 'Swiss', 'Switzerland'],
                    ['フランス', 'フランス', 'Furansu', 'Prancis', 'France'],
                    ['ジャカルタ', 'ジャカルタ', 'Jakaruta', 'Jakarta', 'Jakarta'],
                    ['バンコク', 'バンコク', 'Bankoku', 'Bangkok', 'Bangkok'],
                    ['ベルリン', 'ベルリン', 'Berurin', 'Berlin', 'Berlin'],
                    ['新大阪', 'しんおおさか', 'Shin-Oosaka', 'Shin-Osaka (nama stasiun)', 'Shin-Osaka (station)'],
                ],
            ],

            'pelajaran-4' => [
                'name_id' => 'Kosakata Pelajaran 4',
                'name_en' => 'Lesson 4 Vocabulary',
                'words' => [
                    ['夜', 'よる', 'yoru', 'malam', 'night'],
                    ['～から', 'から', 'kara', 'dari ~', 'from ~'],
                    ['～まで', 'まで', 'made', 'sampai ~', 'until ~'],
                    ['～と', 'と', 'to', 'dan ~ (antar kata benda)', 'and ~ (between nouns)'],
                    ['大変ですね', 'たいへんですね', 'taihen desu ne', 'wah, berat ya', 'that sounds tough'],
                    ['番号', 'ばんごう', 'bangou', 'nomor', 'number'],
                    ['何番', 'なんばん', 'nanban', 'nomor berapa', 'what number'],
                    ['ニューヨーク', 'ニューヨーク', 'Nyuuyooku', 'New York', 'New York'],
                    ['ペキン', 'ペキン', 'Pekin', 'Beijing', 'Beijing'],
                    ['ロサンゼルス', 'ロサンゼルス', 'Rosanzerusu', 'Los Angeles', 'Los Angeles'],
                    ['ロンドン', 'ロンドン', 'Rondon', 'London', 'London'],
                    ['あすか', 'あすか', 'Asuka', 'Asuka (nama toko fiktif)', 'Asuka (fictional shop)'],
                    ['アップル銀行', 'アップルぎんこう', 'Appuru Ginkou', 'Bank Apple (fiktif)', 'Apple Bank (fictional)'],
                    ['みどり図書館', 'みどりとしょかん', 'Midori Toshokan', 'Perpustakaan Midori (fiktif)', 'Midori Library (fictional)'],
                    ['やまと美術館', 'やまとびじゅつかん', 'Yamato Bijutsukan', 'Gedung Kesenian Yamato (fiktif)', 'Yamato Art Museum (fictional)'],
                ],
            ],

            'pelajaran-5' => [
                'name_id' => 'Kosakata Pelajaran 5',
                'name_en' => 'Lesson 5 Vocabulary',
                'words' => [
                    ['～日', 'にち', '-nichi', 'tanggal ~, hari ke-~', 'the ~th day'],
                    ['何日', 'なんにち', 'nannichi', 'tanggal berapa', 'which day of the month'],
                    ['いつ', 'いつ', 'itsu', 'kapan', 'when'],
                    ['誕生日', 'たんじょうび', 'tanjoubi', 'hari ulang tahun', 'birthday'],
                    ['そうですね', 'そうですね', 'sou desu ne', 'iya, betul / hmm (berpikir)', 'that is right / let me see'],
                    ['どうもありがとうございました', 'どうもありがとうございました', 'doumo arigatou gozaimashita', 'terima kasih banyak (atas yang sudah dilakukan)', 'thank you very much (for what you did)'],
                    ['どういたしまして', 'どういたしまして', 'dou itashimashite', 'sama-sama', "you're welcome"],
                    ['～番線', 'ばんせん', '-bansen', 'jalur ke-~', 'platform ~'],
                    ['何番線', 'なんばんせん', 'nanbansen', 'jalur berapa', 'which platform'],
                    ['次の', 'つぎの', 'tsugi no', 'berikutnya', 'the next ~'],
                    ['普通', 'ふつう', 'futsuu', 'kereta biasa (berhenti di tiap stasiun)', 'local train'],
                    ['急行', 'きゅうこう', 'kyuukou', 'kereta ekspres', 'express train'],
                    ['特急', 'とっきゅう', 'tokkyuu', 'kereta ekspres khusus', 'limited express'],
                    ['甲子園', 'こうしえん', 'Koushien', 'Koshien (nama stadion)', 'Koshien (stadium)'],
                    ['大阪城', 'おおさかじょう', 'Oosakajou', 'Kastel Osaka', 'Osaka Castle'],

                    // Tanggal 1-24 — bacaannya tidak beraturan, jadi diajarkan
                    // satu per satu seperti di buku.
                    ['一日', 'ついたち', 'tsuitachi', 'tanggal 1', 'the 1st'],
                    ['二日', 'ふつか', 'futsuka', 'tanggal 2', 'the 2nd'],
                    ['三日', 'みっか', 'mikka', 'tanggal 3', 'the 3rd'],
                    ['四日', 'よっか', 'yokka', 'tanggal 4', 'the 4th'],
                    ['五日', 'いつか', 'itsuka', 'tanggal 5', 'the 5th'],
                    ['六日', 'むいか', 'muika', 'tanggal 6', 'the 6th'],
                    ['七日', 'なのか', 'nanoka', 'tanggal 7', 'the 7th'],
                    ['八日', 'ようか', 'youka', 'tanggal 8', 'the 8th'],
                    ['九日', 'ここのか', 'kokonoka', 'tanggal 9', 'the 9th'],
                    ['十日', 'とおか', 'tooka', 'tanggal 10', 'the 10th'],
                    ['十一日', 'じゅういちにち', 'juuichinichi', 'tanggal 11', 'the 11th'],
                    ['十二日', 'じゅうににち', 'juuninichi', 'tanggal 12', 'the 12th'],
                    ['十三日', 'じゅうさんにち', 'juusannichi', 'tanggal 13', 'the 13th'],
                    ['十四日', 'じゅうよっか', 'juuyokka', 'tanggal 14', 'the 14th'],
                    ['十五日', 'じゅうごにち', 'juugonichi', 'tanggal 15', 'the 15th'],
                    ['十六日', 'じゅうろくにち', 'juurokunichi', 'tanggal 16', 'the 16th'],
                    ['十七日', 'じゅうしちにち', 'juushichinichi', 'tanggal 17', 'the 17th'],
                    ['十八日', 'じゅうはちにち', 'juuhachinichi', 'tanggal 18', 'the 18th'],
                    ['十九日', 'じゅうくにち', 'juukunichi', 'tanggal 19', 'the 19th'],
                    ['二十日', 'はつか', 'hatsuka', 'tanggal 20', 'the 20th'],
                    ['二十一日', 'にじゅういちにち', 'nijuuichinichi', 'tanggal 21', 'the 21st'],
                    ['二十二日', 'にじゅうににち', 'nijuuninichi', 'tanggal 22', 'the 22nd'],
                    ['二十三日', 'にじゅうさんにち', 'nijuusannichi', 'tanggal 23', 'the 23rd'],
                    ['二十四日', 'にじゅうよっか', 'nijuuyokka', 'tanggal 24', 'the 24th'],
                ],
            ],

            'pelajaran-23' => [
                'name_id' => 'Kosakata Pelajaran 23 (pelengkap)',
                'name_en' => 'Lesson 23 Vocabulary (additions)',
                'words' => [
                    ['聞きます（先生に〜）', 'ききます', 'kikimasu', 'bertanya [kepada guru]', 'ask [a teacher]'],
                    ['出ます（お釣りが〜）', 'でます', 'demasu', 'keluar [uang kembalian]', 'come out [change]'],
                    ['〜目', 'め', '-me', 'yang ke 〜 (urutan)', '~th (order)'],
                ],
            ],

            // ── Pelajaran 6-15: dicocokkan dengan halaman "Kosa Kata" buku (halaman ke-2 tiap pelajaran) ──
            'pelajaran-6' => [
                'name_id' => 'Kosakata Pelajaran 6 (pelengkap)',
                'name_en' => 'Lesson 6 Vocabulary (additions)',
                'words' => [
                    ['手紙', 'てがみ', 'tegami', 'surat', 'letter'],
                    ['レポート', 'レポート', 'repooto', 'laporan', 'report'],
                    ['写真', 'しゃしん', 'shashin', 'foto', 'photo'],
                    ['ビデオ', 'ビデオ', 'bideo', 'kaset video, VHS', 'video tape'],
                    ['店', 'みせ', 'mise', 'toko', 'shop'],
                    ['庭', 'にわ', 'niwa', 'halaman', 'garden, yard'],
                    ['宿題', 'しゅくだい', 'shukudai', 'pekerjaan rumah, PR (〜をします: mengerjakan PR)', 'homework'],
                    ['テニス', 'テニス', 'tenisu', 'tenis (〜をします: bermain tenis)', 'tennis'],
                    ['サッカー', 'サッカー', 'sakkaa', 'sepak bola (〜をします: bermain sepak bola)', 'soccer'],
                    ['[お]花見', 'おはなみ', 'ohanami', 'hanami, menikmati bunga sakura (〜をします: melihat dan menikmati bunga sakura)', 'cherry-blossom viewing'],
                    ['何', 'なに', 'nani', 'apa', 'what'],
                    ['いっしょに', 'いっしょに', 'issho ni', 'bersama-sama', 'together'],
                    ['ちょっと', 'ちょっと', 'chotto', 'sebentar, sedikit', 'a little, a moment'],
                    ['いつも', 'いつも', 'itsumo', 'selalu', 'always'],
                    ['時々', 'ときどき', 'tokidoki', 'kadang-kadang', 'sometimes'],
                    ['それから', 'それから', 'sorekara', 'setelah itu, kemudian', 'after that, and then'],
                    ['ええ', 'ええ', 'ee', 'ya', 'yes'],
                    ['いいですね。', 'いいですね', 'ii desu ne', 'Bagus ya. / Baik ya.', 'That\'s nice.'],
                    ['わかりました。', 'わかりました', 'wakarimashita', 'Mengerti. / Baik.', 'I understand. / OK.'],
                    ['何ですか。', 'なんですか', 'nan desu ka', 'Apa?', 'What is it?'],
                    ['じゃ、また[あした]。', 'じゃ、また[あした]', 'ja, mata [ashita]', 'Ok, sampai jumpa [besok].', 'See you [tomorrow].'],
                    ['ミルク', 'ミルク', 'miruku', 'susu (bentuk lain dari 牛乳)', 'milk (alt. of 牛乳)'],
                ],
            ],

            'pelajaran-6-nama' => [
                'name_id' => 'Nama (Pelajaran 6)',
                'name_en' => 'Names (Lesson 6)',
                'words' => [
                    ['メキシコ', 'メキシコ', 'mekishiko', 'Meksiko', 'Mexico'],
                    ['大阪デパート', 'おおさかデパート', 'oosaka depaato', 'Osaka Depaato (toserba fiksi)', 'Osaka Department Store (fictional)'],
                    ['つるや', 'つるや', 'tsuruya', 'Tsuruya (restoran fiksi)', 'Tsuruya (fictional restaurant)'],
                    ['フランス屋', 'フランスや', 'furansuya', 'Furansuya (pasar swalayan fiksi)', 'Furansuya (fictional supermarket)'],
                    ['毎日屋', 'まいにちや', 'mainichiya', 'Mainichiya (pasar swalayan fiksi)', 'Mainichiya (fictional supermarket)'],
                ],
            ],

            'pelajaran-7' => [
                'name_id' => 'Kosakata Pelajaran 7 (pelengkap)',
                'name_en' => 'Lesson 7 Vocabulary (additions)',
                'words' => [
                    ['父', 'ちち', 'chichi', 'ayah (menyebut ayah sendiri)', 'father (my own)'],
                    ['母', 'はは', 'haha', 'ibu (menyebut ibu sendiri)', 'mother (my own)'],
                    ['お父さん', 'おとうさん', 'otousan', 'ayah (ayah orang lain; juga untuk memanggil ayah sendiri)', 'father (someone else\'s; also addressing own father)'],
                    ['お母さん', 'おかあさん', 'okaasan', 'ibu (ibu orang lain; juga untuk memanggil ibu sendiri)', 'mother (someone else\'s; also addressing own mother)'],
                    ['もう', 'もう', 'mou', 'sudah', 'already'],
                    ['まだ', 'まだ', 'mada', 'belum', 'not yet'],
                    ['これから', 'これから', 'korekara', 'mulai dari sekarang', 'from now on'],
                    ['〜、すてきですね。', '〜、すてきですね', '~, suteki desu ne', '[〜] bagus ya. / [〜] indah ya.', '[~] is nice, isn\'t it.'],
                    ['いらっしゃい。', 'いらっしゃい', 'irasshai', 'Selamat datang.', 'Welcome.'],
                    ['どうぞお上がりください。', 'どうぞおあがりください', 'douzo oagari kudasai', 'Silakan masuk.', 'Please come in.'],
                    ['失礼します。', 'しつれいします', 'shitsurei shimasu', 'Permisi.', 'Excuse me.'],
                    ['〜はいかがですか。', '〜はいかがですか', '~ wa ikaga desu ka', 'Bagaimana [〜]? (saat menawarkan sesuatu)', 'How about [~]? (offering)'],
                    ['いただきます。', 'いただきます', 'itadakimasu', 'Selamat makan! (sebelum makan/minum)', 'Let\'s eat! (before eating)'],
                    ['ごちそうさま[でした]。', 'ごちそうさま[でした]', 'gochisousama [deshita]', 'Terima kasih atas hidangannya. (sesudah makan/minum)', 'Thank you for the meal.'],
                ],
            ],

            'pelajaran-7-nama' => [
                'name_id' => 'Nama (Pelajaran 7)',
                'name_en' => 'Names (Lesson 7)',
                'words' => [
                    ['スペイン', 'スペイン', 'supein', 'Spanyol', 'Spain'],
                ],
            ],

            'pelajaran-8' => [
                'name_id' => 'Kosakata Pelajaran 8 (pelengkap)',
                'name_en' => 'Lesson 8 Vocabulary (additions)',
                'words' => [
                    ['所', 'ところ', 'tokoro', 'tempat', 'place'],
                    ['寮', 'りょう', 'ryou', 'asrama', 'dormitory'],
                    ['レストラン', 'レストラン', 'resutoran', 'restoran', 'restaurant'],
                    ['生活', 'せいかつ', 'seikatsu', 'kehidupan', 'life, living'],
                    ['[お]仕事', 'おしごと', 'oshigoto', 'pekerjaan (〜をします: bekerja)', 'work, job'],
                    ['どう', 'どう', 'dou', 'bagaimana', 'how'],
                    ['どんな〜', 'どんな', 'donna ~', 'yang bagaimana 〜', 'what kind of ~'],
                    ['とても', 'とても', 'totemo', 'sangat, 〜 sekali', 'very'],
                    ['あまり', 'あまり', 'amari', 'kurang begitu 〜, tidak begitu 〜 (diikuti kalimat negatif)', 'not very (with negative)'],
                    ['そして', 'そして', 'soshite', 'kemudian, dan (menyambung kalimat)', 'and, then'],
                    ['〜が、〜', 'が', '~ ga, ~', '〜, tetapi 〜', '~, but ~'],
                    ['お元気ですか。', 'おげんきですか', 'ogenki desu ka', 'Apa kabar?', 'How are you?'],
                    ['もう一杯いかがですか。', 'もういっぱいいかがですか', 'mou ippai ikaga desu ka', 'Mau tambah [〜] secangkir lagi?', 'Would you like another cup?'],
                    ['けっこうです。', 'けっこうです', 'kekkou desu', '[Tidak,] terima kasih, saya sudah cukup.', 'No thank you, I\'m fine.'],
                    ['もう〜です[ね]。', 'もう〜です[ね]', 'mou ~ desu [ne]', 'Sudah 〜 [ya].', 'It\'s already ~ [isn\'t it].'],
                    ['そろそろ失礼します。', 'そろそろしつれいします', 'sorosoro shitsurei shimasu', 'Maaf, saya mau pamit dulu.', 'I should be going soon.'],
                    ['またいらっしゃってください。', 'またいらっしゃってください', 'mata irasshatte kudasai', 'Silakan datang lagi.', 'Please come again.'],
                ],
            ],

            'pelajaran-8-nama' => [
                'name_id' => 'Nama (Pelajaran 8)',
                'name_en' => 'Names (Lesson 8)',
                'words' => [
                    ['シャンハイ', 'シャンハイ', 'shanhai', 'Shanghai (上海)', 'Shanghai'],
                    ['金閣寺', 'きんかくじ', 'kinkakuji', 'kuil Kinkakuji', 'Kinkakuji temple'],
                    ['奈良公園', 'ならこうえん', 'nara kouen', 'taman Nara', 'Nara Park'],
                    ['富士山', 'ふじさん', 'fujisan', 'gunung Fuji (gunung tertinggi di Jepang)', 'Mt. Fuji'],
                    ['七人の侍', 'しちにんのさむらい', 'shichinin no samurai', 'Tujuh Orang Samurai (film lama karya Akira Kurosawa)', 'Seven Samurai (film)'],
                ],
            ],

            'pelajaran-9' => [
                'name_id' => 'Kosakata Pelajaran 9 (pelengkap)',
                'name_en' => 'Lesson 9 Vocabulary (additions)',
                'words' => [
                    ['アルバイト', 'アルバイト', 'arubaito', 'kerja paruh waktu (〜をします: bekerja paruh waktu)', 'part-time job'],
                    ['ご主人', 'ごしゅじん', 'goshujin', 'suami (untuk menyebut suami orang lain)', 'husband (someone else\'s)'],
                    ['夫', 'おっと', 'otto', 'suami', 'husband'],
                    ['主人', 'しゅじん', 'shujin', 'suami (bentuk lain)', 'husband (alt.)'],
                    ['奥さん', 'おくさん', 'okusan', 'istri (untuk menyebut istri orang lain)', 'wife (someone else\'s)'],
                    ['妻', 'つま', 'tsuma', 'istri', 'wife'],
                    ['家内', 'かない', 'kanai', 'istri (bentuk lain)', 'wife (alt.)'],
                    ['子ども', 'こども', 'kodomo', 'anak', 'child'],
                    ['だいたい', 'だいたい', 'daitai', 'kira-kira', 'roughly'],
                    ['たくさん', 'たくさん', 'takusan', 'banyak', 'a lot'],
                    ['少し', 'すこし', 'sukoshi', 'sedikit', 'a little'],
                    ['全然', 'ぜんぜん', 'zenzen', 'sama sekali (diikuti kalimat negatif)', 'not at all (with negative)'],
                    ['早く', 'はやく', 'hayaku', 'dengan cepat', 'quickly, early'],
                    ['〜から（sebab）', 'から', '~kara', 'karena 〜, sebab 〜', 'because ~'],
                    ['どうして', 'どうして', 'doushite', 'kenapa', 'why'],
                    ['貸してください。', 'かしてください', 'kashite kudasai', 'Tolong pinjamkan.', 'Please lend me.'],
                    ['いいですよ。', 'いいですよ', 'ii desu yo', 'Boleh.', 'Sure, that\'s fine.'],
                    ['残念です[が]', 'ざんねんです[が]', 'zannen desu [ga]', 'sayang sekali', 'that\'s a pity'],
                    ['ああ', 'ああ', 'aa', 'ah, oh', 'ah, oh'],
                    ['いっしょにいかがですか。', 'いっしょにいかがですか', 'issho ni ikaga desu ka', 'Bagaimana kalau bersama-sama?', 'How about together?'],
                    ['〜はちょっと……。', '〜はちょっと', '~ wa chotto...', 'Maaf ya, saya tidak bisa [〜]. (menolak ajakan secara tidak langsung)', 'Sorry, that\'s a bit difficult (polite refusal)'],
                    ['だめですか。', 'だめですか', 'dame desu ka', 'Tidak bisa ya? / Tidak boleh ya?', 'Is that no good?'],
                    ['また今度お願いします。', 'またこんどおねがいします', 'mata kondo onegai shimasu', 'Maaf, lain kali saja. (menolak secara halus)', 'Maybe another time.'],
                ],
            ],

            'pelajaran-10' => [
                'name_id' => 'Kosakata Pelajaran 10 (pelengkap)',
                'name_en' => 'Lesson 10 Vocabulary (additions)',
                'words' => [
                    ['あります（物）', 'あります', 'arimasu', 'ada (untuk benda mati)', 'exist (inanimate)'],
                    ['上', 'うえ', 'ue', 'atas', 'above, top'],
                    ['下', 'した', 'shita', 'bawah', 'below, under'],
                    ['前', 'まえ', 'mae', 'depan, muka', 'front'],
                    ['後ろ', 'うしろ', 'ushiro', 'belakang', 'behind'],
                    ['右', 'みぎ', 'migi', 'kanan', 'right'],
                    ['左', 'ひだり', 'hidari', 'kiri', 'left'],
                    ['中', 'なか', 'naka', 'dalam', 'inside'],
                    ['外', 'そと', 'soto', 'luar', 'outside'],
                    ['隣', 'となり', 'tonari', 'sebelah', 'next to'],
                    ['近く', 'ちかく', 'chikaku', 'dekat', 'near'],
                    ['間', 'あいだ', 'aida', 'antara', 'between'],
                    ['〜や〜[など]', 'や', '~ya~ [nado]', '〜 dan 〜 [dan lain-lain]', '~ and ~ [etc.]'],
                    ['どうもすみません。', 'どうもすみません', 'doumo sumimasen', 'Terima kasih [banyak].', 'Thank you [very much].'],
                    ['ナンプラー', 'ナンプラー', 'nanpuraa', 'kecap ikan', 'fish sauce (nam pla)'],
                    ['コーナー', 'コーナー', 'koonaa', 'tempat/bagian penjualan', 'corner, section'],
                    ['いちばん下', 'いちばんした', 'ichiban shita', 'paling bawah', 'the very bottom'],
                ],
            ],

            'pelajaran-10-nama' => [
                'name_id' => 'Nama (Pelajaran 10)',
                'name_en' => 'Names (Lesson 10)',
                'words' => [
                    ['東京ディズニーランド', 'とうきょうディズニーランド', 'toukyou dizuniirando', 'Tokyo Disneyland', 'Tokyo Disneyland'],
                    ['アジアストア', 'アジアストア', 'ajia sutoa', 'Asia Store (pasar swalayan fiksi)', 'Asia Store (fictional supermarket)'],
                ],
            ],

            'pelajaran-11' => [
                'name_id' => 'Kosakata Pelajaran 11 (pelengkap)',
                'name_en' => 'Lesson 11 Vocabulary (additions)',
                'words' => [
                    ['います（子どもが〜）', 'います', 'imasu', 'ada, mempunyai [anak]', 'have [a child]'],
                    ['います（日本に〜）', 'います', 'imasu', 'ada [di Jepang]', 'be [in Japan]'],
                    ['休みます（会社を〜）', 'やすみます', 'yasumimasu', 'tidak masuk [kerja]', 'be absent [from work]'],
                    ['〜時間', 'じかん', '-jikan', '〜 jam (lama waktu)', '~ hours'],
                    ['かしこまりました。', 'かしこまりました', 'kashikomarimashita', 'Baik.', 'Certainly.'],
                    ['いい[お]天気ですね。', 'いい[お]てんきですね', 'ii [o]tenki desu ne', 'Cuacanya bagus ya.', 'Nice weather, isn\'t it.'],
                    ['お出かけですか。', 'おでかけですか', 'odekake desu ka', 'Mau keluar?', 'Are you going out?'],
                    ['ちょっと〜まで。', 'ちょっと〜まで', 'chotto ~ made', 'Ke 〜 sebentar.', 'Just going as far as ~.'],
                    ['行ってらっしゃい。', 'いってらっしゃい', 'itterasshai', 'Hati-hati. (kepada yang berangkat)', 'Have a good day.'],
                    ['行ってきます。', 'いってきます', 'ittekimasu', 'Saya berangkat.', 'I\'m off.'],
                    ['船便', 'ふなびん', 'funabin', 'pos laut', 'surface mail (sea)'],
                    ['航空便', 'こうくうびん', 'koukuubin', 'pos udara (エアメール)', 'airmail'],
                    ['お願いします。', 'おねがいします', 'onegai shimasu', 'Tolong.', 'Please.'],
                ],
            ],

            'pelajaran-11-nama' => [
                'name_id' => 'Nama (Pelajaran 11)',
                'name_en' => 'Names (Lesson 11)',
                'words' => [
                    ['オーストラリア', 'オーストラリア', 'oosutoraria', 'Australia', 'Australia'],
                ],
            ],

            'pelajaran-12' => [
                'name_id' => 'Kosakata Pelajaran 12 (pelengkap)',
                'name_en' => 'Lesson 12 Vocabulary (additions)',
                'words' => [
                    ['すき焼き', 'すきやき', 'sukiyaki', 'sukiyaki (masakan daging sapi dan sayur dalam panci)', 'sukiyaki'],
                    ['刺身', 'さしみ', 'sashimi', 'sashimi (ikan mentah yang dipotong tipis)', 'sashimi'],
                    ['[お]すし', 'おすし', 'osushi', 'sushi (nasi bercuka dengan ikan mentah di atasnya)', 'sushi'],
                    ['てんぷら', 'てんぷら', 'tenpura', 'tempura (gorengan seafood dan sayur bertepung)', 'tempura'],
                    ['豚肉', 'ぶたにく', 'butaniku', 'daging babi', 'pork'],
                    ['とり肉', 'とりにく', 'toriniku', 'daging ayam', 'chicken meat'],
                    ['牛肉', 'ぎゅうにく', 'gyuuniku', 'daging sapi', 'beef'],
                    ['レモン', 'レモン', 'remon', 'lemon', 'lemon'],
                    ['生け花', 'いけばな', 'ikebana', 'seni merangkai bunga (〜をします: merangkai bunga)', 'ikebana'],
                    ['紅葉', 'もみじ', 'momiji', 'maple, daun yang berubah warna menjadi merah', 'maple, autumn leaves'],
                    ['どちらも', 'どちらも', 'dochira mo', 'dua-duanya, yang mana juga', 'both, either'],
                    ['いちばん', 'いちばん', 'ichiban', 'paling', 'most, best'],
                    ['ずっと', 'ずっと', 'zutto', 'jauh lebih', 'much more'],
                    ['初めて', 'はじめて', 'hajimete', 'untuk pertama kali', 'for the first time'],
                    ['ただいま。', 'ただいま', 'tadaima', 'Saya kembali. / Saya pulang.', 'I\'m home.'],
                    ['お帰りなさい。', 'おかえりなさい', 'okaerinasai', 'Sudah pulang ya.', 'Welcome back.'],
                    ['わあ、すごい人ですね。', 'わあ、すごいひとですね', 'waa, sugoi hito desu ne', 'Wah, banyak orang.', 'Wow, so many people.'],
                    ['疲れました。', 'つかれました', 'tsukaremashita', 'Lelah.', 'I\'m tired.'],
                ],
            ],

            'pelajaran-12-nama' => [
                'name_id' => 'Nama (Pelajaran 12)',
                'name_en' => 'Names (Lesson 12)',
                'words' => [
                    ['祇園祭', 'ぎおんまつり', 'gion matsuri', 'Perayaan Gion (perayaan paling ternekal di Kyoto)', 'Gion Festival'],
                    ['ホンコン', 'ホンコン', 'honkon', 'Hong Kong (香港)', 'Hong Kong'],
                    ['シンガポール', 'シンガポール', 'shingapooru', 'Singapura', 'Singapore'],
                    ['ABCストア', 'ABCストア', 'ABC sutoa', 'ABC Store (pasar swalayan fiksi)', 'ABC Store (fictional)'],
                    ['ジャパン', 'ジャパン', 'japan', 'Japan (pasar swalayan fiksi)', 'Japan (fictional supermarket)'],
                ],
            ],

            'pelajaran-13' => [
                'name_id' => 'Kosakata Pelajaran 13 (pelengkap)',
                'name_en' => 'Lesson 13 Vocabulary (additions)',
                'words' => [
                    ['のどが渇きます', 'のどがかわきます', 'nodo ga kawakimasu', 'haus (bentuk lampau: のどが渇きました)', 'be thirsty'],
                    ['おなかがすきます', 'おなかがすきます', 'onaka ga sukimasu', 'lapar (bentuk lampau: おなかがすきました)', 'be hungry'],
                    ['そうしましょう。', 'そうしましょう', 'sou shimashou', 'Ya, mari. (menyetujui ajakan lawan bicara)', 'Yes, let\'s do that.'],
                    ['ご注文は？', 'ごちゅうもんは', 'gochuumon wa', 'Mau pesan apa?', 'What would you like to order?'],
                    ['定食', 'ていしょく', 'teishoku', 'menu paket', 'set meal'],
                    ['牛どん', 'ぎゅうどん', 'gyuudon', 'gyudon (nasi dengan daging sapi)', 'gyudon'],
                    ['[少々]お待ちください。', '[しょうしょう]おまちください', '[shoushou] omachi kudasai', 'Tunggu [sebentar].', 'Please wait [a moment].'],
                    ['〜でございます。', '〜でございます', '~ de gozaimasu', '(bentuk halus dari です)', '(polite form of desu)'],
                    ['別々に', 'べつべつに', 'betsubetsu ni', 'sendiri-sendiri, masing-masing', 'separately'],
                ],
            ],

            'pelajaran-13-nama' => [
                'name_id' => 'Nama (Pelajaran 13)',
                'name_en' => 'Names (Lesson 13)',
                'words' => [
                    ['アキックス', 'アキックス', 'akikkusu', 'Akikkusu (perusahaan fiksi)', 'Akikkusu (fictional company)'],
                    ['おはようテレビ', 'おはようテレビ', 'ohayou terebi', 'Ohayou Terebi (acara televisi fiksi)', 'Ohayou TV (fictional show)'],
                ],
            ],

            'pelajaran-14' => [
                'name_id' => 'Kosakata Pelajaran 14 (pelengkap)',
                'name_en' => 'Lesson 14 Vocabulary (additions)',
                'words' => [
                    ['教えます（住所を〜）', 'おしえます', 'oshiemasu', 'memberitahukan [alamat]', 'tell [an address]'],
                    ['問題', 'もんだい', 'mondai', 'masalah, soal', 'problem, question'],
                    ['答え', 'こたえ', 'kotae', 'jawaban', 'answer'],
                    ['読み方', 'よみかた', 'yomikata', 'cara membaca', 'how to read'],
                    ['〜方', 'かた', '~kata', 'cara 〜', 'way of ~ing'],
                    ['まっすぐ', 'まっすぐ', 'massugu', 'lurus', 'straight'],
                    ['ゆっくり', 'ゆっくり', 'yukkuri', 'pelan-pelan, istirahat dengan baik', 'slowly, leisurely'],
                    ['すぐ', 'すぐ', 'sugu', 'segera, langsung', 'immediately'],
                    ['また', 'また', 'mata', 'lagi', 'again'],
                    ['あとで', 'あとで', 'atode', 'nanti', 'later'],
                    ['もう少し', 'もうすこし', 'mou sukoshi', 'sedikit lagi', 'a little more'],
                    ['もう〜', 'もう〜', 'mou ~', '〜 lagi', '~ more'],
                    ['さあ', 'さあ', 'saa', 'mari (ajakan atau perintah kepada lawan bicara)', 'well then, come on'],
                    ['あれ？', 'あれ', 'are?', 'Ah? (heran atau merasa aneh)', 'Huh?'],
                    ['信号を右へ曲がってください。', 'しんごうをみぎへまがってください', 'shingou o migi e magatte kudasai', 'Tolong belok ke kanan di lampu lalu lintas.', 'Please turn right at the traffic light.'],
                    ['これでお願いします。', 'これでおねがいします', 'kore de onegai shimasu', 'Minta dengan ini.', 'With this, please.'],
                    ['お釣り', 'おつり', 'otsuri', 'uang kembalian', 'change (money)'],
                ],
            ],

            'pelajaran-14-nama' => [
                'name_id' => 'Nama (Pelajaran 14)',
                'name_en' => 'Names (Lesson 14)',
                'words' => [
                    ['みどり町', 'みどりちょう', 'midori chou', 'Midori-chō (kota fiksi)', 'Midori Town (fictional)'],
                ],
            ],

            'pelajaran-15' => [
                'name_id' => 'Kosakata Pelajaran 15 (pelengkap)',
                'name_en' => 'Lesson 15 Vocabulary (additions)',
                'words' => [
                    ['皆さん', 'みなさん', 'minasan', 'semuanya, bapak-bapak dan ibu-ibu sekalian', 'everyone'],
                    ['思い出します', 'おもいだします', 'omoidashimasu', 'teringat', 'remember'],
                    ['いらっしゃいます', 'いらっしゃいます', 'irasshaimasu', 'bentuk hormat dari います', '(honorific of imasu)'],
                ],
            ],

            'pelajaran-15-nama' => [
                'name_id' => 'Nama (Pelajaran 15)',
                'name_en' => 'Names (Lesson 15)',
                'words' => [
                    ['日本橋', 'にっぽんばし', 'nippombashi', 'Nipponbashi (daerah perbelanjaan di Osaka)', 'Nipponbashi (shopping district, Osaka)'],
                    ['みんなのインタビュー', 'みんなのインタビュー', 'minna no intabyuu', 'Minna no Intabyuu (acara televisi fiksi)', 'Minna no Interview (fictional show)'],
                ],
            ],

            // ── Pelajaran 16-20: dicocokkan dengan halaman "Kosa Kata" buku ──
            'pelajaran-16' => [
                'name_id' => 'Kosakata Pelajaran 16 (pelengkap)',
                'name_en' => 'Lesson 16 Vocabulary (additions)',
                'words' => [
                    ['入ります（大学に〜）', 'はいります', 'hairimasu', 'masuk [universitas]', 'enter [university]'],
                    ['出ます（大学を〜）', 'でます', 'demasu', 'tamat [dari universitas]', 'graduate [from university]'],
                    ['飲みます（酒）', 'のみます', 'nomimasu', 'minum (minuman keras)', 'drink (alcohol)'],
                    ['雪祭り', 'ゆきまつり', 'yuki matsuri', 'pesta salju', 'snow festival'],
                    ['すごいですね。', 'すごいですね', 'sugoi desu ne', 'Hebat.', 'That\'s amazing.'],
                    ['まだまだです。', 'まだまだです', 'mada mada desu', '[Tidak,] belum memuaskan.', '[No,] I still have a long way to go.'],
                    ['お引き出しですか。', 'おひきだしですか', 'ohikidashi desu ka', 'Mau menarik uang?', 'Would you like to withdraw money?'],
                    ['まず', 'まず', 'mazu', 'pertama-tama, terlebih dahulu', 'first of all'],
                    ['次に', 'つぎに', 'tsugi ni', 'kemudian', 'next'],
                    ['キャッシュカード', 'キャッシュカード', 'kyasshu kaado', 'kartu ATM', 'cash card'],
                    ['暗証番号', 'あんしょうばんごう', 'anshou bangou', 'PIN', 'PIN (personal identification number)'],
                    ['金額', 'きんがく', 'kingaku', 'jumlah uang', 'amount of money'],
                    ['確認', 'かくにん', 'kakunin', 'cek (〜します: mengecek, memastikan)', 'check, confirmation'],
                    ['ボタン', 'ボタン', 'botan', 'tombol', 'button'],
                ],
            ],

            'pelajaran-16-nama' => [
                'name_id' => 'Nama (Pelajaran 16)',
                'name_en' => 'Names (Lesson 16)',
                'words' => [
                    ['JR', 'JR', 'JR', 'JR (nama perusahaan kereta rel listrik)', 'JR (Japan Railways)'],
                    ['バンドン', 'バンドン', 'bandon', 'Bandung (di Indonesia)', 'Bandung (Indonesia)'],
                    ['フランケン', 'フランケン', 'furanken', 'Franken (di Jerman)', 'Franken (Germany)'],
                    ['ベラクルス', 'ベラクルス', 'berakurusu', 'Veracruz (di Meksiko)', 'Veracruz (Mexico)'],
                    ['梅田', 'うめだ', 'umeda', 'Umeda (nama daerah di Osaka)', 'Umeda (district in Osaka)'],
                    ['大学前', 'だいがくまえ', 'daigakumae', 'Daigakumae (halte fiksi)', 'Daigakumae (fictional stop)'],
                ],
            ],

            'pelajaran-17' => [
                'name_id' => 'Kosakata Pelajaran 17 (pelengkap)',
                'name_en' => 'Lesson 17 Vocabulary (additions)',
                'words' => [
                    ['飲みます（薬を〜）', 'のみます', 'nomimasu', 'minum [obat]', 'take [medicine]'],
                    ['入ります（おふろに〜）', 'はいります', 'hairimasu', 'mandi, masuk [ofuro]', 'take a bath'],
                    ['健康保険証', 'けんこうほけんしょう', 'kenkou hokenshou', 'kartu asuransi kesehatan (bentuk lengkap)', 'health insurance card (full form)'],
                    ['二、三日', 'に、さんにち', 'ni, san nichi', 'dua atau tiga hari', 'two or three days'],
                    ['二、三〜', 'に、さん〜', 'ni, san ~', 'dua atau tiga ~ (kata bilangan di bagian ~)', 'two or three ~ (counter in place of ~)'],
                    ['〜までに', 'までに', 'made ni', 'sampai dengan (menyatakan batas waktu)', 'by (deadline)'],
                    ['どうしましたか。', 'どうしましたか', 'dou shimashita ka', 'Kenapa? / Ada masalah apa?', 'What\'s the matter?'],
                    ['のど', 'のど', 'nodo', 'kerongkongan', 'throat'],
                    ['〜が痛いです。', 'がいたいです', 'ga itai desu', '[〜] sakit', '[~] hurts'],
                    ['かぜ', 'かぜ', 'kaze', 'masuk angin', 'a cold'],
                    ['お大事に。', 'おだいじに', 'odaiji ni', 'Semoga lekas sembuh.', 'Please take care (of yourself).'],
                ],
            ],

            'pelajaran-18' => [
                'name_id' => 'Kosakata Pelajaran 18 (pelengkap)',
                'name_en' => 'Lesson 18 Vocabulary (additions)',
                'words' => [
                    ['特に', 'とくに', 'toku ni', 'terutama, khususnya', 'especially'],
                    ['へえ', 'へえ', 'hee', 'Betul ya?/Masa! (terkagum atau terkejut)', 'Really?! (surprise, admiration)'],
                    ['それはおもしろいですね。', 'それはおもしろいですね', 'sore wa omoshiroi desu ne', 'Itu menarik ya.', 'That sounds interesting.'],
                    ['なかなか', 'なかなか', 'nakanaka', 'jarang, tidak mudah (diikuti bentuk negatif)', 'not easily (with negative)'],
                    ['ほんとうですか。', 'ほんとうですか', 'hontou desu ka', 'Betul?/Benar?', 'Really?'],
                    ['ぜひ', 'ぜひ', 'zehi', 'benar-benar, mesti', 'by all means'],
                ],
            ],

            'pelajaran-18-nama' => [
                'name_id' => 'Nama (Pelajaran 18)',
                'name_en' => 'Names (Lesson 18)',
                'words' => [
                    ['故郷', 'ふるさと', 'furusato', 'Furusato (judul lagu; artinya kampung halaman)', 'Furusato (song title; "hometown")'],
                    ['ビートルズ', 'ビートルズ', 'biitoruzu', 'The Beatles', 'The Beatles'],
                    ['秋葉原', 'あきはばら', 'akihabara', 'Akihabara (nama daerah di Tokyo)', 'Akihabara (district in Tokyo)'],
                ],
            ],

            'pelajaran-19' => [
                'name_id' => 'Kosakata Pelajaran 19 (pelengkap)',
                'name_en' => 'Lesson 19 Vocabulary (additions)',
                'words' => [
                    ['お茶（茶道）', 'おちゃ', 'ocha', 'upacara minum teh', 'tea ceremony'],
                    ['上ります', 'のぼります', 'noborimasu', 'naik, mendaki (tulisan lain dari 登ります)', 'climb (alternate spelling of 登ります)'],
                    ['乾杯', 'かんぱい', 'kanpai', 'bersulang (toast)', 'cheers (toast)'],
                    ['ダイエット', 'ダイエット', 'daietto', 'diet (〜を します: berdiet)', 'diet'],
                    ['無理[な]', 'むり', 'muri', 'paksa, berlebihan', 'unreasonable, too much'],
                    ['体にいい', 'からだにいい', 'karada ni ii', 'baik untuk tubuh', 'good for the body'],
                ],
            ],

            'pelajaran-19-nama' => [
                'name_id' => 'Nama (Pelajaran 19)',
                'name_en' => 'Names (Lesson 19)',
                'words' => [
                    ['東京スカイツリー', 'とうきょうスカイツリー', 'toukyou sukaitsurii', 'Tokyo Sky Tree (menara gelombang radio di Tokyo)', 'Tokyo Sky Tree'],
                    ['葛飾北斎', 'かつしかほくさい', 'katsushika hokusai', 'Katsushika Hokusai (pelukis Ukiyo-e zaman Edo, 1760–1849)', 'Katsushika Hokusai (Edo-period ukiyo-e artist)'],
                ],
            ],

            'pelajaran-20' => [
                'name_id' => 'Kosakata Pelajaran 20 (pelengkap)',
                'name_en' => 'Lesson 20 Vocabulary (additions)',
                'words' => [
                    ['みんなで', 'みんなで', 'minna de', 'kita semua, sama-sama', 'all together'],
                    ['〜けど', 'けど', 'kedo', '〜 tetapi (ungkapan informal dari が)', '~ but (informal form of が)'],
                    ['おなかがいっぱいです。', 'おなかがいっぱいです', 'onaka ga ippai desu', 'sudah kenyang', 'I\'m full'],
                    ['よかったら', 'よかったら', 'yokattara', 'kalau mau, kalau suka', 'if you like'],
                ],
            ],

            // ── Pelajaran 21-25: dicocokkan dengan halaman "Kosa Kata" buku ──
            'pelajaran-21' => [
                'name_id' => 'Kosakata Pelajaran 21 (pelengkap)',
                'name_en' => 'Lesson 21 Vocabulary (additions)',
                'words' => [
                    ['あります(お祭りが～)', 'あります', 'arimasu (omatsuri ga ~)', 'ada, diadakan (pesta perayaan)', 'be held (a festival)'],
                    ['意見', 'いけん', 'iken', 'pendapat', 'opinion'],
                    ['話', 'はなし', 'hanashi', 'cerita (〜を します: bercerita, berbicara)', 'story, talk'],
                    ['地球', 'ちきゅう', 'chikyuu', 'bumi', 'the Earth'],
                    ['月', 'つき', 'tsuki', 'bulan', 'the moon'],
                    ['最近', 'さいきん', 'saikin', 'akhir-akhir ini', 'recently'],
                    ['たぶん', 'たぶん', 'tabun', 'mungkin, barangkali', 'probably'],
                    ['きっと', 'きっと', 'kitto', 'pasti', 'surely'],
                    ['ほんとうに', 'ほんとうに', 'hontou ni', 'betul-betul', 'really'],
                    ['そんなに', 'そんなに', 'sonna ni', 'tidak begitu (diikuti bentuk negatif)', 'not that much (with negative)'],
                    ['〜について', 'について', 'ni tsuite', 'tentang ~, mengenai ~', 'about ~'],
                    ['カンガルー', 'カンガルー', 'kangaruu', 'kanguru', 'kangaroo'],
                    ['久しぶりですね。', 'ひさしぶりですね', 'hisashiburi desu ne', 'Sudah lama tidak bertemu ya.', 'Long time no see.'],
                    ['〜でも 飲みませんか。', 'でものみませんか', '~ demo nomimasen ka', 'Bagaimana kalau kita minum ~, atau apa saja?', 'How about a drink of ~ or something?'],
                    ['もちろん', 'もちろん', 'mochiron', 'tentu saja', 'of course'],
                    ['もう 帰らないと……。', 'もうかえらないと', 'mou kaeranai to', 'Saya harus pulang…', 'I have to go home now…'],
                ],
            ],

            'pelajaran-21-nama' => [
                'name_id' => 'Nama & Rujukan Pelajaran 21',
                'name_en' => 'Lesson 21 Names & References',
                'words' => [
                    ['アインシュタイン', 'アインシュタイン', 'ainshutain', 'Albert Einstein (1879-1955)', 'Albert Einstein (1879-1955)'],
                    ['ガガーリン', 'ガガーリン', 'gagaarin', 'Gagarin (1934-1968)', 'Gagarin (1934-1968)'],
                    ['ガリレオ', 'ガリレオ', 'garireo', 'Galileo Galilei (1564-1642)', 'Galileo Galilei (1564-1642)'],
                    ['キング牧師', 'キングぼくし', 'kingu bokushi', 'Martin Luther King, Jr. (1929-1968)', 'Martin Luther King, Jr. (1929-1968)'],
                    ['フランクリン', 'フランクリン', 'furankurin', 'Benjamin Franklin (1706-1790)', 'Benjamin Franklin (1706-1790)'],
                    ['かぐや姫', 'かぐやひめ', 'kaguyahime', 'Putri Kaguya (tokoh dongeng "Taketori Monogatari")', 'Princess Kaguya (from "Taketori Monogatari")'],
                    ['天神祭', 'てんじんまつり', 'tenjinmatsuri', 'Perayaan Tenjin (perayaan di Osaka)', 'Tenjin Festival (Osaka)'],
                    ['吉野山', 'よしのやま', 'yoshinoyama', 'Gunung Yoshino (gunung di Nara)', 'Mount Yoshino (in Nara)'],
                    ['キャプテン・クック', 'キャプテン・クック', 'kyaputen kukku', 'Captain James Cook (1728-1779)', 'Captain James Cook (1728-1779)'],
                    ['ヨーネン', 'ヨーネン', 'yoonen', 'perusahaan fiksi', 'fictional company'],
                ],
            ],

            'pelajaran-22' => [
                'name_id' => 'Kosakata Pelajaran 22 (pelengkap)',
                'name_en' => 'Lesson 22 Vocabulary (additions)',
                'words' => [
                    ['かけます（眼鏡を〜）', 'かけます', 'kakemasu', 'memakai [kaca mata]', 'wear [glasses]'],
                    ['します(ネクタイを～)', 'します', 'shimasu (nekutai o ~)', 'memakai (dasi)', 'wear (a tie)'],
                    ['えーと', 'えーと', 'eeto', 'Itu… (jeda sambil berpikir)', 'um…'],
                    ['おめでとう[ございます]。', 'おめでとうございます', 'omedetou [gozaimasu]', 'Selamat (untuk ulang tahun, pernikahan, tahun baru)', 'Congratulations'],
                    ['お探しですか。', 'おさがしですか', 'osagashi desu ka', 'Mencari apa?', 'Are you looking for something?'],
                    ['では', 'では', 'dewa', 'kalau begitu', 'well then'],
                    ['こちら（これ）', 'こちら', 'kochira (kore)', 'ini (ungkapan sopan dari これ)', 'this (polite form of kore)'],
                    ['家賃', 'やちん', 'yachin', 'biaya sewa rumah', 'rent'],
                    ['ダイニングキッチン', 'ダイニングキッチン', 'dainingu kicchin', 'ruang makan dengan dapur', 'dining kitchen'],
                    ['和室', 'わしつ', 'washitsu', 'kamar ala Jepang', 'Japanese-style room'],
                    ['押し入れ', 'おしいれ', 'oshiire', 'lemari dinding ala Jepang', 'Japanese-style closet'],
                    ['布団', 'ふとん', 'futon', 'selimut dan kasur berisi kapas ala Jepang', 'futon (Japanese bedding)'],
                ],
            ],

            'pelajaran-22-nama' => [
                'name_id' => 'Nama & Rujukan Pelajaran 22',
                'name_en' => 'Lesson 22 Names & References',
                'words' => [
                    ['パリ', 'パリ', 'pari', 'Paris', 'Paris'],
                    ['万里の長城', 'ばんりのちょうじょう', 'banri no choujou', 'Tembok Besar Cina', 'The Great Wall of China'],
                    ['みんなの アンケート', 'みんなのアンケート', 'minna no ankeeto', 'angket fiksi', 'fictional questionnaire'],
                ],
            ],

            'pelajaran-23-nama' => [
                'name_id' => 'Nama & Rujukan Pelajaran 23',
                'name_en' => 'Lesson 23 Names & References',
                'words' => [
                    ['聖徳太子', 'しょうとくたいし', 'shoutoku taishi', 'Pangeran Shotoku (574-622)', 'Prince Shotoku (574-622)'],
                    ['法隆寺', 'ほうりゅうじ', 'houryuuji', 'Wihara Horyuji (dibangun Pangeran Shotoku di Nara pada awal abad ke-7)', 'Horyuji Temple (built by Prince Shotoku in Nara, early 7th century)'],
                    ['元気茶', 'げんきちゃ', 'genkicha', 'teh fiksi', 'fictional tea'],
                    ['本田駅', 'ほんだえき', 'honda eki', 'stasiun fiksi', 'fictional station'],
                    ['図書館前', 'としょかんまえ', 'toshokanmae', 'halte bus fiksi', 'fictional bus stop'],
                ],
            ],

            'pelajaran-24' => [
                'name_id' => 'Kosakata Pelajaran 24 (pelengkap)',
                'name_en' => 'Lesson 24 Vocabulary (additions)',
                'words' => [
                    ['送ります（人を〜）', 'おくります', 'okurimasu', 'mengantar [orang]', 'escort [a person]'],
                    ['おじいちゃん', 'おじいちゃん', 'ojiichan', 'kakek (akrab)', 'grandpa'],
                    ['おばあちゃん', 'おばあちゃん', 'obaachan', 'nenek (akrab)', 'grandma'],
                    ['ほかに', 'ほかに', 'hoka ni', 'selain, yang lain', 'besides, other than that'],
                    ['母の日', 'ははのひ', 'haha no hi', 'hari Ibu', 'Mother\'s Day'],
                ],
            ],

            'pelajaran-25' => [
                'name_id' => 'Kosakata Pelajaran 25 (pelengkap)',
                'name_en' => 'Lesson 25 Vocabulary (additions)',
                'words' => [
                    ['年を取ります', 'としをとります', 'toshi o torimasu', 'berumur, lanjut usia', 'grow old'],
                    ['もしもし', 'もしもし', 'moshimoshi', 'halo (ketika menelepon)', 'hello (on the phone)'],
                    ['転勤', 'てんきん', 'tenkin', 'pindah ke kantor cabang lain atau jabatan lain (〜します: pindah kantor)', 'job transfer'],
                    ['こと（〜のこと）', 'こと', 'koto (~ no koto)', 'hal (〜の こと: hal ~)', 'matter, thing (~ no koto)'],
                    ['暇（名詞）', 'ひま', 'hima', 'waktu luang', 'free time'],
                    ['[いろいろ] お世話に なりました。', 'いろいろおせわになりました', '[iroiro] osewa ni narimashita', 'Terima kasih banyak atas bantuan Anda selama ini.', 'Thank you for all your help.'],
                    ['頑張ります', 'がんばります', 'ganbarimasu', 'berusaha, bekerja keras', 'do one\'s best'],
                    ['どうぞ お元気で。', 'どうぞおげんきで', 'douzo ogenki de', 'Semoga sehat-sehat selalu. (perpisahan jangka lama)', 'Please take care of yourself. (farewell)'],
                ],
            ],

            'pelajaran-25-nama' => [
                'name_id' => 'Nama & Rujukan Pelajaran 25',
                'name_en' => 'Lesson 25 Names & References',
                'words' => [
                    ['ベトナム', 'ベトナム', 'betonamu', 'Vietnam', 'Vietnam'],
                ],
            ],
        ];
    }
}
