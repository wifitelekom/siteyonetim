<?php

namespace App\Policies;

use App\Models\Charge;
use App\Models\User;

class ChargePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('charges.view');
    }

    public function view(User $user, Charge $charge): bool
    {
        if ($user->hasRole('admin')) {
            return $user->site_id === $charge->site_id;
        }

        if ($user->hasAnyRole(['owner', 'tenant'])) {
            return $user->site_id === $charge->site_id
                && in_array($charge->apartment_id, $user->apartment_ids);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('charges.create');
    }

    public function collect(User $user, Charge $charge): bool
    {
        return $user->can('charges.collect')
            && $user->site_id === $charge->site_id;
    }

    public function delete(User $user, Charge $charge): bool
    {
        return $user->can('charges.delete')
            && $user->site_id === $charge->site_id;
    }
}
