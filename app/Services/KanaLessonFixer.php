<?php

namespace App\Services;

use App\Models\KanaCharacter;
use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\Level;
use App\Models\QuestionOption;
use App\Models\Unit;
use Database\Seeders\KanaSeeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Rebuilds the questions of the "Hiragana & Katakana" learning-path unit
 * straight from the kana chart (the source of truth), so what is shown and
 * what counts as correct can never disagree.
 *
 * Ten lessons (5 per script), named like the chart sections:
 *
 *   Hiragana Dasar (Gojuuon) · Hiragana: Dakuten · Hiragana: Handakuten ·
 *   Hiragana: Yoon · Hiragana: Yoon Dakuten
 *   Katakana Dasar (Gojuuon) · Katakana: Dakuten · Katakana: Handakuten ·
 *   Katakana: Yoon · Katakana: Yoon Dakuten
 *
 * Every lesson holds ONLY its own letters (per script: 46 / 20 / 5 / 21 / 15),
 * one multiple-choice question per letter: the letter is shown, the learner
 * picks its reading. Nothing else is printed on the question (no reading), and
 * the three wrong options come from the same lesson only.
 *
 * The chart itself is re-synced first (every letter's type / reading / row is
 * set back to the seeder's canonical data and duplicate rows are removed), so
 * lessons — and the chart page — can never be built from mixed-up letters.
 *
 * Existing lessons are reused (so learner progress on them is kept) and any
 * others in the unit (e.g. "Gabungan…", split lessons) are merged away.
 */
class KanaLessonFixer
{
    public const UNIT_TITLE = 'Hiragana & Katakana';

    /** key => [script, chart type(s), title] */
    private const LESSONS = [
        'hiragana.dasar' => ['hiragana', ['gojuon'], 'Hiragana Dasar (Gojuuon)'],
        'hiragana.dakuten' => ['hiragana', ['dakuten'], 'Hiragana: Dakuten'],
        'hiragana.handakuten' => ['hiragana', ['handakuten'], 'Hiragana: Handakuten'],
        'hiragana.yoon' => ['hiragana', ['yoon'], 'Hiragana: Yoon'],
        'hiragana.yoon_dakuten' => ['hiragana', ['yoon_dakuten'], 'Hiragana: Yoon Dakuten'],
        'katakana.dasar' => ['katakana', ['gojuon'], 'Katakana Dasar (Gojuuon)'],
        'katakana.dakuten' => ['katakana', ['dakuten'], 'Katakana: Dakuten'],
        'katakana.handakuten' => ['katakana', ['handakuten'], 'Katakana: Handakuten'],
        'katakana.yoon' => ['katakana', ['yoon'], 'Katakana: Yoon'],
        'katakana.yoon_dakuten' => ['katakana', ['yoon_dakuten'], 'Katakana: Yoon Dakuten'],
    ];

    /**
     * @return array<string, mixed>
     */
    public function run(): array
    {
        return DB::transaction(function () {
            $chartFixed = $this->syncChart();
            $unit = $this->unit();

            $lessons = Lesson::where('unit_id', $unit->id)->orderBy('order')->orderBy('id')->get();
            $oldIds = $lessons->pluck('id');
            $dependents = Lesson::whereIn('prerequisite_lesson_id', $oldIds)->whereNotIn('id', $oldIds)->pluck('id');

            // One lesson per key: reuse an existing one (keeps learner progress).
            $targets = [];
            $surplus = collect();

            foreach ($lessons as $lesson) {
                $key = $this->keyForLesson($lesson);

                if ($key === null) {
                    $surplus->push($lesson); // e.g. sokuon / chouon: not part of this unit

                    continue;
                }

                if (! isset($targets[$key]) || trim($lesson->title_id) === self::LESSONS[$key][2]) {
                    if (isset($targets[$key])) {
                        $surplus->push($targets[$key]);
                    }
                    $targets[$key] = $lesson;
                }
                else {
                    $surplus->push($lesson);
                }
            }

            foreach (self::LESSONS as $key => [$script, , $title]) {
                $targets[$key] ??= Lesson::create([
                    'unit_id' => $unit->id, 'title_id' => $title, 'title_en' => $title,
                    'category' => $script, 'order' => 99, 'xp_reward' => 10,
                    'required_accuracy' => 70, 'is_active' => true,
                ]);
            }

            Lesson::whereIn('id', $surplus->pluck('id'))->delete(); // questions/options/progress cascade

            $chart = KanaCharacter::orderBy('order')->get();
            $this->assertChartIsSane($chart);
            $previous = null;
            $order = 0;
            $summary = [];

            foreach (self::LESSONS as $key => [$script, $types, $title]) {
                $lesson = $targets[$key];

                $lesson->update([
                    'title_id' => $title, 'title_en' => $title, 'category' => $script,
                    'order' => ++$order, 'prerequisite_lesson_id' => $previous?->id,
                ]);

                $letters = $chart->filter(fn ($k) => $k->script === $script && in_array($k->type, $types, true))->values();

                LessonQuestion::where('lesson_id', $lesson->id)->delete();
                $this->buildQuiz($lesson, $letters);

                // What the lesson really contains, by the chart's type of each letter.
                $byType = $lesson->questions()->pluck('japanese_text')
                    ->map(fn ($c) => $chart->firstWhere('character', $c)?->type ?? '?')
                    ->countBy()->map(fn ($n, $t) => "$t=$n")->implode(', ');
                $summary[$title] = $byType;

                $previous = $lesson;
            }

            // Lessons elsewhere that waited for one of the removed/old kana lessons.
            if ($previous && $dependents->isNotEmpty()) {
                Lesson::whereIn('id', $dependents)->update(['prerequisite_lesson_id' => $previous->id]);
            }

            return [
                'lessons_removed' => $surplus->count(),
                'chart_rows_fixed' => $chartFixed,
                'per_lesson' => $summary,
                'chart' => $chart->groupBy('script')->map(fn ($g) => $g->countBy('type')->all())->all(),
            ];
        });
    }

    // ------------------------------------------------------------------

    private function unit(): Unit
    {
        // Every unit about Hiragana/Katakana (a leftover duplicate would show the
        // old, wrong lessons next to the new ones): keep one, fold in the rest.
        $units = Unit::where('title_id', 'like', '%Hiragana%')
            ->orWhere('title_id', 'like', '%Katakana%')
            ->orWhere('title_en', 'like', '%Hiragana%')
            ->orderBy('order')->orderBy('id')->get();

        $unit = $units->firstWhere('title_id', self::UNIT_TITLE) ?? $units->first();

        if ($unit) {
            foreach ($units->where('id', '!=', $unit->id) as $duplicate) {
                Lesson::where('unit_id', $duplicate->id)->update(['unit_id' => $unit->id]);
                $duplicate->delete();
            }

            return $unit;
        }

        $level = Level::where('code', 'N5')->first() ?? Level::orderBy('order')->firstOrFail();

        return Unit::create([
            'level_id' => $level->id, 'title_id' => self::UNIT_TITLE, 'title_en' => 'Hiragana & Katakana',
            'description_id' => 'Dua huruf dasar bahasa Jepang, lengkap dengan cara menulis, tenten/maru, dan gabungan.',
            'description_en' => 'The two basic Japanese scripts, with writing, tenten/maru and combined letters.',
            'icon' => 'mdi-translate', 'order' => 0, 'is_active' => true,
        ]);
    }

    /** null = a lesson that does not belong in this unit any more. */
    private function keyForLesson(Lesson $lesson): ?string
    {
        $text = mb_strtolower($lesson->category.' '.$lesson->title_id.' '.$lesson->title_en);
        $script = str_contains($text, 'katakana') ? 'katakana' : 'hiragana';

        return match (true) {
            (bool) preg_match('/sokuon|chouon/', $text) => null,
            // "Gabungan (Youon)" from older builds is reused as the plain Yoon lesson.
            (bool) preg_match('/yoon|youon|gabungan|kombinasi/', $text) => preg_match('/dakuten|bersuara|voiced/', $text)
                ? "$script.yoon_dakuten"
                : "$script.yoon",
            (bool) preg_match('/handakuten/', $text) => "$script.handakuten",
            (bool) preg_match('/dakuten|tenten/', $text) => "$script.dakuten", // "Tenten & Maru" -> Dakuten
            (bool) preg_match('/\bmaru\b/', $text) => "$script.handakuten",
            default => "$script.dasar",
        };
    }

    // ------------------------------------------------------------------
    // Chart integrity
    // ------------------------------------------------------------------

    /**
     * Rebuilds the whole chart from the seeder's canonical data.
     *
     * The table is wiped and re-seeded rather than patched row by row: on MySQL
     * (utf8mb4_unicode_ci) か = が = カ compare as EQUAL, so any lookup by
     * character can land on the wrong row and a damaged table cannot be repaired
     * by matching on the letter. Nothing references these rows except each
     * other (base_character_id), so replacing them is safe.
     *
     * @return int number of rows that were wrong (type/reading/letter) or extra
     */
    private function syncChart(): int
    {
        $before = KanaCharacter::orderBy('id')->get()
            ->map(fn ($k) => $k->script.'|'.$k->character.'|'.$k->type.'|'.$k->romaji);

        KanaCharacter::query()->delete();

        app(KanaSeeder::class)->run();

        $after = KanaCharacter::all()
            ->map(fn ($k) => $k->script.'|'.$k->character.'|'.$k->type.'|'.$k->romaji);

        return $before->diff($after)->count() + max(0, $before->count() - $after->count());
    }

    /** The chart type a letter must have, judged from the letter itself. */
    private function expectedType(string $character): string
    {
        $dakuten = 'がぎぐげござじずぜぞだぢづでどばびぶべぼガギグゲゴザジズゼゾダヂヅデドバビブベボ';
        $handakuten = 'ぱぴぷぺぽパピプペポ';

        // Yoon: a consonant+i kana followed by a small ya / yu / yo.
        if (mb_strlen($character) === 2 && preg_match('/^[ゃゅょャュョ]$/u', mb_substr($character, 1, 1))) {
            $first = mb_substr($character, 0, 1);

            return str_contains($dakuten.$handakuten, $first) ? 'yoon_dakuten' : 'yoon';
        }

        return str_contains($handakuten, $character) ? 'handakuten'
            : (str_contains($dakuten, $character) ? 'dakuten' : 'gojuon');
    }

    /** Refuse to build lessons from a chart that is still inconsistent. */
    private function assertChartIsSane($chart): void
    {
        foreach ($chart as $k) {
            $expected = $this->expectedType($k->character);

            if ($k->type !== $expected) {
                throw new RuntimeException("Kana chart still inconsistent: {$k->character} is '{$k->type}', expected '{$expected}'.");
            }
        }

        $counts = $chart->groupBy('script')->map(fn ($g) => $g->countBy('type')->all());
        $expectedCounts = ['gojuon' => 46, 'dakuten' => 20, 'handakuten' => 5, 'yoon' => 21, 'yoon_dakuten' => 15];

        foreach (['hiragana', 'katakana'] as $script) {
            foreach ($expectedCounts as $type => $n) {
                if (($counts[$script][$type] ?? 0) !== $n) {
                    throw new RuntimeException("Kana chart for {$script} is not 46 / 20 / 5 / 21 / 15 (gojuon / dakuten / handakuten / yoon / yoon_dakuten).");
                }
            }
        }
    }

    /** One question per letter: shows the letter, asks for its reading. */
    private function buildQuiz(Lesson $lesson, $letters): int
    {
        $order = 0;

        foreach ($letters as $letter) {
            // Distractors: other letters of THIS lesson with a different, distinct reading
            // (じ/ぢ both read "ji": never offer the twin as a wrong option).
            $wrong = $letters
                ->where('romaji', '!=', $letter->romaji)
                ->pluck('romaji')
                ->unique()
                ->shuffle()
                ->take(3)
                ->values();

            if ($wrong->count() < 2) {
                continue;
            }

            $question = LessonQuestion::create([
                'lesson_id' => $lesson->id,
                'question_type' => 'multiple_choice',
                'prompt_id' => 'Bunyi apa karakter ini?',
                'prompt_en' => 'What sound does this character make?',
                'japanese_text' => $letter->character,
                'romaji' => null, // the reading is the answer: never print it
                'correct_answer' => $letter->romaji,
                'order' => ++$order,
                'difficulty' => 1,
                'is_active' => true,
            ]);

            foreach ($wrong->push($letter->romaji)->shuffle()->values() as $n => $label) {
                QuestionOption::create([
                    'lesson_question_id' => $question->id,
                    'label_id' => $label,
                    'label_en' => $label,
                    'is_correct' => $label === $letter->romaji,
                    'order' => $n + 1,
                ]);
            }
        }

        return $order;
    }
}
