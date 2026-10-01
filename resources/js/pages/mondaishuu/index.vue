<script setup>
// Mondaishuu — latihan soal bergaya kuis interaktif per pelajaran (1–25)
// plus 4 set rangkuman. Konten disusun ulang secara orisinal: pola dan
// cakupan tata bahasa tiap pelajaran mengikuti kurikulum Minna no Nihongo I,
// tapi semua kalimat/kosakata contoh ditulis sendiri (bukan hasil scan buku).
// Kanji memakai furigana lewat RubyText (markup 漢字《かんじ》), tanpa romaji,
// supaya latihannya benar-benar melatih baca tulis Jepang.
import RubyText from '@/components/learning/RubyText.vue'
import { useAutoNext } from '@/composables/useAutoNext'
import { useAuthStore } from '@/stores/auth'
import { $api } from '@/utils/api'

// Unlike the old version, this page does NOT force the blank layout for
// the whole route. The lesson/rangkuman picker (STAGE_SETUP) stays inside
// the normal layout — navbar + sidebar visible — just like every other
// menu page (kosakata, kanji list, dsb). Only once a quiz actually starts
// does the page switch to the same distraction-free full-screen shell used
// by the Kana/Kanji quiz (`.kana-fs--page`), so the "full layar" mode is
// reserved for doing questions, not for browsing which lesson to open.
const { t, locale } = useI18n()
const autoNext = useAutoNext()
const authStore = useAuthStore()

// ---------------------------------------------------------------------
// Bank soal. Dua jenis soal:
//  - mc: pilihan ganda, `choices` 4 opsi, `answer` = index yang benar.
//  - scramble: kata-kata dalam `words` (urutan yang benar) diacak di UI,
//    pengguna menyusunnya kembali dengan mengetuk kata.
// `img` (opsional) adalah nama ikon tabler yang mewakili konteks soal.
// ---------------------------------------------------------------------
const LESSONS = [
  { id: 1, focus: 'これ・それ・あれ、〜は〜です、の（kepemilikan）', questions: [
    { type: 'mc', q: '（自分《じぶん》に 近《ちか》い 物《もの》）＿＿＿は 私《わたし》の 本《ほん》です。', choices: ['これ', 'それ', 'あれ', 'どれ'], answer: 0 , translate: { id: 'Ini adalah buku saya.', en: 'This is my book.' } },
    { type: 'mc', q: '（相手《あいて》に 近《ちか》い 物《もの》）＿＿＿は 辞書《じしょ》ですか。', choices: ['これ', 'それ', 'あれ', 'どなた'], answer: 1, img: 'tabler-book-2' , translate: { id: 'Itu kamus? (dekat lawan bicara)', en: 'Is that a dictionary? (close to the listener)' } },
    { type: 'mc', q: 'これは 私《わたし》＿＿＿ 傘《かさ》です。', choices: ['は', 'の', 'を', 'も'], answer: 1, img: 'tabler-umbrella' , translate: { id: 'Ini payung saya.', en: 'This is my umbrella.' } },
    { type: 'mc', q: 'あの 人《ひと》は＿＿＿ですか。（丁寧《ていねい》に「だれ」）', choices: ['どなた', 'どこ', 'なに', 'いつ'], answer: 0 , translate: { id: 'Siapa orang itu? (sopan)', en: 'Who is that person? (polite)' } },
    { type: 'mc', q: 'サントスさんは ブラジル人《じん》です。マリアさん＿＿＿ ブラジル人《じん》です。', choices: ['も', 'の', 'を', 'は'], answer: 0 , translate: { id: 'Maria juga orang Brasil.', en: 'Maria is also Brazilian.' } },
    { type: 'mc', q: 'ミラーさんは 学生《がくせい》＿＿＿。（否定《ひてい》）', choices: ['じゃありません', 'ではします', 'ですか', 'も'], answer: 0 , translate: { id: 'Miller bukan mahasiswa.', en: 'Miller is not a student.' } },
    { type: 'mc', q: '山田《やまだ》さんは 会社員《かいしゃいん》＿＿＿。', choices: ['です', 'ですか', 'でした', 'ましょう'], answer: 0 , translate: { id: 'Yamada adalah karyawan perusahaan.', en: 'Yamada is a company employee.' } },
    { type: 'scramble', translate: { id: 'Beliau itu siapa? (sopan)', en: 'Who is that person? (polite)' }, words: ['あの', 'かた', 'は', 'どなた', 'ですか'] },
    { type: 'scramble', translate: { id: 'Ya, saya mahasiswa.', en: 'Yes, I am a student.' }, words: ['はい', '、', '学生《がくせい》', 'です'] },
    { type: 'scramble', translate: { id: 'Ini adalah kamus bahasa Jepang saya.', en: 'This is my Japanese dictionary.' }, words: ['これ', 'は', '私《わたし》', 'の', '日本語《にほんご》', 'の', '辞書《じしょ》', 'です'] },
    { type: 'mc', q: 'これは 田中《たなか》さん＿＿＿ 傘《かさ》です。', choices: ['の', 'は', 'を', 'も'], answer: 0 , translate: { id: 'Ini payung Tanaka.', en: 'This is Tanaka\'s umbrella.' } },
    { type: 'mc', q: 'サントスさんは 学生《がくせい》ですか。…いいえ、学生《がくせい》＿＿＿。会社員《かいしゃいん》です。', choices: ['じゃありません', 'ですか', 'でした', 'も'], answer: 0 , translate: { id: 'Tidak, bukan mahasiswa. Karyawan perusahaan.', en: 'No, not a student. A company employee.' } },
    { type: 'scramble', translate: { id: 'Apakah Anda orang IMC?', en: 'Are you from IMC?' }, words: ['IMC', 'の', '方《かた》', 'ですか'] },
    { type: 'scramble', translate: { id: 'Ini bukan payung saya.', en: 'This is not my umbrella.' }, words: ['これ', 'は', '私《わたし》', 'の', '傘《かさ》', 'じゃありません'] },
    { type: 'mc', q: '木村《きむら》：はじめまして。木村《きむら》です。\nリナ：はじめまして。リナです。＿＿＿。', choices: ['どうぞよろしく', 'いただきます', 'お帰《かえ》りなさい', 'かしこまりました'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Senang berkenalan dengan Anda.', en: 'Nice to meet you.' } },
    { type: 'mc', q: 'A：あの　方《かた》は　どなたですか。\nB：＿＿＿。ミラーさんです。', choices: ['山田《やまだ》さんです', 'はい、会社員《かいしゃいん》です', 'いいえ、学生《がくせい》です', '38歳《さい》です'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Itu Yamada.', en: 'That\'s Yamada.' } },
    { type: 'mc', q: 'ワンさんも 学生《がくせい》ですか。…いいえ、ワンさん＿＿＿ 学生《がくせい》じゃありません。', choices: ['は', 'も', 'の', 'を'], answer: 0 , translate: { id: 'Wang bukan mahasiswa.', en: 'Wang is not a student.' } },
    { type: 'mc', q: 'グプタさんは IMC＿＿＿ 社員《しゃいん》です。', choices: ['の', 'は', 'を', 'に'], answer: 0 , translate: { id: 'Gupta adalah karyawan IMC.', en: 'Gupta is an IMC employee.' } },
    { type: 'scramble', translate: { id: 'Apakah ini buku Anda?', en: 'Is this your book?' }, words: ['これ', 'は', 'あなた', 'の', '本《ほん》', 'ですか'] },
    { type: 'scramble', translate: { id: 'Ya, saya karyawan perusahaan.', en: 'Yes, I am a company employee.' }, words: ['はい', '、', '会社員《かいしゃいん》', 'です'] },
  ] },
  { id: 2, focus: 'この・その・あの、ここ・そこ・あそこ、N の N', questions: [
    { type: 'mc', q: '＿＿＿ かばんは 私《わたし》のです。（自分《じぶん》に 近《ちか》い）', choices: ['この', 'その', 'あの', 'どの'], answer: 0, img: 'tabler-briefcase' , translate: { id: 'Tas ini punya saya.', en: 'This bag is mine.' } },
    { type: 'mc', q: '受付《うけつけ》は＿＿＿ですか。（丁寧《ていねい》に「どこ」）', choices: ['ここ', 'そこ', 'どちら', 'どなた'], answer: 2 , translate: { id: 'Resepsionis di sebelah mana? (sopan)', en: 'Which way is the reception? (polite)' } },
    { type: 'mc', q: 'これは＿＿＿の カメラですか。…日本《にほん》のです。', choices: ['どこ', 'なに', 'だれ', 'いつ'], answer: 0, img: 'tabler-camera' , translate: { id: 'Kamera ini buatan mana? ...Buatan Jepang.', en: 'Where is this camera made? ...Made in Japan.' } },
    { type: 'mc', q: 'この かばんは＿＿＿の ですか。…わたしのです。', choices: ['だれ', 'なに', 'どこ', 'いつ'], answer: 0 , translate: { id: 'Tas ini punya siapa? ...Punya saya.', en: 'Whose bag is this? ...It\'s mine.' } },
    { type: 'mc', q: 'エレベーターは＿＿＿ですか。…あそこです。', choices: ['どこ', 'だれ', 'なん', 'どなた'], answer: 0, img: 'tabler-building' , translate: { id: 'Lift di mana? ...Di sana.', en: 'Where is the elevator? ...Over there.' } },
    { type: 'mc', q: 'これは＿＿＿の 雑誌《ざっし》ですか。…車《くるま》の 雑誌《ざっし》です。', choices: ['なん', 'だれ', 'どこ', 'どの'], answer: 0 , translate: { id: 'Ini majalah tentang apa? ...Majalah mobil.', en: 'What is this magazine about? ...A car magazine.' } },
    { type: 'scramble', translate: { id: 'Toilet ada di sana. (dekat lawan bicara)', en: 'The toilet is over there. (close to the listener)' }, words: ['トイレ', 'は', 'そこ', 'です'] },
    { type: 'scramble', translate: { id: 'Guru bahasa Perancis', en: 'French teacher' }, words: ['フランス語《フランスご》', 'の', '先生《せんせい》'] },
    { type: 'scramble', translate: { id: 'Tas ini bukan tas saya.', en: 'This bag is not my bag.' }, words: ['この', 'かばん', 'は', '私《わたし》', 'の', 'じゃありません'] },
    { type: 'mc', q: '＿＿＿ 建物《たてもの》は 何《なん》ですか。（少《すこ》し 遠《とお》い）', choices: ['この', 'その', 'あの', 'どの'], answer: 2 , translate: { id: 'Gedung itu apa? (agak jauh)', en: 'What is that building? (a bit far away)' } },
    { type: 'mc', q: 'この 傘《かさ》は＿＿＿の ですか。…あなたのです。', choices: ['だれ', 'なん', 'どこ', 'いつ'], answer: 0 , translate: { id: 'Payung ini punya siapa? ...Punya Anda.', en: 'Whose umbrella is this? ...It\'s yours.' } },
    { type: 'scramble', translate: { id: 'Kamera itu buatan negara mana?', en: 'Which country is that camera made in?' }, words: ['それ', 'は', 'どこ', 'の', 'カメラ', 'ですか'] },
    { type: 'mc', q: 'A：すみません、受付《うけつけ》は　どちらですか。\nB：＿＿＿。', choices: ['あちらです', 'それはノートです', '3階《がい》です', 'わたしのです'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Permisi, resepsionis di sebelah mana? ...Di sebelah sana.', en: 'Excuse me, which way is the reception? ...That way.' } },
    { type: 'mc', q: 'A：これは　何《なん》の　雑誌《ざっし》ですか。\nB：＿＿＿。', choices: ['車《くるま》の　雑誌《ざっし》です', 'はい、そうです', '3,800円《えん》です', 'わたしのです'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Ini majalah apa? ...Majalah mobil.', en: 'What magazine is this? ...A car magazine.' } },
    { type: 'mc', q: '受付《うけつけ》は＿＿＿ですか。', choices: ['どちら', 'どなた', 'なん', 'いつ'], answer: 0 , translate: { id: 'Resepsionis di sebelah mana?', en: 'Which way is the reception?' } },
    { type: 'mc', q: 'これは＿＿＿の CDですか。…日本語《にほんご》の CDです。', choices: ['なん', 'だれ', 'どこ', 'いつ'], answer: 0 , translate: { id: 'Ini CD apa? ...CD bahasa Jepang.', en: 'What CD is this? ...A Japanese CD.' } },
    { type: 'mc', q: 'あの 人《ひと》は＿＿＿ですか。…佐藤《さとう》さんです。', choices: ['だれ', 'どこ', 'なん', 'いつ'], answer: 0 , translate: { id: 'Siapa orang itu? ...Itu Sato.', en: 'Who is that person? ...That\'s Sato.' } },
    { type: 'scramble', translate: { id: 'Payung ini bukan milik Anda.', en: 'This umbrella is not yours.' }, words: ['この', '傘《かさ》', 'は', 'あなた', 'の', 'じゃありません'] },
    { type: 'scramble', translate: { id: 'Toilet ada di sana.', en: 'The toilet is over there.' }, words: ['トイレ', 'は', 'あそこ', 'です'] },
    { type: 'scramble', translate: { id: 'Ini adalah kamera buatan Jepang.', en: 'This is a Japan-made camera.' }, words: ['これ', 'は', '日本《にほん》', 'の', 'カメラ', 'です'] },
  ] },
  { id: 3, focus: '数字《すうじ》（値段《ねだん》）、この・その・あの、どこの', questions: [
    { type: 'mc', q: '¥5,300 の 読《よ》み方《かた》は？', choices: ['ごせんさんびゃくえん', 'ごまんさんぜんえん', 'ごひゃくさんじゅうえん', 'ごせんさんじゅうえん'], answer: 0, img: 'tabler-tag' , translate: { id: '5.300 yen', en: '5,300 yen' } },
    { type: 'mc', q: '¥18,900 の 読《よ》み方《かた》は？', choices: ['いちまんはっせんきゅうひゃくえん', 'じゅうはちまんきゅうせんえん', 'いちまんはちひゃくきゅうじゅうえん', 'じゅうはっせんきゅうひゃくえん'], answer: 0, img: 'tabler-tag' , translate: { id: '18.900 yen', en: '18,900 yen' } },
    { type: 'mc', q: 'すみません、その 時計《とけい》を 見《み》せて＿＿＿。', choices: ['ください', 'くださいか', 'です', 'ました'], answer: 0, img: 'tabler-clock' , translate: { id: 'Permisi, tolong perlihatkan jam itu.', en: 'Excuse me, please show me that watch.' } },
    { type: 'mc', q: 'マリアさんの お国《くに》は＿＿＿ですか。…ブラジルです。', choices: ['どちら', 'どこの', 'だれの', 'なんの'], answer: 0 , translate: { id: 'Negara asal Maria mana? ...Brasil.', en: 'Where is Maria from? ...Brazil.' } },
    { type: 'mc', q: 'これは どこ＿＿＿ ワインですか。…イタリアのです。', choices: ['の', 'は', 'を', 'に'], answer: 0 , translate: { id: 'Ini anggur dari mana? ...Dari Italia.', en: 'Where is this wine from? ...From Italy.' } },
    { type: 'mc', q: 'カメラ売《う》り場《ば》は＿＿＿ですか。…3階《かい》です。', choices: ['なんかい', 'いくら', 'だれ', 'どなた'], answer: 0 , translate: { id: 'Bagian kamera di lantai berapa? ...Lantai 3.', en: 'Which floor is the camera section on? ...3rd floor.' } },
    { type: 'scramble', translate: { id: 'Jam ini buatan Swiss.', en: 'This watch is made in Switzerland.' }, words: ['この', '時計《とけい》', 'は', 'スイス', 'の', 'です'] },
    { type: 'scramble', translate: { id: 'Silakan lihat.', en: 'Please take a look.' }, words: ['どうぞ', '、', '見《み》て', 'ください'] },
    { type: 'scramble', translate: { id: 'Berapa harga tas ini?', en: 'How much is this bag?' }, words: ['この', 'かばん', 'は', 'いくら', 'ですか'] },
    { type: 'mc', q: '¥124,000 の 読《よ》み方《かた》は？', choices: ['じゅうにまんよんせんえん', 'いちまんにせんよんひゃくえん', 'じゅうにまんよんひゃくえん', 'ひゃくにじゅうよんまんえん'], answer: 0 , translate: { id: '124.000 yen', en: '124,000 yen' } },
    { type: 'mc', q: 'すみません。そのワイン＿＿＿ 見《み》せてください。', choices: ['を', 'は', 'に', 'の'], answer: 0 , translate: { id: 'Permisi. Tolong perlihatkan anggur itu.', en: 'Excuse me. Please show me that wine.' } },
    { type: 'scramble', translate: { id: 'Kalau begitu, saya ambil yang ini.', en: 'In that case, I\'ll take this one.' }, words: ['じゃ', '、', 'これ', 'を', 'ください'] },
    { type: 'mc', q: '店員《てんいん》：いらっしゃいませ。\n客《きゃく》：すみません、この　ネクタイは　いくらですか。\n店員《てんいん》：＿＿＿。', choices: ['6,400円《えん》です', 'どうぞ', 'かしこまりました', 'ちょっと'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Permisi, dasi ini berapa? ...6.400 yen.', en: 'Excuse me, how much is this tie? ...6,400 yen.' } },
    { type: 'mc', q: 'A：すみません、その　時計《とけい》を　見《み》せて　ください。\nB：＿＿＿。', choices: ['どうぞ', 'かしこまりました', 'いいえ、けっこうです', 'また今度《こんど》'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Silakan.', en: 'Here you are.' } },
    { type: 'mc', q: 'その 靴《くつ》は どこ＿＿＿ですか。…フランスのです。', choices: ['の', 'は', 'を', 'に'], answer: 0 , translate: { id: 'Sepatu itu buatan mana? ...Buatan Prancis.', en: 'Where are those shoes from? ...From France.' } },
    { type: 'mc', q: '¥4,700,000 の 読《よ》み方《かた》は？', choices: ['よんひゃくななじゅうまんえん', 'よんじゅうななまんえん', 'よんせんななひゃくまんえん', 'よんひゃくまんななせんえん'], answer: 0 , translate: { id: '4.700.000 yen', en: '4,700,000 yen' } },
    { type: 'mc', q: '松本《まつもと》さんの 車《くるま》は＿＿＿ですか。…あそこです。', choices: ['どこ', 'いくら', 'だれ', 'なん'], answer: 0 , translate: { id: 'Mobil Matsumoto di mana? ...Di sana.', en: 'Where is Matsumoto\'s car? ...Over there.' } },
    { type: 'scramble', translate: { id: 'Ini adalah majalah tentang mobil.', en: 'This is a magazine about cars.' }, words: ['これ', 'は', '車《くるま》', 'の', '雑誌《ざっし》', 'です'] },
    { type: 'scramble', translate: { id: 'Berapa harga komputer ini?', en: 'How much is this computer?' }, words: ['この', 'コンピューター', 'は', 'いくら', 'ですか'] },
    { type: 'scramble', translate: { id: 'Silakan berikan yang ini.', en: 'Please give me this one.' }, words: ['これ', 'を', 'ください'] },
  ] },
  { id: 4, focus: '時間《じかん》（〜時《じ》〜分《ふん》）、から・まで・に、休《やす》み', questions: [
    { type: 'mc', q: '3:15 の 読《よ》み方《かた》は？', choices: ['さんじじゅうごふん', 'さんじゅうごふん', 'さんじはん', 'じゅうごじさんぷん'], answer: 0, img: 'tabler-clock-hour-3' , translate: { id: 'Jam 3 lewat 15 menit', en: '3:15' } },
    { type: 'mc', q: '9:40 の 読《よ》み方《かた》は？', choices: ['くじよんじゅっぷん', 'きゅうじよんじゅうふん', 'くじよんじ', 'きゅうじじゅっぷん'], answer: 0, img: 'tabler-clock-hour-9' , translate: { id: 'Jam 9 lewat 40 menit', en: '9:40' } },
    { type: 'mc', q: '銀行《ぎんこう》は 9時《じ》＿＿＿3時《じ》＿＿＿です。', choices: ['から／まで', 'まで／から', 'に／で', 'を／に'], answer: 0 , translate: { id: 'Bank buka dari jam 9 sampai jam 3.', en: 'The bank is open from 9 to 3.' } },
    { type: 'mc', q: '美術館《びじゅつかん》の 休《やす》みは 月曜日《げつようび》＿＿＿ 木曜日《もくようび》です。', choices: ['と', 'から', 'まで', 'に'], answer: 0 , translate: { id: 'Museum libur hari Senin dan Kamis.', en: 'The museum is closed on Mondays and Thursdays.' } },
    { type: 'mc', q: '毎晩《まいばん》 11時半《じゅういちじはん》＿＿＿ 寝《ね》ます。', choices: ['に', 'で', 'を', 'へ'], answer: 0 , translate: { id: 'Setiap malam saya tidur jam setengah 12.', en: 'Every night I go to sleep at 11:30.' } },
    { type: 'mc', q: 'きょうは 何曜日《なんようび》ですか。…＿＿＿です。', choices: ['水曜日《すいようび》', '9時《じ》', '3月《がつ》', '休《やす》み'], answer: 0 , translate: { id: 'Hari ini hari apa? ...Hari Rabu.', en: 'What day is it today? ...Wednesday.' } },
    { type: 'scramble', translate: { id: 'Setiap pagi saya bangun jam 6.', en: 'Every morning I wake up at 6.' }, words: ['毎朝《まいあさ》', '6時《ろくじ》', 'に', '起《お》きます'] },
    { type: 'scramble', translate: { id: 'Hari ini adalah hari libur.', en: 'Today is a holiday.' }, words: ['きょう', 'は', '休《やす》み', 'です'] },
    { type: 'scramble', translate: { id: 'Perpustakaan buka dari jam 9 sampai jam 6.', en: 'The library is open from 9 to 6.' }, words: ['図書館《としょかん》', 'は', '9時《くじ》', 'から', '6時《ろくじ》', 'まで', 'です'] },
    { type: 'mc', q: 'ニューヨークは 今《いま》 午前《ごぜん》4時《じ》＿＿＿です。', choices: ['×', 'に', 'で', 'の'], answer: 0 , translate: { id: 'Sekarang di New York jam 4 pagi.', en: 'It\'s 4 a.m. in New York now.' } },
    { type: 'mc', q: 'きのうの 晩《ばん》8時《じ》＿＿＿10時《じ》＿＿＿ 勉強《べんきょう》しました。', choices: ['から／まで', 'まで／から', 'に／で', 'を／へ'], answer: 0 , translate: { id: 'Kemarin malam saya belajar dari jam 8 sampai jam 10.', en: 'Last night I studied from 8 to 10.' } },
    { type: 'scramble', translate: { id: 'Toko itu buka dari jam 9 pagi sampai jam 8 malam.', en: 'That shop is open from 9 a.m. to 8 p.m.' }, words: ['あの', '店《みせ》', 'は', '朝《あさ》', '9時《くじ》', 'から', '夜《よる》', '8時《はちじ》', 'まで', 'です'] },
    { type: 'mc', q: 'A：すみません、この　図書館《としょかん》は　何時《なんじ》までですか。\nB：＿＿＿。', choices: ['6時《じ》までです', '月曜日《げつようび》です', 'あそこです', 'わたしのです'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Perpustakaan ini buka sampai jam berapa? ...Sampai jam 6.', en: 'Until what time is this library open? ...Until 6.' } },
    { type: 'mc', q: 'A：美術館《びじゅつかん》の　休《やす》みは　何曜日《なんようび》ですか。\nB：＿＿＿。', choices: ['水曜日《すいようび》です', '9時《じ》からです', 'あそこです', '3階《がい》です'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Museum libur hari apa? ...Hari Rabu.', en: 'What day is the museum closed? ...Wednesday.' } },
    { type: 'mc', q: 'あさって＿＿＿ 日曜日《にちようび》です。', choices: ['は', 'に', 'を', 'で'], answer: 0 , translate: { id: 'Lusa hari Minggu.', en: 'The day after tomorrow is Sunday.' } },
    { type: 'mc', q: 'この 図書館《としょかん》は 土曜日《どようび》＿＿＿ 午後《ごご》 休《やす》みです。', choices: ['×', 'に', 'で', 'を'], answer: 0 , translate: { id: 'Perpustakaan ini libur Sabtu sore.', en: 'This library is closed on Saturday afternoons.' } },
    { type: 'mc', q: '10:00 の 読《よ》み方《かた》は？', choices: ['じゅうじ', 'とおじ', 'じっじ', 'じゅっじ'], answer: 0 , translate: { id: 'Jam 10 tepat', en: '10:00 exactly' } },
    { type: 'scramble', translate: { id: 'Perusahaan saya mulai jam 9 dan selesai jam 5.', en: 'My company starts at 9 and ends at 5.' }, words: ['わたし', 'の', '会社《かいしゃ》', 'は', '9時《くじ》', 'から', '5時《ごじ》', 'までです'] },
    { type: 'scramble', translate: { id: 'Bank tutup jam 3.', en: 'The bank closes at 3.' }, words: ['銀行《ぎんこう》', 'は', '3時《さんじ》', 'に', '終《お》わります'] },
    { type: 'scramble', translate: { id: 'Sekarang New York jam berapa?', en: 'What time is it in New York now?' }, words: ['ニューヨーク', 'は', '今《いま》', '何時《なんじ》', 'ですか'] },
  ] },
  { id: 5, focus: '日《ひ》にち、過去形《かこけい》、へ・と', questions: [
    { type: 'mc', q: '5月《がつ》14日《じゅうよっか》の 読《よ》み方《かた》は？', choices: ['ごがつじゅうよっか', 'ごがついちよんにち', 'ごつきじゅうよんにち', 'ごがつじゅうよんにち'], answer: 0, img: 'tabler-calendar' , translate: { id: 'Tanggal 14 Mei', en: 'May 14th' } },
    { type: 'mc', q: '9月《がつ》20日《はつか》の 読《よ》み方《かた》は？', choices: ['くがつはつか', 'きゅうがつにじゅうにち', 'くがつにじゅうび', 'くつきはつか'], answer: 0, img: 'tabler-calendar' , translate: { id: 'Tanggal 20 September', en: 'September 20th' } },
    { type: 'mc', q: '友達《ともだち》＿＿＿ 映画《えいが》を 見《み》ました。', choices: ['と', 'へ', 'の', 'は'], answer: 0 , translate: { id: 'Saya menonton film bersama teman.', en: 'I watched a movie with a friend.' } },
    { type: 'mc', q: '先月《せんげつ》 日本《にほん》＿＿＿ 来《き》ました。', choices: ['へ', 'で', 'を', 'に「×」'], answer: 0 , translate: { id: 'Bulan lalu saya datang ke Jepang.', en: 'Last month I came to Japan.' } },
    { type: 'mc', q: '誕生日《たんじょうび》は 9月《がつ》＿＿＿ですか。…1日《ついたち》です。', choices: ['なんにち', 'いつ', 'なんじ', 'どこ'], answer: 0 , translate: { id: 'Ulang tahunnya tanggal berapa bulan September? ...Tanggal 1.', en: 'What date in September is the birthday? ...The 1st.' } },
    { type: 'mc', q: '一人《ひとり》＿＿＿ 行《い》きましたか。…友達《ともだち》と 行《い》きました。', choices: ['で', 'に', 'を', 'は'], answer: 0 , translate: { id: 'Apakah pergi sendirian? ...Saya pergi bersama teman.', en: 'Did you go alone? ...I went with a friend.' } },
    { type: 'scramble', translate: { id: 'Kemarin saya pergi ke Kyoto.', en: 'Yesterday I went to Kyoto.' }, words: ['きのう', '京都《きょうと》', 'へ', '行《い》きました'] },
    { type: 'scramble', translate: { id: 'Tahun lalu saya datang ke Jepang.', en: 'Last year I came to Japan.' }, words: ['去年《きょねん》', '日本《にほん》', 'へ', '来《き》ました'] },
    { type: 'scramble', translate: { id: 'Kapan Anda kembali ke negara Anda?', en: 'When are you going back to your country?' }, words: ['いつ', '国《くに》', 'へ', '帰《かえ》りますか'] },
    { type: 'mc', q: '先月《せんげつ》の 25日《にじゅうごにち》＿＿＿ 電車《でんしゃ》＿＿＿ カリナさん＿＿＿ うち＿＿＿ 行《い》きました。', choices: ['に／で／と／へ', 'で／に／と／を', 'に／を／で／へ', 'を／で／に／へ'], answer: 0 , translate: { id: 'Tanggal 25 bulan lalu saya naik kereta pergi ke rumah Karina.', en: 'On the 25th of last month I took the train to Karina\'s house.' } },
    { type: 'scramble', translate: { id: 'Minggu lalu hari Sabtu saya tidak pergi ke mana-mana.', en: 'Last Saturday I didn\'t go anywhere.' }, words: ['先週《せんしゅう》', 'の', '土曜日《どようび》', 'どこも', '行《い》きませんでした'] },
    { type: 'mc', q: 'A：いつ　日本《にほん》へ　来《き》ましたか。\nB：＿＿＿。', choices: ['去年《きょねん》の　4月《がつ》に　来《き》ました', '新幹線《しんかんせん》で　来《き》ました', '友達《ともだち》と　来《き》ました', 'とても　楽《たの》しいです'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Kapan datang ke Jepang? ...Saya datang bulan April tahun lalu.', en: 'When did you come to Japan? ...I came in April last year.' } },
    { type: 'mc', q: 'A：先週《せんしゅう》の　日曜日《にちようび》、どこか　行《い》きましたか。\nB：いいえ、＿＿＿。', choices: ['どこも　行《い》きませんでした', 'どこか　行《い》きました', 'だれも　いません', '何《なに》も　欲《ほ》しいです'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Minggu lalu hari Minggu, apakah pergi ke suatu tempat? ...Tidak, saya tidak pergi ke mana-mana.', en: 'Did you go anywhere last Sunday? ...No, I didn\'t go anywhere.' } },
    { type: 'mc', q: '来年《らいねん》の 8月《がつ》に 国《くに》へ＿＿＿。', choices: ['帰《かえ》ります', '帰《かえ》りました', '帰《かえ》る', '帰《かえ》って'], answer: 0 , translate: { id: 'Bulan Agustus tahun depan saya pulang ke negara asal.', en: 'Next August I\'m going back to my home country.' } },
    { type: 'mc', q: '9月《がつ》9日《ここのか》の 読《よ》み方《かた》は？', choices: ['くがつここのか', 'きゅうがつくにち', 'くがつくにち', 'きゅうがつここのか'], answer: 0 , translate: { id: 'Tanggal 9 September', en: 'September 9th' } },
    { type: 'mc', q: '誕生日《たんじょうび》は＿＿＿ですか。…9月《がつ》15日《じゅうごにち》です。', choices: ['いつ', 'どこ', 'だれ', 'なん'], answer: 0 , translate: { id: 'Kapan ulang tahunnya? ...Tanggal 15 September.', en: 'When is the birthday? ...September 15th.' } },
    { type: 'scramble', translate: { id: 'Bulan lalu saya pergi ke Jepang.', en: 'Last month I went to Japan.' }, words: ['先月《せんげつ》', '日本《にほん》', 'へ', '行《い》きました'] },
    { type: 'scramble', translate: { id: 'Kemarin saya pulang jam 10.', en: 'Yesterday I got home at 10.' }, words: ['きのう', '10時《じゅうじ》', 'に', 'うち', 'へ', '帰《かえ》りました'] },
    { type: 'scramble', translate: { id: 'Saya pergi seorang diri.', en: 'I went alone.' }, words: ['一人《ひとり》', 'で', '行《い》きました'] },
    { type: 'scramble', translate: { id: 'Besok pagi kereta apinya jam berapa?', en: 'What time is the train tomorrow morning?' }, words: ['あした', 'の', '朝《あさ》', '電車《でんしゃ》', 'は', '何時《なんじ》', 'ですか'] },
  ] },
  { id: 6, focus: '動詞《どうし》ます形《けい》、を・に・で・と', questions: [
    { type: 'mc', q: '毎日《まいにち》コーヒー＿＿＿ 飲《の》みます。', choices: ['を', 'に', 'で', 'と'], answer: 0 , translate: { id: 'Setiap hari saya minum kopi.', en: 'Every day I drink coffee.' } },
    { type: 'mc', q: '公園《こうえん》＿＿＿ 散歩《さんぽ》します。', choices: ['を', 'に', 'で', 'へ'], answer: 2 , translate: { id: 'Saya jalan-jalan di taman.', en: 'I take a walk in the park.' } },
    { type: 'mc', q: '先生《せんせい》＿＿＿ 日本語《にほんご》を 習《なら》います。', choices: ['に', 'を', 'で', 'と'], answer: 0 , translate: { id: 'Saya belajar bahasa Jepang dari guru.', en: 'I learn Japanese from a teacher.' } },
    { type: 'mc', q: '毎朝《まいあさ》パン＿＿＿ 卵《たまご》＿＿＿ 食《た》べます。', choices: ['と／を', 'を／と', 'に／で', 'の／を'], answer: 0, img: 'tabler-egg' , translate: { id: 'Setiap pagi saya makan roti dan telur.', en: 'Every morning I eat bread and eggs.' } },
    { type: 'mc', q: 'デパート＿＿＿ パン＿＿＿ 買《か》いました。', choices: ['で／を', 'を／で', 'に／を', 'へ／を'], answer: 0 , translate: { id: 'Saya membeli roti di department store.', en: 'I bought bread at the department store.' } },
    { type: 'mc', q: '3時《じ》＿＿＿ 教室《きょうしつ》＿＿＿ 日本語《にほんご》の 先生《せんせい》＿＿＿ 会《あ》います。', choices: ['に／で／に', 'で／に／を', 'を／に／で', 'に／を／で'], answer: 0 , translate: { id: 'Jam 3 saya bertemu guru bahasa Jepang di kelas.', en: 'At 3 o\'clock I meet my Japanese teacher in the classroom.' } },
    { type: 'scramble', translate: { id: 'Kemarin saya tidak belajar.', en: 'Yesterday I didn\'t study.' }, words: ['きのう', '、', '勉強《べんきょう》', 'しませんでした'] },
    { type: 'scramble', translate: { id: 'Tidak, saya tidak merokok.', en: 'No, I don\'t smoke.' }, words: ['いいえ', '、', 'たばこ', 'を', '吸《す》いません'] },
    { type: 'scramble', translate: { id: 'Ayo kita istirahat sebentar.', en: 'Let\'s take a short break.' }, words: ['ちょっと', '、', '休《やす》みましょう'] },
    { type: 'mc', q: 'きのう 自転車《じてんしゃ》＿＿＿ スーパー＿＿＿ 行《い》きました。スーパー＿＿＿ 牛乳《ぎゅうにゅう》＿＿＿ 果物《くだもの》＿＿＿ 買《か》いました。', choices: ['で／へ／で／と／を', 'に／を／で／を／と', 'で／に／を／と／で', 'へ／で／に／を／と'], answer: 0 , translate: { id: 'Kemarin saya naik sepeda ke supermarket. Di supermarket saya membeli susu dan buah.', en: 'Yesterday I rode a bike to the supermarket. At the supermarket I bought milk and fruit.' } },
    { type: 'scramble', translate: { id: 'Setiap hari saya makan siang jam 12 di kantin.', en: 'Every day I have lunch at 12 in the cafeteria.' }, words: ['毎日《まいにち》', '12時《じゅうにじ》', 'に', '食堂《しょくどう》', 'で', '昼《ひる》ごはん', 'を', '食《た》べます'] },
    { type: 'mc', q: 'A：いっしょに　テニスを　しませんか。\nB：＿＿＿。', choices: ['ええ、しましょう', 'いいえ、しません', 'はい、そうです', 'どういたしまして'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Ayo main tenis bersama. ...Ya, ayo.', en: 'Let\'s play tennis together. ...Yes, let\'s.' } },
    { type: 'mc', q: 'A：ちょっと　休《やす》みませんか。\nB：＿＿＿。', choices: ['ええ、休《やす》みましょう', 'いいえ、休《やす》みでした', 'はい、そうですか', 'すみません'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Mau istirahat sebentar? ...Ya, ayo istirahat.', en: 'Want to take a short break? ...Yes, let\'s rest.' } },
    { type: 'mc', q: 'きょう 3時《じ》に 教室《きょうしつ》で 日本語《にほんご》の 先生《せんせい》＿＿＿ 会《あ》います。', choices: ['に', 'を', 'で', 'と'], answer: 0 , translate: { id: 'Hari ini jam 3 saya bertemu guru bahasa Jepang di kelas.', en: 'Today at 3 I meet my Japanese teacher in the classroom.' } },
    { type: 'mc', q: '毎朝《まいあさ》ロビー＿＿＿ 新聞《しんぶん》＿＿＿ 読《よ》みます。', choices: ['で／を', 'を／で', 'に／を', 'へ／を'], answer: 0 , translate: { id: 'Setiap pagi saya membaca koran di lobi.', en: 'Every morning I read the newspaper in the lobby.' } },
    { type: 'mc', q: '「テニス（を）します」の 例《れい》で、＿＿＿を しますか。', choices: ['サッカー', 'コーヒー', 'たばこ', '手紙《てがみ》'], answer: 0, img: 'tabler-ball-football' , translate: { id: 'Pada contoh \'main tenis\', main apa? ...Sepak bola.', en: 'In the example \'play tennis\', what do you play? ...Soccer.' } },
    { type: 'scramble', translate: { id: 'Setiap malam saya menonton TV.', en: 'Every night I watch TV.' }, words: ['毎晩《まいばん》', 'テレビ', 'を', '見《み》ます'] },
    { type: 'scramble', translate: { id: 'Kemarin malam saya menulis surat.', en: 'Last night I wrote a letter.' }, words: ['きのう', 'の', '晩《ばん》', '手紙《てがみ》', 'を', '書《か》きました'] },
    { type: 'scramble', translate: { id: 'Ayo kita berhenti sebentar.', en: 'Let\'s stop for a bit.' }, words: ['ちょっと', '休《やす》みましょう'] },
    { type: 'scramble', translate: { id: 'Saya bertemu dengan guru bahasa Jepang.', en: 'I meet with the Japanese teacher.' }, words: ['日本語《にほんご》', 'の', '先生《せんせい》', 'に', '会《あ》います'] },
  ] },
  { id: 7, focus: 'で（道具《どうぐ》）、に（相手《あいて》）、あげます・もらいます', questions: [
    { type: 'mc', q: '箸《はし》＿＿＿ ご飯《はん》を 食《た》べます。', choices: ['を', 'で', 'に', 'と'], answer: 1 , translate: { id: 'Saya makan nasi dengan sumpit.', en: 'I eat rice with chopsticks.' } },
    { type: 'mc', q: '誕生日《たんじょうび》に 友達《ともだち》＿＿＿ プレゼントを もらいました。', choices: ['に', 'を', 'で', 'へ'], answer: 0, img: 'tabler-gift' , translate: { id: 'Saat ulang tahun saya menerima hadiah dari teman.', en: 'On my birthday I received a present from a friend.' } },
    { type: 'mc', q: '日本語《にほんご》＿＿＿ 「ありがとう」＿＿＿ 何《なん》ですか。', choices: ['で／は', 'は／で', 'を／に', 'に／を'], answer: 0 , translate: { id: '\'Terima kasih\' dalam bahasa Jepang apa?', en: 'What is \'thank you\' in Japanese?' } },
    { type: 'mc', q: 'わたしは 田中《たなか》先生《せんせい》＿＿＿ 日本語《にほんご》＿＿＿ 習《なら》いました。', choices: ['に／を', 'を／に', 'で／を', 'に／で'], answer: 0 , translate: { id: 'Saya belajar bahasa Jepang dari Pak Tanaka.', en: 'I learned Japanese from Mr. Tanaka.' } },
    { type: 'mc', q: '山田《やまだ》さんは タワポンさんに 日本語《にほんご》を 教《おし》えました。タワポンさんは 山田《やまだ》さん＿＿＿ 日本語《にほんご》を 教《おし》えて＿＿＿。', choices: ['に／もらいました', 'に／あげました', 'を／もらいました', 'で／くれました'], answer: 0 , translate: { id: 'Yamada mengajari Thaworn bahasa Jepang. Thaworn diajari bahasa Jepang oleh Yamada.', en: 'Yamada taught Thaworn Japanese. Thaworn was taught Japanese by Yamada.' } },
    { type: 'scramble', translate: { id: 'Saya memberikan bunga kepada Sari.', en: 'I gave flowers to Sari.' }, words: ['私《わたし》', 'は', 'サリさん', 'に', '花《はな》', 'を', 'あげました'] },
    { type: 'scramble', translate: { id: 'Sari menerima bunga dari saya.', en: 'Sari received flowers from me.' }, words: ['サリさん', 'は', '私《わたし》', 'に', '花《はな》', 'を', 'もらいました'] },
    { type: 'scramble', translate: { id: 'Apakah Anda sudah mengirim laporan?', en: 'Have you sent the report yet?' }, words: ['もう', 'レポート', 'を', '送《おく》りましたか'] },
    { type: 'mc', q: '何《なに》で この 魚《さかな》を 切《き》りますか。…ナイフ＿＿＿ 切《き》ります。', choices: ['で', 'を', 'に', 'と'], answer: 0, img: 'tabler-fish' , translate: { id: 'Dengan apa memotong ikan ini? ...Dipotong dengan pisau.', en: 'What do you cut this fish with? ...Cut with a knife.' } },
    { type: 'scramble', translate: { id: 'Saya mengirim foto keluarga ke Jepang.', en: 'I sent a family photo to Japan.' }, words: ['家族《かぞく》', 'の', '写真《しゃしん》', 'を', '日本《にほん》', 'へ', '送《おく》りました'] },
    { type: 'mc', q: 'A：誕生日《たんじょうび》に　何《なに》を　もらいましたか。\nB：＿＿＿。', choices: ['友達《ともだち》に　時計《とけい》を　もらいました', '友達《ともだち》に　時計《とけい》を　あげました', '花《はな》が　好《す》きです', 'いいえ、けっこうです'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Apa yang diterima saat ulang tahun? ...Saya menerima jam tangan dari teman.', en: 'What did you get for your birthday? ...I got a watch from a friend.' } },
    { type: 'mc', q: 'A：これ、お土産《みやげ》です。どうぞ。\nB：＿＿＿。', choices: ['ありがとうございます', 'いただきます', 'ごちそうさまでした', 'かしこまりました'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Ini oleh-oleh, silakan. ...Terima kasih banyak.', en: 'This is a souvenir, please take it. ...Thank you very much.' } },
    { type: 'mc', q: 'ミラーさんは 上田《うえだ》先生《せんせい》＿＿＿ 英語《えいご》＿＿＿ 習《なら》いました。', choices: ['に／を', 'を／に', 'で／を', 'に／で'], answer: 0 , translate: { id: 'Miller belajar bahasa Inggris dari Bu Ueda.', en: 'Miller learned English from Ms. Ueda.' } },
    { type: 'mc', q: 'もう 荷物《にもつ》を＿＿＿か。…はい、もう 送《おく》りました。', choices: ['送《おく》りました', '送《おく》ります', '送《おく》る', '送《おく》って'], answer: 0 , translate: { id: 'Apakah barangnya sudah dikirim? ...Ya, sudah dikirim.', en: 'Has the package been sent? ...Yes, it\'s been sent.' } },
    { type: 'mc', q: 'この魚《さかな》は 何《なに》で 切《き》りますか。…包丁《ほうちょう》＿＿＿ 切《き》ります。', choices: ['で', 'を', 'に', 'と'], answer: 0, img: 'tabler-fish' , translate: { id: 'Ikan ini dipotong dengan apa? ...Dipotong dengan pisau dapur.', en: 'What is this fish cut with? ...Cut with a kitchen knife.' } },
    { type: 'scramble', translate: { id: 'Saya menerima jam tangan dari ayah.', en: 'I received a watch from my father.' }, words: ['父《ちち》', 'に', '時計《とけい》', 'を', 'もらいました'] },
    { type: 'scramble', translate: { id: 'Apa bahasa Inggrisnya "terima kasih"?', en: 'What is "thank you" in English?' }, words: ['「ありがとう」', 'は', '英語《えいご》', 'で', '何《なん》', 'ですか'] },
    { type: 'scramble', translate: { id: '"Good morning" bahasa Jepangnya apa?', en: 'What is "Good morning" in Japanese?' }, words: ['"Good morning"', 'は', '日本語《にほんご》', 'で', '何《なん》', 'ですか'] },
    { type: 'scramble', translate: { id: 'Saya belajar bahasa Jepang di universitas di Tiongkok.', en: 'I studied Japanese at a university in China.' }, words: ['中国《ちゅうごく》', 'の', '大学《だいがく》', 'で', '日本語《にほんご》', 'を', '習《なら》いました'] },
    { type: 'scramble', translate: { id: 'Di mana Anda meminjam buku?', en: 'Where do you borrow books?' }, words: ['どこ', 'で', '本《ほん》', 'を', '借《か》りますか'] },
  ] },
  { id: 8, focus: 'い形容詞《けいようし》・な形容詞《けいようし》', questions: [
    { type: 'mc', q: '「新《あたら》しい」の 反対《はんたい》は？', choices: ['古《ふる》い', '安《やす》い', '大《おお》きい', '静《しず》か'], answer: 0 , translate: { id: 'Lawan kata \'baru\' apa? ...Lama.', en: 'What\'s the opposite of \'new\'? ...Old.' } },
    { type: 'mc', q: '「安《やす》い」の 反対《はんたい》は？', choices: ['高《たか》い', '低《ひく》い', '古《ふる》い', '暗《くら》い'], answer: 0 , translate: { id: 'Lawan kata \'murah\' apa? ...Mahal.', en: 'What\'s the opposite of \'cheap\'? ...Expensive.' } },
    { type: 'mc', q: '「静《しず》か」の 反対《はんたい》は？', choices: ['賑《にぎ》やか', '有名《ゆうめい》', '親切《しんせつ》', '暇《ひま》'], answer: 0 , translate: { id: 'Lawan kata \'sepi/tenang\' apa? ...Ramai.', en: 'What\'s the opposite of \'quiet\'? ...Lively.' } },
    { type: 'mc', q: '日本語《にほんご》は 難《むずか》しいですか。…いいえ、あまり＿＿＿。', choices: ['難《むずか》しくないです', '難《むずか》しいです', '難《むずか》しかったです', '難《むずか》しくて'], answer: 0 , translate: { id: 'Apakah bahasa Jepang sulit? ...Tidak, tidak terlalu sulit.', en: 'Is Japanese difficult? ...No, not very difficult.' } },
    { type: 'mc', q: 'カリナさんは（きれい・親切《しんせつ》）です。＿＿＿。', choices: ['きれいです。そして、親切《しんせつ》です', 'きれいで、親切《しんせつ》くて', 'きれいくて、親切《しんせつ》です', 'きれいだ、親切《しんせつ》だ'], answer: 0 , translate: { id: 'Karina orangnya cantik dan ramah. Cantik, dan juga ramah.', en: 'Karina is pretty and kind. Pretty, and also kind.' } },
    { type: 'mc', q: 'ワットさんは＿＿＿人《ひと》ですか。…すてきな 人《ひと》です。', choices: ['どんな', 'なんの', 'どこの', 'いつの'], answer: 0 , translate: { id: 'Watt orangnya bagaimana? ...Orang yang menyenangkan.', en: 'What kind of person is Watt? ...A nice person.' } },
    { type: 'scramble', translate: { id: 'Beliau adalah guru yang ramah.', en: 'He/she is a kind teacher.' }, words: ['ワットさん', 'は', '親切《しんせつ》', 'な', '先生《せんせい》', 'です'] },
    { type: 'scramble', translate: { id: 'Saya membeli tas yang baru.', en: 'I bought a new bag.' }, words: ['新《あたら》しい', 'かばん', 'を', '買《か》いました'] },
    { type: 'scramble', translate: { id: 'Restoran ini kecil tapi terkenal.', en: 'This restaurant is small but famous.' }, words: ['この', 'レストラン', 'は', '小《ちい》さい', 'です', 'が', '、', '有名《ゆうめい》', 'です'] },
    { type: 'mc', q: '会社《かいしゃ》の 寮《りょう》は 古《ふる》いです＿＿＿、きれいです。', choices: ['が', 'に', 'を', 'の'], answer: 0 , translate: { id: 'Asrama perusahaan itu tua, tapi bersih.', en: 'That company dormitory is old, but clean.' } },
    { type: 'scramble', translate: { id: 'Apakah kehidupan di Jepang menyenangkan?', en: 'Is life in Japan fun?' }, words: ['日本《にほん》', 'の', '生活《せいかつ》', 'は', '楽《たの》しいですか'] },
    { type: 'mc', q: 'A：日本《にほん》の　生活《せいかつ》は　どうですか。\nB：＿＿＿。', choices: ['大変《たいへん》ですが、楽《たの》しいです', 'どうぞよろしく', 'かしこまりました', 'いただきます'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Bagaimana kehidupan di Jepang? ...Berat, tapi menyenangkan.', en: 'How is life in Japan? ...Tough, but fun.' } },
    { type: 'mc', q: 'A：日本語《にほんご》が　お上手《じょうず》ですね。\nB：＿＿＿。', choices: ['いいえ、まだまだです', 'ありがとうございました', 'ごちそうさまでした', 'そうしましょう'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Bahasa Jepang Anda bagus sekali. ...Tidak, masih belum.', en: 'Your Japanese is very good. ...No, not yet.' } },
    { type: 'mc', q: '奈良《なら》は＿＿＿ 所《ところ》ですか。…いい 所《ところ》ですよ。', choices: ['どんな', 'なん', 'どこ', 'だれ'], answer: 0 , translate: { id: 'Nara itu tempat yang bagaimana? ...Tempat yang bagus.', en: 'What kind of place is Nara? ...A nice place.' } },
    { type: 'mc', q: 'シュミットさんは＿＿＿ 人《ひと》ですか。…すてきな 人《ひと》です。', choices: ['どんな', 'なんの', 'どこの', 'いつの'], answer: 0 , translate: { id: 'Schmidt orangnya bagaimana? ...Orang yang menyenangkan.', en: 'What kind of person is Schmidt? ...A nice person.' } },
    { type: 'mc', q: '寮《りょう》の 部屋《へや》は＿＿＿ですか。…小《ちい》さいですが、きれいです。', choices: ['どう', 'どんな', 'なん', 'どこ'], answer: 0 , translate: { id: 'Kamar asrama bagaimana? ...Kecil, tapi bersih.', en: 'How is the dorm room? ...Small, but clean.' } },
    { type: 'scramble', translate: { id: 'Kamar tidur saya sempit.', en: 'My bedroom is cramped.' }, words: ['わたし', 'の', '部屋《へや》', 'は', '狭《せま》い', 'です'] },
    { type: 'scramble', translate: { id: 'Bagaimana makanan Jepang?', en: 'How is Japanese food?' }, words: ['日本《にほん》', 'の', '食《た》べ物《もの》', 'は', 'どう', 'ですか'] },
    { type: 'scramble', translate: { id: 'Apakah sekarang dingin?', en: 'Is it cold now?' }, words: ['いま', '寒《さむ》い', 'ですか'] },
    { type: 'scramble', translate: { id: 'Terima kasih atas kebaikan Anda.', en: 'Thank you for your kindness.' }, words: ['ご', '親切《しんせつ》', 'に', 'ありがとうございます'] },
  ] },
  { id: 9, focus: '好《す》き・嫌《きら》い（が）、から（理由《りゆう》）', questions: [
    { type: 'mc', q: '私《わたし》は 魚《さかな》＿＿＿ 好《す》きです。', choices: ['が', 'を', 'に', 'は'], answer: 0, img: 'tabler-fish' , translate: { id: 'Saya suka ikan.', en: 'I like fish.' } },
    { type: 'mc', q: '暑《あつ》いです＿＿＿、窓《まど》を 開《あ》けます。', choices: ['から', 'が', 'ので', 'し'], answer: 0 , translate: { id: 'Karena panas, saya membuka jendela.', en: 'Because it\'s hot, I open the window.' } },
    { type: 'mc', q: 'タイ語《ご》＿＿＿ わかりますか。…いいえ、全然《ぜんぜん》 分《わ》かりません。', choices: ['が', 'を', 'に', 'へ'], answer: 0 , translate: { id: 'Apakah mengerti bahasa Thai? ...Tidak, sama sekali tidak mengerti.', en: 'Do you understand Thai? ...No, not at all.' } },
    { type: 'mc', q: '細《こま》かい お金《かね》＿＿＿ ありませんでした＿＿＿、佐藤《さとう》さん＿＿＿ 借《か》りました。', choices: ['が／から／に', 'を／し／で', 'は／と／を', 'が／し／を'], answer: 0 , translate: { id: 'Karena tidak punya uang receh, saya pinjam dari Sato.', en: 'Since I had no small change, I borrowed some from Sato.' } },
    { type: 'mc', q: 'あした 花見《はなみ》を しませんか。…すみません。友達《ともだち》＿＿＿ 約束《やくそく》＿＿＿ ありますから。', choices: ['と／が', 'に／を', 'の／に', 'と／を'], answer: 0 , translate: { id: 'Besok mau lihat bunga sakura bersama? ...Maaf, saya sudah janji dengan teman.', en: 'Want to go cherry blossom viewing together tomorrow? ...Sorry, I already have plans with a friend.' } },
    { type: 'scramble', translate: { id: 'Karena besok ada ujian, saya belajar.', en: 'Since there\'s an exam tomorrow, I\'m studying.' }, words: ['あした', '試験《しけん》', 'が', 'あります', 'から', '、', '勉強《べんきょう》します'] },
    { type: 'scramble', translate: { id: 'Saya tidak begitu suka sayur.', en: 'I don\'t like vegetables very much.' }, words: ['野菜《やさい》', 'が', 'あまり', '好《す》き', 'じゃありません'] },
    { type: 'scramble', translate: { id: 'Kenapa Anda tidak makan sarapan?', en: 'Why didn\'t you eat breakfast?' }, words: ['どうして', '朝《あさ》ごはん', 'を', '食《た》べませんでしたか'] },
    { type: 'mc', q: 'どんな スポーツ＿＿＿ 好《す》きですか。…野球《やきゅう》＿＿＿ 好《す》きです。', choices: ['が／が', 'を／を', 'に／に', 'は／を'], answer: 0, img: 'tabler-ball-baseball' , translate: { id: 'Suka olahraga apa? ...Saya suka bisbol.', en: 'What sport do you like? ...I like baseball.' } },
    { type: 'scramble', translate: { id: 'Saya tidak begitu tidur semalam.', en: 'I didn\'t sleep much last night.' }, words: ['きのう', 'の', '晩《ばん》', 'あまり', '寝《ね》ませんでした'] },
    { type: 'mc', q: '山田《やまだ》：あした　いっしょに　花見《はなみ》を　しませんか。\n木村《きむら》：すみません。あしたは　＿＿＿。友達《ともだち》と　約束《やくそく》が　ありますから。', choices: ['ちょっと……', 'いいですね', 'そうですか', 'どういたしまして'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Besok mau lihat bunga sakura bersama? ...Maaf, besok agak (repot).', en: 'Want to go cherry blossom viewing together tomorrow? ...Sorry, tomorrow\'s a bit (inconvenient).' } },
    { type: 'mc', q: 'A：どうして　きのう　学校《がっこう》を　休《やす》みましたか。\nB：＿＿＿。', choices: ['頭《あたま》が　痛《いた》かったですから', '10時《じ》に　休《やす》みました', 'どこも　休《やす》みです', 'かぜです'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Kenapa kemarin tidak masuk sekolah? ...Karena kepala saya sakit.', en: 'Why didn\'t you go to school yesterday? ...Because I had a headache.' } },
    { type: 'mc', q: '暑《あつ》いですから、冷《つめ》たい 飲《の》み物《もの》を＿＿＿。', choices: ['飲《の》みます', '飲《の》みたくないです', '飲《の》みません', '飲《の》みました'], answer: 0 , translate: { id: 'Karena panas, saya minum minuman dingin.', en: 'Because it\'s hot, I drink something cold.' } },
    { type: 'mc', q: '野菜《やさい》が 好《す》きじゃありませんから、＿＿＿。', choices: ['あまり食《た》べません', 'たくさん食《た》べます', '大好《だいす》きです', '毎日《まいにち》食《た》べます'], answer: 0 , translate: { id: 'Karena tidak suka sayur, saya jarang makan sayur.', en: 'Since I don\'t like vegetables, I rarely eat them.' } },
    { type: 'mc', q: 'かたかなが＿＿＿ 分《わ》かります。（たくさん・全然《ぜんぜん》・だいたい）', choices: ['だいたい', '全然《ぜんぜん》', 'あまり', '少《すこ》し'], answer: 0 , translate: { id: 'Saya mengerti katakana secara garis besar. (banyak/sama sekali/garis besar)', en: 'I understand katakana roughly. (a lot/not at all/roughly)' } },
    { type: 'scramble', translate: { id: 'Karena tidak ada uang kecil, saya pinjam dari Sato.', en: 'Since I had no small change, I borrowed some from Sato.' }, words: ['細《こま》かい', 'お金《かね》', 'が', 'ありませんでしたから', '、', '佐藤《さとう》さん', 'に', '借《か》りました'] },
    { type: 'scramble', translate: { id: 'Mengapa Anda tidak melakukan dansa?', en: 'Why don\'t you dance?' }, words: ['どうして', 'ダンス', 'を', 'しませんか'] },
    { type: 'scramble', translate: { id: 'Karena bahasa Jepang saya belum lancar.', en: 'Because my Japanese isn\'t fluent yet.' }, words: ['日本語《にほんご》', 'が', 'まだ', '上手《じょうず》', 'じゃありませんから'] },
    { type: 'scramble', translate: { id: 'Saya bekerja karena bekerja di perusahaan Jepang.', en: 'I work because I work at a Japanese company.' }, words: ['日本《にほん》', 'の', '会社《かいしゃ》', 'で', '働《はたら》きますから'] },
    { type: 'scramble', translate: { id: 'Apakah kamu suka olahraga apa saja?', en: 'What kinds of sports do you like?' }, words: ['どんな', 'スポーツ', 'が', '好《す》きですか'] },
  ] },
  { id: 10, focus: 'あります・います、上《うえ》・下《した》・中《なか》', questions: [
    { type: 'mc', q: '机《つくえ》の 上《うえ》に 本《ほん》が ＿＿＿。', choices: ['あります', 'います', 'です', 'ました'], answer: 0, img: 'tabler-book' , translate: { id: 'Ada buku di atas meja.', en: 'There is a book on the desk.' } },
    { type: 'mc', q: '庭《にわ》に 猫《ねこ》が ＿＿＿。', choices: ['あります', 'います', 'でした', 'ません'], answer: 1, img: 'tabler-cat' , translate: { id: 'Ada kucing di halaman.', en: 'There is a cat in the yard.' } },
    { type: 'mc', q: '猫《ねこ》は 箱《はこ》の＿＿＿に います。', choices: ['中《なか》', '上《うえ》', '下《した》', '前《まえ》'], answer: 0 , translate: { id: 'Kucing ada di dalam kotak.', en: 'The cat is inside the box.' } },
    { type: 'mc', q: '本屋《ほんや》は 郵便局《ゆうびんきょく》と 喫茶店《きっさてん》の＿＿＿に あります。', choices: ['間《あいだ》', '中《なか》', '上《うえ》', '外《そと》'], answer: 0 , translate: { id: 'Toko buku ada di antara kantor pos dan kedai kopi.', en: 'The bookstore is between the post office and the coffee shop.' } },
    { type: 'mc', q: '車《くるま》の 中《なか》に だれ＿＿＿ いますか。…だれ＿＿＿ いません。', choices: ['が／も', 'は／が', 'を／は', 'に／が'], answer: 0 , translate: { id: 'Apakah ada orang di dalam mobil? ...Tidak ada siapa-siapa.', en: 'Is there anyone in the car? ...There\'s no one.' } },
    { type: 'mc', q: '牛乳《ぎゅうにゅう》は＿＿＿に ありますか。…冷蔵庫《れいぞうこ》に あります。', choices: ['どこ', 'だれ', 'なに', 'いつ'], answer: 0 , translate: { id: 'Susu ada di mana? ...Ada di kulkas.', en: 'Where is the milk? ...It\'s in the fridge.' } },
    { type: 'scramble', translate: { id: 'Kamar mandi tidak ada di lantai satu.', en: 'The bathroom is not on the first floor.' }, words: ['トイレ', 'は', '1階《いっかい》', 'に', 'ありません'] },
    { type: 'scramble', translate: { id: 'Tidak ada siapa-siapa di taman.', en: 'There\'s no one in the yard.' }, words: ['庭《にわ》', 'に', 'だれも', 'いません'] },
    { type: 'scramble', translate: { id: 'Anak kucing ada di bawah kursi.', en: 'The kitten is under the chair.' }, words: ['猫《ねこ》', 'は', '椅子《いす》', 'の', '下《した》', 'に', 'います'] },
    { type: 'mc', q: 'このビル＿＿＿ 喫茶店《きっさてん》＿＿＿ ありますか。…はい、地下《ちか》1階《いっかい》＿＿＿ あります。', choices: ['に／が／に', 'は／を／で', 'で／が／を', 'に／を／で'], answer: 0 , translate: { id: 'Apakah di gedung ini ada kedai kopi? ...Ya, ada di lantai bawah tanah 1.', en: 'Is there a coffee shop in this building? ...Yes, it\'s on basement floor 1.' } },
    { type: 'scramble', translate: { id: 'Saya bertemu dengan Yi di depan stasiun.', en: 'I met Yi in front of the station.' }, words: ['駅《えき》', 'の', '前《まえ》', 'で', 'イーさん', 'に', '会《あ》いました'] },
    { type: 'mc', q: 'A：すみません、トイレは　どこですか。\nB：＿＿＿。', choices: ['あそこです', 'います', 'それです', 'どなたです'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Permisi, toilet di mana? ...Di sana.', en: 'Excuse me, where is the toilet? ...Over there.' } },
    { type: 'mc', q: 'A：事務所《じむしょ》に　だれか　いますか。\nB：いいえ、＿＿＿。', choices: ['だれも　いません', 'だれか　います', '何《なに》も　ありません', 'いいえ、あります'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Apakah ada orang di kantor? ...Tidak, tidak ada siapa-siapa.', en: 'Is there anyone in the office? ...No, there\'s no one.' } },
    { type: 'mc', q: '紅茶《こうちゃ》売《う》り場《ば》は＿＿＿に あります。', choices: ['地下《ちか》', '上《うえ》', '外《そと》', 'そば'], answer: 0 , translate: { id: 'Bagian teh ada di lantai bawah tanah.', en: 'The tea section is on the basement floor.' } },
    { type: 'mc', q: '箱《はこ》の 中《なか》に 何《なに》も＿＿＿。', choices: ['ありません', 'います', 'いません', 'です'], answer: 0 , translate: { id: 'Di dalam kotak tidak ada apa-apa.', en: 'There\'s nothing inside the box.' } },
    { type: 'mc', q: '木《き》の 下《した》に 犬《いぬ》が＿＿＿。', choices: ['います', 'あります', 'でした', 'ません'], answer: 0, img: 'tabler-dog' , translate: { id: 'Ada anjing di bawah pohon.', en: 'There is a dog under the tree.' } },
    { type: 'scramble', translate: { id: 'Susu ada di kulkas.', en: 'The milk is in the fridge.' }, words: ['牛乳《ぎゅうにゅう》', 'は', '冷蔵庫《れいぞうこ》', 'に', 'あります'] },
    { type: 'scramble', translate: { id: 'Sato duduk di sebelah Miller.', en: 'Sato is sitting next to Miller.' }, words: ['佐藤《さとう》さん', 'は', 'ミラーさん', 'の', '隣《となり》', 'に', 'います'] },
    { type: 'scramble', translate: { id: 'Apakah ada bangunan tinggi di sana?', en: 'Is there a tall building over there?' }, words: ['あそこ', 'に', '高《たか》い', 'ビル', 'が', 'ありますね'] },
    { type: 'scramble', translate: { id: 'Di dalam mobil tidak ada siapa-siapa.', en: 'There\'s no one in the car.' }, words: ['車《くるま》', 'の', '中《なか》', 'に', 'だれも', 'いません'] },
  ] },
  { id: 11, focus: '助数詞《じょすうし》（〜つ・〜人《にん》・〜枚《まい》）', questions: [
    { type: 'mc', q: 'りんごを＿＿＿ ください。（4個《こ》）', choices: ['よっつ', 'よんつ', 'よにん', 'よんまい'], answer: 0 , translate: { id: 'Tolong berikan 4 buah apel.', en: 'Please give me 4 apples.' } },
    { type: 'mc', q: '切手《きって》を＿＿＿ ください。（3枚《まい》）', choices: ['さんまい', 'さんにん', 'みっつ', 'さんこ'], answer: 0, img: 'tabler-stamp' , translate: { id: 'Tolong berikan 3 lembar perangko.', en: 'Please give me 3 stamps.' } },
    { type: 'mc', q: '家族《かぞく》は＿＿＿です。（5人《にん》）', choices: ['ごにん', 'いつつ', 'ごまい', 'ごだい'], answer: 0 , translate: { id: 'Keluarga saya berjumlah 5 orang.', en: 'My family has 5 people.' } },
    { type: 'mc', q: '店《みせ》の 前《まえ》に 自転車《じてんしゃ》が 20台《だい》＿＿＿ あります。', choices: ['ぐらい', 'ずつ', 'まで', 'しか'], answer: 0, img: 'tabler-bike' , translate: { id: 'Ada sekitar 20 sepeda di depan toko.', en: 'There are about 20 bicycles in front of the store.' } },
    { type: 'mc', q: '大阪《おおさか》＿＿＿ 東京《とうきょう》＿＿＿ 新幹線《しんかんせん》＿＿＿ 2時間半《にじかんはん》ぐらい＿＿＿ かかります。', choices: ['から／まで／で／×', 'まで／から／に／を', 'に／で／を／×', 'から／まで／に／で'], answer: 0 , translate: { id: 'Dari Osaka ke Tokyo naik shinkansen memakan waktu sekitar 2,5 jam.', en: 'From Osaka to Tokyo by shinkansen takes about 2.5 hours.' } },
    { type: 'mc', q: '1週間《しゅうかん》＿＿＿ 2回《かい》 テニスを します。', choices: ['に', 'で', 'を', 'へ'], answer: 0 , translate: { id: 'Saya main tenis 2 kali seminggu.', en: 'I play tennis twice a week.' } },
    { type: 'scramble', translate: { id: 'Ada dua orang di depan pintu.', en: 'There are two people in front of the door.' }, words: ['ドア', 'の', '前《まえ》', 'に', '二人《ふたり》', 'います'] },
    { type: 'scramble', translate: { id: 'Tolong berikan 5 lembar perangko 80 yen.', en: 'Please give me 5 80-yen stamps.' }, words: ['80円《えん》', 'の', '切手《きって》', 'を', '5枚《まい》', 'ください'] },
    { type: 'scramble', translate: { id: 'Saya belajar bahasa Jepang di negara saya selama 6 bulan.', en: 'I studied Japanese in my country for about 6 months.' }, words: ['国《くに》', 'で', '6か月《ろっかげつ》', 'ぐらい', '日本語《にほんご》', 'を', '勉強《べんきょう》しました'] },
    { type: 'mc', q: 'この 荷物《にもつ》、航空便《こうくうびん》＿＿＿ お願《ねが》いします。', choices: ['で', 'を', 'に', 'が'], answer: 0, img: 'tabler-plane' , translate: { id: 'Tolong barang ini dikirim lewat pos udara.', en: 'Please send this package by airmail.' } },
    { type: 'scramble', translate: { id: 'Kakak laki-laki saya suka sepak bola.', en: 'My older brother likes soccer.' }, words: ['兄《あに》', 'は', 'サッカー', 'が', '好《す》きです'] },
    { type: 'mc', q: '店員《てんいん》：いらっしゃいませ。\n客《きゃく》：すみません、80円《えん》の　切手《きって》を　5枚《まい》　ください。\n店員《てんいん》：＿＿＿。', choices: ['はい、かしこまりました', 'いいですね', 'どういたしまして', 'また今度《こんど》'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Permisi, tolong 5 lembar perangko 80 yen. ...Baik, dimengerti.', en: 'Excuse me, 5 80-yen stamps, please. ...Certainly, sir/ma\'am.' } },
    { type: 'mc', q: 'A：ご家族《かぞく》は　何人《なんにん》ですか。\nB：＿＿＿。', choices: ['5人《にん》です', '5枚《まい》です', '5個《こ》です', '5台《だい》です'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Keluarga Anda berapa orang? ...5 orang.', en: 'How many people are in your family? ...5 people.' } },
    { type: 'mc', q: '1週間《しゅうかん》に 1回《かい》 両親《りょうしん》に 電話《でんわ》を＿＿＿。', choices: ['かけます', 'します', 'あります', 'います'], answer: 0 , translate: { id: 'Seminggu sekali saya menelepon orang tua.', en: 'I call my parents once a week.' } },
    { type: 'mc', q: '先月《せんげつ》 1週間《しゅうかん》 会社《かいしゃ》を＿＿＿。', choices: ['休《やす》みました', '休《やす》みます', '休《やす》みません', '休《やす》みだ'], answer: 0 , translate: { id: 'Bulan lalu saya libur kerja seminggu.', en: 'Last month I took a week off work.' } },
    { type: 'mc', q: '妹《いもうと》＿＿＿ 2人《ふたり》＿＿＿ います。', choices: ['が／×', 'は／を', 'に／で', 'を／に'], answer: 0 , translate: { id: 'Saya punya 2 orang adik perempuan.', en: 'I have 2 younger sisters.' } },
    { type: 'scramble', translate: { id: 'Perjalanan dari Osaka ke Tokyo memakan waktu sekitar 2 jam setengah dengan shinkansen.', en: 'The trip from Osaka to Tokyo takes about two and a half hours by shinkansen.' }, words: ['大阪《おおさか》', 'から', '東京《とうきょう》', 'まで', '新幹線《しんかんせん》', 'で', '2時間半《にじかんはん》', 'ぐらい', 'かかります'] },
    { type: 'scramble', translate: { id: 'Saya membeli 4 buah apel.', en: 'I bought 4 apples.' }, words: ['りんご', 'を', '4《よっ》つ', '買《か》いました'] },
    { type: 'scramble', translate: { id: 'Di depan toko ada sekitar 20 sepeda.', en: 'There are about 20 bicycles in front of the store.' }, words: ['店《みせ》', 'の', '前《まえ》', 'に', '自転車《じてんしゃ》', 'が', '20台《にじゅうだい》', 'ぐらい', 'あります'] },
    { type: 'scramble', translate: { id: 'Tolong kirim barang ini lewat pos udara.', en: 'Please send this package by airmail.' }, words: ['この', '荷物《にもつ》', '、', '航空便《こうくうびん》', 'で', 'お願《ねが》いします'] },
  ] },
  { id: 12, focus: '比較《ひかく》（より、の中《なか》で一番《いちばん》）', questions: [
    { type: 'mc', q: '地下鉄《ちかてつ》は バス＿＿＿ 速《はや》いです。', choices: ['より', 'から', 'まで', 'ほど'], answer: 0, img: 'tabler-train' , translate: { id: 'Kereta bawah tanah lebih cepat daripada bus.', en: 'The subway is faster than the bus.' } },
    { type: 'mc', q: 'スポーツ＿＿＿ サッカーが 一番《いちばん》 おもしろいです。', choices: ['で', 'に', 'と', 'を'], answer: 0, img: 'tabler-ball-football' , translate: { id: 'Di antara olahraga, sepak bola paling menarik.', en: 'Among sports, soccer is the most interesting.' } },
    { type: 'mc', q: '暑《あつ》い 季節《きせつ》＿＿＿ 寒《さむ》い 季節《きせつ》＿＿＿ どちら＿＿＿ いいですか。', choices: ['と／と／が', 'や／の／を', 'に／を／が', 'と／の／に'], answer: 0 , translate: { id: 'Antara musim panas dan musim dingin, mana yang lebih disukai?', en: 'Between summer and winter, which do you prefer?' } },
    { type: 'mc', q: '桜《さくら》＿＿＿ 日本《にほん》の 花《はな》＿＿＿ いちばん 有名《ゆうめい》です。', choices: ['は／で', 'が／と', 'の／に', 'を／が'], answer: 0, img: 'tabler-flower' , translate: { id: 'Di antara bunga Jepang, sakura yang paling terkenal.', en: 'Among Japanese flowers, cherry blossoms are the most famous.' } },
    { type: 'mc', q: '東京《とうきょう》は 大阪《おおさか》＿＿＿ 人《ひと》が 多《おお》いです。', choices: ['より', 'ほど', 'ので', 'でも'], answer: 0 , translate: { id: 'Di Tokyo orangnya lebih banyak daripada Osaka.', en: 'Tokyo has more people than Osaka.' } },
    { type: 'scramble', translate: { id: '1 tahun ini, Agustus yang paling panas.', en: 'This year, August is the hottest.' }, words: ['1年《いちねん》', 'で', '8月《はちがつ》', 'が', '一番《いちばん》', '暑《あつ》い', 'です'] },
    { type: 'scramble', translate: { id: 'Tokyo lebih besar daripada Osaka.', en: 'Tokyo is bigger than Osaka.' }, words: ['東京《とうきょう》', 'は', '大阪《おおさか》', 'より', '大《おお》きい', 'です'] },
    { type: 'scramble', translate: { id: 'Saya paling suka masakan Jepang.', en: 'I like Japanese food the best.' }, words: ['日本《にほん》', '料理《りょうり》', 'が', 'いちばん', '好《す》きです'] },
    { type: 'mc', q: '松本《まつもと》さん＿＿＿ 山田《やまだ》さん＿＿＿ どちら＿＿＿ ダンス＿＿＿ 上手《じょうず》ですか。', choices: ['と／と／が／が', 'や／の／を／に', 'に／を／が／と', 'と／の／に／が'], answer: 0 , translate: { id: 'Antara Matsumoto dan Yamada, siapa yang lebih pandai menari?', en: 'Between Matsumoto and Yamada, who is better at dancing?' } },
    { type: 'scramble', translate: { id: 'Toko roti dekat stasiun lebih murah daripada department store.', en: 'The bakery near the station is cheaper than the department store.' }, words: ['駅《えき》', 'の', '前《まえ》', 'の', 'パン屋《や》', 'は', 'デパート', 'より', '安《やす》い', 'です'] },
    { type: 'mc', q: 'A：日本《にほん》料理《りょうり》で　何《なに》が　いちばん　好《す》きですか。\nB：＿＿＿。', choices: ['すしが　いちばん　好《す》きです', 'すしより　好《す》きです', 'すしが　好《す》きじゃありません', 'すしが　嫌《きら》いです'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Di antara masakan Jepang, apa yang paling disukai? ...Saya paling suka sushi.', en: 'Among Japanese foods, what do you like best? ...I like sushi the best.' } },
    { type: 'mc', q: 'A：東京《とうきょう》と　大阪《おおさか》と　どちらが　好《す》きですか。\nB：＿＿＿。', choices: ['東京《とうきょう》の　ほうが　好《す》きです', '東京《とうきょう》が　嫌《きら》いです', 'どちらも　嫌《きら》いです', '大阪《おおさか》は　東京《とうきょう》です'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Antara Tokyo dan Osaka, lebih suka mana? ...Saya lebih suka Tokyo.', en: 'Between Tokyo and Osaka, which do you prefer? ...I prefer Tokyo.' } },
    { type: 'mc', q: '課長《かちょう》は＿＿＿と 言《い》いましたか。…あさって 名古屋《なごや》へ 出張《しゅっちょう》すると 言《い》いました。', choices: ['何《なん》', 'だれ', 'どこ', 'いつ'], answer: 0 , translate: { id: 'Kepala bagian bilang apa? ...Katanya lusa akan dinas ke Nagoya.', en: 'What did the section chief say? ...He said he\'ll go on a business trip to Nagoya the day after tomorrow.' } },
    { type: 'mc', q: '会社《かいしゃ》の 仕事《しごと》は＿＿＿ですか。…おもしろいです。', choices: ['どう', 'どんな', 'なん', 'いつ'], answer: 0 , translate: { id: 'Bagaimana pekerjaan di kantor? ...Menarik.', en: 'How is the work at the office? ...It\'s interesting.' } },
    { type: 'mc', q: 'タイは＿＿＿ 国《くに》ですか。…暑《あつ》い 国《くに》です。', choices: ['どんな', 'どう', 'なん', 'どこ'], answer: 0 , translate: { id: 'Thailand negara yang bagaimana? ...Negara yang panas.', en: 'What kind of country is Thailand? ...A hot country.' } },
    { type: 'scramble', translate: { id: 'Fuji University lebih tua daripada Sakura University.', en: 'Fuji University is older than Sakura University.' }, words: ['富士《ふじ》大学《だいがく》', 'は', 'さくら大学《だいがく》', 'より', '古《ふる》い', 'です'] },
    { type: 'scramble', translate: { id: 'Bunga sakura yang paling terkenal sebagai bunga Jepang.', en: 'Cherry blossoms are the most famous flower in Japan.' }, words: ['桜《さくら》', 'が', '日本《にほん》', 'の', '花《はな》', 'で', 'いちばん', '有名《ゆうめい》', 'です'] },
    { type: 'scramble', translate: { id: 'Musim panas dan musim dingin, mana yang Anda sukai?', en: 'Summer or winter, which do you like?' }, words: ['夏《なつ》', 'と', '冬《ふゆ》', 'と', 'どちら', 'が', '好《す》きですか'] },
    { type: 'scramble', translate: { id: 'Kamar dorm ini kecil tapi bersih.', en: 'This dorm room is small but clean.' }, words: ['この', '寮《りょう》', 'の', '部屋《へや》', 'は', '小《ちい》さい', 'です', 'が', '、', 'きれいです'] },
    { type: 'scramble', translate: { id: 'Di antara olahraga, sepak bola yang paling menarik.', en: 'Among sports, soccer is the most interesting.' }, words: ['スポーツ', 'で', 'サッカー', 'が', 'いちばん', 'おもしろい', 'です'] },
  ] },
  { id: 13, focus: '欲《ほ》しい、〜たい、〜に行《い》きます', questions: [
    { type: 'mc', q: '私《わたし》は 新《あたら》しい パソコン＿＿＿ 欲《ほ》しいです。', choices: ['が', 'を', 'に', 'は'], answer: 0, img: 'tabler-device-laptop' , translate: { id: 'Saya ingin komputer baru.', en: 'I want a new computer.' } },
    { type: 'mc', q: 'コーヒーを ＿＿＿です。（飲《の》みます→〜たい）', choices: ['飲《の》みたい', '飲《の》みます', '飲《の》みません', '飲《の》みたく'], answer: 0 , translate: { id: 'Saya ingin minum kopi.', en: 'I want to drink coffee.' } },
    { type: 'mc', q: 'のど＿＿＿ かわきましたから、何《なに》＿＿＿ 飲《の》みたいです。', choices: ['が／か', 'を／が', 'に／を', 'は／か'], answer: 0 , translate: { id: 'Karena tenggorokan haus, saya ingin minum sesuatu.', en: 'My throat is dry, so I want to drink something.' } },
    { type: 'mc', q: '疲《つか》れましたから、何《なに》も＿＿＿。', choices: ['したくないです', 'したいです', 'します', 'しました'], answer: 0 , translate: { id: 'Karena lelah, saya tidak ingin melakukan apa-apa.', en: 'I\'m tired, so I don\'t want to do anything.' } },
    { type: 'mc', q: '今《いま》 何《なに》＿＿＿ 欲《ほ》しくないです。', choices: ['も', 'が', 'を', 'は'], answer: 0 , translate: { id: 'Sekarang saya tidak ingin apa-apa.', en: 'I don\'t want anything right now.' } },
    { type: 'scramble', translate: { id: 'Saya pergi ke perpustakaan untuk meminjam buku.', en: 'I\'m going to the library to borrow a book.' }, words: ['図書館《としょかん》', 'へ', '本《ほん》', 'を', '借《か》りに', '行《い》きます'] },
    { type: 'scramble', translate: { id: 'Saya ingin cepat bertemu keluarga.', en: 'I want to see my family soon.' }, words: ['早《はや》く', '家族《かぞく》', 'に', '会《あ》いたい', 'です'] },
    { type: 'scramble', translate: { id: 'Minggu depan saya ingin pergi memancing.', en: 'Next week I want to go fishing.' }, words: ['来週《らいしゅう》', '釣《つ》り', 'に', '行《い》きたい', 'です'] },
    { type: 'mc', q: '夏休《なつやす》みに 北海道《ほっかいどう》＿＿＿ 旅行《りょこう》＿＿＿ 行《い》きます。', choices: ['へ／に', 'に／へ', 'で／を', 'を／に'], answer: 0, img: 'tabler-map' , translate: { id: 'Saat liburan musim panas saya akan bepergian ke Hokkaido.', en: 'During summer vacation I\'m going to travel to Hokkaido.' } },
    { type: 'scramble', translate: { id: 'Waktu dan uang, mana yang lebih Anda inginkan?', en: 'Between time and money, which do you want more?' }, words: ['時間《じかん》', 'と', 'お金《かね》', 'と', 'どちら', 'が', '欲《ほ》しいですか'] },
    { type: 'mc', q: 'A：次《つぎ》の　日曜日《にちようび》は　何《なに》を　したいですか。\nB：＿＿＿。', choices: ['映画《えいが》を　見《み》たいです', '映画《えいが》を　見《み》ます', '映画《えいが》が　好《す》きです', '映画《えいが》を　見《み》に　行《い》きました'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Minggu depan mau melakukan apa? ...Saya ingin menonton film.', en: 'What do you want to do next Sunday? ...I want to watch a movie.' } },
    { type: 'mc', q: 'A：どこへ　行《い》きますか。\nB：＿＿＿。', choices: ['本《ほん》を　借《か》りに　図書館《としょかん》へ　行《い》きます', '本《ほん》を　借《か》りたいです', '本《ほん》が　欲《ほ》しいです', '本《ほん》を　借《か》りて　います'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Mau pergi ke mana? ...Saya pergi ke perpustakaan untuk meminjam buku.', en: 'Where are you going? ...I\'m going to the library to borrow a book.' } },
    { type: 'mc', q: '疲《つか》れましたから、どこも＿＿＿。', choices: ['行《い》きたくないです', '行《い》きたいです', '行《い》きます', '行《い》きました'], answer: 0 , translate: { id: 'Karena lelah, saya tidak ingin pergi ke mana-mana.', en: 'I\'m tired, so I don\'t want to go anywhere.' } },
    { type: 'mc', q: '彼女《かのじょ》の 誕生日《たんじょうび》に すてきな プレゼントを＿＿＿。', choices: ['あげたいです', '欲《ほ》しいです', 'もらいたいです', 'くれたいです'], answer: 0, img: 'tabler-gift' , translate: { id: 'Saya ingin memberi hadiah bagus untuk ulang tahun pacar saya.', en: 'I want to give a nice present for my girlfriend\'s birthday.' } },
    { type: 'mc', q: '日曜日《にちようび》は 何《なに》を＿＿＿たいですか。…何《なに》も したくないです。', choices: ['し', 'する', 'した', 'して'], answer: 0 , translate: { id: 'Hari Minggu ingin melakukan apa? ...Saya tidak ingin melakukan apa-apa.', en: 'What do you want to do on Sunday? ...I don\'t want to do anything.' } },
    { type: 'scramble', translate: { id: 'Saya mau minum sesuatu karena haus.', en: 'I want to drink something because I\'m thirsty.' }, words: ['のど', 'が', 'かわきました', 'から', '、', '何《なに》か', '飲《の》みたいです'] },
    { type: 'scramble', translate: { id: 'Saya sekarang tidak ingin apa-apa.', en: 'I don\'t want anything right now.' }, words: ['わたし', 'は', '今《いま》', '何《なに》も', '欲《ほ》しくないです'] },
    { type: 'scramble', translate: { id: 'Sekarang saya ingin sepatu yang ringan.', en: 'Right now I want light shoes.' }, words: ['軽《かる》い', '靴《くつ》', 'が', '欲《ほ》しいです'] },
    { type: 'scramble', translate: { id: 'Akhir minggu saya pergi ke pantai untuk berenang.', en: 'On the weekend I went to the beach to swim.' }, words: ['週末《しゅうまつ》', '海《うみ》', 'へ', '泳《およ》ぎに', '行《い》きました'] },
    { type: 'scramble', translate: { id: 'Saya pergi ke restoran masakan Thailand untuk makan.', en: 'I went to a Thai restaurant to eat.' }, words: ['タイ料理《りょうり》', 'の', '店《みせ》', 'へ', '食事《しょくじ》', 'に', '行《い》きました'] },
  ] },
  { id: 14, focus: 'て形《けい》、〜てください', questions: [
    { type: 'mc', q: '「書《か》きます」の て形《けい》は？', choices: ['書《か》いて', '書《か》んで', '書《か》きて', '書《か》って'], answer: 0 , translate: { id: 'Bentuk te dari \'menulis\' apa?', en: 'What\'s the te-form of \'to write\'?' } },
    { type: 'mc', q: '「読《よ》みます」の て形《けい》は？', choices: ['読《よ》んで', '読《よ》いて', '読《よ》きて', '読《よ》って'], answer: 0 , translate: { id: 'Bentuk te dari \'membaca\' apa?', en: 'What\'s the te-form of \'to read\'?' } },
    { type: 'mc', q: '「来《き》ます」の て形《けい》は？', choices: ['来《き》て', '来《き》んで', '来《き》いて', '来《き》って'], answer: 0 , translate: { id: 'Bentuk te dari \'datang\' apa?', en: 'What\'s the te-form of \'to come\'?' } },
    { type: 'mc', q: '「急《いそ》ぎます」の て形《けい》は？', choices: ['急《いそ》いで', '急《いそ》いて', '急《いそ》んで', '急《いそ》って'], answer: 0 , translate: { id: 'Bentuk te dari \'bergegas\' apa?', en: 'What\'s the te-form of \'to hurry\'?' } },
    { type: 'mc', q: '「持《も》ちます」の て形《けい》は？', choices: ['持《も》って', '持《も》んで', '持《も》いて', '持《も》きて'], answer: 0 , translate: { id: 'Bentuk te dari \'memegang\' apa?', en: 'What\'s the te-form of \'to hold\'?' } },
    { type: 'mc', q: 'すみませんが、もう 一《いち》度《ど》＿＿＿。（言《い》います→〜てください）', choices: ['言《い》ってください', '言《い》きてください', '言《い》んでください', '言《い》てください'], answer: 0 , translate: { id: 'Maaf, tolong katakan sekali lagi.', en: 'Excuse me, please say it once more.' } },
    { type: 'scramble', translate: { id: 'Tolong buka jendela.', en: 'Please open the window.' }, words: ['窓《まど》', 'を', '開《あ》けて', 'ください'] },
    { type: 'scramble', translate: { id: 'Tolong beritahu nomor telepon Matsumoto.', en: 'Please tell me Matsumoto\'s phone number.' }, words: ['松本《まつもと》さん', 'の', '電話《でんわ》番号《ばんごう》', 'を', '教《おし》えて', 'ください'] },
    { type: 'scramble', translate: { id: 'Boleh saya pinjam gunting ini?', en: 'Could you lend me these scissors?' }, words: ['この', 'はさみ', 'を', '貸《か》して', 'ください'] },
    { type: 'mc', q: '暑《あつ》いですから、エアコンを＿＿＿。（つけます）', choices: ['つけてください', 'つけます', 'つけました', 'つける'], answer: 0 , translate: { id: 'Karena panas, tolong nyalakan AC.', en: 'It\'s hot, so please turn on the AC.' } },
    { type: 'scramble', translate: { id: 'Tolong kirimkan peta lewat email.', en: 'Please send the map by email.' }, words: ['地図《ちず》', 'を', 'メール', 'で', '送《おく》って', 'ください'] },
    { type: 'mc', q: 'A：すみませんが、ちょっと　手伝《てつだ》って　ください。\nB：＿＿＿。', choices: ['ええ、いいですよ', 'ええ、けっこうです', 'いいえ、まだです', 'どういたしまして'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Maaf, tolong bantu sebentar. ...Ya, boleh.', en: 'Excuse me, could you help me for a moment? ...Sure, no problem.' } },
    { type: 'mc', q: 'A：この　いす、座《すわ》っても　いいですか。\nB：＿＿＿。', choices: ['ええ、どうぞ', 'いいえ、まだです', 'かしこまりました', 'ごちそうさまでした'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Boleh saya duduk di kursi ini? ...Ya, silakan.', en: 'May I sit in this chair? ...Yes, please do.' } },
    { type: 'mc', q: '「開《あ》けます」の て形《けい》は？', choices: ['開《あ》けて', '開《あ》いて', '開《あ》けって', '開《あ》んで'], answer: 0 , translate: { id: 'Bentuk te dari \'membuka\' apa?', en: 'What\'s the te-form of \'to open\'?' } },
    { type: 'mc', q: '「降《ふ》ります」の て形《けい》は？', choices: ['降《ふ》って', '降《ふ》りて', '降《ふ》んで', '降《ふ》いて'], answer: 0 , translate: { id: 'Bentuk te dari \'turun (hujan)\' apa?', en: 'What\'s the te-form of \'to fall (rain)\'?' } },
    { type: 'mc', q: 'ちょっと そのはさみを＿＿＿。（貸《か》します→〜てください）', choices: ['貸《か》してください', '貸《か》りてください', '貸《か》んでください', '貸《か》ってください'], answer: 0 , translate: { id: 'Tolong pinjamkan gunting itu sebentar.', en: 'Please lend me those scissors for a moment.' } },
    { type: 'scramble', translate: { id: 'Tolong tunjukkan paspornya sekali lagi.', en: 'Please show me the passport once more.' }, words: ['パスポート', 'を', 'もう一度《いちど》', '見《み》せて', 'ください'] },
    { type: 'scramble', translate: { id: 'Sudah capek, jadi silakan istirahat di sini.', en: 'You must be tired, please rest here.' }, words: ['疲《つか》れました', '。', 'どうぞ', 'こちら', 'で', '休《やす》んで', 'ください'] },
    { type: 'scramble', translate: { id: 'Boleh tolong bantu saya sedikit?', en: 'Could you help me a little?' }, words: ['ちょっと', '手伝《てつだ》って', 'ください'] },
    { type: 'scramble', translate: { id: 'Tolong tuliskan nama dan alamat dengan pulpen.', en: 'Please write the name and address with a pen.' }, words: ['ボールペン', 'で', '住所《じゅうしょ》', 'と', '名前《なまえ》', 'を', '書《か》いて', 'ください'] },
  ] },
  { id: 15, focus: '〜ています（進行《しんこう》・状態《じょうたい》）', questions: [
    { type: 'mc', q: '今《いま》 新聞《しんぶん》を ＿＿＿。（読《よ》んでいます）', choices: ['読《よ》んでいます', '読《よ》みます', '読《よ》みました', '読《よ》む'], answer: 0, img: 'tabler-news' , translate: { id: 'Sekarang saya sedang membaca koran.', en: 'I\'m reading the newspaper right now.' } },
    { type: 'mc', q: 'どこに 住《す》んで＿＿＿か。', choices: ['います', 'あります', 'です', 'ました'], answer: 0 , translate: { id: 'Sedang tinggal di mana?', en: 'Where do you live?' } },
    { type: 'mc', q: '兄《あに》の 会社《かいしゃ》は 電気《でんき》製品《せいひん》を＿＿＿います。（作《つく》ります）', choices: ['作《つく》って', '作《つく》んで', '作《つく》きて', '作《つく》いて'], answer: 0 , translate: { id: 'Perusahaan kakak laki-laki saya membuat produk elektronik.', en: 'My older brother\'s company makes electrical products.' } },
    { type: 'mc', q: 'カリナさんは 日本《にほん》の 古《ふる》い 美術《びじゅつ》を＿＿＿います。（知《し》ります）', choices: ['知《し》って', '知《し》んで', '知《し》きて', '知《し》いて'], answer: 0 , translate: { id: 'Karina mengetahui seni kuno Jepang.', en: 'Karina knows about ancient Japanese art.' } },
    { type: 'mc', q: '教室《きょうしつ》で たばこを＿＿＿は いけません。', choices: ['吸《す》って', '吸《す》う', '吸《す》います', '吸《す》いた'], answer: 0 , translate: { id: 'Tidak boleh merokok di kelas.', en: 'You may not smoke in the classroom.' } },
    { type: 'scramble', translate: { id: 'Saya tinggal di Bandung.', en: 'I live in Bandung.' }, words: ['バンドン', 'に', '住《す》んでいます'] },
    { type: 'scramble', translate: { id: 'Apakah Anda punya kamus elektronik?', en: 'Do you have an electronic dictionary?' }, words: ['電子《でんし》辞書《じしょ》', 'を', '持《も》っていますか'] },
    { type: 'scramble', translate: { id: 'Saya tidak tahu cara membuat tempura.', en: 'I don\'t know how to make tempura.' }, words: ['てんぷら', 'の', '作《つく》り方《かた》', 'を', '知《し》りません'] },
    { type: 'mc', q: '弟《おとうと》は 結婚《けっこん》して＿＿＿。独身《どくしん》です。', choices: ['いません', 'います', 'でした', 'ました'], answer: 0 , translate: { id: 'Adik laki-laki saya belum menikah. Masih lajang.', en: 'My younger brother isn\'t married yet. He\'s still single.' } },
    { type: 'scramble', translate: { id: 'Apakah Anda mengetahui alamat Santos?', en: 'Do you know Santos\'s address?' }, words: ['サントスさん', 'の', '住所《じゅうしょ》', 'を', '知《し》っていますか'] },
    { type: 'mc', q: 'A：カリナさんは　今《いま》　何《なに》を　して　いますか。\nB：＿＿＿。', choices: ['花《はな》を　見《み》て　います', '花《はな》を　見《み》ます', '花《はな》を　見《み》ました', '花《はな》が　好《す》きです'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Karina sekarang sedang melakukan apa? ...Sedang melihat bunga.', en: 'What is Karina doing right now? ...She\'s looking at flowers.' } },
    { type: 'mc', q: 'A：今《いま》　どこに　住《す》んで　いますか。\nB：＿＿＿。', choices: ['バンドンに　住《す》んで　います', 'バンドンへ　行《い》きます', 'バンドンです', 'バンドンが　好《す》きです'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Sekarang tinggal di mana? ...Saya tinggal di Bandung.', en: 'Where do you live now? ...I live in Bandung.' } },
    { type: 'mc', q: 'サントスさんの 趣味《しゅみ》は 日本《にほん》の 古《ふる》い 美術《びじゅつ》を＿＿＿ ことです。', choices: ['集《あつ》める', '集《あつ》めます', '集《あつ》めた', '集《あつ》めて'], answer: 0 , translate: { id: 'Hobi Santos adalah mengumpulkan seni kuno Jepang.', en: 'Santos\'s hobby is collecting ancient Japanese art.' } },
    { type: 'mc', q: 'テレーザちゃんは 自転車《じてんしゃ》を＿＿＿います。', choices: ['持《も》って', '持《も》んで', '持《も》きて', '持《も》いて'], answer: 0, img: 'tabler-bike' , translate: { id: 'Theresa punya sepeda.', en: 'Theresa has a bicycle.' } },
    { type: 'mc', q: 'すみません、この カタログを＿＿＿もいいですか。', choices: ['もらって', 'もらう', 'もらった', 'もらい'], answer: 0 , translate: { id: 'Permisi, boleh saya minta katalog ini?', en: 'Excuse me, could I get this catalog?' } },
    { type: 'scramble', translate: { id: 'Adik laki-laki saya belum menikah, masih lajang.', en: 'My younger brother isn\'t married yet, he\'s still single.' }, words: ['弟《おとうと》', 'は', 'まだ', '結婚《けっこん》して', 'いません'] },
    { type: 'scramble', translate: { id: 'Apa yang sedang Anda kerjakan sekarang?', en: 'What are you doing right now?' }, words: ['いま', '何《なに》', 'を', 'して', 'いますか'] },
    { type: 'scramble', translate: { id: 'Kakak laki-laki saya bekerja di perusahaan komputer.', en: 'My older brother works at a computer company.' }, words: ['兄《あに》', 'は', 'コンピューター', 'の', '会社《かいしゃ》', 'で', '働《はたら》いて', 'います'] },
    { type: 'scramble', translate: { id: 'Tidak boleh minum alkohol di kelas.', en: 'You may not drink alcohol in the classroom.' }, words: ['教室《きょうしつ》', 'で', 'お酒《さけ》', 'を', '飲《の》んでは', 'いけません'] },
    { type: 'scramble', translate: { id: 'Beliau tinggal sendirian.', en: 'He/she lives alone.' }, words: ['一人《ひとり》', 'で', '住《す》んで', 'います'] },
  ] },
  { id: 16, focus: 'て形《けい》の 連続《れんぞく》（〜て、〜てから）', questions: [
    { type: 'mc', q: '大学《だいがく》を 出《で》て＿＿＿、会社《かいしゃ》に 入《はい》りました。', choices: ['から', 'まで', 'ので', 'し'], answer: 0 , translate: { id: 'Setelah lulus kuliah, saya masuk kerja di perusahaan.', en: 'After graduating from university, I got a job at a company.' } },
    { type: 'mc', q: '窓《まど》を＿＿＿、電気《でんき》を＿＿＿、事務所《じむしょ》を 出《で》ました。', choices: ['閉《し》めて／消《け》して', '閉《し》めます／消《け》します', '閉《し》めた／消《け》した', '閉《し》める／消《け》す'], answer: 0 , translate: { id: 'Setelah menutup jendela dan mematikan lampu, saya keluar dari kantor.', en: 'After closing the window and turning off the light, I left the office.' } },
    { type: 'mc', q: '仕事《しごと》が 終《お》わって＿＿＿、1時間《いちじかん》ぐらい プールで 泳《およ》ぎます。', choices: ['から', 'まで', 'ので', 'し'], answer: 0 , translate: { id: 'Setelah pekerjaan selesai, saya berenang di kolam sekitar 1 jam.', en: 'After finishing work, I swam in the pool for about an hour.' } },
    { type: 'mc', q: '「多《おお》い」の 反対《はんたい》は？', choices: ['少《すく》ない', '軽《かる》い', '暗《くら》い', '狭《せま》い'], answer: 0 , translate: { id: 'Lawan kata \'banyak\' apa? ...Sedikit.', en: 'What\'s the opposite of \'many\'? ...Few.' } },
    { type: 'mc', q: '「入《はい》ります」の 反対《はんたい》は？', choices: ['出《で》ます', '座《すわ》ります', '立《た》ちます', '閉《し》めます'], answer: 0 , translate: { id: 'Lawan kata \'masuk\' apa? ...Keluar.', en: 'What\'s the opposite of \'enter\'? ...Exit.' } },
    { type: 'scramble', translate: { id: 'Setelah mengerjakan PR, saya menonton TV.', en: 'After doing my homework, I watched TV.' }, words: ['宿題《しゅくだい》', 'を', 'して', 'から', '、', 'テレビ', 'を', '見《み》ます'] },
    { type: 'scramble', translate: { id: 'Tutup jendela, lalu keluar ruangan.', en: 'Close the window, then leave the room.' }, words: ['窓《まど》', 'を', '閉《し》めて', '、', '部屋《へや》', 'を', '出《で》ます'] },
    { type: 'scramble', translate: { id: 'Kanji itu sulit cara membacanya.', en: 'That kanji is hard to read.' }, words: ['漢字《かんじ》', 'は', '読《よ》み方《かた》', 'が', '難《むずか》しい', 'です'] },
    { type: 'mc', q: '地下鉄《ちかてつ》で 大阪《おおさか》まで＿＿＿、JRに 乗《の》り換《か》えてください。', choices: ['行《い》って', '行《い》きます', '行《い》った', '行《い》く'], answer: 0 , translate: { id: 'Naik kereta bawah tanah sampai Osaka, lalu tolong pindah ke JR.', en: 'Take the subway to Osaka, then please transfer to the JR line.' } },
    { type: 'scramble', translate: { id: 'Saya turun dari bus lalu berjalan ke rumah Tanaka.', en: 'I got off the bus and walked to Tanaka\'s house.' }, words: ['バス', 'を', '降《お》りて', '、', '田中《たなか》さん', 'の', 'うち', 'まで', '歩《ある》いて', '行《い》きました'] },
    { type: 'mc', q: 'A：毎朝《まいあさ》　どんな　順番《じゅんばん》で　準備《じゅんび》しますか。\nB：＿＿＿。', choices: ['顔《かお》を　洗《あら》って　から、朝《あさ》ごはんを　食《た》べます', '顔《かお》を　洗《あら》います', '朝《あさ》ごはんを　食《た》べました', '顔《かお》を　洗《あら》いたいです'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Setiap pagi bersiap dengan urutan bagaimana? ...Setelah mencuci muka, saya sarapan.', en: 'What order do you get ready in every morning? ...After washing my face, I eat breakfast.' } },
    { type: 'mc', q: 'A：会議《かいぎ》の　まえに　何《なに》を　しますか。\nB：＿＿＿。', choices: ['資料《しりょう》を　集《あつ》めて、コピーします', '資料《しりょう》が　あります', '資料《しりょう》を　集《あつ》めたいです', '資料《しりょう》は　集《あつ》めません'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Sebelum rapat melakukan apa? ...Mengumpulkan berkas lalu memfotokopi.', en: 'What do you do before the meeting? ...I gather the documents and make copies.' } },
    { type: 'mc', q: '「暗《くら》い」の 反対《はんたい》は？', choices: ['明《あか》るい', '狭《せま》い', '軽《かる》い', '短《みじか》い'], answer: 0 , translate: { id: 'Lawan kata \'gelap\' apa? ...Terang.', en: 'What\'s the opposite of \'dark\'? ...Bright.' } },
    { type: 'mc', q: '「長《なが》い」の 反対《はんたい》は？', choices: ['短《みじか》い', '狭《せま》い', '遠《とお》い', '暗《くら》い'], answer: 0 , translate: { id: 'Lawan kata \'panjang\' apa? ...Pendek.', en: 'What\'s the opposite of \'long\'? ...Short.' } },
    { type: 'mc', q: 'バスを＿＿＿から、田中《たなか》さんの うちまで 歩《ある》いて 行《い》きました。（降《お》ります）', choices: ['降《お》りて', '降《お》りた', '降《お》りる', '降《お》りない'], answer: 0 , translate: { id: 'Setelah turun dari bus, saya berjalan kaki ke rumah Tanaka.', en: 'After getting off the bus, I walked to Tanaka\'s house.' } },
    { type: 'scramble', translate: { id: 'Bandung terkenal karena banyak hijau dan tenang.', en: 'Bandung is famous for being green and quiet.' }, words: ['バンドン', 'は', '緑《みどり》', 'が', '多《おお》くて', '静《しず》か', 'です'] },
    { type: 'scramble', translate: { id: 'Masakan Thailand punya rasa pedas dan asam yang enak.', en: 'Thai food has a delicious spicy and sour taste.' }, words: ['タイ料理《りょうり》', 'は', '辛《から》くて', '酸《す》っぱくて', 'おいしい', 'です'] },
    { type: 'scramble', translate: { id: 'Saya makan siang lalu tidur siang sebentar.', en: 'I ate lunch and then took a short nap.' }, words: ['昼《ひる》ごはん', 'を', '食《た》べて', '、', '少《すこ》し', '昼寝《ひるね》', 'を', 'します'] },
    { type: 'scramble', translate: { id: 'Setelah keluar dari kantor, saya langsung pulang.', en: 'After leaving the office, I went straight home.' }, words: ['事務所《じむしょ》', 'を', '出《で》て', 'から', '、', 'すぐ', '帰《かえ》ります'] },
    { type: 'scramble', translate: { id: 'Guru Watt orang Inggris dan mengajar bahasa Inggris.', en: 'Mr./Ms. Watt is British and teaches English.' }, words: ['ワット先生《せんせい》', 'は', 'イギリス人《じん》', 'で', '、', '英語《えいご》', 'の', '先生《せんせい》', 'です'] },
  ] },
  { id: 17, focus: '〜なければなりません／〜なくてもいいです／〜ないでください', questions: [
    { type: 'mc', q: '明日《あした》は 早《はや》く 起《お》き＿＿＿。（義務《ぎむ》）', choices: ['なければなりません', 'なくてもいいです', 'ないでください', 'てください'], answer: 0 , translate: { id: 'Besok saya harus bangun pagi.', en: 'Tomorrow I have to wake up early.' } },
    { type: 'mc', q: 'あした 心配《しんぱい》し＿＿＿。（しなくてもいい）', choices: ['なくてもいいです', 'なければなりません', 'ないでください', 'ています'], answer: 0 , translate: { id: 'Besok tidak perlu khawatir.', en: 'You don\'t need to worry tomorrow.' } },
    { type: 'mc', q: '暗証《あんしょう》番号《ばんごう》は 大切《たいせつ》ですから、＿＿＿。', choices: ['忘《わす》れないでください', '忘《わす》れてください', '忘《わす》れなければなりません', '忘《わす》れています'], answer: 0 , translate: { id: 'Karena PIN itu penting, jangan sampai lupa.', en: 'Since the PIN is important, don\'t forget it.' } },
    { type: 'mc', q: 'この 本《ほん》は 来週《らいしゅう》の 火曜日《かようび》＿＿＿に 返《かえ》さなければ なりません。', choices: ['まで', 'までに', 'から', 'に「×」'], answer: 1 , translate: { id: 'Buku ini harus dikembalikan paling lambat hari Selasa depan.', en: 'This book must be returned by next Tuesday at the latest.' } },
    { type: 'mc', q: '危《あぶ》ないですから、うしろから＿＿＿。', choices: ['押《お》さないでください', '押《お》してください', '押《お》しました', '押《お》します'], answer: 0 , translate: { id: 'Karena berbahaya, jangan dorong dari belakang.', en: 'It\'s dangerous, so please don\'t push from behind.' } },
    { type: 'scramble', translate: { id: 'Jangan lupa kata sandi.', en: 'Don\'t forget your password.' }, words: ['暗証番号《あんしょうばんごう》', 'を', '忘《わす》れないで', 'ください'] },
    { type: 'scramble', translate: { id: 'Apakah hari Minggu juga harus bangun pagi?', en: 'Do I have to wake up early on Sundays too?' }, words: ['日曜日《にちようび》', 'も', '早《はや》く', '起《お》きなければ', 'なりませんか'] },
    { type: 'scramble', translate: { id: 'Nomor telepon tidak perlu ditulis.', en: 'You don\'t need to write the phone number.' }, words: ['電話《でんわ》番号《ばんごう》', 'は', '書《か》かなくても', 'いいです'] },
    { type: 'mc', q: '両親《りょうしん》が 来《き》ますから、空港《くうこう》へ 迎《むか》えに＿＿＿。', choices: ['行《い》かなければなりません', '行《い》かなくてもいいです', '行《い》かないでください', '行《い》っています'], answer: 0 , translate: { id: 'Karena orang tua akan datang, saya harus menjemput ke bandara.', en: 'Since my parents are coming, I have to pick them up at the airport.' } },
    { type: 'scramble', translate: { id: 'Karena ujian mudah, tidak perlu khawatir.', en: 'The exam is easy, so you don\'t need to worry.' }, words: ['試験《しけん》', 'は', '簡単《かんたん》', 'です', 'から', '、', '心配《しんぱい》', 'しなくても', 'いいです'] },
    { type: 'mc', q: 'A：日曜日《にちようび》も　早《はや》く　起《お》きなければ　なりませんか。\nB：いいえ、＿＿＿。', choices: ['起《お》きなくても　いいです', '起《お》きなければ　なりません', '起《お》きないで　ください', '起《お》きて　います'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Hari Minggu juga harus bangun pagi? ...Tidak, tidak perlu bangun pagi.', en: 'Do you have to wake up early on Sundays too? ...No, you don\'t need to wake up early.' } },
    { type: 'mc', q: 'A：ここで　写真《しゃしん》を　撮《と》っても　いいですか。\nB：すみません、＿＿＿。', choices: ['撮《と》らないで　ください', '撮《と》っても　いいです', '撮《と》らなくても　いいです', '撮《と》って　います'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Boleh memotret di sini? ...Maaf, jangan memotret.', en: 'May I take a photo here? ...I\'m sorry, please don\'t take photos.' } },
    { type: 'mc', q: '両親《りょうしん》が 病気《びょうき》ですから、早《はや》く＿＿＿。', choices: ['帰《かえ》らなければなりません', '帰《かえ》らなくてもいいです', '帰《かえ》らないでください', '帰《かえ》っています'], answer: 0 , translate: { id: 'Karena orang tua sakit, saya harus segera pulang.', en: 'Since my parents are sick, I have to go home quickly.' } },
    { type: 'mc', q: '子《こ》どもが 病気《びょうき》ですから、電話《でんわ》を＿＿＿。', choices: ['かけなければなりません', 'かけなくてもいいです', 'かけないでください', 'かけています'], answer: 0 , translate: { id: 'Karena anak sedang sakit, saya harus menelepon.', en: 'Since my child is sick, I have to make a phone call.' } },
    { type: 'scramble', translate: { id: 'Karena resep ini mudah, tidak perlu khawatir.', en: 'This recipe is easy, so you don\'t need to worry.' }, words: ['この', 'レシピ', 'は', '簡単《かんたん》', 'です', 'から', '、', '心配《しんぱい》', 'しなくても', 'いいです'] },
    { type: 'scramble', translate: { id: 'Ini penting, jangan sampai hilang.', en: 'This is important, so don\'t lose it.' }, words: ['これ', 'は', '大切《たいせつ》', 'です', 'から', '、', 'なくさないで', 'ください'] },
    { type: 'scramble', translate: { id: 'Karena punya banyak pekerjaan, saya harus lembur hari ini.', en: 'I have a lot of work, so I have to work overtime today.' }, words: ['仕事《しごと》', 'が', 'たくさん', 'あります', 'から', '、', 'きょう', '残業《ざんぎょう》', 'しなければなりません'] },
    { type: 'scramble', translate: { id: 'Tolong lepas sepatu di sini.', en: 'Please take off your shoes here.' }, words: ['ここ', 'で', '靴《くつ》', 'を', '脱《ぬ》いで', 'ください'] },
    { type: 'scramble', translate: { id: 'Nomor telepon tidak perlu ditulis di sana.', en: 'You don\'t need to write the phone number there.' }, words: ['電話《でんわ》番号《ばんごう》', 'は', 'そこ', 'に', '書《か》かなくても', 'いいです'] },
    { type: 'scramble', translate: { id: 'Barang ini harus dikirim sebelum hari Jumat.', en: 'This package must be sent by Friday.' }, words: ['この', '荷物《にもつ》', 'は', '金曜日《きんようび》', 'までに', '送《おく》らなければ', 'なりません'] },
  ] },
  { id: 18, focus: '〜ことができます、辞書形《じしょけい》', questions: [
    { type: 'mc', q: '「食《た》べます」の 辞書《じしょ》形《けい》は？', choices: ['食《た》べる', '食《た》べて', '食《た》べます', '食《た》べた'], answer: 0 , translate: { id: 'Bentuk kamus dari \'makan\' apa?', en: 'What\'s the dictionary form of \'to eat\'?' } },
    { type: 'mc', q: '「話《はな》します」の 辞書《じしょ》形《けい》は？', choices: ['話《はな》す', '話《はな》して', '話《はな》した', '話《はな》します'], answer: 0 , translate: { id: 'Bentuk kamus dari \'berbicara\' apa?', en: 'What\'s the dictionary form of \'to speak\'?' } },
    { type: 'mc', q: '「来《き》ます」の 辞書《じしょ》形《けい》は？', choices: ['来《く》る', '来《き》て', '来《き》た', '来《き》ない'], answer: 0 , translate: { id: 'Bentuk kamus dari \'datang\' apa?', en: 'What\'s the dictionary form of \'to come\'?' } },
    { type: 'mc', q: 'この 図書館《としょかん》の 本《ほん》は 2週間《にしゅうかん》＿＿＿ こと＿＿＿ できます。（借《か》ります）', choices: ['借《か》りる／が', '借《か》りて／を', '借《か》りた／に', '借《か》り／で'], answer: 0 , translate: { id: 'Buku perpustakaan ini bisa dipinjam selama 2 minggu.', en: 'Books from this library can be borrowed for 2 weeks.' } },
    { type: 'mc', q: '漢字《かんじ》が 分《わ》かりませんから、日本語《にほんご》の 新聞《しんぶん》を＿＿＿。', choices: ['読《よ》むことができません', '読《よ》みたいです', '読《よ》んでいます', '読《よ》んでください'], answer: 0 , translate: { id: 'Karena tidak mengerti kanji, saya tidak bisa membaca koran Jepang.', en: 'I can\'t read Japanese newspapers because I don\'t understand kanji.' } },
    { type: 'scramble', translate: { id: 'Saya bisa berenang 50 meter.', en: 'I can swim 50 meters.' }, words: ['50メートル', '泳《およ》ぐ', 'こと', 'が', 'できます'] },
    { type: 'scramble', translate: { id: 'Hobi saya adalah memotret bunga.', en: 'My hobby is taking photos of flowers.' }, words: ['私《わたし》', 'の', '趣味《しゅみ》', 'は', '花《はな》', 'の', '写真《しゃしん》', 'を', '撮《と》る', 'こと', 'です'] },
    { type: 'scramble', translate: { id: 'Sebelum ke Jepang, apakah Anda belajar bahasa Jepang?', en: 'Did you study Japanese before coming to Japan?' }, words: ['日本《にほん》', 'へ', '来《く》る', 'まえに', '、', '日本語《にほんご》', 'を', '勉強《べんきょう》しましたか'] },
    { type: 'mc', q: 'ここは 朝《あさ》10時《じ》から 見学《けんがく》する ＿＿＿ が できます。', choices: ['こと', 'もの', 'とき', 'ところ'], answer: 0 , translate: { id: 'Di sini bisa berkeliling melihat-lihat mulai jam 10 pagi.', en: 'Here you can tour and look around from 10 a.m.' } },
    { type: 'scramble', translate: { id: 'Sepuluh tahun lalu saya belajar bahasa Prancis, tapi sudah lupa.', en: 'Ten years ago I studied French, but I\'ve already forgotten it.' }, words: ['10年《じゅうねん》', 'まえに', '、', 'フランス語《ご》', 'を', '習《なら》いましたが', '、', 'もう', '忘《わす》れました'] },
    { type: 'mc', q: 'A：日本語《にほんご》で　歌《うた》を　歌《うた》う　ことが　できますか。\nB：＿＿＿。', choices: ['はい、できます', 'はい、します', 'はい、あります', 'はい、います'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Bisakah menyanyi lagu dalam bahasa Jepang? ...Ya, bisa.', en: 'Can you sing songs in Japanese? ...Yes, I can.' } },
    { type: 'mc', q: 'A：車《くるま》の　運転《うんてん》が　できますか。\nB：いいえ、＿＿＿。', choices: ['できません', 'しません', 'ありません', 'いません'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Bisa menyetir mobil? ...Tidak, tidak bisa.', en: 'Can you drive a car? ...No, I can\'t.' } },
    { type: 'mc', q: '妹《いもうと》は さくら大学《だいがく》に＿＿＿ ことが できました。', choices: ['入《はい》る', '入《はい》った', '入《はい》って', '入《はい》り'], answer: 0 , translate: { id: 'Adik perempuan saya berhasil masuk Universitas Sakura.', en: 'My younger sister got into Sakura University.' } },
    { type: 'mc', q: '狭《せま》いですから、大《おお》きい 机《つくえ》を 置《お》く ことが＿＿＿。', choices: ['できません', 'しません', 'ありません', 'いません'], answer: 0 , translate: { id: 'Karena sempit, tidak bisa meletakkan meja besar.', en: 'It\'s cramped, so we can\'t put a big table.' } },
    { type: 'mc', q: 'わたしの 趣味《しゅみ》は 外国《がいこく》の 切手《きって》を＿＿＿ ことです。', choices: ['集《あつ》める', '集《あつ》めます', '集《あつ》めた', '集《あつ》めて'], answer: 0 , translate: { id: 'Hobi saya adalah mengumpulkan perangko luar negeri.', en: 'My hobby is collecting foreign stamps.' } },
    { type: 'scramble', translate: { id: 'Karena mabuk, saya tidak bisa menyetir mobil.', en: 'I can\'t drive because I\'m drunk.' }, words: ['お酒《さけ》', 'を', '飲《の》みました', 'から', '、', '車《くるま》', 'を', '運転《うんてん》する', 'こと', 'が', 'できません'] },
    { type: 'scramble', translate: { id: 'Apakah bisa memotret dengan ponsel?', en: 'Can you take photos with a phone?' }, words: ['ケータイ', 'で', '写真《しゃしん》', 'を', '撮《と》る', 'こと', 'が', 'できますか'] },
    { type: 'scramble', translate: { id: 'Sebelum tidur, saya selalu mandi.', en: 'Before going to sleep, I always take a bath.' }, words: ['寝《ね》る', 'まえに', '、', 'いつも', 'シャワー', 'を', '浴《あ》びます'] },
    { type: 'scramble', translate: { id: 'Berapa meter kamu bisa berenang?', en: 'How many meters can you swim?' }, words: ['何《なん》メートル', 'ぐらい', '泳《およ》ぐ', 'こと', 'が', 'できますか'] },
    { type: 'scramble', translate: { id: 'Apakah bisa memasak berbagai masakan negara?', en: 'Can you cook various national dishes?' }, words: ['いろいろな', '国《くに》', 'の', '料理《りょうり》', 'を', '作《つく》る', 'こと', 'が', 'できますか'] },
  ] },
  { id: 19, focus: '〜たことがあります（経験《けいけん》）、〜たり〜たり', questions: [
    { type: 'mc', q: '富士山《ふじさん》に 登《のぼ》った ＿＿＿ が あります。', choices: ['こと', 'もの', 'とき', 'ところ'], answer: 0, img: 'tabler-mountain' , translate: { id: 'Saya pernah mendaki Gunung Fuji.', en: 'I\'ve climbed Mt. Fuji before.' } },
    { type: 'mc', q: '相撲《すもう》を＿＿＿ ことが ありますか。…いいえ、一度《いちど》も ありません。', choices: ['見《み》た', '見《み》る', '見《み》て', '見《み》ます'], answer: 0 , translate: { id: 'Pernah menonton sumo? ...Tidak, belum pernah sekali pun.', en: 'Have you ever watched sumo? ...No, never.' } },
    { type: 'mc', q: 'この 果物《くだもの》を 食《た》べた ことが ありますか。…いいえ、＿＿＿です。', choices: ['初《はじ》めて', 'もう一度《いちど》', 'なかなか', 'だんだん'], answer: 0 , translate: { id: 'Pernah makan buah ini? ...Tidak, ini pertama kalinya.', en: 'Have you ever eaten this fruit? ...No, this is my first time.' } },
    { type: 'mc', q: '休《やす》みの 日《ひ》は 本《ほん》を＿＿＿り、テレビを＿＿＿り します。', choices: ['読《よ》んだ／見《み》た', '読《よ》む／見《み》る', '読《よ》んで／見《み》て', '読《よ》み／見《み》'], answer: 0 , translate: { id: 'Hari libur saya membaca buku dan menonton TV.', en: 'On my day off I read books and watch TV.' } },
    { type: 'mc', q: '子《こ》どもは お酒《さけ》を＿＿＿り、たばこを＿＿＿り しては いけません。', choices: ['飲《の》んだ／吸《す》った', '飲《の》む／吸《す》う', '飲《の》んで／吸《す》って', '飲《の》み／吸《す》い'], answer: 0 , translate: { id: 'Anak-anak tidak boleh minum sake dan merokok.', en: 'Children may not drink sake or smoke.' } },
    { type: 'scramble', translate: { id: 'Akhir pekan saya membaca buku dan menonton TV.', en: 'On weekends I read books and watch TV.' }, words: ['本《ほん》', 'を', '読《よ》んだり', '、', 'テレビ', 'を', '見《み》たり', 'します'] },
    { type: 'scramble', translate: { id: 'Apakah Anda pernah menulis surat dengan bahasa Jepang?', en: 'Have you ever written a letter in Japanese?' }, words: ['日本語《にほんご》', 'で', '手紙《てがみ》', 'を', '書《か》いた', 'こと', 'が', 'ありますか'] },
    { type: 'scramble', translate: { id: 'Bahasa Jepang saya menjadi semakin lancar.', en: 'My Japanese is becoming more fluent.' }, words: ['日本語《にほんご》', 'が', 'だんだん', '上手《じょうず》', 'に', 'なりました'] },
    { type: 'mc', q: '何《なん》回《かい》ぐらい ディズニーランドへ 行《い》った ことが＿＿＿か。', choices: ['あります', 'います', 'でした', 'ました'], answer: 0, img: 'tabler-ferris-wheel' , translate: { id: 'Sudah berapa kali pergi ke Disneyland?', en: 'How many times have you been to Disneyland?' } },
    { type: 'scramble', translate: { id: 'Saya sudah pernah naik ke Gunung Fuji sekali.', en: 'I\'ve climbed Mt. Fuji once.' }, words: ['富士山《ふじさん》', 'に', '一度《いちど》', '登《のぼ》った', 'こと', 'が', 'あります'] },
    { type: 'mc', q: 'A：富士山《ふじさん》に　登《のぼ》った　ことが　ありますか。\nB：いいえ、＿＿＿。ぜひ　登《のぼ》りたいです。', choices: ['一度《いちど》も　ありません', 'もう　一度《いちど》です', '一度《いちど》　あります', 'ときどき　あります'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Pernah mendaki Gunung Fuji? ...Belum pernah sekali pun. Saya ingin sekali mendaki.', en: 'Have you ever climbed Mt. Fuji? ...Never. I really want to climb it.' } },
    { type: 'mc', q: 'A：休《やす》みの　日《ひ》は　何《なに》を　しますか。\nB：＿＿＿。', choices: ['本《ほん》を　読《よ》んだり、テレビを　見《み》たり　します', '本《ほん》を　読《よ》んで、テレビを　見《み》ます', '本《ほん》が　好《す》きです', '本《ほん》を　読《よ》みたい　です'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Hari libur biasanya melakukan apa? ...Membaca buku dan menonton TV.', en: 'What do you usually do on your day off? ...Read books and watch TV.' } },
    { type: 'mc', q: '国《くに》で 日本語《にほんご》の CDを＿＿＿り、漢字《かんじ》を＿＿＿り しなければ なりません。', choices: ['聞《き》いた／覚《おぼ》えた', '聞《き》く／覚《おぼ》える', '聞《き》いて／覚《おぼ》えて', '聞《き》き／覚《おぼ》え'], answer: 0 , translate: { id: 'Di negara asal saya harus mendengarkan CD bahasa Jepang dan menghafal kanji.', en: 'In my home country I have to listen to Japanese CDs and memorize kanji.' } },
    { type: 'mc', q: 'ドイツ語《ご》を＿＿＿ ことが ありますか。…ええ。でも、もう 忘《わす》れましたから、もう一度《いちど》＿＿＿たいです。', choices: ['習《なら》った／習《なら》い', '習《なら》う／習《なら》った', '習《なら》って／習《なら》う', '習《なら》い／習《なら》って'], answer: 0 , translate: { id: 'Pernah belajar bahasa Jerman? ...Ya, tapi sudah lupa, ingin belajar lagi.', en: 'Have you ever studied German? ...Yes, but I\'ve forgotten it, I want to study it again.' } },
    { type: 'scramble', translate: { id: 'Apakah Anda pernah menyanyikan lagu Jepang?', en: 'Have you ever sung a Japanese song?' }, words: ['日本《にほん》', 'の', '歌《うた》', 'を', '歌《うた》った', 'こと', 'が', 'ありますか'] },
    { type: 'scramble', translate: { id: 'Bahasa Jepang saya belum menjadi lancar.', en: 'My Japanese isn\'t fluent yet.' }, words: ['日本語《にほんご》', 'が', 'まだ', '上手《じょうず》', 'に', 'なりません'] },
    { type: 'scramble', translate: { id: 'Sudah bulan September, mulai jadi sejuk.', en: 'It\'s already September, so it\'s starting to get cool.' }, words: ['もう', '9月《くがつ》', 'です', 'ね', '。', 'これから', '涼《すず》しく', 'なりますよ'] },
    { type: 'scramble', translate: { id: 'Anak itu sudah menjadi besar ya, umur berapa sekarang?', en: 'That child has grown so big, how old is he/she now?' }, words: ['あの', '子《こ》', 'は', '大《おお》きく', 'なりました', 'ね', '。', '何歳《なんさい》', 'ですか'] },
    { type: 'scramble', translate: { id: 'Apakah Anda pernah diet?', en: 'Have you ever been on a diet?' }, words: ['ダイエット', 'を', 'した', 'こと', 'が', 'ありますか'] },
    { type: 'scramble', translate: { id: 'Sudah pernah mendaki gunung di Jepang?', en: 'Have you climbed a mountain in Japan before?' }, words: ['日本《にほん》', 'で', '山《やま》', 'に', '登《のぼ》った', 'こと', 'が', 'ありますか'] },
  ] },
  { id: 20, focus: '普通形《ふつうけい》（会話《かいわ》で使《つか》う形《かたち》）', questions: [
    { type: 'mc', q: '「分《わ》かりますか」の 普通《ふつう》形《けい》は？', choices: ['分《わ》かる？', '分《わ》かります？', '分《わ》かった？', '分《わ》かって？'], answer: 0 , translate: { id: 'Bentuk biasa dari \'mengerti?\' apa?', en: 'What\'s the plain form of \'wakaru?\' (do you understand)?' } },
    { type: 'mc', q: '「行《い》きませんでした」の 普通《ふつう》形《けい》は？', choices: ['行《い》かなかった', '行《い》かない', '行《い》った', '行《い》かなくて'], answer: 0 , translate: { id: 'Bentuk biasa dari \'tidak pergi\' apa?', en: 'What\'s the plain form of \'doesn\'t go\'?' } },
    { type: 'mc', q: '「休《やす》みでした」の 普通《ふつう》形《けい》は？', choices: ['休《やす》みだった', '休《やす》みじゃない', '休《やす》み', '休《やす》みでは'], answer: 0 , translate: { id: 'Bentuk biasa dari \'libur\' apa?', en: 'What\'s the plain form of \'day off\'?' } },
    { type: 'mc', q: '「欲《ほ》しくないです」の 普通《ふつう》形《けい》は？', choices: ['欲《ほ》しくない', '欲《ほ》しかった', '欲《ほ》しくて', '欲《ほ》しいだ'], answer: 0 , translate: { id: 'Bentuk biasa dari \'tidak ingin\' apa?', en: 'What\'s the plain form of \'don\'t want\'?' } },
    { type: 'mc', q: '英語《えいご》が 分《わ》かる？…うん、＿＿＿。', choices: ['できる', 'できます', 'できた', 'できて'], answer: 0 , translate: { id: 'Mengerti bahasa Inggris? ...Ya, bisa.', en: 'Do you understand English? ...Yes, I can.' } },
    { type: 'scramble', translate: { id: 'Apakah kamu tahu alamat Karina?', en: 'Do you know Karina\'s address?' }, words: ['カリナさん', 'の', '住所《じゅうしょ》', 'を', '知《し》っている？'] },
    { type: 'scramble', translate: { id: 'Cuaca kemarin bagus.', en: 'The weather was nice yesterday.' }, words: ['きのう', 'は', '天気《てんき》', 'が', 'よかった'] },
    { type: 'mc', q: '「便利《べんり》です」の 普通《ふつう》形《けい》は？', choices: ['便利《べんり》だ', '便利《べんり》い', '便利《べんり》じゃ', '便利《べんり》な'], answer: 0 , translate: { id: 'Bentuk biasa dari \'praktis\' apa?', en: 'What\'s the plain form of \'convenient\'?' } },
    { type: 'scramble', translate: { id: 'Apakah kamu pergi berenang di laut Jepang?', en: 'Did you go swimming in the sea in Japan?' }, words: ['日本《にほん》', 'の', '海《うみ》', 'で', '泳《およ》いだ', 'こと', 'が', 'ある？'] },
    { type: 'mc', q: 'A：あした　いっしょに　行《い》く？\nB：うん、＿＿＿。', choices: ['行《い》く', '行《い》きます', '行《い》った', '行《い》って'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Besok pergi bersama? ...Ya, pergi.', en: 'Are you going tomorrow together? ...Yes, I\'m going.' } },
    { type: 'mc', q: 'A：きのう、山田《やまだ》さんに　会《あ》った？\nB：ううん、＿＿＿。', choices: ['会《あ》わなかった', '会《あ》いません', '会《あ》います', '会《あ》って'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Kemarin bertemu Yamada? ...Tidak, tidak bertemu.', en: 'Did you meet Yamada yesterday? ...No, I didn\'t.' } },
    { type: 'mc', q: '「無理《むり》じゃありません」の 普通《ふつう》形《けい》は？', choices: ['無理《むり》じゃない', '無理《むり》だ', '無理《むり》くない', '無理《むり》じゃなかった'], answer: 0 , translate: { id: 'Bentuk biasa dari \'tidak memaksakan diri\' apa?', en: 'What\'s the plain form of \'not overdo it\'?' } },
    { type: 'mc', q: '「修理《しゅうり》します」の 普通《ふつう》形《けい》は？', choices: ['修理《しゅうり》する', '修理《しゅうり》した', '修理《しゅうり》して', '修理《しゅうり》しない'], answer: 0 , translate: { id: 'Bentuk biasa dari \'memperbaiki\' apa?', en: 'What\'s the plain form of \'to fix\'?' } },
    { type: 'mc', q: 'カリナさんの 住所《じゅうしょ》を 知《し》っている？…ううん、＿＿＿。', choices: ['知《し》らない', '知《し》っています', '知《し》る', '知《し》った'], answer: 0 , translate: { id: 'Tahu alamat Karina? ...Tidak, tidak tahu.', en: 'Do you know Karina\'s address? ...No, I don\'t.' } },
    { type: 'mc', q: 'ビザが 要《い》る？…ううん、＿＿＿。', choices: ['要《い》らない', '要《い》ります', '要《い》る', '要《い》った'], answer: 0 , translate: { id: 'Perlu visa? ...Tidak, tidak perlu.', en: 'Do you need a visa? ...No, you don\'t.' } },
    { type: 'scramble', translate: { id: 'Apakah kamu bisa berbahasa Inggris?', en: 'Can you speak English?' }, words: ['英語《えいご》', 'が', 'わかる？'] },
    { type: 'scramble', translate: { id: 'Kemarin tidak hujan.', en: 'It didn\'t rain yesterday.' }, words: ['きのう', 'は', '雨《あめ》', 'が', '降《ふ》らなかった'] },
    { type: 'scramble', translate: { id: 'Aku tidak butuh apa-apa sekarang.', en: 'I don\'t need anything right now.' }, words: ['いま', '何《なに》も', '欲《ほ》しくない'] },
    { type: 'scramble', translate: { id: 'Apakah kamu tidak perlu membawa paspor?', en: 'Don\'t you need to bring your passport?' }, words: ['パスポート', 'を', '持《も》って', '行《い》かなくても', 'いい？'] },
    { type: 'scramble', translate: { id: 'Kemarin itu bukan hari libur.', en: 'Yesterday wasn\'t a holiday.' }, words: ['きのう', 'は', '休《やす》み', 'じゃなかった'] },
  ] },
  { id: 21, focus: '〜と思《おも》います、〜と言《い》いました、でしょう', questions: [
    { type: 'mc', q: '明日《あした》 雨《あめ》が 降《ふ》る＿＿＿ 思《おも》います。', choices: ['と', 'が', 'の', 'を'], answer: 0, img: 'tabler-cloud-rain' , translate: { id: 'Saya kira besok akan turun hujan.', en: 'I think it will rain tomorrow.' } },
    { type: 'mc', q: '図書館《としょかん》は きょう 休《やす》みですか。…いいえ、休《やす》みじゃない＿＿＿ 思《おも》います。', choices: ['と', 'が', 'の', 'を'], answer: 0 , translate: { id: 'Apakah perpustakaan libur hari ini? ...Tidak, saya kira tidak libur.', en: 'Is the library closed today? ...No, I don\'t think so.' } },
    { type: 'mc', q: 'ミラーさんは お酒《さけ》を＿＿＿でしょう？…ええ、飲《の》みます。', choices: ['飲《の》む', '飲《の》みます', '飲《の》みました', '飲《の》んで'], answer: 0 , translate: { id: 'Miller minum sake, kan? ...Ya, dia minum.', en: 'Miller drinks sake, right? ...Yes, he does.' } },
    { type: 'mc', q: 'B：スキーに 行《い》きます。→ Bさんは スキーに 行《い》く＿＿＿ 言《い》いました。', choices: ['と', 'に', 'を', 'が'], answer: 0 , translate: { id: 'B akan pergi ski. → B bilang akan pergi ski.', en: 'B is going skiing. → B said he\'s going skiing.' } },
    { type: 'mc', q: 'この 資料《しりょう》は 役《やく》に 立《た》ちますか。…ええ、とても 役《やく》に 立《た》つ＿＿＿ 思《おも》います。', choices: ['と', 'が', 'の', 'は'], answer: 0 , translate: { id: 'Apakah berkas ini berguna? ...Ya, saya kira sangat berguna.', en: 'Are these documents useful? ...Yes, I think they\'re very useful.' } },
    { type: 'scramble', translate: { id: 'Dia bilang akan datang jam sepuluh.', en: 'He said he\'ll come at ten o\'clock.' }, words: ['彼《かれ》', 'は', '10時《じゅうじ》', 'に', '来《く》る', 'と', '言《い》いました'] },
    { type: 'scramble', translate: { id: 'Apakah bank hari Minggu libur ya?', en: 'I wonder if the bank is closed on Sundays.' }, words: ['銀行《ぎんこう》', 'は', '日曜日《にちようび》', '休《やす》み', 'でしょう？'] },
    { type: 'scramble', translate: { id: 'Saya rasa mungkin akan ada rapat minggu depan.', en: 'I think there might be a meeting next week.' }, words: ['来週《らいしゅう》', '会議《かいぎ》', 'が', 'ある', 'と', '思《おも》います'] },
    { type: 'mc', q: '病気《びょうき》の 友達《ともだち》に 何《なん》＿＿＿ 言《い》いますか。', choices: ['と', 'が', 'を', 'の'], answer: 0 , translate: { id: 'Apa yang dikatakan kepada teman yang sedang sakit?', en: 'What do you say to a friend who is sick?' } },
    { type: 'scramble', translate: { id: 'Saya rasa Iwan orang yang pandai.', en: 'I think Iwan is a smart person.' }, words: ['イーさん', 'は', '頭《あたま》', 'が', 'いい', 'と', '思《おも》います'] },
    { type: 'mc', q: 'A：あした　雨《あめ》が　降《ふ》ると　思《おも》いますか。\nB：＿＿＿。', choices: ['ええ、降《ふ》ると　思《おも》います', 'ええ、降《ふ》りました', 'いいえ、降《ふ》ります', 'ええ、降《ふ》って　います'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Apakah kamu kira besok akan hujan? ...Ya, saya kira akan hujan.', en: 'Do you think it will rain tomorrow? ...Yes, I think it will.' } },
    { type: 'mc', q: '部長《ぶちょう》：来週《らいしゅう》　名古屋《なごや》へ　出張《しゅっちょう》します。\n木村《きむら》：部長《ぶちょう》は　＿＿＿。', choices: ['来週《らいしゅう》　名古屋《なごや》へ　出張《しゅっちょう》すると　言《い》いました', '来週《らいしゅう》　名古屋《なごや》へ　出張《しゅっちょう》しました', '来週《らいしゅう》　名古屋《なごや》が　好《す》きです', '来週《らいしゅう》　名古屋《なごや》へ　行《い》きたいです'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Kepala bagian: Minggu depan saya dinas ke Nagoya. → Kimura: Kepala bagian bilang minggu depan akan dinas ke Nagoya.', en: 'Section chief: Next week I\'ll go on a business trip to Nagoya. → Kimura: The section chief said he\'ll go on a business trip to Nagoya next week.' } },
    { type: 'mc', q: 'ミラーさんは 伊藤《いとう》さんを 知《し》って いますか。…いいえ、たぶん＿＿＿ 思《おも》います。', choices: ['知《し》らない', '知《し》っている', '知《し》った', '知《し》ります'], answer: 0 , translate: { id: 'Apakah Miller kenal Ito? ...Tidak, saya kira mungkin tidak kenal.', en: 'Does Miller know Ito? ...No, I don\'t think he does.' } },
    { type: 'mc', q: 'このカレーは 辛《から》いでしょう？…いいえ、そんなに＿＿＿。', choices: ['辛《から》くないです', '辛《から》いです', '辛《から》かったです', '辛《から》くて'], answer: 0 , translate: { id: 'Kari ini pedas ya? ...Tidak, tidak terlalu pedas.', en: 'Is this curry spicy? ...No, it\'s not that spicy.' } },
    { type: 'mc', q: 'カリナさんは＿＿＿でしょう？…ええ、留学生《りゅうがくせい》です。', choices: ['留学生《りゅうがくせい》', '留学生《りゅうがくせい》です', '留学生《りゅうがくせい》な', '留学生《りゅうがくせい》の'], answer: 0 , translate: { id: 'Karina itu mahasiswa asing, kan? ...Ya, mahasiswa asing.', en: 'Karina is an international student, right? ...Yes, she is.' } },
    { type: 'scramble', translate: { id: 'Saya pikir kartu telepon ini bisa dipakai.', en: 'I think this phone card can still be used.' }, words: ['この', 'テレホンカード', 'は', '使《つか》える', 'と', '思《おも》います'] },
    { type: 'scramble', translate: { id: 'D bilang akan membaca komik dan menonton anime.', en: 'D said he\'ll read comics and watch anime.' }, words: ['Dさん', 'は', 'マンガ', 'を', '読《よ》んだり', '、', 'アニメ', 'を', '見《み》たり', 'すると', '言《い》いました'] },
    { type: 'scramble', translate: { id: 'E bilang harus menulis laporan.', en: 'E said he has to write a report.' }, words: ['Eさん', 'は', 'レポート', 'を', '書《か》かなければならないと', '言《い》いました'] },
    { type: 'scramble', translate: { id: 'Harga-harga di sini tidak begitu tinggi.', en: 'Prices here aren\'t that high.' }, words: ['ここ', 'は', '物価《ぶっか》', 'が', 'そんなに', '高《たか》くないです'] },
    { type: 'scramble', translate: { id: 'Apakah besok pertandingan sepak bola akan menang menurutmu?', en: 'Do you think the soccer match will be won tomorrow?' }, words: ['あした', 'の', 'サッカー', 'の', '試合《しあい》', 'は', '勝《か》つ', 'と', '思《おも》いますか'] },
  ] },
  { id: 22, focus: '名詞《めいし》を 修飾《しゅうしょく》する 文《ぶん》', questions: [
    { type: 'mc', q: '「赤《あか》い 帽子《ぼうし》を かぶっている 人《ひと》」の 意味《いみ》は？', choices: ['Orang yang memakai topi merah', 'Orang yang menjual topi merah', 'Topi merah orang itu', 'Orang yang membeli topi'], answer: 0, img: 'tabler-hat' , translate: { id: 'Orang yang memakai topi merah.', en: 'The person wearing a red hat.' } },
    { type: 'mc', q: '私《わたし》が＿＿＿所《ところ》は 横浜《よこはま》です。（生《う》まれます）', choices: ['生《う》まれた', '生《う》まれます', '生《う》まれて', '生《う》まれる'], answer: 0 , translate: { id: 'Tempat saya lahir adalah Yokohama.', en: 'The place I was born is Yokohama.' } },
    { type: 'mc', q: 'あの 赤《あか》い コートを＿＿＿人《ひと》は だれですか。（着《き》ます）', choices: ['着《き》ている', '着《き》る', '着《き》た', '着《き》て'], answer: 0 , translate: { id: 'Siapa orang yang memakai mantel merah itu?', en: 'Who is that person wearing a red coat?' } },
    { type: 'mc', q: '安《やす》い パソコンを＿＿＿店《みせ》を 知《し》っていますか。（売《う》ります）', choices: ['売《う》っている', '売《う》る', '売《う》った', '売《う》って'], answer: 0 , translate: { id: 'Tahu toko yang menjual komputer murah?', en: 'Do you know a store that sells cheap computers?' } },
    { type: 'mc', q: '旅行《りょこう》に＿＿＿人《ひと》は＿＿＿ですか。…15人《にん》です。', choices: ['行《い》く／なんにん', '行《い》った／どこ', '行《い》って／なに', '行《い》き／いつ'], answer: 0 , translate: { id: 'Berapa orang yang ikut wisata? ...15 orang.', en: 'How many people are joining the tour? ...15 people.' } },
    { type: 'scramble', translate: { id: 'Buku yang saya beli kemarin', en: 'The book I bought yesterday.' }, words: ['きのう', '買《か》った', '本《ほん》'] },
    { type: 'scramble', translate: { id: 'Orang yang sedang membaca koran adalah Wang.', en: 'The person reading the newspaper is Wang.' }, words: ['新聞《しんぶん》', 'を', '読《よ》んでいる', '人《ひと》', 'は', 'ワンさん', 'です'] },
    { type: 'scramble', translate: { id: 'Apakah punya waktu untuk membaca koran tiap pagi?', en: 'Do you have time to read the newspaper every morning?' }, words: ['朝《あさ》', '新聞《しんぶん》', 'を', '読《よ》む', '時間《じかん》', 'が', 'ありますか'] },
    { type: 'mc', q: '妹《いもうと》さんが＿＿＿部屋《へや》の 家賃《やちん》は＿＿＿ですか。（借《か》ります）', choices: ['借《か》りている／いくら', '借《か》りる／だれ', '借《か》りた／なに', '借《か》りて／どこ'], answer: 0 , translate: { id: 'Berapa sewa kamar yang disewa adik perempuan?', en: 'How much is the rent for the room your younger sister is renting?' } },
    { type: 'scramble', translate: { id: 'Orang yang membuat air enak ini adalah kakak saya.', en: 'The person who makes this delicious water is my older brother.' }, words: ['おいしい', '水《みず》', 'を', '作《つく》っている', 'の', 'は', '兄《あに》', 'です'] },
    { type: 'mc', q: 'A：ミラーさんは　どの　人《ひと》ですか。\nB：＿＿＿。', choices: ['あの　新聞《しんぶん》を　読《よ》んで　いる　人《ひと》です', 'あの　人《ひと》は　新聞《しんぶん》です', '新聞《しんぶん》が　好《す》きな　人《ひと》です', '新聞《しんぶん》を　読《よ》みたい　人《ひと》です'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Siapa Miller? ...Dia orang yang sedang membaca koran itu.', en: 'Who is Miller? ...He\'s the person reading the newspaper over there.' } },
    { type: 'mc', q: 'A：あなたが　生《う》まれた　所《ところ》は　どこですか。\nB：＿＿＿。', choices: ['横浜《よこはま》です', '横浜《よこはま》に　住《す》んで　います', '横浜《よこはま》が　好《す》きです', '横浜《よこはま》へ　行《い》きます'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Di mana tempat kamu lahir? ...Yokohama.', en: 'Where were you born? ...Yokohama.' } },
    { type: 'mc', q: 'きのう 私《わたし》が＿＿＿ 人《ひと》は 山田《やまだ》さんです。（会《あ》います）', choices: ['会《あ》った', '会《あ》う', '会《あ》って', '会《あ》います'], answer: 0 , translate: { id: 'Orang yang saya temui kemarin adalah Yamada.', en: 'The person I met yesterday is Yamada.' } },
    { type: 'mc', q: '今《いま》 使《つか》っている 日本語《にほんご》の 本《ほん》は＿＿＿ですか。', choices: ['どう', 'どんな', 'なん', 'いつ'], answer: 0 , translate: { id: 'Bagaimana buku bahasa Jepang yang sedang dipakai sekarang?', en: 'How is the Japanese textbook you\'re using now?' } },
    { type: 'mc', q: '花《はな》が たくさん＿＿＿ 公園《こうえん》は きれいです。（咲《さ》きます）', choices: ['咲《さ》いている', '咲《さ》く', '咲《さ》いた', '咲《さ》いて'], answer: 0 , translate: { id: 'Taman yang banyak ditumbuhi bunga itu indah.', en: 'The park where lots of flowers are blooming is beautiful.' } },
    { type: 'scramble', translate: { id: 'Kapan waktunya luang untuk pergi bermain?', en: 'When are you free to go out and play?' }, words: ['遊《あそ》びに', '行《い》く', '時間《じかん》', 'が', 'ある', 'とき', '、', 'いつ', 'ですか'] },
    { type: 'scramble', translate: { id: 'Orang yang memakai kacamata itu guru saya.', en: 'The person wearing glasses is my teacher.' }, words: ['眼鏡《めがね》', 'を', 'かけている', '人《ひと》', 'は', 'わたし', 'の', '先生《せんせい》', 'です'] },
    { type: 'scramble', translate: { id: 'Apakah ada yang berbicara bahasa Jepang di keluarga Anda?', en: 'Is there anyone in your family who speaks Japanese?' }, words: ['家族《かぞく》', 'で', '日本語《にほんご》', 'を', '話《はな》す', '人《ひと》', 'が', 'いますか'] },
    { type: 'scramble', translate: { id: 'Bahasa asing pertama yang saya pelajari adalah bahasa Inggris.', en: 'The first foreign language I learned was English.' }, words: ['初《はじ》めて', '習《なら》った', '外国語《がいこくご》', 'は', '英語《えいご》', 'です'] },
    { type: 'scramble', translate: { id: 'Hotel yang saya inapi di Nagoya bersih dan pelayanannya bagus.', en: 'The hotel I stayed at in Nagoya was clean and had good service.' }, words: ['名古屋《なごや》', 'で', '泊《と》まった', 'ホテル', 'は', 'きれいで', '、', 'サービス', 'が', 'よかったです'] },
  ] },
  { id: 23, focus: '〜とき（〜する時《とき》／〜した時《とき》）', questions: [
    { type: 'mc', q: '疲《つか》れた＿＿＿、休《やす》みます。', choices: ['とき', 'こと', 'もの', 'ので'], answer: 0 , translate: { id: 'Saat lelah, saya istirahat.', en: 'When I\'m tired, I rest.' } },
    { type: 'mc', q: '小《ちい》さい 字《じ》を＿＿＿とき、眼鏡《めがね》を かけます。', choices: ['読《よ》む', '読《よ》んだ', '読《よ》んで', '読《よ》み'], answer: 0 , translate: { id: 'Saat membaca huruf kecil, saya memakai kacamata.', en: 'When reading small letters, I wear glasses.' } },
    { type: 'mc', q: '初《はじ》めて 富士山《ふじさん》を＿＿＿とき、きれいな 山《やま》だと 思《おも》いました。', choices: ['見《み》た', '見《み》る', '見《み》て', '見《み》ます'], answer: 0 , translate: { id: 'Saat pertama kali melihat Gunung Fuji, saya pikir gunung yang indah.', en: 'When I first saw Mt. Fuji, I thought it was a beautiful mountain.' } },
    { type: 'mc', q: 'セーターを＿＿＿と、寒《さむ》いです。', choices: ['脱《ぬ》ぐ', '脱《ぬ》いだ', '脱《ぬ》いで', '脱《ぬ》ぎ'], answer: 0 , translate: { id: 'Kalau melepas sweater, jadi dingin.', en: 'If I take off my sweater, it gets cold.' } },
    { type: 'mc', q: 'お酒《さけ》を 飲《の》む＿＿＿、車《くるま》を 運転《うんてん》すると、危《あぶ》ないです。', choices: ['と', 'とき', 'から', 'し'], answer: 0 , translate: { id: 'Berbahaya kalau minum sake lalu menyetir mobil.', en: 'It\'s dangerous to drink sake and then drive.' } },
    { type: 'scramble', translate: { id: 'Waktu kecil, saya suka menggambar.', en: 'When I was little, I liked drawing.' }, words: ['子供《こども》', 'の', 'とき', '、', '絵《え》', 'を', '描《か》くの', 'が', '好《す》きでした'] },
    { type: 'scramble', translate: { id: 'Waktu tidak tahu jalan, saya naik taksi.', en: 'When I don\'t know the way, I take a taxi.' }, words: ['道《みち》', 'が', 'わからない', 'とき', '、', 'タクシー', 'に', '乗《の》ります'] },
    { type: 'scramble', translate: { id: 'Kalau menekan tombol ini, tiket akan keluar.', en: 'If you press this button, a ticket will come out.' }, words: ['ここ', 'を', '押《お》すと', '、', '切符《きっぷ》', 'が', '出《で》ます'] },
    { type: 'mc', q: 'この 歌《うた》を＿＿＿と、家族《かぞく》を 思《おも》い出《だ》します。', choices: ['聞《き》く', '聞《き》いた', '聞《き》いて', '聞《き》き'], answer: 0 , translate: { id: 'Kalau mendengar lagu ini, saya teringat keluarga.', en: 'When I hear this song, I remember my family.' } },
    { type: 'scramble', translate: { id: 'Waktu ke rumah sakit, bawalah kartu asuransi.', en: 'When going to the hospital, bring your insurance card.' }, words: ['病院《びょういん》', 'へ', '行《い》くとき', '、', '保険証《ほけんしょう》', 'を', '持《も》って', '行《い》きます'] },
    { type: 'mc', q: 'A：疲《つか》れた　とき、どう　しますか。\nB：＿＿＿。', choices: ['早《はや》く　休《やす》みます', '早《はや》く　休《やす》んで　います', '早《はや》く　休《やす》んだ　こと　が　あります', '早《はや》く　休《やす》みたい　です'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Saat lelah, apa yang dilakukan? ...Saya segera istirahat.', en: 'What do you do when you\'re tired? ...I rest right away.' } },
    { type: 'mc', q: 'A：日本語《にほんご》を　勉強《べんきょう》する　とき、辞書《じしょ》を　使《つか》いますか。\nB：＿＿＿。', choices: ['ええ、よく　使《つか》います', 'ええ、使《つか》いました', 'いいえ、使《つか》って　います', 'いいえ、使《つか》いたいです'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Saat belajar bahasa Jepang, apakah memakai kamus? ...Ya, sering memakai.', en: 'Do you use a dictionary when studying Japanese? ...Yes, I often use one.' } },
    { type: 'mc', q: 'コピーのサイズを＿＿＿とき、ここを 押《お》してください。（変《か》えます）', choices: ['変《か》えたい', '変《か》えた', '変《か》えて', '変《か》える'], answer: 0 , translate: { id: 'Saat ingin mengubah ukuran fotokopi, tolong tekan di sini.', en: 'When you want to change the copy size, please press here.' } },
    { type: 'mc', q: '暇《ひま》な＿＿＿、よく 美術館《びじゅつかん》へ 絵《え》を 見《み》に 行《い》きます。', choices: ['とき', 'こと', 'もの', 'ので'], answer: 0 , translate: { id: 'Saat senggang, saya sering pergi ke museum melihat lukisan.', en: 'When I have free time, I often go to the museum to see paintings.' } },
    { type: 'mc', q: '子《こ》どもの＿＿＿、医者《いしゃ》に なりたいと 思《おも》いました。', choices: ['とき', 'こと', 'もの', 'ので'], answer: 0 , translate: { id: 'Waktu kecil, saya ingin menjadi dokter.', en: 'When I was little, I wanted to become a doctor.' } },
    { type: 'scramble', translate: { id: 'Waktu bus tidak datang, saya naik taksi.', en: 'When the bus doesn\'t come, I take a taxi.' }, words: ['バス', 'が', '来《こ》ない', 'とき', '、', 'タクシー', 'に', '乗《の》ります'] },
    { type: 'scramble', translate: { id: 'Waktu kesepian, saya selalu mendengarkan musik ini.', en: 'When I\'m lonely, I always listen to this music.' }, words: ['寂《さび》しい', 'とき', '、', 'いつも', 'この', '音楽《おんがく》', 'を', '聞《き》きます'] },
    { type: 'scramble', translate: { id: 'Belok kanan di persimpangan berikutnya, lalu ada perpustakaan di kiri.', en: 'Turn right at the next intersection, and there\'s a library on the left.' }, words: ['次《つぎ》', 'の', '交差点《こうさてん》', 'を', '右《みぎ》', 'へ', '曲《ま》がると', '、', '左《ひだり》', 'に', '図書館《としょかん》', 'が', 'あります'] },
    { type: 'scramble', translate: { id: 'Waktu meninggalkan rumah, saya bilang "itte kimasu".', en: 'When leaving the house, I say "itte kimasu."' }, words: ['出《で》かける', 'とき', '、', '「行《い》ってきます」', 'と', '言《い》います'] },
    { type: 'scramble', translate: { id: 'Waktu menyeberang jalan, hati-hati dengan mobil.', en: 'When crossing the street, watch out for cars.' }, words: ['道《みち》', 'を', '渡《わた》る', 'とき', '、', '車《くるま》', 'に', '気《き》を', 'つけて', 'ください'] },
  ] },
  { id: 24, focus: 'あげます・もらいます・くれます（人《ひと》のために）', questions: [
    { type: 'mc', q: '母《はは》は 私《わたし》に 傘《かさ》を ＿＿＿。（母《はは》→私《わたし》）', choices: ['くれました', 'あげました', 'もらいました', 'でした'], answer: 0, img: 'tabler-umbrella' , translate: { id: 'Ibu memberi saya payung.', en: 'My mother gave me an umbrella.' } },
    { type: 'mc', q: '私《わたし》は リナさんに 花《はな》を ＿＿＿。（私《わたし》→リナ）', choices: ['あげました', 'くれました', 'もらいました', 'です'], answer: 0 , translate: { id: 'Saya memberi bunga kepada Rina.', en: 'I gave flowers to Rina.' } },
    { type: 'mc', q: 'このコピー、全部《ぜんぶ》一人《ひとり》でしましたか。…いいえ、カリナさんに＿＿＿もらいました。（手伝《てつだ》います）', choices: ['手伝《てつだ》って', '手伝《てつだ》う', '手伝《てつだ》った', '手伝《てつだ》い'], answer: 0 , translate: { id: 'Apakah fotokopi ini semua dikerjakan sendiri? ...Tidak, dibantu oleh Karina.', en: 'Did you do all this photocopying by yourself? ...No, Karina helped me.' } },
    { type: 'mc', q: '木村《きむら》さんは あした 奈良《なら》を 案内《あんない》しますよ。→ わたしは 木村《きむら》さんに 奈良《なら》を 案内《あんない》して＿＿＿。', choices: ['もらいます', 'あげます', 'くれます', 'です'], answer: 0 , translate: { id: 'Kimura akan memandu ke Nara besok. → Saya dipandu ke Nara oleh Kimura.', en: 'Kimura will show me around Nara tomorrow. → Kimura will show me around Nara.' } },
    { type: 'mc', q: 'すてきな セーターですね。どこで 買《か》いましたか。…母《はは》が＿＿＿。（作《つく》ります）', choices: ['作《つく》ってくれました', '作《つく》ってもらいました', '作《つく》ってあげました', '作《つく》りました'], answer: 0 , translate: { id: 'Sweter yang bagus. Beli di mana? ...Ibu saya yang membuatkannya.', en: 'That\'s a nice sweater. Where did you buy it? ...My mother made it for me.' } },
    { type: 'scramble', translate: { id: 'Saya diberitahu (dibantu) oleh Sato tentang cara membuat sukiyaki.', en: 'Sato taught me how to make sukiyaki.' }, words: ['佐藤《さとう》さん', 'に', 'すき焼《や》き', 'の', '作《つく》り方《かた》', 'を', '教《おし》えてもらいました'] },
    { type: 'scramble', translate: { id: 'Guru Kobayashi mengajari saya bahasa Jepang.', en: 'Mr./Ms. Kobayashi taught me Japanese.' }, words: ['小林《こばやし》先生《せんせい》', 'は', '日本語《にほんご》', 'を', '教《おし》えて', 'くれました'] },
    { type: 'mc', q: '一人《ひとり》で 病院《びょういん》へ 行《い》きましたか。…いいえ、山田《やまだ》さんに＿＿＿。', choices: ['いっしょに行《い》ってもらいました', 'いっしょに行《い》ってあげました', '行《い》きました', '行《い》っています'], answer: 0 , translate: { id: 'Apakah pergi ke rumah sakit sendirian? ...Tidak, saya ditemani pergi oleh Yamada.', en: 'Did you go to the hospital alone? ...No, Yamada went with me.' } },
    { type: 'scramble', translate: { id: 'Waktu ada teman asing datang, saya akan pandu tempat wisata di negara saya.', en: 'When a friend comes from abroad, I\'ll show them around my country.' }, words: ['外国《がいこく》', 'から', '友達《ともだち》', 'が', '来《き》たとき', '、', '国《くに》', 'の', 'いい', '所《ところ》', 'を', '案内《あんない》してあげます'] },
    { type: 'mc', q: 'A：かわいい　かばんですね。どこで　買《か》いましたか。\nB：＿＿＿。', choices: ['姉《あね》が　買《か》って　くれました', '姉《あね》に　買《か》って　あげました', '姉《あね》が　買《か》って　もらいました', '姉《あね》の　かばんです'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Tas yang lucu ya. Beli di mana? ...Kakak perempuan saya yang membelikannya.', en: 'That\'s a cute bag. Where did you buy it? ...My older sister bought it for me.' } },
    { type: 'mc', q: 'A：木村《きむら》さんの　電話《でんわ》番号《ばんごう》が　分《わ》かりましたか。\nB：ええ、＿＿＿。', choices: ['佐藤《さとう》さんに　教《おし》えて　もらいました', '佐藤《さとう》さんに　教《おし》えて　あげました', '佐藤《さとう》さんが　教《おし》えました', '佐藤《さとう》さんは　知《し》りません'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Sudah tahu nomor telepon Kimura? ...Ya, diberi tahu oleh Sato.', en: 'Do you know Kimura\'s phone number now? ...Yes, Sato told me.' } },
    { type: 'mc', q: 'すてきな セーターですね。母《はは》が＿＿＿。（送《おく》ります）', choices: ['送《おく》ってくれました', '送《おく》ってあげました', '送《おく》りました', '送《おく》っています'], answer: 0 , translate: { id: 'Sweter yang bagus. Ibu saya yang mengirimkannya.', en: 'That\'s a nice sweater. My mother sent it to me.' } },
    { type: 'mc', q: 'あした 引《ひ》っ越《こ》しの 手伝《てつだ》いに 行《い》く 人《ひと》が いますか。…ええ、佐藤《さとう》さんと ミラーさんが＿＿＿。', choices: ['来《き》てくれます', '来《き》てあげます', '来《き》てもらいます', '来《き》ます'], answer: 0 , translate: { id: 'Besok ada yang datang membantu pindahan? ...Ya, Sato dan Miller akan datang untuk saya.', en: 'Is anyone coming to help with the move tomorrow? ...Yes, Sato and Miller are coming for me.' } },
    { type: 'mc', q: 'ビールと ジュースと ワインを 買《か》いました。ほかに 何《なに》か 飲《の》み物《もの》が＿＿＿か。', choices: ['要《い》ります', '要《い》りません', 'あります', 'いります？'], answer: 0 , translate: { id: 'Saya membeli bir, jus, dan anggur. Apakah perlu minuman lain?', en: 'I bought beer, juice, and wine. Do we need any other drinks?' } },
    { type: 'scramble', translate: { id: 'Bibi saya membelikan saya sepeda.', en: 'My aunt bought me a bicycle.' }, words: ['おば', 'が', '自転車《じてんしゃ》', 'を', '買《か》って', 'くれました'] },
    { type: 'scramble', translate: { id: 'Saya meminta bantuan Karina karena tidak paham kanji.', en: 'I asked Karina for help because I don\'t understand kanji.' }, words: ['漢字《かんじ》', 'が', 'わかりません', 'から', '、', 'カリナさん', 'に', '手伝《てつだ》って', 'もらいました'] },
    { type: 'scramble', translate: { id: 'Kapan pun teman dari luar negeri datang, saya akan memandunya keliling kota.', en: 'Whenever a friend from abroad comes, I\'ll show them around town.' }, words: ['外国《がいこく》', 'の', '友達《ともだち》', 'が', '来《き》たら', '、', '町《まち》', 'を', '案内《あんない》してあげます'] },
    { type: 'scramble', translate: { id: 'Teman saya mengantar saya ke rumah sakit.', en: 'My friend took me to the hospital.' }, words: ['友達《ともだち》', 'が', '病院《びょういん》', 'へ', '連《つ》れて', '行《い》ってくれました'] },
    { type: 'scramble', translate: { id: 'Saya membuatkan sukiyaki untuk Matsumoto.', en: 'I made sukiyaki for Matsumoto.' }, words: ['松本《まつもと》さん', 'に', 'すき焼《や》き', 'を', '作《つく》って', 'あげました'] },
    { type: 'scramble', translate: { id: 'Waktu kecil, siapa yang membelikanmu hadiah ulang tahun?', en: 'When you were little, who bought you birthday presents?' }, words: ['子《こ》どもの', 'とき', '、', '誕生日《たんじょうび》', 'に', '何《なに》', 'を', '買《か》って', 'もらいましたか'] },
  ] },
  { id: 25, focus: '〜たら（条件《じょうけん》）、〜ても', questions: [
    { type: 'mc', q: '時間《じかん》が あっ＿＿＿、寄《よ》ってください。', choices: ['たら', 'ても', 'から', 'ので'], answer: 0 , translate: { id: 'Kalau ada waktu, mampirlah.', en: 'If you have time, please stop by.' } },
    { type: 'mc', q: 'いい 大学《だいがく》に＿＿＿ら、頑張《がんば》らなければ なりません。', choices: ['入《はい》った', '入《はい》る', '入《はい》って', '入《はい》り'], answer: 0 , translate: { id: 'Kalau masuk universitas yang bagus, harus berusaha keras.', en: 'If you get into a good university, you have to work hard.' } },
    { type: 'mc', q: '漢字《かんじ》が＿＿＿ら、ひらがなで 書《か》いても いいですか。', choices: ['分《わ》からなかった', '分《わ》からない', '分《わ》からなくて', '分《わ》かって'], answer: 0 , translate: { id: 'Kalau tidak mengerti kanji, boleh menulis dengan hiragana?', en: 'If I don\'t understand kanji, is it okay to write in hiragana?' } },
    { type: 'mc', q: 'ゆっくり 歩《ある》いて＿＿＿、駅《えき》まで 15分《ふん》ぐらいです。', choices: ['も', 'たら', 'から', 'ので'], answer: 0 , translate: { id: 'Kalau jalan santai pun, ke stasiun sekitar 15 menit.', en: 'Even walking slowly, it\'s about 15 minutes to the station.' } },
    { type: 'mc', q: '簡単《かんたん》な 漢字《かんじ》＿＿＿も、なかなか 覚《おぼ》える ことが できません。', choices: ['でも', 'たら', 'から', 'ので'], answer: 0 , translate: { id: 'Meskipun kanji yang mudah, tetap sulit dihafal.', en: 'Even simple kanji are still hard to memorize.' } },
    { type: 'scramble', translate: { id: 'Meski hujan, saya tetap pergi.', en: 'Even if it rains, I\'ll still go.' }, words: ['雨《あめ》', 'が', '降《ふ》っても', '、', '行《い》きます'] },
    { type: 'scramble', translate: { id: 'Kalau mendapat waktu libur, saya ingin bepergian.', en: 'If I get some time off, I want to travel.' }, words: ['休《やす》み', 'が', 'あったら', '、', '旅行《りょこう》', 'したい', 'です'] },
    { type: 'scramble', translate: { id: 'Kalau tidak mengerti walau sudah dicari, tanyakan pada guru.', en: 'If you look it up and still don\'t understand, ask the teacher.' }, words: ['調《しら》べても', '、', '分《わ》からなかったら', '、', '先生《せんせい》', 'に', '聞《き》きます'] },
    { type: 'mc', q: '安《やす》くても、要《い》らない 物《もの》は＿＿＿。', choices: ['買《か》いません', '買《か》います', '買《か》った', '買《か》って'], answer: 0 , translate: { id: 'Walaupun murah, barang yang tidak perlu tidak saya beli.', en: 'Even if it\'s cheap, I won\'t buy things I don\'t need.' } },
    { type: 'scramble', translate: { id: 'Kalau sudah tua dan pensiun, saya ingin berkeliling dunia.', en: 'When I get old and retire, I want to travel around the world.' }, words: ['年《とし》', 'を', '取《と》って', '、', '仕事《しごと》', 'を', 'やめたら', '、', '世界《せかい》', 'を', '旅行《りょこう》したい', 'です'] },
    { type: 'mc', q: 'A：もし、大学《だいがく》を　出《で》たら、何《なに》を　したいですか。\nB：＿＿＿。', choices: ['留学《りゅうがく》して、もう少《すこ》し　勉強《べんきょう》したいです', '留学《りゅうがく》しました', '留学《りゅうがく》が　好《す》きです', '留学《りゅうがく》を　したいと　思《おも》いました'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Kalau lulus kuliah, ingin melakukan apa? ...Saya ingin belajar lagi ke luar negeri.', en: 'What do you want to do after graduating from university? ...I want to study abroad some more.' } },
    { type: 'mc', q: 'A：時間《じかん》が　あったら、どう　しますか。\nB：＿＿＿。', choices: ['遊《あそ》びに　来《き》て　ください', '遊《あそ》びに　来《き》ます', '遊《あそ》びに　来《き》ました', '遊《あそ》びに　来《く》る　と　思《おも》います'], answer: 0, img: 'tabler-message-circle' , translate: { id: 'Kalau ada waktu, apa yang dilakukan? ...Datanglah main.', en: 'What do you do if you have free time? ...Come visit me.' } },
    { type: 'mc', q: '道《みち》が わからない＿＿＿、地図《ちず》を 見《み》ます。', choices: ['とき', 'たら', 'ても', 'から'], answer: 0 , translate: { id: 'Saat tidak tahu jalan, saya melihat peta.', en: 'When I don\'t know the way, I look at a map.' } },
    { type: 'mc', q: 'このパソコンは すぐ 故障《こしょう》します＿＿＿。（修理《しゅうり》しても）', choices: ['修理《しゅうり》しても', '修理《しゅうり》したら', '修理《しゅうり》すると', '修理《しゅうり》するので'], answer: 0 , translate: { id: 'Komputer ini mudah rusak meskipun sudah diperbaiki.', en: 'This computer breaks down easily even after being repaired.' } },
    { type: 'mc', q: 'お酒《さけ》を 飲《の》んだら、車《くるま》を 運転《うんてん》しないで＿＿＿。', choices: ['ください', 'ください？', 'くれます', 'あげます'], answer: 0 , translate: { id: 'Kalau sudah minum sake, jangan menyetir mobil.', en: 'If you\'ve had sake, please don\'t drive.' } },
    { type: 'scramble', translate: { id: 'Meskipun rumahnya lama, kalau sewanya murah saya mau menyewanya.', en: 'Even if the house is old, I want to rent it if the rent is cheap.' }, words: ['古《ふる》くても', '、', '家賃《やちん》', 'が', '安《やす》かったら', '、', '借《か》りたい', 'です'] },
    { type: 'scramble', translate: { id: 'Kalau sudah punya waktu senggang, saya ingin mendaki Gunung Fuji.', en: 'If I have free time, I want to climb Mt. Fuji.' }, words: ['暇《ひま》', 'が', 'あったら', '、', '富士山《ふじさん》', 'に', '登《のぼ》りたい', 'です'] },
    { type: 'scramble', translate: { id: 'Meski sudah mencari, tidak ketemu.', en: 'Even after searching, I couldn\'t find it.' }, words: ['探《さが》しても', '、', '見《み》つかりませんでした'] },
    { type: 'scramble', translate: { id: 'Kalau seandainya bisa lahir sekali lagi, kamu mau jadi laki-laki atau perempuan?', en: 'If you could be born again, would you rather be a man or a woman?' }, words: ['もし', '、', 'もう一度《いちど》', '生《う》まれる', 'こと', 'が', 'できたら', '、', '男《おとこ》の人《ひと》', 'が', 'いいですか'] },
    { type: 'scramble', translate: { id: 'Kalau tiba jam 3 sore di Kyoto, saya akan jemput.', en: 'If I arrive in Kyoto around 3 p.m., I\'ll come pick you up.' }, words: ['3時《さんじ》', 'ごろ', '京都《きょうと》', 'に', '着《つ》いたら', '、', '迎《むか》えに', '行《い》きます'] },
  ] },
]

const REVIEWS = [
  { id: 'r1-8', label: 'まとめ 1〜8', questions: [
    { type: 'mc', q: 'これは 私《わたし》＿＿＿ かばんです。', choices: ['の', 'は', 'を', 'に'], answer: 0 , translate: { id: 'Tas ini tas saya.', en: 'This bag is my bag.' } },
    { type: 'mc', q: '毎朝《まいあさ》 6時《ろくじ》＿＿＿ 起《お》きます。', choices: ['に', 'で', 'を', 'へ'], answer: 0 , translate: { id: 'Setiap pagi saya bangun jam 6.', en: 'Every morning I wake up at 6.' } },
    { type: 'mc', q: '「安《やす》い」の 反対《はんたい》は？', choices: ['高《たか》い', '大《おお》きい', '新《あたら》しい', '静《しず》か'], answer: 0 , translate: { id: 'Lawan kata \'murah\' apa? ...Mahal.', en: 'What\\\'s the opposite of \\\'cheap\\\'? ...Expensive.' } },
    { type: 'scramble', translate: { id: 'Kemarin saya pergi ke Bandung.', en: 'Yesterday I went to Bandung.' }, words: ['きのう', 'バンドン', 'へ', '行《い》きました'] },
    { type: 'mc', q: 'このかばんは＿＿＿ですか。…8,300円《えん》です。', choices: ['いくら', 'だれの', 'なんじ', 'どこ'], answer: 0 , translate: { id: 'Tas ini harganya berapa? ...8.300 yen.', en: 'How much is this bag? ...8,300 yen.' } },
    { type: 'mc', q: '銀行《ぎんこう》は 9時《じ》＿＿＿ 3時《じ》＿＿＿です。', choices: ['から／まで', 'まで／から', 'に／で', 'を／へ'], answer: 0 , translate: { id: 'Bank buka dari jam 9 sampai jam 3.', en: 'The bank is open from 9 to 3.' } },
    { type: 'mc', q: '「静《しず》か」の 反対《はんたい》は？', choices: ['賑《にぎ》やか', '有名《ゆうめい》', '暇《ひま》', '親切《しんせつ》'], answer: 0 , translate: { id: 'Lawan kata \'sepi/tenang\' apa? ...Ramai.', en: 'What\\\'s the opposite of \\\'quiet\\\'? ...Lively.' } },
    { type: 'scramble', translate: { id: 'Saya belajar bahasa Jepang bersama Karina.', en: 'I study Japanese together with Karina.' }, words: ['カリナさん', 'と', '日本語《にほんご》', 'を', '勉強《べんきょう》します'] },
    { type: 'mc', q: 'あの 方《かた》は＿＿＿ですか。（丁寧《ていねい》に「だれ」）', choices: ['どなた', 'どちら', 'どこ', 'なに'], answer: 0 , translate: { id: 'Siapa orang itu? (sopan)', en: 'Who is that person? (polite)' } },
    { type: 'mc', q: 'これは 山田《やまだ》さん＿＿＿ 傘《かさ》です。', choices: ['の', 'は', 'を', 'に'], answer: 0 , translate: { id: 'Ini payung Yamada.', en: 'This is Yamada\'s umbrella.' } },
    { type: 'mc', q: 'この 時計《とけい》は＿＿＿ですか。…3,800円《えん》です。', choices: ['いくら', 'なんじ', 'だれの', 'どこ'], answer: 0 , translate: { id: 'Jam ini harganya berapa? ...3.800 yen.', en: 'How much is this watch? ...3,800 yen.' } },
    { type: 'mc', q: '図書館《としょかん》は 9時《じ》＿＿＿ 6時《じ》＿＿＿です。', choices: ['から／まで', 'まで／から', 'に／で', 'を／へ'], answer: 0 , translate: { id: 'Perpustakaan buka dari jam 9 sampai jam 6.', en: 'The library is open from 9 to 6.' } },
    { type: 'mc', q: '去年《きょねん》の 4月《がつ》に 日本《にほん》へ＿＿＿。', choices: ['来《き》ました', '来《き》ます', '来《く》る', '来《き》て'], answer: 0 , translate: { id: 'Tahun lalu bulan April saya datang ke Jepang.', en: 'Last year in April I came to Japan.' } },
    { type: 'mc', q: '毎朝《まいあさ》パン＿＿＿ 卵《たまご》＿＿＿ 食《た》べます。', choices: ['と／を', 'を／と', 'に／で', 'の／を'], answer: 0 , translate: { id: 'Setiap pagi saya makan roti dan telur.', en: 'Every morning I eat bread and eggs.' } },
    { type: 'mc', q: '誕生日《たんじょうび》に 友達《ともだち》＿＿＿ プレゼントを もらいました。', choices: ['に', 'を', 'で', 'へ'], answer: 0 , translate: { id: 'Saat ulang tahun saya menerima hadiah dari teman.', en: 'On my birthday I received a present from a friend.' } },
    { type: 'mc', q: '「暑《あつ》い」の 反対《はんたい》は？', choices: ['寒《さむ》い', '低《ひく》い', '暗《くら》い', '軽《かる》い'], answer: 0 , translate: { id: 'Lawan kata \'panas\' apa? ...Dingin.', en: 'What\'s the opposite of \'hot\'? ...Cold.' } },
    { type: 'mc', q: 'このレストランは 小《ちい》さいです＿＿＿、有名《ゆうめい》です。', choices: ['が', 'に', 'を', 'の'], answer: 0 , translate: { id: 'Restoran ini kecil, tapi terkenal.', en: 'This restaurant is small, but famous.' } },
    { type: 'scramble', translate: { id: 'Ini adalah tas milik Yamada.', en: 'This is Yamada\'s bag.' }, words: ['これ', 'は', '山田《やまだ》さん', 'の', 'かばん', 'です'] },
    { type: 'scramble', translate: { id: 'Kemarin saya bertemu dengan guru bahasa Jepang.', en: 'Yesterday I met my Japanese teacher.' }, words: ['きのう', '日本語《にほんご》', 'の', '先生《せんせい》', 'に', '会《あ》いました'] },
    { type: 'scramble', translate: { id: 'Bank tutup jam 3.', en: 'The bank closes at 3.' }, words: ['銀行《ぎんこう》', 'は', '3時《さんじ》', 'に', '終《お》わります'] },
  ] },
  { id: 'r9-17', label: 'まとめ 9〜17', questions: [
    { type: 'mc', q: '私《わたし》は 果物《くだもの》＿＿＿ 好《す》きです。', choices: ['が', 'を', 'に', 'へ'], answer: 0, img: 'tabler-apple' , translate: { id: 'Saya suka buah.', en: 'I like fruit.' } },
    { type: 'mc', q: '猫《ねこ》は 椅子《いす》の＿＿＿に います。', choices: ['下《した》', '上《うえ》', '中《なか》', '前《まえ》'], answer: 0 , translate: { id: 'Kucing ada di bawah kursi.', en: 'The cat is under the chair.' } },
    { type: 'mc', q: '「飲《の》みます」の て形《けい》は？', choices: ['飲《の》んで', '飲《の》みて', '飲《の》いて', '飲《の》って'], answer: 0 , translate: { id: 'Bentuk te dari \'minum\' apa?', en: 'What\'s the te-form of \'to drink\'?' } },
    { type: 'scramble', translate: { id: 'Saya harus belajar setiap hari.', en: 'I have to study every day.' }, words: ['毎日《まいにち》', '勉強《べんきょう》', 'しなければなりません'] },
    { type: 'mc', q: '地下鉄《ちかてつ》は バス＿＿＿ 速《はや》いです。', choices: ['より', 'から', 'まで', 'ほど'], answer: 0 , translate: { id: 'Kereta bawah tanah lebih cepat daripada bus.', en: 'The subway is faster than the bus.' } },
    { type: 'mc', q: '窓《まど》を＿＿＿ください。（開《あ》けます）', choices: ['開《あ》けて', '開《あ》けます', '開《あ》けた', '開《あ》ける'], answer: 0 , translate: { id: 'Tolong buka jendela.', en: 'Please open the window.' } },
    { type: 'scramble', translate: { id: 'Setelah selesai bekerja, saya pergi ke perpustakaan untuk meminjam buku.', en: 'After finishing work, I go to the library to borrow a book.' }, words: ['仕事《しごと》', 'が', '終《お》わってから', '、', '本《ほん》', 'を', '借《か》りに', '図書館《としょかん》', 'へ', '行《い》きます'] },
    { type: 'mc', q: 'どんな スポーツ＿＿＿ 好《す》きですか。', choices: ['が', 'を', 'に', 'へ'], answer: 0, img: 'tabler-ball-football' , translate: { id: 'Suka olahraga apa?', en: 'What sport do you like?' } },
    { type: 'mc', q: '事務所《じむしょ》に だれ＿＿＿ いますか。…だれ＿＿＿ いません。', choices: ['が／も', 'は／が', 'を／は', 'に／が'], answer: 0 , translate: { id: 'Apakah ada orang di kantor? ...Tidak ada siapa-siapa.', en: 'Is there anyone in the office? ...No one is there.' } },
    { type: 'mc', q: '家族《かぞく》は＿＿＿です。（5人《にん》）', choices: ['ごにん', 'いつつ', 'ごまい', 'ごだい'], answer: 0 , translate: { id: 'Keluarga saya 5 orang.', en: 'My family has 5 people.' } },
    { type: 'mc', q: '東京《とうきょう》は 大阪《おおさか》＿＿＿ 人《ひと》が 多《おお》いです。', choices: ['より', 'ほど', 'ので', 'でも'], answer: 0 , translate: { id: 'Di Tokyo orangnya lebih banyak daripada Osaka.', en: 'Tokyo has more people than Osaka.' } },
    { type: 'mc', q: '私《わたし》は 新《あたら》しい パソコン＿＿＿ 欲《ほ》しいです。', choices: ['が', 'を', 'に', 'は'], answer: 0, img: 'tabler-device-laptop' , translate: { id: 'Saya ingin komputer baru.', en: 'I want a new computer.' } },
    { type: 'mc', q: 'すみませんが、松本《まつもと》さんの 電話《でんわ》番号《ばんごう》を＿＿＿。（教《おし》えます→〜てください）', choices: ['教《おし》えてください', '教《おし》えました', '教《おし》える', '教《おし》えて'], answer: 0 , translate: { id: 'Maaf, tolong beritahu nomor telepon Matsumoto.', en: 'Excuse me, please tell me Matsumoto\'s phone number.' } },
    { type: 'mc', q: '兄《あに》の 会社《かいしゃ》は 電気《でんき》製品《せいひん》を＿＿＿います。（作《つく》ります）', choices: ['作《つく》って', '作《つく》んで', '作《つく》きて', '作《つく》いて'], answer: 0 , translate: { id: 'Perusahaan kakak laki-laki saya membuat produk elektronik.', en: 'My older brother\\\'s company makes electrical products.' } },
    { type: 'mc', q: '仕事《しごと》が 終《お》わって＿＿＿、1時間《いちじかん》ぐらい プールで 泳《およ》ぎます。', choices: ['から', 'まで', 'ので', 'し'], answer: 0 , translate: { id: 'Setelah pekerjaan selesai, saya berenang di kolam sekitar 1 jam.', en: 'After finishing work, I swam in the pool for about an hour.' } },
    { type: 'mc', q: '危《あぶ》ないですから、うしろから＿＿＿。', choices: ['押《お》さないでください', '押《お》してください', '押《お》しました', '押《お》します'], answer: 0 , translate: { id: 'Karena berbahaya, jangan dorong dari belakang.', en: 'It\\\'s dangerous, so please don\\\'t push from behind.' } },
    { type: 'mc', q: '両親《りょうしん》が 来《き》ますから、空港《くうこう》へ 迎《むか》えに＿＿＿。', choices: ['行《い》かなければなりません', '行《い》かなくてもいいです', '行《い》かないでください', '行《い》っています'], answer: 0 , translate: { id: 'Karena orang tua akan datang, saya harus menjemput ke bandara.', en: 'Since my parents are coming, I have to pick them up at the airport.' } },
    { type: 'scramble', translate: { id: 'Saya paling suka masakan Jepang.', en: 'I like Japanese food the best.' }, words: ['日本《にほん》', '料理《りょうり》', 'が', 'いちばん', '好《す》きです'] },
    { type: 'scramble', translate: { id: 'Ada dua orang di depan pintu.', en: 'There are two people in front of the door.' }, words: ['ドア', 'の', '前《まえ》', 'に', '二人《ふたり》', 'います'] },
    { type: 'scramble', translate: { id: 'Tolong buka jendela.', en: 'Please open the window.' }, words: ['窓《まど》', 'を', '開《あ》けて', 'ください'] },
  ] },
  { id: 'r18-25', label: 'まとめ 18〜25', questions: [
    { type: 'mc', q: '「泳《およ》ぎます」の 辞書《じしょ》形《けい》は？', choices: ['泳《およ》ぐ', '泳《およ》いで', '泳《およ》ぎて', '泳《およ》いた'], answer: 0 , translate: { id: 'Bentuk kamus dari \'berenang\' apa?', en: 'What\'s the dictionary form of \'to swim\'?' } },
    { type: 'mc', q: '明日《あした》 雨《あめ》が 降《ふ》る＿＿＿ 思《おも》います。', choices: ['と', 'が', 'の', 'を'], answer: 0 , translate: { id: 'Saya kira besok akan turun hujan.', en: 'I think it will rain tomorrow.' } },
    { type: 'scramble', translate: { id: 'Ketika lelah, saya tidur cepat.', en: 'When I\'m tired, I go to sleep early.' }, words: ['疲《つか》れた', 'とき', '、', '早《はや》く', '寝《ね》ます'] },
    { type: 'scramble', translate: { id: 'Kalau ada waktu, datanglah main.', en: 'If you have time, come visit.' }, words: ['時間《じかん》', 'が', 'あったら', '、', '遊《あそ》びに', '来《き》てください'] },
    { type: 'mc', q: '私《わたし》は 車《くるま》の 運転《うんてん》が ＿＿＿。（できます）', choices: ['できます', 'します', 'あります', 'います'], answer: 0 , translate: { id: 'Saya bisa menyetir mobil.', en: 'I can drive a car.' } },
    { type: 'mc', q: '母《はは》は 私《わたし》に セーターを＿＿＿。（母《はは》→私《わたし》）', choices: ['くれました', 'あげました', 'もらいました', 'でした'], answer: 0 , translate: { id: 'Ibu memberi saya sweter.', en: 'My mother gave me a sweater.' } },
    { type: 'scramble', translate: { id: 'Buku yang sedang dibaca oleh Wang itu menarik.', en: 'The book Wang is reading is interesting.' }, words: ['ワンさん', 'が', '読《よ》んでいる', '本《ほん》', 'は', 'おもしろい', 'です'] },
    { type: 'mc', q: '「食《た》べます」の 辞書《じしょ》形《けい》は？', choices: ['食《た》べる', '食《た》べて', '食《た》べます', '食《た》べた'], answer: 0 , translate: { id: 'Bentuk kamus dari \'makan\' apa?', en: 'What\\\'s the dictionary form of \\\'to eat\\\'?' } },
    { type: 'mc', q: '漢字《かんじ》が 分《わ》かりませんから、日本語《にほんご》の 新聞《しんぶん》を＿＿＿。', choices: ['読《よ》むことができません', '読《よ》みたいです', '読《よ》んでいます', '読《よ》んでください'], answer: 0 , translate: { id: 'Karena tidak mengerti kanji, saya tidak bisa membaca koran Jepang.', en: 'I can\\\'t read Japanese newspapers because I don\\\'t understand kanji.' } },
    { type: 'mc', q: '富士山《ふじさん》に 登《のぼ》った＿＿＿が あります。', choices: ['こと', 'もの', 'とき', 'ところ'], answer: 0, img: 'tabler-mountain' , translate: { id: 'Saya pernah mendaki Gunung Fuji.', en: 'I\\\'ve climbed Mt. Fuji before.' } },
    { type: 'mc', q: '休《やす》みの 日《ひ》は 本《ほん》を＿＿＿り、テレビを＿＿＿り します。', choices: ['読《よ》んだ／見《み》た', '読《よ》む／見《み》る', '読《よ》んで／見《み》て', '読《よ》み／見《み》'], answer: 0 , translate: { id: 'Hari libur saya membaca buku dan menonton TV.', en: 'On my day off I read books and watch TV.' } },
    { type: 'mc', q: '英語《えいご》が 分《わ》かる？…うん、＿＿＿。', choices: ['できる', 'できます', 'できた', 'できて'], answer: 0 , translate: { id: 'Mengerti bahasa Inggris? ...Ya, bisa.', en: 'Do you understand English? ...Yes, I can.' } },
    { type: 'mc', q: '図書館《としょかん》は きょう 休《やす》みですか。…いいえ、休《やす》みじゃない＿＿＿ 思《おも》います。', choices: ['と', 'が', 'の', 'を'], answer: 0 , translate: { id: 'Apakah perpustakaan libur hari ini? ...Tidak, saya kira tidak libur.', en: 'Is the library closed today? ...No, I don\\\'t think so.' } },
    { type: 'mc', q: 'あの 赤《あか》い コートを＿＿＿人《ひと》は だれですか。（着《き》ます）', choices: ['着《き》ている', '着《き》る', '着《き》た', '着《き》て'], answer: 0 , translate: { id: 'Siapa orang yang memakai mantel merah itu?', en: 'Who is that person wearing a red coat?' } },
    { type: 'mc', q: '小《ちい》さい 字《じ》を＿＿＿とき、眼鏡《めがね》を かけます。', choices: ['読《よ》む', '読《よ》んだ', '読《よ》んで', '読《よ》み'], answer: 0 , translate: { id: 'Saat membaca huruf kecil, saya memakai kacamata.', en: 'When reading small letters, I wear glasses.' } },
    { type: 'mc', q: 'すてきな セーターですね。どこで 買《か》いましたか。…母《はは》が＿＿＿。（作《つく》ります）', choices: ['作《つく》ってくれました', '作《つく》ってもらいました', '作《つく》ってあげました', '作《つく》りました'], answer: 0 , translate: { id: 'Sweter yang bagus. Beli di mana? ...Ibu saya yang membuatkannya.', en: 'That\\\'s a nice sweater. Where did you buy it? ...My mother made it for me.' } },
    { type: 'mc', q: 'いい 大学《だいがく》に＿＿＿ら、頑張《がんば》らなければ なりません。', choices: ['入《はい》った', '入《はい》る', '入《はい》って', '入《はい》り'], answer: 0 , translate: { id: 'Kalau masuk universitas yang bagus, harus berusaha keras.', en: 'If you get into a good university, you have to work hard.' } },
    { type: 'scramble', translate: { id: 'Saya bisa berenang 50 meter.', en: 'I can swim 50 meters.' }, words: ['50メートル', '泳《およ》ぐ', 'こと', 'が', 'できます'] },
    { type: 'scramble', translate: { id: 'Apakah Anda pernah menulis surat dengan bahasa Jepang?', en: 'Have you ever written a letter in Japanese?' }, words: ['日本語《にほんご》', 'で', '手紙《てがみ》', 'を', '書《か》いた', 'こと', 'が', 'ありますか'] },
    { type: 'scramble', translate: { id: 'Dia bilang akan datang jam sepuluh.', en: 'He said he\\\'ll come at ten o\\\'clock.' }, words: ['彼《かれ》', 'は', '10時《じゅうじ》', 'に', '来《く》る', 'と', '言《い》いました'] },
  ] },
  { id: 'r1-25', label: 'まとめ 1〜25', questions: [
    { type: 'mc', q: 'あの 方《かた》は＿＿＿ですか。（丁寧《ていねい》に「だれ」）', choices: ['どなた', 'どちら', 'どこ', 'なに'], answer: 0 , translate: { id: 'Siapa orang itu? (sopan)', en: 'Who is that person? (polite)' } },
    { type: 'mc', q: '窓《まど》を＿＿＿ください。（閉《し》めます）', choices: ['閉《し》めて', '閉《し》めます', '閉《し》めた', '閉《し》める'], answer: 0 , translate: { id: 'Tolong tutup jendela.', en: 'Please close the window.' } },
    { type: 'mc', q: '漢字《かんじ》が 分《わ》から＿＿＿から、ひらがなで 書《か》きます。', choices: ['ない', 'ありません', 'ません', 'なくて'], answer: 0 , translate: { id: 'Karena tidak mengerti kanji, saya menulis dengan hiragana.', en: 'Since I don\'t understand kanji, I write in hiragana.' } },
    { type: 'scramble', translate: { id: 'Saya meminjam CD dari Rina.', en: 'I borrowed a CD from Rina.' }, words: ['リナさん', 'に', 'CD', 'を', '借《か》りました'] },
    { type: 'scramble', translate: { id: 'Kalau hujan turun, pertandingan tidak bisa dilaksanakan.', en: 'If it rains, the match can\'t be held.' }, words: ['雨《あめ》', 'が', '降《ふ》る', 'と', '、', '試合《しあい》', 'が', 'できません'] },
    { type: 'mc', q: '田中《たなか》さんは＿＿＿人《ひと》ですか。…あの 眼鏡《めがね》を かけている 人《ひと》です。', choices: ['どの', 'だれの', 'なんの', 'いつの'], answer: 0 , translate: { id: 'Yang mana Tanaka? ...Dia orang yang memakai kacamata itu.', en: 'Which one is Tanaka? ...He\'s the person wearing glasses over there.' } },
    { type: 'mc', q: '疲《つか》れた＿＿＿、早《はや》く 寝《ね》ます。', choices: ['とき', 'こと', 'もの', 'ので'], answer: 0 , translate: { id: 'Saat lelah, saya cepat tidur.', en: 'When I\'m tired, I go to sleep quickly.' } },
    { type: 'scramble', translate: { id: 'Tolong sampaikan salam kepada semua di kantor.', en: 'Please give my regards to everyone at the office.' }, words: ['会社《かいしゃ》', 'の', 'みなさん', 'に', 'よろしく', '言《い》ってください'] },
    { type: 'mc', q: 'これは＿＿＿の カメラですか。…日本《にほん》のです。', choices: ['どこ', 'なに', 'だれ', 'いつ'], answer: 0, img: 'tabler-camera' , translate: { id: 'Kamera ini buatan mana? ...Buatan Jepang.', en: 'Where is this camera made? ...Made in Japan.' } },
    { type: 'mc', q: '毎日《まいにち》コーヒー＿＿＿ 飲《の》みます。', choices: ['を', 'に', 'で', 'と'], answer: 0 , translate: { id: 'Setiap hari saya minum kopi.', en: 'Every day I drink coffee.' } },
    { type: 'mc', q: '暑《あつ》いです＿＿＿、窓《まど》を 開《あ》けます。', choices: ['から', 'が', 'ので', 'し'], answer: 0 , translate: { id: 'Karena panas, saya membuka jendela.', en: 'Because it\\\'s hot, I open the window.' } },
    { type: 'mc', q: '机《つくえ》の 上《うえ》に 本《ほん》が＿＿＿。', choices: ['あります', 'います', 'です', 'ました'], answer: 0, img: 'tabler-book' , translate: { id: 'Ada buku di atas meja.', en: 'There is a book on the desk.' } },
    { type: 'mc', q: '地下鉄《ちかてつ》は バス＿＿＿ 速《はや》いです。', choices: ['より', 'から', 'まで', 'ほど'], answer: 0, img: 'tabler-train' , translate: { id: 'Kereta bawah tanah lebih cepat daripada bus.', en: 'The subway is faster than the bus.' } },
    { type: 'mc', q: 'のど＿＿＿ かわきましたから、何《なに》＿＿＿ 飲《の》みたいです。', choices: ['が／か', 'を／が', 'に／を', 'は／か'], answer: 0 , translate: { id: 'Karena tenggorokan haus, saya ingin minum sesuatu.', en: 'My throat is dry, so I want to drink something.' } },
    { type: 'mc', q: '「書《か》きます」の て形《けい》は？', choices: ['書《か》いて', '書《か》んで', '書《か》きて', '書《か》って'], answer: 0 , translate: { id: 'Bentuk te dari \'menulis\' apa?', en: 'What\\\'s the te-form of \\\'to write\\\'?' } },
    { type: 'mc', q: '今《いま》 新聞《しんぶん》を＿＿＿。（読《よ》んでいます）', choices: ['読《よ》んでいます', '読《よ》みます', '読《よ》みました', '読《よ》む'], answer: 0, img: 'tabler-news' , translate: { id: 'Sekarang saya sedang membaca koran.', en: 'I\\\'m reading the newspaper right now.' } },
    { type: 'mc', q: '明日《あした》は 早《はや》く 起《お》き＿＿＿。（義務《ぎむ》）', choices: ['なければなりません', 'なくてもいいです', 'ないでください', 'てください'], answer: 0 , translate: { id: 'Besok saya harus bangun pagi.', en: 'Tomorrow I have to wake up early.' } },
    { type: 'scramble', translate: { id: 'Saya tinggal di Bandung.', en: 'I live in Bandung.' }, words: ['バンドン', 'に', '住《す》んでいます'] },
    { type: 'scramble', translate: { id: 'Saya bisa berbicara bahasa Prancis dan Spanyol.', en: 'I can speak French and Spanish.' }, words: ['フランス語《ご》', 'と', 'スペイン語《ご》', 'を', '話《はな》す', 'こと', 'が', 'できます'] },
    { type: 'scramble', translate: { id: 'Waktu lelah, saya beristirahat.', en: 'When I\'m tired, I rest.' }, words: ['疲《つか》れた', 'とき', '、', '休《やす》みます'] },
  ] },
]

const ALL_TABS = [
  ...LESSONS.map(l => ({ key: `l${l.id}`, label: String(l.id), kind: 'lesson', ref: l })),
  ...REVIEWS.map(r => ({ key: r.id, label: r.label.replace('まとめ ', 'R.'), kind: 'review', ref: r })),
]

const activeKey = ref('l1')
const active = computed(() => ALL_TABS.find(x => x.key === activeKey.value))

// The lesson that comes right after the active one — used to offer a
// "lanjut ke Pelajaran N+1" shortcut on the result screen. Only lessons
// chain this way; the 4 rangkuman sets don't have a "next" of their own.
const nextLessonTab = computed(() => {
  if (active.value?.kind !== 'lesson')
    return null

  const i = ALL_TABS.findIndex(x => x.key === activeKey.value)

  return ALL_TABS[i + 1]?.kind === 'lesson' ? ALL_TABS[i + 1] : null
})

// ---------------------------------------------------------------------
// Progres belajar per lesson/rangkuman — disimpan di server lewat
// GET/POST /api/mondaishuu/progress (tabel user_mondaishuu_progress),
// bukan localStorage, supaya progres yang sama muncul di device manapun
// dan tidak hilang kalau cache browser dibersihkan. `crown: true` cuma
// didapat kalau satu set diselesaikan tanpa satupun jawaban salah (nyawa
// penuh sampai akhir). `done: true` tapi tanpa crown berarti sudah pernah
// selesai tapi masih ada kesalahan — tetap tercatat, cuma belum dapat
// mahkota.
// ---------------------------------------------------------------------
const progress = reactive({})
const progressLoaded = ref(false)

async function loadProgress() {
  try {
    const res = await $api('/mondaishuu/progress')

    Object.assign(progress, res.progress ?? {})
  }
  catch {
    // Kalau gagal (mis. offline), lanjut saja dengan progress kosong —
    // status tiap set akan tampil 'available', bukan memblokir halaman.
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

  // Fire-and-forget ke server — sumber kebenaran progres sekarang ada di
  // database (user_mondaishuu_progress), bukan localStorage. Gagal kirim
  // tidak boleh menghentikan alur kuis yang sedang dilihat pengguna.
  $api(`/mondaishuu/progress/${encodeURIComponent(key)}`, {
    method: 'POST',
    body: { perfect: !!perfect },
  }).catch(() => {
    // Best-effort saja — satu update progres yang gagal terkirim tidak
    // sepadan untuk mengganggu layar hasil kuis.
  })
}

// Sama seperti Jalur Belajar: pelajaran berikutnya terkunci sampai
// pelajaran sebelumnya diselesaikan (status `done`, boleh tanpa mahkota).
// Rangkuman butuh semua pelajaran di rentangnya selesai dulu. Admin bebas
// dari penguncian ini supaya bisa memeriksa/menguji materi mana saja.
const REVIEW_RANGES = {
  'r1-8': [1, 8],
  'r9-17': [9, 17],
  'r18-25': [18, 25],
  'r1-25': [1, 25],
}

function isLocked(key) {
  if (authStore.isAdmin)
    return false

  if (key.startsWith('l')) {
    const n = Number(key.slice(1))

    return n > 1 && !progress[`l${n - 1}`]?.done
  }

  const range = REVIEW_RANGES[key]
  if (!range)
    return false

  const [start, end] = range

  for (let i = start; i <= end; i++) {
    if (!progress[`l${i}`]?.done)
      return true
  }

  return false
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

// Ditampilkan di layar setup sebagai jalur belajar (seperti halaman
// "Jalur Belajar" utama) — satu jalur untuk pelajaran 1–25, satu lagi
// untuk set rangkuman. Semua node bisa langsung dibuka (tidak dikunci),
// statusnya saja yang berubah: belum pernah dikerjakan / selesai / mahkota.
const REVIEW_FOCUS = {
  'r1-8': 'Campuran soal Pelajaran 1–8',
  'r9-17': 'Campuran soal Pelajaran 9–17',
  'r18-25': 'Campuran soal Pelajaran 18–25',
  'r1-25': 'Campuran soal semua Pelajaran 1–25',
}

const REVIEW_FOCUS_EN = {
  'r1-8': 'Mixed review of Lessons 1–8',
  'r9-17': 'Mixed review of Lessons 9–17',
  'r18-25': 'Mixed review of Lessons 18–25',
  'r1-25': 'Mixed review of all Lessons 1–25',
}

// English gloss of each lesson's grammar focus — the Japanese terms
// (particles, verb forms, etc.) are kept as-is since they're the grammar
// items themselves, not text to translate; only the surrounding
// description changes with the locale.
const LESSON_FOCUS_EN = {
  1: 'これ・それ・あれ (this/that/that over there), 〜は〜です, possessive の',
  2: 'この・その・あの, ここ・そこ・あそこ (here/there), N の N',
  3: 'Numbers (prices), この・その・あの, どこの (which country/maker)',
  4: 'Time (〜時〜分), から・まで・に, days off',
  5: 'Dates, past tense, へ・と particles',
  6: 'Verb ます-form, を・に・で・と particles',
  7: 'で (tool/means), に (recipient), giving/receiving verbs',
  8: 'い-adjectives and な-adjectives',
  9: '好き・嫌い with が, から (reason)',
  10: 'あります・います (existence), 上・下・中 (above/below/inside)',
  11: 'Counters (〜つ・〜人・〜枚)',
  12: 'Comparisons (より, の中で一番 superlative)',
  13: '欲しい (want), 〜たい (want to), 〜に行きます',
  14: 'て-form, 〜てください',
  15: '〜ています (ongoing action / state)',
  16: 'Chaining て-form (〜て, 〜てから)',
  17: '〜なければなりません／〜なくてもいいです／〜ないでください',
  18: '〜ことができます (ability), dictionary form',
  19: '〜たことがあります (experience), 〜たり〜たり',
  20: 'Plain form (used in casual conversation)',
  21: '〜と思います, 〜と言いました, でしょう',
  22: 'Noun-modifying clauses',
  23: '〜とき (when)',
  24: 'あげます・もらいます・くれます (for someone else)',
  25: '〜たら (conditional), 〜ても',
}

function lessonFocus(l) {
  return locale.value === 'en' ? (LESSON_FOCUS_EN[l.id] ?? l.focus) : l.focus
}

function reviewFocus(id) {
  return locale.value === 'en' ? (REVIEW_FOCUS_EN[id] ?? REVIEW_FOCUS[id]) : REVIEW_FOCUS[id]
}

// Some questions' `translate` is still a plain Indonesian string (lessons
// not yet translated) and some are { id, en } (lessons 1–10, translated) —
// handle both so this can be filled in gradually without breaking anything.
function tr(value) {
  if (value == null)
    return value

  return typeof value === 'object' ? (locale.value === 'en' ? value.en : value.id) : value
}

const pathUnits = computed(() => [
  {
    id: 'lessons',
    title: t('mondaishuu.lessons_label'),
    lessons: LESSONS.map(l => ({
      id: `l${l.id}`,
      title: t('mondaishuu.lesson_label', { n: l.id }),
      focus: lessonFocus(l),
      status: statusFor(`l${l.id}`),
    })),
  },
  {
    id: 'reviews',
    title: t('mondaishuu.reviews_label'),
    lessons: REVIEWS.map(r => ({
      id: r.id,
      title: r.label,
      focus: reviewFocus(r.id),
      status: statusFor(r.id),
    })),
  },
])

function openPathLesson(lesson) {
  if (isLocked(lesson.id))
    return

  startTab(lesson.id)
}

// ---------------------------------------------------------------------
// Stage machine — same shape as the Kana/Kanji quiz (setup -> quiz ->
// result), so Mondaishuu now behaves and looks the same full-screen way.
// Plus `hearts_out`: reached only when nyawa (hearts) run out mid-quiz —
// the learner is stopped there and can't keep going, must retry instead.
// ---------------------------------------------------------------------
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
const selectedChoice = ref(null)
const scrambleBank = ref([])
const scrambleBuilt = ref([])
const isCorrect = ref(false)

// The active lesson/rangkuman's questions, shuffled into a fresh random
// order every time a quiz is (re)started — so opening the same lesson
// twice in a row won't show the same sequence.
const shuffledQuestions = ref([])

const currentQuestion = computed(() => shuffledQuestions.value?.[qIndex.value] ?? null)
const totalQuestions = computed(() => shuffledQuestions.value?.length ?? 0)

const progressPercent = computed(() => {
  if (!totalQuestions.value)
    return 0

  return Math.round((qIndex.value / totalQuestions.value) * 100)
})

// Bottom verdict bar, same shape `{ correct, correctAnswer }` as the
// Kana/Kanji quiz and the lesson player, so all four screens read alike.
const feedback = computed(() => {
  if (!answered.value)
    return null

  const q = currentQuestion.value
  const correctAnswer = q.type === 'mc' ? q.choices[q.answer] : q.words.join('')

  return {
    correct: isCorrect.value,
    correctAnswer,
    translate: tr(q.translate) ?? null,
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
  selectedChoice.value = null
  isCorrect.value = false
  const q = currentQuestion.value
  if (q?.type === 'scramble') {
    scrambleBuilt.value = []
    scrambleBank.value = shuffle(q.words)
  }
}

// Every question resolves the same way as the Kana/Kanji quiz: answer ->
// brief verdict -> automatic advance, as long as auto-next is on. With it
// off, the verdict stays put and the learner taps "Lanjut" themselves.
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

function startTab(key) {
  clearAdvanceTimer()
  activeKey.value = key
  qIndex.value = 0
  score.value = 0
  hearts.value = STARTING_HEARTS
  wrongCount.value = 0
  shuffledQuestions.value = shuffle(ALL_TABS.find(x => x.key === key)?.ref.questions ?? [])
  setupQuestion()
  stage.value = STAGE_QUIZ
}

function registerWrongAnswer() {
  wrongCount.value++
  hearts.value = Math.max(0, hearts.value - 1)
}

function chooseMc(i) {
  if (answered.value)
    return
  selectedChoice.value = i
  answered.value = true
  isCorrect.value = i === currentQuestion.value.answer
  if (isCorrect.value)
    score.value++
  else
    registerWrongAnswer()
  scheduleAdvance(isCorrect.value)
}

function pickWord(word, fromBank) {
  if (answered.value)
    return
  if (fromBank) {
    const idx = scrambleBank.value.indexOf(word)
    scrambleBank.value.splice(idx, 1)
    scrambleBuilt.value.push(word)
  }
  else {
    const idx = scrambleBuilt.value.indexOf(word)
    scrambleBuilt.value.splice(idx, 1)
    scrambleBank.value.push(word)
  }
}

function checkScramble() {
  if (answered.value)
    return
  answered.value = true
  isCorrect.value = scrambleBuilt.value.join('') === currentQuestion.value.words.join('')
  if (isCorrect.value)
    score.value++
  else
    registerWrongAnswer()
  scheduleAdvance(isCorrect.value)
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
    markProgress(activeKey.value, wrongCount.value === 0)
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
  shuffledQuestions.value = shuffle(active.value?.ref.questions ?? [])
  setupQuestion()
  stage.value = STAGE_QUIZ
}

// From the result screen straight into the next lesson's quiz — no trip
// back through the lesson picker in between.
function goToNextLesson() {
  if (nextLessonTab.value)
    startTab(nextLessonTab.value.key)
}

function backToSetup() {
  clearAdvanceTimer()
  stage.value = STAGE_SETUP
}

onBeforeUnmount(clearAdvanceTimer)
</script>

<template>
  <!-- SETUP: lesson / rangkuman picker — normal page, inside the default
       layout (navbar + sidebar visible, same content width as every other
       menu page), just like kosakata/kanji. -->
  <div v-if="stage === STAGE_SETUP">
    <div class="d-flex align-center justify-space-between mb-1 flex-wrap gap-2">
      <h4 class="text-h4 mb-0">
        {{ t('mondaishuu.title') }}
      </h4>
    </div>
    <p class="text-body-2 text-medium-emphasis mb-6">
      {{ t('mondaishuu.subtitle') }}
    </p>

    <!-- Progres belajar ala pemilihan kanji: kartu per pelajaran/rangkuman,
         mahkota untuk set yang diselesaikan tanpa satupun kesalahan, centang
         untuk yang pernah ditamatkan tapi masih ada yang salah, dan polos
         untuk yang belum pernah dicoba. Semua kartu bisa langsung diketuk.

         Ditunggu sampai progres selesai dimuat (progressLoaded) sebelum
         kartu dirender sama sekali — sama seperti halaman Kanji — supaya
         status/lencana progres tidak "muncul belakangan" setelah kartu
         polos sempat kelihatan sesaat. -->
    <div
      v-if="!progressLoaded"
      class="d-flex justify-center pa-10"
    >
      <VProgressCircular indeterminate color="primary" />
    </div>

    <div
      v-for="unit in pathUnits"
      v-else
      :key="unit.id"
      class="mb-8"
    >
      <h6 class="text-h6 mb-3">
        {{ unit.title }}
      </h6>
      <div class="mondaishuu-grid">
        <button
          v-for="lesson in unit.lessons"
          :key="lesson.id"
          type="button"
          class="mondaishuu-card"
          :class="`mondaishuu-card--${lesson.status}`"
          @click="openPathLesson(lesson)"
        >
          <VIcon
            v-if="lesson.status === 'mastered'"
            icon="tabler-crown"
            size="18"
            class="mondaishuu-card__badge mondaishuu-card__badge--crown"
          />
          <VIcon
            v-else-if="lesson.status === 'completed'"
            icon="tabler-circle-check-filled"
            size="16"
            color="success"
            class="mondaishuu-card__badge"
          />
          <span class="mondaishuu-card__title">
            <RubyText :text="lesson.title" />
          </span>
          <span
            v-if="lesson.focus"
            class="mondaishuu-card__focus"
          >
            <RubyText :text="lesson.focus" />
          </span>
        </button>
      </div>
    </div>
  </div>

  <!-- QUIZ / RESULT: same distraction-free full-screen shell as the
       Kana/Kanji quiz — entered only once a lesson/rangkuman is picked. -->
  <div v-else class="kana-fs kana-fs--page">
    <header class="kana-fs__header">
      <VBtn
        icon="tabler-x"
        variant="text"
        color="secondary"
        :aria-label="t('mondaishuu.quiz_back')"
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
        <div
          class="lp__fill"
          :style="{ inlineSize: `${progressPercent}%` }"
        />
      </div>
      <h5
        v-else
        class="text-h6 mb-0"
      >
        {{ t('mondaishuu.title') }}
      </h5>

      <div
        v-if="stage === STAGE_QUIZ"
        class="lp__hearts"
        :aria-label="t('mondaishuu.hearts')"
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
              <template v-if="active.kind === 'lesson'">
                {{ t('mondaishuu.lesson_label', { n: active.ref.id }) }} — <RubyText :text="active.ref.focus" />
              </template>
              <template v-else>
                {{ active.ref.label }}
              </template>
            </div>
            <VChip size="small" variant="tonal" color="primary">
              {{ t('mondaishuu.score', { score, total: totalQuestions }) }}
            </VChip>
          </div>

          <div class="text-caption text-medium-emphasis mb-4">
            {{ t('mondaishuu.question_of', { n: qIndex + 1, total: totalQuestions }) }}
          </div>

          <VCard class="pa-6">
            <div v-if="currentQuestion.img" class="text-center mb-3">
              <VIcon :icon="currentQuestion.img" size="56" color="primary" />
            </div>

            <template v-if="currentQuestion.type === 'mc'">
              <p
                class="text-h6 mb-4 text-center"
                style="white-space: pre-line;"
              >
                <RubyText :text="currentQuestion.q" />
              </p>
              <div class="d-flex flex-column ga-2">
                <button
                  v-for="(choice, ci) in currentQuestion.choices"
                  :key="ci"
                  type="button"
                  class="mondaishuu-choice"
                  :class="{
                    'mondaishuu-choice--selected': selectedChoice === ci && !answered,
                    'mondaishuu-choice--correct': answered && ci === currentQuestion.answer,
                    'mondaishuu-choice--wrong': answered && selectedChoice === ci && ci !== currentQuestion.answer,
                  }"
                  :disabled="answered"
                  @click="chooseMc(ci)"
                >
                  <RubyText :text="choice" />
                </button>
              </div>
            </template>

            <template v-else>
              <p class="text-body-1 mb-3 text-center">
                {{ t('mondaishuu.arrange_prompt') }}<br>
                <span class="text-medium-emphasis">「{{ tr(currentQuestion.translate) }}」</span>
              </p>

              <div
                class="mondaishuu-dropzone mb-3"
                :class="{
                  'mondaishuu-dropzone--correct': answered && isCorrect,
                  'mondaishuu-dropzone--wrong': answered && !isCorrect,
                }"
              >
                <button
                  v-for="(word, wi) in scrambleBuilt"
                  :key="`built-${wi}`"
                  type="button"
                  class="mondaishuu-chip mondaishuu-chip--built"
                  :disabled="answered"
                  @click="pickWord(word, false)"
                >
                  <RubyText :text="word" />
                </button>
                <span v-if="!scrambleBuilt.length" class="text-disabled">{{ t('mondaishuu.tap_words_hint') }}</span>
              </div>

              <div class="mondaishuu-bank mb-3">
                <button
                  v-for="(word, wi) in scrambleBank"
                  :key="`bank-${wi}`"
                  type="button"
                  class="mondaishuu-chip"
                  :disabled="answered"
                  @click="pickWord(word, true)"
                >
                  <RubyText :text="word" />
                </button>
              </div>

              <div class="text-center">
                <VBtn
                  v-if="!answered"
                  :disabled="!scrambleBuilt.length"
                  @click="checkScramble"
                >
                  {{ t('mondaishuu.check') }}
                </VBtn>
              </div>

              <VAlert
                v-if="answered && !isCorrect"
                type="warning"
                variant="tonal"
                density="compact"
                class="mt-3"
              >
                {{ t('mondaishuu.correct_order') }}
                <RubyText :text="currentQuestion.words.join('')" />
              </VAlert>
            </template>
          </VCard>
        </div>

        <!-- RESULT -->
        <VCard
          v-else-if="stage === STAGE_RESULT"
          class="pa-6 text-center mx-auto"
          max-width="480"
        >
          <div class="text-h3 mb-2">
            {{ wrongCount === 0 ? '👑' : '🎉' }}
          </div>
          <h5 class="text-h5 mb-1">
            {{ t('mondaishuu.done_title') }}
          </h5>
          <p class="text-h6 mb-2">
            {{ t('mondaishuu.score', { score, total: totalQuestions }) }}
          </p>

          <div
            v-if="wrongCount === 0"
            class="mondaishuu-crown-earned mb-6"
          >
            <VIcon icon="tabler-crown" size="22" />
            {{ t('mondaishuu.crown_earned') }}
          </div>
          <p
            v-else
            class="text-body-2 text-medium-emphasis mb-6"
          >
            {{ t('mondaishuu.crown_hint') }}
          </p>

          <div class="d-flex flex-wrap gap-2 justify-center">
            <VBtn
              variant="tonal"
              @click="backToSetup"
            >
              {{ t('mondaishuu.quiz_back') }}
            </VBtn>
            <VBtn
              variant="tonal"
              prepend-icon="tabler-refresh"
              @click="restartQuiz"
            >
              {{ t('mondaishuu.retry') }}
            </VBtn>
            <VBtn
              v-if="nextLessonTab"
              color="primary"
              append-icon="tabler-arrow-right"
              @click="goToNextLesson"
            >
              {{ t('mondaishuu.next_lesson', { n: nextLessonTab.ref.id }) }}
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
            {{ t('mondaishuu.out_of_hearts') }}
          </h5>
          <p class="text-body-2 text-medium-emphasis mb-6">
            {{ t('mondaishuu.out_of_hearts_hint') }}
          </p>
          <div class="d-flex flex-wrap gap-2 justify-center">
            <VBtn
              variant="tonal"
              @click="backToSetup"
            >
              {{ t('mondaishuu.quiz_back') }}
            </VBtn>
            <VBtn
              color="primary"
              prepend-icon="tabler-refresh"
              @click="restartQuiz"
            >
              {{ t('mondaishuu.retry') }}
            </VBtn>
          </div>
        </VCard>
      </div>
    </main>

    <!-- Notifikasi benar/salah — bar tetap di bawah, gaya sama dengan
         jalur belajar (lesson player), bukan popup di tengah layar. -->
    <footer
      v-if="feedback && stage === STAGE_QUIZ"
      class="lp__footer"
      :class="feedback.correct ? 'is-correct' : 'is-incorrect'"
    >
      <div class="lp__footer-inner">
        <div
          class="lp__verdict"
          :style="{ color: feedback.correct ? 'rgb(var(--v-theme-success))' : 'rgb(var(--v-theme-error))' }"
        >
          <VIcon
            :icon="feedback.correct ? 'tabler-circle-check-filled' : 'tabler-circle-x-filled'"
            size="36"
          />
          <div>
            {{ feedback.correct ? t('mondaishuu.correct') : t('mondaishuu.incorrect') }}
            <small v-if="!feedback.correct" lang="ja">
              {{ t('mondaishuu.correct_answer') }} <strong>{{ feedback.correctAnswer }}</strong>
            </small>
            <small v-else lang="ja">
              <strong>{{ feedback.correctAnswer }}</strong>
            </small>
            <small v-if="feedback.translate" class="d-block" :lang="locale">
              {{ t('mondaishuu.meaning') }}: {{ feedback.translate }}
            </small>
          </div>
        </div>

        <VBtn
          v-if="!autoNext"
          color="primary"
          append-icon="tabler-arrow-right"
          class="lp__cta"
          @click="nextQuestion"
        >
          {{ hearts <= 0 ? t('mondaishuu.out_of_hearts_cta') : (qIndex + 1 >= totalQuestions ? t('mondaishuu.finish') : t('mondaishuu.next')) }}
        </VBtn>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* ---------- crown badge on the result screen ---------- */
.mondaishuu-crown-earned {
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

/* ---------- lesson grid (setup stage) ---------- */
.mondaishuu-grid {
  display: grid;
  gap: 10px;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
}

.mondaishuu-card {
  position: relative;
  display: flex;
  overflow: hidden;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  color: rgb(var(--v-theme-on-surface));
  cursor: pointer;
  gap: 6px;
  min-block-size: 108px;
  padding-block: 18px 14px;
  padding-inline: 12px;
  text-align: center;
  transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease, background 0.15s ease;
}

/* status accent along the top edge — same language as the kanji picker */
.mondaishuu-card::before {
  position: absolute;
  background: transparent;
  block-size: 3px;
  content: "";
  inline-size: 100%;
  inset-block-start: 0;
  inset-inline-start: 0;
}

.mondaishuu-card--completed::before { background: rgb(var(--v-theme-success)); }
.mondaishuu-card--completed { background: rgba(var(--v-theme-success), 0.06); }

.mondaishuu-card--mastered::before { background: rgb(var(--v-theme-warning)); }

.mondaishuu-card--mastered {
  border-color: rgba(var(--v-theme-warning), 0.5);
  background: rgba(var(--v-theme-warning), 0.08);
}

.mondaishuu-card:hover {
  border-color: rgba(var(--v-theme-primary), 0.7);
  box-shadow: 0 10px 20px -10px rgba(var(--v-theme-primary), 0.5);
  transform: translateY(-3px);
}

.mondaishuu-card--mastered:hover {
  border-color: rgb(var(--v-theme-warning));
  box-shadow: 0 10px 20px -10px rgba(var(--v-theme-warning), 0.55);
}

.mondaishuu-card:focus-visible {
  outline: 2px solid rgb(var(--v-theme-primary));
  outline-offset: 2px;
}

.mondaishuu-card__badge {
  position: absolute;
  inset-block-start: 7px;
  inset-inline-end: 7px;
}

.mondaishuu-card__badge--crown {
  color: #c9971e;
}

.mondaishuu-card__title {
  font-size: 0.85rem;
  font-weight: 600;
}

.mondaishuu-card__focus {
  display: -webkit-box;
  overflow: hidden;
  -webkit-box-orient: vertical;
  color: rgba(var(--v-theme-on-surface), 0.6);
  font-size: 0.7rem;
  -webkit-line-clamp: 2;
  line-height: 1.3;
}

@media (max-width: 599px) {
  .mondaishuu-grid {
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  }

  .mondaishuu-card {
    min-block-size: 96px;
    padding-block: 14px 10px;
    padding-inline: 8px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .mondaishuu-card { transition: none; }
  .mondaishuu-card:hover { transform: none; }
}

.mondaishuu-choice {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.16);
  border-radius: 10px;
  background: rgb(var(--v-theme-surface));
  color: rgb(var(--v-theme-on-surface));
  cursor: pointer;
  font-size: 1rem;
  padding-block: 12px;
  padding-inline: 16px;
  text-align: start;
  transition: background 0.15s ease, border-color 0.15s ease;
}

.mondaishuu-choice:hover:not(:disabled) {
  border-color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.06);
}

.mondaishuu-choice--selected {
  border-color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.1);
}

.mondaishuu-choice--correct {
  border-color: rgb(var(--v-theme-success));
  background: rgba(var(--v-theme-success), 0.14);
}

.mondaishuu-choice--wrong {
  border-color: rgb(var(--v-theme-error));
  background: rgba(var(--v-theme-error), 0.14);
}

.mondaishuu-choice:disabled {
  cursor: default;
}

.mondaishuu-dropzone {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  padding: 10px;
  border: 1px dashed rgba(var(--v-theme-on-surface), 0.24);
  border-radius: 10px;
  background: rgba(var(--v-theme-on-surface), 0.02);
  gap: 8px;
  min-block-size: 56px;
}

.mondaishuu-dropzone--correct {
  border-color: rgb(var(--v-theme-success));
  background: rgba(var(--v-theme-success), 0.08);
}

.mondaishuu-dropzone--wrong {
  border-color: rgb(var(--v-theme-error));
  background: rgba(var(--v-theme-error), 0.08);
}

.mondaishuu-bank {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.mondaishuu-chip {
  border: 1px solid rgba(var(--v-theme-primary), 0.4);
  border-radius: 999px;
  background: rgba(var(--v-theme-primary), 0.08);
  color: rgb(var(--v-theme-primary));
  cursor: pointer;
  font-size: 1rem;
  padding-block: 8px;
  padding-inline: 14px;
  transition: transform 0.1s ease;
}

.mondaishuu-chip:hover:not(:disabled) {
  transform: translateY(-1px);
}

.mondaishuu-chip--built {
  border-color: rgba(var(--v-theme-on-surface), 0.3);
  background: rgb(var(--v-theme-surface));
  color: rgb(var(--v-theme-on-surface));
}

.mondaishuu-chip:disabled {
  cursor: default;
  opacity: 0.85;
}
</style>
