<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Wishlist\Models\Wishlist;
use Illuminate\Contracts\View\View;

class WishlistController extends Controller
{
    public function index(): View
    {
        return view('admin.wishlist.index', ['items' => Wishlist::latest()->paginate(20)]);
    }
}
