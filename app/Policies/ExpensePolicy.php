<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('expenses.view');
    }

    public function view(User $user, Expense $expense): bool
    {
        if ($user->hasRole('admin')) {
            return $user->site_id === $expense->site_id;
        }

        if ($user->hasRole('vendor') && $user->vendor) {
            return $user->site_id === $expense->site_id
                && $expense->vendor_id === $user->vendor->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('expenses.create');
    }

    public function pay(User $user, Expense $expense): bool
    {
        return $user->can('expenses.pay')
            && $user->site_id === $expense->site_id;
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->can('expenses.delete')
            && $user->site_id === $expense->site_id;
    }
}
