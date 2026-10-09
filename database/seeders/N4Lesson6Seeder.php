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

class N4Lesson6Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 6: "Niat dan Rencana" — bentuk maksud (意向形) dan
     * pola-pola untuk menyatakan niat, rencana, dan hal yang belum selesai.
     * Level N4 berdiri di samping N5 dan dipilih lewat selektor N5 / N4 di
     * Tata Bahasa, Kosakata dan Referensi Tata Bahasa.
     *
     * Isi:
     *   - Level N4, Unit order 6 ("Pelajaran 6"). Pelajaran 2-5 N4 belum ada;
     *     Unit.order hanya perlu unik per level, jadi celah ini aman.
     *   - Lesson Bunpou (order 0, category grammar) dengan 7 kartu:
     *       1. Bentuk maksud: cara membentuk
     *       2. Bentuk maksud sebagai bentuk biasa 〜ましょう (ajakan, tawaran, usul)
     *       3. Vよう と 思っています／思います
     *       4. Vる／Vない つもりです
     *       5. Vる／Nの 予定です
     *       6. まだ Vて いません
     *       7. Bentuk ます sebagai kata benda (帰ります → 帰り)
     *   - Kategori kosakata "n4-pelajaran-6" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Referensi Tata Bahasa (ringkasan bentuk maksud + daftar bidang keilmuan)
     * ada di resources/js/pages/lampiran/index.vue, bukan di seeder.
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus. Penjelasan, contoh
     * kalimat dan dialog ditulis baru untuk aplikasi ini. Nama diri (nama
     * tempat, tokoh, kereta) sengaja tidak dimasukkan, sama seperti daftar
     * nama diri di N5.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson6Seeder
     *
     * Kata kunci pencarian SQL memakai ASCII (kategori.slug + romaji), bukan
     * huruf Jepang (lihat docs/AGENTS.md aturan 4).
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
            ['level_id' => $level->id, 'order' => 6],
            [
                'title_id' => 'Pelajaran 6: Niat dan Rencana',
                'title_en' => 'Lesson 6: Intentions and Plans',
                'description_id' => 'Bentuk maksud (〜よう), 〜ようと思っています, 〜つもりです, 〜予定です, まだ〜ていません.',
                'description_en' => 'Volitional form (〜よう), 〜ようと思っています, 〜つもりです, 〜予定です, まだ〜ていません.',
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
                'title_id' => 'Bentuk maksud (いこうけい): cara membentuk',
                'title_en' => 'Volitional form (いこうけい): how to make it',
                'pattern' => 'Ｖ ます → Ｖ(よ)う',
                'payload' => [
                    'explanation_id' => 'Bentuk maksud adalah bentuk biasa dari 〜ましょう, yaitu "mari …" atau "akan …". Cara membuatnya tergantung kelompok kata kerja. Kelompok I: ambil bentuk ます, ubah bunyi terakhir sebelum ます dari baris い ke baris お, lalu tambahkan う (書《か》きます → 書《か》こう). Kelompok II: buang ます dan tambahkan よう (食《た》べます → 食《た》べよう). Kelompok III: します → しよう, 来《き》ます → 来《こ》よう.',
                    'explanation_en' => 'The volitional form is the plain form of 〜ましょう, meaning "let us …" or "I will …". How you make it depends on the verb group. Group I: take the ます form, change the last sound before ます from the い-row to the お-row, then add う (書《か》きます → 書《か》こう). Group II: drop ます and add よう (食《た》べます → 食《た》べよう). Group III: します → しよう, 来《き》ます → 来《こ》よう.',
                    'notes_id' => [
                        'Perubahan bunyi kelompok I: い→おう, き→こう, ぎ→ごう, し→そう, ち→とう, に→のう, び→ぼう, み→もう, り→ろう.',
                        'Cara mengingat: kelompok I selalu berakhir 〜おう／〜こう／〜そう／〜とう／〜ろう dan sejenisnya (bunyi o + う), kelompok II berakhir 〜よう, kelompok III しよう／こよう.',
                        '来《く》る adalah satu-satunya kata kerja yang vokal awalnya berubah: き → こ.',
                    ],
                    'notes_en' => [
                        'Group I sound changes: い→おう, き→こう, ぎ→ごう, し→そう, ち→とう, に→のう, び→ぼう, み→もう, り→ろう.',
                        'A memory aid: group I always ends in an o-sound + う (〜おう／〜こう／〜そう／〜とう／〜ろう …), group II ends in 〜よう, group III is しよう／こよう.',
                        '来《く》る is the only verb whose first vowel changes: き → こ.',
                    ],
                    'examples' => [
                        ['ja' => '書《か》く → 書《か》こう', 'reading' => 'kaku → kakou', 'id' => 'menulis → mari menulis (kelompok I)', 'en' => 'to write → let us write (group I)'],
                        ['ja' => '読《よ》む → 読《よ》もう', 'reading' => 'yomu → yomou', 'id' => 'membaca → mari membaca (kelompok I)', 'en' => 'to read → let us read (group I)'],
                        ['ja' => '遊《あそ》ぶ → 遊《あそ》ぼう', 'reading' => 'asobu → asobou', 'id' => 'bermain → mari bermain (kelompok I)', 'en' => 'to play → let us play (group I)'],
                        ['ja' => '急《いそ》ぐ → 急《いそ》ごう', 'reading' => 'isogu → isogou', 'id' => 'bergegas → mari bergegas (kelompok I)', 'en' => 'to hurry → let us hurry (group I)'],
                        ['ja' => '食《た》べる → 食《た》べよう', 'reading' => 'taberu → tabeyou', 'id' => 'makan → mari makan (kelompok II)', 'en' => 'to eat → let us eat (group II)'],
                        ['ja' => '見《み》る → 見《み》よう', 'reading' => 'miru → miyou', 'id' => 'melihat → mari melihat (kelompok II)', 'en' => 'to see → let us look (group II)'],
                        ['ja' => 'する → しよう', 'reading' => 'suru → shiyou', 'id' => 'melakukan → mari melakukan (kelompok III)', 'en' => 'to do → let us do (group III)'],
                        ['ja' => '来《く》る → 来《こ》よう', 'reading' => 'kuru → koyou', 'id' => 'datang → mari datang (kelompok III)', 'en' => 'to come → let us come (group III)'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Lapar di tengah hari',
                        'title_en' => 'Hungry at midday',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'おなかが すいたね。何《なに》か 食《た》べよう。', 'reading' => 'Onaka ga suita ne. Nani ka tabeyou.', 'id' => 'Perutku lapar. Ayo makan sesuatu.', 'en' => 'I am hungry. Let us eat something.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'うん。あの 店《みせ》に 入《はい》ろう。', 'reading' => 'Un. Ano mise ni hairou.', 'id' => 'Ya. Ayo masuk ke toko itu.', 'en' => 'Yes. Let us go into that shop.'],
                            ['speaker' => 'サリ', 'ja' => 'いいね。まず メニューを 見《み》よう。', 'reading' => 'Ii ne. Mazu menyuu o miyou.', 'id' => 'Boleh. Lihat menunya dulu.', 'en' => 'Sounds good. Let us look at the menu first.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => 'Bentuk maksud sebagai 〜ましょう biasa (ajakan, tawaran, usul)',
                'title_en' => 'Volitional form as plain 〜ましょう (invitation, offer, suggestion)',
                'pattern' => 'Ｖ(よ)う ／ Ｖ(よ)うか',
                'payload' => [
                    'explanation_id' => 'Dalam percakapan akrab dengan teman atau keluarga, bentuk maksud dipakai untuk tiga hal: mengajak ("mari …"), menawarkan bantuan ("biar aku …") dan mengusulkan ("bagaimana kalau …?"). Ajakan cukup memakai bentuk maksud saja. Tawaran dan usul yang menunggu tanggapan lawan bicara memakai 〜ようか dengan nada naik. Dengan atasan atau orang yang belum akrab, tetap pakai 〜ましょう／〜ましょうか.',
                    'explanation_en' => 'In casual conversation with friends or family, the volitional form does three jobs: inviting ("let us …"), offering help ("let me …") and suggesting ("how about …?"). An invitation needs only the volitional form. An offer or suggestion that waits for the other person to respond uses 〜ようか with a rising tone. With superiors or people you are not close to, keep using 〜ましょう／〜ましょうか.',
                    'notes_id' => [
                        'Ajakan: 〜よう (atau bentuk negatif bernada naik 〜ない？). Tawaran dan usul: 〜ようか.',
                        'Pada bentuk biasa, ajakan tidak perlu か di akhir, tetapi usul dan tawaran dengan 〜ようか memerlukan か.',
                        'Setuju: うん、そうしよう／いいね。 Menolak dengan halus: ごめん、ちょっと……。',
                    ],
                    'notes_en' => [
                        'Invitation: 〜よう (or the negative with rising tone, 〜ない？). Offer and suggestion: 〜ようか.',
                        'In plain speech an invitation does not need か at the end, but a suggestion or offer with 〜ようか does.',
                        'Agreeing: うん、そうしよう／いいね。 Declining gently: ごめん、ちょっと……。',
                    ],
                    'examples' => [
                        ['ja' => '日曜日《にちようび》に 映画《えいが》を 見《み》に 行《い》こう。', 'reading' => 'Nichiyoubi ni eiga o mi ni ikou.', 'id' => 'Mari nonton film hari Minggu.', 'en' => 'Let us go and see a film on Sunday.'],
                        ['ja' => '荷物《にもつ》を 持《も》とうか。', 'reading' => 'Nimotsu o motou ka.', 'id' => 'Biar kubawakan barangnya?', 'en' => 'Shall I carry your luggage?'],
                        ['ja' => '暑《あつ》いね。窓《まど》を 開《あ》けようか。', 'reading' => 'Atsui ne. Mado o akeyou ka.', 'id' => 'Panas, ya. Kubuka jendelanya?', 'en' => 'It is hot. Shall I open the window?'],
                        ['ja' => 'いっしょに 昼《ひる》ごはんを 食《た》べない？', 'reading' => 'Issho ni hirugohan o tabenai?', 'id' => 'Makan siang bareng, yuk?', 'en' => 'Want to have lunch together?'],
                        ['ja' => 'あしたは 駅《えき》で 会《あ》おうか。……うん、そうしよう。', 'reading' => 'Ashita wa eki de aou ka. ...Un, sou shiyou.', 'id' => 'Besok bertemu di stasiun, bagaimana? ……Ya, begitu saja.', 'en' => 'How about meeting at the station tomorrow? ...Yes, let us do that.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Rencana ke taman',
                        'title_en' => 'A plan for the park',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'きょうは 天気《てんき》が いいね。公園《こうえん》へ 行《い》こうか。', 'reading' => 'Kyou wa tenki ga ii ne. Kouen e ikou ka.', 'id' => 'Hari ini cuacanya bagus. Ke taman, yuk?', 'en' => 'The weather is nice today. Shall we go to the park?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいね。お弁当《べんとう》を 持《も》って 行《い》こう。', 'reading' => 'Ii ne. Obentou o motte ikou.', 'id' => 'Boleh. Kita bawa bekal.', 'en' => 'Good idea. Let us take a packed lunch.'],
                            ['speaker' => 'サリ', 'ja' => 'じゃ、わたしが サンドイッチを 作《つく》ろう。', 'reading' => 'Ja, watashi ga sandoicchi o tsukurou.', 'id' => 'Kalau begitu, aku yang buat roti lapis.', 'en' => 'Then I will make the sandwiches.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ありがとう。飲《の》み物《もの》は ぼくが 買《か》うよ。', 'reading' => 'Arigatou. Nomimono wa boku ga kau yo.', 'id' => 'Terima kasih. Minumannya aku yang beli.', 'en' => 'Thanks. I will buy the drinks.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => 'Vよう と 思っています／思います (niat yang masih dipikirkan)',
                'title_en' => 'Vよう と 思っています／思います (an intention still being considered)',
                'pattern' => 'Ｖ(よ)う と 思《おも》っています',
                'payload' => [
                    'explanation_id' => 'Dipakai untuk menyatakan niat atau rencana yang masih berupa pikiran: "saya berpikir untuk …". Bentuknya: kata kerja bentuk maksud + と + 思《おも》います／思《おも》っています. 〜と思《おも》います adalah niat yang muncul saat berbicara dan hanya untuk pembicara sendiri. 〜と思《おも》っています menunjukkan niat yang sudah ada sebelumnya dan masih dipegang sampai sekarang, sehingga bisa dipakai juga untuk orang ketiga.',
                    'explanation_en' => 'Used to state an intention or plan that is still a thought: "I am thinking of …". Form: volitional form + と + 思《おも》います／思《おも》っています. 〜と思《おも》います is an intention that comes up as you speak and is only for the speaker. 〜と思《おも》っています shows an intention that already existed and is still held now, so it can also be used for a third person.',
                    'notes_id' => [
                        'Untuk orang ketiga hanya 〜と思《おも》っています: 姉《あね》は 留学《りゅうがく》しようと 思《おも》っています。',
                        'Menanyakan niat lawan bicara: 〜(よ)うと 思《おも》っていますか.',
                        'Kalimatnya tetap sopan (です／ます) di bagian akhir; bentuk maksud sebelum と memang bentuk biasa.',
                    ],
                    'notes_en' => [
                        'For a third person only 〜と思《おも》っています works: 姉《あね》は 留学《りゅうがく》しようと 思《おも》っています。',
                        'To ask about the listener intention: 〜(よ)うと 思《おも》っていますか.',
                        'The sentence still ends politely (です／ます); the volitional form before と is simply the plain form.',
                    ],
                    'examples' => [
                        ['ja' => 'ことしの 夏休《なつやす》みは 山《やま》に 登《のぼ》ろうと 思《おも》っています。', 'reading' => 'Kotoshi no natsuyasumi wa yama ni noborou to omotte imasu.', 'id' => 'Libur musim panas tahun ini saya berpikir untuk mendaki gunung.', 'en' => 'This summer holiday I am thinking of climbing a mountain.'],
                        ['ja' => '今晩《こんばん》は 早《はや》く 寝《ね》ようと 思《おも》います。', 'reading' => 'Konban wa hayaku neyou to omoimasu.', 'id' => 'Malam ini saya mau tidur lebih awal.', 'en' => 'Tonight I think I will go to bed early.'],
                        ['ja' => '姉《あね》は 来年《らいねん》、大学院《だいがくいん》で 勉強《べんきょう》しようと 思《おも》っています。', 'reading' => 'Ane wa rainen, daigakuin de benkyou shiyou to omotte imasu.', 'id' => 'Kakak perempuan saya berpikir untuk belajar di program pascasarjana tahun depan.', 'en' => 'My older sister is thinking of studying at graduate school next year.'],
                        ['ja' => '来月《らいげつ》、休《やす》みを 取《と》ろうと 思《おも》っています。', 'reading' => 'Raigetsu, yasumi o torou to omotte imasu.', 'id' => 'Bulan depan saya berpikir untuk mengambil cuti.', 'en' => 'Next month I am thinking of taking some time off.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Liburan berturut-turut',
                        'title_en' => 'The long holiday',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '来週《らいしゅう》の 連休《れんきゅう》は どう しますか。', 'reading' => 'Raishuu no renkyuu wa dou shimasu ka.', 'id' => 'Libur panjang minggu depan, mau bagaimana?', 'en' => 'What will you do for the long holiday next week?'],
                            ['speaker' => 'ワヒュ', 'ja' => '友達《ともだち》と 海《うみ》へ 行《い》こうと 思《おも》っています。', 'reading' => 'Tomodachi to umi e ikou to omotte imasu.', 'id' => 'Saya berpikir untuk pergi ke pantai dengan teman.', 'en' => 'I am thinking of going to the sea with friends.'],
                            ['speaker' => 'たなか', 'ja' => 'いいですね。', 'reading' => 'Ii desu ne.', 'id' => 'Wah, bagus.', 'en' => 'That sounds nice.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'たなかさんは？', 'reading' => 'Tanaka-san wa?', 'id' => 'Kalau Anda?', 'en' => 'And you?'],
                            ['speaker' => 'たなか', 'ja' => 'わたしは 家《いえ》で ゆっくり 休《やす》もうと 思《おも》っています。', 'reading' => 'Watashi wa ie de yukkuri yasumou to omotte imasu.', 'id' => 'Saya berpikir untuk beristirahat santai di rumah.', 'en' => 'I am thinking of relaxing at home.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'Vる／Vない つもりです (niat yang sudah bulat)',
                'title_en' => 'Vる／Vない つもりです (a firm intention)',
                'pattern' => 'Ｖ(辞書形《じしょけい》)／Ｖ ない ＋ つもりです',
                'payload' => [
                    'explanation_id' => 'Menyatakan niat yang sudah dipegang teguh oleh pembicara: "saya bermaksud …". Kata kerja bentuk kamus + つもりです berarti bermaksud melakukan sesuatu; bentuk ない + つもりです berarti bermaksud tidak melakukannya. Dibanding 〜ようと思《おも》っています yang terdengar masih menimbang-nimbang, つもりです terdengar lebih pasti — keputusannya sudah bulat.',
                    'explanation_en' => 'States an intention the speaker holds firmly: "I intend to …". Dictionary form + つもりです means intending to do something; ない form + つもりです means intending not to. Compared with 〜ようと 思《おも》っています, which still sounds like weighing things up, つもりです sounds more definite — the decision is made.',
                    'notes_id' => [
                        'Negatif: 〜ない つもりです (bukan 〜つもりじゃ ありません yang berarti "bukan bermaksud demikian").',
                        'Pilih 〜ようと 思《おも》っています untuk niat yang masih lunak; pilih つもりです untuk keputusan yang sudah pasti.',
                        'Bisa juga untuk orang ketiga: 彼《かれ》は 会社《かいしゃ》を 辞《や》めない つもりです。',
                    ],
                    'notes_en' => [
                        'Negative: 〜ない つもりです (not 〜つもりじゃ ありません, which means "that was not my intention").',
                        'Choose 〜ようと 思《おも》っています for a soft intention; choose つもりです for a settled decision.',
                        'It can also be used for a third person: 彼《かれ》は 会社《かいしゃ》を 辞《や》めない つもりです。',
                    ],
                    'examples' => [
                        ['ja' => 'あしたから 毎朝《まいあさ》 走《はし》る つもりです。', 'reading' => 'Ashita kara maiasa hashiru tsumori desu.', 'id' => 'Mulai besok saya bermaksud berlari setiap pagi.', 'en' => 'From tomorrow I intend to run every morning.'],
                        ['ja' => 'お酒《さけ》は もう 飲《の》まない つもりです。', 'reading' => 'Osake wa mou nomanai tsumori desu.', 'id' => 'Minuman keras sudah tidak akan saya minum lagi.', 'en' => 'I intend not to drink alcohol any more.'],
                        ['ja' => '忙《いそが》しくても、毎日《まいにち》 日本語《にほんご》を 勉強《べんきょう》する つもりです。', 'reading' => 'Isogashikute mo, mainichi nihongo o benkyou suru tsumori desu.', 'id' => 'Sesibuk apa pun, saya bermaksud belajar bahasa Jepang setiap hari.', 'en' => 'Even when I am busy, I intend to study Japanese every day.'],
                        ['ja' => '来週《らいしゅう》の パーティーには 行《い》かない つもりです。', 'reading' => 'Raishuu no paatii ni wa ikanai tsumori desu.', 'id' => 'Pesta minggu depan saya bermaksud tidak datang.', 'en' => 'I intend not to go to next week party.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menjaga kesehatan',
                        'title_en' => 'Looking after your health',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'ワヒュさん、このごろ 運動《うんどう》して いますね。', 'reading' => 'Wahyu-san, konogoro undou shite imasu ne.', 'id' => 'Wahyu, belakangan ini kamu rajin berolahraga, ya.', 'en' => 'Wahyu, you have been exercising lately.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、健康《けんこう》の ために 毎日《まいにち》 歩《ある》く つもりです。', 'reading' => 'Ee, kenkou no tame ni mainichi aruku tsumori desu.', 'id' => 'Ya, demi kesehatan saya bermaksud berjalan kaki setiap hari.', 'en' => 'Yes, I intend to walk every day for my health.'],
                            ['speaker' => 'たなか', 'ja' => '甘《あま》い 物《もの》も 食《た》べないんですか。', 'reading' => 'Amai mono mo tabenai n desu ka.', 'id' => 'Makanan manis juga tidak dimakan?', 'en' => 'And you will not eat sweets either?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、しばらく 食《た》べない つもりです。', 'reading' => 'Ee, shibaraku tabenai tsumori desu.', 'id' => 'Ya, untuk sementara saya bermaksud tidak memakannya.', 'en' => 'Right, I intend not to eat them for a while.'],
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => 'Vる／Nの 予定です (rencana dan jadwal)',
                'title_en' => 'Vる／Nの 予定です (plans and schedules)',
                'pattern' => 'Ｖ(辞書形《じしょけい》)／Ｎの ＋ 予定《よてい》です',
                'payload' => [
                    'explanation_id' => 'Dipakai untuk menjelaskan rencana atau jadwal yang sudah diatur. Kata kerja bentuk kamus atau kata benda + の, diikuti 予定《よてい》です. Berbeda dari つもり yang menekankan kehendak pribadi, 予定《よてい》 menekankan bahwa jadwalnya memang sudah ditetapkan — oleh pembicara sendiri, orang lain, atau pihak lain seperti perusahaan atau sekolah.',
                    'explanation_en' => 'Used to describe a plan or schedule that has been arranged. Dictionary form or noun + の, followed by 予定《よてい》です. Unlike つもり, which stresses personal will, 予定《よてい》 stresses that the schedule has actually been set — by the speaker, by someone else, or by an organisation such as a company or school.',
                    'notes_id' => [
                        'Dengan kata benda dipakai の: 六月《ろくがつ》の 予定《よてい》です, 一時間《いちじかん》の 予定《よてい》です.',
                        'Pertanyaannya: 〜予定《よてい》ですか. Cocok untuk menanyakan jadwal orang lain tanpa terdengar mendesak.',
                        'Karena sifatnya jadwal, 予定《よてい》 mudah dipakai untuk orang ketiga maupun kegiatan bersama.',
                    ],
                    'notes_en' => [
                        'With a noun, use の: 六月《ろくがつ》の 予定《よてい》です, 一時間《いちじかん》の 予定《よてい》です.',
                        'The question form is 〜予定《よてい》ですか — a gentle way to ask about someone else schedule.',
                        'Because it describes a schedule, 予定《よてい》 is easy to use for third persons and shared events.',
                    ],
                    'examples' => [
                        ['ja' => '来月《らいげつ》、大阪《おおさか》の 支店《してん》へ 行《い》く 予定《よてい》です。', 'reading' => 'Raigetsu, Oosaka no shiten e iku yotei desu.', 'id' => 'Bulan depan rencananya saya pergi ke kantor cabang di Osaka.', 'en' => 'Next month I am scheduled to go to the Osaka branch office.'],
                        ['ja' => '会議《かいぎ》は 午後《ごご》三時《さんじ》から 一時間《いちじかん》の 予定《よてい》です。', 'reading' => 'Kaigi wa gogo sanji kara ichijikan no yotei desu.', 'id' => 'Rapat dijadwalkan mulai pukul tiga sore selama satu jam.', 'en' => 'The meeting is scheduled for one hour from three in the afternoon.'],
                        ['ja' => '結婚式《けっこんしき》は 六月《ろくがつ》の 予定《よてい》です。', 'reading' => 'Kekkonshiki wa rokugatsu no yotei desu.', 'id' => 'Upacara pernikahan dijadwalkan bulan Juni.', 'en' => 'The wedding is planned for June.'],
                        ['ja' => '出張《しゅっちょう》は 来週《らいしゅう》の 木曜日《もくようび》までの 予定《よてい》です。', 'reading' => 'Shucchou wa raishuu no mokuyoubi made no yotei desu.', 'id' => 'Dinas luar dijadwalkan sampai Kamis minggu depan.', 'en' => 'The business trip is scheduled to last until next Thursday.'],
                        ['ja' => '何時《なんじ》に 帰《かえ》る 予定《よてい》ですか。……六時《ろくじ》ごろに 帰《かえ》る 予定《よてい》です。', 'reading' => 'Nanji ni kaeru yotei desu ka. ...Rokuji goro ni kaeru yotei desu.', 'id' => 'Rencananya pulang jam berapa? ……Rencananya pulang sekitar jam enam.', 'en' => 'What time do you plan to go home? ...I plan to go home around six.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Sebelum dinas luar',
                        'title_en' => 'Before a business trip',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'あしたから 出張《しゅっちょう》ですね。', 'reading' => 'Ashita kara shucchou desu ne.', 'id' => 'Mulai besok Anda dinas luar, ya.', 'en' => 'You start your business trip tomorrow, right?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい。本社《ほんしゃ》で 会議《かいぎ》が あるんです。', 'reading' => 'Hai. Honsha de kaigi ga aru n desu.', 'id' => 'Ya. Ada rapat di kantor pusat.', 'en' => 'Yes. There is a meeting at the head office.'],
                            ['speaker' => 'たなか', 'ja' => 'いつ 帰《かえ》る 予定《よてい》ですか。', 'reading' => 'Itsu kaeru yotei desu ka.', 'id' => 'Rencananya kapan pulang?', 'en' => 'When do you plan to come back?'],
                            ['speaker' => 'ワヒュ', 'ja' => '金曜日《きんようび》の 夜《よる》に 帰《かえ》る 予定《よてい》です。', 'reading' => 'Kinyoubi no yoru ni kaeru yotei desu.', 'id' => 'Rencananya pulang Jumat malam.', 'en' => 'I plan to come back on Friday night.'],
                        ],
                    ],
                ],
            ],

            // 6 ------------------------------------------------------------
            [
                'title_id' => 'まだ Vて いません (belum dilakukan)',
                'title_en' => 'まだ Vて いません (not yet done)',
                'pattern' => 'まだ Ｖて いません',
                'payload' => [
                    'explanation_id' => 'Menyatakan bahwa sampai saat berbicara suatu kejadian atau perbuatan belum terjadi atau belum selesai, dan biasanya diharapkan akan terjadi nanti. Bentuknya: まだ + kata kerja bentuk-て + いません. Pola ini lazim dipakai untuk menjawab pertanyaan もう 〜ましたか ("sudah … belum?"). Jawaban singkatnya: いいえ、まだです。',
                    'explanation_en' => 'States that, up to the moment of speaking, something has not happened or has not been finished, and is usually expected to happen later. Form: まだ + verb て-form + いません. It is the usual answer to もう 〜ましたか ("have you … yet?"). The short answer is いいえ、まだです。',
                    'notes_id' => [
                        'Jawaban positif: はい、もう 〜ました. Jawaban negatif: いいえ、まだ 〜て いません／いいえ、まだです.',
                        'Jangan tertukar dengan 〜ませんでした, yang hanya menyatakan bahwa sesuatu tidak dilakukan di masa lalu: きのうは 勉強《べんきょう》しませんでした (kemarin tidak belajar).',
                        'Dengan kata kerja yang menunjukkan perubahan keadaan, artinya "keadaannya belum berubah": 店《みせ》は まだ 開《あ》いて いません (tokonya belum buka).',
                    ],
                    'notes_en' => [
                        'Positive answer: はい、もう 〜ました. Negative answer: いいえ、まだ 〜て いません／いいえ、まだです.',
                        'Do not mix it up with 〜ませんでした, which only says something was not done in the past: きのうは 勉強《べんきょう》しませんでした (I did not study yesterday).',
                        'With verbs of change of state it means "the state has not changed yet": 店《みせ》は まだ 開《あ》いて いません (the shop is not open yet).',
                    ],
                    'examples' => [
                        ['ja' => 'まだ 昼《ひる》ごはんを 食《た》べて いません。', 'reading' => 'Mada hirugohan o tabete imasen.', 'id' => 'Saya belum makan siang.', 'en' => 'I have not had lunch yet.'],
                        ['ja' => '宿題《しゅくだい》は もう 終《お》わりましたか。……いいえ、まだ 終《お》わって いません。', 'reading' => 'Shukudai wa mou owarimashita ka. ...Iie, mada owatte imasen.', 'id' => 'Apakah PR sudah selesai? ……Belum, belum selesai.', 'en' => 'Have you finished your homework? ...No, not yet.'],
                        ['ja' => '作文《さくぶん》は まだ 出《だ》して いません。', 'reading' => 'Sakubun wa mada dashite imasen.', 'id' => 'Karangannya belum saya kumpulkan.', 'en' => 'I have not handed in the essay yet.'],
                        ['ja' => 'たなかさんは まだ 来《き》て いません。', 'reading' => 'Tanaka-san wa mada kite imasen.', 'id' => 'Pak/Bu Tanaka belum datang.', 'en' => 'Mr./Ms. Tanaka has not come yet.'],
                        ['ja' => 'デパートは まだ 開《あ》いて いません。', 'reading' => 'Depaato wa mada aite imasen.', 'id' => 'Departemen store belum buka.', 'en' => 'The department store is not open yet.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Bahan presentasi',
                        'title_en' => 'The presentation material',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'ワヒュさん、発表《はっぴょう》の 資料《しりょう》は もう 作《つく》りましたか。', 'reading' => 'Wahyu-san, happyou no shiryou wa mou tsukurimashita ka.', 'id' => 'Wahyu, bahan presentasinya sudah dibuat?', 'en' => 'Wahyu, have you made the presentation material yet?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、まだ 作《つく》って いません。', 'reading' => 'Iie, mada tsukutte imasen.', 'id' => 'Belum, belum saya buat.', 'en' => 'No, I have not made it yet.'],
                            ['speaker' => 'たなか', 'ja' => 'いつまでに 作《つく》りますか。', 'reading' => 'Itsu made ni tsukurimasu ka.', 'id' => 'Kapan batas pembuatannya?', 'en' => 'By when will you make it?'],
                            ['speaker' => 'ワヒュ', 'ja' => '金曜日《きんようび》までに 作《つく》る つもりです。', 'reading' => 'Kinyoubi made ni tsukuru tsumori desu.', 'id' => 'Saya bermaksud menyelesaikannya sebelum Jumat.', 'en' => 'I intend to make it by Friday.'],
                        ],
                    ],
                ],
            ],

            // 7 ------------------------------------------------------------
            [
                'title_id' => 'Bentuk ます sebagai kata benda (帰ります → 帰り)',
                'title_en' => 'The ます stem as a noun (帰ります → 帰り)',
                'pattern' => 'Ｖ(ます形《けい》の 語幹《ごかん》) → Ｎ',
                'payload' => [
                    'explanation_id' => 'Pada sebagian kata kerja, bagian depan bentuk ます (tanpa ます) bisa dipakai sebagai kata benda: 帰《かえ》ります → 帰《かえ》り ("saat/perjalanan pulang"). Kata benda ini diberi partikel dan dimodifikasi seperti kata benda biasa. Tidak semua kata kerja bisa diperlakukan begini, jadi anggap setiap contoh sebagai kosakata baru.',
                    'explanation_en' => 'For some verbs, the front part of the ます form (without ます) works as a noun: 帰《かえ》ります → 帰《かえ》り ("the way home, return"). Such nouns take particles and are modified like any other noun. Not every verb can be treated this way, so learn each one as new vocabulary.',
                    'notes_id' => [
                        'Contoh lain: 休《やす》みます → 休《やす》み (libur), 遊《あそ》びます → 遊《あそ》び (permainan), 答《こた》えます → 答《こた》え (jawaban), 申《もう》し込《こ》みます → 申《もう》し込《こ》み (pendaftaran), 終《お》わります → 終《お》わり (akhir).',
                        'Arti kata benda kadang bergeser sedikit dari kata kerjanya: 楽《たの》しみます → 楽《たの》しみ berarti "hal yang dinantikan".',
                    ],
                    'notes_en' => [
                        'More examples: 休《やす》みます → 休《やす》み (a day off), 遊《あそ》びます → 遊《あそ》び (play), 答《こた》えます → 答《こた》え (an answer), 申《もう》し込《こ》みます → 申《もう》し込《こ》み (an application), 終《お》わります → 終《お》わり (the end).',
                        'The meaning of the noun can drift a little from the verb: 楽《たの》しみます → 楽《たの》しみ means "something to look forward to".',
                    ],
                    'examples' => [
                        ['ja' => '帰《かえ》りに スーパーで 牛乳《ぎゅうにゅう》を 買《か》いました。', 'reading' => 'Kaeri ni suupaa de gyuunyuu o kaimashita.', 'id' => 'Dalam perjalanan pulang saya membeli susu di supermarket.', 'en' => 'On the way home I bought milk at the supermarket.'],
                        ['ja' => '帰《かえ》りの 電車《でんしゃ》は 込《こ》んで いました。', 'reading' => 'Kaeri no densha wa konde imashita.', 'id' => 'Kereta pulang tadi penuh.', 'en' => 'The train home was crowded.'],
                        ['ja' => '今度《こんど》の 休《やす》みは どこへ 行《い》きますか。', 'reading' => 'Kondo no yasumi wa doko e ikimasu ka.', 'id' => 'Libur kali ini mau ke mana?', 'en' => 'Where are you going on your next day off?'],
                        ['ja' => '来月《らいげつ》の 旅行《りょこう》が 楽《たの》しみです。', 'reading' => 'Raigetsu no ryokou ga tanoshimi desu.', 'id' => 'Saya menantikan perjalanan bulan depan.', 'en' => 'I am looking forward to next month trip.'],
                        ['ja' => '試験《しけん》の 申《もう》し込《こ》みは 今日《きょう》までです。', 'reading' => 'Shiken no moushikomi wa kyou made desu.', 'id' => 'Pendaftaran ujian sampai hari ini.', 'en' => 'The exam application closes today.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Dalam perjalanan pulang',
                        'title_en' => 'On the way home',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'ワヒュさん、帰《かえ》りに 一緒《いっしょ》に ごはんを 食《た》べませんか。', 'reading' => 'Wahyu-san, kaeri ni issho ni gohan o tabemasen ka.', 'id' => 'Wahyu, dalam perjalanan pulang mau makan bersama?', 'en' => 'Wahyu, shall we eat together on the way home?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいですね。今日《きょう》の 帰《かえ》りが 楽《たの》しみです。', 'reading' => 'Ii desu ne. Kyou no kaeri ga tanoshimi desu.', 'id' => 'Boleh. Saya menantikan perjalanan pulang hari ini.', 'en' => 'Sounds good. I am looking forward to going home today.'],
                            ['speaker' => 'サリ', 'ja' => '何時《なんじ》に 会社《かいしゃ》を 出《で》ますか。', 'reading' => 'Nanji ni kaisha o demasu ka.', 'id' => 'Jam berapa keluar dari kantor?', 'en' => 'What time do you leave the office?'],
                            ['speaker' => 'ワヒュ', 'ja' => '六時《ろくじ》ごろに 出《で》る 予定《よてい》です。', 'reading' => 'Rokuji goro ni deru yotei desu.', 'id' => 'Rencananya sekitar jam enam.', 'en' => 'I plan to leave around six.'],
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
            ['slug' => 'n4-pelajaran-6'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 6',
                'name_en' => 'N4 Lesson 6 Vocabulary',
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
            ['続けます', 'つづけます', 'tsuzukemasu', 'melanjutkan', 'to continue'],
            ['見つけます', 'みつけます', 'mitsukemasu', 'menemukan', 'to find'],
            ['休みを取ります', 'やすみをとります', 'yasumi o torimasu', 'mengambil [cuti]', 'to take [time off]'],
            ['試験を受けます', 'しけんをうけます', 'shiken o ukemasu', 'mengikuti [ujian]', 'to take [an exam]'],
            ['申し込みます', 'もうしこみます', 'moushikomimasu', 'mendaftar', 'to apply, to sign up'],
            ['休憩します', 'きゅうけいします', 'kyuukeishimasu', 'beristirahat', 'to take a break'],
            ['連休', 'れんきゅう', 'renkyuu', 'liburan berturutan', 'consecutive holidays'],
            ['作文', 'さくぶん', 'sakubun', 'karangan', 'composition, essay'],
            ['発表', 'はっぴょう', 'happyou', 'presentasi (〜します: mempresentasikan)', 'presentation (〜します: to present)'],
            ['展覧会', 'てんらんかい', 'tenrankai', 'pameran', 'exhibition'],
            ['結婚式', 'けっこんしき', 'kekkonshiki', 'upacara pernikahan', 'wedding ceremony'],
            ['お葬式', 'おそうしき', 'osoushiki', 'upacara kematian', 'funeral'],
            ['式', 'しき', 'shiki', 'upacara', 'ceremony'],
            ['本社', 'ほんしゃ', 'honsha', 'kantor pusat', 'head office'],
            ['支店', 'してん', 'shiten', 'kantor cabang', 'branch office'],
            ['教会', 'きょうかい', 'kyoukai', 'gereja', 'church'],
            ['大学院', 'だいがくいん', 'daigakuin', 'Program S2, S3 Universitas', 'graduate school'],
            ['動物園', 'どうぶつえん', 'doubutsuen', 'kebun binatang', 'zoo'],
            ['温泉', 'おんせん', 'onsen', 'tempat pemandian air panas', 'hot spring'],
            ['帰り', 'かえり', 'kaeri', 'pulang', 'the way back, return'],
            ['お子さん', 'おこさん', 'okosan', 'anak orang lain', 'someone else child'],
            ['〜号', '〜ごう', 'gou', 'nomor ~ (nomor yang dipakai untuk kereta, angin topan, dan lain-lain)', 'number ~ (used for trains, typhoons, etc.)'],
            ['〜の方', '〜のほう', 'no hou', 'sebelah ~', 'the ~ side, direction of ~'],
            ['ずっと', 'ずっと', 'zutto', 'seterusnya, terus-menerus', 'all the time, continuously'],

            // 会話 (percakapan)
            ['残ります', 'のこります', 'nokorimasu', 'tinggal', 'to remain, to stay behind'],
            ['入学試験', 'にゅうがくしけん', 'nyuugaku shiken', 'ujian masuk', 'entrance exam'],
            ['月に', 'つきに', 'tsuki ni', 'dalam sebulan', 'per month'],

            // 読み物 (bacaan)
            ['村', 'むら', 'mura', 'desa, kampung', 'village'],
            ['卒業します', 'そつぎょうします', 'sotsugyoushimasu', 'lulus, tamat', 'to graduate'],
            ['映画館', 'えいがかん', 'eigakan', 'gedung bioskop', 'cinema'],
            ['嫌[な]', 'いや[な]', 'iya', 'tidak senang, tidak suka', 'unpleasant, disagreeable'],
            ['空', 'そら', 'sora', 'langit', 'sky'],
            ['閉じます', 'とじます', 'tojimasu', 'tutup', 'to close'],
            ['都会', 'とかい', 'tokai', 'kota besar', 'big city'],
            ['子どもたち', 'こどもたち', 'kodomotachi', 'anak-anak', 'children'],
            ['自由に', 'じゆうに', 'jiyuu ni', 'dengan bebas', 'freely'],
        ];
    }
}
