<?php

namespace App\Services;

use App\Enums\CheckStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\TableStatusEnum;
use App\Models\Table;
use Illuminate\Support\Collection;

class TableService
{
    /**
     * Busca e filtra tables com seus checks e orders
     */
    public function getFilteredTables(
        int $userId,
        array $filterTableStatuses = [],
        array $filterCheckStatuses = [],
        array $filterOrderStatuses = []
    ): Collection {
        $query = Table::where('user_id', $userId);
        
        // Só exclui mesas fechadas se não está filtrando especificamente por elas
        if (empty($filterTableStatuses) || !in_array('close', $filterTableStatuses)) {
            $query->where('status', '!=', TableStatusEnum::CLOSE->value);
        }
        
        return $query->with(['checks' => function($query) {
                $query->with(['orders.currentStatusHistory']);
            }])
            ->orderBy('number')
            ->get()
            ->filter(function($table) use ($filterTableStatuses, $filterCheckStatuses, $filterOrderStatuses) {
                return $this->applyFilters($table, $filterTableStatuses, $filterCheckStatuses, $filterOrderStatuses);
            })
            ->map(function($table) {
                return $this->enrichTableData($table);
            });
    }

    /**
     * Aplica filtros na table (lógica OR - pelo menos um filtro deve ser atendido)
     */
    protected function applyFilters(
        Table $table,
        array $filterTableStatuses,
        array $filterCheckStatuses,
        array $filterOrderStatuses
    ): bool {
        $currentCheck = $table->checks->sortByDesc('created_at')->first();
        
        // Se nenhum filtro está ativo, mostra todas as tables
        $hasAnyFilter = !empty($filterTableStatuses) || !empty($filterCheckStatuses) || !empty($filterOrderStatuses);
        if (!$hasAnyFilter) {
            return true;
        }
        
        // Verifica se atende pelo menos um filtro (OR)
        $matchesTableStatus = !empty($filterTableStatuses) && in_array($table->status, $filterTableStatuses);
        
        $matchesCheckStatus = false;
        if (!empty($filterCheckStatuses) && $currentCheck) {
            $matchesCheckStatus = in_array($currentCheck->status, $filterCheckStatuses);
        }
        
        $matchesOrderStatus = false;
        if (!empty($filterOrderStatuses) && $currentCheck) {
            $matchesOrderStatus = $currentCheck->orders
                ->filter(fn($order) => in_array($order->status, $filterOrderStatuses))
                ->isNotEmpty();
        }
        
        // Retorna true se atende pelo menos um dos filtros ativos
        return $matchesTableStatus || $matchesCheckStatus || $matchesOrderStatus;
    }

    /**
     * Enriquece os dados da table com informações de check e orders
     */
    protected function enrichTableData(Table $table): Table
    {
        $currentCheck = $table->checks->sortByDesc('created_at')->first();
        
        // Checks pagos ou cancelados são considerados inativos
        $checkIsActive = $currentCheck && 
                        !in_array($currentCheck->status, [
                            CheckStatusEnum::PAID->value,
                            CheckStatusEnum::CANCELED->value
                        ]);
        
        if ($checkIsActive) {
            $this->setCheckData($table, $currentCheck);
            $this->setOrdersData($table, $currentCheck);
        } else {
            $this->setEmptyData($table);
        }
        
        return $table;
    }

    /**
     * Define dados do check na table
     */
    protected function setCheckData(Table $table, $currentCheck): void
    {
        $table->checkId = $currentCheck->id;
        $table->checkStatus = $currentCheck->status;
        $table->checkStatusLabel = match($currentCheck->status) {
            CheckStatusEnum::OPEN->value => 'Aberto',
            CheckStatusEnum::CLOSED->value => 'Fechado',
            CheckStatusEnum::PAID->value => 'Pago',
            CheckStatusEnum::CANCELED->value => 'Cancelado',
            default => 'Livre'
        };
        $table->checkStatusColor = match($currentCheck->status) {
            CheckStatusEnum::OPEN->value => 'green',
            CheckStatusEnum::CLOSED->value => 'red',
            CheckStatusEnum::PAID->value => 'gray',
            CheckStatusEnum::CANCELED->value => 'orange',
            default => 'gray'
        };
        $table->checkTotal = $currentCheck->total ?? 0;
    }

    /**
     * Define dados dos orders na table
     * Usa order_status_history para obter status e tempo corretos
     */
    protected function setOrdersData(Table $table, $currentCheck): void
    {
        $orders = $currentCheck->orders;
        $now = now();
        
        // Pending orders
        $pendingOrders = $orders->filter(fn($order) => $order->status === OrderStatusEnum::PENDING->value);
        $table->ordersPending = $pendingOrders->count();
        $oldestPending = $pendingOrders->sortBy('status_changed_at')->first();
        $table->pendingMinutes = $oldestPending && $oldestPending->status_changed_at 
            ? abs((int) $now->diffInMinutes($oldestPending->status_changed_at)) 
            : 0;
        
        // In production orders
        $productionOrders = $orders->filter(fn($order) => $order->status === OrderStatusEnum::IN_PRODUCTION->value);
        $table->ordersInProduction = $productionOrders->count();
        $oldestProduction = $productionOrders->sortBy('status_changed_at')->first();
        $table->productionMinutes = $oldestProduction && $oldestProduction->status_changed_at 
            ? abs((int) $now->diffInMinutes($oldestProduction->status_changed_at)) 
            : 0;
        
        // In transit orders
        $transitOrders = $orders->filter(fn($order) => $order->status === OrderStatusEnum::IN_TRANSIT->value);
        $table->ordersInTransit = $transitOrders->count();
        $oldestTransit = $transitOrders->sortBy('status_changed_at')->first();
        $table->transitMinutes = $oldestTransit && $oldestTransit->status_changed_at 
            ? abs((int) $now->diffInMinutes($oldestTransit->status_changed_at)) 
            : 0;
        
        // Completed orders
        $completedOrders = $orders->filter(fn($order) => $order->status === OrderStatusEnum::COMPLETED->value);
        $table->ordersCompleted = $completedOrders->count();
        $oldestCompleted = $completedOrders->sortBy('status_changed_at')->first();
        $table->completedMinutes = $oldestCompleted && $oldestCompleted->status_changed_at 
            ? abs((int) $now->diffInMinutes($oldestCompleted->status_changed_at)) 
            : 0;
    }

    /**
     * Define dados vazios quando não há check
     */
    protected function setEmptyData(Table $table): void
    {
        $table->checkStatus = null;
        
        // Define label baseado no status da mesa (não apenas "Livre")
        $table->checkStatusLabel = match($table->status) {
            'occupied' => 'Ocupada',
            'reserved' => 'Reservada',
            'releasing' => 'Liberando',
            'close' => 'Fechada',
            default => 'Livre'
        };
        
        $table->checkStatusColor = match($table->status) {
            'occupied' => 'green',
            'reserved' => 'purple',
            'releasing' => 'teal',
            'close' => 'red',
            default => 'gray'
        };
        
        $table->ordersPending = 0;
        $table->ordersInProduction = 0;
        $table->ordersInTransit = 0;
        $table->ordersCompleted = 0;
        $table->checkTotal = 0;
        $table->pendingMinutes = 0;
        $table->productionMinutes = 0;
        $table->transitMinutes = 0;
        $table->completedMinutes = 0;
    }

    /**
     * Cria uma nova table
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
     * Atualiza o status de uma table
     */
    public function updateTableStatus(int $tableId, string $newStatus): bool
    {
        $table = $this->getTableById($tableId);
        
        if (!$table) {
            return false;
        }

        $table->status = $newStatus;
        return $table->save();
    }
}
