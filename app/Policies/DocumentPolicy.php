<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.update');
    }

    public function download(User $user, Document $document): bool
    {
        return $user->can('users.view')
            && $user->site_id === $document->site_id;
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->can('users.update')
            && $user->site_id === $document->site_id;
    }
}
