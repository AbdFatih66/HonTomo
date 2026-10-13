<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label(),
            'japanese_text' => $this->japanese_text,
            // is_correct intentionally NEVER exposed to the client
        ];
    }
}
