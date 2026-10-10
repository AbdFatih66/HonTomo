<?php

namespace Database\Seeders;

/**
 * N3 Pelajaran 10: kosakata (kesalahan manusia, kecerobohan dan salah paham).
 *
 *   php artisan db:seed --class=N3Lesson10Seeder
 */
class N3Lesson10Seeder extends N3LessonSeeder
{
    protected function unitOrder(): int
    {
        return 10;
    }

    protected function words(): array
    {
        return [
            ['もうける[お金を〜]', 'もうける[おかねを〜]', 'moukeru', 'mendapat [uang], untung', 'to make [money]'],
            ['見かける', 'みかける', 'mikakeru', 'melihat sekilas', 'to catch sight of'],
            ['否定する', 'ひていする', 'hitei suru', 'menyangkal', 'to deny'],
            ['タイムマシン', 'タイムマシン', 'taimu mashin', 'mesin waktu', 'time machine'],
            ['宝くじ', 'たからくじ', 'takarakuji', 'lotre', 'lottery'],
            ['当たる[宝くじが〜]', 'あたる[たからくじが〜]', 'ataru', 'menang [lotre]', 'to win [a lottery]'],
            ['ワールドカップ', 'ワールドカップ', 'waarudo kappu', 'Piala Dunia', 'World Cup'],
            ['カエル', 'カエル', 'kaeru', 'katak', 'frog'],
            ['計画', 'けいかく', 'keikaku', 'rencana', 'plan'],
            ['実際', 'じっさい', 'jissai', 'kenyataan, sebenarnya', 'actuality'],
            ['めったに', 'めったに', 'mettani', 'jarang sekali', 'seldom'],
            ['通じる[電話が〜]', 'つうじる[でんわが〜]', 'tsuujiru', 'tersambung [telepon]', 'to get through'],
            ['時間通りに', 'じかんどおりに', 'jikan doori ni', 'tepat waktu', 'on time'],
            ['かかる[エンジンが〜]', 'かかる[エンジンが〜]', 'kakaru', 'menyala [mesin]', '[engine] to start'],
            ['鬼', 'おに', 'oni', 'setan, raksasa oni', 'demon'],
            ['怒る', 'おこる', 'okoru', 'marah', 'to get angry'],
            ['CO2', 'シーオーツー', 'shii oo tsuu', 'karbon dioksida', 'CO2'],
            ['抽選', 'ちゅうせん', 'chuusen', 'undian', 'draw, ballot'],
            ['一等', 'いっとう', 'ittou', 'juara pertama, hadiah utama', 'first place'],
            ['投票', 'とうひょう', 'touhyou', 'pemungutan suara', 'voting'],
            ['[お]互いに', '[お]たがいに', 'tagai ni', 'satu sama lain', 'each other'],
            ['出す[修理に〜]', 'だす[しゅうりに〜]', 'dasu', 'mengirim [untuk diperbaiki]', 'to send [for repair]'],
            ['聞き返す', 'ききかえす', 'kikikaesu', 'menanyakan ulang', 'to ask for something to be explained again'],
            ['てっきり', 'てっきり', 'tekkiri', 'sudah pasti (ternyata keliru)', 'definitely'],
            ['倉庫', 'そうこ', 'souko', 'gudang', 'storeroom, warehouse'],
            ['プリンター', 'プリンター', 'purintaa', 'printer', 'printer'],
            ['入る[電源が〜]', 'はいる[でんげんが〜]', 'hairu', 'menyala [daya listrik]', '[power] to be on'],
            ['マニュアル', 'マニュアル', 'manyuaru', 'buku panduan', 'manual'],
            ['親しい', 'したしい', 'shitashii', 'akrab, dekat', 'close, familiar'],
            ['驚く', 'おどろく', 'odoroku', 'terkejut', 'to be surprised'],
            ['〜代[60〜]', '〜だい[60〜]', 'dai', 'usia ~an [60-an]', '-ies'],
            ['誤解', 'ごかい', 'gokai', 'salah paham', 'misunderstanding'],
            ['記憶', 'きおく', 'kioku', 'ingatan', 'memory'],
            ['型', 'かた', 'kata', 'tipe, model', 'type'],
            ['〜型', '〜がた', 'gata', 'tipe ~', '~ type'],
            ['落とし物', 'おとしもの', 'otoshimono', 'barang hilang', 'lost property'],
            ['転ぶ', 'ころぶ', 'korobu', 'jatuh, terjatuh', 'to fall over'],
            ['奇数', 'きすう', 'kisuu', 'bilangan ganjil', 'odd number'],
            ['偶数', 'ぐうすう', 'guusuu', 'bilangan genap', 'even number'],
            ['ぼんやりする', 'ぼんやりする', 'bonyari suru', 'melamun', 'to daydream'],
            ['あわて者', 'あわてもの', 'awatemono', 'orang yang ceroboh/gugup', 'careless person'],
            ['ミス', 'ミス', 'misu', 'kesalahan', 'mistake'],
            ['これら', 'これら', 'korera', 'hal-hal ini', 'these'],
            ['ヒューマンエラー', 'ヒューマンエラー', 'hyuuman eraa', 'kesalahan manusia', 'human error'],
            ['手術', 'しゅじゅつ', 'shujutsu', 'operasi bedah', 'operation'],
            ['患者', 'かんじゃ', 'kanja', 'pasien', 'patient'],
            ['心理学者', 'しんりがくしゃ', 'shinrigakusha', 'psikolog', 'psychologist'],
            ['おかす[ミスを〜]', 'おかす[ミスを〜]', 'okasu', 'melakukan [kesalahan]', 'to make [a mistake]'],
            ['うっかりミス', 'うっかりミス', 'ukkari misu', 'kesalahan karena lalai', 'careless mistake'],
            ['うっかり', 'うっかり', 'ukkari', 'lalai, tidak sengaja', 'careless'],
            ['こういう', 'こういう', 'kouiu', 'seperti ini', 'this kind of, such'],
            ['チェックリスト', 'チェックリスト', 'chekku risuto', 'daftar periksa', 'checklist'],
            ['手がかり', 'てがかり', 'tegakari', 'petunjuk, jejak', 'hint, clue'],
            ['一方', 'いっぽう', 'ippou', 'di sisi lain', 'on the other hand'],
            ['深く[〜呼吸する]', 'ふかく[〜こきゅうする]', 'fukaku', 'dalam-dalam [bernapas]', 'deeply'],
            ['指', 'ゆび', 'yubi', 'jari', 'finger'],
            ['聖人君子', 'せいじんくんし', 'seijin kunshi', 'orang yang sempurna budi', 'model of all virtues'],
            ['うそつき', 'うそつき', 'usotsuki', 'pembohong', 'liar'],
            ['または', 'または', 'matawa', 'atau', 'or'],
            ['エラー', 'エラー', 'eraa', 'galat, kesalahan', 'error'],
            ['困った人', 'こまったひと', 'komatta hito', 'orang yang menyusahkan', 'hopeless person'],
            ['完成する', 'かんせいする', 'kansei suru', 'selesai, rampung', 'to complete'],
            ['つながる[出来事に〜]', 'つながる[できごとに〜]', 'tsunagaru', 'berujung pada [kejadian]', 'to lead to [an incident]'],
            ['出来事', 'できごと', 'dekigoto', 'kejadian', 'incident'],
            ['不注意', 'ふちゅうい', 'fuchuui', 'kecerobohan', 'carelessness'],
            ['引き起こす', 'ひきおこす', 'hikiokosu', 'menimbulkan, menyebabkan', 'to cause, to bring about'],

            // Ungkapan
            ['どういうことでしょうか。', 'どういうことでしょうか。', 'douiu koto deshouka', 'Maksudnya bagaimana?', 'What are you talking about?'],
            ['そんなはずはありません。', 'そんなはずはありません。', 'sonna hazu wa arimasen', 'Tidak mungkin begitu!', 'That cannot be true'],
            ['てっきり〜と思っていました。', 'てっきり〜とおもっていました。', 'tekkiri to omotte imashita', 'Saya kira pasti ~.', 'I was sure that...'],
            ['気を悪くする', 'きをわるくする', 'ki o waruku suru', 'tersinggung, kesal', 'to get upset'],
            ['わかってもらえればいいんです。', 'わかってもらえればいいんです。', 'wakatte moraereba ii n desu', 'Saya senang kalau Anda mengerti.', 'I am happy if you understand'],
        ];
    }
}
