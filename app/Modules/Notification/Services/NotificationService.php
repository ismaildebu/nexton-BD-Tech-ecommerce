<?php

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Models\Notification;
use Illuminate\Support\Str;

class NotificationService
{
    public function create(string $type, $notifiable, array $data): Notification
    {
        return Notification::create([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->getKey(),
            'data' => $data,
        ]);
    }
}
