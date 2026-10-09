// Tes bentuk materi latihan N4 (Mondaishuu, Kaite Oboeru, Chokai, Kaiwa) untuk semua pelajaran.
// Jalankan: node --test tests/js/n4Practice.test.mjs  (Node ≥ 18, tanpa dependensi)
import assert from 'node:assert/strict'
import { existsSync, readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { describe, test } from 'node:test'
import { fileURLToPath } from 'node:url'

import { LESSONS_N4 as KAITE } from '../../resources/js/data/practice/kaiteOboeruN4.js'
import { LESSONS_N4 as MONDAI } from '../../resources/js/data/practice/mondaishuuN4.js'

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '../..')
const loadJson = p => JSON.parse(readFileSync(join(ROOT, p), 'utf8'))
const HAN = /[\u3400-\u9FFF々]/u
const RUBY = /[\u3400-\u9FFF々]+《[^》]*》/gu

// Pelajaran dengan bank soal Mondaishuu + Kaite Oboeru, dan yang punya berkas Chokai + Kaiwa.
const PRACTICE_LESSONS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25]
const LISTENING_LESSONS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25]
const CHOKAI_COUNT = { 19: 9 } // selain ini: 10 soal

// Skenario lama yang lebih pendek dari aturan 6–8 giliran / 3–4 giliran user (perpanjang lalu hapus dari daftar).
const SHORT_SCENARIOS = new Set(['n4l3-s1', 'n4l3-s2', 'n4l3-s4', 'n4l11-s2', 'n4l11-s3'])

// Jawaban Kaite Oboeru berisi っ／ッ／ィ yang mungkin belum punya data goresan (siswa bisa melewatinya).
const KNOWN_UNWRITABLE = new Set(['ペット', 'はっけん', 'はっぴょう', 'けっこんしき', 'しゅっぱつします', 'はっぴょうかい', 'かえったところです', 'いらっしゃいます', 'おっしゃいます'])

const hasBilingual = o => o && typeof o.id === 'string' && o.id.trim() && typeof o.en === 'string' && o.en.trim()

describe('Mondaishuu N4', () => {
  test('pelajaran lengkap, berurut, tanpa id ganda', () => {
    assert.deepEqual(MONDAI.map(l => l.id), PRACTICE_LESSONS)
    for (const l of MONDAI) {
      assert.ok(hasBilingual(l.focus), `focus Pelajaran ${l.id}`)
      assert.ok(l.questions.length >= 15, `Pelajaran ${l.id}: ${l.questions.length} soal`)
    }
  })

  test('tiap soal lengkap dan jawabannya valid', () => {
    for (const l of MONDAI) {
      for (const q of l.questions) {
        assert.ok(hasBilingual(q.translate), `translate: ${l.id} ${JSON.stringify(q).slice(0, 60)}`)
        if (q.type === 'mc') {
          assert.equal(q.choices.length, 4, q.q)
          assert.equal(new Set(q.choices).size, 4, `pilihan kembar: ${q.q}`)
          assert.ok(q.answer >= 0 && q.answer < 4, q.q)
        }
        else {
          assert.equal(q.type, 'scramble')
          assert.ok(q.words.length >= 2, `Pelajaran ${l.id}: susun kalimat terlalu pendek`)
        }
      }
    }
  })

  test('semua kanji di soal diberi furigana', () => {
    for (const l of MONDAI) {
      for (const q of l.questions) {
        const texts = q.type === 'mc' ? [q.q, ...q.choices] : q.words
        for (const s of texts)
          assert.ok(!HAN.test(s.replace(RUBY, '')), `kanji tanpa furigana (P${l.id}): ${s}`)
      }
    }
  })
})

describe('Kaite Oboeru N4', () => {
  const stripsPath = join(ROOT, 'resources/js/data/kana-strokes.json')
  const strokes = existsSync(stripsPath) ? loadJson('resources/js/data/kana-strokes.json') : null

  function segment(answer) {
    const out = []
    for (let i = 0; i < answer.length;) {
      if (strokes[answer.slice(i, i + 2)]) { out.push(answer.slice(i, i + 2)); i += 2 }
      else if (strokes[answer[i]]) { out.push(answer[i]); i += 1 }
      else { out.push(null); i += 1 }
    }

    return out
  }

  test('pelajaran lengkap, ≥20 soal, jawaban unik, prompt dan cue dua bahasa', () => {
    assert.deepEqual(KAITE.map(l => l.id), PRACTICE_LESSONS)
    for (const l of KAITE) {
      assert.ok(hasBilingual(l.title) && hasBilingual(l.subtitle), `judul Pelajaran ${l.id}`)
      assert.ok(l.questions.length >= 20, `Pelajaran ${l.id}`)
      assert.equal(new Set(l.questions.map(q => q.answer)).size, l.questions.length, `jawaban ganda P${l.id}`)
      for (const q of l.questions) {
        assert.equal(q.type, 'write')
        assert.ok(hasBilingual(q.cue) && hasBilingual(q.prompt), `P${l.id}: ${q.answer}`)
        assert.ok(/^[\u3041-\u3096\u30A1-\u30FA\u30FC]+$/u.test(q.answer), `bukan kana: ${q.answer}`)
      }
    }
  })

  test('huruf tanpa data goresan hanya pada jawaban yang sudah dikenal', { skip: strokes ? false : 'kana-strokes.json tidak ditemukan' }, () => {
    for (const l of KAITE) {
      for (const q of l.questions) {
        if (KNOWN_UNWRITABLE.has(q.answer))
          continue
        assert.ok(!segment(q.answer).includes(null), `huruf tanpa data goresan (P${l.id}): ${q.answer}`)
      }
    }
  })
})

describe('Chokai N4', () => {
  for (const n of LISTENING_LESSONS) {
    test(`Pelajaran ${n}: id berurut, jawaban valid, audio_text tanpa furigana`, () => {
      const d = loadJson(`public/data/chokai/lesson-n4-${n}.json`)

      assert.equal(d.id, `n4-${n}`)
      assert.equal(d.level, 'N4')
      assert.equal(d.questions.length, CHOKAI_COUNT[n] ?? 10)
      assert.ok(d.kaiwa.questions.length <= 2)
      d.questions.forEach((q, i) => {
        assert.equal(q.id, `n4l${n}-q${i + 1}`)
        assert.ok(q.answer >= 0 && q.answer < q.choices.length)
        assert.equal(new Set(q.choices).size, q.choices.length, q.id)
        assert.ok(hasBilingual(q.translate), q.id)
        assert.ok(!JSON.stringify(q.audio_text).includes('《'), q.id)
      })
      d.kaiwa.questions.forEach((q, i) => assert.equal(q.id, `n4l${n}-kq${i + 1}`))
    })
  }
})

describe('Kaiwa N4', () => {
  const idx = loadJson('public/data/kaiwa/index-n4.json')
  const strip = s => s.replace(/[\s…、。「」]/gu, '')

  test('indeks memuat semua pelajaran dengan berkas yang cocok', () => {
    assert.deepEqual(idx.lessons.map(e => e.id), LISTENING_LESSONS)
    for (const entry of idx.lessons) {
      const lesson = loadJson(`public/data/kaiwa/${entry.file}`)

      assert.equal(entry.file, `lesson-n4-${entry.id}.json`)
      assert.equal(lesson.id, entry.id)
      assert.equal(lesson.level, 'N4')
    }
  })

  for (const n of LISTENING_LESSONS) {
    test(`Pelajaran ${n}: 4 skenario n4l${n}-sN, giliran dan teks bacaan konsisten`, () => {
      const lesson = loadJson(`public/data/kaiwa/lesson-n4-${n}.json`)
      const seen = new Set()

      assert.equal(lesson.scenarios.length, 4)
      for (const sc of lesson.scenarios) {
        assert.match(sc.id, new RegExp(`^n4l${n}-s\\d+$`))
        assert.ok(['male', 'female'].includes(sc.partner.voice), sc.id)

        const you = sc.turns.filter(t => t.who === 'you').length
        const short = SHORT_SCENARIOS.has(sc.id)

        assert.ok(sc.turns.length >= (short ? 5 : 6) && sc.turns.length <= 8, `${sc.id}: ${sc.turns.length} giliran`)
        assert.ok(you >= (short ? 2 : 3) && you <= 4, `${sc.id}: ${you} giliran user`)
        for (const t of sc.turns) {
          assert.ok(!seen.has(t.id), `id ganda ${t.id}`)
          seen.add(t.id)
          assert.ok(t.id.startsWith(`${sc.id}-t`))
          assert.ok(!HAN.test(t.speak) && !t.speak.includes('《'), t.id)
          assert.ok(!HAN.test(t.ja.replace(RUBY, '')), `kanji tanpa furigana: ${t.id}`)
          assert.equal(strip(t.speak), strip(t.ja.replace(/[\u3400-\u9FFF々]+《([^》]*)》/gu, '$1')), t.id)
          assert.ok(t.id_text.trim() && t.en_text.trim(), t.id)
          if (t.who === 'you')
            assert.ok(Array.isArray(t.accept) && t.accept.length > 0, t.id)
        }
      }
    })
  }
})
