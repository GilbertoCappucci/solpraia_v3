<?php

namespace App\Services;

use App\Models\GlobalSetting;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class GlobalSettingService
{
    /**
     * Chaves das configurações globais na sessão
     */
    const SESSION_PREFIX = 'restaurant.';

    /**
     * Carrega as configurações globais para a sessão
     * Busca as configurações do admin (user_id do usuário logado ou do próprio usuário se for admin)
     * 
     * @param User $user
     * @return void
     */
    public function loadGlobalSettings(User $user): void
    {
        // Determina qual admin buscar as configurações
        $adminId = $user->user_id ?? $user->id;

        // Busca configurações globais do admin
        $settings = GlobalSetting::where('user_id', $adminId)->first();

        if (!$settings) {
            // Se não existe, cria com valores padrão do config
            $settings = $this->createDefaultSettings($adminId);
        }

        // Carrega na sessão
        $this->syncToSession($settings);
    }

    /**
     * Cria as configurações padrão para o admin a partir do config/restaurant.php
     * 
     * @param int $adminId
     * @return GlobalSetting
     */
    public function createDefaultSettings(int $adminId): GlobalSetting
    {
        $config = config('restaurant');

        return GlobalSetting::create([
            'user_id' => $adminId,
            'time_limit_pending' => $config['time_limits']['pending'] ?? 15,
            'time_limit_in_production' => $config['time_limits']['in_production'] ?? 30,
            'time_limit_in_transit' => $config['time_limits']['in_transit'] ?? 10,
            'time_limit_closed' => $config['time_limits']['closed'] ?? 5,
            'time_limit_releasing' => $config['time_limits']['releasing'] ?? 10,
            'pix_key' => null,
            'pix_key_type' => null,
            'pix_name' => null,
            'pix_city' => null,
        ]);
    }

    /**
     * Sincroniza os dados do banco para a sessão
     * 
     * @param GlobalSetting $settings
     * @return void
     */
    public function syncToSession(GlobalSetting $settings): void
    {
        // Time limits
        Session::put(self::SESSION_PREFIX . 'time_limits.pending', $settings->time_limit_pending);
        Session::put(self::SESSION_PREFIX . 'time_limits.in_production', $settings->time_limit_in_production);
        Session::put(self::SESSION_PREFIX . 'time_limits.in_transit', $settings->time_limit_in_transit);
        Session::put(self::SESSION_PREFIX . 'time_limits.closed', $settings->time_limit_closed);
        Session::put(self::SESSION_PREFIX . 'time_limits.releasing', $settings->time_limit_releasing);

        // PIX Settings
        Session::put(self::SESSION_PREFIX . 'pix.key', $settings->pix_key);
        Session::put(self::SESSION_PREFIX . 'pix.key_type', $settings->pix_key_type);
        Session::put(self::SESSION_PREFIX . 'pix.name', $settings->pix_name);
        Session::put(self::SESSION_PREFIX . 'pix.city', $settings->pix_city);
    }

    /**
     * Atualiza múltiplas configurações globais de uma vez
     * APENAS ADMIN pode executar esta ação
     * 
     * @param User $user
     * @param array $data
     * @return bool
     */
    public function updateSettings(User $user, array $data): bool
    {
        // Verifica se o usuário é admin (user_id é null)
        if ($user->user_id !== null) {
            throw new \Exception('Apenas administradores podem alterar configurações globais.');
        }

        $settings = GlobalSetting::where('user_id', $user->id)->first();

        if (!$settings) {
            $settings = $this->createDefaultSettings($user->id);
        }

        $dbData = [];

        foreach ($data as $key => $value) {
            // Atualiza na sessão
            Session::put(self::SESSION_PREFIX . $key, $value);

            // Prepara dados para o banco
            $dbField = $this->mapSessionKeyToDbField($key);
            if ($dbField) {
                $dbData[$dbField] = $value;
            }
        }

        if (!empty($dbData)) {
            $settings->update($dbData);
        }

        return true;
    }

    /**
     * Obtém uma configuração da sessão
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getSetting(string $key, $default = null)
    {
        return Session::get(self::SESSION_PREFIX . $key, $default);
    }

    /**
     * Obtém os time limits da sessão como array
     * 
     * @return array
     */
    public function getTimeLimits(): array
    {
        return [
            'pending' => $this->getSetting('time_limits.pending', config('restaurant.time_limits.pending')),
            'in_production' => $this->getSetting('time_limits.in_production', config('restaurant.time_limits.in_production')),
            'in_transit' => $this->getSetting('time_limits.in_transit', config('restaurant.time_limits.in_transit')),
            'closed' => $this->getSetting('time_limits.closed', config('restaurant.time_limits.closed')),
            'releasing' => $this->getSetting('time_limits.releasing', config('restaurant.time_limits.releasing')),
        ];
    }

    /**
     * Mapeia a chave da sessão para o campo do banco de dados
     * 
     * @param string $sessionKey
     * @return string|null
     */
    private function mapSessionKeyToDbField(string $sessionKey): ?string
    {
        $map = [
            'time_limits.pending' => 'time_limit_pending',
            'time_limits.in_production' => 'time_limit_in_production',
            'time_limits.in_transit' => 'time_limit_in_transit',
            'time_limits.closed' => 'time_limit_closed',
            'time_limits.releasing' => 'time_limit_releasing',
            'pix.key' => 'pix_key',
            'pix.key_type' => 'pix_key_type',
            'pix.name' => 'pix_name',
            'pix.city' => 'pix_city',
        ];

        return $map[$sessionKey] ?? null;
    }
}
