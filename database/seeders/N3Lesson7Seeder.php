<?php

namespace Database\Seeders;

/**
 * N3 Pelajaran 7: kosakata (undangan, pesta, cerita dan percakapan).
 *
 *   php artisan db:seed --class=N3Lesson7Seeder
 */
class N3Lesson7Seeder extends N3LessonSeeder
{
    protected function unitOrder(): int
    {
        return 7;
    }

    protected function words(): array
    {
        return [
            ['出す[料理を〜]', 'だす[りょうりを〜]', 'dasu', 'menyajikan [hidangan]', 'to serve [food]'],
            ['歓迎会', 'かんげいかい', 'kangeikai', 'pesta penyambutan', 'welcome party'],
            ['招待状', 'しょうたいじょう', 'shoutaijou', 'surat undangan', 'written invitation'],
            ['ラーメン', 'ラーメン', 'raamen', 'ramen, mi kuah', 'ramen'],
            ['折り紙', 'おりがみ', 'origami', 'origami, seni lipat kertas', 'origami'],
            ['ピンク', 'ピンク', 'pinku', 'merah muda', 'pink'],
            ['送別会', 'そうべつかい', 'soubetsukai', 'pesta perpisahan', 'farewell party'],
            ['中華レストラン', 'ちゅうかレストラン', 'chuuka resutoran', 'restoran Tionghoa', 'Chinese restaurant'],
            ['留学生会', 'りゅうがくせいかい', 'ryuugakuseikai', 'perhimpunan pelajar asing', 'international students association'],
            ['〜会[留学生〜]', '〜かい[りゅうがくせい〜]', 'kai', 'perhimpunan ~ [pelajar asing]', 'association'],
            ['会長', 'かいちょう', 'kaichou', 'ketua', 'chairperson'],
            ['点数', 'てんすう', 'tensuu', 'nilai, skor', 'score'],
            ['たいした', 'たいした', 'taishita', '(tidak) seberapa', 'big deal'],
            ['悪口', 'わるぐち', 'waruguchi', 'menjelek-jelekkan', 'badmouthing'],
            ['夫婦', 'ふうふ', 'fuufu', 'suami istri', 'married couple'],
            ['〜げんか[夫婦〜]', '〜げんか[ふうふ〜]', 'genka', 'pertengkaran ~ [suami istri]', 'quarrel'],
            ['医学部', 'いがくぶ', 'igakubu', 'fakultas kedokteran', 'medical faculty'],
            ['〜部[医学〜]', '〜ぶ[いがく〜]', 'bu', 'fakultas ~', 'faculty'],
            ['ライオン', 'ライオン', 'raion', 'singa', 'lion'],
            ['喜ぶ', 'よろこぶ', 'yorokobu', 'senang, bergembira', 'to be pleased'],
            ['冗談', 'じょうだん', 'joudan', 'lelucon', 'joke'],
            ['〜たち[男〜]', '〜たち[おとこ〜]', 'tachi', 'akhiran jamak (~ -~)', 'plural suffix'],
            ['お化け', 'おばけ', 'obake', 'hantu', 'ghost'],
            ['いじめる', 'いじめる', 'ijimeru', 'menindas, merundung', 'to bully'],
            ['感心する', 'かんしんする', 'kanshin suru', 'kagum', 'to be impressed'],
            ['親', 'おや', 'oya', 'orang tua', 'parents'],
            ['あらためて', 'あらためて', 'aratamete', 'sekali lagi, secara resmi', 'again, anew'],
            ['一周', 'いっしゅう', 'isshuu', 'satu putaran', 'once around'],
            ['〜山', '〜さん', 'san', 'Gunung ~', 'Mt.'],
            ['芝居', 'しばい', 'shibai', 'sandiwara', 'play, show'],
            ['せりふ', 'せりふ', 'serifu', 'dialog, naskah', 'lines in a play'],
            ['泣く', 'なく', 'naku', 'menangis', 'to cry'],
            ['アニメ', 'アニメ', 'anime', 'film animasi', 'animation'],
            ['感動する', 'かんどうする', 'kandou suru', 'terharu, tersentuh', 'to be moved'],
            ['講演', 'こうえん', 'kouen', 'ceramah', 'lecture'],
            ['譲る', 'ゆずる', 'yuzuru', 'menyerahkan, mengalah', 'to hand over'],
            ['ツアー', 'ツアー', 'tsuaa', 'tur', 'tour'],
            ['きつい[スケジュールが〜]', 'きつい[スケジュールが〜]', 'kitsui', 'ketat, berat [jadwal]', 'tough'],
            ['フリーマーケット', 'フリーマーケット', 'furii maaketto', 'pasar loak', 'flea market'],
            ['遠慮する', 'えんりょする', 'enryo suru', 'menolak dengan sopan, sungkan', 'to decline'],
            ['失礼', 'しつれい', 'shitsurei', 'ketidaksopanan', 'impoliteness'],
            ['表す', 'あらわす', 'arawasu', 'menunjukkan, menyatakan', 'to show'],
            ['受ける[誘いを〜]', 'うける[さそいを〜]', 'ukeru', 'menerima [ajakan]', 'to accept [an invitation]'],
            ['着付け教室', 'きつけきょうしつ', 'kitsuke kyoushitsu', 'kelas memakai kimono', 'kimono dressing class'],
            ['待ち合わせる', 'まちあわせる', 'machiawaseru', 'janjian bertemu', 'to meet up'],
            ['空く[時間が〜]', 'あく[じかんが〜]', 'aku', 'kosong [waktu luang]', 'to be free'],
            ['交流会', 'こうりゅうかい', 'kouryuukai', 'acara temu kenal', 'get-to-know-you party'],
            ['いろんな', 'いろんな', 'ironna', 'bermacam-macam', 'various'],
            ['ゼミ', 'ゼミ', 'zemi', 'seminar (kuliah)', 'seminar'],
            ['せっかく', 'せっかく', 'sekkaku', 'dengan susah payah, berkenan', 'kindly'],
            ['今回', 'こんかい', 'konkai', 'kali ini', 'on this occasion'],
            ['同僚', 'どうりょう', 'douryou', 'rekan kerja', 'colleague'],
            ['登山', 'とざん', 'tozan', 'mendaki gunung', 'mountain climbing'],
            ['紅葉', 'こうよう', 'kouyou', 'daun musim gugur yang memerah', 'autumn foliage'],
            ['見物', 'けんぶつ', 'kenbutsu', 'wisata, menonton', 'sightseeing'],
            ['音楽会', 'おんがくかい', 'ongakukai', 'konser', 'recital, concert'],
            ['まんじゅう', 'まんじゅう', 'manjuu', 'manju, kue isi kacang manis', 'bean-jam bun'],
            ['ヘビ', 'ヘビ', 'hebi', 'ular', 'snake'],
            ['毛虫', 'けむし', 'kemushi', 'ulat bulu', 'caterpillar'],
            ['いばる', 'いばる', 'ibaru', 'bersikap sombong', 'to throw ones weight around'],
            ['震える', 'ふるえる', 'furueru', 'gemetar', 'to tremble'],
            ['すると', 'すると', 'suruto', 'lalu, kemudian', 'thereupon'],
            ['おれ', 'おれ', 'ore', 'aku (dipakai pria)', 'I, me (used by men)'],
            ['〜ぐらい', '〜ぐらい', 'gurai', 'setidaknya ~', 'at least'],
            ['お前', 'おまえ', 'omae', 'kamu (dipakai pria)', 'you (used by men)'],
            ['丸い', 'まるい', 'marui', 'bulat', 'round'],
            ['いや', 'いや', 'iya', 'tidak', 'no'],
            ['震えだす', 'ふるえだす', 'furuedasu', 'mulai gemetar', 'to begin to tremble'],
            ['助ける', 'たすける', 'tasukeru', 'menolong, menyelamatkan', 'to help, to save'],
            ['次々に', 'つぎつぎに', 'tsugitsugi ni', 'satu per satu', 'one after another'],
            ['目の前', 'めのまえ', 'me no mae', 'di depan mata', 'in front of ones eyes'],
            ['ポツリと', 'ポツリと', 'potsuri to', 'dengan bergumam', 'mutteringly'],
            ['ホームページ', 'ホームページ', 'hoomupeeji', 'situs web', 'website'],
            ['笑い話', 'わらいばなし', 'waraibanashi', 'cerita lucu', 'funny story'],
            ['落語', 'らくご', 'rakugo', 'rakugo, seni bercerita lucu tradisional', 'rakugo'],

            // Tambahan dari lembar latihan (tata bahasa, bicara-dengar, baca-tulis, soal)
            ['丁寧[な]', 'ていねい[な]', 'teinei', 'sopan, teliti', 'polite, careful'],
            ['断る', 'ことわる', 'kotowaru', 'menolak', 'to decline, to refuse'],
            ['お風呂', 'おふろ', 'ofuro', 'bak mandi, mandi', 'bath'],
            ['以内', 'いない', 'inai', 'dalam ~, kurang dari ~', 'within'],
            ['アルバム', 'アルバム', 'arubamu', 'album', 'album'],
            ['お祭り', 'おまつり', 'omatsuri', 'festival', 'festival'],
            ['二十歳', 'はたち', 'hatachi', 'dua puluh tahun', 'twenty years old'],
            ['がっかりする', 'がっかりする', 'gakkari suru', 'kecewa', 'to be disappointed'],
            ['おしゃべり', 'おしゃべり', 'oshaberi', 'mengobrol, banyak bicara', 'chatting, chatterbox'],
            ['おもしろがる', 'おもしろがる', 'omoshirogaru', 'merasa lucu atau menarik', 'to find amusing'],
            ['まとめる', 'まとめる', 'matomeru', 'merangkum, menyatukan', 'to sum up, to put together'],
            ['贈り物', 'おくりもの', 'okurimono', 'hadiah', 'gift, present'],
            ['しかたがない', 'しかたがない', 'shikata ga nai', 'tidak ada cara lain', 'it cannot be helped'],
            ['ただ', 'ただ', 'tada', 'hanya, namun', 'just, only'],
            ['おなかがいっぱい', 'おなかがいっぱい', 'onaka ga ippai', 'kenyang', 'full (stomach)'],
            ['大使館', 'たいしかん', 'taishikan', 'kedutaan besar', 'embassy'],
            ['怖がる', 'こわがる', 'kowagaru', 'takut (dikatakan untuk orang lain)', 'to be afraid (said of others)'],

            // Ungkapan
            ['本当ですか。', 'ほんとうですか。', 'hontou desu ka', 'Benarkah?', 'Really?'],
            ['ぜひお願いします。', 'ぜひおねがいします。', 'zehi onegai shimasu', 'Ya, tentu, mohon ya.', 'Oh yes, please'],
            ['せっかく誘っていただいたのに、申し訳ありません。今回は遠慮させてください。', 'せっかくさそっていただいたのに、もうしわけありません。こんかいはえんりょさせてください。', 'sekkaku sasotte itadaita noni moushiwake arimasen konkai wa enryo sasete kudasai', 'Terima kasih atas ajakannya, tapi mohon maaf, kali ini saya tidak bisa ikut.', 'I appreciate the invitation but must decline this time'],
            ['……かい？', '……かい？', 'kai (shitsumon)', 'akhiran pertanyaan (gaya akrab)', 'suffix indicating a question'],
            ['助けてくれ！', 'たすけてくれ！', 'tasukete kure', 'Tolong!', 'Help!'],
        ];
    }
}
