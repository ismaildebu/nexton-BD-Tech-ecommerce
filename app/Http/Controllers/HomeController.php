<?php

namespace App\Http\Controllers;

use App\Modules\Product\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('frontend.home.index', [
            'featuredVendors' => Vendor::where('verification_status', 'approved')->where('status', 'active')->latest()->take(6)->get(),
            'featuredProducts' => Product::where('is_active', true)->latest()->take(8)->get(),
            'categories' => Category::where('is_active', true)->whereNull('parent_id')->take(8)->get(),
        ]);
    }
}
