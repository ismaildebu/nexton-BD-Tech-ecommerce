<?php

namespace App\Modules\Commission\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Commission\Http\Requests\CreateCommissionRequest;
use App\Modules\Commission\Models\Commission;
use App\Modules\Commission\Services\CommissionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
        $this->commissions->record(
            $request->integer('vendor_id'),
            (float) $request->input('amount'),
            (float) ($request->input('rate') ?? 0)
        );

        return redirect()->route('commissions.index')->with('status', __('Commission recorded.'));
    }
}
