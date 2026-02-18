<?php

namespace App\Policies;

use App\Models\ExpenseNote;
use App\Models\User;

class ExpenseNotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('expenses.view');
    }

    public function create(User $user): bool
    {
        return $user->can('expenses.create');
    }

    public function delete(User $user, ExpenseNote $note): bool
    {
        return $user->can('expenses.create')
            && $user->site_id === $note->site_id;
    }
}
