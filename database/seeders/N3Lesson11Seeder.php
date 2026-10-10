<?php

namespace Database\Seeders;

/**
 * N3 Pelajaran 11: kosakata (sejarah, warisan dunia, desa dan iklim).
 *
 *   php artisan db:seed --class=N3Lesson11Seeder
 */
class N3Lesson11Seeder extends N3LessonSeeder
{
    protected function unitOrder(): int
    {
        return 11;
    }

    protected function words(): array
    {
        return [
            ['ますます', 'ますます', 'masumasu', 'semakin', 'more and more'],
            ['企業', 'きぎょう', 'kigyou', 'perusahaan', 'business enterprise'],
            ['今後', 'こんご', 'kongo', 'mulai sekarang', 'from now on'],
            ['方言', 'ほうげん', 'hougen', 'dialek', 'dialect'],
            ['普及する', 'ふきゅうする', 'fukyuu suru', 'menyebar luas', 'to become widespread'],
            ['建つ', 'たつ', 'tatsu', 'berdiri, dibangun', 'to be built'],
            ['大家族', 'だいかぞく', 'daikazoku', 'keluarga besar', 'large family'],
            ['大〜[〜家族]', 'だい〜[〜かぞく]', 'dai', 'besar ~ [keluarga]', 'large'],
            ['パックツアー', 'パックツアー', 'pakku tsuaa', 'tur paket', 'package tour'],
            ['個人', 'こじん', 'kojin', 'perorangan', 'individual'],
            ['いかにも', 'いかにも', 'ikanimo', 'memang, betul-betul', 'indeed, really'],
            ['入学式', 'にゅうがくしき', 'nyuugakushiki', 'upacara penerimaan murid baru', 'entrance ceremony'],
            ['派手[な]', 'はで[な]', 'hade', 'mencolok', 'flamboyant'],
            ['元気', 'げんき', 'genki', 'semangat, tenaga', 'vitality, cheerfulness'],
            ['出す[元気を〜]', 'だす[げんきを〜]', 'dasu', 'menyemangati diri [semangat]', 'to cheer oneself up'],
            ['広告', 'こうこく', 'koukoku', 'iklan', 'advertisement'],
            ['美容院', 'びよういん', 'biyouin', 'salon kecantikan', 'beauty parlor'],
            ['車いす', 'くるまいす', 'kurumaisu', 'kursi roda', 'wheelchair'],
            ['寄付する[病院に車いすを〜]', 'きふする[びょういんにくるまいすを〜]', 'kifu suru', 'menyumbang [kursi roda ke rumah sakit]', 'to donate'],
            ['グレー', 'グレー', 'guree', 'abu-abu', 'grey'],
            ['地味[な]', 'じみ[な]', 'jimi', 'sederhana, tidak mencolok', 'plain, understated'],
            ['原爆', 'げんばく', 'genbaku', 'bom atom', 'atomic bomb'],
            ['ただひとつ', 'ただひとつ', 'tada hitotsu', 'hanya satu-satunya', 'only, unique'],
            ['恐ろしさ', 'おそろしさ', 'osoroshisa', 'kengerian', 'horror'],
            ['ダイナマイト', 'ダイナマイト', 'dainamaito', 'dinamit', 'dynamite'],
            ['自宅', 'じたく', 'jitaku', 'rumah sendiri', 'ones home'],
            ['あわてる', 'あわてる', 'awateru', 'panik, gugup', 'to panic, to get flustered'],
            ['落ち着く', 'おちつく', 'ochitsuku', 'tenang', 'to calm down'],
            ['行動する', 'こうどうする', 'koudou suru', 'bertindak', 'to act'],
            ['のんびりする', 'のんびりする', 'nonbiri suru', 'bersantai', 'to spend time leisurely'],
            ['シューズ', 'シューズ', 'shuuzu', 'sepatu', 'shoes'],
            ['つながる[電話が〜]', 'つながる[でんわが〜]', 'tsunagaru', 'tersambung [telepon]', 'to get through'],
            ['遺跡', 'いせき', 'iseki', 'reruntuhan, situs purbakala', 'ruins, remains'],
            ['発掘', 'はっくつ', 'hakkutsu', 'penggalian', 'excavation'],
            ['これまでに', 'これまでに', 'koremade ni', 'sampai sekarang', 'until now'],
            ['南極', 'なんきょく', 'nankyoku', 'Kutub Selatan', 'South Pole'],
            ['探検', 'たんけん', 'tanken', 'penjelajahan', 'exploration'],
            ['世界遺産', 'せかいいさん', 'sekai isan', 'warisan dunia', 'World Heritage'],
            ['価値', 'かち', 'kachi', 'nilai', 'worth, value'],
            ['やっぱり', 'やっぱり', 'yappari', 'memang begitu, setelah dipikir-pikir', 'after all'],
            ['流氷', 'りゅうひょう', 'ryuuhyou', 'es mengambang', 'ice floe'],
            ['自由行動', 'じゆうこうどう', 'jiyuu koudou', 'kegiatan bebas', 'acting at ones own discretion'],
            ['提案する', 'ていあんする', 'teian suru', 'mengusulkan', 'to propose, to suggest'],
            ['軽く[〜体操する]', 'かるく[〜たいそうする]', 'karuku', 'ringan [senam ringan]', 'light'],
            ['乗り物', 'のりもの', 'norimono', 'kendaraan', 'vehicle'],
            ['酔う[乗り物に〜]', 'よう[のりものに〜]', 'you', 'mabuk [kendaraan]', 'to feel sick [from travel]'],
            ['コメント', 'コメント', 'komento', 'komentar', 'comment'],
            ['さらに', 'さらに', 'sara ni', 'lebih lanjut, selain itu', 'furthermore'],
            ['仮装', 'かそう', 'kasou', 'kostum, penyamaran', 'fancy dress'],
            ['染める', 'そめる', 'someru', 'mewarnai', 'to dye'],
            ['黄金', 'おうごん', 'ougon', 'emas', 'gold'],
            ['伝説', 'でんせつ', 'densetsu', 'legenda', 'legend'],
            ['いくつか', 'いくつか', 'ikutsuka', 'beberapa', 'several'],
            ['屋根', 'やね', 'yane', 'atap', 'roof'],
            ['農作物', 'のうさくぶつ', 'nousakubutsu', 'hasil pertanian', 'crop'],
            ['金銀', 'きんぎん', 'kingin', 'emas dan perak', 'gold and silver'],
            ['治める', 'おさめる', 'osameru', 'memerintah, menguasai', 'to control, to rule'],
            ['掌', 'てのひら', 'tenohira', 'telapak tangan', 'palm of the hand'],
            ['後半', 'こうはん', 'kouhan', 'paruh kedua', 'latter part'],
            ['くぎ', 'くぎ', 'kugi', 'paku', 'nail'],
            ['村人', 'むらびと', 'murabito', 'penduduk desa', 'villager'],
            ['かける[費用を〜]', 'かける[ひようを〜]', 'kakeru', 'mengeluarkan [biaya]', 'to spend [money]'],
            ['向き', 'むき', 'muki', 'arah (angin)', 'direction'],
            ['抵抗', 'ていこう', 'teikou', 'perlawanan, hambatan', 'resistance'],
            ['〜層', '〜そう', 'sou', 'lapisan ~', 'layered'],
            ['蚕', 'かいこ', 'kaiko', 'ulat sutra', 'silkworm'],
            ['火薬', 'かやく', 'kayaku', 'bubuk mesiu', 'gunpowder'],
            ['製造する', 'せいぞうする', 'seizou suru', 'memproduksi', 'to manufacture'],
            ['送る[生活を〜]', 'おくる[せいかつを〜]', 'okuru', 'menjalani [kehidupan]', 'to lead [ones life]'],
            ['家内産業', 'かないさんぎょう', 'kanai sangyou', 'industri rumahan', 'cottage industry'],
            ['年貢', 'ねんぐ', 'nengu', 'upeti tahunan, pajak tanah', 'land tax, annual tribute'],
            ['期待する', 'きたいする', 'kitai suru', 'mengharapkan', 'to expect, to hope for'],
            ['地', 'ち', 'chi', 'tanah, daerah', 'land'],
            ['前半', 'ぜんはん', 'zenhan', 'paruh pertama', 'first half'],
            ['やってくる', 'やってくる', 'yatte kuru', 'datang, muncul', 'to come along, to turn up'],
            ['住み着く', 'すみつく', 'sumitsuku', 'menetap', 'to settle down'],
            ['一族', 'いちぞく', 'ichizoku', 'keluarga besar, klan', 'family, clan'],
            ['〜城[帰雲〜]', '〜じょう[かえりくも〜]', 'jou', 'Kastel ~ [Kaerikumo]', 'castle'],
            ['城', 'しろ', 'shiro', 'kastel', 'castle'],
            ['掘り当てる', 'ほりあてる', 'horiateru', 'menemukan lewat penggalian', 'to strike'],
            ['権力者', 'けんりょくしゃ', 'kenryokusha', 'orang berkuasa', 'powerful person'],
            ['飢きん', 'ききん', 'kikin', 'kelaparan, paceklik', 'famine'],
            ['〜軒', '〜けん', 'ken', 'kata bantu bilangan rumah', 'counter for houses'],
            ['数百人', 'すうひゃくにん', 'suuhyakunin', 'beberapa ratus orang', 'several hundred people'],
            ['一人残らず', 'ひとりのこらず', 'hitori nokorazu', 'tanpa terkecuali', 'to the last man'],
            ['消える', 'きえる', 'kieru', 'lenyap, hilang', 'to be wiped out'],
            ['保管する', 'ほかんする', 'hokan suru', 'menyimpan', 'to keep, to store'],
            ['兆', 'ちょう', 'chou', 'triliun', 'trillion'],
            ['分ける', 'わける', 'wakeru', 'membagi, memisahkan', 'to separate'],
            ['積もる[雪が〜]', 'つもる[ゆきが〜]', 'tsumoru', 'menumpuk [salju]', 'to accumulate'],
            ['気候', 'きこう', 'kikou', 'iklim', 'climate'],
            ['観光案内', 'かんこうあんない', 'kankou annai', 'informasi wisata', 'tourist information'],
            ['観光地', 'かんこうち', 'kankouchi', 'tempat wisata', 'tourist spot'],

            // Ungkapan
            ['〜っていうのはどうですか。', '〜っていうのはどうですか。', 'tte iu no wa dou desu ka', 'Bagaimana kalau ~?', 'How about...?'],
            ['それも悪くないですね。', 'それもわるくないですね。', 'sore mo warukunai desu ne', 'Itu juga tidak buruk ya.', 'Yes, that is not bad either'],
            ['それもそうですね。', 'それもそうですね。', 'sore mo sou desu ne', 'Benar juga ya.', 'Yes, I suppose that is right'],
            ['けど、……。', 'けど、……。', 'kedo', 'Tapi, ...', 'But...'],
            ['それも悪くないですけど……。', 'それもわるくないですけど……。', 'sore mo warukunai desu kedo', 'Itu juga tidak buruk, tetapi...', 'That is not a bad idea either, but...'],
        ];
    }
}
