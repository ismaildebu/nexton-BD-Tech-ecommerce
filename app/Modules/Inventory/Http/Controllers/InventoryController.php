<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Http\Requests\StoreInventoryRequest;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Inventory\Services\InventoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function index(): View
    {
        return view('inventory.index', ['items' => Inventory::with('product')->latest()->paginate(20)]);
    }

    public function store(StoreInventoryRequest $request): RedirectResponse
    {
        $this->inventory->update(
            $request->integer('product_id'),
            $request->integer('quantity'),
            $request->integer('low_stock_threshold')
        );

        return redirect()->route('admin.inventory.index')->with('status', __('Inventory saved.'));
    }
}
