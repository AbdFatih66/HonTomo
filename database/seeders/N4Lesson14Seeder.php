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

class N4Lesson14Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 14 ("Maaf, Terlambat"). Level N4 berdiri di samping
     * N5 dan dipilih lewat selektor N5 / N4 di Tata Bahasa, Kosakata dan
     * Referensi Tata Bahasa.
     *
     * Isi:
     *   - Level N4 (dibuat hanya kalau belum ada) dan Unit order 14
     *   - Lesson Bunpou (order 0, category grammar) dengan 5 kartu:
     *       1. Vて／Vなくて／Aくて／Naで、〜  (sebab → perasaan, keadaan)
     *       2. Nで (sebab berupa kejadian atau peristiwa)
     *       3. 〜ので、〜
     *       4. 途中で
     *       5. Batasan: ungkapan keinginan di belakang → から／ので
     *   - Kategori kosakata "n4-pelajaran-14" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus. Penjelasan, contoh
     * kalimat dan dialog ditulis baru untuk aplikasi ini.
     *
     * Referensi Tata Bahasa (halaman lampiran): bagian N4 "Ungkapan Sebab"
     * dan "Perasaan" di resources/js/pages/lampiran/index.vue.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson14Seeder
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
            ['level_id' => $level->id, 'order' => 14],
            [
                'title_id' => 'Pelajaran 14: Maaf, Terlambat',
                'title_en' => 'Lesson 14: Sorry I Am Late',
                'description_id' => 'Menyatakan sebab dengan 〜て, Ｎで dan 〜ので, serta 途中で.',
                'description_en' => 'Giving reasons with 〜て, Ｎで and 〜ので, plus 途中で.',
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
                'title_id' => 'Vて／Vなくて／Aくて／Naで、〜 (sebab → perasaan atau keadaan)',
                'title_en' => 'Vて／Vなくて／Aくて／Naで、〜 (reason → feeling or situation)',
                'pattern' => 'Ｖて／Ｖなくて／Ａくて／Ｎaで、〜',
                'payload' => [
                    'explanation_id' => 'Bentuk-て tidak hanya merangkai kegiatan. Ia juga bisa menyatakan sebab: bagian depan adalah penyebab, bagian belakang adalah akibat yang dialami pembicara. Cara membentuknya: kata kerja bentuk-て; kata kerja negatif 〜ない → 〜なくて; kata sifat い 〜い → 〜くて; kata sifat な dan kata benda (だ) → 〜で. Bagian belakang biasanya berisi (1) perasaan, seperti びっくりします, 安心《あんしん》します, 寂《さび》しい, うれしい, 残念《ざんねん》[な], atau (2) hal yang tidak terkendali oleh kehendak: bentuk potensial (行《い》けません), わかりません, atau kejadian (遅《おく》れて しまいました).',
                    'explanation_en' => 'The て-form does more than link actions. It can also give a reason: the front part is the cause and the back part is the result the speaker experiences. Formation: verb て-form; negative 〜ない → 〜なくて; い-adjective 〜い → 〜くて; な-adjective and noun (だ) → 〜で. The back part usually holds (1) a feeling, such as びっくりします, 安心《あんしん》します, 寂《さび》しい, うれしい, 残念《ざんねん》[な], or (2) something outside the speaker will: a potential form (行《い》けません), わかりません, or an event (遅《おく》れて しまいました).',
                    'notes_id' => [
                        'Bagian belakang tidak boleh berisi ungkapan kehendak seperti ajakan, permintaan, perintah atau niat. Untuk itu pakai 〜から (lihat kartu 5).',
                        'Urutan waktu terpisah dari sebab: 〜て sebab selalu terjadi lebih dulu daripada akibatnya.',
                    ],
                    'notes_en' => [
                        'The back part cannot contain an expression of will such as an invitation, request, command or intention. Use 〜から for those (see card 5).',
                        'The reason always happens before the result it causes.',
                    ],
                    'examples' => [
                        ['ja' => '友達《ともだち》に 会《あ》えなくて、寂《さび》しいです。', 'reading' => 'Tomodachi ni aenakute, sabishii desu.', 'id' => 'Saya kesepian karena tidak bisa bertemu teman.', 'en' => 'I feel lonely because I cannot meet my friends.'],
                        ['ja' => 'テストの 点《てん》を 見《み》て、がっかりしました。', 'reading' => 'Tesuto no ten o mite, gakkari shimashita.', 'id' => 'Saya kecewa setelah melihat nilai tes.', 'en' => 'I was disappointed to see my test score.'],
                        ['ja' => '説明《せつめい》が 複雑《ふくざつ》で、使《つか》い方《かた》が わかりません。', 'reading' => 'Setsumei ga fukuzatsu de, tsukaikata ga wakarimasen.', 'id' => 'Penjelasannya rumit, jadi saya tidak paham cara memakainya.', 'en' => 'The explanation is complicated, so I do not understand how to use it.'],
                        ['ja' => 'あしたは 仕事《しごと》が あって、パーティーに 行《い》けません。', 'reading' => 'Ashita wa shigoto ga atte, paatii ni ikemasen.', 'id' => 'Besok saya ada pekerjaan, jadi tidak bisa pergi ke pesta.', 'en' => 'I have work tomorrow, so I cannot go to the party.'],
                        ['ja' => '電車《でんしゃ》が 止《と》まって、会社《かいしゃ》に 遅《おく》れて しまいました。', 'reading' => 'Densha ga tomatte, kaisha ni okurete shimaimashita.', 'id' => 'Kereta berhenti, sehingga saya terlambat ke kantor.', 'en' => 'The train stopped, so I was late for work.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Kabar hasil ujian',
                        'title_en' => 'News about the exam',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'ワヒュさん、試験《しけん》は どうでしたか。', 'reading' => 'Wahyu-san, shiken wa dou deshita ka.', 'id' => 'Wahyu, bagaimana ujiannya?', 'en' => 'Wahyu, how was the exam?'],
                            ['speaker' => 'ワヒュ', 'ja' => '合格《ごうかく》して、安心《あんしん》しました。', 'reading' => 'Goukaku shite, anshin shimashita.', 'id' => 'Saya lulus, jadi lega.', 'en' => 'I passed, so I am relieved.'],
                            ['speaker' => 'たなか', 'ja' => 'それは よかったですね。', 'reading' => 'Sore wa yokatta desu ne.', 'id' => 'Wah, syukurlah.', 'en' => 'That is great.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'うれしくて、すぐ 家族《かぞく》に 電話《でんわ》しました。', 'reading' => 'Ureshikute, sugu kazoku ni denwa shimashita.', 'id' => 'Karena senang, saya langsung menelepon keluarga.', 'en' => 'I was so happy that I called my family right away.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => 'Ｎで、〜 (sebab berupa kejadian)',
                'title_en' => 'Ｎで、〜 (a reason that is an event)',
                'pattern' => 'Ｎで、〜',
                'payload' => [
                    'explanation_id' => 'Kata benda yang menyatakan fenomena alam, kecelakaan atau peristiwa dapat langsung diikuti で untuk menyatakan sebab. Kata benda yang cocok antara lain 事故《じこ》, 地震《じしん》, 台風《たいふう》, 火事《かじ》, 雷《かみなり》, 津波《つなみ》, dan 病気《びょうき》／風邪《かぜ》. Bagian belakang berisi akibat yang terjadi.',
                    'explanation_en' => 'A noun for a natural phenomenon, accident or event can be followed directly by で to give a reason. Typical nouns: 事故《じこ》, 地震《じしん》, 台風《たいふう》, 火事《かじ》, 雷《かみなり》, 津波《つなみ》, and 病気《びょうき》／風邪《かぜ》. The back part states the result.',
                    'notes_id' => [
                        'Hanya untuk kata benda berupa kejadian. Kata benda biasa seperti 本《ほん》 atau 机《つくえ》 tidak dipakai begini.',
                        'で di sini berbeda dari で yang berarti tempat atau alat, tetapi bentuknya sama. Perhatikan artinya dari konteks.',
                    ],
                    'notes_en' => [
                        'Only for nouns that are events. Ordinary nouns like 本《ほん》 or 机《つくえ》 are not used this way.',
                        'This で differs from the で for place or means, but the form is the same. Read the meaning from context.',
                    ],
                    'examples' => [
                        ['ja' => '台風《たいふう》で、電車《でんしゃ》が 止《と》まりました。', 'reading' => 'Taifuu de, densha ga tomarimashita.', 'id' => 'Kereta berhenti karena angin topan.', 'en' => 'The train stopped because of the typhoon.'],
                        ['ja' => '事故《じこ》で、道《みち》が こんで います。', 'reading' => 'Jiko de, michi ga konde imasu.', 'id' => 'Jalan macet karena kecelakaan.', 'en' => 'The road is crowded because of an accident.'],
                        ['ja' => '火事《かじ》で、古《ふる》い 家《いえ》が 燃《も》えました。', 'reading' => 'Kaji de, furui ie ga moemashita.', 'id' => 'Rumah tua itu terbakar karena kebakaran.', 'en' => 'The old house burned down in a fire.'],
                        ['ja' => '風邪《かぜ》で、きのうは 学校《がっこう》を 休《やす》みました。', 'reading' => 'Kaze de, kinou wa gakkou o yasumimashita.', 'id' => 'Kemarin saya tidak masuk sekolah karena flu.', 'en' => 'I stayed home from school yesterday because of a cold.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di stasiun',
                        'title_en' => 'At the station',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'すみません。電車《でんしゃ》は まだ 動《うご》きませんか。', 'reading' => 'Sumimasen. Densha wa mada ugokimasen ka.', 'id' => 'Permisi. Apakah kereta belum jalan?', 'en' => 'Excuse me. Are the trains still not running?'],
                            ['speaker' => '駅員', 'ja' => '事故《じこ》で、止《と》まって います。', 'reading' => 'Jiko de, tomatte imasu.', 'id' => 'Berhenti karena ada kecelakaan.', 'en' => 'They are stopped because of an accident.'],
                            ['speaker' => 'サリ', 'ja' => 'いつ 動《うご》きますか。', 'reading' => 'Itsu ugokimasu ka.', 'id' => 'Kapan akan jalan lagi?', 'en' => 'When will they run?'],
                            ['speaker' => '駅員', 'ja' => '三十分《さんじゅっぷん》ぐらい かかります。', 'reading' => 'Sanjuppun gurai kakarimasu.', 'id' => 'Sekitar tiga puluh menit.', 'en' => 'It will take about thirty minutes.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => '〜ので、〜 (sebab yang halus)',
                'title_en' => '〜ので、〜 (a soft reason)',
                'pattern' => 'ふつうけい ＋ ので、〜',
                'payload' => [
                    'explanation_id' => '〜ので menyatakan sebab seperti 〜から (N5), tetapi nadanya lebih lembut dan objektif: sebab disampaikan sebagai fakta yang menuntun pada akibat, bukan sebagai alasan yang dikemukakan pembicara. Karena itu cocok untuk meminta izin, minta maaf, menolak, atau meminta bantuan dengan sopan. Cara membentuknya: bentuk biasa + ので. Kata sifat な dan kata benda memakai な sebagai ganti だ: 静《しず》かなので, 雨《あめ》なので.',
                    'explanation_en' => '〜ので gives a reason like 〜から (N5), but the tone is softer and more objective: the reason is presented as a fact that leads to the result, not as an excuse the speaker pushes forward. That makes it a good fit for asking permission, apologising, declining, or politely asking for help. Formation: plain form + ので. な-adjectives and nouns use な instead of だ: 静《しず》かなので, 雨《あめ》なので.',
                    'notes_id' => [
                        'Verba dan kata sifat い: bentuk biasa apa adanya (行《い》くので, 高《たか》いので, 行《い》かないので).',
                        'Dalam kalimat sopan, ので juga bisa mengikuti bentuk ます (行《い》きますので), tetapi bentuk biasa adalah dasarnya.',
                        'Berbeda dengan 〜て (kartu 1), bagian belakang ので boleh berisi permintaan atau izin: 教《おし》えて いただけませんか.',
                    ],
                    'notes_en' => [
                        'Verbs and い-adjectives: plain form as it is (行《い》くので, 高《たか》いので, 行《い》かないので).',
                        'In polite speech ので can also follow the ます form (行《い》きますので), but the plain form is the base.',
                        'Unlike 〜て (card 1), the back part of ので may hold a request or permission: 教《おし》えて いただけませんか.',
                    ],
                    'examples' => [
                        ['ja' => '熱《ねつ》が あるので、きょうは 早《はや》く 帰《かえ》っても いいですか。', 'reading' => 'Netsu ga aru node, kyou wa hayaku kaettemo ii desu ka.', 'id' => 'Karena saya demam, bolehkah saya pulang lebih awal hari ini?', 'en' => 'I have a fever, so may I go home early today?'],
                        ['ja' => 'この 仕事《しごと》は 初《はじ》めてなので、教《おし》えて いただけませんか。', 'reading' => 'Kono shigoto wa hajimete na node, oshiete itadakemasen ka.', 'id' => 'Karena ini pekerjaan pertama saya, bisakah Anda mengajari saya?', 'en' => 'This is my first time doing this work, so could you teach me?'],
                        ['ja' => '駅《えき》から 遠《とお》いので、バスで 行《い》きます。', 'reading' => 'Eki kara tooi node, basu de ikimasu.', 'id' => 'Karena jauh dari stasiun, saya naik bus.', 'en' => 'It is far from the station, so I will go by bus.'],
                        ['ja' => '日本語《にほんご》が 上手《じょうず》じゃ ないので、ゆっくり 話《はな》して ください。', 'reading' => 'Nihongo ga jouzu ja nai node, yukkuri hanashite kudasai.', 'id' => 'Bahasa Jepang saya belum lancar, jadi tolong bicara pelan-pelan.', 'en' => 'My Japanese is not good, so please speak slowly.'],
                        ['ja' => 'あした 試験《しけん》が あるので、きょうは 遊《あそ》びに 行《い》けません。', 'reading' => 'Ashita shiken ga aru node, kyou wa asobi ni ikemasen.', 'id' => 'Besok ada ujian, jadi hari ini saya tidak bisa pergi bermain.', 'en' => 'I have an exam tomorrow, so I cannot go out to play today.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Pamit lebih awal',
                        'title_en' => 'Leaving early',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません。ちょっと 頭《あたま》が 痛《いた》いので、早《はや》く 帰《かえ》っても いいですか。', 'reading' => 'Sumimasen. Chotto atama ga itai node, hayaku kaettemo ii desu ka.', 'id' => 'Maaf. Kepala saya agak sakit, bolehkah saya pulang lebih awal?', 'en' => 'Excuse me. My head hurts a little, so may I go home early?'],
                            ['speaker' => 'たなか', 'ja' => 'ええ、だいじょうぶですか。', 'reading' => 'Ee, daijoubu desu ka.', 'id' => 'Ya, apakah Anda baik-baik saja?', 'en' => 'Yes. Are you all right?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい、家《いえ》で 休《やす》みます。', 'reading' => 'Hai, ie de yasumimasu.', 'id' => 'Ya, saya akan istirahat di rumah.', 'en' => 'Yes, I will rest at home.'],
                            ['speaker' => 'たなか', 'ja' => 'お大事《だいじ》に。', 'reading' => 'Odaiji ni.', 'id' => 'Semoga lekas sembuh.', 'en' => 'Take care.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => '途中で (di tengah jalan／di tengah kegiatan)',
                'title_en' => '途中で (on the way／in the middle of)',
                'pattern' => 'Ｖ(kamus)／Ｎの ＋ 途中《とちゅう》で',
                'payload' => [
                    'explanation_id' => '途中《とちゅう》で menyatakan titik di tengah perjalanan atau di tengah suatu kegiatan, sebelum sampai ke tujuan atau sebelum selesai. Dipakai bersama kata kerja bentuk-kamus atau kata benda + の: 来《く》る 途中《とちゅう》で (di tengah perjalanan datang), 映画《えいが》の 途中《とちゅう》で (di tengah film). Kalimat yang mengikutinya menyebut hal yang terjadi pada titik itu.',
                    'explanation_en' => '途中《とちゅう》で marks a point partway through a journey or an activity, before you arrive or before it is finished. It is used with the dictionary form of a verb or a noun + の: 来《く》る 途中《とちゅう》で (on the way here), 映画《えいが》の 途中《とちゅう》で (in the middle of a movie). The rest of the sentence says what happened at that point.',
                    'notes_id' => [
                        'Kata kerja di depannya selalu bentuk-kamus, bahkan ketika kejadiannya sudah lewat: 来《く》る 途中《とちゅう》で ... ました.',
                        'Kegiatan atau perjalanannya sendiri belum selesai ketika kejadian itu muncul.',
                    ],
                    'notes_en' => [
                        'The verb before it is always in dictionary form, even when the event is in the past: 来《く》る 途中《とちゅう》で ... ました.',
                        'The journey or activity itself was not finished when the event occurred.',
                    ],
                    'examples' => [
                        ['ja' => '会社《かいしゃ》へ 来《く》る 途中《とちゅう》で、友達《ともだち》に 会《あ》いました。', 'reading' => 'Kaisha e kuru tochuu de, tomodachi ni aimashita.', 'id' => 'Di tengah perjalanan ke kantor, saya bertemu teman.', 'en' => 'On my way to the office, I met a friend.'],
                        ['ja' => '映画《えいが》の 途中《とちゅう》で、寝《ね》て しまいました。', 'reading' => 'Eiga no tochuu de, nete shimaimashita.', 'id' => 'Di tengah film, saya malah tertidur.', 'en' => 'I fell asleep in the middle of the movie.'],
                        ['ja' => '買《か》い物《もの》に 行《い》く 途中《とちゅう》で、財布《さいふ》を 落《お》としました。', 'reading' => 'Kaimono ni iku tochuu de, saifu o otoshimashita.', 'id' => 'Di tengah perjalanan berbelanja, saya menjatuhkan dompet.', 'en' => 'On my way shopping, I dropped my wallet.'],
                        ['ja' => '話《はなし》の 途中《とちゅう》で、電話《でんわ》が かかって きました。', 'reading' => 'Hanashi no tochuu de, denwa ga kakatte kimashita.', 'id' => 'Di tengah pembicaraan, ada telepon masuk.', 'en' => 'In the middle of the conversation, a phone call came in.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Terlambat karena kecelakaan',
                        'title_en' => 'Late because of an accident',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'ワヒュさん、遅《おそ》かったですね。', 'reading' => 'Wahyu-san, osokatta desu ne.', 'id' => 'Wahyu, kamu terlambat, ya.', 'en' => 'Wahyu, you are late.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません。来《く》る 途中《とちゅう》で、トラックと 車《くるま》が ぶつかったんです。', 'reading' => 'Sumimasen. Kuru tochuu de, torakku to kuruma ga butsukatta n desu.', 'id' => 'Maaf. Di tengah perjalanan ke sini, sebuah truk dan mobil bertabrakan.', 'en' => 'Sorry. On my way here, a truck and a car collided.'],
                            ['speaker' => 'たなか', 'ja' => 'それは 大変《たいへん》でしたね。', 'reading' => 'Sore wa taihen deshita ne.', 'id' => 'Wah, itu repot sekali.', 'en' => 'That must have been hard.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、それで 道《みち》を 通《とお》れませんでした。', 'reading' => 'Ee, sore de michi o tooremasen deshita.', 'id' => 'Ya, jadi saya tidak bisa lewat jalan itu.', 'en' => 'Yes, so I could not pass along the road.'],
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => 'Ungkapan kehendak di belakang → pakai から',
                'title_en' => 'Will or requests in the back part → use から',
                'pattern' => '〜から、Ｖましょう／Ｖて ください',
                'payload' => [
                    'explanation_id' => 'Sebab dengan 〜て (kartu 1) hanya bisa diikuti hal yang tidak dikendalikan kehendak: perasaan, kemampuan, keadaan, kejadian. Kalau bagian belakang berisi kehendak pembicara — ajakan (〜ましょう／〜ませんか), permintaan (〜て ください), perintah, niat (〜たいです／〜つもりです) — pakai 〜から (atau 〜ので kalau ingin lebih halus). Sebab-nya diletakkan di depan dan ditutup から.',
                    'explanation_en' => 'A reason with 〜て (card 1) can only be followed by things outside the speaker will: feelings, ability, states, events. When the back part expresses the speaker will — an invitation (〜ましょう／〜ませんか), a request (〜て ください), a command, an intention (〜たいです／〜つもりです) — use 〜から (or 〜ので for a softer tone). The reason comes first and is closed by から.',
                    'notes_id' => [
                        'Salah: ×時間《じかん》が なくて、タクシーで 行《い》きましょう。 Benar: 時間《じかん》が ありませんから、タクシーで 行《い》きましょう。',
                        'Kalimat sopan ですから／ますから adalah bentuk yang paling biasa dipakai sebelum permintaan dan ajakan.',
                    ],
                    'notes_en' => [
                        'Wrong: ×時間《じかん》が なくて、タクシーで 行《い》きましょう。 Right: 時間《じかん》が ありませんから、タクシーで 行《い》きましょう。',
                        'The polite ですから／ますから is the most usual form before requests and invitations.',
                    ],
                    'examples' => [
                        ['ja' => '雨《あめ》が 降《ふ》って いますから、傘《かさ》を 持《も》って 行《い》って ください。', 'reading' => 'Ame ga futte imasu kara, kasa o motte itte kudasai.', 'id' => 'Hujan turun, jadi tolong bawa payung.', 'en' => 'It is raining, so please take an umbrella.'],
                        ['ja' => '時間《じかん》が ありませんから、タクシーで 行《い》きましょう。', 'reading' => 'Jikan ga arimasen kara, takushii de ikimashou.', 'id' => 'Tidak ada waktu, jadi mari naik taksi.', 'en' => 'There is no time, so let us take a taxi.'],
                        ['ja' => '疲《つか》れましたから、少《すこ》し 休《やす》みませんか。', 'reading' => 'Tsukaremashita kara, sukoshi yasumimasen ka.', 'id' => 'Sudah lelah, bagaimana kalau istirahat sebentar?', 'en' => 'I am tired, so shall we rest a little?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mengajak minum kopi',
                        'title_en' => 'Inviting someone for coffee',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'ワヒュさん、コーヒーでも 飲《の》みませんか。', 'reading' => 'Wahyu-san, koohii demo nomimasen ka.', 'id' => 'Wahyu, mau minum kopi?', 'en' => 'Wahyu, would you like some coffee?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません、これから 会議《かいぎ》ですから、あとで いっしょに 飲《の》みましょう。', 'reading' => 'Sumimasen, korekara kaigi desu kara, ato de issho ni nomimashou.', 'id' => 'Maaf, sebentar lagi saya rapat, jadi mari minum bersama nanti.', 'en' => 'Sorry, I have a meeting soon, so let us have it together later.'],
                            ['speaker' => 'サリ', 'ja' => 'いいですよ。じゃ、三時《さんじ》に ロビーで 会《あ》いましょう。', 'reading' => 'Ii desu yo. Ja, sanji ni robii de aimashou.', 'id' => 'Boleh. Kalau begitu, mari bertemu jam tiga di lobi.', 'en' => 'Sure. Then let us meet in the lobby at three.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、そう しましょう。', 'reading' => 'Ee, sou shimashou.', 'id' => 'Ya, ayo begitu.', 'en' => 'Yes, let us do that.'],
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
            ['slug' => 'n4-pelajaran-14'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 14',
                'name_en' => 'N4 Lesson 14 Vocabulary',
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
            ['答えます', 'こたえます', 'kotaemasu', 'menjawab [pada pertanyaan]', 'to answer [a question]'],
            ['倒れます', 'たおれます', 'taoremasu', '[bangunan] roboh', '[building] to fall down, to collapse'],
            ['通ります', 'とおります', 'toorimasu', 'lewat, melalui [jalan]', 'to pass, to go along [a road]'],
            ['死にます', 'しにます', 'shinimasu', 'meninggal dunia', 'to die'],
            ['びっくりします', 'びっくりします', 'bikkurishimasu', 'kaget', 'to be surprised'],
            ['がっかりします', 'がっかりします', 'gakkarishimasu', 'putus asa, kecewa', 'to be disappointed'],
            ['安心します', 'あんしんします', 'anshinshimasu', 'lega', 'to feel relieved'],
            ['けんかします', 'けんかします', 'kenkashimasu', 'bertengkar', 'to quarrel, to fight'],
            ['離婚します', 'りこんします', 'rikonshimasu', 'bercerai', 'to get divorced'],
            ['太ります', 'ふとります', 'futorimasu', 'menjadi gemuk', 'to gain weight'],
            ['やせます', 'やせます', 'yasemasu', 'menjadi kurus', 'to lose weight'],
            ['複雑[な]', 'ふくざつ[な]', 'fukuzatsu', 'rumit', 'complicated'],
            ['邪魔[な]', 'じゃま[な]', 'jama', 'gangguan, mengganggu', 'in the way, a nuisance'],
            ['硬い', 'かたい', 'katai', 'keras', 'hard'],
            ['軟らかい', 'やわらかい', 'yawarakai', 'lembut', 'soft'],
            ['汚い', 'きたない', 'kitanai', 'kotor', 'dirty'],
            ['うれしい', 'うれしい', 'ureshii', 'senang', 'happy, glad'],
            ['悲しい', 'かなしい', 'kanashii', 'sedih', 'sad'],
            ['恥ずかしい', 'はずかしい', 'hazukashii', 'malu', 'embarrassed, ashamed'],
            ['首相', 'しゅしょう', 'shushou', 'perdana menteri', 'prime minister'],
            ['地震', 'じしん', 'jishin', 'gempa bumi', 'earthquake'],
            ['津波', 'つなみ', 'tsunami', 'tsunami', 'tsunami'],
            ['台風', 'たいふう', 'taifuu', 'angin topan', 'typhoon'],
            ['雷', 'かみなり', 'kaminari', 'petir', 'thunder, lightning'],
            ['火事', 'かじ', 'kaji', 'kebakaran', 'fire'],
            ['事故', 'じこ', 'jiko', 'kecelakaan', 'accident'],
            ['ハイキング', 'ハイキング', 'haikingu', 'hiking', 'hiking'],
            ['[お]見合い', '[お]みあい', 'omiai', 'perjodohan', 'arranged meeting for marriage'],
            ['操作', 'そうさ', 'sousa', 'operasi (〜します: mengoperasi)', 'operation (〜します: to operate)'],
            ['会場', 'かいじょう', 'kaijou', 'tempat (acara)', 'venue'],
            ['〜代', '〜だい', 'dai', 'biaya untuk ~', 'fee for ~'],
            ['〜屋', '〜や', 'ya', 'penjual ~', '~ shop, seller of ~'],
            ['フロント', 'フロント', 'furonto', 'resepsionis', 'front desk'],
            ['〜号室', '〜ごうしつ', 'goushitsu', 'kamar nomor ~', 'room number ~'],
            ['タオル', 'タオル', 'taoru', 'handuk', 'towel'],
            ['せっけん', 'せっけん', 'sekken', 'sabun', 'soap'],
            ['大勢', 'おおぜい', 'oozei', 'banyak (orang)', 'a large number of people'],
            ['お疲れさまでした。', 'おつかれさまでした。', 'otsukaresama deshita', 'Terima kasih atas kerja samanya.', 'Thank you for your hard work.'],
            ['伺います', 'うかがいます', 'ukagaimasu', 'pergi (bentuk merendah dari いきます)', 'to go (humble form of いきます)'],

            // 会話 (percakapan)
            ['途中で', 'とちゅうで', 'tochuu de', 'di tengah', 'on the way, in the middle'],
            ['トラック', 'トラック', 'torakku', 'truk', 'truck'],
            ['ぶつかります', 'ぶつかります', 'butsukarimasu', 'menabrak', 'to collide, to hit'],

            // 読み物 (bacaan)
            ['大人', 'おとな', 'otona', 'orang dewasa', 'adult'],
            ['しかし', 'しかし', 'shikashi', 'tetapi', 'however, but'],
            ['また', 'また', 'mata', 'dan, serta', 'and, also'],
            ['洋服', 'ようふく', 'youfuku', 'baju (gaya Barat)', 'Western-style clothes'],
            ['西洋化します', 'せいようかします', 'seiyoukashimasu', 'kebarat-baratan', 'to become Westernised'],
            ['合います', 'あいます', 'aimasu', 'cocok', 'to suit, to match'],
            ['今では', 'いまでは', 'ima dewa', 'sekarang', 'nowadays'],
            ['成人式', 'せいじんしき', 'seijinshiki', 'upacara pendewasaan', 'coming-of-age ceremony'],
            ['伝統的[な]', 'でんとうてき[な]', 'dentouteki', 'secara tradisional', 'traditional'],
        ];
    }
}
