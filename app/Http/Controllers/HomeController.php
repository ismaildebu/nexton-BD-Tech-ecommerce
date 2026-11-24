<?php

namespace App\Http\Controllers;

use App\Modules\Product\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Wishlist\Models\Wishlist;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredProducts = Product::with(['category', 'vendor', 'brand', 'inventory'])
            ->where('is_active', true)
            ->where('status', 'approved')
            ->whereHas('vendor', function ($query) {
                $query->where('verification_status', 'approved')->where('status', 'active');
            })
            ->latest()
            ->take(8)
            ->get();

        $spotlightProduct = $featuredProducts->first();
        $wishlistedProductIds = auth()->check()
            ? Wishlist::where('user_id', auth()->id())->whereIn('product_id', $featuredProducts->pluck('id'))->pluck('product_id')->map(fn ($id) => (int) $id)->all()
            : [];

        $categories = Category::where('is_active', true)->whereNull('parent_id')->take(8)->get();
        $featuredVendors = Vendor::where('verification_status', 'approved')->where('status', 'active')->latest()->take(6)->get();

        return view('frontend.home.index', [
            'spotlightProduct' => $spotlightProduct,
            'featuredProducts' => $featuredProducts,
            'categories' => $categories,
            'featuredVendors' => $featuredVendors,
            'wishlistedProductIds' => $wishlistedProductIds,
        ]);
    }
}
