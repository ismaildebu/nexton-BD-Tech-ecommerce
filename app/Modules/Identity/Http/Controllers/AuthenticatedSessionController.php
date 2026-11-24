<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Exceptions\PendingVerificationException;
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
        try {
            $user = $auth->authenticate(
                $request->string('login')->toString(),
                $request->string('password')->toString(),
                (string) $request->ip(),
            );
        } catch (PendingVerificationException $exception) {
            $request->session()->regenerate();
            $request->session()->put('verification_user_id', $exception->user->getKey());

            return redirect()->route('verification.notice')
                ->with('status', __('Verify your account to continue.'));
        }

        $auth->login($request, $user, $request->boolean('remember'));

        return redirect()->intended($user->is_admin ? route('admin.dashboard') : route('profile.edit'));
    }

    public function destroy(Request $request, AuthenticationService $auth): RedirectResponse
    {
        $auth->logout($request);

        return redirect('/');
    }
}
