<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\QuestionOption;
use App\Models\Vocabulary;
use App\Models\VocabularyCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class LessonQuestionSeeder extends Seeder
{
    /**
     * Generates one multiple_choice question per vocabulary word in each
     * lesson: "What does 「word」mean?" with 1 correct answer + 3 distractors
     * pulled from the same word pool. Prompts/options are generated text,
     * not copied from any book.
     */
    public function run(): void
    {
        $this->buildForLesson(
            titleOrder: [1, 1], // unit order 1, lesson order 1
            categorySlugs: ['people-professions', 'pelajaran-1-ungkapan'],
        );

        $this->buildForLesson(
            titleOrder: [1, 2],
            categorySlugs: ['countries'],
        );

        $this->buildForLesson(
            titleOrder: [2, 1],
            categorySlugs: ['demonstratives'],
        );

        $this->buildForLesson(
            titleOrder: [2, 2],
            categorySlugs: ['everyday-objects', 'languages-terms', 'pelajaran-2-ungkapan'],
        );

        // Pelajaran 3-25: each is Unit N / Lesson 1, category slug "pelajaran-N"
        // (see ExtendedCurriculumSeeder), one MC question per vocab word.
        for ($n = 3; $n <= 25; $n++) {
            $this->buildForLesson(
                titleOrder: [$n, 1],
                categorySlugs: ["pelajaran-{$n}"],
            );
        }
    }

    private function buildForLesson(array $titleOrder, array $categorySlugs): void
    {
        [$unitOrder, $lessonOrder] = $titleOrder;

        $lesson = Lesson::whereHas('unit', fn ($q) => $q->where('order', $unitOrder))
            ->where('order', $lessonOrder)
            ->first();

        if (! $lesson) {
            return;
        }

        $categoryIds = VocabularyCategory::whereIn('slug', $categorySlugs)->pluck('id');
        $words = Vocabulary::whereIn('category_id', $categoryIds)->get();

        if ($words->count() < 4) {
            return; // not enough words to build meaningful distractors
        }

        foreach ($words as $index => $word) {
            $question = LessonQuestion::firstOrCreate(
                [
                    'lesson_id' => $lesson->id,
                    'vocabulary_id' => $word->id,
                    'question_type' => 'multiple_choice',
                ],
                [
                    'prompt_id' => 'Apa arti kata ini?',
                    'prompt_en' => 'What does this word mean?',
                    'japanese_text' => $word->japanese,
                    'romaji' => $word->romaji,
                    'audio_id' => $word->audio_id,
                    'correct_answer' => (string) $word->meaning_id,
                    'order' => $index + 1,
                    'difficulty' => 1,
                    'is_active' => true,
                ]
            );

            // Rebuild the options when the question is new, and also when an
            // older run left it with duplicate or stale answer text.
            if (! $question->wasRecentlyCreated && ! $this->needsRepair($question, $word)) {
                continue;
            }

            $this->writeOptions($question, $words, $word);
        }
    }

    /**
     * True when the stored options no longer hold exactly four distinct
     * labels, or when the correct option's text drifted from the word's
     * current meaning (e.g. after a gloss was disambiguated).
     */
    private function needsRepair(LessonQuestion $question, Vocabulary $word): bool
    {
        $options = $question->options()->get();

        if ($options->count() !== 4) {
            return true;
        }

        if ($options->pluck('label_id')->map(fn ($l) => trim((string) $l))->unique()->count() !== 4) {
            return true;
        }

        $correct = $options->firstWhere('is_correct', true);

        return $correct === null || $correct->label_id !== $word->meaning_id;
    }

    /**
     * Replaces this question's option rows in place. The question row itself
     * (and therefore its id, and any user answer history pointing at it) is
     * left untouched.
     */
    private function writeOptions(LessonQuestion $question, Collection $words, Vocabulary $word): void
    {
        $distractors = $this->pickDistractors($words, $word, 3);

        if ($distractors->count() < 3) {
            return; // not enough distinct meanings — leave what is there alone
        }

        $question->options()->delete();

        $question->update(['correct_answer' => (string) $word->meaning_id]);

        $optionPool = $distractors->push($word)->shuffle();

        foreach ($optionPool->values() as $optIndex => $optionWord) {
            QuestionOption::create([
                'lesson_question_id' => $question->id,
                'label_id' => $optionWord->meaning_id,
                'label_en' => $optionWord->meaning_en,
                'is_correct' => $optionWord->id === $word->id,
                'order' => $optIndex + 1,
            ]);
        }
    }

    private function pickDistractors(Collection $pool, Vocabulary $correct, int $count): Collection
    {
        // Two separate traps here, both of which used to show the same answer
        // text twice:
        //   1. a distractor whose meaning equals the correct answer's
        //      (先生/教師 both read "guru, dosen" before the glosses were split);
        //   2. two distractors that duplicate EACH OTHER — the old filter only
        //      compared against the correct word, never between distractors.
        // unique('meaning_id') closes the second one; comparing the trimmed
        // label keeps whitespace-only differences from slipping through.
        $correctLabel = trim((string) $correct->meaning_id);

        return $pool->where('id', '!=', $correct->id)
            ->reject(fn (Vocabulary $w) => trim((string) $w->meaning_id) === $correctLabel)
            ->unique(fn (Vocabulary $w) => trim((string) $w->meaning_id))
            ->shuffle()
            ->take($count)
            ->values();
    }
}
