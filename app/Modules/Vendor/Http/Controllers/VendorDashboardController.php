<?php

namespace App\Modules\Vendor\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class VendorDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $vendor = $request->user()->vendor;

        return view('vendor.dashboard.index', ['vendor' => $vendor]);
    }
}
