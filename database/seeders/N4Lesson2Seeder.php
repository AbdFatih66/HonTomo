<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\Level;
use App\Models\Unit;
use App\Models\Vocabulary;
use App\Models\VocabularyCategory;
use App\Services\VocabularyQuizSync;
use Illuminate\Database\Seeder;

class N4Lesson2Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 2 ("Bisa Membuat Apa Saja, Ya"). Level N4 sudah
     * dibuat oleh N4Lesson1Seeder; seeder ini menambah unit order 2.
     *
     * Isi:
     *   - Unit order 2 ("Pelajaran 2") di level N4
     *   - Lesson Bunpou (order 0, category grammar) dengan 6 kartu:
     *       1. Kata kerja potensial (bentuk)     4. できます (terbentuk／selesai)
     *       2. Kalimat potensial (を → が)        5. 〜しか ＋ negatif
     *       3. 見えます／聞こえます               6. は (perbandingan／setelah partikel)
     *   - Kategori kosakata "n4-pelajaran-2" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus. Penjelasan, contoh
     * kalimat dan dialog ditulis baru untuk aplikasi ini. Nama diri
     * (ドラえもん) sengaja tidak dimasukkan, sama seperti daftar nama diri di N5.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson2Seeder
     *
     * Kata kunci pencarian SQL memakai ASCII (kategori.slug + romaji), bukan
     * huruf Jepang (lihat docs/AGENTS.md aturan 4).
     */
    public function run(): void
    {
        // Level N4 dibuat oleh N4Lesson1Seeder; jalankan dulu bila belum ada.
        $level = Level::where('code', 'N4')->first();

        if (! $level) {
            $this->call(N4Lesson1Seeder::class);
            $level = Level::where('code', 'N4')->firstOrFail();
        }

        $unit = Unit::updateOrCreate(
            ['level_id' => $level->id, 'order' => 2],
            [
                'title_id' => 'Pelajaran 2: Bisa Membuat Apa Saja, Ya',
                'title_en' => 'Lesson 2: What Can You Make?',
                'description_id' => 'Kata kerja potensial, 見えます／聞こえます, できます, 〜しか, dan は untuk perbandingan.',
                'description_en' => 'Potential verbs, 見えます／聞こえます, できます, 〜しか, and は for contrast.',
                'icon' => 'mdi-book-open-page-variant',
                'is_active' => true,
            ]
        );

        $bunpou = Lesson::updateOrCreate(
            ['unit_id' => $unit->id, 'order' => 0],
            [
                'title_id' => 'Tata Bahasa (Bunpou)',
                'title_en' => 'Grammar (Bunpou)',
                'category' => 'grammar',
                'xp_reward' => 10,
                'required_accuracy' => 0,
                'prerequisite_lesson_id' => null,
                'is_active' => true,
            ]
        );

        Lesson::updateOrCreate(
            ['unit_id' => $unit->id, 'order' => 1],
            [
                'title_id' => 'Kosakata',
                'title_en' => 'Vocabulary',
                'category' => 'vocabulary',
                'xp_reward' => 10,
                'required_accuracy' => 70,
                'prerequisite_lesson_id' => $bunpou->id,
                'is_active' => true,
            ]
        );

        $this->seedCards($bunpou);
        $this->seedVocabulary();

        // Kuis kosakata: satu soal pilihan ganda per kata (idempotent).
        app(VocabularyQuizSync::class)->run('N4');
    }

    // ------------------------------------------------------------------
    // Bunpou cards
    // ------------------------------------------------------------------

    private function seedCards(Lesson $bunpou): void
    {
        foreach (array_values($this->cards()) as $position => $card) {
            LessonQuestion::updateOrCreate(
                [
                    'lesson_id' => $bunpou->id,
                    'question_type' => 'grammar',
                    'order' => $position + 1,
                ],
                [
                    'grammar_id' => null,
                    'prompt_id' => $card['title_id'],
                    'prompt_en' => $card['title_en'],
                    'japanese_text' => $card['pattern'],
                    'payload' => $card['payload'],
                    'difficulty' => 2,
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function cards(): array
    {
        return [
            // 1 ------------------------------------------------------------
            [
                'title_id' => 'Kata kerja potensial (bentuk)',
                'title_en' => 'Potential verbs (forms)',
                'pattern' => '可能形《かのうけい》: 書《か》ける ／ 食《た》べられる ／ できる',
                'payload' => [
                    'explanation_id' => 'Kata kerja potensial menyatakan "bisa" melakukan sesuatu. Di N5 kamu memakai 〜ことが できます; sekarang ada cara yang lebih singkat: mengubah kata kerjanya sendiri. Kelompok I: huruf akhir kamus berubah ke baris え (書《か》く → 書《か》けます, 買《か》う → 買《か》えます). Kelompok II: 〜ます diganti 〜られます (食《た》べます → 食《た》べられます). Kelompok III: 来《き》ます → 来《こ》られます, します → できます. Bentuk potensial selalu berperilaku seperti kata kerja kelompok II, jadi bentuk biasanya berakhir 〜る (書《か》ける, 食《た》べられる) dan negatifnya 〜ない (書《か》けない).',
                    'explanation_en' => 'A potential verb says that someone "can" do something. In N5 you used 〜ことが できます; now there is a shorter way: change the verb itself. Group I: the last kana of the dictionary form moves to the え row (書《か》く → 書《か》けます, 買《か》う → 買《か》えます). Group II: 〜ます becomes 〜られます (食《た》べます → 食《た》べられます). Group III: 来《き》ます → 来《こ》られます, します → できます. A potential verb always behaves like a group II verb, so its plain form ends in 〜る (書《か》ける, 食《た》べられる) and its negative in 〜ない (書《か》けない).',
                    'notes_id' => [
                        'Kata kerja yang sudah bermakna "paham／ada／bisa" tidak dibuat potensial: わかります, あります, できます tetap.',
                        'Contoh lengkap satu kata kerja: 買《か》います → 買《か》えます → 買《か》える → 買《か》えない → 買《か》えて.',
                        'Di percakapan sehari-hari 見《み》られる, 食《た》べられる sering dipendekkan jadi 見《み》れる, 食《た》べれる, tetapi ini belum dianggap baku — di ujian pakai bentuk lengkapnya.',
                    ],
                    'notes_en' => [
                        'Verbs that already mean "understand／exist／can" are not made potential: わかります, あります, できます stay as they are.',
                        'One verb in full: 買《か》います → 買《か》えます → 買《か》える → 買《か》えない → 買《か》えて.',
                        'In casual speech 見《み》られる, 食《た》べられる are often shortened to 見《み》れる, 食《た》べれる, but this is not yet standard — use the full form in exams.',
                    ],
                    'examples' => [
                        ['ja' => '漢字《かんじ》を 百《ひゃく》ぐらい 書《か》けます。', 'reading' => 'Kanji o hyaku gurai kakemasu.', 'id' => 'Saya bisa menulis sekitar seratus kanji.', 'en' => 'I can write about a hundred kanji.'],
                        ['ja' => '朝《あさ》 六時《ろくじ》に 起《お》きられますか。', 'reading' => 'Asa rokuji ni okiraremasu ka.', 'id' => 'Bisakah kamu bangun jam enam pagi?', 'en' => 'Can you get up at six in the morning?'],
                        ['ja' => 'あしたの パーティーに 来《こ》られません。', 'reading' => 'Ashita no paatii ni koraremasen.', 'id' => 'Saya tidak bisa datang ke pesta besok.', 'en' => 'I cannot come to the party tomorrow.'],
                        ['ja' => 'この 店《みせ》で クレジットカードが 使《つか》えますか。', 'reading' => 'Kono mise de kurejitto kaado ga tsukaemasu ka.', 'id' => 'Apakah kartu kredit bisa dipakai di toko ini?', 'en' => 'Can I use a credit card at this shop?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Bisa atau belum?',
                        'title_en' => 'Can you or not yet?',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'ワヒュさん、日本語《にほんご》の 新聞《しんぶん》が 読《よ》めますか。', 'reading' => 'Wahyu-san, nihongo no shinbun ga yomemasu ka.', 'id' => 'Wahyu, apakah kamu bisa membaca koran bahasa Jepang?', 'en' => 'Wahyu, can you read a Japanese newspaper?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、まだ 読《よ》めません。でも、ひらがなと カタカナは 書《か》けます。', 'reading' => 'Iie, mada yomemasen. Demo, hiragana to katakana wa kakemasu.', 'id' => 'Belum bisa. Tetapi hiragana dan katakana bisa saya tulis.', 'en' => 'Not yet. But I can write hiragana and katakana.'],
                            ['speaker' => 'たなか', 'ja' => 'すごいですね。漢字《かんじ》も 少《すこ》しずつ 覚《おぼ》えられますよ。', 'reading' => 'Sugoi desu ne. Kanji mo sukoshi zutsu oboeraremasu yo.', 'id' => 'Hebat. Kanji pun bisa kamu hafalkan sedikit demi sedikit.', 'en' => 'That is great. You can learn kanji little by little too.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい、がんばります。', 'reading' => 'Hai, ganbarimasu.', 'id' => 'Ya, saya akan berusaha.', 'en' => 'Yes, I will do my best.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => 'Kalimat potensial (を → が)',
                'title_en' => 'Potential sentences (を → が)',
                'pattern' => 'Ｎが ＋ 可能形《かのうけい》',
                'payload' => [
                    'explanation_id' => 'Kata kerja biasa menyatakan gerakan; kata kerja potensial menyatakan keadaan. Karena itu objek yang tadinya ditandai を berganti menjadi が: 日本語《にほんご》を 話《はな》します → 日本語《にほんご》が 話《はな》せます. Partikel lain (に, へ, と, で, から) tidak berubah: 先生《せんせい》に 会《あ》えます. Kalimat potensial punya dua arti: (1) kemampuan orangnya ("dia pandai／mampu") dan (2) kemungkinan karena keadaan atau tempat ("di sini boleh／bisa dilakukan").',
                    'explanation_en' => 'An ordinary verb expresses an action; a potential verb expresses a state. That is why the object that used to take を now takes が: 日本語《にほんご》を 話《はな》します → 日本語《にほんご》が 話《はな》せます. Other particles (に, へ, と, で, から) do not change: 先生《せんせい》に 会《あ》えます. A potential sentence has two meanings: (1) the person\'s ability ("he／she is able to") and (2) a possibility created by the situation or place ("it can be done here").',
                    'notes_id' => [
                        'Kemampuan: 姉《あね》は ギターが ひけます. Kemungkinan: この 図書館《としょかん》では 本《ほん》が 借《か》りられます.',
                        'Dalam percakapan, を kadang tetap dipakai (日本語《にほんご》を 話《はな》せます), tetapi が lebih baku dan lebih aman untuk ujian.',
                        'Bentuk lampau dan negatif mengikuti kelompok II: 読《よ》めました, 読《よ》めませんでした.',
                    ],
                    'notes_en' => [
                        'Ability: 姉《あね》は ギターが ひけます. Possibility: この 図書館《としょかん》では 本《ほん》が 借《か》りられます.',
                        'In conversation を is sometimes kept (日本語《にほんご》を 話《はな》せます), but が is more standard and safer for exams.',
                        'Past and negative follow group II: 読《よ》めました, 読《よ》めませんでした.',
                    ],
                    'examples' => [
                        ['ja' => 'ワヒュさんは 日本語《にほんご》が 話《はな》せます。', 'reading' => 'Wahyu-san wa nihongo ga hanasemasu.', 'id' => 'Wahyu bisa berbahasa Jepang.', 'en' => 'Wahyu can speak Japanese.'],
                        ['ja' => '姉《あね》は ギターが ひけます。', 'reading' => 'Ane wa gitaa ga hikemasu.', 'id' => 'Kakak perempuan saya bisa bermain gitar.', 'en' => 'My older sister can play the guitar.'],
                        ['ja' => 'あしたの 午後《ごご》、先生《せんせい》に 会《あ》えますか。', 'reading' => 'Ashita no gogo, sensei ni aemasu ka.', 'id' => 'Besok sore, apakah saya bisa bertemu dengan guru?', 'en' => 'Can I see the teacher tomorrow afternoon?'],
                        ['ja' => 'この 図書館《としょかん》では 本《ほん》が 十冊《じゅっさつ》 借《か》りられます。', 'reading' => 'Kono toshokan de wa hon ga jussatsu kariraremasu.', 'id' => 'Di perpustakaan ini kita bisa meminjam sepuluh buku.', 'en' => 'At this library you can borrow ten books.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di perpustakaan',
                        'title_en' => 'At the library',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません。この 図書館《としょかん》で 本《ほん》が 借《か》りられますか。', 'reading' => 'Sumimasen. Kono toshokan de hon ga kariraremasu ka.', 'id' => 'Permisi. Apakah di perpustakaan ini saya bisa meminjam buku?', 'en' => 'Excuse me. Can I borrow books at this library?'],
                            ['speaker' => '係の人', 'ja' => 'はい、借《か》りられますよ。一人《ひとり》 五冊《ごさつ》までです。', 'reading' => 'Hai, kariraremasu yo. Hitori gosatsu made desu.', 'id' => 'Ya, bisa. Sampai lima buku per orang.', 'en' => 'Yes, you can. Up to five books per person.'],
                            ['speaker' => 'ワヒュ', 'ja' => '何日《なんにち》 借《か》りられますか。', 'reading' => 'Nannichi kariraremasu ka.', 'id' => 'Bisa dipinjam berapa hari?', 'en' => 'For how many days can I borrow them?'],
                            ['speaker' => '係の人', 'ja' => '二週間《にしゅうかん》です。', 'reading' => 'Nishuukan desu.', 'id' => 'Dua minggu.', 'en' => 'Two weeks.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => '見えます／聞こえます (terlihat／terdengar)',
                'title_en' => '見えます／聞こえます (can be seen／can be heard)',
                'pattern' => 'Ｎが 見《み》えます／聞《き》こえます',
                'payload' => [
                    'explanation_id' => '見《み》えます dan 聞《き》こえます berarti sesuatu "masuk" ke mata atau telinga dengan sendirinya, tanpa kita sengaja melihat atau mendengarkan. Objeknya ditandai が. Kalau kita sengaja ingin melihat atau mendengar — misalnya menonton film atau mendengarkan radio — pakai kata kerja potensial biasa: 見《み》られます, 聞《き》けます. Jadi: pemandangan dari jendela = 見《み》えます; film yang bisa ditonton di bioskop = 見《み》られます.',
                    'explanation_en' => '見《み》えます and 聞《き》こえます mean that something comes into your eyes or ears by itself, without you deliberately looking or listening. The object takes が. When you deliberately want to see or hear something — for instance watching a film or listening to the radio — use the ordinary potential verb: 見《み》られます, 聞《き》けます. So: a view from a window = 見《み》えます; a film you are able to watch at a cinema = 見《み》られます.',
                    'notes_id' => [
                        '見《み》えます／聞《き》こえます bukan bentuk potensial dari 見《み》ます／聞《き》きます; keduanya kata kerja tersendiri.',
                        'Tidak bisa dipakai untuk perintah atau kemauan sendiri: ×よく 見《み》えて ください (salah).',
                    ],
                    'notes_en' => [
                        '見《み》えます／聞《き》こえます are not the potential forms of 見《み》ます／聞《き》きます; they are verbs in their own right.',
                        'They cannot be used for commands or your own intention: ×よく 見《み》えて ください (wrong).',
                    ],
                    'examples' => [
                        ['ja' => 'この 部屋《へや》の 窓《まど》から 公園《こうえん》が 見《み》えます。', 'reading' => 'Kono heya no mado kara kouen ga miemasu.', 'id' => 'Dari jendela kamar ini terlihat taman.', 'en' => 'You can see the park from the window of this room.'],
                        ['ja' => '夜《よる》は 星《ほし》が よく 見《み》えます。', 'reading' => 'Yoru wa hoshi ga yoku miemasu.', 'id' => 'Pada malam hari bintang terlihat jelas.', 'en' => 'At night the stars are clearly visible.'],
                        ['ja' => '隣《となり》の 部屋《へや》から ピアノの 音《おと》が 聞《き》こえます。', 'reading' => 'Tonari no heya kara piano no oto ga kikoemasu.', 'id' => 'Dari kamar sebelah terdengar suara piano.', 'en' => 'The sound of a piano can be heard from the next room.'],
                        ['ja' => 'この 映画《えいが》は インターネットで 見《み》られます。', 'reading' => 'Kono eiga wa intaanetto de miraremasu.', 'id' => 'Film ini bisa ditonton lewat internet.', 'en' => 'You can watch this film on the internet.'],
                        ['ja' => 'ラジオで 英語《えいご》の ニュースが 聞《き》けます。', 'reading' => 'Rajio de eigo no nyuusu ga kikemasu.', 'id' => 'Lewat radio kita bisa mendengarkan berita berbahasa Inggris.', 'en' => 'You can listen to English news on the radio.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Apartemen baru',
                        'title_en' => 'A new apartment',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'いい 部屋《へや》ですね。', 'reading' => 'Ii heya desu ne.', 'id' => 'Kamarnya bagus, ya.', 'en' => 'This is a nice room.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ありがとう。窓《まど》から 海《うみ》が 見《み》えるんです。', 'reading' => 'Arigatou. Mado kara umi ga mieru n desu.', 'id' => 'Terima kasih. Dari jendela terlihat laut.', 'en' => 'Thank you. You can see the sea from the window.'],
                            ['speaker' => 'サリ', 'ja' => 'へえ、すてきですね。', 'reading' => 'Hee, suteki desu ne.', 'id' => 'Wah, indah sekali.', 'en' => 'Wow, how lovely.'],
                            ['speaker' => 'ワヒュ', 'ja' => '夜《よる》は 波《なみ》の 音《おと》も 聞《き》こえますよ。', 'reading' => 'Yoru wa nami no oto mo kikoemasu yo.', 'id' => 'Pada malam hari suara ombak juga terdengar.', 'en' => 'At night you can hear the waves too.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'できます (terbentuk／selesai)',
                'title_en' => 'できます (to come into being／to be finished)',
                'pattern' => 'Ｎが できます',
                'payload' => [
                    'explanation_id' => 'Selain berarti "bisa" (Ｖる ことが できます di N5), できます juga berarti sesuatu "terbentuk, berdiri, jadi" atau "selesai, siap". Subjeknya ditandai が. Dipakai untuk bangunan atau toko yang baru berdiri (新《あたら》しい 店《みせ》が できました) dan untuk pekerjaan atau masakan yang selesai (晩《ばん》ごはんが できました). Pertanyaan tentang waktu selesai: いつ できますか。',
                    'explanation_en' => 'Besides meaning "can" (Ｖる ことが できます in N5), できます also means that something "comes into being, gets built, is made" or "is finished, is ready". The subject takes が. It is used for a building or shop that has newly opened (新《あたら》しい 店《みせ》が できました) and for work or food that is done (晩《ばん》ごはんが できました). To ask when something will be ready: いつ できますか。',
                    'notes_id' => [
                        'Perhatikan konteks: 日本語《にほんご》が できます = bisa bahasa Jepang; 宿題《しゅくだい》が できました = PR sudah selesai.',
                        'Untuk bangunan yang dibangun orang, bisa juga pakai 建《た》てます ("membangun") — 建《た》てます hanya dipakai bila ada pelaku yang jelas.',
                    ],
                    'notes_en' => [
                        'Watch the context: 日本語《にほんご》が できます = can speak Japanese; 宿題《しゅくだい》が できました = the homework is done.',
                        'For buildings made by people you can also use 建《た》てます ("to build") — 建《た》てます is used only when there is a clear builder.',
                    ],
                    'examples' => [
                        ['ja' => '駅《えき》の 前《まえ》に 新《あたら》しい 本屋《ほんや》が できました。', 'reading' => 'Eki no mae ni atarashii honya ga dekimashita.', 'id' => 'Di depan stasiun telah berdiri toko buku baru.', 'en' => 'A new bookshop has opened in front of the station.'],
                        ['ja' => '晩《ばん》ごはんは もうすぐ できます。', 'reading' => 'Bangohan wa mousugu dekimasu.', 'id' => 'Makan malam sebentar lagi siap.', 'en' => 'Dinner will be ready soon.'],
                        ['ja' => '靴《くつ》の 修理《しゅうり》は いつ できますか。……あさっての 午後《ごご》に できます。', 'reading' => 'Kutsu no shuuri wa itsu dekimasu ka. ...Asatte no gogo ni dekimasu.', 'id' => 'Kapan perbaikan sepatu selesai? ……Selesai lusa sore.', 'en' => 'When will the shoe repair be done? ...It will be done the day after tomorrow in the afternoon.'],
                        ['ja' => '宿題《しゅくだい》が できました。', 'reading' => 'Shukudai ga dekimashita.', 'id' => 'PR saya sudah selesai.', 'en' => 'My homework is finished.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di tempat perbaikan sepatu',
                        'title_en' => 'At the shoe repair shop',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません。この 靴《くつ》の 修理《しゅうり》を お願《ねが》いします。', 'reading' => 'Sumimasen. Kono kutsu no shuuri o onegai shimasu.', 'id' => 'Permisi. Tolong perbaiki sepatu ini.', 'en' => 'Excuse me. Please repair these shoes.'],
                            ['speaker' => '店員', 'ja' => 'はい。あしたの 夕方《ゆうがた》に できますよ。', 'reading' => 'Hai. Ashita no yuugata ni dekimasu yo.', 'id' => 'Baik. Besok sore sudah selesai.', 'en' => 'Certainly. It will be ready tomorrow evening.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'じゃ、あしたの 夕方《ゆうがた》に 取《と》りに 来《き》ます。', 'reading' => 'Ja, ashita no yuugata ni tori ni kimasu.', 'id' => 'Kalau begitu, besok sore saya datang mengambilnya.', 'en' => 'Then I will come and pick them up tomorrow evening.'],
                            ['speaker' => '店員', 'ja' => 'お待《ま》ちして います。', 'reading' => 'Omachi shite imasu.', 'id' => 'Kami tunggu.', 'en' => 'We will be waiting.'],
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => '〜しか ＋ negatif (hanya)',
                'title_en' => '〜しか ＋ negative (only)',
                'pattern' => 'Ｎ／数量《すうりょう》＋ しか ＋ Vない',
                'payload' => [
                    'explanation_id' => 'しか dipasang setelah kata benda atau jumlah dan selalu bersama bentuk negatif. Artinya "hanya ~, tidak ada yang lain" dan membawa nuansa "cuma segitu, kurang／tidak cukup". Bandingkan だけ: だけ + kalimat positif terdengar netral ("hanya ~"), sedangkan しか + negatif terdengar sedikit menyesal. Partikel が dan を hilang di depan しか; partikel lain tetap dan しか ditaruh setelahnya (ここでしか, 友達《ともだち》にしか).',
                    'explanation_en' => 'しか goes after a noun or an amount and is always used with a negative. It means "only ~, nothing else" and carries the feeling of "just that much, too little". Compare だけ: だけ + a positive sentence sounds neutral ("only ~"), while しか + a negative sounds slightly regretful. が and を disappear in front of しか; other particles stay and しか follows them (ここでしか, 友達《ともだち》にしか).',
                    'notes_id' => [
                        '冷蔵庫《れいぞうこ》に 水《みず》だけ あります = ada air saja (netral). 冷蔵庫《れいぞうこ》に 水《みず》しか ありません = hanya ada air (tidak ada yang lain).',
                        'しか tidak bisa dipakai dengan kalimat positif: ×水《みず》しか あります (salah).',
                    ],
                    'notes_en' => [
                        '冷蔵庫《れいぞうこ》に 水《みず》だけ あります = there is just water (neutral). 冷蔵庫《れいぞうこ》に 水《みず》しか ありません = there is only water (nothing else).',
                        'しか cannot be used with a positive sentence: ×水《みず》しか あります (wrong).',
                    ],
                    'examples' => [
                        ['ja' => '冷蔵庫《れいぞうこ》に 水《みず》しか ありません。', 'reading' => 'Reizouko ni mizu shika arimasen.', 'id' => 'Di kulkas hanya ada air.', 'en' => 'There is only water in the fridge.'],
                        ['ja' => 'ワヒュさんは ひらがなしか 読《よ》めません。', 'reading' => 'Wahyu-san wa hiragana shika yomemasen.', 'id' => 'Wahyu hanya bisa membaca hiragana.', 'en' => 'Wahyu can only read hiragana.'],
                        ['ja' => '朝《あさ》は パンしか 食《た》べません。', 'reading' => 'Asa wa pan shika tabemasen.', 'id' => 'Pagi hari saya hanya makan roti.', 'en' => 'In the morning I only eat bread.'],
                        ['ja' => 'この 店《みせ》では 現金《げんきん》でしか 払《はら》えません。', 'reading' => 'Kono mise de wa genkin de shika haraemasen.', 'id' => 'Di toko ini hanya bisa membayar dengan uang tunai.', 'en' => 'At this shop you can only pay in cash.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Ajakan makan siang',
                        'title_en' => 'A lunch invitation',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'ワヒュさん、お昼《ひる》ごはん、行《い》きませんか。', 'reading' => 'Wahyu-san, ohirugohan, ikimasen ka.', 'id' => 'Wahyu, mau pergi makan siang?', 'en' => 'Wahyu, shall we go for lunch?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません。きょうは 財布《さいふ》に 三百円《さんびゃくえん》しか ないんです。', 'reading' => 'Sumimasen. Kyou wa saifu ni sanbyakuen shika nai n desu.', 'id' => 'Maaf. Hari ini di dompet saya hanya ada tiga ratus yen.', 'en' => 'Sorry. Today I only have three hundred yen in my wallet.'],
                            ['speaker' => 'サリ', 'ja' => 'じゃ、コンビニで おにぎりを 買《か》いませんか。', 'reading' => 'Ja, konbini de onigiri o kaimasen ka.', 'id' => 'Kalau begitu, bagaimana kalau beli onigiri di minimarket?', 'en' => 'Then how about buying onigiri at a convenience store?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいですね。そう しましょう。', 'reading' => 'Ii desu ne. Sou shimashou.', 'id' => 'Boleh. Ayo begitu.', 'en' => 'Sounds good. Let us do that.'],
                        ],
                    ],
                ],
            ],

            // 6 ------------------------------------------------------------
            [
                'title_id' => 'は (perbandingan／setelah partikel)',
                'title_en' => 'は (contrast／after a particle)',
                'pattern' => 'Ａは 〜が、Ｂは 〜 ／ Ｎ＋ では・には・からは',
                'payload' => [
                    'explanation_id' => 'Selain menandai topik, は dipakai untuk membandingkan dua hal: "A iya, tetapi B tidak". Biasanya dua klausa dihubungkan dengan が dan masing-masing punya は. Untuk kata benda yang memakai が atau を, partikelnya digantikan oleh は (カレーが 好きです → カレーは 好きです). Untuk partikel lain (で, に, から, と), は ditambahkan setelah partikel itu: 日本《にほん》では, 駅《えき》には, 屋上《おくじょう》からは. Dengan begitu bagian itu menjadi topik atau pembanding.',
                    'explanation_en' => 'Besides marking the topic, は is used to contrast two things: "A yes, but B no". Usually two clauses are joined with が and each has its own は. For a noun that takes が or を, は replaces that particle (カレーが 好きです → カレーは 好きです). For other particles (で, に, から, と), は is added after the particle: 日本《にほん》では, 駅《えき》には, 屋上《おくじょう》からは. This makes that part the topic or the point of contrast.',
                    'notes_id' => [
                        'Bila は dipakai tanpa pembanding yang disebut, lawan bicara tetap menangkap nuansa "kalau yang lain, entahlah": 漢字《かんじ》は 書《か》けます (kanji sih bisa).',
                        'Bentuk に＋は dan で＋は sering terdengar seperti "kalau di tempat/waktu itu".',
                    ],
                    'notes_en' => [
                        'Even when no comparison is stated, the listener still hears "as for the others, who knows": 漢字《かんじ》は 書《か》けます (kanji, I can write).',
                        'に＋は and で＋は often sound like "as for that place／time".',
                    ],
                    'examples' => [
                        ['ja' => '料理《りょうり》は 作《つく》れますが、お菓子《かし》は 作《つく》れません。', 'reading' => 'Ryouri wa tsukuremasu ga, okashi wa tsukuremasen.', 'id' => 'Masakan bisa saya buat, tetapi kue tidak.', 'en' => 'I can cook meals, but I cannot make sweets.'],
                        ['ja' => '家《いえ》からは 海《うみ》が 見《み》えませんが、屋上《おくじょう》からは 見《み》えます。', 'reading' => 'Ie kara wa umi ga miemasen ga, okujou kara wa miemasu.', 'id' => 'Dari rumah laut tidak terlihat, tetapi dari atap terlihat.', 'en' => 'The sea is not visible from the house, but it is visible from the roof.'],
                        ['ja' => 'この 部屋《へや》では 食《た》べられませんが、あの 部屋《へや》では 食《た》べられます。', 'reading' => 'Kono heya de wa taberaremasen ga, ano heya de wa taberaremasu.', 'id' => 'Di kamar ini tidak boleh makan, tetapi di kamar itu boleh.', 'en' => 'You cannot eat in this room, but you can in that room.'],
                        ['ja' => '東京《とうきょう》には 友達《ともだち》が いますが、大阪《おおさか》には いません。', 'reading' => 'Toukyou ni wa tomodachi ga imasu ga, Oosaka ni wa imasen.', 'id' => 'Di Tokyo saya punya teman, tetapi di Osaka tidak.', 'en' => 'I have friends in Tokyo, but not in Osaka.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Kembang api di taman',
                        'title_en' => 'Fireworks in the park',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'この 公園《こうえん》で 花火《はなび》が できますか。', 'reading' => 'Kono kouen de hanabi ga dekimasu ka.', 'id' => 'Apakah di taman ini boleh main kembang api?', 'en' => 'Can we play with fireworks in this park?'],
                            ['speaker' => 'たなか', 'ja' => 'ここでは できませんが、川《かわ》の 近《ちか》くでは できますよ。', 'reading' => 'Koko de wa dekimasen ga, kawa no chikaku de wa dekimasu yo.', 'id' => 'Di sini tidak boleh, tetapi di dekat sungai boleh.', 'en' => 'Not here, but you can near the river.'],
                            ['speaker' => 'ワヒュ', 'ja' => '川《かわ》の 近《ちか》くですね。ありがとうございます。', 'reading' => 'Kawa no chikaku desu ne. Arigatou gozaimasu.', 'id' => 'Di dekat sungai, ya. Terima kasih.', 'en' => 'Near the river, I see. Thank you.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Vocabulary
    // ------------------------------------------------------------------

    private function seedVocabulary(): void
    {
        $category = VocabularyCategory::updateOrCreate(
            ['slug' => 'n4-pelajaran-2'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 2',
                'name_en' => 'N4 Lesson 2 Vocabulary',
            ]
        );

        foreach ($this->words() as [$japanese, $hiragana, $romaji, $meaningId, $meaningEn]) {
            // Kunci ASCII (category + romaji): collation unicode_ci menyamakan か = が = カ.
            Vocabulary::updateOrCreate(
                ['category_id' => $category->id, 'romaji' => $romaji],
                [
                    'japanese' => $japanese,
                    'hiragana' => $hiragana,
                    'meaning_id' => $meaningId,
                    'meaning_en' => $meaningEn,
                    'jlpt_level' => 'N4',
                    'difficulty' => 1,
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * [japanese, hiragana, romaji, meaning_id, meaning_en]
     * Romaji harus unik di dalam pelajaran ini (dipakai sebagai kunci).
     *
     * @return array<int, array<int, string>>
     */
    private function words(): array
    {
        return [
            // Kosakata utama
            ['飼います', 'かいます', 'kaimasu', 'memelihara [hewan]', 'to keep [a pet]'],
            ['走ります', 'はしります', 'hashirimasu', 'berlari [di jalan]', 'to run [along a road]'],
            ['見えます', 'みえます', 'miemasu', 'terlihat [gunung]', 'to be visible [a mountain]'],
            ['聞こえます', 'きこえます', 'kikoemasu', 'terdengar [bunyi]', 'to be audible [a sound]'],
            ['できます', 'できます', 'dekimasu', 'terbentuk, selesai [jalan dibuat]', 'to be built, to be completed [a road]'],
            ['開きます', 'ひらきます', 'hirakimasu', 'membuka, mengadakan [kelas]', 'to open, to hold [a class]'],
            ['心配[な]', 'しんぱい[な]', 'shinpai', 'khawatir', 'worried'],
            ['ペット', 'ペット', 'petto', 'hewan peliharaan', 'pet'],
            ['鳥', 'とり', 'tori', 'burung', 'bird'],
            ['声', 'こえ', 'koe', 'suara', 'voice'],
            ['波', 'なみ', 'nami', 'ombak, gelombang', 'wave'],
            ['花火', 'はなび', 'hanabi', 'kembang api', 'fireworks'],
            ['道具', 'どうぐ', 'dougu', 'alat', 'tool'],
            ['クリーニング', 'クリーニング', 'kuriiningu', 'binatu (laundry)', 'laundry, dry cleaning'],
            ['家', 'いえ', 'ie', 'rumah', 'house'],
            ['マンション', 'マンション', 'manshon', 'apartemen', 'apartment'],
            ['キッチン', 'キッチン', 'kicchin', 'dapur', 'kitchen'],
            ['〜教室', '〜きょうしつ', 'kyoushitsu', 'les ~, kelas ~', '~ class, ~ lessons'],
            ['パーティールーム', 'パーティールーム', 'paatii ruumu', 'ruang pesta', 'party room'],
            ['方', 'かた', 'kata', 'orang (kata hormat dari 人)', 'person (honorific of 人)'],
            ['〜後', '〜ご', 'go', 'setelah ~, sesudah ~', 'after ~'],
            ['〜しか', '〜しか', 'shika', 'hanya ~ (dipakai dengan kata bentuk negatif)', 'only ~ (used with a negative)'],
            ['ほかの', 'ほかの', 'hoka no', 'lain', 'other'],
            ['はっきり', 'はっきり', 'hakkiri', 'jelas', 'clearly'],

            // 会話 (percakapan)
            ['家具', 'かぐ', 'kagu', 'perabot rumah, mebel', 'furniture'],
            ['本棚', 'ほんだな', 'hondana', 'lemari buku', 'bookshelf'],
            ['いつか', 'いつか', 'itsuka', 'kapan-kapan, suatu hari', 'someday'],
            ['建てます', 'たてます', 'tatemasu', 'membangun', 'to build'],
            ['すばらしい', 'すばらしい', 'subarashii', 'bagus, luar biasa', 'wonderful'],

            // 読み物 (bacaan)
            ['子どもたち', 'こどもたち', 'kodomotachi', 'anak-anak', 'children'],
            ['大好き[な]', 'だいすき[な]', 'daisuki', 'sangat suka, kesukaan', 'very fond of'],
            ['主人公', 'しゅじんこう', 'shujinkou', 'pelaku utama, tokoh utama', 'main character'],
            ['形', 'かたち', 'katachi', 'sosok, bentuk', 'shape, form'],
            ['不思議[な]', 'ふしぎ[な]', 'fushigi', 'ajaib', 'mysterious, strange'],
            ['ポケット', 'ポケット', 'poketto', 'kantong', 'pocket'],
            ['例えば', 'たとえば', 'tatoeba', 'misalnya', 'for example'],
            ['付けます', 'つけます', 'tsukemasu', 'memasang, menempelkan', 'to attach, to put on'],
            ['自由に', 'じゆうに', 'jiyuu ni', 'dengan bebas', 'freely'],
            ['空', 'そら', 'sora', 'langit', 'sky'],
            ['飛びます', 'とびます', 'tobimasu', 'terbang', 'to fly'],
            ['昔', 'むかし', 'mukashi', 'dulu, zaman dahulu', 'long ago'],
            ['自分', 'じぶん', 'jibun', 'diri sendiri', 'oneself'],
            ['将来', 'しょうらい', 'shourai', 'masa depan', 'the future'],
        ];
    }
}
