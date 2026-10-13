<?php

namespace Database\Seeders;

/**
 * Tata Bahasa N3 Pelajaran 12 (11 kartu, dengan dialog).
 *   php artisan db:seed --class=N3Lesson12BunpouSeeder
 */
class N3Lesson12BunpouSeeder extends N3GrammarSeeder
{
    protected function unitOrder(): int
    {
        return 12;
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
                $this->dl('A', 'どうして 宿題《しゅくだい》を 出《だ》して いないの？', 'Doushite shukudai o dashite inai no?', 'Mengapa PR-nya belum dikumpulkan?', 'Why have you not handed in your homework?'),
                $this->dl('B', 'すみません、昨日《きのう》 熱《ねつ》が あったものですから。', 'Sumimasen, kinou netsu ga atta mono desu kara.', 'Maaf, soalnya kemarin saya demam.', 'I am sorry, it is because I had a fever yesterday.'),
                $this->dl('A', 'そう。じゃあ、明日《あした》まで 待《ま》ちます。', 'Sou. Jaa, ashita made machimasu.', 'Begitu. Baik, saya tunggu sampai besok.', 'I see. Then I will wait until tomorrow.'),
                $this->dl('B', 'ありがとうございます。', 'Arigatou gozaimasu.', 'Terima kasih banyak.', 'Thank you very much.'),
            ],
            2 => [
                $this->dl('A', '昨日《きのう》、どうして 寝《ね》られなかったの？', 'Kinou, doushite nerarenakatta no?', 'Kemarin kenapa kamu tidak bisa tidur?', 'Why could you not sleep yesterday?'),
                $this->dl('B', '隣《となり》の 部屋《へや》の 人《ひと》に 夜中《よなか》まで 騒《さわ》がれたんだ。', 'Tonari no heya no hito ni yonaka made sawagareta n da.', 'Orang di kamar sebelah ribut sampai tengah malam, aku jadi terganggu.', 'The person in the next room made a racket until midnight, and it affected me.'),
                $this->dl('A', 'それは 大変《たいへん》だったね。', 'Sore wa taihen datta ne.', 'Wah, berat ya.', 'That must have been tough.'),
                $this->dl('B', 'その うえ、帰《かえ》り道《みち》で 雨《あめ》にも 降《ふ》られたし。', 'Sono ue, kaerimichi de ame ni mo furareta shi.', 'Sudah begitu, di jalan pulang aku juga kehujanan.', 'On top of that, I got rained on on the way home too.'),
            ],
            3 => [
                $this->dl('A', '顔色《かおいろ》が 悪《わる》いけど、どうしたの？', 'Kaoiro ga warui kedo, doushita no?', 'Wajahmu pucat, ada apa?', 'You look pale. What happened?'),
                $this->dl('B', '朝《あさ》の 電車《でんしゃ》で 足《あし》を 踏《ふ》まれて、まだ 痛《いた》いんだ。', 'Asa no densha de ashi o fumarete, mada itai n da.', 'Di kereta pagi kakiku terinjak, dan masih sakit.', 'Someone stepped on my foot on the train this morning and it still hurts.'),
                $this->dl('A', '大丈夫《だいじょうぶ》？ 病院《びょういん》に 行《い》った ほうが いいよ。', 'Daijoubu? Byouin ni itta hou ga ii yo.', 'Tidak apa-apa? Lebih baik pergi ke rumah sakit.', 'Are you all right? You should go to the hospital.'),
                $this->dl('B', 'うん、ありがとう。', 'Un, arigatou.', 'Iya, terima kasih.', 'Yes, thanks.'),
            ],
            4 => [
                $this->dl('A', '休《やす》みの 日《ひ》は 何《なに》を して いますか。', 'Yasumi no hi wa nani o shite imasu ka.', 'Di hari libur Anda melakukan apa?', 'What do you do on your days off?'),
                $this->dl('B', '料理《りょうり》を 作《つく》ったり、映画《えいが》を 見《み》たり して います。', 'Ryouri o tsukuttari, eiga o mitari shite imasu.', 'Saya memasak, menonton film, dan sebagainya.', 'I do things like cooking and watching films.'),
                $this->dl('A', '外《そと》には 出《で》ませんか。', 'Soto ni wa demasen ka.', 'Tidak keluar rumah?', 'Do you not go out?'),
                $this->dl('B', '天気《てんき》が よければ、公園《こうえん》を 散歩《さんぽ》したり します。', 'Tenki ga yokereba, kouen o sanpo shitari shimasu.', 'Kalau cuaca bagus, saya kadang berjalan-jalan di taman.', 'If the weather is good, I sometimes take a walk in the park.'),
            ],
            5 => [
                $this->dl('A', 'あれ、窓《まど》が 開《あ》けっぱなしだよ。', 'Are, mado ga akeppanashi da yo.', 'Lho, jendelanya dibiarkan terbuka.', 'Hey, the window has been left open.'),
                $this->dl('B', 'ごめん、閉《し》め忘《わす》れた。', 'Gomen, shimewasureta.', 'Maaf, lupa menutupnya.', 'Sorry, I forgot to close it.'),
                $this->dl('A', '雨《あめ》が 降《ふ》ったら 部屋《へや》が 濡《ぬ》れるよ。', 'Ame ga futtara heya ga nureru yo.', 'Kalau hujan, kamarnya basah lho.', 'If it rains, the room will get wet.'),
                $this->dl('B', 'すぐ 閉《し》めて くる。', 'Sugu shimete kuru.', 'Aku tutup sekarang.', 'I will go and close it right away.'),
            ],
            6 => [
                $this->dl('A', '日本語《にほんご》の 試験《しけん》、合格《ごうかく》したそうですね。おめでとうございます。', 'Nihongo no shiken, goukaku shita sou desu ne. Omedetou gozaimasu.', 'Katanya Anda lulus ujian bahasa Jepang. Selamat ya.', 'I hear you passed the Japanese exam. Congratulations.'),
                $this->dl('B', 'ありがとうございます。先生《せんせい》が 毎週《まいしゅう》 練習《れんしゅう》に つき合《あ》って くださった おかげです。', 'Arigatou gozaimasu. Sensei ga maishuu renshuu ni tsukiatte kudasatta okage desu.', 'Terima kasih. Berkat guru yang menemani saya berlatih setiap minggu.', 'Thank you. It is thanks to my teacher, who practised with me every week.'),
                $this->dl('A', '努力《どりょく》した おかげでも ありますよ。', 'Doryoku shita okage demo arimasu yo.', 'Berkat usaha Anda sendiri juga.', 'It is thanks to your own effort too.'),
                $this->dl('B', 'そう 言《い》って いただけると うれしいです。', 'Sou itte itadakeru to ureshii desu.', 'Senang sekali mendengar Anda berkata begitu.', 'I am glad to hear you say so.'),
            ],
            7 => [
                $this->dl('A', '顔《かお》が 赤《あか》いけど、熱《ねつ》が あるの？', 'Kao ga akai kedo, netsu ga aru no?', 'Wajahmu merah, apa kamu demam?', 'Your face is red. Do you have a fever?'),
                $this->dl('B', 'ううん、昨日《きのう》 お酒《さけ》を 飲《の》みすぎた せいだよ。', 'Uun, kinou osake o nomisugita sei da yo.', 'Bukan, ini karena kemarin aku kebanyakan minum.', 'No, it is because I drank too much yesterday.'),
                $this->dl('A', '二日酔《ふつかよ》いか。', 'Futsukayoi ka.', 'Oh, mabuk sisa semalam.', 'Ah, a hangover.'),
                $this->dl('B', 'うん。頭《あたま》が 痛《いた》い。', 'Un. Atama ga itai.', 'Iya. Kepalaku sakit.', 'Yes. My head hurts.'),
            ],
            8 => [
                $this->dl('A', '隣《となり》の 部屋《へや》、静《しず》かだね。', 'Tonari no heya, shizuka da ne.', 'Kamar sebelah sepi ya.', 'The room next door is quiet.'),
                $this->dl('B', '電気《でんき》も 消《き》えて いるし、出《で》かけた みたいだね。', 'Denki mo kiete iru shi, dekaketa mitai da ne.', 'Lampunya juga mati, sepertinya mereka pergi.', 'The lights are off too, so they seem to have gone out.'),
                $this->dl('A', '旅行《りょこう》かな。', 'Ryokou kana.', 'Mungkin sedang bepergian.', 'Maybe they are on a trip.'),
                $this->dl('B', 'かもしれないね。', 'Kamo shirenai ne.', 'Bisa jadi.', 'Could be.'),
            ],
            9 => [
                $this->dl('A', '田中《たなか》さんは お酒《さけ》を よく 飲《の》みますか。', 'Tanaka-san wa osake o yoku nomimasu ka.', 'Apakah Pak Tanaka sering minum alkohol?', 'Does Mr. Tanaka drink alcohol often?'),
                $this->dl('B', 'どちらかと 言《い》えば、飲《の》まない ほうです。付《つ》き合《あ》い くらいで。', 'Dochira ka to ieba, nomanai hou desu. Tsukiai kurai de.', 'Kalau boleh dibilang, ia termasuk yang tidak minum. Hanya untuk menemani.', 'If anything, he is on the non-drinking side. Just to be sociable.'),
                $this->dl('A', 'そうですか。意外《いがい》です。', 'Sou desu ka. Igai desu.', 'Begitu ya. Di luar dugaan.', 'Is that so? I am surprised.'),
                $this->dl('B', '見《み》た 目《め》より おとなしい ほうですよ。', 'Mita me yori otonashii hou desu yo.', 'Dibanding penampilannya, ia termasuk orang yang pendiam.', 'He is on the quiet side compared with how he looks.'),
            ],
            10 => [
                $this->dl('A', '七夕《たなばた》の 短冊《たんざく》に 何《なに》を 書《か》いた？', 'Tanabata no tanzaku ni nani o kaita?', 'Kamu menulis apa di kertas harapan Tanabata?', 'What did you write on your Tanabata wish strip?'),
                $this->dl('B', '「家族《かぞく》が みんな 元気《げんき》で いられますように」って 書《か》いたよ。', '"Kazoku ga minna genki de iraremasu you ni" tte kaita yo.', 'Aku menulis "Semoga keluarga selalu sehat".', 'I wrote, "May my whole family stay healthy."'),
                $this->dl('A', 'いいね。私《わたし》は 「試験《しけん》に 合格《ごうかく》できますように」と 書《か》いたよ。', 'Ii ne. Watashi wa "shiken ni goukaku dekimasu you ni" to kaita yo.', 'Bagus. Aku menulis "Semoga bisa lulus ujian".', 'Nice. I wrote, "May I pass the exam."'),
                $this->dl('B', '叶《かな》うと いいね。', 'Kanau to ii ne.', 'Semoga terkabul ya.', 'I hope it comes true.'),
            ],
            11 => [
                $this->dl('A', '昨日《きのう》の 夜《よる》、うるさく して すみませんでした。', 'Kinou no yoru, urusaku shite sumimasen deshita.', 'Maaf kemarin malam saya membuat berisik.', 'I am sorry for making noise last night.'),
                $this->dl('B', 'いいえ。でも、音《おと》が 下《した》まで 聞《き》こえるとは 思《おも》わなかったでしょう？', 'Iie. Demo, oto ga shita made kikoeru to wa omowanakatta deshou?', 'Tidak apa-apa. Tapi Anda tidak mengira suaranya terdengar sampai ke bawah, bukan?', 'No problem. But you did not think the sound would reach downstairs, did you?'),
                $this->dl('A', 'ええ、壁《かべ》が こんなに うすいとは 気《き》が 付《つ》きませんでした。', 'Ee, kabe ga konna ni usui to wa ki ga tsukimasen deshita.', 'Ya, saya tidak menyadari dindingnya setipis ini.', 'Right, I did not realize the walls were this thin.'),
                $this->dl('B', 'これから 気《き》を つけて くださいね。', 'Korekara ki o tsukete kudasai ne.', 'Mulai sekarang mohon berhati-hati ya.', 'Please be careful from now on.'),
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    protected function cards(): array
    {
        return [
            $this->card(
                '…もの／もんだから (alasan, dalih)',
                '…もの／もんだから (reason, excuse)',
                'V・いA 普通形《ふつうけい》／なA・N 普通形《ふつうけい》(〜だ → 〜な) ＋ もの／もんだから',
                'Menyatakan alasan atau sebab. 「X ものだから Y」 sering dipakai ketika terjadi hal yang tidak diinginkan (Y), untuk membela diri atau menjelaskan alasan agar terlepas dari tanggung jawab. Bentuk lebih akrab: もんで.',
                'Indicates a reason or cause. 「X ものだから Y」 is often used when something undesirable (Y) has happened, to justify it or give a reason in order to deny responsibility. A more casual variant: もんで.',
                ['Tidak cocok untuk alasan yang objektif seperti 〜から／〜ので: ×この 飛行機《ひこうき》は 速《はや》い ものだから…', 'Di akhir kalimat, 〜ものですから dipakai sebagai permintaan maaf yang sopan.'],
                ['Not suitable for an objective reason as 〜から／〜ので are: ×この 飛行機《ひこうき》は 速《はや》い ものだから…', 'At the end of a sentence, 〜ものですから works as a polite excuse.'],
                [
                    $this->ex('道《みち》が 混《こ》んで いたものですから、遅《おく》れて しまいました。', 'Michi ga konde ita mono desu kara, okurete shimaimashita.', 'Karena jalan macet, saya jadi terlambat.', 'The road was crowded, so I was late.'),
                    $this->ex('子《こ》どもが 熱《ねつ》を 出《だ》したもんだから、会社《かいしゃ》を 休《やす》みました。', 'Kodomo ga netsu o dashita mon da kara, kaisha o yasumimashita.', 'Karena anak saya demam, saya tidak masuk kerja.', 'My child ran a fever, so I took the day off.'),
                    $this->ex('急《きゅう》な 来客《らいきゃく》だったものですから、お茶《ちゃ》しか 出《だ》せませんでした。', 'Kyuu na raikyaku datta mono desu kara, ocha shika dasemasen deshita.', 'Karena tamunya mendadak, saya hanya bisa menyuguhkan teh.', 'The guest came unexpectedly, so I could only offer tea.'),
                ]
            ),

            $this->card(
                '〜(ら)れる (pasif tidak langsung, kata kerja intransitif)',
                '〜(ら)れる (indirect passive, intransitive verb)',
                'N₁ は N₂ に ＋ V 自動詞《じどうし》の 受身形《うけみけい》',
                'Pasif langsung memakai objek kata kerja transitif sebagai subjek. Selain itu, bahasa Jepang punya pasif tidak langsung: kalimat 「X が V する」 dari kata kerja intransitif dapat dijadikan pasif, dengan orang yang terkena dampak sebagai subjek. Ini menyatakan bahwa orang itu dirugikan atau terganggu oleh tindakan tersebut.',
                'Direct passives take the object of a transitive verb as their subject. In addition, Japanese has an indirect passive: a sentence 「X が V する」 built on an intransitive verb can be made passive, with the person affected as the subject. It expresses that this person was inconvenienced or harmed by the action.',
                ['Pemilik dari subjek kata kerja intransitif juga bisa menjadi subjek pasif (子《こ》どもに 泣《な》かれて 困《こま》った).', 'Hampir selalu bernuansa merugikan, sehingga tidak dipakai untuk hal yang menyenangkan.'],
                ['The owner of the subject of an intransitive verb can also become the subject of the passive (子《こ》どもに 泣《な》かれて 困《こま》った).', 'It almost always carries an adverse nuance, so it is not used for pleasant events.'],
                [
                    $this->ex('夜中《よなか》に 隣《となり》の 人《ひと》に 騒《さわ》がれて、眠《ねむ》れなかった。', 'Yonaka ni tonari no hito ni sawagarete, nemurenakatta.', 'Tengah malam tetangga ribut, sehingga saya tidak bisa tidur.', 'The neighbour made noise in the middle of the night and I could not sleep.'),
                    $this->ex('出《で》かける とき、急《きゅう》に 雨《あめ》に 降《ふ》られた。', 'Dekakeru toki, kyuu ni ame ni furareta.', 'Saat hendak berangkat, tiba-tiba saya kehujanan.', 'Just as I was leaving, I was caught in the rain.'),
                    $this->ex('昨日《きのう》、友達《ともだち》に 来《こ》られて、勉強《べんきょう》が できなかった。', 'Kinou, tomodachi ni korarete, benkyou ga dekinakatta.', 'Kemarin teman datang bertamu, jadi saya tidak bisa belajar.', 'A friend turned up yesterday, so I could not study.'),
                ]
            ),

            $this->card(
                '〜(ら)れる (pasif tidak langsung, kata kerja transitif)',
                '〜(ら)れる (indirect passive, transitive verb)',
                'N₁ は N₂ に N₃ を ＋ V 他動詞《たどうし》の 受身形《うけみけい》',
                'Kata kerja transitif juga dapat dipakai dalam kalimat pasif yang menyatakan bahwa subjeknya dirugikan atau terganggu. Biasanya milik atau bagian tubuh subjek (N₃) menjadi sasaran tindakan orang lain (N₂), dan N₁ dirugikan olehnya.',
                'A transitive verb can also be used in a passive sentence indicating that its subject has been inconvenienced or harmed. Typically something belonging to the subject, or part of their body (N₃), is the target of another person\'s action (N₂), and N₁ suffers because of it.',
                ['Bentuk 〜に ＋ N を ＋ Vられる: yang dirugikan berada di posisi subjek.', 'Bandingkan pasif kausatif (〜(さ)せられる) yang menyatakan dipaksa melakukan.'],
                ['Pattern 〜に ＋ N を ＋ Vられる: the person harmed is in subject position.', 'Compare the causative-passive (〜(さ)せられる), which expresses being made to do something.'],
                [
                    $this->ex('電車《でんしゃ》の 中《なか》で 隣《となり》の 人《ひと》に 足《あし》を 踏《ふ》まれた。', 'Densha no naka de tonari no hito ni ashi o fumareta.', 'Di dalam kereta, kaki saya terinjak oleh orang di sebelah.', 'On the train, the person next to me stepped on my foot.'),
                    $this->ex('弟《おとうと》に 大切《たいせつ》な 写真《しゃしん》を 捨《す》てられて、腹《はら》が 立《た》った。', 'Otouto ni taisetsu na shashin o suterarete, hara ga tatta.', 'Foto berharga saya dibuang adik, saya jadi marah.', 'My brother threw away my precious photo and I got angry.'),
                    $this->ex('駅前《えきまえ》に 自転車《じてんしゃ》を 止《と》められて、歩《ある》きにくい。', 'Ekimae ni jitensha o tomerarete, aruki nikui.', 'Orang memarkir sepeda di depan stasiun, jadi sulit berjalan.', 'People park their bicycles in front of the station, which makes it hard to walk.'),
                ]
            ),

            $this->card(
                '〜たり〜たり (menyebut contoh)',
                '〜たり〜たり (listing examples)',
                'V た形＋り／いA(〜かったり)／なA・N(〜だったり)',
                '(1) 〜たり 〜たり する dipakai untuk menyebut kira-kira dua contoh dari beberapa tindakan. (2) Bila V₁ dan V₂ adalah kata kerja yang berlawanan arti, artinya kedua keadaan terjadi bergantian. (3) Dengan kata sifat, menunjukkan bahwa ada bermacam-macam jenis.',
                '(1) 〜たり 〜たり する gives roughly two suitable examples among a number of actions. (2) When V₁ and V₂ are verbs with opposite meanings, the two occur alternately. (3) With adjectives, it shows that there are different kinds of something.',
                ['Akhir kalimat memakai する: 〜たり 〜たり する.', 'Satu kata kerja saja pun bisa: 散歩《さんぽ》したり します ("misalnya jalan-jalan").'],
                ['The sentence ends with する: 〜たり 〜たり する.', 'A single verb is possible: 散歩《さんぽ》したり します ("for example, take walks").'],
                [
                    $this->ex('週末《しゅうまつ》は 本《ほん》を 読《よ》んだり、散歩《さんぽ》を したり して います。', 'Shuumatsu wa hon o yondari, sanpo o shitari shite imasu.', 'Akhir pekan saya membaca buku, berjalan-jalan, dan sebagainya.', 'At weekends I do things like reading and going for walks.'),
                    $this->ex('子《こ》どもたちは 部屋《へや》を 出《で》たり 入《はい》ったり して いる。', 'Kodomotachi wa heya o detari haittari shite iru.', 'Anak-anak keluar masuk ruangan berulang kali.', 'The children keep going in and out of the room.'),
                    $this->ex('この 町《まち》の 天気《てんき》は よかったり 悪《わる》かったり します。', 'Kono machi no tenki wa yokattari warukattari shimasu.', 'Cuaca di kota ini kadang bagus, kadang buruk.', 'The weather in this town is sometimes good and sometimes bad.'),
                ]
            ),

            $this->card(
                '〜っぱなし (dibiarkan terus)',
                '〜っぱなし (left as it is)',
                'V ます語幹《ごかん》 ＋ っぱなし (だ／で／に する)',
                'Menyatakan keadaan yang seharusnya segera diakhiri, tetapi dibiarkan berlanjut terlalu lama atau tidak diinginkan. Contoh: つけっぱなし (dibiarkan menyala), 開《あ》けっぱなし (dibiarkan terbuka), 立《た》ちっぱなし (berdiri terus).',
                'Indicates a situation that would normally be ended promptly but is left going for an undesirably long time. Examples: つけっぱなし (left on), 開《あ》けっぱなし (left open), 立《た》ちっぱなし (standing the whole time).',
                ['Bernuansa tidak baik atau menegur: 出《だ》しっぱなしに しないで.', 'Bandingkan 〜まま yang lebih netral (電気《でんき》を つけた まま).'],
                ['It carries a negative or reproachful nuance: 出《だ》しっぱなしに しないで.', 'Compare the more neutral 〜まま (電気《でんき》を つけた まま).'],
                [
                    $this->ex('電気《でんき》を つけっぱなしで 寝《ね》て しまった。', 'Denki o tsukeppanashi de nete shimatta.', 'Saya tertidur dengan lampu dibiarkan menyala.', 'I fell asleep with the light left on.'),
                    $this->ex('昨日《きのう》は 電車《でんしゃ》が 混《こ》んで いて、一時間《いちじかん》 立《た》ちっぱなしだった。', 'Kinou wa densha ga konde ite, ichijikan tachippanashi datta.', 'Kemarin keretanya penuh, saya berdiri terus selama satu jam.', 'The train was packed yesterday and I stood for a whole hour.'),
                    $this->ex('本《ほん》を 出《だ》しっぱなしに しないで、片付《かたづ》けて ください。', 'Hon o dashippanashi ni shinaide, katazukete kudasai.', 'Jangan biarkan buku berserakan, tolong rapikan.', 'Please do not leave your books out; put them away.'),
                ]
            ),

            $this->card(
                '…おかげで、…・…おかげだ (berkat)',
                '…おかげで、…・…おかげだ (thanks to)',
                'V・いA 普通形《ふつうけい》／なA(〜な)／N(〜の) ＋ おかげで／おかげだ',
                '「X おかげで Y／Y は X おかげだ」 dipakai ketika sebab tertentu (X) menghasilkan akibat yang menguntungkan (Y). Sering mengandung rasa terima kasih kepada orang atau hal yang menjadi sebabnya.',
                '「X おかげで Y／Y は X おかげだ」 is used when a particular cause (X) produces a favourable result (Y). It often carries gratitude toward the person or thing that caused it.',
                ['Untuk akibat yang buruk pakai せいで (kartu berikutnya).', 'Kata benda memakai の: 先生《せんせい》の おかげで／皆様《みなさま》の おかげで.'],
                ['For a bad result use せいで (next card).', 'Nouns take の: 先生《せんせい》の おかげで／皆様《みなさま》の おかげで.'],
                [
                    $this->ex('先輩《せんぱい》が 教《おし》えて くれた おかげで、仕事《しごと》に 早《はや》く 慣《な》れた。', 'Senpai ga oshiete kureta okage de, shigoto ni hayaku nareta.', 'Berkat senior yang mengajari saya, saya cepat terbiasa dengan pekerjaan.', 'Thanks to my senior colleague teaching me, I got used to the work quickly.'),
                    $this->ex('天気《てんき》が よかった おかげで、運動会《うんどうかい》は 無事《ぶじ》に 終《お》わった。', 'Tenki ga yokatta okage de, undoukai wa buji ni owatta.', 'Berkat cuaca bagus, acara olahraga berakhir dengan lancar.', 'Thanks to the good weather, the sports day ended without a hitch.'),
                    $this->ex('医者《いしゃ》の おかげで、父《ちち》は 元気《げんき》に なりました。', 'Isha no okage de, chichi wa genki ni narimashita.', 'Berkat dokter, ayah saya menjadi sehat kembali.', 'Thanks to the doctor, my father got well again.'),
                ]
            ),

            $this->card(
                '…せいで、…・…せいだ (gara-gara)',
                '…せいで、…・…せいだ (because of, blame)',
                'V・いA 普通形《ふつうけい》／なA(〜な)／N(〜の) ＋ せいで／せいだ',
                'Kebalikan dari おかげで: dipakai ketika sebab tertentu menghasilkan akibat yang tidak menguntungkan. Menyalahkan sebab itu: "gara-gara ~". Bentuk せいだ dipakai bila akibat disebut lebih dulu dan sebabnya menjadi predikat.',
                'The counterpart of おかげで: used when a cause produces an unfavourable result. It puts the blame on that cause: "because of ~". せいだ is used when the result is stated first and the cause becomes the predicate.',
                ['Dengan せい, pembicara memandang sebab tersebut sebagai biang masalah.', 'Ungkapan 〜の せいに する = melemparkan kesalahan kepada ~.'],
                ['With せい, the speaker regards the cause as to blame.', 'The expression 〜の せいに する = to put the blame on ~.'],
                [
                    $this->ex('寝坊《ねぼう》した せいで、バスに 乗《の》り遅《おく》れた。', 'Nebou shita sei de, basu ni noriokureta.', 'Gara-gara bangun kesiangan, saya ketinggalan bus.', 'Because I overslept, I missed the bus.'),
                    $this->ex('工事《こうじ》の せいで、駅《えき》までの 道《みち》が 通《とお》れない。', 'Kouji no sei de, eki made no michi ga tooranai.', 'Gara-gara ada pembangunan, jalan menuju stasiun tidak bisa dilewati.', 'Because of the roadworks, the road to the station is impassable.'),
                    $this->ex('昨日《きのう》 食《た》べすぎた せいで、おなかが 痛《いた》い。', 'Kinou tabesugita sei de, onaka ga itai.', 'Gara-gara kemarin makan berlebihan, perut saya sakit.', 'Because I ate too much yesterday, my stomach hurts.'),
                ]
            ),

            $this->card(
                '…みたいです (dugaan dari penampilan)',
                '…みたいです (conjecture from appearance)',
                'V・いA 普通形《ふつうけい》／なA・N 普通形《ふつうけい》(〜だ なし) ＋ みたいだ／みたいです',
                'Menyatakan dugaan berdasarkan bagaimana suatu keadaan terlihat atau terdengar, misalnya dari penampilan luarnya. Artinya sama dengan 〜ようだ, tetapi 〜みたいだ lebih lisan, sedangkan 〜ようだ dipakai dalam tulisan atau tutur kata formal.',
                'Indicates a guess from how a situation looks or sounds, such as its external appearance. It means the same as 〜ようだ, but 〜みたいだ is more spoken while 〜ようだ is used in writing or formal speech.',
                ['なA／N: 〜みたいだ (tanpa だ): 元気《げんき》みたいだ, 学生《がくせい》みたいだ.', 'Sebagai penerang kata benda: 〜みたいな N.'],
                ['な-adjectives／nouns: 〜みたいだ (no だ): 元気《げんき》みたいだ, 学生《がくせい》みたいだ.', 'To modify a noun: 〜みたいな N.'],
                [
                    $this->ex('外《そと》が 濡《ぬ》れて いる。夜《よる》に 雨《あめ》が 降《ふ》った みたいだ。', 'Soto ga nurete iru. Yoru ni ame ga futta mitai da.', 'Di luar basah. Sepertinya semalam hujan.', 'It is wet outside. It looks like it rained during the night.'),
                    $this->ex('部屋《へや》の 電気《でんき》が ついて いる。山田《やまだ》さんは まだ 会社《かいしゃ》に いる みたいです。', 'Heya no denki ga tsuite iru. Yamada-san wa mada kaisha ni iru mitai desu.', 'Lampu ruangan menyala. Sepertinya Pak Yamada masih di kantor.', 'The room light is on. Mr. Yamada seems to still be at the office.'),
                    $this->ex('この 店《みせ》、いつも 行列《ぎょうれつ》が できて いる。人気《にんき》みたいだね。', 'Kono mise, itsumo gyouretsu ga dekite iru. Ninki mitai da ne.', 'Toko ini selalu ada antrean. Sepertinya populer ya.', 'This shop always has a queue. It seems to be popular.'),
                ]
            ),

            $this->card(
                'どちらかと言《い》えば、〜ほうだ (kalau boleh dibilang)',
                'どちらかと言《い》えば、〜ほうだ (if anything, on the ~ side)',
                'V・いA 普通形《ふつうけい》／なA(〜な) ＋ ほうだ',
                '「どちらかと 言《い》えば、X ほうだ」 dipakai untuk menyatakan bahwa sesuatu, kalau dilihat secara ketat, tidak sepenuhnya X, tetapi secara keseluruhan dapat dikatakan cenderung X, tanpa menyatakan dengan pasti apakah itu X atau bukan.',
                '「どちらかと 言《い》えば、X ほうだ」 indicates that, strictly speaking, a subject is not completely X, but could be described as being X on the whole, without committing definitely to whether it is X or not.',
                ['どちらかと 言《い》えば boleh dihilangkan: 安《やす》い ほうです.', 'Cocok untuk jawaban yang tidak ingin tegas: 飲《の》む ほうですね.'],
                ['どちらかと 言《い》えば may be dropped: 安《やす》い ほうです.', 'Good for answers where you do not want to be definite: 飲《の》む ほうですね.'],
                [
                    $this->ex('私《わたし》は どちらかと 言《い》えば、朝型《あさがた》の ほうです。', 'Watashi wa dochira ka to ieba, asagata no hou desu.', 'Kalau boleh dibilang, saya termasuk tipe orang pagi.', 'If anything, I am a morning person.'),
                    $this->ex('この 町《まち》の 物価《ぶっか》は、どちらかと 言《い》えば 安《やす》い ほうだ。', 'Kono machi no bukka wa, dochira ka to ieba yasui hou da.', 'Harga barang di kota ini kalau boleh dibilang termasuk murah.', 'Prices in this town are on the cheap side, if anything.'),
                    $this->ex('弟《おとうと》は どちらかと 言《い》えば、おとなしい ほうです。', 'Otouto wa dochira ka to ieba, otonashii hou desu.', 'Adik laki-laki saya cenderung pendiam.', 'My younger brother tends to be on the quiet side.'),
                ]
            ),

            $this->card(
                '〜ますように／〜ませんように (harapan dan doa)',
                '〜ますように／〜ませんように (wish, prayer)',
                'V ます形 ＋ ように／V ません ＋ ように',
                'Menyatakan harapan atau doa agar sesuatu terjadi atau tidak terjadi. Sering bersama どうか／どうぞ saat berbicara kepada diri sendiri, atau sebagai ucapan harapan kepada orang lain.',
                'Expresses a wish, hope or prayer that something will or will not happen. Often used with どうか／どうぞ when talking to oneself, or as a wish directed at someone else.',
                ['Dipakai untuk hal yang tidak sepenuhnya bisa diatur sendiri (kesehatan, cuaca, hasil ujian).', 'Bukan untuk menyatakan niat sendiri; untuk niat pakai 〜つもりです／〜ようと 思《おも》います.'],
                ['Used for things you cannot fully control (health, weather, exam results).', 'Not for stating your own intention; for intentions use 〜つもりです／〜ようと 思《おも》います.'],
                [
                    $this->ex('明日《あした》が 晴《は》れますように。', 'Ashita ga haremasu you ni.', 'Semoga besok cerah.', 'I hope it will be fine tomorrow.'),
                    $this->ex('家族《かぞく》が 病気《びょうき》に なりませんように。', 'Kazoku ga byouki ni narimasen you ni.', 'Semoga keluarga saya tidak sakit.', 'May my family not fall ill.'),
                    $this->ex('どうか 試験《しけん》に 合格《ごうかく》できますように。', 'Douka shiken ni goukaku dekimasu you ni.', 'Semoga saya bisa lulus ujian.', 'Please let me pass the exam.'),
                ]
            ),
            $this->card(
                '〜とは (terkejut, tak disangka)',
                '〜とは (surprise, unexpectedness)',
                'V・いA・なA・N 普通形《ふつうけい》(〜だ) ＋ とは ＋ 驚《おどろ》き・感想《かんそう》',
                'Menyatakan rasa terkejut atau tak percaya terhadap hal yang baru diketahui atau di luar dugaan. Bagian sesudahnya biasanya ungkapan perasaan seperti 驚《おどろ》いた, 知《し》らなかった, 思《おも》わなかった, 気《き》が 付《つ》きませんでした.',
                'Expresses surprise or disbelief at something newly learned or unexpected. What follows is usually an expression of feeling such as 驚《おどろ》いた, 知《し》らなかった, 思《おも》わなかった or 気《き》が 付《つ》きませんでした.',
                ['Dengan 〜だ: 医者《いしゃ》だとは (N／なA memakai だ sebelum とは).', 'Dipakai saat meminta maaf atas hal yang tidak disadari: 音《おと》が 聞《き》こえるとは 気《き》が 付《つ》きませんでした.'],
                ['With 〜だ: 医者《いしゃ》だとは (N／な-adjective keep だ before とは).', 'Used when apologizing for something you did not realize: 音《おと》が 聞《き》こえるとは 気《き》が 付《つ》きませんでした.'],
                [
                    $this->ex('彼女《かのじょ》が 医者《いしゃ》だとは 知《し》りませんでした。', 'Kanojo ga isha da to wa shirimasen deshita.', 'Saya tidak tahu bahwa dia seorang dokter.', 'I did not know she was a doctor.'),
                    $this->ex('こんなに 早《はや》く 終《お》わるとは 思《おも》わなかった。', 'Konna ni hayaku owaru to wa omowanakatta.', 'Tak kusangka akan selesai secepat ini.', 'I never thought it would end this quickly.'),
                    $this->ex('壁《かべ》が こんなに うすいとは 気《き》が 付《つ》きませんでした。', 'Kabe ga konna ni usui to wa ki ga tsukimasen deshita.', 'Saya tidak menyadari dindingnya setipis ini.', 'I did not realize the wall was this thin.'),
                ]
            ),
        ];
    }
}
