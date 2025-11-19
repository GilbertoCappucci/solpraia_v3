<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !Auth::user()->active) {
            Auth::logout();
            
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect('/login')->with('error', 'Sua conta foi desativada.');
        }

        return $next($request);
    }
}
