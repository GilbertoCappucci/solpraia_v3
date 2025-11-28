<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectByRole
{
    /**
     * Handle an incoming request.
     * Redireciona o usuário para a página apropriada baseado em sua role.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Admin vai para dashboard
            if ($user->isAdmin()) {
                return redirect()->route('dashboard');
            }
            
            // Device vai para tables
            if ($user->isDevice()) {
                return redirect()->route('tables');
            }
        }

        // Se não autenticado, continua para a página normal
        return $next($request);
    }
}
