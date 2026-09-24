<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title(),
            'category' => $this->category,
            'xp_reward' => $this->xp_reward,
            'order' => $this->order,
            // populated when eager-loaded with the current user's UserLesson
            'status' => $this->whenPivotLoaded('user_lessons', fn () => $this->pivot->status),
            'question_count' => $this->whenCounted('questions'),
        ];
    }
}
