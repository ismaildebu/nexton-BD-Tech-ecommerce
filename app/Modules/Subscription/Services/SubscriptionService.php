<?php

namespace App\Modules\Subscription\Services;

use App\Modules\Subscription\Models\Subscription;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function create(int $vendorId, ?string $plan = null, string $status = 'inactive', ?string $startsAt = null, ?string $expiresAt = null): Subscription
    {
        [$startsAt, $expiresAt] = $this->dateRange($startsAt, $expiresAt);

        return Subscription::create([
            'vendor_id' => $vendorId,
            'plan' => $plan,
            'status' => $status,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
        ]);
    }

    public function transition(Subscription $subscription, string $status, ?string $startsAt = null, ?string $expiresAt = null): Subscription
    {
        return DB::transaction(function () use ($subscription, $status, $startsAt, $expiresAt) {
            $locked = Subscription::whereKey($subscription->getKey())->lockForUpdate()->firstOrFail();

            if ($status === 'active' && $locked->status === 'inactive') {
                $start = $startsAt !== null ? Carbon::parse($startsAt) : ($locked->starts_at ?? Carbon::now());
                $expiry = $expiresAt !== null ? Carbon::parse($expiresAt) : $locked->expires_at;

                [$start, $expiry] = $this->dateRange($start, $expiry);

                if ($expiry !== null && $expiry->isPast()) {
                    throw new DomainException(__('An active subscription cannot have an expiry in the past.'));
                }

                $locked->update(['status' => 'active', 'starts_at' => $start, 'expires_at' => $expiry]);

                return $locked->refresh();
            }

            if ($status === 'cancelled' && $locked->status === 'active') {
                $changes = ['status' => 'cancelled'];
                $now = Carbon::now();

                if (($locked->starts_at === null || $locked->starts_at->lessThanOrEqualTo($now))
                    && ($locked->expires_at === null || $locked->expires_at->greaterThan($now))) {
                    $changes['expires_at'] = $now;
                }

                $locked->update($changes);

                return $locked->refresh();
            }

            throw new DomainException(__('This subscription status transition is not allowed.'));
        });
    }

    private function dateRange(string|Carbon|null $startsAt, string|Carbon|null $expiresAt): array
    {
        $start = $startsAt !== null ? Carbon::parse($startsAt) : null;
        $expiry = $expiresAt !== null ? Carbon::parse($expiresAt) : null;

        if ($start !== null && $expiry !== null && $expiry->lessThanOrEqualTo($start)) {
            throw new DomainException(__('Subscription expiry must be after its start date.'));
        }

        return [$start, $expiry];
    }
}
