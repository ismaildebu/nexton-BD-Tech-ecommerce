<?php

namespace App\Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Product\Http\Requests\StoreProductRequest;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Services\ProductService;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(Request $request): View
    {
        $query = Product::where('is_active', true);

        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->string('q')->toString().'%');
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->integer('category'));
        }

        $products = $query->latest()->paginate(12);

        return view('products.index', ['products' => $products]);
    }

    public function show(Product $product): View
    {
        return view('products.show', ['product' => $product]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $vendor = $request->user()->vendor;

        $this->products->create(array_merge($request->validated(), [
            'vendor_id' => $vendor?->id,
            'status' => 'pending',
            'is_active' => false,
        ]));

        return redirect()->route('products.index')->with('status', __('Product created.'));
    }
}
