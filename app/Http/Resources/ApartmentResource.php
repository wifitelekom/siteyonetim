<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $owner = $this->relationLoaded('owners') ? $this->owners->first() : null;
        $tenant = $this->relationLoaded('tenants') ? $this->tenants->first() : null;

        $group = $this->relationLoaded('group') ? $this->group : null;
        $totalCharged = (float) ($this->total_charged ?? 0);
        $totalPaid = (float) ($this->total_paid ?? 0);

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
            'group' => $group ? [
                'id' => $group->id,
                'name' => $group->name,
            ] : null,
            'balance' => round($totalCharged - $totalPaid, 2),
        ];
    }
}
