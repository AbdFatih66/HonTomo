<?php

namespace Database\Seeders;

use App\Models\Grammar;
use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class Lesson6to10BunpouSeeder extends Seeder
{
    /**
     * Same treatment as Lesson1BunpouSeeder and Lesson2to5BunpouSeeder,
     * extended to Pelajaran 6-10: each of these units gets its own
     * "Tata Bahasa (Bunpou)" lesson node (order = 0) that sits BEFORE that
     * unit's vocabulary lesson.
     *
     * Card count per unit follows the order and scope of the grammar points
     * as the textbook presents them:
     *   Pelajaran 6: 8 poin   (を, をします, 何を, なん/なに, で(tempat),
     *                          ませんか, ましょう, 〜か)
     *   Pelajaran 7: 5 poin   (で(alat), 〜語で何ですか, に あげます系,
     *                          に もらいます系, もう〜ました)
     *   Pelajaran 8: 7 poin   (い形/な形, 形容詞＋KB, bentuk negatif,
     *                          とても／あまり, どうですか, どんな, そして／が)
     *   Pelajaran 9: 5 poin   (KBが あります／わかります／好きです, どんな,
     *                          adverbia jumlah, から (alasan), どうして)
     *   Pelajaran 10: 5 poin  (あります／います, (場所)に〜が, 〜は(場所)に,
     *                          KB1や KB2, kata posisi)
     *
     * The Grammar table (GrammarSeeder) only carries rows up to Pelajaran 5,
     * so these cards are written with grammar_id = null. Nothing else in the
     * app depends on that link.
     *
     * Explanations, notes, example sentences and dialogues below are written
     * fresh for this app — nothing is copied from the book (patterns such as
     * "KB o V" are grammatical facts, not copyrightable). Every example is
     * restricted to vocabulary already taught up to that lesson.
     *
     * Japanese strings may carry furigana markers in the form 漢字《かんじ》;
     * RubyText.vue renders them as <ruby>.
     *
     * Safe to run repeatedly AND on a database that already has users:
     *   php artisan db:seed --class=Lesson6to10BunpouSeeder
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

    /**
     * Writes the unit's cards in book order. updateOrCreate is keyed on
     * (lesson, type, order), so a card that already exists is rewritten in
     * place — its id survives, and so does any progress row pointing at it.
     */
    private function seedCards(Lesson $bunpou, array $cards): void
    {
        $grammarIds = Grammar::pluck('id', 'order');

        foreach (array_values($cards) as $position => $card) {
            LessonQuestion::updateOrCreate(
                [
                    'lesson_id' => $bunpou->id,
                    'question_type' => 'grammar',
                    'order' => $position + 1,
                ],
                [
                    'grammar_id' => isset($card['grammar_order'])
                        ? ($grammarIds[$card['grammar_order']] ?? null)
                        : null,
                    'prompt_id' => $card['title_id'],
                    'prompt_en' => $card['title_en'],
                    'japanese_text' => $card['pattern'],
                    'payload' => $card['payload'],
                    'difficulty' => 1,
                    'is_active' => true,
                ]
            );
        }

        // An earlier run may have left more cards than the book has points.
        LessonQuestion::where('lesson_id', $bunpou->id)
            ->where('question_type', 'grammar')
            ->where('order', '>', count($cards))
            ->delete();
    }

    /**
     * Grammar cards that an earlier seeder may have woven into the vocabulary
     * lessons now live in each unit's own Bunpou lesson instead.
     */
    private function removeOldGrammarCards($vocabLessonIds): void
    {
        LessonQuestion::whereIn('lesson_id', $vocabLessonIds)
            ->where('question_type', 'grammar')
            ->delete();
    }

    /**
     * Which unit gets which Bunpou lesson, which existing vocab lesson it
     * gates, and the grammar cards it teaches, in the book's order.
     */
    private function units(): array
    {
        $lessonByUnitOrder = fn (int $unitOrder, int $lessonOrder) => optional(
            Lesson::whereHas('unit', fn ($q) => $q->where('order', $unitOrder)->whereHas('level', fn ($l) => $l->where('code', 'N5')))
                ->where('order', $lessonOrder)
                ->first()
        )->id;

        return [
            6 => [
                'cards' => $this->lesson6Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(5, 1),
            ],
            7 => [
                'cards' => $this->lesson7Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(6, 1),
            ],
            8 => [
                'cards' => $this->lesson8Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(7, 1),
            ],
            9 => [
                'cards' => $this->lesson9Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(8, 1),
            ],
            10 => [
                'cards' => $this->lesson10Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(9, 1),
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Card sets in the order and count of the book's grammar list.
    //
    //   Pelajaran 6 : 8 poin  (を / をします / 何を / なん・なに / (tempat)で /
    //                          ませんか / ましょう / 〜か)
    //   Pelajaran 7 : 6 poin  (で alat / 〜語で / あげます系 / もらいます系 /
    //                          もう〜ました / partikel dihilangkan)
    //   Pelajaran 8 : 8 poin  (jenis kata sifat / KBは形容詞です / 形容詞+KB /
    //                          が / とても・あまり / どうですか / どんな /
    //                          そうですね)
    //   Pelajaran 9 : 5 poin  (unchanged)
    //   Pelajaran 10: 6 poin  (あります・います / (場所)に / KBは(場所)に /
    //                          kata posisi / や / KBですか)
    //
    // The *BaseCards() builders below hold the cards written first; these
    // wrappers add the points that were missing, drop the ones that are not in
    // the book's list for that lesson, and fix a few lines in place.
    // ------------------------------------------------------------------

    private function lesson6Cards(): array
    {
        $c = $this->lesson6BaseCards();

        // Offering help is ましょうか (Pelajaran 14), not part of this point.
        $c[5]['payload']['explanation_id'] = str_replace(', atau menawarkan bantuan', '', $c[5]['payload']['explanation_id']);
        $c[5]['payload']['explanation_en'] = str_replace(', or when offering help', '', $c[5]['payload']['explanation_en']);

        // $c[6] (ちょっと… as a stand-alone point) is not on the book's list;
        // the refusal is still shown in the notes of ませんか.
        return [$c[0], $c[1], $c[2], $this->lesson6NanNani(), $c[3], $c[4], $c[5], $this->lesson6KaUnderstanding()];
    }

    private function lesson6NanNani(): array
    {
        return [
            'title_id' => '何《なん》 と 何《なに》',
            'title_en' => 'nan and nani',
            'pattern' => '何《なん》 ／ 何《なに》',
            'payload' => [
                'explanation_id' => '何 punya dua bacaan. なん dipakai bila kata sesudahnya diawali bunyi baris た・だ・な (何ですか, 何の, 何と), sedangkan なに dipakai di tempat lain, misalnya 何を dan 何が. Untuk menanyakan sarana, 何で dibaca なんで atau なにで. なんで dalam percakapan juga bisa berarti "kenapa", jadi bila ingin jelas menanyakan sarana pakai なにで.',
                'explanation_en' => '何 has two readings. nan is used when the next word begins with a sound from the ta / da / na rows (nan desu ka, nan no, nan to), while nani is used elsewhere, e.g. nani o and nani ga. To ask about a means, 何で is read nande or nani de. Because nande can also mean "why" in conversation, use nani de when you want to be clear that you are asking about the means.',
                'notes_id' => [
                    'Bandingkan: 何《なん》ですか (Ini apa?) vs 何《なに》を 買《か》いますか (Membeli apa?).',
                    'Untuk menanyakan alasan yang jelas, pakai どうして (Pelajaran 9).',
                ],
                'notes_en' => [
                    'Compare: nan desu ka (What is this?) vs nani o kaimasu ka (What will you buy?).',
                    'To ask for a reason unambiguously, use doushite (Lesson 9).',
                ],
                'examples' => [
                    ['ja' => 'これは 何《なん》ですか。', 'reading' => 'Kore wa nan desu ka.', 'id' => 'Ini apa?', 'en' => 'What is this?'],
                    ['ja' => '何《なん》の 本《ほん》ですか。', 'reading' => 'Nan no hon desu ka.', 'id' => 'Buku tentang apa?', 'en' => 'What kind of book is it?'],
                    ['ja' => '何《なに》で 会社《かいしゃ》へ 行《い》きますか。', 'reading' => 'Nani de kaisha e ikimasu ka.', 'id' => 'Pergi ke kantor naik apa?', 'en' => 'How do you go to the office?'],
                ],
                'dialogue' => [
                    'title_id' => 'Di kantin',
                    'title_en' => 'In the canteen',
                    'lines' => [
                        ['speaker' => 'リナ', 'ja' => 'それは 何《なん》ですか。', 'reading' => 'Sore wa nan desu ka.', 'id' => 'Itu apa?', 'en' => 'What is that?'],
                        ['speaker' => 'ワヒュ', 'ja' => '新聞《しんぶん》です。', 'reading' => 'Shinbun desu.', 'id' => 'Koran.', 'en' => 'A newspaper.'],
                        ['speaker' => 'リナ', 'ja' => 'どこで 買《か》いましたか。', 'reading' => 'Doko de kaimashita ka.', 'id' => 'Belinya di mana?', 'en' => 'Where did you buy it?'],
                        ['speaker' => 'ワヒュ', 'ja' => '駅《えき》で 買《か》いました。', 'reading' => 'Eki de kaimashita.', 'id' => 'Beli di stasiun.', 'en' => 'I bought it at the station.'],
                    ],
                ],
            ],
        ];
    }

    private function lesson6KaUnderstanding(): array
    {
        return [
            'title_id' => '〜か (menyatakan mengerti)',
            'title_en' => '〜ka (I see)',
            'pattern' => 'KBですか。',
            'payload' => [
                'explanation_id' => 'Setelah mendengar informasi baru, kamu bisa mengulang bagian intinya lalu menambahkan か dengan nada turun. Artinya "oh, begitu" — kamu menunjukkan bahwa sudah paham, sama seperti そうですか. Berbeda dengan pertanyaan biasa, か di sini tidak menunggu jawaban.',
                'explanation_en' => 'After hearing new information you can repeat its key part and add か with a falling tone. It means "oh, I see" — you show that you have understood, just like そうですか. Unlike a normal question, this か does not wait for an answer.',
                'notes_id' => [
                    'Nada turun = mengerti; nada naik = benar-benar bertanya ulang.',
                    'Sering disusul komentar singkat: 京都《きょうと》ですか。いいですね。',
                ],
                'notes_en' => [
                    'Falling tone = understood; rising tone = really asking again.',
                    'Often followed by a short comment: Kyouto desu ka. Ii desu ne.',
                ],
                'examples' => [
                    ['ja' => '日曜日《にちようび》に 京都《きょうと》へ 行《い》きました。 ― 京都《きょうと》ですか。', 'reading' => 'Nichiyoubi ni Kyouto e ikimashita. ― Kyouto desu ka.', 'id' => 'Hari Minggu saya pergi ke Kyoto. ― Oh, Kyoto, ya.', 'en' => 'I went to Kyoto on Sunday. ― Oh, Kyoto.'],
                    ['ja' => '会議《かいぎ》は 3時《じ》からです。 ― 3時《じ》からですか。', 'reading' => 'Kaigi wa sanji kara desu. ― Sanji kara desu ka.', 'id' => 'Rapatnya mulai jam 3. ― Oh, mulai jam 3, ya.', 'en' => 'The meeting starts at 3. ― Oh, at 3.'],
                    ['ja' => '毎朝《まいあさ》 パンを 食《た》べます。 ― パンですか。', 'reading' => 'Maiasa pan o tabemasu. ― Pan desu ka.', 'id' => 'Setiap pagi saya makan roti. ― Oh, roti, ya.', 'en' => 'I eat bread every morning. ― Oh, bread.'],
                ],
                'dialogue' => [
                    'title_id' => 'Akhir pekan kemarin',
                    'title_en' => 'Last weekend',
                    'lines' => [
                        ['speaker' => 'リナ', 'ja' => '日曜日《にちようび》に 何《なに》を しましたか。', 'reading' => 'Nichiyoubi ni nani o shimashita ka.', 'id' => 'Hari Minggu kemarin melakukan apa?', 'en' => 'What did you do on Sunday?'],
                        ['speaker' => 'ワヒュ', 'ja' => '友達《ともだち》と サッカーを しました。', 'reading' => 'Tomodachi to sakkaa o shimashita.', 'id' => 'Main sepak bola bersama teman.', 'en' => 'I played soccer with friends.'],
                        ['speaker' => 'リナ', 'ja' => 'サッカーですか。いいですね。', 'reading' => 'Sakkaa desu ka. Ii desu ne.', 'id' => 'Oh, sepak bola. Asyik, ya.', 'en' => 'Oh, soccer. Nice.'],
                        ['speaker' => 'ワヒュ', 'ja' => 'ええ、毎週《まいしゅう》 します。', 'reading' => 'Ee, maishuu shimasu.', 'id' => 'Ya, setiap minggu saya main.', 'en' => 'Yes, I play every week.'],
                    ],
                ],
            ],
        ];
    }

    private function lesson7Cards(): array
    {
        $c = $this->lesson7BaseCards();

        // The old dialogue used 借りたい (-tai form, Pelajaran 13) and an
        // adjective (きれい, Pelajaran 8) before they are taught.
        $c[3]['payload']['dialogue']['lines'] = [
            ['speaker' => 'たなか', 'ja' => 'その 本《ほん》は ワヒュさんのですか。', 'reading' => 'Sono hon wa Wahyu-san no desu ka.', 'id' => 'Apakah buku itu punya Wahyu?', 'en' => 'Is that book yours, Wahyu?'],
            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、リナさんに 借《か》りました。', 'reading' => 'Iie, Rina-san ni karimashita.', 'id' => 'Bukan, saya pinjam dari Rina.', 'en' => 'No, I borrowed it from Rina.'],
            ['speaker' => 'たなか', 'ja' => 'そうですか。リナさんは どこですか。', 'reading' => 'Sou desu ka. Rina-san wa doko desu ka.', 'id' => 'Oh begitu. Rina ada di mana?', 'en' => 'I see. Where is Rina?'],
            ['speaker' => 'ワヒュ', 'ja' => '事務所《じむしょ》です。', 'reading' => 'Jimusho desu.', 'id' => 'Di kantor.', 'en' => 'In the office.'],
        ];

        $c[] = $this->lesson7DroppedParticles();

        return $c;
    }

    private function lesson7DroppedParticles(): array
    {
        return [
            'title_id' => 'Partikel yang dihilangkan',
            'title_en' => 'Dropping particles',
            'pattern' => 'KB(は／を／へ) …',
            'payload' => [
                'explanation_id' => 'Dalam percakapan sehari-hari, partikel は, を, dan へ sering dihilangkan selama maksud kalimat tetap jelas dari konteks dan nada bicara. Contoh: コーヒー(を) 飲《の》みませんか。 Yang tidak boleh dibuang adalah partikel yang menentukan arti, seperti に, で, から, まで, dan と.',
                'explanation_en' => 'In everyday conversation the particles は, を and へ are often dropped as long as the meaning stays clear from context and intonation. Example: koohii (o) nomimasen ka. Particles that carry meaning — に, で, から, まで and と — must not be dropped.',
                'notes_id' => [
                    'Dalam tulisan formal (surat, laporan) partikel tetap ditulis lengkap.',
                    'Saat partikel hilang, konteks dan nada suara yang menunjukkan hubungan antar kata.',
                ],
                'notes_en' => [
                    'In formal writing (letters, reports) the particles are written in full.',
                    'When a particle is gone, context and intonation show how the words relate.',
                ],
                'examples' => [
                    ['ja' => 'これ(は) いくらですか。', 'reading' => 'Kore (wa) ikura desu ka.', 'id' => 'Ini harganya berapa?', 'en' => 'How much is this?'],
                    ['ja' => 'コーヒー(を) 飲《の》みませんか。', 'reading' => 'Koohii (o) nomimasen ka.', 'id' => 'Mau minum kopi?', 'en' => 'Would you like some coffee?'],
                    ['ja' => 'あした どこ(へ) 行《い》きますか。', 'reading' => 'Ashita doko (e) ikimasu ka.', 'id' => 'Besok pergi ke mana?', 'en' => 'Where are you going tomorrow?'],
                ],
                'dialogue' => [
                    'title_id' => 'Obrolan santai',
                    'title_en' => 'A casual chat',
                    'lines' => [
                        ['speaker' => 'サリ', 'ja' => '昼《ひる》ごはん 食《た》べましたか。', 'reading' => 'Hirugohan tabemashita ka.', 'id' => 'Sudah makan siang?', 'en' => 'Have you had lunch?'],
                        ['speaker' => 'ワヒュ', 'ja' => 'いいえ、まだです。', 'reading' => 'Iie, mada desu.', 'id' => 'Belum.', 'en' => 'Not yet.'],
                        ['speaker' => 'サリ', 'ja' => 'じゃ、いっしょに 食堂《しょくどう》 行《い》きませんか。', 'reading' => 'Ja, issho ni shokudou ikimasen ka.', 'id' => 'Kalau begitu, ke kantin bareng yuk?', 'en' => 'Then shall we go to the canteen together?'],
                        ['speaker' => 'ワヒュ', 'ja' => 'ええ、行《い》きましょう。', 'reading' => 'Ee, ikimashou.', 'id' => 'Ya, ayo.', 'en' => 'Yes, let us go.'],
                    ],
                ],
            ],
        ];
    }

    private function lesson8Cards(): array
    {
        $b = $this->lesson8BaseCards();

        // Negative forms are taught fully in Pelajaran 12. Here only the
        // present negative is needed (for あまり), so the stand-alone card
        // (which also carried past forms) is folded into とても／あまり.
        $b[3]['payload']['explanation_id'] = 'Bentuk negatif sekarang: kata sifat-い mengganti い dengan くないです (高《たか》い → 高《たか》くないです), kata sifat-な memakai じゃありません (静《しず》か → 静《しず》かじゃありません). '
            . $b[3]['payload']['explanation_id'];
        $b[3]['payload']['explanation_en'] = 'Present negative: an い-adjective replaces い with くないです (takai → takakunai desu); a な-adjective uses じゃありません (shizuka → shizuka ja arimasen). '
            . $b[3]['payload']['explanation_en'];
        $b[3]['payload']['notes_id'][] = 'いい tidak beraturan: いい → よくないです.';
        $b[3]['payload']['notes_en'][] = 'いい is irregular: ii → yokunai desu.';

        // Keep the adjective + noun card free of a forward reference.
        $b[1]['payload']['notes_id'][1] = 'いい (bagus) termasuk kata sifat-い yang bentuk negatifnya tidak beraturan; lihat kartu とても／あまり.';
        $b[1]['payload']['notes_en'][1] = 'いい (good) is an い-adjective with an irregular negative; see the とても／あまり card.';

        $b[0]['pattern'] = 'い形容詞 ／ な形容詞';

        return [$b[0], $this->lesson8Predicate(), $b[1], $b[6], $b[3], $b[4], $b[5], $this->lesson8SouDesuNe()];
    }

    private function lesson8Predicate(): array
    {
        return [
            'title_id' => 'KB は 形容詞 です',
            'title_en' => 'KB は adjective です',
            'pattern' => 'KBは 形容詞(な)です ／ 形容詞(い)です',
            'payload' => [
                'explanation_id' => 'Kata sifat dipakai sebagai predikat: KB は kata sifat です. Pada kata sifat-な, な hanya muncul saat menerangkan kata benda, jadi di akhir kalimat ia dihilangkan: きれいです (bukan きれいなです). Pada kata sifat-い, akhiran い dipertahankan: 大《おお》きいです. です membuat kalimat sopan dan tidak berarti "adalah". Untuk bertanya, tambahkan か.',
                'explanation_en' => 'An adjective can be the predicate: Noun は adjective です. With a な-adjective, な appears only before a noun, so it is dropped at the end of the sentence: kirei desu (not kirei na desu). An い-adjective keeps its final い: ookii desu. です only makes the sentence polite; it does not mean "is". Add か to ask a question.',
                'notes_id' => [
                    'Salah: この 町《まち》は 静《しず》かなです。 Benar: この 町《まち》は 静《しず》かです。',
                    'Kata sifat-い tidak boleh kehilangan い: ✗ 大《おお》きです → ✓ 大《おお》きいです.',
                ],
                'notes_en' => [
                    'Wrong: kono machi wa shizuka na desu. Right: kono machi wa shizuka desu.',
                    'An い-adjective cannot lose its い: ✗ ooki desu → ✓ ookii desu.',
                ],
                'examples' => [
                    ['ja' => 'この 山《やま》は 高《たか》いです。', 'reading' => 'Kono yama wa takai desu.', 'id' => 'Gunung ini tinggi.', 'en' => 'This mountain is high.'],
                    ['ja' => 'この 町《まち》は きれいです。', 'reading' => 'Kono machi wa kirei desu.', 'id' => 'Kota ini bersih dan indah.', 'en' => 'This town is beautiful.'],
                    ['ja' => 'たなかさんは 親切《しんせつ》ですか。', 'reading' => 'Tanaka-san wa shinsetsu desu ka.', 'id' => 'Apakah Tanaka baik hati?', 'en' => 'Is Tanaka kind?'],
                ],
                'dialogue' => [
                    'title_id' => 'Tentang kota baru',
                    'title_en' => 'About a new town',
                    'lines' => [
                        ['speaker' => 'リナ', 'ja' => '新《あたら》しい 町《まち》は 静《しず》かですか。', 'reading' => 'Atarashii machi wa shizuka desu ka.', 'id' => 'Apakah kota barunya tenang?', 'en' => 'Is the new town quiet?'],
                        ['speaker' => 'ワヒュ', 'ja' => 'はい、静《しず》かです。', 'reading' => 'Hai, shizuka desu.', 'id' => 'Ya, tenang.', 'en' => 'Yes, it is quiet.'],
                        ['speaker' => 'リナ', 'ja' => '食《た》べ物《もの》は おいしいですか。', 'reading' => 'Tabemono wa oishii desu ka.', 'id' => 'Makanannya enak?', 'en' => 'Is the food good?'],
                        ['speaker' => 'ワヒュ', 'ja' => 'ええ、おいしいです。', 'reading' => 'Ee, oishii desu.', 'id' => 'Ya, enak.', 'en' => 'Yes, it is delicious.'],
                    ],
                ],
            ],
        ];
    }

    private function lesson8SouDesuNe(): array
    {
        return [
            'title_id' => 'そうですね',
            'title_en' => 'sou desu ne',
            'pattern' => 'そうですね。',
            'payload' => [
                'explanation_id' => 'そうですね punya dua fungsi. Pertama, menyetujui atau ikut merasakan ucapan lawan bicara ("ya, benar juga"). Kedua, dipakai sebagai kata pengisi sambil berpikir sebelum menjawab pertanyaan yang tidak bisa langsung dijawab ("hmm, apa ya…"). Bedanya dengan そうですか: そうですか menunjukkan baru tahu, そうですね menunjukkan sependapat.',
                'explanation_en' => 'そうですね has two uses. First, to agree with or share the feeling of what the other person said ("yes, that is true"). Second, as a filler while you think before answering a question you cannot answer right away ("hmm, let me see…"). Unlike そうですか, which shows you have just learned something, そうですね shows you agree.',
                'notes_id' => [
                    'Saat dipakai sambil berpikir biasanya diucapkan agak panjang: そうですね…',
                    'Jangan tertukar: そうですか = oh begitu (baru tahu); そうですね = ya, benar (sependapat).',
                ],
                'notes_en' => [
                    'When used while thinking it is usually drawn out: sou desu ne…',
                    'Do not mix them up: sou desu ka = oh, I see (new information); sou desu ne = yes, indeed (agreement).',
                ],
                'examples' => [
                    ['ja' => 'ここは にぎやかですね。 ― そうですね。', 'reading' => 'Koko wa nigiyaka desu ne. ― Sou desu ne.', 'id' => 'Di sini ramai, ya. ― Iya, ya.', 'en' => 'It is lively here. ― Yes, it is.'],
                    ['ja' => '日本《にほん》の 生活《せいかつ》は どうですか。 ― そうですね…、楽《たの》しいですが、ちょっと 忙《いそが》しいです。', 'reading' => 'Nihon no seikatsu wa dou desu ka. ― Sou desu ne…, tanoshii desu ga, chotto isogashii desu.', 'id' => 'Bagaimana kehidupan di Jepang? ― Hmm… menyenangkan, tetapi agak sibuk.', 'en' => 'How is life in Japan? ― Well… it is fun, but a bit busy.'],
                    ['ja' => 'どこで 食《た》べますか。 ― そうですね…。あの 店《みせ》は どうですか。', 'reading' => 'Doko de tabemasu ka. ― Sou desu ne…. Ano mise wa dou desu ka.', 'id' => 'Makan di mana? ― Hmm… bagaimana kalau toko itu?', 'en' => 'Where shall we eat? ― Let me see… how about that place?'],
                ],
                'dialogue' => [
                    'title_id' => 'Memilih tempat makan',
                    'title_en' => 'Choosing a place to eat',
                    'lines' => [
                        ['speaker' => 'リナ', 'ja' => '昼《ひる》ごはんは どこで 食《た》べますか。', 'reading' => 'Hirugohan wa doko de tabemasu ka.', 'id' => 'Makan siang di mana?', 'en' => 'Where shall we have lunch?'],
                        ['speaker' => 'ワヒュ', 'ja' => 'そうですね…。あの 店《みせ》は どうですか。', 'reading' => 'Sou desu ne…. Ano mise wa dou desu ka.', 'id' => 'Hmm… bagaimana kalau toko itu?', 'en' => 'Well… how about that place?'],
                        ['speaker' => 'リナ', 'ja' => 'いいですね。行《い》きましょう。', 'reading' => 'Ii desu ne. Ikimashou.', 'id' => 'Boleh. Ayo pergi.', 'en' => 'Sounds good. Let us go.'],
                    ],
                ],
            ],
        ];
    }

    private function lesson10Cards(): array
    {
        $b = $this->lesson10BaseCards();

        // 遊びます is not taught yet at this point; use an activity that is.
        foreach (['notes_id' => ['公園《こうえん》で 遊《あそ》びます', '公園《こうえん》で 昼《ひる》ごはんを 食《た》べます'],
                  'notes_en' => ['kouen de asobimasu', 'kouen de hirugohan o tabemasu']] as $key => [$from, $to]) {
            $b[1]['payload'][$key] = array_map(fn ($n) => str_replace($from, $to, $n), $b[1]['payload'][$key]);
        }

        // Book order: kata posisi (4) comes before や (5).
        return [$b[0], $b[1], $b[2], $b[4], $b[3], $this->lesson10Confirm()];
    }

    private function lesson10Confirm(): array
    {
        return [
            'title_id' => 'KB ですか (mengulang inti pertanyaan)',
            'title_en' => 'KB desu ka (repeating the key word)',
            'pattern' => 'KBですか。…',
            'payload' => [
                'explanation_id' => 'Dalam percakapan nyata, lawan bicara sering tidak langsung menjawab. Ia lebih dulu mengulang kata inti pertanyaanmu dengan ですか untuk memastikan maksudmu ("Maksudnya X?"), lalu baru menjawab. Jadi ですか di sini bukan pertanyaan baru yang menuntut ya/tidak, melainkan konfirmasi singkat.',
                'explanation_en' => 'In real conversation the other person often does not answer straight away. First they repeat the key word of your question with ですか to make sure they understood ("You mean X?"), and only then answer. So this ですか is not a new yes/no question, just a brief confirmation.',
                'notes_id' => [
                    'Ini kebiasaan yang wajar dan sopan; bukan tanda lawan bicara tidak paham.',
                    'Setelah mengulang, jawaban langsung diberikan tanpa menunggu balasan.',
                ],
                'notes_en' => [
                    'It is a natural, polite habit, not a sign that the other person did not understand.',
                    'After repeating, the answer follows immediately without waiting for a reply.',
                ],
                'examples' => [
                    ['ja' => '郵便局《ゆうびんきょく》は どこですか。 ― 郵便局《ゆうびんきょく》ですか。あの ビルの 中《なか》です。', 'reading' => 'Yuubinkyoku wa doko desu ka. ― Yuubinkyoku desu ka. Ano biru no naka desu.', 'id' => 'Kantor pos di mana? ― Kantor pos, ya? Di dalam gedung itu.', 'en' => 'Where is the post office? ― The post office? It is inside that building.'],
                    ['ja' => 'トイレは どこですか。 ― トイレですか。あそこです。', 'reading' => 'Toire wa doko desu ka. ― Toire desu ka. Asoko desu.', 'id' => 'Toilet di mana? ― Toilet, ya? Di sana.', 'en' => 'Where is the restroom? ― The restroom? Over there.'],
                    ['ja' => 'たなかさんは どこに いますか。 ― たなかさんですか。事務所《じむしょ》に います。', 'reading' => 'Tanaka-san wa doko ni imasu ka. ― Tanaka-san desu ka. Jimusho ni imasu.', 'id' => 'Pak Tanaka ada di mana? ― Pak Tanaka, ya? Ada di kantor.', 'en' => 'Where is Mr. Tanaka? ― Mr. Tanaka? He is in the office.'],
                ],
                'dialogue' => [
                    'title_id' => 'Bertanya arah',
                    'title_en' => 'Asking the way',
                    'lines' => [
                        ['speaker' => 'ワヒュ', 'ja' => 'すみません、銀行《ぎんこう》は どこに ありますか。', 'reading' => 'Sumimasen, ginkou wa doko ni arimasu ka.', 'id' => 'Permisi, bank ada di mana?', 'en' => 'Excuse me, where is the bank?'],
                        ['speaker' => 'たなか', 'ja' => '銀行《ぎんこう》ですか。駅《えき》の 前《まえ》に あります。', 'reading' => 'Ginkou desu ka. Eki no mae ni arimasu.', 'id' => 'Bank, ya? Ada di depan stasiun.', 'en' => 'The bank? It is in front of the station.'],
                        ['speaker' => 'ワヒュ', 'ja' => '駅《えき》の 前《まえ》ですね。ありがとう ございます。', 'reading' => 'Eki no mae desu ne. Arigatou gozaimasu.', 'id' => 'Di depan stasiun, ya. Terima kasih.', 'en' => 'In front of the station. Thank you.'],
                        ['speaker' => 'たなか', 'ja' => 'どういたしまして。', 'reading' => 'Douitashimashite.', 'id' => 'Sama-sama.', 'en' => 'You are welcome.'],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 6 — 7 poin
    // ------------------------------------------------------------------

    private function lesson6BaseCards(): array
    {
        return [
            [
                'title_id' => 'KB を KK (objek langsung)',
                'title_en' => 'KB を V (direct object)',
                'pattern' => 'KBを KK',
                'payload' => [
                    'explanation_id' => 'Partikel を menandai objek langsung, yaitu benda yang dikenai perbuatan. Susunannya: topik は + objek を + kata kerja di akhir kalimat. Kata kerja selalu berada paling belakang, jadi objek disebut lebih dulu. Partikel を ditulis dengan huruf を tetapi dibaca "o".',
                    'explanation_en' => 'The particle を marks the direct object — the thing the action is done to. The order is: topic は + object を + verb at the end. The verb always comes last, so the object is mentioned first. を is written with the character を but read "o".',
                    'notes_id' => [
                        'Bentuk negatif memakai 〜ません: パンを 食《た》べません = tidak makan roti.',
                        'Bentuk lampau 〜ました, lampau negatif 〜ませんでした.',
                    ],
                    'notes_en' => [
                        'The negative uses -masen: pan o tabemasen = I do not eat bread.',
                        'Past is -mashita, past negative -masen deshita.',
                    ],
                    'examples' => [
                        ['ja' => 'わたしは ごはんを 食《た》べます。', 'reading' => 'Watashi wa gohan o tabemasu.', 'id' => 'Saya makan nasi.', 'en' => 'I eat rice.'],
                        ['ja' => 'サリさんは お茶《ちゃ》を 飲《の》みます。', 'reading' => 'Sari-san wa ocha o nomimasu.', 'id' => 'Sari minum teh.', 'en' => 'Sari drinks tea.'],
                        ['ja' => 'わたしは たばこを 吸《す》いません。', 'reading' => 'Watashi wa tabako o suimasen.', 'id' => 'Saya tidak merokok.', 'en' => 'I do not smoke.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Sarapan pagi',
                        'title_en' => 'Breakfast',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '朝《あさ》ごはんを 食《た》べましたか。', 'reading' => 'Asagohan o tabemashita ka.', 'id' => 'Sudah sarapan?', 'en' => 'Did you have breakfast?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい、パンと 卵《たまご》を 食《た》べました。', 'reading' => 'Hai, pan to tamago o tabemashita.', 'id' => 'Ya, saya makan roti dan telur.', 'en' => 'Yes, I had bread and eggs.'],
                            ['speaker' => 'たなか', 'ja' => '牛乳《ぎゅうにゅう》も 飲《の》みましたか。', 'reading' => 'Gyuunyuu mo nomimashita ka.', 'id' => 'Minum susu juga?', 'en' => 'Did you drink milk too?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、飲《の》みませんでした。', 'reading' => 'Iie, nomimasen deshita.', 'id' => 'Tidak, saya tidak minum susu.', 'en' => 'No, I did not.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KB を します',
                'title_en' => 'KB を shimasu',
                'pattern' => 'KBを します',
                'payload' => [
                    'explanation_id' => 'します berarti "melakukan/mengerjakan". Digabung dengan kata benda kegiatan, します membentuk banyak sekali ungkapan sehari-hari: olahraga, pekerjaan, belanja. Kata bendanya tetap ditandai を.',
                    'explanation_en' => 'shimasu means "to do". Combined with an activity noun it forms a great many everyday expressions: sports, work, shopping. The noun still takes を.',
                    'notes_id' => [
                        'Beberapa kata benda serapan dipakai langsung: ダンスを します.',
                        'Dalam percakapan santai partikel を kadang dihilangkan, tapi di awal belajar sebaiknya selalu ditulis.',
                    ],
                    'notes_en' => [
                        'Loanword nouns are used directly: dansu o shimasu.',
                        'In casual speech を is sometimes dropped, but write it while you are still learning.',
                    ],
                    'examples' => [
                        ['ja' => 'わたしは 買《か》い物《もの》を します。', 'reading' => 'Watashi wa kaimono o shimasu.', 'id' => 'Saya berbelanja.', 'en' => 'I do the shopping.'],
                        ['ja' => 'きょう 何《なに》を しますか。', 'reading' => 'Kyou nani o shimasu ka.', 'id' => 'Hari ini mau melakukan apa?', 'en' => 'What will you do today?'],
                        ['ja' => '日曜日《にちようび》 わたしは 勉強《べんきょう》を しません。', 'reading' => 'Nichiyoubi watashi wa benkyou o shimasen.', 'id' => 'Hari Minggu saya tidak belajar.', 'en' => 'On Sunday I do not study.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Rencana akhir pekan',
                        'title_en' => 'Weekend plans',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => '土曜日《どようび》 何《なに》を しますか。', 'reading' => 'Doyoubi nani o shimasu ka.', 'id' => 'Hari Sabtu mau apa?', 'en' => 'What are you doing on Saturday?'],
                            ['speaker' => 'サリ', 'ja' => '買《か》い物《もの》を します。', 'reading' => 'Kaimono o shimasu.', 'id' => 'Saya mau belanja.', 'en' => 'I am going shopping.'],
                            ['speaker' => 'リナ', 'ja' => 'わたしは うちで 勉強《べんきょう》を します。', 'reading' => 'Watashi wa uchi de benkyou o shimasu.', 'id' => 'Kalau saya belajar di rumah.', 'en' => 'I will study at home.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => '何《なに》を しますか',
                'title_en' => 'nani o shimasu ka',
                'pattern' => '何を KK か',
                'payload' => [
                    'explanation_id' => 'Untuk menanyakan objek, letakkan 何 di posisi objek lalu tambahkan を dan kata kerja. Bacaan 何 berubah menurut bunyi sesudahnya: dibaca なに sebelum を (何を), tetapi dibaca なん pada 何ですか dan 何の. Jawaban cukup menyebut objeknya saja, tidak perlu mengulang seluruh kalimat.',
                    'explanation_en' => 'To ask about the object, put 何 in the object slot, then を and the verb. The reading of 何 shifts with the sound that follows: it is nani before を (nani o), but nan in nan desu ka and nan no. An answer may name just the object, without repeating the whole sentence.',
                    'notes_id' => [
                        'Kalimat tanya dengan kata tanya tidak dijawab はい／いいえ.',
                        'Bandingkan: 何《なに》を 買《か》いますか vs 何《なん》ですか.',
                    ],
                    'notes_en' => [
                        'Questions containing a question word are not answered with hai/iie.',
                        'Compare: nani o kaimasu ka vs nan desu ka.',
                    ],
                    'examples' => [
                        ['ja' => '何《なに》を 買《か》いますか。', 'reading' => 'Nani o kaimasu ka.', 'id' => 'Mau beli apa?', 'en' => 'What will you buy?'],
                        ['ja' => '野菜《やさい》と 果物《くだもの》を 買《か》います。', 'reading' => 'Yasai to kudamono o kaimasu.', 'id' => 'Beli sayur dan buah.', 'en' => 'Vegetables and fruit.'],
                        ['ja' => '晩《ばん》ごはんに 何《なに》を 食《た》べましたか。', 'reading' => 'Bangohan ni nani o tabemashita ka.', 'id' => 'Makan malam tadi makan apa?', 'en' => 'What did you eat for dinner?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di depan toko',
                        'title_en' => 'In front of the shop',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'ワヒュさん、何《なに》を 買《か》いますか。', 'reading' => 'Wahyu-san, nani o kaimasu ka.', 'id' => 'Wahyu, mau beli apa?', 'en' => 'Wahyu, what are you buying?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'パンと 牛乳《ぎゅうにゅう》です。', 'reading' => 'Pan to gyuunyuu desu.', 'id' => 'Roti dan susu.', 'en' => 'Bread and milk.'],
                            ['speaker' => 'たなか', 'ja' => 'お酒《さけ》は 買《か》いませんか。', 'reading' => 'Osake wa kaimasen ka.', 'id' => 'Tidak beli minuman keras?', 'en' => 'No alcohol?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、飲《の》みませんから。', 'reading' => 'Ee, nomimasen kara.', 'id' => 'Tidak, karena saya tidak minum.', 'en' => 'No — I do not drink.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KB (tempat) で KK',
                'title_en' => 'KB (place) で V',
                'pattern' => '(場所)で KK',
                'payload' => [
                    'explanation_id' => 'Partikel で menandai tempat berlangsungnya suatu perbuatan. Bedakan dengan へ yang menandai arah tujuan, dan dengan に yang dipelajari nanti untuk keberadaan. Kalau kegiatannya terjadi di suatu tempat, pakai で.',
                    'explanation_en' => 'The particle で marks the place where an action takes place. Keep it apart from へ, which marks a destination, and from に, learned later for existence. If the activity happens at a place, use で.',
                    'notes_id' => [
                        'Urutan lazim: (waktu) (tempat)で (objek)を (kata kerja).',
                        'Bandingkan: 食堂《しょくどう》へ 行《い》きます vs 食堂《しょくどう》で 食《た》べます.',
                    ],
                    'notes_en' => [
                        'Common order: (time) (place) de (object) o (verb).',
                        'Compare: shokudou e ikimasu vs shokudou de tabemasu.',
                    ],
                    'examples' => [
                        ['ja' => '食堂《しょくどう》で 昼《ひる》ごはんを 食《た》べます。', 'reading' => 'Shokudou de hirugohan o tabemasu.', 'id' => 'Saya makan siang di kantin.', 'en' => 'I eat lunch at the cafeteria.'],
                        ['ja' => 'うちで 音楽《おんがく》を 聞《き》きます。', 'reading' => 'Uchi de ongaku o kikimasu.', 'id' => 'Saya mendengarkan musik di rumah.', 'en' => 'I listen to music at home.'],
                        ['ja' => '図書館《としょかん》で 本《ほん》を 読《よ》みました。', 'reading' => 'Toshokan de hon o yomimashita.', 'id' => 'Saya membaca buku di perpustakaan.', 'en' => 'I read a book at the library.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Makan siang di mana',
                        'title_en' => 'Where to have lunch',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'いつも どこで 昼《ひる》ごはんを 食《た》べますか。', 'reading' => 'Itsumo doko de hirugohan o tabemasu ka.', 'id' => 'Biasanya makan siang di mana?', 'en' => 'Where do you usually have lunch?'],
                            ['speaker' => 'ワヒュ', 'ja' => '会社《かいしゃ》の 食堂《しょくどう》で 食《た》べます。', 'reading' => 'Kaisha no shokudou de tabemasu.', 'id' => 'Di kantin kantor.', 'en' => 'At the company cafeteria.'],
                            ['speaker' => 'リナ', 'ja' => 'きょうは 喫茶店《きっさてん》で 食《た》べませんか。', 'reading' => 'Kyou wa kissaten de tabemasen ka.', 'id' => 'Hari ini makan di kafe saja, yuk?', 'en' => 'How about the cafe today?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、いいですね。', 'reading' => 'Ee, ii desu ne.', 'id' => 'Boleh, bagus juga.', 'en' => 'Sure, sounds good.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KK ませんか (mengajak)',
                'title_en' => 'V-masen ka (inviting)',
                'pattern' => '〜ませんか',
                'payload' => [
                    'explanation_id' => 'Bentuk 〜ませんか secara harfiah berupa pertanyaan negatif, tetapi fungsinya mengajak lawan bicara dengan sopan: "maukah Anda …?". Karena sifatnya menawarkan, penutur tidak memaksa; lawan bicara bebas menolak. Jawaban setuju biasanya ええ、いいですね.',
                    'explanation_en' => 'The form -masen ka is literally a negative question, but its job is to invite the listener politely: "would you like to …?". Because it only offers, the speaker is not pushing; the listener is free to decline. An agreement is usually ee, ii desu ne.',
                    'notes_id' => [
                        'Menolak dengan halus: すみません、ちょっと…',
                        'Ajakan ini menanyakan kemauan lawan bicara, berbeda dengan ましょう yang mengajak melakukan bersama.',
                    ],
                    'notes_en' => [
                        'To decline gently: sumimasen, chotto…',
                        'This asks about the listener\'s willingness, unlike -mashou, which proposes doing it together.',
                    ],
                    'examples' => [
                        ['ja' => 'いっしょに 昼《ひる》ごはんを 食《た》べませんか。', 'reading' => 'Issho ni hirugohan o tabemasen ka.', 'id' => 'Mau makan siang bersama?', 'en' => 'Would you like to have lunch together?'],
                        ['ja' => '日曜日《にちようび》 映画《えいが》を 見《み》ませんか。', 'reading' => 'Nichiyoubi eiga o mimasen ka.', 'id' => 'Hari Minggu mau nonton film?', 'en' => 'Would you like to see a film on Sunday?'],
                        ['ja' => 'お茶《ちゃ》を 飲《の》みませんか。', 'reading' => 'Ocha o nomimasen ka.', 'id' => 'Mau minum teh?', 'en' => 'Would you like some tea?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mengajak nonton',
                        'title_en' => 'Inviting someone to a film',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '土曜日《どようび》 映画《えいが》を 見《み》ませんか。', 'reading' => 'Doyoubi eiga o mimasen ka.', 'id' => 'Sabtu mau nonton film?', 'en' => 'Would you like to see a film on Saturday?'],
                            ['speaker' => 'サリ', 'ja' => 'ええ、いいですね。', 'reading' => 'Ee, ii desu ne.', 'id' => 'Wah, boleh juga.', 'en' => 'Yes, that sounds good.'],
                            ['speaker' => 'たなか', 'ja' => 'じゃ、2時《じ》に 駅《えき》で 会《あ》いましょう。', 'reading' => 'Ja, niji ni eki de aimashou.', 'id' => 'Kalau begitu, jam 2 bertemu di stasiun, ya.', 'en' => 'Then let us meet at the station at two.'],
                            ['speaker' => 'サリ', 'ja' => 'はい、わかりました。', 'reading' => 'Hai, wakarimashita.', 'id' => 'Baik, mengerti.', 'en' => 'All right.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KK ましょう (ayo)',
                'title_en' => 'V-mashou (let us)',
                'pattern' => '〜ましょう',
                'payload' => [
                    'explanation_id' => 'ましょう mengajak melakukan sesuatu bersama-sama: "mari/ayo". Dipakai juga ketika penutur menerima ajakan lawan bicara, atau menawarkan bantuan. Bedanya dengan ませんか: ましょう terdengar lebih yakin karena penutur sudah menganggap kegiatannya akan dilakukan.',
                    'explanation_en' => 'mashou proposes doing something together: "let us". It is also used when accepting someone\'s invitation, or when offering help. Compared with -masen ka, -mashou sounds more settled: the speaker already assumes it will happen.',
                    'notes_id' => [
                        'Menerima ajakan: ええ、〜ましょう.',
                        'Sering dipakai untuk janji waktu dan tempat: 〜時《じ》に 〜で 会《あ》いましょう.',
                    ],
                    'notes_en' => [
                        'Accepting an invitation: ee, … mashou.',
                        'Often used to fix a time and place: … ji ni … de aimashou.',
                    ],
                    'examples' => [
                        ['ja' => 'いっしょに 帰《かえ》りましょう。', 'reading' => 'Issho ni kaerimashou.', 'id' => 'Ayo pulang bersama.', 'en' => 'Let us go home together.'],
                        ['ja' => 'ちょっと 休《やす》みましょう。', 'reading' => 'Chotto yasumimashou.', 'id' => 'Ayo istirahat sebentar.', 'en' => 'Let us take a short break.'],
                        ['ja' => '12時《じ》に 食堂《しょくどう》で 会《あ》いましょう。', 'reading' => 'Juuniji ni shokudou de aimashou.', 'id' => 'Jam 12 bertemu di kantin, ya.', 'en' => 'Let us meet at the cafeteria at twelve.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menentukan waktu',
                        'title_en' => 'Fixing a time',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'あしたの 朝《あさ》、いっしょに 行《い》きませんか。', 'reading' => 'Ashita no asa, issho ni ikimasen ka.', 'id' => 'Besok pagi mau pergi bareng?', 'en' => 'Shall we go together tomorrow morning?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、行《い》きましょう。何時《なんじ》ですか。', 'reading' => 'Ee, ikimashou. Nanji desu ka.', 'id' => 'Ya, ayo. Jam berapa?', 'en' => 'Yes, let us. What time?'],
                            ['speaker' => 'リナ', 'ja' => '8時《じ》に 駅《えき》で 会《あ》いましょう。', 'reading' => 'Hachiji ni eki de aimashou.', 'id' => 'Jam 8 bertemu di stasiun.', 'en' => 'Let us meet at the station at eight.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'ちょっと〜 (menolak halus)',
                'title_en' => 'chotto… (declining gently)',
                'pattern' => 'すみません、ちょっと…',
                'payload' => [
                    'explanation_id' => 'Menolak ajakan secara langsung dianggap kurang sopan. Karena itu orang Jepang menjawab dengan すみません、ちょっと… lalu membiarkan kalimatnya menggantung. Justru karena tidak selesai, lawan bicara langsung paham bahwa jawabannya "tidak bisa" dan tidak bertanya lebih jauh.',
                    'explanation_en' => 'Turning down an invitation flatly sounds blunt. So people answer with sumimasen, chotto… and let the sentence trail off. Precisely because it is unfinished, the other person understands it is a no and does not press further.',
                    'notes_id' => [
                        'Tidak perlu menyebut alasan sebenarnya; itu justru dianggap wajar.',
                        'Bisa ditambah 残念《ざんねん》ですが (sayang sekali) agar lebih halus.',
                    ],
                    'notes_en' => [
                        'You need not give the real reason; leaving it out is normal.',
                        'zannen desu ga (that is a pity) can be added to soften it further.',
                    ],
                    'examples' => [
                        ['ja' => 'すみません、ちょっと…。', 'reading' => 'Sumimasen, chotto….', 'id' => 'Maaf, saya agak…', 'en' => 'Sorry, I am afraid…'],
                        ['ja' => 'あしたは ちょっと 用事《ようじ》が あります。', 'reading' => 'Ashita wa chotto youji ga arimasu.', 'id' => 'Besok saya ada sedikit urusan.', 'en' => 'I have something on tomorrow.'],
                        ['ja' => '残念《ざんねん》ですが、行《い》きません。', 'reading' => 'Zannen desu ga, ikimasen.', 'id' => 'Sayang sekali, saya tidak ikut.', 'en' => 'It is a pity, but I will not go.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menolak dengan sopan',
                        'title_en' => 'A polite refusal',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '今晩《こんばん》 いっしょに 飲《の》みませんか。', 'reading' => 'Konban issho ni nomimasen ka.', 'id' => 'Malam ini minum bareng, yuk?', 'en' => 'Shall we go for a drink tonight?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません、今晩《こんばん》は ちょっと…。', 'reading' => 'Sumimasen, konban wa chotto….', 'id' => 'Maaf, malam ini agak…', 'en' => 'Sorry, tonight is a bit…'],
                            ['speaker' => 'たなか', 'ja' => 'そうですか。じゃ、また 今度《こんど》。', 'reading' => 'Sou desu ka. Ja, mata kondo.', 'id' => 'Oh begitu. Ya sudah, lain kali.', 'en' => 'I see. Another time, then.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 7 — 5 poin
    // ------------------------------------------------------------------

    private function lesson7BaseCards(): array
    {
        return [
            [
                'title_id' => 'KB (alat／cara) で KK',
                'title_en' => 'KB (tool/means) で V',
                'pattern' => '(道具)で KK',
                'payload' => [
                    'explanation_id' => 'Partikel で juga menandai alat atau cara yang dipakai untuk melakukan sesuatu: memotong dengan gunting, makan dengan sumpit, menulis dengan pensil. Perhatikan bahwa で yang sama sudah dipakai untuk tempat pada Pelajaran 6 — artinya dibedakan dari kata bendanya.',
                    'explanation_en' => 'The particle で also marks the tool or means used to do something: cutting with scissors, eating with chopsticks, writing with a pencil. Note that the same で marked place back in Lesson 6 — the noun tells you which meaning is at work.',
                    'notes_id' => [
                        'Bahasa juga dianggap alat: 日本語《にほんご》で 話《はな》します.',
                        'Untuk kendaraan, で dipakai dengan arti "naik": 電車《でんしゃ》で 行《い》きます.',
                    ],
                    'notes_en' => [
                        'A language counts as a means too: nihongo de hanashimasu.',
                        'With vehicles, de means "by": densha de ikimasu.',
                    ],
                    'examples' => [
                        ['ja' => 'はさみで 紙《かみ》を 切《き》ります。', 'reading' => 'Hasami de kami o kirimasu.', 'id' => 'Memotong kertas dengan gunting.', 'en' => 'I cut paper with scissors.'],
                        ['ja' => 'はしで ごはんを 食《た》べます。', 'reading' => 'Hashi de gohan o tabemasu.', 'id' => 'Makan nasi dengan sumpit.', 'en' => 'I eat rice with chopsticks.'],
                        ['ja' => 'パソコンで メールを 送《おく》りました。', 'reading' => 'Pasokon de meeru o okurimashita.', 'id' => 'Saya mengirim e-mail dengan komputer.', 'en' => 'I sent an email on the computer.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di meja kerja',
                        'title_en' => 'At the desk',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'この 紙《かみ》を 切《き》りますか。', 'reading' => 'Kono kami o kirimasu ka.', 'id' => 'Kertas ini mau dipotong?', 'en' => 'Are you cutting this paper?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい。はさみは ありますか。', 'reading' => 'Hai. Hasami wa arimasu ka.', 'id' => 'Ya. Ada gunting?', 'en' => 'Yes. Are there scissors?'],
                            ['speaker' => 'リナ', 'ja' => 'どうぞ。ホッチキスも 使《つか》いますか。', 'reading' => 'Douzo. Hocchikisu mo tsukaimasu ka.', 'id' => 'Silakan. Pakai stapler juga?', 'en' => 'Here. Do you need the stapler too?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、セロテープで いいです。', 'reading' => 'Iie, seroteepu de ii desu.', 'id' => 'Tidak, pakai selotip saja cukup.', 'en' => 'No, tape is fine.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => '「〜」は 〜語《ご》で 何《なん》ですか',
                'title_en' => '"…" wa …go de nan desu ka',
                'pattern' => '「〜」は 〜語で 何ですか',
                'payload' => [
                    'explanation_id' => 'Pola untuk menanyakan padanan sebuah kata dalam bahasa lain. Kata yang ditanyakan diletakkan dalam tanda kutip sebagai topik, lalu 〜語で menyatakan "dalam bahasa …", dan kalimat ditutup 何ですか. Jawabannya cukup menyebut kata padanannya diikuti です.',
                    'explanation_en' => 'The pattern for asking what a word is in another language. The word in question is quoted as the topic, …go de states "in the language …", and the sentence closes with nan desu ka. The answer simply names the equivalent word plus desu.',
                    'notes_id' => [
                        'Di sini で tetap bermakna "alat/cara", yaitu sarana untuk mengungkapkan.',
                        'Kalau tidak tahu, jawab わかりません atau すみません、わかりません.',
                    ],
                    'notes_en' => [
                        'Here de still carries the "means" sense: the medium of expression.',
                        'If you do not know, answer wakarimasen or sumimasen, wakarimasen.',
                    ],
                    'examples' => [
                        ['ja' => '「ありがとう」は インドネシア語《ご》で 何《なん》ですか。', 'reading' => '"Arigatou" wa Indoneshia-go de nan desu ka.', 'id' => '"Arigatou" dalam bahasa Indonesia apa?', 'en' => 'What is "arigatou" in Indonesian?'],
                        ['ja' => '「terima kasih」です。', 'reading' => '"Terima kasih" desu.', 'id' => '"Terima kasih".', 'en' => 'It is "terima kasih".'],
                        ['ja' => 'これは 日本語《にほんご》で 何《なん》ですか。', 'reading' => 'Kore wa nihongo de nan desu ka.', 'id' => 'Ini dalam bahasa Jepang apa?', 'en' => 'What is this in Japanese?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Bertanya kosakata',
                        'title_en' => 'Asking for a word',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません、これは 日本語《にほんご》で 何《なん》ですか。', 'reading' => 'Sumimasen, kore wa nihongo de nan desu ka.', 'id' => 'Permisi, ini bahasa Jepangnya apa?', 'en' => 'Excuse me, what is this in Japanese?'],
                            ['speaker' => 'たなか', 'ja' => '「消《け》しゴム」です。', 'reading' => '"Keshigomu" desu.', 'id' => '"Keshigomu".', 'en' => 'It is "keshigomu".'],
                            ['speaker' => 'ワヒュ', 'ja' => 'もう 一度《いちど》 お願《ねが》いします。', 'reading' => 'Mou ichido onegaishimasu.', 'id' => 'Tolong sekali lagi.', 'en' => 'Once more, please.'],
                            ['speaker' => 'たなか', 'ja' => '「消《け》しゴム」。紙《かみ》に 書《か》きましょうか。', 'reading' => '"Keshigomu". Kami ni kakimashou ka.', 'id' => '"Keshigomu". Saya tuliskan di kertas, ya?', 'en' => '"Keshigomu". Shall I write it down?'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KB (orang) に あげます・貸《か》します・教《おし》えます',
                'title_en' => 'KB (person) ni agemasu / kashimasu / oshiemasu',
                'pattern' => '(人)に 〜を あげます',
                'payload' => [
                    'explanation_id' => 'Kelompok kata kerja "memberi" — あげます, 貸します, 教えます, 送ります, かけます — menandai penerima dengan に. Susunannya: (orang)に (benda)を KK. Arah perbuatannya keluar dari penutur menuju orang lain.',
                    'explanation_en' => 'The "giving" group of verbs — agemasu, kashimasu, oshiemasu, okurimasu, kakemasu — marks the receiver with に. The order is: (person) ni (thing) o V. The action moves outward, from the speaker to someone else.',
                    'notes_id' => [
                        'Urutan (orang)に dan (benda)を boleh ditukar tanpa mengubah arti, tetapi pola di atas yang paling lazim.',
                        'あげます tidak dipakai untuk memberi kepada diri sendiri.',
                    ],
                    'notes_en' => [
                        'The (person) ni and (thing) o slots can swap without changing the meaning, but the order above is the most common.',
                        'agemasu is not used for giving to oneself.',
                    ],
                    'examples' => [
                        ['ja' => 'わたしは サリさんに 花《はな》を あげました。', 'reading' => 'Watashi wa Sari-san ni hana o agemashita.', 'id' => 'Saya memberi bunga kepada Sari.', 'en' => 'I gave Sari flowers.'],
                        ['ja' => 'リナさんに 本《ほん》を 貸《か》します。', 'reading' => 'Rina-san ni hon o kashimasu.', 'id' => 'Saya meminjamkan buku kepada Rina.', 'en' => 'I will lend Rina a book.'],
                        ['ja' => 'たなかさんに 日本語《にほんご》を 教《おし》えます。', 'reading' => 'Tanaka-san ni nihongo o oshiemasu.', 'id' => 'Saya mengajari Tanaka bahasa Jepang.', 'en' => 'I teach Mr./Ms. Tanaka Japanese.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Kado Natal',
                        'title_en' => 'A Christmas present',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'クリスマスに 何《なに》を あげますか。', 'reading' => 'Kurisumasu ni nani o agemasu ka.', 'id' => 'Waktu Natal mau memberi apa?', 'en' => 'What will you give for Christmas?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'サリさんに シャツを あげます。', 'reading' => 'Sari-san ni shatsu o agemasu.', 'id' => 'Saya mau memberi kemeja untuk Sari.', 'en' => 'I will give Sari a shirt.'],
                            ['speaker' => 'リナ', 'ja' => 'いいですね。わたしは 花《はな》を 送《おく》ります。', 'reading' => 'Ii desu ne. Watashi wa hana o okurimasu.', 'id' => 'Bagus. Kalau saya mengirim bunga.', 'en' => 'Nice. I am sending flowers.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KB (orang) に もらいます・借《か》ります・習《なら》います',
                'title_en' => 'KB (person) ni moraimasu / karimasu / naraimasu',
                'pattern' => '(人)に 〜を もらいます',
                'payload' => [
                    'explanation_id' => 'Kelompok kata kerja "menerima" — もらいます, 借ります, 習います — juga menandai lawan dengan に, tetapi arah perbuatannya masuk ke arah penutur. Jadi に di sini berarti "dari". Kalau pemberinya berupa lembaga (sekolah, perusahaan), lebih lazim dipakai から.',
                    'explanation_en' => 'The "receiving" group — moraimasu, karimasu, naraimasu — also marks the other party with に, but the action moves inward, toward the speaker. So に here means "from". When the giver is an organisation (a school, a company), から is more usual.',
                    'notes_id' => [
                        'Bandingkan pasangannya: あげます⇄もらいます, 貸します⇄借ります, 教えます⇄習います.',
                        'Contoh dengan から: 会社《かいしゃ》から お金《かね》を もらいます.',
                    ],
                    'notes_en' => [
                        'Compare the pairs: agemasu/moraimasu, kashimasu/karimasu, oshiemasu/naraimasu.',
                        'With kara: kaisha kara okane o moraimasu.',
                    ],
                    'examples' => [
                        ['ja' => 'わたしは たなかさんに 年賀状《ねんがじょう》を もらいました。', 'reading' => 'Watashi wa Tanaka-san ni nengajou o moraimashita.', 'id' => 'Saya menerima kartu tahun baru dari Tanaka.', 'en' => 'I received a New Year card from Mr./Ms. Tanaka.'],
                        ['ja' => '友達《ともだち》に お金《かね》を 借《か》りました。', 'reading' => 'Tomodachi ni okane o karimashita.', 'id' => 'Saya meminjam uang dari teman.', 'en' => 'I borrowed money from a friend.'],
                        ['ja' => 'リナさんに 日本語《にほんご》を 習《なら》います。', 'reading' => 'Rina-san ni nihongo o naraimasu.', 'id' => 'Saya belajar bahasa Jepang dari Rina.', 'en' => 'I learn Japanese from Rina.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Barang pinjaman',
                        'title_en' => 'Something borrowed',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'その 本《ほん》、きれいですね。', 'reading' => 'Sono hon, kirei desu ne.', 'id' => 'Buku itu bagus, ya.', 'en' => 'That book looks nice.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、リナさんに 借《か》りました。', 'reading' => 'Ee, Rina-san ni karimashita.', 'id' => 'Iya, saya pinjam dari Rina.', 'en' => 'Yes, I borrowed it from Rina.'],
                            ['speaker' => 'たなか', 'ja' => 'わたしも 借《か》りたいです。', 'reading' => 'Watashi mo karitai desu.', 'id' => 'Saya juga mau pinjam.', 'en' => 'I would like to borrow it too.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'じゃ、あした 貸《か》しますね。', 'reading' => 'Ja, ashita kashimasu ne.', 'id' => 'Kalau begitu besok saya pinjamkan.', 'en' => 'Then I will lend it to you tomorrow.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'もう KK ました',
                'title_en' => 'mou V-mashita',
                'pattern' => 'もう 〜ました',
                'payload' => [
                    'explanation_id' => 'もう berarti "sudah" dan dipakai bersama bentuk ました untuk menyatakan perbuatan yang selesai. Pertanyaan もう 〜ましたか dijawab はい、もう 〜ました bila sudah, dan いいえ、まだです bila belum. Perhatikan: jawaban "belum" TIDAK memakai bentuk ませんでした, karena perbuatannya masih mungkin dilakukan.',
                    'explanation_en' => 'mou means "already" and pairs with the -mashita form for a completed action. A question mou … mashita ka is answered hai, mou … mashita if done, and iie, mada desu if not. Note that "not yet" does NOT use -masen deshita, because the action may still happen.',
                    'notes_id' => [
                        'まだです = belum (dan masih akan dilakukan).',
                        '〜ませんでした dipakai kalau perbuatan itu memang tidak jadi dilakukan sama sekali.',
                    ],
                    'notes_en' => [
                        'mada desu = not yet (and it is still to come).',
                        '-masen deshita is for an action that simply did not happen at all.',
                    ],
                    'examples' => [
                        ['ja' => 'もう 荷物《にもつ》を 送《おく》りましたか。', 'reading' => 'Mou nimotsu o okurimashita ka.', 'id' => 'Barangnya sudah dikirim?', 'en' => 'Have you sent the luggage yet?'],
                        ['ja' => 'はい、もう 送《おく》りました。', 'reading' => 'Hai, mou okurimashita.', 'id' => 'Ya, sudah saya kirim.', 'en' => 'Yes, I have already sent it.'],
                        ['ja' => 'いいえ、まだです。', 'reading' => 'Iie, mada desu.', 'id' => 'Belum.', 'en' => 'No, not yet.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Sudah atau belum',
                        'title_en' => 'Done or not yet',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'もう 昼《ひる》ごはんを 食《た》べましたか。', 'reading' => 'Mou hirugohan o tabemashita ka.', 'id' => 'Sudah makan siang?', 'en' => 'Have you had lunch yet?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、まだです。', 'reading' => 'Iie, mada desu.', 'id' => 'Belum.', 'en' => 'Not yet.'],
                            ['speaker' => 'リナ', 'ja' => 'じゃ、いっしょに 食《た》べませんか。', 'reading' => 'Ja, issho ni tabemasen ka.', 'id' => 'Kalau begitu, makan bareng yuk?', 'en' => 'Then shall we eat together?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、行《い》きましょう。', 'reading' => 'Ee, ikimashou.', 'id' => 'Ayo.', 'en' => 'Yes, let us go.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 8 — 7 poin
    // ------------------------------------------------------------------

    private function lesson8BaseCards(): array
    {
        return [
            [
                'title_id' => 'Dua jenis kata sifat: い dan な',
                'title_en' => 'The two adjective types: i and na',
                'pattern' => 'KB は 形容詞です',
                'payload' => [
                    'explanation_id' => 'Bahasa Jepang punya dua jenis kata sifat. Kata sifat-い berakhiran い pada bentuk kamusnya (大きい, 新しい, 高い). Kata sifat-な tidak berakhiran い dan memerlukan な ketika menerangkan kata benda (きれい, 静か, 有名). Sebagai predikat, keduanya sama-sama diikuti です.',
                    'explanation_en' => 'Japanese has two kinds of adjectives. i-adjectives end in い in their dictionary form (ookii, atarashii, takai). na-adjectives do not, and need な when they modify a noun (kirei, shizuka, yuumei). As a predicate, both simply take です.',
                    'notes_id' => [
                        'きれい dan 有名 berakhir bunyi い tetapi tetap kata sifat-な — hafalkan sebagai pengecualian.',
                        'です di sini hanya penanda sopan, bukan "adalah".',
                    ],
                    'notes_en' => [
                        'kirei and yuumei end in the sound i but are still na-adjectives — memorise them as exceptions.',
                        'desu here is only a politeness marker, not "is".',
                    ],
                    'examples' => [
                        ['ja' => 'この 町《まち》は 静《しず》かです。', 'reading' => 'Kono machi wa shizuka desu.', 'id' => 'Kota ini tenang.', 'en' => 'This town is quiet.'],
                        ['ja' => 'この 本《ほん》は 面白《おもしろ》いです。', 'reading' => 'Kono hon wa omoshiroi desu.', 'id' => 'Buku ini menarik.', 'en' => 'This book is interesting.'],
                        ['ja' => '富士山《ふじさん》は 有名《ゆうめい》です。', 'reading' => 'Fujisan wa yuumei desu.', 'id' => 'Gunung Fuji terkenal.', 'en' => 'Mt. Fuji is famous.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Kesan pertama',
                        'title_en' => 'First impressions',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '日本《にほん》の 生活《せいかつ》は どうですか。', 'reading' => 'Nihon no seikatsu wa dou desu ka.', 'id' => 'Bagaimana kehidupan di Jepang?', 'en' => 'How is life in Japan?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'とても 楽《たの》しいです。', 'reading' => 'Totemo tanoshii desu.', 'id' => 'Sangat menyenangkan.', 'en' => 'Very enjoyable.'],
                            ['speaker' => 'たなか', 'ja' => '町《まち》は にぎやかですか。', 'reading' => 'Machi wa nigiyaka desu ka.', 'id' => 'Kotanya ramai?', 'en' => 'Is the town lively?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、とても にぎやかです。', 'reading' => 'Ee, totemo nigiyaka desu.', 'id' => 'Ya, sangat ramai.', 'en' => 'Yes, very lively.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'Kata sifat + KB',
                'title_en' => 'Adjective + noun',
                'pattern' => 'い形容詞＋KB ／ な形容詞な＋KB',
                'payload' => [
                    'explanation_id' => 'Kata sifat diletakkan langsung di depan kata benda yang diterangkan. Kata sifat-い dipakai apa adanya (新しい 車《くるま》), sedangkan kata sifat-な menambahkan な di antaranya (きれいな 花《はな》). Inilah satu-satunya tempat perbedaan kedua jenis itu benar-benar terlihat.',
                    'explanation_en' => 'An adjective goes directly in front of the noun it describes. An i-adjective is used as it is (atarashii kuruma), while a na-adjective inserts な (kirei na hana). This is the one place where the difference between the two types really shows.',
                    'notes_id' => [
                        'Dengan kata tunjuk: この 大《おお》きい かばん.',
                        'いい bersifat tidak beraturan; bentuk lainnya memakai よ〜 (よくないです).',
                    ],
                    'notes_en' => [
                        'With a demonstrative: kono ookii kaban.',
                        'ii is irregular; its other forms are built on yo- (yokunai desu).',
                    ],
                    'examples' => [
                        ['ja' => '新《あたら》しい パソコンを 買《か》いました。', 'reading' => 'Atarashii pasokon o kaimashita.', 'id' => 'Saya membeli komputer baru.', 'en' => 'I bought a new computer.'],
                        ['ja' => 'きれいな 花《はな》ですね。', 'reading' => 'Kirei na hana desu ne.', 'id' => 'Bunganya cantik, ya.', 'en' => 'What lovely flowers.'],
                        ['ja' => 'あそこに 高《たか》い ビルが あります。', 'reading' => 'Asoko ni takai biru ga arimasu.', 'id' => 'Di sana ada gedung tinggi.', 'en' => 'There is a tall building over there.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Memilih kemeja',
                        'title_en' => 'Choosing a shirt',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'この 白《しろ》い シャツは どうですか。', 'reading' => 'Kono shiroi shatsu wa dou desu ka.', 'id' => 'Kemeja putih ini bagaimana?', 'en' => 'How about this white shirt?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'すてきですが、ちょっと 高《たか》いです。', 'reading' => 'Suteki desu ga, chotto takai desu.', 'id' => 'Bagus, tapi agak mahal.', 'en' => 'It is lovely, but a bit expensive.'],
                            ['speaker' => 'サリ', 'ja' => 'じゃ、あの 青《あお》い シャツは？', 'reading' => 'Ja, ano aoi shatsu wa?', 'id' => 'Kalau kemeja biru itu?', 'en' => 'Then what about that blue one?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'それは 安《やす》いですね。', 'reading' => 'Sore wa yasui desu ne.', 'id' => 'Yang itu murah, ya.', 'en' => 'That one is cheap.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'Bentuk negatif kata sifat',
                'title_en' => 'Negative forms of adjectives',
                'pattern' => '〜くないです ／ 〜じゃありません',
                'payload' => [
                    'explanation_id' => 'Kata sifat-い menjadi negatif dengan mengganti い menjadi くないです: 高い → 高くないです. Kata sifat-な mengikuti pola kata benda: じゃありません (atau ではありません yang lebih formal). Bentuk lampau: 〜かったです dan 〜くなかったです untuk kata sifat-い; でした dan じゃありませんでした untuk kata sifat-な.',
                    'explanation_en' => 'An i-adjective turns negative by swapping い for kunai desu: takai becomes takakunai desu. A na-adjective follows the noun pattern: ja arimasen (or the stiffer dewa arimasen). Past forms: -katta desu and -kunakatta desu for i-adjectives; deshita and ja arimasen deshita for na-adjectives.',
                    'notes_id' => [
                        'いい tidak beraturan: いい → よくないです, よかったです.',
                        '〜くないです bisa juga diucapkan 〜くありません (sedikit lebih formal).',
                    ],
                    'notes_en' => [
                        'ii is irregular: ii becomes yokunai desu, yokatta desu.',
                        '-kunai desu may also be said -ku arimasen (slightly more formal).',
                    ],
                    'examples' => [
                        ['ja' => 'この 料理《りょうり》は 辛《から》くないです。', 'reading' => 'Kono ryouri wa karakunai desu.', 'id' => 'Masakan ini tidak pedas.', 'en' => 'This dish is not spicy.'],
                        ['ja' => 'あの 店《みせ》は 有名《ゆうめい》じゃありません。', 'reading' => 'Ano mise wa yuumei ja arimasen.', 'id' => 'Toko itu tidak terkenal.', 'en' => 'That shop is not famous.'],
                        ['ja' => 'きのうは 暑《あつ》くなかったです。', 'reading' => 'Kinou wa atsukunakatta desu.', 'id' => 'Kemarin tidak panas.', 'en' => 'It was not hot yesterday.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Bagaimana ujiannya',
                        'title_en' => 'How was the test',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'テストは 難《むずか》しかったですか。', 'reading' => 'Tesuto wa muzukashikatta desu ka.', 'id' => 'Ujiannya sulit?', 'en' => 'Was the test difficult?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、難《むずか》しくなかったです。', 'reading' => 'Iie, muzukashikunakatta desu.', 'id' => 'Tidak, tidak sulit.', 'en' => 'No, it was not.'],
                            ['speaker' => 'リナ', 'ja' => 'よかったですね。', 'reading' => 'Yokatta desu ne.', 'id' => 'Syukurlah.', 'en' => 'That is good.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'とても ／ あまり',
                'title_en' => 'totemo / amari',
                'pattern' => 'とても〜です ／ あまり〜ません',
                'payload' => [
                    'explanation_id' => 'とても menguatkan kata sifat dan hanya dipakai pada kalimat positif. あまり berarti "tidak begitu" dan HARUS diikuti bentuk negatif; memakainya dengan kalimat positif adalah kesalahan yang sering terjadi.',
                    'explanation_en' => 'totemo intensifies an adjective and is used only in affirmative sentences. amari means "not very" and MUST be followed by a negative; pairing it with an affirmative is a common mistake.',
                    'notes_id' => [
                        'あまり juga bisa dipakai dengan kata kerja: あまり 食《た》べません.',
                        'Untuk penyangkalan total dipakai 全然《ぜんぜん》〜ません.',
                    ],
                    'notes_en' => [
                        'amari also works with verbs: amari tabemasen.',
                        'For a flat denial, use zenzen … masen.',
                    ],
                    'examples' => [
                        ['ja' => 'この 町《まち》は とても にぎやかです。', 'reading' => 'Kono machi wa totemo nigiyaka desu.', 'id' => 'Kota ini sangat ramai.', 'en' => 'This town is very lively.'],
                        ['ja' => 'この 本《ほん》は あまり 面白《おもしろ》くないです。', 'reading' => 'Kono hon wa amari omoshirokunai desu.', 'id' => 'Buku ini tidak begitu menarik.', 'en' => 'This book is not very interesting.'],
                        ['ja' => 'あの 店《みせ》は あまり 有名《ゆうめい》じゃありません。', 'reading' => 'Ano mise wa amari yuumei ja arimasen.', 'id' => 'Toko itu tidak begitu terkenal.', 'en' => 'That shop is not very famous.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Soal rasa',
                        'title_en' => 'About the taste',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'その 食《た》べ物《もの》は おいしいですか。', 'reading' => 'Sono tabemono wa oishii desu ka.', 'id' => 'Makanan itu enak?', 'en' => 'Is that food good?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、とても おいしいです。', 'reading' => 'Ee, totemo oishii desu.', 'id' => 'Ya, sangat enak.', 'en' => 'Yes, very good.'],
                            ['speaker' => 'たなか', 'ja' => '高《たか》いですか。', 'reading' => 'Takai desu ka.', 'id' => 'Mahal?', 'en' => 'Is it expensive?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、あまり 高《たか》くないです。', 'reading' => 'Iie, amari takakunai desu.', 'id' => 'Tidak, tidak begitu mahal.', 'en' => 'No, not very.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KB は どうですか',
                'title_en' => 'KB wa dou desu ka',
                'pattern' => 'KBは どうですか',
                'payload' => [
                    'explanation_id' => 'どうですか menanyakan kesan atau pendapat lawan bicara tentang sesuatu yang ia alami sendiri. Jawabannya berupa kata sifat. Pola ini juga dipakai untuk menawarkan sesuatu dengan halus: "bagaimana kalau …?".',
                    'explanation_en' => 'dou desu ka asks for the listener\'s impression of something they have experienced themselves. The answer is an adjective. The same pattern gently offers something: "how about …?".',
                    'notes_id' => [
                        'Jawaban aman kalau ragu: そうですね…',
                        'Menawarkan: コーヒーは どうですか = bagaimana kalau kopi?',
                    ],
                    'notes_en' => [
                        'A safe hedge if unsure: sou desu ne…',
                        'Offering: koohii wa dou desu ka = how about coffee?',
                    ],
                    'examples' => [
                        ['ja' => '日本《にほん》の 食《た》べ物《もの》は どうですか。', 'reading' => 'Nihon no tabemono wa dou desu ka.', 'id' => 'Bagaimana makanan Jepang?', 'en' => 'How is Japanese food?'],
                        ['ja' => 'とても おいしいです。', 'reading' => 'Totemo oishii desu.', 'id' => 'Sangat enak.', 'en' => 'Very good.'],
                        ['ja' => 'お茶《ちゃ》は どうですか。', 'reading' => 'Ocha wa dou desu ka.', 'id' => 'Bagaimana kalau teh?', 'en' => 'How about some tea?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menanyakan kesan',
                        'title_en' => 'Asking for an impression',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => '新《あたら》しい 仕事《しごと》は どうですか。', 'reading' => 'Atarashii shigoto wa dou desu ka.', 'id' => 'Bagaimana pekerjaan barunya?', 'en' => 'How is the new job?'],
                            ['speaker' => 'サリ', 'ja' => 'そうですね…、忙《いそが》しいですが、楽《たの》しいです。', 'reading' => 'Sou desu ne…, isogashii desu ga, tanoshii desu.', 'id' => 'Hmm… sibuk, tapi menyenangkan.', 'en' => 'Well… busy, but enjoyable.'],
                            ['speaker' => 'リナ', 'ja' => '会社《かいしゃ》の 人《ひと》は どうですか。', 'reading' => 'Kaisha no hito wa dou desu ka.', 'id' => 'Orang-orang di kantornya bagaimana?', 'en' => 'And the people at the company?'],
                            ['speaker' => 'サリ', 'ja' => 'みんな 親切《しんせつ》です。', 'reading' => 'Minna shinsetsu desu.', 'id' => 'Semuanya baik hati.', 'en' => 'Everyone is kind.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KB1 は どんな KB2 ですか',
                'title_en' => 'KB1 wa donna KB2 desu ka',
                'pattern' => 'KB1は どんな KB2ですか',
                'payload' => [
                    'explanation_id' => 'どんな selalu diikuti kata benda dan menanyakan sifat atau jenisnya: "KB yang bagaimana?". Berbeda dengan どうですか yang menanyakan kesan pribadi, どんな meminta penjelasan tentang benda atau orangnya. Jawabannya berupa kata sifat + kata benda, atau kata sifat sebagai predikat.',
                    'explanation_en' => 'donna is always followed by a noun and asks about its quality or kind: "what sort of …?". Unlike dou desu ka, which asks for a personal impression, donna asks for a description of the thing or person. The answer is an adjective plus noun, or an adjective as the predicate.',
                    'notes_id' => [
                        'どんな tidak pernah berdiri sendiri — selalu ada kata benda sesudahnya.',
                        'Bandingkan: 町《まち》は どうですか (kesanmu?) vs どんな 町《まち》ですか (kotanya seperti apa?).',
                    ],
                    'notes_en' => [
                        'donna never stands alone — a noun always follows it.',
                        'Compare: machi wa dou desu ka (your impression?) vs donna machi desu ka (what is the town like?).',
                    ],
                    'examples' => [
                        ['ja' => '京都《きょうと》は どんな 町《まち》ですか。', 'reading' => 'Kyouto wa donna machi desu ka.', 'id' => 'Kyoto kota yang bagaimana?', 'en' => 'What kind of town is Kyoto?'],
                        ['ja' => '静《しず》かで きれいな 町《まち》です。', 'reading' => 'Shizuka de kirei na machi desu.', 'id' => 'Kota yang tenang dan indah.', 'en' => 'A quiet, beautiful town.'],
                        ['ja' => 'たなかさんは どんな 人《ひと》ですか。', 'reading' => 'Tanaka-san wa donna hito desu ka.', 'id' => 'Tanaka orang yang bagaimana?', 'en' => 'What sort of person is Mr./Ms. Tanaka?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Bertanya tentang kota',
                        'title_en' => 'Asking about a town',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'サリさんの 町《まち》は どんな 町《まち》ですか。', 'reading' => 'Sari-san no machi wa donna machi desu ka.', 'id' => 'Kota Sari itu kota yang bagaimana?', 'en' => 'What is your town like, Sari?'],
                            ['speaker' => 'サリ', 'ja' => '小《ちい》さい 町《まち》です。でも とても きれいです。', 'reading' => 'Chiisai machi desu. Demo totemo kirei desu.', 'id' => 'Kota kecil. Tapi sangat indah.', 'en' => 'A small town. But very beautiful.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'にぎやかですか。', 'reading' => 'Nigiyaka desu ka.', 'id' => 'Ramai?', 'en' => 'Is it lively?'],
                            ['speaker' => 'サリ', 'ja' => 'いいえ、あまり にぎやかじゃありません。', 'reading' => 'Iie, amari nigiyaka ja arimasen.', 'id' => 'Tidak, tidak begitu ramai.', 'en' => 'No, not very.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'そして ／ 〜が、〜',
                'title_en' => 'soshite / … ga, …',
                'pattern' => '〜です。そして 〜です。／ 〜ですが、〜です。',
                'payload' => [
                    'explanation_id' => 'そして menyambung dua kalimat yang searah, misalnya dua sifat yang sama-sama positif. Sebaliknya が menghubungkan dua kalimat yang isinya berlawanan, seperti "tetapi" dalam bahasa Indonesia; が diletakkan di akhir kalimat pertama, bukan di awal kalimat kedua.',
                    'explanation_en' => 'soshite joins two sentences that point the same way, for example two equally positive qualities. が, by contrast, links two sentences whose content contrasts, like "but"; が sits at the end of the first clause, not at the start of the second.',
                    'notes_id' => [
                        'が di sini berbeda dengan partikel subjek が pada Pelajaran 9.',
                        'Dalam percakapan, kalimat baru sering dimulai でも (tetapi) sebagai ganti が.',
                    ],
                    'notes_en' => [
                        'This が is different from the subject particle が in Lesson 9.',
                        'In conversation a new sentence often opens with demo (but) instead of using が.',
                    ],
                    'examples' => [
                        ['ja' => 'この 部屋《へや》は 広《ひろ》いです。そして 静《しず》かです。', 'reading' => 'Kono heya wa hiroi desu. Soshite shizuka desu.', 'id' => 'Kamar ini luas. Dan tenang.', 'en' => 'This room is spacious. And quiet.'],
                        ['ja' => 'この パソコンは 便利《べんり》ですが、高《たか》いです。', 'reading' => 'Kono pasokon wa benri desu ga, takai desu.', 'id' => 'Komputer ini praktis, tetapi mahal.', 'en' => 'This computer is handy, but expensive.'],
                        ['ja' => '日本語《にほんご》は 難《むずか》しいですが、面白《おもしろ》いです。', 'reading' => 'Nihongo wa muzukashii desu ga, omoshiroi desu.', 'id' => 'Bahasa Jepang sulit, tetapi menarik.', 'en' => 'Japanese is difficult, but interesting.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menimbang dua sisi',
                        'title_en' => 'Weighing two sides',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'その ケータイは どうですか。', 'reading' => 'Sono keetai wa dou desu ka.', 'id' => 'HP itu bagaimana?', 'en' => 'How is that phone?'],
                            ['speaker' => 'ワヒュ', 'ja' => '小《ちい》さいです。そして 軽《かる》いです。', 'reading' => 'Chiisai desu. Soshite karui desu.', 'id' => 'Kecil. Dan ringan.', 'en' => 'It is small. And light.'],
                            ['speaker' => 'たなか', 'ja' => '安《やす》いですか。', 'reading' => 'Yasui desu ka.', 'id' => 'Murah?', 'en' => 'Is it cheap?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、便利《べんり》ですが、高《たか》いです。', 'reading' => 'Iie, benri desu ga, takai desu.', 'id' => 'Tidak, praktis tapi mahal.', 'en' => 'No — handy, but expensive.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 9 — 5 poin
    // ------------------------------------------------------------------

    private function lesson9Cards(): array
    {
        return [
            [
                'title_id' => 'KB が あります・わかります・好《す》きです',
                'title_en' => 'KB ga arimasu / wakarimasu / suki desu',
                'pattern' => 'KBが あります／わかります／好きです',
                'payload' => [
                    'explanation_id' => 'Sekelompok kata — あります (punya/ada), わかります (mengerti), 好きです, 嫌いです, 上手です, 下手です — menandai sasarannya dengan が, bukan を. Alasannya: dalam bahasa Jepang hal-hal ini dianggap keadaan yang dialami, bukan perbuatan yang dikerjakan. Orang yang mengalaminya tetap menjadi topik dengan は.',
                    'explanation_en' => 'A set of words — arimasu (have/exist), wakarimasu (understand), suki desu, kirai desu, jouzu desu, heta desu — marks their target with が, not を. The reason: Japanese treats these as states you are in, not actions you perform. The person experiencing them stays the topic, with は.',
                    'notes_id' => [
                        'Susunan lazim: (orang)は (sasaran)が 好《す》きです.',
                        'Dalam kalimat negatif が sering berubah menjadi は: 日本語《にほんご》は わかりません.',
                    ],
                    'notes_en' => [
                        'Usual order: (person) wa (target) ga suki desu.',
                        'In negatives が often shifts to は: nihongo wa wakarimasen.',
                    ],
                    'examples' => [
                        ['ja' => 'わたしは 日本語《にほんご》が わかります。', 'reading' => 'Watashi wa nihongo ga wakarimasu.', 'id' => 'Saya mengerti bahasa Jepang.', 'en' => 'I understand Japanese.'],
                        ['ja' => 'サリさんは 音楽《おんがく》が 好《す》きです。', 'reading' => 'Sari-san wa ongaku ga suki desu.', 'id' => 'Sari suka musik.', 'en' => 'Sari likes music.'],
                        ['ja' => 'わたしは 時間《じかん》が ありません。', 'reading' => 'Watashi wa jikan ga arimasen.', 'id' => 'Saya tidak punya waktu.', 'en' => 'I have no time.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Soal selera',
                        'title_en' => 'About tastes',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'ワヒュさんは スポーツが 好《す》きですか。', 'reading' => 'Wahyu-san wa supootsu ga suki desu ka.', 'id' => 'Wahyu suka olahraga?', 'en' => 'Do you like sports, Wahyu?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい、好《す》きです。野球《やきゅう》が 好《す》きです。', 'reading' => 'Hai, suki desu. Yakyuu ga suki desu.', 'id' => 'Ya, suka. Saya suka baseball.', 'en' => 'Yes. I like baseball.'],
                            ['speaker' => 'リナ', 'ja' => '上手《じょうず》ですか。', 'reading' => 'Jouzu desu ka.', 'id' => 'Pandai main?', 'en' => 'Are you good at it?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、あまり 上手《じょうず》じゃありません。', 'reading' => 'Iie, amari jouzu ja arimasen.', 'id' => 'Tidak, tidak begitu pandai.', 'en' => 'No, not very.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'どんな KB (menanyakan kesukaan)',
                'title_en' => 'donna KB (asking about preferences)',
                'pattern' => 'どんな KBが 好きですか',
                'payload' => [
                    'explanation_id' => 'どんな dipakai lagi di sini untuk menanyakan jenis yang disukai: "KB macam apa yang kamu suka?". Jawabannya menyebut jenis yang lebih sempit, bukan menilai. Pola ini sangat sering muncul saat berkenalan atau berbasa-basi.',
                    'explanation_en' => 'donna appears again here to ask which kind someone likes: "what sort of … do you like?". The answer names a narrower category rather than passing judgement. The pattern comes up constantly in small talk and introductions.',
                    'notes_id' => [
                        'Jawaban singkat cukup: クラシックが 好《す》きです.',
                        'Kalau tidak punya kesukaan khusus: 特《とく》に ありません.',
                    ],
                    'notes_en' => [
                        'A short answer is enough: kurashikku ga suki desu.',
                        'If you have no particular favourite: toku ni arimasen.',
                    ],
                    'examples' => [
                        ['ja' => 'どんな 音楽《おんがく》が 好《す》きですか。', 'reading' => 'Donna ongaku ga suki desu ka.', 'id' => 'Suka musik yang seperti apa?', 'en' => 'What kind of music do you like?'],
                        ['ja' => 'ジャズが 好《す》きです。', 'reading' => 'Jazu ga suki desu.', 'id' => 'Saya suka jaz.', 'en' => 'I like jazz.'],
                        ['ja' => 'どんな スポーツが 好《す》きですか。', 'reading' => 'Donna supootsu ga suki desu ka.', 'id' => 'Suka olahraga apa?', 'en' => 'What sports do you like?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mengobrol soal musik',
                        'title_en' => 'Chatting about music',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'どんな 音楽《おんがく》が 好《す》きですか。', 'reading' => 'Donna ongaku ga suki desu ka.', 'id' => 'Suka musik seperti apa?', 'en' => 'What kind of music do you like?'],
                            ['speaker' => 'サリ', 'ja' => 'クラシックが 好《す》きです。', 'reading' => 'Kurashikku ga suki desu.', 'id' => 'Saya suka musik klasik.', 'en' => 'I like classical music.'],
                            ['speaker' => 'たなか', 'ja' => 'じゃ、コンサートの チケットが ありますが…。', 'reading' => 'Ja, konsaato no chiketto ga arimasu ga….', 'id' => 'Kebetulan saya punya tiket konser…', 'en' => 'Then — I happen to have concert tickets…'],
                            ['speaker' => 'サリ', 'ja' => 'ほんとうですか。行《い》きます！', 'reading' => 'Hontou desu ka. Ikimasu!', 'id' => 'Benarkah? Saya mau ikut!', 'en' => 'Really? I will come!'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'よく・だいたい・たくさん・少《すこ》し・あまり・全然《ぜんぜん》',
                'title_en' => 'yoku / daitai / takusan / sukoshi / amari / zenzen',
                'pattern' => '(副詞) 〜ます／〜ません',
                'payload' => [
                    'explanation_id' => 'Kata keterangan ini menyatakan seberapa sering atau seberapa banyak. よく, だいたい, たくさん, 少し dipakai pada kalimat positif. Sementara あまり (tidak begitu) dan 全然 (sama sekali tidak) hanya boleh dipakai bersama bentuk negatif.',
                    'explanation_en' => 'These adverbs say how often or how much. yoku, daitai, takusan and sukoshi go with affirmative sentences. amari (not much) and zenzen (not at all) may only be used with a negative.',
                    'notes_id' => [
                        'Urutannya di depan kata kerja atau kata sifat yang diterangkan.',
                        'よく punya dua arti: "sering" dan "dengan baik" — konteks yang menentukan.',
                    ],
                    'notes_en' => [
                        'They go in front of the verb or adjective they modify.',
                        'yoku has two senses: "often" and "well" — context decides.',
                    ],
                    'examples' => [
                        ['ja' => 'わたしは よく 音楽《おんがく》を 聞《き》きます。', 'reading' => 'Watashi wa yoku ongaku o kikimasu.', 'id' => 'Saya sering mendengarkan musik.', 'en' => 'I often listen to music.'],
                        ['ja' => '漢字《かんじ》が 少《すこ》し わかります。', 'reading' => 'Kanji ga sukoshi wakarimasu.', 'id' => 'Saya mengerti sedikit kanji.', 'en' => 'I understand a little kanji.'],
                        ['ja' => 'お酒《さけ》は 全然《ぜんぜん》 飲《の》みません。', 'reading' => 'Osake wa zenzen nomimasen.', 'id' => 'Saya sama sekali tidak minum minuman keras.', 'en' => 'I do not drink alcohol at all.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Seberapa sering',
                        'title_en' => 'How often',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'カラオケに よく 行《い》きますか。', 'reading' => 'Karaoke ni yoku ikimasu ka.', 'id' => 'Sering ke karaoke?', 'en' => 'Do you often go to karaoke?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、あまり 行《い》きません。', 'reading' => 'Iie, amari ikimasen.', 'id' => 'Tidak, jarang.', 'en' => 'No, not very often.'],
                            ['speaker' => 'リナ', 'ja' => '歌《うた》は 好《す》きですか。', 'reading' => 'Uta wa suki desu ka.', 'id' => 'Suka menyanyi?', 'en' => 'Do you like singing?'],
                            ['speaker' => 'ワヒュ', 'ja' => '好《す》きですが、全然《ぜんぜん》 上手《じょうず》じゃありません。', 'reading' => 'Suki desu ga, zenzen jouzu ja arimasen.', 'id' => 'Suka, tapi sama sekali tidak pandai.', 'en' => 'I like it, but I am not good at all.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => '〜から (alasan)',
                'title_en' => '… kara (reason)',
                'pattern' => '〜から、〜',
                'payload' => [
                    'explanation_id' => 'から diletakkan di akhir kalimat yang menyatakan alasan, lalu diikuti akibat atau kesimpulannya. Jadi urutannya kebalikan dari bahasa Indonesia: alasan dulu, baru pernyataan utama. Alasan juga boleh berdiri sendiri sebagai jawaban dengan から di ujungnya.',
                    'explanation_en' => 'kara goes at the end of the clause giving the reason, and the result or conclusion follows. The order is the reverse of English: reason first, main statement second. A reason may also stand alone as an answer, with kara at its end.',
                    'notes_id' => [
                        'Jangan bingung dengan から Pelajaran 4 yang berarti "dari (waktu/tempat)".',
                        'Bentuk sopan tetap dipakai sebelum から: 忙《いそが》しいですから.',
                    ],
                    'notes_en' => [
                        'Do not confuse it with the kara of Lesson 4, meaning "from (a time/place)".',
                        'The polite form is kept before kara: isogashii desu kara.',
                    ],
                    'examples' => [
                        ['ja' => '時間《じかん》が ありませんから、行《い》きません。', 'reading' => 'Jikan ga arimasen kara, ikimasen.', 'id' => 'Karena tidak ada waktu, saya tidak pergi.', 'en' => 'I am not going, because I have no time.'],
                        ['ja' => 'あしたは 用事《ようじ》が ありますから、休《やす》みます。', 'reading' => 'Ashita wa youji ga arimasu kara, yasumimasu.', 'id' => 'Karena besok ada urusan, saya libur.', 'en' => 'I will take tomorrow off, as I have something on.'],
                        ['ja' => '約束《やくそく》が ありますから。', 'reading' => 'Yakusoku ga arimasu kara.', 'id' => 'Karena saya ada janji.', 'en' => 'Because I have an appointment.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Alasan tidak ikut',
                        'title_en' => 'A reason not to come',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '今晩《こんばん》 コンサートに 行《い》きませんか。', 'reading' => 'Konban konsaato ni ikimasen ka.', 'id' => 'Malam ini mau ke konser?', 'en' => 'Shall we go to the concert tonight?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません、約束《やくそく》が ありますから…。', 'reading' => 'Sumimasen, yakusoku ga arimasu kara….', 'id' => 'Maaf, saya ada janji…', 'en' => 'Sorry — I have an appointment…'],
                            ['speaker' => 'たなか', 'ja' => 'そうですか。残念《ざんねん》ですね。', 'reading' => 'Sou desu ka. Zannen desu ne.', 'id' => 'Oh begitu. Sayang sekali.', 'en' => 'I see. That is a pity.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'どうして／なぜ 〜か',
                'title_en' => 'doushite / naze … ka',
                'pattern' => 'どうして 〜か。…〜から。',
                'payload' => [
                    'explanation_id' => 'どうして (atau なぜ yang lebih formal) menanyakan alasan. Jawabannya selalu ditutup から. Dalam percakapan, jawaban singkat berupa alasan + から sudah lengkap dan tidak perlu mengulang kalimat pertanyaannya.',
                    'explanation_en' => 'doushite (or the more formal naze) asks why. The answer always ends in kara. In conversation a short answer — the reason plus kara — is complete on its own; there is no need to repeat the question.',
                    'notes_id' => [
                        'Kalau ditanya どうして, sangat tidak wajar menjawab tanpa から.',
                        'Untuk menunjukkan rasa heran, orang sering menambahkan どうしてですか.',
                    ],
                    'notes_en' => [
                        'Answering a doushite question without kara sounds very odd.',
                        'To show surprise, people often add doushite desu ka.',
                    ],
                    'examples' => [
                        ['ja' => 'どうして きのう 休《やす》みましたか。', 'reading' => 'Doushite kinou yasumimashita ka.', 'id' => 'Kenapa kemarin tidak masuk?', 'en' => 'Why did you take yesterday off?'],
                        ['ja' => '用事《ようじ》が ありましたから。', 'reading' => 'Youji ga arimashita kara.', 'id' => 'Karena ada urusan.', 'en' => 'Because I had something to do.'],
                        ['ja' => 'どうして 日本語《にほんご》を 習《なら》いますか。', 'reading' => 'Doushite nihongo o naraimasu ka.', 'id' => 'Kenapa belajar bahasa Jepang?', 'en' => 'Why are you learning Japanese?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menanyakan sebab',
                        'title_en' => 'Asking why',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'どうして 日本語《にほんご》を 習《なら》いますか。', 'reading' => 'Doushite nihongo o naraimasu ka.', 'id' => 'Kenapa belajar bahasa Jepang?', 'en' => 'Why are you learning Japanese?'],
                            ['speaker' => 'ワヒュ', 'ja' => '仕事《しごと》で 使《つか》いますから。', 'reading' => 'Shigoto de tsukaimasu kara.', 'id' => 'Karena dipakai untuk pekerjaan.', 'en' => 'Because I use it at work.'],
                            ['speaker' => 'リナ', 'ja' => '難《むずか》しいですか。', 'reading' => 'Muzukashii desu ka.', 'id' => 'Sulit?', 'en' => 'Is it hard?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、でも 面白《おもしろ》いですから、楽《たの》しいです。', 'reading' => 'Ee, demo omoshiroi desu kara, tanoshii desu.', 'id' => 'Ya, tapi karena menarik, jadi menyenangkan.', 'en' => 'Yes, but it is interesting, so it is fun.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 10 — 5 poin
    // ------------------------------------------------------------------

    private function lesson10BaseCards(): array
    {
        return [
            [
                'title_id' => 'あります ／ います',
                'title_en' => 'arimasu / imasu',
                'pattern' => 'KBが あります／います',
                'payload' => [
                    'explanation_id' => 'Dua kata kerja keberadaan yang dipilih menurut benda atau makhluknya. あります untuk benda mati dan hal-hal abstrak (buku, uang, janji). います untuk makhluk hidup yang bisa bergerak sendiri (orang, binatang). Pohon dan tumbuhan tidak bergerak, jadi memakai あります.',
                    'explanation_en' => 'Two existence verbs, chosen by what exists. arimasu covers inanimate things and abstract ones (books, money, appointments). imasu covers living things that move by themselves (people, animals). Trees and plants do not move, so they take arimasu.',
                    'notes_id' => [
                        'Yang ada ditandai が.',
                        'Bentuk negatif: ありません／いません.',
                    ],
                    'notes_en' => [
                        'The thing that exists is marked with が.',
                        'Negatives: arimasen / imasen.',
                    ],
                    'examples' => [
                        ['ja' => '箱《はこ》が あります。', 'reading' => 'Hako ga arimasu.', 'id' => 'Ada kotak.', 'en' => 'There is a box.'],
                        ['ja' => '犬《いぬ》が います。', 'reading' => 'Inu ga imasu.', 'id' => 'Ada anjing.', 'en' => 'There is a dog.'],
                        ['ja' => '男《おとこ》の 子《こ》が いますか。', 'reading' => 'Otoko no ko ga imasu ka.', 'id' => 'Apakah ada anak laki-laki?', 'en' => 'Is there a boy?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Isi kulkas',
                        'title_en' => 'What is in the fridge',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '冷蔵庫《れいぞうこ》に 何《なに》が ありますか。', 'reading' => 'Reizouko ni nani ga arimasu ka.', 'id' => 'Di kulkas ada apa?', 'en' => 'What is in the fridge?'],
                            ['speaker' => 'ワヒュ', 'ja' => '卵《たまご》と 牛乳《ぎゅうにゅう》が あります。', 'reading' => 'Tamago to gyuunyuu ga arimasu.', 'id' => 'Ada telur dan susu.', 'en' => 'There are eggs and milk.'],
                            ['speaker' => 'サリ', 'ja' => '野菜《やさい》は ありませんか。', 'reading' => 'Yasai wa arimasen ka.', 'id' => 'Sayur tidak ada?', 'en' => 'No vegetables?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、ありません。', 'reading' => 'Ee, arimasen.', 'id' => 'Tidak ada.', 'en' => 'No, none.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KB (tempat) に KB が あります／います',
                'title_en' => 'KB (place) ni KB ga arimasu / imasu',
                'pattern' => '(場所)に KBが あります／います',
                'payload' => [
                    'explanation_id' => 'Pola untuk menjelaskan APA yang ada di suatu tempat. Tempatnya ditandai に dan disebut lebih dulu, lalu benda atau orangnya dengan が. Perhatikan: に menandai keberadaan, berbeda dengan で pada Pelajaran 6 yang menandai tempat berlangsungnya perbuatan.',
                    'explanation_en' => 'The pattern for saying WHAT exists at a place. The place is marked with に and comes first, then the thing or person with が. Note that に marks existence, unlike the で of Lesson 6, which marks where an action happens.',
                    'notes_id' => [
                        'Tanya: (場所)に 何《なに》が ありますか／だれが いますか.',
                        'Bandingkan: 公園《こうえん》で 遊《あそ》びます (perbuatan) vs 公園《こうえん》に 木《き》が あります (keberadaan).',
                    ],
                    'notes_en' => [
                        'To ask: (place) ni nani ga arimasu ka / dare ga imasu ka.',
                        'Compare: kouen de asobimasu (action) vs kouen ni ki ga arimasu (existence).',
                    ],
                    'examples' => [
                        ['ja' => '公園《こうえん》に 木《き》が あります。', 'reading' => 'Kouen ni ki ga arimasu.', 'id' => 'Di taman ada pohon.', 'en' => 'There are trees in the park.'],
                        ['ja' => '部屋《へや》に 男《おとこ》の 人《ひと》が います。', 'reading' => 'Heya ni otoko no hito ga imasu.', 'id' => 'Di kamar ada seorang pria.', 'en' => 'There is a man in the room.'],
                        ['ja' => 'あそこに ATMが あります。', 'reading' => 'Asoko ni eetiiemu ga arimasu.', 'id' => 'Di sana ada ATM.', 'en' => 'There is an ATM over there.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mencari ATM',
                        'title_en' => 'Looking for an ATM',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません、この 近《ちか》くに ATMが ありますか。', 'reading' => 'Sumimasen, kono chikaku ni eetiiemu ga arimasu ka.', 'id' => 'Permisi, di dekat sini ada ATM?', 'en' => 'Excuse me, is there an ATM near here?'],
                            ['speaker' => 'たなか', 'ja' => 'ええ、コンビニに あります。', 'reading' => 'Ee, konbini ni arimasu.', 'id' => 'Ada, di minimarket.', 'en' => 'Yes, in the convenience store.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'コンビニは どこですか。', 'reading' => 'Konbini wa doko desu ka.', 'id' => 'Minimarketnya di mana?', 'en' => 'Where is the convenience store?'],
                            ['speaker' => 'たなか', 'ja' => 'あの ビルの 中《なか》です。', 'reading' => 'Ano biru no naka desu.', 'id' => 'Di dalam gedung itu.', 'en' => 'Inside that building.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KB は (tempat) に あります／います',
                'title_en' => 'KB wa (place) ni arimasu / imasu',
                'pattern' => 'KBは (場所)に あります／います',
                'payload' => [
                    'explanation_id' => 'Pola kebalikannya: ketika bendanya sudah diketahui dan yang ditanyakan justru DI MANA benda itu berada. Bendanya menjadi topik dengan は, lalu tempatnya dengan に. Bandingkan dengan pola sebelumnya — isinya sama, yang berubah hanya informasi mana yang dianggap baru.',
                    'explanation_en' => 'The mirror pattern: when the thing is already known and what you want is WHERE it is. The thing becomes the topic with は, then the place takes に. Compare it with the previous pattern — the content is the same; only which part counts as new information changes.',
                    'notes_id' => [
                        'Tanya: KBは どこに ありますか (atau lebih singkat: KBは どこですか).',
                        'Untuk orang: たなかさんは どこに いますか.',
                    ],
                    'notes_en' => [
                        'To ask: KB wa doko ni arimasu ka (or more briefly, KB wa doko desu ka).',
                        'For people: Tanaka-san wa doko ni imasu ka.',
                    ],
                    'examples' => [
                        ['ja' => '電池《でんち》は 箱《はこ》の 中《なか》に あります。', 'reading' => 'Denchi wa hako no naka ni arimasu.', 'id' => 'Baterainya ada di dalam kotak.', 'en' => 'The batteries are in the box.'],
                        ['ja' => '猫《ねこ》は ベッドの 下《した》に います。', 'reading' => 'Neko wa beddo no shita ni imasu.', 'id' => 'Kucingnya ada di bawah tempat tidur.', 'en' => 'The cat is under the bed.'],
                        ['ja' => 'たなかさんは どこに いますか。', 'reading' => 'Tanaka-san wa doko ni imasu ka.', 'id' => 'Tanaka ada di mana?', 'en' => 'Where is Mr./Ms. Tanaka?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mencari kunci',
                        'title_en' => 'Looking for the key',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => '鍵《かぎ》は どこに ありますか。', 'reading' => 'Kagi wa doko ni arimasu ka.', 'id' => 'Kuncinya ada di mana?', 'en' => 'Where is the key?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'テーブルの 上《うえ》に あります。', 'reading' => 'Teeburu no ue ni arimasu.', 'id' => 'Ada di atas meja.', 'en' => 'On the table.'],
                            ['speaker' => 'サリ', 'ja' => 'ありません…。', 'reading' => 'Arimasen….', 'id' => 'Tidak ada…', 'en' => 'It is not there…'],
                            ['speaker' => 'ワヒュ', 'ja' => 'じゃ、棚《たな》の 中《なか》です。', 'reading' => 'Ja, tana no naka desu.', 'id' => 'Kalau begitu di dalam lemari.', 'en' => 'Then it is in the shelf.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KB1 や KB2 (など)',
                'title_en' => 'KB1 ya KB2 (nado)',
                'pattern' => 'KB1や KB2(など)',
                'payload' => [
                    'explanation_id' => 'や menyambung kata benda seperti と, tetapi hanya menyebut beberapa contoh dari yang ada — bukan daftar lengkap. Sering ditutup など untuk menegaskan "dan lain-lain". と, sebaliknya, berarti daftarnya lengkap.',
                    'explanation_en' => 'や joins nouns like と, but it names only a few examples out of more — not the full list. It is often closed with nado to stress "and so on". と, by contrast, means the list is complete.',
                    'notes_id' => [
                        'Bandingkan: 犬《いぬ》と 猫《ねこ》が います (hanya itu) vs 犬《いぬ》や 猫《ねこ》が います (antara lain).',
                        'など diletakkan setelah kata benda terakhir.',
                    ],
                    'notes_en' => [
                        'Compare: inu to neko ga imasu (only those) vs inu ya neko ga imasu (among others).',
                        'nado goes after the last noun.',
                    ],
                    'examples' => [
                        ['ja' => '箱《はこ》の 中《なか》に 電池《でんち》や 紙《かみ》が あります。', 'reading' => 'Hako no naka ni denchi ya kami ga arimasu.', 'id' => 'Di dalam kotak ada baterai, kertas, dan lain-lain.', 'en' => 'In the box there are batteries, paper and so on.'],
                        ['ja' => '公園《こうえん》に 犬《いぬ》や 猫《ねこ》が います。', 'reading' => 'Kouen ni inu ya neko ga imasu.', 'id' => 'Di taman ada anjing, kucing, dan lain-lain.', 'en' => 'There are dogs and cats and such in the park.'],
                        ['ja' => '机《つくえ》の 上《うえ》に 本《ほん》や ノートなどが あります。', 'reading' => 'Tsukue no ue ni hon ya nooto nado ga arimasu.', 'id' => 'Di atas meja ada buku, buku catatan, dan lain-lain.', 'en' => 'On the desk there are books, notebooks and so on.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Isi tas',
                        'title_en' => 'What is in the bag',
                        'lines' => [
                            ['speaker' => 'リナ', 'ja' => 'かばんの 中《なか》に 何《なに》が ありますか。', 'reading' => 'Kaban no naka ni nani ga arimasu ka.', 'id' => 'Di dalam tas ada apa?', 'en' => 'What is in your bag?'],
                            ['speaker' => 'ワヒュ', 'ja' => '本《ほん》や ケータイなどが あります。', 'reading' => 'Hon ya keetai nado ga arimasu.', 'id' => 'Ada buku, HP, dan lain-lain.', 'en' => 'Books, my phone and so on.'],
                            ['speaker' => 'リナ', 'ja' => '細《こま》かい お金《かね》も ありますか。', 'reading' => 'Komakai okane mo arimasu ka.', 'id' => 'Ada uang receh juga?', 'en' => 'Do you have small change too?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、ありません。', 'reading' => 'Iie, arimasen.', 'id' => 'Tidak ada.', 'en' => 'No, I do not.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'Kata posisi: 上《うえ》・下《した》・中《なか》・前《まえ》…',
                'title_en' => 'Position words: ue / shita / naka / mae…',
                'pattern' => 'KBの 上に／下に／中に',
                'payload' => [
                    'explanation_id' => 'Kata posisi (上, 下, 中, 前, 後ろ, 隣, 近く) sebenarnya kata benda. Karena itu dipakai dengan pola KBの + kata posisi, lalu ditutup に untuk menyatakan letak. Urutannya kebalikan dari bahasa Indonesia: patokannya disebut lebih dulu, baru posisinya.',
                    'explanation_en' => 'Position words (ue, shita, naka, mae, ushiro, tonari, chikaku) are really nouns. So they are used as KB no + position word, closed with に to state a location. The order is the reverse of English: the reference point comes first, then the position.',
                    'notes_id' => [
                        'Kalau posisinya menjadi jawaban, cukup pakai です: テーブルの 上《うえ》です.',
                        '隣《となり》 dipakai untuk benda sejenis yang berdampingan; 近《ちか》く untuk sekadar "dekat".',
                    ],
                    'notes_en' => [
                        'When the position is the answer, desu is enough: teeburu no ue desu.',
                        'tonari is for two things of the same kind side by side; chikaku is simply "near".',
                    ],
                    'examples' => [
                        ['ja' => 'テーブルの 上《うえ》に 花《はな》が あります。', 'reading' => 'Teeburu no ue ni hana ga arimasu.', 'id' => 'Di atas meja ada bunga.', 'en' => 'There are flowers on the table.'],
                        ['ja' => '冷蔵庫《れいぞうこ》の 中《なか》に 果物《くだもの》が あります。', 'reading' => 'Reizouko no naka ni kudamono ga arimasu.', 'id' => 'Di dalam kulkas ada buah.', 'en' => 'There is fruit in the fridge.'],
                        ['ja' => '公園《こうえん》の 近《ちか》くに 喫茶店《きっさてん》が あります。', 'reading' => 'Kouen no chikaku ni kissaten ga arimasu.', 'id' => 'Di dekat taman ada kafe.', 'en' => 'There is a cafe near the park.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menunjukkan letak',
                        'title_en' => 'Pointing out a location',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'ポストは どこに ありますか。', 'reading' => 'Posuto wa doko ni arimasu ka.', 'id' => 'Kotak suratnya di mana?', 'en' => 'Where is the mailbox?'],
                            ['speaker' => 'たなか', 'ja' => 'あの ビルの 前《まえ》に あります。', 'reading' => 'Ano biru no mae ni arimasu.', 'id' => 'Ada di depan gedung itu.', 'en' => 'In front of that building.'],
                            ['speaker' => 'サリ', 'ja' => 'コンビニの 隣《となり》ですか。', 'reading' => 'Konbini no tonari desu ka.', 'id' => 'Sebelah minimarket?', 'en' => 'Next to the convenience store?'],
                            ['speaker' => 'たなか', 'ja' => 'ええ、そうです。', 'reading' => 'Ee, sou desu.', 'id' => 'Ya, betul.', 'en' => 'Yes, that is right.'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
