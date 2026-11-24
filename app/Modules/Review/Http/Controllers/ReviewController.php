<?php

namespace App\Modules\Review\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Review\Http\Requests\CreateReviewRequest;
use App\Modules\Review\Models\Review;
use App\Modules\Review\Services\ReviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(private readonly ReviewService $reviews) {}

    public function index(Request $request): View
    {
        $reviews = Review::where('user_id', $request->user()->id)
            ->with('product')
            ->latest()
            ->paginate(20);

        return view('reviews.index', ['reviews' => $reviews]);
    }

    public function store(CreateReviewRequest $request): RedirectResponse
    {
        $this->reviews->create(
            $request->user()->id,
            $request->integer('product_id'),
            $request->integer('rating'),
            $request->input('comment')
        );

        return redirect()
            ->to(route('products.show', $request->integer('product_id')).'#reviews')
            ->with('status', __('Review submitted.'));
    }
}
