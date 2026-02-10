<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Traits\BelongsToSite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receipt extends Model
{
    use HasFactory, SoftDeletes, BelongsToSite;

    protected $fillable = [
        'site_id',
        'receipt_no',
        'apartment_id',
        'cash_account_id',
        'paid_at',
        'method',
        'total_amount',
        'description',
        'created_by',
    ];

    protected $casts = [
        'paid_at' => 'date',
        'total_amount' => 'decimal:2',
        'method' => PaymentMethod::class,
    ];

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class);
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReceiptItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
