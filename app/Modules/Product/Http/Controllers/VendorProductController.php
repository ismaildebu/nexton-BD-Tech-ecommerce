<?php

namespace App\Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Product\Http\Requests\StoreVendorProductRequest;
use App\Modules\Product\Http\Requests\UpdateVendorProductRequest;
use App\Modules\Product\Models\Brand;
use App\Modules\Product\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Services\ProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VendorProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(Request $request): View
    {
        Gate::authorize('manage', Product::class);

        $products = Product::where('vendor_id', $request->user()->vendor->id)
            ->with('category')
            ->latest()
            ->paginate(15);

        return view('vendor.products.index', ['products' => $products]);
    }

    public function create(): View
    {
        Gate::authorize('create', Product::class);

        return view('vendor.products.create', $this->formData());
    }

    public function store(StoreVendorProductRequest $request): RedirectResponse
    {
        $this->products->create(array_merge($request->validated(), [
            'vendor_id' => $request->user()->vendor->id,
            'status' => 'pending',
            'is_active' => false,
        ]));

        return redirect()->route('vendor.products.index')
            ->with('status', __('Product submitted. It will be visible after admin approval.'));
    }

    public function edit(Product $product): View
    {
        Gate::authorize('update', $product);

        return view('vendor.products.edit', $this->formData() + ['product' => $product]);
    }

    public function update(UpdateVendorProductRequest $request, Product $product): RedirectResponse
    {
        $this->products->update($product, $request->validated());

        return redirect()->route('vendor.products.index')->with('status', __('Product updated.'));
    }

    private function formData(): array
    {
        return [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ];
    }
}