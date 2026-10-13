<script setup>
// Referensi Tata Bahasa N3 (Pelajaran 1–12), statis dan berdiri sendiri.
// Satu komponen, empat bagian lewat prop `part`:
//   patterns — ringkasan semua pola per pelajaran
//   compare  — perbandingan pola yang mudah tertukar
//   forms    — cara membentuk (kausatif-pasif, bentuk lisan, bentuk maksud, dst.)
//   phrases  — ungkapan sopan, bahasa bisnis, dan kata tunjuk
// Teks dua bahasa ditulis { id, en }; furigana memakai 漢字《かんじ》 dan dirender RubyText.
// Nama buku sumber sengaja tidak dicantumkan.
defineProps({
  part: { type: String, required: true },
})

const { t, locale } = useI18n()

const tr = text => (locale.value === 'en' ? text.en : text.id)

const b = (id, en) => ({ id, en })
const ex = (ja, id, en) => ({ ja, id, en })

// ---------------------------------------------------------------------
// patterns — ringkasan pola per pelajaran
// baris: [pola, fungsi, contoh]
// ---------------------------------------------------------------------
const patternCards = [
  {
    title: b('Pelajaran 1', 'Lesson 1'),
    rows: [
      ['Vて もらえませんか／いただけませんか', b('Permintaan sopan', 'Polite request'), ex('この 書類《しょるい》を コピーして もらえませんか。', 'Bisakah Anda memfotokopi dokumen ini?', 'Could you copy this document?')],
      ['Nの ようだ／ような／ように', b('Perumpamaan, contoh', 'Simile, example'), ex('あの 家《いえ》は 宮殿《きゅうでん》のようだ。', 'Rumah itu seperti istana.', 'That house is like a palace.')],
      ['Vる こと＋は／が／を', b('Menjadikan kata kerja kata benda', 'Nominalising a verb'), ex('毎日《まいにち》 歩《ある》く ことは 体《からだ》に いい。', 'Berjalan kaki setiap hari baik untuk tubuh.', 'Walking every day is good for you.')],
      ['N１を N２と 言う', b('Menyebut nama sesuatu', 'Calling something by name'), ex('お米《こめ》を 炊《た》いた ものを ご飯《はん》と 言《い》う。', 'Beras yang ditanak disebut gohan.', 'Cooked rice is called gohan.')],
      ['N１という N２', b('Memperkenalkan nama／judul', 'Introducing a name／title'), ex('「桜《さくら》」という 歌《うた》を 知《し》って いますか。', 'Tahukah Anda lagu berjudul "Sakura"?', 'Do you know the song "Sakura"?')],
      ['疑問詞《ぎもんし》＋〜ても', b('Apa pun keadaannya', 'No matter ...'), ex('どんなに 忙《いそが》しくても、朝《あさ》ごはんは 食《た》べます。', 'Sesibuk apa pun, saya sarapan.', 'However busy I am, I eat breakfast.')],
      ['N１じゃなくて、N２', b('Menyangkal lalu mengoreksi', 'Negating and replacing'), ex('これは 塩《しお》じゃなくて、砂糖《さとう》です。', 'Ini bukan garam, melainkan gula.', 'This is not salt but sugar.')],
      ['…のだ／…のではない', b('Alasan, dugaan, sangkalan', 'Reason, conclusion, negation'), ex('電車《でんしゃ》が 止《と》まって いました。それで、遅《おく》れたのです。', 'Kereta berhenti, itu sebabnya saya terlambat.', 'The train had stopped; that is why I was late.')],
      ['何＋助数詞＋も', b('Jumlah yang banyak', 'A large number'), ex('机《つくえ》の 上《うえ》に 本《ほん》が 何冊《なんさつ》も 積《つ》んで ある。', 'Di atas meja bertumpuk banyak buku.', 'Many books are stacked on the desk.')],
    ],
  },
  {
    title: b('Pelajaran 2', 'Lesson 2'),
    rows: [
      ['Vたら、〜た', b('Hasil dan penemuan', 'Result and discovery'), ex('駅《えき》に 着《つ》いたら、友達《ともだち》が 待《ま》って いた。', 'Begitu sampai di stasiun, teman saya sudah menunggu.', 'When I got to the station, my friend was waiting.')],
      ['Nというのは〜のことだ／〜ということだ', b('Menjelaskan istilah', 'Explaining a term'), ex('「早退《そうたい》」というのは 決《き》められた 時間《じかん》より 早《はや》く 帰《かえ》る ことです。', '"Sōtai" berarti pulang lebih awal.', '"Soutai" means leaving earlier than set.')],
      ['…という＋N (話・うわさ・考え…)', b('Isi ucapan／pikiran', 'Content of a statement／thought'), ex('明日《あした》は 休《やす》みだと いう お知《し》らせが 届《とど》いた。', 'Datang pemberitahuan bahwa besok libur.', 'A notice came saying tomorrow is a holiday.')],
      ['〜ように 言う／注意する／伝える／頼む', b('Menyampaikan perintah／permintaan', 'Reporting instructions／requests'), ex('医者《いしゃ》に 甘《あま》い 物《もの》を 食《た》べないように 言《い》われた。', 'Dokter menyuruh saya tidak makan yang manis.', 'The doctor told me not to eat sweets.')],
      ['Nみたいだ／みたいな／みたいに', b('Perumpamaan lisan', 'Casual simile'), ex('私《わたし》は 彼《かれ》みたいな 優《やさ》しい 人《ひと》に なりたい。', 'Saya ingin jadi orang baik seperti dia.', 'I want to be a kind person like him.')],
      ['お忙《いそが》しい ところ', b('Ungkapan sopan "di saat ~"', 'Polite "at a time when ~"'), ex('お休《やす》みの ところ、失礼《しつれい》します。', 'Maaf mengganggu waktu istirahat Anda.', 'Sorry to disturb your time off.')],
    ],
  },
  {
    title: b('Pelajaran 3', 'Lesson 3'),
    rows: [
      ['V(さ)せて もらえませんか／いただけませんか', b('Meminta izin', 'Asking permission'), ex('ここで 写真《しゃしん》を 撮《と》らせて もらえませんか。', 'Bolehkah saya mengambil foto di sini?', 'May I take a photo here?')],
      ['Vる／Vない ことにする・ことにしている', b('Keputusan dan kebiasaan', 'Decisions and rules one keeps'), ex('来月《らいげつ》から ジムに 通《かよ》う ことに しました。', 'Saya memutuskan berlatih di gym mulai bulan depan.', 'I decided to go to the gym from next month.')],
      ['Vる／Vない ことになる・ことになっている', b('Ketetapan dan peraturan', 'Arrangements and rules'), ex('この 部屋《へや》では 飲食《いんしょく》しない ことに なって います。', 'Di ruangan ini tidak boleh makan dan minum.', 'Eating and drinking are not allowed here.')],
      ['Vて ほしい／Vないで ほしい', b('Harapan kepada orang lain', 'Wanting someone to do something'), ex('夜中《よなか》に 大《おお》きい 音《おと》を 出《だ》さないで ほしい。', 'Saya harap tidak ada suara keras tengah malam.', 'I wish people would not make noise at night.')],
      ['Vそうだ／Aそうだ', b('Dugaan dan kesan', 'Prediction and appearance'), ex('今《いま》にも 雨《あめ》が 降《ふ》りそうです。', 'Sepertinya hujan sebentar lagi turun.', 'It looks like rain any moment now.')],
      ['なさそう／Vそうも ない', b('Negatif dari 〜そうだ', 'Negative of 〜そうだ'), ex('この 仕事《しごと》は 今週中《こんしゅうちゅう》に 終《お》わりそうも ない。', 'Pekerjaan ini tampaknya tak selesai minggu ini.', 'This work does not look finishable this week.')],
      ['Vた あと(で)、〜', b('Setelah ~', 'After ~'), ex('授業《じゅぎょう》が 終《お》わった あと、図書館《としょかん》で 勉強《べんきょう》した。', 'Setelah kelas selesai, saya belajar di perpustakaan.', 'After class I studied in the library.')],
    ],
  },
  {
    title: b('Pelajaran 4', 'Lesson 4'),
    rows: [
      ['…ということだ／とのことです', b('Kalimat tidak langsung', 'Reported speech'), ex('ニュースに よると、明日《あした》は 大雪《おおゆき》に なると いう ことです。', 'Menurut berita, besok salju lebat.', 'According to the news, heavy snow tomorrow.')],
      ['…の／…の？', b('Bentuk akrab のですか', 'Casual のですか'), ex('どうしたの？……頭《あたま》が 痛《いた》いの。', 'Ada apa? ……Kepalaku sakit.', 'What is wrong? ...I have a headache.')],
      ['〜ちゃう・〜とく・〜てる', b('Singkatan lisan', 'Spoken contractions'), ex('宿題《しゅくだい》を 忘《わす》れちゃった。', 'Aduh, PR-nya terlupa.', 'Oh no, I forgot my homework.')],
      ['〜(さ)せられる・〜される', b('Kausatif-pasif (dipaksa)', 'Causative-passive (made to)'), ex('子《こ》どもの とき、毎日《まいにち》 ピアノを 練習《れんしゅう》させられた。', 'Waktu kecil saya dipaksa berlatih piano.', 'As a child I was made to practise piano daily.')],
      ['〜である', b('Gaya tulisan formal', 'Formal written style'), ex('この 問題《もんだい》は 非常《ひじょう》に 重要《じゅうよう》である。', 'Masalah ini sangat penting.', 'This problem is extremely important.')],
      ['Vます形・いA(〜く)・なA／N(〜で)', b('Bentuk penghubung', 'Suspended form'), ex('この 部屋《へや》は 広《ひろ》く、明《あか》るい。', 'Ruangan ini luas dan terang.', 'This room is spacious and bright.')],
      ['〜(た)がる／〜(た)がっている', b('Perasaan orang lain', 'Other people\'s feelings'), ex('妹《いもうと》は 新《あたら》しい 靴《くつ》を 欲《ほ》しがって いる。', 'Adik tampak menginginkan sepatu baru.', 'My sister seems to want new shoes.')],
      ['…こと・…ということ', b('Membuat kalimat jadi benda', 'Nominalising a sentence'), ex('この 薬《くすり》が 安全《あんぜん》な ことが わかった。', 'Terbukti obat ini aman.', 'It became clear that this medicine is safe.')],
      ['N１の N２', b('Apposisi', 'Apposition'), ex('社長《しゃちょう》の 山田《やまだ》さんを ご紹介《しょうかい》します。', 'Saya perkenalkan Direktur kami, Pak Yamada.', 'Let me introduce our president, Mr. Yamada.')],
      ['Vましたら／Vまして', b('Bentuk sopan たら／て', 'Polite たら／て'), ex('準備《じゅんび》が できましたら、ご連絡《れんらく》します。', 'Setelah siap, saya akan menghubungi Anda.', 'I will contact you once we are ready.')],
    ],
  },
  {
    title: b('Pelajaran 5', 'Lesson 5'),
    rows: [
      ['あ〜・そ〜 (percakapan)', b('Kata tunjuk menurut konteks', 'Contextual demonstratives'), ex('あの 店《みせ》、もう 行《い》った？', 'Toko itu, sudah pernah ke sana?', 'Have you been to that shop yet?')],
      ['そ〜 (tulisan)', b('Merujuk kalimat sebelumnya', 'Pointing back in writing'), ex('新《あたら》しい 法律《ほうりつ》が 決《き》まりました。それは 来年《らいねん》から 始《はじ》まります。', 'Itu akan berlaku mulai tahun depan.', 'It takes effect next year.')],
      ['…んじゃない？', b('Dugaan lisan', 'Spoken guess'), ex('顔色《かおいろ》が 悪《わる》いね。熱《ねつ》が あるんじゃない？', 'Wajahmu pucat. Jangan-jangan demam?', 'You look pale. Do you have a fever?')],
      ['Vた ところに／で', b('Tempat setelah bergerak', 'Place reached after moving'), ex('階段《かいだん》を 降《お》りた ところに 受付《うけつけ》が あります。', 'Resepsionis ada di bawah tangga.', 'Reception is at the bottom of the stairs.')],
      ['Vよう(意向形)と する／しない', b('Hampir／berusaha／tak mau', 'About to／trying／refusing'), ex('電車《でんしゃ》に 乗《の》ろうと したら、ドアが 閉《し》まった。', 'Saat hendak naik kereta, pintu menutup.', 'As I tried to board, the doors closed.')],
      ['…のだろうか', b('Bertanya pada diri sendiri', 'Wondering'), ex('どうすれば 日本語《にほんご》が 上手《じょうず》に なるのだろうか。', 'Bagaimana supaya mahir bahasa Jepang, ya?', 'I wonder how to get good at Japanese.')],
      ['Nとの／での／からの／までの／への N', b('Partikel + の menerangkan benda', 'Particle + の modifying a noun'), ex('駅《えき》までの 道《みち》を 教《おし》えて ください。', 'Tolong beri tahu jalan ke stasiun.', 'Please tell me the way to the station.')],
      ['…だろう・…だろうと思う', b('Dugaan', 'Conjecture'), ex('明日《あした》は 晴《は》れるだろう。', 'Besok mungkin cerah.', 'It will probably be sunny tomorrow.')],
      ['…から、〜てください', b('Informasi pendukung permintaan', 'Information before a request'), ex('すぐ 戻《もど》りますから、ここで 待《ま》って いて ください。', 'Saya segera kembali, tolong tunggu di sini.', 'I will be right back, so please wait here.')],
      ['が／の', b('Subjek anak kalimat penerang', 'Subject in a modifying clause'), ex('母《はは》が／の 作《つく》った スープは 温《あたた》かかった。', 'Sup buatan ibu terasa hangat.', 'The soup my mother made was warm.')],
    ],
  },
  {
    title: b('Pelajaran 6', 'Lesson 6'),
    rows: [
      ['…て／…って', b('Kutipan dalam percakapan', 'Quoting in speech'), ex('ミラーさんは 明日《あした》 来《こ》られないって 言《い》って いました。', 'Pak Miller bilang besok tidak bisa datang.', 'Mr. Miller said he cannot come tomorrow.')],
      ['Xって…', b('Topik: menanyakan／menjelaskan X', 'Topic: asking about／describing X'), ex('ねえ、「ぬか漬《づ》け」って 何《なん》？', 'Eh, "nukazuke" itu apa?', 'Say, what is "nukazuke"?')],
      ['Vる つもりはない', b('Sama sekali tidak berniat', 'No intention at all'), ex('会社《かいしゃ》を 辞《や》める つもりは ありません。', 'Saya tidak berniat berhenti dari perusahaan.', 'I have no intention of quitting the company.')],
      ['Vる／Vない つもりだった', b('Niat lampau (sering berubah)', 'Past intention (often changed)'), ex('昨日《きのう》は 早《はや》く 寝《ね》る つもりでしたが、つい 遅《おそ》くまで 起《お》きて いました。', 'Kemarin saya berniat tidur cepat, tetapi tanpa sadar tetap terjaga sampai larut.', 'I meant to go to bed early yesterday, but I ended up staying up late.')],
      ['Vた／Vている つもり', b('Merasa sudah ~', 'Thinking one has ~'), ex('鍵《かぎ》は かけた つもりでしたが、かかって いませんでした。', 'Saya kira sudah mengunci, tetapi ternyata tidak terkunci.', 'I thought I had locked it, but it was not locked.')],
      ['Vて ばかりいる／Nばかり Vている', b('Hanya ~ terus (bernada kritik)', 'Does nothing but ~ (critical)'), ex('息子《むすこ》は 休《やす》みの 日《ひ》は 寝《ね》て ばかり いる。', 'Anak laki-laki saya di hari libur hanya tidur terus.', 'On his days off my son does nothing but sleep.')],
      ['…とか…とか', b('Menyebut beberapa contoh', 'Listing examples'), ex('休《やす》みの 日《ひ》は 映画《えいが》を 見《み》に 行《い》くとか、友達《ともだち》と 食事《しょくじ》するとか して います。', 'Di hari libur saya misalnya menonton film atau makan bersama teman.', 'On my days off I do things like going to the movies or eating out with friends.')],
      ['Vて くる', b('Munculnya keadaan baru', 'A new condition arises'), ex('雨《あめ》が 降《ふ》って きました。傘《かさ》を 出《だ》しましょう。', 'Hujan mulai turun. Mari keluarkan payung.', 'It has started to rain. Let us take out the umbrella.')],
      ['Vて くる／Vて いく', b('Arah gerakan', 'Direction of motion'), ex('遠《とお》くから 友達《ともだち》が 走《はし》って きました。', 'Dari kejauhan teman saya berlari menghampiri.', 'A friend came running from far away.')],
      ['こ〜', b('Menunjuk isi yang akan disebut', 'Pointing ahead to what follows'), ex('新聞《しんぶん》に こんな 記事《きじ》が ありました。この 町《まち》に 大《おお》きな 図書館《としょかん》が できるそうだ。', 'Ada berita seperti ini di koran: katanya akan dibangun perpustakaan besar di kota ini.', 'There was an article like this in the paper: a large library is going to be built in this town.')],
    ],
  },
  {
    title: b('Pelajaran 7', 'Lesson 7'),
    rows: [
      ['Vなくては ならない／いけない', b('Keharusan; ～なくても かまわない = tak perlu', 'Obligation; ～なくても かまわない = not necessary'), ex('明日《あした》までに 報告書《ほうこくしょ》を 出《だ》さなくては ならない。', 'Laporan harus diserahkan paling lambat besok.', 'The report has to be handed in by tomorrow.')],
      ['Vなくちゃ／Vなきゃ', b('Bentuk lisan keharusan', 'Spoken form of obligation'), ex('もう こんな 時間《じかん》！ 帰《かえ》らなくちゃ。', 'Sudah selarut ini! Aku harus pulang.', 'Look at the time! I have to go home.')],
      ['Nだけだ／Vるだけでいい', b('Hanya ~; cukup ~', 'Only ~; ~ is enough'), ex('財布《さいふ》には 千円《せんえん》だけ 入《はい》って います。', 'Di dompet hanya ada seribu yen.', 'There is only one thousand yen in my wallet.')],
      ['…かな', b('Bertanya pada diri sendiri; ajakan lunak', 'Wondering; softened invitation'), ex('明日《あした》は 晴《は》れる かな。', 'Besok cerah tidak ya?', 'I wonder if it will be fine tomorrow.')],
      ['Nなんか', b('Merendahkan／merendah', 'Belittling'), ex('私《わたし》の 料理《りょうり》なんか、人《ひと》に 出《だ》せません。', 'Masakan saya tidak pantas disajikan untuk orang lain.', 'My cooking is not fit to serve to anyone.')],
      ['…なんて', b('Kejutan, kritik, meremehkan', 'Surprise, criticism, disdain'), ex('彼《かれ》が 会社《かいしゃ》を 辞《や》めるなんて、信《しん》じられません。', 'Dia berhenti dari perusahaan? Saya tidak percaya.', 'I cannot believe he is quitting the company.')],
      ['Vi(さ)せる', b('Membuat perasaan orang tergugah', 'Evoking an emotion'), ex('冗談《じょうだん》を 言《い》って、みんなを 笑《わら》わせた。', 'Saya melontarkan lelucon dan membuat semua orang tertawa.', 'I told a joke and made everyone laugh.')],
      ['Vi(さ)せられる', b('Perasaan yang sangat tergugah', 'Strongly evoked emotion'), ex('彼女《かのじょ》の 歌《うた》を 聞《き》いて、感動《かんどう》させられた。', 'Mendengar nyanyiannya, saya sangat terharu.', 'Listening to her sing, I was deeply moved.')],
      ['…なら、…', b('Saran／pertanyaan sesuai topik lawan bicara', 'Advice／question based on the topic the listener raised'), ex('京都《きょうと》へ 行《い》くなら、秋《あき》が おすすめです。', 'Kalau mau ke Kyoto, musim gugur yang saya sarankan.', 'If you are going to Kyoto, I recommend autumn.')],
      ['Vて くれ', b('Perintah／permintaan lugas', 'Blunt request or order'), ex('悪《わる》いが、ちょっと 窓《まど》を 閉《し》めて くれ。', 'Maaf, tolong tutup jendelanya sebentar.', 'Sorry, but shut the window for me, will you.')],
    ],
  },
  {
    title: b('Pelajaran 8', 'Lesson 8'),
    rows: [
      ['Vている／Nの あいだ(に)', b('Selama ~／ketika ~ berlangsung', 'While ~'), ex('子《こ》どもが 寝《ね》て いる あいだ、静《しず》かに 本《ほん》を 読《よ》んで いました。', 'Selama anak tidur, saya membaca buku dengan tenang.', 'While the child was sleeping, I read a book quietly.')],
      ['Nまで／Vるまで・Nまでに／Vるまでに', b('Sampai ~／paling lambat ~', 'Until ~／by ~'), ex('会議《かいぎ》が 始《はじ》まるまで、ロビーで 待《ま》って います。', 'Saya menunggu di lobi sampai rapat dimulai.', 'I will wait in the lobby until the meeting starts.')],
      ['Vた N', b('Penerang kata benda dengan bentuk た', 'た-form noun modifier'), ex('帽子《ぼうし》を かぶった 男《おとこ》の 人《ひと》が 入口《いりぐち》に 立《た》って います。', 'Seorang pria bertopi berdiri di pintu masuk.', 'A man wearing a hat is standing at the entrance.')],
      ['Nによって', b('Berbeda／berubah menurut N', 'Varies depending on N'), ex('国《くに》によって 習慣《しゅうかん》が 違《ちが》います。', 'Kebiasaan berbeda tergantung negara.', 'Customs differ from country to country.')],
      ['Vたまま／Nのまま', b('Dalam keadaan tetap ~', 'Leaving as is'), ex('電気《でんき》を つけた まま 寝《ね》て しまいました。', 'Saya tertidur dengan lampu masih menyala.', 'I fell asleep with the light on.')],
      ['…からだ', b('Alasan di akhir kalimat', 'Stating the reason'), ex('どうして 遅《おく》れたんですか。…電車《でんしゃ》が 止《と》まったからです。', 'Mengapa terlambat? ...Karena kereta berhenti.', 'Why were you late? ...Because the train stopped.')],
      ['髪／目／形 を している', b('Ciri tampilan', 'Describing appearance'), ex('妹《いもうと》は 長《なが》い 髪《かみ》を して います。', 'Adik perempuan saya berambut panjang.', 'My younger sister has long hair.')],
    ],
  },
  {
    title: b('Pelajaran 9', 'Lesson 9'),
    rows: [
      ['お〜です', b('Bentuk hormat dari 〜ている', 'Honorific form of 〜ている'), ex('何《なに》を お探《さが》しですか。', 'Anda sedang mencari apa?', 'What are you looking for?')],
      ['Vても かまわない', b('Izin (lebih formal)', 'Permission (more formal)'), ex('ここで 写真《しゃしん》を 撮《と》っても かまいませんか。', 'Bolehkah saya mengambil foto di sini?', 'May I take photographs here?')],
      ['AはBほど〜ない', b('Tidak seperti B', 'Not as ~ as B'), ex('今年《ことし》の 冬《ふゆ》は 去年《きょねん》ほど 寒《さむ》くない。', 'Musim dingin tahun ini tidak sedingin tahun lalu.', 'This winter is not as cold as last year.')],
      ['Nほど〜はない／いない', b('Tidak ada yang sebanding N', 'Nothing is as ~ as N'), ex('母《はは》の 作《つく》る カレーほど おいしい 料理《りょうり》は ない。', 'Tidak ada masakan seenak kari buatan ibu.', 'No dish is as delicious as the curry my mother makes.')],
      ['…ため(に)／…ためだ', b('Sebab (formal／tulisan)', 'Cause (formal／written)'), ex('台風《たいふう》が 来《き》た ために、新幹線《しんかんせん》が 止《と》まりました。', 'Karena topan datang, Shinkansen berhenti beroperasi.', 'Because a typhoon came, the Shinkansen stopped running.')],
      ['Vたら／Vば、…た(のに)', b('Pengandaian bertentangan dengan fakta', 'Counterfactual'), ex('もっと 早《はや》く 家《いえ》を 出《で》ていれば、電車《でんしゃ》に 間《ま》に 合《あ》ったのに。', 'Kalau saya berangkat lebih awal, saya pasti sempat naik kereta.', 'If I had left home earlier, I would have caught the train.')],
    ],
  },
  {
    title: b('Pelajaran 10', 'Lesson 10'),
    rows: [
      ['…はずだ', b('Keyakinan berdasar alasan', 'Conviction based on grounds'), ex('今《いま》 九時《くじ》だから、駅前《えきまえ》の スーパーは もう 開《あ》いて いる はずだ。', 'Sekarang jam sembilan, jadi supermarket di depan stasiun seharusnya sudah buka.', 'It is nine o\'clock, so the supermarket in front of the station should be open.')],
      ['…はずが／はない', b('Tidak mungkin', 'Impossible, out of the question'), ex('あの 優《やさ》しい 先生《せんせい》が 怒《おこ》る はずは ない。', 'Guru yang lembut itu tidak mungkin marah.', 'That gentle teacher cannot possibly be angry.')],
      ['…はずだった', b('Seharusnya begitu (tetapi tidak)', 'Was supposed to (but did not)'), ex('先週《せんしゅう》には 完成《かんせい》する はずだったのに、まだ 終《お》わって いない。', 'Padahal seharusnya selesai minggu lalu, tetapi belum juga rampung.', 'It was supposed to be finished last week, yet it is not done.')],
      ['…ことが／もある', b('Kadang-kadang terjadi', 'Sometimes happens'), ex('この 道《みち》は 普段《ふだん》 空《す》いて いるが、雨《あめ》の 日《ひ》は 混《こ》む ことが ある。', 'Jalan ini biasanya sepi, tetapi kadang ramai pada hari hujan.', 'This road is usually empty, but it sometimes gets crowded on rainy days.')],
      ['V た結果《けっか》、…／N の結果《けっか》、…', b('Sebagai hasil dari ~', 'As a result of ~'), ex('何度《なんど》も 検査《けんさ》した 結果《けっか》、製品《せいひん》に 問題《もんだい》は ないと わかった。', 'Setelah berkali-kali diperiksa, diketahui produk tidak bermasalah.', 'After repeated inspections, it turned out the product had no problems.')],
      ['V 出《だ》す', b('Mulai mendadak', 'Suddenly begin'), ex('赤《あか》ちゃんが 急《きゅう》に 泣《な》き出《だ》した。', 'Bayi itu tiba-tiba menangis.', 'The baby suddenly burst into tears.')],
      ['V 始《はじ》める・終《お》わる・続《つづ》ける', b('Mulai, selesai, terus', 'Begin, finish, continue'), ex('先月《せんげつ》から 日本語《にほんご》を 習《なら》い始《はじ》めました。', 'Saya mulai belajar bahasa Jepang sejak bulan lalu.', 'I began learning Japanese last month.')],
      ['V 忘《わす》れる・合《あ》う・換《か》える', b('Lupa, saling, mengganti', 'Forget, each other, change'), ex('駅《えき》で 急行《きゅうこう》に 乗《の》り換《か》えます。', 'Di stasiun saya pindah ke kereta ekspres.', 'I change to an express train at the station.')],
      ['…と いう ことに なる', b('Jadi berarti ~', 'That means ~'), ex('毎月《まいつき》 三万円《さんまんえん》ずつ 貯金《ちょきん》すれば、一年《いちねん》で 三十六万円《さんじゅうろくまんえん》 貯《た》まると いう ことに なる。', 'Kalau menabung 30.000 yen tiap bulan, berarti setahun terkumpul 360.000 yen.', 'If you save 30,000 yen a month, that means you will have 360,000 yen in a year.')],
    ],
  },
  {
    title: b('Pelajaran 11', 'Lesson 11'),
    rows: [
      ['〜てくる・〜ていく', b('Perubahan sampai kini／ke depan', 'Change up to now／from now on'), ex('最近《さいきん》、日本語《にほんご》の 勉強《べんきょう》が 楽《たの》しく なって きました。', 'Akhir-akhir ini belajar bahasa Jepang jadi makin menyenangkan.', 'Lately, studying Japanese has become more and more enjoyable.')],
      ['〜たら どう？／いかがですか', b('Saran', 'Suggestion'), ex('分《わ》からない 言葉《ことば》は 辞書《じしょ》で 調《しら》べたら どうですか。', 'Kata yang tidak dimengerti, bagaimana kalau dicari di kamus?', 'Why not look up words you do not know in a dictionary?')],
      ['…より…ほうが…', b('Perbandingan: X lebih ~ daripada Y', 'Comparison: X is more ~ than Y'), ex('電車《でんしゃ》で 行《い》くより、バスで 行《い》く ほうが 安《やす》い。', 'Naik bus lebih murah daripada naik kereta.', 'Going by bus is cheaper than going by train.')],
      ['N らしい', b('Khas, sesuai sifatnya', 'Typical of, befitting'), ex('今日《きょう》は 秋《あき》らしい 天気《てんき》ですね。', 'Cuaca hari ini terasa seperti musim gugur ya.', 'It is typical autumn weather today, is it not?')],
      ['…らしい', b('Kabar yang didengar／dugaan', 'Reported speech／conjecture'), ex('天気予報《てんきよほう》に よると、明日《あした》は 雨《あめ》らしい。', 'Menurut prakiraan cuaca, besok katanya hujan.', 'According to the forecast, it seems it will rain tomorrow.')],
      ['N として', b('Sebagai (kedudukan, peran)', 'As (position, role)'), ex('私《わたし》は 留学生《りゅうがくせい》として 日本《にほん》に 来《き》ました。', 'Saya datang ke Jepang sebagai mahasiswa asing.', 'I came to Japan as an international student.')],
      ['V ない形 −ない ＋ ず[に]', b('Tanpa melakukan ~ (tulisan)', 'Without doing ~ (written)'), ex('朝《あさ》ごはんを 食《た》べずに 家《いえ》を 出《で》た。', 'Saya meninggalkan rumah tanpa sarapan.', 'I left home without eating breakfast.')],
      ['V ない形 −ない ＋ ず、…', b('Sebab／urutan paralel (tulisan)', 'Cause／parataxis (written)'), ex('道《みち》が 分《わ》からず、約束《やくそく》の 時間《じかん》に 遅《おく》れて しまった。', 'Tidak tahu jalan, sehingga terlambat dari waktu yang dijanjikan.', 'I did not know the way and was late for the appointment.')],
      ['〜ている (riwayat)', b('Pengalaman, catatan, riwayat', 'Experience, record, history'), ex('この 工場《こうじょう》は 創業《そうぎょう》以来《いらい》、一度《いちど》も 事故《じこ》を 起《お》こして いない。', 'Pabrik ini sejak berdiri tidak pernah sekali pun mengalami kecelakaan.', 'This factory has not had a single accident since it was founded.')],
      ['〜なんか どう？', b('Menawarkan contoh', 'Offering an example'), ex('週末《しゅうまつ》の 食事《しょくじ》は、イタリア料理《りょうり》なんか どう？', 'Untuk makan akhir pekan, bagaimana kalau masakan Italia?', 'For the weekend meal, how about Italian food?')],
    ],
  },
  {
    title: b('Pelajaran 12', 'Lesson 12'),
    rows: [
      ['…もの／もんだから', b('Alasan, dalih', 'Reason, excuse'), ex('道《みち》が 混《こ》んで いたものですから、遅《おく》れて しまいました。', 'Karena jalan macet, saya jadi terlambat.', 'The road was crowded, so I was late.')],
      ['〜(ら)れる (intransitif)', b('Pasif tidak langsung: terganggu', 'Indirect passive: adversely affected'), ex('出《で》かける とき、急《きゅう》に 雨《あめ》に 降《ふ》られた。', 'Saat hendak berangkat, tiba-tiba saya kehujanan.', 'Just as I was leaving, I was caught in the rain.')],
      ['〜(ら)れる (transitif)', b('Pasif tidak langsung: milik／tubuh jadi sasaran', 'Indirect passive: possession／body affected'), ex('電車《でんしゃ》の 中《なか》で 隣《となり》の 人《ひと》に 足《あし》を 踏《ふ》まれた。', 'Di dalam kereta, kaki saya terinjak oleh orang di sebelah.', 'On the train, the person next to me stepped on my foot.')],
      ['〜たり〜たり', b('Menyebut contoh; bergantian', 'Listing examples; alternation'), ex('週末《しゅうまつ》は 本《ほん》を 読《よ》んだり、散歩《さんぽ》を したり して います。', 'Akhir pekan saya membaca buku, berjalan-jalan, dan sebagainya.', 'At weekends I do things like reading and going for walks.')],
      ['〜っぱなし', b('Dibiarkan terus (tidak baik)', 'Left as is (undesirable)'), ex('電気《でんき》を つけっぱなしで 寝《ね》て しまった。', 'Saya tertidur dengan lampu dibiarkan menyala.', 'I fell asleep with the light left on.')],
      ['…おかげで／おかげだ', b('Berkat (hasil baik)', 'Thanks to (good result)'), ex('先輩《せんぱい》が 教《おし》えて くれた おかげで、仕事《しごと》に 早《はや》く 慣《な》れた。', 'Berkat senior yang mengajari saya, saya cepat terbiasa dengan pekerjaan.', 'Thanks to my senior colleague teaching me, I got used to the work quickly.')],
      ['…せいで／せいだ', b('Gara-gara (hasil buruk)', 'Because of (bad result)'), ex('寝坊《ねぼう》した せいで、バスに 乗《の》り遅《おく》れた。', 'Gara-gara bangun kesiangan, saya ketinggalan bus.', 'Because I overslept, I missed the bus.')],
      ['…みたいです', b('Dugaan dari penampilan', 'Conjecture from appearance'), ex('外《そと》が 濡《ぬ》れて いる。夜《よる》に 雨《あめ》が 降《ふ》った みたいだ。', 'Di luar basah. Sepertinya semalam hujan.', 'It is wet outside. It looks like it rained during the night.')],
      ['どちらかと言《い》えば、〜ほうだ', b('Kalau boleh dibilang, cenderung ~', 'If anything, on the ~ side'), ex('私《わたし》は どちらかと 言《い》えば、朝型《あさがた》の ほうです。', 'Kalau boleh dibilang, saya termasuk tipe orang pagi.', 'If anything, I am a morning person.')],
      ['〜ますように／〜ませんように', b('Harapan, doa', 'Wish, prayer'), ex('明日《あした》が 晴《は》れますように。', 'Semoga besok cerah.', 'I hope it will be fine tomorrow.')],
      ['〜とは', b('Terkejut, tak disangka', 'Surprise, unexpectedness'), ex('こんなに 早《はや》く 終《お》わるとは 思《おも》わなかった。', 'Tak kusangka akan selesai secepat ini.', 'I never thought it would end this quickly.')],
    ],
  },
]

// ---------------------------------------------------------------------
// compare — perbandingan
// baris: sel bebas (string | {id,en} | {ja,id,en})
// ---------------------------------------------------------------------
const compareCards = [
  {
    title: b('ことにする ↔ ことになる', 'ことにする ↔ ことになる'),
    hint: b('Keputusan: siapa yang memutuskan, dan apakah masih berlangsung.', 'Decisions: who decided, and whether it is still going on.'),
    cols: [b('Pola', 'Pattern'), b('Fokus', 'Focus'), b('Contoh', 'Example')],
    rows: [
      ['〜ことにする', b('Pembicara sendiri yang memutuskan (satu kali)', 'The speaker decides (one time)'), ex('来月《らいげつ》から ジムに 通《かよ》う ことに しました。', 'Saya memutuskan berlatih di gym mulai bulan depan.', 'I decided to go to the gym from next month.')],
      ['〜ことにしている', b('Keputusan pribadi yang terus dijalankan (kebiasaan)', 'A personal rule kept up (habit)'), ex('八時《はちじ》までに 食事《しょくじ》を 終《お》える ことに している。', 'Saya membiasakan selesai makan sebelum jam delapan.', 'I make it a rule to finish dinner by eight.')],
      ['〜ことになる', b('Sudah diputuskan／terjadi (oleh pihak lain atau keadaan)', 'It has been decided／has come about (by others or circumstances)'), ex('来年《らいねん》、大阪《おおさか》で 働《はたら》く ことに なりました。', 'Tahun depan saya akan bekerja di Osaka.', 'I will be working in Osaka next year.')],
      ['〜ことになっている', b('Jadwal atau aturan yang sudah berlaku', 'An established schedule or rule'), ex('試験《しけん》は 午前《ごぜん》十時《じゅうじ》に 始《はじ》まる ことに なって いる。', 'Ujian dijadwalkan mulai pukul sepuluh pagi.', 'The exam is scheduled for ten a.m.')],
    ],
  },
  {
    title: b('ようだ ↔ みたいだ ↔ そうだ', 'ようだ ↔ みたいだ ↔ そうだ'),
    hint: b('Tiga cara menggambarkan kesan; bedakan bentuk dan tingkat formalitasnya.', 'Three ways of describing an impression; note the form and the formality.'),
    cols: [b('Pola', 'Pattern'), b('Sambungan', 'Attaches as'), b('Makna', 'Meaning'), b('Contoh', 'Example')],
    rows: [
      ['Nの ようだ', 'N + の + ようだ／ような／ように', b('Perumpamaan; contoh (agak formal／tulisan)', 'Simile; example (fairly formal／written)'), ex('彼女《かのじょ》は 人形《にんぎょう》のように きれいだ。', 'Dia cantik seperti boneka.', 'She is pretty as a doll.')],
      ['Nみたいだ', 'N + みたいだ／みたいな／みたいに', b('Sama dengan ようだ, lebih lisan', 'Same as ようだ, more spoken'), ex('この 部屋《へや》は 冬《ふゆ》みたいに 寒《さむ》いよ。', 'Ruangan ini dingin seperti musim dingin.', 'This room is as cold as winter.')],
      ['Vそうだ', 'Vます-stem + そうだ', b('Perkiraan: akan terjadi', 'Prediction: about to happen'), ex('荷物《にもつ》が 落《お》ちそうだから、気《き》を つけて。', 'Barangnya mau jatuh, hati-hati.', 'The luggage is about to fall, so be careful.')],
      ['Aそうだ', 'い-adj −い／な-adj −な + そうだ', b('Kesan dari penampilan luar', 'Impression from outward appearance'), ex('この スープは 辛《から》そうですね。', 'Sup ini kelihatannya pedas.', 'This soup looks spicy.')],
    ],
  },
  {
    title: b('たら ↔ と ↔ あと', 'たら ↔ と ↔ あと'),
    hint: b('Tiga pola urutan peristiwa.', 'Three patterns of sequence.'),
    cols: [b('Pola', 'Pattern'), b('Makna', 'Meaning'), b('Contoh', 'Example')],
    rows: [
      ['Xたら、Yた', b('Y terjadi／ditemukan setelah X; Y di luar kendali pembicara', 'Y happens／is discovered after X; Y is outside the speaker\'s control'), ex('窓《まど》を 開《あ》けたら、涼《すず》しい 風《かぜ》が 入《はい》って きた。', 'Ketika jendela dibuka, angin sejuk masuk.', 'When I opened the window, a cool breeze came in.')],
      ['Xと、Yた', b('Makna sama dengan たら (penemuan／hasil)', 'Same meaning as たら (discovery／result)'), ex('冷蔵庫《れいぞうこ》を 開《あ》けると、ケーキが 入《はい》って いた。', 'Saat kulkas dibuka, ada kue di dalamnya.', 'When I opened the fridge there was a cake.')],
      ['Xた あと(で)、Y', b('Y terjadi setelah X; boleh perbuatan sengaja', 'Y comes after X; may be a deliberate action'), ex('食事《しょくじ》を した あとで、薬《くすり》を 飲《の》んで ください。', 'Minumlah obat setelah makan.', 'Please take the medicine after eating.')],
    ],
  },
  {
    title: b('Kutipan langsung ↔ tidak langsung', 'Direct ↔ indirect speech'),
    hint: b('〜ように 言う／頼む／注意する／伝える mengubah perintah menjadi kalimat tidak langsung.', '〜ように 言う／頼む／注意する／伝える turns an instruction into indirect speech.'),
    cols: [b('Langsung', 'Direct'), b('Tidak langsung', 'Indirect')],
    rows: [
      [ex('「宿題《しゅくだい》を 出《だ》して ください」と 伝《つた》えた。', 'Disampaikan: "Tolong kumpulkan PR."', 'Told them: "Please hand in your homework."'), ex('宿題《しゅくだい》を 出《だ》すように 伝《つた》えた。', 'Disampaikan agar PR dikumpulkan.', 'Told them to hand in their homework.')],
      [ex('「甘《あま》い 物《もの》を 食《た》べては いけません」と 言《い》われた。', 'Dikatakan: "Jangan makan yang manis."', 'Was told: "You must not eat sweets."'), ex('甘《あま》い 物《もの》を 食《た》べないように 言《い》われた。', 'Disuruh agar tidak makan yang manis.', 'Was told not to eat sweets.')],
      [ex('「早《はや》く 寝《ね》なさい」と 言《い》った。', 'Berkata: "Tidurlah cepat."', 'Said: "Go to bed early."'), ex('早《はや》く 寝《ね》るように 言《い》った。', 'Menyuruh agar tidur cepat.', 'Told them to go to bed early.')],
    ],
  },
  {
    title: b('つもり: 4 pola yang mudah tertukar', 'つもり: four easily confused patterns'),
    hint: b('Niat, sangkalan tegas, niat masa lalu, dan anggapan diri.', 'Intention, firm denial, past intention and self-perception.'),
    cols: [b('Pola', 'Pattern'), b('Makna', 'Meaning'), b('Contoh', 'Example')],
    rows: [
      ['Vる つもりだ', b('Berniat melakukan ~', 'Intend to do ~'), ex('来年《らいねん》、留学《りゅうがく》する つもりです。', 'Tahun depan saya berniat belajar ke luar negeri.', 'I intend to study abroad next year.')],
      ['Vる つもりはない', b('Sama sekali tidak berniat; sangkalan tegas terhadap ucapan lawan bicara', 'No intention at all; a firm denial of what the other said'), ex('会社《かいしゃ》を 辞《や》める つもりは ありません。', 'Saya tidak berniat berhenti dari perusahaan.', 'I have no intention of quitting the company.')],
      ['Vない つもりだ', b('Berniat tidak ~ (lebih lunak daripada つもりはない)', 'Intend not to ~ (softer than つもりはない)'), ex('今年《ことし》は 車《くるま》を 買《か》わない つもりです。', 'Tahun ini saya berniat tidak membeli mobil.', 'I plan not to buy a car this year.')],
      ['Vる／Vない つもりだった', b('Tadinya berniat ~ (biasanya diikuti perubahan rencana)', 'Had intended to ~ (usually followed by a change of plan)'), ex('昨日《きのう》は 早《はや》く 寝《ね》る つもりでしたが、つい 遅《おそ》くまで 起《お》きて いました。', 'Kemarin saya berniat tidur cepat, tetapi tanpa sadar tetap terjaga sampai larut.', 'I meant to go to bed early yesterday, but I ended up staying up late.')],
      ['Vた／Vている つもり', b('Merasa sudah ~ (kenyataannya belum tentu)', 'Thinking one has ~ (reality may differ)'), ex('鍵《かぎ》は かけた つもりでしたが、かかって いませんでした。', 'Saya kira sudah mengunci, tetapi ternyata tidak terkunci.', 'I thought I had locked it, but it was not locked.')],
    ],
  },
  {
    title: b('まで ↔ までに', 'まで ↔ までに'),
    hint: b('Sama-sama batas waktu: まで untuk yang berlanjut, までに untuk yang terjadi sekali.', 'Both are limits: まで for something continuing, までに for a one-off event.'),
    cols: [b('Pola', 'Pattern'), b('Makna', 'Meaning'), b('Contoh', 'Example')],
    rows: [
      ['Nまで／Vるまで', b('Sampai ~ (tindakan／keadaan berlanjut sampai batas)', 'Until ~ (an action／state continues up to the limit)'), ex('会議《かいぎ》が 始《はじ》まるまで、ロビーで 待《ま》って います。', 'Saya menunggu di lobi sampai rapat dimulai.', 'I will wait in the lobby until the meeting starts.')],
      ['Nまでに／Vるまでに', b('Paling lambat ~ (kejadian sekali, selesai sebelum batas)', 'By ~ (a one-off event completed before the limit)'), ex('三時《さんじ》までに この 資料《しりょう》を コピーして ください。', 'Tolong fotokopi dokumen ini paling lambat jam tiga.', 'Please copy these materials by three o\'clock.')],
      ['Vるまでに', b('Sebelum peristiwa tertentu terjadi', 'Before a certain event happens'), ex('先生《せんせい》が 来《く》るまでに、掃除《そうじ》を 終《お》わらせた。', 'Saya menyelesaikan bersih-bersih sebelum guru datang.', 'I finished cleaning before the teacher arrived.')],
    ],
  },
  {
    title: b('あいだ ↔ あいだに', 'あいだ ↔ あいだに'),
    hint: b('Pola Xあいだ(に)Y: X berlangsung selama periode tertentu.', 'In Xあいだ(に)Y, X goes on for a period of time.'),
    cols: [b('Pola', 'Pattern'), b('Makna', 'Meaning'), b('Contoh', 'Example')],
    rows: [
      ['Xあいだ、Y', b('X dan Y sama-sama berlangsung selama periode itu', 'Both X and Y continue throughout the period'), ex('電車《でんしゃ》に 乗《の》って いる あいだ、本《ほん》を 読《よ》んで いた。', 'Selama di kereta, saya membaca buku.', 'While on the train, I was reading a book.')],
      ['Xあいだに、Y', b('Y adalah kejadian sesaat yang muncul selama X', 'Y is a single event that happens during X'), ex('旅行《りょこう》の あいだに、アパートに 泥棒《どろぼう》が 入《はい》った。', 'Selama kami bepergian, pencuri masuk ke apartemen.', 'A burglar broke into our flat while we were away.')],
    ],
  },
  {
    title: b('Nなんか ↔ …なんて', 'Nなんか ↔ …なんて'),
    hint: b('なんか hanya setelah kata benda; なんて bisa setelah kata kerja／sifat／kalimat.', 'なんか follows nouns only; なんて can follow verbs, adjectives and clauses.'),
    cols: [b('Pola', 'Pattern'), b('Makna', 'Meaning'), b('Contoh', 'Example')],
    rows: [
      ['Nなんか', b('Merendahkan N; dipakai juga untuk merendah', 'Disparages N; also used to be modest'), ex('私《わたし》の 料理《りょうり》なんか、人《ひと》に 出《だ》せません。', 'Masakan saya tidak pantas disajikan untuk orang lain.', 'My cooking is not fit to serve to anyone.')],
      ['Nなんて', b('Sama dengan なんか pada kata benda; juga menyatakan heran', 'Same as なんか after a noun; also shows surprise'), ex('大江《おおえ》なんて 人《ひと》は 知《し》りません。', 'Orang bernama Oe? Saya tidak kenal.', 'I do not know anyone called Oe.')],
      ['V・A 普通形＋なんて', b('Kejutan, kritik, tak percaya; なんか tidak bisa dipakai di sini', 'Surprise, criticism, disbelief; なんか cannot be used here'), ex('彼《かれ》が 会社《かいしゃ》を 辞《や》めるなんて、信《しん》じられません。', 'Dia berhenti dari perusahaan? Saya tidak percaya.', 'I cannot believe he is quitting the company.')],
    ],
  },
  {
    title: b('から ↔ ので ↔ ため ↔ からだ', 'から ↔ ので ↔ ため ↔ からだ'),
    hint: b('Empat cara menyatakan sebab; bedakan nuansa dan posisinya dalam kalimat.', 'Four ways to give a reason; note the nuance and the position in the sentence.'),
    cols: [b('Pola', 'Pattern'), b('Ciri', 'Feature'), b('Contoh', 'Example')],
    rows: [
      ['…から', b('Lisan, tegas; boleh diikuti ajakan／perintah', 'Spoken, direct; may be followed by an invitation／request'), ex('時間《じかん》が ありませんから、新聞《しんぶん》を 読《よ》みません。', 'Karena tidak ada waktu, saya tidak membaca koran.', 'I have no time, so I do not read the paper.')],
      ['…ので', b('Lebih lembut; tidak bisa menjadi predikat (×のでだ／×のでです)', 'Softer; cannot end the sentence (×のでだ／×のでです)'), ex('雨《あめ》が 降《ふ》って いるので、家《いえ》に います。', 'Karena hujan, saya di rumah.', 'It is raining, so I am staying home.')],
      ['…ため(に)', b('Formal／tulisan; sebab di depan, akibat di belakang', 'Formal／written; cause first, result after'), ex('台風《たいふう》が 来《き》た ために、新幹線《しんかんせん》が 止《と》まりました。', 'Karena topan datang, Shinkansen berhenti beroperasi.', 'Because a typhoon came, the Shinkansen stopped running.')],
      ['…からだ', b('Menjawab "mengapa"; akibat lebih dulu: …のは、…からだ', 'Answers "why"; result first: …のは、…からだ'), ex('急《きゅう》に ドアが 開《あ》いたのは、だれかが ボタンを 押《お》したからだ。', 'Pintu tiba-tiba terbuka karena seseorang menekan tombol.', 'The door opened suddenly because someone pressed the button.')],
      ['…ためだ', b('Versi formal dari からだ', 'The formal counterpart of からだ'), ex('空港《くうこう》が 使《つか》えなく なったのは、大雪《おおゆき》が 降《ふ》った ためです。', 'Bandara tak bisa dipakai karena salju lebat turun.', 'The airport became unusable because heavy snow fell.')],
    ],
  },
  {
    title: b('たら／ば: hipotesis ↔ pasti ↔ bertentangan dengan fakta', 'たら／ば: hypothetical ↔ certain ↔ counterfactual'),
    hint: b('Pelajaran 9 menambah pemakaian terakhir: membayangkan hal yang tidak terjadi.', 'Lesson 9 adds the last use: imagining what did not happen.'),
    cols: [b('Pola', 'Pattern'), b('Makna', 'Meaning'), b('Contoh', 'Example')],
    rows: [
      ['Vたら、…', b('Jika (hipotesis)', 'If (hypothetical)'), ex('お金《かね》が あったら、旅行《りょこう》します。', 'Kalau punya uang, saya akan bepergian.', 'If I had money, I would travel.')],
      ['Vたら、…', b('Setelah (pasti terjadi di masa depan)', 'When (certain to happen)'), ex('十時《じゅうじ》に なったら、出《で》かけましょう。', 'Kalau sudah jam sepuluh, mari berangkat.', 'When it is ten o\'clock, let us leave.')],
      ['Vば、…', b('Syarat umum', 'General condition'), ex('ボタンを 押《お》せば、窓《まど》が 開《あ》きます。', 'Kalau tombol ditekan, jendela terbuka.', 'If you press the button, the window opens.')],
      ['Vたら／Vば、…た(のに)', b('Bertentangan dengan fakta; penutup dugaan／のに', 'Contrary to fact; ends in a conjecture／のに'), ex('もっと 早《はや》く 家《いえ》を 出《で》ていれば、電車《でんしゃ》に 間《ま》に 合《あ》ったのに。', 'Kalau saya berangkat lebih awal, saya pasti sempat naik kereta.', 'If I had left home earlier, I would have caught the train.')],
    ],
  },
  {
    title: b('AはBほど〜ない ↔ Nほど〜はない', 'AはBほど〜ない ↔ Nほど〜はない'),
    hint: b('Keduanya memakai ほど, tetapi arah perbandingannya berbeda.', 'Both use ほど, but the direction of the comparison differs.'),
    cols: [b('Pola', 'Pattern'), b('Makna', 'Meaning'), b('Contoh', 'Example')],
    rows: [
      ['AはBほど〜ない', b('A kurang ~ dibanding B', 'A is less ~ than B'), ex('今年《ことし》の 冬《ふゆ》は 去年《きょねん》ほど 寒《さむ》くない。', 'Musim dingin tahun ini tidak sedingin tahun lalu.', 'This winter is not as cold as last year.')],
      ['Nほど〜はない／いない', b('N adalah yang paling ~', 'N is the most ~'), ex('母《はは》の 作《つく》る カレーほど おいしい 料理《りょうり》は ない。', 'Tidak ada masakan seenak kari buatan ibu.', 'No dish is as delicious as the curry my mother makes.')],
      ['〜ほどではない', b('Belum sampai tingkat ~', 'Not to the extent of ~'), ex('十月《じゅうがつ》に 入《はい》って 少《すこ》し 寒《さむ》く なったが、まだ コートを 着《き》る ほどでは ない。', 'Memasuki Oktober mulai dingin, tetapi belum sampai perlu mantel.', 'It has got a little colder in October, but not enough to need a coat.')],
    ],
  },
  {
    title: b('Vている N ↔ Vた N', 'Vている N ↔ Vた N'),
    hint: b('Bentuk た pada penerang kata benda hanya setara dengan ている bila ている menyatakan keadaan hasil.', 'The た-form modifier equals ている only when ている shows a resulting state.'),
    cols: [b('Jenis ている', 'Type of ている'), b('Boleh diganti た?', 'Replaceable by た?'), b('Contoh', 'Example')],
    rows: [
      [b('Keadaan hasil', 'Resulting state'), b('Ya, maknanya sama', 'Yes, same meaning'), ex('眼鏡《めがね》を かけた 田中《たなか》さん', 'Pak Tanaka yang berkacamata', 'Mr. Tanaka, who is wearing glasses')],
      [b('Tindakan sedang berlangsung', 'Ongoing action'), b('Tidak, maknanya berubah', 'No, the meaning changes'), ex('本《ほん》を 読《よ》んで いる 山下《やました》さん', 'Pak Yamashita yang sedang membaca', 'Mr. Yamashita, who is reading a book')],
      [b('Tindakan yang sudah selesai', 'Completed action'), b('Hasilnya: lampau', 'Result: past'), ex('本《ほん》を 読《よ》んだ 山下《やました》さん', 'Pak Yamashita yang (telah) membaca buku', 'Mr. Yamashita, who read a book')],
    ],
  },
  {
    title: b('Vて くる ↔ Vて いく', 'Vて くる ↔ Vて いく'),
    hint: b('Arah gerakan relatif terhadap pembicara, dan keadaan baru yang mulai tampak.', 'Direction of motion relative to the speaker, and a new state coming into view.'),
    cols: [b('Pola', 'Pattern'), b('Makna', 'Meaning'), b('Contoh', 'Example')],
    rows: [
      ['Vて くる', b('Gerakan menuju pembicara', 'Motion toward the speaker'), ex('兄《あに》が 旅行《りょこう》から 帰《かえ》って きた。', 'Kakak laki-laki saya pulang dari bepergian.', 'My elder brother came back from his trip.')],
      ['Vて いく', b('Gerakan menjauhi pembicara', 'Motion away from the speaker'), ex('授業《じゅぎょう》の あと、学生《がくせい》たちは うちへ 帰《かえ》って いった。', 'Setelah kelas, para siswa pulang ke rumah.', 'After the lesson, the students went home.')],
      ['Vて くる', b('Keadaan baru muncul dan mulai terasa／terlihat', 'A new state emerges and becomes perceptible'), ex('暗《くら》く なって、星《ほし》が 見《み》えて きた。', 'Hari menjadi gelap dan bintang-bintang mulai terlihat.', 'It grew dark, and the stars came out.')],
    ],
  },
  {
    title: b('はずだ ↔ だろう ↔ ようだ／みたいだ ↔ らしい', 'はずだ ↔ だろう ↔ ようだ／みたいだ ↔ らしい'),
    hint: b('Empat cara menyatakan dugaan: bedakan dasar yang dipakai pembicara.', 'Four ways of expressing a judgement: note what the speaker is basing it on.'),
    cols: [b('Pola', 'Pattern'), b('Dasar pembicara', 'Speaker\'s basis'), b('Makna', 'Meaning'), b('Contoh', 'Example')],
    rows: [
      ['〜はずだ', b('Perhitungan, pengetahuan, logika', 'Calculation, knowledge, logic'), b('Yakin kuat, \"pasti begitu\"', 'Strong conviction, "it must be so"'), ex('今《いま》 九時《くじ》だから、スーパーは もう 開《あ》いて いる はずだ。', 'Sekarang jam sembilan, supermarket seharusnya sudah buka.', 'It is nine, so the supermarket should be open.')],
      ['〜だろう／でしょう', b('Perkiraan pribadi, tidak perlu dasar kuat', 'Personal estimate, no strong basis needed'), b('Mungkin, barangkali', 'Probably'), ex('明日《あした》は 晴《は》れるだろう。', 'Besok mungkin cerah.', 'It will probably be sunny tomorrow.')],
      ['〜ようだ／みたいだ', b('Kesan dari penglihatan atau pendengaran langsung', 'Impression from what one sees or hears directly'), b('Sepertinya (dugaan dari tampilan)', 'It seems (guess from appearance)'), ex('隣《となり》の 部屋《へや》は 電気《でんき》が 消《き》えて いる。出《で》かけた みたいだ。', 'Lampu kamar sebelah mati. Sepertinya mereka pergi.', 'The lights next door are off. They seem to have gone out.')],
      ['〜らしい', b('Informasi dari orang lain, bacaan, atau tanda tidak langsung', 'Information from others, reading, or indirect signs'), b('Katanya／kelihatannya berdasar kabar', 'I hear／apparently, based on reports'), ex('天気予報《てんきよほう》に よると、明日《あした》は 雨《あめ》らしい。', 'Menurut prakiraan cuaca, besok katanya hujan.', 'According to the forecast, it seems it will rain tomorrow.')],
    ],
  },
  {
    title: b('らしい: tiga cara memakainya', 'らしい: three ways it is used'),
    hint: b('Bentuk sama, makna berbeda. Lihat apa yang ada di depan らしい.', 'Same form, different meanings. Look at what comes before らしい.'),
    cols: [b('Jenis', 'Type'), b('Sambungan', 'Attaches as'), b('Makna', 'Meaning'), b('Contoh', 'Example')],
    rows: [
      [b('Sifat khas', 'Typical quality'), 'N ＋ らしい', b('Pantas／khas sebagai N', 'Befitting／typical of N'), ex('子《こ》どもらしい 元気《げんき》な 声《こえ》が 聞《き》こえる。', 'Terdengar suara riang khas anak-anak.', 'I can hear lively, childlike voices.')],
      [b('Kabar yang didengar', 'Hearsay'), b('普通形《ふつうけい》 ＋ らしい (N, なA tanpa だ)', 'Plain form ＋ らしい (N, な-adj. without だ)'), b('Katanya ~', 'I hear that ~'), ex('田中《たなか》さんは 会社《かいしゃ》を 辞《や》める らしい。', 'Katanya Pak Tanaka mau berhenti dari perusahaan.', 'I hear Mr. Tanaka is quitting the company.')],
      [b('Dugaan', 'Conjecture'), b('普通形《ふつうけい》 ＋ らしい', 'Plain form ＋ らしい'), b('Sepertinya ~ (berdasar tanda)', 'It seems ~ (based on signs)'), ex('窓《まど》が 割《わ》れて いる。誰《だれ》かが ボールを ぶつけたらしい。', 'Jendelanya pecah. Sepertinya ada yang melemparkan bola.', 'The window is broken. Someone seems to have hit it with a ball.')],
    ],
  },
  {
    title: b('おかげで ↔ せいで ↔ もの(だから) ↔ ため(に)', 'おかげで ↔ せいで ↔ もの(だから) ↔ ため(に)'),
    hint: b('Semuanya menyatakan sebab. Pilih menurut nuansa hasilnya dan tingkat formalitas.', 'All of them give a cause. Choose by the nuance of the result and the formality.'),
    cols: [b('Pola', 'Pattern'), b('Nuansa', 'Nuance'), b('Contoh', 'Example')],
    rows: [
      ['…おかげで', b('Hasil baik; berterima kasih', 'Good result; gratitude'), ex('先輩《せんぱい》が 教《おし》えて くれた おかげで、仕事《しごと》に 早《はや》く 慣《な》れた。', 'Berkat senior yang mengajari saya, saya cepat terbiasa dengan pekerjaan.', 'Thanks to my senior teaching me, I got used to the work quickly.')],
      ['…せいで', b('Hasil buruk; menyalahkan sebab', 'Bad result; blaming the cause'), ex('寝坊《ねぼう》した せいで、バスに 乗《の》り遅《おく》れた。', 'Gara-gara bangun kesiangan, saya ketinggalan bus.', 'Because I overslept, I missed the bus.')],
      ['…ものだから', b('Dalih untuk hal yang tidak diinginkan (lisan)', 'Excuse for an undesirable event (spoken)'), ex('バスが 来《こ》なかった ものですから、会議《かいぎ》に 遅《おく》れて しまいました。', 'Karena bus tidak datang, saya terlambat ke rapat.', 'The bus did not come, so I was late for the meeting.')],
      ['…ため(に)', b('Sebab formal untuk tulisan／berita', 'Formal cause for writing／news'), ex('台風《たいふう》が 来《き》た ために、新幹線《しんかんせん》が 止《と》まりました。', 'Karena topan datang, Shinkansen berhenti beroperasi.', 'Because a typhoon came, the Shinkansen stopped running.')],
    ],
  },
  {
    title: b('V る ことが ある ↔ V た ことが ある', 'V る ことが ある ↔ V た ことが ある'),
    hint: b('Sama-sama ことが ある, tetapi bentuk kata kerja di depannya mengubah makna.', 'Both end in ことが ある, but the verb form before it changes the meaning.'),
    cols: [b('Pola', 'Pattern'), b('Makna', 'Meaning'), b('Contoh', 'Example')],
    rows: [
      ['V 辞書形《じしょけい》／V ない ＋ ことが ある', b('Kadang-kadang terjadi (kejadian berulang tak tentu)', 'Sometimes happens (an irregular recurring event)'), ex('この 道《みち》は 雨《あめ》の 日《ひ》は 混《こ》む ことが ある。', 'Jalan ini kadang ramai pada hari hujan.', 'This road sometimes gets crowded on rainy days.')],
      ['V た ＋ ことが ある', b('Pernah ~ (pengalaman)', 'Have ~ before (experience)'), ex('北海道《ほっかいどう》に 行《い》った ことが あります。', 'Saya pernah pergi ke Hokkaido.', 'I have been to Hokkaido.')],
    ],
  },
  {
    title: b('V 出す ↔ V 始める ↔ V 続ける ↔ V 終わる', 'V 出す ↔ V 始める ↔ V 続ける ↔ V 終わる'),
    hint: b('Kata kerja majemuk tahap tindakan; hanya 〜出す yang tidak bisa dipakai untuk ajakan atau permintaan.', 'Compound verbs for the stages of an action; only 〜出す cannot be used for invitations or requests.'),
    cols: [b('Pola', 'Pattern'), b('Makna', 'Meaning'), b('Ajakan／permintaan', 'Invitation／request'), b('Contoh', 'Example')],
    rows: [
      ['V 出《だ》す', b('Mulai mendadak', 'Suddenly begins'), b('Tidak bisa', 'Not possible'), ex('犬《いぬ》が 突然《とつぜん》 吠《ほ》え出《だ》した。', 'Anjing itu tiba-tiba menggonggong.', 'The dog suddenly began to bark.')],
      ['V 始《はじ》める', b('Mulai (netral)', 'Begins (neutral)'), b('Bisa', 'Possible'), ex('先《さき》に 料理《りょうり》を 食《た》べ始《はじ》めて ください。', 'Silakan mulai makan lebih dulu.', 'Please start eating first.')],
      ['V 続《つづ》ける', b('Terus berlanjut', 'Continues'), b('Bisa', 'Possible'), ex('三十分《さんじゅっぷん》 歩《ある》き続《つづ》けて ください。', 'Tolong terus berjalan selama tiga puluh menit.', 'Please keep walking for thirty minutes.')],
      ['V 終《お》わる', b('Selesai', 'Finishes'), b('Bisa', 'Possible'), ex('宿題《しゅくだい》を 書《か》き終《お》わってから、遊《あそ》びに 行《い》く。', 'Setelah selesai menulis PR, saya pergi bermain.', 'I will go out to play after I finish writing my homework.')],
    ],
  },
  {
    title: b('〜ずに ↔ 〜ないで ↔ 〜ず、 ↔ 〜なくて', '〜ずに ↔ 〜ないで ↔ 〜ず、 ↔ 〜なくて'),
    hint: b('Bentuk ず lebih ke bahasa tulis; ない で dan なくて dipakai dalam percakapan.', 'The ず forms belong more to writing; ないで and なくて are for conversation.'),
    cols: [b('Pola', 'Pattern'), b('Fungsi', 'Function'), b('Gaya', 'Style'), b('Contoh', 'Example')],
    rows: [
      ['〜ないで', b('Keadaan penyerta／cara', 'Attendant circumstance／means'), b('Lisan', 'Spoken'), ex('かぎを かけないで 出《で》かけて しまった。', 'Saya pergi tanpa mengunci pintu.', 'I left without locking up.')],
      ['〜ずに', b('Sama dengan 〜ないで', 'Same as 〜ないで'), b('Tulisan', 'Written'), ex('かぎを かけずに 出《で》かけて しまった。', 'Saya pergi tanpa mengunci pintu.', 'I left without locking up.')],
      ['〜なくて、…', b('Sebab／alasan', 'Cause／reason'), b('Lisan', 'Spoken'), ex('熱《ねつ》が 下《さ》がらなくて、心配《しんぱい》しました。', 'Demamnya tidak turun, jadi saya khawatir.', 'The fever did not come down, so I was worried.')],
      ['〜ず、…', b('Sebab／alasan; menjajarkan klausa', 'Cause／reason; juxtaposing clauses'), b('Tulisan', 'Written'), ex('熱《ねつ》が 下《さ》がらず、心配《しんぱい》しました。', 'Demamnya tidak turun, jadi saya khawatir.', 'The fever did not come down, so I was worried.')],
    ],
  },
  {
    title: b('Pasif langsung ↔ pasif tidak langsung ↔ kausatif-pasif', 'Direct ↔ indirect passive ↔ causative-passive'),
    hint: b('Lihat siapa subjeknya dan apakah ada nuansa dirugikan atau dipaksa.', 'Look at who the subject is and whether there is a nuance of being harmed or forced.'),
    cols: [b('Jenis', 'Type'), b('Subjek', 'Subject'), b('Nuansa', 'Nuance'), b('Contoh', 'Example')],
    rows: [
      [b('Pasif langsung', 'Direct passive'), b('Objek kata kerja transitif', 'Object of a transitive verb'), b('Netral (atau merugikan)', 'Neutral (or adverse)'), ex('友達《ともだち》に 名前《なまえ》を 呼《よ》ばれた。', 'Saya dipanggil namanya oleh teman.', 'I was called by name by a friend.')],
      [b('Pasif tidak langsung (intransitif)', 'Indirect passive (intransitive)'), b('Orang yang terkena dampak', 'The person affected'), b('Terganggu／dirugikan', 'Inconvenienced／harmed'), ex('昨日《きのう》、雨《あめ》に 降《ふ》られた。', 'Kemarin saya kehujanan.', 'I was rained on yesterday.')],
      [b('Pasif tidak langsung (transitif)', 'Indirect passive (transitive)'), b('Pemilik benda／bagian tubuh', 'Owner of the object／body part'), b('Terganggu／dirugikan', 'Inconvenienced／harmed'), ex('電車《でんしゃ》で 足《あし》を 踏《ふ》まれた。', 'Di kereta, kaki saya terinjak.', 'My foot was stepped on on the train.')],
      [b('Kausatif-pasif', 'Causative-passive'), b('Orang yang disuruh', 'The person made to act'), b('Dipaksa melakukan', 'Forced to do'), ex('子《こ》どもの とき、毎日《まいにち》 ピアノを 練習《れんしゅう》させられた。', 'Waktu kecil saya dipaksa berlatih piano setiap hari.', 'As a child I was made to practise piano daily.')],
    ],
  },
  {
    title: b('Y より X ほうが ↔ X は Y ほど〜ない', 'Y より X ほうが ↔ X は Y ほど〜ない'),
    hint: b('Dua cara membandingkan: yang satu menyebut yang lebih, yang lain menyebut yang kurang.', 'Two ways to compare: one names the greater, the other the lesser.'),
    cols: [b('Pola', 'Pattern'), b('Fokus', 'Focus'), b('Contoh', 'Example')],
    rows: [
      ['Y より X ほうが …', b('X lebih ~ daripada Y (X adalah yang dipilih)', 'X is more ~ than Y (X is the focus)'), ex('電車《でんしゃ》より バスの ほうが 安《やす》い。', 'Bus lebih murah daripada kereta.', 'Buses are cheaper than trains.')],
      ['X は Y ほど …ない', b('X tidak se~ Y (Y yang jadi tolok ukur)', 'X is not as ~ as Y (Y is the yardstick)'), ex('バスは 電車《でんしゃ》ほど 速《はや》くない。', 'Bus tidak secepat kereta.', 'Buses are not as fast as trains.')],
    ],
  },
]

// ---------------------------------------------------------------------
// forms — cara membentuk
// ---------------------------------------------------------------------
const formsCards = [
  {
    title: b('Kausatif-pasif (使役受身)', 'Causative-passive (使役受身)'),
    hint: b('Kelompok I: ない −ない + せられる／される; Kelompok II: +させられる.', 'Group I: ない −ない + せられる／される; Group II: + させられる.'),
    cols: [b('Kelompok', 'Group'), b('Bentuk kamus', 'Dictionary'), b('Kausatif-pasif', 'Causative-passive'), b('Catatan', 'Note')],
    rows: [
      ['I', '書《か》く', '書《か》かせられる／書《か》かされる', b('Dua bentuk', 'Two forms')],
      ['I', '飲《の》む', '飲《の》ませられる／飲《の》まされる', b('Dua bentuk', 'Two forms')],
      ['I', '話《はな》す', '話《はな》させられる', b('Akhiran す: tanpa bentuk pendek', 'Verbs ending in す: no short form')],
      ['II', '食《た》べる', '食《た》べさせられる', ''],
      ['II', '見《み》る', '見《み》させられる', ''],
      ['III', 'する', 'させられる', ''],
      ['III', '来《く》る', '来《こ》させられる', ''],
    ],
  },
  {
    title: b('Bentuk lisan (口語)', 'Spoken contractions (口語)'),
    cols: [b('Bentuk lengkap', 'Full form'), b('Bentuk lisan', 'Spoken form'), b('Contoh', 'Example')],
    rows: [
      ['〜てしまう', '〜ちゃう／〜じゃう', ex('読《よ》んでしまった → 読《よ》んじゃった', 'Sudah terbaca habis', 'Ended up reading it')],
      ['〜ておく', '〜とく／〜どく', ex('買《か》っておく → 買《か》っとく', 'Dibeli lebih dulu', 'Buy in advance')],
      ['〜ている', '〜てる／〜でる', ex('読《よ》んでいる → 読《よ》んでる', 'Sedang membaca', 'Is reading')],
      ['〜ていない', '〜てない', ex('見《み》ていない → 見《み》てない', 'Belum melihat', 'Have not seen')],
      ['〜のですか', '〜の？／〜んですか', ex('どこへ 行《い》くの？', 'Mau ke mana?', 'Where are you going?')],
    ],
  },
  {
    title: b('Bentuk maksud (意向形) + とする', 'Volitional form (意向形) + とする'),
    cols: [b('Kelompok', 'Group'), b('Cara membentuk', 'How to form'), b('Contoh', 'Example')],
    rows: [
      ['I', b('−u → −ou', '−u → −ou'), '書《か》く → 書《か》こう　飲《の》む → 飲《の》もう'],
      ['II', b('−る → −よう', '−る → −よう'), '食《た》べる → 食《た》べよう　見《み》る → 見《み》よう'],
      ['III', '', 'する → しよう　来《く》る → 来《こ》よう'],
    ],
  },
  {
    title: b('Pola 〜そうだ (rangkuman sambungan)', '〜そうだ (summary of how it attaches)'),
    cols: [b('Kata', 'Word'), b('Aturan', 'Rule'), b('Contoh', 'Example')],
    rows: [
      [b('Kata kerja', 'Verb'), 'Vます-stem + そうだ', '降《ふ》り + そうだ → 降《ふ》りそうだ'],
      [b('Kata sifat い', 'い-adjective'), '−い + そうだ', '辛《から》い → 辛《から》そうだ'],
      [b('Kata sifat な', 'な-adjective'), '−な／だ + そうだ', '元気《げんき》な → 元気《げんき》そうだ'],
      [b('Pengecualian', 'Exception'), 'いい → よさそう　ない → なさそう', '人気《にんき》が ない → 人気《にんき》が なさそうだ'],
      [b('Negatif kata kerja', 'Negative of verb'), 'Vます-stem + そうも ない', '終《お》わりそうも ない'],
    ],
  },
  {
    title: b('Partikel ＋ の (とのN, でのN…)', 'Particle ＋ の (とのN, でのN…)'),
    cols: [b('Partikel', 'Particle'), b('Bentuk', 'Form'), b('Contoh', 'Example')],
    rows: [
      ['と', 'との', '先生《せんせい》との 面談《めんだん》'],
      ['で', 'での', '東京《とうきょう》での 生活《せいかつ》'],
      ['から', 'からの', '会社《かいしゃ》からの お知《し》らせ'],
      ['まで', 'までの', '駅《えき》までの 道《みち》'],
      ['に／へ', 'への', '友達《ともだち》への プレゼント'],
      ['が／を', b('tidak memakai の', 'no の is added'), '田中《たなか》さんの 欠席《けっせき》を 伝《つた》える'],
    ],
  },
  {
    title: b('疑問詞＋〜ても', '疑問詞＋〜ても'),
    cols: [b('Kata tanya', 'Question word'), b('Contoh', 'Example')],
    rows: [
      ['いくら', ex('いくら 説明《せつめい》しても、わかって くれない。', 'Betapa pun dijelaskan, tidak mengerti.', 'However much I explain, they do not get it.')],
      ['どこ', ex('どこへ 行《い》っても、この 歌《うた》が 聞《き》こえる。', 'Ke mana pun pergi, lagu ini terdengar.', 'Wherever I go, I hear this song.')],
      ['どんなに', ex('どんなに 忙《いそが》しくても、朝《あさ》ごはんは 食《た》べます。', 'Sesibuk apa pun, saya sarapan.', 'However busy, I eat breakfast.')],
      ['どの N', ex('どの 店《みせ》でも 値段《ねだん》は 同《おな》じです。', 'Di toko mana pun harganya sama.', 'The price is the same at any shop.')],
    ],
  },
  {
    title: b('何＋penghitung＋も', '何＋counter＋も'),
    cols: [b('Penghitung', 'Counter'), b('Bacaan', 'Reading'), b('Contoh', 'Example')],
    rows: [
      ['何人《なんにん》も', 'nannin mo', '何人《なんにん》も 並《なら》んで いる'],
      ['何回《なんかい》も', 'nankai mo', '何回《なんかい》も 聞《き》いた'],
      ['何枚《なんまい》も', 'nanmai mo', '何枚《なんまい》も 書《か》いた'],
      ['何冊《なんさつ》も', 'nansatsu mo', '本《ほん》が 何冊《なんさつ》も ある'],
      ['何杯《なんばい》も', 'nanbai mo', 'コーヒーを 何杯《なんばい》も 飲《の》んだ'],
      ['何台《なんだい》も', 'nandai mo', '車《くるま》が 何台《なんだい》も 止《と》まって いる'],
    ],
  },
  {
    title: b('〜なくてはならない／〜なくてもかまわない (sambungan)', '〜なくてはならない／〜なくてもかまわない (how it attaches)'),
    hint: b('なくては／なければ = harus; なくても = tak perlu.', 'なくては／なければ = must; なくても = need not.'),
    cols: [b('Kata', 'Word'), b('Cara', 'Rule'), b('Contoh', 'Example')],
    rows: [
      [b('Kata kerja', 'Verb'), 'ない −ない ＋ なくて(は／も)', '飲《の》まなくては ならない／飲《の》まなくても かまわない'],
      [b('Kata sifat い', 'い-adjective'), '−い → −く ＋ なくて(は／も)', '長《なが》く なくても かまいません'],
      [b('Kata sifat な／kata benda', 'な-adjective／noun'), 'で ＋ なくて(は／も)', '日本語《にほんご》で なくては なりません'],
    ],
  },
  {
    title: b('Bentuk lisan なくちゃ／なきゃ', 'Spoken なくちゃ／なきゃ'),
    cols: [b('Bentuk lengkap', 'Full form'), b('Bentuk lisan', 'Spoken form'), b('Contoh', 'Example')],
    rows: [
      ['Vなくては いけない', 'Vなくちゃ (いけない)', ex('もう こんな 時間《じかん》！ 帰《かえ》らなくちゃ。', 'Sudah selarut ini! Aku harus pulang.', 'Look at the time! I have to go home.')],
      ['Vなければ いけない', 'Vなきゃ (いけない)', ex('明日《あした》 早《はや》いから、もう 寝《ね》なきゃ。', 'Besok pagi-pagi, aku harus segera tidur.', 'I have an early start tomorrow, so I must get to bed.')],
    ],
  },
  {
    title: b('Kutipan lisan: と → って／て', 'Spoken quotation: と → って／て'),
    cols: [b('Tulisan', 'Written'), b('Lisan', 'Spoken'), b('Contoh', 'Example')],
    rows: [
      ['普通形《ふつうけい》＋と', '普通形《ふつうけい》＋って／て', ex('今日《きょう》は 休《やす》むって 言《い》って いました。', 'Dia bilang hari ini akan libur.', 'He said he was going to take today off.')],
      ['〜という 名前《なまえ》の 人《ひと》', '〜って 人《ひと》', ex('昨日《きのう》、田山《たやま》って 人《ひと》が 来《き》ましたよ。', 'Kemarin ada orang bernama Tayama datang.', 'Someone called Tayama came by yesterday.')],
      ['Xは (topik)', 'Xって', ex('函館《はこだて》って、どんな 町《まち》？', 'Hakodate itu kota seperti apa?', 'What kind of city is Hakodate?')],
    ],
  },
  {
    title: b('つもり (sambungan)', 'つもり (how it attaches)'),
    cols: [b('Kata', 'Word'), b('Bentuk', 'Form'), b('Contoh', 'Example')],
    rows: [
      [b('Niat', 'Intention'), 'Vる／Vない ＋ つもりだ／つもりだった', '行《い》く つもりだった'],
      [b('Sangkalan', 'Denial'), 'Vる ＋ つもりはない', '買《か》う つもりは ない'],
      [b('Kata kerja (anggapan)', 'Verb (self-perception)'), 'Vた／Vている ＋ つもり', '鍵《かぎ》を かけた つもり／頑張《がんば》って いる つもり'],
      [b('Kata sifat い', 'い-adjective'), '−い ＋ つもり', '若《わか》い つもりで 無理《むり》を する'],
      [b('Kata sifat な', 'な-adjective'), '−な ＋ つもり', '元気《げんき》な つもりでも 疲《つか》れて いる'],
      [b('Kata benda', 'Noun'), 'の ＋ つもり', '研究発表《けんきゅうはっぴょう》の つもりで 話《はな》す'],
    ],
  },
  {
    title: b('〜てばかりいる／Nばかり〜ている', '〜てばかりいる／Nばかり〜ている'),
    hint: b('Menyatakan tindakan yang diulang dan dikritik pembicara.', 'An action repeated, with the speaker\'s criticism.'),
    cols: [b('Pola', 'Pattern'), b('Cara', 'Rule'), b('Contoh', 'Example')],
    rows: [
      ['Vて ばかりいる', 'V て形 ＋ ばかりいる', ex('この ネコは 一日中《いちにちじゅう》、寝《ね》て ばかりいる。', 'Kucing ini sepanjang hari hanya tidur.', 'This cat just sleeps all day long.')],
      ['Nばかり Vている', 'N ＋ ばかり ＋ kata kerja transitif ＋ ている', ex('弟《おとうと》は いつも コンピューターゲームばかり している。', 'Adik saya selalu hanya main gim komputer.', 'My younger brother always just plays computer games.')],
    ],
  },
  {
    title: b('Kausatif perasaan (感情の使役) dan pasifnya', 'Emotional causative and its passive'),
    hint: b('Kata kerja intransitif perasaan; orang yang perasaannya tergugah ditandai を. Pasif: orang itu menjadi subjek.', 'Intransitive emotion verbs; the person affected is marked with を. In the passive that person becomes the subject.'),
    cols: [b('Kata kerja', 'Verb'), b('Kausatif', 'Causative'), b('Pasif kausatif', 'Passive causative')],
    rows: [
      ['泣《な》く', '泣《な》かせる', '泣《な》かされる'],
      ['笑《わら》う', '笑《わら》わせる', '笑《わら》わされる'],
      ['驚《おどろ》く', '驚《おどろ》かせる', '驚《おどろ》かされる'],
      ['喜《よろこ》ぶ', '喜《よろこ》ばせる', '喜《よろこ》ばされる'],
      ['びっくりする', 'びっくりさせる', 'びっくりさせられる'],
      ['がっかりする', 'がっかりさせる', 'がっかりさせられる'],
      ['感動《かんどう》する', '感動《かんどう》させる', '感動《かんどう》させられる'],
    ],
  },
  {
    title: b('Nだけ／〜だけだ／〜だけでいい (sambungan)', 'Nだけ／〜だけだ／〜だけでいい (how it attaches)'),
    cols: [b('Kata', 'Word'), b('Cara', 'Rule'), b('Contoh', 'Example')],
    rows: [
      [b('Kata benda', 'Noun'), 'N ＋ だけ', '休《やす》みは 日曜日《にちようび》だけです'],
      [b('Kata kerja', 'Verb'), '普通形《ふつうけい》 ＋ だけだ／だけでいい', '本《ほん》を 読《よ》んで いる だけです'],
      [b('Kata sifat い', 'い-adjective'), '普通形《ふつうけい》 ＋ だけだ', '気分《きぶん》が 悪《わる》い だけです'],
      [b('Kata sifat な', 'な-adjective'), '−だ → −な ＋ だけだ', '静《しず》かな だけで 不便《ふべん》です'],
      [b('Cukup melakukan ~', 'Only need to do ~'), 'Vる ＋ だけで いい', 'この 紙《かみ》に 名前《なまえ》を 書《か》く だけで いいんです'],
    ],
  },
  {
    title: b('…かな／…なら (sambungan)', '…かな／…なら (how they attach)'),
    hint: b('Kata sifat な dan kata benda: buang だ sebelum かな; pada なら, だ juga tidak dipakai.', 'After な-adjectives and nouns, drop だ before かな; だ is also absent before なら.'),
    cols: [b('Kata', 'Word'), b('Bentuk kamus／dasar', 'Base form'), b('…かな', '…かな'), b('…なら', '…なら')],
    rows: [
      [b('Kata kerja', 'Verb'), '行《い》く', '行《い》くかな', '行《い》くなら'],
      [b('Kata sifat い', 'い-adjective'), '安《やす》い', '安《やす》いかな', '安《やす》いなら'],
      [b('Kata sifat な', 'な-adjective'), '静《しず》かだ', '静《しず》かかな', '静《しず》かなら'],
      [b('Kata benda', 'Noun'), '学生《がくせい》だ', '学生《がくせい》かな', '学生《がくせい》なら'],
    ],
  },
  {
    title: b('…ため(に) (sambungan)', '…ため(に) (how it attaches)'),
    cols: [b('Kata', 'Word'), b('Cara', 'Rule'), b('Contoh', 'Example')],
    rows: [
      [b('Kata kerja', 'Verb'), '普通形《ふつうけい》 ＋ ため(に)', '大雪《おおゆき》が 降《ふ》った ために'],
      [b('Kata sifat い', 'い-adjective'), 'い ＋ ため(に)', '寒《さむ》い ために'],
      [b('Kata sifat な', 'な-adjective'), 'な ＋ ため(に)', '静《しず》かな ために'],
      [b('Kata benda', 'Noun'), 'の ＋ ため(に)', '事故《じこ》の ために'],
    ],
  },
  {
    title: b('髪／目／形 を している', '髪／目／形 を している'),
    hint: b('Menggambarkan ciri tampilan orang atau benda.', 'Describes the visual traits of a person or a thing.'),
    cols: [b('Kata benda', 'Noun'), b('Contoh', 'Example')],
    rows: [
      ['髪《かみ》', ex('リンリンちゃんは 長《なが》い 髪《かみ》を して います。', 'Rin-Rin berambut panjang.', 'Little Rin-Rin has long hair.')],
      ['目《め》', ex('この 人形《にんぎょう》は 大《おお》きい 目《め》を して います。', 'Boneka ini bermata besar.', 'This doll has big eyes.')],
      ['形《かたち》', ex('このパンは 帽子《ぼうし》みたいな 形《かたち》を して いる。', 'Roti ini berbentuk seperti topi.', 'This loaf is shaped like a hat.')],
      ['色《いろ》', ex('この 花《はな》は 赤《あか》い 色《いろ》を して います。', 'Bunga ini berwarna merah.', 'This flower is red.')],
    ],
  },
  {
    title: b('Kata kerja majemuk (複合動詞): V ます語幹《ごかん》 ＋ …', 'Compound verbs (複合動詞): V ます-stem ＋ …'),
    hint: b('Buang ます dari bentuk ます, lalu sambungkan kata kerja kedua.', 'Drop ます from the ます-form, then add the second verb.'),
    cols: [b('Bentuk', 'Form'), b('Makna', 'Meaning'), b('Contoh', 'Example')],
    rows: [
      ['V ます語幹《ごかん》＋ 出《だ》す', b('Mulai mendadak', 'Suddenly begin'), '泣《な》き出《だ》す・降《ふ》り出《だ》す・走《はし》り出《だ》す'],
      ['V ます語幹《ごかん》＋ 始《はじ》める', b('Mulai', 'Begin'), '書《か》き始《はじ》める・習《なら》い始《はじ》める'],
      ['V ます語幹《ごかん》＋ 終《お》わる', b('Selesai', 'Finish'), '食《た》べ終《お》わる・読《よ》み終《お》わる'],
      ['V ます語幹《ごかん》＋ 続《つづ》ける', b('Terus', 'Continue'), '走《はし》り続《つづ》ける・働《はたら》き続《つづ》ける'],
      ['V ます語幹《ごかん》＋ 忘《わす》れる', b('Lupa melakukan', 'Forget to do'), '置《お》き忘《わす》れる・買《か》い忘《わす》れる'],
      ['V ます語幹《ごかん》＋ 合《あ》う', b('Saling／bersama', 'Each other／together'), '助《たす》け合《あ》う・話《はな》し合《あ》う'],
      ['V ます語幹《ごかん》＋ 換《か》える', b('Mengganti', 'Change'), '乗《の》り換《か》える・入《い》れ換《か》える'],
      ['V ます語幹《ごかん》＋ っぱなし', b('Dibiarkan terus (tidak baik)', 'Left as it is (undesirable)'), 'つけっぱなし・開《あ》けっぱなし'],
    ],
  },
  {
    title: b('Sambungan はずだ／ことが ある／ほうだ', 'How はずだ／ことが ある／ほうだ attach'),
    hint: b('Perhatikan bentuk kata sifat-na dan kata benda; ほうだ tidak dipakai langsung dengan kata benda.', 'Note the forms for な-adjectives and nouns; ほうだ is not used directly with nouns.'),
    cols: [b('Jenis kata', 'Word type'), b('〜はずだ', '〜はずだ'), b('〜ことが ある', '〜ことが ある'), b('〜ほうだ', '〜ほうだ')],
    rows: [
      [b('Kata kerja', 'Verb'), '来《く》る はずだ', '来《く》る ことが ある', '来《く》る ほうだ'],
      [b('い-adj.', 'い-adj.'), '高《たか》い はずだ', '高《たか》い ことが ある', '高《たか》い ほうだ'],
      [b('な-adj.', 'な-adj.'), '静《しず》かな はずだ', '静《しず》かな ことが ある', '静《しず》かな ほうだ'],
      [b('Kata benda', 'Noun'), '学生《がくせい》の はずだ', '学生《がくせい》の ことが ある', b('(tidak dipakai)', '(not used)')],
    ],
  },
  {
    title: b('Sambungan らしい／みたいだ／ようだ', 'How らしい／みたいだ／ようだ attach'),
    hint: b('Perbedaannya ada pada kata sifat-na dan kata benda.', 'The difference lies in the な-adjective and noun forms.'),
    cols: [b('Jenis kata', 'Word type'), b('〜らしい', '〜らしい'), b('〜みたいだ', '〜みたいだ'), b('〜ようだ', '〜ようだ')],
    rows: [
      [b('Kata kerja', 'Verb'), '来《く》る らしい', '来《く》る みたいだ', '来《く》る ようだ'],
      [b('い-adj.', 'い-adj.'), '高《たか》い らしい', '高《たか》い みたいだ', '高《たか》い ようだ'],
      [b('な-adj.', 'な-adj.'), '静《しず》か らしい', '静《しず》か みたいだ', '静《しず》かな ようだ'],
      [b('Kata benda', 'Noun'), '雨《あめ》 らしい', '雨《あめ》 みたいだ', '雨《あめ》の ようだ'],
    ],
  },
  {
    title: b('Sambungan おかげで／せいで／ものだから', 'How おかげで／せいで／ものだから attach'),
    hint: b('Kata sifat-na dan kata benda: だ berubah menjadi な (おかげで／せいで: N memakai の).', 'な-adjectives and nouns: だ becomes な (おかげで／せいで: nouns take の).'),
    cols: [b('Jenis kata', 'Word type'), b('〜おかげで／せいで', '〜おかげで／せいで'), b('〜ものだから', '〜ものだから'), b('Contoh', 'Example')],
    rows: [
      [b('Kata kerja', 'Verb'), '普通形《ふつうけい》 ＋ おかげで', '普通形《ふつうけい》 ＋ ものだから', ex('遅《おく》れた せいで、怒《おこ》られた。', 'Karena terlambat, saya dimarahi.', 'Because I was late, I was scolded.')],
      [b('い-adj.', 'い-adj.'), '安《やす》かった おかげで', '安《やす》かった ものだから', ex('安《やす》かった ものだから、買《か》いすぎた。', 'Karena murah, saya membeli terlalu banyak.', 'It was cheap, so I bought too much.')],
      [b('な-adj.', 'な-adj.'), '親切《しんせつ》な おかげで', '親切《しんせつ》な ものだから', ex('親切《しんせつ》な おかげで、助《たす》かった。', 'Berkat keramahan itu, saya tertolong.', 'Thanks to the kindness, I was helped out.')],
      [b('Kata benda', 'Noun'), '雨《あめ》の せいで', '雨《あめ》な ものだから', ex('雨《あめ》の せいで、試合《しあい》が 中止《ちゅうし》に なった。', 'Gara-gara hujan, pertandingan dibatalkan.', 'Because of the rain, the match was cancelled.')],
    ],
  },
  {
    title: b('Bentuk ず (〜ず／〜ずに)', 'The ず form (〜ず／〜ずに)'),
    hint: b('Dari bentuk ない: ganti ない dengan ず. Pengecualian: する → せず.', 'From the ない form: replace ない with ず. Exception: する → せず.'),
    cols: [b('Kelompok', 'Group'), b('Bentuk ない', 'ない form'), b('Bentuk ず', 'ず form'), b('Contoh', 'Example')],
    rows: [
      ['I', '書《か》かない', '書《か》かず', ex('何《なに》も 書《か》かずに 提出《ていしゅつ》した。', 'Saya mengumpulkannya tanpa menulis apa pun.', 'I handed it in without writing anything.')],
      ['II', '食《た》べない', '食《た》べず', ex('朝《あさ》ごはんを 食《た》べずに 出《で》かけた。', 'Saya berangkat tanpa sarapan.', 'I left without eating breakfast.')],
      ['III', 'しない', 'せず', ex('連絡《れんらく》せず、休《やす》んで しまった。', 'Saya absen tanpa menghubungi.', 'I stayed away without getting in touch.')],
      ['III', 'こない', 'こず', ex('友達《ともだち》は 来《こ》ず、一人《ひとり》で 待《ま》って いた。', 'Temanku tidak datang, dan aku menunggu sendirian.', 'My friend did not come and I waited alone.')],
    ],
  },
  {
    title: b('Pasif tidak langsung (間接受身)', 'Indirect passive (間接受身)'),
    hint: b('Bentuk pasifnya sama dengan pasif biasa: Kelompok I: 〜あ段 ＋ れる; Kelompok II: ＋ られる.', 'The form is the same as the ordinary passive: Group I: 〜あ-row ＋ れる; Group II: ＋ られる.'),
    cols: [b('Kelompok', 'Group'), b('Bentuk kamus', 'Dictionary'), b('Pasif', 'Passive'), b('Contoh', 'Example')],
    rows: [
      ['I', '降《ふ》る', '降《ふ》られる', ex('雨《あめ》に 降《ふ》られた。', 'Saya kehujanan.', 'I was caught in the rain.')],
      ['I', '泣《な》く', '泣《な》かれる', ex('赤《あか》ちゃんに 泣《な》かれて、困《こま》った。', 'Bayinya menangis, saya kebingungan.', 'The baby cried and I did not know what to do.')],
      ['I', '踏《ふ》む', '踏《ふ》まれる', ex('足《あし》を 踏《ふ》まれた。', 'Kaki saya terinjak.', 'My foot was stepped on.')],
      ['II', '食《た》べる', '食《た》べられる', ex('弟《おとうと》に ケーキを 食《た》べられた。', 'Kue saya dimakan adik.', 'My brother ate my cake on me.')],
      ['III', 'する', 'される', ex('隣《となり》の 人《ひと》に 大声《おおごえ》で 電話《でんわ》を される。', 'Orang di sebelah menelepon dengan suara keras.', 'The person next to me makes loud phone calls.')],
      ['III', '来《く》る', '来《こ》られる', ex('急《きゅう》に 客《きゃく》に 来《こ》られて、あわてた。', 'Tamu datang mendadak, saya panik.', 'A guest suddenly turned up and I panicked.')],
    ],
  },
  {
    title: b('〜たり〜たり (sambungan)', '〜たり〜たり (how it attaches)'),
    hint: b('Bentuk た ditambah り; akhirnya dipasangkan dengan する.', 'The た-form plus り; the sentence ends with する.'),
    cols: [b('Jenis kata', 'Word type'), b('Bentuk', 'Form'), b('Contoh', 'Example')],
    rows: [
      [b('Kata kerja', 'Verb'), 'V た形 ＋ り', ex('読《よ》んだり 書《か》いたり します。', 'Saya membaca dan menulis.', 'I read and write.')],
      [b('い-adj.', 'い-adj.'), '〜い → 〜かったり', ex('暑《あつ》かったり 寒《さむ》かったり します。', 'Kadang panas, kadang dingin.', 'It is sometimes hot and sometimes cold.')],
      [b('な-adj.', 'な-adj.'), '〜だ → 〜だったり', ex('静《しず》かだったり にぎやかだったり します。', 'Kadang sepi, kadang ramai.', 'It is sometimes quiet and sometimes lively.')],
      [b('Kata benda', 'Noun'), 'N ＋ だったり', ex('雨《あめ》だったり 晴《は》れだったり します。', 'Kadang hujan, kadang cerah.', 'It is sometimes rainy and sometimes sunny.')],
    ],
  },
]

// ---------------------------------------------------------------------
// phrases — ungkapan sopan, bahasa bisnis, kata tunjuk
// ---------------------------------------------------------------------
const phraseCards = [
  {
    title: b('Tangga kesopanan permintaan', 'Politeness ladder for requests'),
    hint: b('Makin ke bawah makin halus. Kolom kanan: versi meminta izin (kausatif).', 'The lower the row, the softer. Right column: the ask-permission (causative) version.'),
    cols: [b('Tingkat', 'Level'), b('Meminta tolong', 'Asking a favour'), b('Meminta izin', 'Asking permission')],
    rows: [
      ['1', 'Vて もらえませんか', 'Vさせて もらえませんか'],
      ['2', 'Vて いただけませんか', 'Vさせて いただけませんか'],
      ['3', 'Vて もらえないでしょうか', 'Vさせて もらえないでしょうか'],
      ['4', 'Vて いただけないでしょうか', 'Vさせて いただけないでしょうか'],
    ],
  },
  {
    title: b('Ungkapan sopan dan bahasa bisnis', 'Polite and business expressions'),
    cols: [b('Ungkapan', 'Expression'), b('Kegunaan', 'Use'), b('Contoh', 'Example')],
    rows: [
      ['お忙《いそが》しい ところ', b('Berterima kasih／meminta di saat lawan bicara sibuk', 'Thanking／asking while the other is busy'), ex('お忙《いそが》しい ところ、ありがとうございます。', 'Terima kasih di tengah kesibukan Anda.', 'Thank you in spite of your busy schedule.')],
      ['お休《やす》みの ところ', b('Mengganggu waktu istirahat', 'Disturbing someone\'s time off'), ex('お休《やす》みの ところ、失礼《しつれい》します。', 'Maaf mengganggu waktu istirahat Anda.', 'Sorry to disturb your time off.')],
      ['〜ましたら', b('"Setelah ~" (sopan)', '"Once ~" (polite)'), ex('到着《とうちゃく》しましたら、お電話《でんわ》ください。', 'Telepon kami setelah tiba.', 'Please call once you arrive.')],
      ['〜まして', b('Penghubung sopan', 'Polite connective'), ex('お越《こ》し くださいまして、ありがとうございます。', 'Terima kasih telah berkenan hadir.', 'Thank you for coming.')],
      ['〜とのことです', b('Menyampaikan kabar (tulisan／bisnis)', 'Reporting (written／business)'), ex('山田《やまだ》さんから よろしくとの ことです。', 'Pak Yamada menitipkan salam.', 'Mr. Yamada sends his regards.')],
      ['〜である', b('Gaya tulisan formal', 'Formal written style'), ex('日本《にほん》の 首都《しゅと》は 東京《とうきょう》である。', 'Ibu kota Jepang adalah Tokyo.', 'The capital of Japan is Tokyo.')],
      ['あいにく〜ですが', b('Sayang sekali／mohon maaf (menyampaikan kabar kurang baik)', 'Unfortunately ~ (giving unwelcome news)'), ex('あいにく、部長《ぶちょう》は 出張《しゅっちょう》して おります。', 'Mohon maaf, Kepala Bagian sedang dinas luar.', 'I am afraid the department head is away on business.')],
      ['恐《おそ》れ入《い》りますが、〜', b('Permisi, mohon maaf merepotkan (pembuka permintaan sopan)', 'Excuse me, but ~ (polite opener for a request)'), ex('恐《おそ》れ入《い》りますが、少《すこ》し あとで いらっしゃって いただけませんか。', 'Mohon maaf, bisakah Anda datang sedikit lebih lambat?', 'Sorry to trouble you, but could you come a little later?')],
      ['わざわざ〜ていただいたのに、〜', b('Meminta maaf atas jerih payah orang yang tak bisa dibalas', 'Apologising for not honouring someone\'s effort'), ex('わざわざ 来《き》て いただいたのに、お会《あ》い できませんでした。', 'Anda sudah repot-repot datang, tetapi kita tidak sempat bertemu.', 'You went out of your way to come, yet we could not meet.')],
      ['できれば、〜', b('Jika memungkinkan (melunakkan permintaan)', 'If possible (softening a request)'), ex('できれば、明日《あした》までに ご連絡《れんらく》 いただけないでしょうか。', 'Jika memungkinkan, mohon kabari kami sebelum besok.', 'If possible, could you let us know by tomorrow?')],
      ['〜ということですね', b('Mengonfirmasi isi pesan', 'Confirming the content of a message'), ex('明日《あした》の 午後《ごご》 三時《さんじ》に いらっしゃると いう ことですね。', 'Jadi Anda akan datang besok pukul tiga sore, ya.', 'So you will be coming tomorrow at three, correct?')],
    ],
  },
  {
    title: b('Kata tunjuk menurut konteks (あ／そ)', 'Contextual demonstratives (あ／そ)'),
    cols: [b('Situasi', 'Situation'), b('Pilih', 'Use'), b('Contoh', 'Example')],
    rows: [
      [b('Pembicara dan pendengar sama-sama tahu', 'Both speaker and listener know it'), 'あの／あれ／あそこ', ex('あの 店《みせ》、もう 行《い》った？', 'Toko itu, sudah pernah ke sana?', 'Have you been to that shop?')],
      [b('Hanya satu pihak yang tahu', 'Only one of them knows'), 'その／それ／そこ', ex('鈴木《すずき》さんという 人《ひと》に 会《あ》ったよ。……その 人《ひと》、だれ？', 'Aku bertemu Suzuki. ……Orang itu siapa?', 'I met someone called Suzuki. ...Who is that?')],
      [b('Merujuk kalimat sebelumnya dalam tulisan', 'Pointing back to a previous sentence in writing'), 'その／それ／そこ', ex('公園《こうえん》が あります。そこで 市《いち》が 開《ひら》かれます。', 'Ada taman. Di sanalah pasar digelar.', 'There is a park. A market is held there.')],
    ],
  },
  {
    title: b('お〜です (bentuk hormat 〜ている)', 'お〜です (honorific 〜ている)'),
    hint: b('Menghormati tindakan／keadaan orang lain; tidak dipakai untuk diri sendiri.', 'Honours another person\'s action／state; never used for oneself.'),
    cols: [b('Biasa', 'Plain'), b('Bentuk hormat', 'Honorific'), b('Catatan／contoh', 'Note／example')],
    rows: [
      ['読《よ》んで いる', 'お読《よ》みです', ex('何《なに》を お読《よ》みですか。', 'Anda sedang membaca apa?', 'What are you reading?')],
      ['持《も》って いる', 'お持《も》ちです', ex('いい 時計《とけい》を お持《も》ちですね。', 'Jam Anda bagus ya.', 'You have a nice watch.')],
      ['ある (時間《じかん》が)', 'おありです', ex('お時間《じかん》が おありですか。', 'Apakah Anda ada waktu?', 'Are you free at the moment?')],
      ['着《つ》く／帰《かえ》る', 'お着《つ》きです／お帰《かえ》りです', ex('部長《ぶちょう》は 何時《なんじ》に お着《つ》きですか。', 'Pukul berapa Kepala Bagian tiba?', 'What time will the Department Manager arrive?')],
      ['行《い》く・いる・来《く》る', 'おいでです', b('Bentuk khusus', 'Special form')],
      ['来《く》る', 'お越《こ》しです／お見《み》えです', b('Bentuk khusus', 'Special form')],
      ['食《た》べる', 'お召《め》し上《あ》がりです', b('Bentuk khusus', 'Special form')],
      ['着《き》る', 'お召《め》しです', b('Bentuk khusus', 'Special form')],
      ['寝《ね》る', 'お休《やす》みです', b('Bentuk khusus', 'Special form')],
      ['住《す》んで いる', 'お住《す》まいです', b('Bentuk khusus', 'Special form')],
      ['知《し》って いる', 'ご存《ぞん》じです', b('Bentuk khusus', 'Special form')],
    ],
  },
  {
    title: b('Permintaan: Vて ください ↔ Vて くれ', 'Requests: Vて ください ↔ Vて くれ'),
    hint: b('Vて くれ sering dipakai pria kepada yang lebih rendah dan terdengar agak kasar.', 'Vて くれ is mostly used by men to someone junior and sounds a little rough.'),
    cols: [b('Pola', 'Pattern'), b('Kesan', 'Impression'), b('Contoh', 'Example')],
    rows: [
      ['Vて ください', b('Sopan, langsung kepada lawan bicara', 'Polite, said directly to the listener'), ex('窓《まど》を 閉《し》めて ください。', 'Tolong tutup jendelanya.', 'Please close the window.')],
      ['Vて くれ', b('Lugas／kasar; atasan kepada bawahan', 'Blunt／rough; superior to junior'), ex('田中《たなか》くん、この 資料《しりょう》を コピーして 来《き》てくれ。', 'Tanaka, tolong fotokopi dokumen ini.', 'Tanaka, go and copy these materials.')],
      ['Vて くれと 言《い》う', b('Menyampaikan permintaan secara tidak langsung', 'Reporting a request indirectly'), ex('田中《たなか》さんは お母《かあ》さんに 七時《しちじ》に 起《お》こして くれと 言《い》いました。', 'Pak Tanaka meminta ibunya membangunkannya jam tujuh.', 'Mr. Tanaka asked his mother to wake him at seven.')],
    ],
  },
  {
    title: b('Kata tunjuk menurut konteks (こ／そ)', 'Contextual demonstratives (こ／そ)'),
    cols: [b('Situasi', 'Situation'), b('Pilih', 'Use'), b('Contoh', 'Example')],
    rows: [
      [b('Menunjuk isi yang akan disebut kemudian', 'Pointing ahead to what comes next'), 'こんな／この／こう／これ', ex('新聞《しんぶん》に こんな 記事《きじ》が ありました。この 町《まち》に 大《おお》きな 図書館《としょかん》が できるそうだ。', 'Ada berita seperti ini di koran: katanya akan dibangun perpustakaan besar di kota ini.', 'There was an article like this in the paper: a large library is going to be built in this town.')],
      [b('Merujuk hal yang baru disebut (tulisan)', 'Pointing back to what was just mentioned (writing)'), 'その／それ／そこ', ex('先月《せんげつ》、新《あたら》しい 駅《えき》が できました。そこから 空港《くうこう》まで 十分《じゅっぷん》です。', 'Bulan lalu dibuka stasiun baru. Dari sana ke bandara sepuluh menit.', 'A new station opened last month. It is ten minutes from there to the airport.')],
    ],
  },
  {
    title: b('Tangga saran', 'Ladder of suggestions'),
    hint: b('Makin ke bawah makin halus. Kepada atasan pilih bentuk di bagian bawah.', 'The lower the row, the softer. To a superior choose a form from the lower rows.'),
    cols: [b('Ungkapan', 'Expression'), b('Kepada siapa', 'To whom'), b('Contoh', 'Example')],
    rows: [
      ['〜たら？', b('Teman, keluarga, yang lebih muda', 'Friends, family, juniors'), ex('今日《きょう》は 早《はや》く 寝《ね》たら？', 'Hari ini tidur lebih awal saja?', 'How about going to bed early today?')],
      ['〜たら どう？', b('Teman, keluarga, yang lebih muda', 'Friends, family, juniors'), ex('薬《くすり》を 飲《の》んで みたら どう？', 'Bagaimana kalau coba minum obat?', 'Why not try taking some medicine?')],
      ['〜たら どうですか', b('Rekan, orang yang baru dikenal', 'Colleagues, acquaintances'), ex('先生《せんせい》に 相談《そうだん》したら どうですか。', 'Bagaimana kalau berkonsultasi dengan guru?', 'Why not talk to your teacher?')],
      ['〜たら いかがですか', b('Sopan (pelanggan, tamu)', 'Polite (customers, guests)'), ex('こちらに お掛《か》けに なったら いかがですか。', 'Bagaimana kalau Anda duduk di sini?', 'Would you like to sit here?')],
      ['Nなんか どうですか／いかがですか', b('Menawarkan contoh dengan halus', 'Offering an example softly'), ex('お土産《みやげ》には、これなんか いかがでしょうか。', 'Untuk oleh-oleh, bagaimana kalau yang ini?', 'For a souvenir, how about this one?')],
    ],
  },
  {
    title: b('Harapan dan doa', 'Wishes and prayers'),
    cols: [b('Ungkapan', 'Expression'), b('Kegunaan', 'Use'), b('Contoh', 'Example')],
    rows: [
      ['〜ますように', b('Berharap sesuatu terjadi', 'Hoping something will happen'), ex('どうか 試験《しけん》に 合格《ごうかく》できますように。', 'Semoga saya bisa lulus ujian.', 'Please let me pass the exam.')],
      ['〜ませんように', b('Berharap sesuatu tidak terjadi', 'Hoping something will not happen'), ex('どうか 大《おお》きな 事故《じこ》が 起《お》きませんように。', 'Semoga tidak terjadi kecelakaan besar.', 'I hope no major accident occurs.')],
      ['〜と いい (ね)', b('Harapan ringan dalam percakapan', 'A light hope in conversation'), ex('明日《あした》、晴《は》れると いいね。', 'Semoga besok cerah ya.', 'I hope it clears up tomorrow.')],
    ],
  },
  {
    title: b('Alasan, reaksi, dan kesimpulan', 'Reasons, reactions and conclusions'),
    cols: [b('Ungkapan', 'Expression'), b('Kegunaan', 'Use'), b('Contoh', 'Example')],
    rows: [
      ['〜ものですから', b('Dalih sopan atau alasan saat meminta maaf', 'A polite excuse or reason when apologising'), ex('すみません、道《みち》が 混《こ》んで いたものですから。', 'Maaf, soalnya jalan macet.', 'I am sorry, the roads were crowded.')],
      ['おかげさまで', b('Berterima kasih atas bantuan atau perhatian orang lain', 'Thanks for someone\'s help or concern'), ex('お元気《げんき》ですか。…おかげさまで、元気《げんき》です。', 'Apa kabar? ...Berkat doa Anda, saya sehat.', 'How are you? ...Thanks for asking, I am well.')],
      ['そんな はずは ありません', b('Menolak ucapan orang lain dengan tegas', 'Firmly rejecting what someone said'), ex('山田《やまだ》さんが 約束《やくそく》を 忘《わす》れたそうです。…そんな はずは ありません。', 'Katanya Pak Yamada lupa janji. ...Itu tidak mungkin.', 'I hear Mr. Yamada forgot the appointment. ...That cannot be true.')],
      ['〜と いう ことは、〜と いう ことですね', b('Menegaskan kesimpulan dari informasi yang diberikan', 'Confirming a conclusion drawn from given information'), ex('部長《ぶちょう》が 来《こ》られないと いう ことは、会議《かいぎ》は 延期《えんき》と いう ことですね。', 'Berarti kalau kepala bagian tidak bisa hadir, rapat ditunda ya.', 'So if the manager cannot come, that means the meeting is postponed.')],
    ],
  },
]

const parts = {
  patterns: patternCards,
  compare: compareCards,
  forms: formsCards,
  phrases: phraseCards,
}

const patternCols = computed(() => [t('lampiran.n4_col_pattern'), t('lampiran.n4_col_use'), t('lampiran.n4_col_example')])

const isExample = cell => cell && typeof cell === 'object' && 'ja' in cell
const isBilingual = cell => cell && typeof cell === 'object' && 'id' in cell && !('ja' in cell)

function colLabel(col) {
  return typeof col === 'string' ? col : tr(col)
}
</script>

<template>
  <div>
    <VCard
      v-for="(card, ci) in parts[part]"
      :key="`${part}-${ci}`"
      :title="tr(card.title)"
      :subtitle="card.hint ? tr(card.hint) : undefined"
      class="mb-4"
    >
      <VTable
        density="comfortable"
        style="overflow-x: auto;"
      >
        <thead>
          <tr>
            <th
              v-for="(col, hi) in (part === 'patterns' ? patternCols : card.cols)"
              :key="`h-${hi}`"
            >
              {{ colLabel(col) }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(row, ri) in card.rows"
            :key="`r-${ci}-${ri}`"
          >
            <td
              v-for="(cell, ki) in row"
              :key="`c-${ci}-${ri}-${ki}`"
              :class="ki === 0 ? 'font-weight-medium' : ''"
            >
              <template v-if="isExample(cell)">
                <RubyText :text="cell.ja" />
                <div class="text-medium-emphasis text-body-2">
                  {{ tr(cell) }}
                </div>
              </template>
              <RubyText
                v-else-if="isBilingual(cell)"
                :text="tr(cell)"
              />
              <RubyText
                v-else
                :text="cell"
              />
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>
  </div>
</template>
