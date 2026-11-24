<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Http\Requests\UpdatePaymentStatusRequest;
use App\Modules\Payment\Models\Payment;
use App\Modules\Payment\Services\PaymentService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function index(): View
    {
        $payments = Payment::with(['order:id,order_number,status,payment_status', 'vendor:id,business_name'])->latest()->paginate(20);

        return view('admin.payments.index', ['payments' => $payments]);
    }

    public function transition(UpdatePaymentStatusRequest $request, Payment $payment): RedirectResponse
    {
        try {
            if ($request->string('status')->toString() === 'paid') {
                $this->payments->markPaid($payment, $request->input('transaction_id'));
            } else {
                $this->payments->markRefunded($payment);
            }
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('status', __('Payment status updated.'));
    }
}
