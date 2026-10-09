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

class N4Lesson24Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 24: "Berbicara dengan Sopan kepada Atasan" (bahasa
     * hormat 尊敬語, salam lewat telepon, dan acara musiman). Level N4 berdiri
     * di samping N5 dan dipilih lewat selektor N5 / N4 di Tata Bahasa,
     * Kosakata dan Referensi Tata Bahasa.
     *
     * Isi:
     *   - Unit order 24 di level N4
     *   - Lesson Bunpou (order 0, category grammar) dengan 7 kartu:
     *       1. Gambaran 敬語 (尊敬語・謙譲語・丁寧語)   5. お／ご〜ください
     *       2. Kata kerja hormat bentuk Vれる／Vられる   6. Awalan お／ご
     *       3. お＋Vます形＋になります                  7. 〜まして／〜ますので
     *       4. Kata kerja hormat khusus
     *   - Kategori kosakata "n4-pelajaran-24" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus. Penjelasan, contoh
     * kalimat dan dialog ditulis baru untuk aplikasi ini. Nama diri dan nama
     * sekolah fiktif tidak dimasukkan, sama seperti di pelajaran lain.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson24Seeder
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
            ['level_id' => $level->id, 'order' => 24],
            [
                'title_id' => 'Pelajaran 24: Berbicara dengan Sopan kepada Atasan',
                'title_en' => 'Lesson 24: Speaking Politely to Superiors',
                'description_id' => 'Bahasa hormat (尊敬語): bentuk pasif hormat, お〜になります, kata kerja khusus, お／ご〜ください, serta acara musiman.',
                'description_en' => 'Honorific language (尊敬語): honorific passive forms, お〜になります, special verbs, お／ご〜ください, plus seasonal events.',
                'icon' => 'mdi-account-tie',
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
                'title_id' => '敬語 (gambaran bahasa hormat)',
                'title_en' => '敬語 (an overview of honorific language)',
                'pattern' => '尊敬語・謙譲語・丁寧語',
                'payload' => [
                    'explanation_id' => '敬語《けいご》 adalah ungkapan untuk menunjukkan rasa hormat kepada lawan bicara atau orang yang dibicarakan. Ada tiga jenis: 尊敬語《そんけいご》 (meninggikan orang lain dan tindakannya), 謙譲語《けんじょうご》 (merendahkan diri sendiri; dipelajari di pelajaran berikutnya), dan 丁寧語《ていねいご》 (bentuk sopan です／ます). Pelajaran ini membahas 尊敬語. Dipakai terutama (1) kepada orang yang lebih tua, belum dikenal, atau belum akrab, (2) saat membicarakan orang yang kedudukannya lebih tinggi, dan (3) dalam situasi formal seperti kantor, pelanggan, dan surat.',
                    'explanation_en' => '敬語《けいご》 is language that shows respect to the listener or the person being talked about. There are three kinds: 尊敬語《そんけいご》 (raising the other person and their actions), 謙譲語《けんじょうご》 (lowering yourself; studied in the next lesson), and 丁寧語《ていねいご》 (the polite です／ます). This lesson covers 尊敬語. It is used mainly (1) to people who are older, unfamiliar, or not close, (2) when talking about someone of higher standing, and (3) in formal settings such as the office, with customers, and in letters.',
                    'notes_id' => [
                        'Jangan memakai 尊敬語 untuk diri sendiri atau keluarga sendiri. Itu hanya untuk orang yang dihormati.',
                        'Pilihan memakai atau tidak ditentukan oleh lawan bicara, orang yang dibicarakan, dan suasananya, bukan oleh isi kalimatnya.',
                        'Bentuk sopan です／ます saja belum termasuk 尊敬語 — ia hanya membuat kalimat terdengar sopan.',
                    ],
                    'notes_en' => [
                        'Never use 尊敬語 for yourself or your own family. It is only for people you respect.',
                        'Whether to use it depends on the listener, the person talked about, and the setting, not on the content of the sentence.',
                        'The polite です／ます alone is not 尊敬語; it only makes a sentence sound polite.',
                    ],
                    'examples' => [
                        ['ja' => '社長《しゃちょう》は あした 大阪《おおさか》へ いらっしゃいます。', 'reading' => 'Shachou wa ashita Oosaka e irasshaimasu.', 'id' => 'Direktur utama akan pergi ke Osaka besok.', 'en' => 'The company president will go to Osaka tomorrow.'],
                        ['ja' => '先生《せんせい》は 何《なに》を 召《め》し上《あ》がりますか。', 'reading' => 'Sensei wa nani o meshiagarimasu ka.', 'id' => 'Bapak/Ibu guru mau makan apa?', 'en' => 'What will you (the teacher) have to eat?'],
                        ['ja' => 'お客様《きゃくさま》は もう お帰《かえ》りに なりました。', 'reading' => 'Okyakusama wa mou okaeri ni narimashita.', 'id' => 'Tamu sudah pulang.', 'en' => 'The guest has already gone home.'],
                        ['ja' => '部長《ぶちょう》の 奥様《おくさま》は ピアノが お上手《じょうず》です。', 'reading' => 'Buchou no okusama wa piano ga ojouzu desu.', 'id' => 'Istri kepala bagian pandai bermain piano.', 'en' => 'The department head wife is good at the piano.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menanyakan atasan di kantor tamu',
                        'title_en' => 'Asking for a superior at a reception desk',
                        'lines' => [
                            ['speaker' => '受付', 'ja' => 'いらっしゃいませ。', 'reading' => 'Irasshaimase.', 'id' => 'Selamat datang.', 'en' => 'Welcome.'],
                            ['speaker' => 'ワヒュ', 'ja' => '営業部《えいぎょうぶ》の 山田部長《やまだぶちょう》は いらっしゃいますか。', 'reading' => 'Eigyoubu no Yamada buchou wa irasshaimasu ka.', 'id' => 'Apakah Kepala Bagian Yamada dari bagian penjualan ada?', 'en' => 'Is Department Head Yamada of Sales in?'],
                            ['speaker' => '受付', 'ja' => 'はい、いらっしゃいます。こちらへ どうぞ。', 'reading' => 'Hai, irasshaimasu. Kochira e douzo.', 'id' => 'Ya, ada. Silakan lewat sini.', 'en' => 'Yes, he is. This way, please.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => 'Kata kerja hormat bentuk Vれる／Vられる',
                'title_en' => 'Honorific verbs in the Vれる／Vられる form',
                'pattern' => 'Vれます／Vられます',
                'payload' => [
                    'explanation_id' => 'Cara termudah membuat kalimat hormat: ubah kata kerja ke bentuk yang sama dengan bentuk pasif. Kelompok I: akhiran u diganti a + れます (書《か》く → 書《か》かれます). Kelompok II: tambahkan られます (食《た》べる → 食《た》べられます). Kelompok III: する → されます, 来《く》る → 来《こ》られます. Hasilnya dikonjugasikan seperti kata kerja kelompok II: 書《か》かれる, 書《か》かれない, 書《か》かれた, 書《か》かれて. Bentuk ini memuji pelaku dari tindakan tersebut.',
                    'explanation_en' => 'The easiest way to make a sentence honorific: change the verb to the same form as the passive. Group I: change the final u to a + れます (書《か》く → 書《か》かれます). Group II: add られます (食《た》べる → 食《た》べられます). Group III: する → されます, 来《く》る → 来《こ》られます. The result conjugates like a group II verb: 書《か》かれる, 書《か》かれない, 書《か》かれた, 書《か》かれて. The form praises the doer of the action.',
                    'notes_id' => [
                        'Bentuknya sama dengan pasif, jadi artinya ditentukan oleh konteks: kalau pelakunya orang yang dihormati, itu hormat.',
                        'Bentuk ini lebih ringan dan lebih umum daripada お〜に なります.',
                    ],
                    'notes_en' => [
                        'It looks the same as the passive, so the meaning comes from context: if the doer is a respected person, it is honorific.',
                        'This form is lighter and more common than お〜に なります.',
                    ],
                    'examples' => [
                        ['ja' => '山田《やまだ》さんは 毎朝《まいあさ》 新聞《しんぶん》を 読《よ》まれます。', 'reading' => 'Yamada-san wa maiasa shinbun o yomaremasu.', 'id' => 'Pak Yamada membaca koran setiap pagi.', 'en' => 'Mr. Yamada reads the newspaper every morning.'],
                        ['ja' => '先生《せんせい》は いつ 国《くに》へ 帰《かえ》られますか。', 'reading' => 'Sensei wa itsu kuni e kaeraremasu ka.', 'id' => 'Kapan Bapak/Ibu guru pulang ke negara asal?', 'en' => 'When will you (the teacher) go back to your home country?'],
                        ['ja' => '部長《ぶちょう》は 来週《らいしゅう》 東京《とうきょう》へ 行《い》かれます。', 'reading' => 'Buchou wa raishuu Toukyou e ikaremasu.', 'id' => 'Kepala bagian akan pergi ke Tokyo minggu depan.', 'en' => 'The department head will go to Tokyo next week.'],
                        ['ja' => '社長《しゃちょう》は 毎日《まいにち》 歩《ある》いて 会社《かいしゃ》へ 来《こ》られます。', 'reading' => 'Shachou wa mainichi aruite kaisha e koraremasu.', 'id' => 'Direktur utama datang ke kantor dengan berjalan kaki setiap hari.', 'en' => 'The president walks to the office every day.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Tentang kepala seksi',
                        'title_en' => 'About the section chief',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '課長《かちょう》は 何時《なんじ》に 来《こ》られますか。', 'reading' => 'Kachou wa nanji ni koraremasu ka.', 'id' => 'Kepala seksi datang jam berapa?', 'en' => 'What time will the section chief come?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いつも 九時《くじ》ごろ 来《こ》られますよ。', 'reading' => 'Itsumo kuji goro koraremasu yo.', 'id' => 'Biasanya datang sekitar jam sembilan.', 'en' => 'He usually comes around nine.'],
                            ['speaker' => 'サリ', 'ja' => 'じゃ、もうすぐですね。', 'reading' => 'Ja, mousugu desu ne.', 'id' => 'Kalau begitu sebentar lagi, ya.', 'en' => 'Then it will be soon.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => 'お ＋ Vます形 ＋ に なります',
                'title_en' => 'お ＋ V-stem ＋ に なります',
                'pattern' => 'お ＋ Vます形 ＋ に なります',
                'payload' => [
                    'explanation_id' => 'Bentuk hormat yang dianggap lebih halus daripada Vれます. Susunannya: お + kata kerja bentuk-ます (tanpa ます) + に なります. Contoh: 読《よ》みます → お読《よ》みに なります, 待《ま》ちます → お待《ま》ちに なります. Untuk bentuk lampau atau sedang berlangsung, ubah なります: お読《よ》みに なりました, お読《よ》みに なって います.',
                    'explanation_en' => 'An honorific form regarded as more refined than Vれます. Structure: お + verb ます-stem + に なります. Examples: 読《よ》みます → お読《よ》みに なります, 待《ま》ちます → お待《ま》ちに なります. For the past or an ongoing state, change なります: お読《よ》みに なりました, お読《よ》みに なって います.',
                    'notes_id' => [
                        'Tidak bisa dipakai pada kata kerja yang bentuk-ます-nya hanya satu suku kata (見《み》ます, 寝《ね》ます, 着《き》ます) dan pada kata kerja kelompok III (します, 来《き》ます).',
                        'Bila kata kerja punya bentuk hormat khusus (lihat kartu 4), pakai bentuk khusus itu, bukan お〜に なります.',
                    ],
                    'notes_en' => [
                        'It cannot be used with verbs whose ます-stem is a single syllable (見《み》ます, 寝《ね》ます, 着《き》ます) or with group III verbs (します, 来《き》ます).',
                        'When a verb has a special honorific form (see card 4), use that instead of お〜に なります.',
                    ],
                    'examples' => [
                        ['ja' => '課長《かちょう》は 今《いま》 新聞《しんぶん》を お読《よ》みに なって います。', 'reading' => 'Kachou wa ima shinbun o oyomi ni natte imasu.', 'id' => 'Kepala seksi sekarang sedang membaca koran.', 'en' => 'The section chief is reading the newspaper now.'],
                        ['ja' => 'お客様《きゃくさま》が お待《ま》ちに なって います。', 'reading' => 'Okyakusama ga omachi ni natte imasu.', 'id' => 'Tamu sedang menunggu.', 'en' => 'A guest is waiting.'],
                        ['ja' => '社長《しゃちょう》は 何時《なんじ》に お出《で》かけに なりますか。', 'reading' => 'Shachou wa nanji ni odekake ni narimasu ka.', 'id' => 'Direktur utama berangkat jam berapa?', 'en' => 'What time will the president go out?'],
                        ['ja' => '先生《せんせい》は もう この 手紙《てがみ》を お書《か》きに なりましたか。', 'reading' => 'Sensei wa mou kono tegami o okaki ni narimashita ka.', 'id' => 'Apakah Bapak/Ibu guru sudah menulis surat ini?', 'en' => 'Have you (the teacher) already written this letter?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di ruang tunggu',
                        'title_en' => 'In the waiting room',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '部長《ぶちょう》は もう お帰《かえ》りに なりましたか。', 'reading' => 'Buchou wa mou okaeri ni narimashita ka.', 'id' => 'Apakah kepala bagian sudah pulang?', 'en' => 'Has the department head already gone home?'],
                            ['speaker' => 'サリ', 'ja' => 'いいえ、まだ 会議室《かいぎしつ》で お待《ま》ちに なって います。', 'reading' => 'Iie, mada kaigishitsu de omachi ni natte imasu.', 'id' => 'Belum, beliau masih menunggu di ruang rapat.', 'en' => 'No, he is still waiting in the meeting room.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'Kata kerja hormat khusus',
                'title_en' => 'Special honorific verbs',
                'pattern' => 'いらっしゃいます／召《め》し上《あ》がります／おっしゃいます ほか',
                'payload' => [
                    'explanation_id' => 'Beberapa kata kerja punya bentuk hormat tersendiri dengan tingkat hormat setara お〜に なります: います／行《い》きます／来《き》ます → いらっしゃいます; 食《た》べます／飲《の》みます → 召《め》し上《あ》がります; 言《い》います → おっしゃいます; します → なさいます; 見《み》ます → ご覧《らん》に なります; 知《し》って います → ご存《ぞん》じです. Bentuk khusus ini lebih diutamakan daripada Vれます atau お〜に なります.',
                    'explanation_en' => 'Some verbs have their own honorific forms at the same level as お〜に なります: います／行《い》きます／来《き》ます → いらっしゃいます; 食《た》べます／飲《の》みます → 召《め》し上《あ》がります; 言《い》います → おっしゃいます; します → なさいます; 見《み》ます → ご覧《らん》に なります; 知《し》って います → ご存《ぞん》じです. These special forms take priority over Vれます or お〜に なります.',
                    'notes_id' => [
                        'いらっしゃいます, なさいます, くださいます (memberi), おっしゃいます termasuk kelompok I, tetapi ます-nya berubah: いらっしゃる, いらっしゃらない, いらっしゃった (bukan ×いらっしゃりない). Ingat: bentuk ます-nya berakhiran い, bentuk kamusnya berakhiran る.',
                        'いらっしゃいます bisa berarti "ada", "pergi", atau "datang", tergantung konteks.',
                    ],
                    'notes_en' => [
                        'いらっしゃいます, なさいます, くださいます (to give) and おっしゃいます are group I verbs, but their ます-forms are irregular: いらっしゃる, いらっしゃらない, いらっしゃった (not ×いらっしゃりない).',
                        'いらっしゃいます can mean "be", "go", or "come", depending on context.',
                    ],
                    'examples' => [
                        ['ja' => '山田先生《やまだせんせい》は 今《いま》 図書館《としょかん》に いらっしゃいます。', 'reading' => 'Yamada sensei wa ima toshokan ni irasshaimasu.', 'id' => 'Pak Yamada sedang berada di perpustakaan.', 'en' => 'Professor Yamada is in the library now.'],
                        ['ja' => 'おいしい ケーキですよ。どうぞ 召《め》し上《あ》がって ください。', 'reading' => 'Oishii keeki desu yo. Douzo meshiagatte kudasai.', 'id' => 'Kuenya enak. Silakan dimakan.', 'en' => 'This cake is delicious. Please help yourself.'],
                        ['ja' => '社長《しゃちょう》は 「あした 行《い》きます」と おっしゃいました。', 'reading' => 'Shachou wa "ashita ikimasu" to osshaimashita.', 'id' => 'Direktur utama berkata, "Saya akan pergi besok."', 'en' => 'The president said, "I will go tomorrow."'],
                        ['ja' => '田中《たなか》さんの お名前《なまえ》を ご存《ぞん》じですか。', 'reading' => 'Tanaka-san no onamae o gozonji desu ka.', 'id' => 'Apakah Anda tahu nama Tanaka?', 'en' => 'Do you know Tanaka name?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menawarkan makanan',
                        'title_en' => 'Offering food',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '先生《せんせい》、お茶《ちゃ》を どうぞ。', 'reading' => 'Sensei, ocha o douzo.', 'id' => 'Pak/Bu guru, silakan tehnya.', 'en' => 'Teacher, please have some tea.'],
                            ['speaker' => '先生', 'ja' => 'ありがとう。お菓子《かし》も 召《め》し上《あ》がりますか。', 'reading' => 'Arigatou. Okashi mo meshiagarimasu ka.', 'id' => 'Terima kasih. Apakah kamu mau kue juga?', 'en' => 'Thank you. Will you have some sweets too?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい、いただきます。', 'reading' => 'Hai, itadakimasu.', 'id' => 'Ya, saya makan.', 'en' => 'Yes, I will have some.'],
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => 'お／ご 〜 ください (permintaan hormat)',
                'title_en' => 'お／ご 〜 ください (a respectful request)',
                'pattern' => 'お Vます形 ください／ご N ください',
                'payload' => [
                    'explanation_id' => 'Bentuk hormat dari 〜て ください (lihat N5). Kata kerja kelompok I dan II menjadi お + bentuk-ます + ください: 待《ま》って ください → お待《ま》ち ください. Kata kerja kelompok III bentuk する menjadi ご + kata benda + ください: 利用《りよう》して ください → ご利用《りよう》 ください. Dipakai pada pengumuman, papan, dan kepada pelanggan atau tamu.',
                    'explanation_en' => 'The honorific form of 〜て ください (see N5). Group I and II verbs become お + ます-stem + ください: 待《ま》って ください → お待《ま》ち ください. Group III する verbs become ご + noun + ください: 利用《りよう》して ください → ご利用《りよう》 ください. It is used on announcements, signs, and to customers or guests.',
                    'notes_id' => [
                        'Kata kerja yang ます-nya satu suku kata (見《み》ます, 寝《ね》ます) tidak memakai bentuk ini.',
                        'Kata kerja dengan bentuk hormat khusus memakai bentuk て-nya: いらっしゃって ください, 召《め》し上《あ》がって ください.',
                    ],
                    'notes_en' => [
                        'Verbs whose ます-stem is one syllable (見《み》ます, 寝《ね》ます) do not use this form.',
                        'Verbs with a special honorific use its て-form: いらっしゃって ください, 召《め》し上《あ》がって ください.',
                    ],
                    'examples' => [
                        ['ja' => 'どうぞ お掛《か》け ください。', 'reading' => 'Douzo okake kudasai.', 'id' => 'Silakan duduk.', 'en' => 'Please have a seat.'],
                        ['ja' => 'こちらで しばらく お待《ま》ち ください。', 'reading' => 'Kochira de shibaraku omachi kudasai.', 'id' => 'Mohon tunggu sebentar di sini.', 'en' => 'Please wait here for a while.'],
                        ['ja' => '荷物《にもつ》は ここに お置《お》き ください。', 'reading' => 'Nimotsu wa koko ni oki kudasai.', 'id' => 'Silakan taruh barang bawaan di sini.', 'en' => 'Please put your luggage here.'],
                        ['ja' => 'バスを ご利用《りよう》 ください。', 'reading' => 'Basu o goriyou kudasai.', 'id' => 'Silakan gunakan bus.', 'en' => 'Please use the bus.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di penginapan',
                        'title_en' => 'At an inn',
                        'lines' => [
                            ['speaker' => '受付', 'ja' => 'いらっしゃいませ。お名前《なまえ》を お書《か》き ください。', 'reading' => 'Irasshaimase. Onamae o okaki kudasai.', 'id' => 'Selamat datang. Silakan tuliskan nama Anda.', 'en' => 'Welcome. Please write your name.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい。ここですか。', 'reading' => 'Hai. Koko desu ka.', 'id' => 'Baik. Di sini?', 'en' => 'Yes. Here?'],
                            ['speaker' => '受付', 'ja' => 'ええ。ありがとうございます。では、お部屋《へや》で お待《ま》ち ください。', 'reading' => 'Ee. Arigatou gozaimasu. Dewa, oheya de omachi kudasai.', 'id' => 'Ya. Terima kasih. Silakan menunggu di kamar.', 'en' => 'Yes. Thank you. Please wait in your room.'],
                        ],
                    ],
                ],
            ],

            // 6 ------------------------------------------------------------
            [
                'title_id' => 'Awalan お／ご (kata benda, kata sifat, kata keterangan)',
                'title_en' => 'The prefixes お／ご (nouns, adjectives, adverbs)',
                'pattern' => 'お／ご ＋ N／Adj／Adv',
                'payload' => [
                    'explanation_id' => 'Awalan お atau ご ditempelkan pada kata benda, kata sifat, dan kata keterangan untuk menghormati pemilik hal itu atau orang yang sedang berada dalam keadaan tersebut. Contoh: お名前《なまえ》, お元気《げんき》, ご家族《かぞく》, ご親切《しんせつ》. Kebanyakan kata asli Jepang memakai お (お国《くに》, お仕事《しごと》, お忙《いそが》しい), sedangkan kata serapan dari bahasa Tionghoa memakai ご (ご意見《いけん》, ご旅行《りょこう》, ご熱心《ねっしん》, ご自由《じゆう》に).',
                    'explanation_en' => 'The prefix お or ご is attached to nouns, adjectives, and adverbs to show respect to the owner of the thing or the person in that state. Examples: お名前《なまえ》, お元気《げんき》, ご家族《かぞく》, ご親切《しんせつ》. Most native Japanese words take お (お国《くに》, お仕事《しごと》, お忙《いそが》しい), while words of Chinese origin take ご (ご意見《いけん》, ご旅行《りょこう》, ご熱心《ねっしん》, ご自由《じゆう》に).',
                    'notes_id' => [
                        'Ada pengecualian: お電話《でんわ》, お約束《やくそく》, お食事《しょくじ》, お料理《りょうり》 memakai お walau berasal dari bahasa Tionghoa. Kalau ragu, hafalkan per kata.',
                        'Dalam bahasa hormat, bukan hanya kata kerja yang berubah; kata benda yang menyertainya sering ikut berubah: 部長《ぶちょう》の 奥様《おくさま》も ごいっしょに ゴルフに 行《い》かれます.',
                    ],
                    'notes_en' => [
                        'There are exceptions: お電話《でんわ》, お約束《やくそく》, お食事《しょくじ》, and お料理《りょうり》 take お even though they are of Chinese origin. When unsure, learn them word by word.',
                        'In honorific speech, not only the verb changes; the nouns around it often change too: 部長《ぶちょう》の 奥様《おくさま》も ごいっしょに ゴルフに 行《い》かれます.',
                    ],
                    'examples' => [
                        ['ja' => 'お名前《なまえ》は 何《なん》ですか。', 'reading' => 'Onamae wa nan desu ka.', 'id' => 'Siapa nama Anda?', 'en' => 'What is your name?'],
                        ['ja' => 'ご家族《かぞく》は お元気《げんき》ですか。', 'reading' => 'Gokazoku wa ogenki desu ka.', 'id' => 'Apakah keluarga Anda sehat?', 'en' => 'Is your family well?'],
                        ['ja' => 'ご親切《しんせつ》に ありがとうございます。', 'reading' => 'Goshinsetsu ni arigatou gozaimasu.', 'id' => 'Terima kasih atas kebaikan Anda.', 'en' => 'Thank you for your kindness.'],
                        ['ja' => 'お忙《いそが》しい ところ、すみません。', 'reading' => 'Oisogashii tokoro, sumimasen.', 'id' => 'Maaf mengganggu di tengah kesibukan Anda.', 'en' => 'Sorry to trouble you when you are busy.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Bertanya kabar',
                        'title_en' => 'Asking after someone',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '先生《せんせい》、ご家族《かぞく》は お元気《げんき》ですか。', 'reading' => 'Sensei, gokazoku wa ogenki desu ka.', 'id' => 'Pak/Bu guru, apakah keluarga Anda sehat?', 'en' => 'Teacher, is your family well?'],
                            ['speaker' => '先生', 'ja' => 'ええ、おかげさまで 元気《げんき》です。', 'reading' => 'Ee, okagesama de genki desu.', 'id' => 'Ya, berkat Anda mereka sehat.', 'en' => 'Yes, thanks to you they are well.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'それは よかったです。', 'reading' => 'Sore wa yokatta desu.', 'id' => 'Syukurlah.', 'en' => 'That is good to hear.'],
                        ],
                    ],
                ],
            ],

            // 7 ------------------------------------------------------------
            [
                'title_id' => '〜まして／〜ますので (bentuk lebih halus)',
                'title_en' => '〜まして／〜ますので (more polite connectors)',
                'pattern' => 'Vます形 ＋ まして／Vます ＋ ので',
                'payload' => [
                    'explanation_id' => 'Untuk berbicara lebih halus, bentuk-て kadang diganti dengan bentuk-ます + まして, dan ので diganti dengan bentuk sopan + ので (〜ますので). Keduanya dipakai ketika menyampaikan alasan atau kabar kepada orang yang dihormati, misalnya saat menelepon sekolah atau kantor. Arti dan fungsinya sama dengan bentuk biasa; hanya nuansanya lebih formal.',
                    'explanation_en' => 'To speak more politely, the て-form is sometimes replaced by the ます-stem + まして, and ので by the polite form + ので (〜ますので). Both are used to give a reason or news to someone you respect, for example when phoning a school or an office. The meaning and function are the same as the plain forms; only the tone is more formal.',
                    'notes_id' => [
                        'まして dipakai untuk menyambung kalimat (alasan atau urutan kejadian), bukan untuk meminta.',
                        'Bentuk ini juga cocok untuk pesan, surat, dan percakapan telepon yang sopan.',
                    ],
                    'notes_en' => [
                        'まして connects clauses (a reason or a sequence of events); it is not used for requests.',
                        'This form also suits messages, letters, and polite phone calls.',
                    ],
                    'examples' => [
                        ['ja' => 'きのう 風邪《かぜ》を ひきまして、会社《かいしゃ》を 休《やす》みました。', 'reading' => 'Kinou kaze o hikimashite, kaisha o yasumimashita.', 'id' => 'Kemarin saya masuk angin, jadi saya tidak masuk kerja.', 'en' => 'I caught a cold yesterday and took a day off work.'],
                        ['ja' => 'きょうは 早《はや》く 帰《かえ》りますので、先生《せんせい》に よろしく お伝《つた》え ください。', 'reading' => 'Kyou wa hayaku kaerimasu node, sensei ni yoroshiku otsutae kudasai.', 'id' => 'Karena hari ini saya pulang lebih awal, tolong sampaikan salam saya kepada guru.', 'en' => 'I am leaving early today, so please give my regards to the teacher.'],
                        ['ja' => 'バスが 遅《おく》れまして、すみません。', 'reading' => 'Basu ga okuremashite, sumimasen.', 'id' => 'Maaf, busnya terlambat.', 'en' => 'I am sorry, the bus was late.'],
                        ['ja' => '会議《かいぎ》が 始《はじ》まりますので、お入《はい》り ください。', 'reading' => 'Kaigi ga hajimarimasu node, ohairi kudasai.', 'id' => 'Rapat akan dimulai, jadi silakan masuk.', 'en' => 'The meeting is about to start, so please come in.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menelepon sekolah',
                        'title_en' => 'Phoning a school',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'おはようございます。三年二組《さんねんにくみ》の 子《こ》どもの 母《はは》ですが、先生《せんせい》は いらっしゃいますか。', 'reading' => 'Ohayou gozaimasu. Sannen nikumi no kodomo no haha desu ga, sensei wa irasshaimasu ka.', 'id' => 'Selamat pagi. Saya ibu dari seorang anak kelas 3-2, apakah gurunya ada?', 'en' => 'Good morning. I am the mother of a child in class 3-2. Is the teacher in?'],
                            ['speaker' => '先生', 'ja' => 'まだ 学校《がっこう》に 来《き》て いません。', 'reading' => 'Mada gakkou ni kite imasen.', 'id' => 'Beliau belum datang ke sekolah.', 'en' => 'She has not come to school yet.'],
                            ['speaker' => 'サリ', 'ja' => 'では、伝言《でんごん》を お願《ねが》いします。子《こ》どもが 熱《ねつ》を 出《だ》しまして、けさも 下《さ》がらないんです。', 'reading' => 'Dewa, dengon o onegai shimasu. Kodomo ga netsu o dashimashite, kesa mo sagaranai n desu.', 'id' => 'Kalau begitu, tolong sampaikan pesan. Anak saya demam dan pagi ini belum turun.', 'en' => 'Then please take a message. My child has a fever and it has not gone down this morning.'],
                            ['speaker' => '先生', 'ja' => 'それは いけませんね。お大事《だいじ》に。', 'reading' => 'Sore wa ikemasen ne. Odaiji ni.', 'id' => 'Wah, itu tidak baik. Semoga cepat sembuh.', 'en' => 'That is not good. Get well soon.'],
                            ['speaker' => 'サリ', 'ja' => 'ありがとうございます。先生《せんせい》に よろしく お伝《つた》え ください。失礼《しつれい》いたします。', 'reading' => 'Arigatou gozaimasu. Sensei ni yoroshiku otsutae kudasai. Shitsurei itashimasu.', 'id' => 'Terima kasih. Tolong sampaikan salam saya kepada guru. Permisi.', 'en' => 'Thank you. Please give my regards to the teacher. Goodbye.'],
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
            ['slug' => 'n4-pelajaran-24'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 24',
                'name_en' => 'N4 Lesson 24 Vocabulary',
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
            ['利用します', 'りようします', 'riyou shimasu', 'menggunakan', 'to use'],
            ['勤めます', 'つとめます', 'tsutomemasu', 'bekerja [di perusahaan]', 'to work [for a company]'],
            ['掛けます', 'かけます', 'kakemasu', 'duduk [di kursi]', 'to sit [on a chair]'],
            ['過ごします', 'すごします', 'sugoshimasu', 'menghabiskan (waktu), melewatkan', 'to spend [time]'],
            ['いらっしゃいます', 'いらっしゃいます', 'irasshaimasu', 'ada, pergi, datang (hormat dari います, いきます, きます)', 'to be, go, come (honorific)'],
            ['召し上がります', 'めしあがります', 'meshiagarimasu', 'makan, minum (hormat dari たべます, のみます)', 'to eat, drink (honorific)'],
            ['おっしゃいます', 'おっしゃいます', 'osshaimasu', 'berkata (hormat dari いいます)', 'to say (honorific)'],
            ['なさいます', 'なさいます', 'nasaimasu', 'melakukan (hormat dari します)', 'to do (honorific)'],
            ['ご覧になります', 'ごらんになります', 'goran ni narimasu', 'melihat (hormat dari みます)', 'to look at (honorific)'],
            ['ご存じです', 'ごぞんじです', 'gozonji desu', 'mengetahui (hormat dari しっています)', 'to know (honorific)'],
            ['あいさつ', 'あいさつ', 'aisatsu', 'salam', 'greeting'],
            ['旅館', 'りょかん', 'ryokan', 'penginapan (gaya Jepang)', 'Japanese-style inn'],
            ['バス停', 'バスてい', 'basutei', 'halte', 'bus stop'],
            ['奥様', 'おくさま', 'okusama', 'nyonya, istri (hormat dari おくさん)', 'your wife, Mrs. (honorific)'],
            ['〜様', '〜さま', 'sama', 'Yth. ~, Bapak ~, Ibu ~ (hormat dari 〜さん)', 'Mr./Ms. ~ (honorific of 〜さん)'],
            ['たまに', 'たまに', 'tamani', 'kadang-kadang', 'occasionally'],
            ['どなたでも', 'どなたでも', 'donata demo', 'siapa saja (hormat dari だれでも)', 'anyone (honorific)'],
            ['〜と いいます', '〜と いいます', 'to iimasu', 'bernama ~', 'is called ~'],

            // 会話 (percakapan)
            ['〜年〜組', '〜ねん〜くみ', 'nen kumi', 'kelas ~ (tingkat dan kelas)', 'class ~ (grade and room)'],
            ['出します', 'だします', 'dashimasu', 'menjadi [demam] (mengeluarkan demam)', 'to run [a fever]'],
            ['よろしく お伝え ください', 'よろしく おつたえ ください', 'yoroshiku otsutae kudasai', 'tolong sampaikan salam saya', 'please give my regards'],
            ['失礼いたします', 'しつれいいたします', 'shitsurei itashimasu', 'permisi (merendahkan diri dari しつれいします)', 'excuse me (humble form of しつれいします)'],

            // 読み物 (bacaan)
            ['経歴', 'けいれき', 'keireki', 'riwayat hidup, CV', 'career history, résumé'],
            ['医学部', 'いがくぶ', 'igakubu', 'fakultas kedokteran', 'faculty of medicine'],
            ['目指します', 'めざします', 'mezashimasu', 'menargetkan', 'to aim at'],
            ['進みます', 'すすみます', 'susumimasu', 'lanjut kuliah di, maju', 'to advance to, to go on to'],
            ['iPS細胞', 'あいぴーえすさいぼう', 'aipiiesu saibou', 'sel iPS', 'iPS cell'],
            ['開発します', 'かいはつします', 'kaihatsu shimasu', 'mengembangkan', 'to develop'],
            ['マウス', 'マウス', 'mausu', 'tikus percobaan', 'mouse (lab animal)'],
            ['ヒト', 'ヒト', 'hito', 'manusia', 'human'],
            ['受賞します', 'じゅしょうします', 'jushou shimasu', 'meraih (penghargaan)', 'to win [an award]'],
            ['講演会', 'こうえんかい', 'kouenkai', 'seminar, ceramah', 'lecture, seminar'],
        ];
    }
}
