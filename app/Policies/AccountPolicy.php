<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('accounts.view');
    }

    public function view(User $user, Account $account): bool
    {
        return $user->can('accounts.view')
            && $user->site_id === $account->site_id;
    }

    public function create(User $user): bool
    {
        return $user->can('accounts.manage');
    }

    public function update(User $user, Account $account): bool
    {
        return $user->can('accounts.manage')
            && $user->site_id === $account->site_id;
    }

    public function delete(User $user, Account $account): bool
    {
        return $user->can('accounts.manage')
            && $user->site_id === $account->site_id;
    }
}
