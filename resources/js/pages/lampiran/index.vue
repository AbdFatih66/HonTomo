<script setup>
// Halaman referensi tata bahasa N5 statis (angka, ungkapan waktu, kata
// bantu bilangan, konjugasi kata kerja, kata sifat). Tidak memakai backend —
// semua data ditulis langsung di sini, mengikuti pola halaman kana/kanji
// yang berdiri sendiri dari sistem `lessons`.
const { t } = useI18n()

const section = ref('numbers')
const SECTIONS = [
  { value: 'numbers', label: 'lampiran.section_numbers', icon: 'tabler-123' },
  { value: 'time', label: 'lampiran.section_time', icon: 'tabler-calendar-time' },
  { value: 'clock', label: 'lampiran.section_clock', icon: 'tabler-clock' },
  { value: 'counters', label: 'lampiran.section_counters', icon: 'tabler-list-numbers' },
  { value: 'verbs', label: 'lampiran.section_verbs', icon: 'tabler-abc' },
  { value: 'adjectives', label: 'lampiran.section_adjectives', icon: 'tabler-palette' },
]

// ---------------------------------------------------------------------
// I. Kata Bilangan
// ---------------------------------------------------------------------
const onesReading = ['ゼロ、れい', 'いち', 'に', 'さん', 'よん、し', 'ご', 'ろく', 'なな、しち', 'はち', 'きゅう、く']
const tensReading = ['じゅう', 'にじゅう', 'さんじゅう', 'よんじゅう', 'ごじゅう', 'ろくじゅう', 'ななじゅう、しちじゅう', 'はちじゅう', 'きゅうじゅう']
const hundredsReading = ['ひゃく', 'にひゃく', 'さんびゃく', 'よんひゃく', 'ごひゃく', 'ろっぴゃく', 'ななひゃく', 'はっぴゃく', 'きゅうひゃく']
const thousandsReading = ['せん', 'にせん', 'さんぜん', 'よんせん', 'ごせん', 'ろくせん', 'ななせん', 'はっせん', 'きゅうせん']

const numbersOnes = onesReading.map((r, i) => ({ n: i, r }))
const numbersTens = [{ n: 10, r: tensReading[0] }, ...tensReading.slice(1).map((r, i) => ({ n: (i + 2) * 10, r }))]
const numbersHundreds = hundredsReading.map((r, i) => ({ n: (i + 1) * 100, r }))
const numbersThousands = thousandsReading.map((r, i) => ({ n: (i + 1) * 1000, r }))
const numbersBig = [
  { n: '10,000', r: 'いちまん' },
  { n: '100,000', r: 'じゅうまん' },
  { n: '1,000,000', r: 'ひゃくまん' },
  { n: '10,000,000', r: 'せんまん' },
  { n: '100,000,000', r: 'いちおく' },
]
const numbersDecimalFraction = [
  { n: '17.5', r: 'じゅうななてんご' },
  { n: '0.83', r: 'れいてんはちさん' },
  { n: '1/2', r: 'にぶんの いち' },
  { n: '3/4', r: 'よんぶんの さん' },
]

// ---------------------------------------------------------------------
// II & III. Ungkapan waktu
// ---------------------------------------------------------------------
const relativeDays = [
  { id: 'おととい', label: 'kemarin lusa', hari: 'おととい', pagi: 'おとといの あさ', malam: 'おとといの ばん（よる）' },
  { id: 'きのう', label: 'kemarin', hari: 'きのう', pagi: 'きのうの あさ', malam: 'きのうの ばん（よる）' },
  { id: 'きょう', label: 'hari ini', hari: 'きょう', pagi: 'けさ', malam: 'こんばん（きょうの よる）' },
  { id: 'あした', label: 'besok', hari: 'あした', pagi: 'あしたの あさ', malam: 'あしたの ばん（よる）' },
  { id: 'あさって', label: 'lusa', hari: 'あさって', pagi: 'あさっての あさ', malam: 'あさっての ばん（よる）' },
  { id: 'まいにち', label: 'setiap hari', hari: 'まいにち', pagi: 'まいあさ', malam: 'まいばん' },
]
const relativePeriods = [
  { id: 'dua-x-lalu', label: 'dua ... yang lalu', minggu: 'せんせんしゅう（にしゅうかんまえ）', bulan: 'せんせんげつ（にかげつまえ）', tahun: 'おととし' },
  { id: 'x-lalu', label: '... lalu', minggu: 'せんしゅう', bulan: 'せんげつ', tahun: 'きょねん' },
  { id: 'x-ini', label: '... ini', minggu: 'こんしゅう', bulan: 'こんげつ', tahun: 'ことし' },
  { id: 'x-depan', label: '... depan', minggu: 'らいしゅう', bulan: 'らいげつ', tahun: 'らいねん' },
  { id: 'dua-x-lagi', label: 'dua ... lagi', minggu: 'さらいしゅう', bulan: 'さらいげつ', tahun: 'さらいねん' },
  { id: 'setiap-x', label: 'setiap ...', minggu: 'まいしゅう', bulan: 'まいつき', tahun: 'まいとし、まいねん' },
]

const jamPoint = [
  { n: 1, r: 'いちじ' }, { n: 2, r: 'にじ' }, { n: 3, r: 'さんじ' }, { n: 4, r: 'よじ' }, { n: 5, r: 'ごじ' },
  { n: 6, r: 'ろくじ' }, { n: 7, r: 'しちじ' }, { n: 8, r: 'はちじ' }, { n: 9, r: 'くじ' }, { n: 10, r: 'じゅうじ' },
  { n: 11, r: 'じゅういちじ' }, { n: 12, r: 'じゅうにじ' }, { n: '?', r: 'なんじ' },
]
const menitPoint = [
  { n: 1, r: 'いっぷん' }, { n: 2, r: 'にふん' }, { n: 3, r: 'さんぷん' }, { n: 4, r: 'よんぷん' }, { n: 5, r: 'ごふん' },
  { n: 6, r: 'ろっぷん' }, { n: 7, r: 'ななふん' }, { n: 8, r: 'はっぷん' }, { n: 9, r: 'きゅうふん' },
  { n: 10, r: 'じゅっぷん、じっぷん' }, { n: 15, r: 'じゅうごふん' }, { n: 30, r: 'さんじゅっぷん、さんじっぷん、はん' }, { n: '?', r: 'なんぷん' },
]
const daysOfWeek = [
  { day: 'にちようび', id: 'hari Minggu' }, { day: 'げつようび', id: 'hari Senin' }, { day: 'かようび', id: 'hari Selasa' },
  { day: 'すいようび', id: 'hari Rabu' }, { day: 'もくようび', id: 'hari Kamis' }, { day: 'きんようび', id: 'hari Jumat' },
  { day: 'どようび', id: 'hari Sabtu' }, { day: 'なんようび', id: 'hari apa' },
]
const monthNames = [
  'いちがつ', 'にがつ', 'さんがつ', 'しがつ', 'ごがつ', 'ろくがつ', 'しちがつ', 'はちがつ', 'くがつ', 'じゅうがつ', 'じゅういちがつ', 'じゅうにがつ',
].map((r, i) => ({ n: i + 1, r }))
monthNames.push({ n: '?', r: 'なんがつ' })
const dateNames = [
  'ついたち', 'ふつか', 'みっか', 'よっか', 'いつか', 'むいか', 'なのか', 'ようか', 'ここのか', 'とおか',
  'じゅういちにち', 'じゅうににち', 'じゅうさんにち', 'じゅうよっか', 'じゅうごにち', 'じゅうろくにち', 'じゅうしちにち', 'じゅうはちにち', 'じゅうくにち', 'はつか',
  'にじゅういちにち', 'にじゅうににち', 'にじゅうさんにち', 'にじゅうよっか', 'にじゅうごにち', 'にじゅうろくにち', 'にじゅうしちにち', 'にじゅうはちにち', 'にじゅうくにち', 'さんじゅうにち', 'さんじゅういちにち',
].map((r, i) => ({ n: i + 1, r }))
dateNames.push({ n: '?', r: 'なんにち' })

// jangka waktu (durasi): ...jam / ...menit / ...hari / ...minggu / ...bulan / ...tahun
const durationHours = ['いちじかん', 'にじかん', 'さんじかん', 'よじかん', 'ごじかん', 'ろくじかん', 'ななじかん、しちじかん', 'はちじかん', 'くじかん', 'じゅうじかん', 'なんじかん']
const durationMinutes = ['いっぷん', 'にふん', 'さんぷん', 'よんぷん', 'ごふん', 'ろっぷん', 'ななふん', 'はっぷん', 'きゅうふん', 'じゅっぷん、じっぷん', 'なんぷん']
const durationDays = ['いちにち', 'ふつか', 'みっか', 'よっか', 'いつか', 'むいか', 'なのか', 'ようか', 'ここのか', 'とおか', 'なんにち']
const durationWeeks = ['いっしゅうかん', 'にしゅうかん', 'さんしゅうかん', 'よんしゅうかん', 'ごしゅうかん', 'ろくしゅうかん', 'ななしゅうかん', 'はっしゅうかん', 'きゅうしゅうかん', 'じゅっしゅうかん、じっしゅうかん', 'なんしゅうかん']
const durationMonths = ['いっかげつ', 'にかげつ', 'さんかげつ', 'よんかげつ', 'ごかげつ', 'ろっかげつ、はんとし', 'ななかげつ', 'はちかげつ、はっかげつ', 'きゅうかげつ', 'じゅっかげつ、じっかげつ', 'なんかげつ']
const durationYears = ['いちねん', 'にねん', 'さんねん', 'よねん', 'ごねん', 'ろくねん', 'ななねん、しちねん', 'はちねん', 'きゅうねん', 'じゅうねん', 'なんねん']
const durationRows = durationHours.map((h, i) => ({
  label: i < 10 ? String(i + 1) : '?',
  jam: h,
  menit: durationMinutes[i],
  hari: durationDays[i],
  minggu: durationWeeks[i],
  bulan: durationMonths[i],
  tahun: durationYears[i],
}))

// ---------------------------------------------------------------------
// IV. Kata Bantu Bilangan (kata penggolong / counters)
// ---------------------------------------------------------------------
const counters = [
  { title: 'Benda (umum)', unit: '〜つ', desc: 'apel, jeruk, dan benda pada umumnya', rows: ['ひとつ', 'ふたつ', 'みっつ', 'よっつ', 'いつつ', 'むっつ', 'ななつ', 'やっつ', 'ここのつ', 'とお', 'いくつ'] },
  { title: 'Orang', unit: '〜人', desc: 'menghitung manusia/orang', rows: ['ひとり', 'ふたり', 'さんにん', 'よにん', 'ごにん', 'ろくにん', 'ななにん、しちにん', 'はちにん', 'きゅうにん', 'じゅうにん', 'なんにん'] },
  { title: 'Urutan', unit: '〜番', desc: 'nomor urut', rows: ['いちばん', 'にばん', 'さんばん', 'よんばん', 'ごばん', 'ろくばん', 'ななばん', 'はちばん', 'きゅうばん', 'じゅうばん', 'なんばん'] },
  { title: 'Benda tipis & datar', unit: '〜枚', desc: 'kertas, kemeja, piring, dsb.', rows: ['いちまい', 'にまい', 'さんまい', 'よんまい', 'ごまい', 'ろくまい', 'ななまい', 'はちまい', 'きゅうまい', 'じゅうまい', 'なんまい'] },
  { title: 'Mesin & kendaraan', unit: '〜台', desc: 'TV, sepeda, mobil, dsb.', rows: ['いちだい', 'にだい', 'さんだい', 'よんだい', 'ごだい', 'ろくだい', 'ななだい', 'はちだい', 'きゅうだい', 'じゅうだい', 'なんだい'] },
  { title: 'Umur', unit: '〜歳', desc: 'usia seseorang', rows: ['いっさい', 'にさい', 'さんさい', 'よんさい', 'ごさい', 'ろくさい', 'ななさい', 'はっさい', 'きゅうさい', 'じゅっさい、じっさい', 'なんさい'] },
  { title: 'Buku & buku catatan', unit: '〜冊', desc: 'buku, majalah, notebook', rows: ['いっさつ', 'にさつ', 'さんさつ', 'よんさつ', 'ごさつ', 'ろくさつ', 'ななさつ', 'はっさつ', 'きゅうさつ', 'じゅっさつ、じっさつ', 'なんさつ'] },
  { title: 'Pakaian', unit: '〜着', desc: 'jas, baju, mantel', rows: ['いっちゃく', 'にちゃく', 'さんちゃく', 'よんちゃく', 'ごちゃく', 'ろくちゃく', 'ななちゃく', 'はっちゃく', 'きゅうちゃく', 'じゅっちゃく、じっちゃく', 'なんちゃく'] },
  { title: 'Frekuensi', unit: '〜回', desc: 'jumlah kali/kejadian', rows: ['いっかい', 'にかい', 'さんかい', 'よんかい', 'ごかい', 'ろっかい', 'ななかい', 'はっかい', 'きゅうかい', 'じゅっかい、じっかい', 'なんかい'] },
  { title: 'Benda kecil', unit: '〜個', desc: 'penjepit kertas, dadu, benda kecil lain', rows: ['いっこ', 'にこ', 'さんこ', 'よんこ', 'ごこ', 'ろっこ', 'ななこ', 'はっこ', 'きゅうこ', 'じゅっこ、じっこ', 'なんこ'] },
  { title: 'Sepatu & kaos kaki', unit: '〜足', desc: 'benda berpasangan untuk kaki', rows: ['いっそく', 'にそく', 'さんぞく', 'よんそく', 'ごそく', 'ろくそく', 'ななそく', 'はっそく', 'きゅうそく', 'じゅっそく、じっそく', 'なんぞく'] },
  { title: 'Rumah', unit: '〜軒', desc: 'bangunan rumah', rows: ['いっけん', 'にけん', 'さんげん', 'よんけん', 'ごけん', 'ろっけん', 'ななけん', 'はっけん', 'きゅうけん', 'じゅっけん、じっけん', 'なんげん'] },
  { title: 'Lantai bangunan', unit: '〜階', desc: 'lantai ke- pada sebuah gedung', rows: ['いっかい', 'にかい', 'さんがい', 'よんかい', 'ごかい', 'ろっかい', 'ななかい', 'はっかい', 'きゅうかい', 'じゅっかい、じっかい', 'なんがい'] },
  { title: 'Benda kurus & panjang', unit: '〜本', desc: 'pisang, botol, pensil, dsb.', rows: ['いっぽん', 'にほん', 'さんぼん', 'よんほん', 'ごほん', 'ろっぽん', 'ななほん', 'はっぽん', 'きゅうほん', 'じゅっぽん、じっぽん', 'なんぼん'] },
  { title: 'Minuman dalam gelas/cangkir', unit: '〜杯', desc: 'bir, susu, kopi, teh, dsb.', rows: ['いっぱい', 'にはい', 'さんばい', 'よんはい', 'ごはい', 'ろっぱい', 'ななはい', 'はっぱい', 'きゅうはい', 'じゅっぱい、じっぱい', 'なんばい'] },
  { title: 'Binatang kecil, ikan, serangga', unit: '〜匹', desc: 'kucing, ikan, capung, dsb.', rows: ['いっぴき', 'にひき', 'さんびき', 'よんひき', 'ごひき', 'ろっぴき', 'ななひき', 'はっぴき', 'きゅうひき', 'じゅっぴき、じっぴき', 'なんびき'] },
]
const counterNums = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '?']

// ---------------------------------------------------------------------
// V. Konjugasi Kata Kerja
// ---------------------------------------------------------------------
// Data inti per verba: bentuk kamus, bentuk ます, arti, kelompok, pelajaran.
// Bentuk て / ない / た dihitung otomatis dari bentuk kamus lewat aturan
// konjugasi standar, supaya data yang ditulis tangan tetap ringkas & konsisten.
function conjugate(kamus, group) {
  if (group === 3) {
    if (kamus === 'くる')
      return { te: 'きて', nai: 'こない', ta: 'きた' }
    return { te: `${kamus.slice(0, -2)}して`, nai: `${kamus.slice(0, -2)}しない`, ta: `${kamus.slice(0, -2)}した` }
  }
  if (group === 2) {
    const stem = kamus.slice(0, -1)
    return { te: `${stem}て`, nai: `${stem}ない`, ta: `${stem}た` }
  }
  // Group 1 (godan) — irregular special case: 行く
  if (kamus === 'いく')
    return { te: 'いって', nai: 'いかない', ta: 'いった' }

  const last = kamus.slice(-1)
  const stem = kamus.slice(0, -1)
  const map = {
    う: { te: `${stem}って`, nai: `${stem}わない`, ta: `${stem}った` },
    つ: { te: `${stem}って`, nai: `${stem}たない`, ta: `${stem}った` },
    る: { te: `${stem}って`, nai: `${stem}らない`, ta: `${stem}った` },
    く: { te: `${stem}いて`, nai: `${stem}かない`, ta: `${stem}いた` },
    ぐ: { te: `${stem}いで`, nai: `${stem}がない`, ta: `${stem}いだ` },
    す: { te: `${stem}して`, nai: `${stem}さない`, ta: `${stem}した` },
    ぬ: { te: `${stem}んで`, nai: `${stem}なない`, ta: `${stem}んだ` },
    ぶ: { te: `${stem}んで`, nai: `${stem}ばない`, ta: `${stem}んだ` },
    む: { te: `${stem}んで`, nai: `${stem}まない`, ta: `${stem}んだ` },
  }
  return map[last] ?? { te: '—', nai: '—', ta: '—' }
}

function buildGroup(list, group) {
  return list.map(([masu, kamus, arti, pelajaran]) => ({
    masu, kamus, arti, pelajaran, group, ...conjugate(kamus, group),
  }))
}

const verbGroup1 = buildGroup([
  ['あいます[ともだちに〜]', 'あう', 'bertemu (dengan teman)', 6],
  ['あそびます', 'あそぶ', 'bermain', 13],
  ['あらいます', 'あらう', 'mencuci', 18],
  ['あります', 'ある', 'ada, mempunyai', 9],
  ['あります[おまつりがある]', 'ある', 'ada, diadakan (pesta perayaan)', 10],
  ['あるきます', 'あるく', 'berjalan kaki', 21],
  ['いいます', 'いう', 'mengatakan, berkata', 23],
  ['いきます', 'いく', 'pergi', 5],
  ['いそぎます', 'いそぐ', 'buru-buru', 14],
  ['うごきます', 'うごく', 'pindah, bergerak', 21],
  ['うたいます', 'うたう', 'bernyanyi, menyanyi', 15],
  ['うります', 'うる', 'menjual', 15],
  ['おくります[ひとを〜]', 'おくる', 'mengantarkan (orang)', 24],
  ['おくります[てがみをひとに〜]', 'おくる', 'mengirim (surat)', 16],
  ['おします', 'おす', 'menekan', 16],
  ['おもいます', 'おもう', 'terpikir, berpikir', 15],
  ['かいます[ペットを〜]', 'かう', 'memelihara, memberi makan', 7],
  ['かきます[てがみを〜]', 'かく', 'menulis (surat)', 6],
  ['かします', 'かす', 'meminjamkan', 18],
  ['かちます', 'かつ', 'menang', 21],
  ['かぶります[ぼうしを〜]', 'かぶる', 'memakai (topi)', 22],
  ['がんばります', 'がんばる', 'berusaha, bekerja keras', 25],
  ['ききます', 'きく', 'mendengar', 6],
  ['ききます[せんせいに〜]', 'きく', 'bertanya (kepada guru)', 23],
  ['きります', 'きる', 'memotong, menggunting', 7],
  ['けします', 'けす', 'mematikan', 14],
  ['しります', 'しる', 'mengetahui, mengenal', 6],
  ['すわります', 'すわる', 'duduk', 15],
  ['だします', 'だす', 'mengeluarkan (surat/tugas)', 14],
  ['たちます', 'たつ', 'berdiri', 14],
  ['つかいます', 'つかう', 'memakai (barang/alat)', 25],
  ['つきます', 'つく', 'tiba, sampai', 14],
  ['つくります[しゃしんを〜]', 'つくる', 'membuat, memproduksi', 15],
  ['つれていきます', 'つれていく', 'membawa, mengajak pergi', 24],
  ['てつだいます', 'てつだう', 'membantu', 7],
  ['とまります[ホテルに〜]', 'とまる', 'menginap (di hotel)', 19],
  ['とります', 'とる', 'mengambil', 14],
  ['とります[しゃしんを〜]', 'とる', 'mengambil (foto)', 14],
  ['ならいます', 'ならう', 'belajar', 6],
  ['なくします', 'なくす', 'kehilangan', 25],
  ['ならびます', 'ならぶ', 'berbaris, mengantre', 24],
  ['ぬぎます', 'ぬぐ', 'melepas (pakaian)', 22],
  ['のぼります[やまに〜]', 'のぼる', 'naik, mendaki (gunung)', 19],
  ['のみます[くすりを〜]', 'のむ', 'minum (obat)', 16],
  ['のります[でんしゃに〜]', 'のる', 'naik (kereta rel listrik)', 16],
  ['はいります[きっさてんに〜]', 'はいる', 'masuk (ke coffee shop)', 6],
  ['はいります[だいがくに〜]', 'はいる', 'masuk (universitas)', 17],
  ['はきます', 'はく', 'memakai (sepatu, celana)', 22],
  ['はたらきます', 'はたらく', 'bekerja', 4],
  ['はなします', 'はなす', 'berbicara', 18],
  ['はらいます', 'はらう', 'membayar', 16],
  ['ひきます', 'ひく', 'tarik, bermain (alat musik bersenar/piano)', 17],
  ['ふります[あめが〜]', 'ふる', 'turun (hujan)', 23],
  ['まがります', 'まがる', 'belok (ke kanan)', 14],
  ['まちます', 'まつ', 'menunggu', 7],
  ['まわします', 'まわす', 'memutar', 16],
  ['もちます', 'もつ', 'membawa', 23],
  ['もっていきます', 'もっていく', 'membawa pergi', 14],
  ['もらいます', 'もらう', 'menerima', 6],
  ['やくにたちます', 'やくにたつ', 'berguna, bermanfaat', 11],
  ['やすみます', 'やすむ', 'istirahat, libur, cuti (kerja)', 6],
  ['よびます', 'よぶ', 'memanggil', 9],
  ['よみます', 'よむ', 'membaca', 6],
  ['わかります', 'わかる', 'mengerti', 6],
  ['わたります[はしを〜]', 'わたる', 'menyeberang (jembatan)', 23],
], 1)

const verbGroup2 = buildGroup([
  ['あけます', 'あける', 'membuka', 14],
  ['あげます', 'あげる', 'memberikan', 7],
  ['あつめます', 'あつめる', 'mengumpulkan', 18],
  ['あびます[シャワーを〜]', 'あびる', 'mandi (pakai shower)', 16],
  ['います', 'いる', 'ada (digunakan untuk makhluk hidup)', 10],
  ['うまれます', 'うまれる', 'lahir', 22],
  ['おきます', 'おきる', 'bangun', 4],
  ['おしえます', 'おしえる', 'mengajar', 7],
  ['おしえます[じゅうしょを〜]', 'おしえる', 'memberitahukan (alamat)', 14],
  ['おぼえます', 'おぼえる', 'mengingat, menghafal', 17],
  ['おります[でんしゃを〜]', 'おりる', 'turun (dari kereta rel listrik)', 16],
  ['かえます', 'かえる', 'mengubah, menukar', 22],
  ['かけます[めがねを〜]', 'かける', 'memakai (kacamata)', 22],
  ['かけます[でんわを〜]', 'かける', 'menelepon', 4],
  ['かります', 'かりる', 'meminjam', 11],
  ['かんがえます', 'かんがえる', 'berpikir, memikirkan', 11],
  ['きをつけます', 'きをつける', 'berhati-hati, waspada', 25],
  ['くれます', 'くれる', 'diberikan (kepada saya)', 24],
  ['しめます', 'しめる', 'menutup', 14],
  ['しらべます', 'しらべる', 'memeriksa, meneliti, mengecek', 20],
  ['すてます', 'すてる', 'membuang', 14],
  ['たべます', 'たべる', 'makan', 6],
  ['たります', 'たりる', 'cukup', 25],
  ['つかれます', 'つかれる', 'lelah', 13],
  ['つけます', 'つける', 'memakai (kerja), menyalakan', 14],
  ['でます[きっさてんを〜]', 'でる', 'keluar (dari coffee shop)', 14],
  ['でます[だいがくを〜]', 'でる', 'tamat (dari universitas)', 16],
  ['とめます', 'とめる', 'menghentikan, memarkir (kereta rel)', 14],
  ['ねます', 'ねる', 'tidur', 4],
  ['のりかえます', 'のりかえる', 'ganti, pindah (kereta rel)', 16],
  ['はじめます', 'はじめる', 'mulai', 15],
  ['まけます', 'まける', 'kalah', 21],
  ['みせます', 'みせる', 'memperlihatkan', 14],
  ['みます', 'みる', 'melihat, menonton', 6],
  ['むかえます', 'むかえる', 'menjemput', 13],
  ['やめます[かいしゃを〜]', 'やめる', 'berhenti (kerja)', 21],
  ['わすれます', 'わすれる', 'lupa', 17],
], 2)

const verbGroup3 = buildGroup([
  ['あんないします', 'あんないする', 'menemani, memandu (jalan-jalan)', 24],
  ['うんてんします', 'うんてんする', 'menyetir, mengendarai', 18],
  ['かいものします', 'かいものする', 'berbelanja', 13],
  ['きます', 'くる', 'datang', 5],
  ['けっこんします', 'けっこんする', 'menikah', 10],
  ['けんがくします', 'けんがくする', 'mengunjungi (tur, kunjungan belajar)', 13],
  ['けんきゅうします', 'けんきゅうする', 'meneliti', 16],
  ['コピーします', 'コピーする', 'fotokopi', 15],
  ['ざんぎょうします', 'ざんぎょうする', 'kerja lembur', 17],
  ['しょうかいします', 'しょうかいする', 'memperkenalkan', 6],
  ['しゅっちょうします', 'しゅっちょうする', 'dinas (perjalanan kerja)', 17],
  ['しょくじします', 'しょくじする', 'makan', 24],
  ['します', 'する', 'mengerjakan, melakukan, berbuat', 6],
  ['せつめいします', 'せつめいする', 'menjelaskan, menerangkan', 22],
  ['せんたくします', 'せんたくする', 'mencuci pakaian', 13],
  ['そうじします', 'そうじする', 'membersihkan', 13],
  ['つれてきます', 'つれてくる', 'membawa datang, mengajak datang', 24],
  ['でんわします', 'でんわする', 'menelepon', 4],
  ['もってきます', 'もってくる', 'membawa datang', 17],
  ['よやくします', 'よやくする', 'memesan (tiket, dsb.)', 18],
  ['りゅうがくします', 'りゅうがくする', 'studi di luar negeri', 21],
], 3)

const verbGroupLabels = {
  1: 'Kelompok I (Godan)',
  2: 'Kelompok II (Ichidan)',
  3: 'Kelompok III (tidak beraturan)',
}

// ---------------------------------------------------------------------
// VI. Kata Sifat (i-keiyoushi & na-keiyoushi)
// ---------------------------------------------------------------------
// i-keiyoushi: bentuk negatif = ganti akhiran い dengan くない (exception: いい → よくない)
function negateIAdjective(kamus) {
  if (kamus === 'いい')
    return 'よくない'
  return `${kamus.slice(0, -1)}くない`
}

const iAdjectives = [
  ['おおきい', 'besar'], ['ちいさい', 'kecil'], ['あたらしい', 'baru'], ['ふるい', 'lama, tua (benda)'],
  ['いい', 'bagus'], ['わるい', 'buruk, jelek'], ['あつい[てんき]', 'panas (cuaca)'], ['さむい', 'dingin (cuaca)'],
  ['あつい[コーヒー]', 'panas (benda)'], ['つめたい', 'dingin (benda)'], ['むずかしい', 'sulit'], ['やさしい', 'mudah'],
  ['たかい[たてもの]', 'tinggi'], ['たかい[きっぷ]', 'mahal'], ['やすい', 'murah'], ['ひくい', 'rendah'],
  ['おもしろい', 'menarik'], ['つまらない', 'membosankan'], ['いそがしい', 'sibuk'], ['たのしい', 'menyenangkan'],
  ['おいしい', 'enak'], ['まずい', 'tidak enak'], ['いい[ひと]', 'baik (orang)'], ['あかるい', 'terang, ceria'],
  ['くらい', 'gelap'], ['ながい', 'panjang'], ['みじかい', 'pendek'], ['はやい[じかん]', 'cepat, pagi-pagi'],
  ['おそい', 'lambat, terlambat'], ['おおい', 'banyak'], ['すくない', 'sedikit'], ['あたたかい', 'hangat'],
  ['すずしい', 'sejuk'], ['きたない', 'kotor'], ['ひろい', 'luas'], ['せまい', 'sempit'], ['おもい', 'berat'],
  ['かるい', 'ringan'], ['からい', 'pedas'], ['あまい', 'manis'],
]

const naAdjectives = [
  ['きれい', 'cantik, bersih'], ['しずか', 'tenang, sepi'], ['にぎやか', 'ramai'], ['ゆうめい', 'terkenal'],
  ['しんせつ', 'ramah, baik hati'], ['げんき', 'sehat, bersemangat'], ['ひま', 'senggang'], ['べんり', 'praktis'],
  ['すてき', 'bagus, menawan'], ['じょうぶ', 'kuat, tahan lama'], ['じょうず', 'pandai, mahir'], ['へた', 'tidak pandai'],
  ['すき', 'suka'], ['きらい', 'tidak suka, benci'], ['だいすき', 'sangat suka'], ['だいきらい', 'sangat tidak suka'],
  ['ハンサム', 'tampan'], ['きけん', 'berbahaya'], ['あんぜん', 'aman'], ['たいせつ', 'penting'], ['ざんねん', 'sayang sekali'],
  ['いろいろ', 'bermacam-macam'], ['けっこう', 'cukup, lumayan'], ['だいじょうぶ', 'tidak apa-apa, baik-baik saja'],
  ['まじめ', 'rajin, serius'], ['らく', 'santai, mudah'], ['ふべん', 'tidak praktis'], ['ひつよう', 'perlu'],
  ['おなじ', 'sama'], ['いや', 'tidak suka, enggan'],
]
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between mb-4 ga-2">
      <div>
        <h4 class="text-h4 mb-1">
          {{ t('lampiran.title') }}
        </h4>
        <p class="text-body-2 text-medium-emphasis mb-0">
          {{ t('lampiran.subtitle') }}
        </p>
      </div>
      <VChip color="primary" variant="tonal" prepend-icon="tabler-certificate">
        N5
      </VChip>
    </div>

    <div
      class="lampiran-section-tabs mb-4"
      role="tablist"
      :aria-label="t('lampiran.title')"
    >
      <button
        v-for="opt in SECTIONS"
        :key="opt.value"
        type="button"
        role="tab"
        class="lampiran-section-tabs__btn"
        :class="{ 'lampiran-section-tabs__btn--active': section === opt.value }"
        :aria-selected="section === opt.value"
        @click="section = opt.value"
      >
        <VIcon :icon="opt.icon" size="18" class="me-1" />
        {{ t(opt.label) }}
      </button>
    </div>

    <VWindow v-model="section">
      <!-- I. Kata Bilangan -->
      <VWindowItem value="numbers">
        <VRow>
          <VCol cols="12" md="6">
            <VCard title="0–9">
              <VTable density="compact">
                <tbody>
                  <tr v-for="item in numbersOnes" :key="`o-${item.n}`">
                    <td class="font-weight-medium" style="inline-size: 48px;">
                      {{ item.n }}
                    </td>
                    <td>{{ item.r }}</td>
                  </tr>
                </tbody>
              </VTable>
            </VCard>
          </VCol>
          <VCol cols="12" md="6">
            <VCard title="10–90">
              <VTable density="compact">
                <tbody>
                  <tr v-for="item in numbersTens" :key="`t-${item.n}`">
                    <td class="font-weight-medium" style="inline-size: 48px;">
                      {{ item.n }}
                    </td>
                    <td>{{ item.r }}</td>
                  </tr>
                </tbody>
              </VTable>
            </VCard>
          </VCol>
          <VCol cols="12" md="6">
            <VCard title="100–900">
              <VTable density="compact">
                <tbody>
                  <tr v-for="item in numbersHundreds" :key="`h-${item.n}`">
                    <td class="font-weight-medium" style="inline-size: 64px;">
                      {{ item.n }}
                    </td>
                    <td>{{ item.r }}</td>
                  </tr>
                </tbody>
              </VTable>
            </VCard>
          </VCol>
          <VCol cols="12" md="6">
            <VCard title="1,000–9,000">
              <VTable density="compact">
                <tbody>
                  <tr v-for="item in numbersThousands" :key="`th-${item.n}`">
                    <td class="font-weight-medium" style="inline-size: 64px;">
                      {{ item.n }}
                    </td>
                    <td>{{ item.r }}</td>
                  </tr>
                </tbody>
              </VTable>
            </VCard>
          </VCol>
          <VCol cols="12" md="6">
            <VCard :title="t('lampiran.big_numbers')">
              <VTable density="compact">
                <tbody>
                  <tr v-for="item in numbersBig" :key="`b-${item.n}`">
                    <td class="font-weight-medium" style="inline-size: 110px;">
                      {{ item.n }}
                    </td>
                    <td>{{ item.r }}</td>
                  </tr>
                </tbody>
              </VTable>
            </VCard>
          </VCol>
          <VCol cols="12" md="6">
            <VCard :title="t('lampiran.decimal_fraction')">
              <VTable density="compact">
                <tbody>
                  <tr v-for="item in numbersDecimalFraction" :key="`d-${item.n}`">
                    <td class="font-weight-medium" style="inline-size: 64px;">
                      {{ item.n }}
                    </td>
                    <td>{{ item.r }}</td>
                  </tr>
                </tbody>
              </VTable>
            </VCard>
          </VCol>
        </VRow>
      </VWindowItem>

      <!-- II. Ungkapan waktu -->
      <VWindowItem value="time">
        <VCard :title="t('lampiran.relative_days')" class="mb-4">
          <VTable density="comfortable">
            <thead>
              <tr>
                <th />
                <th>hari</th>
                <th>pagi</th>
                <th>malam</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in relativeDays" :key="row.id">
                <td class="text-medium-emphasis">
                  {{ row.label }}
                </td>
                <td>{{ row.hari }}</td>
                <td>{{ row.pagi }}</td>
                <td>{{ row.malam }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.relative_periods')">
          <VTable density="comfortable">
            <thead>
              <tr>
                <th />
                <th>minggu</th>
                <th>bulan</th>
                <th>tahun</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in relativePeriods" :key="row.id">
                <td class="text-medium-emphasis">
                  {{ row.label }}
                </td>
                <td>{{ row.minggu }}</td>
                <td>{{ row.bulan }}</td>
                <td>{{ row.tahun }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
      </VWindowItem>

      <!-- III. Penanda waktu / tanggal / jangka waktu -->
      <VWindowItem value="clock">
        <VRow>
          <VCol cols="12" md="4">
            <VCard :title="t('lampiran.hours')">
              <VTable density="compact">
                <tbody>
                  <tr v-for="item in jamPoint" :key="`jam-${item.n}`">
                    <td class="font-weight-medium" style="inline-size: 40px;">
                      {{ item.n }}
                    </td>
                    <td>{{ item.r }}</td>
                  </tr>
                </tbody>
              </VTable>
            </VCard>
          </VCol>
          <VCol cols="12" md="4">
            <VCard :title="t('lampiran.minutes')">
              <VTable density="compact">
                <tbody>
                  <tr v-for="item in menitPoint" :key="`men-${item.n}`">
                    <td class="font-weight-medium" style="inline-size: 40px;">
                      {{ item.n }}
                    </td>
                    <td>{{ item.r }}</td>
                  </tr>
                </tbody>
              </VTable>
            </VCard>
          </VCol>
          <VCol cols="12" md="4">
            <VCard :title="t('lampiran.days_of_week')">
              <VTable density="compact">
                <tbody>
                  <tr v-for="item in daysOfWeek" :key="item.day">
                    <td>{{ item.day }}</td>
                    <td class="text-medium-emphasis">
                      {{ item.id }}
                    </td>
                  </tr>
                </tbody>
              </VTable>
            </VCard>
          </VCol>
          <VCol cols="12" md="6">
            <VCard :title="t('lampiran.months')">
              <VTable density="compact">
                <tbody>
                  <tr v-for="item in monthNames" :key="`bln-${item.n}`">
                    <td class="font-weight-medium" style="inline-size: 40px;">
                      {{ item.n }}
                    </td>
                    <td>{{ item.r }}</td>
                  </tr>
                </tbody>
              </VTable>
            </VCard>
          </VCol>
          <VCol cols="12" md="6">
            <VCard :title="t('lampiran.dates')">
              <VTable density="compact" style="max-block-size: 420px; overflow-y: auto;">
                <tbody>
                  <tr v-for="item in dateNames" :key="`tgl-${item.n}`">
                    <td class="font-weight-medium" style="inline-size: 40px;">
                      {{ item.n }}
                    </td>
                    <td>{{ item.r }}</td>
                  </tr>
                </tbody>
              </VTable>
            </VCard>
          </VCol>
          <VCol cols="12">
            <VCard :title="t('lampiran.duration')">
              <VTable density="compact">
                <thead>
                  <tr>
                    <th />
                    <th>…じかん</th>
                    <th>…ふん</th>
                    <th>…にち</th>
                    <th>…しゅうかん</th>
                    <th>…かげつ</th>
                    <th>…ねん</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="row in durationRows" :key="`dur-${row.label}`">
                    <td class="font-weight-medium">
                      {{ row.label }}
                    </td>
                    <td>{{ row.jam }}</td>
                    <td>{{ row.menit }}</td>
                    <td>{{ row.hari }}</td>
                    <td>{{ row.minggu }}</td>
                    <td>{{ row.bulan }}</td>
                    <td>{{ row.tahun }}</td>
                  </tr>
                </tbody>
              </VTable>
            </VCard>
          </VCol>
        </VRow>
      </VWindowItem>

      <!-- IV. Kata Bantu Bilangan -->
      <VWindowItem value="counters">
        <VRow>
          <VCol v-for="c in counters" :key="c.title" cols="12" md="6" lg="4">
            <VCard>
              <VCardItem>
                <VCardTitle>{{ c.title }} <span class="text-primary">{{ c.unit }}</span></VCardTitle>
                <VCardSubtitle>{{ c.desc }}</VCardSubtitle>
              </VCardItem>
              <VTable density="compact">
                <tbody>
                  <tr v-for="(r, i) in c.rows" :key="`${c.title}-${i}`">
                    <td class="font-weight-medium" style="inline-size: 32px;">
                      {{ counterNums[i] }}
                    </td>
                    <td>{{ r }}</td>
                  </tr>
                </tbody>
              </VTable>
            </VCard>
          </VCol>
        </VRow>
      </VWindowItem>

      <!-- V. Konjugasi Kata Kerja -->
      <VWindowItem value="verbs">
        <VCard
          v-for="(group, gi) in [verbGroup1, verbGroup2, verbGroup3]"
          :key="gi"
          :title="verbGroupLabels[gi + 1]"
          class="mb-4"
        >
          <VTable density="compact" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.form_masu') }}</th>
                <th>{{ t('lampiran.form_kamus') }}</th>
                <th>{{ t('lampiran.form_te') }}</th>
                <th>{{ t('lampiran.form_nai') }}</th>
                <th>{{ t('lampiran.form_ta') }}</th>
                <th>{{ t('lampiran.form_arti') }}</th>
                <th>{{ t('lampiran.form_pelajaran') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(v, vi) in group" :key="`${gi}-${vi}`">
                <td>{{ v.masu }}</td>
                <td class="font-weight-medium">
                  {{ v.kamus }}
                </td>
                <td>{{ v.te }}</td>
                <td>{{ v.nai }}</td>
                <td>{{ v.ta }}</td>
                <td class="text-medium-emphasis">
                  {{ v.arti }}
                </td>
                <td>{{ v.pelajaran }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
      </VWindowItem>

      <!-- VI. Kata Sifat -->
      <VWindowItem value="adjectives">
        <VRow>
          <VCol cols="12" md="6">
            <VCard title="i-keiyoushi (〜い)" :subtitle="t('lampiran.i_adjective_hint')">
              <VTable density="compact">
                <thead>
                  <tr>
                    <th>{{ t('lampiran.form_kamus') }}</th>
                    <th>{{ t('lampiran.form_nai') }}</th>
                    <th>{{ t('lampiran.form_arti') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="([kamus, arti], i) in iAdjectives" :key="`ia-${i}`">
                    <td class="font-weight-medium">
                      {{ kamus }}
                    </td>
                    <td>{{ negateIAdjective(kamus.replace(/\[.*\]/, '')) }}</td>
                    <td class="text-medium-emphasis">
                      {{ arti }}
                    </td>
                  </tr>
                </tbody>
              </VTable>
            </VCard>
          </VCol>
          <VCol cols="12" md="6">
            <VCard title="na-keiyoushi (〜な)" :subtitle="t('lampiran.na_adjective_hint')">
              <VTable density="compact">
                <thead>
                  <tr>
                    <th>{{ t('lampiran.form_kamus') }}</th>
                    <th>{{ t('lampiran.form_nai') }}</th>
                    <th>{{ t('lampiran.form_arti') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="([kamus, arti], i) in naAdjectives" :key="`na-${i}`">
                    <td class="font-weight-medium">
                      {{ kamus }}
                    </td>
                    <td>{{ kamus }}じゃない</td>
                    <td class="text-medium-emphasis">
                      {{ arti }}
                    </td>
                  </tr>
                </tbody>
              </VTable>
            </VCard>
          </VCol>
        </VRow>
      </VWindowItem>
    </VWindow>
  </div>
</template>

<style scoped>
/* ---------- section selector — same visual language as kana/kanji ---------- */
.lampiran-section-tabs {
  display: inline-flex;
  flex-wrap: wrap;
  padding: 4px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  gap: 4px;
}

.lampiran-section-tabs__btn {
  display: inline-flex;
  align-items: center;
  padding: 8px 16px;
  border: 0;
  border-radius: 9px;
  background: transparent;
  color: rgba(var(--v-theme-on-surface), 0.72);
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  white-space: nowrap;
  transition: background 0.15s ease, color 0.15s ease;
}

.lampiran-section-tabs__btn:hover {
  background: rgba(var(--v-theme-primary), 0.08);
}

.lampiran-section-tabs__btn--active,
.lampiran-section-tabs__btn--active:hover {
  background: rgba(var(--v-theme-primary), 0.16);
  color: rgb(var(--v-theme-primary));
}

.lampiran-section-tabs__btn:focus-visible {
  outline: 2px solid rgb(var(--v-theme-primary));
  outline-offset: 2px;
}

@media (max-width: 599px) {
  .lampiran-section-tabs {
    display: flex;
    inline-size: 100%;
  }

  .lampiran-section-tabs__btn {
    flex: 1 1 auto;
    justify-content: center;
    padding-inline: 10px;
  }
}
</style>
