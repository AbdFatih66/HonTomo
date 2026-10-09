<?php

namespace Tests\Feature\Regression;

use App\Models\Lesson;
use App\Models\Level;
use App\Services\KanaLessonFixer;
use Database\Seeders\KanaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Hiragana & Katakana unit of the learning path has ten lessons:
 * Dasar / Dakuten / Handakuten / Yoon / Yoon Dakuten for each script.
 */
class KanaLessonFixerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_ten_lessons_with_the_right_letters(): void
    {
        Level::create(['code' => 'N5', 'name_id' => 'N5', 'name_en' => 'N5', 'order' => 1, 'is_active' => true]);
        $this->seed(KanaSeeder::class);

        app(KanaLessonFixer::class)->run();

        $lessons = Lesson::withCount('questions')->orderBy('order')->get();

        $this->assertSame([
            'Hiragana Dasar (Gojuuon)' => 46,
            'Hiragana: Dakuten' => 20,
            'Hiragana: Handakuten' => 5,
            'Hiragana: Yoon' => 21,
            'Hiragana: Yoon Dakuten' => 15,
            'Katakana Dasar (Gojuuon)' => 46,
            'Katakana: Dakuten' => 20,
            'Katakana: Handakuten' => 5,
            'Katakana: Yoon' => 21,
            'Katakana: Yoon Dakuten' => 15,
        ], $lessons->pluck('questions_count', 'title_id')->all());

        // Each lesson unlocks after the one before it.
        foreach ($lessons->values() as $i => $lesson) {
            $this->assertSame($i === 0 ? null : $lessons[$i - 1]->id, $lesson->prerequisite_lesson_id);
        }
    }

    public function test_running_it_twice_keeps_ten_lessons(): void
    {
        Level::create(['code' => 'N5', 'name_id' => 'N5', 'name_en' => 'N5', 'order' => 1, 'is_active' => true]);
        $this->seed(KanaSeeder::class);

        app(KanaLessonFixer::class)->run();
        $ids = Lesson::orderBy('order')->pluck('id')->all();

        app(KanaLessonFixer::class)->run();

        $this->assertSame($ids, Lesson::orderBy('order')->pluck('id')->all()); // reused, so learner progress is kept
    }
}
