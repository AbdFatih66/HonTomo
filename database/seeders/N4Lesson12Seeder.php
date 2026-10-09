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

class N4Lesson12Seeder extends Seeder
{
    /**
     * Materi N4, Pelajaran 12 ("Kinkakuji Didirikan pada Abad Keempat Belas").
     * Level N4 dibuat oleh N4Lesson1Seeder; seeder ini menambah unit order 12.
     * Nomor pelajaran N4 di antara 2 dan 12 belum ada; urutan tampil mengikuti
     * Unit.order sehingga akan terselip sendiri saat pelajaran 3-11 ditambahkan.
     *
     * Isi:
     *   - Unit order 12 di level N4
     *   - Lesson Bunpou (order 0, category grammar) dengan 6 kartu:
     *       1. Kata kerja pasif (bentuk)         4. Ｎが／は + pasif (tanpa pelaku)
     *       2. Ｎ１は Ｎ２に + pasif             5. Ｎから／Ｎで 作られます (bahan)
     *       3. Ｎ１は Ｎ２に Ｎ３を + pasif      6. Ｎ１の Ｎ２ ／ この・その・あの + posisi
     *   - Kategori kosakata "n4-pelajaran-12" + 52 kata (jlpt_level N4)
     *   - Lesson kosakata (order 1) + kuis pilihan ganda per kata
     *     (dibangun oleh VocabularyQuizSync::run('N4'))
     *
     * Pasangan kata / bacaan / arti adalah fakta kamus. Penjelasan, contoh
     * kalimat dan dialog ditulis baru untuk aplikasi ini. Nama diri dan nama
     * tempat (kuil, negara, zaman, pemahat) sengaja tidak dimasukkan.
     *
     * Aman dijalankan berulang dan pada database yang sudah punya user:
     *   php artisan db:seed --class=N4Lesson12Seeder
     *
     * Kata kunci pencarian SQL memakai ASCII (kategori.slug + romaji), bukan
     * huruf Jepang (lihat docs/AGENTS.md aturan 4).
     */
    public function run(): void
    {
        $level = Level::where('code', 'N4')->first();

        if (! $level) {
            $this->call(N4Lesson1Seeder::class);
            $level = Level::where('code', 'N4')->firstOrFail();
        }

        $unit = Unit::updateOrCreate(
            ['level_id' => $level->id, 'order' => 12],
            [
                'title_id' => 'Pelajaran 12: Kinkakuji Didirikan pada Abad Keempat Belas',
                'title_en' => 'Lesson 12: Kinkakuji Was Built in the Fourteenth Century',
                'description_id' => 'Kata kerja pasif, pasif tanpa pelaku, bahan pembuat (から／で), dan この／その／あの + kata posisi.',
                'description_en' => 'Passive verbs, the agentless passive, materials (から／で), and この／その／あの + position words.',
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
                'title_id' => 'Kata kerja pasif (bentuk)',
                'title_en' => 'Passive verbs (forms)',
                'pattern' => '受身形《うけみけい》: 書《か》かれる ／ 褒《ほ》められる ／ される',
                'payload' => [
                    'explanation_id' => 'Kata kerja pasif (受身形《うけみけい》) dipakai ketika sesuatu "dikenai" suatu perbuatan. Kelompok I: huruf akhir kamus berubah ke baris あ lalu ditambah れます (書《か》きます → 書《か》かれます; khusus akhiran う memakai わ: 買《か》う → 買《か》われます). Kelompok II: 〜ます diganti 〜られます (褒《ほ》めます → 褒《ほ》められます). Kelompok III: 来《き》ます → 来《こ》られます, します → されます. Seperti bentuk potensial, bentuk pasif dikonjugasi sebagai kata kerja kelompok II: 書《か》かれる, 書《か》かれない, 書《か》かれて.',
                    'explanation_en' => 'The passive form (受身形《うけみけい》) is used when someone or something is "affected by" an action. Group I: the last kana of the dictionary form moves to the あ row and れます is added (書《か》きます → 書《か》かれます; verbs ending in う use わ: 買《か》う → 買《か》われます). Group II: 〜ます becomes 〜られます (褒《ほ》めます → 褒《ほ》められます). Group III: 来《き》ます → 来《こ》られます, します → されます. Like the potential form, the passive conjugates as a group II verb: 書《か》かれる, 書《か》かれない, 書《か》かれて.',
                    'notes_id' => [
                        'Contoh lain: 読《よ》む → 読《よ》まれる, 話《はな》す → 話《はな》される, 呼《よ》ぶ → 呼《よ》ばれる, 食《た》べる → 食《た》べられる.',
                        '来《こ》られる sama bentuknya dengan potensial 来《こ》られる; artinya dilihat dari konteks.',
                        'Kata kerja yang tidak punya objek atau pelaku tidak dibuat pasif di tingkat ini.',
                    ],
                    'notes_en' => [
                        'More examples: 読《よ》む → 読《よ》まれる, 話《はな》す → 話《はな》される, 呼《よ》ぶ → 呼《よ》ばれる, 食《た》べる → 食《た》べられる.',
                        '来《こ》られる looks the same as the potential 来《こ》られる; the context tells you which one it is.',
                        'Verbs without an object or an agent are not made passive at this level.',
                    ],
                    'examples' => [
                        ['ja' => '先生《せんせい》に 呼《よ》ばれました。', 'reading' => 'Sensei ni yobaremashita.', 'id' => 'Saya dipanggil oleh guru.', 'en' => 'I was called by the teacher.'],
                        ['ja' => '友達《ともだち》に 誘《さそ》われました。', 'reading' => 'Tomodachi ni sasowaremashita.', 'id' => 'Saya diajak oleh teman.', 'en' => 'I was invited by a friend.'],
                        ['ja' => '子《こ》どもの とき、よく 母《はは》に しかられました。', 'reading' => 'Kodomo no toki, yoku haha ni shikararemashita.', 'id' => 'Waktu kecil, saya sering dimarahi ibu.', 'en' => 'When I was a child, I was often scolded by my mother.'],
                        ['ja' => '社長《しゃちょう》に 褒《ほ》められました。', 'reading' => 'Shachou ni homeraremashita.', 'id' => 'Saya dipuji oleh direktur.', 'en' => 'I was praised by the company president.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Kenapa tidak semangat?',
                        'title_en' => 'Why so down?',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'ワヒュさん、どうして 元気《げんき》が ないんですか。', 'reading' => 'Wahyu-san, doushite genki ga nai n desu ka.', 'id' => 'Wahyu, kenapa kamu tidak bersemangat?', 'en' => 'Wahyu, why are you not in good spirits?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'さっき 部長《ぶちょう》に 呼《よ》ばれたんです。', 'reading' => 'Sakki buchou ni yobareta n desu.', 'id' => 'Tadi saya dipanggil oleh kepala bagian.', 'en' => 'I was called by the department head a while ago.'],
                            ['speaker' => 'サリ', 'ja' => '何《なに》か あったんですか。', 'reading' => 'Nani ka atta n desu ka.', 'id' => 'Ada apa?', 'en' => 'Did something happen?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'メールの 書《か》き方《かた》を 注意《ちゅうい》されました。', 'reading' => 'Meeru no kakikata o chuui saremashita.', 'id' => 'Saya ditegur soal cara menulis email.', 'en' => 'I was told to be careful about how I write emails.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => 'N１は N２に 受身 (dikenai perbuatan orang)',
                'title_en' => 'N１は N２に passive (being acted on)',
                'pattern' => 'Ｎ１（人）は Ｎ２（人）に ＋ 受身形《うけみけい》',
                'payload' => [
                    'explanation_id' => 'Kalimat aktif "N２ が N１ を V" dilihat dari sisi orang yang dikenai perbuatan menjadi "N１ は N２ に V-pasif". Orang yang dikenai perbuatan (N１) menjadi topik, dan pelakunya (N２) ditandai に. Contoh: 先生《せんせい》が わたしを 褒《ほ》めました → わたしは 先生《せんせい》に 褒《ほ》められました. Pelaku tidak harus manusia; binatang atau kendaraan juga bisa (犬《いぬ》に 追《お》いかけられました).',
                    'explanation_en' => 'The active sentence "N２ が N１ を V" seen from the side of the person who is affected becomes "N１ は N２ に passive-V". The affected person (N１) becomes the topic and the doer (N２) is marked with に. Example: 先生《せんせい》が わたしを 褒《ほ》めました → わたしは 先生《せんせい》に 褒《ほ》められました. The doer need not be a person; animals and vehicles can be doers too (犬《いぬ》に 追《お》いかけられました).',
                    'notes_id' => [
                        'Pasif ini bisa terasa positif (dipuji, diundang) maupun negatif (dimarahi, ditegur); artinya mengikuti kata kerjanya.',
                        'Pelaku ditandai に, bukan が. Jangan lupa: yang jadi topik adalah orang yang dikenai perbuatan.',
                    ],
                    'notes_en' => [
                        'This passive can feel positive (praised, invited) or negative (scolded, warned); the feeling follows the verb.',
                        'The doer is marked with に, not が. Remember: the topic is the person who is affected.',
                    ],
                    'examples' => [
                        ['ja' => '友達《ともだち》に パーティーに 誘《さそ》われました。', 'reading' => 'Tomodachi ni paatii ni sasowaremashita.', 'id' => 'Saya diajak teman ke pesta.', 'en' => 'I was invited to a party by a friend.'],
                        ['ja' => '母《はは》に 部屋《へや》の そうじを 頼《たの》まれました。', 'reading' => 'Haha ni heya no souji o tanomaremashita.', 'id' => 'Saya diminta ibu membersihkan kamar.', 'en' => 'I was asked by my mother to clean the room.'],
                        ['ja' => '犬《いぬ》に 追《お》いかけられました。', 'reading' => 'Inu ni oikakeraremashita.', 'id' => 'Saya dikejar anjing.', 'en' => 'I was chased by a dog.'],
                        ['ja' => '兄《あに》に 笑《わら》われました。', 'reading' => 'Ani ni warawaremashita.', 'id' => 'Saya ditertawakan kakak laki-laki.', 'en' => 'I was laughed at by my older brother.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Akhir pekan',
                        'title_en' => 'The weekend',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '日曜日《にちようび》、何《なに》を しましたか。', 'reading' => 'Nichiyoubi, nani o shimashita ka.', 'id' => 'Hari Minggu kamu melakukan apa?', 'en' => 'What did you do on Sunday?'],
                            ['speaker' => 'ワヒュ', 'ja' => '友達《ともだち》に 誘《さそ》われて、山《やま》へ 行《い》きました。', 'reading' => 'Tomodachi ni sasowarete, yama e ikimashita.', 'id' => 'Saya diajak teman, lalu pergi ke gunung.', 'en' => 'A friend invited me, so I went to the mountains.'],
                            ['speaker' => 'たなか', 'ja' => 'いいですね。', 'reading' => 'Ii desu ne.', 'id' => 'Menyenangkan, ya.', 'en' => 'That sounds nice.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ。山《やま》で きれいな 花《はな》を たくさん 見《み》ました。', 'reading' => 'Ee. Yama de kirei na hana o takusan mimashita.', 'id' => 'Ya. Di gunung saya melihat banyak bunga indah.', 'en' => 'Yes. I saw lots of beautiful flowers on the mountain.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => 'N１は N２に N３を 受身 (barang milik dikenai perbuatan)',
                'title_en' => 'N１は N２に N３を passive (your belongings affected)',
                'pattern' => 'Ｎ１（人）は Ｎ２（人）に Ｎ３を ＋ 受身形《うけみけい》',
                'payload' => [
                    'explanation_id' => 'Pola ini dipakai ketika orang lain berbuat sesuatu pada benda milik atau bagian tubuh N１ (N３), dan N１ merasa terganggu. Yang jadi topik adalah pemiliknya (N１), bukan bendanya: 弟《おとうと》が わたしの 本《ほん》を 汚《よご》しました → わたしは 弟《おとうと》に 本《ほん》を 汚《よご》されました. Kesan "terganggu／rugi" sangat kuat, jadi pola ini cocok untuk keluhan dan laporan kejadian.',
                    'explanation_en' => 'This pattern is used when someone does something to N１\'s belongings or body part (N３) and N１ is bothered by it. The topic is the owner (N１), not the thing: 弟《おとうと》が わたしの 本《ほん》を 汚《よご》しました → わたしは 弟《おとうと》に 本《ほん》を 汚《よご》されました. The feeling of "being inconvenienced／harmed" is strong, so this pattern suits complaints and reports of incidents.',
                    'notes_id' => [
                        'Jangan menjadikan bendanya topik: ×わたしの 本は 弟に 汚されました (kurang wajar di pola ini).',
                        'Kalau perbuatan itu justru membantu dan kamu berterima kasih, pakai 〜て もらいます, bukan pasif: 友達《ともだち》に 荷物《にもつ》を 運《はこ》んで もらいました.',
                    ],
                    'notes_en' => [
                        'Do not make the thing the topic: ×わたしの 本は 弟に 汚されました (unnatural in this pattern).',
                        'When the action actually helps and you are grateful, use 〜て もらいます, not the passive: 友達《ともだち》に 荷物《にもつ》を 運《はこ》んで もらいました.',
                    ],
                    'examples' => [
                        ['ja' => 'わたしは 弟《おとうと》に 本《ほん》を 汚《よご》されました。', 'reading' => 'Watashi wa otouto ni hon o yogosaremashita.', 'id' => 'Buku saya dikotori adik laki-laki.', 'en' => 'My book was dirtied by my younger brother.'],
                        ['ja' => '電車《でんしゃ》で 知《し》らない 人《ひと》に 足《あし》を 踏《ふ》まれました。', 'reading' => 'Densha de shiranai hito ni ashi o fumaremashita.', 'id' => 'Di kereta, kaki saya terinjak orang yang tidak dikenal.', 'en' => 'On the train, a stranger stepped on my foot.'],
                        ['ja' => '泥棒《どろぼう》に 財布《さいふ》を 盗《ぬす》まれました。', 'reading' => 'Dorobou ni saifu o nusumaremashita.', 'id' => 'Dompet saya dicuri pencuri.', 'en' => 'My wallet was stolen by a thief.'],
                        ['ja' => '友達《ともだち》に 荷物《にもつ》を 運《はこ》んで もらいました。', 'reading' => 'Tomodachi ni nimotsu o hakonde moraimashita.', 'id' => 'Barang saya dibawakan teman (saya berterima kasih).', 'en' => 'A friend carried my luggage for me (I am grateful).'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Melapor ke polisi',
                        'title_en' => 'Reporting to the police',
                        'lines' => [
                            ['speaker' => '警官', 'ja' => 'どう しましたか。', 'reading' => 'Dou shimashita ka.', 'id' => 'Ada apa?', 'en' => 'What happened?'],
                            ['speaker' => 'ワヒュ', 'ja' => '電車《でんしゃ》の 中《なか》で かばんを 盗《ぬす》まれたんです。', 'reading' => 'Densha no naka de kaban o nusumareta n desu.', 'id' => 'Tas saya dicuri di dalam kereta.', 'en' => 'My bag was stolen on the train.'],
                            ['speaker' => '警官', 'ja' => '何《なに》が 入《はい》って いましたか。', 'reading' => 'Nani ga haitte imashita ka.', 'id' => 'Apa isinya?', 'en' => 'What was in it?'],
                            ['speaker' => 'ワヒュ', 'ja' => '財布《さいふ》と パスポートです。', 'reading' => 'Saifu to pasupooto desu.', 'id' => 'Dompet dan paspor.', 'en' => 'A wallet and a passport.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'N が／は 受身 (tanpa menyebut pelaku)',
                'title_en' => 'N が／は passive (no agent mentioned)',
                'pattern' => 'Ｎ（物・事）が／は ＋ 受身形《うけみけい》',
                'payload' => [
                    'explanation_id' => 'Ketika yang ingin dijelaskan adalah kejadian, fakta, atau hasil karya dan pelakunya tidak penting (atau tidak diketahui), bendanya dijadikan subjek atau topik dan kata kerja dibuat pasif. Pola ini sering muncul di berita, buku sejarah, dan pengumuman. Pelaku tidak disebut. Contoh: 毎年《まいとし》 十月《じゅうがつ》に マラソン大会《たいかい》が 行《おこな》われます (Setiap Oktober diadakan lomba maraton). Bentuk 〜て います menyatakan keadaan yang berlangsung: 世界中《せかいじゅう》で 読《よ》まれて います.',
                    'explanation_en' => 'When you want to describe an event, a fact or a work and the doer is unimportant (or unknown), the thing becomes the subject or topic and the verb is made passive. This pattern is common in news, history books and announcements. The doer is not mentioned. Example: 毎年《まいとし》 十月《じゅうがつ》に マラソン大会《たいかい》が 行《おこな》われます (A marathon is held every October). The 〜て います form describes an ongoing state: 世界中《せかいじゅう》で 読《よ》まれて います.',
                    'notes_id' => [
                        'Kata kerja yang sering muncul: 開《ひら》かれます, 行《おこな》われます, 発明《はつめい》されました, 発見《はっけん》されました, 輸出《ゆしゅつ》されて います, 翻訳《ほんやく》されて います.',
                        'Kalau ada pelaku yang ingin ditonjolkan, pakai kalimat aktif (Ｎが Ｖます) atau pasif dengan に.',
                    ],
                    'notes_en' => [
                        'Common verbs: 開《ひら》かれます, 行《おこな》われます, 発明《はつめい》されました, 発見《はっけん》されました, 輸出《ゆしゅつ》されて います, 翻訳《ほんやく》されて います.',
                        'If you want to highlight the doer, use an active sentence (Ｎが Ｖます) or a passive with に.',
                    ],
                    'examples' => [
                        ['ja' => '来月《らいげつ》、この 町《まち》で お祭《まつ》りが 開《ひら》かれます。', 'reading' => 'Raigetsu, kono machi de omatsuri ga hirakaremasu.', 'id' => 'Bulan depan, festival diadakan di kota ini.', 'en' => 'Next month a festival will be held in this town.'],
                        ['ja' => '新《あたら》しい 星《ほし》が 発見《はっけん》されました。', 'reading' => 'Atarashii hoshi ga hakken saremashita.', 'id' => 'Sebuah bintang baru telah ditemukan.', 'en' => 'A new star has been discovered.'],
                        ['ja' => 'この 小説《しょうせつ》は 世界中《せかいじゅう》で 翻訳《ほんやく》されて います。', 'reading' => 'Kono shousetsu wa sekaijuu de honyaku sarete imasu.', 'id' => 'Novel ini diterjemahkan di seluruh dunia.', 'en' => 'This novel has been translated all over the world.'],
                        ['ja' => '日本《にほん》の 車《くるま》は 世界《せかい》へ 輸出《ゆしゅつ》されて います。', 'reading' => 'Nihon no kuruma wa sekai e yushutsu sarete imasu.', 'id' => 'Mobil Jepang diekspor ke seluruh dunia.', 'en' => 'Japanese cars are exported all over the world.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Acara kembang api',
                        'title_en' => 'A fireworks event',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '日本《にほん》では 花火大会《はなびたいかい》は いつ 行《おこな》われますか。', 'reading' => 'Nihon de wa hanabi taikai wa itsu okonawaremasu ka.', 'id' => 'Di Jepang, kapan lomba kembang api diadakan?', 'en' => 'In Japan, when are fireworks festivals held?'],
                            ['speaker' => 'たなか', 'ja' => '夏《なつ》に 行《おこな》われますよ。', 'reading' => 'Natsu ni okonawaremasu yo.', 'id' => 'Diadakan pada musim panas.', 'en' => 'They are held in summer.'],
                            ['speaker' => 'サリ', 'ja' => '見《み》に 行《い》きたいです。', 'reading' => 'Mi ni ikitai desu.', 'id' => 'Saya ingin pergi menontonnya.', 'en' => 'I would like to go and see one.'],
                            ['speaker' => 'たなか', 'ja' => '川《かわ》の 近《ちか》くで 開《ひら》かれますから、いっしょに 行《い》きましょう。', 'reading' => 'Kawa no chikaku de hirakaremasu kara, issho ni ikimashou.', 'id' => 'Diadakan di dekat sungai, ayo pergi bersama.', 'en' => 'It is held near the river, so let us go together.'],
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => 'Ｎから／Ｎで 作ります (bahan)',
                'title_en' => 'Ｎから／Ｎで 作ります (materials)',
                'pattern' => 'Ｎ（原料《げんりょう》）から ／ Ｎ（材料《ざいりょう》）で ＋ 作《つく》られます',
                'payload' => [
                    'explanation_id' => 'Untuk menyatakan bahan pembuat sesuatu, pasif 作《つく》られます sering dipakai. Dua partikel dibedakan: から dipakai bila bahan berubah wujud sehingga sudah tidak tampak asalnya (bahan baku: ビールは 麦《むぎ》から 作《つく》られます), sedangkan で dipakai bila bahan masih terlihat seperti aslinya (bahan: この 机《つくえ》は 木《き》で 作《つく》られて います).',
                    'explanation_en' => 'To say what something is made from, the passive 作《つく》られます is common. Two particles are distinguished: から is used when the material changes form so that its origin is no longer visible (raw material: ビールは 麦《むぎ》から 作《つく》られます), while で is used when the material is still recognisable (material: この 机《つくえ》は 木《き》で 作《つく》られて います).',
                    'notes_id' => [
                        'Untuk minuman keras, bangunan, atau barang dalam jumlah besar kadang dipakai 造《つく》る (造《つく》られます), tetapi di tingkat ini 作《つく》る sudah cukup.',
                        'Bandingkan aktif: 麦《むぎ》から ビールを 作《つく》ります.',
                    ],
                    'notes_en' => [
                        'For alcohol, buildings or large-scale products 造《つく》る (造《つく》られます) is sometimes used, but 作《つく》る is enough at this level.',
                        'Compare the active: 麦《むぎ》から ビールを 作《つく》ります.',
                    ],
                    'examples' => [
                        ['ja' => 'チーズは 牛乳《ぎゅうにゅう》から 作《つく》られます。', 'reading' => 'Chiizu wa gyuunyuu kara tsukuraremasu.', 'id' => 'Keju dibuat dari susu sapi.', 'en' => 'Cheese is made from milk.'],
                        ['ja' => '紙《かみ》は 木《き》から 作《つく》られます。', 'reading' => 'Kami wa ki kara tsukuraremasu.', 'id' => 'Kertas dibuat dari kayu.', 'en' => 'Paper is made from wood.'],
                        ['ja' => 'この 机《つくえ》は 木《き》で 作《つく》られて います。', 'reading' => 'Kono tsukue wa ki de tsukurarete imasu.', 'id' => 'Meja ini dibuat dari kayu.', 'en' => 'This desk is made of wood.'],
                        ['ja' => '昔《むかし》、この 橋《はし》は 石《いし》で 作《つく》られました。', 'reading' => 'Mukashi, kono hashi wa ishi de tsukuraremashita.', 'id' => 'Dulu, jembatan ini dibuat dari batu.', 'en' => 'Long ago, this bridge was made of stone.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Bahan makanan Jepang',
                        'title_en' => 'Ingredients of Japanese food',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '日本《にほん》の お酒《さけ》は 何《なに》から 作《つく》られて いるんですか。', 'reading' => 'Nihon no osake wa nani kara tsukurarete iru n desu ka.', 'id' => 'Sake Jepang dibuat dari apa?', 'en' => 'What is Japanese sake made from?'],
                            ['speaker' => 'たなか', 'ja' => '米《こめ》から 作《つく》られて いますよ。', 'reading' => 'Kome kara tsukurarete imasu yo.', 'id' => 'Dibuat dari beras.', 'en' => 'It is made from rice.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'しょうゆは？', 'reading' => 'Shouyu wa?', 'id' => 'Kalau kecap asin?', 'en' => 'And soy sauce?'],
                            ['speaker' => 'たなか', 'ja' => '大豆《だいず》から 作《つく》られます。', 'reading' => 'Daizu kara tsukuraremasu.', 'id' => 'Dibuat dari kedelai.', 'en' => 'It is made from soybeans.'],
                        ],
                    ],
                ],
            ],

            // 6 ------------------------------------------------------------
            [
                'title_id' => 'Ｎ１の Ｎ２ ／ この・その・あの＋kata posisi',
                'title_en' => 'Ｎ１の Ｎ２ ／ この・その・あの＋position words',
                'pattern' => 'Ｎ１の Ｎ２ ／ この・その・あの ＋ 中《なか》・上《うえ》・隣《となり》・近《ちか》く',
                'payload' => [
                    'explanation_id' => '(1) Ｎ１の Ｎ２ dapat dipakai untuk menjelaskan N２ dengan N１ yang menyebut jenis atau perannya: 原料《げんりょう》の 米《こめ》 ("beras, yaitu bahan bakunya"), 友達《ともだち》の 田中《たなか》さん ("Tanaka, temanku"). (2) Kata benda posisi seperti 上《うえ》, 下《した》, 中《なか》, 隣《となり》, 近《ちか》く dapat diberi この／その／あの di depannya. Maksudnya posisi terhadap benda yang ditunjuk: あの 中《なか》 = "bagian dalam benda (bangunan) itu", その 上《うえ》 = "di atas benda itu".',
                    'explanation_en' => '(1) Ｎ１の Ｎ２ can describe N２ with N１ naming its kind or role: 原料《げんりょう》の 米《こめ》 ("rice, namely the raw material"), 友達《ともだち》の 田中《たなか》さん ("Tanaka, my friend"). (2) Position nouns such as 上《うえ》, 下《した》, 中《なか》, 隣《となり》, 近《ちか》く can take この／その／あの in front. This means the position in relation to the thing being pointed at: あの 中《なか》 = "the inside of that building", その 上《うえ》 = "on top of that".',
                    'notes_id' => [
                        'あの 中《なか》 sebenarnya singkatan あの 建物《たてもの》の 中《なか》; bendanya sudah jelas dari situasi.',
                        'Contoh lain Ｎ１の Ｎ２ akan muncul lagi di pelajaran berikutnya (misalnya ペットの 犬《いぬ》).',
                    ],
                    'notes_en' => [
                        'あの 中《なか》 is short for あの 建物《たてもの》の 中《なか》; the thing is clear from the situation.',
                        'More examples of Ｎ１の Ｎ２ will appear in later lessons (for example ペットの 犬《いぬ》).',
                    ],
                    'examples' => [
                        ['ja' => 'これは 原料《げんりょう》の 米《こめ》です。', 'reading' => 'Kore wa genryou no kome desu.', 'id' => 'Ini beras, bahan bakunya.', 'en' => 'This is rice, the raw material.'],
                        ['ja' => '友達《ともだち》の 田中《たなか》さんを 紹介《しょうかい》します。', 'reading' => 'Tomodachi no Tanaka-san o shoukai shimasu.', 'id' => 'Saya perkenalkan teman saya, Tanaka.', 'en' => 'Let me introduce my friend, Mr. Tanaka.'],
                        ['ja' => 'その 上《うえ》に 辞書《じしょ》が あります。', 'reading' => 'Sono ue ni jisho ga arimasu.', 'id' => 'Di atasnya ada kamus.', 'en' => 'There is a dictionary on top of it.'],
                        ['ja' => 'あの 隣《となり》の 店《みせ》で 昼《ひる》ごはんを 食《た》べましょう。', 'reading' => 'Ano tonari no mise de hirugohan o tabemashou.', 'id' => 'Ayo makan siang di toko sebelahnya itu.', 'en' => 'Let us have lunch at the shop next to that one.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mencari kantor pos',
                        'title_en' => 'Looking for the post office',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません。この 近《ちか》くに 郵便局《ゆうびんきょく》が ありますか。', 'reading' => 'Sumimasen. Kono chikaku ni yuubinkyoku ga arimasu ka.', 'id' => 'Permisi. Apakah ada kantor pos di dekat sini?', 'en' => 'Excuse me. Is there a post office near here?'],
                            ['speaker' => 'たなか', 'ja' => 'ええ。あの 白《しろ》い 建物《たてもの》の 隣《となり》ですよ。', 'reading' => 'Ee. Ano shiroi tatemono no tonari desu yo.', 'id' => 'Ada. Di sebelah gedung putih itu.', 'en' => 'Yes. It is next to that white building.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'あの 隣《となり》ですね。ありがとうございます。', 'reading' => 'Ano tonari desu ne. Arigatou gozaimasu.', 'id' => 'Di sebelahnya itu, ya. Terima kasih.', 'en' => 'Right next to that. Thank you.'],
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
            ['slug' => 'n4-pelajaran-12'],
            [
                'name_id' => 'Kosakata N4 Pelajaran 12',
                'name_en' => 'N4 Lesson 12 Vocabulary',
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
            ['褒めます', 'ほめます', 'homemasu', 'memuji', 'to praise'],
            ['しかります', 'しかります', 'shikarimasu', 'memarahi', 'to scold'],
            ['誘います', 'さそいます', 'sasoimasu', 'mengajak', 'to invite, to ask along'],
            ['招待します', 'しょうたいします', 'shoutaishimasu', 'mengundang', 'to invite (formally)'],
            ['頼みます', 'たのみます', 'tanomimasu', 'meminta', 'to ask, to request'],
            ['注意します', 'ちゅういします', 'chuuishimasu', 'menasihati, menegur', 'to warn, to advise'],
            ['取ります', 'とります', 'torimasu', 'mencuri', 'to steal, to take'],
            ['踏みます', 'ふみます', 'fumimasu', 'menginjak', 'to step on'],
            ['壊します', 'こわします', 'kowashimasu', 'merusak', 'to break, to destroy'],
            ['汚します', 'よごします', 'yogoshimasu', 'mengotori', 'to make dirty'],
            ['行います', 'おこないます', 'okonaimasu', 'mengadakan', 'to hold, to carry out'],
            ['輸出します', 'ゆしゅつします', 'yushutsushimasu', 'mengekspor', 'to export'],
            ['輸入します', 'ゆにゅうします', 'yunyuushimasu', 'mengimpor', 'to import'],
            ['翻訳します', 'ほんやくします', 'honyakushimasu', 'menerjemahkan', 'to translate'],
            ['発明します', 'はつめいします', 'hatsumeishimasu', 'menciptakan', 'to invent'],
            ['発見します', 'はっけんします', 'hakkenshimasu', 'menemukan', 'to discover'],
            ['米', 'こめ', 'kome', 'beras', 'rice (uncooked)'],
            ['麦', 'むぎ', 'mugi', 'gandum', 'wheat, barley'],
            ['石油', 'せきゆ', 'sekiyu', 'minyak tanah', 'petroleum, kerosene'],
            ['原料', 'げんりょう', 'genryou', 'bahan baku', 'raw material'],
            ['インスタントラーメン', 'インスタントラーメン', 'insutanto raamen', 'mie instan', 'instant noodles'],
            ['デート', 'デート', 'deeto', 'kencan', 'date'],
            ['泥棒', 'どろぼう', 'dorobou', 'pencuri', 'thief'],
            ['警官', 'けいかん', 'keikan', 'polisi', 'police officer'],
            ['世界中', 'せかいじゅう', 'sekaijuu', 'seluruh dunia', 'all over the world'],
            ['〜中', '〜じゅう', 'juu', 'seluruh ~', 'all over ~, throughout ~'],
            ['〜世紀', '〜せいき', 'seiki', 'abad ke ~', '~th century'],
            ['何語', 'なにご', 'nanigo', 'bahasa apa', 'what language'],
            ['だれか', 'だれか', 'dareka', 'seseorang, siapa pun', 'someone'],
            ['よかったですね。', 'よかったですね。', 'yokatta desu ne', 'Bagus, ya. (Syukurlah.)', 'That is good. (I am glad.)'],
            ['オリンピック', 'オリンピック', 'orinpikku', 'Olimpiade', 'Olympic Games'],
            ['ワールドカップ', 'ワールドカップ', 'waarudo kappu', 'piala dunia', 'World Cup'],

            // 会話 (percakapan)
            ['皆様', 'みなさま', 'minasama', 'semuanya (kata hormat dari みなさん)', 'everyone (honorific of みなさん)'],
            ['焼けます', 'やけます', 'yakemasu', 'terbakar [rumah terbakar]', 'to burn [a house burns]'],
            ['その後', 'そのご', 'sonogo', 'setelah itu, sesudah itu', 'after that'],
            ['世界遺産', 'せかいいさん', 'sekai isan', 'situs warisan dunia', 'World Heritage site'],
            ['〜の一つ', '〜のひとつ', 'no hitotsu', 'salah satu ~', 'one of ~'],
            ['金色', 'きんいろ', 'kiniro', 'warna emas', 'golden colour'],
            ['本物', 'ほんもの', 'honmono', 'asli', 'genuine, the real thing'],
            ['金', 'きん', 'kin', 'emas', 'gold'],
            ['〜キロ', '〜キロ', 'kiro', '~ kilogram, ~ kilometer', '~ kilogram, ~ kilometre'],
            ['美しい', 'うつくしい', 'utsukushii', 'indah, elok', 'beautiful'],

            // 読み物 (bacaan)
            ['豪華[な]', 'ごうか[な]', 'gouka', 'mewah', 'luxurious'],
            ['彫刻', 'ちょうこく', 'chokoku', 'ukiran, pahatan', 'carving, sculpture'],
            ['言い伝え', 'いいつたえ', 'iitsutae', 'tradisi, legenda', 'tradition, legend'],
            ['眠ります', 'ねむります', 'nemurimasu', 'tidur', 'to sleep'],
            ['彫ります', 'ほります', 'horimasu', 'mengukir, memahat', 'to carve'],
            ['仲間', 'なかま', 'nakama', 'teman, kawan', 'companion, colleague'],
            ['しかし', 'しかし', 'shikashi', 'tetapi, akan tetapi', 'however'],
            ['その あと', 'そのあと', 'sono ato', 'setelah itu, sesudah itu', 'after that'],
            ['一生懸命', 'いっしょうけんめい', 'isshoukenmei', 'sungguh-sungguh', 'with all one\'s might'],
            ['ねずみ', 'ねずみ', 'nezumi', 'tikus', 'mouse, rat'],
        ];
    }
}
