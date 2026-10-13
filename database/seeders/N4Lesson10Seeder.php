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

class N4Lesson10Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 10 ("Ada Tempat yang Bagus?"): bentuk syarat 〜ば,
     * 〜なら, meminta saran dengan どう すれば いいですか, dan pertanyaan
     * negatif 〜は ありませんか. Level N4 dipilih lewat selektor N5 / N4 di
     * Tata Bahasa, Kosakata dan Referensi Tata Bahasa.
     *
     * Isi:
     *   - Unit order 10 di level N4 (unit 2-9 menyusul pada patch berikutnya;
     *     daftar Kosakata dan jalur Tata Bahasa hanya menampilkan unit yang ada)
     *   - Lesson Bunpou (order 0, category grammar) dengan 7 kartu:
     *       1. Membentuk bentuk syarat   5. Kata tanya + Vば いいですか
     *       2. 〜ば、〜 (syarat → hasil)  6. N なら、〜
     *       3. 〜ば、〜 (menanggapi)      7. 〜は ありませんか
     *       4. Perbandingan 〜ば / 〜と / 〜たら
     *   - Kategori kosakata "n4-pelajaran-10" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus. Penjelasan, contoh
     * kalimat dan dialog ditulis baru untuk aplikasi ini. Nama diri (nama
     * tempat wisata, nama sekolah/toko fiktif) sengaja tidak dimasukkan,
     * sama seperti di N5 dan N4 Pelajaran 1.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson10Seeder
     *
     * Kata kunci pencarian SQL memakai ASCII (kategori.slug + romaji), bukan
     * huruf Jepang (lihat docs/AGENTS.md aturan 4).
     */
    public function run(): void
    {
        // Sama persis dengan N4Lesson1Seeder; firstOrCreate agar seeder ini
        // bisa jalan sendiri tanpa menimpa teks level yang sudah ada.
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
            ['level_id' => $level->id, 'order' => 10],
            [
                'title_id' => 'Pelajaran 10: Ada Tempat yang Bagus?',
                'title_en' => 'Lesson 10: Is There a Good Place?',
                'description_id' => 'Bentuk syarat 〜ば dan 〜なら, meminta saran dengan どう すれば いいですか, dan pertanyaan negatif 〜は ありませんか.',
                'description_en' => 'The conditional 〜ば and 〜なら, asking for advice with どう すれば いいですか, and the negative question 〜は ありませんか.',
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
                'title_id' => 'Membentuk bentuk syarat (〜ば／〜ければ／〜なら)',
                'title_en' => 'Forming the conditional (〜ば／〜ければ／〜なら)',
                'pattern' => 'Bentuk syarat: V〜ば ／ いA〜ければ ／ なA・N〜なら',
                'payload' => [
                    'explanation_id' => 'Bentuk syarat (条件形《じょうけんけい》) dibuat berbeda untuk tiap jenis kata. Kata kerja kelompok I: bunyi terakhir bentuk ます (kolom い) diganti bunyi kolom え, lalu tambahkan ば (書《か》きます → 書《か》けば). Kelompok II: buang ます dan tambahkan れば (食《た》べます → 食《た》べれば). Kelompok III: します → すれば, 来《き》ます → 来《く》れば. Bentuk negatif kata kerja: ない diganti なければ (行《い》かない → 行《い》かなければ). Kata sifat い: い diganti ければ (安《やす》い → 安《やす》ければ). Kata sifat な dan kata benda: tambahkan なら tanpa だ (静《しず》かなら, 学生《がくせい》なら).',
                    'explanation_en' => 'The conditional form (条件形《じょうけんけい》) is made differently for each word type. Group I verbs: change the last sound of the ます-form (い-column) to the え-column sound and add ば (書《か》きます → 書《か》けば). Group II: drop ます and add れば (食《た》べます → 食《た》べれば). Group III: します → すれば, 来《き》ます → 来《く》れば. Negative verbs: replace ない with なければ (行《い》かない → 行《い》かなければ). い-adjectives: replace い with ければ (安《やす》い → 安《やす》ければ). な-adjectives and nouns: add なら without だ (静《しず》かなら, 学生《がくせい》なら).',
                    'notes_id' => [
                        'Kata sifat いい berubah menjadi よければ (bukan いければ).',
                        'Kata sifat い negatif: 高《たか》くない → 高《たか》くなければ. Kata sifat な negatif: 好《す》きじゃ ない → 好《す》きじゃ なければ.',
                        '来《く》る dibaca くれば dalam bentuk syarat, bukan きれば.',
                        'Kata sifat な dan kata benda memakai なら, bukan ば.',
                    ],
                    'notes_en' => [
                        'The adjective いい becomes よければ (not いければ).',
                        'Negative い-adjective: 高《たか》くない → 高《たか》くなければ. Negative な-adjective: 好《す》きじゃ ない → 好《す》きじゃ なければ.',
                        '来《く》る is read くれば in the conditional, not きれば.',
                        'な-adjectives and nouns take なら, not ば.',
                    ],
                    'examples' => [
                        ['ja' => '書《か》く → 書《か》けば ／ 待《ま》つ → 待《ま》てば', 'reading' => 'kaku → kakeba / matsu → mateba', 'id' => 'menulis → kalau menulis ／ menunggu → kalau menunggu', 'en' => 'to write → if (one) writes / to wait → if (one) waits'],
                        ['ja' => '食《た》べる → 食《た》べれば ／ 起《お》きる → 起《お》きれば', 'reading' => 'taberu → tabereba / okiru → okireba', 'id' => 'makan → kalau makan ／ bangun → kalau bangun', 'en' => 'to eat → if (one) eats / to get up → if (one) gets up'],
                        ['ja' => 'する → すれば ／ 来《く》る → 来《く》れば', 'reading' => 'suru → sureba / kuru → kureba', 'id' => 'melakukan → kalau melakukan ／ datang → kalau datang', 'en' => 'to do → if (one) does / to come → if (one) comes'],
                        ['ja' => '行《い》かない → 行《い》かなければ', 'reading' => 'ikanai → ikanakereba', 'id' => 'tidak pergi → kalau tidak pergi', 'en' => 'does not go → if (one) does not go'],
                        ['ja' => '安《やす》い → 安《やす》ければ ／ いい → よければ', 'reading' => 'yasui → yasukereba / ii → yokereba', 'id' => 'murah → kalau murah ／ bagus → kalau bagus', 'en' => 'cheap → if cheap / good → if good'],
                        ['ja' => '静《しず》か → 静《しず》かなら ／ 学生《がくせい》 → 学生《がくせい》なら', 'reading' => 'shizuka → shizuka nara / gakusei → gakusei nara', 'id' => 'tenang → kalau tenang ／ pelajar → kalau pelajar', 'en' => 'quiet → if quiet / student → if (one is) a student'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Latihan bentuk syarat',
                        'title_en' => 'Practising the conditional',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '先生《せんせい》、「待《ま》つ」の 条件形《じょうけんけい》は 何《なん》ですか。', 'reading' => 'Sensei, "matsu" no joukenkei wa nan desu ka.', 'id' => 'Bu/Pak guru, bentuk syarat dari "matsu" apa?', 'en' => 'Teacher, what is the conditional form of "matsu"?'],
                            ['speaker' => '先生', 'ja' => '「待《ま》てば」です。ほかの 動詞《どうし》も 言《い》って みましょう。', 'reading' => 'Mateba desu. Hoka no doushi mo itte mimashou.', 'id' => '"Mateba". Coba sebutkan kata kerja lainnya juga.', 'en' => '"Mateba". Let us try other verbs too.'],
                            ['speaker' => 'ワヒュ', 'ja' => '「書《か》く」は 「書《か》けば」で、「来《く》る」は 「来《く》れば」ですね。', 'reading' => '"Kaku" wa "kakeba" de, "kuru" wa "kureba" desu ne.', 'id' => '"Kaku" menjadi "kakeba", dan "kuru" menjadi "kureba", ya.', 'en' => '"Kaku" is "kakeba" and "kuru" is "kureba", right?'],
                            ['speaker' => '先生', 'ja' => 'そうです。よく できました。', 'reading' => 'Sou desu. Yoku dekimashita.', 'id' => 'Benar. Bagus sekali.', 'en' => 'That is right. Well done.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => '〜ば、〜 (syarat → hasil)',
                'title_en' => '〜ば、〜 (condition → result)',
                'pattern' => 'Bentuk syarat 〜ば、〜',
                'payload' => [
                    'explanation_id' => '〜ば menyebut syarat yang perlu dipenuhi agar hal di kalimat utama terjadi: "kalau X terpenuhi, maka Y". Kalimat utama biasanya berisi hasil, kejadian, atau keputusan. Pada dasarnya kalimat utama tidak memuat keinginan, harapan, perintah, atau permintaan. Pengecualian: boleh dipakai bila subjek kalimat awal dan kalimat utama berbeda, atau bila bagian awal menyatakan keadaan (kata sifat, あります, bentuk ない, dsb.).',
                    'explanation_en' => '〜ば states the condition that must be met for the main clause to happen: "if X holds, then Y". The main clause normally gives a result, an event, or a decision. As a rule it does not contain a wish, hope, command, or request. Exceptions: it is allowed when the subjects of the two clauses differ, or when the first clause describes a state (adjective, あります, ない-form, etc.).',
                    'notes_id' => [
                        'Contoh pengecualian — subjek berbeda: 雨《あめ》が やめば、わたしも 出《で》かけたいです (hujan reda ≠ saya pergi).',
                        'Contoh pengecualian — bagian awal berupa keadaan: 時間《じかん》が あれば、手伝《てつだ》って ください.',
                        'Untuk kalimat utama berisi permintaan dengan subjek yang sama, pakai 〜たら (lihat kartu 4).',
                    ],
                    'notes_en' => [
                        'Exception — different subjects: 雨《あめ》が やめば、わたしも 出《で》かけたいです (the rain stops ≠ I go out).',
                        'Exception — first clause is a state: 時間《じかん》が あれば、手伝《てつだ》って ください.',
                        'For a request in the main clause with the same subject, use 〜たら (see card 4).',
                    ],
                    'examples' => [
                        ['ja' => '時間《じかん》が あれば、いっしょに 映画《えいが》を 見《み》に 行《い》きます。', 'reading' => 'Jikan ga areba, issho ni eiga o mi ni ikimasu.', 'id' => 'Kalau ada waktu, saya akan menonton film bersama.', 'en' => 'If I have time, I will go and see a film together.'],
                        ['ja' => '友達《ともだち》が 手伝《てつだ》えば、仕事《しごと》は 早《はや》く 終《お》わります。', 'reading' => 'Tomodachi ga tetsudaeba, shigoto wa hayaku owarimasu.', 'id' => 'Kalau teman membantu, pekerjaan cepat selesai.', 'en' => 'If a friend helps, the work will finish quickly.'],
                        ['ja' => '天気《てんき》が よければ、あした 山《やま》に 登《のぼ》ります。', 'reading' => 'Tenki ga yokereba, ashita yama ni noborimasu.', 'id' => 'Kalau cuacanya bagus, besok saya akan mendaki gunung.', 'en' => 'If the weather is good, I will climb the mountain tomorrow.'],
                        ['ja' => '薬《くすり》を 飲《の》めば、熱《ねつ》が 下《さ》がるでしょう。', 'reading' => 'Kusuri o nomeba, netsu ga sagaru deshou.', 'id' => 'Kalau minum obat, demamnya mungkin akan turun.', 'en' => 'If you take the medicine, the fever will probably go down.'],
                        ['ja' => '彼《かれ》が 来《こ》なければ、会議《かいぎ》を 始《はじ》められません。', 'reading' => 'Kare ga konakereba, kaigi o hajimeraremasen.', 'id' => 'Kalau dia tidak datang, rapat tidak bisa dimulai.', 'en' => 'If he does not come, we cannot start the meeting.'],
                        ['ja' => '雨《あめ》が やめば、わたしも 出《で》かけたいです。', 'reading' => 'Ame ga yameba, watashi mo dekaketai desu.', 'id' => 'Kalau hujan reda, saya juga ingin keluar.', 'en' => 'If the rain stops, I would like to go out too.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Rencana akhir pekan',
                        'title_en' => 'Weekend plans',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'あした 天気《てんき》が よければ、海《うみ》へ 行《い》きませんか。', 'reading' => 'Ashita tenki ga yokereba, umi e ikimasen ka.', 'id' => 'Kalau besok cuacanya bagus, mau pergi ke laut?', 'en' => 'If the weather is good tomorrow, shall we go to the sea?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいですね。雨《あめ》が 降《ふ》れば、どう しますか。', 'reading' => 'Ii desu ne. Ame ga fureba, dou shimasu ka.', 'id' => 'Boleh. Kalau hujan, bagaimana?', 'en' => 'Sounds good. If it rains, what shall we do?'],
                            ['speaker' => 'サリ', 'ja' => '雨《あめ》が 降《ふ》れば、うちで 映画《えいが》を 見《み》ましょう。', 'reading' => 'Ame ga fureba, uchi de eiga o mimashou.', 'id' => 'Kalau hujan, kita nonton film di rumah.', 'en' => 'If it rains, let us watch a film at home.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'そう しましょう。', 'reading' => 'Sou shimashou.', 'id' => 'Baik, begitu saja.', 'en' => 'Let us do that.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => '〜ば、〜 (menanggapi lawan bicara)',
                'title_en' => '〜ば、〜 (responding to the other person)',
                'pattern' => 'A: 〜んですが。 B: 〜なければ／〜なら、〜',
                'payload' => [
                    'explanation_id' => 'Bentuk syarat juga dipakai untuk menanggapi ucapan atau keadaan lawan bicara, lalu menyatakan keputusan, saran, atau tawaran pembicara: "Kalau begitu keadaannya, …". Karena menanggapi keadaan yang sudah ada, kalimat utama boleh berisi permintaan, ajakan, atau perintah. Biasanya lawan bicara lebih dulu menyebut masalahnya dengan 〜んですが (lihat Pelajaran 1), dan kamu mengulang keadaan itu dalam bentuk syarat: ない → なければ, 悪《わる》い → 悪《わる》ければ, 無理《むり》 → 無理《むり》なら.',
                    'explanation_en' => 'The conditional is also used to respond to what the other person said or to their situation, and then state your decision, advice, or offer: "If that is the case, …". Because you are responding to an existing situation, the main clause may contain a request, an invitation, or a command. Usually the other person first states the problem with 〜んですが (see Lesson 1), and you repeat that situation in the conditional: ない → なければ, 悪《わる》い → 悪《わる》ければ, 無理《むり》 → 無理《むり》なら.',
                    'notes_id' => [
                        'Pasangan yang sering muncul: 〜んですが。 → 〜なければ／〜なら、〜.',
                        'Kalimat utama boleh berisi 〜て ください, 〜ましょう, atau 〜ませんか.',
                    ],
                    'notes_en' => [
                        'A common pair: 〜んですが。 → 〜なければ／〜なら、〜.',
                        'The main clause may contain 〜て ください, 〜ましょう, or 〜ませんか.',
                    ],
                    'examples' => [
                        ['ja' => '辞書《じしょ》が ないんですが。……なければ、わたしのを 使《つか》って ください。', 'reading' => 'Jisho ga nai n desu ga. ...Nakereba, watashi no o tsukatte kudasai.', 'id' => 'Kamusnya tidak ada… ……Kalau tidak ada, pakai punya saya.', 'en' => 'I do not have a dictionary... ...If you do not, please use mine.'],
                        ['ja' => 'きょうは 都合《つごう》が 悪《わる》いんです。……悪《わる》ければ、あしたに しましょう。', 'reading' => 'Kyou wa tsugou ga warui n desu. ...Warukereba, ashita ni shimashou.', 'id' => 'Hari ini saya kurang leluasa. ……Kalau tidak bisa, kita lakukan besok saja.', 'en' => 'Today is not convenient for me. ...If it is not, let us do it tomorrow.'],
                        ['ja' => 'この 仕事《しごと》は 今週中《こんしゅうちゅう》に 終《お》わらせなければ なりませんか。……むずかしければ、来週《らいしゅう》でも いいですよ。', 'reading' => 'Kono shigoto wa konshuuchuu ni owarasenakereba narimasen ka. ...Muzukashikereba, raishuu demo ii desu yo.', 'id' => 'Apakah pekerjaan ini harus selesai minggu ini? ……Kalau sulit, minggu depan juga boleh.', 'en' => 'Do I have to finish this work this week? ...If it is difficult, next week is fine too.'],
                        ['ja' => '席《せき》が いっぱいなんですが。……いっぱいなら、ほかの 店《みせ》に 行《い》きましょう。', 'reading' => 'Seki ga ippai na n desu ga. ...Ippai nara, hoka no mise ni ikimashou.', 'id' => 'Tempat duduknya penuh… ……Kalau penuh, ayo kita ke toko lain.', 'en' => 'The seats are full... ...If they are full, let us go to another shop.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Penghapus tidak ada',
                        'title_en' => 'No eraser',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません、消《け》しゴムが ないんですが……。', 'reading' => 'Sumimasen, keshigomu ga nai n desu ga......', 'id' => 'Maaf, penghapus saya tidak ada…', 'en' => 'Excuse me, I do not have an eraser...'],
                            ['speaker' => 'たなか', 'ja' => 'なければ、わたしのを 使《つか》って ください。', 'reading' => 'Nakereba, watashi no o tsukatte kudasai.', 'id' => 'Kalau tidak ada, pakai punya saya.', 'en' => 'If you do not have one, please use mine.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ありがとう ございます。助《たす》かります。', 'reading' => 'Arigatou gozaimasu. Tasukarimasu.', 'id' => 'Terima kasih. Sangat membantu.', 'en' => 'Thank you. That helps a lot.'],
                            ['speaker' => 'たなか', 'ja' => 'ほかにも 必要《ひつよう》な ものが あれば、言《い》って くださいね。', 'reading' => 'Hoka ni mo hitsuyou na mono ga areba, itte kudasai ne.', 'id' => 'Kalau ada hal lain yang diperlukan, bilang saja, ya.', 'en' => 'If there is anything else you need, please tell me.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'Perbandingan 〜ば／〜と／〜たら',
                'title_en' => 'Comparing 〜ば／〜と／〜たら',
                'pattern' => '〜ば ／ 〜と ／ 〜たら',
                'payload' => [
                    'explanation_id' => 'Ketiganya sama-sama bisa diterjemahkan "kalau", tetapi batas pemakaiannya berbeda. 〜と (Pelajaran 23 N5): bagian awal pasti diikuti hasil/kejadian di bagian belakang; bagian belakang tidak boleh berisi keinginan, harapan, perintah, atau permintaan. 〜ば: syarat yang diperlukan; aturan bagian belakang hampir sama dengan 〜と, tetapi lebih longgar bila subjek berbeda atau bagian awal berupa keadaan. 〜たら (Pelajaran 25 N5): bisa untuk syarat, atau untuk "setelah X selesai"; bagian belakang bebas berisi keinginan, harapan, perintah, dan permintaan. Jadi 〜たら paling luas pemakaiannya, hanya saja terdengar lisan sehingga jarang dipakai dalam tulisan.',
                    'explanation_en' => 'All three can be translated "if/when", but their limits differ. 〜と (N5 Lesson 23): the first part is always followed by the result/event in the second part; the second part may not contain a wish, hope, command, or request. 〜ば: a necessary condition; the rule for the second part is almost the same as 〜と, but it is looser when the subjects differ or the first part is a state. 〜たら (N5 Lesson 25): can express a condition or "after X is done"; the second part may freely contain wishes, hopes, commands, and requests. So 〜たら has the widest use, but it sounds spoken and is rarely used in writing.',
                    'notes_id' => [
                        'Aturan praktis: bila kalimat utama berisi 〜て ください／〜たいです／〜ましょう dengan subjek yang sama, pilih 〜たら.',
                        'Tanda ×: kalimat tidak wajar. Tanda ○: wajar.',
                    ],
                    'notes_en' => [
                        'Rule of thumb: if the main clause has 〜て ください／〜たいです／〜ましょう with the same subject, choose 〜たら.',
                        '× means the sentence is unnatural; ○ means natural.',
                    ],
                    'examples' => [
                        ['ja' => '○ ここを 押《お》すと、水《みず》が 出《で》ます。', 'reading' => 'Koko o osu to, mizu ga demasu.', 'id' => 'Kalau ditekan di sini, air keluar. (と)', 'en' => 'If you press here, water comes out. (と)'],
                        ['ja' => '○ ここを 押《お》せば、水《みず》が 出《で》ます。', 'reading' => 'Koko o oseba, mizu ga demasu.', 'id' => 'Kalau ditekan di sini, air keluar. (ば)', 'en' => 'If you press here, water comes out. (ば)'],
                        ['ja' => '○ 駅《えき》に 着《つ》いたら、電話《でんわ》して ください。', 'reading' => 'Eki ni tsuitara, denwa shite kudasai.', 'id' => 'Kalau sudah sampai stasiun, tolong telepon. (たら)', 'en' => 'When you get to the station, please call. (たら)'],
                        ['ja' => '× 駅《えき》に 着《つ》くと、電話《でんわ》して ください。', 'reading' => 'Eki ni tsuku to, denwa shite kudasai.', 'id' => 'Tidak wajar: と tidak boleh diikuti permintaan.', 'en' => 'Unnatural: と cannot be followed by a request.'],
                        ['ja' => '× 駅《えき》に 着《つ》けば、電話《でんわ》して ください。', 'reading' => 'Eki ni tsukeba, denwa shite kudasai.', 'id' => 'Tidak wajar: subjek sama + permintaan, jadi ば tidak cocok.', 'en' => 'Unnatural: same subject plus a request, so ば does not fit.'],
                        ['ja' => '○ 山田《やまだ》さんが 来《く》れば、わたしは 帰《かえ》ります。', 'reading' => 'Yamada-san ga kureba, watashi wa kaerimasu.', 'id' => 'Kalau Sdr. Yamada datang, saya pulang. (subjek berbeda: ば boleh)', 'en' => 'If Mr. Yamada comes, I will go home. (different subjects: ば is fine)'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Pertanyaan di kelas',
                        'title_en' => 'A question in class',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '先生《せんせい》、「春《はる》に なれば」と 「春《はる》に なると」は 同《おな》じ 意味《いみ》ですか。', 'reading' => 'Sensei, "haru ni nareba" to "haru ni naru to" wa onaji imi desu ka.', 'id' => 'Bu/Pak guru, apakah "haru ni nareba" dan "haru ni naru to" artinya sama?', 'en' => 'Teacher, do "haru ni nareba" and "haru ni naru to" mean the same?'],
                            ['speaker' => '先生', 'ja' => 'ほとんど 同《おな》じです。でも、文《ぶん》の 後《うし》ろに 「〜て ください」は ふつう 使《つか》えません。', 'reading' => 'Hotondo onaji desu. Demo, bun no ushiro ni "〜te kudasai" wa futsuu tsukaemasen.', 'id' => 'Hampir sama. Tetapi di bagian belakang kalimat, "〜te kudasai" biasanya tidak bisa dipakai.', 'en' => 'Almost the same. But "〜te kudasai" cannot normally be used at the end of the sentence.'],
                            ['speaker' => 'ワヒュ', 'ja' => '「〜たら」は どうですか。', 'reading' => '"〜tara" wa dou desu ka.', 'id' => 'Kalau "〜tara" bagaimana?', 'en' => 'What about "〜tara"?'],
                            ['speaker' => '先生', 'ja' => '「〜たら」は 使《つか》えますよ。', 'reading' => '"〜tara" wa tsukaemasu yo.', 'id' => '"〜tara" bisa dipakai.', 'en' => '"〜tara" can be used.'],
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => 'Kata tanya ＋ Vば いいですか (minta saran)',
                'title_en' => 'Question word ＋ Vば いいですか (asking for advice)',
                'pattern' => 'Kata tanya ＋ V(ば形) いいですか',
                'payload' => [
                    'explanation_id' => 'Dipakai untuk meminta masukan atau petunjuk, sama seperti 〜たら いいですか di Pelajaran 1. Susunannya: kata tanya (どこ, いつ, 何, だれに, どう) + kata kerja bentuk syarat + いいですか. Jawabannya memakai bentuk yang sama: 〜ば いいですよ ("sebaiknya …"). Arti kedua pola sama, jadi 〜たら いいですか dan 〜ば いいですか boleh dipakai bergantian.',
                    'explanation_en' => 'Used to ask for advice or directions, just like 〜たら いいですか in Lesson 1. Structure: question word (どこ, いつ, 何, だれに, どう) + verb conditional form + いいですか. The answer uses the same form: 〜ば いいですよ ("you should …"). Both patterns mean the same, so 〜たら いいですか and 〜ば いいですか can be used interchangeably.',
                    'notes_id' => [
                        'どう すれば いいですか = "Bagaimana sebaiknya?" — dipakai ketika caranya belum diketahui sama sekali.',
                        'Bagian awalnya sering dibuka dengan 〜たいんですが (lihat Pelajaran 1).',
                    ],
                    'notes_en' => [
                        'どう すれば いいですか = "What should I do?" — used when you have no idea how to go about it.',
                        'The opening is often 〜たいんですが (see Lesson 1).',
                    ],
                    'examples' => [
                        ['ja' => '空港《くうこう》へ 行《い》きたいんですが、どう 行《い》けば いいですか。……この バスに 乗《の》れば いいですよ。', 'reading' => 'Kuukou e ikitai n desu ga, dou ikeba ii desu ka. ...Kono basu ni noreba ii desu yo.', 'id' => 'Saya ingin ke bandara, bagaimana caranya? ……Naik bus ini saja.', 'en' => 'I want to go to the airport. How should I get there? ...Just take this bus.'],
                        ['ja' => '申《もう》し込《こ》みは いつまでに 出《だ》せば いいですか。……金曜日《きんようび》までに 出《だ》せば いいです。', 'reading' => 'Moushikomi wa itsu made ni daseba ii desu ka. ...Kinyoubi made ni daseba ii desu.', 'id' => 'Formulir pendaftaran sebaiknya diserahkan paling lambat kapan? ……Serahkan sampai hari Jumat.', 'en' => 'By when should I hand in the application? ...Hand it in by Friday.'],
                        ['ja' => 'わからない ことは だれに 聞《き》けば いいですか。……事務所《じむしょ》の 人《ひと》に 聞《き》けば いいですよ。', 'reading' => 'Wakaranai koto wa dare ni kikeba ii desu ka. ...Jimusho no hito ni kikeba ii desu yo.', 'id' => 'Kalau ada yang tidak dimengerti, sebaiknya bertanya kepada siapa? ……Tanyakan saja kepada orang di kantor.', 'en' => 'Whom should I ask when I do not understand something? ...Ask the people at the office.'],
                        ['ja' => '日本語《にほんご》が 上手《じょうず》に なりたいんですが、どう すれば いいですか。……毎日《まいにち》 少《すこ》しずつ 話《はな》せば いいですよ。', 'reading' => 'Nihongo ga jouzu ni naritai n desu ga, dou sureba ii desu ka. ...Mainichi sukoshizutsu hanaseba ii desu yo.', 'id' => 'Saya ingin pandai bahasa Jepang, bagaimana sebaiknya? ……Bicaralah sedikit demi sedikit setiap hari.', 'en' => 'I want to get good at Japanese. What should I do? ...Speak a little every day.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Membeli tiket kereta',
                        'title_en' => 'Buying a train ticket',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '京都《きょうと》まで 行《い》きたいんですが、どう すれば いいですか。', 'reading' => 'Kyouto made ikitai n desu ga, dou sureba ii desu ka.', 'id' => 'Saya ingin ke Kyoto, bagaimana caranya?', 'en' => 'I want to go to Kyoto. How should I go?'],
                            ['speaker' => '駅員', 'ja' => '新幹線《しんかんせん》に 乗《の》れば、早《はや》く 着《つ》きますよ。', 'reading' => 'Shinkansen ni noreba, hayaku tsukimasu yo.', 'id' => 'Kalau naik Shinkansen, cepat sampai.', 'en' => 'If you take the Shinkansen, you will arrive quickly.'],
                            ['speaker' => 'ワヒュ', 'ja' => '切符《きっぷ》は どこで 買《か》えば いいですか。', 'reading' => 'Kippu wa doko de kaeba ii desu ka.', 'id' => 'Tiketnya sebaiknya dibeli di mana?', 'en' => 'Where should I buy the ticket?'],
                            ['speaker' => '駅員', 'ja' => 'あちらの 窓口《まどぐち》で 買《か》えば いいです。', 'reading' => 'Achira no madoguchi de kaeba ii desu.', 'id' => 'Beli saja di loket sebelah sana.', 'en' => 'Just buy it at the counter over there.'],
                        ],
                    ],
                ],
            ],

            // 6 ------------------------------------------------------------
            [
                'title_id' => 'Ｎ なら、〜 (menanggapi topik lawan bicara)',
                'title_en' => 'Ｎ なら、〜 (responding to the other person topic)',
                'pattern' => 'Ｎ なら、〜',
                'payload' => [
                    'explanation_id' => 'Kata benda + なら dipakai ketika lawan bicara membawa suatu topik, lalu kamu menanggapinya dengan memberi informasi atau saran yang berkaitan: "Kalau soal …, …". Kata benda yang dimaksud biasanya sudah muncul dalam ucapan lawan bicara, dan bagian setelah なら berisi pendapat atau rekomendasi dari pembicara. Jawaban sering ditutup dengan よ.',
                    'explanation_en' => 'A noun + なら is used when the other person brings up a topic and you respond with related information or advice: "As for …, …". The noun has usually just appeared in what the other person said, and the part after なら gives the speaker opinion or recommendation. The answer is often closed with よ.',
                    'notes_id' => [
                        'なら juga merupakan bentuk syarat kata benda dan kata sifat な (lihat kartu 1).',
                        'Bagian setelah なら tidak menyatakan syarat, melainkan informasi yang "cocok" dengan topik itu.',
                    ],
                    'notes_en' => [
                        'なら is also the conditional form of nouns and な-adjectives (see card 1).',
                        'What follows なら is not a condition but information that "fits" the topic.',
                    ],
                    'examples' => [
                        ['ja' => 'おいしい ラーメンが 食《た》べたいんですが。……ラーメンなら、駅《えき》の 前《まえ》の 店《みせ》が おいしいですよ。', 'reading' => 'Oishii raamen ga tabetai n desu ga. ...Raamen nara, eki no mae no mise ga oishii desu yo.', 'id' => 'Saya ingin makan ramen yang enak. ……Kalau ramen, toko di depan stasiun enak.', 'en' => 'I want to eat good ramen. ...If it is ramen, the shop in front of the station is good.'],
                        ['ja' => '来月《らいげつ》 京都《きょうと》へ 行《い》くんです。……京都《きょうと》なら、秋《あき》が いちばん きれいですよ。', 'reading' => 'Raigetsu Kyouto e iku n desu. ...Kyouto nara, aki ga ichiban kirei desu yo.', 'id' => 'Bulan depan saya akan pergi ke Kyoto. ……Kalau Kyoto, musim gugur yang paling indah.', 'en' => 'I am going to Kyoto next month. ...If it is Kyoto, autumn is the prettiest.'],
                        ['ja' => 'カメラを 買《か》いたいんですが。……カメラなら、あの 店《みせ》が 安《やす》いですよ。', 'reading' => 'Kamera o kaitai n desu ga. ...Kamera nara, ano mise ga yasui desu yo.', 'id' => 'Saya ingin membeli kamera. ……Kalau kamera, toko itu murah.', 'en' => 'I want to buy a camera. ...If it is a camera, that shop is cheap.'],
                        ['ja' => '日本語《にほんご》の 先生《せんせい》を さがして いるんです。……先生《せんせい》なら、山田《やまだ》さんが いいですよ。', 'reading' => 'Nihongo no sensei o sagashite iru n desu. ...Sensei nara, Yamada-san ga ii desu yo.', 'id' => 'Saya sedang mencari guru bahasa Jepang. ……Kalau guru, Sdr. Yamada bagus.', 'en' => 'I am looking for a Japanese teacher. ...If it is a teacher, Mr. Yamada is good.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mencari tempat makan murah',
                        'title_en' => 'Looking for a cheap place to eat',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '安《やす》い 店《みせ》を さがして いるんですが。', 'reading' => 'Yasui mise o sagashite iru n desu ga.', 'id' => 'Saya sedang mencari tempat makan yang murah.', 'en' => 'I am looking for a cheap place to eat.'],
                            ['speaker' => 'ワヒュ', 'ja' => '安《やす》い 店《みせ》なら、大学《だいがく》の 近《ちか》くに いい 店《みせ》が ありますよ。', 'reading' => 'Yasui mise nara, daigaku no chikaku ni ii mise ga arimasu yo.', 'id' => 'Kalau yang murah, ada tempat bagus dekat universitas.', 'en' => 'If it is a cheap place, there is a good one near the university.'],
                            ['speaker' => 'サリ', 'ja' => 'どんな 料理《りょうり》ですか。', 'reading' => 'Donna ryouri desu ka.', 'id' => 'Masakan apa?', 'en' => 'What kind of food?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'カレーなら、何《なん》でも おいしいですよ。', 'reading' => 'Karee nara, nandemo oishii desu yo.', 'id' => 'Kalau kari, apa saja enak.', 'en' => 'If it is curry, anything is delicious.'],
                        ],
                    ],
                ],
            ],

            // 7 ------------------------------------------------------------
            [
                'title_id' => '〜は ありませんか (kalimat tanya negatif)',
                'title_en' => '〜は ありませんか (the negative question)',
                'pattern' => 'Ｎは ありませんか',
                'payload' => [
                    'explanation_id' => 'いい 所《ところ》は ありませんか artinya sama dengan いい 所《ところ》は ありますか, tetapi lebih halus. Bila ditanya dengan ありませんか, lawan bicara mudah menjawab "tidak ada", sehingga bentuk ini dianggap menjaga perasaan lawan bicara. Karena itu bentuk tanya negatif sering dipakai untuk bertanya dengan sopan atau meminta rekomendasi. Jawabannya tetap はい、あります atau いいえ、ありません.',
                    'explanation_en' => 'いい 所《ところ》は ありませんか means the same as いい 所《ところ》は ありますか, but it is softer. When you ask with ありませんか, it is easy for the other person to answer "there is none", so this form is regarded as considerate. For that reason the negative question is often used to ask politely or to request a recommendation. The answer is still はい、あります or いいえ、ありません.',
                    'notes_id' => [
                        'Jawaban positif: はい、あります (bukan はい、ありません).',
                        'Bentuk ませんか untuk mengajak (V ませんか) sudah dipelajari di N5; di sini yang dibahas adalah ありませんか sebagai cara bertanya yang halus.',
                    ],
                    'notes_en' => [
                        'Positive answer: はい、あります (not はい、ありません).',
                        'The invitation ませんか (V ませんか) was learned in N5; here the focus is ありませんか as a gentle way of asking.',
                    ],
                    'examples' => [
                        ['ja' => '安《やす》くて いい ホテルは ありませんか。……はい、ありますよ。駅《えき》の 近《ちか》くに 一《ひと》つ あります。', 'reading' => 'Yasukute ii hoteru wa arimasen ka. ...Hai, arimasu yo. Eki no chikaku ni hitotsu arimasu.', 'id' => 'Apakah ada hotel yang murah dan bagus? ……Ada. Ada satu dekat stasiun.', 'en' => 'Is there a cheap and good hotel? ...Yes, there is. There is one near the station.'],
                        ['ja' => '何《なに》か 冷《つめ》たい 飲《の》み物《もの》は ありませんか。……お茶《ちゃ》なら ありますよ。', 'reading' => 'Nani ka tsumetai nomimono wa arimasen ka. ...Ocha nara arimasu yo.', 'id' => 'Adakah minuman dingin? ……Kalau teh, ada.', 'en' => 'Is there anything cold to drink? ...If it is tea, we have some.'],
                        ['ja' => '日本語《にほんご》の 練習《れんしゅう》が できる 所《ところ》は ありませんか。……いいえ、ありません。', 'reading' => 'Nihongo no renshuu ga dekiru tokoro wa arimasen ka. ...Iie, arimasen.', 'id' => 'Adakah tempat untuk berlatih bahasa Jepang? ……Tidak, tidak ada.', 'en' => 'Is there a place where I can practise Japanese? ...No, there is not.'],
                        ['ja' => '何《なに》か いい 方法《ほうほう》は ありませんか。……そうですね。', 'reading' => 'Nani ka ii houhou wa arimasen ka. ...Sou desu ne.', 'id' => 'Adakah cara yang baik? ……Hmm, coba saya pikirkan.', 'en' => 'Is there a good way? ...Well, let me think.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mencari tempat jalan-jalan',
                        'title_en' => 'Looking for a place to visit',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '来週《らいしゅう》、三日間《みっかかん》 休《やす》みが あるんですが、どこか いい 所《ところ》は ありませんか。', 'reading' => 'Raishuu, mikkakan yasumi ga aru n desu ga, dokoka ii tokoro wa arimasen ka.', 'id' => 'Minggu depan saya libur tiga hari. Adakah tempat yang bagus?', 'en' => 'I have three days off next week. Is there a good place to go?'],
                            ['speaker' => 'たなか', 'ja' => 'そうですね……。山《やま》なら、涼《すず》しくて いいですよ。', 'reading' => 'Sou desu ne...... Yama nara, suzushikute ii desu yo.', 'id' => 'Hmm… Kalau gunung, sejuk dan bagus.', 'en' => 'Well... If it is a mountain, it is cool and nice.'],
                            ['speaker' => 'ワヒュ', 'ja' => '山《やま》ですか。行《い》き方《かた》は どうですか。', 'reading' => 'Yama desu ka. Ikikata wa dou desu ka.', 'id' => 'Gunung, ya. Bagaimana cara ke sana?', 'en' => 'A mountain? How do I get there?'],
                            ['speaker' => 'たなか', 'ja' => 'さあ……。詳《くわ》しい ことは 旅行社《りょこうしゃ》で 聞《き》けば わかりますよ。', 'reading' => 'Saa...... Kuwashii koto wa ryokousha de kikeba wakarimasu yo.', 'id' => 'Yaa… Kalau bertanya di agen perjalanan, pasti jelas lebih detail.', 'en' => 'Hmm... If you ask at a travel agency, you will find out the details.'],
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
            ['slug' => 'n4-pelajaran-10'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 10',
                'name_en' => 'N4 Lesson 10 Vocabulary',
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
            ['咲きます', 'さきます', 'sakimasu', 'mekar [bunga]', 'to bloom [flowers]'],
            ['変わります', 'かわります', 'kawarimasu', 'berubah [warna]', 'to change [color]'],
            ['困ります', 'こまります', 'komarimasu', 'bingung, kesulitan', 'to be in trouble, to be at a loss'],
            ['付けます', 'つけます', 'tsukemasu', 'memberikan [tanda benar], menandai', 'to put on [a mark]'],
            ['治ります・直ります', 'なおります', 'naorimasu', 'sembuh [penyakit], membaik [kerusakan]', 'to get well [illness], to be repaired [breakage]'],
            ['クリックします', 'クリックします', 'kurikku shimasu', 'klik', 'to click'],
            ['入力します', 'にゅうりょくします', 'nyuuryoku shimasu', 'mengisi, memasukkan data', 'to input, to enter [data]'],
            ['正しい', 'ただしい', 'tadashii', 'benar', 'correct, right'],
            ['向こう', 'むこう', 'mukou', '(di) sana, seberang', 'over there, the other side'],
            ['島', 'しま', 'shima', 'pulau', 'island'],
            ['港', 'みなと', 'minato', 'pelabuhan', 'harbor, port'],
            ['近所', 'きんじょ', 'kinjo', 'tetangga, lingkungan sekitar rumah', 'neighborhood, neighbors'],
            ['屋上', 'おくじょう', 'okujou', 'loteng, atap gedung', 'rooftop'],
            ['海外', 'かいがい', 'kaigai', 'luar negeri', 'overseas'],
            ['山登り', 'やまのぼり', 'yamanobori', 'mendaki gunung', 'mountain climbing'],
            ['歴史', 'れきし', 'rekishi', 'sejarah', 'history'],
            ['機会', 'きかい', 'kikai', 'kesempatan', 'opportunity, chance'],
            ['許可', 'きょか', 'kyoka', 'izin', 'permission'],
            ['丸', 'まる', 'maru', 'bulat, tanda benar (○)', 'circle, round'],
            ['ふりがな', 'ふりがな', 'furigana', 'furigana (huruf penunjuk cara baca kanji)', 'furigana (small kana giving the reading of kanji)'],
            ['設備', 'せつび', 'setsubi', 'fasilitas', 'facilities, equipment'],
            ['レバー', 'レバー', 'rebaa', 'tuas', 'lever'],
            ['キー', 'キー', 'kii', 'tombol, keyboard', 'key'],
            ['カーテン', 'カーテン', 'kaaten', 'gorden, tirai', 'curtain'],
            ['ひも', 'ひも', 'himo', 'tali', 'string, cord'],
            ['炊飯器', 'すいはんき', 'suihanki', 'penanak nasi (rice cooker)', 'rice cooker'],
            ['葉', 'は', 'ha', 'daun', 'leaf'],
            ['昔', 'むかし', 'mukashi', 'dulu', 'long ago, in the past'],
            ['もっと', 'もっと', 'motto', 'lebih', 'more'],
            ['これで 終わりましょう。', 'これで おわりましょう。', 'kore de owarimashou', 'Mari kita selesai sampai di sini.', 'Let us finish here.'],

            // 会話 (percakapan)
            ['それなら', 'それなら', 'sore nara', 'kalau begitu', 'in that case'],
            ['夜行バス', 'やこうバス', 'yakou basu', 'bus malam', 'night bus'],
            ['さあ', 'さあ', 'saa', 'yaaa… (dipakai ketika kurang yakin atau kurang mengerti)', 'well… (used when unsure)'],
            ['旅行社', 'りょこうしゃ', 'ryokousha', 'agen perjalanan', 'travel agency'],
            ['詳しい', 'くわしい', 'kuwashii', 'teliti, terperinci', 'detailed, well informed'],
            ['スキー場', 'スキーじょう', 'sukii jou', 'lapangan ski', 'ski resort'],

            // 読み物 (bacaan)
            ['朱', 'しゅ', 'shu', 'merah terang', 'vermilion'],
            ['交わります', 'まじわります', 'majiwarimasu', 'bergaul', 'to associate with, to mix with'],
            ['ことわざ', 'ことわざ', 'kotowaza', 'pepatah', 'proverb'],
            ['関係', 'かんけい', 'kankei', 'hubungan', 'relationship'],
            ['仲よくします', 'なかよくします', 'nakayoku shimasu', 'bergaul dengan akrab', 'to be on good terms'],
            ['必要[な]', 'ひつよう', 'hitsuyou', 'perlu', 'necessary'],
        ];
    }
}
