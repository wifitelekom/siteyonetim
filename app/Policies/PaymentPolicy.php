<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('payments.view');
    }

    public function view(User $user, Payment $payment): bool
    {
        if ($user->hasRole('admin')) {
            return $user->site_id === $payment->site_id;
        }

        if ($user->hasRole('vendor') && $user->vendor) {
            return $user->site_id === $payment->site_id
                && $payment->vendor_id === $user->vendor->id;
        }

        return false;
    }
}
