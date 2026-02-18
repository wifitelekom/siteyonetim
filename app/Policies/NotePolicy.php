<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

class NotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.update');
    }

    public function delete(User $user, Note $note): bool
    {
        return $user->can('users.update')
            && $user->site_id === $note->site_id;
    }
}
