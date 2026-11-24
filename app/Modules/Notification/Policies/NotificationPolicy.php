<?php

namespace App\Modules\Notification\Policies;

use App\Models\User;
use App\Modules\Notification\Models\Notification;

class NotificationPolicy
{
    public function view(User $user, Notification $notification): bool
    {
        return $this->owns($user, $notification);
    }

    public function update(User $user, Notification $notification): bool
    {
        return $this->owns($user, $notification);
    }

    private function owns(User $user, Notification $notification): bool
    {
        return $notification->notifiable_type === User::class
            && (int) $user->id === (int) $notification->notifiable_id;
    }
}
