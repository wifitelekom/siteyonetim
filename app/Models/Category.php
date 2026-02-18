<?php

namespace App\Models;

use App\Traits\BelongsToSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    use BelongsToSite;

    protected $fillable = [
        'site_id',
        'name',
        'type',
        'color',
        'sort_order',
    ];

    public const TYPE_INCOME = 'income';
    public const TYPE_EXPENSE = 'expense';
    public const TYPE_CASH_IN = 'cash_in';
    public const TYPE_CASH_OUT = 'cash_out';
    public const TYPE_MAINTENANCE = 'maintenance';

    public static function types(): array
    {
        return [
            self::TYPE_INCOME,
            self::TYPE_EXPENSE,
            self::TYPE_CASH_IN,
            self::TYPE_CASH_OUT,
            self::TYPE_MAINTENANCE,
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
