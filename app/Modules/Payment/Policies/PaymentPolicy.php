<?php

namespace App\Modules\Payment\Policies;

use App\Models\User;
use App\Modules\Payment\Models\Payment;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        return $user->id === $payment->order->user_id || $user->is_admin;
    }
}
