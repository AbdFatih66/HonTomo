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

class N4Lesson18Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 18 ("Kelihatannya Menyenangkan"): 〜そうです
     * (gejala dan kesan), Vて来ます, dan Vてくれませんか.
     *
     * Isi:
     *   - Level N4 (dibuat bila belum ada) + Unit order 18 ("Pelajaran 18")
     *   - Lesson Bunpou (order 0, category grammar) dengan 5 kartu:
     *       1. Vます形 + そうです       4. N へ 行って来ます／出かけて来ます
     *       2. Aい／Aな + そうです      5. Vて くれませんか
     *       3. Vて 来ます
     *   - Kategori kosakata "n4-pelajaran-18" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus. Penjelasan, contoh
     * kalimat dan dialog ditulis baru untuk aplikasi ini.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson18Seeder
     *
     * Kunci pencarian SQL memakai ASCII (kategori.slug + romaji).
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
            ['level_id' => $level->id, 'order' => 18],
            [
                'title_id' => 'Pelajaran 18: Kelihatannya Menyenangkan',
                'title_en' => 'Lesson 18: It Looks Fun',
                'description_id' => '〜そうです (gejala dan kesan), 〜て 来ます, 〜て くれませんか.',
                'description_en' => '〜そうです (signs and impressions), 〜て 来ます, 〜て くれませんか.',
                'icon' => 'mdi-emoticon-happy-outline',
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
                'title_id' => 'Kata kerja + そうです (gejala akan terjadi)',
                'title_en' => 'Verb + そうです (about to happen)',
                'pattern' => 'Ｖ ます形《けい》（ます なし）＋ そうです',
                'payload' => [
                    'explanation_id' => 'Bentuk ます tanpa ます + そうです menyatakan bahwa ada tanda-tanda sebuah gerakan atau perubahan akan segera terjadi: "kelihatannya mau …", "sepertinya akan …". Sering dipakai bersama kata keterangan waktu seperti 今《いま》にも (sekarang juga), もうすぐ (sebentar lagi), dan これから (mulai sekarang).',
                    'explanation_en' => 'The ます-form stem + そうです says there are signs that an action or change is about to happen: "it looks like it is going to …". It is often used with time adverbs such as 今《いま》にも (any moment now), もうすぐ (soon) and これから (from now on).',
                    'notes_id' => [
                        'Kata kerja なります juga bisa: 寒《さむ》く なります → 寒《さむ》く なりそうです (sepertinya akan jadi dingin).',
                        'Dasarnya adalah penilaian dari penampilan luar, bukan dari informasi yang didengar.',
                    ],
                    'notes_en' => [
                        'なります works too: 寒《さむ》く なります → 寒《さむ》く なりそうです (it looks like it will get cold).',
                        'The judgement is based on outward appearance, not on information you heard.',
                    ],
                    'examples' => [
                        ['ja' => '空《そら》が 暗《くら》いですね。今《いま》にも 雨《あめ》が 降《ふ》りそうです。', 'reading' => 'Sora ga kurai desu ne. Ima ni mo ame ga furisou desu.', 'id' => 'Langitnya gelap, ya. Sepertinya sebentar lagi hujan.', 'en' => 'The sky is dark. It looks like it will rain any moment.'],
                        ['ja' => 'この ボタンは 取《と》れそうです。', 'reading' => 'Kono botan wa toresou desu.', 'id' => 'Kancing ini hampir lepas.', 'en' => 'This button looks like it is about to come off.'],
                        ['ja' => '荷物《にもつ》が 落《お》ちそうですよ。', 'reading' => 'Nimotsu ga ochisou desu yo.', 'id' => 'Barangnya hampir jatuh, lho.', 'en' => 'Your luggage is about to fall.'],
                        ['ja' => 'ガソリンが なくなりそうです。', 'reading' => 'Gasorin ga nakunarisou desu.', 'id' => 'Bensinnya sepertinya mau habis.', 'en' => 'We seem to be running out of petrol.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di jalan',
                        'title_en' => 'On the road',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'たなかさん、ガソリンが なくなりそうです。', 'reading' => 'Tanaka-san, gasorin ga nakunarisou desu.', 'id' => 'Pak Tanaka, bensinnya sepertinya mau habis.', 'en' => 'Mr. Tanaka, we seem to be running out of petrol.'],
                            ['speaker' => 'たなか', 'ja' => 'えっ、本当《ほんとう》ですか。じゃ、次《つぎ》の スタンドで 入《い》れましょう。', 'reading' => 'E, hontou desu ka. Ja, tsugi no sutando de iremashou.', 'id' => 'Eh, benarkah? Kalau begitu, isi di pom bensin berikutnya.', 'en' => 'Oh, really? Then let us fill up at the next station.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => 'Kata sifat + そうです (kesan dari penampilan)',
                'title_en' => 'Adjective + そうです (impression from appearance)',
                'pattern' => 'Ａい（い なし）／Ａな（な なし）＋ そうです',
                'payload' => [
                    'explanation_id' => 'Dipakai untuk menyatakan kesan atau dugaan tentang sifat sesuatu berdasarkan penampilan luar, tanpa memastikannya: "kelihatannya …". Kata sifat い: buang い (辛《から》い → 辛《から》そうです). Kata sifat な: buang な (丈夫《じょうぶ》な → 丈夫《じょうぶ》そうです). Pengecualian: いい → よさそうです, ない → なさそうです.',
                    'explanation_en' => 'Used to give an impression or guess about a quality based on how something looks, without confirming it: "it looks …". い-adjectives: drop い (辛《から》い → 辛《から》そうです). な-adjectives: drop な (丈夫《じょうぶ》な → 丈夫《じょうぶ》そうです). Exceptions: いい → よさそうです, ない → なさそうです.',
                    'notes_id' => [
                        'Perasaan orang lain (うれしい, かなしい, さびしい) tidak boleh dikatakan langsung dengan bentuk biasa. Pakai そうです: うれしそうですね (dia kelihatan senang).',
                        'Untuk menanyakan kesan: どんな 感《かん》じですか。',
                    ],
                    'notes_en' => [
                        'You cannot state the feelings of another person (うれしい, かなしい, さびしい) directly in plain form. Use そうです: うれしそうですね (they look happy).',
                        'To ask for an impression: どんな 感《かん》じですか。',
                    ],
                    'examples' => [
                        ['ja' => 'この スープは おいしそうです。', 'reading' => 'Kono suupu wa oishisou desu.', 'id' => 'Sup ini kelihatannya enak.', 'en' => 'This soup looks delicious.'],
                        ['ja' => '彼女《かのじょ》は 頭《あたま》が よさそうです。', 'reading' => 'Kanojo wa atama ga yosasou desu.', 'id' => 'Dia kelihatannya pintar.', 'en' => 'She looks smart.'],
                        ['ja' => 'この いすは 丈夫《じょうぶ》そうです。', 'reading' => 'Kono isu wa joubu sou desu.', 'id' => 'Kursi ini kelihatannya kuat.', 'en' => 'This chair looks sturdy.'],
                        ['ja' => 'きょうは 暇《ひま》そうですね。', 'reading' => 'Kyou wa hima sou desu ne.', 'id' => 'Hari ini kamu kelihatan santai, ya.', 'en' => 'You look free today.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Kue dari teman',
                        'title_en' => 'A cake from a friend',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'どうぞ。ケーキを 作《つく》りました。', 'reading' => 'Douzo. Keeki o tsukurimashita.', 'id' => 'Silakan. Saya membuat kue.', 'en' => 'Please. I made a cake.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'わあ、おいしそうですね。いただきます。', 'reading' => 'Waa, oishisou desu ne. Itadakimasu.', 'id' => 'Wah, kelihatannya enak. Saya makan, ya.', 'en' => 'Wow, it looks delicious. Let me have some.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => 'Ｖて 来ます (pergi, melakukan, lalu kembali)',
                'title_en' => 'Ｖて 来ます (go, do, and come back)',
                'pattern' => 'Ｖ て形《けい》＋ 来《き》ます',
                'payload' => [
                    'explanation_id' => 'Kata kerja bentuk て + 来《き》ます menyatakan tiga gerakan sekaligus: (1) pergi ke suatu tempat, (2) melakukan sesuatu di sana, (3) kembali ke tempat semula. Tempat berlangsungnya aksi ditandai で; kalau tempat itu dianggap sebagai asal sebuah benda yang diambil, dipakai から. Kata kerja lain yang sering dipakai dengan から: 持《も》って 来《き》ます, 運《はこ》んで 来《き》ます.',
                    'explanation_en' => 'Verb て-form + 来《き》ます expresses three movements at once: (1) go to a place, (2) do something there, (3) return to the starting point. The place where the action happens is marked with で; when the place is regarded as the source of an object you take, から is used. Other verbs often used with から: 持《も》って 来《き》ます, 運《はこ》んで 来《き》ます.',
                    'notes_id' => [
                        '来《き》ます di sini berarti "kembali ke tempat pembicara", bukan "datang" biasa.',
                        'Bukan hanya membeli: 見《み》て 来《き》ます (pergi melihat dan kembali), 食《た》べて 来《き》ます (makan dulu lalu kembali).',
                    ],
                    'notes_en' => [
                        '来《き》ます here means "come back to where the speaker is", not plain "come".',
                        'Not only for buying: 見《み》て 来《き》ます (go and have a look, then return), 食《た》べて 来《き》ます (eat first and come back).',
                    ],
                    'examples' => [
                        ['ja' => 'コンビニで 新聞《しんぶん》を 買《か》って 来《き》ます。', 'reading' => 'Konbini de shinbun o katte kimasu.', 'id' => 'Saya pergi membeli koran di minimarket, lalu kembali.', 'en' => 'I will go and buy a newspaper at the convenience store and come back.'],
                        ['ja' => 'となりの 部屋《へや》から いすを 運《はこ》んで 来《き》ます。', 'reading' => 'Tonari no heya kara isu o hakonde kimasu.', 'id' => 'Saya mengambil kursi dari ruangan sebelah.', 'en' => 'I will fetch a chair from the next room.'],
                        ['ja' => 'かばんから 辞書《じしょ》を 持《も》って 来《き》ます。', 'reading' => 'Kaban kara jisho o motte kimasu.', 'id' => 'Saya ambil kamus dari tas, sebentar.', 'en' => 'I will get a dictionary out of my bag.'],
                        ['ja' => '会議室《かいぎしつ》を 見《み》て 来《き》ます。', 'reading' => 'Kaigishitsu o mite kimasu.', 'id' => 'Saya cek ruang rapat dulu.', 'en' => 'I will go and check the meeting room.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Sebelum rapat',
                        'title_en' => 'Before the meeting',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'しりょうが 足《た》りませんね。', 'reading' => 'Shiryou ga tarimasen ne.', 'id' => 'Materinya kurang, ya.', 'en' => 'We are short of handouts.'],
                            ['speaker' => 'ワヒュ', 'ja' => '事務所《じむしょ》から 持《も》って 来《き》ます。', 'reading' => 'Jimusho kara motte kimasu.', 'id' => 'Saya ambil dari kantor.', 'en' => 'I will bring some from the office.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'N へ 行って 来ます／出かけて 来ます',
                'title_en' => 'N へ 行って 来ます／出かけて 来ます',
                'pattern' => 'Ｎ（ばしょ）へ 行《い》って 来《き》ます／出《で》かけて 来《き》ます',
                'payload' => [
                    'explanation_id' => '行《い》って 来《き》ます berarti pergi ke suatu tempat lalu kembali. Dipakai bila aksi di tempat itu tidak perlu disebutkan, misalnya kantor pos atau bank. 出《で》かけて 来《き》ます dipakai bila tempat dan tujuannya tidak ingin disebutkan: "keluar sebentar". Orang di rumah atau kantor biasanya menjawab いってらっしゃい; yang pergi berpamitan dengan いってきます.',
                    'explanation_en' => '行《い》って 来《き》ます means go somewhere and come back. It is used when the action at the place need not be stated, such as the post office or bank. 出《で》かけて 来《き》ます is used when you do not wish to say where or why: "I am popping out". The person staying behind replies いってらっしゃい; the one leaving says いってきます.',
                    'notes_id' => [
                        'Pamitan harian: いってきます ／ いってらっしゃい. Sepulang: ただいま ／ おかえりなさい.',
                        'Bentuk sopan di kantor: ちょっと 出《で》かけて まいります.',
                    ],
                    'notes_en' => [
                        'Daily exchange: いってきます ／ いってらっしゃい. On return: ただいま ／ おかえりなさい.',
                        'Polite form at work: ちょっと 出《で》かけて まいります.',
                    ],
                    'examples' => [
                        ['ja' => '銀行《ぎんこう》へ 行《い》って 来《き》ます。', 'reading' => 'Ginkou e itte kimasu.', 'id' => 'Saya ke bank dulu.', 'en' => 'I will go to the bank and come back.'],
                        ['ja' => '駅《えき》まで 行《い》って 来《き》ます。', 'reading' => 'Eki made itte kimasu.', 'id' => 'Saya pergi sampai stasiun lalu kembali.', 'en' => 'I will go to the station and back.'],
                        ['ja' => 'ちょっと 出《で》かけて 来《き》ます。', 'reading' => 'Chotto dekakete kimasu.', 'id' => 'Saya keluar sebentar.', 'en' => 'I will pop out for a bit.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Keluar sebentar',
                        'title_en' => 'Popping out',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'ちょっと 出《で》かけて 来《き》ます。', 'reading' => 'Chotto dekakete kimasu.', 'id' => 'Saya keluar sebentar.', 'en' => 'I am popping out for a bit.'],
                            ['speaker' => 'たなか', 'ja' => 'いってらっしゃい。何時《なんじ》ごろ 戻《もど》りますか。', 'reading' => 'Itterasshai. Nanji goro modorimasu ka.', 'id' => 'Hati-hati. Kira-kira jam berapa kembali?', 'en' => 'See you later. About what time will you be back?'],
                            ['speaker' => 'ワヒュ', 'ja' => '四時《よじ》までに 戻《もど》ります。', 'reading' => 'Yoji made ni modorimasu.', 'id' => 'Saya kembali sebelum jam empat.', 'en' => 'I will be back by four.'],
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => 'Ｖて くれませんか (meminta tolong secara halus)',
                'title_en' => 'Ｖて くれませんか (a gentle request)',
                'pattern' => 'Ｖ て形《けい》＋ くれませんか',
                'payload' => [
                    'explanation_id' => 'Permintaan yang lebih halus daripada 〜て ください, tetapi kurang halus daripada 〜て いただけませんか atau 〜て くださいませんか. Cocok dipakai kepada orang yang setara atau di bawah kita, seperti teman, rekan sebaya, atau adik kelas. Bisa digabung dengan 〜て 来《き》て: 買《か》って 来《き》て くれませんか (tolong belikan).',
                    'explanation_en' => 'A request gentler than 〜て ください, but less polite than 〜て いただけませんか or 〜て くださいませんか. It suits people of equal or lower standing, such as friends, same-level colleagues or juniors. It combines with 〜て 来《き》て: 買《か》って 来《き》て くれませんか (could you buy it for me).',
                    'notes_id' => [
                        'Jangan dipakai kepada atasan atau orang yang baru dikenal; pakai 〜て いただけませんか.',
                        'Jawaban setuju: いいですよ／はい、わかりました.',
                    ],
                    'notes_en' => [
                        'Do not use it to a boss or someone you have just met; use 〜て いただけませんか.',
                        'Agreeing: いいですよ／はい、わかりました.',
                    ],
                    'examples' => [
                        ['ja' => 'この 荷物《にもつ》を 運《はこ》んで くれませんか。', 'reading' => 'Kono nimotsu o hakonde kuremasen ka.', 'id' => 'Bisakah kamu membawakan barang ini?', 'en' => 'Could you carry this luggage?'],
                        ['ja' => 'ちょっと 手伝《てつだ》って くれませんか。', 'reading' => 'Chotto tetsudatte kuremasen ka.', 'id' => 'Bisa tolong bantu sebentar?', 'en' => 'Could you give me a hand for a moment?'],
                        ['ja' => 'パンを 買《か》って 来《き》て くれませんか。', 'reading' => 'Pan o katte kite kuremasen ka.', 'id' => 'Bisa tolong belikan roti?', 'en' => 'Could you buy some bread for me?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Titip beli bekal',
                        'title_en' => 'Asking for lunch to be bought',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'コンビニへ 行《い》って 来《き》ます。', 'reading' => 'Konbini e itte kimasu.', 'id' => 'Saya ke minimarket dulu.', 'en' => 'I am going to the convenience store.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'じゃ、お弁当《べんとう》を 買《か》って 来《き》て くれませんか。', 'reading' => 'Ja, obentou o katte kite kuremasen ka.', 'id' => 'Kalau begitu, tolong belikan bekal, ya.', 'en' => 'In that case, could you buy me a boxed lunch?'],
                            ['speaker' => 'サリ', 'ja' => 'いいですよ。', 'reading' => 'Ii desu yo.', 'id' => 'Boleh.', 'en' => 'Sure.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function seedVocabulary(): void
    {
        $category = VocabularyCategory::updateOrCreate(
            ['slug' => 'n4-pelajaran-18'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 18',
                'name_en' => 'N4 Lesson 18 Vocabulary',
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
     *
     * @return array<int, array<int, string>>
     */
    private function words(): array
    {
        return [
            ['増えます', 'ふえます', 'fuemasu', '[ekspor] bertambah', '[exports] increase'],
            ['減ります', 'へります', 'herimasu', '[ekspor] berkurang', '[exports] decrease'],
            ['上がります', 'あがります', 'agarimasu', '[harga] meningkat', '[prices] go up'],
            ['下がります', 'さがります', 'sagarimasu', '[harga] menurun', '[prices] go down'],
            ['切れます', 'きれます', 'kiremasu', '[tali] terputus', '[a string] breaks'],
            ['取れます', 'とれます', 'toremasu', '[kancing] terlepas', '[a button] comes off'],
            ['落ちます', 'おちます', 'ochimasu', '[barang] terjatuh', '[a thing] falls'],
            ['なくなります', 'なくなります', 'nakunarimasu', '[bensin] habis', '[petrol] runs out'],
            ['変[な]', 'へん[な]', 'hen', 'aneh', 'strange'],
            ['幸せ[な]', 'しあわせ[な]', 'shiawase', 'bahagia', 'happy'],
            ['楽[な]', 'らく[な]', 'raku', 'ringan, mudah', 'easy, comfortable'],
            ['うまい', 'うまい', 'umai', 'enak, lezat', 'delicious'],
            ['まずい', 'まずい', 'mazui', 'tidak enak, tidak sedap', 'bad-tasting'],
            ['つまらない', 'つまらない', 'tsumaranai', 'tidak menarik, tidak berguna, percuma', 'boring, worthless'],
            ['優しい', 'やさしい', 'yasashii', 'baik hati', 'kind, gentle'],
            ['ガソリン', 'ガソリン', 'gasorin', 'bensin', 'petrol, gasoline'],
            ['火', 'ひ', 'hi', 'api', 'fire'],
            ['パンフレット', 'パンフレット', 'panfuretto', 'pamflet', 'pamphlet'],
            ['今にも', 'いまにも', 'ima ni mo', 'sekarang juga (untuk kondisi yang persis akan berubah)', 'any moment now'],
            ['わあ', 'わあ', 'waa', 'Wah!', 'Wow!'],

            // 読み物 (bacaan)
            ['ばら', 'ばら', 'bara', 'bunga mawar', 'rose'],
            ['ドライブ', 'ドライブ', 'doraibu', 'berjalan-jalan dengan mobil', 'drive'],
            ['理由', 'りゆう', 'riyuu', 'alasan', 'reason'],
            ['謝ります', 'あやまります', 'ayamarimasu', 'memohon maaf', 'to apologize'],
            ['知り合います', 'しりあいます', 'shiriaimasu', 'berkenalan', 'to get acquainted'],
        ];
    }
}
