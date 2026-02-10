<?php

namespace App\Models;

use App\Enums\ChargeStatus;
use App\Enums\ChargeType;
use App\Traits\BelongsToSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Charge extends Model
{
    use HasFactory, SoftDeletes, BelongsToSite;

    protected $fillable = [
        'site_id',
        'apartment_id',
        'account_id',
        'charge_type',
        'period',
        'due_date',
        'amount',
        'paid_amount',
        'description',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'charge_type' => ChargeType::class,
    ];

    // Relationships
    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receiptItems(): HasMany
    {
        return $this->hasMany(ReceiptItem::class);
    }

    // Accessors
    public function getStatusAttribute(): ChargeStatus
    {
        if ((float) $this->paid_amount >= (float) $this->amount) {
            return ChargeStatus::Paid;
        }
        if ($this->due_date && $this->due_date->lt(today())) {
            return ChargeStatus::Overdue;
        }
        return ChargeStatus::Open;
    }

    public function getRemainingAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }

    // Scopes
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereColumn('paid_amount', '<', 'amount')
            ->where('due_date', '>=', now()->toDateString());
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereColumn('paid_amount', '<', 'amount')
            ->where('due_date', '<', now()->toDateString());
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->whereColumn('paid_amount', '>=', 'amount');
    }

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereColumn('paid_amount', '<', 'amount');
    }

    public function scopeForPeriod(Builder $query, string $period): Builder
    {
        return $query->where('period', $period);
    }

    public function scopeForApartment(Builder $query, int $apartmentId): Builder
    {
        return $query->where('apartment_id', $apartmentId);
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query->whereColumn('paid_amount', '<', 'amount')
            ->whereDate('due_date', now()->toDateString());
    }

    public function scopeNotYetDue(Builder $query): Builder
    {
        return $query->whereColumn('paid_amount', '<', 'amount')
            ->where('due_date', '>', now()->toDateString());
    }
}
