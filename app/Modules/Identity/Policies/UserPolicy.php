<?php

namespace App\Modules\Identity\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Users may only view their own account.
     */
    public function view(User $actor, User $target): bool
    {
        return $actor->is($target);
    }

    /**
     * Users may only update their own profile.
     */
    public function update(User $actor, User $target): bool
    {
        return $actor->is($target);
    }

    /**
     * Protected system accounts can never be deleted.
     */
    public function delete(User $actor, User $target): bool
    {
        return $actor->is($target) && ! $target->is_protected;
    }
}
