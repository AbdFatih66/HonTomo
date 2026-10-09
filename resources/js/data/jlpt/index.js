// Helper tampilan untuk soal Tes JLPT. Bank soal TIDAK ada di frontend: soal
// (dan kunci jawabannya) hidup di server — resources/lang-data/jlpt/ — dan
// baru dikirim lewat API setelah sesi dimulai, tanpa kunci.
//
// Penanda di teks soal (selain furigana 漢字《かんじ》 yang dirender RubyText):
//   ⟦kata⟧   → kata bergaris bawah
//   ⟦　⟧     → kolom kosong bergaris (soal ★ / もんだい 2)
//   ⟦★⟧      → kolom kosong bergaris yang bertanda ★
//   {{22}}   → kotak bernomor (boleh {{43-a}} / {{43-b}} untuk kotak bersuffiks, N1 もんだい 7) (nomor kolom kosong di bacaan, もんだい 3)

const TOKEN = /⟦([^⟧]*)⟧|\{\{(\d+(?:-[a-z])?)\}\}/g

// teks → [{ text, underline, blank, star, box }]
export function parseStem(stem) {
  const src = stem ?? ''
  const out = []
  let last = 0

  TOKEN.lastIndex = 0

  let m = TOKEN.exec(src)

  while (m !== null) {
    if (m.index > last)
      out.push({ text: src.slice(last, m.index) })

    if (m[2] !== undefined) {
      out.push({ text: m[2], box: true })
    }
    else if (/^[\s\u3000★]*$/.test(m[1])) {
      out.push({ text: m[1], blank: true, star: m[1].includes('★') })
    }
    else {
      out.push({ text: m[1], underline: true })
    }

    last = m.index + m[0].length
    m = TOKEN.exec(src)
  }
  if (last < src.length)
    out.push({ text: src.slice(last) })

  return out
}

// Panjang teks tanpa furigana — dipakai untuk memilih tata letak pilihan.
export function plainLength(text) {
  return (text ?? '').replace(/《[^》]*》/g, '').length
}
