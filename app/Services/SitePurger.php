<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Apartment;
use App\Models\CashAccount;
use App\Models\Charge;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Site;
use App\Models\TemplateAidat;
use App\Models\TemplateExpense;
use App\Models\User;
use App\Models\Vendor;
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

            DB::table('receipt_items')
                ->whereIn('receipt_id', function ($query) use ($siteId) {
                    $query->select('id')
                        ->from('receipts')
                        ->where('site_id', $siteId);
                })
                ->orWhereIn('charge_id', function ($query) use ($siteId) {
                    $query->select('id')
                        ->from('charges')
                        ->where('site_id', $siteId);
                })
                ->delete();

            DB::table('payment_items')
                ->whereIn('payment_id', function ($query) use ($siteId) {
                    $query->select('id')
                        ->from('payments')
                        ->where('site_id', $siteId);
                })
                ->orWhereIn('expense_id', function ($query) use ($siteId) {
                    $query->select('id')
                        ->from('expenses')
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

            Receipt::withoutGlobalScope('site')->withTrashed()
                ->where('site_id', $siteId)
                ->forceDelete();

            Payment::withoutGlobalScope('site')->withTrashed()
                ->where('site_id', $siteId)
                ->forceDelete();

            Charge::withoutGlobalScope('site')->withTrashed()
                ->where('site_id', $siteId)
                ->forceDelete();

            Expense::withoutGlobalScope('site')->withTrashed()
                ->where('site_id', $siteId)
                ->forceDelete();

            Apartment::withoutGlobalScope('site')->withTrashed()
                ->where('site_id', $siteId)
                ->forceDelete();

            Vendor::withoutGlobalScope('site')->withTrashed()
                ->where('site_id', $siteId)
                ->forceDelete();

            CashAccount::withoutGlobalScope('site')->withTrashed()
                ->where('site_id', $siteId)
                ->forceDelete();

            Account::withoutGlobalScope('site')
                ->where('site_id', $siteId)
                ->delete();

            User::withoutGlobalScope('site')
                ->where('site_id', $siteId)
                ->update(['site_id' => null]);

            $site->forceDelete();
        });
    }
}
