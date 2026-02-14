<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChargeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'period' => $this->period,
            'due_date' => optional($this->due_date)->format('Y-m-d'),
            'amount' => (float) $this->amount,
            'paid_amount' => (float) $this->paid_amount,
            'remaining' => (float) $this->remaining,
            'description' => $this->description,
            'status' => $this->status->value,
            'charge_type' => $this->charge_type?->value ?? 'aidat',
            'apartment' => $this->whenLoaded('apartment', fn () => $this->apartment ? [
                'id' => $this->apartment->id,
                'label' => $this->apartment->full_label,
            ] : null),
            'account' => $this->whenLoaded('account', fn () => $this->account ? [
                'id' => $this->account->id,
                'name' => $this->account->full_name,
            ] : null),
        ];
    }
}
