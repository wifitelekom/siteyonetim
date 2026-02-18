<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $roles = $this->whenLoaded('roles', fn () => $this->roles->pluck('name')->values(), collect());
        $primaryRole = $roles->first();

        $payload = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'tc_kimlik' => $this->tc_kimlik,
            'address' => $this->address,
            'birth_date' => optional($this->birth_date)->toDateString(),
            'occupation' => $this->occupation,
            'education' => $this->education,
            'role' => $primaryRole,
            'role_label' => self::roleLabel($primaryRole),
            'roles' => $roles,
            'created_at' => optional($this->created_at)->toDateString(),
            'apartment_count' => (int) ($this->apartments_count ?? 0),
        ];

        if ($this->relationLoaded('apartments')) {
            $payload['apartments'] = $this->apartments->map(fn ($apartment) => [
                'id' => $apartment->id,
                'label' => $apartment->full_label,
                'relation_type' => $apartment->pivot?->relation_type,
                'relation_label' => $apartment->pivot?->relation_type === 'owner' ? 'Ev Sahibi' : 'Kiraci',
                'start_date' => $apartment->pivot?->start_date,
                'end_date' => $apartment->pivot?->end_date,
            ])->values();
            $payload['apartment_count'] = $payload['apartments']->count();
        }

        return $payload;
    }

    public static function roleLabel(?string $role): string
    {
        return match ($role) {
            'admin' => 'Yonetici',
            'owner' => 'Ev Sahibi',
            'tenant' => 'Kiraci',
            'vendor' => 'Tedarikci',
            default => 'Rol Yok',
        };
    }
}
