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

class N4Lesson5Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 5: "Sebaiknya Mempersiapkan Kantong Darurat".
     * Level N4 sudah dibuat oleh N4Lesson1Seeder; seeder ini menambah unit
     * order 5 di level itu (Pelajaran 2–4 menyusul, jadi order 5 sengaja
     * dipakai apa adanya — Unit.order hanya unik per level).
     *
     * Isi:
     *   - Unit order 5 ("Pelajaran 5")
     *   - Lesson Bunpou (order 0, category grammar) dengan 7 kartu:
     *       1. Ｎ１に Ｎ２が Vて あります      5. まだ ＋ bentuk positif
     *       2. Ｎ２は Ｎ１に Vて あります      6. とか
     *          (dan beda ています／てあります)  7. Partikel ＋ も
     *       3. Vて おきます (persiapan)
     *       4. Vて おきます (membiarkan / menyimpan untuk dipakai lagi)
     *   - Kategori kosakata "n4-pelajaran-5" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus dari daftar kosakata
     * pelajaran ini. Penjelasan, contoh kalimat dan dialog ditulis baru untuk
     * aplikasi ini.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson5Seeder
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
            ['level_id' => $level->id, 'order' => 5],
            [
                'title_id' => 'Pelajaran 5: Sebaiknya Mempersiapkan Kantong Darurat',
                'title_en' => 'Lesson 5: It Is Best to Prepare an Emergency Bag',
                'description_id' => 'Keadaan hasil perbuatan dan persiapan — 〜て あります, 〜て おきます, まだ, とか, dan partikel も.',
                'description_en' => 'States resulting from an action and preparation — 〜て あります, 〜て おきます, まだ, とか, and the particle も.',
                'icon' => 'mdi-bag-personal',
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
    // Helpers (supaya data kartu ringkas)
    // ------------------------------------------------------------------

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
                'title_id' => 'Ｎ１に Ｎ２が Vて あります (keadaan hasil perbuatan)',
                'title_en' => 'Ｎ１に Ｎ２が Vて あります (a state resulting from an action)',
                'pattern' => 'Ｎ１に Ｎ２が Vて あります',
                'payload' => [
                    'explanation_id' => 'Dipakai ketika seseorang sudah melakukan sesuatu dengan sengaja demi suatu tujuan, dan hasilnya masih terlihat sekarang. Susunannya: tempat (N1) に + benda (N2) が + kata kerja transitif bentuk-て + あります. Yang digambarkan adalah keadaan bendanya, bukan orang yang melakukannya, sehingga pelaku tidak disebut. Benda yang di kalimat biasa ditandai を (ポスターを はります) berubah menjadi が.',
                    'explanation_en' => 'Used when someone has deliberately done something for a purpose and the result can still be seen now. Structure: place (N1) に + thing (N2) が + transitive verb て-form + あります. The sentence describes the state of the thing, not the person who did it, so the doer is not mentioned. The object that is marked を in a normal sentence (ポスターを はります) becomes が.',
                    'notes_id' => [
                        'Hanya kata kerja transitif (yang biasanya memakai を) yang dipakai: 書《か》く, 置《お》く, 貼《は》る, 掛《か》ける, 飾《かざ》る, 並《なら》べる, 植《う》える, 入《い》れる, 開《あ》ける, 閉《し》める.',
                        'Ada orang yang melakukannya dengan sengaja, tetapi tidak disebut. Kalau ingin menyebut pelaku, pakai kalimat biasa: たなかさんが ポスターを 貼《は》りました.',
                    ],
                    'notes_en' => [
                        'Only transitive verbs (those that normally take を) are used: 書《か》く, 置《お》く, 貼《は》る, 掛《か》ける, 飾《かざ》る, 並《なら》べる, 植《う》える, 入《い》れる, 開《あ》ける, 閉《し》める.',
                        'Someone did it on purpose, but that person is not named. To name the doer, use a normal sentence: たなかさんが ポスターを 貼《は》りました.',
                    ],
                    'examples' => [
                        $this->ex('壁《かべ》に 世界《せかい》の 地図《ちず》が 掛《か》けて あります。', 'Kabe ni sekai no chizu ga kakete arimasu.', 'Di dinding tergantung peta dunia.', 'A map of the world is hanging on the wall.'),
                        $this->ex('冷蔵庫《れいぞうこ》に ジュースが 入《い》れて あります。', 'Reizouko ni juusu ga irete arimasu.', 'Di dalam kulkas sudah dimasukkan jus.', 'Juice has been put in the fridge.'),
                        $this->ex('玄関《げんかん》に 花《はな》が 飾《かざ》って あります。', 'Genkan ni hana ga kazatte arimasu.', 'Di ruang depan ada bunga yang dipajang.', 'Flowers are displayed in the entrance.'),
                        $this->ex('ホワイトボードに あしたの 予定《よてい》が 書《か》いて あります。', 'Howaitoboodo ni ashita no yotei ga kaite arimasu.', 'Di papan tulis tertulis acara besok.', 'Tomorrow\'s schedule is written on the whiteboard.'),
                    ],
                    'dialogue' => [
                        'title_id' => 'Di kamar teman',
                        'title_en' => 'In a friend\'s room',
                        'lines' => [
                            $this->line('サリ', '部屋《へや》に きれいな 絵《え》が 掛《か》けて ありますね。', 'Heya ni kirei na e ga kakete arimasu ne.', 'Di kamar ini tergantung lukisan yang bagus, ya.', 'There is a lovely picture hanging in this room.'),
                            $this->line('たなか', 'ええ、友達《ともだち》が くれたんです。', 'Ee, tomodachi ga kureta n desu.', 'Ya, diberi oleh teman.', 'Yes, a friend gave it to me.'),
                            $this->line('サリ', 'テーブルの 上《うえ》にも 花《はな》が 飾《かざ》って ありますね。', 'Teeburu no ue ni mo hana ga kazatte arimasu ne.', 'Di atas meja juga ada bunga yang dipajang.', 'There are flowers displayed on the table too.'),
                            $this->line('たなか', 'お客《きゃく》さんが 来《く》るので、飾《かざ》ったんです。', 'Okyaku-san ga kuru node, kazatta n desu.', 'Karena ada tamu yang datang, saya menghiasnya.', 'A guest is coming, so I decorated.'),
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => 'Ｎ２は Ｎ１に Vて あります (benda sebagai topik, beda dengan 〜て います)',
                'title_en' => 'Ｎ２は Ｎ１に Vて あります (the thing as topic, and the contrast with 〜て います)',
                'pattern' => 'Ｎ２は Ｎ１に Vて あります',
                'payload' => [
                    'explanation_id' => 'Kalau benda (N2) dijadikan topik — karena sudah dibahas atau ditanyakan — が diganti は dan benda itu dipindah ke depan: N2 は N1 に Vて あります. Selain itu, kata kerja yang punya pasangan intransitif–transitif bisa dipakai dua cara. 〜て います (intransitif) hanya menggambarkan keadaan apa adanya. 〜て あります (transitif) menunjukkan bahwa keadaan itu hasil perbuatan seseorang yang disengaja.',
                    'explanation_en' => 'When the thing (N2) becomes the topic — because it was already mentioned or asked about — が is replaced by は and the thing moves to the front: N2 は N1 に Vて あります. Also, verbs that come in intransitive–transitive pairs can be used in two ways. 〜て います (intransitive) just describes a state as it is. 〜て あります (transitive) shows that the state is the result of someone doing it on purpose.',
                    'notes_id' => [
                        'Pasangan yang sering muncul: 開《あ》く／開《あ》ける, 閉《し》まる／閉《し》める, つく／つける, 消《き》える／消《け》す, 入《はい》る／入《い》れる, 決《き》まる／決《き》める.',
                        'ドアが 開《あ》いて います = pintunya terbuka (tanpa tahu sebabnya). ドアが 開《あ》けて あります = pintunya sengaja dibuka seseorang.',
                    ],
                    'notes_en' => [
                        'Common pairs: 開《あ》く／開《あ》ける, 閉《し》まる／閉《し》める, つく／つける, 消《き》える／消《け》す, 入《はい》る／入《い》れる, 決《き》まる／決《き》める.',
                        'ドアが 開《あ》いて います = the door is open (no idea why). ドアが 開《あ》けて あります = someone deliberately left the door open.',
                    ],
                    'examples' => [
                        $this->ex('地図《ちず》は どこに 掛《か》けて ありますか。……玄関《げんかん》に 掛《か》けて あります。', 'Chizu wa doko ni kakete arimasu ka. ...Genkan ni kakete arimasu.', 'Petanya digantung di mana? ……Digantung di ruang depan.', 'Where is the map hung? ...It is hung in the entrance.'),
                        $this->ex('パスポートは かばんに 入《い》れて あります。', 'Pasupooto wa kaban ni irete arimasu.', 'Paspor sudah dimasukkan ke dalam tas.', 'The passport has been put in the bag.'),
                        $this->ex('ドアが 開《あ》いて います。だれか いますか。', 'Doa ga aite imasu. Dare ka imasu ka.', 'Pintunya terbuka. Apakah ada orang?', 'The door is open. Is anyone there?'),
                        $this->ex('暑《あつ》いですから、窓《まど》が 開《あ》けて あります。', 'Atsui desu kara, mado ga akete arimasu.', 'Karena panas, jendelanya sengaja dibuka.', 'It is hot, so the window has been left open.'),
                    ],
                    'dialogue' => [
                        'title_id' => 'Mencari alat tulis',
                        'title_en' => 'Looking for stationery',
                        'lines' => [
                            $this->line('ワヒュ', 'すみません、はさみは どこですか。', 'Sumimasen, hasami wa doko desu ka.', 'Permisi, guntingnya di mana?', 'Excuse me, where are the scissors?'),
                            $this->line('たなか', 'はさみは 引《ひ》き出《だ》しに 入《い》れて ありますよ。', 'Hasami wa hikidashi ni irete arimasu yo.', 'Guntingnya disimpan di laci.', 'The scissors are kept in the drawer.'),
                            $this->line('ワヒュ', 'セロテープも ありますか。', 'Serotepu mo arimasu ka.', 'Selotipnya juga ada?', 'Is there tape as well?'),
                            $this->line('たなか', 'ええ、セロテープも その 引《ひ》き出《だ》しに 入《い》れて あります。', 'Ee, serotepu mo sono hikidashi ni irete arimasu.', 'Ya, selotipnya juga disimpan di laci itu.', 'Yes, the tape is in that drawer too.'),
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => 'Vて おきます (menyiapkan lebih dulu)',
                'title_en' => 'Vて おきます (doing something in advance)',
                'pattern' => 'Vて おきます',
                'payload' => [
                    'explanation_id' => 'Menyatakan bahwa kamu menyelesaikan suatu tindakan lebih dulu sebagai persiapan, supaya siap sampai waktu tertentu atau untuk keperluan berikutnya. Bentuknya: kata kerja bentuk-て + おきます. Rasanya: "sebelum X, saya urus Y dulu". Pertanyaan khasnya adalah 何を して おいたら いいですか (apa yang sebaiknya disiapkan?), gabungan dengan pola たら いいですか dari Pelajaran 1.',
                    'explanation_en' => 'Says that you finish an action beforehand as preparation, so that things are ready by a certain time or for what comes next. Form: verb て-form + おきます. The feeling is: "before X, I will take care of Y first". A typical question is 何を して おいたら いいですか (what should I do in advance?), combining it with the たら いいですか pattern from Lesson 1.',
                    'notes_id' => [
                        'Sering dipakai dengan 〜までに／〜前《まえ》に (sampai／sebelum ...).',
                        'Dalam percakapan, 〜て おきます menjadi 〜ときます, dan 〜で おきます menjadi 〜どきます: 買《か》って おきます → 買《か》っときます, 読《よ》んで おきます → 読《よ》んどきます.',
                    ],
                    'notes_en' => [
                        'Often used with 〜までに／〜前《まえ》に (by ... / before ...).',
                        'In speech, 〜て おきます becomes 〜ときます and 〜で おきます becomes 〜どきます: 買《か》って おきます → 買《か》っときます, 読《よ》んで おきます → 読《よ》んどきます.',
                    ],
                    'examples' => [
                        $this->ex('旅行《りょこう》に 行《い》く 前《まえ》に、ホテルを 予約《よやく》して おきます。', 'Ryokou ni iku mae ni, hoteru o yoyaku shite okimasu.', 'Sebelum bepergian, saya memesan hotel lebih dulu.', 'Before going on a trip, I book the hotel in advance.'),
                        $this->ex('あしたの テストの ために、今晩《こんばん》 復習《ふくしゅう》して おきます。', 'Ashita no tesuto no tame ni, konban fukushuu shite okimasu.', 'Demi tes besok, malam ini saya belajar pengulangan dulu.', 'For tomorrow\'s test, I will review tonight.'),
                        $this->ex('お客《きゃく》さんが 来《く》る 前《まえ》に、部屋《へや》を 片《かた》づけて おきます。', 'Okyaku-san ga kuru mae ni, heya o katazukete okimasu.', 'Sebelum tamu datang, saya membereskan kamar lebih dulu.', 'Before the guest arrives, I will tidy the room.'),
                        $this->ex('会議《かいぎ》の 前《まえ》に、資料《しりょう》を コピーして おいて ください。', 'Kaigi no mae ni, shiryou o kopii shite oite kudasai.', 'Sebelum rapat, tolong fotokopi materinya lebih dulu.', 'Please copy the materials before the meeting.'),
                    ],
                    'dialogue' => [
                        'title_id' => 'Persiapan rapat',
                        'title_en' => 'Getting ready for a meeting',
                        'lines' => [
                            $this->line('ワヒュ', '次《つぎ》の ミーティングまでに、何《なに》を して おいたら いいですか。', 'Tsugi no miitingu made ni, nani o shite oitara ii desu ka.', 'Sampai rapat berikutnya, sebaiknya saya menyiapkan apa?', 'By the next meeting, what should I do in advance?'),
                            $this->line('たなか', 'この お知《し》らせを 読《よ》んで おいて ください。', 'Kono oshirase o yonde oite kudasai.', 'Tolong baca pengumuman ini lebih dulu.', 'Please read this notice beforehand.'),
                            $this->line('ワヒュ', 'わかりました。ほかに ありますか。', 'Wakarimashita. Hoka ni arimasu ka.', 'Baik. Ada yang lain?', 'Understood. Is there anything else?'),
                            $this->line('たなか', '会議室《かいぎしつ》の いすを 並《なら》べて おいて ください。', 'Kaigishitsu no isu o narabete oite kudasai.', 'Tolong tata kursi di ruang rapat lebih dulu.', 'Please line up the chairs in the meeting room.'),
                            $this->line('ワヒュ', 'はい、並《なら》べて おきます。', 'Hai, narabete okimasu.', 'Baik, akan saya tata.', 'Yes, I will do it.'),
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'Vて おきます (menyimpan untuk dipakai lagi, membiarkan begitu)',
                'title_en' => 'Vて おきます (putting away for next time, leaving as it is)',
                'pattern' => 'Vて おいて ください／そのままに して おきます',
                'payload' => [
                    'explanation_id' => 'Selain persiapan, 〜て おきます punya dua fungsi lain. (1) Menyelesaikan tindakan agar siap dipakai lagi, misalnya menyimpan atau mengembalikan ke tempat semula. (2) Tindakan sementara yang mempertahankan suatu keadaan, terutama そのままに して おきます (dibiarkan begitu saja). Alasannya biasanya disebut dengan 〜から. Dalam bentuk permintaan: 〜て おいて ください.',
                    'explanation_en' => 'Besides preparation, 〜て おきます has two more uses. (1) Finishing an action so things are ready for next use, such as putting something away or back in its place. (2) A temporary action that keeps a state as it is, especially そのままに して おきます (leave it as it is). The reason is usually given with 〜から. As a request: 〜て おいて ください.',
                    'notes_id' => [
                        'そのままに して おきます berarti tidak mengubah keadaan sekarang: tidak dibereskan, tidak dipindah.',
                        'Pasangan yang berguna: 〜たら、〜て おいて ください (kalau sudah memakai ..., tolong ...).',
                    ],
                    'notes_en' => [
                        'そのままに して おきます means not changing the present state: do not tidy it, do not move it.',
                        'A useful combination: 〜たら、〜て おいて ください (when you have used ..., please ...).',
                    ],
                    'examples' => [
                        $this->ex('ホチキスを 使《つか》ったら、元《もと》の 所《ところ》に 戻《もど》して おいて ください。', 'Hochikisu o tsukattara, moto no tokoro ni modoshite oite kudasai.', 'Kalau sudah memakai stapler, tolong kembalikan ke tempat semula.', 'When you have used the stapler, please put it back where it was.'),
                        $this->ex('あした 授業《じゅぎょう》が ありますから、机《つくえ》は この ままに して おいて ください。', 'Ashita jugyou ga arimasu kara, tsukue wa kono mama ni shite oite kudasai.', 'Karena besok ada kelas, tolong biarkan mejanya seperti ini.', 'There is a class tomorrow, so please leave the desks as they are.'),
                        $this->ex('雨《あめ》が 降《ふ》って いますから、窓《まど》は 閉《し》めて おきます。', 'Ame ga futte imasu kara, mado wa shimete okimasu.', 'Karena hujan, jendelanya saya tutup dulu.', 'It is raining, so I will keep the window shut.'),
                        $this->ex('この 資料《しりょう》は まだ 使《つか》いますから、そのままに して おいて ください。', 'Kono shiryou wa mada tsukaimasu kara, sono mama ni shite oite kudasai.', 'Materi ini masih saya pakai, jadi tolong biarkan begitu saja.', 'I am still using these materials, so please leave them as they are.'),
                    ],
                    'dialogue' => [
                        'title_id' => 'Setelah kelas selesai',
                        'title_en' => 'After class',
                        'lines' => [
                            $this->line('先生', '授業《じゅぎょう》が 終《お》わりました。ポスターは そのままに して おいて ください。', 'Jugyou ga owarimashita. Posutaa wa sono mama ni shite oite kudasai.', 'Kelas sudah selesai. Posternya tolong biarkan begitu saja.', 'Class is over. Please leave the poster as it is.'),
                            $this->line('ワヒュ', 'はい。ごみ箱《ばこ》は どう しましょうか。', 'Hai. Gomibako wa dou shimashou ka.', 'Baik. Bagaimana dengan tong sampahnya?', 'Yes. What shall I do about the trash can?'),
                            $this->line('先生', 'ごみ箱《ばこ》の ごみは 捨《す》てて おいて ください。', 'Gomibako no gomi wa sutete oite kudasai.', 'Tolong buang dulu sampah di tong sampah.', 'Please empty the trash can.'),
                            $this->line('ワヒュ', 'わかりました。', 'Wakarimashita.', 'Baik.', 'Understood.'),
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => 'まだ ＋ bentuk positif (masih ...)',
                'title_en' => 'まだ ＋ affirmative (still ...)',
                'pattern' => 'まだ ＋ kalimat positif',
                'payload' => [
                    'explanation_id' => 'Di N5 kamu mengenal まだ dengan bentuk negatif (まだ 食《た》べて いません = belum makan). Dengan bentuk positif, まだ berarti "masih": suatu tindakan atau keadaan sedang berlangsung dan belum berubah. Lawannya adalah もう (sudah): もう 降《ふ》って いません (sudah tidak hujan) ↔ まだ 降《ふ》って います (masih hujan).',
                    'explanation_en' => 'In N5 you met まだ with a negative (まだ 食《た》べて いません = have not eaten yet). With an affirmative, まだ means "still": an action or state is going on and has not changed. Its counterpart is もう (already): もう 降《ふ》って いません (it has stopped raining) ↔ まだ 降《ふ》って います (it is still raining).',
                    'notes_id' => [
                        'まだ ＋ 〜て います adalah susunan yang paling sering: 「masih sedang ...」.',
                        'Sering menjadi alasan untuk 〜て おいて ください: まだ 使《つか》って いますから、そのままに して おいて ください.',
                    ],
                    'notes_en' => [
                        'まだ ＋ 〜て います is the most common combination: "is still ...ing".',
                        'It often gives the reason for 〜て おいて ください: まだ 使《つか》って いますから、そのままに して おいて ください.',
                    ],
                    'examples' => [
                        $this->ex('まだ 会議《かいぎ》を して います。', 'Mada kaigi o shite imasu.', 'Rapatnya masih berlangsung.', 'The meeting is still going on.'),
                        $this->ex('まだ 時間《じかん》が ありますから、もう 少《すこ》し 復習《ふくしゅう》しましょう。', 'Mada jikan ga arimasu kara, mou sukoshi fukushuu shimashou.', 'Masih ada waktu, jadi mari kita ulang sedikit lagi.', 'There is still time, so let us review a little more.'),
                        $this->ex('お湯《ゆ》は まだ 熱《あつ》いですから、気《き》を つけて ください。', 'Oyu wa mada atsui desu kara, ki o tsukete kudasai.', 'Airnya masih panas, jadi hati-hati.', 'The hot water is still hot, so please be careful.'),
                        $this->ex('まだ 食《た》べて いますから、お皿《さら》は そのままに して おいて ください。', 'Mada tabete imasu kara, osara wa sono mama ni shite oite kudasai.', 'Saya masih makan, jadi tolong biarkan piringnya begitu.', 'I am still eating, so please leave the plates as they are.'),
                    ],
                    'dialogue' => [
                        'title_id' => 'Membereskan alat',
                        'title_en' => 'Tidying up the tools',
                        'lines' => [
                            $this->line('サリ', '道具《どうぐ》を 片《かた》づけましょうか。', 'Dougu o katazukemashou ka.', 'Mau saya bereskan alatnya?', 'Shall I tidy up the tools?'),
                            $this->line('たなか', 'まだ 使《つか》って いますから、そのままに して おいて ください。', 'Mada tsukatte imasu kara, sono mama ni shite oite kudasai.', 'Masih saya pakai, jadi tolong biarkan begitu saja.', 'I am still using them, so please leave them as they are.'),
                            $this->line('サリ', 'はい。終《お》わったら、教《おし》えて ください。', 'Hai. Owattara, oshiete kudasai.', 'Baik. Kalau sudah selesai, kabari saya.', 'Okay. Please tell me when you are done.'),
                            $this->line('たなか', 'ええ、あと 十分《じゅっぷん》ぐらいです。', 'Ee, ato juppun gurai desu.', 'Ya, sekitar sepuluh menit lagi.', 'Yes, about ten more minutes.'),
                        ],
                    ],
                ],
            ],

            // 6 ------------------------------------------------------------
            [
                'title_id' => 'Ｎ とか Ｎ とか (menyebut contoh dalam percakapan)',
                'title_en' => 'Ｎ とか Ｎ とか (giving examples in conversation)',
                'pattern' => 'Ｎ とか Ｎ とか',
                'payload' => [
                    'explanation_id' => 'とか dipakai untuk menyebut beberapa contoh, seperti や di N5. Artinya "misalnya A, B, dan semacamnya". とか lebih terasa lisan dan santai. Perbedaan bentuknya: や hanya ditaruh di antara kata benda, sedangkan とか juga boleh ditaruh di belakang kata benda terakhir.',
                    'explanation_en' => 'とか is used to list a few examples, like や in N5. It means "for example A, B, and so on". とか sounds more spoken and casual. The difference in form: や goes only between nouns, while とか may also follow the last noun.',
                    'notes_id' => [
                        'や: ラーメンや カレー. とか: ラーメンとか カレーとか.',
                        'Dalam tulisan atau situasi yang sangat resmi, lebih aman memakai や.',
                    ],
                    'notes_en' => [
                        'や: ラーメンや カレー. とか: ラーメンとか カレーとか.',
                        'In writing or very formal situations, や is the safer choice.',
                    ],
                    'examples' => [
                        $this->ex('どんな 料理《りょうり》が 好《す》きですか。……ラーメンとか カレーとかが 好《す》きです。', 'Donna ryouri ga suki desu ka. ...Raamen toka karee toka ga suki desu.', 'Suka masakan apa? ……Misalnya ramen atau kari.', 'What kind of food do you like? ...Things like ramen and curry.'),
                        $this->ex('冷蔵庫《れいぞうこ》に 牛乳《ぎゅうにゅう》とか 卵《たまご》とかが 入《い》れて あります。', 'Reizouko ni gyuunyuu toka tamago toka ga irete arimasu.', 'Di kulkas ada susu, telur, dan sejenisnya.', 'There is milk, eggs and the like in the fridge.'),
                        $this->ex('部屋《へや》に 花瓶《かびん》とか 人形《にんぎょう》とかが 飾《かざ》って あります。', 'Heya ni kabin toka ningyou toka ga kazatte arimasu.', 'Di kamar ada vas, boneka, dan semacamnya yang dipajang.', 'Vases, dolls and the like are displayed in the room.'),
                        $this->ex('日本《にほん》で どこへ 行《い》きたいですか。……京都《きょうと》とか 奈良《なら》とか 行《い》きたいです。', 'Nihon de doko e ikitai desu ka. ...Kyouto toka Nara toka ikitai desu.', 'Di Jepang ingin pergi ke mana? ……Misalnya Kyoto atau Nara.', 'Where do you want to go in Japan? ...Places like Kyoto or Nara.'),
                    ],
                    'dialogue' => [
                        'title_id' => 'Kegiatan di hari libur',
                        'title_en' => 'What to do on days off',
                        'lines' => [
                            $this->line('サリ', '休《やす》みの 日《ひ》は 何《なに》を して いますか。', 'Yasumi no hi wa nani o shite imasu ka.', 'Di hari libur, kamu melakukan apa?', 'What do you do on your days off?'),
                            $this->line('ワヒュ', 'そうですね。掃除《そうじ》とか 洗濯《せんたく》とか……。', 'Sou desu ne. Souji toka sentaku toka......', 'Apa ya. Bersih-bersih, mencuci, dan sebagainya…', 'Let me see. Cleaning, laundry, and so on...'),
                            $this->line('サリ', 'ほかには？', 'Hoka ni wa?', 'Yang lain?', 'Anything else?'),
                            $this->line('ワヒュ', '友達《ともだち》と サッカーとか 水泳《すいえい》とか しますよ。', 'Tomodachi to sakkaa toka suiei toka shimasu yo.', 'Dengan teman, saya main sepak bola atau berenang.', 'With friends I play soccer, go swimming, and the like.'),
                        ],
                    ],
                ],
            ],

            // 7 ------------------------------------------------------------
            [
                'title_id' => 'Partikel ＋ も (が／を hilang, partikel lain tetap)',
                'title_en' => 'Particle ＋ も (が／を disappear, other particles stay)',
                'pattern' => 'Ｎ ＋ (partikel) ＋ も',
                'payload' => [
                    'explanation_id' => 'も berarti "juga／pun". Aturan penggabungannya: jika kata benda bertanda が atau を diberi も, が／を dihilangkan dan hanya も yang tersisa. Jika partikelnya lain (に, で, から, まで, と), partikel itu tetap dan も ditaruh di belakangnya. Untuk へ, boleh dihilangkan boleh juga dipertahankan: どこ[へ]も. Pola kata tanya + も + negatif berarti "tidak ... sama sekali／tidak ... mana pun".',
                    'explanation_en' => 'も means "also／even". How it combines: when a noun marked が or を gets も, the が／を is dropped and only も remains. With any other particle (に, で, から, まで, と), that particle stays and も follows it. With へ, you may drop it or keep it: どこ[へ]も. The pattern question word + も + negative means "not ... at all／not ... anywhere".',
                    'notes_id' => [
                        'が／を + も → も: サリさんが 来《き》ます → サリさんも 来《き》ます.',
                        'に／で／から／まで／と + も → 〜にも, 〜でも, 〜からも, 〜までも, 〜とも.',
                        'どこ[へ]も, 何《なに》も, だれも selalu bertemu kalimat negatif.',
                    ],
                    'notes_en' => [
                        'が／を + も → も: サリさんが 来《き》ます → サリさんも 来《き》ます.',
                        'に／で／から／まで／と + も → 〜にも, 〜でも, 〜からも, 〜までも, 〜とも.',
                        'どこ[へ]も, 何《なに》も, だれも always go with a negative sentence.',
                    ],
                    'examples' => [
                        $this->ex('駅《えき》の 近《ちか》くにも 郵便局《ゆうびんきょく》が あります。', 'Eki no chikaku ni mo yuubinkyoku ga arimasu.', 'Di dekat stasiun juga ada kantor pos.', 'There is a post office near the station too.'),
                        $this->ex('日曜日《にちようび》は どこ[へ]も 行《い》きませんでした。', 'Nichiyoubi wa doko [e] mo ikimasen deshita.', 'Hari Minggu saya tidak pergi ke mana-mana.', 'On Sunday I did not go anywhere.'),
                        $this->ex('大阪《おおさか》からも 友達《ともだち》が 来《き》ました。', 'Oosaka kara mo tomodachi ga kimashita.', 'Dari Osaka pun ada teman yang datang.', 'A friend came from Osaka too.'),
                        $this->ex('冷蔵庫《れいぞうこ》の 中《なか》に 何《なに》も 入《はい》って いません。', 'Reizouko no naka ni nani mo haitte imasen.', 'Di dalam kulkas tidak ada apa-apa.', 'There is nothing in the fridge.'),
                    ],
                    'dialogue' => [
                        'title_id' => 'Akhir pekan',
                        'title_en' => 'The weekend',
                        'lines' => [
                            $this->line('たなか', '日曜日《にちようび》は どこかへ 行《い》きましたか。', 'Nichiyoubi wa doko ka e ikimashita ka.', 'Hari Minggu pergi ke suatu tempat?', 'Did you go somewhere on Sunday?'),
                            $this->line('ワヒュ', 'いいえ、どこ[へ]も 行《い》きませんでした。うちで 勉強《べんきょう》しました。', 'Iie, doko [e] mo ikimasen deshita. Uchi de benkyou shimashita.', 'Tidak, saya tidak ke mana-mana. Saya belajar di rumah.', 'No, I did not go anywhere. I studied at home.'),
                            $this->line('たなか', '土曜日《どようび》も 勉強《べんきょう》しましたか。', 'Doyoubi mo benkyou shimashita ka.', 'Hari Sabtu juga belajar?', 'Did you study on Saturday too?'),
                            $this->line('ワヒュ', 'ええ、土曜日《どようび》も 勉強《べんきょう》しました。', 'Ee, doyoubi mo benkyou shimashita.', 'Ya, hari Sabtu pun saya belajar.', 'Yes, I studied on Saturday as well.'),
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
            ['slug' => 'n4-pelajaran-5'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 5',
                'name_en' => 'N4 Lesson 5 Vocabulary',
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
            // Kata kerja
            ['貼ります', 'はります', 'harimasu', 'menempelkan', 'to stick, to paste'],
            ['掛けます', 'かけます', 'kakemasu', 'menggantungkan', 'to hang'],
            ['飾ります', 'かざります', 'kazarimasu', 'menghiasi, memajang', 'to decorate, to display'],
            ['並べます', 'ならべます', 'narabemasu', 'menata, menjajarkan', 'to line up, to arrange'],
            ['植えます', 'うえます', 'uemasu', 'menanam', 'to plant'],
            ['戻します', 'もどします', 'modoshimasu', 'mengembalikan', 'to put back, to return'],
            ['まとめます', 'まとめます', 'matomemasu', 'merangkum, mengumpulkan menjadi satu', 'to put together, to sum up'],
            ['しまいます', 'しまいます', 'shimaimasu', 'menyimpan, memasukkan kembali', 'to put away'],
            ['決めます', 'きめます', 'kimemasu', 'menentukan, memutuskan', 'to decide'],
            ['予習します', 'よしゅうします', 'yoshuushimasu', 'belajar persiapan', 'to prepare for a lesson'],
            ['復習します', 'ふくしゅうします', 'fukushuushimasu', 'belajar pengulangan', 'to review a lesson'],
            ['そのままにします', 'そのままにします', 'sono mama ni shimasu', 'membiarkan begitu saja', 'to leave as it is'],

            // Kata benda
            ['授業', 'じゅぎょう', 'jugyou', 'kelas, pelajaran', 'class, lesson'],
            ['講義', 'こうぎ', 'kougi', 'kuliah', 'lecture'],
            ['ミーティング', 'ミーティング', 'miitingu', 'rapat', 'meeting'],
            ['予定', 'よてい', 'yotei', 'acara, rencana', 'schedule, plan'],
            ['お知らせ', 'おしらせ', 'oshirase', 'pengumuman', 'notice, announcement'],
            ['ガイドブック', 'ガイドブック', 'gaidobukku', 'buku petunjuk', 'guidebook'],
            ['カレンダー', 'カレンダー', 'karendaa', 'kalender', 'calendar'],
            ['ポスター', 'ポスター', 'posutaa', 'plakat, poster', 'poster'],
            ['予定表', 'よていひょう', 'yoteihyou', 'jadwal acara', 'schedule table'],
            ['ごみ箱', 'ごみばこ', 'gomibako', 'tempat sampah, tong sampah', 'trash can'],
            ['人形', 'にんぎょう', 'ningyou', 'boneka, orang-orangan', 'doll'],
            ['花瓶', 'かびん', 'kabin', 'vas', 'vase'],
            ['鏡', 'かがみ', 'kagami', 'cermin', 'mirror'],
            ['引き出し', 'ひきだし', 'hikidashi', 'laci', 'drawer'],
            ['玄関', 'げんかん', 'genkan', 'ruang depan, pintu masuk', 'entrance hall'],
            ['廊下', 'ろうか', 'rouka', 'koridor, gang', 'corridor'],
            ['壁', 'かべ', 'kabe', 'dinding', 'wall'],
            ['池', 'いけ', 'ike', 'kolam', 'pond'],
            ['元の所', 'もとのところ', 'moto no tokoro', 'tempat semula', 'the original place'],
            ['周り', 'まわり', 'mawari', 'sekitar', 'surroundings'],
            ['真ん中', 'まんなか', 'mannaka', 'tengah', 'middle'],
            ['隅', 'すみ', 'sumi', 'pojok, sudut', 'corner'],
            ['まだ', 'まだ', 'mada', 'masih', 'still'],

            // 会話 (percakapan)
            ['リュック', 'リュック', 'ryukku', 'ransel', 'backpack'],
            ['非常袋', 'ひじょうぶくろ', 'hijoubukuro', 'kantong darurat', 'emergency bag'],
            ['非常時', 'ひじょうじ', 'hijouji', 'saat darurat', 'time of emergency'],
            ['生活します', 'せいかつします', 'seikatsushimasu', 'hidup', 'to live, to get by'],
            ['懐中電灯', 'かいちゅうでんとう', 'kaichuu dentou', 'senter', 'flashlight'],
            ['〜とか、〜とか', '〜とか、〜とか', 'toka toka', '~ atau ~', '~ or ~, things like ~'],

            // 読み物 (bacaan)
            ['丸い', 'まるい', 'marui', 'bundar, bulat', 'round'],
            ['ある〜', 'ある〜', 'aru', 'suatu ~', 'a certain ~'],
            ['夢を見ます', 'ゆめをみます', 'yume o mimasu', 'bermimpi', 'to have a dream'],
            ['うれしい', 'うれしい', 'ureshii', 'senang, gembira', 'glad, happy'],
            ['嫌[な]', 'いや[な]', 'iya na', 'tidak senang, tidak suka', 'unpleasant, disagreeable'],
            ['すると', 'すると', 'suru to', 'kemudian, lalu', 'then, thereupon'],
            ['目が覚めます', 'めがさめます', 'me ga samemasu', 'bangun, sadar', 'to wake up'],
        ];
    }
}
