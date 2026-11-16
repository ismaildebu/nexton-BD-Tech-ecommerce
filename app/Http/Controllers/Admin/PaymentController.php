<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Models\Payment;
use Illuminate\Contracts\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        return view('admin.payments.index', ['payments' => Payment::latest()->paginate(20)]);
    }
}
