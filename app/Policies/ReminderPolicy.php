<?php

namespace App\Policies;

use App\Models\Reminder;
use App\Models\User;

class ReminderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.update');
    }

    public function update(User $user, Reminder $reminder): bool
    {
        return $user->can('users.update')
            && $user->site_id === $reminder->site_id;
    }

    public function delete(User $user, Reminder $reminder): bool
    {
        return $user->can('users.update')
            && $user->site_id === $reminder->site_id;
    }
}
