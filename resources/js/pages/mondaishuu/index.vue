<script setup>
// Mondaishuu — latihan soal bergaya kuis interaktif per pelajaran (1–25)
// plus 4 set rangkuman. Konten disusun ulang secara orisinal: pola dan
// cakupan tata bahasa tiap pelajaran mengikuti kurikulum Minna no Nihongo I,
// tapi semua kalimat/kosakata contoh ditulis sendiri (bukan hasil scan buku).
// Kanji memakai furigana lewat RubyText (markup 漢字《かんじ》), tanpa romaji,
// supaya latihannya benar-benar melatih baca tulis Jepang.
import RubyText from '@/components/learning/RubyText.vue'
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
    { type: 'mc', q: '（自分《じぶん》に 近《ちか》い 物《もの》）＿＿＿は 私《わたし》の 本《ほん》です。', choices: ['これ', 'それ', 'あれ', 'どれ'], answer: 0 },
    { type: 'mc', q: '（相手《あいて》に 近《ちか》い 物《もの》）＿＿＿は 辞書《じしょ》ですか。', choices: ['これ', 'それ', 'あれ', 'どなた'], answer: 1, img: 'tabler-book-2' },
    { type: 'mc', q: 'これは 私《わたし》＿＿＿ 傘《かさ》です。', choices: ['は', 'の', 'を', 'も'], answer: 1, img: 'tabler-umbrella' },
    { type: 'mc', q: 'あの 人《ひと》は＿＿＿ですか。（丁寧《ていねい》に「だれ」）', choices: ['どなた', 'どこ', 'なに', 'いつ'], answer: 0 },
    { type: 'mc', q: 'サントスさんは ブラジル人《じん》です。マリアさん＿＿＿ ブラジル人《じん》です。', choices: ['も', 'の', 'を', 'は'], answer: 0 },
    { type: 'mc', q: 'ミラーさんは 学生《がくせい》＿＿＿。（否定《ひてい》）', choices: ['じゃありません', 'ではします', 'ですか', 'も'], answer: 0 },
    { type: 'mc', q: '山田《やまだ》さんは 会社員《かいしゃいん》＿＿＿。', choices: ['です', 'ですか', 'でした', 'ましょう'], answer: 0 },
    { type: 'scramble', translate: 'Beliau itu siapa? (sopan)', words: ['あの', 'かた', 'は', 'どなた', 'ですか'] },
    { type: 'scramble', translate: 'Ya, saya mahasiswa.', words: ['はい', '、', '学生《がくせい》', 'です'] },
    { type: 'scramble', translate: 'Ini adalah kamus bahasa Jepang saya.', words: ['これ', 'は', '私《わたし》', 'の', '日本語《にほんご》', 'の', '辞書《じしょ》', 'です'] },
    { type: 'mc', q: 'これは 田中《たなか》さん＿＿＿ 傘《かさ》です。', choices: ['の', 'は', 'を', 'も'], answer: 0 },
    { type: 'mc', q: 'サントスさんは 学生《がくせい》ですか。…いいえ、学生《がくせい》＿＿＿。会社員《かいしゃいん》です。', choices: ['じゃありません', 'ですか', 'でした', 'も'], answer: 0 },
    { type: 'scramble', translate: 'Apakah Anda orang IMC?', words: ['IMC', 'の', '方《かた》', 'ですか'] },
    { type: 'scramble', translate: 'Ini bukan payung saya.', words: ['これ', 'は', '私《わたし》', 'の', '傘《かさ》', 'じゃありません'] }
  ] },
  { id: 2, focus: 'この・その・あの、ここ・そこ・あそこ、N の N', questions: [
    { type: 'mc', q: '＿＿＿ かばんは 私《わたし》のです。（自分《じぶん》に 近《ちか》い）', choices: ['この', 'その', 'あの', 'どの'], answer: 0, img: 'tabler-briefcase' },
    { type: 'mc', q: '受付《うけつけ》は＿＿＿ですか。（丁寧《ていねい》に「どこ」）', choices: ['ここ', 'そこ', 'どちら', 'どなた'], answer: 2 },
    { type: 'mc', q: 'これは＿＿＿の カメラですか。…日本《にほん》のです。', choices: ['どこ', 'なに', 'だれ', 'いつ'], answer: 0, img: 'tabler-camera' },
    { type: 'mc', q: 'この かばんは＿＿＿の ですか。…わたしのです。', choices: ['だれ', 'なに', 'どこ', 'いつ'], answer: 0 },
    { type: 'mc', q: 'エレベーターは＿＿＿ですか。…あそこです。', choices: ['どこ', 'だれ', 'なん', 'どなた'], answer: 0, img: 'tabler-building' },
    { type: 'mc', q: 'これは＿＿＿の 雑誌《ざっし》ですか。…車《くるま》の 雑誌《ざっし》です。', choices: ['なん', 'だれ', 'どこ', 'どの'], answer: 0 },
    { type: 'scramble', translate: 'Toilet ada di sana. (dekat lawan bicara)', words: ['トイレ', 'は', 'そこ', 'です'] },
    { type: 'scramble', translate: 'Guru bahasa Perancis', words: ['フランス語《フランスご》', 'の', '先生《せんせい》'] },
    { type: 'scramble', translate: 'Tas ini bukan tas saya.', words: ['この', 'かばん', 'は', '私《わたし》', 'の', 'じゃありません'] },
    { type: 'mc', q: '＿＿＿ 建物《たてもの》は 何《なん》ですか。（少《すこ》し 遠《とお》い）', choices: ['この', 'その', 'あの', 'どの'], answer: 2 },
    { type: 'mc', q: 'この 傘《かさ》は＿＿＿の ですか。…あなたのです。', choices: ['だれ', 'なん', 'どこ', 'いつ'], answer: 0 },
    { type: 'scramble', translate: 'Kamera itu buatan negara mana?', words: ['それ', 'は', 'どこ', 'の', 'カメラ', 'ですか'] }
  ] },
  { id: 3, focus: '数字《すうじ》（値段《ねだん》）、この・その・あの、どこの', questions: [
    { type: 'mc', q: '¥5,300 の 読《よ》み方《かた》は？', choices: ['ごせんさんびゃくえん', 'ごまんさんぜんえん', 'ごひゃくさんじゅうえん', 'ごせんさんじゅうえん'], answer: 0, img: 'tabler-tag' },
    { type: 'mc', q: '¥18,900 の 読《よ》み方《かた》は？', choices: ['いちまんはっせんきゅうひゃくえん', 'じゅうはちまんきゅうせんえん', 'いちまんはちひゃくきゅうじゅうえん', 'じゅうはっせんきゅうひゃくえん'], answer: 0, img: 'tabler-tag' },
    { type: 'mc', q: 'すみません、その 時計《とけい》を 見《み》せて＿＿＿。', choices: ['ください', 'くださいか', 'です', 'ました'], answer: 0, img: 'tabler-clock' },
    { type: 'mc', q: 'マリアさんの お国《くに》は＿＿＿ですか。…ブラジルです。', choices: ['どちら', 'どこの', 'だれの', 'なんの'], answer: 0 },
    { type: 'mc', q: 'これは どこ＿＿＿ ワインですか。…イタリアのです。', choices: ['の', 'は', 'を', 'に'], answer: 0 },
    { type: 'mc', q: 'カメラ売《う》り場《ば》は＿＿＿ですか。…3階《かい》です。', choices: ['なんかい', 'いくら', 'だれ', 'どなた'], answer: 0 },
    { type: 'scramble', translate: 'Jam ini buatan Swiss.', words: ['この', '時計《とけい》', 'は', 'スイス', 'の', 'です'] },
    { type: 'scramble', translate: 'Silakan lihat.', words: ['どうぞ', '、', '見《み》て', 'ください'] },
    { type: 'scramble', translate: 'Berapa harga tas ini?', words: ['この', 'かばん', 'は', 'いくら', 'ですか'] },
    { type: 'mc', q: '¥124,000 の 読《よ》み方《かた》は？', choices: ['じゅうにまんよんせんえん', 'いちまんにせんよんひゃくえん', 'じゅうにまんよんひゃくえん', 'ひゃくにじゅうよんまんえん'], answer: 0 },
    { type: 'mc', q: 'すみません。そのワイン＿＿＿ 見《み》せてください。', choices: ['を', 'は', 'に', 'の'], answer: 0 },
    { type: 'scramble', translate: 'Kalau begitu, saya ambil yang ini.', words: ['じゃ', '、', 'これ', 'を', 'ください'] }
  ] },
  { id: 4, focus: '時間《じかん》（〜時《じ》〜分《ふん》）、から・まで・に、休《やす》み', questions: [
    { type: 'mc', q: '3:15 の 読《よ》み方《かた》は？', choices: ['さんじじゅうごふん', 'さんじゅうごふん', 'さんじはん', 'じゅうごじさんぷん'], answer: 0, img: 'tabler-clock-hour-3' },
    { type: 'mc', q: '9:40 の 読《よ》み方《かた》は？', choices: ['くじよんじゅっぷん', 'きゅうじよんじゅうふん', 'くじよんじ', 'きゅうじじゅっぷん'], answer: 0, img: 'tabler-clock-hour-9' },
    { type: 'mc', q: '銀行《ぎんこう》は 9時《じ》＿＿＿3時《じ》＿＿＿です。', choices: ['から／まで', 'まで／から', 'に／で', 'を／に'], answer: 0 },
    { type: 'mc', q: '美術館《びじゅつかん》の 休《やす》みは 月曜日《げつようび》＿＿＿ 木曜日《もくようび》です。', choices: ['と', 'から', 'まで', 'に'], answer: 0 },
    { type: 'mc', q: '毎晩《まいばん》 11時半《じゅういちじはん》＿＿＿ 寝《ね》ます。', choices: ['に', 'で', 'を', 'へ'], answer: 0 },
    { type: 'mc', q: 'きょうは 何曜日《なんようび》ですか。…＿＿＿です。', choices: ['水曜日《すいようび》', '9時《じ》', '3月《がつ》', '休《やす》み'], answer: 0 },
    { type: 'scramble', translate: 'Setiap pagi saya bangun jam 6.', words: ['毎朝《まいあさ》', '6時《ろくじ》', 'に', '起《お》きます'] },
    { type: 'scramble', translate: 'Hari ini adalah hari libur.', words: ['きょう', 'は', '休《やす》み', 'です'] },
    { type: 'scramble', translate: 'Perpustakaan buka dari jam 9 sampai jam 6.', words: ['図書館《としょかん》', 'は', '9時《くじ》', 'から', '6時《ろくじ》', 'まで', 'です'] },
    { type: 'mc', q: 'ニューヨークは 今《いま》 午前《ごぜん》4時《じ》＿＿＿です。', choices: ['×', 'に', 'で', 'の'], answer: 0 },
    { type: 'mc', q: 'きのうの 晩《ばん》8時《じ》＿＿＿10時《じ》＿＿＿ 勉強《べんきょう》しました。', choices: ['から／まで', 'まで／から', 'に／で', 'を／へ'], answer: 0 },
    { type: 'scramble', translate: 'Toko itu buka dari jam 9 pagi sampai jam 8 malam.', words: ['あの', '店《みせ》', 'は', '朝《あさ》', '9時《くじ》', 'から', '夜《よる》', '8時《はちじ》', 'まで', 'です'] }
  ] },
  { id: 5, focus: '日《ひ》にち、過去形《かこけい》、へ・と', questions: [
    { type: 'mc', q: '5月《がつ》14日《じゅうよっか》の 読《よ》み方《かた》は？', choices: ['ごがつじゅうよっか', 'ごがついちよんにち', 'ごつきじゅうよんにち', 'ごがつじゅうよんにち'], answer: 0, img: 'tabler-calendar' },
    { type: 'mc', q: '9月《がつ》20日《はつか》の 読《よ》み方《かた》は？', choices: ['くがつはつか', 'きゅうがつにじゅうにち', 'くがつにじゅうび', 'くつきはつか'], answer: 0, img: 'tabler-calendar' },
    { type: 'mc', q: '友達《ともだち》＿＿＿ 映画《えいが》を 見《み》ました。', choices: ['と', 'へ', 'の', 'は'], answer: 0 },
    { type: 'mc', q: '先月《せんげつ》 日本《にほん》＿＿＿ 来《き》ました。', choices: ['へ', 'で', 'を', 'に「×」'], answer: 0 },
    { type: 'mc', q: '誕生日《たんじょうび》は 9月《がつ》＿＿＿ですか。…1日《ついたち》です。', choices: ['なんにち', 'いつ', 'なんじ', 'どこ'], answer: 0 },
    { type: 'mc', q: '一人《ひとり》＿＿＿ 行《い》きましたか。…友達《ともだち》と 行《い》きました。', choices: ['で', 'に', 'を', 'は'], answer: 0 },
    { type: 'scramble', translate: 'Kemarin saya pergi ke Kyoto.', words: ['きのう', '京都《きょうと》', 'へ', '行《い》きました'] },
    { type: 'scramble', translate: 'Tahun lalu saya datang ke Jepang.', words: ['去年《きょねん》', '日本《にほん》', 'へ', '来《き》ました'] },
    { type: 'scramble', translate: 'Kapan Anda kembali ke negara Anda?', words: ['いつ', '国《くに》', 'へ', '帰《かえ》りますか'] },
    { type: 'mc', q: '先月《せんげつ》の 25日《にじゅうごにち》＿＿＿ 電車《でんしゃ》＿＿＿ カリナさん＿＿＿ うち＿＿＿ 行《い》きました。', choices: ['に／で／と／へ', 'で／に／と／を', 'に／を／で／へ', 'を／で／に／へ'], answer: 0 },
    { type: 'scramble', translate: 'Minggu lalu hari Sabtu saya tidak pergi ke mana-mana.', words: ['先週《せんしゅう》', 'の', '土曜日《どようび》', 'どこも', '行《い》きませんでした'] }
  ] },
  { id: 6, focus: '動詞《どうし》ます形《けい》、を・に・で・と', questions: [
    { type: 'mc', q: '毎日《まいにち》コーヒー＿＿＿ 飲《の》みます。', choices: ['を', 'に', 'で', 'と'], answer: 0 },
    { type: 'mc', q: '公園《こうえん》＿＿＿ 散歩《さんぽ》します。', choices: ['を', 'に', 'で', 'へ'], answer: 2 },
    { type: 'mc', q: '先生《せんせい》＿＿＿ 日本語《にほんご》を 習《なら》います。', choices: ['に', 'を', 'で', 'と'], answer: 0 },
    { type: 'mc', q: '毎朝《まいあさ》パン＿＿＿ 卵《たまご》＿＿＿ 食《た》べます。', choices: ['と／を', 'を／と', 'に／で', 'の／を'], answer: 0, img: 'tabler-egg' },
    { type: 'mc', q: 'デパート＿＿＿ パン＿＿＿ 買《か》いました。', choices: ['で／を', 'を／で', 'に／を', 'へ／を'], answer: 0 },
    { type: 'mc', q: '3時《じ》＿＿＿ 教室《きょうしつ》＿＿＿ 日本語《にほんご》の 先生《せんせい》＿＿＿ 会《あ》います。', choices: ['に／で／に', 'で／に／を', 'を／に／で', 'に／を／で'], answer: 0 },
    { type: 'scramble', translate: 'Kemarin saya tidak belajar.', words: ['きのう', '、', '勉強《べんきょう》', 'しませんでした'] },
    { type: 'scramble', translate: 'Tidak, saya tidak merokok.', words: ['いいえ', '、', 'たばこ', 'を', '吸《す》いません'] },
    { type: 'scramble', translate: 'Ayo kita istirahat sebentar.', words: ['ちょっと', '、', '休《やす》みましょう'] },
    { type: 'mc', q: 'きのう 自転車《じてんしゃ》＿＿＿ スーパー＿＿＿ 行《い》きました。スーパー＿＿＿ 牛乳《ぎゅうにゅう》＿＿＿ 果物《くだもの》＿＿＿ 買《か》いました。', choices: ['で／へ／で／と／を', 'に／を／で／を／と', 'で／に／を／と／で', 'へ／で／に／を／と'], answer: 0 },
    { type: 'scramble', translate: 'Setiap hari saya makan siang jam 12 di kantin.', words: ['毎日《まいにち》', '12時《じゅうにじ》', 'に', '食堂《しょくどう》', 'で', '昼《ひる》ごはん', 'を', '食《た》べます'] }
  ] },
  { id: 7, focus: 'で（道具《どうぐ》）、に（相手《あいて》）、あげます・もらいます', questions: [
    { type: 'mc', q: '箸《はし》＿＿＿ ご飯《はん》を 食《た》べます。', choices: ['を', 'で', 'に', 'と'], answer: 1 },
    { type: 'mc', q: '誕生日《たんじょうび》に 友達《ともだち》＿＿＿ プレゼントを もらいました。', choices: ['に', 'を', 'で', 'へ'], answer: 0, img: 'tabler-gift' },
    { type: 'mc', q: '日本語《にほんご》＿＿＿ 「ありがとう」＿＿＿ 何《なん》ですか。', choices: ['で／は', 'は／で', 'を／に', 'に／を'], answer: 0 },
    { type: 'mc', q: 'わたしは 田中《たなか》先生《せんせい》＿＿＿ 日本語《にほんご》＿＿＿ 習《なら》いました。', choices: ['に／を', 'を／に', 'で／を', 'に／で'], answer: 0 },
    { type: 'mc', q: '山田《やまだ》さんは タワポンさんに 日本語《にほんご》を 教《おし》えました。タワポンさんは 山田《やまだ》さん＿＿＿ 日本語《にほんご》を 教《おし》えて＿＿＿。', choices: ['に／もらいました', 'に／あげました', 'を／もらいました', 'で／くれました'], answer: 0 },
    { type: 'scramble', translate: 'Saya memberikan bunga kepada Sari.', words: ['私《わたし》', 'は', 'サリさん', 'に', '花《はな》', 'を', 'あげました'] },
    { type: 'scramble', translate: 'Sari menerima bunga dari saya.', words: ['サリさん', 'は', '私《わたし》', 'に', '花《はな》', 'を', 'もらいました'] },
    { type: 'scramble', translate: 'Apakah Anda sudah mengirim laporan?', words: ['もう', 'レポート', 'を', '送《おく》りましたか'] },
    { type: 'mc', q: '何《なに》で この 魚《さかな》を 切《き》りますか。…ナイフ＿＿＿ 切《き》ります。', choices: ['で', 'を', 'に', 'と'], answer: 0, img: 'tabler-fish' },
    { type: 'scramble', translate: 'Saya mengirim foto keluarga ke Jepang.', words: ['家族《かぞく》', 'の', '写真《しゃしん》', 'を', '日本《にほん》', 'へ', '送《おく》りました'] }
  ] },
  { id: 8, focus: 'い形容詞《けいようし》・な形容詞《けいようし》', questions: [
    { type: 'mc', q: '「新《あたら》しい」の 反対《はんたい》は？', choices: ['古《ふる》い', '安《やす》い', '大《おお》きい', '静《しず》か'], answer: 0 },
    { type: 'mc', q: '「安《やす》い」の 反対《はんたい》は？', choices: ['高《たか》い', '低《ひく》い', '古《ふる》い', '暗《くら》い'], answer: 0 },
    { type: 'mc', q: '「静《しず》か」の 反対《はんたい》は？', choices: ['賑《にぎ》やか', '有名《ゆうめい》', '親切《しんせつ》', '暇《ひま》'], answer: 0 },
    { type: 'mc', q: '日本語《にほんご》は 難《むずか》しいですか。…いいえ、あまり＿＿＿。', choices: ['難《むずか》しくないです', '難《むずか》しいです', '難《むずか》しかったです', '難《むずか》しくて'], answer: 0 },
    { type: 'mc', q: 'カリナさんは（きれい・親切《しんせつ》）です。＿＿＿。', choices: ['きれいです。そして、親切《しんせつ》です', 'きれいで、親切《しんせつ》くて', 'きれいくて、親切《しんせつ》です', 'きれいだ、親切《しんせつ》だ'], answer: 0 },
    { type: 'mc', q: 'ワットさんは＿＿＿人《ひと》ですか。…すてきな 人《ひと》です。', choices: ['どんな', 'なんの', 'どこの', 'いつの'], answer: 0 },
    { type: 'scramble', translate: 'Beliau adalah guru yang ramah.', words: ['ワットさん', 'は', '親切《しんせつ》', 'な', '先生《せんせい》', 'です'] },
    { type: 'scramble', translate: 'Saya membeli tas yang baru.', words: ['新《あたら》しい', 'かばん', 'を', '買《か》いました'] },
    { type: 'scramble', translate: 'Restoran ini kecil tapi terkenal.', words: ['この', 'レストラン', 'は', '小《ちい》さい', 'です', 'が', '、', '有名《ゆうめい》', 'です'] },
    { type: 'mc', q: '会社《かいしゃ》の 寮《りょう》は 古《ふる》いです＿＿＿、きれいです。', choices: ['が', 'に', 'を', 'の'], answer: 0 },
    { type: 'scramble', translate: 'Apakah kehidupan di Jepang menyenangkan?', words: ['日本《にほん》', 'の', '生活《せいかつ》', 'は', '楽《たの》しいですか'] }
  ] },
  { id: 9, focus: '好《す》き・嫌《きら》い（が）、から（理由《りゆう》）', questions: [
    { type: 'mc', q: '私《わたし》は 魚《さかな》＿＿＿ 好《す》きです。', choices: ['が', 'を', 'に', 'は'], answer: 0, img: 'tabler-fish' },
    { type: 'mc', q: '暑《あつ》いです＿＿＿、窓《まど》を 開《あ》けます。', choices: ['から', 'が', 'ので', 'し'], answer: 0 },
    { type: 'mc', q: 'タイ語《ご》＿＿＿ わかりますか。…いいえ、全然《ぜんぜん》 分《わ》かりません。', choices: ['が', 'を', 'に', 'へ'], answer: 0 },
    { type: 'mc', q: '細《こま》かい お金《かね》＿＿＿ ありませんでした＿＿＿、佐藤《さとう》さん＿＿＿ 借《か》りました。', choices: ['が／から／に', 'を／し／で', 'は／と／を', 'が／し／を'], answer: 0 },
    { type: 'mc', q: 'あした 花見《はなみ》を しませんか。…すみません。友達《ともだち》＿＿＿ 約束《やくそく》＿＿＿ ありますから。', choices: ['と／が', 'に／を', 'の／に', 'と／を'], answer: 0 },
    { type: 'scramble', translate: 'Karena besok ada ujian, saya belajar.', words: ['あした', '試験《しけん》', 'が', 'あります', 'から', '、', '勉強《べんきょう》します'] },
    { type: 'scramble', translate: 'Saya tidak begitu suka sayur.', words: ['野菜《やさい》', 'が', 'あまり', '好《す》き', 'じゃありません'] },
    { type: 'scramble', translate: 'Kenapa Anda tidak makan sarapan?', words: ['どうして', '朝《あさ》ごはん', 'を', '食《た》べませんでしたか'] },
    { type: 'mc', q: 'どんな スポーツ＿＿＿ 好《す》きですか。…野球《やきゅう》＿＿＿ 好《す》きです。', choices: ['が／が', 'を／を', 'に／に', 'は／を'], answer: 0, img: 'tabler-ball-baseball' },
    { type: 'scramble', translate: 'Saya tidak begitu tidur semalam.', words: ['きのう', 'の', '晩《ばん》', 'あまり', '寝《ね》ませんでした'] }
  ] },
  { id: 10, focus: 'あります・います、上《うえ》・下《した》・中《なか》', questions: [
    { type: 'mc', q: '机《つくえ》の 上《うえ》に 本《ほん》が ＿＿＿。', choices: ['あります', 'います', 'です', 'ました'], answer: 0, img: 'tabler-book' },
    { type: 'mc', q: '庭《にわ》に 猫《ねこ》が ＿＿＿。', choices: ['あります', 'います', 'でした', 'ません'], answer: 1, img: 'tabler-cat' },
    { type: 'mc', q: '猫《ねこ》は 箱《はこ》の＿＿＿に います。', choices: ['中《なか》', '上《うえ》', '下《した》', '前《まえ》'], answer: 0 },
    { type: 'mc', q: '本屋《ほんや》は 郵便局《ゆうびんきょく》と 喫茶店《きっさてん》の＿＿＿に あります。', choices: ['間《あいだ》', '中《なか》', '上《うえ》', '外《そと》'], answer: 0 },
    { type: 'mc', q: '車《くるま》の 中《なか》に だれ＿＿＿ いますか。…だれ＿＿＿ いません。', choices: ['が／も', 'は／が', 'を／は', 'に／が'], answer: 0 },
    { type: 'mc', q: '牛乳《ぎゅうにゅう》は＿＿＿に ありますか。…冷蔵庫《れいぞうこ》に あります。', choices: ['どこ', 'だれ', 'なに', 'いつ'], answer: 0 },
    { type: 'scramble', translate: 'Kamar mandi tidak ada di lantai satu.', words: ['トイレ', 'は', '1階《いっかい》', 'に', 'ありません'] },
    { type: 'scramble', translate: 'Tidak ada siapa-siapa di taman.', words: ['庭《にわ》', 'に', 'だれも', 'いません'] },
    { type: 'scramble', translate: 'Anak kucing ada di bawah kursi.', words: ['猫《ねこ》', 'は', '椅子《いす》', 'の', '下《した》', 'に', 'います'] },
    { type: 'mc', q: 'このビル＿＿＿ 喫茶店《きっさてん》＿＿＿ ありますか。…はい、地下《ちか》1階《いっかい》＿＿＿ あります。', choices: ['に／が／に', 'は／を／で', 'で／が／を', 'に／を／で'], answer: 0 },
    { type: 'scramble', translate: 'Saya bertemu dengan Yi di depan stasiun.', words: ['駅《えき》', 'の', '前《まえ》', 'で', 'イーさん', 'に', '会《あ》いました'] }
  ] },
  { id: 11, focus: '助数詞《じょすうし》（〜つ・〜人《にん》・〜枚《まい》）', questions: [
    { type: 'mc', q: 'りんごを＿＿＿ ください。（4個《こ》）', choices: ['よっつ', 'よんつ', 'よにん', 'よんまい'], answer: 0 },
    { type: 'mc', q: '切手《きって》を＿＿＿ ください。（3枚《まい》）', choices: ['さんまい', 'さんにん', 'みっつ', 'さんこ'], answer: 0, img: 'tabler-stamp' },
    { type: 'mc', q: '家族《かぞく》は＿＿＿です。（5人《にん》）', choices: ['ごにん', 'いつつ', 'ごまい', 'ごだい'], answer: 0 },
    { type: 'mc', q: '店《みせ》の 前《まえ》に 自転車《じてんしゃ》が 20台《だい》＿＿＿ あります。', choices: ['ぐらい', 'ずつ', 'まで', 'しか'], answer: 0, img: 'tabler-bike' },
    { type: 'mc', q: '大阪《おおさか》＿＿＿ 東京《とうきょう》＿＿＿ 新幹線《しんかんせん》＿＿＿ 2時間半《にじかんはん》ぐらい＿＿＿ かかります。', choices: ['から／まで／で／×', 'まで／から／に／を', 'に／で／を／×', 'から／まで／に／で'], answer: 0 },
    { type: 'mc', q: '1週間《しゅうかん》＿＿＿ 2回《かい》 テニスを します。', choices: ['に', 'で', 'を', 'へ'], answer: 0 },
    { type: 'scramble', translate: 'Ada dua orang di depan pintu.', words: ['ドア', 'の', '前《まえ》', 'に', '二人《ふたり》', 'います'] },
    { type: 'scramble', translate: 'Tolong berikan 5 lembar perangko 80 yen.', words: ['80円《えん》', 'の', '切手《きって》', 'を', '5枚《まい》', 'ください'] },
    { type: 'scramble', translate: 'Saya belajar bahasa Jepang di negara saya selama 6 bulan.', words: ['国《くに》', 'で', '6か月《ろっかげつ》', 'ぐらい', '日本語《にほんご》', 'を', '勉強《べんきょう》しました'] },
    { type: 'mc', q: 'この 荷物《にもつ》、航空便《こうくうびん》＿＿＿ お願《ねが》いします。', choices: ['で', 'を', 'に', 'が'], answer: 0, img: 'tabler-plane' },
    { type: 'scramble', translate: 'Kakak laki-laki saya suka sepak bola.', words: ['兄《あに》', 'は', 'サッカー', 'が', '好《す》きです'] }
  ] },
  { id: 12, focus: '比較《ひかく》（より、の中《なか》で一番《いちばん》）', questions: [
    { type: 'mc', q: '地下鉄《ちかてつ》は バス＿＿＿ 速《はや》いです。', choices: ['より', 'から', 'まで', 'ほど'], answer: 0, img: 'tabler-train' },
    { type: 'mc', q: 'スポーツ＿＿＿ サッカーが 一番《いちばん》 おもしろいです。', choices: ['で', 'に', 'と', 'を'], answer: 0, img: 'tabler-ball-football' },
    { type: 'mc', q: '暑《あつ》い 季節《きせつ》＿＿＿ 寒《さむ》い 季節《きせつ》＿＿＿ どちら＿＿＿ いいですか。', choices: ['と／と／が', 'や／の／を', 'に／を／が', 'と／の／に'], answer: 0 },
    { type: 'mc', q: '桜《さくら》＿＿＿ 日本《にほん》の 花《はな》＿＿＿ いちばん 有名《ゆうめい》です。', choices: ['は／で', 'が／と', 'の／に', 'を／が'], answer: 0, img: 'tabler-flower' },
    { type: 'mc', q: '東京《とうきょう》は 大阪《おおさか》＿＿＿ 人《ひと》が 多《おお》いです。', choices: ['より', 'ほど', 'ので', 'でも'], answer: 0 },
    { type: 'scramble', translate: '1 tahun ini, Agustus yang paling panas.', words: ['1年《いちねん》', 'で', '8月《はちがつ》', 'が', '一番《いちばん》', '暑《あつ》い', 'です'] },
    { type: 'scramble', translate: 'Tokyo lebih besar daripada Osaka.', words: ['東京《とうきょう》', 'は', '大阪《おおさか》', 'より', '大《おお》きい', 'です'] },
    { type: 'scramble', translate: 'Saya paling suka masakan Jepang.', words: ['日本《にほん》', '料理《りょうり》', 'が', 'いちばん', '好《す》きです'] },
    { type: 'mc', q: '松本《まつもと》さん＿＿＿ 山田《やまだ》さん＿＿＿ どちら＿＿＿ ダンス＿＿＿ 上手《じょうず》ですか。', choices: ['と／と／が／が', 'や／の／を／に', 'に／を／が／と', 'と／の／に／が'], answer: 0 },
    { type: 'scramble', translate: 'Toko roti dekat stasiun lebih murah daripada department store.', words: ['駅《えき》', 'の', '前《まえ》', 'の', 'パン屋《や》', 'は', 'デパート', 'より', '安《やす》い', 'です'] }
  ] },
  { id: 13, focus: '欲《ほ》しい、〜たい、〜に行《い》きます', questions: [
    { type: 'mc', q: '私《わたし》は 新《あたら》しい パソコン＿＿＿ 欲《ほ》しいです。', choices: ['が', 'を', 'に', 'は'], answer: 0, img: 'tabler-device-laptop' },
    { type: 'mc', q: 'コーヒーを ＿＿＿です。（飲《の》みます→〜たい）', choices: ['飲《の》みたい', '飲《の》みます', '飲《の》みません', '飲《の》みたく'], answer: 0 },
    { type: 'mc', q: 'のど＿＿＿ かわきましたから、何《なに》＿＿＿ 飲《の》みたいです。', choices: ['が／か', 'を／が', 'に／を', 'は／か'], answer: 0 },
    { type: 'mc', q: '疲《つか》れましたから、何《なに》も＿＿＿。', choices: ['したくないです', 'したいです', 'します', 'しました'], answer: 0 },
    { type: 'mc', q: '今《いま》 何《なに》＿＿＿ 欲《ほ》しくないです。', choices: ['も', 'が', 'を', 'は'], answer: 0 },
    { type: 'scramble', translate: 'Saya pergi ke perpustakaan untuk meminjam buku.', words: ['図書館《としょかん》', 'へ', '本《ほん》', 'を', '借《か》りに', '行《い》きます'] },
    { type: 'scramble', translate: 'Saya ingin cepat bertemu keluarga.', words: ['早《はや》く', '家族《かぞく》', 'に', '会《あ》いたい', 'です'] },
    { type: 'scramble', translate: 'Minggu depan saya ingin pergi memancing.', words: ['来週《らいしゅう》', '釣《つ》り', 'に', '行《い》きたい', 'です'] },
    { type: 'mc', q: '夏休《なつやす》みに 北海道《ほっかいどう》＿＿＿ 旅行《りょこう》＿＿＿ 行《い》きます。', choices: ['へ／に', 'に／へ', 'で／を', 'を／に'], answer: 0, img: 'tabler-map' },
    { type: 'scramble', translate: 'Waktu dan uang, mana yang lebih Anda inginkan?', words: ['時間《じかん》', 'と', 'お金《かね》', 'と', 'どちら', 'が', '欲《ほ》しいですか'] }
  ] },
  { id: 14, focus: 'て形《けい》、〜てください', questions: [
    { type: 'mc', q: '「書《か》きます」の て形《けい》は？', choices: ['書《か》いて', '書《か》んで', '書《か》きて', '書《か》って'], answer: 0 },
    { type: 'mc', q: '「読《よ》みます」の て形《けい》は？', choices: ['読《よ》んで', '読《よ》いて', '読《よ》きて', '読《よ》って'], answer: 0 },
    { type: 'mc', q: '「来《き》ます」の て形《けい》は？', choices: ['来《き》て', '来《き》んで', '来《き》いて', '来《き》って'], answer: 0 },
    { type: 'mc', q: '「急《いそ》ぎます」の て形《けい》は？', choices: ['急《いそ》いで', '急《いそ》いて', '急《いそ》んで', '急《いそ》って'], answer: 0 },
    { type: 'mc', q: '「持《も》ちます」の て形《けい》は？', choices: ['持《も》って', '持《も》んで', '持《も》いて', '持《も》きて'], answer: 0 },
    { type: 'mc', q: 'すみませんが、もう 一《いち》度《ど》＿＿＿。（言《い》います→〜てください）', choices: ['言《い》ってください', '言《い》きてください', '言《い》んでください', '言《い》てください'], answer: 0 },
    { type: 'scramble', translate: 'Tolong buka jendela.', words: ['窓《まど》', 'を', '開《あ》けて', 'ください'] },
    { type: 'scramble', translate: 'Tolong beritahu nomor telepon Matsumoto.', words: ['松本《まつもと》さん', 'の', '電話《でんわ》番号《ばんごう》', 'を', '教《おし》えて', 'ください'] },
    { type: 'scramble', translate: 'Boleh saya pinjam gunting ini?', words: ['この', 'はさみ', 'を', '貸《か》して', 'ください'] },
    { type: 'mc', q: '暑《あつ》いですから、エアコンを＿＿＿。（つけます）', choices: ['つけてください', 'つけます', 'つけました', 'つける'], answer: 0 },
    { type: 'scramble', translate: 'Tolong kirimkan peta lewat email.', words: ['地図《ちず》', 'を', 'メール', 'で', '送《おく》って', 'ください'] }
  ] },
  { id: 15, focus: '〜ています（進行《しんこう》・状態《じょうたい》）', questions: [
    { type: 'mc', q: '今《いま》 新聞《しんぶん》を ＿＿＿。（読《よ》んでいます）', choices: ['読《よ》んでいます', '読《よ》みます', '読《よ》みました', '読《よ》む'], answer: 0, img: 'tabler-news' },
    { type: 'mc', q: 'どこに 住《す》んで＿＿＿か。', choices: ['います', 'あります', 'です', 'ました'], answer: 0 },
    { type: 'mc', q: '兄《あに》の 会社《かいしゃ》は 電気《でんき》製品《せいひん》を＿＿＿います。（作《つく》ります）', choices: ['作《つく》って', '作《つく》んで', '作《つく》きて', '作《つく》いて'], answer: 0 },
    { type: 'mc', q: 'カリナさんは 日本《にほん》の 古《ふる》い 美術《びじゅつ》を＿＿＿います。（知《し》ります）', choices: ['知《し》って', '知《し》んで', '知《し》きて', '知《し》いて'], answer: 0 },
    { type: 'mc', q: '教室《きょうしつ》で たばこを＿＿＿は いけません。', choices: ['吸《す》って', '吸《す》う', '吸《す》います', '吸《す》いた'], answer: 0 },
    { type: 'scramble', translate: 'Saya tinggal di Bandung.', words: ['バンドン', 'に', '住《す》んでいます'] },
    { type: 'scramble', translate: 'Apakah Anda punya kamus elektronik?', words: ['電子《でんし》辞書《じしょ》', 'を', '持《も》っていますか'] },
    { type: 'scramble', translate: 'Saya tidak tahu cara membuat tempura.', words: ['てんぷら', 'の', '作《つく》り方《かた》', 'を', '知《し》りません'] },
    { type: 'mc', q: '弟《おとうと》は 結婚《けっこん》して＿＿＿。独身《どくしん》です。', choices: ['いません', 'います', 'でした', 'ました'], answer: 0 },
    { type: 'scramble', translate: 'Apakah Anda mengetahui alamat Santos?', words: ['サントスさん', 'の', '住所《じゅうしょ》', 'を', '知《し》っていますか'] }
  ] },
  { id: 16, focus: 'て形《けい》の 連続《れんぞく》（〜て、〜てから）', questions: [
    { type: 'mc', q: '大学《だいがく》を 出《で》て＿＿＿、会社《かいしゃ》に 入《はい》りました。', choices: ['から', 'まで', 'ので', 'し'], answer: 0 },
    { type: 'mc', q: '窓《まど》を＿＿＿、電気《でんき》を＿＿＿、事務所《じむしょ》を 出《で》ました。', choices: ['閉《し》めて／消《け》して', '閉《し》めます／消《け》します', '閉《し》めた／消《け》した', '閉《し》める／消《け》す'], answer: 0 },
    { type: 'mc', q: '仕事《しごと》が 終《お》わって＿＿＿、1時間《いちじかん》ぐらい プールで 泳《およ》ぎます。', choices: ['から', 'まで', 'ので', 'し'], answer: 0 },
    { type: 'mc', q: '「多《おお》い」の 反対《はんたい》は？', choices: ['少《すく》ない', '軽《かる》い', '暗《くら》い', '狭《せま》い'], answer: 0 },
    { type: 'mc', q: '「入《はい》ります」の 反対《はんたい》は？', choices: ['出《で》ます', '座《すわ》ります', '立《た》ちます', '閉《し》めます'], answer: 0 },
    { type: 'scramble', translate: 'Setelah mengerjakan PR, saya menonton TV.', words: ['宿題《しゅくだい》', 'を', 'して', 'から', '、', 'テレビ', 'を', '見《み》ます'] },
    { type: 'scramble', translate: 'Tutup jendela, lalu keluar ruangan.', words: ['窓《まど》', 'を', '閉《し》めて', '、', '部屋《へや》', 'を', '出《で》ます'] },
    { type: 'scramble', translate: 'Kanji itu sulit cara membacanya.', words: ['漢字《かんじ》', 'は', '読《よ》み方《かた》', 'が', '難《むずか》しい', 'です'] },
    { type: 'mc', q: '地下鉄《ちかてつ》で 大阪《おおさか》まで＿＿＿、JRに 乗《の》り換《か》えてください。', choices: ['行《い》って', '行《い》きます', '行《い》った', '行《い》く'], answer: 0 },
    { type: 'scramble', translate: 'Saya turun dari bus lalu berjalan ke rumah Tanaka.', words: ['バス', 'を', '降《お》りて', '、', '田中《たなか》さん', 'の', 'うち', 'まで', '歩《ある》いて', '行《い》きました'] }
  ] },
  { id: 17, focus: '〜なければなりません／〜なくてもいいです／〜ないでください', questions: [
    { type: 'mc', q: '明日《あした》は 早《はや》く 起《お》き＿＿＿。（義務《ぎむ》）', choices: ['なければなりません', 'なくてもいいです', 'ないでください', 'てください'], answer: 0 },
    { type: 'mc', q: 'あした 心配《しんぱい》し＿＿＿。（しなくてもいい）', choices: ['なくてもいいです', 'なければなりません', 'ないでください', 'ています'], answer: 0 },
    { type: 'mc', q: '暗証《あんしょう》番号《ばんごう》は 大切《たいせつ》ですから、＿＿＿。', choices: ['忘《わす》れないでください', '忘《わす》れてください', '忘《わす》れなければなりません', '忘《わす》れています'], answer: 0 },
    { type: 'mc', q: 'この 本《ほん》は 来週《らいしゅう》の 火曜日《かようび》＿＿＿に 返《かえ》さなければ なりません。', choices: ['まで', 'までに', 'から', 'に「×」'], answer: 1 },
    { type: 'mc', q: '危《あぶ》ないですから、うしろから＿＿＿。', choices: ['押《お》さないでください', '押《お》してください', '押《お》しました', '押《お》します'], answer: 0 },
    { type: 'scramble', translate: 'Jangan lupa kata sandi.', words: ['暗証番号《あんしょうばんごう》', 'を', '忘《わす》れないで', 'ください'] },
    { type: 'scramble', translate: 'Apakah hari Minggu juga harus bangun pagi?', words: ['日曜日《にちようび》', 'も', '早《はや》く', '起《お》きなければ', 'なりませんか'] },
    { type: 'scramble', translate: 'Nomor telepon tidak perlu ditulis.', words: ['電話《でんわ》番号《ばんごう》', 'は', '書《か》かなくても', 'いいです'] },
    { type: 'mc', q: '両親《りょうしん》が 来《き》ますから、空港《くうこう》へ 迎《むか》えに＿＿＿。', choices: ['行《い》かなければなりません', '行《い》かなくてもいいです', '行《い》かないでください', '行《い》っています'], answer: 0 },
    { type: 'scramble', translate: 'Karena ujian mudah, tidak perlu khawatir.', words: ['試験《しけん》', 'は', '簡単《かんたん》', 'です', 'から', '、', '心配《しんぱい》', 'しなくても', 'いいです'] }
  ] },
  { id: 18, focus: '〜ことができます、辞書形《じしょけい》', questions: [
    { type: 'mc', q: '「食《た》べます」の 辞書《じしょ》形《けい》は？', choices: ['食《た》べる', '食《た》べて', '食《た》べます', '食《た》べた'], answer: 0 },
    { type: 'mc', q: '「話《はな》します」の 辞書《じしょ》形《けい》は？', choices: ['話《はな》す', '話《はな》して', '話《はな》した', '話《はな》します'], answer: 0 },
    { type: 'mc', q: '「来《き》ます」の 辞書《じしょ》形《けい》は？', choices: ['来《く》る', '来《き》て', '来《き》た', '来《き》ない'], answer: 0 },
    { type: 'mc', q: 'この 図書館《としょかん》の 本《ほん》は 2週間《にしゅうかん》＿＿＿ こと＿＿＿ できます。（借《か》ります）', choices: ['借《か》りる／が', '借《か》りて／を', '借《か》りた／に', '借《か》り／で'], answer: 0 },
    { type: 'mc', q: '漢字《かんじ》が 分《わ》かりませんから、日本語《にほんご》の 新聞《しんぶん》を＿＿＿。', choices: ['読《よ》むことができません', '読《よ》みたいです', '読《よ》んでいます', '読《よ》んでください'], answer: 0 },
    { type: 'scramble', translate: 'Saya bisa berenang 50 meter.', words: ['50メートル', '泳《およ》ぐ', 'こと', 'が', 'できます'] },
    { type: 'scramble', translate: 'Hobi saya adalah memotret bunga.', words: ['私《わたし》', 'の', '趣味《しゅみ》', 'は', '花《はな》', 'の', '写真《しゃしん》', 'を', '撮《と》る', 'こと', 'です'] },
    { type: 'scramble', translate: 'Sebelum ke Jepang, apakah Anda belajar bahasa Jepang?', words: ['日本《にほん》', 'へ', '来《く》る', 'まえに', '、', '日本語《にほんご》', 'を', '勉強《べんきょう》しましたか'] },
    { type: 'mc', q: 'ここは 朝《あさ》10時《じ》から 見学《けんがく》する ＿＿＿ が できます。', choices: ['こと', 'もの', 'とき', 'ところ'], answer: 0 },
    { type: 'scramble', translate: 'Sepuluh tahun lalu saya belajar bahasa Prancis, tapi sudah lupa.', words: ['10年《じゅうねん》', 'まえに', '、', 'フランス語《ご》', 'を', '習《なら》いましたが', '、', 'もう', '忘《わす》れました'] }
  ] },
  { id: 19, focus: '〜たことがあります（経験《けいけん》）、〜たり〜たり', questions: [
    { type: 'mc', q: '富士山《ふじさん》に 登《のぼ》った ＿＿＿ が あります。', choices: ['こと', 'もの', 'とき', 'ところ'], answer: 0, img: 'tabler-mountain' },
    { type: 'mc', q: '相撲《すもう》を＿＿＿ ことが ありますか。…いいえ、一度《いちど》も ありません。', choices: ['見《み》た', '見《み》る', '見《み》て', '見《み》ます'], answer: 0 },
    { type: 'mc', q: 'この 果物《くだもの》を 食《た》べた ことが ありますか。…いいえ、＿＿＿です。', choices: ['初《はじ》めて', 'もう一度《いちど》', 'なかなか', 'だんだん'], answer: 0 },
    { type: 'mc', q: '休《やす》みの 日《ひ》は 本《ほん》を＿＿＿り、テレビを＿＿＿り します。', choices: ['読《よ》んだ／見《み》た', '読《よ》む／見《み》る', '読《よ》んで／見《み》て', '読《よ》み／見《み》'], answer: 0 },
    { type: 'mc', q: '子《こ》どもは お酒《さけ》を＿＿＿り、たばこを＿＿＿り しては いけません。', choices: ['飲《の》んだ／吸《す》った', '飲《の》む／吸《す》う', '飲《の》んで／吸《す》って', '飲《の》み／吸《す》い'], answer: 0 },
    { type: 'scramble', translate: 'Akhir pekan saya membaca buku dan menonton TV.', words: ['本《ほん》', 'を', '読《よ》んだり', '、', 'テレビ', 'を', '見《み》たり', 'します'] },
    { type: 'scramble', translate: 'Apakah Anda pernah menulis surat dengan bahasa Jepang?', words: ['日本語《にほんご》', 'で', '手紙《てがみ》', 'を', '書《か》いた', 'こと', 'が', 'ありますか'] },
    { type: 'scramble', translate: 'Bahasa Jepang saya menjadi semakin lancar.', words: ['日本語《にほんご》', 'が', 'だんだん', '上手《じょうず》', 'に', 'なりました'] },
    { type: 'mc', q: '何《なん》回《かい》ぐらい ディズニーランドへ 行《い》った ことが＿＿＿か。', choices: ['あります', 'います', 'でした', 'ました'], answer: 0, img: 'tabler-ferris-wheel' },
    { type: 'scramble', translate: 'Saya sudah pernah naik ke Gunung Fuji sekali.', words: ['富士山《ふじさん》', 'に', '一度《いちど》', '登《のぼ》った', 'こと', 'が', 'あります'] }
  ] },
  { id: 20, focus: '普通形《ふつうけい》（会話《かいわ》で使《つか》う形《かたち》）', questions: [
    { type: 'mc', q: '「分《わ》かりますか」の 普通《ふつう》形《けい》は？', choices: ['分《わ》かる？', '分《わ》かります？', '分《わ》かった？', '分《わ》かって？'], answer: 0 },
    { type: 'mc', q: '「行《い》きませんでした」の 普通《ふつう》形《けい》は？', choices: ['行《い》かなかった', '行《い》かない', '行《い》った', '行《い》かなくて'], answer: 0 },
    { type: 'mc', q: '「休《やす》みでした」の 普通《ふつう》形《けい》は？', choices: ['休《やす》みだった', '休《やす》みじゃない', '休《やす》み', '休《やす》みでは'], answer: 0 },
    { type: 'mc', q: '「欲《ほ》しくないです」の 普通《ふつう》形《けい》は？', choices: ['欲《ほ》しくない', '欲《ほ》しかった', '欲《ほ》しくて', '欲《ほ》しいだ'], answer: 0 },
    { type: 'mc', q: '英語《えいご》が 分《わ》かる？…うん、＿＿＿。', choices: ['できる', 'できます', 'できた', 'できて'], answer: 0 },
    { type: 'scramble', translate: 'Apakah kamu tahu alamat Karina?', words: ['カリナさん', 'の', '住所《じゅうしょ》', 'を', '知《し》っている？'] },
    { type: 'scramble', translate: 'Cuaca kemarin bagus.', words: ['きのう', 'は', '天気《てんき》', 'が', 'よかった'] },
    { type: 'mc', q: '「便利《べんり》です」の 普通《ふつう》形《けい》は？', choices: ['便利《べんり》だ', '便利《べんり》い', '便利《べんり》じゃ', '便利《べんり》な'], answer: 0 },
    { type: 'scramble', translate: 'Apakah kamu pergi berenang di laut Jepang?', words: ['日本《にほん》', 'の', '海《うみ》', 'で', '泳《およ》いだ', 'こと', 'が', 'ある？'] }
  ] },
  { id: 21, focus: '〜と思《おも》います、〜と言《い》いました、でしょう', questions: [
    { type: 'mc', q: '明日《あした》 雨《あめ》が 降《ふ》る＿＿＿ 思《おも》います。', choices: ['と', 'が', 'の', 'を'], answer: 0, img: 'tabler-cloud-rain' },
    { type: 'mc', q: '図書館《としょかん》は きょう 休《やす》みですか。…いいえ、休《やす》みじゃない＿＿＿ 思《おも》います。', choices: ['と', 'が', 'の', 'を'], answer: 0 },
    { type: 'mc', q: 'ミラーさんは お酒《さけ》を＿＿＿でしょう？…ええ、飲《の》みます。', choices: ['飲《の》む', '飲《の》みます', '飲《の》みました', '飲《の》んで'], answer: 0 },
    { type: 'mc', q: 'B：スキーに 行《い》きます。→ Bさんは スキーに 行《い》く＿＿＿ 言《い》いました。', choices: ['と', 'に', 'を', 'が'], answer: 0 },
    { type: 'mc', q: 'この 資料《しりょう》は 役《やく》に 立《た》ちますか。…ええ、とても 役《やく》に 立《た》つ＿＿＿ 思《おも》います。', choices: ['と', 'が', 'の', 'は'], answer: 0 },
    { type: 'scramble', translate: 'Dia bilang akan datang jam sepuluh.', words: ['彼《かれ》', 'は', '10時《じゅうじ》', 'に', '来《く》る', 'と', '言《い》いました'] },
    { type: 'scramble', translate: 'Apakah bank hari Minggu libur ya?', words: ['銀行《ぎんこう》', 'は', '日曜日《にちようび》', '休《やす》み', 'でしょう？'] },
    { type: 'scramble', translate: 'Saya rasa mungkin akan ada rapat minggu depan.', words: ['来週《らいしゅう》', '会議《かいぎ》', 'が', 'ある', 'と', '思《おも》います'] },
    { type: 'mc', q: '病気《びょうき》の 友達《ともだち》に 何《なん》＿＿＿ 言《い》いますか。', choices: ['と', 'が', 'を', 'の'], answer: 0 },
    { type: 'scramble', translate: 'Saya rasa Iwan orang yang pandai.', words: ['イーさん', 'は', '頭《あたま》', 'が', 'いい', 'と', '思《おも》います'] }
  ] },
  { id: 22, focus: '名詞《めいし》を 修飾《しゅうしょく》する 文《ぶん》', questions: [
    { type: 'mc', q: '「赤《あか》い 帽子《ぼうし》を かぶっている 人《ひと》」の 意味《いみ》は？', choices: ['Orang yang memakai topi merah', 'Orang yang menjual topi merah', 'Topi merah orang itu', 'Orang yang membeli topi'], answer: 0, img: 'tabler-hat' },
    { type: 'mc', q: '私《わたし》が＿＿＿所《ところ》は 横浜《よこはま》です。（生《う》まれます）', choices: ['生《う》まれた', '生《う》まれます', '生《う》まれて', '生《う》まれる'], answer: 0 },
    { type: 'mc', q: 'あの 赤《あか》い コートを＿＿＿人《ひと》は だれですか。（着《き》ます）', choices: ['着《き》ている', '着《き》る', '着《き》た', '着《き》て'], answer: 0 },
    { type: 'mc', q: '安《やす》い パソコンを＿＿＿店《みせ》を 知《し》っていますか。（売《う》ります）', choices: ['売《う》っている', '売《う》る', '売《う》った', '売《う》って'], answer: 0 },
    { type: 'mc', q: '旅行《りょこう》に＿＿＿人《ひと》は＿＿＿ですか。…15人《にん》です。', choices: ['行《い》く／なんにん', '行《い》った／どこ', '行《い》って／なに', '行《い》き／いつ'], answer: 0 },
    { type: 'scramble', translate: 'Buku yang saya beli kemarin', words: ['きのう', '買《か》った', '本《ほん》'] },
    { type: 'scramble', translate: 'Orang yang sedang membaca koran adalah Wang.', words: ['新聞《しんぶん》', 'を', '読《よ》んでいる', '人《ひと》', 'は', 'ワンさん', 'です'] },
    { type: 'scramble', translate: 'Apakah punya waktu untuk membaca koran tiap pagi?', words: ['朝《あさ》', '新聞《しんぶん》', 'を', '読《よ》む', '時間《じかん》', 'が', 'ありますか'] },
    { type: 'mc', q: '妹《いもうと》さんが＿＿＿部屋《へや》の 家賃《やちん》は＿＿＿ですか。（借《か》ります）', choices: ['借《か》りている／いくら', '借《か》りる／だれ', '借《か》りた／なに', '借《か》りて／どこ'], answer: 0 },
    { type: 'scramble', translate: 'Orang yang membuat air enak ini adalah kakak saya.', words: ['おいしい', '水《みず》', 'を', '作《つく》っている', 'の', 'は', '兄《あに》', 'です'] }
  ] },
  { id: 23, focus: '〜とき（〜する時《とき》／〜した時《とき》）', questions: [
    { type: 'mc', q: '疲《つか》れた＿＿＿、休《やす》みます。', choices: ['とき', 'こと', 'もの', 'ので'], answer: 0 },
    { type: 'mc', q: '小《ちい》さい 字《じ》を＿＿＿とき、眼鏡《めがね》を かけます。', choices: ['読《よ》む', '読《よ》んだ', '読《よ》んで', '読《よ》み'], answer: 0 },
    { type: 'mc', q: '初《はじ》めて 富士山《ふじさん》を＿＿＿とき、きれいな 山《やま》だと 思《おも》いました。', choices: ['見《み》た', '見《み》る', '見《み》て', '見《み》ます'], answer: 0 },
    { type: 'mc', q: 'セーターを＿＿＿と、寒《さむ》いです。', choices: ['脱《ぬ》ぐ', '脱《ぬ》いだ', '脱《ぬ》いで', '脱《ぬ》ぎ'], answer: 0 },
    { type: 'mc', q: 'お酒《さけ》を 飲《の》む＿＿＿、車《くるま》を 運転《うんてん》すると、危《あぶ》ないです。', choices: ['と', 'とき', 'から', 'し'], answer: 0 },
    { type: 'scramble', translate: 'Waktu kecil, saya suka menggambar.', words: ['子供《こども》', 'の', 'とき', '、', '絵《え》', 'を', '描《か》くの', 'が', '好《す》きでした'] },
    { type: 'scramble', translate: 'Waktu tidak tahu jalan, saya naik taksi.', words: ['道《みち》', 'が', 'わからない', 'とき', '、', 'タクシー', 'に', '乗《の》ります'] },
    { type: 'scramble', translate: 'Kalau menekan tombol ini, tiket akan keluar.', words: ['ここ', 'を', '押《お》すと', '、', '切符《きっぷ》', 'が', '出《で》ます'] },
    { type: 'mc', q: 'この 歌《うた》を＿＿＿と、家族《かぞく》を 思《おも》い出《だ》します。', choices: ['聞《き》く', '聞《き》いた', '聞《き》いて', '聞《き》き'], answer: 0 },
    { type: 'scramble', translate: 'Waktu ke rumah sakit, bawalah kartu asuransi.', words: ['病院《びょういん》', 'へ', '行《い》くとき', '、', '保険証《ほけんしょう》', 'を', '持《も》って', '行《い》きます'] }
  ] },
  { id: 24, focus: 'あげます・もらいます・くれます（人《ひと》のために）', questions: [
    { type: 'mc', q: '母《はは》は 私《わたし》に 傘《かさ》を ＿＿＿。（母《はは》→私《わたし》）', choices: ['くれました', 'あげました', 'もらいました', 'でした'], answer: 0, img: 'tabler-umbrella' },
    { type: 'mc', q: '私《わたし》は リナさんに 花《はな》を ＿＿＿。（私《わたし》→リナ）', choices: ['あげました', 'くれました', 'もらいました', 'です'], answer: 0 },
    { type: 'mc', q: 'このコピー、全部《ぜんぶ》一人《ひとり》でしましたか。…いいえ、カリナさんに＿＿＿もらいました。（手伝《てつだ》います）', choices: ['手伝《てつだ》って', '手伝《てつだ》う', '手伝《てつだ》った', '手伝《てつだ》い'], answer: 0 },
    { type: 'mc', q: '木村《きむら》さんは あした 奈良《なら》を 案内《あんない》しますよ。→ わたしは 木村《きむら》さんに 奈良《なら》を 案内《あんない》して＿＿＿。', choices: ['もらいます', 'あげます', 'くれます', 'です'], answer: 0 },
    { type: 'mc', q: 'すてきな セーターですね。どこで 買《か》いましたか。…母《はは》が＿＿＿。（作《つく》ります）', choices: ['作《つく》ってくれました', '作《つく》ってもらいました', '作《つく》ってあげました', '作《つく》りました'], answer: 0 },
    { type: 'scramble', translate: 'Saya diberitahu (dibantu) oleh Sato tentang cara membuat sukiyaki.', words: ['佐藤《さとう》さん', 'に', 'すき焼《や》き', 'の', '作《つく》り方《かた》', 'を', '教《おし》えてもらいました'] },
    { type: 'scramble', translate: 'Guru Kobayashi mengajari saya bahasa Jepang.', words: ['小林《こばやし》先生《せんせい》', 'は', '日本語《にほんご》', 'を', '教《おし》えて', 'くれました'] },
    { type: 'mc', q: '一人《ひとり》で 病院《びょういん》へ 行《い》きましたか。…いいえ、山田《やまだ》さんに＿＿＿。', choices: ['いっしょに行《い》ってもらいました', 'いっしょに行《い》ってあげました', '行《い》きました', '行《い》っています'], answer: 0 },
    { type: 'scramble', translate: 'Waktu ada teman asing datang, saya akan pandu tempat wisata di negara saya.', words: ['外国《がいこく》', 'から', '友達《ともだち》', 'が', '来《き》たとき', '、', '国《くに》', 'の', 'いい', '所《ところ》', 'を', '案内《あんない》してあげます'] }
  ] },
  { id: 25, focus: '〜たら（条件《じょうけん》）、〜ても', questions: [
    { type: 'mc', q: '時間《じかん》が あっ＿＿＿、寄《よ》ってください。', choices: ['たら', 'ても', 'から', 'ので'], answer: 0 },
    { type: 'mc', q: 'いい 大学《だいがく》に＿＿＿ら、頑張《がんば》らなければ なりません。', choices: ['入《はい》った', '入《はい》る', '入《はい》って', '入《はい》り'], answer: 0 },
    { type: 'mc', q: '漢字《かんじ》が＿＿＿ら、ひらがなで 書《か》いても いいですか。', choices: ['分《わ》からなかった', '分《わ》からない', '分《わ》からなくて', '分《わ》かって'], answer: 0 },
    { type: 'mc', q: 'ゆっくり 歩《ある》いて＿＿＿、駅《えき》まで 15分《ふん》ぐらいです。', choices: ['も', 'たら', 'から', 'ので'], answer: 0 },
    { type: 'mc', q: '簡単《かんたん》な 漢字《かんじ》＿＿＿も、なかなか 覚《おぼ》える ことが できません。', choices: ['でも', 'たら', 'から', 'ので'], answer: 0 },
    { type: 'scramble', translate: 'Meski hujan, saya tetap pergi.', words: ['雨《あめ》', 'が', '降《ふ》っても', '、', '行《い》きます'] },
    { type: 'scramble', translate: 'Kalau mendapat waktu libur, saya ingin bepergian.', words: ['休《やす》み', 'が', 'あったら', '、', '旅行《りょこう》', 'したい', 'です'] },
    { type: 'scramble', translate: 'Kalau tidak mengerti walau sudah dicari, tanyakan pada guru.', words: ['調《しら》べても', '、', '分《わ》からなかったら', '、', '先生《せんせい》', 'に', '聞《き》きます'] },
    { type: 'mc', q: '安《やす》くても、要《い》らない 物《もの》は＿＿＿。', choices: ['買《か》いません', '買《か》います', '買《か》った', '買《か》って'], answer: 0 },
    { type: 'scramble', translate: 'Kalau sudah tua dan pensiun, saya ingin berkeliling dunia.', words: ['年《とし》', 'を', '取《と》って', '、', '仕事《しごと》', 'を', 'やめたら', '、', '世界《せかい》', 'を', '旅行《りょこう》したい', 'です'] }
  ] },
]

const REVIEWS = [
  { id: 'r1-8', label: 'まとめ 1〜8', questions: [
    { type: 'mc', q: 'これは 私《わたし》＿＿＿ かばんです。', choices: ['の', 'は', 'を', 'に'], answer: 0 },
    { type: 'mc', q: '毎朝《まいあさ》 6時《ろくじ》＿＿＿ 起《お》きます。', choices: ['に', 'で', 'を', 'へ'], answer: 0 },
    { type: 'mc', q: '「安《やす》い」の 反対《はんたい》は？', choices: ['高《たか》い', '大《おお》きい', '新《あたら》しい', '静《しず》か'], answer: 0 },
    { type: 'scramble', translate: 'Kemarin saya pergi ke Bandung.', words: ['きのう', 'バンドン', 'へ', '行《い》きました'] },
    { type: 'mc', q: 'このかばんは＿＿＿ですか。…8,300円《えん》です。', choices: ['いくら', 'だれの', 'なんじ', 'どこ'], answer: 0 },
    { type: 'mc', q: '銀行《ぎんこう》は 9時《じ》＿＿＿ 3時《じ》＿＿＿です。', choices: ['から／まで', 'まで／から', 'に／で', 'を／へ'], answer: 0 },
    { type: 'mc', q: '「静《しず》か」の 反対《はんたい》は？', choices: ['賑《にぎ》やか', '有名《ゆうめい》', '暇《ひま》', '親切《しんせつ》'], answer: 0 },
    { type: 'scramble', translate: 'Saya belajar bahasa Jepang bersama Karina.', words: ['カリナさん', 'と', '日本語《にほんご》', 'を', '勉強《べんきょう》します'] },
  ] },
  { id: 'r9-17', label: 'まとめ 9〜17', questions: [
    { type: 'mc', q: '私《わたし》は 果物《くだもの》＿＿＿ 好《す》きです。', choices: ['が', 'を', 'に', 'へ'], answer: 0, img: 'tabler-apple' },
    { type: 'mc', q: '猫《ねこ》は 椅子《いす》の＿＿＿に います。', choices: ['下《した》', '上《うえ》', '中《なか》', '前《まえ》'], answer: 0 },
    { type: 'mc', q: '「飲《の》みます」の て形《けい》は？', choices: ['飲《の》んで', '飲《の》みて', '飲《の》いて', '飲《の》って'], answer: 0 },
    { type: 'scramble', translate: 'Saya harus belajar setiap hari.', words: ['毎日《まいにち》', '勉強《べんきょう》', 'しなければなりません'] },
    { type: 'mc', q: '地下鉄《ちかてつ》は バス＿＿＿ 速《はや》いです。', choices: ['より', 'から', 'まで', 'ほど'], answer: 0 },
    { type: 'mc', q: '窓《まど》を＿＿＿ください。（開《あ》けます）', choices: ['開《あ》けて', '開《あ》けます', '開《あ》けた', '開《あ》ける'], answer: 0 },
    { type: 'scramble', translate: 'Setelah selesai bekerja, saya pergi ke perpustakaan untuk meminjam buku.', words: ['仕事《しごと》', 'が', '終《お》わってから', '、', '本《ほん》', 'を', '借《か》りに', '図書館《としょかん》', 'へ', '行《い》きます'] },
  ] },
  { id: 'r18-25', label: 'まとめ 18〜25', questions: [
    { type: 'mc', q: '「泳《およ》ぎます」の 辞書《じしょ》形《けい》は？', choices: ['泳《およ》ぐ', '泳《およ》いで', '泳《およ》ぎて', '泳《およ》いた'], answer: 0 },
    { type: 'mc', q: '明日《あした》 雨《あめ》が 降《ふ》る＿＿＿ 思《おも》います。', choices: ['と', 'が', 'の', 'を'], answer: 0 },
    { type: 'scramble', translate: 'Ketika lelah, saya tidur cepat.', words: ['疲《つか》れた', 'とき', '、', '早《はや》く', '寝《ね》ます'] },
    { type: 'scramble', translate: 'Kalau ada waktu, datanglah main.', words: ['時間《じかん》', 'が', 'あったら', '、', '遊《あそ》びに', '来《き》てください'] },
    { type: 'mc', q: '私《わたし》は 車《くるま》の 運転《うんてん》が ＿＿＿。（できます）', choices: ['できます', 'します', 'あります', 'います'], answer: 0 },
    { type: 'mc', q: '母《はは》は 私《わたし》に セーターを＿＿＿。（母《はは》→私《わたし》）', choices: ['くれました', 'あげました', 'もらいました', 'でした'], answer: 0 },
    { type: 'scramble', translate: 'Buku yang sedang dibaca oleh Wang itu menarik.', words: ['ワンさん', 'が', '読《よ》んでいる', '本《ほん》', 'は', 'おもしろい', 'です'] },
  ] },
  { id: 'r1-25', label: 'まとめ 1〜25', questions: [
    { type: 'mc', q: 'あの 方《かた》は＿＿＿ですか。（丁寧《ていねい》に「だれ」）', choices: ['どなた', 'どちら', 'どこ', 'なに'], answer: 0 },
    { type: 'mc', q: '窓《まど》を＿＿＿ください。（閉《し》めます）', choices: ['閉《し》めて', '閉《し》めます', '閉《し》めた', '閉《し》める'], answer: 0 },
    { type: 'mc', q: '漢字《かんじ》が 分《わ》から＿＿＿から、ひらがなで 書《か》きます。', choices: ['ない', 'ありません', 'ません', 'なくて'], answer: 0 },
    { type: 'scramble', translate: 'Saya meminjam CD dari Rina.', words: ['リナさん', 'に', 'CD', 'を', '借《か》りました'] },
    { type: 'scramble', translate: 'Kalau hujan turun, pertandingan tidak bisa dilaksanakan.', words: ['雨《あめ》', 'が', '降《ふ》る', 'と', '、', '試合《しあい》', 'が', 'できません'] },
    { type: 'mc', q: '田中《たなか》さんは＿＿＿人《ひと》ですか。…あの 眼鏡《めがね》を かけている 人《ひと》です。', choices: ['どの', 'だれの', 'なんの', 'いつの'], answer: 0 },
    { type: 'mc', q: '疲《つか》れた＿＿＿、早《はや》く 寝《ね》ます。', choices: ['とき', 'こと', 'もの', 'ので'], answer: 0 },
    { type: 'scramble', translate: 'Tolong sampaikan salam kepada semua di kantor.', words: ['会社《かいしゃ》', 'の', 'みなさん', 'に', 'よろしく', '言《い》ってください'] },
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
// Stage machine — same shape as the Kana/Kanji quiz (setup -> quiz ->
// result), so Mondaishuu now behaves and looks the same full-screen way.
// ---------------------------------------------------------------------
const STAGE_SETUP = 'setup'
const STAGE_QUIZ = 'quiz'
const STAGE_RESULT = 'result'

const stage = ref(STAGE_SETUP)

const qIndex = ref(0)
const score = ref(0)
const answered = ref(false)
const selectedChoice = ref(null)
const scrambleBank = ref([])
const scrambleBuilt = ref([])
const isCorrect = ref(false)

const currentQuestion = computed(() => active.value?.ref.questions?.[qIndex.value] ?? null)
const totalQuestions = computed(() => active.value?.ref.questions?.length ?? 0)

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

  if (isCorrect.value)
    return { correct: true, correctAnswer: null }

  const q = currentQuestion.value

  return {
    correct: false,
    correctAnswer: q.type === 'mc' ? q.choices[q.answer] : q.words.join(''),
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
  setupQuestion()
  stage.value = STAGE_QUIZ
}

function chooseMc(i) {
  if (answered.value)
    return
  selectedChoice.value = i
  answered.value = true
  isCorrect.value = i === currentQuestion.value.answer
  if (isCorrect.value)
    score.value++
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
  scheduleAdvance(isCorrect.value)
}

function nextQuestion() {
  clearAdvanceTimer()

  if (qIndex.value + 1 >= totalQuestions.value) {
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

    <div class="d-flex align-center ga-2 mb-3">
      <VIcon icon="tabler-books" size="20" color="primary" />
      <span class="text-subtitle-1 font-weight-medium">{{ t('mondaishuu.lessons_label') }}</span>
    </div>
    <div class="mondaishuu-grid mb-8">
      <button
        v-for="lesson in LESSONS"
        :key="lesson.id"
        type="button"
        class="mondaishuu-card"
        @click="startTab(`l${lesson.id}`)"
      >
        <span class="mondaishuu-card__num">{{ lesson.id }}</span>
        <span class="mondaishuu-card__focus">
          <RubyText :text="lesson.focus" :show-furigana="false" />
        </span>
      </button>
    </div>

    <div class="d-flex align-center ga-2 mb-3">
      <VIcon icon="tabler-notebook" size="20" color="secondary" />
      <span class="text-subtitle-1 font-weight-medium">{{ t('mondaishuu.reviews_label') }}</span>
    </div>
    <div class="mondaishuu-review-grid">
      <button
        v-for="review in REVIEWS"
        :key="review.id"
        type="button"
        class="mondaishuu-review-card"
        @click="startTab(review.id)"
      >
        <VIcon icon="tabler-notebook" size="18" class="mondaishuu-review-card__icon" />
        {{ review.label }}
      </button>
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
              <p class="text-h6 mb-4 text-center">
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
            🎉
          </div>
          <h5 class="text-h5 mb-1">
            {{ t('mondaishuu.done_title') }}
          </h5>
          <p class="text-h6 mb-6">
            {{ t('mondaishuu.score', { score, total: totalQuestions }) }}
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
      </div>
    </main>

    <footer
      v-if="feedback"
      class="kana-fs__footer"
      :class="feedback.correct ? 'is-correct' : 'is-incorrect'"
    >
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
            {{ feedback.correct ? t('mondaishuu.correct') : t('mondaishuu.incorrect') }}
          </div>
        </div>

        <VBtn
          v-if="!autoNext"
          color="primary"
          append-icon="tabler-arrow-right"
          @click="nextQuestion"
        >
          {{ qIndex + 1 >= totalQuestions ? t('mondaishuu.finish') : t('mondaishuu.next') }}
        </VBtn>
      </div>
    </footer>
  </div>
</template>

<style scoped>
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
