<?php

namespace App\Services;

use App\Models\CashAccount;
use App\Models\Charge;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\TemplateAidat;
use App\Models\TemplateExpense;
use Carbon\Carbon;

class DashboardService
{
    public function getSummary(): array
    {
        $today = Carbon::today()->toDateString();

        // Receivables (Tahsil Edilecekler)
        $receivables = [
            'not_due' => (float) Charge::unpaid()->where('due_date', '>', $today)->sum(\DB::raw('amount - paid_amount')),
            'due_today' => (float) Charge::dueToday()->sum(\DB::raw('amount - paid_amount')),
            'overdue' => (float) Charge::overdue()->sum(\DB::raw('amount - paid_amount')),
        ];
        $receivables['total'] = $receivables['not_due'] + $receivables['due_today'] + $receivables['overdue'];
        $receivables['count'] = Charge::unpaid()->count();

        // Payables (Ödenecekler)
        $payables = [
            'not_due' => (float) Expense::whereColumn('paid_amount', '<', 'amount')->where('due_date', '>', $today)->sum(\DB::raw('amount - paid_amount')),
            'due_today' => (float) Expense::dueToday()->sum(\DB::raw('amount - paid_amount')),
            'overdue' => (float) Expense::overdue()->sum(\DB::raw('amount - paid_amount')),
        ];
        $payables['total'] = $payables['not_due'] + $payables['due_today'] + $payables['overdue'];
        $payables['vendor_count'] = Expense::whereColumn('paid_amount', '<', 'amount')->distinct('vendor_id')->count('vendor_id');

        // Cash accounts with balances
        $cashAccounts = CashAccount::where('is_active', true)->get()->map(function ($account) {
            return [
                'id' => $account->id,
                'name' => $account->name,
                'type' => $account->type,
                'balance' => $account->balance,
            ];
        });

        $totalCash = $cashAccounts->sum('balance');

        // Recent transactions (last 10)
        $recentReceipts = Receipt::with('apartment')
            ->orderByDesc('paid_at')
            ->limit(10)
            ->get()
            ->map(function ($r) {
                return [
                    'date' => $r->paid_at,
                    'type' => 'receipt',
                    'description' => 'Tahsilat' . ($r->apartment ? ' - ' . $r->apartment->full_label : ''),
                    'amount' => (float) $r->total_amount,
                    'receipt_no' => $r->receipt_no,
                ];
            });

        $recentPayments = Payment::with('vendor')
            ->orderByDesc('paid_at')
            ->limit(10)
            ->get()
            ->map(function ($p) {
                return [
                    'date' => $p->paid_at,
                    'type' => 'payment',
                    'description' => 'Ödeme' . ($p->vendor ? ' - ' . $p->vendor->name : ''),
                    'amount' => (float) $p->total_amount,
                    'receipt_no' => null,
                ];
            });

        $recentTransactions = $recentReceipts->concat($recentPayments)
            ->sortByDesc('date')
            ->take(10)
            ->values();

        $monthStart = Carbon::today()->startOfMonth()->toDateString();
        $monthEnd = Carbon::today()->endOfMonth()->toDateString();
        $monthlyReceiptCount = Receipt::whereBetween('paid_at', [$monthStart, $monthEnd])->count();

        // Template counters
        $aidatTemplates = TemplateAidat::where('is_active', true)->count();
        $aidatTemplatesTotal = TemplateAidat::count();
        $expenseTemplates = TemplateExpense::where('is_active', true)->count();
        $expenseTemplatesTotal = TemplateExpense::count();

        // Timeline: upcoming 30 days (charges + expenses)
        $upcomingEnd = Carbon::today()->addDays(30)->toDateString();
        $timeline = collect();

        // Upcoming charges
        $upcomingCharges = Charge::unpaid()
            ->whereBetween('due_date', [$today, $upcomingEnd])
            ->with('apartment')
            ->orderBy('due_date')
            ->limit(15)
            ->get()
            ->map(function ($c) {
                return [
                    'date' => $c->due_date->format('Y-m-d'),
                    'date_display' => $c->due_date->format('d.m.Y'),
                    'type' => 'receivable',
                    'title' => 'Tahsilat',
                    'subtitle' => $c->apartment?->full_label ?? $c->description,
                    'amount' => number_format($c->remaining, 2, ',', '.') . ' ₺',
                    'dot_class' => 'dot-success',
                ];
            });

        // Upcoming expenses
        $upcomingExpenses = Expense::whereColumn('paid_amount', '<', 'amount')
            ->whereBetween('due_date', [$today, $upcomingEnd])
            ->with('vendor')
            ->orderBy('due_date')
            ->limit(15)
            ->get()
            ->map(function ($e) {
                return [
                    'date' => $e->due_date->format('Y-m-d'),
                    'date_display' => $e->due_date->format('d.m.Y'),
                    'type' => 'payable',
                    'title' => 'Ödeme',
                    'subtitle' => $e->vendor?->name ?? $e->description,
                    'amount' => number_format($e->remaining, 2, ',', '.') . ' ₺',
                    'dot_class' => 'dot-danger',
                ];
            });

        // Recent receipts for timeline
        $pastReceipts = Receipt::with('apartment')
            ->orderByDesc('paid_at')
            ->limit(5)
            ->get()
            ->map(function ($r) {
                return [
                    'date' => Carbon::parse($r->paid_at)->format('Y-m-d'),
                    'date_display' => Carbon::parse($r->paid_at)->format('d.m.Y'),
                    'type' => 'past_receipt',
                    'title' => 'Tahsilatlar',
                    'subtitle' => $r->apartment?->full_label ?? 'Tahsilat',
                    'amount' => number_format($r->total_amount, 2, ',', '.') . ' ₺',
                    'dot_class' => 'dot-info',
                ];
            });

        $timeline = $upcomingCharges->concat($upcomingExpenses)->concat($pastReceipts)
            ->sortBy('date')
            ->values();

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
        ];
    }
}
