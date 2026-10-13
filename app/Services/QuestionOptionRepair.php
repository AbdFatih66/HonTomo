<?php

namespace App\Services;

use App\Models\KanaCharacter;
use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\QuestionOption;
use Illuminate\Support\Collection;

/**
 * Safety net for lessons whose quiz reaches the player unanswerable.
 *
 * Runs when a lesson is opened and only touches what is broken:
 *
 *  1. A Hiragana/Katakana lesson that has flashcards but NO quiz at all gets a
 *     multiple-choice quiz built from its flashcards (kana <-> reading).
 *  2. A choice question whose answer options are missing, a single one, without
 *     exactly one correct flag, or with duplicated labels is rebuilt.
 *  3. A question with an unrecognised type that carries options is normalised
 *     to `multiple_choice` (the only type the player can grade by option).
 *
 * Question ids are kept, so user progress/attempts are untouched. Healthy
 * questions are never modified.
 */
class QuestionOptionRepair
{
    private const CHOICE_TYPES = ['multiple_choice', 'listening'];

    /** Types that never use answer options. */
    private const NON_CHOICE_TYPES = [
        'flashcard', 'grammar', 'typing', 'translation', 'matching',
        'sentence_ordering', 'writing', 'speaking',
    ];

    private const GENERATED_QUIZ_SIZE = 10;

    public function ensureForLesson(Lesson $lesson): void
    {
        $this->buildMissingKanaQuiz($lesson);

        $questions = $lesson->questions()
            ->whereNotIn('question_type', self::NON_CHOICE_TYPES)
            ->with('options')
            ->get();

        foreach ($questions as $question) {
            if ($this->isHealthy($question)) {
                $this->normaliseType($question);

                continue;
            }

            $this->repair($question, $lesson, $questions);
        }

        // Callers may have eager-loaded 'questions.options' before this ran;
        // drop the stale copy so the response reflects the repaired data.
        if ($lesson->relationLoaded('questions')) {
            $lesson->unsetRelation('questions');
        }
    }

    // ------------------------------------------------------------------
    // 1. Flashcards without a quiz
    // ------------------------------------------------------------------

    private function buildMissingKanaQuiz(Lesson $lesson): void
    {
        $questions = $lesson->questions()->with('options')->get();

        $hasChoice = $questions->contains(
            fn (LessonQuestion $q) => ! in_array($q->question_type, array_merge(self::NON_CHOICE_TYPES, ['grammar']), true)
        );

        if ($hasChoice) {
            return; // the lesson already has a quiz (repaired further down if broken)
        }

        $kanaTitled = (bool) preg_match('/hiragana|katakana/i', $lesson->category.' '.$lesson->title_id.' '.$lesson->title_en);

        // (letter, reading) pairs taken from the lesson's own flashcards, so
        // the quiz matches exactly what the learner just studied — including
        // combined letters (きゃ, シャ…) that are not in the basic chart.
        $pairs = $questions
            ->where('question_type', 'flashcard')
            ->map(fn (LessonQuestion $q) => [
                'jp' => trim((string) $q->japanese_text),
                'ro' => trim((string) ($q->romaji ?: $q->options->first()?->label_id ?: $q->correct_answer)),
            ])
            ->filter(fn ($p) => $p['ro'] !== '' && preg_match('/^[\p{Hiragana}\p{Katakana}ー]{1,3}$/u', $p['jp']))
            ->unique('jp')
            ->values();

        if ($pairs->count() >= 4) {
            $this->createQuizFromPairs($lesson, $pairs);

            return;
        }

        // Too few flashcards to build from (or none): fall back to the kana chart.
        $kanas = $pairs->map(fn ($p) => $this->kanaFromText($p['jp']))->filter()->unique('id')->values();

        if ($kanas->count() < 4 && $kanaTitled) {
            $kanas = $this->kanaForTitle($lesson);
        }

        if ($kanas->count() < 4) {
            return; // not a kana lesson, or nothing to build from
        }

        $order = (int) $lesson->questions()->max('order');

        foreach ($kanas->shuffle()->take(self::GENERATED_QUIZ_SIZE)->values() as $i => $kana) {
            // A reading shared by two letters (ji: じ/ぢ) can't name ONE letter.
            $sharedReading = KanaCharacter::where('script', $kana->script)->where('romaji', $kana->romaji)->count() > 1;
            $askForCharacter = $i % 2 === 1 && ! $sharedReading;

            LessonQuestion::create([
                'lesson_id' => $lesson->id,
                'question_type' => 'multiple_choice',
                'prompt_id' => $askForCharacter ? 'Karakter mana untuk bunyi ini?' : 'Bunyi apa karakter ini?',
                'prompt_en' => $askForCharacter ? 'Which character makes this sound?' : 'What sound does this character make?',
                'japanese_text' => $askForCharacter ? null : $kana->character,
                'romaji' => $askForCharacter ? $kana->romaji : null,
                'correct_answer' => $askForCharacter ? $kana->character : $kana->romaji,
                'order' => ++$order,
                'difficulty' => 1,
                'is_active' => true,
            ]);
        }
        // Options for these are created by the repair pass that follows.
    }

    /** Quiz + ready-made options from the lesson's own (letter, reading) pairs. */
    private function createQuizFromPairs(Lesson $lesson, Collection $pairs): void
    {
        $order = (int) $lesson->questions()->max('order');

        foreach ($pairs->shuffle()->take(self::GENERATED_QUIZ_SIZE)->values() as $i => $pair) {
            $sharedReading = $pairs->where('ro', $pair['ro'])->count() > 1;
            $askForCharacter = $i % 2 === 1 && ! $sharedReading;

            // Distractors differ from the answer and from each other in what is
            // shown; letters that share a reading with the target are skipped
            // (じ/ぢ both "ji") so there is never a second correct option.
            $others = $pairs->where('jp', '!=', $pair['jp'])->where('ro', '!=', $pair['ro']);
            $column = $askForCharacter ? 'jp' : 'ro';
            $answer = $pair[$column];

            $distractors = $others->pluck($column)->unique()->shuffle()->take(3)->values();

            if ($distractors->isEmpty()) {
                continue;
            }

            $question = LessonQuestion::create([
                'lesson_id' => $lesson->id,
                'question_type' => 'multiple_choice',
                'prompt_id' => $askForCharacter ? 'Karakter mana untuk bunyi ini?' : 'Bunyi apa karakter ini?',
                'prompt_en' => $askForCharacter ? 'Which character makes this sound?' : 'What sound does this character make?',
                'japanese_text' => $askForCharacter ? null : $pair['jp'],
                'romaji' => $askForCharacter ? $pair['ro'] : null,
                'correct_answer' => $answer,
                'order' => ++$order,
                'difficulty' => 1,
                'is_active' => true,
            ]);

            foreach ($distractors->push($answer)->shuffle()->values() as $n => $label) {
                QuestionOption::create([
                    'lesson_question_id' => $question->id,
                    'label_id' => $label,
                    'label_en' => $label,
                    'is_correct' => $label === $answer,
                    'order' => $n + 1,
                ]);
            }
        }
    }

    /** Letters a kana lesson is about, guessed from its title ("Hiragana: Tenten & Maru"). */
    private function kanaForTitle(Lesson $lesson): Collection
    {
        $text = mb_strtolower($lesson->category.' '.$lesson->title_id.' '.$lesson->title_en);
        $script = str_contains($text, 'katakana') ? 'katakana' : 'hiragana';

        $types = [];

        if (preg_match('/yoon|youon/', $text)) {
            $types[] = preg_match('/dakuten|bersuara|voiced/', $text) ? 'yoon_dakuten' : 'yoon';
        }
        else {
            if (preg_match('/tenten|dakuten/', $text)) {
                $types[] = 'dakuten';
            }

            if (preg_match('/maru/', $text)) {
                $types[] = 'handakuten';
            }
        }

        if ($types === []) {
            $types = ['gojuon'];
        }

        return KanaCharacter::where('script', $script)->whereIn('type', $types)->get();
    }

    // ------------------------------------------------------------------
    // 2 + 3. Broken / unusual choice questions
    // ------------------------------------------------------------------

    private function isHealthy(LessonQuestion $question): bool
    {
        $labels = $question->options->map(fn ($o) => mb_strtolower(trim((string) $o->label_id)));

        return $question->options->count() >= 2
            && $question->options->where('is_correct', true)->count() === 1
            && $labels->unique()->count() === $labels->count();
    }

    private function normaliseType(LessonQuestion $question): void
    {
        if (! in_array($question->question_type, LessonQuestion::TYPES, true)) {
            $question->update(['question_type' => 'multiple_choice']);
        }
    }

    private function repair(LessonQuestion $question, Lesson $lesson, Collection $siblings): void
    {
        $answer = $this->resolveAnswer($question, $lesson);

        if ($answer === null) {
            return; // nothing to build a right answer from
        }

        $distractors = $this->distractors($question, $siblings, $answer);

        if ($distractors->isEmpty()) {
            return;
        }

        QuestionOption::where('lesson_question_id', $question->id)->delete();

        $labels = $distractors->push($answer['label'])->shuffle()->values();

        foreach ($labels as $i => $label) {
            QuestionOption::create([
                'lesson_question_id' => $question->id,
                'label_id' => $label,
                'label_en' => $label,
                'is_correct' => $label === $answer['label'],
                'order' => $i + 1,
            ]);
        }

        $this->normaliseType($question);
    }

    /**
     * @return array{label: string, kind: string, kana: ?KanaCharacter}|null
     *   kind: 'romaji' (answer is a reading), 'char' (answer is a kana
     *   character) or 'other' (any other text, e.g. a word meaning)
     */
    private function resolveAnswer(LessonQuestion $question, Lesson $lesson): ?array
    {
        $stored = trim((string) $question->correct_answer);

        if ($stored === '') {
            $stored = trim((string) $question->options->firstWhere('is_correct', true)?->label_id);
        }

        if ($stored !== '') {
            $asChar = $this->kanaFromText($stored);

            if ($asChar) {
                return ['label' => $stored, 'kind' => 'char', 'kana' => $asChar];
            }

            $asRomaji = $this->kanaFromRomaji($stored, $this->scriptOf($question, $lesson));

            return [
                'label' => $stored,
                'kind' => $asRomaji && $this->kanaFromText($question->japanese_text) ? 'romaji' : 'other',
                'kana' => $asRomaji,
            ];
        }

        // No stored answer: derive it from the kana on the card.
        if ($kana = $this->kanaFromText($question->japanese_text)) {
            return ['label' => $kana->romaji, 'kind' => 'romaji', 'kana' => $kana];
        }

        $romaji = trim((string) $question->romaji);

        if ($romaji !== '' && ($kana = $this->kanaFromRomaji($romaji, $this->scriptOf($question, $lesson)))) {
            return ['label' => $kana->character, 'kind' => 'char', 'kana' => $kana];
        }

        return null;
    }

    private function distractors(LessonQuestion $question, Collection $siblings, array $answer): Collection
    {
        $label = $answer['label'];
        $kana = $answer['kana'];

        // Kana questions: distractors come from the kana chart so the options
        // are always the right KIND (readings vs. characters).
        if ($kana && in_array($answer['kind'], ['romaji', 'char'], true)) {
            $column = $answer['kind'] === 'romaji' ? 'romaji' : 'character';

            // Same script AND same kind of letter (basic / tenten / maru), so a
            // "ki" question never offers "gi" from a lesson not taught yet.
            return KanaCharacter::where('script', $kana->script)
                ->where('type', $kana->type)
                ->where('id', '!=', $kana->id)
                ->where('romaji', '!=', $kana->romaji) // じ/ぢ read the same: never offer both
                ->pluck($column)
                ->unique()
                ->shuffle()
                ->take(3)
                ->values();
        }

        return $siblings
            ->where('id', '!=', $question->id)
            ->map(fn (LessonQuestion $q) => trim((string) ($q->correct_answer ?: $q->options->firstWhere('is_correct', true)?->label_id)))
            ->filter(fn ($text) => $text !== '' && $text !== $label)
            ->unique()
            ->shuffle()
            ->take(3)
            ->values();
    }

    // ------------------------------------------------------------------
    // Kana lookups
    // ------------------------------------------------------------------

    /** @var Collection<string, KanaCharacter>|null */
    private ?Collection $chartByLetter = null;

    private function kanaFromText(?string $text): ?KanaCharacter
    {
        $text = trim((string) $text);

        if ($text === '' || mb_strlen($text) > 1) {
            return null;
        }

        // Compared in PHP, not SQL: MySQL's default collation treats か = が = カ as equal.
        $this->chartByLetter ??= KanaCharacter::all()->keyBy('character');

        $kana = $this->chartByLetter->get($text);

        return $kana && $kana->character === $text ? $kana : null;
    }

    private function kanaFromRomaji(string $romaji, string $script): ?KanaCharacter
    {
        return KanaCharacter::where('script', $script)
            ->where('romaji', mb_strtolower($romaji))
            ->orderBy('order')
            ->first();
    }

    private function scriptOf(LessonQuestion $question, Lesson $lesson): string
    {
        if (in_array($lesson->category, ['hiragana', 'katakana'], true)) {
            return $lesson->category;
        }

        return $this->kanaFromText($question->japanese_text)?->script ?? 'hiragana';
    }
}
