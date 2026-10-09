<script setup>
// Halaman referensi tata bahasa statis. Selektor N5 / N4 memilih isinya:
//   N5: angka, ungkapan waktu, kata bantu bilangan, konjugasi kata kerja, kata sifat.
//   N4: Pelajaran 1–25, dikelompokkan jadi 6 tab (pola kalimat, bentuk kata kerja, ungkapan,
//       bahasa hormat, kehidupan sehari-hari, kosakata tema); tiap tab menumpuk kartu per pelajaran.
// Tidak memakai backend — semua data ditulis langsung di sini, mengikuti pola
// halaman kana/kanji yang berdiri sendiri dari sistem `lessons`.
import { useLevelChoice } from '@/composables/useLevelChoice'

const { t, locale } = useI18n()
const { level } = useLevelChoice()

// Teks dua bahasa untuk data N4: { id, en }
const tr = text => (locale.value === 'en' ? text.en : text.id)

const SECTIONS_N5 = [
  { value: 'numbers', label: 'lampiran.section_numbers', icon: 'tabler-numbers' },
  { value: 'time', label: 'lampiran.section_time', icon: 'tabler-calendar-time' },
  { value: 'clock', label: 'lampiran.section_clock', icon: 'tabler-clock' },
  { value: 'counters', label: 'lampiran.section_counters', icon: 'tabler-list-numbers' },
  { value: 'verbs', label: 'lampiran.section_verbs', icon: 'tabler-abc' },
  { value: 'adjectives', label: 'lampiran.section_adjectives', icon: 'tabler-palette' },
]

const SECTIONS_N4 = [
  { value: 'n4g-patterns', label: 'lampiran.section_n4_g_patterns', icon: 'tabler-message-question' },
  { value: 'n4g-verbs', label: 'lampiran.section_n4_g_verbs', icon: 'tabler-bulb' },
  { value: 'n4g-expressions', label: 'lampiran.section_n4_g_expressions', icon: 'tabler-bulb' },
  { value: 'n4g-keigo', label: 'lampiran.section_n4_g_keigo', icon: 'tabler-bow' },
  { value: 'n4g-daily', label: 'lampiran.section_n4_g_daily', icon: 'tabler-building-store' },
  { value: 'n4g-vocab', label: 'lampiran.section_n4_g_vocab', icon: 'tabler-school' },
]

const sections = computed(() => (level.value === 'N4' ? SECTIONS_N4 : SECTIONS_N5))
const section = ref(sections.value[0].value)

watch(level, () => {
  section.value = sections.value[0].value
})

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

// ---------------------------------------------------------------------
// N4 · Pelajaran 1 — ringkasan 〜んです
// ---------------------------------------------------------------------
const ndesuForms = [
  {
    type: { id: 'Kata kerja', en: 'Verb' },
    plain: '行く ／ 行かない ／ 行った',
    example: '行くんです ／ 行かないんです ／ 行ったんです',
    note: { id: 'Bentuk biasa apa adanya', en: 'Plain form as it is' },
  },
  {
    type: { id: 'Kata sifat い', en: 'い-adjective' },
    plain: '高い ／ 高くない ／ 高かった',
    example: '高いんです ／ 高くないんです ／ 高かったんです',
    note: { id: 'Bentuk biasa apa adanya', en: 'Plain form as it is' },
  },
  {
    type: { id: 'Kata sifat な', en: 'な-adjective' },
    plain: '好き(だ) ／ 好きじゃない',
    example: '好きなんです ／ 好きじゃないんです',
    note: { id: 'だ → な (bentuk positif)', en: 'だ → な (affirmative)' },
  },
  {
    type: { id: 'Kata benda', en: 'Noun' },
    plain: '病気(だ) ／ 病気じゃない',
    example: '病気なんです ／ 病気じゃないんです',
    note: { id: 'だ → な (bentuk positif)', en: 'だ → な (affirmative)' },
  },
]

const ndesuUses = [
  {
    pattern: '〜んですか',
    use: { id: 'Memastikan hal yang dilihat/didengar, meminta keterangan lanjutan, menanyakan alasan atau keadaan.', en: 'Confirming what you saw or heard, asking for more details, asking for a reason or a situation.' },
    example: 'どうして 遅《おく》れたんですか。',
    meaning: { id: 'Kenapa terlambat?', en: 'Why were you late?' },
  },
  {
    pattern: '〜んです',
    use: { id: 'Menjelaskan alasan atau keadaan (jawaban 〜んですか), atau menambah alasan pada ucapan sendiri.', en: 'Explaining a reason or a situation (the answer to 〜んですか), or adding a reason to your own statement.' },
    example: 'バスが 来《こ》なかったんです。',
    meaning: { id: 'Karena bus tidak datang.', en: 'Because the bus did not come.' },
  },
  {
    pattern: '〜んですが、〜',
    use: { id: 'Membuka pembicaraan sebelum permintaan, ajakan, atau permohonan izin.', en: 'Opening a conversation before a request, an invitation, or asking permission.' },
    example: '頭《あたま》が 痛《いた》いんですが、帰《かえ》っても いいですか。',
    meaning: { id: 'Kepala saya sakit, bolehkah saya pulang?', en: 'My head hurts. May I go home?' },
  },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 1 — Cara Membuang Sampah (ごみの出し方)
// ---------------------------------------------------------------------
const garbageTypes = [
  {
    ja: '燃《も》えるごみ',
    alt: '可燃《かねん》ごみ',
    name: { id: 'Sampah organik (mudah terbakar)', en: 'Burnable garbage' },
    items: { id: 'Sisa dapur (生《なま》ごみ), kertas bekas (紙《かみ》くず), dan sejenisnya', en: 'Kitchen waste (生《なま》ごみ), scrap paper (紙《かみ》くず), and similar' },
    day: { id: 'Setiap Senin dan Kamis (月曜日・木曜日)', en: 'Every Monday and Thursday (月曜日・木曜日)' },
  },
  {
    ja: '燃《も》えないごみ',
    alt: '不燃《ふねん》ごみ',
    name: { id: 'Sampah anorganik (tidak terbakar)', en: 'Non-burnable garbage' },
    items: { id: 'Produk kaca, keramik (瀬戸物《せともの》), peralatan dapur dari logam', en: 'Glass goods, ceramics (瀬戸物《せともの》), metal kitchenware' },
    day: { id: 'Setiap Rabu (水曜日)', en: 'Every Wednesday (水曜日)' },
  },
  {
    ja: '資源《しげん》ごみ',
    alt: '',
    name: { id: 'Sampah yang dapat didaur ulang', en: 'Recyclable garbage' },
    items: { id: 'Kaleng (缶《かん》), botol kaca (瓶《びん》), botol plastik (ペットボトル), dan sejenisnya', en: 'Cans (缶《かん》), glass bottles (瓶《びん》), plastic bottles (ペットボトル), and similar' },
    day: { id: 'Selasa minggu ke-2 dan ke-4 (第2・第4火曜日)', en: 'The 2nd and 4th Tuesday (第2・第4火曜日)' },
  },
  {
    ja: '粗大《そだい》ごみ',
    alt: '',
    name: { id: 'Sampah ukuran besar', en: 'Oversized garbage' },
    items: { id: 'Perabot rumah tangga (家具《かぐ》), sepeda (自転車《じてんしゃ》), dan sejenisnya', en: 'Furniture (家具《かぐ》), bicycles (自転車《じてんしゃ》), and similar' },
    day: { id: 'Perlu pendaftaran sebelumnya (事前《じぜん》申《もう》し込《こ》み)', en: 'Needs prior registration (事前《じぜん》申《もう》し込《こ》み)' },
  },
]

const garbageTips = [
  { id: 'Tempat dan hari pengumpulan berbeda di tiap daerah — tabel ini hanya gambaran umum. Selalu cek pengumuman (ごみ収集日《しゅうしゅうび》のお知《し》らせ) di tempat tinggalmu.', en: 'Places and collection days differ by area — this table is only a general picture. Always check the notice (ごみ収集日《しゅうしゅうび》のお知《し》らせ) where you live.' },
  { id: 'Untuk sampah ukuran besar, daftar dulu (biasanya ke kantor kelurahan) lalu buang pada hari yang ditentukan.', en: 'For oversized garbage, register first (usually at the ward office) and put it out on the assigned day.' },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 2 — Kata Kerja Potensial (可能形)
// ---------------------------------------------------------------------
const potentialForms = [
  { group: 'I', dict: '書《か》く', polite: '書《か》けます', plain: '書《か》ける', note: { id: 'Akhiran う → え + る', en: 'Final う-row kana → え-row + る' } },
  { group: 'I', dict: '買《か》う', polite: '買《か》えます', plain: '買《か》える', note: { id: 'う → え', en: 'う → え' } },
  { group: 'I', dict: '話《はな》す', polite: '話《はな》せます', plain: '話《はな》せる', note: { id: 'す → せ', en: 'す → せ' } },
  { group: 'I', dict: '飲《の》む', polite: '飲《の》めます', plain: '飲《の》める', note: { id: 'む → め', en: 'む → め' } },
  { group: 'II', dict: '食《た》べる', polite: '食《た》べられます', plain: '食《た》べられる', note: { id: 'る → られる', en: 'る → られる' } },
  { group: 'II', dict: '起《お》きる', polite: '起《お》きられます', plain: '起《お》きられる', note: { id: 'る → られる', en: 'る → られる' } },
  { group: 'III', dict: '来《く》る', polite: '来《こ》られます', plain: '来《こ》られる', note: { id: 'Tidak beraturan', en: 'Irregular' } },
  { group: 'III', dict: 'する', polite: 'できます', plain: 'できる', note: { id: 'Tidak beraturan', en: 'Irregular' } },
]

const potentialParticles = [
  { before: '日本語《にほんご》を 話《はな》します。', after: '日本語《にほんご》が 話《はな》せます。', note: { id: 'を → が (objek)', en: 'を → が (object)' } },
  { before: 'ギターを ひきます。', after: 'ギターが ひけます。', note: { id: 'を → が (objek)', en: 'を → が (object)' } },
  { before: '先生《せんせい》に 会《あ》います。', after: '先生《せんせい》に 会《あ》えます。', note: { id: 'に tetap', en: 'に stays' } },
  { before: '図書館《としょかん》で 本《ほん》を 借《か》ります。', after: '図書館《としょかん》で 本《ほん》が 借《か》りられます。', note: { id: 'で tetap, を → が', en: 'で stays, を → が' } },
]

const potentialCompare = [
  {
    pattern: '見《み》えます／聞《き》こえます',
    use: { id: 'Terlihat／terdengar dengan sendirinya (objek が).', en: 'Comes into sight／hearing by itself (object が).' },
    example: '窓《まど》から 公園《こうえん》が 見《み》えます。',
    meaning: { id: 'Dari jendela terlihat taman.', en: 'The park is visible from the window.' },
  },
  {
    pattern: '見《み》られます／聞《き》けます',
    use: { id: 'Bisa menonton／mendengarkan dengan sengaja (kemungkinan).', en: 'Able to watch／listen on purpose (possibility).' },
    example: 'この 映画《えいが》は インターネットで 見《み》られます。',
    meaning: { id: 'Film ini bisa ditonton lewat internet.', en: 'You can watch this film online.' },
  },
  {
    pattern: 'Ｎが できます',
    use: { id: 'Terbentuk, berdiri, selesai.', en: 'Comes into being, gets built, is finished.' },
    example: '駅《えき》の 前《まえ》に 本屋《ほんや》が できました。',
    meaning: { id: 'Di depan stasiun berdiri toko buku.', en: 'A bookshop opened in front of the station.' },
  },
  {
    pattern: 'Ｎ＋ しか ＋ Ｖない',
    use: { id: 'Hanya ~ (selalu bersama bentuk negatif).', en: 'Only ~ (always with a negative).' },
    example: '冷蔵庫《れいぞうこ》に 水《みず》しか ありません。',
    meaning: { id: 'Di kulkas hanya ada air.', en: 'There is only water in the fridge.' },
  },
  {
    pattern: 'Ａは 〜が、Ｂは 〜',
    use: { id: 'は untuk membandingkan dua hal.', en: 'は to contrast two things.' },
    example: '料理《りょうり》は 作《つく》れますが、お菓子《かし》は 作《つく》れません。',
    meaning: { id: 'Masakan bisa, kue tidak.', en: 'Meals yes, sweets no.' },
  },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 2 — Toko di Sekitar Lingkungan (近《ちか》くの店《みせ》)
// ---------------------------------------------------------------------
const shopGroups = [
  {
    ja: '靴《くつ》・かばん 修理《しゅうり》、合《あ》いかぎ',
    name: { id: 'Perbaikan sepatu・tas, kunci cadangan', en: 'Shoe & bag repair, spare keys' },
    items: [
      { ja: 'ヒール・かかと 修理《しゅうり》', meaning: { id: 'Perbaikan hak・tumit', en: 'Heel repair' } },
      { ja: 'つま先《さき》 修理《しゅうり》', meaning: { id: 'Perbaikan ujung sepatu', en: 'Toe repair' } },
      { ja: '中敷《なかじ》き 交換《こうかん》', meaning: { id: 'Ganti pengalas dalam', en: 'Insole replacement' } },
      { ja: 'ファスナー 交換《こうかん》', meaning: { id: 'Ganti ritsleting', en: 'Zipper replacement' } },
      { ja: 'ハンドル・持《も》ち手《て》 交換《こうかん》', meaning: { id: 'Ganti setir・pegangan', en: 'Handle replacement' } },
      { ja: 'ほつれ・縫《ぬ》い目《め》の 修理《しゅうり》', meaning: { id: 'Perbaikan jahitan lepas', en: 'Repair of loose seams' } },
      { ja: '合《あ》いかぎ', meaning: { id: 'Kunci cadangan', en: 'Spare key' } },
    ],
  },
  {
    ja: 'クリーニング屋《や》',
    name: { id: 'Binatu (laundry)', en: 'Dry cleaner' },
    items: [
      { ja: 'ドライクリーニング', meaning: { id: 'Mencuci tanpa air', en: 'Dry cleaning' } },
      { ja: '水洗《みずあら》い', meaning: { id: 'Mencuci dengan air', en: 'Washing with water' } },
      { ja: '染《し》み抜《ぬ》き', meaning: { id: 'Menghilangkan noda', en: 'Stain removal' } },
      { ja: '撥水《はっすい》加工《かこう》', meaning: { id: 'Pengolahan anti air', en: 'Water-repellent treatment' } },
      { ja: 'サイズ 直《なお》し', meaning: { id: 'Permak ukuran', en: 'Size alteration' } },
      { ja: '縮《ちぢ》む', meaning: { id: 'Menyusut', en: 'To shrink' } },
      { ja: '伸《の》びる', meaning: { id: 'Melar, memanjang', en: 'To stretch' } },
    ],
  },
  {
    ja: 'コンビニ',
    name: { id: 'Minimarket 24 jam', en: '24-hour convenience store' },
    items: [
      { ja: '宅配便《たくはいびん》の 受《う》け付《つ》け', meaning: { id: 'Penerimaan titipan paket kilat', en: 'Parcel delivery drop-off' } },
      { ja: 'ATM', meaning: { id: 'ATM', en: 'ATM' } },
      { ja: '公共《こうきょう》料金《りょうきん》などの 支払《しはら》い', meaning: { id: 'Pembayaran listrik, air, dan tagihan lain', en: 'Payment of utility bills and similar' } },
      { ja: 'コピー、ファクス', meaning: { id: 'Fotokopi, faks', en: 'Copying, fax' } },
      { ja: 'はがき・切手《きって》の 販売《はんばい》', meaning: { id: 'Penjualan kartu pos, perangko', en: 'Postcards and stamps' } },
      { ja: 'コンサートチケットの 販売《はんばい》', meaning: { id: 'Penjualan tiket konser', en: 'Concert tickets' } },
    ],
  },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 3 — ringkasan pola
// ---------------------------------------------------------------------
const l3Patterns = [
  {
    pattern: 'V₁(ます形)ながら V₂',
    form: { id: 'Buang ます dari V₁ + ながら. Kegiatan inti di V₂.', en: 'Drop ます from V₁ + ながら. The main action is V₂.' },
    example: '音楽《おんがく》を 聞《き》きながら 歩《ある》きます。',
    meaning: { id: 'Berjalan sambil mendengarkan musik.', en: 'I walk while listening to music.' },
  },
  {
    pattern: 'Vて います／Vて いました',
    form: { id: 'Kebiasaan yang berulang (sekarang／dulu), biasanya dengan 毎朝, 毎晩, よく, dsb.', en: 'A repeated habit (now／in the past), usually with 毎朝, 毎晩, よく, etc.' },
    example: '毎朝《まいあさ》 走《はし》って います。',
    meaning: { id: 'Setiap pagi saya berlari.', en: 'I run every morning.' },
  },
  {
    pattern: 'ふつうけい ＋ し、〜し、〜',
    form: { id: 'Menyebut beberapa hal sejenis atau alasan. Kata benda／kata sifat な memakai だ.', en: 'Lists several similar points or reasons. Nouns／な-adjectives take だ.' },
    example: '安《やす》いし、おいしいし、よく 来《き》ます。',
    meaning: { id: 'Murah dan enak, jadi sering datang.', en: 'It is cheap and tasty, so I come often.' },
  },
  {
    pattern: '〜。それで、〜',
    form: { id: 'Sebab → akibat. Setelahnya bukan ajakan／perintah／permintaan.', en: 'Cause → result. Not followed by an invitation／command／request.' },
    example: '熱《ねつ》が ありました。それで、会社《かいしゃ》を 休《やす》みました。',
    meaning: { id: 'Saya demam. Makanya saya tidak masuk kerja.', en: 'I had a fever. So I took the day off.' },
  },
  {
    pattern: '〜とき＋は／に／も／や',
    form: { id: 'とき adalah kata benda sehingga bisa diikuti partikel; 〜ときや〜とき menyebut beberapa keadaan.', en: 'とき is a noun, so particles can follow it; 〜ときや〜とき lists several situations.' },
    example: '疲《つか》れた ときや 眠《ねむ》い ときは、休《やす》みます。',
    meaning: { id: 'Kalau lelah atau mengantuk, saya beristirahat.', en: 'When I am tired or sleepy, I rest.' },
  },
]

const nagaraForms = [
  { v: '聞《き》きます', stem: '聞《き》き', result: '聞《き》きながら', note: { id: 'Golongan I: buang ます (sisanya sudah bentuk i) lalu ながら', en: 'Group I: drop ます (what is left is the i-stem) then ながら' } },
  { v: '食《た》べます', stem: '食《た》べ', result: '食《た》べながら', note: { id: 'Golongan II: buang ます', en: 'Group II: drop ます' } },
  { v: 'します', stem: 'し', result: 'しながら', note: { id: 'Golongan III', en: 'Group III' } },
  { v: '来《き》ます', stem: '来《き》', result: '来《き》ながら', note: { id: 'Golongan III', en: 'Group III' } },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 3 — Cara membaca info sewa rumah (うちを借りる)
// ---------------------------------------------------------------------
const rentalTerms = [
  { ja: '〜線《せん》', name: { id: 'Nama jalur kereta', en: 'Train line name' }, sample: '〜線', note: { id: 'Biasanya ada di bagian paling atas iklan.', en: 'Usually at the top of the listing.' } },
  { ja: '〜駅《えき》', name: { id: 'Stasiun terdekat', en: 'Nearest station' }, sample: '〜駅', note: { id: 'Stasiun yang paling dekat dari rumah.', en: 'The station closest to the home.' } },
  { ja: '徒歩《とほ》〜分《ふん》', name: { id: 'Jalan kaki ~ menit dari stasiun', en: '~ minutes on foot from the station' }, sample: '徒歩《とほ》5分《ふん》', note: { id: 'Lima menit dengan berjalan kaki.', en: 'Five minutes walking.' } },
  { ja: 'マンション', name: { id: 'Kondominium／apartemen', en: 'Condominium／apartment building' }, sample: 'マンション', note: { id: 'アパート = bangunan kayu satu atau dua lantai; 一戸建《いっこだ》て = rumah tunggal.', en: 'アパート = wooden one- or two-storey building; 一戸建《いっこだ》て = detached house.' } },
  { ja: '築《ちく》〜年《ねん》', name: { id: 'Usia bangunan', en: 'Age of the building' }, sample: '築《ちく》3年《ねん》', note: { id: 'Didirikan tiga tahun yang lalu.', en: 'Built three years ago.' } },
  { ja: '家賃《やちん》', name: { id: 'Biaya sewa', en: 'Rent' }, sample: '8万《まん》5千円《せんえん》', note: { id: 'Dibayar setiap bulan.', en: 'Paid every month.' } },
  { ja: '敷金《しききん》', name: { id: 'Deposit (uang jaminan)', en: 'Security deposit' }, sample: '2か月分《げつぶん》', note: { id: 'Dititipkan kepada pemilik rumah; sebagian dikembalikan saat pindah.', en: 'Left with the landlord; part is returned when you move out.' } },
  { ja: '礼金《れいきん》', name: { id: 'Uang tanda terima kasih', en: 'Key money' }, sample: '1か月分《げつぶん》', note: { id: 'Dibayar kepada pemilik rumah saat mulai menyewa; tidak dikembalikan.', en: 'Paid to the landlord when you start renting; not returned.' } },
  { ja: '管理費《かんりひ》', name: { id: 'Biaya perawatan', en: 'Maintenance fee' }, sample: '1万《まん》2千円《せんえん》', note: { id: 'Biaya perawatan bagian bersama gedung.', en: 'Upkeep of the shared parts of the building.' } },
  { ja: '南向《みなみむ》き', name: { id: 'Menghadap ke selatan', en: 'Faces south' }, sample: '南向《みなみむ》き', note: { id: 'Biasanya terang dan hangat.', en: 'Usually bright and warm.' } },
  { ja: '〜階建《かいだ》ての〜階《かい》', name: { id: 'Gedung ~ tingkat, unit di tingkat ~', en: '~-storey building, unit on floor ~' }, sample: '10階建《かいだ》ての8階《かい》', note: { id: 'Gedung sepuluh tingkat, unit di tingkat delapan.', en: 'A ten-storey building, unit on the eighth floor.' } },
  { ja: '2LDK', name: { id: 'Denah: 2 kamar tidur + ruang keluarga + ruang makan + dapur', en: 'Layout: 2 bedrooms + living + dining + kitchen' }, sample: '2LDK', note: { id: 'L = living, D = dining, K = kitchen; angka di depan = jumlah kamar tidur.', en: 'L = living, D = dining, K = kitchen; the number in front = bedrooms.' } },
  { ja: '6畳《じょう》', name: { id: 'Luas ruangan 6 tatami', en: 'Room size of 6 tatami' }, sample: '6畳《じょう》', note: { id: '1畳《じょう》 = sebuah tatami (kira-kira 180 × 90 cm).', en: '1畳《じょう》 = one tatami mat (about 180 × 90 cm).' } },
  { ja: '〜まで 400m', name: { id: 'Jarak ke tempat terdekat (mis. supermarket)', en: 'Distance to a nearby place (e.g. supermarket)' }, sample: 'スーパーまで 400m', note: { id: 'Supermarket berjarak 400 meter.', en: 'The supermarket is 400 metres away.' } },
  { ja: '不動産《ふどうさん》', name: { id: 'Agen real estate', en: 'Real estate agency' }, sample: '〜不動産', note: { id: 'Nama dan nomor telepon agen biasanya ada di bagian bawah.', en: 'The agency name and phone number are usually at the bottom.' } },
]

const rentalTips = [
  { id: 'Biaya awal bukan hanya 家賃《やちん》: hitung juga 敷金《しききん》 dan 礼金《れいきん》 (kadang ditambah 管理費《かんりひ》).', en: 'Upfront cost is not just 家賃《やちん》: also count 敷金《しききん》 and 礼金《れいきん》 (sometimes plus 管理費《かんりひ》).' },
  { id: '〜か月分《げつぶん》 berarti "setara sewa ~ bulan"; 2か月分《げつぶん》 dari sewa 8万5千円 = 17万円.', en: '〜か月分《げつぶん》 means "the equivalent of ~ months of rent"; 2か月分《げつぶん》 of an 8万5千円 rent = 17万円.' },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 4 — Vて います (keadaan) & Vて しまいます
// ---------------------------------------------------------------------
const stateVerbs = [
  { dict: '開《あ》く', te: '開《あ》いて います', name: { id: 'terbuka', en: 'to be open' } },
  { dict: '閉《し》まる', te: '閉《し》まって います', name: { id: 'tertutup', en: 'to be closed' } },
  { dict: 'つく', te: 'ついて います', name: { id: 'menyala', en: 'to be on' } },
  { dict: '消《き》える', te: '消《き》えて います', name: { id: 'padam', en: 'to be off, gone out' } },
  { dict: '壊《こわ》れる', te: '壊《こわ》れて います', name: { id: 'rusak', en: 'to be broken' } },
  { dict: '割《わ》れる', te: '割《わ》れて います', name: { id: 'pecah', en: 'to be cracked, shattered' } },
  { dict: '折《お》れる', te: '折《お》れて います', name: { id: 'patah', en: 'to be snapped' } },
  { dict: '破《やぶ》れる', te: '破《やぶ》れて います', name: { id: 'robek', en: 'to be torn' } },
  { dict: '汚《よご》れる', te: '汚《よご》れて います', name: { id: 'kotor', en: 'to be dirty' } },
  { dict: '付《つ》く', te: '付《つ》いて います', name: { id: 'terpasang', en: 'to be attached' } },
  { dict: '外《はず》れる', te: '外《はず》れて います', name: { id: 'terlepas', en: 'to have come off' } },
  { dict: '止《と》まる', te: '止《と》まって います', name: { id: 'berhenti', en: 'to be stopped' } },
  { dict: '掛《か》かる', te: '掛《か》かって います', name: { id: 'terkunci', en: 'to be locked' } },
]

const teImasuCompare = [
  {
    use: { id: 'Aksi sedang berlangsung (N5)', en: 'Action in progress (N5)' },
    example: '本《ほん》を 読《よ》んで います。',
    meaning: { id: 'Sedang membaca buku.', en: 'I am reading a book.' },
    note: { id: 'Kata kerja aksi dengan pelaku', en: 'Action verb with an agent' },
  },
  {
    use: { id: 'Keadaan hasil kejadian (N4)', en: 'State resulting from an event (N4)' },
    example: 'コップが 割《わ》れて います。',
    meaning: { id: 'Gelasnya pecah.', en: 'The glass is broken.' },
    note: { id: 'Kata kerja perubahan keadaan, benda ditandai が', en: 'Change-of-state verb, object marked with が' },
  },
]

const shimauUses = [
  {
    use: { id: 'Penyelesaian', en: 'Completion' },
    form: '〜て しまいました／しまいます',
    example: '夕方《ゆうがた》までに 書類《しょるい》を 片《かた》づけて しまいます。',
    meaning: { id: 'Sampai sore, dokumen akan saya bereskan sampai selesai.', en: 'I will finish putting the documents in order by evening.' },
  },
  {
    use: { id: 'Penyesalan／kekecewaan', en: 'Regret／disappointment' },
    form: '〜て しまいました',
    example: '財布《さいふ》を 家《いえ》に 忘《わす》れて しまいました。',
    meaning: { id: 'Dompet saya tertinggal di rumah (sayang sekali).', en: 'I left my wallet at home (unfortunately).' },
  },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 4 — Keadaan & Kondisi (状態・様子)
// ---------------------------------------------------------------------
const conditionWords = [
  { ja: '太《ふと》って いる', name: { id: 'gemuk', en: 'fat, plump' }, example: '猫《ねこ》が 太《ふと》って います。', meaning: { id: 'Kucingnya gemuk.', en: 'The cat is plump.' } },
  { ja: 'やせて いる', name: { id: 'kurus', en: 'thin' }, example: '弟《おとうと》は やせて います。', meaning: { id: 'Adik laki-laki saya kurus.', en: 'My younger brother is thin.' } },
  { ja: '膨《ふく》らんで いる', name: { id: 'kembung', en: 'swollen, puffed up' }, example: 'かばんが 膨《ふく》らんで います。', meaning: { id: 'Tasnya menggembung.', en: 'The bag is bulging.' } },
  { ja: '穴《あな》が 開《あ》いて いる', name: { id: 'bolong', en: 'to have a hole' }, example: '靴下《くつした》に 穴《あな》が 開《あ》いて います。', meaning: { id: 'Kaus kakinya bolong.', en: 'The sock has a hole.' } },
  { ja: '曲《ま》がって いる', name: { id: 'bengkok', en: 'bent, crooked' }, example: 'この 木《き》は 曲《ま》がって います。', meaning: { id: 'Pohon ini bengkok.', en: 'This tree is crooked.' } },
  { ja: 'ゆがんで いる', name: { id: 'melengkung', en: 'distorted, warped' }, example: 'お茶碗《ちゃわん》が ゆがんで います。', meaning: { id: 'Mangkuknya melengkung (tidak bulat).', en: 'The bowl is warped.' } },
  { ja: 'へこんで いる', name: { id: 'penyok', en: 'dented' }, example: '缶《かん》が へこんで います。', meaning: { id: 'Kalengnya penyok.', en: 'The can is dented.' } },
  { ja: 'ねじれて いる', name: { id: 'terpelintir', en: 'twisted' }, example: 'ひもが ねじれて います。', meaning: { id: 'Talinya terpelintir.', en: 'The string is twisted.' } },
  { ja: '欠《か》けて いる', name: { id: 'sumbing', en: 'chipped' }, example: 'お皿《さら》が 欠《か》けて います。', meaning: { id: 'Piringnya sumbing.', en: 'The plate is chipped.' } },
  { ja: 'ひびが 入《はい》って いる', name: { id: 'retak', en: 'cracked' }, example: 'ガラスに ひびが 入《はい》って います。', meaning: { id: 'Kacanya retak.', en: 'The glass has a crack.' } },
  { ja: '腐《くさ》って いる', name: { id: 'busuk', en: 'rotten' }, example: 'この 魚《さかな》は 腐《くさ》って います。', meaning: { id: 'Ikan ini busuk.', en: 'This fish has gone bad.' } },
  { ja: '乾《かわ》いて いる', name: { id: 'kering', en: 'dry' }, example: '洗濯物《せんたくもの》は もう 乾《かわ》いて います。', meaning: { id: 'Cuciannya sudah kering.', en: 'The laundry is already dry.' } },
  { ja: 'ぬれて いる', name: { id: 'basah', en: 'wet' }, example: '道《みち》が ぬれて います。', meaning: { id: 'Jalannya basah.', en: 'The road is wet.' } },
  { ja: '凍《こお》って いる', name: { id: 'beku', en: 'frozen' }, example: '池《いけ》が 凍《こお》って います。', meaning: { id: 'Kolamnya membeku.', en: 'The pond is frozen.' } },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 5 — ringkasan 〜て います / 〜て あります / 〜て おきます
// ---------------------------------------------------------------------
const teCompare = [
  {
    pattern: '〜て います',
    use: { id: 'Keadaan apa adanya (kata kerja intransitif), tanpa menyebut siapa penyebabnya.', en: 'A state as it is (intransitive verb), without saying who caused it.' },
    example: 'ドアが 開《あ》いて います。',
    meaning: { id: 'Pintunya terbuka.', en: 'The door is open.' },
  },
  {
    pattern: '〜て あります',
    use: { id: 'Keadaan hasil perbuatan seseorang yang disengaja (kata kerja transitif).', en: 'A state resulting from someone\'s deliberate action (transitive verb).' },
    example: '窓《まど》が 開《あ》けて あります。',
    meaning: { id: 'Jendelanya sengaja dibuka.', en: 'The window has been left open on purpose.' },
  },
  {
    pattern: '〜て おきます',
    use: { id: 'Melakukan sesuatu lebih dulu sebagai persiapan, menyimpan untuk dipakai lagi, atau membiarkan keadaan begitu saja.', en: 'Doing something in advance as preparation, putting things away for next time, or leaving a state as it is.' },
    example: '会議《かいぎ》の 前《まえ》に、資料《しりょう》を 読《よ》んで おきます。',
    meaning: { id: 'Sebelum rapat, saya membaca materi lebih dulu.', en: 'Before the meeting, I read the materials beforehand.' },
  },
]

const tePairs = [
  { state: '開《あ》く', action: '開《あ》ける', meaning: { id: 'terbuka ／ membuka', en: 'to open (itself) ／ to open (something)' } },
  { state: '閉《し》まる', action: '閉《し》める', meaning: { id: 'tertutup ／ menutup', en: 'to close (itself) ／ to close (something)' } },
  { state: 'つく', action: 'つける', meaning: { id: 'menyala ／ menyalakan', en: 'to turn on (itself) ／ to turn on (something)' } },
  { state: '消《き》える', action: '消《け》す', meaning: { id: 'padam ／ memadamkan', en: 'to go out ／ to put out' } },
  { state: '入《はい》る', action: '入《い》れる', meaning: { id: 'masuk ／ memasukkan', en: 'to enter ／ to put in' } },
  { state: '決《き》まる', action: '決《き》める', meaning: { id: 'menjadi ditentukan ／ menentukan', en: 'to be decided ／ to decide' } },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 5 — Apabila Saat Darurat (非常《ひじょう》の 場合《ばあい》)
// ---------------------------------------------------------------------
const emergencyGroups = [
  {
    title: { id: 'Gempa bumi — persiapan (備《そな》え)', en: 'Earthquake — preparing (備《そな》え)' },
    steps: [
      { ja: '家具《かぐ》が 倒《たお》れないように しておく', id: 'Mencegah perabot roboh', en: 'Make sure furniture cannot fall over' },
      { ja: '消火器《しょうかき》を 備《そな》える', id: 'Menyiapkan alat pemadam api', en: 'Keep a fire extinguisher' },
      { ja: '水《みず》を 貯《たくわ》えて おく', id: 'Menyimpan persediaan air', en: 'Store water' },
      { ja: '非常袋《ひじょうぶくろ》を 用意《ようい》して おく', id: 'Menyiapkan kantong darurat', en: 'Prepare an emergency bag' },
      { ja: '避難場所《ひなんばしょ》を 確認《かくにん》して おく', id: 'Memeriksa tempat pengungsian di daerahmu', en: 'Check the evacuation site in your area' },
      { ja: '家族《かぞく》と 連絡先《れんらくさき》を 決《き》めて おく', id: 'Menentukan alamat kontak darurat dengan keluarga (juga kenalan dan teman)', en: 'Agree on emergency contacts with family (and acquaintances and friends)' },
    ],
  },
  {
    title: { id: 'Gempa bumi — saat terjadi', en: 'Earthquake — while it is happening' },
    steps: [
      { ja: '丈夫《じょうぶ》な テーブルの 下《した》に もぐる', id: 'Berlindung di bawah meja yang kuat', en: 'Get under a sturdy table' },
      { ja: '落《お》ち着《つ》いて 火《ひ》を 消《け》す', id: 'Mematikan api dengan tenang', en: 'Calmly turn off any flame' },
      { ja: '戸《と》を 開《あ》けて 出口《でぐち》を 確保《かくほ》する', id: 'Membuka pintu agar jalan keluar aman', en: 'Open a door to keep an exit' },
      { ja: 'あわてて 外《そと》へ 飛《と》び出《だ》さない', id: 'Tidak berlari keluar dengan tergesa-gesa', en: 'Do not rush outside in a panic' },
    ],
  },
  {
    title: { id: 'Gempa bumi — setelah berhenti dan saat mengungsi', en: 'Earthquake — afterwards and evacuating' },
    steps: [
      { ja: '正《ただ》しい 情報《じょうほう》を 聞《き》く', id: 'Mendengarkan informasi yang tepat', en: 'Listen for accurate information' },
      { ja: '山崩《やまくず》れ、崖崩《がけくず》れ、津波《つなみ》に 注意《ちゅうい》する', id: 'Waspada longsor gunung, longsor tebing, dan tsunami', en: 'Watch out for landslides, cliff collapses and tsunami' },
      { ja: '車《くるま》を 使《つか》わず、歩《ある》いて 避難《ひなん》する', id: 'Mengungsi dengan berjalan kaki, tidak memakai mobil', en: 'Evacuate on foot, not by car' },
    ],
  },
  {
    title: { id: 'Angin topan (台風《たいふう》)', en: 'Typhoon (台風《たいふう》)' },
    steps: [
      { ja: '気象情報《きしょうじょうほう》を 聞《き》く', id: 'Mendengarkan informasi cuaca', en: 'Listen to the weather information' },
      { ja: '家《いえ》の 周《まわ》りを 点検《てんけん》する', id: 'Memeriksa sekitar rumah', en: 'Check around the house' },
      { ja: 'ラジオの 電池《でんち》を 用意《ようい》して おく', id: 'Menyiapkan baterai radio', en: 'Have batteries ready for the radio' },
      { ja: '水《みず》と 非常食《ひじょうしょく》を 準備《じゅんび》する', id: 'Menyiapkan air dan makanan darurat', en: 'Prepare water and emergency food' },
    ],
  },
]

const emergencyBag = [
  { ja: '貴重品《きちょうひん》', id: 'Barang berharga', en: 'Valuables' },
  { ja: '救急薬品《きゅうきゅうやくひん》', id: 'Obat-obatan pertolongan pertama', en: 'First-aid medicine' },
  { ja: '懐中電灯《かいちゅうでんとう》', id: 'Senter', en: 'Flashlight' },
  { ja: 'ラジオ', id: 'Radio', en: 'Radio' },
  { ja: '水《みず》', id: 'Air minum', en: 'Drinking water' },
  { ja: '非常食《ひじょうしょく》', id: 'Makanan darurat (mis. makanan kaleng)', en: 'Emergency food (e.g. canned food)' },
  { ja: '軍手《ぐんて》', id: 'Sarung tangan kerja', en: 'Work gloves' },
  { ja: '下着《したぎ》', id: 'Pakaian dalam', en: 'Underwear' },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 6 — ringkasan bentuk maksud (意向形)
// ---------------------------------------------------------------------
const volitionalGroups = [
  {
    type: { id: 'Kelompok I', en: 'Group I' },
    rule: { id: 'Bunyi terakhir sebelum ます: baris い → baris お, lalu + う', en: 'Last sound before ます: い-row → お-row, then + う' },
    masu: '書きます ／ 読みます',
    example: '書こう ／ 読もう',
  },
  {
    type: { id: 'Kelompok II', en: 'Group II' },
    rule: { id: 'Buang ます, lalu + よう', en: 'Drop ます, then + よう' },
    masu: '食べます ／ 見ます',
    example: '食べよう ／ 見よう',
  },
  {
    type: { id: 'Kelompok III', en: 'Group III' },
    rule: { id: 'Tidak beraturan', en: 'Irregular' },
    masu: 'します ／ 来《き》ます',
    example: 'しよう ／ 来《こ》よう',
  },
]

// [bentuk kamus, bentuk maksud, contoh kamus → maksud]
const volitionalSounds = [
  ['う', 'おう', '買う → 買おう'],
  ['く', 'こう', '書く → 書こう'],
  ['ぐ', 'ごう', '急ぐ → 急ごう'],
  ['す', 'そう', '話す → 話そう'],
  ['つ', 'とう', '待つ → 待とう'],
  ['ぬ', 'のう', '死ぬ → 死のう'],
  ['ぶ', 'ぼう', '遊ぶ → 遊ぼう'],
  ['む', 'もう', '読む → 読もう'],
  ['る', 'ろう', '帰る → 帰ろう'],
]

const volitionalUses = [
  {
    pattern: 'Ｖ(よ)う',
    use: { id: 'Mengajak lawan bicara yang akrab ("mari …").', en: 'Inviting someone close to you ("let us …").' },
    example: '日曜日《にちようび》に 映画《えいが》を 見《み》に 行《い》こう。',
    meaning: { id: 'Mari nonton film hari Minggu.', en: 'Let us go and see a film on Sunday.' },
  },
  {
    pattern: 'Ｖ(よ)うか',
    use: { id: 'Menawarkan bantuan atau mengusulkan ("biar aku … ?", "bagaimana kalau … ?"). Bentuk sopannya 〜ましょうか.', en: 'Offering help or suggesting ("shall I … ?", "how about … ?"). The polite form is 〜ましょうか.' },
    example: '荷物《にもつ》を 持《も》とうか。',
    meaning: { id: 'Biar kubawakan barangnya?', en: 'Shall I carry your luggage?' },
  },
  {
    pattern: 'Ｖ(よ)う と 思《おも》っています',
    use: { id: 'Niat yang masih dipikirkan; bisa untuk orang ketiga. 〜と思います hanya untuk pembicara.', en: 'An intention still being considered; usable for a third person. 〜と思います is for the speaker only.' },
    example: '来月《らいげつ》、休《やす》みを 取《と》ろうと 思《おも》っています。',
    meaning: { id: 'Bulan depan saya berpikir untuk mengambil cuti.', en: 'Next month I am thinking of taking some time off.' },
  },
  {
    pattern: 'Ｖる／Ｖない つもりです',
    use: { id: 'Niat yang sudah bulat (positif dan negatif).', en: 'A firm intention (affirmative and negative).' },
    example: 'お酒《さけ》は もう 飲《の》まない つもりです。',
    meaning: { id: 'Saya bermaksud tidak minum minuman keras lagi.', en: 'I intend not to drink alcohol any more.' },
  },
  {
    pattern: 'Ｖる／Ｎの 予定《よてい》です',
    use: { id: 'Rencana atau jadwal yang sudah diatur.', en: 'A plan or schedule that has been arranged.' },
    example: '結婚式《けっこんしき》は 六月《ろくがつ》の 予定《よてい》です。',
    meaning: { id: 'Upacara pernikahan dijadwalkan bulan Juni.', en: 'The wedding is planned for June.' },
  },
  {
    pattern: 'まだ Ｖて いません',
    use: { id: 'Belum dilakukan / belum selesai; jawaban untuk もう 〜ましたか.', en: 'Not done yet / not finished; the answer to もう 〜ましたか.' },
    example: '作文《さくぶん》は まだ 出《だ》して いません。',
    meaning: { id: 'Karangannya belum saya kumpulkan.', en: 'I have not handed in the essay yet.' },
  },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 6 — Bidang Keilmuan (専門《せんもん》)
// [tulisan, bacaan, { id, en }]
// ---------------------------------------------------------------------
const fieldGroups = [
  {
    title: { id: 'Ilmu alam', en: 'Natural sciences' },
    items: [
      ['化学', 'かがく', { id: 'ilmu kimia', en: 'chemistry' }],
      ['生化学', 'せいかがく', { id: 'ilmu biokimia', en: 'biochemistry' }],
      ['生物学', 'せいぶつがく', { id: 'ilmu biologi', en: 'biology' }],
      ['農学', 'のうがく', { id: 'ilmu pertanian', en: 'agriculture' }],
      ['地学', 'ちがく', { id: 'ilmu geologi', en: 'geology' }],
      ['地理学', 'ちりがく', { id: 'ilmu geografi', en: 'geography' }],
      ['数学', 'すうがく', { id: 'ilmu matematika', en: 'mathematics' }],
      ['物理学', 'ぶつりがく', { id: 'ilmu fisika', en: 'physics' }],
      ['天文学', 'てんもんがく', { id: 'ilmu astronomi', en: 'astronomy' }],
      ['環境科学', 'かんきょうかがく', { id: 'ilmu tata lingkungan', en: 'environmental science' }],
    ],
  },
  {
    title: { id: 'Kedokteran dan kesehatan', en: 'Medicine and health' },
    items: [
      ['医学', 'いがく', { id: 'ilmu kedokteran', en: 'medicine' }],
      ['薬学', 'やくがく', { id: 'ilmu farmasi', en: 'pharmacy' }],
      ['体育学', 'たいいくがく', { id: 'ilmu pendidikan jasmani', en: 'physical education' }],
    ],
  },
  {
    title: { id: 'Teknik', en: 'Engineering' },
    items: [
      ['工学', 'こうがく', { id: 'ilmu teknik', en: 'engineering' }],
      ['土木工学', 'どぼくこうがく', { id: 'ilmu teknik sipil', en: 'civil engineering' }],
      ['電子工学', 'でんしこうがく', { id: 'ilmu teknik elektronik', en: 'electronic engineering' }],
      ['電気工学', 'でんきこうがく', { id: 'ilmu teknik listrik', en: 'electrical engineering' }],
      ['機械工学', 'きかいこうがく', { id: 'ilmu teknik mesin', en: 'mechanical engineering' }],
      ['コンピューター工学', 'コンピューターこうがく', { id: 'ilmu informatika', en: 'computer science' }],
      ['遺伝子工学', 'いでんしこうがく', { id: 'ilmu genetika', en: 'genetic engineering' }],
      ['建築学', 'けんちくがく', { id: 'ilmu arsitektur', en: 'architecture' }],
    ],
  },
  {
    title: { id: 'Ilmu sosial dan humaniora', en: 'Social sciences and humanities' },
    items: [
      ['政治学', 'せいじがく', { id: 'ilmu politik', en: 'political science' }],
      ['国際関係学', 'こくさいかんけいがく', { id: 'ilmu hubungan internasional', en: 'international relations' }],
      ['法律学', 'ほうりつがく', { id: 'ilmu hukum', en: 'law' }],
      ['経済学', 'けいざいがく', { id: 'ilmu ekonomi', en: 'economics' }],
      ['経営学', 'けいえいがく', { id: 'ilmu manajemen bisnis', en: 'business management' }],
      ['社会学', 'しゃかいがく', { id: 'ilmu sosial', en: 'sociology' }],
      ['教育学', 'きょういくがく', { id: 'ilmu pendidikan', en: 'education' }],
      ['文学', 'ぶんがく', { id: 'ilmu sastra', en: 'literature' }],
      ['言語学', 'げんごがく', { id: 'ilmu linguistik', en: 'linguistics' }],
      ['心理学', 'しんりがく', { id: 'ilmu psikologi', en: 'psychology' }],
      ['哲学', 'てつがく', { id: 'ilmu filsafat', en: 'philosophy' }],
      ['宗教学', 'しゅうきょうがく', { id: 'ilmu teologi', en: 'theology' }],
    ],
  },
  {
    title: { id: 'Seni', en: 'Arts' },
    items: [
      ['芸術', 'げいじゅつ', { id: 'ilmu seni', en: 'the arts' }],
      ['美術', 'びじゅつ', { id: 'ilmu seni murni', en: 'fine arts' }],
      ['音楽', 'おんがく', { id: 'ilmu seni musik', en: 'music' }],
    ],
  },
]

const fieldTips = [
  { id: 'Banyak nama bidang berakhiran 〜学《がく》 yang berarti "ilmu". Untuk menyebut bidangmu: 専門《せんもん》は 〜です。', en: 'Many field names end in 〜学《がく》, meaning "study of". To state your own field: 専門《せんもん》は 〜です。' },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 7 — saran & dugaan
// ---------------------------------------------------------------------
const adviceForms = [
  {
    type: { id: 'Menyarankan melakukan', en: 'Recommending an action' },
    form: 'Vた ＋ ほうが いいです',
    example: '早《はや》く 寝《ね》た ほうが いいです。',
    meaning: { id: 'Lebih baik tidur lebih awal.', en: 'You had better go to bed early.' },
  },
  {
    type: { id: 'Menyarankan tidak melakukan', en: 'Recommending against an action' },
    form: 'Vない ＋ ほうが いいです',
    example: '無理《むり》を しない ほうが いいです。',
    meaning: { id: 'Lebih baik jangan memaksakan diri.', en: 'You had better not push yourself.' },
  },
]

const guessForms = [
  {
    type: { id: 'Kata kerja', en: 'Verb' },
    deshou: '降《ふ》るでしょう',
    kamo: '降《ふ》るかもしれません',
    note: { id: 'Bentuk biasa apa adanya (juga ない, た)', en: 'Plain form as it is (also ない, た)' },
  },
  {
    type: { id: 'Kata sifat い', en: 'い-adjective' },
    deshou: '寒《さむ》いでしょう',
    kamo: '寒《さむ》いかもしれません',
    note: { id: 'Bentuk biasa apa adanya', en: 'Plain form as it is' },
  },
  {
    type: { id: 'Kata sifat な', en: 'な-adjective' },
    deshou: '静《しず》かでしょう',
    kamo: '静《しず》かかもしれません',
    note: { id: 'Tanpa だ', en: 'Without だ' },
  },
  {
    type: { id: 'Kata benda', en: 'Noun' },
    deshou: '雨《あめ》でしょう',
    kamo: '雨《あめ》かもしれません',
    note: { id: 'Tanpa だ', en: 'Without だ' },
  },
]

const certaintyLevels = [
  {
    pattern: 'きっと 〜でしょう',
    use: { id: 'Hampir pasti; pembicara sangat yakin.', en: 'Almost certain; the speaker is very sure.' },
    example: 'きっと 合格《ごうかく》するでしょう。',
    meaning: { id: 'Pasti lulus.', en: 'He will surely pass.' },
  },
  {
    pattern: '〜でしょう',
    use: { id: 'Dugaan yang cukup yakin.', en: 'A fairly confident guess.' },
    example: '午後《ごご》から 雨《あめ》が 降《ふ》るでしょう。',
    meaning: { id: 'Mulai siang mungkin hujan.', en: 'It will probably rain from the afternoon.' },
  },
  {
    pattern: '〜かもしれません',
    use: { id: 'Bisa jadi; kemungkinannya kecil dan pembicara tidak yakin.', en: 'Could be; the chance is small and the speaker is unsure.' },
    example: '約束《やくそく》の 時間《じかん》に 遅《おく》れるかもしれません。',
    meaning: { id: 'Mungkin saya terlambat dari waktu janji.', en: 'I might be late for the appointed time.' },
  },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 7 — Prakiraan Cuaca (天気予報)
// ---------------------------------------------------------------------
const weatherSymbols = [
  { icon: '☀️', ja: '晴《は》れ', meaning: { id: 'cerah', en: 'sunny' } },
  { icon: '☁️', ja: '曇《くも》り', meaning: { id: 'mendung／berawan', en: 'cloudy' } },
  { icon: '☔', ja: '雨《あめ》', meaning: { id: 'hujan', en: 'rain' } },
  { icon: '❄️', ja: '雪《ゆき》', meaning: { id: 'salju', en: 'snow' } },
  { icon: '🌤️', ja: '晴《は》れのち 曇《くも》り', meaning: { id: 'cerah kemudian berawan', en: 'sunny, then cloudy' } },
  { icon: '🌦️', ja: '曇《くも》り 時々《ときどき》 雨《あめ》', meaning: { id: 'berawan, kadang-kadang hujan', en: 'cloudy with occasional rain' } },
  { icon: '🌧️', ja: '曇《くも》り 所《ところ》に よって 雨《あめ》', meaning: { id: 'berawan, hujan di beberapa daerah', en: 'cloudy with rain in some areas' } },
]

const weatherTerms = [
  { ja: '降水確率《こうすいかくりつ》', meaning: { id: 'kemungkinan turun hujan', en: 'chance of precipitation' } },
  { ja: '最高気温《さいこうきおん》', meaning: { id: 'suhu udara tertinggi', en: 'highest temperature' } },
  { ja: '最低気温《さいていきおん》', meaning: { id: 'suhu udara terendah', en: 'lowest temperature' } },
  { ja: 'にわか雨《あめ》／夕立《ゆうだち》', meaning: { id: 'hujan sebentar', en: 'sudden shower' } },
  { ja: '雷《かみなり》', meaning: { id: 'guntur', en: 'thunder' } },
  { ja: '台風《たいふう》', meaning: { id: 'angin topan', en: 'typhoon' } },
  { ja: '虹《にじ》', meaning: { id: 'pelangi', en: 'rainbow' } },
  { ja: '風《かぜ》', meaning: { id: 'angin', en: 'wind' } },
  { ja: '雲《くも》', meaning: { id: 'awan', en: 'cloud' } },
  { ja: '湿度《しつど》', meaning: { id: 'kelembaban', en: 'humidity' } },
  { ja: '蒸《む》し暑《あつ》い', meaning: { id: 'panas lembab', en: 'hot and humid' } },
  { ja: 'さわやか[な]', meaning: { id: 'segar', en: 'refreshing' } },
]

const regions = [
  { ja: '北海道地方《ほっかいどうちほう》', city: '札幌《さっぽろ》', name: { id: 'Daerah Hokkaido', en: 'Hokkaido region' } },
  { ja: '東北地方《とうほくちほう》', city: '仙台《せんだい》', name: { id: 'Daerah Tohoku', en: 'Tohoku region' } },
  { ja: '関東地方《かんとうちほう》', city: '東京《とうきょう》', name: { id: 'Daerah Kanto', en: 'Kanto region' } },
  { ja: '中部地方《ちゅうぶちほう》', city: '長野《ながの》、名古屋《なごや》', name: { id: 'Daerah Chubu', en: 'Chubu region' } },
  { ja: '近畿地方《きんきちほう》', city: '大阪《おおさか》', name: { id: 'Daerah Kinki', en: 'Kinki region' } },
  { ja: '中国地方《ちゅうごくちほう》', city: '松江《まつえ》', name: { id: 'Daerah Chugoku', en: 'Chugoku region' } },
  { ja: '四国地方《しこくちほう》', city: '高知《こうち》', name: { id: 'Daerah Shikoku', en: 'Shikoku region' } },
  { ja: '九州地方《きゅうしゅうちほう》', city: '鹿児島《かごしま》', name: { id: 'Daerah Kyushu', en: 'Kyushu region' } },
]

const directions = [
  { ja: '東《ひがし》', meaning: { id: 'timur', en: 'east' } },
  { ja: '西《にし》', meaning: { id: 'barat', en: 'west' } },
  { ja: '南《みなみ》', meaning: { id: 'selatan', en: 'south' } },
  { ja: '北《きた》', meaning: { id: 'utara', en: 'north' } },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 8 — bentuk perintah dan larangan
// ---------------------------------------------------------------------
const imperativeRows = [
  {
    group: { id: 'Kelompok I', en: 'Group I' },
    rule: { id: 'Bunyi akhir ます-form kolom い → kolom え', en: 'Final い-column sound of the ます form → え-column' },
    dict: '書《か》く ／ 飲《の》む ／ 急《いそ》ぐ',
    imperative: '書《か》け ／ 飲《の》め ／ 急《いそ》げ',
    prohibitive: '書《か》くな ／ 飲《の》むな ／ 急《いそ》ぐな',
  },
  {
    group: { id: 'Kelompok II', en: 'Group II' },
    rule: { id: 'Buang ます, tambah ろ', en: 'Drop ます, add ろ' },
    dict: '食《た》べる ／ 見《み》る ／ 起《お》きる',
    imperative: '食《た》べろ ／ 見《み》ろ ／ 起《お》きろ',
    prohibitive: '食《た》べるな ／ 見《み》るな ／ 起《お》きるな',
  },
  {
    group: { id: 'Kelompok III', en: 'Group III' },
    rule: { id: 'Tidak beraturan', en: 'Irregular' },
    dict: 'する ／ 来《く》る',
    imperative: 'しろ ／ 来《こ》い',
    prohibitive: 'するな ／ 来《く》るな',
  },
  {
    group: { id: 'Pengecualian', en: 'Exception' },
    rule: { id: 'Kata kerja くれる', en: 'The verb くれる' },
    dict: 'くれる',
    imperative: 'くれ',
    prohibitive: 'くれるな',
  },
]

const imperativeUses = [
  {
    who: { id: 'Pria berposisi/berusia di atas, atau ayah kepada anak', en: 'A man of higher position or age, or a father to a child' },
    example: '早《はや》く 寝《ね》ろ。／ 遅《おく》れるな。',
    meaning: { id: 'Cepat tidur! ／ Jangan terlambat!', en: 'Go to bed! ／ Do not be late!' },
  },
  {
    who: { id: 'Sesama pria akrab (sering ditambah よ)', en: 'Between close male friends (often with よ)' },
    example: 'あした うちへ 来《こ》い[よ]。',
    meaning: { id: 'Besok datanglah ke rumahku!', en: 'Come to my house tomorrow!' },
  },
  {
    who: { id: 'Keadaan darurat, atau instruksi saat bekerja sama', en: 'Emergencies, or instructions during joint work' },
    example: '逃《に》げろ。／ エレベーターを 使《つか》うな。',
    meaning: { id: 'Berlarilah! ／ Jangan gunakan lift!', en: 'Run! ／ Do not use the elevator!' },
  },
  {
    who: { id: 'Latihan berkelompok atau pelajaran olahraga', en: 'Group training or PE class' },
    example: '休《やす》め。／ 休《やす》むな。',
    meaning: { id: 'Beristirahatlah! ／ Jangan beristirahat!', en: 'At ease! ／ Do not rest!' },
  },
  {
    who: { id: 'Menyoraki pertandingan (wanita pun memakainya)', en: 'Cheering at a match (women use it too)' },
    example: '頑張《がんば》れ。／ 負《ま》けるな。',
    meaning: { id: 'Semangat! ／ Jangan kalah!', en: 'Go for it! ／ Do not lose!' },
  },
  {
    who: { id: 'Tanda lalu lintas, slogan, tulisan yang butuh efek kuat', en: 'Traffic signs, slogans, writing that needs a strong effect' },
    example: '止《と》まれ。／ 入《はい》るな。',
    meaning: { id: 'Berhenti! ／ Dilarang masuk!', en: 'Stop! ／ No entry!' },
  },
  {
    who: { id: 'Bentuk 〜なさい: orang tua kepada anak, guru kepada murid (lebih halus; tidak untuk yang lebih tua)', en: '〜なさい: a parent to a child, a teacher to a student (gentler; never to someone older)' },
    example: '勉強《べんきょう》しなさい。',
    meaning: { id: 'Belajarlah!', en: 'Study!' },
  },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 8 — tanda-tanda (標識《ひょうしき》)
// ---------------------------------------------------------------------
const signGroups = [
  {
    title: { id: 'Tanda di toko dan tempat umum', en: 'Shop and public-place signs' },
    rows: [
      { ja: '営業中《えいぎょうちゅう》', meaning: { id: 'Buka', en: 'Open' } },
      { ja: '準備中《じゅんびちゅう》', meaning: { id: 'Dalam persiapan', en: 'Preparing to open' } },
      { ja: '閉店《へいてん》', meaning: { id: 'Tutup', en: 'Closed' } },
      { ja: '定休日《ていきゅうび》', meaning: { id: 'Hari libur', en: 'Regular day off' } },
      { ja: '化粧室《けしょうしつ》', meaning: { id: 'Kamar kecil / toilet / WC', en: 'Restroom' } },
      { ja: '禁煙席《きんえんせき》', meaning: { id: 'Tempat duduk bebas rokok', en: 'Non-smoking seat' } },
      { ja: '予約席《よやくせき》', meaning: { id: 'Tempat duduk yang telah dipesan', en: 'Reserved seat' } },
      { ja: '非常口《ひじょうぐち》', meaning: { id: 'Pintu darurat', en: 'Emergency exit' } },
    ],
  },
  {
    title: { id: 'Tanda peringatan', en: 'Warning signs' },
    rows: [
      { ja: '火気厳禁《かきげんきん》', meaning: { id: 'Mudah terbakar (jauhkan dari api)', en: 'Flammable (keep away from fire)' } },
      { ja: '割《わ》れ物《もの》注意《ちゅうい》', meaning: { id: 'Hati-hati barang pecah belah', en: 'Fragile, handle with care' } },
      { ja: '運転初心者注意《うんてんしょしんしゃちゅうい》', meaning: { id: 'Tanda bagi pengemudi yang baru mendapatkan SIM', en: 'Sign for a newly licensed driver' } },
      { ja: '工事中《こうじちゅう》', meaning: { id: 'Sedang konstruksi', en: 'Under construction' } },
    ],
  },
  {
    title: { id: 'Tanda pada label cucian', en: 'Laundry-label marks' },
    rows: [
      { ja: '塩素系漂白剤不可《えんそけいひょうはくざいふか》', meaning: { id: 'Jangan gunakan pemutih', en: 'Do not use bleach' } },
      { ja: '手洗《てあら》い', meaning: { id: 'Cuci dengan tangan', en: 'Hand wash' } },
      { ja: 'アイロン(低温《ていおん》)', meaning: { id: 'Harus disetrika dalam suhu rendah', en: 'Iron at a low temperature' } },
      { ja: 'ドライクリーニング', meaning: { id: 'Hanya untuk di-dry cleaning', en: 'Dry clean only' } },
    ],
  },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 9 — ringkasan とおりに / あとで / て・ないで
// ---------------------------------------------------------------------
const p9Patterns = [
  {
    pattern: 'V₁た とおりに、V₂',
    use: { id: 'V₂ dilakukan persis seperti V₁ yang sudah terjadi (meniru contoh).', en: 'V₂ is done exactly like the V₁ that already happened (copying a model).' },
    example: '先生《せんせい》が 書《か》いた とおりに、写《うつ》して ください。',
    meaning: { id: 'Salin persis seperti yang ditulis guru.', en: 'Copy it exactly as the teacher wrote it.' },
  },
  {
    pattern: 'N の とおりに、V',
    use: { id: 'Bertindak sesuai standar yang ditunjukkan benda (petunjuk, gambar, garis, peta).', en: 'Acting according to a standard shown by an object (manual, diagram, line, map).' },
    example: '図《ず》の とおりに、紙《かみ》を 折《お》って ください。',
    meaning: { id: 'Lipat kertasnya sesuai gambar.', en: 'Fold the paper as shown in the diagram.' },
  },
  {
    pattern: 'Vた あとで、〜 ／ N の あとで、〜',
    use: { id: 'Sesuatu terjadi setelah V atau setelah suatu kegiatan selesai.', en: 'Something happens after V, or after an activity is over.' },
    example: '会議《かいぎ》の あとで、昼《ひる》ごはんを 食《た》べませんか。',
    meaning: { id: 'Setelah rapat, maukah makan siang bersama?', en: 'After the meeting, shall we have lunch?' },
  },
  {
    pattern: 'V₁て、V₂',
    use: { id: 'V₁ adalah keadaan/cara yang menyertai V₂ (memakai atau menggunakan sesuatu).', en: 'V₁ is the state or manner that accompanies V₂ (using or wearing something).' },
    example: 'ソースを つけて 食《た》べて ください。',
    meaning: { id: 'Silakan makan dengan dibubuhi saus.', en: 'Please eat it with the sauce on.' },
  },
  {
    pattern: 'V₁ないで、V₂',
    use: { id: 'Melakukan V₂ tanpa V₁, atau memilih V₂ dan bukan V₁.', en: 'Doing V₂ without V₁, or choosing V₂ instead of V₁.' },
    example: 'バスに 乗《の》らないで、歩《ある》いて 行《い》きます。',
    meaning: { id: 'Tidak naik bus, melainkan berjalan kaki.', en: 'I walk instead of taking the bus.' },
  },
]

const p9Forms = [
  { group: 'I', dict: '書《か》く', ta: '書《か》いた', te: '書《か》いて', naide: '書《か》かないで' },
  { group: 'I', dict: '行《い》く', ta: '行《い》った', te: '行《い》って', naide: '行《い》かないで' },
  { group: 'II', dict: '食《た》べる', ta: '食《た》べた', te: '食《た》べて', naide: '食《た》べないで' },
  { group: 'III', dict: 'する', ta: 'した', te: 'して', naide: 'しないで' },
  { group: 'III', dict: '来《く》る', ta: '来《き》た', te: '来《き》て', naide: '来《こ》ないで' },
]

const p9Compare = [
  {
    pattern: '〜て から',
    nuance: { id: 'V₁ terasa sebagai dasar atau persiapan bagi V₂ ("lakukan ini dulu, baru itu").', en: 'V₁ feels like the basis or preparation for V₂ ("do this first, then that").' },
    example: '手《て》を 洗《あら》ってから、食《た》べます。',
  },
  {
    pattern: '〜た あとで',
    nuance: { id: 'Hanya menekankan urutan waktu sebelum–sesudah, tanpa kesan persiapan.', en: 'Only stresses the before–after order in time, without a sense of preparation.' },
    example: '食事《しょくじ》の あとで、散歩《さんぽ》します。',
  },
]

// N4 · Pelajaran 9 — Memasak (料理)
const cookingGroups = [
  {
    key: 'verbs',
    title: 'lampiran.n4_cook_verbs',
    wide: false,
    items: [
      { ja: '煮《に》る', meaning: { id: 'merebus, menyemur dalam kuah berbumbu', en: 'to simmer in seasoned broth' } },
      { ja: '焼《や》く', meaning: { id: 'membakar, memanggang', en: 'to grill, to bake' } },
      { ja: '揚《あ》げる', meaning: { id: 'menggoreng dengan minyak banyak', en: 'to deep-fry' } },
      { ja: 'いためる', meaning: { id: 'menumis', en: 'to stir-fry' } },
      { ja: 'ゆでる', meaning: { id: 'merebus dalam air (telur, mi, sayur)', en: 'to boil in water (eggs, noodles, vegetables)' } },
      { ja: '蒸《む》す', meaning: { id: 'mengukus', en: 'to steam' } },
      { ja: '炊《た》く', meaning: { id: 'menanak (nasi)', en: 'to cook (rice)' } },
      { ja: 'むく', meaning: { id: 'mengupas', en: 'to peel' } },
      { ja: '刻《きざ》む', meaning: { id: 'mencincang, mengiris halus', en: 'to chop finely' } },
      { ja: 'かき混《ま》ぜる', meaning: { id: 'mengaduk, mencampur', en: 'to stir, to mix' } },
    ],
  },
  {
    key: 'seasonings',
    title: 'lampiran.n4_cook_seasonings',
    wide: false,
    items: [
      { ja: 'しょうゆ', meaning: { id: 'kecap asin', en: 'soy sauce' } },
      { ja: '砂糖《さとう》', meaning: { id: 'gula', en: 'sugar' } },
      { ja: '塩《しお》', meaning: { id: 'garam', en: 'salt' } },
      { ja: '酢《す》', meaning: { id: 'cuka', en: 'vinegar' } },
      { ja: 'みそ', meaning: { id: 'miso (pasta kedelai fermentasi)', en: 'miso (fermented soybean paste)' } },
      { ja: '油《あぶら》', meaning: { id: 'minyak', en: 'oil' } },
      { ja: 'ソース', meaning: { id: 'saus (jenis Worcester)', en: 'Worcestershire-style sauce' } },
      { ja: 'マヨネーズ', meaning: { id: 'mayones', en: 'mayonnaise' } },
      { ja: 'ケチャップ', meaning: { id: 'saus tomat', en: 'ketchup' } },
      { ja: 'からし（マスタード）', meaning: { id: 'mustard', en: 'mustard' } },
      { ja: 'こしょう', meaning: { id: 'lada', en: 'pepper' } },
      { ja: 'とうがらし', meaning: { id: 'cabai', en: 'chili pepper' } },
      { ja: 'しょうが', meaning: { id: 'jahe', en: 'ginger' } },
      { ja: 'わさび', meaning: { id: 'wasabi (lobak hijau Jepang)', en: 'wasabi (Japanese horseradish)' } },
      { ja: 'カレー粉《こ》', meaning: { id: 'bubuk kari', en: 'curry powder' } },
    ],
  },
  {
    key: 'kitchenware',
    title: 'lampiran.n4_cook_kitchenware',
    wide: true,
    items: [
      { ja: 'なべ', meaning: { id: 'panci', en: 'pot' } },
      { ja: 'やかん', meaning: { id: 'ketel', en: 'kettle' } },
      { ja: 'ふた', meaning: { id: 'tutup', en: 'lid' } },
      { ja: 'おたま', meaning: { id: 'sendok sayur', en: 'ladle' } },
      { ja: 'まな板《いた》', meaning: { id: 'talenan', en: 'cutting board' } },
      { ja: '包丁《ほうちょう》', meaning: { id: 'pisau dapur', en: 'kitchen knife' } },
      { ja: 'ふきん', meaning: { id: 'kain lap', en: 'dish cloth' } },
      { ja: 'フライパン', meaning: { id: 'wajan datar, penggorengan', en: 'frying pan' } },
      { ja: '電子《でんし》レンジ', meaning: { id: 'microwave', en: 'microwave oven' } },
      { ja: '炊飯器《すいはんき》', meaning: { id: 'penanak nasi', en: 'rice cooker' } },
      { ja: 'しゃもじ', meaning: { id: 'centong nasi', en: 'rice paddle' } },
      { ja: '缶切《かんき》り', meaning: { id: 'pembuka kaleng', en: 'can opener' } },
      { ja: '栓抜《せんぬ》き', meaning: { id: 'pembuka botol', en: 'bottle opener' } },
      { ja: 'ざる', meaning: { id: 'saringan', en: 'strainer' } },
      { ja: 'ポット', meaning: { id: 'termos air panas', en: 'thermos pot' } },
      { ja: 'ガス台《だい》', meaning: { id: 'kompor gas', en: 'gas stove' } },
      { ja: '流《なが》し台《だい》', meaning: { id: 'bak cuci piring', en: 'kitchen sink' } },
      { ja: '換気扇《かんきせん》', meaning: { id: 'kipas penyedot udara', en: 'ventilation fan' } },
    ],
  },
]

const cookingTips = [
  { id: 'Urutan memasak mudah disusun dengan 〜た あとで: 野菜《やさい》を 刻《きざ》んだ あとで、油《あぶら》で いためます。', en: 'Cooking order is easy to build with 〜た あとで: 野菜《やさい》を 刻《きざ》んだ あとで、油《あぶら》で いためます。' },
  { id: 'Resep bisa dibaca sebagai 〜とおりに: レシピの とおりに 煮《に》て ください。', en: 'A recipe works as a 〜とおりに phrase: レシピの とおりに 煮《に》て ください。' },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 10 — bentuk syarat, perbandingan, pepatah
// ---------------------------------------------------------------------
const condForms = [
  {
    type: { id: 'Kata kerja kelompok I', en: 'Group I verb' },
    dict: '書《か》く ／ 飲《の》む ／ 話《はな》す ／ 買《か》う',
    cond: '書《か》けば ／ 飲《の》めば ／ 話《はな》せば ／ 買《か》えば',
    how: { id: 'Bunyi akhir kolom う → kolom え, lalu + ば', en: 'Last sound う-column → え-column, then + ば' },
  },
  {
    type: { id: 'Kata kerja kelompok II', en: 'Group II verb' },
    dict: '食《た》べる ／ 見《み》る ／ 起《お》きる',
    cond: '食《た》べれば ／ 見《み》れば ／ 起《お》きれば',
    how: { id: 'Buang る, lalu + れば', en: 'Drop る, then + れば' },
  },
  {
    type: { id: 'Kata kerja kelompok III', en: 'Group III verb' },
    dict: 'する ／ 来《く》る',
    cond: 'すれば ／ 来《く》れば',
    how: { id: 'Tidak beraturan (来る dibaca くれば)', en: 'Irregular (来る is read くれば)' },
  },
  {
    type: { id: 'Kata kerja negatif', en: 'Negative verb' },
    dict: '行《い》かない ／ 食《た》べない',
    cond: '行《い》かなければ ／ 食《た》べなければ',
    how: { id: 'ない → なければ', en: 'ない → なければ' },
  },
  {
    type: { id: 'Kata sifat い', en: 'い-adjective' },
    dict: '安《やす》い ／ 高《たか》くない ／ いい',
    cond: '安《やす》ければ ／ 高《たか》くなければ ／ よければ',
    how: { id: 'い → ければ (いい → よければ)', en: 'い → ければ (いい → よければ)' },
  },
  {
    type: { id: 'Kata sifat な', en: 'な-adjective' },
    dict: '静《しず》か(だ)',
    cond: '静《しず》かなら',
    how: { id: 'Hilangkan だ, tambah なら', en: 'Drop だ, add なら' },
  },
  {
    type: { id: 'Kata benda', en: 'Noun' },
    dict: '学生《がくせい》(だ)',
    cond: '学生《がくせい》なら',
    how: { id: 'Hilangkan だ, tambah なら', en: 'Drop だ, add なら' },
  },
]

const condCompare = [
  {
    pattern: '〜と',
    use: { id: 'Hasil pasti atau otomatis setelah suatu aksi. Bagian belakang tidak boleh berisi keinginan, harapan, perintah, permintaan.', en: 'A certain or automatic result after an action. The second part may not contain a wish, hope, command, or request.' },
    example: '冬《ふゆ》に なると、雪《ゆき》が 降《ふ》ります。',
    meaning: { id: 'Kalau musim dingin tiba, salju turun.', en: 'When winter comes, it snows.' },
  },
  {
    pattern: '〜ば',
    use: { id: 'Syarat yang diperlukan. Aturan bagian belakang mirip 〜と, lebih longgar bila subjek berbeda atau bagian awal berupa keadaan.', en: 'A necessary condition. The rule for the second part is like 〜と, but looser when subjects differ or the first part is a state.' },
    example: '安《やす》ければ、買《か》います。',
    meaning: { id: 'Kalau murah, saya beli.', en: 'If it is cheap, I will buy it.' },
  },
  {
    pattern: '〜たら',
    use: { id: 'Syarat, atau "setelah X selesai". Bagian belakang bebas (keinginan, perintah, permintaan). Paling luas, tetapi bersifat lisan.', en: 'A condition, or "after X is done". The second part is free (wishes, commands, requests). The widest use, but spoken in tone.' },
    example: '着《つ》いたら、電話《でんわ》して ください。',
    meaning: { id: 'Kalau sudah sampai, tolong telepon.', en: 'When you arrive, please call.' },
  },
  {
    pattern: 'Ｎ なら',
    use: { id: 'Menanggapi topik lawan bicara lalu memberi informasi atau saran yang berkaitan.', en: 'Responding to the other person topic and giving related information or advice.' },
    example: 'カメラなら、あの 店《みせ》が 安《やす》いですよ。',
    meaning: { id: 'Kalau kamera, toko itu murah.', en: 'If it is a camera, that shop is cheap.' },
  },
]

const proverbs = [
  {
    ja: '住《す》めば 都《みやこ》',
    literal: { id: 'Kalau tinggal, (tempat itu) jadi ibu kota.', en: 'If you live there, it becomes the capital.' },
    meaning: { id: 'Tempat seburuk apa pun akan terasa paling nyaman setelah lama ditinggali. Padanan: Alah bisa karena biasa.', en: 'Any place starts to feel like the best place once you have lived there for a while.' },
  },
  {
    ja: '三人《さんにん》 寄《よ》れば 文殊《もんじゅ》の 知恵《ちえ》',
    literal: { id: 'Kalau tiga orang berkumpul, ada kebijaksanaan Monju (tokoh kebijaksanaan).', en: 'When three people gather, there is the wisdom of Monju (a figure of wisdom).' },
    meaning: { id: 'Walau tak ada yang istimewa, berunding bersama menghasilkan gagasan bagus. Padanan: Dua kepala lebih baik daripada satu.', en: 'Even without a genius, putting heads together produces a good idea.' },
  },
  {
    ja: '立《た》てば 芍薬《しゃくやく》、座《すわ》れば 牡丹《ぼたん》、歩《ある》く 姿《すがた》は 百合《ゆり》の 花《はな》',
    literal: { id: 'Berdiri bagai peoni, duduk bagai peoni pohon, berjalan bagai bunga lili.', en: 'Standing like a peony, sitting like a tree peony, walking like a lily.' },
    meaning: { id: 'Pujian untuk perempuan cantik yang anggun dalam setiap gerakan.', en: 'Praise for a beautiful woman who is graceful in every movement.' },
  },
  {
    ja: 'ちりも 積《つ》もれば 山《やま》と なる',
    literal: { id: 'Debu pun kalau menumpuk menjadi gunung.', en: 'Even dust, if piled up, becomes a mountain.' },
    meaning: { id: 'Hal sekecil apa pun, bila terkumpul, menjadi besar. Padanan: Sedikit demi sedikit, lama-lama menjadi bukit.', en: 'Even tiny things add up to something big.' },
  },
  {
    ja: 'うわさを すれば 影《かげ》',
    literal: { id: 'Kalau membicarakan seseorang, bayangannya muncul.', en: 'If you talk about someone, their shadow appears.' },
    meaning: { id: 'Orang yang sedang dibicarakan sering tiba-tiba muncul. Padanan: Panjang umur.', en: 'The person being talked about often turns up right then.' },
  },
  {
    ja: '苦《く》あれば 楽《らく》あり、楽《らく》あれば 苦《く》あり',
    literal: { id: 'Kalau ada susah, ada senang; kalau ada senang, ada susah.', en: 'Where there is hardship there is ease; where there is ease there is hardship.' },
    meaning: { id: 'Hidup silih berganti suka dan duka. Padanan: Berakit-rakit ke hulu, berenang-renang ke tepian.', en: 'Life alternates between good times and bad.' },
  },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 11 — ringkasan pola ように
// ---------------------------------------------------------------------
const l11Patterns = [
  {
    pattern: 'Vる／Vない ように、V₂',
    form: { id: 'Tujuan: keadaan yang ingin dicapai (kata kerja potensial／わかる／なる／ない) + tindakan V₂.', en: 'Purpose: the state to reach (potential verbs／わかる／なる／ない) + the action V₂.' },
    example: '忘《わす》れないように、メモします。',
    meaning: { id: 'Supaya tidak lupa, saya mencatat.', en: 'So that I do not forget, I take notes.' },
  },
  {
    pattern: 'Vる ように なります',
    form: { id: 'Perubahan: jadi bisa (kata kerja potensial) atau jadi terbiasa (kata kerja lain).', en: 'Change: come to be able to (potential verbs) or come to do (other verbs).' },
    example: '日本語《にほんご》の ニュースが わかるように なりました。',
    meaning: { id: 'Sekarang saya bisa mengerti berita berbahasa Jepang.', en: 'I have come to understand the news in Japanese.' },
  },
  {
    pattern: 'Vる／Vない ように して います',
    form: { id: 'Berusaha secara terus-menerus (kebiasaan). ようにします = tekad ke depan.', en: 'Ongoing effort (a habit). ようにします = a resolution for the future.' },
    example: '毎日《まいにち》 野菜《やさい》を 食《た》べるように して います。',
    meaning: { id: 'Setiap hari saya berusaha makan sayur.', en: 'I try to eat vegetables every day.' },
  },
  {
    pattern: 'Vる／Vない ように して ください',
    form: { id: 'Permintaan halus (tidak langsung). Tidak untuk permintaan saat itu juga.', en: 'A gentle (indirect) request. Not for a request that is needed right now.' },
    example: '約束《やくそく》の 時間《じかん》に 遅《おく》れないように して ください。',
    meaning: { id: 'Usahakan jangan terlambat pada waktu janji.', en: 'Please try not to be late for the appointment.' },
  },
  {
    pattern: 'い形 → 〜く ／ な形 → 〜に',
    form: { id: 'Kata sifat yang menerangkan kata kerja／kata sifat lain berubah menjadi kata keterangan.', en: 'An adjective that describes a verb／another adjective becomes an adverb.' },
    example: '早《はや》く 上手《じょうず》に 話《はな》せるように なりたいです。',
    meaning: { id: 'Saya ingin cepat pandai berbicara.', en: 'I want to become able to speak well, quickly.' },
  },
]

const adverbForms = [
  { type: { id: 'Kata sifat い', en: 'い-adjective' }, base: '早《はや》い', adverb: '早《はや》く', meaning: { id: 'dengan cepat／awal', en: 'quickly／early' } },
  { type: { id: 'Kata sifat い', en: 'い-adjective' }, base: '大《おお》きい', adverb: '大《おお》きく', meaning: { id: 'dengan besar／keras', en: 'big／loudly' } },
  { type: { id: 'Kata sifat い (khusus)', en: 'い-adjective (irregular)' }, base: 'いい', adverb: 'よく', meaning: { id: 'dengan baik', en: 'well' } },
  { type: { id: 'Kata sifat な', en: 'な-adjective' }, base: '上手《じょうず》な', adverb: '上手《じょうず》に', meaning: { id: 'dengan pandai', en: 'skillfully' } },
  { type: { id: 'Kata sifat な', en: 'な-adjective' }, base: '静《しず》かな', adverb: '静《しず》かに', meaning: { id: 'dengan tenang', en: 'quietly' } },
  { type: { id: 'Kata sifat な', en: 'な-adjective' }, base: '自由《じゆう》な', adverb: '自由《じゆう》に', meaning: { id: 'dengan bebas', en: 'freely' } },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 11 — 健康 (Kesehatan)
// ---------------------------------------------------------------------
const goodHabits = [
  { ja: '規則《きそく》正《ただ》しい 生活《せいかつ》を する', meaning: { id: 'hidup secara teratur', en: 'to live a regular life' } },
  { ja: '早寝《はやね》、早起《はやお》きを する', meaning: { id: 'cepat tidur dan bangun pagi-pagi', en: 'to go to bed and get up early' } },
  { ja: '運動《うんどう》する／スポーツを する', meaning: { id: 'berolahraga', en: 'to exercise／play sports' } },
  { ja: 'よく 歩《ある》く', meaning: { id: 'suka berjalan kaki', en: 'to walk a lot' } },
  { ja: '好《す》き嫌《きら》いが ない', meaning: { id: 'tidak pilih makan', en: 'not to be a picky eater' } },
  { ja: '栄養《えいよう》の バランスを 考《かんが》えて 食《た》べる', meaning: { id: 'makan dengan mempertimbangkan keseimbangan gizi', en: 'to eat with a balanced diet in mind' } },
  { ja: '健康診断《けんこうしんだん》を 受《う》ける', meaning: { id: 'mengikuti pemeriksaan kesehatan', en: 'to have a health check-up' } },
]

const badHabits = [
  { ja: '夜更《よふ》かしを する', meaning: { id: 'begadang', en: 'to stay up late' } },
  { ja: 'あまり 運動《うんどう》しない', meaning: { id: 'kurang berolahraga', en: 'not to exercise much' } },
  { ja: '好《す》き嫌《きら》いが ある', meaning: { id: 'pilih makan', en: 'to be a picky eater' } },
  { ja: 'よく インスタント食品《しょくひん》を 食《た》べる', meaning: { id: 'suka makan makanan instan', en: 'to eat instant food often' } },
  { ja: '外食《がいしょく》が 多《おお》い', meaning: { id: 'sering makan di luar', en: 'to eat out often' } },
  { ja: 'たばこを 吸《す》う', meaning: { id: 'merokok', en: 'to smoke' } },
  { ja: 'よく お酒《さけ》を 飲《の》む', meaning: { id: 'suka minum minuman keras', en: 'to drink alcohol often' } },
]

const nutrients = [
  { ja: '炭水化物《たんすいかぶつ》', name: { id: 'Karbohidrat', en: 'Carbohydrates' }, foods: { id: 'ご飯《はん》, パン, いも (kentang／ubi)', en: 'ご飯《はん》 (rice), パン (bread), いも (potatoes)' } },
  { ja: '脂肪《しぼう》', name: { id: 'Lemak', en: 'Fats' }, foods: { id: 'バター (mentega), マーガリン, 油《あぶら》 (minyak)', en: 'バター (butter), マーガリン (margarine), 油《あぶら》 (oil)' } },
  { ja: 'たんぱく質《しつ》', name: { id: 'Protein', en: 'Protein' }, foods: { id: '肉《にく》 (daging), 魚《さかな》 (ikan), 卵《たまご》 (telur), 豆《まめ》 (kacang-kacangan), とうふ (tahu)', en: '肉《にく》 (meat), 魚《さかな》 (fish), 卵《たまご》 (eggs), 豆《まめ》 (beans), とうふ (tofu)' } },
  { ja: 'カルシウム', name: { id: 'Kalsium', en: 'Calcium' }, foods: { id: '牛乳《ぎゅうにゅう》 (susu), チーズ, 小魚《こざかな》 (ikan kecil), 海草《かいそう》 (rumput laut), のり', en: '牛乳《ぎゅうにゅう》 (milk), チーズ (cheese), 小魚《こざかな》 (small fish), 海草《かいそう》 (seaweed), のり (nori)' } },
  { ja: 'ビタミン', name: { id: 'Vitamin', en: 'Vitamins' }, foods: { id: '野菜《やさい》 (sayur), 果物《くだもの》 (buah)', en: '野菜《やさい》 (vegetables), 果物《くだもの》 (fruit)' } },
]
// ---------------------------------------------------------------------
// N4 · Pelajaran 12 — Kata Kerja Pasif (受身形)
// ---------------------------------------------------------------------
const passiveForms = [
  { group: 'I', dict: '書《か》く', polite: '書《か》かれます', plain: '書《か》かれる', note: { id: 'Akhiran → baris あ + れる', en: 'Final kana → あ row + れる' } },
  { group: 'I', dict: '買《か》う', polite: '買《か》われます', plain: '買《か》われる', note: { id: 'う → わ + れる', en: 'う → わ + れる' } },
  { group: 'I', dict: '話《はな》す', polite: '話《はな》されます', plain: '話《はな》される', note: { id: 'す → さ + れる', en: 'す → さ + れる' } },
  { group: 'I', dict: '呼《よ》ぶ', polite: '呼《よ》ばれます', plain: '呼《よ》ばれる', note: { id: 'ぶ → ば + れる', en: 'ぶ → ば + れる' } },
  { group: 'II', dict: '褒《ほ》める', polite: '褒《ほ》められます', plain: '褒《ほ》められる', note: { id: 'る → られる', en: 'る → られる' } },
  { group: 'II', dict: '食《た》べる', polite: '食《た》べられます', plain: '食《た》べられる', note: { id: 'る → られる', en: 'る → られる' } },
  { group: 'III', dict: '来《く》る', polite: '来《こ》られます', plain: '来《こ》られる', note: { id: 'Tidak beraturan (sama dengan potensial)', en: 'Irregular (same as the potential)' } },
  { group: 'III', dict: 'する', polite: 'されます', plain: 'される', note: { id: 'Tidak beraturan', en: 'Irregular' } },
]

const passivePatterns = [
  {
    active: '先生《せんせい》が わたしを 褒《ほ》めました。',
    passive: 'わたしは 先生《せんせい》に 褒《ほ》められました。',
    note: { id: 'Ｎ１は Ｎ２に Ｖ-pasif — orang yang dikenai perbuatan jadi topik', en: 'Ｎ１は Ｎ２に passive-V — the affected person is the topic' },
  },
  {
    active: '弟《おとうと》が わたしの 本《ほん》を 汚《よご》しました。',
    passive: 'わたしは 弟《おとうと》に 本《ほん》を 汚《よご》されました。',
    note: { id: 'Ｎ１は Ｎ２に Ｎ３を Ｖ-pasif — barang milik; terasa terganggu', en: 'Ｎ１は Ｎ２に Ｎ３を passive-V — your belongings; feels inconvenient' },
  },
  {
    active: '毎年《まいとし》 十月《じゅうがつ》に マラソン大会《たいかい》を 行《おこな》います。',
    passive: '毎年《まいとし》 十月《じゅうがつ》に マラソン大会《たいかい》が 行《おこな》われます。',
    note: { id: 'Ｎが Ｖ-pasif — pelaku tidak disebut', en: 'Ｎが passive-V — no agent mentioned' },
  },
  {
    active: '麦《むぎ》で ビールを 作《つく》ります。',
    passive: 'ビールは 麦《むぎ》から 作《つく》られます。',
    note: { id: 'Bahan: から (bentuk berubah) ／ で (masih tampak)', en: 'Material: から (form changes) ／ で (still visible)' },
  },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 12 — Kecelakaan dan Kejadian (事故《じこ》・事件《じけん》)
// ---------------------------------------------------------------------
const incidentGroups = [
  {
    ja: '人《ひと》や 動物《どうぶつ》に 関《かん》する 事件《じけん》',
    name: { id: 'Kejahatan dan serangan', en: 'Crimes and attacks' },
    items: [
      { ja: '殺《ころ》す', meaning: { id: 'Membunuh', en: 'To kill' } },
      { ja: '撃《う》つ', meaning: { id: 'Menembak', en: 'To shoot' } },
      { ja: '刺《さ》す', meaning: { id: 'Menikam', en: 'To stab' } },
      { ja: 'かむ', meaning: { id: 'Menggigit', en: 'To bite' } },
      { ja: '盗《ぬす》む', meaning: { id: 'Mencuri', en: 'To steal' } },
      { ja: '誘拐《ゆうかい》する', meaning: { id: 'Menculik', en: 'To kidnap' } },
      { ja: 'ハイジャックする', meaning: { id: 'Membajak (pesawat)', en: 'To hijack' } },
    ],
  },
  {
    ja: '事故《じこ》',
    name: { id: 'Kecelakaan dan bencana', en: 'Accidents and disasters' },
    items: [
      { ja: 'ひく', meaning: { id: 'Melindas', en: 'To run over' } },
      { ja: 'はねる', meaning: { id: 'Menabrak (hingga terpental)', en: 'To hit (and knock away)' } },
      { ja: '衝突《しょうとつ》する', meaning: { id: 'Menabrak, bertabrakan', en: 'To collide' } },
      { ja: '追突《ついとつ》する', meaning: { id: 'Menabrak dari belakang', en: 'To rear-end' } },
      { ja: '墜落《ついらく》する', meaning: { id: 'Jatuh (pesawat)', en: 'To crash, to fall' } },
      { ja: '爆発《ばくはつ》する', meaning: { id: 'Meledak', en: 'To explode' } },
      { ja: '沈没《ちんぼつ》する', meaning: { id: 'Tenggelam (kapal)', en: 'To sink' } },
      { ja: '運《はこ》ぶ', meaning: { id: 'Mengangkut', en: 'To carry, to transport' } },
      { ja: '助《たす》ける', meaning: { id: 'Membantu, menolong', en: 'To help, to rescue' } },
    ],
  },
]
// ---------------------------------------------------------------------
// N4 · Pelajaran 13 — ringkasan の sebagai pembentuk kata benda
// ---------------------------------------------------------------------
const nominalPatterns = [
  {
    pattern: 'Ｖ(kamus)のは Adj です',
    adjectives: 'むずかしい, やさしい, おもしろい, たのしい, たいへん[な]',
    example: '漢字《かんじ》を 覚《おぼ》えるのは むずかしいです。',
    meaning: { id: 'Menghafal kanji itu sulit.', en: 'Memorising kanji is difficult.' },
  },
  {
    pattern: 'Ｖ(kamus)のが Adj です',
    adjectives: '好《す》き[な], 嫌《きら》い[な], 上手《じょうず》[な], 下手《へた》[な], 速《はや》い, 遅《おそ》い',
    example: '料理《りょうり》を 作《つく》るのが 好《す》きです。',
    meaning: { id: 'Saya suka memasak.', en: 'I like cooking.' },
  },
  {
    pattern: 'Ｖ(kamus)のを 忘《わす》れました',
    adjectives: '—',
    example: '電気《でんき》を 消《け》すのを 忘《わす》れました。',
    meaning: { id: 'Saya lupa mematikan lampu.', en: 'I forgot to turn off the light.' },
  },
  {
    pattern: 'ふつうけい のを 知《し》って いますか',
    adjectives: '—',
    example: '来週《らいしゅう》 試験《しけん》が あるのを 知《し》って いますか。',
    meaning: { id: 'Apakah kamu tahu bahwa minggu depan ada ujian?', en: 'Do you know there is an exam next week?' },
  },
  {
    pattern: 'ふつうけい のは Ｎ です',
    adjectives: '—',
    example: '初《はじ》めて 来《き》たのは いつですか。',
    meaning: { id: 'Kapan pertama kali datang?', en: 'When did you first come?' },
  },
]

const shiranaiAnswers = [
  {
    answer: '知《し》りません',
    use: { id: 'Memang tidak tahu sejak sebelum ditanya (pertanyaan tentang hal biasa, mis. alamat, nomor telepon).', en: 'You did not know even before being asked (a question about a plain thing, e.g. an address or phone number).' },
    example: '先生《せんせい》の 電話《でんわ》番号《ばんごう》を 知《し》って いますか。……いいえ、知《し》りません。',
  },
  {
    answer: '知《し》りませんでした',
    use: { id: 'Kabarnya baru kamu terima dari pertanyaan itu: \"sampai tadi saya tidak tahu\".', en: 'You learn the news from the question itself: "until just now I did not know".' },
    example: '駅前《えきまえ》に 新《あたら》しい 店《みせ》が できたのを 知《し》って いますか。……いいえ、知《し》りませんでした。',
  },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 13 — 位置 (letak／posisi)
// ---------------------------------------------------------------------
const positionWords = [
  {
    ja: '上《うえ》から 二段目《にだんめ》',
    meaning: { id: 'tingkat kedua dari atas', en: 'second tier from the top' },
    example: 'はんこは 上《うえ》から 二段目《にだんめ》の 引《ひ》き出《だ》しに あります。',
    exMeaning: { id: 'Cap ada di laci kedua dari atas.', en: 'The seal is in the second drawer from the top.' },
  },
  {
    ja: '奥《おく》',
    meaning: { id: 'bagian dalam', en: 'the back, the far inside' },
    example: 'かぎは 引《ひ》き出《だ》しの 奥《おく》に 入《い》れて あります。',
    exMeaning: { id: 'Kunci disimpan di bagian dalam laci.', en: 'The key is kept at the back of the drawer.' },
  },
  {
    ja: '手前《てまえ》',
    meaning: { id: 'bagian depan (dekat pembicara)', en: 'the near side (close to the speaker)' },
    example: 'ペンは 引《ひ》き出《だ》しの 手前《てまえ》に あります。',
    exMeaning: { id: 'Pulpen ada di bagian depan laci.', en: 'The pen is at the front of the drawer.' },
  },
  {
    ja: '前《まえ》から 二列目《にれつめ》',
    meaning: { id: 'deretan kedua dari depan', en: 'second row from the front' },
    example: '田中《たなか》さんは 前《まえ》から 二列目《にれつめ》に います。',
    exMeaning: { id: 'Tanaka ada di deretan kedua dari depan.', en: 'Tanaka is in the second row from the front.' },
  },
  {
    ja: '〜の 周《まわ》り',
    meaning: { id: 'sekitar ~', en: 'around ~' },
    example: '池《いけ》の 周《まわ》りに 花《はな》が 植《う》えて あります。',
    exMeaning: { id: 'Di sekitar kolam ditanam bunga.', en: 'Flowers are planted around the pond.' },
  },
  {
    ja: '〜の 真《ま》ん中《なか》',
    meaning: { id: 'tengah ~', en: 'the middle of ~' },
    example: '教室《きょうしつ》の 真《ま》ん中《なか》に 机《つくえ》が 並《なら》べて あります。',
    exMeaning: { id: 'Di tengah kelas ada meja yang ditata.', en: 'Desks are lined up in the middle of the classroom.' },
  },
  {
    ja: '斜《なな》め前《まえ》',
    meaning: { id: 'samping depan (serong ke depan)', en: 'diagonally in front' },
    example: '先生《せんせい》は 私《わたし》の 斜《なな》め前《まえ》に います。',
    exMeaning: { id: 'Guru ada di serong depan saya.', en: 'The teacher is diagonally in front of me.' },
  },
  {
    ja: '斜《なな》め後《うし》ろ',
    meaning: { id: 'samping belakang (serong ke belakang)', en: 'diagonally behind' },
    example: 'サリさんは ワヒュさんの 斜《なな》め後《うし》ろに います。',
    exMeaning: { id: 'Sari ada di serong belakang Wahyu.', en: 'Sari is diagonally behind Wahyu.' },
  },
  {
    ja: '〜の そば',
    meaning: { id: 'di samping／dekat ~', en: 'beside, near ~' },
    example: '本《ほん》の そばに ペンが 置《お》いて あります。',
    exMeaning: { id: 'Di dekat buku ada pulpen yang diletakkan.', en: 'A pen has been placed next to the book.' },
  },
  {
    ja: '〜の 横《よこ》',
    meaning: { id: 'sebelah ~', en: 'next to ~' },
    example: 'テレビの 横《よこ》に 花瓶《かびん》が 飾《かざ》って あります。',
    exMeaning: { id: 'Di sebelah televisi ada vas yang dipajang.', en: 'A vase is displayed next to the TV.' },
  },
  {
    ja: '隅《すみ》',
    meaning: { id: 'pojok, sudut', en: 'corner' },
    example: '部屋《へや》の 隅《すみ》に ごみ箱《ばこ》が あります。',
    exMeaning: { id: 'Di pojok ruangan ada tong sampah.', en: 'There is a trash can in the corner of the room.' },
  },
  {
    ja: '〜ページ・〜行目《ぎょうめ》',
    meaning: { id: 'halaman ~ ・ baris ke-~', en: 'page ~ ・ line number ~' },
    example: '四《よん》ページの 二行目《にぎょうめ》を 読《よ》んで ください。',
    exMeaning: { id: 'Tolong baca baris kedua di halaman 4.', en: 'Please read line 2 on page 4.' },
  },
]
// ---------------------------------------------------------------------
// N4 · Pelajaran 14 — Ungkapan Sebab (て／で／ので／から) & 途中で
// ---------------------------------------------------------------------
const reasonCompare = [
  {
    pattern: 'Ｖて／Ｖなくて',
    form: { id: 'Bentuk-て; negatif 〜ない → 〜なくて', en: 'て-form; negative 〜ない → 〜なくて' },
    back: { id: 'Perasaan, kemampuan, keadaan, kejadian (tanpa kehendak)', en: 'Feelings, ability, states, events (no will)' },
    example: '友達《ともだち》に 会《あ》えなくて、寂《さび》しいです。',
    meaning: { id: 'Kesepian karena tidak bisa bertemu teman.', en: 'I feel lonely because I cannot meet my friends.' },
  },
  {
    pattern: 'Ａくて',
    form: { id: 'Kata sifat い: 〜い → 〜くて', en: 'い-adjective: 〜い → 〜くて' },
    back: { id: 'Sama seperti di atas', en: 'Same as above' },
    example: '部屋《へや》が 狭《せま》くて、困《こま》ります。',
    meaning: { id: 'Kamarnya sempit, jadi repot.', en: 'The room is small, so it is a nuisance.' },
  },
  {
    pattern: 'Ａnaで／Ｎで',
    form: { id: 'Kata sifat な dan kata benda: だ → で', en: 'な-adjective and noun: だ → で' },
    back: { id: 'Sama seperti di atas', en: 'Same as above' },
    example: 'この 道《みち》は 複雑《ふくざつ》で、迷《まよ》いました。',
    meaning: { id: 'Jalan ini rumit, jadi saya tersesat.', en: 'This road is complicated, so I got lost.' },
  },
  {
    pattern: 'Ｎで (kejadian)',
    form: { id: 'Kata benda kejadian／fenomena + で', en: 'Event or phenomenon noun + で' },
    back: { id: 'Akibat yang terjadi', en: 'The resulting event' },
    example: '台風《たいふう》で、電車《でんしゃ》が 止《と》まりました。',
    meaning: { id: 'Kereta berhenti karena angin topan.', en: 'The train stopped because of the typhoon.' },
  },
  {
    pattern: '〜ので',
    form: { id: 'Bentuk biasa + ので (な／N: だ → な)', en: 'Plain form + ので (な-adj／N: だ → な)' },
    back: { id: 'Bebas, termasuk permintaan dan izin', en: 'Anything, including requests and permission' },
    example: '用事《ようじ》が あるので、先《さき》に 帰《かえ》ります。',
    meaning: { id: 'Karena ada urusan, saya pulang duluan.', en: 'I have an errand, so I will leave first.' },
  },
  {
    pattern: '〜から',
    form: { id: 'Bentuk biasa／sopan + から', en: 'Plain／polite form + から' },
    back: { id: 'Bebas, terutama kehendak: ajakan, permintaan, perintah', en: 'Anything, especially will: invitations, requests, commands' },
    example: '時間《じかん》が ありませんから、タクシーで 行《い》きましょう。',
    meaning: { id: 'Tidak ada waktu, jadi mari naik taksi.', en: 'There is no time, so let us take a taxi.' },
  },
]

const nodeForms = [
  { type: { id: 'Kata kerja', en: 'Verb' }, plain: '行く ／ 行かない ／ 行った', example: '行くので ／ 行かないので ／ 行ったので', note: { id: 'Bentuk biasa apa adanya', en: 'Plain form as it is' } },
  { type: { id: 'Kata sifat い', en: 'い-adjective' }, plain: '高い ／ 高くない ／ 高かった', example: '高いので ／ 高くないので ／ 高かったので', note: { id: 'Bentuk biasa apa adanya', en: 'Plain form as it is' } },
  { type: { id: 'Kata sifat な', en: 'な-adjective' }, plain: '静か(だ) ／ 静かじゃない', example: '静かなので ／ 静かじゃないので', note: { id: 'だ → な (bentuk positif)', en: 'だ → な (affirmative)' } },
  { type: { id: 'Kata benda', en: 'Noun' }, plain: '雨(だ) ／ 雨じゃない', example: '雨なので ／ 雨じゃないので', note: { id: 'だ → な (bentuk positif)', en: 'だ → な (affirmative)' } },
]

const tochuuRows = [
  { form: 'Ｖ(kamus) ＋ 途中《とちゅう》で', example: '来《く》る 途中《とちゅう》で、友達《ともだち》に 会《あ》いました。', meaning: { id: 'Di tengah perjalanan ke sini, saya bertemu teman.', en: 'On my way here, I met a friend.' } },
  { form: 'Ｎの ＋ 途中《とちゅう》で', example: '映画《えいが》の 途中《とちゅう》で、寝《ね》て しまいました。', meaning: { id: 'Di tengah film, saya tertidur.', en: 'I fell asleep in the middle of the movie.' } },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 14 — Perasaan (気持ち)
// ---------------------------------------------------------------------
const feelingWords = [
  { ja: 'うれしい', name: { id: 'senang', en: 'happy, glad' }, example: 'プレゼントを もらって、うれしいです。', meaning: { id: 'Saya senang menerima hadiah.', en: 'I am happy to receive a present.' } },
  { ja: '楽《たの》しい', name: { id: 'senang, menyenangkan', en: 'fun, enjoyable' }, example: 'みんなと 話《はな》すのは 楽《たの》しいです。', meaning: { id: 'Berbicara dengan semua orang menyenangkan.', en: 'Talking with everyone is fun.' } },
  { ja: '寂《さび》しい', name: { id: 'sepi', en: 'lonely' }, example: '一人《ひとり》で いると、寂《さび》しいです。', meaning: { id: 'Kalau sendirian, rasanya sepi.', en: 'I feel lonely when I am alone.' } },
  { ja: '悲《かな》しい', name: { id: 'sedih', en: 'sad' }, example: '悲《かな》しい ニュースを 聞《き》きました。', meaning: { id: 'Saya mendengar kabar sedih.', en: 'I heard some sad news.' } },
  { ja: 'おもしろい', name: { id: 'menarik', en: 'interesting' }, example: 'この 本《ほん》は おもしろいです。', meaning: { id: 'Buku ini menarik.', en: 'This book is interesting.' } },
  { ja: 'うらやましい', name: { id: 'iri hati', en: 'envious' }, example: '旅行《りょこう》に 行《い》った 友達《ともだち》が うらやましいです。', meaning: { id: 'Saya iri pada teman yang pergi berlibur.', en: 'I envy my friend who went on a trip.' } },
  { ja: '恥《は》ずかしい', name: { id: 'malu', en: 'embarrassed' }, example: '大《おお》きな 声《こえ》で 間違《まちが》えて、恥《は》ずかしかったです。', meaning: { id: 'Saya malu karena salah dengan suara keras.', en: 'I made a mistake out loud and was embarrassed.' } },
  { ja: '懐《なつ》かしい', name: { id: 'terkenang', en: 'nostalgic' }, example: '昔《むかし》の 写真《しゃしん》を 見《み》て、懐《なつ》かしく なりました。', meaning: { id: 'Melihat foto lama, saya jadi terkenang.', en: 'Looking at old photos made me nostalgic.' } },
  { ja: 'びっくりする', name: { id: 'kaget', en: 'to be surprised' }, example: 'ドアが 急《きゅう》に 開《あ》いて、びっくりしました。', meaning: { id: 'Pintu tiba-tiba terbuka, saya kaget.', en: 'The door suddenly opened and I was startled.' } },
  { ja: 'がっかりする', name: { id: 'putus asa, kecewa', en: 'to be disappointed' }, example: 'コンサートが 中止《ちゅうし》で、がっかりしました。', meaning: { id: 'Konsernya dibatalkan, saya kecewa.', en: 'The concert was cancelled and I was disappointed.' } },
  { ja: 'うっとりする', name: { id: 'terpesona', en: 'to be enchanted' }, example: '美《うつく》しい 歌《うた》に うっとりしました。', meaning: { id: 'Saya terpesona oleh lagu yang indah.', en: 'I was enchanted by the beautiful song.' } },
  { ja: 'いらいらする', name: { id: 'jengkel', en: 'to be irritated' }, example: 'バスが 来《こ》なくて、いらいらします。', meaning: { id: 'Bus tidak datang, saya jengkel.', en: 'The bus does not come and I am irritated.' } },
  { ja: 'どきどきする', name: { id: 'berdebar (takut／gugup)', en: 'to have a pounding heart' }, example: '発表《はっぴょう》の 前《まえ》は どきどきします。', meaning: { id: 'Sebelum presentasi, jantung saya berdebar.', en: 'My heart pounds before a presentation.' } },
  { ja: 'はらはらする', name: { id: 'merasa tidak nyaman, cemas', en: 'to be on edge, anxious' }, example: '子《こ》どもが 木《き》に 登《のぼ》って、はらはらしました。', meaning: { id: 'Anak memanjat pohon, saya cemas.', en: 'The child climbed a tree and I was on edge.' } },
  { ja: 'わくわくする', name: { id: 'sangat bersemangat, menggebu', en: 'to be excited' }, example: '旅行《りょこう》の 前《まえ》は わくわくします。', meaning: { id: 'Sebelum bepergian, saya sangat bersemangat.', en: 'I get excited before a trip.' } },
]
// ---------------------------------------------------------------------
// N4 · Pelajaran 15 — ringkasan kalimat tanya sisipan
// ---------------------------------------------------------------------
const embeddedForms = [
  {
    type: { id: 'Kata kerja', en: 'Verb' },
    plain: '行く ／ 行かない ／ 行った',
    example: '行くか ／ 行かないか ／ 行ったか',
    note: { id: 'Bentuk biasa apa adanya', en: 'Plain form as it is' },
  },
  {
    type: { id: 'Kata sifat い', en: 'い-adjective' },
    plain: '高い ／ 高くない ／ 高かった',
    example: '高いか ／ 高くないか ／ 高かったか',
    note: { id: 'Bentuk biasa apa adanya', en: 'Plain form as it is' },
  },
  {
    type: { id: 'Kata sifat な', en: 'な-adjective' },
    plain: '好き(だ) ／ 好きじゃない',
    example: '好きか ／ 好きじゃないか',
    note: { id: 'だ dihilangkan sebelum か', en: 'だ is dropped before か' },
  },
  {
    type: { id: 'Kata benda', en: 'Noun' },
    plain: '雨(だ) ／ 雨じゃない',
    example: '雨か ／ 雨じゃないか',
    note: { id: 'だ dihilangkan sebelum か', en: 'だ is dropped before か' },
  },
]

const embeddedUses = [
  {
    pattern: 'Kata tanya ＋ か、〜',
    use: { id: 'Memasukkan pertanyaan berkata tanya ke dalam kalimat lain.', en: 'Placing a question with a question word inside another sentence.' },
    example: '会議《かいぎ》は 何時《なんじ》に 始《はじ》まるか、教《おし》えて ください。',
    meaning: { id: 'Tolong beri tahu rapat dimulai pukul berapa.', en: 'Please tell me what time the meeting starts.' },
  },
  {
    pattern: '〜か どうか、〜',
    use: { id: 'Memasukkan pertanyaan ya/tidak ("apakah … atau tidak"). どうか wajib ada.', en: 'Placing a yes/no question inside a sentence ("whether … or not"). どうか is required.' },
    example: '新年会《しんねんかい》に 来《く》るか どうか、教《おし》えて ください。',
    meaning: { id: 'Tolong beri tahu apakah Anda datang ke pesta tahun baru atau tidak.', en: 'Please let me know whether you are coming to the New Year party.' },
  },
  {
    pattern: 'Ｖて みます',
    use: { id: 'Mencoba melakukan sesuatu lalu melihat hasilnya.', en: 'Trying something and seeing the result.' },
    example: 'この 靴《くつ》を はいて みても いいですか。',
    meaning: { id: 'Bolehkah saya mencoba memakai sepatu ini?', en: 'May I try these shoes on?' },
  },
  {
    pattern: 'Ａい → Ａさ',
    use: { id: 'Mengubah kata sifat い menjadi kata benda tingkat/ukuran.', en: 'Turning an い-adjective into a noun of degree or measure.' },
    example: '山《やま》の 高《たか》さは どうやって 測《はか》りますか。',
    meaning: { id: 'Bagaimana cara mengukur tinggi gunung?', en: 'How do you measure the height of a mountain?' },
  },
  {
    pattern: '〜でしょうか',
    use: { id: 'Pertanyaan yang lebih lembut daripada 〜ですか.', en: 'A softer question than 〜ですか.' },
    example: 'あしたの 天気《てんき》は どうでしょうか。',
    meaning: { id: 'Kira-kira bagaimana cuaca besok?', en: 'How will the weather be tomorrow, I wonder?' },
  },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 15 — Ukuran, Garis, Bentuk, Corak (単位《たんい》・線《せん》・形《かたち》・模様《もよう》)
// [tulisan, bacaan, { id, en }, simbol]
// ---------------------------------------------------------------------
const measureGroups = [
  {
    title: { id: 'Bidang (面積)', en: 'Area (面積)' },
    items: [
      ['平方センチメートル', 'へいほうセンチメートル', { id: 'sentimeter persegi', en: 'square centimetre' }, 'cm²'],
      ['平方メートル', 'へいほうメートル', { id: 'meter persegi', en: 'square metre' }, 'm²'],
      ['平方キロメートル', 'へいほうキロメートル', { id: 'kilometer persegi', en: 'square kilometre' }, 'km²'],
    ],
  },
  {
    title: { id: 'Panjang (長さ)', en: 'Length (長さ)' },
    items: [
      ['ミリ[メートル]', 'ミリ[メートル]', { id: 'milimeter', en: 'millimetre' }, 'mm'],
      ['センチ[メートル]', 'センチ[メートル]', { id: 'sentimeter', en: 'centimetre' }, 'cm'],
      ['メートル', 'メートル', { id: 'meter', en: 'metre' }, 'm'],
      ['キロ[メートル]', 'キロ[メートル]', { id: 'kilometer', en: 'kilometre' }, 'km'],
    ],
  },
  {
    title: { id: 'Volume dan kapasitas (体積・容積)', en: 'Volume and capacity (体積・容積)' },
    items: [
      ['立方センチメートル', 'りっぽうセンチメートル', { id: 'sentimeter kubik', en: 'cubic centimetre' }, 'cm³'],
      ['立方メートル', 'りっぽうメートル', { id: 'meter kubik', en: 'cubic metre' }, 'm³'],
      ['ミリリットル', 'ミリリットル', { id: 'mililiter', en: 'millilitre' }, 'ml'],
      ['シーシー', 'シーシー', { id: 'cc', en: 'cc' }, 'cc'],
      ['リットル', 'リットル', { id: 'liter', en: 'litre' }, 'l'],
    ],
  },
  {
    title: { id: 'Berat (重さ)', en: 'Weight (重さ)' },
    items: [
      ['ミリグラム', 'ミリグラム', { id: 'miligram', en: 'milligram' }, 'mg'],
      ['グラム', 'グラム', { id: 'gram', en: 'gram' }, 'g'],
      ['キロ[グラム]', 'キロ[グラム]', { id: 'kilogram', en: 'kilogram' }, 'kg'],
      ['トン', 'トン', { id: 'ton', en: 'tonne' }, 't'],
    ],
  },
  {
    title: { id: 'Perhitungan (計算)', en: 'Calculation (計算)' },
    items: [
      ['たす', 'たす', { id: 'tambah', en: 'plus' }, '＋'],
      ['ひく', 'ひく', { id: 'kurang', en: 'minus' }, '－'],
      ['かける', 'かける', { id: 'kali', en: 'times' }, '×'],
      ['わる', 'わる', { id: 'bagi', en: 'divided by' }, '÷'],
      ['は(イコール)', 'は(イコール)', { id: 'sama dengan', en: 'equals' }, '＝'],
    ],
  },
  {
    title: { id: 'Garis (線)', en: 'Lines (線)' },
    items: [
      ['直線', 'ちょくせん', { id: 'garis lurus', en: 'straight line' }, ''],
      ['曲線', 'きょくせん', { id: 'garis kurva', en: 'curved line' }, ''],
      ['点線', 'てんせん', { id: 'garis titik', en: 'dotted line' }, ''],
    ],
  },
  {
    title: { id: 'Bentuk (形)', en: 'Shapes (形)' },
    items: [
      ['円(丸)', 'えん(まる)', { id: 'bulat', en: 'circle' }, ''],
      ['三角[形]', 'さんかく[けい]', { id: 'segi tiga', en: 'triangle' }, ''],
      ['四角[形]', 'しかく[けい]', { id: 'segi empat', en: 'square, rectangle' }, ''],
    ],
  },
  {
    title: { id: 'Corak (模様)', en: 'Patterns (模様)' },
    items: [
      ['縦じま', 'たてじま', { id: 'belang vertikal', en: 'vertical stripes' }, ''],
      ['横じま', 'よこじま', { id: 'belang horizontal', en: 'horizontal stripes' }, ''],
      ['チェック', 'チェック', { id: 'berpetak-petak', en: 'checked' }, ''],
      ['水玉', 'みずたま', { id: 'berbintik-bintik', en: 'polka dots' }, ''],
      ['花柄', 'はながら', { id: 'corak bunga', en: 'floral pattern' }, ''],
      ['無地', 'むじ', { id: 'polos', en: 'plain, solid' }, ''],
    ],
  },
]

const measureTips = [
  { id: 'Contoh perhitungan: 1 ＋ 2 － 3 × 4 ÷ 6 ＝ 1 dibaca 1 たす 2 ひく 3 かける 4 わる 6 は 1 (イコール 1).', en: 'Example calculation: 1 ＋ 2 － 3 × 4 ÷ 6 ＝ 1 is read 1 たす 2 ひく 3 かける 4 わる 6 は 1.' },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 16 — ungkapan memberi dan menerima
// ---------------------------------------------------------------------
const givingRows = [
  {
    situation: { id: 'Menerima dari orang yang setara/lebih rendah', en: 'Receiving from an equal/lower person' },
    word: 'もらいます',
    example: 'わたしは 友達《ともだち》に 本《ほん》を もらいました。',
    meaning: { id: 'Saya menerima buku dari teman.', en: 'I received a book from a friend.' },
  },
  {
    situation: { id: 'Menerima dari orang yang lebih tinggi', en: 'Receiving from a higher person' },
    word: 'いただきます',
    example: 'わたしは 部長《ぶちょう》に 本《ほん》を いただきました。',
    meaning: { id: 'Saya menerima buku dari kepala bagian.', en: 'I received a book from the department head.' },
  },
  {
    situation: { id: 'Diberi oleh orang yang setara/lebih rendah', en: 'Being given by an equal/lower person' },
    word: 'くれます',
    example: '友達《ともだち》が わたしに 本《ほん》を くれました。',
    meaning: { id: 'Teman memberi saya buku.', en: 'A friend gave me a book.' },
  },
  {
    situation: { id: 'Diberi oleh orang yang lebih tinggi', en: 'Being given by a higher person' },
    word: 'くださいます',
    example: '部長《ぶちょう》が わたしに 本《ほん》を くださいました。',
    meaning: { id: 'Kepala bagian memberi saya buku.', en: 'The department head gave me a book.' },
  },
  {
    situation: { id: 'Memberi kepada orang lain (setara)', en: 'Giving to another person (equal)' },
    word: 'あげます',
    example: 'わたしは 友達《ともだち》に 本《ほん》を あげました。',
    meaning: { id: 'Saya memberi teman buku.', en: 'I gave my friend a book.' },
  },
  {
    situation: { id: 'Memberi kepada yang lebih rendah, hewan, tanaman', en: 'Giving to a lower person, an animal, a plant' },
    word: 'やります／あげます',
    example: '犬《いぬ》に えさを やりました。',
    meaning: { id: 'Saya memberi makan anjing.', en: 'I fed the dog.' },
  },
]

const givingTeForms = [
  { pattern: 'Vて いただきます', use: { id: 'Orang yang lebih tinggi melakukan sesuatu untuk saya (subjek: penerima).', en: 'A higher person does something for me (subject: receiver).' } },
  { pattern: 'Vて くださいます', use: { id: 'Orang yang lebih tinggi melakukan sesuatu untuk saya (subjek: pelaku).', en: 'A higher person does something for me (subject: doer).' } },
  { pattern: 'Vて やります／あげます', use: { id: 'Saya melakukan sesuatu untuk orang/hewan lain.', en: 'I do something for another person or animal.' } },
  { pattern: 'Vて くださいませんか', use: { id: 'Permintaan sopan: bisakah Anda …? (di antara 〜て ください dan 〜て いただけませんか).', en: 'Polite request: would you …? (between 〜て ください and 〜て いただけませんか).' } },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 16 — Informasi Berguna (便利情報)
// ---------------------------------------------------------------------
const usefulGroups = [
  {
    title: { ja: '貸衣装《かしいしょう》', id: 'Penyewaan pakaian', en: 'Clothing rental' },
    terms: [
      { ja: '七五三《しちごさん》', id: 'Perayaan umur 7, 5, dan 3 tahun', en: 'Celebration at ages 7, 5 and 3' },
      { ja: '卒業式《そつぎょうしき》', id: 'Upacara wisuda', en: 'Graduation ceremony' },
      { ja: '成人式《せいじんしき》', id: 'Upacara pendewasaan', en: 'Coming-of-age ceremony' },
      { ja: '結婚式《けっこんしき》', id: 'Upacara pernikahan', en: 'Wedding ceremony' },
    ],
  },
  {
    title: { ja: '民宿《みんしゅく》', id: 'Penginapan keluarga', en: 'Family-run inn' },
    terms: [
      { ja: '泊《と》まります', id: 'Menginap', en: 'To stay overnight' },
      { ja: '家庭的《かていてき》な', id: 'Bersuasana keluarga', en: 'Homelike' },
    ],
  },
  {
    title: { ja: '公民館《こうみんかん》', id: 'Balai warga', en: 'Community centre' },
    terms: [
      { ja: '日本料理講習会《にほんりょうりこうしゅうかい》', id: 'Les masakan Jepang', en: 'Japanese cooking class' },
      { ja: '生《い》け花《ばな》スクール', id: 'Klub merangkai bunga (ikebana)', en: 'Flower-arranging school (ikebana)' },
      { ja: '日本語教室《にほんごきょうしつ》', id: 'Kursus bahasa Jepang', en: 'Japanese language class' },
      { ja: 'バザー', id: 'Bazar', en: 'Bazaar' },
    ],
  },
  {
    title: { ja: 'レンタルサービス', id: 'Jasa penyewaan', en: 'Rental service' },
    terms: [
      { ja: 'カラオケ', id: 'Karaoke', en: 'Karaoke' },
      { ja: 'ビデオカメラ', id: 'Kamera video', en: 'Video camera' },
      { ja: '携帯電話《けいたいでんわ》', id: 'Ponsel', en: 'Mobile phone' },
      { ja: 'ベビー用品《ようひん》', id: 'Peralatan bayi', en: 'Baby goods' },
      { ja: 'レジャー用品《ようひん》', id: 'Peralatan rekreasi', en: 'Leisure goods' },
      { ja: '旅行用品《りょこうようひん》', id: 'Peralatan perjalanan', en: 'Travel goods' },
    ],
  },
  {
    title: { ja: '便利屋《べんりや》', id: 'Tukang serba guna', en: 'Handyman service' },
    terms: [
      { ja: '家《いえ》の 修理《しゅうり》、掃除《そうじ》', id: 'Perbaikan dan pembersihan rumah', en: 'House repairs and cleaning' },
      { ja: '赤《あか》ちゃん、子《こ》どもの 世話《せわ》', id: 'Menjaga bayi dan anak', en: 'Looking after babies and children' },
      { ja: '犬《いぬ》の 散歩《さんぽ》', id: 'Mengajak anjing berjalan-jalan', en: 'Walking the dog' },
      { ja: '話《はな》し相手《あいて》', id: 'Teman bicara', en: 'Someone to talk with' },
    ],
  },
  {
    title: { ja: 'お寺《てら》で 体験《たいけん》', id: 'Pengalaman di wihara', en: 'Temple experience' },
    terms: [
      { ja: '禅《ぜん》', id: 'Meditasi Zen', en: 'Zen meditation' },
      { ja: '精進料理《しょうじんりょうり》', id: 'Masakan vegetarian wihara', en: 'Temple vegetarian cuisine' },
    ],
  },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 17 — tujuan & kegunaan
// ---------------------------------------------------------------------
const purposeCompare = [
  {
    pattern: 'V(意志) ＋ ために',
    use: { id: 'Tujuan yang ingin dicapai pelaku sendiri; kata kerja menyatakan kehendak.', en: 'A goal the doer wants to reach; the verb expresses will.' },
    example: '店《みせ》を 持《も》つ ために、貯金《ちょきん》して います。',
    meaning: { id: 'Saya menabung untuk memiliki toko.', en: 'I am saving in order to own a shop.' },
  },
  {
    pattern: 'Nの ＋ ために',
    use: { id: 'Demi／untuk N (manfaat atau sasaran).', en: 'For the sake of N (benefit or target).' },
    example: '家族《かぞく》の ために 働《はたら》きます。',
    meaning: { id: 'Saya bekerja demi keluarga.', en: 'I work for my family.' },
  },
  {
    pattern: 'V(無意志／ない) ＋ ように',
    use: { id: 'Keadaan yang diharapkan terjadi; kata kerja bukan kehendak atau bentuk negatif.', en: 'A hoped-for state; the verb is non-volitional or negative.' },
    example: '忘《わす》れない ように、メモを します。',
    meaning: { id: 'Saya mencatat agar tidak lupa.', en: 'I take notes so I do not forget.' },
  },
]

const useVerbs = [
  {
    pattern: '〜のに 使《つか》います',
    use: { id: 'Dipakai untuk ...', en: 'Is used for ...' },
    example: 'はさみは 紙《かみ》を 切《き》るのに 使《つか》います。',
    meaning: { id: 'Gunting dipakai untuk memotong kertas.', en: 'Scissors are used to cut paper.' },
  },
  {
    pattern: '〜のに 便利《べんり》です／いいです',
    use: { id: 'Praktis／cocok untuk ...', en: 'Handy／good for ...' },
    example: 'この かばんは 旅行《りょこう》に 便利《べんり》です。',
    meaning: { id: 'Tas ini praktis untuk bepergian.', en: 'This bag is handy for trips.' },
  },
  {
    pattern: '〜のに 役《やく》に 立《た》ちます',
    use: { id: 'Berguna untuk ...', en: 'Is useful for ...' },
    example: 'この 辞書《じしょ》は 勉強《べんきょう》に 役《やく》に 立《た》ちます。',
    meaning: { id: 'Kamus ini berguna untuk belajar.', en: 'This dictionary is useful for studying.' },
  },
  {
    pattern: '〜のに 時間《じかん》が かかります',
    use: { id: 'Memakan waktu／uang untuk ...', en: 'Takes time／money to ...' },
    example: '駅《えき》まで 行《い》くのに 二十分《にじゅっぷん》 かかります。',
    meaning: { id: 'Pergi sampai stasiun memakan dua puluh menit.', en: 'It takes twenty minutes to get to the station.' },
  },
]

const amountParticles = [
  {
    pattern: 'Bilangan ＋ は',
    use: { id: 'Batas minimal ("paling tidak sebanyak ini").', en: 'A minimum ("at least this much").' },
    example: '毎日《まいにち》 三十分《さんじゅっぷん》は 勉強《べんきょう》します。',
    meaning: { id: 'Setiap hari belajar paling tidak tiga puluh menit.', en: 'I study at least thirty minutes every day.' },
  },
  {
    pattern: 'Bilangan ＋ も',
    use: { id: 'Terasa banyak ("sampai sebanyak ini!").', en: 'Feels like a lot ("as many as this!").' },
    example: 'ケーキを 五《いつ》つも 食《た》べました。',
    meaning: { id: 'Saya makan kue sampai lima potong.', en: 'I ate as many as five cakes.' },
  },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 17 — Perlengkapan Kantor & Alat (事務用品・道具)
// ---------------------------------------------------------------------
const toolItems = [
  { ja: 'ホッチキス', action: 'とじる', name: { id: 'stepler', en: 'stapler' }, doing: { id: 'menjilid', en: 'to bind' } },
  { ja: 'クリップ', action: '挟《はさ》む／とじる', name: { id: 'jepitan', en: 'paper clip' }, doing: { id: 'menjepit／menjilid', en: 'to clip／to bind' } },
  { ja: '画《が》びょう', action: '留《と》める', name: { id: 'paku payung', en: 'thumbtack' }, doing: { id: 'menyematkan', en: 'to pin' } },
  { ja: 'カッター', action: '切《き》る', name: { id: 'pisau pemotong', en: 'cutter' }, doing: { id: 'memotong', en: 'to cut' } },
  { ja: 'はさみ', action: '切《き》る', name: { id: 'gunting', en: 'scissors' }, doing: { id: 'menggunting', en: 'to cut' } },
  { ja: 'セロテープ', action: 'はる', name: { id: 'selotip', en: 'cellophane tape' }, doing: { id: 'menempelkan', en: 'to stick' } },
  { ja: 'ガムテープ', action: 'はる', name: { id: 'selotip kertas', en: 'packing tape' }, doing: { id: 'menempelkan', en: 'to stick' } },
  { ja: 'のり', action: 'はる', name: { id: 'lem', en: 'glue' }, doing: { id: 'menempelkan', en: 'to stick' } },
  { ja: '鉛筆削《えんぴつけず》り', action: '削《けず》る', name: { id: 'peraut pensil', en: 'pencil sharpener' }, doing: { id: 'meruncingkan', en: 'to sharpen' } },
  { ja: 'ファイル', action: 'ファイルする', name: { id: 'map', en: 'file folder' }, doing: { id: 'mengarsipkan', en: 'to file' } },
  { ja: '消《け》しゴム', action: '消《け》す', name: { id: 'penghapus karet', en: 'eraser' }, doing: { id: 'menghapus', en: 'to erase' } },
  { ja: '修正液《しゅうせいえき》', action: '消《け》す', name: { id: 'penghapus cair', en: 'correction fluid' }, doing: { id: 'menghapus', en: 'to erase' } },
  { ja: 'パンチ', action: '[穴《あな》を] 開《あ》ける', name: { id: 'pelubang kertas', en: 'hole punch' }, doing: { id: 'melubangi', en: 'to punch a hole' } },
  { ja: '電卓《でんたく》', action: '計算《けいさん》する', name: { id: 'kalkulator', en: 'calculator' }, doing: { id: 'menghitung', en: 'to calculate' } },
  { ja: '定規《じょうぎ》（物差《ものさ》し）', action: '[線《せん》を] 引《ひ》く／測《はか》る', name: { id: 'penggaris', en: 'ruler' }, doing: { id: 'menggaris／mengukur', en: 'to draw a line／to measure' } },
  { ja: 'のこぎり', action: '切《き》る', name: { id: 'gergaji', en: 'saw' }, doing: { id: 'memotong', en: 'to cut' } },
  { ja: '金《かな》づち', action: '[くぎを] 打《う》つ', name: { id: 'palu', en: 'hammer' }, doing: { id: 'memaku', en: 'to hammer [a nail]' } },
  { ja: 'ペンチ', action: '挟《はさ》む／曲《ま》げる／切《き》る', name: { id: 'tang', en: 'pliers' }, doing: { id: 'menjepit／membengkokkan／memotong', en: 'to grip／to bend／to cut' } },
  { ja: 'ドライバー', action: '[ねじを] 締《し》める／緩《ゆる》める', name: { id: 'obeng', en: 'screwdriver' }, doing: { id: 'mengencangkan／melonggarkan [sekrup]', en: 'to tighten／to loosen [a screw]' } },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 18 — pola 〜そうです
// ---------------------------------------------------------------------
const souForms = [
  {
    type: { id: 'Kata kerja', en: 'Verb' },
    rule: { id: 'Bentuk ます tanpa ます', en: 'ます-form stem' },
    example: '降《ふ》ります → 降《ふ》りそうです',
    meaning: { id: 'Sepertinya akan turun (hujan)', en: 'It looks like it will fall (rain)' },
  },
  {
    type: { id: 'Kata sifat い', en: 'い-adjective' },
    rule: { id: 'Buang い', en: 'Drop い' },
    example: '辛《から》い → 辛《から》そうです',
    meaning: { id: 'Kelihatannya pedas', en: 'It looks spicy' },
  },
  {
    type: { id: 'Kata sifat な', en: 'な-adjective' },
    rule: { id: 'Buang な', en: 'Drop な' },
    example: '丈夫《じょうぶ》[な] → 丈夫《じょうぶ》そうです',
    meaning: { id: 'Kelihatannya kuat', en: 'It looks sturdy' },
  },
  {
    type: { id: 'Pengecualian', en: 'Exceptions' },
    rule: { id: 'いい → よさ／ない → なさ', en: 'いい → よさ／ない → なさ' },
    example: 'いい → よさそうです ／ ない → なさそうです',
    meaning: { id: 'Kelihatannya bagus ／ kelihatannya tidak ada', en: 'It looks good ／ it looks like there is none' },
  },
]

const souNotes = [
  { id: 'Dasarnya penampilan luar. Untuk kabar yang didengar dari orang lain, pola yang dipakai berbeda.', en: 'It rests on outward appearance. Information heard from someone else uses a different pattern.' },
  { id: 'Perasaan orang lain (うれしい, かなしい, さびしい) dikatakan dengan そう: うれしそうですね。', en: 'The feelings of other people (うれしい, かなしい, さびしい) are expressed with そう: うれしそうですね。' },
  { id: 'Dengan kata keterangan 今《いま》にも, もうすぐ, これから, pola kata kerja menyatakan gejala yang segera terjadi.', en: 'With 今《いま》にも, もうすぐ, これから, the verb pattern shows something about to happen.' },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 18 — karakter dan sifat (性格・性質)
// ---------------------------------------------------------------------
const characterRows = [
  { pair: true, ja: '明《あか》るい', meaning: { id: 'ceria', en: 'cheerful' }, ja2: '暗《くら》い', meaning2: { id: 'muram', en: 'gloomy' } },
  { pair: true, ja: '気《き》が 長《なが》い', meaning: { id: 'kalem, sabar', en: 'patient' }, ja2: '気《き》が 短《みじか》い', meaning2: { id: 'tidak sabar, cepat marah', en: 'short-tempered' } },
  { pair: true, ja: '気《き》が 強《つよ》い', meaning: { id: 'bersifat keras, galak', en: 'strong-willed' }, ja2: '気《き》が 弱《よわ》い', meaning2: { id: 'penakut', en: 'timid' } },
  { pair: true, ja: 'まじめ[な]', meaning: { id: 'serius, tekun', en: 'serious, diligent' }, ja2: 'ふまじめ[な]', meaning2: { id: 'tidak serius, sembrono', en: 'not serious, careless' } },
  { ja: '活発《かっぱつ》[な]', meaning: { id: 'aktif, giat', en: 'active, lively' } },
  { ja: '誠実《せいじつ》[な]', meaning: { id: 'setia, tulus', en: 'sincere, faithful' } },
  { ja: 'わがまま[な]', meaning: { id: 'egois', en: 'selfish' } },
  { ja: '優《やさ》しい', meaning: { id: 'baik hati', en: 'kind' } },
  { ja: 'おとなしい', meaning: { id: 'pendiam', en: 'quiet, gentle' } },
  { ja: '冷《つめ》たい', meaning: { id: 'kaku, dingin', en: 'cold, distant' } },
  { ja: '厳《きび》しい', meaning: { id: 'keras, disiplin', en: 'strict' } },
  { ja: '頑固《がんこ》[な]', meaning: { id: 'keras kepala', en: 'stubborn' } },
  { ja: '素直《すなお》[な]', meaning: { id: 'penurut, lemah-lembut', en: 'obedient, gentle' } },
  { ja: '意地悪《いじわる》[な]', meaning: { id: 'jahat', en: 'mean' } },
  { ja: '勝《か》ち気《き》[な]', meaning: { id: 'tidak mau kalah', en: 'competitive' } },
  { ja: '神経質《しんけいしつ》[な]', meaning: { id: 'gelisah, gugup', en: 'nervous, high-strung' } },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 19 — ringkasan すぎます / やすい・にくい / します
// ---------------------------------------------------------------------
const p19Patterns = [
  {
    pattern: 'V(ます)／イ形／ナ形 ＋ すぎます',
    use: { id: 'Melewati batas yang pantas (biasanya kurang menyenangkan).', en: 'Going past a fitting limit (usually unpleasant).' },
    example: 'この かばんは 小《ちい》さすぎます。',
    meaning: { id: 'Tas ini terlalu kecil.', en: 'This bag is too small.' },
  },
  {
    pattern: 'V(ます) ＋ やすい／にくい',
    use: { id: 'Mudah／sulit dilakukan, atau mudah／jarang terjadi.', en: 'Easy／hard to do, or easily／rarely happens.' },
    example: 'この ペンは 書《か》きやすいです。',
    meaning: { id: 'Pulpen ini enak dipakai menulis.', en: 'This pen is easy to write with.' },
  },
  {
    pattern: 'Ｎ₁を イ形(〜く)／ナ形(〜に)／Ｎ₂に します',
    use: { id: 'Sengaja mengubah benda menjadi keadaan lain.', en: 'Deliberately changing something into another state.' },
    example: 'スカートを もう 少《すこ》し 長《なが》く して ください。',
    meaning: { id: 'Tolong panjangkan roknya sedikit lagi.', en: 'Please make the skirt a little longer.' },
  },
  {
    pattern: 'Ｎに します',
    use: { id: 'Menyatakan pilihan atau keputusan.', en: 'Stating a choice or decision.' },
    example: 'お昼《ひる》は 和食《わしょく》に しませんか。',
    meaning: { id: 'Bagaimana kalau makan siang makanan Jepang?', en: 'Shall we have Japanese food for lunch?' },
  },
]

const p19Forms = [
  { type: { id: 'Kata kerja + すぎます', en: 'Verb + すぎます' }, base: '飲《の》みます', result: '飲《の》みすぎます', note: { id: 'Buang ます', en: 'Drop ます' } },
  { type: { id: 'Kata sifat い + すぎます', en: 'い-adjective + すぎます' }, base: '大《おお》きい', result: '大《おお》きすぎます', note: { id: 'Buang い (いい → よすぎます)', en: 'Drop い (いい → よすぎます)' } },
  { type: { id: 'Kata sifat な + すぎます', en: 'な-adjective + すぎます' }, base: '静《しず》か[な]', result: '静《しず》かすぎます', note: { id: 'Buang な', en: 'Drop な' } },
  { type: { id: 'Kata kerja + やすい／にくい', en: 'Verb + やすい／にくい' }, base: '読《よ》みます', result: '読《よ》みやすい ／ 読《よ》みにくい', note: { id: 'Berubah seperti kata sifat い', en: 'Conjugates like an い-adjective' } },
  { type: { id: 'Kata sifat い → 〜く', en: 'い-adjective → 〜く' }, base: '短《みじか》い', result: '短《みじか》く します', note: { id: 'い → く', en: 'い → く' } },
  { type: { id: 'Kata sifat な → 〜に', en: 'な-adjective → 〜に' }, base: 'きれい[な]', result: 'きれいに します', note: { id: 'な → に', en: 'な → に' } },
]

const p19Change = [
  {
    pattern: '〜く／〜に なります',
    use: { id: 'Sesuatu berubah dengan sendirinya (tanpa pelaku yang disebut).', en: 'Something changes by itself (no doer mentioned).' },
    example: '部屋《へや》が きれいに なりました。',
    meaning: { id: 'Kamarnya menjadi bersih.', en: 'The room became clean.' },
  },
  {
    pattern: 'Ｎを 〜く／〜に します',
    use: { id: 'Seseorang sengaja mengubah benda itu.', en: 'Someone deliberately changes that thing.' },
    example: '部屋《へや》を きれいに しました。',
    meaning: { id: 'Saya membersihkan kamarnya.', en: 'I cleaned the room.' },
  },
]

// N4 · Pelajaran 19 — Salon kecantikan (美容院・理髪店)
const salonServices = [
  { ja: 'カット', meaning: { id: 'potong rambut', en: 'haircut' } },
  { ja: 'パーマ', meaning: { id: 'keriting rambut', en: 'perm' } },
  { ja: 'シャンプー', meaning: { id: 'keramas', en: 'shampoo' } },
  { ja: 'トリートメント', meaning: { id: 'perawatan rambut', en: 'hair treatment' } },
  { ja: 'ブロー', meaning: { id: 'blow (mengeringkan dan menata)', en: 'blow-dry' } },
  { ja: 'カラー', meaning: { id: 'mengecat／mewarnai rambut', en: 'hair coloring' } },
  { ja: 'エクステ', meaning: { id: 'sambung rambut', en: 'hair extensions' } },
  { ja: 'ネイル', meaning: { id: 'perawatan kuku', en: 'nail care' } },
  { ja: 'フェイシャルマッサージ', meaning: { id: 'pijat wajah', en: 'facial massage' } },
  { ja: 'メイク', meaning: { id: 'dandan, riasan', en: 'make-up' } },
  { ja: '着付《きつ》け', meaning: { id: 'membantu mengenakan kimono', en: 'help putting on a kimono' } },
]

const salonRequests = [
  { ja: '耳《みみ》が 見《み》える くらいに', meaning: { id: 'sampai telinga terlihat', en: 'so that the ears show' } },
  { ja: '肩《かた》に かかる くらいに', meaning: { id: 'sebahu', en: 'to shoulder length' } },
  { ja: 'まゆが 隠《かく》れる くらいに', meaning: { id: 'sebatas alis', en: 'so that it covers the eyebrows' } },
  { ja: '１センチ くらい', meaning: { id: 'kira-kira 1 cm', en: 'about 1 cm' } },
  { ja: 'この 写真《しゃしん》みたいに', meaning: { id: 'seperti foto ini', en: 'like this photo' } },
]

const salonActions = [
  { ja: '髪《かみ》を とかす', meaning: { id: 'menyisir rambut', en: 'to comb the hair' } },
  { ja: '髪《かみ》を 分《わ》ける', meaning: { id: 'membelah rambut', en: 'to part the hair' } },
  { ja: '髪《かみ》を まとめる', meaning: { id: 'mengikat／menata rambut', en: 'to tie up the hair' } },
  { ja: '髪《かみ》を アップに する', meaning: { id: 'menyanggul rambut', en: 'to put the hair up' } },
  { ja: '髪《かみ》を 染《そ》める', meaning: { id: 'mewarnai rambut', en: 'to dye the hair' } },
  { ja: 'ひげ／顔《かお》を そる', meaning: { id: 'mencukur kumis／bulu wajah', en: 'to shave the beard／face' } },
  { ja: '化粧《けしょう》／メイクを する', meaning: { id: 'berdandan', en: 'to put on make-up' } },
  { ja: '三《み》つ編《あ》みに する', meaning: { id: 'mengepang', en: 'to braid' } },
  { ja: '刈《か》り上《あ》げる', meaning: { id: 'mencukur pendek bagian bawah rambut', en: 'to crop the hair short at the sides' } },
  { ja: 'パーマを かける', meaning: { id: 'mengeriting rambut', en: 'to perm the hair' } },
]

const salonGroups = [
  { key: 'services', title: 'lampiran.n4_salon_services', hint: '', wide: false, items: salonServices },
  { key: 'requests', title: 'lampiran.n4_salon_requests', hint: 'lampiran.n4_salon_requests_hint', wide: false, items: salonRequests },
  { key: 'actions', title: 'lampiran.n4_salon_actions', hint: '', wide: true, items: salonActions },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 20 — kosakata rumah sakit, 場合は / のに
// ---------------------------------------------------------------------
const hospitalGroups = [
  {
    title: { id: 'Bagian rumah sakit', en: 'Hospital departments' },
    rows: [
      { ja: '整形外科《せいけいげか》', m: { id: 'bedah ortopedi', en: 'orthopedics' } },
      { ja: '皮膚科《ひふか》', m: { id: 'dermatologi', en: 'dermatology' } },
      { ja: '産婦人科《さんふじんか》', m: { id: 'obstetri dan ginekologi', en: 'obstetrics and gynecology' } },
      { ja: '内科《ないか》', m: { id: 'penyakit dalam', en: 'internal medicine' } },
      { ja: '外科《げか》', m: { id: 'bedah', en: 'surgery' } },
      { ja: '眼科《がんか》', m: { id: 'mata', en: 'ophthalmology' } },
      { ja: '小児科《しょうにか》', m: { id: 'pediatri／kesehatan anak', en: 'pediatrics' } },
      { ja: '歯科《しか》', m: { id: 'gigi', en: 'dentistry' } },
      { ja: '泌尿器科《ひにょうきか》', m: { id: 'urologi', en: 'urology' } },
      { ja: '耳鼻咽喉科《じびいんこうか》', m: { id: 'THT', en: 'ENT (ear, nose, throat)' } },
    ],
  },
  {
    title: { id: 'Tempat di rumah sakit', en: 'Places in a hospital' },
    rows: [
      { ja: '受付《うけつけ》', m: { id: 'tempat pendaftaran', en: 'reception' } },
      { ja: '待合室《まちあいしつ》', m: { id: 'ruang tunggu', en: 'waiting room' } },
      { ja: '会計《かいけい》', m: { id: 'kasir', en: 'payment counter' } },
      { ja: '薬局《やっきょく》', m: { id: 'apotek', en: 'pharmacy' } },
      { ja: 'コンビニ', m: { id: 'toko 24 jam', en: 'convenience store' } },
    ],
  },
  {
    title: { id: 'Tindakan medis', en: 'Medical actions' },
    rows: [
      { ja: '診察《しんさつ》する', m: { id: 'memeriksa', en: 'to examine (a patient)' } },
      { ja: '検査《けんさ》する', m: { id: 'memeriksa, mengecek', en: 'to test, to check' } },
      { ja: '注射《ちゅうしゃ》する', m: { id: 'menyuntik', en: 'to give an injection' } },
      { ja: 'レントゲンを 撮《と》る', m: { id: 'meronsen', en: 'to take an X-ray' } },
      { ja: '入院《にゅういん》する／退院《たいいん》する', m: { id: 'opname／keluar dari rumah sakit', en: 'to be admitted／discharged' } },
      { ja: '手術《しゅじゅつ》する', m: { id: 'dioperasi', en: 'to operate' } },
      { ja: '麻酔《ますい》する', m: { id: 'membius', en: 'to anesthetize' } },
    ],
  },
  {
    title: { id: 'Dokumen dan barang', en: 'Documents and items' },
    rows: [
      { ja: '処方箋《しょほうせん》', m: { id: 'resep', en: 'prescription' } },
      { ja: 'カルテ', m: { id: 'data pasien', en: 'medical record' } },
      { ja: '保険証《ほけんしょう》', m: { id: 'kartu asuransi', en: 'insurance card' } },
      { ja: '診察券《しんさつけん》', m: { id: 'kartu pasien', en: 'patient card' } },
    ],
  },
  {
    title: { id: 'Jenis obat', en: 'Types of medicine' },
    rows: [
      { ja: '痛《いた》み止《ど》め', m: { id: 'analgesik', en: 'painkiller' } },
      { ja: '湿布薬《しっぷやく》', m: { id: 'obat koyo', en: 'medicated patch' } },
      { ja: '解熱剤《げねつざい》', m: { id: 'penurun panas', en: 'fever reducer' } },
      { ja: '錠剤《じょうざい》', m: { id: 'pil', en: 'tablet' } },
      { ja: '粉薬《こなぐすり》', m: { id: 'puyer', en: 'powdered medicine' } },
      { ja: 'カプセル', m: { id: 'kapsul', en: 'capsule' } },
    ],
  },
]

const baaiForms = [
  {
    pattern: '〜場合《ばあい》は',
    type: { id: 'Kata kerja, kata sifat い', en: 'Verbs, い-adjectives' },
    rule: { id: 'Bentuk biasa (kamus, ない, た) + 場合は', en: 'Plain form (dictionary, ない, た) + 場合は' },
    example: '遅《おく》れた 場合《ばあい》は、連絡《れんらく》して ください。',
    meaning: { id: 'Jika terlambat, hubungi kami.', en: 'If you are late, please contact us.' },
  },
  {
    pattern: '〜場合《ばあい》は',
    type: { id: 'Kata sifat な', en: 'な-adjectives' },
    rule: { id: 'な + 場合は', en: 'な + 場合は' },
    example: '必要《ひつよう》な 場合《ばあい》は、言《い》って ください。',
    meaning: { id: 'Jika perlu, katakan.', en: 'If it is necessary, please say so.' },
  },
  {
    pattern: '〜場合《ばあい》は',
    type: { id: 'Kata benda', en: 'Nouns' },
    rule: { id: 'の + 場合は', en: 'の + 場合は' },
    example: '雨《あめ》の 場合《ばあい》は、中止《ちゅうし》です。',
    meaning: { id: 'Jika hujan, dibatalkan.', en: 'If it rains, it is cancelled.' },
  },
  {
    pattern: '〜のに',
    type: { id: 'Kata kerja, kata sifat い', en: 'Verbs, い-adjectives' },
    rule: { id: 'Bentuk biasa + のに', en: 'Plain form + のに' },
    example: '急《いそ》いだのに、間《ま》に 合《あ》いませんでした。',
    meaning: { id: 'Padahal sudah terburu-buru, tidak sempat.', en: 'Although I hurried, I was not in time.' },
  },
  {
    pattern: '〜のに',
    type: { id: 'Kata sifat な, kata benda', en: 'な-adjectives, nouns' },
    rule: { id: 'Bentuk biasa, だ → な + のに', en: 'Plain form, だ → な + のに' },
    example: '春《はる》なのに、寒《さむ》いです。',
    meaning: { id: 'Padahal musim semi, dingin.', en: 'Even though it is spring, it is cold.' },
  },
]

const noniCompare = [
  {
    pattern: '〜のに',
    use: { id: 'Fakta yang sudah terjadi + perasaan di luar dugaan atau tidak puas. Tidak untuk pengandaian.', en: 'An actual fact plus a feeling of surprise or dissatisfaction. Not for suppositions.' },
    example: '薬《くすり》を 飲《の》んだのに、治《なお》りません。',
    meaning: { id: 'Padahal sudah minum obat, tidak sembuh.', en: 'Even though I took medicine, I am not getting better.' },
  },
  {
    pattern: '〜が',
    use: { id: 'Kontras netral, tanpa perasaan khusus.', en: 'Neutral contrast with no special feeling.' },
    example: '薬《くすり》を 飲《の》んだが、治《なお》りません。',
    meaning: { id: 'Sudah minum obat, tetapi tidak sembuh.', en: 'I took medicine, but I am not getting better.' },
  },
  {
    pattern: '〜ても',
    use: { id: 'Pengandaian "walaupun …"; bisa untuk hal yang belum terjadi.', en: 'Supposition "even if …"; can be used for things not yet happened.' },
    example: 'あした 雨《あめ》が 降《ふ》っても、行《い》きます。',
    meaning: { id: 'Walaupun besok hujan, saya tetap pergi.', en: 'Even if it rains tomorrow, I will go.' },
  },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 21 — ところ・ばかり・はずです
// ---------------------------------------------------------------------
const tokoroPatterns = [
  {
    pattern: 'Ｖる ところです',
    use: { id: 'Baru akan memulai (sesaat sebelum perbuatan).', en: 'About to start (just before the action).' },
    example: '今《いま》から 出《で》かける ところです。',
    meaning: { id: 'Saya baru mau pergi sekarang.', en: 'I am just about to go out.' },
  },
  {
    pattern: 'Ｖて いる ところです',
    use: { id: 'Sedang berlangsung tepat saat berbicara.', en: 'In progress right at the moment of speaking.' },
    example: '今《いま》 調《しら》べて いる ところです。',
    meaning: { id: 'Sekarang sedang diperiksa.', en: 'We are checking it now.' },
  },
  {
    pattern: 'Ｖた ところです',
    use: { id: 'Baru saja selesai (sangat dekat dengan saat berbicara).', en: 'Has just finished (very close to the moment of speaking).' },
    example: 'たった今《いま》 帰《かえ》った ところです。',
    meaning: { id: 'Dia baru saja pulang.', en: 'He has just left.' },
  },
  {
    pattern: 'Ｖた ばかりです',
    use: { id: 'Baru (perasaan pembicara; jangka waktu boleh agak panjang).', en: 'Recently (the speaker\'s feeling; the time span may be longer).' },
    example: '先月《せんげつ》 入《はい》った ばかりです。',
    meaning: { id: 'Baru masuk bulan lalu.', en: 'I only joined last month.' },
  },
  {
    pattern: '〜はずです',
    use: { id: 'Pasti／seharusnya, berdasarkan bukti atau alasan.', en: 'Should be／must be, based on evidence or reasoning.' },
    example: '来《く》る はずです。電話《でんわ》が ありましたから。',
    meaning: { id: 'Pasti datang. Soalnya ada telepon.', en: 'He should come. There was a call.' },
  },
]

const hazuForms = [
  { type: { id: 'Kata kerja', en: 'Verb' }, connect: '来《く》る ／ 来《こ》ない', example: '来《く》る はずです ／ 来《こ》ない はずです', note: { id: 'Bentuk kamus atau ない', en: 'Dictionary or ない form' } },
  { type: { id: 'Kata sifat い', en: 'い-adjective' }, connect: '高《たか》い ／ 高《たか》くない', example: '高《たか》い はずです ／ 高《たか》くない はずです', note: { id: 'Apa adanya', en: 'As it is' } },
  { type: { id: 'Kata sifat な', en: 'な-adjective' }, connect: '元気《げんき》な', example: '元気《げんき》な はずです', note: { id: 'な ditambahkan', en: 'Add な' } },
  { type: { id: 'Kata benda', en: 'Noun' }, connect: '休《やす》みの', example: '休《やす》みの はずです', note: { id: 'の ditambahkan', en: 'Add の' } },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 21 — Asal-Usul Kata Katakana (かたかな語《ご》のルーツ)
// ---------------------------------------------------------------------
const katakanaOrigins = [
  {
    lang: { id: 'Bahasa Inggris (英語《えいご》)', en: 'English (英語《えいご》)' },
    items: [
      { ja: 'ジャム', meaning: { id: 'selai', en: 'jam' } }, { ja: 'ハム', meaning: { id: 'ham', en: 'ham' } },
      { ja: 'クッキー', meaning: { id: 'kue kering', en: 'cookie' } }, { ja: 'チーズ', meaning: { id: 'keju', en: 'cheese' } },
      { ja: 'エプロン', meaning: { id: 'celemek', en: 'apron' } }, { ja: 'スカート', meaning: { id: 'rok', en: 'skirt' } },
      { ja: 'スーツ', meaning: { id: 'jas', en: 'suit' } }, { ja: 'インフルエンザ', meaning: { id: 'influenza', en: 'influenza' } },
      { ja: 'ストレス', meaning: { id: 'stres', en: 'stress' } }, { ja: 'ドラマ', meaning: { id: 'drama', en: 'drama' } },
      { ja: 'コーラス', meaning: { id: 'paduan suara', en: 'chorus' } }, { ja: 'メロディー', meaning: { id: 'melodi', en: 'melody' } },
      { ja: 'スケジュール', meaning: { id: 'jadwal', en: 'schedule' } }, { ja: 'ティッシュペーパー', meaning: { id: 'tisu', en: 'tissue paper' } },
      { ja: 'トラブル', meaning: { id: 'masalah', en: 'trouble' } }, { ja: 'レジャー', meaning: { id: 'hiburan, rekreasi', en: 'leisure' } },
    ],
  },
  {
    lang: { id: 'Bahasa Prancis (フランス語《ご》)', en: 'French (フランス語《ご》)' },
    items: [
      { ja: 'コロッケ', meaning: { id: 'kroket', en: 'croquette' } }, { ja: 'オムレツ', meaning: { id: 'telur dadar', en: 'omelette' } },
      { ja: 'ズボン', meaning: { id: 'celana', en: 'trousers' } }, { ja: 'ランジェリー', meaning: { id: 'pakaian dalam', en: 'lingerie' } },
      { ja: 'バレエ', meaning: { id: 'balet', en: 'ballet' } }, { ja: 'アトリエ', meaning: { id: 'studio', en: 'studio' } },
      { ja: 'アンケート', meaning: { id: 'angket', en: 'questionnaire' } }, { ja: 'コンクール', meaning: { id: 'kompetisi', en: 'competition' } },
    ],
  },
  {
    lang: { id: 'Bahasa Jerman (ドイツ語《ご》)', en: 'German (ドイツ語《ご》)' },
    items: [
      { ja: 'ソーセージ', meaning: { id: 'sosis', en: 'sausage' } }, { ja: 'レントゲン', meaning: { id: 'rontgen', en: 'X-ray' } },
      { ja: 'アレルギー', meaning: { id: 'alergi', en: 'allergy' } }, { ja: 'メルヘン', meaning: { id: 'cerita dongeng', en: 'fairy tale' } },
      { ja: 'アルバイト', meaning: { id: 'kerja paruh waktu', en: 'part-time job' } }, { ja: 'エネルギー', meaning: { id: 'energi', en: 'energy' } },
      { ja: 'テーマ', meaning: { id: 'tema', en: 'theme' } },
    ],
  },
  {
    lang: { id: 'Bahasa Belanda (オランダ語《ご》)', en: 'Dutch (オランダ語《ご》)' },
    items: [
      { ja: 'ビール', meaning: { id: 'bir', en: 'beer' } }, { ja: 'コーヒー', meaning: { id: 'kopi', en: 'coffee' } },
      { ja: 'ホック', meaning: { id: 'kancing hak', en: 'hook fastener' } }, { ja: 'ズック', meaning: { id: 'sepatu kanvas', en: 'canvas shoes' } },
      { ja: 'メス', meaning: { id: 'pisau bedah', en: 'scalpel' } }, { ja: 'ピンセット', meaning: { id: 'penjepit', en: 'tweezers' } },
      { ja: 'オルゴール', meaning: { id: 'kotak musik', en: 'music box' } }, { ja: 'ゴム', meaning: { id: 'karet', en: 'rubber' } },
      { ja: 'ペンキ', meaning: { id: 'cat', en: 'paint' } }, { ja: 'ガラス', meaning: { id: 'kaca', en: 'glass' } },
    ],
  },
  {
    lang: { id: 'Bahasa Portugis (ポルトガル語《ご》)', en: 'Portuguese (ポルトガル語《ご》)' },
    items: [
      { ja: 'パン', meaning: { id: 'roti', en: 'bread' } }, { ja: 'カステラ', meaning: { id: 'kue bolu', en: 'sponge cake' } },
      { ja: 'ビロード', meaning: { id: 'beledu', en: 'velvet' } }, { ja: 'ボタン', meaning: { id: 'kancing', en: 'button' } },
      { ja: 'カルタ', meaning: { id: 'kartu', en: 'playing cards' } }, { ja: 'コップ', meaning: { id: 'gelas', en: 'cup, glass' } },
    ],
  },
  {
    lang: { id: 'Bahasa Italia (イタリア語《ご》)', en: 'Italian (イタリア語《ご》)' },
    items: [
      { ja: 'マカロニ', meaning: { id: 'makaroni', en: 'macaroni' } }, { ja: 'パスタ', meaning: { id: 'pasta', en: 'pasta' } },
      { ja: 'スパゲッティ', meaning: { id: 'spageti', en: 'spaghetti' } }, { ja: 'オペラ', meaning: { id: 'opera', en: 'opera' } },
    ],
  },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 22 — ringkasan そうです／ようです
// ---------------------------------------------------------------------
const souyouPatterns = [
  {
    pattern: 'ふつうけい そうです',
    meaning: { id: 'Katanya ~ (kabar dari pihak lain)', en: 'I hear that ~ (news from someone else)' },
    form: { id: 'Bentuk biasa. Kata sifat な dan kata benda tetap memakai だ.', en: 'Plain form. な-adjectives and nouns keep だ.' },
    example: '駅前《えきまえ》に 新《あたら》しい 店《みせ》が できるそうです。',
    exMeaning: { id: 'Katanya akan ada toko baru di depan stasiun.', en: 'I hear a new shop is opening in front of the station.' },
  },
  {
    pattern: '語幹《ごかん》 そうです',
    meaning: { id: 'Tampaknya ~ (kesan dari penampilan)', en: 'Looks ~ (impression from appearance)' },
    form: { id: 'Bentuk ます tanpa ます, atau kata sifat tanpa い／な.', en: 'ます-stem, or adjective without い／な.' },
    example: 'この ケーキは おいしそうです。',
    exMeaning: { id: 'Kue ini kelihatannya enak.', en: 'This cake looks delicious.' },
  },
  {
    pattern: 'ふつうけい ようです',
    meaning: { id: 'Rupanya ~ (kesimpulan pembicara dari keadaan)', en: 'It seems ~ (the speaker\'s conclusion from conditions)' },
    form: { id: 'Bentuk biasa. Kata sifat な memakai な, kata benda memakai の.', en: 'Plain form. な-adjectives take な, nouns take の.' },
    example: '道《みち》が ぬれて いますね。夜《よる》に 雨《あめ》が 降《ふ》ったようです。',
    exMeaning: { id: 'Jalannya basah. Rupanya semalam hujan.', en: 'The road is wet. It seems it rained at night.' },
  },
  {
    pattern: '〜と 言《い》って いました',
    meaning: { id: 'Dia bilang ~ (sumber ucapan jelas)', en: 'He／she said ~ (the speaker is the source)' },
    form: { id: 'Bentuk biasa + と 言《い》って いました.', en: 'Plain form + と 言《い》って いました.' },
    example: 'たなかさんは あした 休《やす》むと 言《い》って いました。',
    exMeaning: { id: 'Tanaka bilang besok dia libur.', en: 'Tanaka said he will take a day off tomorrow.' },
  },
  {
    pattern: '〜に よると',
    meaning: { id: 'Menurut ~ (menyatakan sumber kabar)', en: 'According to ~ (gives the source of news)' },
    form: { id: 'Sumber + に よると, lalu kalimat ふつうけい そうです.', en: 'Source + に よると, followed by a ふつうけい そうです sentence.' },
    example: 'ニュースに よると、きのう 大《おお》きい 事故《じこ》が あったそうです。',
    exMeaning: { id: 'Menurut berita, kemarin ada kecelakaan besar.', en: 'According to the news, there was a big accident yesterday.' },
  },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 22 — 擬音語・擬態語 (onomatope)
// ---------------------------------------------------------------------
const onomatope = [
  { word: 'ザーザー', verb: '降《ふ》る', meaning: { id: 'lebat (hujan)', en: 'heavy (rain)' }, example: 'ザーザー 雨《あめ》が 降《ふ》って います。', exMeaning: { id: 'Hujan turun dengan lebat.', en: 'The rain is pouring.' } },
  { word: 'ビュービュー', verb: '吹《ふ》く', meaning: { id: 'hyu hyu (angin)', en: 'whoosh (wind)' }, example: '風《かぜ》が ビュービュー 吹《ふ》いて います。', exMeaning: { id: 'Angin bertiup hyu hyu.', en: 'The wind is howling.' } },
  { word: 'ゴロゴロ', verb: '鳴《な》る', meaning: { id: 'grudug (petir)', en: 'rumble (thunder)' }, example: '空《そら》で かみなりが ゴロゴロ 鳴《な》って います。', exMeaning: { id: 'Petir bergemuruh di langit.', en: 'Thunder is rumbling in the sky.' } },
  { word: 'ワンワン', verb: 'ほえる', meaning: { id: 'guk (menggonggong)', en: 'woof (barking)' }, example: '犬《いぬ》が ワンワン ほえて います。', exMeaning: { id: 'Anjing menggonggong guk-guk.', en: 'The dog is barking.' } },
  { word: 'ニャーニャー', verb: '鳴《な》く', meaning: { id: 'meong (mengeong)', en: 'meow (cat)' }, example: '猫《ねこ》が ニャーニャー 鳴《な》いて います。', exMeaning: { id: 'Kucing mengeong.', en: 'The cat is meowing.' } },
  { word: 'カーカー', verb: '鳴《な》く', meaning: { id: 'gak (gagak)', en: 'caw (crow)' }, example: 'からすが カーカー 鳴《な》いて います。', exMeaning: { id: 'Gagak berkoak gak-gak.', en: 'A crow is cawing.' } },
  { word: 'げらげら', verb: '笑《わら》う', meaning: { id: 'tertawa terbahak-bahak', en: 'laughing loudly' }, example: 'みんなで げらげら 笑《わら》いました。', exMeaning: { id: 'Semua tertawa terbahak-bahak.', en: 'Everyone burst out laughing.' } },
  { word: 'しくしく', verb: '泣《な》く', meaning: { id: 'menangis tersedu-sedu', en: 'sobbing quietly' }, example: '女《おんな》の 子《こ》が しくしく 泣《な》いて います。', exMeaning: { id: 'Seorang anak perempuan menangis tersedu-sedu.', en: 'A girl is sobbing quietly.' } },
  { word: 'きょろきょろ', verb: '見《み》る', meaning: { id: 'melihat dengan gelisah', en: 'looking around restlessly' }, example: '道《みち》が わからなくて、きょろきょろ 見《み》て いました。', exMeaning: { id: 'Karena tidak tahu jalan, dia celingukan.', en: 'Not knowing the way, he was looking around.' } },
  { word: 'ぱくぱく', verb: '食《た》べる', meaning: { id: 'makan dengan lahap', en: 'eating heartily' }, example: '子《こ》どもが ケーキを ぱくぱく 食《た》べました。', exMeaning: { id: 'Anak itu makan kue dengan lahap.', en: 'The child gobbled up the cake.' } },
  { word: 'ぐうぐう', verb: '寝《ね》る', meaning: { id: 'tidur pulas', en: 'sleeping soundly' }, example: '疲《つか》れて、ぐうぐう 寝《ね》て います。', exMeaning: { id: 'Dia kelelahan dan tidur pulas.', en: 'He is tired and sleeping soundly.' } },
  { word: 'すらすら', verb: '読《よ》む', meaning: { id: 'membaca dengan lancar', en: 'reading fluently' }, example: '兄《あに》は 英語《えいご》の 本《ほん》を すらすら 読《よ》みます。', exMeaning: { id: 'Kakak laki-laki saya membaca buku bahasa Inggris dengan lancar.', en: 'My brother reads English books fluently.' } },
  { word: 'ざらざら', verb: 'している', meaning: { id: 'terasa kasar', en: 'feeling rough' }, example: 'この 紙《かみ》は ざらざら して います。', exMeaning: { id: 'Kertas ini terasa kasar.', en: 'This paper feels rough.' } },
  { word: 'べたべた', verb: 'している', meaning: { id: 'terasa lengket', en: 'feeling sticky' }, example: '手《て》が べたべた して います。', exMeaning: { id: 'Tangan saya terasa lengket.', en: 'My hands feel sticky.' } },
  { word: 'つるつる', verb: 'している', meaning: { id: 'terasa licin', en: 'feeling slippery' }, example: '雨《あめ》で 道《みち》が つるつる して います。', exMeaning: { id: 'Karena hujan, jalannya licin.', en: 'The road is slippery from the rain.' } },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 23 — Kata Kerja Kausatif (使役)
// ---------------------------------------------------------------------
const causativeGroups = [
  { type: { id: 'Kelompok I', en: 'Group I' }, masu: '行きます', polite: '行かせます', plain: '行かせる', te: '行かせて' },
  { type: { id: 'Kelompok II', en: 'Group II' }, masu: '食べます', polite: '食べさせます', plain: '食べさせる', te: '食べさせて' },
  { type: { id: 'Kelompok III', en: 'Group III' }, masu: '来《き》ます', polite: '来《こ》させます', plain: '来《こ》させる', te: '来《こ》させて' },
  { type: { id: 'Kelompok III', en: 'Group III' }, masu: 'します', polite: 'させます', plain: 'させる', te: 'させて' },
]

const causativeEndings = [
  { ending: '〜く', example: '書《か》く → 書《か》かせる', meaning: { id: 'menyuruh menulis', en: 'to make someone write' } },
  { ending: '〜ぐ', example: '泳《およ》ぐ → 泳《およ》がせる', meaning: { id: 'menyuruh berenang', en: 'to make someone swim' } },
  { ending: '〜す', example: '話《はな》す → 話《はな》させる', meaning: { id: 'menyuruh berbicara', en: 'to make someone speak' } },
  { ending: '〜つ', example: '待《ま》つ → 待《ま》たせる', meaning: { id: 'membuat menunggu', en: 'to make someone wait' } },
  { ending: '〜ぬ', example: '死《し》ぬ → 死《し》なせる', meaning: { id: 'membiarkan meninggal', en: 'to let someone die' } },
  { ending: '〜ぶ', example: '遊《あそ》ぶ → 遊《あそ》ばせる', meaning: { id: 'membiarkan bermain', en: 'to let someone play' } },
  { ending: '〜む', example: '飲《の》む → 飲《の》ませる', meaning: { id: 'menyuruh minum', en: 'to make someone drink' } },
  { ending: '〜る', example: '帰《かえ》る → 帰《かえ》らせる', meaning: { id: 'menyuruh pulang', en: 'to make someone go home' } },
  { ending: '〜う', example: '買《か》う → 買《か》わせる', meaning: { id: 'menyuruh membeli (う → わ)', en: 'to make someone buy (う → わ)' } },
]

const causativePatterns = [
  {
    pattern: 'Ｎ(orang)を ＋ Ｖ使役',
    use: { id: 'Kata kerja intransitif (tanpa objek)', en: 'Intransitive verb (no object)' },
    example: '部長《ぶちょう》は ミラーさんを 出張《しゅっちょう》させます。',
    meaning: { id: 'Kepala bagian menyuruh Miller dinas.', en: 'The department head sends Miller on a business trip.' },
  },
  {
    pattern: 'Ｎ(orang)に ＋ Ｎ(tempat)を ＋ Ｖ使役',
    use: { id: 'Kata kerja gerak yang melewati tempat (を)', en: 'Movement verb passing through a place (を)' },
    example: '子《こ》どもに 公園《こうえん》を 走《はし》らせます。',
    meaning: { id: 'Menyuruh anak berlari di taman.', en: 'I make my child run in the park.' },
  },
  {
    pattern: 'Ｎ１(orang)に ＋ Ｎ２を ＋ Ｖ使役',
    use: { id: 'Kata kerja transitif (punya objek)', en: 'Transitive verb (has an object)' },
    example: '母《はは》は 子《こ》どもに 皿《さら》を 洗《あら》わせます。',
    meaning: { id: 'Ibu menyuruh anak mencuci piring.', en: 'Mother makes her child wash the dishes.' },
  },
  {
    pattern: 'Ｖ使役て いただけませんか',
    use: { id: 'Minta izin dengan sopan untuk melakukan sendiri', en: 'Politely asking permission to do something yourself' },
    example: 'あした 休《やす》ませて いただけませんか。',
    meaning: { id: 'Bolehkah saya libur besok?', en: 'May I take tomorrow off?' },
  },
  {
    pattern: 'Ｖて もらいます／いただきます',
    use: { id: 'Menerima kebaikan orang yang tidak bisa diperintah (setara／atasan)', en: 'Receiving a favour from someone you cannot command (peer／superior)' },
    example: '友達《ともだち》に 荷物《にもつ》を 持《も》って もらいました。',
    meaning: { id: 'Saya dibantu teman membawakan barang.', en: 'A friend carried my luggage for me.' },
  },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 23 — Mendidik & Disiplin (しつける・鍛える)
// ---------------------------------------------------------------------
const disciplineWords = [
  { ja: '自然《しぜん》の 中《なか》で 遊《あそ》ぶ', name: { id: 'bermain di tengah alam', en: 'to play in nature' }, example: '子《こ》どもを 自然《しぜん》の 中《なか》で 遊《あそ》ばせます。', meaning: { id: 'Membiarkan anak bermain di alam.', en: 'I let my child play in nature.' } },
  { ja: 'スポーツを する', name: { id: 'berolahraga', en: 'to play sports' }, example: '子《こ》どもに スポーツを させます。', meaning: { id: 'Menyuruh anak berolahraga.', en: 'I have my child play sports.' } },
  { ja: '一人《ひとり》で 旅行《りょこう》する', name: { id: 'berjalan-jalan sendirian', en: 'to travel alone' }, example: '子《こ》どもを 一人《ひとり》で 旅行《りょこう》させます。', meaning: { id: 'Membiarkan anak bepergian sendirian.', en: 'I let my child travel alone.' } },
  { ja: 'いろいろな 経験《けいけん》を する', name: { id: 'mencari berbagai pengalaman', en: 'to gain various experiences' }, example: '子《こ》どもに いろいろな 経験《けいけん》を させます。', meaning: { id: 'Membiarkan anak mencari pengalaman.', en: 'I let my child gain varied experiences.' } },
  { ja: 'いい 本《ほん》を たくさん 読《よ》む', name: { id: 'membaca banyak buku yang bagus', en: 'to read many good books' }, example: '子《こ》どもに いい 本《ほん》を たくさん 読《よ》ませます。', meaning: { id: 'Menyuruh anak membaca banyak buku bagus.', en: 'I have my child read many good books.' } },
  { ja: 'お年寄《としよ》りの 話《はなし》を 聞《き》く', name: { id: 'mendengarkan cerita orang lanjut usia', en: 'to listen to elderly people' }, example: '子《こ》どもに お年寄《としよ》りの 話《はなし》を 聞《き》かせます。', meaning: { id: 'Menyuruh anak mendengarkan cerita orang lanjut usia.', en: 'I have my child listen to the stories of elderly people.' } },
  { ja: 'ボランティアに 参加《さんか》する', name: { id: 'mengikuti sukarelawan', en: 'to take part in volunteering' }, example: '子《こ》どもを ボランティアに 参加《さんか》させます。', meaning: { id: 'Mengikutsertakan anak dalam kegiatan sukarelawan.', en: 'I have my child take part in volunteer work.' } },
  { ja: 'うちの 仕事《しごと》を 手伝《てつだ》う', name: { id: 'membantu urusan rumah tangga', en: 'to help with housework' }, example: '子《こ》どもに うちの 仕事《しごと》を 手伝《てつだ》わせます。', meaning: { id: 'Menyuruh anak membantu urusan rumah.', en: 'I have my child help with housework.' } },
  { ja: '弟《おとうと》や 妹《いもうと》の 世話《せわ》を する', name: { id: 'menjaga adik laki-laki dan perempuan', en: 'to look after younger siblings' }, example: '子《こ》どもに 弟《おとうと》の 世話《せわ》を させます。', meaning: { id: 'Menyuruh anak menjaga adiknya.', en: 'I have my child look after his younger brother.' } },
  { ja: '自分《じぶん》が やりたい ことを やる', name: { id: 'membuat apa yang dia inginkan', en: 'to do what one wants' }, example: '子《こ》どもに やりたい ことを やらせます。', meaning: { id: 'Membiarkan anak melakukan apa yang diinginkan.', en: 'I let my child do what he wants.' } },
  { ja: '自分《じぶん》の ことは 自分《じぶん》で 決《き》める', name: { id: 'mengambil keputusan untuk dirinya sendiri', en: 'to decide for oneself' }, example: '子《こ》どもに 進路《しんろ》を 自分《じぶん》で 決《き》めさせます。', meaning: { id: 'Membiarkan anak memutuskan jalannya sendiri.', en: 'I let my child decide his path himself.' } },
  { ja: '自信《じしん》を 持《も》つ', name: { id: 'percaya diri', en: 'to have confidence' }, example: '子《こ》どもに 自信《じしん》を 持《も》たせます。', meaning: { id: 'Membuat anak percaya diri.', en: 'I help my child gain confidence.' } },
  { ja: '責任《せきにん》を 持《も》つ', name: { id: 'bertanggung jawab', en: 'to take responsibility' }, example: '子《こ》どもに 責任《せきにん》を 持《も》たせます。', meaning: { id: 'Membuat anak bertanggung jawab.', en: 'I make my child take responsibility.' } },
  { ja: '我慢《がまん》する', name: { id: 'sabar', en: 'to be patient' }, example: '子《こ》どもに 我慢《がまん》させます。', meaning: { id: 'Menyuruh anak bersabar.', en: 'I make my child be patient.' } },
  { ja: '塾《じゅく》へ 行《い》く', name: { id: 'pergi les', en: 'to go to cram school' }, example: '子《こ》どもを 塾《じゅく》へ 行《い》かせます。', meaning: { id: 'Menyuruh anak pergi les.', en: 'I send my child to cram school.' } },
  { ja: 'ピアノや 英語《えいご》を 習《なら》う', name: { id: 'belajar piano atau bahasa Inggris', en: 'to learn piano or English' }, example: '子《こ》どもに ピアノを 習《なら》わせます。', meaning: { id: 'Menyuruh anak belajar piano.', en: 'I have my child take piano lessons.' } },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 24 — Bahasa Hormat (尊敬語)
// ---------------------------------------------------------------------
const honorificSpecial = [
  { plain: 'います／行《い》きます／来《き》ます', honorific: 'いらっしゃいます', meaning: { id: 'ada／pergi／datang', en: 'to be／go／come' } },
  { plain: '食《た》べます／飲《の》みます', honorific: '召《め》し上《あ》がります', meaning: { id: 'makan／minum', en: 'to eat／drink' } },
  { plain: '言《い》います', honorific: 'おっしゃいます', meaning: { id: 'berkata', en: 'to say' } },
  { plain: 'します', honorific: 'なさいます', meaning: { id: 'melakukan', en: 'to do' } },
  { plain: '見《み》ます', honorific: 'ご覧《らん》に なります', meaning: { id: 'melihat', en: 'to look at' } },
  { plain: '知《し》って います', honorific: 'ご存《ぞん》じです', meaning: { id: 'mengetahui', en: 'to know' } },
  { plain: 'くれます', honorific: 'くださいます', meaning: { id: 'memberi (kepada saya)', en: 'to give (to me)' } },
]

const honorificForms = [
  {
    pattern: 'Vれます／Vられます',
    how: { id: 'Bentuk sama dengan pasif. Kelompok I: u → a + れます; II: Vられます; III: されます, 来《こ》られます.', en: 'Same shape as the passive. Group I: u → a + れます; II: Vられます; III: されます, 来《こ》られます.' },
    example: '先生《せんせい》は 本《ほん》を 読《よ》まれます。',
    meaning: { id: 'Guru membaca buku.', en: 'The teacher reads a book.' },
  },
  {
    pattern: 'お Vます形 に なります',
    how: { id: 'お + bentuk-ます tanpa ます + に なります. Tidak untuk kata kerja satu suku kata dan kelompok III.', en: 'お + ます-stem + に なります. Not for one-syllable verbs or group III.' },
    example: '社長《しゃちょう》は お帰《かえ》りに なりました。',
    meaning: { id: 'Direktur utama sudah pulang.', en: 'The president has gone home.' },
  },
  {
    pattern: 'お Vます形 ください',
    how: { id: 'Permintaan hormat. Kelompok III: ご + kata benda + ください.', en: 'A respectful request. Group III: ご + noun + ください.' },
    example: 'どうぞ お入《はい》り ください。',
    meaning: { id: 'Silakan masuk.', en: 'Please come in.' },
  },
  {
    pattern: 'Bentuk khusus + て ください',
    how: { id: 'Kata kerja dengan bentuk khusus memakai bentuk-て-nya.', en: 'Verbs with a special form use its て-form.' },
    example: 'また いらっしゃって ください。',
    meaning: { id: 'Silakan datang lagi.', en: 'Please come again.' },
  },
]

const honorificPrefix = [
  { kind: { id: 'Kata benda', en: 'Noun' }, o: 'お国《くに》、お名前《なまえ》、お仕事《しごと》、お約束《やくそく》、お電話《でんわ》', go: 'ご家族《かぞく》、ご意見《いけん》、ご旅行《りょこう》' },
  { kind: { id: 'Kata sifat な', en: 'な-adjective' }, o: 'お元気《げんき》、お上手《じょうず》、お暇《ひま》', go: 'ご熱心《ねっしん》、ご親切《しんせつ》' },
  { kind: { id: 'Kata sifat い', en: 'い-adjective' }, o: 'お忙《いそが》しい、お若《わか》い', go: '—' },
  { kind: { id: 'Kata keterangan', en: 'Adverb' }, o: '—', go: 'ご自由《じゆう》に' },
]

const irregularConj = [
  { dict: 'いらっしゃる', forms: 'いらっしゃいます／いらっしゃらない／いらっしゃった／いらっしゃって' },
  { dict: 'なさる', forms: 'なさいます／なさらない／なさった／なさって' },
  { dict: 'くださる', forms: 'くださいます／くださらない／くださった／くださって' },
  { dict: 'おっしゃる', forms: 'おっしゃいます／おっしゃらない／おっしゃった／おっしゃって' },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 24 — Acara Musiman (季節の行事)
// ---------------------------------------------------------------------
const seasonalEvents = [
  { icon: '🎍', ja: 'お正月《しょうがつ》', date: { id: '1 Januari – 3 Januari', en: '1 – 3 January' }, name: { id: 'Tahun Baru', en: 'New Year' }, desc: { id: 'Perayaan awal tahun. Orang berkunjung ke kuil atau wihara untuk berdoa bagi kesehatan dan kesejahteraan.', en: 'The celebration of the new year. People visit a shrine or temple to pray for health and prosperity.' } },
  { icon: '🫘', ja: '豆《まめ》まき', date: { id: 'Sekitar 3 Februari', en: 'Around 3 February' }, name: { id: 'Upacara melempar kacang', en: 'Bean-throwing ritual' }, desc: { id: 'Pada malam Setsubun (pergantian musim), kacang dilempar sambil berseru agar roh jahat keluar dan keberuntungan masuk.', en: 'On the night of Setsubun (the change of season), beans are thrown while calling for bad spirits to leave and good luck to come in.' } },
  { icon: '🎎', ja: 'ひな祭《まつ》り', date: { id: '3 Maret', en: '3 March' }, name: { id: 'Perayaan Boneka', en: 'Doll Festival' }, desc: { id: 'Keluarga yang punya anak perempuan menghias boneka Hinaningyo.', en: 'Families with daughters display Hinaningyo dolls.' } },
  { icon: '🎏', ja: 'こどもの日《ひ》', date: { id: '5 Mei', en: '5 May' }, name: { id: 'Hari Anak', en: "Children's Day" }, desc: { id: 'Hari untuk merayakan pertumbuhan dan kesehatan anak. Dahulu terutama untuk anak laki-laki.', en: 'A day to celebrate the growth and health of children. It was originally mainly for boys.' } },
  { icon: '🎋', ja: '七夕《たなばた》', date: { id: '7 Juli', en: '7 July' }, name: { id: 'Perayaan Bintang', en: 'Star Festival' }, desc: { id: 'Berasal dari legenda Tiongkok tentang dua bintang yang bertemu setahun sekali di tepi Bimasakti. Orang menggantungkan permohonan di bambu.', en: 'Based on a Chinese legend of two stars meeting once a year across the Milky Way. People hang wishes on bamboo.' } },
  { icon: '🏮', ja: 'お盆《ぼん》', date: { id: '13 – 15 Agustus', en: '13 – 15 August' }, name: { id: 'Perayaan Bon', en: 'Bon Festival' }, desc: { id: 'Acara Buddha untuk menyambut dan mendoakan arwah leluhur. Orang berziarah ke makam.', en: 'A Buddhist event to welcome and pray for ancestral spirits. People visit graves.' } },
  { icon: '🌕', ja: 'お月見《つきみ》', date: { id: 'Sekitar 15 September', en: 'Around 15 September' }, name: { id: 'Melihat bulan', en: 'Moon viewing' }, desc: { id: 'Menikmati keindahan bulan purnama musim gugur.', en: 'Enjoying the beauty of the autumn full moon.' } },
  { icon: '🔔', ja: '大《おお》みそか', date: { id: '31 Desember', en: '31 December' }, name: { id: 'Malam Tahun Baru', en: "New Year's Eve" }, desc: { id: 'Hari terakhir tahun. Orang bersih-bersih dan menyiapkan Osechi (makanan Tahun Baru). Menjelang tengah malam, lonceng kuil dibunyikan.', en: 'The last day of the year. People clean and prepare Osechi (New Year food). Near midnight, temple bells ring.' } },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 25 — kata merendahkan diri (謙譲語)
// ---------------------------------------------------------------------
const keigoPatterns = [
  {
    pattern: 'お ＋ Ｖ(ます形) ＋ します',
    use: { id: 'Kata kerja Kelompok I dan II. Tidak untuk kata kerja satu suku kata seperti 見ます, います.', en: 'Group I and II verbs. Not for one-syllable stems such as 見ます, います.' },
    example: 'お持《も》ちします ／ お送《おく》りします',
    meaning: { id: 'Saya bawakan ／ Saya antarkan', en: 'I will carry it ／ I will escort you' },
  },
  {
    pattern: 'ご ＋ Ｎ ＋ します',
    use: { id: 'Kata kerja Kelompok III (kata benda + します). Pengecualian: お電話します, お約束します.', en: 'Group III verbs (noun + します). Exceptions: お電話します, お約束します.' },
    example: 'ご案内《あんない》します ／ ご説明《せつめい》します',
    meaning: { id: 'Saya pandu ／ Saya jelaskan', en: 'I will guide you ／ I will explain' },
  },
  {
    pattern: '申します ／ 参ります ／ いたします',
    use: { id: '謙譲語II: bersikap sopan kepada pendengar, tanpa sasaran khusus.', en: '謙譲語II: courtesy toward the listener, no specific target.' },
    example: '私《わたし》は ワヒュと 申《もう》します。',
    meaning: { id: 'Nama saya Wahyu.', en: 'My name is Wahyu.' },
  },
  {
    pattern: '〜て おります',
    use: { id: 'Pengganti 〜て います dalam bahasa yang sangat sopan.', en: 'Replaces 〜て います in very polite speech.' },
    example: '日本語《にほんご》を 勉強《べんきょう》して おります。',
    meaning: { id: 'Saya sedang belajar bahasa Jepang.', en: 'I am studying Japanese.' },
  },
]

const humbleVerbs = [
  { plain: 'いきます ／ きます', humble: '参《まい》ります', meaning: { id: 'pergi ／ datang', en: 'to go ／ to come' } },
  { plain: 'います', humble: 'おります', meaning: { id: 'ada', en: 'to be' } },
  { plain: 'たべます ／ のみます ／ もらいます', humble: 'いただきます', meaning: { id: 'makan ／ minum ／ menerima', en: 'to eat ／ drink ／ receive' } },
  { plain: 'いいます', humble: '申《もう》します', meaning: { id: 'berkata, bernama', en: 'to say, to be called' } },
  { plain: 'します', humble: 'いたします', meaning: { id: 'melakukan', en: 'to do' } },
  { plain: 'みます', humble: '拝見《はいけん》します', meaning: { id: 'melihat', en: 'to see' } },
  { plain: 'しります', humble: '存《ぞん》じます', meaning: { id: 'mengenal, tahu', en: 'to know' } },
  { plain: 'ききます ／ いきます(berkunjung)', humble: '伺《うかが》います', meaning: { id: 'bertanya, mendengar, berkunjung', en: 'to ask, hear, visit' } },
  { plain: 'あいます', humble: 'お目《め》に かかります', meaning: { id: 'bertemu', en: 'to meet' } },
  { plain: 'わたし', humble: '私《わたくし》', meaning: { id: 'saya', en: 'I' } },
]

// ---------------------------------------------------------------------
// N4 · Pelajaran 25 — cara menulis alamat (封筒・はがき)
// ---------------------------------------------------------------------
const addressRows = [
  {
    part: { id: 'Penerima: nomor kode pos', en: 'Recipient: postal code' },
    how: { id: 'Ditulis pada kotak-kotak di bagian kanan atas (3 angka – 4 angka).', en: 'Written in the boxes at the top right (3 digits – 4 digits).' },
  },
  {
    part: { id: 'Penerima: alamat', en: 'Recipient: address' },
    how: { id: 'Ditulis dari provinsi sampai nomor rumah, di sisi kanan, ditulis ke bawah.', en: 'Written from prefecture down to the house number, on the right side, running downward.' },
  },
  {
    part: { id: 'Penerima: nama', en: 'Recipient: name' },
    how: { id: 'Nama lengkap di tengah dengan ukuran huruf paling besar, diikuti 様《さま》.', en: 'Full name in the centre in the largest characters, followed by 様《さま》.' },
  },
  {
    part: { id: 'Untuk guru sendiri', en: 'For your own teacher' },
    how: { id: 'Pakai 先生《せんせい》 sebagai ganti 様《さま》: 田中《たなか》 昭子《あきこ》 先生《せんせい》.', en: 'Use 先生《せんせい》 instead of 様《さま》.' },
  },
  {
    part: { id: 'Pengirim: alamat', en: 'Sender: address' },
    how: { id: 'Ditulis lebih kecil di sisi kiri bawah (amplop: bagian belakang), ditulis ke bawah.', en: 'Written smaller at the lower left (on an envelope: the back), running downward.' },
  },
  {
    part: { id: 'Pengirim: nama dan kode pos', en: 'Sender: name and postal code' },
    how: { id: 'Nama pengirim di sebelah kiri alamat, dan kode pos pengirim pada kotak di bagian bawah.', en: 'The name of the sender to the left of the address, and the sender postal code in the boxes at the bottom.' },
  },
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
          {{ level === 'N4' ? t('lampiran.subtitle_n4') : t('lampiran.subtitle') }}
        </p>
      </div>
      <div class="d-flex align-center flex-wrap ga-2">
        <LevelSwitch />
        <VChip color="primary" variant="tonal" prepend-icon="tabler-certificate">
          {{ level }}
        </VChip>
      </div>
    </div>

    <div
      class="lampiran-section-tabs mb-4"
      role="tablist"
      :aria-label="t('lampiran.title')"
    >
      <button
        v-for="opt in sections"
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
                    <td class="font-weight-medium" style="inline-size: 48px;">{{ item.n }}</td>
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
                    <td class="font-weight-medium" style="inline-size: 48px;">{{ item.n }}</td>
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
                    <td class="font-weight-medium" style="inline-size: 64px;">{{ item.n }}</td>
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
                    <td class="font-weight-medium" style="inline-size: 64px;">{{ item.n }}</td>
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
                    <td class="font-weight-medium" style="inline-size: 110px;">{{ item.n }}</td>
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
                    <td class="font-weight-medium" style="inline-size: 64px;">{{ item.n }}</td>
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
              <tr><th /><th>hari</th><th>pagi</th><th>malam</th></tr>
            </thead>
            <tbody>
              <tr v-for="row in relativeDays" :key="row.id">
                <td class="text-medium-emphasis">{{ row.label }}</td>
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
              <tr><th /><th>minggu</th><th>bulan</th><th>tahun</th></tr>
            </thead>
            <tbody>
              <tr v-for="row in relativePeriods" :key="row.id">
                <td class="text-medium-emphasis">{{ row.label }}</td>
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
                    <td class="font-weight-medium" style="inline-size: 40px;">{{ item.n }}</td>
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
                    <td class="font-weight-medium" style="inline-size: 40px;">{{ item.n }}</td>
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
                    <td class="text-medium-emphasis">{{ item.id }}</td>
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
                    <td class="font-weight-medium" style="inline-size: 40px;">{{ item.n }}</td>
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
                    <td class="font-weight-medium" style="inline-size: 40px;">{{ item.n }}</td>
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
                    <td class="font-weight-medium">{{ row.label }}</td>
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
                    <td class="font-weight-medium" style="inline-size: 32px;">{{ counterNums[i] }}</td>
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
                <td class="font-weight-medium">{{ v.kamus }}</td>
                <td>{{ v.te }}</td>
                <td>{{ v.nai }}</td>
                <td>{{ v.ta }}</td>
                <td class="text-medium-emphasis">{{ v.arti }}</td>
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
                    <td class="font-weight-medium">{{ kamus }}</td>
                    <td>{{ negateIAdjective(kamus.replace(/\[.*\]/, '')) }}</td>
                    <td class="text-medium-emphasis">{{ arti }}</td>
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
                    <td class="font-weight-medium">{{ kamus }}</td>
                    <td>{{ kamus }}じゃない</td>
                    <td class="text-medium-emphasis">{{ arti }}</td>
                  </tr>
                </tbody>
              </VTable>
            </VCard>
          </VCol>
        </VRow>
      </VWindowItem>
      <VWindowItem value="n4g-patterns">
        <VCard :title="t('lampiran.n4_ndesu_forms')" :subtitle="t('lampiran.n4_ndesu_forms_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_type') }}</th>
                <th>{{ t('lampiran.n4_col_plain') }}</th>
                <th>{{ t('lampiran.n4_col_with_ndesu') }}</th>
                <th>{{ t('lampiran.n4_col_note') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(f, i) in ndesuForms" :key="`nd-${i}`">
                <td class="font-weight-medium">
                  {{ tr(f.type) }}
                </td>
                <td>{{ f.plain }}</td>
                <td class="text-h6">
                  {{ f.example }}
                </td>
                <td class="text-medium-emphasis">
                  {{ tr(f.note) }}
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_ndesu_uses')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(u, i) in ndesuUses" :key="`nu-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  {{ u.pattern }}
                </td>
                <td>{{ tr(u.use) }}</td>
                <td>
                  <RubyText :text="u.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(u.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_l3_patterns_title')" :subtitle="t('lampiran.n4_l3_patterns_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(p, i) in l3Patterns" :key="`l3p-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  {{ p.pattern }}
                </td>
                <td><RubyText :text="tr(p.form)" /></td>
                <td>
                  <RubyText :text="p.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(p.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_nagara_title')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_masu') }}</th>
                <th>{{ t('lampiran.n4_col_stem') }}</th>
                <th>{{ t('lampiran.n4_col_nagara') }}</th>
                <th>{{ t('lampiran.n4_col_note') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(f, i) in nagaraForms" :key="`ng-${i}`">
                <td><RubyText :text="f.v" /></td>
                <td><RubyText :text="f.stem" /></td>
                <td class="text-h6">
                  <RubyText :text="f.result" />
                </td>
                <td class="text-medium-emphasis">
                  {{ tr(f.note) }}
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_l9_patterns_title')" :subtitle="t('lampiran.n4_l9_patterns_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(p, i) in p9Patterns" :key="`p9p-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  {{ p.pattern }}
                </td>
                <td>{{ tr(p.use) }}</td>
                <td>
                  <RubyText :text="p.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(p.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_l9_forms_title')" :subtitle="t('lampiran.n4_l9_forms_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_group') }}</th>
                <th>{{ t('lampiran.n4_col_dict') }}</th>
                <th>{{ t('lampiran.n4_col_ta') }}</th>
                <th>{{ t('lampiran.n4_col_te') }}</th>
                <th>{{ t('lampiran.n4_col_naide') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(f, i) in p9Forms" :key="`p9f-${i}`">
                <td>{{ f.group }}</td>
                <td class="text-h6">
                  <RubyText :text="f.dict" />
                </td>
                <td><RubyText :text="f.ta" /></td>
                <td><RubyText :text="f.te" /></td>
                <td><RubyText :text="f.naide" /></td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_l9_compare_title')" :subtitle="t('lampiran.n4_l9_compare_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_nuance') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(c, i) in p9Compare" :key="`p9c-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  {{ c.pattern }}
                </td>
                <td>{{ tr(c.nuance) }}</td>
                <td><RubyText :text="c.example" /></td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_l11_patterns_title')" :subtitle="t('lampiran.n4_l11_patterns_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(p, i) in l11Patterns" :key="`l11p-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  {{ p.pattern }}
                </td>
                <td><RubyText :text="tr(p.form)" /></td>
                <td>
                  <RubyText :text="p.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(p.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_adverb_title')" :subtitle="t('lampiran.n4_adverb_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_type') }}</th>
                <th>{{ t('lampiran.n4_col_base') }}</th>
                <th>{{ t('lampiran.n4_col_adverb') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(a, i) in adverbForms" :key="`adv-${i}`">
                <td>{{ tr(a.type) }}</td>
                <td><RubyText :text="a.base" /></td>
                <td class="text-h6">
                  <RubyText :text="a.adverb" />
                </td>
                <td class="text-medium-emphasis">
                  {{ tr(a.meaning) }}
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_nominal_title')" :subtitle="t('lampiran.n4_nominal_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_adjectives') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(n, i) in nominalPatterns" :key="`nm-${i}`">
                <td class="text-h6 font-weight-medium"><RubyText :text="n.pattern" /></td>
                <td><RubyText :text="n.adjectives" /></td>
                <td>
                  <RubyText :text="n.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(n.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_shiranai_title')" :subtitle="t('lampiran.n4_shiranai_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_answer') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(a, i) in shiranaiAnswers" :key="`sh-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap"><RubyText :text="a.answer" /></td>
                <td>{{ tr(a.use) }}</td>
                <td><RubyText :text="a.example" /></td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_emb_forms')" :subtitle="t('lampiran.n4_emb_forms_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_type') }}</th>
                <th>{{ t('lampiran.n4_col_plain') }}</th>
                <th>{{ t('lampiran.n4_col_with_ka') }}</th>
                <th>{{ t('lampiran.n4_col_note') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(f, i) in embeddedForms" :key="`ef-${i}`">
                <td class="font-weight-medium">
                  {{ tr(f.type) }}
                </td>
                <td>{{ f.plain }}</td>
                <td class="text-h6">
                  {{ f.example }}
                </td>
                <td class="text-medium-emphasis">
                  {{ tr(f.note) }}
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_emb_uses')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(u, i) in embeddedUses" :key="`eu-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  {{ u.pattern }}
                </td>
                <td>{{ tr(u.use) }}</td>
                <td>
                  <RubyText :text="u.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(u.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_l19_patterns_title')" :subtitle="t('lampiran.n4_l19_patterns_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(p, i) in p19Patterns" :key="`p19p-${i}`">
                <td class="text-h6 font-weight-medium">
                  {{ p.pattern }}
                </td>
                <td>{{ tr(p.use) }}</td>
                <td>
                  <RubyText :text="p.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(p.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_l19_forms_title')" :subtitle="t('lampiran.n4_l19_forms_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_type') }}</th>
                <th>{{ t('lampiran.n4_col_base') }}</th>
                <th>{{ t('lampiran.n4_col_result') }}</th>
                <th>{{ t('lampiran.n4_col_note') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(f, i) in p19Forms" :key="`p19f-${i}`">
                <td class="font-weight-medium">
                  {{ tr(f.type) }}
                </td>
                <td><RubyText :text="f.base" /></td>
                <td class="text-h6">
                  <RubyText :text="f.result" />
                </td>
                <td class="text-medium-emphasis">
                  {{ tr(f.note) }}
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_l19_change_title')" :subtitle="t('lampiran.n4_l19_change_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(c, i) in p19Change" :key="`p19c-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  {{ c.pattern }}
                </td>
                <td>{{ tr(c.use) }}</td>
                <td>
                  <RubyText :text="c.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(c.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_baai_title')" :subtitle="t('lampiran.n4_baai_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_type') }}</th>
                <th>{{ t('lampiran.n4_col_how') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(f, i) in baaiForms" :key="`bf-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  <RubyText :text="f.pattern" />
                </td>
                <td>{{ tr(f.type) }}</td>
                <td>{{ tr(f.rule) }}</td>
                <td>
                  <RubyText :text="f.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(f.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_noni_compare_title')" :subtitle="t('lampiran.n4_noni_compare_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(c, i) in noniCompare" :key="`nc-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  {{ c.pattern }}
                </td>
                <td>{{ tr(c.use) }}</td>
                <td>
                  <RubyText :text="c.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(c.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_tokoro_title')" :subtitle="t('lampiran.n4_tokoro_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(u, i) in tokoroPatterns" :key="`tk-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  {{ u.pattern }}
                </td>
                <td>{{ tr(u.use) }}</td>
                <td>
                  <RubyText :text="u.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(u.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_hazu_title')" :subtitle="t('lampiran.n4_hazu_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_type') }}</th>
                <th>{{ t('lampiran.n4_col_connect') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
                <th>{{ t('lampiran.n4_col_note') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(h, i) in hazuForms" :key="`hz-${i}`">
                <td class="font-weight-medium">
                  {{ tr(h.type) }}
                </td>
                <td><RubyText :text="h.connect" /></td>
                <td class="text-h6">
                  <RubyText :text="h.example" />
                </td>
                <td class="text-medium-emphasis">
                  {{ tr(h.note) }}
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
      </VWindowItem>

      <VWindowItem value="n4g-verbs">
        <VCard :title="t('lampiran.n4_potential_forms')" :subtitle="t('lampiran.n4_potential_forms_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_group') }}</th>
                <th>{{ t('lampiran.n4_col_dict') }}</th>
                <th>{{ t('lampiran.n4_col_polite') }}</th>
                <th>{{ t('lampiran.n4_col_potential_plain') }}</th>
                <th>{{ t('lampiran.n4_col_note') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(f, i) in potentialForms" :key="`pf-${i}`">
                <td class="font-weight-medium">
                  {{ f.group }}
                </td>
                <td class="text-h6">
                  <RubyText :text="f.dict" />
                </td>
                <td class="text-h6">
                  <RubyText :text="f.polite" />
                </td>
                <td class="text-h6">
                  <RubyText :text="f.plain" />
                </td>
                <td class="text-medium-emphasis">
                  {{ tr(f.note) }}
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_potential_particles')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_before') }}</th>
                <th>{{ t('lampiran.n4_col_after') }}</th>
                <th>{{ t('lampiran.n4_col_note') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(p, i) in potentialParticles" :key="`pp-${i}`">
                <td><RubyText :text="p.before" /></td>
                <td class="text-h6">
                  <RubyText :text="p.after" />
                </td>
                <td class="text-medium-emphasis">
                  {{ tr(p.note) }}
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_potential_compare')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(u, i) in potentialCompare" :key="`pc-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  <RubyText :text="u.pattern" />
                </td>
                <td>{{ tr(u.use) }}</td>
                <td>
                  <RubyText :text="u.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(u.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_vol_forms')" :subtitle="t('lampiran.n4_vol_forms_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_type') }}</th>
                <th>{{ t('lampiran.n4_col_rule') }}</th>
                <th>{{ t('lampiran.n4_col_masu') }}</th>
                <th>{{ t('lampiran.n4_col_vol') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(g, i) in volitionalGroups" :key="`vg-${i}`">
                <td class="font-weight-medium text-no-wrap">
                  {{ tr(g.type) }}
                </td>
                <td>{{ tr(g.rule) }}</td>
                <td><RubyText :text="g.masu" /></td>
                <td class="text-h6">
                  <RubyText :text="g.example" />
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_vol_sounds')" :subtitle="t('lampiran.n4_vol_sounds_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_dict_end') }}</th>
                <th>{{ t('lampiran.n4_col_vol_end') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(s, i) in volitionalSounds" :key="`vs-${i}`">
                <td class="text-h6">
                  {{ s[0] }}
                </td>
                <td class="text-h6">
                  {{ s[1] }}
                </td>
                <td><RubyText :text="s[2]" /></td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_vol_uses')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(u, i) in volitionalUses" :key="`vu-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  <RubyText :text="u.pattern" />
                </td>
                <td>{{ tr(u.use) }}</td>
                <td>
                  <RubyText :text="u.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(u.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_imp_forms')" :subtitle="t('lampiran.n4_imp_forms_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_group') }}</th>
                <th>{{ t('lampiran.n4_col_dict') }}</th>
                <th>{{ t('lampiran.n4_col_imperative') }}</th>
                <th>{{ t('lampiran.n4_col_prohibitive') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(r, i) in imperativeRows" :key="`im-${i}`">
                <td>
                  <div class="font-weight-medium">
                    {{ tr(r.group) }}
                  </div>
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(r.rule) }}
                  </div>
                </td>
                <td><RubyText :text="r.dict" /></td>
                <td class="text-h6">
                  <RubyText :text="r.imperative" />
                </td>
                <td class="text-h6">
                  <RubyText :text="r.prohibitive" />
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_imp_uses')" :subtitle="t('lampiran.n4_imp_uses_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_who') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(u, i) in imperativeUses" :key="`iu-${i}`">
                <td>{{ tr(u.who) }}</td>
                <td>
                  <RubyText :text="u.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(u.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_te_verbs_title')" :subtitle="t('lampiran.n4_te_verbs_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_dict') }}</th>
                <th>{{ t('lampiran.n4_col_te_imasu') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(v, i) in stateVerbs" :key="`sv-${i}`">
                <td class="text-h6"><RubyText :text="v.dict" /></td>
                <td class="text-h6"><RubyText :text="v.te" /></td>
                <td>{{ tr(v.name) }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_te_compare_title')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
                <th>{{ t('lampiran.n4_col_note') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(c, i) in teImasuCompare" :key="`tc-${i}`">
                <td class="font-weight-medium">{{ tr(c.use) }}</td>
                <td>
                  <RubyText :text="c.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(c.meaning) }}
                  </div>
                </td>
                <td class="text-medium-emphasis">{{ tr(c.note) }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_shimau_title')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(u, i) in shimauUses" :key="`sh-${i}`">
                <td class="font-weight-medium">{{ tr(u.use) }}</td>
                <td class="text-h6 text-no-wrap">{{ u.form }}</td>
                <td>
                  <RubyText :text="u.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(u.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_teaoku_compare')" :subtitle="t('lampiran.n4_teaoku_compare_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(c, i) in teCompare" :key="`tc-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  {{ c.pattern }}
                </td>
                <td>{{ tr(c.use) }}</td>
                <td>
                  <RubyText :text="c.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(c.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_teaoku_pairs')" :subtitle="t('lampiran.n4_teaoku_pairs_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_state') }}</th>
                <th>{{ t('lampiran.n4_col_action_transitive') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(p, i) in tePairs" :key="`tp-${i}`">
                <td class="text-h6"><RubyText :text="p.state" /></td>
                <td class="text-h6"><RubyText :text="p.action" /></td>
                <td>{{ tr(p.meaning) }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_cond_title')" :subtitle="t('lampiran.n4_cond_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_type') }}</th>
                <th>{{ t('lampiran.n4_col_dict_base') }}</th>
                <th>{{ t('lampiran.n4_col_cond') }}</th>
                <th>{{ t('lampiran.n4_col_how') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(f, i) in condForms" :key="`cf-${i}`">
                <td class="font-weight-medium">
                  {{ tr(f.type) }}
                </td>
                <td><RubyText :text="f.dict" /></td>
                <td class="text-h6">
                  <RubyText :text="f.cond" />
                </td>
                <td class="text-medium-emphasis">
                  {{ tr(f.how) }}
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_compare_title')" :subtitle="t('lampiran.n4_compare_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(c, i) in condCompare" :key="`cc-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  {{ c.pattern }}
                </td>
                <td>{{ tr(c.use) }}</td>
                <td>
                  <RubyText :text="c.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(c.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_passive_forms')" :subtitle="t('lampiran.n4_passive_forms_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_group') }}</th>
                <th>{{ t('lampiran.n4_col_dict') }}</th>
                <th>{{ t('lampiran.n4_col_polite') }}</th>
                <th>{{ t('lampiran.n4_col_potential_plain') }}</th>
                <th>{{ t('lampiran.n4_col_note') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(f, i) in passiveForms" :key="`pv-${i}`">
                <td class="font-weight-medium">
                  {{ f.group }}
                </td>
                <td class="text-h6">
                  <RubyText :text="f.dict" />
                </td>
                <td class="text-h6">
                  <RubyText :text="f.polite" />
                </td>
                <td class="text-h6">
                  <RubyText :text="f.plain" />
                </td>
                <td class="text-medium-emphasis">
                  {{ tr(f.note) }}
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_passive_patterns')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_active') }}</th>
                <th>{{ t('lampiran.n4_col_passive') }}</th>
                <th>{{ t('lampiran.n4_col_note') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(p, i) in passivePatterns" :key="`pa-${i}`">
                <td><RubyText :text="p.active" /></td>
                <td class="text-h6">
                  <RubyText :text="p.passive" />
                </td>
                <td class="text-medium-emphasis">
                  {{ tr(p.note) }}
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_caus_forms_title')" :subtitle="t('lampiran.n4_caus_forms_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_type') }}</th>
                <th>{{ t('lampiran.n4_col_masu') }}</th>
                <th>{{ t('lampiran.n4_col_polite_causative') }}</th>
                <th>{{ t('lampiran.n4_col_plain') }}</th>
                <th>{{ t('lampiran.n4_col_te') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(g, i) in causativeGroups" :key="`cg-${i}`">
                <td class="font-weight-medium">{{ tr(g.type) }}</td>
                <td class="text-h6"><RubyText :text="g.masu" /></td>
                <td class="text-h6"><RubyText :text="g.polite" /></td>
                <td class="text-h6"><RubyText :text="g.plain" /></td>
                <td class="text-h6"><RubyText :text="g.te" /></td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_caus_endings_title')" :subtitle="t('lampiran.n4_caus_endings_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_ending') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(e, i) in causativeEndings" :key="`ce-${i}`">
                <td class="text-h6 text-no-wrap">{{ e.ending }}</td>
                <td class="text-h6"><RubyText :text="e.example" /></td>
                <td>{{ tr(e.meaning) }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_caus_patterns_title')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(p, i) in causativePatterns" :key="`cp-${i}`">
                <td class="text-h6 font-weight-medium">{{ p.pattern }}</td>
                <td>{{ tr(p.use) }}</td>
                <td>
                  <RubyText :text="p.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(p.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
      </VWindowItem>

      <VWindowItem value="n4g-expressions">
        <VCard :title="t('lampiran.n4_advice_title')" :subtitle="t('lampiran.n4_advice_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_type') }}</th>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(a, i) in adviceForms" :key="`ad-${i}`">
                <td class="font-weight-medium">
                  {{ tr(a.type) }}
                </td>
                <td class="text-h6 text-no-wrap">
                  {{ a.form }}
                </td>
                <td>
                  <RubyText :text="a.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(a.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_guess_title')" :subtitle="t('lampiran.n4_guess_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_type') }}</th>
                <th>〜でしょう</th>
                <th>〜かもしれません</th>
                <th>{{ t('lampiran.n4_col_note') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(g, i) in guessForms" :key="`gf-${i}`">
                <td class="font-weight-medium">
                  {{ tr(g.type) }}
                </td>
                <td class="text-h6">
                  <RubyText :text="g.deshou" />
                </td>
                <td class="text-h6">
                  <RubyText :text="g.kamo" />
                </td>
                <td class="text-medium-emphasis">
                  {{ tr(g.note) }}
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_certainty_title')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(c, i) in certaintyLevels" :key="`ce-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  {{ c.pattern }}
                </td>
                <td>{{ tr(c.use) }}</td>
                <td>
                  <RubyText :text="c.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(c.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_sou_forms')" :subtitle="t('lampiran.n4_sou_forms_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_type') }}</th>
                <th>{{ t('lampiran.n4_col_howto') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(f, i) in souForms" :key="`so-${i}`">
                <td class="font-weight-medium">
                  {{ tr(f.type) }}
                </td>
                <td>{{ tr(f.rule) }}</td>
                <td>
                  <div class="text-h6">
                    <RubyText :text="f.example" />
                  </div>
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(f.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VAlert type="info" variant="tonal">
          <ul class="ps-4 mb-0">
            <li v-for="(n, i) in souNotes" :key="`sn-${i}`">
              <RubyText :text="tr(n)" />
            </li>
          </ul>
        </VAlert>
        <VCard :title="t('lampiran.n4_souyou_title')" :subtitle="t('lampiran.n4_souyou_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
                <th>{{ t('lampiran.n4_col_form') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(s, i) in souyouPatterns" :key="`sy-${i}`">
                <td class="text-h6 font-weight-medium"><RubyText :text="s.pattern" /></td>
                <td>{{ tr(s.meaning) }}</td>
                <td>{{ tr(s.form) }}</td>
                <td>
                  <RubyText :text="s.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(s.exMeaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_reason_title')" :subtitle="t('lampiran.n4_reason_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_form_make') }}</th>
                <th>{{ t('lampiran.n4_col_back') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(r, i) in reasonCompare" :key="`rc-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">{{ r.pattern }}</td>
                <td>{{ tr(r.form) }}</td>
                <td class="text-medium-emphasis">{{ tr(r.back) }}</td>
                <td>
                  <RubyText :text="r.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(r.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_node_title')" :subtitle="t('lampiran.n4_node_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_type') }}</th>
                <th>{{ t('lampiran.n4_col_plain') }}</th>
                <th>{{ t('lampiran.n4_col_with_node') }}</th>
                <th>{{ t('lampiran.n4_col_note') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(f, i) in nodeForms" :key="`nf-${i}`">
                <td class="font-weight-medium">{{ tr(f.type) }}</td>
                <td>{{ f.plain }}</td>
                <td class="text-h6">{{ f.example }}</td>
                <td class="text-medium-emphasis">{{ tr(f.note) }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_tochuu_title')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(r, i) in tochuuRows" :key="`tc-${i}`">
                <td class="text-h6 text-no-wrap"><RubyText :text="r.form" /></td>
                <td>
                  <RubyText :text="r.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(r.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_feelings_title')" :subtitle="t('lampiran.n4_feelings_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_expression') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(w, i) in feelingWords" :key="`fw-${i}`">
                <td class="text-h6 text-no-wrap"><RubyText :text="w.ja" /></td>
                <td class="font-weight-medium">{{ tr(w.name) }}</td>
                <td>
                  <RubyText :text="w.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(w.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_giving_title')" :subtitle="t('lampiran.n4_giving_subtitle')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_situation') }}</th>
                <th>{{ t('lampiran.n4_col_word') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(g, i) in givingRows" :key="`gv-${i}`">
                <td>{{ tr(g.situation) }}</td>
                <td class="text-h6 font-weight-medium text-no-wrap">
                  {{ g.word }}
                </td>
                <td>
                  <RubyText :text="g.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(g.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_giving_te')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(f, i) in givingTeForms" :key="`gt-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  {{ f.pattern }}
                </td>
                <td>{{ tr(f.use) }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_purpose_title')" :subtitle="t('lampiran.n4_purpose_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(c, i) in purposeCompare" :key="`pc-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  <RubyText :text="c.pattern" />
                </td>
                <td>{{ tr(c.use) }}</td>
                <td>
                  <RubyText :text="c.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(c.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_noni_title')" :subtitle="t('lampiran.n4_noni_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(u, i) in useVerbs" :key="`uv-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  <RubyText :text="u.pattern" />
                </td>
                <td>{{ tr(u.use) }}</td>
                <td>
                  <RubyText :text="u.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(u.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_amount_title')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(a, i) in amountParticles" :key="`am-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  {{ a.pattern }}
                </td>
                <td>{{ tr(a.use) }}</td>
                <td>
                  <RubyText :text="a.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(a.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_condition_title')" :subtitle="t('lampiran.n4_condition_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_expression') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(w, i) in conditionWords" :key="`cw-${i}`">
                <td class="text-h6 text-no-wrap"><RubyText :text="w.ja" /></td>
                <td class="font-weight-medium">{{ tr(w.name) }}</td>
                <td>
                  <RubyText :text="w.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(w.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_char_title')" :subtitle="t('lampiran.n4_char_subtitle')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_trait') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
                <th>{{ t('lampiran.n4_col_opposite') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(c, i) in characterRows" :key="`ch-${i}`">
                <td class="text-h6 font-weight-medium">
                  <RubyText :text="c.ja" />
                </td>
                <td>{{ tr(c.meaning) }}</td>
                <td class="text-h6 font-weight-medium">
                  <RubyText v-if="c.pair" :text="c.ja2" />
                </td>
                <td>{{ c.pair ? tr(c.meaning2) : '' }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
      </VWindowItem>

      <VWindowItem value="n4g-keigo">
        <VCard :title="t('lampiran.n4_keigo_special_title')" :subtitle="t('lampiran.n4_keigo_special_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_plain_verb') }}</th>
                <th>{{ t('lampiran.n4_col_honorific') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(h, i) in honorificSpecial" :key="`hs-${i}`">
                <td><RubyText :text="h.plain" /></td>
                <td class="text-h6 font-weight-medium">
                  <RubyText :text="h.honorific" />
                </td>
                <td>{{ tr(h.meaning) }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_keigo_forms_title')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_how_formed') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(f, i) in honorificForms" :key="`hf-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  {{ f.pattern }}
                </td>
                <td><RubyText :text="tr(f.how)" /></td>
                <td>
                  <RubyText :text="f.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(f.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_keigo_prefix_title')" :subtitle="t('lampiran.n4_keigo_prefix_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_type') }}</th>
                <th>お 〜</th>
                <th>ご 〜</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(p, i) in honorificPrefix" :key="`hp-${i}`">
                <td class="font-weight-medium">
                  {{ tr(p.kind) }}
                </td>
                <td><RubyText :text="p.o" /></td>
                <td><RubyText :text="p.go" /></td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_keigo_conj_title')" :subtitle="t('lampiran.n4_keigo_conj_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_dict') }}</th>
                <th>{{ t('lampiran.n4_col_conj') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(c, i) in irregularConj" :key="`ic-${i}`">
                <td class="text-h6 font-weight-medium">
                  {{ c.dict }}
                </td>
                <td>{{ c.forms }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_keigo_patterns')" :subtitle="t('lampiran.n4_keigo_patterns_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_pattern') }}</th>
                <th>{{ t('lampiran.n4_col_use') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(k, i) in keigoPatterns" :key="`kp-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  <RubyText :text="k.pattern" />
                </td>
                <td>{{ tr(k.use) }}</td>
                <td>
                  <RubyText :text="k.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(k.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_keigo_verbs')" :subtitle="t('lampiran.n4_keigo_verbs_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_plainverb') }}</th>
                <th>{{ t('lampiran.n4_col_humble') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(h, i) in humbleVerbs" :key="`hv-${i}`">
                <td>{{ h.plain }}</td>
                <td class="text-h6 font-weight-medium">
                  <RubyText :text="h.humble" />
                </td>
                <td>{{ tr(h.meaning) }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_addr_title')" :subtitle="t('lampiran.n4_addr_subtitle')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_part') }}</th>
                <th>{{ t('lampiran.n4_col_howwrite') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(a, i) in addressRows" :key="`ad-${i}`">
                <td class="font-weight-medium">
                  {{ tr(a.part) }}
                </td>
                <td><RubyText :text="tr(a.how)" /></td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
      </VWindowItem>

      <VWindowItem value="n4g-daily">
        <VCard :title="t('lampiran.n4_trash_title')" :subtitle="t('lampiran.n4_trash_subtitle')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_type') }}</th>
                <th>{{ t('lampiran.n4_col_items') }}</th>
                <th>{{ t('lampiran.n4_col_day') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(g, i) in garbageTypes" :key="`gb-${i}`">
                <td>
                  <div class="text-h6 font-weight-medium">
                    <RubyText :text="g.ja" />
                  </div>
                  <div v-if="g.alt" class="text-medium-emphasis text-body-2">
                    <RubyText :text="g.alt" />
                  </div>
                  <div class="text-body-2">
                    {{ tr(g.name) }}
                  </div>
                </td>
                <td><RubyText :text="tr(g.items)" /></td>
                <td><RubyText :text="tr(g.day)" /></td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VAlert type="info" variant="tonal">
          <ul class="ps-4 mb-0">
            <li v-for="(tip, i) in garbageTips" :key="`tip-${i}`">
              <RubyText :text="tr(tip)" />
            </li>
          </ul>
        </VAlert>
        <VCard :title="t('lampiran.n4_shops_title')" :subtitle="t('lampiran.n4_shops_subtitle')" class="mb-4">
          <div v-for="(g, gi) in shopGroups" :key="`sg-${gi}`" class="px-4 pb-4">
            <div class="text-h6 font-weight-medium">
              <RubyText :text="g.ja" />
            </div>
            <div class="text-body-2 text-medium-emphasis mb-2">
              {{ tr(g.name) }}
            </div>
            <VTable density="compact" style="overflow-x: auto;">
              <tbody>
                <tr v-for="(it, ii) in g.items" :key="`si-${gi}-${ii}`">
                  <td class="text-h6">
                    <RubyText :text="it.ja" />
                  </td>
                  <td>{{ tr(it.meaning) }}</td>
                </tr>
              </tbody>
            </VTable>
          </div>
        </VCard>
        <VCard :title="t('lampiran.n4_rental_title')" :subtitle="t('lampiran.n4_rental_subtitle')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_term') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
                <th>{{ t('lampiran.n4_col_sample') }}</th>
                <th>{{ t('lampiran.n4_col_note') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(r, i) in rentalTerms" :key="`rt-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  <RubyText :text="r.ja" />
                </td>
                <td>{{ tr(r.name) }}</td>
                <td class="text-no-wrap">
                  <RubyText :text="r.sample" />
                </td>
                <td><RubyText :text="tr(r.note)" /></td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VAlert type="info" variant="tonal">
          <ul class="ps-4 mb-0">
            <li v-for="(tip, i) in rentalTips" :key="`rtip-${i}`">
              <RubyText :text="tr(tip)" />
            </li>
          </ul>
        </VAlert>
        <VCard
          v-for="(g, gi) in emergencyGroups"
          :key="`eg-${gi}`"
          :title="tr(g.title)"
          class="mb-4"
        >
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_step') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(st, si) in g.steps" :key="`es-${gi}-${si}`">
                <td class="text-h6"><RubyText :text="st.ja" /></td>
                <td>{{ tr(st) }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_bag_title')" :subtitle="t('lampiran.n4_bag_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_item') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(b, bi) in emergencyBag" :key="`eb-${bi}`">
                <td class="text-h6"><RubyText :text="b.ja" /></td>
                <td>{{ tr(b) }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VAlert type="info" variant="tonal">
          {{ t('lampiran.n4_emergency_note') }}
        </VAlert>
        <VRow class="mb-2">
          <VCol
            v-for="g in cookingGroups"
            :key="`cg-${g.key}`"
            cols="12"
            :md="g.wide ? 12 : 6"
          >
            <VCard :title="t(g.title)" class="h-100">
              <VTable density="comfortable" style="overflow-x: auto;">
                <thead>
                  <tr>
                    <th>{{ t('lampiran.n4_col_word') }}</th>
                    <th>{{ t('lampiran.n4_col_meaning') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(it, i) in g.items" :key="`cgi-${g.key}-${i}`">
                    <td class="text-h6 font-weight-medium text-no-wrap">
                      <RubyText :text="it.ja" />
                    </td>
                    <td>{{ tr(it.meaning) }}</td>
                  </tr>
                </tbody>
              </VTable>
            </VCard>
          </VCol>
        </VRow>

        <VAlert type="info" variant="tonal">
          <ul class="ps-4 mb-0">
            <li v-for="(tip, i) in cookingTips" :key="`ct-${i}`">
              <RubyText :text="tr(tip)" />
            </li>
          </ul>
        </VAlert>
        <VRow>
          <VCol
            v-for="g in salonGroups"
            :key="`sg-${g.key}`"
            cols="12"
            :md="g.wide ? 12 : 6"
          >
            <VCard :title="t(g.title)" :subtitle="g.hint ? t(g.hint) : undefined" class="h-100">
              <VTable density="comfortable" style="overflow-x: auto;">
                <thead>
                  <tr>
                    <th>{{ t('lampiran.n4_col_word') }}</th>
                    <th>{{ t('lampiran.n4_col_meaning') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(it, i) in g.items" :key="`sgi-${g.key}-${i}`">
                    <td class="text-h6 font-weight-medium">
                      <RubyText :text="it.ja" />
                    </td>
                    <td>{{ tr(it.meaning) }}</td>
                  </tr>
                </tbody>
              </VTable>
            </VCard>
          </VCol>
        </VRow>
        <VCard v-for="(g, gi) in hospitalGroups" :key="`hg-${gi}`" :title="tr(g.title)" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <tbody>
              <tr v-for="(row, ri) in g.rows" :key="`hr-${gi}-${ri}`">
                <td class="text-h6 font-weight-medium">
                  <RubyText :text="row.ja" />
                </td>
                <td class="text-medium-emphasis">
                  {{ tr(row.m) }}
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VRow class="mb-1">
          <VCol cols="12" md="6">
            <VCard :title="t('lampiran.n4_health_good')" class="h-100">
              <VList density="comfortable">
                <VListItem v-for="(h, i) in goodHabits" :key="`gh-${i}`">
                  <VListItemTitle class="text-wrap">
                    <RubyText :text="h.ja" />
                  </VListItemTitle>
                  <VListItemSubtitle class="text-wrap">
                    {{ tr(h.meaning) }}
                  </VListItemSubtitle>
                </VListItem>
              </VList>
            </VCard>
          </VCol>
          <VCol cols="12" md="6">
            <VCard :title="t('lampiran.n4_health_bad')" class="h-100">
              <VList density="comfortable">
                <VListItem v-for="(h, i) in badHabits" :key="`bh-${i}`">
                  <VListItemTitle class="text-wrap">
                    <RubyText :text="h.ja" />
                  </VListItemTitle>
                  <VListItemSubtitle class="text-wrap">
                    {{ tr(h.meaning) }}
                  </VListItemSubtitle>
                </VListItem>
              </VList>
            </VCard>
          </VCol>
        </VRow>

        <VCard :title="t('lampiran.n4_nutrients_title')" :subtitle="t('lampiran.n4_nutrients_subtitle')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_nutrient') }}</th>
                <th>{{ t('lampiran.n4_col_foods') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(n, i) in nutrients" :key="`nt-${i}`">
                <td>
                  <div class="text-h6 font-weight-medium">
                    <RubyText :text="n.ja" />
                  </div>
                  <div class="text-body-2">
                    {{ tr(n.name) }}
                  </div>
                </td>
                <td><RubyText :text="tr(n.foods)" /></td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_weather_title')" :subtitle="t('lampiran.n4_weather_hint')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_symbol') }}</th>
                <th>{{ t('lampiran.n4_col_expression') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(w, i) in weatherSymbols" :key="`ws-${i}`">
                <td class="text-h5">
                  {{ w.icon }}
                </td>
                <td class="text-h6">
                  <RubyText :text="w.ja" />
                </td>
                <td>{{ tr(w.meaning) }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_weather_terms')" class="mb-4">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_expression') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(w, i) in weatherTerms" :key="`wt-${i}`">
                <td class="text-h6">
                  <RubyText :text="w.ja" />
                </td>
                <td>{{ tr(w.meaning) }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard :title="t('lampiran.n4_regions_title')" :subtitle="t('lampiran.n4_regions_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_region') }}</th>
                <th>{{ t('lampiran.n4_col_city') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(r, i) in regions" :key="`rg-${i}`">
                <td>
                  <div class="text-h6">
                    <RubyText :text="r.ja" />
                  </div>
                  <div class="text-body-2 text-medium-emphasis">
                    {{ tr(r.name) }}
                  </div>
                </td>
                <td class="text-h6">
                  <RubyText :text="r.city" />
                </td>
              </tr>
            </tbody>
          </VTable>
          <VCardText class="d-flex flex-wrap ga-2">
            <VChip v-for="(d, i) in directions" :key="`dr-${i}`" variant="tonal" size="large">
              <RubyText :text="d.ja" />&nbsp;· {{ tr(d.meaning) }}
            </VChip>
          </VCardText>
        </VCard>
        <VCard :title="t('lampiran.n4_events_title')" :subtitle="t('lampiran.n4_events_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_event') }}</th>
                <th>{{ t('lampiran.n4_col_date') }}</th>
                <th>{{ t('lampiran.n4_col_desc') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(e, i) in seasonalEvents" :key="`ev-${i}`">
                <td>
                  <div class="text-h6 font-weight-medium">
                    {{ e.icon }} <RubyText :text="e.ja" />
                  </div>
                  <div class="text-body-2 text-medium-emphasis">
                    {{ tr(e.name) }}
                  </div>
                </td>
                <td class="text-no-wrap">
                  {{ tr(e.date) }}
                </td>
                <td>{{ tr(e.desc) }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
      </VWindowItem>

      <VWindowItem value="n4g-vocab">
        <VCard
          v-for="(group, gi) in fieldGroups"
          :key="`fg-${gi}`"
          :title="gi === 0 ? t('lampiran.n4_fields_title') : undefined"
          :subtitle="gi === 0 ? t('lampiran.n4_fields_subtitle') : undefined"
          class="mb-4"
        >
          <VCardText class="pb-0 text-subtitle-1 font-weight-medium">
            {{ tr(group.title) }}
          </VCardText>
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_field') }}</th>
                <th>{{ t('lampiran.n4_col_reading') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(f, i) in group.items" :key="`fi-${gi}-${i}`">
                <td class="text-h6 font-weight-medium">
                  {{ f[0] }}
                </td>
                <td>{{ f[1] }}</td>
                <td>{{ tr(f[2]) }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VAlert type="info" variant="tonal">
          <ul class="ps-4 mb-0">
            <li v-for="(tip, i) in fieldTips" :key="`ft-${i}`">
              <RubyText :text="tr(tip)" />
            </li>
          </ul>
        </VAlert>
        <VCard
          v-for="(g, gi) in signGroups"
          :key="`sg-${gi}`"
          :title="tr(g.title)"
          :subtitle="gi === 0 ? t('lampiran.n4_signs_subtitle') : undefined"
          class="mb-4"
        >
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_sign') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(r, i) in g.rows" :key="`sr-${gi}-${i}`">
                <td class="text-h6 font-weight-medium">
                  <RubyText :text="r.ja" />
                </td>
                <td>{{ tr(r.meaning) }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_incidents_title')" :subtitle="t('lampiran.n4_incidents_subtitle')" class="mb-4">
          <div v-for="(g, gi) in incidentGroups" :key="`ig-${gi}`" class="px-4 pb-4">
            <div class="text-h6 font-weight-medium">
              <RubyText :text="g.ja" />
            </div>
            <div class="text-body-2 text-medium-emphasis mb-2">
              {{ tr(g.name) }}
            </div>
            <VTable density="compact" style="overflow-x: auto;">
              <tbody>
                <tr v-for="(it, ii) in g.items" :key="`ii-${gi}-${ii}`">
                  <td class="text-h6">
                    <RubyText :text="it.ja" />
                  </td>
                  <td>{{ tr(it.meaning) }}</td>
                </tr>
              </tbody>
            </VTable>
          </div>
        </VCard>
        <VCard :title="t('lampiran.n4_position_title')" :subtitle="t('lampiran.n4_position_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_expression') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(p, i) in positionWords" :key="`ps-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap"><RubyText :text="p.ja" /></td>
                <td>{{ tr(p.meaning) }}</td>
                <td>
                  <RubyText :text="p.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(p.exMeaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard
          v-for="(group, gi) in measureGroups"
          :key="`mg-${gi}`"
          :title="gi === 0 ? t('lampiran.n4_measure_title') : undefined"
          :subtitle="gi === 0 ? t('lampiran.n4_measure_subtitle') : undefined"
          class="mb-4"
        >
          <VCardText class="pb-0 text-subtitle-1 font-weight-medium">
            {{ tr(group.title) }}
          </VCardText>
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_symbol') }}</th>
                <th>{{ t('lampiran.n4_col_word') }}</th>
                <th>{{ t('lampiran.n4_col_reading') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(m, i) in group.items" :key="`mi-${gi}-${i}`">
                <td class="text-medium-emphasis">
                  {{ m[3] || '—' }}
                </td>
                <td class="text-h6 font-weight-medium">
                  {{ m[0] }}
                </td>
                <td>{{ m[1] }}</td>
                <td>{{ tr(m[2]) }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VAlert type="info" variant="tonal">
          <ul class="ps-4 mb-0">
            <li v-for="(tip, i) in measureTips" :key="`mt-${i}`">
              {{ tr(tip) }}
            </li>
          </ul>
        </VAlert>
        <p class="text-body-2 text-medium-emphasis mb-4">
          {{ t('lampiran.n4_useful_hint') }}
        </p>
        <VRow>
          <VCol
            v-for="(g, i) in usefulGroups"
            :key="`us-${i}`"
            cols="12"
            md="6"
          >
            <VCard class="h-100">
              <VCardTitle>
                <RubyText :text="g.title.ja" />
                <span class="text-body-2 text-medium-emphasis ms-2">{{ tr(g.title) }}</span>
              </VCardTitle>
              <VList density="compact">
                <VListItem v-for="(term, j) in g.terms" :key="`us-${i}-${j}`">
                  <VListItemTitle><RubyText :text="term.ja" /></VListItemTitle>
                  <VListItemSubtitle>{{ tr(term) }}</VListItemSubtitle>
                </VListItem>
              </VList>
            </VCard>
          </VCol>
        </VRow>
        <VCard :title="t('lampiran.n4_tools_title')" :subtitle="t('lampiran.n4_tools_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_tool') }}</th>
                <th>{{ t('lampiran.n4_col_action') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(x, i) in toolItems" :key="`tl-${i}`">
                <td>
                  <div class="text-h6 font-weight-medium">
                    <RubyText :text="x.ja" />
                  </div>
                  <div class="text-body-2 text-medium-emphasis">
                    {{ tr(x.name) }}
                  </div>
                </td>
                <td class="text-h6">
                  <RubyText :text="x.action" />
                </td>
                <td>{{ tr(x.doing) }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_katakana_title')" :subtitle="t('lampiran.n4_katakana_subtitle')" class="mb-4">
          <div v-for="(g, gi) in katakanaOrigins" :key="`ko-${gi}`" class="px-4 pb-4">
            <div class="text-h6 font-weight-medium mb-2">
              <RubyText :text="tr(g.lang)" />
            </div>
            <VTable density="compact" style="overflow-x: auto;">
              <tbody>
                <tr v-for="(it, ii) in g.items" :key="`kw-${gi}-${ii}`">
                  <td class="text-h6">
                    {{ it.ja }}
                  </td>
                  <td>{{ tr(it.meaning) }}</td>
                </tr>
              </tbody>
            </VTable>
          </div>
        </VCard>

        <VAlert type="info" variant="tonal">
          {{ t('lampiran.n4_katakana_note') }}
        </VAlert>
        <VCard :title="t('lampiran.n4_onomatope_title')" :subtitle="t('lampiran.n4_onomatope_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_expression') }}</th>
                <th>{{ t('lampiran.n4_col_used_with') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
                <th>{{ t('lampiran.n4_col_example') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(o, i) in onomatope" :key="`on-${i}`">
                <td class="text-h6 font-weight-medium text-no-wrap">
                  {{ o.word }}
                </td>
                <td class="text-no-wrap"><RubyText :text="o.verb" /></td>
                <td>{{ tr(o.meaning) }}</td>
                <td>
                  <RubyText :text="o.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(o.exMeaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_proverbs_title')" :subtitle="t('lampiran.n4_proverbs_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_proverb') }}</th>
                <th>{{ t('lampiran.n4_col_literal') }}</th>
                <th>{{ t('lampiran.n4_col_meaning_proverb') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(p, i) in proverbs" :key="`pv-${i}`">
                <td class="text-h6 font-weight-medium">
                  <RubyText :text="p.ja" />
                </td>
                <td>{{ tr(p.literal) }}</td>
                <td>{{ tr(p.meaning) }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
        <VCard :title="t('lampiran.n4_discipline_title')" :subtitle="t('lampiran.n4_discipline_hint')">
          <VTable density="comfortable" style="overflow-x: auto;">
            <thead>
              <tr>
                <th>{{ t('lampiran.n4_col_expression') }}</th>
                <th>{{ t('lampiran.n4_col_meaning') }}</th>
                <th>{{ t('lampiran.n4_col_causative') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(w, i) in disciplineWords" :key="`dw-${i}`">
                <td class="text-h6"><RubyText :text="w.ja" /></td>
                <td class="font-weight-medium">{{ tr(w.name) }}</td>
                <td>
                  <RubyText :text="w.example" />
                  <div class="text-medium-emphasis text-body-2">
                    {{ tr(w.meaning) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
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
  border: 0;
  border-radius: 9px;
  background: transparent;
  color: rgba(var(--v-theme-on-surface), 0.72);
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  padding-block: 8px;
  padding-inline: 16px;
  transition: background 0.15s ease, color 0.15s ease;
  white-space: nowrap;
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
