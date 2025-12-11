<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class SettingService
{
    /**
     * Chaves das configurações na sessão
     */
    const SESSION_PREFIX = 'restaurant.';
    
    /**
     * Carrega as configurações do usuário para a sessão
     * Se não existir no banco, cria usando os valores padrão do config
     * 
     * @param User $user
     * @return void
     */
    public function loadUserSettings(User $user): void
    {
        // Busca configurações do usuário no banco
        $settings = Setting::where('user_id', $user->id)->first();
        
        if (!$settings) {
            // Se não existe, cria com valores padrão do config
            $settings = $this->createDefaultSettings($user);
        }
        
        // Carrega na sessão
        $this->syncToSession($settings);
    }
    
    /**
     * Cria as configurações padrão para o usuário a partir do config/restaurant.php
     * 
     * @param User $user
     * @return Setting
     */
    public function createDefaultSettings(User $user): Setting
    {
        $config = config('restaurant');
        
        return Setting::create([
            'user_id' => $user->id,
            'time_limit_pending' => $config['time_limits']['pending'],
            'time_limit_in_production' => $config['time_limits']['in_production'],
            'time_limit_in_transit' => $config['time_limits']['in_transit'],
            'time_limit_closed' => $config['time_limits']['closed'],
            'time_limit_releasing' => $config['time_limits']['releasing'],
            'table_filter_mode' => $config['table_filter']['mode'],
            'table_filter_table' => $config['table_filter']['table'],
            'table_filter_check' => $config['table_filter']['check'],
            'table_filter_order' => $config['table_filter']['order'],
            'table_filter_departament' => $config['table_filter']['departament'],
        ]);
    }
    
    /**
     * Sincroniza os dados do banco para a sessão
     * 
     * @param Setting $settings
     * @return void
     */
    public function syncToSession(Setting $settings): void
    {
        // Time limits
        Session::put(self::SESSION_PREFIX . 'time_limits.pending', $settings->time_limit_pending);
        Session::put(self::SESSION_PREFIX . 'time_limits.in_production', $settings->time_limit_in_production);
        Session::put(self::SESSION_PREFIX . 'time_limits.in_transit', $settings->time_limit_in_transit);
        Session::put(self::SESSION_PREFIX . 'time_limits.closed', $settings->time_limit_closed);
        Session::put(self::SESSION_PREFIX . 'time_limits.releasing', $settings->time_limit_releasing);
        
        // Table filters
        Session::put(self::SESSION_PREFIX . 'table_filter.mode', $settings->table_filter_mode);
        Session::put(self::SESSION_PREFIX . 'table_filter.table', $settings->table_filter_table);
        Session::put(self::SESSION_PREFIX . 'table_filter.check', $settings->table_filter_check);
        Session::put(self::SESSION_PREFIX . 'table_filter.order', $settings->table_filter_order);
        Session::put(self::SESSION_PREFIX . 'table_filter.departament', $settings->table_filter_departament);
    }
    
    /**
     * Atualiza uma configuração específica na sessão e no banco
     * 
     * @param User $user
     * @param string $key Chave da configuração (ex: 'time_limits.pending', 'table_filter.mode')
     * @param mixed $value
     * @return bool
     */
    public function updateSetting(User $user, string $key, $value): bool
    {
        // Atualiza na sessão
        Session::put(self::SESSION_PREFIX . $key, $value);
        
        // Mapeia a chave para o campo do banco
        $dbField = $this->mapSessionKeyToDbField($key);
        
        if (!$dbField) {
            return false;
        }
        
        // Atualiza no banco
        $settings = Setting::where('user_id', $user->id)->first();
        
        if (!$settings) {
            // Se não existe, cria primeiro
            $settings = $this->createDefaultSettings($user);
        }
        
        $settings->update([$dbField => $value]);
        
        return true;
    }
    
    /**
     * Atualiza múltiplas configurações de uma vez
     * 
     * @param User $user
     * @param array $data Array associativo com as chaves e valores (ex: ['time_limits.pending' => 5])
     * @return bool
     */
    public function updateSettings(User $user, array $data): bool
    {
        $settings = Setting::where('user_id', $user->id)->first();
        
        if (!$settings) {
            $settings = $this->createDefaultSettings($user);
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
     * Obtém todas as configurações da sessão
     * 
     * @return array
     */
    public function getAllSettings(): array
    {
        return [
            'time_limits' => [
                'pending' => $this->getSetting('time_limits.pending'),
                'in_production' => $this->getSetting('time_limits.in_production'),
                'in_transit' => $this->getSetting('time_limits.in_transit'),
                'closed' => $this->getSetting('time_limits.closed'),
                'releasing' => $this->getSetting('time_limits.releasing'),
            ],
            'table_filter' => [
                'mode' => $this->getSetting('table_filter.mode'),
                'table' => $this->getSetting('table_filter.table'),
                'check' => $this->getSetting('table_filter.check'),
                'order' => $this->getSetting('table_filter.order'),
                'departament' => $this->getSetting('table_filter.departament'),
            ],
        ];
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
            'table_filter.mode' => 'table_filter_mode',
            'table_filter.table' => 'table_filter_table',
            'table_filter.check' => 'table_filter_check',
            'table_filter.order' => 'table_filter_order',
            'table_filter.departament' => 'table_filter_departament',
        ];
        
        return $map[$sessionKey] ?? null;
    }
}
