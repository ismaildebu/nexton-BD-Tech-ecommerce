<?php

namespace App\Modules\Vendor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorBankAccount;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VendorBankAccountController extends Controller
{
    public function index(Vendor $vendor): View
    {
        Gate::authorize('manage', $vendor);

        return view('vendor.bank-accounts.index', ['vendor' => $vendor, 'accounts' => $vendor->bankAccounts]);
    }

    public function store(Request $request, Vendor $vendor): RedirectResponse
    {
        Gate::authorize('manage', $vendor);

        $data = $request->validate([
            'account_holder_name' => ['required', 'string', 'max:150'],
            'bank_name' => ['required', 'string', 'max:150'],
            'branch_name' => ['nullable', 'string', 'max:150'],
            'account_number' => ['required', 'string', 'min:6', 'max:32', 'regex:/^[A-Za-z0-9 -]+$/'],
            'routing_number' => ['nullable', 'string', 'max:64'],
            'account_type' => ['nullable', Rule::in(['savings', 'current', 'checking'])],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $isDefault = $request->boolean('is_default');

        DB::transaction(function () use ($vendor, $data, $isDefault) {
            if ($isDefault) {
                $vendor->bankAccounts()->update(['is_default' => false]);
            }

            $vendor->bankAccounts()->create([
                'account_holder_name' => $data['account_holder_name'],
                'bank_name' => $data['bank_name'],
                'branch_name' => $data['branch_name'] ?? null,
                'account_number' => $data['account_number'],
                'routing_number' => $data['routing_number'] ?? null,
                'account_type' => $data['account_type'] ?? null,
                'verification_status' => 'pending',
                'is_default' => $isDefault,
            ]);
        });

        return redirect()->route('vendor.bank-accounts.index', $vendor)->with('status', __('Bank account added.'));
    }
}
