<?php

namespace App\Models;

use App\Traits\BelongsToSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApartmentGroup extends Model
{
    use BelongsToSite;

    protected $fillable = [
        'site_id',
        'name',
        'description',
        'multiplier',
    ];

    protected $casts = [
        'multiplier' => 'decimal:2',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function apartments(): HasMany
    {
        return $this->hasMany(Apartment::class);
    }
}
