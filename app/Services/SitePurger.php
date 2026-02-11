<?php

namespace App\Services;

use App\Models\Site;
use App\Models\TemplateAidat;
use App\Models\TemplateExpense;
use Illuminate\Support\Facades\DB;

class SitePurger
{
    public function purge(Site $site): void
    {
        DB::transaction(function () use ($site) {
            $siteId = $site->id;

            DB::table('templates_aidat_apartments')
                ->whereIn('template_id', function ($query) use ($siteId) {
                    $query->select('id')
                        ->from('templates_aidat')
                        ->where('site_id', $siteId);
                })
                ->delete();

            TemplateAidat::withoutGlobalScope('site')
                ->where('site_id', $siteId)
                ->delete();

            TemplateExpense::withoutGlobalScope('site')
                ->where('site_id', $siteId)
                ->delete();

            DB::table('apartment_user')
                ->whereIn('apartment_id', function ($query) use ($siteId) {
                    $query->select('id')
                        ->from('apartments')
                        ->where('site_id', $siteId);
                })
                ->delete();

            $site->receipts()->withTrashed()->forceDelete();
            $site->payments()->withTrashed()->forceDelete();
            $site->charges()->withTrashed()->forceDelete();
            $site->expenses()->withTrashed()->forceDelete();
            $site->apartments()->withTrashed()->forceDelete();
            $site->vendors()->withTrashed()->forceDelete();
            $site->cashAccounts()->withTrashed()->forceDelete();

            $site->accounts()->delete();
            $site->users()->update(['site_id' => null]);

            $site->forceDelete();
        });
    }
}
