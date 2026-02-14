<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $owner = $this->whenLoaded('owners', fn () => $this->owners->first());
        $tenant = $this->whenLoaded('tenants', fn () => $this->tenants->first());

        return [
            'id' => $this->id,
            'block' => $this->block,
            'floor' => $this->floor,
            'number' => $this->number,
            'm2' => $this->m2 !== null ? (float) $this->m2 : null,
            'arsa_payi' => $this->arsa_payi !== null ? (float) $this->arsa_payi : null,
            'is_active' => (bool) $this->is_active,
            'full_label' => $this->full_label,
            'resident_count' => (int) ($this->users_count ?? 0),
            'current_owner' => $owner ? [
                'id' => $owner->id,
                'name' => $owner->name,
            ] : null,
            'current_tenant' => $tenant ? [
                'id' => $tenant->id,
                'name' => $tenant->name,
            ] : null,
        ];
    }
}
