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

class N4Lesson21Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 21 ("Baru Minggu Lalu Diperbaiki, Tapi...").
     * Level N4 dibuat oleh N4Lesson1Seeder; seeder ini menambah unit order 21.
     * Nomor pelajaran N4 yang belum ada tidak apa-apa: urutan tampil mengikuti
     * Unit.order sehingga pelajaran lain terselip sendiri saat ditambahkan.
     *
     * Isi:
     *   - Unit order 21 di level N4
     *   - Lesson Bunpou (order 0, category grammar) dengan 6 kartu:
     *       1. Ｖる ところです        4. Ｖた ばかりです
     *       2. Ｖて いる ところです   5. 〜はずです
     *       3. Ｖた ところです        6. 〜ところなんです／〜ばかりなのに
     *   - Kategori kosakata "n4-pelajaran-21" + 32 kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus. Penjelasan, contoh
     * kalimat dan dialog ditulis baru untuk aplikasi ini. Daftar asal-usul kata
     * katakana dari halaman referensi ada di Referensi Tata Bahasa, bukan di sini.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson21Seeder
     *
     * Kata kunci pencarian SQL memakai ASCII (kategori.slug + romaji), bukan
     * huruf Jepang (lihat docs/AGENTS.md aturan 4).
     */
    public function run(): void
    {
        $level = Level::where('code', 'N4')->first();

        if (! $level) {
            $this->call(N4Lesson1Seeder::class);
            $level = Level::where('code', 'N4')->firstOrFail();
        }

        $unit = Unit::updateOrCreate(
            ['level_id' => $level->id, 'order' => 21],
            [
                'title_id' => 'Pelajaran 21: Baru Minggu Lalu Diperbaiki, Tapi...',
                'title_en' => 'Lesson 21: It Was Only Repaired Last Week, but...',
                'description_id' => 'ところです (akan／sedang／baru saja), ばかりです, dan はずです.',
                'description_en' => 'ところです (about to／in the middle of／has just), ばかりです, and はずです.',
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
                'title_id' => 'Vる ところです (baru akan mulai)',
                'title_en' => 'Vる ところです (about to do)',
                'pattern' => 'Ｖる（辞書形《じしょけい》）＋ ところです',
                'payload' => [
                    'explanation_id' => 'Kata kerja bentuk kamus + ところです menyatakan bahwa perbuatan itu "baru akan dimulai", artinya pembicara berada tepat sebelum perbuatan. Biasanya dipakai bersama keterangan seperti これから, 今《いま》から, ちょうど. Pola ini sering dipakai untuk menjawab pertanyaan "sudah ~?" dengan jawaban "belum, baru mau ~".',
                    'explanation_en' => 'Dictionary form + ところです says that the action is "just about to begin": the speaker is right before the action. It is usually used with adverbs such as これから, 今《いま》から, ちょうど. It is often used to answer "have you ~ yet?" with "not yet, I am about to ~".',
                    'notes_id' => [
                        'ところ aslinya berarti "tempat／titik"; di sini ia menunjuk titik waktu dalam sebuah perbuatan.',
                        'Bandingkan Ｖる つもりです (berniat): ところです hanya untuk saat yang sangat dekat.',
                    ],
                    'notes_en' => [
                        'ところ originally means "place／point"; here it points to a point in time within an action.',
                        'Compare Ｖる つもりです (intending to): ところです is only for a moment very close to now.',
                    ],
                    'examples' => [
                        ['ja' => '晩《ばん》ごはんは もう 食《た》べましたか。……いいえ、これから 食《た》べる ところです。', 'reading' => 'Bangohan wa mou tabemashita ka. ...Iie, korekara taberu tokoro desu.', 'id' => 'Sudah makan malam? ……Belum, baru mau makan.', 'en' => 'Have you eaten dinner yet? ...No, I am just about to.'],
                        ['ja' => '試験《しけん》は もう 始《はじ》まりましたか。……いいえ、今《いま》から 始《はじ》まる ところです。', 'reading' => 'Shiken wa mou hajimarimashita ka. ...Iie, ima kara hajimaru tokoro desu.', 'id' => 'Ujian sudah dimulai? ……Belum, baru akan dimulai sekarang.', 'en' => 'Has the exam started? ...No, it is just about to start.'],
                        ['ja' => '今《いま》から 出《で》かける ところです。', 'reading' => 'Ima kara dekakeru tokoro desu.', 'id' => 'Saya baru mau pergi sekarang.', 'en' => 'I am just about to go out.'],
                        ['ja' => 'ちょうど 電話《でんわ》を かける ところでした。', 'reading' => 'Choudo denwa o kakeru tokoro deshita.', 'id' => 'Saya tadi baru mau menelepon.', 'en' => 'I was just about to make a phone call.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Sudah selesai?',
                        'title_en' => 'Is it done?',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'ワヒュさん、レポートは もう 書《か》きましたか。', 'reading' => 'Wahyu-san, repooto wa mou kakimashita ka.', 'id' => 'Wahyu, laporannya sudah ditulis?', 'en' => 'Wahyu, have you written the report yet?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、これから 書《か》く ところです。', 'reading' => 'Iie, korekara kaku tokoro desu.', 'id' => 'Belum, baru mau saya tulis.', 'en' => 'No, I am just about to write it.'],
                            ['speaker' => 'たなか', 'ja' => 'きょうの 午後《ごご》までに お願《ねが》いしますね。', 'reading' => 'Kyou no gogo made ni onegai shimasu ne.', 'id' => 'Mohon selesai sebelum siang ini, ya.', 'en' => 'Please have it done by this afternoon.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい、わかりました。', 'reading' => 'Hai, wakarimashita.', 'id' => 'Baik, saya mengerti.', 'en' => 'Yes, understood.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => 'Vて いる ところです (sedang)',
                'title_en' => 'Vて いる ところです (in the middle of)',
                'pattern' => 'Ｖて いる ＋ ところです',
                'payload' => [
                    'explanation_id' => 'Bentuk 〜て いる + ところです menyatakan bahwa perbuatan "sedang berlangsung" tepat pada saat berbicara. Biasanya bersama 今《いま》. Kalimat ini sering dipakai untuk melaporkan perkembangan kepada orang lain: "sekarang sedang ~".',
                    'explanation_en' => '〜て いる + ところです says that the action is "in progress" right at the moment of speaking. It usually goes with 今《いま》. It is often used to report progress to someone else: "I am in the middle of ~ right now".',
                    'notes_id' => [
                        'Bandingkan 〜て います biasa, yang bisa berarti kebiasaan atau keadaan; 〜て いる ところです hanya berarti sedang dikerjakan saat ini.',
                        'Dalam percakapan sering ditambah んです: 調《しら》べて いる ところなんです.',
                    ],
                    'notes_en' => [
                        'Compare plain 〜て います, which can mean a habit or a state; 〜て いる ところです only means that the action is being done right now.',
                        'In conversation んです is often added: 調《しら》べて いる ところなんです.',
                    ],
                    'examples' => [
                        ['ja' => '故障《こしょう》の 原因《げんいん》は わかりましたか。……いいえ、今《いま》 調《しら》べて いる ところです。', 'reading' => 'Koshou no gen\'in wa wakarimashita ka. ...Iie, ima shirabete iru tokoro desu.', 'id' => 'Apakah penyebab kerusakan sudah diketahui? ……Belum, sekarang sedang diperiksa.', 'en' => 'Do you know the cause of the breakdown? ...Not yet, we are checking it now.'],
                        ['ja' => 'もしもし、今《いま》 何《なに》を して いますか。……ごはんを 作《つく》って いる ところです。', 'reading' => 'Moshimoshi, ima nani o shite imasu ka. ...Gohan o tsukutte iru tokoro desu.', 'id' => 'Halo, sekarang sedang apa? ……Sedang memasak.', 'en' => 'Hello, what are you doing now? ...I am in the middle of cooking.'],
                        ['ja' => '今《いま》、メールを 書《か》いて いる ところです。', 'reading' => 'Ima, meeru o kaite iru tokoro desu.', 'id' => 'Sekarang saya sedang menulis email.', 'en' => 'I am in the middle of writing an email now.'],
                        ['ja' => '会議《かいぎ》の 資料《しりょう》を コピーして いる ところです。', 'reading' => 'Kaigi no shiryou o kopii shite iru tokoro desu.', 'id' => 'Saya sedang memfotokopi bahan rapat.', 'en' => 'I am in the middle of copying the meeting materials.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menelepon rekan kerja',
                        'title_en' => 'Calling a colleague',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'もしもし、ワヒュさん、今《いま》 どこですか。', 'reading' => 'Moshimoshi, Wahyu-san, ima doko desu ka.', 'id' => 'Halo, Wahyu, sekarang di mana?', 'en' => 'Hello, Wahyu, where are you now?'],
                            ['speaker' => 'ワヒュ', 'ja' => '駅《えき》に 向《む》かって いる ところです。', 'reading' => 'Eki ni mukatte iru tokoro desu.', 'id' => 'Saya sedang menuju stasiun.', 'en' => 'I am on my way to the station.'],
                            ['speaker' => 'たなか', 'ja' => 'じゃ、お客《きゃく》さんは もう 来《き》て いますよ。', 'reading' => 'Ja, okyaku-san wa mou kite imasu yo.', 'id' => 'Kalau begitu, tamunya sudah datang, lho.', 'en' => 'Then the guest is already here.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません。あと 十分《じゅっぷん》で 着《つ》きます。', 'reading' => 'Sumimasen. Ato juppun de tsukimasu.', 'id' => 'Maaf. Sepuluh menit lagi saya tiba.', 'en' => 'Sorry. I will arrive in ten minutes.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => 'Vた ところです (baru saja selesai)',
                'title_en' => 'Vた ところです (has just done)',
                'pattern' => 'Ｖた ＋ ところです',
                'payload' => [
                    'explanation_id' => 'Kata kerja bentuk た + ところです menyatakan bahwa perbuatan "baru saja selesai" pada saat berbicara. Biasanya bersama たった今《いま》 atau 今《いま》 ... ところ. Seperti dua pola sebelumnya, ところ memandang titik waktu secara tepat: jaraknya dengan saat berbicara sangat dekat.',
                    'explanation_en' => 'Past form + ところです says that the action "has just finished" at the moment of speaking. It usually goes with たった今《いま》 or 今《いま》. Like the previous two patterns, ところ looks at a precise point in time: the gap to the moment of speaking is very small.',
                    'notes_id' => [
                        'Urutan tiga pola: Ｖる ところ (sebelum) → Ｖて いる ところ (sedang) → Ｖた ところ (sesudah).',
                        'Untuk menjawab "ada atau tidak ada seseorang": あ、たった今《いま》 帰《かえ》った ところです ("baru saja pulang").',
                    ],
                    'notes_en' => [
                        'The order of the three patterns: Ｖる ところ (before) → Ｖて いる ところ (during) → Ｖた ところ (just after).',
                        'To answer whether someone is in: あ、たった今《いま》 帰《かえ》った ところです ("he just went home").',
                    ],
                    'examples' => [
                        ['ja' => '山田《やまだ》さんは いますか。……あ、たった今《いま》 帰《かえ》った ところです。', 'reading' => 'Yamada-san wa imasu ka. ...A, tattaima kaetta tokoro desu.', 'id' => 'Apakah Yamada ada? ……Ah, baru saja pulang.', 'en' => 'Is Mr. Yamada in? ...Ah, he has just left.'],
                        ['ja' => 'たった今《いま》 バスが 出《で》た ところです。', 'reading' => 'Tattaima basu ga deta tokoro desu.', 'id' => 'Bus baru saja berangkat.', 'en' => 'The bus has just left.'],
                        ['ja' => '今《いま》、駅《えき》に 着《つ》いた ところです。', 'reading' => 'Ima, eki ni tsuita tokoro desu.', 'id' => 'Saya baru saja tiba di stasiun.', 'en' => 'I have just arrived at the station.'],
                        ['ja' => 'ちょうど 掃除《そうじ》が 終《お》わった ところです。', 'reading' => 'Choudo souji ga owatta tokoro desu.', 'id' => 'Pembersihan baru saja selesai.', 'en' => 'The cleaning has just finished.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mencari rekan kerja',
                        'title_en' => 'Looking for a colleague',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません、渡辺《わたなべ》さんは いますか。', 'reading' => 'Sumimasen, Watanabe-san wa imasu ka.', 'id' => 'Permisi, apakah Watanabe ada?', 'en' => 'Excuse me, is Mr. Watanabe in?'],
                            ['speaker' => 'たなか', 'ja' => 'あ、たった今《いま》 帰《かえ》った ところです。', 'reading' => 'A, tattaima kaetta tokoro desu.', 'id' => 'Ah, dia baru saja pulang.', 'en' => 'Ah, he has just gone home.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'そうですか。まだ 近《ちか》くに いますか。', 'reading' => 'Sou desu ka. Mada chikaku ni imasu ka.', 'id' => 'Begitu. Apakah dia masih di dekat sini?', 'en' => 'I see. Is he still nearby?'],
                            ['speaker' => 'たなか', 'ja' => 'エレベーターの 所《ところ》に いるかもしれませんよ。', 'reading' => 'Erebeetaa no tokoro ni iru kamo shiremasen yo.', 'id' => 'Mungkin dia masih di dekat lift.', 'en' => 'He may still be by the elevator.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'Vた ばかりです (baru)',
                'title_en' => 'Vた ばかりです (only just)',
                'pattern' => 'Ｖた ＋ ばかりです',
                'payload' => [
                    'explanation_id' => 'Kata kerja bentuk た + ばかりです juga berarti "baru saja", tetapi ini perasaan pembicara bahwa waktu sejak perbuatan itu belum lama. Lama waktu sebenarnya tidak penting: seminggu atau sebulan lalu pun boleh dibilang "baru" bila pembicara merasa begitu. Inilah beda dengan ところです, yang hanya untuk saat yang benar-benar sangat dekat.',
                    'explanation_en' => 'Past form + ばかりです also means "just", but it is the speaker\'s feeling that not much time has passed since the action. The actual length of time does not matter: even a week or a month ago can be "recent" if the speaker feels so. This is the difference from ところです, which is only for a truly very recent moment.',
                    'notes_id' => [
                        'さっき 食《た》べた ところです (beberapa menit lalu) dan 先月《せんげつ》 入《はい》った ばかりです (bulan lalu) — hanya ばかり yang cocok untuk yang kedua.',
                        'Sering dipakai untuk memberi alasan: 日本《にほん》に 来《き》た ばかりですから、まだ よく わかりません.',
                    ],
                    'notes_en' => [
                        'さっき 食《た》べた ところです (a few minutes ago) and 先月《せんげつ》 入《はい》った ばかりです (last month) — only ばかり suits the second.',
                        'Often used to give a reason: 日本《にほん》に 来《き》た ばかりですから、まだ よく わかりません.',
                    ],
                    'examples' => [
                        ['ja' => 'さっき 昼《ひる》ごはんを 食《た》べた ばかりです。', 'reading' => 'Sakki hirugohan o tabeta bakari desu.', 'id' => 'Tadi saya baru makan siang.', 'en' => 'I have just had lunch.'],
                        ['ja' => 'キムさんは 先月《せんげつ》 この 会社《かいしゃ》に 入《はい》った ばかりです。', 'reading' => 'Kimu-san wa sengetsu kono kaisha ni haitta bakari desu.', 'id' => 'Kim baru masuk perusahaan ini bulan lalu.', 'en' => 'Kim only joined this company last month.'],
                        ['ja' => '日本《にほん》に 来《き》た ばかりですから、まだ 友達《ともだち》が いません。', 'reading' => 'Nihon ni kita bakari desu kara, mada tomodachi ga imasen.', 'id' => 'Karena baru datang ke Jepang, saya belum punya teman.', 'en' => 'Since I have only just come to Japan, I do not have friends yet.'],
                        ['ja' => 'この 車《くるま》は 去年《きょねん》 買《か》った ばかりです。', 'reading' => 'Kono kuruma wa kyonen katta bakari desu.', 'id' => 'Mobil ini baru saya beli tahun lalu.', 'en' => 'I only bought this car last year.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Bagaimana pekerjaannya?',
                        'title_en' => 'How is the new job?',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '新《あたら》しい 仕事《しごと》は どうですか。', 'reading' => 'Atarashii shigoto wa dou desu ka.', 'id' => 'Bagaimana pekerjaan barunya?', 'en' => 'How is the new job?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'まだ よく わかりません。先月《せんげつ》 入《はい》った ばかりなんです。', 'reading' => 'Mada yoku wakarimasen. Sengetsu haitta bakari na n desu.', 'id' => 'Saya belum begitu paham. Saya baru masuk bulan lalu.', 'en' => 'I do not really know yet. I only joined last month.'],
                            ['speaker' => 'サリ', 'ja' => 'そうですか。わからない ことは 聞《き》いて くださいね。', 'reading' => 'Sou desu ka. Wakaranai koto wa kiite kudasai ne.', 'id' => 'Begitu. Kalau ada yang tidak paham, tanyakan, ya.', 'en' => 'I see. Please ask if there is anything you do not understand.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ありがとうございます。', 'reading' => 'Arigatou gozaimasu.', 'id' => 'Terima kasih.', 'en' => 'Thank you.'],
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => '〜はずです (pasti／seharusnya)',
                'title_en' => '〜はずです (should be, must be)',
                'pattern' => 'ふつうけい ＋ はずです',
                'payload' => [
                    'explanation_id' => 'はずです dipakai ketika pembicara yakin akan sesuatu karena ada dasar atau bukti. Bentuk sebelumnya: kata kerja bentuk kamus／ない, kata sifat い apa adanya, kata sifat な + な, kata benda + の. Contoh: bukti "kemarin dia menelepon" membuat pembicara menyimpulkan "dia pasti datang hari ini". Karena ada dasar logis, はずです lebih kuat daripada でしょう, dan tidak dipakai untuk niat sendiri.',
                    'explanation_en' => 'はずです is used when the speaker is sure of something because there is a basis or evidence. What comes before it: verb dictionary／ない form, い-adjective as it is, な-adjective + な, noun + の. Example: the evidence "he phoned yesterday" leads the speaker to conclude "he should come today". Because there is a logical basis, はずです is stronger than でしょう, and it is not used for your own intentions.',
                    'notes_id' => [
                        'Negatif: 〜ない はずです ("seharusnya tidak ~"). Berbeda dari 〜はずが ありません ("tidak mungkin ~").',
                        'Kalau kesimpulan ternyata salah, dipakai 〜はずなのに: 来《く》る はずなのに、来《こ》ない ("seharusnya datang, tetapi tidak").',
                    ],
                    'notes_en' => [
                        'Negative: 〜ない はずです ("should not ~"). Different from 〜はずが ありません ("cannot possibly ~").',
                        'When the conclusion turns out wrong, 〜はずなのに is used: 来《く》る はずなのに、来《こ》ない ("he should be coming, but he does not").',
                    ],
                    'examples' => [
                        ['ja' => 'ワヒュさんは きょう 来《く》る はずです。きのう 電話《でんわ》が ありましたから。', 'reading' => 'Wahyu-san wa kyou kuru hazu desu. Kinou denwa ga arimashita kara.', 'id' => 'Wahyu pasti datang hari ini. Soalnya kemarin ada telepon.', 'en' => 'Wahyu should come today. He phoned yesterday.'],
                        ['ja' => 'この 店《みせ》の ラーメンは おいしい はずです。いつも 人《ひと》が 並《なら》んで いますから。', 'reading' => 'Kono mise no raamen wa oishii hazu desu. Itsumo hito ga narande imasu kara.', 'id' => 'Mi di toko ini pasti enak. Soalnya selalu ada antrean.', 'en' => 'The ramen at this shop must be good. There is always a queue.'],
                        ['ja' => 'サリさんは 日本語《にほんご》が 上手《じょうず》な はずです。三年《さんねん》 勉強《べんきょう》しましたから。', 'reading' => 'Sari-san wa nihongo ga jouzu na hazu desu. Sannen benkyou shimashita kara.', 'id' => 'Sari pasti pandai bahasa Jepang. Soalnya dia belajar tiga tahun.', 'en' => 'Sari should be good at Japanese. She studied for three years.'],
                        ['ja' => 'きょうは 日曜日《にちようび》ですから、銀行《ぎんこう》は 休《やす》みの はずです。', 'reading' => 'Kyou wa nichiyoubi desu kara, ginkou wa yasumi no hazu desu.', 'id' => 'Hari ini Minggu, jadi bank seharusnya libur.', 'en' => 'It is Sunday, so the bank should be closed.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Apakah dia datang?',
                        'title_en' => 'Is he coming?',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'ワヒュさんは まだ 来《き》ませんね。', 'reading' => 'Wahyu-san wa mada kimasen ne.', 'id' => 'Wahyu belum datang, ya.', 'en' => 'Wahyu has not come yet.'],
                            ['speaker' => 'たなか', 'ja' => 'もうすぐ 来《く》る はずですよ。さっき 駅《えき》から 電話《でんわ》が ありましたから。', 'reading' => 'Mousugu kuru hazu desu yo. Sakki eki kara denwa ga arimashita kara.', 'id' => 'Sebentar lagi pasti datang. Tadi ada telepon dari stasiun.', 'en' => 'He should be here soon. There was a call from the station just now.'],
                            ['speaker' => 'サリ', 'ja' => 'そうですか。じゃ、待《ま》ちましょう。', 'reading' => 'Sou desu ka. Ja, machimashou.', 'id' => 'Begitu. Kalau begitu kita tunggu.', 'en' => 'I see. Then let us wait.'],
                        ],
                    ],
                ],
            ],

            // 6 ------------------------------------------------------------
            [
                'title_id' => '〜ところなんです／〜ばかりなのに (disambung ke pola lain)',
                'title_en' => '〜ところなんです／〜ばかりなのに (linked to other patterns)',
                'pattern' => '〜ところ ／ 〜ばかり ＋ なんです・なのに・ですが',
                'payload' => [
                    'explanation_id' => '〜ところ dan 〜ばかり adalah kata benda, jadi ia dapat disambung ke pola lain seperti kalimat benda: 〜ところなんです (menjelaskan alasan), 〜ところですが (membuka pembicaraan), 〜ばかりなのに ("padahal baru ~"). Pola ini sering dipakai untuk menolak dengan sopan atau mengeluh: "Maaf, saya baru mau pergi", "padahal baru dibeli, sudah rusak".',
                    'explanation_en' => '〜ところ and 〜ばかり are nouns, so they can be linked to other patterns like noun sentences: 〜ところなんです (giving a reason), 〜ところですが (opening a conversation), 〜ばかりなのに ("even though it is only just ~"). This is often used to decline politely or to complain: "Sorry, I am just about to leave", "it was only just bought and already broke".',
                    'notes_id' => [
                        'Menjawab telepon: すみません。今《いま》から 出《で》かける ところなんです ("Maaf, saya baru mau pergi").',
                        'Keluhan: 先週《せんしゅう》 買《か》った ばかりなのに、調子《ちょうし》が おかしいです.',
                    ],
                    'notes_en' => [
                        'Answering a call: すみません。今《いま》から 出《で》かける ところなんです ("Sorry, I am just about to go out").',
                        'A complaint: 先週《せんしゅう》 買《か》った ばかりなのに、調子《ちょうし》が おかしいです.',
                    ],
                    'examples' => [
                        ['ja' => 'もしもし、今《いま》 いいですか。……すみません、今《いま》から 出《で》かける ところなんです。', 'reading' => 'Moshimoshi, ima ii desu ka. ...Sumimasen, ima kara dekakeru tokoro na n desu.', 'id' => 'Halo, apakah sekarang boleh mengganggu? ……Maaf, saya baru mau pergi.', 'en' => 'Hello, is now a good time? ...Sorry, I am just about to go out.'],
                        ['ja' => 'この スマホは 先週《せんしゅう》 買《か》った ばかりなのに、すぐ 壊《こわ》れました。', 'reading' => 'Kono sumaho wa senshuu katta bakari na noni, sugu kowaremashita.', 'id' => 'Ponsel ini baru saya beli minggu lalu, tetapi sudah rusak.', 'en' => 'I only bought this phone last week, yet it broke right away.'],
                        ['ja' => 'ごはんを 食《た》べた ところですから、まだ おなかが いっぱいです。', 'reading' => 'Gohan o tabeta tokoro desu kara, mada onaka ga ippai desu.', 'id' => 'Karena baru saja makan, perut saya masih kenyang.', 'en' => 'I have just eaten, so I am still full.'],
                        ['ja' => '今《いま》 家《いえ》を 出《で》る ところですが、五分《ごふん》ぐらい 遅《おく》れます。', 'reading' => 'Ima ie o deru tokoro desu ga, gofun gurai okuremasu.', 'id' => 'Saya baru mau keluar rumah, kira-kira terlambat lima menit.', 'en' => 'I am just leaving the house, but I will be about five minutes late.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Telepon ke pusat servis',
                        'title_en' => 'A call to the service centre',
                        'lines' => [
                            ['speaker' => 'スタッフ', 'ja' => 'はい、ガスサービスセンターです。', 'reading' => 'Hai, gasu saabisu sentaa desu.', 'id' => 'Halo, pusat layanan gas.', 'en' => 'Hello, this is the gas service centre.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'あのう、ガスレンジの 具合《ぐあい》が よくないんですが……。', 'reading' => 'Anou, gasurenji no guai ga yokunai n desu ga......', 'id' => 'Anu, kondisi kompor gas kurang baik...', 'en' => 'Um, the gas stove is not working well...'],
                            ['speaker' => 'スタッフ', 'ja' => 'どう なんですか。', 'reading' => 'Dou na n desu ka.', 'id' => 'Bagaimana kondisinya?', 'en' => 'What is the problem?'],
                            ['speaker' => 'ワヒュ', 'ja' => '先週《せんしゅう》 直《なお》して もらった ばかりなのに、火《ひ》が すぐ 消《き》えるんです。', 'reading' => 'Senshuu naoshite moratta bakari na noni, hi ga sugu kieru n desu.', 'id' => 'Baru minggu lalu diperbaiki, tetapi apinya cepat padam.', 'en' => 'It was only repaired last week, yet the flame goes out right away.'],
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
            ['slug' => 'n4-pelajaran-21'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 21',
                'name_en' => 'N4 Lesson 21 Vocabulary',
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
            ['渡します', 'わたします', 'watashimasu', 'menyerahkan', 'to hand over'],
            ['帰って来ます', 'かえってきます', 'kaettekimasu', 'pulang', 'to come back home'],
            ['出ます', 'でます', 'demasu', 'berangkat [bus]', 'to leave, to depart [a bus]'],
            ['届きます', 'とどきます', 'todokimasu', 'sampai [barang]', 'to arrive [a package]'],
            ['入学します', 'にゅうがくします', 'nyuugakushimasu', 'masuk [universitas]', 'to enter [a university]'],
            ['卒業します', 'そつぎょうします', 'sotsugyoushimasu', 'lulus, tamat [dari universitas]', 'to graduate [from a university]'],
            ['焼きます', 'やきます', 'yakimasu', 'membakar', 'to grill, to bake'],
            ['焼けます', 'やけます', 'yakemasu', 'dibakar, matang [roti, daging]', 'to be baked, to be grilled [bread, meat]'],
            ['留守', 'るす', 'rusu', 'ketidakadaan (tidak di rumah)', 'absence (not at home)'],
            ['宅配便', 'たくはいびん', 'takuhaibin', 'titipan kilat, kiriman paket', 'home delivery service'],
            ['原因', 'げんいん', 'gen\'in', 'sebab', 'cause'],
            ['こちら', 'こちら', 'kochira', 'sini', 'this way, here'],
            ['〜の所', '〜のところ', 'no tokoro', 'tempat ~', 'the place of ~'],
            ['半年', 'はんとし', 'hantoshi', 'setengah tahun', 'half a year'],
            ['ちょうど', 'ちょうど', 'choudo', 'pas, tepat', 'exactly, just'],
            ['たった今', 'たったいま', 'tattaima', 'baru saja (dipakai dengan bentuk lampau)', 'just now (used with the past tense)'],
            ['今 いいですか。', 'いま いいですか。', 'ima ii desu ka', 'Sekarang boleh mengganggu?', 'Is now a good time?'],

            // 会話 (percakapan)
            ['ガスサービスセンター', 'ガスサービスセンター', 'gasu saabisu sentaa', 'pusat pelayanan gas', 'gas service centre'],
            ['ガスレンジ', 'ガスレンジ', 'gasurenji', 'kompor gas', 'gas stove'],
            ['具合', 'ぐあい', 'guai', 'kondisi, keadaan', 'condition'],
            ['申し訳ありません。', 'もうしわけありません。', 'moushiwake arimasen', 'Maaf.', 'I am very sorry.'],
            ['どちら様でしょうか。', 'どちらさまでしょうか。', 'dochirasama deshou ka', 'Atas nama siapa?', 'May I ask who is calling?'],
            ['お待たせしました。', 'おまたせしました。', 'omatase shimashita', 'Maaf lama menunggu.', 'Sorry to have kept you waiting.'],
            ['向かいます', 'むかいます', 'mukaimasu', 'menuju', 'to head for'],

            // 読み物 (bacaan)
            ['ついて います', 'ついています', 'tsuite imasu', 'mujur, untung', 'to be lucky'],
            ['床', 'ゆか', 'yuka', 'lantai', 'floor'],
            ['転びます', 'ころびます', 'korobimasu', 'jatuh, terjatuh', 'to fall over'],
            ['ベル', 'ベル', 'beru', 'bel', 'bell'],
            ['鳴ります', 'なります', 'narimasu', 'berbunyi', 'to ring, to sound'],
            ['慌てて', 'あわてて', 'awatete', 'tergesa-gesa', 'in a hurry, flustered'],
            ['順番に', 'じゅんばんに', 'junban ni', 'sesuai dengan urutan', 'in order, in turn'],
            ['出来事', 'できごと', 'dekigoto', 'peristiwa, kejadian', 'event, incident'],
        ];
    }
}
