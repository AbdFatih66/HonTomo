<?php

namespace Database\Seeders;

/**
 * N3 Pelajaran 1: kosakata (rumah gaya Jepang, tatami, kehidupan sehari-hari).
 *
 *   php artisan db:seed --class=N3Lesson1Seeder
 */
class N3Lesson1Seeder extends N3LessonSeeder
{
    protected function unitOrder(): int
    {
        return 1;
    }

    protected function words(): array
    {
        return [
            ['どのように', 'どのように', 'dono you ni', 'bagaimana, dengan cara apa', 'how'],
            ['迷う[道に〜]', 'まよう[みちに〜]', 'mayou', 'tersesat [di jalan]', 'to get lost [on the way]'],
            ['先輩', 'せんぱい', 'senpai', 'senior (di sekolah, kantor)', 'senior (student, colleague)'],
            ['まるで', 'まるで', 'marude', 'seolah-olah, persis seperti', 'just like, as if'],
            ['明るい[性格が〜]', 'あかるい[せいかくが〜]', 'akarui', 'ceria [watak]', 'cheerful [personality]'],
            ['父親', 'ちちおや', 'chichioya', 'ayah', 'father'],
            ['湖', 'みずうみ', 'mizuumi', 'danau', 'lake'],
            ['目指す', 'めざす', 'mezasu', 'mengincar, bertujuan', 'to aim at'],
            ['命', 'いのち', 'inochi', 'nyawa, hidup', 'life'],
            ['おせち料理', 'おせちりょうり', 'osechi ryouri', 'hidangan tradisional Tahun Baru', 'traditional New Year food'],
            ['初詣で', 'はつもうで', 'hatsumoude', 'kunjungan pertama ke kuil di Tahun Baru', 'first shrine visit of the New Year'],
            ['畳', 'たたみ', 'tatami', 'tikar tatami', 'tatami mat'],
            ['座布団', 'ざぶとん', 'zabuton', 'bantal duduk', 'floor cushion'],
            ['床', 'ゆか', 'yuka', 'lantai', 'floor'],
            ['正座', 'せいざ', 'seiza', 'duduk bersimpuh secara formal', 'formal kneeling position'],
            ['おじぎ', 'おじぎ', 'ojigi', 'membungkuk (memberi salam)', 'bow (greeting)'],
            ['作家', 'さっか', 'sakka', 'penulis, pengarang', 'writer, author'],
            ['〜中[留守〜]', '〜ちゅう[るす〜]', 'chuu', 'selama ~ [selama tidak di rumah]', 'while [while out]'],
            ['いっぱい', 'いっぱい', 'ippai', 'penuh, ramai', 'full, crowded'],
            ['どんなに', 'どんなに', 'donna ni', 'betapa pun', 'however, no matter how'],
            ['立派[な]', 'りっぱ[な]', 'rippa', 'hebat, megah', 'wonderful, grand'],
            ['欠点', 'けってん', 'ketten', 'kekurangan, kelemahan', 'shortcoming'],
            ['〜過ぎ', '〜すぎ', 'sugi', 'lewat dari ~', 'past, after'],
            ['似合う', 'にあう', 'niau', 'cocok (dipakai)', 'to suit, to look good in'],
            ['使い分ける', 'つかいわける', 'tsukaiwakeru', 'menggunakan sesuai keperluan', 'to use selectively'],
            ['良さ', 'よさ', 'yosa', 'kebaikan, keunggulan', 'merit, good quality'],
            ['読み取る', 'よみとる', 'yomitoru', 'memahami dari bacaan', 'to understand by reading'],
            ['旅行者', 'りょこうしゃ', 'ryokousha', 'wisatawan', 'tourist, traveller'],
            ['〜者', '〜しゃ', 'sha', 'orang yang ~', '-er (person)'],
            ['最も', 'もっとも', 'mottomo', 'paling', 'most'],
            ['非常に', 'ひじょうに', 'hijou ni', 'sangat, amat', 'very, extremely'],
            ['それほど', 'それほど', 'sorehodo', 'sampai segitu', 'to that extent'],
            ['代表する', 'だいひょうする', 'daihyou suru', 'mewakili', 'to represent'],
            ['全体', 'ぜんたい', 'zentai', 'keseluruhan', 'whole'],
            ['敷く', 'しく', 'shiku', 'membentangkan (tikar, kasur)', 'to lay out'],
            ['ちょうど', 'ちょうど', 'choudo', 'tepat, persis', 'just, exactly'],
            ['何枚も', 'なんまいも', 'nanmai mo', 'berlembar-lembar', 'many (flat objects)'],
            ['つける[名前を〜]', 'つける[なまえを〜]', 'tsukeru', 'memberi [nama]', 'to give [a name]'],
            ['やまとことば', 'やまとことば', 'yamato kotoba', 'kata asli bahasa Jepang', 'word of purely Japanese origin'],
            ['動かす', 'うごかす', 'ugokasu', 'menggerakkan', 'to move'],
            ['組み合わせる', 'くみあわせる', 'kumiawaseru', 'menggabungkan', 'to combine'],
            ['客間', 'きゃくま', 'kyakuma', 'ruang tamu', 'drawing room'],
            ['居間', 'いま', 'ima', 'ruang keluarga', 'living room'],
            ['仕事部屋', 'しごとべや', 'shigoto beya', 'ruang kerja', 'workroom, study'],
            ['ワラ', 'ワラ', 'wara', 'jerami', 'straw'],
            ['イグサ', 'イグサ', 'igusa', 'rumput igusa (bahan tikar)', 'mat rush'],
            ['呼吸する', 'こきゅうする', 'kokyuu suru', 'bernapas', 'to breathe'],
            ['湿気', 'しっけ', 'shikke', 'kelembapan', 'moisture, dampness'],
            ['取る[湿気を〜]', 'とる[しっけを〜]', 'toru', 'menghilangkan [kelembapan]', 'to remove [moisture]'],
            ['快適[な]', 'かいてき[な]', 'kaiteki', 'nyaman', 'comfortable'],
            ['清潔[な]', 'せいけつ[な]', 'seiketsu', 'bersih', 'clean'],
            ['本文', 'ほんぶん', 'honbun', 'isi teks utama', 'main text'],
            ['一戸建て', 'いっこだて', 'ikkodate', 'rumah tunggal', 'detached house'],
            ['小学生', 'しょうがくせい', 'shougakusei', 'murid sekolah dasar', 'primary school student'],
            ['日常生活', 'にちじょうせいかつ', 'nichijou seikatsu', 'kehidupan sehari-hari', 'daily life'],

            // Ungkapan
            ['あのう、〜ていただけないでしょうか。', 'あのう、〜ていただけないでしょうか。', 'anou teitadakenai deshouka', 'Permisi, apakah kiranya Anda berkenan ~?', 'Excuse me, I wonder if you could possibly...?'],
            ['何とかお願いできないでしょうか。', 'なんとかおねがいできないでしょうか。', 'nantoka onegai dekinai deshouka', 'Bisakah kiranya Anda mengusahakannya?', 'Could you possibly find some way to help me?'],
            ['うちでよければどうぞ。', 'うちでよければどうぞ。', 'uchi de yokereba douzo', 'Kalau rumah kami tidak keberatan, silakan.', 'If our house is all right, please come.'],
            ['お役に立ててよかったです。', 'おやくにたててよかったです。', 'oyaku ni tatete yokatta desu', 'Syukurlah bisa membantu.', 'I am glad I was able to help.'],
            ['お預かりします。', 'おあずかりします。', 'oazukari shimasu', 'Saya yang akan menjaganya.', 'We will take care of it.'],
        ];
    }
}
