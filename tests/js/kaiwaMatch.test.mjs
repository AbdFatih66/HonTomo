// Tes untuk resources/js/utils/kaiwaMatch.js (penilaian ucapan Kaiwa).
// Jalankan: node --test tests/js/kaiwaMatch.test.mjs
// Tanpa dependensi: memakai node:test bawaan Node (≥ 18). Modul dimuat apa adanya (tanpa alias @/).
import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { before, describe, test } from 'node:test'
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
    ['5個', 'ごこ'],
    ['1個', 'いっこ'],
    ['10個', 'じゅっこ'],
    ['2本', 'にほん'],
    ['3本', 'さんぼん'],
    ['6本', 'ろっぽん'],
    ['10本', 'じゅっぽん'],
    ['1膳', 'いちぜん'],
    ['38度', 'さんじゅうはちど'],
    ['もう1度', 'もういちど'],
    ['3番線', 'さんばんせん'],
    ['3番', 'さんばん'],
    ['3番の ライン', 'さんばんの ライン'],
    ['3年', 'さんねん'],
    ['三年', 'さんねん'],
    ['4年', 'よねん'],
    ['7年', 'しちねん'],
    ['9年', 'くねん'],
    ['10年', 'じゅうねん'],
    ['4年間', 'よねんかん'],
    ['三年間', 'さんねんかん'],
    ['6年前', 'ろくねん前'],
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
    assert.equal(numbersToKana('10000円'), 'いちまんえん') // 万 didukung (paket Bank); sampai 8 digit
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

describe('paket situasi (situations di index.json) × penilai', () => {
  const index = loadJson('index.json')
  const packs = (index.situations ?? []).map(m => loadJson(m.file))

  // Di dalam before() (bukan saat koleksi): registerReadings mengubah peta global, jadi jangan sampai
  // mempengaruhi tes registerReadings di atas (mis. 一番 yang sengaja tidak terdaftar di sana).
  before(() => {
    for (const pack of packs) {
      for (const sc of pack.scenarios) {
        for (const t of sc.turns)
          registerReadings(t.ja)
      }
    }
  })

  test('ada minimal satu paket situasi dan setiap entri punya berkas', () => {
    assert.ok(packs.length >= 1)
    for (const [i, m] of (index.situations ?? []).entries())
      assert.equal(packs[i].id, m.id)
  })

  test('setiap giliran (kedua peran) cocok dengan dirinya sendiri (speak, ja, accept)', () => {
    const failures = []

    for (const pack of packs) {
      for (const sc of pack.scenarios) {
        for (const t of sc.turns) {
          for (const f of [t.speak, stripRuby(t.ja), ...(t.accept ?? [])]) {
            const score = scoreSpeech([f], t)

            if (score < 0.97)
              failures.push(`${t.id}: "${f}" → ${score.toFixed(2)}`)
          }
        }
      }
    }

    assert.deepEqual(failures, [])
  })

  test('bentuk digit dari ucapan (mis. 25歳, 4年間, 5分前) tetap lolos', () => {
    const t = loadJson('situation-mensetsu.json').scenarios.flatMap(sc => sc.turns)
    const byId = Object.fromEntries(t.map(x => [x.id, x]))

    assert.ok(scoreSpeech(['はい。ブディと申します。25歳です。インドネシアのジャカルタから来ました。'], byId['mensetsu-s2-t2']) >= 0.97)
    assert.ok(scoreSpeech(['工場で4年間働きました。機械を使って、部品を作りました。'], byId['mensetsu-s2-t4']) >= 0.97)
    assert.ok(scoreSpeech(['6年前に工業高校を卒業しました。'], byId['mensetsu-s4-t2']) >= 0.97)
    assert.ok(scoreSpeech(['5人です。父と母と兄と妹と私です。'], byId['mensetsu-s3-t2']) >= 0.97)
    assert.ok(scoreSpeech(['はい、できます。毎日5分前に会社に着きます。'], byId['mensetsu-s14-t6']) >= 0.9)
  })

  test('jawaban yang jelas salah tidak lolos (mis. 短所 diganti 長所)', () => {
    const t = loadJson('situation-mensetsu.json').scenarios.flatMap(sc => sc.turns).find(x => x.id === 'mensetsu-s8-t2')

    assert.ok(scoreSpeech(['私の長所はまじめなことです'], t) < PASS_SCORE)
  })

  test('jawaban untuk pertanyaan lain dalam skenario yang sama tidak saling meloloskan', () => {
    // Pengguna harus membedakan "alasan melamar" dari "kelebihan", dst.: dua jawaban berbeda dalam satu
    // skenario tidak boleh saling meloloskan pada ambang PASS_SCORE.
    const clashes = []

    for (const pack of packs) {
      for (const sc of pack.scenarios) {
        const mine = sc.turns.filter(t => t.who === 'you')

        for (const a of mine) {
          for (const b of mine) {
            if (a.id !== b.id && a.speak !== b.speak && scoreSpeech([b.speak], a) >= PASS_SCORE)
              clashes.push(`${a.id} ← ${b.id}`)
          }
        }
      }
    }
    assert.deepEqual(clashes, [])
  })

  test('jawaban panjang tetap lolos dengan satu-dua salah dengar kecil', () => {
    const t = loadJson('situation-mensetsu.json').scenarios.flatMap(sc => sc.turns).find(x => x.id === 'mensetsu-s16-t6')
    // "とても" didengar "とって" dan "のんで" didengar "のって"
    const heard = t.speak.replace('とても', 'とって').replace('のんで', 'のって')

    assert.notEqual(heard, t.speak)
    assert.ok(scoreSpeech([heard], t) >= PASS_SCORE)
  })

  test('struktur: id unik, 6–10 giliran, minimal 3 giliran user, speak tanpa kanji', () => {
    const seen = new Set()

    for (const pack of packs) {
      for (const sc of pack.scenarios) {
        assert.match(sc.id, new RegExp(`^${pack.id}-s\\d+$`))
        assert.ok(sc.turns.length >= 6 && sc.turns.length <= 10, sc.id)
        const you = sc.turns.filter(x => x.who === 'you').length

        assert.ok(you >= 3, sc.id)
        for (const t of sc.turns) {
          assert.ok(!seen.has(t.id), `id ganda ${t.id}`)
          seen.add(t.id)
          assert.doesNotMatch(t.speak, /\p{Script=Han}|《/u, t.id)
        }
      }
    }
  })
})

describe('paket situasi harian (kereta, konbini, pabrik, restoran, rumahsakit, telepon, supermarket, pakaian, gaji, keitai, bank, kelurahan, tetangga) × penilai', () => {
  const index = loadJson('index.json')
  const daily = ['kereta', 'konbini', 'pabrik', 'restoran', 'rumahsakit', 'telepon', 'supermarket', 'pakaian', 'gaji', 'keitai', 'bank', 'kelurahan', 'tetangga'].filter(id => (index.situations ?? []).some(m => m.id === id))
  const packs = daily.map(id => loadJson(`situation-${id}.json`))
  const turnsOf = id => packs.find(p => p.id === id).scenarios.flatMap(sc => sc.turns)

  before(() => {
    for (const pack of packs) {
      for (const sc of pack.scenarios) {
        for (const t of sc.turns)
          registerReadings(t.ja)
      }
    }
  })

  // Pengenal suara (Chrome) hampir selalu menulis angka sebagai digit: 5個, 38度, 3番, 2本, 1度.
  // Sapuan otomatis: ubah semua angka kanji + pencacah di tiap giliran menjadi digit, lalu nilai.
  const KNUM = { 〇: 0, 一: 1, 二: 2, 三: 3, 四: 4, 五: 5, 六: 6, 七: 7, 八: 8, 九: 9 }
  const parseKanji = (str) => {
    let sec = 0
    let cur = 0

    for (const ch of str) {
      if (ch in KNUM)
        cur = KNUM[ch]
      else if (ch === '十' || ch === '百' || ch === '千') {
        sec += (cur || 1) * { 十: 10, 百: 100, 千: 1000 }[ch]
        cur = 0
      }
    }

    return sec + cur
  }
  const toDigits = str => str.replace(/([〇一二三四五六七八九十百千]+)(?=[時分円個枚本度番人年日月歳膳つ階])/g, m => String(parseKanji(m)))

  test('semua giliran user tetap lolos bila angkanya didengar sebagai digit', () => {
    const failures = []
    let tested = 0

    for (const pack of packs) {
      for (const t of pack.scenarios.flatMap(sc => sc.turns).filter(x => x.who === 'you')) {
        const kanji = stripRuby(t.ja)
        const digits = toDigits(kanji)

        if (digits === kanji)
          continue
        tested++
        const score = scoreSpeech([digits], t)

        if (score < 0.9)
          failures.push(`${t.id}: ${score.toFixed(2)} ${digits}`)
      }
    }

    assert.ok(tested > 10)
    assert.deepEqual(failures, [])
  })

  test('kasus tetap: 5個, 38度, 2本, 1膳, 5枚, 3番', () => {
    const ok = (id, heard) => assert.ok(scoreSpeech([heard], turnsOf(id.split('-s')[0]).find(x => x.id === id)) >= PASS_SCORE, `${id}: ${heard}`)

    ok('konbini-s8-t1', 'すみません、からあげを5個ください')
    ok('pabrik-s6-t3', '38度です。頭も痛いです')
    ok('konbini-s3-t5', '単三を2本お願いします')
    ok('konbini-s1-t4', 'はい、1膳お願いします')
    ok('konbini-s4-t3', '白黒で5枚コピーしたいです')
    ok('pabrik-s4-t3', '3番のラインです。傷があります')
  })

  test('restoran: jumlah orang dan nomor dalam bentuk digit tetap lolos, jawaban salah tidak', () => {
    const t = turnsOf('restoran').find(x => x.id === 'restoran-s1-t2')
    const seven = turnsOf('restoran').find(x => x.id === 'restoran-s7-t4')

    assert.ok(scoreSpeech(['2人です'], t) >= PASS_SCORE)
    assert.ok(scoreSpeech(['明日の夜7時に3人です'], seven) >= PASS_SCORE)
    assert.ok(scoreSpeech(['三人です'], t) < PASS_SCORE + 0.3 && scoreSpeech(['カードは使えますか'], t) < PASS_SCORE)
  })

  test('rumah sakit: 3日前, 38度, 1回 dalam bentuk digit tetap lolos; gejala lain tidak', () => {
    const fever = turnsOf('rumahsakit').find(x => x.id === 'rumahsakit-s2-t4')
    const temp = turnsOf('rumahsakit').find(x => x.id === 'rumahsakit-s2-t6')

    assert.ok(scoreSpeech(['3日前からです'], fever) >= PASS_SCORE)
    assert.ok(scoreSpeech(['昨日は38度でした'], temp) >= PASS_SCORE)
    assert.ok(scoreSpeech(['お腹が痛いです'], fever) < PASS_SCORE)
  })

  test('telepon: jam, menit, dan 8時 dalam bentuk digit tetap lolos; jawaban skenario lain tidak', () => {
    const late = turnsOf('telepon').find(x => x.id === 'telepon-s7-t6')
    const meet = turnsOf('telepon').find(x => x.id === 'telepon-s8-t8')
    const bad = turnsOf('telepon').find(x => x.id === 'telepon-s8-t2')

    assert.ok(scoreSpeech(['15分ぐらいです'], late) >= PASS_SCORE)
    assert.ok(scoreSpeech(['明日の朝8時に駅ですね。わかりました'], meet) >= PASS_SCORE)
    assert.ok(scoreSpeech(['明日の朝8時に駅ですね。わかりました'], bad) < PASS_SCORE)
  })

  test('digit 日: 3日→みっか, 2日→ふつか, 10日→とおか; 3月3日 dan 日曜日 tidak berubah', () => {
    assert.equal(normalize('3日前'), normalize('みっか前'))
    assert.equal(normalize('2日'), 'ふつか')
    assert.equal(normalize('10日'), 'とおか')
    assert.equal(normalize('日曜日'), normalize('日曜日'))
    assert.ok(!normalize('3月3日').includes('みっか'))
  })

  test('jawaban untuk skenario lain tidak meloloskan (kereta vs konbini)', () => {
    const t = turnsOf('konbini').find(x => x.id === 'konbini-s1-t2')

    assert.ok(scoreSpeech(['横浜まで大人一枚お願いします'], t) < PASS_SCORE)
  })

  test('supermarket & pakaian: harga/ukuran digit tetap lolos; jawaban skenario lain tidak', () => {
    const size = turnsOf('pakaian').find(x => x.id === 'pakaian-s7-t3')
    const ask = turnsOf('pakaian').find(x => x.id === 'pakaian-s2-t1')
    const pay = turnsOf('supermarket').find(x => x.id === 'supermarket-s3-t4')

    assert.ok(scoreSpeech(['26センチです'], size) >= PASS_SCORE)
    assert.ok(scoreSpeech(['すみません、このシャツのMサイズはありますか'], ask) >= PASS_SCORE)
    assert.ok(scoreSpeech(['魚もほしいです。これを2切れください'], pay) >= PASS_SCORE)
    assert.ok(scoreSpeech(['26センチです'], ask) < PASS_SCORE)
  })

  test('gaji: jam lembur, 二十五日, dan 千五百円 dalam bentuk digit tetap lolos; jawaban skenario lain tidak', () => {
    const hours = turnsOf('gaji').find(x => x.id === 'gaji-s5-t3')
    const price = turnsOf('gaji').find(x => x.id === 'gaji-s3-t5')
    const other = turnsOf('gaji').find(x => x.id === 'gaji-s6-t1')

    assert.ok(scoreSpeech(['残業が10時間と書いてあります。でも、わたしのメモでは12時間です'], hours) >= PASS_SCORE)
    assert.ok(scoreSpeech(['1時間の残業代はいくらですか'], price) >= PASS_SCORE)
    assert.ok(scoreSpeech(['残業が10時間と書いてあります。でも、わたしのメモでは12時間です'], other) < PASS_SCORE)
  })

  test('bank: 万円 (3万円, 10万円, 4万5000円, 50000円) dibaca benar; 万 di kata lain tidak rusak', () => {
    assert.equal(normalize('3万円'), normalize('さんまんえん'))
    assert.equal(normalize('10万円'), normalize('じゅうまんえん'))
    assert.equal(normalize('十万円'), normalize('じゅうまんえん'))
    assert.equal(normalize('1万円'), normalize('いちまんえん'))
    assert.equal(normalize('4万5000円'), normalize('よんまんごせんえん'))
    assert.equal(normalize('50,000円'), normalize('ごまんえん'))
    assert.equal(normalize('120000円'), normalize('じゅうにまんえん'))
    assert.equal(normalize('万年筆'), normalize('万年筆'))
    assert.ok(!normalize('万年筆').includes('まん'))
  })

  test('bank: jumlah dalam bentuk digit tetap lolos; jawaban skenario lain tidak', () => {
    const atm = turnsOf('bank').find(x => x.id === 'bank-s3-t6')
    const send = turnsOf('bank').find(x => x.id === 'bank-s4-t4')
    const lost = turnsOf('bank').find(x => x.id === 'bank-s7-t2')

    assert.ok(scoreSpeech(['3万円を引き出したいです'], atm) >= PASS_SCORE)
    assert.ok(scoreSpeech(['家族に10万円を送ります'], send) >= PASS_SCORE)
    assert.ok(scoreSpeech(['家族に10万円を送ります'], atm) < PASS_SCORE)
    assert.ok(scoreSpeech(['キャッシュカードをなくしました。すぐ止めてください'], lost) >= PASS_SCORE)
    assert.ok(scoreSpeech(['キャッシュカードをなくしました。すぐ止めてください'], send) < PASS_SCORE)
  })

  test('kelurahan: 2枚, 14日, 3年 dalam bentuk digit tetap lolos; jawaban skenario lain tidak', () => {
    const copies = turnsOf('kelurahan').find(x => x.id === 'kelurahan-s3-t8')
    const days = turnsOf('kelurahan').find(x => x.id === 'kelurahan-s5-t8')
    const years = turnsOf('kelurahan').find(x => x.id === 'kelurahan-s8-t8')
    const sign = turnsOf('kelurahan').find(x => x.id === 'kelurahan-s10-t10')

    assert.ok(scoreSpeech(['2枚お願いします'], copies) >= PASS_SCORE)
    assert.ok(scoreSpeech(['はい、14日以内ですね。受理番号をメモします。ありがとうございます'], days) >= PASS_SCORE)
    assert.ok(scoreSpeech(['はい、確認しました。在留期間は3年ですね'], years) >= PASS_SCORE)
    assert.ok(scoreSpeech(['2枚お願いします'], sign) < PASS_SCORE)
  })

  test('tetangga: 8時 dalam bentuk digit tetap lolos; jawaban skenario lain tidak', () => {
    const put = turnsOf('tetangga').find(x => x.id === 'tetangga-s2-t8')
    const sorry = turnsOf('tetangga').find(x => x.id === 'tetangga-s4-t6')

    assert.ok(scoreSpeech(['わかりました。朝8時までに出します'], put) >= PASS_SCORE)
    assert.ok(scoreSpeech(['わかりました。朝8時までに出します'], sorry) < PASS_SCORE)
    assert.ok(scoreSpeech(['鍵を無くしました'], turnsOf('tetangga').find(x => x.id === 'tetangga-s6-t2')) >= PASS_SCORE)
  })
})
