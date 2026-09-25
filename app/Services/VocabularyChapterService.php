<?php

namespace App\Services;

use App\Models\Unit;
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
        1 => ['people-professions', 'countries'],
        2 => ['demonstratives', 'everyday-objects', 'languages-terms'],
    ];

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
    public function chapters(): Collection
    {
        $units = Unit::whereHas('level', fn ($q) => $q->where('code', 'N5'))
            ->whereBetween('order', [1, 25])
            ->orderBy('order')
            ->get(['order', 'title_id', 'title_en']);

        $countsByCategory = Vocabulary::where('is_active', true)
            ->selectRaw('category_id, count(*) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        return $units->map(function ($unit) use ($countsByCategory) {
            $ids = $this->categoryIds($unit->order);
            $count = collect($ids)->sum(fn ($id) => $countsByCategory[$id] ?? 0);

            return [
                'order' => $unit->order,
                'title_id' => $unit->title_id,
                'title_en' => $unit->title_en,
                'count' => (int) $count,
            ];
        });
    }
}
