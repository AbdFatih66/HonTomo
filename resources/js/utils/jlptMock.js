// Fungsi murni untuk pack bergaya "mock" pada Tes JLPT (mudah diuji tanpa DOM).
// Struktur data: lihat resources/lang-data/jlpt/packs/*/ dan docs/JLPT-TEST.md.
// Penilaian & skor TIDAK lagi dihitung di klien — semuanya ada di JlptTestService.

/** Semua soal bernilai dalam satu sesi (contoh "例" tidak termasuk). */
export function sectionQuestions(section) {
  const out = []

  for (const m of section.mondai) {
    for (const g of m.groups)
      for (const q of g.questions)
        out.push({ ...q, mondaiId: m.id })
  }

  return out
}

/** Detik → "MM:SS". */
export function formatClock(seconds) {
  const s = Math.max(0, Math.floor(seconds))
  const mm = String(Math.floor(s / 60)).padStart(2, '0')
  const ss = String(s % 60).padStart(2, '0')

  return `${mm}:${ss}`
}

/**
 * Urutan langkah Chokai: tiap mondai = intro → contoh → soal 1..n.
 * Contoh ditandai `example: true` (tidak dinilai, kunci jawaban ditampilkan).
 * `script` = teks untuk suara browser (hanya ada di mode latihan).
 */
export function buildListeningSteps(section) {
  const steps = []

  for (const m of section.mondai) {
    steps.push({ kind: 'intro', mondai: m, audio: m.intro?.audio ?? null, script: m.intro?.audio_text ?? [], key: `${m.id}-intro` })

    if (m.example)
      steps.push({ kind: 'item', mondai: m, q: m.example, example: true, audio: m.example.audio ?? null, script: m.example.audio_text ?? [], key: m.example.id })

    for (const g of m.groups) {
      for (const q of g.questions)
        steps.push({ kind: 'item', mondai: m, q, example: false, audio: q.audio ?? null, script: q.audio_text ?? [], key: q.id })
    }
  }

  return steps
}
