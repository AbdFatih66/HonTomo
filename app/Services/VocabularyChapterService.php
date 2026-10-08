<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserLesson;
use App\Models\Vocabulary;
use App\Models\VocabularyCategory;
use Illuminate\Support\Collection;

/**
 * Maps each curriculum chapter (Unit.order, "Pelajaran 1..25") to the
 * VocabularyCategory row(s) that hold its words.
 *
 * Pelajaran 3-25 each got their own single category with slug
 * "pelajaran-{order}" (see ExtendedCurriculumSeeder). Pelajaran 1 and 2
 * predate that convention (VocabularySeeder / MissingVocabularySeeder) and
 * spread their words across several older, topic-named categories, so
 * those two are listed explicitly here instead.
 */
class VocabularyChapterService
{
    private const LEGACY_CHAPTER_SLUGS = [
        1 => ['people-professions', 'countries', 'pelajaran-1-ungkapan'],
        2 => ['demonstratives', 'everyday-objects', 'languages-terms', 'pelajaran-2-ungkapan'],
    ];

    /**
     * Which quiz lesson (Lesson.order inside the chapter's unit) quizzes which
     * category. Mirrors LessonQuestionSeeder. Chapters not listed here have a
     * single quiz lesson (order 1) for category "pelajaran-{n}".
     */
    private const QUIZ_LESSON_SLUGS = [
        1 => [1 => ['people-professions', 'pelajaran-1-ungkapan'], 2 => ['countries']],
        2 => [1 => ['demonstratives'], 2 => ['everyday-objects', 'languages-terms', 'pelajaran-2-ungkapan']],
    ];

    /** @return array<int, array<int, string>> lesson order => category slugs quizzed by that lesson */
    public function quizLessonSlugs(int $chapter): array
    {
        return self::QUIZ_LESSON_SLUGS[$chapter] ?? [1 => ["pelajaran-{$chapter}"]];
    }

    /** The vocabulary quiz lessons of a chapter (Pelajaran n), in order. */
    public function quizLessons(int $chapter): Collection
    {
        return Lesson::where('category', 'vocabulary')
            ->whereHas('unit', fn ($q) => $q->where('order', $chapter)
                ->whereHas('level', fn ($l) => $l->where('code', 'N5')))
            ->orderBy('order')
            ->get();
    }

    /** @return array<int, string> category slugs that belong to this chapter */
    public function categorySlugs(int $chapter): array
    {
        return self::LEGACY_CHAPTER_SLUGS[$chapter] ?? ["pelajaran-{$chapter}"];
    }

    /** @return array<int, int> VocabularyCategory ids that belong to this chapter */
    public function categoryIds(int $chapter): array
    {
        return VocabularyCategory::whereIn('slug', $this->categorySlugs($chapter))
            ->pluck('id')
            ->all();
    }

    /**
     * One row per Unit (order 1-25) in the N5 learning path, with how many
     * active vocabulary words that chapter has. Units without any mapped
     * vocabulary yet are still listed (count 0) so the menu shows the full
     * 1-25 range.
     *
     * @return Collection<int, array{order:int,title_id:string,title_en:string,count:int}>
     */
    public function chapters(?User $user = null): Collection
    {
        $units = Unit::whereHas('level', fn ($q) => $q->where('code', 'N5'))
            ->whereBetween('order', [1, 25])
            ->orderBy('order')
            ->get(['id', 'order', 'title_id', 'title_en']);

        $countsByCategory = Vocabulary::where('is_active', true)
            ->selectRaw('category_id, count(*) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $quizLessons = Lesson::where('category', 'vocabulary')
            ->where('is_active', true)
            ->whereIn('unit_id', Unit::whereHas('level', fn ($q) => $q->where('code', 'N5'))
                ->whereBetween('order', [1, 25])->pluck('id'))
            ->withCount(['questions' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('order')
            ->get()
            ->groupBy('unit_id');

        $statuses = $user
            ? UserLesson::where('user_id', $user->id)->pluck('status', 'lesson_id')
            : collect();

        return $units->map(function ($unit) use ($countsByCategory, $quizLessons, $statuses) {
            $ids = $this->categoryIds($unit->order);
            $count = collect($ids)->sum(fn ($id) => $countsByCategory[$id] ?? 0);

            return [
                'order' => $unit->order,
                'title_id' => $unit->title_id,
                'title_en' => $unit->title_en,
                'count' => (int) $count,
                'quiz' => ($quizLessons[$unit->id] ?? collect())
                    ->filter(fn ($l) => $l->questions_count > 0)
                    ->map(fn ($l) => [
                        'id' => $l->id,
                        'title' => $l->title(),
                        'question_count' => (int) $l->questions_count,
                        'status' => $statuses[$l->id] ?? null,
                    ])->values()->all(),
            ];
        });
    }
}
