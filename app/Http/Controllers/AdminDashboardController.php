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
        $revenue = (float) Order::where('payment_status', 'paid')
            ->where('status', 'completed')
            ->sum('total');
        $ordersCount = Order::count();
        $customersCount = User::where('is_admin', false)->count();
        $pendingVendorsCount = Vendor::where('verification_status', 'pending')->count();
        $productsCount = Product::count();
        $paymentsCount = Payment::count();
        $vendorsCount = Vendor::count();

        $recentOrders = Order::with('user')->latest()->take(6)->get();
        $inventoryProducts = Product::with(['inventory', 'category'])->latest()->take(6)->get();

        return view('admin.dashboard.index', [
            'customers' => $customersCount,
            'vendors' => $vendorsCount,
            'pendingVendors' => $pendingVendorsCount,
            'products' => $productsCount,
            'orders' => $ordersCount,
            'payments' => $paymentsCount,
            'revenue' => $revenue,
            'recentOrders' => $recentOrders,
            'inventoryProducts' => $inventoryProducts,
        ]);
    }
}
