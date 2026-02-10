<?php

namespace App\Models;

use App\Traits\BelongsToSite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Apartment extends Model
{
    use HasFactory, SoftDeletes, BelongsToSite;

    protected $fillable = [
        'site_id',
        'block',
        'floor',
        'number',
        'm2',
        'arsa_payi',
        'is_active',
    ];

    protected $casts = [
        'm2' => 'decimal:2',
        'arsa_payi' => 'decimal:6',
        'is_active' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('relation_type', 'start_date', 'end_date')
            ->withTimestamps();
    }

    public function owners(): BelongsToMany
    {
        return $this->users()->wherePivot('relation_type', 'owner');
    }

    public function tenants(): BelongsToMany
    {
        return $this->users()->wherePivot('relation_type', 'tenant');
    }

    public function charges(): HasMany
    {
        return $this->hasMany(Charge::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function getFullLabelAttribute(): string
    {
        $parts = [];
        if ($this->block) {
            $parts[] = $this->block . ' Blok';
        }
        if ($this->floor) {
            $parts[] = 'Kat ' . $this->floor;
        }
        $parts[] = 'No ' . $this->number;
        return implode(' ', $parts);
    }

    public function getCurrentOwnerAttribute(): ?User
    {
        return $this->owners()->first();
    }

    public function getCurrentTenantAttribute(): ?User
    {
        return $this->tenants()
            ->where(function ($q) {
                $q->whereNull('apartment_user.end_date')
                    ->orWhere('apartment_user.end_date', '>=', now());
            })
            ->first();
    }
}
