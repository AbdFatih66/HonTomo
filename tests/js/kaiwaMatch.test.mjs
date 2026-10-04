// Tes untuk resources/js/utils/kaiwaMatch.js (penilaian ucapan Kaiwa).
// Jalankan: node --test tests/js/kaiwaMatch.test.mjs
// Tanpa dependensi: memakai node:test bawaan Node (≥ 18). Modul dimuat apa adanya (tanpa alias @/).
import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { describe, test } from 'node:test'
import { fileURLToPath } from 'node:url'

import {
  normalize,
  numbersToKana,
  PASS_SCORE,
  registerReadings,
  scoreSpeech,
  similarity,
  stripRuby,
  targetsOf,
} from '../../resources/js/utils/kaiwaMatch.js'

const DATA_DIR = join(dirname(fileURLToPath(import.meta.url)), '../../public/data/kaiwa')
const loadJson = f => JSON.parse(readFileSync(join(DATA_DIR, f), 'utf8'))

describe('stripRuby / normalize', () => {
  test('stripRuby membuang bacaan furigana', () => {
    assert.equal(stripRuby('インドネシア人《じん》です'), 'インドネシア人です')
    assert.equal(stripRuby(null), '')
  })

  test('katakana = hiragana, spasi dan tanda baca diabaikan, lebar-penuh dinormalkan', () => {
    assert.equal(normalize('ブディ です。'), normalize('ぶでぃです'))
    assert.equal(normalize('はい、そうです！'), 'はいそうです')
    assert.equal(normalize('Ａ'), 'a')
  })

  test('salah dengar nama ブディ dinormalkan ke nama', () => {
    assert.equal(normalize('初めまして 無理です'), normalize('はじめまして ブディです'))
    assert.equal(normalize('はじめまして ぶりです'), normalize('はじめまして ブディです'))
  })
})

describe('numbersToKana', () => {
  const cases = [
    ['25歳', 'にじゅうごさい'],
    ['二十五歳', 'にじゅうごさい'],
    ['20歳', 'はたち'],
    ['1歳', 'いっさい'],
    ['8歳', 'はっさい'],
    ['4時', 'よじ'],
    ['7時', 'しちじ'],
    ['9時', 'くじ'],
    ['12時', 'じゅうにじ'],
    ['5分', 'ごふん'],
    ['3分', 'さんぷん'],
    ['10分', 'じゅっぷん'],
    ['2000円', 'にせんえん'],
    ['3000円', 'さんぜんえん'],
    ['8000円', 'はっせんえん'],
    ['四百円', 'よんひゃくえん'],
    ['1階', 'いっかい'],
    ['2階', 'にかい'],
    ['3階', 'さんがい'],
    ['1人', 'ひとり'],
    ['2人', 'ふたり'],
    ['3人', 'さんにん'],
    ['4人', 'よにん'],
    ['3枚', 'さんまい'],
    ['2台', 'にだい'],
    ['2時間', 'にじかん'],
    ['4時間', 'よじかん'],
    ['1回', 'いっかい'],
    ['3回', 'さんかい'],
    ['6回', 'ろっかい'],
    ['2週間', 'にしゅうかん'],
    ['1か月', 'いっかげつ'],
    ['3か月', 'さんかげつ'],
    ['2つ', 'ふたつ'],
    ['9つ', 'ここのつ'],
    ['にじゅうご歳', 'にじゅうご歳'], // bukan angka: tidak diubah
  ]

  for (const [input, expected] of cases) {
    test(`${input} → ${expected}`, () => {
      assert.equal(numbersToKana(input), expected)
    })
  }

  test('kalimat utuh dan batas cakupan', () => {
    assert.equal(numbersToKana('私も25歳です'), '私もにじゅうごさいです')
    assert.equal(numbersToKana('10000円'), '10000円') // > 9999 tidak diubah
    assert.equal(numbersToKana('0歳'), '0歳')
    assert.equal(numbersToKana('123'), '123') // tanpa pencacah tidak diubah
  })

  test('9時半 cocok dengan くじはん lewat normalize', () => {
    assert.equal(normalize('9時半'), 'くじはん')
  })
})

describe('registerReadings', () => {
  test('bacaan kanji dari furigana dipakai di kedua sisi', () => {
    registerReadings('毎朝《まいあさ》 六時《ろくじ》に 起《お》きます。')
    assert.equal(normalize('起きます'), 'おきます')
    assert.equal(similarity('毎朝 6時に 起きます', 'まいあさ ろくじに おきます'), 1)
  })

  test('angka murni TIDAK didaftarkan (一《いっ》か月, 二《ふた》つ) dan kata lain yang memuat 一 tidak rusak', () => {
    registerReadings('一《いっ》か月《げつ》に 二《ふた》つ 二《に》、三日《さんにち》')
    assert.equal(normalize('一番'), '一番')
    assert.equal(normalize('二人'), 'ふたり')
    assert.equal(normalize('1か月'), 'いっかげつ')
    assert.equal(normalize('二つ'), 'ふたつ')
    // yang bukan angka murni tetap terdaftar
    assert.equal(normalize('三日'), 'さんにち')
  })
})

describe('similarity / scoreSpeech', () => {
  test('batas kasus: sama, kosong, satu kosong', () => {
    assert.equal(similarity('はい', 'はい'), 1)
    assert.equal(similarity('', ''), 1)
    assert.equal(similarity('はい', ''), 0)
  })

  test('ambang lulus = 65%', () => {
    assert.equal(PASS_SCORE, 0.65)
  })

  test('targetsOf menggabung speak, ja tanpa furigana, dan accept tanpa duplikat', () => {
    const turn = { speak: 'はい、わたしです。', ja: 'はい、私《わたし》です。', accept: ['はい、わたしです。', 'はい、私です。'] }

    assert.deepEqual(targetsOf(turn), ['はい、わたしです。', 'はい、私です。'])
  })

  test('skor terbaik dari semua alternatif hasil pengenalan × semua target', () => {
    const turn = { speak: 'ブディです', ja: 'ブディです', accept: ['ぶでぃです'] }

    assert.equal(scoreSpeech(['まったくちがう', 'ブディです'], turn), 1)
  })

  test('ucapan benar lolos, salah dengar nama masih lolos', () => {
    const turn = { speak: 'はじめまして。ブディです。', ja: 'はじめまして。ブディです。' }

    assert.ok(scoreSpeech(['初めまして 無理です'], turn) >= PASS_SCORE)
    assert.ok(scoreSpeech(['はじめまして ブディです'], turn) >= PASS_SCORE)
  })

  test('jawaban negatif vs positif tidak lolos', () => {
    const turn = { speak: 'いいえ、はたらきません。', ja: 'いいえ、働《はたら》きません。' }

    assert.ok(scoreSpeech(['はい、はたらきます'], turn) < PASS_SCORE)
  })

  test('kalimat lain sama sekali tidak lolos', () => {
    const turn = { speak: 'デパートへ いきます。', ja: 'デパートへ 行《い》きます。' }

    assert.ok(scoreSpeech(['ありがとうございます'], turn) < PASS_SCORE)
  })
})

describe('materi kaiwa (public/data/kaiwa) × penilai', () => {
  const lessons = loadJson('index.json').lessons.map(m => loadJson(m.file))

  // Seperti halaman: semua furigana pelajaran didaftarkan lebih dulu.
  for (const lesson of lessons) {
    for (const sc of lesson.scenarios) {
      for (const t of sc.turns)
        registerReadings(t.ja)
    }
  }

  test('setiap giliran (kedua peran, karena putaran tukar peran) cocok dengan dirinya sendiri', () => {
    const failures = []

    for (const lesson of lessons) {
      for (const sc of lesson.scenarios) {
        for (const t of sc.turns) {
          const forms = [t.speak, stripRuby(t.ja), ...(t.accept ?? [])]

          for (const f of forms) {
            const score = scoreSpeech([f], t)

            if (score < 0.97)
              failures.push(`${t.id}: "${f}" → ${score.toFixed(2)}`)
          }
        }
      }
    }

    assert.deepEqual(failures, [])
  })
})
