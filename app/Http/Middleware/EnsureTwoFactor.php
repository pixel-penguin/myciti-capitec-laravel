<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->two_factor_enabled) {
            return $next($request);
        }

        if ($request->session()->get('two_factor_passed')) {
            return $next($request);
        }

        if ($request->routeIs('admin.two-factor.*')) {
            return $next($request);
        }

        return redirect()->route('admin.two-factor.challenge');
    }
}
