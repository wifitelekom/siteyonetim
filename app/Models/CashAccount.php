<?php

namespace App\Models;

use App\Enums\CashAccountType;
use App\Traits\BelongsToSite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashAccount extends Model
{
    use HasFactory, SoftDeletes, BelongsToSite;

    protected $fillable = [
        'site_id',
        'name',
        'type',
        'opening_balance',
        'is_active',
    ];

    protected $casts = [
        'type' => CashAccountType::class,
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getBalanceAttribute(): float
    {
        $receiptsTotal = $this->receipts()->sum('total_amount');
        $paymentsTotal = $this->payments()->sum('total_amount');

        return (float) $this->opening_balance + (float) $receiptsTotal - (float) $paymentsTotal;
    }
}
