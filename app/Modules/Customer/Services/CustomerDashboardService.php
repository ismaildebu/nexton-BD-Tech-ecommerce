<?php

namespace App\Modules\Customer\Services;

use App\Models\User;
use Illuminate\Contracts\Support\Arrayable;

class CustomerDashboardService
{
    /**
     * Build the dashboard data for the authenticated customer.
     *
     * Order-related data is an integration point only; when the Order module
     * is unavailable it is left null so the dashboard still renders.
     */
    public function build(User $user): array
    {
        $user->loadMissing(['customerProfile', 'addresses']);

        return [
            'user' => $user,
            'profile' => $user->customerProfile,
            'defaultAddress' => $user->addresses->firstWhere('is_default', true),
            'addressCount' => $user->addresses->count(),
            'ordersAvailable' => class_exists(\App\Modules\Order\Services\OrderService::class),
        ];
    }
}
