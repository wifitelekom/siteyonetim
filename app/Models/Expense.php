<?php

namespace App\Models;

use App\Enums\ExpenseStatus;
use App\Traits\BelongsToSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expense extends Model
{
    use HasFactory, SoftDeletes, BelongsToSite;

    protected $fillable = [
        'site_id',
        'vendor_id',
        'account_id',
        'expense_date',
        'due_date',
        'amount',
        'paid_amount',
        'description',
        'invoice_no',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    // Relationships
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paymentItems(): HasMany
    {
        return $this->hasMany(PaymentItem::class);
    }

    public function expenseNotes(): HasMany
    {
        return $this->hasMany(ExpenseNote::class);
    }

    // Accessors
    public function getStatusAttribute(): ExpenseStatus
    {
        if ((float) $this->paid_amount >= (float) $this->amount) {
            return ExpenseStatus::Paid;
        }
        if ((float) $this->paid_amount > 0) {
            return ExpenseStatus::Partial;
        }
        return ExpenseStatus::Unpaid;
    }

    public function getRemainingAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }

    // Scopes
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->where('paid_amount', 0);
    }

    public function scopePartial(Builder $query): Builder
    {
        return $query->where('paid_amount', '>', 0)
            ->whereColumn('paid_amount', '<', 'amount');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->whereColumn('paid_amount', '>=', 'amount');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereColumn('paid_amount', '<', 'amount')
            ->where('due_date', '<', now()->toDateString());
    }

    public function scopeNotYetDue(Builder $query): Builder
    {
        return $query->whereColumn('paid_amount', '<', 'amount')
            ->where('due_date', '>', now()->toDateString());
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query->whereColumn('paid_amount', '<', 'amount')
            ->whereDate('due_date', now()->toDateString());
    }
}
