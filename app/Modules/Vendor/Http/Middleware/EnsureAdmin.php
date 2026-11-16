<?php

namespace App\Modules\Vendor\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('admin') ?? $request->user();

        if (! $user || ! $user->is_admin) {
            abort(403);
        }

        return $next($request);
    }
}
