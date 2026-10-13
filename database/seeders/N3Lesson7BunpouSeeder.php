<?php

namespace Database\Seeders;

/**
 * Tata Bahasa N3 Pelajaran 7 (10 kartu, dengan dialog).
 *   php artisan db:seed --class=N3Lesson7BunpouSeeder
 */
class N3Lesson7BunpouSeeder extends N3GrammarSeeder
{
    protected function unitOrder(): int
    {
        return 7;
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
                $this->dl('A', '明日《あした》の 会議《かいぎ》、私《わたし》も 出《で》なくては いけませんか。', 'Ashita no kaigi, watashi mo denakute wa ikemasen ka.', 'Rapat besok, apakah saya juga harus hadir?', 'Do I have to attend tomorrow\'s meeting too?'),
                $this->dl('B', '部長《ぶちょう》は 全員《ぜんいん》 出席《しゅっせき》しなくては ならないと 言《い》って いましたよ。', 'Buchou wa zenin shusseki shinakute wa naranai to itte imashita yo.', 'Kepala bagian bilang semua orang harus hadir.', 'The manager said everyone has to attend.'),
                $this->dl('A', '資料《しりょう》は 準備《じゅんび》しなくても かまいませんか。', 'Shiryou wa junbi shinakute mo kamaimasen ka.', 'Apakah saya tidak perlu menyiapkan dokumen?', 'Is it all right if I do not prepare materials?'),
                $this->dl('B', 'ええ、資料《しりょう》は 私《わたし》が 用意《ようい》します。', 'Ee, shiryou wa watashi ga youi shimasu.', 'Ya, dokumennya saya yang siapkan.', 'Yes, I will prepare the materials.'),
            ],
            2 => [
                $this->dl('A', 'そろそろ 行《い》かなくちゃ。', 'Sorosoro ikanakucha.', 'Sudah waktunya aku pergi.', 'I have to get going soon.'),
                $this->dl('B', 'えっ、もう？', 'E, mou?', 'Eh, sudah?', 'What, already?'),
                $this->dl('A', 'うん、五時《ごじ》までに 銀行《ぎんこう》に 行《い》かなきゃ いけないの。', 'Un, goji made ni ginkou ni ikanakya ikenai no.', 'Iya, aku harus ke bank sebelum jam lima.', 'Yes, I have to get to the bank by five.'),
                $this->dl('B', 'じゃ、また 今度《こんど》ね。', 'Ja, mata kondo ne.', 'Kalau begitu, lain kali ya.', 'Then see you next time.'),
            ],
            3 => [
                $this->dl('A', 'この 書類《しょるい》、どう 書《か》けば いいですか。', 'Kono shorui, dou kakeba ii desu ka.', 'Dokumen ini harus ditulis bagaimana?', 'How should I fill in this form?'),
                $this->dl('B', '名前《なまえ》と 住所《じゅうしょ》を 書《か》くだけで いいですよ。', 'Namae to juusho o kaku dake de ii desu yo.', 'Cukup tulis nama dan alamat saja.', 'You only need to write your name and address.'),
                $this->dl('A', 'ほかの 欄《らん》は？', 'Hoka no ran wa?', 'Kolom yang lain?', 'What about the other boxes?'),
                $this->dl('B', '空《あ》けて おくだけで 大丈夫《だいじょうぶ》です。', 'Akete oku dake de daijoubu desu.', 'Cukup dibiarkan kosong saja.', 'Just leaving them blank is fine.'),
            ],
            4 => [
                $this->dl('A', '母《はは》の 誕生日《たんじょうび》に 何《なに》を あげようかな。', 'Haha no tanjoubi ni nani o ageyou kana.', 'Kasih apa ya untuk ulang tahun ibu?', 'What should I give my mother for her birthday?'),
                $this->dl('B', '花《はな》は どうかな。', 'Hana wa dou kana.', 'Bunga bagaimana?', 'How about flowers?'),
                $this->dl('A', 'いいね。あ、花屋《はなや》まで 一緒《いっしょ》に 来《き》て くれないかな。', 'Ii ne. A, hanaya made issho ni kite kurenai kana.', 'Bagus. Oh, bisakah kamu menemaniku ke toko bunga?', 'Good idea. Oh, could you come to the florist with me?'),
                $this->dl('B', 'いいよ。', 'Ii yo.', 'Boleh.', 'Sure.'),
            ],
            5 => [
                $this->dl('A', '田中《たなか》さんの 絵《え》、とても 上手《じょうず》ですね。', 'Tanaka-san no e, totemo jouzu desu ne.', 'Lukisan Anda bagus sekali.', 'Your paintings are very good, Ms. Tanaka.'),
                $this->dl('B', 'いえ、私《わたし》の 絵《え》なんか、まだまだです。', 'Ie, watashi no e nanka, madamada desu.', 'Ah, lukisan saya masih jauh dari bagus.', 'Oh, my paintings are nothing yet.'),
                $this->dl('A', '展覧会《てんらんかい》に 出《だ》して みませんか。', 'Tenrankai ni dashite mimasen ka.', 'Mau mencoba mengirim ke pameran?', 'Why not submit them to an exhibition?'),
                $this->dl('B', '私《わたし》なんかが 出《だ》しても いいんでしょうか。', 'Watashi nanka ga dashite mo ii n deshou ka.', 'Apakah orang seperti saya boleh mengirim?', 'Is it all right for someone like me to submit?'),
            ],
            6 => [
                $this->dl('A', '聞《き》きました？ 佐藤《さとう》さんが 来月《らいげつ》 結婚《けっこん》するそうですよ。', 'Kikimashita? Satou-san ga raigetsu kekkon suru sou desu yo.', 'Sudah dengar? Katanya Pak Sato menikah bulan depan.', 'Have you heard? Apparently Mr. Sato is getting married next month.'),
                $this->dl('B', 'えっ、結婚《けっこん》するなんて 全然《ぜんぜん》 知《し》りませんでした。', 'E, kekkon suru nante zenzen shirimasen deshita.', 'Eh, saya sama sekali tidak tahu dia akan menikah.', 'What? I had no idea he was getting married.'),
                $this->dl('A', '私《わたし》も 驚《おどろ》きました。', 'Watashi mo odorokimashita.', 'Saya juga terkejut.', 'I was surprised too.'),
                $this->dl('B', '相手《あいて》が 同《おな》じ 会社《かいしゃ》の 人《ひと》だなんて…。', 'Aite ga onaji kaisha no hito da nante...', 'Apalagi pasangannya orang satu perusahaan...', 'And that his partner is from the same company...'),
            ],
            7 => [
                $this->dl('A', '昨日《きのう》の 試験《しけん》、どうだった？', 'Kinou no shiken, dou datta?', 'Ujian kemarin bagaimana?', 'How was yesterday\'s exam?'),
                $this->dl('B', '満点《まんてん》を 取《と》って、先生《せんせい》を びっくりさせたよ。', 'Manten o totte, sensei o bikkuri saseta yo.', 'Aku dapat nilai penuh dan membuat guru kaget.', 'I got full marks and surprised the teacher.'),
                $this->dl('A', 'すごい！ お母《かあ》さんも 喜《よろこ》んだでしょう。', 'Sugoi! Okaasan mo yorokonda deshou.', 'Hebat! Ibumu pasti senang.', 'Wow! Your mother must have been pleased too.'),
                $this->dl('B', 'うん、母《はは》を 喜《よろこ》ばせる ことが できて うれしい。', 'Un, haha o yorokobaseru koto ga dekite ureshii.', 'Iya, aku senang bisa membuat ibuku bahagia.', 'Yes, I am glad I could make her happy.'),
            ],
            8 => [
                $this->dl('A', '昨日《きのう》の 映画《えいが》は どうでしたか。', 'Kinou no eiga wa dou deshita ka.', 'Bagaimana film kemarin?', 'How was yesterday\'s film?'),
                $this->dl('B', '最後《さいご》の 場面《ばめん》に とても 感動《かんどう》させられました。', 'Saigo no bamen ni totemo kandou saseraremashita.', 'Saya sangat terharu oleh adegan terakhirnya.', 'I was deeply moved by the last scene.'),
                $this->dl('A', '私《わたし》は 泣《な》かされましたよ。', 'Watashi wa nakasaremashita yo.', 'Saya sampai menangis.', 'It made me cry.'),
                $this->dl('B', '本当《ほんとう》に いい 映画《えいが》でしたね。', 'Hontou ni ii eiga deshita ne.', 'Memang film yang bagus.', 'It really was a good film.'),
            ],
            9 => [
                $this->dl('A', '来月《らいげつ》、北海道《ほっかいどう》へ 旅行《りょこう》に 行《い》く 予定《よてい》なんです。', 'Raigetsu, Hokkaidou e ryokou ni iku yotei nan desu.', 'Bulan depan saya berencana bepergian ke Hokkaido.', 'I am planning a trip to Hokkaido next month.'),
                $this->dl('B', '北海道《ほっかいどう》なら、海《うみ》の 幸《さち》が おいしいですよ。', 'Hokkaidou nara, umi no sachi ga oishii desu yo.', 'Kalau Hokkaido, hasil lautnya enak.', 'If it is Hokkaido, the seafood is delicious.'),
                $this->dl('A', 'どこで 食《た》べるのが いいでしょうか。', 'Doko de taberu no ga ii deshou ka.', 'Sebaiknya makan di mana?', 'Where would be a good place to eat?'),
                $this->dl('B', '食《た》べるなら、市場《いちば》の 食堂《しょくどう》が おすすめです。', 'Taberu nara, ichiba no shokudou ga osusume desu.', 'Kalau makan, saya sarankan kedai di pasar.', 'If you are eating, I recommend the canteens in the market.'),
            ],
            10 => [
                $this->dl('A', '部長《ぶちょう》は 何《なん》て 言《い》って いた？', 'Buchou wa nante itte ita?', 'Apa kata kepala bagian?', 'What did the manager say?'),
                $this->dl('B', '明日《あした》の 資料《しりょう》を 今日《きょう》中《じゅう》に コピーして くれと 言《い》われた。', 'Ashita no shiryou o kyou juu ni kopii shite kure to iwareta.', 'Aku disuruh menyalin dokumen untuk besok hari ini juga.', 'I was told to copy tomorrow\'s materials today.'),
                $this->dl('A', '大変《たいへん》だね。', 'Taihen da ne.', 'Berat juga ya.', 'That is tough.'),
                $this->dl('B', 'うん、でも 急《いそ》いで やらなくちゃ。', 'Un, demo isoide yaranakucha.', 'Iya, tapi aku harus cepat mengerjakannya.', 'Yes, but I have to get on with it quickly.'),
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    protected function cards(): array
    {
        return [
            $this->card(
                'Vなくては ならない／いけない・Vなくても かまわない',
                'Vなくては ならない／いけない・Vなくても かまわない (must / need not)',
                'V ない形(〜ない → 〜なくて)／いA(〜く)／なA・N(で) ＋ は ならない／いけない／も かまわない',
                '〜なくては ならない／いけない menyatakan keharusan, sama dengan 〜なければ ならない. 〜なくても かまわない menyatakan tidak perlu melakukannya; lebih sopan daripada 〜なくても いいです.',
                '〜なくては ならない／いけない expresses obligation and means the same as 〜なければ ならない. 〜なくても かまわない says that something is not necessary and is politer than 〜なくても いいです.',
                ['Kata sifat-na dan kata benda memakai で／では: 日本語《にほんご》で なくては ならない.', 'かまわない lebih formal: 来《こ》なくても かまいません.'],
                ['Na-adjectives and nouns take で／では: 日本語《にほんご》で なくては ならない.', 'かまわない is more formal: 来《こ》なくても かまいません.'],
                [
                    $this->ex('明日《あした》までに 報告書《ほうこくしょ》を 出《だ》さなくては ならない。', 'Ashita made ni houkokusho o dasanakute wa naranai.', 'Laporan harus diserahkan paling lambat besok.', 'The report has to be handed in by tomorrow.'),
                    $this->ex('参加者《さんかしゃ》は 二十歳《はたち》以上《いじょう》で なくては いけません。', 'Sankasha wa hatachi ijou de nakute wa ikemasen.', 'Peserta harus berusia dua puluh tahun ke atas.', 'Participants must be twenty or older.'),
                    $this->ex('時間《じかん》が ない 場合《ばあい》は、全部《ぜんぶ》 読《よ》まなくても かまいません。', 'Jikan ga nai baai wa, zenbu yomanakute mo kamaimasen.', 'Kalau tidak ada waktu, tidak perlu membaca semuanya.', 'If you are short of time, you do not have to read it all.'),
                ]
            ),

            $this->card(
                'Vなくちゃ／Vなきゃ (bentuk lisan)',
                'Vなくちゃ／Vなきゃ (spoken forms)',
                'Vなくては いけない → Vなくちゃ (いけない)／Vなければ いけない → Vなきゃ (いけない)',
                'Dalam percakapan akrab, 〜なくては いけない menjadi 〜なくちゃ(いけない) dan 〜なければ いけない menjadi 〜なきゃ(いけない). Bagian いけない sering dihilangkan sama sekali.',
                'In informal conversation 〜なくては いけない becomes 〜なくちゃ(いけない), and 〜なければ いけない becomes 〜なきゃ(いけない). The いけない part is often dropped altogether.',
                ['Contoh singkat: もう 行《い》かなくちゃ。', 'Hanya untuk percakapan dengan orang akrab.'],
                ['Short example: もう 行《い》かなくちゃ。', 'Only for conversation with people you are close to.'],
                [
                    $this->ex('もう こんな 時間《じかん》！ 帰《かえ》らなくちゃ。', 'Mou konna jikan! Kaeranakucha.', 'Sudah selarut ini! Aku harus pulang.', 'Look at the time! I have to go home.'),
                    $this->ex('明日《あした》は 早《はや》いから、もう 寝《ね》なきゃ。', 'Ashita wa hayai kara, mou nenakya.', 'Besok pagi-pagi, jadi aku harus tidur sekarang.', 'I have an early start tomorrow, so I must sleep now.'),
                    $this->ex('宿題《しゅくだい》、今日《きょう》中《じゅう》に 出《だ》さなきゃ いけないんだ。', 'Shukudai, kyou juu ni dasanakya ikenai n da.', 'PR itu harus dikumpulkan hari ini.', 'I have to hand in my homework today.'),
                ]
            ),

            $this->card(
                'Nだけだ／Vるだけだ／Vるだけでいい',
                'Nだけだ／Vるだけだ／Vるだけでいい (only ~)',
                'N ＋ だけ／V・いA 普通形《ふつうけい》・なA(〜な) ＋ だけだ／だけでいい',
                'だけ setelah kata benda menyatakan batas: "hanya ~". Setelah kata kerja atau kata sifat, 〜だけだ membuat predikat "hanya ~ saja". 〜するだけでいい berarti cukup melakukan itu dan tidak perlu yang lain.',
                'だけ after a noun marks a limit: "only ~". After a verb or adjective, 〜だけだ forms a predicate meaning "just ~". 〜するだけでいい means that doing that is enough and nothing else is needed.',
                ['Kata sifat-na: 〜なだけ.', 'Nada "cukup" pada 〜だけでいい: tidak ada hal lain yang harus dilakukan.'],
                ['Na-adjectives: 〜なだけ.', '〜だけでいい has the sense of "enough": nothing else needs to be done.'],
                [
                    $this->ex('財布《さいふ》には 千円《せんえん》だけ 入《はい》って います。', 'Saifu ni wa sen\'en dake haitte imasu.', 'Di dompet hanya ada seribu yen.', 'There is only one thousand yen in my wallet.'),
                    $this->ex('どうしたの？ …いや、少《すこ》し 疲《つか》れて いるだけだよ。', 'Doushita no? ...Iya, sukoshi tsukarete iru dake da yo.', 'Ada apa? ...Tidak, aku hanya sedikit lelah.', 'What is wrong? ...Nothing, I am just a little tired.'),
                    $this->ex('このボタンを 押《お》すだけで、お湯《ゆ》が 出《で》ます。', 'Kono botan o osu dake de, oyu ga demasu.', 'Cukup menekan tombol ini, air panas keluar.', 'All you have to do is press this button and hot water comes out.'),
                ]
            ),

            $this->card(
                '…かな (partikel akhir kalimat)',
                '…かな (sentence-final particle)',
                'V・いA 普通形《ふつうけい》／なA・N(〜だ を とる) ＋ かな',
                'Partikel akhir kalimat yang akrab: dipakai untuk bertanya pada diri sendiri atau mengajukan pertanyaan yang tidak menuntut jawaban. Dengan 〜ないかな, ajakan atau permintaan menjadi lebih halus dan tidak langsung.',
                'A casual sentence-final particle used to wonder aloud, or to pose a question that does not demand an answer. With 〜ないかな an invitation or request becomes softer and less direct.',
                ['Bentuk yang lebih sopan: 〜でしょうか.', '〜て くれないかな = "tidak bisakah kamu ~" (permintaan halus).'],
                ['A politer equivalent is 〜でしょうか.', '〜て くれないかな = "I wonder if you could ~" (a gentle request).'],
                [
                    $this->ex('明日《あした》は 晴《は》れる かな。', 'Ashita wa hareru kana.', 'Besok cerah tidak ya?', 'I wonder if it will be fine tomorrow.'),
                    $this->ex('今度《こんど》の 日曜日《にちようび》、一緒《いっしょ》に 出《で》かけられないかな。', 'Kondo no nichiyoubi, issho ni dekakerarenai kana.', 'Hari Minggu nanti, bisakah kita pergi bersama?', 'I wonder if we could go out together this Sunday.'),
                    $this->ex('この 荷物《にもつ》、ちょっと 持《も》って くれないかな。', 'Kono nimotsu, chotto motte kurenai kana.', 'Bisakah kamu memegangi barang ini sebentar?', 'Could you hold this luggage for a moment?'),
                ]
            ),

            $this->card(
                'Nなんか (merendahkan atau merendah)',
                'Nなんか (belittling)',
                'N ＋ なんか',
                'Menunjukkan bahwa pembicara menganggap sesuatu tidak penting atau tidak berharga, termasuk saat merendahkan diri sendiri. Seperti など tetapi dipakai dalam percakapan. Tidak dipakai setelah kata kerja atau kata sifat (untuk itu pakai なんて).',
                'Shows that the speaker regards something as unimportant or of little value, including when being modest about oneself. It is like など but conversational. It is not used after verbs or adjectives (use なんて for that).',
                ['Untuk merendah: 私《わたし》なんか…', 'Setelah kata kerja／kata sifat gunakan なんて (kartu berikutnya).'],
                ['Used for modesty: 私《わたし》なんか…', 'After verbs or adjectives use なんて (next card).'],
                [
                    $this->ex('私《わたし》の 料理《りょうり》なんか、人《ひと》に 出《だ》せません。', 'Watashi no ryouri nanka, hito ni dasemasen.', 'Masakan saya tidak pantas disajikan untuk orang lain.', 'My cooking is not fit to serve to anyone.'),
                    $this->ex('私《わたし》なんかに その 仕事《しごと》は 無理《むり》です。', 'Watashi nanka ni sono shigoto wa muri desu.', 'Pekerjaan itu mustahil bagi orang seperti saya.', 'That job is impossible for someone like me.'),
                    $this->ex('テレビなんか 見《み》ないで、勉強《べんきょう》しなさい。', 'Terebi nanka minaide, benkyou shinasai.', 'Jangan menonton TV, belajarlah.', 'Stop watching silly TV and study.'),
                ]
            ),

            $this->card(
                '…なんて (kejutan, kritik, merendahkan)',
                '…なんて (surprise, criticism, disdain)',
                'V・いA・なA 普通形《ふつうけい》／N ＋ なんて',
                'Seperti なんか, なんて menunjukkan sikap merendahkan, tetapi bisa mengikuti kata kerja dan kata sifat. Juga dipakai untuk menyatakan kritik atau keterkejutan terhadap suatu hal. Sering ditutup 〜なんて 思《おも》わなかった／知《し》りませんでした.',
                'Like なんか, なんて shows disdain, but it can follow verbs and adjectives. It also expresses criticism of, or surprise at, something. It is often closed with 〜なんて 思《おも》わなかった／知《し》りませんでした.',
                ['Dipakai untuk kejutan, baik yang menyenangkan maupun tidak.', 'なんか tidak bisa menggantikan なんて setelah kata kerja.'],
                ['Used for surprise, whether pleasant or unpleasant.', 'なんか cannot replace なんて after a verb.'],
                [
                    $this->ex('彼《かれ》が 会社《かいしゃ》を 辞《や》めるなんて、信《しん》じられません。', 'Kare ga kaisha o yameru nante, shinjiraremasen.', 'Dia berhenti dari perusahaan? Saya tidak percaya.', 'I cannot believe he is quitting the company.'),
                    $this->ex('こんなに 簡単《かんたん》に 見《み》つかるなんて、思《おも》いませんでした。', 'Konna ni kantan ni mitsukaru nante, omoimasen deshita.', 'Tidak terpikir bisa ditemukan semudah ini.', 'I never thought it would be found so easily.'),
                    $this->ex('山田《やまだ》さんが 走《はし》るのが 速《はや》いなんて、知《し》りませんでした。', 'Yamada-san ga hashiru no ga hayai nante, shirimasen deshita.', 'Saya tidak tahu kalau Pak Yamada cepat larinya.', 'I did not know that Mr. Yamada was a fast runner.'),
                ]
            ),

            $this->card(
                'Vi(さ)せる (kausatif perasaan)',
                'Vi(さ)せる (emotional causative)',
                '感情《かんじょう》を 表《あらわ》す 自動詞《じどうし》 ＋ (さ)せる',
                'Selain "menyuruh", bentuk kausatif dipakai untuk menimbulkan perasaan pada orang lain. Kata kerja yang dipakai adalah kata kerja intransitif perasaan (泣《な》く, 笑《わら》う, 驚《おどろ》く, 喜《よろこ》ぶ, がっかりする, 心配《しんぱい》する), dan orang yang dibuat merasa ditandai dengan を.',
                'Besides "making someone do", the causative describes evoking an emotion in someone. The verbs are intransitive emotion verbs (泣《な》く, 笑《わら》う, 驚《おどろ》く, 喜《よろこ》ぶ, がっかりする, 心配《しんぱい》する), and the person who feels the emotion is marked with を.',
                ['Contoh: 泣《な》く → 泣《な》かせる; びっくりする → びっくりさせる.', 'Pelaku menimbulkan perasaan; bukan memaksa.'],
                ['Examples: 泣《な》く → 泣《な》かせる; びっくりする → びっくりさせる.', 'The agent evokes the feeling; it is not coercion.'],
                [
                    $this->ex('冗談《じょうだん》を 言《い》って、みんなを 笑《わら》わせた。', 'Joudan o itte, minna o warawaseta.', 'Saya melontarkan lelucon dan membuat semua orang tertawa.', 'I told a joke and made everyone laugh.'),
                    $this->ex('連絡《れんらく》しないで、親《おや》を 心配《しんぱい》させて しまった。', 'Renraku shinaide, oya o shinpai sasete shimatta.', 'Karena tidak memberi kabar, saya membuat orang tua khawatir.', 'I did not get in touch and ended up worrying my parents.'),
                    $this->ex('合格《ごうかく》の 知《し》らせは、家族《かぞく》を 喜《よろこ》ばせた。', 'Goukaku no shirase wa, kazoku o yorokobaseta.', 'Kabar kelulusan membuat keluarga bahagia.', 'The news that I had passed made my family happy.'),
                ]
            ),

            $this->card(
                'Vi(さ)せられる (pasif dari kausatif perasaan)',
                'Vi(さ)せられる (passive of emotional causative)',
                '感情《かんじょう》を 表《あらわ》す 自動詞《じどうし》 ＋ (さ)せられる',
                'Kausatif perasaan juga dapat dipakai dalam bentuk pasif: "perasaan ~ ditimbulkan padaku". Menunjukkan bahwa perasaan seperti kagum, sedih, kecewa, atau terharu muncul dengan kuat karena sesuatu.',
                'The emotional causative can also be used in the passive: "I was made to feel ~". It shows that an emotion such as admiration, sadness, disappointment, or being moved was strongly evoked by something.',
                ['Pola: 感動《かんどう》させられる, がっかりさせられる, 感心《かんしん》させられる.', 'Subjek adalah orang yang merasakan perasaan itu.'],
                ['Patterns: 感動《かんどう》させられる, がっかりさせられる, 感心《かんしん》させられる.', 'The subject is the person who feels the emotion.'],
                [
                    $this->ex('彼女《かのじょ》の 歌《うた》を 聞《き》いて、感動《かんどう》させられた。', 'Kanojo no uta o kiite, kandou saserareta.', 'Mendengar nyanyiannya, saya sangat terharu.', 'Listening to her sing, I was deeply moved.'),
                    $this->ex('約束《やくそく》を 破《やぶ》られて、がっかりさせられた。', 'Yakusoku o yaburarete, gakkari saserareta.', 'Janji dilanggar, saya sungguh kecewa.', 'My promise was broken and I was really disappointed.'),
                    $this->ex('子《こ》どもの 優《やさ》しさに、感心《かんしん》させられました。', 'Kodomo no yasashisa ni, kanshin saseraremashita.', 'Saya dibuat kagum oleh kebaikan hati anak itu.', 'I was impressed by the child\'s kindness.'),
                ]
            ),

            $this->card(
                '…なら、… (kalau ~, sebaiknya ...)',
                '…なら、… (if ~, then ...)',
                'V・いA 普通形《ふつうけい》／なA・N(〜だ を とる) ＋ なら',
                '「X なら Y」 dipakai untuk menyarankan atau menanyakan Y ketika lawan bicara berniat melakukan X atau sedang dalam keadaan X. X bisa kata benda, kata kerja, atau kata sifat. Setelah なA dan kata benda, だ dibuang.',
                '「X なら Y」 is used to recommend or ask about Y when the listener is thinking of doing X or is in the state of X. X can be a noun, verb, or adjective. After a なA or a noun, だ is dropped.',
                ['Berbeda dengan たら: なら menanggapi topik yang dikemukakan lawan bicara.', 'Akhir kalimat sering berupa saran: 〜と いいですよ, 〜が おすすめです.'],
                ['Unlike たら, なら responds to a topic the listener has raised.', 'The main clause is often advice: 〜と いいですよ, 〜が おすすめです.'],
                [
                    $this->ex('京都《きょうと》へ 行《い》くなら、秋《あき》が おすすめです。', 'Kyouto e iku nara, aki ga osusume desu.', 'Kalau mau ke Kyoto, musim gugur yang saya sarankan.', 'If you are going to Kyoto, I recommend autumn.'),
                    $this->ex('安《やす》い 店《みせ》を 探《さが》して いるなら、駅《えき》の 近《ちか》くの スーパーが いいですよ。', 'Yasui mise o sagashite iru nara, eki no chikaku no suupaa ga ii desu yo.', 'Kalau mencari toko yang murah, supermarket dekat stasiun bagus.', 'If you are looking for a cheap shop, the supermarket near the station is good.'),
                    $this->ex('日本語《にほんご》を 勉強《べんきょう》するなら、毎日《まいにち》 少《すこ》しずつ 続《つづ》ける ことが 大切《たいせつ》です。', 'Nihongo o benkyou suru nara, mainichi sukoshi zutsu tsuzukeru koto ga taisetsu desu.', 'Kalau belajar bahasa Jepang, yang penting terus sedikit demi sedikit setiap hari.', 'If you are studying Japanese, the important thing is to keep at it a little every day.'),
                ]
            ),

            $this->card(
                'Vて くれ (perintah／permintaan lugas)',
                'Vて くれ (blunt request or order)',
                'V て形 ＋ くれ',
                'Bentuk perintah atau permintaan yang lugas, terdengar agak kasar. Terutama dipakai pria kepada bawahan atau orang akrab. Dalam kalimat tidak langsung berubah menjadi 〜て くれと 言《い》う／頼《たの》む.',
                'A blunt form of order or request that sounds a little rough. It is used mainly by men toward juniors or close friends. In reported speech it becomes 〜て くれと 言《い》う／頼《たの》む.',
                ['Tidak dipakai kepada atasan; gunakan 〜て ください.', 'Dalam kutipan: 〜て くれと 言《い》われた (disuruh ~).'],
                ['Not used toward superiors; use 〜て ください instead.', 'In reported speech: 〜て くれと 言《い》われた (was told to ~).'],
                [
                    $this->ex('悪《わる》いが、ちょっと 窓《まど》を 閉《し》めて くれ。', 'Warui ga, chotto mado o shimete kure.', 'Maaf, tolong tutup jendelanya sebentar.', 'Sorry, but shut the window for me, will you.'),
                    $this->ex('父《ちち》は 兄《あに》に 「新聞《しんぶん》を 取《と》って きて くれ」と 言《い》った。', 'Chichi wa ani ni "shinbun o totte kite kure" to itta.', 'Ayah berkata kepada kakak, "Ambilkan koran."', 'My father told my older brother, "Go and get the paper."'),
                    $this->ex('田中《たなか》さんは 友達《ともだち》に 駅《えき》まで 迎《むか》えに 来《き》て くれと 頼《たの》んだ。', 'Tanaka-san wa tomodachi ni eki made mukae ni kite kure to tanonda.', 'Tanaka meminta temannya menjemput sampai stasiun.', 'Tanaka asked his friend to come and pick him up at the station.'),
                ]
            ),
        ];
    }
}
