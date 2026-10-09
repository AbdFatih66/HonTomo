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

class N4Lesson3Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 3 ("Melakukan Dua Hal Sekaligus"). Berdiri di level N4
     * di samping Pelajaran 1 dan dipilih lewat selektor N5 / N4 di Tata Bahasa,
     * Kosakata dan Referensi Tata Bahasa.
     *
     * Isi:
     *   - Unit order 3 di level N4 ("Pelajaran 3")
     *   - Lesson Bunpou (order 0, category grammar) dengan 5 kartu:
     *       1. V₁(ます形)ながら V₂        4. それで
     *       2. Vて います (kebiasaan)      5. 〜とき + partikel
     *       3. Bentuk biasa し、〜し、〜
     *   - Kategori kosakata "n4-pelajaran-3" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus. Penjelasan, contoh
     * kalimat dan dialog ditulis baru untuk aplikasi ini. Nama diri tidak
     * dimasukkan ke daftar kosakata.
     *
     * Referensi Tata Bahasa (info menyewa rumah) tidak memakai database; datanya
     * ada di resources/js/pages/lampiran/index.vue.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson3Seeder
     *
     * Kata kunci pencarian SQL memakai ASCII (kategori.slug + romaji), bukan
     * huruf Jepang (lihat docs/AGENTS.md aturan 4).
     */
    public function run(): void
    {
        // Level N4 dibuat oleh N4Lesson1Seeder; jalankan dulu bila belum ada.
        if (! Level::where('code', 'N4')->exists()) {
            $this->call(N4Lesson1Seeder::class);
        }

        $level = Level::where('code', 'N4')->firstOrFail();

        $unit = Unit::updateOrCreate(
            ['level_id' => $level->id, 'order' => 3],
            [
                'title_id' => 'Pelajaran 3: Melakukan Dua Hal Sekaligus',
                'title_en' => 'Lesson 3: Doing Two Things at Once',
                'description_id' => '〜ながら, 〜ています (kebiasaan), 〜し、〜し, それで, dan 〜とき + partikel.',
                'description_en' => '〜ながら, 〜ています (habits), 〜し、〜し, それで, and 〜とき + particles.',
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
                'title_id' => 'V₁ ながら V₂ (dua kegiatan bersamaan)',
                'title_en' => 'V₁ ながら V₂ (two actions at the same time)',
                'pattern' => 'V₁(ます形)ながら V₂',
                'payload' => [
                    'explanation_id' => 'Dipakai ketika satu orang melakukan dua kegiatan pada saat yang sama: "sambil". Kegiatan yang menjadi inti kalimat ditaruh di belakang (V₂), sedangkan kegiatan penyerta ditaruh di depan (V₁). Cara membentuknya: buang ます dari kata kerja V₁, lalu tambahkan ながら (聞《き》きます → 聞《き》きながら). Pola ini juga cocok untuk dua kegiatan yang dilakukan terus-menerus dalam jangka waktu tertentu, misalnya 働《はたら》きながら 勉強《べんきょう》して います.',
                    'explanation_en' => 'Used when one person does two actions at the same time: "while". The action that is the main point of the sentence goes last (V₂); the accompanying action goes first (V₁). Form: drop ます from the verb V₁ and add ながら (聞《き》きます → 聞《き》きながら). It also fits two actions carried on continuously over a period, for example 働《はたら》きながら 勉強《べんきょう》して います.',
                    'notes_id' => [
                        'Pelakunya harus orang yang sama untuk V₁ dan V₂. Kalau pelakunya berbeda, pakai kalimat lain (misalnya 〜て、〜).',
                        'Kala dan bentuk sopan ditentukan oleh V₂ di akhir kalimat, bukan oleh V₁.',
                        'Kegiatan yang inti ada di belakang: 歩《ある》きながら 食《た》べます = makan (inti) sambil berjalan.',
                    ],
                    'notes_en' => [
                        'V₁ and V₂ must have the same subject. If the subjects differ, use another sentence pattern (for example 〜て、〜).',
                        'Tense and politeness are decided by V₂ at the end of the sentence, not by V₁.',
                        'The main action comes last: 歩《ある》きながら 食《た》べます = eat (main) while walking.',
                    ],
                    'examples' => [
                        ['ja' => '歌《うた》を 歌《うた》いながら 掃除《そうじ》します。', 'reading' => 'Uta o utainagara souji shimasu.', 'id' => 'Saya bersih-bersih sambil bernyanyi.', 'en' => 'I clean while singing.'],
                        ['ja' => 'コーヒーを 飲《の》みながら 新聞《しんぶん》を 読《よ》みます。', 'reading' => 'Koohii o nominagara shinbun o yomimasu.', 'id' => 'Saya membaca koran sambil minum kopi.', 'en' => 'I read the newspaper while drinking coffee.'],
                        ['ja' => 'メモしながら 先生《せんせい》の 話《はなし》を 聞《き》きます。', 'reading' => 'Memo shinagara sensei no hanashi o kikimasu.', 'id' => 'Saya mendengarkan penjelasan guru sambil mencatat.', 'en' => 'I listen to the teacher while taking notes.'],
                        ['ja' => '歩《ある》きながら スマホを 見《み》ないで ください。', 'reading' => 'Arukinagara sumaho o minaide kudasai.', 'id' => 'Tolong jangan melihat ponsel sambil berjalan.', 'en' => 'Please do not look at your phone while walking.'],
                        ['ja' => 'アルバイトを しながら 大学《だいがく》に 通《かよ》って います。', 'reading' => 'Arubaito o shinagara daigaku ni kayotte imasu.', 'id' => 'Saya kuliah sambil kerja paruh waktu.', 'en' => 'I go to university while working part-time.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Belajar sambil mendengarkan musik?',
                        'title_en' => 'Studying while listening to music?',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'ワヒュさんは いつも 音楽《おんがく》を 聞《き》きながら 勉強《べんきょう》しますか。', 'reading' => 'Wahyu-san wa itsumo ongaku o kikinagara benkyou shimasu ka.', 'id' => 'Wahyu, apakah kamu selalu belajar sambil mendengarkan musik?', 'en' => 'Wahyu, do you always study while listening to music?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、聞《き》きません。静《しず》かな 部屋《へや》で 勉強《べんきょう》します。', 'reading' => 'Iie, kikimasen. Shizuka na heya de benkyou shimasu.', 'id' => 'Tidak, saya tidak mendengarkan. Saya belajar di ruangan yang tenang.', 'en' => 'No, I do not. I study in a quiet room.'],
                            ['speaker' => 'たなか', 'ja' => 'そうですか。じゃ、料理《りょうり》を する ときは？', 'reading' => 'Sou desu ka. Ja, ryouri o suru toki wa?', 'id' => 'Begitu, ya. Lalu, kalau sedang memasak?', 'en' => 'I see. Then, what about when you cook?'],
                            ['speaker' => 'ワヒュ', 'ja' => '料理《りょうり》の ときは、ラジオを 聞《き》きながら 作《つく》ります。', 'reading' => 'Ryouri no toki wa, rajio o kikinagara tsukurimasu.', 'id' => 'Kalau memasak, saya memasak sambil mendengarkan radio.', 'en' => 'When cooking, I cook while listening to the radio.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => 'Vて います (kebiasaan)',
                'title_en' => 'Vて います (habitual action)',
                'pattern' => 'Vて います／Vて いました',
                'payload' => [
                    'explanation_id' => 'Selain untuk "sedang melakukan" dan "keadaan", Vて います juga menyatakan kegiatan yang dilakukan berulang-ulang sebagai kebiasaan: "(setiap …) saya …". Kalau kebiasaan itu sudah berlalu sebelum saat berbicara, bentuknya menjadi Vて いました. Keterangan waktu seperti 毎朝《まいあさ》, 毎晩《まいばん》, よく, 週《しゅう》に 二回《にかい》, atau 子《こ》どもの とき membantu pendengar membedakannya dari "sedang melakukan sekarang".',
                    'explanation_en' => 'Besides "in the middle of doing" and "a state", Vて います also expresses an action repeated as a habit: "(every …) I …". If the habit belongs to the past, before the moment of speaking, it becomes Vて いました. Time words such as 毎朝《まいあさ》, 毎晩《まいばん》, よく, 週《しゅう》に 二回《にかい》, or 子《こ》どもの とき help the listener tell it apart from "doing it right now".',
                    'notes_id' => [
                        'Kebiasaan juga bisa dinyatakan dengan bentuk ます biasa (毎朝 ジョギングを します). Vて います terasa lebih menekankan bahwa kebiasaan itu terus berlangsung sebagai rutinitas.',
                        'Tanpa keterangan waktu, 走《はし》って います bisa berarti "sedang berlari" atau "rutin berlari" — konteks yang menentukan.',
                    ],
                    'notes_en' => [
                        'A habit can also be stated with the plain ます form (毎朝 ジョギングを します). Vて います puts more weight on the habit continuing as a routine.',
                        'Without a time word, 走《はし》って います can mean "is running" or "runs regularly" — context decides.',
                    ],
                    'examples' => [
                        ['ja' => '毎朝《まいあさ》 六時《ろくじ》に 起《お》きて、散歩《さんぽ》を して います。', 'reading' => 'Maiasa rokuji ni okite, sanpo o shite imasu.', 'id' => 'Setiap pagi saya bangun jam enam lalu berjalan-jalan.', 'en' => 'Every morning I get up at six and go for a walk.'],
                        ['ja' => '毎晩《まいばん》 寝《ね》る まえに 日記《にっき》を 書《か》いて います。', 'reading' => 'Maiban neru mae ni nikki o kaite imasu.', 'id' => 'Setiap malam sebelum tidur saya menulis buku harian.', 'en' => 'Every night before bed I write in my diary.'],
                        ['ja' => '週《しゅう》に 二回《にかい》 ジムに 通《かよ》って います。', 'reading' => 'Shuu ni nikai jimu ni kayotte imasu.', 'id' => 'Saya pergi ke pusat kebugaran dua kali seminggu.', 'en' => 'I go to the gym twice a week.'],
                        ['ja' => '子《こ》どもの とき、毎日《まいにち》 友達《ともだち》と サッカーを して いました。', 'reading' => 'Kodomo no toki, mainichi tomodachi to sakkaa o shite imashita.', 'id' => 'Waktu kecil, setiap hari saya bermain sepak bola dengan teman.', 'en' => 'As a child, I played soccer with my friends every day.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Kegiatan di hari libur',
                        'title_en' => 'Weekend activities',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'ワヒュさんは 休《やす》みの 日《ひ》に よく 何《なに》を しますか。', 'reading' => 'Wahyu-san wa yasumi no hi ni yoku nani o shimasu ka.', 'id' => 'Wahyu, di hari libur biasanya kamu melakukan apa?', 'en' => 'Wahyu, what do you often do on your days off?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'たいてい 絵《え》を 描《か》いて います。', 'reading' => 'Taitei e o kaite imasu.', 'id' => 'Biasanya saya melukis.', 'en' => 'I usually paint.'],
                            ['speaker' => 'サリ', 'ja' => 'へえ、すごいですね。いつから 描《か》いて いるんですか。', 'reading' => 'Hee, sugoi desu ne. Itsu kara kaite iru n desu ka.', 'id' => 'Wah, hebat ya. Sejak kapan kamu melukis?', 'en' => 'Wow, that is great. Since when have you been painting?'],
                            ['speaker' => 'ワヒュ', 'ja' => '子《こ》どもの ときから 描《か》いて います。', 'reading' => 'Kodomo no toki kara kaite imasu.', 'id' => 'Sejak kecil.', 'en' => 'Since I was a child.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => 'Bentuk biasa し、〜し、〜 (menyebut beberapa hal)',
                'title_en' => 'Plain form し、〜し、〜 (listing several points)',
                'pattern' => 'ふつうけい ＋ し、ふつうけい ＋ し、〜',
                'payload' => [
                    'explanation_id' => 'し dipakai untuk menyebutkan dua hal atau lebih yang sejenis tentang topik yang sama, biasanya sama-sama kelebihan atau sama-sama kekurangan: "…, dan juga …". Cara membentuknya: bentuk biasa + し (kata benda dan kata sifat な memakai だ: 静《しず》かだし, 学生《がくせい》だし). Karena pembicara menumpuk beberapa hal, nuansanya juga bisa berupa alasan — bagian terakhir menjadi kesimpulan atau alasan yang tidak perlu disebut lagi. Untuk menambah satu hal lagi yang menguatkan perasaan pembicara, pakai それに.',
                    'explanation_en' => 'し is used to list two or more points of the same kind about the same topic, usually all advantages or all drawbacks: "…, and also …". Form: plain form + し (nouns and な-adjectives take だ: 静《しず》かだし, 学生《がくせい》だし). Because the speaker piles up several points, the nuance can also be a reason — the conclusion is left unsaid. To add one more point that strengthens the speaker\'s feeling, use それに.',
                    'notes_id' => [
                        'Dipakai untuk menjawab どうして: ……静《しず》かだし、買《か》い物《もの》も 便利《べんり》だし……。 Kesimpulan boleh dihilangkan kalau sudah jelas.',
                        'Kalau alasan terakhir ingin ditegaskan, bagian paling belakang boleh diakhiri から: 歌《うた》も 上手《じょうず》だし、ダンスも 上手《じょうず》ですから。',
                        'Biasanya ada も atau が yang menandai tiap hal yang disebut, supaya terasa bahwa itu salah satu dari beberapa hal.',
                    ],
                    'notes_en' => [
                        'Often used to answer どうして: ……静《しず》かだし、買《か》い物《もの》も 便利《べんり》だし……。 The conclusion can be dropped when it is obvious.',
                        'To stress the last reason, the final clause may end in から: 歌《うた》も 上手《じょうず》だし、ダンスも 上手《じょうず》ですから。',
                        'も or が usually marks each point listed, which makes it feel like "one of several".',
                    ],
                    'examples' => [
                        ['ja' => 'サリさんは 料理《りょうり》も 上手《じょうず》だし、歌《うた》も 上手《じょうず》だし、いい 人《ひと》です。', 'reading' => 'Sari-san wa ryouri mo jouzu dashi, uta mo jouzu dashi, ii hito desu.', 'id' => 'Sari pandai memasak, pandai bernyanyi juga, dia orang yang baik.', 'en' => 'Sari is good at cooking, and good at singing too. She is a nice person.'],
                        ['ja' => 'この アパートは 駅《えき》から 近《ちか》いし、家賃《やちん》も 安《やす》いし、いい アパートです。', 'reading' => 'Kono apaato wa eki kara chikai shi, yachin mo yasui shi, ii apaato desu.', 'id' => 'Apartemen ini dekat dari stasiun dan sewanya murah juga, jadi apartemen yang bagus.', 'en' => 'This apartment is close to the station and the rent is cheap too. It is a good apartment.'],
                        ['ja' => 'どうして この 町《まち》に 住《す》んで いるんですか。……静《しず》かだし、買《か》い物《もの》も 便利《べんり》だし……。', 'reading' => 'Doushite kono machi ni sunde iru n desu ka. ...Shizuka da shi, kaimono mo benri da shi......', 'id' => 'Kenapa tinggal di kota ini? ……Tenang, dan belanja juga mudah……', 'en' => 'Why do you live in this town? ...It is quiet, and shopping is convenient too...'],
                        ['ja' => 'きょうは 雨《あめ》も 降《ふ》って いるし、寒《さむ》いし、出《で》かけたく ないです。', 'reading' => 'Kyou wa ame mo futte iru shi, samui shi, dekaketaku nai desu.', 'id' => 'Hari ini hujan dan dingin juga, jadi saya tidak ingin keluar.', 'en' => 'It is raining and it is cold too, so I do not want to go out.'],
                        ['ja' => 'どうして あの 歌手《かしゅ》が 好《す》きなんですか。……歌《うた》も 上手《じょうず》だし、ダンスも 上手《じょうず》ですから。', 'reading' => 'Doushite ano kashu ga suki na n desu ka. ...Uta mo jouzu dashi, dansu mo jouzu desu kara.', 'id' => 'Kenapa suka penyanyi itu? ……Karena nyanyiannya bagus dan dansanya juga bagus.', 'en' => 'Why do you like that singer? ...Because the singing is good and the dancing is good too.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mencari tempat tinggal',
                        'title_en' => 'Looking for a place to live',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'この 部屋《へや》は どうですか。', 'reading' => 'Kono heya wa dou desu ka.', 'id' => 'Bagaimana dengan kamar ini?', 'en' => 'How about this room?'],
                            ['speaker' => 'ワヒュ', 'ja' => '景色《けしき》も いいし、台所《だいどころ》も 広《ひろ》いし、いい 部屋《へや》ですね。', 'reading' => 'Keshiki mo ii shi, daidokoro mo hiroi shi, ii heya desu ne.', 'id' => 'Pemandangannya bagus, dapurnya luas juga, kamar yang bagus ya.', 'en' => 'The view is nice and the kitchen is spacious too. It is a good room.'],
                            ['speaker' => 'サリ', 'ja' => 'でも、家賃《やちん》が 高《たか》いんです。', 'reading' => 'Demo, yachin ga takai n desu.', 'id' => 'Tapi sewanya mahal.', 'en' => 'But the rent is expensive.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'それに、駅《えき》からも ちょっと 遠《とお》いですね。もう 少《すこ》し 探《さが》しましょう。', 'reading' => 'Soreni, eki kara mo chotto tooi desu ne. Mou sukoshi sagashimashou.', 'id' => 'Lagi pula, agak jauh dari stasiun juga. Mari kita cari sedikit lagi.', 'en' => 'Besides, it is a bit far from the station too. Let us look a little more.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'それで (sebab → akibat)',
                'title_en' => 'それで (cause → result)',
                'pattern' => '〜。それで、〜',
                'payload' => [
                    'explanation_id' => 'それで menyambung kalimat yang berisi sebab dengan kalimat yang berisi akibat atau kesimpulan: "oleh karena itu", "makanya". Hal yang disebut sebelumnya dianggap sebagai alasan bagi hal yang disebut sesudahnya. それで juga dipakai oleh pendengar yang akhirnya paham alasan sesuatu setelah mendengar penjelasan: それで 〜んですね = "Oh, pantas saja…".',
                    'explanation_en' => 'それで joins a sentence that gives a cause to a sentence that gives a result or conclusion: "therefore", "that is why". What came before is treated as the reason for what follows. それで is also used by a listener who finally understands the reason after hearing an explanation: それで 〜んですね = "Ah, no wonder…".',
                    'notes_id' => [
                        'Kalimat setelah それで biasanya berupa kenyataan atau hasil, bukan ajakan, perintah, atau permintaan. Untuk itu pakai だから／ですから.',
                        'Bedakan: それに = menambah hal lain yang sejenis ("dan lagi"), それで = menyatakan akibat ("makanya").',
                    ],
                    'notes_en' => [
                        'What follows それで is normally a fact or a result, not an invitation, command, or request. For those, use だから／ですから.',
                        'Do not mix them up: それに = adds another point of the same kind ("moreover"), それで = states a result ("so").',
                    ],
                    'examples' => [
                        ['ja' => '来月《らいげつ》 日本《にほん》へ 行《い》きます。それで、いま 日本語《にほんご》を 勉強《べんきょう》して います。', 'reading' => 'Raigetsu Nihon e ikimasu. Sorede, ima nihongo o benkyou shite imasu.', 'id' => 'Bulan depan saya pergi ke Jepang. Makanya sekarang saya belajar bahasa Jepang.', 'en' => 'I am going to Japan next month. That is why I am studying Japanese now.'],
                        ['ja' => '朝《あさ》から 何《なに》も 食《た》べて いません。それで、おなかが すいて います。', 'reading' => 'Asa kara nani mo tabete imasen. Sorede, onaka ga suite imasu.', 'id' => 'Sejak pagi saya belum makan apa-apa. Makanya perut saya lapar.', 'en' => 'I have not eaten anything since morning. That is why I am hungry.'],
                        ['ja' => 'ボーナスを もらいました。それで、新《あたら》しい かばんを 買《か》いました。', 'reading' => 'Boonasu o moraimashita. Sorede, atarashii kaban o kaimashita.', 'id' => 'Saya menerima bonus. Makanya saya membeli tas baru.', 'en' => 'I received a bonus. So I bought a new bag.'],
                        ['ja' => 'この 店《みせ》の ケーキは 安《やす》くて おいしいです。……それで 人気《にんき》が あるんですね。', 'reading' => 'Kono mise no keeki wa yasukute oishii desu. ...Sorede ninki ga aru n desu ne.', 'id' => 'Kue di toko ini murah dan enak. ……Oh, makanya populer, ya.', 'en' => 'The cakes at this shop are cheap and tasty. ...Ah, that is why it is popular.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Pantas saja jadi pandai',
                        'title_en' => 'No wonder you got good',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'ワヒュさん、日本語《にほんご》が 上手《じょうず》に なりましたね。', 'reading' => 'Wahyu-san, nihongo ga jouzu ni narimashita ne.', 'id' => 'Wahyu, bahasa Jepangmu jadi pandai, ya.', 'en' => 'Wahyu, your Japanese has become good.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ありがとうございます。毎日《まいにち》 日本語《にほんご》の ドラマを 見《み》て いるんです。', 'reading' => 'Arigatou gozaimasu. Mainichi nihongo no dorama o mite iru n desu.', 'id' => 'Terima kasih. Setiap hari saya menonton drama berbahasa Jepang.', 'en' => 'Thank you. I watch a Japanese drama every day.'],
                            ['speaker' => 'たなか', 'ja' => 'それで 上手《じょうず》に なったんですね。', 'reading' => 'Sorede jouzu ni natta n desu ne.', 'id' => 'Oh, makanya jadi pandai, ya.', 'en' => 'Ah, that is why you got good.'],
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => '〜とき + partikel (は／に／も／や)',
                'title_en' => '〜とき + particles (は／に／も／や)',
                'pattern' => 'ふつうけい ＋ とき＋は／に／も／や',
                'payload' => [
                    'explanation_id' => 'とき ("ketika") adalah kata benda, sehingga bisa diikuti partikel. Yang paling sering: ときは (menjadikan keadaan itu topik atau kontras: "kalau sedang …"), ときに (menunjuk saat terjadinya), ときも ("juga ketika …"), dan 〜ときや〜とき (menyebut beberapa keadaan sekaligus: "ketika … atau ketika …"). Bagian sebelum とき memakai bentuk biasa; kata benda memakai の dan kata sifat な memakai な.',
                    'explanation_en' => 'とき ("when") is a noun, so it can be followed by particles. The most common: ときは (makes the situation a topic or contrast: "when it comes to …"), ときに (points to the moment something happens), ときも ("also when …"), and 〜ときや〜とき (names several situations: "when … or when …"). The part before とき uses the plain form; nouns take の and な-adjectives take な.',
                    'notes_id' => [
                        'Ingat aturan Pelajaran 23 N5: bentuk kamus + とき = kegiatan belum/sedang berlangsung, bentuk た + とき = sudah selesai.',
                        'ときは sering menandakan kontras: 勉強《べんきょう》する ときは 聞《き》きません (belajar → tidak; saat lain → mungkin ya).',
                    ],
                    'notes_en' => [
                        'Remember the N5 Lesson 23 rule: dictionary form + とき = the action has not happened yet or is ongoing, た-form + とき = it has finished.',
                        'ときは often signals contrast: 勉強《べんきょう》する ときは 聞《き》きません (when studying → no; at other times → maybe yes).',
                    ],
                    'examples' => [
                        ['ja' => '勉強《べんきょう》する ときは、テレビを 見《み》ません。', 'reading' => 'Benkyou suru toki wa, terebi o mimasen.', 'id' => 'Kalau sedang belajar, saya tidak menonton TV.', 'en' => 'When I study, I do not watch TV.'],
                        ['ja' => '眠《ねむ》い ときや 疲《つか》れた ときは、ガムを 噛《か》みます。', 'reading' => 'Nemui toki ya tsukareta toki wa, gamu o kamimasu.', 'id' => 'Kalau sedang mengantuk atau lelah, saya mengunyah permen karet.', 'en' => 'When I am sleepy or tired, I chew gum.'],
                        ['ja' => 'ひまな ときは、よく 絵《え》を 描《か》きます。', 'reading' => 'Hima na toki wa, yoku e o kakimasu.', 'id' => 'Kalau sedang senggang, saya sering melukis.', 'en' => 'When I have free time, I often paint.'],
                        ['ja' => '友達《ともだち》に 会《あ》った ときに、この 本《ほん》を 返《かえ》します。', 'reading' => 'Tomodachi ni atta toki ni, kono hon o kaeshimasu.', 'id' => 'Saat bertemu teman nanti, saya akan mengembalikan buku ini.', 'en' => 'When I meet my friend, I will return this book.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Kalau sedang lelah',
                        'title_en' => 'When you are tired',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'ワヒュさんは 疲《つか》れた ときは、何《なに》を しますか。', 'reading' => 'Wahyu-san wa tsukareta toki wa, nani o shimasu ka.', 'id' => 'Wahyu, kalau sedang lelah, kamu melakukan apa?', 'en' => 'Wahyu, what do you do when you are tired?'],
                            ['speaker' => 'ワヒュ', 'ja' => '好《す》きな 歌手《かしゅ》の 歌《うた》を 聞《き》きます。', 'reading' => 'Suki na kashu no uta o kikimasu.', 'id' => 'Saya mendengarkan lagu penyanyi favorit.', 'en' => 'I listen to songs by my favourite singer.'],
                            ['speaker' => 'サリ', 'ja' => '寝《ね》る ときも 聞《き》くんですか。', 'reading' => 'Neru toki mo kiku n desu ka.', 'id' => 'Kalau mau tidur juga mendengarkan?', 'en' => 'Do you listen when you go to bed too?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、寝《ね》る ときは 聞《き》きません。', 'reading' => 'Iie, neru toki wa kikimasen.', 'id' => 'Tidak, kalau mau tidur saya tidak mendengarkan.', 'en' => 'No, I do not when I go to bed.'],
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
            ['slug' => 'n4-pelajaran-3'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 3',
                'name_en' => 'N4 Lesson 3 Vocabulary',
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
            ['売れます', 'うれます', 'uremasu', 'terjual [roti]', 'to be sold [bread]'],
            ['踊ります', 'おどります', 'odorimasu', 'menari', 'to dance'],
            ['かみます', 'かみます', 'kamimasu', 'mengunyah, menggigit', 'to chew, to bite'],
            ['選びます', 'えらびます', 'erabimasu', 'memilih', 'to choose'],
            ['通います', 'かよいます', 'kayoimasu', 'pergi-pulang [ke universitas]', 'to commute, to attend [university]'],
            ['メモします', 'メモします', 'memo shimasu', 'mencatat', 'to take notes'],
            ['まじめ[な]', 'まじめ', 'majime', 'serius, rajin', 'serious, diligent'],
            ['熱心[な]', 'ねっしん', 'nesshin', 'tekun, antusias', 'enthusiastic, earnest'],
            ['偉い', 'えらい', 'erai', 'hebat', 'great, admirable'],
            ['ちょうどいい', 'ちょうどいい', 'choudo ii', 'pas, cocok', 'just right, perfect'],
            ['景色', 'けしき', 'keshiki', 'pemandangan', 'scenery, view'],
            ['美容院', 'びよういん', 'biyouin', 'salon kecantikan', 'beauty salon'],
            ['台所', 'だいどころ', 'daidokoro', 'dapur', 'kitchen'],
            ['経験', 'けいけん', 'keiken', 'pengalaman', 'experience'],
            ['力', 'ちから', 'chikara', 'tenaga, kekuatan', 'power, strength'],
            ['人気', 'にんき', 'ninki', 'populer, disenangi', 'popularity'],
            ['形', 'かたち', 'katachi', 'bentuk', 'shape, form'],
            ['色', 'いろ', 'iro', 'warna', 'color'],
            ['味', 'あじ', 'aji', 'rasa', 'taste, flavor'],
            ['ガム', 'ガム', 'gamu', 'permen karet', 'chewing gum'],
            ['品物', 'しなもの', 'shinamono', 'barang', 'goods, item'],
            ['値段', 'ねだん', 'nedan', 'harga', 'price'],
            ['給料', 'きゅうりょう', 'kyuuryou', 'gaji', 'salary'],
            ['ボーナス', 'ボーナス', 'boonasu', 'bonus', 'bonus'],
            ['ゲーム', 'ゲーム', 'geemu', 'permainan, game', 'game'],
            ['番組', 'ばんぐみ', 'bangumi', 'acara TV', 'TV program'],
            ['ドラマ', 'ドラマ', 'dorama', 'drama', 'drama'],
            ['歌手', 'かしゅ', 'kashu', 'penyanyi', 'singer'],
            ['小説', 'しょうせつ', 'shousetsu', 'novel', 'novel'],
            ['小説家', 'しょうせつか', 'shousetsuka', 'novelis', 'novelist'],
            ['〜家', '〜か', 'ka', 'pelaku bidang ~ (akhiran)', 'person engaged in ~ (suffix)'],
            ['〜機', '〜き', 'ki', 'mesin ~', '~ machine'],
            ['息子', 'むすこ', 'musuko', 'anak laki-laki (sendiri)', "son (one's own)"],
            ['息子さん', 'むすこさん', 'musuko san', 'putra (anak orang lain)', "son (someone else's)"],
            ['娘', 'むすめ', 'musume', 'anak perempuan (sendiri)', "daughter (one's own)"],
            ['娘さん', 'むすめさん', 'musume san', 'putri (anak orang lain)', "daughter (someone else's)"],
            ['自分', 'じぶん', 'jibun', 'diri sendiri', 'oneself'],
            ['将来', 'しょうらい', 'shourai', 'masa depan', 'future'],
            ['しばらく', 'しばらく', 'shibaraku', 'sebentar, sementara', 'for a while'],
            ['たいてい', 'たいてい', 'taitei', 'kebanyakan, biasanya', 'usually, mostly'],
            ['それに', 'それに', 'soreni', 'dan juga, lagi pula', 'besides, moreover'],
            ['それで', 'それで', 'sorede', 'dan, lalu; oleh karena itu', 'and so, therefore'],

            // 会話 (percakapan)
            ['お願いがあるんですが', 'おねがいがあるんですが', 'onegai ga aru n desu ga', 'Minta sesuatu [sebentar]', 'I have a favor to ask'],
            ['実は', 'じつは', 'jitsu wa', 'solanya, sebenarnya', 'actually, the fact is'],
            ['会話', 'かいわ', 'kaiwa', 'percakapan', 'conversation'],
            ['うーん', 'うーん', 'uun', 'mmm...', 'hmm...'],

            // 読み物 (bacaan)
            ['お知らせ', 'おしらせ', 'oshirase', 'pemberitahuan, pengumuman', 'notice, announcement'],
            ['参加します', 'さんかします', 'sankashimasu', 'mengikuti, ikut serta', 'to participate, to join'],
            ['日にち', 'ひにち', 'hinichi', 'tanggal', 'date, day'],
            ['土', 'ど', 'do', 'hari Sabtu', 'Saturday'],
            ['体育館', 'たいいくかん', 'taiikukan', 'gedung olahraga', 'gymnasium'],
            ['無料', 'むりょう', 'muryou', 'gratis, cuma-cuma', 'free of charge'],
            ['誘います', 'さそいます', 'sasoimasu', 'mengajak', 'to invite'],
            ['イベント', 'イベント', 'ibento', 'acara', 'event'],
        ];
    }
}
