<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role  Role esperado (admin, device, etc)
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Admin tem acesso a tudo
        if (Auth::user()->role === 'admin') {
            return $next($request);
        }

        // Outros usuários precisam ter a role específica
        if (Auth::user()->role !== $role) {
            abort(403, 'Você não tem permissão para acessar esta página.');
        }

        return $next($request);
    }
}
