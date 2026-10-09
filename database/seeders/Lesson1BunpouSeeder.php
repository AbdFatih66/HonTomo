<?php

namespace Database\Seeders;

use App\Models\Grammar;
use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class Lesson1BunpouSeeder extends Seeder
{
    /**
     * Pelajaran 1 gets its own "Tata Bahasa (Bunpou)" lesson node that sits
     * BEFORE the two vocabulary nodes (Orang & Profesi, Negara). It covers the
     * six grammar points of N5 Pelajaran 1:
     *
     *   1. KB1 は KB2 です          4. KB も
     *   2. KB1 は KB2 じゃありません 5. KB1 の KB2
     *   3. KB1 は KB2 ですか        6. 〜さん
     *
     * The six patterns follow the book's coverage (patterns are grammatical
     * facts). Explanations, example sentences and dialogues are written fresh
     * for this app — nothing is copied from the book (see project copyright
     * note in README).
     *
     * Safe to run repeatedly AND on a database that already has users:
     *   php artisan db:seed --class=Lesson1BunpouSeeder
     *
     * The lesson uses order = 0 so the existing (unit 1, lesson 1/2) lookups in
     * the other seeders keep pointing at the vocabulary lessons.
     */
    public function run(): void
    {
        $unit1 = Unit::where('order', 1)->whereHas('level', fn ($l) => $l->where('code', 'N5'))->firstOrFail();

        $bunpou = Lesson::updateOrCreate(
            ['unit_id' => $unit1->id, 'order' => 0],
            [
                'title_id' => 'Tata Bahasa (Bunpou)',
                'title_en' => 'Grammar (Bunpou)',
                'category' => 'grammar',
                'xp_reward' => 10,
                'required_accuracy' => 0,
                'prerequisite_lesson_id' => null,
                'is_active' => true,
            ]
        );

        // Orang & Profesi now comes after the grammar lesson.
        Lesson::where('unit_id', $unit1->id)
            ->where('order', 1)
            ->update(['prerequisite_lesson_id' => $bunpou->id]);

        $this->seedCards($bunpou);
        $this->removeOldGrammarCards($unit1);
    }

    private function seedCards(Lesson $bunpou): void
    {
        // grammars.order => the matching row in GrammarSeeder (null = no row)
        $grammarOrders = [1 => 1, 2 => 2, 3 => 4, 4 => 5, 5 => 6, 6 => null];
        $grammarIds = Grammar::pluck('id', 'order');

        foreach ($this->cards() as $position => $card) {
            $grammarOrder = $grammarOrders[$position] ?? null;

            LessonQuestion::updateOrCreate(
                [
                    'lesson_id' => $bunpou->id,
                    'question_type' => 'grammar',
                    'order' => $position,
                ],
                [
                    'grammar_id' => $grammarOrder ? ($grammarIds[$grammarOrder] ?? null) : null,
                    'prompt_id' => $card['title_id'],
                    'prompt_en' => $card['title_en'],
                    'japanese_text' => $card['pattern'],
                    'payload' => $card['payload'],
                    'difficulty' => 1,
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Grammar cards that used to be tucked inside the vocabulary lessons are
     * now taught in the Bunpou lesson, so drop the old copies (on existing
     * databases). の "no" (Grammar #6) also moved here from Pelajaran 2.
     */
    private function removeOldGrammarCards(Unit $unit1): void
    {
        $vocabLessonIds = Lesson::where('unit_id', $unit1->id)
            ->whereIn('order', [1, 2])
            ->pluck('id');

        LessonQuestion::whereIn('lesson_id', $vocabLessonIds)
            ->where('question_type', 'grammar')
            ->delete();

        $noGrammar = Grammar::where('order', 6)->first();
        $unit2 = Unit::where('order', 2)->whereHas('level', fn ($l) => $l->where('code', 'N5'))->first();
        $lesson2a = $unit2
            ? Lesson::where('unit_id', $unit2->id)->where('order', 1)->first()
            : null;

        if ($noGrammar && $lesson2a) {
            LessonQuestion::where('lesson_id', $lesson2a->id)
                ->where('question_type', 'grammar')
                ->where('grammar_id', $noGrammar->id)
                ->delete();
        }
    }

    /**
     * @return array<int, array<string, mixed>> keyed by card position (1-6)
     */
    private function cards(): array
    {
        return [
            1 => [
                'title_id' => 'KB1 は KB2 です',
                'title_en' => 'KB1 は KB2 です (X is Y)',
                'pattern' => 'Ａは Ｂです',
                'payload' => [
                    'explanation_id' => 'Pola paling dasar untuk menyebut siapa atau apa sesuatu itu. は menandai topik ("kalau soal ini…"), lalu です menutup kalimat dengan sopan. Bahasa Jepang tidak butuh kata "adalah" di tengah kalimat — cukup KB1 は KB2 です. Kata ganti seperti わたし (saya) memang sering dijadikan topik.',
                    'explanation_en' => 'The most basic pattern for saying who or what something is. は marks the topic ("as for this…") and です closes the sentence politely. Japanese has no separate word for "is" in the middle of the sentence — just Noun1 は Noun2 です. Pronouns such as わたし (I) are very common topics.',
                    'notes_id' => [
                        'Partikel は ditulis "ha" tetapi dibaca "wa".',
                        'Tambahkan 人《じん》 (jin) setelah nama negara untuk menyebut orang dari negara itu: インドネシア人《じん》 = orang Indonesia.',
                    ],
                    'notes_en' => [
                        'The particle は is written "ha" but read "wa".',
                        'Add 人《じん》 (jin) after a country name to say a person from that country: インドネシア人《じん》 = an Indonesian.',
                    ],
                    'examples' => [
                        ['ja' => 'わたしは ワヒュです。', 'reading' => 'Watashi wa Wahyu desu.', 'id' => 'Saya Wahyu.', 'en' => 'I am Wahyu.'],
                        ['ja' => 'わたしは インドネシア人《じん》です。', 'reading' => 'Watashi wa Indoneshia-jin desu.', 'id' => 'Saya orang Indonesia.', 'en' => 'I am Indonesian.'],
                        ['ja' => 'たなかさんは 会社員《かいしゃいん》です。', 'reading' => 'Tanaka-san wa kaishain desu.', 'id' => 'Pak/Bu Tanaka adalah karyawan perusahaan.', 'en' => 'Mr./Ms. Tanaka is a company employee.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Perkenalan pertama',
                        'title_en' => 'A first introduction',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'はじめまして。わたしは ワヒュです。', 'reading' => 'Hajimemashite. Watashi wa Wahyu desu.', 'id' => 'Perkenalkan. Saya Wahyu.', 'en' => 'Nice to meet you. I am Wahyu.'],
                            ['speaker' => 'たなか', 'ja' => 'はじめまして。たなかです。', 'reading' => 'Hajimemashite. Tanaka desu.', 'id' => 'Perkenalkan. Saya Tanaka.', 'en' => 'Nice to meet you. I am Tanaka.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'インドネシアから きました。どうぞ よろしく。', 'reading' => 'Indoneshia kara kimashita. Douzo yoroshiku.', 'id' => 'Saya datang dari Indonesia. Salam kenal.', 'en' => 'I came from Indonesia. Pleased to meet you.'],
                            ['speaker' => 'たなか', 'ja' => 'こちらこそ、よろしく おねがいします。', 'reading' => 'Kochira koso, yoroshiku onegaishimasu.', 'id' => 'Saya juga, mohon kerja samanya.', 'en' => 'Likewise, pleased to meet you.'],
                        ],
                    ],
                ],
            ],

            2 => [
                'title_id' => 'KB1 は KB2 じゃありません',
                'title_en' => 'KB1 は KB2 じゃありません (X is not Y)',
                'pattern' => 'Ａは Ｂじゃ(では) ありません',
                'payload' => [
                    'explanation_id' => 'Bentuk negatif dari です, dipakai untuk menyangkal: "A bukan B". Dalam percakapan sehari-hari orang mengucapkan じゃありません. Untuk pidato resmi atau tulisan formal dipakai ではありません — artinya sama, hanya lebih kaku.',
                    'explanation_en' => 'The negative form of です, used to deny something: "A is not B". In everyday speech people say じゃありません. In formal speeches and writing, ではありません is used — same meaning, just stiffer.',
                    'notes_id' => [
                        'Pada ではありません, は tetap dibaca "wa".',
                    ],
                    'notes_en' => [
                        'In ではありません, は is still read "wa".',
                    ],
                    'examples' => [
                        ['ja' => 'わたしは 先生《せんせい》じゃありません。', 'reading' => 'Watashi wa sensei ja arimasen.', 'id' => 'Saya bukan guru.', 'en' => 'I am not a teacher.'],
                        ['ja' => 'たなかさんは 医者《いしゃ》じゃありません。', 'reading' => 'Tanaka-san wa isha ja arimasen.', 'id' => 'Pak/Bu Tanaka bukan dokter.', 'en' => 'Mr./Ms. Tanaka is not a doctor.'],
                        ['ja' => 'あの人《ひと》は 学生《がくせい》ではありません。', 'reading' => 'Ano hito wa gakusei dewa arimasen.', 'id' => 'Orang itu bukan mahasiswa. (lebih formal)', 'en' => 'That person is not a student. (more formal)'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Menyangkal dengan sopan',
                        'title_en' => 'A polite denial',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'サリさんは 学生《がくせい》ですか。', 'reading' => 'Sari-san wa gakusei desu ka.', 'id' => 'Sari, apakah kamu mahasiswa?', 'en' => 'Sari, are you a student?'],
                            ['speaker' => 'サリ', 'ja' => 'いいえ、学生《がくせい》じゃありません。', 'reading' => 'Iie, gakusei ja arimasen.', 'id' => 'Bukan, saya bukan mahasiswa.', 'en' => 'No, I am not a student.'],
                            ['speaker' => 'サリ', 'ja' => 'わたしは 銀行員《ぎんこういん》です。', 'reading' => 'Watashi wa ginkouin desu.', 'id' => 'Saya pegawai bank.', 'en' => 'I am a bank employee.'],
                        ],
                    ],
                ],
            ],

            3 => [
                'title_id' => 'KB1 は KB2 ですか (kalimat tanya)',
                'title_en' => 'KB1 は KB2 ですか (questions)',
                'pattern' => 'Ａは Ｂですか',
                'payload' => [
                    'explanation_id' => 'Tambahkan partikel か di akhir kalimat untuk membuat pertanyaan. Susunan kata tidak berubah sama sekali — hanya nada suara naik di ujung kalimat. Pertanyaan ya/tidak dijawab はい (ya) atau いいえ (bukan). Kalau ingin menanyakan bagian tertentu, ganti bagian itu dengan kata tanya di tempat yang sama, misalnya だれ (siapa) atau なんさい (umur berapa), lalu akhiri dengan か.',
                    'explanation_en' => 'Add the particle か to the end of a sentence to make it a question. The word order does not change at all — only your voice rises at the end. Yes/no questions are answered with はい (yes) or いいえ (no). To ask about one specific part, put a question word in that spot — like だれ (who) or なんさい (how old) — and finish with か.',
                    'notes_id' => [
                        'どなた lebih sopan daripada だれ; おいくつ lebih sopan daripada なんさい.',
                        'Kalau ditanya nama sendiri, cukup jawab "Nama + です".',
                    ],
                    'notes_en' => [
                        'どなた is more polite than だれ; おいくつ is more polite than なんさい.',
                        'When asked your name, just answer "Name + です".',
                    ],
                    'examples' => [
                        ['ja' => 'サリさんは 医者《いしゃ》ですか。', 'reading' => 'Sari-san wa isha desu ka.', 'id' => 'Apakah Sari seorang dokter?', 'en' => 'Is Sari a doctor?'],
                        ['ja' => 'あの人《ひと》は だれですか。', 'reading' => 'Ano hito wa dare desu ka.', 'id' => 'Siapa orang itu?', 'en' => 'Who is that person?'],
                        ['ja' => 'ミナちゃんは なんさいですか。', 'reading' => 'Mina-chan wa nansai desu ka.', 'id' => 'Mina umurnya berapa?', 'en' => 'How old is Mina?'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Bertanya soal pekerjaan',
                        'title_en' => 'Asking about work',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'しつれいですが、お名前《なまえ》は？', 'reading' => 'Shitsurei desu ga, onamae wa?', 'id' => 'Permisi, siapa namanya?', 'en' => 'Excuse me, what is your name?'],
                            ['speaker' => 'リナ', 'ja' => 'リナです。', 'reading' => 'Rina desu.', 'id' => 'Rina.', 'en' => 'I am Rina.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'リナさんは 学生《がくせい》ですか。', 'reading' => 'Rina-san wa gakusei desu ka.', 'id' => 'Rina, apakah kamu mahasiswi?', 'en' => 'Rina, are you a student?'],
                            ['speaker' => 'リナ', 'ja' => 'はい、学生《がくせい》です。', 'reading' => 'Hai, gakusei desu.', 'id' => 'Ya, saya mahasiswi.', 'en' => 'Yes, I am a student.'],
                        ],
                    ],
                ],
            ],

            4 => [
                'title_id' => 'KB も (juga)',
                'title_en' => 'KB も (also / too)',
                'pattern' => 'Ａも Ｂです',
                'payload' => [
                    'explanation_id' => 'も berarti "juga". Pakai も menggantikan は ketika informasi tentang topik baru sama dengan topik yang baru saja disebut. も dan は tidak pernah menempel pada kata benda yang sama. Kalau informasinya berbeda, kembali pakai は. も juga bisa dipakai di kalimat negatif: "juga bukan".',
                    'explanation_en' => 'も means "also". Use も in place of は when the new topic shares the same information as the topic just mentioned. も and は never sit on the same noun. If the information is different, go back to は. も also works in negative sentences: "also not".',
                    'notes_id' => [
                        'Contoh negatif: わたしも 学生《がくせい》じゃありません = saya juga bukan mahasiswa.',
                    ],
                    'notes_en' => [
                        'Negative example: わたしも 学生《がくせい》じゃありません = I am not a student either.',
                    ],
                    'examples' => [
                        ['ja' => 'わたしは 学生《がくせい》です。リナさんも 学生《がくせい》です。', 'reading' => 'Watashi wa gakusei desu. Rina-san mo gakusei desu.', 'id' => 'Saya mahasiswa. Rina juga mahasiswi.', 'en' => 'I am a student. Rina is a student too.'],
                        ['ja' => 'ワヒュさんは インドネシア人《じん》です。サリさんも インドネシア人《じん》です。', 'reading' => 'Wahyu-san wa Indoneshia-jin desu. Sari-san mo Indoneshia-jin desu.', 'id' => 'Wahyu orang Indonesia. Sari juga orang Indonesia.', 'en' => 'Wahyu is Indonesian. Sari is Indonesian too.'],
                        ['ja' => 'たなかさんは 銀行員《ぎんこういん》じゃありません。ワヒュさんも 銀行員《ぎんこういん》じゃありません。', 'reading' => 'Tanaka-san wa ginkouin ja arimasen. Wahyu-san mo ginkouin ja arimasen.', 'id' => 'Pak/Bu Tanaka bukan pegawai bank. Wahyu juga bukan pegawai bank.', 'en' => 'Mr./Ms. Tanaka is not a bank employee. Wahyu is not one either.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Sama, tapi tidak semuanya',
                        'title_en' => 'The same — but not in everything',
                        'lines' => [
                            ['speaker' => 'サリ', 'ja' => 'わたしは インドネシア人《じん》です。', 'reading' => 'Watashi wa Indoneshia-jin desu.', 'id' => 'Saya orang Indonesia.', 'en' => 'I am Indonesian.'],
                            ['speaker' => 'リナ', 'ja' => 'わたしも インドネシア人《じん》です。', 'reading' => 'Watashi mo Indoneshia-jin desu.', 'id' => 'Saya juga orang Indonesia.', 'en' => 'I am Indonesian too.'],
                            ['speaker' => 'サリ', 'ja' => 'リナさんも 会社員《かいしゃいん》ですか。', 'reading' => 'Rina-san mo kaishain desu ka.', 'id' => 'Apakah Rina juga karyawan perusahaan?', 'en' => 'Are you a company employee too, Rina?'],
                            ['speaker' => 'リナ', 'ja' => 'いいえ、わたしは 学生《がくせい》です。', 'reading' => 'Iie, watashi wa gakusei desu.', 'id' => 'Bukan, saya mahasiswi.', 'en' => 'No, I am a student.'],
                        ],
                    ],
                ],
            ],

            5 => [
                'title_id' => 'KB1 の KB2',
                'title_en' => 'KB1 の KB2 (linking two nouns)',
                'pattern' => 'Ａの Ｂ',
                'payload' => [
                    'explanation_id' => 'の menyambung dua kata benda: KB1 menerangkan KB2. Di Pelajaran 1, KB1 menunjukkan tempat/kelompok tempat seseorang berada — perusahaan, universitas, rumah sakit. Urutannya kebalikan dari bahasa Indonesia: keterangan disebut lebih dulu, baru bendanya. "Mahasiswa Universitas Himawari" menjadi 「Himawari daigaku no gakusei」.',
                    'explanation_en' => 'の links two nouns: Noun1 describes Noun2. In Lesson 1, Noun1 shows the place or group someone belongs to — a company, university or hospital. The order is the reverse of Indonesian or English: the descriptor comes first, then the thing it describes. "A student of Himawari University" becomes 「Himawari daigaku no gakusei」.',
                    'notes_id' => [
                        'Fungsi の akan bertambah di pelajaran berikutnya (misalnya kepemilikan: わたしの ほん = buku saya).',
                    ],
                    'notes_en' => [
                        'の gets more uses in later lessons (for example possession: わたしの ほん = my book).',
                    ],
                    'examples' => [
                        ['ja' => 'わたしは ひまわり大学《だいがく》の 学生《がくせい》です。', 'reading' => 'Watashi wa Himawari daigaku no gakusei desu.', 'id' => 'Saya mahasiswa Universitas Himawari.', 'en' => 'I am a student of Himawari University.'],
                        ['ja' => 'リナさんは あおぞら銀行《ぎんこう》の 社員《しゃいん》です。', 'reading' => 'Rina-san wa Aozora ginkou no shain desu.', 'id' => 'Rina adalah karyawan Bank Aozora.', 'en' => 'Rina is an employee of Aozora Bank.'],
                        ['ja' => 'サリさんは みどり病院《びょういん》の 医者《いしゃ》です。', 'reading' => 'Sari-san wa Midori byouin no isha desu.', 'id' => 'Sari adalah dokter di Rumah Sakit Midori.', 'en' => 'Sari is a doctor at Midori Hospital.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Siapa orang itu?',
                        'title_en' => 'Who is that person?',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'あの人《ひと》は だれですか。', 'reading' => 'Ano hito wa dare desu ka.', 'id' => 'Siapa orang itu?', 'en' => 'Who is that person?'],
                            ['speaker' => 'リナ', 'ja' => 'ひまわり大学《だいがく》の 先生《せんせい》です。', 'reading' => 'Himawari daigaku no sensei desu.', 'id' => 'Dosen Universitas Himawari.', 'en' => 'A teacher at Himawari University.'],
                            ['speaker' => 'ワヒュ', 'ja' => 'たなかさんですか。', 'reading' => 'Tanaka-san desu ka.', 'id' => 'Apakah itu Pak/Bu Tanaka?', 'en' => 'Is that Mr./Ms. Tanaka?'],
                            ['speaker' => 'リナ', 'ja' => 'はい、たなかさんです。', 'reading' => 'Hai, Tanaka-san desu.', 'id' => 'Ya, betul, Pak/Bu Tanaka.', 'en' => 'Yes, that is Mr./Ms. Tanaka.'],
                        ],
                    ],
                ],
            ],

            6 => [
                'title_id' => '〜さん',
                'title_en' => '〜さん (name suffix)',
                'pattern' => 'なまえ ＋ さん',
                'payload' => [
                    'explanation_id' => 'さん ditambahkan di belakang nama keluarga atau nama orang lain sebagai tanda hormat — mirip "Bapak/Ibu/Kak", tetapi dipakai untuk siapa saja dan semua gender. Karena menunjukkan rasa hormat, さん tidak pernah dipakai untuk nama diri sendiri. Kepada anak kecil dipakai ちゃん yang terdengar akrab. Saat memanggil lawan bicara yang sudah dikenal, pakai nama + さん, jangan あなた.',
                    'explanation_en' => 'さん is added after someone else\'s family name or name as a mark of respect — similar to "Mr./Ms.", but used for anyone of any gender. Because it shows respect, さん is never attached to your own name. For small children, ちゃん is used and sounds affectionate. When addressing someone you already know, use name + さん rather than あなた.',
                    'notes_id' => [
                        'Benar: わたしは ワヒュです。 Salah: わたしは ワヒュさんです。',
                        'あなた terdengar terlalu langsung kalau dipakai kepada orang yang tidak akrab; biasanya hanya untuk hubungan yang sangat dekat.',
                    ],
                    'notes_en' => [
                        'Correct: わたしは ワヒュです。 Wrong: わたしは ワヒュさんです。',
                        'あなた can sound too direct with people you are not close to; it is mostly kept for very close relationships.',
                    ],
                    'examples' => [
                        ['ja' => 'こちらは たなかさんです。', 'reading' => 'Kochira wa Tanaka-san desu.', 'id' => 'Ini Pak/Bu Tanaka.', 'en' => 'This is Mr./Ms. Tanaka.'],
                        ['ja' => 'ワヒュさんは 会社員《かいしゃいん》ですか。', 'reading' => 'Wahyu-san wa kaishain desu ka.', 'id' => 'Wahyu, apakah kamu karyawan perusahaan?', 'en' => 'Wahyu, are you a company employee?'],
                        ['ja' => 'ミナちゃんは 七歳《ななさい》です。', 'reading' => 'Mina-chan wa nanasai desu.', 'id' => 'Mina berumur tujuh tahun.', 'en' => 'Mina is seven years old.'],
                    ],
                    'dialogue' => [
                        'title_id' => 'Memperkenalkan seseorang',
                        'title_en' => 'Introducing someone',
                        'lines' => [
                            ['speaker' => 'ワヒュ', 'ja' => 'リナさん、こちらは たなかさんです。', 'reading' => 'Rina-san, kochira wa Tanaka-san desu.', 'id' => 'Rina, ini Pak/Bu Tanaka.', 'en' => 'Rina, this is Mr./Ms. Tanaka.'],
                            ['speaker' => 'たなか', 'ja' => 'はじめまして。たなかです。', 'reading' => 'Hajimemashite. Tanaka desu.', 'id' => 'Perkenalkan. Saya Tanaka.', 'en' => 'Nice to meet you. I am Tanaka.'],
                            ['speaker' => 'リナ', 'ja' => 'はじめまして。リナです。どうぞ よろしく。', 'reading' => 'Hajimemashite. Rina desu. Douzo yoroshiku.', 'id' => 'Perkenalkan. Saya Rina. Salam kenal.', 'en' => 'Nice to meet you. I am Rina. Pleased to meet you.'],
                            ['speaker' => 'たなか', 'ja' => 'よろしく おねがいします。', 'reading' => 'Yoroshiku onegaishimasu.', 'id' => 'Mohon kerja samanya.', 'en' => 'Pleased to meet you.'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
