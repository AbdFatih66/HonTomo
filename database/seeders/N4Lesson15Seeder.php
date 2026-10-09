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

class N4Lesson15Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 15: "Mengecek dan Mencoba" — kalimat tanya yang
     * disisipkan ke dalam kalimat lain, 〜てみます, kata benda dari kata sifat い
     * (〜さ) dan 〜でしょうか.
     *
     * Isi:
     *   - Level N4, Unit order 15 ("Pelajaran 15"). Pelajaran 2-5 dan 7-14 N4
     *     belum ada; Unit.order hanya perlu unik per level, jadi celah aman.
     *   - Lesson Bunpou (order 0, category grammar) dengan 5 kartu:
     *       1. Kata tanya ＋ か (kalimat tanya sisipan)
     *       2. 〜か どうか
     *       3. Vて みます
     *       4. Kata sifat い → 〜さ
     *       5. 〜でしょうか
     *   - Kategori kosakata "n4-pelajaran-15" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Referensi Tata Bahasa (ringkasan kalimat tanya sisipan + ukuran, garis,
     * bentuk, corak) ada di resources/js/pages/lampiran/index.vue.
     *
     * Nama diri (kota, maskapai, perayaan, kuil) sengaja tidak dimasukkan.
     * Penjelasan, contoh kalimat dan dialog ditulis baru untuk aplikasi ini.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson15Seeder
     *
     * Kata kunci pencarian SQL memakai ASCII (kategori.slug + romaji).
     */
    public function run(): void
    {
        $level = Level::where('code', 'N4')->first();

        if (! $level) {
            // Level N4 dibuat oleh seeder Pelajaran 1.
            $this->call(N4Lesson1Seeder::class);
            $level = Level::where('code', 'N4')->firstOrFail();
        }

        $unit = Unit::updateOrCreate(
            ['level_id' => $level->id, 'order' => 15],
            [
                'title_id' => 'Pelajaran 15: Mengecek dan Mencoba',
                'title_en' => 'Lesson 15: Checking and Trying',
                'description_id' => 'Kalimat tanya sisipan (〜か, 〜かどうか), 〜てみます, kata benda 〜さ, 〜でしょうか.',
                'description_en' => 'Embedded questions (〜か, 〜かどうか), 〜てみます, the 〜さ noun, 〜でしょうか.',
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
                'title_id' => 'Kata tanya ＋ か (kalimat tanya sisipan)',
                'title_en' => 'Question word ＋ か (embedded question)',
                'pattern' => 'ふつうけい ＋ か、〜',
                'payload' => [
                    'explanation_id' => 'Kalimat tanya yang memakai kata tanya (何《なに》, いつ, どこ, だれ, どう, いくら …) bisa dimasukkan sebagai bagian dari kalimat lain. Bagian pertanyaan memakai bentuk biasa lalu diakhiri か, dan kalimat induknya biasanya berisi kata kerja seperti 知《し》っていますか, 教《おし》えて ください, 調《しら》べて ください, 覚《おぼ》えていますか atau わかりません. Pada kata benda dan kata sifat な, だ dihilangkan sebelum か.',
                    'explanation_en' => 'A question that uses a question word (何《なに》, いつ, どこ, だれ, どう, いくら …) can be placed inside another sentence. The question part uses the plain form followed by か, and the main clause usually has a verb such as 知《し》っていますか, 教《おし》えて ください, 調《しら》べて ください, 覚《おぼ》えていますか or わかりません. After a noun or な-adjective, だ is dropped before か.',
                    'notes_id' => [
                        'Kata tanya tetap dipertahankan di dalam kalimat: 何時《なんじ》に 始《はじ》まるか (pukul berapa dimulai).',
                        'Jangan tertukar dengan 何《なに》か = "sesuatu": 何《なに》か 食《た》べました (makan sesuatu) berbeda dari 何《なに》を 食《た》べたか 覚《おぼ》えて います (ingat apa yang dimakan).',
                        'Pertanyaan sisipan tidak memakai ですか; bagian itu memakai bentuk biasa, sedangkan kesopanan ditentukan oleh akhir kalimat induk.',
                    ],
                    'notes_en' => [
                        'The question word stays inside the clause: 何時《なんじ》に 始《はじ》まるか (what time it starts).',
                        'Do not mix it up with 何《なに》か = "something": 何《なに》か 食《た》べました (I ate something) is different from 何《なに》を 食《た》べたか 覚《おぼ》えて います (I remember what I ate).',
                        'The embedded question does not use ですか; that part is in plain form, and politeness is set by the end of the main clause.',
                    ],
                    'examples' => [
                        ['ja' => '会議《かいぎ》は 何時《なんじ》に 始《はじ》まるか、教《おし》えて ください。', 'reading' => 'Kaigi wa nanji ni hajimaru ka, oshiete kudasai.', 'id' => 'Tolong beri tahu rapat dimulai pukul berapa.', 'en' => 'Please tell me what time the meeting starts.'],
                        ['ja' => '駅《えき》は どこに あるか、知《し》って いますか。', 'reading' => 'Eki wa doko ni aru ka, shitte imasu ka.', 'id' => 'Apakah Anda tahu stasiunnya ada di mana?', 'en' => 'Do you know where the station is?'],
                        ['ja' => 'どの 大学《だいがく》が いいか、先生《せんせい》に 相談《そうだん》しました。', 'reading' => 'Dono daigaku ga ii ka, sensei ni soudan shimashita.', 'id' => 'Saya berkonsultasi dengan guru tentang universitas mana yang baik.', 'en' => 'I consulted my teacher about which university is good.'],
                        ['ja' => 'この 荷物《にもつ》が いくらか、確《たし》かめて ください。', 'reading' => 'Kono nimotsu ga ikura ka, tashikamete kudasai.', 'id' => 'Tolong cek berapa harga barang ini.', 'en' => 'Please check how much this package is.'],
                        ['ja' => 'あの 人《ひと》が だれか、覚《おぼ》えて いますか。', 'reading' => 'Ano hito ga dare ka, oboete imasu ka.', 'id' => 'Apakah Anda ingat siapa orang itu?', 'en' => 'Do you remember who that person is?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menanyakan jadwal pesta',
                        'title_en' => 'Asking about the party',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'たなかさん、新年会《しんねんかい》は どこで あるか、知《し》って いますか。', 'reading' => 'Tanaka-san, shinnenkai wa doko de aru ka, shitte imasu ka.', 'id' => 'Pak/Bu Tanaka, apakah Anda tahu pesta tahun baru diadakan di mana?', 'en' => 'Mr./Ms. Tanaka, do you know where the New Year party is?'],
                            ['speaker' => 'たなか', 'ja' => 'さあ、わかりません。何時《なんじ》に 始《はじ》まるかも わかりません。', 'reading' => 'Saa, wakarimasen. Nanji ni hajimaru ka mo wakarimasen.', 'id' => 'Wah, saya tidak tahu. Mulai pukul berapa pun saya tidak tahu.', 'en' => 'Hmm, I do not know. I do not know what time it starts either.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'じゃ、課長《かちょう》に 聞《き》いて みます。', 'reading' => 'Ja, kachou ni kiite mimasu.', 'id' => 'Kalau begitu, saya coba tanya kepala bagian.', 'en' => 'Then I will try asking the section manager.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => '〜か どうか (ya atau tidak)',
                'title_en' => '〜か どうか (whether or not)',
                'pattern' => 'ふつうけい ＋ か どうか、〜',
                'payload' => [
                    'explanation_id' => 'Dipakai ketika pertanyaan yang disisipkan tidak memakai kata tanya, yaitu pertanyaan jawabannya "ya atau tidak": "apakah … atau tidak". Bagian pertanyaan memakai bentuk biasa, lalu か どうか. Bedanya dengan kalimat tanya sisipan pada kartu sebelumnya, di sini どうか wajib ditambahkan di belakang か. Kata benda dan kata sifat な juga kehilangan だ.',
                    'explanation_en' => 'Used when the embedded question has no question word, so the answer is "yes or no": "whether … or not". The question part is in plain form, followed by か どうか. Unlike the embedded question on the previous card, どうか must be added after か here. Nouns and な-adjectives also drop だ.',
                    'notes_id' => [
                        'Kalimat induk yang umum: 教《おし》えて ください, 調《しら》べて ください, 確《たし》かめて ください, わかりません, 知《し》って いますか.',
                        '〜ないか どうか dipakai ketika pembicara ingin memastikan sesuatu tidak ada, misalnya kesalahan: 間違《まちが》いが ないか どうか、見《み》て ください.',
                    ],
                    'notes_en' => [
                        'Common main clauses: 教《おし》えて ください, 調《しら》べて ください, 確《たし》かめて ください, わかりません, 知《し》って いますか.',
                        '〜ないか どうか is used when the speaker wants to make sure something is absent, such as a mistake: 間違《まちが》いが ないか どうか、見《み》て ください.',
                    ],
                    'examples' => [
                        ['ja' => '新年会《しんねんかい》に 来《く》るか どうか、金曜日《きんようび》までに 教《おし》えて ください。', 'reading' => 'Shinnenkai ni kuru ka dou ka, kinyoubi made ni oshiete kudasai.', 'id' => 'Tolong beri tahu sebelum Jumat apakah Anda datang ke pesta tahun baru atau tidak.', 'en' => 'Please let me know by Friday whether you are coming to the New Year party.'],
                        ['ja' => 'この 魚《さかな》が 新《あたら》しいか どうか、わかりません。', 'reading' => 'Kono sakana ga atarashii ka dou ka, wakarimasen.', 'id' => 'Saya tidak tahu apakah ikan ini segar atau tidak.', 'en' => 'I do not know whether this fish is fresh.'],
                        ['ja' => '電車《でんしゃ》が 動《うご》いて いるか どうか、駅《えき》で 確《たし》かめます。', 'reading' => 'Densha ga ugoite iru ka dou ka, eki de tashikamemasu.', 'id' => 'Saya akan mengecek di stasiun apakah kereta berjalan atau tidak.', 'en' => 'I will check at the station whether the trains are running.'],
                        ['ja' => '作文《さくぶん》に 間違《まちが》いが ないか どうか、見《み》て ください。', 'reading' => 'Sakubun ni machigai ga nai ka dou ka, mite kudasai.', 'id' => 'Tolong periksa apakah ada kesalahan di karangan saya atau tidak.', 'en' => 'Please look over my essay for mistakes.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Memeriksa dokumen',
                        'title_en' => 'Checking a document',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません。この 書類《しょるい》に 間違《まちが》いが ないか どうか、見《み》て いただけませんか。', 'reading' => 'Sumimasen. Kono shorui ni machigai ga nai ka dou ka, mite itadakemasen ka.', 'id' => 'Permisi. Bisakah Anda memeriksa apakah ada kesalahan di dokumen ini?', 'en' => 'Excuse me. Could you check whether there are any mistakes in this document?'],
                            ['speaker' => 'たなか', 'ja' => 'はい、いいですよ。……ここが 少《すこ》し 違《ちが》いますね。', 'reading' => 'Hai, ii desu yo. ...Koko ga sukoshi chigaimasu ne.', 'id' => 'Ya, boleh. ……Bagian ini sedikit berbeda, ya.', 'en' => 'Sure. ...This part is a little off.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ありがとうございます。直《なお》します。', 'reading' => 'Arigatou gozaimasu. Naoshimasu.', 'id' => 'Terima kasih. Saya perbaiki.', 'en' => 'Thank you. I will fix it.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => 'Vて みます (mencoba)',
                'title_en' => 'Vて みます (trying something)',
                'pattern' => 'Ｖて みます',
                'payload' => [
                    'explanation_id' => 'Menyatakan melakukan sesuatu sebagai percobaan, lalu melihat hasilnya: "mencoba …". Bentuknya: kata kerja bentuk-て + みます. Pola ini berkonjugasi seperti 見《み》ます biasa: 〜て みました, 〜て みたいです (ingin mencoba), 〜て みても いいですか (bolehkah saya mencoba), 〜て みましょう (mari kita coba). 〜て みたいです terdengar lebih lunak dan tidak langsung dibanding 〜たいです.',
                    'explanation_en' => 'Means doing something as a trial and seeing the result: "try …". Form: verb て-form + みます. It conjugates like 見《み》ます: 〜て みました, 〜て みたいです (want to try), 〜て みても いいですか (may I try), 〜て みましょう (let us try). 〜て みたいです sounds softer and less direct than 〜たいです.',
                    'notes_id' => [
                        'Maknanya "melakukan lalu melihat hasilnya", bukan sekadar melihat. Pada 〜て みます, 見《み》ます kehilangan arti "melihat" dan ditulis dalam kana.',
                        'Pas untuk makanan, pakaian, dan tempat baru: 食《た》べて みます, 着《き》て みます, 行《い》って みます.',
                    ],
                    'notes_en' => [
                        'The meaning is "do it and see how it turns out", not just looking. In 〜て みます, みます loses the meaning of "to see" and is written in kana.',
                        'It suits food, clothes and new places: 食《た》べて みます, 着《き》て みます, 行《い》って みます.',
                    ],
                    'examples' => [
                        ['ja' => 'この 靴《くつ》を はいて みても いいですか。', 'reading' => 'Kono kutsu o haite mite mo ii desu ka.', 'id' => 'Bolehkah saya mencoba memakai sepatu ini?', 'en' => 'May I try these shoes on?'],
                        ['ja' => 'もう 一度《いちど》 やって みます。', 'reading' => 'Mou ichido yatte mimasu.', 'id' => 'Saya coba lakukan sekali lagi.', 'en' => 'I will try doing it once more.'],
                        ['ja' => '京都《きょうと》の お寺《てら》を 見《み》て みたいです。', 'reading' => 'Kyouto no otera o mite mitai desu.', 'id' => 'Saya ingin mencoba mengunjungi kuil di Kyoto.', 'en' => 'I would like to see the temples of Kyoto.'],
                        ['ja' => 'この スープを 飲《の》んで みて ください。', 'reading' => 'Kono suupu o nonde mite kudasai.', 'id' => 'Tolong coba minum sup ini.', 'en' => 'Please try this soup.'],
                        ['ja' => '新《あたら》しい 店《みせ》で 食《た》べて みましょう。', 'reading' => 'Atarashii mise de tabete mimashou.', 'id' => 'Mari kita coba makan di toko yang baru.', 'en' => 'Let us try eating at the new restaurant.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di toko pakaian',
                        'title_en' => 'At a clothes shop',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'この ズボン、いいですね。はいて みても いいですか。', 'reading' => 'Kono zubon, ii desu ne. Haite mite mo ii desu ka.', 'id' => 'Celana ini bagus. Boleh saya coba?', 'en' => 'These trousers are nice. May I try them on?'],
                            ['speaker' => '店員', 'ja' => 'はい、どうぞ。あちらで はいて みて ください。', 'reading' => 'Hai, douzo. Achira de haite mite kudasai.', 'id' => 'Silakan. Coba pakai di sebelah sana.', 'en' => 'Please. Try them on over there.'],
                            ['speaker' => 'サリ', 'ja' => 'サイズが 合《あ》いました。これを ください。', 'reading' => 'Saizu ga aimashita. Kore o kudasai.', 'id' => 'Ukurannya cocok. Saya ambil yang ini.', 'en' => 'The size fits. I will take these.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'Kata sifat い → 〜さ (kata benda)',
                'title_en' => 'い-adjective → 〜さ (making a noun)',
                'pattern' => 'Ａい → Ａ(い→さ)',
                'payload' => [
                    'explanation_id' => 'Kata sifat い bisa diubah menjadi kata benda yang menyatakan tingkat atau ukuran dengan mengganti い di akhir dengan さ: 高《たか》い → 高《たか》さ ("tingginya"), 長《なが》い → 長《なが》さ ("panjangnya"), 重《おも》い → 重《おも》さ ("beratnya"). Kata benda ini dipakai dengan の dan は seperti kata benda biasa, misalnya 山《やま》の 高《たか》さ.',
                    'explanation_en' => 'An い-adjective becomes a noun expressing degree or measure when its final い is replaced by さ: 高《たか》い → 高《たか》さ ("height"), 長《なが》い → 長《なが》さ ("length"), 重《おも》い → 重《おも》さ ("weight"). The noun is used with の and は like any noun, for example 山《やま》の 高《たか》さ.',
                    'notes_id' => [
                        'Pasangan yang sering muncul: 高《たか》さ (tinggi), 長《なが》さ (panjang), 重《おも》さ (berat), 大《おお》きさ (besar), 速《はや》さ (kecepatan), 深《ふか》さ (kedalaman), 暑《あつ》さ (panasnya).',
                        'Pertanyaan: 〜の 高《たか》さは どのくらいですか／何《なん》メートルですか.',
                    ],
                    'notes_en' => [
                        'Frequent pairs: 高《たか》さ (height), 長《なが》さ (length), 重《おも》さ (weight), 大《おお》きさ (size), 速《はや》さ (speed), 深《ふか》さ (depth), 暑《あつ》さ (the heat).',
                        'Questions: 〜の 高《たか》さは どのくらいですか／何《なん》メートルですか.',
                    ],
                    'examples' => [
                        ['ja' => '山《やま》の 高《たか》さは どうやって 測《はか》りますか。', 'reading' => 'Yama no takasa wa dou yatte hakarimasu ka.', 'id' => 'Bagaimana cara mengukur tinggi gunung?', 'en' => 'How do you measure the height of a mountain?'],
                        ['ja' => 'この 川《かわ》の 長《なが》さは 何《なん》キロですか。', 'reading' => 'Kono kawa no nagasa wa nan kiro desu ka.', 'id' => 'Berapa kilometer panjang sungai ini?', 'en' => 'How many kilometres long is this river?'],
                        ['ja' => 'この かばんの 重《おも》さは 二《に》キロです。', 'reading' => 'Kono kaban no omosa wa ni kiro desu.', 'id' => 'Berat tas ini dua kilogram.', 'en' => 'This bag weighs two kilograms.'],
                        ['ja' => '部屋《へや》の 大《おお》きさを 測《はか》って ください。', 'reading' => 'Heya no ookisa o hakatte kudasai.', 'id' => 'Tolong ukur besar ruangan itu.', 'en' => 'Please measure the size of the room.'],
                        ['ja' => '日本《にほん》の 夏《なつ》の 暑《あつ》さに おどろきました。', 'reading' => 'Nihon no natsu no atsusa ni odorokimashita.', 'id' => 'Saya terkejut dengan panasnya musim panas di Jepang.', 'en' => 'I was surprised by the heat of the Japanese summer.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mengirim paket',
                        'title_en' => 'Sending a package',
                        'lines' => [
                            ['speaker' => '係《かかり》', 'ja' => '荷物《にもつ》の 大《おお》きさと 重《おも》さを 測《はか》りますね。', 'reading' => 'Nimotsu no ookisa to omosa o hakarimasu ne.', 'id' => 'Saya ukur besar dan berat paketnya, ya.', 'en' => 'I will measure the size and weight of the package.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'お願《ねが》いします。', 'reading' => 'Onegai shimasu.', 'id' => 'Silakan.', 'en' => 'Please do.'],
                            ['speaker' => '係《かかり》', 'ja' => '重《おも》さは 三《さん》キロです。', 'reading' => 'Omosa wa san kiro desu.', 'id' => 'Beratnya tiga kilogram.', 'en' => 'The weight is three kilograms.'],
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => '〜でしょうか (pertanyaan yang lembut)',
                'title_en' => '〜でしょうか (a softer question)',
                'pattern' => '〜でしょうか',
                'payload' => [
                    'explanation_id' => 'Dipakai pada kalimat tanya untuk menanyakan sesuatu dengan lebih halus daripada 〜ですか. Ungkapan ini tidak menuntut jawaban yang pasti, sehingga memberi kesan lembut kepada lawan bicara. Cocok untuk menanyakan kondisi seseorang, meminta pendapat, atau berbicara dengan orang yang dihormati, misalnya どうでしょうか ("bagaimana kira-kira?").',
                    'explanation_en' => 'Used in questions to ask more gently than 〜ですか. It does not demand a definite answer, so it gives a soft impression to the listener. It suits asking about someone situation, asking for an opinion, or speaking to someone you respect, as in どうでしょうか ("how is it, I wonder?").',
                    'notes_id' => [
                        'どうでしょうか adalah bentuk lebih sopan dari どうですか.',
                        'Bisa digabung dengan pola lain: これで いいでしょうか (apakah begini sudah baik?), どこに 置《お》いたら いいでしょうか.',
                    ],
                    'notes_en' => [
                        'どうでしょうか is a more polite version of どうですか.',
                        'It combines with other patterns: これで いいでしょうか (is this all right?), どこに 置《お》いたら いいでしょうか (where should I put it?).',
                    ],
                    'examples' => [
                        ['ja' => 'あしたの 天気《てんき》は どうでしょうか。', 'reading' => 'Ashita no tenki wa dou deshou ka.', 'id' => 'Kira-kira bagaimana cuaca besok?', 'en' => 'How will the weather be tomorrow, I wonder?'],
                        ['ja' => '先生《せんせい》、この 漢字《かんじ》の 読《よ》み方《かた》は これで いいでしょうか。', 'reading' => 'Sensei, kono kanji no yomikata wa kore de ii deshou ka.', 'id' => 'Pak/Bu guru, apakah cara membaca kanji ini sudah benar seperti ini?', 'en' => 'Teacher, is this the right way to read this kanji?'],
                        ['ja' => 'うちの 子《こ》は 学校《がっこう》で どうでしょうか。', 'reading' => 'Uchi no ko wa gakkou de dou deshou ka.', 'id' => 'Bagaimana kira-kira anak kami di sekolah?', 'en' => 'How is our child doing at school?'],
                        ['ja' => 'この 荷物《にもつ》は どこに 置《お》いたら いいでしょうか。', 'reading' => 'Kono nimotsu wa doko ni oitara ii deshou ka.', 'id' => 'Sebaiknya barang ini diletakkan di mana?', 'en' => 'Where should I put this package?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Bertanya kepada guru',
                        'title_en' => 'Asking a teacher',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '先生《せんせい》、うちの 子《こ》は 学校《がっこう》で どうでしょうか。', 'reading' => 'Sensei, uchi no ko wa gakkou de dou deshou ka.', 'id' => 'Bu/Pak guru, bagaimana kira-kira anak kami di sekolah?', 'en' => 'Teacher, how is our child doing at school?'],
                            ['speaker' => '先生', 'ja' => '元気《げんき》ですよ。友達《ともだち》も たくさん いますよ。', 'reading' => 'Genki desu yo. Tomodachi mo takusan imasu yo.', 'id' => 'Dia sehat dan ceria. Temannya juga banyak.', 'en' => 'He is doing well. He has many friends too.'],
                            ['speaker' => 'たなか', 'ja' => 'そうですか。安心《あんしん》しました。', 'reading' => 'Sou desu ka. Anshin shimashita.', 'id' => 'Begitu. Saya lega.', 'en' => 'I see. That is a relief.'],
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
            ['slug' => 'n4-pelajaran-15'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 15',
                'name_en' => 'N4 Lesson 15 Vocabulary',
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
            ['数えます', 'かぞえます', 'kazoemasu', 'menghitung', 'to count'],
            ['測ります', 'はかります', 'hakarimasu', 'mengukur, menimbang', 'to measure, to weigh'],
            ['確かめます', 'たしかめます', 'tashikamemasu', 'mengecek', 'to check, to make sure'],
            ['サイズが合います', 'サイズがあいます', 'saizu ga aimasu', 'cocok [ukurannya]', 'to fit [the size]'],
            ['出発します', 'しゅっぱつします', 'shuppatsushimasu', 'berangkat', 'to depart'],
            ['到着します', 'とうちゃくします', 'touchakushimasu', 'tiba', 'to arrive'],
            ['酔います', 'よいます', 'yoimasu', 'mabuk', 'to get drunk'],
            ['うまく いきます', 'うまくいきます', 'umaku ikimasu', 'berhasil dengan baik', 'to go well'],
            ['問題が出ます', 'もんだいがでます', 'mondai ga demasu', 'diberikan [soal di ujian]', 'to be given [a question on an exam]'],
            ['相談します', 'そうだんします', 'soudanshimasu', 'berkonsultasi', 'to consult, to discuss'],
            ['必要[な]', 'ひつよう[な]', 'hitsuyou', 'perlu', 'necessary'],
            ['天気予報', 'てんきよほう', 'tenkiyohou', 'prakiraan cuaca', 'weather forecast'],
            ['忘年会', 'ぼうねんかい', 'bounenkai', 'pesta akhir tahun', 'year-end party'],
            ['新年会', 'しんねんかい', 'shinnenkai', 'pesta tahun baru', 'New Year party'],
            ['二次会', 'にじかい', 'nijikai', 'pesta untuk kedua kalinya', 'after-party, second party'],
            ['発表会', 'はっぴょうかい', 'happyoukai', 'acara presentasi', 'presentation event, recital'],
            ['大会', 'たいかい', 'taikai', 'lomba', 'tournament, competition'],
            ['マラソン', 'マラソン', 'marason', 'maraton', 'marathon'],
            ['コンテスト', 'コンテスト', 'kontesuto', 'pertandingan', 'contest'],
            ['表', 'おもて', 'omote', 'muka', 'front, face'],
            ['裏', 'うら', 'ura', 'belakang', 'back, reverse side'],
            ['間違い', 'まちがい', 'machigai', 'kesalahan', 'mistake'],
            ['傷', 'きず', 'kizu', 'cacat, luka', 'flaw, wound'],
            ['ズボン', 'ズボン', 'zubon', 'celana', 'trousers'],
            ['お年寄り', 'おとしより', 'otoshiyori', 'orang lanjut usia', 'elderly person'],
            ['長さ', 'ながさ', 'nagasa', 'panjangnya', 'length'],
            ['重さ', 'おもさ', 'omosa', 'beratnya', 'weight'],
            ['高さ', 'たかさ', 'takasa', 'tingginya', 'height'],
            ['大きさ', 'おおきさ', 'ookisa', 'besarnya', 'size'],
            ['〜便', '〜びん', 'bin', 'penerbangan nomor ~', 'flight number ~'],
            ['〜個', '〜こ', 'ko', 'buah (kata bantu bilangan untuk menghitung benda kecil)', 'counter for small items'],
            ['〜本', '〜ほん', 'hon', 'batang (kata bantu bilangan untuk menghitung benda yang kurus panjang)', 'counter for long, thin items'],
            ['〜杯', '〜はい', 'hai', 'cangkir, gelas (kata bantu bilangan untuk menghitung isi cangkir atau gelas dan lain-lain)', 'cupful, glassful (counter)'],
            ['〜センチ', '〜センチ', 'senchi', 'senti', 'centimetre'],
            ['〜ミリ', '〜ミリ', 'miri', 'mili', 'millimetre'],
            ['〜グラム', '〜グラム', 'guramu', 'gram', 'gram'],
            ['〜以上', '〜いじょう', 'ijou', 'ke atas ~', '~ or more, over ~'],
            ['〜以下', '〜いか', 'ika', 'ke bawah ~', '~ or less, under ~'],

            // 会話 (percakapan)
            ['どうでしょうか', 'どうでしょうか', 'dou deshou ka', 'bagaimana? (bentuk sopan dari どうですか)', 'how is it? (polite form of どうですか)'],
            ['テスト', 'テスト', 'tesuto', 'ujian', 'test'],
            ['成績', 'せいせき', 'seiseki', 'nilai', 'grades, results'],
            ['ところで', 'ところで', 'tokorode', 'ngomong-ngomong', 'by the way'],
            ['いらっしゃいます', 'いらっしゃいます', 'irasshaimasu', 'datang (bentuk hormat dari きます)', 'to come (honorific of きます)'],
            ['様子', 'ようす', 'yousu', 'kondisi, suasana', 'state, appearance'],

            // 読み物 (bacaan)
            ['事件', 'じけん', 'jiken', 'kejadian', 'incident, case'],
            ['オートバイ', 'オートバイ', 'ootobai', 'sepeda motor', 'motorcycle'],
            ['爆弾', 'ばくだん', 'bakudan', 'bom', 'bomb'],
            ['積みます', 'つみます', 'tsumimasu', 'memuatkan', 'to load'],
            ['運転手', 'うんてんしゅ', 'untenshu', 'sopir', 'driver'],
            ['離れた', 'はなれた', 'hanareta', 'terpisah', 'distant, separated'],
            ['急に', 'きゅうに', 'kyuu ni', 'mendadak, tiba-tiba', 'suddenly'],
            ['動かします', 'うごかします', 'ugokashimasu', 'menggerakkan', 'to move, to operate'],
            ['一生懸命', 'いっしょうけんめい', 'isshoukenmei', 'sungguh-sungguh', 'with all one might'],
            ['犯人', 'はんにん', 'hannin', 'pelaku', 'criminal, culprit'],
            ['男', 'おとこ', 'otoko', 'laki-laki', 'man'],
            ['手に入れます', 'てにいれます', 'te ni iremasu', 'mendapatkan', 'to obtain'],
            ['今でも', 'いまでも', 'ima demo', 'sampai sekarang', 'even now'],
        ];
    }
}
