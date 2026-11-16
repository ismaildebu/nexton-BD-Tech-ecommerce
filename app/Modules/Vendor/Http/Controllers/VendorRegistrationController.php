<?php

namespace App\Modules\Vendor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vendor\Services\VendorRegistrationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VendorRegistrationController extends Controller
{
    public function __construct(private readonly VendorRegistrationService $registration) {}

    public function create(): View
    {
        return view('vendor.registration.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
        ]);

        $this->registration->register($request->user(), $data);

        return redirect()->route('vendor.registration.status')->with('status', __('Application submitted.'));
    }

    public function status(Request $request): View
    {
        $vendor = $request->user()->vendor ?? null;

        return view('vendor.registration.status', ['vendor' => $vendor]);
    }
}
