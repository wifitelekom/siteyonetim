<?php

namespace App\Models;

use App\Enums\CashAccountType;
use App\Traits\BelongsToSite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
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

    public function scopeWithComputedBalance(Builder $query): Builder
    {
        return $query
            ->addSelect([
                'receipts_total' => Receipt::selectRaw('COALESCE(SUM(total_amount), 0)')
                    ->whereColumn('cash_account_id', 'cash_accounts.id'),
                'payments_total' => Payment::selectRaw('COALESCE(SUM(total_amount), 0)')
                    ->whereColumn('cash_account_id', 'cash_accounts.id'),
            ]);
    }

    public function getBalanceAttribute(): float
    {
        if (($this->attributes['receipts_total'] ?? null) !== null) {
            return (float) $this->opening_balance
                + (float) ($this->attributes['receipts_total'] ?? 0)
                - (float) ($this->attributes['payments_total'] ?? 0);
        }

        $receiptsTotal = $this->receipts()->sum('total_amount');
        $paymentsTotal = $this->payments()->sum('total_amount');

        return (float) $this->opening_balance + (float) $receiptsTotal - (float) $paymentsTotal;
    }
}
