<?php

namespace Database\Seeders;

/**
 * Tata Bahasa N3 Pelajaran 1 (9 kartu, dengan dialog).
 *   php artisan db:seed --class=N3Lesson1BunpouSeeder
 */
class N3Lesson1BunpouSeeder extends N3GrammarSeeder
{
    protected function unitOrder(): int
    {
        return 1;
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
                $this->dl('A', 'すみません、ちょっと 手伝《てつだ》って もらえませんか。', 'Sumimasen, chotto tetsudatte moraemasen ka.', 'Maaf, bisakah Anda membantu saya sebentar?', 'Excuse me, could you give me a hand for a moment?'),
                $this->dl('B', 'はい、何《なに》を すれば いいですか。', 'Hai, nani o sureba ii desu ka.', 'Ya, apa yang harus saya lakukan?', 'Sure, what should I do?'),
                $this->dl('A', 'この 荷物《にもつ》を 二階《にかい》まで 運《はこ》んで いただけませんか。', 'Kono nimotsu o nikai made hakonde itadakemasen ka.', 'Maukah Anda membawa barang ini sampai lantai dua?', 'Would you carry this luggage up to the second floor?'),
                $this->dl('B', 'わかりました。お安《やす》い 御用《ごよう》です。', 'Wakarimashita. Oyasui goyou desu.', 'Baik. Dengan senang hati.', 'Of course. Happy to.'),
            ],
            2 => [
                $this->dl('A', '新《あたら》しい 事務所《じむしょ》は どうですか。', 'Atarashii jimusho wa dou desu ka.', 'Bagaimana kantor yang baru?', 'How is the new office?'),
                $this->dl('B', '広《ひろ》くて、ホテルのようですよ。', 'Hirokute, hoteru no you desu yo.', 'Luas, seperti hotel.', 'It is spacious, like a hotel.'),
                $this->dl('A', 'へえ、うらやましいです。', 'Hee, urayamashii desu.', 'Wah, saya iri.', 'Wow, I am jealous.'),
                $this->dl('B', '社長《しゃちょう》の 部屋《へや》は 美術館《びじゅつかん》のような 雰囲気《ふんいき》ですよ。', 'Shachou no heya wa bijutsukan no you na fun\'iki desu yo.', 'Ruang direktur suasananya seperti museum.', 'The president\'s room has an atmosphere like a museum.'),
            ],
            3 => [
                $this->dl('A', 'ご 趣味《しゅみ》は 何《なん》ですか。', 'Go shumi wa nan desu ka.', 'Apa hobi Anda?', 'What is your hobby?'),
                $this->dl('B', '写真《しゃしん》を 撮《と》る ことです。散歩《さんぽ》しながら 撮《と》る ことが 好《す》きなんです。', 'Shashin o toru koto desu. Sanpo shinagara toru koto ga suki nan desu.', 'Memotret. Saya suka memotret sambil berjalan-jalan.', 'Taking photos. I like taking them while I take a walk.'),
                $this->dl('A', '私《わたし》は 料理《りょうり》を 作《つく》る ことが 好《す》きです。', 'Watashi wa ryouri o tsukuru koto ga suki desu.', 'Saya suka memasak.', 'I like cooking.'),
                $this->dl('B', '料理《りょうり》が できる ことは 将来《しょうらい》 役《やく》に 立《た》ちますね。', 'Ryouri ga dekiru koto wa shourai yaku ni tachimasu ne.', 'Bisa memasak itu berguna untuk masa depan, ya.', 'Being able to cook will come in handy in the future.'),
            ],
            4 => [
                $this->dl('A', '日本《にほん》では お正月《しょうがつ》に 食《た》べる 料理《りょうり》を 何《なん》と 言《い》いますか。', 'Nihon de wa oshougatsu ni taberu ryouri o nan to iimasu ka.', 'Di Jepang, masakan yang dimakan saat Tahun Baru disebut apa?', 'In Japan, what do you call the food eaten at New Year?'),
                $this->dl('B', 'おせち料理《りょうり》と 言《い》います。', 'Osechi ryouri to iimasu.', 'Namanya osechi ryouri.', 'It is called osechi ryouri.'),
                $this->dl('A', '箱《はこ》に きれいに 詰《つ》めて ありますよね。', 'Hako ni kirei ni tsumete arimasu yo ne.', 'Dikemas rapi di dalam kotak, ya.', 'It is neatly packed in boxes, isn\'t it?'),
                $this->dl('B', 'ええ、あの 箱《はこ》は お重《じゅう》と 言《い》うんですよ。', 'Ee, ano hako wa ojuu to iu n desu yo.', 'Ya, kotak itu disebut ojuu.', 'Yes, that box is called an ojuu.'),
            ],
            5 => [
                $this->dl('A', '昨日《きのう》、田中《たなか》という 人《ひと》が 訪《たず》ねて 来《き》ましたよ。', 'Kinou, Tanaka toiu hito ga tazunete kimashita yo.', 'Kemarin ada orang bernama Tanaka yang datang.', 'Yesterday a person called Tanaka came to see you.'),
                $this->dl('B', '田中《たなか》さん…？どんな 方《かた》ですか。', 'Tanaka-san...? Donna kata desu ka.', 'Tanaka...? Orang yang seperti apa?', 'Tanaka...? What kind of person?'),
                $this->dl('A', '「青空《あおぞら》商事《しょうじ》」という 会社《かいしゃ》の 方《かた》だそうです。', '"Aozora Shouji" toiu kaisha no kata da sou desu.', 'Katanya dari perusahaan bernama "Aozora Shoji".', 'He is said to be from a company called "Aozora Shoji".'),
                $this->dl('B', 'ああ、先月《せんげつ》 名刺《めいし》を 交換《こうかん》した 人《ひと》ですね。', 'Aa, sengetsu meishi o koukan shita hito desu ne.', 'Oh, orang yang bulan lalu bertukar kartu nama dengan saya.', 'Oh, that is the person I exchanged business cards with last month.'),
            ],
            6 => [
                $this->dl('A', '明日《あした》は 雨《あめ》が 降《ふ》っても、サッカーの 試合《しあい》が ありますか。', 'Ashita wa ame ga futte mo, sakkaa no shiai ga arimasu ka.', 'Besok, meski hujan, apakah pertandingan sepak bola tetap ada?', 'Will tomorrow\'s soccer match go ahead even if it rains?'),
                $this->dl('B', 'ええ、雨《あめ》が 降《ふ》っても 試合《しあい》は 行《おこな》います。', 'Ee, ame ga futte mo shiai wa okonaimasu.', 'Ya, meski hujan pertandingan tetap diadakan.', 'Yes, the match is held even if it rains.'),
                $this->dl('A', 'どんなに 寒《さむ》くても ですか。', 'Donna ni samukute mo desu ka.', 'Sedingin apa pun?', 'No matter how cold it is?'),
                $this->dl('B', 'はい、どんなに 寒《さむ》くても 中止《ちゅうし》には なりませんよ。', 'Hai, donna ni samukute mo chuushi ni wa narimasen yo.', 'Ya, sedingin apa pun tidak akan dibatalkan.', 'Yes, however cold it is, it will not be cancelled.'),
            ],
            7 => [
                $this->dl('A', '今夜《こんや》は 肉《にく》を 食《た》べに 行《い》きませんか。', 'Konya wa niku o tabe ni ikimasen ka.', 'Malam ini mau makan daging?', 'Shall we go out for meat tonight?'),
                $this->dl('B', '肉《にく》じゃなくて、魚《さかな》が いいなあ。', 'Niku janakute, sakana ga ii naa.', 'Bukan daging, saya lebih ingin ikan.', 'Not meat, I would rather have fish.'),
                $this->dl('A', 'じゃ、寿司《すし》に しましょうか。', 'Ja, sushi ni shimashou ka.', 'Kalau begitu, bagaimana kalau sushi?', 'Then how about sushi?'),
                $this->dl('B', '回《まわ》る 寿司《ずし》じゃなくて、カウンターの お店《みせ》に 行《い》きたいです。', 'Mawaru sushi janakute, kauntaa no omise ni ikitai desu.', 'Bukan sushi berputar, saya ingin ke kedai yang ada meja konternya.', 'Not conveyor-belt sushi, I want to go to a counter place.'),
            ],
            8 => [
                $this->dl('A', '顔色《かおいろ》が 悪《わる》いですね。どうしたのですか。', 'Kaoiro ga warui desu ne. Doushita no desu ka.', 'Wajah Anda pucat. Ada apa?', 'You look pale. What happened?'),
                $this->dl('B', '昨日《きのう》の 夜《よる》、ほとんど 寝《ね》て いないのです。', 'Kinou no yoru, hotondo nete inai no desu.', 'Semalam saya hampir tidak tidur.', 'I hardly slept last night.'),
                $this->dl('A', 'どうして 寝《ね》られなかったのですか。', 'Doushite nerarenakatta no desu ka.', 'Kenapa tidak bisa tidur?', 'Why could you not sleep?'),
                $this->dl('B', '隣《となり》の 部屋《へや》が うるさかったのです。', 'Tonari no heya ga urusakatta no desu.', 'Kamar sebelah berisik.', 'The room next door was noisy.'),
            ],
            9 => [
                $this->dl('A', '昨日《きのう》の セールは どうでしたか。', 'Kinou no seeru wa dou deshita ka.', 'Bagaimana obral kemarin?', 'How was yesterday\'s sale?'),
                $this->dl('B', 'すごかったですよ。店《みせ》の 前《まえ》に 人《ひと》が 何十人《なんじゅうにん》も 並《なら》んで いました。', 'Sugokatta desu yo. Mise no mae ni hito ga nanjuunin mo narande imashita.', 'Luar biasa. Puluhan orang mengantre di depan toko.', 'It was amazing. Dozens of people were lined up in front of the shop.'),
                $this->dl('A', '何時間《なんじかん》も 待《ま》ったんですか。', 'Nanjikan mo matta n desu ka.', 'Apakah Anda menunggu berjam-jam?', 'Did you wait for hours?'),
                $this->dl('B', 'ええ、二時間《にじかん》も 待《ま》って、やっと 買《か》えました。', 'Ee, nijikan mo matte, yatto kaemashita.', 'Ya, sampai dua jam menunggu, akhirnya bisa membeli.', 'Yes, I waited a full two hours before I could finally buy it.'),
            ],
        ];
    }

    protected function cards(): array
    {
        return [
            $this->card(
                'Vて もらえませんか／いただけませんか (permintaan sopan)',
                'Vて もらえませんか／いただけませんか (polite requests)',
                'Vて ＋ もらえませんか／いただけませんか／もらえないでしょうか／いただけないでしょうか',
                'Cara sopan meminta seseorang melakukan sesuatu untuk kita. Bentuknya: kata kerja bentuk-て + もらえませんか (sopan) atau いただけませんか (lebih hormat). Versi もらえないでしょうか dan いただけないでしょうか terdengar lebih halus lagi karena tidak langsung menuntut jawaban, cocok ketika permintaan itu merepotkan lawan bicara.',
                'A polite way to ask someone to do something for you. Form: verb て-form + もらえませんか (polite) or いただけませんか (more humble-respectful). The もらえないでしょうか and いただけないでしょうか versions sound softer still because they do not press for an answer, which suits requests that put the other person out.',
                [
                    'Urutan kesopanan: もらえませんか → いただけませんか → もらえないでしょうか → いただけないでしょうか.',
                    'Biasanya didahului すみませんが／申《もう》し訳《わけ》ありませんが untuk melembutkan permintaan.',
                ],
                [
                    'Politeness order: もらえませんか → いただけませんか → もらえないでしょうか → いただけないでしょうか.',
                    'Usually preceded by すみませんが／申《もう》し訳《わけ》ありませんが to soften the request.',
                ],
                [
                    $this->ex('すみません、この 書類《しょるい》を コピーして もらえませんか。', 'Sumimasen, kono shorui o kopii shite moraemasen ka.', 'Maaf, bisakah Anda memfotokopi dokumen ini?', 'Excuse me, could you copy this document?'),
                    $this->ex('来週《らいしゅう》の 会議《かいぎ》の 日程《にってい》を 教《おし》えて いただけませんか。', 'Raishuu no kaigi no nittei o oshiete itadakemasen ka.', 'Bolehkah saya diberi tahu jadwal rapat minggu depan?', 'Would you tell me the schedule of next week\'s meeting?'),
                    $this->ex('申《もう》し訳《わけ》ありませんが、荷物《にもつ》を 少《すこ》し 預《あず》かって いただけないでしょうか。', 'Moushiwake arimasen ga, nimotsu o sukoshi azukatte itadakenai deshouka.', 'Mohon maaf, apakah kiranya Anda bersedia menitipkan barang ini sebentar?', 'I am very sorry, but could you possibly look after my luggage for a moment?'),
                ]
            ),

            $this->card(
                'Nの ようだ／ような／ように (perumpamaan, contoh)',
                'Nの ようだ／ような／ように (simile, illustration)',
                'Nの ＋ ようだ／ような N／ように V・いA・なA',
                'Dipakai untuk menggambarkan sifat sesuatu dengan mengibaratkannya pada benda lain (perumpamaan). Di akhir kalimat memakai ようだ; saat menerangkan kata benda berubah menjadi ような; saat menerangkan kata kerja atau kata sifat berubah menjadi ように. ような juga sering dipakai untuk menyebut contoh: "sesuatu seperti ~".',
                'Used to describe something by likening it to something else (a simile). At the end of a sentence it is ようだ; before a noun it becomes ような; before a verb or adjective it becomes ように. ような is also often used to give an example: "something like ~".',
                [
                    'Penggambaran contoh: カレーのような 簡単《かんたん》な 料理《りょうり》 = masakan sederhana seperti kari.',
                    'Kata sifat-na tidak memakai な di depan ように: ホテルのように きれいだ.',
                ],
                [
                    'Giving an example: カレーのような 簡単《かんたん》な 料理《りょうり》 = simple dishes like curry.',
                    'A na-adjective takes no な before ように: ホテルのように きれいだ.',
                ],
                [
                    $this->ex('あの 家《いえ》は 宮殿《きゅうでん》のようだ。', 'Ano ie wa kyuuden no you da.', 'Rumah itu seperti istana.', 'That house is like a palace.'),
                    $this->ex('彼女《かのじょ》は 人形《にんぎょう》のように きれいだ。', 'Kanojo wa ningyou no you ni kirei da.', 'Dia cantik seperti boneka.', 'She is as pretty as a doll.'),
                    $this->ex('この 町《まち》は 夏《なつ》でも 秋《あき》のような 涼《すず》しい 日《ひ》が ある。', 'Kono machi wa natsu demo aki no you na suzushii hi ga aru.', 'Di kota ini, bahkan di musim panas ada hari yang sejuk seperti musim gugur.', 'In this town, even in summer there are cool days like autumn.'),
                    $this->ex('私《わたし》は 天《てん》ぷらのような 揚《あ》げ物《もの》が 好《す》きです。', 'Watashi wa tenpura no you na agemono ga suki desu.', 'Saya suka gorengan seperti tempura.', 'I like deep-fried food such as tempura.'),
                ]
            ),

            $this->card(
                'Vる こと＋は／が／を (membuat kata benda)',
                'Vる こと＋は／が／を (nominalising a verb)',
                'V 辞書形《じしょけい》 ＋ こと ＋ は／が／を',
                'Menambahkan こと setelah kata kerja bentuk kamus mengubah kata kerja itu menjadi kata benda (gerund): "melakukan ~". Setelahnya dipasang partikel は, が, atau を sesuai peran kalimat itu. Bentuk ini sering dipakai untuk hal yang agak umum atau abstrak, misalnya kebiasaan, hobi, dan tujuan.',
                'Adding こと after the dictionary form of a verb turns it into a noun (a gerund): "doing ~". The particle は, が, or を then follows depending on its role in the sentence. It is often used for general or abstract matters such as habits, hobbies, and goals.',
                [
                    'Pola serupa: ことができます (bisa ~) dan しゅみは ～ことです (hobi saya ~).',
                    'Kalau kata kerja itu berupa kalimat panjang, lihat juga こと／ということ di Pelajaran 4.',
                ],
                [
                    'Related patterns: ことができます (can ~) and しゅみは ～ことです (my hobby is ~).',
                    'For long clauses see also こと／ということ in Lesson 4.',
                ],
                [
                    $this->ex('毎日《まいにち》 歩《ある》く ことは 体《からだ》に いい。', 'Mainichi aruku koto wa karada ni ii.', 'Berjalan kaki setiap hari baik untuk tubuh.', 'Walking every day is good for the body.'),
                    $this->ex('妹《いもうと》は 絵《え》を かく ことが 好《す》きです。', 'Imouto wa e o kaku koto ga suki desu.', 'Adik perempuan saya suka menggambar.', 'My younger sister likes drawing.'),
                    $this->ex('彼《かれ》は 試験《しけん》に 合格《ごうかく》する ことを 目標《もくひょう》に して いる。', 'Kare wa shiken ni goukaku suru koto o mokuhyou ni shite iru.', 'Dia menjadikan lulus ujian sebagai tujuannya.', 'He has made passing the exam his goal.'),
                ]
            ),

            $this->card(
                'N１を N２と 言う (menyebut nama sesuatu)',
                'N１を N２と 言う (calling something by name)',
                'N１を N２と 言《い》う',
                'Dipakai untuk memberi tahu nama (N2) dari suatu benda atau peristiwa (N1): "N1 disebut N2". Cocok untuk menjelaskan istilah kepada orang yang belum tahu.',
                'Used to tell someone the name (N2) of a thing or event (N1): "N1 is called N2". Handy for explaining a term to someone who does not know it.',
                [
                    'N2 biasanya berupa istilah yang dikutip; boleh ditulis dengan 「」.',
                    'Bentuk sopan: 〜と 言《い》います.',
                ],
                [
                    'N2 is usually a quoted term and may be written with 「」.',
                    'Polite form: 〜と 言《い》います.',
                ],
                [
                    $this->ex('十二月《じゅうにがつ》の 最後《さいご》の 日《ひ》を 大晦日《おおみそか》と 言《い》います。', 'Juunigatsu no saigo no hi o oomisoka to iimasu.', 'Hari terakhir bulan Desember disebut Oomisoka.', 'The last day of December is called Oomisoka.'),
                    $this->ex('お米《こめ》を 炊《た》いた ものを ご飯《はん》と 言《い》う。', 'Okome o taita mono o gohan to iu.', 'Beras yang sudah ditanak disebut gohan.', 'Cooked rice is called gohan.'),
                    $this->ex('大《おお》きな 地震《じしん》の あとに 来《く》る 小《ちい》さな 揺《ゆ》れを 余震《よしん》と 言《い》います。', 'Ooki na jishin no ato ni kuru chiisana yure o yoshin to iimasu.', 'Getaran kecil yang datang setelah gempa besar disebut gempa susulan.', 'The small tremors that follow a big earthquake are called aftershocks.'),
                ]
            ),

            $this->card(
                'N１という N２ (memperkenalkan nama)',
                'N１という N２ (introducing a name)',
                'N１ という N２',
                'Dipakai untuk menyebut nama atau judul suatu orang atau benda yang mungkin belum dikenal pendengar. N1 adalah nama diri (nama orang, judul, nama tempat), sedangkan N2 adalah kata benda umum yang menjelaskan jenisnya. Terjemahan kasarnya: "N2 yang bernama N1".',
                'Used to mention the name or title of a person or thing the listener may not know. N1 is a proper name (a person, a title, a place) and N2 is a common noun saying what kind of thing it is. Roughly: "an N2 called N1".',
                [
                    'Kalau pendengar sudah mengenalnya, cukup ucapkan namanya saja.',
                    'N1 nama diri, N2 kata benda umum: 「桜《さくら》」という 歌《うた》 (lagu berjudul "Sakura").',
                ],
                [
                    'If the listener already knows it, just say the name.',
                    'N1 is a proper name and N2 a common noun: 「桜《さくら》」という 歌《うた》 (a song titled "Sakura").',
                ],
                [
                    $this->ex('「桜《さくら》」という 歌《うた》を 知《し》って いますか。', 'Sakura toiu uta o shitte imasu ka.', 'Apakah Anda tahu lagu yang berjudul "Sakura"?', 'Do you know the song called "Sakura"?'),
                    $this->ex('先週《せんしゅう》、山田《やまだ》という 人《ひと》から 電話《でんわ》が ありました。', 'Senshuu, Yamada toiu hito kara denwa ga arimashita.', 'Minggu lalu ada telepon dari seseorang bernama Yamada.', 'Last week I got a call from a person named Yamada.'),
                    $this->ex('駅《えき》の 前《まえ》に「あおぞら」という 喫茶店《きっさてん》が できた。', 'Eki no mae ni "Aozora" toiu kissaten ga dekita.', 'Di depan stasiun dibuka kafe bernama "Aozora".', 'A café called "Aozora" opened in front of the station.'),
                ]
            ),

            $this->card(
                'いつ／どこ／何／だれ／どんなに ＋ 〜ても',
                'いつ／どこ／何／だれ／どんなに ＋ 〜ても (no matter ...)',
                '疑問詞《ぎもんし》 ＋ Vて／いA-くて／なA・N-で ＋ も',
                'Menyatakan sesuatu yang berlaku dalam keadaan apa pun, "tidak peduli ~". Kata tanya seperti いつ, どこ, 何, だれ, dan どんなに diikuti bentuk ても: kata kerja bentuk-て + も, kata sifat-い (い → くて) + も, kata sifat-na dan kata benda + でも. Dengan kata benda dipakai どんな N でも, どの N でも, atau どんなに〜 N でも.',
                'Expresses something that holds under any circumstances, "no matter ~". A question word such as いつ, どこ, 何, だれ, or どんなに is followed by ても: verb て-form + も, い-adjective (い → くて) + も, na-adjective or noun + でも. With nouns the forms どんな N でも, どの N でも, or どんなに〜 N でも are used.',
                [
                    'いくら〜ても juga bermakna "betapa pun ~".',
                    'Bagian akhir kalimat biasanya menyatakan hasil yang tidak berubah, sering berbentuk negatif atau menyangkal.',
                ],
                [
                    'いくら〜ても also means "however much ~".',
                    'The end of the sentence usually states a result that does not change, often negative or defiant.',
                ],
                [
                    $this->ex('いくら 説明《せつめい》しても、弟《おとうと》は わかって くれない。', 'Ikura setsumei shite mo, otouto wa wakatte kurenai.', 'Betapa pun saya menjelaskan, adik saya tidak mau mengerti.', 'However much I explain, my brother just does not get it.'),
                    $this->ex('どこへ 行《い》っても、この 歌《うた》が 聞《き》こえる。', 'Doko e itte mo, kono uta ga kikoeru.', 'Ke mana pun saya pergi, lagu ini terdengar.', 'Wherever I go, I can hear this song.'),
                    $this->ex('どんなに 忙《いそが》しくても、朝《あさ》ごはんは 食《た》べます。', 'Donna ni isogashikute mo, asagohan wa tabemasu.', 'Sesibuk apa pun, saya tetap sarapan.', 'No matter how busy I am, I eat breakfast.'),
                    $this->ex('どの 店《みせ》でも 値段《ねだん》は 同《おな》じです。', 'Dono mise demo nedan wa onaji desu.', 'Di toko mana pun harganya sama.', 'The price is the same at any shop.'),
                ]
            ),

            $this->card(
                'N１じゃなくて、N２ (menyangkal lalu mengoreksi)',
                'N１じゃなくて、N２ (negating and replacing)',
                'N１ じゃなくて、N２',
                'Menyangkal N1 lalu mengajukan N2 sebagai gantinya: "bukan N1, melainkan N2". Dipakai untuk meluruskan salah paham atau menolak tawaran dengan menyebut pilihan yang diinginkan. Bentuk formalnya ではなくて／ではなく.',
                'Negates N1 and puts forward N2 in its place: "not N1 but N2". Used to correct a misunderstanding or to turn down an offer by naming what you actually want. The formal versions are ではなくて／ではなく.',
                [
                    'Dalam percakapan sering dipotong: 「お寺《てら》じゃなくて、〜」.',
                    'Selain kata benda, bisa juga dipasang pada kata sifat-na: 静《しず》かじゃなくて、にぎやかな 所《ところ》.',
                ],
                [
                    'In conversation it is often clipped: 「お寺《てら》じゃなくて、〜」.',
                    'It also works with na-adjectives: 静《しず》かじゃなくて、にぎやかな 所《ところ》.',
                ],
                [
                    $this->ex('これは 塩《しお》じゃなくて、砂糖《さとう》です。', 'Kore wa shio janakute, satou desu.', 'Ini bukan garam, melainkan gula.', 'This is not salt, it is sugar.'),
                    $this->ex('今日《きょう》は 映画《えいが》じゃなくて、買《か》い物《もの》に 行《い》きたいです。', 'Kyou wa eiga janakute, kaimono ni ikitai desu.', 'Hari ini saya ingin berbelanja, bukan menonton film.', 'Today I would like to go shopping, not to the movies.'),
                    $this->ex('静《しず》かな 所《ところ》じゃなくて、景色《けしき》が いい 所《ところ》に 泊《と》まりたい。', 'Shizuka na tokoro janakute, keshiki ga ii tokoro ni tomaritai.', 'Saya ingin menginap di tempat dengan pemandangan bagus, bukan yang tenang.', 'I want to stay somewhere with a nice view rather than somewhere quiet.'),
                ]
            ),

            $this->card(
                '…のだ／…のではない (alasan, dugaan, sangkalan)',
                '…のだ／…のではない (reasons, conclusions, negation)',
                'V・いA 普通形《ふつうけい》／なA・N 普通形(〜だ → 〜な) ＋ のだ／のではない',
                '〜のです dipakai untuk menunjukkan hasil yang muncul karena suatu alasan, atau dugaan yang berdasarkan dasar yang masuk akal. Dalam gaya biasa menjadi 〜のだ. Bentuk negatifnya 〜のではない menyangkal semua bagian kalimat kecuali bagian paling akhir; yang disangkal adalah keterangan di depannya, misalnya 一人《ひとり》で (sendirian).',
                '〜のです is used to show a result that arises from a reason, or a guess based on logical grounds. In plain style it is 〜のだ. The negative 〜のではない negates everything except the final part of the sentence (the part that carries the emphasis).',
                [
                    'Kata sifat-na dan kata benda: 〜だ → 〜な sebelum のだ. Contoh: 元気《げんき》なのだ.',
                    'Bandingkan: 作《つく》りませんでした (tidak membuat sama sekali) dengan 一人《ひとり》で 作《つく》ったのでは ありません (bukan saya sendiri yang membuat).',
                ],
                [
                    'Na-adjectives and nouns: 〜だ → 〜な before のだ. Example: 元気《げんき》なのだ.',
                    'Compare: 作《つく》りませんでした (did not make it at all) with 一人《ひとり》で 作《つく》ったのでは ありません (I did not make it by myself).',
                ],
                [
                    $this->ex('電車《でんしゃ》が 止《と》まって いました。それで、遅《おく》れたのです。', 'Densha ga tomatte imashita. Sorede, okureta no desu.', 'Kereta berhenti. Itulah sebabnya saya terlambat.', 'The train was stopped. That is why I was late.'),
                    $this->ex('窓《まど》が 濡《ぬ》れて いる。夜《よる》の 間《あいだ》に 雨《あめ》が 降《ふ》ったのだ。', 'Mado ga nurete iru. Yoru no aida ni ame ga futta no da.', 'Jendelanya basah. Rupanya semalam hujan turun.', 'The window is wet. It must have rained during the night.'),
                    $this->ex('私《わたし》は この 料理《りょうり》を 一人《ひとり》で 作《つく》ったのでは ありません。', 'Watashi wa kono ryouri o hitori de tsukutta no dewa arimasen.', 'Saya tidak membuat masakan ini sendirian.', 'I did not make this dish all by myself.'),
                ]
            ),

            $this->card(
                '何人も・何回も・何枚も… (jumlah yang banyak)',
                '何人も・何回も・何枚も… (a large number)',
                '何《なん》＋ 助数詞《じょすうし》(人・回・枚…) ＋ も',
                'Kata tanya 何 + penghitung (人, 回, 枚, 台, 冊, 杯, dan seterusnya) + も menunjukkan bahwa jumlahnya banyak, lebih dari yang dikira. Penutur menyatakan rasa terkejut atau kesan bahwa jumlahnya besar.',
                'The question word 何 + a counter (人, 回, 枚, 台, 冊, 杯, etc.) + も shows that the number is large, more than one might expect. The speaker conveys surprise or the impression of a big amount.',
                [
                    'Bunyi penghitung berubah sesuai angkanya: 何杯《なんばい》, 何百人《なんびゃくにん》, 何冊《なんさつ》.',
                    'Kalimat dengan も ini memakai bentuk positif; untuk "tidak satu pun" dipakai 一〜も〜ない.',
                ],
                [
                    'The counter\'s sound changes with the number: 何杯《なんばい》, 何百人《なんびゃくにん》, 何冊《なんさつ》.',
                    'This も is used in positive sentences; for "not even one" use 一〜も〜ない.',
                ],
                [
                    $this->ex('駅《えき》の 前《まえ》に 人《ひと》が 何百人《なんびゃくにん》も 並《なら》んで いる。', 'Eki no mae ni hito ga nanbyakunin mo narande iru.', 'Di depan stasiun berjajar ratusan orang.', 'Hundreds of people are lined up in front of the station.'),
                    $this->ex('今日《きょう》は コーヒーを 何杯《なんばい》も 飲《の》んで しまった。', 'Kyou wa koohii o nanbai mo nonde shimatta.', 'Hari ini saya sampai minum kopi bercangkir-cangkir.', 'I ended up drinking cup after cup of coffee today.'),
                    $this->ex('机《つくえ》の 上《うえ》に 本《ほん》が 何冊《なんさつ》も 積《つ》んで ある。', 'Tsukue no ue ni hon ga nansatsu mo tsunde aru.', 'Di atas meja bertumpuk banyak buku.', 'Many books are stacked on the desk.'),
                ]
            ),
        ];
    }
}
