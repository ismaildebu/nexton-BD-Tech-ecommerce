<?php

namespace App\Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CustomerAccountController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->loadMissing('customerProfile');

        return view('customer.account.index', ['user' => $user]);
    }
}
