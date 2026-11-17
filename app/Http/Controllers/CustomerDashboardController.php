<?php

namespace App\Http\Controllers;

use App\Modules\Order\Models\Order;
use App\Modules\Wishlist\Models\Wishlist;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class CustomerDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        return view('customer.dashboard.index', [
            'user' => $user,
            'ordersCount' => Order::where('user_id', $user->id)->count(),
            'wishlistCount' => Wishlist::where('user_id', $user->id)->count(),
            'recentOrders' => Order::where('user_id', $user->id)->latest()->take(5)->get(),
        ]);
    }
}
