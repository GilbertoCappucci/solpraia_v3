<?php

use App\Services\GlobalSettingService;

if (!function_exists('time_limits')) {
    /**
     * Obtém os time limits configurados (configuração global do admin)
     * Sempre lê da sessão (que é sincronizada com o banco pelo middleware)
     * 
     * @return array
     */
    function time_limits(): array
    {
        $settingService = app(GlobalSettingService::class);
        return $settingService->getTimeLimits();
    }
}

if (!function_exists('global_setting')) {
    /**
     * Obtém uma configuração global (PIX, time limits, etc)
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function global_setting(string $key, $default = null)
    {
        $settingService = app(GlobalSettingService::class);
        return $settingService->getSetting($key, $default);
    }
}
