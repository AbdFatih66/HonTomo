// Tes bentuk materi latihan N3 (Mondaishuu, Kaite Oboeru, Chokai, Kaiwa).
// Jalankan: node --test tests/js/n3Practice.test.mjs  (Node ≥ 18, tanpa dependensi)
import assert from 'node:assert/strict'
import { existsSync, readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { describe, test } from 'node:test'
import { fileURLToPath } from 'node:url'

import { LESSONS_N3 as KAITE } from '../../resources/js/data/practice/kaiteOboeruN3.js'
import { LESSONS_N3 as MONDAI } from '../../resources/js/data/practice/mondaishuuN3.js'

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '../..')
const loadJson = p => JSON.parse(readFileSync(join(ROOT, p), 'utf8'))
const HAN = /[\u3400-\u9FFF々]/u
const RUBY = /[\u3400-\u9FFF々]+《[^》]*》/gu

// Tambah nomor pelajaran N3 baru di sini (sama dengan N3_LESSON_IDS di pages/chokai/index.vue).
const PRACTICE_LESSONS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]

// Jawaban Kaite Oboeru berisi っ yang belum punya data goresan (siswa melewatinya lewat skipWriting).
const KNOWN_UNWRITABLE = new Set(['しっけ', 'けっか', 'ひっこす', 'アンケート', 'けっきょく', 'さっそく', 'そっくり', 'いっしょう', 'とっきょ', 'じっさい', 'はっくつ'])

const hasBilingual = o => o && typeof o.id === 'string' && o.id.trim() && typeof o.en === 'string' && o.en.trim()

describe('Mondaishuu N3', () => {
  test('pelajaran lengkap, berurut, soal cukup', () => {
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

describe('Kaite Oboeru N3', () => {
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

describe('Chokai N3', () => {
  for (const n of PRACTICE_LESSONS) {
    test(`Pelajaran ${n}: id berurut, jawaban valid, audio_text tanpa furigana`, () => {
      const d = loadJson(`public/data/chokai/lesson-n3-${n}.json`)

      assert.equal(d.id, `n3-${n}`)
      assert.equal(d.level, 'N3')
      assert.equal(d.questions.length, 10)
      assert.ok(d.kaiwa.questions.length <= 2)
      d.questions.forEach((q, i) => {
        assert.equal(q.id, `n3l${n}-q${i + 1}`)
        assert.ok(q.answer >= 0 && q.answer < q.choices.length, q.id)
        assert.equal(new Set(q.choices).size, q.choices.length, `pilihan kembar: ${q.id}`)
        assert.ok(hasBilingual(q.translate), q.id)
        for (const t of q.audio_text)
          assert.ok(!t.text.includes('《'), `audio_text berisi furigana: ${q.id}`)
        for (const s of [q.prompt, ...q.choices])
          assert.ok(!HAN.test(s.replace(RUBY, '')), `kanji tanpa furigana: ${q.id} ${s}`)
      })
    })
  }
})

describe('Kaiwa N3', () => {
  test('manifest, skenario, furigana dan speak konsisten', () => {
    const manifest = loadJson('public/data/kaiwa/index-n3.json')

    assert.deepEqual(manifest.lessons.map(l => l.id), PRACTICE_LESSONS)

    const seen = new Set()

    for (const entry of manifest.lessons) {
      const d = loadJson(`public/data/kaiwa/${entry.file}`)

      assert.equal(d.level, 'N3')
      assert.ok(d.scenarios.length >= 4)
      for (const sc of d.scenarios) {
        assert.match(sc.id, new RegExp(`^n3l${d.id}-s\\d+$`))
        assert.ok(sc.turns.length >= 6 && sc.turns.length <= 8, sc.id)
        const you = sc.turns.filter(t => t.who === 'you')

        assert.ok(you.length >= 3 && you.length <= 4, sc.id)
        for (const t of sc.turns) {
          assert.ok(!seen.has(t.id), `id ganda ${t.id}`)
          seen.add(t.id)
          assert.ok(!HAN.test(t.speak) && !t.speak.includes('《'), `speak harus kana: ${t.id}`)
          assert.ok(!HAN.test(t.ja.replace(RUBY, '')), `kanji tanpa furigana: ${t.id}`)
          const strip = s => s.replace(/[\s…、。「」]/gu, '')

          assert.equal(strip(t.speak), strip(t.ja.replace(/[\u3400-\u9FFF々]+《([^》]*)》/gu, '$1')), `speak ≠ furigana: ${t.id}`)
          if (t.who === 'you')
            assert.ok(t.accept?.length, t.id)
        }
      }
    }
  })
})
