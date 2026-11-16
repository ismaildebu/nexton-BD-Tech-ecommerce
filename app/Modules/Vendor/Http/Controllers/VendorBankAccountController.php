<?php

namespace App\Modules\Vendor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorBankAccount;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VendorBankAccountController extends Controller
{
    public function index(Vendor $vendor): View
    {
        return view('vendor.bank-accounts.index', ['vendor' => $vendor, 'accounts' => $vendor->bankAccounts]);
    }

    public function store(Request $request, Vendor $vendor): RedirectResponse
    {
        $data = $request->validate([
            'account_holder_name' => ['required', 'string', 'max:150'],
            'bank_name' => ['required', 'string', 'max:150'],
            'account_number' => ['required', 'string', 'min:6', 'max:32'],
        ]);

        $vendor->bankAccounts()->create([
            'account_holder_name' => $data['account_holder_name'],
            'bank_name' => $data['bank_name'],
            'account_number' => $data['account_number'],
        ]);

        return redirect()->route('vendor.bank-accounts.index', $vendor)->with('status', __('Bank account added.'));
    }
}
