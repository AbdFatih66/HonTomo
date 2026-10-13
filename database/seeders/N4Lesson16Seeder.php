<?php

namespace Database\Seeders;

/**
 * Materi N4, Pelajaran 16 ("Selamat Menempuh Hidup Baru!"). Logika bersama ada di N4LessonSeeder.
 *
 * Bunpou (6 kartu): いただきます, くださいます, やります／あげます, Vて いただきます／
 * くださいます／やります, Vて くださいませんか, N に (sebagai tanda／kenang-kenangan).
 * Kosakata: 59 kata, tanpa nama diri (浦島太郎).
 *
 * Catatan isi: 発音 di buku diterjemahkan "ungkapan"; di sini dipakai arti yang
 * benar, "pengucapan (lafal)".
 *
 *   php artisan db:seed --class=N4Lesson16Seeder
 */
class N4Lesson16Seeder extends N4LessonSeeder
{
    protected function unitOrder(): int
    {
        return 16;
    }

    protected function unitInfo(): array
    {
        return [
            'title_id' => 'Pelajaran 16: Selamat Menempuh Hidup Baru!',
            'title_en' => 'Lesson 16: Congratulations on Your New Life!',
            'description_id' => 'ungkapan memberi dan menerima: いただきます, くださいます, やります, 〜てくださいませんか.',
            'description_en' => 'giving and receiving: いただきます, くださいます, やります, 〜てくださいませんか.',
        ];
    }

    protected function cards(): array
    {
        return [
            // 1 ------------------------------------------------------------
            [
                'title_id' => 'いただきます (menerima dari orang yang lebih tinggi)',
                'title_en' => 'いただきます (receiving from someone higher)',
                'pattern' => 'Ｎ１(orang)に Ｎ２を いただきます',
                'payload' => [
                    'explanation_id' => 'いただきます adalah bentuk merendah dari もらいます. Dipakai ketika pembicara menerima sesuatu (N2) dari orang yang kedudukannya lebih tinggi — atasan, guru, orang yang dihormati. Pemberinya ditandai に (boleh juga から). Kalau pemberi sebanding atau lebih rendah, tetap pakai もらいます.',
                    'explanation_en' => 'いただきます is the humble form of もらいます. It is used when the speaker receives something (N2) from a person of higher standing — a boss, a teacher, someone respected. The giver is marked with に (から also works). When the giver is an equal or lower, keep using もらいます.',
                    'notes_id' => [
                        'Bentuk lampau: いただきました. Bentuk ます-nya tetap いただきます (bukan いただります).',
                        'Penerima bisa anggota keluarga pembicara: 娘《むすめ》は 先生《せんせい》に 本《ほん》を いただきました — keluarga dianggap satu kelompok dengan pembicara.',
                        'Dipakai juga untuk makan/minum secara sopan (いただきます sebelum makan) — artinya sama: "menerima".',
                    ],
                    'notes_en' => [
                        'Past: いただきました. The ます-form stays いただきます (not いただります).',
                        'The receiver can be a member of the speaker family: 娘《むすめ》は 先生《せんせい》に 本《ほん》を いただきました — family counts as the speaker side.',
                        'It is also the polite word for eating/drinking (いただきます before a meal) — the sense is the same: "to receive".',
                    ],
                    'examples' => [
                        ['ja' => 'わたしは 先生《せんせい》に 辞書《じしょ》を いただきました。', 'reading' => 'Watashi wa sensei ni jisho o itadakimashita.', 'id' => 'Saya menerima kamus dari guru.', 'en' => 'I received a dictionary from my teacher.'],
                        ['ja' => '部長《ぶちょう》に ワインを いただきました。', 'reading' => 'Buchou ni wain o itadakimashita.', 'id' => 'Saya diberi anggur oleh kepala bagian.', 'en' => 'I received a bottle of wine from the department head.'],
                        ['ja' => '娘《むすめ》は 田中《たなか》さんの おばあさんに おかしを いただきました。', 'reading' => 'Musume wa Tanaka-san no obaasan ni okashi o itadakimashita.', 'id' => 'Anak perempuan saya menerima kue dari nenek Pak Tanaka.', 'en' => 'My daughter received sweets from Mr. Tanaka grandmother.'],
                        ['ja' => '山田《やまだ》さんに きれいな 絵《え》はがきを いただきました。', 'reading' => 'Yamada-san ni kirei na ehagaki o itadakimashita.', 'id' => 'Saya menerima kartu pos bergambar yang indah dari Bapak/Ibu Yamada.', 'en' => 'I received a lovely picture postcard from Mr./Ms. Yamada.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Sapu tangan baru',
                        'title_en' => 'A new handkerchief',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'いい ハンカチですね。', 'reading' => 'Ii hankachi desu ne.', 'id' => 'Sapu tangannya bagus, ya.', 'en' => 'That is a nice handkerchief.'],
                            ['speaker' => 'サリ', 'ja' => 'ありがとうございます。先生《せんせい》に いただいたんです。', 'reading' => 'Arigatou gozaimasu. Sensei ni itadaita n desu.', 'id' => 'Terima kasih. Ini saya terima dari guru.', 'en' => 'Thank you. I received it from my teacher.'],
                            ['speaker' => 'たなか', 'ja' => 'そうですか。すてきな 先生《せんせい》ですね。', 'reading' => 'Sou desu ka. Suteki na sensei desu ne.', 'id' => 'Begitu, ya. Gurunya baik sekali.', 'en' => 'I see. What a wonderful teacher.'],
                        ],
                    ],
                ],
            ],

            // 2 ------------------------------------------------------------
            [
                'title_id' => 'くださいます (diberi oleh orang yang lebih tinggi)',
                'title_en' => 'くださいます (being given something by someone higher)',
                'pattern' => 'Ｎ１が [わたしに] Ｎ２を くださいます',
                'payload' => [
                    'explanation_id' => 'くださいます adalah bentuk hormat dari くれます. Dipakai ketika orang yang lebih tinggi (N1) memberikan sesuatu kepada pembicara atau keluarganya. Sudut pandangnya: orang yang menerima adalah "kita". Bandingkan dengan いただきます yang memakai subjek penerima; くださいます memakai subjek pemberi, ditandai が.',
                    'explanation_en' => 'くださいます is the honorific form of くれます. It is used when a person of higher standing (N1) gives something to the speaker or the speaker family. The viewpoint stays with the receiver ("us"). Compare いただきます, whose subject is the receiver; くださいます has the giver as subject, marked with が.',
                    'notes_id' => [
                        'Bentuk ます-nya くださいます (bukan くださります); lampau くださいました; bentuk biasa lampau くださった.',
                        'Pasangan: 先生が くださいました = わたしは 先生に いただきました (arti sama, subjek berbeda).',
                    ],
                    'notes_en' => [
                        'The ます-form is くださいます (not くださります); past くださいました; plain past くださった.',
                        'A pair: 先生が くださいました = わたしは 先生に いただきました (same meaning, different subject).',
                    ],
                    'examples' => [
                        ['ja' => '先生《せんせい》が わたしに 本《ほん》を くださいました。', 'reading' => 'Sensei ga watashi ni hon o kudasaimashita.', 'id' => 'Guru memberi saya sebuah buku.', 'en' => 'My teacher gave me a book.'],
                        ['ja' => '部長《ぶちょう》が 子《こ》どもに おもちゃを くださいました。', 'reading' => 'Buchou ga kodomo ni omocha o kudasaimashita.', 'id' => 'Kepala bagian memberi anak saya mainan.', 'en' => 'The department head gave my child a toy.'],
                        ['ja' => '管理人《かんりにん》さんが 野菜《やさい》を くださいました。', 'reading' => 'Kanrinin-san ga yasai o kudasaimashita.', 'id' => 'Pak penjaga apartemen memberi saya sayuran.', 'en' => 'The apartment manager gave me some vegetables.'],
                        ['ja' => '社長《しゃちょう》が 娘《むすめ》に 絵本《えほん》を くださいました。', 'reading' => 'Shachou ga musume ni ehon o kudasaimashita.', 'id' => 'Direktur memberi anak perempuan saya buku gambar.', 'en' => 'The company president gave my daughter a picture book.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Buku gambar dari guru',
                        'title_en' => 'A picture book from the teacher',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '先生《せんせい》が この 絵本《えほん》を くださったんです。', 'reading' => 'Sensei ga kono ehon o kudasatta n desu.', 'id' => 'Guru memberi saya buku gambar ini.', 'en' => 'My teacher gave me this picture book.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'すてきですね。', 'reading' => 'Suteki desu ne.', 'id' => 'Bagus, ya.', 'en' => 'It is lovely.'],
                            ['speaker' => 'サリ', 'ja' => 'ええ。子《こ》どもに 読《よ》んで あげます。', 'reading' => 'Ee. Kodomo ni yonde agemasu.', 'id' => 'Ya. Akan saya bacakan untuk anak saya.', 'en' => 'Yes. I will read it to my child.'],
                        ],
                    ],
                ],
            ],

            // 3 ------------------------------------------------------------
            [
                'title_id' => 'やります／あげます (memberi kepada yang lebih rendah)',
                'title_en' => 'やります／あげます (giving to someone lower)',
                'pattern' => 'Ｎ１に Ｎ２を やります／あげます',
                'payload' => [
                    'explanation_id' => 'やります dipakai ketika pembicara memberi sesuatu kepada orang yang lebih rendah (anak, adik), hewan, atau tumbuhan. Dalam praktik, banyak orang sekarang memakai あげます sebagai gantinya karena terdengar lebih sopan, terutama untuk anak dan orang lain. Untuk hewan dan tanaman, やります masih wajar.',
                    'explanation_en' => 'やります is used when the speaker gives something to a person of lower standing (a child, a younger sibling), an animal, or a plant. In practice many people now use あげます instead because it sounds more polite, especially for children and other people. For animals and plants, やります is still natural.',
                    'notes_id' => [
                        'Jangan memakai やります kepada atasan atau orang yang dihormati — itu terdengar merendahkan.',
                        'Untuk orang yang lebih tinggi ada bentuk lain (さしあげます), yang dipelajari di pelajaran berikutnya.',
                    ],
                    'notes_en' => [
                        'Do not use やります toward a superior or someone you respect — it sounds condescending.',
                        'For people of higher standing there is another form (さしあげます), covered in a later lesson.',
                    ],
                    'examples' => [
                        ['ja' => '毎朝《まいあさ》 花《はな》に 水《みず》を やります。', 'reading' => 'Maiasa hana ni mizu o yarimasu.', 'id' => 'Setiap pagi saya menyiram bunga.', 'en' => 'Every morning I water the flowers.'],
                        ['ja' => '息子《むすこ》に おもちゃを やりました（あげました）。', 'reading' => 'Musuko ni omocha o yarimashita (agemashita).', 'id' => 'Saya memberi anak laki-laki saya mainan.', 'en' => 'I gave my son a toy.'],
                        ['ja' => '動物園《どうぶつえん》で 猿《さる》に えさを やりました。', 'reading' => 'Doubutsuen de saru ni esa o yarimashita.', 'id' => 'Di kebun binatang saya memberi makan monyet.', 'en' => 'At the zoo I fed the monkeys.'],
                        ['ja' => '猿《さる》に えさを やっては いけません。', 'reading' => 'Saru ni esa o yatte wa ikemasen.', 'id' => 'Dilarang memberi makan monyet.', 'en' => 'You must not feed the monkeys.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di taman',
                        'title_en' => 'In the garden',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '毎朝《まいあさ》 何《なに》を して いますか。', 'reading' => 'Maiasa nani o shite imasu ka.', 'id' => 'Setiap pagi Anda melakukan apa?', 'en' => 'What do you do every morning?'],
                            ['speaker' => 'たなか', 'ja' => '庭《にわ》の 花《はな》に 水《みず》を やって いるんです。', 'reading' => 'Niwa no hana ni mizu o yatte iru n desu.', 'id' => 'Saya menyiram bunga di kebun.', 'en' => 'I water the flowers in the garden.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'きれいな 花《はな》ですね。', 'reading' => 'Kirei na hana desu ne.', 'id' => 'Bunganya cantik, ya.', 'en' => 'They are pretty flowers.'],
                        ],
                    ],
                ],
            ],

            // 4 ------------------------------------------------------------
            [
                'title_id' => 'Vて いただきます／くださいます／やります (memberi dan menerima perbuatan)',
                'title_en' => 'Vて いただきます／くださいます／やります (giving and receiving actions)',
                'pattern' => 'Vて いただきます／くださいます／やります',
                'payload' => [
                    'explanation_id' => 'Tiga kata tadi juga dipakai setelah kata kerja bentuk-て untuk menyatakan bahwa sebuah perbuatan dilakukan sebagai kebaikan. Maknanya mengikuti kata pemberi/penerimanya: 〜て いただきます = "dibantu/dilakukan untuk saya oleh orang yang lebih tinggi" (subjek: penerima), 〜て くださいます = "orang yang lebih tinggi melakukan sesuatu untuk saya" (subjek: pelaku), 〜て やります／あげます = "saya melakukan sesuatu untuk orang yang lebih rendah".',
                    'explanation_en' => 'The same three words follow a verb て-form to say that an action is done as a kindness. The sense follows the giving/receiving word: 〜て いただきます = "someone higher did it for me" (subject: the receiver), 〜て くださいます = "someone higher does something for me" (subject: the doer), 〜て やります／あげます = "I do something for someone lower".',
                    'notes_id' => [
                        'Ada rasa terima kasih pada いただきます／くださいます — pembicara menghargai kebaikan itu. Pada 〜て やります tidak ada rasa itu; lebih sopan pakai 〜て あげます.',
                        'Penerima perbuatan (わたしに／わたしを／わたしの〜) sering dihilangkan jika sudah jelas.',
                    ],
                    'notes_en' => [
                        'いただきます／くださいます carry gratitude — the speaker appreciates the kindness. 〜て やります has no such feeling; 〜て あげます is the politer choice.',
                        'The receiver (わたしに／わたしを／わたしの〜) is often dropped when it is obvious.',
                    ],
                    'examples' => [
                        ['ja' => '先生《せんせい》に 漢字《かんじ》の 読《よ》み方《かた》を 教《おし》えて いただきました。', 'reading' => 'Sensei ni kanji no yomikata o oshiete itadakimashita.', 'id' => 'Saya diajari cara membaca kanji oleh guru.', 'en' => 'My teacher taught me how to read the kanji.'],
                        ['ja' => '管理人《かんりにん》さんが 荷物《にもつ》を 持《も》って くださいました。', 'reading' => 'Kanrinin-san ga nimotsu o motte kudasaimashita.', 'id' => 'Pak penjaga apartemen membawakan barang saya.', 'en' => 'The apartment manager carried my luggage for me.'],
                        ['ja' => '部長《ぶちょう》が 雨《あめ》の 日《ひ》に 車《くるま》で うちまで 送《おく》って くださいました。', 'reading' => 'Buchou ga ame no hi ni kuruma de uchi made okutte kudasaimashita.', 'id' => 'Kepala bagian mengantar saya sampai rumah dengan mobil pada hari hujan.', 'en' => 'The department head drove me home on a rainy day.'],
                        ['ja' => '子《こ》どもに 絵本《えほん》を 読《よ》んで やりました（あげました）。', 'reading' => 'Kodomo ni ehon o yonde yarimashita (agemashita).', 'id' => 'Saya membacakan buku gambar untuk anak saya.', 'en' => 'I read a picture book to my child.'],
                        ['ja' => '友達《ともだち》の 引《ひ》っ越《こ》しを 手伝《てつだ》って あげました。', 'reading' => 'Tomodachi no hikkoshi o tetsudatte agemashita.', 'id' => 'Saya membantu teman pindahan.', 'en' => 'I helped my friend move.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Belajar bahasa Jepang',
                        'title_en' => 'Studying Japanese',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '日本語《にほんご》の 勉強《べんきょう》は どうですか。', 'reading' => 'Nihongo no benkyou wa dou desu ka.', 'id' => 'Bagaimana belajar bahasa Jepangnya?', 'en' => 'How is your Japanese study going?'],
                            ['speaker' => 'ワヒュ', 'ja' => '楽《たの》しいです。先生《せんせい》が 毎週《まいしゅう》 発音《はつおん》を 直《なお》して くださいます。', 'reading' => 'Tanoshii desu. Sensei ga maishuu hatsuon o naoshite kudasaimasu.', 'id' => 'Menyenangkan. Guru memperbaiki pengucapan saya setiap minggu.', 'en' => 'It is fun. My teacher corrects my pronunciation every week.'],
                            ['speaker' => 'サリ', 'ja' => 'いい 先生《せんせい》ですね。', 'reading' => 'Ii sensei desu ne.', 'id' => 'Gurunya baik, ya.', 'en' => 'You have a good teacher.'],
                        ],
                    ],
                ],
            ],

            // 5 ------------------------------------------------------------
            [
                'title_id' => 'Vて くださいませんか (permintaan sopan)',
                'title_en' => 'Vて くださいませんか (a polite request)',
                'pattern' => 'Vて くださいませんか',
                'payload' => [
                    'explanation_id' => 'Permintaan yang lebih halus daripada 〜て ください, karena berbentuk pertanyaan negatif: "Tidak bisakah Anda …?". Tingkat kesopanannya berada di antara 〜て ください dan 〜て いただけませんか (Pelajaran 1). Cocok untuk orang yang belum akrab atau yang kedudukannya lebih tinggi.',
                    'explanation_en' => 'A request that is gentler than 〜て ください because it is a negative question: "Would you not …?". Its politeness sits between 〜て ください and 〜て いただけませんか (Lesson 1). Suitable for people you are not close to or who are of higher standing.',
                    'notes_id' => [
                        'Urutan kesopanan: 〜て ください < 〜て くださいませんか < 〜て いただけませんか.',
                        'Tambahkan すみませんが di depan agar lebih halus lagi.',
                    ],
                    'notes_en' => [
                        'Politeness order: 〜て ください < 〜て くださいませんか < 〜て いただけませんか.',
                        'Add すみませんが in front to soften it further.',
                    ],
                    'examples' => [
                        ['ja' => 'すみませんが、この 荷物《にもつ》を 持《も》って くださいませんか。', 'reading' => 'Sumimasen ga, kono nimotsu o motte kudasaimasen ka.', 'id' => 'Maaf, bisakah Anda membawakan barang ini?', 'en' => 'Excuse me, would you carry this luggage for me?'],
                        ['ja' => 'もう 少《すこ》し ゆっくり 話《はな》して くださいませんか。', 'reading' => 'Mou sukoshi yukkuri hanashite kudasaimasen ka.', 'id' => 'Bisakah Anda bicara sedikit lebih pelan?', 'en' => 'Would you speak a little more slowly?'],
                        ['ja' => '子《こ》どもに 英語《えいご》を 教《おし》えて くださいませんか。', 'reading' => 'Kodomo ni eigo o oshiete kudasaimasen ka.', 'id' => 'Bisakah Anda mengajari anak saya bahasa Inggris?', 'en' => 'Would you teach my child English?'],
                        ['ja' => 'この 写真《しゃしん》を 見《み》て くださいませんか。', 'reading' => 'Kono shashin o mite kudasaimasen ka.', 'id' => 'Bisakah Anda melihat foto ini?', 'en' => 'Would you take a look at this photo?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Meminjam obeng',
                        'title_en' => 'Borrowing a screwdriver',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '管理人《かんりにん》さん、すみません。ドライバーを 貸《か》して くださいませんか。', 'reading' => 'Kanrinin-san, sumimasen. Doraibaa o kashite kudasaimasen ka.', 'id' => 'Pak penjaga, permisi. Bisakah Anda meminjamkan obeng?', 'en' => 'Manager, excuse me. Would you lend me a screwdriver?'],
                            ['speaker' => '管理人', 'ja' => 'ええ、いいですよ。何《なに》に 使《つか》うんですか。', 'reading' => 'Ee, ii desu yo. Nani ni tsukau n desu ka.', 'id' => 'Ya, boleh. Untuk apa?', 'en' => 'Sure. What do you need it for?'],
                            ['speaker' => 'ワヒュ', 'ja' => '机《つくえ》を 直《なお》したいんです。', 'reading' => 'Tsukue o naoshitai n desu.', 'id' => 'Saya ingin memperbaiki meja.', 'en' => 'I want to fix my desk.'],
                            ['speaker' => '管理人', 'ja' => 'どうぞ。', 'reading' => 'Douzo.', 'id' => 'Silakan.', 'en' => 'Here you are.'],
                        ],
                    ],
                ],
            ],

            // 6 ------------------------------------------------------------
            [
                'title_id' => 'Ｎに V (に = sebagai tanda／kenang-kenangan)',
                'title_en' => 'Ｎに V (に = as a token／as a souvenir)',
                'pattern' => 'Ｎ１に Ｎ２を V',
                'payload' => [
                    'explanation_id' => 'Partikel に setelah kata benda tertentu (お祝《いわ》い, お見舞《みま》い, お土産《みやげ》, お礼《れい》, 誕生日《たんじょうび》) menyatakan tujuan atau kesempatan dari benda yang diberikan atau dibeli: "sebagai tanda ~", "sebagai kenang-kenangan ~". Jadi N1 adalah alasan/acaranya, N2 adalah bendanya.',
                    'explanation_en' => 'The particle に after certain nouns (お祝《いわ》い, お見舞《みま》い, お土産《みやげ》, お礼《れい》, 誕生日《たんじょうび》) states the purpose or occasion of the thing given or bought: "as a token of ~", "as a souvenir of ~". N1 is the occasion, N2 is the item.',
                    'notes_id' => [
                        'Perbedaan dengan に tujuan arah: 京都《きょうと》に 行《い》きます (pergi ke Kyoto) vs 京都《きょうと》の お土産《みやげ》に お茶《ちゃ》を 買《か》いました (membeli teh sebagai oleh-oleh Kyoto).',
                        'お祝い = hadiah perayaan; お見舞い = hadiah/kunjungan untuk orang sakit; お土産 = oleh-oleh.',
                    ],
                    'notes_en' => [
                        'Compare に of direction: 京都《きょうと》に 行《い》きます (go to Kyoto) vs 京都《きょうと》の お土産《みやげ》に お茶《ちゃ》を 買《か》いました (bought tea as a Kyoto souvenir).',
                        'お祝い = a celebration gift; お見舞い = a gift or visit for someone sick; お土産 = a souvenir.',
                    ],
                    'examples' => [
                        ['ja' => '友達《ともだち》の 結婚《けっこん》の お祝《いわ》いに 時計《とけい》を あげました。', 'reading' => 'Tomodachi no kekkon no oiwai ni tokei o agemashita.', 'id' => 'Saya memberi teman jam tangan sebagai hadiah pernikahan.', 'en' => 'I gave my friend a watch as a wedding present.'],
                        ['ja' => 'お見舞《みま》いに 果物《くだもの》を 持《も》って 行《い》きました。', 'reading' => 'Omimai ni kudamono o motte ikimashita.', 'id' => 'Saya membawa buah sebagai bingkisan untuk orang sakit.', 'en' => 'I took fruit as a get-well gift.'],
                        ['ja' => '京都《きょうと》の お土産《みやげ》に お茶《ちゃ》を 買《か》いました。', 'reading' => 'Kyouto no omiyage ni ocha o kaimashita.', 'id' => 'Saya membeli teh sebagai oleh-oleh dari Kyoto.', 'en' => 'I bought tea as a souvenir from Kyoto.'],
                        ['ja' => '子《こ》どもの 誕生日《たんじょうび》に 絵本《えほん》を あげました。', 'reading' => 'Kodomo no tanjoubi ni ehon o agemashita.', 'id' => 'Saya memberi anak itu buku gambar sebagai hadiah ulang tahun.', 'en' => 'I gave the child a picture book for their birthday.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Hadiah pernikahan',
                        'title_en' => 'A wedding gift',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '来週《らいしゅう》、友達《ともだち》の 結婚式《けっこんしき》に 行《い》くんです。', 'reading' => 'Raishuu, tomodachi no kekkonshiki ni iku n desu.', 'id' => 'Minggu depan saya akan menghadiri pernikahan teman.', 'en' => 'Next week I am going to a friend wedding.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'お祝《いわ》いに 何《なに》を あげますか。', 'reading' => 'Oiwai ni nani o agemasu ka.', 'id' => 'Hadiah apa yang akan Anda berikan?', 'en' => 'What will you give as a gift?'],
                            ['speaker' => 'たなか', 'ja' => 'きれいな お皿《さら》を あげます。', 'reading' => 'Kirei na osara o agemasu.', 'id' => 'Saya akan memberi piring yang cantik.', 'en' => 'I will give some pretty plates.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function words(): array
    {
        return [
            // Kosakata utama
            ['いただきます', 'いただきます', 'itadakimasu', 'menerima (kata merendahkan diri dari もらいます)', 'to receive (humble form of もらいます)'],
            ['くださいます', 'くださいます', 'kudasaimasu', 'memberikan (kata hormat dari くれます)', 'to give (honorific form of くれます)'],
            ['やります', 'やります', 'yarimasu', 'memberi (kepada orang yang lebih rendah, binatang, tanaman)', 'to give (to juniors, animals, plants)'],
            ['上げます', 'あげます', 'agemasu', 'menaikkan', 'to raise'],
            ['下げます', 'さげます', 'sagemasu', 'menurunkan', 'to lower'],
            ['親切にします', 'しんせつにします', 'shinsetsu ni shimasu', 'berbaik hati', 'to be kind'],
            ['かわいい', 'かわいい', 'kawaii', 'manis', 'cute'],
            ['珍しい', 'めずらしい', 'mezurashii', 'langka', 'rare, unusual'],
            ['お祝い', 'おいわい', 'oiwai', 'perayaan, hadiah', 'celebration, congratulatory gift'],
            ['お年玉', 'おとしだま', 'otoshidama', 'otoshidama (angpao Tahun Baru)', 'otoshidama (New Year money gift)'],
            ['[お]見舞い', '[お]みまい', 'omimai', 'pembesukan, barang bantuan', 'visit to the sick, get-well gift'],
            ['興味', 'きょうみ', 'kyoumi', 'minat', 'interest'],
            ['情報', 'じょうほう', 'jouhou', 'informasi', 'information'],
            ['文法', 'ぶんぽう', 'bunpou', 'tata bahasa', 'grammar'],
            ['発音', 'はつおん', 'hatsuon', 'pengucapan (lafal)', 'pronunciation'],
            ['猿', 'さる', 'saru', 'monyet', 'monkey'],
            ['えさ', 'えさ', 'esa', 'makanan hewan, umpan', 'animal feed, bait'],
            ['おもちゃ', 'おもちゃ', 'omocha', 'mainan', 'toy'],
            ['絵本', 'えほん', 'ehon', 'buku gambar', 'picture book'],
            ['絵はがき', 'えはがき', 'ehagaki', 'kartu pos bergambar', 'picture postcard'],
            ['ドライバー', 'ドライバー', 'doraibaa', 'obeng', 'screwdriver'],
            ['ハンカチ', 'ハンカチ', 'hankachi', 'sapu tangan', 'handkerchief'],
            ['靴下', 'くつした', 'kutsushita', 'kaus kaki', 'socks'],
            ['手袋', 'てぶくろ', 'tebukuro', 'sarung tangan', 'gloves'],
            ['幼稚園', 'ようちえん', 'youchien', 'TK (taman kanak-kanak)', 'kindergarten'],
            ['暖房', 'だんぼう', 'danbou', 'alat pemanas', 'heating'],
            ['冷房', 'れいぼう', 'reibou', 'AC (pendingin ruangan)', 'air conditioning (cooling)'],
            ['温度', 'おんど', 'ondo', 'suhu', 'temperature'],
            ['祖父', 'そふ', 'sofu', 'kakek', 'grandfather'],
            ['祖母', 'そぼ', 'sobo', 'nenek', 'grandmother'],
            ['孫', 'まご', 'mago', 'cucu', 'grandchild'],
            ['お孫さん', 'おまごさん', 'omagosan', 'cucu (untuk orang lain)', 'grandchild (of another person)'],
            ['おじ', 'おじ', 'oji', 'om, paman', 'uncle'],
            ['おじさん', 'おじさん', 'ojisan', 'om (untuk orang lain)', 'uncle (of another person)'],
            ['おば', 'おば', 'oba', 'tante', 'aunt'],
            ['おばさん', 'おばさん', 'obasan', 'tante (untuk orang lain)', 'aunt (of another person)'],
            ['管理人', 'かんりにん', 'kanrinin', 'penjaga (misalnya penjaga apartemen)', 'manager, caretaker (e.g. of an apartment)'],
            ['〜さん', '〜さん', 'san', 'Bapak ~, Ibu ~ (akhiran pada nama pekerjaan atau jabatan)', 'Mr./Ms. ~ (suffix after a job title)'],
            ['この間', 'このあいだ', 'konoaida', 'beberapa saat lalu', 'the other day'],

            // 会話 (percakapan)
            ['ひとこと', 'ひとこと', 'hitokoto', 'sepatah kata', 'a few words'],
            ['〜ずつ', '〜ずつ', 'zutsu', 'setiap ~', '~ each, ~ at a time'],
            ['二人', 'ふたり', 'futari', 'berdua, pasangan', 'two people, a couple'],
            ['お宅', 'おたく', 'otaku', 'rumah (kata hormat dari うち atau いえ)', 'home (honorific of うち／いえ)'],
            ['どうぞ お幸せに。', 'どうぞ おしあわせに。', 'douzo oshiawase ni', 'Selamat berbahagia!', 'I wish you happiness!'],

            // 読み物 (bacaan)
            ['昔話', 'むかしばなし', 'mukashibanashi', 'cerita dongeng', 'folk tale'],
            ['ある〜', 'ある〜', 'aru', 'suatu ~', 'a certain ~'],
            ['男', 'おとこ', 'otoko', 'laki-laki', 'man'],
            ['子どもたち', 'こどもたち', 'kodomotachi', 'anak-anak', 'children'],
            ['いじめます', 'いじめます', 'ijimemasu', 'menyiksa, menganiaya', 'to bully, to torment'],
            ['かめ', 'かめ', 'kame', 'kura-kura', 'turtle'],
            ['助けます', 'たすけます', 'tasukemasu', 'membantu, menolong', 'to help, to rescue'],
            ['優しい', 'やさしい', 'yasashii', 'baik hati', 'kind, gentle'],
            ['お姫様', 'おひめさま', 'ohimesama', 'putri', 'princess'],
            ['暮らします', 'くらします', 'kurashimasu', 'hidup', 'to live, to make a living'],
            ['陸', 'りく', 'riku', 'daratan', 'land'],
            ['すると', 'すると', 'suruto', 'kemudian', 'then, thereupon'],
            ['煙', 'けむり', 'kemuri', 'asap', 'smoke'],
            ['真っ白[な]', 'まっしろ[な]', 'masshiro', 'putih bersih', 'pure white'],
            ['中身', 'なかみ', 'nakami', 'isi', 'contents'],
        ];
    }
}
