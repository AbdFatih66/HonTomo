<?php

namespace Database\Seeders;

/**
 * N3 Pelajaran 6: kosakata (bisnis, negosiasi, seminar dan rencana).
 *
 *   php artisan db:seed --class=N3Lesson6Seeder
 */
class N3Lesson6Seeder extends N3LessonSeeder
{
    protected function unitOrder(): int
    {
        return 6;
    }

    protected function words(): array
    {
        return [
            ['一期一会', 'いちごいちえ', 'ichigo ichie', 'sekali seumur hidup (hargai setiap pertemuan)', 'treasure each moment as once in a lifetime'],
            ['フクロウ', 'フクロウ', 'fukurou', 'burung hantu', 'owl'],
            ['学ぶ', 'まなぶ', 'manabu', 'belajar', 'to study, to learn'],
            ['一生', 'いっしょう', 'isshou', 'seumur hidup', 'whole life'],
            ['店員', 'てんいん', 'tenin', 'pegawai toko', 'shop assistant'],
            ['就職する', 'しゅうしょくする', 'shuushoku suru', 'mendapat pekerjaan', 'to get a job'],
            ['自分では', 'じぶんでは', 'jibun de wa', 'kalau menurut saya sendiri', 'personally'],
            ['ゲーム', 'ゲーム', 'geemu', 'permainan, game', 'game'],
            ['うがい', 'うがい', 'ugai', 'berkumur', 'gargle'],
            ['ビタミンC', 'ビタミンシー', 'bitamin shii', 'vitamin C', 'vitamin C'],
            ['取る[ビタミンを〜]', 'とる[ビタミンを〜]', 'toru', 'mengonsumsi [vitamin]', 'to take [a vitamin]'],
            ['遠く', 'とおく', 'tooku', 'jauh, tempat yang jauh', 'far off'],
            ['太鼓', 'たいこ', 'taiko', 'genderang, taiko', 'drum'],
            ['けいこ', 'けいこ', 'keiko', 'latihan', 'training, practice, rehearsal'],
            ['サケ', 'サケ', 'sake', 'salmon', 'salmon'],
            ['着陸する', 'ちゃくりくする', 'chakuriku suru', 'mendarat', 'to land'],
            ['振る[手を〜]', 'ふる[てを〜]', 'furu', 'melambaikan [tangan]', 'to wave [a hand]'],
            ['タラップ', 'タラップ', 'tarappu', 'tangga pesawat', 'movable steps'],
            ['ようこそ', 'ようこそ', 'youkoso', 'selamat datang', 'welcome'],
            ['ビジネスマナー', 'ビジネスマナー', 'bijinesu manaa', 'etika bisnis', 'business etiquette'],
            ['セミナー', 'セミナー', 'seminaa', 'seminar, lokakarya', 'seminar, workshop'],
            ['案内', 'あんない', 'annai', 'informasi, pemberitahuan', 'information, description'],
            ['費用', 'ひよう', 'hiyou', 'biaya', 'cost'],
            ['交渉する', 'こうしょうする', 'koushou suru', 'bernegosiasi', 'to negotiate'],
            ['条件', 'じょうけん', 'jouken', 'syarat, kondisi', 'conditions'],
            ['制度', 'せいど', 'seido', 'sistem, aturan', 'system'],
            ['メンタルトレーニング', 'メンタルトレーニング', 'mentaru toreeningu', 'latihan mental', 'mental training'],
            ['取り入れる', 'とりいれる', 'toriireru', 'memasukkan, mengadopsi', 'to include, to adopt'],
            ['ビジネス', 'ビジネス', 'bijinesu', 'bisnis', 'business'],
            ['レベル', 'レベル', 'reberu', 'tingkat, level', 'level'],
            ['週', 'しゅう', 'shuu', 'minggu', 'week'],
            ['全額', 'ぜんがく', 'zengaku', 'harga penuh', 'full price'],
            ['半額', 'はんがく', 'hangaku', 'setengah harga', 'half price'],
            ['出す[費用を〜]', 'だす[ひようを〜]', 'dasu', 'mengeluarkan [biaya]', 'to allow [an expense]'],
            ['それでは', 'それでは', 'sore de wa', 'kalau begitu', 'in that case'],
            ['期間', 'きかん', 'kikan', 'jangka waktu', 'period of time'],
            ['日時', 'にちじ', 'nichiji', 'tanggal dan waktu', 'date and time'],
            ['授業料', 'じゅぎょうりょう', 'jugyouryou', 'biaya kuliah/pelajaran', 'lesson fee'],
            ['〜料', '〜りょう', 'ryou', 'biaya ~', '~ fee'],
            ['日にち', 'ひにち', 'hinichi', 'tanggal (pertemuan)', 'date of a meeting'],
            ['担当者', 'たんとうしゃ', 'tantousha', 'penanggung jawab', 'person in charge'],
            ['延期する', 'えんきする', 'enki suru', 'menunda', 'to postpone'],
            ['買い換える', 'かいかえる', 'kaikaeru', 'mengganti dengan membeli baru', 'to replace by buying new'],
            ['講演会', 'こうえんかい', 'kouenkai', 'ceramah, kuliah umum', 'lecture'],
            ['〜会[講演〜]', '〜かい[こうえん〜]', 'kai', 'pertemuan ~ [ceramah]', 'meeting'],
            ['上司', 'じょうし', 'joushi', 'atasan', 'boss'],
            ['つかむ', 'つかむ', 'tsukamu', 'menggenggam, menangkap', 'to grasp'],
            ['そのような', 'そのような', 'sono you na', 'seperti itu', 'that kind of'],
            ['想像する', 'そうぞうする', 'souzou suru', 'membayangkan', 'to imagine'],
            ['イメージする', 'イメージする', 'imeeji suru', 'membayangkan (citra)', 'to visualize'],
            ['具体的[な]', 'ぐたいてき[な]', 'gutaiteki', 'konkret, spesifik', 'specific, concrete'],
            ['理想', 'りそう', 'risou', 'cita-cita, ideal', 'ideal'],
            ['近づく', 'ちかづく', 'chikazuku', 'mendekat', 'to approach'],
            ['こそあど', 'こそあど', 'kosoado', 'kata tunjuk dan kata tanya (ko, so, a, do)', 'demonstrative and interrogative words'],
            ['指す', 'さす', 'sasu', 'menunjuk', 'to indicate, to refer to'],
            ['記者会見', 'きしゃかいけん', 'kisha kaiken', 'konferensi pers', 'press conference'],
            ['記者', 'きしゃ', 'kisha', 'wartawan', 'reporter'],
            ['会見', 'かいけん', 'kaiken', 'wawancara, jumpa pers', 'interview'],
            ['〜ごっこ', '〜ごっこ', 'gokko', 'bermain ~ (pura-pura)', 'playing at ~'],
            ['暗い[気持ちが〜]', 'くらい[きもちが〜]', 'kurai', 'murung [perasaan]', 'gloomy'],
            ['世の中', 'よのなか', 'yononaka', 'dunia, masyarakat', 'the world'],
            ['アホ', 'アホ', 'aho', 'bodoh, konyol', 'stupid, ridiculous'],
            ['見える[アホに〜]', 'みえる[アホに〜]', 'mieru', 'tampak [bodoh]', 'to appear, to seem'],
            ['ビジネスマン', 'ビジネスマン', 'bijinesuman', 'pebisnis', 'businessman'],
            ['同じような', 'おなじような', 'onaji you na', 'sejenis, serupa', 'same kind of'],
            ['閉じる', 'とじる', 'tojiru', 'menutup', 'to close'],
            ['トレーニング', 'トレーニング', 'toreeningu', 'latihan', 'training'],
            ['つまり', 'つまり', 'tsumari', 'singkatnya, dengan kata lain', 'that is to say'],
            ['過去', 'かこ', 'kako', 'masa lalu', 'past'],
            ['向き合う', 'むきあう', 'mukiau', 'berhadapan, menghadapi', 'to face one another'],
            ['そうすれば', 'そうすれば', 'sou sureba', 'kalau begitu, dengan begitu', 'if you do so'],
            ['現在', 'げんざい', 'genzai', 'sekarang, saat ini', 'present, now'],
            ['そこから', 'そこから', 'soko kara', 'dari sana', 'from there'],
            ['解決する', 'かいけつする', 'kaiketsu suru', 'menyelesaikan', 'to solve'],
            ['プラン', 'プラン', 'puran', 'rencana', 'plan'],
            ['立てる[プランを〜]', 'たてる[プランを〜]', 'tateru', 'menyusun [rencana]', 'to make [a plan]'],
            ['順番', 'じゅんばん', 'junban', 'urutan, giliran', 'order, sequence'],

            // Tambahan dari lembar latihan (tata bahasa, bicara-dengar, baca-tulis, soal)
            ['昼間', 'ひるま', 'hiruma', 'siang hari', 'daytime'],
            ['ちょうちょ', 'ちょうちょ', 'chouchou', 'kupu-kupu', 'butterfly'],
            ['バッグ', 'バッグ', 'baggu', 'tas', 'bag'],
            ['つもり貯金', 'つもりちょきん', 'tsumori chokin', 'tabungan "anggap sudah membeli"', '"as if I bought it" saving'],
            ['体操', 'たいそう', 'taisou', 'senam', 'gymnastics, exercises'],
            ['漫画', 'まんが', 'manga', 'komik', 'comic, manga'],
            ['かむ', 'かむ', 'kamu', 'mengunyah, menggigit', 'to chew, to bite'],
            ['ビタミン', 'ビタミン', 'bitamin', 'vitamin', 'vitamin'],
            ['秋祭り', 'あきまつり', 'aki matsuri', 'festival musim gugur', 'autumn festival'],
            ['やむ[雨が〜]', 'やむ[あめが〜]', 'yamu', 'berhenti [hujan]', '[rain] to stop'],
            ['欲しがる', 'ほしがる', 'hoshigaru', 'menginginkan (dikatakan untuk orang lain)', 'to want (said of others)'],

            // Ungkapan
            ['いやあ、……。', 'いやあ、……。', 'iyaa', 'Wah, tidak...', 'No...'],
            ['今ちょっとよろしいでしょうか。', 'いまちょっとよろしいでしょうか。', 'ima chotto yoroshii deshouka', 'Apakah Anda punya waktu sebentar?', 'Have you got a moment?'],
            ['実は〜のことなんですが、……。', 'じつは〜のことなんですが、……。', 'jitsu wa no koto nan desu ga', 'Sebenarnya ini soal ~...', 'As a matter of fact, it is about...'],
            ['ふうん。', 'ふうん。', 'fuun', 'Oh, begitu?', 'Oh, really?'],
            ['もし〜が無理なら、……。', 'もし〜がむりなら、……。', 'moshi ga muri nara', 'Kalau ~ terlalu sulit, ...', 'If ... is too much to ask...'],
        ];
    }
}
