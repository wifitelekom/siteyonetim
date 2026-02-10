<?php

namespace App\Models;

use App\Enums\AccountType;
use App\Traits\BelongsToSite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory, BelongsToSite;

    protected $fillable = [
        'site_id',
        'code',
        'name',
        'type',
        'is_active',
    ];

    protected $casts = [
        'type' => AccountType::class,
        'is_active' => 'boolean',
    ];

    public function charges(): HasMany
    {
        return $this->hasMany(Charge::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->code . ' - ' . $this->name;
    }
}
