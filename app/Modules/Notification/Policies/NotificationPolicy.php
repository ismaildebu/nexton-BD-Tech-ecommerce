<?php

namespace App\Modules\Notification\Policies;

use App\Models\User;
use App\Modules\Notification\Models\Notification;

class NotificationPolicy
{
    public function view(User $user, Notification $notification): bool
    {
        return $user->id === $notification->notifiable_id;
    }
}
