<script setup>
// Kaite Oboeru (書いて覚える) — "write it to remember it" practice. Content
// below is organized lesson by lesson (word banks / picture prompts). Only
// a first couple of lessons are included so far; add more objects to
// LESSONS, following the same shape, to extend it further.
//
// Grading is automatic and stroke-based, exactly like the Kana/Kanji writing
// quiz: the user writes each kana character of the answer by hand and
// HanziWriter checks every stroke's shape/direction in real time (see
// KanaWritingCanvas.vue, which already powers Kana practice). A multi-kana
// answer (e.g. おはようございます) is written one character at a time; the
// question is graded once every character in the word is done. There is no
// self-report step — same as Kana/Kanji, not like Mondaishuu's tap-to-check.
import KaiteOboekuWritingCanvas from '@/components/learning/KaiteOboekuWritingCanvas.vue'
import { useAutoNext } from '@/composables/useAutoNext'
import { useAuthStore } from '@/stores/auth'
import kanaStrokes from '@/data/kana-strokes.json'
import { $api } from '@/utils/api'

const { t, locale } = useI18n()
const autoNext = useAutoNext()
const authStore = useAuthStore()

// Every learner-facing string below is bilingual ({ id, en }); pick the
// active one from the current UI locale instead of hardcoding Indonesian.
function tr(pair) {
  return locale.value === 'en' ? pair.en : pair.id
}

// Greedy-match against the known kana glyphs (see kana-strokes.json), trying
// two-character yoon combos (しゃ, きゅ, …) first since those are stored and
// written as one glyph, then falling back to single characters.
function segmentAnswer(answer) {
  const units = []
  let i = 0
  while (i < answer.length) {
    const two = answer.slice(i, i + 2)
    if (kanaStrokes[two]) {
      units.push(two)
      i += 2
      continue
    }
    const one = answer[i]
    if (kanaStrokes[one]) {
      units.push(one)
      i += 1
      continue
    }
    // No stroke data for this glyph (shouldn't happen with the curated
    // word list below) — still show it, but it'll need `skipWriting()`.
    units.push(one)
    i += 1
  }

  return units
}

// ---------------------------------------------------------------------
// Bank soal. Setiap soal: { type: 'write', prompt: {id,en}, cue: {id,en}, answer }
//  - prompt: instruksi/konteks soal (apa yang digambarkan/diminta)
//  - cue:    kata kunci (dipakai sebagai pengganti gambar pada halaman asli)
//  - answer: jawaban dalam hiragana — ditulis tangan huruf demi huruf,
//            dinilai otomatis lewat pengecekan goresan (sama seperti Kana)
// ---------------------------------------------------------------------
const LESSONS = [
  // The workbook (Kaite Oboeru) has 5 numbered chapters (だい1か..だい5か),
  // each made of several word-bank/picture-prompt sub-pages (e.g. 1■1, 1■2,
  // 1■3 ...). Each entry below is one chapter: a curated, non-repeating set
  // of 20 questions drawn from that chapter's own sub-pages (greetings,
  // occupations, objects, numbers, time, verbs, etc.) — no duplicate words.
  {
    id: 1,
    title: { id: 'Salam & Perkenalan Diri', en: 'Greetings & Self-introduction' },
    subtitle: { id: 'あいさつ・職業名・自己紹介', en: 'Greetings, occupations, self-introduction' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan ucapan salam ini dalam bahasa Jepang.', en: 'Write this greeting in Japanese.' }, cue: { id: 'Selamat pagi', en: 'Good morning' }, answer: 'おはようございます' },
      { type: 'write', prompt: { id: 'Tuliskan ucapan salam ini dalam bahasa Jepang.', en: 'Write this greeting in Japanese.' }, cue: { id: 'Selamat siang', en: 'Good afternoon' }, answer: 'こんにちは' },
      { type: 'write', prompt: { id: 'Tuliskan ucapan salam ini dalam bahasa Jepang.', en: 'Write this greeting in Japanese.' }, cue: { id: 'Selamat malam (bertemu)', en: 'Good evening (meeting)' }, answer: 'こんばんは' },
      { type: 'write', prompt: { id: 'Tuliskan ucapan salam ini dalam bahasa Jepang.', en: 'Write this greeting in Japanese.' }, cue: { id: 'Selamat tinggal / sampai jumpa', en: 'Goodbye / see you' }, answer: 'さようなら' },
      { type: 'write', prompt: { id: 'Tuliskan ucapan salam ini dalam bahasa Jepang.', en: 'Write this greeting in Japanese.' }, cue: { id: 'Selamat tidur', en: 'Good night' }, answer: 'おやすみなさい' },
      { type: 'write', prompt: { id: 'Tuliskan ucapan salam ini dalam bahasa Jepang.', en: 'Write this greeting in Japanese.' }, cue: { id: 'Permisi (masuk/lewat)', en: 'Excuse me (entering/passing)' }, answer: 'しつれいします' },
      { type: 'write', prompt: { id: 'Tuliskan ucapan salam ini dalam bahasa Jepang.', en: 'Write this greeting in Japanese.' }, cue: { id: 'Sebelum makan', en: 'Before eating' }, answer: 'いただきます' },
      { type: 'write', prompt: { id: 'Tuliskan kata bahasa Jepang untuk profesi ini.', en: 'Write the Japanese word for this occupation.' }, cue: { id: 'karyawan perusahaan', en: 'company employee' }, answer: 'かいしゃいん' },
      { type: 'write', prompt: { id: 'Tuliskan kata bahasa Jepang untuk profesi ini.', en: 'Write the Japanese word for this occupation.' }, cue: { id: 'guru', en: 'teacher' }, answer: 'せんせい' },
      { type: 'write', prompt: { id: 'Tuliskan kata bahasa Jepang untuk profesi ini.', en: 'Write the Japanese word for this occupation.' }, cue: { id: 'murid / mahasiswa', en: 'student' }, answer: 'がくせい' },
      { type: 'write', prompt: { id: 'Tuliskan kata bahasa Jepang untuk profesi ini.', en: 'Write the Japanese word for this occupation.' }, cue: { id: 'dokter', en: 'doctor' }, answer: 'いしゃ' },
      { type: 'write', prompt: { id: 'Tuliskan kata bahasa Jepang untuk profesi ini.', en: 'Write the Japanese word for this occupation.' }, cue: { id: 'pegawai bank', en: 'bank employee' }, answer: 'ぎんこういん' },
      { type: 'write', prompt: { id: 'Tuliskan nama negara ini dalam bahasa Jepang.', en: 'Write this country name in Japanese.' }, cue: { id: 'Indonesia', en: 'Indonesia' }, answer: 'インドネシア' },
      { type: 'write', prompt: { id: 'Tuliskan nama negara ini dalam bahasa Jepang.', en: 'Write this country name in Japanese.' }, cue: { id: 'Jepang', en: 'Japan' }, answer: 'にほん' },
      { type: 'write', prompt: { id: 'Tuliskan nama negara ini dalam bahasa Jepang.', en: 'Write this country name in Japanese.' }, cue: { id: 'Korea', en: 'Korea' }, answer: 'かんこく' },
      { type: 'write', prompt: { id: 'Tuliskan nama negara ini dalam bahasa Jepang.', en: 'Write this country name in Japanese.' }, cue: { id: 'Tiongkok', en: 'China' }, answer: 'ちゅうごく' },
      { type: 'write', prompt: { id: 'Tuliskan nama negara ini dalam bahasa Jepang.', en: 'Write this country name in Japanese.' }, cue: { id: 'Amerika', en: 'America' }, answer: 'アメリカ' },
      { type: 'write', prompt: { id: 'Tuliskan kewarganegaraan ini dalam bahasa Jepang.', en: 'Write this nationality in Japanese.' }, cue: { id: 'orang Jepang', en: 'Japan (person)' }, answer: 'にほんじん' },
      { type: 'write', prompt: { id: 'Tuliskan kewarganegaraan ini dalam bahasa Jepang.', en: 'Write this nationality in Japanese.' }, cue: { id: 'orang Korea', en: 'Korea (person)' }, answer: 'かんこくじん' },
      { type: 'write', prompt: { id: 'Tuliskan kewarganegaraan ini dalam bahasa Jepang.', en: 'Write this nationality in Japanese.' }, cue: { id: 'orang Amerika', en: 'America (person)' }, answer: 'アメリカじん' },
    ],
  },
  {
    id: 2,
    title: { id: 'Benda & Kata Tanya', en: 'Objects & Question Words' },
    subtitle: { id: '物の名前・疑問文の整理', en: 'Objects, question words' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'kamus', en: 'dictionary' }, answer: 'じしょ' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'buku', en: 'book' }, answer: 'ほん' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'meja', en: 'desk' }, answer: 'つくえ' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'kartu nama', en: 'business card' }, answer: 'めいし' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'koran', en: 'newspaper' }, answer: 'しんぶん' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'tas', en: 'bag' }, answer: 'かばん' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'majalah', en: 'magazine' }, answer: 'ざっし' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'pensil', en: 'pencil' }, answer: 'えんぴつ' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'mobil', en: 'car' }, answer: 'くるま' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'kamera', en: 'camera' }, answer: 'カメラ' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'televisi', en: 'television' }, answer: 'テレビ' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'radio', en: 'radio' }, answer: 'ラジオ' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'buku catatan', en: 'notebook' }, answer: 'メモちょう' },
      { type: 'write', prompt: { id: 'Tuliskan kata tanya ini dalam bahasa Jepang.', en: 'Write this question word in Japanese.' }, cue: { id: 'apa', en: 'what' }, answer: 'なに' },
      { type: 'write', prompt: { id: 'Tuliskan kata tanya ini dalam bahasa Jepang.', en: 'Write this question word in Japanese.' }, cue: { id: 'siapa', en: 'who' }, answer: 'だれ' },
      { type: 'write', prompt: { id: 'Tuliskan kata tanya ini dalam bahasa Jepang.', en: 'Write this question word in Japanese.' }, cue: { id: 'mana / di mana', en: 'where' }, answer: 'どこ' },
      { type: 'write', prompt: { id: 'Tuliskan kata tanya ini dalam bahasa Jepang.', en: 'Write this question word in Japanese.' }, cue: { id: 'kapan', en: 'when' }, answer: 'いつ' },
      { type: 'write', prompt: { id: 'Tuliskan kata tanya ini dalam bahasa Jepang.', en: 'Write this question word in Japanese.' }, cue: { id: 'berapa harga', en: 'how much' }, answer: 'いくら' },
      { type: 'write', prompt: { id: 'Tuliskan kata tanya ini dalam bahasa Jepang.', en: 'Write this question word in Japanese.' }, cue: { id: 'berapa umur', en: 'how old' }, answer: 'なんさい' },
      { type: 'write', prompt: { id: 'Tuliskan kata tanya ini dalam bahasa Jepang.', en: 'Write this question word in Japanese.' }, cue: { id: 'punya siapa', en: 'whose' }, answer: 'だれの' },
    ],
  },
  {
    id: 3,
    title: { id: 'Tempat, Angka & Belanja', en: 'Places, Numbers & Shopping' },
    subtitle: { id: '～はどこですか・数字・買い物 ほか', en: 'Places, numbers, shopping etc.' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'kantor', en: 'office' }, answer: 'じむしょ' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'kantin', en: 'cafeteria' }, answer: 'しょくどう' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'ruang kelas', en: 'classroom' }, answer: 'きょうしつ' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'ruang rapat', en: 'meeting room' }, answer: 'かいぎしつ' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'toilet', en: 'toilet' }, answer: 'トイレ' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'perpustakaan', en: 'library' }, answer: 'としょかん' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'bank', en: 'bank' }, answer: 'ぎんこう' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'sekolah', en: 'school' }, answer: 'がっこう' },
      { type: 'write', prompt: { id: 'Tuliskan angka ini dalam bahasa Jepang.', en: 'Write this number in Japanese.' }, cue: { id: '1', en: '1' }, answer: 'いち' },
      { type: 'write', prompt: { id: 'Tuliskan angka ini dalam bahasa Jepang.', en: 'Write this number in Japanese.' }, cue: { id: '10', en: '10' }, answer: 'じゅう' },
      { type: 'write', prompt: { id: 'Tuliskan angka ini dalam bahasa Jepang.', en: 'Write this number in Japanese.' }, cue: { id: '100', en: '100' }, answer: 'ひゃく' },
      { type: 'write', prompt: { id: 'Tuliskan angka ini dalam bahasa Jepang.', en: 'Write this number in Japanese.' }, cue: { id: '1.000', en: '1,000' }, answer: 'せん' },
      { type: 'write', prompt: { id: 'Tuliskan angka ini dalam bahasa Jepang.', en: 'Write this number in Japanese.' }, cue: { id: '10.000', en: '10,000' }, answer: 'まん' },
      { type: 'write', prompt: { id: 'Tuliskan angka ini dalam bahasa Jepang.', en: 'Write this number in Japanese.' }, cue: { id: '20', en: '20' }, answer: 'にじゅう' },
      { type: 'write', prompt: { id: 'Tuliskan nama barang ini dalam bahasa Jepang.', en: 'Write the Japanese name of this item.' }, cue: { id: 'anggur / wine', en: 'wine' }, answer: 'ワイン' },
      { type: 'write', prompt: { id: 'Tuliskan nama barang ini dalam bahasa Jepang.', en: 'Write the Japanese name of this item.' }, cue: { id: 'tas', en: 'bag' }, answer: 'かばん' },
      { type: 'write', prompt: { id: 'Tuliskan nama barang ini dalam bahasa Jepang.', en: 'Write the Japanese name of this item.' }, cue: { id: 'dasi', en: 'necktie' }, answer: 'ネクタイ' },
      { type: 'write', prompt: { id: 'Tuliskan nama barang ini dalam bahasa Jepang.', en: 'Write the Japanese name of this item.' }, cue: { id: 'payung', en: 'umbrella' }, answer: 'かさ' },
      { type: 'write', prompt: { id: 'Tuliskan asal buatan ini dalam bahasa Jepang.', en: 'Write where this is made, in Japanese.' }, cue: { id: 'buatan Jepang', en: 'made in Japan' }, answer: 'にほんの' },
      { type: 'write', prompt: { id: 'Tuliskan asal buatan ini dalam bahasa Jepang.', en: 'Write where this is made, in Japanese.' }, cue: { id: 'buatan Amerika', en: 'made in USA' }, answer: 'アメリカの' },
    ],
  },
  {
    id: 4,
    title: { id: 'Waktu, Hari & Kata Kerja', en: 'Time, Days & Verbs' },
    subtitle: { id: '時刻・曜日・～ます／～ました ほか', en: 'Time, days, masu-verbs etc.' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan waktu ini dalam bahasa Jepang.', en: 'Write this time in Japanese.' }, cue: { id: 'jam 1', en: '1 o’clock' }, answer: 'いちじ' },
      { type: 'write', prompt: { id: 'Tuliskan waktu ini dalam bahasa Jepang.', en: 'Write this time in Japanese.' }, cue: { id: 'jam 5', en: '5 o’clock' }, answer: 'ごじ' },
      { type: 'write', prompt: { id: 'Tuliskan waktu ini dalam bahasa Jepang.', en: 'Write this time in Japanese.' }, cue: { id: 'jam 10', en: '10 o’clock' }, answer: 'じゅうじ' },
      { type: 'write', prompt: { id: 'Tuliskan waktu ini dalam bahasa Jepang.', en: 'Write this time in Japanese.' }, cue: { id: 'setengah / 30 menit', en: 'half / 30 minutes' }, answer: 'はん' },
      { type: 'write', prompt: { id: 'Tuliskan waktu ini dalam bahasa Jepang.', en: 'Write this time in Japanese.' }, cue: { id: '5 menit', en: '5 minutes' }, answer: 'ごふん' },
      { type: 'write', prompt: { id: 'Tuliskan waktu ini dalam bahasa Jepang.', en: 'Write this time in Japanese.' }, cue: { id: '10 menit', en: '10 minutes' }, answer: 'じゅっぷん' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'hari Minggu', en: 'Sunday' }, answer: 'にちようび' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'hari Senin', en: 'Monday' }, answer: 'げつようび' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'hari Sabtu', en: 'Saturday' }, answer: 'どようび' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'kemarin', en: 'yesterday' }, answer: 'きのう' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'besok', en: 'tomorrow' }, answer: 'あした' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'setiap hari', en: 'every day' }, answer: 'まいにち' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'hari ini', en: 'today' }, answer: 'きょう' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'bangun', en: 'get up' }, answer: 'おきます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'tidur', en: 'sleep' }, answer: 'ねます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'makan', en: 'eat' }, answer: 'たべます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'pergi', en: 'go' }, answer: 'いきます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'pulang', en: 'return' }, answer: 'かえります' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'belajar', en: 'study' }, answer: 'べんきょうします' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'bekerja', en: 'work' }, answer: 'はたらきます' },
    ],
  },
  {
    id: 5,
    title: { id: 'Tanggal, Ulang Tahun & Bepergian', en: 'Dates, Birthday & Going Places' },
    subtitle: { id: '日付・誕生日・～へ行きます ほか', en: 'Dates, birthday, going places etc.' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan tanggal/bulan ini dalam bahasa Jepang.', en: 'Write this date/month in Japanese.' }, cue: { id: 'tanggal 1', en: 'the 1st' }, answer: 'ついたち' },
      { type: 'write', prompt: { id: 'Tuliskan tanggal/bulan ini dalam bahasa Jepang.', en: 'Write this date/month in Japanese.' }, cue: { id: 'tanggal 2', en: 'the 2nd' }, answer: 'ふつか' },
      { type: 'write', prompt: { id: 'Tuliskan tanggal/bulan ini dalam bahasa Jepang.', en: 'Write this date/month in Japanese.' }, cue: { id: 'tanggal 3', en: 'the 3rd' }, answer: 'みっか' },
      { type: 'write', prompt: { id: 'Tuliskan tanggal/bulan ini dalam bahasa Jepang.', en: 'Write this date/month in Japanese.' }, cue: { id: 'bulan Januari', en: 'January' }, answer: 'いちがつ' },
      { type: 'write', prompt: { id: 'Tuliskan tanggal/bulan ini dalam bahasa Jepang.', en: 'Write this date/month in Japanese.' }, cue: { id: 'bulan April', en: 'April' }, answer: 'しがつ' },
      { type: 'write', prompt: { id: 'Tuliskan tanggal/bulan ini dalam bahasa Jepang.', en: 'Write this date/month in Japanese.' }, cue: { id: 'bulan Juni', en: 'June' }, answer: 'ろくがつ' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'hari ulang tahun', en: 'birthday' }, answer: 'たんじょうび' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'hadiah', en: 'present' }, answer: 'プレゼント' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'selamat!', en: 'congratulations!' }, answer: 'おめでとう' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'kereta listrik', en: 'train' }, answer: 'でんしゃ' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'pesawat', en: 'airplane' }, answer: 'ひこうき' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'bis', en: 'bus' }, answer: 'バス' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'sepeda', en: 'bicycle' }, answer: 'じてんしゃ' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'jalan kaki', en: 'on foot' }, answer: 'あるいて' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'sendirian', en: 'alone' }, answer: 'ひとりで' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'keluarga', en: 'family' }, answer: 'かぞく' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'teman', en: 'friend' }, answer: 'ともだち' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'sekolah', en: 'school' }, answer: 'がっこう' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'pergi', en: 'go' }, answer: 'いきます' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'datang', en: 'come' }, answer: 'きます' },
    ],
  },
  {
    id: 6,
    title: { id: 'Kegiatan Sehari-hari & Kata Kerja', en: 'Daily Activities & Verbs' },
    subtitle: { id: '～を～ます・[場所]で～ます・[時刻]に～ます', en: 'Object + verb, place で, time に' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'sarapan', en: 'breakfast' }, answer: 'あさごはん' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'makan malam', en: 'dinner' }, answer: 'ばんごはん' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'surat', en: 'letter' }, answer: 'てがみ' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'foto', en: 'photo' }, answer: 'しゃしん' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'radio', en: 'radio' }, answer: 'ラジオ' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'roti', en: 'bread' }, answer: 'パン' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'televisi', en: 'television' }, answer: 'テレビ' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'teh', en: 'tea' }, answer: 'おちゃ' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'koran', en: 'newspaper' }, answer: 'しんぶん' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'ikan', en: 'fish' }, answer: 'さかな' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'rumah', en: 'house' }, answer: 'うち' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'sekolah', en: 'school' }, answer: 'がっこう' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'belajar', en: 'study' }, answer: 'べんきょうします' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'makan', en: 'eat' }, answer: 'たべます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'pergi', en: 'go' }, answer: 'いきます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'minum', en: 'drink' }, answer: 'のみます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'mengambil (foto)', en: 'take (a photo)' }, answer: 'とります' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'menonton / melihat', en: 'watch / see' }, answer: 'みます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'pulang', en: 'return home' }, answer: 'かえります' },
      { type: 'write', prompt: { id: 'Tuliskan nama olahraga ini dalam bahasa Jepang.', en: 'Write this sport in Japanese.' }, cue: { id: 'tenis', en: 'tennis' }, answer: 'テニス' },
    ],
  },
  {
    id: 7,
    title: { id: 'Alat, Bahasa & Pinjam-Meminjam', en: 'Tools, Language & Lending/Borrowing' },
    subtitle: { id: '～で～ます・～語で～です・かします／かります／おしえます／ならいます', en: 'Tool で, language で, lend/borrow/teach/learn' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan nama alat ini dalam bahasa Jepang.', en: 'Write the Japanese name of this tool.' }, cue: { id: 'sumpit', en: 'chopsticks' }, answer: 'はし' },
      { type: 'write', prompt: { id: 'Tuliskan nama alat ini dalam bahasa Jepang.', en: 'Write the Japanese name of this tool.' }, cue: { id: 'gunting', en: 'scissors' }, answer: 'はさみ' },
      { type: 'write', prompt: { id: 'Tuliskan nama alat ini dalam bahasa Jepang.', en: 'Write the Japanese name of this tool.' }, cue: { id: 'pisau', en: 'knife' }, answer: 'ナイフ' },
      { type: 'write', prompt: { id: 'Tuliskan nama transportasi ini dalam bahasa Jepang.', en: 'Write this transportation in Japanese.' }, cue: { id: 'bis', en: 'bus' }, answer: 'バス' },
      { type: 'write', prompt: { id: 'Tuliskan nama transportasi ini dalam bahasa Jepang.', en: 'Write this transportation in Japanese.' }, cue: { id: 'kereta listrik', en: 'train' }, answer: 'でんしゃ' },
      { type: 'write', prompt: { id: 'Tuliskan nama transportasi ini dalam bahasa Jepang.', en: 'Write this transportation in Japanese.' }, cue: { id: 'sepeda', en: 'bicycle' }, answer: 'じてんしゃ' },
      { type: 'write', prompt: { id: 'Tuliskan nama bahasa ini dalam bahasa Jepang.', en: 'Write this language name in Japanese.' }, cue: { id: 'bahasa Jepang', en: 'Japanese language' }, answer: 'にほんご' },
      { type: 'write', prompt: { id: 'Tuliskan nama bahasa ini dalam bahasa Jepang.', en: 'Write this language name in Japanese.' }, cue: { id: 'bahasa Inggris', en: 'English language' }, answer: 'えいご' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'bunga', en: 'flower' }, answer: 'はな' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'meminjamkan', en: 'lend' }, answer: 'かします' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'meminjam', en: 'borrow' }, answer: 'かります' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'mengajar', en: 'teach' }, answer: 'おしえます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'belajar (dari orang)', en: 'learn (from someone)' }, answer: 'ならいます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'memberi', en: 'give' }, answer: 'あげます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'menerima', en: 'receive' }, answer: 'もらいます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'mengirim', en: 'send' }, answer: 'おくります' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'menelepon', en: 'make a phone call' }, answer: 'かけます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'selesai', en: 'finish' }, answer: 'おわります' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'kantor pos', en: 'post office' }, answer: 'ゆうびんきょく' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'bank', en: 'bank' }, answer: 'ぎんこう' },
    ],
  },
  {
    id: 8,
    title: { id: 'Kata Sifat な dan い', en: 'な-Adjectives & い-Adjectives' },
    subtitle: { id: 'なけいようし・いけいようし・けいようしの ひていけい', en: 'na-adjectives, i-adjectives, negative forms' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan kata sifat (な) ini dalam bahasa Jepang.', en: 'Write this na-adjective in Japanese.' }, cue: { id: 'praktis', en: 'convenient' }, answer: 'べんり' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat (な) ini dalam bahasa Jepang.', en: 'Write this na-adjective in Japanese.' }, cue: { id: 'sehat / bersemangat', en: 'healthy / energetic' }, answer: 'げんき' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat (な) ini dalam bahasa Jepang.', en: 'Write this na-adjective in Japanese.' }, cue: { id: 'terkenal', en: 'famous' }, answer: 'ゆうめい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat (な) ini dalam bahasa Jepang.', en: 'Write this na-adjective in Japanese.' }, cue: { id: 'senggang', en: 'free (time)' }, answer: 'ひま' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat (な) ini dalam bahasa Jepang.', en: 'Write this na-adjective in Japanese.' }, cue: { id: 'cantik / bersih', en: 'pretty / clean' }, answer: 'きれい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat (な) ini dalam bahasa Jepang.', en: 'Write this na-adjective in Japanese.' }, cue: { id: 'tenang', en: 'quiet' }, answer: 'しずか' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat (な) ini dalam bahasa Jepang.', en: 'Write this na-adjective in Japanese.' }, cue: { id: 'ramah', en: 'kind' }, answer: 'しんせつ' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat (な) ini dalam bahasa Jepang.', en: 'Write this na-adjective in Japanese.' }, cue: { id: 'ramai', en: 'lively' }, answer: 'にぎやか' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat (な) ini dalam bahasa Jepang.', en: 'Write this na-adjective in Japanese.' }, cue: { id: 'tampan', en: 'handsome' }, answer: 'ハンサム' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat (い) ini dalam bahasa Jepang.', en: 'Write this i-adjective in Japanese.' }, cue: { id: 'besar', en: 'big' }, answer: 'おおきい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat (い) ini dalam bahasa Jepang.', en: 'Write this i-adjective in Japanese.' }, cue: { id: 'mahal / tinggi', en: 'expensive / tall' }, answer: 'たかい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat (い) ini dalam bahasa Jepang.', en: 'Write this i-adjective in Japanese.' }, cue: { id: 'murah', en: 'cheap' }, answer: 'やすい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat (い) ini dalam bahasa Jepang.', en: 'Write this i-adjective in Japanese.' }, cue: { id: 'enak', en: 'delicious' }, answer: 'おいしい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat (い) ini dalam bahasa Jepang.', en: 'Write this i-adjective in Japanese.' }, cue: { id: 'menarik', en: 'interesting' }, answer: 'おもしろい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat (い) ini dalam bahasa Jepang.', en: 'Write this i-adjective in Japanese.' }, cue: { id: 'dingin (cuaca)', en: 'cold (weather)' }, answer: 'さむい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat (い) ini dalam bahasa Jepang.', en: 'Write this i-adjective in Japanese.' }, cue: { id: 'panas', en: 'hot' }, answer: 'あつい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat (い) ini dalam bahasa Jepang.', en: 'Write this i-adjective in Japanese.' }, cue: { id: 'baru', en: 'new' }, answer: 'あたらしい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat (い) ini dalam bahasa Jepang.', en: 'Write this i-adjective in Japanese.' }, cue: { id: 'sibuk', en: 'busy' }, answer: 'いそがしい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat (い) ini dalam bahasa Jepang.', en: 'Write this i-adjective in Japanese.' }, cue: { id: 'kecil', en: 'small' }, answer: 'ちいさい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat (い) ini dalam bahasa Jepang.', en: 'Write this i-adjective in Japanese.' }, cue: { id: 'bagus', en: 'good' }, answer: 'いい' },
    ],
  },
  {
    id: 9,
    title: { id: 'Suka, Tidak Suka & Kata Keterangan', en: 'Likes, Dislikes & Adverbs' },
    subtitle: { id: 'すきです・きらいです・じょうずです・ふくしの せいり', en: 'Like / dislike / good at, adverbs' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan kata keterangan ini dalam bahasa Jepang.', en: 'Write this adverb in Japanese.' }, cue: { id: 'sering', en: 'often' }, answer: 'よく' },
      { type: 'write', prompt: { id: 'Tuliskan kata keterangan ini dalam bahasa Jepang.', en: 'Write this adverb in Japanese.' }, cue: { id: 'sangat', en: 'very' }, answer: 'とても' },
      { type: 'write', prompt: { id: 'Tuliskan kata keterangan ini dalam bahasa Jepang.', en: 'Write this adverb in Japanese.' }, cue: { id: 'tidak terlalu', en: 'not much' }, answer: 'あまり' },
      { type: 'write', prompt: { id: 'Tuliskan kata keterangan ini dalam bahasa Jepang.', en: 'Write this adverb in Japanese.' }, cue: { id: 'banyak', en: 'a lot' }, answer: 'たくさん' },
      { type: 'write', prompt: { id: 'Tuliskan kata keterangan ini dalam bahasa Jepang.', en: 'Write this adverb in Japanese.' }, cue: { id: 'sama sekali tidak', en: 'not at all' }, answer: 'ぜんぜん' },
      { type: 'write', prompt: { id: 'Tuliskan kata keterangan ini dalam bahasa Jepang.', en: 'Write this adverb in Japanese.' }, cue: { id: 'sedikit', en: 'a little' }, answer: 'すこし' },
      { type: 'write', prompt: { id: 'Tuliskan kata keterangan ini dalam bahasa Jepang.', en: 'Write this adverb in Japanese.' }, cue: { id: 'kira-kira / hampir semua', en: 'mostly / roughly' }, answer: 'だいたい' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'rokok', en: 'cigarette' }, answer: 'たばこ' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'tidak mahir', en: 'unskilled' }, answer: 'へた' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'waktu', en: 'time' }, answer: 'じかん' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'urusan / keperluan', en: 'errand / business' }, answer: 'ようじ' },
      { type: 'write', prompt: { id: 'Tuliskan nama minuman ini dalam bahasa Jepang.', en: 'Write this drink in Japanese.' }, cue: { id: 'teh hitam', en: 'black tea' }, answer: 'こうちゃ' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'uang', en: 'money' }, answer: 'おかね' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'komputer pribadi', en: 'personal computer' }, answer: 'パソコン' },
      { type: 'write', prompt: { id: 'Tuliskan nama olahraga ini dalam bahasa Jepang.', en: 'Write this sport in Japanese.' }, cue: { id: 'bisbol', en: 'baseball' }, answer: 'やきゅう' },
      { type: 'write', prompt: { id: 'Tuliskan nama makanan ini dalam bahasa Jepang.', en: 'Write this food in Japanese.' }, cue: { id: 'daging', en: 'meat' }, answer: 'にく' },
      { type: 'write', prompt: { id: 'Tuliskan nama makanan ini dalam bahasa Jepang.', en: 'Write this food in Japanese.' }, cue: { id: 'sayur', en: 'vegetables' }, answer: 'やさい' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'suka', en: 'to like' }, answer: 'すきです' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'tidak suka', en: 'to dislike' }, answer: 'きらいです' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'mahir / pandai', en: 'to be good at' }, answer: 'じょうずです' },
    ],
  },
  {
    id: 10,
    title: { id: 'Ada Benda, Ada Orang & Posisi', en: 'Existence & Position Words' },
    subtitle: { id: 'あります・います・いちしょくご', en: 'あります / います and position words (位置詞)' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan nama binatang ini dalam bahasa Jepang.', en: 'Write this animal in Japanese.' }, cue: { id: 'anjing', en: 'dog' }, answer: 'いぬ' },
      { type: 'write', prompt: { id: 'Tuliskan nama binatang ini dalam bahasa Jepang.', en: 'Write this animal in Japanese.' }, cue: { id: 'kucing', en: 'cat' }, answer: 'ねこ' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'stasiun', en: 'station' }, answer: 'えき' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'orang', en: 'person' }, answer: 'ひと' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'telepon', en: 'telephone' }, answer: 'でんわ' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'anak', en: 'child' }, answer: 'こども' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'baterai', en: 'battery' }, answer: 'でんち' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'jam', en: 'clock' }, answer: 'とけい' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'anak laki-laki', en: 'boy' }, answer: 'おとこのこ' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'pohon', en: 'tree' }, answer: 'き' },
      { type: 'write', prompt: { id: 'Tuliskan kata posisi ini dalam bahasa Jepang.', en: 'Write this position word in Japanese.' }, cue: { id: 'atas', en: 'above / on top' }, answer: 'うえ' },
      { type: 'write', prompt: { id: 'Tuliskan kata posisi ini dalam bahasa Jepang.', en: 'Write this position word in Japanese.' }, cue: { id: 'bawah', en: 'below / under' }, answer: 'した' },
      { type: 'write', prompt: { id: 'Tuliskan kata posisi ini dalam bahasa Jepang.', en: 'Write this position word in Japanese.' }, cue: { id: 'depan', en: 'front' }, answer: 'まえ' },
      { type: 'write', prompt: { id: 'Tuliskan kata posisi ini dalam bahasa Jepang.', en: 'Write this position word in Japanese.' }, cue: { id: 'belakang', en: 'behind' }, answer: 'うしろ' },
      { type: 'write', prompt: { id: 'Tuliskan kata posisi ini dalam bahasa Jepang.', en: 'Write this position word in Japanese.' }, cue: { id: 'dalam', en: 'inside' }, answer: 'なか' },
      { type: 'write', prompt: { id: 'Tuliskan kata posisi ini dalam bahasa Jepang.', en: 'Write this position word in Japanese.' }, cue: { id: 'luar', en: 'outside' }, answer: 'そと' },
      { type: 'write', prompt: { id: 'Tuliskan kata posisi ini dalam bahasa Jepang.', en: 'Write this position word in Japanese.' }, cue: { id: 'kanan', en: 'right' }, answer: 'みぎ' },
      { type: 'write', prompt: { id: 'Tuliskan kata posisi ini dalam bahasa Jepang.', en: 'Write this position word in Japanese.' }, cue: { id: 'kiri', en: 'left' }, answer: 'ひだり' },
      { type: 'write', prompt: { id: 'Tuliskan kata posisi ini dalam bahasa Jepang.', en: 'Write this position word in Japanese.' }, cue: { id: 'antara', en: 'between' }, answer: 'あいだ' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'kulkas', en: 'refrigerator' }, answer: 'れいぞうこ' },
    ],
  },
  {
    id: 11,
    title: { id: 'Kata Bantu Bilangan (Josuushi)', en: 'Counters (Josuushi)' },
    subtitle: { id: '助数詞・～を／が[助数詞]～ます・時間／期間・～に～回', en: 'Counters for objects, people, flat things, machines' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan kata bantu bilangan ini dalam bahasa Jepang.', en: 'Write this counter word in Japanese.' }, cue: { id: 'satu (benda umum)', en: 'one (general objects)' }, answer: 'ひとつ' },
      { type: 'write', prompt: { id: 'Tuliskan kata bantu bilangan ini dalam bahasa Jepang.', en: 'Write this counter word in Japanese.' }, cue: { id: 'tujuh (benda umum)', en: 'seven (general objects)' }, answer: 'ななつ' },
      { type: 'write', prompt: { id: 'Tuliskan kata tanya jumlah ini dalam bahasa Jepang.', en: 'Write this quantity question word in Japanese.' }, cue: { id: 'berapa (benda umum)', en: 'how many (general objects)' }, answer: 'いくつ' },
      { type: 'write', prompt: { id: 'Tuliskan kata bantu bilangan ini dalam bahasa Jepang.', en: 'Write this counter word in Japanese.' }, cue: { id: 'tiga orang', en: 'three people' }, answer: 'さんにん' },
      { type: 'write', prompt: { id: 'Tuliskan kata bantu bilangan ini dalam bahasa Jepang.', en: 'Write this counter word in Japanese.' }, cue: { id: 'lima orang', en: 'five people' }, answer: 'ごにん' },
      { type: 'write', prompt: { id: 'Tuliskan kata bantu bilangan ini dalam bahasa Jepang.', en: 'Write this counter word in Japanese.' }, cue: { id: 'enam orang', en: 'six people' }, answer: 'ろくにん' },
      { type: 'write', prompt: { id: 'Tuliskan kata bantu bilangan ini dalam bahasa Jepang.', en: 'Write this counter word in Japanese.' }, cue: { id: 'delapan orang', en: 'eight people' }, answer: 'はちにん' },
      { type: 'write', prompt: { id: 'Tuliskan kata bantu bilangan ini dalam bahasa Jepang.', en: 'Write this counter word in Japanese.' }, cue: { id: 'sepuluh orang', en: 'ten people' }, answer: 'じゅうにん' },
      { type: 'write', prompt: { id: 'Tuliskan kata tanya ini dalam bahasa Jepang.', en: 'Write this question word in Japanese.' }, cue: { id: 'berapa orang', en: 'how many people' }, answer: 'なんにん' },
      { type: 'write', prompt: { id: 'Tuliskan kata bantu bilangan ini dalam bahasa Jepang.', en: 'Write this counter word in Japanese.' }, cue: { id: 'satu lembar (benda tipis)', en: 'one sheet (flat things)' }, answer: 'いちまい' },
      { type: 'write', prompt: { id: 'Tuliskan kata bantu bilangan ini dalam bahasa Jepang.', en: 'Write this counter word in Japanese.' }, cue: { id: 'tiga lembar', en: 'three sheets' }, answer: 'さんまい' },
      { type: 'write', prompt: { id: 'Tuliskan kata bantu bilangan ini dalam bahasa Jepang.', en: 'Write this counter word in Japanese.' }, cue: { id: 'lima lembar', en: 'five sheets' }, answer: 'ごまい' },
      { type: 'write', prompt: { id: 'Tuliskan kata bantu bilangan ini dalam bahasa Jepang.', en: 'Write this counter word in Japanese.' }, cue: { id: 'tujuh lembar', en: 'seven sheets' }, answer: 'ななまい' },
      { type: 'write', prompt: { id: 'Tuliskan kata bantu bilangan ini dalam bahasa Jepang.', en: 'Write this counter word in Japanese.' }, cue: { id: 'sembilan lembar', en: 'nine sheets' }, answer: 'きゅうまい' },
      { type: 'write', prompt: { id: 'Tuliskan kata bantu bilangan ini dalam bahasa Jepang.', en: 'Write this counter word in Japanese.' }, cue: { id: 'sepuluh lembar', en: 'ten sheets' }, answer: 'じゅうまい' },
      { type: 'write', prompt: { id: 'Tuliskan kata bantu bilangan ini dalam bahasa Jepang.', en: 'Write this counter word in Japanese.' }, cue: { id: 'dua unit (mesin)', en: 'two units (machines)' }, answer: 'にだい' },
      { type: 'write', prompt: { id: 'Tuliskan kata bantu bilangan ini dalam bahasa Jepang.', en: 'Write this counter word in Japanese.' }, cue: { id: 'empat unit (mesin)', en: 'four units (machines)' }, answer: 'よんだい' },
      { type: 'write', prompt: { id: 'Tuliskan kata bantu bilangan ini dalam bahasa Jepang.', en: 'Write this counter word in Japanese.' }, cue: { id: 'enam unit (mesin)', en: 'six units (machines)' }, answer: 'ろくだい' },
      { type: 'write', prompt: { id: 'Tuliskan kata bantu bilangan ini dalam bahasa Jepang.', en: 'Write this counter word in Japanese.' }, cue: { id: 'tujuh unit (mesin)', en: 'seven units (machines)' }, answer: 'ななだい' },
      { type: 'write', prompt: { id: 'Tuliskan kata bantu bilangan ini dalam bahasa Jepang.', en: 'Write this counter word in Japanese.' }, cue: { id: 'delapan unit (mesin)', en: 'eight units (machines)' }, answer: 'はちだい' },
    ],
  },
  {
    id: 12,
    title: { id: 'Tinjauan Kata Sifat & Perbandingan', en: 'Adjective Review & Comparison' },
    subtitle: { id: '形容詞の整理・～は～より／いちばん', en: 'Adjective review, comparison with より／いちばん' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'tampan', en: 'handsome' }, answer: 'ハンサム' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'kecil', en: 'small' }, answer: 'ちいさい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'cantik / bersih', en: 'pretty / clean' }, answer: 'きれい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'panas', en: 'hot' }, answer: 'あつい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'mahal / tinggi', en: 'expensive / tall' }, answer: 'たかい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'sehat / bersemangat', en: 'healthy / energetic' }, answer: 'げんき' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'terkenal', en: 'famous' }, answer: 'ゆうめい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'sulit', en: 'difficult' }, answer: 'むずかしい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'mahir', en: 'skilled' }, answer: 'じょうず' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'tidak mahir', en: 'unskilled' }, answer: 'へた' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'bagus', en: 'good' }, answer: 'いい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'cepat', en: 'fast' }, answer: 'はやい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'ramah', en: 'kind' }, answer: 'しんせつ' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'indah / bagus sekali', en: 'lovely' }, answer: 'すてき' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'enak', en: 'delicious' }, answer: 'おいしい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'senggang', en: 'free (time)' }, answer: 'ひま' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'praktis', en: 'convenient' }, answer: 'べんり' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'dekat', en: 'close / near' }, answer: 'ちかい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'ramai', en: 'lively' }, answer: 'にぎやか' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'tidak suka', en: 'dislike' }, answer: 'きらい' },
    ],
  },
  {
    id: 13,
    title: { id: 'Ingin & Ajakan Bepergian', en: 'Wants & Going Somewhere' },
    subtitle: { id: '～が欲しいです・～たい／～たくない・場所へ～に行きます・助詞「に」の整理', en: 'Wanting things, wanting to do, going somewhere, particle に' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'berenang', en: 'swim' }, answer: 'およぎます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'bermain', en: 'play' }, answer: 'あそびます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'belanja', en: 'go shopping' }, answer: 'かいものします' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'menjadi lelah (lampau)', en: 'got tired (past)' }, answer: 'つかれました' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'menjemput', en: 'go pick up' }, answer: 'むかえます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'mengerti', en: 'understand' }, answer: 'わかります' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'menghabiskan (waktu/biaya)', en: 'take / cost (time or money)' }, answer: 'かかります' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'taman', en: 'park' }, answer: 'こうえん' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'laut', en: 'sea' }, answer: 'うみ' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'kedai kopi', en: 'coffee shop' }, answer: 'きっさてん' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'restoran', en: 'restaurant' }, answer: 'レストラン' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'toko bunga', en: 'flower shop' }, answer: 'はなや' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'rumah sakit', en: 'hospital' }, answer: 'びょういん' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'satu minggu', en: 'one week' }, answer: 'しゅうかん' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'akhir pekan', en: 'weekend' }, answer: 'しゅうまつ' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'seni', en: 'art' }, answer: 'びじゅつ' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'jendela', en: 'window' }, answer: 'まど' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'AC / pendingin ruangan', en: 'air conditioner' }, answer: 'エアコン' },
      { type: 'write', prompt: { id: 'Tuliskan nama minuman ini dalam bahasa Jepang.', en: 'Write this drink in Japanese.' }, cue: { id: 'sake / arak', en: 'sake (rice wine)' }, answer: 'おさけ' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'urusan / keperluan libur', en: 'day off' }, answer: 'やすみです' },
    ],
  },
  {
    id: 14,
    title: { id: 'Kelompok Kata Kerja & Bentuk Te', en: 'Verb Groups & Te-form' },
    subtitle: { id: '動詞グループ分け・て形の作り方・て形の練習', en: 'Verb groups I/II/III and the て-form' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'menulis', en: 'write' }, answer: 'かきます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'mendengar / bertanya', en: 'listen / ask' }, answer: 'ききます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'bekerja', en: 'work' }, answer: 'はたらきます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'terburu-buru', en: 'hurry' }, answer: 'いそぎます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'membaca', en: 'read' }, answer: 'よみます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'beristirahat / libur', en: 'rest / take a day off' }, answer: 'やすみます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'memotong', en: 'cut' }, answer: 'きります' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'menunggu', en: 'wait' }, answer: 'まちます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'memegang / memiliki', en: 'hold / have' }, answer: 'もちます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'bertemu', en: 'meet' }, answer: 'あいます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'berbicara', en: 'speak' }, answer: 'はなします' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'keluar', en: 'go out / exit' }, answer: 'でます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'membuka', en: 'open' }, answer: 'あけます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'menutup', en: 'close' }, answer: 'しめます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'menunjukkan', en: 'show' }, answer: 'みせます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'berhenti / parkir', en: 'stop / park' }, answer: 'とめます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'menikah', en: 'get married' }, answer: 'けっこんします' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'makan (formal)', en: 'have a meal' }, answer: 'しょくじします' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'jalan-jalan', en: 'take a walk' }, answer: 'さんぽします' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'duduk', en: 'sit' }, answer: 'すわります' },
    ],
  },
  {
    id: 15,
    title: { id: 'Meminta Izin & Cerita Keluarga', en: 'Requests, Permission & Family' },
    subtitle: { id: '～てください・～てもいいですか・～てはいけません・～ています・わたしの家族', en: 'Requests, permission, prohibition, ongoing states, family' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'barang bawaan', en: 'luggage / baggage' }, answer: 'にもつ' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'hujan', en: 'rain' }, answer: 'あめ' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'huruf hiragana', en: 'hiragana' }, answer: 'ひらがな' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'katalog', en: 'catalog' }, answer: 'カタログ' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'peta', en: 'map' }, answer: 'ちず' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'saudara kandung', en: 'siblings' }, answer: 'きょうだい' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'ayah', en: 'father' }, answer: 'ちち' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'memakai / menggunakan', en: 'use' }, answer: 'つかいます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'menghapus', en: 'erase' }, answer: 'けします' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'tinggal / bermukim', en: 'live / reside' }, answer: 'すみます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'meminta (tolong)', en: 'ask (a favor)' }, answer: 'たのみます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'memberi instruksi', en: 'instruct' }, answer: 'しじします' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'menawarkan / merekomendasikan', en: 'offer / recommend' }, answer: 'すすめます' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'silakan', en: 'please (go ahead)' }, answer: 'どうぞ' },
      { type: 'write', prompt: { id: 'Tuliskan ucapan salam ini dalam bahasa Jepang.', en: 'Write this greeting in Japanese.' }, cue: { id: 'terima kasih (formal)', en: 'thank you (formal)' }, answer: 'ありがとうございます' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'nomor telepon', en: 'phone number' }, answer: 'でんわばんごう' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'bulan lalu', en: 'last month' }, answer: 'せんげつ' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'perangkat lunak', en: 'software' }, answer: 'ソフト' },
      { type: 'write', prompt: { id: 'Tuliskan nama kota ini dalam bahasa Jepang.', en: 'Write this city name in Japanese.' }, cue: { id: 'Osaka', en: 'Osaka' }, answer: 'おおさか' },
      { type: 'write', prompt: { id: 'Tuliskan nama kota ini dalam bahasa Jepang.', en: 'Write this city name in Japanese.' }, cue: { id: 'Bandung', en: 'Bandung' }, answer: 'バンドン' },
    ],
  },
  {
    id: 16,
    title: { id: 'Bentuk Te Lanjutan & Rute Transportasi', en: 'More Te-form & Transport Routes' },
    subtitle: { id: '[あ・い・お・か・き・し]で始まる動詞のて形・～て、～て、～ます・交通の経路・～てから・～は～が', en: 'Te-form practice, travel routes, ～てから, ～は～が' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'mandi / berendam', en: 'take a shower / bathe' }, answer: 'あびます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'berjalan kaki', en: 'walk' }, answer: 'あるきます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'mengatakan', en: 'say' }, answer: 'いいます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'ada / berada (makhluk hidup)', en: 'be / exist (living things)' }, answer: 'います' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'memasukkan', en: 'put in' }, answer: 'いれます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'berhati-hati', en: 'be careful' }, answer: 'きをつけます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'memperbaiki', en: 'repair' }, answer: 'しゅうりします' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'turun (dari kendaraan)', en: 'get off (a vehicle)' }, answer: 'おります' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'naik (kendaraan)', en: 'board / ride' }, answer: 'のります' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'kereta bawah tanah', en: 'subway' }, answer: 'ちかてつ' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'universitas', en: 'university' }, answer: 'だいがく' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'kota / kampung', en: 'town' }, answer: 'まち' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'nama', en: 'name' }, answer: 'なまえ' },
      { type: 'write', prompt: { id: 'Tuliskan nama binatang ini dalam bahasa Jepang.', en: 'Write this animal in Japanese.' }, cue: { id: 'kelinci', en: 'rabbit' }, answer: 'うさぎ' },
      { type: 'write', prompt: { id: 'Tuliskan nama binatang ini dalam bahasa Jepang.', en: 'Write this animal in Japanese.' }, cue: { id: 'ular', en: 'snake' }, answer: 'へび' },
      { type: 'write', prompt: { id: 'Tuliskan nama binatang ini dalam bahasa Jepang.', en: 'Write this animal in Japanese.' }, cue: { id: 'gajah', en: 'elephant' }, answer: 'ぞう' },
      { type: 'write', prompt: { id: 'Tuliskan bagian tubuh ini dalam bahasa Jepang.', en: 'Write this body part in Japanese.' }, cue: { id: 'telinga', en: 'ear' }, answer: 'みみ' },
      { type: 'write', prompt: { id: 'Tuliskan bagian tubuh ini dalam bahasa Jepang.', en: 'Write this body part in Japanese.' }, cue: { id: 'rambut', en: 'hair' }, answer: 'かみ' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'panjang', en: 'long' }, answer: 'ながい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'cerah / ceria', en: 'bright / cheerful' }, answer: 'あかるい' },
    ],
  },
  {
    id: 17,
    title: { id: 'Bentuk Nai & Ungkapan Sakit', en: 'Nai-form & Illness Expressions' },
    subtitle: { id: 'ない形の作り方・～ないでください・～なければなりません／～なくてもいいです・病気の表現', en: 'Nai-form, requests not to, obligation, illness expressions' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'membayar', en: 'pay' }, answer: 'はらいます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'melupakan', en: 'forget' }, answer: 'わすれます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'melepas (sepatu)', en: 'take off (shoes)' }, answer: 'ぬぎます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'menghisap / merokok', en: 'smoke / inhale' }, answer: 'すいます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'membantu', en: 'help' }, answer: 'てつだいます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'mengingat', en: 'remember' }, answer: 'おぼえます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'menghilangkan', en: 'lose (something)' }, answer: 'なくします' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'mengeluarkan / menyerahkan', en: 'take out / submit' }, answer: 'だします' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'membeli', en: 'buy' }, answer: 'かいます' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'bandara', en: 'airport' }, answer: 'くうこう' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'kamar', en: 'room' }, answer: 'へや' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'kamar mandi / bak mandi', en: 'bathroom / bath' }, answer: 'おふろ' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'sakit', en: 'sick / illness' }, answer: 'びょうき' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'flu / pilek', en: 'cold (illness)' }, answer: 'かぜ' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'demam', en: 'fever' }, answer: 'ねつ' },
      { type: 'write', prompt: { id: 'Tuliskan bagian tubuh ini dalam bahasa Jepang.', en: 'Write this body part in Japanese.' }, cue: { id: 'kepala', en: 'head' }, answer: 'あたま' },
      { type: 'write', prompt: { id: 'Tuliskan bagian tubuh ini dalam bahasa Jepang.', en: 'Write this body part in Japanese.' }, cue: { id: 'perut', en: 'stomach' }, answer: 'おなか' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'obat', en: 'medicine' }, answer: 'くすり' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'sepatu', en: 'shoes' }, answer: 'くつ' },
      { type: 'write', prompt: { id: 'Tuliskan nama olahraga ini dalam bahasa Jepang.', en: 'Write this sport in Japanese.' }, cue: { id: 'golf', en: 'golf' }, answer: 'ゴルフ' },
    ],
  },
  {
    id: 18,
    title: { id: 'Bentuk Kamus & Kemampuan', en: 'Dictionary Form & Ability' },
    subtitle: { id: '辞書形の作り方・[名詞／動詞辞書形こと]ができます・辞書形まえに・趣味', en: 'Dictionary form, ability, "before doing", hobbies' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'memainkan (alat musik)', en: 'play (an instrument)' }, answer: 'ひきます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'memulai', en: 'start' }, answer: 'はじめます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'mengumpulkan', en: 'collect' }, answer: 'あつめます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'berdiri', en: 'stand' }, answer: 'たちます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'membuat', en: 'make' }, answer: 'つくります' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'mencuci', en: 'wash' }, answer: 'あらいます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'bernyanyi', en: 'sing' }, answer: 'うたいます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'menukar', en: 'exchange' }, answer: 'かえます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'mengemudi', en: 'drive' }, answer: 'うんてんします' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'memesan (reservasi)', en: 'make a reservation' }, answer: 'よやくします' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'masakan', en: 'cooking' }, answer: 'りょうり' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'mengemudi (kata benda)', en: 'driving (noun)' }, answer: 'うんてん' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'ujian', en: 'exam' }, answer: 'しけん' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'hobi', en: 'hobby' }, answer: 'しゅみ' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'perjalanan / wisata', en: 'travel' }, answer: 'りょこう' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'memancing', en: 'fishing' }, answer: 'つり' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'piano', en: 'piano' }, answer: 'ピアノ' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'tiket', en: 'ticket' }, answer: 'チケット' },
      { type: 'write', prompt: { id: 'Tuliskan nama binatang ini dalam bahasa Jepang.', en: 'Write this animal in Japanese.' }, cue: { id: 'kuda', en: 'horse' }, answer: 'うま' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'uang tunai', en: 'cash' }, answer: 'げんきん' },
    ],
  },
  {
    id: 19,
    title: { id: 'Bentuk Ta & Pengalaman', en: 'Ta-form & Experience' },
    subtitle: { id: '～たことがあります／ありません・～たり、～たり・[い形容詞・な形容詞／名詞]なります', en: 'Past experience, listing actions, becoming' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'hangat', en: 'warm' }, answer: 'あたたかい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'sedikit', en: 'few' }, answer: 'すくない' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'banyak', en: 'many' }, answer: 'おおい' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'museum seni', en: 'art museum' }, answer: 'びじゅつかん' },
      { type: 'write', prompt: { id: 'Tuliskan nama kota ini dalam bahasa Jepang.', en: 'Write this city name in Japanese.' }, cue: { id: 'Hiroshima', en: 'Hiroshima' }, answer: 'ひろしま' },
      { type: 'write', prompt: { id: 'Tuliskan nama gunung/tempat ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'gunung', en: 'mountain' }, answer: 'やま' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'hotel', en: 'hotel' }, answer: 'ホテル' },
      { type: 'write', prompt: { id: 'Tuliskan nama makanan ini dalam bahasa Jepang.', en: 'Write this food in Japanese.' }, cue: { id: 'sushi', en: 'sushi' }, answer: 'すし' },
      { type: 'write', prompt: { id: 'Tuliskan nama bunga ini dalam bahasa Jepang.', en: 'Write this flower in Japanese.' }, cue: { id: 'bunga sakura', en: 'cherry blossom' }, answer: 'さくら' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'sekarang', en: 'now' }, answer: 'いま' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'negara', en: 'country' }, answer: 'くに' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'cucian', en: 'laundry' }, answer: 'せんたく' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'bersih-bersih', en: 'cleaning' }, answer: 'そうじ' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'cuaca', en: 'weather' }, answer: 'てんき' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'festival', en: 'festival' }, answer: 'おまつり' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'satu kali', en: 'once' }, answer: 'いちど' },
      { type: 'write', prompt: { id: 'Tuliskan kata keterangan ini dalam bahasa Jepang.', en: 'Write this adverb in Japanese.' }, cue: { id: 'semakin lama semakin', en: 'gradually' }, answer: 'だんだん' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'membersihkan', en: 'clean' }, answer: 'そうじします' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'mendaki', en: 'climb' }, answer: 'のぼります' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'menginap', en: 'stay overnight' }, answer: 'とまります' },
    ],
  },
  {
    id: 20,
    title: { id: 'Bentuk Biasa (Futsuutai)', en: 'Plain Form (Futsuutai)' },
    subtitle: { id: '普通体［動詞・い形容詞・な形容詞・名詞］・普通体の会話・日記', en: 'Plain-form verbs, adjectives, nouns; casual conversation; diary' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'ringan', en: 'light (weight)' }, answer: 'かるい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'ingin (punya)', en: 'want (something)' }, answer: 'ほしい' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'libur', en: 'day off / holiday' }, answer: 'やすみ' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'Ginza', en: 'Ginza' }, answer: 'ぎんざ' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'huruf kanji', en: 'kanji character' }, answer: 'かんじ' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'sebelah / tetangga', en: 'next door / neighbor' }, answer: 'となり' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'melihat bunga sakura', en: 'cherry blossom viewing' }, answer: 'はなみ' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'perangko', en: 'stamp' }, answer: 'きって' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'buku harian', en: 'diary' }, answer: 'にっき' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'jalan-jalan', en: 'a walk' }, answer: 'さんぽ' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'sore / siang (PM)', en: 'afternoon' }, answer: 'ごご' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'setelah itu', en: 'after that' }, answer: 'あと' },
      { type: 'write', prompt: { id: 'Tuliskan kata sambung ini dalam bahasa Jepang.', en: 'Write this conjunction in Japanese.' }, cue: { id: 'lalu / kemudian', en: 'and then' }, answer: 'それから' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'lajang / belum menikah', en: 'single / unmarried' }, answer: 'どくしん' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'alamat', en: 'address' }, answer: 'じゅうしょ' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'kamus elektronik', en: 'electronic dictionary' }, answer: 'でんしじしょ' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'visa', en: 'visa' }, answer: 'ビザ' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'memerlukan', en: 'need' }, answer: 'いります' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'bisa / mampu', en: 'can do' }, answer: 'できます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang (bentuk biasa).', en: 'Write this verb in Japanese (plain form).' }, cue: { id: 'ada (bentuk biasa dari あります)', en: 'exist (plain form)' }, answer: 'ある' },
    ],
  },
  {
    id: 21,
    title: { id: 'Pendapat & Dugaan', en: 'Opinions & Reports' },
    subtitle: { id: '普通形＋と思います／と言います・副詞の整理（21課まで）', en: 'Plain form + to think/to say; adverb review (up to lesson 21)' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'pendapat', en: 'opinion' }, answer: 'いけん' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'malam ini', en: 'tonight' }, answer: 'こんばん' },
      { type: 'write', prompt: { id: 'Tuliskan nama negara ini dalam bahasa Jepang.', en: 'Write this country name in Japanese.' }, cue: { id: 'Brasil', en: 'Brazil' }, answer: 'ブラジル' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'pertandingan', en: 'match / game' }, answer: 'しあい' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'menang', en: 'win' }, answer: 'かちます' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'humor', en: 'humor' }, answer: 'ユーモア' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'sia-sia', en: 'useless / a waste' }, answer: 'むだ' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'berat / repot', en: 'tough / a big deal' }, answer: 'たいへん' },
      { type: 'write', prompt: { id: 'Tuliskan kata keterangan ini dalam bahasa Jepang.', en: 'Write this adverb in Japanese.' }, cue: { id: 'akhir-akhir ini', en: 'recently' }, answer: 'さいきん' },
      { type: 'write', prompt: { id: 'Tuliskan nama gunung ini dalam bahasa Jepang.', en: 'Write this mountain name in Japanese.' }, cue: { id: 'Gunung Fuji', en: 'Mt. Fuji' }, answer: 'ふじさん' },
      { type: 'write', prompt: { id: 'Tuliskan kata keterangan ini dalam bahasa Jepang.', en: 'Write this adverb in Japanese.' }, cue: { id: 'mungkin', en: 'probably' }, answer: 'たぶん' },
      { type: 'write', prompt: { id: 'Tuliskan kata keterangan ini dalam bahasa Jepang.', en: 'Write this adverb in Japanese.' }, cue: { id: 'benar-benar', en: 'really / truly' }, answer: 'ほんとうに' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'rapat', en: 'meeting' }, answer: 'かいぎ' },
      { type: 'write', prompt: { id: 'Tuliskan kata keterangan ini dalam bahasa Jepang.', en: 'Write this adverb in Japanese.' }, cue: { id: 'pasti (tolong)', en: 'by all means' }, answer: 'ぜひ' },
      { type: 'write', prompt: { id: 'Tuliskan kata keterangan ini dalam bahasa Jepang.', en: 'Write this adverb in Japanese.' }, cue: { id: 'pasti / yakin', en: 'surely' }, answer: 'きっと' },
      { type: 'write', prompt: { id: 'Tuliskan kata keterangan ini dalam bahasa Jepang.', en: 'Write this adverb in Japanese.' }, cue: { id: 'tidak sebegitu', en: 'not that much' }, answer: 'そんなに' },
      { type: 'write', prompt: { id: 'Tuliskan kata keterangan ini dalam bahasa Jepang.', en: 'Write this adverb in Japanese.' }, cue: { id: 'tentu saja', en: 'of course' }, answer: 'もちろん' },
      { type: 'write', prompt: { id: 'Tuliskan kata keterangan ini dalam bahasa Jepang.', en: 'Write this adverb in Japanese.' }, cue: { id: 'pertama kali', en: 'for the first time' }, answer: 'はじめて' },
      { type: 'write', prompt: { id: 'Tuliskan kata keterangan ini dalam bahasa Jepang.', en: 'Write this adverb in Japanese.' }, cue: { id: 'mulai sekarang', en: 'from now on' }, answer: 'これから' },
      { type: 'write', prompt: { id: 'Tuliskan kata keterangan ini dalam bahasa Jepang.', en: 'Write this adverb in Japanese.' }, cue: { id: 'sebentar lagi', en: 'soon / about time' }, answer: 'そろそろ' },
    ],
  },
  {
    id: 22,
    title: { id: 'Klausa Pewatas Kata Benda', en: 'Noun-modifying Clauses' },
    subtitle: { id: '連体修飾［物・人・所］・着ます／はきます／かぶります／かけます', en: 'Noun-modifying clauses; wearing verbs' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'perusahaan', en: 'company' }, answer: 'かいしゃ' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'kursi', en: 'chair' }, answer: 'いす' },
      { type: 'write', prompt: { id: 'Tuliskan nama makanan ini dalam bahasa Jepang.', en: 'Write this food in Japanese.' }, cue: { id: 'kue', en: 'cake' }, answer: 'ケーキ' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'lahir', en: 'be born' }, answer: 'うまれます' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'siswa SMA', en: 'high-school student' }, answer: 'こうこうせい' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'ponsel', en: 'mobile phone' }, answer: 'ケータイ' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'sedang memegang/punya', en: 'is holding / has' }, answer: 'もっています' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'janji', en: 'promise' }, answer: 'やくそく' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'robot', en: 'robot' }, answer: 'ロボット' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'topi', en: 'hat' }, answer: 'ぼうし' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'mantel', en: 'coat' }, answer: 'コート' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'putih', en: 'white' }, answer: 'しろい' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'buku catatan', en: 'notebook' }, answer: 'ノート' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'Istana Osaka', en: 'Osaka Castle' }, answer: 'おおさかじょう' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'memakai (celana/sepatu)', en: 'wear (pants/shoes)' }, answer: 'はきます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'memakai (di kepala)', en: 'wear (on the head)' }, answer: 'かぶります' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'hitam', en: 'black' }, answer: 'くろい' },
      { type: 'write', prompt: { id: 'Tuliskan kata tanya ini dalam bahasa Jepang.', en: 'Write this question word in Japanese.' }, cue: { id: 'seperti apa', en: 'what kind of' }, answer: 'どんな' },
      { type: 'write', prompt: { id: 'Tuliskan kata tanya ini dalam bahasa Jepang.', en: 'Write this question word in Japanese.' }, cue: { id: 'yang mana (+ kata benda)', en: 'which (+ noun)' }, answer: 'どの' },
      { type: 'write', prompt: { id: 'Tuliskan kata tanya ini dalam bahasa Jepang.', en: 'Write this question word in Japanese.' }, cue: { id: 'yang mana (mandiri)', en: 'which one' }, answer: 'どれ' },
    ],
  },
  {
    id: 23,
    title: { id: '～とき & Petunjuk Arah', en: '~Toki & Giving Directions' },
    subtitle: { id: 'るとき／たとき・辞書形と、～（道案内）', en: '~toki (when); dictionary form + to (giving directions)' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'kartu asuransi', en: 'insurance card' }, answer: 'ほけんしょう' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'oleh-oleh', en: 'souvenir' }, answer: 'おみやげ' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'tahun depan', en: 'next year' }, answer: 'らいねん' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'paspor', en: 'passport' }, answer: 'パスポート' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'kedutaan besar', en: 'embassy' }, answer: 'たいしかん' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'jalan', en: 'road' }, answer: 'みち' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'belok', en: 'turn' }, answer: 'まがります' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'menyeberang', en: 'cross' }, answer: 'わたります' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'lampu lalu lintas', en: 'traffic light' }, answer: 'しんごう' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'lurus', en: 'straight ahead' }, answer: 'まっすぐ' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'tempat parkir', en: 'parking lot' }, answer: 'ちゅうしゃじょう' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'toko alat elektronik', en: 'electronics shop' }, answer: 'でんきや' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'supermarket', en: 'supermarket' }, answer: 'スーパー' },
      { type: 'write', prompt: { id: 'Tuliskan nama tempat ini dalam bahasa Jepang.', en: 'Write this place name in Japanese.' }, cue: { id: 'persimpangan', en: 'intersection' }, answer: 'こうさてん' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'sakit (nyeri)', en: 'painful' }, answer: 'いたい' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'menyenangkan', en: 'fun' }, answer: 'たのしい' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'sudut / pojokan', en: 'corner' }, answer: 'かど' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'direktur perusahaan', en: 'company president' }, answer: 'しゃちょう' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'masuk', en: 'enter' }, answer: 'はいります' },
      { type: 'write', prompt: { id: 'Tuliskan kata tanya ini dalam bahasa Jepang.', en: 'Write this question word in Japanese.' }, cue: { id: 'dengan cara apa', en: 'how / by what means' }, answer: 'どうやって' },
    ],
  },
  {
    id: 24,
    title: { id: 'Memberi & Menerima', en: 'Giving & Receiving' },
    subtitle: { id: '～に～をあげました／もらいました／くれました・～てくれました／～てもらいました', en: 'Giving/receiving verbs; te-kuremashita/te-moraimashita' },
    questions: [
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'mesin cuci', en: 'washing machine' }, answer: 'せんたくき' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'mesin penyedot debu', en: 'vacuum cleaner' }, answer: 'そうじき' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'semuanya', en: 'all / entirely' }, answer: 'ぜんぶ' },
      { type: 'write', prompt: { id: 'Tuliskan nama negara ini dalam bahasa Jepang.', en: 'Write this country name in Japanese.' }, cue: { id: 'Prancis', en: 'France' }, answer: 'フランス' },
      { type: 'write', prompt: { id: 'Tuliskan kata keterangan ini dalam bahasa Jepang.', en: 'Write this adverb in Japanese.' }, cue: { id: 'sendiri-sendiri/terpisah', en: 'separately' }, answer: 'べつべつに' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'memberi (kepada saya)', en: 'give (to me)' }, answer: 'くれます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'membawa/mengantar orang', en: 'take someone along' }, answer: 'つれていきます' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'memperkenalkan', en: 'introduce' }, answer: 'しょうかいします' },
      { type: 'write', prompt: { id: 'Tuliskan kata kerja ini dalam bahasa Jepang.', en: 'Write this verb in Japanese.' }, cue: { id: 'memutar', en: 'turn / rotate' }, answer: 'まわします' },
      { type: 'write', prompt: { id: 'Tuliskan nama benda ini dalam bahasa Jepang.', en: 'Write the Japanese name of this object.' }, cue: { id: 'kimono', en: 'kimono' }, answer: 'きもの' },
      { type: 'write', prompt: { id: 'Tuliskan kata sambung ini dalam bahasa Jepang.', en: 'Write this conjunction in Japanese.' }, cue: { id: 'jadi / makanya (santai)', en: 'so (casual)' }, answer: 'だから' },
      { type: 'write', prompt: { id: 'Tuliskan kata sambung ini dalam bahasa Jepang.', en: 'Write this conjunction in Japanese.' }, cue: { id: 'karena itu', en: 'therefore' }, answer: 'ですから' },
      { type: 'write', prompt: { id: 'Tuliskan kata sambung ini dalam bahasa Jepang.', en: 'Write this conjunction in Japanese.' }, cue: { id: 'dan', en: 'and' }, answer: 'そして' },
      { type: 'write', prompt: { id: 'Tuliskan kata sambung ini dalam bahasa Jepang.', en: 'Write this conjunction in Japanese.' }, cue: { id: 'tetapi', en: 'but' }, answer: 'でも' },
      { type: 'write', prompt: { id: 'Tuliskan kata sambung ini dalam bahasa Jepang.', en: 'Write this conjunction in Japanese.' }, cue: { id: 'kalau begitu (santai)', en: 'well then (casual)' }, answer: 'じゃ' },
      { type: 'write', prompt: { id: 'Tuliskan kata sambung ini dalam bahasa Jepang.', en: 'Write this conjunction in Japanese.' }, cue: { id: 'kalau begitu', en: 'well then' }, answer: 'では' },
      { type: 'write', prompt: { id: 'Tuliskan kata sifat ini dalam bahasa Jepang.', en: 'Write this adjective in Japanese.' }, cue: { id: 'mengantuk', en: 'sleepy' }, answer: 'ねむい' },
      { type: 'write', prompt: { id: 'Tuliskan nama kendaraan ini dalam bahasa Jepang.', en: 'Write this vehicle name in Japanese.' }, cue: { id: 'taksi', en: 'taxi' }, answer: 'タクシー' },
      { type: 'write', prompt: { id: 'Tuliskan kata ini dalam bahasa Jepang.', en: 'Write this word in Japanese.' }, cue: { id: 'wajah', en: 'face' }, answer: 'かお' },
      { type: 'write', prompt: { id: 'Tuliskan nama kota ini dalam bahasa Jepang.', en: 'Write this city name in Japanese.' }, cue: { id: 'Kyoto', en: 'Kyoto' }, answer: 'きょうと' },
    ],
  },
  {
    id: 25,
    title: { id: '～たら & ～ても', en: '~Tara & ~Temo' },
    subtitle: { id: '「たら」と「ても」の練習・～たら、～たいです', en: '~tara and ~temo conjugation practice; ~tara, ~tai desu' },
    questions: [
      { type: 'write', prompt: { id: 'Ubah kata kerja ini ke bentuk ～たら.', en: 'Change this verb to the ~tara form.' }, cue: { id: '行きます (pergi)', en: '行きます (go)' }, answer: 'いったら' },
      { type: 'write', prompt: { id: 'Ubah kata kerja ini ke bentuk ～ても.', en: 'Change this verb to the ~temo form.' }, cue: { id: '行きます (pergi)', en: '行きます (go)' }, answer: 'いっても' },
      { type: 'write', prompt: { id: 'Ubah kata kerja ini ke bentuk ～たら.', en: 'Change this verb to the ~tara form.' }, cue: { id: '泳ぎます (berenang)', en: '泳ぎます (swim)' }, answer: 'およいだら' },
      { type: 'write', prompt: { id: 'Ubah kata kerja ini ke bentuk negatif ～なかったら.', en: 'Change this verb to the negative ~nakattara form.' }, cue: { id: '泳ぎます (berenang)', en: '泳ぎます (swim)' }, answer: 'およがなかったら' },
      { type: 'write', prompt: { id: 'Ubah kata kerja ini ke bentuk ～たら.', en: 'Change this verb to the ~tara form.' }, cue: { id: '読みます (membaca)', en: '読みます (read)' }, answer: 'よんだら' },
      { type: 'write', prompt: { id: 'Ubah kata kerja ini ke bentuk ～ても.', en: 'Change this verb to the ~temo form.' }, cue: { id: '読みます (membaca)', en: '読みます (read)' }, answer: 'よんでも' },
      { type: 'write', prompt: { id: 'Ubah kata kerja ini ke bentuk negatif ～なかったら.', en: 'Change this verb to the negative ~nakattara form.' }, cue: { id: '遊びます (bermain)', en: '遊びます (play)' }, answer: 'あそばなかったら' },
      { type: 'write', prompt: { id: 'Ubah kata kerja ini ke bentuk ～ても.', en: 'Change this verb to the ~temo form.' }, cue: { id: '遊びます (bermain)', en: '遊びます (play)' }, answer: 'あそんでも' },
      { type: 'write', prompt: { id: 'Ubah kata kerja ini ke bentuk ～たら.', en: 'Change this verb to the ~tara form.' }, cue: { id: '帰ります (pulang)', en: '帰ります (go home)' }, answer: 'かえったら' },
      { type: 'write', prompt: { id: 'Ubah kata kerja ini ke bentuk ～ても.', en: 'Change this verb to the ~temo form.' }, cue: { id: '帰ります (pulang)', en: '帰ります (go home)' }, answer: 'かえっても' },
      { type: 'write', prompt: { id: 'Ubah kata kerja ini ke bentuk ～たら.', en: 'Change this verb to the ~tara form.' }, cue: { id: 'あります (ada)', en: 'あります (there is/are)' }, answer: 'あったら' },
      { type: 'write', prompt: { id: 'Ubah kata kerja ini ke bentuk ～ても.', en: 'Change this verb to the ~temo form.' }, cue: { id: 'あります (ada)', en: 'あります (there is/are)' }, answer: 'あっても' },
      { type: 'write', prompt: { id: 'Ubah kata kerja ini ke bentuk ～たら.', en: 'Change this verb to the ~tara form.' }, cue: { id: '買います (membeli)', en: '買います (buy)' }, answer: 'かったら' },
      { type: 'write', prompt: { id: 'Ubah kata kerja ini ke bentuk negatif ～なかったら.', en: 'Change this verb to the negative ~nakattara form.' }, cue: { id: '買います (membeli)', en: '買います (buy)' }, answer: 'かわなかったら' },
      { type: 'write', prompt: { id: 'Ubah kata kerja ini ke bentuk negatif ～なかったら.', en: 'Change this verb to the negative ~nakattara form.' }, cue: { id: '待ちます (menunggu)', en: '待ちます (wait)' }, answer: 'またなかったら' },
      { type: 'write', prompt: { id: 'Ubah kata kerja ini ke bentuk ～ても.', en: 'Change this verb to the ~temo form.' }, cue: { id: '待ちます (menunggu)', en: '待ちます (wait)' }, answer: 'まっても' },
      { type: 'write', prompt: { id: 'Ubah kata kerja ini ke bentuk ～たら.', en: 'Change this verb to the ~tara form.' }, cue: { id: '話します (berbicara)', en: '話します (speak)' }, answer: 'はなしたら' },
      { type: 'write', prompt: { id: 'Ubah kata kerja ini ke bentuk negatif ～なかったら.', en: 'Change this verb to the negative ~nakattara form.' }, cue: { id: '話します (berbicara)', en: '話します (speak)' }, answer: 'はなさなかったら' },
      { type: 'write', prompt: { id: 'Ubah kata kerja ini ke bentuk ～たら.', en: 'Change this verb to the ~tara form.' }, cue: { id: '食べます (makan)', en: '食べます (eat)' }, answer: 'たべたら' },
      { type: 'write', prompt: { id: 'Ubah kata kerja ini ke bentuk ～ても.', en: 'Change this verb to the ~temo form.' }, cue: { id: '食べます (makan)', en: '食べます (eat)' }, answer: 'たべても' },
    ],
  },
]

// ---------------------------------------------------------------------
// Progres belajar per bab — disimpan di server lewat GET/POST
// /api/kaite-oboeru/progress (tabel user_kaite_oboeru_progress), sama
// persis polanya dengan Mondaishuu. `crown: true` cuma didapat kalau satu
// bab diselesaikan tanpa satupun jawaban salah (nyawa penuh sampai
// akhir). `done: true` tapi tanpa crown berarti sudah pernah selesai
// tapi masih ada kesalahan — tetap tercatat, cuma belum dapat mahkota.
// ---------------------------------------------------------------------
const progress = reactive({})
const progressLoaded = ref(false)

async function loadProgress() {
  try {
    const res = await $api('/kaite-oboeru/progress')

    Object.assign(progress, res.progress ?? {})
  }
  catch {
    // Kalau gagal (mis. offline), lanjut saja dengan progress kosong —
    // status tiap bab akan tampil 'available', bukan memblokir halaman.
  }
  finally {
    progressLoaded.value = true
  }
}

loadProgress()

function markProgress(key, perfect) {
  const prev = progress[key]

  // Optimistic: UI langsung update, tidak menunggu network round-trip.
  progress[key] = { done: true, crown: perfect || !!prev?.crown }

  // Fire-and-forget ke server — sama seperti recordProgress() di
  // kanji/quiz.vue. Gagal kirim tidak boleh mengganggu alur kuis yang
  // sedang dilihat pengguna.
  $api(`/kaite-oboeru/progress/${encodeURIComponent(key)}`, {
    method: 'POST',
    body: { perfect: !!perfect },
  }).catch(() => {
    // Best-effort saja.
  })
}

// Sama seperti Jalur Belajar/Mondaishuu: bab berikutnya terkunci sampai
// bab sebelumnya diselesaikan (status `done`). Admin bebas dari penguncian
// ini supaya bisa memeriksa/menguji bab mana saja.
function isLocked(key) {
  if (authStore.isAdmin)
    return false

  const n = Number(key.slice(1))

  return n > 1 && !progress[`l${n - 1}`]?.done
}

function statusFor(key) {
  if (isLocked(key))
    return 'locked'

  const p = progress[key]
  if (p?.crown)
    return 'mastered'
  if (p?.done)
    return 'completed'

  return 'available'
}

const activeLesson = ref(null)

const STAGE_SETUP = 'setup'
const STAGE_QUIZ = 'quiz'
const STAGE_RESULT = 'result'
const STAGE_HEARTS_OUT = 'hearts_out'

const stage = ref(STAGE_SETUP)

const STARTING_HEARTS = 5
const hearts = ref(STARTING_HEARTS)
const wrongCount = ref(0)

const qIndex = ref(0)
const score = ref(0)
const answered = ref(false)
const isCorrect = ref(false)

const shuffledQuestions = ref([])
const currentQuestion = computed(() => shuffledQuestions.value?.[qIndex.value] ?? null)
const totalQuestions = computed(() => shuffledQuestions.value?.length ?? 0)

// Per-character progress through the current word. `revealed` only ever
// grows with characters the learner has actually finished writing
// correctly — nothing about the answer is shown ahead of time.
const units = ref([])
const unitIndex = ref(0)
const revealed = ref([])
const canvasRef = ref(null)

const progressPercent = computed(() => {
  if (!totalQuestions.value)
    return 0

  return Math.round((qIndex.value / totalQuestions.value) * 100)
})

// Same shape/verdict bar as Mondaishuu: verdict icon+label, plus the
// correct answer (always shown, since it doubles as confirmation when
// right) and the cue's meaning underneath.
const feedback = computed(() => {
  if (!answered.value)
    return null

  const q = currentQuestion.value

  return {
    correct: isCorrect.value,
    correctAnswer: q?.answer ?? null,
    translate: q ? tr(q.cue) : null,
  }
})

function shuffle(arr) {
  const a = [...arr]
  for (let i = a.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[a[i], a[j]] = [a[j], a[i]]
  }
  return a
}

function setupQuestion() {
  answered.value = false
  isCorrect.value = false
  unitIndex.value = 0
  revealed.value = []
  units.value = segmentAnswer(currentQuestion.value?.answer ?? '')
}

const ADVANCE_DELAY_CORRECT = 700
const ADVANCE_DELAY_INCORRECT = 1300
let advanceTimer = null

function clearAdvanceTimer() {
  if (advanceTimer) {
    clearTimeout(advanceTimer)
    advanceTimer = null
  }
}

function scheduleAdvance(correct) {
  if (!autoNext.value)
    return
  clearAdvanceTimer()
  advanceTimer = setTimeout(nextQuestion, correct ? ADVANCE_DELAY_CORRECT : ADVANCE_DELAY_INCORRECT)
}

watch(autoNext, on => {
  if (!on)
    clearAdvanceTimer()
})

function startLesson(lesson) {
  if (isLocked(`l${lesson.id}`))
    return

  clearAdvanceTimer()
  activeLesson.value = lesson
  qIndex.value = 0
  score.value = 0
  hearts.value = STARTING_HEARTS
  wrongCount.value = 0
  shuffledQuestions.value = shuffle(lesson.questions)
  setupQuestion()
  stage.value = STAGE_QUIZ
}

// Fires once per character, and only once it has actually been written
// correctly (KaiteOboekuWritingCanvas never force-passes a wrong stroke).
// So reaching the last character always means the whole word was written
// correctly — the question is simply graded "correct" at that point.
function onCharComplete() {
  if (answered.value)
    return

  revealed.value = [...revealed.value, units.value[unitIndex.value]]

  if (unitIndex.value + 1 < units.value.length) {
    unitIndex.value += 1

    return
  }

  answered.value = true
  isCorrect.value = true
  score.value++
  scheduleAdvance(true)
}

// Nyawa berkurang satu setiap kali sebuah kata dilewati/menyerah — sama
// seperti jawaban salah di Mondaishuu.
function registerWrongAnswer() {
  wrongCount.value++
  hearts.value = Math.max(0, hearts.value - 1)
}

// Escape hatch for a word the learner cannot (or does not want to) finish.
function skipWriting() {
  if (answered.value)
    return
  answered.value = true
  isCorrect.value = false
  registerWrongAnswer()
  scheduleAdvance(false)
}

function nextQuestion() {
  clearAdvanceTimer()

  // Nyawa habis -> berhenti di sini, tidak boleh lanjut ke soal berikutnya
  // ataupun ke layar hasil. Satu-satunya jalan keluar adalah mengulang.
  if (hearts.value <= 0) {
    stage.value = STAGE_HEARTS_OUT

    return
  }

  if (qIndex.value + 1 >= totalQuestions.value) {
    markProgress(`l${activeLesson.value.id}`, wrongCount.value === 0)
    stage.value = STAGE_RESULT

    return
  }
  qIndex.value++
  setupQuestion()
}

function restartQuiz() {
  clearAdvanceTimer()
  qIndex.value = 0
  score.value = 0
  hearts.value = STARTING_HEARTS
  wrongCount.value = 0
  shuffledQuestions.value = shuffle(activeLesson.value?.questions ?? [])
  setupQuestion()
  stage.value = STAGE_QUIZ
}

function backToSetup() {
  clearAdvanceTimer()
  stage.value = STAGE_SETUP
}

onBeforeUnmount(clearAdvanceTimer)
</script>

<template>
  <!-- SETUP: lesson picker, normal layout -->
  <div v-if="stage === STAGE_SETUP">
    <div class="d-flex align-center justify-space-between mb-1 flex-wrap gap-2">
      <h4 class="text-h4 mb-0">
        {{ t('kaite_oboeru.title') }}
      </h4>
    </div>
    <p class="text-body-2 text-medium-emphasis mb-6">
      {{ t('kaite_oboeru.subtitle') }}
    </p>

    <!-- Ditunggu sampai progres selesai dimuat (progressLoaded) sebelum
         kartu dirender sama sekali — sama seperti halaman Kanji — supaya
         mahkota/centang tidak "muncul belakangan" setelah kartu polos
         sempat kelihatan sesaat. -->
    <div
      v-if="!progressLoaded"
      class="d-flex justify-center pa-10"
    >
      <VProgressCircular indeterminate color="primary" />
    </div>

    <div v-else class="ko-grid">
      <button
        v-for="lesson in LESSONS"
        :key="lesson.id"
        type="button"
        class="ko-card"
        :class="`ko-card--${statusFor(`l${lesson.id}`)}`"
        :disabled="statusFor(`l${lesson.id}`) === 'locked'"
        @click="startLesson(lesson)"
      >
        <VIcon
          v-if="statusFor(`l${lesson.id}`) === 'mastered'"
          icon="tabler-crown"
          size="18"
          class="ko-card__crown ko-card__crown--gold"
        />
        <VIcon
          v-else-if="statusFor(`l${lesson.id}`) === 'completed'"
          icon="tabler-circle-check-filled"
          size="16"
          color="success"
          class="ko-card__crown"
        />
        <VIcon
          v-else-if="statusFor(`l${lesson.id}`) === 'locked'"
          icon="tabler-lock"
          size="16"
          class="ko-card__crown"
        />
        <span class="ko-card__badge">{{ lesson.id }}</span>
        <span class="ko-card__title">{{ tr(lesson.title) }}</span>
        <span class="ko-card__subtitle" lang="ja">{{ tr(lesson.subtitle) }}</span>
      </button>
    </div>
  </div>

  <!-- QUIZ / RESULT: distraction-free full-screen shell, same as Kana/Kanji/Mondaishuu -->
  <div v-else class="kana-fs kana-fs--page">
    <header class="kana-fs__header">
      <VBtn
        icon="tabler-x"
        variant="text"
        color="secondary"
        :aria-label="t('kaite_oboeru.quiz_back')"
        @click="backToSetup"
      />

      <div
        v-if="stage === STAGE_QUIZ"
        class="lp__bar"
        role="progressbar"
        :aria-valuenow="progressPercent"
        aria-valuemin="0"
        aria-valuemax="100"
      >
        <div class="lp__fill" :style="{ inlineSize: `${progressPercent}%` }" />
      </div>
      <h5 v-else class="text-h6 mb-0">
        {{ t('kaite_oboeru.title') }}
      </h5>

      <div
        v-if="stage === STAGE_QUIZ"
        class="lp__hearts"
        :aria-label="t('kaite_oboeru.hearts')"
      >
        <VIcon icon="tabler-heart-filled" />
        {{ hearts }}
      </div>

      <VSwitch
        v-if="stage === STAGE_QUIZ"
        v-model="autoNext"
        :label="t('kana.auto_next')"
        color="primary"
        density="compact"
        hide-details
        class="flex-shrink-0"
      />
    </header>

    <main class="kana-fs__body">
      <div class="kana-fs__content">
        <!-- QUIZ -->
        <div
          v-if="stage === STAGE_QUIZ && currentQuestion"
          class="mx-auto"
          style="max-inline-size: 560px;"
        >
          <div class="d-flex align-center justify-space-between mb-2">
            <div class="text-caption text-medium-emphasis">
              {{ tr(activeLesson.title) }}
            </div>
            <VChip size="small" variant="tonal" color="primary">
              {{ t('kaite_oboeru.score', { score, total: totalQuestions }) }}
            </VChip>
          </div>

          <div class="text-caption text-medium-emphasis mb-4">
            {{ t('kaite_oboeru.question_of', { n: qIndex + 1, total: totalQuestions }) }}
          </div>

          <VCard class="pa-6 text-center">
            <p class="text-body-2 text-medium-emphasis mb-1">
              {{ tr(currentQuestion.prompt) }}
            </p>
            <p class="text-h6 mb-4">
              {{ tr(currentQuestion.cue) }}
            </p>

            <!-- Only characters already written correctly are shown — the
                 rest of the word is just dots, so nothing here hints at the
                 answer ahead of time. -->
            <div class="ko-word mb-4">
              <span
                v-for="(u, ui) in units"
                :key="ui"
                class="ko-word__char"
                :class="{ 'ko-word__char--active': ui === unitIndex && !answered }"
              >
                <template v-if="ui < revealed.length">{{ revealed[ui] }}</template>
                <template v-else>・</template>
              </span>
            </div>

            <div class="d-flex justify-center">
              <KaiteOboekuWritingCanvas
                :key="`${qIndex}-${unitIndex}`"
                ref="canvasRef"
                :character="units[unitIndex]"
                :size="200"
                @complete="onCharComplete"
              />
            </div>

            <VBtn
              v-if="!answered"
              variant="text"
              size="small"
              class="mt-2"
              @click="skipWriting"
            >
              {{ t('kaite_oboeru.skip') }}
            </VBtn>
          </VCard>
        </div>

        <!-- RESULT -->
        <VCard
          v-else-if="stage === STAGE_RESULT"
          class="pa-6 text-center mx-auto"
          max-width="480"
        >
          <div class="text-h3 mb-2">
            {{ wrongCount === 0 ? '👑' : '✍️' }}
          </div>
          <h5 class="text-h5 mb-1">
            {{ t('kaite_oboeru.done_title') }}
          </h5>
          <p class="text-h6 mb-2">
            {{ t('kaite_oboeru.score', { score, total: totalQuestions }) }}
          </p>

          <div
            v-if="wrongCount === 0"
            class="ko-crown-earned mb-6"
          >
            <VIcon icon="tabler-crown" size="22" />
            {{ t('kaite_oboeru.crown_earned') }}
          </div>
          <p
            v-else
            class="text-body-2 text-medium-emphasis mb-6"
          >
            {{ t('kaite_oboeru.crown_hint') }}
          </p>

          <div class="d-flex flex-wrap gap-2 justify-center">
            <VBtn variant="tonal" @click="backToSetup">
              {{ t('kaite_oboeru.quiz_back') }}
            </VBtn>
            <VBtn variant="tonal" prepend-icon="tabler-refresh" @click="restartQuiz">
              {{ t('kaite_oboeru.retry') }}
            </VBtn>
          </div>
        </VCard>

        <!-- HEARTS OUT: nyawa habis, tidak boleh lanjut, cuma bisa mengulang -->
        <VCard
          v-else-if="stage === STAGE_HEARTS_OUT"
          class="pa-6 text-center mx-auto"
          max-width="480"
        >
          <div class="text-h3 mb-2">
            💔
          </div>
          <h5 class="text-h5 mb-1">
            {{ t('kaite_oboeru.out_of_hearts') }}
          </h5>
          <p class="text-body-2 text-medium-emphasis mb-6">
            {{ t('kaite_oboeru.out_of_hearts_hint') }}
          </p>
          <div class="d-flex flex-wrap gap-2 justify-center">
            <VBtn variant="tonal" @click="backToSetup">
              {{ t('kaite_oboeru.quiz_back') }}
            </VBtn>
            <VBtn color="primary" prepend-icon="tabler-refresh" @click="restartQuiz">
              {{ t('kaite_oboeru.retry') }}
            </VBtn>
          </div>
        </VCard>
      </div>
    </main>

    <footer v-if="feedback && stage === STAGE_QUIZ" class="kana-fs__footer" :class="feedback.correct ? 'is-correct' : 'is-incorrect'">
      <div class="kana-fs__footer-inner">
        <div
          class="lp__verdict"
          :style="{ color: feedback.correct ? 'rgb(var(--v-theme-success))' : 'rgb(var(--v-theme-error))' }"
        >
          <VIcon
            :icon="feedback.correct ? 'tabler-circle-check-filled' : 'tabler-circle-x-filled'"
            size="36"
          />
          <div>
            {{ feedback.correct ? t('kaite_oboeru.correct') : t('kaite_oboeru.incorrect') }}
            <small v-if="!feedback.correct" lang="ja">
              {{ t('kaite_oboeru.correct_answer') }} <strong>{{ feedback.correctAnswer }}</strong>
            </small>
            <small v-else lang="ja">
              <strong>{{ feedback.correctAnswer }}</strong>
            </small>
            <small v-if="feedback.translate" class="d-block" :lang="locale">
              {{ t('kaite_oboeru.meaning') }}: {{ feedback.translate }}
            </small>
          </div>
        </div>

        <VBtn v-if="!autoNext" color="primary" append-icon="tabler-arrow-right" @click="nextQuestion">
          {{ hearts <= 0 ? t('kaite_oboeru.out_of_hearts_cta') : (qIndex + 1 >= totalQuestions ? t('kaite_oboeru.finish') : t('kaite_oboeru.next')) }}
        </VBtn>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* ---------- crown badge on the result screen ---------- */
.ko-crown-earned {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  background: rgba(var(--v-theme-warning), 0.14);
  color: rgb(var(--v-theme-warning));
  font-weight: 600;
  gap: 8px;
  padding-block: 8px;
  padding-inline: 18px;
}

/* ---------- status accent on lesson cards (setup stage) ---------- */
.ko-card::before {
  position: absolute;
  background: transparent;
  block-size: 3px;
  content: "";
  inline-size: 100%;
  inset-block-start: 0;
  inset-inline-start: 0;
}

.ko-card--completed::before { background: rgb(var(--v-theme-success)); }
.ko-card--completed { background: rgba(var(--v-theme-success), 0.06); }

.ko-card--mastered::before { background: rgb(var(--v-theme-warning)); }

.ko-card--mastered {
  border-color: rgba(var(--v-theme-warning), 0.5);
  background: rgba(var(--v-theme-warning), 0.08);
}

.ko-card--mastered:hover {
  border-color: rgb(var(--v-theme-warning));
  box-shadow: 0 10px 20px -10px rgba(var(--v-theme-warning), 0.55);
}

.ko-card__crown {
  position: absolute;
  z-index: 1;
  inset-block-start: 7px;
  inset-inline-end: 7px;
}

.ko-card__crown--gold {
  color: #c9971e;
}

.ko-grid {
  display: grid;
  gap: 12px;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
}

.ko-card {
  position: relative;
  display: flex;
  overflow: hidden;
  flex-direction: column;
  align-items: flex-start;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.1);
  border-radius: 16px;
  background: rgb(var(--v-theme-surface));
  color: rgb(var(--v-theme-on-surface));
  cursor: pointer;
  gap: 4px;
  padding-block: 20px 16px;
  padding-inline: 18px;
  text-align: start;
  transition: border-color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
}

.ko-card:hover {
  border-color: rgba(var(--v-theme-primary), 0.5);
  box-shadow: 0 6px 16px rgba(var(--v-theme-primary), 0.14);
  transform: translateY(-2px);
}

.ko-card:disabled {
  cursor: not-allowed;
  opacity: 0.55;
}

.ko-card:disabled:hover {
  box-shadow: none;
  transform: none;
}

.ko-card__badge {
  z-index: 1;
  border-radius: 999px;
  background: rgba(var(--v-theme-primary), 0.12);
  color: rgb(var(--v-theme-primary));
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.02em;
  padding-block: 3px;
  padding-inline: 10px;
  text-transform: uppercase;
}

.ko-card__title {
  z-index: 1;
  color: rgb(var(--v-theme-on-surface));
  font-size: 1.05rem;
  font-weight: 700;
  line-height: 1.3;
  margin-block-start: 10px;
}

.ko-card__subtitle {
  z-index: 1;
  color: rgba(var(--v-theme-on-surface), 0.6);
  font-size: 0.8rem;
}

.ko-word {
  display: flex;
  justify-content: center;
  gap: 4px;
}

.ko-word__char {
  border-radius: 6px;
  color: rgba(var(--v-theme-on-surface), 0.35);
  font-size: 1.1rem;
  padding-block: 2px;
  padding-inline: 4px;
}

.ko-word__char--active {
  background: rgba(var(--v-theme-primary), 0.14);
  color: rgb(var(--v-theme-primary));
  font-weight: 700;
}

.ko-word__char--done {
  color: rgba(var(--v-theme-on-surface), 0.7);
}
</style>
