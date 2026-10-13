// Membaca (読解) — bacaan pendek per pelajaran untuk N5, N4 dan N3.
//
// Semua teks ditulis baru untuk aplikasi ini (bukan salinan buku). Kanji memakai furigana
// 漢字《かんじ》 (dirender RubyText), tanpa romaji.
//
// Setiap level punya 12 pelajaran (MEMBACA_LESSON_COUNT). N3 = nomor pelajaran N3 apa adanya.
// N5 dan N4 aslinya 25 pelajaran, jadi digabung menjadi 12 — lihat MEMBACA_SOURCE_LESSONS.
// Kunci progres: `n{level}l{n}` (mis. n5l1, n4l1, n3l1), lihat MembacaController dan
// NotificationService::setLabel.
//
// Menambah pelajaran: tambahkan satu entri di MEMBACA[level] dengan kunci nomor pelajaran.
// Pelajaran yang belum punya entri tampil sebagai "segera hadir".
//
// Bentuk satu pelajaran:
//   title      { id, en }            judul bacaan (Jepang ada di passage.title)
//   focus      { id, en }            pola/kosakata yang dilatih
//   before     { id, en }            pertanyaan pemanasan sebelum membaca (tidak dinilai)
//   passage    { title, paragraphs: [{ ja, id, en }] }
//   vocab      [{ ja, reading, id, en }]   kata penting pada bacaan
//   questions  [{ type: 'mc' | 'tf', q, choices?, answer, explain }]
//                mc: answer = indeks pilihan benar; tf: answer = true | false (○ / ×)
//   challenge  { id, en }            tantangan menulis/berbicara (tidak dinilai)

export const MEMBACA_LEVELS = ['N5', 'N4', 'N3']
export const MEMBACA_LESSON_COUNT = 12

// Pelajaran Membaca → pelajaran asli di Tata Bahasa/Kosakata yang dicakup.
// N3 tidak digabung (1 → 1).
export const MEMBACA_SOURCE_LESSONS = {
  N5: { 1: [1, 2], 2: [3, 4], 3: [5, 6], 4: [7, 8], 5: [9, 10], 6: [11, 12], 7: [13, 14], 8: [15, 16], 9: [17, 18], 10: [19, 20], 11: [21, 22], 12: [23, 24, 25] },
  N4: { 1: [1, 2], 2: [3, 4], 3: [5, 6], 4: [7, 8], 5: [9, 10], 6: [11, 12], 7: [13, 14], 8: [15, 16], 9: [17, 18], 10: [19, 20], 11: [21, 22], 12: [23, 24, 25] },
  N3: Object.fromEntries(Array.from({ length: 12 }, (_, i) => [i + 1, [i + 1]])),
}

export const MEMBACA = {
  // ---------------------------------------------------------------- N5
  N5: {
    1: {
      title: { id: 'Kelas bahasa Jepang', en: 'The Japanese class' },
      focus: {
        id: 'これ・それ・あれ、この・その・あの、ここ・そこ・あそこ、N は N です、N の N、だれ・何',
        en: 'これ・それ・あれ, この・その・あの, ここ・そこ・あそこ, N は N です, N の N, だれ・何',
      },
      before: {
        id: 'Ada apa saja di dalam kelasmu? Sebutkan tiga benda dalam bahasa Jepang.',
        en: 'What is in your classroom? Name three things in Japanese.',
      },
      passage: {
        title: '日本語《にほんご》の 教室《きょうしつ》',
        paragraphs: [
          {
            ja: 'ここは 日本語《にほんご》の 教室《きょうしつ》です。わたしは 学生《がくせい》です。先生《せんせい》は 山田《やまだ》さんです。',
            id: 'Di sini kelas bahasa Jepang. Saya seorang pelajar. Guru saya Ibu/Bapak Yamada.',
            en: 'This is a Japanese class. I am a student. My teacher is Mr./Ms. Yamada.',
          },
          {
            ja: 'あそこは 食堂《しょくどう》です。あの 人《ひと》は ミラーさんです。ミラーさんは 会社員《かいしゃいん》です。',
            id: 'Di sana kantin. Orang itu Miller. Miller adalah karyawan perusahaan.',
            en: 'Over there is the cafeteria. That person is Miller. Miller is a company employee.',
          },
          {
            ja: 'これは わたしの 本《ほん》です。それは ミラーさんの 辞書《じしょ》です。あれは 先生《せんせい》の かばんです。',
            id: 'Ini buku saya. Itu kamus Miller. Itu (di sana) tas guru.',
            en: 'This is my book. That is Miller’s dictionary. That over there is the teacher’s bag.',
          },
          {
            ja: 'その かさは だれのですか。…わたしのです。',
            id: 'Payung itu punya siapa? … Punya saya.',
            en: 'Whose umbrella is that? … It is mine.',
          },
        ],
      },
      vocab: [
        { ja: '教室', reading: 'きょうしつ', id: 'ruang kelas', en: 'classroom' },
        { ja: '食堂', reading: 'しょくどう', id: 'kantin', en: 'cafeteria' },
        { ja: '会社員', reading: 'かいしゃいん', id: 'karyawan perusahaan', en: 'company employee' },
        { ja: '辞書', reading: 'じしょ', id: 'kamus', en: 'dictionary' },
        { ja: 'かさ', reading: 'かさ', id: 'payung', en: 'umbrella' },
        { ja: 'だれの', reading: 'だれの', id: 'milik siapa', en: 'whose' },
      ],
      questions: [
        {
          type: 'mc',
          q: '先生《せんせい》は だれですか。',
          choices: ['山田《やまだ》さん', 'ミラーさん', 'わたし', '学生《がくせい》'],
          answer: 0,
          explain: { id: 'Paragraf 1: 先生は 山田さんです。', en: 'Paragraph 1: 先生は 山田さんです。' },
        },
        {
          type: 'mc',
          q: 'ミラーさんは 何《なん》ですか。',
          choices: ['学生《がくせい》', '先生《せんせい》', '会社員《かいしゃいん》', '山田《やまだ》さん'],
          answer: 2,
          explain: { id: 'Paragraf 2: ミラーさんは 会社員です。', en: 'Paragraph 2: ミラーさんは 会社員です。' },
        },
        {
          type: 'mc',
          q: '「ここ」は 何《なん》ですか。',
          choices: ['食堂《しょくどう》', '日本語《にほんご》の 教室《きょうしつ》', '会社《かいしゃ》', '山田《やまだ》さんの うち'],
          answer: 1,
          explain: { id: 'ここ = tempat pembicara berada: 日本語の 教室.', en: 'ここ = where the speaker is: the Japanese classroom.' },
        },
        {
          type: 'tf',
          q: 'あそこは 食堂《しょくどう》です。',
          answer: true,
          explain: { id: 'Paragraf 2: あそこは 食堂です。', en: 'Paragraph 2: あそこは 食堂です。' },
        },
        {
          type: 'tf',
          q: '辞書《じしょ》は わたしのです。',
          answer: false,
          explain: { id: 'Kamus itu milik ミラーさん. Yang milik saya adalah bukunya.', en: 'The dictionary is Miller’s. The book is mine.' },
        },
        {
          type: 'mc',
          q: '先生《せんせい》の かばんは どれですか。',
          choices: ['これ', 'それ', 'あれ', 'ここ'],
          answer: 2,
          explain: { id: 'Paragraf 3: あれは 先生の かばんです。', en: 'Paragraph 3: あれは 先生の かばんです。' },
        },
      ],
      challenge: {
        id: 'Tulis 3 kalimat tentang benda di dekatmu memakai これ・それ・あれ.',
        en: 'Write three sentences about things near you using これ・それ・あれ.',
      },
    },
    2: {
      title: { id: 'Sehari di toserba', en: 'A day at the department store' },
      focus: {
        id: 'ここ・そこ・あそこ、どこの、N の（asal）、いくら、〜時〜分、V ます／ました、〜に、〜から〜まで、N と N、〜ね',
        en: 'ここ・そこ・あそこ, どこの, N の (origin), いくら, 〜時〜分, V ます／ました, 〜に, 〜から〜まで, N と N, 〜ね',
      },
      before: {
        id: 'Di toserba, apa yang biasanya kamu cari? Bagaimana menanyakan letak toilet dalam bahasa Jepang?',
        en: 'What do you usually look for in a department store? How would you ask where the toilet is in Japanese?',
      },
      passage: {
        title: 'デパートの 一日《いちにち》',
        paragraphs: [
          {
            ja: 'ここは さくらデパートです。トイレは あそこです。エレベーターは そこです。',
            id: 'Di sini Toserba Sakura. Toiletnya di sana (jauh dari kita). Liftnya di situ (dekat Anda).',
            en: 'This is Sakura Department Store. The toilet is over there. The elevator is right there by you.',
          },
          {
            ja: '時計《とけい》の 売《う》り場《ば》は 2階《かい》です。「この 時計《とけい》は どこのですか。」「スイスのです。3万《まん》円《えん》です。」「あの 時計《とけい》は？」「日本《にほん》のです。5千《せん》円《えん》です。」',
            id: 'Lantai jam ada di lantai 2. “Jam ini buatan mana?” “Buatan Swiss. 30.000 yen.” “Kalau jam yang itu?” “Buatan Jepang. 5.000 yen.”',
            en: 'The watch department is on the 2nd floor. “Where is this watch from?” “It’s Swiss. It’s 30,000 yen.” “And that one?” “It’s Japanese. It’s 5,000 yen.”',
          },
          {
            ja: '「デパートは 10時《じ》から 8時《じ》までですね。」「はい、そうです。水曜日《すいようび》は 休《やす》みです。」',
            id: '“Toserba buka dari pukul 10 sampai pukul 8, ya.” “Ya, benar. Hari Rabu libur.”',
            en: '“The store is open from 10 to 8, right?” “Yes, that’s right. Wednesday is a day off.”',
          },
          {
            ja: '田中《たなか》さんは この デパートの 人《ひと》です。毎朝《まいあさ》 7時《じ》に 起《お》きます。9時《じ》半《はん》から 8時《じ》半《はん》まで 働《はたら》きます。昼休《ひるやす》みは 1時《じ》から 2時《じ》までです。毎晩《まいばん》 12時《じ》に 寝《ね》ます。水曜日《すいようび》と 日曜日《にちようび》は 休《やす》みです。',
            id: 'Tanaka bekerja di toserba ini. Setiap pagi ia bangun pukul 7. Ia bekerja dari pukul 9.30 sampai 8.30. Istirahat siang dari pukul 1 sampai pukul 2. Setiap malam ia tidur pukul 12. Hari Rabu dan Minggu ia libur.',
            en: 'Mr./Ms. Tanaka works at this store. Every morning they get up at 7. They work from 9:30 to 8:30. The lunch break is from 1 to 2. Every night they go to bed at 12. Wednesday and Sunday are days off.',
          },
          {
            ja: 'きのう、田中《たなか》さんは 休《やす》みました。9時《じ》まで 寝《ね》ました。きょうは 7時《じ》に 起《お》きました。',
            id: 'Kemarin Tanaka libur. Ia tidur sampai pukul 9. Hari ini ia bangun pukul 7.',
            en: 'Yesterday Tanaka had the day off and slept until 9. Today they got up at 7.',
          },
        ],
      },
      vocab: [
        { ja: '売り場', reading: 'うりば', id: 'tempat/bagian penjualan', en: 'sales floor, counter' },
        { ja: '階', reading: 'かい', id: 'lantai (bangunan)', en: 'floor, storey' },
        { ja: '時計', reading: 'とけい', id: 'jam', en: 'watch, clock' },
        { ja: '昼休み', reading: 'ひるやすみ', id: 'istirahat siang', en: 'lunch break' },
        { ja: '毎朝', reading: 'まいあさ', id: 'setiap pagi', en: 'every morning' },
        { ja: '起きます', reading: 'おきます', id: 'bangun', en: 'to get up' },
        { ja: '働きます', reading: 'はたらきます', id: 'bekerja', en: 'to work' },
        { ja: '寝ます', reading: 'ねます', id: 'tidur', en: 'to sleep, to go to bed' },
        { ja: '休みます', reading: 'やすみます', id: 'libur, beristirahat', en: 'to rest, to take a day off' },
      ],
      questions: [
        {
          type: 'mc',
          q: '時計《とけい》の 売《う》り場《ば》は 何階《なんがい》ですか。',
          choices: ['1階《かい》', '2階《かい》', '3階《かい》', '4階《かい》'],
          answer: 1,
          explain: { id: 'Paragraf 2: 時計の 売り場は 2階です。', en: 'Paragraph 2: 時計の 売り場は 2階です。' },
        },
        {
          type: 'mc',
          q: 'スイスの 時計《とけい》は いくらですか。',
          choices: ['5千《せん》円《えん》', '3万《まん》円《えん》', '3千《せん》円《えん》', '5万《まん》円《えん》'],
          answer: 1,
          explain: { id: 'Paragraf 2: スイスのです。3万円です。(5千円 adalah jam Jepang.)', en: 'Paragraph 2: the Swiss one is 3万円. (5千円 is the Japanese one.)' },
        },
        {
          type: 'mc',
          q: 'デパートは 何時《なんじ》から 何時《なんじ》までですか。',
          choices: [
            '9時《じ》から 5時《じ》まで',
            '10時《じ》から 6時《じ》まで',
            '10時《じ》から 8時《じ》まで',
            '9時《じ》半《はん》から 8時《じ》半《はん》まで',
          ],
          answer: 2,
          explain: { id: 'Paragraf 3: 10時から 8時までです。(9時半〜8時半 adalah jam kerja Tanaka.)', en: 'Paragraph 3: 10 to 8. (9:30–8:30 is Tanaka’s working time.)' },
        },
        {
          type: 'tf',
          q: '水曜日《すいようび》は デパートの 休《やす》みです。',
          answer: true,
          explain: { id: 'Paragraf 3: 水曜日は 休みです。', en: 'Paragraph 3: 水曜日は 休みです。' },
        },
        {
          type: 'tf',
          q: '田中《たなか》さんは 毎朝《まいあさ》 6時《じ》に 起《お》きます。',
          answer: false,
          explain: { id: 'Paragraf 4: 毎朝 7時に 起きます.', en: 'Paragraph 4: 毎朝 7時に 起きます。' },
        },
        {
          type: 'mc',
          q: '田中《たなか》さんは きのう 何時《なんじ》まで 寝《ね》ましたか。',
          choices: ['7時《じ》', '8時《じ》', '9時《じ》', '10時《じ》'],
          answer: 2,
          explain: { id: 'Paragraf 5: 9時まで 寝ました。', en: 'Paragraph 5: 9時まで 寝ました。' },
        },
      ],
      challenge: {
        id: 'Tulis jadwal harianmu memakai 〜に dan 〜から〜まで. Contoh: 毎朝 6時に 起きます。',
        en: 'Write your daily schedule using 〜に and 〜から〜まで. Example: 毎朝 6時に 起きます。',
      },
    },
    3: {
      title: { id: 'Perjalanan hari Minggu', en: 'A Sunday trip' },
      focus: {
        id: '〜へ 行きます・帰ります、〜で（alat・kendaraan）、〜と（teman）、〜を V、〜で（tempat）、〜ましょう、いつ、tanggal',
        en: '〜へ 行きます・帰ります, 〜で (vehicle), 〜と (with), 〜を V, 〜で (place), 〜ましょう, いつ, dates',
      },
      before: {
        id: 'Ke mana kamu biasa pergi di akhir pekan, dan dengan siapa? Naik apa?',
        en: 'Where do you usually go on weekends, and with whom? How do you get there?',
      },
      passage: {
        title: '日曜日《にちようび》の 旅行《りょこう》',
        paragraphs: [
          {
            ja: 'あしたは 10月《がつ》 20日《はつか》、日曜日《にちようび》です。わたしは 友達《ともだち》と 京都《きょうと》へ 行《い》きます。',
            id: 'Besok tanggal 20 Oktober, hari Minggu. Saya pergi ke Kyoto bersama teman.',
            en: 'Tomorrow is Sunday, October 20th. I am going to Kyoto with a friend.',
          },
          {
            ja: '電車《でんしゃ》で 行《い》きます。駅《えき》で 切符《きっぷ》を 買《か》います。',
            id: 'Kami pergi naik kereta. Di stasiun saya membeli tiket.',
            en: 'We go by train. I buy the tickets at the station.',
          },
          {
            ja: '京都《きょうと》で お寺《てら》の 写真《しゃしん》を とります。昼《ひる》は レストランで ご飯《はん》を 食《た》べます。',
            id: 'Di Kyoto saya memotret kuil. Siang hari kami makan nasi di restoran.',
            en: 'In Kyoto I take photos of temples. At noon we eat a meal at a restaurant.',
          },
          {
            ja: '「いつ 帰《かえ》りますか。」「7時《じ》に 帰《かえ》ります。」「じゃ、一緒《いっしょ》に 晩《ばん》ご飯《はん》を 食《た》べましょう。」',
            id: '“Kapan pulang?” “Pulang pukul 7.” “Kalau begitu, ayo kita makan malam bersama.”',
            en: '“When will you come home?” “I will be home at 7.” “Then let’s have dinner together.”',
          },
        ],
      },
      vocab: [
        { ja: '切符', reading: 'きっぷ', id: 'tiket', en: 'ticket' },
        { ja: '電車', reading: 'でんしゃ', id: 'kereta listrik', en: 'train' },
        { ja: '友達', reading: 'ともだち', id: 'teman', en: 'friend' },
        { ja: '写真', reading: 'しゃしん', id: 'foto', en: 'photo' },
        { ja: '昼', reading: 'ひる', id: 'siang', en: 'noon, daytime' },
        { ja: 'ご飯', reading: 'ごはん', id: 'nasi, makan', en: 'rice, meal' },
        { ja: '一緒に', reading: 'いっしょに', id: 'bersama-sama', en: 'together' },
        { ja: '帰ります', reading: 'かえります', id: 'pulang', en: 'to return home' },
      ],
      questions: [
        {
          type: 'mc',
          q: '「わたし」は だれと 京都《きょうと》へ 行《い》きますか。',
          choices: ['友達《ともだち》', '先生《せんせい》', '家族《かぞく》', 'ひとり'],
          answer: 0,
          explain: { id: 'Paragraf 1: 友達と 京都へ 行きます.', en: 'Paragraph 1: 友達と 京都へ 行きます。' },
        },
        {
          type: 'mc',
          q: '何《なん》で 行《い》きますか。',
          choices: ['電車《でんしゃ》', 'バス', 'タクシー', '飛行機《ひこうき》'],
          answer: 0,
          explain: { id: 'Paragraf 2: 電車で 行きます.', en: 'Paragraph 2: 電車で 行きます。' },
        },
        {
          type: 'mc',
          q: '駅《えき》で 何《なに》を 買《か》いますか。',
          choices: ['切符《きっぷ》', '本《ほん》', '写真《しゃしん》', 'ご飯《はん》'],
          answer: 0,
          explain: { id: 'Paragraf 2: 駅で 切符を 買います.', en: 'Paragraph 2: 駅で 切符を 買います。' },
        },
        {
          type: 'tf',
          q: '昼《ひる》は レストランで ご飯《はん》を 食《た》べます。',
          answer: true,
          explain: { id: 'Paragraf 3: 昼は レストランで ご飯を 食べます.', en: 'Paragraph 3: 昼は レストランで ご飯を 食べます。' },
        },
        {
          type: 'tf',
          q: '京都《きょうと》で 本《ほん》を 買《か》います。',
          answer: false,
          explain: { id: 'Di Kyoto ia memotret kuil (写真を とります), bukan membeli buku.', en: 'In Kyoto the writer takes photos of temples; no book is bought.' },
        },
        {
          type: 'mc',
          q: '何時《なんじ》に 帰《かえ》りますか。',
          choices: ['5時《じ》', '6時《じ》', '7時《じ》', '8時《じ》'],
          answer: 2,
          explain: { id: 'Paragraf 4: 7時に 帰ります.', en: 'Paragraph 4: 7時に 帰ります。' },
        },
      ],
      challenge: {
        id: 'Tulis rencana akhir pekanmu: ke mana, dengan siapa, naik apa, dan apa yang kamu lakukan di sana.',
        en: 'Write your weekend plan: where, with whom, by what, and what you do there.',
      },
    },
    4: {
      title: { id: 'Hadiah ulang tahun', en: 'A birthday present' },
      focus: {
        id: 'あげます・もらいます、もう〜ました／まだです、Adj い・な ＋ N、Adj は〜です、そして、〜が、〜語で',
        en: 'あげます・もらいます, もう〜ました／まだです, い・な adjectives + N, そして, 〜が, 〜語で',
      },
      before: {
        id: 'Hadiah apa yang pernah kamu berikan kepada temanmu? Apa yang kamu terima?',
        en: 'What present have you given a friend? What have you received?',
      },
      passage: {
        title: '誕生日《たんじょうび》の プレゼント',
        paragraphs: [
          {
            ja: 'あしたは 友達《ともだち》の 誕生日《たんじょうび》です。わたしは 友達《ともだち》に きれいな 花《はな》を あげます。',
            id: 'Besok hari ulang tahun teman saya. Saya memberikan bunga yang cantik kepada teman saya.',
            en: 'Tomorrow is my friend’s birthday. I will give my friend some pretty flowers.',
          },
          {
            ja: 'この 花《はな》は 母《はは》に もらいました。',
            id: 'Bunga ini saya terima dari ibu saya.',
            en: 'I received these flowers from my mother.',
          },
          {
            ja: 'ケーキも あげます。もう ケーキを 買《か》いました。「どんな ケーキですか。」「小《ちい》さいですが、おいしいです。そして、安《やす》いです。」',
            id: 'Saya juga memberikan kue. Kuenya sudah saya beli. “Kue seperti apa?” “Kecil, tetapi enak. Dan murah.”',
            en: 'I will give a cake too. I have already bought the cake. “What kind of cake is it?” “It is small, but delicious. And it is cheap.”',
          },
          {
            ja: 'カードは 日本語《にほんご》で 書《か》きます。「たんじょうび おめでとう。」でも、カードは まだです。',
            id: 'Kartunya saya tulis dalam bahasa Jepang: “Selamat ulang tahun.” Tetapi kartunya belum.',
            en: 'I write the card in Japanese: “Happy birthday.” But the card is not done yet.',
          },
        ],
      },
      vocab: [
        { ja: '誕生日', reading: 'たんじょうび', id: 'hari ulang tahun', en: 'birthday' },
        { ja: '花', reading: 'はな', id: 'bunga', en: 'flower' },
        { ja: 'あげます', reading: 'あげます', id: 'memberi', en: 'to give' },
        { ja: 'もらいます', reading: 'もらいます', id: 'menerima', en: 'to receive' },
        { ja: 'もう', reading: 'もう', id: 'sudah', en: 'already' },
        { ja: 'まだ', reading: 'まだ', id: 'belum, masih', en: 'not yet, still' },
        { ja: 'きれい', reading: 'きれい', id: 'cantik, bersih', en: 'pretty, clean' },
        { ja: '日本語', reading: 'にほんご', id: 'bahasa Jepang', en: 'Japanese language' },
      ],
      questions: [
        {
          type: 'mc',
          q: 'あしたは だれの 誕生日《たんじょうび》ですか。',
          choices: ['友達《ともだち》', '母《はは》', '先生《せんせい》', 'わたし'],
          answer: 0,
          explain: { id: 'Paragraf 1: あしたは 友達の 誕生日です.', en: 'Paragraph 1: あしたは 友達の 誕生日です。' },
        },
        {
          type: 'mc',
          q: '友達《ともだち》に 何《なに》を あげますか。',
          choices: ['花《はな》と ケーキ', '本《ほん》と かさ', '時計《とけい》', '切符《きっぷ》'],
          answer: 0,
          explain: { id: 'Paragraf 1 dan 3: 花を あげます。ケーキも あげます.', en: 'Paragraphs 1 and 3: flowers and also a cake.' },
        },
        {
          type: 'tf',
          q: '花《はな》は 母《はは》に もらいました。',
          answer: true,
          explain: { id: 'Paragraf 2: この 花は 母に もらいました.', en: 'Paragraph 2: この 花は 母に もらいました。' },
        },
        {
          type: 'tf',
          q: '「わたし」は もう ケーキを 買《か》いました。',
          answer: true,
          explain: { id: 'Paragraf 3: もう ケーキを 買いました.', en: 'Paragraph 3: もう ケーキを 買いました。' },
        },
        {
          type: 'tf',
          q: 'カードは もう 書《か》きました。',
          answer: false,
          explain: { id: 'Paragraf 4: カードは まだです (belum).', en: 'Paragraph 4: カードは まだです (not yet).' },
        },
        {
          type: 'mc',
          q: 'カードは 何語《なにご》で 書《か》きますか。',
          choices: ['日本語《にほんご》', '英語《えいご》', 'インドネシア語《ご》', '中国語《ちゅうごくご》'],
          answer: 0,
          explain: { id: 'Paragraf 4: カードは 日本語で 書きます.', en: 'Paragraph 4: カードは 日本語で 書きます。' },
        },
      ],
      challenge: {
        id: 'Tulis tentang hadiah yang kamu berikan atau terima. Pakai あげます, もらいます, dan kata sifat.',
        en: 'Write about a present you gave or received. Use あげます, もらいます, and adjectives.',
      },
    },
    5: {
      title: { id: 'Kamarku', en: 'My room' },
      focus: {
        id: '〜に 〜が あります・います、上・下・後ろ、〜や〜、〜が 好きです、どんな、〜から（alasan）、あまり〜ません',
        en: '〜に 〜が あります・います, 上・下・後ろ, 〜や〜, 〜が 好きです, どんな, 〜から (reason), あまり〜ません',
      },
      before: {
        id: 'Ada apa saja di kamarmu? Sebutkan tiga benda dan letaknya.',
        en: 'What is in your room? Name three things and where they are.',
      },
      passage: {
        title: 'わたしの 部屋《へや》',
        paragraphs: [
          {
            ja: 'わたしの 部屋《へや》には 机《つくえ》と ベッドが あります。机《つくえ》の 上《うえ》に パソコンや 本《ほん》が あります。',
            id: 'Di kamar saya ada meja dan tempat tidur. Di atas meja ada komputer, buku, dan sebagainya.',
            en: 'In my room there is a desk and a bed. On the desk there are a computer, books, and so on.',
          },
          {
            ja: 'ベッドの 下《した》に かばんが あります。いすの 後《うし》ろに ねこが います。',
            id: 'Di bawah tempat tidur ada tas. Di belakang kursi ada kucing.',
            en: 'There is a bag under the bed. There is a cat behind the chair.',
          },
          {
            ja: 'わたしは ねこが 好《す》きです。「どんな ねこですか。」「白《しろ》い ねこです。」',
            id: 'Saya suka kucing. “Kucing seperti apa?” “Kucing putih.”',
            en: 'I like cats. “What kind of cat is it?” “A white cat.”',
          },
          {
            ja: 'わたしは 音楽《おんがく》も 好《す》きです。毎晩《まいばん》 音楽《おんがく》を 聞《き》きます。時間《じかん》が ありませんから、あまり 本《ほん》を 読《よ》みません。',
            id: 'Saya juga suka musik. Setiap malam saya mendengarkan musik. Karena tidak ada waktu, saya tidak terlalu banyak membaca buku.',
            en: 'I like music too. Every night I listen to music. Because I have no time, I do not read books very much.',
          },
        ],
      },
      vocab: [
        { ja: '机', reading: 'つくえ', id: 'meja (belajar)', en: 'desk' },
        { ja: '上', reading: 'うえ', id: 'atas', en: 'on, above' },
        { ja: '下', reading: 'した', id: 'bawah', en: 'under, below' },
        { ja: '後ろ', reading: 'うしろ', id: 'belakang', en: 'behind' },
        { ja: 'あります', reading: 'あります', id: 'ada (benda)', en: 'there is (things)' },
        { ja: 'います', reading: 'います', id: 'ada (orang, hewan)', en: 'there is (people, animals)' },
        { ja: '好き', reading: 'すき', id: 'suka', en: 'to like' },
        { ja: '時間', reading: 'じかん', id: 'waktu', en: 'time' },
      ],
      questions: [
        {
          type: 'mc',
          q: '机《つくえ》の 上《うえ》に 何《なに》が ありますか。',
          choices: ['パソコンや 本《ほん》', 'かばん', 'ねこ', 'ベッド'],
          answer: 0,
          explain: { id: 'Paragraf 1: 机の 上に パソコンや 本が あります.', en: 'Paragraph 1: 机の 上に パソコンや 本が あります。' },
        },
        {
          type: 'mc',
          q: 'かばんは どこに ありますか。',
          choices: ['ベッドの 下《した》', '机《つくえ》の 上《うえ》', 'いすの 後《うし》ろ', '部屋《へや》の 外《そと》'],
          answer: 0,
          explain: { id: 'Paragraf 2: ベッドの 下に かばんが あります.', en: 'Paragraph 2: ベッドの 下に かばんが あります。' },
        },
        {
          type: 'mc',
          q: 'ねこは どこに いますか。',
          choices: ['いすの 後《うし》ろ', 'ベッドの 下《した》', '机《つくえ》の 上《うえ》', 'かばんの 中《なか》'],
          answer: 0,
          explain: { id: 'Paragraf 2: いすの 後ろに ねこが います.', en: 'Paragraph 2: いすの 後ろに ねこが います。' },
        },
        {
          type: 'tf',
          q: 'ベッドの 下《した》に かばんが あります。',
          answer: true,
          explain: { id: 'Paragraf 2: ベッドの 下に かばんが あります.', en: 'Paragraph 2: ベッドの 下に かばんが あります。' },
        },
        {
          type: 'tf',
          q: '「わたし」は ねこが きらいです。',
          answer: false,
          explain: { id: 'Paragraf 3: ねこが 好きです.', en: 'Paragraph 3: ねこが 好きです。' },
        },
        {
          type: 'mc',
          q: 'どうして あまり 本《ほん》を 読《よ》みませんか。',
          choices: ['時間《じかん》が ありませんから', '本《ほん》が 好《す》きじゃ ありませんから', 'ねこが いますから', '目《め》が 痛《いた》いですから'],
          answer: 0,
          explain: { id: 'Paragraf 4: 時間が ありませんから、あまり 本を 読みません.', en: 'Paragraph 4: no time, so the writer rarely reads.' },
        },
      ],
      challenge: {
        id: 'Gambarkan kamarmu: apa yang ada dan di mana letaknya (〜に 〜が あります). Tambahkan satu benda yang kamu suka.',
        en: 'Describe your room: what is there and where (〜に 〜が あります). Add one thing you like.',
      },
    },
    6: {
      title: { id: 'Keluargaku dan musim favorit', en: 'My family and my favorite season' },
      focus: {
        id: '数え方（〜人・〜本・〜つ）、〜に 〜回、〜より 〜のほうが、〜の 中で 〜が いちばん、どちらも',
        en: 'counters (〜人・〜本・〜つ), 〜に 〜回, 〜より 〜のほうが, 〜の 中で 〜が いちばん, どちらも',
      },
      before: {
        id: 'Berapa orang anggota keluargamu? Musim apa yang paling kamu suka?',
        en: 'How many people are in your family? Which season do you like best?',
      },
      passage: {
        title: 'わたしの 家族《かぞく》と 好《す》きな 季節《きせつ》',
        paragraphs: [
          {
            ja: 'わたしの 家族《かぞく》は 五人《ごにん》です。父《ちち》と 母《はは》と 姉《あね》が 一人《ひとり》と、弟《おとうと》が 一人《ひとり》 います。',
            id: 'Keluarga saya lima orang. Ada ayah, ibu, satu kakak perempuan, dan satu adik laki-laki.',
            en: 'My family has five people: my father, my mother, one older sister, and one younger brother.',
          },
          {
            ja: '家《いえ》には 部屋《へや》が 四《よっ》つ あります。庭《にわ》には りんごの 木《き》が 二本《にほん》 あります。',
            id: 'Rumah kami punya empat kamar. Di halaman ada dua pohon apel.',
            en: 'Our house has four rooms. In the garden there are two apple trees.',
          },
          {
            ja: '母《はは》は 毎日《まいにち》 りんごを 二《ふた》つ 食《た》べます。わたしは 一週間《いっしゅうかん》に 一回《いっかい》だけ 食《た》べます。',
            id: 'Ibu makan dua apel setiap hari. Saya hanya makan sekali seminggu.',
            en: 'My mother eats two apples every day. I eat one only once a week.',
          },
          {
            ja: 'わたしは 夏《なつ》より 冬《ふゆ》のほうが 好《す》きです。四《よっ》つの 季節《きせつ》の 中《なか》で、冬《ふゆ》が いちばん 好《す》きです。雪《ゆき》が 好《す》きですから。',
            id: 'Saya lebih suka musim dingin daripada musim panas. Dari empat musim, musim dinginlah yang paling saya suka, karena saya suka salju.',
            en: 'I like winter better than summer. Of the four seasons, I like winter the most, because I like snow.',
          },
          {
            ja: '姉《あね》に 「夏《なつ》と 冬《ふゆ》と どちらが 好《す》きですか」と 聞《き》きました。姉《あね》は 「どちらも 好《す》きじゃ ありません。春《はる》が いちばん 好《す》きです」と 言《い》いました。',
            id: 'Saya bertanya kepada kakak, “Musim panas dan musim dingin, mana yang kamu suka?” Kakak menjawab, “Dua-duanya tidak aku suka. Aku paling suka musim semi.”',
            en: 'I asked my sister, “Summer or winter — which do you like?” She said, “I like neither. I like spring the most.”',
          },
        ],
      },
      vocab: [
        { ja: '家族', reading: 'かぞく', id: 'keluarga', en: 'family' },
        { ja: '一週間', reading: 'いっしゅうかん', id: 'satu minggu', en: 'one week' },
        { ja: '〜回', reading: 'かい', id: '〜kali', en: '〜times' },
        { ja: '季節', reading: 'きせつ', id: 'musim', en: 'season' },
        { ja: '雪', reading: 'ゆき', id: 'salju', en: 'snow' },
        { ja: '庭', reading: 'にわ', id: 'halaman, taman', en: 'garden, yard' },
        { ja: 'どちらも', reading: 'どちらも', id: 'kedua-duanya', en: 'both (neither, with a negative)' },
      ],
      questions: [
        {
          type: 'mc',
          q: 'わたしの 家族《かぞく》は 何人《なんにん》ですか。',
          choices: ['三人《さんにん》', '四人《よにん》', '五人《ごにん》', '六人《ろくにん》'],
          answer: 2,
          explain: { id: 'Paragraf 1: 家族は 五人です (ayah, ibu, kakak, adik, saya).', en: 'Paragraph 1: 家族は 五人です (father, mother, sister, brother, me).' },
        },
        {
          type: 'mc',
          q: '庭《にわ》に りんごの 木《き》は 何本《なんぼん》 ありますか。',
          choices: ['一本《いっぽん》', '二本《にほん》', '三本《さんぼん》', '四本《よんほん》'],
          answer: 1,
          explain: { id: 'Paragraf 2: りんごの 木が 二本 あります.', en: 'Paragraph 2: there are 二本 of apple trees.' },
        },
        {
          type: 'tf',
          q: 'お母《かあ》さんは 毎日《まいにち》 りんごを 二《ふた》つ 食《た》べます。',
          answer: true,
          explain: { id: 'Paragraf 3: 母は 毎日 りんごを 二つ 食べます.', en: 'Paragraph 3: my mother eats two apples every day.' },
        },
        {
          type: 'mc',
          q: 'わたしは りんごを 一週間《いっしゅうかん》に 何回《なんかい》 食《た》べますか。',
          choices: ['一回《いっかい》', '二回《にかい》', '三回《さんかい》', '毎日《まいにち》'],
          answer: 0,
          explain: { id: '一週間に 一回だけ = hanya sekali seminggu.', en: '一週間に 一回だけ = only once a week.' },
        },
        {
          type: 'mc',
          q: 'わたしが いちばん 好《す》きな 季節《きせつ》は どれですか。',
          choices: ['秋《あき》', '夏《なつ》', '春《はる》', '冬《ふゆ》'],
          answer: 3,
          explain: { id: 'Paragraf 4: 四つの 季節の 中で、冬が いちばん 好きです.', en: 'Paragraph 4: of the four seasons, I like winter the most.' },
        },
        {
          type: 'tf',
          q: 'わたしは 冬《ふゆ》より 夏《なつ》のほうが 好《す》きです。',
          answer: false,
          explain: { id: 'Paragraf 4: 夏より 冬のほうが 好き — kebalikannya.', en: 'Paragraph 4: 夏より 冬のほうが 好き — the opposite.' },
        },
        {
          type: 'mc',
          q: 'お姉《ねえ》さんが いちばん 好《す》きな 季節《きせつ》は いつですか。',
          choices: ['夏《なつ》', '冬《ふゆ》', '春《はる》', '秋《あき》'],
          answer: 2,
          explain: { id: 'Paragraf 5: 春が いちばん 好きです (夏も 冬も 好きじゃ ありません).', en: 'Paragraph 5: she likes spring the most (she likes neither summer nor winter).' },
        },
      ],
      challenge: {
        id: 'Ceritakan keluargamu dan musim favoritmu. Pakai 〜人、〜より 〜のほうが、〜の 中で 〜が いちばん.',
        en: 'Tell about your family and your favorite season. Use 〜人, 〜より 〜のほうが, 〜の 中で 〜が いちばん.',
      },
    },
    7: {
      title: { id: 'Di stasiun', en: 'At the station' },
      focus: {
        id: 'ほしい、〜たい、〜たいと 思います、〜て ください、〜て います（sedang）、〜ましょうか、すみませんが',
        en: 'ほしい, 〜たい, 〜たいと 思います, 〜て ください, 〜て います (in progress), 〜ましょうか, すみませんが',
      },
      before: {
        id: 'Pernahkah kamu membeli tiket kereta di negara lain? Apa yang kamu tanyakan?',
        en: 'Have you ever bought a train ticket in another country? What did you ask?',
      },
      passage: {
        title: '駅《えき》で',
        paragraphs: [
          {
            ja: '日曜日《にちようび》に 友達《ともだち》と 京都《きょうと》へ 行《い》きます。わたしは お寺《てら》が 見《み》たいです。それから、お土産《みやげ》が ほしいです。友達《ともだち》は おいしい ものを 食《た》べたいと 思《おも》って います。',
            id: 'Hari Minggu saya pergi ke Kyoto bersama teman. Saya ingin melihat kuil. Selain itu, saya ingin oleh-oleh. Teman saya ingin makan makanan enak.',
            en: 'On Sunday I am going to Kyoto with a friend. I want to see temples. I also want a souvenir. My friend wants to eat something delicious.',
          },
          {
            ja: '駅《えき》に 着《つ》きました。駅員《えきいん》に 「すみませんが、京都《きょうと》まで いくらですか」と 聞《き》きました。',
            id: 'Kami sampai di stasiun. Saya bertanya kepada petugas stasiun, “Permisi, sampai Kyoto berapa harganya?”',
            en: 'I arrived at the station. I asked the station staff, “Excuse me, how much is it to Kyoto?”',
          },
          {
            ja: '駅員《えきいん》は 「千円《せんえん》です。ここに お金《かね》を 入《い》れて、ボタンを 押《お》して ください」と 言《い》いました。',
            id: 'Petugas berkata, “Seribu yen. Masukkan uang di sini, lalu tekan tombolnya.”',
            en: 'The staff said, “It is 1,000 yen. Put the money in here and press the button.”',
          },
          {
            ja: '友達《ともだち》は 駅《えき》の 前《まえ》で 待《ま》って います。赤《あか》い 帽子《ぼうし》を かぶって います。わたしは 急《いそ》いで 切符《きっぷ》を 買《か》いました。',
            id: 'Teman saya sedang menunggu di depan stasiun. Ia memakai topi merah. Saya cepat-cepat membeli tiket.',
            en: 'My friend is waiting in front of the station, wearing a red hat. I hurried and bought the tickets.',
          },
          {
            ja: '友達《ともだち》は 荷物《にもつ》が 多《おお》かったです。わたしは 「荷物《にもつ》を 持《も》ちましょうか」と 言《い》いました。友達《ともだち》は 「ありがとう。お願《ねが》いします」と 言《い》いました。',
            id: 'Barang bawaan teman saya banyak. Saya berkata, “Mau saya bawakan?” Teman saya menjawab, “Terima kasih. Tolong ya.”',
            en: 'My friend had a lot of luggage. I said, “Shall I carry it?” My friend said, “Thanks. Please do.”',
          },
        ],
      },
      vocab: [
        { ja: '駅員', reading: 'えきいん', id: 'petugas stasiun', en: 'station staff' },
        { ja: '切符', reading: 'きっぷ', id: 'tiket', en: 'ticket' },
        { ja: 'お土産', reading: 'おみやげ', id: 'oleh-oleh', en: 'souvenir' },
        { ja: '押す', reading: 'おす', id: 'menekan, mendorong', en: 'to press, to push' },
        { ja: '帽子', reading: 'ぼうし', id: 'topi', en: 'hat' },
        { ja: '荷物', reading: 'にもつ', id: 'barang bawaan', en: 'luggage' },
        { ja: 'かぶる', reading: 'かぶる', id: 'memakai (di kepala)', en: 'to wear (on the head)' },
      ],
      questions: [
        {
          type: 'mc',
          q: 'わたしは 京都《きょうと》で 何《なに》が 見《み》たいですか。',
          choices: ['お寺《てら》', '新《あたら》しい かばん', 'おいしい もの', '駅《えき》'],
          answer: 0,
          explain: { id: 'Paragraf 1: お寺が 見たいです.', en: 'Paragraph 1: お寺が 見たいです (I want to see temples).' },
        },
        {
          type: 'mc',
          q: '友達《ともだち》は 何《なに》を 食《た》べたいと 思《おも》って いますか。',
          choices: ['おいしい もの', 'お土産《みやげ》', '切符《きっぷ》', 'お寺《てら》'],
          answer: 0,
          explain: { id: 'Paragraf 1: おいしい ものを 食べたいと 思って います.', en: 'Paragraph 1: wants to eat something delicious.' },
        },
        {
          type: 'tf',
          q: '京都《きょうと》までの 切符《きっぷ》は 千円《せんえん》です。',
          answer: true,
          explain: { id: 'Paragraf 3: 千円です.', en: 'Paragraph 3: 千円です (1,000 yen).' },
        },
        {
          type: 'mc',
          q: '駅員《えきいん》は 何《なん》と 言《い》いましたか。',
          choices: [
            'お金《かね》を 入《い》れて、ボタンを 押《お》して ください',
            'ここで 待《ま》って ください',
            'お金《かね》は いりません',
            '次《つぎ》の 電車《でんしゃ》に 乗《の》って ください',
          ],
          answer: 0,
          explain: { id: 'Paragraf 3: お金を 入れて、ボタンを 押して ください.', en: 'Paragraph 3: put the money in and press the button.' },
        },
        {
          type: 'mc',
          q: '友達《ともだち》は どこで 待《ま》って いますか。',
          choices: ['駅《えき》の 前《まえ》', '電車《でんしゃ》の 中《なか》', '京都《きょうと》', 'お寺《てら》'],
          answer: 0,
          explain: { id: 'Paragraf 4: 駅の 前で 待って います (〜て います = sedang).', en: 'Paragraph 4: waiting in front of the station (〜て います = in progress).' },
        },
        {
          type: 'tf',
          q: '友達《ともだち》は 青《あお》い 帽子《ぼうし》を かぶって います。',
          answer: false,
          explain: { id: 'Paragraf 4: 赤い 帽子 (merah), bukan biru.', en: 'Paragraph 4: a red hat, not a blue one.' },
        },
        {
          type: 'mc',
          q: 'わたしは 友達《ともだち》に 何《なん》と 言《い》いましたか。',
          choices: [
            '荷物《にもつ》を 持《も》ちましょうか',
            '帽子《ぼうし》を かぶって ください',
            '先《さき》に 行《い》って ください',
            '切符《きっぷ》が ほしいですか',
          ],
          answer: 0,
          explain: { id: 'Paragraf 5: 荷物を 持ちましょうか (menawarkan bantuan).', en: 'Paragraph 5: 荷物を 持ちましょうか (offering help).' },
        },
      ],
      challenge: {
        id: 'Bayangkan kamu membeli tiket di stasiun. Tulis percakapan pendek memakai すみませんが、〜たいです、〜て ください.',
        en: 'Imagine you are buying a ticket at a station. Write a short dialogue using すみませんが, 〜たいです, 〜て ください.',
      },
    },
    8: {
      title: { id: 'Di perpustakaan', en: 'At the library' },
      focus: {
        id: '〜ても いいです、〜ては いけません、〜て います（tinggal・kebiasaan）、〜てから、どうやって、どれ・どの、〜を 出ます',
        en: '〜ても いいです, 〜ては いけません, 〜て います (live・habit), 〜てから, どうやって, どれ・どの, 〜を 出ます',
      },
      before: {
        id: 'Aturan apa saja yang ada di perpustakaan dekat rumahmu?',
        en: 'What rules are there at the library near your home?',
      },
      passage: {
        title: '図書館《としょかん》で',
        paragraphs: [
          {
            ja: 'わたしは 図書館《としょかん》の 近《ちか》くに 住《す》んで います。毎週《まいしゅう》 土曜日《どようび》に 行《い》きます。',
            id: 'Saya tinggal di dekat perpustakaan. Saya pergi ke sana setiap hari Sabtu.',
            en: 'I live near the library. I go there every Saturday.',
          },
          {
            ja: '図書館《としょかん》の 中《なか》では、写真《しゃしん》を とっては いけません。電話《でんわ》も かけては いけません。でも、水《みず》は 飲《の》んでも いいです。',
            id: 'Di dalam perpustakaan, tidak boleh memotret. Menelepon juga tidak boleh. Tetapi minum air boleh.',
            en: 'Inside the library you must not take photos. You must not make phone calls either. But you may drink water.',
          },
          {
            ja: '本《ほん》は 五冊《ごさつ》まで 借《か》りても いいです。',
            id: 'Buku boleh dipinjam sampai lima.',
            en: 'You may borrow up to five books.',
          },
          {
            ja: '昨日《きのう》、友達《ともだち》が 「どうやって 本《ほん》を 借《か》りますか」と 聞《き》きました。わたしは 「カードを 機械《きかい》に 入《い》れてから、本《ほん》を 置《お》いて、このボタンを 押《お》します」と 教《おし》えました。',
            id: 'Kemarin teman saya bertanya, “Bagaimana cara meminjam buku?” Saya mengajarinya, “Setelah memasukkan kartu ke mesin, taruh bukunya, lalu tekan tombol ini.”',
            en: 'Yesterday my friend asked, “How do you borrow a book?” I told him, “After you put your card into the machine, put the book down and press this button.”',
          },
          {
            ja: '友達《ともだち》は 「どの 機械《きかい》ですか」と 聞《き》きました。「あの 青《あお》い 機械《きかい》です」と 答《こた》えました。',
            id: 'Teman saya bertanya, “Mesin yang mana?” Saya menjawab, “Mesin biru itu.”',
            en: 'My friend asked, “Which machine?” I answered, “That blue one.”',
          },
          {
            ja: 'わたしは 図書館《としょかん》を 出《で》てから、公園《こうえん》で 本《ほん》を 読《よ》みます。',
            id: 'Setelah keluar dari perpustakaan, saya membaca buku di taman.',
            en: 'After I leave the library, I read the book in the park.',
          },
        ],
      },
      vocab: [
        { ja: '図書館', reading: 'としょかん', id: 'perpustakaan', en: 'library' },
        { ja: '写真', reading: 'しゃしん', id: 'foto', en: 'photo' },
        { ja: '借りる', reading: 'かりる', id: 'meminjam', en: 'to borrow' },
        { ja: '機械', reading: 'きかい', id: 'mesin', en: 'machine' },
        { ja: '置く', reading: 'おく', id: 'meletakkan', en: 'to put, to place' },
        { ja: '〜冊', reading: 'さつ', id: '〜buah (buku)', en: 'counter for books' },
        { ja: '公園', reading: 'こうえん', id: 'taman (umum)', en: 'park' },
      ],
      questions: [
        {
          type: 'mc',
          q: 'わたしは どこに 住《す》んで いますか。',
          choices: ['図書館《としょかん》の 近《ちか》く', '公園《こうえん》の 中《なか》', '駅《えき》の 前《まえ》', '会社《かいしゃ》の 近《ちか》く'],
          answer: 0,
          explain: { id: 'Paragraf 1: 図書館の 近くに 住んで います.', en: 'Paragraph 1: I live near the library.' },
        },
        {
          type: 'tf',
          q: '図書館《としょかん》の 中《なか》で 写真《しゃしん》を とっても いいです。',
          answer: false,
          explain: { id: 'Paragraf 2: 写真を とっては いけません.', en: 'Paragraph 2: 写真を とっては いけません (not allowed).' },
        },
        {
          type: 'mc',
          q: '図書館《としょかん》で しても いい ことは どれですか。',
          choices: [
            '水《みず》を 飲《の》む',
            '写真《しゃしん》を とる',
            '電話《でんわ》を かける',
            '本《ほん》を 六冊《ろくさつ》 借《か》りる',
          ],
          answer: 0,
          explain: { id: 'Paragraf 2: 水は 飲んでも いいです. Buku maksimal 五冊.', en: 'Paragraph 2: 水は 飲んでも いいです. Books: five at most.' },
        },
        {
          type: 'mc',
          q: '本《ほん》を 借《か》りる とき、最初《さいしょ》に 何《なに》を しますか。',
          choices: [
            'カードを 機械《きかい》に 入《い》れる',
            '本《ほん》を 置《お》く',
            'ボタンを 押《お》す',
            '図書館《としょかん》を 出《で》る',
          ],
          answer: 0,
          explain: { id: 'Paragraf 4: カードを 入れてから、本を 置いて、ボタンを 押します.', en: 'Paragraph 4: card first, then put the book down, then press the button.' },
        },
        {
          type: 'mc',
          q: 'わたしが 教《おし》えた 機械《きかい》は どれですか。',
          choices: ['青《あお》い 機械《きかい》', '赤《あか》い 機械《きかい》', '白《しろ》い 機械《きかい》', '小《ちい》さい 機械《きかい》'],
          answer: 0,
          explain: { id: 'Paragraf 5: あの 青い 機械です.', en: 'Paragraph 5: あの 青い 機械です (that blue machine).' },
        },
        {
          type: 'tf',
          q: '本《ほん》を 置《お》いてから、カードを 入《い》れます。',
          answer: false,
          explain: { id: 'Urutannya terbalik: カードを 入れてから 本を 置く.', en: 'The order is reversed: card first, then the book.' },
        },
        {
          type: 'mc',
          q: '図書館《としょかん》を 出《で》てから、何《なに》を しますか。',
          choices: [
            '公園《こうえん》で 本《ほん》を 読《よ》む',
            'うちで 寝《ね》る',
            '友達《ともだち》に 会《あ》う',
            'ごはんを 食《た》べる',
          ],
          answer: 0,
          explain: { id: 'Paragraf 6: 図書館を 出てから、公園で 本を 読みます.', en: 'Paragraph 6: after leaving the library, read in the park.' },
        },
      ],
      challenge: {
        id: 'Tulis aturan di kelas atau perpustakaanmu memakai 〜ても いいです dan 〜ては いけません.',
        en: 'Write the rules of your classroom or library using 〜ても いいです and 〜ては いけません.',
      },
    },
    9: {
      title: { id: 'Terkena flu', en: 'Catching a cold' },
      focus: {
        id: '〜ないで ください、〜なければ なりません、〜なくても いいです、〜ことが できます、趣味は 〜ことです、〜まえに、〜までに',
        en: '〜ないで ください, 〜なければ なりません, 〜なくても いいです, 〜ことが できます, 趣味は 〜ことです, 〜まえに, 〜までに',
      },
      before: {
        id: 'Apa yang dikatakan dokter saat kamu sakit? Apa yang boleh dan tidak boleh kamu lakukan?',
        en: 'What does the doctor tell you when you are sick? What may and may not you do?',
      },
      passage: {
        title: 'かぜを ひきました',
        paragraphs: [
          {
            ja: '昨日《きのう》から 頭《あたま》が 痛《いた》くて、病院《びょういん》へ 行《い》きました。医者《いしゃ》は 「かぜですね。今日《きょう》は お風呂《ふろ》に 入《はい》らないで ください」と 言《い》いました。',
            id: 'Sejak kemarin kepala saya sakit, jadi saya pergi ke rumah sakit. Dokter berkata, “Ini flu. Hari ini jangan mandi berendam.”',
            en: 'My head has hurt since yesterday, so I went to the hospital. The doctor said, “It’s a cold. Please don’t take a bath today.”',
          },
          {
            ja: '「薬《くすり》を 飲《の》まなければ なりません。一日《いちにち》に 三回《さんかい》、ごはんの あとで 飲《の》んで ください」と 言《い》いました。',
            id: 'Dokter berkata, “Anda harus minum obat. Minumlah tiga kali sehari, setelah makan.”',
            en: 'He said, “You must take the medicine. Take it three times a day, after meals.”',
          },
          {
            ja: '「でも、学校《がっこう》を 休《やす》まなくても いいです。熱《ねつ》が 出《で》たら、休《やす》んで ください。来週《らいしゅう》までに、元気《げんき》に なりますよ」。',
            id: '“Tetapi Anda tidak perlu bolos sekolah. Kalau demam, istirahatlah. Sebelum minggu depan Anda akan sehat.”',
            en: '“But you don’t have to stay home from school. If you get a fever, rest. You will be well by next week.”',
          },
          {
            ja: 'わたしの 趣味《しゅみ》は 本《ほん》を 読《よ》む ことです。寝《ね》る まえに、いつも 三十分《さんじゅっぷん》 読《よ》みます。でも、今日《きょう》は 早《はや》く 寝《ね》なければ なりません。',
            id: 'Hobi saya membaca buku. Sebelum tidur saya selalu membaca tiga puluh menit. Tetapi hari ini saya harus tidur cepat.',
            en: 'My hobby is reading. Before I sleep I always read for thirty minutes. But today I have to go to bed early.',
          },
          {
            ja: 'わたしは 泳《およ》ぐ ことが できます。でも、かぜの とき は 泳《およ》がないで、うちで 休《やす》みます。',
            id: 'Saya bisa berenang. Tetapi ketika flu, saya tidak berenang dan beristirahat di rumah.',
            en: 'I can swim. But when I have a cold, I don’t swim; I rest at home.',
          },
        ],
      },
      vocab: [
        { ja: '病院', reading: 'びょういん', id: 'rumah sakit', en: 'hospital' },
        { ja: '医者', reading: 'いしゃ', id: 'dokter', en: 'doctor' },
        { ja: '薬', reading: 'くすり', id: 'obat', en: 'medicine' },
        { ja: '熱', reading: 'ねつ', id: 'demam', en: 'fever' },
        { ja: 'お風呂', reading: 'おふろ', id: 'mandi berendam', en: 'bath' },
        { ja: '趣味', reading: 'しゅみ', id: 'hobi', en: 'hobby' },
        { ja: '泳ぐ', reading: 'およぐ', id: 'berenang', en: 'to swim' },
      ],
      questions: [
        {
          type: 'mc',
          q: 'わたしは どうして 病院《びょういん》へ 行《い》きましたか。',
          choices: ['頭《あたま》が 痛《いた》かったから', '足《あし》が 痛《いた》かったから', '友達《ともだち》に 会《あ》うから', '薬《くすり》を 買《か》うから'],
          answer: 0,
          explain: { id: 'Paragraf 1: 昨日から 頭が 痛くて.', en: 'Paragraph 1: my head had hurt since yesterday.' },
        },
        {
          type: 'mc',
          q: '医者《いしゃ》は 今日《きょう》、何《なに》を しないで ほしいと 言《い》いましたか。',
          choices: ['お風呂《ふろ》に 入《はい》る', 'ごはんを 食《た》べる', '薬《くすり》を 飲《の》む', '学校《がっこう》へ 行《い》く'],
          answer: 0,
          explain: { id: 'Paragraf 1: お風呂に 入らないで ください.', en: 'Paragraph 1: please don’t take a bath.' },
        },
        {
          type: 'mc',
          q: '薬《くすり》は 一日《いちにち》に 何回《なんかい》 飲《の》みますか。',
          choices: ['一回《いっかい》', '二回《にかい》', '三回《さんかい》', '四回《よんかい》'],
          answer: 2,
          explain: { id: 'Paragraf 2: 一日に 三回、ごはんの あとで.', en: 'Paragraph 2: three times a day after meals.' },
        },
        {
          type: 'tf',
          q: 'わたしは 学校《がっこう》を 休《やす》まなければ なりません。',
          answer: false,
          explain: { id: 'Paragraf 3: 休まなくても いいです.', en: 'Paragraph 3: 休まなくても いいです (no need to rest from school).' },
        },
        {
          type: 'mc',
          q: 'わたしの 趣味《しゅみ》は 何《なん》ですか。',
          choices: ['本《ほん》を 読《よ》む こと', '泳《およ》ぐ こと', '料理《りょうり》を 作《つく》る こと', '映画《えいが》を 見《み》る こと'],
          answer: 0,
          explain: { id: 'Paragraf 4: 趣味は 本を 読む ことです.', en: 'Paragraph 4: my hobby is reading.' },
        },
        {
          type: 'tf',
          q: 'わたしは 寝《ね》る まえに、本《ほん》を 読《よ》みます。',
          answer: true,
          explain: { id: 'Paragraf 4: 寝る まえに、いつも 三十分 読みます.', en: 'Paragraph 4: before sleeping I read thirty minutes.' },
        },
        {
          type: 'mc',
          q: 'いつまでに 元気《げんき》に なると 医者《いしゃ》は 言《い》いましたか。',
          choices: ['来週《らいしゅう》', '明日《あした》', '来月《らいげつ》', '今日《きょう》'],
          answer: 0,
          explain: { id: 'Paragraf 3: 来週までに、元気に なりますよ.', en: 'Paragraph 3: you will be well by next week.' },
        },
      ],
      challenge: {
        id: 'Bayangkan kamu sakit. Tulis apa yang harus dan tidak boleh kamu lakukan memakai 〜なければ なりません dan 〜ないで ください.',
        en: 'Imagine you are sick. Write what you must and must not do using 〜なければ なりません and 〜ないで ください.',
      },
    },
    10: {
      title: { id: 'Buku harian', en: 'My diary' },
      focus: {
        id: '〜た ことが あります、〜たり 〜たり します、〜く／〜に なります、ふつうけい（日記）、〜けど',
        en: '〜た ことが あります, 〜たり 〜たり します, 〜く／〜に なります, plain form (diary), 〜けど',
      },
      before: {
        id: 'Pernahkah kamu menulis buku harian? Pengalaman apa yang pernah kamu alami di negara lain?',
        en: 'Have you ever kept a diary? What have you experienced in another country?',
      },
      passage: {
        title: '日記《にっき》',
        paragraphs: [
          {
            ja: 'わたしは 去年《きょねん》、日本《にほん》へ 来《き》た。日本《にほん》で 富士山《ふじさん》に 登《のぼ》った ことが ある。',
            id: 'Saya datang ke Jepang tahun lalu. Di Jepang saya pernah mendaki Gunung Fuji.',
            en: 'I came to Japan last year. In Japan I have climbed Mt. Fuji.',
          },
          {
            ja: '日曜日《にちようび》は、いつも 買《か》い物《もの》を したり、友達《ともだち》と 映画《えいが》を 見《み》たり する。',
            id: 'Pada hari Minggu, saya biasanya berbelanja dan menonton film bersama teman.',
            en: 'On Sundays I usually do things like shopping and watching movies with friends.',
          },
          {
            ja: '先月《せんげつ》から 日本語《にほんご》が 少《すこ》し 上手《じょうず》に なった。漢字《かんじ》は まだ 難《むずか》しいけど、毎日《まいにち》 練習《れんしゅう》して いる。',
            id: 'Sejak bulan lalu bahasa Jepang saya menjadi sedikit lebih baik. Kanji masih sulit, tetapi saya berlatih setiap hari.',
            en: 'Since last month my Japanese has become a little better. Kanji are still hard, but I practice every day.',
          },
          {
            ja: '友達《ともだち》は 「すしを 食《た》べた ことが ある？」と 聞《き》いた。「うん、ある。おいしかった」と 答《こた》えた。',
            id: 'Teman bertanya, “Pernahkah kamu makan sushi?” Saya menjawab, “Ya, pernah. Enak.”',
            en: 'My friend asked, “Have you ever eaten sushi?” I answered, “Yes, I have. It was delicious.”',
          },
          {
            ja: 'これから 寒《さむ》く なる。冬《ふゆ》は 雪《ゆき》を 見《み》に 行《い》きたい。',
            id: 'Mulai sekarang udara akan menjadi dingin. Pada musim dingin saya ingin pergi melihat salju.',
            en: 'It will get cold from now on. In winter I want to go and see snow.',
          },
        ],
      },
      vocab: [
        { ja: '日記', reading: 'にっき', id: 'buku harian', en: 'diary' },
        { ja: '去年', reading: 'きょねん', id: 'tahun lalu', en: 'last year' },
        { ja: '登る', reading: 'のぼる', id: 'mendaki, memanjat', en: 'to climb' },
        { ja: '練習する', reading: 'れんしゅうする', id: 'berlatih', en: 'to practice' },
        { ja: '難しい', reading: 'むずかしい', id: 'sulit', en: 'difficult' },
        { ja: '答える', reading: 'こたえる', id: 'menjawab', en: 'to answer' },
        { ja: '寒い', reading: 'さむい', id: 'dingin', en: 'cold' },
      ],
      questions: [
        {
          type: 'mc',
          q: 'わたしは 日本《にほん》で 何《なに》を した ことが ありますか。',
          choices: ['富士山《ふじさん》に 登《のぼ》った', '飛行機《ひこうき》を 作《つく》った', '雪《ゆき》を 見《み》た', '会社《かいしゃ》で 働《はたら》いた'],
          answer: 0,
          explain: { id: 'Paragraf 1: 富士山に 登った ことが ある.', en: 'Paragraph 1: I have climbed Mt. Fuji.' },
        },
        {
          type: 'mc',
          q: 'わたしは 日曜日《にちようび》に よく 何《なに》を しますか。',
          choices: ['買《か》い物《もの》や 映画《えいが》', '勉強《べんきょう》や 仕事《しごと》', '料理《りょうり》や 掃除《そうじ》', '山《やま》に 登《のぼ》る こと'],
          answer: 0,
          explain: { id: 'Paragraf 2: 買い物を したり、映画を 見たり する.', en: 'Paragraph 2: shopping, watching movies, etc.' },
        },
        {
          type: 'mc',
          q: '先月《せんげつ》から、何《なに》が 上手《じょうず》に なりましたか。',
          choices: ['日本語《にほんご》', '料理《りょうり》', '水泳《すいえい》', '運転《うんてん》'],
          answer: 0,
          explain: { id: 'Paragraf 3: 日本語が 少し 上手に なった.', en: 'Paragraph 3: my Japanese has become a little better.' },
        },
        {
          type: 'tf',
          q: '漢字《かんじ》は もう 難《むずか》しく ありません。',
          answer: false,
          explain: { id: 'Paragraf 3: 漢字は まだ 難しいけど.', en: 'Paragraph 3: kanji are still difficult.' },
        },
        {
          type: 'tf',
          q: 'わたしは すしを 食《た》べた ことが ありません。',
          answer: false,
          explain: { id: 'Paragraf 4: 「うん、ある。おいしかった」.', en: 'Paragraph 4: “Yes, I have. It was delicious.”' },
        },
        {
          type: 'mc',
          q: 'これから、どう なりますか。',
          choices: ['寒《さむ》く なる', '暑《あつ》く なる', '暗《くら》く ならない', '静《しず》かに なる'],
          answer: 0,
          explain: { id: 'Paragraf 5: これから 寒く なる.', en: 'Paragraph 5: it will get cold from now on.' },
        },
        {
          type: 'mc',
          q: 'わたしは 冬《ふゆ》に 何《なに》を したいですか。',
          choices: ['雪《ゆき》を 見《み》に 行《い》きたい', '海《うみ》で 泳《およ》ぎたい', '富士山《ふじさん》に 登《のぼ》りたい', 'うちで 寝《ね》たい'],
          answer: 0,
          explain: { id: 'Paragraf 5: 雪を 見に 行きたい.', en: 'Paragraph 5: I want to go see snow.' },
        },
      ],
      challenge: {
        id: 'Tulis buku harian pendek dengan bentuk biasa. Pakai 〜た ことが ある dan 〜たり 〜たり する.',
        en: 'Write a short diary in plain form. Use 〜た ことが ある and 〜たり 〜たり する.',
      },
    },
    11: {
      title: { id: 'Supermarket baru', en: 'The new supermarket' },
      focus: {
        id: 'ふつうけい と 思います、「〜」と 言います、〜でしょう？、N（tempat）で N が あります、N でも、〜ないと…、kalimat penjelas N、V kamus ＋ 時間・約束・用事、〜ましょうか',
        en: 'plain form と 思います, 「〜」と 言います, 〜でしょう?, N（place）で N が あります, N でも, 〜ないと…, clauses modifying a noun, V dictionary form ＋ 時間・約束・用事, 〜ましょうか',
      },
      before: {
        id: 'Apa pendapatmu tentang toko baru di dekat rumahmu? Murah atau mahal?',
        en: 'What do you think of a new shop near your home? Is it cheap or expensive?',
      },
      passage: {
        title: '新《あたら》しい スーパー',
        paragraphs: [
          {
            ja: 'わたしの 町《まち》に、先月《せんげつ》 新《あたら》しい スーパーが できました。わたしは とても 便利《べんり》だと 思《おも》います。',
            id: 'Di kota saya, bulan lalu berdiri sebuah supermarket baru. Menurut saya sangat praktis.',
            en: 'Last month a new supermarket opened in my town. I think it is very convenient.',
          },
          {
            ja: '友達《ともだち》の アンナさんは 「あの スーパーは 野菜《やさい》が 安《やす》いでしょう？」と 言《い》いました。わたしは 「ええ、安《やす》いと 思《おも》います。でも、肉《にく》は 高《たか》いです」と 答《こた》えました。',
            id: 'Teman saya, Anna, berkata, “Sayur di supermarket itu murah, kan?” Saya menjawab, “Ya, menurut saya murah. Tapi dagingnya mahal.”',
            en: 'My friend Anna said, “The vegetables at that supermarket are cheap, aren’t they?” I answered, “Yes, I think they are cheap. But the meat is expensive.”',
          },
          {
            ja: '来週《らいしゅう》の 日曜日《にちようび》、駅《えき》の 前《まえ》で お祭《まつ》りが あります。わたしは アンナさんと 行《い》く 約束《やくそく》を しました。',
            id: 'Hari Minggu depan ada festival di depan stasiun. Saya sudah berjanji pergi bersama Anna.',
            en: 'Next Sunday there will be a festival in front of the station. I made a promise to go with Anna.',
          },
          {
            ja: 'アンナさんが 作《つく》った ケーキを 持《も》って 行《い》きます。わたしは 昨日《きのう》 買《か》った 青《あお》い 服《ふく》を 着《き》て 行《い》きます。',
            id: 'Kami akan membawa kue yang dibuat Anna. Saya akan memakai baju biru yang kemarin saya beli.',
            en: 'We will take the cake Anna made. I will wear the blue clothes I bought yesterday.',
          },
          {
            ja: 'お祭《まつ》りの あと、喫茶店《きっさてん》で コーヒーでも 飲《の》みたいです。でも、日曜日《にちようび》の 午後《ごご》は 用事《ようじ》が あります。だから、長《なが》く いる 時間《じかん》が ありません。',
            id: 'Setelah festival, saya ingin minum kopi atau semacamnya di kafe. Tapi Minggu sore saya ada urusan. Jadi tidak ada waktu untuk berlama-lama.',
            en: 'After the festival I would like to have coffee or something at a café. But I have errands on Sunday afternoon, so I do not have time to stay long.',
          },
          {
            ja: '先週《せんしゅう》の 夜《よる》、アンナさんと 会《あ》いました。もう 九時《くじ》でした。「そろそろ 帰《かえ》らないと…」と アンナさんは 言《い》いました。荷物《にもつ》が 重《おも》かったですから、「持《も》ちましょうか」と わたしは 言《い》いました。',
            id: 'Malam minggu lalu saya bertemu Anna. Sudah pukul sembilan. Anna berkata, “Sudah waktunya pulang…” Karena barangnya berat, saya berkata, “Mau saya bawakan?”',
            en: 'Last week in the evening I met Anna. It was already nine o’clock. Anna said, “I’d better go home soon…” Her bag was heavy, so I said, “Shall I carry it?”',
          },
        ],
      },
      vocab: [
        { ja: '町', reading: 'まち', id: 'kota, kota kecil', en: 'town' },
        { ja: '便利', reading: 'べんり', id: 'praktis, nyaman', en: 'convenient' },
        { ja: '約束', reading: 'やくそく', id: 'janji', en: 'promise, appointment' },
        { ja: '用事', reading: 'ようじ', id: 'urusan', en: 'errand, things to do' },
        { ja: '荷物', reading: 'にもつ', id: 'barang bawaan', en: 'luggage, baggage' },
        { ja: 'お祭り', reading: 'おまつり', id: 'festival', en: 'festival' },
        { ja: '肉', reading: 'にく', id: 'daging', en: 'meat' },
      ],
      questions: [
        {
          type: 'mc',
          q: 'わたしは 新《あたら》しい スーパーを どう 思《おも》いますか。',
          choices: ['便利《べんり》だと 思《おも》います', '高《たか》いと 思《おも》います', '遠《とお》いと 思《おも》います', '小《ちい》さいと 思《おも》います'],
          answer: 0,
          explain: { id: 'Paragraf 1: とても 便利だと 思います.', en: 'Paragraph 1: I think it is very convenient.' },
        },
        {
          type: 'mc',
          q: 'スーパーで 高《たか》い ものは 何《なん》ですか。',
          choices: ['肉《にく》', '野菜《やさい》', 'ケーキ', '服《ふく》'],
          answer: 0,
          explain: { id: 'Paragraf 2: 野菜は 安いですが、肉は 高いです.', en: 'Paragraph 2: vegetables are cheap, but meat is expensive.' },
        },
        {
          type: 'mc',
          q: 'お祭《まつ》りは どこで ありますか。',
          choices: ['駅《えき》の 前《まえ》', 'スーパーの 中《なか》', '学校《がっこう》', 'アンナさんの うち'],
          answer: 0,
          explain: { id: 'Paragraf 3: 駅の 前で お祭りが あります.', en: 'Paragraph 3: the festival is in front of the station.' },
        },
        {
          type: 'tf',
          q: 'お祭《まつ》りは 今週《こんしゅう》の 日曜日《にちようび》です。',
          answer: false,
          explain: { id: 'Paragraf 3: 来週の 日曜日.', en: 'Paragraph 3: it is next Sunday.' },
        },
        {
          type: 'mc',
          q: 'アンナさんが 作《つく》った ものは 何《なん》ですか。',
          choices: ['ケーキ', '青《あお》い 服《ふく》', 'コーヒー', 'お祭《まつ》り'],
          answer: 0,
          explain: { id: 'Paragraf 4: アンナさんが 作った ケーキ.', en: 'Paragraph 4: the cake Anna made.' },
        },
        {
          type: 'tf',
          q: '日曜日《にちようび》の 午後《ごご》、わたしは 用事《ようじ》が あります。',
          answer: true,
          explain: { id: 'Paragraf 5: 日曜日の 午後は 用事が あります.', en: 'Paragraph 5: I have errands on Sunday afternoon.' },
        },
        {
          type: 'mc',
          q: '「持《も》ちましょうか」と 言《い》ったのは どうしてですか。',
          choices: ['アンナさんの 荷物《にもつ》が 重《おも》かったから', '九時《くじ》だったから', '雨《あめ》だったから', 'お祭《まつ》りだったから'],
          answer: 0,
          explain: { id: 'Paragraf 6: 荷物が 重かったですから.', en: 'Paragraph 6: because the bag was heavy.' },
        },
      ],
      challenge: {
        id: 'Tulis pendapatmu tentang sebuah toko atau tempat di kotamu. Pakai 〜と 思います dan 〜でしょう？.',
        en: 'Write your opinion about a shop or place in your town. Use 〜と 思います and 〜でしょう?.',
      },
    },
    12: {
      title: { id: 'Tersesat di Kyoto', en: 'Lost in Kyoto' },
      focus: {
        id: '〜とき、V kamus／V た ＋ とき、〜と（kalau… maka）、N が Adj、N を ＋ kata kerja gerak、くれます、〜て あげます・もらいます・くれます、〜たら、〜ても、もし',
        en: '〜とき, V dictionary/ta form + とき, 〜と (if/when), N が Adj, N を + motion verb, くれます, 〜て あげます・もらいます・くれます, 〜たら, 〜ても, もし',
      },
      before: {
        id: 'Pernahkah kamu tersesat di kota yang belum kamu kenal? Siapa yang membantumu?',
        en: 'Have you ever got lost in an unfamiliar city? Who helped you?',
      },
      passage: {
        title: '京都《きょうと》で',
        paragraphs: [
          {
            ja: '先週《せんしゅう》、友達《ともだち》と 京都《きょうと》へ 行《い》きました。駅《えき》を 出《で》た とき、雨《あめ》が 降《ふ》って いました。',
            id: 'Minggu lalu saya pergi ke Kyoto bersama teman. Ketika keluar dari stasiun, hujan sedang turun.',
            en: 'Last week I went to Kyoto with a friend. When we left the station, it was raining.',
          },
          {
            ja: '友達《ともだち》は 「雨《あめ》が 降《ふ》っても、お寺《てら》へ 行《い》きましょう」と 言《い》いました。友達《ともだち》は 元気《げんき》で、足《あし》が 速《はや》いです。',
            id: 'Teman saya berkata, “Meskipun hujan, ayo kita ke kuil.” Temanku bersemangat dan larinya cepat.',
            en: 'My friend said, “Even if it rains, let’s go to the temple.” My friend is energetic and fast on their feet.',
          },
          {
            ja: 'でも、道《みち》が わかりませんでした。わたしは 近《ちか》くの 人《ひと》に 道《みち》を 聞《き》きました。その 人《ひと》は 「この 道《みち》を まっすぐ 行《い》くと、橋《はし》が あります。橋《はし》を 渡《わた》ると、右《みぎ》に お寺《てら》が あります」と 教《おし》えて くれました。',
            id: 'Tetapi saya tidak tahu jalannya. Saya bertanya kepada orang di dekat situ. Orang itu mengajari saya, “Kalau Anda terus lurus di jalan ini, ada jembatan. Kalau menyeberangi jembatan, kuilnya ada di sebelah kanan.”',
            en: 'But we did not know the way. I asked a person nearby. They told me, “If you go straight along this road, there is a bridge. If you cross the bridge, the temple is on the right.”',
          },
          {
            ja: 'その 人《ひと》は 地図《ちず》も かいて くれました。わたしは うれしかったです。',
            id: 'Orang itu juga menggambarkan peta untuk saya. Saya senang.',
            en: 'That person also drew a map for me. I was happy.',
          },
          {
            ja: 'お寺《てら》で、友達《ともだち》が わたしの 写真《しゃしん》を 撮《と》って くれました。わたしも 友達《ともだち》の 写真《しゃしん》を 撮《と》って あげました。',
            id: 'Di kuil, teman saya memotret saya. Saya juga memotret teman saya.',
            en: 'At the temple my friend took a photo of me. I also took a photo of my friend.',
          },
          {
            ja: '帰《かえ》る とき、友達《ともだち》が 「もし また 京都《きょうと》へ 来《き》たら、今度《こんど》は 天気《てんき》が いい 日《ひ》に 来《き》ましょう」と 言《い》いました。わたしも そう 思《おも》います。',
            id: 'Saat pulang, teman saya berkata, “Kalau kita ke Kyoto lagi, kali ini mari datang pada hari yang cuacanya bagus.” Saya pun berpikir begitu.',
            en: 'When we went home, my friend said, “If we come to Kyoto again, let’s come on a day with good weather next time.” I think so too.',
          },
        ],
      },
      vocab: [
        { ja: '橋', reading: 'はし', id: 'jembatan', en: 'bridge' },
        { ja: '渡る', reading: 'わたる', id: 'menyeberang', en: 'to cross' },
        { ja: '地図', reading: 'ちず', id: 'peta', en: 'map' },
        { ja: '教える', reading: 'おしえる', id: 'mengajari, memberi tahu', en: 'to teach, to tell' },
        { ja: '近く', reading: 'ちかく', id: 'dekat, sekitar', en: 'nearby' },
        { ja: '今度', reading: 'こんど', id: 'kali ini, lain kali', en: 'this time, next time' },
        { ja: 'お寺', reading: 'おてら', id: 'kuil Buddha', en: 'temple' },
      ],
      questions: [
        {
          type: 'mc',
          q: '駅《えき》を 出《で》た とき、天気《てんき》は どうでしたか。',
          choices: ['雨《あめ》が 降《ふ》って いました', 'いい 天気《てんき》でした', '雪《ゆき》が 降《ふ》って いました', '風《かぜ》が 強《つよ》かったです'],
          answer: 0,
          explain: { id: 'Paragraf 1: 駅を 出た とき、雨が 降って いました.', en: 'Paragraph 1: it was raining when they left the station.' },
        },
        {
          type: 'mc',
          q: '友達《ともだち》は 何《なん》と 言《い》いましたか。',
          choices: ['雨《あめ》が 降《ふ》っても、お寺《てら》へ 行《い》きましょう', '雨《あめ》ですから、うちへ 帰《かえ》りましょう', '雨《あめ》が 降《ふ》ったら、バスに 乗《の》りましょう', 'お寺《てら》へ 行《い》きたくないです'],
          answer: 0,
          explain: { id: 'Paragraf 2: 雨が 降っても、お寺へ 行きましょう.', en: 'Paragraph 2: even if it rains, let’s go to the temple.' },
        },
        {
          type: 'mc',
          q: 'だれが 道《みち》を 教《おし》えて くれましたか。',
          choices: ['近《ちか》くの 人《ひと》', '友達《ともだち》', '駅《えき》の 人《ひと》', '先生《せんせい》'],
          answer: 0,
          explain: { id: 'Paragraf 3: 近くの 人に 道を 聞いた.', en: 'Paragraph 3: a person nearby told them the way.' },
        },
        {
          type: 'tf',
          q: '橋《はし》を 渡《わた》ると、左《ひだり》に お寺《てら》が あります。',
          answer: false,
          explain: { id: 'Paragraf 3: 橋を 渡ると、右に お寺が あります.', en: 'Paragraph 3: the temple is on the right after crossing the bridge.' },
        },
        {
          type: 'mc',
          q: '近《ちか》くの 人《ひと》は ほかに 何《なに》を して くれましたか。',
          choices: ['地図《ちず》を かいて くれました', 'かさを 貸《か》して くれました', 'お寺《てら》まで いっしょに 行《い》って くれました', '写真《しゃしん》を 撮《と》って くれました'],
          answer: 0,
          explain: { id: 'Paragraf 4: 地図も かいて くれました.', en: 'Paragraph 4: they also drew a map.' },
        },
        {
          type: 'tf',
          q: '友達《ともだち》は わたしの 写真《しゃしん》を 撮《と》って くれました。',
          answer: true,
          explain: { id: 'Paragraf 5: 友達が わたしの 写真を 撮って くれました.', en: 'Paragraph 5: my friend took a photo of me.' },
        },
        {
          type: 'mc',
          q: '帰《かえ》る とき、友達《ともだち》は 何《なん》と 言《い》いましたか。',
          choices: ['また 来《き》たら、天気《てんき》が いい 日《ひ》に 来《き》ましょう', 'もう 京都《きょうと》へ 来《き》ません', '来週《らいしゅう》 また 来《き》ます', 'お寺《てら》は おもしろくなかったです'],
          answer: 0,
          explain: { id: 'Paragraf 6: もし また 来たら、天気が いい 日に 来ましょう.', en: 'Paragraph 6: if we come again, let’s come on a day with good weather.' },
        },
      ],
      challenge: {
        id: 'Tulis pengalamanmu bertanya jalan. Pakai 〜と、〜とき、〜て くれました、〜ても.',
        en: 'Write about a time you asked for directions. Use 〜と, 〜とき, 〜て くれました, 〜ても.',
      },
    },
  },

  // ---------------------------------------------------------------- N4
  N4: {
    1: {
      title: { id: 'Suara piano dari kamar sebelah', en: 'Piano sounds from next door' },
      focus: {
        id: '〜んです／〜んですが、〜ていただけませんか、可能形、見えます・聞こえます、〜しか、は（perbandingan）',
        en: '〜んです／〜んですが, 〜ていただけませんか, potential verbs, 見えます・聞こえます, 〜しか, は for contrast',
      },
      before: {
        id: 'Pernahkah kamu terganggu oleh suara dari tetangga? Apa yang kamu lakukan?',
        en: 'Have you ever been bothered by a neighbor’s noise? What did you do?',
      },
      passage: {
        title: 'となりの ピアノ',
        paragraphs: [
          {
            ja: '先週《せんしゅう》、新《あたら》しい アパートに 引《ひ》っ越《こ》しました。駅《えき》から 遠《とお》いんですが、部屋《へや》から 富士山《ふじさん》が 見《み》えます。',
            id: 'Minggu lalu saya pindah ke apartemen baru. Jauh dari stasiun, tetapi dari kamar terlihat Gunung Fuji.',
            en: 'Last week I moved into a new apartment. It is far from the station, but I can see Mt. Fuji from my room.',
          },
          {
            ja: 'でも、となりの 部屋《へや》から ピアノの 音《おと》が 聞《き》こえるんです。夜《よる》 10時《じ》ごろまで 聞《き》こえます。',
            id: 'Namun, suara piano terdengar dari kamar sebelah. Terdengar sampai sekitar pukul 10 malam.',
            en: 'But I can hear piano sounds from the next room. I can hear it until about 10 at night.',
          },
          {
            ja: 'わたしは ピアノの 音《おと》は 好《す》きですが、夜《よる》は 勉強《べんきょう》が できません。それで、となりの 人《ひと》に 「すみません、もう 少《すこ》し 小《ちい》さい 音《おと》で 弾《ひ》いていただけませんか」と 頼《たの》みました。',
            id: 'Saya suka suara piano, tetapi malam hari saya tidak bisa belajar. Karena itu saya meminta kepada tetangga, “Maaf, bisakah Anda bermain dengan suara sedikit lebih kecil?”',
            en: 'I like the sound of the piano, but I can’t study at night. So I asked my neighbor, “Excuse me, could you play a little more quietly?”',
          },
          {
            ja: 'となりの 人《ひと》は 「すみません。夜《よる》は 8時《じ》までしか 弾《ひ》きません」と 言《い》いました。',
            id: 'Tetangga menjawab, “Maaf. Malam hari saya hanya bermain sampai pukul 8.”',
            en: 'My neighbor said, “I’m sorry. At night I only play until 8 o’clock.”',
          },
        ],
      },
      vocab: [
        { ja: '引っ越す', reading: 'ひっこす', id: 'pindah rumah', en: 'to move (house)' },
        { ja: '見える', reading: 'みえる', id: 'terlihat', en: 'to be visible' },
        { ja: '聞こえる', reading: 'きこえる', id: 'terdengar', en: 'to be audible' },
        { ja: '音', reading: 'おと', id: 'bunyi, suara', en: 'sound' },
        { ja: '弾く', reading: 'ひく', id: 'memainkan (piano, gitar)', en: 'to play (piano, guitar)' },
        { ja: '頼む', reading: 'たのむ', id: 'meminta, memohon', en: 'to ask, to request' },
      ],
      questions: [
        {
          type: 'mc',
          q: '部屋《へや》から 何《なに》が 見《み》えますか。',
          choices: ['駅《えき》', '富士山《ふじさん》', 'ピアノ', '新《あたら》しい アパート'],
          answer: 1,
          explain: { id: 'Paragraf 1: 部屋から 富士山が 見えます。', en: 'Paragraph 1: 部屋から 富士山が 見えます。' },
        },
        {
          type: 'mc',
          q: '「わたし」は 夜《よる》、何《なに》が できませんか。',
          choices: ['食事《しょくじ》', '勉強《べんきょう》', '電話《でんわ》', '散歩《さんぽ》'],
          answer: 1,
          explain: { id: 'Paragraf 3: 夜は 勉強が できません。', en: 'Paragraph 3: 夜は 勉強が できません。' },
        },
        {
          type: 'mc',
          q: '「弾《ひ》いていただけませんか」は どんな 意味《いみ》ですか。',
          choices: [
            'Tolong mainkan dengan sopan',
            'Jangan bermain',
            'Saya akan bermain',
            'Apakah Anda sudah bermain?',
          ],
          answer: 0,
          explain: { id: '〜ていただけませんか = permintaan yang sangat sopan.', en: '〜ていただけませんか = a very polite request.' },
        },
        {
          type: 'tf',
          q: '「わたし」は ピアノの 音《おと》が きらいです。',
          answer: false,
          explain: { id: 'Paragraf 3: ピアノの 音は 好きですが…', en: 'Paragraph 3: ピアノの 音は 好きですが…' },
        },
        {
          type: 'tf',
          q: 'となりの 人《ひと》は 夜《よる》 10時《じ》まで ピアノを 弾《ひ》きます。',
          answer: false,
          explain: { id: 'Paragraf 4: 8時までしか 弾きません (hanya sampai pukul 8).', en: 'Paragraph 4: 8時までしか 弾きません (only until 8).' },
        },
        {
          type: 'tf',
          q: 'アパートは 駅《えき》から 近《ちか》いです。',
          answer: false,
          explain: { id: 'Paragraf 1: 駅から 遠いんですが… (jauh).', en: 'Paragraph 1: 駅から 遠いんですが… (far).' },
        },
      ],
      challenge: {
        id: 'Tulis permintaan sopan kepada tetangga memakai 〜ていただけませんか.',
        en: 'Write a polite request to a neighbor using 〜ていただけませんか.',
      },
    },
    2: {
      title: { id: 'Tas yang tertinggal di kereta', en: 'A bag left on the train' },
      focus: {
        id: '〜ながら、〜て います（keadaan）、〜て しまいました、〜し、〜とき、ありました、何か',
        en: '〜ながら, 〜て います (state), 〜て しまいました, 〜し, 〜とき, ありました, 何か',
      },
      before: {
        id: 'Pernahkah kamu meninggalkan sesuatu di kereta atau bus? Apa yang kamu lakukan setelah itu?',
        en: 'Have you ever left something behind on a train or bus? What did you do afterwards?',
      },
      passage: {
        title: '電車《でんしゃ》の 忘《わす》れ物《もの》',
        paragraphs: [
          {
            ja: 'きのうの 夜《よる》、わたしは 音楽《おんがく》を 聞《き》きながら、電車《でんしゃ》の 中《なか》で 小説《しょうせつ》を 読《よ》んで いました。とても おもしろい 本《ほん》でしたから、駅《えき》に 着《つ》いた とき、急《いそ》いで 電車《でんしゃ》を 降《お》りました。',
            id: 'Tadi malam, di dalam kereta saya membaca novel sambil mendengarkan musik. Karena bukunya sangat menarik, begitu sampai di stasiun saya turun dari kereta dengan tergesa-gesa.',
            en: 'Last night I was reading a novel on the train while listening to music. The book was so interesting that when we reached the station I got off in a hurry.',
          },
          {
            ja: 'うちに 帰《かえ》った とき、かばんが ありませんでした。網棚《あみだな》に 忘《わす》れて しまったんです。かばんの 中《なか》には 財布《さいふ》も 入《はい》って いたし、大事《だいじ》な 書類《しょるい》も 入《はい》って いました。',
            id: 'Ketika sampai di rumah, tas saya tidak ada. Ternyata tertinggal di rak bagasi. Di dalam tas ada dompet, dan ada juga dokumen penting.',
            en: 'When I got home, my bag was gone. I had left it on the luggage rack. My wallet was in the bag, and there were also important documents.',
          },
          {
            ja: 'すぐに 駅《えき》に 電話《でんわ》しました。駅員《えきいん》は 「何時《なんじ》の 電車《でんしゃ》ですか」と 聞《き》きました。わたしは 「確《たし》か 10時《じ》ごろです。黒《くろ》い かばんです」と 答《こた》えました。駅員《えきいん》は 「中《なか》に 何《なに》か 大事《だいじ》な 物《もの》が 入《はい》って いますか」と 聞《き》きました。',
            id: 'Saya segera menelepon stasiun. Petugas stasiun bertanya, “Kereta jam berapa?” Saya menjawab, “Kalau tidak salah sekitar pukul 10. Tasnya hitam.” Petugas bertanya lagi, “Apakah ada barang penting di dalamnya?”',
            en: 'I called the station right away. The station staff asked, “Which train was it?” I answered, “If I remember correctly, around 10. It’s a black bag.” They asked, “Is there anything important inside?”',
          },
          {
            ja: '三十分《さんじゅっぷん》ぐらい 待《ま》って いたら、駅員《えきいん》から 電話《でんわ》が ありました。「かばんが ありましたよ。あした、駅前《えきまえ》の 交番《こうばん》へ 取《と》りに 行《い》って ください」。「ああ、よかった」と、わたしは 思《おも》いました。',
            id: 'Setelah menunggu sekitar tiga puluh menit, ada telepon dari petugas stasiun. “Tasnya ada, lho. Besok tolong ambil di pos polisi depan stasiun.” “Ah, syukurlah,” pikir saya.',
            en: 'After waiting about thirty minutes, I got a call from the station staff. “We found your bag. Please go and pick it up at the police box in front of the station tomorrow.” “Oh, what a relief,” I thought.',
          },
          {
            ja: '今朝《けさ》、交番《こうばん》へ 行《い》きました。かばんの 中《なか》の 物《もの》は 全部《ぜんぶ》 ありました。これから 電車《でんしゃ》を 降《お》りる とき、よく 確《たし》かめます。',
            id: 'Pagi ini saya pergi ke pos polisi. Semua isi tas ada. Mulai sekarang, ketika turun dari kereta, saya akan memeriksa dengan teliti.',
            en: 'This morning I went to the police box. Everything in the bag was there. From now on, when I get off a train, I will check carefully.',
          },
        ],
      },
      vocab: [
        { ja: '網棚', reading: 'あみだな', id: 'rak bagasi (di kereta)', en: 'luggage rack' },
        { ja: '駅員', reading: 'えきいん', id: 'petugas stasiun', en: 'station staff' },
        { ja: '交番', reading: 'こうばん', id: 'pos polisi', en: 'police box' },
        { ja: '忘れ物', reading: 'わすれもの', id: 'barang tertinggal', en: 'a forgotten item' },
        { ja: '確か', reading: 'たしか', id: 'kalau tidak salah', en: 'if I remember correctly' },
        { ja: '小説', reading: 'しょうせつ', id: 'novel', en: 'novel' },
        { ja: '書類', reading: 'しょるい', id: 'dokumen', en: 'documents' },
        { ja: '全部', reading: 'ぜんぶ', id: 'semuanya', en: 'all, everything' },
        { ja: 'ああ、よかった', reading: 'ああ、よかった', id: 'Ah, syukurlah', en: 'Oh, what a relief' },
      ],
      questions: [
        {
          type: 'mc',
          q: '「わたし」は 電車《でんしゃ》の 中《なか》で、何《なに》を 聞《き》きながら 小説《しょうせつ》を 読《よ》んで いましたか。',
          choices: ['音楽《おんがく》', 'ラジオ', '友達《ともだち》の 話《はなし》', '駅員《えきいん》の 声《こえ》'],
          answer: 0,
          explain: { id: 'Paragraf 1: 音楽を 聞きながら、小説を 読んで いました.', en: 'Paragraph 1: 音楽を 聞きながら、小説を 読んで いました。' },
        },
        {
          type: 'mc',
          q: 'かばんを どこに 忘《わす》れて しまいましたか。',
          choices: ['網棚《あみだな》', '駅《えき》の ベンチ', '会社《かいしゃ》', '交番《こうばん》'],
          answer: 0,
          explain: { id: 'Paragraf 2: 網棚に 忘れて しまったんです.', en: 'Paragraph 2: 網棚に 忘れて しまったんです。' },
        },
        {
          type: 'mc',
          q: 'あとで 駅員《えきいん》は 何《なん》と 言《い》いましたか。',
          choices: [
            'かばんが ありました',
            'かばんは ありませんでした',
            'かばんは もう 売《う》って しまいました',
            'かばんは あした うちに 届《とど》きます',
          ],
          answer: 0,
          explain: { id: 'Paragraf 4: かばんが ありましたよ.', en: 'Paragraph 4: かばんが ありましたよ。' },
        },
        {
          type: 'tf',
          q: 'かばんの 中《なか》には 財布《さいふ》が 入《はい》って いました。',
          answer: true,
          explain: { id: 'Paragraf 2: 財布も 入って いたし… (dompet juga ada).', en: 'Paragraph 2: 財布も 入って いたし… (the wallet was in it).' },
        },
        {
          type: 'tf',
          q: 'かばんは 駅《えき》の 事務所《じむしょ》に ありました。',
          answer: false,
          explain: { id: 'Paragraf 4: 駅前の 交番へ 取りに 行って ください — tasnya ada di pos polisi.', en: 'Paragraph 4: 駅前の 交番へ 取りに… — the bag was at the police box.' },
        },
        {
          type: 'mc',
          q: '「わたし」は これから 電車《でんしゃ》を 降《お》りる とき、どうしますか。',
          choices: ['よく 確《たし》かめます', '小説《しょうせつ》を 読《よ》みます', '音楽《おんがく》を 聞《き》きます', '急《いそ》いで 降《お》ります'],
          answer: 0,
          explain: { id: 'Paragraf 5: 電車を 降りる とき、よく 確かめます.', en: 'Paragraph 5: 電車を 降りる とき、よく 確かめます。' },
        },
      ],
      challenge: {
        id: 'Ceritakan pengalamanmu kehilangan atau menemukan barang. Pakai 〜て しまいました dan 〜とき.',
        en: 'Describe a time you lost or found something. Use 〜て しまいました and 〜とき.',
      },
    },
    3: {
      title: { id: 'Persiapan liburan ke Hokkaido', en: 'Preparing for a trip to Hokkaido' },
      focus: {
        id: '〜て あります、〜て おきます、まだ 〜て いません、〜とか〜とか、〜（よ）うと 思って います、〜つもり、〜予定、ます形 + 名詞（帰り）',
        en: '〜て あります, 〜て おきます, まだ 〜て いません, 〜とか〜とか, 〜(よ)うと 思って います, 〜つもり, 〜予定, stem as noun (帰り)',
      },
      before: {
        id: 'Apa yang kamu siapkan sebelum bepergian jauh? Apakah kamu membuat daftar atau jadwal?',
        en: 'What do you prepare before a long trip? Do you make a list or a schedule?',
      },
      passage: {
        title: '旅行《りょこう》の 準備《じゅんび》',
        paragraphs: [
          {
            ja: '来月《らいげつ》、友達《ともだち》と 北海道《ほっかいどう》へ 旅行《りょこう》に 行《い》く 予定《よてい》です。雪《ゆき》を 見《み》たり、スキーとか 温泉《おんせん》とかを 楽《たの》しんだり しようと 思《おも》って います。',
            id: 'Bulan depan saya berencana bepergian ke Hokkaido bersama teman. Saya berniat melihat salju dan menikmati ski, pemandian air panas, dan sebagainya.',
            en: 'Next month I plan to travel to Hokkaido with a friend. I am thinking of seeing the snow and enjoying things like skiing and hot springs.',
          },
          {
            ja: 'ホテルは もう 予約《よやく》して あります。飛行機《ひこうき》の 切符《きっぷ》も 買《か》って あります。でも、まだ 地図《ちず》を 買《か》って いません。',
            id: 'Hotelnya sudah dipesan. Tiket pesawat juga sudah dibeli. Tetapi saya belum membeli peta.',
            en: 'The hotel is already booked. The plane tickets have been bought too. But I haven’t bought a map yet.',
          },
          {
            ja: '行《い》く 前《まえ》に、ガイドブックを 読《よ》んで おきます。それから、寒《さむ》いので、あたたかい 服《ふく》も かばんに 入《い》れて おく つもりです。',
            id: 'Sebelum berangkat, saya akan membaca buku panduan terlebih dahulu. Lalu, karena dingin, saya berniat memasukkan pakaian hangat ke dalam tas sebelumnya.',
            en: 'Before going, I will read the guidebook in advance. Also, since it is cold, I intend to put warm clothes in my bag ahead of time.',
          },
          {
            ja: 'カレンダーに 予定《よてい》が 書《か》いて あります。10日《とおか》に 出発《しゅっぱつ》して、13日《じゅうさんにち》に 帰《かえ》ります。帰《かえ》りの 飛行機《ひこうき》は 夜《よる》です。',
            id: 'Jadwalnya sudah tertulis di kalender. Berangkat tanggal 10 dan pulang tanggal 13. Pesawat pulangnya malam hari.',
            en: 'The schedule is written on the calendar. We leave on the 10th and come back on the 13th. The return flight is at night.',
          },
          {
            ja: 'まだ 準備《じゅんび》は 終《お》わって いませんが、早《はや》く 行《い》きたいです。',
            id: 'Persiapan belum selesai, tetapi saya ingin segera berangkat.',
            en: 'My preparations are not finished yet, but I want to go soon.',
          },
        ],
      },
      vocab: [
        { ja: '予定', reading: 'よてい', id: 'rencana, jadwal', en: 'plan, schedule' },
        { ja: '予約', reading: 'よやく', id: 'pemesanan', en: 'reservation' },
        { ja: '地図', reading: 'ちず', id: 'peta', en: 'map' },
        { ja: 'ガイドブック', reading: 'ガイドブック', id: 'buku panduan', en: 'guidebook' },
        { ja: 'カレンダー', reading: 'カレンダー', id: 'kalender', en: 'calendar' },
        { ja: '出発', reading: 'しゅっぱつ', id: 'keberangkatan', en: 'departure' },
        { ja: '温泉', reading: 'おんせん', id: 'pemandian air panas', en: 'hot spring' },
        { ja: '準備', reading: 'じゅんび', id: 'persiapan', en: 'preparation' },
        { ja: '帰り', reading: 'かえり', id: 'perjalanan pulang', en: 'return trip' },
      ],
      questions: [
        {
          type: 'mc',
          q: '「わたし」は 来月《らいげつ》、どこへ 行《い》きますか。',
          choices: ['北海道《ほっかいどう》', '京都《きょうと》', '沖縄《おきなわ》', '大阪《おおさか》'],
          answer: 0,
          explain: { id: 'Paragraf 1: 北海道へ 旅行に 行く 予定です.', en: 'Paragraph 1: 北海道へ 旅行に 行く 予定です。' },
        },
        {
          type: 'mc',
          q: 'ホテルは どう なって いますか。',
          choices: ['もう 予約《よやく》して あります', 'まだ 予約《よやく》して いません', '友達《ともだち》の うちです', 'まだ 決《き》めて いません'],
          answer: 0,
          explain: { id: 'Paragraf 2: ホテルは もう 予約して あります.', en: 'Paragraph 2: ホテルは もう 予約して あります。' },
        },
        {
          type: 'tf',
          q: '地図《ちず》は もう 買《か》って あります。',
          answer: false,
          explain: { id: 'Paragraf 2: まだ 地図を 買って いません.', en: 'Paragraph 2: まだ 地図を 買って いません。' },
        },
        {
          type: 'tf',
          q: '飛行機《ひこうき》の 切符《きっぷ》は 買《か》って あります。',
          answer: true,
          explain: { id: 'Paragraf 2: 飛行機の 切符も 買って あります.', en: 'Paragraph 2: 飛行機の 切符も 買って あります。' },
        },
        {
          type: 'mc',
          q: '行《い》く 前《まえ》に、何《なに》を して おきますか。',
          choices: ['ガイドブックを 読《よ》みます', 'スキーを します', '温泉《おんせん》に 入《はい》ります', '地図《ちず》を 買《か》います'],
          answer: 0,
          explain: { id: 'Paragraf 3: ガイドブックを 読んで おきます.', en: 'Paragraph 3: ガイドブックを 読んで おきます。' },
        },
        {
          type: 'mc',
          q: '何日《なんにち》に 帰《かえ》りますか。',
          choices: ['10日《とおか》', '11日《じゅういちにち》', '12日《じゅうににち》', '13日《じゅうさんにち》'],
          answer: 3,
          explain: { id: 'Paragraf 4: 10日に 出発して、13日に 帰ります.', en: 'Paragraph 4: leave on the 10th, return on the 13th.' },
        },
      ],
      challenge: {
        id: 'Tulis persiapan perjalananmu memakai 〜て あります, 〜て おきます, dan まだ 〜て いません.',
        en: 'Write your trip preparations using 〜て あります, 〜て おきます, and まだ 〜て いません.',
      },
    },
    4: {
      title: { id: 'Papan tanda di gunung', en: 'The sign on the mountain' },
      focus: {
        id: '命令形・禁止形（入るな）、〜と 書いて あります、〜と いう 意味です、〜と 言って いました、〜ほうが いい、〜かもしれません、〜て いただけませんか',
        en: 'imperative・prohibitive (入るな), 〜と 書いて あります, 〜と いう 意味です, 〜と 言って いました, 〜ほうが いい, 〜かもしれません, 〜て いただけませんか',
      },
      before: {
        id: 'Papan tanda apa yang pernah kamu lihat di jalan atau di tempat wisata? Apa artinya?',
        en: 'What signs have you seen on the street or at tourist spots? What did they mean?',
      },
      passage: {
        title: '山《やま》の 看板《かんばん》',
        paragraphs: [
          {
            ja: '日曜日《にちようび》、友達《ともだち》と 山《やま》に 登《のぼ》りました。出《で》かける 前《まえ》に、友達《ともだち》は 「今日《きょう》は 雨《あめ》が 降《ふ》るかもしれないから、傘《かさ》を 持《も》って 行《い》った ほうが いいよ」と 言《い》って いました。',
            id: 'Hari Minggu saya mendaki gunung bersama teman. Sebelum berangkat, teman berkata, “Hari ini mungkin hujan, jadi sebaiknya bawa payung.”',
            en: 'On Sunday I climbed a mountain with a friend. Before we left, my friend had said, “It might rain today, so we’d better take an umbrella.”',
          },
          {
            ja: '山《やま》の 入口《いりぐち》に 看板《かんばん》が ありました。「ここから 先《さき》は 入《はい》るな」と 書《か》いて ありました。「入《はい》るな」は 「入《はい》っては いけません」と いう 意味《いみ》です。',
            id: 'Di pintu masuk gunung ada papan tanda. Tertulis “Dilarang masuk dari sini ke depan.” 「入るな」 berarti “tidak boleh masuk”.',
            en: 'There was a sign at the entrance to the mountain. It said “Do not enter beyond this point.” 「入るな」 means “you must not enter”.',
          },
          {
            ja: '山《やま》の 上《うえ》で、友達《ともだち》は 「疲《つか》れたら、休《やす》め。無理《むり》を しない ほうが いいよ」と 言《い》いました。わたしは 少《すこ》し 休《やす》んで、お弁当《べんとう》を 食《た》べました。',
            id: 'Di puncak gunung, teman berkata, “Kalau lelah, istirahatlah. Sebaiknya jangan memaksakan diri.” Saya beristirahat sebentar lalu makan bekal.',
            en: 'At the top, my friend said, “If you’re tired, rest. You’d better not push yourself.” I rested a little and ate my lunch.',
          },
          {
            ja: '山《やま》を 下《お》りたあと、山田《やまだ》さんと 会《あ》う 約束《やくそく》が ありました。でも、間《ま》に 合《あ》わないかもしれないと 思《おも》いました。それで、山田《やまだ》さんの 会社《かいしゃ》に 電話《でんわ》して、受付《うけつけ》の 人《ひと》に 「少《すこ》し 遅《おく》れると 伝《つた》えて いただけませんか」と 頼《たの》みました。',
            id: 'Setelah turun gunung, saya punya janji bertemu Yamada. Tetapi saya merasa mungkin tidak akan sempat. Jadi saya menelepon kantor Yamada dan meminta petugas resepsionis, “Bisakah Anda menyampaikan bahwa saya akan sedikit terlambat?”',
            en: 'After coming down the mountain I had an appointment with Mr./Ms. Yamada, but I thought I might not make it in time. So I phoned Yamada’s company and asked the receptionist, “Could you please tell them I will be a little late?”',
          },
          {
            ja: '夕方《ゆうがた》、雨《あめ》が 降《ふ》りました。傘《かさ》を 持《も》って 行《い》って、よかったです。',
            id: 'Sore harinya hujan turun. Syukurlah saya membawa payung.',
            en: 'In the evening it rained. I was glad I had taken an umbrella.',
          },
        ],
      },
      vocab: [
        { ja: '看板', reading: 'かんばん', id: 'papan tanda', en: 'sign, signboard' },
        { ja: '入口', reading: 'いりぐち', id: 'pintu masuk', en: 'entrance' },
        { ja: '意味', reading: 'いみ', id: 'arti', en: 'meaning' },
        { ja: '無理', reading: 'むり', id: 'memaksakan diri, mustahil', en: 'overdoing it, impossible' },
        { ja: '約束', reading: 'やくそく', id: 'janji', en: 'appointment, promise' },
        { ja: '間に合う', reading: 'まにあう', id: 'sempat, tepat waktu', en: 'to be in time' },
        { ja: '受付', reading: 'うけつけ', id: 'resepsionis, meja penerima', en: 'reception' },
        { ja: '伝える', reading: 'つたえる', id: 'menyampaikan', en: 'to convey' },
      ],
      questions: [
        {
          type: 'mc',
          q: '出《で》かける 前《まえ》に、友達《ともだち》は 何《なん》と 言《い》って いましたか。',
          choices: [
            '傘《かさ》を 持《も》って 行《い》った ほうが いい',
            '山《やま》に 登《のぼ》らない ほうが いい',
            'お弁当《べんとう》は いらない',
            '早《はや》く 帰《かえ》れ',
          ],
          answer: 0,
          explain: { id: 'Paragraf 1: 雨が 降るかもしれないから、傘を 持って 行った ほうが いい.', en: 'Paragraph 1: it might rain, so take an umbrella.' },
        },
        {
          type: 'mc',
          q: '看板《かんばん》には 何《なん》と 書《か》いて ありましたか。',
          choices: ['ここから 先《さき》は 入《はい》るな', 'ここで 休《やす》め', 'この 道《みち》を 行《い》け', '写真《しゃしん》を とるな'],
          answer: 0,
          explain: { id: 'Paragraf 2: 「ここから 先は 入るな」と 書いて ありました.', en: 'Paragraph 2: it said 「ここから 先は 入るな」.' },
        },
        {
          type: 'mc',
          q: '「入《はい》るな」は どんな 意味《いみ》ですか。',
          choices: ['入《はい》っては いけません', '入《はい》って ください', '入《はい》りたいです', '入《はい》りました'],
          answer: 0,
          explain: { id: 'V辞書形＋な = larangan keras: tidak boleh masuk.', en: 'V dictionary form + な is a strong prohibition: you must not enter.' },
        },
        {
          type: 'tf',
          q: '友達《ともだち》は 山《やま》の 上《うえ》で 「疲《つか》れたら、休《やす》め」と 言《い》いました。',
          answer: true,
          explain: { id: 'Paragraf 3: 疲れたら、休め (休め = bentuk perintah).', en: 'Paragraph 3: 疲れたら、休め (休め is the imperative).' },
        },
        {
          type: 'tf',
          q: '夕方《ゆうがた》、雨《あめ》は 降《ふ》りませんでした。',
          answer: false,
          explain: { id: 'Paragraf 5: 夕方、雨が 降りました.', en: 'Paragraph 5: 夕方、雨が 降りました。' },
        },
        {
          type: 'mc',
          q: '「わたし」は 受付《うけつけ》の 人《ひと》に 何《なに》を 頼《たの》みましたか。',
          choices: [
            '少《すこ》し 遅《おく》れると 伝《つた》える こと',
            '山田《やまだ》さんの 電話《でんわ》を 教《おし》える こと',
            '約束《やくそく》を やめる こと',
            '傘《かさ》を 貸《か》す こと',
          ],
          answer: 0,
          explain: { id: 'Paragraf 4: 少し 遅れると 伝えて いただけませんか.', en: 'Paragraph 4: could you tell them I will be a little late.' },
        },
      ],
      challenge: {
        id: 'Tulis arti satu tanda larangan yang kamu kenal memakai 〜と いう 意味です, lalu sampaikan pesan memakai 〜と 伝えて いただけませんか.',
        en: 'Explain one prohibition sign you know with 〜と いう 意味です, then pass on a message with 〜と 伝えて いただけませんか.',
      },
    },
    5: {
      title: { id: 'Kelas memasak', en: 'A cooking class' },
      focus: {
        id: '〜た とおりに、〜た あとで、〜て V（座って 食べる）、〜ないで V、〜ば／〜ければ、〜なら',
        en: '〜た とおりに, 〜た あとで, 〜て V (eat sitting down), 〜ないで V, 〜ば／〜ければ, 〜なら',
      },
      before: {
        id: 'Pernahkah kamu belajar memasak dari seorang guru atau dari video? Apa yang sulit?',
        en: 'Have you ever learned to cook from a teacher or a video? What was difficult?',
      },
      passage: {
        title: '料理《りょうり》教室《きょうしつ》',
        paragraphs: [
          {
            ja: '先週《せんしゅう》、料理《りょうり》教室《きょうしつ》に 行《い》きました。先生《せんせい》が 作《つく》った とおりに、わたしも 卵焼《たまごや》きを 作《つく》りました。',
            id: 'Minggu lalu saya pergi ke kelas memasak. Saya juga membuat telur gulung persis seperti yang dibuat guru.',
            en: 'Last week I went to a cooking class. I made a rolled omelet exactly the way the teacher had made it.',
          },
          {
            ja: '最初《さいしょ》は 上手《じょうず》に できませんでした。先生《せんせい》は 「卵《たまご》を 割《わ》る とき、強《つよ》く たたかないで 割《わ》って ください」と 言《い》いました。',
            id: 'Awalnya saya tidak bisa melakukannya dengan baik. Guru berkata, “Saat memecahkan telur, pecahkan tanpa memukulnya terlalu keras.”',
            en: 'At first I could not do it well. The teacher said, “When you crack the egg, crack it without hitting it hard.”',
          },
          {
            ja: '練習《れんしゅう》した あとで、もう 一度《いちど》 作《つく》りました。今度《こんど》は うまく できました。',
            id: 'Setelah berlatih, saya membuatnya sekali lagi. Kali ini berhasil dengan baik.',
            en: 'After practicing, I made it once more. This time I did it well.',
          },
          {
            ja: 'みんなで 座《すわ》って 食《た》べました。先生《せんせい》は 「ゆっくり 食《た》べれば、もっと おいしく 感《かん》じますよ」と 言《い》いました。',
            id: 'Kami semua makan sambil duduk. Guru berkata, “Kalau makan perlahan, rasanya akan terasa lebih enak.”',
            en: 'We all sat down and ate. The teacher said, “If you eat slowly, it will taste even better.”',
          },
          {
            ja: 'この 教室《きょうしつ》は 安《やす》くて、駅《えき》から 近《ちか》いです。習《なら》いたければ、来月《らいげつ》から 始《はじ》められます。料理《りょうり》が 好《す》きなら、一緒《いっしょ》に 行《い》きませんか。',
            id: 'Kelas ini murah dan dekat dari stasiun. Kalau ingin belajar, bisa mulai bulan depan. Kalau Anda suka memasak, maukah pergi bersama?',
            en: 'This class is cheap and close to the station. If you want to learn, you can start next month. If you like cooking, why not go together?',
          },
        ],
      },
      vocab: [
        { ja: '卵焼き', reading: 'たまごやき', id: 'telur gulung', en: 'rolled omelet' },
        { ja: '割る', reading: 'わる', id: 'memecahkan', en: 'to crack, to break' },
        { ja: '強く', reading: 'つよく', id: 'dengan keras, kuat', en: 'strongly, hard' },
        { ja: '今度', reading: 'こんど', id: 'kali ini, lain kali', en: 'this time, next time' },
        { ja: '座る', reading: 'すわる', id: 'duduk', en: 'to sit' },
        { ja: '習う', reading: 'ならう', id: 'belajar (dari guru)', en: 'to learn' },
        { ja: '始める', reading: 'はじめる', id: 'memulai', en: 'to start' },
        { ja: '教室', reading: 'きょうしつ', id: 'kelas, ruang kelas', en: 'classroom, class' },
      ],
      questions: [
        {
          type: 'mc',
          q: '先生《せんせい》が 作《つく》った とおりに、何《なに》を 作《つく》りましたか。',
          choices: ['卵焼《たまごや》き', 'カレー', 'ケーキ', 'みそ汁《しる》'],
          answer: 0,
          explain: { id: 'Paragraf 1: 先生が 作った とおりに、卵焼きを 作りました.', en: 'Paragraph 1: made 卵焼き just as the teacher did.' },
        },
        {
          type: 'mc',
          q: '先生《せんせい》は 卵《たまご》を 割《わ》る とき、何《なん》と 言《い》いましたか。',
          choices: [
            '強《つよ》く たたかないで 割《わ》って ください',
            '強《つよ》く たたいて 割《わ》って ください',
            '割《わ》らないで ください',
            '水《みず》で 洗《あら》って ください',
          ],
          answer: 0,
          explain: { id: 'Paragraf 2: 強く たたかないで 割って ください (〜ないで V).', en: 'Paragraph 2: 強く たたかないで 割って ください (〜ないで V).' },
        },
        {
          type: 'tf',
          q: '最初《さいしょ》から 上手《じょうず》に できました。',
          answer: false,
          explain: { id: 'Paragraf 2: 最初は 上手に できませんでした.', en: 'Paragraph 2: at first it did not go well.' },
        },
        {
          type: 'mc',
          q: '練習《れんしゅう》した あとで、どう なりましたか。',
          choices: ['今度《こんど》は うまく できた', 'もっと 下手《へた》に なった', '料理《りょうり》が いやに なった', '先生《せんせい》が 帰《かえ》った'],
          answer: 0,
          explain: { id: 'Paragraf 3: 今度は うまく できました.', en: 'Paragraph 3: this time it went well.' },
        },
        {
          type: 'tf',
          q: '先生《せんせい》は 「立《た》って 食《た》べた ほうが いい」と 言《い》いました。',
          answer: false,
          explain: { id: 'Paragraf 4: みんなで 座って 食べました。先生は ゆっくり 食べれば、と 言いました.', en: 'Paragraph 4: they sat to eat; the teacher said to eat slowly.' },
        },
        {
          type: 'mc',
          q: '習《なら》いたければ、いつから 始《はじ》められますか。',
          choices: ['来月《らいげつ》から', '来週《らいしゅう》から', '今日《きょう》から', '来年《らいねん》から'],
          answer: 0,
          explain: { id: 'Paragraf 5: 習いたければ、来月から 始められます.', en: 'Paragraph 5: if you want to learn, you can start next month.' },
        },
      ],
      challenge: {
        id: 'Jelaskan cara membuat masakan favoritmu. Pakai 〜た とおりに, 〜た あとで, dan 〜ないで.',
        en: 'Explain how to make your favorite dish. Use 〜た とおりに, 〜た あとで, and 〜ないで.',
      },
    },
    6: {
      title: { id: 'Kakek di kota tua', en: 'Grandfather in the old town' },
      focus: {
        id: '〜ように（tujuan）、〜ように なります、〜ように します、〜ように して ください、受身（〜られます）、〜から 作られます、い形容詞 → 〜く',
        en: '〜ように (purpose), 〜ように なります, 〜ように します, 〜ように して ください, passive (〜られます), 〜から 作られます, い-adjective → 〜く',
      },
      before: {
        id: 'Kebiasaan apa yang kamu jalani demi kesehatan? Adakah bangunan tua di kotamu?',
        en: 'What habits do you keep for your health? Are there old buildings in your town?',
      },
      passage: {
        title: '古《ふる》い 町《まち》の 祖父《そふ》',
        paragraphs: [
          {
            ja: '祖父《そふ》は 七十歳《ななじゅっさい》ですが、とても 元気《げんき》です。健康《けんこう》の ために、毎朝《まいあさ》 三十分《さんじゅっぷん》 歩《ある》くように して います。',
            id: 'Kakek saya berumur tujuh puluh tahun, tetapi sangat sehat. Demi kesehatan, ia berusaha berjalan tiga puluh menit setiap pagi.',
            en: 'My grandfather is seventy but very healthy. For his health, he makes a point of walking for thirty minutes every morning.',
          },
          {
            ja: '去年《きょねん》は 足《あし》が 痛《いた》くて あまり 歩《ある》けませんでしたが、今《いま》は 遠《とお》くまで 歩《ある》けるように なりました。',
            id: 'Tahun lalu kakinya sakit sehingga hampir tidak bisa berjalan, tetapi sekarang ia sudah bisa berjalan jauh.',
            en: 'Last year his legs hurt and he could hardly walk, but now he has become able to walk a long way.',
          },
          {
            ja: '祖父《そふ》の 家《いえ》の 近《ちか》くには、二百年前《にひゃくねんまえ》に 建《た》てられた お寺《てら》が あります。この お寺《てら》は 町《まち》の 人《ひと》に 大切《たいせつ》に されて います。',
            id: 'Dekat rumah kakek ada sebuah kuil yang dibangun dua ratus tahun lalu. Kuil ini dijaga dengan baik oleh warga kota.',
            en: 'Near my grandfather’s house there is a temple that was built two hundred years ago. It is treasured by the people of the town.',
          },
          {
            ja: '祖父《そふ》は お酒《さけ》が 好《す》きです。この 町《まち》の お酒《さけ》は 米《こめ》から 作《つく》られます。でも、医者《いしゃ》に 「飲《の》みすぎないように して ください」と 言《い》われました。',
            id: 'Kakek suka minuman keras. Sake di kota ini dibuat dari beras. Tetapi dokter berkata kepadanya, “Usahakan jangan minum berlebihan.”',
            en: 'My grandfather likes sake. The sake of this town is made from rice. But the doctor told him, “Please try not to drink too much.”',
          },
          {
            ja: 'わたしは 祖父《そふ》に 「風邪《かぜ》を ひかないように、あたたかい 服《ふく》を 着《き》て ください」と 言《い》いました。祖父《そふ》は 「ありがとう。これからも 早《はや》く 起《お》きるように するよ」と 笑《わら》いました。',
            id: 'Saya berkata kepada kakek, “Supaya tidak masuk angin, tolong pakai baju hangat.” Kakek tertawa, “Terima kasih. Mulai sekarang pun aku akan berusaha bangun pagi.”',
            en: 'I told my grandfather, “So that you don’t catch a cold, please wear warm clothes.” He laughed, “Thanks. I’ll keep trying to get up early from now on, too.”',
          },
        ],
      },
      vocab: [
        { ja: '健康', reading: 'けんこう', id: 'kesehatan', en: 'health' },
        { ja: '痛い', reading: 'いたい', id: 'sakit, nyeri', en: 'painful' },
        { ja: '建てる', reading: 'たてる', id: 'membangun', en: 'to build' },
        { ja: '大切にする', reading: 'たいせつにする', id: 'menjaga dengan baik', en: 'to treasure, to take care of' },
        { ja: '米', reading: 'こめ', id: 'beras', en: 'rice (uncooked)' },
        { ja: '医者', reading: 'いしゃ', id: 'dokter', en: 'doctor' },
        { ja: '風邪をひく', reading: 'かぜをひく', id: 'masuk angin, flu', en: 'to catch a cold' },
      ],
      questions: [
        {
          type: 'mc',
          q: '祖父《そふ》は 何《なん》の ために 毎朝《まいあさ》 歩《ある》いて いますか。',
          choices: ['健康《けんこう》の ため', '仕事《しごと》の ため', 'お寺《てら》に 行《い》く ため', '犬《いぬ》の 散歩《さんぽ》の ため'],
          answer: 0,
          explain: { id: 'Paragraf 1: 健康の ために、毎朝 歩くように して います.', en: 'Paragraph 1: for his health he walks every morning.' },
        },
        {
          type: 'tf',
          q: '祖父《そふ》は 去年《きょねん》も 遠《とお》くまで 歩《ある》けました。',
          answer: false,
          explain: { id: 'Paragraf 2: 去年は あまり 歩けませんでした.', en: 'Paragraph 2: last year he could hardly walk.' },
        },
        {
          type: 'mc',
          q: '祖父《そふ》は 今《いま》、どう なりましたか。',
          choices: [
            '遠《とお》くまで 歩《ある》けるように なった',
            '足《あし》が 痛《いた》く なった',
            '歩《ある》かなく なった',
            'お酒《さけ》が 飲《の》めなく なった',
          ],
          answer: 0,
          explain: { id: '〜ように なりました = perubahan: sekarang bisa berjalan jauh.', en: '〜ように なりました = change: now he can walk far.' },
        },
        {
          type: 'mc',
          q: 'お寺《てら》に ついて、本文《ほんぶん》と 合《あ》うのは どれですか。',
          choices: [
            '二百年前《にひゃくねんまえ》に 建《た》てられた',
            '最近《さいきん》 建《た》てられた',
            '祖父《そふ》が 建《た》てた',
            '町《まち》の 人《ひと》は あまり 行《い》かない',
          ],
          answer: 0,
          explain: { id: 'Paragraf 3: 二百年前に 建てられた お寺 (pasif).', en: 'Paragraph 3: a temple built (passive) two hundred years ago.' },
        },
        {
          type: 'mc',
          q: '医者《いしゃ》は 祖父《そふ》に 何《なん》と 言《い》いましたか。',
          choices: [
            '飲《の》みすぎないように して ください',
            '毎日《まいにち》 飲《の》むように して ください',
            'お酒《さけ》を 作《つく》って ください',
            'もっと 歩《ある》いて ください',
          ],
          answer: 0,
          explain: { id: 'Paragraf 4: 飲みすぎないように して ください.', en: 'Paragraph 4: please try not to drink too much.' },
        },
        {
          type: 'tf',
          q: 'この 町《まち》の お酒《さけ》は 米《こめ》から 作《つく》られます。',
          answer: true,
          explain: { id: 'Paragraf 4: 米から 作られます (bahan → から).', en: 'Paragraph 4: made from rice (material → から).' },
        },
        {
          type: 'mc',
          q: '祖父《そふ》は これからも 何《なに》を する つもりですか。',
          choices: ['早《はや》く 起《お》きるように する', 'お酒《さけ》を やめる', '町《まち》を 出《で》る', '仕事《しごと》を 始《はじ》める'],
          answer: 0,
          explain: { id: 'Paragraf 5: これからも 早く 起きるように するよ.', en: 'Paragraph 5: he will keep trying to get up early.' },
        },
      ],
      challenge: {
        id: 'Tulis tiga hal yang kamu usahakan demi kesehatan memakai 〜ように して います.',
        en: 'Write three things you make a point of doing for your health using 〜ように して います.',
      },
    },
    7: {
      title: { id: 'Maaf, aku terlambat', en: 'Sorry, I’m late' },
      focus: {
        id: 'V のが 好きです、V のを 忘れました、ふつうけい のを 知って いますか、〜て／〜なくて（sebab）、N で（sebab）、〜ので、途中で',
        en: 'V のが 好きです, V のを 忘れました, plain form のを 知って いますか, 〜て／〜なくて (cause), N で (cause), 〜ので, 途中で',
      },
      before: {
        id: 'Pernahkah kamu terlambat ke janji temu? Apa penyebabnya?',
        en: 'Have you ever been late to an appointment? What was the reason?',
      },
      passage: {
        title: '遅《おく》れて ごめんなさい',
        paragraphs: [
          {
            ja: 'わたしは 部屋《へや》を 片付《かたづ》けるのが 好《す》きです。でも、朝《あさ》 早《はや》く 起《お》きるのは あまり 得意《とくい》じゃ ありません。',
            id: 'Saya suka membereskan kamar. Tetapi saya kurang pandai bangun pagi.',
            en: 'I like tidying my room. But I am not very good at getting up early.',
          },
          {
            ja: '昨日《きのう》は 友達《ともだち》と 会《あ》う 約束《やくそく》が ありました。起《お》きる 時間《じかん》が 遅《おそ》くなって、友達《ともだち》に 電話《でんわ》を するのを 忘《わす》れました。',
            id: 'Kemarin saya punya janji bertemu teman. Karena bangun kesiangan, saya lupa menelepon teman.',
            en: 'Yesterday I had an appointment to meet a friend. I got up late and forgot to call my friend.',
          },
          {
            ja: '急《いそ》いで 家《いえ》を 出《で》ましたが、電車《でんしゃ》に 乗《の》った 途中《とちゅう》で、事故《じこ》で 電車《でんしゃ》が 止《と》まりました。',
            id: 'Saya buru-buru keluar rumah, tetapi di tengah perjalanan setelah naik kereta, kereta berhenti karena kecelakaan.',
            en: 'I rushed out of the house, but partway through the ride the train stopped because of an accident.',
          },
          {
            ja: '電車《でんしゃ》が 動《うご》かなかった ので、一時間《いちじかん》 遅《おく》れて 着《つ》きました。友達《ともだち》は 約束《やくそく》の 場所《ばしょ》で 待《ま》って いました。',
            id: 'Karena kereta tidak bergerak, saya tiba terlambat satu jam. Teman saya menunggu di tempat janji.',
            en: 'Because the train did not move, I arrived an hour late. My friend was waiting at the meeting place.',
          },
          {
            ja: '「遅《おく》れて ごめんなさい。電車《でんしゃ》が 止《と》まったのを 知《し》って いますか」と 聞《き》くと、友達《ともだち》は 「連絡《れんらく》が なくて、心配《しんぱい》したよ。でも、会《あ》えて うれしい」と 言《い》いました。',
            id: 'Ketika saya bertanya, “Maaf terlambat. Tahukah kamu kereta berhenti?” teman saya berkata, “Karena tidak ada kabar, aku khawatir. Tapi aku senang bisa bertemu.”',
            en: 'When I asked, “Sorry I’m late. Did you know the train stopped?” my friend said, “I was worried since there was no word. But I’m glad we could meet.”',
          },
          {
            ja: 'これからは、遅《おく》れる とき、すぐ 電話《でんわ》を かけるのを 忘《わす》れないように します。',
            id: 'Mulai sekarang, kalau terlambat, saya akan berusaha tidak lupa langsung menelepon.',
            en: 'From now on, when I am going to be late, I will make sure not to forget to call right away.',
          },
        ],
      },
      vocab: [
        { ja: '片付ける', reading: 'かたづける', id: 'membereskan', en: 'to tidy up' },
        { ja: '約束', reading: 'やくそく', id: 'janji', en: 'appointment, promise' },
        { ja: '途中', reading: 'とちゅう', id: 'di tengah jalan', en: 'on the way, midway' },
        { ja: '事故', reading: 'じこ', id: 'kecelakaan', en: 'accident' },
        { ja: '連絡', reading: 'れんらく', id: 'kabar, kontak', en: 'contact, word' },
        { ja: '心配する', reading: 'しんぱいする', id: 'khawatir', en: 'to worry' },
        { ja: '得意', reading: 'とくい', id: 'pandai, andalan', en: 'good at' },
      ],
      questions: [
        {
          type: 'mc',
          q: 'わたしは 何《なに》が 好《す》きですか。',
          choices: [
            '部屋《へや》を 片付《かたづ》けるの',
            '料理《りょうり》を 作《つく》るの',
            '電車《でんしゃ》に 乗《の》るの',
            '早《はや》く 起《お》きるの',
          ],
          answer: 0,
          explain: { id: 'Paragraf 1: 部屋を 片付けるのが 好きです.', en: 'Paragraph 1: I like tidying my room.' },
        },
        {
          type: 'mc',
          q: 'わたしは 何《なに》を 忘《わす》れましたか。',
          choices: [
            '友達《ともだち》に 電話《でんわ》を する こと',
            'かさを 持《も》って 行《い》く こと',
            '約束《やくそく》の 場所《ばしょ》',
            'お金《かね》を 持《も》って 行《い》く こと',
          ],
          answer: 0,
          explain: { id: 'Paragraf 2: 電話を するのを 忘れました.', en: 'Paragraph 2: I forgot to make the call.' },
        },
        {
          type: 'mc',
          q: '電車《でんしゃ》は どうして 止《と》まりましたか。',
          choices: [
            '事故《じこ》が あった ので',
            '雨《あめ》が 降《ふ》った ので',
            '駅《えき》が 閉《し》まった ので',
            '電車《でんしゃ》が 古《ふる》い ので',
          ],
          answer: 0,
          explain: { id: 'Paragraf 3: 事故で 電車が 止まりました (N で = sebab).', en: 'Paragraph 3: the train stopped due to an accident (N で = cause).' },
        },
        {
          type: 'tf',
          q: 'わたしは 約束《やくそく》の 時間《じかん》に 着《つ》きました。',
          answer: false,
          explain: { id: 'Paragraf 4: 一時間 遅れて 着きました.', en: 'Paragraph 4: I arrived an hour late.' },
        },
        {
          type: 'mc',
          q: 'わたしは 何時間《なんじかん》 遅《おく》れましたか。',
          choices: ['一時間《いちじかん》', '三十分《さんじゅっぷん》', '二時間《にじかん》', '十分《じゅっぷん》'],
          answer: 0,
          explain: { id: 'Paragraf 4: 一時間 遅れて.', en: 'Paragraph 4: an hour late.' },
        },
        {
          type: 'mc',
          q: '友達《ともだち》は どうして 心配《しんぱい》しましたか。',
          choices: [
            '連絡《れんらく》が なかった ので',
            '電車《でんしゃ》が 止《と》まった ので',
            'わたしが 病気《びょうき》だった ので',
            '約束《やくそく》を 忘《わす》れた ので',
          ],
          answer: 0,
          explain: { id: 'Paragraf 5: 連絡が なくて、心配したよ (〜なくて = sebab).', en: 'Paragraph 5: 連絡が なくて、心配した (〜なくて = cause).' },
        },
        {
          type: 'tf',
          q: 'わたしは これから、遅《おく》れる とき 電話《でんわ》を するように します。',
          answer: true,
          explain: { id: 'Paragraf 6: 電話を かけるのを 忘れないように します.', en: 'Paragraph 6: I will make sure not to forget to call.' },
        },
      ],
      challenge: {
        id: 'Tulis tentang saat kamu terlambat atau lupa sesuatu. Pakai 〜ので、〜て／〜なくて、〜のを 忘れました.',
        en: 'Write about a time you were late or forgot something. Use 〜ので, 〜て／〜なくて, 〜のを 忘れました.',
      },
    },
    8: {
      title: { id: 'Hadiah untuk guru', en: 'A present for my teacher' },
      focus: {
        id: '〜か どうか、疑問詞＋か（わかりません）、〜て みます、い形容詞 → 〜さ、〜でしょうか、いただきます、くださいます',
        en: '〜か どうか, question word + か (I don’t know…), 〜て みます, い-adjective → 〜さ, 〜でしょうか, いただきます, くださいます',
      },
      before: {
        id: 'Apa yang biasanya kamu berikan kepada guru atau orang yang berjasa saat berpisah?',
        en: 'What do you usually give to a teacher or someone who helped you when parting?',
      },
      passage: {
        title: '先生《せんせい》への プレゼント',
        paragraphs: [
          {
            ja: '来月《らいげつ》、お世話《せわ》に なった 先生《せんせい》が 田舎《いなか》へ 帰《かえ》ります。何《なに》を あげたら いいか、まだ わかりません。',
            id: 'Bulan depan guru yang banyak membantu saya akan pulang ke kampung. Saya belum tahu harus memberi apa.',
            en: 'Next month my teacher, who has looked after me, is going back to the countryside. I still don’t know what I should give.',
          },
          {
            ja: '友達《ともだち》に 「先生《せんせい》は お茶《ちゃ》が 好《す》きかどうか、知《し》って いますか」と 聞《き》きました。友達《ともだち》は 先生《せんせい》に 聞《き》いて みました。',
            id: 'Saya bertanya kepada teman, “Tahukah kamu apakah guru suka teh?” Teman saya mencoba bertanya kepada guru.',
            en: 'I asked a friend, “Do you know whether our teacher likes tea?” My friend tried asking the teacher.',
          },
          {
            ja: '先生《せんせい》は 「お茶《ちゃ》は 好《す》きですが、軽《かる》い ものが いいです」と 答《こた》えました。荷物《にもつ》が 多《おお》いからです。',
            id: 'Guru menjawab, “Saya suka teh, tetapi yang ringan saja.” Sebab barang bawaannya banyak.',
            en: 'The teacher answered, “I like tea, but a light one would be best,” because he has a lot of luggage.',
          },
          {
            ja: 'お店《みせ》で 店員《てんいん》に 「この お茶《ちゃ》を 飲《の》んで みても いいでしょうか」と 聞《き》くと、「どうぞ」と 言《い》って、お茶《ちゃ》を 入《い》れて くださいました。',
            id: 'Di toko saya bertanya kepada pegawai, “Bolehkah saya mencoba minum teh ini?” Ia menjawab, “Silakan,” dan menyeduhkan teh untuk saya.',
            en: 'At the shop I asked the clerk, “May I try drinking this tea?” She said, “Please,” and kindly made some tea for me.',
          },
          {
            ja: '味《あじ》が よかった ので、それを 買《か》いました。箱《はこ》の 大《おお》きさも ちょうど よかったです。',
            id: 'Karena rasanya enak, saya membeli teh itu. Ukuran kotaknya pun pas.',
            en: 'The taste was good, so I bought it. The size of the box was just right, too.',
          },
          {
            ja: '先生《せんせい》に お茶《ちゃ》を 渡《わた》すと、先生《せんせい》は 「ありがとう。去年《きょねん》も 生徒《せいと》に お茶《ちゃ》を いただきましたよ」と 笑《わら》いました。',
            id: 'Ketika saya menyerahkan teh itu, guru tertawa, “Terima kasih. Tahun lalu pun saya menerima teh dari murid.”',
            en: 'When I handed over the tea, the teacher laughed, “Thank you. Last year, too, I received tea from a student.”',
          },
        ],
      },
      vocab: [
        { ja: 'お世話になる', reading: 'おせわになる', id: 'banyak dibantu', en: 'to be looked after' },
        { ja: '田舎', reading: 'いなか', id: 'kampung halaman', en: 'countryside' },
        { ja: '軽い', reading: 'かるい', id: 'ringan', en: 'light (weight)' },
        { ja: '店員', reading: 'てんいん', id: 'pegawai toko', en: 'shop clerk' },
        { ja: '大きさ', reading: 'おおきさ', id: 'ukuran', en: 'size' },
        { ja: '渡す', reading: 'わたす', id: 'menyerahkan', en: 'to hand over' },
        { ja: '生徒', reading: 'せいと', id: 'murid', en: 'pupil, student' },
      ],
      questions: [
        {
          type: 'mc',
          q: 'わたしは 何《なに》に 困《こま》って いましたか。',
          choices: ['先生《せんせい》への プレゼント', '引《ひ》っ越《こ》しの 場所《ばしょ》', '試験《しけん》の 勉強《べんきょう》', '旅行《りょこう》の 予約《よやく》'],
          answer: 0,
          explain: { id: 'Paragraf 1: 何を あげたら いいか、まだ わかりません.', en: 'Paragraph 1: I don’t yet know what to give.' },
        },
        {
          type: 'mc',
          q: '友達《ともだち》は 何《なに》を しましたか。',
          choices: ['先生《せんせい》に 聞《き》いて みた', 'お茶《ちゃ》を 買《か》った', '店《みせ》に 電話《でんわ》した', '先生《せんせい》の 家《いえ》へ 行《い》った'],
          answer: 0,
          explain: { id: 'Paragraf 2: 先生に 聞いて みました (〜て みます = mencoba).', en: 'Paragraph 2: tried asking the teacher (〜て みます = try doing).' },
        },
        {
          type: 'tf',
          q: '先生《せんせい》は 重《おも》い お茶《ちゃ》が いいと 言《い》いました。',
          answer: false,
          explain: { id: 'Paragraf 3: 軽い ものが いい (yang ringan).', en: 'Paragraph 3: a light one is better.' },
        },
        {
          type: 'mc',
          q: 'わたしは 店《みせ》で 何《なに》を 聞《き》きましたか。',
          choices: ['飲《の》んで みても いいか', '値段《ねだん》が 高《たか》いか', 'いつ 閉《し》まるか', '先生《せんせい》が 好《す》きか'],
          answer: 0,
          explain: { id: 'Paragraf 4: 飲んで みても いいでしょうか.', en: 'Paragraph 4: whether I may try drinking it.' },
        },
        {
          type: 'tf',
          q: '店員《てんいん》は お茶《ちゃ》を 入《い》れて くださいました。',
          answer: true,
          explain: { id: 'Paragraf 4: お茶を 入れて くださいました (〜て くださる).', en: 'Paragraph 4: she made tea for me (〜て くださる).' },
        },
        {
          type: 'mc',
          q: '先生《せんせい》は 去年《きょねん》、何《なに》を いただきましたか。',
          choices: ['お茶《ちゃ》', '花《はな》', '本《ほん》', 'お菓子《かし》'],
          answer: 0,
          explain: { id: 'Paragraf 6: 去年も 生徒に お茶を いただきました.', en: 'Paragraph 6: last year too he received tea from a student.' },
        },
        {
          type: 'mc',
          q: '「お茶《ちゃ》が 好《す》きかどうか」は どういう 意味《いみ》ですか。',
          choices: [
            'お茶《ちゃ》が 好《す》きか 好《す》きじゃ ないか',
            'お茶《ちゃ》を 作《つく》るか どうか',
            'お茶《ちゃ》が 安《やす》いか',
            'お茶《ちゃ》を 飲《の》んだか',
          ],
          answer: 0,
          explain: { id: '〜か どうか = apakah ... atau tidak.', en: '〜か どうか = whether or not.' },
        },
      ],
      challenge: {
        id: 'Tulis tentang hadiah yang pernah kamu beri atau terima. Pakai 〜か どうか、〜て みます、いただきます／くださいます.',
        en: 'Write about a present you gave or received. Use 〜か どうか, 〜て みます, いただきます／くださいます.',
      },
    },
    9: {
      title: { id: 'Celengan untuk liburan', en: 'A piggy bank for a trip' },
      focus: {
        id: '〜ために（tujuan）、〜のに（kegunaan）、数＋も、〜によって（pelaku pasif）、〜そうです（kelihatannya）、〜て きます、〜て くれませんか',
        en: '〜ために (purpose), 〜のに (use), number + も, 〜によって (agent of passive), 〜そうです (looks like), 〜て きます, 〜て くれませんか',
      },
      before: {
        id: 'Untuk apa kamu menabung sekarang? Berapa yang kamu tabung setiap bulan?',
        en: 'What are you saving for now? How much do you save each month?',
      },
      passage: {
        title: '貯金箱《ちょきんばこ》',
        paragraphs: [
          {
            ja: '旅行《りょこう》に 行《い》く ために、毎月《まいつき》 三万円《さんまんえん》も 貯金《ちょきん》して います。',
            id: 'Demi pergi berlibur, saya menabung sampai tiga puluh ribu yen setiap bulan.',
            en: 'To go on a trip, I save as much as 30,000 yen every month.',
          },
          {
            ja: 'この 貯金箱《ちょきんばこ》は 木《き》で、友達《ともだち》に よって 作《つく》られました。',
            id: 'Celengan ini terbuat dari kayu dan dibuat oleh teman saya.',
            en: 'This piggy bank is made of wood and was made by a friend.',
          },
          {
            ja: '貯金箱《ちょきんばこ》は お金《かね》を 入《い》れるのに 便利《べんり》です。毎日《まいにち》 百円《ひゃくえん》は 入《い》れます。',
            id: 'Celengan itu praktis untuk memasukkan uang. Setiap hari saya memasukkan sedikitnya seratus yen.',
            en: 'The piggy bank is handy for putting money in. Every day I put in at least 100 yen.',
          },
          {
            ja: '今日《きょう》は 友達《ともだち》の 家《いえ》へ 行《い》って きます。友達《ともだち》は おいしそうな ケーキを 作《つく》って いました。',
            id: 'Hari ini saya pergi ke rumah teman. Teman saya sedang membuat kue yang kelihatannya enak.',
            en: 'Today I am going to my friend’s house. He was making a cake that looked delicious.',
          },
          {
            ja: '来月《らいげつ》の 旅行《りょこう》の ために、友達《ともだち》に 「地図《ちず》を 書《か》いて くれませんか」と 頼《たの》みました。友達《ともだち》は 「いいよ」と 言《い》って、すぐ 書《か》いて くれました。',
            id: 'Untuk liburan bulan depan, saya meminta teman, “Bisakah kamu menggambarkan peta?” Teman saya menjawab, “Boleh,” dan langsung menggambarkannya.',
            en: 'For next month’s trip I asked my friend, “Could you draw a map for me?” He said, “Sure,” and drew one right away.',
          },
        ],
      },
      vocab: [
        { ja: '貯金', reading: 'ちょきん', id: 'tabungan', en: 'savings' },
        { ja: '貯金箱', reading: 'ちょきんばこ', id: 'celengan', en: 'piggy bank' },
        { ja: '便利', reading: 'べんり', id: 'praktis', en: 'convenient' },
        { ja: '地図', reading: 'ちず', id: 'peta', en: 'map' },
        { ja: '頼む', reading: 'たのむ', id: 'meminta (tolong)', en: 'to ask, to request' },
        { ja: '毎月', reading: 'まいつき', id: 'setiap bulan', en: 'every month' },
        { ja: '来月', reading: 'らいげつ', id: 'bulan depan', en: 'next month' },
      ],
      questions: [
        {
          type: 'mc',
          q: 'わたしは 何《なん》の ために 貯金《ちょきん》して いますか。',
          choices: ['旅行《りょこう》', '結婚《けっこん》', '車《くるま》', '勉強《べんきょう》'],
          answer: 0,
          explain: { id: 'Paragraf 1: 旅行に 行く ために.', en: 'Paragraph 1: in order to go on a trip.' },
        },
        {
          type: 'mc',
          q: '毎月《まいつき》 いくら 貯金《ちょきん》して いますか。',
          choices: ['三万円《さんまんえん》', '三千円《さんぜんえん》', '三十万円《さんじゅうまんえん》', '百円《ひゃくえん》'],
          answer: 0,
          explain: { id: 'Paragraf 1: 三万円も (も = banyak/sampai).', en: 'Paragraph 1: 三万円も (も = as much as).' },
        },
        {
          type: 'tf',
          q: '貯金箱《ちょきんばこ》は 友達《ともだち》に よって 作《つく》られました。',
          answer: true,
          explain: { id: 'Paragraf 2: 友達に よって 作られました.', en: 'Paragraph 2: made by a friend.' },
        },
        {
          type: 'mc',
          q: '貯金箱《ちょきんばこ》は 何《なに》に 便利《べんり》ですか。',
          choices: ['お金《かね》を 入《い》れるの', '旅行《りょこう》に 行《い》くの', 'ケーキを 作《つく》るの', '地図《ちず》を 書《か》くの'],
          answer: 0,
          explain: { id: 'Paragraf 3: お金を 入れるのに 便利です (〜のに = untuk).', en: 'Paragraph 3: handy for putting money in (〜のに = for).' },
        },
        {
          type: 'mc',
          q: '友達《ともだち》は 何《なに》を 作《つく》って いましたか。',
          choices: ['ケーキ', '貯金箱《ちょきんばこ》', '地図《ちず》', '料理《りょうり》'],
          answer: 0,
          explain: { id: 'Paragraf 4: おいしそうな ケーキ.', en: 'Paragraph 4: a delicious-looking cake.' },
        },
        {
          type: 'mc',
          q: 'わたしは 友達《ともだち》に 何《なん》と 頼《たの》みましたか。',
          choices: [
            '地図《ちず》を 書《か》いて くれませんか',
            '旅行《りょこう》に 行《い》きませんか',
            'ケーキを 作《つく》りませんか',
            '貯金箱《ちょきんばこ》を 買《か》ってくれませんか',
          ],
          answer: 0,
          explain: { id: 'Paragraf 5: 地図を 書いて くれませんか.', en: 'Paragraph 5: could you draw a map?' },
        },
        {
          type: 'tf',
          q: '友達《ともだち》は 地図《ちず》を 書《か》いて くれませんでした。',
          answer: false,
          explain: { id: 'Paragraf 5: すぐ 書いて くれました.', en: 'Paragraph 5: he drew it right away.' },
        },
      ],
      challenge: {
        id: 'Tulis untuk apa kamu menabung dan siapa yang membantumu. Pakai 〜ために、〜のに、〜て くれませんか.',
        en: 'Write what you are saving for and who helps you. Use 〜ために, 〜のに, 〜て くれませんか.',
      },
    },
    10: {
      title: { id: 'Di salon rambut', en: 'At the hair salon' },
      focus: {
        id: '〜すぎます、〜やすい・〜にくい、〜くします・〜にします、〜場合は、〜のに',
        en: '〜すぎます, 〜やすい・〜にくい, 〜くします・〜にします, 〜場合は, 〜のに',
      },
      before: {
        id: 'Kapan terakhir kamu memotong rambut? Gaya rambut seperti apa yang kamu suka?',
        en: 'When did you last get a haircut? What kind of hairstyle do you like?',
      },
      passage: {
        title: '美容院《びよういん》で',
        paragraphs: [
          {
            ja: '先週《せんしゅう》、美容院《びよういん》へ 行《い》きました。髪《かみ》が 長《なが》すぎて、洗《あら》いにくかったからです。',
            id: 'Minggu lalu saya pergi ke salon. Rambut saya terlalu panjang sehingga sulit dicuci.',
            en: 'Last week I went to a hair salon, because my hair was too long and hard to wash.',
          },
          {
            ja: '店《みせ》の 人《ひと》に 「髪《かみ》を 短《みじか》く して ください。でも、切《き》りすぎないで ください」と 頼《たの》みました。',
            id: 'Saya meminta pegawai salon, “Tolong pendekkan rambut saya. Tapi jangan dipotong terlalu pendek.”',
            en: 'I asked the staff, “Please make my hair shorter. But please don’t cut too much.”',
          },
          {
            ja: '色《いろ》は 今《いま》と 同《おな》じ 黒《くろ》に しました。短《みじか》い 髪《かみ》は 乾《かわ》きやすくて 楽《らく》です。',
            id: 'Untuk warna, saya memilih hitam seperti sekarang. Rambut pendek mudah kering sehingga praktis.',
            en: 'For the color I chose black, the same as now. Short hair dries easily, so it is easy.',
          },
          {
            ja: '「家《いえ》で 髪《かみ》が 乾《かわ》かない 場合《ばあい》は、ドライヤーを 使《つか》って ください」と 言《い》われました。',
            id: 'Saya diberi tahu, “Kalau rambut tidak kering di rumah, silakan pakai pengering rambut.”',
            en: 'I was told, “If your hair doesn’t dry at home, please use a hair dryer.”',
          },
          {
            ja: '次《つぎ》は 予約《よやく》が 取《と》りにくいので、早《はや》めに 電話《でんわ》して ください、とも 言《い》われました。',
            id: 'Saya juga diberi tahu bahwa reservasi berikutnya sulit didapat, jadi sebaiknya menelepon lebih awal.',
            en: 'I was also told that the next reservation is hard to get, so I should call early.',
          },
          {
            ja: '友達《ともだち》に 会《あ》ったら、「髪《かみ》、切《き》った？」と 言《い》われました。短《みじか》く したのに 気《き》が つかない 人《ひと》も いるので、少《すこ》し がっかりしました。',
            id: 'Ketika bertemu teman, ia bertanya, “Kamu potong rambut?” Padahal sudah dipendekkan, ada juga orang yang tidak sadar, jadi saya agak kecewa.',
            en: 'When I met my friend he said, “Did you cut your hair?” I was a little disappointed, since some people do not notice even though it is shorter.',
          },
        ],
      },
      vocab: [
        { ja: '美容院', reading: 'びよういん', id: 'salon rambut', en: 'hair salon' },
        { ja: '髪', reading: 'かみ', id: 'rambut', en: 'hair' },
        { ja: '乾く', reading: 'かわく', id: 'kering', en: 'to dry' },
        { ja: '予約', reading: 'よやく', id: 'reservasi', en: 'reservation' },
        { ja: '場合', reading: 'ばあい', id: 'kasus, jika', en: 'case, if' },
        { ja: '気がつく', reading: 'きがつく', id: 'menyadari', en: 'to notice' },
        { ja: '短い', reading: 'みじかい', id: 'pendek', en: 'short' },
      ],
      questions: [
        {
          type: 'mc',
          q: 'わたしは どうして 美容院《びよういん》へ 行《い》きましたか。',
          choices: [
            '髪《かみ》が 長《なが》すぎて 洗《あら》いにくかったから',
            '髪《かみ》の 色《いろ》を 変《か》えたかったから',
            '友達《ともだち》に 会《あ》うから',
            '髪《かみ》が 短《みじか》すぎたから',
          ],
          answer: 0,
          explain: { id: 'Paragraf 1: 長すぎて、洗いにくかった.', en: 'Paragraph 1: too long, hard to wash.' },
        },
        {
          type: 'mc',
          q: 'わたしは 店《みせ》の 人《ひと》に 何《なん》と 頼《たの》みましたか。',
          choices: [
            '短《みじか》く して ください。切《き》りすぎないで ください',
            '長《なが》く して ください',
            '色《いろ》を 変《か》えて ください',
            'もっと 切《き》って ください',
          ],
          answer: 0,
          explain: { id: 'Paragraf 2: 短くして、でも 切りすぎないで.', en: 'Paragraph 2: make it shorter, but don’t cut too much.' },
        },
        {
          type: 'mc',
          q: '髪《かみ》の 色《いろ》は どう しましたか。',
          choices: ['今《いま》と 同《おな》じ 黒《くろ》に した', '茶色《ちゃいろ》に した', '金色《きんいろ》に した', '白《しろ》に した'],
          answer: 0,
          explain: { id: 'Paragraf 3: 同じ 黒に しました (Nに します).', en: 'Paragraph 3: chose black, same as now (Nに します).' },
        },
        {
          type: 'tf',
          q: '短《みじか》い 髪《かみ》は 乾《かわ》きにくいです。',
          answer: false,
          explain: { id: 'Paragraf 3: 乾きやすくて 楽です.', en: 'Paragraph 3: dries easily and is easy to manage.' },
        },
        {
          type: 'mc',
          q: '髪《かみ》が 乾《かわ》かない 場合《ばあい》は、どう しますか。',
          choices: ['ドライヤーを 使《つか》う', '電話《でんわ》を する', '店《みせ》に 来《く》る', '帽子《ぼうし》を かぶる'],
          answer: 0,
          explain: { id: 'Paragraf 4: ドライヤーを 使って ください.', en: 'Paragraph 4: use a hair dryer.' },
        },
        {
          type: 'tf',
          q: '次《つぎ》の 予約《よやく》は 取《と》りやすいです。',
          answer: false,
          explain: { id: 'Paragraf 5: 予約が 取りにくい.', en: 'Paragraph 5: reservations are hard to get.' },
        },
        {
          type: 'mc',
          q: 'わたしは どうして がっかりしましたか。',
          choices: [
            '短《みじか》く したのに 気《き》が つかない 人《ひと》が いたから',
            '髪《かみ》が 長《なが》すぎたから',
            '友達《ともだち》に 会《あ》えなかったから',
            '店《みせ》が 閉《し》まって いたから',
          ],
          answer: 0,
          explain: { id: 'Paragraf 6: 短くしたのに 気がつかない (〜のに = padahal).', en: 'Paragraph 6: some do not notice even though it is shorter (〜のに = although).' },
        },
      ],
      challenge: {
        id: 'Tulis tentang pengalaman di salon atau toko pakaian. Pakai 〜すぎます、〜やすい／〜にくい、〜に します.',
        en: 'Write about an experience at a salon or clothing store. Use 〜すぎます, 〜やすい／〜にくい, 〜に します.',
      },
    },
    11: {
      title: { id: 'Di kedai ramen', en: 'At the ramen shop' },
      focus: {
        id: 'Ｖる／Ｖて いる／Ｖた ところです、Ｖた ばかりです、〜はずです、ふつうけい そうです（kabar）、〜に よると、〜ようです、〜そうです（tampak）、〜が します',
        en: 'Ｖる／Ｖて いる／Ｖた ところです, Ｖた ばかりです, 〜はずです, plain form そうです (hearsay), 〜に よると, 〜ようです, 〜そうです (looks), 〜が します',
      },
      before: {
        id: 'Pernahkah kamu antre lama di restoran terkenal? Apakah rasanya sepadan?',
        en: 'Have you ever queued for a long time at a famous restaurant? Was it worth it?',
      },
      passage: {
        title: 'ラーメン屋《や》で',
        paragraphs: [
          {
            ja: '日曜日《にちようび》の 昼《ひる》、友達《ともだち》の 田中《たなか》さんから 電話《でんわ》が ありました。「今《いま》、駅《えき》の 前《まえ》の ラーメン屋《や》へ 行《い》く ところです。いっしょに 行《い》きませんか」。わたしは 昼《ひる》ごはんを 食《た》べた ばかりでしたが、おもしろそうなので、行《い》くと 答《こた》えました。',
            id: 'Pada siang hari Minggu, teman saya, Tanaka, menelepon. “Sekarang saya hendak pergi ke kedai ramen di depan stasiun. Mau ikut?” Saya baru saja makan siang, tetapi karena kelihatannya menarik, saya menjawab akan ikut.',
            en: 'On Sunday at noon my friend Tanaka called. “I’m just about to go to the ramen shop in front of the station. Want to come?” I had just eaten lunch, but it sounded fun, so I said I would go.',
          },
          {
            ja: '田中《たなか》さんは 「今日《きょう》は 日曜日《にちようび》ですから、込《こ》んで いる はずです。早《はや》く 行《い》きましょう」と 言《い》いました。',
            id: 'Tanaka berkata, “Hari ini hari Minggu, jadi seharusnya ramai. Ayo cepat pergi.”',
            en: 'Tanaka said, “It’s Sunday, so it should be crowded. Let’s go early.”',
          },
          {
            ja: '駅《えき》に 着《つ》くと、おいしそうな においが しました。店《みせ》の 前《まえ》には 十人《じゅうにん》ぐらい 並《なら》んで いました。',
            id: 'Ketika tiba di stasiun, tercium aroma yang kelihatannya lezat. Di depan toko ada sekitar sepuluh orang mengantre.',
            en: 'When we reached the station, there was a delicious smell. About ten people were lined up in front of the shop.',
          },
          {
            ja: '田中《たなか》さんの 話《はなし》に よると、この 店《みせ》は 先月《せんげつ》 できた ばかりなのに、とても 人気《にんき》が あるそうです。',
            id: 'Menurut cerita Tanaka, kedai ini baru dibuka bulan lalu, tetapi katanya sangat populer.',
            en: 'According to Tanaka, this shop only opened last month, yet it is said to be very popular.',
          },
          {
            ja: '店《みせ》の 人《ひと》に 聞《き》くと、「今《いま》、スープを 作《つく》って いる ところなので、少《すこ》し 待《ま》って ください」と 言《い》われました。',
            id: 'Ketika saya bertanya pada pegawai, saya diberi tahu, “Sekarang kami sedang membuat kuah, jadi mohon tunggu sebentar.”',
            en: 'When I asked the staff, I was told, “We are in the middle of making the soup, so please wait a little.”',
          },
          {
            ja: '三十分《さんじゅっぷん》ぐらい 待《ま》って、やっと 食《た》べる ことが できました。店《みせ》の 中《なか》は にぎやかな 声《こえ》が して、みんな 満足《まんぞく》して いる ようでした。今《いま》、ちょうど 店《みせ》を 出《で》た ところです。スープは とても おいしくて、また 来《き》たいです。',
            id: 'Setelah menunggu sekitar tiga puluh menit, akhirnya saya bisa makan. Di dalam toko terdengar suara riuh dan semua orang tampaknya puas. Sekarang saya baru saja keluar dari toko. Kuahnya sangat enak, saya ingin datang lagi.',
            en: 'After waiting about thirty minutes, we were finally able to eat. The shop was filled with lively voices and everyone seemed satisfied. I have just left the shop now. The soup was delicious and I want to come again.',
          },
        ],
      },
      vocab: [
        { ja: '並ぶ', reading: 'ならぶ', id: 'mengantre, berjajar', en: 'to line up' },
        { ja: '込む', reading: 'こむ', id: 'ramai, padat', en: 'to be crowded' },
        { ja: '人気', reading: 'にんき', id: 'populer', en: 'popularity' },
        { ja: '満足する', reading: 'まんぞくする', id: 'puas', en: 'to be satisfied' },
        { ja: 'におい', reading: 'におい', id: 'bau, aroma', en: 'smell' },
        { ja: '声', reading: 'こえ', id: 'suara (orang)', en: 'voice' },
        { ja: 'やっと', reading: 'やっと', id: 'akhirnya', en: 'at last' },
      ],
      questions: [
        {
          type: 'mc',
          q: '電話《でんわ》を もらった とき、わたしは 何《なに》を した ばかりでしたか。',
          choices: ['昼《ひる》ごはんを 食《た》べた', 'ラーメンを 食《た》べた', 'うちを 出《で》た', '田中《たなか》さんに 会《あ》った'],
          answer: 0,
          explain: { id: 'Paragraf 1: 昼ごはんを 食べた ばかりでした.', en: 'Paragraph 1: I had just eaten lunch.' },
        },
        {
          type: 'mc',
          q: '田中《たなか》さんは どうして 込《こ》んで いる はずだと 言《い》いましたか。',
          choices: ['日曜日《にちようび》だから', '店《みせ》が 小《ちい》さいから', '雨《あめ》が 降《ふ》って いるから', '田中《たなか》さんが 遅《おそ》いから'],
          answer: 0,
          explain: { id: 'Paragraf 2: 今日は 日曜日ですから、込んで いる はずです.', en: 'Paragraph 2: it is Sunday, so it should be crowded.' },
        },
        {
          type: 'mc',
          q: '店《みせ》の 前《まえ》で、何《なに》が しましたか。',
          choices: ['おいしそうな においが しました', '大《おお》きい 音《おと》が しました', '犬《いぬ》の 声《こえ》が しました', '雨《あめ》の 音《おと》が しました'],
          answer: 0,
          explain: { id: 'Paragraf 3: おいしそうな においが しました.', en: 'Paragraph 3: there was a delicious-smelling aroma.' },
        },
        {
          type: 'tf',
          q: 'この 店《みせ》は ずっと 前《まえ》から あります。',
          answer: false,
          explain: { id: 'Paragraf 4: 先月 できた ばかりです.', en: 'Paragraph 4: it only opened last month.' },
        },
        {
          type: 'mc',
          q: '店《みせ》の 人《ひと》は どうして 待《ま》つように 言《い》いましたか。',
          choices: ['スープを 作《つく》って いる ところだから', '席《せき》が 一《ひと》つも ないから', '店《みせ》が 閉《し》まる ところだから', '今日《きょう》は 休《やす》みだから'],
          answer: 0,
          explain: { id: 'Paragraf 5: スープを 作って いる ところなので.', en: 'Paragraph 5: they were in the middle of making the soup.' },
        },
        {
          type: 'tf',
          q: '店《みせ》の 中《なか》の 客《きゃく》は 満足《まんぞく》して いない ようでした。',
          answer: false,
          explain: { id: 'Paragraf 6: みんな 満足して いる ようでした.', en: 'Paragraph 6: everyone seemed satisfied.' },
        },
        {
          type: 'mc',
          q: '田中《たなか》さんの 話《はなし》に よると、この 店《みせ》は どんな 店《みせ》ですか。',
          choices: ['先月《せんげつ》 できた ばかりなのに、人気《にんき》が ある', '先月《せんげつ》 閉《し》まって しまった', '古《ふる》いのに 安《やす》い', '有名《ゆうめい》だが おいしくない'],
          answer: 0,
          explain: { id: 'Paragraf 4: できた ばかりなのに、とても 人気が あるそうです.', en: 'Paragraph 4: newly opened yet very popular.' },
        },
      ],
      challenge: {
        id: 'Tulis pengalamanmu di sebuah restoran. Pakai 〜ところ、〜ばかり、〜そうです、〜ようです、〜が します.',
        en: 'Write about an experience at a restaurant. Use 〜ところ, 〜ばかり, 〜そうです, 〜ようです, 〜が します.',
      },
    },
    12: {
      title: { id: 'Kerja paruh waktu di hotel', en: 'A part-time job at a hotel' },
      focus: {
        id: 'Kausatif（〜（さ）せます）、Ｎ を ＋ kausatif、Ｎ１ に Ｎ２ を ＋ kausatif、〜（さ）せて いただけませんか、尊敬語（いらっしゃる・おっしゃる・お〜に なる・お〜ください）、謙譲語（お〜します・ご〜します・申します）、〜まして／〜ますので',
        en: 'Causative (〜（さ）せます), Ｎ を + causative, Ｎ１ に Ｎ２ を + causative, 〜（さ）せて いただけませんか, honorific (いらっしゃる・おっしゃる・お〜に なる・お〜ください), humble (お〜します・ご〜します・申します), 〜まして／〜ますので',
      },
      before: {
        id: 'Pernahkah kamu bekerja melayani pelanggan? Bagaimana cara berbicara yang sopan kepada tamu?',
        en: 'Have you ever worked serving customers? How do you speak politely to guests?',
      },
      passage: {
        title: 'ホテルの アルバイト',
        paragraphs: [
          {
            ja: 'わたしは 夏休《なつやす》みに、ホテルの フロントで アルバイトを して います。初《はじ》めての 日《ひ》、先輩《せんぱい》の 木村《きむら》さんに 「お客様《きゃくさま》が いらっしゃったら、まず 笑顔《えがお》で 『いらっしゃいませ』と 言《い》って ください」と 言《い》われました。',
            id: 'Selama liburan musim panas saya bekerja paruh waktu di meja resepsionis hotel. Pada hari pertama, senior saya, Kimura, berkata, “Kalau tamu datang, pertama-tama ucapkan ‘Irasshaimase’ dengan senyum.”',
            en: 'During summer vacation I work part-time at a hotel front desk. On my first day my senior, Kimura, told me, “When a guest arrives, first say ‘Irasshaimase’ with a smile.”',
          },
          {
            ja: '木村《きむら》さんは お客様《きゃくさま》に 「こちらに お名前《なまえ》を お書《か》き ください」と 言《い》いました。「ただいま お部屋《へや》を 準備《じゅんび》して おりますので、少《すこ》し お待《ま》ち ください」とも 言《い》いました。',
            id: 'Kimura berkata kepada tamu, “Silakan tuliskan nama Anda di sini.” Ia juga berkata, “Kamar sedang kami siapkan, jadi mohon tunggu sebentar.”',
            en: 'Kimura said to the guest, “Please write your name here.” He also said, “We are preparing your room now, so please wait a moment.”',
          },
          {
            ja: 'わたしは お客様《きゃくさま》の 荷物《にもつ》を お持《も》ちして、部屋《へや》まで ご案内《あんない》しました。電話《でんわ》では 「はい、ホテル さくらで ございます」と 答《こた》えます。自分《じぶん》の 名前《なまえ》を 言《い》う ときは、「田中《たなか》と 申《もう》します」と 言《い》います。',
            id: 'Saya membawakan barang tamu dan mengantar mereka sampai ke kamar. Saat menjawab telepon, saya berkata, “Ya, Hotel Sakura.” Saat menyebut nama sendiri, saya berkata, “Saya Tanaka.”',
            en: 'I carried the guest’s luggage and showed them to the room. On the phone I answer, “Yes, this is Hotel Sakura.” When I give my own name, I say, “I am Tanaka.”',
          },
          {
            ja: '昨日《きのう》、山本《やまもと》様《さま》と いう お客様《きゃくさま》が いらっしゃいました。「あした 何時《なんじ》に お出《で》かけに なりますか」と 聞《き》くと、「九時《くじ》ごろです」と おっしゃいました。',
            id: 'Kemarin datang seorang tamu bernama Tuan Yamamoto. Ketika saya bertanya, “Besok pukul berapa Anda akan berangkat?”, beliau menjawab, “Sekitar pukul sembilan.”',
            en: 'Yesterday a guest named Mr. Yamamoto arrived. When I asked, “What time will you be leaving tomorrow?”, he said, “Around nine o’clock.”',
          },
          {
            ja: '店長《てんちょう》は 厳《きび》しいですが、いい 人《ひと》です。新《あたら》しい アルバイトに 毎日《まいにち》 ロビーの 掃除《そうじ》を させます。朝《あさ》は 早《はや》く 来《こ》させるので、大変《たいへん》です。',
            id: 'Manajer toko tegas tetapi orang baik. Ia menyuruh pekerja paruh waktu baru membersihkan lobi setiap hari. Karena menyuruh datang pagi-pagi, itu cukup berat.',
            en: 'The manager is strict but a good person. He makes new part-timers clean the lobby every day. Since he makes us come early in the morning, it is tough.',
          },
          {
            ja: '先週《せんしゅう》、わたしは 熱《ねつ》が あって、「熱《ねつ》が ありまして…。今日《きょう》は 早《はや》く 帰《かえ》らせて いただけませんか」と 店長《てんちょう》に 頼《たの》みました。店長《てんちょう》は 「お大事《だいじ》に」と 言《い》って、帰《かえ》らせて くれました。',
            id: 'Minggu lalu saya demam, lalu meminta kepada manajer, “Saya demam… Bolehkah saya pulang lebih awal hari ini?” Manajer berkata, “Semoga cepat sembuh,” dan mengizinkan saya pulang.',
            en: 'Last week I had a fever and asked the manager, “I have a fever… May I go home early today?” He said, “Take care,” and let me go home.',
          },
        ],
      },
      vocab: [
        { ja: '笑顔', reading: 'えがお', id: 'wajah tersenyum', en: 'smile' },
        { ja: '案内する', reading: 'あんないする', id: 'mengantar, memandu', en: 'to guide' },
        { ja: '申す', reading: 'もうす', id: 'berkata (rendah hati)', en: 'to say (humble)' },
        { ja: 'おっしゃる', reading: 'おっしゃる', id: 'berkata (hormat)', en: 'to say (honorific)' },
        { ja: '店長', reading: 'てんちょう', id: 'manajer toko', en: 'store manager' },
        { ja: '頼む', reading: 'たのむ', id: 'meminta', en: 'to ask, to request' },
        { ja: '荷物', reading: 'にもつ', id: 'barang bawaan', en: 'luggage' },
      ],
      questions: [
        {
          type: 'mc',
          q: '先輩《せんぱい》は まず 何《なに》を するように 言《い》いましたか。',
          choices: ['笑顔《えがお》で 「いらっしゃいませ」と 言《い》う', '荷物《にもつ》を 持《も》つ', '電話《でんわ》に 出《で》る', '掃除《そうじ》を する'],
          answer: 0,
          explain: { id: 'Paragraf 1: まず 笑顔で 「いらっしゃいませ」と 言う.', en: 'Paragraph 1: first say “Irasshaimase” with a smile.' },
        },
        {
          type: 'mc',
          q: '「お名前《なまえ》を お書《か》き ください」は だれが だれに 言《い》いましたか。',
          choices: ['ホテルの 人《ひと》が お客様《きゃくさま》に', 'お客様《きゃくさま》が ホテルの 人《ひと》に', 'わたしが 先輩《せんぱい》に', '店長《てんちょう》が わたしに'],
          answer: 0,
          explain: { id: 'お〜ください adalah bentuk hormat yang dipakai staf kepada tamu.', en: 'お〜ください is the honorific request staff use toward guests.' },
        },
        {
          type: 'mc',
          q: '電話《でんわ》で 自分《じぶん》の 名前《なまえ》を 言《い》う とき、どう 言《い》いますか。',
          choices: ['田中《たなか》と 申《もう》します', '田中《たなか》で いらっしゃいます', '田中《たなか》に なります', '田中《たなか》と おっしゃいます'],
          answer: 0,
          explain: { id: 'Paragraf 3: 申します adalah bentuk merendah untuk diri sendiri.', en: 'Paragraph 3: 申します is the humble form for oneself.' },
        },
        {
          type: 'tf',
          q: '山本《やまもと》様《さま》は あした 九時《くじ》ごろ 出《で》かけると おっしゃいました。',
          answer: true,
          explain: { id: 'Paragraf 4: 九時ごろです と おっしゃいました.', en: 'Paragraph 4: he said around nine o’clock.' },
        },
        {
          type: 'mc',
          q: '店長《てんちょう》は アルバイトに 毎日《まいにち》 何《なに》を させますか。',
          choices: ['ロビーの 掃除《そうじ》', '料理《りょうり》', '運転《うんてん》', '洗濯《せんたく》'],
          answer: 0,
          explain: { id: 'Paragraf 4: 毎日 ロビーの 掃除を させます.', en: 'Paragraph 4: he has them clean the lobby every day.' },
        },
        {
          type: 'tf',
          q: '先週《せんしゅう》、店長《てんちょう》は わたしを 早《はや》く 帰《かえ》らせて くれませんでした。',
          answer: false,
          explain: { id: 'Paragraf 5: 帰らせて くれました.', en: 'Paragraph 5: he let me go home.' },
        },
        {
          type: 'mc',
          q: '「帰《かえ》らせて いただけませんか」は どんな 意味《いみ》ですか。',
          choices: ['帰《かえ》る 許可《きょか》を 丁寧《ていねい》に 頼《たの》む', '帰《かえ》るように 命令《めいれい》する', '帰《かえ》る 人《ひと》に お礼《れい》を 言《い》う', '帰《かえ》る 時間《じかん》を 教《おし》える'],
          answer: 0,
          explain: { id: '〜（さ）せて いただけませんか = minta izin dengan sopan.', en: '〜（さ）せて いただけませんか = politely asking for permission.' },
        },
      ],
      challenge: {
        id: 'Tulis percakapan di tempat kerja memakai bahasa hormat dan 〜（さ）せて いただけませんか.',
        en: 'Write a workplace dialogue using polite language and 〜（さ）せて いただけませんか.',
      },
    },
  },

  // ---------------------------------------------------------------- N3
  N3: {
    1: {
      title: { id: 'Semalam di kamar bertatami', en: 'A night in a tatami room' },
      focus: {
        id: '〜ようだ／〜ように、〜という、いつ・どんなに〜ても、〜ことが できる、kosakata rumah gaya Jepang',
        en: '〜ようだ／〜ように, 〜という, いつ・どんなに〜ても, 〜ことが できる, Japanese-style house vocabulary',
      },
      before: {
        id: 'Pernahkah kamu masuk ke kamar bertatami (和室)? Bagaimana kesan pertamamu?',
        en: 'Have you ever been in a tatami room (和室)? What was your first impression?',
      },
      passage: {
        title: '和室《わしつ》の 一晩《ひとばん》',
        paragraphs: [
          {
            ja: '先月《せんげつ》、日本人《にほんじん》の 友達《ともだち》の 家《いえ》に 泊《と》めて もらいました。案内《あんない》された 部屋《へや》は、床《ゆか》に 畳《たたみ》が 敷《し》いてある 和室《わしつ》で、ベッドも いすも ありませんでした。',
            id: 'Bulan lalu saya menginap di rumah teman Jepang. Kamar yang ditunjukkan kepada saya adalah kamar bergaya Jepang dengan tatami terbentang di lantai; tidak ada tempat tidur maupun kursi.',
            en: 'Last month I stayed at a Japanese friend’s house. The room I was shown to was a Japanese-style room with tatami laid on the floor; there was neither a bed nor a chair.',
          },
          {
            ja: '靴下《くつした》を 脱《ぬ》いで、素足《すあし》で 畳《たたみ》の 上《うえ》を 歩《ある》くと、少《すこ》し 柔《やわ》らかくて、草《くさ》のような いい 匂《にお》いが しました。まるで 森《もり》の 中《なか》に いるようでした。',
            id: 'Saat saya melepas kaus kaki dan berjalan tanpa alas kaki di atas tatami, rasanya agak empuk dan tercium aroma harum seperti rumput. Rasanya persis seperti berada di dalam hutan.',
            en: 'When I took off my socks and walked barefoot on the tatami, it felt a little soft and smelled pleasantly like grass. It was just like being in a forest.',
          },
          {
            ja: '友達《ともだち》の お母《かあ》さんは 「この 部屋《へや》は、昼《ひる》は 居間《いま》、夜《よる》は 寝室《しんしつ》として 使《つか》い分《わ》けているんですよ」と 言《い》いました。夜《よる》に なると、押《お》し入《い》れから 布団《ふとん》を 出《だ》して 敷《し》きます。朝《あさ》は 布団《ふとん》を たたんで しまうので、部屋《へや》は また 広《ひろ》く なります。',
            id: 'Ibu temanku berkata, “Kamar ini kami gunakan secara berbeda: siang sebagai ruang keluarga, malam sebagai kamar tidur.” Saat malam tiba, kasur futon dikeluarkan dari lemari dan dibentangkan. Pagi harinya futon dilipat dan disimpan, sehingga kamar kembali luas.',
            en: 'My friend’s mother said, “We use this room for different purposes: as a living room by day and a bedroom at night.” When night comes, the futon is taken out of the closet and laid out. In the morning it is folded and put away, so the room becomes spacious again.',
          },
          {
            ja: '日本《にほん》では、畳《たたみ》の 部屋《へや》を 和室《わしつ》と 言《い》います。畳《たたみ》は 湿気《しっけ》を 吸《す》ったり 出《だ》したり するので、どんなに 暑《あつ》い 日《ひ》でも 比較的《ひかくてき》 快適《かいてき》だそうです。',
            id: 'Di Jepang, kamar bertatami disebut washitsu (和室). Konon karena tatami menyerap dan melepaskan kelembapan, tempat ini relatif nyaman seberapa pun panasnya hari.',
            en: 'In Japan, a room with tatami is called a 和室. Because tatami absorbs and releases moisture, I’m told it is relatively comfortable no matter how hot the day is.',
          },
          {
            ja: '最初《さいしょ》は 床《ゆか》に 寝《ね》るのが 不安《ふあん》でしたが、朝《あさ》まで ぐっすり 眠《ねむ》ることが できました。いつか 自分《じぶん》の 家《いえ》にも 和室《わしつ》を 作《つく》りたいと 思《おも》っています。',
            id: 'Awalnya saya cemas tidur di lantai, tetapi saya bisa tidur nyenyak sampai pagi. Suatu hari nanti saya ingin membuat kamar bergaya Jepang di rumah sendiri.',
            en: 'At first I was uneasy about sleeping on the floor, but I was able to sleep soundly until morning. Someday I would like to build a Japanese-style room in my own house.',
          },
        ],
      },
      vocab: [
        { ja: '畳', reading: 'たたみ', id: 'tatami (tikar lantai)', en: 'tatami mat' },
        { ja: '和室', reading: 'わしつ', id: 'kamar bergaya Jepang', en: 'Japanese-style room' },
        { ja: '床', reading: 'ゆか', id: 'lantai', en: 'floor' },
        { ja: '素足', reading: 'すあし', id: 'tanpa alas kaki', en: 'bare feet' },
        { ja: '敷く', reading: 'しく', id: 'membentangkan (tikar, futon)', en: 'to lay out' },
        { ja: '使い分ける', reading: 'つかいわける', id: 'menggunakan sesuai keperluan', en: 'to use selectively' },
        { ja: '居間', reading: 'いま', id: 'ruang keluarga', en: 'living room' },
        { ja: '湿気', reading: 'しっけ', id: 'kelembapan', en: 'moisture, humidity' },
        { ja: '快適', reading: 'かいてき', id: 'nyaman', en: 'comfortable' },
        { ja: '比較的', reading: 'ひかくてき', id: 'relatif', en: 'relatively' },
      ],
      questions: [
        {
          type: 'mc',
          q: '友達《ともだち》の 家《いえ》の 部屋《へや》の 床《ゆか》には 何《なに》が 敷《し》いてありましたか。',
          choices: ['カーペット', '畳《たたみ》', '板《いた》', '布団《ふとん》'],
          answer: 1,
          explain: { id: 'Paragraf 1: 床に 畳が 敷いてある 和室.', en: 'Paragraph 1: 床に 畳が 敷いてある 和室。' },
        },
        {
          type: 'mc',
          q: '「草《くさ》のような いい 匂《にお》い」は 何《なに》の 匂《にお》いですか。',
          choices: ['布団《ふとん》', '森《もり》', '畳《たたみ》', '友達《ともだち》の お母《かあ》さんの 料理《りょうり》'],
          answer: 2,
          explain: { id: 'Paragraf 2: 畳の 上を 歩くと… 草のような 匂いが しました.', en: 'Paragraph 2: walking on the tatami gave off a grass-like smell.' },
        },
        {
          type: 'mc',
          q: '部屋《へや》を 「使《つか》い分《わ》けている」の 例《れい》は どれですか。',
          choices: [
            '昼《ひる》は 居間《いま》、夜《よる》は 寝室《しんしつ》に する',
            '夏《なつ》と 冬《ふゆ》で 部屋《へや》を かえる',
            '友達《ともだち》と 自分《じぶん》の 部屋《へや》を 分《わ》ける',
            '部屋《へや》を 広《ひろ》く する',
          ],
          answer: 0,
          explain: { id: 'Paragraf 3: 昼は 居間、夜は 寝室として 使い分けている.', en: 'Paragraph 3: used as a living room by day and a bedroom at night.' },
        },
        {
          type: 'tf',
          q: '「わたし」は ベッドで 寝《ね》ました。',
          answer: false,
          explain: { id: 'Tidak ada tempat tidur. Ia tidur di futon yang dibentangkan di atas tatami.', en: 'There was no bed. The writer slept on a futon laid on the tatami.' },
        },
        {
          type: 'tf',
          q: '畳《たたみ》の 部屋《へや》は、暑《あつ》い 日《ひ》でも 比較的《ひかくてき》 快適《かいてき》だそうです。',
          answer: true,
          explain: { id: 'Paragraf 4: どんなに 暑い 日でも 比較的 快適だそうです.', en: 'Paragraph 4: relatively comfortable even on hot days.' },
        },
        {
          type: 'mc',
          q: '「わたし」は 最初《さいしょ》、床《ゆか》に 寝《ね》ることを どう 思《おも》いましたか。',
          choices: ['うれしかった', '不安《ふあん》だった', 'つまらなかった', '怒《おこ》った'],
          answer: 1,
          explain: { id: 'Paragraf 5: 最初は… 不安でしたが…', en: 'Paragraph 5: 最初は… 不安でしたが…' },
        },
        {
          type: 'mc',
          q: '日本《にほん》では、畳《たたみ》の 部屋《へや》を 何《なん》と 言《い》いますか。',
          choices: ['居間《いま》', '寝室《しんしつ》', '和室《わしつ》', '客間《きゃくま》'],
          answer: 2,
          explain: { id: 'Paragraf 4: 畳の 部屋を 和室と 言います.', en: 'Paragraph 4: 畳の 部屋を 和室と 言います。' },
        },
      ],
      challenge: {
        id: 'Ceritakan kamar di rumahmu: apa yang ada di lantai dan bagaimana kamu menggunakannya? Pakai 〜のようだ atau 〜という.',
        en: 'Describe a room in your home: what is on the floor and how do you use it? Use 〜のようだ or 〜という.',
      },
    },
    2: {
      title: { id: 'Kata katakana di kantor', en: 'Katakana words at the office' },
      focus: {
        id: '〜たら、〜た（hasil・penemuan）、〜というのは〜ことだ、〜という＋N、〜ように 言う・頼む、〜みたいだ、kosakata kata serapan',
        en: '〜たら、〜た (result・discovery), 〜というのは〜ことだ, 〜という＋N, 〜ように 言う・頼む, 〜みたいだ, loanword vocabulary',
      },
      before: {
        id: 'Kata serapan apa yang sering kamu dengar atau pakai dalam bahasa Jepang? Pernahkah kamu bingung dengan artinya?',
        en: 'Which loanwords do you often hear or use in Japanese? Have you ever been confused by their meaning?',
      },
      passage: {
        title: '会社《かいしゃ》の カタカナ語《ご》',
        paragraphs: [
          {
            ja: '入社《にゅうしゃ》して 初《はじ》めて 会議《かいぎ》に 出《で》たら、カタカナの 言葉《ことば》ばかりで、何《なに》が 何《なん》だか わからなかった。',
            id: 'Ketika pertama kali menghadiri rapat setelah masuk perusahaan, isinya hanya kata-kata katakana sehingga saya sama sekali tidak mengerti apa pun.',
            en: 'When I attended my first meeting after joining the company, it was nothing but katakana words, and I could not tell what was what.',
          },
          {
            ja: '会議《かいぎ》が 終《お》わってから 先輩《せんぱい》に 聞《き》いたら、「アポというのは 会《あ》う 約束《やくそく》の ことだよ。プレゼンというのは 自分《じぶん》の 考《かんが》えを みんなの 前《まえ》で 説明《せつめい》するという ことだ」と 教《おし》えて くれた。',
            id: 'Setelah rapat selesai saya bertanya kepada senior, dan ia menjelaskan, “‘Apo’ itu artinya janji untuk bertemu. ‘Purezen’ itu artinya menjelaskan gagasan sendiri di depan semua orang.”',
            en: 'After the meeting I asked a senior colleague, and he told me, “‘Apo’ means an appointment to meet someone. ‘Purezen’ means explaining your own ideas in front of everyone.”',
          },
          {
            ja: '先輩《せんぱい》たちの 会話《かいわ》は、まるで 外国語《がいこくご》みたいだった。「あとで アポを とって、プレゼンの 準備《じゅんび》を しよう」のように、日本語《にほんご》の 中《なか》に カタカナ語《ご》が たくさん 入《はい》って いる。日本語《にほんご》で 言《い》えるのに、なぜ 使《つか》うのだろうと 思《おも》った。',
            id: 'Percakapan para senior terdengar persis seperti bahasa asing. Seperti dalam “Nanti buat janji dulu, lalu siapkan presentasinya”, banyak kata katakana tercampur di dalam bahasa Jepang. Saya bertanya-tanya mengapa mereka memakainya padahal bisa dikatakan dalam bahasa Jepang.',
            en: 'My seniors’ conversations sounded just like a foreign language. As in “Let’s set up an appointment later and prepare the presentation”, a lot of katakana words are mixed into the Japanese. I wondered why they use them when they could say it in Japanese.',
          },
          {
            ja: '次《つぎ》の 日《ひ》、課長《かちょう》に 「お客様《きゃくさま》の 前《まえ》では、できるだけ わかりやすい 日本語《にほんご》で 説明《せつめい》するように」と 言《い》われた。日本語《にほんご》で 言《い》えるのに 外来語《がいらいご》を 使《つか》うのは 問題《もんだい》だという 意見《いけん》が、会社《かいしゃ》の 中《なか》に あるそうだ。',
            id: 'Keesokan harinya, kepala seksi berpesan kepada saya, “Di depan pelanggan, jelaskanlah dengan bahasa Jepang yang semudah mungkin.” Katanya di dalam perusahaan ada pendapat bahwa memakai kata serapan padahal bisa dikatakan dalam bahasa Jepang itu bermasalah.',
            en: 'The next day my section chief told me, “In front of customers, explain things in Japanese that is as easy to understand as possible.” I hear there is an opinion inside the company that using loanwords when you can say it in Japanese is a problem.',
          },
          {
            ja: '試《ため》しに 友人《ゆうじん》に 話《はな》したら、友人《ゆうじん》の 会社《かいしゃ》でも 同《おな》じだった。「アポ」を 「約束《やくそく》」に 変《か》えて 言《い》ったら、お客様《きゃくさま》は ほっとした 顔《かお》を した。もちろん、外来語《がいらいご》が 必要《ひつよう》な 場合《ばあい》も ある。でも、使《つか》いすぎないように 気《き》を つけたいと 思《おも》う。',
            id: 'Saya mencoba menceritakannya kepada seorang teman, dan ternyata di perusahaan teman saya pun sama. Ketika saya mengganti “apo” dengan “yakusoku” (janji), pelanggan tampak lega. Tentu saja ada kalanya kata serapan memang diperlukan. Namun saya ingin berhati-hati agar tidak memakainya berlebihan.',
            en: 'When I tried talking about it with a friend, it turned out to be the same at my friend’s company. When I said “yakusoku” (promise) instead of “apo”, the customer looked relieved. Of course, there are cases where loanwords are necessary. Still, I want to be careful not to overuse them.',
          },
        ],
      },
      vocab: [
        { ja: 'アポ', reading: 'アポ', id: 'janji temu', en: 'appointment' },
        { ja: 'プレゼン', reading: 'プレゼン', id: 'presentasi', en: 'presentation' },
        { ja: '外来語', reading: 'がいらいご', id: 'kata serapan', en: 'loanword' },
        { ja: '約束', reading: 'やくそく', id: 'janji', en: 'promise, appointment' },
        { ja: '説明', reading: 'せつめい', id: 'penjelasan', en: 'explanation' },
        { ja: '友人', reading: 'ゆうじん', id: 'teman', en: 'friend' },
        { ja: '意見', reading: 'いけん', id: 'pendapat', en: 'opinion' },
        { ja: '場合', reading: 'ばあい', id: 'kasus, keadaan', en: 'case, situation' },
        { ja: '必要', reading: 'ひつよう', id: 'perlu, kebutuhan', en: 'necessary, need' },
        { ja: 'バランス', reading: 'バランス', id: 'keseimbangan', en: 'balance' },
      ],
      questions: [
        {
          type: 'mc',
          q: '「わたし」は 初《はじ》めて 会議《かいぎ》に 出《で》たとき、どうでしたか。',
          choices: [
            '何《なに》が 何《なん》だか わからなかった',
            'とても おもしろかった',
            '眠《ねむ》かった',
            '自分《じぶん》の 意見《いけん》を たくさん 言《い》えた',
          ],
          answer: 0,
          explain: { id: 'Paragraf 1: カタカナの 言葉ばかりで、何が 何だか わからなかった.', en: 'Paragraph 1: it was all katakana words and the writer could not tell what was what.' },
        },
        {
          type: 'mc',
          q: '「アポ」というのは どういう 意味《いみ》ですか。',
          choices: ['会《あ》う 約束《やくそく》', '会議《かいぎ》の 資料《しりょう》', '会議室《かいぎしつ》', '説明《せつめい》する こと'],
          answer: 0,
          explain: { id: 'Paragraf 2: アポというのは 会う 約束の ことだ.', en: 'Paragraph 2: アポ is an appointment to meet someone.' },
        },
        {
          type: 'mc',
          q: '「プレゼン」というのは どんな ことですか。',
          choices: [
            '自分《じぶん》の 考《かんが》えを みんなの 前《まえ》で 説明《せつめい》する こと',
            'お客様《きゃくさま》に 会《あ》う 約束《やくそく》を する こと',
            '会議《かいぎ》に 出《で》る こと',
            '新《あたら》しい 資料《しりょう》を 作《つく》る こと',
          ],
          answer: 0,
          explain: { id: 'Paragraf 2: プレゼンというのは 自分の 考えを みんなの 前で 説明するという ことだ.', en: 'Paragraph 2: プレゼン means explaining your own ideas in front of everyone.' },
        },
        {
          type: 'tf',
          q: '先輩《せんぱい》たちの 会話《かいわ》は、外国語《がいこくご》みたいだと 思《おも》った。',
          answer: true,
          explain: { id: 'Paragraf 3: まるで 外国語みたいだった.', en: 'Paragraph 3: it was just like a foreign language.' },
        },
        {
          type: 'mc',
          q: '課長《かちょう》は 「わたし」に 何《なん》と 言《い》いましたか。',
          choices: [
            'お客様《きゃくさま》の 前《まえ》では わかりやすい 日本語《にほんご》で 説明《せつめい》するように 言《い》った',
            'カタカナ語《ご》を もっと 使《つか》うように 言《い》った',
            '会議《かいぎ》に 出《で》ないように 言《い》った',
            '友人《ゆうじん》に 話《はな》さないように 言《い》った',
          ],
          answer: 0,
          explain: { id: 'Paragraf 4: できるだけ わかりやすい 日本語で 説明するようにと 言われた.', en: 'Paragraph 4: told to explain in Japanese that is as easy to understand as possible.' },
        },
        {
          type: 'tf',
          q: '友人《ゆうじん》の 会社《かいしゃ》では カタカナ語《ご》を 使《つか》いません。',
          answer: false,
          explain: { id: 'Paragraf 5: 友人の 会社でも 同じだった (カタカナ語が 多い).', en: 'Paragraph 5: it was the same at the friend’s company (many katakana words).' },
        },
        {
          type: 'mc',
          q: '「わたし」の 考《かんが》えに 合《あ》う ものは どれですか。',
          choices: [
            '必要《ひつよう》な 場合《ばあい》は 使《つか》うが、使《つか》いすぎないように したい',
            '外来語《がいらいご》は 全部《ぜんぶ》 やめた ほうが いい',
            '外来語《がいらいご》は 日本語《にほんご》より わかりやすい',
            'プレゼンは しない ほうが いい',
          ],
          answer: 0,
          explain: { id: 'Paragraf 5: 外来語が 必要な 場合も ある。でも、使いすぎないように 気を つけたい.', en: 'Paragraph 5: loanwords are sometimes necessary, but the writer wants to avoid overusing them.' },
        },
      ],
      challenge: {
        id: 'Tulis pendapatmu tentang kata serapan. Jelaskan satu kata serapan dengan 〜というのは〜ことだ.',
        en: 'Write your opinion about loanwords. Explain one loanword using 〜というのは〜ことだ.',
      },
    },
    3: {
      title: { id: 'Pindah rumah dan kebiasaan baru', en: 'Moving house and new habits' },
      focus: {
        id: '〜ことにする・ことにしている、〜ことになる・ことになっている、〜てほしい、〜そうな／そうに、〜なさそう、〜そうもない',
        en: '〜ことにする・ことにしている, 〜ことになる・ことになっている, 〜てほしい, 〜そうな／そうに, 〜なさそう, 〜そうもない',
      },
      before: {
        id: 'Pernahkah kamu pindah ke kota lain? Apa kebiasaan baru yang kamu putuskan untuk dijalani?',
        en: 'Have you ever moved to another city? What new habits did you decide to adopt?',
      },
      passage: {
        title: '引《ひ》っ越《こ》しと 新《あたら》しい 習慣《しゅうかん》',
        paragraphs: [
          {
            ja: '会社《かいしゃ》の 研修《けんしゅう》を 受《う》ける ために、来月《らいげつ》から 三《さん》か月《げつ》 大阪《おおさか》へ 引《ひ》っ越《こ》す ことに なった。',
            id: 'Untuk mengikuti pelatihan perusahaan, mulai bulan depan saya akan pindah ke Osaka selama tiga bulan (begitu diputuskan).',
            en: 'To attend company training, it has been decided that I will move to Osaka for three months starting next month.',
          },
          {
            ja: '今《いま》までは 車《くるま》で 通勤《つうきん》して いたが、大阪《おおさか》では 電車《でんしゃ》で 通《かよ》う ことに した。友人《ゆうじん》が 毎朝《まいあさ》 電車《でんしゃ》で 本《ほん》を 読《よ》む ことに して いると 聞《き》いて、わたしも まねして みる ことに した。',
            id: 'Selama ini saya berangkat kerja dengan mobil, tetapi di Osaka saya memutuskan memakai kereta. Mendengar bahwa seorang teman membiasakan diri membaca buku di kereta setiap pagi, saya pun memutuskan menirunya.',
            en: 'Until now I commuted by car, but in Osaka I decided to go by train. Hearing that a friend makes a point of reading a book on the train every morning, I decided to copy them.',
          },
          {
            ja: '研修《けんしゅう》では、朝《あさ》 9時《じ》までに 会社《かいしゃ》に 着《つ》く ことに なって いる。遅《おく》れそうな 場合《ばあい》は、必《かなら》ず 連絡《れんらく》する ことに なって いる。',
            id: 'Dalam pelatihan, aturannya kami harus tiba di kantor paling lambat pukul 9 pagi. Jika sepertinya akan terlambat, kami harus selalu memberi kabar.',
            en: 'During the training, we are supposed to arrive at the office by 9 a.m. If it looks like we will be late, we are required to get in touch without fail.',
          },
          {
            ja: '先週《せんしゅう》、引《ひ》っ越《こ》しの 荷物《にもつ》を 作《つく》って いたが、本《ほん》が 多《おお》すぎて、一日《いちにち》では 終《お》わりそうも なかった。母《はは》は 古《ふる》い 本《ほん》を 見《み》て、「これは もう 必要《ひつよう》じゃ なさそうね」と 言《い》い、「手伝《てつだ》って ほしい 時《とき》は 言《い》ってね」と 心配《しんぱい》そうな 顔《かお》で 笑《わら》った。',
            id: 'Minggu lalu saya mengemas barang pindahan, tetapi bukunya terlalu banyak sehingga sepertinya tidak akan selesai dalam sehari. Ibu melihat buku-buku lama dan berkata, “Yang ini sepertinya sudah tidak perlu,” lalu sambil tersenyum dengan wajah khawatir ia berkata, “Kalau butuh bantuan, bilang ya.”',
            en: 'Last week I was packing for the move, but I had so many books that it did not look like I could finish in a day. My mother looked at the old books and said, “You don’t seem to need this any more,” and, with a worried look, laughed and said, “Tell me when you want help.”',
          },
          {
            ja: '本当《ほんとう》は 少《すこ》し 不安《ふあん》だが、新《あたら》しい 町《まち》が 楽《たの》しみだ。大阪《おおさか》の 友人《ゆうじん》には、駅《えき》まで 迎《むか》えに 来《き》て ほしいと 頼《たの》んだ。楽《たの》しそうに 話《はな》して いたので、きっと いい 生活《せいかつ》に なると 思《おも》う。',
            id: 'Sebenarnya saya sedikit cemas, tetapi saya menantikan kota baru itu. Saya meminta teman di Osaka untuk menjemput saya sampai stasiun. Ia berbicara dengan gembira, jadi saya yakin hidup di sana akan menyenangkan.',
            en: 'Honestly I am a little anxious, but I am looking forward to the new town. I asked my friend in Osaka to come and meet me at the station. They sounded happy about it, so I am sure it will be a good life.',
          },
        ],
      },
      vocab: [
        { ja: '引っ越す', reading: 'ひっこす', id: 'pindah rumah', en: 'to move (house)' },
        { ja: '研修', reading: 'けんしゅう', id: 'pelatihan', en: 'training' },
        { ja: '通勤する', reading: 'つうきんする', id: 'pergi-pulang kerja', en: 'to commute' },
        { ja: '連絡', reading: 'れんらく', id: 'kabar, kontak', en: 'contact, notice' },
        { ja: '荷物', reading: 'にもつ', id: 'barang bawaan', en: 'luggage, belongings' },
        { ja: '必要', reading: 'ひつよう', id: 'perlu', en: 'necessary' },
        { ja: '心配', reading: 'しんぱい', id: 'khawatir', en: 'worry' },
        { ja: '不安', reading: 'ふあん', id: 'cemas', en: 'uneasy' },
        { ja: '迎える', reading: 'むかえる', id: 'menjemput, menyambut', en: 'to meet, to welcome' },
      ],
      questions: [
        {
          type: 'mc',
          q: 'なぜ 大阪《おおさか》へ 引《ひ》っ越《こ》す ことに なりましたか。',
          choices: ['研修《けんしゅう》を 受《う》ける ため', '結婚《けっこん》する ため', '家《いえ》が 古《ふる》い ため', '友人《ゆうじん》に 会《あ》う ため'],
          answer: 0,
          explain: { id: 'Paragraf 1: 会社の 研修を 受ける ために… 引っ越す ことに なった.', en: 'Paragraph 1: to attend company training the move was decided.' },
        },
        {
          type: 'mc',
          q: '今《いま》までは どうやって 通勤《つうきん》して いましたか。',
          choices: ['車《くるま》', '電車《でんしゃ》', '自転車《じてんしゃ》', 'バス'],
          answer: 0,
          explain: { id: 'Paragraf 2: 今までは 車で 通勤して いた.', en: 'Paragraph 2: until now the writer commuted by car.' },
        },
        {
          type: 'mc',
          q: '大阪《おおさか》では、どう 通《かよ》う ことに しましたか。',
          choices: ['電車《でんしゃ》で 通《かよ》う', '車《くるま》で 通《かよ》う', '歩《ある》いて 通《かよ》う', '会社《かいしゃ》に 泊《と》まる'],
          answer: 0,
          explain: { id: 'Paragraf 2: 大阪では 電車で 通う ことに した.', en: 'Paragraph 2: in Osaka the writer decided to go by train.' },
        },
        {
          type: 'tf',
          q: '研修《けんしゅう》では、朝《あさ》 9時《じ》までに 会社《かいしゃ》に 着《つ》く ことに なって います。',
          answer: true,
          explain: { id: 'Paragraf 3: 朝 9時までに 会社に 着く ことに なって いる.', en: 'Paragraph 3: arrive at the office by 9 a.m. is the rule.' },
        },
        {
          type: 'tf',
          q: '遅《おく》れそうな 場合《ばあい》は、連絡《れんらく》しなくても いいです。',
          answer: false,
          explain: { id: 'Paragraf 3: 必ず 連絡する ことに なって いる (wajib memberi kabar).', en: 'Paragraph 3: contact must be made without fail.' },
        },
        {
          type: 'mc',
          q: '母《はは》は 「わたし」に 何《なに》を 言《い》いましたか。',
          choices: [
            '手伝《てつだ》って ほしい 時《とき》は 言《い》って',
            '本《ほん》を 全部《ぜんぶ》 捨《す》てて ほしい',
            '一緒《いっしょ》に 大阪《おおさか》へ 来《き》て ほしい',
            '毎日《まいにち》 電話《でんわ》して ほしい',
          ],
          answer: 0,
          explain: { id: 'Paragraf 4: 手伝って ほしい 時は 言ってね.', en: 'Paragraph 4: tell me when you want help.' },
        },
        {
          type: 'mc',
          q: '「わたし」は 大阪《おおさか》の 友人《ゆうじん》に 何《なに》を 頼《たの》みましたか。',
          choices: [
            '駅《えき》まで 迎《むか》えに 来《き》て ほしい',
            '荷物《にもつ》を 送《おく》って ほしい',
            '研修《けんしゅう》の 資料《しりょう》を 見《み》せて ほしい',
            '部屋《へや》を 探《さが》して ほしい',
          ],
          answer: 0,
          explain: { id: 'Paragraf 5: 駅まで 迎えに 来て ほしいと 頼んだ.', en: 'Paragraph 5: asked the friend to come and meet them at the station.' },
        },
      ],
      challenge: {
        id: 'Tulis kebiasaan yang kamu putuskan untuk dijalani (〜ことにしている) dan aturan di tempatmu (〜ことになっている).',
        en: 'Write a habit you have decided to keep (〜ことにしている) and a rule where you live or work (〜ことになっている).',
      },
    },
    4: {
      title: { id: 'Kenangan les piano', en: 'Memories of piano lessons' },
      focus: {
        id: '〜（さ）せられる、〜たがる・たがっている、〜である、連用形（〜く、〜て）、〜ということだ、〜の？、〜ちゃう・〜とく・〜てる',
        en: '〜(さ)せられる, 〜たがる・たがっている, 〜である, connective forms (〜く、〜て), 〜ということだ, 〜の？, 〜ちゃう・〜とく・〜てる',
      },
      before: {
        id: 'Apakah kamu pernah disuruh mengikuti les (musik, olahraga, dll.) saat kecil? Bagaimana perasaanmu waktu itu?',
        en: 'Were you ever made to take lessons (music, sports, etc.) as a child? How did you feel about it then?',
      },
      passage: {
        title: '習《なら》い事《ごと》の 思《おも》い出《で》',
        paragraphs: [
          {
            ja: '小学生《しょうがくせい》の とき、わたしは 毎週《まいしゅう》 ピアノの 教室《きょうしつ》に 通《かよ》わされた。母《はは》は 「毎日《まいにち》 30分《ぷん》 練習《れんしゅう》しなさい」と 言《い》い、練習《れんしゅう》しないと、夕飯《ゆうはん》の あとに 弾《ひ》かされた。',
            id: 'Waktu SD, saya disuruh pergi ke les piano setiap minggu. Ibu berkata, “Berlatihlah 30 menit setiap hari,” dan kalau saya tidak berlatih, saya dipaksa bermain setelah makan malam.',
            en: 'When I was in elementary school I was made to go to piano lessons every week. My mother said, “Practice for 30 minutes every day,” and if I did not, I was made to play after dinner.',
          },
          {
            ja: 'そのころは 嫌《いや》で 嫌《いや》で、ピアノの 先生《せんせい》も 怖《こわ》がって いた。弟《おとうと》は 外《そと》で サッカーを したがって いたので、わたしは うらやましかった。',
            id: 'Waktu itu saya sangat tidak suka, bahkan takut pada guru piano. Adik saya ingin sekali bermain sepak bola di luar, jadi saya iri.',
            en: 'At that time I hated it so much that I was even afraid of the piano teacher. My younger brother wanted to play soccer outside, so I was envious.',
          },
          {
            ja: '大人《おとな》に なった 今《いま》、わたしは 次《つぎ》の ことを 認《みと》めて いる。ピアノは 難《むずか》しく、時間《じかん》も かかり、楽《たの》しくない 日《ひ》も あった。しかし、その 経験《けいけん》は、わたしの 人生《じんせい》に とって 大切《たいせつ》な ものである。続《つづ》ける ことの 大切《たいせつ》さを 学《まな》んだと いう ことである。',
            id: 'Sekarang setelah dewasa, saya mengakui hal berikut. Piano itu sulit, memakan waktu, dan ada hari-hari yang tidak menyenangkan. Namun pengalaman itu adalah sesuatu yang penting bagi hidup saya. Artinya, saya belajar pentingnya terus berusaha.',
            en: 'Now that I am an adult, I admit the following. The piano was difficult, took time, and some days were not fun. However, that experience is something important in my life. In other words, I learned the importance of continuing.',
          },
          {
            ja: '友人《ゆうじん》に 話《はな》したら、「えっ、まだ 弾《ひ》ける の？」と 聞《き》かれた。「うん、毎週《まいしゅう》 少《すこ》しだけ 練習《れんしゅう》してるよ。何《なに》もしないと 忘《わす》れちゃうから、楽譜《がくふ》は 捨《す》てないで 取《と》っとくんだ」と 答《こた》えた。',
            id: 'Ketika saya menceritakannya kepada teman, ia bertanya, “Hah, kamu masih bisa main?” Saya menjawab, “Iya, tiap minggu saya berlatih sedikit. Kalau tidak melakukan apa-apa nanti lupa, jadi partitur tidak saya buang, saya simpan.”',
            en: 'When I told a friend, they asked, “Huh, you can still play?” I answered, “Yeah, I practice a little every week. If I do nothing I’ll end up forgetting, so I keep my sheet music instead of throwing it away.”',
          },
          {
            ja: 'ある 調査《ちょうさ》に よると、子《こ》どもの ころ 習《なら》い事《ごと》を させられた 大人《おとな》の 多《おお》くは、今《いま》は それを 感謝《かんしゃ》して いると いう ことだ。',
            id: 'Menurut suatu survei, kebanyakan orang dewasa yang dulu waktu kecil dipaksa les kini bersyukur atas hal itu.',
            en: 'According to one survey, many adults who were made to take lessons as children are now grateful for it.',
          },
        ],
      },
      vocab: [
        { ja: '習い事', reading: 'ならいごと', id: 'les, kursus', en: 'lessons (music, etc.)' },
        { ja: '練習', reading: 'れんしゅう', id: 'latihan', en: 'practice' },
        { ja: '弾く', reading: 'ひく', id: 'memainkan (piano)', en: 'to play (piano)' },
        { ja: '経験', reading: 'けいけん', id: 'pengalaman', en: 'experience' },
        { ja: '人生', reading: 'じんせい', id: 'kehidupan', en: 'life' },
        { ja: '認める', reading: 'みとめる', id: 'mengakui', en: 'to admit' },
        { ja: '楽譜', reading: 'がくふ', id: 'partitur', en: 'sheet music' },
        { ja: '調査', reading: 'ちょうさ', id: 'survei', en: 'survey' },
        { ja: '感謝', reading: 'かんしゃ', id: 'rasa syukur, terima kasih', en: 'gratitude' },
      ],
      questions: [
        {
          type: 'mc',
          q: '母《はは》は 練習《れんしゅう》しない とき、「わたし」に 何《なに》を させましたか。',
          choices: [
            '夕飯《ゆうはん》の あとで ピアノを 弾《ひ》かせた',
            'ピアノを 売《う》らせた',
            '宿題《しゅくだい》を たくさん 書《か》かせた',
            '弟《おとうと》と 遊《あそ》ばせた',
          ],
          answer: 0,
          explain: { id: 'Paragraf 1: 練習しないと、夕飯の あとに 弾かされた.', en: 'Paragraph 1: if I did not practice, I was made to play after dinner.' },
        },
        {
          type: 'mc',
          q: '弟《おとうと》は 何《なに》を したがって いましたか。',
          choices: ['外《そと》で サッカーを する こと', 'ピアノを 習《なら》う こと', '家《いえ》で 寝《ね》る こと', '先生《せんせい》に 会《あ》う こと'],
          answer: 0,
          explain: { id: 'Paragraf 2: 外で サッカーを したがって いた.', en: 'Paragraph 2: he wanted to play soccer outside.' },
        },
        {
          type: 'tf',
          q: '「わたし」は 子《こ》どもの とき、ピアノの 先生《せんせい》を 怖《こわ》がって いました。',
          answer: true,
          explain: { id: 'Paragraf 2: ピアノの 先生も 怖がって いた.', en: 'Paragraph 2: the writer was afraid of the piano teacher too.' },
        },
        {
          type: 'mc',
          q: '「わたし」は ピアノの 経験《けいけん》から 何《なに》を 学《まな》びましたか。',
          choices: ['続《つづ》ける ことの 大切《たいせつ》さ', '早《はや》く 弾《ひ》く 方法《ほうほう》', '友人《ゆうじん》を 作《つく》る 方法《ほうほう》', 'お金《かね》の 大切《たいせつ》さ'],
          answer: 0,
          explain: { id: 'Paragraf 3: 続ける ことの 大切さを 学んだ.', en: 'Paragraph 3: learned the importance of continuing.' },
        },
        {
          type: 'tf',
          q: '今《いま》、「わたし」は ピアノを 全然《ぜんぜん》 弾《ひ》きません。',
          answer: false,
          explain: { id: 'Paragraf 4: 毎週 少しだけ 練習してる (berlatih tiap minggu).', en: 'Paragraph 4: practices a little every week.' },
        },
        {
          type: 'mc',
          q: '「取《と》っとく」は どういう 意味《いみ》ですか。',
          choices: ['取《と》って おく', '取《と》って しまう', '取《と》って いる', '取《と》りに 行《い》く'],
          answer: 0,
          explain: { id: '〜とく adalah bentuk lisan 〜ておく (menyimpan untuk nanti).', en: '〜とく is the spoken form of 〜ておく (do in advance, keep).' },
        },
        {
          type: 'tf',
          q: '調査《ちょうさ》に よると、習《なら》い事《ごと》を させられた 大人《おとな》の 多《おお》くは、今《いま》 後悔《こうかい》して います。',
          answer: false,
          explain: { id: 'Paragraf 5: 多くは 今は 感謝して いる (bersyukur, bukan menyesal).', en: 'Paragraph 5: most are now grateful, not regretful.' },
        },
      ],
      challenge: {
        id: 'Tulis pengalaman masa kecil yang pernah kamu "dipaksa" lakukan (〜させられた) dan apa yang kamu pelajari darinya (〜ということだ).',
        en: 'Write about something you were made to do as a child (〜させられた) and what you learned from it (〜ということだ).',
      },
    },
    5: {
      title: { id: 'Menunjukkan jalan', en: 'Giving directions' },
      focus: {
        id: 'あ〜・そ〜、〜んじゃない？、〜た ところに／で、〜（よ）うと する、〜のだろうか、〜との／での／からの〜、〜だろう',
        en: 'あ〜・そ〜, 〜んじゃない？, 〜た ところに／で, 〜(よ)うと する, 〜のだろうか, 〜との／での／からの〜, 〜だろう',
      },
      before: {
        id: 'Pernahkah kamu ditanya arah oleh orang asing? Bisakah kamu menjelaskannya dengan bahasa Jepang?',
        en: 'Have you ever been asked for directions by a stranger? Could you explain in Japanese?',
      },
      passage: {
        title: '道《みち》を 聞《き》かれて',
        paragraphs: [
          {
            ja: '駅前《えきまえ》で、外国《がいこく》から 来《き》た 観光客《かんこうきゃく》に 道《みち》を 聞《き》かれた。「すみません、市立《しりつ》博物館《はくぶつかん》へは どう 行《い》ったら いいですか」。わたしは 説明《せつめい》しようと したが、うまく 言《い》えなかった。',
            id: 'Di depan stasiun, seorang turis dari luar negeri bertanya jalan kepada saya. “Permisi, bagaimana cara ke museum kota?” Saya mencoba menjelaskan, tetapi tidak bisa mengatakannya dengan baik.',
            en: 'In front of the station, a tourist from abroad asked me for directions. “Excuse me, how do I get to the city museum?” I tried to explain, but I could not put it well.',
          },
          {
            ja: 'そこで、「この 大通《おおどお》りを まっすぐ 行《い》くと、コンビニが あります。その コンビニの 角《かど》を 右《みぎ》に 曲《ま》がった ところに 博物館《はくぶつかん》が あります」と 教《おし》えた。',
            id: 'Maka saya berkata, “Kalau Anda lurus di jalan besar ini, ada minimarket. Museumnya ada di tempat setelah berbelok ke kanan di pojok minimarket itu.”',
            en: 'So I told him, “If you go straight along this main street, there is a convenience store. The museum is at the place you reach after turning right at the corner of that store.”',
          },
          {
            ja: '観光客《かんこうきゃく》は 地図《ちず》を 見《み》ながら、「あの 赤《あか》い 看板《かんばん》の コンビニですか。あそこですね」と 言《い》って、うれしそうに 歩《ある》いて 行《い》った。',
            id: 'Sang turis sambil melihat peta berkata, “Minimarket dengan papan merah itu? Di sana, ya,” lalu pergi berjalan dengan gembira.',
            en: 'Looking at his map, the tourist said, “The convenience store with that red sign? Over there, right?” and walked off happily.',
          },
          {
            ja: 'あとで 友人《ゆうじん》に この 話《はなし》を したら、「地図《ちず》を 見《み》せて 説明《せつめい》した ほうが よかったんじゃない？」と 言《い》われた。確《たし》かに、そうかもしれない。言葉《ことば》だけで 道順《みちじゅん》を 伝《つた》える ことは 本当《ほんとう》に できるのだろうか。',
            id: 'Ketika saya menceritakannya kepada teman, ia berkata, “Bukankah lebih baik kalau kamu menunjukkan peta sambil menjelaskan?” Memang benar juga. Apakah kita benar-benar bisa menyampaikan rute hanya dengan kata-kata?',
            en: 'When I told a friend about it later, he said, “Wouldn’t it have been better to show him a map while explaining?” He may be right. Can you really convey a route with words alone?',
          },
          {
            ja: '翌日《よくじつ》、その 人《ひと》からの メールが 届《とど》いた。「親切《しんせつ》な 説明《せつめい》を ありがとう」という 内容《ないよう》だった。無事《ぶじ》に 博物館《はくぶつかん》に 着《つ》いたのだろう。日本《にほん》での 旅行《りょこう》の いい 思《おも》い出《で》に なったと いい。',
            id: 'Keesokan harinya, sebuah surel dari orang itu tiba. Isinya, “Terima kasih atas penjelasan yang ramah.” Sepertinya ia tiba di museum dengan selamat. Semoga ini menjadi kenangan perjalanan yang baik di Jepang.',
            en: 'The next day an e-mail from that person arrived. It said, “Thank you for your kind explanation.” He probably reached the museum safely. I hope it became a good memory of his trip in Japan.',
          },
        ],
      },
      vocab: [
        { ja: '観光客', reading: 'かんこうきゃく', id: 'wisatawan', en: 'tourist' },
        { ja: '大通り', reading: 'おおどおり', id: 'jalan raya', en: 'main street' },
        { ja: '角', reading: 'かど', id: 'pojok, sudut', en: 'corner' },
        { ja: '曲がる', reading: 'まがる', id: 'berbelok', en: 'to turn' },
        { ja: '地図', reading: 'ちず', id: 'peta', en: 'map' },
        { ja: '道順', reading: 'みちじゅん', id: 'rute, urutan jalan', en: 'route, directions' },
        { ja: '説明', reading: 'せつめい', id: 'penjelasan', en: 'explanation' },
        { ja: '内容', reading: 'ないよう', id: 'isi', en: 'content' },
        { ja: '感謝', reading: 'かんしゃ', id: 'rasa terima kasih', en: 'gratitude' },
      ],
      questions: [
        {
          type: 'mc',
          q: '観光客《かんこうきゃく》は 何《なに》を 聞《き》きましたか。',
          choices: ['博物館《はくぶつかん》への 行《い》き方《かた》', '駅《えき》の 場所《ばしょ》', 'コンビニの 名前《なまえ》', 'ホテルの 値段《ねだん》'],
          answer: 0,
          explain: { id: 'Paragraf 1: 市立博物館へは どう 行ったら いいですか.', en: 'Paragraph 1: how to get to the city museum.' },
        },
        {
          type: 'mc',
          q: '博物館《はくぶつかん》は どこに ありますか。',
          choices: [
            'コンビニの 角《かど》を 右《みぎ》に 曲《ま》がった ところ',
            '駅《えき》の すぐ 前《まえ》',
            '大通《おおどお》りの 反対側《はんたいがわ》',
            'コンビニの 中《なか》',
          ],
          answer: 0,
          explain: { id: 'Paragraf 2: コンビニの 角を 右に 曲がった ところに あります.', en: 'Paragraph 2: at the place after turning right at the store’s corner.' },
        },
        {
          type: 'tf',
          q: '「わたし」は 最初《さいしょ》から うまく 説明《せつめい》できました。',
          answer: false,
          explain: { id: 'Paragraf 1: 説明しようと したが、うまく 言えなかった.', en: 'Paragraph 1: tried to explain but could not put it well.' },
        },
        {
          type: 'mc',
          q: '「説明《せつめい》しようと した」は どういう 意味《いみ》ですか。',
          choices: ['説明《せつめい》を 始《はじ》めようと した', '説明《せつめい》を やめた', '説明《せつめい》が 上手《じょうず》だった', '説明《せつめい》を 聞《き》いた'],
          answer: 0,
          explain: { id: '〜（よ）うと する = mencoba／hendak melakukan (belum tentu berhasil).', en: '〜(よ)うと する = to try／be about to do (not necessarily successfully).' },
        },
        {
          type: 'mc',
          q: '友人《ゆうじん》は 何《なん》と 言《い》いましたか。',
          choices: [
            '地図《ちず》を 見《み》せて 説明《せつめい》した ほうが よかったんじゃない',
            'もっと 早《はや》く 話《はな》した ほうが よかった',
            '道《みち》を 教《おし》えない ほうが よかった',
            '博物館《はくぶつかん》に 連《つ》れて 行《い》った ほうが よかった',
          ],
          answer: 0,
          explain: { id: 'Paragraf 4: 地図を 見せて 説明した ほうが よかったんじゃない？', en: 'Paragraph 4: it would have been better to show a map, wouldn’t it?' },
        },
        {
          type: 'tf',
          q: '「わたし」は、言葉《ことば》だけで 道順《みちじゅん》を 伝《つた》える ことは 簡単《かんたん》だと 思《おも》って います。',
          answer: false,
          explain: { id: 'Paragraf 4: 本当に できるのだろうか — ia ragu.', en: 'Paragraph 4: “can it really be done?” — the writer doubts it.' },
        },
        {
          type: 'mc',
          q: '観光客《かんこうきゃく》からの メールの 内容《ないよう》は 何《なん》でしたか。',
          choices: ['親切《しんせつ》な 説明《せつめい》への お礼《れい》', '道《みち》に 迷《まよ》った という 知《し》らせ', '博物館《はくぶつかん》の 場所《ばしょ》の 質問《しつもん》', 'ホテルの 予約《よやく》の お願《ねが》い'],
          answer: 0,
          explain: { id: 'Paragraf 5: 「親切な 説明を ありがとう」という 内容.', en: 'Paragraph 5: it said “Thank you for your kind explanation.”' },
        },
      ],
      challenge: {
        id: 'Tulis petunjuk jalan dari stasiun ke rumahmu memakai 〜た ところに／で dan あ〜／そ〜.',
        en: 'Write directions from the station to your home using 〜た ところに／で and あ〜／そ〜.',
      },
    },
    6: {
      title: { id: 'Janji pada diri sendiri', en: 'A promise to myself' },
      focus: {
        id: '〜って（topik・kutipan）、〜つもりはない・〜つもりだった、〜て ばかり いる、〜とか〜とか、〜て くる・〜て いく',
        en: '〜って (topic・quotation), 〜つもりはない・〜つもりだった, 〜て ばかり いる, 〜とか〜とか, 〜て くる・〜て いく',
      },
      before: {
        id: 'Bagaimana kamu membayangkan dirimu sepuluh tahun lagi? Apa yang sudah kamu mulai sekarang?',
        en: 'How do you picture yourself ten years from now? What have you already started?',
      },
      passage: {
        title: '自分《じぶん》との 約束《やくそく》',
        paragraphs: [
          {
            ja: '大学《だいがく》を 卒業《そつぎょう》して 五年《ごねん》が たった。最近《さいきん》、「一生《いっしょう》 同《おな》じ 会社《かいしゃ》で 働《はたら》く つもりは ない」と 友人《ゆうじん》に 話《はな》したら、「えっ、辞《や》める つもりなの？」と 驚《おどろ》かれた。',
            id: 'Lima tahun telah berlalu sejak saya lulus kuliah. Belakangan ini saya bilang pada teman, “Aku tidak berniat bekerja di perusahaan yang sama seumur hidup,” lalu ia terkejut, “Hah, kamu berniat berhenti?”',
            en: 'Five years have passed since I graduated. Recently I told a friend, “I don’t intend to work at the same company for life,” and was met with surprise: “What, are you planning to quit?”',
          },
          {
            ja: '実《じつ》は、仕事《しごと》が いやなわけでは ない。ただ、毎日《まいにち》 パソコンの 前《まえ》に 座《すわ》って ばかり いて、運動《うんどう》も しないし、人《ひと》と 話《はな》す 時間《じかん》も 少《すく》ない。',
            id: 'Sebenarnya bukan karena saya membenci pekerjaan. Hanya saja, setiap hari saya terus-menerus duduk di depan komputer, tidak berolahraga, dan jarang berbicara dengan orang.',
            en: 'The truth is, it isn’t that I dislike my work. It is just that I do nothing but sit in front of a computer every day, never exercise, and rarely talk with people.',
          },
          {
            ja: 'そこで、先週《せんしゅう》から 朝《あさ》の 散歩《さんぽ》を 始《はじ》めた。本当《ほんとう》は 先月《せんげつ》から 始《はじ》める つもりだったが、雨《あめ》が 続《つづ》いて できなかった。',
            id: 'Karena itu, sejak minggu lalu saya mulai berjalan pagi. Sebenarnya saya berniat memulainya bulan lalu, tetapi hujan terus turun sehingga tidak jadi.',
            en: 'So I started a morning walk last week. I had actually meant to begin last month, but the rain kept up and I couldn’t.',
          },
          {
            ja: '公園《こうえん》に 「ラジオ体操《たいそう》の 会《かい》」って 書《か》いて あった。何《なん》だろうと 思《おも》って 聞《き》いたら、「みんなで 体操《たいそう》を する 会《かい》ですよ」と 教《おし》えて もらった。',
            id: 'Di taman tertulis “Kelompok Senam Radio”. Saat saya bertanya apa itu, saya diberi tahu, “Itu kelompok yang senam bersama-sama.”',
            en: 'In the park a sign read “Radio Calisthenics Group.” When I asked what it was, I was told, “It’s a group that does exercises together.”',
          },
          {
            ja: '参加《さんか》して みると、仲間《なかま》も できた。ストレッチとか 散歩《さんぽ》とか、簡単《かんたん》な ことを 続《つづ》けて いると、少《すこ》しずつ 体《からだ》が 軽《かる》く なって きた。',
            id: 'Setelah mencoba ikut, saya pun mendapat teman. Dengan terus melakukan hal sederhana seperti peregangan dan jalan kaki, tubuh saya sedikit demi sedikit terasa makin ringan.',
            en: 'After I joined in, I even made friends. Continuing simple things like stretching and walking, my body has gradually come to feel lighter.',
          },
          {
            ja: '将来《しょうらい》の ことは まだ わからない。でも、「いつか 自分《じぶん》の 店《みせ》を 持《も》ちたい」って 思《おも》う ように なった。これから 何《なに》が 変《か》わって いくのか、楽《たの》しみだ。',
            id: 'Masa depan masih belum jelas. Namun saya mulai berpikir, “Suatu hari saya ingin punya toko sendiri.” Saya menantikan apa yang akan berubah mulai sekarang.',
            en: 'I still don’t know about the future. But I have come to think, “Someday I’d like to have my own shop.” I look forward to what will change from here on.',
          },
        ],
      },
      vocab: [
        { ja: '一生', reading: 'いっしょう', id: 'seumur hidup', en: 'one’s whole life' },
        { ja: '驚く', reading: 'おどろく', id: 'terkejut', en: 'to be surprised' },
        { ja: '運動', reading: 'うんどう', id: 'olahraga, gerak badan', en: 'exercise' },
        { ja: '体操', reading: 'たいそう', id: 'senam', en: 'calisthenics, gymnastics' },
        { ja: '仲間', reading: 'なかま', id: 'teman seperjuangan, rekan', en: 'companion, fellow member' },
        { ja: '続ける', reading: 'つづける', id: 'melanjutkan', en: 'to continue' },
        { ja: '将来', reading: 'しょうらい', id: 'masa depan', en: 'the future' },
        { ja: '簡単', reading: 'かんたん', id: 'sederhana, mudah', en: 'simple, easy' },
      ],
      questions: [
        {
          type: 'mc',
          q: '「わたし」は 友人《ゆうじん》に 何《なん》と 話《はな》しましたか。',
          choices: [
            '一生《いっしょう》 同《おな》じ 会社《かいしゃ》で 働《はたら》く つもりは ない',
            '来月《らいげつ》 会社《かいしゃ》を 辞《や》める',
            '結婚《けっこん》する つもりだ',
            '外国《がいこく》へ 行《い》く つもりだ',
          ],
          answer: 0,
          explain: { id: 'Paragraf 1: 一生 同じ 会社で 働く つもりは ない.', en: 'Paragraph 1: I don’t intend to work at the same company for life.' },
        },
        {
          type: 'mc',
          q: '「わたし」の 毎日《まいにち》の 問題《もんだい》は 何《なん》ですか。',
          choices: [
            '座《すわ》って ばかり いて、運動《うんどう》が 足《た》りない こと',
            '仕事《しごと》が 多《おお》すぎる こと',
            '給料《きゅうりょう》が 低《ひく》い こと',
            '友人《ゆうじん》が いない こと',
          ],
          answer: 0,
          explain: { id: 'Paragraf 2: 座って ばかり いて、運動も しない (〜て ばかり いる).', en: 'Paragraph 2: sits all the time and doesn’t exercise (〜て ばかり いる).' },
        },
        {
          type: 'tf',
          q: '「わたし」は 仕事《しごと》が いやで 会社《かいしゃ》を 辞《や》めたいと 思《おも》って いる。',
          answer: false,
          explain: { id: 'Paragraf 2: 仕事が いやなわけでは ない.', en: 'Paragraph 2: it isn’t that I dislike the work.' },
        },
        {
          type: 'mc',
          q: '朝《あさ》の 散歩《さんぽ》を、本当《ほんとう》は いつから 始《はじ》める つもりでしたか。',
          choices: ['先月《せんげつ》', '先週《せんしゅう》', '来月《らいげつ》', '去年《きょねん》'],
          answer: 0,
          explain: { id: 'Paragraf 3: 先月から 始める つもりだったが (〜つもりだった = rencana yang tidak terlaksana).', en: 'Paragraph 3: meant to start last month (〜つもりだった = a plan that fell through).' },
        },
        {
          type: 'mc',
          q: '「ラジオ体操《たいそう》の 会《かい》」って 何《なん》ですか。',
          choices: [
            'みんなで 体操《たいそう》を する 会《かい》',
            'ラジオを 作《つく》る 会《かい》',
            '犬《いぬ》の 散歩《さんぽ》を する 会《かい》',
            '料理《りょうり》を 習《なら》う 会《かい》',
          ],
          answer: 0,
          explain: { id: 'Paragraf 4: みんなで 体操を する 会ですよ.', en: 'Paragraph 4: a group that does exercises together.' },
        },
        {
          type: 'tf',
          q: '続《つづ》けて いる うちに、体《からだ》が 少《すこ》しずつ 軽《かる》く なって きた。',
          answer: true,
          explain: { id: 'Paragraf 5: 軽く なって きた (〜て くる = perubahan bertahap).', en: 'Paragraph 5: it has become lighter (〜て くる = gradual change).' },
        },
        {
          type: 'mc',
          q: '「わたし」は 将来《しょうらい》、何《なに》を したいと 思《おも》う ように なりましたか。',
          choices: ['自分《じぶん》の 店《みせ》を 持《も》つ', '留学《りゅうがく》する', '先生《せんせい》に なる', '田舎《いなか》に 住《す》む'],
          answer: 0,
          explain: { id: 'Paragraf 6: いつか 自分の 店を 持ちたい.', en: 'Paragraph 6: someday I want to have my own shop.' },
        },
      ],
      challenge: {
        id: 'Tulis janji kecil yang ingin kamu mulai. Pakai 〜つもりだ、〜とか〜とか、〜て きた.',
        en: 'Write a small promise you want to start keeping. Use 〜つもりだ, 〜とか〜とか, 〜て きた.',
      },
    },
    7: {
      title: { id: 'Pesta perpisahan', en: 'The farewell party' },
      focus: {
        id: '〜なくては ならない・〜なくても かまわない、〜なくちゃ・〜なきゃ、〜だけ、〜かな、〜なんか・〜なんて、〜（さ）せる・〜（さ）せられる、〜なら',
        en: '〜なくては ならない・〜なくても かまわない, 〜なくちゃ・〜なきゃ, 〜だけ, 〜かな, 〜なんか・〜なんて, 〜(さ)せる・〜(さ)せられる, 〜なら',
      },
      before: {
        id: 'Pernahkah kamu menjadi panitia sebuah acara? Apa yang paling merepotkan?',
        en: 'Have you ever organized an event? What was the most troublesome part?',
      },
      passage: {
        title: '送別会《そうべつかい》の 準備《じゅんび》',
        paragraphs: [
          {
            ja: '来週《らいしゅう》、同僚《どうりょう》の 田中《たなか》さんの 送別会《そうべつかい》が ある。幹事《かんじ》は わたしだ。何《なに》を 準備《じゅんび》しなくては ならないのか、考《かんが》えた。',
            id: 'Minggu depan ada pesta perpisahan untuk rekan kerja, Tanaka. Sayalah panitianya. Saya memikirkan apa saja yang harus dipersiapkan.',
            en: 'Next week there is a farewell party for my colleague Tanaka. I am the organizer. I thought about what has to be prepared.',
          },
          {
            ja: '店《みせ》は どこが いいかな。先輩《せんぱい》に 相談《そうだん》したら、「駅前《えきまえ》の 中華《ちゅうか》レストランなら 安《やす》くて おいしいよ」と 教《おし》えて くれた。',
            id: 'Tempatnya sebaiknya di mana, ya? Ketika saya berkonsultasi dengan senior, ia memberi tahu, “Kalau restoran Cina di depan stasiun, murah dan enak.”',
            en: 'Where would be a good place, I wondered. When I consulted a senior colleague, he said, “If it’s the Chinese restaurant in front of the station, it’s cheap and tasty.”',
          },
          {
            ja: 'プレゼントの お金《かね》は、たくさん 出《だ》さなくても かまわない。一人《ひとり》 五百円《ごひゃくえん》 だけで いい ことに した。',
            id: 'Uang untuk hadiah tidak perlu banyak. Saya memutuskan cukup lima ratus yen per orang.',
            en: 'For the present, people don’t have to chip in much. I decided that 500 yen each is enough.',
          },
          {
            ja: 'スピーチも 頼《たの》まれた。「わたしなんか 無理《むり》ですよ」と 断《ことわ》ろうと したら、部長《ぶちょう》に 「君《きみ》が 話《はな》す だけで いいんだ」と 言《い》われ、結局《けっきょく》 引《ひ》き受《う》けた。',
            id: 'Saya juga diminta berpidato. Ketika saya hendak menolak, “Orang seperti saya mana mungkin,” kepala bagian berkata, “Cukup kamu yang bicara,” dan akhirnya saya menerimanya.',
            en: 'I was also asked to give a speech. When I tried to decline with “Someone like me can’t possibly,” the department head said, “It’s enough that you speak,” and in the end I accepted.',
          },
          {
            ja: '当日《とうじつ》、田中《たなか》さんは みんなの 話《はなし》を 聞《き》いて、泣《な》き出《だ》した。田中《たなか》さんは いつも 冗談《じょうだん》を 言《い》って、みんなを 笑《わら》わせて いた 人《ひと》だ。「田中《たなか》さんが 泣《な》くなんて 信《しん》じられない」と 隣《となり》の 人《ひと》が 言《い》った。わたしも 思《おも》わず 感動《かんどう》させられた。',
            id: 'Pada hari-H, Tanaka mendengarkan cerita semua orang lalu menangis. Tanaka biasanya melontarkan lelucon dan membuat semua orang tertawa. Orang di sebelah berkata, “Tak kusangka Tanaka bisa menangis.” Saya pun tanpa sadar jadi terharu.',
            en: 'On the day, Tanaka listened to everyone’s stories and burst into tears. He was always the one cracking jokes and making everyone laugh. The person next to me said, “I can’t believe Tanaka would cry.” I, too, was moved despite myself.',
          },
          {
            ja: '準備《じゅんび》は 大変《たいへん》だったが、やって よかった。あしたまでに お礼《れい》の メールを 書《か》かなきゃ。',
            id: 'Persiapannya melelahkan, tetapi senang sudah melakukannya. Besok saya harus menulis surel ucapan terima kasih.',
            en: 'The preparation was hard, but I’m glad I did it. I have to write a thank-you e-mail by tomorrow.',
          },
        ],
      },
      vocab: [
        { ja: '送別会', reading: 'そうべつかい', id: 'pesta perpisahan', en: 'farewell party' },
        { ja: '幹事', reading: 'かんじ', id: 'panitia, penyelenggara', en: 'organizer' },
        { ja: '同僚', reading: 'どうりょう', id: 'rekan kerja', en: 'colleague' },
        { ja: '引き受ける', reading: 'ひきうける', id: 'menerima (tugas)', en: 'to take on' },
        { ja: '結局', reading: 'けっきょく', id: 'pada akhirnya', en: 'in the end' },
        { ja: '当日', reading: 'とうじつ', id: 'hari-H', en: 'the day itself' },
        { ja: '冗談', reading: 'じょうだん', id: 'lelucon', en: 'joke' },
        { ja: '感動', reading: 'かんどう', id: 'terharu', en: 'being moved' },
      ],
      questions: [
        {
          type: 'mc',
          q: '送別会《そうべつかい》の 幹事《かんじ》は だれですか。',
          choices: ['「わたし」', '田中《たなか》さん', '先輩《せんぱい》', '部長《ぶちょう》'],
          answer: 0,
          explain: { id: 'Paragraf 1: 幹事は わたしだ.', en: 'Paragraph 1: I am the organizer.' },
        },
        {
          type: 'mc',
          q: '先輩《せんぱい》は どんな 店《みせ》を すすめましたか。',
          choices: [
            '駅前《えきまえ》の 中華《ちゅうか》レストラン',
            '会社《かいしゃ》の 近《ちか》くの 喫茶店《きっさてん》',
            '駅《えき》から 遠《とお》い 和食《わしょく》の 店《みせ》',
            '有名《ゆうめい》な フランス料理《りょうり》の 店《みせ》',
          ],
          answer: 0,
          explain: { id: 'Paragraf 2: 駅前の 中華レストランなら 安くて おいしい.', en: 'Paragraph 2: if it’s the Chinese restaurant by the station, it’s cheap and tasty.' },
        },
        {
          type: 'tf',
          q: 'プレゼントの ために、一人《ひとり》 たくさん お金《かね》を 出《だ》さなくては ならない。',
          answer: false,
          explain: { id: 'Paragraf 3: たくさん 出さなくても かまわない; 五百円だけで いい.', en: 'Paragraph 3: they needn’t pay a lot; 500 yen is enough.' },
        },
        {
          type: 'mc',
          q: '「わたしなんか 無理《むり》ですよ」と 言《い》ったのは なぜですか。',
          choices: ['スピーチを 断《ことわ》りたかったから', '田中《たなか》さんが 嫌《きら》いだったから', '忙《いそが》しかったから', 'お金《かね》が なかったから'],
          answer: 0,
          explain: { id: 'Paragraf 4: 断ろうと した とき の ことば (〜なんか = merendahkan diri).', en: 'Paragraph 4: said while trying to decline (〜なんか = self-deprecating).' },
        },
        {
          type: 'mc',
          q: '部長《ぶちょう》は 何《なん》と 言《い》いましたか。',
          choices: [
            '君《きみ》が 話《はな》す だけで いい',
            '話《はな》さなくても かまわない',
            '短《みじか》く 話《はな》さなくては ならない',
            '上手《じょうず》に 話《はな》さなくては いけない',
          ],
          answer: 0,
          explain: { id: 'Paragraf 4: 君が 話す だけで いいんだ.', en: 'Paragraph 4: it’s enough that you speak.' },
        },
        {
          type: 'tf',
          q: '田中《たなか》さんが 泣《な》いた ので、隣《となり》の 人《ひと》は 驚《おどろ》いた。',
          answer: true,
          explain: { id: 'Paragraf 5: 泣くなんて 信じられない — terkejut karena ia biasanya melucu.', en: 'Paragraph 5: “can’t believe he’d cry” — surprised, as he is usually the joker.' },
        },
        {
          type: 'mc',
          q: '「感動《かんどう》させられた」の 意味《いみ》は どれですか。',
          choices: [
            '思《おも》わず 感動《かんどう》した',
            'わざと 感動《かんどう》した',
            'だれかに 感動《かんどう》を 頼《たの》んだ',
            '感動《かんどう》しなかった',
          ],
          answer: 0,
          explain: { id: '〜（さ）せられる = tergerak oleh sesuatu di luar kehendak: terharu tanpa sengaja.', en: '〜(さ)せられる = made to feel by something: moved involuntarily.' },
        },
      ],
      challenge: {
        id: 'Tulis tentang acara yang pernah kamu siapkan. Pakai 〜なくては ならない、〜だけ、〜かな、〜なら.',
        en: 'Write about an event you once prepared. Use 〜なくては ならない, 〜だけ, 〜かな, 〜なら.',
      },
    },
    8: {
      title: { id: 'Musim panas menjaga rumah', en: 'A summer house-sitting' },
      focus: {
        id: '〜あいだ・〜あいだに、〜まで・〜までに、〜た N（kata sifat bentuk た）、〜によって、〜まま、〜からだ',
        en: '〜あいだ・〜あいだに, 〜まで・〜までに, 〜た N (modifier), 〜によって, 〜まま, 〜からだ',
      },
      before: {
        id: 'Pernahkah kamu menjaga rumah sendirian? Apa yang kamu lakukan selama itu?',
        en: 'Have you ever looked after a house alone? What did you do in that time?',
      },
      passage: {
        title: '留守番《るすばん》の 夏休《なつやす》み',
        paragraphs: [
          {
            ja: '夏休《なつやす》みの あいだ、両親《りょうしん》が 旅行《りょこう》に 行《い》って、わたしは 一人《ひとり》で 留守番《るすばん》を した。両親《りょうしん》が 帰《かえ》って くるまで、犬《いぬ》の 世話《せわ》も わたしの 仕事《しごと》だった。',
            id: 'Selama liburan musim panas, orang tua saya bepergian dan saya menjaga rumah sendirian. Sampai mereka pulang, merawat anjing juga menjadi tugas saya.',
            en: 'During summer vacation my parents went on a trip and I kept the house by myself. Until they came back, taking care of the dog was also my job.',
          },
          {
            ja: '両親《りょうしん》が いない あいだに、部屋《へや》の 大掃除《おおそうじ》を しようと 思《おも》った。母《はは》が 帰《かえ》るまでに、台所《だいどころ》を きれいに しなくては ならない。',
            id: 'Selagi orang tua tidak ada, saya berniat membersihkan kamar besar-besaran. Sebelum ibu pulang, dapur harus sudah bersih.',
            en: 'While my parents were away, I decided to give my room a thorough cleaning. By the time my mother returned, the kitchen had to be spotless.',
          },
          {
            ja: '窓《まど》を 開《あ》けた まま 出《で》かけて しまった 日《ひ》が あった。帰《かえ》ったら、雨《あめ》で 床《ゆか》が ぬれて いた。',
            id: 'Ada hari ketika saya pergi dengan jendela masih terbuka. Sesampainya di rumah, lantai basah karena hujan.',
            en: 'One day I went out leaving the window open. When I got back, the floor was wet from the rain.',
          },
          {
            ja: '玄関《げんかん》には、壊《こわ》れた 傘《かさ》が 置《お》いて あった。母《はは》が 帰《かえ》る 前《まえ》に、それを 捨《す》てて おいた。',
            id: 'Di pintu masuk ada sebuah payung rusak yang diletakkan. Sebelum ibu pulang, saya membuangnya.',
            en: 'In the entrance hall a broken umbrella had been left. Before my mother came back, I threw it away.',
          },
          {
            ja: '帰《かえ》って きた 両親《りょうしん》は、食《た》べ物《もの》や あいさつの 仕方《しかた》は 国《くに》によって 違《ちが》う ことに 驚《おどろ》いたと 話《はな》して くれた。',
            id: 'Orang tua yang pulang bercerita bahwa mereka terkejut karena makanan dan cara memberi salam berbeda tergantung negaranya.',
            en: 'My parents, back home, told me they were surprised that food and the way of greeting differ from country to country.',
          },
          {
            ja: 'わたしも いつか 外国《がいこく》を 旅行《りょこう》したい。それは、いろいろな 文化《ぶんか》を 知《し》りたいからだ。',
            id: 'Saya pun suatu hari ingin bepergian ke luar negeri. Itu karena saya ingin mengenal berbagai budaya.',
            en: 'Someday I want to travel abroad too. That is because I want to learn about many different cultures.',
          },
        ],
      },
      vocab: [
        { ja: '留守番', reading: 'るすばん', id: 'menjaga rumah', en: 'house-sitting' },
        { ja: '世話', reading: 'せわ', id: 'merawat, mengurus', en: 'care, looking after' },
        { ja: '大掃除', reading: 'おおそうじ', id: 'bersih-bersih besar', en: 'thorough cleaning' },
        { ja: '床', reading: 'ゆか', id: 'lantai', en: 'floor' },
        { ja: '玄関', reading: 'げんかん', id: 'pintu masuk, genkan', en: 'entrance hall' },
        { ja: '習慣', reading: 'しゅうかん', id: 'kebiasaan, adat', en: 'custom, habit' },
        { ja: '文化', reading: 'ぶんか', id: 'budaya', en: 'culture' },
      ],
      questions: [
        {
          type: 'mc',
          q: '両親《りょうしん》が 旅行《りょこう》して いる あいだ、「わたし」は 何《なに》を しましたか。',
          choices: [
            '留守番《るすばん》を して、犬《いぬ》の 世話《せわ》を した',
            '一緒《いっしょ》に 旅行《りょこう》した',
            'アルバイトを した',
            '友人《ゆうじん》の 家《いえ》に 泊《と》まった',
          ],
          answer: 0,
          explain: { id: 'Paragraf 1: 留守番を して、犬の 世話も した.', en: 'Paragraph 1: kept the house and looked after the dog.' },
        },
        {
          type: 'mc',
          q: '母《はは》が 帰《かえ》る までに、何《なに》を しなくては なりませんか。',
          choices: ['台所《だいどころ》を きれいに する', '犬《いぬ》の 散歩《さんぽ》を 始《はじ》める', '旅行《りょこう》の 準備《じゅんび》を する', '窓《まど》を 閉《し》める'],
          answer: 0,
          explain: { id: 'Paragraf 2: 母が 帰るまでに、台所を きれいに (〜までに = batas waktu).', en: 'Paragraph 2: by the time Mother returns, clean the kitchen (〜までに = deadline).' },
        },
        {
          type: 'tf',
          q: '「わたし」は 窓《まど》を 閉《し》めた まま 出《で》かけた。',
          answer: false,
          explain: { id: 'Paragraf 3: 窓を 開けた まま 出かけた.', en: 'Paragraph 3: left with the window open.' },
        },
        {
          type: 'mc',
          q: '帰《かえ》った とき、部屋《へや》は どう なって いましたか。',
          choices: ['雨《あめ》で 床《ゆか》が ぬれて いた', '泥棒《どろぼう》が 入《はい》って いた', '犬《いぬ》が いなかった', '火事《かじ》に なって いた'],
          answer: 0,
          explain: { id: 'Paragraf 3: 雨で 床が ぬれて いた.', en: 'Paragraph 3: the floor was wet from the rain.' },
        },
        {
          type: 'mc',
          q: '玄関《げんかん》に 何《なに》が 置《お》いて ありましたか。',
          choices: ['壊《こわ》れた 傘《かさ》', '新《あたら》しい 靴《くつ》', '古《ふる》い 自転車《じてんしゃ》', '大《おお》きい かばん'],
          answer: 0,
          explain: { id: 'Paragraf 4: 壊れた 傘 (〜た + 名詞 = keadaan hasil).', en: 'Paragraph 4: a broken umbrella (〜た + noun = resulting state).' },
        },
        {
          type: 'tf',
          q: '両親《りょうしん》は、習慣《しゅうかん》は どの 国《くに》でも 同《おな》じだと 話《はな》した。',
          answer: false,
          explain: { id: 'Paragraf 5: 国によって 違う (berbeda tergantung negara).', en: 'Paragraph 5: it differs depending on the country.' },
        },
        {
          type: 'mc',
          q: '「わたし」が 外国《がいこく》を 旅行《りょこう》したいのは なぜですか。',
          choices: [
            'いろいろな 文化《ぶんか》を 知《し》りたいから',
            '仕事《しごと》を 休《やす》みたいから',
            '両親《りょうしん》と 話《はな》したいから',
            '犬《いぬ》に 会《あ》いたいから',
          ],
          answer: 0,
          explain: { id: 'Paragraf 6: いろいろな 文化を 知りたいからだ (〜からだ = alasan).', en: 'Paragraph 6: because I want to learn about many cultures (〜からだ = reason).' },
        },
      ],
      challenge: {
        id: 'Tulis tentang perbedaan budaya yang kamu tahu. Pakai 〜によって、〜まま、〜からだ.',
        en: 'Write about a cultural difference you know. Use 〜によって, 〜まま, 〜からだ.',
      },
    },
    9: {
      title: { id: 'Penemuan tukang roti', en: 'The baker’s invention' },
      focus: {
        id: 'お〜です（hormat）、〜ても かまわない、〜ほど〜ない、〜ほど〜は ない、〜ため（に）、〜たら／〜ば〜た（pengandaian yang tidak terjadi）',
        en: 'お〜です (honorific), 〜ても かまわない, 〜ほど〜ない, 〜ほど〜は ない, 〜ため(に), 〜たら／〜ば〜た (counterfactual)',
      },
      before: {
        id: 'Pernahkah kamu punya ide yang bagus tapi tidak kamu lindungi atau kembangkan? Apa yang terjadi?',
        en: 'Have you ever had a good idea you did not protect or develop? What happened?',
      },
      passage: {
        title: 'パン屋《や》の 発明《はつめい》',
        paragraphs: [
          {
            ja: '駅前《えきまえ》の パン屋《や》の 主人《しゅじん》は、発明《はつめい》が 趣味《しゅみ》だ。「お客様《きゃくさま》、何《なに》を お探《さが》しですか」と 声《こえ》を かけながら、いつも 新《あたら》しい 道具《どうぐ》の ことを 考《かんが》えて いる。',
            id: 'Pemilik toko roti di depan stasiun hobi menemukan hal baru. Sambil menyapa, “Pelanggan, sedang mencari apa?”, ia selalu memikirkan alat baru.',
            en: 'The owner of the bakery in front of the station loves inventing. While greeting customers with “What are you looking for?”, he is always thinking about new tools.',
          },
          {
            ja: '五年前《ごねんまえ》、パンを きれいに 切《き》れる ナイフを 作《つく》った。家《いえ》で 使《つか》う だけの つもりだったが、友人《ゆうじん》に 見《み》せたら、とても 喜《よろこ》ばれた。',
            id: 'Lima tahun lalu ia membuat pisau yang bisa memotong roti dengan rapi. Awalnya hanya untuk dipakai di rumah, tetapi ketika diperlihatkan kepada teman, ia sangat disukai.',
            en: 'Five years ago he made a knife that cuts bread cleanly. He meant it only for home use, but when he showed it to a friend, it was very well received.',
          },
          {
            ja: 'ところが、特許《とっきょ》を 取《と》って おかなかった ために、大《おお》きな 会社《かいしゃ》に 同《おな》じ ものを 売《う》られて しまった。',
            id: 'Namun, karena ia tidak mengurus paten sebelumnya, perusahaan besar menjual barang yang sama.',
            en: 'However, because he had not taken out a patent, a big company ended up selling the same thing.',
          },
          {
            ja: '主人《しゅじん》は 「特許《とっきょ》を 取《と》っていたら、今《いま》ごろ 店《みせ》を 二《ふた》つに 増《ふ》やせたのに」と 笑《わら》う。でも、大会社《だいがいしゃ》の ナイフは 主人《しゅじん》のほど 使《つか》いやすくは ないそうだ。',
            id: 'Si pemilik tertawa, “Kalau dulu aku mengurus paten, sekarang tokoku sudah jadi dua.” Namun pisau perusahaan besar itu katanya tidak semudah dipakai seperti buatannya.',
            en: 'The owner laughs, “If I had gotten a patent, I could have grown to two shops by now.” But the big company’s knife is said to be not as easy to use as his.',
          },
          {
            ja: '「発明《はつめい》ほど 楽《たの》しい ことは ない」と 主人《しゅじん》は 言《い》う。お客様《きゃくさま》が 少《すこ》しぐらい 値段《ねだん》が 高《たか》くても かまわないと 言《い》って、今《いま》も 注文《ちゅうもん》を して くれる。',
            id: '“Tidak ada yang semenyenangkan menemukan sesuatu,” kata pemilik. Pelanggan berkata tidak apa-apa meski harganya agak mahal, dan sampai sekarang masih memesan.',
            en: '“Nothing is as much fun as inventing,” the owner says. Customers say they don’t mind if it costs a little more, and still place orders today.',
          },
        ],
      },
      vocab: [
        { ja: '発明', reading: 'はつめい', id: 'penemuan', en: 'invention' },
        { ja: '特許', reading: 'とっきょ', id: 'paten', en: 'patent' },
        { ja: '主人', reading: 'しゅじん', id: 'pemilik, tuan', en: 'owner, master' },
        { ja: '道具', reading: 'どうぐ', id: 'alat', en: 'tool' },
        { ja: '増やす', reading: 'ふやす', id: 'menambah', en: 'to increase' },
        { ja: '注文', reading: 'ちゅうもん', id: 'pesanan', en: 'order' },
        { ja: '趣味', reading: 'しゅみ', id: 'hobi', en: 'hobby' },
        { ja: '喜ぶ', reading: 'よろこぶ', id: 'senang', en: 'to be pleased' },
      ],
      questions: [
        {
          type: 'mc',
          q: 'パン屋《や》の 主人《しゅじん》の 趣味《しゅみ》は 何《なん》ですか。',
          choices: ['発明《はつめい》', '旅行《りょこう》', '料理《りょうり》', 'スポーツ'],
          answer: 0,
          explain: { id: 'Paragraf 1: 発明が 趣味だ.', en: 'Paragraph 1: inventing is his hobby.' },
        },
        {
          type: 'mc',
          q: '主人《しゅじん》が 作《つく》った ものは 何《なん》ですか。',
          choices: [
            'パンを きれいに 切《き》れる ナイフ',
            '焼《や》き やすい オーブン',
            'パンを 運《はこ》ぶ 車《くるま》',
            '新《あたら》しい 種類《しゅるい》の パン',
          ],
          answer: 0,
          explain: { id: 'Paragraf 2: パンを きれいに 切れる ナイフ.', en: 'Paragraph 2: a knife that cuts bread cleanly.' },
        },
        {
          type: 'mc',
          q: '大《おお》きな 会社《かいしゃ》が 同《おな》じ ものを 売《う》ったのは、なぜですか。',
          choices: [
            '主人《しゅじん》が 特許《とっきょ》を 取《と》って おかなかったから',
            '主人《しゅじん》が 売《う》るのを 頼《たの》んだから',
            '友人《ゆうじん》が 宣伝《せんでん》したから',
            'ナイフが 安《やす》かったから',
          ],
          answer: 0,
          explain: { id: 'Paragraf 3: 特許を 取って おかなかった ために (〜ために = sebab).', en: 'Paragraph 3: because he had not obtained a patent (〜ために = cause).' },
        },
        {
          type: 'tf',
          q: '主人《しゅじん》は 特許《とっきょ》を 取《と》って いる。',
          answer: false,
          explain: { id: 'Paragraf 3-4: 取っていたら…のに — ia tidak mengurusnya.', en: 'Paragraphs 3–4: “if I had taken it…” — he did not.' },
        },
        {
          type: 'mc',
          q: '「特許《とっきょ》を 取《と》っていたら、〜増《ふ》やせたのに」は どういう 意味《いみ》ですか。',
          choices: [
            '実際《じっさい》には 取《と》らなかったので、増《ふ》やせなかった',
            '特許《とっきょ》を 取《と》ったので、増《ふ》やせた',
            'これから 取《と》る つもりだ',
            '増《ふ》やす 必要《ひつよう》は ない',
          ],
          answer: 0,
          explain: { id: '〜たら〜のに = pengandaian yang tidak terjadi: kenyataannya tidak.', en: '〜たら〜のに = counterfactual: in reality he didn’t.' },
        },
        {
          type: 'tf',
          q: '大会社《だいがいしゃ》の ナイフは 主人《しゅじん》のより 使《つか》いやすいそうだ。',
          answer: false,
          explain: { id: 'Paragraf 4: 主人のほど 使いやすくは ない.', en: 'Paragraph 4: not as easy to use as his.' },
        },
        {
          type: 'mc',
          q: 'お客様《きゃくさま》は 今《いま》も どう して くれますか。',
          choices: [
            '少《すこ》し 高《たか》くても 注文《ちゅうもん》して くれる',
            '安《やす》い ものだけ 買《か》って くれる',
            '店《みせ》を 手伝《てつだ》って くれる',
            '特許《とっきょ》を 取《と》って くれる',
          ],
          answer: 0,
          explain: { id: 'Paragraf 5: 高くても かまわないと 言って、注文を して くれる.', en: 'Paragraph 5: say they don’t mind a higher price and keep ordering.' },
        },
      ],
      challenge: {
        id: 'Tulis tentang pengandaian yang tidak terjadi dalam hidupmu. Pakai 〜たら〜のに、〜ほど〜ない、〜ため(に).',
        en: 'Write about something that could have gone differently in your life. Use 〜たら〜のに, 〜ほど〜ない, 〜ため(に).',
      },
    },
    10: {
      title: { id: 'Kesalahan kecil di pagi hari', en: 'Small mistakes in the morning' },
      focus: {
        id: '〜はずだ・〜はずが ない・〜はずだった、〜ことが ある・〜ことも ある、〜た 結果、〜出す、〜始める・〜終わる・〜続ける、〜忘れる・〜合う・〜換える',
        en: '〜はずだ・〜はずが ない・〜はずだった, 〜ことが ある・〜ことも ある, 〜た 結果, 〜出す, 〜始める・〜終わる・〜続ける, 〜忘れる・〜合う・〜換える',
      },
      before: {
        id: 'Kesalahan kecil apa yang sering kamu lakukan di pagi hari? Bagaimana kamu mencegahnya?',
        en: 'What small mistakes do you often make in the morning? How do you prevent them?',
      },
      passage: {
        title: '朝《あさ》の うっかり',
        paragraphs: [
          {
            ja: '今朝《けさ》、家《いえ》を 出《で》ようと したら、かぎが ない。ポケットに 入《い》れた はずなのに、どこにも ない。探《さが》し 始《はじ》めて 十分《じゅっぷん》、結局《けっきょく》 かばんの 中《なか》から 出《で》て きた。',
            id: 'Pagi ini, saat hendak keluar rumah, kunci tidak ada. Padahal seharusnya sudah kumasukkan ke saku, tetapi tidak ketemu di mana pun. Setelah sepuluh menit mencari, akhirnya kunci keluar dari dalam tas.',
            en: 'This morning, as I tried to leave the house, my key was missing. I should have put it in my pocket, but it was nowhere. After ten minutes of searching, it finally turned up inside my bag.',
          },
          {
            ja: 'こんな ことは めったに ないが、たまに 忘《わす》れ物《もの》を する ことが ある。',
            id: 'Hal seperti ini jarang terjadi, tetapi sesekali saya memang suka melupakan barang.',
            en: 'This rarely happens, but now and then I do forget things.',
          },
          {
            ja: '駅《えき》で 電車《でんしゃ》に 乗《の》ったら、急《きゅう》に 雨《あめ》が 降《ふ》り出《だ》した。かさを 持《も》って きた はずなのに、電車《でんしゃ》の 中《なか》に 置《お》き忘《わす》れて いた。',
            id: 'Ketika naik kereta di stasiun, tiba-tiba hujan mulai turun. Seharusnya saya membawa payung, tetapi ternyata tertinggal di kereta.',
            en: 'When I got on the train at the station, rain suddenly began to fall. I was sure I had brought an umbrella, but I had left it behind on the train.',
          },
          {
            ja: '同僚《どうりょう》に 話《はな》したら、「そんな はずは ない。いつも しっかりして いる 君《きみ》が」と 笑《わら》われた。',
            id: 'Ketika saya ceritakan pada rekan kerja, ia tertawa, “Mana mungkin. Orang yang selalu teliti seperti kamu.”',
            en: 'When I told a colleague, he laughed, “That can’t be. Not you, who is always so reliable.”',
          },
          {
            ja: '失敗《しっぱい》の 原因《げんいん》を 考《かんが》えた 結果《けっか》、朝《あさ》は 時間《じかん》が なくて、確認《かくにん》しないで 出《で》かけて いる ことに 気《き》が ついた。',
            id: 'Setelah memikirkan penyebab kesalahan, saya menyadari bahwa di pagi hari saya tidak punya waktu dan pergi tanpa memeriksa.',
            en: 'After thinking about the cause, I realized that in the morning I am short of time and leave without checking.',
          },
          {
            ja: 'これからは、前《まえ》の 晩《ばん》に 荷物《にもつ》を 準備《じゅんび》し 終《お》わって から 寝《ね》る ことに した。続《つづ》けて みると、朝《あさ》の あわてる ことも 減《へ》った。',
            id: 'Mulai sekarang, saya memutuskan tidur setelah selesai menyiapkan barang pada malam sebelumnya. Setelah dicoba terus, kejadian panik di pagi hari pun berkurang.',
            en: 'From now on I decided to finish preparing my things the night before and then sleep. Having kept it up, my morning panics have decreased.',
          },
        ],
      },
      vocab: [
        { ja: '今朝', reading: 'けさ', id: 'pagi ini', en: 'this morning' },
        { ja: '結局', reading: 'けっきょく', id: 'pada akhirnya', en: 'in the end' },
        { ja: '忘れ物', reading: 'わすれもの', id: 'barang tertinggal', en: 'forgotten item' },
        { ja: '置き忘れる', reading: 'おきわすれる', id: 'meninggalkan lupa', en: 'to leave behind' },
        { ja: '原因', reading: 'げんいん', id: 'penyebab', en: 'cause' },
        { ja: '確認', reading: 'かくにん', id: 'pengecekan', en: 'confirmation, checking' },
        { ja: '減る', reading: 'へる', id: 'berkurang', en: 'to decrease' },
        { ja: '準備', reading: 'じゅんび', id: 'persiapan', en: 'preparation' },
      ],
      questions: [
        {
          type: 'mc',
          q: '今朝《けさ》、かぎは どこに ありましたか。',
          choices: ['かばんの 中《なか》', 'ポケットの 中《なか》', '電車《でんしゃ》の 中《なか》', '会社《かいしゃ》'],
          answer: 0,
          explain: { id: 'Paragraf 1: 結局 かばんの 中から 出て きた.', en: 'Paragraph 1: it turned up in the bag.' },
        },
        {
          type: 'mc',
          q: '「ポケットに 入《い》れた はずなのに」は どういう 意味《いみ》ですか。',
          choices: [
            '入《い》れた と 思《おも》って いたが、違《ちが》った',
            '入《い》れる つもりは なかった',
            '入《い》れなくても かまわなかった',
            '入《い》れたので 安心《あんしん》だった',
          ],
          answer: 0,
          explain: { id: '〜はずなのに = seharusnya begitu, tetapi kenyataan berbeda.', en: '〜はずなのに = it should have been so, but reality differs.' },
        },
        {
          type: 'tf',
          q: 'こんな 失敗《しっぱい》は 毎日《まいにち》 する。',
          answer: false,
          explain: { id: 'Paragraf 2: めったに ない が、たまに ある.', en: 'Paragraph 2: rare, but sometimes happens.' },
        },
        {
          type: 'mc',
          q: '電車《でんしゃ》に 乗《の》ったあと、何《なに》が 起《お》きましたか。',
          choices: ['雨《あめ》が 降《ふ》り出《だ》した', '電車《でんしゃ》が 止《と》まった', 'かぎを なくした', '友達《ともだち》に 会《あ》った'],
          answer: 0,
          explain: { id: 'Paragraf 3: 急に 雨が 降り出した (〜出す = mulai tiba-tiba).', en: 'Paragraph 3: rain suddenly started (〜出す = start suddenly).' },
        },
        {
          type: 'mc',
          q: '「そんな はずは ない」と 言《い》った のは、なぜですか。',
          choices: [
            '「わたし」は いつも しっかりして いると 思《おも》って いたから',
            '「わたし」が うそを ついたから',
            '雨《あめ》が 降《ふ》らなかったから',
            'かさが 高《たか》かったから',
          ],
          answer: 0,
          explain: { id: 'Paragraf 4: いつも しっかりして いる 君が.', en: 'Paragraph 4: “you, who are always so reliable.”' },
        },
        {
          type: 'mc',
          q: '考《かんが》えた 結果《けっか》、失敗《しっぱい》の 原因《げんいん》は 何《なん》でしたか。',
          choices: [
            '朝《あさ》は 時間《じかん》が なくて、確認《かくにん》しないで 出《で》かけて いた こと',
            '会社《かいしゃ》が 遠《とお》い こと',
            '電車《でんしゃ》が 混《こ》んで いる こと',
            '同僚《どうりょう》が 話《はな》しかけた こと',
          ],
          answer: 0,
          explain: { id: 'Paragraf 5: 時間が なくて、確認しないで 出かけて いる.', en: 'Paragraph 5: no time, leaving without checking.' },
        },
        {
          type: 'tf',
          q: '「わたし」は 前《まえ》の 晩《ばん》に 荷物《にもつ》を 準備《じゅんび》する ことに した。',
          answer: true,
          explain: { id: 'Paragraf 6: 前の 晩に 荷物を 準備し 終わって から 寝る ことに した.', en: 'Paragraph 6: decided to finish preparing the night before.' },
        },
      ],
      challenge: {
        id: 'Tulis kesalahan kecilmu dan penyebabnya. Pakai 〜はずだった、〜た 結果、〜忘れる、〜続ける.',
        en: 'Write about a small mistake of yours and its cause. Use 〜はずだった, 〜た 結果, 〜忘れる, 〜続ける.',
      },
    },
    11: {
      title: { id: 'Desa tua di pegunungan', en: 'An old village in the mountains' },
      focus: {
        id: '〜てくる・〜ていく、〜たら どう？、〜より 〜ほうが、〜らしい（kabar・khas）、〜として、〜ず（に）、〜て いる（keadaan・pengalaman）',
        en: '〜てくる・〜ていく, 〜たら どう?, 〜より 〜ほうが, 〜らしい (hearsay・typical), 〜として, 〜ず（に）, 〜て いる (state・experience)',
      },
      before: {
        id: 'Adakah tempat bersejarah atau warisan dunia yang pernah kamu kunjungi? Apa kesanmu?',
        en: 'Have you visited a historic place or World Heritage site? What was your impression?',
      },
      passage: {
        title: '山《やま》の 中《なか》の 古《ふる》い 村《むら》',
        paragraphs: [
          {
            ja: '先月《せんげつ》、友達《ともだち》に 勧《すす》められて、山《やま》の 中《なか》の 古《ふる》い 村《むら》へ 行《い》って みた。世界遺産《せかいいさん》として 登録《とうろく》されて いる 有名《ゆうめい》な 場所《ばしょ》だ。',
            id: 'Bulan lalu, atas anjuran teman, saya mencoba pergi ke sebuah desa tua di tengah pegunungan. Tempat itu terkenal dan terdaftar sebagai warisan dunia.',
            en: 'Last month, on a friend’s recommendation, I went to an old village in the mountains. It is a famous place registered as a World Heritage site.',
          },
          {
            ja: '友達《ともだち》の 話《はなし》では、冬《ふゆ》は 雪《ゆき》が 深《ふか》く 積《つ》もるので、屋根《やね》は 重《おも》い 雪《ゆき》でも 壊《こわ》れにくい ように 作《つく》られて いるらしい。',
            id: 'Menurut cerita teman, salju menumpuk tebal pada musim dingin, sehingga atap tampaknya dibuat agar tidak mudah rusak meski tertimpa salju berat.',
            en: 'According to my friend, deep snow piles up in winter, so the roofs are apparently built so that they do not break easily even under heavy snow.',
          },
          {
            ja: '駅《えき》から バスで 一時間《いちじかん》。バスが 山道《やまみち》を 登《のぼ》って いくと、景色《けしき》は だんだん 田舎《いなか》らしく なって きた。',
            id: 'Satu jam naik bus dari stasiun. Saat bus mendaki jalan pegunungan, pemandangan lambat laun menjadi khas pedesaan.',
            en: 'It was an hour by bus from the station. As the bus climbed the mountain road, the scenery gradually became more countryside-like.',
          },
          {
            ja: '村《むら》に 着《つ》くと、同《おな》じ 形《かたち》の 大《おお》きな 屋根《やね》の 家《いえ》が 並《なら》んで いた。「手《て》を 合《あ》わせたような 形《かたち》だ」と 思《おも》った。',
            id: 'Sesampainya di desa, rumah-rumah beratap besar dengan bentuk yang sama berjajar. Saya berpikir, “Bentuknya seperti telapak tangan yang dikatupkan.”',
            en: 'When I arrived in the village, houses with big roofs of the same shape stood in a row. I thought, “The shape looks like hands pressed together.”',
          },
          {
            ja: '昔《むかし》は、一階《いっかい》で 家族《かぞく》が 暮《く》らし、上《うえ》の 階《かい》では 蚕《かいこ》を 飼《か》って いたそうだ。この 辺《あた》りは 米《こめ》が あまり とれないので、家《いえ》の 中《なか》で できる 仕事《しごと》が 大切《たいせつ》だったのだろう。',
            id: 'Konon dahulu keluarga tinggal di lantai satu, sedangkan di lantai atas dipelihara ulat sutra. Karena di sekitar sini padi tidak banyak dihasilkan, pekerjaan yang bisa dilakukan di dalam rumah mungkin penting.',
            en: 'In the old days, the family lived on the first floor and silkworms were raised upstairs, they say. Rice does not grow well around here, so work that could be done indoors was probably important.',
          },
          {
            ja: '村《むら》の おじいさんは 「釘《くぎ》を 使《つか》わずに 屋根《やね》を 作《つく》って いる。みんなで 協力《きょうりょく》して きたから、百年《ひゃくねん》以上《いじょう》 壊《こわ》れずに 残《のこ》って いる」と 話《はな》して くれた。',
            id: 'Seorang kakek di desa bercerita, “Atap dibuat tanpa memakai paku. Karena semua orang bekerja sama, atap itu bertahan lebih dari seratus tahun tanpa rusak.”',
            en: 'An old man in the village told me, “The roofs are built without using nails. Because everyone has cooperated, they have remained standing for over a hundred years without breaking.”',
          },
          {
            ja: '帰《かえ》りに、友達《ともだち》は 「来年《らいねん》の 冬《ふゆ》に また 来《き》たら どう？　夏《なつ》より 雪《ゆき》の 季節《きせつ》の ほうが きれいだよ」と 言《い》った。ここへ 来《く》る 人《ひと》は これから 増《ふ》えて いくだろうが、静《しず》かな うちに また 行《い》きたい。',
            id: 'Saat pulang, teman saya berkata, “Bagaimana kalau datang lagi musim dingin tahun depan? Musim salju lebih indah daripada musim panas.” Orang yang datang ke sini mungkin akan terus bertambah, tetapi saya ingin kembali selagi masih tenang.',
            en: 'On the way back, my friend said, “Why not come again next winter? The snowy season is more beautiful than summer.” The number of visitors will probably keep growing, but I want to go again while it is still quiet.',
          },
        ],
      },
      vocab: [
        { ja: '世界遺産', reading: 'せかいいさん', id: 'warisan dunia', en: 'World Heritage site' },
        { ja: '登録する', reading: 'とうろくする', id: 'mendaftarkan', en: 'to register' },
        { ja: '屋根', reading: 'やね', id: 'atap', en: 'roof' },
        { ja: '協力する', reading: 'きょうりょくする', id: 'bekerja sama', en: 'to cooperate' },
        { ja: '壊れる', reading: 'こわれる', id: 'rusak, runtuh', en: 'to break' },
        { ja: '景色', reading: 'けしき', id: 'pemandangan', en: 'scenery' },
        { ja: '勧める', reading: 'すすめる', id: 'menganjurkan', en: 'to recommend' },
      ],
      questions: [
        {
          type: 'mc',
          q: '筆者《ひっしゃ》は どうして この 村《むら》へ 行《い》きましたか。',
          choices: ['友達《ともだち》に 勧《すす》められたから', '仕事《しごと》だったから', '家《いえ》が あるから', '学校《がっこう》の 旅行《りょこう》だったから'],
          answer: 0,
          explain: { id: 'Paragraf 1: 友達に 勧められて 行って みた.', en: 'Paragraph 1: went on a friend’s recommendation.' },
        },
        {
          type: 'mc',
          q: 'この 村《むら》は 何《なに》として 登録《とうろく》されて いますか。',
          choices: ['世界遺産《せかいいさん》', '国立公園《こくりつこうえん》', '温泉地《おんせんち》', '博物館《はくぶつかん》'],
          answer: 0,
          explain: { id: 'Paragraf 1: 世界遺産として 登録されて いる.', en: 'Paragraph 1: registered as a World Heritage site.' },
        },
        {
          type: 'tf',
          q: '屋根《やね》は 重《おも》い 雪《ゆき》でも 壊《こわ》れにくい ように 作《つく》られて いる らしい。',
          answer: true,
          explain: { id: 'Paragraf 2: 友達の 話では そう 作られて いる らしい.', en: 'Paragraph 2: according to the friend, it is apparently built that way.' },
        },
        {
          type: 'mc',
          q: 'バスが 山道《やまみち》を 登《のぼ》ると、景色《けしき》は どう なって きましたか。',
          choices: ['田舎《いなか》らしく なって きた', '都会《とかい》らしく なって きた', '海《うみ》らしく なって きた', '工場《こうじょう》らしく なって きた'],
          answer: 0,
          explain: { id: 'Paragraf 3: だんだん 田舎らしく なって きた.', en: 'Paragraph 3: it gradually became more countryside-like.' },
        },
        {
          type: 'mc',
          q: 'おじいさんに よると、家《いえ》は どうして 長《なが》く 残《のこ》って いますか。',
          choices: ['釘《くぎ》を 使《つか》わず、みんなで 協力《きょうりょく》して きたから', '新《あたら》しく 作《つく》り 替《か》えたから', '雪《ゆき》が 降《ふ》らないから', 'お金《かね》が たくさん あったから'],
          answer: 0,
          explain: { id: 'Paragraf 6: 釘を 使わずに 作り、みんなで 協力して きた.', en: 'Paragraph 6: built without nails and kept up through everyone’s cooperation.' },
        },
        {
          type: 'tf',
          q: '友達《ともだち》は 冬《ふゆ》より 夏《なつ》の ほうが きれいだと 言《い》った。',
          answer: false,
          explain: { id: 'Paragraf 7: 夏より 雪の 季節の ほうが きれい.', en: 'Paragraph 7: the snowy season is more beautiful than summer.' },
        },
        {
          type: 'mc',
          q: '筆者《ひっしゃ》は これから どう したいと 思《おも》って いますか。',
          choices: ['静《しず》かな うちに また 行《い》きたい', 'もう 二度《にど》と 行《い》かない', 'この 村《むら》に 家《いえ》を 建《た》てたい', '夏《なつ》だけ 行《い》きたい'],
          answer: 0,
          explain: { id: 'Paragraf 7: 静かな うちに また 行きたい.', en: 'Paragraph 7: wants to go again while it is still quiet.' },
        },
      ],
      challenge: {
        id: 'Ceritakan tempat wisata yang pernah kamu kunjungi. Pakai 〜として、〜らしい、〜より 〜ほうが、〜ず（に）.',
        en: 'Describe a tourist spot you have visited. Use 〜として, 〜らしい, 〜より 〜ほうが, 〜ず（に）.',
      },
    },
    12: {
      title: { id: 'Suara dari kamar sebelah', en: 'Noise from next door' },
      focus: {
        id: '〜もの／もんだから、〜（ら）れる（受身 yang merugikan）、〜たり 〜たり、〜っぱなし、〜おかげで・〜せいで',
        en: '〜もの／もんだから, 〜（ら）れる (adversative passive), 〜たり 〜たり, 〜っぱなし, 〜おかげで・〜せいで',
      },
      before: {
        id: 'Pernahkah kamu terganggu oleh suara tetangga, atau menyadari bahwa kamu mengganggu orang lain?',
        en: 'Have you ever been bothered by a neighbor’s noise, or realized you were bothering others?',
      },
      passage: {
        title: '隣《となり》の 部屋《へや》の 音《おと》',
        paragraphs: [
          {
            ja: '先月《せんげつ》 引《ひ》っ越《こ》して きた アパートは、駅《えき》に 近《ちか》くて 便利《べんり》だが、壁《かべ》が うすい せいで、音《おと》が よく 聞《き》こえる。',
            id: 'Apartemen yang saya tempati sejak bulan lalu dekat stasiun dan praktis, tetapi karena dindingnya tipis, suara terdengar jelas.',
            en: 'The apartment I moved into last month is close to the station and convenient, but the walls are thin, so sounds carry easily.',
          },
          {
            ja: 'ある 晩《ばん》、雨《あめ》に 降《ふ》られて ぬれて 帰《かえ》ると、管理人《かんりにん》さんに 呼《よ》び 止《と》められた。「下《した》の 部屋《へや》の 人《ひと》から 苦情《くじょう》が あったんです」と 言《い》われた。',
            id: 'Suatu malam, saat pulang dalam keadaan basah karena kehujanan, saya dipanggil oleh pengelola apartemen. Beliau berkata, “Ada keluhan dari penghuni kamar di bawah.”',
            en: 'One evening, as I came home soaked after being caught in the rain, the building manager stopped me. “There was a complaint from the person downstairs,” he said.',
          },
          {
            ja: '「夜遅《よるおそ》くまで テレビが つけっぱなしに なって いるし、洗濯機《せんたくき》の 音《おと》も 聞《き》こえるそうです」。',
            id: '“Televisi dibiarkan menyala sampai larut malam, dan katanya suara mesin cuci juga terdengar.”',
            en: '“The TV is left on until late at night, and they say they can hear the washing machine too.”',
          },
          {
            ja: 'わたしは 「仕事《しごと》が 忙《いそが》しくて 帰《かえ》りが 遅《おそ》い もんですから、つい…」と 説明《せつめい》して、翌日《よくじつ》 下《した》の 部屋《へや》へ 謝《あやま》りに 行《い》った。',
            id: 'Saya menjelaskan, “Karena pekerjaan sibuk dan pulang larut, jadi tanpa sadar…”, lalu keesokan harinya pergi meminta maaf ke kamar di bawah.',
            en: 'I explained, “Because work is busy and I get home late, I just…” and the next day I went to apologize at the room downstairs.',
          },
          {
            ja: '下《した》の 部屋《へや》の 人《ひと》は 「それは わかりますけど、子《こ》どもが 寝《ね》て いる 時間《じかん》に 洗濯《せんたく》されると、ちょっと…」と 言《い》った。',
            id: 'Penghuni kamar di bawah berkata, “Saya mengerti, tetapi kalau mencuci pada jam anak saya tidur, agak…”',
            en: 'The person downstairs said, “I understand that, but when laundry is done while my child is sleeping, it’s a bit…”',
          },
          {
            ja: '話《はな》し 合《あ》った 結果《けっか》、夜《よる》の 洗濯《せんたく》は やめて、休《やす》みの 日《ひ》に まとめて する ように した。テレビも 消《け》して 寝《ね》る。その おかげで、下《した》の 人《ひと》に 会《あ》っても 気持《きも》ち よく 挨拶《あいさつ》できる ように なった。',
            id: 'Setelah berbincang, saya berhenti mencuci pada malam hari dan mencuci sekaligus pada hari libur. TV juga saya matikan sebelum tidur. Berkat itu, saya bisa menyapa penghuni di bawah dengan nyaman ketika bertemu.',
            en: 'After talking it over, I stopped doing laundry at night and now do it all at once on my days off. I also turn off the TV before sleeping. Thanks to that, I can greet the person downstairs comfortably when we meet.',
          },
          {
            ja: '今《いま》は、夜《よる》に 掃除《そうじ》を したり、本《ほん》を 読《よ》んだり して、静《しず》かに 過《す》ごして いる。',
            id: 'Sekarang pada malam hari saya membersihkan rumah atau membaca buku, menghabiskan waktu dengan tenang.',
            en: 'Now in the evenings I do things like tidying up or reading, and spend the time quietly.',
          },
        ],
      },
      vocab: [
        { ja: '苦情', reading: 'くじょう', id: 'keluhan', en: 'complaint' },
        { ja: '洗濯', reading: 'せんたく', id: 'mencuci pakaian', en: 'laundry' },
        { ja: '壁', reading: 'かべ', id: 'dinding', en: 'wall' },
        { ja: '管理人', reading: 'かんりにん', id: 'pengelola (gedung)', en: 'building manager' },
        { ja: '謝る', reading: 'あやまる', id: 'meminta maaf', en: 'to apologize' },
        { ja: '解決する', reading: 'かいけつする', id: 'menyelesaikan', en: 'to resolve' },
        { ja: '翌日', reading: 'よくじつ', id: 'keesokan harinya', en: 'the next day' },
      ],
      questions: [
        {
          type: 'mc',
          q: 'この アパートの 問題《もんだい》は 何《なん》ですか。',
          choices: ['壁《かべ》が うすくて 音《おと》が 聞《き》こえる こと', '駅《えき》から 遠《とお》い こと', '家賃《やちん》が 高《たか》い こと', '部屋《へや》が 狭《せま》い こと'],
          answer: 0,
          explain: { id: 'Paragraf 1: 壁が うすい せいで、音が よく 聞こえる.', en: 'Paragraph 1: the walls are thin, so sounds carry.' },
        },
        {
          type: 'mc',
          q: '管理人《かんりにん》さんは 何《なに》に ついて 話《はな》しましたか。',
          choices: ['下《した》の 人《ひと》の 苦情《くじょう》', '引《ひ》っ越《こ》しの 日《ひ》', '家賃《やちん》', 'ごみの 出《だ》し 方《かた》'],
          answer: 0,
          explain: { id: 'Paragraf 2: 下の 部屋の 人から 苦情が あった.', en: 'Paragraph 2: a complaint from the person downstairs.' },
        },
        {
          type: 'tf',
          q: '苦情《くじょう》の 一《ひと》つは、テレビが つけっぱなしに なって いる ことだ。',
          answer: true,
          explain: { id: 'Paragraf 3: 夜遅くまで テレビが つけっぱなし.', en: 'Paragraph 3: the TV is left on until late at night.' },
        },
        {
          type: 'mc',
          q: 'わたしは どうして 下《した》の 部屋《へや》へ 謝《あやま》りに 行《い》きましたか。',
          choices: ['夜遅《よるおそ》くの 洗濯《せんたく》などで 迷惑《めいわく》を かけて いたから', '引《ひ》っ越《こ》しの 挨拶《あいさつ》の ためだから', '荷物《にもつ》を 借《か》りたいから', '隣《となり》の 人《ひと》に 頼《たの》まれたから'],
          answer: 0,
          explain: { id: 'Paragraf 3–4: テレビや 洗濯機の 音で 迷惑を かけて いた.', en: 'Paragraphs 3–4: the TV and washing machine noise had caused trouble.' },
        },
        {
          type: 'tf',
          q: '下《した》の 人《ひと》は 洗濯《せんたく》の 音《おと》は 全《まった》く 気《き》に ならないと 言《い》った。',
          answer: false,
          explain: { id: 'Paragraf 5: 子どもが 寝て いる 時間に 洗濯されると ちょっと…', en: 'Paragraph 5: laundry while the child sleeps is a problem.' },
        },
        {
          type: 'mc',
          q: '問題《もんだい》は どう 解決《かいけつ》しましたか。',
          choices: ['洗濯《せんたく》を 休《やす》みの 日《ひ》に まとめて する ように なった', '別《べつ》の アパートへ 引《ひ》っ越《こ》した', '洗濯機《せんたくき》を 売《う》った', '管理人《かんりにん》さんが 注意《ちゅうい》しなく なった'],
          answer: 0,
          explain: { id: 'Paragraf 6: 夜の 洗濯を やめて、休みの 日に まとめて する.', en: 'Paragraph 6: stopped night laundry and does it on days off.' },
        },
        {
          type: 'mc',
          q: '今《いま》、夜《よる》は どう 過《す》ごして いますか。',
          choices: ['掃除《そうじ》や 読書《どくしょ》を して 静《しず》かに 過《す》ごして いる', '友達《ともだち》と 騒《さわ》いで いる', 'テレビを つけたまま 寝《ね》て いる', '夜中《よなか》に 洗濯《せんたく》して いる'],
          answer: 0,
          explain: { id: 'Paragraf 7: 掃除を したり、本を 読んだり して、静かに 過ごして いる.', en: 'Paragraph 7: tidies up and reads, spending the time quietly.' },
        },
      ],
      challenge: {
        id: 'Tulis kejadian ketika kamu mengganggu atau diganggu orang lain, lalu bagaimana menyelesaikannya. Pakai 〜もんだから、〜（ら）れて、〜っぱなし、〜おかげで／せいで.',
        en: 'Write about a time you bothered someone or were bothered, and how it was resolved. Use 〜もんだから, 〜（ら）れて, 〜っぱなし, 〜おかげで／せいで.',
      },
    },
  },
}
