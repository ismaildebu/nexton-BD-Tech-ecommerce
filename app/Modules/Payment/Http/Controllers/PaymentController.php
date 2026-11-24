<?php

namespace App\Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Http\Requests\CreatePaymentRequest;
use App\Modules\Payment\Models\Payment;
use App\Modules\Order\Models\Order;
use App\Modules\Payment\Services\PaymentService;
use DomainException;
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
        $order = Order::findOrFail($request->integer('order_id'));

        try {
            $this->payments->record(
                $order,
                $request->input('gateway'),
                $request->filled('vendor_id') ? $request->integer('vendor_id') : null,
            );
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('payments.index')->with('status', __('Payment recorded.'));
    }
}
