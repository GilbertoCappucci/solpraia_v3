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
            $userId = $request->session()->previousUrl() ? $request->session()->get('_previous_user_id') : null;
            if ($userId) {
                Cache::forget('user-is-online-' . $userId);
            }
        }

        // Salva o ID do usuário atual na sessão para referência futura
        if (Auth::check()) {
            $request->session()->put('_previous_user_id', Auth::id());
        }

        return $next($request);
    }
}
