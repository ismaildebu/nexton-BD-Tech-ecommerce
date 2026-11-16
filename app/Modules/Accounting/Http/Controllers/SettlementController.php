<?php

namespace App\Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Accounting\Http\Requests\CreateSettlementRequest;
use App\Modules\Accounting\Models\Settlement;
use App\Modules\Accounting\Services\SettlementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function __construct(private readonly SettlementService $settlements) {}

    public function index(Request $request): View
    {
        $settlements = Settlement::whereHas('vendor', fn ($q) => $q->where('owner_user_id', $request->user()->id))->latest()->paginate(20);

        return view('settlements.index', ['settlements' => $settlements]);
    }

    public function store(CreateSettlementRequest $request): RedirectResponse
    {
        $this->settlements->create(
            $request->integer('vendor_id'),
            (float) $request->input('amount')
        );

        return redirect()->route('settlements.index')->with('status', __('Settlement created.'));
    }
}
