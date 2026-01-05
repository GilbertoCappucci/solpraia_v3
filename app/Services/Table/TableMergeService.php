<?php

namespace App\Services\Table;

use App\Enums\TableStatusEnum;
use App\Models\Table;
use Illuminate\Support\Collection;

class TableMergeService
{
    /**
     * Libera múltiplas mesas, definindo seu status para FREE.
     * Usado após a união de mesas para liberar as mesas de origem.
     * 
     * @param array $tableIds IDs das mesas a serem liberadas.
     * @return void
     */
    public function releaseTables(array $tableIds): void
    {
        // Usar loop para garantir que os Observers sejam disparados
        foreach ($tableIds as $tableId) {
            $table = Table::find($tableId);
            if ($table) {
                $table->update(['status' => TableStatusEnum::FREE->value]);
            }
        }
    }

    /**
     * Verifica se uma mesa pode ser selecionada para união
     * 
     * @param Table|object $table
     * @return bool
     */
    public function canTableBeMerged($table): bool
    {
        // Mesas com os seguintes status não podem ser unidas:
        // - releasing: mesa está sendo liberada
        // - close: mesa está fechada permanentemente
        // - reserved: mesa está reservada
        $excludedStatuses = ['releasing', 'close', 'reserved'];
        return !in_array($table->status, $excludedStatuses);
    }

    /**
     * Filtra uma coleção de mesas retornando apenas as que podem ser unidas
     * 
     * @param Collection $tables
     * @return Collection
     */
    public function getMergeableTables(Collection $tables): Collection
    {
        return $tables->filter(fn($table) => $this->canTableBeMerged($table));
    }

    /**
     * Verifica se há mesas suficientes para realizar uma união
     * Requer pelo menos 2 mesas que podem ser unidas
     * E pelo menos uma delas deve ter um check ativo
     * 
     * @param Collection $tables
     * @return bool
     */
    public function canMergeTables(Collection $tables): bool
    {
        $mergeableTables = $this->getMergeableTables($tables);
        
        // Precisa de pelo menos 2 mesas que podem ser unidas
        if ($mergeableTables->count() < 2) {
            return false;
        }
        
        // Pelo menos uma das mesas deve ter um check ativo
        $hasActiveCheck = $mergeableTables->contains(function ($table) {
            return !empty($table->checkId);
        });
        
        return $hasActiveCheck;
    }
}
