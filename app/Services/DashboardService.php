<?php

namespace App\Services;

use App\Models\CashAccount;
use App\Models\Charge;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\TemplateAidat;
use App\Models\TemplateExpense;
use App\Models\User;
use Carbon\Carbon;

class DashboardService
{
    public function getSummary(User $user): array
    {
        $isAdmin = $user->hasAnyRole(['admin', 'super-admin']);
        $apartmentIds = (!$isAdmin && $user->hasAnyRole(['owner', 'tenant']))
            ? $user->apartment_ids
            : null;
        $vendorId = (!$isAdmin && $user->hasRole('vendor') && $user->vendor)
            ? $user->vendor->id
            : null;

        $today = Carbon::today()->toDateString();
        $monthStart = Carbon::today()->startOfMonth()->toDateString();
        $monthEnd = Carbon::today()->endOfMonth()->toDateString();

        // Helper: scope charge queries for residents
        $scopeCharge = function ($query) use ($apartmentIds) {
            return $apartmentIds !== null ? $query->whereIn('apartment_id', $apartmentIds) : $query;
        };

        // Helper: scope expense queries for vendors
        $scopeExpense = function ($query) use ($vendorId) {
            return $vendorId !== null ? $query->where('vendor_id', $vendorId) : $query;
        };

        // Receivables: admin or resident
        $receivables = null;
        if ($isAdmin || $apartmentIds !== null) {
            $receivables = [
                'not_due' => (float) $scopeCharge(Charge::unpaid()->where('due_date', '>', $today))->sum(\DB::raw('amount - paid_amount')),
                'due_today' => (float) $scopeCharge(Charge::dueToday())->sum(\DB::raw('amount - paid_amount')),
                'overdue' => (float) $scopeCharge(Charge::overdue())->sum(\DB::raw('amount - paid_amount')),
            ];
            $receivables['total'] = $receivables['not_due'] + $receivables['due_today'] + $receivables['overdue'];
            $receivables['count'] = $scopeCharge(Charge::unpaid())->count();
        }

        // Payables: admin or vendor
        $payables = null;
        if ($isAdmin || $vendorId !== null) {
            $payables = [
                'not_due' => (float) $scopeExpense(Expense::whereColumn('paid_amount', '<', 'amount')->where('due_date', '>', $today))->sum(\DB::raw('amount - paid_amount')),
                'due_today' => (float) $scopeExpense(Expense::dueToday())->sum(\DB::raw('amount - paid_amount')),
                'overdue' => (float) $scopeExpense(Expense::overdue())->sum(\DB::raw('amount - paid_amount')),
            ];
            $payables['total'] = $payables['not_due'] + $payables['due_today'] + $payables['overdue'];
            $payables['vendor_count'] = $scopeExpense(Expense::whereColumn('paid_amount', '<', 'amount'))->distinct('vendor_id')->count('vendor_id');
        }

        // Cash accounts: admin only
        $cashAccounts = null;
        $totalCash = null;
        if ($isAdmin) {
            $cashAccounts = CashAccount::withComputedBalance()->where('is_active', true)->get()->map(function ($account) {
                return [
                    'id' => $account->id,
                    'name' => $account->name,
                    'type' => $account->type,
                    'balance' => $account->balance,
                ];
            });
            $totalCash = $cashAccounts->sum('balance');
        }

        // Recent transactions
        $recentReceipts = collect();
        $recentPayments = collect();

        if ($isAdmin || $apartmentIds !== null) {
            $receiptQuery = Receipt::with('apartment')->orderByDesc('paid_at')->limit(10);
            if ($apartmentIds !== null) {
                $receiptQuery->whereIn('apartment_id', $apartmentIds);
            }
            $recentReceipts = $receiptQuery->get()->map(function ($r) {
                return [
                    'id' => $r->id,
                    'date' => $r->paid_at,
                    'type' => 'receipt',
                    'description' => 'Tahsilat' . ($r->apartment ? ' - ' . $r->apartment->full_label : ''),
                    'amount' => (float) $r->total_amount,
                    'receipt_no' => $r->receipt_no,
                ];
            });
        }

        if ($isAdmin || $vendorId !== null) {
            $paymentQuery = Payment::with('vendor')->orderByDesc('paid_at')->limit(10);
            if ($vendorId !== null) {
                $paymentQuery->where('vendor_id', $vendorId);
            }
            $recentPayments = $paymentQuery->get()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'date' => $p->paid_at,
                    'type' => 'payment',
                    'description' => 'Ödeme' . ($p->vendor ? ' - ' . $p->vendor->name : ''),
                    'amount' => (float) $p->total_amount,
                    'receipt_no' => null,
                ];
            });
        }

        $recentTransactions = $recentReceipts->concat($recentPayments)
            ->sortByDesc('date')
            ->take(10)
            ->values();

        $monthlyReceiptCount = null;
        if ($isAdmin || $apartmentIds !== null) {
            $receiptCountQuery = Receipt::whereBetween('paid_at', [$monthStart, $monthEnd]);
            if ($apartmentIds !== null) {
                $receiptCountQuery->whereIn('apartment_id', $apartmentIds);
            }
            $monthlyReceiptCount = $receiptCountQuery->count();
        }

        // Templates, monthly trend, collection rate: admin only
        $aidatTemplates = null;
        $aidatTemplatesTotal = null;
        $expenseTemplates = null;
        $expenseTemplatesTotal = null;
        $monthlyTrend = null;
        $collectionRate = null;
        $timeline = null;

        if ($isAdmin) {
            $aidatTemplates = TemplateAidat::where('is_active', true)->count();
            $aidatTemplatesTotal = TemplateAidat::count();
            $expenseTemplates = TemplateExpense::where('is_active', true)->count();
            $expenseTemplatesTotal = TemplateExpense::count();

            // Monthly trend (last 6 months)
            $monthlyTrend = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::today()->subMonths($i);
                $start = $month->copy()->startOfMonth()->toDateString();
                $end = $month->copy()->endOfMonth()->toDateString();

                $monthlyTrend[] = [
                    'month' => $month->translatedFormat('M Y'),
                    'income' => (float) Receipt::whereBetween('paid_at', [$start, $end])->sum('total_amount'),
                    'expense' => (float) Payment::whereBetween('paid_at', [$start, $end])->sum('total_amount'),
                ];
            }

            // Collection rate for current month
            $monthlyChargeTotal = (float) Charge::whereBetween('due_date', [$monthStart, $monthEnd])->sum('amount');
            $monthlyCollected = (float) Receipt::whereBetween('paid_at', [$monthStart, $monthEnd])->sum('total_amount');
            $collectionRate = $monthlyChargeTotal > 0 ? round(($monthlyCollected / $monthlyChargeTotal) * 100) : 0;
        }

        // Timeline: upcoming 30 days
        if ($isAdmin || $apartmentIds !== null || $vendorId !== null) {
            $upcomingEnd = Carbon::today()->addDays(30)->toDateString();
            $timeline = collect();

            // Upcoming charges (admin + residents)
            if ($isAdmin || $apartmentIds !== null) {
                $chargeQuery = $scopeCharge(Charge::unpaid()->whereBetween('due_date', [$today, $upcomingEnd]))
                    ->with('apartment')
                    ->orderBy('due_date')
                    ->limit(15);

                $timeline = $timeline->concat($chargeQuery->get()->map(function ($c) {
                    return [
                        'uid' => 'charge-' . $c->id,
                        'date' => $c->due_date->format('Y-m-d'),
                        'date_display' => $c->due_date->format('d.m.Y'),
                        'type' => 'receivable',
                        'title' => 'Tahsilat',
                        'subtitle' => $c->apartment?->full_label ?? $c->description,
                        'amount' => number_format($c->remaining, 2, ',', '.') . ' ₺',
                        'dot_class' => 'dot-success',
                    ];
                }));
            }

            // Upcoming expenses (admin + vendors)
            if ($isAdmin || $vendorId !== null) {
                $expenseQuery = $scopeExpense(Expense::whereColumn('paid_amount', '<', 'amount')->whereBetween('due_date', [$today, $upcomingEnd]))
                    ->with('vendor')
                    ->orderBy('due_date')
                    ->limit(15);

                $timeline = $timeline->concat($expenseQuery->get()->map(function ($e) {
                    return [
                        'uid' => 'expense-' . $e->id,
                        'date' => $e->due_date->format('Y-m-d'),
                        'date_display' => $e->due_date->format('d.m.Y'),
                        'type' => 'payable',
                        'title' => 'Ödeme',
                        'subtitle' => $e->vendor?->name ?? $e->description,
                        'amount' => number_format($e->remaining, 2, ',', '.') . ' ₺',
                        'dot_class' => 'dot-danger',
                    ];
                }));
            }

            // Past receipts for timeline (admin + residents)
            if ($isAdmin || $apartmentIds !== null) {
                $pastReceiptQuery = Receipt::with('apartment')->orderByDesc('paid_at')->limit(5);
                if ($apartmentIds !== null) {
                    $pastReceiptQuery->whereIn('apartment_id', $apartmentIds);
                }

                $timeline = $timeline->concat($pastReceiptQuery->get()->map(function ($r) {
                    return [
                        'uid' => 'receipt-' . $r->id,
                        'date' => Carbon::parse($r->paid_at)->format('Y-m-d'),
                        'date_display' => Carbon::parse($r->paid_at)->format('d.m.Y'),
                        'type' => 'past_receipt',
                        'title' => 'Tahsilatlar',
                        'subtitle' => $r->apartment?->full_label ?? 'Tahsilat',
                        'amount' => number_format($r->total_amount, 2, ',', '.') . ' ₺',
                        'dot_class' => 'dot-info',
                    ];
                }));
            }

            $timeline = $timeline->sortBy('date')->values();
        }

        return [
            'receivables' => $receivables,
            'payables' => $payables,
            'cashAccounts' => $cashAccounts,
            'totalCash' => $totalCash,
            'recentTransactions' => $recentTransactions,
            'monthlyReceiptCount' => $monthlyReceiptCount,
            'aidatTemplates' => $aidatTemplates,
            'aidatTemplatesTotal' => $aidatTemplatesTotal,
            'expenseTemplates' => $expenseTemplates,
            'expenseTemplatesTotal' => $expenseTemplatesTotal,
            'timeline' => $timeline,
            'monthlyTrend' => $monthlyTrend,
            'collectionRate' => $collectionRate,
        ];
    }
}
