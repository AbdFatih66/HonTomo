<?php

namespace Database\Seeders;

/**
 * Tata Bahasa N3 Pelajaran 3 (7 kartu, dengan dialog).
 *   php artisan db:seed --class=N3Lesson3BunpouSeeder
 */
class N3Lesson3BunpouSeeder extends N3GrammarSeeder
{
    protected function unitOrder(): int
    {
        return 3;
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
                $this->dl('A', 'すみません、明日《あした》 早《はや》く 帰《かえ》らせて もらえませんか。', 'Sumimasen, ashita hayaku kaerasete moraemasen ka.', 'Maaf, bolehkah saya pulang lebih awal besok?', 'Excuse me, may I leave early tomorrow?'),
                $this->dl('B', 'どうしたんですか。', 'Doushita n desu ka.', 'Ada apa?', 'What is the matter?'),
                $this->dl('A', '子《こ》どもの 学校《がっこう》で 面談《めんだん》が あるんです。', 'Kodomo no gakkou de mendan ga aru n desu.', 'Ada pertemuan orang tua di sekolah anak saya.', 'I have a parent-teacher meeting at my child\'s school.'),
                $this->dl('B', 'わかりました。いいですよ。', 'Wakarimashita. Ii desu yo.', 'Baik. Silakan.', 'I see. That is fine.'),
            ],
            2 => [
                $this->dl('A', '来月《らいげつ》から、毎朝《まいあさ》 走《はし》る ことに しました。', 'Raigetsu kara, maiasa hashiru koto ni shimashita.', 'Mulai bulan depan saya memutuskan berlari setiap pagi.', 'I have decided to run every morning from next month.'),
                $this->dl('B', 'すごいですね。', 'Sugoi desu ne.', 'Hebat ya.', 'That is great.'),
                $this->dl('A', '夜《よる》は 甘《あま》い ものを 食《た》べない ことに して います。', 'Yoru wa amai mono o tabenai koto ni shite imasu.', 'Malam hari saya selalu tidak makan yang manis-manis.', 'I make it a rule not to eat sweets at night.'),
                $this->dl('B', '続《つづ》けられると いいですね。', 'Tsuzukerareru to ii desu ne.', 'Semoga bisa terus berlanjut.', 'I hope you can keep it up.'),
            ],
            3 => [
                $this->dl('A', '実《じつ》は、来月《らいげつ》 大阪《おおさか》へ 転勤《てんきん》する ことに なりました。', 'Jitsu wa, raigetsu Oosaka e tenkin suru koto ni narimashita.', 'Sebenarnya, bulan depan saya akan dipindahkan ke Osaka.', 'Actually, it has been decided that I will be transferred to Osaka next month.'),
                $this->dl('B', 'えっ、本当《ほんとう》ですか。', 'E, hontou desu ka.', 'Eh, benarkah?', 'What, really?'),
                $this->dl('A', 'ええ。うちの 会社《かいしゃ》では 三年《さんねん》ごとに 転勤《てんきん》する ことに なって いるんです。', 'Ee. Uchi no kaisha de wa sannen goto ni tenkin suru koto ni natte iru n desu.', 'Ya. Di perusahaan kami memang sudah menjadi aturan dipindahkan tiap tiga tahun.', 'Yes. At our company it is the rule to be transferred every three years.'),
                $this->dl('B', 'そうですか。寂《さび》しく なりますね。', 'Sou desu ka. Sabishiku narimasu ne.', 'Begitu ya. Jadi sepi nanti.', 'I see. We will miss you.'),
            ],
            4 => [
                $this->dl('A', '息子《むすこ》には もっと 勉強《べんきょう》して ほしいんです。', 'Musuko ni wa motto benkyou shite hoshii n desu.', 'Saya ingin anak saya belajar lebih giat.', 'I wish my son would study harder.'),
                $this->dl('B', '何《なに》か 心配《しんぱい》な ことが あるんですか。', 'Nani ka shinpai na koto ga aru n desu ka.', 'Ada yang mengkhawatirkan?', 'Is something worrying you?'),
                $this->dl('A', 'ゲームばかり しないで ほしいんですが…。', 'Geemu bakari shinaide hoshii n desu ga...', 'Saya ingin dia tidak main game terus...', 'I wish he would not play games all the time...'),
                $this->dl('B', 'うちも 同《おな》じです。', 'Uchi mo onaji desu.', 'Di rumah saya juga sama.', 'It is the same at my house.'),
            ],
            5 => [
                $this->dl('A', '見《み》て！このケーキ、おいしそうですね。', 'Mite! Kono keeki, oishisou desu ne.', 'Lihat! Kue ini kelihatannya enak.', 'Look! This cake looks delicious.'),
                $this->dl('B', '本当《ほんとう》！でも、その 皿《さら》は テーブルから 落《お》ちそうですよ。', 'Hontou! Demo, sono sara wa teeburu kara ochisou desu yo.', 'Benar! Tapi piring itu sepertinya mau jatuh dari meja.', 'Really! But that plate looks like it is about to fall off the table.'),
                $this->dl('A', 'あっ、本当《ほんとう》だ。ありがとう。', 'A, hontou da. Arigatou.', 'Oh, benar. Terima kasih.', 'Oh, you are right. Thanks.'),
            ],
            6 => [
                $this->dl('A', 'あの 映画《えいが》、見《み》に 行《い》きませんか。', 'Ano eiga, mi ni ikimasen ka.', 'Mau menonton film itu?', 'Shall we go and see that movie?'),
                $this->dl('B', 'あまり おもしろくなさそうですね。', 'Amari omoshirokunasasou desu ne.', 'Sepertinya kurang menarik.', 'It does not look very interesting.'),
                $this->dl('A', 'そうですか。今日《きょう》は 雨《あめ》も やみそうも ありませんし、うちで DVDを 見《み》ましょうか。', 'Sou desu ka. Kyou wa ame mo yamisou mo arimasen shi, uchi de diibuidii o mimashou ka.', 'Begitu ya. Hujan juga sepertinya belum akan reda, bagaimana kalau menonton DVD di rumah?', 'I see. It does not look like the rain will stop either, so shall we watch a DVD at home?'),
                $this->dl('B', 'いいですね。', 'Ii desu ne.', 'Boleh.', 'Sounds good.'),
            ],
            7 => [
                $this->dl('A', '会議《かいぎ》が 終《お》わった あと、ちょっと 時間《じかん》 ありますか。', 'Kaigi ga owatta ato, chotto jikan arimasu ka.', 'Setelah rapat selesai, apakah Anda ada waktu sebentar?', 'After the meeting is over, do you have a little time?'),
                $this->dl('B', 'ええ、大丈夫《だいじょうぶ》です。', 'Ee, daijoubu desu.', 'Ya, tidak masalah.', 'Yes, that is fine.'),
                $this->dl('A', '昼《ひる》ごはんを 食《た》べた あと、一緒《いっしょ》に 資料《しりょう》を 確認《かくにん》しませんか。', 'Hirugohan o tabeta ato, issho ni shiryou o kakunin shimasen ka.', 'Setelah makan siang, mau memeriksa dokumen bersama?', 'After lunch, shall we check the materials together?'),
                $this->dl('B', 'いいですよ。', 'Ii desu yo.', 'Boleh.', 'Sure.'),
            ],
        ];
    }

    protected function cards(): array
    {
        return [
            $this->card(
                'V(さ)せて もらえませんか／いただけませんか (meminta izin)',
                'V(さ)せて もらえませんか／いただけませんか (asking permission)',
                'V(さ)せて ＋ もらえませんか／いただけませんか／もらえないでしょうか／いただけないでしょうか',
                'Dipakai untuk meminta izin melakukan sesuatu. Kata kerja diubah ke bentuk kausatif-て (〜させて) lalu diikuti もらえませんか atau いただけませんか. いただけませんか lebih sopan daripada もらえませんか, dan いただけないでしょうか lebih sopan lagi dibanding いただけませんか.',
                'Used to ask for permission to do something. The verb is put into the causative て-form (〜させて) followed by もらえませんか or いただけませんか. いただけませんか is politer than もらえませんか, and いただけないでしょうか is politer still.',
                [
                    'Kelompok I: bentuk ない dikurangi ない + せて (書《か》かせて). Kelompok II: bentuk ない dikurangi ない + させて (見《み》させて). する → させて, 来《く》る → 来《こ》させて.',
                    'Pembicara-lah yang melakukan tindakannya; lawan bicara hanya memberi izin.',
                ],
                [
                    'Group I: ない form minus ない + せて (書《か》かせて). Group II: ない form minus ない + させて (見《み》させて). する → させて, 来《く》る → 来《こ》させて.',
                    'The speaker is the one who does the action; the listener only gives permission.',
                ],
                [
                    $this->ex('すみません、ここで 写真《しゃしん》を 撮《と》らせて もらえませんか。', 'Sumimasen, koko de shashin o torasete moraemasen ka.', 'Maaf, bolehkah saya mengambil foto di sini?', 'Excuse me, may I take a photo here?'),
                    $this->ex('明日《あした》、早《はや》めに 帰《かえ》らせて いただけませんか。', 'Ashita, hayame ni kaerasete itadakemasen ka.', 'Bolehkah saya pulang lebih awal besok?', 'Could I be allowed to leave a bit early tomorrow?'),
                    $this->ex('来月《らいげつ》の 会議《かいぎ》に 参加《さんか》させて いただけないでしょうか。', 'Raigetsu no kaigi ni sanka sasete itadakenai deshouka.', 'Mohon izin agar saya dapat ikut dalam rapat bulan depan.', 'I wonder if I might be allowed to take part in next month\'s meeting.'),
                ]
            ),

            $this->card(
                'Vる／Vない ことにする・ことにしている (keputusan dan kebiasaan)',
                'Vる／Vない ことにする・ことにしている (decisions and rules one keeps)',
                'V 辞書形／V ない形 ＋ ことにする／ことにしている',
                '「V する／V しない ことにする」 menyatakan keputusan untuk melakukan atau tidak melakukan V (keputusan yang diambil oleh pembicara). 「V する／V しない ことにしている」 menyatakan kebiasaan atau aturan pribadi yang sudah diputuskan lebih dulu dan terus dijalankan.',
                '「V する／V しない ことにする」 expresses a decision to do or not to do V (made by the speaker). 「V する／V しない ことにしている」 expresses a habit or personal rule one decided on earlier and keeps following.',
                [
                    'ことにした = sudah memutuskan (satu kali); ことにしている = terus berpegang pada keputusan itu.',
                    'Bentuk negatif: Vない ことにする (memutuskan untuk tidak ~).',
                ],
                [
                    'ことにした = decided (a one-time decision); ことにしている = keeps to that decision.',
                    'Negative: Vない ことにする (decide not to ~).',
                ],
                [
                    $this->ex('来月《らいげつ》から ジムに 通《かよ》う ことに しました。', 'Raigetsu kara jimu ni kayou koto ni shimashita.', 'Saya memutuskan untuk berlatih di pusat kebugaran mulai bulan depan.', 'I have decided to go to the gym from next month.'),
                    $this->ex('無理《むり》な 計画《けいかく》なので、参加《さんか》しない ことに します。', 'Muri na keikaku nanode, sanka shinai koto ni shimasu.', 'Karena rencananya mustahil, saya putuskan untuk tidak ikut.', 'The plan is unrealistic, so I will not take part.'),
                    $this->ex('夜《よる》は 食《た》べ 過《す》ぎないように、八時《はちじ》までに 食事《しょくじ》を 終《お》える ことに している。', 'Yoru wa tabesuginai you ni, hachiji made ni shokuji o oeru koto ni shite iru.', 'Agar tidak makan berlebihan malam hari, saya membiasakan selesai makan sebelum jam delapan.', 'So as not to overeat at night, I make it a rule to finish dinner by eight.'),
                ]
            ),

            $this->card(
                'Vる／Vない ことになる・ことになっている (ketetapan dan peraturan)',
                'Vる／Vない ことになる・ことになっている (arrangements and rules)',
                'V 辞書形／V ない形 ＋ ことになる／ことになっている',
                '「ことになる」 menyatakan bahwa sesuatu telah diputuskan atau terjadi (hasil keputusan pihak lain atau keadaan). 「ことにする」 menekankan orang yang memutuskan, sedangkan 「ことになる」 menekankan peristiwa atau hasilnya. 「ことになっている」 menyatakan sesuatu telah dijadwalkan atau sudah menjadi aturan.',
                '「ことになる」 indicates that something has been decided or has come about (the result of someone else\'s decision or of circumstances). 「ことにする」 focuses on who made the decision, while 「ことになる」 focuses on the event or result. 「ことになっている」 indicates something has been scheduled or established as a rule.',
                [
                    'Walaupun keputusannya dibuat sendiri, ことになりました bisa dipakai untuk menyiratkan sikap rendah hati atau menyamarkan keputusan itu.',
                    'ことになっている sering dipakai untuk aturan umum: 〜しない ことに なっている (menurut aturan tidak boleh).',
                ],
                [
                    'Even when the decision was one\'s own, ことになりました can be used to play it down and sound modest.',
                    'ことになっている is often used for general rules: 〜しない ことに なっている (it is a rule not to).',
                ],
                [
                    $this->ex('来年《らいねん》、大阪《おおさか》の 支店《してん》で 働《はたら》く ことに なりました。', 'Rainen, Oosaka no shiten de hataraku koto ni narimashita.', 'Tahun depan saya akan ditugaskan bekerja di cabang Osaka.', 'It has been decided that I will work at the Osaka branch next year.'),
                    $this->ex('この 部屋《へや》では 飲食《いんしょく》しない ことに なって います。', 'Kono heya de wa inshoku shinai koto ni natte imasu.', 'Menurut aturan, di ruangan ini tidak boleh makan dan minum.', 'It is a rule that eating and drinking are not allowed in this room.'),
                    $this->ex('試験《しけん》は 午前《ごぜん》十時《じゅうじ》に 始《はじ》まる ことに なって いる。', 'Shiken wa gozen juuji ni hajimaru koto ni natte iru.', 'Ujian dijadwalkan mulai pukul sepuluh pagi.', 'The exam is scheduled to start at ten in the morning.'),
                ]
            ),

            $this->card(
                'Vて ほしい／Vないで ほしい (harapan kepada orang lain)',
                'Vて ほしい／Vないで ほしい (wanting someone to do something)',
                'V て形 ＋ ほしい／V ない形 (〜ないで) ＋ ほしい',
                '「N に V て ほしい」 menyatakan keinginan agar orang lain (N) melakukan V; negatifnya 「V ないで ほしい」 (berharap orang itu tidak melakukan V). Kalau N sudah jelas, に boleh dihilangkan. Untuk hal selain tindakan orang, が dipakai menggantikan に: 「N が V て ほしい」 (berharap keadaan N terjadi).',
                '「N に V て ほしい」 expresses a wish that someone else (N) would do V; the negative 「V ないで ほしい」 means wishing they would not. When N is known, に can be omitted. For matters other than people\'s actions, が replaces に: 「N が V て ほしい」 (wishing for N to happen).',
                [
                    'Jika ditujukan langsung kepada lawan bicara, terdengar terlalu langsung; biasanya dilembutkan dengan のですが／んですが.',
                    'Untuk diri sendiri, pakai 〜たい; 〜てほしい hanya untuk keinginan terhadap orang lain atau keadaan.',
                ],
                [
                    'Said directly to the listener it sounds too blunt, so it is usually softened with のですが／んですが.',
                    'For yourself use 〜たい; 〜てほしい is only for wishes about other people or situations.',
                ],
                [
                    $this->ex('子《こ》どもには 自分《じぶん》で 考《かんが》える 人《ひと》に なって ほしい。', 'Kodomo ni wa jibun de kangaeru hito ni natte hoshii.', 'Saya ingin anak saya menjadi orang yang berpikir sendiri.', 'I want my child to become a person who thinks for themselves.'),
                    $this->ex('夜中《よなか》に 大《おお》きい 音《おと》を 出《だ》さないで ほしい。', 'Yonaka ni ookii oto o dasanai de hoshii.', 'Saya berharap tidak ada yang membuat suara keras di tengah malam.', 'I wish people would not make loud noises in the middle of the night.'),
                    $this->ex('すみません、ちょっと 手伝《てつだ》って ほしいんですが。', 'Sumimasen, chotto tetsudatte hoshii n desu ga.', 'Maaf, saya ingin Anda membantu sebentar.', 'Excuse me, I would like you to help me for a moment.'),
                    $this->ex('早《はや》く 暑《あつ》さが 終《お》わって ほしい。', 'Hayaku atsusa ga owatte hoshii.', 'Semoga panas ini cepat berakhir.', 'I hope the heat ends soon.'),
                ]
            ),

            $this->card(
                'Vそうだ／Aそうだ (dugaan dan kesan)',
                'Vそうだ／Aそうだ (prediction and appearance)',
                'Vます形(〜ます) ＋ そうだ／そうな N／そうに V ・ いA(〜い)／なA ＋ そうだ',
                '〜そうだ berbeda makna menurut kata yang dipasangi. Dengan kata kerja (Vます-stem + そうだ), pembicara memperkirakan bahwa V hampir pasti terjadi atau ada tanda-tandanya. Dengan kata sifat (A そうだ), artinya sesuatu tampak A dari luar. Saat menerangkan kata benda berbentuk そうな N, dan saat menerangkan kata kerja berbentuk そうに V.',
                '〜そうだ means different things depending on what it follows. After a verb (V ます-stem + そうだ), the speaker predicts that V is very likely or that there are signs it will happen. After an adjective (A そうだ), it means something appears to be A from the outside. Before a noun it is そうな N, and before a verb it is そうに V.',
                [
                    'い-adjective: buang い (おいしい → おいしそう). Na-adjective: buang な/だ (元気《げんき》 → 元気《げんき》そう). Pengecualian: いい → よさそう.',
                    'Berbeda dengan 〜そうだ "katanya" (kabar yang didengar) yang memakai bentuk biasa.',
                ],
                [
                    'い-adjective: drop い (おいしい → おいしそう). Na-adjective: drop な/だ (元気《げんき》 → 元気《げんき》そう). Exception: いい → よさそう.',
                    'Not to be confused with 〜そうだ "I heard that" (hearsay), which attaches to the plain form.',
                ],
                [
                    $this->ex('今《いま》にも 雨《あめ》が 降《ふ》りそうです。', 'Ima ni mo ame ga furisou desu.', 'Sepertinya hujan akan turun sebentar lagi.', 'It looks like it will rain any moment now.'),
                    $this->ex('この スープは 辛《から》そうですね。', 'Kono suupu wa karasou desu ne.', 'Sup ini kelihatannya pedas, ya.', 'This soup looks spicy, doesn\'t it?'),
                    $this->ex('荷物《にもつ》が 落《お》ちそうだから、気《き》を つけて。', 'Nimotsu ga ochisou dakara, ki o tsukete.', 'Barangnya sepertinya mau jatuh, hati-hati.', 'The luggage looks like it is about to fall, so be careful.'),
                    $this->ex('子《こ》どもが 嬉《うれ》しそうに 走《はし》って いる。', 'Kodomo ga ureshisou ni hashitte iru.', 'Anak itu berlari dengan tampak gembira.', 'The child is running along looking happy.'),
                ]
            ),

            $this->card(
                'なさそう／Vそうも ない (bentuk negatif 〜そうだ)',
                'なさそう／Vそうも ない (negative of 〜そうだ)',
                'いA(〜く)・なA(〜では)・N(〜では) ＋ なさそう ・ Vます形 ＋ そうも ない',
                'Bentuk negatif 「A／N そうだ」 bukan 「〜ないそうだ」 melainkan 「〜なさそうだ」. Artinya sesuatu tampaknya tidak A atau dikira bukan A. Bentuk negatif 「V そうだ」 adalah 「V そうも ない」 (atau そうに ない), yang menyatakan perkiraan bahwa V kemungkinan besar tidak akan terjadi.',
                'The negative of 「A／N そうだ」 is not 「〜ないそうだ」 but 「〜なさそうだ」. It means the thing does not appear to be A or is not thought to be A. The negative of 「V そうだ」 is 「V そうも ない」, which predicts that V will probably not happen.',
                [
                    'い-adjective: い → くなさそう (おもしろくなさそう). Na-adjective／kata benda: ではなさそう／じゃなさそう.',
                    'Kata kerja: Vます-stem + そうも ありません／そうに ありません (ありません adalah bentuk sopan ない).',
                ],
                [
                    'い-adjective: い → くなさそう (おもしろくなさそう). Na-adjective／noun: ではなさそう／じゃなさそう.',
                    'Verb: ます-stem + そうも ありません／そうに ありません (ありません is the polite ない).',
                ],
                [
                    $this->ex('この 店《みせ》は あまり 人気《にんき》が なさそうだ。', 'Kono mise wa amari ninki ga nasasou da.', 'Toko ini sepertinya tidak begitu populer.', 'This shop does not look very popular.'),
                    $this->ex('彼女《かのじょ》は あまり 元気《げんき》では なさそうでした。', 'Kanojo wa amari genki de wa nasasou deshita.', 'Dia tampaknya kurang sehat.', 'She did not seem very well.'),
                    $this->ex('今日《きょう》は 雨《あめ》が やみそうも ありません。', 'Kyou wa ame ga yamisou mo arimasen.', 'Sepertinya hari ini hujan tidak akan berhenti.', 'It does not look like the rain will stop today.'),
                    $this->ex('この 仕事《しごと》は 今週中《こんしゅうちゅう》に 終《お》わりそうも ない。', 'Kono shigoto wa konshuuchuu ni owarisou mo nai.', 'Pekerjaan ini sepertinya tidak akan selesai minggu ini.', 'This work does not look like it will be finished this week.'),
                ]
            ),

            $this->card(
                'Vた あと、〜 (setelah ~)',
                'Vた あと、〜 (after ~)',
                'Vた形 ＋ あと(で)、〜',
                '「V た あと、…」 menunjukkan bahwa keadaan atau peristiwa di bagian … terjadi setelah V. Boleh memakai あと atau あとで. Kalau bagian … memuat kata kerja keadaan seperti いる atau ある, あとで terdengar janggal; pakai あと saja.',
                '「V た あと、…」 indicates that what follows (…) happens after V. Both あと and あとで can be used. When … contains state verbs like いる or ある, あとで sounds awkward; use あと only.',
                [
                    'Dengan kata benda: Nの あとで／あと (会議《かいぎ》の あとで).',
                    'Berbeda dengan 〜てから yang menekankan urutan langsung; 〜たあと lebih longgar secara waktu.',
                ],
                [
                    'With nouns: N の あとで／あと (会議《かいぎ》の あとで).',
                    'Different from 〜てから, which stresses direct sequence; 〜たあと is looser in time.',
                ],
                [
                    $this->ex('授業《じゅぎょう》が 終《お》わった あと、図書館《としょかん》で 勉強《べんきょう》した。', 'Jugyou ga owatta ato, toshokan de benkyou shita.', 'Setelah kelas selesai, saya belajar di perpustakaan.', 'After class finished, I studied in the library.'),
                    $this->ex('食事《しょくじ》を した あとで、薬《くすり》を 飲《の》んで ください。', 'Shokuji o shita ato de, kusuri o nonde kudasai.', 'Silakan minum obat setelah makan.', 'Please take the medicine after eating.'),
                    $this->ex('日曜日《にちようび》は 朝《あさ》ごはんを 食《た》べた あと、どこへも 行《い》かず 家《いえ》に いました。', 'Nichiyoubi wa asagohan o tabeta ato, doko e mo ikazu ie ni imashita.', 'Hari Minggu, setelah sarapan saya tidak ke mana-mana dan tinggal di rumah.', 'On Sunday, after breakfast, I did not go anywhere and stayed home.'),
                ]
            ),
        ];
    }
}
