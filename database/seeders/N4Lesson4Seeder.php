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

class N4Lesson4Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 4 ("Barang Saya Tertinggal"). Level N4 berdiri di
     * samping N5 dan dipilih lewat selektor N5 / N4 di Tata Bahasa, Kosakata
     * dan Referensi Tata Bahasa.
     *
     * Isi:
     *   - Level N4 (dibuat hanya kalau belum ada) dan Unit order 4 ("Pelajaran 4")
     *   - Lesson Bunpou (order 0, category grammar) dengan 6 kartu:
     *       1. Vて います (keadaan sebagai hasil kejadian)
     *       2. Vて しまいました／しまいます
     *       3. Ｎ(tempat)に 行きます／来ます／帰ります
     *       4. それ／その／そう
     *       5. ありました (menemukan sesuatu)
     *       6. どこかで／どこかに
     *   - Kategori kosakata "n4-pelajaran-4" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus. Penjelasan, contoh
     * kalimat dan dialog ditulis baru untuk aplikasi ini. Nama diri sengaja
     * tidak dimasukkan ke daftar kata, sama seperti di N5 dan N4 Pelajaran 1.
     *
     * Referensi Tata Bahasa (halaman lampiran) untuk pelajaran ini ada di
     * resources/js/pages/lampiran/index.vue (bagian N4: Keadaan & Kondisi,
     * Vて います & しまいます).
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson4Seeder
     *
     * Kata kunci pencarian SQL memakai ASCII (kategori.slug + romaji), bukan
     * huruf Jepang (lihat docs/AGENTS.md aturan 4).
     */
    public function run(): void
    {
        // Level N4 sudah dibuat oleh seeder Pelajaran 1. firstOrCreate supaya
        // seeder ini tidak menimpa perubahan apa pun pada level itu.
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
            ['level_id' => $level->id, 'order' => 4],
            [
                'title_id' => 'Pelajaran 4: Barang Saya Tertinggal',
                'title_en' => 'Lesson 4: I Left Something Behind',
                'description_id' => 'Keadaan sebagai hasil kejadian (〜て います), 〜て しまいました, それ／その／そう, ありました, dan どこかで／どこかに.',
                'description_en' => 'States resulting from an event (〜て います), 〜て しまいました, それ／その／そう, ありました, and どこかで／どこかに.',
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
                'title_id' => 'Vて います (keadaan sebagai hasil kejadian)',
                'title_en' => 'Vて います (a state resulting from an event)',
                'pattern' => 'Ｎが Ｖて います',
                'payload' => [
                    'explanation_id' => 'Di N5 kamu mengenal 〜て います untuk aksi yang sedang berlangsung (食《た》べて います = sedang makan). Pola yang sama punya pemakaian kedua: menyatakan keadaan yang ada sekarang sebagai akibat dari sesuatu yang sudah terjadi. Kata kerja yang dipakai adalah kata kerja yang menggambarkan perubahan keadaan tanpa pelaku, seperti 開《あ》きます, 割《わ》れます, 消《き》えます. Contoh: 皿《さら》が 割《わ》れました = piringnya pecah (kejadiannya), 皿《さら》が 割《わ》れて います = piringnya dalam keadaan pecah (keadaannya sekarang).',
                    'explanation_en' => 'In N5 you met 〜て います for an action in progress (食《た》べて います = is eating). The same pattern has a second use: it describes the present state that results from something that already happened. The verbs used are those that describe a change of state with no agent, such as 開《あ》きます, 割《わ》れます, 消《き》えます. Example: 皿《さら》が 割《わ》れました = the plate broke (the event), 皿《さら》が 割《わ》れて います = the plate is broken (the state now).',
                    'notes_id' => [
                        'Kata kerja yang sering dipakai: 開《あ》く (terbuka), 閉《し》まる (tertutup), つく (menyala), 消《き》える (padam), 壊《こわ》れる (rusak), 割《わ》れる (pecah), 折《お》れる (patah), 破《やぶ》れる (robek), 汚《よご》れる (kotor), 付《つ》く (terpasang), 外《はず》れる (terlepas), 止《と》まる (berhenti), 掛《か》かる (terkunci).',
                        'Bandingkan: 食《た》べて います = sedang makan (aksi berlangsung), tetapi 割《わ》れて います bukan "sedang pecah" — artinya sudah pecah dan tetap begitu.',
                        'Ketika kamu menggambarkan keadaan yang kelihatan langsung di depan mata, penanda yang dipakai adalah が (ドアが 開《あ》いて います). Kalau bendanya dijadikan topik, pakai は (この いすは 壊《こわ》れて います).',
                    ],
                    'notes_en' => [
                        'Common verbs: 開《あ》く (open), 閉《し》まる (be closed), つく (be on), 消《き》える (go out), 壊《こわ》れる (be broken), 割《わ》れる (crack, shatter), 折《お》れる (snap), 破《やぶ》れる (tear), 汚《よご》れる (get dirty), 付《つ》く (be attached), 外《はず》れる (come off), 止《と》まる (stop), 掛《か》かる (be locked).',
                        'Compare: 食《た》べて います = is eating (action in progress), but 割《わ》れて います does not mean "is breaking" — it means it has broken and stays that way.',
                        'When you describe a state you can see right in front of you, the marker is が (ドアが 開《あ》いて います). If the object becomes the topic, use は (この いすは 壊《こわ》れて います).',
                    ],
                    'examples' => [
                        ['ja' => 'ドアが 開《あ》いて います。だれか いるんですか。', 'reading' => 'Doa ga aite imasu. Dareka iru n desu ka.', 'id' => 'Pintunya terbuka. Apa ada orang di dalam?', 'en' => 'The door is open. Is someone inside?'],
                        ['ja' => '部屋《へや》の 電気《でんき》が ついて います。まだ 起《お》きて いるんでしょう。', 'reading' => 'Heya no denki ga tsuite imasu. Mada okite iru n deshou.', 'id' => 'Lampu kamarnya menyala. Pasti dia masih bangun.', 'en' => 'The light in the room is on. He must still be awake.'],
                        ['ja' => 'この コップは 割《わ》れて いますから、使《つか》わないで ください。', 'reading' => 'Kono koppu wa warete imasu kara, tsukawanai de kudasai.', 'id' => 'Gelas ini pecah, jadi tolong jangan dipakai.', 'en' => 'This glass is broken, so please do not use it.'],
                        ['ja' => 'シャツの ボタンが 外《はず》れて いますよ。', 'reading' => 'Shatsu no botan ga hazurete imasu yo.', 'id' => 'Kancing bajumu terlepas, lho.', 'en' => 'A button on your shirt has come off.'],
                        ['ja' => '車《くるま》が 家《いえ》の 前《まえ》に 止《と》まって います。', 'reading' => 'Kuruma ga ie no mae ni tomatte imasu.', 'id' => 'Ada mobil berhenti di depan rumah.', 'en' => 'A car is stopped in front of the house.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di depan ruang rapat',
                        'title_en' => 'In front of the meeting room',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'たなかさん、会議室《かいぎしつ》の ドアが 閉《し》まって いますが、入《はい》っても いいですか。', 'reading' => 'Tanaka-san, kaigishitsu no doa ga shimatte imasu ga, haittemo ii desu ka.', 'id' => 'Pak/Bu Tanaka, pintu ruang rapat tertutup. Bolehkah saya masuk?', 'en' => 'Mr./Ms. Tanaka, the meeting room door is closed. May I go in?'],
                            ['speaker' => 'たなか', 'ja' => 'ええ、鍵《かぎ》は かかって いませんから、どうぞ。', 'reading' => 'Ee, kagi wa kakatte imasen kara, douzo.', 'id' => 'Ya, kuncinya tidak terkunci, silakan.', 'en' => 'Yes, it is not locked, so go ahead.'],
                            ['speaker' => 'ワヒュ', 'ja' => '電気《でんき》が 消《き》えて いますね。', 'reading' => 'Denki ga kiete imasu ne.', 'id' => 'Lampunya padam, ya.', 'en' => 'The light is off, is it not?'],
                            ['speaker' => 'たなか', 'ja' => 'スイッチは ドアの 横《よこ》に ありますよ。', 'reading' => 'Suicchi wa doa no yoko ni arimasu yo.', 'id' => 'Saklarnya ada di samping pintu.', 'en' => 'The switch is next to the door.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => 'Vて しまいました／しまいます (selesai dan penyesalan)',
                'title_en' => 'Vて しまいました／しまいます (completion and regret)',
                'pattern' => 'Ｖて しまいました／しまいます',
                'payload' => [
                    'explanation_id' => 'Bentuk-て + しまいます punya dua fungsi. (1) Penyelesaian: aksi dituntaskan sampai habis. しまいました = sudah selesai, しまいます = akan diselesaikan (sering bersama までに). (2) Penyesalan: sesuatu terjadi di luar keinginan dan pembicara menyesal atau kecewa — \"sayangnya…\", \"terlanjur…\". Fungsi mana yang dimaksud ditentukan oleh arti kata kerja dan konteksnya.',
                    'explanation_en' => 'て-form + しまいます has two functions. (1) Completion: the action is carried through to the end. しまいました = it is finished, しまいます = it will be finished (often with までに). (2) Regret: something happened that the speaker did not want, and they feel sorry or disappointed — \"unfortunately…\", \"I ended up…\". Which one is meant depends on the verb and the context.',
                    'notes_id' => [
                        'Untuk hal yang tidak disengaja (kehilangan, melupakan, merusak), selalu bermakna penyesalan. Biasanya diawali すみません atau ああ.',
                        'Dalam percakapan akrab sering diucapkan 〜ちゃいました／〜ちゃった. Di pelajaran ini cukup kenali bentuk lengkapnya dulu.',
                        'Contoh kata kerja untuk penyesalan: なくす (menghilangkan), 忘《わす》れる (lupa/tertinggal), 落《お》とす (menjatuhkan), 間違《まちが》える (salah).',
                    ],
                    'notes_en' => [
                        'For unintended events (losing, forgetting, breaking), it always carries regret. It is often introduced with すみません or ああ.',
                        'In casual speech it is often pronounced 〜ちゃいました／〜ちゃった. For this lesson, just recognise the full form first.',
                        'Verbs often used for regret: なくす (lose), 忘《わす》れる (forget, leave behind), 落《お》とす (drop), 間違《まちが》える (make a mistake).',
                    ],
                    'examples' => [
                        ['ja' => '借《か》りた 本《ほん》は きのう 読《よ》んで しまいました。', 'reading' => 'Karita hon wa kinou yonde shimaimashita.', 'id' => 'Buku yang saya pinjam sudah selesai saya baca kemarin.', 'en' => 'I finished reading the book I borrowed yesterday.'],
                        ['ja' => '夕方《ゆうがた》までに この 書類《しょるい》を 片《かた》づけて しまいます。', 'reading' => 'Yuugata made ni kono shorui o katazukete shimaimasu.', 'id' => 'Sampai sore nanti saya akan membereskan dokumen ini sampai selesai.', 'en' => 'I will finish putting these documents in order by evening.'],
                        ['ja' => '財布《さいふ》を 家《いえ》に 忘《わす》れて しまいました。', 'reading' => 'Saifu o ie ni wasurete shimaimashita.', 'id' => 'Sayang sekali, dompet saya tertinggal di rumah.', 'en' => 'Oh no, I left my wallet at home.'],
                        ['ja' => 'きのう、携帯電話《けいたいでんわ》を 落《お》として しまいました。', 'reading' => 'Kinou, keitai denwa o otoshite shimaimashita.', 'id' => 'Kemarin saya menjatuhkan ponsel saya (dan menyesalinya).', 'en' => 'Yesterday I dropped my mobile phone, unfortunately.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Dokumen yang terbuang',
                        'title_en' => 'The discarded documents',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません。書類《しょるい》を 間違《まちが》えて 捨《す》てて しまいました。', 'reading' => 'Sumimasen. Shorui o machigaete sutete shimaimashita.', 'id' => 'Maaf. Saya salah membuang dokumen.', 'en' => 'I am sorry. I threw away the documents by mistake.'],
                            ['speaker' => 'たなか', 'ja' => 'えっ、どの 書類《しょるい》ですか。', 'reading' => 'E, dono shorui desu ka.', 'id' => 'Eh, dokumen yang mana?', 'en' => 'What? Which documents?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'きのうの 会議《かいぎ》の 書類《しょるい》です。', 'reading' => 'Kinou no kaigi no shorui desu.', 'id' => 'Dokumen rapat kemarin.', 'en' => 'The documents from the meeting yesterday.'],
                            ['speaker' => 'たなか', 'ja' => 'じゃ、ごみ置《お》き場《ば》を いっしょに 探《さが》しましょう。', 'reading' => 'Ja, gomi-okiba o issho ni sagashimashou.', 'id' => 'Kalau begitu, mari kita cari bersama di tempat sampah.', 'en' => 'Then let us look for them together at the garbage place.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => 'Ｎ(tempat)に 行きます／来ます／帰ります',
                'title_en' => 'Ｎ(place)に 行きます／来ます／帰ります',
                'pattern' => 'Ｎ(tempat)に 行きます／来ます／帰ります',
                'payload' => [
                    'explanation_id' => 'Di N5 kamu memakai へ untuk arah tujuan: 駅《えき》へ 行《い》きます. Dengan kata kerja 行《い》きます, 来《き》ます, 帰《かえ》ります, tujuan juga bisa ditandai に. へ menekankan arah, sedangkan に menekankan titik yang dituju atau dicapai. Keduanya boleh dipakai dan artinya hampir sama: 駅《えき》へ 行《い》きます = 駅《えき》に 行《い》きます.',
                    'explanation_en' => 'In N5 you used へ for a direction: 駅《えき》へ 行《い》きます. With the verbs 行《い》きます, 来《き》ます and 帰《かえ》ります the destination can also be marked with に. へ emphasises the direction, while に emphasises the point you are heading to or reach. Both are fine and mean almost the same: 駅《えき》へ 行《い》きます = 駅《えき》に 行《い》きます.',
                    'notes_id' => [
                        'に di sini berbeda dari に pada 〜に 行きます yang menyatakan tujuan kegiatan (買《か》い物《もの》に 行《い》きます). Keduanya bisa muncul dalam satu kalimat: 駅《えき》に 買《か》い物《もの》に 行《い》きます.',
                        'Kalau tujuannya bukan tempat melainkan arah umum (misalnya 西《にし》へ), へ lebih alami.',
                    ],
                    'notes_en' => [
                        'This に differs from the に in 〜に 行きます that states the purpose of going (買《か》い物《もの》に 行《い》きます). Both can appear in one sentence: 駅《えき》に 買《か》い物《もの》に 行《い》きます.',
                        'When the target is a general direction rather than a place (for example 西《にし》へ), へ sounds more natural.',
                    ],
                    'examples' => [
                        ['ja' => '交番《こうばん》に 行《い》って、道《みち》を 聞《き》きました。', 'reading' => 'Kouban ni itte, michi o kikimashita.', 'id' => 'Saya pergi ke pos polisi dan menanyakan jalan.', 'en' => 'I went to the police box and asked for directions.'],
                        ['ja' => '来週《らいしゅう》、友達《ともだち》が 日本《にほん》に 来《き》ます。', 'reading' => 'Raishuu, tomodachi ga Nihon ni kimasu.', 'id' => 'Minggu depan teman saya datang ke Jepang.', 'en' => 'Next week a friend is coming to Japan.'],
                        ['ja' => '十時《じゅうじ》までに うちに 帰《かえ》ります。', 'reading' => 'Juuji made ni uchi ni kaerimasu.', 'id' => 'Saya pulang ke rumah sebelum jam sepuluh.', 'en' => 'I will go home by ten.'],
                        ['ja' => '駅前《えきまえ》へ 行《い》きます。／駅前《えきまえ》に 行《い》きます。', 'reading' => 'Ekimae e ikimasu. / Ekimae ni ikimasu.', 'id' => 'Saya pergi ke depan stasiun. (kedua bentuk boleh dipakai)', 'en' => 'I am going to the front of the station. (both forms are fine)'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Rencana besok',
                        'title_en' => 'Plans for tomorrow',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'ワヒュさん、あしたは どこに 行《い》きますか。', 'reading' => 'Wahyu-san, ashita wa doko ni ikimasu ka.', 'id' => 'Wahyu, besok kamu pergi ke mana?', 'en' => 'Wahyu, where are you going tomorrow?'],
                            ['speaker' => 'ワヒュ', 'ja' => '駅前《えきまえ》の 交番《こうばん》に 行《い》きます。', 'reading' => 'Ekimae no kouban ni ikimasu.', 'id' => 'Ke pos polisi di depan stasiun.', 'en' => 'To the police box in front of the station.'],
                            ['speaker' => 'サリ', 'ja' => '何時《なんじ》に 帰《かえ》りますか。', 'reading' => 'Nanji ni kaerimasu ka.', 'id' => 'Kamu pulang jam berapa?', 'en' => 'What time will you come back?'],
                            ['speaker' => 'ワヒュ', 'ja' => '昼《ひる》までに 会社《かいしゃ》に 帰《かえ》ります。', 'reading' => 'Hiru made ni kaisha ni kaerimasu.', 'id' => 'Saya kembali ke kantor sebelum siang.', 'en' => 'I will be back at the office before noon.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'それ／その／そう (merujuk pada ucapan sebelumnya)',
                'title_en' => 'それ／その／そう (referring back to what was said)',
                'pattern' => 'それ／その＋Ｎ／そう',
                'payload' => [
                    'explanation_id' => 'Di N5 それ dan その menunjuk benda yang dekat dengan lawan bicara. Di sini kegunaannya bertambah: それ, その, そう juga menunjuk isi pembicaraan yang baru saja muncul. それ = hal yang baru dikatakan lawan bicara. その＋Ｎ = Ｎ yang baru disebut. そう = \"begitu\", dipakai bersama します／思《おも》います／言《い》います untuk menyebut isi ucapan atau saran tadi. Dalam tulisan, その merujuk isi kalimat sebelumnya.',
                    'explanation_en' => 'In N5 それ and その point to things near the listener. Here their use grows: それ, その and そう also refer to content that has just come up in the conversation. それ = the matter the other person just mentioned. その＋Ｎ = the Ｎ that was just mentioned. そう = \"like that\", used with します／思《おも》います／言《い》います to refer to the content of what was said or suggested. In writing, その refers back to the previous sentence.',
                    'notes_id' => [
                        'Pasangan tetap yang sering muncul: それは 大変《たいへん》ですね (wah, itu repot), その とき (pada saat itu), その 場合《ばあい》は (dalam hal itu), そう します (baik, akan saya lakukan begitu), そう 思《おも》います (saya kira begitu).',
                        'これ／この／こう dipakai untuk hal yang pembicara sendiri sedang ceritakan atau yang ada di dekatnya; それ／その／そう untuk hal yang datang dari lawan bicara atau dari kalimat sebelumnya.',
                    ],
                    'notes_en' => [
                        'Fixed expressions you will see often: それは 大変《たいへん》ですね (that sounds hard), その とき (at that time), その 場合《ばあい》は (in that case), そう します (all right, I will do that), そう 思《おも》います (I think so).',
                        'これ／この／こう is for what the speaker is telling or what is near the speaker; それ／その／そう is for what came from the listener or from the previous sentence.',
                    ],
                    'examples' => [
                        ['ja' => 'あした、国《くに》へ 帰《かえ》ります。……それは いいですね。', 'reading' => 'Ashita, kuni e kaerimasu. ...Sore wa ii desu ne.', 'id' => 'Besok saya pulang ke kampung halaman. ……Wah, itu bagus ya.', 'en' => 'Tomorrow I am going back to my home country. ...That is nice.'],
                        ['ja' => '先週《せんしゅう》、友達《ともだち》が 日本《にほん》へ 来《き》ました。その 友達《ともだち》と いっしょに 買《か》い物《もの》に 行《い》きました。', 'reading' => 'Senshuu, tomodachi ga Nihon e kimashita. Sono tomodachi to issho ni kaimono ni ikimashita.', 'id' => 'Minggu lalu teman saya datang ke Jepang. Saya pergi berbelanja bersama teman itu.', 'en' => 'Last week a friend came to Japan. I went shopping with that friend.'],
                        ['ja' => '頭《あたま》が 痛《いた》いんです。……じゃ、早《はや》く 帰《かえ》って 休《やす》んだ ほうが いいですよ。……ええ、そう します。', 'reading' => 'Atama ga itai n desu. ...Ja, hayaku kaette yasunda hou ga ii desu yo. ...Ee, sou shimasu.', 'id' => 'Kepala saya sakit. ……Kalau begitu, sebaiknya cepat pulang dan istirahat. ……Ya, saya akan begitu.', 'en' => 'My head hurts. ...Then you had better go home early and rest. ...Yes, I will do that.'],
                        ['ja' => '地震《じしん》が ありました。その とき、わたしは 電車《でんしゃ》の 中《なか》に いました。', 'reading' => 'Jishin ga arimashita. Sono toki, watashi wa densha no naka ni imashita.', 'id' => 'Terjadi gempa bumi. Pada saat itu saya sedang berada di dalam kereta.', 'en' => 'There was an earthquake. At that moment I was on the train.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Kurang enak badan',
                        'title_en' => 'Feeling unwell',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'ワヒュさん、元気《げんき》が ありませんね。どうしたんですか。', 'reading' => 'Wahyu-san, genki ga arimasen ne. Doushita n desu ka.', 'id' => 'Wahyu, kamu kelihatan lesu. Ada apa?', 'en' => 'Wahyu, you do not look well. What is wrong?'],
                            ['speaker' => 'ワヒュ', 'ja' => '昼《ひる》ごはんの 後《あと》から 気分《きぶん》が 悪《わる》いんです。', 'reading' => 'Hirugohan no ato kara kibun ga warui n desu.', 'id' => 'Sejak sesudah makan siang saya merasa tidak enak badan.', 'en' => 'I have felt unwell since lunch.'],
                            ['speaker' => 'サリ', 'ja' => 'それは いけませんね。薬《くすり》を 飲《の》んだ ほうが いいですよ。', 'reading' => 'Sore wa ikemasen ne. Kusuri o nonda hou ga ii desu yo.', 'id' => 'Wah, itu tidak baik. Sebaiknya minum obat.', 'en' => 'Oh dear. You should take some medicine.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、そう します。', 'reading' => 'Ee, sou shimasu.', 'id' => 'Ya, akan saya lakukan.', 'en' => 'Yes, I will do that.'],
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => 'ありました (menemukan sesuatu)',
                'title_en' => 'ありました (finding something)',
                'pattern' => '[Ｎが] ありました',
                'payload' => [
                    'explanation_id' => 'Bentuk lampau ありました tidak selalu berarti \"dulu ada\". Ketika seseorang mencari sesuatu lalu menemukannya, dia mengatakan ありました untuk melaporkan penemuannya: \"Ketemu!\", \"Ada, nih.\" Bentuk ini mengungkapkan bahwa pembicara baru saja menyadari keberadaan benda itu, bukan bahwa benda itu pernah ada lalu hilang.',
                    'explanation_en' => 'The past form ありました does not always mean \"there used to be\". When someone looks for something and finds it, they say ありました to report the discovery: \"Found it!\", \"Here it is.\" It expresses that the speaker has just become aware that the thing exists there, not that it once existed and is now gone.',
                    'notes_id' => [
                        'Pola yang sama berlaku untuk います: いました = \"ketemu\" untuk orang atau makhluk hidup yang dicari.',
                        'Orang yang membantu mencari juga memakai ありました ketika dia menemukan benda itu.',
                    ],
                    'notes_en' => [
                        'The same pattern applies to います: いました = \"found him/her\" for a person or living thing being looked for.',
                        'A person helping to search also says ありました when they find the item.',
                    ],
                    'examples' => [
                        ['ja' => '[かぎが] ありましたよ。ソファの 下《した》に ありました。', 'reading' => '[Kagi ga] arimashita yo. Sofa no shita ni arimashita.', 'id' => '[Kuncinya] ketemu! Ada di bawah sofa.', 'en' => 'I found [the key]. It was under the sofa.'],
                        ['ja' => '探《さが》して いた 本《ほん》は ここに ありましたよ。', 'reading' => 'Sagashite ita hon wa koko ni arimashita yo.', 'id' => 'Buku yang kamu cari ternyata ada di sini.', 'en' => 'The book you were looking for was right here.'],
                        ['ja' => '財布《さいふ》は かばんの 中《なか》に ありました。', 'reading' => 'Saifu wa kaban no naka ni arimashita.', 'id' => 'Dompetnya ketemu di dalam tas.', 'en' => 'The wallet was in the bag.'],
                        ['ja' => 'あ、めがねは ここに ありました。', 'reading' => 'A, megane wa koko ni arimashita.', 'id' => 'Ah, kacamatanya ada di sini.', 'en' => 'Ah, the glasses are here.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mencari kacamata',
                        'title_en' => 'Looking for glasses',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'ワヒュさん、何《なに》を 探《さが》して いるんですか。', 'reading' => 'Wahyu-san, nani o sagashite iru n desu ka.', 'id' => 'Wahyu, kamu sedang mencari apa?', 'en' => 'Wahyu, what are you looking for?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'めがねを 探《さが》して いるんです。', 'reading' => 'Megane o sagashite iru n desu.', 'id' => 'Saya sedang mencari kacamata.', 'en' => 'I am looking for my glasses.'],
                            ['speaker' => 'サリ', 'ja' => 'あっ、ありましたよ。テーブルの 下《した》です。', 'reading' => 'A, arimashita yo. Teeburu no shita desu.', 'id' => 'Ah, ketemu! Ada di bawah meja.', 'en' => 'Oh, found them! They are under the table.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ああ、よかった。ありがとう ございます。', 'reading' => 'Aa, yokatta. Arigatou gozaimasu.', 'id' => 'Syukurlah. Terima kasih.', 'en' => 'Thank goodness. Thank you.'],
                        ],
                    ],
                ],
            ],

            // 6 ------------------------------------------------------------
            [
                'title_id' => 'どこかで／どこかに (partikel yang tidak boleh hilang)',
                'title_en' => 'どこかで／どこかに (particles that cannot be dropped)',
                'pattern' => 'どこかで／どこかに ／ 何か／だれか',
                'payload' => [
                    'explanation_id' => 'どこか (di suatu tempat), 何《なに》か (sesuatu), だれか (seseorang) bisa diikuti partikel. Partikel が, を dan へ di belakangnya boleh dihilangkan dalam bahasa lisan: 何《なに》か 飲《の》みませんか。, どこか 行《い》きませんか。 Tetapi で dan に di belakang どこかで／どこかに tidak boleh dihilangkan, karena tanpa keduanya kalimat kehilangan arti tempat kejadian atau tempat keberadaan.',
                    'explanation_en' => 'どこか (somewhere), 何《なに》か (something) and だれか (someone) can take a particle. The particles が, を and へ after them can be dropped in speech: 何《なに》か 飲《の》みませんか。, どこか 行《い》きませんか。 But で and に after どこかで／どこかに cannot be dropped, because without them the sentence loses the meaning of where something happened or where something exists.',
                    'notes_id' => [
                        'どこかで = di suatu tempat (tempat kejadian), どこかに = ke/di suatu tempat (tempat tujuan atau keberadaan).',
                        'Kalimat negatif atau pertanyaan memakai pola yang sama: どこかに ありませんか。 (adakah di suatu tempat?).',
                    ],
                    'notes_en' => [
                        'どこかで = somewhere (where something happens), どこかに = to/at somewhere (a destination or where something exists).',
                        'Negative sentences and questions use the same pattern: どこかに ありませんか。 (is there one somewhere?).',
                    ],
                    'examples' => [
                        ['ja' => 'どこかで 手袋《てぶくろ》を 落《お》として しまったんです。', 'reading' => 'Dokoka de tebukuro o otoshite shimatta n desu.', 'id' => 'Entah di mana, sarung tangan saya terjatuh.', 'en' => 'I dropped my gloves somewhere.'],
                        ['ja' => 'どこかに コンビニが ありませんか。', 'reading' => 'Dokoka ni konbini ga arimasen ka.', 'id' => 'Adakah minimarket di sekitar sini?', 'en' => 'Is there a convenience store somewhere around here?'],
                        ['ja' => '何《なに》か 飲《の》みませんか。／何《なに》かを 飲《の》みませんか。', 'reading' => 'Nanika nomimasen ka. / Nanika o nomimasen ka.', 'id' => 'Mau minum sesuatu? (を boleh dihilangkan)', 'en' => 'Would you like something to drink? (を can be dropped)'],
                        ['ja' => '休《やす》みの 日《ひ》に、どこか 行《い》きませんか。', 'reading' => 'Yasumi no hi ni, dokoka ikimasen ka.', 'id' => 'Di hari libur, mau pergi ke suatu tempat?', 'en' => 'On your day off, would you like to go somewhere?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Dompet hilang',
                        'title_en' => 'A lost wallet',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'たなかさん、どうしましょう。どこかで 財布《さいふ》を 落《お》として しまったんです。', 'reading' => 'Tanaka-san, doushimashou. Dokoka de saifu o otoshite shimatta n desu.', 'id' => 'Pak/Bu Tanaka, bagaimana ini. Dompet saya terjatuh entah di mana.', 'en' => 'Mr./Ms. Tanaka, what should I do? I dropped my wallet somewhere.'],
                            ['speaker' => 'たなか', 'ja' => 'それは 大変《たいへん》ですね。どこで 最後《さいご》に 見《み》ましたか。', 'reading' => 'Sore wa taihen desu ne. Doko de saigo ni mimashita ka.', 'id' => 'Wah, itu repot. Terakhir kali kamu melihatnya di mana?', 'en' => 'That is trouble. Where did you last see it?'],
                            ['speaker' => 'ワヒュ', 'ja' => '駅《えき》で 切符《きっぷ》を 買《か》った とき、ありました。', 'reading' => 'Eki de kippu o katta toki, arimashita.', 'id' => 'Waktu membeli tiket di stasiun, dompetnya masih ada.', 'en' => 'It was there when I bought my ticket at the station.'],
                            ['speaker' => 'たなか', 'ja' => 'じゃ、駅前《えきまえ》の 交番《こうばん》に 行《い》きましょう。いっしょに 行《い》きますよ。', 'reading' => 'Ja, ekimae no kouban ni ikimashou. Issho ni ikimasu yo.', 'id' => 'Kalau begitu, mari ke pos polisi di depan stasiun. Saya ikut.', 'en' => 'Then let us go to the police box in front of the station. I will go with you.'],
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
            ['slug' => 'n4-pelajaran-4'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 4',
                'name_en' => 'N4 Lesson 4 Vocabulary',
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
     * Dua kata kerja つきます dibedakan dengan keterangan di dalam kurung.
     *
     * @return array<int, array<int, string>>
     */
    private function words(): array
    {
        return [
            // Kosakata utama
            ['開きます', 'あきます', 'akimasu', '[pintu] terbuka', '[door] to open, to be open'],
            ['閉まります', 'しまります', 'shimarimasu', '[pintu] tertutup', '[door] to close, to be closed'],
            ['つきます', 'つきます', 'tsukimasu (lampu)', '[lampu] menyala', '[light] to come on'],
            ['消えます', 'きえます', 'kiemasu', '[lampu] terpadam', '[light] to go out'],
            ['壊れます', 'こわれます', 'kowaremasu', '[kursi] rusak', '[chair] to break, to be broken'],
            ['割れます', 'われます', 'waremasu', '[gelas] pecah', '[glass] to break, to shatter'],
            ['折れます', 'おれます', 'oremasu', '[pohon] patah', '[tree] to snap, to break off'],
            ['破れます', 'やぶれます', 'yaburemasu', '[kertas] robek', '[paper] to tear'],
            ['汚れます', 'よごれます', 'yogoremasu', '[baju] menjadi kotor', '[clothes] to get dirty'],
            ['付きます', 'つきます', 'tsukimasu (kantong)', '[kantong] terpasang', '[pocket] to be attached'],
            ['外れます', 'はずれます', 'hazuremasu', '[kancing] terlepas', '[button] to come off'],
            ['止まります', 'とまります', 'tomarimasu', '[mobil] berhenti', '[car] to stop'],
            ['間違えます', 'まちがえます', 'machigaemasu', 'bersalah, keliru', 'to make a mistake'],
            ['落とします', 'おとします', 'otoshimasu', 'menjatuhkan, kehilangan', 'to drop, to lose'],
            ['掛かります', 'かかります', 'kakarimasu', '[kunci] terkunci', '[lock] to be locked'],
            ['拭きます', 'ふきます', 'fukimasu', 'mengelap', 'to wipe'],
            ['取り替えます', 'とりかえます', 'torikaemasu', 'mengganti', 'to replace, to change'],
            ['片づけます', 'かたづけます', 'katazukemasu', 'membereskan', 'to tidy up'],
            ['[お]皿', '[お]さら', 'osara', 'piring', 'plate, dish'],
            ['[お]茶碗', '[お]ちゃわん', 'ochawan', 'mangkuk', 'rice bowl'],
            ['コップ', 'コップ', 'koppu', 'gelas', 'cup, glass'],
            ['ガラス', 'ガラス', 'garasu', 'kaca', 'glass (material)'],
            ['袋', 'ふくろ', 'fukuro', 'kantong plastik／kertas', 'bag'],
            ['書類', 'しょるい', 'shorui', 'dokumen', 'documents'],
            ['枝', 'えだ', 'eda', 'ranting', 'branch, twig'],
            ['駅員', 'えきいん', 'ekiin', 'petugas stasiun', 'station staff'],
            ['交番', 'こうばん', 'kouban', 'pos polisi', 'police box'],
            ['スピーチ', 'スピーチ', 'supiichi', 'pidato', 'speech'],
            ['返事', 'へんじ', 'henji', 'jawaban', 'reply, answer'],
            ['お先にどうぞ。', 'おさきにどうぞ。', 'osaki ni douzo', 'Silakan duluan!', 'After you!'],

            // 会話 (percakapan)
            ['今の電車', 'いまのでんしゃ', 'ima no densha', 'kereta tadi', 'the train a moment ago'],
            ['忘れ物', 'わすれもの', 'wasuremono', 'barang yang tertinggal', 'a forgotten item, lost property'],
            ['このくらい', 'このくらい', 'konokurai', 'segini', 'about this much'],
            ['〜側', '〜がわ', 'gawa', 'sebelah ~', '~ side'],
            ['ポケット', 'ポケット', 'poketto', 'saku (kantong baju)', 'pocket'],
            ['〜辺', '〜へん', 'hen', 'sekitar ~', 'around ~, vicinity of ~'],
            ['覚えていません', 'おぼえていません', 'oboete imasen', 'tidak ingat', 'I do not remember'],
            ['網棚', 'あみだな', 'amidana', 'rak bagasi', 'luggage rack'],
            ['確か', 'たしか', 'tashika', 'kalau tidak salah', 'if I am not mistaken'],
            ['[ああ、]よかった。', '[ああ、]よかった。', 'yokatta', '[O,] syukur. (dipakai saat merasa lega)', '[Oh,] thank goodness.'],

            // 読み物 (bacaan)
            ['地震', 'じしん', 'jishin', 'gempa bumi', 'earthquake'],
            ['壁', 'かべ', 'kabe', 'dinding', 'wall'],
            ['針', 'はり', 'hari', 'jarum', 'needle, hand (of a clock)'],
            ['指します', 'さします', 'sashimasu', 'menunjuk', 'to point at'],
            ['駅前', 'えきまえ', 'ekimae', 'depan stasiun', 'in front of the station'],
            ['倒れます', 'たおれます', 'taoremasu', 'jatuh, roboh', 'to fall down, to collapse'],
            ['西', 'にし', 'nishi', 'barat', 'west'],
            ['〜の方', '〜のほう', 'no hou', 'sebelah ~', 'the ~ side, toward ~'],
            ['燃えます', 'もえます', 'moemasu', 'terbakar', 'to burn'],
            ['レポーター', 'レポーター', 'repootaa', 'wartawan, jurnalis, pelapor', 'reporter'],
        ];
    }
}
