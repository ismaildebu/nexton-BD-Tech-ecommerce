<?php

namespace App\Modules\Notification\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notification\Http\Requests\CreateNotificationRequest;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function index(Request $request): View
    {
        $notifications = Notification::where('notifiable_type', get_class($request->user()))
            ->where('notifiable_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('notifications.index', ['notifications' => $notifications]);
    }

    public function store(CreateNotificationRequest $request): RedirectResponse
    {
        $this->notifications->create(
            $request->string('type')->toString(),
            $request->user(),
            $request->input('data')
        );

        return redirect()->route('notifications.index')->with('status', __('Notification created.'));
    }
}
