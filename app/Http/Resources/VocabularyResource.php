<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VocabularyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'japanese' => $this->japanese,
            'hiragana' => $this->hiragana,
            'romaji' => $this->romaji,
            'meaning' => $this->meaning(),
            'jlpt_level' => $this->jlpt_level,
            'audio_url' => $this->audio?->url(),
            'examples' => VocabularyExampleResource::collection($this->whenLoaded('examples')),
        ];
    }
}
