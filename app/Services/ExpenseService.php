<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    public function createExpense(array $data): Expense
    {
        $expense = DB::transaction(function () use ($data) {
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

        DashboardService::clearCache((int) $expense->site_id);

        return $expense;
    }

    public function recalculatePaidAmount(Expense $expense): void
    {
        $totalPaid = $expense->paymentItems()->sum('amount');
        $expense->update(['paid_amount' => $totalPaid]);
    }

    public function updateExpense(Expense $expense, array $data): Expense
    {
        $updatedExpense = DB::transaction(function () use ($expense, $data) {
            $expense->update([
                'vendor_id' => $data['vendor_id'] ?? null,
                'account_id' => $data['account_id'],
                'expense_date' => $data['expense_date'],
                'due_date' => $data['due_date'],
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'invoice_no' => $data['invoice_no'] ?? null,
            ]);

            return $expense->fresh();
        });

        DashboardService::clearCache((int) $updatedExpense->site_id);

        return $updatedExpense;
    }

    public function deleteExpense(Expense $expense): bool
    {
        if ((float) $expense->paid_amount > 0) {
            return false;
        }

        $deleted = (bool) $expense->delete();

        if ($deleted) {
            DashboardService::clearCache((int) $expense->site_id);
        }

        return $deleted;
    }
}
