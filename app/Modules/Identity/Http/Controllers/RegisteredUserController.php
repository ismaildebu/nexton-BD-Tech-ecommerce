<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Http\Requests\RegisterUserRequest;
use App\Modules\Identity\Services\AuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterUserRequest $request, AuthenticationService $auth): RedirectResponse
    {
        $user = $auth->register($request->validated());

        $auth->login($request, $user);

        return redirect()->route('profile.edit');
    }
}
