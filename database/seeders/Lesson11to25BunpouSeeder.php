<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class Lesson11to25BunpouSeeder extends Seeder
{
    /**
     * Same treatment as Lesson1BunpouSeeder / Lesson2to5BunpouSeeder /
     * Lesson6to10BunpouSeeder, extended to Pelajaran 11-25: each unit gets
     * its own "Tata Bahasa (Bunpou)" lesson node (order = 0) that sits
     * BEFORE that unit's vocabulary lesson.
     *
     * The grammar progression follows the standard beginner (N5) textbook sequence
     * (counters, comparison, desire, te-form, ~ています, giving directions,
     * nai-form, dictionary form, ta-form, plain form, quoting opinions,
     * relative clauses, then the と／たら／なら conditionals), matched to
     * each unit's vocabulary theme already seeded by ExtendedCurriculumSeeder:
     *
     *   11 Bilangan & Keluarga     -> counters, 〜に(frekuensi), 〜だけ, +ぐらい (bonus)
     *   12 Musim & Cuaca           -> perbandingan (ほう／いちばん／も)
     *   13 Rekreasi & Kota         -> keinginan (ほしい／〜たい／〜たいとおもいます)
     *   14 Aktivitas di Stasiun    -> bentuk-て, 〜てください／ています(sedang)／ましょうか／KBがKK／すみませんが
     *   15 Pekerjaan & Kegiatan    -> てもいいですか／てはいけません／ています(kebiasaan・hasil)／KBにKK／KB1にKB2をKK
     *   16 Cara Menggunakan & AT   -> menyambung >2 kalimat, てから, は〜が〜, を(titik awal: 出ます／降ります), どうやって, どれ／どの
     *   17 Kesehatan & Tubuh       -> bentuk-ない, 〜ないでください／なければなりません／なくてもいいです, pentopikan objek, までに
     *   18 Kemampuan & Hobi        -> bentuk kamus, 〜ことができます／趣味は〜ことです／まえに, なかなか, ぜひ
     *   19 Pengalaman & Kebiasaan  -> bentuk-た (pembentukan), 〜たことがあります／〜たり〜たりします, 〜く／に なります
     *   20 Bentuk Biasa & Sapaan   -> bentuk biasa, pemakaian halus vs biasa, pertanyaan tanpa か／だ, partikel & い dihilangkan, けど
     *   21 Pendapat & Jabatan      -> 普通形と思います／「文」と言います／でしょう？／N1(場所)でN2があります／N(場面)で／Nでも／Vないと
     *   22 Pakaian & Penampilan    -> anak kalimat penerang KB／V辞書形＋時間・約束・用事／V(ます形)ましょうか
     *   23 Jalan & Petunjuk Arah   -> とき／V辞書形・V(た形)とき／V辞書形と／NがAdj／Nを+kata kerja gerak
     *   24 Bantuan & Keluarga Besar-> くれます／V(て形)あげます・もらいます・くれます／N1はN2がV
     *   25 Pengandaian & Kehidupan -> 普通形過去ら／V(た形)ら／ても／もし／subjek anak kalimat
     *
     * As with the earlier Bunpou seeders, the Grammar table (GrammarSeeder)
     * only carries rows through Pelajaran 5, so every card here is written
     * with grammar_id = null; nothing else in the app depends on that link.
     *
     * Explanations, notes, example sentences and dialogues are written fresh
     * for this app — nothing is copied from the book (grammar patterns are
     * grammatical facts, not copyrightable). Every example sticks to
     * vocabulary already introduced up to that lesson.
     *
     * Japanese strings may carry furigana markers in the form 漢字《かんじ》;
     * RubyText.vue renders them as <ruby>.
     *
     * Safe to run repeatedly AND on a database that already has users:
     *   php artisan db:seed --class=Lesson11to25BunpouSeeder
     */
    public function run(): void
    {
        foreach ($this->units() as $unitOrder => $unitData) {
            $this->buildUnitBunpou($unitOrder, $unitData);
        }
    }

    private function buildUnitBunpou(int $unitOrder, array $unitData): void
    {
        $unit = Unit::where('order', $unitOrder)->whereHas('level', fn ($l) => $l->where('code', 'N5'))->first();

        if (! $unit) {
            return;
        }

        $bunpou = Lesson::updateOrCreate(
            ['unit_id' => $unit->id, 'order' => 0],
            [
                'title_id' => 'Tata Bahasa (Bunpou)',
                'title_en' => 'Grammar (Bunpou)',
                'category' => 'grammar',
                'xp_reward' => 10,
                'required_accuracy' => 0,
                'prerequisite_lesson_id' => $unitData['prerequisite_lesson_id'] ?? null,
                'is_active' => true,
            ]
        );

        $vocabLessons = Lesson::where('unit_id', $unit->id)
            ->whereIn('order', $unitData['vocab_lesson_orders'])
            ->orderBy('order')
            ->get();

        foreach ($vocabLessons as $index => $vocabLesson) {
            $prereq = $index === 0
                ? $bunpou->id
                : $vocabLessons[$index - 1]->id;

            $vocabLesson->update(['prerequisite_lesson_id' => $prereq]);
        }

        $this->seedCards($bunpou, $unitData['cards']);
        $this->removeOldGrammarCards($vocabLessons->pluck('id'));
    }

    private function seedCards(Lesson $bunpou, array $cards): void
    {
        foreach (array_values($cards) as $position => $card) {
            LessonQuestion::updateOrCreate(
                [
                    'lesson_id' => $bunpou->id,
                    'question_type' => 'grammar',
                    'order' => $position + 1,
                ],
                [
                    'grammar_id' => null,
                    'prompt_id' => $card['title_id'],
                    'prompt_en' => $card['title_en'],
                    'japanese_text' => $card['pattern'],
                    'payload' => $card['payload'],
                    'difficulty' => 2,
                    'is_active' => true,
                ]
            );
        }

        LessonQuestion::where('lesson_id', $bunpou->id)
            ->where('question_type', 'grammar')
            ->where('order', '>', count($cards))
            ->delete();
    }

    private function removeOldGrammarCards($vocabLessonIds): void
    {
        LessonQuestion::whereIn('lesson_id', $vocabLessonIds)
            ->where('question_type', 'grammar')
            ->delete();
    }

    private function units(): array
    {
        $lessonByUnitOrder = fn (int $unitOrder, int $lessonOrder) => optional(
            Lesson::whereHas('unit', fn ($q) => $q->where('order', $unitOrder)->whereHas('level', fn ($l) => $l->where('code', 'N5')))
                ->where('order', $lessonOrder)
                ->first()
        )->id;

        return [
            11 => [
                'cards' => $this->lesson11Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(10, 1),
            ],
            12 => [
                'cards' => $this->lesson12Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(11, 1),
            ],
            13 => [
                'cards' => $this->lesson13Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(12, 1),
            ],
            14 => [
                'cards' => $this->lesson14Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(13, 1),
            ],
            15 => [
                'cards' => $this->lesson15Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(14, 1),
            ],
            16 => [
                'cards' => $this->lesson16Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(15, 1),
            ],
            17 => [
                'cards' => $this->lesson17Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(16, 1),
            ],
            18 => [
                'cards' => $this->lesson18Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(17, 1),
            ],
            19 => [
                'cards' => $this->lesson19Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(18, 1),
            ],
            20 => [
                'cards' => $this->lesson20Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(19, 1),
            ],
            21 => [
                'cards' => $this->lesson21Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(20, 1),
            ],
            22 => [
                'cards' => $this->lesson22Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(21, 1),
            ],
            23 => [
                'cards' => $this->lesson23Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(22, 1),
            ],
            24 => [
                'cards' => $this->lesson24Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(23, 1),
            ],
            25 => [
                'cards' => $this->lesson25Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(24, 1),
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 11 — Bilangan: kata bantu bilangan, ni(frekuensi), kara-made, gurai
    // ------------------------------------------------------------------

    private function lesson11Cards(): array
    {
        return [
            [
                'title_id' => 'Kata bantu bilangan (josuushi)',
                'title_en' => 'Counters (josuushi)',
                'pattern' => '数字＋助数詞',
                'payload' => [
                    'explanation_id' => 'Untuk menghitung benda, bahasa Jepang memakai kata bantu bilangan yang berbeda menurut bentuk atau jenis bendanya: 〜人《にん》 untuk orang, 〜枚《まい》 untuk benda tipis dan datar (perangko, kertas), 〜台《だい》 untuk mesin dan kendaraan. Selain itu ada rangkaian tersendiri untuk benda secara umum: 一《ひと》つ、二《ふた》つ、三《みっ》つ… sampai 十《とお》, dipakai kalau tidak ada kata bantu bilangan yang cocok.',
                    'explanation_en' => 'To count things, Japanese uses different counters depending on the shape or kind of object: -nin for people, -mai for thin flat objects (stamps, paper), -dai for machines and vehicles. There is also a separate all-purpose series — hitotsu, futatsu, mittsu… up to too — used when no specific counter fits.',
                    'notes_id' => [
                        'Jumlahnya diletakkan SETELAH kata benda, tanpa の: りんごを 三《みっ》つ 買《か》います (bukan 三《みっ》つの りんご).',
                        'Beberapa bacaan tidak beraturan: 一人《ひとり》、二人《ふたり》、四人《よにん》.',
                    ],
                    'notes_en' => [
                        'The quantity comes AFTER the noun, with no の: ringo o mittsu kaimasu (not mittsu no ringo).',
                        'A few readings are irregular: hitori, futari, yonin.',
                    ],
                    'examples' => [
                        ['ja' => 'きってを 三枚《さんまい》 ください。', 'reading' => 'Kitte o sanmai kudasai.', 'id' => 'Tolong perangkonya 3 lembar.', 'en' => 'Three stamps, please.'],
                        ['ja' => 'りんごを 一《ひと》つ 買《か》いました。', 'reading' => 'Ringo o hitotsu kaimashita.', 'id' => 'Saya beli apel satu.', 'en' => 'I bought one apple.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Membeli perangko',
                        'title_en' => 'Buying stamps',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません、きってを 二枚《にまい》 ください。', 'reading' => 'Sumimasen, kitte o nimai kudasai.', 'id' => 'Permisi, perangkonya 2 lembar.', 'en' => 'Excuse me, two stamps please.'],
                            ['speaker' => '店員《てんいん》', 'ja' => 'はい、どうぞ。', 'reading' => 'Hai, douzo.', 'id' => 'Baik, silakan.', 'en' => 'Here you are.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はがきも 一枚《いちまい》 お願《ねが》いします。', 'reading' => 'Hagaki mo ichimai onegaishimasu.', 'id' => 'Kartu pos juga satu, ya.', 'en' => 'And one postcard, please.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'Cara menghitung: 一つ・一人・kata bantu',
                'title_en' => 'How to count: hitotsu, hitori, counters',
                'pattern' => '一《ひと》つ…十《とお》／〜人《にん》・〜台《だい》・〜枚《まい》・〜回《かい》',
                'payload' => [
                    'explanation_id' => '(1) Untuk menghitung benda 1 sampai 10 dipakai 一《ひと》つ、二《ふた》つ、三《みっ》つ、四《よっ》つ、五《いつ》つ、六《むっ》つ、七《なな》つ、八《やっ》つ、九《ここの》つ、十《とお》. Dari 11 ke atas dipakai angka biasa (十一《じゅういち》、十二《じゅうに》 …) tanpa つ. (2) Menghitung orang, benda tertentu, atau menyatakan kuantitas memakai kata bantu bilangan yang dipasang di belakang angka: 〜人《にん》 (orang), 〜台《だい》 (mesin, kendaraan), 〜枚《まい》 (benda tipis dan datar: kertas, baju kaus, piring, CD), 〜回《かい》 (kali), 〜分《ふん》 (menit), 〜時間《じかん》 (jam), 〜日《にち》 (hari), 〜週間《しゅうかん》 (minggu), 〜か月《げつ》 (bulan), 〜年《ねん》 (tahun).',
                    'explanation_en' => '(1) Objects from 1 to 10 are counted 一《ひと》つ、二《ふた》つ、三《みっ》つ、四《よっ》つ、五《いつ》つ、六《むっ》つ、七《なな》つ、八《やっ》つ、九《ここの》つ、十《とお》. From 11 on, ordinary numbers are used without つ. (2) People, particular objects and quantities take a counter placed after the number: 〜人《にん》 (people), 〜台《だい》 (machines, vehicles), 〜枚《まい》 (thin flat things: paper, T-shirts, plates, CDs), 〜回《かい》 (times), 〜分《ふん》 (minutes), 〜時間《じかん》 (hours), 〜日《にち》 (days), 〜週間《しゅうかん》 (weeks), 〜か月《げつ》 (months), 〜年《ねん》 (years).',
                    'notes_id' => [
                        'Orang: 一人《ひとり》, 二人《ふたり》, lalu 三人《さんにん》 dan seterusnya; 四人《よにん》 dibaca よにん.',
                        '〜日《にち》 dibaca seperti tanggal (二日《ふつか》, 三日《みっか》 …), tetapi "1 hari" adalah 一日《いちにち》, bukan ついたち.',
                    ],
                    'notes_en' => [
                        'People: 一人《ひとり》, 二人《ふたり》, then 三人《さんにん》 onwards; 四人《よにん》 is read よにん.',
                        '〜日《にち》 is read like dates (二日《ふつか》, 三日《みっか》 …), but "1 day" is 一日《いちにち》, not ついたち.',
                    ],
                    'examples' => [
                        [
                            'ja' => 'みかんを 六《むっ》つ 買《か》いました。',
                            'reading' => 'Mikan o muttsu kaimashita.',
                            'id' => 'Membeli enam buah jeruk.',
                            'en' => 'I bought six mandarin oranges.',
                        ],
                        [
                            'ja' => '切手《きって》を 三枚《さんまい》 買《か》いました。',
                            'reading' => 'Kitte o sanmai kaimashita.',
                            'id' => 'Membeli tiga lembar perangko.',
                            'en' => 'I bought three stamps.',
                        ],
                        [
                            'ja' => '車《くるま》が 二台《にだい》 あります。',
                            'reading' => 'Kuruma ga nidai arimasu.',
                            'id' => 'Ada dua buah mobil.',
                            'en' => 'There are two cars.',
                        ],
                        [
                            'ja' => '毎晩《まいばん》 二時間《にじかん》 勉強《べんきょう》します。',
                            'reading' => 'Maiban nijikan benkyou shimasu.',
                            'id' => 'Setiap malam belajar dua jam.',
                            'en' => 'I study for two hours every night.',
                        ],
                    ],
                    'dialogue' => null,
                ],
            ],

            [
                'title_id' => 'Letak kata keterangan bilangan',
                'title_en' => 'Where quantity words go',
                'pattern' => 'KB＋partikel＋数量《すうりょう》＋KK',
                'payload' => [
                    'explanation_id' => 'Kata keterangan bilangan (angka + kata bantu) pada dasarnya diletakkan langsung di belakang kata benda yang jenis jumlahnya ditentukan olehnya, beserta partikelnya: 〜を 六《むっ》つ 買《か》います, 〜が 五人《ごにん》 います. Pengecualiannya adalah kata keterangan bilangan yang menyatakan lama waktu (〜時間《じかん》, 〜か月《げつ》, 〜年《ねん》): ia tidak terikat aturan susunan kata ini.',
                    'explanation_en' => 'A quantity phrase (number + counter) normally comes right after the noun whose amount it gives, together with the particle: 〜を 六《むっ》つ 買《か》います, 〜が 五人《ごにん》 います. The exception is quantity phrases that express a length of time (〜時間《じかん》, 〜か月《げつ》, 〜年《ねん》): they do not follow this word-order rule.',
                    'notes_id' => [
                        'Pola dasar: KB を／が ＋ jumlah ＋ KK.',
                    ],
                    'notes_en' => [
                        'Basic pattern: noun を／が + quantity + verb.',
                    ],
                    'examples' => [
                        [
                            'ja' => 'りんごを 三《みっ》つ 買《か》いました。',
                            'reading' => 'Ringo o mittsu kaimashita.',
                            'id' => 'Membeli tiga buah apel.',
                            'en' => 'I bought three apples.',
                        ],
                        [
                            'ja' => '教室《きょうしつ》に 学生《がくせい》が 五人《ごにん》 います。',
                            'reading' => 'Kyoushitsu ni gakusei ga gonin imasu.',
                            'id' => 'Di ruang kelas ada lima mahasiswa.',
                            'en' => 'There are five students in the classroom.',
                        ],
                        [
                            'ja' => '大阪《おおさか》で 三年《さんねん》 働《はたら》きました。',
                            'reading' => 'Oosaka de sannen hatarakimashita.',
                            'id' => 'Bekerja di Osaka selama tiga tahun.',
                            'en' => 'I worked in Osaka for three years.',
                        ],
                    ],
                    'dialogue' => null,
                ],
            ],

            [
                'title_id' => 'Menanyakan jumlah: いくつ／何＋kata bantu／どのくらい',
                'title_en' => 'Asking amounts: ikutsu / nan + counter / dono kurai',
                'pattern' => 'いくつ・何《なん》＋kata bantu・どのくらい',
                'payload' => [
                    'explanation_id' => '(1) いくつ dipakai untuk menanyakan jumlah benda yang dihitung dengan 一《ひと》つ、二《ふた》つ…. (2) 何《なん》＋kata bantu bilangan (何人《なんにん》, 何枚《なんまい》, 何時間《なんじかん》, 何回《なんかい》, 何年《なんねん》 …) dipakai untuk kata bantu yang bersangkutan. (3) どのくらい menanyakan lama waktu; bisa dijawab dengan kata keterangan bilangan waktu.',
                    'explanation_en' => '(1) いくつ asks how many objects when counting with 一《ひと》つ、二《ふた》つ…. (2) 何《なん》+ counter (何人《なんにん》, 何枚《なんまい》, 何時間《なんじかん》, 何回《なんかい》, 何年《なんねん》 …) is used for the matching counter. (3) どのくらい asks how long; it is answered with a time quantity phrase.',
                    'notes_id' => [
                        'どのくらい juga dipakai untuk "seberapa lama" pada かかります: 大阪《おおさか》から 東京《とうきょう》まで どのくらい かかりますか。',
                    ],
                    'notes_en' => [
                        'どのくらい is also used for "how long" with かかります: 大阪《おおさか》から 東京《とうきょう》まで どのくらい かかりますか。',
                    ],
                    'examples' => [
                        [
                            'ja' => 'みかんを いくつ 買《か》いましたか。－八《やっ》つ 買《か》いました。',
                            'reading' => 'Mikan o ikutsu kaimashita ka. Yattsu kaimashita.',
                            'id' => 'Membeli berapa buah jeruk? — Delapan buah.',
                            'en' => 'How many mandarins did you buy? — Eight.',
                        ],
                        [
                            'ja' => 'この 会社《かいしゃ》に 外国人《がいこくじん》が 何人《なんにん》 いますか。－五人《ごにん》 います。',
                            'reading' => 'Kono kaisha ni gaikokujin ga nannin imasu ka. Gonin imasu.',
                            'id' => 'Di perusahaan ini ada berapa orang asing? — Lima orang.',
                            'en' => 'How many foreigners are in this company? — Five.',
                        ],
                        [
                            'ja' => '毎晩《まいばん》 何時間《なんじかん》 勉強《べんきょう》しますか。－二時間《にじかん》 勉強《べんきょう》します。',
                            'reading' => 'Maiban nanjikan benkyou shimasu ka. Nijikan benkyou shimasu.',
                            'id' => 'Setiap malam belajar berapa jam? — Dua jam.',
                            'en' => 'How many hours do you study every night? — Two.',
                        ],
                        [
                            'ja' => '大阪《おおさか》から 東京《とうきょう》まで どのくらい かかりますか。',
                            'reading' => 'Oosaka kara Toukyou made dono kurai kakarimasu ka.',
                            'id' => 'Dari Osaka sampai Tokyo memerlukan waktu berapa lama?',
                            'en' => 'How long does it take from Osaka to Tokyo?',
                        ],
                    ],
                    'dialogue' => null,
                ],
            ],

            [
                // Not one of the book's 4 numbered points for this lesson, but it's
                // genuine N5 grammar that pairs naturally with the frequency card
                // above — kept as bonus material rather than dropped.
                'title_id' => 'ぐらい／くらい (perkiraan) — tambahan',
                'title_en' => 'gurai / kurai (approximation)',
                'pattern' => '数量＋ぐらい',
                'payload' => [
                    'explanation_id' => 'ぐらい (juga diucapkan くらい) ditambahkan setelah jumlah atau lama waktu untuk menyatakan "kira-kira". Dipakai baik untuk waktu, jarak, maupun jumlah benda.',
                    'explanation_en' => 'gurai (also said kurai) is added after a quantity or duration to mean "approximately". It works for time, distance, and quantities of things alike.',
                    'notes_id' => [
                        'Untuk benda: りんごを 五《いつ》つぐらい 買《か》いました.',
                        'ごろ dipakai untuk titik waktu perkiraan (jam berapa), berbeda dengan ぐらい untuk lama/jumlah perkiraan.',
                    ],
                    'notes_en' => [
                        'With things: ringo o itsutsu gurai kaimashita.',
                        'goro is for an approximate point in time (around what time), unlike gurai for an approximate duration/amount.',
                    ],
                    'examples' => [
                        ['ja' => '会社《かいしゃ》まで 一時間《いちじかん》ぐらい かかります。', 'reading' => 'Kaisha made ichijikan gurai kakarimasu.', 'id' => 'Ke kantor makan waktu sekitar 1 jam.', 'en' => 'It takes about an hour to the office.'],
                        ['ja' => '一週間《いっしゅうかん》ぐらい 休《やす》みます。', 'reading' => 'Isshuukan gurai yasumimasu.', 'id' => 'Saya libur sekitar seminggu.', 'en' => 'I will take about a week off.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Berapa lama tinggal',
                        'title_en' => 'How long you have lived here',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'インドネシアに どのくらい 住《す》んでいますか。', 'reading' => 'Indoneshia ni dono kurai sundeimasu ka.', 'id' => 'Sudah berapa lama tinggal di Indonesia?', 'en' => 'How long have you lived in Indonesia?'],
                            ['speaker' => 'ワヒュ', 'ja' => '三年《さんねん》ぐらいです。', 'reading' => 'Sannen gurai desu.', 'id' => 'Sekitar 3 tahun.', 'en' => 'About three years.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => '数量＋に (frekuensi)',
                'title_en' => 'quantity + ni (frequency)',
                'pattern' => '〜に 〜回',
                'payload' => [
                    'explanation_id' => 'Untuk menyatakan berapa kali suatu hal terjadi dalam satu satuan waktu, satuan waktunya diikuti に lalu jumlah kali dengan 〜回《かい》. Pola: (1週間《しゅうかん》)に (3回《かい》). に di sini berarti "per" atau "dalam setiap".',
                    'explanation_en' => 'To say how many times something happens within a unit of time, the time unit takes に, followed by the number of times with -kai. Pattern: (isshuukan) ni (sankai). に here means "per" or "in every".',
                    'notes_id' => [
                        'Satuan waktu tanpa penanda apa pun tidak memakai に: まいにち、まいしゅう sudah berarti "per hari/minggu" secara langsung.',
                        'どのくらい〜ますか untuk bertanya frekuensi.',
                    ],
                    'notes_en' => [
                        'A bare time-unit word needs no に: mainichi, maishuu already mean "per day/week" on their own.',
                        'dono kurai … masu ka is how you ask about frequency.',
                    ],
                    'examples' => [
                        ['ja' => '一週間《いっしゅうかん》に 二回《にかい》 プールへ 行《い》きます。', 'reading' => 'Isshuukan ni nikai puuru e ikimasu.', 'id' => 'Seminggu 2 kali saya ke kolam renang.', 'en' => 'I go to the pool twice a week.'],
                        ['ja' => '一《いち》か月《げつ》に 一回《いっかい》 両親《りょうしん》に 手紙《てがみ》を 書《か》きます。', 'reading' => 'Ikkagetsu ni ikkai ryoushin ni tegami o kakimasu.', 'id' => 'Sebulan sekali saya menulis surat untuk orang tua.', 'en' => 'Once a month I write a letter to my parents.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Seberapa sering berolahraga',
                        'title_en' => 'How often you exercise',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'どのくらい スポーツを しますか。', 'reading' => 'Dono kurai supootsu o shimasu ka.', 'id' => 'Seberapa sering olahraga?', 'en' => 'How often do you exercise?'],
                            ['speaker' => 'ワヒュ', 'ja' => '一週間《いっしゅうかん》に 三回《さんかい》ぐらいです。', 'reading' => 'Isshuukan ni sankai gurai desu.', 'id' => 'Kira-kira 3 kali seminggu.', 'en' => 'About three times a week.'],
                        ],
                    ],
                ],
            ],

            [
                // Official Pelajaran 11 point 4 (Kata Keterangan Bilangan だけ／Kata Benda だけ).
                // The previous card here duplicated から〜まで, which is actually
                // Pelajaran 4's grammar — replaced with the lesson's real 4th point.
                'title_id' => 'Kata Keterangan Bilangan／KB＋だけ (hanya)',
                'title_en' => 'Quantity/Noun + dake (only)',
                'pattern' => '〜だけ',
                'payload' => [
                    'explanation_id' => 'だけ berarti "hanya/cuma". Ia menempel langsung setelah kata benda atau kata keterangan bilangan untuk membatasi cakupannya pada jumlah atau hal itu saja, tidak lebih.',
                    'explanation_en' => 'dake means "only/just". It attaches directly after a noun or a quantity word to limit things strictly to that one amount or item, nothing more.',
                    'notes_id' => [
                        'だけ menggantikan partikel を／が, tapi tetap berdampingan dengan は／も: これだけで いいです。',
                        'Berbeda dengan しか, yang selalu diikuti bentuk kata kerja negatif.',
                    ],
                    'notes_en' => [
                        'dake replaces the particles wo/ga, but stays alongside wa/mo: kore dake de ii desu.',
                        'Unlike shika, which is always followed by a negative verb.',
                    ],
                    'examples' => [
                        ['ja' => '今日《きょう》は これだけ 買《か》いました。', 'reading' => 'Kyou wa kore dake kaimashita.', 'id' => 'Hari ini saya cuma beli ini saja.', 'en' => 'Today I only bought this.'],
                        ['ja' => 'テストは 三十分《さんじゅっぷん》だけです。', 'reading' => 'Tesuto wa sanjuppun dake desu.', 'id' => 'Tesnya cuma 30 menit saja.', 'en' => 'The test is only 30 minutes.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Belanja secukupnya',
                        'title_en' => 'Buying just enough',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'たくさん 買《か》いましたか。', 'reading' => 'Takusan kaimashita ka.', 'id' => 'Belanja banyak?', 'en' => 'Did you buy a lot?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、これだけです。', 'reading' => 'Iie, kore dake desu.', 'id' => 'Tidak, cuma ini saja.', 'en' => 'No, just this.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 12 — Perbandingan
    // ------------------------------------------------------------------

    private function lesson12Cards(): array
    {
        return [
            [
                // Official point 1: past tense for noun / na-adjective sentences.
                'title_id' => 'Waktu Kalimat Nominal ／ Kata Sifat-な (positif/negatif)',
                'title_en' => 'Tense for noun & na-adjective sentences',
                'pattern' => '〜でした／〜じゃなかったです',
                'payload' => [
                    'explanation_id' => 'Kalimat yang predikatnya kata benda atau kata sifat-な mengikuti pola です seperti Pelajaran 1: lampau positif です→でした, lampau negatif じゃありません→じゃありませんでした (bentuk singkatnya じゃなかったです).',
                    'explanation_en' => 'Sentences ending in a noun or na-adjective follow the same desu pattern as Lesson 1: past affirmative desu -> deshita, past negative ja arimasen -> ja arimasen deshita (shortened to ja nakatta desu).',
                    'notes_id' => [
                        'Kata sifat-な TIDAK memakai な saat menjadi predikat: 静《しず》かです, bukan 静《しず》かなです。',
                        'な hanya muncul saat langsung menerangkan kata benda di depannya: 静《しず》かな 町《まち》。',
                    ],
                    'notes_en' => [
                        'A na-adjective does NOT take な when it is the sentence\'s predicate: shizuka desu, not shizuka na desu.',
                        'な only appears right before a noun it directly describes: shizuka na machi.',
                    ],
                    'examples' => [
                        ['ja' => 'きのうは 休《やす》みでした。', 'reading' => 'Kinou wa yasumi deshita.', 'id' => 'Kemarin hari libur.', 'en' => 'Yesterday was a day off.'],
                        ['ja' => 'このへんは 昔《むかし》 静《しず》かじゃなかったです。', 'reading' => 'Kono hen wa mukashi shizuka ja nakatta desu.', 'id' => 'Daerah ini dulu tidak tenang.', 'en' => 'This area used to not be quiet.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menanyakan hari kemarin',
                        'title_en' => 'Asking about yesterday',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'きのうは 休《やす》みでしたか。', 'reading' => 'Kinou wa yasumi deshita ka.', 'id' => 'Kemarin libur?', 'en' => 'Was yesterday a day off?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、休《やす》みじゃなかったです。', 'reading' => 'Iie, yasumi ja nakatta desu.', 'id' => 'Bukan, bukan hari libur.', 'en' => 'No, it wasn\'t.'],
                        ],
                    ],
                ],
            ],

            [
                // Official point 2: past tense for i-adjective sentences.
                'title_id' => 'Waktu Kalimat Kata Sifat-い (positif/negatif)',
                'title_en' => 'Tense for i-adjective sentences',
                'pattern' => '〜かったです／〜くなかったです',
                'payload' => [
                    'explanation_id' => 'Kata sifat-い punya cara sendiri untuk bentuk lampau: buang い lalu tambah かったです (lampau positif); untuk lampau negatif buang い lalu tambah くなかったです. Berbeda dari kata sifat-な yang cukup mengubah です-nya saja.',
                    'explanation_en' => 'i-adjectives form their own past tense: drop い and add katta desu for the past affirmative; for the past negative, drop い and add kunakatta desu. This differs from na-adjectives, which just change desu itself.',
                    'notes_id' => [
                        'Pengecualian: いい (bagus) → よかったです, bukan いかったです。',
                        'Bentuk sekarang negatifnya juga beraturan sama: 〜くないです。',
                    ],
                    'notes_en' => [
                        'Exception: ii (good) -> yokatta desu, not ikatta desu.',
                        'The present negative follows the same shift: … kunai desu.',
                    ],
                    'examples' => [
                        ['ja' => 'きのうは 暑《あつ》かったです。', 'reading' => 'Kinou wa atsukatta desu.', 'id' => 'Kemarin panas.', 'en' => 'Yesterday was hot.'],
                        ['ja' => 'テストは むずかしくなかったです。', 'reading' => 'Tesuto wa muzukashikunakatta desu.', 'id' => 'Tesnya dulu tidak sulit.', 'en' => 'The test was not hard.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Cuaca kemarin',
                        'title_en' => 'Yesterday\'s weather',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'きのうの 天気《てんき》は どうでしたか。', 'reading' => 'Kinou no tenki wa dou deshita ka.', 'id' => 'Cuaca kemarin bagaimana?', 'en' => 'How was the weather yesterday?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'あまり よくなかったです。寒《さむ》かったです。', 'reading' => 'Amari yokunakatta desu. Samukatta desu.', 'id' => 'Kurang bagus. Dingin.', 'en' => 'Not very good. It was cold.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KB1と KB2と どちらが〜ですか',
                'title_en' => 'KB1 to KB2 to dochira ga … desu ka',
                'pattern' => 'AとBと どちらが〜ですか',
                'payload' => [
                    'explanation_id' => 'Untuk membandingkan dua hal, kedua-duanya diikuti と, lalu どちら (yang mana, di antara dua) dan が. Jawabannya menyebut salah satu pilihan diikuti の ほうが〜です — ほう secara harfiah berarti "arah/pihak", jadi maknanya "pihak … lebih …".',
                    'explanation_en' => 'To compare two things, both take と, followed by dochira (which one, of two) and が. The answer names the chosen one followed by no hou ga … desu — hou literally means "side/direction", so the sense is "the … side is more …".',
                    'notes_id' => [
                        'どちら dipakai untuk 2 pilihan; どれ untuk 3 atau lebih (lihat kartu berikutnya).',
                        'Kalau keduanya sama: どちらも〜です atau どちらも〜じゃありません.',
                    ],
                    'notes_en' => [
                        'dochira is for two options; dore is for three or more (see the next card).',
                        'If both are equal: dochira mo … desu, or dochira mo … ja arimasen.',
                    ],
                    'examples' => [
                        ['ja' => '夏《なつ》と 冬《ふゆ》と どちらが 好《す》きですか。', 'reading' => 'Natsu to fuyu to dochira ga suki desu ka.', 'id' => 'Suka mana, musim panas atau musim dingin?', 'en' => 'Which do you like more, summer or winter?'],
                        ['ja' => '冬《ふゆ》の ほうが 好《す》きです。', 'reading' => 'Fuyu no hou ga suki desu.', 'id' => 'Saya lebih suka musim dingin.', 'en' => 'I like winter better.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Musim panas atau dingin',
                        'title_en' => 'Summer or winter',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '夏《なつ》と 冬《ふゆ》と どちらが 暑《あつ》いですか。', 'reading' => 'Natsu to fuyu to dochira ga atsui desu ka.', 'id' => 'Mana yang lebih panas, musim panas atau dingin?', 'en' => 'Which is hotter, summer or winter?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'もちろん 夏《なつ》の ほうが 暑《あつ》いです。', 'reading' => 'Mochiron natsu no hou ga atsui desu.', 'id' => 'Tentu saja musim panas lebih panas.', 'en' => 'Summer is hotter, of course.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => '〜の中で〜が いちばん〜',
                'title_en' => '… no naka de … ga ichiban …',
                'pattern' => '(範囲)の中で 〜が いちばん〜',
                'payload' => [
                    'explanation_id' => 'Untuk membandingkan tiga hal atau lebih, sebutkan cakupannya dengan 〜の中《なか》で, lalu tanyakan dengan 何《なに》/どれ/だれ + が + いちばん (paling) + kata sifat. Jawabannya menyebut satu pemenang tanpa perlu の ほう.',
                    'explanation_en' => 'To compare three or more things, name the range with … no naka de, then ask with nani/dore/dare + が + ichiban (most) + adjective. The answer simply names the one winner — no hou is not needed here.',
                    'notes_id' => [
                        'Cakupan bisa berupa kelompok (季節《きせつ》の中《なか》で) atau daftar (これと それと あれの中《なか》で).',
                        'いちばん diletakkan tepat sebelum kata sifat.',
                    ],
                    'notes_en' => [
                        'The range can be a category (kisetsu no naka de) or a listed set (kore to sore to are no naka de).',
                        'ichiban sits right before the adjective.',
                    ],
                    'examples' => [
                        ['ja' => '季節《きせつ》の中《なか》で 春《はる》が いちばん 好《す》きです。', 'reading' => 'Kisetsu no naka de haru ga ichiban suki desu.', 'id' => 'Dari semua musim, saya paling suka musim semi.', 'en' => 'Of all the seasons, I like spring the most.'],
                        ['ja' => 'クラスで だれが いちばん 上手《じょうず》ですか。', 'reading' => 'Kurasu de dare ga ichiban jouzu desu ka.', 'id' => 'Di kelas, siapa yang paling pandai?', 'en' => 'Who in the class is the best?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Musim favorit',
                        'title_en' => 'Favorite season',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '一年《いちねん》の中《なか》で いつが いちばん 好《す》きですか。', 'reading' => 'Ichinen no naka de itsu ga ichiban suki desu ka.', 'id' => 'Dalam setahun, kapan yang paling kamu suka?', 'en' => 'Which time of year do you like the most?'],
                            ['speaker' => 'ワヒュ', 'ja' => '秋《あき》が いちばん 好《す》きです。涼《すず》しいですから。', 'reading' => 'Aki ga ichiban suki desu. Suzushii desu kara.', 'id' => 'Paling suka musim gugur. Karena sejuk.', 'en' => 'I like autumn the most — it is cool.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'A は Bより〜です',
                'title_en' => 'A wa B yori … desu',
                'pattern' => 'AはBより〜です',
                'payload' => [
                    'explanation_id' => 'より menandai patokan pembanding: "A lebih … daripada B". Berbeda dengan pola どちら yang menanyakan pilihan, より langsung menyatakan atau menegaskan mana yang lebih.',
                    'explanation_en' => 'yori marks the point of comparison: "A is more … than B". Unlike the dochira pattern, which asks for a choice, yori directly states or confirms which one is more.',
                    'notes_id' => [
                        'Urutan A dan B boleh dibalik tanpa mengubah makna dasarnya, asal より tetap menempel pada patokannya.',
                        'Bisa digabung dengan もっと: もっと 寒《さむ》いです (lebih dingin lagi/lebih-lebih dingin).',
                    ],
                    'notes_en' => [
                        'A and B can be swapped without changing the basic meaning, as long as yori stays attached to the reference point.',
                        'It combines with motto: motto samui desu (even colder / more so).',
                    ],
                    'examples' => [
                        ['ja' => '今年《ことし》の 冬《ふゆ》は きょねんより 寒《さむ》いです。', 'reading' => 'Kotoshi no fuyu wa kyonen yori samui desu.', 'id' => 'Musim dingin tahun ini lebih dingin daripada tahun lalu.', 'en' => 'This winter is colder than last year.'],
                        ['ja' => '飛行機《ひこうき》は 電車《でんしゃ》より 速《はや》いです。', 'reading' => 'Hikouki wa densha yori hayai desu.', 'id' => 'Pesawat lebih cepat daripada kereta.', 'en' => 'Planes are faster than trains.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Membandingkan kota',
                        'title_en' => 'Comparing towns',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'ここは ジャカルタより 涼《すず》しいですね。', 'reading' => 'Koko wa Jakaruta yori suzushii desu ne.', 'id' => 'Di sini lebih sejuk daripada Jakarta, ya.', 'en' => 'It is cooler here than in Jakarta.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、ずっと 涼《すず》しいです。', 'reading' => 'Ee, zutto suzushii desu.', 'id' => 'Iya, jauh lebih sejuk.', 'en' => 'Yes, much cooler.'],
                        ],
                    ],
                ],
            ],

            [
                // Official point 6: adjective + no standing in for a known noun.
                'title_id' => 'Kata Sifat＋の (pengganti kata benda)',
                'title_en' => 'Adjective + no (standing in for a noun)',
                'pattern' => 'Kata Sifat＋の',
                'payload' => [
                    'explanation_id' => 'Ketika kata bendanya sudah jelas dari percakapan, の bisa menggantikan kata benda itu tepat setelah kata sifat — peran yang sama seperti の pengganti kata benda di Pelajaran 2, hanya kali ini menempel ke kata sifat, bukan ke kata benda lain.',
                    'explanation_en' => 'When the noun is already clear from context, の can stand in for it right after an adjective — the same substituting role の played back in Lesson 2, but this time attached to an adjective instead of another noun.',
                    'notes_id' => [
                        'Kata sifat-な tetap memakai な sebelum の: 静《しず》かなの。',
                        'Sering dipakai saat memilih di antara beberapa benda sejenis: 赤《あか》いのを ください。',
                    ],
                    'notes_en' => [
                        'A na-adjective still keeps な before の: shizuka na no.',
                        'Often used when picking among several similar items: akai no o kudasai.',
                    ],
                    'examples' => [
                        ['ja' => '赤《あか》い かばんが ほしいです。／赤《あか》いのが ほしいです。', 'reading' => 'Akai kaban ga hoshii desu. / Akai no ga hoshii desu.', 'id' => 'Saya ingin tas merah. / Saya ingin yang merah.', 'en' => 'I want a red bag. / I want the red one.'],
                        ['ja' => 'もっと 大《おお》きいのは ありますか。', 'reading' => 'Motto ookii no wa arimasu ka.', 'id' => 'Ada yang lebih besar?', 'en' => 'Do you have a bigger one?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Memilih tas',
                        'title_en' => 'Choosing a bag',
                        'lines' => [
                            ['speaker' => '店員《てんいん》', 'ja' => 'どちらの かばんに しますか。', 'reading' => 'Dochira no kaban ni shimasu ka.', 'id' => 'Mau tas yang mana?', 'en' => 'Which bag would you like?'],
                            ['speaker' => 'ワヒュ', 'ja' => '黒《くろ》いのを ください。', 'reading' => 'Kuroi no o kudasai.', 'id' => 'Yang hitam saja.', 'en' => 'The black one, please.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 13 — Keinginan
    // ------------------------------------------------------------------

    private function lesson13Cards(): array
    {
        return [
            [
                'title_id' => 'KBが ほしいです',
                'title_en' => 'KB ga hoshii desu',
                'pattern' => 'KBが ほしいです',
                'payload' => [
                    'explanation_id' => 'ほしい adalah kata sifat-い yang berarti "ingin memiliki". Karena kata sifat, bendanya ditandai が seperti pada 好《す》き dan わかります. Hanya dipakai untuk keinginan penutur sendiri (atau ditanyakan langsung pada lawan bicara); untuk keinginan orang ketiga dipakai bentuk lain.',
                    'explanation_en' => 'hoshii is an i-adjective meaning "want to have". Being an adjective, the thing wanted is marked with が, just like suki and wakarimasu. It is used only for the speaker\'s own want (or asked directly of the listener); a third person\'s want needs a different form.',
                    'notes_id' => [
                        'Negatif: ほしくないです.',
                        'Jangan disamakan dengan もらいます (menerima) — ほしい murni menyatakan keinginan, belum tentu terjadi.',
                    ],
                    'notes_en' => [
                        'Negative: hoshikunai desu.',
                        'Do not confuse with moraimasu (to receive) — hoshii is purely the want, not necessarily fulfilled.',
                    ],
                    'examples' => [
                        ['ja' => '新《あたら》しい ケータイが ほしいです。', 'reading' => 'Atarashii keetai ga hoshii desu.', 'id' => 'Saya ingin HP baru.', 'en' => 'I want a new phone.'],
                        ['ja' => '今《いま》 何《なに》が ほしいですか。', 'reading' => 'Ima nani ga hoshii desu ka.', 'id' => 'Sekarang ingin apa?', 'en' => 'What do you want right now?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Kado ulang tahun',
                        'title_en' => 'A birthday present',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '誕生日《たんじょうび》に 何《なに》が ほしいですか。', 'reading' => 'Tanjoubi ni nani ga hoshii desu ka.', 'id' => 'Waktu ulang tahun mau apa?', 'en' => 'What do you want for your birthday?'],
                            ['speaker' => 'ワヒュ', 'ja' => '新《あたら》しい カメラが ほしいです。', 'reading' => 'Atarashii kamera ga hoshii desu.', 'id' => 'Saya ingin kamera baru.', 'en' => 'I want a new camera.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KKます-stem＋たいです',
                'title_en' => 'V-stem + tai desu',
                'pattern' => 'KK(ます形)＋たいです',
                'payload' => [
                    'explanation_id' => '〜たい dibentuk dari bentuk ます dengan mengganti ます menjadi たい: 行《い》きます → 行《い》きたい. Artinya "ingin melakukan". Seperti ほしい, たい adalah kata sifat-い, jadi bernegasi 〜たくないです dan lampau 〜たかったです. Objeknya boleh ditandai を atau が.',
                    'explanation_en' => '-tai is built from the -masu form by swapping masu for tai: ikimasu becomes ikitai. It means "want to do". Like hoshii, tai is an i-adjective, so its negative is -takunai desu and its past is -takatta desu. The object may take を or が.',
                    'notes_id' => [
                        'Sama seperti ほしい, hanya untuk keinginan penutur sendiri (atau pertanyaan langsung).',
                        'り-verba (かえります) mengikuti pola sama: かえりたいです.',
                    ],
                    'notes_en' => [
                        'Like hoshii, this is only for the speaker\'s own want (or a direct question).',
                        '-ri verbs (kaerimasu) follow the same pattern: kaeritai desu.',
                    ],
                    'examples' => [
                        ['ja' => '日本《にほん》へ 行《い》きたいです。', 'reading' => 'Nihon e ikitai desu.', 'id' => 'Saya ingin pergi ke Jepang.', 'en' => 'I want to go to Japan.'],
                        ['ja' => '何《なに》を 食《た》べたいですか。', 'reading' => 'Nani o tabetai desu ka.', 'id' => 'Mau makan apa?', 'en' => 'What do you want to eat?'],
                        ['ja' => 'きのうは 早《はや》く 帰《かえ》りたかったです。', 'reading' => 'Kinou wa hayaku kaeritakatta desu.', 'id' => 'Kemarin saya ingin cepat pulang.', 'en' => 'Yesterday I wanted to go home early.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Rencana liburan',
                        'title_en' => 'Holiday plans',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => '休《やす》みに どこへ 行《い》きたいですか。', 'reading' => 'Yasumi ni doko e ikitai desu ka.', 'id' => 'Waktu libur mau ke mana?', 'en' => 'Where do you want to go on your break?'],
                            ['speaker' => 'ワヒュ', 'ja' => '京都《きょうと》へ 行《い》きたいです。お寺《てら》を 見《み》たいですから。', 'reading' => 'Kyouto e ikitai desu. Otera o mitai desu kara.', 'id' => 'Ingin ke Kyoto. Karena ingin melihat kuil.', 'en' => 'I want to go to Kyoto — I want to see the temples.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'ほしい／〜たい: batas pemakaian',
                'title_en' => 'Limits on hoshii / -tai',
                'pattern' => 'ほしいです／〜たいです (perhatian)',
                'payload' => [
                    'explanation_id' => 'ほしいです dan 〜たいです hanya dipakai untuk menyatakan keinginan si pembicara atau menanyakan keinginan lawan bicara. Keduanya tidak dapat dipakai untuk menyatakan keinginan pihak ketiga. Keduanya juga tidak dipakai untuk menawarkan sesuatu kepada lawan bicara.',
                    'explanation_en' => 'ほしいです and 〜たいです only express the speaker\'s own wish or ask about the listener\'s wish. They cannot express the wish of a third person, and they are not used to offer something to the listener.',
                    'notes_id' => [
                        'Untuk menawarkan kopi, jangan bilang コーヒーが ほしいですか／飲《の》みたいですか. Pakai コーヒーは いかがですか atau コーヒーを 飲《の》みませんか。',
                        'Bentuk negatif: ほしくないです／〜たくないです.',
                    ],
                    'notes_en' => [
                        'To offer coffee, do not say コーヒーが ほしいですか／飲《の》みたいですか. Use コーヒーは いかがですか or コーヒーを 飲《の》みませんか。',
                        'Negative: ほしくないです／〜たくないです.',
                    ],
                    'examples' => [
                        [
                            'ja' => 'わたしは 車《くるま》が ほしいです。',
                            'reading' => 'Watashi wa kuruma ga hoshii desu.',
                            'id' => 'Saya ingin punya mobil.',
                            'en' => 'I want a car.',
                        ],
                        [
                            'ja' => '今《いま》 何《なに》が いちばん ほしいですか。',
                            'reading' => 'Ima nani ga ichiban hoshii desu ka.',
                            'id' => 'Sekarang apa yang paling diinginkan?',
                            'en' => 'What do you want most right now?',
                        ],
                        [
                            'ja' => 'コーヒーは いかがですか。／コーヒーを 飲《の》みませんか。',
                            'reading' => 'Koohii wa ikaga desu ka. / Koohii o nomimasen ka.',
                            'id' => 'Bagaimana kalau kopi? (menawarkan)',
                            'en' => 'Would you like some coffee? (offering)',
                        ],
                        [
                            'ja' => 'きょうは 何《なに》も 食《た》べたくないです。',
                            'reading' => 'Kyou wa nani mo tabetakunai desu.',
                            'id' => 'Hari ini tidak ingin makan apa-apa.',
                            'en' => 'I don\'t want to eat anything today.',
                        ],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menawarkan minuman',
                        'title_en' => 'Offering a drink',
                        'lines' => [
                            [
                                'speaker' => 'リナ',
                                'ja' => 'コーヒーは いかがですか。',
                                'reading' => 'Koohii wa ikaga desu ka.',
                                'id' => 'Bagaimana kalau kopi?',
                                'en' => 'How about some coffee?',
                            ],
                            [
                                'speaker' => 'ワヒュ',
                                'ja' => 'ありがとう。いただきます。',
                                'reading' => 'Arigatou. Itadakimasu.',
                                'id' => 'Terima kasih. Saya minum ya.',
                                'en' => 'Thank you. I\'ll have some.',
                            ],
                        ],
                    ],
                ],
            ],

            [
                // Official point 3: purpose of a trip (V-stem/noun + ni + ikimasu/kimasu/kaerimasu).
                'title_id' => 'KK(ます形)／KB＋に 行きます・来ます・帰ります',
                'title_en' => 'V-stem/Noun + ni + ikimasu/kimasu/kaerimasu (going somewhere to do)',
                'pattern' => '(場所)へ KK(ます形)／KBに 行きます',
                'payload' => [
                    'explanation_id' => 'Untuk menyatakan tujuan sebuah perjalanan, tempelkan に setelah kata benda aktivitas (買《か》い物《もの》に) atau bentuk ます kata kerja tanpa ます-nya sendiri (見《み》に), lalu tempat dengan へ, baru kata kerja gerak. に di sini menandai maksud kepergian.',
                    'explanation_en' => 'To state the purpose of a trip, attach に after an activity noun (kaimono ni) or a verb\'s masu-stem (mi ni), then the place with へ, then the motion verb. に here marks the purpose of going.',
                    'notes_id' => [
                        'Hanya kata kerja gerak (行《い》きます／来《き》ます／帰《かえ》ります) yang memakai pola ini.',
                        'Urutan tempat dan tujuan boleh dibalik: 京都《きょうと》へ 花見《はなみ》に 行《い》きます, atau 花見《はなみ》に 京都《きょうと》へ 行《い》きます。',
                    ],
                    'notes_en' => [
                        'Only motion verbs (ikimasu/kimasu/kaerimasu) take this pattern.',
                        'Place and purpose can swap order: Kyouto e hanami ni ikimasu, or hanami ni Kyouto e ikimasu.',
                    ],
                    'examples' => [
                        ['ja' => 'デパートへ 買《か》い物《もの》に 行《い》きます。', 'reading' => 'Depaato e kaimono ni ikimasu.', 'id' => 'Saya pergi ke mal untuk belanja.', 'en' => 'I am going to the department store to shop.'],
                        ['ja' => '図書館《としょかん》へ 本《ほん》を 借《か》りに 行《い》きます。', 'reading' => 'Toshokan e hon o kari ni ikimasu.', 'id' => 'Saya pergi ke perpustakaan untuk pinjam buku.', 'en' => 'I am going to the library to borrow a book.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Ke mana akhir pekan',
                        'title_en' => 'Where to on the weekend',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => '週末《しゅうまつ》 どこかへ 行《い》きますか。', 'reading' => 'Shuumatsu dokoka e ikimasu ka.', 'id' => 'Akhir pekan mau pergi ke suatu tempat?', 'en' => 'Are you going anywhere this weekend?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい、映画《えいが》を 見《み》に 行《い》きます。', 'reading' => 'Hai, eiga o mi ni ikimasu.', 'id' => 'Ya, mau nonton film.', 'en' => 'Yes, I am going to watch a movie.'],
                        ],
                    ],
                ],
            ],

            [
                // Official point 4.
                'title_id' => 'どこか／何か (kata tanya＋か)',
                'title_en' => 'dokoka / nanika (somewhere / something)',
                'pattern' => 'どこか／何か',
                'payload' => [
                    'explanation_id' => 'Menambahkan か di belakang kata tanya seperti どこ atau 何《なに》 mengubahnya menjadi "suatu tempat/sesuatu" yang tidak tertentu — dipakai saat pembicara sendiri belum tahu atau belum menentukan apa/di mananya.',
                    'explanation_en' => 'Adding か after a question word like どこ or 何《なに》 turns it into "somewhere" or "something" — an unspecified thing, used when the speaker themself does not know or has not decided what/where it is.',
                    'notes_id' => [
                        'Jawaban ya/tidak untuk pertanyaan ini tetap はい／いいえ dulu, bukan langsung menyebut tempatnya: どこかへ 行《い》きますか。－はい、行《い》きます。',
                        'Kalau memang tidak ada sama sekali, jawab いいえ、どこ（に）も／何《なに》も…ません。',
                    ],
                    'notes_en' => [
                        'The yes/no answer to this kind of question is still はい／いいえ first, not the actual place: dokoka e ikimasu ka. -- Hai, ikimasu.',
                        'If there truly is nothing, answer iie, doko (ni) mo / nani mo … masen.',
                    ],
                    'examples' => [
                        ['ja' => '今日《きょう》 どこかへ 行《い》きますか。', 'reading' => 'Kyou dokoka e ikimasu ka.', 'id' => 'Hari ini mau pergi ke suatu tempat?', 'en' => 'Are you going somewhere today?'],
                        ['ja' => '何《なに》か 食《た》べましたか。', 'reading' => 'Nanika tabemashita ka.', 'id' => 'Sudah makan sesuatu?', 'en' => 'Have you eaten something?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Sebelum berangkat',
                        'title_en' => 'Before heading out',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '何《なに》か 飲《の》みますか。', 'reading' => 'Nanika nomimasu ka.', 'id' => 'Mau minum sesuatu?', 'en' => 'Would you like something to drink?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、けっこうです。', 'reading' => 'Iie, kekkou desu.', 'id' => 'Tidak usah, terima kasih.', 'en' => 'No, I am fine, thanks.'],
                        ],
                    ],
                ],
            ],

            [
                // Official point 5.
                'title_id' => 'ご〜 (awalan sopan)',
                'title_en' => 'go~ (polite prefix)',
                'pattern' => 'ご＋KB',
                'payload' => [
                    'explanation_id' => 'ご berfungsi sama seperti お dari Pelajaran 2 — awalan yang menunjukkan kesopanan/penghormatan — tapi umumnya menempel pada kata benda serapan dari bahasa Cina (kango), seperti ご家族《かぞく》 atau ご結婚《けっこん》。',
                    'explanation_en' => 'go works just like お from Lesson 2 — a polite/honorific prefix — but it generally attaches to Sino-Japanese vocabulary (kango), such as go-kazoku or go-kekkon.',
                    'notes_id' => [
                        'Tidak ada aturan pasti kapan お dan kapan ご dipakai — dihafal per kata.',
                        'Dipakai untuk menyapa milik/urusan lawan bicara secara sopan: ご家族《かぞく》は お元気《げんき》ですか。',
                    ],
                    'notes_en' => [
                        'There is no fixed rule for when お or ご is used — it is memorised word by word.',
                        'Used to politely refer to the listener\'s own family/matters: go-kazoku wa o-genki desu ka.',
                    ],
                    'examples' => [
                        ['ja' => 'ご家族《かぞく》は 元気《げんき》ですか。', 'reading' => 'Go-kazoku wa genki desu ka.', 'id' => 'Keluarga (Anda) sehat-sehat saja?', 'en' => 'Is your family well?'],
                        ['ja' => 'ご結婚《けっこん》、おめでとうございます。', 'reading' => 'Go-kekkon, omedetou gozaimasu.', 'id' => 'Selamat atas pernikahannya.', 'en' => 'Congratulations on your marriage.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menyapa keluarga',
                        'title_en' => 'Asking after someone\'s family',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'ご家族《かぞく》は 元気《げんき》ですか。', 'reading' => 'Go-kazoku wa genki desu ka.', 'id' => 'Keluarganya sehat?', 'en' => 'Is your family doing well?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい、みんな 元気《げんき》です。', 'reading' => 'Hai, minna genki desu.', 'id' => 'Ya, semuanya sehat.', 'en' => 'Yes, everyone is well.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 14 — Bentuk-て, minta izin/melarang
    // ------------------------------------------------------------------

    private function lesson14Cards(): array
    {
        return [
            [
                'title_id' => 'Kelompok kata kerja (I, II, III)',
                'title_en' => 'Verb groups (I, II, III)',
                'pattern' => 'KK: Kelompok I／II／III',
                'payload' => [
                    'explanation_id' => 'Kata kerja bahasa Jepang mengalami perubahan bentuk (konjugasi), dan kata-kata yang mengikutinya berbeda menurut bentuknya. Menurut cara konjugasi, kata kerja dibagi tiga kelompok. Kelompok I: bunyi terakhir bentuk ます berada di baris い (mis. 書《か》きます, 飲《の》みます). Kelompok II: hampir semuanya berakhir dengan bunyi baris え (mis. 食《た》べます, 見《み》せます), sebagian kecil berakhir dengan baris い (mis. 見《み》ます). Kelompok III: します (juga kata benda aksi + します) dan 来《き》ます.',
                    'explanation_en' => 'Japanese verbs change form (conjugate), and what can follow them depends on the form. By conjugation pattern, verbs fall into three groups. Group I: the sound before ます is in the い row (e.g. 書《か》きます, 飲《の》みます). Group II: almost all end in the え row (e.g. 食《た》べます, 見《み》せます), a few in the い row (e.g. 見《み》ます). Group III: します (also action noun + します) and 来《き》ます.',
                    'notes_id' => [
                        'Kelompok menentukan cara membentuk bentuk-て (dan bentuk lain di pelajaran berikutnya).',
                        '見《み》ます dan 起《お》きます berbunyi い tetapi termasuk Kelompok II.',
                    ],
                    'notes_en' => [
                        'The group decides how the te-form (and later forms) is made.',
                        '見《み》ます and 起《お》きます sound like the い row but belong to Group II.',
                    ],
                    'examples' => [
                        [
                            'ja' => '書《か》きます／飲《の》みます',
                            'reading' => 'kakimasu / nomimasu',
                            'id' => 'menulis／minum — Kelompok I',
                            'en' => 'write / drink — Group I',
                        ],
                        [
                            'ja' => '食《た》べます／見《み》せます',
                            'reading' => 'tabemasu / misemasu',
                            'id' => 'makan／memperlihatkan — Kelompok II',
                            'en' => 'eat / show — Group II',
                        ],
                        [
                            'ja' => '見《み》ます／起《お》きます',
                            'reading' => 'mimasu / okimasu',
                            'id' => 'melihat／bangun — Kelompok II (bunyi い)',
                            'en' => 'see / get up — Group II (い sound)',
                        ],
                        [
                            'ja' => '勉強《べんきょう》します／来《き》ます',
                            'reading' => 'benkyou shimasu / kimasu',
                            'id' => 'belajar／datang — Kelompok III',
                            'en' => 'study / come — Group III',
                        ],
                    ],
                    'dialogue' => null,
                ],
            ],

            [
                'title_id' => 'Membentuk bentuk-て',
                'title_en' => 'Forming the te-form',
                'pattern' => 'KK(ます形)→KKて',
                'payload' => [
                    'explanation_id' => 'Bentuk-て adalah bentuk kata kerja yang tidak menyatakan waktu sendiri, dipakai untuk menyambung kalimat atau membentuk ungkapan lain. Aturannya tergantung kelompok kata kerja: kelompok II (tipe 食《た》べます) tinggal ganti ます→て (食《た》べます→食《た》べて); kelompok III tidak beraturan (します→して、来《き》ます→来《き》て); kelompok I berubah menurut bunyi terakhir sebelum ます (書《か》きます→書《か》いて、飲《の》みます→飲《の》んで、買《か》います→買《か》って, dan seterusnya).',
                    'explanation_en' => 'The te-form carries no tense of its own; it is used to link sentences or build other expressions. The rule depends on the verb group: group II (tabemasu type) simply swaps masu for te (tabemasu -> tabete); group III is irregular (shimasu -> shite, kimasu -> kite); group I changes according to the sound before masu (kakimasu -> kaite, nomimasu -> nonde, kaimasu -> katte, and so on).',
                    'notes_id' => [
                        'Pola perubahan kelompok I sama dengan bentuk た (bentuk lampau biasa), hanya て/た yang berbeda.',
                        'Hafalkan bentuk-て sebagai satu kesatuan bersama kata kerjanya, bukan dihafal aturan dulu baru diterapkan setiap kali.',
                    ],
                    'notes_en' => [
                        'Group I follows the same shift pattern as the plain past (-ta) form — only te/ta differs.',
                        'Memorise each te-form together with its verb as one unit, rather than reapplying the rule every time.',
                    ],
                    'examples' => [
                        ['ja' => '食《た》べます → 食《た》べて', 'reading' => 'tabemasu -> tabete', 'id' => 'makan → (bentuk-て)', 'en' => 'to eat -> te-form'],
                        ['ja' => '書《か》きます → 書《か》いて', 'reading' => 'kakimasu -> kaite', 'id' => 'menulis → (bentuk-て)', 'en' => 'to write -> te-form'],
                        ['ja' => 'します → して', 'reading' => 'shimasu -> shite', 'id' => 'melakukan → (bentuk-て)', 'en' => 'to do -> te-form'],
                    ],
                    'dialogue' => null,
                ],
            ],

            [
                'title_id' => 'KKて ください',
                'title_en' => 'V-te kudasai',
                'pattern' => '〜てください',
                'payload' => [
                    'explanation_id' => 'Bentuk-て + ください berarti "tolong lakukan…". Dipakai untuk meminta atau menyuruh dengan sopan. Lebih halus dari perintah langsung, tetapi tetap terdengar sebagai instruksi, jadi kurang cocok dipakai pada atasan.',
                    'explanation_en' => 'te-form + kudasai means "please do…". It is used to ask or instruct politely. It is softer than a bare command, but still sounds like an instruction, so it is not quite right to use on a superior.',
                    'notes_id' => [
                        'すみませんが、〜てください terdengar lebih sopan.',
                        '〜てください ませんか lebih halus lagi, menanyakan kesediaan.',
                    ],
                    'notes_en' => [
                        'sumimasen ga, … te kudasai sounds more polite.',
                        '… te kudasaimasen ka is softer still, asking about willingness.',
                    ],
                    'examples' => [
                        ['ja' => 'ここに 名前《なまえ》を 書《か》いてください。', 'reading' => 'Koko ni namae o kaite kudasai.', 'id' => 'Tolong tulis nama di sini.', 'en' => 'Please write your name here.'],
                        ['ja' => 'ちょっと 待《ま》ってください。', 'reading' => 'Chotto matte kudasai.', 'id' => 'Tolong tunggu sebentar.', 'en' => 'Please wait a moment.'],
                        ['ja' => 'すみませんが、パスポートを 見《み》せてくださいませんか。', 'reading' => 'Sumimasen ga, pasupooto o misete kudasaimasen ka.', 'id' => 'Maaf, bolehkah saya lihat paspornya?', 'en' => 'Excuse me, could you please show me your passport?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di loket stasiun',
                        'title_en' => 'At the station counter',
                        'lines' => [
                            ['speaker' => '駅員《えきいん》', 'ja' => 'ここに 住所《じゅうしょ》を 書《か》いてください。', 'reading' => 'Koko ni juusho o kaite kudasai.', 'id' => 'Tolong tulis alamat di sini.', 'en' => 'Please write your address here.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい、わかりました。', 'reading' => 'Hai, wakarimashita.', 'id' => 'Baik.', 'en' => 'All right.'],
                            ['speaker' => '駅員《えきいん》', 'ja' => 'ちょっと 待《ま》ってください。', 'reading' => 'Chotto matte kudasai.', 'id' => 'Tolong tunggu sebentar.', 'en' => 'Please wait a moment.'],
                        ],
                    ],
                ],
            ],

            [
                // Official point 4 (moved here from Pelajaran 15, where it had been
                // misplaced — this is the "sedang berlangsung" reading of ~ています,
                // which is genuinely Pelajaran 14's own grammar point).
                'title_id' => 'KKています (sedang berlangsung)',
                'title_en' => 'V-te imasu (in progress)',
                'pattern' => '〜ています',
                'payload' => [
                    'explanation_id' => 'Bentuk-て + います menyatakan perbuatan yang sedang berlangsung saat ini, seperti "sedang …". Bentuknya dari bentuk-て kata kerja ditambah います (います di sini berfungsi seperti kata bantu, bukan "ada").',
                    'explanation_en' => 'te-form + imasu describes an action happening right now, "is …ing". It is built from the verb\'s te-form plus imasu (here imasu works as an auxiliary, not as "exist").',
                    'notes_id' => [
                        'Pertanyaan: 何《なに》を していますか.',
                        'Negatifnya: 〜ていません.',
                    ],
                    'notes_en' => [
                        'Question: nani o shiteimasu ka.',
                        'Negative: … teimasen.',
                    ],
                    'examples' => [
                        ['ja' => '今《いま》 新聞《しんぶん》を 読《よ》んでいます。', 'reading' => 'Ima shinbun o yondeimasu.', 'id' => 'Sekarang sedang membaca koran.', 'en' => 'I am reading the newspaper right now.'],
                        ['ja' => 'サリさんは 今《いま》 電話《でんわ》を しています。', 'reading' => 'Sari-san wa ima denwa o shiteimasu.', 'id' => 'Sari sedang menelepon.', 'en' => 'Sari is on the phone right now.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menelepon di kantor',
                        'title_en' => 'A call at the office',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'たなかさんは どこですか。', 'reading' => 'Tanaka-san wa doko desu ka.', 'id' => 'Tanaka di mana?', 'en' => 'Where is Mr./Ms. Tanaka?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'あそこで 電話《でんわ》を しています。', 'reading' => 'Asoko de denwa o shiteimasu.', 'id' => 'Sedang menelepon di sana.', 'en' => 'On the phone over there.'],
                        ],
                    ],
                ],
            ],

            [
                // Official point 5.
                'title_id' => 'KK(ます形)＋ましょうか',
                'title_en' => 'V-stem + mashou ka (shall I/we?)',
                'pattern' => '〜ましょうか',
                'payload' => [
                    'explanation_id' => 'ましょうか dipakai untuk menawarkan bantuan atau mengajak dengan sopan, mirip "bagaimana kalau saya/kita …?". Berbeda dengan ましょう (ayo, tanpa か) yang mengajak langsung, ましょうか lebih dulu menanyakan kesediaan/pendapat lawan bicara.',
                    'explanation_en' => 'mashou ka politely offers help or suggests doing something together, like "shall I/we …?". Unlike plain mashou (let\'s, without か), which invites directly, mashou ka first checks the listener\'s willingness or opinion.',
                    'notes_id' => [
                        'Menjawab menerima: ええ、おねがいします／ええ、そうしましょう。',
                        'Menjawab menolak halus: いいえ、けっこうです。',
                    ],
                    'notes_en' => [
                        'Accepting reply: ee, onegaishimasu / ee, sou shimashou.',
                        'Politely declining: iie, kekkou desu.',
                    ],
                    'examples' => [
                        ['ja' => '荷物《にもつ》を 持《も》ちましょうか。', 'reading' => 'Nimotsu o mochimashou ka.', 'id' => 'Mau saya bawakan barangnya?', 'en' => 'Shall I carry your luggage?'],
                        ['ja' => 'まどを 閉《し》めましょうか。', 'reading' => 'Mado o shimemashou ka.', 'id' => 'Mau saya tutup jendelanya?', 'en' => 'Shall I close the window?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menawarkan bantuan',
                        'title_en' => 'Offering to help',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '重《おも》そうですね。持《も》ちましょうか。', 'reading' => 'Omosou desu ne. Mochimashou ka.', 'id' => 'Kelihatannya berat ya. Mau saya bawakan?', 'en' => 'That looks heavy. Shall I carry it?'],
                            ['speaker' => 'サリ', 'ja' => 'すみません、おねがいします。', 'reading' => 'Sumimasen, onegaishimasu.', 'id' => 'Terima kasih, tolong ya.', 'en' => 'Thanks, please do.'],
                        ],
                    ],
                ],
            ],

            [
                // Official point 6.
                'title_id' => 'KB が KK (menyatakan sesuatu terjadi/muncul)',
                'title_en' => 'Noun ga verb (something happening/appearing)',
                'pattern' => 'KBが KK',
                'payload' => [
                    'explanation_id' => 'Partikel が (bukan は) dipakai ketika kalimat sekadar melaporkan bahwa sesuatu sedang terjadi atau muncul di depan mata, tanpa menjadikannya topik yang sudah diketahui — sering dipakai untuk mendeskripsikan pemandangan atau suara.',
                    'explanation_en' => 'が (not は) is used when a sentence simply reports that something is happening or appearing right in front of you, without making it an already-known topic — often used to describe a scene or a sound.',
                    'notes_id' => [
                        'Bandingkan: 電話《でんわ》が 鳴《な》っています (menonjolkan "ada telepon berbunyi") vs 電話《でんわ》は 鳴《な》っています (membahas telepon yang sudah dikenal).',
                        'Sering muncul bersama ています untuk mendeskripsikan keadaan yang sedang berlangsung.',
                    ],
                    'notes_en' => [
                        'Compare: denwa ga natteimasu (highlighting "a phone is ringing") vs denwa wa natteimasu (discussing an already-known phone).',
                        'Often paired with teimasu to describe an ongoing scene.',
                    ],
                    'examples' => [
                        ['ja' => 'あ、雨《あめ》が 降《ふ》っています。', 'reading' => 'A, ame ga futteimasu.', 'id' => 'Ah, hujan turun.', 'en' => 'Oh, it is raining.'],
                        ['ja' => '子供《こども》が 泣《な》いています。', 'reading' => 'Kodomo ga naiteimasu.', 'id' => 'Ada anak yang menangis.', 'en' => 'A child is crying.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di luar jendela',
                        'title_en' => 'Outside the window',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'あ、雪《ゆき》が 降《ふ》っていますよ。', 'reading' => 'A, yuki ga futteimasu yo.', 'id' => 'Ah, lihat, salju turun.', 'en' => 'Oh look, it is snowing.'],
                            ['speaker' => 'ワヒュ', 'ja' => '本当《ほんとう》だ！ きれいですね。', 'reading' => 'Hontou da! Kirei desu ne.', 'id' => 'Benar! Indah ya.', 'en' => "Really! It's beautiful."],
                        ],
                    ],
                ],
            ],

            [
                // Official point 7.
                'title_id' => 'すみませんが、〜 (basa-basi sebelum meminta)',
                'title_en' => 'sumimasen ga, … (softening a request)',
                'pattern' => 'すみませんが、〜',
                'payload' => [
                    'explanation_id' => 'すみませんが disisipkan di awal kalimat sebelum menyampaikan permintaan, pertanyaan, atau penolakan, supaya terdengar lebih sopan dan tidak terkesan tiba-tiba. が di sini hanya menghubungkan secara halus, bukan "tetapi" yang bermakna kontras kuat.',
                    'explanation_en' => 'sumimasen ga is placed at the start of a sentence before making a request, asking a question, or declining something, to sound more polite and less abrupt. が here just softens the connection — it is not a strong-contrast "but".',
                    'notes_id' => [
                        'Sangat sering dipakai sebelum bertanya kepada orang asing: すみませんが、駅《えき》は どこですか。',
                        'Juga dipakai untuk menolak dengan sopan: すみませんが、ちょっと….',
                    ],
                    'notes_en' => [
                        'Very commonly used before asking a stranger something: sumimasen ga, eki wa doko desu ka.',
                        'Also used to politely decline: sumimasen ga, chotto….',
                    ],
                    'examples' => [
                        ['ja' => 'すみませんが、写真《しゃしん》を とってください。', 'reading' => 'Sumimasen ga, shashin o totte kudasai.', 'id' => 'Maaf, tolong fotokan saya.', 'en' => 'Excuse me, could you take a photo, please?'],
                        ['ja' => 'すみませんが、今日《きょう》は ちょっと….', 'reading' => 'Sumimasen ga, kyou wa chotto….', 'id' => 'Maaf, hari ini agak (tidak bisa)….', 'en' => 'Sorry, today is a bit (difficult)….'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Minta tolong difoto',
                        'title_en' => 'Asking for a photo',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみませんが、写真《しゃしん》を とってくださいませんか。', 'reading' => 'Sumimasen ga, shashin o totte kudasaimasen ka.', 'id' => 'Maaf, bisa tolong fotokan saya?', 'en' => 'Excuse me, could you possibly take our photo?'],
                            ['speaker' => 'サリ', 'ja' => 'ええ、いいですよ。', 'reading' => 'Ee, ii desu yo.', 'id' => 'Ya, boleh.', 'en' => 'Sure, no problem.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 15 — ています (kebiasaan/hasil), izin & larangan, letak
    // ------------------------------------------------------------------

    private function lesson15Cards(): array
    {
        return [
            [
                // Official point 1 (moved here from Pelajaran 14, where it had
                // been misplaced).
                'title_id' => 'KKても いいですか',
                'title_en' => 'V-te mo ii desu ka',
                'pattern' => '〜てもいいですか',
                'payload' => [
                    'explanation_id' => 'Bentuk-て + も いいですか berarti "bolehkah saya …?" — meminta izin. も berarti "juga/walaupun", jadi harfiahnya "walaupun melakukan …, apakah baik-baik saja?". Jawaban mengizinkan: ええ、いいですよ. Menolak: すみません、ちょっと….',
                    'explanation_en' => 'te-form + mo ii desu ka means "may I …?" — asking permission. mo means "also/even", so literally "even if I do …, is it all right?". A granting answer: ee, ii desu yo. A refusal: sumimasen, chotto….',
                    'notes_id' => [
                        'Menjawab dengan tegas menolak: いいえ、〜ないでください (dipelajari Pelajaran 17).',
                        'てもいいですか juga dipakai untuk menawarkan bantuan: 手伝《てつだ》ってもいいですか (bolehkah saya membantu?).',
                    ],
                    'notes_en' => [
                        'A firm refusal: iie, … nai de kudasai (covered in Lesson 17).',
                        'te mo ii desu ka is also used to offer help: tetsudatte mo ii desu ka (may I help?).',
                    ],
                    'examples' => [
                        ['ja' => 'ここに 座《すわ》っても いいですか。', 'reading' => 'Koko ni suwatte mo ii desu ka.', 'id' => 'Bolehkah saya duduk di sini?', 'en' => 'May I sit here?'],
                        ['ja' => 'ええ、いいですよ。', 'reading' => 'Ee, ii desu yo.', 'id' => 'Ya, boleh.', 'en' => 'Yes, go ahead.'],
                        ['ja' => 'エアコンを 消《け》しても いいですか。', 'reading' => 'Eakon o keshite mo ii desu ka.', 'id' => 'Bolehkah AC-nya dimatikan?', 'en' => 'May I turn off the AC?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Meminta izin',
                        'title_en' => 'Asking permission',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません、窓《まど》を 開《あ》けても いいですか。', 'reading' => 'Sumimasen, mado o akete mo ii desu ka.', 'id' => 'Permisi, bolehkah jendelanya dibuka?', 'en' => 'Excuse me, may I open the window?'],
                            ['speaker' => 'たなか', 'ja' => 'ええ、どうぞ。', 'reading' => 'Ee, douzo.', 'id' => 'Ya, silakan.', 'en' => 'Sure, go ahead.'],
                            ['speaker' => 'ワヒュ', 'ja' => '電気《でんき》も つけても いいですか。', 'reading' => 'Denki mo tsukete mo ii desu ka.', 'id' => 'Lampunya juga boleh dinyalakan?', 'en' => 'May I turn on the light too?'],
                        ],
                    ],
                ],
            ],

            [
                // Official point 2 (moved here from Pelajaran 14, where it had
                // been misplaced).
                'title_id' => 'KKては いけません',
                'title_en' => 'V-te wa ikemasen',
                'pattern' => '〜てはいけません',
                'payload' => [
                    'explanation_id' => 'Bentuk-て + はいけません menyatakan larangan tegas: "tidak boleh". Lebih keras daripada menolak permintaan izin — dipakai untuk peraturan, rambu, atau larangan yang berlaku umum, bukan sekadar pendapat pribadi.',
                    'explanation_en' => 'te-form + wa ikemasen states a firm prohibition: "must not". It is stronger than declining a request for permission — used for rules, signs, or general prohibitions rather than a personal opinion.',
                    'notes_id' => [
                        'は di sini menegaskan kontras, seperti pada kalimat negatif lain.',
                        'Dalam papan pengumuman sering dipersingkat menjadi 〜ないでください or hanya 禁止《きんし》 (dilarang).',
                    ],
                    'notes_en' => [
                        'は here adds emphasis by contrast, as in other negative sentences.',
                        'Signs often shorten it to … nai de kudasai, or just kinshi (prohibited).',
                    ],
                    'examples' => [
                        ['ja' => 'ここで たばこを 吸《す》ってはいけません。', 'reading' => 'Koko de tabako o sutte wa ikemasen.', 'id' => 'Tidak boleh merokok di sini.', 'en' => 'You must not smoke here.'],
                        ['ja' => 'この 部屋《へや》に 入《はい》ってはいけません。', 'reading' => 'Kono heya ni haitte wa ikemasen.', 'id' => 'Tidak boleh masuk ke kamar ini.', 'en' => 'You must not enter this room.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Peraturan di kantor',
                        'title_en' => 'Office rules',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'ここで 電話《でんわ》を 使《つか》っては いけません。', 'reading' => 'Koko de denwa o tsukatte wa ikemasen.', 'id' => 'Tidak boleh pakai HP di sini.', 'en' => 'You must not use the phone here.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'あ、すみません。', 'reading' => 'A, sumimasen.', 'id' => 'Ah, maaf.', 'en' => 'Oh, sorry.'],
                        ],
                    ],
                ],
            ],

            [
                // Official point 3 (kebiasaan): one of the two nuances of ~ています
                // taught in this lesson, alongside the "hasil keadaan" card below.
                'title_id' => 'KKています (pekerjaan/kebiasaan)',
                'title_en' => 'V-te imasu (occupation/habit)',
                'pattern' => '〜で 働《はたら》いています',
                'payload' => [
                    'explanation_id' => 'ています juga dipakai untuk menyatakan kebiasaan atau kegiatan yang berulang dalam jangka panjang, termasuk pekerjaan: 銀行《ぎんこう》で 働《はたら》いています berarti "bekerja di bank" sebagai kondisi tetap, bukan sedang terjadi detik ini.',
                    'explanation_en' => 'teimasu also states a long-running habit or repeated activity, including a job: ginkou de hataraiteimasu means "I work at a bank" as an ongoing state, not something happening this very second.',
                    'notes_id' => [
                        'Bandingkan dengan 〜ます biasa yang lebih menyatakan kejadian satu kali di masa depan/kebiasaan sederhana.',
                        'Untuk pekerjaan tetap, ています lebih wajar dipakai daripada ます.',
                    ],
                    'notes_en' => [
                        'Compare with plain -masu, which leans toward a one-off future event or a simple habitual statement.',
                        'For a settled job, teimasu sounds more natural than plain -masu.',
                    ],
                    'examples' => [
                        ['ja' => '兄《あに》は 銀行《ぎんこう》で 働《はたら》いています。', 'reading' => 'Ani wa ginkou de hataraiteimasu.', 'id' => 'Kakak laki-laki saya bekerja di bank.', 'en' => 'My older brother works at a bank.'],
                        ['ja' => '毎朝《まいあさ》 新聞《しんぶん》を 読《よ》んでいます。', 'reading' => 'Maiasa shinbun o yondeimasu.', 'id' => 'Setiap pagi saya membaca koran.', 'en' => 'I read the newspaper every morning.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Pekerjaan keluarga',
                        'title_en' => "Family's jobs",
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'お兄《にい》さんは 何《なに》を していますか。', 'reading' => 'Oniisan wa nani o shiteimasu ka.', 'id' => 'Kakakmu kerja apa?', 'en' => "What does your older brother do?"],
                            ['speaker' => 'ワヒュ', 'ja' => '会社《かいしゃ》で 働《はたら》いています。', 'reading' => 'Kaisha de hataraiteimasu.', 'id' => 'Bekerja di perusahaan.', 'en' => 'He works at a company.'],
                        ],
                    ],
                ],
            ],

            [
                // Also part of official point 3 (hasil keadaan nuance).
                'title_id' => 'KKています (hasil keadaan)',
                'title_en' => 'V-te imasu (resulting state)',
                'pattern' => '結婚《けっこん》しています',
                'payload' => [
                    'explanation_id' => 'Dengan sekelompok kata kerja tertentu — 結婚《けっこん》します、住《す》みます、知《し》っています, dan beberapa lagi — ています tidak menyatakan sedang terjadi, melainkan hasil/keadaan yang berlaku sekarang akibat kejadian di masa lalu. 結婚《けっこん》しています berarti "sudah menikah (dan masih)", bukan "sedang menikah".',
                    'explanation_en' => 'With a specific set of verbs — kekkon shimasu, sumimasu, shitteimasu, and a few others — teimasu does not mean "in progress"; it means a resulting state that still holds now because of a past event. kekkon shiteimasu means "is married", not "is in the middle of getting married".',
                    'notes_id' => [
                        '知《し》っています khusus: negatifnya bukan 知《し》っていません, melainkan 知《し》りません.',
                        '住《す》んでいます = tinggal (di suatu tempat, sebagai keadaan menetap).',
                    ],
                    'notes_en' => [
                        'shitteimasu is special: its negative is not shitteimasen but shirimasen.',
                        'sundeimasu = live (somewhere, as a settled state).',
                    ],
                    'examples' => [
                        ['ja' => 'わたしは 結婚《けっこん》しています。', 'reading' => 'Watashi wa kekkon shiteimasu.', 'id' => 'Saya sudah menikah.', 'en' => 'I am married.'],
                        ['ja' => 'サリさんの 住所《じゅうしょ》を 知《し》っていますか。', 'reading' => 'Sari-san no juusho o shitteimasu ka.', 'id' => 'Kamu tahu alamat Sari?', 'en' => 'Do you know Sari\'s address?'],
                        ['ja' => 'いいえ、知《し》りません。', 'reading' => 'Iie, shirimasen.', 'id' => 'Tidak, saya tidak tahu.', 'en' => 'No, I do not.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Sudah tahu belum',
                        'title_en' => 'Do you know yet',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'たなかさんの 電話番号《でんわばんごう》を 知《し》っていますか。', 'reading' => 'Tanaka-san no denwa bangou o shitteimasu ka.', 'id' => 'Tahu nomor telepon Tanaka?', 'en' => 'Do you know Mr./Ms. Tanaka\'s phone number?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、知《し》りません。', 'reading' => 'Iie, shirimasen.', 'id' => 'Tidak tahu.', 'en' => 'No, I do not.'],
                        ],
                    ],
                ],
            ],

            [
                // Official point 4.
                'title_id' => 'KB(場所) に KK (menetap/berada — bukan sekadar beraktivitas)',
                'title_en' => 'Place ni + verb (a settled state, not just an action)',
                'pattern' => '(場所)に KK',
                'payload' => [
                    'explanation_id' => 'Sejumlah kata kerja yang menyatakan keadaan menetap — seperti 住《す》みます (tinggal) atau つとめます (bekerja tetap di suatu tempat) — memakai partikel に untuk tempatnya, bukan で. で dipakai untuk tempat terjadinya sebuah aktivitas biasa, sedangkan に di sini menandai tempat keberadaan/keterikatannya.',
                    'explanation_en' => 'A handful of verbs describing a settled state — like sumimasu (to live) or tsutomemasu (to be employed at a place) — take に for their location instead of で. で marks where an ordinary action happens, while に here marks where something/someone is anchored.',
                    'notes_id' => [
                        '住《す》んでいます (sedang/sudah tinggal) hampir selalu dipakai dalam bentuk ています, bukan bentuk ます polos.',
                        'Bandingkan: 図書館《としょかん》で 勉強《べんきょう》します (で, aktivitas) vs 図書館《としょかん》に います (に, keberadaan).',
                    ],
                    'notes_en' => [
                        'sundeimasu (living/have been living) almost always appears in the teimasu form, not the plain -masu form.',
                        'Compare: toshokan de benkyou shimasu (de, an activity) vs toshokan ni imasu (ni, being located).',
                    ],
                    'examples' => [
                        ['ja' => 'わたしは 東京《とうきょう》に 住《す》んでいます。', 'reading' => 'Watashi wa Toukyou ni sundeimasu.', 'id' => 'Saya tinggal di Tokyo.', 'en' => 'I live in Tokyo.'],
                        ['ja' => '兄《あに》は 銀行《ぎんこう》に つとめています。', 'reading' => 'Ani wa ginkou ni tsutometeimasu.', 'id' => 'Kakak saya bekerja tetap di bank.', 'en' => 'My brother is employed at a bank.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Tempat tinggal',
                        'title_en' => 'Where you live',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'どこに 住《す》んでいますか。', 'reading' => 'Doko ni sundeimasu ka.', 'id' => 'Tinggal di mana?', 'en' => 'Where do you live?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ジャカルタに 住《す》んでいます。', 'reading' => 'Jakaruta ni sundeimasu.', 'id' => 'Saya tinggal di Jakarta.', 'en' => 'I live in Jakarta.'],
                        ],
                    ],
                ],
            ],

            [
                // Official point 5.
                'title_id' => 'KB1に KB2を KK (meletakkan/menuliskan sesuatu pada suatu tempat)',
                'title_en' => 'Place ni + thing wo + verb (putting/writing something at/on a place)',
                'pattern' => '(場所)に (もの)を KK',
                'payload' => [
                    'explanation_id' => 'に di sini menandai titik tujuan tempat suatu benda diletakkan, ditulis, atau ditempelkan, sedangkan を tetap menandai objek yang dikenai perbuatan. Pola ini dipakai dengan kata kerja seperti 書《か》きます (menulis), 貼《は》ります (menempel), atau 入《い》れます (memasukkan).',
                    'explanation_en' => 'に here marks the destination point where something is placed, written, or attached, while を still marks the thing being acted on. This pattern goes with verbs like kakimasu (write), harimasu (stick/paste), or iremasu (put in).',
                    'notes_id' => [
                        'Jangan tertukar dengan で(alat/sarana): 紙《かみ》に ペンで 名前《なまえ》を 書《か》きます (menulis nama DI kertas, DENGAN pena).',
                        'Urutan tempat dan objek boleh ditukar asalkan partikelnya tetap menempel benar.',
                    ],
                    'notes_en' => [
                        'Do not confuse with で (tool/means): kami ni pen de namae o kakimasu (write a name ON paper, WITH a pen).',
                        'The place and the object can swap order as long as each keeps its own particle.',
                    ],
                    'examples' => [
                        ['ja' => 'かみに 名前《なまえ》を 書《か》きます。', 'reading' => 'Kami ni namae o kakimasu.', 'id' => 'Menulis nama di kertas.', 'en' => 'I write my name on the paper.'],
                        ['ja' => 'かべに カレンダーを はりました。', 'reading' => 'Kabe ni karendaa o harimashita.', 'id' => 'Saya menempelkan kalender di dinding.', 'en' => 'I put a calendar up on the wall.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menempel poster',
                        'title_en' => 'Putting up a poster',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'ここに ポスターを はっても いいですか。', 'reading' => 'Koko ni posutaa o hatte mo ii desu ka.', 'id' => 'Bolehkah menempel poster di sini?', 'en' => 'May I put a poster up here?'],
                            ['speaker' => 'たなか', 'ja' => 'ええ、いいですよ。', 'reading' => 'Ee, ii desu yo.', 'id' => 'Ya, boleh.', 'en' => 'Sure, go ahead.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 16 — bentuk-て berurutan, memberi petunjuk
    // ------------------------------------------------------------------

    private function lesson16Cards(): array
    {
        return [
            [
                'title_id' => 'Menyambung lebih dari dua kalimat',
                'title_en' => 'Joining more than two sentences',
                'pattern' => '〜て、〜て、〜／〜くて、〜／〜で、〜',
                'payload' => [
                    'explanation_id' => 'Bentuk-て（〜で）dipakai untuk menyambung lebih dari dua kalimat menjadi satu. Kata kerja: rangkaian bentuk-て berturut-turut, waktu ditentukan oleh kata kerja terakhir (朝《あさ》ジョギングをして、シャワーを浴《あ》びて、会社《かいしゃ》へ行《い》きます). Kata sifat-い: 〜い→〜くて (おおきい→おおきくて; kecuali いい→よくて). Kata sifat-な: cukup ganti な dengan で. Kata benda: cukup tambah で. Kalau kalimat yang disambung bersubjek sama tapi nilai/rasanya berlawanan (misal sempit tapi bersih), tidak boleh pakai 〜て — harus pakai が.',
                    'explanation_en' => 'The te-form (or de) joins more than two sentences into one. Verbs: chain te-forms in sequence; the tense is set by the final verb (I jog, shower, then go to work). i-adjectives: -i becomes -kute (ookii -> ookikute; exception ii -> yokute). na-adjectives: な becomes で. Nouns: simply add で. If the joined sentences share a subject but contrast in value (e.g. small but clean), て cannot be used — が is required instead.',
                    'notes_id' => [
                        'ミラーさんは 若《わか》くて、元気《げんき》です。（kata sifat-い）',
                        'ミラーさんは ハンサムで、親切《しんせつ》です。（kata sifat-な）',
                        'カリナさんは 学生《がくせい》で、マリアさんは 主婦《しゅふ》です。（kata benda, membandingkan dua orang）',
                        '× この部屋《へや》は 狭《せま》くて、きれいです。→ ○ この部屋《へや》は 狭《せま》いですが、きれいです。',
                    ],
                    'notes_en' => [
                        'Mr. Miller wa wakakute, genki desu. (i-adjective chain)',
                        'Mr. Miller wa hansamu de, shinsetsu desu. (na-adjective chain)',
                        'Karina-san wa gakusei de, Maria-san wa shufu desu. (comparing two people with nouns)',
                        'Wrong: kono heya wa semakute, kirei desu. -> Right: kono heya wa semai desu ga, kirei desu.',
                    ],
                    'examples' => [
                        ['ja' => '朝《あさ》 ジョギングを して、シャワーを 浴《あ》びて、会社《かいしゃ》へ 行《い》きます。', 'reading' => 'Asa jogingu o shite, shawaa o abite, kaisha e ikimasu.', 'id' => 'Pagi hari joging, mandi, lalu pergi ke kantor.', 'en' => 'In the morning I jog, shower, then go to the office.'],
                        ['ja' => 'この 部屋《へや》は 広《ひろ》くて、明《あか》るいです。', 'reading' => 'Kono heya wa hirokute, akarui desu.', 'id' => 'Kamar ini luas dan terang.', 'en' => 'This room is spacious and bright.'],
                        ['ja' => '奈良《なら》は 静《しず》かで、きれいな 町《まち》です。', 'reading' => 'Nara wa shizuka de, kirei na machi desu.', 'id' => 'Nara adalah kota yang tenang dan indah.', 'en' => 'Nara is a quiet, beautiful town.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Kegiatan pagi',
                        'title_en' => 'Morning routine',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'いつも 何時《なんじ》に 起《お》きますか。', 'reading' => 'Itsumo nanji ni okimasu ka.', 'id' => 'Biasanya bangun jam berapa?', 'en' => 'What time do you usually wake up?'],
                            ['speaker' => 'ワヒュ', 'ja' => '六時《ろくじ》に 起《お》きて、シャワーを 浴《あ》びて、それから 会社《かいしゃ》へ 行《い》きます。', 'reading' => 'Rokuji ni okite, shawaa o abite, sorekara kaisha e ikimasu.', 'id' => 'Bangun jam 6, mandi, lalu pergi ke kantor.', 'en' => 'I wake up at six, shower, then go to work.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KK1(てから)、KK2 (setelah melakukan …)',
                'title_en' => 'V1-te kara, V2 (after doing …)',
                'pattern' => '〜てから、〜',
                'payload' => [
                    'explanation_id' => 'てから menekankan bahwa Kata Kerja2 dilakukan SETELAH Kata Kerja1 selesai — Kata Kerja1 biasanya menjadi persiapan/prasyarat bagi Kata Kerja2. Waktu kalimat ditentukan oleh kata kerja terakhir.',
                    'explanation_en' => 'te kara stresses that the second verb happens AFTER the first one is finished — the first verb is usually a preparation or prerequisite for the second. The sentence\'s tense is set by the final verb.',
                    'notes_id' => [
                        'Berbeda dari 〜て、〜 biasa: てから lebih menonjolkan urutan sebab-akibat/prasyarat, bukan sekadar daftar kejadian.',
                    ],
                    'notes_en' => [
                        'Unlike a plain te-chain, te kara highlights a prerequisite relationship, not just a list of events.',
                    ],
                    'examples' => [
                        ['ja' => 'コンサートが 終《お》わってから、レストランで 食事《しょくじ》します。', 'reading' => 'Konsaato ga owatte kara, resutoran de shokuji shimasu.', 'id' => 'Setelah konser selesai, makan di restoran.', 'en' => 'After the concert ends, I\'ll eat at a restaurant.'],
                        ['ja' => '図書館《としょかん》へ 行《い》って 本《ほん》を 借《か》りてから、友達《ともだち》に 会《あ》いました。', 'reading' => 'Toshokan e itte hon o karite kara, tomodachi ni aimashita.', 'id' => 'Pergi ke perpustakaan untuk pinjam buku, lalu bertemu teman.', 'en' => 'I went to the library to borrow a book, then met a friend.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Rencana setelah rapat',
                        'title_en' => 'Plans after the meeting',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '今《いま》から 大阪城《おおさかじょう》を 見《み》に 行《い》きますか。', 'reading' => 'Ima kara Oosakajou o mi ni ikimasu ka.', 'id' => 'Sekarang mau lihat Benteng Osaka?', 'en' => 'Shall we go see Osaka Castle now?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、昼《ひる》ごはんを 食《た》べてから 見《み》に 行《い》きます。', 'reading' => 'Iie, hirugohan o tabete kara mi ni ikimasu.', 'id' => 'Tidak, setelah makan siang baru pergi lihat.', 'en' => 'No, we\'ll go see it after eating lunch.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KB1は KB2が Kata Sifat',
                'title_en' => 'KB1 wa KB2 ga adjective',
                'pattern' => '〜は 〜が 〜',
                'payload' => [
                    'explanation_id' => 'は menandai topik besar (misalnya nama kota/tempat), sedangkan が menandai bagian tertentu dari topik itu yang dideskripsikan oleh kata sifat. Pola ini dipakai untuk menjelaskan ciri khas suatu tempat atau hal.',
                    'explanation_en' => 'は marks the overall topic (e.g. a city or place), while が marks the specific aspect of that topic being described by the adjective. This pattern explains a characteristic feature of a place or thing.',
                    'notes_id' => [
                        '大阪《おおさか》は 食《た》べ物《もの》が おいしいです。（Osaka: makanannya enak）',
                    ],
                    'notes_en' => [
                        'Oosaka wa tabemono ga oishii desu. (Osaka: its food is delicious)',
                    ],
                    'examples' => [
                        ['ja' => '大阪《おおさか》は 食《た》べ物《もの》が おいしいです。', 'reading' => 'Oosaka wa tabemono ga oishii desu.', 'id' => 'Osaka, makanannya enak.', 'en' => 'In Osaka, the food is delicious.'],
                        ['ja' => 'マリアさんは 髪《かみ》が 長《なが》いです。', 'reading' => 'Maria-san wa kami ga nagai desu.', 'id' => 'Sdr. Maria rambutnya panjang.', 'en' => 'Maria\'s hair is long.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Membicarakan sebuah kota',
                        'title_en' => 'Talking about a city',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '奈良《なら》は どんな 町《まち》ですか。', 'reading' => 'Nara wa donna machi desu ka.', 'id' => 'Nara kota yang bagaimana?', 'en' => 'What kind of town is Nara?'],
                            ['speaker' => 'ワヒュ', 'ja' => '奈良《なら》は 静《しず》かで、きれいな 町《まち》です。公園《こうえん》が きれいです。', 'reading' => 'Nara wa shizuka de, kirei na machi desu. Kouen ga kirei desu.', 'id' => 'Nara kota yang tenang dan indah. Tamannya indah.', 'en' => 'Nara is a quiet, beautiful town. Its park is beautiful.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KB(tempat) を KK (titik awal keberangkatan)',
                'title_en' => 'N (place) o V (point of departure)',
                'pattern' => '(場所《ばしょ》)を 出《で》ます／降《お》ります',
                'payload' => [
                    'explanation_id' => 'Kata kerja 出《で》ます (keluar, berangkat) dan 降《お》ります (turun) dipakai bersama partikel を. を di sini menunjukkan titik awal keberangkatan — tempat yang ditinggalkan — bukan objek dalam arti biasa.',
                    'explanation_en' => 'The verbs 出《で》ます (leave, go out) and 降《お》ります (get off) take the particle を. Here を marks the point of departure — the place you leave — not an object in the usual sense.',
                    'notes_id' => [
                        'Bandingkan dengan に yang menunjukkan tempat masuk/naik: 大学《だいがく》に 入《はい》ります (masuk universitas) ⇔ 大学《だいがく》を 出《で》ます (tamat dari universitas); 電車《でんしゃ》に 乗《の》ります (naik) ⇔ 電車《でんしゃ》を 降《お》ります (turun).',
                        'Kata kerja gerak lain yang memakai を untuk tempat yang dilewati dipelajari di Pelajaran 23.',
                    ],
                    'notes_en' => [
                        'Compare with に, which marks the place you enter or board: 大学《だいがく》に 入《はい》ります (enter university) ⇔ 大学《だいがく》を 出《で》ます (graduate); 電車《でんしゃ》に 乗《の》ります (board) ⇔ 電車《でんしゃ》を 降《お》ります (get off).',
                        'Other motion verbs that take を for the place you pass through are covered in Lesson 23.',
                    ],
                    'examples' => [
                        [
                            'ja' => '七時《しちじ》に うちを 出《で》ます。',
                            'reading' => 'Shichiji ni uchi o demasu.',
                            'id' => 'Berangkat dari rumah pukul tujuh.',
                            'en' => 'I leave home at seven.',
                        ],
                        [
                            'ja' => '駅《えき》で 電車《でんしゃ》を 降《お》りました。',
                            'reading' => 'Eki de densha o orimashita.',
                            'id' => 'Turun dari kereta di stasiun.',
                            'en' => 'I got off the train at the station.',
                        ],
                        [
                            'ja' => '去年《きょねん》 大学《だいがく》を 出《で》ました。',
                            'reading' => 'Kyonen daigaku o demashita.',
                            'id' => 'Tahun lalu tamat dari universitas.',
                            'en' => 'I graduated from university last year.',
                        ],
                    ],
                    'dialogue' => [
                        'title_id' => 'Berangkat dan turun',
                        'title_en' => 'Leaving and getting off',
                        'lines' => [
                            [
                                'speaker' => 'たなか',
                                'ja' => '毎朝《まいあさ》 何時《なんじ》に うちを 出《で》ますか。',
                                'reading' => 'Maiasa nanji ni uchi o demasu ka.',
                                'id' => 'Setiap pagi berangkat dari rumah pukul berapa?',
                                'en' => 'What time do you leave home every morning?',
                            ],
                            [
                                'speaker' => 'ワヒュ',
                                'ja' => '七時半《しちじはん》に 出《で》ます。電車《でんしゃ》に 乗《の》って、新大阪《しんおおさか》で 降《お》ります。',
                                'reading' => 'Shichiji han ni demasu. Densha ni notte, Shin-Oosaka de orimasu.',
                                'id' => 'Pukul setengah delapan. Naik kereta, lalu turun di Shin-Osaka.',
                                'en' => 'At 7:30. I take the train and get off at Shin-Osaka.',
                            ],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'どうやって (dengan cara bagaimana)',
                'title_en' => 'dou yatte (how, by what method)',
                'pattern' => 'どうやって〜',
                'payload' => [
                    'explanation_id' => 'どうやって menanyakan CARA atau METODE melakukan sesuatu, biasanya dijawab dengan menyebut sarana transportasi atau langkah-langkahnya, bukan alasan.',
                    'explanation_en' => 'dou yatte asks HOW or by what method something is done, usually answered by naming the means of transport or the steps involved, not a reason.',
                    'notes_id' => [
                        'Berbeda dari どうして (mengapa) yang menanyakan alasan.',
                    ],
                    'notes_en' => [
                        'Different from doushite (why), which asks for a reason.',
                    ],
                    'examples' => [
                        ['ja' => '大学《だいがく》まで どうやって 行《い》きますか。', 'reading' => 'Daigaku made dou yatte ikimasu ka.', 'id' => 'Bagaimana caranya pergi ke universitas?', 'en' => 'How do you get to the university?'],
                        ['ja' => '京都駅《きょうとえき》から バスに 乗《の》って、大学前《だいがくまえ》で 降《お》ります。', 'reading' => 'Kyouto-eki kara basu ni notte, Daigakumae de orimasu.', 'id' => 'Dari stasiun Kyoto naik bus, lalu turun di Daigakumae.', 'en' => 'From Kyoto Station, take the bus and get off at Daigakumae.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menanyakan cara pergi',
                        'title_en' => 'Asking how to get somewhere',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '大学《だいがく》まで どうやって 行《い》きますか。', 'reading' => 'Daigaku made dou yatte ikimasu ka.', 'id' => 'Bagaimana caranya pergi ke universitas?', 'en' => 'How do you get to the university?'],
                            ['speaker' => 'ワヒュ', 'ja' => '駅《えき》で 電車《でんしゃ》に 乗《の》って、次《つぎ》の 駅《えき》で 降《お》ります。', 'reading' => 'Eki de densha ni notte, tsugi no eki de orimasu.', 'id' => 'Naik kereta di stasiun, lalu turun di stasiun berikutnya.', 'en' => 'Get on the train at the station and get off at the next one.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'どれ／どの KB (yang mana, di antara tiga atau lebih)',
                'title_en' => 'dore / dono KB (which one, of three or more)',
                'pattern' => 'どれ／どの〜',
                'payload' => [
                    'explanation_id' => 'どれ berdiri sendiri seperti これ/それ/あれ, sedangkan どの harus diikuti kata benda seperti この/その/あの. Keduanya dipakai untuk memilih di antara TIGA benda atau lebih (kalau hanya dua pilihan, dipakai どちら, lihat Pelajaran 3).',
                    'explanation_en' => 'dore stands alone like kore/sore/are, while dono must be followed by a noun like kono/sono/ano. Both are used to choose among THREE or more things (for a choice between only two, dochira is used — see Lesson 3).',
                    'notes_id' => [
                        'マリアさんの 傘《かさ》は どれですか。／どの 傘《かさ》が マリアさんのですか。',
                    ],
                    'notes_en' => [
                        'Maria-san no kasa wa dore desu ka. / Dono kasa ga Maria-san no desu ka.',
                    ],
                    'examples' => [
                        ['ja' => 'タロさんの 自転車《じてんしゃ》は どれですか。', 'reading' => 'Taro-san no jitensha wa dore desu ka.', 'id' => 'Sepeda Taro yang mana?', 'en' => 'Which one is Taro\'s bicycle?'],
                        ['ja' => '青《あお》くて 新《あたら》しいのです。', 'reading' => 'Aokute atarashii no desu.', 'id' => 'Yang biru dan baru.', 'en' => 'The blue, new one.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menunjukkan barang milik',
                        'title_en' => 'Pointing out one\'s belongings',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'マリアさんの 傘《かさ》は どれですか。', 'reading' => 'Maria-san no kasa wa dore desu ka.', 'id' => 'Payung Sdr. Maria yang mana?', 'en' => 'Which one is Maria\'s umbrella?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'あの 赤《あか》い 傘《かさ》です。', 'reading' => 'Ano akai kasa desu.', 'id' => 'Payung merah itu.', 'en' => 'That red umbrella.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 17 — bentuk-ない
    // ------------------------------------------------------------------

    private function lesson17Cards(): array
    {
        return [
            [
                'title_id' => 'Membentuk bentuk-ない',
                'title_en' => 'Forming the nai-form',
                'pattern' => 'KK(ます形)→KKない',
                'payload' => [
                    'explanation_id' => 'Bentuk-ない adalah bentuk negatif biasa/kasual dari kata kerja, dipakai juga sebagai dasar beberapa ungkapan sopan. Kelompok II: ます→ない (食《た》べます→食《た》べない). Kelompok III: します→しない、来《き》ます→来《こ》ない. Kelompok I: ubah bunyi い di depan ます menjadi bunyi あ, lalu tambah ない (書《か》きます→書《か》かない, 飲《の》みます→飲《の》まない). Pengecualian: 買《か》います→買《か》わない (bukan 買《か》あない).',
                    'explanation_en' => 'The nai-form is the plain/casual negative, also the base for several polite expressions. Group II: masu -> nai (tabemasu -> tabenai). Group III: shimasu -> shinai, kimasu -> konai. Group I: change the i-sound before masu to an a-sound, then add nai (kakimasu -> kakanai, nomimasu -> nomanai). Exception: kaimasu -> kawanai (not kaanai).',
                    'notes_id' => [
                        'あります adalah pengecualian: bentuk-ないnya ない, bukan あらない.',
                        'Bentuk ini akan dipakai untuk 〜ないでください、〜なければなりません、〜なくてもいいです pada kartu berikutnya.',
                    ],
                    'notes_en' => [
                        'arimasu is an exception: its nai-form is simply nai, not aranai.',
                        'This form feeds -nai de kudasai, -nakereba narimasen and -nakute mo ii desu in the next cards.',
                    ],
                    'examples' => [
                        ['ja' => '飲《の》みます → 飲《の》まない', 'reading' => 'nomimasu -> nomanai', 'id' => 'minum → (bentuk-ない)', 'en' => 'to drink -> nai-form'],
                        ['ja' => '食《た》べます → 食《た》べない', 'reading' => 'tabemasu -> tabenai', 'id' => 'makan → (bentuk-ない)', 'en' => 'to eat -> nai-form'],
                        ['ja' => 'します → しない', 'reading' => 'shimasu -> shinai', 'id' => 'melakukan → (bentuk-ない)', 'en' => 'to do -> nai-form'],
                    ],
                    'dialogue' => null,
                ],
            ],

            [
                'title_id' => 'KKないでください',
                'title_en' => 'V-nai de kudasai',
                'pattern' => '〜ないでください',
                'payload' => [
                    'explanation_id' => 'Bentuk-ない + でください berarti "tolong jangan …". Merupakan kebalikan dari 〜てください, dipakai untuk meminta lawan bicara tidak melakukan sesuatu, biasanya demi kesehatan atau keselamatan.',
                    'explanation_en' => 'nai-form + de kudasai means "please do not …". It is the mirror of te kudasai, used to ask the listener not to do something, often for health or safety.',
                    'notes_id' => [
                        'Lebih personal/lembut daripada 〜てはいけません yang terdengar seperti peraturan resmi.',
                        'Sering muncul dalam nasihat dokter: たばこを 吸《す》わないでください.',
                    ],
                    'notes_en' => [
                        'It is more personal and gentler than te wa ikemasen, which sounds like a formal rule.',
                        'Common in a doctor\'s advice: tabako o suwanai de kudasai.',
                    ],
                    'examples' => [
                        ['ja' => 'お酒《さけ》を 飲《の》まないでください。', 'reading' => 'Osake o nomanai de kudasai.', 'id' => 'Tolong jangan minum minuman keras.', 'en' => 'Please do not drink alcohol.'],
                        ['ja' => '心配《しんぱい》しないでください。', 'reading' => 'Shinpai shinai de kudasai.', 'id' => 'Tolong jangan khawatir.', 'en' => 'Please do not worry.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di dokter',
                        'title_en' => "At the doctor's",
                        'lines' => [
                            ['speaker' => '医者《いしゃ》', 'ja' => 'あしたまで お酒《さけ》を 飲《の》まないでください。', 'reading' => 'Ashita made osake o nomanai de kudasai.', 'id' => 'Jangan minum alkohol sampai besok.', 'en' => 'Please do not drink alcohol until tomorrow.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい、わかりました。', 'reading' => 'Hai, wakarimashita.', 'id' => 'Baik, mengerti.', 'en' => 'Yes, understood.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KKなければなりません',
                'title_en' => 'V-nakereba narimasen',
                'pattern' => '〜なければなりません',
                'payload' => [
                    'explanation_id' => 'Bentuk-ない, ganti い terakhir menjadi ければ, lalu tambah なりません, berarti "harus …" — kewajiban. Harfiah artinya "kalau tidak melakukan …, tidak jadi (baik)".',
                    'explanation_en' => 'Take the nai-form, swap the final i for kereba, then add narimasen, meaning "must …" — an obligation. Literally: "if I do not do …, it will not do (be all right)".',
                    'notes_id' => [
                        'Sering dipersingkat dalam percakapan santai menjadi 〜なきゃ.',
                        'Berbeda dengan 〜てください: ini menyatakan keharusan, bukan permintaan kepada lawan bicara.',
                    ],
                    'notes_en' => [
                        'Casual speech often shortens this to -nakya.',
                        'Unlike te kudasai, this states an obligation, not a request made to the listener.',
                    ],
                    'examples' => [
                        ['ja' => 'あした 早《はや》く 起《お》きなければなりません。', 'reading' => 'Ashita hayaku okinakereba narimasen.', 'id' => 'Besok saya harus bangun cepat.', 'en' => 'I have to wake up early tomorrow.'],
                        ['ja' => '薬《くすり》を 飲《の》まなければなりません。', 'reading' => 'Kusuri o nomanakereba narimasen.', 'id' => 'Harus minum obat.', 'en' => 'I have to take the medicine.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Kewajiban besok',
                        'title_en' => "Tomorrow's obligation",
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'あした 病院《びょういん》へ 行《い》かなければなりません。', 'reading' => 'Ashita byouin e ikanakereba narimasen.', 'id' => 'Besok saya harus ke rumah sakit.', 'en' => 'I have to go to the hospital tomorrow.'],
                            ['speaker' => 'ワヒュ', 'ja' => '大丈夫《だいじょうぶ》ですか。', 'reading' => 'Daijoubu desu ka.', 'id' => 'Baik-baik saja?', 'en' => 'Are you all right?'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KKなくてもいいです',
                'title_en' => 'V-nakute mo ii desu',
                'pattern' => '〜なくてもいいです',
                'payload' => [
                    'explanation_id' => 'Bentuk-ない, ganti い terakhir menjadi くて, lalu も いいです, berarti "tidak perlu …" atau "boleh tidak …". Menyatakan sesuatu tidak wajib, berbeda dengan 〜てはいけません yang melarang.',
                    'explanation_en' => 'Take the nai-form, swap the final i for kute, then mo ii desu, meaning "you do not have to …" or "it is fine not to …". It states that something is not required, unlike te wa ikemasen, which forbids it.',
                    'notes_id' => [
                        'Bandingkan tiga bentuk: 〜てください (tolong lakukan), 〜てはいけません (dilarang), 〜なくてもいいです (tidak wajib).',
                        'Sering dipakai menjawab pertanyaan なければなりませんか.',
                    ],
                    'notes_en' => [
                        'Compare the three forms: te kudasai (please do), te wa ikemasen (forbidden), nakute mo ii desu (not required).',
                        'Often used to answer a … nakereba narimasen ka question.',
                    ],
                    'examples' => [
                        ['ja' => 'あした 来《こ》なくてもいいです。', 'reading' => 'Ashita konakute mo ii desu.', 'id' => 'Besok tidak perlu datang.', 'en' => 'You do not have to come tomorrow.'],
                        ['ja' => '薬《くすり》を 毎日《まいにち》 飲《の》まなくてもいいですか。', 'reading' => 'Kusuri o mainichi nomanakute mo ii desu ka.', 'id' => 'Apakah obatnya tidak perlu diminum setiap hari?', 'en' => 'Do I not need to take the medicine every day?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Boleh tidak datang',
                        'title_en' => 'Whether you must come',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'あした 病院《びょういん》へ 行《い》かなければなりませんか。', 'reading' => 'Ashita byouin e ikanakereba narimasen ka.', 'id' => 'Besok harus ke rumah sakit?', 'en' => 'Do I have to go to the hospital tomorrow?'],
                            ['speaker' => '医者《いしゃ》', 'ja' => 'いいえ、来《こ》なくてもいいです。', 'reading' => 'Iie, konakute mo ii desu.', 'id' => 'Tidak, tidak perlu datang.', 'en' => 'No, you do not have to come.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'Pentopikan objek',
                'title_en' => 'Topicalizing the object',
                'pattern' => 'KBを → KBは',
                'payload' => [
                    'explanation_id' => 'Jika objek langsung (biasanya bertanda を) ingin dijadikan topik kalimat — misalnya untuk menekankan atau membandingkan — partikel を dihilangkan lalu diganti は, dan bagian itu dipindah ke paling depan kalimat.',
                    'explanation_en' => 'When the direct object (normally marked with を) is made the topic of the sentence — for emphasis or contrast — を is dropped and replaced with は, and that part moves to the front of the sentence.',
                    'notes_id' => [
                        '荷物《にもつ》は ここに 置《お》かないでください。（awalnya：ここに 荷物《にもつ》を 置《お》かないでください）',
                        '昼《ひる》ごはんは 会社《かいしゃ》の 食堂《しょくどう》で 食《た》べます。',
                    ],
                    'notes_en' => [
                        'Nimotsu wa koko ni okanai de kudasai. (originally: koko ni nimotsu o okanai de kudasai)',
                        'Hirugohan wa kaisha no shokudou de tabemasu.',
                    ],
                    'examples' => [
                        ['ja' => '荷物《にもつ》は ここに 置《お》かないでください。', 'reading' => 'Nimotsu wa koko ni okanai de kudasai.', 'id' => 'Barangnya jangan diletakkan di sini.', 'en' => 'Please do not put the luggage here.'],
                        ['ja' => '昼《ひる》ごはんは 会社《かいしゃ》の 食堂《しょくどう》で 食《た》べます。', 'reading' => 'Hirugohan wa kaisha no shokudou de tabemasu.', 'id' => 'Makan siangnya, saya makan di kantin perusahaan.', 'en' => 'As for lunch, I eat it at the company cafeteria.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menaruh barang',
                        'title_en' => 'Where to put luggage',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'この 荷物《にもつ》は どこに 置《お》きますか。', 'reading' => 'Kono nimotsu wa doko ni okimasu ka.', 'id' => 'Barang ini diletakkan di mana?', 'en' => 'Where should this luggage go?'],
                            ['speaker' => 'ワヒュ', 'ja' => '荷物《にもつ》は そこに 置《お》かないでください。あぶないですから。', 'reading' => 'Nimotsu wa soko ni okanai de kudasai. Abunai desu kara.', 'id' => 'Barangnya jangan diletakkan di situ. Karena berbahaya.', 'en' => 'Please don\'t put the luggage there. It\'s dangerous.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KB(waktu)までに KK',
                'title_en' => 'KB(time) made ni KK (by a deadline)',
                'pattern' => '〜までに〜',
                'payload' => [
                    'explanation_id' => 'までに menunjukkan batas waktu suatu aksi/peristiwa harus SUDAH terjadi — "paling lambat pada …". Berbeda dari まで (Pelajaran 4) yang menunjukkan titik akhir dari aksi yang berlangsung terus-menerus.',
                    'explanation_en' => 'made ni marks the deadline by which an action/event must already have happened — "by …, at the latest". This differs from まで (Lesson 4), which marks the end point of a continuously ongoing action.',
                    'notes_id' => [
                        '5時《じ》までに 終《お》わります（selesai sebelum/paling lambat jam 5） vs 5時《じ》まで 働《はたら》きます（bekerja terus sampai jam 5）.',
                    ],
                    'notes_en' => [
                        'Goji made ni owarimasu (finishes by 5 at the latest) vs goji made hatarakimasu (keeps working until 5).',
                    ],
                    'examples' => [
                        ['ja' => '会議《かいぎ》は 5時《ごじ》までに 終《お》わります。', 'reading' => 'Kaigi wa goji made ni owarimasu.', 'id' => 'Rapat selesai sebelum jam 5.', 'en' => 'The meeting will be over by 5 o\'clock.'],
                        ['ja' => '土曜日《どようび》までに 本《ほん》を 返《かえ》さなければ なりません。', 'reading' => 'Doyoubi made ni hon o kaesanakereba narimasen.', 'id' => 'Harus mengembalikan buku paling lambat hari Sabtu.', 'en' => 'I must return the book by Saturday.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Batas waktu mengembalikan buku',
                        'title_en' => 'A book return deadline',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'いつまでに 本《ほん》を 返《かえ》さなければ なりませんか。', 'reading' => 'Itsu made ni hon o kaesanakereba narimasen ka.', 'id' => 'Buku ini harus dikembalikan paling lambat kapan?', 'en' => 'By when do I have to return this book?'],
                            ['speaker' => 'たなか', 'ja' => '土曜日《どようび》までに 返《かえ》してください。', 'reading' => 'Doyoubi made ni kaeshite kudasai.', 'id' => 'Tolong kembalikan paling lambat hari Sabtu.', 'en' => 'Please return it by Saturday.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 18 — bentuk kamus
    // ------------------------------------------------------------------

    private function lesson18Cards(): array
    {
        return [
            [
                'title_id' => 'Membentuk bentuk kamus (jishokei)',
                'title_en' => 'Forming the dictionary form',
                'pattern' => 'KK(ます形)→KK(辞書形)',
                'payload' => [
                    'explanation_id' => 'Bentuk kamus adalah bentuk kata kerja yang dipakai di kamus, juga dasar berbagai ungkapan. Kelompok II: ます→る (食《た》べます→食《た》べる). Kelompok III: します→する、来《き》ます→来《く》る. Kelompok I: bunyi い di depan ます diganti bunyi う (書《か》きます→書《か》く、飲《の》みます→飲《の》む、買《か》います→買《か》う).',
                    'explanation_en' => 'The dictionary form is the form used in dictionaries, and the base for several expressions. Group II: masu -> ru (tabemasu -> taberu). Group III: shimasu -> suru, kimasu -> kuru. Group I: the i-sound before masu becomes a u-sound (kakimasu -> kaku, nomimasu -> nomu, kaimasu -> kau).',
                    'notes_id' => [
                        'Bentuk kamus, bentuk-て dan bentuk-ない semuanya diturunkan dari kelompok kata kerja yang sama — begitu satu dikuasai, yang lain lebih mudah diingat.',
                        'あります tidak punya bentuk kamus yang dipakai wajar sebagai predikat kalimat biasa dalam pola KKことができます.',
                    ],
                    'notes_en' => [
                        'The dictionary form, te-form and nai-form are all derived from the same verb group — mastering one makes the others easier to recall.',
                        'arimasu is rarely used in its dictionary form as an ordinary predicate within the koto ga dekimasu pattern.',
                    ],
                    'examples' => [
                        ['ja' => '飲《の》みます → 飲《の》む', 'reading' => 'nomimasu -> nomu', 'id' => 'minum → (bentuk kamus)', 'en' => 'to drink -> dictionary form'],
                        ['ja' => '見《み》ます → 見《み》る', 'reading' => 'mimasu -> miru', 'id' => 'melihat → (bentuk kamus)', 'en' => 'to see -> dictionary form'],
                        ['ja' => '来《き》ます → 来《く》る', 'reading' => 'kimasu -> kuru', 'id' => 'datang → (bentuk kamus)', 'en' => 'to come -> dictionary form'],
                    ],
                    'dialogue' => null,
                ],
            ],

            [
                'title_id' => 'KK(辞書形)ことができます',
                'title_en' => 'V(dict.) koto ga dekimasu',
                'pattern' => '〜ことができます',
                'payload' => [
                    'explanation_id' => 'こと mengubah kata kerja menjadi kata benda ("hal melakukan …"), lalu ができます (bisa) menyatakan kemampuan atau sesuatu yang diizinkan terjadi. Karena こと mengubahnya jadi kata benda, sasarannya boleh diikuti が.',
                    'explanation_en' => 'koto turns a verb into a noun ("the act of doing …"), and ga dekimasu (can) states an ability or something that is possible. Because koto makes it a noun, the target may take が.',
                    'notes_id' => [
                        'できます sendiri (tanpa こと) juga langsung dipakai untuk kata benda kemampuan: 日本語《にほんご》が できます.',
                        'Pertanyaan: 〜ことができますか.',
                    ],
                    'notes_en' => [
                        'dekimasu alone (without koto) is also used directly with an ability noun: nihongo ga dekimasu.',
                        'Question: … koto ga dekimasu ka.',
                    ],
                    'examples' => [
                        ['ja' => 'サリさんは 日本語《にほんご》が できます。', 'reading' => 'Sari-san wa nihongo ga dekimasu.', 'id' => 'Sari bisa berbahasa Jepang.', 'en' => 'Sari can speak Japanese.'],
                        ['ja' => 'わたしは 漢字《かんじ》を 読《よ》むことが できます。', 'reading' => 'Watashi wa kanji o yomu koto ga dekimasu.', 'id' => 'Saya bisa membaca kanji.', 'en' => 'I can read kanji.'],
                        ['ja' => 'ピアノを 弾《ひ》くことが できますか。', 'reading' => 'Piano o hiku koto ga dekimasu ka.', 'id' => 'Bisa main piano?', 'en' => 'Can you play the piano?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Bertanya kemampuan',
                        'title_en' => 'Asking about a skill',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'ワヒュさんは ピアノを 弾《ひ》くことが できますか。', 'reading' => 'Wahyu-san wa piano o hiku koto ga dekimasu ka.', 'id' => 'Wahyu bisa main piano?', 'en' => 'Wahyu, can you play the piano?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、できません。でも ギターは できます。', 'reading' => 'Iie, dekimasen. Demo gitaa wa dekimasu.', 'id' => 'Tidak bisa. Tapi gitar bisa.', 'en' => 'No, I cannot. But I can play the guitar.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => '趣味《しゅみ》は KK(辞書形)ことです',
                'title_en' => 'shumi wa V(dict.) koto desu',
                'pattern' => '趣味は 〜ことです',
                'payload' => [
                    'explanation_id' => 'Pola yang sama (こと mengubah kata kerja menjadi kata benda) dipakai untuk menyebutkan hobi: 趣味《しゅみ》は + bentuk kamus + ことです. こと di sini berfungsi sebagai predikat kata benda dengan です biasa, bukan lagi bersama できます.',
                    'explanation_en' => 'The same koto pattern (turning a verb into a noun) is used to state a hobby: shumi wa + dictionary form + koto desu. Here koto simply serves as the noun predicate with plain desu, not paired with dekimasu.',
                    'notes_id' => [
                        'Bisa juga langsung pakai kata benda hobi tanpa こと: 趣味《しゅみ》は 音楽《おんがく》です.',
                        'Pertanyaan: 趣味《しゅみ》は 何《なん》ですか.',
                    ],
                    'notes_en' => [
                        'A plain hobby noun also works without koto: shumi wa ongaku desu.',
                        'Question: shumi wa nan desu ka.',
                    ],
                    'examples' => [
                        ['ja' => 'わたしの 趣味《しゅみ》は 写真《しゃしん》を 撮《と》ることです。', 'reading' => 'Watashi no shumi wa shashin o toru koto desu.', 'id' => 'Hobi saya adalah memotret.', 'en' => 'My hobby is taking photographs.'],
                        ['ja' => '趣味《しゅみ》は 音楽《おんがく》です。', 'reading' => 'Shumi wa ongaku desu.', 'id' => 'Hobi saya musik.', 'en' => 'My hobby is music.'],
                        ['ja' => '趣味《しゅみ》は 本《ほん》を 読《よ》むことです。', 'reading' => 'Shumi wa hon o yomu koto desu.', 'id' => 'Hobi saya membaca buku.', 'en' => 'My hobby is reading books.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Berkenalan soal hobi',
                        'title_en' => 'Getting to know hobbies',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'ワヒュさんの 趣味《しゅみ》は 何《なん》ですか。', 'reading' => 'Wahyu-san no shumi wa nan desu ka.', 'id' => 'Hobi Wahyu apa?', 'en' => 'What is your hobby, Wahyu?'],
                            ['speaker' => 'ワヒュ', 'ja' => '趣味《しゅみ》は 写真《しゃしん》を 撮《と》ることです。', 'reading' => 'Shumi wa shashin o toru koto desu.', 'id' => 'Hobi saya memotret.', 'en' => 'My hobby is taking photographs.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KK(辞書形)／KBの／期間＋まえに、〜',
                'title_en' => 'V(dict.) / KB no / duration + mae ni, …',
                'pattern' => '〜まえに、〜',
                'payload' => [
                    'explanation_id' => 'まえに berarti "sebelum" dan bisa didahului tiga jenis kata: (1) kata kerja bentuk kamus, tidak peduli tenses kalimat utamanya; (2) kata benda + の (KBの まえに); (3) kata keterangan bilangan/jangka waktu langsung + まえに (misal 3年《ねん》まえに = 3 tahun yang lalu).',
                    'explanation_en' => 'mae ni means "before" and can be preceded by three kinds of words: (1) a verb in the dictionary form, regardless of the main clause\'s tense; (2) a noun + の (KB no mae ni); (3) a duration/quantity word directly + まえに (e.g. 3-nen mae ni = 3 years ago).',
                    'notes_id' => [
                        '寝《ね》るまえに、歯《は》を磨《みが》きます。（kata kerja bentuk kamus）',
                        '食事《しょくじ》の まえに、手《て》を洗《あら》います。（kata benda + の）',
                        '3年《ねん》まえに、日本《にほん》へ 来《き》ました。（jangka waktu langsung）',
                        'Bentuk kamus di sini TIDAK berarti kalimatnya berwaktu sekarang/masa depan — hanya aturan tata bahasa まえに.',
                    ],
                    'notes_en' => [
                        'Neru mae ni, ha o migakimasu. (dictionary-form verb)',
                        'Shokuji no mae ni, te o araimasu. (noun + no)',
                        'San-nen mae ni, Nihon e kimashita. (duration directly)',
                        'The dictionary form here does NOT mean the sentence is present/future tense — it is simply the grammar rule for mae ni.',
                    ],
                    'examples' => [
                        ['ja' => '寝《ね》るまえに、歯《は》を 磨《みが》きます。', 'reading' => 'Neru mae ni, ha o migakimasu.', 'id' => 'Sebelum tidur, saya menggosok gigi.', 'en' => 'Before sleeping, I brush my teeth.'],
                        ['ja' => '食事《しょくじ》の まえに、手《て》を 洗《あら》ってください。', 'reading' => 'Shokuji no mae ni, te o aratte kudasai.', 'id' => 'Sebelum makan, tolong cuci tangan.', 'en' => 'Please wash your hands before the meal.'],
                        ['ja' => '3年《ねん》まえに、日本《にほん》へ 来《き》ました。', 'reading' => 'San-nen mae ni, Nihon e kimashita.', 'id' => 'Saya datang ke Jepang 3 tahun yang lalu.', 'en' => 'I came to Japan 3 years ago.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Kebiasaan sebelum tidur',
                        'title_en' => 'A habit before bed',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '寝《ね》るまえに 何《なに》を しますか。', 'reading' => 'Neru mae ni nani o shimasu ka.', 'id' => 'Sebelum tidur ngapain?', 'en' => 'What do you do before sleeping?'],
                            ['speaker' => 'ワヒュ', 'ja' => '本《ほん》を 読《よ》みます。', 'reading' => 'Hon o yomimasu.', 'id' => 'Membaca buku.', 'en' => 'I read a book.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'なかなか (tidak semudah itu …)',
                'title_en' => 'nakanaka (not easily …)',
                'pattern' => 'なかなか〜ません',
                'payload' => [
                    'explanation_id' => 'なかなか diikuti bentuk negatif menyatakan sesuatu tidak terjadi/tercapai semudah yang diharapkan, walau sudah diusahakan. Berbeda dari sekadar ません biasa, なかなか menyiratkan usaha yang belum membuahkan hasil.',
                    'explanation_en' => 'nakanaka followed by a negative form states that something does not happen/succeed as easily as hoped, despite effort. Unlike a plain negative, nakanaka implies effort that has not yet paid off.',
                    'notes_id' => [
                        'Tanpa bentuk negatif, なかなか juga bisa berarti "cukup, lumayan" (pujian): なかなか おもしろいです.',
                    ],
                    'notes_en' => [
                        'Without a negative, nakanaka can also mean "quite, considerably" (praise): nakanaka omoshiroi desu.',
                    ],
                    'examples' => [
                        ['ja' => '漢字《かんじ》は なかなか 覚《おぼ》えられません。', 'reading' => 'Kanji wa nakanaka oboeraremasen.', 'id' => 'Kanji sulit dihafal (tidak mudah-mudah).', 'en' => 'Kanji is not easy to memorize.'],
                        ['ja' => 'なかなか じょうずですね。', 'reading' => 'Nakanaka jouzu desu ne.', 'id' => 'Lumayan pandai, ya.', 'en' => 'You\'re quite good at it.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Belajar piano',
                        'title_en' => 'Learning the piano',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'ピアノは じょうずに なりましたか。', 'reading' => 'Piano wa jouzu ni narimashita ka.', 'id' => 'Piano sudah pandai?', 'en' => 'Have you gotten good at piano?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、なかなか じょうずに なりません。', 'reading' => 'Iie, nakanaka jouzu ni narimasen.', 'id' => 'Belum, tidak mudah-mudah pandai.', 'en' => 'No, I\'m not getting good at it easily.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'ぜひ (dengan segala cara, pasti)',
                'title_en' => 'zehi (by all means, definitely)',
                'pattern' => 'ぜひ〜',
                'payload' => [
                    'explanation_id' => 'ぜひ menekankan keinginan atau ajakan yang sungguh-sungguh, sering dipakai bersama 〜たいです atau 〜てください/〜ましょう untuk mengungkapkan harapan kuat.',
                    'explanation_en' => 'zehi emphasizes a sincere wish or invitation, often paired with -tai desu or -te kudasai/-mashou to express a strong hope.',
                    'notes_id' => [
                        'ぜひ 来《き》てください＝tolong datang, ya (dengan sungguh-sungguh mengharapkan).',
                    ],
                    'notes_en' => [
                        'zehi kite kudasai = please do come (a sincere invitation).',
                    ],
                    'examples' => [
                        ['ja' => 'ぜひ 遊《あそ》びに 来《き》てください。', 'reading' => 'Zehi asobi ni kite kudasai.', 'id' => 'Silakan main ke rumah, ya (sungguh mengharapkan).', 'en' => 'Please do come visit sometime.'],
                        ['ja' => 'いつか ぜひ 富士山《ふじさん》に 登《のぼ》りたいです。', 'reading' => 'Itsuka zehi Fujisan ni noboritai desu.', 'id' => 'Suatu hari saya benar-benar ingin mendaki Gunung Fuji.', 'en' => 'Someday I really want to climb Mt. Fuji.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mengundang teman',
                        'title_en' => 'Inviting a friend',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => '今度《こんど》 うちへ 遊《あそ》びに 来《き》ませんか。', 'reading' => 'Kondo uchi e asobi ni kimasen ka.', 'id' => 'Lain kali main ke rumah, yuk?', 'en' => 'Would you like to come visit my place sometime?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい、ぜひ。', 'reading' => 'Hai, zehi.', 'id' => 'Ya, dengan senang hati.', 'en' => 'Yes, I\'d love to.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 19 — bentuk-た
    // ------------------------------------------------------------------

    private function lesson19Cards(): array
    {
        return [
            [
                'title_id' => 'Membentuk bentuk-た',
                'title_en' => 'Forming the -ta form',
                'pattern' => 'KK(て形《けい》)→KK(た形《けい》)',
                'payload' => [
                    'explanation_id' => 'Bentuk-た adalah bentuk lampau biasa (kata kerja yang berakhir dengan た atau だ). Cara membuatnya mudah kalau sudah menguasai bentuk-て: ganti て dengan た, dan で dengan だ. Aturan bunyinya sama persis dengan bentuk-て untuk Kelompok I, II, dan III.',
                    'explanation_en' => 'The -ta form is the plain past (a verb ending in た or だ). It is easy once you know the -te form: replace て with た and で with だ. The sound rules are exactly the same as for the -te form for Groups I, II and III.',
                    'notes_id' => [
                        'Bentuk-た dipakai pada pola-pola di pelajaran ini: 〜たことがあります (pengalaman) dan 〜たり、〜たりします (contoh kegiatan).',
                        'Bentuk-た juga menjadi bentuk lampau positif dalam bentuk biasa (dipelajari di Pelajaran 20).',
                    ],
                    'notes_en' => [
                        'The -ta form is used in this lesson\'s patterns: -ta koto ga arimasu (experience) and -tari, -tari shimasu (example activities).',
                        'It is also the plain affirmative past (covered in Lesson 20).',
                    ],
                    'examples' => [
                        [
                            'ja' => '書《か》いて → 書《か》いた',
                            'reading' => 'kaite -> kaita',
                            'id' => 'menulis → (telah) menulis',
                            'en' => 'write -> wrote',
                        ],
                        [
                            'ja' => '飲《の》んで → 飲《の》んだ',
                            'reading' => 'nonde -> nonda',
                            'id' => 'minum → (telah) minum',
                            'en' => 'drink -> drank',
                        ],
                        [
                            'ja' => '泳《およ》いで → 泳《およ》いだ',
                            'reading' => 'oyoide -> oyoida',
                            'id' => 'berenang → (telah) berenang',
                            'en' => 'swim -> swam',
                        ],
                        [
                            'ja' => '食《た》べて → 食《た》べた',
                            'reading' => 'tabete -> tabeta',
                            'id' => 'makan → (telah) makan',
                            'en' => 'eat -> ate',
                        ],
                        [
                            'ja' => '来《き》て → 来《き》た',
                            'reading' => 'kite -> kita',
                            'id' => 'datang → (telah) datang',
                            'en' => 'come -> came',
                        ],
                        [
                            'ja' => '勉強《べんきょう》して → 勉強《べんきょう》した',
                            'reading' => 'benkyou shite -> benkyou shita',
                            'id' => 'belajar → (telah) belajar',
                            'en' => 'study -> studied',
                        ],
                    ],
                    'dialogue' => null,
                ],
            ],

            [
                'title_id' => 'KK(た形)ことが あります',
                'title_en' => 'V-ta koto ga arimasu',
                'pattern' => '〜たことがあります',
                'payload' => [
                    'explanation_id' => 'Bentuk-た sama dengan bentuk lampau biasa (て→た dengan aturan yang sama). たことがあります memakai bentuk-た + ことが あります untuk menyatakan pengalaman: "pernah …". Menekankan bahwa hal itu pernah terjadi setidaknya sekali, tanpa menyebut kapan persisnya.',
                    'explanation_en' => 'The -ta form matches the plain past (te -> ta with the same sound rules). -ta koto ga arimasu uses the -ta form + koto ga arimasu to state an experience: "have done … before". It stresses that it has happened at least once, without saying exactly when.',
                    'notes_id' => [
                        'Berbeda dengan 〜ました yang menyebut kejadian tertentu di masa lalu; たことがあります fokus pada pengalaman seumur hidup.',
                        'Negatif: 一度《いちど》も 〜たことがありません (belum pernah sama sekali).',
                    ],
                    'notes_en' => [
                        'Unlike -mashita, which names a specific past occasion, -ta koto ga arimasu focuses on lifetime experience.',
                        'Negative: ichido mo … ta koto ga arimasen (have never once …).',
                    ],
                    'examples' => [
                        ['ja' => '富士山《ふじさん》に 登《のぼ》ったことが あります。', 'reading' => 'Fujisan ni nobotta koto ga arimasu.', 'id' => 'Saya pernah mendaki Gunung Fuji.', 'en' => 'I have climbed Mt. Fuji before.'],
                        ['ja' => '刺身《さしみ》を 食《た》べたことが ありますか。', 'reading' => 'Sashimi o tabeta koto ga arimasu ka.', 'id' => 'Pernah makan sashimi?', 'en' => 'Have you ever eaten sashimi?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Membicarakan pengalaman',
                        'title_en' => 'Talking about experience',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '日本《にほん》へ 行《い》ったことが ありますか。', 'reading' => 'Nihon e itta koto ga arimasu ka.', 'id' => 'Pernah ke Jepang?', 'en' => 'Have you ever been to Japan?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、一度《いちど》も 行《い》ったことが ありません。', 'reading' => 'Iie, ichido mo itta koto ga arimasen.', 'id' => 'Belum pernah sama sekali.', 'en' => 'No, never.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KK(た形)り、KK(た形)り します',
                'title_en' => 'V-ta ri, V-ta ri shimasu',
                'pattern' => '〜たり、〜たりします',
                'payload' => [
                    'explanation_id' => 'たり dibentuk dari bentuk-た + り, dan dipakai berpasangan untuk menyebutkan beberapa contoh kegiatan (biasanya dua) yang dilakukan tanpa urutan tetap, ditutup します/しました. Berbeda dengan rangkaian 〜て、〜て yang urutannya pasti.',
                    'explanation_en' => '-tari is built from the -ta form + ri, and used in pairs to list example activities (usually two) done without a fixed order, closed with shimasu/shimashita. This differs from the te, te sequence, whose order is fixed.',
                    'notes_id' => [
                        'Menyiratkan makna "kegiatan semacam itu, di antaranya …" — bukan daftar lengkap.',
                        'Tidak dipakai untuk hal yang dilakukan setiap hari secara rutin (bangun, makan, tidur malam); pola ini menyebut beberapa contoh kegiatan yang mewakili.',
                        'Bisa dipakai untuk kegiatan yang berlawanan: 電気《でんき》を つけたり 消《け》したり します.',
                    ],
                    'notes_en' => [
                        'It implies "activities like these, among others" — not a complete list.',
                        'It is not used for things done every single day as routine (waking up, eating, going to bed); the pattern names a few representative activities.',
                        'It can pair opposite actions: denki o tsuketari keshitari shimasu.',
                    ],
                    'examples' => [
                        ['ja' => '週末《しゅうまつ》は 本《ほん》を 読《よ》んだり、映画《えいが》を 見《み》たりします。', 'reading' => 'Shuumatsu wa hon o yondari, eiga o mitari shimasu.', 'id' => 'Akhir pekan saya membaca buku, menonton film, dan sebagainya.', 'en' => 'On weekends I read, watch movies, and so on.'],
                        ['ja' => '休《やす》みの日《ひ》は 泳《およ》いだり、歌《うた》を 歌《うた》ったりします。', 'reading' => 'Yasumi no hi wa oyoidari, uta o utattari shimasu.', 'id' => 'Hari libur saya berenang, bernyanyi, dan sebagainya.', 'en' => 'On days off I swim, sing, and so on.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Kegiatan akhir pekan',
                        'title_en' => 'Weekend activities',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => '週末《しゅうまつ》は いつも 何《なに》を しますか。', 'reading' => 'Shuumatsu wa itsumo nani o shimasu ka.', 'id' => 'Akhir pekan biasanya ngapain?', 'en' => 'What do you usually do on weekends?'],
                            ['speaker' => 'ワヒュ', 'ja' => '本《ほん》を 読《よ》んだり、料理《りょうり》を 作《つく》ったりします。', 'reading' => 'Hon o yondari, ryouri o tsukuttari shimasu.', 'id' => 'Membaca buku, memasak, dan lain-lain.', 'en' => 'Reading, cooking, that sort of thing.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'Kata Sifat〜く／〜に なります (menjadi …)',
                'title_en' => 'Adjective -ku/-ni narimasu (become …)',
                'pattern' => '〜く／〜に なります',
                'payload' => [
                    'explanation_id' => 'なります (menjadi) dipakai untuk menyatakan perubahan keadaan. Kata sifat-い: 〜い→〜く なります (寒《さむ》い→寒《さむ》くなります). Kata sifat-な dan kata benda: tambah に なります (にぎやか→にぎやかに なります; 25歳《さい》→25歳《さい》に なります).',
                    'explanation_en' => 'なります (become) states a change of state. i-adjectives: -i becomes -ku narimasu (samui -> samuku narimasu). na-adjectives and nouns: add ni narimasu (nigiyaka -> nigiyaka ni narimasu; 25-sai -> 25-sai ni narimasu).',
                    'notes_id' => [
                        'だんだん 寒《さむ》く なりました。（berangsur-angsur menjadi dingin）',
                        'もうすぐ 春《はる》に なります。（sebentar lagi menjadi musim semi）',
                    ],
                    'notes_en' => [
                        'Dandan samuku narimashita. (it gradually became cold)',
                        'Mou sugu haru ni narimasu. (spring is coming soon)',
                    ],
                    'examples' => [
                        ['ja' => 'だんだん 寒《さむ》く なりました。', 'reading' => 'Dandan samuku narimashita.', 'id' => 'Berangsur-angsur menjadi dingin.', 'en' => 'It has gradually gotten cold.'],
                        ['ja' => '来月《らいげつ》 25歳《さい》に なります。', 'reading' => 'Raigetsu nijuugo-sai ni narimasu.', 'id' => 'Bulan depan (saya) akan berusia 25 tahun.', 'en' => 'Next month I\'ll turn 25.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Membicarakan cuaca',
                        'title_en' => 'Talking about the weather',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '最近《さいきん》 寒《さむ》く なりましたね。', 'reading' => 'Saikin samuku narimashita ne.', 'id' => 'Akhir-akhir ini jadi dingin, ya.', 'en' => 'It\'s gotten cold recently, hasn\'t it.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'そうですね。もうすぐ 冬《ふゆ》に なりますね。', 'reading' => 'Sou desu ne. Mou sugu fuyu ni narimasu ne.', 'id' => 'Betul. Sebentar lagi jadi musim dingin, ya.', 'en' => 'That\'s right. Winter will be here soon.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 20 — bentuk biasa
    // ------------------------------------------------------------------

    private function lesson20Cards(): array
    {
        return [

            [
                'title_id' => 'Bentuk halus dan bentuk biasa (futsuukei)',
                'title_en' => 'The plain form (futsuukei)',
                'pattern' => '丁寧形→普通形',
                'payload' => [
                    'explanation_id' => 'Sampai di sini semua kalimat memakai bentuk sopan (です・ます). Bentuk biasa dipakai pada percakapan santai dengan teman/keluarga, dan menjadi dasar berbagai pola tata bahasa berikutnya. Kata kerja: bentuk kamus (positif sekarang), bentuk-ない (negatif sekarang), bentuk-た (positif lampau), bentuk-ない +かった (negatif lampau: 食《た》べなかった). Kata benda/な-sifat: だ menggantikan です (学生《がくせい》だ), じゃない menggantikan じゃありません. い-sifat: cukup buang です (高《たか》い, bukan 高《たか》いだ).',
                    'explanation_en' => 'Up to now every sentence has used the polite form (desu/masu). The plain form is used in casual talk with friends and family, and is the base for several later grammar patterns. Verbs: dictionary form (present affirmative), nai-form (present negative), -ta form (past affirmative), nai-form + katta (past negative: tabenakatta). Nouns/na-adjectives: da replaces desu (gakusei da), ja nai replaces ja arimasen. i-adjectives: simply drop desu (takai, not takai da).',
                    'notes_id' => [
                        'だ sering dihilangkan sama sekali dalam percakapan biasa, terutama pada pertanyaan.',
                        'Bentuk biasa BUKAN berarti kasar — dipakai wajar antar teman sebaya; tetap dipakai です・ます kepada orang yang belum akrab atau lebih tua.',
                    ],
                    'notes_en' => [
                        'da is often dropped entirely in ordinary conversation, especially in questions.',
                        'The plain form is not rude — it is normal among peers; desu/masu is still used with people you are not close to or who are older.',
                    ],
                    'examples' => [
                        ['ja' => '行《い》きます → 行《い》く／行《い》かない／行《い》った／行《い》かなかった', 'reading' => 'ikimasu -> iku / ikanai / itta / ikanakatta', 'id' => 'pergi (bentuk biasa: sekarang, negatif, lampau, lampau negatif)', 'en' => 'to go (plain: present, negative, past, past negative)'],
                        ['ja' => '学生《がくせい》です → 学生《がくせい》だ', 'reading' => 'gakusei desu -> gakusei da', 'id' => '(saya) mahasiswa (bentuk biasa)', 'en' => '(I) am a student (plain form)'],
                        ['ja' => '毎朝《まいあさ》 六時《ろくじ》に 起《お》きます → 起《お》きる', 'reading' => 'Maiasa rokuji ni okimasu -> okiru', 'id' => 'Setiap pagi bangun pukul enam (bentuk biasa: kata kerja)', 'en' => 'I wake up at six every morning (plain: verb)'],
                        ['ja' => 'あした 忙《いそが》しいです → 忙《いそが》しい', 'reading' => 'Ashita isogashii desu -> isogashii', 'id' => 'Besok sibuk (bentuk biasa: sifat-い, cukup buang です)', 'en' => 'Busy tomorrow (plain: i-adjective, just drop です)'],
                        ['ja' => '音楽《おんがく》が 好《す》きです → 好《す》きだ', 'reading' => 'Ongaku ga suki desu -> suki da', 'id' => 'Suka musik (bentuk biasa: sifat-な, です → だ)', 'en' => 'I like music (plain: na-adjective, です -> だ)'],
                        ['ja' => '京都《きょうと》へ 行《い》きたいです → 行《い》きたい', 'reading' => 'Kyouto e ikitai desu -> ikitai', 'id' => 'Ingin pergi ke Kyoto (bentuk biasa: 〜たいです → 〜たい)', 'en' => 'I want to go to Kyoto (plain: -tai desu -> -tai)'],
                        ['ja' => '一度《いちど》も 乗《の》ったことが ありません → 乗《の》ったことが ない', 'reading' => 'Ichido mo notta koto ga arimasen -> notta koto ga nai', 'id' => 'Belum pernah naik sama sekali (bentuk biasa: ありません → ない)', 'en' => 'I have never ridden it (plain: arimasen -> nai)'],
                    ],
                    'dialogue' => null,
                ],
            ],

            [
                'title_id' => 'Kapan memakai Bentuk Halus, kapan Bentuk Biasa',
                'title_en' => 'When to use the polite form, when to use the plain form',
                'pattern' => '丁寧形 ⇔ 普通形',
                'payload' => [
                    'explanation_id' => 'Bentuk halus（です・ます）dipakai kepada orang yang belum akrab, orang yang lebih tua/berkedudukan lebih tinggi, dan dalam situasi resmi atau tulisan. Bentuk biasa dipakai kepada teman dekat, keluarga, atau orang yang lebih muda/setara akrab — bukan karena kasar, tetapi karena menunjukkan kedekatan. Kedua bentuk TIDAK boleh dicampur dalam satu kalimat/lawan bicara yang sama.',
                    'explanation_en' => 'The polite form (desu/masu) is used with people you are not close to, people older or of higher status, and in formal situations or writing. The plain form is used with close friends, family, or people younger/on equally close terms — not because it is rude, but because it signals closeness. The two forms must not be mixed within one sentence or toward the same listener.',
                    'notes_id' => [
                        'Dalam tulisan: surat umumnya memakai bentuk halus, sedangkan makalah, laporan, dan catatan harian memakai bentuk biasa.',
                        'Orang Jepang bisa berpindah dari bentuk biasa ke bentuk halus di tengah percakapan jika topiknya berubah jadi lebih resmi, atau sebaliknya.',
                        'Terhadap orang yang baru dikenal atau atasan, tetap pakai bentuk halus meskipun mereka memakai bentuk biasa kepada kita.',
                    ],
                    'notes_en' => [
                        'In writing: letters are usually in the polite form, while essays, reports and diaries use the plain form.',
                        'Japanese speakers can shift from plain to polite mid-conversation if the topic turns more formal, or the other way around.',
                        'With someone you have just met or a superior, keep using the polite form even if they use the plain form toward you.',
                    ],
                    'examples' => [
                        ['ja' => '（先生《せんせい》に）今日《きょう》は お忙《いそが》しいですか。', 'reading' => '(Sensei ni) Kyou wa oisogashii desu ka.', 'id' => '(Kepada guru) Hari ini sibuk?', 'en' => '(To a teacher) Are you busy today?'],
                        ['ja' => '（友達《ともだち》に）今日《きょう》 忙《いそが》しい？', 'reading' => '(Tomodachi ni) Kyou isogashii?', 'id' => '(Kepada teman) Hari ini sibuk?', 'en' => '(To a friend) Busy today?'],
                    ],
                    'dialogue' => null,
                ],
            ],

            [
                'title_id' => 'Percakapan santai: pertanyaan tanpa か',
                'title_en' => 'Casual talk in the plain form',
                'pattern' => '〜？ うん／ううん、〜',
                'payload' => [
                    'explanation_id' => 'Dalam percakapan santai, pertanyaan sering tidak memakai か sama sekali — cukup nada suara naik di akhir kalimat bentuk biasa. Jawaban はい/いいえ juga diganti うん (ya) dan ううん (tidak) dalam suasana akrab.',
                    'explanation_en' => 'In casual speech, a question often drops か entirely — a rising tone on the plain-form sentence is enough. hai/iie also become un (yeah) and uun (nah) among close friends.',
                    'notes_id' => [
                        'Partikel akhir よ dan ね tetap dipakai sama seperti bentuk sopan.',
                        'Pilih bentuk biasa/sopan sesuai lawan bicara — jangan campur dalam satu kalimat yang sama.',
                    ],
                    'notes_en' => [
                        'The sentence-final particles yo and ne are used just as with the polite form.',
                        'Pick plain or polite to match the listener — do not mix them within a single sentence.',
                    ],
                    'examples' => [
                        ['ja' => 'あした 忙《いそが》しい？', 'reading' => 'Ashita isogashii?', 'id' => 'Besok sibuk?', 'en' => 'Busy tomorrow?'],
                        ['ja' => 'ううん、暇《ひま》だよ。', 'reading' => 'Uun, hima da yo.', 'id' => 'Enggak, senggang, kok.', 'en' => 'Nah, I am free.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mengobrol dengan teman dekat',
                        'title_en' => 'Chatting with a close friend',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'ねえ、あした 暇《ひま》？', 'reading' => 'Nee, ashita hima?', 'id' => 'Eh, besok senggang?', 'en' => 'Hey, are you free tomorrow?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'うん、暇《ひま》だよ。どうして？', 'reading' => 'Un, hima da yo. Doushite?', 'id' => 'Iya, senggang. Kenapa?', 'en' => 'Yeah, I am free. Why?'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'Pertanyaan tanpa だ (kata benda／sifat-な)',
                'title_en' => 'Questions without だ (nouns / na-adjectives)',
                'pattern' => '〜？ うん、〜(だ)よ／ううん、〜じゃない',
                'payload' => [
                    'explanation_id' => 'Pada kalimat tanya bentuk biasa dengan kata benda atau kata sifat-な, だ (bentuk biasa dari です) dihilangkan: 今晩《こんばん》 暇《ひま》？ Untuk jawaban positif, memakai だ saja terdengar kasar dan keras, sehingga だ dihilangkan atau ditambah partikel penutup untuk memperhalus nada: 暇《ひま》／暇《ひま》だよ (biasanya laki-laki), 暇《ひま》よ (biasanya perempuan). Jawaban negatif: ううん、暇《ひま》じゃない。',
                    'explanation_en' => 'In plain-form questions with a noun or na-adjective, だ (the plain form of です) is dropped: 今晩《こんばん》 暇《ひま》？ For a positive answer, a bare だ sounds blunt and harsh, so だ is dropped or a softening final particle is added: 暇《ひま》／暇《ひま》だよ (usually men), 暇《ひま》よ (usually women). Negative: ううん、暇《ひま》じゃない。',
                    'notes_id' => [
                        'Pertanyaan ditandai nada suara naik (↗), bukan か.',
                        'Penutup よ／だよ／よ terutama membedakan gaya bicara laki-laki dan perempuan.',
                    ],
                    'notes_en' => [
                        'Questions are marked by rising intonation (↗), not か.',
                        'The endings よ／だよ／よ mainly distinguish male and female speech styles.',
                    ],
                    'examples' => [
                        [
                            'ja' => '今晩《こんばん》 暇《ひま》？－うん、暇《ひま》／暇《ひま》だよ。',
                            'reading' => 'Konban hima? Un, hima / hima da yo.',
                            'id' => 'Nanti malam luang? — Ya, luang.',
                            'en' => 'Free tonight? — Yeah, I\'m free.',
                        ],
                        [
                            'ja' => 'あした 休《やす》み？－ううん、休《やす》みじゃない。',
                            'reading' => 'Ashita yasumi? Uun, yasumi ja nai.',
                            'id' => 'Besok libur? — Tidak, tidak libur.',
                            'en' => 'Off tomorrow? — No, I\'m not.',
                        ],
                        [
                            'ja' => 'それ、あなたの 傘《かさ》？－ううん、リナさんの。',
                            'reading' => 'Sore, anata no kasa? Uun, Rina-san no.',
                            'id' => 'Itu payung kamu? — Bukan, punya Rina.',
                            'en' => 'Is that your umbrella? — No, it\'s Rina\'s.',
                        ],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mengajak teman',
                        'title_en' => 'Inviting a friend',
                        'lines' => [
                            [
                                'speaker' => 'リナ',
                                'ja' => '日曜日《にちようび》、暇《ひま》？',
                                'reading' => 'Nichiyoubi, hima?',
                                'id' => 'Hari Minggu senggang?',
                                'en' => 'Free on Sunday?',
                            ],
                            [
                                'speaker' => 'ワヒュ',
                                'ja' => 'ううん、暇《ひま》じゃない。友達《ともだち》と 出《で》かける。',
                                'reading' => 'Uun, hima ja nai. Tomodachi to dekakeru.',
                                'id' => 'Tidak, tidak senggang. Mau pergi dengan teman.',
                                'en' => 'No, I\'m not. I\'m going out with a friend.',
                            ],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'Partikel sering dihilangkan',
                'title_en' => 'Dropping particles',
                'pattern' => 'ごはん[を] 食《た》べる？',
                'payload' => [
                    'explanation_id' => 'Dalam percakapan bentuk biasa, bila hubungan antarbagian kalimat sudah jelas dari konteks sebelum dan sesudahnya, partikel kadang dihilangkan (を, は, が, へ). Akan tetapi partikel で, に, から, まで, と, dan sebagainya tidak dihilangkan, sebab konteksnya menjadi tidak jelas.',
                    'explanation_en' => 'In plain-form conversation, when the relationships are clear from the context before and after, particles are sometimes dropped (を, は, が, へ). However, particles such as で, に, から, まで, と are not dropped, because the meaning would become unclear.',
                    'notes_id' => [
                        'Boleh dihilangkan: を, は, が, へ — ごはん[を] 食《た》べる？／この ワイン[は] おいしいね。',
                        'Tidak boleh dihilangkan: で, に, から, まで, と — 友達《ともだち》と 行《い》く。',
                    ],
                    'notes_en' => [
                        'May be dropped: を, は, が, へ.',
                        'Must stay: で, に, から, まで, と.',
                    ],
                    'examples' => [
                        [
                            'ja' => 'ごはん[を] 食《た》べる？',
                            'reading' => 'Gohan [o] taberu?',
                            'id' => 'Mau makan?',
                            'en' => 'Want to eat?',
                        ],
                        [
                            'ja' => 'あした 大阪《おおさか》[へ] 行《い》かない？',
                            'reading' => 'Ashita Oosaka [e] ikanai?',
                            'id' => 'Bagaimana kalau besok ke Osaka?',
                            'en' => 'How about going to Osaka tomorrow?',
                        ],
                        [
                            'ja' => 'この ワイン[は] おいしいね。',
                            'reading' => 'Kono wain [wa] oishii ne.',
                            'id' => 'Anggur ini enak ya.',
                            'en' => 'This wine is good, isn\'t it.',
                        ],
                        [
                            'ja' => 'そこに 傘《かさ》[が] ある？',
                            'reading' => 'Soko ni kasa [ga] aru?',
                            'id' => 'Di situ ada payung?',
                            'en' => 'Is there an umbrella there?',
                        ],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menawarkan minuman',
                        'title_en' => 'Offering a drink',
                        'lines' => [
                            [
                                'speaker' => 'リナ',
                                'ja' => 'コーヒー[を] 飲《の》む？',
                                'reading' => 'Koohii [o] nomu?',
                                'id' => 'Mau minum kopi?',
                                'en' => 'Want some coffee?',
                            ],
                            [
                                'speaker' => 'ワヒュ',
                                'ja' => 'うん、飲《の》む。',
                                'reading' => 'Un, nomu.',
                                'id' => 'Ya, mau minum.',
                                'en' => 'Yeah, I\'ll have some.',
                            ],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'ている → [い]る／[い]ない',
                'title_en' => 'te iru -> te [i]ru / te [i]nai',
                'pattern' => '〜て[い]る',
                'payload' => [
                    'explanation_id' => 'Dalam bentuk biasa, い dari bentuk ている sering dihilangkan dalam percakapan, sehingga terdengar てる／てない. Contoh: 持《も》って[い]る？－うん、持《も》って[い]る。／ううん、持《も》って[い]ない。',
                    'explanation_en' => 'In the plain form, the い of ている is often dropped in conversation, so it sounds like てる／てない. Example: 持《も》って[い]る？－うん、持《も》って[い]る。／ううん、持《も》って[い]ない。',
                    'notes_id' => [
                        'Hanya い yang dihilangkan; て tetap ada.',
                    ],
                    'notes_en' => [
                        'Only い is dropped; て stays.',
                    ],
                    'examples' => [
                        [
                            'ja' => '辞書《じしょ》、持《も》って[い]る？－うん、持《も》って[い]る。',
                            'reading' => 'Jisho, motte [i]ru? Un, motte [i]ru.',
                            'id' => 'Punya kamus? — Ya, punya.',
                            'en' => 'Do you have a dictionary? — Yes, I do.',
                        ],
                        [
                            'ja' => '辞書《じしょ》、持《も》って[い]る？－ううん、持《も》って[い]ない。',
                            'reading' => 'Jisho, motte [i]ru? Uun, motte [i]nai.',
                            'id' => 'Punya kamus? — Tidak, tidak punya.',
                            'en' => 'Do you have a dictionary? — No, I don\'t.',
                        ],
                        [
                            'ja' => 'いま 何《なに》 して[い]る？－テレビ 見《み》て[い]る。',
                            'reading' => 'Ima nani shite [i]ru? Terebi mite [i]ru.',
                            'id' => 'Sekarang sedang apa? — Sedang menonton televisi.',
                            'en' => 'What are you doing now? — Watching TV.',
                        ],
                    ],
                    'dialogue' => [
                        'title_id' => 'Bertanya tentang seseorang',
                        'title_en' => 'Asking about someone',
                        'lines' => [
                            [
                                'speaker' => 'サリ',
                                'ja' => 'ワヒュ、あの 人《ひと》、知《し》って[い]る？',
                                'reading' => 'Wahyu, ano hito, shitte [i]ru?',
                                'id' => 'Wahyu, kamu kenal orang itu?',
                                'en' => 'Wahyu, do you know that person?',
                            ],
                            [
                                'speaker' => 'ワヒュ',
                                'ja' => 'ううん、知《し》らない。',
                                'reading' => 'Uun, shiranai.',
                                'id' => 'Tidak, tidak kenal.',
                                'en' => 'No, I don\'t.',
                            ],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => '〜けど',
                'title_en' => '〜kedo',
                'pattern' => '〜けど、〜',
                'payload' => [
                    'explanation_id' => 'けど mempunyai fungsi yang sama dengan が ("tetapi"), dan sering dipakai dalam percakapan. けど menyambung dua kalimat yang isinya berlawanan; juga sering dipakai untuk melembutkan ajakan atau permintaan, misalnya 〜けど、いっしょに 行《い》かない？',
                    'explanation_en' => 'けど has the same function as が ("but") and is common in conversation. It joins two contrasting clauses; it is also often used to soften an invitation or request, e.g. 〜けど、いっしょに 行《い》かない？',
                    'notes_id' => [
                        'Dalam bentuk sopan dipakai が; dalam percakapan santai, けど.',
                    ],
                    'notes_en' => [
                        'In polite speech use が; in casual conversation, けど.',
                    ],
                    'examples' => [
                        [
                            'ja' => 'そのカレー[は] おいしい？－うん、辛《から》いけど、おいしい。',
                            'reading' => 'Sono karee [wa] oishii? Un, karai kedo, oishii.',
                            'id' => 'Kare itu enak? — Ya, pedas tapi enak.',
                            'en' => 'Is that curry good? — Yeah, it\'s spicy, but good.',
                        ],
                        [
                            'ja' => '映画《えいが》の チケット[が] あるけど、いっしょに 行《い》かない？－いいね。',
                            'reading' => 'Eiga no chiketto [ga] aru kedo, issho ni ikanai? Ii ne.',
                            'id' => 'Ada tiket film, mau pergi bersama? — Bagus ya.',
                            'en' => 'I have movie tickets — want to go together? — Sounds good.',
                        ],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mengajak makan',
                        'title_en' => 'Inviting someone to eat',
                        'lines' => [
                            [
                                'speaker' => 'リナ',
                                'ja' => 'あした 忙《いそが》しいけど、夜《よる》は 暇《ひま》だよ。',
                                'reading' => 'Ashita isogashii kedo, yoru wa hima da yo.',
                                'id' => 'Besok sibuk, tapi malamnya senggang.',
                                'en' => 'I\'m busy tomorrow, but free in the evening.',
                            ],
                            [
                                'speaker' => 'ワヒュ',
                                'ja' => 'じゃ、いっしょに ごはん[を] 食《た》べない？',
                                'reading' => 'Ja, issho ni gohan [o] tabenai?',
                                'id' => 'Kalau begitu, bagaimana kalau makan bersama?',
                                'en' => 'Then how about eating together?',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 21 — と思います／と言います／でしょう／で(acara・adegan)／でも／ないと
    // ------------------------------------------------------------------

    private function lesson21Cards(): array
    {
        return [
            [
                'title_id' => '普通形《ふつうけい》＋と 思《おも》います',
                'title_en' => 'Plain form + to omoimasu',
                'pattern' => '普通形《ふつうけい》＋と 思《おも》います',
                'payload' => [
                    'explanation_id' => 'と 思《おも》います dipakai untuk (1) menyatakan dugaan dan (2) menyampaikan pendapat. Bagian sebelum と berbentuk biasa (kata kerja／kata sifat apa adanya; kata benda dan kata sifat-な memakai だ). Untuk menanyakan pendapat orang lain dipakai 〜について どう 思《おも》いますか — setelah どう TIDAK dipasang と. Untuk setuju dengan pendapat orang lain: わたしも そう 思《おも》います。',
                    'explanation_en' => 'to omoimasu is used to (1) state a guess and (2) give an opinion. What comes before to is in plain form (verbs/adjectives as they are; nouns and na-adjectives take da). To ask someone\'s opinion use 〜について どう 思《おも》いますか — NO to after dou. To agree with someone: watashi mo sou omoimasu.',
                    'notes_id' => [
                        'Dugaan negatif: isi pikiran dinegatifkan di dalam kalimat: 知《し》らないと 思《おも》います (bukan 思《おも》いません).',
                        'Isi pikiran tidak terpengaruh waktu kalimat utama: 降《ふ》ると 思《おも》いました tetap "akan turun".',
                    ],
                    'notes_en' => [
                        'A negative guess puts the negative inside the clause: shiranai to omoimasu (not omoimasen).',
                        'The content is not affected by the tense of the main verb: furu to omoimashita still means "it will rain".',
                    ],
                    'examples' => [
                        ['ja' => 'あした 雨《あめ》が 降《ふ》ると 思《おも》います。', 'reading' => 'Ashita ame ga furu to omoimasu.', 'id' => 'Saya kira besok hujan turun.', 'en' => 'I think it will rain tomorrow.'],
                        ['ja' => 'ミラーさんは この ニュースを 知《し》って いますか。― いいえ、知《し》らないと 思《おも》います。', 'reading' => 'Miraa-san wa kono nyuusu o shitte imasu ka. Iie, shiranai to omoimasu.', 'id' => 'Pak Miller tahu berita ini? ― Tidak, saya kira dia tidak tahu.', 'en' => 'Does Mr. Miller know this news? ― No, I do not think he does.'],
                        ['ja' => '日本《にほん》は 物価《ぶっか》が 高《たか》いと 思《おも》います。', 'reading' => 'Nihon wa bukka ga takai to omoimasu.', 'id' => 'Saya pikir harga barang di Jepang mahal.', 'en' => 'I think prices in Japan are high.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menanyakan pendapat',
                        'title_en' => 'Asking for an opinion',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'この 町《まち》について どう 思《おも》いますか。', 'reading' => 'Kono machi ni tsuite dou omoimasu ka.', 'id' => 'Bagaimana pendapatmu tentang kota ini?', 'en' => 'What do you think about this town?'],
                            ['speaker' => 'リナ', 'ja' => 'きれいですが、ちょっと 交通《こうつう》が 不便《ふべん》だと 思《おも》います。', 'reading' => 'Kirei desu ga, chotto koutsuu ga fuben da to omoimasu.', 'id' => 'Bersih, tapi menurut saya lalu lintasnya kurang praktis.', 'en' => 'It is clean, but I think transport is a bit inconvenient.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'わたしも そう 思《おも》います。', 'reading' => 'Watashi mo sou omoimasu.', 'id' => 'Saya juga berpikir begitu.', 'en' => 'I think so too.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => '「文《ぶん》」と 言《い》います',
                'title_en' => '"Sentence" to iimasu',
                'pattern' => '「文《ぶん》」と 言《い》います ／ 普通形《ふつうけい》＋と 言《い》います',
                'payload' => [
                    'explanation_id' => 'と 言《い》います menyatakan isi ucapan, dengan dua cara. (1) Kutipan langsung: ucapan ditaruh di dalam 「 」 persis seperti diucapkan, lalu と 言《い》います. (2) Kutipan tidak langsung: isinya diringkas dalam bentuk biasa lalu と 言《い》います. Isi kutipan tidak terpengaruh waktu kalimat utama. Lawan bicara yang mendengar ditandai に.',
                    'explanation_en' => 'to iimasu reports what was said, in two ways. (1) Direct quotation: the words go inside 「 」 exactly as spoken, followed by to iimasu. (2) Indirect quotation: the content is summarised in plain form followed by to iimasu. The quoted part is not affected by the tense of the main verb. The listener is marked with ni.',
                    'notes_id' => [
                        'Sebelum tidur mengucapkan salam: 寝《ね》る まえに、「おやすみなさい」と 言《い》います。',
                        'Pola lain: だれかに 〜と 言《い》います (mengatakan kepada seseorang).',
                    ],
                    'notes_en' => [
                        'Saying good night before bed: neru mae ni, 「oyasuminasai」 to iimasu.',
                        'Also: dareka ni 〜 to iimasu (tell someone that …).',
                    ],
                    'examples' => [
                        ['ja' => '寝《ね》る まえに、「おやすみなさい」と 言《い》います。', 'reading' => 'Neru mae ni, "oyasuminasai" to iimasu.', 'id' => 'Sebelum tidur saya berkata "selamat tidur".', 'en' => 'Before going to bed I say "good night".'],
                        ['ja' => 'ミラーさんは 「来週《らいしゅう》 東京《とうきょう》へ 行《い》きます」と 言《い》いました。', 'reading' => 'Miraa-san wa "raishuu Toukyou e ikimasu" to iimashita.', 'id' => 'Pak Miller berkata, "Minggu depan saya pergi ke Tokyo".', 'en' => 'Mr. Miller said, "I am going to Tokyo next week".'],
                        ['ja' => 'ミラーさんは 来週《らいしゅう》 東京《とうきょう》へ 行《い》くと 言《い》いました。', 'reading' => 'Miraa-san wa raishuu Toukyou e iku to iimashita.', 'id' => 'Pak Miller mengatakan bahwa minggu depan dia pergi ke Tokyo.', 'en' => 'Mr. Miller said that he is going to Tokyo next week.'],
                        ['ja' => '父《ちち》に 留学《りゅうがく》したいと 言《い》いました。', 'reading' => 'Chichi ni ryuugaku shitai to iimashita.', 'id' => 'Saya bilang kepada ayah bahwa ingin belajar di luar negeri.', 'en' => 'I told my father that I want to study abroad.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menyampaikan ucapan',
                        'title_en' => 'Passing on what was said',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '部長《ぶちょう》は 何《なん》と 言《い》いましたか。', 'reading' => 'Buchou wa nan to iimashita ka.', 'id' => 'Apa kata kepala bagian tadi?', 'en' => 'What did the manager say?'],
                            ['speaker' => 'リナ', 'ja' => '明日《あした》 会議《かいぎ》が あると 言《い》いました。', 'reading' => 'Ashita kaigi ga aru to iimashita.', 'id' => 'Katanya besok ada rapat.', 'en' => 'He said there is a meeting tomorrow.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => '普通形《ふつうけい》＋でしょう？',
                'title_en' => 'Plain form + deshou?',
                'pattern' => '普通形《ふつうけい》＋でしょう？',
                'payload' => [
                    'explanation_id' => 'でしょう？ (dengan nada naik) dipakai untuk meminta persetujuan atau menegaskan sesuatu kepada lawan bicara: "…, bukan?". Di depan でしょう dipakai bentuk biasa; namun untuk kata sifat-な dan kata benda, だ dihilangkan (静《しず》かでしょう？, 先生《せんせい》でしょう？). Jawaban yang menyetujui: はい／ええ; yang menyangkal: いいえ.',
                    'explanation_en' => 'deshou? (rising tone) seeks the listener\'s agreement or confirmation: "…, right?". Plain form comes before deshou, but for na-adjectives and nouns the da is dropped (shizuka deshou?, sensei deshou?). Agree with hai / ee; disagree with iie.',
                    'notes_id' => [
                        'Berbeda dengan 〜ですか yang bertanya tanpa berharap jawaban tertentu: でしょう？ sudah mengharapkan "ya".',
                    ],
                    'notes_en' => [
                        'Unlike 〜desu ka, which asks without expecting a particular answer, deshou? already expects "yes".',
                    ],
                    'examples' => [
                        ['ja' => 'あした パーティーに 行《い》くでしょう？― ええ、行《い》きます。', 'reading' => 'Ashita paatii ni iku deshou? Ee, ikimasu.', 'id' => 'Besok pergi ke pesta, kan? ― Ya, pergi.', 'en' => 'You are going to the party tomorrow, right? ― Yes, I am.'],
                        ['ja' => '北海道《ほっかいどう》は 寒《さむ》かったでしょう？― いいえ、そんなに 寒《さむ》くなかったです。', 'reading' => 'Hokkaidou wa samukatta deshou? Iie, sonna ni samukunakatta desu.', 'id' => 'Hokkaido dingin, kan? ― Tidak, tidak begitu dingin.', 'en' => 'Hokkaido was cold, right? ― No, it was not that cold.'],
                        ['ja' => 'この 部屋《へや》は 静《しず》かでしょう？', 'reading' => 'Kono heya wa shizuka deshou?', 'id' => 'Kamar ini tenang, kan?', 'en' => 'This room is quiet, right?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Memastikan',
                        'title_en' => 'Checking',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => '田中《たなか》さんは 先生《せんせい》でしょう？', 'reading' => 'Tanaka-san wa sensei deshou?', 'id' => 'Pak Tanaka itu guru, kan?', 'en' => 'Mr. Tanaka is a teacher, right?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、会社員《かいしゃいん》です。', 'reading' => 'Iie, kaishain desu.', 'id' => 'Bukan, dia karyawan perusahaan.', 'en' => 'No, he is a company employee.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'N1(場所《ばしょ》)で N2が あります',
                'title_en' => 'N1 (place) de N2 ga arimasu',
                'pattern' => 'N1(場所《ばしょ》)で N2が あります',
                'payload' => [
                    'explanation_id' => 'Jika N2 adalah acara atau kejadian (pesta, konser, festival, pertandingan, bencana, dsb.), あります berarti "diadakan／terjadi" dan tempatnya ditandai で (bukan に). Bandingkan: N1に N2が あります = N2 (benda) ada di N1 (Pelajaran 10).',
                    'explanation_en' => 'When N2 is an event (party, concert, festival, match, disaster, etc.), arimasu means "is held / takes place" and the place is marked with de (not ni). Compare N1 ni N2 ga arimasu = N2 (a thing) exists at N1 (Lesson 10).',
                    'notes_id' => [
                        'Salah: 東京《とうきょう》に 試合《しあい》が あります (untuk acara). Benar: 東京《とうきょう》で 試合《しあい》が あります。',
                    ],
                    'notes_en' => [
                        'Wrong: Toukyou ni shiai ga arimasu (for an event). Correct: Toukyou de shiai ga arimasu.',
                    ],
                    'examples' => [
                        ['ja' => '東京《とうきょう》で 日本《にほん》と ブラジルの サッカーの 試合《しあい》が あります。', 'reading' => 'Toukyou de Nihon to Burajiru no sakkaa no shiai ga arimasu.', 'id' => 'Di Tokyo ada pertandingan sepak bola Jepang lawan Brasil.', 'en' => 'A soccer match between Japan and Brazil is held in Tokyo.'],
                        ['ja' => '大阪《おおさか》で 天神祭《てんじんまつり》が あります。', 'reading' => 'Oosaka de Tenjinmatsuri ga arimasu.', 'id' => 'Di Osaka ada Perayaan Tenjin.', 'en' => 'The Tenjin Festival takes place in Osaka.'],
                        ['ja' => '会社《かいしゃ》で パーティーが あります。', 'reading' => 'Kaisha de paatii ga arimasu.', 'id' => 'Di kantor ada pesta.', 'en' => 'There is a party at the company.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Acara minggu depan',
                        'title_en' => 'Events next week',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '来週《らいしゅう》 どこで お祭《まつ》りが ありますか。', 'reading' => 'Raishuu doko de omatsuri ga arimasu ka.', 'id' => 'Minggu depan di mana ada festival?', 'en' => 'Where is the festival next week?'],
                            ['speaker' => 'リナ', 'ja' => '大阪《おおさか》で あります。', 'reading' => 'Oosaka de arimasu.', 'id' => 'Di Osaka.', 'en' => 'In Osaka.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'N(場面《ばめん》)で',
                'title_en' => 'N (scene) de',
                'pattern' => 'N(場面《ばめん》)で ＋ V',
                'payload' => [
                    'explanation_id' => 'Partikel で juga menunjukkan situasi atau kesempatan berlangsungnya suatu perbuatan, misalnya 会議《かいぎ》で (dalam rapat), パーティーで (di pesta), 授業《じゅぎょう》で (dalam pelajaran). Selain tempat fisik, で dipakai untuk "adegan" tempat aksi terjadi.',
                    'explanation_en' => 'The particle de also marks the situation or occasion in which an action happens, e.g. kaigi de (in a meeting), paatii de (at a party), jugyou de (in class). Besides physical places, de marks the "scene" of the action.',
                    'notes_id' => [
                        'Menanyakan: 会議《かいぎ》で 何《なに》を 言《い》いましたか。',
                    ],
                    'notes_en' => [
                        'Asking: kaigi de nani o iimashita ka.',
                    ],
                    'examples' => [
                        ['ja' => '会議《かいぎ》で 意見《いけん》を 言《い》いましたか。', 'reading' => 'Kaigi de iken o iimashita ka.', 'id' => 'Apakah Anda menyampaikan pendapat dalam rapat?', 'en' => 'Did you give your opinion in the meeting?'],
                        ['ja' => 'パーティーで 歌《うた》を 歌《うた》いました。', 'reading' => 'Paatii de uta o utaimashita.', 'id' => 'Di pesta saya menyanyi.', 'en' => 'I sang a song at the party.'],
                        ['ja' => '授業《じゅぎょう》で 日本語《にほんご》を 話《はな》します。', 'reading' => 'Jugyou de nihongo o hanashimasu.', 'id' => 'Dalam pelajaran kami berbicara bahasa Jepang.', 'en' => 'We speak Japanese in class.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Dalam rapat',
                        'title_en' => 'In the meeting',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '会議《かいぎ》で 意見《いけん》を 言《い》いましたか。', 'reading' => 'Kaigi de iken o iimashita ka.', 'id' => 'Tadi di rapat, Anda menyampaikan pendapat?', 'en' => 'Did you speak up in the meeting?'],
                            ['speaker' => 'リナ', 'ja' => 'はい、言《い》いました。', 'reading' => 'Hai, iimashita.', 'id' => 'Ya, saya sudah menyampaikan.', 'en' => 'Yes, I did.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'N でも V',
                'title_en' => 'N demo V',
                'pattern' => 'N でも V',
                'payload' => [
                    'explanation_id' => 'N でも 〜 dipakai ketika menawarkan, mengajak, atau menyatakan keinginan dengan menyebut SATU contoh dari beberapa kemungkinan. でも menggantikan を／が; partikel lain (に, へ, dll.) tetap ada di depan でも.',
                    'explanation_en' => 'N demo 〜 is used when offering, inviting, or expressing a wish by naming ONE example among several possibilities. demo replaces o/ga; other particles (ni, e, etc.) stay in front of demo.',
                    'notes_id' => [
                        'Nadanya lembut karena tidak memastikan pilihan: ちょっと ビールでも 飲《の》みませんか。',
                    ],
                    'notes_en' => [
                        'It sounds gentle because it does not commit to one choice: chotto biiru demo nomimasen ka.',
                    ],
                    'examples' => [
                        ['ja' => 'ちょっと ビールでも 飲《の》みませんか。', 'reading' => 'Chotto biiru demo nomimasen ka.', 'id' => 'Bagaimana kalau kita minum bir, atau semacamnya?', 'en' => 'How about a beer or something?'],
                        ['ja' => '日曜日《にちようび》に 映画《えいが》でも 見《み》ませんか。', 'reading' => 'Nichiyoubi ni eiga demo mimasen ka.', 'id' => 'Hari Minggu mau nonton film atau sesuatu?', 'en' => 'Shall we watch a movie or something on Sunday?'],
                        ['ja' => '京都《きょうと》へでも 行《い》きませんか。', 'reading' => 'Kyouto e demo ikimasen ka.', 'id' => 'Bagaimana kalau pergi ke Kyoto?', 'en' => 'How about going to Kyoto or somewhere?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mengajak minum',
                        'title_en' => 'Inviting for a drink',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'ワヒュさん、久《ひさ》しぶりですね。', 'reading' => 'Wahyu-san, hisashiburi desu ne.', 'id' => 'Wahyu, lama tidak bertemu ya.', 'en' => 'Wahyu, it has been a while.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ。ちょっと コーヒーでも 飲《の》みませんか。', 'reading' => 'Ee. Chotto koohii demo nomimasen ka.', 'id' => 'Iya. Mau minum kopi sebentar?', 'en' => 'Yes. Want to have some coffee?'],
                            ['speaker' => 'リナ', 'ja' => 'いいですね。', 'reading' => 'Ii desu ne.', 'id' => 'Boleh.', 'en' => 'Sounds good.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'V(ない形《けい》)ないと…',
                'title_en' => 'V-nai to…',
                'pattern' => 'V(ない形《けい》)ないと（いけません）',
                'payload' => [
                    'explanation_id' => 'V ないと いけません adalah bentuk singkat (percakapan) dari V なければ なりません (Pelajaran 17), artinya "harus …". Dalam percakapan, bagian いけません sering dihilangkan sehingga kalimat berhenti di ないと…., misalnya untuk mohon diri dengan halus: もう 帰《かえ》らないと……。',
                    'explanation_en' => 'V nai to ikemasen is the short, spoken form of V nakereba narimasen (Lesson 17), meaning "must …". In conversation the ikemasen part is often dropped, leaving V nai to…, e.g. to excuse yourself politely: mou kaeranai to……',
                    'notes_id' => [
                        'Kalimat yang menggantung ini terdengar lebih halus daripada menyebut alasan panjang.',
                    ],
                    'notes_en' => [
                        'The trailing-off sentence sounds softer than giving a long reason.',
                    ],
                    'examples' => [
                        ['ja' => 'もう 帰《かえ》らないと……。', 'reading' => 'Mou kaeranai to……', 'id' => 'Saya harus pulang…', 'en' => 'I have to go home…'],
                        ['ja' => 'あしたは 早《はや》く 起《お》きないと いけません。', 'reading' => 'Ashita wa hayaku okinai to ikemasen.', 'id' => 'Besok saya harus bangun pagi.', 'en' => 'I must get up early tomorrow.'],
                        ['ja' => 'レポートを 出《だ》さないと……。', 'reading' => 'Repooto o dasanai to……', 'id' => 'Saya harus mengumpulkan laporan…', 'en' => 'I have to hand in my report…'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mohon diri',
                        'title_en' => 'Excusing oneself',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'もう 遅《おそ》いですね。', 'reading' => 'Mou osoi desu ne.', 'id' => 'Sudah malam ya.', 'en' => 'It is late already.'],
                            ['speaker' => 'リナ', 'ja' => 'そうですね。もう 帰《かえ》らないと……。', 'reading' => 'Sou desu ne. Mou kaeranai to……', 'id' => 'Iya. Saya harus pulang…', 'en' => 'Yes. I have to go home…'],
                            ['speaker' => 'ワヒュ', 'ja' => 'そうですか。じゃ、また あした。', 'reading' => 'Sou desu ka. Ja, mata ashita.', 'id' => 'Oh begitu. Sampai besok.', 'en' => 'I see. See you tomorrow.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 22 — anak kalimat (penerang kata benda)／時間・約束・用事／ましょうか
    // ------------------------------------------------------------------

    private function lesson22Cards(): array
    {
        return [
            [
                'title_id' => 'Anak kalimat (bentuk biasa ＋ KB)',
                'title_en' => 'Noun-modifying clause (plain form + noun)',
                'pattern' => '普通形《ふつうけい》＋N',
                'payload' => [
                    'explanation_id' => 'Kata atau kalimat yang menerangkan kata benda diletakkan DI DEPAN kata benda itu. Pada pelajaran ini penerangnya berupa anak kalimat: kata kerja, kata sifat, atau kata benda dalam BENTUK BIASA. Anak kalimat kata kerja bisa positif, negatif, dan lampau: 京都《きょうと》へ 行《い》く 人《ひと》 (orang yang pergi ke Kyoto), 行《い》かない 人《ひと》, 行《い》った 人《ひと》, 行《い》かなかった 人《ひと》.',
                    'explanation_en' => 'A word or clause describing a noun goes BEFORE that noun. In this lesson the describer is a clause whose verb, adjective or noun is in PLAIN FORM. A verb clause can be positive, negative or past: Kyouto e iku hito (the person who goes to Kyoto), ikanai hito, itta hito, ikanakatta hito.',
                    'notes_id' => [
                        'Anak kalimat bisa berisi keterangan sendiri (tempat, waktu, objek) seperti kalimat biasa.',
                        'Kata benda inti (人《ひと》, うち, ケーキ, dsb.) selalu paling belakang.',
                    ],
                    'notes_en' => [
                        'The clause can carry its own place, time and object like an ordinary sentence.',
                        'The head noun (hito, uchi, keeki, etc.) always comes last.',
                    ],
                    'examples' => [
                        ['ja' => '京都《きょうと》へ 行《い》く 人《ひと》', 'reading' => 'Kyouto e iku hito', 'id' => 'orang yang pergi ke Kyoto', 'en' => 'the person who is going to Kyoto'],
                        ['ja' => '京都《きょうと》へ 行《い》かなかった 人《ひと》', 'reading' => 'Kyouto e ikanakatta hito', 'id' => 'orang yang tidak pergi ke Kyoto', 'en' => 'the person who did not go to Kyoto'],
                        ['ja' => 'ミラーさんが 住《す》んで いた うち', 'reading' => 'Miraa-san ga sunde ita uchi', 'id' => 'rumah yang dulu dihuni Pak Miller', 'en' => 'the house Mr. Miller used to live in'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mengenali orang',
                        'title_en' => 'Recognising someone',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'あの 京都《きょうと》へ 行《い》く 人《ひと》は だれですか。', 'reading' => 'Ano Kyouto e iku hito wa dare desu ka.', 'id' => 'Siapa orang yang akan pergi ke Kyoto itu?', 'en' => 'Who is the person going to Kyoto?'],
                            ['speaker' => 'リナ', 'ja' => 'ミラーさんです。', 'reading' => 'Miraa-san desu.', 'id' => 'Pak Miller.', 'en' => 'It is Mr. Miller.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'Anak kalimat: kata sifat & kata benda',
                'title_en' => 'Clauses with adjectives and nouns',
                'pattern' => 'Aい／Aな(な)／N(の) ＋ N',
                'payload' => [
                    'explanation_id' => 'Di dalam anak kalimat, kata sifat-い tetap bentuk biasanya (高《たか》くて、髪《かみ》が 黒《くろ》い 人《ひと》), kata sifat-な memakai な (親切《しんせつ》で、きれいな 人《ひと》), dan kata benda memakai の (六十五歳《ろくじゅうごさい》の 人《ひと》). Beberapa keterangan bisa disambung dengan bentuk て.',
                    'explanation_en' => 'Inside the clause an i-adjective keeps its plain form (takakute, kami ga kuroi hito), a na-adjective takes na (shinsetsu de, kirei na hito), and a noun takes no (rokujuu-go sai no hito). Several descriptions can be linked with the te-form.',
                    'notes_id' => [
                        'Bandingkan pola Pelajaran 2 dan 8: ミラーさんの うち／新《あたら》しい うち／きれいな うち.',
                    ],
                    'notes_en' => [
                        'Compare the patterns of Lessons 2 and 8: Miraa-san no uchi / atarashii uchi / kirei na uchi.',
                    ],
                    'examples' => [
                        ['ja' => '背《せ》が 高《たか》くて、髪《かみ》が 黒《くろ》い 人《ひと》', 'reading' => 'Se ga takakute, kami ga kuroi hito', 'id' => 'orang yang tinggi dan berambut hitam', 'en' => 'a person who is tall and has black hair'],
                        ['ja' => '親切《しんせつ》で、きれいな 人《ひと》', 'reading' => 'Shinsetsu de, kirei na hito', 'id' => 'orang yang baik hati dan cantik', 'en' => 'a kind and beautiful person'],
                        ['ja' => 'ケーキが 好《す》きな 人《ひと》', 'reading' => 'Keeki ga suki na hito', 'id' => 'orang yang suka kue', 'en' => 'a person who likes cake'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menjelaskan penampilan',
                        'title_en' => 'Describing appearance',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'ミラーさんは どの 人《ひと》ですか。', 'reading' => 'Miraa-san wa dono hito desu ka.', 'id' => 'Yang mana Pak Miller?', 'en' => 'Which one is Mr. Miller?'],
                            ['speaker' => 'リナ', 'ja' => '背《せ》が 高《たか》くて、眼鏡《めがね》を かけて いる 人《ひと》です。', 'reading' => 'Se ga takakute, megane o kakete iru hito desu.', 'id' => 'Yang tinggi dan berkacamata itu.', 'en' => 'The tall one wearing glasses.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'Anak kalimat dalam berbagai pola',
                'title_en' => 'Clauses inside larger sentence patterns',
                'pattern' => '普通形《ふつうけい》＋N は／を／が／に…',
                'payload' => [
                    'explanation_id' => 'Kata benda yang diterangkan anak kalimat bisa berperan sebagai topik (は), objek (を), penyebab rasa suka (が), tempat (に／へ) dan seterusnya, sama seperti kata benda biasa. Subjek di dalam anak kalimat ditandai が, bukan は: これは ミラーさんが 作《つく》った ケーキです。',
                    'explanation_en' => 'The noun described by the clause can act as topic (wa), object (o), object of liking (ga), place (ni/e), etc., just like an ordinary noun. The subject inside the clause is marked with ga, not wa: kore wa Miraa-san ga tsukutta keeki desu.',
                    'notes_id' => [
                        'Salah: ミラーさんは 作《つく》った ケーキ (subjek anak kalimat harus が).',
                    ],
                    'notes_en' => [
                        'Wrong: Miraa-san wa tsukutta keeki (the subject of the clause must be ga).',
                    ],
                    'examples' => [
                        ['ja' => 'これは ミラーさんが 作《つく》った ケーキです。', 'reading' => 'Kore wa Miraa-san ga tsukutta keeki desu.', 'id' => 'Ini kue yang dibuat Pak Miller.', 'en' => 'This is the cake Mr. Miller made.'],
                        ['ja' => 'わたしは カリナさんが かいた 絵《え》が 好《す》きです。', 'reading' => 'Watashi wa Karina-san ga kaita e ga suki desu.', 'id' => 'Saya suka lukisan yang dilukis Karina.', 'en' => 'I like the picture Karina painted.'],
                        ['ja' => 'ミラーさんが 住《す》んで いた うちへ 行《い》った ことが あります。', 'reading' => 'Miraa-san ga sunde ita uchi e itta koto ga arimasu.', 'id' => 'Saya pernah pergi ke rumah yang dulu dihuni Pak Miller.', 'en' => 'I have been to the house Mr. Miller used to live in.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Membicarakan kue',
                        'title_en' => 'Talking about a cake',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'これは だれが 作《つく》った ケーキですか。', 'reading' => 'Kore wa dare ga tsukutta keeki desu ka.', 'id' => 'Ini kue buatan siapa?', 'en' => 'Who made this cake?'],
                            ['speaker' => 'リナ', 'ja' => 'ミラーさんが 作《つく》った ケーキです。', 'reading' => 'Miraa-san ga tsukutta keeki desu.', 'id' => 'Kue buatan Pak Miller.', 'en' => 'A cake Mr. Miller made.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'V(辞書形《じしょけい》) 時間《じかん》／約束《やくそく》／用事《ようじ》',
                'title_en' => 'V (dictionary form) + jikan / yakusoku / youji',
                'pattern' => 'V(辞書形《じしょけい》) ＋ 時間《じかん》／約束《やくそく》／用事《ようじ》',
                'payload' => [
                    'explanation_id' => 'Untuk menyatakan waktu, janji, atau urusan untuk melakukan sesuatu, kata kerja dijadikan bentuk kamus lalu diletakkan di depan kata benda 時間《じかん》, 約束《やくそく》, 用事《ようじ》. Isi kegiatannya diterangkan oleh kata kerja itu.',
                    'explanation_en' => 'To say that there is time, an appointment or an errand for doing something, put the verb in dictionary form before the nouns jikan, yakusoku, youji. The verb tells what the activity is.',
                    'notes_id' => [
                        'Biasanya dengan あります／ありません: 朝《あさ》ごはんを 食《た》べる 時間《じかん》が ありません。',
                    ],
                    'notes_en' => [
                        'Usually with arimasu/arimasen: asagohan o taberu jikan ga arimasen.',
                    ],
                    'examples' => [
                        ['ja' => 'わたしは 朝《あさ》ごはんを 食《た》べる 時間《じかん》が ありません。', 'reading' => 'Watashi wa asagohan o taberu jikan ga arimasen.', 'id' => 'Saya tidak punya waktu untuk sarapan.', 'en' => 'I have no time to eat breakfast.'],
                        ['ja' => 'わたしは 友達《ともだち》と 映画《えいが》を 見《み》る 約束《やくそく》が あります。', 'reading' => 'Watashi wa tomodachi to eiga o miru yakusoku ga arimasu.', 'id' => 'Saya sudah janji menonton film bersama teman.', 'en' => 'I have an arrangement to see a film with a friend.'],
                        ['ja' => 'きょうは 市役所《しやくしょ》へ 行《い》く 用事《ようじ》が あります。', 'reading' => 'Kyou wa shiyakusho e iku youji ga arimasu.', 'id' => 'Hari ini saya ada urusan ke kantor wali kota.', 'en' => 'Today I have an errand to the city hall.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menolak ajakan',
                        'title_en' => 'Declining an invitation',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'きょう いっしょに 食事《しょくじ》しませんか。', 'reading' => 'Kyou issho ni shokuji shimasen ka.', 'id' => 'Hari ini mau makan bersama?', 'en' => 'Shall we eat together today?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません、友達《ともだち》と 会《あ》う 約束《やくそく》が あります。', 'reading' => 'Sumimasen, tomodachi to au yakusoku ga arimasu.', 'id' => 'Maaf, saya ada janji bertemu teman.', 'en' => 'Sorry, I have a promise to meet a friend.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'V(ます形《けい》) ましょうか',
                'title_en' => 'V-mashou ka (suggesting together)',
                'pattern' => 'V(ます形《けい》)ましょうか',
                'payload' => [
                    'explanation_id' => 'Pada Pelajaran 14 pola ini dipakai untuk menawarkan diri melakukan sesuatu bagi lawan bicara. Pada pelajaran ini ましょうか juga dipakai untuk mengajak lawan bicara melakukan sesuatu BERSAMA-SAMA: "Bagaimana kalau kita …?".',
                    'explanation_en' => 'In Lesson 14 this pattern offered to do something for the listener. In this lesson mashou ka is also used to propose doing something TOGETHER: "Shall we …?".',
                    'notes_id' => [
                        'Kalau menawarkan bantuan: 手伝《てつだ》いましょうか。 Kalau mengajak: 今《いま》から 行《い》きましょうか。',
                    ],
                    'notes_en' => [
                        'Offering help: tetsudaimashou ka. Inviting: ima kara ikimashou ka.',
                    ],
                    'examples' => [
                        ['ja' => '今《いま》から 行《い》きましょうか。', 'reading' => 'Ima kara ikimashou ka.', 'id' => 'Bagaimana kalau kita pergi sekarang?', 'en' => 'Shall we go now?'],
                        ['ja' => 'いっしょに 食事《しょくじ》しましょうか。', 'reading' => 'Issho ni shokuji shimashou ka.', 'id' => 'Bagaimana kalau kita makan bersama?', 'en' => 'Shall we eat together?'],
                        ['ja' => '荷物《にもつ》を 持《も》ちましょうか。', 'reading' => 'Nimotsu o mochimashou ka.', 'id' => 'Biar saya bawakan barangnya.', 'en' => 'Shall I carry your luggage?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Melihat kamar',
                        'title_en' => 'Viewing a room',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'この 部屋《へや》を 今日《きょう》 見《み》る ことが できますか。', 'reading' => 'Kono heya o kyou miru koto ga dekimasu ka.', 'id' => 'Bisakah saya melihat kamar ini hari ini?', 'en' => 'Can I see this room today?'],
                            ['speaker' => 'リナ', 'ja' => 'ええ。今《いま》から 行《い》きましょうか。', 'reading' => 'Ee. Ima kara ikimashou ka.', 'id' => 'Bisa. Bagaimana kalau kita pergi sekarang?', 'en' => 'Yes. Shall we go now?'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 23 — とき／と (bersyarat)／N が Adj／N を + kata kerja gerak
    // ------------------------------------------------------------------

    private function lesson23Cards(): array
    {
        return [
            [
                'title_id' => 'とき（時《じ》）の 作《つく》り方《かた》',
                'title_en' => 'Forming toki (when …)',
                'pattern' => 'V(辞書形《じしょけい》／ない形《けい》)／Aい／Aな(な)／Nの ＋ とき、〜',
                'payload' => [
                    'explanation_id' => 'とき menyatakan waktu terjadinya suatu keadaan atau perbuatan yang dijelaskan di kalimat pokok yang menyusul. Bentuk di depan とき sama dengan bentuk yang menerangkan kata benda: kata kerja bentuk kamus／bentuk ない, kata sifat-い apa adanya, kata sifat-な + な, dan kata benda + の.',
                    'explanation_en' => 'toki states the time when a situation or action in the main clause takes place. The form before toki is the same as when modifying a noun: verbs in dictionary/nai form, i-adjectives as they are, na-adjectives + na, and nouns + no.',
                    'notes_id' => [
                        'Kalimat dengan とき tidak memengaruhi bentuk waktu kalimat pokok.',
                        'Contoh: 暇《ひま》な とき, 病気《びょうき》の とき, 若《わか》い とき.',
                    ],
                    'notes_en' => [
                        'A toki clause does not affect the tense of the main clause.',
                        'Examples: hima na toki, byouki no toki, wakai toki.',
                    ],
                    'examples' => [
                        ['ja' => '図書館《としょかん》で 本《ほん》を 借《か》りる とき、カードが 要《い》ります。', 'reading' => 'Toshokan de hon o kariru toki, kaado ga irimasu.', 'id' => 'Kalau meminjam buku di perpustakaan, kartu diperlukan.', 'en' => 'When you borrow books at the library, you need a card.'],
                        ['ja' => '使い方《つかいかた》が わからない とき、わたしに 聞《き》いて ください。', 'reading' => 'Tsukaikata ga wakaranai toki, watashi ni kiite kudasai.', 'id' => 'Kalau tidak tahu cara memakainya, tanyakan kepada saya.', 'en' => 'When you do not know how to use it, please ask me.'],
                        ['ja' => '暇《ひま》な とき、うちへ 来《き》ませんか。', 'reading' => 'Hima na toki, uchi e kimasen ka.', 'id' => 'Kalau sedang senggang, datanglah ke rumah.', 'en' => 'When you are free, why not come to my place?'],
                        ['ja' => '若《わか》い とき、あまり 勉強《べんきょう》しませんでした。', 'reading' => 'Wakai toki, amari benkyou shimasen deshita.', 'id' => 'Waktu muda saya tidak begitu belajar.', 'en' => 'When I was young, I did not study much.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Kalau tidak enak badan',
                        'title_en' => 'When you feel unwell',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '体《からだ》の 調子《ちょうし》が 悪《わる》い とき、どう しますか。', 'reading' => 'Karada no choushi ga warui toki, dou shimasu ka.', 'id' => 'Kalau badan tidak enak, apa yang Anda lakukan?', 'en' => 'What do you do when you feel unwell?'],
                            ['speaker' => 'リナ', 'ja' => '元気茶《げんきちゃ》を 飲《の》みます。', 'reading' => 'Genkicha o nomimasu.', 'id' => 'Saya minum teh "Genki-cha".', 'en' => 'I drink Genki tea.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'V(辞書形《じしょけい》) とき ／ V(た形《けい》) とき',
                'title_en' => 'V (dictionary form) toki vs V (ta-form) toki',
                'pattern' => 'V(辞書形《じしょけい》)とき、〜 ／ V(た形《けい》)とき、〜',
                'payload' => [
                    'explanation_id' => 'Bentuk kata kerja di depan とき menentukan urutan waktunya. V bentuk kamus + とき: perbuatan kalimat pokok terjadi SEBELUM perbuatan di anak kalimat (belum sampai). V bentuk た + とき: perbuatan kalimat pokok terjadi SETELAH perbuatan di anak kalimat (sudah selesai).',
                    'explanation_en' => 'The verb form before toki sets the order of events. V dictionary form + toki: the main-clause action happens BEFORE the action in the clause (not yet done). V ta-form + toki: the main-clause action happens AFTER the action in the clause (already done).',
                    'notes_id' => [
                        'パリへ 行《い》く とき = waktu mau pergi (belum berangkat); パリへ 行《い》った とき = waktu sampai／berada di Paris.',
                    ],
                    'notes_en' => [
                        'Pari e iku toki = when about to go (before leaving); Pari e itta toki = when in Paris (after arriving).',
                    ],
                    'examples' => [
                        ['ja' => 'パリへ 行《い》く とき、かばんを 買《か》いました。', 'reading' => 'Pari e iku toki, kaban o kaimashita.', 'id' => 'Sebelum berangkat ke Paris, saya membeli tas.', 'en' => 'I bought a bag when I was about to go to Paris (before leaving).'],
                        ['ja' => 'パリへ 行《い》った とき、かばんを 買《か》いました。', 'reading' => 'Pari e itta toki, kaban o kaimashita.', 'id' => 'Waktu di Paris, saya membeli tas.', 'en' => 'I bought a bag when I went to Paris (while there).'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Beli tas di mana?',
                        'title_en' => 'Where did you buy the bag?',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'その かばんは どこで 買《か》いましたか。', 'reading' => 'Sono kaban wa doko de kaimashita ka.', 'id' => 'Tas itu Anda beli di mana?', 'en' => 'Where did you buy that bag?'],
                            ['speaker' => 'リナ', 'ja' => 'パリへ 行《い》った とき、買《か》いました。', 'reading' => 'Pari e itta toki, kaimashita.', 'id' => 'Saya beli waktu pergi ke Paris.', 'en' => 'I bought it when I went to Paris.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'V(辞書形《じしょけい》) と、〜',
                'title_en' => 'V (dictionary form) to, …',
                'pattern' => 'V(辞書形《じしょけい》)と、〜',
                'payload' => [
                    'explanation_id' => 'と (bersyarat) menyatakan bahwa jika perbuatan atau kejadian di depan と terjadi, maka akibat yang pasti (otomatis) terjadi di kalimat pokok: "kalau … pasti …". Dipakai untuk mekanisme mesin, petunjuk arah jalan, gejala alam, dan hal yang selalu berlaku.',
                    'explanation_en' => 'to (conditional) says that when the action or event before to happens, the result in the main clause inevitably follows: "if/when … then …". It is used for how machines work, road directions, natural phenomena and things that always hold.',
                    'notes_id' => [
                        'Kalimat pokok tidak boleh berisi keinginan, ajakan, atau permohonan.',
                    ],
                    'notes_en' => [
                        'The main clause cannot contain a wish, an invitation, or a request.',
                    ],
                    'examples' => [
                        ['ja' => 'この ボタンを 押《お》すと、お釣《つ》りが 出《で》ます。', 'reading' => 'Kono botan o osu to, otsuri ga demasu.', 'id' => 'Kalau tombol ini ditekan, uang kembalian keluar.', 'en' => 'If you press this button, the change comes out.'],
                        ['ja' => 'これを 回《まわ》すと、音《おと》が 大《おお》きく なります。', 'reading' => 'Kore o mawasu to, oto ga ookiku narimasu.', 'id' => 'Kalau ini diputar, suaranya membesar.', 'en' => 'If you turn this, the sound gets louder.'],
                        ['ja' => '右《みぎ》へ 曲《ま》がると、郵便局《ゆうびんきょく》が あります。', 'reading' => 'Migi e magaru to, yuubinkyoku ga arimasu.', 'id' => 'Kalau belok kanan, ada kantor pos.', 'en' => 'If you turn right, there is a post office.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mesin tiket',
                        'title_en' => 'The ticket machine',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'この 機械《きかい》の 使い方《つかいかた》を 教《おし》えて ください。', 'reading' => 'Kono kikai no tsukaikata o oshiete kudasai.', 'id' => 'Tolong ajarkan cara memakai mesin ini.', 'en' => 'Please tell me how to use this machine.'],
                            ['speaker' => 'リナ', 'ja' => 'この ボタンを 押《お》すと、切符《きっぷ》が 出《で》ます。', 'reading' => 'Kono botan o osu to, kippu ga demasu.', 'id' => 'Kalau tombol ini ditekan, tiketnya keluar.', 'en' => 'If you press this button, the ticket comes out.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'N が Adj',
                'title_en' => 'N ga Adj',
                'pattern' => 'N が Aい／Aな',
                'payload' => [
                    'explanation_id' => 'Partikel が dipakai untuk menyatakan gejala yang ditangkap langsung oleh panca indera, atau untuk menyampaikan peristiwa secara objektif (Pelajaran 14). Ini tidak terbatas pada kalimat kata kerja, tetapi juga dipakai pada kalimat kata sifat: 音《おと》が 小《ちい》さいです (suaranya kecil).',
                    'explanation_en' => 'The particle ga is used for phenomena perceived directly by the senses, or for reporting an event objectively (Lesson 14). This is not limited to verb sentences; it is also used in adjective sentences: oto ga chiisai desu (the sound is small).',
                    'notes_id' => [
                        'Sering dipakai saat memberi tahu kondisi yang terlihat／terdengar saat itu.',
                    ],
                    'notes_en' => [
                        'Often used to report a condition being seen or heard right now.',
                    ],
                    'examples' => [
                        ['ja' => '音《おと》が 小《ちい》さいです。', 'reading' => 'Oto ga chiisai desu.', 'id' => 'Suaranya kecil.', 'en' => 'The sound is quiet.'],
                        ['ja' => '空《そら》が 青《あお》いです。', 'reading' => 'Sora ga aoi desu.', 'id' => 'Langitnya biru.', 'en' => 'The sky is blue.'],
                        ['ja' => 'この 部屋《へや》は 電気《でんき》が 暗《くら》いです。', 'reading' => 'Kono heya wa denki ga kurai desu.', 'id' => 'Kamar ini lampunya redup.', 'en' => 'The light in this room is dim.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Suaranya kecil',
                        'title_en' => 'The volume is low',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'すみません、音《おと》が 小《ちい》さいです。', 'reading' => 'Sumimasen, oto ga chiisai desu.', 'id' => 'Maaf, suaranya kecil.', 'en' => 'Sorry, the sound is too quiet.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'これを 回《まわ》すと、大《おお》きく なりますよ。', 'reading' => 'Kore o mawasu to, ookiku narimasu yo.', 'id' => 'Kalau ini diputar, suaranya membesar.', 'en' => 'If you turn this, it gets louder.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'N を V(移動《いどう》)',
                'title_en' => 'N o + movement verb',
                'pattern' => 'N を 歩《ある》きます／渡《わた》ります／曲《ま》がります',
                'payload' => [
                    'explanation_id' => 'Partikel を yang dipakai bersama kata kerja gerak seperti 散歩《さんぽ》します, 渡《わた》ります, 歩《ある》きます, 曲《ま》がります menunjukkan tempat yang DILEWATI orang atau benda.',
                    'explanation_en' => 'The particle o used with verbs of movement such as sanpo shimasu, watarimasu, arukimasu and magarimasu shows the place that is PASSED THROUGH.',
                    'notes_id' => [
                        'Jalan: 道《みち》を 歩《ある》きます; jembatan: 橋《はし》を 渡《わた》ります; perempatan: 交差点《こうさてん》を 右《みぎ》へ 曲《ま》がります.',
                    ],
                    'notes_en' => [
                        'Road: michi o arukimasu; bridge: hashi o watarimasu; intersection: kousaten o migi e magarimasu.',
                    ],
                    'examples' => [
                        ['ja' => '公園《こうえん》を 散歩《さんぽ》します。', 'reading' => 'Kouen o sanpo shimasu.', 'id' => 'Saya berjalan-jalan di taman.', 'en' => 'I take a walk in the park.'],
                        ['ja' => '道《みち》を 渡《わた》ります。', 'reading' => 'Michi o watarimasu.', 'id' => 'Saya menyeberang jalan.', 'en' => 'I cross the road.'],
                        ['ja' => '交差点《こうさてん》を 右《みぎ》へ 曲《ま》がります。', 'reading' => 'Kousaten o migi e magarimasu.', 'id' => 'Di perempatan saya belok kanan.', 'en' => 'I turn right at the intersection.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menanyakan jalan',
                        'title_en' => 'Asking the way',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '図書館前《としょかんまえ》の バス停《てい》は どこですか。', 'reading' => 'Toshokanmae no basutei wa doko desu ka.', 'id' => 'Halte bus di depan perpustakaan di mana?', 'en' => 'Where is the bus stop in front of the library?'],
                            ['speaker' => 'リナ', 'ja' => 'あの 橋《はし》を 渡《わた》って、交差点《こうさてん》を 右《みぎ》へ 曲《ま》がって ください。', 'reading' => 'Ano hashi o watatte, kousaten o migi e magatte kudasai.', 'id' => 'Menyeberangi jembatan itu, lalu belok kanan di perempatan.', 'en' => 'Cross that bridge and turn right at the intersection.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 24 — くれます／〜てあげます・もらいます・くれます／N は N が V
    // ------------------------------------------------------------------

    private function lesson24Cards(): array
    {
        return [
            [
                'title_id' => 'くれます',
                'title_en' => 'kuremasu',
                'pattern' => 'N1(人《ひと》)は わたしに N2を くれます',
                'payload' => [
                    'explanation_id' => 'あげます (Pelajaran 7) tidak dapat dipakai bila orang lain memberikan sesuatu kepada pembicara (diri sendiri) atau kepada keluarga／orang dekat pembicara. Dalam hal ini dipakai くれます: pemberi menjadi subjek dan penerima ditandai に (わたしに sering dihilangkan).',
                    'explanation_en' => 'ageru (Lesson 7) cannot be used when someone else gives something to the speaker or to the speaker\'s family or close circle. In that case kureru is used: the giver is the subject and the receiver is marked with ni (watashi ni is often dropped).',
                    'notes_id' => [
                        'Bandingkan: わたしは 佐藤《さとう》さんに 花《はな》を あげました。 vs 佐藤《さとう》さんは わたしに カードを くれました。',
                    ],
                    'notes_en' => [
                        'Compare: watashi wa Satou-san ni hana o agemashita vs Satou-san wa watashi ni kaado o kuremashita.',
                    ],
                    'examples' => [
                        ['ja' => 'わたしは 佐藤《さとう》さんに 花《はな》を あげました。', 'reading' => 'Watashi wa Satou-san ni hana o agemashita.', 'id' => 'Saya memberikan bunga kepada Sdr. Sato.', 'en' => 'I gave flowers to Ms. Sato.'],
                        ['ja' => '佐藤《さとう》さんは わたしに クリスマスカードを くれました。', 'reading' => 'Satou-san wa watashi ni kurisumasu kaado o kuremashita.', 'id' => 'Sdr. Sato memberi saya kartu Natal.', 'en' => 'Ms. Sato gave me a Christmas card.'],
                        ['ja' => '佐藤《さとう》さんは 妹《いもうと》に お菓子《かし》を くれました。', 'reading' => 'Satou-san wa imouto ni okashi o kuremashita.', 'id' => 'Sdr. Sato memberi adik saya kue.', 'en' => 'Ms. Sato gave my younger sister some sweets.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Oleh-oleh dari teman',
                        'title_en' => 'A gift from a friend',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'おいしい お菓子《かし》ですね。', 'reading' => 'Oishii okashi desu ne.', 'id' => 'Kuenya enak ya.', 'en' => 'These are tasty sweets.'],
                            ['speaker' => 'リナ', 'ja' => 'ええ、佐藤《さとう》さんが くれました。', 'reading' => 'Ee, Satou-san ga kuremashita.', 'id' => 'Iya, Sdr. Sato yang memberi.', 'en' => 'Yes, Ms. Sato gave them to me.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'V(て形《けい》) あげます',
                'title_en' => 'V-te agemasu',
                'pattern' => 'V(て形《けい》)あげます',
                'payload' => [
                    'explanation_id' => 'V て あげます menyatakan bahwa pelaku melakukan suatu perbuatan yang menguntungkan orang lain. Pelaku menjadi subjek dan penerima kebaikan ditandai に. Hati-hati: kepada atasan pola ini bisa terdengar memaksakan kebaikan; untuk menawarkan bantuan kepada atasan dipakai V ましょうか.',
                    'explanation_en' => 'V-te agemasu says the doer performs an action that benefits another person. The doer is the subject and the beneficiary is marked with ni. Careful: to a superior it can sound as if you are forcing a favour; to offer help to a superior use V-mashou ka.',
                    'notes_id' => [
                        'Menawarkan kepada atasan: タクシーを 呼《よ》びましょうか。／手伝《てつだ》いましょうか。',
                    ],
                    'notes_en' => [
                        'Offering to a superior: takushii o yobimashou ka / tetsudaimashou ka.',
                    ],
                    'examples' => [
                        ['ja' => 'わたしは 木村《きむら》さんに 本《ほん》を 貸《か》して あげました。', 'reading' => 'Watashi wa Kimura-san ni hon o kashite agemashita.', 'id' => 'Saya meminjamkan buku kepada Sdr. Kimura.', 'en' => 'I lent Mr. Kimura a book.'],
                        ['ja' => '友達《ともだち》に 日本語《にほんご》を 教《おし》えて あげます。', 'reading' => 'Tomodachi ni nihongo o oshiete agemasu.', 'id' => 'Saya mengajari teman bahasa Jepang.', 'en' => 'I teach my friend Japanese.'],
                        ['ja' => 'タクシーを 呼《よ》びましょうか。', 'reading' => 'Takushii o yobimashou ka.', 'id' => 'Bagaimana kalau saya panggilkan taksi?', 'en' => 'Shall I call a taxi?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Membantu teman',
                        'title_en' => 'Helping a friend',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '昨日《きのう》 何《なに》を しましたか。', 'reading' => 'Kinou nani o shimashita ka.', 'id' => 'Kemarin Anda melakukan apa?', 'en' => 'What did you do yesterday?'],
                            ['speaker' => 'リナ', 'ja' => '友達《ともだち》に 荷物《にもつ》を 持《も》って あげました。', 'reading' => 'Tomodachi ni nimotsu o motte agemashita.', 'id' => 'Saya membawakan barang teman.', 'en' => 'I carried my friend\'s luggage.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'V(て形《けい》) もらいます',
                'title_en' => 'V-te moraimasu',
                'pattern' => 'V(て形《けい》)もらいます',
                'payload' => [
                    'explanation_id' => 'V て もらいます menyatakan bahwa pembicara (atau orang yang dijadikan subjek) menerima kebaikan dari orang lain. Penerima kebaikan menjadi subjek, dan orang yang berbuat baik ditandai に. Jika subjeknya わたし, biasanya subjek dihilangkan.',
                    'explanation_en' => 'V-te moraimasu says the speaker (or the person made the subject) receives a favour from someone. The receiver is the subject and the person who did the favour is marked with ni. If the subject is watashi it is usually omitted.',
                    'notes_id' => [
                        'Dari sisi penerima. Bandingkan V て くれます yang dilihat dari sisi pemberi.',
                    ],
                    'notes_en' => [
                        'Told from the receiver\'s side. Compare V-te kuremasu, told from the giver\'s side.',
                    ],
                    'examples' => [
                        ['ja' => 'わたしは 山田《やまだ》さんに 図書館《としょかん》の 電話番号《でんわばんごう》を 教《おし》えて もらいました。', 'reading' => 'Watashi wa Yamada-san ni toshokan no denwa bangou o oshiete moraimashita.', 'id' => 'Saya diberi tahu nomor telepon perpustakaan oleh Sdr. Yamada.', 'en' => 'Mr. Yamada told me the library\'s phone number.'],
                        ['ja' => '先生《せんせい》に 日本語《にほんご》を 直《なお》して もらいました。', 'reading' => 'Sensei ni nihongo o naoshite moraimashita.', 'id' => 'Guru memperbaiki bahasa Jepang saya.', 'en' => 'My teacher corrected my Japanese.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Diajari teman',
                        'title_en' => 'Taught by a friend',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '日本語《にほんご》が 上手《じょうず》ですね。', 'reading' => 'Nihongo ga jouzu desu ne.', 'id' => 'Bahasa Jepang Anda bagus ya.', 'en' => 'Your Japanese is good.'],
                            ['speaker' => 'リナ', 'ja' => '友達《ともだち》に 教《おし》えて もらいました。', 'reading' => 'Tomodachi ni oshiete moraimashita.', 'id' => 'Saya belajar dari teman.', 'en' => 'A friend taught me.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'V(て形《けい》) くれます',
                'title_en' => 'V-te kuremasu',
                'pattern' => 'V(て形《けい》)くれます',
                'payload' => [
                    'explanation_id' => 'V て くれます menyatakan bahwa orang lain melakukan sesuatu yang menguntungkan pembicara (atau orang dekatnya). Pelaku perbuatan menjadi subjek. Penerima kebaikan (わたし) biasanya dihilangkan; bila disebut ditandai に (わたしに) atau を sesuai kata kerjanya.',
                    'explanation_en' => 'V-te kuremasu says someone does something that benefits the speaker (or their close circle). The doer is the subject. The receiver (watashi) is usually omitted; if stated, it takes ni (watashi ni) or o depending on the verb.',
                    'notes_id' => [
                        'Sering dipakai untuk mengungkapkan rasa terima kasih.',
                    ],
                    'notes_en' => [
                        'Often used to express gratitude.',
                    ],
                    'examples' => [
                        ['ja' => '母《はは》は [わたしに] セーターを 送《おく》って くれました。', 'reading' => 'Haha wa [watashi ni] seetaa o okutte kuremashita.', 'id' => 'Ibu mengirimi saya sweter.', 'en' => 'My mother sent me a sweater.'],
                        ['ja' => 'わたしを 大阪城《おおさかじょう》へ 連《つ》れて 行《い》って くれます。', 'reading' => 'Watashi o Oosakajou e tsurete itte kuremasu.', 'id' => 'Dia mengantar saya ke Kastil Osaka.', 'en' => 'He takes me to Osaka Castle.'],
                        ['ja' => 'わたしの 引《ひ》っ越《こ》しを 手伝《てつだ》って くれました。', 'reading' => 'Watashi no hikkoshi o tetsudatte kuremashita.', 'id' => 'Dia membantu saya pindah rumah.', 'en' => 'She helped me move.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Berterima kasih',
                        'title_en' => 'Saying thanks',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => '引《ひ》っ越《こ》しは どうでしたか。', 'reading' => 'Hikkoshi wa dou deshita ka.', 'id' => 'Bagaimana pindah rumahnya?', 'en' => 'How was the move?'],
                            ['speaker' => 'ワヒュ', 'ja' => '友達《ともだち》が 手伝《てつだ》って くれました。', 'reading' => 'Tomodachi ga tetsudatte kuremashita.', 'id' => 'Teman-teman membantu saya.', 'en' => 'My friends helped me.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'N1(人《ひと》)は N2が V',
                'title_en' => 'N1 wa N2 ga V',
                'pattern' => 'N1(人《ひと》)は N2が くれました',
                'payload' => [
                    'explanation_id' => 'Ketika objek kalimat (misalnya この ワイン) dijadikan topik dengan は, pelakunya tetap ditandai が. Jawaban 「[この ワインは] 佐藤《さとう》さんが くれました」 menjawab pertanyaan tentang benda itu; この ワインは boleh dihilangkan karena sudah dipahami bersama.',
                    'explanation_en' => 'When the object (e.g. kono wain) is made the topic with wa, the doer is still marked with ga. The answer "[kono wain wa] Satou-san ga kuremashita" answers a question about that thing; kono wain wa can be omitted since both know it.',
                    'notes_id' => [
                        'が menandai pelaku (subjek) yang baru ditonjolkan sebagai jawaban.',
                    ],
                    'notes_en' => [
                        'ga marks the doer (subject) being singled out as the answer.',
                    ],
                    'examples' => [
                        ['ja' => 'おいしい ワインですね。― ええ、[この ワインは] 佐藤《さとう》さんが くれました。', 'reading' => 'Oishii wain desu ne. Ee, [kono wain wa] Satou-san ga kuremashita.', 'id' => 'Anggur yang enak ya. ― Ya, Sdr. Sato yang memberikannya.', 'en' => 'This is nice wine. ― Yes, Ms. Sato gave it to me.'],
                        ['ja' => 'その かばんは 母《はは》が 買《か》って くれました。', 'reading' => 'Sono kaban wa haha ga katte kuremashita.', 'id' => 'Tas itu dibelikan ibu saya.', 'en' => 'My mother bought me that bag.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Anggur enak',
                        'title_en' => 'A nice wine',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'おいしい ワインですね。', 'reading' => 'Oishii wain desu ne.', 'id' => 'Anggur yang enak ya.', 'en' => 'What nice wine.'],
                            ['speaker' => 'リナ', 'ja' => 'ええ、佐藤《さとう》さんが くれました。', 'reading' => 'Ee, Satou-san ga kuremashita.', 'id' => 'Iya, Sdr. Sato yang memberi.', 'en' => 'Yes, Ms. Sato gave it to me.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 25 — 〜たら／〜ても／もし／subjek anak kalimat
    // ------------------------------------------------------------------

    private function lesson25Cards(): array
    {
        return [
            [
                'title_id' => '普通形《ふつうけい》の 過去《かこ》＋ら、〜',
                'title_en' => 'Plain past form + ra, …',
                'pattern' => 'V／Aい／Aな／N の 普通形《ふつうけい》過去《かこ》＋ら、〜',
                'payload' => [
                    'explanation_id' => 'Bentuk biasa lampau + ら menyatakan syarat pengandaian: jika hal di depan ら terjadi, maka hal di kalimat pokok. Dipakai untuk kata kerja, kata sifat, dan kata benda. Di kalimat pokok boleh ada ungkapan keinginan, harapan, ajakan, dan permohonan.',
                    'explanation_en' => 'Plain past form + ra states a hypothetical condition: if what precedes ra happens, then the main clause. It works with verbs, adjectives and nouns. The main clause may contain wishes, hopes, invitations and requests.',
                    'notes_id' => [
                        'Tidak bermakna lampau; bentuk た hanya alat pembentuk syarat.',
                        'Berbeda dengan と (Pel. 23) yang kalimat pokoknya tidak boleh berisi ajakan／permohonan.',
                    ],
                    'notes_en' => [
                        'It does not mean past; the ta-form only builds the condition.',
                        'Unlike to (Lesson 23), the main clause of tara may contain invitations/requests.',
                    ],
                    'examples' => [
                        ['ja' => 'お金《かね》が あったら、旅行《りょこう》します。', 'reading' => 'Okane ga attara, ryokou shimasu.', 'id' => 'Kalau punya uang, saya akan berwisata.', 'en' => 'If I have money, I will travel.'],
                        ['ja' => '時間《じかん》が なかったら、テレビを 見《み》ません。', 'reading' => 'Jikan ga nakattara, terebi o mimasen.', 'id' => 'Kalau tidak ada waktu, saya tidak menonton TV.', 'en' => 'If I have no time, I do not watch TV.'],
                        ['ja' => '安《やす》かったら、パソコンを 買《か》いたいです。', 'reading' => 'Yasukattara, pasokon o kaitai desu.', 'id' => 'Kalau murah, saya ingin beli komputer.', 'en' => 'If it is cheap, I want to buy a computer.'],
                        ['ja' => '暇《ひま》だったら、手伝《てつだ》って ください。', 'reading' => 'Hima dattara, tetsudatte kudasai.', 'id' => 'Kalau senggang, tolong bantu saya.', 'en' => 'If you are free, please help me.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Rencana kalau ada waktu',
                        'title_en' => 'Plans if there is time',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => '天気《てんき》が よかったら、散歩《さんぽ》しませんか。', 'reading' => 'Tenki ga yokattara, sanpo shimasen ka.', 'id' => 'Kalau cuaca bagus, mau jalan-jalan?', 'en' => 'If the weather is good, shall we go for a walk?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいですね。', 'reading' => 'Ii desu ne.', 'id' => 'Boleh.', 'en' => 'Sounds good.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'V(た形《けい》) ら、〜',
                'title_en' => 'V-ta ra, … (once it has happened)',
                'pattern' => 'V(た形《けい》)ら、〜',
                'payload' => [
                    'explanation_id' => 'Jika sudah diketahui bahwa perbuatan bentuk たら pasti akan terjadi, pola ini menyatakan bahwa SETELAH hal itu terjadi, terjadilah aksi atau kejadian di kalimat pokok: "kalau sudah …, …". Ini bukan pengandaian, melainkan urutan yang pasti.',
                    'explanation_en' => 'When it is already known that the ta-form action will surely happen, this pattern says that AFTER it happens, the action or event in the main clause follows: "once …, then …". It is not hypothetical but a definite sequence.',
                    'notes_id' => [
                        'Sering dengan waktu yang pasti datang: 十時《じゅうじ》に なったら、うちへ 帰《かえ》ったら.',
                    ],
                    'notes_en' => [
                        'Often with a time that will surely come: juuji ni nattara, uchi e kaettara.',
                    ],
                    'examples' => [
                        ['ja' => '十時《じゅうじ》に なったら、出《で》かけましょう。', 'reading' => 'Juuji ni nattara, dekakemashou.', 'id' => 'Kalau sudah jam sepuluh, mari berangkat.', 'en' => 'Once it is ten o\'clock, let us set out.'],
                        ['ja' => 'うちへ 帰《かえ》ったら、すぐ シャワーを 浴《あ》びます。', 'reading' => 'Uchi e kaettara, sugu shawaa o abimasu.', 'id' => 'Kalau sudah pulang ke rumah, saya langsung mandi.', 'en' => 'Once I get home, I shower right away.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Setelah pulang',
                        'title_en' => 'After getting home',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '家《いえ》に 着《つ》いたら、電話《でんわ》して ください。', 'reading' => 'Ie ni tsuitara, denwa shite kudasai.', 'id' => 'Kalau sudah sampai rumah, telepon saya.', 'en' => 'Please call me once you get home.'],
                            ['speaker' => 'リナ', 'ja' => 'はい、わかりました。', 'reading' => 'Hai, wakarimashita.', 'id' => 'Baik, mengerti.', 'en' => 'Yes, understood.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'V(て形《けい》)も／Aくても／Aでも／Nでも',
                'title_en' => 'Even if … (temo)',
                'pattern' => 'V(て形《けい》)も／V(ない形《けい》)なくても／Aくても／Aでも／Nでも、〜',
                'payload' => [
                    'explanation_id' => 'て形《けい》 + も (kata kerja), く + ても (kata sifat-い), で + も (kata sifat-な dan kata benda) menyatakan syarat yang berlawanan: "walaupun …, tetap …". Hasil kalimat pokok menyatakan hal yang berlawanan dengan yang biasa diduga, atau tidak terjadinya hal yang biasa diduga.',
                    'explanation_en' => 'te-form + mo (verbs), ku + temo (i-adjectives), de + mo (na-adjectives and nouns) state a concessive condition: "even if …, still …". The main clause expresses the opposite of what one would expect, or that the expected thing does not happen.',
                    'notes_id' => [
                        'Bentuk negatif kata kerja: 〜なくても. Kata benda: 日曜日《にちようび》でも、働《はたら》きます.',
                    ],
                    'notes_en' => [
                        'Negative verb: -nakute mo. Noun: nichiyoubi demo, hatarakimasu.',
                    ],
                    'examples' => [
                        ['ja' => '雨《あめ》が 降《ふ》っても、洗濯《せんたく》します。', 'reading' => 'Ame ga futtemo, sentaku shimasu.', 'id' => 'Walaupun hujan, saya tetap mencuci.', 'en' => 'Even if it rains, I do the laundry.'],
                        ['ja' => '安《やす》くても、団体旅行《だんたいりょこう》は 嫌《きら》いです。', 'reading' => 'Yasukutemo, dantai ryokou wa kirai desu.', 'id' => 'Walaupun murah, saya tidak suka tur rombongan.', 'en' => 'Even if it is cheap, I dislike group tours.'],
                        ['ja' => '便利《べんり》でも、パソコンを 使《つか》いません。', 'reading' => 'Benri demo, pasokon o tsukaimasen.', 'id' => 'Walaupun praktis, saya tidak memakai komputer.', 'en' => 'Even if it is convenient, I do not use a computer.'],
                        ['ja' => '日曜日《にちようび》でも、働《はたら》きます。', 'reading' => 'Nichiyoubi demo, hatarakimasu.', 'id' => 'Walaupun hari Minggu, saya bekerja.', 'en' => 'Even on Sundays I work.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Tetap berangkat',
                        'title_en' => 'Going anyway',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '雨《あめ》が 降《ふ》っても、行《い》きますか。', 'reading' => 'Ame ga futtemo, ikimasu ka.', 'id' => 'Walaupun hujan, Anda tetap pergi?', 'en' => 'Will you go even if it rains?'],
                            ['speaker' => 'リナ', 'ja' => 'はい、行《い》きます。', 'reading' => 'Hai, ikimasu.', 'id' => 'Ya, saya tetap pergi.', 'en' => 'Yes, I will.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'もし',
                'title_en' => 'moshi',
                'pattern' => 'もし＋〜たら、〜',
                'payload' => [
                    'explanation_id' => 'もし dipakai bersama 〜たら untuk memberi tahu lebih dulu bahwa kalimat itu adalah kalimat pengandaian. もし menegaskan perasaan menganggap suatu hal hanya sebagai andaian pembicara: "seandainya …".',
                    'explanation_en' => 'moshi is used together with -tara to signal in advance that the sentence is a hypothetical. moshi stresses the speaker\'s feeling of merely supposing: "suppose …".',
                    'notes_id' => [
                        'もし boleh dihilangkan tanpa mengubah tata bahasa; hanya nuansa andaian yang berkurang.',
                    ],
                    'notes_en' => [
                        'moshi can be dropped without changing the grammar; only the sense of supposition weakens.',
                    ],
                    'examples' => [
                        ['ja' => 'もし 一億円《いちおくえん》 あったら、いろいろな 国《くに》を 旅行《りょこう》したいです。', 'reading' => 'Moshi ichioku en attara, iroiro na kuni o ryokou shitai desu.', 'id' => 'Kalau punya seratus juta yen, saya ingin berwisata ke berbagai negara.', 'en' => 'If I had a hundred million yen, I would like to travel to many countries.'],
                        ['ja' => 'もし 友達《ともだち》が 来《こ》なかったら、どう しますか。', 'reading' => 'Moshi tomodachi ga konakattara, dou shimasu ka.', 'id' => 'Kalau seandainya teman tidak datang, bagaimana?', 'en' => 'What will you do if your friend does not come?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Berandai-andai',
                        'title_en' => 'Daydreaming',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'もし 一億円《いちおくえん》 あったら、何《なに》を しますか。', 'reading' => 'Moshi ichioku en attara, nani o shimasu ka.', 'id' => 'Kalau seandainya punya seratus juta yen, apa yang Anda lakukan?', 'en' => 'If you had a hundred million yen, what would you do?'],
                            ['speaker' => 'リナ', 'ja' => '世界《せかい》を 旅行《りょこう》したいです。', 'reading' => 'Sekai o ryokou shitai desu.', 'id' => 'Saya ingin keliling dunia.', 'en' => 'I would like to travel around the world.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'Subjek dalam anak kalimat',
                'title_en' => 'Subject inside a subordinate clause',
                'pattern' => 'anak kalimat の 主語《しゅご》 ＋ が',
                'payload' => [
                    'explanation_id' => 'Seperti pada 〜てから (Pelajaran 16), subjek di dalam anak kalimat ditandai が. Hal yang sama berlaku untuk 〜てから, とき, 〜まえに, 〜たら, dan 〜ても: subjek anak kalimat memakai が, bukan は.',
                    'explanation_en' => 'As with -te kara (Lesson 16), the subject inside a subordinate clause is marked with ga. The same holds for -te kara, toki, -mae ni, -tara and -temo: the subject of the subordinate clause takes ga, not wa.',
                    'notes_id' => [
                        'Salah: 友達《ともだち》は 来《く》る まえに、〜. Benar: 友達《ともだち》が 来《く》る まえに、〜.',
                    ],
                    'notes_en' => [
                        'Wrong: tomodachi wa kuru mae ni, … Correct: tomodachi ga kuru mae ni, …',
                    ],
                    'examples' => [
                        ['ja' => '友達《ともだち》が 来《く》る まえに、部屋《へや》を 掃除《そうじ》します。', 'reading' => 'Tomodachi ga kuru mae ni, heya o souji shimasu.', 'id' => 'Sebelum teman datang, saya membersihkan kamar.', 'en' => 'Before my friend comes, I clean the room.'],
                        ['ja' => '妻《つま》が 病気《びょうき》の とき、会社《かいしゃ》を 休《やす》みます。', 'reading' => 'Tsuma ga byouki no toki, kaisha o yasumimasu.', 'id' => 'Kalau istri sakit, saya tidak masuk kerja.', 'en' => 'When my wife is ill, I take the day off.'],
                        ['ja' => '友達《ともだち》が 約束《やくそく》の 時間《じかん》に 来《こ》なかったら、どう しますか。', 'reading' => 'Tomodachi ga yakusoku no jikan ni konakattara, dou shimasu ka.', 'id' => 'Kalau teman tidak datang pada waktu yang dijanjikan, bagaimana?', 'en' => 'What will you do if your friend does not come at the appointed time?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menunggu teman',
                        'title_en' => 'Waiting for a friend',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '友達《ともだち》が 来《こ》なかったら、どう しますか。', 'reading' => 'Tomodachi ga konakattara, dou shimasu ka.', 'id' => 'Kalau teman tidak datang, apa yang Anda lakukan?', 'en' => 'What will you do if your friend does not come?'],
                            ['speaker' => 'リナ', 'ja' => '電話《でんわ》して みます。', 'reading' => 'Denwa shite mimasu.', 'id' => 'Saya akan mencoba menelepon.', 'en' => 'I will try phoning.'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
