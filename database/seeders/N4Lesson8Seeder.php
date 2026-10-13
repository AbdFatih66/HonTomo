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

class N4Lesson8Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 8 ("Apa Artinya Tanda Ini?"): bentuk perintah dan
     * larangan, membaca tulisan pada tanda, menjelaskan arti, dan menyampaikan
     * pesan orang lain.
     *
     * Isi:
     *   - Level N4 (dibuat bila belum ada) + Unit order 8 ("Pelajaran 8")
     *   - Lesson Bunpou (order 0, category grammar) dengan 7 kartu:
     *       1. Bentuk perintah (命令形)        5. XはYという意味です
     *       2. Bentuk larangan (V辞書形＋な)    6. 〜と言っていました
     *       3. Cara pakai perintah／〜なさい     7. 〜と伝えていただけませんか
     *       4. 〜と書いてあります／〜と読みます
     *   - Kategori kosakata "n4-pelajaran-8" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus. Penjelasan, contoh
     * kalimat dan dialog ditulis baru untuk aplikasi ini.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson8Seeder
     *
     * Kata kunci pencarian SQL memakai ASCII (kategori.slug + romaji), bukan
     * huruf Jepang (lihat docs/AGENTS.md aturan 4).
     */
    public function run(): void
    {
        // Level N4 biasanya sudah dibuat N4Lesson1Seeder; dibuat lagi (idempoten) agar seeder ini bisa jalan sendiri.
        $level = Level::updateOrCreate(
            ['code' => 'N4'],
            [
                'name_id' => 'Dasar Lanjutan (N4)',
                'name_en' => 'Upper Beginner (N4)',
                'description_id' => 'Lanjutan dari N5: percakapan sehari-hari yang lebih natural, meminta penjelasan, meminta tolong dengan sopan, dan bertanya cara melakukan sesuatu.',
                'description_en' => 'Continues from N5: more natural everyday conversation, asking for explanations, polite requests, and asking how to do things.',
                'order' => 2,
                'is_active' => true,
            ]
        );

        $unit = Unit::updateOrCreate(
            ['level_id' => $level->id, 'order' => 8],
            [
                'title_id' => 'Pelajaran 8: Apa Artinya Tanda Ini?',
                'title_en' => 'Lesson 8: What Does This Sign Mean?',
                'description_id' => 'Bentuk perintah dan larangan, 〜と書いてあります, 〜という意味です, 〜と言っていました, 〜と伝えていただけませんか.',
                'description_en' => 'Imperative and prohibitive forms, 〜と書いてあります, 〜という意味です, 〜と言っていました, 〜と伝えていただけませんか.',
                'icon' => 'mdi-sign-direction',
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
                'title_id' => 'Bentuk perintah (命令形)',
                'title_en' => 'Imperative form (命令形)',
                'pattern' => 'Ｖ 命令形《めいれいけい》',
                'payload' => [
                    'explanation_id' => 'Bentuk perintah dipakai untuk menyuruh seseorang melakukan sesuatu dengan nada tegas: "Lakukan!". Cara membentuknya: Kelompok I — bunyi akhir bentuk ます (kolom い) diganti bunyi kolom え (書《か》きます → 書《か》け, 飲《の》みます → 飲《の》め, 急《いそ》ぎます → 急《いそ》げ). Kelompok II — bentuk ます dibuang, ganti ろ (食《た》べます → 食《た》べろ, 見《み》ます → 見《み》ろ). Kelompok III — します → しろ, 来《き》ます → 来《こ》い. Pengecualian: くれます → くれ.',
                    'explanation_en' => 'The imperative tells someone to do something in a firm tone: "Do it!". Forming it: Group I — the final い-column sound of the ます form changes to the え-column (書《か》きます → 書《か》け, 飲《の》みます → 飲《の》め, 急《いそ》ぎます → 急《いそ》げ). Group II — drop ます and add ろ (食《た》べます → 食《た》べろ, 見《み》ます → 見《み》ろ). Group III — します → しろ, 来《き》ます → 来《こ》い. Exception: くれます → くれ.',
                    'notes_id' => [
                        'Kata kerja keadaan seperti ある, できる, わかる tidak punya bentuk perintah.',
                        'Bunyinya kasar dan keras, jadi pemakaiannya sangat terbatas (lihat kartu 3). Jangan dipakai kepada atasan atau orang yang belum akrab.',
                        'Contoh Kelompok I lain: 買《か》う → 買《か》え, 待《ま》つ → 待《ま》て, 走《はし》る → 走《はし》れ, 話《はな》す → 話《はな》せ.',
                    ],
                    'notes_en' => [
                        'State verbs such as ある, できる and わかる have no imperative form.',
                        'It sounds rough and strong, so its use is quite limited (see card 3). Do not use it to a superior or someone you do not know well.',
                        'More Group I examples: 買《か》う → 買《か》え, 待《ま》つ → 待《ま》て, 走《はし》る → 走《はし》れ, 話《はな》す → 話《はな》せ.',
                    ],
                    'examples' => [
                        ['ja' => '早《はや》く 起《お》きろ。', 'reading' => 'Hayaku okiro.', 'id' => 'Cepat bangun!', 'en' => 'Get up quickly!'],
                        ['ja' => 'もっと 走《はし》れ。', 'reading' => 'Motto hashire.', 'id' => 'Lari lebih kencang!', 'en' => 'Run faster!'],
                        ['ja' => '危《あぶ》ない。逃《に》げろ。', 'reading' => 'Abunai. Nigero.', 'id' => 'Bahaya. Lari!', 'en' => 'Danger. Run!'],
                        ['ja' => 'ちょっと 待《ま》て。', 'reading' => 'Chotto mate.', 'id' => 'Tunggu sebentar!', 'en' => 'Wait a moment!'],
                        ['ja' => 'もう 時間《じかん》が ない。早《はや》く 来《こ》い。', 'reading' => 'Mou jikan ga nai. Hayaku koi.', 'id' => 'Waktunya sudah tidak ada. Cepat ke sini!', 'en' => 'There is no time left. Come quickly!'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Latihan lari',
                        'title_en' => 'Running practice',
                        'lines' => [
                            ['speaker' => 'コーチ', 'ja' => 'もっと 速《はや》く 走《はし》れ。あと 五百《ごひゃく》メートルだ。', 'reading' => 'Motto hayaku hashire. Ato gohyaku meetoru da.', 'id' => 'Lari lebih cepat. Tinggal lima ratus meter.', 'en' => 'Run faster. Only five hundred metres to go.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい。もう 走《はし》れません。', 'reading' => 'Hai. Mou hashiremasen.', 'id' => 'Baik. Saya sudah tidak sanggup berlari lagi.', 'en' => 'Yes. I cannot run any more.'],
                            ['speaker' => 'コーチ', 'ja' => 'がんばれ。もう すぐ ゴールだ。', 'reading' => 'Ganbare. Mou sugu goru da.', 'id' => 'Semangat. Sebentar lagi garis akhir.', 'en' => 'Hang in there. The finish line is close.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => 'Bentuk larangan (V辞書形＋な)',
                'title_en' => 'Prohibitive form (dictionary form + な)',
                'pattern' => 'Ｖ 辞書形《じしょけい》＋ な',
                'payload' => [
                    'explanation_id' => 'Bentuk larangan dipakai untuk menyuruh seseorang agar TIDAK melakukan sesuatu: "Jangan …!". Cara membentuknya sangat mudah — tambahkan な pada bentuk kamus, untuk semua kelompok: 行《い》く → 行《い》くな, 食《た》べる → 食《た》べるな, する → するな, 来《く》る → 来《く》るな. Seperti bentuk perintah, nadanya keras dan hanya cocok pada situasi tertentu.',
                    'explanation_en' => 'The prohibitive tells someone NOT to do something: "Do not …!". It is simple to form — add な to the dictionary form, for every group: 行《い》く → 行《い》くな, 食《た》べる → 食《た》べるな, する → するな, 来《く》る → 来《く》るな. Like the imperative, it sounds strong and fits only certain situations.',
                    'notes_id' => [
                        'Sesama pria yang akrab sering menambah よ di akhir kalimat agar terdengar lebih lunak: あまり 飲《の》むな[よ]。',
                        'Jangan tertukar: 〜ないで ください itu permintaan sopan, sedangkan 〜な adalah larangan keras.',
                        'Pada tanda dan slogan, larangan sering muncul sebagai kata benda: 入《はい》るな → 立入禁止《たちいりきんし》.',
                    ],
                    'notes_en' => [
                        'Between close male friends, よ is often added at the end to soften it: あまり 飲《の》むな[よ]。',
                        'Do not confuse them: 〜ないで ください is a polite request, while 〜な is a strong prohibition.',
                        'On signs and slogans a prohibition often appears as a noun: 入《はい》るな → 立入禁止《たちいりきんし》.',
                    ],
                    'examples' => [
                        ['ja' => '遅《おく》れるな。', 'reading' => 'Okureru na.', 'id' => 'Jangan terlambat!', 'en' => 'Do not be late!'],
                        ['ja' => '危《あぶ》ないから、ここで 遊《あそ》ぶな。', 'reading' => 'Abunai kara, koko de asobu na.', 'id' => 'Berbahaya, jangan bermain di sini!', 'en' => 'It is dangerous, so do not play here!'],
                        ['ja' => '約束《やくそく》を 忘《わす》れるな。', 'reading' => 'Yakusoku o wasureru na.', 'id' => 'Jangan lupa janji!', 'en' => 'Do not forget the promise!'],
                        ['ja' => '最後《さいご》まで あきらめるな。', 'reading' => 'Saigo made akirameru na.', 'id' => 'Jangan menyerah sampai akhir!', 'en' => 'Do not give up until the end!'],
                        ['ja' => 'ここで 写真《しゃしん》を 撮《と》るな。', 'reading' => 'Koko de shashin o toru na.', 'id' => 'Jangan memotret di sini!', 'en' => 'Do not take photos here!'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di tepi kolam',
                        'title_en' => 'By the pond',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'おい、その 池《いけ》に 入《はい》るな。', 'reading' => 'Oi, sono ike ni hairu na.', 'id' => 'Hei, jangan masuk ke kolam itu!', 'en' => 'Hey, do not go into that pond!'],
                            ['speaker' => 'ワヒュ', 'ja' => 'えっ、だめですか。', 'reading' => 'E, dame desu ka.', 'id' => 'Eh, tidak boleh, ya?', 'en' => 'Oh, is it not allowed?'],
                            ['speaker' => 'たなか', 'ja' => 'あそこに 「入《はい》るな」と 書《か》いて あるだろう。', 'reading' => 'Asoko ni "hairu na" to kaite aru darou.', 'id' => 'Di sana tertulis "Dilarang masuk", kan.', 'en' => 'It says "Do not enter" over there, does it not.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => 'Kapan memakai perintah／larangan, dan 〜なさい',
                'title_en' => 'When to use them, and 〜なさい',
                'pattern' => 'Ｖ ます形《けい》の ます → なさい',
                'payload' => [
                    'explanation_id' => 'Bentuk perintah dan larangan terdengar keras, jadi hanya dipakai di situasi tertentu: (1) pria yang posisinya atau usianya di atas, atau ayah kepada anak; (2) antar sesama pria akrab, sering dengan よ di akhir; (3) darurat seperti kebakaran atau gempa bumi, atau instruksi saat kerja bersama di pabrik; (4) latihan kelompok atau pelajaran olahraga; (5) menyoraki pertandingan olahraga — di sini wanita pun memakainya; (6) tanda lalu lintas, slogan, dan tulisan yang butuh efek kuat. Untuk perintah yang lebih halus dipakai 〜なさい (bentuk ます tanpa ます + なさい): orang tua kepada anak atau guru kepada murid. Tidak boleh dipakai kepada orang yang lebih tua.',
                    'explanation_en' => 'The imperative and prohibitive sound strong, so they are used only in certain situations: (1) a man of higher position or age, or a father to a child; (2) between close male friends, often with よ at the end; (3) emergencies such as fire or earthquake, or work instructions in a factory; (4) group training or PE class; (5) cheering at a sports match — here women use them too; (6) traffic signs, slogans and writing that needs a strong effect. A gentler command is 〜なさい (ます-form stem + なさい): a parent to a child or a teacher to a student. It cannot be used to someone older.',
                    'notes_id' => [
                        'Kalau ragu, pakai 〜て ください／〜ないで ください. Perintah tegas hampir tidak pernah dipakai kepada atasan atau tamu.',
                        'Contoh 〜なさい: 食《た》べます → 食《た》べなさい, 勉強《べんきょう》します → 勉強《べんきょう》しなさい.',
                        'Orang non-Jepang tidak perlu memakainya untuk berbicara, tetapi harus bisa mengenalinya saat membaca tanda, mendengar teriakan, atau menonton olahraga.',
                    ],
                    'notes_en' => [
                        'When in doubt, use 〜て ください／〜ないで ください. A firm command is almost never used to a boss or a guest.',
                        '〜なさい examples: 食《た》べます → 食《た》べなさい, 勉強《べんきょう》します → 勉強《べんきょう》しなさい.',
                        'Learners rarely need to speak this way, but must be able to recognise it on signs, in shouts and in sports.',
                    ],
                    'examples' => [
                        ['ja' => '早《はや》く 寝《ね》ろ。あしたも 学校《がっこう》だぞ。', 'reading' => 'Hayaku nero. Ashita mo gakkou da zo.', 'id' => 'Cepat tidur. Besok juga sekolah! (ayah kepada anak)', 'en' => 'Go to bed. You have school tomorrow too! (father to child)'],
                        ['ja' => '火事《かじ》だ。逃《に》げろ。', 'reading' => 'Kaji da. Nigero.', 'id' => 'Kebakaran! Lari! (keadaan darurat)', 'en' => 'Fire! Run! (emergency)'],
                        ['ja' => '休《やす》め。', 'reading' => 'Yasume.', 'id' => 'Istirahat! (aba-aba di latihan)', 'en' => 'At ease! (order during training)'],
                        ['ja' => '負《ま》けるな。', 'reading' => 'Makeru na.', 'id' => 'Jangan kalah! (menyoraki pertandingan)', 'en' => 'Do not lose! (cheering at a match)'],
                        ['ja' => '手《て》を 洗《あら》いなさい。', 'reading' => 'Te o arainasai.', 'id' => 'Cuci tangan! (orang tua kepada anak)', 'en' => 'Wash your hands! (parent to child)'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di rumah',
                        'title_en' => 'At home',
                        'lines' => [
                            ['speaker' => 'おかあさん', 'ja' => 'ごはんですよ。手《て》を 洗《あら》いなさい。', 'reading' => 'Gohan desu yo. Te o arainasai.', 'id' => 'Waktunya makan. Cuci tanganmu!', 'en' => 'Time to eat. Wash your hands!'],
                            ['speaker' => 'こども', 'ja' => 'はあい。', 'reading' => 'Haai.', 'id' => 'Iya.', 'en' => 'Okay.'],
                            ['speaker' => 'おかあさん', 'ja' => 'それから、宿題《しゅくだい》も しなさい。', 'reading' => 'Sorekara, shukudai mo shinasai.', 'id' => 'Dan kerjakan PR-mu juga.', 'en' => 'And do your homework too.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => '〜と書いてあります／〜と読みます',
                'title_en' => '〜と書いてあります／〜と読みます',
                'pattern' => '「〜」と 書《か》いて あります／「〜」と 読《よ》みます',
                'payload' => [
                    'explanation_id' => 'Dipakai untuk menyebutkan isi tulisan atau cara membaca sebuah huruf. 「〜」と 書《か》いて あります berarti "di situ tertulis 〜" — keadaan yang tersisa karena seseorang sudah menulisnya (て形 + あります). 「〜」と 読《よ》みます berarti "dibaca 〜". Kata と menandai isi kutipan, sama seperti と pada 〜と 言《い》います. Untuk menanyakan cara baca: この 漢字《かんじ》は 何《なん》と 読《よ》みますか。',
                    'explanation_en' => 'Used to state what a piece of writing says or how a character is read. 「〜」と 書《か》いて あります means "it says 〜 there" — a state that remains because someone has written it (て-form + あります). 「〜」と 読《よ》みます means "it is read as 〜". The particle と marks the content of the quotation, just like と in 〜と 言《い》います. To ask how to read something: この 漢字《かんじ》は 何《なん》と 読《よ》みますか。',
                    'notes_id' => [
                        'Jawaban cara baca: 「出口《でぐち》」と 読《よ》みます atau cukup 「でぐち」です。',
                        'Untuk menanyakan isi: あそこに 何《なに》と 書《か》いて ありますか。',
                        'Kutipannya ditulis apa adanya (boleh kanji, hiragana, atau bahasa Inggris) di dalam 「 」.',
                    ],
                    'notes_en' => [
                        'To answer a reading question: 「出口《でぐち》」と 読《よ》みます, or just 「でぐち」です。',
                        'To ask what something says: あそこに 何《なに》と 書《か》いて ありますか。',
                        'The quotation goes inside 「 」 exactly as written (kanji, hiragana or English).',
                    ],
                    'examples' => [
                        ['ja' => 'この 漢字《かんじ》は 何《なん》と 読《よ》みますか。……「入口《いりぐち》」と 読《よ》みます。', 'reading' => 'Kono kanji wa nan to yomimasu ka. ..."Iriguchi" to yomimasu.', 'id' => 'Huruf kanji ini dibaca apa? ……Dibaca "iriguchi".', 'en' => 'How is this kanji read? ...It is read "iriguchi".'],
                        ['ja' => 'あそこに 「止《と》まれ」と 書《か》いて あります。', 'reading' => 'Asoko ni "tomare" to kaite arimasu.', 'id' => 'Di sana tertulis "Berhenti".', 'en' => 'It says "Stop" over there.'],
                        ['ja' => 'ドアに 「押《お》して ください」と 書《か》いて あります。', 'reading' => 'Doa ni "oshite kudasai" to kaite arimasu.', 'id' => 'Di pintu tertulis "Silakan dorong".', 'en' => 'The door says "Please push".'],
                        ['ja' => '黒板《こくばん》に 何《なん》と 書《か》いて ありますか。……あしたは テストだと 書《か》いて あります。', 'reading' => 'Kokuban ni nan to kaite arimasu ka. ...Ashita wa tesuto da to kaite arimasu.', 'id' => 'Apa yang tertulis di papan tulis? ……Tertulis bahwa besok ada ujian.', 'en' => 'What does the blackboard say? ...It says there is a test tomorrow.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Membaca pengumuman',
                        'title_en' => 'Reading a notice',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません。この 紙《かみ》に 何《なん》と 書《か》いて ありますか。', 'reading' => 'Sumimasen. Kono kami ni nan to kaite arimasu ka.', 'id' => 'Permisi. Apa yang tertulis di kertas ini?', 'en' => 'Excuse me. What does this paper say?'],
                            ['speaker' => 'たなか', 'ja' => '「使用中《しようちゅう》」と 書《か》いて あります。', 'reading' => 'Shiyouchuu to kaite arimasu.', 'id' => 'Tertulis "sedang dipakai".', 'en' => 'It says "in use".'],
                            ['speaker' => 'ワヒュ', 'ja' => 'この 漢字《かんじ》は 何《なん》と 読《よ》みますか。', 'reading' => 'Kono kanji wa nan to yomimasu ka.', 'id' => 'Kanji ini dibaca apa?', 'en' => 'How is this kanji read?'],
                            ['speaker' => 'たなか', 'ja' => '「しようちゅう」と 読《よ》みます。', 'reading' => 'Shiyouchuu to yomimasu.', 'id' => 'Dibaca "shiyouchuu".', 'en' => 'It is read "shiyouchuu".'],
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => 'X は Y という意味です (menjelaskan arti)',
                'title_en' => 'X は Y という意味です (explaining a meaning)',
                'pattern' => 'Ｘは Ｙと いう 意味《いみ》です',
                'payload' => [
                    'explanation_id' => 'Pola ini mendefinisikan arti X: "X artinya Y". という berasal dari と いいます dan di sini berarti "yang disebut / yang berarti". Untuk menanyakan arti dipakai どういう: Ｘは どういう 意味《いみ》ですか。 ("Apa maksud X?"). Y berupa kalimat dalam bentuk biasa. Sangat berguna untuk menanyakan arti tanda, tulisan, dan ungkapan yang baru kamu temui.',
                    'explanation_en' => 'This pattern defines the meaning of X: "X means Y". という comes from と いいます and here means "called / meaning". To ask for a meaning use どういう: Ｘは どういう 意味《いみ》ですか。 ("What does X mean?"). Y is a sentence in plain form. Very handy for asking about signs, writing and expressions you have just met.',
                    'notes_id' => [
                        'Y memakai bentuk biasa: 入《はい》るな, 洗《あら》える, 禁煙《きんえん》だ.',
                        'Dibandingkan dengan 〜と 書《か》いて あります (isi tulisan), pola ini menjelaskan maksud di balik tulisan itu.',
                    ],
                    'notes_en' => [
                        'Y takes the plain form: 入《はい》るな, 洗《あら》える, 禁煙《きんえん》だ.',
                        'Compared with 〜と 書《か》いて あります (what it says), this pattern explains the meaning behind the writing.',
                    ],
                    'examples' => [
                        ['ja' => '「立入禁止《たちいりきんし》」は 入《はい》るなと いう 意味《いみ》です。', 'reading' => '"Tachiiri kinshi" wa hairu na to iu imi desu.', 'id' => '"Tachiiri kinshi" artinya dilarang masuk.', 'en' => '"Tachiiri kinshi" means "no entry".'],
                        ['ja' => '「徐行《じょこう》」は ゆっくり 行《い》くと いう 意味《いみ》です。', 'reading' => '"Jokou" wa yukkuri iku to iu imi desu.', 'id' => '"Jokou" artinya berjalan perlahan.', 'en' => '"Jokou" means "go slowly".'],
                        ['ja' => '「無料《むりょう》」は お金《かね》が いらないと いう 意味《いみ》です。', 'reading' => '"Muryou" wa okane ga iranai to iu imi desu.', 'id' => '"Muryou" artinya tidak perlu bayar.', 'en' => '"Muryou" means no money is needed.'],
                        ['ja' => 'この マークは どういう 意味《いみ》ですか。……手《て》で 洗《あら》うと いう 意味《いみ》です。', 'reading' => 'Kono maaku wa douiu imi desu ka. ...Te de arau to iu imi desu.', 'id' => 'Apa maksud tanda ini? ……Maksudnya dicuci dengan tangan.', 'en' => 'What does this mark mean? ...It means wash by hand.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di toko pakaian',
                        'title_en' => 'At a clothes shop',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'この マークは どういう 意味《いみ》ですか。', 'reading' => 'Kono maaku wa douiu imi desu ka.', 'id' => 'Apa maksud tanda ini?', 'en' => 'What does this mark mean?'],
                            ['speaker' => 'てんいん', 'ja' => '低《ひく》い 温度《おんど》で アイロンを かけると いう 意味《いみ》です。', 'reading' => 'Hikui ondo de airon o kakeru to iu imi desu.', 'id' => 'Maksudnya disetrika dengan suhu rendah.', 'en' => 'It means iron at a low temperature.'],
                            ['speaker' => 'サリ', 'ja' => 'そうですか。ありがとう ございます。', 'reading' => 'Sou desu ka. Arigatou gozaimasu.', 'id' => 'Begitu, ya. Terima kasih.', 'en' => 'I see. Thank you.'],
                        ],
                    ],
                ],
            ],

            // 6 ------------------------------------------------------------
            [
                'title_id' => '〜と言っていました (menyampaikan ucapan orang lain)',
                'title_en' => '〜と言っていました (relaying what someone said)',
                'pattern' => '「〜」／ふつうけい と 言《い》って いました',
                'payload' => [
                    'explanation_id' => 'Untuk mengutip ucapan orang ketiga dipakai 〜と 言《い》いました (hanya melaporkan bahwa orang itu mengucapkannya), sedangkan untuk menyampaikan isi ucapannya kepada lawan bicara dipakai 〜と 言《い》って いました: "Katanya …". Isi kutipan boleh berupa kalimat langsung dalam 「 」 atau bentuk biasa tanpa kurung, lalu と 言《い》って いました.',
                    'explanation_en' => 'To quote a third person with 〜と 言《い》いました you simply report that they said it, while 〜と 言《い》って いました is used to pass on what they said to your listener: "They said …". The content can be a direct sentence in 「 」 or a plain-form sentence without quotation marks, followed by と 言《い》って いました.',
                    'notes_id' => [
                        'Kalimat langsung: 田中《たなか》さんは 「あした 休《やす》みます」と 言《い》って いました. Kalimat tidak langsung: 田中《たなか》さんは あした 休《やす》むと 言《い》って いました.',
                        'Jika dilaporkan secara tidak langsung, ます diubah menjadi bentuk biasa: 休《やす》みます → 休《やす》む.',
                    ],
                    'notes_en' => [
                        'Direct: 田中《たなか》さんは 「あした 休《やす》みます」と 言《い》って いました. Indirect: 田中《たなか》さんは あした 休《やす》むと 言《い》って いました.',
                        'When reporting indirectly, ます becomes the plain form: 休《やす》みます → 休《やす》む.',
                    ],
                    'examples' => [
                        ['ja' => '山田《やまだ》さんは 来週《らいしゅう》 大阪《おおさか》へ 行《い》くと 言《い》って いました。', 'reading' => 'Yamada-san wa raishuu Oosaka e iku to itte imashita.', 'id' => 'Pak/Bu Yamada bilang bahwa minggu depan beliau ke Osaka.', 'en' => 'Mr./Ms. Yamada said they are going to Osaka next week.'],
                        ['ja' => '先生《せんせい》は あした テストが あると 言《い》って いました。', 'reading' => 'Sensei wa ashita tesuto ga aru to itte imashita.', 'id' => 'Guru bilang besok ada ujian.', 'en' => 'The teacher said there is a test tomorrow.'],
                        ['ja' => 'サリさんは 今日《きょう》 来《こ》ないと 言《い》って いました。', 'reading' => 'Sari-san wa kyou konai to itte imashita.', 'id' => 'Sari bilang hari ini dia tidak datang.', 'en' => 'Sari said she is not coming today.'],
                        ['ja' => '田中《たなか》さんは 「パーティーは 六時《ろくじ》からです」と 言《い》って いました。', 'reading' => 'Tanaka-san wa "paatii wa rokuji kara desu" to itte imashita.', 'id' => 'Pak/Bu Tanaka bilang "Pestanya mulai jam enam".', 'en' => 'Mr./Ms. Tanaka said "The party starts at six".'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mencari Pak Tanaka',
                        'title_en' => 'Looking for Mr. Tanaka',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'たなかさんは どこですか。', 'reading' => 'Tanaka-san wa doko desu ka.', 'id' => 'Pak Tanaka di mana?', 'en' => 'Where is Mr. Tanaka?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'さっき 出《で》かけました。三十分《さんじゅっぷん》ぐらいで 戻《もど》ると 言《い》って いました。', 'reading' => 'Sakki dekakemashita. Sanjuppun gurai de modoru to itte imashita.', 'id' => 'Tadi beliau keluar. Katanya kembali sekitar tiga puluh menit lagi.', 'en' => 'He went out a moment ago. He said he would be back in about thirty minutes.'],
                            ['speaker' => 'サリ', 'ja' => 'そうですか。じゃ、また 来《き》ます。', 'reading' => 'Sou desu ka. Ja, mata kimasu.', 'id' => 'Begitu, ya. Kalau begitu saya datang lagi nanti.', 'en' => 'I see. Then I will come again later.'],
                        ],
                    ],
                ],
            ],

            // 7 ------------------------------------------------------------
            [
                'title_id' => '〜と伝えていただけませんか (menitip pesan dengan sopan)',
                'title_en' => '〜と伝えていただけませんか (asking someone to pass on a message politely)',
                'pattern' => '「〜」／ふつうけい と 伝《つた》えて いただけませんか',
                'payload' => [
                    'explanation_id' => 'Dipakai untuk menitip pesan kepada seseorang secara halus: "Tolong sampaikan kepada … bahwa …". Orang yang dituju ditandai に, isi pesannya diletakkan sebelum と, dan 伝《つた》えて いただけませんか adalah bentuk permintaan yang sopan. Kalimat pesan memakai bentuk biasa (kata benda dan kata sifat な memakai だ): 六時《ろくじ》からだ.',
                    'explanation_en' => 'Used to leave a message for someone in a polite way: "Could you please tell … that …". The person to receive the message is marked with に, the message goes before と, and 伝《つた》えて いただけませんか is the polite request. The message uses the plain form (nouns and な-adjectives take だ): 六時《ろくじ》からだ.',
                    'notes_id' => [
                        'Kurang formal: 〜と 伝《つた》えて ください. Lebih sopan lagi: 〜と お伝《つた》え ください.',
                        'Pesan boleh berupa kalimat langsung dalam 「 」: ワンさんに 「あとで 電話《でんわ》を ください」と 伝《つた》えて いただけませんか。',
                        'Kosakata yang sering muncul bersama pola ini: 会議《かいぎ》に 出席《しゅっせき》します (menghadiri rapat), 締《し》め切《き》り (batas waktu).',
                    ],
                    'notes_en' => [
                        'Less formal: 〜と 伝《つた》えて ください. More polite: 〜と お伝《つた》え ください.',
                        'The message can be a direct quotation in 「 」: ワンさんに 「あとで 電話《でんわ》を ください」と 伝《つた》えて いただけませんか。',
                        'Vocabulary often seen with this pattern: 会議《かいぎ》に 出席《しゅっせき》します (attend a meeting), 締《し》め切《き》り (deadline).',
                    ],
                    'examples' => [
                        ['ja' => 'すみませんが、山田《やまだ》さんに 会議《かいぎ》は 三時《さんじ》からだと 伝《つた》えて いただけませんか。', 'reading' => 'Sumimasen ga, Yamada-san ni kaigi wa sanji kara da to tsutaete itadakemasen ka.', 'id' => 'Maaf, tolong sampaikan kepada Pak/Bu Yamada bahwa rapat mulai jam tiga.', 'en' => 'Excuse me, could you tell Mr./Ms. Yamada that the meeting starts at three?'],
                        ['ja' => 'ワンさんに 「あとで 電話《でんわ》を ください」と 伝《つた》えて いただけませんか。', 'reading' => 'Wan-san ni "ato de denwa o kudasai" to tsutaete itadakemasen ka.', 'id' => 'Tolong sampaikan kepada Wang bahwa "Minta ditelepon nanti".', 'en' => 'Could you tell Wang "Please call me later"?'],
                        ['ja' => 'たなかさんに 今日《きょう》は 少《すこ》し 遅《おく》れると 伝《つた》えて いただけませんか。', 'reading' => 'Tanaka-san ni kyou wa sukoshi okureru to tsutaete itadakemasen ka.', 'id' => 'Tolong sampaikan kepada Pak Tanaka bahwa hari ini saya agak terlambat.', 'en' => 'Could you tell Mr. Tanaka that I will be a little late today?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menitip pesan lewat telepon',
                        'title_en' => 'Leaving a message by phone',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'もしもし、ワヒュです。たなかさんは いらっしゃいますか。', 'reading' => 'Moshimoshi, Wahyu desu. Tanaka-san wa irasshaimasu ka.', 'id' => 'Halo, saya Wahyu. Apakah Pak Tanaka ada?', 'en' => 'Hello, this is Wahyu. Is Mr. Tanaka in?'],
                            ['speaker' => 'うけつけ', 'ja' => 'ただいま 出《で》かけて おります。', 'reading' => 'Tadaima dekakete orimasu.', 'id' => 'Beliau sedang keluar.', 'en' => 'He is out at the moment.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'では、あしたの 会議《かいぎ》は 九時《くじ》からだと 伝《つた》えて いただけませんか。', 'reading' => 'Dewa, ashita no kaigi wa kuji kara da to tsutaete itadakemasen ka.', 'id' => 'Kalau begitu, tolong sampaikan bahwa rapat besok mulai jam sembilan.', 'en' => 'In that case, could you tell him that the meeting tomorrow starts at nine?'],
                            ['speaker' => 'うけつけ', 'ja' => 'はい、わかりました。', 'reading' => 'Hai, wakarimashita.', 'id' => 'Baik, saya mengerti.', 'en' => 'Certainly, understood.'],
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
            ['slug' => 'n4-pelajaran-8'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 8',
                'name_en' => 'N4 Lesson 8 Vocabulary',
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
            ['逃げます', 'にげます', 'nigemasu', 'berlari, melarikan diri', 'to run away, to escape'],
            ['騒ぎます', 'さわぎます', 'sawagimasu', 'menghebohkan, membuat gaduh', 'to make a fuss, to make noise'],
            ['あきらめます', 'あきらめます', 'akiramemasu', 'putus asa, menyerah', 'to give up'],
            ['投げます', 'なげます', 'nagemasu', 'melempar', 'to throw'],
            ['守ります', 'まもります', 'mamorimasu', 'menjaga, menaati', 'to protect, to keep [a rule]'],
            ['始まります', 'はじまります', 'hajimarimasu', '[upacara] dimulai', '[a ceremony] begins'],
            ['出席します', 'しゅっせきします', 'shussekishimasu', 'menghadiri [rapat]', 'to attend [a meeting]'],
            ['伝えます', 'つたえます', 'tsutaemasu', 'menyampaikan', 'to convey, to pass on'],
            ['注意します', 'ちゅういします', 'chuuishimasu', 'berhati-hati [pada mobil]', 'to be careful [of cars]'],
            ['外します', 'はずします', 'hazushimasu', 'meninggalkan [tempat]', 'to be away from [a seat]'],
            ['戻ります', 'もどります', 'modorimasu', 'kembali', 'to return, to go back'],
            ['あります', 'あります', 'arimasu', 'ada [telepon]', 'to have [a phone call]'],
            ['リサイクルします', 'リサイクルします', 'risaikuru shimasu', 'mendaur ulang', 'to recycle'],
            ['だめ[な]', 'だめ[な]', 'dame', 'tidak baik, tidak boleh', 'no good, not allowed'],
            ['同じ', 'おなじ', 'onaji', 'sama', 'the same'],
            ['警察', 'けいさつ', 'keisatsu', 'polisi', 'police'],
            ['席', 'せき', 'seki', 'tempat, kursi', 'seat'],
            ['マーク', 'マーク', 'maaku', 'tanda, cap', 'mark, symbol'],
            ['ボール', 'ボール', 'booru', 'bola', 'ball'],
            ['締め切り', 'しめきり', 'shimekiri', 'batas waktu', 'deadline'],
            ['規則', 'きそく', 'kisoku', 'peraturan', 'rule, regulation'],
            ['危険', 'きけん', 'kiken', 'bahaya', 'danger'],
            ['使用禁止', 'しようきんし', 'shiyou kinshi', 'dilarang pakai', 'use prohibited'],
            ['立入禁止', 'たちいりきんし', 'tachiiri kinshi', 'dilarang masuk', 'no entry'],
            ['徐行', 'じょこう', 'jokou', 'berjalan perlahan-lahan', 'slow down, go slowly'],
            ['入口', 'いりぐち', 'iriguchi', 'pintu masuk', 'entrance'],
            ['出口', 'でぐち', 'deguchi', 'pintu keluar', 'exit'],
            ['非常口', 'ひじょうぐち', 'hijouguchi', 'pintu darurat', 'emergency exit'],
            ['無料', 'むりょう', 'muryou', 'gratis, cuma-cuma', 'free of charge'],
            ['割引', 'わりびき', 'waribiki', 'diskon', 'discount'],
            ['飲み放題', 'のみほうだい', 'nomihoudai', 'minum sepuasnya', 'all-you-can-drink'],
            ['使用中', 'しようちゅう', 'shiyouchuu', 'sedang dipakai', 'in use'],
            ['募集中', 'ぼしゅうちゅう', 'boshuuchuu', 'sedang dicari', 'now recruiting'],
            ['〜中', '〜ちゅう', 'chuu', 'sedang ~', 'in the middle of ~'],
            ['どういう〜', 'どういう〜', 'douiu', 'bagaimana ~', 'what kind of ~'],
            ['いくら[〜ても]', 'いくら[〜ても]', 'ikura', 'bagaimana pun, seberapa pun', 'no matter how much'],
            ['もう', 'もう', 'mou', 'lagi (dipakai dengan bentuk negatif)', 'any more (used with negative)'],
            ['あと〜', 'あと〜', 'ato', 'sisa ~', 'remaining ~'],
            ['〜ほど', '〜ほど', 'hodo', 'kira-kira ~', 'about ~'],

            // 会話 (percakapan)
            ['駐車違反', 'ちゅうしゃいはん', 'chuusha ihan', 'pelanggaran lalu lintas (parkir)', 'parking violation'],
            ['罰金', 'ばっきん', 'bakkin', 'denda', 'fine, penalty'],

            // 読み物 (bacaan)
            ['地震', 'じしん', 'jishin', 'gempa bumi', 'earthquake'],
            ['起きます', 'おきます', 'okimasu', 'terjadi', 'to happen, to occur'],
            ['助け合います', 'たすけあいます', 'tasukeaimasu', 'bantu-membantu', 'to help each other'],
            ['もともと', 'もともと', 'motomoto', 'memang, sejak semula', 'originally, by nature'],
            ['悲しい', 'かなしい', 'kanashii', 'sedih', 'sad'],
            ['もっと', 'もっと', 'motto', 'lebih', 'more'],
            ['あいさつ', 'あいさつ', 'aisatsu', 'salam (〜を します: memberi salam sepatah kata pada)', 'greeting (〜を します: to give a greeting)'],
            ['相手', 'あいて', 'aite', 'kawan, pasangan, lawan bicara', 'partner, the other person'],
            ['気持ち', 'きもち', 'kimochi', 'perasaan', 'feeling'],
        ];
    }
}
