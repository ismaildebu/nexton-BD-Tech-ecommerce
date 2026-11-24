<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Review\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    public function index(): View
    {
        return view('admin.reviews.index', [
            'reviews' => Review::with(['user', 'product.vendor'])->latest()->paginate(20),
        ]);
    }

    public function destroy(Review $review): RedirectResponse
    {
        Gate::authorize('delete', $review);

        $review->delete();

        return redirect()->route('admin.reviews.index')->with('status', __('Review deleted.'));
    }
}
