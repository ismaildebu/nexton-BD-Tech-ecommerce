<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Commission\Models\Commission;
use App\Modules\Order\Models\Order;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Contracts\View\View;

class CommissionController extends Controller
{
    public function index(): View
    {
        return view('admin.commissions.index', [
            'commissions' => Commission::with(['vendor', 'order'])->latest()->paginate(20),
            'vendors' => Vendor::orderBy('business_name')->get(['id', 'business_name']),
            'orders' => Order::with('user')->latest()->limit(100)->get(),
        ]);
    }
}
