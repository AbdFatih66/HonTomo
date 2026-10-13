<?php

namespace Database\Seeders;

/**
 * N3 Pelajaran 5: kosakata (petunjuk arah, jalan, dan arah mata angin).
 *
 *   php artisan db:seed --class=N3Lesson5Seeder
 */
class N3Lesson5Seeder extends N3LessonSeeder
{
    protected function unitOrder(): int
    {
        return 5;
    }

    protected function words(): array
    {
        return [
            ['教科書', 'きょうかしょ', 'kyoukasho', 'buku pelajaran', 'textbook'],
            ['居酒屋', 'いざかや', 'izakaya', 'kedai minum (izakaya)', 'pub, tavern'],
            ['やきとり', 'やきとり', 'yakitori', 'sate ayam panggang', 'grilled chicken on skewers'],
            ['画面', 'がめん', 'gamen', 'layar, gambar', 'picture, screen'],
            ['俳優', 'はいゆう', 'haiyuu', 'aktor', 'actor'],
            ['そっくり', 'そっくり', 'sokkuri', 'mirip sekali', 'looking exactly like'],
            ['コンビニ', 'コンビニ', 'konbini', 'minimarket 24 jam', 'convenience store'],
            ['改札[〜口]', 'かいさつ[〜ぐち]', 'kaisatsu', 'gerbang tiket [pintu ~]', 'ticket barrier'],
            ['運転手', 'うんてんしゅ', 'untenshu', 'pengemudi', 'driver'],
            ['かかってくる[電話が〜]', 'かかってくる[でんわが〜]', 'kakattekuru', 'berdering, masuk [telepon]', '[telephone] to ring'],
            ['切れる[電話が〜]', 'きれる[でんわが〜]', 'kireru', 'terputus [telepon]', '[telephone] to be cut off'],
            ['挙げる[例を〜]', 'あげる[れいを〜]', 'ageru', 'memberikan [contoh]', 'to give [an example]'],
            ['未来', 'みらい', 'mirai', 'masa depan', 'future'],
            ['なくす[戦争を〜]', 'なくす[せんそうを〜]', 'nakusu', 'menghapuskan [perang]', 'to get rid of [war]'],
            ['不思議[な]', 'ふしぎ[な]', 'fushigi', 'aneh, ajaib', 'strange, mysterious'],
            ['増やす', 'ふやす', 'fuyasu', 'menambah', 'to increase'],
            ['今ごろ', 'いまごろ', 'imagoro', 'sekitar sekarang', 'about now'],
            ['観光客', 'かんこうきゃく', 'kankoukyaku', 'wisatawan', 'sightseer, tourist'],
            ['沿う[川に〜]', 'そう[かわに〜]', 'sou', 'menyusuri [sungai]', 'along [a river]'],
            ['大通り', 'おおどおり', 'oodoori', 'jalan raya', 'main street, avenue'],
            ['出る[大通りに〜]', 'でる[おおどおりに〜]', 'deru', 'tiba di [jalan raya]', 'to come out [on the main street]'],
            ['横断歩道', 'おうだんほどう', 'oudanhodou', 'zebra cross', 'pedestrian crossing'],
            ['突き当たり', 'つきあたり', 'tsukiatari', 'ujung jalan', 'end of a street'],
            ['線路', 'せんろ', 'senro', 'rel kereta', 'railway line'],
            ['向こう側', 'むこうがわ', 'mukougawa', 'seberang', 'far side'],
            ['踏切', 'ふみきり', 'fumikiri', 'perlintasan kereta', 'level crossing'],
            ['分かれる[道が〜]', 'わかれる[みちが〜]', 'wakareru', 'bercabang [jalan]', '[road] to divide'],
            ['ページ', 'ページ', 'peeji', 'halaman', 'page'],
            ['芸術', 'げいじゅつ', 'geijutsu', 'seni', 'art'],
            ['道順', 'みちじゅん', 'michijun', 'rute, urutan jalan', 'way, route'],
            ['通行人', 'つうこうにん', 'tsuukounin', 'pejalan kaki, orang lewat', 'passer-by'],
            ['通り', 'とおり', 'toori', 'jalan', 'street, road'],
            ['川沿い', 'かわぞい', 'kawazoi', 'sepanjang sungai', 'beside a river'],
            ['〜沿い', '〜ぞい', 'zoi', 'sepanjang ~', 'along, beside'],
            ['流れる', 'ながれる', 'nagareru', 'mengalir', 'to flow'],
            ['〜先[100メートル〜]', '〜さき[100メートル〜]', 'saki', '~ di depan [100 meter]', '[100 m] ahead'],
            ['〜方[右の〜]', 'ほう[みぎの〜]', 'hou', 'arah ~ [kanan]', 'direction [to the right]'],
            ['南北', 'なんぼく', 'nanboku', 'utara dan selatan', 'north and south'],
            ['逆', 'ぎゃく', 'gyaku', 'kebalikan', 'opposite, reverse'],
            ['南半球', 'みなみはんきゅう', 'minami hankyuu', 'belahan bumi selatan', 'Southern Hemisphere'],
            ['北半球', 'きたはんきゅう', 'kita hankyuu', 'belahan bumi utara', 'Northern Hemisphere'],
            ['常識', 'じょうしき', 'joushiki', 'akal sehat, kebiasaan umum', 'common sense, common practice'],
            ['差別', 'さべつ', 'sabetsu', 'diskriminasi', 'discrimination'],
            ['平等[な]', 'びょうどう[な]', 'byoudou', 'setara, sama rata', 'equal'],
            ['位置', 'いち', 'ichi', 'posisi, letak', 'position, location'],
            ['人間', 'にんげん', 'ningen', 'manusia', 'person, human being'],
            ['観察する', 'かんさつする', 'kansatsu suru', 'mengamati', 'to observe'],
            ['面', 'めん', 'men', 'permukaan, sisi', 'surface'],
            ['中央', 'ちゅうおう', 'chuuou', 'tengah, pusat', 'center'],
            ['自然に', 'しぜんに', 'shizen ni', 'secara alami', 'naturally'],
            ['努力する', 'どりょくする', 'doryoku suru', 'berusaha, berupaya', 'to make an effort'],
            ['そこで', 'そこで', 'sokode', 'oleh karena itu, maka', 'so'],
            ['普通', 'ふつう', 'futsuu', 'biasanya, umumnya', 'usually'],
            ['経緯度', 'けいいど', 'keiido', 'garis bujur dan lintang', 'latitude and longitude'],
            ['無意識に', 'むいしきに', 'muishiki ni', 'tanpa sadar', 'unconsciously'],
            ['表れ', 'あらわれ', 'araware', 'tanda, perwujudan', 'sign, manifestation'],
            ['上下', 'じょうげ', 'jouge', 'atas bawah, vertikal', 'top and bottom, vertically'],
            ['左右', 'さゆう', 'sayuu', 'kiri kanan, horizontal', 'left and right, laterally'],
            ['少なくとも', 'すくなくとも', 'sukunakutomo', 'sedikitnya, setidaknya', 'at least'],
            ['文句', 'もんく', 'monku', 'keluhan', 'complaint'],
            ['わざと', 'わざと', 'wazato', 'dengan sengaja', 'deliberately'],
            ['経度', 'けいど', 'keido', 'garis bujur', 'longitude'],
            ['緯度', 'いど', 'ido', 'garis lintang', 'latitude'],
            ['使用する', 'しようする', 'shiyou suru', 'menggunakan', 'to use'],
            ['東西', 'とうざい', 'touzai', 'timur dan barat', 'East and West'],

            // Tambahan dari lembar latihan (tata bahasa, bicara-dengar, baca-tulis, soal)
            ['行き方', 'いきかた', 'ikikata', 'cara pergi, rute', 'way to get somewhere'],
            ['尋ねる', 'たずねる', 'tazuneru', 'bertanya, menanyakan', 'to ask, to inquire'],
            ['そのころ', 'そのころ', 'sono koro', 'pada waktu itu', 'at that time'],
            ['食欲', 'しょくよく', 'shokuyoku', 'nafsu makan', 'appetite'],
            ['診る', 'みる', 'miru shinsatsu', 'memeriksa (dokter)', 'to examine (a patient)'],
            ['なかなか〜ない', 'なかなか〜ない', 'nakanaka nai', 'tidak mudah ~, tidak kunjung ~', 'not easily ~, hardly ever ~'],
            ['携帯電話', 'けいたいでんわ', 'keitai denwa', 'telepon genggam', 'mobile phone'],
            ['しまう', 'しまう', 'shimau', 'menyimpan, memasukkan ke tempatnya', 'to put away'],
            ['アクセス', 'アクセス', 'akusesu', 'akses, jalur menuju suatu tempat', 'access'],
            ['地球環境', 'ちきゅうかんきょう', 'chikyuu kankyou', 'lingkungan bumi', 'global environment'],
            ['内容', 'ないよう', 'naiyou', 'isi, kandungan', 'content'],
            ['世界地図', 'せかいちず', 'sekai chizu', 'peta dunia', 'world map'],
            ['びっくりする', 'びっくりする', 'bikkuri suru', 'terkejut', 'to be surprised'],
            ['カーナビ', 'カーナビ', 'kaanabi', 'navigasi mobil (GPS)', 'car navigation system'],
            ['地形図', 'ちけいず', 'chikeizu', 'peta topografi', 'topographic map'],
            ['元', 'もと', 'moto', 'tempat semula, keadaan semula', 'original place, former state'],
            ['けんか', 'けんか', 'kenka', 'pertengkaran', 'quarrel'],
            ['原因', 'げんいん', 'genin', 'penyebab', 'cause'],
            ['電話代', 'でんわだい', 'denwadai', 'biaya telepon', 'telephone charges'],
            ['見やすい', 'みやすい', 'miyasui', 'mudah dilihat', 'easy to see'],

            // Ungkapan
            ['〜から、〜てください。', '〜から、〜てください。', 'kara tekudasai', 'Dari ~, tolong ~ (menjelaskan rute lewat patokan).', 'Describing a route by landmarks'],
        ];
    }
}
