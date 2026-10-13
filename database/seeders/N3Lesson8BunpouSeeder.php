<?php

namespace Database\Seeders;

/**
 * Tata Bahasa N3 Pelajaran 8 (7 kartu, dengan dialog).
 *   php artisan db:seed --class=N3Lesson8BunpouSeeder
 */
class N3Lesson8BunpouSeeder extends N3GrammarSeeder
{
    protected function unitOrder(): int
    {
        return 8;
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
                $this->dl('A', '昨日《きのう》、出《で》かけて いる あいだに、誰《だれ》か 来《き》ましたか。', 'Kinou, dekakete iru aida ni, dare ka kimashita ka.', 'Kemarin selama saya pergi, apakah ada yang datang?', 'Did anyone come yesterday while I was out?'),
                $this->dl('B', 'ええ、留守《るす》の あいだに 宅配便《たくはいびん》が 届《とど》きましたよ。', 'Ee, rusu no aida ni takuhaibin ga todokimashita yo.', 'Ya, selama Anda tidak ada, paket kiriman datang.', 'Yes, a delivery came while you were out.'),
                $this->dl('A', '荷物《にもつ》は どこですか。', 'Nimotsu wa doko desu ka.', 'Di mana paketnya?', 'Where is the package?'),
                $this->dl('B', '玄関《げんかん》に 置《お》いて あります。', 'Genkan ni oite arimasu.', 'Saya taruh di pintu depan.', 'I left it at the front door.'),
            ],
            2 => [
                $this->dl('A', '報告書《ほうこくしょ》は いつまでに 出《だ》せば いいですか。', 'Houkokusho wa itsu made ni dasebaii desu ka.', 'Laporannya harus diserahkan paling lambat kapan?', 'By when should I submit the report?'),
                $this->dl('B', '金曜日《きんようび》までに お願《ねが》いします。', 'Kinyoubi made ni onegai shimasu.', 'Mohon paling lambat hari Jumat.', 'By Friday, please.'),
                $this->dl('A', '今日《きょう》は 何時《なんじ》まで 会社《かいしゃ》に いますか。', 'Kyou wa nanji made kaisha ni imasu ka.', 'Hari ini Anda di kantor sampai jam berapa?', 'Until what time are you at the office today?'),
                $this->dl('B', '夜《よる》まで いますから、いつでも どうぞ。', 'Yoru made imasu kara, itsu demo douzo.', 'Sampai malam, jadi silakan kapan saja.', 'I will be here until evening, so come any time.'),
            ],
            3 => [
                $this->dl('A', '田中《たなか》さんは どの 人《ひと》ですか。', 'Tanaka-san wa dono hito desu ka.', 'Yang mana Pak Tanaka?', 'Which one is Mr. Tanaka?'),
                $this->dl('B', 'ほら、あの めがねを かけた 人《ひと》ですよ。', 'Hora, ano megane o kaketa hito desu yo.', 'Itu, yang berkacamata.', 'Look, the one wearing glasses.'),
                $this->dl('A', '青《あお》い シャツを 着《き》た 人《ひと》ですか。', 'Aoi shatsu o kita hito desu ka.', 'Yang memakai kemeja biru?', 'The one in the blue shirt?'),
                $this->dl('B', 'いいえ、その 隣《となり》の 帽子《ぼうし》を かぶった 人《ひと》です。', 'Iie, sono tonari no boushi o kabutta hito desu.', 'Bukan, orang di sebelahnya yang bertopi.', 'No, the one next to him wearing a hat.'),
            ],
            4 => [
                $this->dl('A', '日本《にほん》では お正月《しょうがつ》に 何《なに》を 食《た》べますか。', 'Nihon de wa oshougatsu ni nani o tabemasu ka.', 'Di Jepang, apa yang dimakan saat Tahun Baru?', 'What do people eat at New Year in Japan?'),
                $this->dl('B', 'お雑煮《ぞうに》を 食《た》べますが、地方《ちほう》によって 作《つく》り方《かた》が 違《ちが》います。', 'Ozouni o tabemasu ga, chihou ni yotte tsukurikata ga chigaimasu.', 'Ozoni, tetapi cara membuatnya berbeda tiap daerah.', 'Ozoni, but how it is made differs by region.'),
                $this->dl('A', '家《いえ》によっても 違《ちが》いますか。', 'Ie ni yotte mo chigaimasu ka.', 'Apakah berbeda tiap keluarga juga?', 'Does it differ from family to family too?'),
                $this->dl('B', 'ええ、家《いえ》によって 味《あじ》も さまざまです。', 'Ee, ie ni yotte aji mo samazama desu.', 'Ya, rasanya pun beraneka ragam tiap rumah.', 'Yes, the taste varies from home to home too.'),
            ],
            5 => [
                $this->dl('A', 'あれ、エアコンが つけた ままだよ。', 'Are, eakon ga tsuketa mama da yo.', 'Eh, AC-nya masih menyala.', 'Hey, the air conditioner is still on.'),
                $this->dl('B', 'ごめん、つけた まま 出《で》かけて しまった。', 'Gomen, tsuketa mama dekakete shimatta.', 'Maaf, aku pergi tanpa mematikannya.', 'Sorry, I went out and left it on.'),
                $this->dl('A', '電気代《でんきだい》が もったいないよ。', 'Denkidai ga mottainai yo.', 'Sayang tagihan listriknya.', 'What a waste of electricity.'),
                $this->dl('B', '次《つぎ》から 気《き》を つけるね。', 'Tsugi kara ki o tsukeru ne.', 'Mulai lain kali aku akan lebih hati-hati.', 'I will be more careful from now on.'),
            ],
            6 => [
                $this->dl('A', 'どうして 日本語《にほんご》を 勉強《べんきょう》して いるんですか。', 'Doushite nihongo o benkyou shite iru n desu ka.', 'Mengapa Anda belajar bahasa Jepang?', 'Why are you studying Japanese?'),
                $this->dl('B', '日本《にほん》で 働《はたら》きたいからです。', 'Nihon de hatarakitai kara desu.', 'Karena saya ingin bekerja di Jepang.', 'Because I want to work in Japan.'),
                $this->dl('A', '日本《にほん》で 働《はたら》きたいのは どうしてですか。', 'Nihon de hatarakitai no wa doushite desu ka.', 'Mengapa ingin bekerja di Jepang?', 'Why do you want to work in Japan?'),
                $this->dl('B', '日本《にほん》の ものづくりを 学《まな》びたいからです。', 'Nihon no monozukuri o manabitai kara desu.', 'Karena saya ingin mempelajari cara Jepang membuat produk.', 'Because I want to learn how Japan makes things.'),
            ],
            7 => [
                $this->dl('A', '待《ま》ち 合《あ》わせの 人《ひと》、どんな 人《ひと》ですか。', 'Machiawase no hito, donna hito desu ka.', 'Orang yang akan Anda temui itu seperti apa?', 'What does the person you are meeting look like?'),
                $this->dl('B', '背《せ》が 高《たか》くて、短《みじか》い 髪《かみ》を して います。', 'Se ga takakute, mijikai kami o shite imasu.', 'Tinggi dan berambut pendek.', 'Tall, with short hair.'),
                $this->dl('A', '目《め》は？', 'Me wa?', 'Matanya?', 'And his eyes?'),
                $this->dl('B', '大《おお》きな 目《め》を して いて、いつも 笑《わら》って います。', 'Ookina me o shite ite, itsumo waratte imasu.', 'Matanya besar dan selalu tersenyum.', 'He has big eyes and is always smiling.'),
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    protected function cards(): array
    {
        return [
            $this->card(
                'Vている／Nの あいだ(に)',
                'Vている／Nの あいだ(に) (while ~)',
                'V ている／N の ＋ あいだ／あいだに',
                '「Xあいだ、Y」: selama X berlangsung, Y juga berlangsung terus pada waktu yang sama. 「Xあいだに、Y」: pada saat X berlangsung, terjadi satu peristiwa Y yang berdiri sendiri.',
                '「X あいだ、Y」: while X continues, Y also goes on at the same time. 「X あいだに、Y」: while X is going on, a single isolated event Y happens.',
                ['あいだ: dua keadaan sama-sama berlanjut; あいだに: satu kejadian di tengah-tengahnya.', 'Dengan kata benda dipakai Nの: 休《やす》みの あいだ.'],
                ['あいだ: both situations continue; あいだに: one event happens within the period.', 'With nouns use Nの: 休《やす》みの あいだ.'],
                [
                    $this->ex('子《こ》どもが 寝《ね》て いる あいだ、静《しず》かに 本《ほん》を 読《よ》んで いました。', 'Kodomo ga nete iru aida, shizuka ni hon o yonde imashita.', 'Selama anak tidur, saya membaca buku dengan tenang.', 'While the child was sleeping, I read a book quietly.'),
                    $this->ex('留守《るす》の あいだに、荷物《にもつ》が 届《とど》いた。', 'Rusu no aida ni, nimotsu ga todoita.', 'Selama saya tidak di rumah, paket datang.', 'A package arrived while I was out.'),
                    $this->ex('夏休《なつやす》みの あいだ、毎朝《まいあさ》 プールで 泳《およ》いで いました。', 'Natsuyasumi no aida, maiasa puuru de oyoide imashita.', 'Selama liburan musim panas, saya berenang di kolam setiap pagi.', 'Throughout the summer holidays I swam in the pool every morning.'),
                ]
            ),

            $this->card(
                'Nまで／Vるまで と Nまでに／Vるまでに',
                'まで vs までに (until / by)',
                'N／V 辞書形《じしょけい》 ＋ まで／までに',
                '「Xまで、Y」: X adalah batas akhir dari tindakan atau keadaan Y yang berlangsung terus. 「Xまでに、Y」: X juga batas, tetapi Y adalah kejadian sekali yang terjadi sebelum X. X bisa berupa waktu atau peristiwa.',
                '「X まで、Y」: X is the final limit of a continuing action or state Y. 「X までに、Y」: X is also a limit, but Y is a one-off event that happens before X. X can be a time or an event.',
                ['まで = terus sampai; までに = selesai sebelum.', 'Kata kerja sebelum まで／までに berbentuk kamus.'],
                ['まで = continues up to; までに = done by.', 'The verb before まで／までに is in dictionary form.'],
                [
                    $this->ex('会議《かいぎ》が 始《はじ》まるまで、ロビーで 待《ま》って います。', 'Kaigi ga hajimaru made, robii de matte imasu.', 'Saya menunggu di lobi sampai rapat dimulai.', 'I will wait in the lobby until the meeting starts.'),
                    $this->ex('来週《らいしゅう》の 金曜日《きんようび》までに、レポートを 出《だ》して ください。', 'Raishuu no kinyoubi made ni, repooto o dashite kudasai.', 'Serahkan laporan paling lambat Jumat minggu depan.', 'Please hand in the report by next Friday.'),
                    $this->ex('帰《かえ》る までに、部屋《へや》を 片《かた》づけて おきます。', 'Kaeru made ni, heya o katazukete okimasu.', 'Sebelum pulang, saya akan merapikan kamar.', 'I will tidy the room before I leave.'),
                ]
            ),

            $this->card(
                'Vた N (penerang kata benda dengan bentuk た)',
                'Vた N (た-form noun modifier)',
                'V た形 ＋ N',
                'Kata kerja bentuk 〜ている yang menunjukkan keadaan hasil dari suatu tindakan atau perubahan dapat diganti bentuk 〜た saat menerangkan kata benda: めがねを かけている 人 → めがねを かけた 人. Namun bila 〜ている menunjukkan tindakan yang sedang berlangsung, mengganti dengan 〜た mengubah arti.',
                'When a 〜ている verb showing a state resulting from an action or change modifies a noun, it can be replaced by the 〜た form: めがねを かけている 人 → めがねを かけた 人. But if 〜ている shows an action in progress, replacing it with 〜た changes the meaning.',
                ['Keadaan hasil: 曲《ま》がって いる 線 = 曲《ま》がった 線.', 'Tindakan berlangsung: 働《はたら》いて いる 友達 (sekarang) ≠ 働《はたら》いた 友達 (dulu).'],
                ['Resulting state: 曲《ま》がって いる 線 = 曲《ま》がった 線.', 'Ongoing action: 働《はたら》いて いる 友達 (now) ≠ 働《はたら》いた 友達 (formerly).'],
                [
                    $this->ex('帽子《ぼうし》を かぶった 男《おとこ》の 人《ひと》が 入口《いりぐち》に 立《た》って います。', 'Boushi o kabutta otoko no hito ga iriguchi ni tatte imasu.', 'Seorang pria bertopi berdiri di pintu masuk.', 'A man wearing a hat is standing at the entrance.'),
                    $this->ex('割《わ》れた コップは ごみ箱《ばこ》に 捨《す》てて ください。', 'Wareta koppu wa gomibako ni sutete kudasai.', 'Buang gelas yang pecah ke tempat sampah.', 'Please throw the broken glass in the bin.'),
                    $this->ex('東京《とうきょう》で 働《はたら》いた 友達《ともだち》は 今《いま》は 大阪《おおさか》に 住《す》んで います。', 'Toukyou de hataraita tomodachi wa ima wa Oosaka ni sunde imasu.', 'Teman yang dulu bekerja di Tokyo sekarang tinggal di Osaka.', 'My friend who used to work in Tokyo now lives in Osaka.'),
                ]
            ),

            $this->card(
                'Nによって (bergantung pada N)',
                'Nによって (depending on N)',
                'N ＋ によって ＋ 違《ちが》う／変《か》わる／さまざまだ',
                '「XによってY」: Y berbeda-beda tergantung jenis X. Predikat Y biasanya 違《ちが》う (berbeda), 変《か》わる (berubah), atau さまざまだ (beraneka ragam).',
                '「X によって Y」: Y varies depending on the type of X. The predicate Y usually uses 違《ちが》う (differs), 変《か》わる (changes), or さまざまだ (varies).',
                ['Juga dapat berbentuk Nによって は: 人《ひと》によっては …', 'Bentuk lisan yang mirip: N しだいで.'],
                ['Can also appear as Nによっては: 人《ひと》によっては …', 'A similar spoken form is N しだいで.'],
                [
                    $this->ex('国《くに》によって 習慣《しゅうかん》が 違《ちが》います。', 'Kuni ni yotte shuukan ga chigaimasu.', 'Kebiasaan berbeda tergantung negara.', 'Customs differ from country to country.'),
                    $this->ex('天気《てんき》によって、予定《よてい》が 変《か》わる かも しれません。', 'Tenki ni yotte, yotei ga kawaru kamo shiremasen.', 'Rencana mungkin berubah tergantung cuaca.', 'The plans may change depending on the weather.'),
                    $this->ex('人《ひと》によって 考《かんが》え方《かた》は さまざまです。', 'Hito ni yotte kangaekata wa samazama desu.', 'Cara berpikir beraneka ragam tergantung orangnya.', 'Ways of thinking vary from person to person.'),
                ]
            ),

            $this->card(
                'Vたまま／Nのまま (dalam keadaan tetap ~)',
                'Vたまま／Nのまま (leaving as is)',
                'V た形／N の ＋ まま',
                'Menunjukkan bahwa Y dilakukan dalam keadaan yang tetap seperti setelah suatu tindakan (V) atau seperti yang ditunjukkan kata benda (N). Dipakai untuk hal yang biasanya tidak dilakukan dalam keadaan itu.',
                'Shows that Y is done while remaining in the state left after an action (V) or the state shown by a noun (N). It is used when Y is not normally done in that state.',
                ['Sering bernada kurang pantas atau kurang baik: つけた まま 寝《ね》る.', 'Kata benda: 靴《くつ》の まま (tetap bersepatu).'],
                ['Often implies something improper or undesirable: つけた まま 寝《ね》る.', 'Nouns: 靴《くつ》の まま (still in shoes).'],
                [
                    $this->ex('電気《でんき》を つけた まま 寝《ね》て しまいました。', 'Denki o tsuketa mama nete shimaimashita.', 'Saya tertidur dengan lampu masih menyala.', 'I fell asleep with the light on.'),
                    $this->ex('靴《くつ》の まま、部屋《へや》に 入《はい》らないで ください。', 'Kutsu no mama, heya ni hairanaide kudasai.', 'Jangan masuk ke kamar dengan memakai sepatu.', 'Please do not enter the room with your shoes on.'),
                    $this->ex('窓《まど》を 開《あ》けた まま 出《で》かけて、雨《あめ》が 入《はい》って しまった。', 'Mado o aketa mama dekakete, ame ga haitte shimatta.', 'Saya pergi dengan jendela terbuka dan hujan pun masuk.', 'I went out leaving the window open and the rain came in.'),
                ]
            ),

            $this->card(
                '…からだ (alasan di akhir kalimat)',
                '…からだ (stating the reason)',
                'S 普通形《ふつうけい》 ＋ からだ／…のは、S 普通形 ＋ からだ',
                'Cara menyatakan sebab atau alasan suatu hal, terutama saat menjawab pertanyaan "mengapa". Bila hasil disebut lebih dulu, kalimat menjadi 「…のは、…からだ」. Berbeda dengan ので, bentuk 〜のでだ tidak bisa dipakai.',
                'A way to state the cause of or reason for something, especially when answering a "why" question. When the result is stated first, the sentence becomes 「…のは、…からだ」. Unlike ので, the form 〜のでだ cannot be used.',
                ['Bentuk sopan: 〜からです.', 'Pola 「…のは、…からだ」: hasil dulu, lalu alasan.'],
                ['Polite form: 〜からです.', 'The pattern 「…のは、…からだ」 gives the result first and then the reason.'],
                [
                    $this->ex('どうして 遅《おく》れたんですか。…電車《でんしゃ》が 止《と》まったからです。', 'Doushite okureta n desu ka. ...Densha ga tomatta kara desu.', 'Mengapa terlambat? ...Karena kereta berhenti.', 'Why were you late? ...Because the train stopped.'),
                    $this->ex('日本語《にほんご》を 勉強《べんきょう》して いるのは、日本《にほん》で 働《はたら》きたいからだ。', 'Nihongo o benkyou shite iru no wa, Nihon de hatarakitai kara da.', 'Saya belajar bahasa Jepang karena ingin bekerja di Jepang.', 'The reason I am studying Japanese is that I want to work in Japan.'),
                    $this->ex('彼《かれ》が 怒《おこ》ったのは、約束《やくそく》を 守《まも》らなかったからだ。', 'Kare ga okotta no wa, yakusoku o mamoranakatta kara da.', 'Dia marah karena janji tidak ditepati.', 'He got angry because the promise was not kept.'),
                ]
            ),

            $this->card(
                '髪／目／形 を している (ciri tampilan)',
                '髪／目／形 を している (describing appearance)',
                'N(髪《かみ》・目《め》・形《かたち》・色《いろ》…) ＋ を している',
                'Ungkapan untuk menyebut ciri tampilan orang atau benda: rambut, mata, bentuk, warna. Kata benda diterangkan dengan kata sifat atau ungkapan perumpamaan, lalu ditutup を している.',
                'An expression for describing the visual features of a person or thing: hair, eyes, shape, colour. The noun is qualified by an adjective or a simile and closed with を している.',
                ['Bisa memakai perumpamaan: 帽子《ぼうし》みたいな 形《かたち》を している.', 'Bentuk sopan: 〜を して います.'],
                ['A simile can be used: 帽子《ぼうし》みたいな 形《かたち》を している.', 'Polite form: 〜を して います.'],
                [
                    $this->ex('妹《いもうと》は 長《なが》い 髪《かみ》を して います。', 'Imouto wa nagai kami o shite imasu.', 'Adik perempuan saya berambut panjang.', 'My younger sister has long hair.'),
                    $this->ex('この 猫《ねこ》は 青《あお》い 目《め》を して いる。', 'Kono neko wa aoi me o shite iru.', 'Kucing ini bermata biru.', 'This cat has blue eyes.'),
                    $this->ex('この お菓子《かし》は 星《ほし》の 形《かたち》を して いて、かわいいです。', 'Kono okashi wa hoshi no katachi o shite ite, kawaii desu.', 'Kue ini berbentuk bintang dan lucu.', 'This sweet is star-shaped and cute.'),
                ]
            ),
        ];
    }
}
