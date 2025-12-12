<?php

use App\Services\SettingService;

if (!function_exists('time_limits')) {
    /**
     * Obtém os time limits configurados para o usuário logado
     * Sempre lê da sessão (que é sincronizada com o banco pelo middleware)
     * 
     * @return array
     */
    function time_limits(): array
    {
        $settingService = app(SettingService::class);
        return $settingService->getTimeLimits();
    }
}

if (!function_exists('user_setting')) {
    /**
     * Obtém uma configuração específica do usuário
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function user_setting(string $key, $default = null)
    {
        $settingService = app(SettingService::class);
        return $settingService->getSetting($key, $default);
    }
}
