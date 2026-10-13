// Tes bentuk materi Membaca (N5, N4, N3).
// Jalankan: node --test tests/js/membaca.test.mjs  (Node ≥ 18, tanpa dependensi)
import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { describe, test } from 'node:test'

import {
  MEMBACA,
  MEMBACA_LEVELS,
  MEMBACA_LESSON_COUNT,
  MEMBACA_SOURCE_LESSONS,
} from '../../resources/js/data/practice/membaca.js'

const HAN = /[㐀-鿿々]/u
const RUBY = /[㐀-鿿々]+《[^》]*》/gu
const hasBilingual = o => o && typeof o.id === 'string' && o.id.trim() && typeof o.en === 'string' && o.en.trim()

// Semua kanji pada teks Jepang harus punya furigana 漢字《かんじ》.
const assertFurigana = (text, where) => {
  assert.ok(!HAN.test(text.replace(RUBY, '')), `kanji tanpa furigana di ${where}: ${text}`)
}

describe('Membaca — sinkron dengan backend', () => {
  test('MembacaController::AVAILABLE_KEYS = pelajaran yang punya materi', () => {
    const root = join(dirname(fileURLToPath(import.meta.url)), '../..')
    const php = readFileSync(join(root, 'app/Http/Controllers/Api/MembacaController.php'), 'utf8')
    const m = php.match(/AVAILABLE_KEYS\s*=\s*\[([^\]]*)\]/)

    assert.ok(m, 'AVAILABLE_KEYS tidak ditemukan')

    const backend = [...m[1].matchAll(/'([^']+)'/g)].map(x => x[1]).sort()
    const frontend = MEMBACA_LEVELS.flatMap(l => Object.keys(MEMBACA[l]).map(n => `${l.toLowerCase()}l${n}`)).sort()

    assert.deepEqual(backend, frontend)
  })
})

describe('Membaca — struktur', () => {
  test('tiga level, masing-masing 12 pelajaran terpetakan', () => {
    assert.deepEqual(MEMBACA_LEVELS, ['N5', 'N4', 'N3'])
    for (const level of MEMBACA_LEVELS) {
      assert.equal(Object.keys(MEMBACA_SOURCE_LESSONS[level]).length, MEMBACA_LESSON_COUNT)
    }
  })

  test('N5 dan N4: 25 pelajaran asli terbagi habis ke 12 tanpa tumpang tindih', () => {
    for (const level of ['N5', 'N4']) {
      const all = Object.values(MEMBACA_SOURCE_LESSONS[level]).flat()
      assert.deepEqual([...all].sort((a, b) => a - b), Array.from({ length: 25 }, (_, i) => i + 1))
    }
  })

  test('N3: satu banding satu', () => {
    for (let n = 1; n <= 12; n++)
      assert.deepEqual(MEMBACA_SOURCE_LESSONS.N3[n], [n])
  })

  test('Pelajaran 1 ada di ketiga level', () => {
    for (const level of MEMBACA_LEVELS)
      assert.ok(MEMBACA[level][1], `${level} Pelajaran 1 belum ada`)
  })
})

for (const level of MEMBACA_LEVELS) {
  describe(`Membaca ${level}`, () => {
    for (const [id, lesson] of Object.entries(MEMBACA[level])) {
      test(`Pelajaran ${id}: bentuk materi`, () => {
        assert.ok(Number(id) >= 1 && Number(id) <= MEMBACA_LESSON_COUNT)
        for (const f of ['title', 'focus', 'before', 'challenge'])
          assert.ok(hasBilingual(lesson[f]), `${f} harus dua bahasa`)

        assert.ok(lesson.passage.title)
        assert.ok(lesson.passage.paragraphs.length >= 3)
        for (const p of lesson.passage.paragraphs) {
          assert.ok(p.ja && p.id && p.en, 'paragraf butuh ja, id, en')
          assertFurigana(p.ja, 'paragraf')
        }
        assertFurigana(lesson.passage.title, 'judul bacaan')

        assert.ok(lesson.vocab.length >= 5)
        for (const w of lesson.vocab) {
          assert.ok(w.ja && w.reading && w.id && w.en)
          assert.ok(!HAN.test(w.reading), 'bacaan kata harus kana')
        }

        assert.ok(lesson.questions.length >= 5)
        const types = new Set(lesson.questions.map(q => q.type))
        assert.ok(types.has('mc') && types.has('tf'), 'harus ada soal mc dan tf')

        for (const q of lesson.questions) {
          assert.ok(['mc', 'tf'].includes(q.type))
          assertFurigana(q.q, 'soal')
          assert.ok(hasBilingual(q.explain), 'explain harus dua bahasa')
          if (q.type === 'mc') {
            assert.equal(q.choices.length, 4)
            assert.equal(new Set(q.choices).size, 4, 'pilihan tidak boleh kembar')
            assert.ok(Number.isInteger(q.answer) && q.answer >= 0 && q.answer < 4)
            q.choices.forEach(c => c.trim() && assertFurigana(c, 'pilihan'))
          }
          else {
            assert.equal(typeof q.answer, 'boolean')
          }
        }
      })

      test(`Pelajaran ${id}: tidak menyebut nama buku`, () => {
        assert.ok(!/minna/i.test(JSON.stringify(lesson)))
      })
    }
  })
}
