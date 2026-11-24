<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Services\ProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    private const STATUSES = ['pending', 'approved', 'rejected'];

    public function __construct(private readonly ProductService $products) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');

        $query = Product::with(['vendor:id,business_name', 'category:id,name'])->latest();

        if (in_array($status, self::STATUSES, true)) {
            $query->where('status', $status);
        } else {
            $status = null;
        }

        return view('admin.products.index', [
            'products' => $query->paginate(20)->withQueryString(),
            'status' => $status,
            'pendingCount' => Product::where('status', 'pending')->count(),
        ]);
    }

    public function approve(Request $request, Product $product): RedirectResponse
    {
        try {
            $this->products->approve($product);
        } catch (\DomainException $e) {
            return back()->with('product_error', $e->getMessage());
        }

        Log::info('Product approved', ['product_id' => $product->id, 'admin_id' => $request->user()->id]);

        return back()->with('product_notice', __('Product approved and is now live.'));
    }

    public function reject(Request $request, Product $product): RedirectResponse
    {
        try {
            $this->products->reject($product);
        } catch (\DomainException $e) {
            return back()->with('product_error', $e->getMessage());
        }

        Log::info('Product rejected', ['product_id' => $product->id, 'admin_id' => $request->user()->id]);

        return back()->with('product_notice', __('Product rejected.'));
    }
}