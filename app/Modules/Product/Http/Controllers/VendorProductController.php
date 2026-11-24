<?php

namespace App\Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Product\Http\Requests\StoreVendorProductRequest;
use App\Modules\Product\Http\Requests\UpdateVendorProductRequest;
use App\Modules\Product\Models\Brand;
use App\Modules\Product\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Services\ProductService;
use App\Modules\Vendor\Models\Vendor;
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

        $vendor = $this->vendorFor($request->user());
        $products = Product::where('vendor_id', $vendor->id)
            ->with('category')
            ->latest()
            ->paginate(15);

        return view('vendor.products.index', ['products' => $products, 'vendor' => $vendor]);
    }

    public function create(): View
    {
        Gate::authorize('create', Product::class);

        return view('vendor.products.create', $this->formData());
    }

    public function store(StoreVendorProductRequest $request): RedirectResponse
    {
        $this->products->create(array_merge($request->validated(), [
            'vendor_id' => $this->vendorFor($request->user())->id,
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
        $requiresApproval = $product->status === 'approved';
        $this->products->update($product, $request->validated());

        $message = $requiresApproval
            ? __('Product updated and hidden from the storefront pending admin re-approval.')
            : __('Product updated.');

        return redirect()->route('vendor.products.index')->with('status', $message);
    }

    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);
        $this->products->archive($product);

        return redirect()->route('vendor.products.index')->with('status', __('Product deactivated and hidden from the storefront.'));
    }

    private function vendorFor(User $user): Vendor
    {
        return Vendor::accessibleTo($user)->firstOrFail();
    }

    private function formData(): array
    {
        return [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ];
    }
}