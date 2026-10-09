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

class N4Lesson7Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 7: "Lebih Baik Jangan Memaksakan Diri" (saran,
     * dugaan, kesehatan dan cuaca). Level N4 berdiri di samping N5 dan dipilih
     * lewat selektor N5 / N4 di Tata Bahasa, Kosakata dan Referensi Tata Bahasa.
     *
     * Pelajaran 2-6 N4 belum ada; menu Kosakata dan Tata Bahasa hanya menampilkan
     * unit yang ada, jadi N4 sementara berisi Pelajaran 1 dan 7.
     *
     * Isi:
     *   - Unit order 7 di level N4
     *   - Lesson Bunpou (order 0, category grammar) dengan 6 kartu:
     *       1. Vた／Vない ほうが いいです      4. Vます形 ＋ ましょう
     *       2. 〜でしょう                       5. Bilangan ＋ で (batas)
     *       3. 〜かもしれません                 6. 何か／どこか／だれか＋ penjelas ＋ こと／ところ／ひと
     *   - Kategori kosakata "n4-pelajaran-7" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus. Penjelasan, contoh
     * kalimat dan dialog ditulis baru untuk aplikasi ini. Nama diri sengaja
     * tidak dimasukkan, sama seperti daftar nama diri di N5.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson7Seeder
     *
     * Kata kunci pencarian SQL memakai ASCII (kategori.slug + romaji), bukan
     * huruf Jepang (lihat docs/AGENTS.md aturan 4).
     */
    public function run(): void
    {
        // Level N4 dibuat oleh seeder Pelajaran 1; jalankan dulu bila belum ada.
        if (! Level::where('code', 'N4')->exists()) {
            $this->call(N4Lesson1Seeder::class);
        }

        $level = Level::where('code', 'N4')->firstOrFail();

        $unit = Unit::updateOrCreate(
            ['level_id' => $level->id, 'order' => 7],
            [
                'title_id' => 'Pelajaran 7: Lebih Baik Jangan Memaksakan Diri',
                'title_en' => 'Lesson 7: You Had Better Not Push Yourself',
                'description_id' => 'Memberi saran dengan 〜ほうがいい, menduga dengan 〜でしょう dan 〜かもしれません, serta kosakata kesehatan dan cuaca.',
                'description_en' => 'Giving advice with 〜ほうがいい, guessing with 〜でしょう and 〜かもしれません, plus health and weather vocabulary.',
                'icon' => 'mdi-weather-partly-cloudy',
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
                'title_id' => 'Vた／Vない ほうが いいです (memberi saran)',
                'title_en' => 'Vた／Vない ほうが いいです (giving advice)',
                'pattern' => 'Vた／Vない ＋ ほうが いいです',
                'payload' => [
                    'explanation_id' => 'Dipakai untuk menasihati lawan bicara. Untuk menyarankan melakukan sesuatu, pakai kata kerja bentuk-た: 毎日《まいにち》 歩《ある》いた ほうが いいです (lebih baik berjalan kaki setiap hari). Untuk menyarankan agar tidak melakukan sesuatu, pakai bentuk-ない: 夜《よる》 遅《おそ》く 食《た》べない ほうが いいです (lebih baik tidak makan larut malam). Secara makna, pembicara menimbang dua pilihan lalu menyebut yang menurutnya lebih baik, sehingga pilihan lainnya terkesan kurang baik. Karena itu nasihatnya terdengar cukup kuat.',
                    'explanation_en' => 'Used to advise the listener. To recommend doing something, use the verb た-form: 毎日《まいにち》 歩《ある》いた ほうが いいです (you had better walk every day). To recommend not doing something, use the ない-form: 夜《よる》 遅《おそ》く 食《た》べない ほうが いいです (you had better not eat late at night). In meaning, the speaker weighs two options and names the better one, which implies the other is not so good. That is why the advice sounds fairly strong.',
                    'notes_id' => [
                        'Karena terasa agak memaksa, hati-hati memakainya kepada atasan atau orang yang belum akrab. Untuk saran yang lebih ringan, pakai 〜たら いいですよ.',
                        'Bentuk-た untuk kata kerja kelompok mana pun sama dengan yang dipakai di 〜たことが あります: 行《い》った, 食《た》べた, 来《き》た, した.',
                        'Bentuk negatif memakai bentuk-ない, bukan bentuk-た: ×行《い》った ほうが いいです → ○行《い》かない ほうが いいです (lain arti!).',
                    ],
                    'notes_en' => [
                        'Because it feels a little pushy, be careful using it with superiors or people you are not close to. For lighter advice, use 〜たら いいですよ.',
                        'The た-form is the same as in 〜たことが あります: 行《い》った, 食《た》べた, 来《き》た, した.',
                        'The negative uses the ない-form, not the た-form: ×行《い》った ほうが いいです → ○行《い》かない ほうが いいです (a different meaning!).',
                    ],
                    'examples' => [
                        ['ja' => '毎日《まいにち》 少《すこ》し 歩《ある》いた ほうが いいですよ。', 'reading' => 'Mainichi sukoshi aruita hou ga ii desu yo.', 'id' => 'Lebih baik berjalan kaki sedikit setiap hari.', 'en' => 'You had better walk a little every day.'],
                        ['ja' => '熱《ねつ》が あるんです。……じゃ、きょうは 学校《がっこう》へ 行《い》かない ほうが いいですよ。', 'reading' => 'Netsu ga aru n desu. ...Ja, kyou wa gakkou e ikanai hou ga ii desu yo.', 'id' => 'Saya demam. ……Kalau begitu, lebih baik hari ini tidak pergi ke sekolah.', 'en' => 'I have a fever. ...Then you had better not go to school today.'],
                        ['ja' => '雨《あめ》ですから、かさを 持《も》って 行《い》った ほうが いいです。', 'reading' => 'Ame desu kara, kasa o motte itta hou ga ii desu.', 'id' => 'Karena hujan, lebih baik membawa payung.', 'en' => 'It is raining, so you had better take an umbrella.'],
                        ['ja' => '夜《よる》 遅《おそ》く コーヒーを 飲《の》まない ほうが いいですよ。', 'reading' => 'Yoru osoku koohii o nomanai hou ga ii desu yo.', 'id' => 'Lebih baik tidak minum kopi larut malam.', 'en' => 'You had better not drink coffee late at night.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Tampak tidak sehat',
                        'title_en' => 'Not looking well',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'ワヒュさん、元気《げんき》が ないですね。どうしたんですか。', 'reading' => 'Wahyu-san, genki ga nai desu ne. Doushita n desu ka.', 'id' => 'Wahyu, kamu tampak lesu. Ada apa?', 'en' => 'Wahyu, you look down. What is the matter?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'きのうから 頭《あたま》が 痛《いた》いんです。', 'reading' => 'Kinou kara atama ga itai n desu.', 'id' => 'Sejak kemarin kepala saya sakit.', 'en' => 'My head has hurt since yesterday.'],
                            ['speaker' => 'たなか', 'ja' => 'それは いけませんね。無理《むり》を しないで、きょうは 早《はや》く 帰《かえ》った ほうが いいですよ。', 'reading' => 'Sore wa ikemasen ne. Muri o shinaide, kyou wa hayaku kaetta hou ga ii desu yo.', 'id' => 'Wah, itu tidak baik. Jangan memaksakan diri, lebih baik hari ini pulang lebih awal.', 'en' => 'That is not good. Do not push yourself; you had better go home early today.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ありがとうございます。そうします。', 'reading' => 'Arigatou gozaimasu. Sou shimasu.', 'id' => 'Terima kasih. Akan saya lakukan.', 'en' => 'Thank you. I will do that.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => '〜でしょう (dugaan)',
                'title_en' => '〜でしょう (a guess)',
                'pattern' => 'ふつうけい ＋ でしょう',
                'payload' => [
                    'explanation_id' => 'Dipakai untuk menyampaikan dugaan pembicara tentang hal yang akan terjadi atau hal yang belum pasti: "mungkin ...", "kira-kira ...". Dibentuk dari bentuk biasa + でしょう. Kata benda dan kata sifat な dipakai tanpa だ: 晴《は》れでしょう, 静《しず》かでしょう. Kalau diucapkan dengan nada naik, 〜でしょうか berarti "apakah ...?" dan menjadi cara bertanya yang halus tentang sesuatu yang tidak pasti. Jawaban sering diperkuat dengan きっと (pasti) atau たぶん (barangkali).',
                    'explanation_en' => 'Used to give the speaker guess about something that will happen or is uncertain: "probably ...", "I expect ...". Formed with plain form + でしょう. Nouns and な-adjectives are used without だ: 晴《は》れでしょう, 静《しず》かでしょう. With a rising tone, 〜でしょうか means "I wonder if ...?" and is a soft way to ask about something uncertain. Answers are often strengthened with きっと (surely) or たぶん (perhaps).',
                    'notes_id' => [
                        'でしょう adalah bentuk sopan dari だろう. Dalam percakapan akrab kamu akan mendengar だろう atau 〜よね.',
                        'Jangan dipakai untuk rencana sendiri yang sudah pasti. Untuk itu pakai kalimat biasa: あした 会社《かいしゃ》へ 行《い》きます.',
                        'Sering muncul di prakiraan cuaca: あしたは 雨《あめ》でしょう.',
                    ],
                    'notes_en' => [
                        'でしょう is the polite form of だろう. In casual talk you will hear だろう or 〜よね.',
                        'Do not use it for your own firm plans. For those use an ordinary sentence: あした 会社《かいしゃ》へ 行《い》きます.',
                        'It appears often in weather forecasts: あしたは 雨《あめ》でしょう.',
                    ],
                    'examples' => [
                        ['ja' => 'あしたは 寒《さむ》くなるでしょう。', 'reading' => 'Ashita wa samuku naru deshou.', 'id' => 'Besok mungkin akan menjadi dingin.', 'en' => 'It will probably get cold tomorrow.'],
                        ['ja' => 'たなかさんは もう うちに 帰《かえ》ったでしょう。', 'reading' => 'Tanaka-san wa mou uchi ni kaetta deshou.', 'id' => 'Tanaka mungkin sudah pulang ke rumah.', 'en' => 'Tanaka has probably already gone home.'],
                        ['ja' => 'サリさんは 試験《しけん》に 合格《ごうかく》するでしょうか。……毎日《まいにち》 勉強《べんきょう》して いますから、きっと 合格《ごうかく》するでしょう。', 'reading' => 'Sari-san wa shiken ni goukaku suru deshou ka. ...Mainichi benkyou shite imasu kara, kitto goukaku suru deshou.', 'id' => 'Apakah Sari akan lulus ujian? ……Karena belajar setiap hari, pasti dia lulus.', 'en' => 'I wonder if Sari will pass the exam. ...She studies every day, so she will surely pass.'],
                        ['ja' => 'こんやは 星《ほし》が きれいでしょう。', 'reading' => 'Kon-ya wa hoshi ga kirei deshou.', 'id' => 'Malam ini bintang-bintangnya mungkin indah.', 'en' => 'The stars will probably be beautiful tonight.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Cuaca besok',
                        'title_en' => 'Tomorrow weather',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'あしたの 天気《てんき》は どうでしょうか。', 'reading' => 'Ashita no tenki wa dou deshou ka.', 'id' => 'Bagaimana cuaca besok, ya?', 'en' => 'I wonder what the weather will be like tomorrow.'],
                            ['speaker' => 'たなか', 'ja' => '天気予報《てんきよほう》を 見《み》ましたよ。午後《ごご》から 雨《あめ》が 降《ふ》るでしょう。', 'reading' => 'Tenki yohou o mimashita yo. Gogo kara ame ga furu deshou.', 'id' => 'Saya sudah lihat prakiraan cuaca. Mulai siang mungkin akan hujan.', 'en' => 'I saw the forecast. It will probably rain from the afternoon.'],
                            ['speaker' => 'サリ', 'ja' => 'じゃ、かさを 持《も》って 行《い》った ほうが いいですね。', 'reading' => 'Ja, kasa o motte itta hou ga ii desu ne.', 'id' => 'Kalau begitu, lebih baik membawa payung, ya.', 'en' => 'Then I had better take an umbrella.'],
                            ['speaker' => 'たなか', 'ja' => 'ええ、そのほうが いいですよ。', 'reading' => 'Ee, sono hou ga ii desu yo.', 'id' => 'Ya, itu lebih baik.', 'en' => 'Yes, that would be better.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => '〜かもしれません (kemungkinan)',
                'title_en' => '〜かもしれません (a possibility)',
                'pattern' => 'ふつうけい ＋ かもしれません',
                'payload' => [
                    'explanation_id' => 'Dipakai untuk mengatakan bahwa sesuatu "bisa jadi" terjadi, walaupun kemungkinannya kecil dan pembicara sendiri tidak yakin. Dibentuk dari bentuk biasa + かもしれません, dengan kata benda dan kata sifat な tanpa だ: 休《やす》みかもしれません. Dibandingkan 〜でしょう yang menandakan dugaan yang cukup yakin, 〜かもしれません menyisakan ruang untuk kemungkinan lain.',
                    'explanation_en' => 'Used to say that something "might" happen, even if the chance is small and the speaker is not sure. Formed with plain form + かもしれません, with nouns and な-adjectives used without だ: 休《やす》みかもしれません. Compared with 〜でしょう, which signals a fairly confident guess, 〜かもしれません leaves room for other possibilities.',
                    'notes_id' => [
                        'Tingkat keyakinan: きっと 〜でしょう (hampir pasti) > 〜でしょう (cukup yakin) > 〜かもしれません (bisa jadi).',
                        'Bentuk negatif: 〜ないかもしれません. Bentuk lampau: 〜たかもしれません.',
                        'Dalam percakapan akrab dipendekkan menjadi 〜かも.',
                    ],
                    'notes_en' => [
                        'Level of confidence: きっと 〜でしょう (almost certain) > 〜でしょう (fairly sure) > 〜かもしれません (could be).',
                        'Negative: 〜ないかもしれません. Past: 〜たかもしれません.',
                        'In casual talk it is shortened to 〜かも.',
                    ],
                    'examples' => [
                        ['ja' => '電車《でんしゃ》が 遅《おく》れて いますから、会議《かいぎ》に 間《ま》に 合《あ》わないかもしれません。', 'reading' => 'Densha ga okurete imasu kara, kaigi ni ma ni awanai kamo shiremasen.', 'id' => 'Kereta terlambat, jadi mungkin saya tidak sempat untuk rapat.', 'en' => 'The train is delayed, so I might not make it to the meeting.'],
                        ['ja' => 'あの 店《みせ》は きょうは 休《やす》みかもしれません。', 'reading' => 'Ano mise wa kyou wa yasumi kamo shiremasen.', 'id' => 'Toko itu mungkin tutup hari ini.', 'en' => 'That shop might be closed today.'],
                        ['ja' => 'この 肉《にく》は 古《ふる》いかもしれません。食《た》べない ほうが いいですよ。', 'reading' => 'Kono niku wa furui kamo shiremasen. Tabenai hou ga ii desu yo.', 'id' => 'Daging ini mungkin sudah tidak segar. Lebih baik jangan dimakan.', 'en' => 'This meat might be old. You had better not eat it.'],
                        ['ja' => 'ワヒュさんは 風邪《かぜ》を ひいたかもしれません。きのうから せきを して いますから。', 'reading' => 'Wahyu-san wa kaze o hiita kamo shiremasen. Kinou kara seki o shite imasu kara.', 'id' => 'Wahyu mungkin masuk angin. Sejak kemarin dia batuk.', 'en' => 'Wahyu may have caught a cold. He has been coughing since yesterday.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Teman belum datang',
                        'title_en' => 'A friend has not arrived',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'たなかさんは まだ 来《き》ませんね。', 'reading' => 'Tanaka-san wa mada kimasen ne.', 'id' => 'Tanaka belum datang, ya.', 'en' => 'Tanaka has not come yet.'],
                            ['speaker' => 'ワヒュ', 'ja' => '道《みち》が 込《こ》んでいるかもしれません。', 'reading' => 'Michi ga konde iru kamo shiremasen.', 'id' => 'Mungkin jalanan macet.', 'en' => 'The road might be jammed.'],
                            ['speaker' => 'サリ', 'ja' => '電話《でんわ》して みましょうか。', 'reading' => 'Denwa shite mimashou ka.', 'id' => 'Mau coba kita telepon?', 'en' => 'Shall we try calling?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'そうですね。でも、もう 少《すこ》し 待《ま》って みましょう。', 'reading' => 'Sou desu ne. Demo, mou sukoshi matte mimashou.', 'id' => 'Benar juga. Tapi, mari kita tunggu sebentar lagi.', 'en' => 'True. But let us wait a little longer.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'Vます形 ＋ ましょう (usul／tekad)',
                'title_en' => 'Vます-stem ＋ ましょう (a proposal or resolve)',
                'pattern' => 'Vます形 ＋ ましょう',
                'payload' => [
                    'explanation_id' => 'Dipakai pembicara untuk mengajukan usul atau menyatakan tekad kepada lawan bicara: "Mari kita ...", "Baik, saya ...". Dibentuk dari kata kerja bentuk-ます tanpa ます + ましょう. Dibandingkan 〜ましょうか (menawarkan atau mengajak sambil menanyakan pendapat), 〜ましょう lebih tegas karena pembicara sudah memutuskan. Pola ini sering menyusul sebuah pengamatan atau masalah: 〜ですね。〜ましょう。',
                    'explanation_en' => 'Used by the speaker to propose something or state a resolve to the listener: "Let us ...", "All right, I will ...". Formed from the verb ます-stem + ましょう. Compared with 〜ましょうか (offering or inviting while asking for the listener view), 〜ましょう is firmer because the speaker has already decided. It often follows an observation or a problem: 〜ですね。〜ましょう。',
                    'notes_id' => [
                        'Tingkat ketegasan: 〜ませんか (mengajak dengan halus) < 〜ましょうか (menawarkan／mengajak) < 〜ましょう (menetapkan).',
                        'Untuk menyetujui ajakan, jawab: ええ、そうしましょう／はい、行《い》きましょう.',
                    ],
                    'notes_en' => [
                        'Strength: 〜ませんか (a gentle invitation) < 〜ましょうか (offering／inviting) < 〜ましょう (deciding).',
                        'To accept an invitation, answer: ええ、そうしましょう／はい、行《い》きましょう.',
                    ],
                    'examples' => [
                        ['ja' => 'もう 時間《じかん》ですね。会議《かいぎ》を 始《はじ》めましょう。', 'reading' => 'Mou jikan desu ne. Kaigi o hajimemashou.', 'id' => 'Sudah waktunya, ya. Mari kita mulai rapat.', 'en' => 'It is time. Let us start the meeting.'],
                        ['ja' => '疲《つか》れましたね。少《すこ》し 休《やす》みましょう。', 'reading' => 'Tsukaremashita ne. Sukoshi yasumimashou.', 'id' => 'Capek, ya. Mari istirahat sebentar.', 'en' => 'We are tired. Let us rest a little.'],
                        ['ja' => '雨《あめ》が やみましたね。出《で》かけましょう。', 'reading' => 'Ame ga yamimashita ne. Dekakemashou.', 'id' => 'Hujannya sudah berhenti. Mari kita berangkat.', 'en' => 'The rain has stopped. Let us go out.'],
                        ['ja' => 'パソコンの 音《おと》が うるさいですね。ちょっと 見《み》ましょう。', 'reading' => 'Pasokon no oto ga urusai desu ne. Chotto mimashou.', 'id' => 'Bunyi PC-nya berisik, ya. Mari saya lihat sebentar.', 'en' => 'The PC is noisy. Let me take a look.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Makan siang',
                        'title_en' => 'Lunch',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'もう 十二時《じゅうにじ》ですね。昼《ひる》ごはんを 食《た》べに 行《い》きましょう。', 'reading' => 'Mou juuniji desu ne. Hirugohan o tabe ni ikimashou.', 'id' => 'Sudah jam dua belas, ya. Mari pergi makan siang.', 'en' => 'It is already twelve. Let us go for lunch.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい。どこへ 行《い》きましょうか。', 'reading' => 'Hai. Doko e ikimashou ka.', 'id' => 'Baik. Kita ke mana?', 'en' => 'Yes. Where shall we go?'],
                            ['speaker' => 'たなか', 'ja' => 'きょうは 雨《あめ》ですから、近《ちか》くの 店《みせ》に しましょう。', 'reading' => 'Kyou wa ame desu kara, chikaku no mise ni shimashou.', 'id' => 'Hari ini hujan, jadi mari kita pilih toko yang dekat.', 'en' => 'It is raining today, so let us choose a nearby place.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、そうしましょう。', 'reading' => 'Ee, sou shimashou.', 'id' => 'Ya, mari kita lakukan begitu.', 'en' => 'Yes, let us do that.'],
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => 'Bilangan ＋ で (batas waktu／jumlah)',
                'title_en' => 'Quantity ＋ で (a limit of time or amount)',
                'pattern' => 'Bilangan ＋ で',
                'payload' => [
                    'explanation_id' => 'Partikel で setelah keterangan bilangan (waktu, uang, jumlah) menyatakan batas: "dalam ...", "dengan ... saja", "seluruhnya ...". Pola ini menjawab pertanyaan seperti "dalam berapa lama?" atau "dengan berapa uang?". Contoh: 三十分《さんじゅっぷん》で (dalam tiga puluh menit), 千円《せんえん》で (dengan seribu yen), 全部《ぜんぶ》で (seluruhnya).',
                    'explanation_en' => 'The particle で after a quantity (time, money, number) states a limit: "within ...", "for ... only", "in total ...". It answers questions like "in how long?" or "for how much?". Examples: 三十分《さんじゅっぷん》で (in thirty minutes), 千円《せんえん》で (for one thousand yen), 全部《ぜんぶ》で (in total).',
                    'notes_id' => [
                        'Kata tanyanya: 何分《なんぷん》で (dalam berapa menit), いくらで (dengan harga berapa), 何日《なんにち》で (dalam berapa hari).',
                        'Jangan tertukar dengan で pada alat atau cara (バスで 行《い》きます) dan で pada tempat kegiatan (学校《がっこう》で 勉強《べんきょう》します). Yang ini mengikuti bilangan.',
                    ],
                    'notes_en' => [
                        'Question words: 何分《なんぷん》で (in how many minutes), いくらで (for how much), 何日《なんにち》で (in how many days).',
                        'Do not confuse it with で for means (バスで 行《い》きます) or で for the place of an activity (学校《がっこう》で 勉強《べんきょう》します). This one follows a quantity.',
                    ],
                    'examples' => [
                        ['ja' => 'この 仕事《しごと》は 二時間《にじかん》で 終《お》わります。', 'reading' => 'Kono shigoto wa nijikan de owarimasu.', 'id' => 'Pekerjaan ini selesai dalam dua jam.', 'en' => 'This work finishes in two hours.'],
                        ['ja' => '千円《せんえん》で 昼《ひる》ごはんを 食《た》べました。', 'reading' => 'Sen-en de hirugohan o tabemashita.', 'id' => 'Saya makan siang dengan seribu yen.', 'en' => 'I had lunch for one thousand yen.'],
                        ['ja' => '一週間《いっしゅうかん》で この 本《ほん》を 読《よ》みます。', 'reading' => 'Isshuukan de kono hon o yomimasu.', 'id' => 'Saya akan membaca buku ini dalam seminggu.', 'en' => 'I will read this book in a week.'],
                        ['ja' => '全部《ぜんぶ》で いくらですか。', 'reading' => 'Zenbu de ikura desu ka.', 'id' => 'Seluruhnya berapa?', 'en' => 'How much is it in total?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Dari stasiun ke kantor',
                        'title_en' => 'From the station to the office',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '駅《えき》から 会社《かいしゃ》まで どのぐらい かかりますか。', 'reading' => 'Eki kara kaisha made donogurai kakarimasu ka.', 'id' => 'Dari stasiun ke kantor kira-kira berapa lama?', 'en' => 'About how long does it take from the station to the office?'],
                            ['speaker' => 'ワヒュ', 'ja' => '歩《ある》くと 二十分《にじゅっぷん》ぐらいです。バスで 行《い》くと、五分《ごふん》で 着《つ》きますよ。', 'reading' => 'Aruku to nijuppun gurai desu. Basu de iku to, gofun de tsukimasu yo.', 'id' => 'Kalau jalan kaki sekitar dua puluh menit. Kalau naik bus, sampai dalam lima menit.', 'en' => 'On foot it is about twenty minutes. By bus you arrive in five minutes.'],
                            ['speaker' => 'サリ', 'ja' => '速《はや》いですね。バス代《だい》は いくらですか。', 'reading' => 'Hayai desu ne. Basudai wa ikura desu ka.', 'id' => 'Cepat juga. Ongkos busnya berapa?', 'en' => 'That is fast. How much is the bus fare?'],
                            ['speaker' => 'ワヒュ', 'ja' => '二百円《にひゃくえん》で 行《い》けますよ。', 'reading' => 'Nihyaku-en de ikemasu yo.', 'id' => 'Cukup dengan dua ratus yen.', 'en' => 'You can go for two hundred yen.'],
                        ],
                    ],
                ],
            ],

            // 6 ------------------------------------------------------------
            [
                'title_id' => '何か／どこか／だれか ＋ penjelas ＋ こと／ところ／ひと',
                'title_en' => '何か／どこか／だれか ＋ modifier ＋ こと／ところ／ひと',
                'pattern' => '何《なに》か ＋ 〜な こと',
                'payload' => [
                    'explanation_id' => 'Untuk menyebut sesuatu yang belum tentu ("sesuatu", "suatu tempat", "seseorang") sekaligus memberinya ciri, susunannya adalah kata tanya + か, lalu penjelas, lalu kata benda umum. Contoh: 何《なに》か 心配《しんぱい》な こと (suatu hal yang mengkhawatirkan). Urutan 心配《しんぱい》な 何《なに》か tidak dipakai. Kata benda umum yang lazim: こと (hal), もの (benda), ところ (tempat), ひと (orang), とき (waktu).',
                    'explanation_en' => 'To name something unspecified ("something", "somewhere", "someone") and describe it at the same time, the order is question word + か, then the modifier, then a general noun. Example: 何《なに》か 心配《しんぱい》な こと (something worrying). The order 心配《しんぱい》な 何《なに》か is not used. Common general nouns: こと (matter), もの (thing), ところ (place), ひと (person), とき (time).',
                    'notes_id' => [
                        'Pasangan yang sering dipakai: 何《なに》か〜もの／こと, どこか〜ところ, だれか〜ひと, いつか〜とき.',
                        'Penjelasnya boleh berupa kata sifat い／な atau kalimat bentuk biasa: 何《なに》か 食《た》べる もの.',
                    ],
                    'notes_en' => [
                        'Frequent pairs: 何《なに》か〜もの／こと, どこか〜ところ, だれか〜ひと, いつか〜とき.',
                        'The modifier can be an い／な-adjective or a plain-form clause: 何《なに》か 食《た》べる もの.',
                    ],
                    'examples' => [
                        ['ja' => '何《なに》か 食《た》べる ものが ありますか。', 'reading' => 'Nani ka taberu mono ga arimasu ka.', 'id' => 'Adakah sesuatu yang bisa dimakan?', 'en' => 'Is there something to eat?'],
                        ['ja' => 'どこか 静《しず》かな ところで 話《はな》しませんか。', 'reading' => 'Doko ka shizuka na tokoro de hanashimasen ka.', 'id' => 'Bagaimana kalau kita bicara di suatu tempat yang tenang?', 'en' => 'Shall we talk somewhere quiet?'],
                        ['ja' => 'だれか 日本語《にほんご》が わかる 人《ひと》は いませんか。', 'reading' => 'Dare ka nihongo ga wakaru hito wa imasen ka.', 'id' => 'Adakah seseorang yang mengerti bahasa Jepang?', 'en' => 'Is there anyone who understands Japanese?'],
                        ['ja' => '何《なに》か 困《こま》った ことが あったら、いつでも 言《い》って ください。', 'reading' => 'Nani ka komatta koto ga attara, itsudemo itte kudasai.', 'id' => 'Kalau ada sesuatu yang menyulitkan, katakan kapan saja.', 'en' => 'If you have any trouble, please tell me any time.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di ruang periksa',
                        'title_en' => 'At the clinic',
                        'lines' => [
                            ['speaker' => '先生', 'ja' => 'どうしましたか。', 'reading' => 'Doushimashita ka.', 'id' => 'Ada keluhan apa?', 'en' => 'What seems to be the problem?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'さいきん、よく 頭《あたま》が 痛《いた》くなるんです。', 'reading' => 'Saikin, yoku atama ga itaku naru n desu.', 'id' => 'Akhir-akhir ini kepala saya sering sakit.', 'en' => 'Lately my head often hurts.'],
                            ['speaker' => '先生', 'ja' => '何《なに》か ストレスに なる ことが ありますか。', 'reading' => 'Nani ka sutoresu ni naru koto ga arimasu ka.', 'id' => 'Adakah sesuatu yang membuat Anda stres?', 'en' => 'Is there anything stressing you?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、仕事《しごと》が 忙《いそが》しくて、あまり 寝《ね》て いないんです。', 'reading' => 'Ee, shigoto ga isogashikute, amari nete inai n desu.', 'id' => 'Ya, pekerjaan sibuk dan saya kurang tidur.', 'en' => 'Yes, work is busy and I am not sleeping much.'],
                            ['speaker' => '先生', 'ja' => 'そうですか。無理《むり》を しない ほうが いいですよ。', 'reading' => 'Sou desu ka. Muri o shinai hou ga ii desu yo.', 'id' => 'Begitu, ya. Lebih baik jangan memaksakan diri.', 'en' => 'I see. You had better not push yourself.'],
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
            ['slug' => 'n4-pelajaran-7'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 7',
                'name_en' => 'N4 Lesson 7 Vocabulary',
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
            ['運動します', 'うんどうします', 'undou shimasu', 'berolahraga', 'to exercise'],
            ['成功します', 'せいこうします', 'seikou shimasu', 'berhasil', 'to succeed'],
            ['失敗します', 'しっぱいします', 'shippai shimasu', 'gagal [ujian]', 'to fail [an exam]'],
            ['合格します', 'ごうかくします', 'goukaku shimasu', 'lulus [ujian]', 'to pass [an exam]'],
            ['やみます', 'やみます', 'yamimasu', '[hujan] berhenti', '[rain] stops'],
            ['晴れます', 'はれます', 'haremasu', 'cerah', 'to clear up'],
            ['曇ります', 'くもります', 'kumorimasu', 'mendung', 'to become cloudy'],
            ['続きます', 'つづきます', 'tsuzukimasu', '[demam] berlangsung', '[a fever] continues'],
            ['ひきます', 'ひきます', 'hikimasu', 'terkena [masuk angin] (かぜを〜)', 'to catch [a cold] (かぜを〜)'],
            ['冷やします', 'ひやします', 'hiyashimasu', 'mendinginkan', 'to cool'],
            ['込みます', 'こみます', 'komimasu', '[jalan] macet', '[a road] is crowded'],
            ['すきます', 'すきます', 'sukimasu', '[jalan] sepi', '[a road] is empty'],
            ['出ます', 'でます', 'demasu', 'mengikuti [pertandingan], menghadiri [pesta]', 'to take part in [a match], to attend [a party]'],
            ['無理をします', 'むりをします', 'muri o shimasu', 'memaksakan diri', 'to overdo it'],
            ['十分[な]', 'じゅうぶん[な]', 'juubun', 'cukup', 'enough, sufficient'],
            ['おかしい', 'おかしい', 'okashii', 'aneh, lucu', 'strange, funny'],
            ['うるさい', 'うるさい', 'urusai', 'bising', 'noisy'],
            ['先生', 'せんせい', 'sensei', 'dokter', 'doctor'],
            ['やけど', 'やけど', 'yakedo', 'luka bakar', 'a burn'],
            ['けが', 'けが', 'kega', 'luka', 'an injury'],
            ['せき', 'せき', 'seki', 'batuk', 'a cough'],
            ['インフルエンザ', 'インフルエンザ', 'infuruenza', 'influenza', 'influenza'],
            ['空', 'そら', 'sora', 'langit', 'sky'],
            ['太陽', 'たいよう', 'taiyou', 'matahari', 'sun'],
            ['星', 'ほし', 'hoshi', 'bintang', 'star'],
            ['風', 'かぜ', 'kaze', 'angin', 'wind'],
            ['東', 'ひがし', 'higashi', 'timur', 'east'],
            ['西', 'にし', 'nishi', 'barat', 'west'],
            ['南', 'みなみ', 'minami', 'selatan', 'south'],
            ['北', 'きた', 'kita', 'utara', 'north'],
            ['国際〜', 'こくさい〜', 'kokusai', 'internasional ~', 'international ~'],
            ['水道', 'すいどう', 'suidou', 'air PAM (air ledeng)', 'tap water, water supply'],
            ['エンジン', 'エンジン', 'enjin', 'mesin', 'engine'],
            ['チーム', 'チーム', 'chiimu', 'regu, tim', 'team'],
            ['今夜', 'こんや', 'konya', 'nanti malam, malam ini', 'tonight'],
            ['夕方', 'ゆうがた', 'yuugata', 'sore', 'evening, late afternoon'],
            ['まえ', 'まえ', 'mae', 'sebelum', 'before'],
            ['遅く', 'おそく', 'osoku', 'kemalaman, larut', 'late'],
            ['こんなに', 'こんなに', 'konna ni', 'begini', 'like this'],
            ['そんなに', 'そんなに', 'sonna ni', 'begitu (hal dekat lawan bicara)', 'like that (near the listener)'],
            ['あんなに', 'あんなに', 'anna ni', 'begitu (jauh dari pembicara dan lawan bicara)', 'like that (far from both)'],
            ['ヨーロッパ', 'ヨーロッパ', 'yooroppa', 'Eropa', 'Europe'],

            // 会話 (percakapan)
            ['元気', 'げんき', 'genki', 'sehat, bersemangat', 'healthy, in good spirits'],
            ['胃', 'い', 'i', 'lambung', 'stomach'],
            ['ストレス', 'ストレス', 'sutoresu', 'stres', 'stress'],
            ['それは いけませんね', 'それは いけませんね', 'sore wa ikemasen ne', 'Itu kurang bagus, ya (ungkapan simpati)', 'That is not good (expression of sympathy)'],

            // 読み物 (bacaan)
            ['星占い', 'ほしうらない', 'hoshiuranai', 'horoskop', 'horoscope'],
            ['牡牛座', 'おうしざ', 'oushiza', 'Taurus', 'Taurus'],
            ['働きすぎ', 'はたらきすぎ', 'hatarakisugi', 'bekerja terlalu keras', 'overwork'],
            ['困ります', 'こまります', 'komarimasu', 'bingung, kesulitan', 'to be in trouble'],
            ['宝くじ', 'たからくじ', 'takarakuji', 'lotre', 'lottery'],
            ['当たります', 'あたります', 'atarimasu', 'memenangi [lotre]', 'to win [the lottery]'],
            ['健康', 'けんこう', 'kenkou', 'kesehatan', 'health'],
            ['恋愛', 'れんあい', 'renai', 'asmara', 'romance'],
            ['恋人', 'こいびと', 'koibito', 'pacar, kekasih', 'sweetheart, partner'],
            ['ラッキーアイテム', 'ラッキーアイテム', 'rakkii aitemu', 'benda keberuntungan', 'lucky item'],
            ['石', 'いし', 'ishi', 'batu', 'stone'],
        ];
    }
}
