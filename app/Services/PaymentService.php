<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\PaymentItem;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private ExpenseService $expenseService
    ) {}

    /**
     * Make payment for one or more expenses.
     * $allocations = [['expense_id' => 1, 'amount' => 500.00], ...]
     */
    public function makePayment(array $data, array $allocations): ?Payment
    {
        return DB::transaction(function () use ($data, $allocations) {
            $siteId = $data['site_id'] ?? auth()->user()->site_id;

            $payment = Payment::create([
                'site_id' => $siteId,
                'vendor_id' => $data['vendor_id'] ?? null,
                'cash_account_id' => $data['cash_account_id'],
                'paid_at' => $data['paid_at'],
                'method' => $data['method'],
                'total_amount' => 0,
                'description' => $data['description'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            $actualTotal = 0;

            foreach ($allocations as $alloc) {
                $expense = Expense::whereKey($alloc['expense_id'])
                    ->where('site_id', $siteId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $cappedAmount = min((float) $alloc['amount'], (float) $expense->remaining);
                if ($cappedAmount <= 0) {
                    continue;
                }

                PaymentItem::create([
                    'payment_id' => $payment->id,
                    'expense_id' => $expense->id,
                    'amount' => $cappedAmount,
                ]);

                $actualTotal += $cappedAmount;
                $this->expenseService->recalculatePaidAmount($expense);
            }

            if ($actualTotal <= 0) {
                return null;
            }

            $payment->update(['total_amount' => $actualTotal]);

            return $payment;
        });
    }

    /**
     * Make payment for a single expense (simplified).
     */
    public function makeSinglePayment(array $data, Expense $expense, float $amount): ?Payment
    {
        return $this->makePayment(
            array_merge($data, ['vendor_id' => $expense->vendor_id]),
            [['expense_id' => $expense->id, 'amount' => $amount]]
        );
    }
}
