<?php

namespace Database\Seeders;

/**
 * Tata Bahasa N3 Pelajaran 6 (10 kartu, dengan dialog).
 *   php artisan db:seed --class=N3Lesson6BunpouSeeder
 */
class N3Lesson6BunpouSeeder extends N3GrammarSeeder
{
    protected function unitOrder(): int
    {
        return 6;
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
                $this->dl('A', '田中《たなか》さんは 何《なん》て 言《い》って いた？', 'Tanaka-san wa nante itte ita?', 'Pak Tanaka bilang apa?', 'What did Mr. Tanaka say?'),
                $this->dl('B', '今日《きょう》は 早《はや》く 帰《かえ》るって。', 'Kyou wa hayaku kaeru tte.', 'Katanya hari ini dia pulang cepat.', 'He said he is going home early today.'),
                $this->dl('A', '理由《りゆう》は？', 'Riyuu wa?', 'Alasannya?', 'Why?'),
                $this->dl('B', '子《こ》どもが 熱《ねつ》を 出《だ》したって 言《い》って いたよ。', 'Kodomo ga netsu o dashita tte itte ita yo.', 'Katanya anaknya demam.', 'He said his child has a fever.'),
            ],
            2 => [
                $this->dl('A', '札幌《さっぽろ》って、どんな 町《まち》？', 'Sapporo tte, donna machi?', 'Sapporo itu kota yang seperti apa?', 'What kind of city is Sapporo?'),
                $this->dl('B', '冬《ふゆ》は 雪《ゆき》が 多《おお》くて、食《た》べ物《もの》が おいしい 町《まち》だよ。', 'Fuyu wa yuki ga ookute, tabemono ga oishii machi da yo.', 'Kota yang bersalju lebat di musim dingin dan makanannya enak.', 'A city with lots of snow in winter and delicious food.'),
                $this->dl('A', '行《い》った ことが あるの？', 'Itta koto ga aru no?', 'Kamu pernah ke sana?', 'Have you been there?'),
                $this->dl('B', 'うん、去年《きょねん》 行《い》った。', 'Un, kyonen itta.', 'Ya, tahun lalu.', 'Yes, last year.'),
            ],
            3 => [
                $this->dl('A', '来月《らいげつ》の 同窓会《どうそうかい》、行《い》く？', 'Raigetsu no dousoukai, iku?', 'Reuni bulan depan, kamu datang?', 'Are you going to next month\'s reunion?'),
                $this->dl('B', 'ううん、行《い》く つもりは ないよ。', 'Uun, iku tsumori wa nai yo.', 'Tidak, aku tidak berniat datang.', 'No, I have no intention of going.'),
                $this->dl('A', 'どうして？', 'Doushite?', 'Kenapa?', 'Why not?'),
                $this->dl('B', '仕事《しごと》が 忙《いそが》しいし、あまり 気《き》が 進《すす》まないんだ。', 'Shigoto ga isogashii shi, amari ki ga susumanai n da.', 'Pekerjaan sibuk dan aku juga kurang berminat.', 'I am busy with work and I am not really keen on it.'),
            ],
            4 => [
                $this->dl('A', '昨日《きのう》の 会議《かいぎ》、なぜ 来《こ》なかったの？', 'Kinou no kaigi, naze konakatta no?', 'Kenapa kemarin tidak datang ke rapat?', 'Why did you not come to yesterday\'s meeting?'),
                $this->dl('B', 'すみません、行《い》く つもりだったんですが、お客《きゃく》さんが 来《き》て しまって。', 'Sumimasen, iku tsumori datta n desu ga, okyaku-san ga kite shimatte.', 'Maaf, tadinya saya berniat datang, tetapi tamu tiba-tiba datang.', 'Sorry, I had meant to come, but a customer turned up.'),
                $this->dl('A', '連絡《れんらく》して ほしかったな。', 'Renraku shite hoshikatta na.', 'Seharusnya kamu memberi kabar.', 'I wish you had let me know.'),
                $this->dl('B', '本当《ほんとう》に すみません。', 'Hontou ni sumimasen.', 'Sungguh maaf.', 'I am truly sorry.'),
            ],
            5 => [
                $this->dl('A', '窓《まど》、閉《し》めた？', 'Mado, shimeta?', 'Jendelanya sudah ditutup?', 'Did you close the window?'),
                $this->dl('B', '閉《し》めた つもりだけど…あれ、開《あ》いて いるね。', 'Shimeta tsumori da kedo... are, aite iru ne.', 'Rasanya sudah, tapi... eh, ternyata terbuka.', 'I thought I did... oh, it is open.'),
                $this->dl('A', '雨《あめ》が 入《はい》って くるよ。', 'Ame ga haitte kuru yo.', 'Nanti hujan masuk.', 'The rain will come in.'),
                $this->dl('B', 'ごめん、すぐ 閉《し》める。', 'Gomen, sugu shimeru.', 'Maaf, segera kututup.', 'Sorry, I will close it right away.'),
            ],
            6 => [
                $this->dl('A', '最近《さいきん》、うちの 子《こ》は スマホばかり 見《み》て いるんです。', 'Saikin, uchi no ko wa sumaho bakari mite iru n desu.', 'Akhir-akhir ini anak saya hanya melihat ponsel.', 'These days my child does nothing but look at his phone.'),
                $this->dl('B', '宿題《しゅくだい》は して いますか。', 'Shukudai wa shite imasu ka.', 'Apakah PR-nya dikerjakan?', 'Does he do his homework?'),
                $this->dl('A', 'それも 忘《わす》れて、遊《あそ》んで ばかり いて…。', 'Sore mo wasurete, asonde bakari ite...', 'Itu pun lupa, kerjanya bermain terus...', 'He forgets that too and just plays all the time...'),
                $this->dl('B', '時間《じかん》を 決《き》めたら どうですか。', 'Jikan o kimetara dou desu ka.', 'Bagaimana kalau ditentukan batas waktunya?', 'Why not set a time limit?'),
            ],
            7 => [
                $this->dl('A', '夏休《なつやす》みは 何《なに》を する 予定《よてい》？', 'Natsuyasumi wa nani o suru yotei?', 'Liburan musim panas mau apa?', 'What are you planning for the summer holidays?'),
                $this->dl('B', '海《うみ》へ 行《い》くとか、山《やま》に 登《のぼ》るとか 考《かんが》えて いるよ。', 'Umi e iku toka, yama ni noboru toka kangaete iru yo.', 'Kepikiran pergi ke laut atau mendaki gunung.', 'I am thinking of things like going to the sea or climbing a mountain.'),
                $this->dl('A', 'いいね。', 'Ii ne.', 'Asyik.', 'Sounds nice.'),
                $this->dl('B', '君《きみ》は？', 'Kimi wa?', 'Kamu?', 'And you?'),
                $this->dl('A', '私《わたし》は レポートとか アルバイトとかで 忙《いそが》しそう。', 'Watashi wa repooto toka arubaito toka de isogashisou.', 'Aku sepertinya sibuk dengan laporan dan kerja paruh waktu.', 'I will probably be busy with reports and part-time work.'),
            ],
            8 => [
                $this->dl('A', 'あれ、だんだん 暗《くら》く なって きたね。', 'Are, dandan kuraku natte kita ne.', 'Eh, lama-lama jadi gelap ya.', 'Hey, it is getting dark.'),
                $this->dl('B', '本当《ほんとう》だ。雨《あめ》が 降《ふ》って くるかも しれない。', 'Hontou da. Ame ga futte kuru kamo shirenai.', 'Benar. Mungkin sebentar lagi hujan.', 'True. It might start raining.'),
                $this->dl('A', '傘《かさ》、持《も》って きた？', 'Kasa, motte kita?', 'Kamu bawa payung?', 'Did you bring an umbrella?'),
                $this->dl('B', 'ううん。急《いそ》いで 帰《かえ》ろう。', 'Uun. Isoide kaerou.', 'Tidak. Ayo cepat pulang.', 'No. Let us hurry home.'),
            ],
            9 => [
                $this->dl('A', '田中《たなか》さんは どこ？', 'Tanaka-san wa doko?', 'Pak Tanaka di mana?', 'Where is Mr. Tanaka?'),
                $this->dl('B', 'さっき 資料《しりょう》を 持《も》って 出《で》て いきましたよ。', 'Sakki shiryou o motte dete ikimashita yo.', 'Tadi dia keluar membawa dokumen.', 'He just went out with some documents.'),
                $this->dl('A', 'すぐ 帰《かえ》って きますか。', 'Sugu kaette kimasu ka.', 'Apakah dia segera kembali?', 'Will he be back soon?'),
                $this->dl('B', 'ええ、十分《じゅっぷん》ぐらいで 戻《もど》って くると 思《おも》います。', 'Ee, juppun gurai de modotte kuru to omoimasu.', 'Ya, kira-kira sepuluh menit lagi dia kembali.', 'Yes, I think he will be back in about ten minutes.'),
            ],
            10 => [
                $this->dl('A', '昨日《きのう》の ニュースで、こんな 話《はなし》を 聞《き》きました。', 'Kinou no nyuusu de, konna hanashi o kikimashita.', 'Di berita kemarin saya mendengar kabar seperti ini.', 'I heard a story like this on yesterday\'s news.'),
                $this->dl('B', 'どんな 話《はなし》ですか。', 'Donna hanashi desu ka.', 'Kabar apa?', 'What kind of story?'),
                $this->dl('A', 'この 町《まち》に 大《おお》きな 図書館《としょかん》が できる そうです。', 'Kono machi ni ookina toshokan ga dekiru sou desu.', 'Katanya akan dibangun perpustakaan besar di kota ini.', 'They say a big library will be built in this town.'),
                $this->dl('B', 'それは いい ニュースですね。', 'Sore wa ii nyuusu desu ne.', 'Itu kabar bagus.', 'That is good news.'),
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    protected function cards(): array
    {
        return [
            $this->card(
                '…て／…って (kutipan dalam percakapan)',
                '…て／…って (quoting in speech)',
                '普通形《ふつうけい》 ＋ て／って',
                'Dalam bahasa lisan, partikel kutipan と sering berubah menjadi て atau って. Dipakai setelah bentuk biasa untuk menyampaikan ucapan, tulisan, atau pikiran orang. って juga dapat menggantikan という pada 「〜という 名前《なまえ》の 人《ひと》／もの／ところ」, yaitu "seseorang bernama ~".',
                'In spoken Japanese the quoting particle と often becomes て or って. It follows the plain form and reports what someone said, wrote, or thought. って can also replace という in 「〜という 名前《なまえ》の 人《ひと》／もの／ところ」, meaning "a person named ~".',
                ['って lebih santai daripada と; jangan dipakai dalam tulisan resmi.', 'Kata kerja tetap bentuk biasa, termasuk negatif dan lampau: 行《い》かないって 言《い》った.'],
                ['って is more casual than と; avoid it in formal writing.', 'The verb stays in plain form, including negative and past: 行《い》かないって 言《い》った.'],
                [
                    $this->ex('ミラーさんは 明日《あした》 来《こ》られないって 言《い》って いました。', 'Miraa-san wa ashita korarenai tte itte imashita.', 'Pak Miller bilang besok tidak bisa datang.', 'Mr. Miller said he cannot come tomorrow.'),
                    $this->ex('入口《いりぐち》に 「工事中《こうじちゅう》」って 書《か》いて あります。', 'Iriguchi ni "koujichuu" tte kaite arimasu.', 'Di pintu masuk tertulis "sedang dalam pembangunan".', 'There is a sign at the entrance saying "Under construction".'),
                    $this->ex('昨日《きのう》、鈴木《すずき》って 人《ひと》から 電話《でんわ》が ありました。', 'Kinou, Suzuki tte hito kara denwa ga arimashita.', 'Kemarin ada telepon dari orang bernama Suzuki.', 'Yesterday there was a call from someone called Suzuki.'),
                ]
            ),

            $this->card(
                'Xって… (topik: menanyakan atau menjelaskan sesuatu)',
                'Xって… (topic marker)',
                'S 普通形《ふつうけい》／N(〜だ を とる) ＋ って',
                'Dalam percakapan akrab, って dipakai untuk mengangkat sesuatu (X) sebagai topik, mirip は. Cocok untuk menanyakan hal yang kurang dikenal pembicara, atau untuk menjelaskan sifat dan kesan tentang X.',
                'In casual conversation って raises something (X) as the topic, much like は. It is used to ask about something the speaker knows little about, or to describe the nature of X.',
                ['Kata benda dan kata sifat-na: buang だ, langsung って.', '「Xって、どんな N？」 = "X itu N yang seperti apa?"'],
                ['After nouns and na-adjectives, drop だ and attach って directly.', '「Xって、どんな N？」 = "What kind of N is X?"'],
                [
                    $this->ex('ねえ、「ぬか漬《づ》け」って 何《なん》？', 'Nee, "nukazuke" tte nan?', 'Eh, "nukazuke" itu apa?', 'Say, what is "nukazuke"?'),
                    $this->ex('ここの ラーメンって、本当《ほんとう》に おいしいね。', 'Koko no raamen tte, hontou ni oishii ne.', 'Ramen di sini enak sekali ya.', 'The ramen here is really good, isn\'t it?'),
                    $this->ex('一人《ひとり》で 旅行《りょこう》するって、案外《あんがい》 楽《たの》しいよ。', 'Hitori de ryokou suru tte, angai tanoshii yo.', 'Bepergian sendirian itu ternyata menyenangkan.', 'Traveling alone is surprisingly fun.'),
                ]
            ),

            $this->card(
                'Vる つもりはない (tidak berniat)',
                'Vる つもりはない (no intention of ~)',
                'V 辞書形《じしょけい》 ＋ つもりはない',
                'Bentuk negatif つもりだ yang lebih kuat: "sama sekali tidak berniat ~". Dipakai untuk menolak dengan tegas ajakan atau anggapan orang. Bila isi niatnya sudah jelas, bisa diringkas menjadi そのつもりはない.',
                'A stronger negative form of つもりだ: "I have no intention at all of ~". Used to refuse firmly an invitation or an assumption. If the content is already clear, it can be shortened to そのつもりはない.',
                ['〜ないつもりだ lebih lembut; 〜つもりはない lebih tegas.', 'Bentuk sopan: つもりは ありません.'],
                ['〜ないつもりだ is softer; 〜つもりはない is firmer.', 'Polite form: つもりは ありません.'],
                [
                    $this->ex('会社《かいしゃ》を 辞《や》める つもりは ありません。', 'Kaisha o yameru tsumori wa arimasen.', 'Saya tidak berniat berhenti dari perusahaan.', 'I have no intention of quitting the company.'),
                    $this->ex('彼《かれ》に 謝《あやま》る つもりは ない。', 'Kare ni ayamaru tsumori wa nai.', 'Aku tidak berniat minta maaf padanya.', 'I have no intention of apologizing to him.'),
                    $this->ex('もう 二度《にど》と あの 店《みせ》には 行《い》く つもりは ない。', 'Mou nido to ano mise ni wa iku tsumori wa nai.', 'Saya tidak berniat pergi lagi ke toko itu.', 'I have no intention of ever going to that shop again.'),
                ]
            ),

            $this->card(
                'Vる／Vない つもりだった (niat di masa lalu)',
                'Vる／Vない つもりだった (had intended to ~)',
                'V 辞書形《じしょけい》／V ない形 ＋ つもりだった',
                'Bentuk lampau つもりだ: "tadinya berniat ~". Sering disusul kalimat yang menyatakan niat itu tidak terlaksana atau pikiran berubah (〜が、…).',
                'The past form of つもりだ: "I had intended to ~". It is often followed by a clause saying the plan did not work out or that one changed one\'s mind (〜が、…).',
                ['Pola umum: 〜つもりでしたが、〜ことにしました (berubah pikiran).', 'Dapat dipakai untuk meminta maaf karena tidak jadi melakukan sesuatu.'],
                ['Common pattern: 〜つもりでしたが、〜ことにしました (changed my mind).', 'Can be used to apologise for not having done something.'],
                [
                    $this->ex('昨日《きのう》は 早《はや》く 寝《ね》る つもりでしたが、つい 遅《おそ》くまで 起《お》きて いました。', 'Kinou wa hayaku neru tsumori deshita ga, tsui osoku made okite imashita.', 'Kemarin saya berniat tidur cepat, tetapi tanpa sadar tetap terjaga sampai larut.', 'I meant to go to bed early yesterday, but I ended up staying up late.'),
                    $this->ex('今日《きょう》は 買《か》い物《もの》に 行《い》かない つもりだったが、友達《ともだち》に 誘《さそ》われて 行《い》った。', 'Kyou wa kaimono ni ikanai tsumori datta ga, tomodachi ni sasowarete itta.', 'Hari ini tadinya saya tidak berniat belanja, tetapi diajak teman lalu pergi.', 'I had not intended to go shopping today, but a friend invited me and I went.'),
                    $this->ex('手紙《てがみ》を 出《だ》す つもりだったのに、忘《わす》れて しまいました。', 'Tegami o dasu tsumori datta no ni, wasurete shimaimashita.', 'Saya berniat mengirim surat, tetapi malah lupa.', 'I meant to mail the letter, but I forgot.'),
                ]
            ),

            $this->card(
                'Vた／Vている つもり (menganggap sudah ~)',
                'Vた／Vている つもり (thinking one has ~)',
                'Vた／Vている／いA／なA(〜な)／N(〜の) ＋ つもり',
                'Menyatakan bahwa pelaku mengira atau menganggap sesuatu sudah begitu, padahal kenyataannya bisa berbeda atau tidak diketahui. Dengan つもりで juga berarti "dengan menganggap / seolah-olah ~", misalnya saat berlatih.',
                'Shows that the agent thinks or believes something is so, although in reality it may not be, or it is unknown. With つもりで it also means "as if ~" or "pretending ~", for example when practising.',
                ['〜つもりで やって みる = mencoba seolah-olah ~.', 'Hasilnya sering berbeda dari anggapan: 〜つもりだったが、実際《じっさい》は…'],
                ['〜つもりで やって みる = try doing it as if ~.', 'The result often differs from the belief: 〜つもりだったが、実際《じっさい》は…'],
                [
                    $this->ex('鍵《かぎ》は かけた つもりでしたが、かかって いませんでした。', 'Kagi wa kaketa tsumori deshita ga, kakatte imasen deshita.', 'Saya kira sudah mengunci, tetapi ternyata tidak terkunci.', 'I thought I had locked it, but it was not locked.'),
                    $this->ex('自分《じぶん》では 頑張《がんば》って いる つもりです。', 'Jibun de wa ganbatte iru tsumori desu.', 'Menurut saya sendiri, saya sudah berusaha keras.', 'I think I am doing my best.'),
                    $this->ex('外国《がいこく》に いる つもりで、一日中《いちにちじゅう》 日本語《にほんご》だけで 話《はな》して みた。', 'Gaikoku ni iru tsumori de, ichinichijuu nihongo dake de hanashite mita.', 'Seolah-olah sedang di luar negeri, saya mencoba berbicara hanya dalam bahasa Jepang seharian.', 'Pretending I was abroad, I tried speaking only Japanese all day.'),
                ]
            ),

            $this->card(
                'Vてばかりいる／Nばかり Vている (hanya ~ terus)',
                'Vてばかりいる／Nばかり Vている (does nothing but ~)',
                'V て形 ＋ ばかりいる／N ＋ ばかり ＋ V(他動詞《たどうし》)ている',
                'Menyatakan suatu tindakan dilakukan terus-menerus atau berulang, dengan nada mengkritik atau tidak puas. Dengan kata kerja transitif, ばかり dapat langsung mengikuti kata benda objeknya.',
                'Shows that an action is done constantly or repeatedly, with a tone of criticism or dissatisfaction. With transitive verbs, ばかり can follow the object noun directly.',
                ['Intransitif: Vてばかりいる (寝《ね》て ばかり いる); transitif: Nばかり Vている (ゲームばかり している).', 'Biasanya dipakai pada orang lain dengan nada keluhan.'],
                ['Intransitive: Vてばかりいる (寝《ね》て ばかり いる); transitive: Nばかり Vている (ゲームばかり している).', 'Normally said about others with a complaining tone.'],
                [
                    $this->ex('息子《むすこ》は 休《やす》みの 日《ひ》は 寝《ね》て ばかり いる。', 'Musuko wa yasumi no hi wa nete bakari iru.', 'Anak laki-laki saya di hari libur hanya tidur terus.', 'On his days off my son does nothing but sleep.'),
                    $this->ex('夫《おっと》は 最近《さいきん》 テレビばかり 見《み》て いる。', 'Otto wa saikin terebi bakari mite iru.', 'Suami saya akhir-akhir ini hanya menonton TV.', 'My husband does nothing but watch TV these days.'),
                    $this->ex('あの 人《ひと》は 文句《もんく》ばかり 言《い》って いる。', 'Ano hito wa monku bakari itte iru.', 'Orang itu hanya mengeluh terus.', 'That person does nothing but complain.'),
                ]
            ),

            $this->card(
                '…とか…とか (menyebut beberapa contoh)',
                '…とか…とか (listing examples)',
                'N／S 普通形《ふつうけい》 ＋ とか ＋ N／S ＋ とか',
                'Dipakai untuk menyebut beberapa contoh yang sejenis, "misalnya ~ atau ~". Yang disebut bisa kata benda, bisa juga kalimat utuh seperti kutipan ucapan atau pikiran.',
                'Used to list several similar examples, "such as ~ or ~". What is listed can be nouns or whole sentences, like quotations of speech or thought.',
                ['Lebih lisan daripada や dan など.', 'Dengan kalimat utuh sering untuk mengutip hal yang sering dikatakan orang: 「〜」とか 言《い》われる.'],
                ['More colloquial than や and など.', 'With whole sentences it often quotes things people say: 「〜」とか 言《い》われる.'],
                [
                    $this->ex('休《やす》みの 日《ひ》は 映画《えいが》を 見《み》に 行《い》くとか、友達《ともだち》と 食事《しょくじ》するとか して います。', 'Yasumi no hi wa eiga o mi ni iku toka, tomodachi to shokuji suru toka shite imasu.', 'Di hari libur saya misalnya menonton film atau makan bersama teman.', 'On my days off I do things like going to the movies or eating out with friends.'),
                    $this->ex('日本《にほん》では 寿司《すし》とか 天《てん》ぷらとかが 人気《にんき》です。', 'Nihon de wa sushi toka tenpura toka ga ninki desu.', 'Di Jepang, sushi dan tempura misalnya, populer.', 'In Japan things like sushi and tempura are popular.'),
                    $this->ex('父《ちち》には 「早《はや》く 結婚《けっこん》しろ」とか 「仕事《しごと》は まだか」とか よく 言《い》われます。', 'Chichi ni wa "hayaku kekkon shiro" toka "shigoto wa mada ka" toka yoku iwaremasu.', 'Ayah sering berkata hal seperti "cepatlah menikah" atau "belum dapat kerja?".', 'My father often says things like "get married soon" or "haven\'t you found a job yet?".'),
                ]
            ),

            $this->card(
                'Vて くる (munculnya keadaan baru)',
                'Vて くる (a new condition arises)',
                'V て形 ＋ くる',
                'Menyatakan bahwa suatu peristiwa baru muncul sehingga sesuatu menjadi terasa, terlihat, atau terdengar: hujan mulai turun, bau tercium, kantuk datang. Biasanya dengan kata kerja persepsi atau perubahan.',
                'Shows that something new has happened and as a result something becomes perceptible: rain starts to fall, a smell drifts over, drowsiness comes on. It is typically used with perception or change verbs.',
                ['Contoh umum: 見《み》えて くる, 聞《き》こえて くる, 降《ふ》って くる, 眠《ねむ》く なって くる.', 'Bentuk lampau: 〜て きた.'],
                ['Common examples: 見《み》えて くる, 聞《き》こえて くる, 降《ふ》って くる, 眠《ねむ》く なって くる.', 'Past form: 〜て きた.'],
                [
                    $this->ex('雨《あめ》が 降《ふ》って きました。傘《かさ》を 出《だ》しましょう。', 'Ame ga futte kimashita. Kasa o dashimashou.', 'Hujan mulai turun. Mari keluarkan payung.', 'It has started to rain. Let us take out the umbrella.'),
                    $this->ex('薬《くすり》を 飲《の》んだら、眠《ねむ》く なって きた。', 'Kusuri o nondara, nemuku natte kita.', 'Setelah minum obat, mulai terasa mengantuk.', 'After taking the medicine I started to feel sleepy.'),
                    $this->ex('台所《だいどころ》から カレーの いい においが して きた。', 'Daidokoro kara karee no ii nioi ga shite kita.', 'Dari dapur tercium aroma kari yang sedap.', 'A nice smell of curry came wafting from the kitchen.'),
                ]
            ),

            $this->card(
                'Vて くる／Vて いく (arah gerakan)',
                'Vて くる／Vて いく (direction of motion)',
                'V て形 ＋ くる／いく',
                'Dipakai dengan kata kerja gerak untuk menunjukkan arah. 〜てくる: gerakan mendekati pembicara (atau kembali ke tempat semula); 〜ていく: gerakan menjauh dari pembicara.',
                'Attached to motion verbs to show direction. 〜てくる: motion toward the speaker (or coming back); 〜ていく: motion away from the speaker.',
                ['持《も》って くる = membawa ke sini; 持《も》って いく = membawa ke sana.', 'Pilihan くる／いく ditentukan dari sudut pandang pembicara.'],
                ['持《も》って くる = bring here; 持《も》って いく = take there.', 'The choice of くる／いく depends on the speaker\'s viewpoint.'],
                [
                    $this->ex('遠《とお》くから 友達《ともだち》が 走《はし》って きました。', 'Tooku kara tomodachi ga hashitte kimashita.', 'Dari kejauhan teman saya berlari menghampiri.', 'A friend came running from far away.'),
                    $this->ex('先生《せんせい》は 資料《しりょう》を 持《も》って 教室《きょうしつ》を 出《で》て いった。', 'Sensei wa shiryou o motte kyoushitsu o dete itta.', 'Guru membawa dokumen lalu meninggalkan kelas.', 'The teacher took the documents and left the classroom.'),
                    $this->ex('犬《いぬ》が こちらに 歩《ある》いて くる。', 'Inu ga kochira ni aruite kuru.', 'Seekor anjing berjalan ke arah sini.', 'A dog is walking toward us.'),
                ]
            ),

            $this->card(
                'こ〜 (kata tunjuk untuk isi yang akan disebut)',
                'こ〜 (pointing ahead to what follows)',
                'こ(れ・この・こう・こんな) 〜',
                'Dalam tulisan, kata こ〜 dapat menunjuk isi yang baru akan disebutkan setelahnya (bukan yang sudah disebut). Sering dipakai untuk memperkenalkan kutipan atau isi berita: こんな こと, こう 書《か》いて ある, 次《つぎ》のような.',
                'In writing, a こ〜 word can point to content that comes later in the text rather than something already mentioned. It often introduces a quotation or news item: こんな こと, こう 書《か》いて ある, 次《つぎ》のような.',
                ['Bandingkan: そ〜 menunjuk hal yang sudah disebut; こ〜 menunjuk hal yang akan disebut.', 'Sering ditutup dengan そうだ atau という こと.'],
                ['Compare: そ〜 points back to something already mentioned; こ〜 points ahead to what will be said.', 'Often closed with そうだ or という こと.'],
                [
                    $this->ex('新聞《しんぶん》に こんな 記事《きじ》が ありました。この 町《まち》に 大《おお》きな 図書館《としょかん》が できるそうだ。', 'Shinbun ni konna kiji ga arimashita. Kono machi ni ookina toshokan ga dekiru sou da.', 'Ada berita seperti ini di koran: katanya akan dibangun perpustakaan besar di kota ini.', 'There was an article like this in the paper: a large library is going to be built in this town.'),
                    $this->ex('先生《せんせい》は こう 言《い》った。「失敗《しっぱい》を 恐《おそ》れては いけない」と。', 'Sensei wa kou itta. "Shippai o osorete wa ikenai" to.', 'Guru berkata begini: "Jangan takut gagal."', 'The teacher said this: "Do not be afraid of failure."'),
                    $this->ex('手紙《てがみ》には こんな ことが 書《か》いて あった。「来月《らいげつ》、日本《にほん》へ 行《い》きます」。', 'Tegami ni wa konna koto ga kaite atta. "Raigetsu, Nihon e ikimasu".', 'Di surat tertulis begini: "Bulan depan saya pergi ke Jepang."', 'The letter said this: "I will go to Japan next month."'),
                ]
            ),
        ];
    }
}
