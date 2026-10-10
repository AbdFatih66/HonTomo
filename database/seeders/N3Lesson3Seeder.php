<?php

namespace Database\Seeders;

/**
 * N3 Pelajaran 3: kosakata (wawancara, kerja paruh waktu, telepon dan pesan).
 *
 *   php artisan db:seed --class=N3Lesson3Seeder
 */
class N3Lesson3Seeder extends N3LessonSeeder
{
    protected function unitOrder(): int
    {
        return 3;
    }

    protected function words(): array
    {
        return [
            ['インタビューする', 'インタビューする', 'intabyuu suru', 'mewawancarai', 'to interview'],
            ['担当する', 'たんとうする', 'tantou suru', 'bertanggung jawab atas, menangani', 'to be in charge of'],
            ['アルバイト先', 'アルバイトさき', 'arubaito saki', 'tempat kerja paruh waktu', 'part-time workplace'],
            ['〜先', '〜さき', 'saki', 'tempat tujuan, lokasi ~', 'location, destination'],
            ['店長', 'てんちょう', 'tenchou', 'manajer toko', 'store manager'],
            ['研修', 'けんしゅう', 'kenshuu', 'pelatihan', 'training'],
            ['話し合う', 'はなしあう', 'hanashiau', 'berdiskusi', 'to discuss'],
            ['通勤する', 'つうきんする', 'tsuukin suru', 'pergi-pulang kerja', 'to commute'],
            ['これまで', 'これまで', 'koremade', 'sampai sekarang', 'until now'],
            ['減らす', 'へらす', 'herasu', 'mengurangi', 'to reduce'],
            ['引っ越す', 'ひっこす', 'hikkosu', 'pindah rumah', 'to move (house)'],
            ['〜か国', '〜かこく', 'kakoku', '~ negara', '(number of) countries'],
            ['家庭', 'かてい', 'katei', 'rumah tangga, keluarga', 'household, home, family'],
            ['事情', 'じじょう', 'jijou', 'keadaan, alasan', 'circumstances, reasons'],
            ['幼稚園', 'ようちえん', 'youchien', 'taman kanak-kanak', 'kindergarten, preschool'],
            ['昼寝する', 'ひるねする', 'hirune suru', 'tidur siang', 'to take a daytime nap'],
            ['帰国する', 'きこくする', 'kikoku suru', 'pulang ke negara asal', 'to return to the home country'],
            ['来社', 'らいしゃ', 'raisha', 'kunjungan ke perusahaan', 'visit to a company or office'],
            ['新製品', 'しんせいひん', 'shinseihin', 'produk baru', 'new product'],
            ['新〜', 'しん〜', 'shin', 'baru ~', 'new, novel'],
            ['発表会', 'はっぴょうかい', 'happyoukai', 'acara presentasi, peluncuran', 'presentation'],
            ['いつまでも', 'いつまでも', 'itsumademo', 'selamanya, sampai kapan pun', 'indefinitely'],
            ['景気', 'けいき', 'keiki', 'kondisi ekonomi', 'economy, business conditions'],
            ['これ以上', 'これいじょう', 'koreijou', 'lebih dari ini', 'any longer, any more than this'],
            ['森', 'もり', 'mori', 'hutan', 'forest'],
            ['受ける[インタビューを〜]', 'うける[インタビューを〜]', 'ukeru', 'diwawancarai [wawancara]', 'to be interviewed'],
            ['要望', 'ようぼう', 'youbou', 'permintaan, harapan', 'request, wish'],
            ['本当は', 'ほんとうは', 'hontou wa', 'sebenarnya', 'really, actually'],
            ['おとなしい', 'おとなしい', 'otonashii', 'penurut, pendiam', 'gentle, quiet'],
            ['声[市民の〜]', 'こえ[しみんの〜]', 'koe', 'suara [warga], pendapat', 'voice, opinion'],
            ['しゃべる', 'しゃべる', 'shaberu', 'berbicara, mengobrol', 'to talk'],
            ['振る[彼女を〜]', 'ふる[かのじょを〜]', 'furu', 'memutuskan hubungan [dengan pacar]', 'to dump, to jilt'],
            ['Tシャツ', 'ティーシャツ', 'tiishatsu', 'kaus T-shirt', 'T-shirt'],
            ['数', 'かず', 'kazu', 'jumlah, angka', 'number'],
            ['切る[電話を〜]', 'きる[でんわを〜]', 'kiru', 'menutup [telepon]', 'to hang up [the phone]'],
            ['秘書', 'ひしょ', 'hisho', 'sekretaris', 'secretary'],
            ['教授', 'きょうじゅ', 'kyouju', 'profesor', 'professor'],
            ['わざわざ', 'わざわざ', 'wazawaza', 'repot-repot, sengaja', 'specially, going to the trouble of'],
            ['取る[時間を〜]', 'とる[じかんを〜]', 'toru', 'menyisihkan [waktu]', 'to set aside [time]'],
            ['できれば', 'できれば', 'dekireba', 'kalau bisa', 'if possible'],
            ['変更する', 'へんこうする', 'henkou suru', 'mengubah', 'to change'],
            ['急用', 'きゅうよう', 'kyuuyou', 'urusan mendesak', 'urgent business'],
            ['気にする', 'きにする', 'ki ni suru', 'memikirkan, mengkhawatirkan', 'to be concerned about'],
            ['取引先', 'とりひきさき', 'torihikisaki', 'mitra bisnis, pelanggan', 'client, customer'],
            ['学生用', 'がくせいよう', 'gakuseiyou', 'untuk pelajar/mahasiswa', 'for use by students'],
            ['〜用[学生〜]', '〜よう[がくせい〜]', 'you', 'untuk ~ [pelajar]', 'for use by'],
            ['コンピューター室', 'コンピューターしつ', 'konpyuutaa shitsu', 'ruang komputer', 'computer room'],
            ['〜室', '〜しつ', 'shitsu', 'ruang ~', '~ room'],
            ['渋滞', 'じゅうたい', 'juutai', 'kemacetan', 'traffic jam, holdup'],
            ['瞬間', 'しゅんかん', 'shunkan', 'saat, momen', 'instant, moment'],
            ['意識', 'いしき', 'ishiki', 'kesadaran', 'awareness, sense'],
            ['アンケート', 'アンケート', 'ankeeto', 'kuesioner', 'questionnaire'],
            ['調査', 'ちょうさ', 'chousa', 'penyelidikan, survei', 'investigation, survey'],
            ['傾向', 'けいこう', 'keikou', 'kecenderungan', 'trend'],
            ['避ける', 'さける', 'sakeru', 'menghindari', 'to avoid'],
            ['悲観的[な]', 'ひかんてき[な]', 'hikanteki', 'pesimistis', 'pessimistic'],
            ['グラフ', 'グラフ', 'gurafu', 'grafik', 'graph'],
            ['時', 'とき', 'toki', 'waktu, saat', 'time'],
            ['最高に', 'さいこうに', 'saikou ni', 'paling, sangat', 'most, extremely'],
            ['もう一つ', 'もうひとつ', 'mou hitotsu', 'satu lagi', 'another, one more'],
            ['〜あいだ', '〜あいだ', 'aida', 'selama ~', 'while'],
            ['前者', 'ぜんしゃ', 'zensha', 'yang pertama disebut', 'former'],
            ['後者', 'こうしゃ', 'kousha', 'yang terakhir disebut', 'latter'],
            ['やはり', 'やはり', 'yahari', 'ternyata, memang', 'after all'],
            ['恋', 'こい', 'koi', 'cinta (asmara)', 'love'],
            ['幸せ', 'しあわせ', 'shiawase', 'kebahagiaan', 'happiness'],
            ['感じる', 'かんじる', 'kanjiru', 'merasa', 'to feel'],
            ['寝坊する', 'ねぼうする', 'nebou suru', 'bangun kesiangan', 'to oversleep'],
            ['危険', 'きけん', 'kiken', 'bahaya', 'danger'],
            ['寝顔', 'ねがお', 'negao', 'wajah orang yang sedang tidur', 'face of a sleeping person'],

            // Ungkapan
            ['お電話、代わりました。', 'おでんわ、かわりました。', 'odenwa kawarimashita', 'Halo, ini [nama] (telepon sudah dialihkan).', 'Hello, this is [name] speaking'],
            ['どうかしましたか。', 'どうかしましたか。', 'dou ka shimashita ka', 'Ada apa?', 'Is something the matter?'],
            ['わざわざ〜ていただいたのに、……。', 'わざわざ〜ていただいたのに、……。', 'wazawaza teitadaita noni', 'Padahal Anda sudah repot-repot ~...', 'After you went to the trouble of...'],
            ['困りましたね。', 'こまりましたね。', 'komarimashita ne', 'Wah, repot juga ya.', 'Oh dear'],
            ['できれば、〜ていただけないでしょうか。', 'できれば、〜ていただけないでしょうか。', 'dekireba teitadakenai deshouka', 'Kalau bisa, apakah kiranya Anda berkenan ~?', 'If possible, I wonder if we could...'],
            ['おいでください。', 'おいでください。', 'oide kudasai', 'Silakan datang.', 'Please come'],
            ['申し訳ありませんでした。', 'もうしわけありませんでした。', 'moushiwake arimasen deshita', 'Mohon maaf sebesar-besarnya.', 'I am very sorry'],
        ];
    }
}
