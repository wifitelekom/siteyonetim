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
    public function makePayment(array $data, array $allocations): Payment
    {
        return DB::transaction(function () use ($data, $allocations) {
            $siteId = $data['site_id'] ?? auth()->user()->site_id;
            $totalAmount = collect($allocations)->sum('amount');

            $payment = Payment::create([
                'site_id' => $siteId,
                'vendor_id' => $data['vendor_id'] ?? null,
                'cash_account_id' => $data['cash_account_id'],
                'paid_at' => $data['paid_at'],
                'method' => $data['method'],
                'total_amount' => $totalAmount,
                'description' => $data['description'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            foreach ($allocations as $alloc) {
                $expense = Expense::whereKey($alloc['expense_id'])
                    ->where('site_id', $siteId)
                    ->firstOrFail();

                PaymentItem::create([
                    'payment_id' => $payment->id,
                    'expense_id' => $expense->id,
                    'amount' => $alloc['amount'],
                ]);

                $this->expenseService->recalculatePaidAmount($expense);
            }

            return $payment;
        });
    }

    /**
     * Make payment for a single expense (simplified).
     */
    public function makeSinglePayment(array $data, Expense $expense, float $amount): Payment
    {
        return $this->makePayment(
            array_merge($data, ['vendor_id' => $expense->vendor_id]),
            [['expense_id' => $expense->id, 'amount' => $amount]]
        );
    }
}
