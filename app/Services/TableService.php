<?php

namespace App\Services;

use App\Enums\CheckStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\TableStatusEnum;
use App\Models\Table;
use App\Services\Table\TableFilterService;
use App\Services\Table\TableEnrichmentService;
use App\Services\Table\TableMergeService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class TableService
{
    public function __construct(
        protected GlobalSettingService $globalSettingService,
        protected TableFilterService $filterService,
        protected TableEnrichmentService $enrichmentService,
        protected TableMergeService $mergeService
    ) {}
    /**
     * Busca e filtra tables com seus checks e orders
     */

    public function getFilteredTables(
        int $userId,
        array $filterTableStatuses = [],
        array $filterCheckStatuses = [],
        array $filterOrderStatuses = [],
        array $filterDepartaments = [],
        string $globalFilterMode = 'OR'
    ): Collection {
        $query = Table::where('user_id', $userId);

        return $query->with(['checks' => function ($query) {
            $query->with(['orders.currentStatusHistory', 'orders.product']);
        }])
            ->orderBy('number')
            ->get()
            ->filter(function ($table) use (
                $filterTableStatuses,
                $filterCheckStatuses,
                $filterOrderStatuses,
                $filterDepartaments,
                $globalFilterMode
            ) {
                return $this->filterService->applyFilters(
                    $table,
                    $filterTableStatuses,
                    $filterCheckStatuses,
                    $filterOrderStatuses,
                    $filterDepartaments,
                    $globalFilterMode
                );
            })
            ->map(function ($table) {
                return $this->enrichmentService->enrichTableData($table);
            });
    }


    /**
     * Busca uma table por ID
     */
    public function createTable(int $userId, string $name, int $number): Table
    {
        return Table::create([
            'user_id' => $userId,
            'name' => $name,
            'number' => $number,
            'status' => TableStatusEnum::FREE->value,
        ]);
    }

    /**
     * Valida dados para criação de table
     */
    public function validateTableData(array $data): array
    {
        $userId = $data['userId'] ?? null;

        $rules = [
            'newTableName' => 'nullable|string|max:255',
            'newTableNumber' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($userId) {
                    if ($userId && Table::where('user_id', $userId)->where('number', $value)->exists()) {
                        $fail('Já existe um local com este número.');
                    }
                },
            ],
        ];

        $messages = [
            'newTableNumber.required' => 'O número do local é obrigatório.',
            'newTableNumber.integer' => 'O número deve ser um valor numérico.',
            'newTableNumber.min' => 'O número deve ser maior que zero.',
        ];

        return ['rules' => $rules, 'messages' => $messages];
    }

    /**
     * Busca uma table por ID
     */
    public function getTableById(int $tableId): ?Table
    {
        return Table::find($tableId);
    }

    /**
     * Busca multiple tables por seus IDs com relacionamentos
     */
    public function getTablesByIds(array $tableIds): Collection
    {
        if (empty($tableIds)) {
            return collect();
        }

        return Table::whereIn('id', $tableIds)
            ->with(['checks' => function ($query) {
                $query->with(['orders.currentStatusHistory', 'orders.product']);
            }])
            ->orderBy('number')
            ->get()
            ->map(function ($table) {
                // Adicionar propriedades calculadas 
                return $this->enrichmentService->enrichTableData($table);
            });
    }

    /**
     * Atualiza o status de uma table
     */
    public function updateTableStatus(int $tableId, string $newStatus): bool
    {
        $table = $this->getTableById($tableId);

        if (!$table) {
            return false;
        }

        $table->status = $newStatus;
        return $table->save(); // O Observer vai disparar o evento automaticamente
    }

    /**
     * Libera múltiplas mesas, definindo seu status para FREE.
     * Usado após a união de mesas para liberar as mesas de origem.
     * 
     * @param array $tableIds IDs das mesas a serem liberadas.
     * @return void
     */
    public function releaseTables(array $tableIds): void
    {
        $this->mergeService->releaseTables($tableIds);
    }

    /**
     * Verifica se uma mesa pode ser selecionada para união
     * 
     * @param Table|object $table
     * @return bool
     */
    public function canTableBeMerged($table): bool
    {
        return $this->mergeService->canTableBeMerged($table);
    }

    /**
     * Filtra uma coleção de mesas retornando apenas as que podem ser unidas
     * 
     * @param Collection $tables
     * @return Collection
     */
    public function getMergeableTables(Collection $tables): Collection
    {
        return $this->mergeService->getMergeableTables($tables);
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
        return $this->mergeService->canMergeTables($tables);
    }
}