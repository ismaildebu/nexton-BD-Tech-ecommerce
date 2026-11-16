<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Accounting\Models\Settlement;
use Illuminate\Contracts\View\View;

class SettlementController extends Controller
{
    public function index(): View
    {
        return view('admin.settlements.index', ['settlements' => Settlement::latest()->paginate(20)]);
    }
}
