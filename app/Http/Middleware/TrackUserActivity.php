<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // Marca usuário como online (sem expiração)
            Cache::forever('user-is-online-' . Auth::id(), true);
        } else {
            // Se não está autenticado, verifica se tinha cache anterior e remove
            $adminId = $request->session()->previousUrl() ? $request->session()->get('_previous_user_id') : null;
            if ($adminId) {
                Cache::forget('user-is-online-' . $adminId);
            }
        }

        // Salva o ID do usuário atual na sessão para referência futura
        if (Auth::check()) {
            $request->session()->put('_previous_user_id', Auth::id());
        }

        return $next($request);
    }
}
