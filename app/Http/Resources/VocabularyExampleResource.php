<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VocabularyExampleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'sentence_japanese' => $this->sentence_japanese,
            'sentence_reading' => $this->sentence_reading,
            'translation' => $this->translation(),
        ];
    }
}
