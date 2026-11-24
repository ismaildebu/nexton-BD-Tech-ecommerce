<?php

namespace App\Modules\Accounting\Services;

use App\Modules\Accounting\Models\Settlement;
use DomainException;
use Illuminate\Support\Facades\DB;

class SettlementService
{
    public function create(int $vendorId, float $amount): Settlement
    {
        return Settlement::create([
            'vendor_id' => $vendorId,
            'amount' => $amount,
            'status' => 'pending',
        ]);
    }

    public function transition(Settlement $settlement, string $status): Settlement
    {
        return DB::transaction(function () use ($settlement, $status) {
            $locked = Settlement::whereKey($settlement->getKey())->lockForUpdate()->firstOrFail();
            $allowed = match ($locked->status) {
                'pending' => ['processing'],
                'processing' => ['paid', 'failed'],
                default => [],
            };

            if (! in_array($status, $allowed, true)) {
                throw new DomainException(__('This settlement status transition is not allowed.'));
            }

            $locked->update(['status' => $status]);

            return $locked->refresh();
        });
    }
}
