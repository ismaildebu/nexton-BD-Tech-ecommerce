<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Review\Models\Review;
use Illuminate\Contracts\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        return view('admin.reviews.index', ['reviews' => Review::latest()->paginate(20)]);
    }
}
