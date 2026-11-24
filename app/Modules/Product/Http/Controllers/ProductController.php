<?php

namespace App\Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Product\Http\Requests\StoreProductRequest;
use App\Modules\Product\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Services\ProductService;
use App\Modules\Review\Models\Review;
use App\Modules\Wishlist\Models\Wishlist;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(Request $request): View
    {
        $query = $this->publicProducts()->with(['inventory', 'category', 'brand', 'vendor']);

        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->string('q')->toString().'%');
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->integer('category'));
        }

        $products = $query->latest()->paginate(12);
        $wishlistedProductIds = $this->wishlistedProductIds($products->pluck('id'));

        return view('products.index', [
            'products' => $products,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'wishlistedProductIds' => $wishlistedProductIds,
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($this->publicProducts()->whereKey($product->getKey())->exists(), 404);

        $product->load(['vendor', 'category', 'brand', 'inventory']);
        $reviews = Review::where('product_id', $product->id)->with('user')->latest()->paginate(10);
        $ratingSummary = Review::where('product_id', $product->id)
            ->selectRaw('AVG(rating) as average_rating, COUNT(*) as review_count')
            ->first();

        return view('products.show', [
            'product' => $product,
            'reviews' => $reviews,
            'ratingSummary' => $ratingSummary,
            'stock' => (int) ($product->inventory?->quantity ?? 0),
            'isWishlisted' => in_array($product->id, $this->wishlistedProductIds(collect([$product->id])), true),
        ]);
    }

    private function publicProducts(): Builder
    {
        return Product::query()
            ->where('is_active', true)
            ->where('status', 'approved')
            ->whereHas('vendor', function (Builder $query) {
                $query->where('verification_status', 'approved')->where('status', 'active');
            });
    }

    private function wishlistedProductIds($productIds): array
    {
        if (! auth()->check() || $productIds->isEmpty()) {
            return [];
        }

        return Wishlist::where('user_id', auth()->id())
            ->whereIn('product_id', $productIds)
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->all();
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
