<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard.index', [
            'customers' => User::count(),
            'vendors' => Vendor::count(),
            'pendingVendors' => Vendor::where('verification_status', 'pending')->count(),
        ]);
    }
}
