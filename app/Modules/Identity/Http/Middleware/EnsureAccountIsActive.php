<?php

namespace App\Modules\Identity\Http\Middleware;

use App\Modules\Identity\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->canAuthenticate()) {
            $pending = $user->status === UserStatus::PendingVerification || ! $user->hasVerifiedContact();
            $userId = $pending ? $user->getKey() : null;

            Auth::guard('web')->logout();

            if ($pending) {
                $request->session()->put('verification_user_id', $userId);
                $request->session()->migrate(true);

                return redirect()->route('verification.notice')
                    ->with('status', __('Verify your account to continue.'));
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', __('This account cannot sign in. Please contact support.'));
        }

        return $next($request);
    }
}
