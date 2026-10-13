<?php

namespace Database\Seeders;

use App\Models\Grammar;
use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class Lesson2to5BunpouSeeder extends Seeder
{
    /**
     * Same treatment as Lesson1BunpouSeeder, extended to Pelajaran 2-5: each
     * of these units gets its own "Tata Bahasa (Bunpou)" lesson node that
     * sits BEFORE that unit's vocabulary lesson(s).
     *
     * The card list per unit now follows the ORDER AND SCOPE of the grammar
     * points as the textbook presents them (Pelajaran 2: 8 points, 3: 6,
     * 4: 6, 5: 7) instead of the app's own Grammar table ordering, which
     * skipped points (お〜, そうですか, the ます-form table, particles に / と /
     * ね / よ, どこへも, いつ, そうですね) and promoted one the book does not
     * treat separately (何の).
     *
     * Explanations, notes, example sentences and dialogues below are written
     * fresh for this app — nothing is copied from the book (patterns like
     * "KB1 wa KB2 desu ka" are grammatical facts, not copyrightable). Every
     * example is restricted to vocabulary already taught up to that lesson.
     *
     * Japanese strings may carry furigana markers in the form 漢字《かんじ》;
     * RubyText.vue renders them as <ruby>.
     *
     * Safe to run repeatedly AND on a database that already has users:
     *   php artisan db:seed --class=Lesson2to5BunpouSeeder
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

        // The vocab lesson(s) that used to open this unit now come after the
        // grammar lesson.
        $vocabLessons = Lesson::where('unit_id', $unit->id)
            ->whereIn('order', $unitData['vocab_lesson_orders'])
            ->get();

        foreach ($vocabLessons as $index => $vocabLesson) {
            // Chain multiple vocab lessons in the same unit one after another,
            // with the first one gated behind the new Bunpou lesson.
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
     * Grammar cards that LessonGrammarQuestionSeeder wove into the
     * vocabulary lessons now live in each unit's own Bunpou lesson instead —
     * drop the old copies (safe on a database that already has them, and a
     * no-op on a fresh one seeded in the new order).
     */
    private function removeOldGrammarCards($vocabLessonIds): void
    {
        LessonQuestion::whereIn('lesson_id', $vocabLessonIds)
            ->where('question_type', 'grammar')
            ->delete();
    }

    /**
     * Which unit gets which Bunpou lesson, which existing vocab lesson(s) it
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
            2 => [
                'cards' => $this->lesson2Cards(),
                'vocab_lesson_orders' => [1, 2],
                // Pelajaran 1 has THREE nodes (Bunpou, Orang & Profesi, Negara): Bunpou 2
                // must wait for the last of them. It used to point at order 1, which let
                // Bunpou 2 open right after Orang & Profesi, ahead of Negara.
                'prerequisite_lesson_id' => $lessonByUnitOrder(1, 2),
            ],
            3 => [
                'cards' => $this->lesson3Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(2, 2),
            ],
            4 => [
                'cards' => $this->lesson4Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(3, 1),
            ],
            5 => [
                'cards' => $this->lesson5Cards(),
                'vocab_lesson_orders' => [1],
                'prerequisite_lesson_id' => $lessonByUnitOrder(4, 1),
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 2 — 8 poin
    // ------------------------------------------------------------------

    private function lesson2Cards(): array
    {
        return [
            [
                'grammar_order' => 3,
                'title_id' => 'これ／それ／あれ',
                'title_en' => 'kore / sore / are',
                'pattern' => 'これ・それ・あれ は 〜です',
                'payload' => [
                    'explanation_id' => 'これ, それ, あれ adalah kata tunjuk yang berdiri sendiri: ketiganya menggantikan nama benda, jadi tidak pernah diikuti kata benda lain. これ untuk benda yang dekat dengan pembicara, それ untuk benda yang dekat dengan lawan bicara, dan あれ untuk benda yang jauh dari keduanya.',
                    'explanation_en' => 'kore, sore and are are stand-alone demonstratives: they replace the name of a thing, so they are never followed by another noun. kore is for something near the speaker, sore for something near the listener, and are for something far from both.',
                    'notes_id' => [
                        'Ketiganya dipakai seperti kata benda biasa, jadi bisa menjadi subjek: これは〜です。',
                        'Kalau bendanya tidak diketahui, tanyakan dengan 何《なん》: これは 何《なん》ですか。',
                    ],
                    'notes_en' => [
                        'All three behave like ordinary nouns, so they can be the subject: kore wa ... desu.',
                        'When the thing is unknown, ask with nan: kore wa nan desu ka.',
                    ],
                    'examples' => [
                        ['ja' => 'これは 辞書《じしょ》です。', 'reading' => 'Kore wa jisho desu.', 'id' => 'Ini kamus.', 'en' => 'This is a dictionary.'],
                        ['ja' => 'それは 鍵《かぎ》ですか。', 'reading' => 'Sore wa kagi desu ka.', 'id' => 'Apakah itu kunci?', 'en' => 'Is that a key?'],
                        ['ja' => 'あれは 車《くるま》です。', 'reading' => 'Are wa kuruma desu.', 'id' => 'Itu (yang di sana) mobil.', 'en' => 'That over there is a car.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menanyakan benda di atas meja',
                        'title_en' => 'Asking about something on the desk',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません、これは 何《なん》ですか。', 'reading' => 'Sumimasen, kore wa nan desu ka.', 'id' => 'Permisi, ini apa?', 'en' => 'Excuse me, what is this?'],
                            ['speaker' => 'たなか', 'ja' => 'それは 手帳《てちょう》です。', 'reading' => 'Sore wa techou desu.', 'id' => 'Itu buku agenda.', 'en' => 'That is a planner.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'あれも 手帳《てちょう》ですか。', 'reading' => 'Are mo techou desu ka.', 'id' => 'Yang di sana juga buku agenda?', 'en' => 'Is that one over there a planner too?'],
                            ['speaker' => 'たなか', 'ja' => 'いいえ、あれは 雑誌《ざっし》です。', 'reading' => 'Iie, are wa zasshi desu.', 'id' => 'Bukan, itu majalah.', 'en' => 'No, that is a magazine.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'この／その／あの ＋ kata benda',
                'title_en' => 'kono / sono / ano + noun',
                'pattern' => 'この・その・あの ＋ KB',
                'payload' => [
                    'explanation_id' => 'この, その, あの tidak bisa berdiri sendiri. Ketiganya wajib diikuti kata benda yang diterangkannya. Logika jaraknya sama dengan これ／それ／あれ: この dekat pembicara, その dekat lawan bicara, あの jauh dari keduanya.',
                    'explanation_en' => 'kono, sono and ano cannot stand alone. Each must be followed by the noun it modifies. The distance logic matches kore/sore/are: kono near the speaker, sono near the listener, ano far from both.',
                    'notes_id' => [
                        'Salah: あのは 鞄《かばん》です。 — あの tidak bisa menjadi subjek sendirian.',
                        'Benar: あの 鞄《かばん》は 私《わたし》のです。',
                        'Untuk menyebut orang, pakai あの 人《ひと》 atau yang lebih sopan あの 方《かた》.',
                    ],
                    'notes_en' => [
                        'Wrong: ano wa kaban desu. — ano cannot be a subject on its own.',
                        'Correct: ano kaban wa watashi no desu.',
                        'To refer to a person, use ano hito, or the politer ano kata.',
                    ],
                    'examples' => [
                        ['ja' => 'この 本《ほん》は 私《わたし》のです。', 'reading' => 'Kono hon wa watashi no desu.', 'id' => 'Buku ini punya saya.', 'en' => 'This book is mine.'],
                        ['ja' => 'その 傘《かさ》は あなたのですか。', 'reading' => 'Sono kasa wa anata no desu ka.', 'id' => 'Apakah payung itu punya Anda?', 'en' => 'Is that umbrella yours?'],
                        ['ja' => 'あの 方《かた》は 先生《せんせい》です。', 'reading' => 'Ano kata wa sensei desu.', 'id' => 'Orang itu (sopan) adalah guru.', 'en' => 'That person is a teacher.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Memastikan pemilik barang',
                        'title_en' => 'Checking who owns what',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'この 鞄《かばん》は ワヒュさんのですか。', 'reading' => 'Kono kaban wa Wahyu-san no desu ka.', 'id' => 'Apakah tas ini punya Wahyu?', 'en' => 'Is this bag yours, Wahyu?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、私《わたし》のじゃありません。', 'reading' => 'Iie, watashi no ja arimasen.', 'id' => 'Bukan, itu bukan punya saya.', 'en' => 'No, it is not mine.'],
                            ['speaker' => 'たなか', 'ja' => 'じゃ、あの 鞄《かばん》は。', 'reading' => 'Ja, ano kaban wa.', 'id' => 'Kalau tas yang di sana?', 'en' => 'Then how about that bag over there?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'あれは 私《わたし》のです。', 'reading' => 'Are wa watashi no desu.', 'id' => 'Itu punya saya.', 'en' => 'That one is mine.'],
                        ],
                    ],
                ],
            ],

            [
                'grammar_order' => 9,
                'title_id' => 'そうです／そうじゃありません',
                'title_en' => 'sou desu / sou ja arimasen',
                'pattern' => 'はい、そうです。／ いいえ、そうじゃありません。',
                'payload' => [
                    'explanation_id' => 'Untuk pertanyaan berpola KB1は KB2ですか, jawabannya tidak perlu mengulang seluruh kalimat. そう menggantikan isi pertanyaan itu: はい、そうです untuk membenarkan, いいえ、そうじゃありません untuk menyangkal.',
                    'explanation_en' => 'For a question shaped KB1 wa KB2 desu ka, the answer need not repeat the whole sentence. sou stands in for its content: hai, sou desu to confirm, iie, sou ja arimasen to deny.',
                    'notes_id' => [
                        'そう hanya dipakai untuk kalimat kata benda, bukan untuk pertanyaan dengan kata tanya seperti 何《なん》ですか.',
                        'Bentuk penyangkalan yang lebih halus: いいえ、違《ちが》います。',
                    ],
                    'notes_en' => [
                        'sou only works for noun sentences, not for question-word questions such as nan desu ka.',
                        'A softer way to deny: iie, chigaimasu.',
                    ],
                    'examples' => [
                        ['ja' => 'それは 名刺《めいし》ですか。…はい、そうです。', 'reading' => 'Sore wa meishi desu ka. … Hai, sou desu.', 'id' => 'Apakah itu kartu nama? … Ya, betul.', 'en' => 'Is that a business card? … Yes, it is.'],
                        ['ja' => 'これは 鉛筆《えんぴつ》ですか。…いいえ、そうじゃありません。', 'reading' => 'Kore wa enpitsu desu ka. … Iie, sou ja arimasen.', 'id' => 'Apakah ini pensil? … Bukan.', 'en' => 'Is this a pencil? … No, it is not.'],
                        ['ja' => 'あの 方《かた》は 医者《いしゃ》ですか。…はい、そうです。', 'reading' => 'Ano kata wa isha desu ka. … Hai, sou desu.', 'id' => 'Apakah orang itu dokter? … Ya.', 'en' => 'Is that person a doctor? … Yes.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Memastikan sebuah kartu',
                        'title_en' => 'Confirming a card',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'それは カードですか。', 'reading' => 'Sore wa kaado desu ka.', 'id' => 'Apakah itu kartu?', 'en' => 'Is that a card?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、そうじゃありません。', 'reading' => 'Iie, sou ja arimasen.', 'id' => 'Bukan.', 'en' => 'No, it is not.'],
                            ['speaker' => 'たなか', 'ja' => '名刺《めいし》ですか。', 'reading' => 'Meishi desu ka.', 'id' => 'Kartu nama?', 'en' => 'A business card?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい、そうです。', 'reading' => 'Hai, sou desu.', 'id' => 'Ya, betul.', 'en' => 'Yes, it is.'],
                        ],
                    ],
                ],
            ],

            [
                'grammar_order' => 10,
                'title_id' => '〜か、〜か (pilihan)',
                'title_en' => '~ka, ~ka (choosing between two)',
                'pattern' => 'KB1ですか、KB2ですか',
                'payload' => [
                    'explanation_id' => 'Pola ini menawarkan dua kemungkinan sekaligus dan meminta lawan bicara memilih salah satu. Jawabannya bukan はい atau いいえ, melainkan salah satu pilihan itu.',
                    'explanation_en' => 'This pattern offers two possibilities at once and asks the listener to pick one. The answer is not hai or iie but one of the two options.',
                    'notes_id' => [
                        'Salah: これは コーヒーですか、ワインですか。…はい。',
                        'Benar: …コーヒーです。',
                    ],
                    'notes_en' => [
                        'Wrong: Kore wa koohii desu ka, wain desu ka. … Hai.',
                        'Correct: … Koohii desu.',
                    ],
                    'examples' => [
                        ['ja' => 'これは 鉛筆《えんぴつ》ですか、ボールペンですか。', 'reading' => 'Kore wa enpitsu desu ka, boorupen desu ka.', 'id' => 'Ini pensil atau bolpoin?', 'en' => 'Is this a pencil or a ballpoint pen?'],
                        ['ja' => '…ボールペンです。', 'reading' => '… Boorupen desu.', 'id' => '… Bolpoin.', 'en' => '… It is a ballpoint pen.'],
                        ['ja' => 'あの 方《かた》は 学生《がくせい》ですか、先生《せんせい》ですか。', 'reading' => 'Ano kata wa gakusei desu ka, sensei desu ka.', 'id' => 'Orang itu mahasiswa atau guru?', 'en' => 'Is that person a student or a teacher?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Memilih di antara dua benda',
                        'title_en' => 'Choosing between two things',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'その 本《ほん》は 日本語《にほんご》の 本《ほん》ですか、英語《えいご》の 本《ほん》ですか。', 'reading' => 'Sono hon wa nihongo no hon desu ka, eigo no hon desu ka.', 'id' => 'Buku itu buku bahasa Jepang atau bahasa Inggris?', 'en' => 'Is that book a Japanese book or an English book?'],
                            ['speaker' => 'ワヒュ', 'ja' => '日本語《にほんご》の 本《ほん》です。', 'reading' => 'Nihongo no hon desu.', 'id' => 'Buku bahasa Jepang.', 'en' => 'A Japanese book.'],
                            ['speaker' => 'たなか', 'ja' => '辞書《じしょ》ですか、雑誌《ざっし》ですか。', 'reading' => 'Jisho desu ka, zasshi desu ka.', 'id' => 'Kamus atau majalah?', 'en' => 'A dictionary or a magazine?'],
                            ['speaker' => 'ワヒュ', 'ja' => '辞書《じしょ》です。', 'reading' => 'Jisho desu.', 'id' => 'Kamus.', 'en' => 'A dictionary.'],
                        ],
                    ],
                ],
            ],

            [
                'grammar_order' => 6,
                'title_id' => 'KB1 の KB2 (dua fungsi)',
                'title_en' => 'KB1 no KB2 (two functions)',
                'pattern' => 'KB1 の KB2',
                'payload' => [
                    'explanation_id' => 'の menghubungkan dua kata benda, dan hubungan itu ada dua macam. Pertama, KB1 menerangkan isi atau jenis KB2: 日本語《にほんご》の 本《ほん》 "buku (tentang) bahasa Jepang". Kedua, KB1 adalah pemilik KB2: 私《わたし》の 本《ほん》 "buku saya". Yang diterangkan selalu kata benda yang kedua.',
                    'explanation_en' => 'no links two nouns, and the link comes in two kinds. First, KB1 states the content or type of KB2: nihongo no hon, "a Japanese(-language) book". Second, KB1 is the owner of KB2: watashi no hon, "my book". The head noun is always the second one.',
                    'notes_id' => [
                        'Urutannya tidak boleh dibalik: 私《わたし》の 車《くるま》 bukan 車《くるま》の 私《わたし》.',
                        'Fungsi "isi" dan fungsi "pemilik" memakai partikel yang sama; konteks yang membedakan.',
                    ],
                    'notes_en' => [
                        'The order cannot be reversed: watashi no kuruma, not kuruma no watashi.',
                        'The "content" reading and the "owner" reading use the same particle; context tells them apart.',
                    ],
                    'examples' => [
                        ['ja' => 'これは 日本語《にほんご》の 雑誌《ざっし》です。', 'reading' => 'Kore wa nihongo no zasshi desu.', 'id' => 'Ini majalah berbahasa Jepang. (isi)', 'en' => 'This is a Japanese-language magazine. (content)'],
                        ['ja' => 'それは 先生《せんせい》の 車《くるま》です。', 'reading' => 'Sore wa sensei no kuruma desu.', 'id' => 'Itu mobil Pak/Bu Guru. (pemilik)', 'en' => "That is the teacher's car. (owner)"],
                        ['ja' => 'あれは 大学《だいがく》の 病院《びょういん》です。', 'reading' => 'Are wa daigaku no byouin desu.', 'id' => 'Itu rumah sakit universitas.', 'en' => 'That is the university hospital.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Buku siapa, buku apa',
                        'title_en' => 'Whose book, which book',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'それは 何《なん》の 本《ほん》ですか。', 'reading' => 'Sore wa nan no hon desu ka.', 'id' => 'Itu buku tentang apa?', 'en' => 'What kind of book is that?'],
                            ['speaker' => 'ワヒュ', 'ja' => '英語《えいご》の 本《ほん》です。', 'reading' => 'Eigo no hon desu.', 'id' => 'Buku bahasa Inggris.', 'en' => 'An English book.'],
                            ['speaker' => 'たなか', 'ja' => '誰《だれ》の 本《ほん》ですか。', 'reading' => 'Dare no hon desu ka.', 'id' => 'Buku siapa?', 'en' => 'Whose book is it?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'たなかさんのです。', 'reading' => 'Tanaka-san no desu.', 'id' => 'Punya Pak Tanaka.', 'en' => "It is Mr Tanaka's."],
                        ],
                    ],
                ],
            ],

            [
                'grammar_order' => 7,
                'title_id' => 'の sebagai pengganti kata benda',
                'title_en' => 'no standing in for a noun',
                'pattern' => '〜の (KB dihilangkan)',
                'payload' => [
                    'explanation_id' => 'Kalau kata benda yang diterangkan sudah jelas dari pembicaraan, kata benda itu boleh dihilangkan dan の dibiarkan berdiri sendiri: 私《わたし》の 本《ほん》 → 私《わたし》の. Di sini の berperan sebagai pengganti kata benda tersebut.',
                    'explanation_en' => 'When the head noun is already clear from the conversation it can be dropped, leaving no on its own: watashi no hon becomes watashi no. Here no stands in for that noun.',
                    'notes_id' => [
                        'Penggantian ini hanya untuk benda, tidak untuk orang. Salah: あの 方《かた》は 大学《だいがく》のです。 Benar: あの 方《かた》は 大学《だいがく》の 先生《せんせい》です。',
                        'Kalau bendanya belum jelas, jangan dihilangkan.',
                    ],
                    'notes_en' => [
                        'This only replaces things, never people. Wrong: ano kata wa daigaku no desu. Correct: ano kata wa daigaku no sensei desu.',
                        'If the noun is not yet clear, keep it.',
                    ],
                    'examples' => [
                        ['ja' => 'この 傘《かさ》は 私《わたし》のです。', 'reading' => 'Kono kasa wa watashi no desu.', 'id' => 'Payung ini punya saya.', 'en' => 'This umbrella is mine.'],
                        ['ja' => 'その 時計《とけい》は 誰《だれ》のですか。', 'reading' => 'Sono tokei wa dare no desu ka.', 'id' => 'Jam itu punya siapa?', 'en' => 'Whose watch is that?'],
                        ['ja' => 'あの 鞄《かばん》は たなかさんのじゃありません。', 'reading' => 'Ano kaban wa Tanaka-san no ja arimasen.', 'id' => 'Tas itu bukan punya Pak Tanaka.', 'en' => "That bag is not Mr Tanaka's."],
                    ],
                    'dialogue' => [
                        'title_id' => 'Payung yang tertinggal',
                        'title_en' => 'A left-behind umbrella',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'この 傘《かさ》は 誰《だれ》のですか。', 'reading' => 'Kono kasa wa dare no desu ka.', 'id' => 'Payung ini punya siapa?', 'en' => 'Whose umbrella is this?'],
                            ['speaker' => 'ワヒュ', 'ja' => '私《わたし》のじゃありません。', 'reading' => 'Watashi no ja arimasen.', 'id' => 'Bukan punya saya.', 'en' => 'It is not mine.'],
                            ['speaker' => 'たなか', 'ja' => 'ミラーさんのですか。', 'reading' => 'Miraa-san no desu ka.', 'id' => 'Punya Pak Miller?', 'en' => "Is it Mr Miller's?"],
                            ['speaker' => 'ワヒュ', 'ja' => 'はい、そうです。', 'reading' => 'Hai, sou desu.', 'id' => 'Ya, betul.', 'en' => 'Yes, it is.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'お〜 (awalan penghalus)',
                'title_en' => 'the polite prefix o-',
                'pattern' => 'お ＋ KB',
                'payload' => [
                    'explanation_id' => 'Awalan お dipasang di depan kata benda untuk menghaluskan ucapan, terutama kalau benda itu milik atau berkaitan dengan lawan bicara: お 名前《なまえ》, お 国《くに》, お 土産《みやげ》. Beberapa kata memang hampir selalu memakai お.',
                    'explanation_en' => 'The prefix o- is attached to a noun to make the wording politer, especially when the thing belongs to or concerns the listener: o-namae, o-kuni, o-miyage. A few words are used with o- almost all the time.',
                    'notes_id' => [
                        'Jangan dipakai untuk barang milik sendiri: cukup 名前《なまえ》は…, bukan お 名前《なまえ》は 私《わたし》の…',
                        'Tidak semua kata benda bisa diberi お; hafalkan per kata.',
                    ],
                    'notes_en' => [
                        'Do not use it for your own things: just namae wa ..., not o-namae wa watashi no ...',
                        'Not every noun accepts o-; learn it word by word.',
                    ],
                    'examples' => [
                        ['ja' => 'お 名前《なまえ》は。', 'reading' => 'O-namae wa.', 'id' => 'Nama Anda siapa?', 'en' => 'May I ask your name?'],
                        ['ja' => 'これは お 土産《みやげ》です。', 'reading' => 'Kore wa omiyage desu.', 'id' => 'Ini oleh-oleh.', 'en' => 'This is a souvenir.'],
                        ['ja' => 'お 国《くに》は どちらですか。', 'reading' => 'O-kuni wa dochira desu ka.', 'id' => 'Anda berasal dari negara mana?', 'en' => 'Which country are you from?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menyerahkan oleh-oleh',
                        'title_en' => 'Handing over a souvenir',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'これ、お 土産《みやげ》です。どうぞ。', 'reading' => 'Kore, omiyage desu. Douzo.', 'id' => 'Ini oleh-oleh. Silakan.', 'en' => 'This is a souvenir. Please, take it.'],
                            ['speaker' => 'たなか', 'ja' => 'どうもありがとうございます。何《なん》ですか。', 'reading' => 'Doumo arigatou gozaimasu. Nan desu ka.', 'id' => 'Terima kasih banyak. Apa ini?', 'en' => 'Thank you very much. What is it?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'チョコレートです。', 'reading' => 'Chokoreeto desu.', 'id' => 'Coklat.', 'en' => 'Chocolate.'],
                            ['speaker' => 'たなか', 'ja' => 'そうですか。ありがとうございます。', 'reading' => 'Sou desu ka. Arigatou gozaimasu.', 'id' => 'Oh begitu. Terima kasih.', 'en' => 'I see. Thank you.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'そうですか',
                'title_en' => 'sou desu ka',
                'pattern' => 'そうですか。',
                'payload' => [
                    'explanation_id' => 'そうですか diucapkan dengan nada turun untuk menunjukkan bahwa pembicara baru menerima informasi baru dan menerimanya — kira-kira "oh, begitu". Ini bukan pertanyaan, jadi berbeda dari そうですか dengan nada naik yang berarti "benarkah?".',
                    'explanation_en' => 'sou desu ka, said with a falling intonation, signals that the speaker has just taken in new information and accepts it — roughly "oh, I see". It is not a question, unlike the same phrase with a rising intonation, which means "really?".',
                    'notes_id' => [
                        'Jangan dijawab dengan はい／いいえ — ini bukan pertanyaan.',
                        'Bedakan dengan そうです, yang berarti "betul" dan dipakai untuk membenarkan.',
                    ],
                    'notes_en' => [
                        'Do not answer it with hai/iie — it is not a question.',
                        'Keep it apart from sou desu, which means "that is right" and confirms something.',
                    ],
                    'examples' => [
                        ['ja' => 'この 鍵《かぎ》は 私《わたし》のです。…そうですか。', 'reading' => 'Kono kagi wa watashi no desu. … Sou desu ka.', 'id' => 'Kunci ini punya saya. … Oh, begitu.', 'en' => 'This key is mine. … Oh, I see.'],
                        ['ja' => 'あの 方《かた》は 研究者《けんきゅうしゃ》です。…そうですか。', 'reading' => 'Ano kata wa kenkyuusha desu. … Sou desu ka.', 'id' => 'Orang itu peneliti. … Oh, begitu.', 'en' => 'That person is a researcher. … I see.'],
                        ['ja' => 'これは インドネシアの コーヒーです。…そうですか。', 'reading' => 'Kore wa Indoneshia no koohii desu. … Sou desu ka.', 'id' => 'Ini kopi Indonesia. … Oh, begitu.', 'en' => 'This is Indonesian coffee. … I see.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menerima kabar baru',
                        'title_en' => 'Taking in something new',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'この カメラは 日本《にほん》のですか。', 'reading' => 'Kono kamera wa Nihon no desu ka.', 'id' => 'Kamera ini buatan Jepang?', 'en' => 'Is this camera Japanese?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、ドイツのです。', 'reading' => 'Iie, Doitsu no desu.', 'id' => 'Bukan, buatan Jerman.', 'en' => 'No, it is German.'],
                            ['speaker' => 'たなか', 'ja' => 'そうですか。', 'reading' => 'Sou desu ka.', 'id' => 'Oh, begitu.', 'en' => 'I see.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、お 土産《みやげ》です。', 'reading' => 'Ee, omiyage desu.', 'id' => 'Iya, ini oleh-oleh.', 'en' => 'Yes, it was a gift.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 3 — 6 poin
    // ------------------------------------------------------------------

    private function lesson3Cards(): array
    {
        return [
            [
                'grammar_order' => 8,
                'title_id' => 'ここ／そこ／あそこ dan こちら／そちら／あちら',
                'title_en' => 'koko/soko/asoko and kochira/sochira/achira',
                'pattern' => 'ここ・そこ・あそこ ／ こちら・そちら・あちら',
                'payload' => [
                    'explanation_id' => 'ここ, そこ, あそこ adalah kata tunjuk tempat: ここ tempat pembicara, そこ tempat lawan bicara, あそこ tempat yang jauh dari keduanya. こちら, そちら, あちら menunjuk arah, dan dipakai juga sebagai bentuk sopan untuk tempat.',
                    'explanation_en' => 'koko, soko and asoko point at places: koko where the speaker is, soko where the listener is, asoko away from both. kochira, sochira and achira point at directions, and also serve as the polite way to say a place.',
                    'notes_id' => [
                        'こちら／そちら／あちら lebih sopan daripada ここ／そこ／あそこ.',
                        'Kalau pembicara dan lawan bicara berada di tempat yang sama, ここ mencakup keduanya.',
                    ],
                    'notes_en' => [
                        'kochira/sochira/achira are politer than koko/soko/asoko.',
                        'When speaker and listener share the same spot, koko covers both of them.',
                    ],
                    'examples' => [
                        ['ja' => 'ここは 教室《きょうしつ》です。', 'reading' => 'Koko wa kyoushitsu desu.', 'id' => 'Di sini ruang kelas.', 'en' => 'This place is a classroom.'],
                        ['ja' => 'そこは 事務所《じむしょ》です。', 'reading' => 'Soko wa jimusho desu.', 'id' => 'Di situ kantor.', 'en' => 'That place is the office.'],
                        ['ja' => 'あちらは 会議室《かいぎしつ》です。', 'reading' => 'Achira wa kaigishitsu desu.', 'id' => 'Di sebelah sana ruang rapat.', 'en' => 'Over there is the meeting room.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menunjukkan ruangan',
                        'title_en' => 'Pointing out the rooms',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'ここは 受付《うけつけ》です。', 'reading' => 'Koko wa uketsuke desu.', 'id' => 'Di sini meja informasi.', 'en' => 'This is the reception desk.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'そこは 食堂《しょくどう》ですか。', 'reading' => 'Soko wa shokudou desu ka.', 'id' => 'Di situ kantin?', 'en' => 'Is that the cafeteria?'],
                            ['speaker' => 'たなか', 'ja' => 'いいえ、ロビーです。食堂《しょくどう》は あそこです。', 'reading' => 'Iie, robii desu. Shokudou wa asoko desu.', 'id' => 'Bukan, itu lobi. Kantinnya di sana.', 'en' => 'No, that is the lobby. The cafeteria is over there.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'そうですか。', 'reading' => 'Sou desu ka.', 'id' => 'Oh, begitu.', 'en' => 'I see.'],
                        ],
                    ],
                ],
            ],

            [
                'grammar_order' => 13,
                'title_id' => 'KB は tempat です',
                'title_en' => 'KB wa <place> desu',
                'pattern' => 'KB は (場所) です',
                'payload' => [
                    'explanation_id' => 'Untuk menyatakan di mana sesuatu atau seseorang berada, tempatnya diletakkan sebagai predikat: KBは(tempat)です. Yang ditanyakan atau diberitahukan adalah lokasinya, bukan identitas bendanya.',
                    'explanation_en' => 'To say where something or someone is, the place becomes the predicate: KB wa <place> desu. What is being asked or told is the location, not the identity of the thing.',
                    'notes_id' => [
                        'Pertanyaannya memakai どこ atau どちら: 電話《でんわ》は どこですか。',
                        'Pola ini menyatakan keberadaan secara singkat; kata kerja "ada" belum diajarkan di pelajaran ini.',
                    ],
                    'notes_en' => [
                        'The matching question uses doko or dochira: denwa wa doko desu ka.',
                        'This is the short way to state existence; the verb "to be located" comes later.',
                    ],
                    'examples' => [
                        ['ja' => 'トイレは あそこです。', 'reading' => 'Toire wa asoko desu.', 'id' => 'Toiletnya di sana.', 'en' => 'The toilet is over there.'],
                        ['ja' => '先生《せんせい》は 事務所《じむしょ》です。', 'reading' => 'Sensei wa jimusho desu.', 'id' => 'Pak/Bu Guru ada di kantor.', 'en' => 'The teacher is in the office.'],
                        ['ja' => 'エレベーターは そこです。', 'reading' => 'Erebeetaa wa soko desu.', 'id' => 'Liftnya di situ.', 'en' => 'The elevator is right there.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Mencari telepon',
                        'title_en' => 'Looking for a phone',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません、電話《でんわ》は どこですか。', 'reading' => 'Sumimasen, denwa wa doko desu ka.', 'id' => 'Permisi, teleponnya di mana?', 'en' => 'Excuse me, where is the telephone?'],
                            ['speaker' => 'たなか', 'ja' => 'あそこです。', 'reading' => 'Asoko desu.', 'id' => 'Di sana.', 'en' => 'Over there.'],
                            ['speaker' => 'ワヒュ', 'ja' => '自動販売機《じどうはんばいき》も あそこですか。', 'reading' => 'Jidouhanbaiki mo asoko desu ka.', 'id' => 'Mesin minuman otomatis juga di sana?', 'en' => 'Is the vending machine over there too?'],
                            ['speaker' => 'たなか', 'ja' => 'いいえ、あれは ロビーです。', 'reading' => 'Iie, are wa robii desu.', 'id' => 'Bukan, yang itu di lobi.', 'en' => 'No, that one is in the lobby.'],
                        ],
                    ],
                ],
            ],

            [
                'grammar_order' => 12,
                'title_id' => 'どこ／どちら',
                'title_en' => 'doko / dochira',
                'pattern' => 'KB は どこ・どちら ですか',
                'payload' => [
                    'explanation_id' => 'どこ menanyakan tempat, どちら menanyakan arah dan sekaligus menjadi bentuk sopannya. どちら juga dipakai untuk menanyakan asal negara, perusahaan atau sekolah seseorang dengan halus.',
                    'explanation_en' => 'doko asks for a place; dochira asks for a direction and doubles as its polite form. dochira is also the courteous way to ask which country, company or school someone belongs to.',
                    'notes_id' => [
                        'お 国《くに》は どちらですか。 lebih sopan daripada 国《くに》は どこですか。',
                        'Pertanyaan dengan どこ／どちら tidak pernah dijawab はい／いいえ.',
                    ],
                    'notes_en' => [
                        'O-kuni wa dochira desu ka is politer than kuni wa doko desu ka.',
                        'Questions with doko/dochira are never answered with hai/iie.',
                    ],
                    'examples' => [
                        ['ja' => 'お 手洗《てあら》いは どこですか。', 'reading' => 'Otearai wa doko desu ka.', 'id' => 'Kamar kecilnya di mana?', 'en' => 'Where is the restroom?'],
                        ['ja' => '会社《かいしゃ》は どちらですか。', 'reading' => 'Kaisha wa dochira desu ka.', 'id' => 'Perusahaan Anda yang mana?', 'en' => 'Which company are you with?'],
                        ['ja' => 'エスカレーターは どちらですか。', 'reading' => 'Esukareetaa wa dochira desu ka.', 'id' => 'Eskalatornya sebelah mana?', 'en' => 'Which way is the escalator?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Berkenalan di lobi',
                        'title_en' => 'Getting acquainted in the lobby',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => 'お 国《くに》は どちらですか。', 'reading' => 'O-kuni wa dochira desu ka.', 'id' => 'Anda dari negara mana?', 'en' => 'Which country are you from?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'インドネシアです。', 'reading' => 'Indoneshia desu.', 'id' => 'Indonesia.', 'en' => 'Indonesia.'],
                            ['speaker' => 'たなか', 'ja' => '会社《かいしゃ》は どちらですか。', 'reading' => 'Kaisha wa dochira desu ka.', 'id' => 'Perusahaan Anda yang mana?', 'en' => 'And your company?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'パワー電気《でんき》です。', 'reading' => 'Pawaa Denki desu.', 'id' => 'Power Denki.', 'en' => 'Power Denki.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KB1 の KB2 (asal / buatan)',
                'title_en' => 'KB1 no KB2 (where it is made)',
                'pattern' => '(国・会社) の KB',
                'payload' => [
                    'explanation_id' => 'Nama negara atau perusahaan dipasang di depan の untuk menyatakan asal atau pembuat suatu barang: 日本《にほん》の 車《くるま》 "mobil buatan Jepang". Kalau barangnya sudah jelas, kata bendanya boleh dihilangkan seperti pada pelajaran sebelumnya.',
                    'explanation_en' => 'A country or company name before no states where a thing comes from or who made it: Nihon no kuruma, "a Japanese-made car". If the thing is already clear, the noun may be dropped as in the previous lesson.',
                    'notes_id' => [
                        'Pertanyaannya: どこの 車《くるま》ですか。 — "mobil buatan mana?".',
                        'Jawaban singkat: 日本《にほん》のです。',
                    ],
                    'notes_en' => [
                        'The question form is doko no kuruma desu ka — "made where?".',
                        'Short answer: Nihon no desu.',
                    ],
                    'examples' => [
                        ['ja' => 'これは 日本《にほん》の 時計《とけい》です。', 'reading' => 'Kore wa Nihon no tokei desu.', 'id' => 'Ini jam buatan Jepang.', 'en' => 'This is a Japanese-made watch.'],
                        ['ja' => 'その カメラは どこのですか。', 'reading' => 'Sono kamera wa doko no desu ka.', 'id' => 'Kamera itu buatan mana?', 'en' => 'Where is that camera made?'],
                        ['ja' => '…ドイツのです。', 'reading' => '… Doitsu no desu.', 'id' => '… Buatan Jerman.', 'en' => '… It is German.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di lantai penjualan',
                        'title_en' => 'On the sales floor',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'この 靴《くつ》は どこのですか。', 'reading' => 'Kono kutsu wa doko no desu ka.', 'id' => 'Sepatu ini buatan mana?', 'en' => 'Where are these shoes from?'],
                            ['speaker' => 'たなか', 'ja' => 'イタリアのです。', 'reading' => 'Itaria no desu.', 'id' => 'Buatan Italia.', 'en' => 'They are Italian.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'その ネクタイも イタリアのですか。', 'reading' => 'Sono nekutai mo Itaria no desu ka.', 'id' => 'Dasi itu juga buatan Italia?', 'en' => 'Is that tie Italian too?'],
                            ['speaker' => 'たなか', 'ja' => 'いいえ、フランスのです。', 'reading' => 'Iie, Furansu no desu.', 'id' => 'Bukan, buatan Prancis.', 'en' => 'No, it is French.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'Tabel こ／そ／あ／ど',
                'title_en' => 'The ko-so-a-do table',
                'pattern' => 'こ・そ・あ・ど',
                'payload' => [
                    'explanation_id' => 'Semua kata tunjuk yang sudah dipelajari mengikuti satu pola yang sama. Deret こ untuk yang dekat pembicara, そ untuk yang dekat lawan bicara, あ untuk yang jauh dari keduanya, dan ど untuk bertanya.',
                    'explanation_en' => 'Every demonstrative learned so far follows one and the same pattern. The ko- series is near the speaker, so- near the listener, a- far from both, and do- asks the question.',
                    'notes_id' => [
                        'Benda: これ／それ／あれ／どれ.',
                        'Penerang benda: この／その／あの／どの ＋ KB.',
                        'Tempat: ここ／そこ／あそこ／どこ.',
                        'Arah (dan bentuk sopan tempat): こちら／そちら／あちら／どちら.',
                    ],
                    'notes_en' => [
                        'Things: kore / sore / are / dore.',
                        'Noun modifiers: kono / sono / ano / dono + noun.',
                        'Places: koko / soko / asoko / doko.',
                        'Directions (and the polite place form): kochira / sochira / achira / dochira.',
                    ],
                    'examples' => [
                        ['ja' => 'どれが あなたの 鞄《かばん》ですか。', 'reading' => 'Dore ga anata no kaban desu ka.', 'id' => 'Yang mana tas Anda?', 'en' => 'Which one is your bag?'],
                        ['ja' => 'どの 本《ほん》が 日本語《にほんご》の 本《ほん》ですか。', 'reading' => 'Dono hon ga nihongo no hon desu ka.', 'id' => 'Buku yang mana buku bahasa Jepang?', 'en' => 'Which book is the Japanese one?'],
                        ['ja' => '受付《うけつけ》は どこですか。', 'reading' => 'Uketsuke wa doko desu ka.', 'id' => 'Meja informasinya di mana?', 'en' => 'Where is the reception desk?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Memastikan letak',
                        'title_en' => 'Pinning down a location',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '会議室《かいぎしつ》は どちらですか。', 'reading' => 'Kaigishitsu wa dochira desu ka.', 'id' => 'Ruang rapatnya sebelah mana?', 'en' => 'Which way is the meeting room?'],
                            ['speaker' => 'たなか', 'ja' => 'あちらです。', 'reading' => 'Achira desu.', 'id' => 'Sebelah sana.', 'en' => 'That way.'],
                            ['speaker' => 'ワヒュ', 'ja' => '階段《かいだん》は ここですか、そこですか。', 'reading' => 'Kaidan wa koko desu ka, soko desu ka.', 'id' => 'Tangganya di sini atau di situ?', 'en' => 'Are the stairs here or there?'],
                            ['speaker' => 'たなか', 'ja' => 'そこです。', 'reading' => 'Soko desu.', 'id' => 'Di situ.', 'en' => 'Right there.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'お〜 (penghalus, lanjutan)',
                'title_en' => 'o- (polite prefix, continued)',
                'pattern' => 'お ＋ KB',
                'payload' => [
                    'explanation_id' => 'Pelajaran ini menambah beberapa kata yang lazim memakai awalan お: お 国《くに》, お 手洗《てあら》い, dan ungkapan お 名前《なまえ》 yang sudah dikenal. Awalan ini melembutkan pertanyaan tentang hal yang berkaitan dengan lawan bicara.',
                    'explanation_en' => 'This lesson adds a few more words that normally take the o- prefix: o-kuni, o-tearai, and the already familiar o-namae. The prefix softens a question about something belonging to the listener.',
                    'notes_id' => [
                        'お 手洗《てあら》い lebih halus daripada トイレ.',
                        'Saat menyebut negara sendiri, awalan お tidak dipakai: 私《わたし》の 国《くに》は インドネシアです。',
                    ],
                    'notes_en' => [
                        'o-tearai is more delicate than toire.',
                        'Drop the prefix when talking about your own country: watashi no kuni wa Indoneshia desu.',
                    ],
                    'examples' => [
                        ['ja' => 'お 国《くに》は どちらですか。', 'reading' => 'O-kuni wa dochira desu ka.', 'id' => 'Anda dari negara mana?', 'en' => 'Where are you from?'],
                        ['ja' => 'お 手洗《てあら》いは あそこです。', 'reading' => 'Otearai wa asoko desu.', 'id' => 'Kamar kecilnya di sana.', 'en' => 'The restroom is over there.'],
                        ['ja' => 'お 名前《なまえ》は。', 'reading' => 'O-namae wa.', 'id' => 'Nama Anda?', 'en' => 'Your name, please?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Bertanya dengan sopan',
                        'title_en' => 'Asking politely',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません、お 手洗《てあら》いは どちらですか。', 'reading' => 'Sumimasen, otearai wa dochira desu ka.', 'id' => 'Permisi, kamar kecilnya sebelah mana?', 'en' => 'Excuse me, which way is the restroom?'],
                            ['speaker' => '受付《うけつけ》', 'ja' => '地下《ちか》です。', 'reading' => 'Chika desu.', 'id' => 'Di lantai bawah tanah.', 'en' => 'In the basement.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'エレベーターは どこですか。', 'reading' => 'Erebeetaa wa doko desu ka.', 'id' => 'Liftnya di mana?', 'en' => 'Where is the elevator?'],
                            ['speaker' => '受付《うけつけ》', 'ja' => 'あちらです。', 'reading' => 'Achira desu.', 'id' => 'Sebelah sana.', 'en' => 'That way.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 4 — 6 poin
    // ------------------------------------------------------------------

    private function lesson4Cards(): array
    {
        return [
            [
                'grammar_order' => 14,
                'title_id' => '今 〜時〜分です',
                'title_en' => 'ima ~ji ~fun desu',
                'pattern' => '今《いま》 〜時《じ》〜分《ふん》です',
                'payload' => [
                    'explanation_id' => 'Waktu dinyatakan dengan 時《じ》 untuk jam dan 分《ふん》 untuk menit. Untuk bertanya dipakai 何時《なんじ》 dan 何分《なんぷん》. 半《はん》 dipakai sebagai ganti "tiga puluh menit".',
                    'explanation_en' => 'Time is stated with -ji for the hour and -fun for the minutes. To ask, use nanji and nanpun. han replaces "thirty minutes" for the half hour.',
                    'notes_id' => [
                        'Beberapa bacaan jam berubah: 4時《よじ》, 7時《しちじ》, 9時《くじ》.',
                        'Bacaan menit juga berubah: 1分《いっぷん》, 3分《さんぷん》, 6分《ろっぷん》, 8分《はっぷん》, 10分《じゅっぷん》.',
                        '午前《ごぜん》 dan 午後《ごご》 diletakkan di depan angka jam, bukan di belakang.',
                    ],
                    'notes_en' => [
                        'Some hour readings shift: yoji (4), shichiji (7), kuji (9).',
                        'Minute readings shift too: ippun, sanpun, roppun, happun, juppun.',
                        'gozen and gogo go before the hour, not after.',
                    ],
                    'examples' => [
                        ['ja' => '今《いま》 何時《なんじ》ですか。', 'reading' => 'Ima nanji desu ka.', 'id' => 'Sekarang jam berapa?', 'en' => 'What time is it now?'],
                        ['ja' => '…午前《ごぜん》 9時《くじ》半《はん》です。', 'reading' => '… Gozen kuji han desu.', 'id' => '… Pukul setengah sepuluh pagi.', 'en' => '… It is half past nine in the morning.'],
                        ['ja' => '今《いま》 午後《ごご》 4時《よじ》15分《じゅうごふん》です。', 'reading' => 'Ima gogo yoji juugofun desu.', 'id' => 'Sekarang pukul 16.15.', 'en' => 'It is 4:15 p.m.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menanyakan jam',
                        'title_en' => 'Asking the time',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません、今《いま》 何時《なんじ》ですか。', 'reading' => 'Sumimasen, ima nanji desu ka.', 'id' => 'Permisi, sekarang jam berapa?', 'en' => 'Excuse me, what time is it?'],
                            ['speaker' => 'たなか', 'ja' => '午後《ごご》 1時《いちじ》半《はん》です。', 'reading' => 'Gogo ichiji han desu.', 'id' => 'Pukul setengah dua siang.', 'en' => 'Half past one in the afternoon.'],
                            ['speaker' => 'ワヒュ', 'ja' => '昼休《ひるやす》みは 何時《なんじ》までですか。', 'reading' => 'Hiruyasumi wa nanji made desu ka.', 'id' => 'Istirahat siang sampai jam berapa?', 'en' => 'Until what time is the lunch break?'],
                            ['speaker' => 'たなか', 'ja' => '2時《にじ》までです。', 'reading' => 'Niji made desu.', 'id' => 'Sampai jam dua.', 'en' => 'Until two.'],
                        ],
                    ],
                ],
            ],

            [
                'grammar_order' => 16,
                'title_id' => 'Kata kerja: ます／ません／ました／ませんでした',
                'title_en' => 'Verbs: masu / masen / mashita / masendeshita',
                'pattern' => '〜ます・〜ません・〜ました・〜ませんでした',
                'payload' => [
                    'explanation_id' => 'Kata kerja bentuk ます punya empat bentuk dasar. ます untuk hal yang biasa atau akan terjadi, ません untuk penyangkalannya, ました untuk yang sudah terjadi, dan ませんでした untuk yang tidak terjadi di masa lampau.',
                    'explanation_en' => 'The masu-form verb has four basic shapes. masu covers habits and things still to come, masen is its negative, mashita is the past, and masendeshita the past negative.',
                    'notes_id' => [
                        'Bentuk ます dan ません sekaligus menyatakan kebiasaan dan masa depan; tidak ada bentuk khusus untuk "akan".',
                        'Pertanyaannya cukup ditambah か di akhir: 働《はたら》きますか。',
                        'Jawaban singkat: はい、働《はたら》きます。／ いいえ、働《はたら》きません。',
                    ],
                    'notes_en' => [
                        'masu and masen cover both habit and future; there is no separate "will" form.',
                        'Turn any of them into a question by adding ka: hatarakimasu ka.',
                        'Short answers: hai, hatarakimasu / iie, hatarakimasen.',
                    ],
                    'examples' => [
                        ['ja' => '毎日《まいにち》 勉強《べんきょう》します。', 'reading' => 'Mainichi benkyoushimasu.', 'id' => 'Setiap hari saya belajar.', 'en' => 'I study every day.'],
                        ['ja' => '日曜日《にちようび》は 働《はたら》きません。', 'reading' => 'Nichiyoubi wa hatarakimasen.', 'id' => 'Hari Minggu saya tidak bekerja.', 'en' => 'I do not work on Sundays.'],
                        ['ja' => 'きのう 休《やす》みました。', 'reading' => 'Kinou yasumimashita.', 'id' => 'Kemarin saya libur.', 'en' => 'I took yesterday off.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menanyakan kebiasaan',
                        'title_en' => 'Asking about a routine',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '毎朝《まいあさ》 何時《なんじ》に 起《お》きますか。', 'reading' => 'Maiasa nanji ni okimasu ka.', 'id' => 'Setiap pagi bangun jam berapa?', 'en' => 'What time do you get up every morning?'],
                            ['speaker' => 'ワヒュ', 'ja' => '6時《ろくじ》に 起《お》きます。', 'reading' => 'Rokuji ni okimasu.', 'id' => 'Bangun jam enam.', 'en' => 'I get up at six.'],
                            ['speaker' => 'たなか', 'ja' => 'きのうも 6時《ろくじ》に 起《お》きましたか。', 'reading' => 'Kinou mo rokuji ni okimashita ka.', 'id' => 'Kemarin juga bangun jam enam?', 'en' => 'Did you get up at six yesterday too?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、起《お》きませんでした。休《やす》みでした。', 'reading' => 'Iie, okimasen deshita. Yasumi deshita.', 'id' => 'Tidak. Kemarin libur.', 'en' => 'No, I did not. It was a day off.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KB(waktu) に ＋ kata kerja',
                'title_en' => 'KB(time) ni + verb',
                'pattern' => 'KB(時) に 〜ます',
                'payload' => [
                    'explanation_id' => 'Partikel に menandai titik waktu terjadinya suatu perbuatan. に dipakai kalau kata waktunya berupa angka — jam, tanggal, hari — dan tidak dipakai untuk kata waktu yang tidak berangka seperti きょう, あした, けさ, 毎日《まいにち》.',
                    'explanation_en' => 'The particle ni marks the point in time when an action happens. Use ni when the time word carries a number — a clock time, a date, a weekday — and leave it out for number-less time words such as kyou, ashita, kesa, mainichi.',
                    'notes_id' => [
                        'Dengan angka: 7時《しちじ》に 起《お》きます。 月曜日《げつようび》に 働《はたら》きます。',
                        'Tanpa angka: きょう 休《やす》みます。 (bukan きょうに)',
                        'Nama hari boleh dengan atau tanpa に; dengan に terasa lebih tegas.',
                    ],
                    'notes_en' => [
                        'With a number: shichiji ni okimasu. Getsuyoubi ni hatarakimasu.',
                        'Without one: kyou yasumimasu (never kyou ni).',
                        'Weekdays work with or without ni; with ni it is more explicit.',
                    ],
                    'examples' => [
                        ['ja' => '毎晩《まいばん》 11時《じゅういちじ》に 寝《ね》ます。', 'reading' => 'Maiban juuichiji ni nemasu.', 'id' => 'Setiap malam saya tidur jam sebelas.', 'en' => 'I go to bed at eleven every night.'],
                        ['ja' => '会議《かいぎ》は 9時《くじ》に 終《お》わります。', 'reading' => 'Kaigi wa kuji ni owarimasu.', 'id' => 'Rapatnya selesai jam sembilan.', 'en' => 'The meeting ends at nine.'],
                        ['ja' => 'あした 休《やす》みます。', 'reading' => 'Ashita yasumimasu.', 'id' => 'Besok saya libur.', 'en' => 'I am taking tomorrow off.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Jadwal ujian',
                        'title_en' => 'Exam schedule',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '試験《しけん》は 何曜日《なんようび》ですか。', 'reading' => 'Shiken wa nanyoubi desu ka.', 'id' => 'Ujiannya hari apa?', 'en' => 'What day is the exam?'],
                            ['speaker' => 'たなか', 'ja' => '金曜日《きんようび》です。', 'reading' => 'Kinyoubi desu.', 'id' => 'Hari Jumat.', 'en' => 'Friday.'],
                            ['speaker' => 'ワヒュ', 'ja' => '何時《なんじ》に 終《お》わりますか。', 'reading' => 'Nanji ni owarimasu ka.', 'id' => 'Selesainya jam berapa?', 'en' => 'What time does it finish?'],
                            ['speaker' => 'たなか', 'ja' => '午後《ごご》 3時《さんじ》に 終《お》わります。', 'reading' => 'Gogo sanji ni owarimasu.', 'id' => 'Selesai jam tiga sore.', 'en' => 'It finishes at three in the afternoon.'],
                        ],
                    ],
                ],
            ],

            [
                'grammar_order' => 15,
                'title_id' => 'KB1 から KB2 まで',
                'title_en' => 'KB1 kara KB2 made',
                'pattern' => '〜から 〜まで',
                'payload' => [
                    'explanation_id' => 'から menandai titik awal dan まで titik akhir, baik untuk waktu maupun tempat. Keduanya boleh dipakai sendiri-sendiri kalau hanya satu ujung yang perlu disebut.',
                    'explanation_en' => 'kara marks the starting point and made the end point, for time as well as for places. Either one can stand on its own when only one end needs saying.',
                    'notes_id' => [
                        'Pasangan ini sering langsung diikuti です: 9時《くじ》から 5時《ごじ》までです。',
                        'Bisa dipakai sebagian: 1時《いちじ》から 働《はたら》きます。',
                    ],
                    'notes_en' => [
                        'The pair often takes desu directly: kuji kara goji made desu.',
                        'One half is fine on its own: ichiji kara hatarakimasu.',
                    ],
                    'examples' => [
                        ['ja' => '銀行《ぎんこう》は 9時《くじ》から 3時《さんじ》までです。', 'reading' => 'Ginkou wa kuji kara sanji made desu.', 'id' => 'Bank buka dari jam 9 sampai jam 3.', 'en' => 'The bank is open from nine to three.'],
                        ['ja' => '昼休《ひるやす》みは 12時《じゅうにじ》から 1時《いちじ》までです。', 'reading' => 'Hiruyasumi wa juuniji kara ichiji made desu.', 'id' => 'Istirahat siang dari jam 12 sampai jam 1.', 'en' => 'The lunch break runs from twelve to one.'],
                        ['ja' => '月曜日《げつようび》から 金曜日《きんようび》まで 働《はたら》きます。', 'reading' => 'Getsuyoubi kara kinyoubi made hatarakimasu.', 'id' => 'Saya bekerja dari Senin sampai Jumat.', 'en' => 'I work from Monday to Friday.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Jam buka perpustakaan',
                        'title_en' => 'Library hours',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => '図書館《としょかん》は 何時《なんじ》から 何時《なんじ》までですか。', 'reading' => 'Toshokan wa nanji kara nanji made desu ka.', 'id' => 'Perpustakaan buka dari jam berapa sampai jam berapa?', 'en' => 'From what time to what time is the library open?'],
                            ['speaker' => 'たなか', 'ja' => '午前《ごぜん》 9時《くじ》から 午後《ごご》 6時《ろくじ》までです。', 'reading' => 'Gozen kuji kara gogo rokuji made desu.', 'id' => 'Dari jam 9 pagi sampai jam 6 sore.', 'en' => 'From nine in the morning to six in the evening.'],
                            ['speaker' => 'ワヒュ', 'ja' => '日曜日《にちようび》も ですか。', 'reading' => 'Nichiyoubi mo desu ka.', 'id' => 'Hari Minggu juga?', 'en' => 'On Sundays too?'],
                            ['speaker' => 'たなか', 'ja' => 'いいえ、日曜日《にちようび》は 休《やす》みです。', 'reading' => 'Iie, nichiyoubi wa yasumi desu.', 'id' => 'Tidak, hari Minggu tutup.', 'en' => 'No, it is closed on Sundays.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'KB と KB',
                'title_en' => 'KB to KB',
                'pattern' => 'KB1 と KB2',
                'payload' => [
                    'explanation_id' => 'と menggabungkan dua kata benda atau lebih dengan arti "dan". Partikel ini hanya untuk kata benda — tidak dipakai untuk menggabungkan kalimat atau kata kerja.',
                    'explanation_en' => 'to joins two or more nouns with the sense of "and". The particle works only between nouns — never between clauses or verbs.',
                    'notes_id' => [
                        'Salah: 働《はたら》きますと 休《やす》みます。',
                        'Benar: 銀行《ぎんこう》と 郵便局《ゆうびんきょく》.',
                        'Untuk daftar panjang, と diulang di antara tiap pasangan.',
                    ],
                    'notes_en' => [
                        'Wrong: hatarakimasu to yasumimasu.',
                        'Correct: ginkou to yuubinkyoku.',
                        'For longer lists, repeat to between each pair.',
                    ],
                    'examples' => [
                        ['ja' => '銀行《ぎんこう》と 郵便局《ゆうびんきょく》は 休《やす》みです。', 'reading' => 'Ginkou to yuubinkyoku wa yasumi desu.', 'id' => 'Bank dan kantor pos libur.', 'en' => 'The bank and the post office are closed.'],
                        ['ja' => '土曜日《どようび》と 日曜日《にちようび》は 働《はたら》きません。', 'reading' => 'Doyoubi to nichiyoubi wa hatarakimasen.', 'id' => 'Sabtu dan Minggu saya tidak bekerja.', 'en' => 'I do not work on Saturday and Sunday.'],
                        ['ja' => '図書館《としょかん》と 美術館《びじゅつかん》は あそこです。', 'reading' => 'Toshokan to bijutsukan wa asoko desu.', 'id' => 'Perpustakaan dan gedung kesenian ada di sana.', 'en' => 'The library and the art museum are over there.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Hari libur',
                        'title_en' => 'Days off',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '休《やす》みは 何曜日《なんようび》ですか。', 'reading' => 'Yasumi wa nanyoubi desu ka.', 'id' => 'Liburnya hari apa?', 'en' => 'Which days are your days off?'],
                            ['speaker' => 'ワヒュ', 'ja' => '土曜日《どようび》と 日曜日《にちようび》です。', 'reading' => 'Doyoubi to nichiyoubi desu.', 'id' => 'Sabtu dan Minggu.', 'en' => 'Saturday and Sunday.'],
                            ['speaker' => 'たなか', 'ja' => 'そうですか。', 'reading' => 'Sou desu ka.', 'id' => 'Oh, begitu.', 'en' => 'I see.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、月曜日《げつようび》から 金曜日《きんようび》まで 働《はたら》きます。', 'reading' => 'Ee, getsuyoubi kara kinyoubi made hatarakimasu.', 'id' => 'Iya, kerja dari Senin sampai Jumat.', 'en' => 'Yes, I work from Monday to Friday.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => '〜ね',
                'title_en' => 'the particle ne',
                'pattern' => '〜ですね。',
                'payload' => [
                    'explanation_id' => 'ね diletakkan di akhir kalimat untuk mengajak lawan bicara ikut merasakan atau membenarkan apa yang baru dikatakan. Nadanya mencari persetujuan, bukan memberi informasi baru.',
                    'explanation_en' => 'ne closes a sentence to invite the listener to share the feeling or agree with what was just said. Its tone seeks agreement rather than delivering new information.',
                    'notes_id' => [
                        'Bandingkan: 大変《たいへん》です。 (memberi tahu) — 大変《たいへん》ですね。 (mengajak setuju).',
                        'Jangan dipakai untuk hal yang jelas hanya diketahui pembicara sendiri.',
                    ],
                    'notes_en' => [
                        'Compare taihen desu (stating it) with taihen desu ne (inviting agreement).',
                        'Avoid it for facts only the speaker could know.',
                    ],
                    'examples' => [
                        ['ja' => '毎日《まいにち》 9時《くじ》までですか。大変《たいへん》ですね。', 'reading' => 'Mainichi kuji made desu ka. Taihen desu ne.', 'id' => 'Setiap hari sampai jam 9? Berat ya.', 'en' => 'Until nine every day? That is rough.'],
                        ['ja' => '会議《かいぎ》は 6時《ろくじ》に 終《お》わりますね。', 'reading' => 'Kaigi wa rokuji ni owarimasu ne.', 'id' => 'Rapatnya selesai jam enam, kan?', 'en' => 'The meeting finishes at six, right?'],
                        ['ja' => 'あしたは 休《やす》みですね。', 'reading' => 'Ashita wa yasumi desu ne.', 'id' => 'Besok libur, ya?', 'en' => 'Tomorrow is a day off, is it not?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Berbasa-basi soal jam kerja',
                        'title_en' => 'Small talk about working hours',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '毎晩《まいばん》 何時《なんじ》まで 働《はたら》きますか。', 'reading' => 'Maiban nanji made hatarakimasu ka.', 'id' => 'Setiap malam bekerja sampai jam berapa?', 'en' => 'How late do you work each night?'],
                            ['speaker' => 'ワヒュ', 'ja' => '10時《じゅうじ》までです。', 'reading' => 'Juuji made desu.', 'id' => 'Sampai jam sepuluh.', 'en' => 'Until ten.'],
                            ['speaker' => 'たなか', 'ja' => 'そうですか。大変《たいへん》ですね。', 'reading' => 'Sou desu ka. Taihen desu ne.', 'id' => 'Oh begitu. Berat ya.', 'en' => 'I see. That is tough.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ。日曜日《にちようび》は 休《やす》みます。', 'reading' => 'Ee. Nichiyoubi wa yasumimasu.', 'id' => 'Iya. Hari Minggu libur.', 'en' => 'Yes. I take Sundays off.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Pelajaran 5 — 7 poin
    // ------------------------------------------------------------------

    private function lesson5Cards(): array
    {
        return [
            [
                'grammar_order' => 17,
                'title_id' => '〜へ 行きます／来ます／帰ります',
                'title_en' => '~e ikimasu / kimasu / kaerimasu',
                'pattern' => '(場所) へ 行《い》きます・来《き》ます・帰《かえ》ります',
                'payload' => [
                    'explanation_id' => 'Partikel へ menandai arah tujuan perpindahan dan dibaca "e", bukan "he". Kata kerja perpindahan yang dipakai di pelajaran ini adalah 行《い》きます, 来《き》ます, dan 帰《かえ》ります.',
                    'explanation_en' => 'The particle e marks the direction of a movement; it is written with the kana he but always read "e". The movement verbs in this lesson are ikimasu, kimasu and kaerimasu.',
                    'notes_id' => [
                        '帰《かえ》ります dipakai untuk pulang ke tempat asal — rumah atau negara sendiri.',
                        'Pertanyaan tujuan: どこへ 行《い》きますか。',
                    ],
                    'notes_en' => [
                        'kaerimasu is for returning to where you belong — home, your own country.',
                        'To ask the destination: doko e ikimasu ka.',
                    ],
                    'examples' => [
                        ['ja' => 'あした 駅《えき》へ 行《い》きます。', 'reading' => 'Ashita eki e ikimasu.', 'id' => 'Besok saya pergi ke stasiun.', 'en' => 'I am going to the station tomorrow.'],
                        ['ja' => '来週《らいしゅう》 日本《にほん》へ 来《き》ます。', 'reading' => 'Raishuu Nihon e kimasu.', 'id' => 'Minggu depan dia datang ke Jepang.', 'en' => 'He is coming to Japan next week.'],
                        ['ja' => '6時《ろくじ》に うちへ 帰《かえ》ります。', 'reading' => 'Rokuji ni uchi e kaerimasu.', 'id' => 'Jam enam saya pulang ke rumah.', 'en' => 'I go home at six.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Rencana akhir pekan',
                        'title_en' => 'Weekend plans',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '日曜日《にちようび》 どこへ 行《い》きますか。', 'reading' => 'Nichiyoubi doko e ikimasu ka.', 'id' => 'Hari Minggu mau ke mana?', 'en' => 'Where are you going on Sunday?'],
                            ['speaker' => 'ワヒュ', 'ja' => '大阪城《おおさかじょう》へ 行《い》きます。', 'reading' => 'Oosakajou e ikimasu.', 'id' => 'Ke Kastel Osaka.', 'en' => 'To Osaka Castle.'],
                            ['speaker' => 'たなか', 'ja' => '何時《なんじ》に 帰《かえ》りますか。', 'reading' => 'Nanji ni kaerimasu ka.', 'id' => 'Pulangnya jam berapa?', 'en' => 'What time will you be back?'],
                            ['speaker' => 'ワヒュ', 'ja' => '午後《ごご》 7時《しちじ》に 帰《かえ》ります。', 'reading' => 'Gogo shichiji ni kaerimasu.', 'id' => 'Pulang jam tujuh malam.', 'en' => 'I will be home at seven.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'どこ[へ]も 行きません',
                'title_en' => 'doko (e) mo ikimasen',
                'pattern' => 'どこ[へ]も 行《い》きません',
                'payload' => [
                    'explanation_id' => 'Kata tanya yang diikuti も dan kata kerja bentuk menyangkal berarti "tidak … ke mana pun". Untuk tempat, partikel へ boleh disebut atau dihilangkan: どこへも 行《い》きません atau どこも 行《い》きません.',
                    'explanation_en' => 'A question word followed by mo and a negative verb means "not ... anywhere at all". With places the particle e may be kept or dropped: doko e mo ikimasen or doko mo ikimasen.',
                    'notes_id' => [
                        'Kata kerjanya wajib bentuk menyangkal. Salah: どこへも 行《い》きます。',
                        'Pola yang sama berlaku untuk kata tanya lain: 何《なに》も, 誰《だれ》も.',
                    ],
                    'notes_en' => [
                        'The verb must be negative. Wrong: doko e mo ikimasu.',
                        'The same shape works with other question words: nani mo, dare mo.',
                    ],
                    'examples' => [
                        ['ja' => '土曜日《どようび》 どこへも 行《い》きません。', 'reading' => 'Doyoubi doko e mo ikimasen.', 'id' => 'Hari Sabtu saya tidak pergi ke mana pun.', 'en' => 'I am not going anywhere on Saturday.'],
                        ['ja' => 'きのうは どこも 行《い》きませんでした。', 'reading' => 'Kinou wa doko mo ikimasen deshita.', 'id' => 'Kemarin saya tidak ke mana-mana.', 'en' => 'I did not go anywhere yesterday.'],
                        ['ja' => '来週《らいしゅう》は どこへも 行《い》きません。', 'reading' => 'Raishuu wa doko e mo ikimasen.', 'id' => 'Minggu depan saya tidak ke mana-mana.', 'en' => 'I am not going anywhere next week.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Akhir pekan di rumah',
                        'title_en' => 'A weekend at home',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '土曜日《どようび》 どこへ 行《い》きますか。', 'reading' => 'Doyoubi doko e ikimasu ka.', 'id' => 'Sabtu mau ke mana?', 'en' => 'Where are you going on Saturday?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'どこへも 行《い》きません。', 'reading' => 'Doko e mo ikimasen.', 'id' => 'Tidak ke mana-mana.', 'en' => 'Nowhere at all.'],
                            ['speaker' => 'たなか', 'ja' => 'そうですか。', 'reading' => 'Sou desu ka.', 'id' => 'Oh, begitu.', 'en' => 'I see.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'ええ、うちで 休《やす》みます。', 'reading' => 'Ee, uchi de yasumimasu.', 'id' => 'Iya, istirahat di rumah.', 'en' => 'Yes, I will rest at home.'],
                        ],
                    ],
                ],
            ],

            [
                'grammar_order' => 18,
                'title_id' => 'Kendaraan で ＋ kata kerja',
                'title_en' => '<vehicle> de + verb',
                'pattern' => '(乗り物) で 行《い》きます',
                'payload' => [
                    'explanation_id' => 'で menandai alat atau sarana yang dipakai, termasuk kendaraan: 電車《でんしゃ》で 行《い》きます. Untuk berjalan kaki tidak dipakai で, melainkan kata 歩《ある》いて yang berdiri sendiri.',
                    'explanation_en' => 'de marks the means used, vehicles included: densha de ikimasu. For going on foot there is no de — the word aruite stands on its own.',
                    'notes_id' => [
                        'Salah: 歩《ある》いてで 行《い》きます。 Benar: 歩《ある》いて 行《い》きます。',
                        'Pertanyaan sarana: 何《なに》で 行《い》きますか。',
                    ],
                    'notes_en' => [
                        'Wrong: aruite de ikimasu. Correct: aruite ikimasu.',
                        'To ask the means: nan de ikimasu ka.',
                    ],
                    'examples' => [
                        ['ja' => 'バスで 会社《かいしゃ》へ 行《い》きます。', 'reading' => 'Basu de kaisha e ikimasu.', 'id' => 'Saya pergi ke kantor naik bus.', 'en' => 'I go to the office by bus.'],
                        ['ja' => '新幹線《しんかんせん》で 来《き》ました。', 'reading' => 'Shinkansen de kimashita.', 'id' => 'Saya datang naik Shinkansen.', 'en' => 'I came by bullet train.'],
                        ['ja' => '毎朝《まいあさ》 歩《ある》いて 駅《えき》へ 行《い》きます。', 'reading' => 'Maiasa aruite eki e ikimasu.', 'id' => 'Setiap pagi saya jalan kaki ke stasiun.', 'en' => 'I walk to the station every morning.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Perjalanan ke kantor',
                        'title_en' => 'The commute',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '毎日《まいにち》 何《なに》で 会社《かいしゃ》へ 行《い》きますか。', 'reading' => 'Mainichi nan de kaisha e ikimasu ka.', 'id' => 'Setiap hari ke kantor naik apa?', 'en' => 'How do you get to work every day?'],
                            ['speaker' => 'ワヒュ', 'ja' => '地下鉄《ちかてつ》で 行《い》きます。', 'reading' => 'Chikatetsu de ikimasu.', 'id' => 'Naik kereta bawah tanah.', 'en' => 'By subway.'],
                            ['speaker' => 'たなか', 'ja' => '駅《えき》までは。', 'reading' => 'Eki made wa.', 'id' => 'Kalau sampai stasiun?', 'en' => 'And as far as the station?'],
                            ['speaker' => 'ワヒュ', 'ja' => '自転車《じてんしゃ》で 行《い》きます。', 'reading' => 'Jitensha de ikimasu.', 'id' => 'Naik sepeda.', 'en' => 'By bicycle.'],
                        ],
                    ],
                ],
            ],

            [
                'grammar_order' => 19,
                'title_id' => 'Orang と ＋ kata kerja',
                'title_en' => '<person> to + verb',
                'pattern' => '(人) と 行《い》きます ／ 一人《ひとり》で',
                'payload' => [
                    'explanation_id' => 'と setelah nama orang berarti "bersama". Kalau perbuatan itu dilakukan seorang diri, dipakai 一人《ひとり》で, dan と tidak muncul.',
                    'explanation_en' => 'to after a person means "together with". When the action is done alone, use hitori de, and to does not appear.',
                    'notes_id' => [
                        'Salah: 一人《ひとり》と 行《い》きます。 Benar: 一人《ひとり》で 行《い》きます。',
                        'Pertanyaannya: 誰《だれ》と 行《い》きますか。',
                    ],
                    'notes_en' => [
                        'Wrong: hitori to ikimasu. Correct: hitori de ikimasu.',
                        'The question form is dare to ikimasu ka.',
                    ],
                    'examples' => [
                        ['ja' => '友達《ともだち》と 大阪城《おおさかじょう》へ 行《い》きます。', 'reading' => 'Tomodachi to Oosakajou e ikimasu.', 'id' => 'Saya pergi ke Kastel Osaka bersama teman.', 'en' => 'I am going to Osaka Castle with a friend.'],
                        ['ja' => '家族《かぞく》と 日本《にほん》へ 来《き》ました。', 'reading' => 'Kazoku to Nihon e kimashita.', 'id' => 'Saya datang ke Jepang bersama keluarga.', 'en' => 'I came to Japan with my family.'],
                        ['ja' => '一人《ひとり》で 帰《かえ》ります。', 'reading' => 'Hitori de kaerimasu.', 'id' => 'Saya pulang sendirian.', 'en' => 'I am going home alone.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Pergi berdua',
                        'title_en' => 'Going together',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '来週《らいしゅう》 誰《だれ》と 甲子園《こうしえん》へ 行《い》きますか。', 'reading' => 'Raishuu dare to Koushien e ikimasu ka.', 'id' => 'Minggu depan ke Koshien sama siapa?', 'en' => 'Who are you going to Koshien with next week?'],
                            ['speaker' => 'ワヒュ', 'ja' => '友達《ともだち》と 行《い》きます。', 'reading' => 'Tomodachi to ikimasu.', 'id' => 'Sama teman.', 'en' => 'With a friend.'],
                            ['speaker' => 'たなか', 'ja' => '何《なに》で 行《い》きますか。', 'reading' => 'Nan de ikimasu ka.', 'id' => 'Naik apa?', 'en' => 'How will you get there?'],
                            ['speaker' => 'ワヒュ', 'ja' => '電車《でんしゃ》で 行《い》きます。', 'reading' => 'Densha de ikimasu.', 'id' => 'Naik kereta.', 'en' => 'By train.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'いつ',
                'title_en' => 'itsu',
                'pattern' => 'いつ 〜ますか',
                'payload' => [
                    'explanation_id' => 'いつ menanyakan waktu secara umum, tanpa menyebut jam atau tanggal tertentu. Berbeda dengan 何時《なんじ》 dan 何日《なんにち》, いつ tidak pernah diikuti partikel に.',
                    'explanation_en' => 'itsu asks about time in general, without naming a clock time or a date. Unlike nanji and nannichi, itsu is never followed by the particle ni.',
                    'notes_id' => [
                        'Salah: いつに 行《い》きますか。 Benar: いつ 行《い》きますか。',
                        'Jawabannya boleh berupa kata waktu tanpa angka: 来週《らいしゅう》です。',
                    ],
                    'notes_en' => [
                        'Wrong: itsu ni ikimasu ka. Correct: itsu ikimasu ka.',
                        'The answer may be a number-less time word: raishuu desu.',
                    ],
                    'examples' => [
                        ['ja' => 'いつ 日本《にほん》へ 来《き》ましたか。', 'reading' => 'Itsu Nihon e kimashita ka.', 'id' => 'Kapan Anda datang ke Jepang?', 'en' => 'When did you come to Japan?'],
                        ['ja' => '…先月《せんげつ》 来《き》ました。', 'reading' => '… Sengetsu kimashita.', 'id' => '… Bulan lalu.', 'en' => '… Last month.'],
                        ['ja' => 'いつ 国《くに》へ 帰《かえ》りますか。', 'reading' => 'Itsu kuni e kaerimasu ka.', 'id' => 'Kapan Anda pulang ke negara Anda?', 'en' => 'When are you going back to your country?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Ulang tahun',
                        'title_en' => 'A birthday',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '誕生日《たんじょうび》は いつですか。', 'reading' => 'Tanjoubi wa itsu desu ka.', 'id' => 'Ulang tahunnya kapan?', 'en' => 'When is your birthday?'],
                            ['speaker' => 'ワヒュ', 'ja' => '来月《らいげつ》です。', 'reading' => 'Raigetsu desu.', 'id' => 'Bulan depan.', 'en' => 'Next month.'],
                            ['speaker' => 'たなか', 'ja' => '何日《なんにち》ですか。', 'reading' => 'Nannichi desu ka.', 'id' => 'Tanggal berapa?', 'en' => 'Which day?'],
                            ['speaker' => 'ワヒュ', 'ja' => '十日《とおか》です。', 'reading' => 'Tooka desu.', 'id' => 'Tanggal sepuluh.', 'en' => 'The tenth.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => '〜よ',
                'title_en' => 'the particle yo',
                'pattern' => '〜ますよ。／〜ですよ。',
                'payload' => [
                    'explanation_id' => 'よ di akhir kalimat menegaskan bahwa yang disampaikan adalah informasi baru yang belum diketahui lawan bicara. Nadanya memberitahu — kebalikan dari ね yang mencari persetujuan.',
                    'explanation_en' => 'yo at the end of a sentence stresses that what follows is new information the listener does not have yet. Its tone informs, the opposite of ne, which seeks agreement.',
                    'notes_id' => [
                        'Bandingkan: 次《つぎ》の 電車《でんしゃ》は 急行《きゅうこう》ですよ。 (memberi tahu) — …ですね。 (mencari persetujuan).',
                        'Jangan dipakai berlebihan kepada atasan; kesannya bisa menggurui.',
                    ],
                    'notes_en' => [
                        'Compare tsugi no densha wa kyuukou desu yo (informing) with ... desu ne (seeking agreement).',
                        'Use it sparingly with superiors; overused it can sound like lecturing.',
                    ],
                    'examples' => [
                        ['ja' => 'その 電車《でんしゃ》は 特急《とっきゅう》ですよ。', 'reading' => 'Sono densha wa tokkyuu desu yo.', 'id' => 'Kereta itu kereta ekspres khusus, lho.', 'en' => 'That train is a limited express, you know.'],
                        ['ja' => '図書館《としょかん》は 日曜日《にちようび》 休《やす》みですよ。', 'reading' => 'Toshokan wa nichiyoubi yasumi desu yo.', 'id' => 'Perpustakaan tutup hari Minggu, lho.', 'en' => 'The library is closed on Sundays, just so you know.'],
                        ['ja' => 'バスで 行《い》きますよ。', 'reading' => 'Basu de ikimasu yo.', 'id' => 'Kita pergi naik bus, ya.', 'en' => 'We are going by bus, just so you know.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Di peron stasiun',
                        'title_en' => 'On the platform',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'すみません、甲子園《こうしえん》は 何番線《なんばんせん》ですか。', 'reading' => 'Sumimasen, Koushien wa nanbansen desu ka.', 'id' => 'Permisi, ke Koshien dari jalur berapa?', 'en' => 'Excuse me, which platform is for Koshien?'],
                            ['speaker' => '駅員《えきいん》', 'ja' => '3番線《さんばんせん》です。', 'reading' => 'Sanbansen desu.', 'id' => 'Jalur tiga.', 'en' => 'Platform three.'],
                            ['speaker' => 'ワヒュ', 'ja' => '次《つぎ》の 電車《でんしゃ》ですか。', 'reading' => 'Tsugi no densha desu ka.', 'id' => 'Kereta berikutnya?', 'en' => 'The next train?'],
                            ['speaker' => '駅員《えきいん》', 'ja' => 'いいえ、次《つぎ》のは 特急《とっきゅう》ですよ。', 'reading' => 'Iie, tsugi no wa tokkyuu desu yo.', 'id' => 'Bukan, yang berikutnya kereta ekspres khusus, lho.', 'en' => 'No, the next one is a limited express.'],
                        ],
                    ],
                ],
            ],

            [
                'title_id' => 'そうですね',
                'title_en' => 'sou desu ne',
                'pattern' => 'そうですね。',
                'payload' => [
                    'explanation_id' => 'そうですね punya dua pemakaian. Diucapkan singkat, artinya menyetujui pendapat lawan bicara. Diucapkan dengan nada memanjang, ungkapan ini justru mengisi jeda sambil pembicara berpikir sebelum menjawab.',
                    'explanation_en' => 'sou desu ne has two uses. Said briskly, it agrees with what the other person just said. Drawn out, it instead fills a pause while the speaker thinks before answering.',
                    'notes_id' => [
                        'Bedakan dengan そうです (membenarkan fakta) dan そうですか (menerima informasi baru).',
                        'Sebagai pengisi jeda, ungkapan ini bukan tanda setuju — jawaban sebenarnya menyusul.',
                    ],
                    'notes_en' => [
                        'Keep it apart from sou desu (confirming a fact) and sou desu ka (taking in news).',
                        'As a pause-filler it is not agreement — the real answer comes after it.',
                    ],
                    'examples' => [
                        ['ja' => 'きょうは 休《やす》みですね。…そうですね。', 'reading' => 'Kyou wa yasumi desu ne. … Sou desu ne.', 'id' => 'Hari ini libur, ya. … Iya, betul.', 'en' => 'Today is a day off. … Yes, it is.'],
                        ['ja' => 'いつ 行《い》きますか。…そうですね、来週《らいしゅう》です。', 'reading' => 'Itsu ikimasu ka. … Sou desu ne, raishuu desu.', 'id' => 'Kapan perginya? … Hmm, minggu depan.', 'en' => 'When are you going? … Let me see — next week.'],
                        ['ja' => '誰《だれ》と 行《い》きますか。…そうですね、友達《ともだち》とです。', 'reading' => 'Dare to ikimasu ka. … Sou desu ne, tomodachi to desu.', 'id' => 'Pergi sama siapa? … Hmm, sama teman.', 'en' => 'Who with? … Let me think — with a friend.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menimbang rencana',
                        'title_en' => 'Mulling over a plan',
                        'lines' => [
                            ['speaker' => 'たなか', 'ja' => '今週《こんしゅう》の 日曜日《にちようび》、どこへ 行《い》きますか。', 'reading' => 'Konshuu no nichiyoubi, doko e ikimasu ka.', 'id' => 'Minggu ini hari Minggu mau ke mana?', 'en' => 'Where are you going this Sunday?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'そうですね…、美術館《びじゅつかん》へ 行《い》きます。', 'reading' => 'Sou desu ne…, bijutsukan e ikimasu.', 'id' => 'Hmm… ke gedung kesenian.', 'en' => 'Let me see… to the art museum.'],
                            ['speaker' => 'たなか', 'ja' => 'そうですか。一人《ひとり》でですか。', 'reading' => 'Sou desu ka. Hitori de desu ka.', 'id' => 'Oh begitu. Sendirian?', 'en' => 'I see. On your own?'],
                            ['speaker' => 'ワヒュ', 'ja' => 'いいえ、友達《ともだち》と 行《い》きます。', 'reading' => 'Iie, tomodachi to ikimasu.', 'id' => 'Tidak, sama teman.', 'en' => 'No, with a friend.'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
