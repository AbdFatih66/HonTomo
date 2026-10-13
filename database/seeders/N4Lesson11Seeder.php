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

class N4Lesson11Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 11 ("Berusaha Hidup Sehat"). Berdiri di level N4
     * bersama Pelajaran 1 dan 3, dan dipilih lewat selektor N5 / N4 di Tata
     * Bahasa, Kosakata dan Referensi Tata Bahasa.
     *
     * Isi:
     *   - Unit order 11 di level N4 ("Pelajaran 11")
     *   - Lesson Bunpou (order 0, category grammar) dengan 5 kartu:
     *       1. Vる／Vない ように、〜 (tujuan)
     *       2. Vる ように なります (perubahan)
     *       3. Vる／Vない ように して います／します (usaha)
     *       4. Vる／Vない ように して ください (permintaan tidak langsung)
     *       5. Kata sifat → kata keterangan (早く, 上手に)
     *   - Kategori kosakata "n4-pelajaran-11" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus. Penjelasan, contoh
     * kalimat dan dialog ditulis baru untuk aplikasi ini. Nama diri tidak
     * dimasukkan ke daftar kosakata.
     *
     * Referensi Tata Bahasa (ringkasan pola + info kesehatan) tidak memakai
     * database; datanya ada di resources/js/pages/lampiran/index.vue.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson11Seeder
     *
     * Kata kunci pencarian SQL memakai ASCII (kategori.slug + romaji), bukan
     * huruf Jepang (lihat docs/AGENTS.md aturan 4).
     */
    public function run(): void
    {
        // Level N4 dibuat oleh N4Lesson1Seeder; jalankan dulu bila belum ada.
        if (! Level::where('code', 'N4')->exists()) {
            $this->call(N4Lesson1Seeder::class);
        }

        $level = Level::where('code', 'N4')->firstOrFail();

        $unit = Unit::updateOrCreate(
            ['level_id' => $level->id, 'order' => 11],
            [
                'title_id' => 'Pelajaran 11: Berusaha Hidup Sehat',
                'title_en' => 'Lesson 11: Making an Effort to Stay Healthy',
                'description_id' => '〜ように (tujuan), 〜ようになります, 〜ようにしています／してください, dan kata sifat sebagai kata keterangan.',
                'description_en' => '〜ように (purpose), 〜ようになります, 〜ようにしています／してください, and adjectives as adverbs.',
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
                'title_id' => 'Vる／Vない ように、〜 (agar／supaya)',
                'title_en' => 'Vる／Vない ように、〜 (so that)',
                'pattern' => 'V₁(辞書形／ない形)ように、V₂',
                'payload' => [
                    'explanation_id' => 'ように menunjukkan keadaan yang ingin dicapai, sedangkan V₂ adalah tindakan yang dilakukan demi keadaan itu: "agar ~, (saya) …". Sebelum ように dipakai kata kerja yang tidak bisa diatur oleh kehendak pembicara — kata kerja potensial (わかる, 見《み》える, 聞《き》こえる, 話《はな》せる, なる, dan sejenisnya) dalam bentuk kamus — atau kata kerja bentuk ない.',
                    'explanation_en' => 'ように shows the state you want to reach, and V₂ is the action taken for the sake of it: "so that ~, (I) …". Before ように comes a verb that is not under the speaker\'s will — a potential verb (わかる, 見《み》える, 聞《き》こえる, 話《はな》せる, なる, and the like) in dictionary form — or a verb in ない form.',
                    'notes_id' => [
                        'Contoh yang cocok di depan ように: 話《はな》せる, 読《よ》める, わかる, 聞《き》こえる, 忘《わす》れない, 遅《おく》れない.',
                        'Bagian V₂ adalah tindakan yang disengaja (練習《れんしゅう》して います, メモします, 大《おお》きい 声《こえ》で 話《はな》します).',
                    ],
                    'notes_en' => [
                        'Typical verbs before ように: 話《はな》せる, 読《よ》める, わかる, 聞《き》こえる, 忘《わす》れない, 遅《おく》れない.',
                        'The V₂ part is a deliberate action (練習《れんしゅう》して います, メモします, 大《おお》きい 声《こえ》で 話《はな》します).',
                    ],
                    'examples' => [
                        ['ja' => '日本語《にほんご》が 上手《じょうず》に 話《はな》せるように、毎日《まいにち》 ドラマを 見《み》て います。', 'reading' => 'Nihongo ga jouzu ni hanaseru you ni, mainichi dorama o mite imasu.', 'id' => 'Agar bisa berbicara bahasa Jepang dengan baik, setiap hari saya menonton drama.', 'en' => 'So that I can speak Japanese well, I watch dramas every day.'],
                        ['ja' => '忘《わす》れないように、メモします。', 'reading' => 'Wasurenai you ni, memo shimasu.', 'id' => 'Supaya tidak lupa, saya mencatat.', 'en' => 'So that I do not forget, I take notes.'],
                        ['ja' => '後《うし》ろの 人《ひと》にも 聞《き》こえるように、大《おお》きい 声《こえ》で 話《はな》して ください。', 'reading' => 'Ushiro no hito ni mo kikoeru you ni, ookii koe de hanashite kudasai.', 'id' => 'Tolong bicara dengan suara keras agar orang di belakang juga bisa mendengar.', 'en' => 'Please speak loudly so that the people at the back can hear too.'],
                        ['ja' => 'ラッシュに あわないように、早《はや》く うちを 出《で》ます。', 'reading' => 'Rasshu ni awanai you ni, hayaku uchi o demasu.', 'id' => 'Supaya tidak terjebak keramaian jam sibuk, saya berangkat dari rumah lebih awal.', 'en' => 'To avoid the rush hour, I leave home early.'],
                        ['ja' => '風邪《かぜ》を ひかないように、あたたかい 服《ふく》を 着《き》ます。', 'reading' => 'Kaze o hikanai you ni, atatakai fuku o kimasu.', 'id' => 'Supaya tidak masuk angin, saya memakai pakaian hangat.', 'en' => 'So that I do not catch a cold, I wear warm clothes.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Kamus elektronik',
                        'title_en' => 'An electronic dictionary',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'ワヒュさん、それは 電子辞書《でんしじしょ》ですか。', 'reading' => 'Wahyu-san, sore wa denshi jisho desu ka.', 'id' => 'Wahyu, apakah itu kamus elektronik?', 'en' => 'Wahyu, is that an electronic dictionary?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい。わからない 言葉《ことば》が あったら、すぐ 調《しら》べられるように、いつも 持《も》って います。', 'reading' => 'Hai. Wakaranai kotoba ga attara, sugu shiraberareru you ni, itsumo motte imasu.', 'id' => 'Ya. Supaya kalau ada kata yang tidak saya mengerti bisa langsung dicari, saya selalu membawanya.', 'en' => 'Yes. I always carry it so that I can look up any word I do not know right away.'],
                            ['speaker' => 'たなか', 'ja' => 'いいですね。わたしも ほしいです。', 'reading' => 'Ii desu ne. Watashi mo hoshii desu.', 'id' => 'Bagus, ya. Saya juga mau.', 'en' => 'Nice. I want one too.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => 'Vる ように なります (jadi bisa／jadi terbiasa)',
                'title_en' => 'Vる ように なります (come to be able to／come to do)',
                'pattern' => 'V(辞書形)ように なります',
                'payload' => [
                    'explanation_id' => 'なります menyatakan perubahan keadaan. Dengan kata kerja potensial (わかる, 見《み》える, 話《はな》せる, dan sejenisnya), pola ini berarti keadaan "tidak bisa" berubah menjadi "bisa": "jadi bisa ~". Kalau dipakai dengan kata kerja selain potensial, artinya kebiasaan baru yang sebelumnya tidak ada: "jadi (mulai) ~". Kala lampau ようになりました dipakai untuk melaporkan perubahan yang sudah terjadi, sering bersama やっと, だんだん, atau このごろ.',
                    'explanation_en' => 'なります expresses a change of state. With a potential verb (わかる, 見《み》える, 話《はな》せる, and the like), this pattern means a change from "cannot" to "can": "come to be able to ~". With a verb other than a potential one, it means a new habit that did not exist before: "come to (start to) ~". The past ようになりました reports a change that has already happened, often with やっと, だんだん, or このごろ.',
                    'notes_id' => [
                        'Menjawab pertanyaan ようになりましたか dengan "belum": いいえ、まだ 〜ません (bukan ようになりません).',
                        'Kebiasaan baru: 去年《きょねん》から 毎朝《まいあさ》 散歩《さんぽ》するように なりました = sejak tahun lalu saya mulai berjalan-jalan setiap pagi.',
                    ],
                    'notes_en' => [
                        'To answer a ようになりましたか question with "not yet": いいえ、まだ 〜ません (not ようになりません).',
                        'A new habit: 去年《きょねん》から 毎朝《まいあさ》 散歩《さんぽ》するように なりました = since last year I have started taking a walk every morning.',
                    ],
                    'examples' => [
                        ['ja' => '日本《にほん》の 食《た》べ物《もの》が 食《た》べられるように なりました。', 'reading' => 'Nihon no tabemono ga taberareru you ni narimashita.', 'id' => 'Sekarang saya jadi bisa makan makanan Jepang.', 'en' => 'I have come to be able to eat Japanese food.'],
                        ['ja' => 'このごろ、日本語《にほんご》の ニュースが わかるように なりました。', 'reading' => 'Konogoro, nihongo no nyuusu ga wakaru you ni narimashita.', 'id' => 'Akhir-akhir ini saya jadi bisa mengerti berita berbahasa Jepang.', 'en' => 'Lately I have come to understand the news in Japanese.'],
                        ['ja' => 'やっと 一人《ひとり》で 服《ふく》を 着《き》られるように なりました。', 'reading' => 'Yatto hitori de fuku o kirareru you ni narimashita.', 'id' => 'Akhirnya (dia) bisa memakai baju sendiri.', 'en' => 'At last (he/she) can put on clothes alone.'],
                        ['ja' => 'このごろ、毎朝《まいあさ》 走《はし》るように なりました。', 'reading' => 'Konogoro, maiasa hashiru you ni narimashita.', 'id' => 'Akhir-akhir ini saya jadi berlari setiap pagi.', 'en' => 'Lately I have started running every morning.'],
                        ['ja' => '英語《えいご》が 話《はな》せるように なりましたか。……いいえ、まだ 話《はな》せません。', 'reading' => 'Eigo ga hanaseru you ni narimashita ka. ...Iie, mada hanasemasen.', 'id' => 'Apakah sudah bisa berbicara bahasa Inggris? ……Belum, belum bisa.', 'en' => 'Can you speak English now? ...No, not yet.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Sudah bisa bersepeda?',
                        'title_en' => 'Can you ride a bicycle yet?',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'ワヒュさん、自転車《じてんしゃ》に 乗《の》れるように なりましたか。', 'reading' => 'Wahyu-san, jitensha ni noreru you ni narimashita ka.', 'id' => 'Wahyu, apakah kamu sudah bisa naik sepeda?', 'en' => 'Wahyu, can you ride a bicycle now?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、やっと 乗《の》れるように なりました。', 'reading' => 'Ee, yatto noreru you ni narimashita.', 'id' => 'Ya, akhirnya saya sudah bisa.', 'en' => 'Yes, I can finally ride it.'],
                            ['speaker' => 'サリ', 'ja' => 'よかったですね。じゃ、今度《こんど》の 日曜日《にちようび》に いっしょに 行《い》きませんか。', 'reading' => 'Yokatta desu ne. Ja, kondo no nichiyoubi ni issho ni ikimasen ka.', 'id' => 'Syukurlah. Kalau begitu, hari Minggu nanti mau pergi bersama?', 'en' => 'That is great. Then shall we go together next Sunday?'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => 'Vる／Vない ように して います／します (berusaha)',
                'title_en' => 'Vる／Vない ように して います／します (making an effort)',
                'pattern' => 'V(辞書形／ない形)ように して います',
                'payload' => [
                    'explanation_id' => 'Dipakai untuk menyatakan bahwa kamu berusaha melakukan sesuatu (atau berusaha tidak melakukannya) secara terus-menerus sebagai kebiasaan: "(saya) berusaha agar …". Di sini kata kerja di depan ように adalah kata kerja yang bisa diatur kehendak sendiri, dalam bentuk kamus atau bentuk ない. Bentuk ようにします (tanpa ています) dipakai untuk niat atau tekad ke depan: これから 早《はや》く 寝《ね》るように します.',
                    'explanation_en' => 'Used to say that you are making an ongoing effort, as a habit, to do something (or not to do it): "(I) try to …". Here the verb before ように is one you control yourself, in dictionary or ない form. ようにします (without ています) expresses an intention or resolution for the future: これから 早《はや》く 寝《ね》るように します.',
                    'notes_id' => [
                        'Perbedaan dengan ように なります: ように して います = usaha yang sengaja dilakukan; ように なります = perubahan yang terjadi dengan sendirinya.',
                        'Bisa dipakai dengan ない: 甘《あま》い 物《もの》を 食《た》べないように して います (berusaha tidak makan yang manis).',
                    ],
                    'notes_en' => [
                        'Difference from ように なります: ように して います = a deliberate effort; ように なります = a change that happens by itself.',
                        'It works with ない: 甘《あま》い 物《もの》を 食《た》べないように して います (I try not to eat sweets).',
                    ],
                    'examples' => [
                        ['ja' => '毎日《まいにち》 野菜《やさい》を 食《た》べるように して います。', 'reading' => 'Mainichi yasai o taberu you ni shite imasu.', 'id' => 'Setiap hari saya berusaha makan sayur.', 'en' => 'I try to eat vegetables every day.'],
                        ['ja' => '夜《よる》 遅《おそ》く 食《た》べないように して います。', 'reading' => 'Yoru osoku tabenai you ni shite imasu.', 'id' => 'Saya berusaha tidak makan larut malam.', 'en' => 'I try not to eat late at night.'],
                        ['ja' => 'エレベーターを 使《つか》わないで、階段《かいだん》を 歩《ある》くように して います。', 'reading' => 'Erebeetaa o tsukawanaide, kaidan o aruku you ni shite imasu.', 'id' => 'Saya berusaha tidak naik lift dan berjalan lewat tangga.', 'en' => 'I try to walk up the stairs instead of using the elevator.'],
                        ['ja' => '毎月《まいつき》 給料《きゅうりょう》から 少《すこ》し 貯金《ちょきん》するように して います。', 'reading' => 'Maitsuki kyuuryou kara sukoshi chokin suru you ni shite imasu.', 'id' => 'Setiap bulan saya berusaha menabung sedikit dari gaji.', 'en' => 'Every month I try to save a little from my salary.'],
                        ['ja' => 'これから 早《はや》く 寝《ね》るように します。', 'reading' => 'Korekara hayaku neru you ni shimasu.', 'id' => 'Mulai sekarang saya akan berusaha tidur lebih awal.', 'en' => 'From now on I will try to go to bed early.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menjaga kesehatan',
                        'title_en' => 'Staying healthy',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'ワヒュさんは 元気《げんき》ですね。何《なに》か 特別《とくべつ》な ことを して いますか。', 'reading' => 'Wahyu-san wa genki desu ne. Nanika tokubetsu na koto o shite imasu ka.', 'id' => 'Wahyu, kamu sehat, ya. Apakah melakukan sesuatu yang khusus?', 'en' => 'Wahyu, you look well. Do you do anything special?'],
                            ['speaker' => 'ワヒュ', 'ja' => '毎日《まいにち》 少《すこ》し 歩《ある》くように して います。それから、何《なん》でも 食《た》べるように して います。', 'reading' => 'Mainichi sukoshi aruku you ni shite imasu. Sorekara, nan demo taberu you ni shite imasu.', 'id' => 'Setiap hari saya berusaha berjalan sedikit. Lalu, berusaha makan apa saja.', 'en' => 'I try to walk a little every day. And I try to eat anything.'],
                            ['speaker' => 'たなか', 'ja' => 'いいですね。わたしも まねします。', 'reading' => 'Ii desu ne. Watashi mo mane shimasu.', 'id' => 'Bagus, ya. Saya juga akan menirunya.', 'en' => 'Nice. I will copy you.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'Vる／Vない ように して ください (permintaan halus)',
                'title_en' => 'Vる／Vない ように して ください (a gentle request)',
                'pattern' => 'V(辞書形／ない形)ように して ください',
                'payload' => [
                    'explanation_id' => 'Dipakai untuk meminta lawan bicara berusaha melakukan sesuatu (atau berusaha tidak melakukannya): "Usahakan agar …". Dibandingkan dengan 〜て／〜ないで ください yang langsung, 〜ように して ください bersifat tidak langsung sehingga terdengar lebih halus. Cocok untuk nasihat dari dokter, guru, atau atasan.',
                    'explanation_en' => 'Used to ask the listener to make an effort to do something (or not do it): "Please try to …". Compared with the direct 〜て／〜ないで ください, 〜ように して ください is indirect and sounds gentler. Suitable for advice from a doctor, teacher, or superior.',
                    'notes_id' => [
                        'Tidak bisa dipakai untuk permintaan yang harus dilakukan saat itu juga: ×すみませんが、窓《まど》を 開《あ》けるように して ください → すみませんが、窓《まど》を 開《あ》けて ください。',
                        'Cocok untuk hal yang dilakukan berulang atau ke depan: 毎日《まいにち》, 絶対《ぜったい》に, できるだけ.',
                    ],
                    'notes_en' => [
                        'It cannot be used for a request that must be done right now: ×すみませんが、窓《まど》を 開《あ》けるように して ください → すみませんが、窓《まど》を 開《あ》けて ください。',
                        'It suits things done repeatedly or in the future: 毎日《まいにち》, 絶対《ぜったい》に, できるだけ.',
                    ],
                    'examples' => [
                        ['ja' => '毎日《まいにち》 薬《くすり》を 飲《の》むように して ください。', 'reading' => 'Mainichi kusuri o nomu you ni shite kudasai.', 'id' => 'Usahakan minum obat setiap hari.', 'en' => 'Please make sure to take your medicine every day.'],
                        ['ja' => '約束《やくそく》の 時間《じかん》に 遅《おく》れないように して ください。', 'reading' => 'Yakusoku no jikan ni okurenai you ni shite kudasai.', 'id' => 'Usahakan jangan terlambat pada waktu janji.', 'en' => 'Please try not to be late for the appointment.'],
                        ['ja' => '絶対《ぜったい》に 財布《さいふ》を なくさないように して ください。', 'reading' => 'Zettai ni saifu o nakusanai you ni shite kudasai.', 'id' => 'Jangan sampai kehilangan dompet.', 'en' => 'Please be sure not to lose your wallet.'],
                        ['ja' => 'できるだけ 重《おも》い 物《もの》は 持《も》たないように して ください。', 'reading' => 'Dekiru dake omoi mono wa motanai you ni shite kudasai.', 'id' => 'Sedapat mungkin usahakan tidak membawa barang berat.', 'en' => 'As far as possible, please avoid carrying heavy things.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Nasihat dokter',
                        'title_en' => "The doctor's advice",
                        'lines' => [
                            ['speaker' => '先生', 'ja' => 'お酒《さけ》は あまり 飲《の》まないように して ください。', 'reading' => 'Osake wa amari nomanai you ni shite kudasai.', 'id' => 'Usahakan jangan terlalu banyak minum minuman keras.', 'en' => 'Please try not to drink much alcohol.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい、わかりました。たばこは どうですか。', 'reading' => 'Hai, wakarimashita. Tabako wa dou desu ka.', 'id' => 'Baik, saya mengerti. Kalau rokok bagaimana?', 'en' => 'Yes, I understand. What about cigarettes?'],
                            ['speaker' => '先生', 'ja' => 'たばこも 吸《す》わないように して ください。', 'reading' => 'Tabako mo suwanai you ni shite kudasai.', 'id' => 'Rokok juga usahakan jangan merokok.', 'en' => 'Please try not to smoke either.'],
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => 'Kata sifat → kata keterangan (〜く／〜に)',
                'title_en' => 'Adjective → adverb (〜く／〜に)',
                'pattern' => 'い形 → 〜く　／　な形 → 〜に',
                'payload' => [
                    'explanation_id' => 'Kalau kata sifat menerangkan kata sifat atau kata kerja lain, bentuknya berubah: kata sifat い → 〜く (早《はや》い → 早《はや》く), kata sifat な → 〜に (上手《じょうず》な → 上手《じょうず》に). Bentuk ini sudah kamu pakai di N5 (たかく なります), sekarang dipakai bersama pola ように.',
                    'explanation_en' => 'When an adjective describes another adjective or a verb, its form changes: い-adjective → 〜く (早《はや》い → 早《はや》く), な-adjective → 〜に (上手《じょうず》な → 上手《じょうず》に). You already used this in N5 (たかく なります); now it is combined with ように patterns.',
                    'notes_id' => [
                        'Pengecualian: いい → よく (よく なりました = jadi lebih baik).',
                        'Contoh: 静《しず》かな → 静《しず》かに, 大《おお》きい → 大《おお》きく, きれいな → きれいに.',
                    ],
                    'notes_en' => [
                        'Exception: いい → よく (よく なりました = got better).',
                        'Examples: 静《しず》かな → 静《しず》かに, 大《おお》きい → 大《おお》きく, きれいな → きれいに.',
                    ],
                    'examples' => [
                        ['ja' => '早《はや》く 上手《じょうず》に お茶《ちゃ》が たてられるように なりたいです。', 'reading' => 'Hayaku jouzu ni ocha ga taterareru you ni naritai desu.', 'id' => 'Saya ingin cepat pandai membuat teh.', 'en' => 'I want to become able to make tea well, and quickly.'],
                        ['ja' => '部屋《へや》を きれいに 掃除《そうじ》して ください。', 'reading' => 'Heya o kirei ni souji shite kudasai.', 'id' => 'Tolong bersihkan kamar dengan rapi.', 'en' => 'Please clean the room thoroughly.'],
                        ['ja' => 'この 本《ほん》は 自由《じゆう》に 使《つか》って ください。', 'reading' => 'Kono hon wa jiyuu ni tsukatte kudasai.', 'id' => 'Silakan pakai buku ini dengan bebas.', 'en' => 'Please feel free to use this book.'],
                        ['ja' => 'もう 少《すこ》し 大《おお》きく 話《はな》して ください。', 'reading' => 'Mou sukoshi ookiku hanashite kudasai.', 'id' => 'Tolong bicara sedikit lebih keras.', 'en' => 'Please speak a little louder.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Belajar masak',
                        'title_en' => 'Learning to cook',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '早《はや》く 上手《じょうず》に 料理《りょうり》が できるように なりたいです。', 'reading' => 'Hayaku jouzu ni ryouri ga dekiru you ni naritai desu.', 'id' => 'Saya ingin cepat pandai memasak.', 'en' => 'I want to become good at cooking quickly.'],
                            ['speaker' => 'たなか', 'ja' => 'じゃ、毎日《まいにち》 少《すこ》しずつ 作《つく》るように したら いいですよ。', 'reading' => 'Ja, mainichi sukoshi zutsu tsukuru you ni shitara ii desu yo.', 'id' => 'Kalau begitu, sebaiknya berusaha membuat sedikit demi sedikit setiap hari.', 'en' => 'Then it is good to try making a little every day.'],
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
            ['slug' => 'n4-pelajaran-11'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 11',
                'name_en' => 'N4 Lesson 11 Vocabulary',
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
            ['事故にあいます', 'じこにあいます', 'jiko ni aimasu', 'tertimpa [kecelakaan]', 'to have [an accident]'],
            ['貯金します', 'ちょきんします', 'chokinshimasu', 'menabung', 'to save money'],
            ['過ぎます', 'すぎます', 'sugimasu', 'lewat [pukul tujuh]', 'to pass [seven o\'clock]'],
            ['慣れます', 'なれます', 'naremasu', 'terbiasa [dengan tugas]', 'to get used to [a job]'],
            ['腐ります', 'くさります', 'kusarimasu', 'busuk [makanan]', 'to go bad [food]'],
            ['剣道', 'けんどう', 'kendou', 'kendo (anggar gaya Jepang)', 'kendo (Japanese fencing)'],
            ['柔道', 'じゅうどう', 'juudou', 'judo', 'judo'],
            ['ラッシュ', 'ラッシュ', 'rasshu', 'keramaian, kesibukan (jam sibuk)', 'rush hour, rush'],
            ['宇宙', 'うちゅう', 'uchuu', 'angkasa', 'space, universe'],
            ['曲', 'きょく', 'kyoku', 'musik, lagu', 'piece of music, tune'],
            ['毎週', 'まいしゅう', 'maishuu', 'setiap minggu', 'every week'],
            ['毎月', 'まいつき', 'maitsuki', 'setiap bulan', 'every month'],
            ['毎年', 'まいとし', 'maitoshi', 'setiap tahun', 'every year'],
            ['このごろ', 'このごろ', 'konogoro', 'akhir-akhir ini', 'these days, lately'],
            ['やっと', 'やっと', 'yatto', 'akhirnya', 'at last, finally'],
            ['かなり', 'かなり', 'kanari', 'cukup, lumayan', 'fairly, considerably'],
            ['必ず', 'かならず', 'kanarazu', 'pasti, tentu', 'without fail, certainly'],
            ['絶対に', 'ぜったいに', 'zettai ni', 'mutlak, sama sekali', 'absolutely, definitely'],
            ['上手に', 'じょうずに', 'jouzu ni', 'dengan pandai', 'skillfully, well'],
            ['できるだけ', 'できるだけ', 'dekiru dake', 'sedapat mungkin', 'as much as possible'],
            ['ほとんど', 'ほとんど', 'hotondo', 'sebagian besar', 'almost all, mostly'],

            // 会話 (percakapan)
            ['お客様', 'おきゃくさま', 'okyakusama', 'tamu (kata hormat dari おきゃくさん)', 'guest (honorific of おきゃくさん)'],
            ['特別[な]', 'とくべつ', 'tokubetsu', 'khusus, spesial', 'special'],
            ['していらっしゃいます', 'していらっしゃいます', 'shite irasshaimasu', 'melakukan (kata hormat dari して います)', 'to be doing (honorific of して います)'],
            ['水泳', 'すいえい', 'suiei', 'renang', 'swimming'],
            ['違います', 'ちがいます', 'chigaimasu', 'salah, tidak benar', 'to be wrong, to differ'],
            ['使っていらっしゃるんですね', 'つかっていらっしゃるんですね', 'tsukatte irassharu n desu ne', 'Menggunakan ~, ya (kata hormat dari つかっているんですね)', 'You are using ~, I see (honorific)'],
            ['チャレンジします', 'チャレンジします', 'charenji shimasu', 'menantang, mencoba', 'to challenge, to try'],
            ['気持ち', 'きもち', 'kimochi', 'sikap, perasaan', 'attitude, feeling'],

            // 読み物 (bacaan)
            ['乗り物', 'のりもの', 'norimono', 'kendaraan', 'vehicle'],
            ['〜世紀', '〜せいき', 'seiki', 'abad ke ~', '~th century'],
            ['遠く', 'とおく', 'tooku', 'jauh', 'far away'],
            ['珍しい', 'めずらしい', 'mezurashii', 'langka, jarang', 'rare, unusual'],
            ['汽車', 'きしゃ', 'kisha', 'kereta api', 'steam train'],
            ['汽船', 'きせん', 'kisen', 'kapal api', 'steamship'],
            ['大勢の〜', 'おおぜいの〜', 'oozei no', 'banyak ~ (orang)', 'many ~ (people)'],
            ['運びます', 'はこびます', 'hakobimasu', 'mengangkut', 'to carry, to transport'],
            ['利用します', 'りようします', 'riyoushimasu', 'menggunakan', 'to use, to make use of'],
            ['自由に', 'じゆうに', 'jiyuu ni', 'dengan bebas', 'freely'],
        ];
    }
}
