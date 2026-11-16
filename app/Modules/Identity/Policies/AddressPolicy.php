<?php

namespace App\Modules\Identity\Policies;

use App\Models\Address;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AddressPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, Address $address): Response
    {
        return $this->owns($user, $address);
    }

    public function update(User $user, Address $address): Response
    {
        return $this->owns($user, $address);
    }

    public function delete(User $user, Address $address): Response
    {
        return $this->owns($user, $address);
    }

    /**
     * Another user's address is reported as "not found" so its existence is not revealed.
     */
    private function owns(User $user, Address $address): Response
    {
        return (int) $user->getKey() === (int) $address->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
