<?php

namespace Database\Seeders;

/**
 * N3 Pelajaran 9: kosakata (kamus, belanja, iklim dan pengaruh budaya).
 *
 *   php artisan db:seed --class=N3Lesson9Seeder
 */
class N3Lesson9Seeder extends N3LessonSeeder
{
    protected function unitOrder(): int
    {
        return 9;
    }

    protected function words(): array
    {
        return [
            ['決まる', 'きまる', 'kimaru', 'diputuskan, ditetapkan', 'to be decided'],
            ['済む', 'すむ', 'sumu', 'selesai', 'to be over'],
            ['印鑑', 'いんかん', 'inkan', 'stempel pribadi', 'stamp, seal'],
            ['サイン', 'サイン', 'sain', 'tanda tangan', 'signature'],
            ['性能', 'せいのう', 'seinou', 'kinerja, performa', 'performance'],
            ['タイプ', 'タイプ', 'taipu', 'tipe, jenis', 'type'],
            ['機能', 'きのう', 'kinou', 'fungsi, fitur', 'function'],
            ['平日', 'へいじつ', 'heijitsu', 'hari kerja', 'weekday'],
            ['将棋', 'しょうぎ', 'shougi', 'shogi, catur Jepang', 'shogi'],
            ['自慢する', 'じまんする', 'jiman suru', 'membanggakan, menyombongkan', 'to boast about'],
            ['豚肉', 'ぶたにく', 'butaniku', 'daging babi', 'pork'],
            ['牛肉', 'ぎゅうにく', 'gyuuniku', 'daging sapi', 'beef'],
            ['バレーボール', 'バレーボール', 'bareebooru', 'bola voli', 'volleyball'],
            ['気温', 'きおん', 'kion', 'suhu udara', 'temperature'],
            ['降水量', 'こうすいりょう', 'kousuiryou', 'curah hujan', 'amount of rainfall'],
            ['月別', 'つきべつ', 'tsukibetsu', 'per bulan', 'month by month'],
            ['平均', 'へいきん', 'heikin', 'rata-rata', 'average'],
            ['予防注射', 'よぼうちゅうしゃ', 'yobou chuusha', 'suntik vaksin', 'vaccination'],
            ['国々', 'くにぐに', 'kuniguni', 'negara-negara', 'countries'],
            ['都市', 'とし', 'toshi', 'kota', 'city'],
            ['入国する', 'にゅうこくする', 'nyuukoku suru', 'memasuki negara', 'to enter a country'],
            ['資源', 'しげん', 'shigen', 'sumber daya alam', 'resources'],
            ['とれる[米が〜]', 'とれる[こめが〜]', 'toreru', 'dapat dipanen [padi/beras]', 'to be harvested'],
            ['大雪', 'おおゆき', 'ooyuki', 'hujan salju lebat', 'heavy snowfall'],
            ['乾燥する', 'かんそうする', 'kansou suru', 'mengering', 'to dry out'],
            ['道路', 'どうろ', 'douro', 'jalan raya', 'road'],
            ['どんどん', 'どんどん', 'dondon', 'dengan cepat, terus-menerus', 'rapidly'],
            ['最後', 'さいご', 'saigo', 'akhir, terakhir', 'end'],
            ['生きる', 'いきる', 'ikiru', 'hidup', 'to live'],
            ['誕生', 'たんじょう', 'tanjou', 'kelahiran', 'birth'],
            ['実現する', 'じつげんする', 'jitsugen suru', 'terwujud', 'to become a reality'],
            ['金メダル', 'きんメダル', 'kin medaru', 'medali emas', 'gold medal'],
            ['金', 'きん', 'kin', 'emas', 'gold'],
            ['メダル', 'メダル', 'medaru', 'medali', 'medal'],
            ['バスケットボール', 'バスケットボール', 'basukettobooru', 'bola basket', 'basketball'],
            ['選手', 'せんしゅ', 'senshu', 'atlet, pemain', 'player, athlete'],
            ['シンプル[な]', 'シンプル[な]', 'shinpuru', 'sederhana', 'simple'],
            ['書き込み', 'かきこみ', 'kakikomi', 'tulisan, catatan tambahan', 'writing'],
            ['検索', 'けんさく', 'kensaku', 'pencarian', 'retrieval, search'],
            ['例文', 'れいぶん', 'reibun', 'contoh kalimat', 'example sentence'],
            ['ジャンプ機能', 'ジャンプきのう', 'janpu kinou', 'fitur lompat (kamus elektronik)', 'jump function of electronic dictionaries'],
            ['ジャンプ', 'ジャンプ', 'janpu', 'lompatan', 'jump'],
            ['商品', 'しょうひん', 'shouhin', 'barang dagangan, produk', 'product, merchandise'],
            ['〜社', '〜しゃ', 'sha', 'perusahaan ~', 'Co.'],
            ['国語辞書', 'こくごじしょ', 'kokugo jisho', 'kamus bahasa Jepang', 'Japanese dictionary'],
            ['和英辞書', 'わえいじしょ', 'waei jisho', 'kamus Jepang-Inggris', 'Japanese-English dictionary'],
            ['載る[例文が〜]', 'のる[れいぶんが〜]', 'noru', 'dimuat [contoh kalimat]', 'to be given'],
            ['シルバー', 'シルバー', 'shirubaa', 'perak, lansia', 'silver'],
            ['付け加える', 'つけくわえる', 'tsukekuwaeru', 'menambahkan', 'to add to'],
            ['編集する', 'へんしゅうする', 'henshuu suru', 'menyunting', 'to edit'],
            ['しっかり', 'しっかり', 'shikkari', 'dengan kuat, dengan benar', 'reliably, properly'],
            ['留守番をする', 'るすばんをする', 'rusuban o suru', 'menjaga rumah saat semua pergi', 'to look after the house while others are out'],
            ['柄', 'がら', 'gara', 'corak, motif', 'pattern'],
            ['共通語', 'きょうつうご', 'kyoutsuugo', 'bahasa umum', 'common language'],
            ['演奏', 'えんそう', 'ensou', 'permainan musik, pertunjukan', 'performance'],
            ['特許', 'とっきょ', 'tokkyo', 'paten', 'patent'],
            ['倒産', 'とうさん', 'tousan', 'kebangkrutan', 'bankruptcy'],
            ['大金持ち', 'おおがねもち', 'oogane mochi', 'orang kaya raya', 'millionaire'],
            ['誇る', 'ほこる', 'hokoru', 'membanggakan', 'to be proud of'],
            ['表れる', 'あらわれる', 'arawareru', 'tampak, terlihat', 'to be revealed in'],
            ['今では', 'いまでは', 'ima de wa', 'sekarang, kini', 'now, today'],
            ['TSUNAMI', 'ツナミ', 'tsunami', 'tsunami', 'tsunami'],
            ['影響', 'えいきょう', 'eikyou', 'pengaruh', 'influence'],
            ['有名人', 'ゆうめいじん', 'yuumeijin', 'orang terkenal', 'famous person'],
            ['録音する', 'ろくおんする', 'rokuon suru', 'merekam suara', 'to record'],
            ['ヒント', 'ヒント', 'hinto', 'petunjuk', 'hint'],
            ['貸し出す', 'かしだす', 'kashidasu', 'meminjamkan, menyewakan', 'to rent out'],
            ['ところが', 'ところが', 'tokoroga', 'akan tetapi', 'however'],
            ['競争', 'きょうそう', 'kyousou', 'persaingan', 'competition'],
            ['性別', 'せいべつ', 'seibetsu', 'jenis kelamin', 'sex, gender'],
            ['地域', 'ちいき', 'chiiki', 'daerah, wilayah', 'region'],
            ['関係なく', 'かんけいなく', 'kankei naku', 'tanpa memandang', 'regardless of'],
            ['娯楽', 'ごらく', 'goraku', 'hiburan', 'amusement'],
            ['[お]年寄り', '[お]としより', 'toshiyori', 'orang lanjut usia', 'elderly person'],
            ['仲間', 'なかま', 'nakama', 'teman, kawan', 'friend, companion'],
            ['心', 'こころ', 'kokoro', 'hati, pikiran', 'mind'],
            ['治す', 'なおす', 'naosu', 'menyembuhkan', 'to cure'],
            ['単なる', 'たんなる', 'tannaru', 'hanya, sekadar', 'mere'],
            ['きっかけ', 'きっかけ', 'kikkake', 'pemicu, awal mula', 'reason, catalyst'],
            ['交流協会', 'こうりゅうきょうかい', 'kouryuu kyoukai', 'asosiasi pertukaran', 'exchange association'],
            ['広報誌', 'こうほうし', 'kouhoushi', 'majalah humas, buletin', 'public relations magazine'],
            ['暮らし', 'くらし', 'kurashi', 'kehidupan sehari-hari', 'life'],
            ['役立つ', 'やくだつ', 'yakudatsu', 'berguna', 'to be useful for'],
            ['参加者', 'さんかしゃ', 'sankasha', 'peserta', 'participant'],

            // Tambahan dari bacaan dan latihan di buku
            ['勤める', 'つとめる', 'tsutomeru', 'bekerja (di perusahaan)', 'to work for'],
            ['減る', 'へる', 'heru', 'berkurang', 'to decrease'],
            ['勝つ', 'かつ', 'katsu', 'menang', 'to win'],
            ['並ぶ', 'ならぶ', 'narabu', 'berjajar, sejajar', 'to line up, to rank alongside'],
            ['技術', 'ぎじゅつ', 'gijutsu', 'teknologi, keterampilan teknis', 'technology, technique'],
            ['開く[店を〜]', 'ひらく[みせを〜]', 'hiraku', 'membuka [toko]', 'to open [a shop]'],
            ['踊る', 'おどる', 'odoru', 'menari', 'to dance'],
            ['事実', 'じじつ', 'jijitsu', 'fakta', 'fact'],
            ['比較する', 'ひかくする', 'hikaku suru', 'membandingkan', 'to compare'],
            ['選ぶ', 'えらぶ', 'erabu', 'memilih', 'to choose'],
            ['戻る', 'もどる', 'modoru', 'kembali', 'to return'],
            ['生まれる', 'うまれる', 'umareru', 'lahir', 'to be born'],
            ['売れる', 'うれる', 'ureru', 'laris, terjual', 'to sell well'],

            // Ungkapan
            ['こうやって', 'こうやって', 'kou yatte', 'dengan cara begini', 'Like this...'],
            ['〜だけじゃなくて、〜のがいいんですが……。', '〜だけじゃなくて、〜のがいいんですが……。', 'dake janakute no ga ii n desu ga', 'Tidak hanya ~, tetapi yang ~ lebih bagus...', 'Not just..., but... would be good'],
            ['それでしたら、〜(の)がよろしいんじゃないでしょうか。', 'それでしたら、〜(の)がよろしいんじゃないでしょうか。', 'sore deshitara no ga yoroshiin janai deshouka', 'Kalau begitu, bukankah ~ lebih baik?', 'In that case, would not ~ be best?'],
            ['ほとんど変わりませんね。', 'ほとんどかわりませんね。', 'hotondo kawarimasen ne', 'Hampir tidak ada bedanya.', 'There is hardly any difference'],
            ['で、〜はありませんか。', 'で、〜はありませんか。', 'de wa arimasen ka', 'Lalu, apakah ada ~?', 'Do you have... with...?'],
        ];
    }
}
