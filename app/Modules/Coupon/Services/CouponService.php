<?php

namespace App\Modules\Coupon\Services;

use App\Modules\Coupon\Models\Coupon;
use DomainException;

class CouponService
{
    public const SESSION_KEY = 'applied_coupon_code';

    public function create(array $data): Coupon
    {
        return Coupon::create($data);
    }

    public function findApplicable(string $code, float $subtotal, bool $lockForUpdate = false): Coupon
    {
        $code = strtoupper(trim($code));
        $query = Coupon::where('code', $code);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $coupon = $query->first();

        if ($coupon === null) {
            throw new DomainException(__('Coupon :code is invalid.', ['code' => $code]));
        }

        $now = now();
        $value = (float) $coupon->value;

        if (! $coupon->is_active) {
            throw new DomainException(__('Coupon :code is inactive.', ['code' => $coupon->code]));
        }

        if ($coupon->starts_at?->greaterThan($now)) {
            throw new DomainException(__('Coupon :code is not active yet.', ['code' => $coupon->code]));
        }

        if ($coupon->expires_at?->lessThan($now)) {
            throw new DomainException(__('Coupon :code has expired.', ['code' => $coupon->code]));
        }

        if (round($subtotal, 2) < (float) $coupon->minimum_order) {
            throw new DomainException(__('Coupon :code requires a minimum order of ৳:amount.', [
                'code' => $coupon->code,
                'amount' => number_format((float) $coupon->minimum_order, 2),
            ]));
        }

        if (! in_array($coupon->type, ['fixed', 'percent'], true)
            || $value <= 0
            || ($coupon->type === 'percent' && $value > 100)) {
            throw new DomainException(__('Coupon :code has an invalid value.', ['code' => $coupon->code]));
        }

        return $coupon;
    }

    public function discount(Coupon $coupon, float $subtotal): float
    {
        $subtotal = round($subtotal, 2);
        $discount = $coupon->type === 'percent'
            ? $subtotal * ((float) $coupon->value / 100)
            : (float) $coupon->value;

        return round(min($subtotal, max(0.0, $discount)), 2);
    }
}
