<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Commission\Models\Commission;
use Illuminate\Contracts\View\View;

class CommissionController extends Controller
{
    public function index(): View
    {
        return view('admin.commissions.index', ['commissions' => Commission::latest()->paginate(20)]);
    }
}
