<?php

namespace Database\Seeders;

/**
 * Tata Bahasa N3 Pelajaran 11 (10 kartu, dengan dialog).
 *   php artisan db:seed --class=N3Lesson11BunpouSeeder
 */
class N3Lesson11BunpouSeeder extends N3GrammarSeeder
{
    protected function unitOrder(): int
    {
        return 11;
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
                $this->dl('A', '最近《さいきん》、朝《あさ》は 寒《さむ》く なって きたね。', 'Saikin, asa wa samuku natte kita ne.', 'Akhir-akhir ini pagi hari jadi dingin ya.', 'The mornings have been getting cold lately.'),
                $this->dl('B', 'もう 十月《じゅうがつ》だからね。これから もっと 寒《さむ》く なって いくよ。', 'Mou juugatsu da kara ne. Korekara motto samuku natte iku yo.', 'Sudah bulan Oktober. Mulai sekarang akan makin dingin.', 'It is already October. It will keep getting colder from now on.'),
                $this->dl('A', 'コートを 出《だ》さなきゃ。', 'Kooto o dasanakya.', 'Aku harus mengeluarkan mantel.', 'I have to get out my coat.'),
                $this->dl('B', '風邪《かぜ》を ひかないように、早《はや》めに 準備《じゅんび》した ほうが いいよ。', 'Kaze o hikanai you ni, hayame ni junbi shita hou ga ii yo.', 'Supaya tidak masuk angin, lebih baik bersiap lebih awal.', 'It is better to get ready early so you do not catch a cold.'),
            ],
            2 => [
                $this->dl('A', '最近《さいきん》、よく 眠《ねむ》れないんだ。', 'Saikin, yoku nemurenai n da.', 'Akhir-akhir ini aku susah tidur nyenyak.', 'Lately I cannot sleep well.'),
                $this->dl('B', '寝《ね》る 前《まえ》に スマホを 見《み》ないように したら どう？', 'Neru mae ni sumaho o minai you ni shitara dou?', 'Bagaimana kalau sebelum tidur tidak melihat ponsel?', 'Why not stop looking at your phone before bed?'),
                $this->dl('A', 'そうだね。やって みる。', 'Sou da ne. Yatte miru.', 'Benar juga. Akan kucoba.', 'True. I will try.'),
                $this->dl('B', 'あと、軽《かる》い 運動《うんどう》を して みたら？', 'Ato, karui undou o shite mitara?', 'Lalu, coba juga olahraga ringan?', 'And how about doing some light exercise too?'),
            ],
            3 => [
                $this->dl('A', '会議《かいぎ》は 対面《たいめん》と オンラインと、どちらが いいですか。', 'Kaigi wa taimen to onrain to, dochira ga ii desu ka.', 'Rapat sebaiknya bertatap muka atau daring, mana yang lebih baik?', 'For the meeting, which is better, face to face or online?'),
                $this->dl('B', '移動《いどう》する 時間《じかん》を 考《かんが》えると、行《い》くより オンラインの ほうが 楽《らく》ですね。', 'Idou suru jikan o kangaeru to, iku yori onrain no hou ga raku desu ne.', 'Kalau memikirkan waktu perjalanan, daring lebih praktis daripada datang.', 'Considering travel time, online is easier than going there.'),
                $this->dl('A', 'でも、大事《だいじ》な 話《はなし》は 画面《がめん》で 話《はな》すより 会《あ》って 話《はな》した ほうが いいと 思《おも》います。', 'Demo, daiji na hanashi wa gamen de hanasu yori atte hanashita hou ga ii to omoimasu.', 'Tetapi untuk hal penting, menurut saya lebih baik bertemu langsung daripada lewat layar.', 'But for important matters, I think meeting in person is better than talking on a screen.'),
                $this->dl('B', 'たしかに、そうですね。', 'Tashika ni, sou desu ne.', 'Benar juga.', 'That is true.'),
            ],
            4 => [
                $this->dl('A', '新《あたら》しい 先生《せんせい》は どんな 人《ひと》？', 'Atarashii sensei wa donna hito?', 'Guru yang baru orangnya seperti apa?', 'What is the new teacher like?'),
                $this->dl('B', '先生《せんせい》らしい 落《お》ち着《つ》いた 人《ひと》だよ。', 'Sensei rashii ochitsuita hito da yo.', 'Orangnya tenang, benar-benar seperti guru.', 'A calm person, very much like a teacher.'),
                $this->dl('A', '授業《じゅぎょう》は おもしろい？', 'Jugyou wa omoshiroi?', 'Pelajarannya menarik?', 'Are the lessons interesting?'),
                $this->dl('B', 'うん。子《こ》どもらしい 質問《しつもん》にも 丁寧《ていねい》に 答《こた》えて くれるよ。', 'Un. Kodomo rashii shitsumon ni mo teinei ni kotaete kureru yo.', 'Ya. Pertanyaan yang polos seperti anak-anak pun dijawab dengan sabar.', 'Yes. They even answer childlike questions patiently.'),
            ],
            5 => [
                $this->dl('A', '田中《たなか》さん、会社《かいしゃ》を 辞《や》める らしいよ。', 'Tanaka-san, kaisha o yameru rashii yo.', 'Katanya Pak Tanaka mau berhenti dari perusahaan.', 'I hear Mr. Tanaka is quitting the company.'),
                $this->dl('B', 'えっ、本当《ほんとう》？ だれから 聞《き》いたの？', 'E, hontou? Dare kara kiita no?', 'Hah, sungguh? Kamu dengar dari siapa?', 'What, really? Who did you hear that from?'),
                $this->dl('A', '鈴木《すずき》さんが 言《い》って いたの。故郷《ふるさと》に 帰《かえ》る らしい。', 'Suzuki-san ga itte ita no. Furusato ni kaeru rashii.', 'Pak Suzuki yang bilang. Katanya ia akan pulang kampung.', 'Mr. Suzuki said so. He is apparently going back to his hometown.'),
                $this->dl('B', '寂《さび》しく なるね。', 'Sabishiku naru ne.', 'Jadi sepi ya.', 'It will feel lonely.'),
            ],
            6 => [
                $this->dl('A', '山田《やまだ》さんは どうして 日本《にほん》へ 来《き》たんですか。', 'Yamada-san wa doushite nihon e kita n desu ka.', 'Mengapa Anda datang ke Jepang?', 'Why did you come to Japan, Ms. Yamada?'),
                $this->dl('B', 'エンジニアとして 工場《こうじょう》で 働《はたら》く ためです。', 'Enjinia toshite koujou de hataraku tame desu.', 'Untuk bekerja di pabrik sebagai insinyur.', 'To work at a factory as an engineer.'),
                $this->dl('A', '仕事《しごと》は どうですか。', 'Shigoto wa dou desu ka.', 'Bagaimana pekerjaannya?', 'How is the work?'),
                $this->dl('B', '外国人《がいこくじん》の 社員《しゃいん》として、学《まな》ぶ ことが たくさん あります。', 'Gaikokujin no shain toshite, manabu koto ga takusan arimasu.', 'Sebagai karyawan asing, banyak hal yang saya pelajari.', 'As a foreign employee, there is a lot for me to learn.'),
            ],
            7 => [
                $this->dl('A', '昨日《きのう》は 傘《かさ》を 持《も》たずに 出《で》かけて、濡《ぬ》れて しまいました。', 'Kinou wa kasa o motazu ni dekakete, nurete shimaimashita.', 'Kemarin saya pergi tanpa membawa payung dan jadi basah.', 'Yesterday I went out without an umbrella and got wet.'),
                $this->dl('B', '天気予報《てんきよほう》を 見《み》なかったんですか。', 'Tenki yohou o minakatta n desu ka.', 'Tidak melihat prakiraan cuaca?', 'Did you not check the weather forecast?'),
                $this->dl('A', 'ええ、確《たし》かめずに 家《いえ》を 出《で》て しまって…。', 'Ee, tashikamezu ni ie o dete shimatte...', 'Ya, saya keluar rumah tanpa memeriksanya...', 'No, I left the house without checking...'),
                $this->dl('B', '今日《きょう》は 気《き》を つけて くださいね。', 'Kyou wa ki o tsukete kudasai ne.', 'Hari ini hati-hati ya.', 'Please be careful today.'),
            ],
            8 => [
                $this->dl('A', '昨日《きのう》の 会議《かいぎ》に どうして 来《こ》なかったの？', 'Kinou no kaigi ni doushite konakatta no?', 'Mengapa kamu tidak datang ke rapat kemarin?', 'Why did you not come to yesterday\'s meeting?'),
                $this->dl('B', '電車《でんしゃ》が 動《うご》かず、会社《かいしゃ》に 着《つ》けなかったんです。', 'Densha ga ugokazu, kaisha ni tsukenakatta n desu.', 'Keretanya tidak jalan, jadi saya tidak bisa sampai di kantor.', 'The trains were not running, so I could not get to the office.'),
                $this->dl('A', '連絡《れんらく》は？', 'Renraku wa?', 'Kabar kepada kami?', 'And did you contact us?'),
                $this->dl('B', '電話《でんわ》も 通《つう》じず、連絡《れんらく》できませんでした。', 'Denwa mo tsuujizu, renraku dekimasen deshita.', 'Teleponnya juga tidak tersambung, jadi tidak bisa menghubungi.', 'The phone would not connect either, so I could not reach you.'),
            ],
            9 => [
                $this->dl('A', 'この 町《まち》は 地震《じしん》が 多《おお》いですか。', 'Kono machi wa jishin ga ooi desu ka.', 'Apakah kota ini sering gempa?', 'Does this town have many earthquakes?'),
                $this->dl('B', '過去《かこ》に 大《おお》きな 地震《じしん》が 二回《にかい》 起《お》こって いますよ。', 'Kako ni ookina jishin ga nikai okotte imasu yo.', 'Di masa lalu pernah terjadi gempa besar dua kali.', 'There have been two big earthquakes in the past.'),
                $this->dl('A', '最近《さいきん》は？', 'Saikin wa?', 'Kalau akhir-akhir ini?', 'And recently?'),
                $this->dl('B', '十年間《じゅうねんかん》 大《おお》きな 地震《じしん》は 起《お》こって いません。でも、備《そな》えは 大切《たいせつ》です。', 'Juunenkan ookina jishin wa okotte imasen. Demo, sonae wa taisetsu desu.', 'Sepuluh tahun terakhir tidak ada gempa besar. Tetapi kesiapsiagaan itu penting.', 'There has been no big earthquake for ten years. Even so, being prepared is important.'),
            ],
            10 => [
                $this->dl('A', '今度《こんど》の 飲《の》み会《かい》、どこに する？', 'Kondo no nomikai, doko ni suru?', 'Acara minum berikutnya di mana?', 'Where shall we have the next get-together?'),
                $this->dl('B', '駅前《えきまえ》の 居酒屋《いざかや》なんか どう？', 'Ekimae no izakaya nanka dou?', 'Bagaimana kalau kedai di depan stasiun?', 'How about that izakaya in front of the station?'),
                $this->dl('A', 'いいね。何人《なんにん》くらい 入《はい》れるかな。', 'Ii ne. Nannin kurai haireru kana.', 'Boleh. Kira-kira muat berapa orang ya?', 'Sounds good. I wonder how many people it holds.'),
                $this->dl('B', '十人《じゅうにん》は 入《はい》れる はずだよ。', 'Juunin wa haireru hazu da yo.', 'Seharusnya muat sepuluh orang.', 'It should hold ten people.'),
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    protected function cards(): array
    {
        return [
            $this->card(
                '〜てくる・〜ていく (perubahan)',
                '〜てくる・〜ていく (change)',
                'V て形／いA(〜く)・なA／N(〜に) なって ＋ くる／いく',
                '〜てくる menyatakan bahwa suatu keadaan sampai pada kondisi sekarang melalui proses perubahan (dari lampau hingga kini). 〜ていく menyatakan perubahan yang bergerak ke arah masa depan (dari sekarang ke depan).',
                '〜てくる says that a situation has arrived at its present state through a process of change (from the past up to now). 〜ていく says that change moves in the direction of the future (from now onward).',
                ['Sering bersama なる: 寒《さむ》く なって くる, 増《ふ》えて いく.', 'Bandingkan dengan 〜てくる／〜ていく untuk arah gerakan (兄《あに》が 帰《かえ》って きた).'],
                ['Often combined with なる: 寒《さむ》く なって くる, 増《ふ》えて いく.', 'Compare 〜てくる／〜ていく for direction of motion (兄《あに》が 帰《かえ》って きた).'],
                [
                    $this->ex('夜《よる》に なって、風《かぜ》が 強《つよ》く なって きた。', 'Yoru ni natte, kaze ga tsuyoku natte kita.', 'Malam tiba dan angin makin kencang.', 'Night fell and the wind gradually grew stronger.'),
                    $this->ex('最近《さいきん》、日本語《にほんご》の 勉強《べんきょう》が 楽《たの》しく なって きました。', 'Saikin, nihongo no benkyou ga tanoshiku natte kimashita.', 'Akhir-akhir ini belajar bahasa Jepang jadi makin menyenangkan.', 'Lately, studying Japanese has become more and more enjoyable.'),
                    $this->ex('これから 日本《にほん》では お年寄《としよ》りが ますます 増《ふ》えて いくだろう。', 'Korekara nihon de wa otoshiyori ga masumasu fuete iku darou.', 'Ke depannya di Jepang jumlah lansia akan terus bertambah.', 'In Japan, the number of elderly people will probably keep growing from now on.'),
                ]
            ),

            $this->card(
                '〜たら どう？／いかがですか (saran)',
                '〜たら どう？／いかがですか (suggestion)',
                'V たら ＋ どう？／どうですか／いかがですか',
                'Dipakai untuk memberi saran dengan hanya menunjukkan satu pilihan yang bisa diambil lawan bicara. 〜たら いかがですか adalah bentuk sopan dari 〜たら どう？. 〜たら どう？ dan 〜たら？ dipakai kepada orang yang lebih muda atau akrab, seperti keluarga dan teman.',
                'Used to make a suggestion by simply indicating one choice the listener could make. 〜たら いかがですか is the polite form of 〜たら どう？. 〜たら どう？ and 〜たら？ are used to someone junior or on familiar terms, such as family and friends.',
                ['Kepada atasan lebih aman memakai bentuk yang lebih halus (misalnya 〜て は いかがでしょうか).', 'Bukan perintah: lawan bicara bebas menolak.'],
                ['To a superior, a softer form (e.g. 〜て は いかがでしょうか) is safer.', 'It is not an order: the listener is free to decline.'],
                [
                    $this->ex('疲《つか》れて いるなら、今日《きょう》は 早《はや》く 帰《かえ》ったら どう？', 'Tsukarete iru nara, kyou wa hayaku kaettara dou?', 'Kalau lelah, bagaimana kalau hari ini pulang lebih awal?', 'If you are tired, why not go home early today?'),
                    $this->ex('分《わ》からない 言葉《ことば》は 辞書《じしょ》で 調《しら》べたら どうですか。', 'Wakaranai kotoba wa jisho de shirabetara dou desu ka.', 'Kata yang tidak dimengerti, bagaimana kalau dicari di kamus?', 'Why not look up words you do not know in a dictionary?'),
                    $this->ex('こちらの 椅子《いす》に お掛《か》けに なったら いかがですか。', 'Kochira no isu ni o-kake ni nattara ikaga desu ka.', 'Bagaimana kalau Anda duduk di kursi ini?', 'Would you like to take a seat on this chair?'),
                ]
            ),

            $this->card(
                '…より…ほうが… (perbandingan)',
                '…より…ほうが… (comparison)',
                'V 辞書形《じしょけい》／いA／なA(〜な)／N(〜の) ＋ より ＋ V 辞書形《じしょけい》／いA／なA(〜な)／N(〜の) ＋ ほうが …',
                '「Y より X ほうが …」 dipakai terutama untuk menjawab pertanyaan 「X と Y とでは どちらが …ですか」 (mana yang lebih ~, X atau Y?). Pada jawaban, X ほうが … adalah bagian utama. Pola ini juga bisa dipakai tanpa pertanyaan, dengan nuansa "orang mungkin mengira Y lebih ~ daripada X, tetapi kenyataannya sebaliknya".',
                '「Y より X ほうが …」 is mainly used to answer the question 「X と Y とでは どちらが …ですか」 (which is more ~, X or Y?). In an answer, X ほうが … is the main part. It can also be used without a question, with the nuance "you might expect Y to be more ~ than X, but actually the reverse is true".',
                ['Jawaban atas 「どちらが」 memakai 〜の ほうが; bentuk 「Yは Xより …」 kurang tepat untuk menjawabnya.', '〜ほうが いい = lebih baik (saran): 少《すく》ないより 多《おお》い ほうが いい.'],
                ['Answers to 「どちらが」 use 〜の ほうが; the form 「Yは Xより …」 is not a natural answer.', '〜ほうが いい = better (advice): 少《すく》ないより 多《おお》い ほうが いい.'],
                [
                    $this->ex('電車《でんしゃ》で 行《い》くより、バスで 行《い》く ほうが 安《やす》い。', 'Densha de iku yori, basu de iku hou ga yasui.', 'Pergi naik bus lebih murah daripada naik kereta.', 'Going by bus is cheaper than going by train.'),
                    $this->ex('説明書《せつめいしょ》を 読《よ》むより、実際《じっさい》に 使《つか》って みる ほうが 早《はや》く 覚《おぼ》えられる。', 'Setsumeisho o yomu yori, jissai ni tsukatte miru hou ga hayaku oboerareru.', 'Lebih cepat hafal dengan langsung mencoba daripada membaca buku petunjuk.', 'You learn faster by actually trying it than by reading the manual.'),
                    $this->ex('無理《むり》に 頑張《がんば》るより、少《すこ》し 休《やす》んだ ほうが いいですよ。', 'Muri ni ganbaru yori, sukoshi yasunda hou ga ii desu yo.', 'Daripada memaksakan diri, lebih baik beristirahat sebentar.', 'Rather than forcing yourself, it is better to rest a little.'),
                ]
            ),

            $this->card(
                'N らしい (khas, sesuai sifatnya)',
                'N らしい (typical of, befitting)',
                'N ＋ らしい',
                '「N₁ らしい N₂」 menyatakan bahwa N₂ memiliki sifat atau ciri khas yang pantas bagi N₁. Dapat dipakai sebagai penerang kata benda (秋《あき》らしい 天気《てんき》), sebagai predikat (学生《がくせい》らしい), atau bentuk adverbia (らしく). Bentuk negatifnya らしくない berarti "tidak seperti biasanya" atau "tidak pantas".',
                '「N₁ らしい N₂」 indicates that N₂ has the typical quality or property that suits N₁. It can modify a noun (秋《あき》らしい 天気《てんき》), act as a predicate (学生《がくせい》らしい), or become an adverb (らしく). The negative らしくない means "not like you / not befitting".',
                ['Berbeda dengan らしい untuk kabar atau dugaan (kartu berikutnya): di sini N langsung disambung らしい.', 'らしくない sering dipakai untuk menegur sifat yang tidak biasa pada seseorang.'],
                ['Not the same as らしい for reports or conjecture (next card): here N attaches directly to らしい.', 'らしくない is often used to remark on behaviour that is out of character.'],
                [
                    $this->ex('今日《きょう》は 秋《あき》らしい 天気《てんき》ですね。', 'Kyou wa aki rashii tenki desu ne.', 'Cuaca hari ini terasa seperti musim gugur ya.', 'It is typical autumn weather today, is it not?'),
                    $this->ex('子《こ》どもらしい 元気《げんき》な 声《こえ》が 公園《こうえん》から 聞《き》こえて くる。', 'Kodomo rashii genki na koe ga kouen kara kikoete kuru.', 'Dari taman terdengar suara riang khas anak-anak.', 'Lively, childlike voices can be heard from the park.'),
                    $this->ex('弱音《よわね》を 吐《は》くなんて、あなたらしくない。', 'Yowane o haku nante, anata rashikunai.', 'Mengeluh seperti itu bukan dirimu.', 'Complaining like that is not like you.'),
                ]
            ),

            $this->card(
                '…らしい (kabar yang didengar／dugaan)',
                '…らしい (reported speech／conjecture)',
                'V・いA 普通形《ふつうけい》／なA・N 普通形《ふつうけい》(〜だ なし) ＋ らしい',
                '(1) 〜らしい menyatakan bahwa isinya adalah informasi yang dibaca atau didengar dari orang lain: "katanya ~". (2) Dapat juga menyatakan dugaan bahwa sesuatu mungkin benar, berdasarkan informasi yang dibaca atau didengar atau tanda-tanda di sekitar.',
                '(1) 〜らしい says the content is information read or heard from someone else: "I hear that ~". (2) It can also express a conjecture that something is probably true, based on information read or heard or on signs around.',
                ['なA dan N: tanpa だ → 静《しず》からしい, 雨《あめ》らしい.', 'Sering bersama ～に よると／～の 話《はなし》では untuk menyebut sumber.'],
                ['な-adjectives and nouns: no だ → 静《しず》からしい, 雨《あめ》らしい.', 'Often used with ～に よると／～の 話《はなし》では to name the source.'],
                [
                    $this->ex('天気予報《てんきよほう》に よると、明日《あした》は 雨《あめ》らしい。', 'Tenki yohou ni yoru to, ashita wa ame rashii.', 'Menurut prakiraan cuaca, besok katanya hujan.', 'According to the forecast, it seems it will rain tomorrow.'),
                    $this->ex('隣《となり》の 席《せき》の 人《ひと》の 話《はなし》では、この 近《ちか》くに 新《あたら》しい パン屋《や》が できたらしい。', 'Tonari no seki no hito no hanashi de wa, kono chikaku ni atarashii panya ga dekita rashii.', 'Menurut orang di sebelah, katanya ada toko roti baru di dekat sini.', 'From what the person next to me said, a new bakery has apparently opened nearby.'),
                    $this->ex('窓《まど》が 割《わ》れて いる。誰《だれ》かが ボールを ぶつけたらしい。', 'Mado ga warete iru. Dareka ga booru o butsuketa rashii.', 'Jendelanya pecah. Sepertinya ada yang melemparkan bola.', 'The window is broken. Someone seems to have hit it with a ball.'),
                ]
            ),

            $this->card(
                'N として (sebagai)',
                'N として (as, in the capacity of)',
                'N ＋ として',
                'Menunjukkan kualifikasi, kedudukan, atau sudut pandang: "sebagai N". Bentuk turunannya: としては (jika dilihat sebagai), としても (juga sebagai), dan としての N (N sebagai ~).',
                'Indicates a qualification, position or perspective: "as N". Derived forms: としては (as for being), としても (also as), and としての N (N in the capacity of ~).',
                ['Dipakai untuk peran, jabatan, atau status.', '〜として dengan kata benda abstrak: 例《れい》として (sebagai contoh), 目的《もくてき》として (sebagai tujuan).'],
                ['Used for roles, positions and statuses.', '〜として with abstract nouns: 例《れい》として (as an example), 目的《もくてき》として (as a purpose).'],
                [
                    $this->ex('私《わたし》は 留学生《りゅうがくせい》として 日本《にほん》に 来《き》ました。', 'Watashi wa ryuugakusei toshite nihon ni kimashita.', 'Saya datang ke Jepang sebagai mahasiswa asing.', 'I came to Japan as an international student.'),
                    $this->ex('この 建物《たてもの》は 昔《むかし》、病院《びょういん》として 使《つか》われて いた。', 'Kono tatemono wa mukashi, byouin toshite tsukawarete ita.', 'Gedung ini dahulu dipakai sebagai rumah sakit.', 'This building used to be used as a hospital.'),
                    $this->ex('一人《ひとり》の 親《おや》として、子《こ》どもの 将来《しょうらい》が 心配《しんぱい》です。', 'Hitori no oya toshite, kodomo no shourai ga shinpai desu.', 'Sebagai orang tua, saya mengkhawatirkan masa depan anak saya.', 'As a parent, I worry about my child\'s future.'),
                ]
            ),

            $this->card(
                '〜ず[に]… (keadaan penyerta, cara)',
                '〜ず[に]… (attendant circumstances, means)',
                'V ない形(−ない) ＋ ず[に] …　※する → せず',
                'Menyatakan keadaan penyerta atau cara: "tanpa melakukan ~". Maknanya sama dengan 〜ないで, tetapi lebih banyak dipakai dalam bahasa tulis daripada bahasa lisan.',
                'Indicates attendant circumstances or means: "without doing ~". It means the same as 〜ないで, but is used more in writing than in speech.',
                ['する berubah menjadi せず／せずに (bukan ×しず).', 'Dalam percakapan sehari-hari 〜ないで lebih wajar.'],
                ['する becomes せず／せずに (not ×しず).', 'In everyday conversation 〜ないで sounds more natural.'],
                [
                    $this->ex('朝《あさ》ごはんを 食《た》べずに 家《いえ》を 出《で》た。', 'Asagohan o tabezu ni ie o deta.', 'Saya meninggalkan rumah tanpa sarapan.', 'I left home without eating breakfast.'),
                    $this->ex('彼《かれ》は 何《なに》も 言《い》わず、静《しず》かに 部屋《へや》を 出《で》て 行《い》った。', 'Kare wa nani mo iwazu, shizuka ni heya o dete itta.', 'Ia keluar ruangan dengan tenang tanpa berkata apa-apa.', 'He quietly left the room without saying a word.'),
                    $this->ex('地図《ちず》を 見《み》ずに、目的地《もくてきち》まで 歩《ある》いて 行《い》けた。', 'Chizu o mizu ni, mokutekichi made aruite iketa.', 'Saya bisa berjalan sampai tujuan tanpa melihat peta.', 'I was able to walk to the destination without looking at a map.'),
                ]
            ),

            $this->card(
                '〜ず、… (sebab, urutan paralel)',
                '〜ず、… (cause／reason, parataxis)',
                'V ない形(−ない) ＋ ず、…　※する → せず',
                '(1) 〜ず、… menyatakan sebab atau alasan, sama dengan 〜なくて、… tetapi lebih sering dipakai dalam tulisan. (2) 「X ず、Y」 juga bisa menjajarkan dua klausa: "bukan X, melainkan Y".',
                '(1) 〜ず、… indicates a cause or reason, the same as 〜なくて、… but used more in writing. (2) 「X ず、Y」 can also juxtapose two clauses: "not X, but Y".',
                ['Berbeda dari 〜ずに (keadaan penyerta): pola ini memakai koma setelah 〜ず.', 'Bentuk 〜ず ini sering muncul di berita, laporan, dan surat.'],
                ['Different from 〜ずに (attendant circumstance): this pattern takes a comma after 〜ず.', 'This 〜ず form often appears in news, reports and letters.'],
                [
                    $this->ex('熱《ねつ》が 下《さ》がらず、一週間《いっしゅうかん》 学校《がっこう》を 休《やす》んだ。', 'Netsu ga sagarazu, isshuukan gakkou o yasunda.', 'Demamnya tidak turun, jadi saya absen sekolah seminggu.', 'The fever would not go down, so I stayed away from school for a week.'),
                    $this->ex('この 店《みせ》は 昼《ひる》は 開《あ》かず、夜《よる》だけ 営業《えいぎょう》して いる。', 'Kono mise wa hiru wa akazu, yoru dake eigyou shite iru.', 'Toko ini tidak buka siang hari, hanya buka malam.', 'This shop does not open in the daytime and operates only at night.'),
                    $this->ex('道《みち》が 分《わ》からず、約束《やくそく》の 時間《じかん》に 遅《おく》れて しまった。', 'Michi ga wakarazu, yakusoku no jikan ni okurete shimatta.', 'Tidak tahu jalan, sehingga terlambat dari waktu yang dijanjikan.', 'I did not know the way and was late for the appointment.'),
                ]
            ),

            $this->card(
                '〜ている (pengalaman, catatan, riwayat)',
                '〜ている (experience／record, history)',
                'V て形 ＋ いる',
                '〜ている juga dipakai untuk menyatakan fakta riwayat, pengalaman, atau catatan. Sering bersama keterangan frekuensi atau lama seperti 〜回《かい》 (kali), 長《なが》い 間《あいだ》 (lama), 以来《いらい》, dan 過去《かこ》に. Tindakan di masa lalu dianggap masih berkaitan dengan keadaan sekarang.',
                '〜ている is also used for a historical fact, experience or record. It is often used with adverbs of frequency or duration such as 〜回《かい》 (times), 長《なが》い 間《あいだ》 (for a long time), 以来《いらい》, and 過去《かこ》に. The past action is regarded as relevant to the present situation.',
                ['Bukan sedang berlangsung, bukan keadaan hasil, bukan kebiasaan, melainkan catatan riwayat.', 'Bentuk negatifnya (起《お》こって いない) berarti "belum pernah terjadi (selama ini)".'],
                ['It is not ongoing action, resultant state or habit, but a record of history.', 'The negative (起《お》こって いない) means "has not happened (up to now)".'],
                [
                    $this->ex('この 工場《こうじょう》は 創業《そうぎょう》以来《いらい》、一度《いちど》も 事故《じこ》を 起《お》こして いない。', 'Kono koujou wa sougyou irai, ichido mo jiko o okoshite inai.', 'Pabrik ini sejak berdiri tidak pernah sekali pun mengalami kecelakaan.', 'This factory has not had a single accident since it was founded.'),
                    $this->ex('彼女《かのじょ》は 子《こ》どもの ころ 何度《なんど》も 引《ひ》っ越《こ》しを して いる。', 'Kanojo wa kodomo no koro nando mo hikkoshi o shite iru.', 'Sewaktu kecil ia sudah berkali-kali pindah rumah.', 'She moved house many times when she was a child.'),
                    $this->ex('この 橋《はし》は 過去《かこ》に 二回《にかい》、洪水《こうずい》で 流《なが》されて いる。', 'Kono hashi wa kako ni nikai, kouzui de nagasarete iru.', 'Jembatan ini pernah dua kali hanyut oleh banjir.', 'This bridge has been washed away by floods twice in the past.'),
                ]
            ),

            $this->card(
                '〜なんか どう？ (menawarkan contoh)',
                '〜なんか どう？ (offering an example)',
                'N ＋ なんか ＋ どう？／どうですか／どうでしょうか',
                'Dipakai untuk menawarkan contoh yang cocok kepada lawan bicara, dengan nuansa bahwa ada pilihan lain yang juga patut dipertimbangkan, sehingga tidak terkesan memaksakan usul sendiri. 〜など どうですか artinya sama tetapi sedikit lebih formal.',
                'Used to offer a suitable example to the listener, implying that there are other options worth considering, so it does not seem to force one\'s own suggestion. 〜など どうですか means the same but is a little more formal.',
                ['Berbeda dari Nなんか yang merendahkan (私《わたし》の 料理《りょうり》なんか): di sini nuansanya hanya menawarkan.', 'Di toko: これなんか いかがでしょうか.'],
                ['Different from the belittling Nなんか (私《わたし》の 料理《りょうり》なんか): here it simply offers.', 'In a shop: これなんか いかがでしょうか.'],
                [
                    $this->ex('誕生日《たんじょうび》の プレゼントは、この 時計《とけい》なんか どうですか。', 'Tanjoubi no purezento wa, kono tokei nanka dou desu ka.', 'Untuk hadiah ulang tahun, bagaimana kalau jam ini?', 'For a birthday present, how about this watch?'),
                    $this->ex('週末《しゅうまつ》の 食事《しょくじ》は、イタリア料理《りょうり》なんか どう？', 'Shuumatsu no shokuji wa, itaria ryouri nanka dou?', 'Untuk makan akhir pekan, bagaimana kalau masakan Italia?', 'For the weekend meal, how about Italian food?'),
                    $this->ex('次《つぎ》の 幹事《かんじ》は、佐藤《さとう》さんなんか どうでしょうか。', 'Tsugi no kanji wa, Satou-san nanka dou deshou ka.', 'Untuk panitia berikutnya, bagaimana kalau Pak Sato?', 'For the next organiser, how about Mr. Sato?'),
                ]
            ),
        ];
    }
}
