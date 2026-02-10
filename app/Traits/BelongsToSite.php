<?php

namespace App\Traits;

use App\Models\Site;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToSite
{
    public static function bootBelongsToSite(): void
    {
        static::creating(function ($model) {
            $siteId = static::resolveAuthenticatedSiteId();

            if (!$model->site_id && $siteId) {
                $model->site_id = $siteId;
            }
        });

        static::addGlobalScope('site', function (Builder $builder) {
            $siteId = static::resolveAuthenticatedSiteId();

            if ($siteId) {
                $builder->where(
                    $builder->getModel()->getTable() . '.site_id',
                    $siteId
                );
            }
        });
    }

    private static function resolveAuthenticatedSiteId(): ?int
    {
        // hasUser() does not trigger user retrieval from session; this avoids
        // recursive auth->user() resolution when global scopes are applied.
        if (!auth()->hasUser()) {
            return null;
        }

        return auth()->user()?->site_id;
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
