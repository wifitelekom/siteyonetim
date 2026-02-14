<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CashAccount;
use App\Models\Charge;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function __construct(
        private CashAccountService $cashAccountService
    ) {}

    public function cashStatement(int $cashAccountId, Carbon $from, Carbon $to): array
    {
        $account = CashAccount::findOrFail($cashAccountId);
        return $this->cashAccountService->getStatement($account, $from, $to);
    }

    public function accountStatement(int $accountId, Carbon $from, Carbon $to): array
    {
        $account = Account::findOrFail($accountId);

        $charges = Charge::where('account_id', $accountId)
            ->whereBetween('due_date', [$from->toDateString(), $to->toDateString()])
            ->with('apartment')
            ->orderBy('due_date')
            ->get();

        $expenses = Expense::where('account_id', $accountId)
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->with('vendor')
            ->orderBy('expense_date')
            ->get();

        return [
            'account' => $account,
            'from' => $from,
            'to' => $to,
            'charges' => $charges,
            'expenses' => $expenses,
            'totalCharges' => $charges->sum('amount'),
            'totalExpenses' => $expenses->sum('amount'),
        ];
    }

    public function collectionsReport(Carbon $from, Carbon $to, int $perPage = 0): array
    {
        $query = Receipt::with(['apartment', 'cashAccount', 'items.charge'])
            ->whereBetween('paid_at', [$from->toDateString(), $to->toDateString()])
            ->orderBy('paid_at');

        $total = (float) Receipt::whereBetween('paid_at', [$from->toDateString(), $to->toDateString()])
            ->sum('total_amount');

        $receipts = $perPage > 0 ? $query->paginate($perPage)->withQueryString() : $query->get();

        return [
            'from' => $from,
            'to' => $to,
            'receipts' => $receipts,
            'total' => $total,
        ];
    }

    public function paymentsReport(Carbon $from, Carbon $to, int $perPage = 0): array
    {
        $query = Payment::with(['vendor', 'cashAccount', 'items.expense'])
            ->whereBetween('paid_at', [$from->toDateString(), $to->toDateString()])
            ->orderBy('paid_at');

        $total = (float) Payment::whereBetween('paid_at', [$from->toDateString(), $to->toDateString()])
            ->sum('total_amount');

        $payments = $perPage > 0 ? $query->paginate($perPage)->withQueryString() : $query->get();

        return [
            'from' => $from,
            'to' => $to,
            'payments' => $payments,
            'total' => $total,
        ];
    }

    public function debtStatus(): array
    {
        $charges = Charge::with('apartment.users')
            ->whereColumn('paid_amount', '<', 'amount')
            ->orderBy('apartment_id')
            ->orderBy('due_date')
            ->get();

        $today = now()->toDateString();

        $debts = $charges->groupBy('apartment_id')->map(function ($items) use ($today) {
            $apartment = $items->first()->apartment;
            return [
                'apartment' => $apartment->full_label,
                'resident' => $apartment->users->first()?->name ?? '-',
                'total' => $items->sum('amount'),
                'paid' => $items->sum('paid_amount'),
                'remaining' => $items->sum(fn ($c) => $c->remaining),
                'overdue_count' => $items->filter(fn ($c) => $c->due_date && $c->due_date->toDateString() < $today && $c->remaining > 0)->count(),
                'open_count' => $items->filter(fn ($c) => $c->remaining > 0)->count(),
            ];
        })->values();

        return [
            'debts' => $debts,
            'grandTotal' => $charges->sum(fn ($c) => $c->remaining),
        ];
    }

    public function receivableStatus(): array
    {
        $expenses = Expense::with('vendor')
            ->whereColumn('paid_amount', '<', 'amount')
            ->orderBy('vendor_id')
            ->get();

        $grouped = $expenses->groupBy('vendor_id')->map(function ($items) {
            $vendor = $items->first()->vendor;
            return [
                'vendor' => $vendor ? $vendor->name : 'Belirtilmemiş',
                'total' => $items->sum('amount'),
                'paid' => $items->sum('paid_amount'),
                'remaining' => $items->sum(fn ($e) => $e->amount - $e->paid_amount),
            ];
        })->values();

        return [
            'receivables' => $grouped,
            'grandTotal' => $expenses->sum(fn ($e) => $e->amount - $e->paid_amount),
        ];
    }

    public function chargeList(string $period, int $perPage = 0): array
    {
        $query = Charge::with(['apartment', 'account'])
            ->where('period', $period)
            ->orderBy('apartment_id');

        $totals = Charge::where('period', $period)
            ->selectRaw('SUM(amount) as total_amount, SUM(paid_amount) as total_paid, SUM(amount - paid_amount) as total_remaining')
            ->first();

        $charges = $perPage > 0 ? $query->paginate($perPage)->withQueryString() : $query->get();

        return [
            'period' => $period,
            'charges' => $charges,
            'totalAmount' => (float) ($totals->total_amount ?? 0),
            'totalPaid' => (float) ($totals->total_paid ?? 0),
            'totalRemaining' => (float) ($totals->total_remaining ?? 0),
        ];
    }
}
