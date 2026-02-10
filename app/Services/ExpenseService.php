<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    public function createExpense(array $data): Expense
    {
        return DB::transaction(function () use ($data) {
            return Expense::create([
                'site_id' => $data['site_id'] ?? auth()->user()->site_id,
                'vendor_id' => $data['vendor_id'] ?? null,
                'account_id' => $data['account_id'],
                'expense_date' => $data['expense_date'],
                'due_date' => $data['due_date'],
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);
        });
    }

    public function recalculatePaidAmount(Expense $expense): void
    {
        $totalPaid = $expense->paymentItems()->sum('amount');
        $expense->update(['paid_amount' => $totalPaid]);
    }

    public function deleteExpense(Expense $expense): bool
    {
        if ((float) $expense->paid_amount > 0) {
            return false;
        }

        return (bool) $expense->delete();
    }
}
