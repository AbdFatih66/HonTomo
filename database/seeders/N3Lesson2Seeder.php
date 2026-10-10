<?php

namespace Database\Seeders;

/**
 * N3 Pelajaran 2: kosakata (kata serapan, lingkungan, surel, kehidupan kerja).
 *
 *   php artisan db:seed --class=N3Lesson2Seeder
 */
class N3Lesson2Seeder extends N3LessonSeeder
{
    protected function unitOrder(): int
    {
        return 2;
    }

    protected function words(): array
    {
        return [
            ['ふく[ガラスを〜]', 'ふく[ガラスを〜]', 'fuku', 'mengelap [kaca]', 'to wipe [glass]'],
            ['結果', 'けっか', 'kekka', 'hasil', 'result'],
            ['外来語', 'がいらいご', 'gairaigo', 'kata serapan', 'loanword'],
            ['守る[地球を〜]', 'まもる[ちきゅうを〜]', 'mamoru', 'melindungi [bumi]', 'to protect [the earth]'],
            ['ソフトウェア', 'ソフトウェア', 'sofutowea', 'perangkat lunak', 'software'],
            ['メール', 'メール', 'meeru', 'surel, email', 'e-mail'],
            ['郵便', 'ゆうびん', 'yuubin', 'pos, surat', 'postal mail'],
            ['Eメール', 'イーメール', 'iimeeru', 'surel, email', 'e-mail'],
            ['プレイガイド', 'プレイガイド', 'pureigaido', 'agen tiket pertunjukan', 'theater ticket agency'],
            ['栄養', 'えいよう', 'eiyou', 'gizi, nutrisi', 'nutrition'],
            ['カロリー', 'カロリー', 'karorii', 'kalori', 'calorie'],
            ['エコ', 'エコ', 'eko', 'ramah lingkungan', 'eco, concern for the environment'],
            ['環境', 'かんきょう', 'kankyou', 'lingkungan', 'environment'],
            ['アポ', 'アポ', 'apo', 'janji temu', 'appointment'],
            ['省エネ', 'しょうエネ', 'shouene', 'hemat energi', 'energy-saving'],
            ['学習する', 'がくしゅうする', 'gakushuu suru', 'belajar', 'to learn'],
            ['記事', 'きじ', 'kiji', 'artikel (koran, majalah)', 'article'],
            ['分ける[ごみを〜]', 'わける', 'wakeru', 'memilah [sampah]', 'to separate [rubbish]'],
            ['うわさ', 'うわさ', 'uwasa', 'gosip, kabar burung', 'rumor'],
            ['辺り', 'あたり', 'atari', 'sekitar, daerah', 'area'],
            ['アドバイス', 'アドバイス', 'adobaisu', 'saran', 'advice'],
            ['事件', 'じけん', 'jiken', 'kejadian, kasus', 'incident'],
            ['奪う', 'うばう', 'ubau', 'merampas, mencuri', 'to take, to snatch'],
            ['干す', 'ほす', 'hosu', 'menjemur, mengeringkan', 'to dry'],
            ['以外', 'いがい', 'igai', 'selain, kecuali', 'apart from, except'],
            ['つく[うそを〜]', 'つく[うそを〜]', 'tsuku', 'berbohong', 'to tell [a lie]'],
            ['ロボット', 'ロボット', 'robotto', 'robot', 'robot'],
            ['本物', 'ほんもの', 'honmono', 'barang asli', 'real thing'],
            ['飛ぶ[空を〜]', 'とぶ[そらを〜]', 'tobu', 'terbang [di langit]', 'to fly [in the sky]'],
            ['オレンジ', 'オレンジ', 'orenji', 'oranye, jeruk', 'orange'],
            ['パジャマ', 'パジャマ', 'pajama', 'piyama', 'pajamas'],
            ['四角い', 'しかくい', 'shikakui', 'berbentuk persegi', 'square (in shape)'],
            ['腕', 'うで', 'ude', 'lengan', 'arm'],
            ['つける[腕に〜]', 'つける[うでに〜]', 'tsukeru', 'memakai [di lengan]', 'to wear on [the arm]'],
            ['ふるさと', 'ふるさと', 'furusato', 'kampung halaman', 'hometown'],
            ['話しかける', 'はなしかける', 'hanashikakeru', 'menyapa, mengajak bicara', 'to speak to, to address'],
            ['不在連絡票', 'ふざいれんらくひょう', 'fuzai renrakuhyou', 'pemberitahuan paket tidak terkirim', 'attempted delivery notice'],
            ['〜宅', '〜たく', 'taku', 'kediaman ~', 'home, residence'],
            ['工事', 'こうじ', 'kouji', 'pekerjaan konstruksi', 'construction'],
            ['休日', 'きゅうじつ', 'kyuujitsu', 'hari libur', 'holiday, day off'],
            ['断水', 'だんすい', 'dansui', 'pemutusan air', 'interruption to water supply'],
            ['リモコン', 'リモコン', 'rimokon', 'remote control', 'remote control'],
            ['ロボコン', 'ロボコン', 'robokon', 'kontes robot', 'robot contest'],
            ['苦手[な]', 'にがて[な]', 'nigate', 'kelemahan, hal yang tidak dikuasai', 'weak point'],
            ['紛らわしい', 'まぎらわしい', 'magirawashii', 'membingungkan', 'confusing'],
            ['正確[な]', 'せいかく[な]', 'seikaku', 'akurat, tepat', 'accurate'],
            ['バランス', 'バランス', 'baransu', 'keseimbangan', 'balance'],
            ['引く[線を〜]', 'ひく[せんを〜]', 'hiku', 'menarik [garis]', 'to draw [a line]'],
            ['筆者', 'ひっしゃ', 'hissha', 'penulis (suatu tulisan)', 'writer, author of a piece'],
            ['いまだに', 'いまだに', 'imadani', 'sampai sekarang pun', 'even now'],
            ['とんでもない', 'とんでもない', 'tondemonai', 'mana mungkin!, sama sekali tidak', 'nothing of the sort'],
            ['宇宙人', 'うちゅうじん', 'uchuujin', 'makhluk luar angkasa', 'alien'],
            ['全く', 'まったく', 'mattaku', 'sama sekali, benar-benar', 'entirely'],
            ['別の', 'べつの', 'betsu no', 'lain, berbeda', 'another, different'],
            ['〜自身', '〜じしん', 'jishin', '~ sendiri', 'oneself'],
            ['友人', 'ゆうじん', 'yuujin', 'teman', 'friend'],
            ['また', 'また', 'mata', 'lagi pula, selain itu', 'moreover'],
            ['ライス', 'ライス', 'raisu', 'nasi (hidangan ala Barat)', 'cooked rice served with a Western meal'],
            ['アドレス', 'アドレス', 'adoresu', 'alamat (termasuk alamat email)', 'address'],
            ['メールアドレス', 'メールアドレス', 'meeru adoresu', 'alamat email', 'e-mail address'],
            ['プレゼン', 'プレゼン', 'purezen', 'presentasi', 'presentation'],
            ['アイデンティティ', 'アイデンティティ', 'aidentiti', 'identitas', 'identity'],
            ['コンプライアンス', 'コンプライアンス', 'konpuraiansu', 'kepatuhan (aturan)', 'compliance'],
            ['例えば', 'たとえば', 'tatoeba', 'misalnya', 'for example'],
            ['ポリシー', 'ポリシー', 'porishii', 'kebijakan, prinsip', 'policy, principles'],
            ['場合', 'ばあい', 'baai', 'kasus, keadaan', 'case, situation'],
            ['%', 'パーセント', 'paasento', 'persen', 'percent'],
            ['普通に', 'ふつうに', 'futsuu ni', 'biasanya, secara wajar', 'usually'],
            ['いまさら', 'いまさら', 'imasara', 'baru sekarang (sudah terlambat)', 'at this late stage'],
            ['必要', 'ひつよう', 'hitsuyou', 'keperluan, kebutuhan', 'necessity'],
            ['なくてはならない', 'なくてはならない', 'nakutewanaranai', 'tidak boleh tidak, harus ada', 'indispensable'],
            ['取る[バランスを〜]', 'とる[バランスを〜]', 'toru', 'menjaga [keseimbangan]', 'to achieve [a balance]'],
            ['文章', 'ぶんしょう', 'bunshou', 'tulisan, kalimat', 'essay, sentence'],
            ['比べる', 'くらべる', 'kuraberu', 'membandingkan', 'to compare'],

            // Ungkapan
            ['お忙しいところ、……。', 'おいそがしいところ、……。', 'oisogashii tokoro', 'Maaf mengganggu kesibukan Anda...', 'I am sorry to disturb you...'],
            ['それで……。', 'それで……。', 'sorede', 'Lalu...? (mendorong lawan bicara melanjutkan)', 'And...?'],
            ['僕自身もそうだけど、……。', 'ぼくじしんもそうだけど、……。', 'boku jishin mo sou dakedo', 'Saya sendiri pun begitu, sih...', 'The same goes for me too, though'],
            ['何が何だかわからない。', 'なにがなんだかわからない。', 'nani ga nan da ka wakaranai', 'Saya tidak mengerti apa pun.', 'I do not know what is what'],
        ];
    }
}
