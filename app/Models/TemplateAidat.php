<?php

namespace App\Models;

use App\Traits\BelongsToSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class TemplateAidat extends Model
{
    use BelongsToSite;

    protected $table = 'templates_aidat';

    protected $fillable = [
        'site_id',
        'name',
        'amount',
        'due_day',
        'account_id',
        'scope',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_day' => 'integer',
        'is_active' => 'boolean',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function apartments(): BelongsToMany
    {
        return $this->belongsToMany(
            Apartment::class,
            'templates_aidat_apartments',
            'template_id',
            'apartment_id'
        )->withTimestamps();
    }

    public function getTargetApartments(): Collection
    {
        if ($this->scope === 'all') {
            return Apartment::withoutGlobalScope('site')
                ->where('site_id', $this->site_id)
                ->where('is_active', true)
                ->get();
        }
        return $this->apartments;
    }
}
