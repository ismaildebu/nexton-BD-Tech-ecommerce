<?php

namespace App\Modules\Wishlist\Policies;

use App\Models\User;
use App\Modules\Wishlist\Models\Wishlist;

class WishlistPolicy
{
    public function view(User $user, Wishlist $wishlist): bool
    {
        return $user->id === $wishlist->user_id;
    }

    public function delete(User $user, Wishlist $wishlist): bool
    {
        return $user->id === $wishlist->user_id;
    }
}
