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

class N4Lesson9Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 9 ("Lakukan Seperti yang Saya Lakukan"). Berdiri di
     * level N4 yang sama dengan Pelajaran 1 dan dipilih lewat selektor N5 / N4
     * di Tata Bahasa, Kosakata dan Referensi Tata Bahasa.
     *
     * Isi:
     *   - Unit order 9 ("Pelajaran 9") di level N4
     *   - Lesson Bunpou (order 0, category grammar) dengan 5 kartu:
     *       1. Vた とおりに、V        4. Vて V (keadaan penyerta)
     *       2. N の とおりに、V      5. Vないで V (tanpa ~ / bukan ~ melainkan ~)
     *       3. Vた あとで／N の あとで
     *   - Kategori kosakata "n4-pelajaran-9" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus. Penjelasan, contoh
     * kalimat dan dialog ditulis baru untuk aplikasi ini. Nama diri tidak
     * dimasukkan. Pelajaran 2-8 belum ada; unit 9 berdiri sendiri dan menu
     * kosakata hanya menampilkan unit yang ada.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson9Seeder
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
            ['level_id' => $level->id, 'order' => 9],
            [
                'title_id' => 'Pelajaran 9: Lakukan Seperti yang Saya Lakukan',
                'title_en' => 'Lesson 9: Do It the Way I Do',
                'description_id' => 'Pelajaran 9 — 〜とおりに, 〜あとで, 〜て／〜ないで (keadaan penyerta).',
                'description_en' => 'Lesson 9 — 〜とおりに, 〜あとで, 〜て／〜ないで (accompanying state).',
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
                'title_id' => 'Vた とおりに (seperti yang sudah dilakukan／dilihat／didengar)',
                'title_en' => 'Vた とおりに (just as it was done／seen／heard)',
                'pattern' => 'V₁(た形) とおりに、V₂',
                'payload' => [
                    'explanation_id' => 'Menyatakan bahwa V₂ dilakukan dengan cara atau keadaan yang sama persis seperti V₁ yang sudah terjadi. Kata kerja di depan とおりに memakai bentuk-た, lalu V₂ meniru hasilnya. Pola ini cocok untuk meminta orang menirukan contoh ("Lakukan seperti yang tadi saya lakukan"), atau untuk menceritakan sesuatu apa adanya ("Ceritakan sesuai yang kamu lihat").',
                    'explanation_en' => 'States that V₂ is done in exactly the same way or state as V₁, which has already happened. The verb before とおりに is in the た-form, and V₂ follows that example. It suits asking someone to copy a model ("Do it the way I just did") or reporting something as it was ("Tell it as you saw it").',
                    'notes_id' => [
                        'とおり (通り) adalah kata benda, jadi bentuk-た langsung bersambung ke とおりに tanpa の.',
                        'Kalimat akhirnya sering berupa permintaan (〜て ください／〜て みて ください), tetapi kalimat biasa juga bisa.',
                        'Kata kerja yang sering dipakai di depannya: やる, 言《い》う, 書《か》く, 作《つく》る, 見《み》る.',
                    ],
                    'notes_en' => [
                        'とおり (通り) is a noun, so the た-form connects directly to とおりに without の.',
                        'The sentence often ends in a request (〜て ください／〜て みて ください), but a plain statement works too.',
                        'Verbs often seen before it: やる, 言《い》う, 書《か》く, 作《つく》る, 見《み》る.',
                    ],
                    'examples' => [
                        ['ja' => '先生《せんせい》が 書《か》いた とおりに、ノートに 写《うつ》して ください。', 'reading' => 'Sensei ga kaita toori ni, nooto ni utsushite kudasai.', 'id' => 'Salin ke buku catatan persis seperti yang ditulis guru.', 'en' => 'Copy it into your notebook exactly as the teacher wrote it.'],
                        ['ja' => 'きのう 聞《き》いた とおりに、みんなに 伝《つた》えました。', 'reading' => 'Kinou kiita toori ni, minna ni tsutaemashita.', 'id' => 'Saya menyampaikan kepada semuanya sesuai apa yang kemarin saya dengar.', 'en' => 'I passed it on to everyone just as I heard it yesterday.'],
                        ['ja' => '母《はは》が 作《つく》った とおりに、作《つく》って みました。', 'reading' => 'Haha ga tsukutta toori ni, tsukutte mimashita.', 'id' => 'Saya mencoba membuatnya persis seperti yang dibuat ibu.', 'en' => 'I tried making it exactly the way my mother made it.'],
                        ['ja' => '先輩《せんぱい》が やった とおりに、やって みて ください。', 'reading' => 'Senpai ga yatta toori ni, yatte mite kudasai.', 'id' => 'Cobalah lakukan seperti yang dilakukan senior.', 'en' => 'Please try doing it the way your senior did it.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Belajar memecahkan telur',
                        'title_en' => 'Learning to crack an egg',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'ワヒュさん、わたしが やった とおりに、卵《たまご》を 割《わ》って みて ください。', 'reading' => 'Wahyu-san, watashi ga yatta toori ni, tamago o watte mite kudasai.', 'id' => 'Wahyu, coba pecahkan telurnya seperti yang saya lakukan.', 'en' => 'Wahyu, please try cracking the egg the way I did.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい。……こう ですか。', 'reading' => 'Hai. ...Kou desu ka.', 'id' => 'Baik. ……Begini?', 'en' => 'Okay. ...Like this?'],
                            ['speaker' => 'たなか', 'ja' => 'ええ、上手《じょうず》ですね。その とおりに もう 一《ひと》つ 割《わ》って ください。', 'reading' => 'Ee, jouzu desu ne. Sono toori ni mou hitotsu watte kudasai.', 'id' => 'Ya, pandai sekali. Pecahkan satu lagi dengan cara yang sama.', 'en' => 'Yes, very good. Crack one more the same way.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'わかりました。', 'reading' => 'Wakarimashita.', 'id' => 'Baik, mengerti.', 'en' => 'Understood.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => 'N の とおりに (sesuai petunjuk／gambar／garis)',
                'title_en' => 'N の とおりに (according to a guide／diagram／line)',
                'pattern' => 'Ｎの とおりに、V',
                'payload' => [
                    'explanation_id' => 'Menyatakan bahwa suatu tindakan dilakukan tanpa menyimpang dari standar yang ditunjukkan oleh sebuah benda: petunjuk, gambar, garis, peta, catatan. Bendanya dihubungkan dengan の lalu disusul とおりに. Kalau とおり didahului kata tunjuk (この／その／あの), の tidak dipakai: この とおりに, その とおりに. Artinya "seperti ini／seperti itu".',
                    'explanation_en' => 'States that an action is carried out without straying from the standard shown by an object: a manual, a diagram, a line, a map, a memo. The object is linked with の and followed by とおりに. When とおり follows a demonstrative (この／その／あの), no の is used: この とおりに, その とおりに, meaning "like this／like that".',
                    'notes_id' => [
                        'Benda yang lazim di depan の: 説明書《せつめいしょ》, 図《ず》, 線《せん》, 地図《ちず》, メモ, レシピ.',
                        'Dalam tulisan sehari-hari sering menempel tanpa の dan berbunyi どおり: 説明書《せつめいしょ》どおりに, 予定《よてい》どおりに.',
                    ],
                    'notes_en' => [
                        'Typical nouns before の: 説明書《せつめいしょ》, 図《ず》, 線《せん》, 地図《ちず》, メモ, レシピ.',
                        'In everyday writing it often attaches without の and is read どおり: 説明書《せつめいしょ》どおりに, 予定《よてい》どおりに.',
                    ],
                    'examples' => [
                        ['ja' => '地図《ちず》の とおりに 行《い》きましたが、店《みせ》が 見《み》つかりませんでした。', 'reading' => 'Chizu no toori ni ikimashita ga, mise ga mitsukarimasen deshita.', 'id' => 'Saya pergi sesuai peta, tetapi tokonya tidak ditemukan.', 'en' => 'I went as the map showed, but I could not find the shop.'],
                        ['ja' => 'メモの とおりに、買《か》い物《もの》を して きました。', 'reading' => 'Memo no toori ni, kaimono o shite kimashita.', 'id' => 'Saya berbelanja sesuai catatan.', 'en' => 'I did the shopping according to my memo.'],
                        ['ja' => '図《ず》の とおりに、紙《かみ》を 折《お》って ください。', 'reading' => 'Zu no toori ni, kami o otte kudasai.', 'id' => 'Lipat kertasnya sesuai gambar.', 'en' => 'Please fold the paper as shown in the diagram.'],
                        ['ja' => 'その とおりに やって みます。', 'reading' => 'Sono toori ni yatte mimasu.', 'id' => 'Akan saya coba lakukan seperti itu.', 'en' => 'I will try doing it just like that.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Merakit rak buku',
                        'title_en' => 'Assembling a bookshelf',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'ワヒュさん、本棚《ほんだな》は できましたか。', 'reading' => 'Wahyu-san, hondana wa dekimashita ka.', 'id' => 'Wahyu, apakah rak bukunya sudah jadi?', 'en' => 'Wahyu, is the bookshelf finished?'],
                            ['speaker' => 'ワヒュ', 'ja' => '説明書《せつめいしょ》の とおりに 組《く》み立《た》てたんですが、うまく 立《た》たないんです。', 'reading' => 'Setsumeisho no toori ni kumitateta n desu ga, umaku tatanai n desu.', 'id' => 'Saya merakitnya sesuai petunjuk, tetapi tidak bisa berdiri dengan baik.', 'en' => 'I assembled it according to the manual, but it will not stand properly.'],
                            ['speaker' => 'サリ', 'ja' => 'ちょっと 図《ず》を 見《み》せて ください。……ここが 違《ちが》いますよ。矢印《やじるし》の とおりに もう 一度《いちど》 やって みましょう。', 'reading' => 'Chotto zu o misete kudasai. ...Koko ga chigaimasu yo. Yajirushi no toori ni mou ichido yatte mimashou.', 'id' => 'Coba tunjukkan gambarnya. ……Bagian ini keliru. Mari kita coba lagi sesuai tanda panahnya.', 'en' => 'Let me see the diagram. ...This part is wrong. Let us try again, following the arrows.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'あ、本当《ほんとう》ですね。ありがとうございます。', 'reading' => 'A, hontou desu ne. Arigatou gozaimasu.', 'id' => 'Oh, benar juga. Terima kasih.', 'en' => 'Oh, you are right. Thank you.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => 'Vた あとで／N の あとで (setelah ...)',
                'title_en' => 'Vた あとで／N の あとで (after ...)',
                'pattern' => 'V(た形) あとで、〜／Ｎの あとで、〜',
                'payload' => [
                    'explanation_id' => 'Menyatakan bahwa V₂ terjadi setelah V₁ atau setelah suatu kegiatan (N) selesai. Kata kerja memakai bentuk-た, kata benda memakai の. Dibandingkan 〜て から (N5), 〜た あとで menekankan hubungan waktu "sebelum–sesudah" saja. 〜て から memberi kesan bahwa V₁ adalah dasar atau persiapan bagi V₂.',
                    'explanation_en' => 'States that V₂ happens after V₁, or after an activity (N) is over. Verbs take the た-form and nouns take の. Compared with 〜て から (N5), 〜た あとで stresses only the before–after time relationship, while 〜て から suggests that V₁ is the basis or preparation for V₂.',
                    'notes_id' => [
                        'Bentuk-た di depan あとで bukan penanda lampau. Peristiwa V₂ boleh di masa depan: 晩《ばん》ごはんを 食《た》べた あとで、薬《くすり》を 飲《の》みます。',
                        'Kata benda yang dipakai berupa kegiatan atau kejadian: 会議《かいぎ》, 授業《じゅぎょう》, 仕事《しごと》, 食事《しょくじ》.',
                    ],
                    'notes_en' => [
                        'The た-form before あとで is not a past marker. V₂ may be in the future: 晩《ばん》ごはんを 食《た》べた あとで、薬《くすり》を 飲《の》みます。',
                        'The noun is an activity or event: 会議《かいぎ》, 授業《じゅぎょう》, 仕事《しごと》, 食事《しょくじ》.',
                    ],
                    'examples' => [
                        ['ja' => 'おふろに 入《はい》った あとで、冷《つめ》たい 水《みず》を 飲《の》みます。', 'reading' => 'Ofuro ni haitta ato de, tsumetai mizu o nomimasu.', 'id' => 'Setelah mandi berendam, saya minum air dingin.', 'en' => 'After taking a bath, I drink cold water.'],
                        ['ja' => '会議《かいぎ》の あとで、みんなで 昼《ひる》ごはんを 食《た》べませんか。', 'reading' => 'Kaigi no ato de, minna de hirugohan o tabemasen ka.', 'id' => 'Setelah rapat, maukah kita makan siang bersama-sama?', 'en' => 'After the meeting, shall we all have lunch together?'],
                        ['ja' => '授業《じゅぎょう》が 終《お》わった あとで、先生《せんせい》に 質問《しつもん》しました。', 'reading' => 'Jugyou ga owatta ato de, sensei ni shitsumon shimashita.', 'id' => 'Setelah pelajaran selesai, saya bertanya kepada guru.', 'en' => 'After class ended, I asked the teacher a question.'],
                        ['ja' => '財布《さいふ》を 買《か》った あとで、気《き》が つきました。同《おな》じ 物《もの》を もう 持《も》って いたんです。', 'reading' => 'Saifu o katta ato de, ki ga tsukimashita. Onaji mono o mou motte ita n desu.', 'id' => 'Setelah membeli dompet, saya baru sadar. Ternyata saya sudah punya yang sama.', 'en' => 'After buying the wallet, I realized I already had the same one.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mengajak belajar setelah kelas',
                        'title_en' => 'Inviting someone to study after class',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '授業《じゅぎょう》の あとで、時間《じかん》が ありますか。', 'reading' => 'Jugyou no ato de, jikan ga arimasu ka.', 'id' => 'Setelah kelas, apakah kamu ada waktu?', 'en' => 'After class, do you have some time?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、少《すこ》し あります。どうしたんですか。', 'reading' => 'Ee, sukoshi arimasu. Doushita n desu ka.', 'id' => 'Ya, sedikit. Ada apa?', 'en' => 'Yes, a little. What is it?'],
                            ['speaker' => 'サリ', 'ja' => '図書館《としょかん》で 一緒《いっしょ》に 勉強《べんきょう》しませんか。', 'reading' => 'Toshokan de issho ni benkyou shimasen ka.', 'id' => 'Maukah belajar bersama di perpustakaan?', 'en' => 'Would you like to study together at the library?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいですね。昼《ひる》ごはんを 食《た》べた あとで、行《い》きましょう。', 'reading' => 'Ii desu ne. Hirugohan o tabeta ato de, ikimashou.', 'id' => 'Boleh. Setelah makan siang, mari kita pergi.', 'en' => 'Sounds good. Let us go after lunch.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'Vて V (melakukan sesuatu dengan keadaan tertentu)',
                'title_en' => 'Vて V (doing something in a certain state)',
                'pattern' => 'V₁て、V₂',
                'payload' => [
                    'explanation_id' => 'Bentuk-て pada V₁ menyatakan keadaan atau cara yang menyertai tindakan V₂. Inti kalimat ada pada V₂, sedangkan V₁ menjelaskan dalam keadaan apa V₂ dilakukan, misalnya memakai atau menggunakan sesuatu. Pelaku V₁ dan V₂ sama. Contoh: しょうゆを つけて 食《た》べます = makan dengan membubuhkan kecap asin.',
                    'explanation_en' => 'The て-form of V₁ expresses the state or manner that accompanies V₂. The point of the sentence is V₂, and V₁ explains in what condition it is done, for example while using or wearing something. V₁ and V₂ have the same doer. Example: しょうゆを つけて 食《た》べます = eat with soy sauce on it.',
                    'notes_id' => [
                        'Berbeda dari bentuk-て yang menyambung urutan ("lalu"): di sini V₁ bukan langkah terpisah, melainkan keadaan selama V₂ berlangsung.',
                        'Kata kerja yang lazim di V₁: つける, さす, かける, 持《も》つ, 着《き》る, 見《み》る.',
                    ],
                    'notes_en' => [
                        'Unlike the て-form that links a sequence ("and then"), V₁ here is not a separate step but the state during V₂.',
                        'Common V₁ verbs: つける, さす, かける, 持《も》つ, 着《き》る, 見《み》る.',
                    ],
                    'examples' => [
                        ['ja' => 'きょうは 雨《あめ》ですから、傘《かさ》を さして 学校《がっこう》へ 行《い》きます。', 'reading' => 'Kyou wa ame desu kara, kasa o sashite gakkou e ikimasu.', 'id' => 'Hari ini hujan, jadi saya pergi ke sekolah dengan berpayung.', 'en' => 'It is raining today, so I go to school holding an umbrella.'],
                        ['ja' => '兄《あに》は いつも めがねを かけて 本《ほん》を 読《よ》みます。', 'reading' => 'Ani wa itsumo megane o kakete hon o yomimasu.', 'id' => 'Kakak laki-laki saya selalu membaca buku dengan memakai kacamata.', 'en' => 'My older brother always reads books wearing glasses.'],
                        ['ja' => 'ソースを つけて 食《た》べて ください。', 'reading' => 'Sousu o tsukete tabete kudasai.', 'id' => 'Silakan makan dengan dibubuhi saus.', 'en' => 'Please eat it with the sauce on.'],
                        ['ja' => 'メモを 見《み》て 話《はな》しても いいですか。', 'reading' => 'Memo o mite hanashite mo ii desu ka.', 'id' => 'Bolehkah saya berbicara sambil melihat catatan?', 'en' => 'May I speak while looking at my notes?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Cara makan tonkatsu',
                        'title_en' => 'How to eat tonkatsu',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'たなかさん、とんかつは どう 食《た》べたら いいですか。', 'reading' => 'Tanaka-san, tonkatsu wa dou tabetara ii desu ka.', 'id' => 'Pak/Bu Tanaka, bagaimana sebaiknya makan tonkatsu?', 'en' => 'Mr./Ms. Tanaka, how should I eat tonkatsu?'],
                            ['speaker' => 'たなか', 'ja' => 'ソースを つけて 食《た》べて ください。おいしいですよ。', 'reading' => 'Sousu o tsukete tabete kudasai. Oishii desu yo.', 'id' => 'Silakan makan dengan saus. Enak, lho.', 'en' => 'Please eat it with sauce. It is delicious.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい、いただきます。……本当《ほんとう》に おいしいですね。', 'reading' => 'Hai, itadakimasu. ...Hontou ni oishii desu ne.', 'id' => 'Baik, selamat makan. ……Benar-benar enak.', 'en' => 'Okay, let me eat. ...It is really delicious.'],
                            ['speaker' => 'たなか', 'ja' => 'キャベツも 一緒《いっしょ》に どうぞ。', 'reading' => 'Kyabetsu mo issho ni douzo.', 'id' => 'Kolnya juga, silakan.', 'en' => 'Please have some cabbage with it too.'],
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => 'Vないで V (tanpa ...／bukan ..., melainkan ...)',
                'title_en' => 'Vないで V (without ...／not ..., but ...)',
                'pattern' => 'V₁(ない形) ＋ で、V₂',
                'payload' => [
                    'explanation_id' => 'Bentuk-ない ditambah で menyatakan bahwa V₁ tidak dilakukan, lalu V₂ dilakukan. Ada dua pemakaian. (1) Keadaan penyerta, kebalikan dari 〜て: melakukan V₂ tanpa V₁ (しょうゆを つけないで 食《た》べます = makan tanpa kecap asin). (2) Memilih salah satu dari dua tindakan yang tidak bisa dilakukan bersamaan: tidak V₁, melainkan V₂.',
                    'explanation_en' => 'The ない-form plus で says that V₁ is not done and V₂ is done. It has two uses. (1) An accompanying state, the opposite of 〜て: doing V₂ without V₁ (しょうゆを つけないで 食《た》べます = eat without soy sauce). (2) Choosing one of two actions that cannot be done together: not V₁, but V₂ instead.',
                    'notes_id' => [
                        '〜ないで ください (N5) berarti "tolong jangan ~" dan berdiri sendiri sebagai permintaan. Di sini ないで menyambung ke kata kerja lain sebagai keadaan atau pilihan: ノートを 見《み》ないで 答《こた》えて ください = jawablah tanpa melihat catatan.',
                        'Bentuk-ない: 食《た》べる → 食《た》べない, 行《い》く → 行《い》かない, する → しない, 来《く》る → 来《こ》ない. Tambahkan で setelahnya.',
                    ],
                    'notes_en' => [
                        '〜ないで ください (N5) means "please do not ~" and stands alone as a request. Here ないで links to another verb as a state or a choice: ノートを 見《み》ないで 答《こた》えて ください = answer without looking at your notes.',
                        'ない-form: 食《た》べる → 食《た》べない, 行《い》く → 行《い》かない, する → しない, 来《く》る → 来《こ》ない. Add で after it.',
                    ],
                    'examples' => [
                        ['ja' => '朝《あさ》ごはんを 食《た》べないで、学校《がっこう》へ 来《き》ました。', 'reading' => 'Asagohan o tabenaide, gakkou e kimashita.', 'id' => 'Saya datang ke sekolah tanpa sarapan.', 'en' => 'I came to school without eating breakfast.'],
                        ['ja' => 'ノートを 見《み》ないで、答《こた》えて ください。', 'reading' => 'Nooto o minaide, kotaete kudasai.', 'id' => 'Jawablah tanpa melihat buku catatan.', 'en' => 'Please answer without looking at your notebook.'],
                        ['ja' => 'バスに 乗《の》らないで、歩《ある》いて 行《い》きます。', 'reading' => 'Basu ni noranaide, aruite ikimasu.', 'id' => 'Tidak naik bus, melainkan berjalan kaki.', 'en' => 'I will walk instead of taking the bus.'],
                        ['ja' => 'テレビを 見《み》ないで、早《はや》く 寝《ね》ましょう。', 'reading' => 'Terebi o minaide, hayaku nemashou.', 'id' => 'Mari tidur lebih awal, jangan menonton TV.', 'en' => 'Let us go to bed early instead of watching TV.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Kurang tidur',
                        'title_en' => 'Not enough sleep',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '元気《げんき》が ありませんね。どうしたんですか。', 'reading' => 'Genki ga arimasen ne. Doushita n desu ka.', 'id' => 'Kamu tampak lesu. Ada apa?', 'en' => 'You seem low on energy. What is the matter?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'きのうの 夜《よる》、寝《ね》ないで 勉強《べんきょう》したんです。', 'reading' => 'Kinou no yoru, nenaide benkyou shita n desu.', 'id' => 'Tadi malam saya belajar tanpa tidur.', 'en' => 'Last night I studied without sleeping.'],
                            ['speaker' => 'たなか', 'ja' => 'そうですか。きょうは どこへも 寄《よ》らないで、まっすぐ 帰《かえ》ったら いいですよ。', 'reading' => 'Sou desu ka. Kyou wa doko e mo yoranaide, massugu kaettara ii desu yo.', 'id' => 'Begitu, ya. Hari ini sebaiknya langsung pulang tanpa mampir ke mana-mana.', 'en' => 'I see. Today you should go straight home without stopping anywhere.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい、そう します。', 'reading' => 'Hai, sou shimasu.', 'id' => 'Baik, akan saya lakukan.', 'en' => 'Yes, I will do that.'],
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
            ['slug' => 'n4-pelajaran-9'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 9',
                'name_en' => 'N4 Lesson 9 Vocabulary',
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
     * Romaji harus unik di dalam pelajaran ini (dipakai sebagai kunci), dan
     * arti (meaning_id) harus berbeda antar kata agar pilihan kuis tidak ganda.
     *
     * @return array<int, array<int, string>>
     */
    private function words(): array
    {
        return [
            // Kosakata utama
            ['磨きます', 'みがきます', 'migakimasu', 'menggosok [gigi]', 'to brush, to polish [teeth]'],
            ['組み立てます', 'くみたてます', 'kumitatemasu', 'merakit, memasang', 'to assemble, to put together'],
            ['折ります', 'おります', 'orimasu', 'melipat', 'to fold'],
            ['気が付きます', 'きがつきます', 'ki ga tsukimasu', 'menyadari, teringat [barang tertinggal]', 'to notice, to realize [e.g. a forgotten item]'],
            ['つけます', 'つけます', 'tsukemasu', 'membubuhkan, mencelupkan [kecap asin]', 'to put on, to dip [soy sauce]'],
            ['見つかります', 'みつかります', 'mitsukarimasu', 'ditemukan [mis. kunci]', 'to be found [e.g. keys]'],
            ['質問します', 'しつもんします', 'shitsumon shimasu', 'bertanya', 'to ask a question'],
            ['差します', 'さします', 'sashimasu', 'memakai [payung], berpayung', 'to hold up [an umbrella]'],
            ['スポーツクラブ', 'スポーツクラブ', 'supootsu kurabu', 'klub olahraga', 'sports club'],
            ['お城', 'おしろ', 'oshiro', 'istana, kastil', 'castle'],
            ['説明書', 'せつめいしょ', 'setsumeisho', 'buku petunjuk', 'manual, instructions'],
            ['図', 'ず', 'zu', 'gambar, diagram', 'diagram, figure'],
            ['線', 'せん', 'sen', 'garis', 'line'],
            ['矢印', 'やじるし', 'yajirushi', 'tanda panah', 'arrow mark'],
            ['黒', 'くろ', 'kuro', 'hitam', 'black'],
            ['白', 'しろ', 'shiro', 'putih', 'white'],
            ['赤', 'あか', 'aka', 'merah', 'red'],
            ['青', 'あお', 'ao', 'biru', 'blue'],
            ['紺', 'こん', 'kon', 'biru tua', 'navy blue'],
            ['黄色', 'きいろ', 'kiiro', 'kuning', 'yellow'],
            ['茶色', 'ちゃいろ', 'chairo', 'coklat', 'brown'],
            ['しょうゆ', 'しょうゆ', 'shouyu', 'kecap asin', 'soy sauce'],
            ['ソース', 'ソース', 'sousu', 'saus (jenis Worcester)', 'Worcestershire-style sauce'],
            ['お客さん', 'おきゃくさん', 'okyaku-san', 'tamu, pelanggan', 'guest, customer'],
            ['〜か〜', '〜か〜', 'ka', '~ atau ~', '~ or ~'],
            ['ゆうべ', 'ゆうべ', 'yuube', 'semalam, tadi malam', 'last night'],
            ['さっき', 'さっき', 'sakki', 'tadi, barusan', 'a little while ago'],

            // 会話 (percakapan)
            ['茶道', 'さどう', 'sadou', 'upacara minum teh', 'tea ceremony'],
            ['お茶をたてます', 'おちゃをたてます', 'ocha o tatemasu', 'menyeduh teh (dalam upacara teh)', 'to prepare tea (in a tea ceremony)'],
            ['先に', 'さきに', 'saki ni', 'duluan, lebih dulu', 'first, ahead'],
            ['載せます', 'のせます', 'nosemasu', 'menaruh di atas, meletakkan', 'to put on top, to place'],
            ['これでいいですか', 'これでいいですか', 'kore de ii desu ka', 'Boleh seperti ini?', 'Is this all right?'],
            ['いかがですか', 'いかがですか', 'ikaga desu ka', 'Bagaimana (menurut Anda)?', 'How is it? (polite)'],
            ['苦い', 'にがい', 'nigai', 'pahit', 'bitter'],

            // 読み物 (bacaan)
            ['親子どんぶり', 'おやこどんぶり', 'oyako donburi', 'oyakodon (nasi mangkuk ayam dan telur)', 'oyakodon (chicken and egg rice bowl)'],
            ['材料', 'ざいりょう', 'zairyou', 'bahan', 'ingredients'],
            ['〜分', '〜ぶん', 'bun', 'porsi untuk ~', 'portion for ~ (e.g. 〜人分)'],
            ['グラム', 'グラム', 'guramu', 'gram', 'gram'],
            ['〜個', '〜こ', 'ko', '~ buah (penghitung benda kecil)', '~ pieces (counter for small items)'],
            ['たまねぎ', 'たまねぎ', 'tamanegi', 'bawang bombai', 'onion'],
            ['4分の1', 'よんぶんのいち', 'yonbun no ichi', 'seperempat', 'one quarter'],
            ['調味料', 'ちょうみりょう', 'choumiryou', 'bumbu, bahan penyedap', 'seasoning'],
            ['適当な大きさに', 'てきとうなおおきさに', 'tekitou na ookisa ni', 'dengan ukuran yang pas', 'to a suitable size'],
            ['なべ', 'なべ', 'nabe', 'panci', 'pot, pan'],
            ['火', 'ひ', 'hi', 'api', 'fire'],
            ['火にかけます', 'ひにかけます', 'hi ni kakemasu', 'menaruh di atas api, memanaskan', 'to put on the heat'],
            ['煮ます', 'にます', 'nimasu', 'merebus, menyemur', 'to boil, to simmer'],
            ['煮えます', 'にえます', 'niemasu', 'matang (karena direbus)', 'to be cooked, to be done boiling'],
            ['どんぶり', 'どんぶり', 'donburi', 'mangkuk besar (untuk nasi)', 'large rice bowl'],
            ['経ちます', 'たちます', 'tachimasu', 'berlalu (waktu)', 'to pass (time)'],
        ];
    }
}
