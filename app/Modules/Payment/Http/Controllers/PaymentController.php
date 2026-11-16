<?php

namespace App\Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Http\Requests\CreatePaymentRequest;
use App\Modules\Payment\Models\Payment;
use App\Modules\Payment\Services\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function index(Request $request): View
    {
        $payments = Payment::whereHas('order', fn ($q) => $q->where('user_id', $request->user()->id))->latest()->paginate(20);

        return view('payments.index', ['payments' => $payments]);
    }

    public function store(CreatePaymentRequest $request): RedirectResponse
    {
        $this->payments->record(
            $request->integer('order_id'),
            (float) $request->input('amount'),
            $request->input('gateway')
        );

        return redirect()->route('payments.index')->with('status', __('Payment recorded.'));
    }
}
