<?php

namespace App\Modules\Review\Policies;

use App\Models\User;
use App\Modules\Review\Models\Review;

class ReviewPolicy
{
    public function view(User $user, Review $review): bool
    {
        return $user->id === $review->user_id || $user->is_admin;
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id || $user->is_admin;
    }
}
