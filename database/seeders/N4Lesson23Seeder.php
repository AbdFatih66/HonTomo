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

class N4Lesson23Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 23 ("Boleh Saya Mengambil Cuti?"): kata kerja
     * kausatif. Level N4 berdiri di samping N5 dan dipilih lewat selektor
     * N5 / N4 di Tata Bahasa, Kosakata dan Referensi Tata Bahasa.
     *
     * Isi:
     *   - Level N4 (dibuat hanya kalau belum ada) dan Unit order 23
     *   - Lesson Bunpou (order 0, category grammar) dengan 5 kartu:
     *       1. Membentuk kata kerja kausatif
     *       2. Ｎ(orang)を ＋ kausatif (kata kerja intransitif)
     *       3. Ｎ１(orang)に Ｎ２を ＋ kausatif (kata kerja transitif)
     *       4. Makna paksaan／izin dan Vて もらいます／いただきます
     *       5. Vさせて いただけませんか (minta izin)
     *   - Kategori kosakata "n4-pelajaran-23" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus. Penjelasan, contoh
     * kalimat dan dialog ditulis baru untuk aplikasi ini.
     *
     * Referensi Tata Bahasa (halaman lampiran): bagian N4 "Kata Kerja
     * Kausatif" dan "Mendidik & Disiplin" di resources/js/pages/lampiran/index.vue.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson23Seeder
     *
     * Kata kunci pencarian SQL memakai ASCII (kategori.slug + romaji).
     */
    public function run(): void
    {
        $level = Level::firstOrCreate(
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
            ['level_id' => $level->id, 'order' => 23],
            [
                'title_id' => 'Pelajaran 23: Boleh Saya Mengambil Cuti?',
                'title_en' => 'Lesson 23: May I Take Some Leave?',
                'description_id' => 'Kata kerja kausatif: menyuruh dan mengizinkan orang melakukan sesuatu, serta 〜させて いただけませんか.',
                'description_en' => 'Causative verbs: making and letting someone do something, plus 〜させて いただけませんか.',
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
                'title_id' => 'Membentuk kata kerja kausatif',
                'title_en' => 'Forming causative verbs',
                'pattern' => 'Ｖ(ない形)＋せる／させる',
                'payload' => [
                    'explanation_id' => 'Kata kerja kausatif menyatakan \"menyuruh\" atau \"membiarkan\" orang lain melakukan sesuatu. Cara membentuknya: Kelompok I — ubah akhiran ke baris あ (bentuk-ない) lalu tambahkan せる (行《い》く → 行《い》かせる; 買《か》う → 買《か》わせる). Kelompok II — buang る lalu tambahkan させる (食《た》べる → 食《た》べさせる). Kelompok III — 来《く》る → 来《こ》させる, する → させる. Hasilnya selalu berperilaku seperti kata kerja Kelompok II: かかせます, かかせる, かかせない, かかせて.',
                    'explanation_en' => 'A causative verb means \"make\" or \"let\" someone do something. Formation: Group I — change the ending to the あ-row (ない-stem) and add せる (行《い》く → 行《い》かせる; 買《か》う → 買《か》わせる). Group II — drop る and add させる (食《た》べる → 食《た》べさせる). Group III — 来《く》る → 来《こ》させる, する → させる. The result always conjugates like a Group II verb: かかせます, かかせる, かかせない, かかせて.',
                    'notes_id' => [
                        'Kelompok I berakhiran う memakai わ sebelum せる: 買《か》う → 買《か》わせる, 習《なら》う → 習《なら》わせる.',
                        'Bentuk sopan: 〜せます／〜させます. Bentuk biasa: 〜せる／〜させる.',
                        'する dan kata benda する: 運動《うんどう》する → 運動《うんどう》させる.',
                    ],
                    'notes_en' => [
                        'Group I verbs ending in う take わ before せる: 買《か》う → 買《か》わせる, 習《なら》う → 習《なら》わせる.',
                        'Polite form: 〜せます／〜させます. Plain form: 〜せる／〜させる.',
                        'する and noun + する: 運動《うんどう》する → 運動《うんどう》させる.',
                    ],
                    'examples' => [
                        ['ja' => 'コーチは 選手《せんしゅ》を 毎日《まいにち》 走《はし》らせます。', 'reading' => 'Koochi wa senshu o mainichi hashirasemasu.', 'id' => 'Pelatih menyuruh para atlet berlari setiap hari.', 'en' => 'The coach makes the athletes run every day.'],
                        ['ja' => '母《はは》は 子《こ》どもに 野菜《やさい》を 食《た》べさせます。', 'reading' => 'Haha wa kodomo ni yasai o tabesasemasu.', 'id' => 'Ibu menyuruh anaknya makan sayur.', 'en' => 'Mother makes her child eat vegetables.'],
                        ['ja' => '会社《かいしゃ》は 社員《しゃいん》を 海外《かいがい》へ 出張《しゅっちょう》させます。', 'reading' => 'Kaisha wa shain o kaigai e shucchou sasemasu.', 'id' => 'Perusahaan mengirim karyawan dinas ke luar negeri.', 'en' => 'The company sends its employees on business trips abroad.'],
                        ['ja' => 'わたしは 弟《おとうと》に 部屋《へや》を 掃除《そうじ》させました。', 'reading' => 'Watashi wa otouto ni heya o souji sasemashita.', 'id' => 'Saya menyuruh adik membersihkan kamar.', 'en' => 'I made my younger brother clean the room.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Olahraga pagi anak',
                        'title_en' => 'The children morning run',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'ワヒュさんの お子《こ》さんは 毎朝《まいあさ》 何《なに》を して いますか。', 'reading' => 'Wahyu-san no okosan wa maiasa nani o shite imasu ka.', 'id' => 'Wahyu, anakmu setiap pagi melakukan apa?', 'en' => 'Wahyu, what does your child do every morning?'],
                            ['speaker' => 'ワヒュ', 'ja' => '公園《こうえん》を 走《はし》って います。わたしが 走《はし》らせて いるんです。', 'reading' => 'Kouen o hashitte imasu. Watashi ga hashirasete iru n desu.', 'id' => 'Dia berlari di taman. Saya yang menyuruhnya berlari.', 'en' => 'He runs in the park. I am the one who makes him run.'],
                            ['speaker' => 'サリ', 'ja' => 'いい ことですね。', 'reading' => 'Ii koto desu ne.', 'id' => 'Bagus, ya.', 'en' => 'That is good.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'でも、雨《あめ》の 日《ひ》は 休《やす》ませます。', 'reading' => 'Demo, ame no hi wa yasumasemasu.', 'id' => 'Tapi kalau hujan, saya membiarkannya libur.', 'en' => 'But on rainy days I let him rest.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => 'Ｎ(orang)を ＋ kausatif (kata kerja intransitif)',
                'title_en' => 'Ｎ(person)を ＋ causative (intransitive verbs)',
                'pattern' => 'Ｎ(orang)を Ｖ使役 (intransitif)',
                'payload' => [
                    'explanation_id' => 'Kalau kata kerja aslinya intransitif (tidak punya objek: 行《い》く, 遊《あそ》ぶ, 立《た》つ, 笑《わら》う), orang yang disuruh atau dibiarkan ditandai を: ＡはＢを Ｖ使役. Contoh: 部長《ぶちょう》は ミラーさんを 出張《しゅっちょう》させます. Kata kerja emosi seperti 笑《わら》う, 泣《な》く, 怒《おこ》る juga dipakai di pola ini untuk \"membuat orang merasa ...\".',
                    'explanation_en' => 'When the original verb is intransitive (takes no object: 行《い》く, 遊《あそ》ぶ, 立《た》つ, 笑《わら》う), the person who is made or allowed to do it is marked with を: ＡはＢを Ｖcausative. Example: 部長《ぶちょう》は ミラーさんを 出張《しゅっちょう》させます. Emotion verbs such as 笑《わら》う, 泣《な》く, 怒《おこ》る also fit this pattern for \"making someone feel ...\".',
                    'notes_id' => [
                        'Jika kata kerja intransitif itu memakai を untuk tempat yang dilalui (道《みち》を 歩《ある》く, 公園《こうえん》を 走《はし》る), orangnya ditandai に: 子《こ》どもに 公園《こうえん》を 走《はし》らせます.',
                        'Pelaku asli (yang melakukan) tidak boleh ditandai を dua kali dalam satu kalimat — itu sebabnya に dipakai di kasus tempat.',
                    ],
                    'notes_en' => [
                        'If the intransitive verb already uses を for a place passed through (道《みち》を 歩《ある》く, 公園《こうえん》を 走《はし》る), the person is marked with に: 子《こ》どもに 公園《こうえん》を 走《はし》らせます.',
                        'The same sentence cannot hold two を for different roles — that is why に is used in the place case.',
                    ],
                    'examples' => [
                        ['ja' => '母《はは》は 子《こ》どもを 外《そと》で 遊《あそ》ばせました。', 'reading' => 'Haha wa kodomo o soto de asobasemashita.', 'id' => 'Ibu membiarkan anaknya bermain di luar.', 'en' => 'Mother let her child play outside.'],
                        ['ja' => '先生《せんせい》は 生徒《せいと》を 廊下《ろうか》に 立《た》たせました。', 'reading' => 'Sensei wa seito o rouka ni tatasemashita.', 'id' => 'Guru menyuruh murid berdiri di lorong.', 'en' => 'The teacher made the student stand in the corridor.'],
                        ['ja' => 'わたしは 子《こ》どもに 公園《こうえん》を 走《はし》らせます。', 'reading' => 'Watashi wa kodomo ni kouen o hashirasemasu.', 'id' => 'Saya menyuruh anak berlari di taman.', 'en' => 'I make my child run in the park.'],
                        ['ja' => '兄《あに》は おもしろい 話《はなし》で みんなを 笑《わら》わせました。', 'reading' => 'Ani wa omoshiroi hanashi de minna o warawasemashita.', 'id' => 'Kakak membuat semua orang tertawa dengan ceritanya yang lucu.', 'en' => 'My older brother made everyone laugh with a funny story.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Perintah dinas',
                        'title_en' => 'A business trip order',
                        'lines' => [
                            ['speaker' => '部長', 'ja' => '来月《らいげつ》、ワヒュさんを 東京《とうきょう》へ 出張《しゅっちょう》させる つもりです。', 'reading' => 'Raigetsu, Wahyu-san o Toukyou e shucchou saseru tsumori desu.', 'id' => 'Bulan depan saya berencana mengirim Wahyu dinas ke Tokyo.', 'en' => 'Next month I plan to send Wahyu on a business trip to Tokyo.'],
                            ['speaker' => 'たなか', 'ja' => 'はい、わかりました。何日間《なんにちかん》ですか。', 'reading' => 'Hai, wakarimashita. Nan-nichi-kan desu ka.', 'id' => 'Baik, saya mengerti. Berapa hari?', 'en' => 'Understood. For how many days?'],
                            ['speaker' => '部長', 'ja' => '一週間《いっしゅうかん》です。', 'reading' => 'Isshuukan desu.', 'id' => 'Satu minggu.', 'en' => 'One week.'],
                            ['speaker' => 'たなか', 'ja' => 'だいじょうぶです。', 'reading' => 'Daijoubu desu.', 'id' => 'Tidak masalah.', 'en' => 'No problem.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => 'Ｎ１(orang)に Ｎ２を ＋ kausatif (kata kerja transitif)',
                'title_en' => 'Ｎ１(person)に Ｎ２を ＋ causative (transitive verbs)',
                'pattern' => 'Ｎ１(orang)に Ｎ２を Ｖ使役 (transitif)',
                'payload' => [
                    'explanation_id' => 'Kalau kata kerja aslinya transitif (sudah punya objek を: 読《よ》む, 書《か》く, 洗《あら》う), objeknya tetap を, dan orang yang disuruh ditandai に: ＡはＢに Ｎを Ｖ使役. Contoh: 母《はは》は 子《こ》どもに 皿《さら》を 洗《あら》わせます. Aturan praktis: orang yang disuruh ditandai を kalau tidak ada objek lain, dan に kalau ada objek を.',
                    'explanation_en' => 'When the original verb is transitive (it already has an object を: 読《よ》む, 書《か》く, 洗《あら》う), the object keeps を and the person who is made to do it is marked with に: ＡはＢに Ｎを Ｖcausative. Example: 母《はは》は 子《こ》どもに 皿《さら》を 洗《あら》わせます. Rule of thumb: the person is を if there is no other object, and に if there is an object を.',
                    'notes_id' => [
                        'Bandingkan: 子《こ》どもを 行《い》かせます (intransitif, を) dan 子《こ》どもに 本《ほん》を 読《よ》ませます (transitif, に).',
                        'Kata kerja kausatif transitif sering muncul dengan 手伝《てつだ》う, 書《か》く, 読《よ》む, 運《はこ》ぶ, 洗《あら》う.',
                    ],
                    'notes_en' => [
                        'Compare: 子《こ》どもを 行《い》かせます (intransitive, を) and 子《こ》どもに 本《ほん》を 読《よ》ませます (transitive, に).',
                        'Transitive causatives often appear with 手伝《てつだ》う, 書《か》く, 読《よ》む, 運《はこ》ぶ, 洗《あら》う.',
                    ],
                    'examples' => [
                        ['ja' => '母《はは》は 子《こ》どもに 皿《さら》を 洗《あら》わせます。', 'reading' => 'Haha wa kodomo ni sara o arawasemasu.', 'id' => 'Ibu menyuruh anaknya mencuci piring.', 'en' => 'Mother makes her child wash the dishes.'],
                        ['ja' => '先生《せんせい》は 生徒《せいと》に 作文《さくぶん》を 書《か》かせました。', 'reading' => 'Sensei wa seito ni sakubun o kakasemashita.', 'id' => 'Guru menyuruh murid menulis karangan.', 'en' => 'The teacher made the students write an essay.'],
                        ['ja' => '父《ちち》は 弟《おとうと》に 荷物《にもつ》を 運《はこ》ばせました。', 'reading' => 'Chichi wa otouto ni nimotsu o hakobasemashita.', 'id' => 'Ayah menyuruh adik membawa barang.', 'en' => 'Father made my younger brother carry the luggage.'],
                        ['ja' => '課長《かちょう》は 新《あたら》しい 社員《しゃいん》に 資料《しりょう》を コピーさせました。', 'reading' => 'Kachou wa atarashii shain ni shiryou o kopii sasemashita.', 'id' => 'Kepala seksi menyuruh karyawan baru memfotokopi dokumen.', 'en' => 'The section chief had the new employee copy the documents.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Tugas rumah untuk anak',
                        'title_en' => 'Chores for the children',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'お子《こ》さんに 家《いえ》の 仕事《しごと》を させて いますか。', 'reading' => 'Okosan ni ie no shigoto o sasete imasu ka.', 'id' => 'Apakah anak Anda diberi tugas rumah?', 'en' => 'Do you give your child chores at home?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい、毎晩《まいばん》 皿《さら》を 洗《あら》わせて います。', 'reading' => 'Hai, maiban sara o arawasete imasu.', 'id' => 'Ya, setiap malam saya menyuruhnya mencuci piring.', 'en' => 'Yes, I have him wash the dishes every night.'],
                            ['speaker' => 'たなか', 'ja' => 'うちは 掃除《そうじ》を 手伝《てつだ》わせて います。', 'reading' => 'Uchi wa souji o tetsudawasete imasu.', 'id' => 'Di rumah kami, anak disuruh membantu membersihkan.', 'en' => 'At our house, the kids help with the cleaning.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いい ことですね。', 'reading' => 'Ii koto desu ne.', 'id' => 'Bagus, ya.', 'en' => 'That is good.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'Makna paksaan／izin dan Vて もらいます／いただきます',
                'title_en' => 'Making vs letting, and Vて もらいます／いただきます',
                'pattern' => 'Ｖ使役 ／ Ｖて もらいます・いただきます',
                'payload' => [
                    'explanation_id' => 'Kausatif punya dua makna: (1) paksaan — orang yang berposisi di atas menyuruh orang di bawahnya (orang tua ke anak, atasan ke bawahan, kakak ke adik); (2) izin — membiarkan orang di bawah melakukan keinginannya. Maknanya dibaca dari konteks. Karena kausatif menyiratkan kekuasaan, pola ini tidak dipakai ke orang yang tidak punya kuasa memerintah. Untuk meminta seseorang melakukan sesuatu dan menerima kebaikannya, pakai Vて もらいます (setara atau lebih rendah) atau Vて いただきます (lebih tinggi).',
                    'explanation_en' => 'The causative has two meanings: (1) compulsion — a person in a higher position makes someone below them do something (parent to child, boss to subordinate, older to younger sibling); (2) permission — letting someone below do what they want. The meaning is read from context. Because the causative implies authority, it is not used toward people you have no power to command. To have someone do something and receive their kindness, use Vて もらいます (equal or lower) or Vて いただきます (higher).',
                    'notes_id' => [
                        '先生《せんせい》は 生徒《せいと》に 好《す》きな 本《ほん》を 読《よ》ませました = izin; 母《はは》は 子《こ》どもに 嫌《きら》いな 野菜《やさい》を 食《た》べさせました = paksaan.',
                        'Ke atasan jangan 説明《せつめい》させます, tetapi 説明《せつめい》して いただきます.',
                    ],
                    'notes_en' => [
                        '先生《せんせい》は 生徒《せいと》に 好《す》きな 本《ほん》を 読《よ》ませました = permission; 母《はは》は 子《こ》どもに 嫌《きら》いな 野菜《やさい》を 食《た》べさせました = compulsion.',
                        'Toward a superior, do not say 説明《せつめい》させます; say 説明《せつめい》して いただきます.',
                    ],
                    'examples' => [
                        ['ja' => '先生《せんせい》は 生徒《せいと》に 好《す》きな 本《ほん》を 読《よ》ませました。', 'reading' => 'Sensei wa seito ni suki na hon o yomasemashita.', 'id' => 'Guru membiarkan murid membaca buku yang mereka sukai.', 'en' => 'The teacher let the students read books they like.'],
                        ['ja' => '母《はは》は 子《こ》どもに 嫌《きら》いな 野菜《やさい》を 食《た》べさせました。', 'reading' => 'Haha wa kodomo ni kirai na yasai o tabesasemashita.', 'id' => 'Ibu menyuruh anaknya makan sayur yang tidak disukai.', 'en' => 'Mother made her child eat vegetables he dislikes.'],
                        ['ja' => 'わたしは 部長《ぶちょう》に 説明《せつめい》して いただきました。', 'reading' => 'Watashi wa buchou ni setsumei shite itadakimashita.', 'id' => 'Saya dijelaskan oleh kepala bagian (atas permintaan saya).', 'en' => 'I had the department head explain it to me.'],
                        ['ja' => 'わたしは 友達《ともだち》に 荷物《にもつ》を 持《も》って もらいました。', 'reading' => 'Watashi wa tomodachi ni nimotsu o motte moraimashita.', 'id' => 'Saya dibantu teman membawakan barang.', 'en' => 'I had a friend carry my luggage.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Soal pindahan',
                        'title_en' => 'About moving house',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '引《ひ》っ越《こ》しは どうでしたか。', 'reading' => 'Hikkoshi wa dou deshita ka.', 'id' => 'Bagaimana pindahannya?', 'en' => 'How was the move?'],
                            ['speaker' => 'ワヒュ', 'ja' => '友達《ともだち》に 手伝《てつだ》って もらいました。', 'reading' => 'Tomodachi ni tetsudatte moraimashita.', 'id' => 'Saya dibantu oleh teman.', 'en' => 'My friends helped me.'],
                            ['speaker' => 'サリ', 'ja' => 'それは よかったですね。', 'reading' => 'Sore wa yokatta desu ne.', 'id' => 'Syukurlah.', 'en' => 'That is good to hear.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、お礼《れい》に 昼《ひる》ごはんを ごちそうしました。', 'reading' => 'Ee, orei ni hirugohan o gochisou shimashita.', 'id' => 'Ya, sebagai terima kasih saya traktir makan siang.', 'en' => 'Yes, I treated them to lunch as thanks.'],
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => 'Vさせて いただけませんか (minta izin dengan sopan)',
                'title_en' => 'Vさせて いただけませんか (asking permission politely)',
                'pattern' => 'Ｖ使役て いただけませんか',
                'payload' => [
                    'explanation_id' => 'Di N4 Pelajaran 1 kamu belajar Vて いただけませんか untuk meminta orang lain melakukan sesuatu. Kalau yang melakukan adalah pembicara sendiri dan ia meminta izin, bentuk kausatif dipakai: Vさせて いただけませんか = \"Bolehkah saya ...?\" (secara harfiah: tolong izinkan saya melakukan). Bentuk ini sangat sopan dan dipakai kepada atasan, pelanggan, atau orang yang belum akrab.',
                    'explanation_en' => 'In N4 Lesson 1 you learned Vて いただけませんか to ask someone else to do something. When the one who will do it is the speaker and they are asking permission, use the causative: Vさせて いただけませんか = \"May I ...?\" (literally: please let me do). This form is very polite and is used toward superiors, customers, or people you do not know well.',
                    'notes_id' => [
                        'Bandingkan: 説明《せつめい》して いただけませんか (tolong jelaskan — orang lain bertindak) dan 帰《かえ》らせて いただけませんか (izinkan saya pulang — saya bertindak).',
                        'Untuk menyatakan keinginan, 〜させて いただきたいんですが juga lazim dipakai.',
                    ],
                    'notes_en' => [
                        'Compare: 説明《せつめい》して いただけませんか (please explain — the other person acts) and 帰《かえ》らせて いただけませんか (please let me go home — I act).',
                        '〜させて いただきたいんですが is also commonly used to state a wish.',
                    ],
                    'examples' => [
                        ['ja' => '用事《ようじ》が ありますので、あした 休《やす》ませて いただけませんか。', 'reading' => 'Youji ga arimasu node, ashita yasumasete itadakemasen ka.', 'id' => 'Karena ada urusan, bolehkah saya izin libur besok?', 'en' => 'I have an errand, so may I take tomorrow off?'],
                        ['ja' => 'この 資料《しりょう》を コピーさせて いただけませんか。', 'reading' => 'Kono shiryou o kopii sasete itadakemasen ka.', 'id' => 'Bolehkah saya memfotokopi dokumen ini?', 'en' => 'May I copy these documents?'],
                        ['ja' => 'ここに 車《くるま》を 止《と》めさせて いただけませんか。', 'reading' => 'Koko ni kuruma o tomesasete itadakemasen ka.', 'id' => 'Bolehkah saya memarkir mobil di sini?', 'en' => 'May I park my car here?'],
                        ['ja' => '来月《らいげつ》、十日間《とおかかん》 休《やす》ませて いただきたいんですが。', 'reading' => 'Raigetsu, tooka-kan yasumasete itadakitai n desu ga.', 'id' => 'Bulan depan saya ingin mengambil cuti selama sepuluh hari.', 'en' => 'Next month I would like to take ten days off.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Meminta cuti',
                        'title_en' => 'Asking for leave',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '部長《ぶちょう》、今《いま》、お忙《いそが》しいですか。', 'reading' => 'Buchou, ima, oisogashii desu ka.', 'id' => 'Pak kepala bagian, apakah sekarang sibuk?', 'en' => 'Department head, are you busy now?'],
                            ['speaker' => '部長', 'ja' => 'いいえ、どうぞ。', 'reading' => 'Iie, douzo.', 'id' => 'Tidak, silakan.', 'en' => 'No, go ahead.'],
                            ['speaker' => 'ワヒュ', 'ja' => '実《じつ》は お願《ねが》いが あるんですが……。来月《らいげつ》、五日間《いつかかん》 休《やす》ませて いただけませんか。', 'reading' => 'Jitsu wa onegai ga aru n desu ga...... Raigetsu, itsuka-kan yasumasete itadakemasen ka.', 'id' => 'Sebenarnya ada permohonan... Bulan depan, bolehkah saya cuti lima hari?', 'en' => 'Actually I have a request... May I take five days off next month?'],
                            ['speaker' => '部長', 'ja' => '五日間《いつかかん》ですか。', 'reading' => 'Itsuka-kan desu ka.', 'id' => 'Lima hari?', 'en' => 'Five days?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい、国《くに》の 家族《かぞく》が 日本《にほん》へ 来《く》るんです。', 'reading' => 'Hai, kuni no kazoku ga Nihon e kuru n desu.', 'id' => 'Ya, keluarga dari kampung halaman akan datang ke Jepang.', 'en' => 'Yes, my family from home is coming to Japan.'],
                            ['speaker' => '部長', 'ja' => 'そうですか。それまでに 仕事《しごと》を 終《お》えて くださいね。', 'reading' => 'Sou desu ka. Sore made ni shigoto o oete kudasai ne.', 'id' => 'Begitu. Selesaikan pekerjaanmu sebelum itu, ya.', 'en' => 'I see. Please finish your work by then.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい、ありがとう ございます。', 'reading' => 'Hai, arigatou gozaimasu.', 'id' => 'Baik, terima kasih.', 'en' => 'Yes, thank you.'],
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
            ['slug' => 'n4-pelajaran-23'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 23',
                'name_en' => 'N4 Lesson 23 Vocabulary',
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
            // Kosakata utama
            ['降ろします／下ろします', 'おろします', 'oroshimasu', 'menurunkan', 'to lower, to take down'],
            ['届けます', 'とどけます', 'todokemasu', 'mengantarkan', 'to deliver'],
            ['世話をします', 'せわをします', 'sewa o shimasu', 'menjaga, membantu', 'to look after, to help'],
            ['録音します', 'ろくおんします', 'rokuonshimasu', 'merekam', 'to record (sound)'],
            ['嫌[な]', 'いや[な]', 'iya', 'tidak senang, tidak suka', 'unpleasant, disliked'],
            ['塾', 'じゅく', 'juku', 'les', 'cram school, private lessons'],
            ['生徒', 'せいと', 'seito', 'murid', 'pupil, student'],
            ['ファイル', 'ファイル', 'fairu', 'map', 'file, folder'],
            ['自由に', 'じゆうに', 'jiyuu ni', 'dengan bebas', 'freely'],
            ['〜間', '〜かん', 'kan', 'selama ~', 'for a period of ~'],
            ['いい ことですね。', 'いい ことですね。', 'ii koto desu ne', 'Bagus, ya.', 'That is good, is it not?'],

            // 会話 (percakapan)
            ['お忙しいですか。', 'おいそがしいですか。', 'oisogashii desu ka', 'Apakah sibuk? (dipakai untuk mengajak bicara orang berpangkat tinggi)', 'Are you busy? (used to start talking to a superior)'],
            ['営業', 'えいぎょう', 'eigyou', 'perdagangan, penjualan', 'sales, business'],
            ['それまでに', 'それまでに', 'sore made ni', 'sebelumnya', 'by then, before that'],
            ['かまいません。', 'かまいません。', 'kamaimasen', 'Tidak apa-apa.', 'It does not matter.'],
            ['楽しみます', 'たのしみます', 'tanoshimimasu', 'menikmati', 'to enjoy'],

            // 読み物 (bacaan)
            ['親', 'おや', 'oya', 'orang tua', 'parent'],
            ['小学生', 'しょうがくせい', 'shougakusei', 'murid SD', 'elementary school pupil'],
            ['〜パーセント', '〜パーセント', 'paasento', '~ persen', '~ percent'],
            ['その次', 'そのつぎ', 'sono tsugi', 'yang berikut', 'the next one'],
            ['習字', 'しゅうじ', 'shuuji', 'kaligrafi', 'calligraphy'],
            ['普通の', 'ふつうの', 'futsuu no', 'biasa', 'ordinary'],
        ];
    }
}
