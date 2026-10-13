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

class N4Lesson20Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 20 ("Bagaimana Caranya Jika Salah Jalur?"):
     * 〜場合は、〜 (jika terjadi ~) dan 〜のに、〜 (padahal ~), beserta
     * perbandingan のに / が / ても. Level N4 dipilih lewat selektor N5 / N4
     * di Tata Bahasa, Kosakata dan Referensi Tata Bahasa.
     *
     * Isi:
     *   - Unit order 20 di level N4 (unit 2-9 dan 11-19 menyusul; daftar
     *     Kosakata dan jalur Tata Bahasa hanya menampilkan unit yang ada)
     *   - Lesson Bunpou (order 0, category grammar) dengan 5 kartu:
     *       1. 〜場合は、〜 (bentuk)          4. 〜のに、〜 (kecewa／tidak puas)
     *       2. 〜場合は、〜 (aturan darurat)   5. Perbandingan のに／が／ても
     *       3. 〜のに、〜 (bentuk dan arti)
     *   - Kategori kosakata "n4-pelajaran-20" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Referensi (kosakata rumah sakit, perbandingan 場合は／のに) ada di
     * resources/js/pages/lampiran/index.vue.
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus. Penjelasan, contoh
     * kalimat dan dialog ditulis baru untuk aplikasi ini. Nama diri (nama
     * rumah sakit fiktif) sengaja tidak dimasukkan.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson20Seeder
     *
     * Kata kunci pencarian SQL memakai ASCII (kategori.slug + romaji), bukan
     * huruf Jepang (lihat docs/AGENTS.md aturan 4).
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
            ['level_id' => $level->id, 'order' => 20],
            [
                'title_id' => 'Pelajaran 20: Bagaimana Caranya Jika Salah Jalur?',
                'title_en' => 'Lesson 20: What If I Take the Wrong Route?',
                'description_id' => 'Pola 〜場合は dan 〜のに, serta perbandingan のに／が／ても.',
                'description_en' => 'The patterns 〜場合は and 〜のに, and comparing のに／が／ても.',
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
                'title_id' => '〜場合は、〜 (jika terjadi ~)',
                'title_en' => '〜場合は、〜 (in case ~)',
                'pattern' => 'V(辞書形／ない形／た形)・いA・なAな・Nの ＋ 場合《ばあい》は、〜',
                'payload' => [
                    'explanation_id' => '場合《ばあい》 berarti "keadaan, kasus". 〜場合《ばあい》は menyebut suatu keadaan yang mungkin terjadi, lalu kalimat di belakangnya menyatakan cara mengatasinya atau akibat yang akan terjadi. Karena 場合《ばあい》 adalah kata benda, cara menyambungnya sama dengan menerangkan kata benda: kata kerja (bentuk kamus, ない, た) dan kata sifat い memakai bentuk biasa; kata sifat な memakai な; kata benda memakai の.',
                    'explanation_en' => '場合《ばあい》 means "a case, a situation". 〜場合《ばあい》は names a situation that may occur, and the main clause gives how to deal with it or what will result. Because 場合《ばあい》 is a noun, it is attached the way any noun is modified: verbs (dictionary, ない, た forms) and い-adjectives take the plain form; な-adjectives take な; nouns take の.',
                    'notes_id' => [
                        'Dipakai untuk kemungkinan yang belum tentu terjadi, sehingga sering muncul di aturan, petunjuk, dan pengumuman.',
                        'Kata sifat な: 暇《ひま》な 場合《ばあい》は. Kata benda: 雨《あめ》の 場合《ばあい》は.',
                    ],
                    'notes_en' => [
                        'Used for something that may or may not happen, so it often appears in rules, instructions, and announcements.',
                        'な-adjective: 暇《ひま》な 場合《ばあい》は. Noun: 雨《あめ》の 場合《ばあい》は.',
                    ],
                    'examples' => [
                        ['ja' => '道《みち》に 迷《まよ》った 場合《ばあい》は、近《ちか》くの 人《ひと》に 聞《き》いて ください。', 'reading' => 'Michi ni mayotta baai wa, chikaku no hito ni kiite kudasai.', 'id' => 'Jika tersesat, tanyakan kepada orang di sekitar.', 'en' => 'If you get lost, please ask someone nearby.'],
                        ['ja' => '熱《ねつ》が 高《たか》い 場合《ばあい》は、病院《びょういん》へ 行《い》って ください。', 'reading' => 'Netsu ga takai baai wa, byouin e itte kudasai.', 'id' => 'Jika demamnya tinggi, pergilah ke rumah sakit.', 'en' => 'If the fever is high, please go to the hospital.'],
                        ['ja' => '雨《あめ》の 場合《ばあい》は、試合《しあい》は 中止《ちゅうし》です。', 'reading' => 'Ame no baai wa, shiai wa chuushi desu.', 'id' => 'Jika hujan, pertandingan dibatalkan.', 'en' => 'If it rains, the match is cancelled.'],
                        ['ja' => '暇《ひま》な 場合《ばあい》は、手伝《てつだ》って くれませんか。', 'reading' => 'Hima na baai wa, tetsudatte kuremasen ka.', 'id' => 'Jika kamu sedang senggang, maukah membantu?', 'en' => 'If you are free, could you help?'],
                        ['ja' => '薬《くすり》が 必要《ひつよう》な 場合《ばあい》は、受付《うけつけ》に 言《い》って ください。', 'reading' => 'Kusuri ga hitsuyou na baai wa, uketsuke ni itte kudasai.', 'id' => 'Jika memerlukan obat, katakan kepada bagian pendaftaran.', 'en' => 'If you need medicine, please tell the reception.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Jika tersesat',
                        'title_en' => 'If I get lost',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '道《みち》に 迷《まよ》った 場合《ばあい》は、どう したら いいですか。', 'reading' => 'Michi ni mayotta baai wa, dou shitara ii desu ka.', 'id' => 'Jika tersesat, sebaiknya bagaimana?', 'en' => 'If I get lost, what should I do?'],
                            ['speaker' => 'たなか', 'ja' => '近《ちか》くの 人《ひと》に 聞《き》くか、わたしに 電話《でんわ》して ください。', 'reading' => 'Chikaku no hito ni kiku ka, watashi ni denwa shite kudasai.', 'id' => 'Tanyakan kepada orang sekitar, atau telepon saya.', 'en' => 'Ask someone nearby, or call me.'],
                            ['speaker' => 'ワヒュ', 'ja' => '電話《でんわ》が つながらない 場合《ばあい》は？', 'reading' => 'Denwa ga tsunagaranai baai wa?', 'id' => 'Kalau teleponnya tidak tersambung?', 'en' => 'And if the call does not connect?'],
                            ['speaker' => 'たなか', 'ja' => '交番《こうばん》に 行《い》けば いいですよ。', 'reading' => 'Kouban ni ikeba ii desu yo.', 'id' => 'Pergi saja ke pos polisi.', 'en' => 'Just go to the police box.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => '〜場合は、〜 (aturan dan petunjuk)',
                'title_en' => '〜場合は、〜 (rules and instructions)',
                'pattern' => '〜場合《ばあい》は、〜て／ないで ください',
                'payload' => [
                    'explanation_id' => 'Pola ini sangat sering dipakai pada pengumuman, aturan, dan petunjuk pemakaian: lebih dulu disebut keadaan yang mungkin terjadi, lalu diberi tahu apa yang harus atau tidak boleh dilakukan. Kalimat belakang biasanya berbentuk 〜て ください, 〜ないで ください, atau kalimat biasa dengan ます. Dibandingkan 〜たら, 〜場合《ばあい》は terdengar lebih resmi dan lebih menekankan keadaan khusus.',
                    'explanation_en' => 'This pattern is very common in announcements, rules, and instructions: first the situation that may arise is named, then what to do or not to do is given. The main clause is usually 〜て ください, 〜ないで ください, or an ordinary ます sentence. Compared with 〜たら, 〜場合《ばあい》は sounds more formal and stresses a special case.',
                    'notes_id' => [
                        'Contoh papan pengumuman: 火事《かじ》の 場合《ばあい》は、走《はし》らないで ください。',
                        'Larangan memakai 〜ないで ください; perintah halus memakai 〜て ください.',
                    ],
                    'notes_en' => [
                        'Notice-board example: 火事《かじ》の 場合《ばあい》は、走《はし》らないで ください。',
                        'Prohibitions use 〜ないで ください; polite instructions use 〜て ください.',
                    ],
                    'examples' => [
                        ['ja' => '火事《かじ》の 場合《ばあい》は、走《はし》らないで ください。', 'reading' => 'Kaji no baai wa, hashiranaide kudasai.', 'id' => 'Jika terjadi kebakaran, jangan berlari.', 'en' => 'In case of fire, please do not run.'],
                        ['ja' => '電車《でんしゃ》が 止《と》まった 場合《ばあい》は、駅員《えきいん》に 聞《き》いて ください。', 'reading' => 'Densha ga tomatta baai wa, ekiin ni kiite kudasai.', 'id' => 'Jika kereta berhenti, tanyakan kepada petugas stasiun.', 'en' => 'If the train stops, please ask a station attendant.'],
                        ['ja' => '遅《おく》れる 場合《ばあい》は、必《かなら》ず 連絡《れんらく》して ください。', 'reading' => 'Okureru baai wa, kanarazu renraku shite kudasai.', 'id' => 'Jika akan terlambat, pastikan menghubungi kami.', 'en' => 'If you will be late, be sure to contact us.'],
                        ['ja' => '領収書《りょうしゅうしょ》が 必要《ひつよう》な 場合《ばあい》は、受付《うけつけ》で 言《い》って ください。', 'reading' => 'Ryoushuusho ga hitsuyou na baai wa, uketsuke de itte kudasai.', 'id' => 'Jika memerlukan kuitansi, katakan di bagian pendaftaran.', 'en' => 'If you need a receipt, please say so at the reception.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di kolam renang',
                        'title_en' => 'At the swimming pool',
                        'lines' => [
                            ['speaker' => '係員', 'ja' => '泳《およ》いで いる 時《とき》に 足《あし》が 痛《いた》くなった 場合《ばあい》は、すぐ プールから 出《で》て ください。', 'reading' => 'Oyoide iru toki ni ashi ga itaku natta baai wa, sugu puuru kara dete kudasai.', 'id' => 'Jika kaki terasa sakit saat berenang, segera keluar dari kolam.', 'en' => 'If your leg starts to hurt while swimming, get out of the pool at once.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい、わかりました。', 'reading' => 'Hai, wakarimashita.', 'id' => 'Baik, saya mengerti.', 'en' => 'Yes, understood.'],
                            ['speaker' => '係員', 'ja' => '出《で》られない 場合《ばあい》は、手《て》を 上《あ》げて ください。', 'reading' => 'Derarenai baai wa, te o agete kudasai.', 'id' => 'Jika tidak bisa keluar, angkat tangan.', 'en' => 'If you cannot get out, raise your hand.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい。', 'reading' => 'Hai.', 'id' => 'Baik.', 'en' => 'Yes.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => '〜のに、〜 (padahal ~)',
                'title_en' => '〜のに、〜 (although ~)',
                'pattern' => 'V・いA(ふつうけい)／なA・N(〜だ→〜な) ＋ のに、〜',
                'payload' => [
                    'explanation_id' => 'のに dipakai ketika kenyataan di kalimat belakang berbeda dari apa yang biasanya diduga dari kalimat depan: "padahal …", "walaupun …". Cara menyambungnya: kata kerja dan kata sifat い memakai bentuk biasa; kata sifat な dan kata benda memakai bentuk biasa dengan だ diganti な (春《はる》なのに).',
                    'explanation_en' => 'のに is used when the fact in the main clause differs from what you would normally expect from the first clause: "even though …", "although …". Attachment: verbs and い-adjectives take the plain form; な-adjectives and nouns take the plain form with だ changed to な (春《はる》なのに).',
                    'notes_id' => [
                        'Bagian belakang berisi fakta atau kejadian, bukan perintah atau permintaan.',
                        'Bentuk lampau: 勉強《べんきょう》したのに, 高《たか》かったのに, 休《やす》みだったのに.',
                    ],
                    'notes_en' => [
                        'The main clause states a fact or event, not a command or request.',
                        'Past forms: 勉強《べんきょう》したのに, 高《たか》かったのに, 休《やす》みだったのに.',
                    ],
                    'examples' => [
                        ['ja' => '三時間《さんじかん》も 勉強《べんきょう》したのに、テストは 悪《わる》かったです。', 'reading' => 'Sanjikan mo benkyou shita noni, tesuto wa warukatta desu.', 'id' => 'Padahal sudah belajar tiga jam, hasil tesnya jelek.', 'en' => 'Although I studied for three hours, the test went badly.'],
                        ['ja' => '春《はる》なのに、まだ 寒《さむ》いです。', 'reading' => 'Haru na noni, mada samui desu.', 'id' => 'Padahal sudah musim semi, masih dingin.', 'en' => 'Even though it is spring, it is still cold.'],
                        ['ja' => '高《たか》かったのに、すぐ 壊《こわ》れました。', 'reading' => 'Takakatta noni, sugu kowaremashita.', 'id' => 'Padahal mahal, barangnya cepat rusak.', 'en' => 'Even though it was expensive, it broke right away.'],
                        ['ja' => '急《いそ》いだのに、電車《でんしゃ》に 間《ま》に 合《あ》いませんでした。', 'reading' => 'Isoida noni, densha ni maniaimasen deshita.', 'id' => 'Padahal sudah terburu-buru, saya tidak sempat naik kereta.', 'en' => 'Although I hurried, I did not make the train.'],
                        ['ja' => '彼《かれ》は 医者《いしゃ》なのに、薬《くすり》が きらいです。', 'reading' => 'Kare wa isha na noni, kusuri ga kirai desu.', 'id' => 'Padahal dia dokter, dia tidak suka obat.', 'en' => 'Although he is a doctor, he dislikes medicine.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Nilai ujian',
                        'title_en' => 'The test score',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'テストの 点《てん》が 悪《わる》かったんです。', 'reading' => 'Tesuto no ten ga warukatta n desu.', 'id' => 'Nilai ujian saya jelek.', 'en' => 'My test score was bad.'],
                            ['speaker' => 'たなか', 'ja' => '勉強《べんきょう》したのに、ですか。', 'reading' => 'Benkyou shita noni, desu ka.', 'id' => 'Padahal sudah belajar?', 'en' => 'Even though you studied?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、三時間《さんじかん》も 勉強《べんきょう》したのに、悪《わる》かったんです。', 'reading' => 'Ee, sanjikan mo benkyou shita noni, warukatta n desu.', 'id' => 'Ya, padahal sudah belajar tiga jam, tetap jelek.', 'en' => 'Yes, even though I studied for three hours, it was bad.'],
                            ['speaker' => 'たなか', 'ja' => '残念《ざんねん》ですね。でも、次《つぎ》は きっと よく なりますよ。', 'reading' => 'Zannen desu ne. Demo, tsugi wa kitto yoku narimasu yo.', 'id' => 'Sayang sekali. Tapi lain kali pasti lebih baik.', 'en' => 'What a pity. But next time will surely be better.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => '〜のに、〜 (kecewa dan tidak puas)',
                'title_en' => '〜のに、〜 (disappointment and dissatisfaction)',
                'pattern' => '〜のに、〜 (perasaan di luar dugaan)',
                'payload' => [
                    'explanation_id' => 'のに terutama dipakai untuk mengungkapkan perasaan pembicara — kecewa, kesal, tidak puas, atau heran — terhadap sesuatu yang sudah terjadi. Dari bagian depan, orang wajar menduga hasil tertentu; kenyataan di bagian belakang ternyata berbeda sehingga muncul perasaan itu. Misalnya pada 休《やす》みなのに 働《はたら》く, dari "libur" orang menduga "tidak bekerja", tetapi kenyataannya bekerja, sehingga terasa tidak puas.',
                    'explanation_en' => 'のに is mainly used to express the speaker feelings — disappointment, annoyance, dissatisfaction, or surprise — about something that has already happened. From the first clause one would naturally expect a certain outcome; the fact in the second clause differs, which causes the feeling. For example in 休《やす》みなのに 働《はたら》く, "holiday" leads you to expect "no work", yet the person works, which feels unsatisfying.',
                    'notes_id' => [
                        'Nada kalimat cenderung menyalahkan atau menyayangkan; pakai dengan hati-hati kepada atasan.',
                        'Bila ingin sekadar menyatakan kontras tanpa perasaan, pakai が (lihat kartu 5).',
                    ],
                    'notes_en' => [
                        'The tone tends to blame or regret, so use it carefully toward superiors.',
                        'To state a plain contrast without feeling, use が (see card 5).',
                    ],
                    'examples' => [
                        ['ja' => '今日《きょう》は 祝日《しゅくじつ》なのに、仕事《しごと》が あります。', 'reading' => 'Kyou wa shukujitsu na noni, shigoto ga arimasu.', 'id' => 'Hari ini hari libur nasional, tetapi saya tetap harus bekerja.', 'en' => 'Even though today is a public holiday, I have work.'],
                        ['ja' => '気《き》を つけて 運転《うんてん》して いたのに、事故《じこ》に あいました。', 'reading' => 'Ki o tsukete unten shite ita noni, jiko ni aimashita.', 'id' => 'Padahal sudah berhati-hati menyetir, saya mengalami kecelakaan.', 'en' => 'Although I was driving carefully, I had an accident.'],
                        ['ja' => '薬《くすり》を 飲《の》んだのに、風邪《かぜ》が 治《なお》りません。', 'reading' => 'Kusuri o nonda noni, kaze ga naorimasen.', 'id' => 'Padahal sudah minum obat, flunya tidak sembuh.', 'en' => 'Even though I took medicine, my cold does not get better.'],
                        ['ja' => '先生《せんせい》に 習《なら》ったのに、答《こた》えを 忘《わす》れました。', 'reading' => 'Sensei ni naratta noni, kotae o wasuremashita.', 'id' => 'Padahal sudah diajari guru, saya lupa jawabannya.', 'en' => 'Although the teacher taught me, I forgot the answer.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Weker tidak terdengar',
                        'title_en' => 'The alarm clock',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '目覚《めざ》ましを セットしたのに、起《お》きられませんでした。', 'reading' => 'Mezamashi o setto shita noni, okiraremasen deshita.', 'id' => 'Padahal sudah memasang weker, saya tidak bisa bangun.', 'en' => 'I set the alarm clock, but I could not get up.'],
                            ['speaker' => 'たなか', 'ja' => '目覚《めざ》ましは 鳴《な》ったんですか。', 'reading' => 'Mezamashi wa natta n desu ka.', 'id' => 'Wekernya berbunyi?', 'en' => 'Did the alarm go off?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、鳴《な》ったのに、聞《き》こえなかったんです。', 'reading' => 'Ee, natta noni, kikoenakatta n desu.', 'id' => 'Ya, padahal berbunyi, saya tidak mendengarnya.', 'en' => 'Yes, it rang, yet I did not hear it.'],
                            ['speaker' => 'たなか', 'ja' => 'それは 大変《たいへん》でしたね。', 'reading' => 'Sore wa taihen deshita ne.', 'id' => 'Wah, itu repot sekali, ya.', 'en' => 'That must have been tough.'],
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => 'Perbandingan のに／が／ても',
                'title_en' => 'Comparing のに／が／ても',
                'pattern' => '〜のに ／ 〜が ／ 〜ても',
                'payload' => [
                    'explanation_id' => 'Ketiganya menyatakan kontras, tetapi berbeda sifatnya. 〜が hanya menghubungkan dua hal yang berlawanan secara netral, tanpa perasaan khusus. 〜のに menyatakan kenyataan yang sudah terjadi disertai perasaan di luar dugaan atau tidak puas. 〜ても (bentuk て + も) menyatakan pengandaian "walaupun … (tetap …)" dan dapat dipakai untuk hal yang belum terjadi, sedangkan 〜のに tidak bisa dipakai untuk pengandaian.',
                    'explanation_en' => 'All three express contrast, but they differ in nature. 〜が simply links two opposed facts neutrally, with no special feeling. 〜のに states an actual fact with a feeling of surprise or dissatisfaction. 〜ても means "even if … (still …)" and can be used for hypothetical events that have not happened yet, whereas 〜のに cannot express a supposition.',
                    'notes_id' => [
                        'Tanda ×: kalimat tidak wajar. Tanda ○: wajar.',
                        'Ingat: のに = fakta + perasaan; ても = pengandaian; が = kontras netral.',
                    ],
                    'notes_en' => [
                        '× means the sentence is unnatural; ○ means natural.',
                        'Remember: のに = fact + feeling; ても = supposition; が = neutral contrast.',
                    ],
                    'examples' => [
                        ['ja' => '○ 薬《くすり》を 飲《の》んだのに、熱《ねつ》が 下《さ》がりません。', 'reading' => 'Kusuri o nonda noni, netsu ga sagarimasen.', 'id' => 'Padahal sudah minum obat, demamnya tidak turun. (kecewa)', 'en' => 'Even though I took medicine, the fever does not go down. (dissatisfied)'],
                        ['ja' => '○ 薬《くすり》を 飲《の》んだが、熱《ねつ》が 下《さ》がりません。', 'reading' => 'Kusuri o nonda ga, netsu ga sagarimasen.', 'id' => 'Saya sudah minum obat, tetapi demamnya tidak turun. (netral)', 'en' => 'I took medicine, but the fever does not go down. (neutral)'],
                        ['ja' => '○ あした 雨《あめ》が 降《ふ》っても、試合《しあい》を します。', 'reading' => 'Ashita ame ga futte mo, shiai o shimasu.', 'id' => 'Walaupun besok hujan, pertandingan tetap dilaksanakan.', 'en' => 'Even if it rains tomorrow, we will hold the match.'],
                        ['ja' => '× あした 雨《あめ》が 降《ふ》るのに、試合《しあい》を します。', 'reading' => 'Ashita ame ga furu noni, shiai o shimasu.', 'id' => 'Tidak wajar: のに tidak bisa untuk pengandaian.', 'en' => 'Unnatural: のに cannot express a supposition.'],
                        ['ja' => '○ きょうは 休《やす》みですが、仕事《しごと》が あります。', 'reading' => 'Kyou wa yasumi desu ga, shigoto ga arimasu.', 'id' => 'Hari ini libur, tetapi ada pekerjaan. (netral)', 'en' => 'Today is a day off, but there is work. (neutral)'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Bertanya kepada guru',
                        'title_en' => 'Asking the teacher',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '先生《せんせい》、「のに」と 「が」は どう 違《ちが》いますか。', 'reading' => 'Sensei, "noni" to "ga" wa dou chigaimasu ka.', 'id' => 'Bu/Pak guru, apa beda "noni" dan "ga"?', 'en' => 'Teacher, what is the difference between "noni" and "ga"?'],
                            ['speaker' => '先生', 'ja' => '「のに」は 残念《ざんねん》な 気持《きも》ちが あります。「が」は ありません。', 'reading' => '"Noni" wa zannen na kimochi ga arimasu. "Ga" wa arimasen.', 'id' => '"Noni" mengandung perasaan kecewa. "Ga" tidak.', 'en' => '"Noni" carries a feeling of disappointment. "Ga" does not.'],
                            ['speaker' => 'ワヒュ', 'ja' => '「ても」は どうですか。', 'reading' => '"Temo" wa dou desu ka.', 'id' => 'Kalau "temo"?', 'en' => 'And "temo"?'],
                            ['speaker' => '先生', 'ja' => '「ても」は これから 起《お》こる ことにも 使《つか》えますよ。', 'reading' => '"Temo" wa korekara okoru koto ni mo tsukaemasu yo.', 'id' => '"Temo" bisa dipakai juga untuk hal yang akan terjadi.', 'en' => '"Temo" can also be used for things that will happen.'],
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
            ['slug' => 'n4-pelajaran-20'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 20',
                'name_en' => 'N4 Lesson 20 Vocabulary',
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
            ['信じます', 'しんじます', 'shinjimasu', 'percaya', 'to believe, to trust'],
            ['キャンセルします', 'キャンセルします', 'kyanseru shimasu', 'membatalkan', 'to cancel'],
            ['知らせます', 'しらせます', 'shirasemasu', 'memberitahukan', 'to inform, to notify'],
            ['保証書', 'ほしょうしょ', 'hoshousho', 'surat garansi', 'warranty card'],
            ['領収書', 'りょうしゅうしょ', 'ryoushuusho', 'kuitansi', 'receipt'],
            ['キャンプ', 'キャンプ', 'kyanpu', 'kemah', 'camp'],
            ['中止', 'ちゅうし', 'chuushi', 'pembatalan', 'cancellation, suspension'],
            ['点', 'てん', 'ten', 'poin, nilai', 'point, score'],
            ['梅', 'うめ', 'ume', 'bunga plum', 'plum (blossom)'],
            ['110番', 'ひゃくとおばん', 'hyakutouban', 'nomor telepon polisi untuk saat darurat', 'emergency phone number for the police'],
            ['119番', 'ひゃくじゅうきゅうばん', 'hyakujuukyuuban', 'nomor telepon pemadam kebakaran dan penanggulangan bencana untuk saat darurat', 'emergency phone number for fire and ambulance'],
            ['急に', 'きゅうに', 'kyuu ni', 'mendadak, tiba-tiba', 'suddenly'],
            ['無理に', 'むりに', 'muri ni', 'dengan paksa', 'by force, forcibly'],
            ['楽しみにしています', 'たのしみにしています', 'tanoshimi ni shite imasu', 'menantikan, menikmati', 'to look forward to'],
            ['以上です。', 'いじょうです。', 'ijou desu', 'Sekian.', 'That is all.'],

            // 会話 (percakapan)
            ['係員', 'かかりいん', 'kakariin', 'staf, petugas', 'staff member, attendant'],
            ['コース', 'コース', 'koosu', 'jalur, jalan, rute', 'course, route'],
            ['スタート', 'スタート', 'sutaato', 'mulai', 'start'],
            ['―位', 'い', 'i', 'juara ke-', 'place (ranking)'],
            ['優勝します', 'ゆうしょうします', 'yuushou shimasu', 'menang, juara', 'to win a championship'],

            // 読み物 (bacaan)
            ['悩み', 'なやみ', 'nayami', 'masalah, kekhawatiran', 'worry, trouble'],
            ['目覚まし[時計]', 'めざまし[どけい]', 'mezamashi', 'weker', 'alarm clock'],
            ['目が覚めます', 'めがさめます', 'me ga samemasu', 'terbangun', 'to wake up'],
            ['大学生', 'だいがくせい', 'daigakusei', 'mahasiswa', 'university student'],
            ['回答', 'かいとう', 'kaitou', 'jawaban (〜します: menjawab)', 'answer, reply'],
            ['鳴ります', 'なります', 'narimasu', 'berbunyi', 'to ring, to sound'],
            ['セットします', 'セットします', 'setto shimasu', 'memasang, mengatur', 'to set'],
            ['それでも', 'それでも', 'sore demo', 'masih, meskipun begitu', 'even so, still'],
        ];
    }
}
