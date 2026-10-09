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

class N4Lesson19Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 19 ("Tolong Buat Seperti Foto Ini"). Berdiri di
     * level N4 yang sama dengan Pelajaran 1 dan 9 dan dipilih lewat selektor
     * N5 / N4 di Tata Bahasa, Kosakata dan Referensi Tata Bahasa.
     *
     * Isi:
     *   - Unit order 19 ("Pelajaran 19") di level N4
     *   - Lesson Bunpou (order 0, category grammar) dengan 4 kartu:
     *       1. 〜すぎます (berlebihan)          3. Ｎを 〜く／〜に します (mengubah)
     *       2. Vます 〜やすい／〜にくい          4. Ｎに します (memutuskan pilihan)
     *   - Kategori kosakata "n4-pelajaran-19" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus. Penjelasan, contoh
     * kalimat dan dialog ditulis baru untuk aplikasi ini. Nama diri tidak
     * dimasukkan. Pelajaran N4 yang belum ada tidak muncul di menu.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson19Seeder
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
            ['level_id' => $level->id, 'order' => 19],
            [
                'title_id' => 'Pelajaran 19: Tolong Buat Seperti Foto Ini',
                'title_en' => 'Lesson 19: Please Make It Like This Photo',
                'description_id' => 'Pelajaran 19 — 〜すぎます, 〜やすい／〜にくい, 〜く／〜に します, Ｎに します.',
                'description_en' => 'Lesson 19 — 〜すぎます, 〜やすい／〜にくい, 〜く／〜に します, Ｎに します.',
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
                'title_id' => '〜すぎます (terlalu ...)',
                'title_en' => '〜すぎます (too much ...)',
                'pattern' => 'Vます／イ形(〜い)／ナ形 ＋ すぎます',
                'payload' => [
                    'explanation_id' => 'Menyatakan bahwa suatu perbuatan atau keadaan melewati batas yang pantas. Biasanya dipakai untuk hal yang kurang menyenangkan: terlalu banyak, terlalu besar, terlalu sulit. Cara membentuknya: kata kerja bentuk-ます tanpa ます + すぎます, kata sifat い tanpa い + すぎます, kata sifat な tanpa な + すぎます.',
                    'explanation_en' => 'States that an action or a state goes beyond a fitting limit. It is usually used for things that are not pleasant: too much, too big, too difficult. Formation: verb ます-stem + すぎます, い-adjective without い + すぎます, な-adjective without な + すぎます.',
                    'notes_id' => [
                        'すぎます berubah seperti kata kerja Kelompok II: 飲《の》みすぎる, 飲《の》みすぎない, 飲《の》みすぎた, 飲《の》みすぎて.',
                        'Pengecualian: いい menjadi よすぎます (bukan いすぎます).',
                        'Untuk kata benda atau kata sifat yang sudah mengandung makna berlebihan, cukup pakai kata biasa; すぎます menambahkan kesan "melewati batas".',
                    ],
                    'notes_en' => [
                        'すぎます conjugates like a Group II verb: 飲《の》みすぎる, 飲《の》みすぎない, 飲《の》みすぎた, 飲《の》みすぎて.',
                        'Exception: いい becomes よすぎます (not いすぎます).',
                        'It adds the feeling of "going past the limit" to an ordinary word.',
                    ],
                    'examples' => [
                        ['ja' => 'きのうは 食《た》べすぎて、おなかが 痛《いた》く なりました。', 'reading' => 'Kinou wa tabesugite, onaka ga itaku narimashita.', 'id' => 'Kemarin saya makan terlalu banyak sampai perut jadi sakit.', 'en' => 'I ate too much yesterday and my stomach started to hurt.'],
                        ['ja' => 'この かばんは 小《ちい》さすぎます。', 'reading' => 'Kono kaban wa chiisasugimasu.', 'id' => 'Tas ini terlalu kecil.', 'en' => 'This bag is too small.'],
                        ['ja' => 'この 部屋《へや》は 静《しず》かすぎて、ちょっと さびしいです。', 'reading' => 'Kono heya wa shizuka sugite, chotto sabishii desu.', 'id' => 'Kamar ini terlalu sunyi, jadi agak sepi.', 'en' => 'This room is too quiet, so it feels a little lonely.'],
                        ['ja' => 'テレビを 見《み》すぎると、目《め》が 疲《つか》れますよ。', 'reading' => 'Terebi o misugiru to, me ga tsukaremasu yo.', 'id' => 'Kalau terlalu banyak menonton TV, mata jadi lelah.', 'en' => 'If you watch too much TV, your eyes get tired.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Memilih sepatu',
                        'title_en' => 'Choosing shoes',
                        'lines' => [
                            ['speaker' => '店員', 'ja' => 'いらっしゃいませ。この 靴《くつ》は いかがですか。', 'reading' => 'Irasshaimase. Kono kutsu wa ikaga desu ka.', 'id' => 'Selamat datang. Bagaimana dengan sepatu ini?', 'en' => 'Welcome. How about these shoes?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'うーん、ちょっと 小《ちい》さすぎます。もう 少《すこ》し 大《おお》きいのは ありますか。', 'reading' => 'Uun, chotto chiisasugimasu. Mou sukoshi ookii no wa arimasu ka.', 'id' => 'Hmm, agak terlalu kecil. Adakah yang sedikit lebih besar?', 'en' => 'Hmm, they are a bit too small. Do you have a slightly bigger pair?'],
                            ['speaker' => '店員', 'ja' => 'はい、こちらは どうですか。', 'reading' => 'Hai, kochira wa dou desu ka.', 'id' => 'Ada, bagaimana dengan yang ini?', 'en' => 'Yes, how about this pair?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'あ、これは ちょうど いいです。', 'reading' => 'A, kore wa choudo ii desu.', 'id' => 'Oh, yang ini pas sekali.', 'en' => 'Oh, these fit just right.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => 'Vます 〜やすい／〜にくい (mudah／sulit ...)',
                'title_en' => 'Vます 〜やすい／〜にくい (easy／hard to ...)',
                'pattern' => 'Vます ＋ やすいです／にくいです',
                'payload' => [
                    'explanation_id' => 'Kata kerja bentuk-ます tanpa ます + やすい (mudah) atau にくい (sulit). Ada dua arti. (1) Kata kerja yang dilakukan dengan sengaja: melakukannya mudah／sulit (書《か》きやすい = enak ditulis). (2) Kata kerja yang terjadi dengan sendirinya: hal itu mudah／jarang terjadi (割《わ》れやすい = mudah pecah).',
                    'explanation_en' => 'Verb ます-stem + やすい (easy) or にくい (hard). It has two meanings. (1) With a verb done on purpose: doing it is easy／hard (書《か》きやすい = easy to write with). (2) With a verb that happens by itself: it easily／rarely happens (割《わ》れやすい = breaks easily).',
                    'notes_id' => [
                        'やすい／にくい berubah seperti kata sifat い: 使《つか》いやすくない, 使《つか》いやすかった, 使《つか》いやすく なります.',
                        'Benda yang dibicarakan menjadi topik (は): この 漢字《かんじ》は 読《よ》みにくいです。',
                    ],
                    'notes_en' => [
                        'やすい／にくい conjugate like い-adjectives: 使《つか》いやすくない, 使《つか》いやすかった, 使《つか》いやすく なります.',
                        'The thing being described becomes the topic (は): この 漢字《かんじ》は 読《よ》みにくいです。',
                    ],
                    'examples' => [
                        ['ja' => 'この ペンは 書《か》きやすいです。', 'reading' => 'Kono pen wa kakiyasui desu.', 'id' => 'Pulpen ini enak dipakai menulis.', 'en' => 'This pen is easy to write with.'],
                        ['ja' => 'この 漢字《かんじ》は 読《よ》みにくいです。', 'reading' => 'Kono kanji wa yominikui desu.', 'id' => 'Kanji ini sulit dibaca.', 'en' => 'This kanji is hard to read.'],
                        ['ja' => 'ガラスの コップは 割《わ》れやすいですから、気《き》を つけて ください。', 'reading' => 'Garasu no koppu wa wareyasui desu kara, ki o tsukete kudasai.', 'id' => 'Gelas kaca mudah pecah, jadi hati-hati.', 'en' => 'Glass cups break easily, so please be careful.'],
                        ['ja' => '冬《ふゆ》は 風邪《かぜ》を ひきやすいです。', 'reading' => 'Fuyu wa kaze o hikiyasui desu.', 'id' => 'Pada musim dingin, orang mudah masuk angin.', 'en' => 'In winter it is easy to catch a cold.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Memilih kamus',
                        'title_en' => 'Choosing a dictionary',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'この 辞書《じしょ》は 使《つか》いやすいですか。', 'reading' => 'Kono jisho wa tsukaiyasui desu ka.', 'id' => 'Apakah kamus ini mudah dipakai?', 'en' => 'Is this dictionary easy to use?'],
                            ['speaker' => 'サリ', 'ja' => 'ええ、字《じ》が 大《おお》きくて 読《よ》みやすいですよ。', 'reading' => 'Ee, ji ga ookikute yomiyasui desu yo.', 'id' => 'Ya, hurufnya besar sehingga mudah dibaca.', 'en' => 'Yes, the letters are big, so it is easy to read.'],
                            ['speaker' => 'サリ', 'ja' => 'ほかの 辞書《じしょ》は 字《じ》が 小《ちい》さくて 読《よ》みにくいんです。', 'reading' => 'Hoka no jisho wa ji ga chiisakute yominikui n desu.', 'id' => 'Kamus yang lain hurufnya kecil, jadi sulit dibaca.', 'en' => 'The other dictionaries have small letters, so they are hard to read.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'そうですか。じゃ、これに します。', 'reading' => 'Sou desu ka. Ja, kore ni shimasu.', 'id' => 'Begitu, ya. Kalau begitu, saya pilih ini.', 'en' => 'I see. Then I will take this one.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => 'Ｎを 〜く／〜に します (mengubah keadaan)',
                'title_en' => 'Ｎを 〜く／〜に します (changing a state)',
                'pattern' => 'Ｎ₁を イ形(〜く)／ナ形(〜に)／Ｎ₂に します',
                'payload' => [
                    'explanation_id' => 'Menyatakan bahwa pembicara sengaja mengubah suatu benda (N₁) menjadi keadaan lain. Kata sifat い: buang い lalu ganti く (大《おお》きい → 大《おお》きく). Kata sifat な: buang な lalu ganti に (きれい → きれいに). Kata benda: N₂ + に. Dibandingkan 〜く／〜に なります (N5) yang menyatakan sesuatu berubah dengan sendirinya, 〜く／〜に します memiliki pelaku yang mengubahnya.',
                    'explanation_en' => 'States that the speaker deliberately changes something (N₁) into another state. い-adjective: drop い and add く (大《おお》きい → 大《おお》きく). な-adjective: drop な and add に (きれい → きれいに). Noun: N₂ + に. Compared with 〜く／〜に なります (N5), where something changes by itself, 〜く／〜に します has someone who makes the change.',
                    'notes_id' => [
                        'Contoh pasangan: お湯《ゆ》が 熱《あつ》く なりました (airnya menjadi panas sendiri) ↔ お湯《ゆ》を 熱《あつ》く しました (saya memanaskan airnya).',
                        'Bentuk permintaan 〜く／〜に して ください dipakai di salon, toko, dan tempat jasa: もう 少《すこ》し 短《みじか》く して ください。',
                    ],
                    'notes_en' => [
                        'A pair: お湯《ゆ》が 熱《あつ》く なりました (the water became hot by itself) ↔ お湯《ゆ》を 熱《あつ》く しました (I heated the water).',
                        'The request form 〜く／〜に して ください is used at salons, shops, and services: もう 少《すこ》し 短《みじか》く して ください。',
                    ],
                    'examples' => [
                        ['ja' => 'スカートを もう 少《すこ》し 長《なが》く して ください。', 'reading' => 'Sukaato o mou sukoshi nagaku shite kudasai.', 'id' => 'Tolong panjangkan roknya sedikit lagi.', 'en' => 'Please make the skirt a little longer.'],
                        ['ja' => 'エアコンの 温度《おんど》を 低《ひく》く しました。', 'reading' => 'Eakon no ondo o hikuku shimashita.', 'id' => 'Saya menurunkan suhu AC.', 'en' => 'I lowered the air conditioner temperature.'],
                        ['ja' => '机《つくえ》の 上《うえ》を きれいに しましょう。', 'reading' => 'Tsukue no ue o kirei ni shimashou.', 'id' => 'Mari kita bersihkan bagian atas meja.', 'en' => 'Let us tidy up the top of the desk.'],
                        ['ja' => 'ミルクの 量《りょう》を 二倍《にばい》に します。', 'reading' => 'Miruku no ryou o nibai ni shimasu.', 'id' => 'Jumlah susunya saya jadikan dua kali lipat.', 'en' => 'I will double the amount of milk.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mengatur AC',
                        'title_en' => 'Adjusting the air conditioner',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'ワヒュさん、ちょっと 寒《さむ》くないですか。', 'reading' => 'Wahyu-san, chotto samukunai desu ka.', 'id' => 'Wahyu, apakah kamu tidak merasa agak dingin?', 'en' => 'Wahyu, do you not feel a little cold?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、少《すこ》し 寒《さむ》いです。', 'reading' => 'Ee, sukoshi samui desu.', 'id' => 'Ya, sedikit dingin.', 'en' => 'Yes, it is a little cold.'],
                            ['speaker' => 'サリ', 'ja' => 'エアコンを 少《すこ》し 弱《よわ》く しましょうか。', 'reading' => 'Eakon o sukoshi yowaku shimashou ka.', 'id' => 'Mau saya kecilkan sedikit AC-nya?', 'en' => 'Shall I turn the air conditioner down a bit?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'お願《ねが》いします。', 'reading' => 'Onegai shimasu.', 'id' => 'Tolong, ya.', 'en' => 'Yes, please.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'Ｎに します (memutuskan pilihan)',
                'title_en' => 'Ｎに します (deciding on a choice)',
                'pattern' => 'Ｎに します',
                'payload' => [
                    'explanation_id' => 'Menyatakan pilihan atau keputusan pembicara di antara beberapa kemungkinan: "Saya ambil ~", "Kita tentukan ~". Pola tanya: Ｎに しますか (mau yang mana?). Sering dipakai saat memesan makanan, memilih barang, atau menentukan waktu.',
                    'explanation_en' => 'States the speaker choice or decision among several possibilities: "I will have ~", "Let us settle on ~". Question form: Ｎに しますか (which one will you take?). Often used when ordering food, choosing goods, or fixing a date.',
                    'notes_id' => [
                        'Bedakan dari Ｎに なります (menjadi N): 先生《せんせい》に なります = menjadi guru, 先生《せんせい》に します = memilih/menetapkan guru.',
                        'Kata tanya どれ／何《なに》／いつ sering muncul di depannya: どれに しますか。',
                    ],
                    'notes_en' => [
                        'Do not confuse with Ｎに なります (become N): 先生《せんせい》に なります = become a teacher, 先生《せんせい》に します = pick or appoint a teacher.',
                        'The question words どれ／何《なに》／いつ often come before it: どれに しますか。',
                    ],
                    'examples' => [
                        ['ja' => '飲《の》み物《もの》は コーヒーに します。', 'reading' => 'Nomimono wa koohii ni shimasu.', 'id' => 'Untuk minuman, saya pilih kopi.', 'en' => 'For a drink, I will have coffee.'],
                        ['ja' => 'お昼《ひる》は 和食《わしょく》に しませんか。', 'reading' => 'Ohiru wa washoku ni shimasen ka.', 'id' => 'Bagaimana kalau makan siang dengan makanan Jepang?', 'en' => 'Shall we have Japanese food for lunch?'],
                        ['ja' => '旅行《りょこう》は 来月《らいげつ》に します。', 'reading' => 'Ryokou wa raigetsu ni shimasu.', 'id' => 'Perjalanannya saya tetapkan bulan depan.', 'en' => 'I will make the trip next month.'],
                        ['ja' => 'ケーキは どれに しますか。……いちごの ケーキに します。', 'reading' => 'Keeki wa dore ni shimasu ka. ...Ichigo no keeki ni shimasu.', 'id' => 'Kuenya mau yang mana? ……Saya pilih kue stroberi.', 'en' => 'Which cake will you have? ...I will have the strawberry cake.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Memilih makan siang',
                        'title_en' => 'Choosing lunch',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'お昼《ひる》は 何《なに》に しますか。', 'reading' => 'Ohiru wa nani ni shimasu ka.', 'id' => 'Makan siang mau pilih apa?', 'en' => 'What will you have for lunch?'],
                            ['speaker' => 'ワヒュ', 'ja' => '和食《わしょく》は どうですか。', 'reading' => 'Washoku wa dou desu ka.', 'id' => 'Bagaimana kalau makanan Jepang?', 'en' => 'How about Japanese food?'],
                            ['speaker' => 'たなか', 'ja' => 'いいですね。わたしは てんぷらに します。', 'reading' => 'Ii desu ne. Watashi wa tenpura ni shimasu.', 'id' => 'Boleh. Saya pilih tempura.', 'en' => 'Sounds good. I will have tempura.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'じゃ、わたしは さしみの 定食《ていしょく》に します。', 'reading' => 'Ja, watashi wa sashimi no teishoku ni shimasu.', 'id' => 'Kalau begitu, saya pilih paket sashimi.', 'en' => 'Then I will take the sashimi set meal.'],
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
            ['slug' => 'n4-pelajaran-19'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 19',
                'name_en' => 'N4 Lesson 19 Vocabulary',
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
            ['泣きます', 'なきます', 'nakimasu', 'menangis', 'to cry'],
            ['笑います', 'わらいます', 'waraimasu', 'tertawa', 'to laugh'],
            ['眠ります', 'ねむります', 'nemurimasu', 'tidur', 'to sleep'],
            ['乾きます', 'かわきます', 'kawakimasu', 'mengering [pakaian]', 'to dry [clothes]'],
            ['ぬれます', 'ぬれます', 'nuremasu', 'basah [pakaian]', 'to get wet [clothes]'],
            ['滑ります', 'すべります', 'subemasu', 'licin, tergelincir', 'to slip'],
            ['起きます', 'おきます', 'okimasu', 'terjadi [kecelakaan]', 'to happen [an accident]'],
            ['調節します', 'ちょうせつします', 'chousetsu shimasu', 'mengatur, mengontrol', 'to adjust, to control'],
            ['安全[な]', 'あんぜん[な]', 'anzen', 'aman', 'safe'],
            ['危険[な]', 'きけん[な]', 'kiken', 'berbahaya', 'dangerous'],
            ['濃い', 'こい', 'koi', 'kental, pekat (rasa), tua (warna)', 'strong (taste), dark (color)'],
            ['薄い', 'うすい', 'usui', 'tawar (rasa), muda (warna), tipis', 'weak (taste), light (color), thin'],
            ['厚い', 'あつい', 'atsui', 'tebal (benda pipih)', 'thick (flat things)'],
            ['太い', 'ふとい', 'futoi', 'gemuk, besar (benda bulat panjang)', 'fat, thick (round and long things)'],
            ['細い', 'ほそい', 'hosoi', 'kurus, ramping (benda bulat panjang)', 'thin, slender (round and long things)'],
            ['空気', 'くうき', 'kuuki', 'udara', 'air'],
            ['涙', 'なみだ', 'namida', 'air mata', 'tears'],
            ['和食', 'わしょく', 'washoku', 'makanan Jepang', 'Japanese food'],
            ['洋食', 'ようしょく', 'youshoku', 'makanan Barat', 'Western food'],
            ['おかず', 'おかず', 'okazu', 'lauk-pauk', 'side dish'],
            ['量', 'りょう', 'ryou', 'jumlah, kuantitas', 'amount, quantity'],
            ['〜倍', '〜ばい', 'bai', '~ kali (lipat)', '~ times'],
            ['シングル', 'シングル', 'shinguru', 'kamar single', 'single room'],
            ['ツイン', 'ツイン', 'tsuin', 'kamar twin', 'twin room'],
            ['洗濯物', 'せんたくもの', 'sentakumono', 'cucian', 'laundry'],
            ['DVD', 'ディーブイディー', 'dvd', 'DVD', 'DVD'],

            // 会話 (percakapan)
            ['どうなさいますか', 'どうなさいますか', 'dou nasaimasu ka', 'Mau dibuat bagaimana? (hormat)', 'How would you like it? (honorific)'],
            ['カット', 'カット', 'katto', 'potong rambut', 'haircut'],
            ['シャンプー', 'シャンプー', 'shanpuu', 'keramas, sampo', 'shampoo, hair wash'],
            ['どういうふうになさいますか', 'どういうふうになさいますか', 'dou iu fuu ni nasaimasu ka', 'Modelnya mau seperti apa? (hormat)', 'What style would you like? (honorific)'],
            ['ショート', 'ショート', 'shooto', 'pendek (model rambut)', 'short (hair style)'],
            ['〜みたいにしてください', '〜みたいにしてください', 'mitai ni shite kudasai', 'Tolong buat seperti ~', 'Please make it like ~'],
            ['これでよろしいでしょうか', 'これでよろしいでしょうか', 'kore de yoroshii deshou ka', 'Apakah begini sudah baik? (sopan)', 'Is this all right? (polite)'],
            ['お疲れさまでした', 'おつかれさまでした', 'otsukaresama deshita', 'Terima kasih atas kerja kerasnya, sudah selesai (kepada tamu)', 'Thank you for your patience, it is done (to a customer)'],

            // 読み物 (bacaan)
            ['嫌がります', 'いやがります', 'iyagarimasu', 'tidak mau, enggan', 'to be unwilling'],
            ['また', 'また', 'mata', 'dan, lalu, lagi', 'and, also, again'],
            ['うまく', 'うまく', 'umaku', 'dengan baik, pandai', 'well, skillfully'],
            ['順序', 'じゅんじょ', 'junjo', 'urutan', 'order, sequence'],
            ['安心[な]', 'あんしん[な]', 'anshin', 'lega, tenang', 'relieved, at ease'],
            ['表現', 'ひょうげん', 'hyougen', 'ungkapan, ekspresi', 'expression'],
            ['例えば', 'たとえば', 'tatoeba', 'misalnya', 'for example'],
            ['別れます', 'わかれます', 'wakaremasu', 'berpisah', 'to part, to separate'],
            ['これら', 'これら', 'korera', 'hal-hal ini', 'these'],
            ['縁起が悪い', 'えんぎがわるい', 'engi ga warui', 'pertanda buruk, tidak menyenangkan', 'inauspicious, bad omen'],
        ];
    }
}
