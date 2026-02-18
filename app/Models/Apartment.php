<?php

namespace App\Models;

use App\Traits\BelongsToSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Apartment extends Model
{
    use HasFactory, SoftDeletes, BelongsToSite;

    private static ?bool $apartmentUserHasFamilyRoleColumn = null;

    protected $fillable = [
        'site_id',
        'apartment_group_id',
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

    public function group(): BelongsTo
    {
        return $this->belongsTo(ApartmentGroup::class, 'apartment_group_id');
    }

    public function users(): BelongsToMany
    {
        $relation = $this->belongsToMany(User::class)
            ->withPivot('relation_type', 'start_date', 'end_date')
            ->withTimestamps();

        if (self::hasApartmentUserFamilyRoleColumn()) {
            $relation->withPivot('family_role');
        }

        return $relation;
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

    public function scopeOrderedForDisplay(Builder $query): Builder
    {
        $driver = $query->getConnection()->getDriverName();

        $isNumericExpression = match ($driver) {
            'pgsql' => "TRIM(number) ~ '^[0-9]+$'",
            'sqlite' => "TRIM(number) GLOB '[0-9]*' AND TRIM(number) NOT GLOB '*[^0-9]*'",
            default => "TRIM(number) REGEXP '^[0-9]+$'",
        };

        $castExpression = match ($driver) {
            'pgsql', 'sqlite' => 'CAST(TRIM(number) AS INTEGER)',
            default => 'CAST(TRIM(number) AS UNSIGNED)',
        };

        return $query
            ->orderBy('block')
            ->orderBy('floor')
            ->orderByRaw("CASE WHEN {$isNumericExpression} THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN {$isNumericExpression} THEN {$castExpression} END")
            ->orderByRaw('LOWER(TRIM(number))');
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

    private static function hasApartmentUserFamilyRoleColumn(): bool
    {
        if (self::$apartmentUserHasFamilyRoleColumn === null) {
            self::$apartmentUserHasFamilyRoleColumn = Schema::hasColumn('apartment_user', 'family_role');
        }

        return self::$apartmentUserHasFamilyRoleColumn;
    }
}
