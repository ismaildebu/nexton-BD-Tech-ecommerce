<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Shipping\Models\Shipping;
use Illuminate\Contracts\View\View;

class ShippingController extends Controller
{
    public function index(): View
    {
        return view('admin.shipping.index', ['shippings' => Shipping::latest()->paginate(20)]);
    }
}
