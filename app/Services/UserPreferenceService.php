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
        $config = config('restaurant');

        return UserPreference::create([
            'user_id' => $user->id,
            'table_filter_table' => $config['table_filter']['table'] ?? [],
            'table_filter_check' => $config['table_filter']['check'] ?? [],
            'table_filter_order' => $config['table_filter']['order'] ?? [],
            'table_filter_departament' => $config['table_filter']['departament'] ?? [],
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
        Session::put(self::SESSION_PREFIX . 'table_filter.table', $preferences->table_filter_table);
        Session::put(self::SESSION_PREFIX . 'table_filter.check', $preferences->table_filter_check);
        Session::put(self::SESSION_PREFIX . 'table_filter.order', $preferences->table_filter_order);
        Session::put(self::SESSION_PREFIX . 'table_filter.departament', $preferences->table_filter_departament);
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

            // Prepara dados para o banco
            $dbField = $this->mapSessionKeyToDbField($key);
            if ($dbField) {
                $dbData[$dbField] = $value;
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

    /**
     * Obtém todos os filtros de tabela da sessão
     * 
     * @return array
     */
    public function getTableFilters(): array
    {
        return [
            'table' => $this->getPreference('table_filter.table', []),
            'check' => $this->getPreference('table_filter.check', []),
            'order' => $this->getPreference('table_filter.order', []),
            'departament' => $this->getPreference('table_filter.departament', []),
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
            'table_filter.table' => 'table_filter_table',
            'table_filter.check' => 'table_filter_check',
            'table_filter.order' => 'table_filter_order',
            'table_filter.departament' => 'table_filter_departament',
        ];

        return $map[$sessionKey] ?? null;
    }
}
