<?php

namespace App\Modules\Wishlist\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Wishlist\Http\Requests\AddToWishlistRequest;
use App\Modules\Wishlist\Models\Wishlist;
use App\Modules\Wishlist\Services\WishlistService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(private readonly WishlistService $wishlist) {}

    public function index(Request $request): View
    {
        $items = Wishlist::where('user_id', $request->user()->id)->with('product')->latest()->paginate(20);

        return view('wishlist.index', ['items' => $items]);
    }

    public function store(AddToWishlistRequest $request): RedirectResponse
    {
        $this->wishlist->add($request->user()->id, $request->integer('product_id'));

        return redirect()->route('wishlist.index')->with('status', __('Added to wishlist.'));
    }
}
