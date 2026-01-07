<?php

namespace App\Services;

use App\Models\GlobalSetting;
use App\Models\Menu;
use App\Models\User;

class GlobalSettingService
{

    /**
     * Carrega as configurações globais do banco de dados
     * Busca as configurações do admin (user_id do usuário logado ou do próprio usuário se for admin)
     * 
     * @param User $user
     * @return ?GlobalSetting
     */
    public function loadGlobalSettings(User $user): ?GlobalSetting
    {
        // Determina qual admin buscar as configurações
        $adminId = $user->user_id ?? $user->id;

        // Busca configurações globais do admin
        $settings = GlobalSetting::where('user_id', $adminId)->first();

        return $settings;
    }

    public function getTimeLimits(User $user): array
    {
        $settings = GlobalSetting::where('user_id', $user->user_id ?? $user->id)->first();

        // Define os valores padrão para todos os limites de tempo, caso não existam configurações.
        $defaults = [
            'pending' => 15,
            'in_production' => 30,
            'releasing' => 15,
            'in_transit' => 10,
            'closed' => 60,
            'paid' => 120,      // Valor padrão, pois não está no formulário
            'occupied' => 999,  // Valor padrão, pois não está no formulário
            'reserved' => 999,  // Valor padrão, pois não está no formulário
            'close' => 999,     // Valor padrão, pois não está no formulário
        ];

        if (!$settings) {
            return $defaults;
        }

        return [
            'pending' => $settings->time_limit_pending ?? $defaults['pending'],
            'in_production' => $settings->time_limit_in_production ?? $defaults['in_production'],
            'releasing' => $settings->time_limit_releasing ?? $defaults['releasing'],
            'in_transit' => $settings->time_limit_in_transit ?? $defaults['in_transit'],
            'closed' => $settings->time_limit_closed ?? $defaults['closed'],
            'paid' => $settings->time_limit_paid ?? $defaults['paid'], // Mantém a lógica, mas usa um padrão
            'occupied' => $settings->time_limit_occupied ?? $defaults['occupied'], // Mantém a lógica, mas usa um padrão
            'reserved' => $settings->time_limit_reserved ?? $defaults['reserved'], // Mantém a lógica, mas usa um padrão
            'close' => $settings->time_limit_close ?? $defaults['close'], // Mantém a lógica, mas usa um padrão
        ];
    }
    
    public static function getActiveMenu(int $user_id): ?Menu
    {
        $settings = GlobalSetting::where('user_id', $user_id)->first();
        if (!$settings || !$settings->menu_id) {
            return null;
        }
        return Menu::find($settings->menu_id);
    }

    
    public static function getPixEnabled(int $user_id): bool
    {
        $settings = GlobalSetting::where('user_id', $user_id)->first();
        return $settings->pix_enabled ?? false;
    }

    public static function getPixKey(int $user_id): ?string
    {
        $settings = GlobalSetting::where('user_id', $user_id)->first();
        return $settings->pix_key ?? null;
    }
    

}
