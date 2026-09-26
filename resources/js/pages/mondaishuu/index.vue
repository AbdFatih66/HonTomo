<script setup>
// Mondaishuu — latihan soal bergaya kuis interaktif per pelajaran (1–25)
// plus 4 set rangkuman. Konten disusun ulang secara orisinal: pola dan
// cakupan tata bahasa tiap pelajaran mengikuti kurikulum Minna no Nihongo I,
// tapi semua kalimat/kosakata contoh ditulis sendiri (bukan hasil scan buku).
// Kanji memakai furigana lewat RubyText (markup 漢字《かんじ》), tanpa romaji,
// supaya latihannya benar-benar melatih baca tulis Jepang.
import RubyText from '@/components/learning/RubyText.vue'
import LearningPath from '@/components/learning/LearningPath.vue'
import { useAutoNext } from '@/composables/useAutoNext'

// Unlike the old version, this page does NOT force the blank layout for
// the whole route. The lesson/rangkuman picker (STAGE_SETUP) stays inside
// the normal layout — navbar + sidebar visible — just like every other
// menu page (kosakata, kanji list, dsb). Only once a quiz actually starts
// does the page switch to the same distraction-free full-screen shell used
// by the Kana/Kanji quiz (`.kana-fs--page`), so the "full layar" mode is
// reserved for doing questions, not for browsing which lesson to open.
const { t } = useI18n()
const autoNext = useAutoNext()

// ---------------------------------------------------------------------
// Bank soal. Dua jenis soal:
//  - mc: pilihan ganda, `choices` 4 opsi, `answer` = index yang benar.
//  - scramble: kata-kata dalam `words` (urutan yang benar) diacak di UI,
//    pengguna menyusunnya kembali dengan mengetuk kata.
// `img` (opsional) adalah nama ikon tabler yang mewakili konteks soal.
// ---------------------------------------------------------------------
const LESSONS = [
  { id: 1, focus: 'これ・それ・あれ、〜は〜です、の（kepemilikan）', questions: [
    { type: 'mc', q: '（自分《じぶん》に 近《ちか》い 物《もの》）＿＿＿は 私《わたし》の 本《ほん》です。', choices: ['これ', 'それ', 'あれ', 'どれ'], answer: 0 , translate: 'Ini adalah buku saya.' },
    { type: 'mc', q: '（相手《あいて》に 近《ちか》い 物《もの》）＿＿＿は 辞書《じしょ》ですか。', choices: ['これ', 'それ', 'あれ', 'どなた'], answer: 1, img: 'tabler-book-2' , translate: 'Itu kamus? (dekat lawan bicara)' },
    { type: 'mc', q: 'これは 私《わたし》＿＿＿ 傘《かさ》です。', choices: ['は', 'の', 'を', 'も'], answer: 1, img: 'tabler-umbrella' , translate: 'Ini payung saya.' },
    { type: 'mc', q: 'あの 人《ひと》は＿＿＿ですか。（丁寧《ていねい》に「だれ」）', choices: ['どなた', 'どこ', 'なに', 'いつ'], answer: 0 , translate: 'Siapa orang itu? (sopan)' },
    { type: 'mc', q: 'サントスさんは ブラジル人《じん》です。マリアさん＿＿＿ ブラジル人《じん》です。', choices: ['も', 'の', 'を', 'は'], answer: 0 , translate: 'Maria juga orang Brasil.' },
    { type: 'mc', q: 'ミラーさんは 学生《がくせい》＿＿＿。（否定《ひてい》）', choices: ['じゃありません', 'ではします', 'ですか', 'も'], answer: 0 , translate: 'Miller bukan mahasiswa.' },
    { type: 'mc', q: '山田《やまだ》さんは 会社員《かいしゃいん》＿＿＿。', choices: ['です', 'ですか', 'でした', 'ましょう'], answer: 0 , translate: 'Yamada adalah karyawan perusahaan.' },
    { type: 'scramble', translate: 'Beliau itu siapa? (sopan)', words: ['あの', 'かた', 'は', 'どなた', 'ですか'] },
    { type: 'scramble', translate: 'Ya, saya mahasiswa.', words: ['はい', '、', '学生《がくせい》', 'です'] },
    { type: 'scramble', translate: 'Ini adalah kamus bahasa Jepang saya.', words: ['これ', 'は', '私《わたし》', 'の', '日本語《にほんご》', 'の', '辞書《じしょ》', 'です'] },
    { type: 'mc', q: 'これは 田中《たなか》さん＿＿＿ 傘《かさ》です。', choices: ['の', 'は', 'を', 'も'], answer: 0 , translate: 'Ini payung Tanaka.' },
    { type: 'mc', q: 'サントスさんは 学生《がくせい》ですか。…いいえ、学生《がくせい》＿＿＿。会社員《かいしゃいん》です。', choices: ['じゃありません', 'ですか', 'でした', 'も'], answer: 0 , translate: 'Tidak, bukan mahasiswa. Karyawan perusahaan.' },
    { type: 'scramble', translate: 'Apakah Anda orang IMC?', words: ['IMC', 'の', '方《かた》', 'ですか'] },
    { type: 'scramble', translate: 'Ini bukan payung saya.', words: ['これ', 'は', '私《わたし》', 'の', '傘《かさ》', 'じゃありません'] },
    { type: 'mc', q: '木村《きむら》：はじめまして。木村《きむら》です。\nリナ：はじめまして。リナです。＿＿＿。', choices: ['どうぞよろしく', 'いただきます', 'お帰《かえ》りなさい', 'かしこまりました'], answer: 0, img: 'tabler-message-circle' , translate: 'Senang berkenalan dengan Anda.' },
    { type: 'mc', q: 'A：あの　方《かた》は　どなたですか。\nB：＿＿＿。ミラーさんです。', choices: ['山田《やまだ》さんです', 'はい、会社員《かいしゃいん》です', 'いいえ、学生《がくせい》です', '38歳《さい》です'], answer: 0, img: 'tabler-message-circle' , translate: 'Itu Yamada.' },
    { type: 'mc', q: 'ワンさんも 学生《がくせい》ですか。…いいえ、ワンさん＿＿＿ 学生《がくせい》じゃありません。', choices: ['は', 'も', 'の', 'を'], answer: 0 , translate: 'Wang bukan mahasiswa.' },
    { type: 'mc', q: 'グプタさんは IMC＿＿＿ 社員《しゃいん》です。', choices: ['の', 'は', 'を', 'に'], answer: 0 , translate: 'Gupta adalah karyawan IMC.' },
    { type: 'scramble', translate: 'Apakah ini buku Anda?', words: ['これ', 'は', 'あなた', 'の', '本《ほん》', 'ですか'] },
    { type: 'scramble', translate: 'Ya, saya karyawan perusahaan.', words: ['はい', '、', '会社員《かいしゃいん》', 'です'] },
  ] },
  { id: 2, focus: 'この・その・あの、ここ・そこ・あそこ、N の N', questions: [
    { type: 'mc', q: '＿＿＿ かばんは 私《わたし》のです。（自分《じぶん》に 近《ちか》い）', choices: ['この', 'その', 'あの', 'どの'], answer: 0, img: 'tabler-briefcase' , translate: 'Tas ini punya saya.' },
    { type: 'mc', q: '受付《うけつけ》は＿＿＿ですか。（丁寧《ていねい》に「どこ」）', choices: ['ここ', 'そこ', 'どちら', 'どなた'], answer: 2 , translate: 'Resepsionis di sebelah mana? (sopan)' },
    { type: 'mc', q: 'これは＿＿＿の カメラですか。…日本《にほん》のです。', choices: ['どこ', 'なに', 'だれ', 'いつ'], answer: 0, img: 'tabler-camera' , translate: 'Kamera ini buatan mana? ...Buatan Jepang.' },
    { type: 'mc', q: 'この かばんは＿＿＿の ですか。…わたしのです。', choices: ['だれ', 'なに', 'どこ', 'いつ'], answer: 0 , translate: 'Tas ini punya siapa? ...Punya saya.' },
    { type: 'mc', q: 'エレベーターは＿＿＿ですか。…あそこです。', choices: ['どこ', 'だれ', 'なん', 'どなた'], answer: 0, img: 'tabler-building' , translate: 'Lift di mana? ...Di sana.' },
    { type: 'mc', q: 'これは＿＿＿の 雑誌《ざっし》ですか。…車《くるま》の 雑誌《ざっし》です。', choices: ['なん', 'だれ', 'どこ', 'どの'], answer: 0 , translate: 'Ini majalah tentang apa? ...Majalah mobil.' },
    { type: 'scramble', translate: 'Toilet ada di sana. (dekat lawan bicara)', words: ['トイレ', 'は', 'そこ', 'です'] },
    { type: 'scramble', translate: 'Guru bahasa Perancis', words: ['フランス語《フランスご》', 'の', '先生《せんせい》'] },
    { type: 'scramble', translate: 'Tas ini bukan tas saya.', words: ['この', 'かばん', 'は', '私《わたし》', 'の', 'じゃありません'] },
    { type: 'mc', q: '＿＿＿ 建物《たてもの》は 何《なん》ですか。（少《すこ》し 遠《とお》い）', choices: ['この', 'その', 'あの', 'どの'], answer: 2 , translate: 'Gedung itu apa? (agak jauh)' },
    { type: 'mc', q: 'この 傘《かさ》は＿＿＿の ですか。…あなたのです。', choices: ['だれ', 'なん', 'どこ', 'いつ'], answer: 0 , translate: 'Payung ini punya siapa? ...Punya Anda.' },
    { type: 'scramble', translate: 'Kamera itu buatan negara mana?', words: ['それ', 'は', 'どこ', 'の', 'カメラ', 'ですか'] },
    { type: 'mc', q: 'A：すみません、受付《うけつけ》は　どちらですか。\nB：＿＿＿。', choices: ['あちらです', 'それはノートです', '3階《がい》です', 'わたしのです'], answer: 0, img: 'tabler-message-circle' , translate: 'Permisi, resepsionis di sebelah mana? ...Di sebelah sana.' },
    { type: 'mc', q: 'A：これは　何《なん》の　雑誌《ざっし》ですか。\nB：＿＿＿。', choices: ['車《くるま》の　雑誌《ざっし》です', 'はい、そうです', '3,800円《えん》です', 'わたしのです'], answer: 0, img: 'tabler-message-circle' , translate: 'Ini majalah apa? ...Majalah mobil.' },
    { type: 'mc', q: '受付《うけつけ》は＿＿＿ですか。', choices: ['どちら', 'どなた', 'なん', 'いつ'], answer: 0 , translate: 'Resepsionis di sebelah mana?' },
    { type: 'mc', q: 'これは＿＿＿の CDですか。…日本語《にほんご》の CDです。', choices: ['なん', 'だれ', 'どこ', 'いつ'], answer: 0 , translate: 'Ini CD apa? ...CD bahasa Jepang.' },
    { type: 'mc', q: 'あの 人《ひと》は＿＿＿ですか。…佐藤《さとう》さんです。', choices: ['だれ', 'どこ', 'なん', 'いつ'], answer: 0 , translate: 'Siapa orang itu? ...Itu Sato.' },
    { type: 'scramble', translate: 'Payung ini bukan milik Anda.', words: ['この', '傘《かさ》', 'は', 'あなた', 'の', 'じゃありません'] },
    { type: 'scramble', translate: 'Toilet ada di sana.', words: ['トイレ', 'は', 'あそこ', 'です'] },
    { type: 'scramble', translate: 'Ini adalah kamera buatan Jepang.', words: ['これ', 'は', '日本《にほん》', 'の', 'カメラ', 'です'] },
  ] },
  { id: 3, focus: '数字《すうじ》（値段《ねだん》）、この・その・あの、どこの', questions: [
    { type: 'mc', q: '¥5,300 の 読《よ》み方《かた》は？', choices: ['ごせんさんびゃくえん', 'ごまんさんぜんえん', 'ごひゃくさんじゅうえん', 'ごせんさんじゅうえん'], answer: 0, img: 'tabler-tag' , translate: '5.300 yen' },
    { type: 'mc', q: '¥18,900 の 読《よ》み方《かた》は？', choices: ['いちまんはっせんきゅうひゃくえん', 'じゅうはちまんきゅうせんえん', 'いちまんはちひゃくきゅうじゅうえん', 'じゅうはっせんきゅうひゃくえん'], answer: 0, img: 'tabler-tag' , translate: '18.900 yen' },
    { type: 'mc', q: 'すみません、その 時計《とけい》を 見《み》せて＿＿＿。', choices: ['ください', 'くださいか', 'です', 'ました'], answer: 0, img: 'tabler-clock' , translate: 'Permisi, tolong perlihatkan jam itu.' },
    { type: 'mc', q: 'マリアさんの お国《くに》は＿＿＿ですか。…ブラジルです。', choices: ['どちら', 'どこの', 'だれの', 'なんの'], answer: 0 , translate: 'Negara asal Maria mana? ...Brasil.' },
    { type: 'mc', q: 'これは どこ＿＿＿ ワインですか。…イタリアのです。', choices: ['の', 'は', 'を', 'に'], answer: 0 , translate: 'Ini anggur dari mana? ...Dari Italia.' },
    { type: 'mc', q: 'カメラ売《う》り場《ば》は＿＿＿ですか。…3階《かい》です。', choices: ['なんかい', 'いくら', 'だれ', 'どなた'], answer: 0 , translate: 'Bagian kamera di lantai berapa? ...Lantai 3.' },
    { type: 'scramble', translate: 'Jam ini buatan Swiss.', words: ['この', '時計《とけい》', 'は', 'スイス', 'の', 'です'] },
    { type: 'scramble', translate: 'Silakan lihat.', words: ['どうぞ', '、', '見《み》て', 'ください'] },
    { type: 'scramble', translate: 'Berapa harga tas ini?', words: ['この', 'かばん', 'は', 'いくら', 'ですか'] },
    { type: 'mc', q: '¥124,000 の 読《よ》み方《かた》は？', choices: ['じゅうにまんよんせんえん', 'いちまんにせんよんひゃくえん', 'じゅうにまんよんひゃくえん', 'ひゃくにじゅうよんまんえん'], answer: 0 , translate: '124.000 yen' },
    { type: 'mc', q: 'すみません。そのワイン＿＿＿ 見《み》せてください。', choices: ['を', 'は', 'に', 'の'], answer: 0 , translate: 'Permisi. Tolong perlihatkan anggur itu.' },
    { type: 'scramble', translate: 'Kalau begitu, saya ambil yang ini.', words: ['じゃ', '、', 'これ', 'を', 'ください'] },
    { type: 'mc', q: '店員《てんいん》：いらっしゃいませ。\n客《きゃく》：すみません、この　ネクタイは　いくらですか。\n店員《てんいん》：＿＿＿。', choices: ['6,400円《えん》です', 'どうぞ', 'かしこまりました', 'ちょっと'], answer: 0, img: 'tabler-message-circle' , translate: 'Permisi, dasi ini berapa? ...6.400 yen.' },
    { type: 'mc', q: 'A：すみません、その　時計《とけい》を　見《み》せて　ください。\nB：＿＿＿。', choices: ['どうぞ', 'かしこまりました', 'いいえ、けっこうです', 'また今度《こんど》'], answer: 0, img: 'tabler-message-circle' , translate: 'Silakan.' },
    { type: 'mc', q: 'これは どこ＿＿＿ワインですか。…イタリアのです。', choices: ['の', 'は', 'を', 'に'], answer: 0 , translate: 'Ini anggur dari mana? ...Dari Italia.' },
    { type: 'mc', q: '¥4,700,000 の 読《よ》み方《かた》は？', choices: ['よんひゃくななじゅうまんえん', 'よんじゅうななまんえん', 'よんせんななひゃくまんえん', 'よんひゃくまんななせんえん'], answer: 0 , translate: '4.700.000 yen' },
    { type: 'mc', q: '松本《まつもと》さんの 車《くるま》は＿＿＿ですか。…あそこです。', choices: ['どこ', 'いくら', 'だれ', 'なん'], answer: 0 , translate: 'Mobil Matsumoto di mana? ...Di sana.' },
    { type: 'scramble', translate: 'Ini adalah majalah tentang mobil.', words: ['これ', 'は', '車《くるま》', 'の', '雑誌《ざっし》', 'です'] },
    { type: 'scramble', translate: 'Berapa harga komputer ini?', words: ['この', 'コンピューター', 'は', 'いくら', 'ですか'] },
    { type: 'scramble', translate: 'Silakan berikan yang ini.', words: ['これ', 'を', 'ください'] },
  ] },
  { id: 4, focus: '時間《じかん》（〜時《じ》〜分《ふん》）、から・まで・に、休《やす》み', questions: [
    { type: 'mc', q: '3:15 の 読《よ》み方《かた》は？', choices: ['さんじじゅうごふん', 'さんじゅうごふん', 'さんじはん', 'じゅうごじさんぷん'], answer: 0, img: 'tabler-clock-hour-3' , translate: 'Jam 3 lewat 15 menit' },
    { type: 'mc', q: '9:40 の 読《よ》み方《かた》は？', choices: ['くじよんじゅっぷん', 'きゅうじよんじゅうふん', 'くじよんじ', 'きゅうじじゅっぷん'], answer: 0, img: 'tabler-clock-hour-9' , translate: 'Jam 9 lewat 40 menit' },
    { type: 'mc', q: '銀行《ぎんこう》は 9時《じ》＿＿＿3時《じ》＿＿＿です。', choices: ['から／まで', 'まで／から', 'に／で', 'を／に'], answer: 0 , translate: 'Bank buka dari jam 9 sampai jam 3.' },
    { type: 'mc', q: '美術館《びじゅつかん》の 休《やす》みは 月曜日《げつようび》＿＿＿ 木曜日《もくようび》です。', choices: ['と', 'から', 'まで', 'に'], answer: 0 , translate: 'Museum libur hari Senin dan Kamis.' },
    { type: 'mc', q: '毎晩《まいばん》 11時半《じゅういちじはん》＿＿＿ 寝《ね》ます。', choices: ['に', 'で', 'を', 'へ'], answer: 0 , translate: 'Setiap malam saya tidur jam setengah 12.' },
    { type: 'mc', q: 'きょうは 何曜日《なんようび》ですか。…＿＿＿です。', choices: ['水曜日《すいようび》', '9時《じ》', '3月《がつ》', '休《やす》み'], answer: 0 , translate: 'Hari ini hari apa? ...Hari Rabu.' },
    { type: 'scramble', translate: 'Setiap pagi saya bangun jam 6.', words: ['毎朝《まいあさ》', '6時《ろくじ》', 'に', '起《お》きます'] },
    { type: 'scramble', translate: 'Hari ini adalah hari libur.', words: ['きょう', 'は', '休《やす》み', 'です'] },
    { type: 'scramble', translate: 'Perpustakaan buka dari jam 9 sampai jam 6.', words: ['図書館《としょかん》', 'は', '9時《くじ》', 'から', '6時《ろくじ》', 'まで', 'です'] },
    { type: 'mc', q: 'ニューヨークは 今《いま》 午前《ごぜん》4時《じ》＿＿＿です。', choices: ['×', 'に', 'で', 'の'], answer: 0 , translate: 'Sekarang di New York jam 4 pagi.' },
    { type: 'mc', q: 'きのうの 晩《ばん》8時《じ》＿＿＿10時《じ》＿＿＿ 勉強《べんきょう》しました。', choices: ['から／まで', 'まで／から', 'に／で', 'を／へ'], answer: 0 , translate: 'Kemarin malam saya belajar dari jam 8 sampai jam 10.' },
    { type: 'scramble', translate: 'Toko itu buka dari jam 9 pagi sampai jam 8 malam.', words: ['あの', '店《みせ》', 'は', '朝《あさ》', '9時《くじ》', 'から', '夜《よる》', '8時《はちじ》', 'まで', 'です'] },
    { type: 'mc', q: 'A：すみません、この　図書館《としょかん》は　何時《なんじ》までですか。\nB：＿＿＿。', choices: ['6時《じ》までです', '月曜日《げつようび》です', 'あそこです', 'わたしのです'], answer: 0, img: 'tabler-message-circle' , translate: 'Perpustakaan ini buka sampai jam berapa? ...Sampai jam 6.' },
    { type: 'mc', q: 'A：美術館《びじゅつかん》の　休《やす》みは　何曜日《なんようび》ですか。\nB：＿＿＿。', choices: ['水曜日《すいようび》です', '9時《じ》からです', 'あそこです', '3階《がい》です'], answer: 0, img: 'tabler-message-circle' , translate: 'Museum libur hari apa? ...Hari Rabu.' },
    { type: 'mc', q: 'あさって＿＿＿ 日曜日《にちようび》です。', choices: ['は', 'に', 'を', 'で'], answer: 0 , translate: 'Lusa hari Minggu.' },
    { type: 'mc', q: 'この 図書館《としょかん》は 土曜日《どようび》＿＿＿ 午後《ごご》 休《やす》みです。', choices: ['×', 'に', 'で', 'を'], answer: 0 , translate: 'Perpustakaan ini libur Sabtu sore.' },
    { type: 'mc', q: '10:00 の 読《よ》み方《かた》は？', choices: ['じゅうじ', 'とおじ', 'じっじ', 'じゅっじ'], answer: 0 , translate: 'Jam 10 tepat' },
    { type: 'scramble', translate: 'Perusahaan saya mulai jam 9 dan selesai jam 5.', words: ['わたし', 'の', '会社《かいしゃ》', 'は', '9時《くじ》', 'から', '5時《ごじ》', 'までです'] },
    { type: 'scramble', translate: 'Bank tutup jam 3.', words: ['銀行《ぎんこう》', 'は', '3時《さんじ》', 'に', '終《お》わります'] },
    { type: 'scramble', translate: 'Sekarang New York jam berapa?', words: ['ニューヨーク', 'は', '今《いま》', '何時《なんじ》', 'ですか'] },
  ] },
  { id: 5, focus: '日《ひ》にち、過去形《かこけい》、へ・と', questions: [
    { type: 'mc', q: '5月《がつ》14日《じゅうよっか》の 読《よ》み方《かた》は？', choices: ['ごがつじゅうよっか', 'ごがついちよんにち', 'ごつきじゅうよんにち', 'ごがつじゅうよんにち'], answer: 0, img: 'tabler-calendar' , translate: 'Tanggal 14 Mei' },
    { type: 'mc', q: '9月《がつ》20日《はつか》の 読《よ》み方《かた》は？', choices: ['くがつはつか', 'きゅうがつにじゅうにち', 'くがつにじゅうび', 'くつきはつか'], answer: 0, img: 'tabler-calendar' , translate: 'Tanggal 20 September' },
    { type: 'mc', q: '友達《ともだち》＿＿＿ 映画《えいが》を 見《み》ました。', choices: ['と', 'へ', 'の', 'は'], answer: 0 , translate: 'Saya menonton film bersama teman.' },
    { type: 'mc', q: '先月《せんげつ》 日本《にほん》＿＿＿ 来《き》ました。', choices: ['へ', 'で', 'を', 'に「×」'], answer: 0 , translate: 'Bulan lalu saya datang ke Jepang.' },
    { type: 'mc', q: '誕生日《たんじょうび》は 9月《がつ》＿＿＿ですか。…1日《ついたち》です。', choices: ['なんにち', 'いつ', 'なんじ', 'どこ'], answer: 0 , translate: 'Ulang tahunnya tanggal berapa bulan September? ...Tanggal 1.' },
    { type: 'mc', q: '一人《ひとり》＿＿＿ 行《い》きましたか。…友達《ともだち》と 行《い》きました。', choices: ['で', 'に', 'を', 'は'], answer: 0 , translate: 'Apakah pergi sendirian? ...Saya pergi bersama teman.' },
    { type: 'scramble', translate: 'Kemarin saya pergi ke Kyoto.', words: ['きのう', '京都《きょうと》', 'へ', '行《い》きました'] },
    { type: 'scramble', translate: 'Tahun lalu saya datang ke Jepang.', words: ['去年《きょねん》', '日本《にほん》', 'へ', '来《き》ました'] },
    { type: 'scramble', translate: 'Kapan Anda kembali ke negara Anda?', words: ['いつ', '国《くに》', 'へ', '帰《かえ》りますか'] },
    { type: 'mc', q: '先月《せんげつ》の 25日《にじゅうごにち》＿＿＿ 電車《でんしゃ》＿＿＿ カリナさん＿＿＿ うち＿＿＿ 行《い》きました。', choices: ['に／で／と／へ', 'で／に／と／を', 'に／を／で／へ', 'を／で／に／へ'], answer: 0 , translate: 'Tanggal 25 bulan lalu saya naik kereta pergi ke rumah Karina.' },
    { type: 'scramble', translate: 'Minggu lalu hari Sabtu saya tidak pergi ke mana-mana.', words: ['先週《せんしゅう》', 'の', '土曜日《どようび》', 'どこも', '行《い》きませんでした'] },
    { type: 'mc', q: 'A：いつ　日本《にほん》へ　来《き》ましたか。\nB：＿＿＿。', choices: ['去年《きょねん》の　4月《がつ》に　来《き》ました', '新幹線《しんかんせん》で　来《き》ました', '友達《ともだち》と　来《き》ました', 'とても　楽《たの》しいです'], answer: 0, img: 'tabler-message-circle' , translate: 'Kapan datang ke Jepang? ...Saya datang bulan April tahun lalu.' },
    { type: 'mc', q: 'A：先週《せんしゅう》の　日曜日《にちようび》、どこか　行《い》きましたか。\nB：いいえ、＿＿＿。', choices: ['どこも　行《い》きませんでした', 'どこか　行《い》きました', 'だれも　いません', '何《なに》も　欲《ほ》しいです'], answer: 0, img: 'tabler-message-circle' , translate: 'Minggu lalu hari Minggu, apakah pergi ke suatu tempat? ...Tidak, saya tidak pergi ke mana-mana.' },
    { type: 'mc', q: '来年《らいねん》の 8月《がつ》に 国《くに》へ＿＿＿。', choices: ['帰《かえ》ります', '帰《かえ》りました', '帰《かえ》る', '帰《かえ》って'], answer: 0 , translate: 'Bulan Agustus tahun depan saya pulang ke negara asal.' },
    { type: 'mc', q: '9月《がつ》9日《ここのか》の 読《よ》み方《かた》は？', choices: ['くがつここのか', 'きゅうがつくにち', 'くがつくにち', 'きゅうがつここのか'], answer: 0 , translate: 'Tanggal 9 September' },
    { type: 'mc', q: '誕生日《たんじょうび》は＿＿＿ですか。…9月《がつ》15日《じゅうごにち》です。', choices: ['いつ', 'どこ', 'だれ', 'なん'], answer: 0 , translate: 'Kapan ulang tahunnya? ...Tanggal 15 September.' },
    { type: 'scramble', translate: 'Bulan lalu saya pergi ke Jepang.', words: ['先月《せんげつ》', '日本《にほん》', 'へ', '行《い》きました'] },
    { type: 'scramble', translate: 'Kemarin saya pulang jam 10.', words: ['きのう', '10時《じゅうじ》', 'に', 'うち', 'へ', '帰《かえ》りました'] },
    { type: 'scramble', translate: 'Saya pergi seorang diri.', words: ['一人《ひとり》', 'で', '行《い》きました'] },
    { type: 'scramble', translate: 'Besok pagi kereta apinya jam berapa?', words: ['あした', 'の', '朝《あさ》', '電車《でんしゃ》', 'は', '何時《なんじ》', 'ですか'] },
  ] },
  { id: 6, focus: '動詞《どうし》ます形《けい》、を・に・で・と', questions: [
    { type: 'mc', q: '毎日《まいにち》コーヒー＿＿＿ 飲《の》みます。', choices: ['を', 'に', 'で', 'と'], answer: 0 , translate: 'Setiap hari saya minum kopi.' },
    { type: 'mc', q: '公園《こうえん》＿＿＿ 散歩《さんぽ》します。', choices: ['を', 'に', 'で', 'へ'], answer: 2 , translate: 'Saya jalan-jalan di taman.' },
    { type: 'mc', q: '先生《せんせい》＿＿＿ 日本語《にほんご》を 習《なら》います。', choices: ['に', 'を', 'で', 'と'], answer: 0 , translate: 'Saya belajar bahasa Jepang dari guru.' },
    { type: 'mc', q: '毎朝《まいあさ》パン＿＿＿ 卵《たまご》＿＿＿ 食《た》べます。', choices: ['と／を', 'を／と', 'に／で', 'の／を'], answer: 0, img: 'tabler-egg' , translate: 'Setiap pagi saya makan roti dan telur.' },
    { type: 'mc', q: 'デパート＿＿＿ パン＿＿＿ 買《か》いました。', choices: ['で／を', 'を／で', 'に／を', 'へ／を'], answer: 0 , translate: 'Saya membeli roti di department store.' },
    { type: 'mc', q: '3時《じ》＿＿＿ 教室《きょうしつ》＿＿＿ 日本語《にほんご》の 先生《せんせい》＿＿＿ 会《あ》います。', choices: ['に／で／に', 'で／に／を', 'を／に／で', 'に／を／で'], answer: 0 , translate: 'Jam 3 saya bertemu guru bahasa Jepang di kelas.' },
    { type: 'scramble', translate: 'Kemarin saya tidak belajar.', words: ['きのう', '、', '勉強《べんきょう》', 'しませんでした'] },
    { type: 'scramble', translate: 'Tidak, saya tidak merokok.', words: ['いいえ', '、', 'たばこ', 'を', '吸《す》いません'] },
    { type: 'scramble', translate: 'Ayo kita istirahat sebentar.', words: ['ちょっと', '、', '休《やす》みましょう'] },
    { type: 'mc', q: 'きのう 自転車《じてんしゃ》＿＿＿ スーパー＿＿＿ 行《い》きました。スーパー＿＿＿ 牛乳《ぎゅうにゅう》＿＿＿ 果物《くだもの》＿＿＿ 買《か》いました。', choices: ['で／へ／で／と／を', 'に／を／で／を／と', 'で／に／を／と／で', 'へ／で／に／を／と'], answer: 0 , translate: 'Kemarin saya naik sepeda ke supermarket. Di supermarket saya membeli susu dan buah.' },
    { type: 'scramble', translate: 'Setiap hari saya makan siang jam 12 di kantin.', words: ['毎日《まいにち》', '12時《じゅうにじ》', 'に', '食堂《しょくどう》', 'で', '昼《ひる》ごはん', 'を', '食《た》べます'] },
    { type: 'mc', q: 'A：いっしょに　テニスを　しませんか。\nB：＿＿＿。', choices: ['ええ、しましょう', 'いいえ、しません', 'はい、そうです', 'どういたしまして'], answer: 0, img: 'tabler-message-circle' , translate: 'Ayo main tenis bersama. ...Ya, ayo.' },
    { type: 'mc', q: 'A：ちょっと　休《やす》みませんか。\nB：＿＿＿。', choices: ['ええ、休《やす》みましょう', 'いいえ、休《やす》みでした', 'はい、そうですか', 'すみません'], answer: 0, img: 'tabler-message-circle' , translate: 'Mau istirahat sebentar? ...Ya, ayo istirahat.' },
    { type: 'mc', q: 'きょう 3時《じ》に 教室《きょうしつ》で 日本語《にほんご》の 先生《せんせい》＿＿＿ 会《あ》います。', choices: ['に', 'を', 'で', 'と'], answer: 0 , translate: 'Hari ini jam 3 saya bertemu guru bahasa Jepang di kelas.' },
    { type: 'mc', q: '毎朝《まいあさ》ロビー＿＿＿ 新聞《しんぶん》＿＿＿ 読《よ》みます。', choices: ['で／を', 'を／で', 'に／を', 'へ／を'], answer: 0 , translate: 'Setiap pagi saya membaca koran di lobi.' },
    { type: 'mc', q: '「テニス（を）します」の 例《れい》で、＿＿＿を しますか。', choices: ['サッカー', 'コーヒー', 'たばこ', '手紙《てがみ》'], answer: 0, img: 'tabler-ball-football' , translate: 'Pada contoh \'main tenis\', main apa? ...Sepak bola.' },
    { type: 'scramble', translate: 'Setiap hari saya minum kopi.', words: ['毎日《まいにち》', 'コーヒー', 'を', '飲《の》みます'] },
    { type: 'scramble', translate: 'Kemarin malam saya menulis surat.', words: ['きのう', 'の', '晩《ばん》', '手紙《てがみ》', 'を', '書《か》きました'] },
    { type: 'scramble', translate: 'Ayo kita berhenti sebentar.', words: ['ちょっと', '休《やす》みましょう'] },
    { type: 'scramble', translate: 'Saya bertemu dengan guru bahasa Jepang.', words: ['日本語《にほんご》', 'の', '先生《せんせい》', 'に', '会《あ》います'] },
  ] },
  { id: 7, focus: 'で（道具《どうぐ》）、に（相手《あいて》）、あげます・もらいます', questions: [
    { type: 'mc', q: '箸《はし》＿＿＿ ご飯《はん》を 食《た》べます。', choices: ['を', 'で', 'に', 'と'], answer: 1 , translate: 'Saya makan nasi dengan sumpit.' },
    { type: 'mc', q: '誕生日《たんじょうび》に 友達《ともだち》＿＿＿ プレゼントを もらいました。', choices: ['に', 'を', 'で', 'へ'], answer: 0, img: 'tabler-gift' , translate: 'Saat ulang tahun saya menerima hadiah dari teman.' },
    { type: 'mc', q: '日本語《にほんご》＿＿＿ 「ありがとう」＿＿＿ 何《なん》ですか。', choices: ['で／は', 'は／で', 'を／に', 'に／を'], answer: 0 , translate: '\'Terima kasih\' dalam bahasa Jepang apa?' },
    { type: 'mc', q: 'わたしは 田中《たなか》先生《せんせい》＿＿＿ 日本語《にほんご》＿＿＿ 習《なら》いました。', choices: ['に／を', 'を／に', 'で／を', 'に／で'], answer: 0 , translate: 'Saya belajar bahasa Jepang dari Pak Tanaka.' },
    { type: 'mc', q: '山田《やまだ》さんは タワポンさんに 日本語《にほんご》を 教《おし》えました。タワポンさんは 山田《やまだ》さん＿＿＿ 日本語《にほんご》を 教《おし》えて＿＿＿。', choices: ['に／もらいました', 'に／あげました', 'を／もらいました', 'で／くれました'], answer: 0 , translate: 'Yamada mengajari Thaworn bahasa Jepang. Thaworn diajari bahasa Jepang oleh Yamada.' },
    { type: 'scramble', translate: 'Saya memberikan bunga kepada Sari.', words: ['私《わたし》', 'は', 'サリさん', 'に', '花《はな》', 'を', 'あげました'] },
    { type: 'scramble', translate: 'Sari menerima bunga dari saya.', words: ['サリさん', 'は', '私《わたし》', 'に', '花《はな》', 'を', 'もらいました'] },
    { type: 'scramble', translate: 'Apakah Anda sudah mengirim laporan?', words: ['もう', 'レポート', 'を', '送《おく》りましたか'] },
    { type: 'mc', q: '何《なに》で この 魚《さかな》を 切《き》りますか。…ナイフ＿＿＿ 切《き》ります。', choices: ['で', 'を', 'に', 'と'], answer: 0, img: 'tabler-fish' , translate: 'Dengan apa memotong ikan ini? ...Dipotong dengan pisau.' },
    { type: 'scramble', translate: 'Saya mengirim foto keluarga ke Jepang.', words: ['家族《かぞく》', 'の', '写真《しゃしん》', 'を', '日本《にほん》', 'へ', '送《おく》りました'] },
    { type: 'mc', q: 'A：誕生日《たんじょうび》に　何《なに》を　もらいましたか。\nB：＿＿＿。', choices: ['友達《ともだち》に　時計《とけい》を　もらいました', '友達《ともだち》に　時計《とけい》を　あげました', '花《はな》が　好《す》きです', 'いいえ、けっこうです'], answer: 0, img: 'tabler-message-circle' , translate: 'Apa yang diterima saat ulang tahun? ...Saya menerima jam tangan dari teman.' },
    { type: 'mc', q: 'A：これ、お土産《みやげ》です。どうぞ。\nB：＿＿＿。', choices: ['ありがとうございます', 'いただきます', 'ごちそうさまでした', 'かしこまりました'], answer: 0, img: 'tabler-message-circle' , translate: 'Ini oleh-oleh, silakan. ...Terima kasih banyak.' },
    { type: 'mc', q: '田中《たなか》先生《せんせい》＿＿＿ 日本語《にほんご》＿＿＿ 習《なら》いました。', choices: ['に／を', 'を／に', 'で／を', 'に／で'], answer: 0 , translate: 'Saya belajar bahasa Jepang dari Pak Tanaka.' },
    { type: 'mc', q: 'もう 荷物《にもつ》を＿＿＿か。…はい、もう 送《おく》りました。', choices: ['送《おく》りました', '送《おく》ります', '送《おく》る', '送《おく》って'], answer: 0 , translate: 'Apakah barangnya sudah dikirim? ...Ya, sudah dikirim.' },
    { type: 'mc', q: 'この魚《さかな》は 何《なに》で 切《き》りますか。…包丁《ほうちょう》＿＿＿ 切《き》ります。', choices: ['で', 'を', 'に', 'と'], answer: 0, img: 'tabler-fish' , translate: 'Ikan ini dipotong dengan apa? ...Dipotong dengan pisau dapur.' },
    { type: 'scramble', translate: 'Saya menerima jam tangan dari ayah.', words: ['父《ちち》', 'に', '時計《とけい》', 'を', 'もらいました'] },
    { type: 'scramble', translate: 'Apa bahasa Inggrisnya "terima kasih"?', words: ['「ありがとう」', 'は', '英語《えいご》', 'で', '何《なん》', 'ですか'] },
    { type: 'scramble', translate: '"Good morning" bahasa Jepangnya apa?', words: ['"Good morning"', 'は', '日本語《にほんご》', 'で', '何《なん》', 'ですか'] },
    { type: 'scramble', translate: 'Saya belajar bahasa Jepang di universitas di Tiongkok.', words: ['中国《ちゅうごく》', 'の', '大学《だいがく》', 'で', '日本語《にほんご》', 'を', '習《なら》いました'] },
    { type: 'scramble', translate: 'Di mana Anda meminjam buku?', words: ['どこ', 'で', '本《ほん》', 'を', '借《か》りますか'] },
  ] },
  { id: 8, focus: 'い形容詞《けいようし》・な形容詞《けいようし》', questions: [
    { type: 'mc', q: '「新《あたら》しい」の 反対《はんたい》は？', choices: ['古《ふる》い', '安《やす》い', '大《おお》きい', '静《しず》か'], answer: 0 , translate: 'Lawan kata \'baru\' apa? ...Lama.' },
    { type: 'mc', q: '「安《やす》い」の 反対《はんたい》は？', choices: ['高《たか》い', '低《ひく》い', '古《ふる》い', '暗《くら》い'], answer: 0 , translate: 'Lawan kata \'murah\' apa? ...Mahal.' },
    { type: 'mc', q: '「静《しず》か」の 反対《はんたい》は？', choices: ['賑《にぎ》やか', '有名《ゆうめい》', '親切《しんせつ》', '暇《ひま》'], answer: 0 , translate: 'Lawan kata \'sepi/tenang\' apa? ...Ramai.' },
    { type: 'mc', q: '日本語《にほんご》は 難《むずか》しいですか。…いいえ、あまり＿＿＿。', choices: ['難《むずか》しくないです', '難《むずか》しいです', '難《むずか》しかったです', '難《むずか》しくて'], answer: 0 , translate: 'Apakah bahasa Jepang sulit? ...Tidak, tidak terlalu sulit.' },
    { type: 'mc', q: 'カリナさんは（きれい・親切《しんせつ》）です。＿＿＿。', choices: ['きれいです。そして、親切《しんせつ》です', 'きれいで、親切《しんせつ》くて', 'きれいくて、親切《しんせつ》です', 'きれいだ、親切《しんせつ》だ'], answer: 0 , translate: 'Karina orangnya cantik dan ramah. Cantik, dan juga ramah.' },
    { type: 'mc', q: 'ワットさんは＿＿＿人《ひと》ですか。…すてきな 人《ひと》です。', choices: ['どんな', 'なんの', 'どこの', 'いつの'], answer: 0 , translate: 'Watt orangnya bagaimana? ...Orang yang menyenangkan.' },
    { type: 'scramble', translate: 'Beliau adalah guru yang ramah.', words: ['ワットさん', 'は', '親切《しんせつ》', 'な', '先生《せんせい》', 'です'] },
    { type: 'scramble', translate: 'Saya membeli tas yang baru.', words: ['新《あたら》しい', 'かばん', 'を', '買《か》いました'] },
    { type: 'scramble', translate: 'Restoran ini kecil tapi terkenal.', words: ['この', 'レストラン', 'は', '小《ちい》さい', 'です', 'が', '、', '有名《ゆうめい》', 'です'] },
    { type: 'mc', q: '会社《かいしゃ》の 寮《りょう》は 古《ふる》いです＿＿＿、きれいです。', choices: ['が', 'に', 'を', 'の'], answer: 0 , translate: 'Asrama perusahaan itu tua, tapi bersih.' },
    { type: 'scramble', translate: 'Apakah kehidupan di Jepang menyenangkan?', words: ['日本《にほん》', 'の', '生活《せいかつ》', 'は', '楽《たの》しいですか'] },
    { type: 'mc', q: 'A：日本《にほん》の　生活《せいかつ》は　どうですか。\nB：＿＿＿。', choices: ['大変《たいへん》ですが、楽《たの》しいです', 'どうぞよろしく', 'かしこまりました', 'いただきます'], answer: 0, img: 'tabler-message-circle' , translate: 'Bagaimana kehidupan di Jepang? ...Berat, tapi menyenangkan.' },
    { type: 'mc', q: 'A：日本語《にほんご》が　お上手《じょうず》ですね。\nB：＿＿＿。', choices: ['いいえ、まだまだです', 'ありがとうございました', 'ごちそうさまでした', 'そうしましょう'], answer: 0, img: 'tabler-message-circle' , translate: 'Bahasa Jepang Anda bagus sekali. ...Tidak, masih belum.' },
    { type: 'mc', q: '奈良《なら》は＿＿＿ 所《ところ》ですか。…いい 所《ところ》ですよ。', choices: ['どんな', 'なん', 'どこ', 'だれ'], answer: 0 , translate: 'Nara itu tempat yang bagaimana? ...Tempat yang bagus.' },
    { type: 'mc', q: 'シュミットさんは＿＿＿ 人《ひと》ですか。…すてきな 人《ひと》です。', choices: ['どんな', 'なんの', 'どこの', 'いつの'], answer: 0 , translate: 'Schmidt orangnya bagaimana? ...Orang yang menyenangkan.' },
    { type: 'mc', q: '寮《りょう》の 部屋《へや》は＿＿＿ですか。…小《ちい》さいですが、きれいです。', choices: ['どう', 'どんな', 'なん', 'どこ'], answer: 0 , translate: 'Kamar asrama bagaimana? ...Kecil, tapi bersih.' },
    { type: 'scramble', translate: 'Kamar tidur saya sempit.', words: ['わたし', 'の', '部屋《へや》', 'は', '狭《せま》い', 'です'] },
    { type: 'scramble', translate: 'Bagaimana makanan Jepang?', words: ['日本《にほん》', 'の', '食《た》べ物《もの》', 'は', 'どう', 'ですか'] },
    { type: 'scramble', translate: 'Apakah sekarang dingin?', words: ['いま', '寒《さむ》い', 'ですか'] },
    { type: 'scramble', translate: 'Terima kasih atas kebaikan Anda.', words: ['ご', '親切《しんせつ》', 'に', 'ありがとうございます'] },
  ] },
  { id: 9, focus: '好《す》き・嫌《きら》い（が）、から（理由《りゆう》）', questions: [
    { type: 'mc', q: '私《わたし》は 魚《さかな》＿＿＿ 好《す》きです。', choices: ['が', 'を', 'に', 'は'], answer: 0, img: 'tabler-fish' , translate: 'Saya suka ikan.' },
    { type: 'mc', q: '暑《あつ》いです＿＿＿、窓《まど》を 開《あ》けます。', choices: ['から', 'が', 'ので', 'し'], answer: 0 , translate: 'Karena panas, saya membuka jendela.' },
    { type: 'mc', q: 'タイ語《ご》＿＿＿ わかりますか。…いいえ、全然《ぜんぜん》 分《わ》かりません。', choices: ['が', 'を', 'に', 'へ'], answer: 0 , translate: 'Apakah mengerti bahasa Thai? ...Tidak, sama sekali tidak mengerti.' },
    { type: 'mc', q: '細《こま》かい お金《かね》＿＿＿ ありませんでした＿＿＿、佐藤《さとう》さん＿＿＿ 借《か》りました。', choices: ['が／から／に', 'を／し／で', 'は／と／を', 'が／し／を'], answer: 0 , translate: 'Karena tidak punya uang receh, saya pinjam dari Sato.' },
    { type: 'mc', q: 'あした 花見《はなみ》を しませんか。…すみません。友達《ともだち》＿＿＿ 約束《やくそく》＿＿＿ ありますから。', choices: ['と／が', 'に／を', 'の／に', 'と／を'], answer: 0 , translate: 'Besok mau lihat bunga sakura bersama? ...Maaf, saya sudah janji dengan teman.' },
    { type: 'scramble', translate: 'Karena besok ada ujian, saya belajar.', words: ['あした', '試験《しけん》', 'が', 'あります', 'から', '、', '勉強《べんきょう》します'] },
    { type: 'scramble', translate: 'Saya tidak begitu suka sayur.', words: ['野菜《やさい》', 'が', 'あまり', '好《す》き', 'じゃありません'] },
    { type: 'scramble', translate: 'Kenapa Anda tidak makan sarapan?', words: ['どうして', '朝《あさ》ごはん', 'を', '食《た》べませんでしたか'] },
    { type: 'mc', q: 'どんな スポーツ＿＿＿ 好《す》きですか。…野球《やきゅう》＿＿＿ 好《す》きです。', choices: ['が／が', 'を／を', 'に／に', 'は／を'], answer: 0, img: 'tabler-ball-baseball' , translate: 'Suka olahraga apa? ...Saya suka bisbol.' },
    { type: 'scramble', translate: 'Saya tidak begitu tidur semalam.', words: ['きのう', 'の', '晩《ばん》', 'あまり', '寝《ね》ませんでした'] },
    { type: 'mc', q: '山田《やまだ》：あした　いっしょに　花見《はなみ》を　しませんか。\n木村《きむら》：すみません。あしたは　＿＿＿。友達《ともだち》と　約束《やくそく》が　ありますから。', choices: ['ちょっと……', 'いいですね', 'そうですか', 'どういたしまして'], answer: 0, img: 'tabler-message-circle' , translate: 'Besok mau lihat bunga sakura bersama? ...Maaf, besok agak (repot).' },
    { type: 'mc', q: 'A：どうして　きのう　学校《がっこう》を　休《やす》みましたか。\nB：＿＿＿。', choices: ['頭《あたま》が　痛《いた》かったですから', '10時《じ》に　休《やす》みました', 'どこも　休《やす》みです', 'かぜです'], answer: 0, img: 'tabler-message-circle' , translate: 'Kenapa kemarin tidak masuk sekolah? ...Karena kepala saya sakit.' },
    { type: 'mc', q: '暑《あつ》いですから、冷《つめ》たい 飲《の》み物《もの》を＿＿＿。', choices: ['飲《の》みます', '飲《の》みたくないです', '飲《の》みません', '飲《の》みました'], answer: 0 , translate: 'Karena panas, saya minum minuman dingin.' },
    { type: 'mc', q: '野菜《やさい》が 好《す》きじゃありませんから、＿＿＿。', choices: ['あまり食《た》べません', 'たくさん食《た》べます', '大好《だいす》きです', '毎日《まいにち》食《た》べます'], answer: 0 , translate: 'Karena tidak suka sayur, saya jarang makan sayur.' },
    { type: 'mc', q: 'かたかなが＿＿＿ 分《わ》かります。（たくさん・全然《ぜんぜん》・だいたい）', choices: ['だいたい', '全然《ぜんぜん》', 'あまり', '少《すこ》し'], answer: 0 , translate: 'Saya mengerti katakana secara garis besar. (banyak/sama sekali/garis besar)' },
    { type: 'scramble', translate: 'Karena tidak ada uang kecil, saya pinjam dari Sato.', words: ['細《こま》かい', 'お金《かね》', 'が', 'ありませんでしたから', '、', '佐藤《さとう》さん', 'に', '借《か》りました'] },
    { type: 'scramble', translate: 'Mengapa Anda tidak melakukan dansa?', words: ['どうして', 'ダンス', 'を', 'しませんか'] },
    { type: 'scramble', translate: 'Karena bahasa Jepang saya belum lancar.', words: ['日本語《にほんご》', 'が', 'まだ', '上手《じょうず》', 'じゃありませんから'] },
    { type: 'scramble', translate: 'Saya bekerja karena bekerja di perusahaan Jepang.', words: ['日本《にほん》', 'の', '会社《かいしゃ》', 'で', '働《はたら》きますから'] },
    { type: 'scramble', translate: 'Apakah kamu suka olahraga apa saja?', words: ['どんな', 'スポーツ', 'が', '好《す》きですか'] },
  ] },
  { id: 10, focus: 'あります・います、上《うえ》・下《した》・中《なか》', questions: [
    { type: 'mc', q: '机《つくえ》の 上《うえ》に 本《ほん》が ＿＿＿。', choices: ['あります', 'います', 'です', 'ました'], answer: 0, img: 'tabler-book' , translate: 'Ada buku di atas meja.' },
    { type: 'mc', q: '庭《にわ》に 猫《ねこ》が ＿＿＿。', choices: ['あります', 'います', 'でした', 'ません'], answer: 1, img: 'tabler-cat' , translate: 'Ada kucing di halaman.' },
    { type: 'mc', q: '猫《ねこ》は 箱《はこ》の＿＿＿に います。', choices: ['中《なか》', '上《うえ》', '下《した》', '前《まえ》'], answer: 0 , translate: 'Kucing ada di dalam kotak.' },
    { type: 'mc', q: '本屋《ほんや》は 郵便局《ゆうびんきょく》と 喫茶店《きっさてん》の＿＿＿に あります。', choices: ['間《あいだ》', '中《なか》', '上《うえ》', '外《そと》'], answer: 0 , translate: 'Toko buku ada di antara kantor pos dan kedai kopi.' },
    { type: 'mc', q: '車《くるま》の 中《なか》に だれ＿＿＿ いますか。…だれ＿＿＿ いません。', choices: ['が／も', 'は／が', 'を／は', 'に／が'], answer: 0 , translate: 'Apakah ada orang di dalam mobil? ...Tidak ada siapa-siapa.' },
    { type: 'mc', q: '牛乳《ぎゅうにゅう》は＿＿＿に ありますか。…冷蔵庫《れいぞうこ》に あります。', choices: ['どこ', 'だれ', 'なに', 'いつ'], answer: 0 , translate: 'Susu ada di mana? ...Ada di kulkas.' },
    { type: 'scramble', translate: 'Kamar mandi tidak ada di lantai satu.', words: ['トイレ', 'は', '1階《いっかい》', 'に', 'ありません'] },
    { type: 'scramble', translate: 'Tidak ada siapa-siapa di taman.', words: ['庭《にわ》', 'に', 'だれも', 'いません'] },
    { type: 'scramble', translate: 'Anak kucing ada di bawah kursi.', words: ['猫《ねこ》', 'は', '椅子《いす》', 'の', '下《した》', 'に', 'います'] },
    { type: 'mc', q: 'このビル＿＿＿ 喫茶店《きっさてん》＿＿＿ ありますか。…はい、地下《ちか》1階《いっかい》＿＿＿ あります。', choices: ['に／が／に', 'は／を／で', 'で／が／を', 'に／を／で'], answer: 0 , translate: 'Apakah di gedung ini ada kedai kopi? ...Ya, ada di lantai bawah tanah 1.' },
    { type: 'scramble', translate: 'Saya bertemu dengan Yi di depan stasiun.', words: ['駅《えき》', 'の', '前《まえ》', 'で', 'イーさん', 'に', '会《あ》いました'] },
    { type: 'mc', q: 'A：すみません、トイレは　どこですか。\nB：＿＿＿。', choices: ['あそこです', 'います', 'それです', 'どなたです'], answer: 0, img: 'tabler-message-circle' , translate: 'Permisi, toilet di mana? ...Di sana.' },
    { type: 'mc', q: 'A：事務所《じむしょ》に　だれか　いますか。\nB：いいえ、＿＿＿。', choices: ['だれも　いません', 'だれか　います', '何《なに》も　ありません', 'いいえ、あります'], answer: 0, img: 'tabler-message-circle' , translate: 'Apakah ada orang di kantor? ...Tidak, tidak ada siapa-siapa.' },
    { type: 'mc', q: '紅茶《こうちゃ》売《う》り場《ば》は＿＿＿に あります。', choices: ['地下《ちか》', '上《うえ》', '外《そと》', 'そば'], answer: 0 , translate: 'Bagian teh ada di lantai bawah tanah.' },
    { type: 'mc', q: '箱《はこ》の 中《なか》に 何《なに》も＿＿＿。', choices: ['ありません', 'います', 'いません', 'です'], answer: 0 , translate: 'Di dalam kotak tidak ada apa-apa.' },
    { type: 'mc', q: '木《き》の 下《した》に 犬《いぬ》が＿＿＿。', choices: ['います', 'あります', 'でした', 'ません'], answer: 0, img: 'tabler-dog' , translate: 'Ada anjing di bawah pohon.' },
    { type: 'scramble', translate: 'Susu ada di kulkas.', words: ['牛乳《ぎゅうにゅう》', 'は', '冷蔵庫《れいぞうこ》', 'に', 'あります'] },
    { type: 'scramble', translate: 'Sato duduk di sebelah Miller.', words: ['佐藤《さとう》さん', 'は', 'ミラーさん', 'の', '隣《となり》', 'に', 'います'] },
    { type: 'scramble', translate: 'Apakah ada bangunan tinggi di sana?', words: ['あそこ', 'に', '高《たか》い', 'ビル', 'が', 'ありますね'] },
    { type: 'scramble', translate: 'Di dalam mobil tidak ada siapa-siapa.', words: ['車《くるま》', 'の', '中《なか》', 'に', 'だれも', 'いません'] },
  ] },
  { id: 11, focus: '助数詞《じょすうし》（〜つ・〜人《にん》・〜枚《まい》）', questions: [
    { type: 'mc', q: 'りんごを＿＿＿ ください。（4個《こ》）', choices: ['よっつ', 'よんつ', 'よにん', 'よんまい'], answer: 0 , translate: 'Tolong berikan 4 buah apel.' },
    { type: 'mc', q: '切手《きって》を＿＿＿ ください。（3枚《まい》）', choices: ['さんまい', 'さんにん', 'みっつ', 'さんこ'], answer: 0, img: 'tabler-stamp' , translate: 'Tolong berikan 3 lembar perangko.' },
    { type: 'mc', q: '家族《かぞく》は＿＿＿です。（5人《にん》）', choices: ['ごにん', 'いつつ', 'ごまい', 'ごだい'], answer: 0 , translate: 'Keluarga saya berjumlah 5 orang.' },
    { type: 'mc', q: '店《みせ》の 前《まえ》に 自転車《じてんしゃ》が 20台《だい》＿＿＿ あります。', choices: ['ぐらい', 'ずつ', 'まで', 'しか'], answer: 0, img: 'tabler-bike' , translate: 'Ada sekitar 20 sepeda di depan toko.' },
    { type: 'mc', q: '大阪《おおさか》＿＿＿ 東京《とうきょう》＿＿＿ 新幹線《しんかんせん》＿＿＿ 2時間半《にじかんはん》ぐらい＿＿＿ かかります。', choices: ['から／まで／で／×', 'まで／から／に／を', 'に／で／を／×', 'から／まで／に／で'], answer: 0 , translate: 'Dari Osaka ke Tokyo naik shinkansen memakan waktu sekitar 2,5 jam.' },
    { type: 'mc', q: '1週間《しゅうかん》＿＿＿ 2回《かい》 テニスを します。', choices: ['に', 'で', 'を', 'へ'], answer: 0 , translate: 'Saya main tenis 2 kali seminggu.' },
    { type: 'scramble', translate: 'Ada dua orang di depan pintu.', words: ['ドア', 'の', '前《まえ》', 'に', '二人《ふたり》', 'います'] },
    { type: 'scramble', translate: 'Tolong berikan 5 lembar perangko 80 yen.', words: ['80円《えん》', 'の', '切手《きって》', 'を', '5枚《まい》', 'ください'] },
    { type: 'scramble', translate: 'Saya belajar bahasa Jepang di negara saya selama 6 bulan.', words: ['国《くに》', 'で', '6か月《ろっかげつ》', 'ぐらい', '日本語《にほんご》', 'を', '勉強《べんきょう》しました'] },
    { type: 'mc', q: 'この 荷物《にもつ》、航空便《こうくうびん》＿＿＿ お願《ねが》いします。', choices: ['で', 'を', 'に', 'が'], answer: 0, img: 'tabler-plane' , translate: 'Tolong barang ini dikirim lewat pos udara.' },
    { type: 'scramble', translate: 'Kakak laki-laki saya suka sepak bola.', words: ['兄《あに》', 'は', 'サッカー', 'が', '好《す》きです'] },
    { type: 'mc', q: '店員《てんいん》：いらっしゃいませ。\n客《きゃく》：すみません、80円《えん》の　切手《きって》を　5枚《まい》　ください。\n店員《てんいん》：＿＿＿。', choices: ['はい、かしこまりました', 'いいですね', 'どういたしまして', 'また今度《こんど》'], answer: 0, img: 'tabler-message-circle' , translate: 'Permisi, tolong 5 lembar perangko 80 yen. ...Baik, dimengerti.' },
    { type: 'mc', q: 'A：ご家族《かぞく》は　何人《なんにん》ですか。\nB：＿＿＿。', choices: ['5人《にん》です', '5枚《まい》です', '5個《こ》です', '5台《だい》です'], answer: 0, img: 'tabler-message-circle' , translate: 'Keluarga Anda berapa orang? ...5 orang.' },
    { type: 'mc', q: '1週間《しゅうかん》に 1回《かい》 両親《りょうしん》に 電話《でんわ》を＿＿＿。', choices: ['かけます', 'します', 'あります', 'います'], answer: 0 , translate: 'Seminggu sekali saya menelepon orang tua.' },
    { type: 'mc', q: '先月《せんげつ》 1週間《しゅうかん》 会社《かいしゃ》を＿＿＿。', choices: ['休《やす》みました', '休《やす》みます', '休《やす》みません', '休《やす》みだ'], answer: 0 , translate: 'Bulan lalu saya libur kerja seminggu.' },
    { type: 'mc', q: '妹《いもうと》＿＿＿ 2人《ふたり》＿＿＿ います。', choices: ['が／×', 'は／を', 'に／で', 'を／に'], answer: 0 , translate: 'Saya punya 2 orang adik perempuan.' },
    { type: 'scramble', translate: 'Perjalanan dari Osaka ke Tokyo memakan waktu sekitar 2 jam setengah dengan shinkansen.', words: ['大阪《おおさか》', 'から', '東京《とうきょう》', 'まで', '新幹線《しんかんせん》', 'で', '2時間半《にじかんはん》', 'ぐらい', 'かかります'] },
    { type: 'scramble', translate: 'Saya membeli 4 buah apel.', words: ['りんご', 'を', '4《よっ》つ', '買《か》いました'] },
    { type: 'scramble', translate: 'Di depan toko ada sekitar 20 sepeda.', words: ['店《みせ》', 'の', '前《まえ》', 'に', '自転車《じてんしゃ》', 'が', '20台《にじゅうだい》', 'ぐらい', 'あります'] },
    { type: 'scramble', translate: 'Tolong kirim barang ini lewat pos udara.', words: ['この', '荷物《にもつ》', '、', '航空便《こうくうびん》', 'で', 'お願《ねが》いします'] },
  ] },
  { id: 12, focus: '比較《ひかく》（より、の中《なか》で一番《いちばん》）', questions: [
    { type: 'mc', q: '地下鉄《ちかてつ》は バス＿＿＿ 速《はや》いです。', choices: ['より', 'から', 'まで', 'ほど'], answer: 0, img: 'tabler-train' , translate: 'Kereta bawah tanah lebih cepat daripada bus.' },
    { type: 'mc', q: 'スポーツ＿＿＿ サッカーが 一番《いちばん》 おもしろいです。', choices: ['で', 'に', 'と', 'を'], answer: 0, img: 'tabler-ball-football' , translate: 'Di antara olahraga, sepak bola paling menarik.' },
    { type: 'mc', q: '暑《あつ》い 季節《きせつ》＿＿＿ 寒《さむ》い 季節《きせつ》＿＿＿ どちら＿＿＿ いいですか。', choices: ['と／と／が', 'や／の／を', 'に／を／が', 'と／の／に'], answer: 0 , translate: 'Antara musim panas dan musim dingin, mana yang lebih disukai?' },
    { type: 'mc', q: '桜《さくら》＿＿＿ 日本《にほん》の 花《はな》＿＿＿ いちばん 有名《ゆうめい》です。', choices: ['は／で', 'が／と', 'の／に', 'を／が'], answer: 0, img: 'tabler-flower' , translate: 'Di antara bunga Jepang, sakura yang paling terkenal.' },
    { type: 'mc', q: '東京《とうきょう》は 大阪《おおさか》＿＿＿ 人《ひと》が 多《おお》いです。', choices: ['より', 'ほど', 'ので', 'でも'], answer: 0 , translate: 'Di Tokyo orangnya lebih banyak daripada Osaka.' },
    { type: 'scramble', translate: '1 tahun ini, Agustus yang paling panas.', words: ['1年《いちねん》', 'で', '8月《はちがつ》', 'が', '一番《いちばん》', '暑《あつ》い', 'です'] },
    { type: 'scramble', translate: 'Tokyo lebih besar daripada Osaka.', words: ['東京《とうきょう》', 'は', '大阪《おおさか》', 'より', '大《おお》きい', 'です'] },
    { type: 'scramble', translate: 'Saya paling suka masakan Jepang.', words: ['日本《にほん》', '料理《りょうり》', 'が', 'いちばん', '好《す》きです'] },
    { type: 'mc', q: '松本《まつもと》さん＿＿＿ 山田《やまだ》さん＿＿＿ どちら＿＿＿ ダンス＿＿＿ 上手《じょうず》ですか。', choices: ['と／と／が／が', 'や／の／を／に', 'に／を／が／と', 'と／の／に／が'], answer: 0 , translate: 'Antara Matsumoto dan Yamada, siapa yang lebih pandai menari?' },
    { type: 'scramble', translate: 'Toko roti dekat stasiun lebih murah daripada department store.', words: ['駅《えき》', 'の', '前《まえ》', 'の', 'パン屋《や》', 'は', 'デパート', 'より', '安《やす》い', 'です'] },
    { type: 'mc', q: 'A：日本《にほん》料理《りょうり》で　何《なに》が　いちばん　好《す》きですか。\nB：＿＿＿。', choices: ['すしが　いちばん　好《す》きです', 'すしより　好《す》きです', 'すしが　好《す》きじゃありません', 'すしが　嫌《きら》いです'], answer: 0, img: 'tabler-message-circle' , translate: 'Di antara masakan Jepang, apa yang paling disukai? ...Saya paling suka sushi.' },
    { type: 'mc', q: 'A：東京《とうきょう》と　大阪《おおさか》と　どちらが　好《す》きですか。\nB：＿＿＿。', choices: ['東京《とうきょう》の　ほうが　好《す》きです', '東京《とうきょう》が　嫌《きら》いです', 'どちらも　嫌《きら》いです', '大阪《おおさか》は　東京《とうきょう》です'], answer: 0, img: 'tabler-message-circle' , translate: 'Antara Tokyo dan Osaka, lebih suka mana? ...Saya lebih suka Tokyo.' },
    { type: 'mc', q: '課長《かちょう》は＿＿＿と 言《い》いましたか。…あさって 名古屋《なごや》へ 出張《しゅっちょう》すると 言《い》いました。', choices: ['何《なん》', 'だれ', 'どこ', 'いつ'], answer: 0 , translate: 'Kepala bagian bilang apa? ...Katanya lusa akan dinas ke Nagoya.' },
    { type: 'mc', q: '会社《かいしゃ》の 仕事《しごと》は＿＿＿ですか。…おもしろいです。', choices: ['どう', 'どんな', 'なん', 'いつ'], answer: 0 , translate: 'Bagaimana pekerjaan di kantor? ...Menarik.' },
    { type: 'mc', q: 'タイは＿＿＿ 国《くに》ですか。…暑《あつ》い 国《くに》です。', choices: ['どんな', 'どう', 'なん', 'どこ'], answer: 0 , translate: 'Thailand negara yang bagaimana? ...Negara yang panas.' },
    { type: 'scramble', translate: 'Fuji University lebih tua daripada Sakura University.', words: ['富士《ふじ》大学《だいがく》', 'は', 'さくら大学《だいがく》', 'より', '古《ふる》い', 'です'] },
    { type: 'scramble', translate: 'Bunga sakura yang paling terkenal sebagai bunga Jepang.', words: ['桜《さくら》', 'が', '日本《にほん》', 'の', '花《はな》', 'で', 'いちばん', '有名《ゆうめい》', 'です'] },
    { type: 'scramble', translate: 'Musim panas dan musim dingin, mana yang Anda sukai?', words: ['夏《なつ》', 'と', '冬《ふゆ》', 'と', 'どちら', 'が', '好《す》きですか'] },
    { type: 'scramble', translate: 'Kamar dorm ini kecil tapi bersih.', words: ['この', '寮《りょう》', 'の', '部屋《へや》', 'は', '小《ちい》さい', 'です', 'が', '、', 'きれいです'] },
    { type: 'scramble', translate: 'Di antara olahraga, sepak bola yang paling menarik.', words: ['スポーツ', 'で', 'サッカー', 'が', 'いちばん', 'おもしろい', 'です'] },
  ] },
  { id: 13, focus: '欲《ほ》しい、〜たい、〜に行《い》きます', questions: [
    { type: 'mc', q: '私《わたし》は 新《あたら》しい パソコン＿＿＿ 欲《ほ》しいです。', choices: ['が', 'を', 'に', 'は'], answer: 0, img: 'tabler-device-laptop' , translate: 'Saya ingin komputer baru.' },
    { type: 'mc', q: 'コーヒーを ＿＿＿です。（飲《の》みます→〜たい）', choices: ['飲《の》みたい', '飲《の》みます', '飲《の》みません', '飲《の》みたく'], answer: 0 , translate: 'Saya ingin minum kopi.' },
    { type: 'mc', q: 'のど＿＿＿ かわきましたから、何《なに》＿＿＿ 飲《の》みたいです。', choices: ['が／か', 'を／が', 'に／を', 'は／か'], answer: 0 , translate: 'Karena tenggorokan haus, saya ingin minum sesuatu.' },
    { type: 'mc', q: '疲《つか》れましたから、何《なに》も＿＿＿。', choices: ['したくないです', 'したいです', 'します', 'しました'], answer: 0 , translate: 'Karena lelah, saya tidak ingin melakukan apa-apa.' },
    { type: 'mc', q: '今《いま》 何《なに》＿＿＿ 欲《ほ》しくないです。', choices: ['も', 'が', 'を', 'は'], answer: 0 , translate: 'Sekarang saya tidak ingin apa-apa.' },
    { type: 'scramble', translate: 'Saya pergi ke perpustakaan untuk meminjam buku.', words: ['図書館《としょかん》', 'へ', '本《ほん》', 'を', '借《か》りに', '行《い》きます'] },
    { type: 'scramble', translate: 'Saya ingin cepat bertemu keluarga.', words: ['早《はや》く', '家族《かぞく》', 'に', '会《あ》いたい', 'です'] },
    { type: 'scramble', translate: 'Minggu depan saya ingin pergi memancing.', words: ['来週《らいしゅう》', '釣《つ》り', 'に', '行《い》きたい', 'です'] },
    { type: 'mc', q: '夏休《なつやす》みに 北海道《ほっかいどう》＿＿＿ 旅行《りょこう》＿＿＿ 行《い》きます。', choices: ['へ／に', 'に／へ', 'で／を', 'を／に'], answer: 0, img: 'tabler-map' , translate: 'Saat liburan musim panas saya akan bepergian ke Hokkaido.' },
    { type: 'scramble', translate: 'Waktu dan uang, mana yang lebih Anda inginkan?', words: ['時間《じかん》', 'と', 'お金《かね》', 'と', 'どちら', 'が', '欲《ほ》しいですか'] },
    { type: 'mc', q: 'A：次《つぎ》の　日曜日《にちようび》は　何《なに》を　したいですか。\nB：＿＿＿。', choices: ['映画《えいが》を　見《み》たいです', '映画《えいが》を　見《み》ます', '映画《えいが》が　好《す》きです', '映画《えいが》を　見《み》に　行《い》きました'], answer: 0, img: 'tabler-message-circle' , translate: 'Minggu depan mau melakukan apa? ...Saya ingin menonton film.' },
    { type: 'mc', q: 'A：どこへ　行《い》きますか。\nB：＿＿＿。', choices: ['本《ほん》を　借《か》りに　図書館《としょかん》へ　行《い》きます', '本《ほん》を　借《か》りたいです', '本《ほん》が　欲《ほ》しいです', '本《ほん》を　借《か》りて　います'], answer: 0, img: 'tabler-message-circle' , translate: 'Mau pergi ke mana? ...Saya pergi ke perpustakaan untuk meminjam buku.' },
    { type: 'mc', q: '疲《つか》れましたから、どこも＿＿＿。', choices: ['行《い》きたくないです', '行《い》きたいです', '行《い》きます', '行《い》きました'], answer: 0 , translate: 'Karena lelah, saya tidak ingin pergi ke mana-mana.' },
    { type: 'mc', q: '彼女《かのじょ》の 誕生日《たんじょうび》に すてきな プレゼントを＿＿＿。', choices: ['あげたいです', '欲《ほ》しいです', 'もらいたいです', 'くれたいです'], answer: 0, img: 'tabler-gift' , translate: 'Saya ingin memberi hadiah bagus untuk ulang tahun pacar saya.' },
    { type: 'mc', q: '日曜日《にちようび》は 何《なに》を＿＿＿たいですか。…何《なに》も したくないです。', choices: ['し', 'する', 'した', 'して'], answer: 0 , translate: 'Hari Minggu ingin melakukan apa? ...Saya tidak ingin melakukan apa-apa.' },
    { type: 'scramble', translate: 'Saya mau minum sesuatu karena haus.', words: ['のど', 'が', 'かわきました', 'から', '、', '何《なに》か', '飲《の》みたいです'] },
    { type: 'scramble', translate: 'Saya sekarang tidak ingin apa-apa.', words: ['わたし', 'は', '今《いま》', '何《なに》も', '欲《ほ》しくないです'] },
    { type: 'scramble', translate: 'Sekarang saya ingin sepatu yang ringan.', words: ['軽《かる》い', '靴《くつ》', 'が', '欲《ほ》しいです'] },
    { type: 'scramble', translate: 'Akhir minggu saya pergi ke pantai untuk berenang.', words: ['週末《しゅうまつ》', '海《うみ》', 'へ', '泳《およ》ぎに', '行《い》きました'] },
    { type: 'scramble', translate: 'Saya pergi ke restoran masakan Thailand untuk makan.', words: ['タイ料理《りょうり》', 'の', '店《みせ》', 'へ', '食事《しょくじ》', 'に', '行《い》きました'] },
  ] },
  { id: 14, focus: 'て形《けい》、〜てください', questions: [
    { type: 'mc', q: '「書《か》きます」の て形《けい》は？', choices: ['書《か》いて', '書《か》んで', '書《か》きて', '書《か》って'], answer: 0 , translate: 'Bentuk te dari \'menulis\' apa?' },
    { type: 'mc', q: '「読《よ》みます」の て形《けい》は？', choices: ['読《よ》んで', '読《よ》いて', '読《よ》きて', '読《よ》って'], answer: 0 , translate: 'Bentuk te dari \'membaca\' apa?' },
    { type: 'mc', q: '「来《き》ます」の て形《けい》は？', choices: ['来《き》て', '来《き》んで', '来《き》いて', '来《き》って'], answer: 0 , translate: 'Bentuk te dari \'datang\' apa?' },
    { type: 'mc', q: '「急《いそ》ぎます」の て形《けい》は？', choices: ['急《いそ》いで', '急《いそ》いて', '急《いそ》んで', '急《いそ》って'], answer: 0 , translate: 'Bentuk te dari \'bergegas\' apa?' },
    { type: 'mc', q: '「持《も》ちます」の て形《けい》は？', choices: ['持《も》って', '持《も》んで', '持《も》いて', '持《も》きて'], answer: 0 , translate: 'Bentuk te dari \'memegang\' apa?' },
    { type: 'mc', q: 'すみませんが、もう 一《いち》度《ど》＿＿＿。（言《い》います→〜てください）', choices: ['言《い》ってください', '言《い》きてください', '言《い》んでください', '言《い》てください'], answer: 0 , translate: 'Maaf, tolong katakan sekali lagi.' },
    { type: 'scramble', translate: 'Tolong buka jendela.', words: ['窓《まど》', 'を', '開《あ》けて', 'ください'] },
    { type: 'scramble', translate: 'Tolong beritahu nomor telepon Matsumoto.', words: ['松本《まつもと》さん', 'の', '電話《でんわ》番号《ばんごう》', 'を', '教《おし》えて', 'ください'] },
    { type: 'scramble', translate: 'Boleh saya pinjam gunting ini?', words: ['この', 'はさみ', 'を', '貸《か》して', 'ください'] },
    { type: 'mc', q: '暑《あつ》いですから、エアコンを＿＿＿。（つけます）', choices: ['つけてください', 'つけます', 'つけました', 'つける'], answer: 0 , translate: 'Karena panas, tolong nyalakan AC.' },
    { type: 'scramble', translate: 'Tolong kirimkan peta lewat email.', words: ['地図《ちず》', 'を', 'メール', 'で', '送《おく》って', 'ください'] },
    { type: 'mc', q: 'A：すみませんが、ちょっと　手伝《てつだ》って　ください。\nB：＿＿＿。', choices: ['ええ、いいですよ', 'ええ、けっこうです', 'いいえ、まだです', 'どういたしまして'], answer: 0, img: 'tabler-message-circle' , translate: 'Maaf, tolong bantu sebentar. ...Ya, boleh.' },
    { type: 'mc', q: 'A：この　いす、座《すわ》っても　いいですか。\nB：＿＿＿。', choices: ['ええ、どうぞ', 'いいえ、まだです', 'かしこまりました', 'ごちそうさまでした'], answer: 0, img: 'tabler-message-circle' , translate: 'Boleh saya duduk di kursi ini? ...Ya, silakan.' },
    { type: 'mc', q: '「開《あ》けます」の て形《けい》は？', choices: ['開《あ》けて', '開《あ》いて', '開《あ》けって', '開《あ》んで'], answer: 0 , translate: 'Bentuk te dari \'membuka\' apa?' },
    { type: 'mc', q: '「降《ふ》ります」の て形《けい》は？', choices: ['降《ふ》って', '降《ふ》りて', '降《ふ》んで', '降《ふ》いて'], answer: 0 , translate: 'Bentuk te dari \'turun (hujan)\' apa?' },
    { type: 'mc', q: 'ちょっと そのはさみを＿＿＿。（貸《か》します→〜てください）', choices: ['貸《か》してください', '貸《か》りてください', '貸《か》んでください', '貸《か》ってください'], answer: 0 , translate: 'Tolong pinjamkan gunting itu sebentar.' },
    { type: 'scramble', translate: 'Tolong tunjukkan paspornya sekali lagi.', words: ['パスポート', 'を', 'もう一度《いちど》', '見《み》せて', 'ください'] },
    { type: 'scramble', translate: 'Sudah capek, jadi silakan istirahat di sini.', words: ['疲《つか》れました', '。', 'どうぞ', 'こちら', 'で', '休《やす》んで', 'ください'] },
    { type: 'scramble', translate: 'Boleh tolong bantu saya sedikit?', words: ['ちょっと', '手伝《てつだ》って', 'ください'] },
    { type: 'scramble', translate: 'Tolong tuliskan nama dan alamat dengan pulpen.', words: ['ボールペン', 'で', '住所《じゅうしょ》', 'と', '名前《なまえ》', 'を', '書《か》いて', 'ください'] },
  ] },
  { id: 15, focus: '〜ています（進行《しんこう》・状態《じょうたい》）', questions: [
    { type: 'mc', q: '今《いま》 新聞《しんぶん》を ＿＿＿。（読《よ》んでいます）', choices: ['読《よ》んでいます', '読《よ》みます', '読《よ》みました', '読《よ》む'], answer: 0, img: 'tabler-news' , translate: 'Sekarang saya sedang membaca koran.' },
    { type: 'mc', q: 'どこに 住《す》んで＿＿＿か。', choices: ['います', 'あります', 'です', 'ました'], answer: 0 , translate: 'Sedang tinggal di mana?' },
    { type: 'mc', q: '兄《あに》の 会社《かいしゃ》は 電気《でんき》製品《せいひん》を＿＿＿います。（作《つく》ります）', choices: ['作《つく》って', '作《つく》んで', '作《つく》きて', '作《つく》いて'], answer: 0 , translate: 'Perusahaan kakak laki-laki saya membuat produk elektronik.' },
    { type: 'mc', q: 'カリナさんは 日本《にほん》の 古《ふる》い 美術《びじゅつ》を＿＿＿います。（知《し》ります）', choices: ['知《し》って', '知《し》んで', '知《し》きて', '知《し》いて'], answer: 0 , translate: 'Karina mengetahui seni kuno Jepang.' },
    { type: 'mc', q: '教室《きょうしつ》で たばこを＿＿＿は いけません。', choices: ['吸《す》って', '吸《す》う', '吸《す》います', '吸《す》いた'], answer: 0 , translate: 'Tidak boleh merokok di kelas.' },
    { type: 'scramble', translate: 'Saya tinggal di Bandung.', words: ['バンドン', 'に', '住《す》んでいます'] },
    { type: 'scramble', translate: 'Apakah Anda punya kamus elektronik?', words: ['電子《でんし》辞書《じしょ》', 'を', '持《も》っていますか'] },
    { type: 'scramble', translate: 'Saya tidak tahu cara membuat tempura.', words: ['てんぷら', 'の', '作《つく》り方《かた》', 'を', '知《し》りません'] },
    { type: 'mc', q: '弟《おとうと》は 結婚《けっこん》して＿＿＿。独身《どくしん》です。', choices: ['いません', 'います', 'でした', 'ました'], answer: 0 , translate: 'Adik laki-laki saya belum menikah. Masih lajang.' },
    { type: 'scramble', translate: 'Apakah Anda mengetahui alamat Santos?', words: ['サントスさん', 'の', '住所《じゅうしょ》', 'を', '知《し》っていますか'] },
    { type: 'mc', q: 'A：カリナさんは　今《いま》　何《なに》を　して　いますか。\nB：＿＿＿。', choices: ['花《はな》を　見《み》て　います', '花《はな》を　見《み》ます', '花《はな》を　見《み》ました', '花《はな》が　好《す》きです'], answer: 0, img: 'tabler-message-circle' , translate: 'Karina sekarang sedang melakukan apa? ...Sedang melihat bunga.' },
    { type: 'mc', q: 'A：今《いま》　どこに　住《す》んで　いますか。\nB：＿＿＿。', choices: ['バンドンに　住《す》んで　います', 'バンドンへ　行《い》きます', 'バンドンです', 'バンドンが　好《す》きです'], answer: 0, img: 'tabler-message-circle' , translate: 'Sekarang tinggal di mana? ...Saya tinggal di Bandung.' },
    { type: 'mc', q: 'サントスさんの 趣味《しゅみ》は 日本《にほん》の 古《ふる》い 美術《びじゅつ》を＿＿＿ ことです。', choices: ['集《あつ》める', '集《あつ》めます', '集《あつ》めた', '集《あつ》めて'], answer: 0 , translate: 'Hobi Santos adalah mengumpulkan seni kuno Jepang.' },
    { type: 'mc', q: 'テレーザちゃんは 自転車《じてんしゃ》を＿＿＿います。', choices: ['持《も》って', '持《も》んで', '持《も》きて', '持《も》いて'], answer: 0, img: 'tabler-bike' , translate: 'Theresa punya sepeda.' },
    { type: 'mc', q: 'すみません、この カタログを＿＿＿もいいですか。', choices: ['もらって', 'もらう', 'もらった', 'もらい'], answer: 0 , translate: 'Permisi, boleh saya minta katalog ini?' },
    { type: 'scramble', translate: 'Adik laki-laki saya belum menikah, masih lajang.', words: ['弟《おとうと》', 'は', 'まだ', '結婚《けっこん》して', 'いません'] },
    { type: 'scramble', translate: 'Apa yang sedang Anda kerjakan sekarang?', words: ['いま', '何《なに》', 'を', 'して', 'いますか'] },
    { type: 'scramble', translate: 'Kakak laki-laki saya bekerja di perusahaan komputer.', words: ['兄《あに》', 'は', 'コンピューター', 'の', '会社《かいしゃ》', 'で', '働《はたら》いて', 'います'] },
    { type: 'scramble', translate: 'Tidak boleh minum alkohol di kelas.', words: ['教室《きょうしつ》', 'で', 'お酒《さけ》', 'を', '飲《の》んでは', 'いけません'] },
    { type: 'scramble', translate: 'Beliau tinggal sendirian.', words: ['一人《ひとり》', 'で', '住《す》んで', 'います'] },
  ] },
  { id: 16, focus: 'て形《けい》の 連続《れんぞく》（〜て、〜てから）', questions: [
    { type: 'mc', q: '大学《だいがく》を 出《で》て＿＿＿、会社《かいしゃ》に 入《はい》りました。', choices: ['から', 'まで', 'ので', 'し'], answer: 0 , translate: 'Setelah lulus kuliah, saya masuk kerja di perusahaan.' },
    { type: 'mc', q: '窓《まど》を＿＿＿、電気《でんき》を＿＿＿、事務所《じむしょ》を 出《で》ました。', choices: ['閉《し》めて／消《け》して', '閉《し》めます／消《け》します', '閉《し》めた／消《け》した', '閉《し》める／消《け》す'], answer: 0 , translate: 'Setelah menutup jendela dan mematikan lampu, saya keluar dari kantor.' },
    { type: 'mc', q: '仕事《しごと》が 終《お》わって＿＿＿、1時間《いちじかん》ぐらい プールで 泳《およ》ぎます。', choices: ['から', 'まで', 'ので', 'し'], answer: 0 , translate: 'Setelah pekerjaan selesai, saya berenang di kolam sekitar 1 jam.' },
    { type: 'mc', q: '「多《おお》い」の 反対《はんたい》は？', choices: ['少《すく》ない', '軽《かる》い', '暗《くら》い', '狭《せま》い'], answer: 0 , translate: 'Lawan kata \'banyak\' apa? ...Sedikit.' },
    { type: 'mc', q: '「入《はい》ります」の 反対《はんたい》は？', choices: ['出《で》ます', '座《すわ》ります', '立《た》ちます', '閉《し》めます'], answer: 0 , translate: 'Lawan kata \'masuk\' apa? ...Keluar.' },
    { type: 'scramble', translate: 'Setelah mengerjakan PR, saya menonton TV.', words: ['宿題《しゅくだい》', 'を', 'して', 'から', '、', 'テレビ', 'を', '見《み》ます'] },
    { type: 'scramble', translate: 'Tutup jendela, lalu keluar ruangan.', words: ['窓《まど》', 'を', '閉《し》めて', '、', '部屋《へや》', 'を', '出《で》ます'] },
    { type: 'scramble', translate: 'Kanji itu sulit cara membacanya.', words: ['漢字《かんじ》', 'は', '読《よ》み方《かた》', 'が', '難《むずか》しい', 'です'] },
    { type: 'mc', q: '地下鉄《ちかてつ》で 大阪《おおさか》まで＿＿＿、JRに 乗《の》り換《か》えてください。', choices: ['行《い》って', '行《い》きます', '行《い》った', '行《い》く'], answer: 0 , translate: 'Naik kereta bawah tanah sampai Osaka, lalu tolong pindah ke JR.' },
    { type: 'scramble', translate: 'Saya turun dari bus lalu berjalan ke rumah Tanaka.', words: ['バス', 'を', '降《お》りて', '、', '田中《たなか》さん', 'の', 'うち', 'まで', '歩《ある》いて', '行《い》きました'] },
    { type: 'mc', q: 'A：毎朝《まいあさ》　どんな　順番《じゅんばん》で　準備《じゅんび》しますか。\nB：＿＿＿。', choices: ['顔《かお》を　洗《あら》って　から、朝《あさ》ごはんを　食《た》べます', '顔《かお》を　洗《あら》います', '朝《あさ》ごはんを　食《た》べました', '顔《かお》を　洗《あら》いたいです'], answer: 0, img: 'tabler-message-circle' , translate: 'Setiap pagi bersiap dengan urutan bagaimana? ...Setelah mencuci muka, saya sarapan.' },
    { type: 'mc', q: 'A：会議《かいぎ》の　まえに　何《なに》を　しますか。\nB：＿＿＿。', choices: ['資料《しりょう》を　集《あつ》めて、コピーします', '資料《しりょう》が　あります', '資料《しりょう》を　集《あつ》めたいです', '資料《しりょう》は　集《あつ》めません'], answer: 0, img: 'tabler-message-circle' , translate: 'Sebelum rapat melakukan apa? ...Mengumpulkan berkas lalu memfotokopi.' },
    { type: 'mc', q: '「暗《くら》い」の 反対《はんたい》は？', choices: ['明《あか》るい', '狭《せま》い', '軽《かる》い', '短《みじか》い'], answer: 0 , translate: 'Lawan kata \'gelap\' apa? ...Terang.' },
    { type: 'mc', q: '「長《なが》い」の 反対《はんたい》は？', choices: ['短《みじか》い', '狭《せま》い', '遠《とお》い', '暗《くら》い'], answer: 0 , translate: 'Lawan kata \'panjang\' apa? ...Pendek.' },
    { type: 'mc', q: 'バスを＿＿＿から、田中《たなか》さんの うちまで 歩《ある》いて 行《い》きました。（降《お》ります）', choices: ['降《お》りて', '降《お》りた', '降《お》りる', '降《お》りない'], answer: 0 , translate: 'Setelah turun dari bus, saya berjalan kaki ke rumah Tanaka.' },
    { type: 'scramble', translate: 'Bandung terkenal karena banyak hijau dan tenang.', words: ['バンドン', 'は', '緑《みどり》', 'が', '多《おお》くて', '静《しず》か', 'です'] },
    { type: 'scramble', translate: 'Masakan Thailand punya rasa pedas dan asam yang enak.', words: ['タイ料理《りょうり》', 'は', '辛《から》くて', '酸《す》っぱくて', 'おいしい', 'です'] },
    { type: 'scramble', translate: 'Saya makan siang lalu tidur siang sebentar.', words: ['昼《ひる》ごはん', 'を', '食《た》べて', '、', '少《すこ》し', '昼寝《ひるね》', 'を', 'します'] },
    { type: 'scramble', translate: 'Setelah keluar dari kantor, saya langsung pulang.', words: ['事務所《じむしょ》', 'を', '出《で》て', 'から', '、', 'すぐ', '帰《かえ》ります'] },
    { type: 'scramble', translate: 'Guru Watt orang Inggris dan mengajar bahasa Inggris.', words: ['ワット先生《せんせい》', 'は', 'イギリス人《じん》', 'で', '、', '英語《えいご》', 'の', '先生《せんせい》', 'です'] },
  ] },
  { id: 17, focus: '〜なければなりません／〜なくてもいいです／〜ないでください', questions: [
    { type: 'mc', q: '明日《あした》は 早《はや》く 起《お》き＿＿＿。（義務《ぎむ》）', choices: ['なければなりません', 'なくてもいいです', 'ないでください', 'てください'], answer: 0 , translate: 'Besok saya harus bangun pagi.' },
    { type: 'mc', q: 'あした 心配《しんぱい》し＿＿＿。（しなくてもいい）', choices: ['なくてもいいです', 'なければなりません', 'ないでください', 'ています'], answer: 0 , translate: 'Besok tidak perlu khawatir.' },
    { type: 'mc', q: '暗証《あんしょう》番号《ばんごう》は 大切《たいせつ》ですから、＿＿＿。', choices: ['忘《わす》れないでください', '忘《わす》れてください', '忘《わす》れなければなりません', '忘《わす》れています'], answer: 0 , translate: 'Karena PIN itu penting, jangan sampai lupa.' },
    { type: 'mc', q: 'この 本《ほん》は 来週《らいしゅう》の 火曜日《かようび》＿＿＿に 返《かえ》さなければ なりません。', choices: ['まで', 'までに', 'から', 'に「×」'], answer: 1 , translate: 'Buku ini harus dikembalikan paling lambat hari Selasa depan.' },
    { type: 'mc', q: '危《あぶ》ないですから、うしろから＿＿＿。', choices: ['押《お》さないでください', '押《お》してください', '押《お》しました', '押《お》します'], answer: 0 , translate: 'Karena berbahaya, jangan dorong dari belakang.' },
    { type: 'scramble', translate: 'Jangan lupa kata sandi.', words: ['暗証番号《あんしょうばんごう》', 'を', '忘《わす》れないで', 'ください'] },
    { type: 'scramble', translate: 'Apakah hari Minggu juga harus bangun pagi?', words: ['日曜日《にちようび》', 'も', '早《はや》く', '起《お》きなければ', 'なりませんか'] },
    { type: 'scramble', translate: 'Nomor telepon tidak perlu ditulis.', words: ['電話《でんわ》番号《ばんごう》', 'は', '書《か》かなくても', 'いいです'] },
    { type: 'mc', q: '両親《りょうしん》が 来《き》ますから、空港《くうこう》へ 迎《むか》えに＿＿＿。', choices: ['行《い》かなければなりません', '行《い》かなくてもいいです', '行《い》かないでください', '行《い》っています'], answer: 0 , translate: 'Karena orang tua akan datang, saya harus menjemput ke bandara.' },
    { type: 'scramble', translate: 'Karena ujian mudah, tidak perlu khawatir.', words: ['試験《しけん》', 'は', '簡単《かんたん》', 'です', 'から', '、', '心配《しんぱい》', 'しなくても', 'いいです'] },
    { type: 'mc', q: 'A：日曜日《にちようび》も　早《はや》く　起《お》きなければ　なりませんか。\nB：いいえ、＿＿＿。', choices: ['起《お》きなくても　いいです', '起《お》きなければ　なりません', '起《お》きないで　ください', '起《お》きて　います'], answer: 0, img: 'tabler-message-circle' , translate: 'Hari Minggu juga harus bangun pagi? ...Tidak, tidak perlu bangun pagi.' },
    { type: 'mc', q: 'A：ここで　写真《しゃしん》を　撮《と》っても　いいですか。\nB：すみません、＿＿＿。', choices: ['撮《と》らないで　ください', '撮《と》っても　いいです', '撮《と》らなくても　いいです', '撮《と》って　います'], answer: 0, img: 'tabler-message-circle' , translate: 'Boleh memotret di sini? ...Maaf, jangan memotret.' },
    { type: 'mc', q: '両親《りょうしん》が 病気《びょうき》ですから、早《はや》く＿＿＿。', choices: ['帰《かえ》らなければなりません', '帰《かえ》らなくてもいいです', '帰《かえ》らないでください', '帰《かえ》っています'], answer: 0 , translate: 'Karena orang tua sakit, saya harus segera pulang.' },
    { type: 'mc', q: '子《こ》どもが 病気《びょうき》ですから、電話《でんわ》を＿＿＿。', choices: ['かけなければなりません', 'かけなくてもいいです', 'かけないでください', 'かけています'], answer: 0 , translate: 'Karena anak sedang sakit, saya harus menelepon.' },
    { type: 'scramble', translate: 'Karena resep ini mudah, tidak perlu khawatir.', words: ['この', 'レシピ', 'は', '簡単《かんたん》', 'です', 'から', '、', '心配《しんぱい》', 'しなくても', 'いいです'] },
    { type: 'scramble', translate: 'Ini penting, jangan sampai hilang.', words: ['これ', 'は', '大切《たいせつ》', 'です', 'から', '、', 'なくさないで', 'ください'] },
    { type: 'scramble', translate: 'Karena punya banyak pekerjaan, saya harus lembur hari ini.', words: ['仕事《しごと》', 'が', 'たくさん', 'あります', 'から', '、', 'きょう', '残業《ざんぎょう》', 'しなければなりません'] },
    { type: 'scramble', translate: 'Tolong lepas sepatu di sini.', words: ['ここ', 'で', '靴《くつ》', 'を', '脱《ぬ》いで', 'ください'] },
    { type: 'scramble', translate: 'Nomor telepon tidak perlu ditulis di sana.', words: ['電話《でんわ》番号《ばんごう》', 'は', 'そこ', 'に', '書《か》かなくても', 'いいです'] },
    { type: 'scramble', translate: 'Barang ini harus dikirim sebelum hari Jumat.', words: ['この', '荷物《にもつ》', 'は', '金曜日《きんようび》', 'までに', '送《おく》らなければ', 'なりません'] },
  ] },
  { id: 18, focus: '〜ことができます、辞書形《じしょけい》', questions: [
    { type: 'mc', q: '「食《た》べます」の 辞書《じしょ》形《けい》は？', choices: ['食《た》べる', '食《た》べて', '食《た》べます', '食《た》べた'], answer: 0 , translate: 'Bentuk kamus dari \'makan\' apa?' },
    { type: 'mc', q: '「話《はな》します」の 辞書《じしょ》形《けい》は？', choices: ['話《はな》す', '話《はな》して', '話《はな》した', '話《はな》します'], answer: 0 , translate: 'Bentuk kamus dari \'berbicara\' apa?' },
    { type: 'mc', q: '「来《き》ます」の 辞書《じしょ》形《けい》は？', choices: ['来《く》る', '来《き》て', '来《き》た', '来《き》ない'], answer: 0 , translate: 'Bentuk kamus dari \'datang\' apa?' },
    { type: 'mc', q: 'この 図書館《としょかん》の 本《ほん》は 2週間《にしゅうかん》＿＿＿ こと＿＿＿ できます。（借《か》ります）', choices: ['借《か》りる／が', '借《か》りて／を', '借《か》りた／に', '借《か》り／で'], answer: 0 , translate: 'Buku perpustakaan ini bisa dipinjam selama 2 minggu.' },
    { type: 'mc', q: '漢字《かんじ》が 分《わ》かりませんから、日本語《にほんご》の 新聞《しんぶん》を＿＿＿。', choices: ['読《よ》むことができません', '読《よ》みたいです', '読《よ》んでいます', '読《よ》んでください'], answer: 0 , translate: 'Karena tidak mengerti kanji, saya tidak bisa membaca koran Jepang.' },
    { type: 'scramble', translate: 'Saya bisa berenang 50 meter.', words: ['50メートル', '泳《およ》ぐ', 'こと', 'が', 'できます'] },
    { type: 'scramble', translate: 'Hobi saya adalah memotret bunga.', words: ['私《わたし》', 'の', '趣味《しゅみ》', 'は', '花《はな》', 'の', '写真《しゃしん》', 'を', '撮《と》る', 'こと', 'です'] },
    { type: 'scramble', translate: 'Sebelum ke Jepang, apakah Anda belajar bahasa Jepang?', words: ['日本《にほん》', 'へ', '来《く》る', 'まえに', '、', '日本語《にほんご》', 'を', '勉強《べんきょう》しましたか'] },
    { type: 'mc', q: 'ここは 朝《あさ》10時《じ》から 見学《けんがく》する ＿＿＿ が できます。', choices: ['こと', 'もの', 'とき', 'ところ'], answer: 0 , translate: 'Di sini bisa berkeliling melihat-lihat mulai jam 10 pagi.' },
    { type: 'scramble', translate: 'Sepuluh tahun lalu saya belajar bahasa Prancis, tapi sudah lupa.', words: ['10年《じゅうねん》', 'まえに', '、', 'フランス語《ご》', 'を', '習《なら》いましたが', '、', 'もう', '忘《わす》れました'] },
    { type: 'mc', q: 'A：日本語《にほんご》で　歌《うた》を　歌《うた》う　ことが　できますか。\nB：＿＿＿。', choices: ['はい、できます', 'はい、します', 'はい、あります', 'はい、います'], answer: 0, img: 'tabler-message-circle' , translate: 'Bisakah menyanyi lagu dalam bahasa Jepang? ...Ya, bisa.' },
    { type: 'mc', q: 'A：車《くるま》の　運転《うんてん》が　できますか。\nB：いいえ、＿＿＿。', choices: ['できません', 'しません', 'ありません', 'いません'], answer: 0, img: 'tabler-message-circle' , translate: 'Bisa menyetir mobil? ...Tidak, tidak bisa.' },
    { type: 'mc', q: '妹《いもうと》は さくら大学《だいがく》に＿＿＿ ことが できました。', choices: ['入《はい》る', '入《はい》った', '入《はい》って', '入《はい》り'], answer: 0 , translate: 'Adik perempuan saya berhasil masuk Universitas Sakura.' },
    { type: 'mc', q: '狭《せま》いですから、大《おお》きい 机《つくえ》を 置《お》く ことが＿＿＿。', choices: ['できません', 'しません', 'ありません', 'いません'], answer: 0 , translate: 'Karena sempit, tidak bisa meletakkan meja besar.' },
    { type: 'mc', q: 'わたしの 趣味《しゅみ》は 外国《がいこく》の 切手《きって》を＿＿＿ ことです。', choices: ['集《あつ》める', '集《あつ》めます', '集《あつ》めた', '集《あつ》めて'], answer: 0 , translate: 'Hobi saya adalah mengumpulkan perangko luar negeri.' },
    { type: 'scramble', translate: 'Karena mabuk, saya tidak bisa menyetir mobil.', words: ['お酒《さけ》', 'を', '飲《の》みました', 'から', '、', '車《くるま》', 'を', '運転《うんてん》する', 'こと', 'が', 'できません'] },
    { type: 'scramble', translate: 'Apakah bisa memotret dengan ponsel?', words: ['ケータイ', 'で', '写真《しゃしん》', 'を', '撮《と》る', 'こと', 'が', 'できますか'] },
    { type: 'scramble', translate: 'Sebelum tidur, saya selalu mandi.', words: ['寝《ね》る', 'まえに', '、', 'いつも', 'シャワー', 'を', '浴《あ》びます'] },
    { type: 'scramble', translate: 'Berapa meter kamu bisa berenang?', words: ['何《なん》メートル', 'ぐらい', '泳《およ》ぐ', 'こと', 'が', 'できますか'] },
    { type: 'scramble', translate: 'Apakah bisa memasak berbagai masakan negara?', words: ['いろいろな', '国《くに》', 'の', '料理《りょうり》', 'を', '作《つく》る', 'こと', 'が', 'できますか'] },
  ] },
  { id: 19, focus: '〜たことがあります（経験《けいけん》）、〜たり〜たり', questions: [
    { type: 'mc', q: '富士山《ふじさん》に 登《のぼ》った ＿＿＿ が あります。', choices: ['こと', 'もの', 'とき', 'ところ'], answer: 0, img: 'tabler-mountain' , translate: 'Saya pernah mendaki Gunung Fuji.' },
    { type: 'mc', q: '相撲《すもう》を＿＿＿ ことが ありますか。…いいえ、一度《いちど》も ありません。', choices: ['見《み》た', '見《み》る', '見《み》て', '見《み》ます'], answer: 0 , translate: 'Pernah menonton sumo? ...Tidak, belum pernah sekali pun.' },
    { type: 'mc', q: 'この 果物《くだもの》を 食《た》べた ことが ありますか。…いいえ、＿＿＿です。', choices: ['初《はじ》めて', 'もう一度《いちど》', 'なかなか', 'だんだん'], answer: 0 , translate: 'Pernah makan buah ini? ...Tidak, ini pertama kalinya.' },
    { type: 'mc', q: '休《やす》みの 日《ひ》は 本《ほん》を＿＿＿り、テレビを＿＿＿り します。', choices: ['読《よ》んだ／見《み》た', '読《よ》む／見《み》る', '読《よ》んで／見《み》て', '読《よ》み／見《み》'], answer: 0 , translate: 'Hari libur saya membaca buku dan menonton TV.' },
    { type: 'mc', q: '子《こ》どもは お酒《さけ》を＿＿＿り、たばこを＿＿＿り しては いけません。', choices: ['飲《の》んだ／吸《す》った', '飲《の》む／吸《す》う', '飲《の》んで／吸《す》って', '飲《の》み／吸《す》い'], answer: 0 , translate: 'Anak-anak tidak boleh minum sake dan merokok.' },
    { type: 'scramble', translate: 'Akhir pekan saya membaca buku dan menonton TV.', words: ['本《ほん》', 'を', '読《よ》んだり', '、', 'テレビ', 'を', '見《み》たり', 'します'] },
    { type: 'scramble', translate: 'Apakah Anda pernah menulis surat dengan bahasa Jepang?', words: ['日本語《にほんご》', 'で', '手紙《てがみ》', 'を', '書《か》いた', 'こと', 'が', 'ありますか'] },
    { type: 'scramble', translate: 'Bahasa Jepang saya menjadi semakin lancar.', words: ['日本語《にほんご》', 'が', 'だんだん', '上手《じょうず》', 'に', 'なりました'] },
    { type: 'mc', q: '何《なん》回《かい》ぐらい ディズニーランドへ 行《い》った ことが＿＿＿か。', choices: ['あります', 'います', 'でした', 'ました'], answer: 0, img: 'tabler-ferris-wheel' , translate: 'Sudah berapa kali pergi ke Disneyland?' },
    { type: 'scramble', translate: 'Saya sudah pernah naik ke Gunung Fuji sekali.', words: ['富士山《ふじさん》', 'に', '一度《いちど》', '登《のぼ》った', 'こと', 'が', 'あります'] },
    { type: 'mc', q: 'A：富士山《ふじさん》に　登《のぼ》った　ことが　ありますか。\nB：いいえ、＿＿＿。ぜひ　登《のぼ》りたいです。', choices: ['一度《いちど》も　ありません', 'もう　一度《いちど》です', '一度《いちど》　あります', 'ときどき　あります'], answer: 0, img: 'tabler-message-circle' , translate: 'Pernah mendaki Gunung Fuji? ...Belum pernah sekali pun. Saya ingin sekali mendaki.' },
    { type: 'mc', q: 'A：休《やす》みの　日《ひ》は　何《なに》を　しますか。\nB：＿＿＿。', choices: ['本《ほん》を　読《よ》んだり、テレビを　見《み》たり　します', '本《ほん》を　読《よ》んで、テレビを　見《み》ます', '本《ほん》が　好《す》きです', '本《ほん》を　読《よ》みたい　です'], answer: 0, img: 'tabler-message-circle' , translate: 'Hari libur biasanya melakukan apa? ...Membaca buku dan menonton TV.' },
    { type: 'mc', q: '国《くに》で 日本語《にほんご》の CDを＿＿＿り、漢字《かんじ》を＿＿＿り しなければ なりません。', choices: ['聞《き》いた／覚《おぼ》えた', '聞《き》く／覚《おぼ》える', '聞《き》いて／覚《おぼ》えて', '聞《き》き／覚《おぼ》え'], answer: 0 , translate: 'Di negara asal saya harus mendengarkan CD bahasa Jepang dan menghafal kanji.' },
    { type: 'mc', q: 'ドイツ語《ご》を＿＿＿ ことが ありますか。…ええ。でも、もう 忘《わす》れましたから、もう一度《いちど》＿＿＿たいです。', choices: ['習《なら》った／習《なら》い', '習《なら》う／習《なら》った', '習《なら》って／習《なら》う', '習《なら》い／習《なら》って'], answer: 0 , translate: 'Pernah belajar bahasa Jerman? ...Ya, tapi sudah lupa, ingin belajar lagi.' },
    { type: 'scramble', translate: 'Apakah Anda pernah menyanyikan lagu Jepang?', words: ['日本《にほん》', 'の', '歌《うた》', 'を', '歌《うた》った', 'こと', 'が', 'ありますか'] },
    { type: 'scramble', translate: 'Bahasa Jepang saya belum menjadi lancar.', words: ['日本語《にほんご》', 'が', 'まだ', '上手《じょうず》', 'に', 'なりません'] },
    { type: 'scramble', translate: 'Sudah bulan September, mulai jadi sejuk.', words: ['もう', '9月《くがつ》', 'です', 'ね', '。', 'これから', '涼《すず》しく', 'なりますよ'] },
    { type: 'scramble', translate: 'Anak itu sudah menjadi besar ya, umur berapa sekarang?', words: ['あの', '子《こ》', 'は', '大《おお》きく', 'なりました', 'ね', '。', '何歳《なんさい》', 'ですか'] },
    { type: 'scramble', translate: 'Apakah Anda pernah diet?', words: ['ダイエット', 'を', 'した', 'こと', 'が', 'ありますか'] },
    { type: 'scramble', translate: 'Sudah pernah mendaki gunung di Jepang?', words: ['日本《にほん》', 'で', '山《やま》', 'に', '登《のぼ》った', 'こと', 'が', 'ありますか'] },
  ] },
  { id: 20, focus: '普通形《ふつうけい》（会話《かいわ》で使《つか》う形《かたち》）', questions: [
    { type: 'mc', q: '「分《わ》かりますか」の 普通《ふつう》形《けい》は？', choices: ['分《わ》かる？', '分《わ》かります？', '分《わ》かった？', '分《わ》かって？'], answer: 0 , translate: 'Bentuk biasa dari \'mengerti?\' apa?' },
    { type: 'mc', q: '「行《い》きませんでした」の 普通《ふつう》形《けい》は？', choices: ['行《い》かなかった', '行《い》かない', '行《い》った', '行《い》かなくて'], answer: 0 , translate: 'Bentuk biasa dari \'tidak pergi\' apa?' },
    { type: 'mc', q: '「休《やす》みでした」の 普通《ふつう》形《けい》は？', choices: ['休《やす》みだった', '休《やす》みじゃない', '休《やす》み', '休《やす》みでは'], answer: 0 , translate: 'Bentuk biasa dari \'libur\' apa?' },
    { type: 'mc', q: '「欲《ほ》しくないです」の 普通《ふつう》形《けい》は？', choices: ['欲《ほ》しくない', '欲《ほ》しかった', '欲《ほ》しくて', '欲《ほ》しいだ'], answer: 0 , translate: 'Bentuk biasa dari \'tidak ingin\' apa?' },
    { type: 'mc', q: '英語《えいご》が 分《わ》かる？…うん、＿＿＿。', choices: ['できる', 'できます', 'できた', 'できて'], answer: 0 , translate: 'Mengerti bahasa Inggris? ...Ya, bisa.' },
    { type: 'scramble', translate: 'Apakah kamu tahu alamat Karina?', words: ['カリナさん', 'の', '住所《じゅうしょ》', 'を', '知《し》っている？'] },
    { type: 'scramble', translate: 'Cuaca kemarin bagus.', words: ['きのう', 'は', '天気《てんき》', 'が', 'よかった'] },
    { type: 'mc', q: '「便利《べんり》です」の 普通《ふつう》形《けい》は？', choices: ['便利《べんり》だ', '便利《べんり》い', '便利《べんり》じゃ', '便利《べんり》な'], answer: 0 , translate: 'Bentuk biasa dari \'praktis\' apa?' },
    { type: 'scramble', translate: 'Apakah kamu pergi berenang di laut Jepang?', words: ['日本《にほん》', 'の', '海《うみ》', 'で', '泳《およ》いだ', 'こと', 'が', 'ある？'] },
    { type: 'mc', q: 'A：あした　いっしょに　行《い》く？\nB：うん、＿＿＿。', choices: ['行《い》く', '行《い》きます', '行《い》った', '行《い》って'], answer: 0, img: 'tabler-message-circle' , translate: 'Besok pergi bersama? ...Ya, pergi.' },
    { type: 'mc', q: 'A：きのう、山田《やまだ》さんに　会《あ》った？\nB：ううん、＿＿＿。', choices: ['会《あ》わなかった', '会《あ》いません', '会《あ》います', '会《あ》って'], answer: 0, img: 'tabler-message-circle' , translate: 'Kemarin bertemu Yamada? ...Tidak, tidak bertemu.' },
    { type: 'mc', q: '「無理《むり》じゃありません」の 普通《ふつう》形《けい》は？', choices: ['無理《むり》じゃない', '無理《むり》だ', '無理《むり》くない', '無理《むり》じゃなかった'], answer: 0 , translate: 'Bentuk biasa dari \'tidak memaksakan diri\' apa?' },
    { type: 'mc', q: '「修理《しゅうり》します」の 普通《ふつう》形《けい》は？', choices: ['修理《しゅうり》する', '修理《しゅうり》した', '修理《しゅうり》して', '修理《しゅうり》しない'], answer: 0 , translate: 'Bentuk biasa dari \'memperbaiki\' apa?' },
    { type: 'mc', q: 'カリナさんの 住所《じゅうしょ》を 知《し》っている？…ううん、＿＿＿。', choices: ['知《し》らない', '知《し》っています', '知《し》る', '知《し》った'], answer: 0 , translate: 'Tahu alamat Karina? ...Tidak, tidak tahu.' },
    { type: 'mc', q: 'ビザが 要《い》る？…ううん、＿＿＿。', choices: ['要《い》らない', '要《い》ります', '要《い》る', '要《い》った'], answer: 0 , translate: 'Perlu visa? ...Tidak, tidak perlu.' },
    { type: 'scramble', translate: 'Apakah kamu bisa berbahasa Inggris?', words: ['英語《えいご》', 'が', 'わかる？'] },
    { type: 'scramble', translate: 'Kemarin tidak hujan.', words: ['きのう', 'は', '雨《あめ》', 'が', '降《ふ》らなかった'] },
    { type: 'scramble', translate: 'Aku tidak butuh apa-apa sekarang.', words: ['いま', '何《なに》も', '欲《ほ》しくない'] },
    { type: 'scramble', translate: 'Apakah kamu tidak perlu membawa paspor?', words: ['パスポート', 'を', '持《も》って', '行《い》かなくても', 'いい？'] },
    { type: 'scramble', translate: 'Kemarin itu bukan hari libur.', words: ['きのう', 'は', '休《やす》み', 'じゃなかった'] },
  ] },
  { id: 21, focus: '〜と思《おも》います、〜と言《い》いました、でしょう', questions: [
    { type: 'mc', q: '明日《あした》 雨《あめ》が 降《ふ》る＿＿＿ 思《おも》います。', choices: ['と', 'が', 'の', 'を'], answer: 0, img: 'tabler-cloud-rain' , translate: 'Saya kira besok akan turun hujan.' },
    { type: 'mc', q: '図書館《としょかん》は きょう 休《やす》みですか。…いいえ、休《やす》みじゃない＿＿＿ 思《おも》います。', choices: ['と', 'が', 'の', 'を'], answer: 0 , translate: 'Apakah perpustakaan libur hari ini? ...Tidak, saya kira tidak libur.' },
    { type: 'mc', q: 'ミラーさんは お酒《さけ》を＿＿＿でしょう？…ええ、飲《の》みます。', choices: ['飲《の》む', '飲《の》みます', '飲《の》みました', '飲《の》んで'], answer: 0 , translate: 'Miller minum sake, kan? ...Ya, dia minum.' },
    { type: 'mc', q: 'B：スキーに 行《い》きます。→ Bさんは スキーに 行《い》く＿＿＿ 言《い》いました。', choices: ['と', 'に', 'を', 'が'], answer: 0 , translate: 'B akan pergi ski. → B bilang akan pergi ski.' },
    { type: 'mc', q: 'この 資料《しりょう》は 役《やく》に 立《た》ちますか。…ええ、とても 役《やく》に 立《た》つ＿＿＿ 思《おも》います。', choices: ['と', 'が', 'の', 'は'], answer: 0 , translate: 'Apakah berkas ini berguna? ...Ya, saya kira sangat berguna.' },
    { type: 'scramble', translate: 'Dia bilang akan datang jam sepuluh.', words: ['彼《かれ》', 'は', '10時《じゅうじ》', 'に', '来《く》る', 'と', '言《い》いました'] },
    { type: 'scramble', translate: 'Apakah bank hari Minggu libur ya?', words: ['銀行《ぎんこう》', 'は', '日曜日《にちようび》', '休《やす》み', 'でしょう？'] },
    { type: 'scramble', translate: 'Saya rasa mungkin akan ada rapat minggu depan.', words: ['来週《らいしゅう》', '会議《かいぎ》', 'が', 'ある', 'と', '思《おも》います'] },
    { type: 'mc', q: '病気《びょうき》の 友達《ともだち》に 何《なん》＿＿＿ 言《い》いますか。', choices: ['と', 'が', 'を', 'の'], answer: 0 , translate: 'Apa yang dikatakan kepada teman yang sedang sakit?' },
    { type: 'scramble', translate: 'Saya rasa Iwan orang yang pandai.', words: ['イーさん', 'は', '頭《あたま》', 'が', 'いい', 'と', '思《おも》います'] },
    { type: 'mc', q: 'A：あした　雨《あめ》が　降《ふ》ると　思《おも》いますか。\nB：＿＿＿。', choices: ['ええ、降《ふ》ると　思《おも》います', 'ええ、降《ふ》りました', 'いいえ、降《ふ》ります', 'ええ、降《ふ》って　います'], answer: 0, img: 'tabler-message-circle' , translate: 'Apakah kamu kira besok akan hujan? ...Ya, saya kira akan hujan.' },
    { type: 'mc', q: '部長《ぶちょう》：来週《らいしゅう》　名古屋《なごや》へ　出張《しゅっちょう》します。\n木村《きむら》：部長《ぶちょう》は　＿＿＿。', choices: ['来週《らいしゅう》　名古屋《なごや》へ　出張《しゅっちょう》すると　言《い》いました', '来週《らいしゅう》　名古屋《なごや》へ　出張《しゅっちょう》しました', '来週《らいしゅう》　名古屋《なごや》が　好《す》きです', '来週《らいしゅう》　名古屋《なごや》へ　行《い》きたいです'], answer: 0, img: 'tabler-message-circle' , translate: 'Kepala bagian: Minggu depan saya dinas ke Nagoya. → Kimura: Kepala bagian bilang minggu depan akan dinas ke Nagoya.' },
    { type: 'mc', q: 'ミラーさんは 伊藤《いとう》さんを 知《し》って いますか。…いいえ、たぶん＿＿＿ 思《おも》います。', choices: ['知《し》らない', '知《し》っている', '知《し》った', '知《し》ります'], answer: 0 , translate: 'Apakah Miller kenal Ito? ...Tidak, saya kira mungkin tidak kenal.' },
    { type: 'mc', q: 'このカレーは 辛《から》いでしょう？…いいえ、そんなに＿＿＿。', choices: ['辛《から》くないです', '辛《から》いです', '辛《から》かったです', '辛《から》くて'], answer: 0 , translate: 'Kari ini pedas ya? ...Tidak, tidak terlalu pedas.' },
    { type: 'mc', q: 'カリナさんは＿＿＿でしょう？…ええ、留学生《りゅうがくせい》です。', choices: ['留学生《りゅうがくせい》', '留学生《りゅうがくせい》です', '留学生《りゅうがくせい》な', '留学生《りゅうがくせい》の'], answer: 0 , translate: 'Karina itu mahasiswa asing, kan? ...Ya, mahasiswa asing.' },
    { type: 'scramble', translate: 'Saya pikir kartu telepon ini bisa dipakai.', words: ['この', 'テレホンカード', 'は', '使《つか》える', 'と', '思《おも》います'] },
    { type: 'scramble', translate: 'D bilang akan membaca komik dan menonton anime.', words: ['Dさん', 'は', 'マンガ', 'を', '読《よ》んだり', '、', 'アニメ', 'を', '見《み》たり', 'すると', '言《い》いました'] },
    { type: 'scramble', translate: 'E bilang harus menulis laporan.', words: ['Eさん', 'は', 'レポート', 'を', '書《か》かなければならないと', '言《い》いました'] },
    { type: 'scramble', translate: 'Harga-harga di sini tidak begitu tinggi.', words: ['ここ', 'は', '物価《ぶっか》', 'が', 'そんなに', '高《たか》くないです'] },
    { type: 'scramble', translate: 'Apakah besok pertandingan sepak bola akan menang menurutmu?', words: ['あした', 'の', 'サッカー', 'の', '試合《しあい》', 'は', '勝《か》つ', 'と', '思《おも》いますか'] },
  ] },
  { id: 22, focus: '名詞《めいし》を 修飾《しゅうしょく》する 文《ぶん》', questions: [
    { type: 'mc', q: '「赤《あか》い 帽子《ぼうし》を かぶっている 人《ひと》」の 意味《いみ》は？', choices: ['Orang yang memakai topi merah', 'Orang yang menjual topi merah', 'Topi merah orang itu', 'Orang yang membeli topi'], answer: 0, img: 'tabler-hat' , translate: 'Orang yang memakai topi merah.' },
    { type: 'mc', q: '私《わたし》が＿＿＿所《ところ》は 横浜《よこはま》です。（生《う》まれます）', choices: ['生《う》まれた', '生《う》まれます', '生《う》まれて', '生《う》まれる'], answer: 0 , translate: 'Tempat saya lahir adalah Yokohama.' },
    { type: 'mc', q: 'あの 赤《あか》い コートを＿＿＿人《ひと》は だれですか。（着《き》ます）', choices: ['着《き》ている', '着《き》る', '着《き》た', '着《き》て'], answer: 0 , translate: 'Siapa orang yang memakai mantel merah itu?' },
    { type: 'mc', q: '安《やす》い パソコンを＿＿＿店《みせ》を 知《し》っていますか。（売《う》ります）', choices: ['売《う》っている', '売《う》る', '売《う》った', '売《う》って'], answer: 0 , translate: 'Tahu toko yang menjual komputer murah?' },
    { type: 'mc', q: '旅行《りょこう》に＿＿＿人《ひと》は＿＿＿ですか。…15人《にん》です。', choices: ['行《い》く／なんにん', '行《い》った／どこ', '行《い》って／なに', '行《い》き／いつ'], answer: 0 , translate: 'Berapa orang yang ikut wisata? ...15 orang.' },
    { type: 'scramble', translate: 'Buku yang saya beli kemarin', words: ['きのう', '買《か》った', '本《ほん》'] },
    { type: 'scramble', translate: 'Orang yang sedang membaca koran adalah Wang.', words: ['新聞《しんぶん》', 'を', '読《よ》んでいる', '人《ひと》', 'は', 'ワンさん', 'です'] },
    { type: 'scramble', translate: 'Apakah punya waktu untuk membaca koran tiap pagi?', words: ['朝《あさ》', '新聞《しんぶん》', 'を', '読《よ》む', '時間《じかん》', 'が', 'ありますか'] },
    { type: 'mc', q: '妹《いもうと》さんが＿＿＿部屋《へや》の 家賃《やちん》は＿＿＿ですか。（借《か》ります）', choices: ['借《か》りている／いくら', '借《か》りる／だれ', '借《か》りた／なに', '借《か》りて／どこ'], answer: 0 , translate: 'Berapa sewa kamar yang disewa adik perempuan?' },
    { type: 'scramble', translate: 'Orang yang membuat air enak ini adalah kakak saya.', words: ['おいしい', '水《みず》', 'を', '作《つく》っている', 'の', 'は', '兄《あに》', 'です'] },
    { type: 'mc', q: 'A：ミラーさんは　どの　人《ひと》ですか。\nB：＿＿＿。', choices: ['あの　新聞《しんぶん》を　読《よ》んで　いる　人《ひと》です', 'あの　人《ひと》は　新聞《しんぶん》です', '新聞《しんぶん》が　好《す》きな　人《ひと》です', '新聞《しんぶん》を　読《よ》みたい　人《ひと》です'], answer: 0, img: 'tabler-message-circle' , translate: 'Siapa Miller? ...Dia orang yang sedang membaca koran itu.' },
    { type: 'mc', q: 'A：あなたが　生《う》まれた　所《ところ》は　どこですか。\nB：＿＿＿。', choices: ['横浜《よこはま》です', '横浜《よこはま》に　住《す》んで　います', '横浜《よこはま》が　好《す》きです', '横浜《よこはま》へ　行《い》きます'], answer: 0, img: 'tabler-message-circle' , translate: 'Di mana tempat kamu lahir? ...Yokohama.' },
    { type: 'mc', q: '安《やす》い パソコンを＿＿＿店《みせ》を 知《し》っていますか。', choices: ['売《う》っている', '売《う》る', '売《う》った', '売《う》って'], answer: 0 , translate: 'Tahu toko yang menjual komputer murah?' },
    { type: 'mc', q: '今《いま》 使《つか》っている 日本語《にほんご》の 本《ほん》は＿＿＿ですか。', choices: ['どう', 'どんな', 'なん', 'いつ'], answer: 0 , translate: 'Bagaimana buku bahasa Jepang yang sedang dipakai sekarang?' },
    { type: 'mc', q: '安《やす》いパソコンを 売《う》っている 店《みせ》を＿＿＿。', choices: ['知《し》っていますか', '知《し》りますか', '知《し》った', '知《し》って'], answer: 0 , translate: 'Tahu toko yang menjual komputer murah?' },
    { type: 'scramble', translate: 'Kapan waktunya luang untuk pergi bermain?', words: ['遊《あそ》びに', '行《い》く', '時間《じかん》', 'が', 'ある', 'とき', '、', 'いつ', 'ですか'] },
    { type: 'scramble', translate: 'Orang yang memakai kacamata itu guru saya.', words: ['眼鏡《めがね》', 'を', 'かけている', '人《ひと》', 'は', 'わたし', 'の', '先生《せんせい》', 'です'] },
    { type: 'scramble', translate: 'Apakah ada yang berbicara bahasa Jepang di keluarga Anda?', words: ['家族《かぞく》', 'で', '日本語《にほんご》', 'を', '話《はな》す', '人《ひと》', 'が', 'いますか'] },
    { type: 'scramble', translate: 'Bahasa asing pertama yang saya pelajari adalah bahasa Inggris.', words: ['初《はじ》めて', '習《なら》った', '外国語《がいこくご》', 'は', '英語《えいご》', 'です'] },
    { type: 'scramble', translate: 'Hotel yang saya inapi di Nagoya bersih dan pelayanannya bagus.', words: ['名古屋《なごや》', 'で', '泊《と》まった', 'ホテル', 'は', 'きれいで', '、', 'サービス', 'が', 'よかったです'] },
  ] },
  { id: 23, focus: '〜とき（〜する時《とき》／〜した時《とき》）', questions: [
    { type: 'mc', q: '疲《つか》れた＿＿＿、休《やす》みます。', choices: ['とき', 'こと', 'もの', 'ので'], answer: 0 , translate: 'Saat lelah, saya istirahat.' },
    { type: 'mc', q: '小《ちい》さい 字《じ》を＿＿＿とき、眼鏡《めがね》を かけます。', choices: ['読《よ》む', '読《よ》んだ', '読《よ》んで', '読《よ》み'], answer: 0 , translate: 'Saat membaca huruf kecil, saya memakai kacamata.' },
    { type: 'mc', q: '初《はじ》めて 富士山《ふじさん》を＿＿＿とき、きれいな 山《やま》だと 思《おも》いました。', choices: ['見《み》た', '見《み》る', '見《み》て', '見《み》ます'], answer: 0 , translate: 'Saat pertama kali melihat Gunung Fuji, saya pikir gunung yang indah.' },
    { type: 'mc', q: 'セーターを＿＿＿と、寒《さむ》いです。', choices: ['脱《ぬ》ぐ', '脱《ぬ》いだ', '脱《ぬ》いで', '脱《ぬ》ぎ'], answer: 0 , translate: 'Kalau melepas sweater, jadi dingin.' },
    { type: 'mc', q: 'お酒《さけ》を 飲《の》む＿＿＿、車《くるま》を 運転《うんてん》すると、危《あぶ》ないです。', choices: ['と', 'とき', 'から', 'し'], answer: 0 , translate: 'Berbahaya kalau minum sake lalu menyetir mobil.' },
    { type: 'scramble', translate: 'Waktu kecil, saya suka menggambar.', words: ['子供《こども》', 'の', 'とき', '、', '絵《え》', 'を', '描《か》くの', 'が', '好《す》きでした'] },
    { type: 'scramble', translate: 'Waktu tidak tahu jalan, saya naik taksi.', words: ['道《みち》', 'が', 'わからない', 'とき', '、', 'タクシー', 'に', '乗《の》ります'] },
    { type: 'scramble', translate: 'Kalau menekan tombol ini, tiket akan keluar.', words: ['ここ', 'を', '押《お》すと', '、', '切符《きっぷ》', 'が', '出《で》ます'] },
    { type: 'mc', q: 'この 歌《うた》を＿＿＿と、家族《かぞく》を 思《おも》い出《だ》します。', choices: ['聞《き》く', '聞《き》いた', '聞《き》いて', '聞《き》き'], answer: 0 , translate: 'Kalau mendengar lagu ini, saya teringat keluarga.' },
    { type: 'scramble', translate: 'Waktu ke rumah sakit, bawalah kartu asuransi.', words: ['病院《びょういん》', 'へ', '行《い》くとき', '、', '保険証《ほけんしょう》', 'を', '持《も》って', '行《い》きます'] },
    { type: 'mc', q: 'A：疲《つか》れた　とき、どう　しますか。\nB：＿＿＿。', choices: ['早《はや》く　休《やす》みます', '早《はや》く　休《やす》んで　います', '早《はや》く　休《やす》んだ　こと　が　あります', '早《はや》く　休《やす》みたい　です'], answer: 0, img: 'tabler-message-circle' , translate: 'Saat lelah, apa yang dilakukan? ...Saya segera istirahat.' },
    { type: 'mc', q: 'A：日本語《にほんご》を　勉強《べんきょう》する　とき、辞書《じしょ》を　使《つか》いますか。\nB：＿＿＿。', choices: ['ええ、よく　使《つか》います', 'ええ、使《つか》いました', 'いいえ、使《つか》って　います', 'いいえ、使《つか》いたいです'], answer: 0, img: 'tabler-message-circle' , translate: 'Saat belajar bahasa Jepang, apakah memakai kamus? ...Ya, sering memakai.' },
    { type: 'mc', q: 'コピーのサイズを＿＿＿とき、ここを 押《お》してください。（変《か》えます）', choices: ['変《か》えたい', '変《か》えた', '変《か》えて', '変《か》える'], answer: 0 , translate: 'Saat ingin mengubah ukuran fotokopi, tolong tekan di sini.' },
    { type: 'mc', q: '暇《ひま》な＿＿＿、よく 美術館《びじゅつかん》へ 絵《え》を 見《み》に 行《い》きます。', choices: ['とき', 'こと', 'もの', 'ので'], answer: 0 , translate: 'Saat senggang, saya sering pergi ke museum melihat lukisan.' },
    { type: 'mc', q: '子《こ》どもの＿＿＿、医者《いしゃ》に なりたいと 思《おも》いました。', choices: ['とき', 'こと', 'もの', 'ので'], answer: 0 , translate: 'Waktu kecil, saya ingin menjadi dokter.' },
    { type: 'scramble', translate: 'Waktu bus tidak datang, saya naik taksi.', words: ['バス', 'が', '来《こ》ない', 'とき', '、', 'タクシー', 'に', '乗《の》ります'] },
    { type: 'scramble', translate: 'Waktu kesepian, saya selalu mendengarkan musik ini.', words: ['寂《さび》しい', 'とき', '、', 'いつも', 'この', '音楽《おんがく》', 'を', '聞《き》きます'] },
    { type: 'scramble', translate: 'Belok kanan di persimpangan berikutnya, lalu ada perpustakaan di kiri.', words: ['次《つぎ》', 'の', '交差点《こうさてん》', 'を', '右《みぎ》', 'へ', '曲《ま》がると', '、', '左《ひだり》', 'に', '図書館《としょかん》', 'が', 'あります'] },
    { type: 'scramble', translate: 'Waktu meninggalkan rumah, saya bilang "itte kimasu".', words: ['出《で》かける', 'とき', '、', '「行《い》ってきます」', 'と', '言《い》います'] },
    { type: 'scramble', translate: 'Waktu menyeberang jalan, hati-hati dengan mobil.', words: ['道《みち》', 'を', '渡《わた》る', 'とき', '、', '車《くるま》', 'に', '気《き》を', 'つけて', 'ください'] },
  ] },
  { id: 24, focus: 'あげます・もらいます・くれます（人《ひと》のために）', questions: [
    { type: 'mc', q: '母《はは》は 私《わたし》に 傘《かさ》を ＿＿＿。（母《はは》→私《わたし》）', choices: ['くれました', 'あげました', 'もらいました', 'でした'], answer: 0, img: 'tabler-umbrella' , translate: 'Ibu memberi saya payung.' },
    { type: 'mc', q: '私《わたし》は リナさんに 花《はな》を ＿＿＿。（私《わたし》→リナ）', choices: ['あげました', 'くれました', 'もらいました', 'です'], answer: 0 , translate: 'Saya memberi bunga kepada Rina.' },
    { type: 'mc', q: 'このコピー、全部《ぜんぶ》一人《ひとり》でしましたか。…いいえ、カリナさんに＿＿＿もらいました。（手伝《てつだ》います）', choices: ['手伝《てつだ》って', '手伝《てつだ》う', '手伝《てつだ》った', '手伝《てつだ》い'], answer: 0 , translate: 'Apakah fotokopi ini semua dikerjakan sendiri? ...Tidak, dibantu oleh Karina.' },
    { type: 'mc', q: '木村《きむら》さんは あした 奈良《なら》を 案内《あんない》しますよ。→ わたしは 木村《きむら》さんに 奈良《なら》を 案内《あんない》して＿＿＿。', choices: ['もらいます', 'あげます', 'くれます', 'です'], answer: 0 , translate: 'Kimura akan memandu ke Nara besok. → Saya dipandu ke Nara oleh Kimura.' },
    { type: 'mc', q: 'すてきな セーターですね。どこで 買《か》いましたか。…母《はは》が＿＿＿。（作《つく》ります）', choices: ['作《つく》ってくれました', '作《つく》ってもらいました', '作《つく》ってあげました', '作《つく》りました'], answer: 0 , translate: 'Sweter yang bagus. Beli di mana? ...Ibu saya yang membuatkannya.' },
    { type: 'scramble', translate: 'Saya diberitahu (dibantu) oleh Sato tentang cara membuat sukiyaki.', words: ['佐藤《さとう》さん', 'に', 'すき焼《や》き', 'の', '作《つく》り方《かた》', 'を', '教《おし》えてもらいました'] },
    { type: 'scramble', translate: 'Guru Kobayashi mengajari saya bahasa Jepang.', words: ['小林《こばやし》先生《せんせい》', 'は', '日本語《にほんご》', 'を', '教《おし》えて', 'くれました'] },
    { type: 'mc', q: '一人《ひとり》で 病院《びょういん》へ 行《い》きましたか。…いいえ、山田《やまだ》さんに＿＿＿。', choices: ['いっしょに行《い》ってもらいました', 'いっしょに行《い》ってあげました', '行《い》きました', '行《い》っています'], answer: 0 , translate: 'Apakah pergi ke rumah sakit sendirian? ...Tidak, saya ditemani pergi oleh Yamada.' },
    { type: 'scramble', translate: 'Waktu ada teman asing datang, saya akan pandu tempat wisata di negara saya.', words: ['外国《がいこく》', 'から', '友達《ともだち》', 'が', '来《き》たとき', '、', '国《くに》', 'の', 'いい', '所《ところ》', 'を', '案内《あんない》してあげます'] },
    { type: 'mc', q: 'A：すてきな　セーターですね。どこで　買《か》いましたか。\nB：＿＿＿。', choices: ['母《はは》が　作《つく》って　くれました', '母《はは》に　作《つく》って　あげました', '母《はは》が　作《つく》って　もらいました', '母《はは》の　セーターです'], answer: 0, img: 'tabler-message-circle' , translate: 'Sweter yang bagus. Beli di mana? ...Ibu saya yang membuatkannya.' },
    { type: 'mc', q: 'A：木村《きむら》さんの　電話《でんわ》番号《ばんごう》が　分《わ》かりましたか。\nB：ええ、＿＿＿。', choices: ['佐藤《さとう》さんに　教《おし》えて　もらいました', '佐藤《さとう》さんに　教《おし》えて　あげました', '佐藤《さとう》さんが　教《おし》えました', '佐藤《さとう》さんは　知《し》りません'], answer: 0, img: 'tabler-message-circle' , translate: 'Sudah tahu nomor telepon Kimura? ...Ya, diberi tahu oleh Sato.' },
    { type: 'mc', q: 'すてきな セーターですね。母《はは》が＿＿＿。（送《おく》ります）', choices: ['送《おく》ってくれました', '送《おく》ってあげました', '送《おく》りました', '送《おく》っています'], answer: 0 , translate: 'Sweter yang bagus. Ibu saya yang mengirimkannya.' },
    { type: 'mc', q: 'あした 引《ひ》っ越《こ》しの 手伝《てつだ》いに 行《い》く 人《ひと》が いますか。…ええ、佐藤《さとう》さんと ミラーさんが＿＿＿。', choices: ['来《き》てくれます', '来《き》てあげます', '来《き》てもらいます', '来《き》ます'], answer: 0 , translate: 'Besok ada yang datang membantu pindahan? ...Ya, Sato dan Miller akan datang untuk saya.' },
    { type: 'mc', q: 'ビールと ジュースと ワインを 買《か》いました。ほかに 何《なに》か 飲《の》み物《もの》が＿＿＿か。', choices: ['要《い》ります', '要《い》りません', 'あります', 'いります？'], answer: 0 , translate: 'Saya membeli bir, jus, dan anggur. Apakah perlu minuman lain?' },
    { type: 'scramble', translate: 'Bibi saya membelikan saya sepeda.', words: ['おば', 'が', '自転車《じてんしゃ》', 'を', '買《か》って', 'くれました'] },
    { type: 'scramble', translate: 'Saya meminta bantuan Karina karena tidak paham kanji.', words: ['漢字《かんじ》', 'が', 'わかりません', 'から', '、', 'カリナさん', 'に', '手伝《てつだ》って', 'もらいました'] },
    { type: 'scramble', translate: 'Kapan pun teman dari luar negeri datang, saya akan memandunya keliling kota.', words: ['外国《がいこく》', 'の', '友達《ともだち》', 'が', '来《き》たら', '、', '町《まち》', 'を', '案内《あんない》してあげます'] },
    { type: 'scramble', translate: 'Kobayashi sensei mengajari saya bahasa Jepang.', words: ['小林《こばやし》先生《せんせい》', 'は', '日本語《にほんご》', 'を', '教《おし》えて', 'くれました'] },
    { type: 'scramble', translate: 'Saya membuatkan sukiyaki untuk Matsumoto.', words: ['松本《まつもと》さん', 'に', 'すき焼《や》き', 'を', '作《つく》って', 'あげました'] },
    { type: 'scramble', translate: 'Waktu kecil, siapa yang membelikanmu hadiah ulang tahun?', words: ['子《こ》どもの', 'とき', '、', '誕生日《たんじょうび》', 'に', '何《なに》', 'を', '買《か》って', 'もらいましたか'] },
  ] },
  { id: 25, focus: '〜たら（条件《じょうけん》）、〜ても', questions: [
    { type: 'mc', q: '時間《じかん》が あっ＿＿＿、寄《よ》ってください。', choices: ['たら', 'ても', 'から', 'ので'], answer: 0 , translate: 'Kalau ada waktu, mampirlah.' },
    { type: 'mc', q: 'いい 大学《だいがく》に＿＿＿ら、頑張《がんば》らなければ なりません。', choices: ['入《はい》った', '入《はい》る', '入《はい》って', '入《はい》り'], answer: 0 , translate: 'Kalau masuk universitas yang bagus, harus berusaha keras.' },
    { type: 'mc', q: '漢字《かんじ》が＿＿＿ら、ひらがなで 書《か》いても いいですか。', choices: ['分《わ》からなかった', '分《わ》からない', '分《わ》からなくて', '分《わ》かって'], answer: 0 , translate: 'Kalau tidak mengerti kanji, boleh menulis dengan hiragana?' },
    { type: 'mc', q: 'ゆっくり 歩《ある》いて＿＿＿、駅《えき》まで 15分《ふん》ぐらいです。', choices: ['も', 'たら', 'から', 'ので'], answer: 0 , translate: 'Kalau jalan santai pun, ke stasiun sekitar 15 menit.' },
    { type: 'mc', q: '簡単《かんたん》な 漢字《かんじ》＿＿＿も、なかなか 覚《おぼ》える ことが できません。', choices: ['でも', 'たら', 'から', 'ので'], answer: 0 , translate: 'Meskipun kanji yang mudah, tetap sulit dihafal.' },
    { type: 'scramble', translate: 'Meski hujan, saya tetap pergi.', words: ['雨《あめ》', 'が', '降《ふ》っても', '、', '行《い》きます'] },
    { type: 'scramble', translate: 'Kalau mendapat waktu libur, saya ingin bepergian.', words: ['休《やす》み', 'が', 'あったら', '、', '旅行《りょこう》', 'したい', 'です'] },
    { type: 'scramble', translate: 'Kalau tidak mengerti walau sudah dicari, tanyakan pada guru.', words: ['調《しら》べても', '、', '分《わ》からなかったら', '、', '先生《せんせい》', 'に', '聞《き》きます'] },
    { type: 'mc', q: '安《やす》くても、要《い》らない 物《もの》は＿＿＿。', choices: ['買《か》いません', '買《か》います', '買《か》った', '買《か》って'], answer: 0 , translate: 'Walaupun murah, barang yang tidak perlu tidak saya beli.' },
    { type: 'scramble', translate: 'Kalau sudah tua dan pensiun, saya ingin berkeliling dunia.', words: ['年《とし》', 'を', '取《と》って', '、', '仕事《しごと》', 'を', 'やめたら', '、', '世界《せかい》', 'を', '旅行《りょこう》したい', 'です'] },
    { type: 'mc', q: 'A：もし、大学《だいがく》を　出《で》たら、何《なに》を　したいですか。\nB：＿＿＿。', choices: ['留学《りゅうがく》して、もう少《すこ》し　勉強《べんきょう》したいです', '留学《りゅうがく》しました', '留学《りゅうがく》が　好《す》きです', '留学《りゅうがく》を　したいと　思《おも》いました'], answer: 0, img: 'tabler-message-circle' , translate: 'Kalau lulus kuliah, ingin melakukan apa? ...Saya ingin belajar lagi ke luar negeri.' },
    { type: 'mc', q: 'A：時間《じかん》が　あったら、どう　しますか。\nB：＿＿＿。', choices: ['遊《あそ》びに　来《き》て　ください', '遊《あそ》びに　来《き》ます', '遊《あそ》びに　来《き》ました', '遊《あそ》びに　来《く》る　と　思《おも》います'], answer: 0, img: 'tabler-message-circle' , translate: 'Kalau ada waktu, apa yang dilakukan? ...Datanglah main.' },
    { type: 'mc', q: '道《みち》が わからない＿＿＿、地図《ちず》を 見《み》ます。', choices: ['とき', 'たら', 'ても', 'から'], answer: 0 , translate: 'Saat tidak tahu jalan, saya melihat peta.' },
    { type: 'mc', q: 'このパソコンは すぐ 故障《こしょう》します＿＿＿。（修理《しゅうり》しても）', choices: ['修理《しゅうり》しても', '修理《しゅうり》したら', '修理《しゅうり》すると', '修理《しゅうり》するので'], answer: 0 , translate: 'Komputer ini mudah rusak meskipun sudah diperbaiki.' },
    { type: 'mc', q: 'お酒《さけ》を 飲《の》んだら、車《くるま》を 運転《うんてん》しないで＿＿＿。', choices: ['ください', 'ください？', 'くれます', 'あげます'], answer: 0 , translate: 'Kalau sudah minum sake, jangan menyetir mobil.' },
    { type: 'scramble', translate: 'Meskipun rumahnya lama, kalau sewanya murah saya mau menyewanya.', words: ['古《ふる》くても', '、', '家賃《やちん》', 'が', '安《やす》かったら', '、', '借《か》りたい', 'です'] },
    { type: 'scramble', translate: 'Kalau sudah punya waktu senggang, saya ingin mendaki Gunung Fuji.', words: ['暇《ひま》', 'が', 'あったら', '、', '富士山《ふじさん》', 'に', '登《のぼ》りたい', 'です'] },
    { type: 'scramble', translate: 'Meski sudah mencari, tidak ketemu.', words: ['探《さが》しても', '、', '見《み》つかりませんでした'] },
    { type: 'scramble', translate: 'Kalau seandainya bisa lahir sekali lagi, kamu mau jadi laki-laki atau perempuan?', words: ['もし', '、', 'もう一度《いちど》', '生《う》まれる', 'こと', 'が', 'できたら', '、', '男《おとこ》の人《ひと》', 'が', 'いいですか'] },
    { type: 'scramble', translate: 'Kalau tiba jam 3 sore di Kyoto, saya akan jemput.', words: ['3時《さんじ》', 'ごろ', '京都《きょうと》', 'に', '着《つ》いたら', '、', '迎《むか》えに', '行《い》きます'] },
  ] },
]

const REVIEWS = [
  { id: 'r1-8', label: 'まとめ 1〜8', questions: [
    { type: 'mc', q: 'これは 私《わたし》＿＿＿ かばんです。', choices: ['の', 'は', 'を', 'に'], answer: 0 , translate: 'Tas ini tas saya.' },
    { type: 'mc', q: '毎朝《まいあさ》 6時《ろくじ》＿＿＿ 起《お》きます。', choices: ['に', 'で', 'を', 'へ'], answer: 0 , translate: 'Setiap pagi saya bangun jam 6.' },
    { type: 'mc', q: '「安《やす》い」の 反対《はんたい》は？', choices: ['高《たか》い', '大《おお》きい', '新《あたら》しい', '静《しず》か'], answer: 0 , translate: 'Lawan kata \'murah\' apa? ...Mahal.' },
    { type: 'scramble', translate: 'Kemarin saya pergi ke Bandung.', words: ['きのう', 'バンドン', 'へ', '行《い》きました'] },
    { type: 'mc', q: 'このかばんは＿＿＿ですか。…8,300円《えん》です。', choices: ['いくら', 'だれの', 'なんじ', 'どこ'], answer: 0 , translate: 'Tas ini harganya berapa? ...8.300 yen.' },
    { type: 'mc', q: '銀行《ぎんこう》は 9時《じ》＿＿＿ 3時《じ》＿＿＿です。', choices: ['から／まで', 'まで／から', 'に／で', 'を／へ'], answer: 0 , translate: 'Bank buka dari jam 9 sampai jam 3.' },
    { type: 'mc', q: '「静《しず》か」の 反対《はんたい》は？', choices: ['賑《にぎ》やか', '有名《ゆうめい》', '暇《ひま》', '親切《しんせつ》'], answer: 0 , translate: 'Lawan kata \'sepi/tenang\' apa? ...Ramai.' },
    { type: 'scramble', translate: 'Saya belajar bahasa Jepang bersama Karina.', words: ['カリナさん', 'と', '日本語《にほんご》', 'を', '勉強《べんきょう》します'] },
    { type: 'mc', q: 'あの 方《かた》は＿＿＿ですか。（丁寧《ていねい》に「だれ」）', choices: ['どなた', 'どちら', 'どこ', 'なに'], answer: 0 , translate: 'Siapa orang itu? (sopan)' },
    { type: 'mc', q: 'これは 山田《やまだ》さん＿＿＿ 傘《かさ》です。', choices: ['の', 'は', 'を', 'に'], answer: 0 , translate: 'Ini payung Yamada.' },
    { type: 'mc', q: 'この 時計《とけい》は＿＿＿ですか。…3,800円《えん》です。', choices: ['いくら', 'なんじ', 'だれの', 'どこ'], answer: 0 , translate: 'Jam ini harganya berapa? ...3.800 yen.' },
    { type: 'mc', q: '図書館《としょかん》は 9時《じ》＿＿＿ 6時《じ》＿＿＿です。', choices: ['から／まで', 'まで／から', 'に／で', 'を／へ'], answer: 0 , translate: 'Perpustakaan buka dari jam 9 sampai jam 6.' },
    { type: 'mc', q: '去年《きょねん》の 4月《がつ》に 日本《にほん》へ＿＿＿。', choices: ['来《き》ました', '来《き》ます', '来《く》る', '来《き》て'], answer: 0 , translate: 'Tahun lalu bulan April saya datang ke Jepang.' },
    { type: 'mc', q: '毎朝《まいあさ》パン＿＿＿ 卵《たまご》＿＿＿ 食《た》べます。', choices: ['と／を', 'を／と', 'に／で', 'の／を'], answer: 0 , translate: 'Setiap pagi saya makan roti dan telur.' },
    { type: 'mc', q: '誕生日《たんじょうび》に 友達《ともだち》＿＿＿ プレゼントを もらいました。', choices: ['に', 'を', 'で', 'へ'], answer: 0 , translate: 'Saat ulang tahun saya menerima hadiah dari teman.' },
    { type: 'mc', q: '「暑《あつ》い」の 反対《はんたい》は？', choices: ['寒《さむ》い', '低《ひく》い', '暗《くら》い', '軽《かる》い'], answer: 0 , translate: 'Lawan kata \'panas\' apa? ...Dingin.' },
    { type: 'mc', q: 'このレストランは 小《ちい》さいです＿＿＿、有名《ゆうめい》です。', choices: ['が', 'に', 'を', 'の'], answer: 0 , translate: 'Restoran ini kecil, tapi terkenal.' },
    { type: 'scramble', translate: 'Ini adalah tas milik Yamada.', words: ['これ', 'は', '山田《やまだ》さん', 'の', 'かばん', 'です'] },
    { type: 'scramble', translate: 'Kemarin saya bertemu dengan guru bahasa Jepang.', words: ['きのう', '日本語《にほんご》', 'の', '先生《せんせい》', 'に', '会《あ》いました'] },
    { type: 'scramble', translate: 'Bank tutup jam 3.', words: ['銀行《ぎんこう》', 'は', '3時《さんじ》', 'に', '終《お》わります'] },
  ] },
  { id: 'r9-17', label: 'まとめ 9〜17', questions: [
    { type: 'mc', q: '私《わたし》は 果物《くだもの》＿＿＿ 好《す》きです。', choices: ['が', 'を', 'に', 'へ'], answer: 0, img: 'tabler-apple' , translate: 'Saya suka buah.' },
    { type: 'mc', q: '猫《ねこ》は 椅子《いす》の＿＿＿に います。', choices: ['下《した》', '上《うえ》', '中《なか》', '前《まえ》'], answer: 0 , translate: 'Kucing ada di bawah kursi.' },
    { type: 'mc', q: '「飲《の》みます」の て形《けい》は？', choices: ['飲《の》んで', '飲《の》みて', '飲《の》いて', '飲《の》って'], answer: 0 , translate: 'Bentuk te dari \'minum\' apa?' },
    { type: 'scramble', translate: 'Saya harus belajar setiap hari.', words: ['毎日《まいにち》', '勉強《べんきょう》', 'しなければなりません'] },
    { type: 'mc', q: '地下鉄《ちかてつ》は バス＿＿＿ 速《はや》いです。', choices: ['より', 'から', 'まで', 'ほど'], answer: 0 , translate: 'Kereta bawah tanah lebih cepat daripada bus.' },
    { type: 'mc', q: '窓《まど》を＿＿＿ください。（開《あ》けます）', choices: ['開《あ》けて', '開《あ》けます', '開《あ》けた', '開《あ》ける'], answer: 0 , translate: 'Tolong buka jendela.' },
    { type: 'scramble', translate: 'Setelah selesai bekerja, saya pergi ke perpustakaan untuk meminjam buku.', words: ['仕事《しごと》', 'が', '終《お》わってから', '、', '本《ほん》', 'を', '借《か》りに', '図書館《としょかん》', 'へ', '行《い》きます'] },
    { type: 'mc', q: 'どんな スポーツ＿＿＿ 好《す》きですか。', choices: ['が', 'を', 'に', 'へ'], answer: 0, img: 'tabler-ball-football' , translate: 'Suka olahraga apa?' },
    { type: 'mc', q: '事務所《じむしょ》に だれ＿＿＿ いますか。…だれ＿＿＿ いません。', choices: ['が／も', 'は／が', 'を／は', 'に／が'], answer: 0 , translate: 'Apakah ada orang di kantor? ...Tidak ada siapa-siapa.' },
    { type: 'mc', q: '家族《かぞく》は＿＿＿です。（5人《にん》）', choices: ['ごにん', 'いつつ', 'ごまい', 'ごだい'], answer: 0 , translate: 'Keluarga saya 5 orang.' },
    { type: 'mc', q: '東京《とうきょう》は 大阪《おおさか》＿＿＿ 人《ひと》が 多《おお》いです。', choices: ['より', 'ほど', 'ので', 'でも'], answer: 0 , translate: 'Di Tokyo orangnya lebih banyak daripada Osaka.' },
    { type: 'mc', q: '私《わたし》は 新《あたら》しい パソコン＿＿＿ 欲《ほ》しいです。', choices: ['が', 'を', 'に', 'は'], answer: 0, img: 'tabler-device-laptop' , translate: 'Saya ingin komputer baru.' },
    { type: 'mc', q: 'すみませんが、松本《まつもと》さんの 電話《でんわ》番号《ばんごう》を＿＿＿。（教《おし》えます→〜てください）', choices: ['教《おし》えてください', '教《おし》えました', '教《おし》える', '教《おし》えて'], answer: 0 , translate: 'Maaf, tolong beritahu nomor telepon Matsumoto.' },
    { type: 'mc', q: '兄《あに》の 会社《かいしゃ》は 電気《でんき》製品《せいひん》を＿＿＿います。（作《つく》ります）', choices: ['作《つく》って', '作《つく》んで', '作《つく》きて', '作《つく》いて'], answer: 0 , translate: 'Perusahaan kakak laki-laki saya membuat produk elektronik.' },
    { type: 'mc', q: '仕事《しごと》が 終《お》わって＿＿＿、1時間《いちじかん》ぐらい プールで 泳《およ》ぎます。', choices: ['から', 'まで', 'ので', 'し'], answer: 0 , translate: 'Setelah pekerjaan selesai, saya berenang di kolam sekitar 1 jam.' },
    { type: 'mc', q: '危《あぶ》ないですから、うしろから＿＿＿。', choices: ['押《お》さないでください', '押《お》してください', '押《お》しました', '押《お》します'], answer: 0 , translate: 'Karena berbahaya, jangan dorong dari belakang.' },
    { type: 'mc', q: '両親《りょうしん》が 来《き》ますから、空港《くうこう》へ 迎《むか》えに＿＿＿。', choices: ['行《い》かなければなりません', '行《い》かなくてもいいです', '行《い》かないでください', '行《い》っています'], answer: 0 , translate: 'Karena orang tua akan datang, saya harus menjemput ke bandara.' },
    { type: 'scramble', translate: 'Saya paling suka masakan Jepang.', words: ['日本《にほん》', '料理《りょうり》', 'が', 'いちばん', '好《す》きです'] },
    { type: 'scramble', translate: 'Ada dua orang di depan pintu.', words: ['ドア', 'の', '前《まえ》', 'に', '二人《ふたり》', 'います'] },
    { type: 'scramble', translate: 'Tolong buka jendela.', words: ['窓《まど》', 'を', '開《あ》けて', 'ください'] },
  ] },
  { id: 'r18-25', label: 'まとめ 18〜25', questions: [
    { type: 'mc', q: '「泳《およ》ぎます」の 辞書《じしょ》形《けい》は？', choices: ['泳《およ》ぐ', '泳《およ》いで', '泳《およ》ぎて', '泳《およ》いた'], answer: 0 , translate: 'Bentuk kamus dari \'berenang\' apa?' },
    { type: 'mc', q: '明日《あした》 雨《あめ》が 降《ふ》る＿＿＿ 思《おも》います。', choices: ['と', 'が', 'の', 'を'], answer: 0 , translate: 'Saya kira besok akan turun hujan.' },
    { type: 'scramble', translate: 'Ketika lelah, saya tidur cepat.', words: ['疲《つか》れた', 'とき', '、', '早《はや》く', '寝《ね》ます'] },
    { type: 'scramble', translate: 'Kalau ada waktu, datanglah main.', words: ['時間《じかん》', 'が', 'あったら', '、', '遊《あそ》びに', '来《き》てください'] },
    { type: 'mc', q: '私《わたし》は 車《くるま》の 運転《うんてん》が ＿＿＿。（できます）', choices: ['できます', 'します', 'あります', 'います'], answer: 0 , translate: 'Saya bisa menyetir mobil.' },
    { type: 'mc', q: '母《はは》は 私《わたし》に セーターを＿＿＿。（母《はは》→私《わたし》）', choices: ['くれました', 'あげました', 'もらいました', 'でした'], answer: 0 , translate: 'Ibu memberi saya sweter.' },
    { type: 'scramble', translate: 'Buku yang sedang dibaca oleh Wang itu menarik.', words: ['ワンさん', 'が', '読《よ》んでいる', '本《ほん》', 'は', 'おもしろい', 'です'] },
    { type: 'mc', q: '「食《た》べます」の 辞書《じしょ》形《けい》は？', choices: ['食《た》べる', '食《た》べて', '食《た》べます', '食《た》べた'], answer: 0 , translate: 'Bentuk kamus dari \'makan\' apa?' },
    { type: 'mc', q: '漢字《かんじ》が 分《わ》かりませんから、日本語《にほんご》の 新聞《しんぶん》を＿＿＿。', choices: ['読《よ》むことができません', '読《よ》みたいです', '読《よ》んでいます', '読《よ》んでください'], answer: 0 , translate: 'Karena tidak mengerti kanji, saya tidak bisa membaca koran Jepang.' },
    { type: 'mc', q: '富士山《ふじさん》に 登《のぼ》った＿＿＿が あります。', choices: ['こと', 'もの', 'とき', 'ところ'], answer: 0, img: 'tabler-mountain' , translate: 'Saya pernah mendaki Gunung Fuji.' },
    { type: 'mc', q: '休《やす》みの 日《ひ》は 本《ほん》を＿＿＿り、テレビを＿＿＿り します。', choices: ['読《よ》んだ／見《み》た', '読《よ》む／見《み》る', '読《よ》んで／見《み》て', '読《よ》み／見《み》'], answer: 0 , translate: 'Hari libur saya membaca buku dan menonton TV.' },
    { type: 'mc', q: '英語《えいご》が 分《わ》かる？…うん、＿＿＿。', choices: ['できる', 'できます', 'できた', 'できて'], answer: 0 , translate: 'Mengerti bahasa Inggris? ...Ya, bisa.' },
    { type: 'mc', q: '図書館《としょかん》は きょう 休《やす》みですか。…いいえ、休《やす》みじゃない＿＿＿ 思《おも》います。', choices: ['と', 'が', 'の', 'を'], answer: 0 , translate: 'Apakah perpustakaan libur hari ini? ...Tidak, saya kira tidak libur.' },
    { type: 'mc', q: 'あの 赤《あか》い コートを＿＿＿人《ひと》は だれですか。（着《き》ます）', choices: ['着《き》ている', '着《き》る', '着《き》た', '着《き》て'], answer: 0 , translate: 'Siapa orang yang memakai mantel merah itu?' },
    { type: 'mc', q: '小《ちい》さい 字《じ》を＿＿＿とき、眼鏡《めがね》を かけます。', choices: ['読《よ》む', '読《よ》んだ', '読《よ》んで', '読《よ》み'], answer: 0 , translate: 'Saat membaca huruf kecil, saya memakai kacamata.' },
    { type: 'mc', q: 'すてきな セーターですね。どこで 買《か》いましたか。…母《はは》が＿＿＿。（作《つく》ります）', choices: ['作《つく》ってくれました', '作《つく》ってもらいました', '作《つく》ってあげました', '作《つく》りました'], answer: 0 , translate: 'Sweter yang bagus. Beli di mana? ...Ibu saya yang membuatkannya.' },
    { type: 'mc', q: 'いい 大学《だいがく》に＿＿＿ら、頑張《がんば》らなければ なりません。', choices: ['入《はい》った', '入《はい》る', '入《はい》って', '入《はい》り'], answer: 0 , translate: 'Kalau masuk universitas yang bagus, harus berusaha keras.' },
    { type: 'scramble', translate: 'Saya bisa berenang 50 meter.', words: ['50メートル', '泳《およ》ぐ', 'こと', 'が', 'できます'] },
    { type: 'scramble', translate: 'Apakah Anda pernah menulis surat dengan bahasa Jepang?', words: ['日本語《にほんご》', 'で', '手紙《てがみ》', 'を', '書《か》いた', 'こと', 'が', 'ありますか'] },
    { type: 'scramble', translate: 'Dia bilang akan datang jam sepuluh.', words: ['彼《かれ》', 'は', '10時《じゅうじ》', 'に', '来《く》る', 'と', '言《い》いました'] },
  ] },
  { id: 'r1-25', label: 'まとめ 1〜25', questions: [
    { type: 'mc', q: 'あの 方《かた》は＿＿＿ですか。（丁寧《ていねい》に「だれ」）', choices: ['どなた', 'どちら', 'どこ', 'なに'], answer: 0 , translate: 'Siapa orang itu? (sopan)' },
    { type: 'mc', q: '窓《まど》を＿＿＿ください。（閉《し》めます）', choices: ['閉《し》めて', '閉《し》めます', '閉《し》めた', '閉《し》める'], answer: 0 , translate: 'Tolong tutup jendela.' },
    { type: 'mc', q: '漢字《かんじ》が 分《わ》から＿＿＿から、ひらがなで 書《か》きます。', choices: ['ない', 'ありません', 'ません', 'なくて'], answer: 0 , translate: 'Karena tidak mengerti kanji, saya menulis dengan hiragana.' },
    { type: 'scramble', translate: 'Saya meminjam CD dari Rina.', words: ['リナさん', 'に', 'CD', 'を', '借《か》りました'] },
    { type: 'scramble', translate: 'Kalau hujan turun, pertandingan tidak bisa dilaksanakan.', words: ['雨《あめ》', 'が', '降《ふ》る', 'と', '、', '試合《しあい》', 'が', 'できません'] },
    { type: 'mc', q: '田中《たなか》さんは＿＿＿人《ひと》ですか。…あの 眼鏡《めがね》を かけている 人《ひと》です。', choices: ['どの', 'だれの', 'なんの', 'いつの'], answer: 0 , translate: 'Yang mana Tanaka? ...Dia orang yang memakai kacamata itu.' },
    { type: 'mc', q: '疲《つか》れた＿＿＿、早《はや》く 寝《ね》ます。', choices: ['とき', 'こと', 'もの', 'ので'], answer: 0 , translate: 'Saat lelah, saya cepat tidur.' },
    { type: 'scramble', translate: 'Tolong sampaikan salam kepada semua di kantor.', words: ['会社《かいしゃ》', 'の', 'みなさん', 'に', 'よろしく', '言《い》ってください'] },
    { type: 'mc', q: 'これは＿＿＿の カメラですか。…日本《にほん》のです。', choices: ['どこ', 'なに', 'だれ', 'いつ'], answer: 0, img: 'tabler-camera' , translate: 'Kamera ini buatan mana? ...Buatan Jepang.' },
    { type: 'mc', q: '毎日《まいにち》コーヒー＿＿＿ 飲《の》みます。', choices: ['を', 'に', 'で', 'と'], answer: 0 , translate: 'Setiap hari saya minum kopi.' },
    { type: 'mc', q: '暑《あつ》いです＿＿＿、窓《まど》を 開《あ》けます。', choices: ['から', 'が', 'ので', 'し'], answer: 0 , translate: 'Karena panas, saya membuka jendela.' },
    { type: 'mc', q: '机《つくえ》の 上《うえ》に 本《ほん》が＿＿＿。', choices: ['あります', 'います', 'です', 'ました'], answer: 0, img: 'tabler-book' , translate: 'Ada buku di atas meja.' },
    { type: 'mc', q: '地下鉄《ちかてつ》は バス＿＿＿ 速《はや》いです。', choices: ['より', 'から', 'まで', 'ほど'], answer: 0, img: 'tabler-train' , translate: 'Kereta bawah tanah lebih cepat daripada bus.' },
    { type: 'mc', q: 'のど＿＿＿ かわきましたから、何《なに》＿＿＿ 飲《の》みたいです。', choices: ['が／か', 'を／が', 'に／を', 'は／か'], answer: 0 , translate: 'Karena tenggorokan haus, saya ingin minum sesuatu.' },
    { type: 'mc', q: '「書《か》きます」の て形《けい》は？', choices: ['書《か》いて', '書《か》んで', '書《か》きて', '書《か》って'], answer: 0 , translate: 'Bentuk te dari \'menulis\' apa?' },
    { type: 'mc', q: '今《いま》 新聞《しんぶん》を＿＿＿。（読《よ》んでいます）', choices: ['読《よ》んでいます', '読《よ》みます', '読《よ》みました', '読《よ》む'], answer: 0, img: 'tabler-news' , translate: 'Sekarang saya sedang membaca koran.' },
    { type: 'mc', q: '明日《あした》は 早《はや》く 起《お》き＿＿＿。（義務《ぎむ》）', choices: ['なければなりません', 'なくてもいいです', 'ないでください', 'てください'], answer: 0 , translate: 'Besok saya harus bangun pagi.' },
    { type: 'scramble', translate: 'Saya tinggal di Bandung.', words: ['バンドン', 'に', '住《す》んでいます'] },
    { type: 'scramble', translate: 'Saya bisa berbicara bahasa Prancis dan Spanyol.', words: ['フランス語《ご》', 'と', 'スペイン語《ご》', 'を', '話《はな》す', 'こと', 'が', 'できます'] },
    { type: 'scramble', translate: 'Waktu lelah, saya beristirahat.', words: ['疲《つか》れた', 'とき', '、', '休《やす》みます'] },
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
// Progres belajar per lesson/rangkuman — disimpan di localStorage supaya
// bertahan lintas sesi. `crown: true` cuma didapat kalau satu set
// diselesaikan tanpa satupun jawaban salah (nyawa penuh sampai akhir).
// `done: true` tapi tanpa crown berarti sudah pernah selesai tapi masih
// ada kesalahan — tetap tercatat, cuma belum dapat mahkota.
// ---------------------------------------------------------------------
const PROGRESS_STORAGE_KEY = 'mondaishuu-progress-v1'

function loadStoredProgress() {
  try {
    const raw = localStorage.getItem(PROGRESS_STORAGE_KEY)

    return raw ? JSON.parse(raw) : {}
  }
  catch {
    return {}
  }
}

const progress = reactive(loadStoredProgress())

function saveProgress() {
  try {
    localStorage.setItem(PROGRESS_STORAGE_KEY, JSON.stringify(progress))
  }
  catch {
    // localStorage bisa saja tidak tersedia (mode privat dsb) — abaikan saja.
  }
}

function markProgress(key, perfect) {
  const prev = progress[key]

  progress[key] = { done: true, crown: perfect || !!prev?.crown }
  saveProgress()
}

function statusFor(key) {
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
const pathUnits = computed(() => [
  {
    id: 'lessons',
    title: t('mondaishuu.lessons_label'),
    lessons: LESSONS.map(l => ({
      id: `l${l.id}`,
      title: t('mondaishuu.lesson_label', { n: l.id }),
      status: statusFor(`l${l.id}`),
    })),
  },
  {
    id: 'reviews',
    title: t('mondaishuu.reviews_label'),
    lessons: REVIEWS.map(r => ({
      id: r.id,
      title: r.label,
      status: statusFor(r.id),
    })),
  },
])

function openPathLesson(lesson) {
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
    translate: q.translate ?? null,
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

    <!-- Progres belajar ala jalur belajar: node "mahkota" untuk set yang
         diselesaikan tanpa satupun kesalahan, "selesai" untuk yang pernah
         ditamatkan tapi masih ada yang salah, dan "tersedia" untuk yang
         belum pernah dicoba. Semua node bisa langsung diketuk. -->
    <LearningPath
      :units="pathUnits"
      @open="openPathLesson"
    />
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
                style="white-space: pre-line"
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
                <span class="text-medium-emphasis">「{{ currentQuestion.translate }}」</span>
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
            <small v-if="feedback.translate" class="d-block" lang="id">
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
  gap: 8px;
  border-radius: 999px;
  background: rgba(var(--v-theme-warning), 0.14);
  color: rgb(var(--v-theme-warning));
  font-weight: 600;
  padding-block: 8px;
  padding-inline: 18px;
}

/* ---------- lesson grid (setup stage) ---------- */
.mondaishuu-grid {
  display: grid;
  gap: 10px;
  grid-template-columns: repeat(auto-fill, minmax(112px, 1fr));
}

.mondaishuu-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  color: rgb(var(--v-theme-on-surface));
  cursor: pointer;
  gap: 6px;
  min-block-size: 104px;
  padding-block: 14px 12px;
  padding-inline: 10px;
  text-align: center;
  transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease, background 0.15s ease;
}

.mondaishuu-card:hover {
  border-color: rgba(var(--v-theme-primary), 0.6);
  transform: translateY(-2px);
}

.mondaishuu-card:focus-visible {
  outline: 2px solid rgb(var(--v-theme-primary));
  outline-offset: 2px;
}

.mondaishuu-card__num {
  display: flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: rgba(var(--v-theme-primary), 0.12);
  block-size: 34px;
  color: rgb(var(--v-theme-primary));
  font-size: 1.05rem;
  font-weight: 700;
  inline-size: 34px;
  line-height: 1;
}

.mondaishuu-card__focus {
  display: -webkit-box;
  overflow: hidden;
  -webkit-box-orient: vertical;
  color: rgba(var(--v-theme-on-surface), 0.6);
  font-size: 0.7rem;
  -webkit-line-clamp: 2;
  line-height: 1.25;
}

@media (max-width: 599px) {
  .mondaishuu-grid {
    grid-template-columns: repeat(auto-fill, minmax(84px, 1fr));
  }

  .mondaishuu-card {
    min-block-size: 88px;
    padding-block: 10px 8px;
    padding-inline: 6px;
  }

  .mondaishuu-card__focus {
    display: none;
  }
}

@media (prefers-reduced-motion: reduce) {
  .mondaishuu-card { transition: none; }
  .mondaishuu-card:hover { transform: none; }
}

/* ---------- rangkuman / review sets (setup stage) ---------- */
.mondaishuu-review-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.mondaishuu-review-card {
  display: inline-flex;
  align-items: center;
  border: 1px solid rgba(var(--v-theme-secondary), 0.3);
  border-radius: 12px;
  background: rgba(var(--v-theme-secondary), 0.06);
  color: rgb(var(--v-theme-secondary));
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 600;
  gap: 8px;
  padding-block: 10px;
  padding-inline: 16px;
  transition: border-color 0.15s ease, background-color 0.15s ease, transform 0.15s ease;
}

.mondaishuu-review-card:hover {
  border-color: rgb(var(--v-theme-secondary));
  background: rgba(var(--v-theme-secondary), 0.12);
  transform: translateY(-1px);
}

.mondaishuu-review-card:focus-visible {
  outline: 2px solid rgb(var(--v-theme-secondary));
  outline-offset: 2px;
}

.mondaishuu-review-card__icon {
  opacity: 0.8;
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
