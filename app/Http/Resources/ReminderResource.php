<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReminderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => optional($this->due_date)->toDateString(),
            'completed_at' => optional($this->completed_at)->toDateTimeString(),
            'is_completed' => $this->is_completed,
            'created_by' => $this->relationLoaded('creator') && $this->creator ? [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ] : null,
            'created_at' => optional($this->created_at)->toDateTimeString(),
        ];
    }
}
