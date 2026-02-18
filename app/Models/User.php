<?php

namespace App\Models;

use App\Traits\BelongsToSite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, BelongsToSite;

    private static ?bool $apartmentUserHasFamilyRoleColumn = null;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'tc_kimlik',
        'password',
        'site_id',
        'archived_at',
        'address',
        'birth_date',
        'occupation',
        'education',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'archived_at' => 'datetime',
            'birth_date' => 'date',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function apartments(): BelongsToMany
    {
        $relation = $this->belongsToMany(Apartment::class)
            ->withPivot('relation_type', 'start_date', 'end_date')
            ->withTimestamps();

        if (self::hasApartmentUserFamilyRoleColumn()) {
            $relation->withPivot('family_role');
        }

        return $relation;
    }

    public function ownedApartments(): BelongsToMany
    {
        return $this->apartments()->wherePivot('relation_type', 'owner');
    }

    public function rentedApartments(): BelongsToMany
    {
        return $this->apartments()->wherePivot('relation_type', 'tenant');
    }

    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    public function getApartmentIdsAttribute(): array
    {
        return $this->apartments()->pluck('apartments.id')->toArray();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    private static function hasApartmentUserFamilyRoleColumn(): bool
    {
        if (self::$apartmentUserHasFamilyRoleColumn === null) {
            self::$apartmentUserHasFamilyRoleColumn = Schema::hasColumn('apartment_user', 'family_role');
        }

        return self::$apartmentUserHasFamilyRoleColumn;
    }
}
