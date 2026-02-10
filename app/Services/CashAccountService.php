<?php

namespace App\Services;

use App\Models\CashAccount;
use App\Models\Payment;
use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CashAccountService
{
    public function getBalance(CashAccount $account): float
    {
        $receiptsTotal = Receipt::where('cash_account_id', $account->id)->sum('total_amount');
        $paymentsTotal = Payment::where('cash_account_id', $account->id)->sum('total_amount');

        return (float) $account->opening_balance + (float) $receiptsTotal - (float) $paymentsTotal;
    }

    public function getStatement(CashAccount $account, Carbon $from, Carbon $to): array
    {
        // Calculate opening balance (opening_balance + all transactions before $from)
        $receiptsBefore = Receipt::where('cash_account_id', $account->id)
            ->where('paid_at', '<', $from->toDateString())
            ->sum('total_amount');

        $paymentsBefore = Payment::where('cash_account_id', $account->id)
            ->where('paid_at', '<', $from->toDateString())
            ->sum('total_amount');

        $openingBalance = (float) $account->opening_balance + (float) $receiptsBefore - (float) $paymentsBefore;

        // Get transactions in range
        $receipts = Receipt::where('cash_account_id', $account->id)
            ->whereBetween('paid_at', [$from->toDateString(), $to->toDateString()])
            ->with('apartment')
            ->get()
            ->map(function ($r) {
                return [
                    'date' => $r->paid_at,
                    'description' => $r->description ?: ('Tahsilat - ' . ($r->apartment ? $r->apartment->full_label : '')),
                    'type' => 'receipt',
                    'receipt_no' => $r->receipt_no,
                    'amount' => (float) $r->total_amount,
                    'direction' => 'in',
                ];
            });

        $payments = Payment::where('cash_account_id', $account->id)
            ->whereBetween('paid_at', [$from->toDateString(), $to->toDateString()])
            ->with('vendor')
            ->get()
            ->map(function ($p) {
                return [
                    'date' => $p->paid_at,
                    'description' => $p->description ?: ('Ödeme - ' . ($p->vendor ? $p->vendor->name : '')),
                    'type' => 'payment',
                    'receipt_no' => null,
                    'amount' => (float) $p->total_amount,
                    'direction' => 'out',
                ];
            });

        $transactions = $receipts->concat($payments)
            ->sortBy('date')
            ->values();

        // Calculate running balance
        $runningBalance = $openingBalance;
        $transactions = $transactions->map(function ($tx) use (&$runningBalance) {
            if ($tx['direction'] === 'in') {
                $runningBalance += $tx['amount'];
            } else {
                $runningBalance -= $tx['amount'];
            }
            $tx['balance'] = $runningBalance;
            return $tx;
        });

        return [
            'account' => $account,
            'from' => $from,
            'to' => $to,
            'opening_balance' => $openingBalance,
            'transactions' => $transactions,
            'closing_balance' => $runningBalance,
        ];
    }
}
