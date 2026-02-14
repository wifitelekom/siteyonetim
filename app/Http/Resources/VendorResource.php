<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tax_no' => $this->tax_no,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'is_active' => (bool) $this->is_active,
            'expenses_count' => (int) ($this->expenses_count ?? 0),
        ];
    }
}
