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

class N4Lesson13Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 13: "Senang Beres-Beres".
     * Level N4 dibuat oleh N4Lesson1Seeder; seeder ini menambah unit order 13
     * di level itu (Unit.order hanya unik per level).
     *
     * Isi:
     *   - Unit order 13 ("Pelajaran 13")
     *   - Lesson Bunpou (order 0, category grammar) dengan 6 kartu:
     *       1. の sebagai pembentuk kata benda   4. Vのを 忘れました
     *       2. Vのは Adj です                    5. ふつうけいのを 知っていますか
     *       3. Vのが Adj です                    6. ふつうけいのは Ｎ です
     *   - Kategori kosakata "n4-pelajaran-13" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus dari daftar kosakata
     * pelajaran ini. Nama diri dan nama tempat khusus tidak dimasukkan.
     * Penjelasan, contoh kalimat dan dialog ditulis baru untuk aplikasi ini.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson13Seeder
     *
     * Kata kunci pencarian SQL memakai ASCII (kategori.slug + romaji), bukan
     * huruf Jepang (lihat docs/AGENTS.md aturan 4).
     */
    public function run(): void
    {
        if (! Level::where('code', 'N4')->exists()) {
            $this->call(N4Lesson1Seeder::class);
        }

        $level = Level::where('code', 'N4')->firstOrFail();

        $unit = Unit::updateOrCreate(
            ['level_id' => $level->id, 'order' => 13],
            [
                'title_id' => 'Pelajaran 13: Senang Beres-Beres',
                'title_en' => 'Lesson 13: I Like Tidying Up',
                'description_id' => 'Mengubah kalimat menjadi kata benda dengan の — Vのは／が〜です, Vのを 忘れました, 〜のを 知っていますか, 〜のは Ｎ です.',
                'description_en' => 'Turning a clause into a noun with の — Vのは／が〜です, Vのを 忘れました, 〜のを 知っていますか, 〜のは Ｎ です.',
                'icon' => 'mdi-notebook-edit',
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

        app(VocabularyQuizSync::class)->run('N4');
    }

    /** @return array<string, string> */
    private function ex(string $ja, string $reading, string $id, string $en): array
    {
        return ['ja' => $ja, 'reading' => $reading, 'id' => $id, 'en' => $en];
    }

    /** @return array<string, string> */
    private function line(string $speaker, string $ja, string $reading, string $id, string $en): array
    {
        return ['speaker' => $speaker, 'ja' => $ja, 'reading' => $reading, 'id' => $id, 'en' => $en];
    }

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
                'title_id' => 'の sebagai pembentuk kata benda',
                'title_en' => 'の as a noun-maker',
                'pattern' => 'ふつうけい ＋ の',
                'payload' => [
                    'explanation_id' => 'の yang dipasang setelah bentuk biasa mengubah isi sebuah kalimat menjadi seperti kata benda: "hal melakukan ...", "hal bahwa ...". Dengan begitu isi kalimat itu bisa dijadikan topik (のは), sasaran suka／bisa (のが), atau objek (のを), persis seperti kata benda biasa. Yang berada di depan の adalah bentuk biasa (kamus, ない, た), bukan bentuk sopan. Kata benda dan kata sifat な memakai な: 静《しず》かなのが 好《す》きです.',
                    'explanation_en' => 'の placed after the plain form turns the content of a sentence into something like a noun: "the act of doing ...", "the fact that ...". That lets the content act as a topic (のは), the target of liking or ability (のが), or an object (のを), just like an ordinary noun. What comes before の is the plain form (dictionary, ない, た), not the polite form. Nouns and な-adjectives take な: 静《しず》かなのが 好《す》きです.',
                    'notes_id' => [
                        'Bentuk kamus + の = "melakukan ..." (kegiatannya). Bentuk た + の = "hal yang sudah terjadi".',
                        'Pola turunan di pelajaran ini: Vのは Adj です, Vのが Adj です, Vのを 忘《わす》れました, 〜のを 知《し》って いますか, 〜のは Ｎ です.',
                    ],
                    'notes_en' => [
                        'Dictionary form + の = "doing ..." (the activity). た-form + の = "something that has happened".',
                        'Patterns built on it in this lesson: Vのは Adj です, Vのが Adj です, Vのを 忘《わす》れました, 〜のを 知《し》って いますか, 〜のは Ｎ です.',
                    ],
                    'examples' => [
                        $this->ex('泳《およ》ぐのは 楽《たの》しいです。', 'Oyogu no wa tanoshii desu.', 'Berenang itu menyenangkan.', 'Swimming is fun.'),
                        $this->ex('日本語《にほんご》を 話《はな》すのが 上手《じょうず》です。', 'Nihongo o hanasu no ga jouzu desu.', 'Dia pandai berbicara bahasa Jepang.', 'He is good at speaking Japanese.'),
                        $this->ex('先生《せんせい》が 来《き》たのを 知《し》って いますか。', 'Sensei ga kita no o shitte imasu ka.', 'Apakah kamu tahu bahwa guru sudah datang?', 'Do you know that the teacher has come?'),
                        $this->ex('傘《かさ》を 持《も》って 行《い》くのを 忘《わす》れました。', 'Kasa o motte iku no o wasuremashita.', 'Saya lupa membawa payung.', 'I forgot to take an umbrella.'),
                    ],
                    'dialogue' => [
                        'title_id' => 'Tentang hobi',
                        'title_en' => 'About hobbies',
                        'lines' => [
                            $this->line('サリ', 'ワヒュさんは 何《なに》を するのが 好《す》きですか。', 'Wahyu-san wa nani o suru no ga suki desu ka.', 'Wahyu, kamu suka melakukan apa?', 'Wahyu, what do you like doing?'),
                            $this->line('ワヒュ', '写真《しゃしん》を 撮《と》るのが 好《す》きです。', 'Shashin o toru no ga suki desu.', 'Saya suka memotret.', 'I like taking photos.'),
                            $this->line('サリ', '撮《と》った 写真《しゃしん》を 見《み》るのも 楽《たの》しいですよね。', 'Totta shashin o miru no mo tanoshii desu yo ne.', 'Melihat foto yang sudah diambil juga menyenangkan, ya.', 'Looking at the photos you took is fun too.'),
                            $this->line('ワヒュ', 'ええ、とても。', 'Ee, totemo.', 'Ya, sangat.', 'Yes, very much.'),
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => 'Ｖ(kamus)のは Adj です (kegiatan sebagai topik)',
                'title_en' => 'Ｖ(dictionary)のは Adj です (an activity as the topic)',
                'pattern' => 'Ｖ(kamus) ＋ のは ＋ Adj です',
                'payload' => [
                    'explanation_id' => 'Kegiatan dijadikan topik dengan のは, lalu dinilai dengan kata sifat: "melakukan ... itu ...". Bandingkan テニスは おもしろいです (tenis itu menarik) dengan テニスを するのは おもしろいです (bermain tenis itu menarik): versi dengan の menyebut kegiatannya secara konkret. Kata sifat yang sering dipakai: むずかしい, やさしい, おもしろい, たのしい, たいへん[な].',
                    'explanation_en' => 'An activity becomes the topic with のは and is then judged with an adjective: "doing ... is ...". Compare テニスは おもしろいです (tennis is interesting) with テニスを するのは おもしろいです (playing tennis is interesting): the version with の names the activity concretely. Common adjectives: むずかしい, やさしい, おもしろい, たのしい, たいへん[な].',
                    'notes_id' => [
                        'Objek kata kerja tetap memakai を: 漢字《かんじ》を 覚《おぼ》えるのは ...',
                        'Kata sifat yang dipakai menilai kegiatan: sulit, mudah, menarik, menyenangkan, berat.',
                    ],
                    'notes_en' => [
                        'The verb keeps its object particle を: 漢字《かんじ》を 覚《おぼ》えるのは ...',
                        'The adjectives evaluate the activity: difficult, easy, interesting, fun, hard work.',
                    ],
                    'examples' => [
                        $this->ex('漢字《かんじ》を 覚《おぼ》えるのは むずかしいです。', 'Kanji o oboeru no wa muzukashii desu.', 'Menghafal kanji itu sulit.', 'Memorising kanji is difficult.'),
                        $this->ex('毎日《まいにち》 日記《にっき》を 書《か》くのは たいへんです。', 'Mainichi nikki o kaku no wa taihen desu.', 'Menulis buku harian setiap hari itu berat.', 'Writing a diary every day is hard work.'),
                        $this->ex('友達《ともだち》と 話《はな》すのは 楽《たの》しいです。', 'Tomodachi to hanasu no wa tanoshii desu.', 'Mengobrol dengan teman itu menyenangkan.', 'Talking with friends is fun.'),
                        $this->ex('外国《がいこく》で 生活《せいかつ》するのは おもしろいです。', 'Gaikoku de seikatsu suru no wa omoshiroi desu.', 'Hidup di luar negeri itu menarik.', 'Living abroad is interesting.'),
                    ],
                    'dialogue' => [
                        'title_id' => 'Buku harian',
                        'title_en' => 'A diary',
                        'lines' => [
                            $this->line('たなか', 'ワヒュさんは 日記《にっき》を 書《か》いて いますか。', 'Wahyu-san wa nikki o kaite imasu ka.', 'Wahyu, apakah kamu menulis buku harian?', 'Wahyu, do you keep a diary?'),
                            $this->line('ワヒュ', '始《はじ》めましたが、毎日《まいにち》 書《か》くのは たいへんです。', 'Hajimemashita ga, mainichi kaku no wa taihen desu.', 'Sudah mulai, tetapi menulis tiap hari itu berat.', 'I started, but writing every day is hard.'),
                            $this->line('たなか', '短《みじか》く 書《か》くのは どうですか。', 'Mijikaku kaku no wa dou desu ka.', 'Kalau menulis singkat, bagaimana?', 'How about writing just a little?'),
                            $this->line('ワヒュ', 'それは やさしいですね。', 'Sore wa yasashii desu ne.', 'Itu mudah, ya.', 'That is easy.'),
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => 'Ｖ(kamus)のが Adj です (suka／bisa／cepat)',
                'title_en' => 'Ｖ(dictionary)のが Adj です (liking, ability, speed)',
                'pattern' => 'Ｖ(kamus) ＋ のが ＋ Adj です',
                'payload' => [
                    'explanation_id' => 'Kegiatan menjadi sasaran dari kata sifat yang menyatakan kesukaan, kemampuan, atau kecepatan. Ini perluasan dari Ｎが 好《す》きです di N5: kata benda diganti kegiatan + の. Kata sifat yang sering dipakai: 好《す》き[な], 嫌《きら》い[な], 上手《じょうず》[な], 下手《へた》[な], 速《はや》い, 遅《おそ》い.',
                    'explanation_en' => 'An activity becomes the target of an adjective of liking, ability, or speed. It extends Ｎが 好《す》きです from N5: the noun is replaced by an activity + の. Common adjectives: 好《す》き[な], 嫌《きら》い[な], 上手《じょうず》[な], 下手《へた》[な], 速《はや》い, 遅《おそ》い.',
                    'notes_id' => [
                        'Pemilik kemampuan memakai は／が di depan: 妹《いもうと》は 走《はし》るのが 速《はや》いです.',
                        'Beda dengan のは: のが menunjuk sasaran suka／bisa, のは menjadikan kegiatan sebagai topik penilaian.',
                    ],
                    'notes_en' => [
                        'The person who has the skill takes は／が in front: 妹《いもうと》は 走《はし》るのが 速《はや》いです.',
                        'Unlike のは: のが marks the target of liking or ability, while のは makes the activity the topic of an evaluation.',
                    ],
                    'examples' => [
                        $this->ex('料理《りょうり》を 作《つく》るのが 好《す》きです。', 'Ryouri o tsukuru no ga suki desu.', 'Saya suka memasak.', 'I like cooking.'),
                        $this->ex('歌《うた》を 歌《うた》うのが 下手《へた》です。', 'Uta o utau no ga heta desu.', 'Saya tidak pandai menyanyi.', 'I am bad at singing.'),
                        $this->ex('妹《いもうと》は 走《はし》るのが 速《はや》いです。', 'Imouto wa hashiru no ga hayai desu.', 'Adik perempuan saya cepat larinya.', 'My younger sister runs fast.'),
                        $this->ex('兄《あに》は 食《た》べるのが 遅《おそ》いです。', 'Ani wa taberu no ga osoi desu.', 'Kakak laki-laki saya lambat makannya.', 'My older brother eats slowly.'),
                    ],
                    'dialogue' => [
                        'title_id' => 'Pandai apa?',
                        'title_en' => 'What are you good at?',
                        'lines' => [
                            $this->line('たなか', 'サリさんは 何《なに》を するのが 上手《じょうず》ですか。', 'Sari-san wa nani o suru no ga jouzu desu ka.', 'Sari, kamu pandai melakukan apa?', 'Sari, what are you good at?'),
                            $this->line('サリ', '絵《え》を かくのが 好《す》きです。上手《じょうず》じゃ ありませんが。', 'E o kaku no ga suki desu. Jouzu ja arimasen ga.', 'Saya suka menggambar. Meskipun tidak pandai.', 'I like drawing, though I am not good at it.'),
                            $this->line('たなか', 'そうですか。ワヒュさんは？', 'Sou desu ka. Wahyu-san wa?', 'Begitu. Kalau Wahyu?', 'I see. And you, Wahyu?'),
                            $this->line('ワヒュ', '私《わたし》は 歩《ある》くのが 速《はや》いです。', 'Watashi wa aruku no ga hayai desu.', 'Saya cepat berjalan.', 'I walk fast.'),
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'Ｖ(kamus)のを 忘れました (lupa melakukan ...)',
                'title_en' => 'Ｖ(dictionary)のを 忘れました (forgot to do ...)',
                'pattern' => 'Ｖ(kamus) ＋ のを ＋ 忘《わす》れました',
                'payload' => [
                    'explanation_id' => 'Menyatakan bahwa kamu lupa melakukan sesuatu yang seharusnya dilakukan. Kegiatan yang dilupakan disebut secara konkret dengan のを. Bandingkan: かぎを 忘《わす》れました (kunci tertinggal／lupa membawa) dengan かぎを かけるのを 忘《わす》れました (lupa mengunci). Ungkapan spontan saat menyadari kesalahan: [あ、]いけない。',
                    'explanation_en' => 'Says that you forgot to do something you should have done. The forgotten action is named concretely with のを. Compare: かぎを 忘《わす》れました (forgot the key／left it behind) with かぎを かけるのを 忘《わす》れました (forgot to lock). The spontaneous phrase on realising a mistake: [あ、]いけない。',
                    'notes_id' => [
                        'Kata kerja di depan のを biasanya bentuk kamus (tindakan yang belum dilakukan).',
                        'Dalam bahasa lisan, を setelah の sering dihilangkan: 電気《でんき》を 消《け》すの 忘《わす》れました.',
                    ],
                    'notes_en' => [
                        'The verb before のを is normally in dictionary form (an action not yet done).',
                        'In speech, を after の is often dropped: 電気《でんき》を 消《け》すの 忘《わす》れました.',
                    ],
                    'examples' => [
                        $this->ex('出《で》かける とき、かぎを 掛《か》けるのを 忘《わす》れました。', 'Dekakeru toki, kagi o kakeru no o wasuremashita.', 'Saat pergi, saya lupa mengunci pintu.', 'When I went out, I forgot to lock the door.'),
                        $this->ex('宿題《しゅくだい》を 出《だ》すのを 忘《わす》れました。', 'Shukudai o dasu no o wasuremashita.', 'Saya lupa mengumpulkan PR.', 'I forgot to hand in my homework.'),
                        $this->ex('電気《でんき》を 消《け》すのを 忘《わす》れました。', 'Denki o kesu no o wasuremashita.', 'Saya lupa mematikan lampu.', 'I forgot to turn off the light.'),
                        $this->ex('銀行《ぎんこう》へ 行《い》くのを 忘《わす》れました。', 'Ginkou e iku no o wasuremashita.', 'Saya lupa pergi ke bank.', 'I forgot to go to the bank.'),
                    ],
                    'dialogue' => [
                        'title_id' => 'Sebelum pulang',
                        'title_en' => 'Before leaving',
                        'lines' => [
                            $this->line('サリ', 'あ、いけない！', 'A, ikenai!', 'Aduh, salah!', 'Oh no!'),
                            $this->line('ワヒュ', 'どうしたんですか。', 'Doushita n desu ka.', 'Ada apa?', 'What is wrong?'),
                            $this->line('サリ', '家《いえ》を 出《で》る とき、ドアに かぎを 掛《か》けるのを 忘《わす》れました。', 'Ie o deru toki, doa ni kagi o kakeru no o wasuremashita.', 'Saat keluar rumah, saya lupa mengunci pintu.', 'When I left home, I forgot to lock the door.'),
                            $this->line('ワヒュ', 'それは たいへんですね。早《はや》く 帰《かえ》ったら いいですよ。', 'Sore wa taihen desu ne. Hayaku kaettara ii desu yo.', 'Wah, gawat. Sebaiknya pulang cepat.', 'That is bad. You had better go home soon.'),
                            $this->line('サリ', 'じゃ、お先《さき》に 失礼《しつれい》します。', 'Ja, osaki ni shitsurei shimasu.', 'Kalau begitu, permisi duluan.', 'Then, excuse me for leaving first.'),
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => 'ふつうけい のを 知って いますか (apakah tahu bahwa ...?)',
                'title_en' => 'ふつうけい のを 知って いますか (do you know that ...?)',
                'pattern' => 'ふつうけい ＋ のを 知《し》って いますか',
                'payload' => [
                    'explanation_id' => 'Menanyakan apakah lawan bicara tahu suatu hal yang isinya disebut secara konkret, dengan bentuk biasa + のを. Jawaban "tidak" punya dua bentuk. 知《し》りません dipakai kalau sejak sebelum ditanya memang tidak tahu (misalnya pada 〜を 知《し》って いますか tentang alamat). 知《し》りませんでした dipakai kalau kabar itu baru kamu dapat dari pertanyaan tersebut, jadi "sampai tadi saya tidak tahu".',
                    'explanation_en' => 'Asks whether the listener knows a fact whose content is stated concretely, with plain form + のを. A negative answer has two forms. 知《し》りません is used when you simply did not know before being asked (e.g. asked about an address with 〜を 知《し》って いますか). 知《し》りませんでした is used when the news is new to you and you learn it from the question: "until just now I did not know".',
                    'notes_id' => [
                        'Jawaban positif: はい、知《し》って います.',
                        'Pertanyaan tentang benda biasa (住所《じゅうしょ》を 知《し》って いますか) dijawab 知《し》りません; pertanyaan berisi kabar baru (〜のを 知《し》って いますか) sering dijawab 知《し》りませんでした.',
                    ],
                    'notes_en' => [
                        'Positive answer: はい、知《し》って います.',
                        'A question about a plain thing (住所《じゅうしょ》を 知《し》って いますか) is answered 知《し》りません; a question that carries news (〜のを 知《し》って いますか) is often answered 知《し》りませんでした.',
                    ],
                    'examples' => [
                        $this->ex('来週《らいしゅう》 試験《しけん》が あるのを 知《し》って いますか。', 'Raishuu shiken ga aru no o shitte imasu ka.', 'Apakah kamu tahu bahwa minggu depan ada ujian?', 'Do you know there is an exam next week?'),
                        $this->ex('駅前《えきまえ》に 新《あたら》しい 店《みせ》が できたのを 知《し》って いますか。……いいえ、知《し》りませんでした。', 'Ekimae ni atarashii mise ga dekita no o shitte imasu ka. ...Iie, shirimasen deshita.', 'Tahukah kamu ada toko baru di depan stasiun? ……Tidak, saya tidak tahu.', 'Do you know a new shop opened in front of the station? ...No, I did not know.'),
                        $this->ex('この 近《ちか》くに 海岸《かいがん》が あるのを 知《し》って いますか。……はい、知《し》って います。', 'Kono chikaku ni kaigan ga aru no o shitte imasu ka. ...Hai, shitte imasu.', 'Tahukah kamu ada pantai di dekat sini? ……Ya, tahu.', 'Do you know there is a beach near here? ...Yes, I do.'),
                        $this->ex('先生《せんせい》の 電話《でんわ》番号《ばんごう》を 知《し》って いますか。……いいえ、知《し》りません。', 'Sensei no denwa bangou o shitte imasu ka. ...Iie, shirimasen.', 'Tahukah kamu nomor telepon guru? ……Tidak, tidak tahu.', 'Do you know the teacher\'s phone number? ...No, I do not.'),
                    ],
                    'dialogue' => [
                        'title_id' => 'Kabar dari kantor',
                        'title_en' => 'News from the office',
                        'lines' => [
                            $this->line('たなか', 'ワヒュさん、工場《こうじょう》が あしたから 休《やす》みなのを 知《し》って いますか。', 'Wahyu-san, koujou ga ashita kara yasumi na no o shitte imasu ka.', 'Wahyu, tahukah kamu bahwa pabrik libur mulai besok?', 'Wahyu, do you know the factory is closed from tomorrow?'),
                            $this->line('ワヒュ', 'いいえ、知《し》りませんでした。いつまでですか。', 'Iie, shirimasen deshita. Itsu made desu ka.', 'Tidak, saya tidak tahu. Sampai kapan?', 'No, I did not know. Until when?'),
                            $this->line('たなか', '三日間《みっかかん》です。', 'Mikkakan desu.', 'Tiga hari.', 'Three days.'),
                            $this->line('ワヒュ', 'そうですか。ありがとうございます。', 'Sou desu ka. Arigatou gozaimasu.', 'Begitu. Terima kasih.', 'I see. Thank you.'),
                        ],
                    ],
                ],
            ],

            // 6 ------------------------------------------------------------
            [
                'title_id' => 'ふつうけい のは Ｎ です (menegaskan satu hal)',
                'title_en' => 'ふつうけい のは Ｎ です (singling out one thing)',
                'pattern' => 'ふつうけい ＋ のは ＋ Ｎ です',
                'payload' => [
                    'explanation_id' => 'Pola untuk menegaskan satu unsur kalimat. Sebuah kalimat diubah menjadi frasa benda dengan の, dijadikan topik dengan は, lalu unsur yang ingin ditegaskan ditaruh di akhir sebagai Ｎ です. Cocok untuk menanyakan hal tertentu (kapan／di mana／siapa) atau untuk meluruskan ucapan lawan bicara. Di dalam frasa sebelum のは, subjek ditandai が, bukan は. Contoh: 初《はじ》めて 来《き》たのは いつですか (kapan pertama kali datang?).',
                    'explanation_en' => 'A pattern for singling out one element of a sentence. A clause is turned into a noun phrase with の, made the topic with は, and the element you want to stress goes at the end as Ｎ です. It is good for asking about one thing (when／where／who) or for correcting what the listener said. Inside the clause before のは, the subject is marked が, not は. Example: 初《はじ》めて 来《き》たのは いつですか (when did you first come?).',
                    'notes_id' => [
                        'Bagian sebelum のは memakai bentuk biasa; kata sifat な dan kata benda memakai な.',
                        'Saat meluruskan: いいえ、〜のは 〜です (Bukan, yang ... adalah ...).',
                    ],
                    'notes_en' => [
                        'The part before のは uses the plain form; な-adjectives and nouns take な.',
                        'When correcting: いいえ、〜のは 〜です (No, the one that ... is ...).',
                    ],
                    'examples' => [
                        $this->ex('初《はじ》めて 日本《にほん》へ 来《き》たのは いつですか。……おととしの 三月《さんがつ》です。', 'Hajimete Nihon e kita no wa itsu desu ka. ...Ototoshi no sangatsu desu.', 'Kapan pertama kali ke Jepang? ……Bulan Maret dua tahun lalu.', 'When did you first come to Japan? ...March two years ago.'),
                        $this->ex('ワヒュさんが 住《す》んで いるのは どこですか。……駅前《えきまえ》の アパートです。', 'Wahyu-san ga sunde iru no wa doko desu ka. ...Ekimae no apaato desu.', 'Di mana Wahyu tinggal? ……Di apartemen depan stasiun.', 'Where does Wahyu live? ...In the apartment in front of the station.'),
                        $this->ex('大阪《おおさか》で 生《う》まれたんですか。……いいえ、生《う》まれたのは 京都《きょうと》です。', 'Oosaka de umareta n desu ka. ...Iie, umareta no wa Kyouto desu.', 'Lahir di Osaka? ……Bukan, lahirnya di Kyoto.', 'Were you born in Osaka? ...No, I was born in Kyoto.'),
                        $this->ex('母《はは》が 育《そだ》ったのは 小《ちい》さな 村《むら》です。', 'Haha ga sodatta no wa chiisa na mura desu.', 'Tempat ibu saya dibesarkan adalah sebuah desa kecil.', 'The place my mother grew up is a small village.'),
                    ],
                    'dialogue' => [
                        'title_id' => 'Tempat lahir',
                        'title_en' => 'Where you were born',
                        'lines' => [
                            $this->line('たなか', 'ワヒュさんは ジャカルタで 生《う》まれたんですか。', 'Wahyu-san wa Jakaruta de umareta n desu ka.', 'Wahyu, apakah kamu lahir di Jakarta?', 'Wahyu, were you born in Jakarta?'),
                            $this->line('ワヒュ', 'いいえ、生《う》まれたのは 小《ちい》さな 村《むら》です。', 'Iie, umareta no wa chiisa na mura desu.', 'Bukan, saya lahir di sebuah desa kecil.', 'No, I was born in a small village.'),
                            $this->line('たなか', 'そうですか。ジャカルタに 来《き》たのは いつですか。', 'Sou desu ka. Jakaruta ni kita no wa itsu desu ka.', 'Begitu. Kapan pindah ke Jakarta?', 'I see. When did you come to Jakarta?'),
                            $this->line('ワヒュ', '十年前《じゅうねんまえ》です。', 'Juunen mae desu.', 'Sepuluh tahun lalu.', 'Ten years ago.'),
                        ],
                    ],
                ],
            ],
        ];
    }

    private function seedVocabulary(): void
    {
        $category = VocabularyCategory::updateOrCreate(
            ['slug' => 'n4-pelajaran-13'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 13',
                'name_en' => 'N4 Lesson 13 Vocabulary',
            ]
        );

        foreach ($this->words() as [$japanese, $hiragana, $romaji, $meaningId, $meaningEn]) {
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
            // Kata kerja
            ['参加します', 'さんかします', 'sankashimasu', 'mengikuti [perjalanan]', 'to take part in [a trip]'],
            ['育てます', 'そだてます', 'sodatemasu', 'memelihara, membesarkan', 'to raise, to grow'],
            ['運びます', 'はこびます', 'hakobimasu', 'mengangkut, membawa', 'to carry, to transport'],
            ['入院します', 'にゅういんします', 'nyuuinshimasu', 'opname, dirawat di rumah sakit', 'to be hospitalised'],
            ['退院します', 'たいいんします', 'taiinshimasu', 'keluar dari rumah sakit', 'to leave hospital'],
            ['入れます', 'いれます', 'iremasu', 'menghidupkan [catu daya]', 'to switch on [power]'],
            ['切ります', 'きります', 'kirimasu', 'mematikan [catu daya]', 'to switch off [power]'],
            ['掛けます', 'かけます', 'kakemasu', 'mengunci [kunci]', 'to lock [a key]'],
            ['つきます', 'つきます', 'tsukimasu', 'berbohong', 'to tell [a lie]'],

            // Kata sifat dan ungkapan
            ['気持ちがいい', 'きもちがいい', 'kimochi ga ii', 'rasa enak, nyaman', 'to feel good'],
            ['気持ちが悪い', 'きもちがわるい', 'kimochi ga warui', 'rasa tidak enak, jijik', 'to feel bad, to feel sick'],
            ['大きな〜', 'おおきな〜', 'ookina', 'besar ~', 'big ~'],
            ['小さな〜', 'ちいさな〜', 'chiisana', 'kecil ~', 'small ~'],

            // Kata benda
            ['赤ちゃん', 'あかちゃん', 'akachan', 'bayi', 'baby'],
            ['小学校', 'しょうがっこう', 'shougakkou', 'sekolah dasar (SD)', 'elementary school'],
            ['中学校', 'ちゅうがっこう', 'chuugakkou', 'sekolah menengah pertama (SMP)', 'junior high school'],
            ['駅前', 'えきまえ', 'ekimae', 'depan stasiun', 'in front of the station'],
            ['海岸', 'かいがん', 'kaigan', 'pantai laut', 'seashore'],
            ['工場', 'こうじょう', 'koujou', 'pabrik', 'factory'],
            ['村', 'むら', 'mura', 'desa, kampung', 'village'],
            ['かな', 'かな', 'kana', 'huruf hiragana, huruf katakana', 'kana characters'],
            ['指輪', 'ゆびわ', 'yubiwa', 'cincin', 'ring'],
            ['電源', 'でんげん', 'dengen', 'catu daya (power supply)', 'power supply'],
            ['習慣', 'しゅうかん', 'shuukan', 'kebiasaan', 'habit, custom'],
            ['健康', 'けんこう', 'kenkou', 'kesehatan', 'health'],
            ['〜製', '〜せい', 'sei', 'buatan ~', 'made in ~'],
            ['一昨年', 'おととし', 'ototoshi', 'dua tahun yang lalu', 'the year before last'],
            ['[あ、]いけない。', '[あ、]いけない。', 'ikenai', '[Ah,] salah! (ketika bersalah atau gagal)', 'Oh no! (on making a mistake)'],
            ['お先に[失礼します]。', 'おさきに[しつれいします]。', 'osaki ni', '[Permisi] duluan.', 'Excuse me for leaving first.'],

            // 会話 (percakapan)
            ['回覧', 'かいらん', 'kairan', 'edaran', 'circular'],
            ['研究室', 'けんきゅうしつ', 'kenkyuushitsu', 'laboratorium, ruang dosen', 'laboratory, professor\'s office'],
            ['きちんと', 'きちんと', 'kichinto', 'dengan rapi', 'neatly, properly'],
            ['整理します', 'せいりします', 'seirishimasu', 'mengatur, merapikan', 'to organise'],
            ['方法', 'ほうほう', 'houhou', 'cara', 'method'],
            ['〜という', '〜という', 'to iu', 'dengan judul ~', 'called ~, titled ~'],
            ['〜冊', '〜さつ', 'satsu', '~ buah (penghitung buku dan sejenisnya)', 'counter for books'],
            ['はんこ', 'はんこ', 'hanko', 'cap, stempel', 'seal, stamp'],
            ['押します', 'おします', 'oshimasu', 'mengecap [cap]', 'to press, to stamp [a seal]'],

            // 読み物 (bacaan)
            ['双子', 'ふたご', 'futago', 'anak kembar', 'twins'],
            ['姉妹', 'しまい', 'shimai', 'saudara perempuan', 'sisters'],
            ['5年生', 'ごねんせい', 'gonensei', 'kelas lima', 'fifth grader'],
            ['似ています', 'にています', 'nite imasu', 'mirip', 'to resemble'],
            ['性格', 'せいかく', 'seikaku', 'sifat, watak', 'character, personality'],
            ['おとなしい', 'おとなしい', 'otonashii', 'pendiam', 'quiet, gentle'],
            ['優しい', 'やさしい', 'yasashii', 'baik hati', 'kind'],
            ['世話をします', 'せわをします', 'sewa o shimasu', 'merawat', 'to take care of'],
            ['時間がたちます', 'じかんがたちます', 'jikan ga tachimasu', 'waktu berlalu', 'time passes'],
            ['大好き[な]', 'だいすき[な]', 'daisuki na', 'suka sekali', 'to love, to like very much'],
            ['〜点', '〜てん', 'ten', '~ poin', '~ points'],
            ['気が強い', 'きがつよい', 'ki ga tsuyoi', 'bersifat keras, galak', 'strong-willed'],
            ['けんかします', 'けんかします', 'kenkashimasu', 'bertengkar', 'to quarrel'],
            ['不思議[な]', 'ふしぎ[な]', 'fushigi na', 'aneh, ajaib', 'strange, mysterious'],
            ['年齢', 'ねんれい', 'nenrei', 'usia, umur', 'age'],
            ['しかた', 'しかた', 'shikata', 'cara', 'way, manner'],
        ];
    }
}
