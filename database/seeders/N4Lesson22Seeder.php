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

class N4Lesson22Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 22: "Katanya Telah Bertunangan".
     * Level N4 dibuat oleh N4Lesson1Seeder; seeder ini menambah unit order 22
     * di level itu (Unit.order hanya unik per level).
     *
     * Isi:
     *   - Unit order 22 ("Pelajaran 22")
     *   - Lesson Bunpou (order 0, category grammar) dengan 5 kartu:
     *       1. ふつうけい そうです (kabar dari pihak lain, 〜に よると)
     *       2. そうです (kabar) vs そうです (tampak) vs 〜と 言っていました
     *       3. ふつうけい ようです (dugaan dari keadaan)
     *       4. 〜ようです vs 〜そうです (tampak)
     *       5. 声／音／においが します
     *   - Kategori kosakata "n4-pelajaran-22" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus dari daftar kosakata
     * pelajaran ini. Penjelasan, contoh kalimat dan dialog ditulis baru untuk
     * aplikasi ini.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson22Seeder
     *
     * Kata kunci pencarian SQL memakai ASCII (kategori.slug + romaji), bukan
     * huruf Jepang (lihat docs/AGENTS.md aturan 4).
     */
    public function run(): void
    {
        if (! Level::where('code', 'N4')->exists()) {
            $this->call(N4Lesson1Seeder::class);
        }

        $level = Level::where('code', 'N4')->firstOrFail();

        $unit = Unit::updateOrCreate(
            ['level_id' => $level->id, 'order' => 22],
            [
                'title_id' => 'Pelajaran 22: Katanya Telah Bertunangan',
                'title_en' => 'Lesson 22: I Heard They Got Engaged',
                'description_id' => 'Menyampaikan kabar dan dugaan — ふつうけい そうです, 〜に よると, ふつうけい ようです, dan 声／音／においが します.',
                'description_en' => 'Reporting news and guessing — ふつうけい そうです, 〜に よると, ふつうけい ようです, and 声／音／においが します.',
                'icon' => 'mdi-newspaper-variant',
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

        app(VocabularyQuizSync::class)->run('N4');
    }

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
                'title_id' => 'ふつうけい そうです (katanya ...)',
                'title_en' => 'ふつうけい そうです (I hear that ...)',
                'pattern' => 'ふつうけい ＋ そうです',
                'payload' => [
                    'explanation_id' => 'Menyampaikan kabar yang kamu peroleh dari pihak lain, tanpa menambahkan pendapat sendiri: "katanya ...". Rumusnya: bentuk biasa + そうです. Sumber kabar biasanya disebut di depan dengan 〜に よると (menurut ~). Kata sifat な dan kata benda tetap membawa だ: きれいだそうです, 雨《あめ》だそうです.',
                    'explanation_en' => 'Passes on news you got from someone or something else, without adding your own opinion: "I hear that ...". Formula: plain form + そうです. The source is usually given first with 〜に よると (according to ~). な-adjectives and nouns keep だ: きれいだそうです, 雨《あめ》だそうです.',
                    'notes_id' => [
                        'Sumber umum: ニュース, 天気予報《てんきよほう》, 新聞《しんぶん》, 友達《ともだち》の 話《はなし》 + に よると.',
                        'Hanya bentuk biasa yang dipakai sebelum そうです; bentuk sopan (ます／です) tidak bisa.',
                    ],
                    'notes_en' => [
                        'Common sources: ニュース, 天気予報《てんきよほう》, 新聞《しんぶん》, 友達《ともだち》の 話《はなし》 + に よると.',
                        'Only the plain form goes before そうです; the polite form (ます／です) cannot be used.',
                    ],
                    'examples' => [
                        $this->ex('ニュースに よると、きのう 大《おお》きい 事故《じこ》が あったそうです。', 'Nyuusu ni yoru to, kinou ookii jiko ga atta sou desu.', 'Menurut berita, kemarin terjadi kecelakaan besar.', 'According to the news, there was a big accident yesterday.'),
                        $this->ex('天気予報《てんきよほう》に よると、あさっては 雨《あめ》だそうです。', 'Tenki yohou ni yoru to, asatte wa ame da sou desu.', 'Menurut prakiraan cuaca, lusa akan hujan.', 'According to the weather forecast, it will rain the day after tomorrow.'),
                        $this->ex('友達《ともだち》の 話《はなし》に よると、あの 店《みせ》の ラーメンは おいしいそうです。', 'Tomodachi no hanashi ni yoru to, ano mise no raamen wa oishii sou desu.', 'Menurut cerita teman, ramen di toko itu enak.', 'According to my friend, the ramen at that shop is good.'),
                        $this->ex('たなかさんは 学生《がくせい》の とき、ピアノを 習《なら》って いたそうです。', 'Tanaka-san wa gakusei no toki, piano o naratte ita sou desu.', 'Katanya Tanaka dulu belajar piano waktu masih mahasiswa.', 'I hear that Tanaka took piano lessons as a student.'),
                    ],
                    'dialogue' => [
                        'title_id' => 'Kabar dari televisi',
                        'title_en' => 'News from the TV',
                        'lines' => [
                            $this->line('サリ', 'ニュースに よると、あした 台風《たいふう》が 来《く》るそうですよ。', 'Nyuusu ni yoru to, ashita taifuu ga kuru sou desu yo.', 'Menurut berita, besok topan akan datang.', 'According to the news, a typhoon is coming tomorrow.'),
                            $this->line('ワヒュ', 'えっ、本当《ほんとう》ですか。', 'E, hontou desu ka.', 'Eh, benarkah?', 'Really?'),
                            $this->line('サリ', 'ええ。電車《でんしゃ》も 止《と》まるそうです。', 'Ee. Densha mo tomaru sou desu.', 'Ya. Katanya kereta juga berhenti.', 'Yes. I hear the trains will stop too.'),
                            $this->line('ワヒュ', 'じゃ、今日《きょう》の うちに 水《みず》を 買《か》って おきます。', 'Ja, kyou no uchi ni mizu o katte okimasu.', 'Kalau begitu, hari ini saya beli air dulu.', 'Then I will buy water today in advance.'),
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => 'Dua そうです dan 〜と 言って いました',
                'title_en' => 'The two そうです and 〜と 言って いました',
                'pattern' => 'ふつうけい そうです ⇔ 語幹 そうです ⇔ 〜と 言《い》って いました',
                'payload' => [
                    'explanation_id' => 'Ada dua そうです yang cara menyambung dan artinya berbeda. (1) Bentuk biasa + そうです = katanya (kabar): 雨《あめ》が 降《ふ》るそうです. (2) Bentuk ます tanpa ます／kata sifat tanpa い + そうです = tampaknya (kesan dari penampilan): 雨《あめ》が 降《ふ》りそうです. Selain itu, 〜と 言《い》って いました juga menyampaikan ucapan, tetapi sumbernya jelas orang yang mengucapkannya, sedangkan 〜そうです tidak harus begitu.',
                    'explanation_en' => 'There are two そうです with different connection and meaning. (1) Plain form + そうです = I hear that (news): 雨《あめ》が 降《ふ》るそうです. (2) ます-stem／adjective stem + そうです = looks like (an impression from appearance): 雨《あめ》が 降《ふ》りそうです. 〜と 言《い》って いました also reports speech, but its source is the person who said it, while 〜そうです need not have such a source.',
                    'notes_id' => [
                        'Kabar: 降《ふ》るそうです／おいしいそうです. Tampak: 降《ふ》りそうです／おいしそうです.',
                        'Sumber ucapan jelas: ミラーさんは あした 行《い》くと 言《い》って いました. Kabar umum: あした 行《い》くそうです.',
                    ],
                    'notes_en' => [
                        'News: 降《ふ》るそうです／おいしいそうです. Looks like: 降《ふ》りそうです／おいしそうです.',
                        'Speaker is the source: ミラーさんは あした 行《い》くと 言《い》って いました. General report: あした 行《い》くそうです.',
                    ],
                    'examples' => [
                        $this->ex('雨《あめ》が 降《ふ》るそうです。', 'Ame ga furu sou desu.', 'Katanya akan hujan.', 'I hear it will rain.'),
                        $this->ex('雨《あめ》が 降《ふ》りそうです。', 'Ame ga furi sou desu.', 'Sepertinya mau hujan (dari tampak langit).', 'It looks like it is going to rain.'),
                        $this->ex('この ケーキは おいしいそうです。', 'Kono keeki wa oishii sou desu.', 'Katanya kue ini enak.', 'I hear this cake is good.'),
                        $this->ex('この ケーキは おいしそうです。', 'Kono keeki wa oishi sou desu.', 'Kue ini kelihatannya enak.', 'This cake looks delicious.'),
                        $this->ex('たなかさんは あした 休《やす》むと 言《い》って いました。', 'Tanaka-san wa ashita yasumu to itte imashita.', 'Tanaka bilang bahwa besok dia libur.', 'Tanaka said he will take a day off tomorrow.'),
                    ],
                    'dialogue' => [
                        'title_id' => 'Soal restoran',
                        'title_en' => 'About a restaurant',
                        'lines' => [
                            $this->line('サリ', 'この 店《みせ》の パスタは おいしいそうですよ。', 'Kono mise no pasuta wa oishii sou desu yo.', 'Katanya pasta di restoran ini enak.', 'I hear the pasta in this restaurant is good.'),
                            $this->line('ワヒュ', 'あ、あの 人《ひと》が 食《た》べて いるのも おいしそうですね。', 'A, ano hito ga tabete iru no mo oishi sou desu ne.', 'Ah, yang dimakan orang itu juga kelihatan enak.', 'Ah, what that person is eating looks good too.'),
                            $this->line('サリ', 'じゃ、あれを 頼《たの》みましょう。', 'Ja, are o tanomimashou.', 'Kalau begitu, mari pesan yang itu.', 'Then let us order that one.'),
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => 'ふつうけい ようです (rupanya ...)',
                'title_en' => 'ふつうけい ようです (it seems that ...)',
                'pattern' => 'ふつうけい ＋ ようです',
                'payload' => [
                    'explanation_id' => 'Menyatakan dugaan atau kesimpulan pembicara yang ditarik dari keadaan yang ia lihat, dengar, atau rasakan sendiri: "rupanya ...", "sepertinya ...". Bentuk biasa + ようです. Kata sifat な memakai な (にぎやかなようです) dan kata benda memakai の (事故《じこ》のようです). Sering disertai どうも yang berarti "belum pasti, tetapi ...".',
                    'explanation_en' => 'Expresses the speaker\'s guess or conclusion drawn from conditions they have seen, heard, or felt themselves: "it seems that ...". Plain form + ようです. な-adjectives take な (にぎやかなようです) and nouns take の (事故《じこ》のようです). Often used with どうも, meaning "not certain, but ...".',
                    'notes_id' => [
                        'Dasarnya adalah bukti di depan mata atau telinga, bukan kabar dari orang lain (itu そうです).',
                        'どうも ＋ 〜ようです: "kelihatannya sih ..." — kesimpulan yang belum pasti.',
                    ],
                    'notes_en' => [
                        'The basis is evidence before your own eyes or ears, not news from someone else (that is そうです).',
                        'どうも ＋ 〜ようです: "it looks like ..." — a conclusion that is not yet certain.',
                    ],
                    'examples' => [
                        $this->ex('あの 部屋《へや》に 人《ひと》が いるようです。声《こえ》が しますから。', 'Ano heya ni hito ga iru you desu. Koe ga shimasu kara.', 'Rupanya ada orang di kamar itu. Soalnya terdengar suara.', 'It seems someone is in that room, because I hear a voice.'),
                        $this->ex('のども 痛《いた》いし、熱《ねつ》も あります。どうも かぜの ようです。', 'Nodo mo itai shi, netsu mo arimasu. Doumo kaze no you desu.', 'Tenggorokan sakit dan demam. Sepertinya saya masuk angin.', 'My throat hurts and I have a fever. It looks like I have a cold.'),
                        $this->ex('道《みち》が ぬれて いますね。夜《よる》に 雨《あめ》が 降《ふ》ったようです。', 'Michi ga nurete imasu ne. Yoru ni ame ga futta you desu.', 'Jalannya basah, ya. Rupanya semalam hujan.', 'The road is wet. It seems it rained during the night.'),
                        $this->ex('隣《となり》の 部屋《へや》は にぎやかなようですね。', 'Tonari no heya wa nigiyaka na you desu ne.', 'Kamar sebelah sepertinya ramai, ya.', 'The room next door seems lively.'),
                    ],
                    'dialogue' => [
                        'title_id' => 'Suara dari kamar sebelah',
                        'title_en' => 'A sound from next door',
                        'lines' => [
                            $this->line('サリ', '隣《となり》の 部屋《へや》から 音楽《おんがく》が 聞《き》こえますね。', 'Tonari no heya kara ongaku ga kikoemasu ne.', 'Dari kamar sebelah terdengar musik, ya.', 'Music is coming from the room next door.'),
                            $this->line('ワヒュ', 'どうも パーティーを して いるようですね。', 'Doumo paatii o shite iru you desu ne.', 'Sepertinya sedang berpesta.', 'It seems they are having a party.'),
                            $this->line('サリ', '人《ひと》も たくさん いるようです。', 'Hito mo takusan iru you desu.', 'Rupanya orangnya juga banyak.', 'There seem to be many people too.'),
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'ようです vs そうです (tampak)',
                'title_en' => 'ようです vs そうです (looks)',
                'pattern' => '語幹 ＋ そうです ⇔ ふつうけい ＋ ようです',
                'payload' => [
                    'explanation_id' => 'Keduanya terkesan sama, tetapi berbeda. 〜そうです (tampak) hanya melukiskan kesan luar yang terlihat: 忙《いそが》しそうです = kelihatan sibuk. 〜ようです adalah kesimpulan pembicara yang ditarik dari beberapa keadaan, misalnya setelah menghubungi atau karena orangnya tidak datang ke acara: 忙《いそが》しいようです = rupanya (memang) sibuk.',
                    'explanation_en' => 'They seem alike but differ. 〜そうです (looks) only describes an outward impression: 忙《いそが》しそうです = looks busy. 〜ようです is the speaker\'s conclusion drawn from several conditions, for example after contacting someone or because they did not come to an event: 忙《いそが》しいようです = it seems (they really) are busy.',
                    'notes_id' => [
                        'Menyambung: そうです ← kata sifat tanpa い (忙《いそが》し‑そう). ようです ← bentuk biasa (忙《いそが》しい‑よう).',
                        'Jika ada alasan yang jadi dasar kesimpulan, pakai ようです.',
                    ],
                    'notes_en' => [
                        'Connection: そうです ← adjective stem (忙《いそが》し‑そう). ようです ← plain form (忙《いそが》しい‑よう).',
                        'When there is a reason you base your conclusion on, use ようです.',
                    ],
                    'examples' => [
                        $this->ex('たなかさんは 忙《いそが》しそうです。', 'Tanaka-san wa isogashi sou desu.', 'Tanaka kelihatan sibuk.', 'Tanaka looks busy.'),
                        $this->ex('パーティーに 来《き》ませんでしたから、たなかさんは 忙《いそが》しいようです。', 'Paatii ni kimasen deshita kara, Tanaka-san wa isogashii you desu.', 'Karena tidak datang ke pesta, rupanya Tanaka sibuk.', 'He did not come to the party, so Tanaka seems to be busy.'),
                        $this->ex('電気《でんき》が 消《き》えて います。だれも いないようです。', 'Denki ga kiete imasu. Dare mo inai you desu.', 'Lampunya padam. Rupanya tidak ada siapa-siapa.', 'The lights are off. It seems nobody is there.'),
                        $this->ex('この 本《ほん》は むずかしそうです。', 'Kono hon wa muzukashi sou desu.', 'Buku ini kelihatannya sulit.', 'This book looks difficult.'),
                    ],
                    'dialogue' => [
                        'title_id' => 'Rekan yang tidak datang',
                        'title_en' => 'A colleague who did not show up',
                        'lines' => [
                            $this->line('たなか', 'ワヒュさんは 今日《きょう》 忙《いそが》しそうでしたね。', 'Wahyu-san wa kyou isogashi sou deshita ne.', 'Wahyu tadi kelihatan sibuk, ya.', 'Wahyu looked busy today.'),
                            $this->line('サリ', 'ええ。昼《ひる》ごはんも 食《た》べて いないようです。', 'Ee. Hirugohan mo tabete inai you desu.', 'Ya. Rupanya makan siang pun belum.', 'Yes. It seems he has not even had lunch.'),
                            $this->line('たなか', 'じゃ、パンを 買《か》って いきましょう。', 'Ja, pan o katte ikimashou.', 'Kalau begitu, mari kita belikan roti.', 'Then let us bring him some bread.'),
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => '声／音／におい／味が します (indra)',
                'title_en' => '声／音／におい／味が します (the senses)',
                'pattern' => '声／音／におい／味が します',
                'payload' => [
                    'explanation_id' => 'します dipakai untuk menyatakan hal yang kamu tangkap lewat panca indera: suara (声《こえ》), bunyi (音《おと》), bau (におい), dan rasa (味《あじ》). Susunannya: Ｎ が します. Ｎ yang menjadi dasar ditandai が. Kata sifat sebelum Ｎ bisa ditambahkan: いい においが します, 変《へん》な 味《あじ》が します.',
                    'explanation_en' => 'します is used to say what you perceive through the senses: voices (声《こえ》), sounds (音《おと》), smells (におい) and tastes (味《あじ》). Structure: Ｎ が します. The perceived thing is marked が. An adjective may precede it: いい においが します, 変《へん》な 味《あじ》が します.',
                    'notes_id' => [
                        'Pembicara hanya menyatakan yang ia tangkap sekarang; sering diikuti ね.',
                        'Tempat sumbernya ditandai から: 台所《だいどころ》から いい においが します.',
                    ],
                    'notes_en' => [
                        'The speaker only reports what they perceive right now; often followed by ね.',
                        'The source location is marked with から: 台所《だいどころ》から いい においが します.',
                    ],
                    'examples' => [
                        $this->ex('隣《となり》の 部屋《へや》から 音《おと》が します。', 'Tonari no heya kara oto ga shimasu.', 'Terdengar bunyi dari kamar sebelah.', 'I hear a noise from the next room.'),
                        $this->ex('台所《だいどころ》から いい においが しますね。', 'Daidokoro kara ii nioi ga shimasu ne.', 'Dari dapur tercium bau yang enak, ya.', 'There is a nice smell from the kitchen.'),
                        $this->ex('この スープは 変《へん》な 味《あじ》が します。', 'Kono suupu wa hen na aji ga shimasu.', 'Sup ini rasanya aneh.', 'This soup tastes strange.'),
                        $this->ex('外《そと》で 子《こ》どもの 声《こえ》が します。', 'Soto de kodomo no koe ga shimasu.', 'Di luar terdengar suara anak-anak.', 'I hear children\'s voices outside.'),
                    ],
                    'dialogue' => [
                        'title_id' => 'Bau dari dapur',
                        'title_en' => 'A smell from the kitchen',
                        'lines' => [
                            $this->line('ワヒュ', 'あれ、何《なに》か 燃《も》えるにおいが しますね。', 'Are, nani ka moeru nioi ga shimasu ne.', 'Lho, tercium bau seperti sesuatu terbakar.', 'Hey, there is a smell of something burning.'),
                            $this->line('サリ', '台所《だいどころ》から 音《おと》も しますよ。', 'Daidokoro kara oto mo shimasu yo.', 'Dari dapur juga terdengar bunyi.', 'I hear a noise from the kitchen too.'),
                            $this->line('ワヒュ', 'あ、いけない！ガスを 切《き》るのを 忘《わす》れました。', 'A, ikenai! Gasu o kiru no o wasuremashita.', 'Aduh! Saya lupa mematikan gas.', 'Oh no! I forgot to turn off the gas.'),
                        ],
                    ],
                ],
            ],
        ];
    }

    private function seedVocabulary(): void
    {
        $category = VocabularyCategory::updateOrCreate(
            ['slug' => 'n4-pelajaran-22'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 22',
                'name_en' => 'N4 Lesson 22 Vocabulary',
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
            // Kata kerja
            ['吹きます', 'ふきます', 'fukimasu', '[angin] bertiup', 'to blow [wind]'],
            ['燃えます', 'もえます', 'moemasu', '[sampah] terbakar', 'to burn [garbage]'],
            ['亡くなります', 'なくなります', 'nakunarimasu', 'meninggal dunia (ungkapan halus untuk 死にます)', 'to pass away'],
            ['集まります', 'あつまります', 'atsumarimasu', '[orang] berkumpul', 'to gather [people]'],
            ['別れます', 'わかれます', 'wakaremasu', '[orang] berpisah', 'to part [people]'],
            ['音がします', 'おとがします', 'oto ga shimasu', 'berbunyi, terdengar bunyi', 'to hear a sound'],
            ['声がします', 'こえがします', 'koe ga shimasu', 'bersuara, terdengar suara', 'to hear a voice'],
            ['味がします', 'あじがします', 'aji ga shimasu', 'terasa, berasa', 'to taste of'],
            ['においがします', 'においがします', 'nioi ga shimasu', 'berbau, tercium bau', 'to smell of'],

            // Kata sifat
            ['厳しい', 'きびしい', 'kibishii', 'keras, berat', 'strict, severe'],
            ['ひどい', 'ひどい', 'hidoi', 'kejam, parah', 'terrible, cruel'],
            ['怖い', 'こわい', 'kowai', 'takut, menakutkan', 'scary'],

            // Kata benda
            ['実験', 'じっけん', 'jikken', 'percobaan, eksperimen', 'experiment'],
            ['データ', 'データ', 'deeta', 'data', 'data'],
            ['人口', 'じんこう', 'jinkou', 'penduduk, populasi', 'population'],
            ['におい', 'におい', 'nioi', 'bau', 'smell'],
            ['科学', 'かがく', 'kagaku', 'ilmu pengetahuan', 'science'],
            ['医学', 'いがく', 'igaku', 'ilmu kedokteran', 'medicine (the field)'],
            ['文学', 'ぶんがく', 'bungaku', 'sastra', 'literature'],
            ['パトカー', 'パトカー', 'patokaa', 'mobil patroli', 'police car'],
            ['救急車', 'きゅうきゅうしゃ', 'kyuukyuusha', 'mobil ambulan', 'ambulance'],
            ['賛成', 'さんせい', 'sansei', 'persetujuan', 'agreement, approval'],
            ['反対', 'はんたい', 'hantai', 'penentangan, kebalikan', 'opposition'],
            ['大統領', 'だいとうりょう', 'daitouryou', 'presiden', 'president'],
            ['〜によると', '〜によると', 'ni yoru to', 'menurut ~ (menyatakan sumber informasi)', 'according to ~'],

            // 会話 (percakapan)
            ['婚約します', 'こんやくします', 'konyakushimasu', 'bertunangan', 'to get engaged'],
            ['どうも', 'どうも', 'doumo', 'rupanya, sepertinya (dugaan)', 'it seems (a guess)'],
            ['恋人', 'こいびと', 'koibito', 'pacar', 'sweetheart, partner'],
            ['相手', 'あいて', 'aite', 'pasangan, lawan', 'partner, the other party'],
            ['知り合います', 'しりあいます', 'shiriaimasu', 'berkenalan', 'to get acquainted'],

            // 読み物 (bacaan)
            ['化粧', 'けしょう', 'keshou', 'dandan, rias (〜をします: berdandan)', 'make-up'],
            ['世話をします', 'せわをします', 'sewa o shimasu', 'menjaga, membantu', 'to look after'],
            ['女性', 'じょせい', 'josei', 'wanita', 'woman'],
            ['男性', 'だんせい', 'dansei', 'pria', 'man'],
            ['長生き', 'ながいき', 'nagaiki', 'panjang umur (〜します: berumur panjang)', 'long life'],
            ['理由', 'りゆう', 'riyuu', 'alasan', 'reason'],
            ['関係', 'かんけい', 'kankei', 'hubungan', 'relationship, connection'],
        ];
    }
}
