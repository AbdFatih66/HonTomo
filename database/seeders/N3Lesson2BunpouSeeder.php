<?php

namespace Database\Seeders;

/**
 * Tata Bahasa N3 Pelajaran 2 (6 kartu, dengan dialog).
 *   php artisan db:seed --class=N3Lesson2BunpouSeeder
 */
class N3Lesson2BunpouSeeder extends N3GrammarSeeder
{
    protected function unitOrder(): int
    {
        return 2;
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
                $this->dl('A', '薬《くすり》を 飲《の》んだら、頭痛《ずつう》は どうなりましたか。', 'Kusuri o nondara, zutsuu wa dou narimashita ka.', 'Setelah minum obat, bagaimana sakit kepalanya?', 'How did your headache get after you took the medicine?'),
                $this->dl('B', '飲《の》んだら、すぐに 楽《らく》に なりました。', 'Nondara, sugu ni raku ni narimashita.', 'Setelah minum, langsung terasa lebih ringan.', 'After I took it, I felt better right away.'),
                $this->dl('A', 'それは よかったです。', 'Sore wa yokatta desu.', 'Syukurlah.', 'I am glad to hear that.'),
                $this->dl('B', 'でも、会社《かいしゃ》に 着《つ》いて かばんを 開《あ》けたら、財布《さいふ》が なかったんです。', 'Demo, kaisha ni tsuite kaban o aketara, saifu ga nakatta n desu.', 'Tapi begitu sampai kantor dan membuka tas, dompet saya tidak ada.', 'But when I got to the office and opened my bag, my wallet was not there.'),
            ],
            2 => [
                $this->dl('A', '「残業《ざんぎょう》」というのは どういう 意味《いみ》ですか。', '"Zangyou" toiu no wa douiu imi desu ka.', 'Apa arti "zangyou"?', 'What does "zangyou" mean?'),
                $this->dl('B', '決《き》まった 時間《じかん》より 長《なが》く 働《はたら》く ことです。', 'Kimatta jikan yori nagaku hataraku koto desu.', 'Artinya bekerja lebih lama dari jam yang ditentukan.', 'It means working longer than the set hours.'),
                $this->dl('A', 'では、「定時《ていじ》」というのは？', 'Dewa, "teiji" toiu no wa?', 'Lalu, "teiji"?', 'Then what about "teiji"?'),
                $this->dl('B', '会社《かいしゃ》が 決《き》めた 終《お》わりの 時間《じかん》の ことです。', 'Kaisha ga kimeta owari no jikan no koto desu.', 'Itu jam selesai kerja yang ditetapkan perusahaan.', 'It is the finishing time set by the company.'),
            ],
            3 => [
                $this->dl('A', '駅《えき》の 前《まえ》に 新《あたら》しい デパートが できるという 話《はなし》を 聞《き》きましたか。', 'Eki no mae ni atarashii depaato ga dekiru toiu hanashi o kikimashita ka.', 'Apakah Anda dengar kabar akan dibangun mal baru di depan stasiun?', 'Have you heard that a new department store is going to open in front of the station?'),
                $this->dl('B', 'ええ、来年《らいねん》 オープンするといううわさですね。', 'Ee, rainen oopun suru toiu uwasa desu ne.', 'Ya, katanya rumornya akan buka tahun depan.', 'Yes, the rumor is that it will open next year.'),
                $this->dl('A', '本当《ほんとう》なら いいですね。', 'Hontou nara ii desu ne.', 'Semoga benar.', 'I hope it is true.'),
            ],
            4 => [
                $this->dl('A', '部長《ぶちょう》は 何《なん》と おっしゃって いましたか。', 'Buchou wa nan to osshatte imashita ka.', 'Apa kata kepala bagian tadi?', 'What did the department manager say?'),
                $this->dl('B', '明日《あした》までに 報告書《ほうこくしょ》を 出《だ》すように 言《い》われました。', 'Ashita made ni houkokusho o dasu you ni iwaremashita.', 'Saya diminta menyerahkan laporan paling lambat besok.', 'I was told to hand in the report by tomorrow.'),
                $this->dl('A', '他《ほか》には？', 'Hoka ni wa?', 'Ada lagi?', 'Anything else?'),
                $this->dl('B', '資料《しりょう》は 机《つくえ》の 上《うえ》に 置《お》いて おくように 頼《たの》まれました。', 'Shiryou wa tsukue no ue ni oite oku you ni tanomaremashita.', 'Saya juga diminta meletakkan dokumen di atas meja.', 'I was also asked to leave the documents on the desk.'),
            ],
            5 => [
                $this->dl('A', 'このジュース、飲《の》んで みて。', 'Kono juusu, nonde mite.', 'Coba minum jus ini.', 'Try this juice.'),
                $this->dl('B', 'わあ、本当《ほんとう》の 果物《くだもの》みたいな 味《あじ》だね。', 'Waa, hontou no kudamono mitai na aji da ne.', 'Wah, rasanya seperti buah sungguhan.', 'Wow, it tastes like real fruit.'),
                $this->dl('A', 'でしょう？水《みず》みたいに 軽《かる》くて、飲《の》みやすいよ。', 'Deshou? Mizu mitai ni karukute, nomiyasui yo.', 'Kan? Ringan seperti air, mudah diminum.', 'Right? It is light like water and easy to drink.'),
                $this->dl('B', 'もう 一杯《いっぱい》 もらって いい？', 'Mou ippai moratte ii?', 'Boleh minta segelas lagi?', 'Can I have another glass?'),
            ],
            6 => [
                $this->dl('A', 'お忙《いそが》しいところ、すみません。少《すこ》し よろしいでしょうか。', 'Oisogashii tokoro, sumimasen. Sukoshi yoroshii deshou ka.', 'Maaf mengganggu di tengah kesibukan Anda. Apakah ada waktu sebentar?', 'I am sorry to bother you when you are busy. Do you have a moment?'),
                $this->dl('B', 'はい、何《なん》でしょうか。', 'Hai, nan deshou ka.', 'Ya, ada apa?', 'Yes, what is it?'),
                $this->dl('A', '来週《らいしゅう》の 資料《しりょう》の ことで 相談《そうだん》が あるのですが。', 'Raishuu no shiryou no koto de soudan ga aru no desu ga.', 'Saya ingin berkonsultasi soal dokumen minggu depan.', 'I would like to consult you about next week\'s materials.'),
                $this->dl('B', 'わかりました。会議室《かいぎしつ》で 聞《き》きましょう。', 'Wakarimashita. Kaigishitsu de kikimashou.', 'Baik. Mari kita bicarakan di ruang rapat.', 'All right. Let us talk in the meeting room.'),
            ],
        ];
    }

    protected function cards(): array
    {
        return [
            $this->card(
                'Vたら、〜た (hasil dan penemuan)',
                'Vたら、〜た (result and discovery)',
                'Vたら、{V・A} た',
                'Pola 「X たら、Y た」 menyatakan dua hal. (1) Y terjadi sebagai hasil dari tindakan X. (2) Setelah melakukan X, pembicara menemukan atau menyadari Y (Y sudah ada begitu X dilakukan). Makna yang sama juga bisa dinyatakan dengan 「X と、Y た」.',
                'The pattern 「X たら、Y た」 has two uses. (1) Y happened as a result of the action X. (2) After doing X, the speaker discovers or realises Y (Y was already there once X was done). The same meanings can also be expressed with 「X と、Y た」.',
                [
                    'Y selalu bentuk lampau (た) dan merupakan hal yang terjadi di luar kendali pembicara.',
                    'Berbeda dengan たら pengandaian (お金《かね》が あったら、…) dan たら selesai (十時《じゅうじ》に なったら、…).',
                ],
                [
                    'Y is always in the past tense (た) and is something outside the speaker\'s control.',
                    'Different from the conditional たら (お金《かね》が あったら、…) and the "once it has happened" たら (十時《じゅうじ》に なったら、…).',
                ],
                [
                    $this->ex('窓《まど》を 開《あ》けたら、涼《すず》しい 風《かぜ》が 入《はい》って きた。', 'Mado o aketara, suzushii kaze ga haitte kita.', 'Ketika saya membuka jendela, angin sejuk masuk.', 'When I opened the window, a cool breeze came in.'),
                    $this->ex('駅《えき》に 着《つ》いたら、友達《ともだち》が 待《ま》って いた。', 'Eki ni tsuitara, tomodachi ga matte ita.', 'Begitu sampai di stasiun, ternyata teman saya sudah menunggu.', 'When I got to the station, my friend was waiting.'),
                    $this->ex('新《あたら》しい 薬《くすり》を 使《つか》ったら、かゆみが なくなった。', 'Atarashii kusuri o tsukattara, kayumi ga nakunatta.', 'Setelah memakai obat baru, rasa gatalnya hilang.', 'After I used the new medicine, the itching went away.'),
                    $this->ex('冷蔵庫《れいぞうこ》を 開《あ》けると、ケーキが 入《はい》って いた。', 'Reizouko o akeru to, keeki ga haitte ita.', 'Ketika membuka kulkas, ada kue di dalamnya.', 'When I opened the fridge, there was a cake inside.'),
                ]
            ),

            $this->card(
                'Nというのは〜のことだ／〜ということだ (menjelaskan istilah)',
                'Nというのは〜のことだ／〜ということだ (explaining a term)',
                'N というのは ｛N の／S 普通形《ふつうけい》という｝ ＋ ことだ',
                'Dipakai untuk menjelaskan arti sebuah istilah (X). 「X というのは N の ことだ」 menjelaskan bahwa X berarti N; 「X というのは 〜という ことだ」 menjelaskan X dengan kalimat penuh (bentuk biasa) yang diikuti という. Bentuk sopannya ことです.',
                'Used to explain the meaning of a term (X). 「X というのは N の ことだ」 says that X means N; 「X というのは 〜という ことだ」 explains X with a full sentence in plain form followed by という. The polite ending is ことです.',
                [
                    'Dalam percakapan, というのは bisa menjadi っていうのは atau ってのは.',
                    'Istilah dan singkatan sering dijelaskan dengan pola ini, misalnya 「PC」というのは パソコンの ことです.',
                ],
                [
                    'In conversation, というのは can become っていうのは or ってのは.',
                    'Terms and abbreviations are often explained with this pattern, e.g. 「PC」というのは パソコンの ことです.',
                ],
                [
                    $this->ex('「早退《そうたい》」というのは 決《き》められた 時間《じかん》より 早《はや》く 帰《かえ》る ことです。', 'Soutai toiu no wa kimerareta jikan yori hayaku kaeru koto desu.', '"Sōtai" berarti pulang lebih awal dari waktu yang ditentukan.', '"Soutai" means going home earlier than the set time.'),
                    $this->ex('「SNS」というのは インターネット上《じょう》の 交流《こうりゅう》サービスの ことです。', 'Esu enu esu toiu no wa intaanetto jou no kouryuu saabisu no koto desu.', '"SNS" artinya layanan pergaulan di internet.', '"SNS" means a social networking service on the internet.'),
                    $this->ex('「立《た》ち入《い》り禁止《きんし》」というのは 入《はい》っては いけないと いう ことです。', 'Tachiiri kinshi toiu no wa haitte wa ikenai to iu koto desu.', '"Tachiiri kinshi" berarti tidak boleh masuk.', '"Tachiiri kinshi" means that you must not enter.'),
                ]
            ),

            $this->card(
                '…という〜 (rincian isi ucapan atau pikiran)',
                '…という〜 (giving the content of a statement or thought)',
                'S 普通形《ふつうけい》 ＋ という ＋ N (話・うわさ・考え・意見・知らせ・ニュース…)',
                 'Dipakai untuk menjelaskan isi dari kata benda yang menyatakan ucapan atau pikiran, seperti 話, うわさ, 考え, 意見, 意志, 批判, 知らせ, dan ニュース. Kalimat penjelas (bentuk biasa) ditaruh sebelum という, lalu kata bendanya: "kabar bahwa ~", "pendapat bahwa ~".',
                'Used to give the content of a noun that signifies an utterance or thought, such as 話 (talk), うわさ (rumour), 考え (thought), 意見 (opinion), 意志 (intention), 批判 (criticism), 知らせ (notice) or ニュース (news). The explaining clause (plain form) goes before という, then the noun: "the rumour that ~", "the opinion that ~".',
                [
                    'Berbeda dengan Pelajaran 1 (N1という N2): di sini yang ada sebelum という adalah kalimat utuh, bukan nama.',
                    'Bentuk kalimat sebelum という tetap bentuk biasa, termasuk kata sifat-na → 〜だ.',
                ],
                [
                    'Unlike Lesson 1 (N1という N2): here a full sentence, not a name, comes before という.',
                    'The clause before という stays in plain form, including na-adjective → 〜だ.',
                ],
                [
                    $this->ex('来月《らいげつ》 駅《えき》の 前《まえ》に 新《あたら》しい 店《みせ》が できると いう うわさを 聞《き》いた。', 'Raigetsu eki no mae ni atarashii mise ga dekiru to iu uwasa o kiita.', 'Saya mendengar kabar burung bahwa bulan depan akan ada toko baru di depan stasiun.', 'I heard a rumour that a new shop will open in front of the station next month.'),
                    $this->ex('明日《あした》は 休《やす》みだと いう お知《し》らせが 届《とど》いた。', 'Ashita wa yasumi da to iu oshirase ga todoita.', 'Pemberitahuan bahwa besok libur telah datang.', 'A notice arrived saying that tomorrow is a day off.'),
                    $this->ex('彼《かれ》は もっと 勉強《べんきょう》したいと いう 希望《きぼう》を 持《も》って いる。', 'Kare wa motto benkyou shitai to iu kibou o motte iru.', 'Dia memiliki keinginan untuk belajar lebih banyak lagi.', 'He has a wish to study more.'),
                ]
            ),

            $this->card(
                '〜ように 言う／注意する／伝える／頼む (menyampaikan perintah dan permintaan)',
                '〜ように 言う／注意する／伝える／頼む (reporting instructions and requests)',
                'V 辞書形・V ない形 ＋ ように ＋ 言《い》う／注意《ちゅうい》する／伝《つた》える／頼《たの》む',
                'Dipakai untuk menyampaikan isi perintah atau permintaan secara tidak langsung (kalimat tidak langsung). Kata kerja bentuk kamus atau bentuk ない + ように, lalu kata kerja seperti 言う (berkata), 注意する (memperingatkan), 伝える (menyampaikan), atau 頼む (meminta). Kalau dikutip langsung, isinya berbentuk 〜なさい, 〜てはいけません, atau 〜てください dengan と.',
                'Used to report the content of an instruction or request indirectly. Verb dictionary form or ない form + ように, followed by a verb such as 言う (say), 注意する (warn), 伝える (tell), or 頼む (ask). When quoted directly, the content takes the form 〜なさい, 〜てはいけません, or 〜てください with と.',
                [
                    '〜なさい adalah ungkapan perintah yang hanya dipakai pada situasi tertentu, misalnya orang tua kepada anak atau pada petunjuk soal ujian.',
                    'Orang yang menerima perintah ditandai に: 学生《がくせい》に 〜ように 言《い》う.',
                ],
                [
                    '〜なさい is an instruction form used only in certain situations, such as parents speaking to children or exam instructions.',
                    'The person being told is marked with に: 学生《がくせい》に 〜ように 言《い》う.',
                ],
                [
                    $this->ex('医者《いしゃ》に 甘《あま》い 物《もの》を 食《た》べないように 言《い》われた。', 'Isha ni amai mono o tabenai you ni iwareta.', 'Saya disuruh dokter agar tidak makan makanan manis.', 'The doctor told me not to eat sweet things.'),
                    $this->ex('先生《せんせい》は 学生《がくせい》に 宿題《しゅくだい》を 出《だ》すように 伝《つた》えた。', 'Sensei wa gakusei ni shukudai o dasu you ni tsutaeta.', 'Guru menyampaikan kepada murid agar mengumpulkan PR.', 'The teacher told the students to hand in their homework.'),
                    $this->ex('母《はは》に 電話《でんわ》を かけるように 頼《たの》まれた。', 'Haha ni denwa o kakeru you ni tanomareta.', 'Saya diminta ibu untuk menelepon.', 'My mother asked me to call.'),
                    $this->ex('母《はは》は 子《こ》どもたちに「早《はや》く 寝《ね》なさい」と 言《い》った。', 'Haha wa kodomotachi ni "Hayaku nenasai" to itta.', 'Ibu berkata kepada anak-anak, "Tidurlah cepat."', 'Mother said to the children, "Go to bed early."'),
                ]
            ),

            $this->card(
                'Nみたいだ／みたいな／みたいに (perumpamaan lisan)',
                'Nみたいだ／みたいな／みたいに (casual simile)',
                'N ＋ みたいだ／みたいな N／みたいに V・いA・なA',
                'Artinya sama dengan 〜ようだ (perumpamaan atau contoh), tetapi みたいだ kurang formal sehingga dipakai dalam percakapan. Cara menyambungnya langsung dengan kata benda tanpa の: ジュースみたいだ, ジュースみたいな お酒《さけ》, ジュースみたいに 甘《あま》い.',
                'It means the same as 〜ようだ (simile or example), but みたいだ is less formal and is used in conversation. It attaches directly to the noun without の: ジュースみたいだ, ジュースみたいな お酒《さけ》, ジュースみたいに 甘《あま》い.',
                [
                    'Bandingkan: ジュースのようだ (tulisan／formal) ↔ ジュースみたいだ (percakapan).',
                    'みたいな N bisa dipakai untuk menyebut contoh: 「あなたみたいな 人《ひと》」 = orang seperti Anda.',
                ],
                [
                    'Compare: ジュースのようだ (written／formal) ↔ ジュースみたいだ (spoken).',
                    'みたいな N can give an example: 「あなたみたいな 人《ひと》」 = someone like you.',
                ],
                [
                    $this->ex('わあ、この ケーキ、夢《ゆめ》みたいだね。', 'Waa, kono keeki, yume mitai da ne.', 'Wah, kue ini seperti mimpi, ya.', 'Wow, this cake is like a dream.'),
                    $this->ex('私《わたし》は 彼《かれ》みたいな 優《やさ》しい 人《ひと》に なりたい。', 'Watashi wa kare mitai na yasashii hito ni naritai.', 'Saya ingin menjadi orang baik hati seperti dia.', 'I want to become a kind person like him.'),
                    $this->ex('この 部屋《へや》は 冬《ふゆ》みたいに 寒《さむ》いよ。', 'Kono heya wa fuyu mitai ni samui yo.', 'Ruangan ini dingin seperti musim dingin.', 'This room is as cold as winter.'),
                    $this->ex('兄《あに》は 料理《りょうり》が 上手《じょうず》で、プロみたいだ。', 'Ani wa ryouri ga jouzu de, puro mitai da.', 'Kakak saya pandai memasak, seperti seorang profesional.', 'My brother is good at cooking, like a pro.'),
                ]
            ),

            $this->card(
                '〜ところ (ungkapan sopan: お忙しいところ)',
                '〜ところ (polite expressions: お忙しいところ)',
                 'お忙《いそが》しい／お休《やす》み／お急《いそ》ぎ／お疲《つか》れ ＋ の ところ',
                'Artinya sama dengan 〜とき (saat), tetapi hanya dipakai dengan kata-kata tertentu dalam situasi sopan, misalnya お忙《いそが》しいところ (di saat Anda sibuk), お休《やす》みのところ (di saat Anda beristirahat), お急《いそ》ぎのところ (di saat Anda terburu-buru), dan お疲《つか》れのところ (di saat Anda lelah). Dipakai untuk berterima kasih atau meminta tolong sambil mengakui bahwa kita mengganggu.',
                'It means the same as 〜とき (when), but is only used with certain words in polite situations, such as お忙《いそが》しいところ (when you are busy), お休《やす》みのところ (when you are resting), お急《いそ》ぎのところ (when you are in a hurry), and お疲《つか》れのところ (when you are tired). It is used to thank or ask someone for something while acknowledging that we are imposing.',
                [
                    'Kata sifat-い langsung tersambung (お忙しい ところ); kata benda memakai の (お休みの ところ).',
                    'Sering diikuti すみません／申し訳ありません／ありがとうございます.',
                ],
                [
                    'い-adjectives connect directly (お忙しい ところ); nouns take の (お休みの ところ).',
                    'Often followed by すみません／申し訳ありません／ありがとうございます.',
                ],
                [
                    $this->ex('お忙《いそが》しい ところ、お越《こ》し いただき、ありがとうございます。', 'Oisogashii tokoro, okoshi itadaki, arigatou gozaimasu.', 'Terima kasih telah datang di tengah kesibukan Anda.', 'Thank you for coming despite how busy you are.'),
                    $this->ex('お疲《つか》れの ところ、すみません。少《すこ》し ご協力《きょうりょく》を お願《ねが》いします。', 'Otsukare no tokoro, sumimasen. Sukoshi gokyouryoku o onegai shimasu.', 'Maaf mengganggu di saat Anda lelah. Mohon kerja samanya sebentar.', 'I am sorry to trouble you when you are tired. Could I ask for your help for a moment?'),
                    $this->ex('お休《やす》みの ところ、失礼《しつれい》します。', 'Oyasumi no tokoro, shitsurei shimasu.', 'Maaf mengganggu waktu istirahat Anda.', 'Excuse me for disturbing you during your time off.'),
                ]
            ),
        ];
    }
}
