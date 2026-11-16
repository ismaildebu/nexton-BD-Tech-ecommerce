<?php

namespace App\Modules\Vendor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Product\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class VendorDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $vendor = $request->user()->vendor;
        $stats = [];
        if ($vendor) {
            $stats = [
                'products' => Product::where('vendor_id', $vendor->id)->count(),
                'activeProducts' => Product::where('vendor_id', $vendor->id)->where('is_active', true)->count(),
            ];
        }

        return view('vendor.dashboard.index', ['vendor' => $vendor, 'stats' => $stats]);
    }
}
