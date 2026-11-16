<?php

namespace App\Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Customer\Services\CustomerDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CustomerDashboardController extends Controller
{
    public function __construct(private readonly CustomerDashboardService $dashboard) {}

    public function index(Request $request): View
    {
        return view('customer.dashboard.index', $this->dashboard->build($request->user()));
    }
}
