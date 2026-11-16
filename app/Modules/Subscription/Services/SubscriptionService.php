<?php

namespace App\Modules\Subscription\Services;

use App\Modules\Subscription\Models\Subscription;

class SubscriptionService
{
    public function create(int $vendorId, ?string $plan = null, string $status = 'inactive'): Subscription
    {
        return Subscription::create([
            'vendor_id' => $vendorId,
            'plan' => $plan,
            'status' => $status,
        ]);
    }
}
