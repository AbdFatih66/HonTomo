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

class N4Lesson17Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 17: "Menabung untuk Apa?" (tujuan, kegunaan,
     * bilangan + は／も, dan pelaku dengan 〜によって). Level N4 berdiri di
     * samping N5 dan dipilih lewat selektor N5 / N4 di Tata Bahasa, Kosakata dan
     * Referensi Tata Bahasa.
     *
     * Isi:
     *   - Unit order 17 di level N4
     *   - Lesson Bunpou (order 0, category grammar) dengan 6 kartu:
     *       1. V(kamus) ＋ ために           4. V(kamus)のに／Nに (kegunaan)
     *       2. Nの ＋ ために                5. Bilangan ＋ は／も
     *       3. ために dan ように            6. 〜によって (pelaku)
     *   - Kategori kosakata "n4-pelajaran-17" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus. Penjelasan, contoh
     * kalimat dan dialog ditulis baru untuk aplikasi ini. Nama diri dan judul
     * karya tidak dimasukkan, sama seperti di pelajaran lain.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson17Seeder
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
            ['level_id' => $level->id, 'order' => 17],
            [
                'title_id' => 'Pelajaran 17: Menabung untuk Apa?',
                'title_en' => 'Lesson 17: What Are You Saving For?',
                'description_id' => 'Menyatakan tujuan dengan 〜ために, kegunaan dengan 〜のに, jumlah dengan は／も, dan pelaku dengan 〜によって.',
                'description_en' => 'Stating purpose with 〜ために, use with 〜のに, amounts with は／も, and the agent with 〜によって.',
                'icon' => 'mdi-piggy-bank',
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
                'title_id' => 'V(bentuk kamus) ＋ ために (tujuan)',
                'title_en' => 'V (dictionary form) ＋ ために (purpose)',
                'pattern' => 'V(じしょけい) ＋ ために、〜',
                'payload' => [
                    'explanation_id' => 'ために menyatakan tujuan dari suatu tindakan: "untuk ...", "supaya bisa ...". Bagian depannya adalah kata kerja bentuk kamus yang menyatakan kehendak pembicara (持《も》つ, 行《い》く, 合格《ごうかく》する), dan bagian belakangnya adalah tindakan yang dilakukan demi tujuan itu. Pelaku di kedua bagian kalimat adalah orang yang sama. Karena tujuan itu belum tercapai, kata kerja di depan ために selalu bentuk kamus, bukan bentuk lampau.',
                    'explanation_en' => 'ために states the purpose of an action: "in order to ...". The front part is a dictionary-form verb expressing the speaker will (持《も》つ, 行《い》く, 合格《ごうかく》する), and the back part is the action taken for that purpose. The doer is the same person in both parts. Because the goal has not been reached yet, the verb before ために is always the dictionary form, never the past.',
                    'notes_id' => [
                        'Urutan kalimat bisa dibalik: 貯金《ちょきん》して います。自分《じぶん》の 店《みせ》を 持《も》つ ために。 Tetapi bentuk yang umum adalah tujuan lebih dulu.',
                        'Bentuk negatif jarang dipakai dengan ために. Untuk "agar tidak ...", pakai 〜ないように (lihat kartu 3).',
                    ],
                    'notes_en' => [
                        'The order can be reversed, but the usual form puts the purpose first.',
                        'The negative is rarely used with ために. For "so that I do not ...", use 〜ないように (see card 3).',
                    ],
                    'examples' => [
                        ['ja' => '日本《にほん》で 働《はたら》く ために、日本語《にほんご》を 勉強《べんきょう》して います。', 'reading' => 'Nihon de hataraku tame ni, nihongo o benkyou shite imasu.', 'id' => 'Saya belajar bahasa Jepang untuk bekerja di Jepang.', 'en' => 'I study Japanese in order to work in Japan.'],
                        ['ja' => '友達《ともだち》に 会《あ》う ために、京都《きょうと》へ 行《い》きます。', 'reading' => 'Tomodachi ni au tame ni, Kyouto e ikimasu.', 'id' => 'Saya pergi ke Kyoto untuk bertemu teman.', 'en' => 'I am going to Kyoto to meet a friend.'],
                        ['ja' => '家《いえ》を 買《か》う ために、毎月《まいつき》 貯金《ちょきん》して います。', 'reading' => 'Ie o kau tame ni, maitsuki chokin shite imasu.', 'id' => 'Saya menabung setiap bulan untuk membeli rumah.', 'en' => 'I save money every month to buy a house.'],
                        ['ja' => '試験《しけん》に 合格《ごうかく》する ために、毎晩《まいばん》 勉強《べんきょう》します。', 'reading' => 'Shiken ni goukaku suru tame ni, maiban benkyou shimasu.', 'id' => 'Saya belajar setiap malam supaya lulus ujian.', 'en' => 'I study every night in order to pass the exam.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menabung untuk kuliah',
                        'title_en' => 'Saving for study abroad',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'ワヒュさん、毎月《まいつき》 貯金《ちょきん》して いるんですか。', 'reading' => 'Wahyu-san, maitsuki chokin shite iru n desu ka.', 'id' => 'Wahyu, kamu menabung setiap bulan?', 'en' => 'Wahyu, do you save money every month?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、日本《にほん》へ 留学《りゅうがく》する ために、貯金《ちょきん》して います。', 'reading' => 'Ee, Nihon e ryuugaku suru tame ni, chokin shite imasu.', 'id' => 'Ya, saya menabung untuk belajar ke Jepang.', 'en' => 'Yes, I am saving to study in Japan.'],
                            ['speaker' => 'サリ', 'ja' => 'そうですか。がんばって くださいね。', 'reading' => 'Sou desu ka. Ganbatte kudasai ne.', 'id' => 'Begitu, ya. Semangat!', 'en' => 'I see. Good luck!'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ありがとうございます。', 'reading' => 'Arigatou gozaimasu.', 'id' => 'Terima kasih.', 'en' => 'Thank you.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => 'Nの ＋ ために (demi／untuk)',
                'title_en' => 'Nの ＋ ために (for the sake of)',
                'pattern' => 'Nの ＋ ために、〜',
                'payload' => [
                    'explanation_id' => 'Kata benda + の + ために berarti "demi N" atau "untuk N": tindakan dilakukan agar N mendapat manfaat atau tercapai. N bisa berupa orang (家族《かぞく》, 子《こ》どもたち), keadaan (健康《けんこう》, 平和《へいわ》), atau peristiwa (引《ひ》っ越《こ》し, 旅行《りょこう》). Bagian belakang berisi tindakan yang dilakukan demi N tersebut.',
                    'explanation_en' => 'Noun + の + ために means "for N" or "for the sake of N": the action is done so that N benefits or is achieved. N can be a person (家族《かぞく》, 子《こ》どもたち), a state (健康《けんこう》, 平和《へいわ》), or an event (引《ひ》っ越《こ》し, 旅行《りょこう》). The back part is the action done for that N.',
                    'notes_id' => [
                        'Kata benda yang tidak menyatakan tindakan atau hal yang dituju sulit dipakai; yang wajar adalah N yang bisa diuntungkan atau dicapai.',
                        'Untuk kata benda tindakan, bisa juga memakai kata kerja: 旅行《りょこう》の ために = 旅行《りょこう》する ために.',
                    ],
                    'notes_en' => [
                        'It works best when N is something that can benefit or be achieved.',
                        'For action nouns, a verb can be used instead: 旅行《りょこう》の ために = 旅行《りょこう》する ために.',
                    ],
                    'examples' => [
                        ['ja' => '家族《かぞく》の ために、毎日《まいにち》 働《はたら》いて います。', 'reading' => 'Kazoku no tame ni, mainichi hataraite imasu.', 'id' => 'Saya bekerja setiap hari demi keluarga.', 'en' => 'I work every day for my family.'],
                        ['ja' => '健康《けんこう》の ために、たばこを やめました。', 'reading' => 'Kenkou no tame ni, tabako o yamemashita.', 'id' => 'Demi kesehatan, saya berhenti merokok.', 'en' => 'I quit smoking for my health.'],
                        ['ja' => '子《こ》どもたちの ために、おいしい ごはんを 作《つく》ります。', 'reading' => 'Kodomotachi no tame ni, oishii gohan o tsukurimasu.', 'id' => 'Saya memasak makanan lezat untuk anak-anak.', 'en' => 'I cook delicious meals for the children.'],
                        ['ja' => '旅行《りょこう》の ために、新《あたら》しい かばんを 買《か》いました。', 'reading' => 'Ryokou no tame ni, atarashii kaban o kaimashita.', 'id' => 'Untuk perjalanan, saya membeli tas baru.', 'en' => 'I bought a new bag for the trip.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Hadiah untuk siapa?',
                        'title_en' => 'A gift for whom?',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'きれいな 花《はな》ですね。だれの ために 買《か》ったんですか。', 'reading' => 'Kirei na hana desu ne. Dare no tame ni katta n desu ka.', 'id' => 'Bunganya indah, ya. Dibeli untuk siapa?', 'en' => 'Pretty flowers. Who did you buy them for?'],
                            ['speaker' => 'ワヒュ', 'ja' => '母《はは》の ために 買《か》いました。きょうは 母《はは》の 誕生日《たんじょうび》なんです。', 'reading' => 'Haha no tame ni kaimashita. Kyou wa haha no tanjoubi na n desu.', 'id' => 'Saya beli untuk ibu. Hari ini ulang tahun ibu.', 'en' => 'I bought them for my mother. Today is her birthday.'],
                            ['speaker' => 'たなか', 'ja' => 'そうですか。いいですね。', 'reading' => 'Sou desu ka. Ii desu ne.', 'id' => 'Begitu, ya. Bagus sekali.', 'en' => 'I see. How nice.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => 'ために dan ように (membedakan tujuan)',
                'title_en' => 'ために and ように (telling purposes apart)',
                'pattern' => 'V(意志) ために／V(無意志・ない) ように',
                'payload' => [
                    'explanation_id' => 'Keduanya berarti "untuk/agar", tetapi kata kerja di depannya berbeda. ために dipakai dengan kata kerja yang menyatakan kehendak (bisa dilakukan sesuka hati: 持《も》つ, 行《い》く, 勉強《べんきょう》する) dan pelakunya sama. ように dipakai dengan kata kerja bukan kehendak (できる, わかる, なる, bentuk potensial) atau bentuk negatif ない, untuk menyatakan "agar keadaan menjadi ...". Sebuah kata kerja bisa memakai keduanya, dengan nuansa berbeda: ために menekankan tujuan yang ingin dicapai, ように menekankan keadaan yang diharapkan terjadi.',
                    'explanation_en' => 'Both mean "so that / in order to", but the verb in front differs. ために goes with verbs of will (things you can do at will: 持《も》つ, 行《い》く, 勉強《べんきょう》する) and the same doer. ように goes with non-volitional verbs (できる, わかる, なる, potential forms) or the negative ない, to mean "so that the situation becomes ...". Some verbs can take both, with a different feel: ために stresses the goal you want to reach, ように stresses the state you hope will come about.',
                    'notes_id' => [
                        'なります: 先生《せんせい》に なる ために (menjadi guru, kehendak sendiri) lawan 上手《じょうず》に なる ように (agar menjadi pandai, hasil yang diharapkan).',
                        'Pola ように akan dipelajari lebih lengkap di pelajaran lain; di sini cukup bisa membedakannya dari ために.',
                    ],
                    'notes_en' => [
                        'なります: 先生《せんせい》に なる ために (becoming a teacher, your own will) vs 上手《じょうず》に なる ように (so that you become good, a hoped-for result).',
                        'The ように pattern is covered in more depth in another lesson; here it is enough to tell it apart from ために.',
                    ],
                    'examples' => [
                        ['ja' => '日本語《にほんご》の 先生《せんせい》に なる ために、大学《だいがく》で 勉強《べんきょう》します。', 'reading' => 'Nihongo no sensei ni naru tame ni, daigaku de benkyou shimasu.', 'id' => 'Saya belajar di universitas untuk menjadi guru bahasa Jepang.', 'en' => 'I study at university in order to become a Japanese teacher.'],
                        ['ja' => '日本語《にほんご》が 上手《じょうず》に なる ように、毎日《まいにち》 話《はな》して います。', 'reading' => 'Nihongo ga jouzu ni naru you ni, mainichi hanashite imasu.', 'id' => 'Saya berbicara setiap hari agar bahasa Jepang saya menjadi pandai.', 'en' => 'I speak every day so that my Japanese gets good.'],
                        ['ja' => '弁護士《べんごし》に なる ために、法律《ほうりつ》を 勉強《べんきょう》して います。', 'reading' => 'Bengoshi ni naru tame ni, houritsu o benkyou shite imasu.', 'id' => 'Saya belajar hukum untuk menjadi pengacara.', 'en' => 'I study law in order to become a lawyer.'],
                        ['ja' => '忘《わす》れない ように、メモを します。', 'reading' => 'Wasurenai you ni, memo o shimasu.', 'id' => 'Saya mencatat agar tidak lupa.', 'en' => 'I take notes so that I do not forget.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Lari setiap pagi',
                        'title_en' => 'Running every morning',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '毎朝《まいあさ》 走《はし》って いるんですか。', 'reading' => 'Maiasa hashitte iru n desu ka.', 'id' => 'Kamu berlari setiap pagi?', 'en' => 'Do you run every morning?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、マラソンに 出《で》る ために、走《はし》って います。', 'reading' => 'Ee, marason ni deru tame ni, hashitte imasu.', 'id' => 'Ya, saya berlari untuk ikut maraton.', 'en' => 'Yes, I run in order to take part in a marathon.'],
                            ['speaker' => 'たなか', 'ja' => 'すごいですね。', 'reading' => 'Sugoi desu ne.', 'id' => 'Hebat, ya.', 'en' => 'That is impressive.'],
                            ['speaker' => 'ワヒュ', 'ja' => '疲《つか》れない ように、毎晩《まいばん》 早《はや》く 寝《ね》て います。', 'reading' => 'Tsukarenai you ni, maiban hayaku nete imasu.', 'id' => 'Supaya tidak lelah, saya tidur lebih awal setiap malam.', 'en' => 'So that I do not get tired, I go to bed early every night.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'V(kamus)の／Nに (kegunaan)',
                'title_en' => 'V (dictionary form)の／Nに (use or usefulness)',
                'pattern' => 'V(じしょけい)の／Nに ＋ 使《つか》います／便利《べんり》です',
                'payload' => [
                    'explanation_id' => 'Dipakai untuk menyatakan kegunaan: sesuatu "dipakai untuk ...", "praktis untuk ...", "berguna untuk ...", atau "memakan waktu untuk ...". Bagian depan berupa kata kerja bentuk kamus + の, atau kata benda + に. Bagian belakang biasanya salah satu dari: 使《つか》います, いいです, 便利《べんり》です, 役《やく》に 立《た》ちます, dan [時間《じかん》／お金《かね》が] かかります. の mengubah kata kerja menjadi kata benda.',
                    'explanation_en' => 'Used to state use: something is "used for ...", "handy for ...", "useful for ...", or "takes time/money to ...". The front is a dictionary-form verb + の, or a noun + に. The back is usually one of 使《つか》います, いいです, 便利《べんり》です, 役《やく》に 立《た》ちます, and [時間《じかん》／お金《かね》が] かかります. の turns the verb into a noun.',
                    'notes_id' => [
                        'Pertanyaannya: 何《なん》に 使《つか》いますか／何《なに》を するのに 使《つか》いますか (dipakai untuk apa?).',
                        'Jangan tertukar dengan のに yang berarti "padahal". Di sini の + に terpisah dan bagian belakangnya selalu tentang kegunaan.',
                    ],
                    'notes_en' => [
                        'Questions: 何《なん》に 使《つか》いますか／何《なに》を するのに 使《つか》いますか (what is it used for?).',
                        'Do not confuse it with のに meaning "even though". Here の + に are separate and the back part is always about use.',
                    ],
                    'examples' => [
                        ['ja' => 'この ナイフは パンを 切《き》るのに 使《つか》います。', 'reading' => 'Kono naifu wa pan o kiru no ni tsukaimasu.', 'id' => 'Pisau ini dipakai untuk memotong roti.', 'en' => 'This knife is used for cutting bread.'],
                        ['ja' => 'この かばんは 軽《かる》くて、旅行《りょこう》に 便利《べんり》です。', 'reading' => 'Kono kaban wa karukute, ryokou ni benri desu.', 'id' => 'Tas ini ringan, jadi praktis untuk bepergian.', 'en' => 'This bag is light, so it is handy for trips.'],
                        ['ja' => 'この 辞書《じしょ》は 漢字《かんじ》を 調《しら》べるのに 役《やく》に 立《た》ちます。', 'reading' => 'Kono jisho wa kanji o shiraberu no ni yaku ni tachimasu.', 'id' => 'Kamus ini berguna untuk mencari kanji.', 'en' => 'This dictionary is useful for looking up kanji.'],
                        ['ja' => '駅《えき》まで 歩《ある》くのに 二十分《にじゅっぷん》 かかります。', 'reading' => 'Eki made aruku no ni nijuppun kakarimasu.', 'id' => 'Berjalan kaki sampai stasiun memakan waktu dua puluh menit.', 'en' => 'It takes twenty minutes to walk to the station.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di toko perabot dapur',
                        'title_en' => 'At a kitchenware shop',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません、これは 何《なん》に 使《つか》うんですか。', 'reading' => 'Sumimasen, kore wa nan ni tsukau n desu ka.', 'id' => 'Permisi, ini dipakai untuk apa?', 'en' => 'Excuse me, what is this used for?'],
                            ['speaker' => '店員', 'ja' => '缶《かん》を 開《あ》けるのに 使《つか》います。缶切《かんき》りです。', 'reading' => 'Kan o akeru no ni tsukaimasu. Kankiri desu.', 'id' => 'Dipakai untuk membuka kaleng. Ini pembuka kaleng.', 'en' => 'It is used to open cans. It is a can opener.'],
                            ['speaker' => 'ワヒュ', 'ja' => '便利《べんり》ですね。これを ください。', 'reading' => 'Benri desu ne. Kore o kudasai.', 'id' => 'Praktis, ya. Saya ambil yang ini.', 'en' => 'Handy. I will take this one.'],
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => 'Bilangan ＋ は／も (batas minimal／banyak)',
                'title_en' => 'Quantity ＋ は／も (at least / as many as)',
                'pattern' => 'Bilangan ＋ は／も',
                'payload' => [
                    'explanation_id' => 'Partikel は dan も yang ditempelkan pada keterangan jumlah memberi kesan tentang banyak-sedikitnya. Bilangan + は berarti "paling tidak sebanyak itu" (batas minimal yang dianjurkan atau diharapkan pembicara). Bilangan + も berarti "sampai sebanyak itu!" (pembicara merasa jumlahnya banyak). Dengan kata lain, は menunjukkan batas bawah, sedangkan も mengungkapkan rasa kaget akan banyaknya.',
                    'explanation_en' => 'The particles は and も attached to a quantity convey a feeling about how much it is. Quantity + は means "at least that much" (a minimum the speaker recommends or expects). Quantity + も means "as many as that!" (the speaker feels it is a lot). In short, は marks a lower limit, while も expresses surprise at the large amount.',
                    'notes_id' => [
                        'Dengan も, kalimatnya biasanya afirmatif. Untuk menyatakan "sedikit", dipakai しか〜ない (lihat N5).',
                        'は pada bilangan sering dipakai untuk saran, misalnya 〜た ほうが いいです.',
                    ],
                    'notes_en' => [
                        'With も, the sentence is normally affirmative. To say "only a little", use しか〜ない (see N5).',
                        'は on a quantity is often used with advice, such as 〜た ほうが いいです.',
                    ],
                    'examples' => [
                        ['ja' => '日本語《にほんご》は 毎日《まいにち》 三十分《さんじゅっぷん》は 勉強《べんきょう》した ほうが いいです。', 'reading' => 'Nihongo wa mainichi sanjuppun wa benkyou shita hou ga ii desu.', 'id' => 'Bahasa Jepang sebaiknya dipelajari setidaknya tiga puluh menit setiap hari.', 'en' => 'You had better study Japanese at least thirty minutes every day.'],
                        ['ja' => 'あの 店《みせ》は いつも 二十人《にじゅうにん》も 並《なら》んで います。', 'reading' => 'Ano mise wa itsumo nijuunin mo narande imasu.', 'id' => 'Di toko itu selalu ada dua puluh orang yang antre.', 'en' => 'There are always as many as twenty people lined up at that shop.'],
                        ['ja' => 'きのうは ビールを 五本《ごほん》も 飲《の》みました。', 'reading' => 'Kinou wa biiru o gohon mo nomimashita.', 'id' => 'Kemarin saya minum bir sampai lima botol.', 'en' => 'Yesterday I drank as many as five bottles of beer.'],
                        ['ja' => '家《いえ》から 駅《えき》まで 三十分《さんじゅっぷん》は かかります。', 'reading' => 'Ie kara eki made sanjuppun wa kakarimasu.', 'id' => 'Dari rumah ke stasiun memerlukan paling tidak tiga puluh menit.', 'en' => 'It takes at least thirty minutes from my house to the station.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Belajar bahasa Jepang',
                        'title_en' => 'Studying Japanese',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '日本語《にほんご》の 勉強《べんきょう》は 毎日《まいにち》 どのぐらい して いますか。', 'reading' => 'Nihongo no benkyou wa mainichi donogurai shite imasu ka.', 'id' => 'Belajar bahasa Jepang setiap hari kira-kira berapa lama?', 'en' => 'About how long do you study Japanese each day?'],
                            ['speaker' => 'ワヒュ', 'ja' => '少《すく》なくても 三十分《さんじゅっぷん》は して います。きのうは 二時間《にじかん》も しました。', 'reading' => 'Sukunakute mo sanjuppun wa shite imasu. Kinou wa nijikan mo shimashita.', 'id' => 'Paling sedikit tiga puluh menit. Kemarin bahkan sampai dua jam.', 'en' => 'At least thirty minutes. Yesterday I did as much as two hours.'],
                            ['speaker' => 'サリ', 'ja' => 'えっ、二時間《にじかん》も？すごいですね。', 'reading' => 'E, nijikan mo? Sugoi desu ne.', 'id' => 'Eh, sampai dua jam? Hebat, ya.', 'en' => 'What, two whole hours? Impressive.'],
                        ],
                    ],
                ],
            ],

            // 6 ------------------------------------------------------------
            [
                'title_id' => '〜に よって (pelaku pada kalimat pasif)',
                'title_en' => '〜に よって (the agent in a passive sentence)',
                'pattern' => 'Pelaku ＋ に よって ＋ V(受身)',
                'payload' => [
                    'explanation_id' => 'Pada kalimat pasif tentang penciptaan atau penemuan (書《か》く, 作《つく》る, 発明《はつめい》する, 発見《はっけん》する), pelaku ditandai dengan に よって, bukan に biasa. Susunannya: karya／penemuan は pelaku に よって kata kerja pasif. Contoh: 電話《でんわ》は ベルに よって 発明《はつめい》されました. Bentuk pasifnya: 書《か》く → 書《か》かれる, 作《つく》る → 作《つく》られる, 発明《はつめい》する → 発明《はつめい》される.',
                    'explanation_en' => 'In passive sentences about creation or discovery (書《か》く, 作《つく》る, 発明《はつめい》する, 発見《はっけん》する), the agent is marked with に よって, not plain に. Structure: work／discovery は agent に よって passive verb. Example: 電話《でんわ》は ベルに よって 発明《はつめい》されました. Passive forms: 書《か》く → 書《か》かれる, 作《つく》る → 作《つく》られる, 発明《はつめい》する → 発明《はつめい》される.',
                    'notes_id' => [
                        'Kata benda yang menyatakan pelaku bisa orang atau kelompok. Tahun bisa ditambahkan di depan: 一九五八年《せんきゅうひゃくごじゅうはちねん》に.',
                        'Pasif adalah topik tersendiri; di pelajaran ini cukup mengenali pola に よって dalam kalimat seperti contoh.',
                    ],
                    'notes_en' => [
                        'The agent can be a person or a group. A year can be added in front: 一九五八年《せんきゅうひゃくごじゅうはちねん》に.',
                        'The passive is a topic of its own; in this lesson it is enough to recognise the に よって pattern in sentences like the examples.',
                    ],
                    'examples' => [
                        ['ja' => '電話《でんわ》は ベルに よって 発明《はつめい》されました。', 'reading' => 'Denwa wa Beru ni yotte hatsumei saremashita.', 'id' => 'Telepon diciptakan oleh Bell.', 'en' => 'The telephone was invented by Bell.'],
                        ['ja' => 'この 歌《うた》は 有名《ゆうめい》な 音楽家《おんがくか》に よって 作《つく》られました。', 'reading' => 'Kono uta wa yuumei na ongakuka ni yotte tsukuraremashita.', 'id' => 'Lagu ini dibuat oleh seorang musisi terkenal.', 'en' => 'This song was written by a famous musician.'],
                        ['ja' => '新《あたら》しい 星《ほし》が 日本人《にほんじん》に よって 発見《はっけん》されました。', 'reading' => 'Atarashii hoshi ga nihonjin ni yotte hakken saremashita.', 'id' => 'Sebuah bintang baru ditemukan oleh orang Jepang.', 'en' => 'A new star was discovered by a Japanese person.'],
                        ['ja' => '世界初《せかいはつ》の カップめんは 日本人《にほんじん》に よって 作《つく》られました。', 'reading' => 'Sekai hatsu no kappumen wa nihonjin ni yotte tsukuraremashita.', 'id' => 'Mie cup pertama di dunia dibuat oleh orang Jepang.', 'en' => 'The world first cup noodles were made by a Japanese person.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Lagu yang merdu',
                        'title_en' => 'A beautiful song',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'いい 曲《きょく》ですね。', 'reading' => 'Ii kyoku desu ne.', 'id' => 'Lagu yang bagus, ya.', 'en' => 'What a nice piece of music.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、有名《ゆうめい》な 音楽家《おんがくか》に よって 作《つく》られた 曲《きょく》なんです。', 'reading' => 'Ee, yuumei na ongakuka ni yotte tsukurareta kyoku na n desu.', 'id' => 'Ya, ini lagu yang diciptakan oleh musisi terkenal.', 'en' => 'Yes, it was composed by a famous musician.'],
                            ['speaker' => 'たなか', 'ja' => 'そうなんですか。もう 一度《いちど》 聞《き》きたいです。', 'reading' => 'Sou na n desu ka. Mou ichido kikitai desu.', 'id' => 'Oh begitu. Saya ingin mendengarnya sekali lagi.', 'en' => 'Is that so. I want to hear it once more.'],
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
            ['slug' => 'n4-pelajaran-17'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 17',
                'name_en' => 'N4 Lesson 17 Vocabulary',
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
            ['包みます', 'つつみます', 'tsutsumimasu', 'membungkus', 'to wrap'],
            ['沸かします', 'わかします', 'wakashimasu', 'memasak (air), mendidihkan', 'to boil'],
            ['混ぜます', 'まぜます', 'mazemasu', 'mencampur', 'to mix'],
            ['計算します', 'けいさんします', 'keisan shimasu', 'menghitung', 'to calculate'],
            ['並びます', 'ならびます', 'narabimasu', 'berbaris', 'to line up'],
            ['丈夫[な]', 'じょうぶ[な]', 'joubu', 'kuat', 'strong, sturdy'],
            ['アパート', 'アパート', 'apaato', 'apartemen', 'apartment'],
            ['弁護士', 'べんごし', 'bengoshi', 'pengacara', 'lawyer'],
            ['音楽家', 'おんがくか', 'ongakuka', 'musisi', 'musician'],
            ['子どもたち', 'こどもたち', 'kodomotachi', 'anak-anak', 'children'],
            ['自然', 'しぜん', 'shizen', 'alam', 'nature'],
            ['教育', 'きょういく', 'kyouiku', 'pendidikan', 'education'],
            ['文化', 'ぶんか', 'bunka', 'budaya', 'culture'],
            ['社会', 'しゃかい', 'shakai', 'masyarakat, sosial', 'society'],
            ['政治', 'せいじ', 'seiji', 'politik', 'politics'],
            ['法律', 'ほうりつ', 'houritsu', 'hukum', 'law'],
            ['戦争', 'せんそう', 'sensou', 'perang', 'war'],
            ['平和', 'へいわ', 'heiwa', 'damai, perdamaian', 'peace'],
            ['目的', 'もくてき', 'mokuteki', 'tujuan', 'purpose'],
            ['論文', 'ろんぶん', 'ronbun', 'tesis, makalah ilmiah', 'thesis, paper'],
            ['楽しみ', 'たのしみ', 'tanoshimi', 'kesenangan, hal yang dinanti', 'pleasure, something to look forward to'],
            ['ミキサー', 'ミキサー', 'mikisaa', 'mixer, alat pencampur listrik', 'mixer, blender'],
            ['やかん', 'やかん', 'yakan', 'ceret, ketel', 'kettle'],
            ['ふた', 'ふた', 'futa', 'tutup', 'lid'],
            ['栓抜き', 'せんぬき', 'sennuki', 'pembuka botol', 'bottle opener'],
            ['缶切り', 'かんきり', 'kankiri', 'pembuka kaleng', 'can opener'],
            ['缶詰', 'かんづめ', 'kanzume', 'makanan kaleng', 'canned food'],
            ['のし袋', 'のしぶくろ', 'noshibukuro', 'amplop khusus untuk mengisi uang', 'envelope for gift money'],
            ['ふろしき', 'ふろしき', 'furoshiki', 'kain pembungkus barang', 'wrapping cloth'],
            ['そろばん', 'そろばん', 'soroban', 'sempoa', 'abacus'],
            ['体温計', 'たいおんけい', 'taionkei', 'termometer', 'thermometer'],
            ['材料', 'ざいりょう', 'zairyou', 'bahan', 'ingredients, materials'],
            ['ある〜', 'ある〜', 'aru', 'suatu ~', 'a certain ~'],
            ['一生懸命', 'いっしょうけんめい', 'isshoukenmei', 'sungguh-sungguh', 'with all one\'s might'],
            ['なぜ', 'なぜ', 'naze', 'kenapa', 'why'],
            ['どのくらい', 'どのくらい', 'donokurai', 'berapa banyak, seberapa', 'how much, how long'],
            ['国連', 'こくれん', 'kokuren', 'Perserikatan Bangsa-Bangsa (PBB)', 'United Nations'],
            // 会話 (percakapan)
            ['出ます', 'でます', 'demasu', 'diberi [bonus]', 'to be paid out [a bonus]'],
            ['半分', 'はんぶん', 'hanbun', 'setengah', 'half'],
            ['ローン', 'ローン', 'roon', 'kredit, pinjaman', 'loan'],

            // 読み物 (bacaan)
            ['カップめん', 'カップめん', 'kappumen', 'mie instan dalam cup', 'cup noodles'],
            ['世界初', 'せかいはつ', 'sekai hatsu', 'pertama di dunia', 'world first'],
            ['〜によって', '〜によって', 'ni yotte', 'oleh ~', 'by ~'],
            ['どんぶり', 'どんぶり', 'donburi', 'mangkuk besar', 'large bowl'],
            ['めん', 'めん', 'men', 'mie', 'noodles'],
            ['広めます', 'ひろめます', 'hiromemasu', 'menyebarluaskan', 'to spread'],
            ['市場調査', 'しじょうちょうさ', 'shijou chousa', 'riset pemasaran', 'market research'],
            ['割ります', 'わります', 'warimasu', 'membagi', 'to divide'],
            ['注ぎます', 'そそぎます', 'sosogimasu', 'menuang, menyiram', 'to pour'],
        ];
    }
}
