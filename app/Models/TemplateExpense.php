<?php

namespace App\Models;

use App\Enums\TemplatePeriod;
use App\Traits\BelongsToSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateExpense extends Model
{
    use BelongsToSite;

    protected $table = 'templates_expense';

    protected $fillable = [
        'site_id',
        'name',
        'amount',
        'due_day',
        'period',
        'vendor_id',
        'account_id',
        'is_active',
        'last_generated_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_day' => 'integer',
        'period' => TemplatePeriod::class,
        'is_active' => 'boolean',
        'last_generated_at' => 'date',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function shouldGenerate(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$this->last_generated_at) {
            return true;
        }

        $monthsSince = $this->last_generated_at->diffInMonths(now());
        return $monthsSince >= $this->period->months();
    }
}
