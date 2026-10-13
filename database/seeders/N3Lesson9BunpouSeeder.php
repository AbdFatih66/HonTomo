<?php

namespace Database\Seeders;

/**
 * Tata Bahasa N3 Pelajaran 9 (6 kartu, dengan dialog).
 *   php artisan db:seed --class=N3Lesson9BunpouSeeder
 */
class N3Lesson9BunpouSeeder extends N3GrammarSeeder
{
    protected function unitOrder(): int
    {
        return 9;
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
                $this->dl('A', 'いらっしゃいませ。何《なに》を お探《さが》しですか。', 'Irasshaimase. Nani o osagashi desu ka.', 'Selamat datang. Anda sedang mencari apa?', 'Welcome. What are you looking for?'),
                $this->dl('B', '母《はは》への 誕生日《たんじょうび》の プレゼントです。', 'Haha e no tanjoubi no purezento desu.', 'Hadiah ulang tahun untuk ibu saya.', 'A birthday present for my mother.'),
                $this->dl('A', 'お母《かあ》さまは 青《あお》い かばんを お持《も》ちですか。', 'Okaasama wa aoi kaban o omochi desu ka.', 'Apakah ibu Anda memiliki tas biru?', 'Does your mother have a blue bag?'),
                $this->dl('B', 'いいえ、持《も》って いません。', 'Iie, motte imasen.', 'Tidak, belum punya.', 'No, she does not.'),
            ],
            2 => [
                $this->dl('A', 'すみません、ここに 座《すわ》っても かまいませんか。', 'Sumimasen, koko ni suwatte mo kamaimasen ka.', 'Maaf, bolehkah saya duduk di sini?', 'Excuse me, may I sit here?'),
                $this->dl('B', 'ええ、どうぞ。', 'Ee, douzo.', 'Ya, silakan.', 'Yes, please do.'),
                $this->dl('A', '荷物《にもつ》を 隣《となり》に 置《お》いても かまいませんか。', 'Nimotsu o tonari ni oite mo kamaimasen ka.', 'Bolehkah saya menaruh barang di sebelah?', 'May I put my luggage next to me?'),
                $this->dl('B', 'ええ、大丈夫《だいじょうぶ》ですよ。', 'Ee, daijoubu desu yo.', 'Ya, tidak masalah.', 'Yes, that is fine.'),
            ],
            3 => [
                $this->dl('A', '今日《きょう》は 昨日《きのう》より 暑《あつ》いですね。', 'Kyou wa kinou yori atsui desu ne.', 'Hari ini lebih panas dari kemarin ya.', 'It is hotter today than yesterday.'),
                $this->dl('B', 'そうですか。昨日《きのう》ほど 暑《あつ》くは ないと 思《おも》いますよ。', 'Sou desu ka. Kinou hodo atsuku wa nai to omoimasu yo.', 'Begitukah? Menurut saya tidak sepanas kemarin.', 'Is it? I think it is not as hot as yesterday.'),
                $this->dl('A', 'そうですか？', 'Sou desu ka?', 'Masa?', 'Really?'),
                $this->dl('B', 'ええ、昨日《きのう》は 本当《ほんとう》に 暑《あつ》かったですから。', 'Ee, kinou wa hontou ni atsukatta desu kara.', 'Ya, karena kemarin benar-benar panas.', 'Yes, because yesterday was really hot.'),
            ],
            4 => [
                $this->dl('A', '日本《にほん》で 一番《いちばん》 好《す》きな 場所《ばしょ》は どこですか。', 'Nihon de ichiban suki na basho wa doko desu ka.', 'Tempat favorit Anda di Jepang di mana?', 'Where is your favourite place in Japan?'),
                $this->dl('B', '富士山《ふじさん》ほど 美《うつく》しい 山《やま》は ないと 思《おも》います。', 'Fujisan hodo utsukushii yama wa nai to omoimasu.', 'Menurut saya tidak ada gunung seindah Gunung Fuji.', 'I think there is no mountain as beautiful as Mt. Fuji.'),
                $this->dl('A', '登《のぼ》った ことが ありますか。', 'Nobotta koto ga arimasu ka.', 'Pernahkah Anda mendakinya?', 'Have you ever climbed it?'),
                $this->dl('B', 'いいえ。でも、いつか 登《のぼ》って みたいです。', 'Iie. Demo, itsuka nobotte mitai desu.', 'Belum. Tapi suatu saat saya ingin mencoba.', 'No. But I would like to try someday.'),
            ],
            5 => [
                $this->dl('A', '昨日《きのう》の 工場《こうじょう》は どうでしたか。', 'Kinou no koujou wa dou deshita ka.', 'Bagaimana kondisi pabrik kemarin?', 'How was the factory yesterday?'),
                $this->dl('B', '機械《きかい》が 故障《こしょう》した ために、午後《ごご》は 作業《さぎょう》が 止《と》まりました。', 'Kikai ga koshou shita tame ni, gogo wa sagyou ga tomarimashita.', 'Karena mesin rusak, pekerjaan berhenti pada sore hari.', 'Because a machine broke down, work stopped in the afternoon.'),
                $this->dl('A', '原因《げんいん》は 何《なん》でしたか。', 'Gen\'in wa nan deshita ka.', 'Apa penyebabnya?', 'What was the cause?'),
                $this->dl('B', '部品《ぶひん》が 古《ふる》く なって いた ためです。', 'Buhin ga furuku natte ita tame desu.', 'Karena suku cadangnya sudah tua.', 'It was because the parts had worn out.'),
            ],
            6 => [
                $this->dl('A', '昨日《きのう》の パーティー、来《こ》なかったね。', 'Kinou no paatii, konakatta ne.', 'Kamu tidak datang ke pesta kemarin.', 'You did not come to yesterday\'s party.'),
                $this->dl('B', '仕事《しごと》が 終《お》わらなかったんだ。早《はや》く 終《お》わって いれば、行《い》けたのに。', 'Shigoto ga owaranakatta n da. Hayaku owatte ireba, iketa no ni.', 'Pekerjaanku belum selesai. Kalau selesai lebih awal, aku pasti bisa datang.', 'My work was not finished. If I had finished early, I could have gone.'),
                $this->dl('A', 'みんな 楽《たの》しんで いたよ。', 'Minna tanoshinde ita yo.', 'Semua orang bersenang-senang.', 'Everyone was having a great time.'),
                $this->dl('B', '行《い》けば よかったなあ。', 'Ikeba yokatta naa.', 'Seandainya aku datang...', 'I wish I had gone.'),
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    protected function cards(): array
    {
        return [
            $this->card(
                'お〜です (bentuk hormat dari 〜ている)',
                'お〜です (honorific form of 〜ている)',
                'お ＋ V ます語幹《ごかん》 ＋ です／特別《とくべつ》な 形《かたち》(おいでです・お越《こ》しです…)',
                'Bentuk hormat dari 〜している: menyebut dengan hormat tindakan yang sedang berlangsung atau keadaan hasilnya. Dengan kata kerja datang-pergi, dapat berarti bentuk hormat lampau atau akan datang. Kata kerja khusus: 行《い》く・いる・来《く》る → おいでです; 来《く》る → お越《こ》しです／お見《み》えです; 食《た》べる → お召《め》し上《あ》がりです; 着《き》る → お召《め》しです; 寝《ね》る → お休《やす》みです; 住《す》んでいる → お住《す》まいです; 知《し》っている → ご存《ぞん》じです.',
                'The honorific form of 〜している: it respectfully describes an ongoing action or the state resulting from it. With verbs of coming and going it can also serve as a respectful past or future. Special forms: 行《い》く・いる・来《く》る → おいでです; 来《く》る → お越《こ》しです／お見《み》えです; 食《た》べる → お召《め》し上《あ》がりです; 着《き》る → お召《め》しです; 寝《ね》る → お休《やす》みです; 住《す》んでいる → お住《す》まいです; 知《し》っている → ご存《ぞん》じです.',
                ['Hanya untuk orang yang dihormati, tidak untuk diri sendiri.', 'Kata kerja dengan する memakai ご〜です: ご利用《りよう》ですか.'],
                ['Only for the person being honoured, never for oneself.', 'する-verbs use ご〜です: ご利用《りよう》ですか.'],
                [
                    $this->ex('何《なに》を お探《さが》しですか。', 'Nani o osagashi desu ka.', 'Anda sedang mencari apa?', 'What are you looking for?'),
                    $this->ex('社長《しゃちょう》は 今《いま》 お出《で》かけですか。', 'Shachou wa ima odekake desu ka.', 'Apakah direktur sedang pergi sekarang?', 'Is the president out at the moment?'),
                    $this->ex('この 件《けん》を ご存《ぞん》じですか。', 'Kono ken o gozonji desu ka.', 'Apakah Anda mengetahui hal ini?', 'Are you aware of this matter?'),
                ]
            ),

            $this->card(
                'Vても かまわない (izin, lebih formal)',
                'Vても かまわない (permission, formal)',
                'V て形／いA(〜くて)／なA・N(で) ＋ も かまわない',
                'Menyatakan izin, atau dalam kalimat tanya meminta izin. Artinya sama dengan 〜ても いい tetapi lebih formal. Bisa juga menyatakan bahwa suatu pilihan lain pun tidak masalah.',
                'Expresses permission, or asks for it in a question. It means the same as 〜ても いい but is more formal. It can also say that an alternative is acceptable too.',
                ['Pertanyaan: 〜ても かまいませんか = "bolehkah ~?"', 'Jawaban: ええ、かまいません／どうぞ.'],
                ['Question: 〜ても かまいませんか = "may I ~?"', 'Answer: ええ、かまいません／どうぞ.'],
                [
                    $this->ex('ここで 写真《しゃしん》を 撮《と》っても かまいませんか。', 'Koko de shashin o totte mo kamaimasen ka.', 'Bolehkah saya mengambil foto di sini?', 'May I take photographs here?'),
                    $this->ex('電話《でんわ》は 明日《あした》でも かまいません。', 'Denwa wa ashita demo kamaimasen.', 'Teleponnya besok pun tidak apa-apa.', 'Calling tomorrow is fine too.'),
                    $this->ex('少《すこ》し 高《たか》くても かまいませんから、品質《ひんしつ》の いい ものを 選《えら》びたい。', 'Sukoshi takakute mo kamaimasen kara, hinshitsu no ii mono o erabitai.', 'Sedikit mahal pun tidak apa-apa, saya ingin memilih yang berkualitas.', 'I do not mind if it is a bit expensive; I want to choose something of good quality.'),
                ]
            ),

            $this->card(
                'AはBほど〜ない／〜ほどではない (perbandingan)',
                'AはBほど〜ない／〜ほどではない (not as ~ as)',
                'N／V 普通形《ふつうけい》 ＋ ほど ＋ いA(〜く)ない／なA(〜では)ない／〜ほどではない',
                '「AはBほどXではない」: A dan B sama-sama X, tetapi A tidak seberapa dibandingkan B. B bisa berupa kata kerja bentuk biasa seperti 思《おも》ったほど. X dapat dihilangkan: 〜ほどではない.',
                '「A は B ほど X ではない」: both A and B are X, but A is less so than B. B can be a plain-form verb such as 思《おも》ったほど. X can be omitted: 〜ほどではない.',
                ['いA: 〜ほど 〜くない; なA: 〜ほど 〜では ない.', 'Bentuk 思《おも》ったほど〜ない = "tidak se~ yang dikira".'],
                ['い-adjectives: 〜ほど 〜くない; na-adjectives: 〜ほど 〜では ない.', '思《おも》ったほど〜ない = "not as ~ as I thought".'],
                [
                    $this->ex('今年《ことし》の 冬《ふゆ》は 去年《きょねん》ほど 寒《さむ》くない。', 'Kotoshi no fuyu wa kyonen hodo samukunai.', 'Musim dingin tahun ini tidak sedingin tahun lalu.', 'This winter is not as cold as last year.'),
                    $this->ex('私《わたし》の 日本語《にほんご》は 田中《たなか》さんほど 上手《じょうず》では ありません。', 'Watashi no nihongo wa Tanaka-san hodo jouzu de wa arimasen.', 'Bahasa Jepang saya tidak sebagus Pak Tanaka.', 'My Japanese is not as good as Mr. Tanaka\'s.'),
                    $this->ex('試験《しけん》は 思《おも》ったほど 難《むずか》しく なかった。', 'Shiken wa omotta hodo muzukashiku nakatta.', 'Ujiannya tidak sesulit yang saya kira.', 'The exam was not as difficult as I had thought.'),
                ]
            ),

            $this->card(
                'Nほど〜はない／いない (yang paling ~)',
                'Nほど〜はない／いない (nothing is as ~ as N)',
                'N ＋ ほど ＋ いA／なA(〜な) ＋ N ＋ は ない／いない',
                '「XほどYはない／いない」: X adalah yang paling Y; tidak ada hal atau orang lain yang sebanding. Dipakai untuk memuji atau menegaskan bahwa X adalah yang terbaik (atau terburuk) dalam hal Y.',
                '「X ほど Y は ない／いない」: X is the pinnacle of Y; nothing or nobody else is its equal. Used to praise or stress that X is the best (or worst) in terms of Y.',
                ['Benda: ない／ありません; orang dan makhluk hidup: いない／いません.', 'Mengandung makna superlatif tanpa kata 一番《いちばん》.'],
                ['Things: ない／ありません; people and living things: いない／いません.', 'It carries a superlative meaning without the word 一番《いちばん》.'],
                [
                    $this->ex('母《はは》の 作《つく》る カレーほど おいしい 料理《りょうり》は ない。', 'Haha no tsukuru karee hodo oishii ryouri wa nai.', 'Tidak ada masakan seenak kari buatan ibu.', 'No dish is as delicious as the curry my mother makes.'),
                    $this->ex('健康《けんこう》ほど 大切《たいせつ》な ものは ありません。', 'Kenkou hodo taisetsu na mono wa arimasen.', 'Tidak ada yang sepenting kesehatan.', 'Nothing is as important as health.'),
                    $this->ex('山田《やまだ》さんほど 親切《しんせつ》な 人《ひと》は いません。', 'Yamada-san hodo shinsetsu na hito wa imasen.', 'Tidak ada orang sebaik hati Pak Yamada.', 'Nobody is as kind as Mr. Yamada.'),
                ]
            ),

            $this->card(
                '…ため(に)／…ためだ (sebab dan alasan, formal)',
                '…ため(に)／…ためだ (cause, formal)',
                'S 普通形《ふつうけい》／いA／なA(〜な)／N(〜の) ＋ ため(に)／ためだ',
                '「XためにY」: X adalah sebab atau alasan, Y adalah akibatnya. Dipakai dalam tulisan dan lebih formal daripada から dan ので. Bila akibat disebut lebih dulu dan sebab menjadi predikat, bentuknya 「Y(の)はXためだ」.',
                '「X ために Y」: X is the cause or reason and Y is what happened. It is used in writing and is more formal than から and ので. When the result is stated first and the cause becomes the predicate, the form is 「Y(の)は X ためだ」.',
                ['Jangan tertukar dengan ために tujuan (〜ために = demi ~).', 'Untuk akibat yang tidak diinginkan sering dipakai dalam berita atau laporan.'],
                ['Do not confuse it with the purpose ために (〜ために = in order to ~).', 'Often used in news and reports for undesirable results.'],
                [
                    $this->ex('台風《たいふう》が 来《き》た ために、新幹線《しんかんせん》が 止《と》まりました。', 'Taifuu ga kita tame ni, shinkansen ga tomarimashita.', 'Karena topan datang, Shinkansen berhenti beroperasi.', 'Because a typhoon came, the Shinkansen stopped running.'),
                    $this->ex('電車《でんしゃ》が 遅《おく》れた のは、事故《じこ》が あった ためです。', 'Densha ga okureta no wa, jiko ga atta tame desu.', 'Kereta terlambat karena ada kecelakaan.', 'The reason the train was late is that there was an accident.'),
                    $this->ex('工場《こうじょう》の 機械《きかい》が 故障《こしょう》した ため、今日《きょう》の 生産《せいさん》は 中止《ちゅうし》に なった。', 'Koujou no kikai ga koshou shita tame, kyou no seisan wa chuushi ni natta.', 'Karena mesin pabrik rusak, produksi hari ini dihentikan.', 'Because a factory machine broke down, today\'s production was cancelled.'),
                ]
            ),

            $this->card(
                'Vたら／Vば、…た(のに) (pengandaian yang bertentangan dengan fakta)',
                'Vたら／Vば、…た(のに) (counterfactual)',
                'V たら／V ば ＋ …だろう／…のに／…た',
                'Dipakai untuk membayangkan apa yang akan terjadi seandainya suatu peristiwa yang sebenarnya tidak terjadi, ternyata terjadi. Kalimat biasanya ditutup ungkapan dugaan seperti 〜だろう atau 〜のに ("padahal / sayang sekali").',
                'Used to imagine what would have happened if an event that did not actually occur had occurred. The sentence usually ends with a conjectural expression such as 〜だろう or 〜のに ("and yet / what a pity").',
                ['Sering berbau penyesalan: 〜ば よかった.', 'Bandingkan: たら／ば biasa untuk kemungkinan di masa depan, bukan yang sudah lewat.'],
                ['It often carries regret: 〜ば よかった.', 'Compare: ordinary たら／ば is for future possibilities, not for things already past.'],
                [
                    $this->ex('もっと 早《はや》く 家《いえ》を 出《で》ていれば、電車《でんしゃ》に 間《ま》に 合《あ》ったのに。', 'Motto hayaku ie o deteireba, densha ni maniatta no ni.', 'Kalau saya berangkat lebih awal, saya pasti sempat naik kereta.', 'If I had left home earlier, I would have caught the train.'),
                    $this->ex('傘《かさ》を 持《も》って 来《き》たら、濡《ぬ》れなかっただろう。', 'Kasa o motte kitara, nurenakatta darou.', 'Kalau membawa payung, mungkin saya tidak basah.', 'If I had brought an umbrella, I probably would not have got wet.'),
                    $this->ex('日本語《にほんご》が もっと 上手《じょうず》だったら、通訳《つうやく》の 仕事《しごと》が できたのに。', 'Nihongo ga motto jouzu dattara, tsuuyaku no shigoto ga dekita no ni.', 'Kalau bahasa Jepang saya lebih baik, saya bisa mengerjakan pekerjaan penerjemah.', 'If my Japanese had been better, I could have done the interpreting job.'),
                ]
            ),
        ];
    }
}
