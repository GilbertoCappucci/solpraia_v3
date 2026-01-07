<?php

namespace App\Services\Check;

use App\Enums\CheckStatusEnum;
use App\Models\Check;
use App\Services\CheckService;

/**
 * Serviço responsável pelo gerenciamento de comandas (checks)
 * - Buscar checks existentes
 * - Criar checks automaticamente quando necessário
 * - Recalcular totais de checks ativos
 */
class CheckManagementService
{
    public function __construct(
        protected CheckService $checkService
    ) {}

    /**
     * Recalcula o total de TODAS as comandas ativas
     * Útil para sincronização global ou após migrações
     */
    public function recalculateAllActiveChecks(): void
    {
        $activeChecks = Check::whereIn('status', [
            CheckStatusEnum::OPEN->value,
            CheckStatusEnum::CLOSED->value
        ])->get();

        foreach ($activeChecks as $check) {
            $this->checkService->recalculateCheckTotal($check);
        }

        logger('✅ Todos os checks ativos foram recalculados', [
            'total_checks' => $activeChecks->count()
        ]);
    }

    /**
     * Busca a comanda ativa de uma mesa (apenas leitura, NÃO cria)
     * Use quando precisar verificar se existe check sem criar automaticamente
     * 
     * @return Check|null Retorna a comanda encontrada ou null
     */
    public function findCheck(int $tableId): ?Check
    {
        return Check::where('table_id', $tableId)
            ->whereIn('status', [
                CheckStatusEnum::OPEN->value,
                CheckStatusEnum::CLOSED->value
            ])
            ->first();
    }

    /**
     * Busca ou cria uma comanda para a mesa especificada
     * 
     * ⚠️ ATENÇÃO: Este método cria comandas AUTOMATICAMENTE se não encontrar uma ativa
     * Use-o com cuidado! Considere usar findCheck() se não quiser criar.
     * 
     * @return Check A comanda encontrada ou recém-criada
     */
    public function findOrCreateCheck(int $tableId): Check
    {
        // Primeiro tenta encontrar
        $existingCheck = $this->findCheck($tableId);
        
        if ($existingCheck) {
            return $existingCheck;
        }

        // Se não encontrou, cria nova comanda
        $newCheck = Check::create([
            'table_id' => $tableId,
            'status' => CheckStatusEnum::OPEN->value,
            'total' => 0,
            'user_id' => auth()->id()
        ]);

        logger('🆕 Check criado automaticamente', [
            'check_id' => $newCheck->id,
            'table_id' => $tableId,
            'user_id' => auth()->id() ?? 'sistema'
        ]);

        return $newCheck;
    }

    /**
     * Valida se o check pode receber novos pedidos
     * 
     * @return array ['valid' => bool, 'message' => string|null]
     */
    public function validateCheckForNewOrders(?Check $check): array
    {
        if (!$check) {
            return [
                'valid' => false,
                'message' => 'Nenhuma comanda ativa encontrada.'
            ];
        }

        if (!in_array($check->status, [CheckStatusEnum::OPEN->value, CheckStatusEnum::CLOSED->value])) {
            return [
                'valid' => false,
                'message' => 'A comanda não está em um status válido para receber pedidos.'
            ];
        }

        return ['valid' => true, 'message' => null];
    }

    public function getOrdersInCheckNotPaid(Check $check)
    {
        //Retorna orders que nao foram pagas e que nao estao em pending em seu historico de status
        $orders = $check->orders()
            ->where('is_paid', false)
            ->get();

        return $orders;
    }
}
