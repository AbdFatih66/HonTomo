// Mengubah pasangan (japanese, hiragana) dari data kosakata menjadi teks berpenanda
// furigana 漢字《かんじ》 yang dirender RubyText. Hanya kanji yang diberi furigana;
// okurigana, katakana, dan tanda baca dibiarkan polos.
//
//   toFurigana('迷う[道に〜]', 'まよう[みちに〜]') -> '迷《まよ》う[道《みち》に〜]'
//
// Bila susunan kana tidak cocok dengan bacaannya (data tidak lazim), seluruh kata
// diberi satu furigana sebagai cadangan; kata tanpa kanji dikembalikan apa adanya.

const KANJI = '\\u3400-\\u9FFF々〇〻'
const RUN = new RegExp(`([${KANJI}]+)|([^${KANJI}]+)`, 'g')
const HAS_KANJI = new RegExp(`[${KANJI}]`)

const escapeRe = s => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')

// Tanda bantu di data (kurung, ～, tanda baca) tidak selalu ada di bacaan: dibuat opsional.
const OPTIONAL = new Set('[]()（）〜～。、 ・/'.split(''))
const nonKanjiPattern = text => [...text].map(c => (OPTIONAL.has(c) ? `${escapeRe(c)}?` : escapeRe(c))).join('')

function align(ja, yomi, lenient) {
  const runs = [...ja.matchAll(RUN)].map(m => ({ kanji: m[1] !== undefined, text: m[0] }))
  const other = lenient ? nonKanjiPattern : escapeRe
  const pattern = `^${runs.map(r => (r.kanji ? '(.+?)' : other(r.text))).join('')}$`
  const match = new RegExp(pattern).exec(yomi)
  if (!match)
    return null

  // Bacaan kanji tidak boleh memuat tanda bantu (tanda cocok yang salah arah).
  const groups = match.slice(1)
  if (groups.some(g => /[\[\]()（）〜～/]/.test(g)))
    return null

  let i = 0

  return runs.map(r => (r.kanji ? `${r.text}《${groups[i++]}》` : r.text)).join('')
}

// Data seperti 迷う[道に〜] / まよう[みちに〜]: cocokkan bagian utama dan isi kurung satu per satu.
function alignSegments(ja, yomi) {
  const split = t => t.split(/(\[[^\]]*\])/)
  const a = split(ja)
  const b = split(yomi)
  if (a.length !== b.length || a.length === 1)
    return null

  const parts = a.map((seg, i) => {
    if (!HAS_KANJI.test(seg))
      return seg

    return align(seg, b[i], false) ?? align(seg, b[i], true)
  })

  return parts.includes(null) ? null : parts.join('')
}

const cache = new Map()

export function toFurigana(japanese, reading) {
  const ja = (japanese ?? '').trim()
  const yomi = (reading ?? '').trim()

  if (!ja || !yomi || !HAS_KANJI.test(ja))
    return ja

  const key = `${ja}\u0000${yomi}`
  if (cache.has(key))
    return cache.get(key)

  let out = align(ja, yomi, false) ?? alignSegments(ja, yomi) ?? align(ja, yomi, true)

  // Keterangan dalam kurung (mis. 聞きます（先生に〜）) tidak punya bacaan sendiri:
  // beri furigana pada bagian utamanya saja, keterangan tetap polos.
  if (out === null) {
    const cut = ja.search(/[（(［\[]/)
    if (cut > 0) {
      const head = align(ja.slice(0, cut), yomi, false)
      if (head !== null)
        out = head + ja.slice(cut)
    }
  }

  // Cadangan terakhir: satu furigana untuk seluruh kata (hanya bila tanpa tanda bantu).
  if (out === null)
    out = !/[\[\]（）()〜～]/.test(ja) ? `${ja}《${yomi}》` : ja

  cache.set(key, out)

  return out
}
