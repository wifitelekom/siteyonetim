<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vendor;

class VendorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('vendors.view');
    }

    public function view(User $user, Vendor $vendor): bool
    {
        return $user->can('vendors.view')
            && $user->site_id === $vendor->site_id;
    }

    public function create(User $user): bool
    {
        return $user->can('vendors.manage');
    }

    public function update(User $user, Vendor $vendor): bool
    {
        return $user->can('vendors.manage')
            && $user->site_id === $vendor->site_id;
    }

    public function delete(User $user, Vendor $vendor): bool
    {
        return $user->can('vendors.manage')
            && $user->site_id === $vendor->site_id;
    }
}
