<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Http\Requests\ForgotPasswordRequest;
use App\Modules\Identity\Http\Requests\ResetPasswordRequest;
use App\Modules\Identity\Services\AuthenticationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * The same message is returned whether or not the email exists.
     */
    public function store(ForgotPasswordRequest $request, AuthenticationService $auth): RedirectResponse
    {
        $auth->sendPasswordResetLink($request->string('email')->toString());

        return back()->with('status', __('If an account with that email exists, a password reset link has been sent.'));
    }

    public function edit(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function update(ResetPasswordRequest $request, AuthenticationService $auth): RedirectResponse
    {
        $reset = $auth->resetPassword([
            'token' => $request->string('token')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
            'password_confirmation' => $request->string('password_confirmation')->toString(),
        ]);

        if (! $reset) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('This password reset link is invalid or has expired.')]);
        }

        return redirect()->route('login')->with('status', __('Your password has been reset. Please sign in.'));
    }
}
