<?php

namespace Database\Seeders;

/**
 * N3 Pelajaran 8: kosakata (ciri-ciri, pola pakaian, barang hilang dan tema).
 *
 *   php artisan db:seed --class=N3Lesson8Seeder
 */
class N3Lesson8Seeder extends N3LessonSeeder
{
    protected function unitOrder(): int
    {
        return 8;
    }

    protected function words(): array
    {
        return [
            ['眠る', 'ねむる', 'nemuru', 'tidur', 'to sleep'],
            ['黙る', 'だまる', 'damaru', 'diam', 'to remain silent'],
            ['取る[ノートを〜]', 'とる[ノートを〜]', 'toru (nooto)', 'mencatat [catatan]', 'to take [notes]'],
            ['盗む', 'ぬすむ', 'nusumu', 'mencuri', 'to steal'],
            ['焦げる', 'こげる', 'kogeru', 'gosong', 'to burn, to get scorched'],
            ['枯れる', 'かれる', 'kareru', 'layu, mengering', 'to wither'],
            ['平凡[な]', 'へいぼん[な]', 'heibon', 'biasa-biasa saja', 'commonplace'],
            ['人生', 'じんせい', 'jinsei', 'hidup, kehidupan', 'life'],
            ['免許', 'めんきょ', 'menkyo', 'surat izin, SIM', 'license'],
            ['取る[免許を〜]', 'とる[めんきょを〜]', 'toru (menkyo)', 'memperoleh [SIM]', 'to get [a license]'],
            ['退職する', 'たいしょくする', 'taishoku suru', 'pensiun, berhenti kerja', 'to retire'],
            ['もったいない', 'もったいない', 'mottainai', 'sayang (mubazir)', 'wasteful'],
            ['鍋', 'なべ', 'nabe', 'panci', 'pot'],
            ['ことば遣い', 'ことばづかい', 'kotoba zukai', 'tutur kata', 'way of talking'],
            ['生', 'なま', 'nama', 'mentah, segar', 'raw'],
            ['専門的[な]', 'せんもんてき[な]', 'senmonteki', 'bersifat khusus, profesional', 'specialized'],
            ['社会勉強', 'しゃかいべんきょう', 'shakai benkyou', 'belajar tentang masyarakat', 'learning about the world'],
            ['高校生', 'こうこうせい', 'koukousei', 'siswa SMA', 'high-school student'],
            ['迷子', 'まいご', 'maigo', 'anak hilang, tersesat', 'lost child'],
            ['しま', 'しま', 'shima', 'corak garis', 'stripe'],
            ['花柄', 'はながら', 'hanagara', 'motif bunga', 'floral pattern'],
            ['チェック', 'チェック', 'chekku', 'kotak-kotak, pemeriksaan', 'check'],
            ['スカート', 'スカート', 'sukaato', 'rok', 'skirt'],
            ['無地', 'むじ', 'muji', 'polos', 'unpatterned'],
            ['水玉', 'みずたま', 'mizutama', 'motif polkadot', 'polka dots'],
            ['リュック', 'リュック', 'ryukku', 'ransel', 'rucksack'],
            ['背負う', 'せおう', 'seou', 'memanggul di punggung', 'to carry on ones back'],
            ['サービスカウンター', 'サービスカウンター', 'saabisu kauntaa', 'konter layanan', 'service counter'],
            ['姪', 'めい', 'mei', 'keponakan perempuan', 'niece'],
            ['特徴', 'とくちょう', 'tokuchou', 'ciri, karakteristik', 'characteristic'],
            ['身長', 'しんちょう', 'shinchou', 'tinggi badan', 'height'],
            ['ジーンズ', 'ジーンズ', 'jiinzu', 'jins', 'jeans'],
            ['髪型', 'かみがた', 'kamigata', 'model rambut', 'hairstyle'],
            ['肩', 'かた', 'kata', 'bahu', 'shoulder'],
            ['持ち物', 'もちもの', 'mochimono', 'barang bawaan', 'personal belongings'],
            ['水色', 'みずいろ', 'mizuiro', 'biru muda', 'pale blue'],
            ['折りたたみ', 'おりたたみ', 'oritatami', 'lipat', 'folding'],
            ['青地', 'あおじ', 'aoji', 'dasar biru', 'blue background'],
            ['〜地', '〜じ', 'ji', 'dasar ~ (warna latar)', 'background'],
            ['持つところ', 'もつところ', 'motsu tokoro', 'pegangan', 'handle'],
            ['プラスチック', 'プラスチック', 'purasuchikku', 'plastik', 'plastic'],
            ['途上国', 'とじょうこく', 'tojoukoku', 'negara berkembang', 'developing country'],
            ['先進国', 'せんしんこく', 'senshinkoku', 'negara maju', 'developed country'],
            ['プラス', 'プラス', 'purasu', 'keuntungan, plus', 'advantage'],
            ['マイナス', 'マイナス', 'mainasu', 'kerugian, minus', 'disadvantage'],
            ['共通', 'きょうつう', 'kyoutsuu', 'kesamaan, umum', 'common'],
            ['関心', 'かんしん', 'kanshin', 'minat, perhatian', 'interest'],
            ['多様化', 'たようか', 'tayouka', 'diversifikasi', 'diversification'],
            ['タイトル', 'タイトル', 'taitoru', 'judul', 'title'],
            ['反対に', 'はんたいに', 'hantai ni', 'sebaliknya', 'conversely'],
            ['前後', 'ぜんご', 'zengo', 'depan dan belakang', 'front and back'],
            ['対象', 'たいしょう', 'taishou', 'sasaran, objek', 'subject'],
            ['少女', 'しょうじょ', 'shoujo', 'gadis kecil', 'young girl'],
            ['アイディア', 'アイディア', 'aidia', 'ide', 'idea'],
            ['輝く', 'かがやく', 'kagayaku', 'bersinar, berkilau', 'to shine, to sparkle'],
            ['浮力', 'ふりょく', 'furyoku', 'daya apung', 'buoyancy'],
            ['少年', 'しょうねん', 'shounen', 'anak laki-laki', 'boy'],
            ['キノコ雲', 'キノコぐも', 'kinoko gumo', 'awan jamur', 'mushroom cloud'],
            ['時に', 'ときに', 'toki ni', 'kadang-kadang', 'sometimes'],
            ['ダメージ', 'ダメージ', 'dameeji', 'kerusakan', 'damage'],
            ['与える[ダメージを〜]', 'あたえる[ダメージを〜]', 'ataeru', 'memberi [kerusakan]', 'to cause [damage]'],
            ['ひげ', 'ひげ', 'hige', 'janggut, kumis', 'beard'],
            ['伸びる', 'のびる', 'nobiru', 'tumbuh memanjang', 'to grow'],
            ['発展する', 'はってんする', 'hatten suru', 'berkembang', 'to develop'],
            ['魅力', 'みりょく', 'miryoku', 'daya tarik', 'attractiveness'],
            ['豊か[な]', 'ゆたか[な]', 'yutaka', 'makmur, kaya', 'affluent'],
            ['受ける[ダメージを〜]', 'うける[ダメージを〜]', 'ukeru', 'menerima [kerusakan]', 'to sustain [damage]'],
            ['テーマ', 'テーマ', 'teema', 'tema', 'topic'],
            ['述べる', 'のべる', 'noberu', 'menyatakan, mengemukakan', 'to state, to speak'],

            // Tambahan dari lembar latihan (tata bahasa, bicara-dengar, baca-tulis, soal)
            ['日本中', 'にほんじゅう', 'nihonjuu', 'seluruh Jepang', 'all over Japan'],
            ['違い', 'ちがい', 'chigai', 'perbedaan', 'difference'],
            ['時と場合', 'ときとばあい', 'toki to baai', 'waktu dan keadaan', 'time and occasion'],
            ['履く[スカートを〜]', 'はく[スカートを〜]', 'haku', 'memakai [rok, celana, sepatu]', 'to wear [skirt, trousers, shoes]'],
            ['かぶる[帽子を〜]', 'かぶる[ぼうしを〜]', 'kaburu', 'memakai [topi]', 'to wear [a hat]'],
            ['捜す', 'さがす', 'sagasu', 'mencari (barang atau orang yang hilang)', 'to search for (something lost)'],
            ['いなくなる', 'いなくなる', 'inakunaru', 'menghilang, tidak ada lagi', 'to go missing'],
            ['イントネーション', 'イントネーション', 'intoneeshon', 'intonasi', 'intonation'],
            ['イメージ', 'イメージ', 'imeeji', 'citra, kesan', 'image'],
            ['科学技術', 'かがくぎじゅつ', 'kagaku gijutsu', 'sains dan teknologi', 'science and technology'],
            ['夕刊', 'ゆうかん', 'yuukan', 'koran sore', 'evening paper'],
            ['一部', 'いちぶ', 'ichibu', 'sebagian, satu bagian', 'a part, one copy'],
            ['改変する', 'かいへんする', 'kaihen suru', 'mengubah, merevisi', 'to alter'],
            ['掲載する', 'けいさいする', 'keisai suru', 'memuat (di media)', 'to publish, to carry (an article)'],
            ['預かる', 'あずかる', 'azukaru', 'menitipkan, dititipi', 'to look after, to keep'],
            ['左手', 'ひだりて', 'hidarite', 'tangan kiri', 'left hand'],
            ['交通事故', 'こうつうじこ', 'koutsuu jiko', 'kecelakaan lalu lintas', 'traffic accident'],
            ['ふだん', 'ふだん', 'fudan', 'biasanya, sehari-hari', 'usually, ordinarily'],
            ['健康的[な]', 'けんこうてき[な]', 'kenkouteki', 'sehat', 'healthy'],
            ['人間関係', 'にんげんかんけい', 'ningen kankei', 'hubungan antar manusia', 'human relations'],
            ['理想的[な]', 'りそうてき[な]', 'risouteki', 'ideal', 'ideal'],
            ['外国人', 'がいこくじん', 'gaikokujin', 'orang asing', 'foreigner'],
            ['考え方', 'かんがえかた', 'kangaekata', 'cara berpikir', 'way of thinking'],

            // Ungkapan
            ['確か、〜たと思います。', 'たしか、〜たとおもいます。', 'tashika ta to omoimasu', 'Seingat saya, ~.', 'I am pretty sure... was...'],
        ];
    }
}
