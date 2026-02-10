<?php

namespace App\Policies;

use App\Models\Receipt;
use App\Models\User;

class ReceiptPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('receipts.view');
    }

    public function view(User $user, Receipt $receipt): bool
    {
        if ($user->hasRole('admin')) {
            return $user->site_id === $receipt->site_id;
        }

        if ($user->hasAnyRole(['owner', 'tenant'])) {
            return $user->site_id === $receipt->site_id
                && in_array($receipt->apartment_id, $user->apartment_ids);
        }

        return false;
    }

    public function print(User $user, Receipt $receipt): bool
    {
        if ($user->hasRole('admin')) {
            return $user->site_id === $receipt->site_id;
        }

        if ($user->can('receipts.print') && $user->hasAnyRole(['owner', 'tenant'])) {
            return $user->site_id === $receipt->site_id
                && in_array($receipt->apartment_id, $user->apartment_ids);
        }

        return false;
    }
}
