<?php

namespace App\Services;

use App\Models\LessonQuestion;
use Illuminate\Support\Str;

class QuestionService
{
    /**
     * Grade a submitted answer against a question's correct answer / payload.
     * Each question_type has its own comparison rules.
     */
    public function grade(LessonQuestion $question, mixed $answer): bool
    {
        return match ($question->question_type) {
            'multiple_choice', 'listening' => $this->gradeChoice($question, $answer),
            'typing', 'translation' => $this->gradeText($question, $answer),
            'matching' => $this->gradeMatching($question, $answer),
            'sentence_ordering' => $this->gradeOrdering($question, $answer),
            'flashcard' => true, // self-graded by the user ("I knew it" / "I didn't")
            'grammar' => true, // explanation card — reading it through counts as "answered"
            'writing', 'speaking' => true, // requires manual/AI review in a later phase
            default => false,
        };
    }

    /**
     * What the learner should have answered — shown in the feedback bar after
     * they check. Only ever called AFTER an answer has been graded.
     *
     * @return array{correct_option_id: ?int, correct_answer: ?string}
     */
    public function revealCorrectAnswer(LessonQuestion $question): array
    {
        if (in_array($question->question_type, ['multiple_choice', 'listening'], true)) {
            $option = $question->options()->where('is_correct', true)->first();

            return [
                'correct_option_id' => $option?->id,
                'correct_answer' => $option
                    ? trim(($option->japanese_text ? $option->japanese_text.' ' : '').$option->label())
                    : null,
            ];
        }

        $accepted = $question->payload['accepted_answers'] ?? [];

        return [
            'correct_option_id' => null,
            'correct_answer' => $question->correct_answer ?: ($accepted[0] ?? null),
        ];
    }

    private function gradeChoice(LessonQuestion $question, mixed $answer): bool
    {
        $correct = $question->options()->where('is_correct', true)->first();

        return $correct && (string) $correct->id === (string) $answer;
    }

    private function gradeText(LessonQuestion $question, mixed $answer): bool
    {
        $accepted = collect($question->payload['accepted_answers'] ?? [$question->correct_answer])
            ->filter()
            ->map(fn ($a) => Str::lower(trim($a)));

        return $accepted->contains(Str::lower(trim((string) $answer)));
    }

    private function gradeMatching(LessonQuestion $question, mixed $answer): bool
    {
        // Expected $answer: ["left_id" => "right_id", ...]
        // Expected payload:  ["pairs" => [["left" => .., "right" => ..], ...]]
        $pairs = collect($question->payload['pairs'] ?? []);

        if (! is_array($answer) || $pairs->count() !== count($answer)) {
            return false;
        }

        foreach ($pairs as $pair) {
            if (($answer[$pair['left']] ?? null) !== $pair['right']) {
                return false;
            }
        }

        return true;
    }

    private function gradeOrdering(LessonQuestion $question, mixed $answer): bool
    {
        $correctOrder = $question->payload['correct_order'] ?? [];

        return is_array($answer) && $answer === $correctOrder;
    }
}
