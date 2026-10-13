<?php

namespace Database\Seeders;

/**
 * Tata Bahasa N3 Pelajaran 5 (10 kartu, dengan dialog).
 *   php artisan db:seed --class=N3Lesson5BunpouSeeder
 */
class N3Lesson5BunpouSeeder extends N3GrammarSeeder
{
    protected function unitOrder(): int
    {
        return 5;
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
                $this->dl('A', '先週《せんしゅう》 行《い》った あの レストラン、おいしかったね。', 'Senshuu itta ano resutoran, oishikatta ne.', 'Restoran yang kita datangi minggu lalu itu enak ya.', 'That restaurant we went to last week was good, wasn\'t it?'),
                $this->dl('B', 'うん、あそこの パスタは 最高《さいこう》だった。', 'Un, asoko no pasuta wa saikou datta.', 'Iya, pastanya di sana luar biasa.', 'Yes, the pasta there was the best.'),
                $this->dl('A', '昨日《きのう》、駅《えき》で ミラーさんという 人《ひと》に 会《あ》ったんだけど、その 人《ひと》も あの 店《みせ》が 好《す》きなんだって。', 'Kinou, eki de Miraa-san toiu hito ni atta n da kedo, sono hito mo ano mise ga suki nan datte.', 'Kemarin aku bertemu seseorang bernama Miller di stasiun, katanya dia juga suka restoran itu.', 'Yesterday I met someone called Miller at the station, and he said he likes that restaurant too.'),
            ],
            2 => [
                $this->dl('A', '昨日《きのう》、新《あたら》しい 小説《しょうせつ》を 読《よ》みました。', 'Kinou, atarashii shousetsu o yomimashita.', 'Kemarin saya membaca novel baru.', 'Yesterday I read a new novel.'),
                $this->dl('B', 'どんな 小説《しょうせつ》ですか。', 'Donna shousetsu desu ka.', 'Novel seperti apa?', 'What kind of novel is it?'),
                $this->dl('A', '海外《かいがい》の 作家《さっか》が 書《か》いた 本《ほん》で、最近《さいきん》 日本語《にほんご》に 翻訳《ほんやく》されました。それが 今《いま》 人気《にんき》なんです。', 'Kaigai no sakka ga kaita hon de, saikin nihongo ni honyaku saremashita. Sore ga ima ninki nan desu.', 'Buku karya penulis luar negeri yang baru-baru ini diterjemahkan ke bahasa Jepang. Buku itu sedang populer.', 'It is a book by a foreign author that was recently translated into Japanese. It is popular right now.'),
                $this->dl('B', 'その 本《ほん》、私《わたし》も 読《よ》んで みたいです。', 'Sono hon, watashi mo yonde mitai desu.', 'Saya juga ingin membaca buku itu.', 'I would like to read that book too.'),
            ],
            3 => [
                $this->dl('A', '元気《げんき》が ないね。何《なに》か あったんじゃない？', 'Genki ga nai ne. Nani ka atta n ja nai?', 'Kamu lesu. Ada sesuatu, ya?', 'You look down. Something happened, didn\'t it?'),
                $this->dl('B', 'うん、実《じつ》は…仕事《しごと》が 忙《いそが》しくて。', 'Un, jitsu wa... shigoto ga isogashikute.', 'Iya, sebenarnya... pekerjaan sedang sibuk.', 'Yeah, actually... work has been busy.'),
                $this->dl('A', '少《すこ》し 休《やす》んだ ほうが いいんじゃない？', 'Sukoshi yasunda hou ga ii n ja nai?', 'Sebaiknya istirahat sebentar, bukan?', 'Wouldn\'t it be better to take a little rest?'),
                $this->dl('B', 'そうだね。', 'Sou da ne.', 'Benar juga.', 'You are right.'),
            ],
            4 => [
                $this->dl('A', 'すみません、郵便局《ゆうびんきょく》は どこですか。', 'Sumimasen, yuubinkyoku wa doko desu ka.', 'Maaf, di mana kantor pos?', 'Excuse me, where is the post office?'),
                $this->dl('B', 'あの 信号《しんごう》を 右《みぎ》へ 曲《ま》がった ところに ありますよ。', 'Ano shingou o migi e magatta tokoro ni arimasu yo.', 'Ada di tempat setelah belok kanan di lampu lalu lintas itu.', 'It is just after you turn right at that traffic light.'),
                $this->dl('A', '駅《えき》から 近《ちか》いですか。', 'Eki kara chikai desu ka.', 'Apakah dekat dari stasiun?', 'Is it close to the station?'),
                $this->dl('B', 'ええ、改札《かいさつ》を 出《で》て、階段《かいだん》を 下《お》りた ところが 出口《でぐち》です。', 'Ee, kaisatsu o dete, kaidan o orita tokoro ga deguchi desu.', 'Ya, keluar dari gerbang tiket lalu turun tangga, di situ pintu keluarnya.', 'Yes, go through the ticket gate and the exit is at the bottom of the stairs.'),
            ],
            5 => [
                $this->dl('A', 'もしもし、今《いま》 どこ？', 'Moshimoshi, ima doko?', 'Halo, sekarang di mana?', 'Hello, where are you now?'),
                $this->dl('B', 'ごめん。ちょうど 家《いえ》を 出《で》ようと した とき、電話《でんわ》が かかって きたんだ。', 'Gomen. Choudo ie o deyou to shita toki, denwa ga kakatte kita n da.', 'Maaf. Tepat saat aku mau keluar rumah, ada telepon.', 'Sorry. Just as I was about to leave the house, a call came in.'),
                $this->dl('A', '誰《だれ》から？', 'Dare kara?', 'Dari siapa?', 'From whom?'),
                $this->dl('B', '母《はは》から。もう すぐ 着《つ》くよ。', 'Haha kara. Mou sugu tsuku yo.', 'Dari ibu. Sebentar lagi sampai.', 'From my mother. I will be there soon.'),
            ],
            6 => [
                $this->dl('A', 'この 店《みせ》、クレジットカードが 使《つか》えるのだろうか。', 'Kono mise, kurejitto kaado ga tsukaeru no darou ka.', 'Apakah toko ini bisa pakai kartu kredit, ya?', 'I wonder if this shop takes credit cards.'),
                $this->dl('B', '店員《てんいん》さんに 聞《き》いて みたら？', 'Tenin-san ni kiite mitara?', 'Coba tanya pegawainya?', 'Why not ask the staff?'),
                $this->dl('A', 'すみません、クレジットカードは 使《つか》えるのでしょうか。', 'Sumimasen, kurejitto kaado wa tsukaeru no deshou ka.', 'Maaf, apakah bisa menggunakan kartu kredit?', 'Excuse me, can I use a credit card here?'),
                $this->dl('C', 'はい、使《つか》えますよ。', 'Hai, tsukaemasu yo.', 'Ya, bisa.', 'Yes, you can.'),
            ],
            7 => [
                $this->dl('A', '先週《せんしゅう》の 北海道《ほっかいどう》旅行《りょこう》は どうでしたか。', 'Senshuu no Hokkaidou ryokou wa dou deshita ka.', 'Bagaimana perjalanan ke Hokkaido minggu lalu?', 'How was last week\'s trip to Hokkaido?'),
                $this->dl('B', '友達《ともだち》との 旅行《りょこう》だったので、とても 楽《たの》しかったです。', 'Tomodachi to no ryokou datta node, totemo tanoshikatta desu.', 'Karena bersama teman, sangat menyenangkan.', 'It was a trip with friends, so it was a lot of fun.'),
                $this->dl('A', 'お土産《みやげ》は 買《か》いましたか。', 'Omiyage wa kaimashita ka.', 'Apakah membeli oleh-oleh?', 'Did you buy souvenirs?'),
                $this->dl('B', 'はい、先生《せんせい》への お土産《みやげ》に チョコレートを 買《か》いました。', 'Hai, sensei e no omiyage ni chokoreeto o kaimashita.', 'Ya, saya membeli cokelat sebagai oleh-oleh untuk guru.', 'Yes, I bought chocolate as a souvenir for our teacher.'),
            ],
            8 => [
                $this->dl('A', '来年《らいねん》の 景気《けいき》は どうなると 思《おも》いますか。', 'Rainen no keiki wa dou naru to omoimasu ka.', 'Menurut Anda, bagaimana kondisi ekonomi tahun depan?', 'What do you think the economy will be like next year?'),
                $this->dl('B', '少《すこ》しずつ 良《よ》く なるだろうと 思《おも》います。', 'Sukoshi zutsu yoku naru darou to omoimasu.', 'Saya kira akan membaik sedikit demi sedikit.', 'I think it will probably get better little by little.'),
                $this->dl('A', '物価《ぶっか》は？', 'Bukka wa?', 'Bagaimana dengan harga-harga?', 'And prices?'),
                $this->dl('B', '上《あ》がる だろうと 思《おも》いますが、給料《きゅうりょう》も 上《あ》がるでしょう。', 'Agaru darou to omoimasu ga, kyuuryou mo agaru deshou.', 'Saya kira akan naik, tetapi gaji pun mungkin ikut naik.', 'I think they will probably rise, but salaries will probably go up too.'),
            ],
            9 => [
                $this->dl('A', '十分《じゅっぷん》ぐらいで 戻《もど》って きますから、ここで 待《ま》って いて くれますか。', 'Juppun gurai de modotte kimasu kara, koko de matte ite kuremasu ka.', 'Saya kembali sekitar sepuluh menit lagi, bisakah Anda menunggu di sini?', 'I will be back in about ten minutes, so could you wait here?'),
                $this->dl('B', 'はい、わかりました。', 'Hai, wakarimashita.', 'Ya, baik.', 'Yes, all right.'),
                $this->dl('A', '荷物《にもつ》は ここに 置《お》きますから、見《み》て いて ください。', 'Nimotsu wa koko ni okimasu kara, mite ite kudasai.', 'Barang saya taruh di sini, tolong dijaga ya.', 'I will leave my luggage here, so please keep an eye on it.'),
                $this->dl('B', 'はい、大丈夫《だいじょうぶ》ですよ。', 'Hai, daijoubu desu yo.', 'Ya, tenang saja.', 'Yes, it will be fine.'),
            ],
            10 => [
                $this->dl('A', '昨日《きのう》、留学生《りゅうがくせい》が かいた 絵《え》を 見《み》ました。', 'Kinou, ryuugakusei ga kaita e o mimashita.', 'Kemarin saya melihat lukisan yang dibuat mahasiswa asing.', 'Yesterday I saw the pictures the foreign students painted.'),
                $this->dl('B', 'どうでしたか。', 'Dou deshita ka.', 'Bagaimana?', 'How were they?'),
                $this->dl('A', 'すばらしかったです。田中《たなか》さんの 作《つく》った ケーキも 食《た》べましたよ。', 'Subarashikatta desu. Tanaka-san no tsukutta keeki mo tabemashita yo.', 'Luar biasa. Saya juga makan kue buatan Pak Tanaka.', 'They were wonderful. I also ate the cake that Mr. Tanaka made.'),
                $this->dl('B', 'いいですね。', 'Ii desu ne.', 'Asyik ya.', 'Sounds nice.'),
            ],
        ];
    }

    protected function cards(): array
    {
        return [
            $this->card(
                'あ〜・そ〜 (kata tunjuk menurut konteks, percakapan)',
                'あ〜・そ〜 (contextual demonstratives in conversation)',
                'あ(あれ・あの・あそこ…) ／ そ(それ・その・そこ…)',
                'Kata tunjuk あ〜 dan そ〜 juga dipakai untuk merujuk hal yang muncul dalam percakapan, bukan hanya benda yang ada di depan mata. Hal yang dikenal langsung oleh pembicara dan pendengar ditunjuk dengan あ〜 (あれ, あの, あそこ…). Hal yang hanya diketahui salah satu pihak (pembicara atau pendengar) ditunjuk dengan そ〜 (それ, その, そこ…).',
                'The demonstratives あ〜 and そ〜 are also used for things that came up in the conversation, not only things physically present. Something directly known to both speaker and listener is referred to with あ〜 (あれ, あの, あそこ…). Something known to only one of them is referred to with そ〜 (それ, その, そこ…).',
                [
                    'Perhatikan "siapa yang tahu": kalau kedua-duanya tahu → あの 人《ひと》; kalau hanya pembicara yang tahu → その 人《ひと》.',
                    'Perbedaan ini penting untuk terdengar wajar dalam obrolan.',
                ],
                [
                    'Think "who knows what": both know → あの 人《ひと》; only the speaker knows → その 人《ひと》.',
                    'This distinction matters for sounding natural in conversation.',
                ],
                [
                    $this->ex('昨日《きのう》 駅前《えきまえ》に 新《あたら》しい パン屋《や》が できたね。……あの 店《みせ》、もう 行《い》った？', 'Kinou ekimae ni atarashii panya ga dekita ne. ...Ano mise, mou itta?', 'Kemarin di depan stasiun dibuka toko roti baru, ya. ……Toko itu, sudah pernah ke sana?', 'A new bakery opened in front of the station yesterday. ...Have you been to that shop yet?'),
                    $this->ex('さっき、鈴木《すずき》さんという 人《ひと》に 会《あ》ったよ。……その 人《ひと》、だれ？', 'Sakki, Suzuki-san toiu hito ni atta yo. ...Sono hito, dare?', 'Tadi aku bertemu orang bernama Suzuki. ……Orang itu siapa?', 'I met someone called Suzuki just now. ...Who is that person?'),
                    $this->ex('昔《むかし》 住《す》んで いた 町《まち》の 話《はなし》ですが、あそこは 今《いま》も 静《しず》かですよ。', 'Mukashi sunde ita machi no hanashi desu ga, asoko wa ima mo shizuka desu yo.', 'Ini tentang kota tempat dulu kita tinggal; di sana sekarang pun masih tenang.', 'About the town we used to live in, it is still quiet there.'),
                ]
            ),

            $this->card(
                'そ〜 (kata tunjuk dalam tulisan)',
                'そ〜 (contextual demonstratives in writing)',
                'そ(それ・その・そこ…)',
                'Dalam tulisan, そ〜 (それ, その, そこ…) dipakai untuk merujuk hal yang telah muncul di kalimat sebelumnya. Dengan begitu pembaca bisa mengikuti isi tanpa pengulangan.',
                'In writing, そ〜 (それ, その, そこ…) is used to refer to something that appeared in the previous sentence. This lets the reader follow along without repetition.',
                [
                    'Dalam tulisan あ〜 jarang dipakai untuk merujuk teks sebelumnya.',
                    'それ merujuk benda atau hal; そこ merujuk tempat atau titik waktu; その + N menerangkan kata benda.',
                ],
                [
                    'In writing あ〜 is rarely used to point back at earlier text.',
                    'それ refers to a thing or fact; そこ to a place or point; その + N modifies a noun.',
                ],
                [
                    $this->ex('会社《かいしゃ》を 出《で》た あと、駅《えき》の レストランで 夕食《ゆうしょく》を 食《た》べました。そのとき、財布《さいふ》を 落《お》としたんだと 思《おも》います。', 'Kaisha o deta ato, eki no resutoran de yuushoku o tabemashita. Sono toki, saifu o otoshita n da to omoimasu.', 'Setelah keluar kantor, saya makan malam di restoran stasiun. Saat itulah rupanya dompet saya jatuh.', 'After leaving the office I had dinner at the station restaurant. I think I dropped my wallet then.'),
                    $this->ex('新《あたら》しい 法律《ほうりつ》が 決《き》まりました。それは 来年《らいねん》から 始《はじ》まります。', 'Atarashii houritsu ga kimarimashita. Sore wa rainen kara hajimarimasu.', 'Sebuah undang-undang baru telah ditetapkan. Itu akan berlaku mulai tahun depan.', 'A new law has been decided. It will take effect next year.'),
                    $this->ex('駅《えき》の 近《ちか》くに 公園《こうえん》が あります。そこで 毎週《まいしゅう》 市《いち》が 開《ひら》かれます。', 'Eki no chikaku ni kouen ga arimasu. Soko de maishuu ichi ga hirakaremasu.', 'Dekat stasiun ada taman. Di sanalah pasar digelar setiap minggu.', 'There is a park near the station. A market is held there every week.'),
                ]
            ),

            $this->card(
                '…んじゃない？ (menyatakan dugaan)',
                '…んじゃない？ (offering a guess)',
                'V・いA 普通形／なA・N 普通形(〜だ→〜な) ＋ んじゃない？／んじゃないですか',
                '「…んじゃないですか」 adalah bentuk tidak resmi dari 「…のではありませんか」, dipakai dalam percakapan untuk menyampaikan apa yang dipikirkan pembicara: "bukankah ~?", "jangan-jangan ~". Dengan orang akrab dipersingkat menjadi 「…んじゃない？」. Dalam situasi formal, bentuknya lebih panjang: 「…のではないでしょうか」.',
                '「…んじゃないですか」 is the informal form of 「…のではありませんか」 and is used in conversation to say what the speaker thinks: "isn\'t it ~?", "perhaps ~". With familiar people it shortens to 「…んじゃない？」. In formal conversation it lengthens to 「…のではないでしょうか」.',
                [
                    'Walaupun berbentuk pertanyaan, isinya adalah dugaan atau saran lembut.',
                    'Kata sifat-na dan kata benda: 〜だ → 〜な (元気《げんき》なんじゃない？).',
                ],
                [
                    'Although it is in question form, it conveys a guess or a gentle suggestion.',
                    'Na-adjectives and nouns: 〜だ → 〜な (元気《げんき》なんじゃない？).',
                ],
                [
                    $this->ex('顔色《かおいろ》が 悪《わる》いね。熱《ねつ》が あるんじゃない？', 'Kaoiro ga warui ne. Netsu ga aru n ja nai?', 'Wajahmu pucat. Jangan-jangan kamu demam?', 'You look pale. Don\'t you have a fever?'),
                    $this->ex('この 道《みち》の ほうが 近《ちか》いんじゃないですか。', 'Kono michi no hou ga chikai n ja nai desu ka.', 'Bukankah jalan ini lebih dekat?', 'Isn\'t this road shorter?'),
                    $this->ex('それは 高《たか》すぎるのではないでしょうか。', 'Sore wa takasugiru no de wa nai deshouka.', 'Bukankah itu terlalu mahal?', 'Isn\'t that too expensive, I wonder?'),
                ]
            ),

            $this->card(
                'Vた ところに／で (tempat setelah bergerak)',
                'Vた ところに／で (the place reached after moving)',
                'V(kata kerja gerak)た形 ＋ ところ ＋ に／で',
                'Kata kerja gerak seperti 行く, 渡る, 曲がる, 出る, 上る, 降りる dalam bentuk 「V た + ところ」 menunjukkan tempat yang dicapai setelah gerakan itu dilakukan. Setelahnya dipakai に (ada) atau で (melakukan sesuatu di sana).',
                'Verbs of motion such as 行く, 渡る, 曲がる, 出る, 上る, and 降りる, in the form 「V た + ところ」, indicate the position arrived at after the motion has taken place. It is followed by に (something exists there) or で (something is done there).',
                [
                    'ところ di sini berarti "tempat", bukan "saat" (bandingkan Pelajaran 2).',
                    'Cocok untuk petunjuk jalan.',
                ],
                [
                    'ところ here means "place", not "time" (compare Lesson 2).',
                    'Handy for giving directions.',
                ],
                [
                    $this->ex('あの 角《かど》を 右《みぎ》へ 曲《ま》がった ところに、コンビニが あります。', 'Ano kado o migi e magatta tokoro ni, konbini ga arimasu.', 'Setelah berbelok kanan di sudut itu, ada minimarket.', 'There is a convenience store just after you turn right at that corner.'),
                    $this->ex('橋《はし》を 渡《わた》った ところで 待《ま》って います。', 'Hashi o watatta tokoro de matte imasu.', 'Saya menunggu di tempat setelah menyeberangi jembatan.', 'I will be waiting just after the bridge.'),
                    $this->ex('階段《かいだん》を 降《お》りた ところに 受付《うけつけ》が あります。', 'Kaidan o orita tokoro ni uketsuke ga arimasu.', 'Meja resepsionis ada di bawah tangga.', 'The reception desk is at the bottom of the stairs.'),
                ]
            ),

            $this->card(
                'Vよう(意向形)と する／しない',
                'Vよう(volitional)と する／しない',
                'V 意向形《いこうけい》 ＋ とする／としない',
                '(1) 「V よう と する」 menunjukkan keadaan tepat sebelum melakukan V, sehingga V belum benar-benar terjadi. Biasanya dipakai bersama 〜とき, 〜たら, dan sejenisnya. (2) Bisa juga berarti "berusaha melakukan V". (3) 「V よう と しない」 menyatakan bahwa seseorang tidak berniat melakukan V; biasanya dipakai untuk orang lain, bukan untuk diri sendiri.',
                '(1) 「V よう と する」 indicates a situation just before doing V, so V does not actually take place. It is usually used together with 〜とき, 〜たら, etc. (2) It can also mean someone is trying to do V. (3) 「V よう と しない」 indicates that a person does not intend to do V; it is usually used about people other than oneself.',
                [
                    'Bentuk maksud: Kelompok I −u → −ou (書く → 書こう), Kelompok II −る → −よう (食べる → 食べよう), する → しよう, 来る → 来よう.',
                    'Bandingkan dengan 〜つもり: としない menekankan sikap menolak yang tampak dari luar.',
                ],
                [
                    'Volitional form: Group I −u → −ou (書く → 書こう), Group II −る → −よう (食べる → 食べよう), する → しよう, 来る → 来よう.',
                    'Compare 〜つもり: としない emphasises a refusal visible from the outside.',
                ],
                [
                    $this->ex('ドアを 開《あ》けようと した とき、後《うし》ろから 声《こえ》を かけられた。', 'Doa o akeyou to shita toki, ushiro kara koe o kakerareta.', 'Ketika saya hendak membuka pintu, ada yang memanggil dari belakang.', 'Just as I was about to open the door, someone called out from behind.'),
                    $this->ex('電車《でんしゃ》に 乗《の》ろうと したら、ドアが 閉《し》まった。', 'Densha ni norou to shitara, doa ga shimatta.', 'Saat saya hendak naik kereta, pintunya menutup.', 'When I tried to board the train, the doors closed.'),
                    $this->ex('祖父《そふ》は お酒《さけ》を やめようと して いる。', 'Sofu wa osake o yameyou to shite iru.', 'Kakek saya sedang berusaha berhenti minum minuman keras.', 'My grandfather is trying to give up alcohol.'),
                    $this->ex('あの 子《こ》は 野菜《やさい》を 食《た》べようと しない。', 'Ano ko wa yasai o tabeyou to shinai.', 'Anak itu sama sekali tidak mau makan sayur.', 'That child will not even try to eat vegetables.'),
                ]
            ),

            $this->card(
                '…のだろうか (bertanya pada diri sendiri)',
                '…のだろうか (wondering)',
                'V・いA 普通形／なA・N 普通形(〜だ→〜な) ＋ のだろうか',
                '「X のだろうか」 dipakai ketika bertanya kepada diri sendiri apakah X benar atau tidak: "apakah ~?", "bagaimana ya ~". Bisa juga bersama kata tanya seperti どう, 何, dan いつ untuk bertanya pada diri sendiri. Bisa juga dipakai untuk bertanya kepada orang lain; dibanding 「X のですか」 yang lebih langsung, 「X のでしょうか」 lebih lembut dan tidak menuntut jawaban. Tanpa kata tanya, 「X のだろうか」 juga dipakai untuk menyiratkan bahwa X mungkin tidak benar.',
                '「X のだろうか」 is used when asking oneself whether X is true or not: "I wonder if ~". It can also be used with a question word such as どう, 何, or いつ to ask oneself a question. It can also be used when asking someone else; compared with the more direct 「X のですか」, 「X のでしょうか」 is a gentler way of asking that does not demand an answer. Without a question word, 「X のだろうか」 can also suggest that X may not be true.',
                [
                    'Dalam tulisan sering menyiratkan keraguan: 本当《ほんとう》に これで いいのだろうか。 (apa benar begini sudah baik?)',
                    'Kata sifat-na dan kata benda: 〜だ → 〜な.',
                ],
                [
                    'In writing it often implies doubt: 本当《ほんとう》に これで いいのだろうか。 (is this really all right?)',
                    'Na-adjectives and nouns: 〜だ → 〜な.',
                ],
                [
                    $this->ex('来週《らいしゅう》の 試験《しけん》は むずかしいのだろうか。', 'Raishuu no shiken wa muzukashii no darouka.', 'Apakah ujian minggu depan akan sulit, ya?', 'I wonder if next week\'s exam will be difficult.'),
                    $this->ex('どうすれば 日本語《にほんご》が 上手《じょうず》に なるのだろうか。', 'Dou sureba nihongo ga jouzu ni naru no darouka.', 'Bagaimana caranya supaya bahasa Jepang bisa lancar, ya?', 'I wonder how I can get good at Japanese.'),
                    $this->ex('こんな 練習《れんしゅう》で 本当《ほんとう》に 上手《じょうず》に なるのだろうか。', 'Konna renshuu de hontou ni jouzu ni naru no darouka.', 'Apa benar dengan latihan seperti ini bisa menjadi mahir?', 'I wonder whether practice like this will really make me good.'),
                ]
            ),

            $this->card(
                'Nとの／での／からの／までの／への N (partikel + の)',
                'Nとの／での／からの／までの／への N (particle + の)',
                'N ＋ ｛格助詞《かくじょし》＋の｝ ＋ N',
                'Ketika kata yang disertai partikel kasus (と, で, から, まで, へ) dipakai untuk menerangkan kata benda, の ditambahkan setelah partikel itu: とのN, でのN, からのN, までのN, へのN. Kalau partikelnya に, に diganti へ lalu dipasang の (への). Partikel が dan を tidak diikuti の.',
                'When a word with a case particle (と, で, から, まで, or へ) modifies a noun, の is added after the particle: とのN, でのN, からのN, までのN, へのN. If the particle is に, it changes to へ and の is added (への). の is not added to が or を.',
                [
                    'に + の tidak dipakai; ganti へ → への.',
                    'が／を tidak berubah: 田中《たなか》さんの 欠席《けっせき》を 〜 (bukan 田中さんをの).',
                ],
                [
                    'に + の is not used; switch to へ → への.',
                    'が／を do not change: 田中《たなか》さんの 欠席《けっせき》を 〜 (not 田中さんをの).',
                ],
                [
                    $this->ex('先生《せんせい》との 面談《めんだん》は 三時《さんじ》からです。', 'Sensei to no mendan wa sanji kara desu.', 'Pertemuan dengan guru mulai pukul tiga.', 'The meeting with the teacher starts at three.'),
                    $this->ex('東京《とうきょう》での 生活《せいかつ》は 楽《たの》しいです。', 'Toukyou de no seikatsu wa tanoshii desu.', 'Hidup di Tokyo menyenangkan.', 'Life in Tokyo is enjoyable.'),
                    $this->ex('会社《かいしゃ》からの お知《し》らせを 読《よ》んで ください。', 'Kaisha kara no oshirase o yonde kudasai.', 'Tolong baca pemberitahuan dari kantor.', 'Please read the notice from the company.'),
                    $this->ex('駅《えき》までの 道《みち》を 教《おし》えて ください。', 'Eki made no michi o oshiete kudasai.', 'Tolong beri tahu jalan ke stasiun.', 'Please tell me the way to the station.'),
                    $this->ex('友達《ともだち》への プレゼントを 選《えら》んで いる。', 'Tomodachi e no purezento o erande iru.', 'Saya sedang memilih hadiah untuk teman.', 'I am choosing a present for my friend.'),
                ]
            ),

            $this->card(
                '…だろう・…だろうと思う (dugaan)',
                '…だろう・…だろうと思う (conjecture)',
                'V・いA 普通形／なA・N 普通形(〜だ は つけない) ＋ だろう',
                '「…だろう」 adalah bentuk biasa dari 「…でしょう」 dan dipakai dalam gaya biasa untuk menyampaikan pendapat secara dugaan, bukan kepastian: "mungkin ~". Dalam percakapan lazim memakai 「…だろうと 思《おも》う」 (saya kira ~ akan ~).',
                '「…だろう」 is the plain form of 「…でしょう」 and is used in plain-style sentences to express one\'s opinion conjecturally rather than definitely: "probably ~". In conversation it is usual to use 「…だろうと 思《おも》う」 (I think ~ probably ~).',
                [
                    'Kata sifat-na dan kata benda: tidak diberi だ sebelum だろう (元気《げんき》だろう, 学生《がくせい》だろう).',
                    '「でしょう？」 (meminta persetujuan) dan 「でしょう」 (dugaan) berbeda nada.',
                ],
                [
                    'Na-adjectives and nouns: no だ before だろう (元気《げんき》だろう, 学生《がくせい》だろう).',
                    '「でしょう？」 (asking for confirmation) and 「でしょう」 (conjecture) differ in intonation.',
                ],
                [
                    $this->ex('明日《あした》は 晴《は》れるだろう。', 'Ashita wa hareru darou.', 'Besok mungkin cerah.', 'It will probably be sunny tomorrow.'),
                    $this->ex('彼《かれ》は 今《いま》ごろ 会社《かいしゃ》に 着《つ》いて いるだろうと 思《おも》います。', 'Kare wa imagoro kaisha ni tsuite iru darou to omoimasu.', 'Saya kira dia sekarang sudah tiba di kantor.', 'I think he has probably arrived at the office by now.'),
                    $this->ex('この 計画《けいかく》は うまく いくだろう。', 'Kono keikaku wa umaku iku darou.', 'Rencana ini mungkin akan berjalan baik.', 'This plan will probably go well.'),
                ]
            ),

            $this->card(
                '…から、〜てください (informasi pendukung permintaan)',
                '…から、〜てください (prerequisite information before a request)',
                'V 丁寧形《ていねいけい》 ＋ から、Vてください',
                'Dalam pola ini, 「…から」 bukan menyatakan alasan, melainkan informasi yang menjadi prasyarat bagi permintaan atau petunjuk yang menyusul. Fungsinya menyiapkan lawan bicara: dijelaskan dahulu keadaannya, lalu diminta melakukan sesuatu.',
                'In this use, 「…から」 does not indicate a reason. It gives information that is a prerequisite for the request or instruction that follows. Its function is to prepare the listener: the situation is described first, then they are asked to do something.',
                [
                    'Biasanya dipakai dalam petunjuk dan percakapan layanan.',
                    'Bentuk lain: 〜から、〜て くれますか／〜て いただけますか.',
                ],
                [
                    'Typical in instructions and service conversation.',
                    'Other forms: 〜から、〜て くれますか／〜て いただけますか.',
                ],
                [
                    $this->ex('切符《きっぷ》を 入《い》れると 光《ひか》りますから、ボタンを 押《お》して ください。', 'Kippu o ireru to hikarimasu kara, botan o oshite kudasai.', 'Jika tiket dimasukkan, tombolnya menyala, jadi silakan tekan tombol itu.', 'When you insert the ticket the button lights up, so please press it.'),
                    $this->ex('すぐ 戻《もど》りますから、ここに 座《すわ》って 待《ま》って いて ください。', 'Sugu modorimasu kara, koko ni suwatte matte ite kudasai.', 'Saya segera kembali, jadi tolong duduk dan tunggu di sini.', 'I will be right back, so please sit here and wait.'),
                    $this->ex('今《いま》から 説明《せつめい》しますから、よく 聞《き》いて ください。', 'Ima kara setsumei shimasu kara, yoku kiite kudasai.', 'Saya akan menjelaskan sekarang, jadi tolong dengarkan baik-baik.', 'I will explain now, so please listen carefully.'),
                ]
            ),

            $this->card(
                'が／の (subjek dalam anak kalimat penerang kata benda)',
                'が／の (subject in a noun-modifying clause)',
                'S(が／の) ＋ V ＋ N',
                'Partikel 「が」 pada subjek dari anak kalimat yang menerangkan kata benda bisa diganti dengan 「の」. Pergantian ini hanya berlaku untuk anak kalimat penerang kata benda, bukan untuk kalimat utama.',
                'The 「が」 of the subject of a clause describing a noun can be replaced by 「の」. This substitution applies only within a noun-modifying clause, not in a main clause.',
                [
                    'Makna tidak berubah; の terdengar sedikit lebih lembut atau tulisan.',
                    'Kalau ada bagian lain di antaranya sehingga hubungannya tidak jelas, tetap pakai が.',
                ],
                [
                    'The meaning does not change; の sounds a little softer or more literary.',
                    'If other material sits in between and the relationship becomes unclear, keep が.',
                ],
                [
                    $this->ex('留学生《りゅうがくせい》が／の かいた 絵《え》を 見《み》ました。', 'Ryuugakusei ga/no kaita e o mimashita.', 'Saya melihat lukisan yang dibuat oleh mahasiswa asing.', 'I saw the pictures that the international students painted.'),
                    $this->ex('母《はは》が／の 作《つく》った スープは 温《あたた》かかった。', 'Haha ga/no tsukutta suupu wa atatakakatta.', 'Sup yang dibuat ibu terasa hangat.', 'The soup my mother made was warm.'),
                    $this->ex('先生《せんせい》が／の 書《か》いた 本《ほん》を 読《よ》んで います。', 'Sensei ga/no kaita hon o yonde imasu.', 'Saya sedang membaca buku yang ditulis oleh guru.', 'I am reading the book the teacher wrote.'),
                ]
            ),
        ];
    }
}
