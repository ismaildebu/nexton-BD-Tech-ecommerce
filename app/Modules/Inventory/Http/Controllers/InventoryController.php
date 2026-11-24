<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Http\Requests\StoreInventoryRequest;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Inventory\Models\InventoryMovement;
use App\Modules\Inventory\Services\InventoryService;
use App\Modules\Product\Models\Product;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function index(): View
    {
        $products = Product::with('inventory')->latest()->paginate(20);
        $movements = InventoryMovement::with(['product', 'user'])
            ->latest('created_at')
            ->limit(15)
            ->get();

        return view('admin.inventory.index', [
            'products' => $products,
            'movements' => $movements,
        ]);
    }

    public function store(StoreInventoryRequest $request): RedirectResponse
    {
        try {
            $this->inventory->adjust(
                $request->integer('product_id'),
                $request->integer('quantity'),
                $request->user(),
                $request->filled('note') ? $request->string('note')->toString() : null,
                $request->has('low_stock_threshold') ? $request->integer('low_stock_threshold') : null,
            );
        } catch (DomainException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.inventory.index')->with('status', __('Inventory adjusted and audit movement recorded.'));
    }
}
