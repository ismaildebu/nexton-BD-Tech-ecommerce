<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Notification\Models\Notification;
use Illuminate\Contracts\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        return view('admin.notifications.index', ['notifications' => Notification::latest()->paginate(20)]);
    }
}
