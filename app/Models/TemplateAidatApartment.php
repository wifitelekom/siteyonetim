<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateAidatApartment extends Model
{
    protected $table = 'templates_aidat_apartments';

    protected $fillable = [
        'template_id',
        'apartment_id',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(TemplateAidat::class, 'template_id');
    }

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class);
    }
}
