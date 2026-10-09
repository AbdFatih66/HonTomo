<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KanaCharacter;
use Illuminate\Http\Request;

class KanaController extends Controller
{
    /**
     * Full chart for one script, grouped by row, ready for the chart UI.
     * GET /api/kana?script=hiragana
     */
    public function index(Request $request)
    {
        $script = $request->query('script', 'hiragana');

        $characters = KanaCharacter::where('script', $script)
            ->orderBy('type')
            ->orderBy('order')
            ->get()
            ->map(fn (KanaCharacter $k) => $this->present($k));

        return response()->json([
            'script' => $script,
            'gojuon' => $characters->where('type', 'gojuon')->values(),
            'dakuten' => $characters->where('type', 'dakuten')->values(),
            'handakuten' => $characters->where('type', 'handakuten')->values(),
            'yoon' => $characters->where('type', 'yoon')->values(),
            'yoon_dakuten' => $characters->where('type', 'yoon_dakuten')->values(),
        ]);
    }

    /**
     * Single character detail (stroke order + usage note) for the writing
     * practice screen.
     * GET /api/kana/{kanaCharacter}
     */
    public function show(KanaCharacter $kanaCharacter)
    {
        return response()->json($this->present($kanaCharacter, withSteps: true));
    }

    /**
     * A batch of quiz questions mixing multiple-choice recognition with
     * "write it yourself" prompts, per ?script=&count=&write_ratio=
     * GET /api/kana/quiz
     */
    public function quiz(Request $request)
    {
        $script = $request->query('script', 'hiragana');
        $count = max(1, min(30, (int) $request->query('count', 10)));
        $writeRatio = max(0, min(1, (float) $request->query('write_ratio', 0.3)));
        $types = array_filter(explode(',', $request->query('types', 'gojuon,dakuten,handakuten,yoon,yoon_dakuten')));

        $pool = KanaCharacter::where('script', $script)
            ->whereIn('type', $types)
            ->get();

        if ($pool->count() < 4) {
            return response()->json(['questions' => []]);
        }

        $picked = $pool->shuffle()->take($count);

        // Letters that share a reading (じ/ぢ = "ji", ず/づ = "zu"): a romaji
        // prompt or a romaji answer would be ambiguous for them, so they are
        // only ever asked as "which reading is this character?".
        $sharedReadings = $pool->groupBy('romaji')
            ->filter(fn ($group) => $group->count() > 1)
            ->keys();

        $questions = $picked->map(function (KanaCharacter $target) use ($pool, $writeRatio, $sharedReadings) {
            $ambiguous = $sharedReadings->contains($target->romaji);

            // Roughly writeRatio of the questions are "write it yourself";
            // the rest are multiple-choice in either direction.
            $isWriting = ! $ambiguous && (mt_rand(1, 100) / 100) <= $writeRatio;

            if ($isWriting) {
                return [
                    'mode' => 'write',
                    'id' => $target->id,
                    'prompt_character' => null,
                    'prompt_romaji' => $target->romaji,
                    'answer_character' => $target->character,
                    'answer_romaji' => $target->romaji,
                    'stroke_order' => $target->strokeOrder(),
                    'options' => [],
                ];
            }

            // Alternate direction: sometimes show the kana and ask for the
            // reading, sometimes show the romaji and ask for the kana.
            $askForCharacter = ! $ambiguous && mt_rand(0, 1) === 1;

            // Distractors must differ from the answer AND from each other in
            // what is displayed, otherwise the learner sees the same option
            // twice (or two correct ones).
            $distractors = $pool
                ->where('id', '!=', $target->id)
                ->where('romaji', '!=', $target->romaji)
                ->unique($askForCharacter ? 'character' : 'romaji')
                ->shuffle()
                ->take(3);

            $options = $askForCharacter
                ? $distractors->push($target)->pluck('character')
                : $distractors->push($target)->pluck('romaji');

            return [
                'mode' => 'choice',
                'id' => $target->id,
                'prompt_character' => $askForCharacter ? null : $target->character,
                'prompt_romaji' => $askForCharacter ? $target->romaji : null,
                'answer_character' => $target->character,
                'answer_romaji' => $target->romaji,
                'stroke_order' => null,
                'options' => $options->unique()->shuffle()->values(),
            ];
        })->values();

        return response()->json(['script' => $script, 'questions' => $questions]);
    }

    private function present(KanaCharacter $k, bool $withSteps = false): array
    {
        $data = [
            'id' => $k->id,
            'script' => $k->script,
            'character' => $k->character,
            'romaji' => $k->romaji,
            'type' => $k->type,
            'row' => $k->row,
            'column' => $k->column,
            'base_character_id' => $k->base_character_id,
            'stroke_count' => $k->stroke_count,
            'usage_note' => $k->usageNote(),
        ];

        if ($withSteps) {
            $data['stroke_order'] = $k->strokeOrder();
        }

        return $data;
    }
}
