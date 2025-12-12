<?php

namespace App\Http\Middleware;

use App\Services\SettingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LoadUserSettings
{
    protected $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * Recarrega as configurações do usuário do banco de dados a cada request
     * Garante que mudanças feitas por outros usuários sejam refletidas imediatamente
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // Recarrega as configurações do banco para a sessão
            $this->settingService->loadUserSettings(Auth::user());
        }

        return $next($request);
    }
}
