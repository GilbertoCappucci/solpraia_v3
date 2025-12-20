<?php

namespace App\Services;

use App\Models\UserPreference;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class UserPreferenceService
{
    /**
     * Chaves das preferências do usuário na sessão
     */
    const SESSION_PREFIX = 'user.';

    /**
     * Carrega as preferências do usuário para a sessão
     * 
     * @param User $user
     * @return void
     */
    public function loadUserPreferences(User $user): void
    {
        // Busca preferências do usuário no banco
        $preferences = UserPreference::where('user_id', $user->id)->first();

        if (!$preferences) {
            // Se não existe, cria com valores padrão do config
            $preferences = $this->createDefaultPreferences($user);
        }

        // Carrega na sessão
        $this->syncToSession($preferences);
    }

    /**
     * Cria as preferências padrão para o usuário a partir do config/restaurant.php
     * 
     * @param User $user
     * @return UserPreference
     */
    public function createDefaultPreferences(User $user): UserPreference
    {
        $config = config('solpraia');

        return UserPreference::create([
            'user_id' => $user->id,
            'table_filter_table' => $config['table_filter']['table'] ?? [],
            'table_filter_check' => $config['table_filter']['check'] ?? [],
            'table_filter_order' => $config['table_filter']['order'] ?? [],
            'table_filter_departament' => $config['table_filter']['departament'] ?? [],
            'table_filter_mode' => 'AND',
        ]);
    }

    /**
     * Sincroniza os dados do banco para a sessão
     * 
     * @param UserPreference $preferences
     * @return void
     */
    public function syncToSession(UserPreference $preferences): void
    {
        // Table filters
        Session::put(self::SESSION_PREFIX . 'table_filter_table', $preferences->table_filter_table);
        Session::put(self::SESSION_PREFIX . 'table_filter_check', $preferences->table_filter_check);
        Session::put(self::SESSION_PREFIX . 'table_filter_order', $preferences->table_filter_order);
        Session::put(self::SESSION_PREFIX . 'table_filter_departament', $preferences->table_filter_departament);
        Session::put(self::SESSION_PREFIX . 'table_filter_mode', $preferences->table_filter_mode ?? 'AND');
    }

    /**
     * Atualiza múltiplas preferências de uma vez
     * 
     * @param User $user
     * @param array $data
     * @return bool
     */
    public function updatePreferences(User $user, array $data): bool
    {
        $preferences = UserPreference::where('user_id', $user->id)->first();

        if (!$preferences) {
            $preferences = $this->createDefaultPreferences($user);
        }

        $dbData = [];

        foreach ($data as $key => $value) {
            // Atualiza na sessão
            Session::put(self::SESSION_PREFIX . $key, $value);

            // Se a chave corresponde a um campo do banco, adiciona
            if (in_array($key, ['table_filter_table', 'table_filter_check', 'table_filter_order', 'table_filter_departament', 'table_filter_mode'])) {
                $dbData[$key] = $value;
            }
        }

        if (!empty($dbData)) {
            $preferences->update($dbData);
        }

        return true;
    }

    /**
     * Obtém uma preferência da sessão
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getPreference(string $key, $default = null)
    {
        return Session::get(self::SESSION_PREFIX . $key, $default);
    }
}
