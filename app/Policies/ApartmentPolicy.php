<?php

namespace App\Policies;

use App\Models\Apartment;
use App\Models\User;

class ApartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('apartments.view');
    }

    public function view(User $user, Apartment $apartment): bool
    {
        return $user->can('apartments.view')
            && $user->site_id === $apartment->site_id;
    }

    public function create(User $user): bool
    {
        return $user->can('apartments.manage');
    }

    public function update(User $user, Apartment $apartment): bool
    {
        return $user->can('apartments.manage')
            && $user->site_id === $apartment->site_id;
    }

    public function delete(User $user, Apartment $apartment): bool
    {
        return $user->can('apartments.manage')
            && $user->site_id === $apartment->site_id;
    }
}
