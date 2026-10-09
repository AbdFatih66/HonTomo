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

class N4Lesson25Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 25 ("Mengucapkan Terima Kasih dari Hati"):
     * ungkapan merendahkan diri (謙譲語 I dan II).
     *
     * Isi:
     *   - Level N4 (dibuat bila belum ada) + Unit order 25 ("Pelajaran 25")
     *   - Lesson Bunpou (order 0, category grammar) dengan 4 kartu:
     *       1. お＋Vます形＋します       3. Kata kerja merendahkan diri khusus
     *       2. ご＋N＋します             4. 謙譲語II (申します, 参ります, いたします, 〜ております)
     *   - Kategori kosakata "n4-pelajaran-25" + daftar kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis (VocabularyQuizSync::run('N4'))
     *
     * Nama diri (museum, kota) sengaja tidak dimasukkan ke daftar kata.
     * Penjelasan, contoh kalimat dan dialog ditulis baru untuk aplikasi ini.
     *
     * Aman dijalankan berulang:
     *   php artisan db:seed --class=N4Lesson25Seeder
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
            ['level_id' => $level->id, 'order' => 25],
            [
                'title_id' => 'Pelajaran 25: Terima Kasih dari Hati',
                'title_en' => 'Lesson 25: Thanks from the Heart',
                'description_id' => 'Ungkapan merendahkan diri (謙譲語): お〜します, ご〜します, 参ります, 申します, いたします.',
                'description_en' => 'Humble language (謙譲語): お〜します, ご〜します, 参ります, 申します, いたします.',
                'icon' => 'mdi-hand-heart-outline',
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
                    'difficulty' => 3,
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
                'title_id' => 'お＋Vます形＋します (merendahkan diri I)',
                'title_en' => 'お＋Vます stem＋します (humble I)',
                'pattern' => 'お ＋ Ｖ ます形《けい》（ます なし）＋ します',
                'payload' => [
                    'explanation_id' => 'Ungkapan merendahkan diri (謙譲語《けんじょうご》) menurunkan tindakan pembicara atau pihak pembicara untuk menunjukkan hormat kepada orang yang menjadi sasaran tindakan itu, misalnya orang yang barangnya dibawa, orang yang diberi tahu, atau orang yang diantar. Untuk kata kerja Kelompok I dan II, bentuknya お＋bentuk ます tanpa ます＋します: 持《も》ちます → お持《も》ちします, 送《おく》ります → お送《おく》りします, 知《し》らせます → お知《し》らせします.',
                    'explanation_en' => 'Humble language (謙譲語《けんじょうご》) lowers the action of the speaker or their side to show respect to the person the action is directed at, such as the person whose bag is carried, who is informed, or who is escorted. For Group I and II verbs the form is お＋ます-form stem＋します: 持《も》ちます → お持《も》ちします, 送《おく》ります → お送《おく》りします, 知《し》らせます → お知《し》らせします.',
                    'notes_id' => [
                        'Pola ini tidak bisa dipakai untuk kata kerja yang bagian ます-nya hanya satu suku kata, seperti 見《み》ます, 居《い》ます, 着《き》ます. Kata-kata itu punya bentuk khusus (lihat kartu 3).',
                        'Yang direndahkan adalah tindakanmu sendiri. Jangan dipakai untuk tindakan orang yang kamu hormati; untuk itu ada bentuk menghormati (尊敬語).',
                        'Tawaran sopan: お持《も》ちしましょうか (maukah saya bawakan?).',
                    ],
                    'notes_en' => [
                        'This pattern cannot be used for verbs whose ます-stem is a single syllable, such as 見《み》ます, 居《い》ます, 着《き》ます. They have special forms (see card 3).',
                        'It lowers your own action. Never use it for the actions of a person you respect; that is the job of honorific language (尊敬語).',
                        'A polite offer: お持《も》ちしましょうか (shall I carry it for you?).',
                    ],
                    'examples' => [
                        ['ja' => 'その 荷物《にもつ》、お持《も》ちしましょうか。', 'reading' => 'Sono nimotsu, omochi shimashou ka.', 'id' => 'Barang itu, maukah saya bawakan?', 'en' => 'Shall I carry that luggage for you?'],
                        ['ja' => '私《わたし》が 先生《せんせい》に 資料《しりょう》を お渡《わた》しします。', 'reading' => 'Watashi ga sensei ni shiryou o owatashi shimasu.', 'id' => 'Saya yang akan menyerahkan materi kepada guru.', 'en' => 'I will hand the materials to the teacher.'],
                        ['ja' => '駅《えき》まで お送《おく》りします。', 'reading' => 'Eki made ookuri shimasu.', 'id' => 'Saya antarkan sampai stasiun.', 'en' => 'I will see you to the station.'],
                        ['ja' => '来週《らいしゅう》の 予定《よてい》を お知《し》らせします。', 'reading' => 'Raishuu no yotei o oshirase shimasu.', 'id' => 'Saya sampaikan jadwal minggu depan.', 'en' => 'I will let you know the schedule for next week.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di stasiun',
                        'title_en' => 'At the station',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '重《おも》そうですね。お持《も》ちしましょうか。', 'reading' => 'Omosou desu ne. Omochi shimashou ka.', 'id' => 'Kelihatannya berat, ya. Mari saya bawakan.', 'en' => 'It looks heavy. Shall I carry it?'],
                            ['speaker' => 'おきゃくさん', 'ja' => 'すみません。お願《ねが》いします。', 'reading' => 'Sumimasen. Onegai shimasu.', 'id' => 'Maaf merepotkan. Tolong, ya.', 'en' => 'Sorry to trouble you. Please.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => 'ご＋N＋します (kata kerja Kelompok III)',
                'title_en' => 'ご＋N＋します (Group III verbs)',
                'pattern' => 'ご ＋ Ｎ（する動詞《どうし》）＋ します',
                'payload' => [
                    'explanation_id' => 'Untuk kata kerja Kelompok III berbentuk kata benda + します, awalan ご dipasang di depan kata bendanya: 案内《あんない》します → ご案内《あんない》します, 説明《せつめい》します → ご説明《せつめい》します, 紹介《しょうかい》します → ご紹介《しょうかい》します, 招待《しょうたい》します → ご招待《しょうたい》します, 相談《そうだん》します → ご相談《そうだん》します, 連絡《れんらく》します → ご連絡《れんらく》します.',
                    'explanation_en' => 'For Group III verbs made of a noun + します, the prefix ご goes before the noun: 案内《あんない》します → ご案内《あんない》します, 説明《せつめい》します → ご説明《せつめい》します, 紹介《しょうかい》します → ご紹介《しょうかい》します, 招待《しょうたい》します → ご招待《しょうたい》します, 相談《そうだん》します → ご相談《そうだん》します, 連絡《れんらく》します → ご連絡《れんらく》します.',
                    'notes_id' => [
                        'Pengecualian: 電話《でんわ》します, 約束《やくそく》します memakai お, bukan ご: お電話《でんわ》します, お約束《やくそく》します.',
                        'Dengan kata benda yang bacaannya berasal dari bahasa Jepang asli (wago) dipakai お; dengan bacaan Tionghoa (on-yomi) biasanya ご. Pengecualian di atas harus dihafal.',
                    ],
                    'notes_en' => [
                        'Exceptions: 電話《でんわ》します and 約束《やくそく》します take お, not ご: お電話《でんわ》します, お約束《やくそく》します.',
                        'Native Japanese (wago) nouns take お; Sino-Japanese (on-yomi) nouns usually take ご. The exceptions above must be memorised.',
                    ],
                    'examples' => [
                        ['ja' => '市内《しない》を ご案内《あんない》します。', 'reading' => 'Shinai o goannai shimasu.', 'id' => 'Saya pandu Anda keliling kota.', 'en' => 'I will show you around the city.'],
                        ['ja' => '今日《きょう》の 予定《よてい》を ご説明《せつめい》します。', 'reading' => 'Kyou no yotei o gosetsumei shimasu.', 'id' => 'Saya jelaskan jadwal hari ini.', 'en' => 'I will explain the schedule for today.'],
                        ['ja' => '先生《せんせい》に 友《とも》だちを ご紹介《しょうかい》します。', 'reading' => 'Sensei ni tomodachi o goshoukai shimasu.', 'id' => 'Saya perkenalkan teman saya kepada guru.', 'en' => 'I will introduce my friend to the teacher.'],
                        ['ja' => 'あとで お電話《でんわ》します。', 'reading' => 'Ato de odenwa shimasu.', 'id' => 'Nanti saya telepon.', 'en' => 'I will call you later.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Pemandu dan tamu',
                        'title_en' => 'Guide and guest',
                        'lines' => [
                            ['speaker' => 'ガイド', 'ja' => 'まず、きょうの 予定《よてい》を ご説明《せつめい》します。', 'reading' => 'Mazu, kyou no yotei o gosetsumei shimasu.', 'id' => 'Pertama, saya jelaskan jadwal hari ini.', 'en' => 'First, I will explain the schedule for today.'],
                            ['speaker' => 'おきゃくさん', 'ja' => 'お願《ねが》いします。', 'reading' => 'Onegai shimasu.', 'id' => 'Silakan.', 'en' => 'Please do.'],
                            ['speaker' => 'ガイド', 'ja' => 'そのあと、古《ふる》い お寺《てら》へ ご案内《あんない》します。', 'reading' => 'Sono ato, furui otera e goannai shimasu.', 'id' => 'Setelah itu, saya antar Anda ke kuil tua.', 'en' => 'After that, I will take you to an old temple.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => 'Kata kerja merendahkan diri khusus',
                'title_en' => 'Special humble verbs',
                'pattern' => '参《まい》ります／おります／いただきます／伺《うかが》います…',
                'payload' => [
                    'explanation_id' => 'Sebagian kata kerja punya bentuk merendahkan diri sendiri. Pasangan pentingnya: いきます／きます → 参《まい》ります; います → おります; たべます／のみます／もらいます → いただきます; いいます → 申《もう》します; します → いたします; みます → 拝見《はいけん》します; しります → 存《ぞん》じます; ききます／いきます (berkunjung) → 伺《うかが》います; あいます → お目《め》に かかります; わたし → 私《わたくし》.',
                    'explanation_en' => 'Some verbs have their own humble forms. Key pairs: いきます／きます → 参《まい》ります; います → おります; たべます／のみます／もらいます → いただきます; いいます → 申《もう》します; します → いたします; みます → 拝見《はいけん》します; しります → 存《ぞん》じます; ききます／いきます (to visit) → 伺《うかが》います; あいます → お目《め》に かかります; わたし → 私《わたくし》.',
                    'notes_id' => [
                        'Bentuk khusus lebih utama daripada pola お〜します. Kamu tidak mengatakan ×お見《み》えします untuk みます; yang benar 拝見《はいけん》します.',
                        '伺《うかが》います bisa berarti "bertanya", "mendengar" atau "berkunjung" tergantung konteks.',
                        'Untuk "saya tahu" lebih sering dipakai 存《ぞん》じて おります; untuk "tidak tahu" 存《ぞん》じません.',
                    ],
                    'notes_en' => [
                        'The special forms take priority over the お〜します pattern. You do not say ×お見《み》えします for みます; the correct form is 拝見《はいけん》します.',
                        '伺《うかが》います can mean "to ask", "to hear" or "to visit" depending on context.',
                        'For "I know" 存《ぞん》じて おります is more common; for "I do not know" 存《ぞん》じません.',
                    ],
                    'examples' => [
                        ['ja' => 'あしたの 三時《さんじ》に 伺《うかが》います。', 'reading' => 'Ashita no sanji ni ukagaimasu.', 'id' => 'Besok jam tiga saya berkunjung.', 'en' => 'I will visit at three tomorrow.'],
                        ['ja' => '先生《せんせい》に お目《め》に かかりたいです。', 'reading' => 'Sensei ni ome ni kakaritai desu.', 'id' => 'Saya ingin bertemu dengan guru.', 'en' => 'I would like to meet the teacher.'],
                        ['ja' => '資料《しりょう》を 拝見《はいけん》しました。', 'reading' => 'Shiryou o haiken shimashita.', 'id' => 'Saya sudah melihat materinya.', 'en' => 'I have looked at the materials.'],
                        ['ja' => 'では、コーヒーを いただきます。', 'reading' => 'Dewa, koohii o itadakimasu.', 'id' => 'Kalau begitu, saya minum kopinya.', 'en' => 'Then I will have the coffee.'],
                        ['ja' => 'その 話《はなし》は 存《ぞん》じて おります。', 'reading' => 'Sono hanashi wa zonjite orimasu.', 'id' => 'Saya mengetahui hal itu.', 'en' => 'I am aware of that matter.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Janji bertemu',
                        'title_en' => 'Arranging a meeting',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'あした、そちらへ 伺《うかが》っても よろしいでしょうか。', 'reading' => 'Ashita, sochira e ukagatte mo yoroshii deshou ka.', 'id' => 'Besok, bolehkah saya berkunjung ke tempat Anda?', 'en' => 'May I visit you tomorrow?'],
                            ['speaker' => 'たなか', 'ja' => 'はい、お待《ま》ちして おります。', 'reading' => 'Hai, omachi shite orimasu.', 'id' => 'Baik, saya tunggu kedatangan Anda.', 'en' => 'Yes, I will be waiting for you.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => '謙譲語II (申します・参ります・いたします・〜ております)',
                'title_en' => '謙譲語II (申します, 参ります, いたします, 〜ております)',
                'pattern' => '申《もう》します／参《まい》りました／いたします／〜て おります',
                'payload' => [
                    'explanation_id' => '謙譲語II dipakai untuk menjelaskan tindakan pembicara atau pihak pembicara kepada lawan bicara secara halus, tanpa orang tertentu yang menjadi sasaran tindakan itu. Contoh: 私《わたし》は ワヒュと 申《もう》します (pengganti いいます), インドネシアから 参《まい》りました (pengganti きました). Yang lain: いたします (pengganti します) dan 〜て おります (pengganti 〜て います).',
                    'explanation_en' => '謙譲語II describes the actions of the speaker or their side politely to the listener, with no particular person being the target of the action. Examples: 私《わたし》は ワヒュと 申《もう》します (instead of いいます), インドネシアから 参《まい》りました (instead of きました). Others: いたします (instead of します) and 〜て おります (instead of 〜て います).',
                    'notes_id' => [
                        'Perbedaan dengan 謙譲語I: 謙譲語I merendahkan tindakan demi menghormati orang yang menjadi sasaran (yang diantar, yang diberi tahu). 謙譲語II hanya bersikap sopan kepada pendengar.',
                        'Sering muncul di pidato, perkenalan diri, dan telepon bisnis.',
                    ],
                    'notes_en' => [
                        'Difference from 謙譲語I: 謙譲語I lowers the action to honour the person it is directed at (the one escorted or informed). 謙譲語II is simply being courteous to the listener.',
                        'Common in speeches, self-introductions and business phone calls.',
                    ],
                    'examples' => [
                        ['ja' => '私《わたし》は ワヒュと 申《もう》します。', 'reading' => 'Watakushi wa Wahyu to moushimasu.', 'id' => 'Nama saya Wahyu.', 'en' => 'My name is Wahyu.'],
                        ['ja' => 'インドネシアから 参《まい》りました。', 'reading' => 'Indoneshia kara mairimashita.', 'id' => 'Saya datang dari Indonesia.', 'en' => 'I come from Indonesia.'],
                        ['ja' => '今《いま》、日本語《にほんご》を 勉強《べんきょう》して おります。', 'reading' => 'Ima, nihongo o benkyou shite orimasu.', 'id' => 'Sekarang saya sedang belajar bahasa Jepang.', 'en' => 'I am currently studying Japanese.'],
                        ['ja' => 'あしたは 私《わたし》が いたします。', 'reading' => 'Ashita wa watashi ga itashimasu.', 'id' => 'Besok saya yang mengerjakannya.', 'en' => 'I will do it tomorrow.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Perkenalan di acara',
                        'title_en' => 'Introduction at an event',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'はじめまして。ワヒュと 申《もう》します。インドネシアから 参《まい》りました。', 'reading' => 'Hajimemashite. Wahyu to moushimasu. Indoneshia kara mairimashita.', 'id' => 'Salam kenal. Saya Wahyu. Saya datang dari Indonesia.', 'en' => 'Nice to meet you. I am Wahyu. I come from Indonesia.'],
                            ['speaker' => 'たなか', 'ja' => 'たなかです。よろしく お願《ねが》いします。', 'reading' => 'Tanaka desu. Yoroshiku onegai shimasu.', 'id' => 'Saya Tanaka. Mohon bantuannya.', 'en' => 'I am Tanaka. Pleased to meet you.'],
                            ['speaker' => 'ワヒュ', 'ja' => '今《いま》、工場《こうじょう》で 働《はたら》いて おります。', 'reading' => 'Ima, koujou de hataraite orimasu.', 'id' => 'Sekarang saya bekerja di pabrik.', 'en' => 'I am working at a factory now.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function seedVocabulary(): void
    {
        $category = VocabularyCategory::updateOrCreate(
            ['slug' => 'n4-pelajaran-25'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 25',
                'name_en' => 'N4 Lesson 25 Vocabulary',
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
            ['参ります', 'まいります', 'mairimasu', 'pergi, datang (merendahkan diri dari いきます, きます)', 'to go, to come (humble)'],
            ['おります', 'おります', 'orimasu', 'ada (merendahkan diri dari います)', 'to be (humble)'],
            ['いただきます', 'いただきます', 'itadakimasu', 'makan, minum, menerima (merendahkan diri)', 'to eat, drink, receive (humble)'],
            ['申します', 'もうします', 'moushimasu', 'bernama (merendahkan diri dari いいます)', 'to say, to be called (humble)'],
            ['いたします', 'いたします', 'itashimasu', 'melakukan (merendahkan diri dari します)', 'to do (humble)'],
            ['拝見します', 'はいけんします', 'haikenshimasu', 'melihat (merendahkan diri dari みます)', 'to see, to look at (humble)'],
            ['存じます', 'ぞんじます', 'zonjimasu', 'mengenal (merendahkan diri dari しります)', 'to know (humble)'],
            ['伺います', 'うかがいます', 'ukagaimasu', 'bertanya, mendengar, berkunjung (merendahkan diri)', 'to ask, hear, visit (humble)'],
            ['お目にかかります', 'おめにかかります', 'ome ni kakarimasu', 'bertemu (merendahkan diri dari あいます)', 'to meet (humble)'],
            ['入れます', 'いれます', 'iremasu', 'membuat [kopi] (menyiram air panas)', 'to make [coffee]'],
            ['用意します', 'よういします', 'youi shimasu', 'menyediakan', 'to prepare, to provide'],
            ['私', 'わたくし', 'watakushi', 'saya (merendahkan diri dari わたし)', 'I (humble)'],
            ['ガイド', 'ガイド', 'gaido', 'pemandu', 'guide'],
            ['メールアドレス', 'メールアドレス', 'meeru adoresu', 'alamat e-mail', 'e-mail address'],
            ['スケジュール', 'スケジュール', 'sukejuuru', 'jadwal', 'schedule'],
            ['再来週', 'さらいしゅう', 'saraishuu', 'dua minggu lagi', 'the week after next'],
            ['再来月', 'さらいげつ', 'saraigetsu', 'dua bulan lagi', 'the month after next'],
            ['再来年', 'さらいねん', 'sarainen', 'dua tahun lagi', 'the year after next'],
            ['初めに', 'はじめに', 'hajime ni', 'pertama-tama', 'first of all'],

            // 会話 (percakapan)
            ['緊張します', 'きんちょうします', 'kinchou shimasu', 'tegang', 'to be nervous'],
            ['賞金', 'しょうきん', 'shoukin', 'hadiah uang', 'prize money'],
            ['きりん', 'きりん', 'kirin', 'jerapah', 'giraffe'],
            ['頃', 'ころ', 'koro', 'ketika (contoh: ketika anak, ketika mahasiswa)', 'time when, around'],
            ['かないます', 'かないます', 'kanaimasu', '[impian] terkabul', '[a dream] comes true'],
            ['応援します', 'おうえんします', 'ouen shimasu', 'mendukung', 'to support, to cheer'],
            ['心から', 'こころから', 'kokoro kara', 'dari segenap hati', 'from the heart'],
            ['感謝します', 'かんしゃします', 'kansha shimasu', 'berterima kasih', 'to be thankful'],

            // 読み物 (bacaan)
            ['お礼', 'おれい', 'orei', 'ucapan terima kasih', 'thanks, token of thanks'],
            ['お元気でいらっしゃいますか', 'おげんきでいらっしゃいますか', 'ogenki de irasshaimasu ka', 'Bagaimana kabarnya? (hormat dari おげんきですか)', 'How are you? (honorific)'],
            ['迷惑をかけます', 'めいわくをかけます', 'meiwaku o kakemasu', 'mengganggu', 'to cause trouble'],
            ['生かします', 'いかします', 'ikashimasu', 'menggunakan (contoh: menggunakan pengalaman)', 'to make use of'],
        ];
    }
}
