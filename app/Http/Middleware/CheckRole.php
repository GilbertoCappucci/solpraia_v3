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
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // O método `isAdmin()` foi corrigido para usar o enum, então esta verificação é confiável.
        // A regra de negócio existente é que o admin tem acesso a tudo.
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Para outros usuários, comparamos o valor do enum com a string da rota.
        if ($user->role->value !== $role) {
            abort(403, 'Você não tem permissão para acessar esta página.');
        }

        return $next($request);
    }
}
