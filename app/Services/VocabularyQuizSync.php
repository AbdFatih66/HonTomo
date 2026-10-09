<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\LessonQuestion;
use App\Models\Unit;
use App\Models\QuestionOption;
use App\Models\Vocabulary;
use App\Models\VocabularyCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Keeps the Kosakata list (Vocabulary words per Pelajaran) and the vocabulary
 * quiz (multiple-choice LessonQuestions in the chapter's vocabulary lessons)
 * in step, in both directions:
 *
 *  - a listed word without a quiz question gets one (with four options);
 *  - a quiz question that has no word in the list gets that word added to the
 *    chapter's list, and a quiz question whose word is hidden (inactive, no
 *    category, or filed outside every chapter) is made visible in the list.
 *
 * Additive and repeatable: nothing is deleted and existing questions (and the
 * learner progress attached to them) are left alone.
 *
 * Proper-noun lists ("pelajaran-N-nama") are deliberately in neither place.
 */
class VocabularyQuizSync
{
    /** JLPT level whose chapters are being synced (N5 = Pelajaran 1-25, N4 = its own units). */
    private string $level = 'N5';

    public function __construct(private VocabularyChapterService $chapters) {}

    /** @return list<int> chapter numbers (Unit.order) of the level being synced */
    private function chapterNumbers(): array
    {
        if ($this->level === 'N5') {
            return range(1, 25);
        }

        return Unit::whereHas('level', fn ($q) => $q->where('code', $this->level))
            ->orderBy('order')->pluck('order')->map(fn ($o) => (int) $o)->all();
    }

    /**
     * @return array{questions_added:int, words_added:int, words_fixed:int, questions_reactivated:int, chapters_without_quiz:list<int>}
     */
    public function run(string $level = 'N5'): array
    {
        $this->level = $level;

        return DB::transaction(function () {
            $summary = [
                'questions_added' => 0, 'words_added' => 0, 'words_fixed' => 0,
                'questions_reactivated' => 0, 'chapters_without_quiz' => [],
            ];

            $numbers = $this->chapterNumbers();

            $allChapterCategoryIds = collect($numbers)
                ->flatMap(fn ($n) => $this->chapters->categoryIds($n, $this->level))
                ->unique()->all();

            foreach ($numbers as $chapter) {
                $lessons = $this->chapters->quizLessons($chapter, $this->level)->keyBy('order');

                if ($lessons->isEmpty()) {
                    $summary['chapters_without_quiz'][] = $chapter;

                    continue;
                }

                $this->quizToList($chapter, $lessons, $allChapterCategoryIds, $summary);
                $this->listToQuiz($chapter, $lessons, $summary);
            }

            return $summary;
        });
    }

    // ------------------------------------------------------------------
    // quiz -> list
    // ------------------------------------------------------------------

    private function quizToList(int $chapter, Collection $lessons, array $allChapterCategoryIds, array &$summary): void
    {
        $questions = LessonQuestion::whereIn('lesson_id', $lessons->pluck('id'))
            ->where('question_type', 'multiple_choice')
            ->get();

        foreach ($questions as $question) {
            $lesson = $lessons->firstWhere('id', $question->lesson_id);
            $category = $this->categoryForLesson($chapter, (int) $lesson->order);

            if ($question->vocabulary_id === null) {
                $japanese = trim((string) $question->japanese_text);
                $meaning = trim((string) $question->correct_answer);

                if ($japanese === '' || $meaning === '') {
                    continue; // not a word-meaning question
                }

                // Exact match in PHP: MySQL's unicode_ci treats か = が = カ as equal.
                $word = Vocabulary::where('japanese', $japanese)->get()
                    ->first(fn ($v) => $v->japanese === $japanese && in_array($v->category_id, $this->chapters->categoryIds($chapter, $this->level), true));

                if (! $word) {
                    $word = Vocabulary::create([
                        'japanese' => $japanese,
                        'hiragana' => $japanese,
                        'romaji' => (string) $question->romaji,
                        'meaning_id' => $meaning,
                        'meaning_en' => $meaning,
                        'category_id' => $category->id,
                        'jlpt_level' => $this->level,
                        'difficulty' => 1,
                        'is_active' => true,
                    ]);
                    $summary['words_added']++;
                }

                $question->update(['vocabulary_id' => $word->id]);

                continue;
            }

            $word = $question->vocabulary;

            if (! $word) {
                continue;
            }

            $hidden = ! $word->is_active
                || $word->category_id === null
                || ! in_array($word->category_id, $allChapterCategoryIds, true);

            if ($hidden) {
                $word->update([
                    'is_active' => true,
                    'category_id' => in_array($word->category_id, $allChapterCategoryIds, true)
                        ? $word->category_id
                        : $category->id,
                ]);
                $summary['words_fixed']++;
            }
        }
    }

    // ------------------------------------------------------------------
    // list -> quiz
    // ------------------------------------------------------------------

    private function listToQuiz(int $chapter, Collection $lessons, array &$summary): void
    {
        $existing = LessonQuestion::whereIn('lesson_id', $lessons->pluck('id'))
            ->whereNotNull('vocabulary_id')
            ->where('question_type', 'multiple_choice')
            ->get()
            ->groupBy('vocabulary_id');

        foreach ($this->chapters->quizLessonSlugs($chapter, $this->level) as $lessonOrder => $slugs) {
            $lesson = $lessons->get($lessonOrder) ?? $lessons->first();
            $categoryIds = VocabularyCategory::whereIn('slug', $slugs)->pluck('id');

            $words = Vocabulary::where('is_active', true)
                ->whereIn('category_id', $categoryIds)
                ->orderBy('id')
                ->get();

            $order = (int) LessonQuestion::where('lesson_id', $lesson->id)->max('order');

            foreach ($words as $word) {
                $have = $existing->get($word->id);

                if ($have !== null) {
                    if ($have->where('is_active', true)->isEmpty()) {
                        $have->first()->update(['is_active' => true]);
                        $summary['questions_reactivated']++;
                    }

                    continue;
                }

                $distractors = $this->distractors($word, $words, $chapter);

                if ($distractors->count() < 3) {
                    continue; // not enough distinct meanings anywhere to build a fair question
                }

                $question = LessonQuestion::create([
                    'lesson_id' => $lesson->id,
                    'vocabulary_id' => $word->id,
                    'question_type' => 'multiple_choice',
                    'prompt_id' => 'Apa arti kata ini?',
                    'prompt_en' => 'What does this word mean?',
                    'japanese_text' => $word->japanese,
                    'romaji' => $word->romaji,
                    'audio_id' => $word->audio_id,
                    'correct_answer' => (string) $word->meaning_id,
                    'order' => ++$order,
                    'difficulty' => 1,
                    'is_active' => true,
                ]);

                foreach ($distractors->push($word)->shuffle()->values() as $i => $option) {
                    QuestionOption::create([
                        'lesson_question_id' => $question->id,
                        'label_id' => $option->meaning_id,
                        'label_en' => $option->meaning_en,
                        'is_correct' => $option->id === $word->id,
                        'order' => $i + 1,
                    ]);
                }

                $summary['questions_added']++;
            }
        }
    }

    /** Three wrong options with distinct meanings: same lesson first, then the chapter, then any word of the same level. */
    private function distractors(Vocabulary $word, Collection $lessonWords, int $chapter): Collection
    {
        $correct = trim((string) $word->meaning_id);

        $pick = function (Collection $pool, Collection $taken) use ($word, $correct) {
            $seen = $taken->map(fn ($w) => trim((string) $w->meaning_id))->push($correct);

            return $pool->where('id', '!=', $word->id)->shuffle()
                ->filter(function (Vocabulary $w) use (&$seen) {
                    $label = trim((string) $w->meaning_id);

                    if ($label === '' || $seen->contains($label)) {
                        return false;
                    }
                    $seen->push($label);

                    return true;
                });
        };

        $chosen = $pick($lessonWords, collect())->take(3)->values();

        if ($chosen->count() < 3) {
            $chapterWords = Vocabulary::where('is_active', true)
                ->whereIn('category_id', $this->chapters->categoryIds($chapter, $this->level))->get();
            $chosen = $chosen->concat($pick($chapterWords, $chosen)->take(3 - $chosen->count()))->values();
        }

        if ($chosen->count() < 3) {
            $anyLevel = Vocabulary::where('is_active', true)->where('jlpt_level', $this->level)->inRandomOrder()->limit(60)->get();
            $chosen = $chosen->concat($pick($anyLevel, $chosen)->take(3 - $chosen->count()))->values();
        }

        return $chosen;
    }

    private function categoryForLesson(int $chapter, int $lessonOrder): VocabularyCategory
    {
        $slugs = $this->chapters->quizLessonSlugs($chapter, $this->level)[$lessonOrder]
            ?? $this->chapters->quizLessonSlugs($chapter, $this->level)[1];
        $slug = $slugs[0];

        return VocabularyCategory::firstOrCreate(
            ['slug' => $slug],
            $this->level === 'N5'
                ? ['name_id' => "Kosakata Pelajaran {$chapter}", 'name_en' => "Lesson {$chapter} Vocabulary"]
                : ['name_id' => "Kosakata {$this->level} Pelajaran {$chapter}", 'name_en' => "{$this->level} Lesson {$chapter} Vocabulary"],
        );
    }
}
