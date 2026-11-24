<?php

namespace App\Modules\Commission\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Commission\Http\Requests\CreateCommissionRequest;
use App\Modules\Commission\Models\Commission;
use App\Modules\Commission\Services\CommissionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use DomainException;

class CommissionController extends Controller
{
    public function __construct(private readonly CommissionService $commissions) {}

    public function index(Request $request): View
    {
        $commissions = Commission::whereHas('vendor', fn ($q) => $q->where('owner_user_id', $request->user()->id))->latest()->paginate(20);

        return view('commissions.index', ['commissions' => $commissions]);
    }

    public function store(CreateCommissionRequest $request): RedirectResponse
    {
        try {
            $this->commissions->record(
                $request->integer('vendor_id'),
                $request->filled('amount') ? (float) $request->input('amount') : null,
                $request->filled('rate') ? (float) $request->input('rate') : null,
                $request->filled('order_id') ? $request->integer('order_id') : null,
                $request->user(),
            );
        } catch (DomainException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.commissions.index')->with('status', __('Commission recorded.'));
    }
}
