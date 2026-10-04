// Penilaian ucapan kaiwa: bandingkan hasil pengenalan suara dengan kalimat target
// secara LONGGAR. Pengenalan suara sering mengubah kana jadi kanji, menambah
// tanda baca, atau salah satu-dua huruf pada lafal pelajar, jadi yang dinilai
// adalah KEMIRIPAN teks (0..1), bukan kecocokan persis.

export const PASS_SCORE = 0.65

const PUNCT = /[\s、。，．,.!?！？「」『』・~〜ー\-]/g

export function stripRuby(s) {
  return String(s ?? '').replace(/《[^》]*》/g, '')
}

// Pengenal suara sering menuliskan kata kana sebagai kanji (初めまして, 私は, お願いします).
// Semua kanji → kana disamakan di KEDUA sisi (ucapan & target) sebelum dibandingkan.
// Daftar dasar di bawah + otomatis dari penanda furigana 漢字《かんじ》 di materi pelajaran
// (lihat registerReadings, dipanggil halaman saat pelajaran dimuat).
const KANJI_KANA = new Map([
  ['初め', 'はじめ'],
  ['私', 'わたし'],
  ['宜しく', 'よろしく'],
  ['お願い', 'おねがい'],
  ['願い', 'ねがい'],
  ['半', 'はん'],
])
let kanjiOrder = null

// ── Angka + pencacah ─────────────────────────────────────────────────────────
// Pengenal suara menulis angka sebagai digit/kanji ("私も25歳です", "二十五歳", "9時半"),
// padahal target ditulis kana ("にじゅうごさい"). Angka + 歳/時/分/円 diubah ke bacaan kana
// di KEDUA sisi sebelum dibandingkan. Cakupan: 0–9999 (cukup untuk umur, jam, menit, harga).
const KANJI_DIGIT = { 〇: 0, 零: 0, 一: 1, 二: 2, 三: 3, 四: 4, 五: 5, 六: 6, 七: 7, 八: 8, 九: 9 }
const DIGIT_KANA = ['', 'いち', 'に', 'さん', 'よん', 'ご', 'ろく', 'なな', 'はち', 'きゅう']

function kanjiNumToInt(str) {
  let total = 0
  let cur = 0
  const units = { 十: 10, 百: 100, 千: 1000 }

  for (const ch of str) {
    if (ch in units) {
      total += (cur || 1) * units[ch]
      cur = 0
    }
    else if (ch in KANJI_DIGIT) {
      cur = cur * 10 + KANJI_DIGIT[ch]
    }
  }

  return total + cur
}

function intToKana(n) {
  if (n === 0)
    return ''
  let out = ''
  const th = Math.floor(n / 1000)
  const h = Math.floor((n % 1000) / 100)
  const t = Math.floor((n % 100) / 10)
  const o = n % 10

  if (th)
    out += th === 1 ? 'せん' : th === 3 ? 'さんぜん' : th === 8 ? 'はっせん' : `${DIGIT_KANA[th]}せん`
  if (h)
    out += h === 1 ? 'ひゃく' : h === 3 ? 'さんびゃく' : h === 6 ? 'ろっぴゃく' : h === 8 ? 'はっぴゃく' : `${DIGIT_KANA[h]}ひゃく`
  if (t)
    out += t === 1 ? 'じゅう' : `${DIGIT_KANA[t]}じゅう`
  if (o)
    out += DIGIT_KANA[o]

  return out
}

function counterReading(n, counter) {
  if (counter === '歳' || counter === '才') {
    if (n === 20)
      return 'はたち'

    return `${intToKana(n).replace(/いち$/, 'いっ').replace(/はち$/, 'はっ').replace(/じゅう$/, 'じゅっ')}さい`
  }
  if (counter === '時') {
    const last = n % 10
    const head = intToKana(n - last)
    const tail = { 4: 'よ', 7: 'しち', 9: 'く' }[last] ?? DIGIT_KANA[last]

    return `${head}${tail}じ`
  }
  if (counter === '分') {
    const last = n % 10
    const head = intToKana(n - last)
    const tail = { 0: '', 1: 'いっぷん', 2: 'にふん', 3: 'さんぷん', 4: 'よんぷん', 5: 'ごふん', 6: 'ろっぷん', 7: 'ななふん', 8: 'はっぷん', 9: 'きゅうふん' }[last]

    return last === 0 ? `${head.replace(/じゅう$/, 'じゅっ')}ぷん` : `${head}${tail}`
  }
  if (counter === '人') {
    if (n === 1)
      return 'ひとり'
    if (n === 2)
      return 'ふたり'

    return `${intToKana(n - (n % 10))}${n % 10 === 4 ? 'よ' : DIGIT_KANA[n % 10]}にん`
  }
  if (counter === '枚' || counter === '台') {
    const suffix = counter === '枚' ? 'まい' : 'だい'
    const last = n % 10

    return `${intToKana(n - last)}${last === 3 ? 'さん' : last === 4 ? 'よん' : DIGIT_KANA[last]}${suffix}`
  }
  if (counter === '回' || counter === '週間' || counter === 'か月') {
    const suffix = { 回: 'かい', 週間: 'しゅうかん', か月: 'かげつ' }[counter]
    const last = n % 10
    const geminate = [1, 6, 8].includes(last) || (n >= 10 && last === 0)

    if (n === 10)
      return `じゅっ${suffix}`
    const head = intToKana(n - last)
    const tail = last === 0 ? '' : geminate ? `${DIGIT_KANA[last].replace(/(いち|ろく|はち)$/, m => ({ いち: 'いっ', ろく: 'ろっ', はち: 'はっ' }[m]))}` : DIGIT_KANA[last]

    return `${head}${tail}${suffix}`
  }
  if (counter === '時間') {
    const last = n % 10

    return `${intToKana(n - last)}${last === 4 ? 'よ' : last === 7 ? 'しち' : last === 9 ? 'く' : DIGIT_KANA[last]}じかん`
  }
  if (counter === 'つ') {
    return n >= 1 && n <= 9 ? ['', 'ひとつ', 'ふたつ', 'みっつ', 'よっつ', 'いつつ', 'むっつ', 'ななつ', 'やっつ', 'ここのつ'][n] : ''
  }
  if (counter === '階') {
    const last = n % 10
    const head = intToKana(n - last)
    const tail = { 0: '', 1: 'いっかい', 2: 'にかい', 3: 'さんがい', 4: 'よんかい', 5: 'ごかい', 6: 'ろっかい', 7: 'ななかい', 8: 'はっかい', 9: 'きゅうかい' }[last]

    return last === 0 ? `${head.replace(/じゅう$/, 'じゅっ')}かい` : `${head}${tail}`
  }
  if (counter === '円') {
    const last = n % 10

    return `${intToKana(n - last)}${last === 4 ? 'よ' : DIGIT_KANA[last]}えん`
  }

  return intToKana(n)
}

const NUM_COUNTER = /(\d{1,4}|[〇零一二三四五六七八九十百千]+)(歳|才|時間|時|分|円|階|人|枚|台|回|週間|か月|ヶ月|ヵ月|つ|さい|じ|ふん|ぷん|えん|かい|がい)/g
const KANA_COUNTER = { さい: '歳', じ: '時', ふん: '分', ぷん: '分', えん: '円', かい: '階', がい: '階', ヶ月: 'か月', ヵ月: 'か月' }

export function numbersToKana(s) {
  return s.replace(NUM_COUNTER, (_, num, counter) => {
    const n = /^\d+$/.test(num) ? Number.parseInt(num, 10) : kanjiNumToInt(num)

    return n < 1 || n > 9999 ? _ : (counterReading(n, KANA_COUNTER[counter] ?? counter) || _)
  })
}

/** Daftarkan pasangan kanji→bacaan dari teks bertanda furigana, mis. 起《お》きます. */
export function registerReadings(text) {
  for (const m of String(text ?? '').matchAll(/([一-龥々]+)《([^》]+)》/g)) {
    // Angka murni (一《いっ》か月, 二《ふた》つ) ditangani numbersToKana bersama pencacahnya. Bila didaftarkan,
    // 一 → いっ akan merusak kata lain yang memuat 一 (一番, 一緒) dan saling menimpa antar pelajaran.
    if (/^[〇零一二三四五六七八九十百千]+$/.test(m[1]))
      continue
    KANJI_KANA.set(m[1], m[2])
  }
  kanjiOrder = null
}

function kanjiToKana(s) {
  kanjiOrder ??= [...KANJI_KANA.keys()].sort((a, b) => b.length - a.length)
  let out = s

  for (const k of kanjiOrder)
    out = out.split(k).join(KANJI_KANA.get(k))

  return out
}

// Nama tokoh user (ブディ) sering salah dengar: "ブディです" → "無理です" (むりです), ぶり, dst.
// Dinormalkan ke bentuk nama di kedua sisi. Tambah di sini bila ada salah dengar baru
// (lihat teks "Terdengar:" di layar). Jangan memasukkan 'ぶで' (awalan dari ぶでぃ itu sendiri).
const NAME = 'ぶでぃ'
const NAME_ALIASES = /無理|むり|ぶり|ぶでい|ぶじ|ぶい/g

export function normalize(s) {
  return kanjiToKana(numbersToKana(stripRuby(s)
    .normalize('NFKC')))
    // katakana -> hiragana, supaya ブディ == ぶでぃ
    .replace(/[ァ-ヶ]/g, ch => String.fromCharCode(ch.charCodeAt(0) - 0x60))
    .replace(PUNCT, '')
    .replace(NAME_ALIASES, NAME)
    .toLowerCase()
}

function levenshtein(a, b) {
  const m = a.length
  const n = b.length

  if (!m)
    return n
  if (!n)
    return m
  let prev = Array.from({ length: n + 1 }, (_, j) => j)

  for (let i = 1; i <= m; i++) {
    const cur = [i]

    for (let j = 1; j <= n; j++)
      cur[j] = Math.min(prev[j] + 1, cur[j - 1] + 1, prev[j - 1] + (a[i - 1] === b[j - 1] ? 0 : 1))
    prev = cur
  }

  return prev[n]
}

export function similarity(a, b) {
  const x = normalize(a)
  const y = normalize(b)

  if (!x && !y)
    return 1
  if (!x || !y)
    return 0

  return 1 - levenshtein(x, y) / Math.max(x.length, y.length)
}

/** Kalimat yang dianggap benar untuk satu giliran user. */
export function targetsOf(turn) {
  return [...new Set([turn.speak, stripRuby(turn.ja), ...(turn.accept ?? [])].filter(Boolean))]
}

/** Skor terbaik dari semua alternatif hasil pengenalan × semua target yang diterima. */
export function scoreSpeech(heard, turn) {
  const targets = targetsOf(turn)
  let best = 0

  for (const h of heard) {
    for (const t of targets)
      best = Math.max(best, similarity(h, t))
  }

  return best
}
