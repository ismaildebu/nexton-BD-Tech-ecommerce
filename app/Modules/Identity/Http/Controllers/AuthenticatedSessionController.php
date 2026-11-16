<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Http\Requests\LoginRequest;
use App\Modules\Identity\Services\AuthenticationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request, AuthenticationService $auth): RedirectResponse
    {
        $user = $auth->authenticate(
            $request->string('login')->toString(),
            $request->string('password')->toString(),
            (string) $request->ip(),
        );

        $auth->login($request, $user, $request->boolean('remember'));

        return redirect()->intended(route('profile.edit'));
    }

    public function destroy(Request $request, AuthenticationService $auth): RedirectResponse
    {
        $auth->logout($request);

        return redirect('/');
    }
}
