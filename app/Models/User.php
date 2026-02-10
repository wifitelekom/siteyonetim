<?php

namespace App\Models;

use App\Traits\BelongsToSite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, BelongsToSite;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'tc_kimlik',
        'password',
        'site_id',
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
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function apartments(): BelongsToMany
    {
        return $this->belongsToMany(Apartment::class)
            ->withPivot('relation_type', 'start_date', 'end_date')
            ->withTimestamps();
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

    public function getApartmentIdsAttribute(): array
    {
        return $this->apartments()->pluck('apartments.id')->toArray();
    }
}
