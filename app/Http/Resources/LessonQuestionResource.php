<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question_type' => $this->question_type,
            'prompt' => $this->prompt(),
            'japanese_text' => $this->japanese_text,
            'romaji' => $this->romaji,
            'audio_url' => $this->audio?->url(),
            'payload' => $this->payload,
            'options' => QuestionOptionResource::collection($this->whenLoaded('options')),
            // correct_answer intentionally NEVER exposed to the client
        ];
    }
}
