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
        $featuredProducts = Product::with(['category', 'vendor', 'brand'])
            ->where('is_active', true)
            ->where('status', 'approved')
            ->latest()
            ->take(8)
            ->get();

        $spotlightProduct = $featuredProducts->first();

        $categories = Category::where('is_active', true)->whereNull('parent_id')->take(8)->get();
        $featuredVendors = Vendor::where('verification_status', 'approved')->where('status', 'active')->latest()->take(6)->get();

        return view('frontend.home.index', [
            'spotlightProduct' => $spotlightProduct,
            'featuredProducts' => $featuredProducts,
            'categories' => $categories,
            'featuredVendors' => $featuredVendors,
        ]);
    }
}
