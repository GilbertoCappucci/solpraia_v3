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
     * @return void
     */
    public function loadGlobalSettings(User $user): GlobalSetting
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
        return [
            'pending' => $settings->pending_minutes,
            'in_production' => $settings->in_production_minutes,
            'releasing' => $settings->releasing_minutes,
            'in_transit' => $settings->in_transit_minutes,
            'closed' => $settings->closed_minutes,
            'paid' => $settings->paid_minutes,
            'occupied' => $settings->occupied_minutes,
            'reserved' => $settings->reserved_minutes,
            'close' => $settings->close_minutes,
        ];
    }
    
    public static function getActiveMenu(int $user_id): ?Menu
    {
        $settings = GlobalSetting::where('user_id', $user_id)->first();
        return Menu::find($settings->menu_id);
    }

    public static function getPollingInterval(int $user_id): int
    {
        $settings = GlobalSetting::where('user_id', $user_id)->first();
        return $settings->polling_interval;
    }
    
    public static function getPixEnabled(int $user_id): bool
    {
        $settings = GlobalSetting::where('user_id', $user_id)->first();
        return $settings->pix_enabled;
    }
    

}
