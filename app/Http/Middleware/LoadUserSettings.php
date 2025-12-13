<?php

namespace App\Http\Middleware;

use App\Services\GlobalSettingService;
use App\Services\UserPreferenceService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LoadUserSettings
{
    protected $globalSettingService;
    protected $userPreferenceService;

    public function __construct(GlobalSettingService $globalSettingService, UserPreferenceService $userPreferenceService)
    {
        $this->globalSettingService = $globalSettingService;
        $this->userPreferenceService = $userPreferenceService;
    }

    /**
     * Recarrega as configurações globais e preferências do usuário do banco de dados a cada request
     * Garante que mudanças feitas por outros usuários sejam refletidas imediatamente
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // Recarrega as configurações globais e preferências do banco para a sessão
            $this->globalSettingService->loadGlobalSettings(Auth::user());
            $this->userPreferenceService->loadUserPreferences(Auth::user());
        }

        return $next($request);
    }
}
