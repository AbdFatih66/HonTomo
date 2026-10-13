<?php

namespace Database\Seeders;

/**
 * N3 Pelajaran 12: kosakata (kehidupan kota, keluhan, lingkungan dan pengumuman).
 *
 *   php artisan db:seed --class=N3Lesson12Seeder
 */
class N3Lesson12Seeder extends N3LessonSeeder
{
    protected function unitOrder(): int
    {
        return 12;
    }

    protected function words(): array
    {
        return [
            ['演奏会', 'えんそうかい', 'ensoukai', 'konser, resital', 'concert, recital'],
            ['報告書', 'ほうこくしょ', 'houkokusho', 'laporan', 'report'],
            ['あくび', 'あくび', 'akubi', 'menguap', 'yawn'],
            ['犯人', 'はんにん', 'hannin', 'pelaku kejahatan', 'criminal'],
            ['追いかける', 'おいかける', 'oikakeru', 'mengejar', 'to chase'],
            ['作業', 'さぎょう', 'sagyou', 'pekerjaan, kegiatan kerja', 'work'],
            ['スープ', 'スープ', 'suupu', 'sup', 'soup'],
            ['こぼす', 'こぼす', 'kobosu', 'menumpahkan', 'to spill'],
            ['シャッター', 'シャッター', 'shattaa', 'rolling door, rana', 'shutter'],
            ['スプレー', 'スプレー', 'supuree', 'semprotan', 'spray'],
            ['落書きする', 'らくがきする', 'rakugaki suru', 'mencoret-coret', 'to draw graffiti'],
            ['夜中', 'よなか', 'yonaka', 'tengah malam', 'in the middle of the night'],
            ['日', 'ひ', 'hi', 'sinar matahari', 'sunshine'],
            ['当たる[日が〜]', 'あたる[ひが〜]', 'ataru', 'terkena [sinar matahari]', 'to get [sunshine]'],
            ['暮らす', 'くらす', 'kurasu', 'hidup, tinggal', 'to live'],
            ['書道', 'しょどう', 'shodou', 'kaligrafi', 'calligraphy'],
            ['蛍光灯', 'けいこうとう', 'keikoutou', 'lampu neon', 'fluorescent light'],
            ['メニュー', 'メニュー', 'menyuu', 'menu', 'menu'],
            ['バイク', 'バイク', 'baiku', 'sepeda motor', 'motorbike'],
            ['目覚まし時計', 'めざましどけい', 'mezamashi dokei', 'jam beker', 'alarm clock'],
            ['鳴る', 'なる', 'naru', 'berbunyi', 'to go off'],
            ['温暖な', 'おんだんな', 'ondan na', 'hangat, sejuk (iklim)', 'mild'],
            ['家事', 'かじ', 'kaji', 'pekerjaan rumah tangga', 'housework'],
            ['ぐっすり[〜眠る]', 'ぐっすり[〜ねむる]', 'gussuri', 'nyenyak [tidur]', 'soundly'],
            ['迷惑', 'めいわく', 'meiwaku', 'gangguan, kerepotan', 'nuisance, trouble'],
            ['かける[迷惑を〜]', 'かける[めいわくを〜]', 'kakeru', 'merepotkan [menimbulkan gangguan]', 'to cause [trouble]'],
            ['風邪薬', 'かぜぐすり', 'kazegusuri', 'obat flu', 'cold medicine'],
            ['乗り遅れる', 'のりおくれる', 'noriokureru', 'ketinggalan (bus, kereta)', 'to miss a bus or train'],
            ['苦情', 'くじょう', 'kujou', 'keluhan', 'complaint'],
            ['遅く', 'おそく', 'osoku', 'larut, terlambat', 'late'],
            ['[お]帰り', '[お]かえり', 'kaeri', 'pulang', 'coming home'],
            ['あまり', 'あまり', 'amari', 'terlalu', 'too'],
            ['どうしても', 'どうしても', 'doushitemo', 'bagaimanapun juga, harus', 'inevitably, like it or not'],
            ['自治会', 'じちかい', 'jichikai', 'rukun warga, perkumpulan warga', 'community association'],
            ['役員', 'やくいん', 'yakuin', 'pengurus', 'officer'],
            ['DVD', 'ディーブイディー', 'diibuidii', 'DVD', 'DVD'],
            ['座談会', 'ざだんかい', 'zadankai', 'diskusi meja bundar', 'round-table discussion'],
            ['カルチャーショック', 'カルチャーショック', 'karuchaa shokku', 'kejutan budaya', 'culture shock'],
            ['受ける[ショックを〜]', 'うける[ショックを〜]', 'ukeru', 'mengalami [kejutan]', 'to receive [a shock]'],
            ['それまで', 'それまで', 'sore made', 'sampai saat itu', 'until then'],
            ['騒々しい', 'そうぞうしい', 'souzoushii', 'berisik, gaduh', 'noisy'],
            ['アナウンス', 'アナウンス', 'anaunsu', 'pengumuman', 'announcement'],
            ['分かれる[意見が〜]', 'わかれる[いけんが〜]', 'wakareru', 'terbagi [pendapat]', '[opinion] to be divided'],
            ['奥様', 'おくさま', 'okusama', 'istri (hormat), ibu rumah tangga', 'housewife'],
            ['おいでいただく', 'おいでいただく', 'oide itadaku', 'berkenan datang', 'to kindly come'],
            ['苦労', 'くろう', 'kurou', 'kesulitan, jerih payah', 'hardship'],
            ['中略', 'ちゅうりゃく', 'chuuryaku', '(beberapa paragraf) dihilangkan', 'several paragraphs omitted'],
            ['おかしな', 'おかしな', 'okashi na', 'aneh, lucu', 'funny'],
            ['サンダル', 'サンダル', 'sandaru', 'sandal', 'sandal'],
            ['ピーピー', 'ピーピー', 'piipii', 'bunyi nyaring (ketel)', 'whistling sound'],
            ['たまらない', 'たまらない', 'tamaranai', 'tidak tahan', 'cannot stand'],
            ['都会', 'とかい', 'tokai', 'kota besar', 'city'],
            ['住宅地', 'じゅうたくち', 'juutakuchi', 'daerah perumahan', 'residential area'],
            ['虫', 'むし', 'mushi', 'serangga', 'insect'],
            ['虫の音', 'むしのね', 'mushi no ne', 'suara serangga', 'sound of insects'],
            ['車内', 'しゃない', 'shanai', 'di dalam kendaraan', 'inside a vehicle'],
            ['ホーム', 'ホーム', 'hoomu', 'peron', 'platform'],
            ['加える', 'くわえる', 'kuwaeru', 'menambahkan', 'to add'],
            ['さっぱり[〜ない]', 'さっぱり[〜ない]', 'sappari', 'sama sekali [tidak]', 'completely [not]'],
            ['乗客', 'じょうきゃく', 'joukyaku', 'penumpang', 'passenger'],
            ['安全性', 'あんぜんせい', 'anzensei', 'keamanan', 'safety'],
            ['配慮する', 'はいりょする', 'hairyo suru', 'mempertimbangkan, memperhatikan', 'to consider'],
            ['含む', 'ふくむ', 'fukumu', 'mengandung, termasuk', 'to include'],
            ['チャイム', 'チャイム', 'chaimu', 'bel nada', 'chime'],
            ['発車ベル', 'はっしゃベル', 'hassha beru', 'bel keberangkatan kereta', 'train departure bell'],
            ['必ずしも[〜ない]', 'かならずしも[〜ない]', 'kanarazushimo', 'belum tentu [tidak]', '[not] necessarily'],
            ['近所づきあい', 'きんじょづきあい', 'kinjo zukiai', 'bergaul dengan tetangga', 'socializing with neighbors'],
            ['コマーシャル', 'コマーシャル', 'komaasharu', 'iklan komersial', 'commercial'],

            // Tambahan dari bacaan dan latihan di buku
            ['祈る', 'いのる', 'inoru', 'berdoa, mendoakan', 'to pray for'],
            ['国際交流協会', 'こくさいこうりゅうきょうかい', 'kokusai kouryuu kyoukai', 'asosiasi pertukaran internasional', 'international exchange association'],
            ['体の調子', 'からだのちょうし', 'karada no choushi', 'kondisi tubuh', 'physical condition'],
            ['タンス', 'タンス', 'tansu', 'lemari pakaian', 'chest of drawers'],
            ['役に立つ', 'やくにたつ', 'yaku ni tatsu', 'berguna', 'to be useful'],
            ['テープレコーダー', 'テープレコーダー', 'teepu rekoodaa', 'alat perekam kaset', 'tape recorder'],
            ['聞き取る', 'ききとる', 'kikitoru', 'menangkap dengan telinga', 'to catch by ear'],
            ['洗濯機', 'せんたくき', 'sentakuki', 'mesin cuci', 'washing machine'],
            ['召し上がる', 'めしあがる', 'meshiagaru', 'makan／minum (hormat)', 'to eat／drink (honorific)'],
            ['汚れる', 'よごれる', 'yogoreru', 'menjadi kotor', 'to get dirty'],
            ['止める[自転車を〜]', 'とめる[じてんしゃを〜]', 'tomeru', 'memarkir [sepeda]', 'to park [a bicycle]'],
            ['逃げる', 'にげる', 'nigeru', 'melarikan diri', 'to escape'],
            ['年を取る', 'としをとる', 'toshi o toru', 'bertambah usia', 'to get old'],
            ['社員食堂', 'しゃいんしょくどう', 'shain shokudou', 'kantin karyawan', 'staff cafeteria'],
            ['騒ぐ', 'さわぐ', 'sawagu', 'ribut, hura-hura', 'to make noise'],
            ['浴びる[シャワーを〜]', 'あびる[シャワーを〜]', 'abiru', 'mandi [shower]', 'to take [a shower]'],
            ['長い間', 'ながいあいだ', 'nagai aida', 'untuk waktu yang lama', 'for a long time'],
            ['下がる', 'さがる', 'sagaru', 'turun', 'to go down'],
            ['負ける', 'まける', 'makeru', 'kalah', 'to lose'],
            ['司会', 'しかい', 'shikai', 'pembawa acara, moderator', 'chairperson, moderator'],

            // Ungkapan
            ['気が付きませんでした。', 'きがつきませんでした。', 'ki ga tsukimasen deshita', 'Saya tidak menyadarinya.', 'I did not notice that'],
            ['どうしても……', 'どうしても……', 'doushitemo (ungkapan)', 'Bagaimanapun juga, ... (terpaksa)', 'inevitably, always'],
            ['それはわかりますけど、……', 'それはわかりますけど、……', 'sore wa wakarimasu kedo', 'Saya mengerti itu, tetapi...', 'I understand that, but...'],
            ['どちらかと言えば……', 'どちらかといえば……', 'dochira ka to ieba', 'Kalau boleh dibilang, ...', 'On the whole...'],
            ['いい勉強になる', 'いいべんきょうになる', 'ii benkyou ni naru', 'jadi pelajaran yang bagus', 'good for learning'],
        ];
    }
}
