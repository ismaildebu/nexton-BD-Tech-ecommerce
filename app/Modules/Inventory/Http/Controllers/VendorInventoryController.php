<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Http\Requests\AdjustVendorInventoryRequest;
use App\Modules\Inventory\Models\InventoryMovement;
use App\Modules\Inventory\Services\InventoryService;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VendorInventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function index(Request $request): View
    {
        $vendor = Vendor::accessibleTo($request->user())->firstOrFail();
        $products = Product::where('vendor_id', $vendor->id)->with('inventory')->latest()->paginate(15);
        $movements = InventoryMovement::with('product')
            ->whereIn('product_id', Product::where('vendor_id', $vendor->id)->select('id'))
            ->latest('created_at')
            ->limit(20)
            ->get();

        return view('vendor.inventory.index', [
            'vendor' => $vendor,
            'products' => $products,
            'movements' => $movements,
        ]);
    }

    public function adjust(AdjustVendorInventoryRequest $request): RedirectResponse
    {
        $product = Product::findOrFail($request->integer('product_id'));
        Gate::authorize('update', $product);

        $quantity = $request->integer('quantity');
        $change = $request->input('direction') === 'in' ? $quantity : -$quantity;

        try {
            $this->inventory->adjust(
                $product->id,
                $change,
                $request->user(),
                $request->string('note')->toString() ?: null
            );
        } catch (DomainException $exception) {
            return redirect()->route('vendor.inventory.index')->with('error', $exception->getMessage());
        }

        return redirect()->route('vendor.inventory.index')->with('status', __('Inventory adjusted.'));
    }
}
