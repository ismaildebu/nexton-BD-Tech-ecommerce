<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Modules\Order\Models\Order;
use App\Modules\Payment\Models\Payment;
use App\Modules\Product\Models\Product;
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
            'products' => Product::count(),
            'orders' => Order::count(),
            'payments' => Payment::count(),
            'revenue' => Order::sum('total'),
        ]);
    }
}
