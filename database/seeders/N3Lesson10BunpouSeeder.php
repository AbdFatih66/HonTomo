<?php

namespace Database\Seeders;

/**
 * Tata Bahasa N3 Pelajaran 10 (9 kartu, dengan dialog).
 *   php artisan db:seed --class=N3Lesson10BunpouSeeder
 */
class N3Lesson10BunpouSeeder extends N3GrammarSeeder
{
    protected function unitOrder(): int
    {
        return 10;
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
                $this->dl('A', '山田《やまだ》さん、まだ 来《こ》ないね。', 'Yamada-san, mada konai ne.', 'Pak Yamada belum datang ya.', 'Mr. Yamada still has not come.'),
                $this->dl('B', '十時《じゅうじ》の 電車《でんしゃ》に 乗《の》ると 言《い》って いたから、もうすぐ 着《つ》く はずだよ。', 'Juuji no densha ni noru to itte ita kara, mou sugu tsuku hazu da yo.', 'Katanya naik kereta jam sepuluh, jadi seharusnya sebentar lagi sampai.', 'He said he would take the ten o\'clock train, so he should arrive soon.'),
                $this->dl('A', '電話《でんわ》して みようか。', 'Denwa shite miyou ka.', 'Mau kutelepon?', 'Shall I call him?'),
                $this->dl('B', '移動中《いどうちゅう》の はずだから、少《すこ》し 待《ま》とう。', 'Idouchuu no hazu da kara, sukoshi matou.', 'Seharusnya dia sedang di perjalanan, kita tunggu sebentar.', 'He should be on the way, so let us wait a little.'),
            ],
            2 => [
                $this->dl('A', 'この 財布《さいふ》、鈴木《すずき》さんのかな。', 'Kono saifu, Suzuki-san no kana.', 'Dompet ini milik Pak Suzuki, ya?', 'I wonder if this wallet is Mr. Suzuki\'s.'),
                $this->dl('B', '鈴木《すずき》さんは 先週《せんしゅう》から 出張中《しゅっちょうちゅう》だから、会社《かいしゃ》に ある はずが ないよ。', 'Suzuki-san wa senshuu kara shucchouchuu da kara, kaisha ni aru hazu ga nai yo.', 'Pak Suzuki sedang dinas luar sejak minggu lalu, tidak mungkin dompetnya ada di kantor.', 'Mr. Suzuki has been on a business trip since last week, so it cannot be here at the office.'),
                $this->dl('A', 'そうか。じゃあ、受付《うけつけ》に 届《とど》けよう。', 'Sou ka. Jaa, uketsuke ni todokeyou.', 'Begitu ya. Kalau begitu, kita serahkan ke resepsionis.', 'I see. Then let us hand it in at reception.'),
            ],
            3 => [
                $this->dl('A', '昨日《きのう》の 試合《しあい》、見《み》に 行《い》った？', 'Kinou no shiai, mi ni itta?', 'Kamu pergi menonton pertandingan kemarin?', 'Did you go to see yesterday\'s match?'),
                $this->dl('B', '行《い》く はずだったんだけど、急《きゅう》に 残業《ざんぎょう》に なっちゃって。', 'Iku hazu datta n da kedo, kyuu ni zangyou ni nacchatte.', 'Seharusnya pergi, tetapi tiba-tiba harus lembur.', 'I was supposed to go, but I suddenly had to work overtime.'),
                $this->dl('A', 'それは 残念《ざんねん》だったね。', 'Sore wa zannen datta ne.', 'Sayang sekali ya.', 'What a shame.'),
                $this->dl('B', 'うん。チケットも 買《か》って あったのに。', 'Un. Chiketto mo katte atta no ni.', 'Iya. Padahal tiketnya sudah dibeli.', 'Yes. And I had even bought the ticket.'),
            ],
            4 => [
                $this->dl('A', '新《あたら》しい 機械《きかい》は どう？', 'Atarashii kikai wa dou?', 'Bagaimana mesin yang baru?', 'How is the new machine?'),
                $this->dl('B', '便利《べんり》だけど、ときどき 止《と》まる ことが あるんだ。', 'Benri da kedo, tokidoki tomaru koto ga aru n da.', 'Praktis, tetapi kadang-kadang berhenti sendiri.', 'It is handy, but it stops now and then.'),
                $this->dl('A', '原因《げんいん》は わかった？', 'Gen-in wa wakatta?', 'Sudah tahu penyebabnya?', 'Do you know the cause yet?'),
                $this->dl('B', 'いいえ。動《うご》かない ことも あるから、メーカーに 連絡《れんらく》しようと 思《おも》って いる。', 'Iie. Ugokanai koto mo aru kara, meekaa ni renraku shiyou to omotte iru.', 'Belum. Kadang juga tidak mau menyala, jadi saya berniat menghubungi produsennya.', 'Not yet. It sometimes will not start either, so I am thinking of contacting the manufacturer.'),
            ],
            5 => [
                $this->dl('A', '工場《こうじょう》の 騒音《そうおん》の 調査《ちょうさ》は どう なりましたか。', 'Koujou no souon no chousa wa dou narimashita ka.', 'Bagaimana hasil survei kebisingan pabrik?', 'How did the survey of the factory noise turn out?'),
                $this->dl('B', '測定《そくてい》した 結果《けっか》、基準《きじゅん》を 超《こ》えて いる 場所《ばしょ》が ありました。', 'Sokutei shita kekka, kijun o koete iru basho ga arimashita.', 'Setelah diukur, ada tempat yang melampaui batas standar.', 'As a result of measuring, some places exceeded the standard.'),
                $this->dl('A', '対策《たいさく》は？', 'Taisaku wa?', 'Lalu langkah penanganannya?', 'And the countermeasures?'),
                $this->dl('B', '話《はな》し合《あ》いの 結果《けっか》、来月《らいげつ》 壁《かべ》を 増《ふ》やす ことに なりました。', 'Hanashiai no kekka, raigetsu kabe o fuyasu koto ni narimashita.', 'Hasil diskusi: bulan depan dinding akan ditambah.', 'After discussion, it was decided to add walls next month.'),
            ],
            6 => [
                $this->dl('A', '外《そと》、見《み》て。雨《あめ》が 降《ふ》り出《だ》したよ。', 'Soto, mite. Ame ga furidashita yo.', 'Lihat di luar. Hujan mulai turun.', 'Look outside. It has started to rain.'),
                $this->dl('B', '本当《ほんとう》だ。傘《かさ》、持《も》って きた？', 'Hontou da. Kasa, motte kita?', 'Benar. Kamu bawa payung?', 'You are right. Did you bring an umbrella?'),
                $this->dl('A', 'ううん、持《も》って きて いない。', 'Uun, motte kite inai.', 'Tidak, aku tidak bawa.', 'No, I did not.'),
                $this->dl('B', 'じゃあ、止《や》むまで ここで 待《ま》ちましょう。', 'Jaa, yamu made koko de machimashou.', 'Kalau begitu, mari kita menunggu di sini sampai reda.', 'Then let us wait here until it stops.'),
            ],
            7 => [
                $this->dl('A', 'レポートは もう 書《か》き終《お》わりましたか。', 'Repooto wa mou kakiowarimashita ka.', 'Apakah laporannya sudah selesai ditulis?', 'Have you finished writing the report?'),
                $this->dl('B', 'いいえ、まだ 書《か》き始《はじ》めた ところです。', 'Iie, mada kakihajimeta tokoro desu.', 'Belum, baru mulai menulis.', 'No, I have only just started writing.'),
                $this->dl('A', '明日《あした》までですよ。', 'Ashita made desu yo.', 'Batasnya besok lho.', 'It is due by tomorrow, you know.'),
                $this->dl('B', 'わかって います。今夜《こんや》は 夜中《よなか》まで 書《か》き続《つづ》けます。', 'Wakatte imasu. Kon-ya wa yonaka made kakitsuzukemasu.', 'Saya tahu. Malam ini saya akan terus menulis sampai tengah malam.', 'I know. Tonight I will keep writing until midnight.'),
            ],
            8 => [
                $this->dl('A', 'あっ、切符《きっぷ》を 買《か》い忘《わす》れちゃった。', 'Aa, kippu o kaiwasurechatta.', 'Aduh, aku lupa membeli tiket.', 'Oh no, I forgot to buy a ticket.'),
                $this->dl('B', '今《いま》なら まだ 間《ま》に 合《あ》うよ。窓口《まどぐち》で 買《か》って おいで。', 'Ima nara mada ma ni au yo. Madoguchi de katte oide.', 'Kalau sekarang masih sempat. Belilah di loket.', 'You can still make it now. Go and buy one at the window.'),
                $this->dl('A', 'うん。ところで、乗《の》り換《か》えは どこだっけ？', 'Un. Tokoro de, norikae wa doko dakke?', 'Iya. Omong-omong, pindah keretanya di mana ya?', 'Right. By the way, where do we change trains again?'),
                $this->dl('B', '次《つぎ》の 駅《えき》で 乗《の》り換《か》えるんだよ。二人《ふたり》で 確《たし》かめ合《あ》おう。', 'Tsugi no eki de norikaeru n da yo. Futari de tashikameaou.', 'Pindah di stasiun berikutnya. Mari kita periksa bersama-sama.', 'We change at the next station. Let us check it together.'),
            ],
            9 => [
                $this->dl('A', '部長《ぶちょう》が 出張《しゅっちょう》から 戻《もど》るのは 金曜日《きんようび》ですね。', 'Buchou ga shucchou kara modoru no wa kinyoubi desu ne.', 'Kepala bagian kembali dari dinas luar hari Jumat ya.', 'The department manager returns from the business trip on Friday, right?'),
                $this->dl('B', 'ええ。でも 会議《かいぎ》は 木曜日《もくようび》です。', 'Ee. Demo kaigi wa mokuyoubi desu.', 'Ya. Tetapi rapatnya hari Kamis.', 'Yes. But the meeting is on Thursday.'),
                $this->dl('A', 'ということは、部長《ぶちょう》は 会議《かいぎ》に 出《で》られないと いう ことに なりますね。', 'To iu koto wa, buchou wa kaigi ni derarenai to iu koto ni narimasu ne.', 'Berarti kepala bagian tidak bisa hadir di rapat ya.', 'That means the manager cannot attend the meeting.'),
                $this->dl('B', 'そうです。代《か》わりに 課長《かちょう》が 出《で》ます。', 'Sou desu. Kawari ni kachou ga demasu.', 'Benar. Sebagai gantinya kepala seksi yang hadir.', 'Right. The section chief will attend instead.'),
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    protected function cards(): array
    {
        return [
            $this->card(
                '…はずだ (keyakinan berdasar alasan)',
                '…はずだ (conviction based on grounds)',
                'V・いA 普通形《ふつうけい》／なA(〜な)／N(〜の) ＋ はずだ',
                'Menyatakan keyakinan kuat pembicara bahwa sesuatu pasti benar, berdasarkan perhitungan, pengetahuan sebelumnya, atau logika. Kata 「はず」 diperlakukan seperti kata benda, sehingga muncul dalam bentuk はずの N, はずなのに, dan そのはずだ.',
                'Expresses the speaker\'s strong belief that something is true, based on calculation, prior knowledge or logic. 「はず」 behaves like a noun, which is why forms such as はずの N, はずなのに and そのはずだ exist.',
                ['なA: 〜な ＋ はず; N: 〜の ＋ はず. Sopan: はずです.', 'Perlu dasar yang jelas. Tebakan tanpa dasar lebih cocok memakai 〜だろう／〜かもしれない.'],
                ['な-adjectives: 〜な ＋ はず; nouns: 〜の ＋ はず. Polite: はずです.', 'It needs a clear basis. A guess without grounds fits 〜だろう／〜かもしれない better.'],
                [
                    $this->ex('今《いま》 九時《くじ》だから、駅前《えきまえ》の スーパーは もう 開《あ》いて いる はずだ。', 'Ima kuji da kara, ekimae no suupaa wa mou aite iru hazu da.', 'Sekarang jam sembilan, jadi supermarket di depan stasiun seharusnya sudah buka.', 'It is nine o\'clock now, so the supermarket in front of the station should be open.'),
                    $this->ex('田中《たなか》さんは 毎日《まいにち》 ジムに 通《かよ》って いるから、体力《たいりょく》が ある はずだ。', 'Tanaka-san wa mainichi jimu ni kayotte iru kara, tairyoku ga aru hazu da.', 'Pak Tanaka pergi ke gym setiap hari, jadi staminanya pasti bagus.', 'Mr. Tanaka goes to the gym every day, so he must have plenty of stamina.'),
                    $this->ex('昨日《きのう》 掃除《そうじ》した ばかりなので、この 部屋《へや》は きれいな はずです。', 'Kinou souji shita bakari na node, kono heya wa kirei na hazu desu.', 'Baru kemarin dibersihkan, jadi ruangan ini seharusnya bersih.', 'It was only cleaned yesterday, so this room should be clean.'),
                ]
            ),

            $this->card(
                '…はずが／はない (tidak mungkin)',
                '…はずが／はない (impossible)',
                'V・いA 普通形《ふつうけい》／なA(〜な)／N(〜の) ＋ はずが ない／はずは ない',
                'Bentuk negatif dari 〜はずだ. Menyangkal dengan tegas bahwa sesuatu mungkin terjadi, berdasarkan alasan yang masuk akal: "mustahil, tidak mungkin". Sopan: はずが ありません／はずは ありません.',
                'The negative of 〜はずだ. It firmly denies that something is possible, based on sound reasoning: "impossible, out of the question". Polite: はずが ありません／はずは ありません.',
                ['Ungkapan そんな はずは ない dipakai untuk menolak ucapan orang lain: "Itu tidak mungkin!"', 'Bukan larangan. Artinya bukan "tidak boleh" melainkan "tidak mungkin".'],
                ['そんな はずは ない is used to reject what someone has just said: "That cannot be true!"', 'It is not a prohibition. It means "cannot possibly be", not "must not".'],
                [
                    $this->ex('勉強《べんきょう》を 始《はじ》めた ばかりの 人《ひと》が、すぐに 試験《しけん》に 受《う》かる はずが ない。', 'Benkyou o hajimeta bakari no hito ga, sugu ni shiken ni ukaru hazu ga nai.', 'Orang yang baru mulai belajar tidak mungkin langsung lulus ujian.', 'Someone who has only just started studying cannot possibly pass the exam right away.'),
                    $this->ex('彼《かれ》は 先月《せんげつ》 来《き》た ばかりだから、この 町《まち》に 詳《くわ》しい はずが ありません。', 'Kare wa sengetsu kita bakari da kara, kono machi ni kuwashii hazu ga arimasen.', 'Dia baru datang bulan lalu, jadi tidak mungkin ia hafal kota ini.', 'He only came last month, so he cannot know this town well.'),
                    $this->ex('あの 優《やさ》しい 先生《せんせい》が 怒《おこ》る はずは ない。', 'Ano yasashii sensei ga okoru hazu wa nai.', 'Guru yang lembut itu tidak mungkin marah.', 'That gentle teacher cannot possibly be angry.'),
                ]
            ),

            $this->card(
                '…はずだった (seharusnya begitu, tetapi…)',
                '…はずだった (was supposed to, but…)',
                'V・いA 普通形《ふつうけい》／なA(〜な)／N(〜の) ＋ はずだった',
                'Bentuk lampau dari 〜はずだ. Menyatakan bahwa sesuatu semula diharapkan atau direncanakan terjadi, dan paling sering dipakai ketika hasilnya ternyata berbeda dari yang diperkirakan.',
                'The past of 〜はずだ. It says something had been expected or planned to happen, and it is used most often when the outcome turned out different from what was expected.',
                ['Sering disusul しかし／が／のに yang menyebut kenyataannya.', '〜はずでは なかった = "seharusnya tidak begini" (penyesalan atau kekecewaan).'],
                ['It is often followed by しかし／が／のに stating the reality.', '〜はずでは なかった = "it was not supposed to be like this" (regret or disappointment).'],
                [
                    $this->ex('今日《きょう》は 出荷《しゅっか》の はずだったが、部品《ぶひん》が 届《とど》かなくて 遅《おく》れて いる。', 'Kyou wa shukka no hazu datta ga, buhin ga todokanakute okurete iru.', 'Hari ini seharusnya dikirim, tetapi suku cadang belum tiba sehingga tertunda.', 'It was supposed to ship today, but the parts have not arrived and it is delayed.'),
                    $this->ex('先週《せんしゅう》には 完成《かんせい》する はずだったのに、まだ 終《お》わって いない。', 'Senshuu ni wa kansei suru hazu datta no ni, mada owatte inai.', 'Padahal seharusnya selesai minggu lalu, tetapi belum juga rampung.', 'It was supposed to be finished last week, yet it is still not done.'),
                    $this->ex('こんなに 難《むずか》しい はずでは なかった。', 'Konna ni muzukashii hazu de wa nakatta.', 'Seharusnya tidak sesulit ini.', 'It was not supposed to be this difficult.'),
                ]
            ),

            $this->card(
                '…ことが／もある (kadang-kadang terjadi)',
                '…ことが／もある (sometimes happens)',
                'V 辞書形《じしょけい》／V ない形／いA(〜い)／なA(〜な)／N(〜の) ＋ ことが／も ある',
                'Menyatakan bahwa suatu kejadian terkadang terjadi atau suatu keadaan kadang muncul. 〜ことが ある dan 〜ことも ある hampir selalu bermakna sama; 〜ことも ある menekankan "ada juga kalanya".',
                'Says that an event occasionally happens or a situation sometimes arises. 〜ことが ある and 〜ことも ある mean almost the same; 〜ことも ある stresses "there are also times when".',
                ['Sering bersama ときどき／たまに／たまには.', 'Jangan tertukar dengan V た ＋ ことが ある (pengalaman: "pernah ~").'],
                ['Often used with ときどき／たまに／たまには.', 'Do not confuse it with V た ＋ ことが ある (experience: "have ever ~").'],
                [
                    $this->ex('この 道《みち》は 普段《ふだん》 空《す》いて いるが、雨《あめ》の 日《ひ》は 混《こ》む ことが ある。', 'Kono michi wa fudan suite iru ga, ame no hi wa komu koto ga aru.', 'Jalan ini biasanya sepi, tetapi kadang ramai pada hari hujan.', 'This road is usually empty, but it sometimes gets crowded on rainy days.'),
                    $this->ex('夫《おっと》は 料理《りょうり》が 得意《とくい》だが、たまに 塩《しお》を 入《い》れすぎる ことも ある。', 'Otto wa ryouri ga tokui da ga, tama ni shio o iresugiru koto mo aru.', 'Suami saya pandai memasak, tetapi sesekali garamnya kebanyakan.', 'My husband is good at cooking, but sometimes he puts in too much salt.'),
                    $this->ex('この 地域《ちいき》は 暖《あたた》かいが、冬《ふゆ》には 雪《ゆき》の ことも ある。', 'Kono chiiki wa atatakai ga, fuyu ni wa yuki no koto mo aru.', 'Daerah ini hangat, tetapi di musim dingin kadang turun salju.', 'This area is warm, but in winter it sometimes snows.'),
                ]
            ),

            $this->card(
                'V た結果《けっか》、…／N の結果《けっか》、… (sebagai hasil dari)',
                'V た結果《けっか》、…／N の結果《けっか》、… (as a result of)',
                'V た形／N の ＋ 結果《けっか》、…',
                'Menyatakan bahwa suatu tindakan (〜) menghasilkan suatu kejadian atau kesimpulan (…). Terutama dipakai dalam tulisan, laporan, dan siaran berita televisi atau radio.',
                'Shows that an action (〜) led to a subsequent event or conclusion (…). Mainly used in writing, reports, and on TV or radio news.',
                ['Bagian setelah 結果《けっか》 menyebut hasil nyata: ことが わかった／ことに した／ことに なった.', 'Kata benda memakai の: 調査《ちょうさ》の 結果《けっか》.'],
                ['What follows 結果《けっか》 states an actual outcome: ことが わかった／ことに した／ことに なった.', 'Nouns take の: 調査《ちょうさ》の 結果《けっか》.'],
                [
                    $this->ex('何度《なんど》も 検査《けんさ》した 結果《けっか》、製品《せいひん》に 問題《もんだい》は ないと わかった。', 'Nando mo kensa shita kekka, seihin ni mondai wa nai to wakatta.', 'Setelah berkali-kali diperiksa, diketahui produk ini tidak bermasalah.', 'After repeated inspections, it turned out the product had no problems.'),
                    $this->ex('家族《かぞく》との 相談《そうだん》の 結果《けっか》、来年《らいねん》から 実家《じっか》の 近《ちか》くに 引《ひ》っ越《こ》す ことに した。', 'Kazoku to no soudan no kekka, rainen kara jikka no chikaku ni hikkosu koto ni shita.', 'Setelah berunding dengan keluarga, saya memutuskan pindah ke dekat rumah orang tua mulai tahun depan.', 'After talking it over with my family, I decided to move near my parents\' home from next year.'),
                    $this->ex('調査《ちょうさ》の 結果《けっか》、若《わか》い 人《ひと》ほど 新聞《しんぶん》を 読《よ》まない ことが 明《あき》らかに なった。', 'Chousa no kekka, wakai hito hodo shinbun o yomanai koto ga akiraka ni natta.', 'Dari hasil survei, terbukti makin muda seseorang makin jarang membaca koran.', 'The survey showed that the younger the person, the less they read newspapers.'),
                ]
            ),

            $this->card(
                'V 出《だ》す (kata kerja majemuk: mulai mendadak)',
                'V 出《だ》す (compound verb: suddenly begin)',
                'V ます語幹《ごかん》 ＋ 出《だ》す',
                'Menyatakan bahwa tindakan V mulai terjadi, sering secara tiba-tiba atau tanpa direncanakan. Contoh: 泣《な》き出《だ》す (mulai menangis), 降《ふ》り出《だ》す (mulai turun hujan), 動《うご》き出《だ》す (mulai bergerak), 笑《わら》い出《だ》す, 話《はな》し出《だ》す.',
                'Says that the action V commences, often suddenly or unplanned. Examples: 泣《な》き出《だ》す (burst into tears), 降《ふ》り出《だ》す (begin to rain), 動《うご》き出《だ》す (start moving), 笑《わら》い出《だ》す, 話《はな》し出《だ》す.',
                ['Tidak dapat dipakai untuk ajakan, permintaan, atau niat: ×食《た》べ出《だ》しましょう → ○食《た》べ始《はじ》めましょう.', 'Untuk kalimat semacam itu pakailah 〜始《はじ》める.'],
                ['It cannot be used for invitations, requests or intentions: ×食《た》べ出《だ》しましょう → ○食《た》べ始《はじ》めましょう.', 'Use 〜始《はじ》める for such sentences.'],
                [
                    $this->ex('赤《あか》ちゃんが 急《きゅう》に 泣《な》き出《だ》した。', 'Akachan ga kyuu ni nakidashita.', 'Bayi itu tiba-tiba menangis.', 'The baby suddenly burst into tears.'),
                    $this->ex('会議《かいぎ》の 途中《とちゅう》で、外《そと》が 暗《くら》く なって 雨《あめ》が 降《ふ》り出《だ》した。', 'Kaigi no tochuu de, soto ga kuraku natte ame ga furidashita.', 'Di tengah rapat, di luar menjadi gelap dan hujan mulai turun.', 'In the middle of the meeting it grew dark outside and it began to rain.'),
                    $this->ex('信号《しんごう》が 青《あお》に なると、車《くるま》が 一斉《いっせい》に 走《はし》り出《だ》した。', 'Shingou ga ao ni naru to, kuruma ga issei ni hashiridashita.', 'Begitu lampu berubah hijau, mobil-mobil serentak melaju.', 'When the light turned green, the cars all started moving at once.'),
                ]
            ),

            $this->card(
                'V 始《はじ》める・終《お》わる・続《つづ》ける (kata kerja majemuk)',
                'V 始《はじ》める・終《お》わる・続《つづ》ける (compound verbs)',
                'V ます語幹《ごかん》 ＋ 始《はじ》める／終《お》わる／続《つづ》ける',
                'Menyatakan V mulai, selesai, atau berlanjut. 〜始《はじ》める dapat dipakai untuk ajakan, permintaan, dan niat; 〜終《お》わる menekankan bahwa tindakan telah sampai selesai; 〜続《つづ》ける menekankan tindakan yang terus berlangsung.',
                'Shows V beginning, ending or continuing. 〜始《はじ》める can be used in invitations, requests and intentions; 〜終《お》わる stresses that the action has been carried through to the end; 〜続《つづ》ける stresses that it keeps going.',
                ['Untuk titik awal gunakan 降《ふ》り始《はじ》めた, bukan 降《ふ》った, bila yang dimaksud adalah saat hujan mulai.', 'Bandingkan 〜出《だ》す: lebih mendadak dan tidak bisa dipakai untuk ajakan.'],
                ['To pinpoint the moment, use 降《ふ》り始《はじ》めた rather than 降《ふ》った when you mean the moment the rain started.', 'Compare 〜出《だ》す: more sudden, and not usable for invitations.'],
                [
                    $this->ex('食事《しょくじ》を 食《た》べ終《お》わったら、お皿《さら》を 片付《かたづ》けて ください。', 'Shokuji o tabeowattara, osara o katazukete kudasai.', 'Setelah selesai makan, tolong rapikan piringnya.', 'When you have finished eating, please clear away the dishes.'),
                    $this->ex('彼《かれ》は 三十年《さんじゅうねん》間《かん》 同《おな》じ 会社《かいしゃ》で 働《はたら》き続《つづ》けて いる。', 'Kare wa sanjuunen kan onaji kaisha de hatarakitsuzukete iru.', 'Ia terus bekerja di perusahaan yang sama selama tiga puluh tahun.', 'He has kept working at the same company for thirty years.'),
                    $this->ex('先月《せんげつ》から 日本語《にほんご》を 習《なら》い始《はじ》めました。', 'Sengetsu kara nihongo o naraihajimemashita.', 'Saya mulai belajar bahasa Jepang sejak bulan lalu.', 'I began learning Japanese last month.'),
                ]
            ),

            $this->card(
                'V 忘《わす》れる・合《あ》う・換《か》える (kata kerja majemuk)',
                'V 忘《わす》れる・合《あ》う・換《か》える (compound verbs)',
                'V ます語幹《ごかん》 ＋ 忘《わす》れる／合《あ》う／換《か》える',
                '〜忘《わす》れる = lupa melakukan V (置《お》き忘《わす》れる: lupa membawa pulang barang yang ditaruh). 〜合《あ》う = beberapa orang atau benda saling melakukan V (助《たす》け合《あ》う, 話《はな》し合《あ》う). 〜換《か》える = mengganti sesuatu dengan melakukan V, atau melakukan V dengan cara lain (乗《の》り換《か》える, 入《い》れ換《か》える).',
                '〜忘《わす》れる = forget to do V (置《お》き忘《わす》れる: leave something behind by mistake). 〜合《あ》う = several people or things do V to or with each other (助《たす》け合《あ》う, 話《はな》し合《あ》う). 〜換《か》える = change something by doing V, or do V in a different way (乗《の》り換《か》える, 入《い》れ換《か》える).',
                ['Kata kerja majemuk ini punya arti yang bisa ditebak dari bagian V-nya.', 'Bentuk 〜忘《わす》れた dipakai untuk kelalaian: 書《か》き忘《わす》れる, 言《い》い忘《わす》れる.'],
                ['The meaning of these compounds can be guessed from the V part.', '〜忘《わす》れた is used for an oversight: 書《か》き忘《わす》れる, 言《い》い忘《わす》れる.'],
                [
                    $this->ex('傘《かさ》を 電車《でんしゃ》の 中《なか》に 置《お》き忘《わす》れて しまった。', 'Kasa o densha no naka ni okiwasurete shimatta.', 'Payung saya tertinggal di dalam kereta.', 'I left my umbrella behind on the train.'),
                    $this->ex('隣《となり》の 人《ひと》と 意見《いけん》を 話《はな》し合《あ》って ください。', 'Tonari no hito to iken o hanashiatte kudasai.', 'Silakan bertukar pendapat dengan orang di sebelah Anda.', 'Please discuss your opinions with the person next to you.'),
                    $this->ex('駅《えき》で 急行《きゅうこう》に 乗《の》り換《か》えます。', 'Eki de kyuukou ni norikaemasu.', 'Di stasiun saya pindah ke kereta ekspres.', 'I change to an express train at the station.'),
                ]
            ),

            $this->card(
                '…と いう ことに なる (jadi berarti…)',
                '…と いう ことに なる (that means…)',
                '普通形《ふつうけい》 ＋ と いう ことに なる',
                'Dipakai untuk merangkum beberapa informasi lalu menyatakan kesimpulan atau implikasinya: "jadi, berarti…". Bagian sebelum と いう berisi kondisi atau fakta, bagian setelahnya adalah kesimpulan yang ditarik.',
                'Used to sum up several pieces of information and state the implication of the situation: "so that means…". What comes before と いう is the condition or fact, and what follows is the conclusion drawn.',
                ['Sering bersama 〜ば／〜たら／〜という ことは untuk menarik kesimpulan.', 'Berbeda dari 〜ことに なる (keputusan atau ketetapan): di sini 〜と いう menunjukkan kesimpulan yang dihitung secara logis.'],
                ['Often used with 〜ば／〜たら／〜という ことは to draw a conclusion.', 'Unlike 〜ことに なる (a decision or arrangement), 〜と いう marks a logically derived conclusion.'],
                [
                    $this->ex('参加者《さんかしゃ》が 二十人《にじゅうにん》 集《あつ》まらなければ、この 講座《こうざ》は 開《ひら》かれないと いう ことに なる。', 'Sankasha ga nijuunin atsumaranakereba, kono kouza wa hirakarenai to iu koto ni naru.', 'Kalau peserta tidak mencapai dua puluh orang, berarti kuliah ini tidak jadi dibuka.', 'If twenty participants do not gather, that means this course will not be held.'),
                    $this->ex('毎月《まいつき》 三万円《さんまんえん》ずつ 貯金《ちょきん》すれば、一年《いちねん》で 三十六万円《さんじゅうろくまんえん》 貯《た》まると いう ことに なる。', 'Maitsuki sanmanen zutsu chokin sureba, ichinen de sanjuuroku-man-en tamaru to iu koto ni naru.', 'Kalau menabung tiga puluh ribu yen tiap bulan, berarti dalam setahun terkumpul 360.000 yen.', 'If you save 30,000 yen each month, that means you will have 360,000 yen in a year.'),
                    $this->ex('彼《かれ》が 来《こ》なかったと いう ことは、今回《こんかい》の 計画《けいかく》に 反対《はんたい》だと いう ことに なる。', 'Kare ga konakatta to iu koto wa, konkai no keikaku ni hantai da to iu koto ni naru.', 'Dia tidak datang, jadi berarti dia menentang rencana kali ini.', 'He did not come, which means he is against this plan.'),
                ]
            ),
        ];
    }
}
