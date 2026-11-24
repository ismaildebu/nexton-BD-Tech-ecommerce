<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Notification\Http\Requests\CreateAdminNotificationRequest;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function index(): View
    {
        return view('admin.notifications.index', [
            'notifications' => Notification::with('notifiable')->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.notifications.create', [
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function store(CreateAdminNotificationRequest $request, NotificationService $notifications): RedirectResponse
    {
        $data = $request->validated();
        $user = User::findOrFail($data['user_id']);

        $notifications->create($data['type'], $user, $data['data']);

        return redirect()->route('admin.notifications.index')->with('status', __('Notification sent.'));
    }
}
