<?php

namespace Database\Seeders;

/**
 * Tata Bahasa N3 Pelajaran 4 (10 kartu, dengan dialog).
 *   php artisan db:seed --class=N3Lesson4BunpouSeeder
 */
class N3Lesson4BunpouSeeder extends N3GrammarSeeder
{
    protected function unitOrder(): int
    {
        return 4;
    }

    /**
     * Dialog pendek per kartu (kunci = nomor kartu, mulai 1).
     *
     * @return array<int, array<int, array<string, string>>>
     */
    protected function dialogues(): array
    {
        return [
            1 => [
                $this->dl('A', '山田《やまだ》さんから 電話《でんわ》が ありました。', 'Yamada-san kara denwa ga arimashita.', 'Ada telepon dari Pak Yamada.', 'There was a phone call from Mr. Yamada.'),
                $this->dl('B', '何《なん》と 言《い》って いましたか。', 'Nan to itte imashita ka.', 'Dia bilang apa?', 'What did he say?'),
                $this->dl('A', '電車《でんしゃ》が 遅《おく》れて いて、少《すこ》し 遅《おく》れるということです。', 'Densha ga okurete ite, sukoshi okureru toiu koto desu.', 'Katanya keretanya terlambat, jadi dia akan sedikit terlambat.', 'He said his train is delayed, so he will be a little late.'),
                $this->dl('B', '少《すこ》し 遅《おく》れるということですね。わかりました。', 'Sukoshi okureru toiu koto desu ne. Wakarimashita.', 'Jadi dia akan sedikit terlambat, ya. Baik.', 'So he will be a little late. Understood.'),
            ],
            2 => [
                $this->dl('A', 'どこへ 行《い》くの？', 'Doko e iku no?', 'Mau ke mana?', 'Where are you going?'),
                $this->dl('B', 'ちょっと 図書館《としょかん》へ。', 'Chotto toshokan e.', 'Ke perpustakaan sebentar.', 'Just to the library.'),
                $this->dl('A', '何《なに》を 借《か》りるの？', 'Nani o kariru no?', 'Mau pinjam apa?', 'What are you going to borrow?'),
                $this->dl('B', '日本語《にほんご》の 本《ほん》。明日《あした》 試験《しけん》なの。', 'Nihongo no hon. Ashita shiken na no.', 'Buku bahasa Jepang. Besok ada ujian.', 'A Japanese book. I have an exam tomorrow.'),
            ],
            3 => [
                $this->dl('A', 'あ、また 財布《さいふ》を 忘《わす》れちゃった。', 'A, mata saifu o wasurechatta.', 'Ah, dompetku tertinggal lagi.', 'Oh no, I have forgotten my wallet again.'),
                $this->dl('B', '取《と》りに 行《い》く？', 'Tori ni iku?', 'Mau kuambilkan?', 'Do you want to go get it?'),
                $this->dl('A', 'ううん、友達《ともだち》が 待《ま》ってるから、このまま 行《い》っちゃう。', 'Uun, tomodachi ga matteru kara, kono mama ichau.', 'Tidak, teman sudah menunggu, jadi aku langsung pergi saja.', 'No, my friend is waiting, so I will just go like this.'),
                $this->dl('B', 'じゃあ、お金《かね》を 貸《か》しとくよ。', 'Jaa, okane o kashitoku yo.', 'Kalau begitu, aku pinjamkan uang dulu.', 'Then I will lend you some money.'),
            ],
            4 => [
                $this->dl('A', '昨日《きのう》の 飲《の》み会《かい》、どうだった？', 'Kinou no nomikai, dou datta?', 'Bagaimana acara minum kemarin?', 'How was the drinking party yesterday?'),
                $this->dl('B', '疲《つか》れたよ。上司《じょうし》に 歌《うた》を 歌《うた》わされたんだ。', 'Tsukareta yo. Joushi ni uta o utawasareta n da.', 'Capek. Aku dipaksa atasan menyanyi.', 'I was exhausted. My boss made me sing.'),
                $this->dl('A', 'それは 大変《たいへん》だったね。', 'Sore wa taihen datta ne.', 'Wah, berat juga ya.', 'That must have been rough.'),
                $this->dl('B', 'その うえ、最後《さいご》まで 付《つ》き合《あ》わされたよ。', 'Sono ue, saigo made tsukiawasareta yo.', 'Belum lagi, aku dipaksa menemani sampai akhir.', 'On top of that, I was made to stay until the very end.'),
            ],
            5 => [
                $this->dl('A', 'この レポートの 結論《けつろん》は どう 書《か》けば いいでしょうか。', 'Kono repooto no ketsuron wa dou kakeba ii deshou ka.', 'Bagaimana sebaiknya saya menulis kesimpulan laporan ini?', 'How should I write the conclusion of this report?'),
                $this->dl('B', '論文《ろんぶん》ですから、「〜だ」ではなく「〜である」で 書《か》いて ください。', 'Ronbun desu kara, "~da" de wa naku "~dearu" de kaite kudasai.', 'Karena ini karya tulis ilmiah, tulislah dengan "~dearu", bukan "~da".', 'Since it is a paper, please write it with "~dearu" rather than "~da".'),
                $this->dl('A', 'たとえば、「この 問題《もんだい》は 重要《じゅうよう》である」と 書《か》くのですか。', 'Tatoeba, "kono mondai wa juuyou dearu" to kaku no desu ka.', 'Misalnya, menulis "masalah ini penting (dearu)", begitu?', 'For example, writing "this problem is important (dearu)"?'),
                $this->dl('B', 'ええ、そうです。', 'Ee, sou desu.', 'Ya, betul.', 'Yes, that is right.'),
            ],
            6 => [
                $this->dl('A', '朝《あさ》は 何《なに》を して いますか。', 'Asa wa nani o shite imasu ka.', 'Pagi hari Anda biasa melakukan apa?', 'What do you do in the morning?'),
                $this->dl('B', '起《お》きて、顔《かお》を 洗《あら》い、コーヒーを 飲《の》み、新聞《しんぶん》を 読《よ》みます。', 'Okite, kao o arai, koohii o nomi, shinbun o yomimasu.', 'Bangun, cuci muka, minum kopi, lalu membaca koran.', 'I get up, wash my face, drink coffee and read the paper.'),
                $this->dl('A', '忙《いそが》しいですね。', 'Isogashii desu ne.', 'Sibuk ya.', 'You are busy.'),
                $this->dl('B', 'でも、朝《あさ》の 時間《じかん》は 静《しず》かで、気持《きも》ちが いいですよ。', 'Demo, asa no jikan wa shizuka de, kimochi ga ii desu yo.', 'Tapi waktu pagi tenang dan nyaman.', 'But the morning is quiet and pleasant.'),
            ],
            7 => [
                $this->dl('A', '息子《むすこ》さんは 最近《さいきん》 どうですか。', 'Musukosan wa saikin dou desu ka.', 'Bagaimana kabar putra Anda akhir-akhir ini?', 'How is your son these days?'),
                $this->dl('B', '新《あたら》しい ゲームを 欲《ほ》しがって いるんです。', 'Atarashii geemu o hoshigatte iru n desu.', 'Dia sedang menginginkan game baru.', 'He is longing for a new game.'),
                $this->dl('A', '友達《ともだち》が 持《も》って いるからですか。', 'Tomodachi ga motte iru kara desu ka.', 'Apakah karena temannya punya?', 'Because his friends have it?'),
                $this->dl('B', 'ええ。でも 私《わたし》が 買《か》わないので、悔《くや》しがって います。', 'Ee. Demo watashi ga kawanai node, kuyashigatte imasu.', 'Ya. Tapi karena saya tidak membelikan, dia kesal.', 'Yes. But since I will not buy it, he is frustrated.'),
            ],
            8 => [
                $this->dl('A', '田中《たなか》さんが 結婚《けっこん》した ことを 知《し》って いますか。', 'Tanaka-san ga kekkon shita koto o shitte imasu ka.', 'Tahukah Anda bahwa Pak Tanaka sudah menikah?', 'Did you know that Mr. Tanaka got married?'),
                $this->dl('B', 'ええ、聞《き》きました。', 'Ee, kikimashita.', 'Ya, saya sudah dengar.', 'Yes, I heard.'),
                $this->dl('A', '相手《あいて》が 有名《ゆうめい》な 歌手《かしゅ》だという ことも 知《し》って いますか。', 'Aite ga yuumei na kashu da toiu koto mo shitte imasu ka.', 'Tahukah Anda juga bahwa pasangannya penyanyi terkenal?', 'Did you also know that his partner is a famous singer?'),
                $this->dl('B', 'えっ、それは 知《し》りませんでした。', 'E, sore wa shirimasen deshita.', 'Eh, itu saya tidak tahu.', 'What? I did not know that.'),
            ],
            9 => [
                $this->dl('A', '皆《みな》さん、こちらが 部長《ぶちょう》の 田中《たなか》です。', 'Minasan, kochira ga buchou no Tanaka desu.', 'Semuanya, ini Pak Tanaka, kepala bagian kita.', 'Everyone, this is Mr. Tanaka, our department manager.'),
                $this->dl('B', 'はじめまして。課長《かちょう》の 山田《やまだ》です。', 'Hajimemashite. Kachou no Yamada desu.', 'Salam kenal. Saya Yamada, kepala seksi.', 'Nice to meet you. I am Yamada, the section chief.'),
                $this->dl('A', 'ところで、あさっての 金曜日《きんようび》は ご都合《つごう》は いかがですか。', 'Tokorode, asatte no kinyoubi wa gotsugou wa ikaga desu ka.', 'Omong-omong, apakah lusa, hari Jumat, Anda ada waktu?', 'By the way, are you free the day after tomorrow, on Friday?'),
                $this->dl('B', 'ええ、大丈夫《だいじょうぶ》です。', 'Ee, daijoubu desu.', 'Ya, tidak masalah.', 'Yes, that is fine.'),
            ],
            10 => [
                $this->dl('A', '本日《ほんじつ》は お越《こ》し いただきまして、ありがとうございました。', 'Honjitsu wa okoshi itadakimashite, arigatou gozaimashita.', 'Terima kasih banyak atas kedatangan Anda hari ini.', 'Thank you very much for coming today.'),
                $this->dl('B', 'こちらこそ、ありがとうございました。', 'Kochira koso, arigatou gozaimashita.', 'Saya yang berterima kasih.', 'Thank you, it is I who should thank you.'),
                $this->dl('A', '会議《かいぎ》が 終《お》わりましたら、こちらから お電話《でんわ》させて いただきます。', 'Kaigi ga owarimashitara, kochira kara odenwa sasete itadakimasu.', 'Setelah rapat selesai, kami akan menghubungi Anda.', 'Once the meeting is over, we will give you a call.'),
                $this->dl('B', 'ありがとうございます。お待《ま》ちして おります。', 'Arigatou gozaimasu. Omachi shite orimasu.', 'Terima kasih. Kami tunggu.', 'Thank you. We will be waiting.'),
            ],
        ];
    }

    protected function cards(): array
    {
        return [
            $this->card(
                '…ということだ (kalimat tidak langsung)',
                '…ということだ (reported speech)',
                'S 普通形《ふつうけい》 ＋ ということだ／とのことです',
                '「X ということだ」 dipakai untuk menyampaikan apa yang dikatakan orang lain (X) atau apa yang dikatakan orang pada umumnya; maknanya mirip dengan 「X そうだ」. Bentuk 「X ということですね」 dipakai untuk mengulang ucapan lawan bicara guna memastikan. Bentuk 「とのことです」 lebih bernuansa bahasa tulis.',
                '「X ということだ」 is used to report what someone else has said (X) or what people in general say; its meaning is close to 「X そうだ」. 「X ということですね」 is used to repeat what the other person just said in order to confirm it. The form 「とのことです」 is more like written language.',
                [
                    'Kalimat sebelum ということだ memakai bentuk biasa.',
                    'とのことです sering muncul di surat atau pesan: ○○さんから よろしくとの ことです.',
                ],
                [
                    'The sentence before ということだ takes the plain form.',
                    'とのことです often appears in letters and messages: ○○さんから よろしくとの ことです.',
                ],
                [
                    $this->ex('課長《かちょう》から 連絡《れんらく》が あって、今日《きょう》は 出張《しゅっちょう》から 戻《もど》らないと いう ことです。', 'Kachou kara renraku ga atte, kyou wa shucchou kara modoranai to iu koto desu.', 'Ada kabar dari kepala seksi bahwa hari ini beliau tidak kembali dari dinas luar.', 'The section chief called to say he will not be back from his business trip today.'),
                    $this->ex('ニュースに よると、明日《あした》は 大雪《おおゆき》に なると いう ことです。', 'Nyuusu ni yoru to, ashita wa ooyuki ni naru to iu koto desu.', 'Menurut berita, besok akan turun salju lebat.', 'According to the news, there will be heavy snow tomorrow.'),
                    $this->ex('先生《せんせい》は 少《すこ》し 遅《おく》れると いう ことですね。', 'Sensei wa sukoshi okureru to iu koto desu ne.', 'Jadi guru akan terlambat sedikit, begitu ya?', 'So the teacher will be a little late, is that right?'),
                ]
            ),

            $this->card(
                '…の／…の？ (bentuk akrab dari のですか)',
                '…の／…の？ (casual form of のですか)',
                'S 普通形《ふつうけい》 ＋ の／の？',
                'Bentuk tidak resmi dari 「…のですか」 yang dipakai ketika berbicara dengan orang yang akrab. Dengan nada naik berarti bertanya (…の？), dan pembicara juga memakai の untuk menjelaskan alasan atau keadaan dengan nada lembut.',
                'An informal form of 「…のですか」 used when talking with someone you are on familiar terms with. With a rising tone it asks a question (…の？), and speakers also use の to explain a reason or situation in a soft tone.',
                [
                    'Kata sifat-na dan kata benda memakai な sebelum の: 病気《びょうき》なの？',
                    'Dipakai terutama dalam percakapan; dalam tulisan formal pakai 〜のです.',
                ],
                [
                    'Na-adjectives and nouns take な before の: 病気《びょうき》なの？',
                    'Used mainly in speech; in formal writing use 〜のです.',
                ],
                [
                    $this->ex('どこへ 行《い》くの？……ちょっと 買《か》い物《もの》。', 'Doko e iku no? ...Chotto kaimono.', 'Mau ke mana? ……Belanja sebentar.', 'Where are you going? ...Just shopping.'),
                    $this->ex('元気《げんき》が ないね。どうしたの？……頭《あたま》が 痛《いた》いの。', 'Genki ga nai ne. Doushita no? ...Atama ga itai no.', 'Kamu lesu. Ada apa? ……Kepalaku sakit.', 'You look down. What is wrong? ...I have a headache.'),
                    $this->ex('あした 来《こ》ないの？……ええ、用事《ようじ》が あるの。', 'Ashita konai no? ...Ee, youji ga aru no.', 'Besok kamu tidak datang? ……Ya, ada urusan.', 'Aren\'t you coming tomorrow? ...No, I have something to do.'),
                ]
            ),

            $this->card(
                '〜ちゃう・〜とく・〜てる (bentuk lisan)',
                '〜ちゃう・〜とく・〜てる (spoken contractions)',
                'Vてしまう → Vちゃう／Vておく → Vとく／Vている → Vてる',
                'Dalam percakapan, tiga pola sering disingkat. 〜てしまう menjadi 〜ちゃう (〜でしまう → 〜じゃう), 〜ておく menjadi 〜とく (〜でおく → 〜どく), dan 〜ている menjadi 〜てる (〜でいる → 〜でる). Bentuk lampau dan lainnya mengikuti: 〜ちゃった, 〜といた, 〜てた.',
                'In speech, three patterns are often shortened. 〜てしまう becomes 〜ちゃう (〜でしまう → 〜じゃう), 〜ておく becomes 〜とく (〜でおく → 〜どく), and 〜ている becomes 〜てる (〜でいる → 〜でる). Other forms follow: 〜ちゃった, 〜といた, 〜てた.',
                [
                    'Hanya dipakai dalam percakapan sehari-hari dengan orang akrab; dalam tulisan dan situasi formal pakai bentuk lengkap.',
                    '見《み》ていない → 見《み》てない; 読《よ》んでいる → 読《よ》んでる; 行《い》ってしまいます → 行《い》っちゃいます.',
                ],
                [
                    'Only for everyday speech with people you know; use the full forms in writing and formal situations.',
                    '見《み》ていない → 見《み》てない; 読《よ》んでいる → 読《よ》んでる; 行《い》ってしまいます → 行《い》っちゃいます.',
                ],
                [
                    $this->ex('宿題《しゅくだい》を 忘《わす》れちゃった。', 'Shukudai o wasurechatta.', 'Aduh, PR-nya sampai terlupa.', 'Oh no, I forgot my homework.'),
                    $this->ex('先《さき》に 切符《きっぷ》を 買《か》っとくね。', 'Saki ni kippu o kattoku ne.', 'Saya belikan tiketnya lebih dulu, ya.', 'I will buy the tickets first, OK?'),
                    $this->ex('今《いま》、何《なに》を 読《よ》んでる？', 'Ima, nani o yonderu?', 'Sekarang kamu sedang membaca apa?', 'What are you reading now?'),
                ]
            ),

            $this->card(
                '〜(さ)せられる・〜される (kausatif-pasif)',
                '〜(さ)せられる・〜される (causative-passive)',
                'V I: ない形 −ない ＋ せられる／される ・ V II: ない形 −ない ＋ させられる ・ する → させられる ・ 来る → 来させられる',
                'Gabungan kausatif dan pasif: "dipaksa melakukan ~". Contoh dari 「先生は 太郎君に 掃除を させました」 (kausatif) menjadi 「太郎君は 先生に 掃除を させられました」 (kausatif-pasif). Pola dasarnya 「N1 は N2 に V させられる」; N2 kadang tidak disebut. Artinya N1 melakukan V bukan atas kehendak sendiri, melainkan karena disuruh orang lain.',
                'A combination of causative and passive: "was made to do ~". From 「先生は 太郎君に 掃除を させました」 (causative) we get 「太郎君は 先生に 掃除を させられました」 (causative-passive). The basic pattern is 「N1 は N2 に V させられる」; N2 is sometimes omitted. It means N1 does V not out of their own will but because someone else made them.',
                [
                    'Kelompok I punya dua bentuk: 歌《うた》わせられる／歌《うた》わされる. Verba berakhiran す tidak memakai bentuk pendek (話《はな》させられる saja).',
                    'Sering dipakai untuk menyatakan rasa tidak enak atau terpaksa.',
                ],
                [
                    'Group I has two forms: 歌《うた》わせられる／歌《うた》わされる. Verbs ending in す have no short form (only 話《はな》させられる).',
                    'Often used to express reluctance or an unwilling feeling.',
                ],
                [
                    $this->ex('子《こ》どもの とき、毎日《まいにち》 ピアノを 練習《れんしゅう》させられた。', 'Kodomo no toki, mainichi piano o renshuu saserareta.', 'Waktu kecil, saya dipaksa berlatih piano setiap hari.', 'As a child I was made to practise the piano every day.'),
                    $this->ex('昨日《きのう》の 忘年会《ぼうねんかい》では カラオケを 歌《うた》わされた。', 'Kinou no bounenkai de wa karaoke o utawasareta.', 'Di pesta akhir tahun kemarin, saya disuruh bernyanyi karaoke.', 'At yesterday\'s year-end party I was made to sing karaoke.'),
                    $this->ex('この 会議《かいぎ》では 毎月《まいつき》 研究《けんきゅう》の 結果《けっか》を 発表《はっぴょう》させられる。', 'Kono kaigi de wa maitsuki kenkyuu no kekka o happyou saserareru.', 'Dalam rapat ini kami diharuskan mempresentasikan hasil penelitian setiap bulan.', 'At this conference we are made to present our research results every month.'),
                ]
            ),

            $this->card(
                '〜である (gaya である)',
                '〜である (the である style)',
                'N／なA(〜な→) ＋ である ・ 〜ている → 〜ているのである',
                '「〜である」 sama maknanya dengan 「〜だ」 tetapi lebih formal; banyak dipakai dalam tulisan, terutama skripsi dan karangan ilmiah. Dalam gaya である, 「〜のだ」 menjadi 「〜のである」. Bentuk lampaunya 〜であった.',
                '「〜である」 means the same as 「〜だ」 but is more formal. It is often used in writing, particularly in dissertations and the like. In である style, 「〜のだ」 becomes 「〜のである」. The past form is 〜であった.',
                [
                    'Tidak dipakai dalam percakapan sehari-hari.',
                    'Bentuk negatif: 〜ではない.',
                ],
                [
                    'Not used in everyday conversation.',
                    'Negative: 〜ではない.',
                ],
                [
                    $this->ex('日本《にほん》の 首都《しゅと》は 東京《とうきょう》である。', 'Nihon no shuto wa Toukyou de aru.', 'Ibu kota Jepang adalah Tokyo.', 'The capital of Japan is Tokyo.'),
                    $this->ex('この 問題《もんだい》は 非常《ひじょう》に 重要《じゅうよう》である。', 'Kono mondai wa hijou ni juuyou de aru.', 'Masalah ini sangat penting.', 'This problem is extremely important.'),
                    $this->ex('彼《かれ》が 優勝《ゆうしょう》したのである。', 'Kare ga yuushou shita no de aru.', 'Dialah yang menjadi juara.', 'It was he who won the championship.'),
                ]
            ),

            $this->card(
                'Vます・いA(〜く)・なA／N(〜で) (bentuk penghubung)',
                'Vます・いA(〜く)・なA／N(〜で) (suspended form)',
                'V ます形(−ます)／いA: い → く／なA・N: で',
                'Kata kerja bentuk ます (tanpa ます) bisa dipakai menghubungkan kalimat: 「V1(ます形)、V2」, menyatakan urutan atau kejadian yang berlangsung bersamaan, sama seperti bentuk て. Kata kerja いる menjadi おり. Pada kata sifat dan kata benda, bentuk ini (い → く, なA／N + で) menyatakan hal-hal yang berdampingan atau sifat yang saling menambah.',
                'The suspended form of a verb (the ます-stem) links clauses in the form 「V1(ます-stem)、V2」, marking a succession or concurrent events, just like the て-form. The suspended form of いる is おり. With adjectives and nouns this form (い → く, なA／N + で) shows coexisting qualities.',
                [
                    'Bentuk ini dominan di tulisan; dalam percakapan dipakai bentuk て.',
                    'Contoh bentuk sambung kata sifat: 優《やさ》しく、頭《あたま》が よく、すばらしい 人《ひと》だ.',
                ],
                [
                    'This form is mostly used in writing; speech uses the て-form.',
                    'Example with adjectives: 優《やさ》しく、頭《あたま》が よく、すばらしい 人《ひと》だ.',
                ],
                [
                    $this->ex('弟《おとうと》は 朝《あさ》 早《はや》く 起《お》き、走《はし》り、シャワーを 浴《あ》びる。', 'Otouto wa asa hayaku oki, hashiri, shawaa o abiru.', 'Adik saya bangun pagi-pagi, berlari, lalu mandi.', 'My brother gets up early in the morning, runs, and then showers.'),
                    $this->ex('この 部屋《へや》は 広《ひろ》く、明《あか》るい。', 'Kono heya wa hiroku, akarui.', 'Ruangan ini luas dan terang.', 'This room is spacious and bright.'),
                    $this->ex('両親《りょうしん》は 福岡《ふくおか》に おり、私《わたし》は 東京《とうきょう》に 住《す》んで います。', 'Ryoushin wa Fukuoka ni ori, watashi wa Toukyou ni sunde imasu.', 'Orang tua saya tinggal di Fukuoka, sedangkan saya tinggal di Tokyo.', 'My parents are in Fukuoka, and I live in Tokyo.'),
                    $this->ex('兄《あに》は 医者《いしゃ》で、姉《あね》は 教師《きょうし》です。', 'Ani wa isha de, ane wa kyoushi desu.', 'Kakak laki-laki saya dokter, kakak perempuan saya guru.', 'My older brother is a doctor and my older sister is a teacher.'),
                ]
            ),

            $this->card(
                '〜(た)がる／〜(た)がっている (perasaan orang lain)',
                '〜(た)がる／〜(た)がっている (other people\'s feelings)',
                'Vます形 ＋ たがる ・ いA(−い)／なA(−な) ＋ がる／がっている',
                'Bentuk 「N が 〜(た)がる」 dipasang pada kata sifat perasaan untuk menyatakan bahwa perasaan orang lain (bukan pembicara) tampak dari ekspresi atau tingkah lakunya. Ketika orang tampak menginginkan sesuatu (〜たい), dipakai 〜(た)がる. 〜(た)がる menyatakan kecenderungan yang biasa; kalau terjadi sekarang dipakai 〜(た)がっている.',
                'The form 「N が 〜(た)がる」 attaches to an adjective of emotion to say that someone else\'s (not the speaker\'s) emotion is showing in their expression or behaviour. When a person seems to want something (〜たい), 〜(た)がる is used. 〜(た)がる expresses a habitual tendency; for what is happening now, 〜(た)がっている is used.',
                [
                    'Untuk perasaan pembicara sendiri dipakai 〜たい／〜い (ほしい, くやしい).',
                    'Objek memakai を: おもちゃを ほしがる.',
                ],
                [
                    'For the speaker\'s own feelings use 〜たい／〜い (ほしい, くやしい).',
                    'The object takes を: おもちゃを ほしがる.',
                ],
                [
                    $this->ex('妹《いもうと》は 新《あたら》しい 靴《くつ》を 欲《ほ》しがって いる。', 'Imouto wa atarashii kutsu o hoshigatte iru.', 'Adik perempuan saya tampak menginginkan sepatu baru.', 'My younger sister seems to want new shoes.'),
                    $this->ex('息子《むすこ》は 野菜《やさい》を 食《た》べたがらない。', 'Musuko wa yasai o tabegaranai.', 'Anak laki-laki saya tidak mau makan sayur.', 'My son does not want to eat vegetables.'),
                    $this->ex('彼《かれ》は いつも 海外《かいがい》へ 行《い》きたがる。', 'Kare wa itsumo kaigai e ikitagaru.', 'Dia selalu ingin pergi ke luar negeri.', 'He is always wanting to go abroad.'),
                ]
            ),

            $this->card(
                '…こと・…ということ (membuat kalimat menjadi benda)',
                '…こと・…ということ (nominalising a sentence)',
                'S 普通形《ふつうけい》 ＋ ｛という｝こと ＋ 格助詞《かくじょし》 ・ なA ＋ なこと／であること',
                'Ketika sebuah kalimat diberi partikel atau bagian lain, 「…こと + partikel」 mengubah kalimat itu menjadi kata benda. Kalimat sebelum こと berbentuk biasa; kalau berakhir dengan kata sifat-na, dipakai 「なA + なこと」 atau 「なA + であること」. Bila kalimatnya panjang dan rumit, 「という」 harus dipasang di depan こと agar jelas.',
                'When a particle or other part of speech is added, 「…こと + particle」 turns the sentence into a noun. The sentence before こと takes the plain form; when it ends in a na-adjective, 「なA + なこと」 or 「なA + であること」 is used. When the sentence is long and complicated, 「という」 has to be placed in front of こと to nominalise it.',
                [
                    'Dengan だ／であった, bentuk という ことを untuk kalimat panjang: 海《うみ》だったということは 知《し》られて いない.',
                    'Berbeda dengan の (nominalisasi lisan), こと lebih formal dan dipakai dengan kata kerja seperti 知《し》っている, わかる, 気《き》がつく.',
                ],
                [
                    'With だ／であった, the という ことを form for long sentences: 海《うみ》だったということは 知《し》られて いない.',
                    'Unlike の (spoken nominalisation), こと is more formal and goes with verbs like 知《し》っている, わかる, 気《き》がつく.',
                ],
                [
                    $this->ex('彼《かれ》が 来《こ》ない ことを みんな 知《し》って いる。', 'Kare ga konai koto o minna shitte iru.', 'Semua orang tahu bahwa dia tidak akan datang.', 'Everyone knows that he is not coming.'),
                    $this->ex('この 薬《くすり》が 安全《あんぜん》な ことが わかった。', 'Kono kusuri ga anzen na koto ga wakatta.', 'Terbukti bahwa obat ini aman.', 'It became clear that this medicine is safe.'),
                    $this->ex('この 町《まち》が 昔《むかし》 港《みなと》だったと いう ことは、あまり 知《し》られて いない。', 'Kono machi ga mukashi minato datta to iu koto wa, amari shirarete inai.', 'Tidak banyak yang tahu bahwa kota ini dulunya pelabuhan.', 'It is not widely known that this town used to be a port.'),
                ]
            ),

            $this->card(
                'N１の N２ (apposisi)',
                'N１の N２ (apposition)',
                'N１ の N２ (N１ ＝ N２)',
                'Dipakai ketika N1 dan N2 menunjuk hal yang sama. N1 adalah kata benda yang menunjukkan atribut N2 (jabatan, hubungan, nama hari) dan memberi informasi tambahan tentangnya. Bisa juga dinyatakan dengan 「N1 である N2」.',
                'Shows that N1 and N2 are identical. N1 is a noun that gives an attribute of N2 (a title, relation, or day) and adds information about it. It can also be expressed as 「N1 である N2」.',
                [
                    'Berbeda dengan の kepemilikan (私の 本): di sini N1 = N2.',
                    'Sering dipakai saat memperkenalkan orang atau menyebut hari dan tanggal.',
                ],
                [
                    'Different from possessive の (私の 本): here N1 = N2.',
                    'Often used when introducing people or giving days and dates.',
                ],
                [
                    $this->ex('社長《しゃちょう》の 山田《やまだ》さんを ご紹介《しょうかい》します。', 'Shachou no Yamada-san o goshoukai shimasu.', 'Saya perkenalkan Direktur kami, Pak Yamada.', 'Let me introduce our president, Mr. Yamada.'),
                    $this->ex('来週《らいしゅう》の 水曜日《すいようび》に お会《あ》いしましょう。', 'Raishuu no suiyoubi ni oai shimashou.', 'Mari bertemu hari Rabu minggu depan.', 'Let us meet next Wednesday.'),
                    $this->ex('友達《ともだち》の 鈴木《すずき》さんは 医者《いしゃ》です。', 'Tomodachi no Suzuki-san wa isha desu.', 'Teman saya, Suzuki, adalah seorang dokter.', 'My friend Suzuki is a doctor.'),
                ]
            ),

            $this->card(
                'Vましたら、〜・Vまして、〜 (bentuk sopan たら／て)',
                'Vましたら、〜・Vまして、〜 (polite たら／て)',
                'V 丁寧形《ていねいけい》(〜まし) ＋ ｛たら・て｝、〜',
                'たら dan bentuk て bisa dibuat sopan dengan 〜ましたら dan 〜まして. Dipakai terutama dalam bahasa bisnis dan situasi resmi, misalnya untuk menyampaikan rencana tindakan atau berterima kasih.',
                'たら and the て-form can be made polite as 〜ましたら and 〜まして. Used mainly in business language and formal situations, for example to state what one will do or to express thanks.',
                [
                    '〜ましたら: ketika ~ terjadi (setelah ~). 〜まして: bentuk sopan penghubung bagian pertama kalimat.',
                    'Sering dipakai bersama させていただきます atau ください.',
                ],
                [
                    '〜ましたら: when／once ~ happens. 〜まして: the polite linking form of the first clause.',
                    'Often paired with させていただきます or ください.',
                ],
                [
                    $this->ex('準備《じゅんび》が できましたら、ご連絡《れんらく》します。', 'Junbi ga dekimashitara, gorenraku shimasu.', 'Setelah persiapan selesai, saya akan menghubungi Anda.', 'I will contact you once the preparations are ready.'),
                    $this->ex('本日《ほんじつ》は お忙《いそが》しい 中《なか》、お越《こ》し くださいまして、ありがとうございます。', 'Honjitsu wa oisogashii naka, okoshi kudasaimashite, arigatou gozaimasu.', 'Terima kasih telah berkenan hadir hari ini di tengah kesibukan Anda.', 'Thank you very much for coming today despite your busy schedule.'),
                    $this->ex('到着《とうちゃく》しましたら、お電話《でんわ》ください。', 'Touchaku shimashitara, odenwa kudasai.', 'Mohon telepon kami setelah Anda tiba.', 'Please call us once you have arrived.'),
                ]
            ),
        ];
    }
}
