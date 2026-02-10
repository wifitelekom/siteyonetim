<?php

namespace App\Policies;

use App\Models\CashAccount;
use App\Models\User;

class CashAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('cash-accounts.view');
    }

    public function view(User $user, CashAccount $cashAccount): bool
    {
        return $user->can('cash-accounts.view')
            && $user->site_id === $cashAccount->site_id;
    }

    public function create(User $user): bool
    {
        return $user->can('cash-accounts.manage');
    }

    public function update(User $user, CashAccount $cashAccount): bool
    {
        return $user->can('cash-accounts.manage')
            && $user->site_id === $cashAccount->site_id;
    }

    public function delete(User $user, CashAccount $cashAccount): bool
    {
        return $user->can('cash-accounts.manage')
            && $user->site_id === $cashAccount->site_id;
    }
}
