<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Accounting\Http\Requests\CreateSettlementRequest;
use App\Modules\Accounting\Http\Requests\TransitionSettlementRequest;
use App\Modules\Accounting\Models\Settlement;
use App\Modules\Accounting\Services\SettlementService;
use App\Modules\Vendor\Models\Vendor;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SettlementController extends Controller
{
    public function __construct(private readonly SettlementService $settlements) {}

    public function index(): View
    {
        return view('admin.settlements.index', [
            'settlements' => Settlement::with('vendor')->latest()->paginate(20),
            'vendors' => Vendor::orderBy('business_name')->get(['id', 'business_name']),
        ]);
    }

    public function store(CreateSettlementRequest $request): RedirectResponse
    {
        $this->settlements->create($request->integer('vendor_id'), (float) $request->input('amount'));

        return redirect()->route('admin.settlements.index')->with('status', __('Settlement created.'));
    }

    public function transition(TransitionSettlementRequest $request, Settlement $settlement): RedirectResponse
    {
        try {
            $this->settlements->transition($settlement, $request->string('status')->toString());
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('status', __('Settlement status updated.'));
    }
}
