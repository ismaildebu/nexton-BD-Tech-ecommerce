<?php

namespace App\Modules\Subscription\Policies;

use App\Models\User;
use App\Modules\Subscription\Models\Subscription;

class SubscriptionPolicy
{
    public function view(User $user, Subscription $subscription): bool
    {
        return $user->is_admin || $user->vendor?->id === $subscription->vendor_id;
    }
}
