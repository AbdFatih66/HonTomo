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

class N4Lesson1Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 1 ("Sebaiknya Buang
     * Sampah di Mana?"). Level N4 berdiri di samping N5 dan dipilih lewat
     * selektor N5 / N4 di Tata Bahasa, Kosakata dan Referensi Tata Bahasa.
     *
     * Isi:
     *   - Level N4, Unit order 1 ("Pelajaran 1")
     *   - Lesson Bunpou (order 0, category grammar) dengan 6 kartu:
     *       1. 〜んですか           4. Vて いただけませんか
     *       2. 〜んです              5. Kata tanya + Vたら いいですか
     *       3. 〜んですが、〜        6. N は + 好きです／上手です／あります
     *   - Kategori kosakata "n4-pelajaran-1" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus dari daftar kosakata
     * buku. Penjelasan, contoh kalimat dan dialog ditulis baru untuk aplikasi
     * ini — tidak disalin dari buku. Nama diri (エドヤストア, 星出彰彦) sengaja
     * tidak dimasukkan, sama seperti daftar nama diri di N5.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson1Seeder
     *
     * Kata kunci pencarian SQL memakai ASCII (kategori.slug + romaji), bukan
     * huruf Jepang (lihat docs/AGENTS.md aturan 4).
     */
    public function run(): void
    {
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
            ['level_id' => $level->id, 'order' => 1],
            [
                'title_id' => 'Pelajaran 1: Sebaiknya Buang Sampah di Mana?',
                'title_en' => 'Lesson 1: Where Should I Put Out the Garbage?',
                'description_id' => 'Pola 〜んです, 〜ていただけませんか, 〜たらいいですか.',
                'description_en' => 'Patterns 〜んです, 〜ていただけませんか, 〜たらいいですか.',
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
                'title_id' => '〜んですか (meminta penjelasan)',
                'title_en' => '〜んですか (asking for an explanation)',
                'pattern' => 'ふつうけい ＋ んですか',
                'payload' => [
                    'explanation_id' => 'Bentuk biasa (kamus, ない, た, なかった) + んですか dipakai ketika kamu melihat atau mendengar sesuatu lalu ingin memastikan atau bertanya lebih jauh: "Jadi begitu, ya?", "Kok bisa?", "Bagaimana ceritanya?". Berbeda dari pertanyaan biasa dengan ですか, んですか membawa rasa ingin tahu terhadap situasi yang ada di depan mata atau baru saja dikatakan lawan bicara. Kata benda dan kata sifat な memakai な sebagai ganti だ: 病気《びょうき》なんですか.',
                    'explanation_en' => 'Plain form (dictionary, ない, た, なかった) + んですか is used when you have seen or heard something and want to confirm it or ask more: "So that is the case?", "How come?", "What happened?". Unlike a plain question with ですか, んですか carries curiosity about the situation right in front of you or what the other person just said. Nouns and な-adjectives use な instead of だ: 病気《びょうき》なんですか.',
                    'notes_id' => [
                        'Dipakai untuk: (1) memastikan hal yang dilihat/didengar, (2) meminta keterangan lanjutan, (3) menanyakan alasan (どうして／なぜ), (4) menanyakan keadaan (どうしたんですか).',
                        'Dalam bahasa lisan ditulis 〜んです; dalam tulisan menjadi 〜のです.',
                        'Jangan dipakai pada pertanyaan yang sebenarnya tidak butuh latar belakang — lawan bicara bisa merasa diinterogasi. Untuk pertanyaan biasa tetap pakai ですか／ますか.',
                    ],
                    'notes_en' => [
                        'Used to: (1) confirm what you saw or heard, (2) ask for more details, (3) ask for a reason (どうして／なぜ), (4) ask about a situation (どうしたんですか).',
                        'In speech it is 〜んです; in writing it becomes 〜のです.',
                        'Do not use it for questions that need no background — the other person may feel interrogated. For ordinary questions stay with ですか／ますか.',
                    ],
                    'examples' => [
                        ['ja' => '買《か》い物《もの》の 袋《ふくろ》が たくさん ありますね。たくさん 買《か》ったんですか。', 'reading' => 'Kaimono no fukuro ga takusan arimasu ne. Takusan katta n desu ka.', 'id' => 'Banyak sekali kantong belanja, ya. Kamu belanja banyak?', 'en' => 'There are lots of shopping bags. Did you buy a lot?'],
                        ['ja' => 'いい かばんですね。どこで 買《か》ったんですか。', 'reading' => 'Ii kaban desu ne. Doko de katta n desu ka.', 'id' => 'Tasnya bagus, ya. Beli di mana?', 'en' => 'Nice bag. Where did you buy it?'],
                        ['ja' => 'どうして 会社《かいしゃ》を 休《やす》んだんですか。', 'reading' => 'Doushite kaisha o yasunda n desu ka.', 'id' => 'Kenapa tidak masuk kerja?', 'en' => 'Why did you take a day off work?'],
                        ['ja' => 'きょうは 元気《げんき》ですね。何《なに》か いい ことが あったんですか。', 'reading' => 'Kyou wa genki desu ne. Nani ka ii koto ga atta n desu ka.', 'id' => 'Hari ini kamu semangat, ya. Ada kabar baik?', 'en' => 'You look cheerful today. Did something good happen?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Kenapa terlambat?',
                        'title_en' => 'Why were you late?',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'ワヒュさん、遅《おそ》かったですね。どうして 遅《おく》れたんですか。', 'reading' => 'Wahyu-san, osokatta desu ne. Doushite okureta n desu ka.', 'id' => 'Wahyu, kamu terlambat, ya. Kenapa bisa terlambat?', 'en' => 'Wahyu, you were late. Why were you late?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません。きのう 夜《よる》 遅《おそ》くまで 勉強《べんきょう》したんです。', 'reading' => 'Sumimasen. Kinou yoru osoku made benkyou shita n desu.', 'id' => 'Maaf. Kemarin malam saya belajar sampai larut.', 'en' => 'Sorry. I studied until late last night.'],
                            ['speaker' => 'たなか', 'ja' => 'そうですか。あまり 無理《むり》を しないで くださいね。', 'reading' => 'Sou desu ka. Amari muri o shinaide kudasai ne.', 'id' => 'Begitu, ya. Jangan terlalu memaksakan diri.', 'en' => 'I see. Please do not overdo it.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => '〜んです (menjelaskan alasan)',
                'title_en' => '〜んです (giving a reason)',
                'pattern' => 'ふつうけい ＋ んです',
                'payload' => [
                    'explanation_id' => 'Jawaban atas 〜んですか. Dipakai ketika kamu menjelaskan alasan atau keadaan: "Soalnya…", "Habisnya…". Pola ini juga bisa dipakai untuk menambahkan alasan pada hal yang baru kamu katakan sendiri. Cara membentuknya sama: bentuk biasa + んです, dengan な untuk kata benda dan kata sifat な (子《こ》どもが 病気《びょうき》なんです).',
                    'explanation_en' => 'The answer to 〜んですか. It is used to explain a reason or a situation: "It is because…", "The thing is…". You can also use it to add a reason to something you just said yourself. It is formed the same way: plain form + んです, with な for nouns and な-adjectives (子《こ》どもが 病気《びょうき》なんです).',
                    'notes_id' => [
                        'Hanya pakai んです kalau ada alasan atau penjelasan. Untuk sekadar menyatakan fakta, pakai です biasa: ×わたしは ワヒュなんです (salah) → わたしは ワヒュです.',
                        'Bentuk negatif: 〜ないんです; bentuk lampau: 〜たんです／〜なかったんです.',
                    ],
                    'notes_en' => [
                        'Use んです only when there is a reason or explanation. To simply state a fact, use plain です: ×わたしは ワヒュなんです (wrong) → わたしは ワヒュです.',
                        'Negative: 〜ないんです; past: 〜たんです／〜なかったんです.',
                    ],
                    'examples' => [
                        ['ja' => 'どうして 会社《かいしゃ》を 休《やす》んだんですか。……熱《ねつ》が あったんです。', 'reading' => 'Doushite kaisha o yasunda n desu ka. ...Netsu ga atta n desu.', 'id' => 'Kenapa tidak masuk kerja? ……Karena saya demam.', 'en' => 'Why did you take a day off work? ...I had a fever.'],
                        ['ja' => 'よく スポーツを しますか。……いいえ、あまり しません。運動《うんどう》は 好《す》きじゃ ないんです。', 'reading' => 'Yoku supootsu o shimasu ka. ...Iie, amari shimasen. Undou wa suki ja nai n desu.', 'id' => 'Apakah sering berolahraga? ……Tidak, jarang. Saya memang tidak suka olahraga.', 'en' => 'Do you often play sports? ...No, not much. The thing is, I do not like exercise.'],
                        ['ja' => 'きょうは 早《はや》く 帰《かえ》ります。子《こ》どもが 病気《びょうき》なんです。', 'reading' => 'Kyou wa hayaku kaerimasu. Kodomo ga byouki na n desu.', 'id' => 'Hari ini saya pulang lebih awal. Anak saya sakit.', 'en' => 'I am going home early today. My child is sick.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Ada apa?',
                        'title_en' => 'What is wrong?',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'ワヒュさん、どうしたんですか。', 'reading' => 'Wahyu-san, doushita n desu ka.', 'id' => 'Wahyu, ada apa?', 'en' => 'Wahyu, what is the matter?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ちょっと おなかが 痛《いた》いんです。', 'reading' => 'Chotto onaka ga itai n desu.', 'id' => 'Perut saya agak sakit.', 'en' => 'My stomach hurts a little.'],
                            ['speaker' => 'たなか', 'ja' => 'だいじょうぶですか。', 'reading' => 'Daijoubu desu ka.', 'id' => 'Tidak apa-apa?', 'en' => 'Are you all right?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ。朝《あさ》ごはんを 食《た》べなかったんです。', 'reading' => 'Ee. Asagohan o tabenakatta n desu.', 'id' => 'Ya. Soalnya saya tidak sarapan.', 'en' => 'Yes. I did not eat breakfast.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => '〜んですが、〜 (pembuka permintaan／ajakan)',
                'title_en' => '〜んですが、〜 (opening a request or invitation)',
                'pattern' => 'ふつうけい ＋ んですが、〜',
                'payload' => [
                    'explanation_id' => '〜んですが dipakai untuk membuka pembicaraan: pertama kamu menyebut situasimu, lalu menyusul inti pembicaraan — permintaan, ajakan, atau permohonan izin. Partikel が di sini hanya penghalus (lihat Pelajaran 14 N5), bukan "tetapi". Dengan begitu permintaan terdengar lebih halus karena lawan bicara paham dulu latar belakangnya.',
                    'explanation_en' => '〜んですが opens a conversation: first you state your situation, then comes the main point — a request, an invitation, or asking permission. The particle が here is just a softener (see N5 Lesson 14), not "but". This makes the request sound gentler because the listener understands the background first.',
                    'notes_id' => [
                        'Kalau latar belakangnya sudah jelas bagi kedua pihak, kalimat lanjutan boleh dihilangkan: エアコンが つかないんですが……。 (maksudnya: tolong dibantu).',
                        'Kalimat setelah 〜んですが biasanya berupa ですか／ませんか／てもいいですか／たらいいですか.',
                    ],
                    'notes_en' => [
                        'When the background is already clear to both sides, the follow-up can be left out: エアコンが つかないんですが……。 (meaning: please help).',
                        'What follows 〜んですが is usually ですか／ませんか／てもいいですか／たらいいですか.',
                    ],
                    'examples' => [
                        ['ja' => '日本語《にほんご》の 勉強《べんきょう》を 始《はじ》めたいんですが、いい 本《ほん》が ありますか。', 'reading' => 'Nihongo no benkyou o hajimetai n desu ga, ii hon ga arimasu ka.', 'id' => 'Saya ingin mulai belajar bahasa Jepang. Adakah buku yang bagus?', 'en' => 'I would like to start studying Japanese. Is there a good book?'],
                        ['ja' => 'あした 友達《ともだち》が うちへ 来《く》るんですが、サリさんも 来《き》ませんか。', 'reading' => 'Ashita tomodachi ga uchi e kuru n desu ga, Sari-san mo kimasen ka.', 'id' => 'Besok teman saya akan datang ke rumah. Sari, maukah kamu datang juga?', 'en' => 'A friend is coming to my house tomorrow. Would you like to come too, Sari?'],
                        ['ja' => '足《あし》が 痛《いた》いんですが、先《さき》に 帰《かえ》っても いいですか。', 'reading' => 'Ashi ga itai n desu ga, saki ni kaette mo ii desu ka.', 'id' => 'Kaki saya sakit. Bolehkah saya pulang duluan?', 'en' => 'My leg hurts. May I go home first?'],
                        ['ja' => 'エアコンが つかないんですが……。', 'reading' => 'Eakon ga tsukanai n desu ga......', 'id' => 'AC-nya tidak menyala… (maksudnya: tolong dilihat).', 'en' => 'The air conditioner will not turn on... (meaning: could you take a look).'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Bertanya pada rekan kerja',
                        'title_en' => 'Asking a colleague',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません。あしたの 会議《かいぎ》の ことで 聞《き》きたいんですが、今《いま》 いいですか。', 'reading' => 'Sumimasen. Ashita no kaigi no koto de kikitai n desu ga, ima ii desu ka.', 'id' => 'Permisi. Saya ingin bertanya soal rapat besok, apakah sekarang bisa?', 'en' => 'Excuse me. I would like to ask about tomorrow meeting. Is now a good time?'],
                            ['speaker' => 'たなか', 'ja' => 'ええ、いいですよ。', 'reading' => 'Ee, ii desu yo.', 'id' => 'Ya, silakan.', 'en' => 'Yes, go ahead.'],
                            ['speaker' => 'ワヒュ', 'ja' => '会議室《かいぎしつ》は 何階《なんがい》ですか。', 'reading' => 'Kaigishitsu wa nangai desu ka.', 'id' => 'Ruang rapatnya di lantai berapa?', 'en' => 'Which floor is the meeting room on?'],
                            ['speaker' => 'たなか', 'ja' => '三階《さんがい》です。', 'reading' => 'Sangai desu.', 'id' => 'Lantai tiga.', 'en' => 'The third floor.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'Vて いただけませんか (permintaan sopan)',
                'title_en' => 'Vて いただけませんか (a polite request)',
                'pattern' => 'Vて いただけませんか',
                'payload' => [
                    'explanation_id' => 'Bentuk permintaan yang lebih sopan daripada 〜て ください. Secara harfiah berarti "Bolehkah saya menerima (kebaikan) Anda melakukan ~?" — pembicara merendah, lawan bicara dihormati. Cocok untuk atasan, guru, atau orang yang belum akrab. Cara membentuknya: kata kerja bentuk-て + いただけませんか. Jawaban yang umum: はい、いいですよ (ya, boleh) atau すみません、ちょっと…… (maaf, agak sulit).',
                    'explanation_en' => 'A request that is more polite than 〜て ください. Literally "Could I receive the favour of you doing ~?" — the speaker humbles themselves and honours the listener. Suitable for superiors, teachers, or people you are not close to. Form: verb て-form + いただけませんか. Common answers: はい、いいですよ (yes, of course) or すみません、ちょっと…… (sorry, that is a bit difficult).',
                    'notes_id' => [
                        'Urutan kesopanan: 〜て ください < 〜て いただけませんか.',
                        'Tambahkan すみませんが di depan agar lebih halus.',
                    ],
                    'notes_en' => [
                        'Politeness order: 〜て ください < 〜て いただけませんか.',
                        'Add すみませんが in front to make it softer.',
                    ],
                    'examples' => [
                        ['ja' => 'すみませんが、もう 一度《いちど》 言《い》って いただけませんか。', 'reading' => 'Sumimasen ga, mou ichido itte itadakemasen ka.', 'id' => 'Maaf, bisakah Anda mengatakannya sekali lagi?', 'en' => 'Excuse me, could you say that once more?'],
                        ['ja' => 'この 漢字《かんじ》の 読《よ》み方《かた》を 教《おし》えて いただけませんか。', 'reading' => 'Kono kanji no yomikata o oshiete itadakemasen ka.', 'id' => 'Bisakah Anda mengajari saya cara membaca kanji ini?', 'en' => 'Could you teach me how to read this kanji?'],
                        ['ja' => 'ここに 住所《じゅうしょ》を 書《か》いて いただけませんか。', 'reading' => 'Koko ni juusho o kaite itadakemasen ka.', 'id' => 'Bisakah Anda menuliskan alamat di sini?', 'en' => 'Could you write the address here?'],
                        ['ja' => '写真《しゃしん》を 撮《と》って いただけませんか。', 'reading' => 'Shashin o totte itadakemasen ka.', 'id' => 'Bisakah Anda memotret kami?', 'en' => 'Could you take a photo for us?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Meminta tolong kepada guru',
                        'title_en' => 'Asking a teacher for help',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '先生《せんせい》、すみません。この 言葉《ことば》の 意味《いみ》が わからないんですが、教《おし》えて いただけませんか。', 'reading' => 'Sensei, sumimasen. Kono kotoba no imi ga wakaranai n desu ga, oshiete itadakemasen ka.', 'id' => 'Bu/Pak guru, permisi. Saya tidak paham arti kata ini. Bisakah Anda menjelaskannya?', 'en' => 'Teacher, excuse me. I do not understand the meaning of this word. Could you explain it?'],
                            ['speaker' => '先生', 'ja' => 'はい、いいですよ。どれですか。', 'reading' => 'Hai, ii desu yo. Dore desu ka.', 'id' => 'Ya, tentu. Yang mana?', 'en' => 'Yes, of course. Which one?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'これです。', 'reading' => 'Kore desu.', 'id' => 'Yang ini.', 'en' => 'This one.'],
                            ['speaker' => '先生', 'ja' => 'ああ、これですか。ゆっくり 説明《せつめい》しますね。', 'reading' => 'Aa, kore desu ka. Yukkuri setsumei shimasu ne.', 'id' => 'Oh, yang ini. Saya jelaskan pelan-pelan, ya.', 'en' => 'Ah, this one. I will explain it slowly.'],
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => 'Kata tanya ＋ Vたら いいですか (minta saran)',
                'title_en' => 'Question word ＋ Vたら いいですか (asking for advice)',
                'pattern' => 'Kata tanya ＋ V(た形)ら いいですか',
                'payload' => [
                    'explanation_id' => 'Dipakai untuk meminta masukan atau petunjuk: "Sebaiknya saya … bagaimana/di mana/kapan?" Susunannya: kata tanya (どこ, いつ, 何, どう, だれに) + kata kerja bentuk-た + ら + いいですか. Bentuk-たら dibuat dengan menambahkan ら pada bentuk-た (買《か》った → 買《か》ったら). Untuk menjawab, pakai pola yang sama: 〜たら いいですよ ("sebaiknya ...").',
                    'explanation_en' => 'Used to ask for advice or directions: "Where/when/how should I …?" Structure: question word (どこ, いつ, 何, どう, だれに) + verb た-form + ら + いいですか. The たら form is made by adding ら to the た-form (買《か》った → 買《か》ったら). To answer, use the same pattern: 〜たら いいですよ ("you should …").',
                    'notes_id' => [
                        'どう したら いいですか = "Bagaimana sebaiknya?" — dipakai ketika caranya sama sekali belum diketahui.',
                        'Bentuk-た: 行《い》く → 行《い》った, 食《た》べる → 食《た》べた, 来《く》る → 来《き》た, する → した.',
                    ],
                    'notes_en' => [
                        'どう したら いいですか = "What should I do?" — used when you have no idea how to go about it.',
                        'た-form: 行《い》く → 行《い》った, 食《た》べる → 食《た》べた, 来《く》る → 来《き》た, する → した.',
                    ],
                    'examples' => [
                        ['ja' => 'どこで 切符《きっぷ》を 買《か》ったら いいですか。……あの 自動販売機《じどうはんばいき》で 買《か》ったら いいですよ。', 'reading' => 'Doko de kippu o kattara ii desu ka. ...Ano jidouhanbaiki de kattara ii desu yo.', 'id' => 'Sebaiknya beli tiket di mana? ……Sebaiknya beli di mesin penjual otomatis itu.', 'en' => 'Where should I buy a ticket? ...You should buy one at that vending machine.'],
                        ['ja' => '何時《なんじ》に 来《き》たら いいですか。……九時《くじ》に 来《き》たら いいですよ。', 'reading' => 'Nanji ni kitara ii desu ka. ...Kuji ni kitara ii desu yo.', 'id' => 'Sebaiknya datang jam berapa? ……Sebaiknya datang jam sembilan.', 'en' => 'What time should I come? ...You should come at nine.'],
                        ['ja' => '駅《えき》へ 行《い》きたいんですが、どう したら いいですか。……この 道《みち》を まっすぐ 行《い》ったら いいですよ。', 'reading' => 'Eki e ikitai n desu ga, dou shitara ii desu ka. ...Kono michi o massugu ittara ii desu yo.', 'id' => 'Saya ingin ke stasiun, bagaimana caranya? ……Jalan saja lurus di jalan ini.', 'en' => 'I want to go to the station. What should I do? ...Just go straight along this road.'],
                        ['ja' => 'ごみは いつ 出《だ》したら いいですか。……月曜日《げつようび》と 木曜日《もくようび》に 出《だ》したら いいですよ。', 'reading' => 'Gomi wa itsu dashitara ii desu ka. ...Getsuyoubi to mokuyoubi ni dashitara ii desu yo.', 'id' => 'Kapan sebaiknya membuang sampah? ……Sebaiknya Senin dan Kamis.', 'en' => 'When should I put out the garbage? ...On Monday and Thursday.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Soal sampah di lingkungan baru',
                        'title_en' => 'Garbage rules in a new neighbourhood',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'たなかさん、びんは いつ 出《だ》したら いいですか。', 'reading' => 'Tanaka-san, bin wa itsu dashitara ii desu ka.', 'id' => 'Pak/Bu Tanaka, kapan sebaiknya membuang botol?', 'en' => 'Mr./Ms. Tanaka, when should I put out bottles?'],
                            ['speaker' => 'たなか', 'ja' => '土曜日《どようび》ですよ。', 'reading' => 'Doyoubi desu yo.', 'id' => 'Hari Sabtu.', 'en' => 'On Saturday.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ごみ置《お》き場《ば》は どこですか。', 'reading' => 'Gomi-okiba wa doko desu ka.', 'id' => 'Tempat sampahnya di mana?', 'en' => 'Where is the garbage place?'],
                            ['speaker' => 'たなか', 'ja' => 'あの 建物《たてもの》の 横《よこ》です。', 'reading' => 'Ano tatemono no yoko desu.', 'id' => 'Di samping gedung itu.', 'en' => 'Next to that building.'],
                        ],
                    ],
                ],
            ],

            // 6 ------------------------------------------------------------
            [
                'title_id' => 'N は 好きです／上手です／あります (benda sebagai topik)',
                'title_en' => 'N は 好きです／上手です／あります (the object as topic)',
                'pattern' => 'Ｎは 好《す》きです／上手《じょうず》です／あります',
                'payload' => [
                    'explanation_id' => 'Di N5 kamu belajar bahwa objek dari 好《す》きです, 上手《じょうず》です, あります dan sejenisnya ditandai が (カレーが 好きです). Kalau objek itu ingin dijadikan topik — misalnya untuk membandingkan, atau karena sudah dibahas — が diganti は. Hasilnya: N は 好きです／上手です／あります. Dalam kalimat negatif atau perbandingan, は memberi kesan kontras: "yang ini iya, yang itu tidak".',
                    'explanation_en' => 'In N5 you learned that the object of 好《す》きです, 上手《じょうず》です, あります and similar words is marked with が (カレーが 好きです). When you want that object to be the topic — to contrast things, or because it has already come up — が is replaced by は. The result: N は 好きです／上手です／あります. In negative or comparing sentences, は gives a contrast: "this one yes, that one no".',
                    'notes_id' => [
                        'Kata benda yang ditunjuk を juga bisa dijadikan topik dengan cara yang sama: この ケーキは 食《た》べました (kue ini sudah saya makan).',
                        'Pertanyaannya juga sama: コーヒーは 好《す》きですか。',
                    ],
                    'notes_en' => [
                        'A noun marked with を can be made the topic in the same way: この ケーキは 食《た》べました (this cake, I have eaten).',
                        'Questions work the same way: コーヒーは 好《す》きですか。',
                    ],
                    'examples' => [
                        ['ja' => 'お酒《さけ》は 好《す》きですが、タバコは 好《す》きじゃ ありません。', 'reading' => 'Osake wa suki desu ga, tabako wa suki ja arimasen.', 'id' => 'Minuman keras saya suka, tetapi rokok tidak.', 'en' => 'I like alcohol, but I do not like cigarettes.'],
                        ['ja' => '日本語《にほんご》は 少《すこ》し わかりますが、英語《えいご》は ぜんぜん わかりません。', 'reading' => 'Nihongo wa sukoshi wakarimasu ga, eigo wa zenzen wakarimasen.', 'id' => 'Bahasa Jepang saya sedikit mengerti, tetapi bahasa Inggris sama sekali tidak.', 'en' => 'I understand a little Japanese, but no English at all.'],
                        ['ja' => '車《くるま》は ありませんが、自転車《じてんしゃ》は あります。', 'reading' => 'Kuruma wa arimasen ga, jitensha wa arimasu.', 'id' => 'Mobil tidak punya, tetapi sepeda ada.', 'en' => 'I do not have a car, but I have a bicycle.'],
                        ['ja' => '料理《りょうり》は 上手《じょうず》ですが、掃除《そうじ》は 下手《へた》です。', 'reading' => 'Ryouri wa jouzu desu ga, souji wa heta desu.', 'id' => 'Memasak saya pandai, tetapi bersih-bersih payah.', 'en' => 'I am good at cooking, but bad at cleaning.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Suka dan tidak suka',
                        'title_en' => 'Likes and dislikes',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'ワヒュさんは 日本《にほん》の 料理《りょうり》が 好《す》きですか。', 'reading' => 'Wahyu-san wa Nihon no ryouri ga suki desu ka.', 'id' => 'Wahyu, apakah kamu suka masakan Jepang?', 'en' => 'Wahyu, do you like Japanese food?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'すしは 好《す》きです。でも、さしみは あまり 好《す》きじゃ ないんです。', 'reading' => 'Sushi wa suki desu. Demo, sashimi wa amari suki ja nai n desu.', 'id' => 'Sushi saya suka. Tapi sashimi saya kurang suka.', 'en' => 'I like sushi. But I do not really like sashimi.'],
                            ['speaker' => 'サリ', 'ja' => 'そうですか。からい 料理《りょうり》は どうですか。', 'reading' => 'Sou desu ka. Karai ryouri wa dou desu ka.', 'id' => 'Begitu, ya. Kalau masakan pedas bagaimana?', 'en' => 'I see. How about spicy food?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'からい 料理《りょうり》は 大好《だいす》きです。', 'reading' => 'Karai ryouri wa daisuki desu.', 'id' => 'Masakan pedas saya sangat suka.', 'en' => 'I love spicy food.'],
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
            ['slug' => 'n4-pelajaran-1'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 1',
                'name_en' => 'N4 Lesson 1 Vocabulary',
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
            ['見ます', 'みます', 'mimasu', 'melihat, memeriksa', 'to look at, to examine'],
            ['探します', 'さがします', 'sagashimasu', 'mencari', 'to look for'],
            ['遅れます', 'おくれます', 'okuremasu', 'terlambat [pada waktu]', 'to be late [for an appointment]'],
            ['間に合います', 'まにあいます', 'maniaimasu', 'sempat [pada waktu]', 'to be in time [for an appointment]'],
            ['やります', 'やります', 'yarimasu', 'melakukan', 'to do'],
            ['拾います', 'ひろいます', 'hiroimasu', 'mendapat, mengambil', 'to pick up, to find'],
            ['連絡します', 'れんらくします', 'renrakushimasu', 'menghubungi', 'to contact'],
            ['気分がいい', 'きぶんがいい', 'kibun ga ii', 'rasa enak, segar', 'to feel good'],
            ['気分が悪い', 'きぶんがわるい', 'kibun ga warui', 'rasa tidak enak', 'to feel unwell'],
            ['運動会', 'うんどうかい', 'undoukai', 'lomba olahraga', 'sports day'],
            ['盆踊り', 'ぼんおどり', 'bon odori', 'tarian Bon', 'Bon dance'],
            ['フリーマーケット', 'フリーマーケット', 'furii maaketto', 'pasar rombengan', 'flea market'],
            ['場所', 'ばしょ', 'basho', 'tempat', 'place'],
            ['ボランティア', 'ボランティア', 'borantia', 'sukarelawan', 'volunteer'],
            ['財布', 'さいふ', 'saifu', 'dompet', 'wallet'],
            ['ごみ', 'ごみ', 'gomi', 'sampah', 'garbage'],
            ['国会議事堂', 'こっかいぎじどう', 'kokkai gijidou', 'gedung parlemen', 'National Diet Building'],
            ['平日', 'へいじつ', 'heijitsu', 'hari kerja', 'weekday'],
            ['〜弁', '〜べん', 'ben', 'dialek ~', '~ dialect'],
            ['今度', 'こんど', 'kondo', 'kali ini, lain kali', 'this time, next time'],
            ['ずいぶん', 'ずいぶん', 'zuibun', 'sangat, amat', 'quite, very'],
            ['直接', 'ちょくせつ', 'chokusetsu', 'langsung', 'directly'],
            ['いつでも', 'いつでも', 'itsudemo', 'kapan saja', 'any time'],
            ['どこでも', 'どこでも', 'dokodemo', 'di mana-mana', 'anywhere'],
            ['だれでも', 'だれでも', 'daredemo', 'siapa saja', 'anyone'],
            ['何でも', 'なんでも', 'nandemo', 'apa saja', 'anything'],
            ['こんな〜', 'こんな〜', 'konna', '~ begini', 'this kind of ~'],
            ['そんな〜', 'そんな〜', 'sonna', '~ begitu (dekat dari lawan bicara)', 'that kind of ~ (near the listener)'],
            ['あんな〜', 'あんな〜', 'anna', '~ begitu (jauh dari pembicara dan lawan bicara)', 'that kind of ~ (far from both)'],

            // 会話 (percakapan)
            ['片づけます', 'かたづけます', 'katazukemasu', 'membereskan [barang]', 'to tidy up'],
            ['出します', 'だします', 'dashimasu', 'membuang [sampah], mengeluarkan', 'to put out [garbage], to take out'],
            ['燃えるごみ', 'もえるごみ', 'moeru gomi', 'sampah organik', 'burnable garbage'],
            ['置き場', 'おきば', 'okiba', 'tempat meletakkan sampah', 'place for putting things'],
            ['横', 'よこ', 'yoko', 'sebelah, samping', 'side, next to'],
            ['瓶', 'びん', 'bin', 'botol', 'bottle'],
            ['缶', 'かん', 'kan', 'kaleng', 'can'],
            ['ガス', 'ガス', 'gasu', 'gas', 'gas'],
            ['〜会社', '〜がいしゃ', 'gaisha', 'perusahaan ~', '~ company'],

            // 読み物 (bacaan)
            ['宇宙', 'うちゅう', 'uchuu', 'angkasa', 'space, universe'],
            ['〜様', '〜さま', 'sama', 'kepada Yth. ~ (kata hormat dari 〜さん)', 'Mr./Ms. ~ (honorific of 〜さん)'],
            ['宇宙船', 'うちゅうせん', 'uchuusen', 'wahana antariksa', 'spaceship'],
            ['怖い', 'こわい', 'kowai', 'takut', 'scary, afraid'],
            ['宇宙ステーション', 'うちゅうステーション', 'uchuu suteeshon', 'stasiun luar angkasa', 'space station'],
            ['違います', 'ちがいます', 'chigaimasu', 'tidak benar', 'that is wrong, to differ'],
            ['宇宙飛行士', 'うちゅうひこうし', 'uchuu hikoushi', 'astronot', 'astronaut'],
        ];
    }
}
