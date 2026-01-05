<?php

namespace App\Services;

use App\Enums\CheckStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\TableStatusEnum;
use App\Models\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class TableService
{
    protected $globalSettingService;

    public function __construct(GlobalSettingService $globalSettingService)
    {
        $this->globalSettingService = $globalSettingService;
    }
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
                return $this->applyFilters(
                    $table,
                    $filterTableStatuses,
                    $filterCheckStatuses,
                    $filterOrderStatuses,
                    $filterDepartaments,
                    $globalFilterMode
                );
            })
            ->map(function ($table) {
                return $this->enrichTableData($table);
            });
    }


    /**
     * Aplica filtros na table (lógica configurável por tipo de filtro)
     */

    protected function applyFilters(
        Table $table,
        array $filterTableStatuses,
        array $filterCheckStatuses,
        array $filterOrderStatuses,
        array $filterDepartaments,
        string $globalFilterMode = 'OR'
    ): bool {
        $currentCheck = $table->checks->sortByDesc('created_at')->first();

        // Se nenhum filtro está ativo, mostra todas as tables
        $hasAnyFilter = !empty($filterTableStatuses) || !empty($filterCheckStatuses) || !empty($filterOrderStatuses) || !empty($filterDepartaments);
        if (!$hasAnyFilter) {
            return true;
        }

        // Verifica filtro de status da mesa (OR: qualquer um dos status selecionados)
        $matchesTableStatus = !empty($filterTableStatuses) && in_array($table->status, $filterTableStatuses);

        $matchesCheckStatus = false;
        if (!empty($filterCheckStatuses) && $currentCheck) {
            // Verifica se há filtro de 'delayed_closed'
            $hasDelayedClosedFilter = in_array('delayed_closed', $filterCheckStatuses);
            $otherCheckStatuses = array_diff($filterCheckStatuses, ['delayed_closed']);

            // Verifica checks com status normais
            if (!empty($otherCheckStatuses)) {
                $matchesCheckStatus = in_array($currentCheck->status, $otherCheckStatuses);
            }

            // Verifica checks fechados atrasados (status virtual)
            if ($hasDelayedClosedFilter && !$matchesCheckStatus) {
                if ($currentCheck->status === CheckStatusEnum::CLOSED->value && $currentCheck->updated_at) {
                    $timeLimits = $this->globalSettingService->getTimeLimits($table->user);
                    $closedMinutes = abs((int) now()->diffInMinutes($currentCheck->updated_at));
                    $matchesCheckStatus = $closedMinutes > $timeLimits['closed'];
                }
            }
        }

        $matchesOrderStatus = false;
        if (!empty($filterOrderStatuses) && $currentCheck) {
            $hasDelayedFilter = in_array('delayed', $filterOrderStatuses);
            $otherStatuses = array_diff($filterOrderStatuses, ['delayed']);

            // OR: deve ter pelo menos um pedido correspondendo a QUALQUER status selecionado
            if (!empty($otherStatuses)) {
                $matchesOrderStatus = $currentCheck->orders
                    ->filter(fn($order) => in_array($order->status, $otherStatuses))
                    ->isNotEmpty();
            }

            if ($hasDelayedFilter && !$matchesOrderStatus) {
                $timeLimits = $this->globalSettingService->getTimeLimits($table->user);
                $now = now();
                $matchesOrderStatus = $currentCheck->orders
                    ->filter(function ($order) use ($now, $timeLimits) {
                        if (!$order->status_changed_at) return false;
                        $minutes = abs((int) $now->diffInMinutes($order->status_changed_at));
                        return match ($order->status) {
                            OrderStatusEnum::PENDING->value => $minutes > $timeLimits['pending'],
                            OrderStatusEnum::IN_PRODUCTION->value => $minutes > $timeLimits['in_production'],
                            OrderStatusEnum::IN_TRANSIT->value => $minutes > $timeLimits['in_transit'],
                            default => false
                        };
                    })
                    ->isNotEmpty();
            }
        }

        $matchesDepartament = false;
        if (!empty($filterDepartaments) && $currentCheck) {
            // OR: deve ter pedido de QUALQUER UM dos departamentos selecionados
            $matchesDepartament = $currentCheck->orders
                ->filter(function ($order) use ($filterDepartaments) {
                    return $order->product && in_array($order->product->production_local, $filterDepartaments);
                })
                ->isNotEmpty();
        }

        // Aplica lógica global (AND ou OR) entre as categorias de filtros
        if ($globalFilterMode === 'AND') {
            // AND: Quando há filtros de Order Status E Departamento, verifica se há pedidos que atendem AMBOS
            $hasOrderAndDeptFilters = !empty($filterOrderStatuses) && !empty($filterDepartaments);

            if ($hasOrderAndDeptFilters && $currentCheck) {
                // Filtro combinado: pedidos devem ser do departamento E ter o status selecionado
                $matchesCombinedOrderDept = $this->checkOrdersMatchBothFilters(
                    $currentCheck,
                    $filterOrderStatuses,
                    $filterDepartaments
                );

                // Se não passou no filtro combinado, já retorna false
                if (!$matchesCombinedOrderDept) {
                    return false;
                }
            } else {
                // Se não há ambos os filtros ativos, valida separadamente
                if (!empty($filterOrderStatuses) && !$matchesOrderStatus) {
                    return false;
                }
                if (!empty($filterDepartaments) && !$matchesDepartament) {
                    return false;
                }
            }

            // Valida os outros filtros normalmente
            if (!empty($filterTableStatuses) && !$matchesTableStatus) {
                return false;
            }
            if (!empty($filterCheckStatuses) && !$matchesCheckStatus) {
                return false;
            }

            return true;
        } else {
            // OR: deve atender pelo menos UMA das categorias que tiverem filtros ativos
            return $matchesTableStatus || $matchesCheckStatus || $matchesOrderStatus || $matchesDepartament;
        }
    }


    /**
     * Verifica se há pedidos que atendem simultaneamente aos filtros de status E departamento
     */

    protected function checkOrdersMatchBothFilters(
        $currentCheck,
        array $filterOrderStatuses,
        array $filterDepartaments
    ): bool {
        $hasDelayedFilter = in_array('delayed', $filterOrderStatuses);
        $otherStatuses = array_diff($filterOrderStatuses, ['delayed']);
        $timeLimits = $this->globalSettingService->getTimeLimits($table->user);
        $now = now();

        // Filtra pedidos que atendem AMBOS os critérios: departamento E status (OR dentro de cada)
        $matchingOrders = $currentCheck->orders->filter(function ($order) use (
            $filterDepartaments,
            $otherStatuses,
            $hasDelayedFilter,
            $now,
            $timeLimits
        ) {
            // Primeiro verifica departamento (OR: qualquer um dos departamentos)
            $matchesDept = $order->product && in_array($order->product->production_local, $filterDepartaments);

            if (!$matchesDept) {
                return false;
            }

            // Agora verifica status do pedido (OR: qualquer um dos status)
            $matchesStatus = false;

            // Verifica status normais
            if (!empty($otherStatuses) && in_array($order->status, $otherStatuses)) {
                $matchesStatus = true;
            }

            // Verifica status atrasado
            if (!$matchesStatus && $hasDelayedFilter && $order->status_changed_at) {
                $minutes = abs((int) $now->diffInMinutes($order->status_changed_at));
                $isDelayed = match ($order->status) {
                    OrderStatusEnum::PENDING->value => $minutes > $timeLimits['pending'],
                    OrderStatusEnum::IN_PRODUCTION->value => $minutes > $timeLimits['in_production'],
                    OrderStatusEnum::IN_TRANSIT->value => $minutes > $timeLimits['in_transit'],
                    default => false
                };
                if ($isDelayed) {
                    $matchesStatus = true;
                }
            }

            return $matchesStatus;
        });

        // OR: Deve ter pelo menos UM pedido que atende ambos os critérios
        return $matchingOrders->isNotEmpty();
    }


    /**
     * Enriquece os dados da table com informações de check e orders
     */
    protected function enrichTableData(Table $table): Table
    {
        $currentCheck = $table->checks->sortByDesc('created_at')->first();

        // Checks pagos ou cancelados são considerados inativos, 
        // a menos que esteja Pago mas a mesa ainda não tenha sido liberada (status RELEASING)
        $checkIsActive = $currentCheck && (
            !in_array($currentCheck->status, [
                CheckStatusEnum::PAID->value,
                CheckStatusEnum::CANCELED->value,
                CheckStatusEnum::MERGED->value,
            ]) || ($currentCheck->status === CheckStatusEnum::PAID->value && $table->status === TableStatusEnum::RELEASING->value)
        );

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
        $table->checkStatusLabel = match ($currentCheck->status) {
            CheckStatusEnum::OPEN->value => 'Aberto',
            CheckStatusEnum::CLOSED->value => 'Fechado',
            CheckStatusEnum::PAID->value => 'Pago',
            CheckStatusEnum::CANCELED->value => 'Cancelado',
            CheckStatusEnum::MERGED->value => 'Unida', // Adicionado MERGED
            default => 'Livre'
        };
        $table->checkStatusColor = match ($currentCheck->status) {
            CheckStatusEnum::OPEN->value => 'green',
            CheckStatusEnum::CLOSED->value => 'red',
            CheckStatusEnum::PAID->value => 'gray',
            CheckStatusEnum::CANCELED->value => 'orange',
            CheckStatusEnum::MERGED->value => 'purple', // Adicionado MERGED
            default => 'gray'
        };

        $table->checkTotal = $currentCheck->total ?? 0;

        if ($table->status === TableStatusEnum::RELEASING->value) {
            $table->checkStatusLabel = 'Liberando';
            $table->checkStatusColor = 'teal';
            $table->releasingMinutes = $table->updated_at ? abs((int) now()->diffInMinutes($table->updated_at)) : 0;
            $table->releasingTimestamp = $table->updated_at;
        } else {
            $table->releasingMinutes = 0;
            $table->releasingTimestamp = null;
        }

        // Calcula tempo desde que o check foi fechado
        if ($currentCheck->status === CheckStatusEnum::CLOSED->value && $currentCheck->updated_at) {
            $table->closedMinutes = abs((int) now()->diffInMinutes($currentCheck->updated_at));
            $table->closedTimestamp = $currentCheck->updated_at;
        } else {
            $table->closedMinutes = 0;
            $table->closedTimestamp = null;
        }
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
        $table->pendingTimestamp = $oldestPending?->status_changed_at;

        // In production orders
        $productionOrders = $orders->filter(fn($order) => $order->status === OrderStatusEnum::IN_PRODUCTION->value);
        $table->ordersInProduction = $productionOrders->count();
        $oldestProduction = $productionOrders->sortBy('status_changed_at')->first();
        $table->productionMinutes = $oldestProduction && $oldestProduction->status_changed_at
            ? abs((int) $now->diffInMinutes($oldestProduction->status_changed_at))
            : 0;
        $table->productionTimestamp = $oldestProduction?->status_changed_at;

        // In transit orders
        $transitOrders = $orders->filter(fn($order) => $order->status === OrderStatusEnum::IN_TRANSIT->value);
        $table->ordersInTransit = $transitOrders->count();
        $oldestTransit = $transitOrders->sortBy('status_changed_at')->first();
        $table->transitMinutes = $oldestTransit && $oldestTransit->status_changed_at
            ? abs((int) $now->diffInMinutes($oldestTransit->status_changed_at))
            : 0;
        $table->transitTimestamp = $oldestTransit?->status_changed_at;

        // Completed orders
        $completedOrders = $orders->filter(fn($order) => $order->status === OrderStatusEnum::COMPLETED->value);
        $table->ordersCompleted = $completedOrders->count();
        $oldestCompleted = $completedOrders->sortBy('status_changed_at')->first();
        $table->completedMinutes = $oldestCompleted && $oldestCompleted->status_changed_at
            ? abs((int) $now->diffInMinutes($oldestCompleted->status_changed_at))
            : 0;

        // Delayed orders (virtual status - pedidos que excederam o tempo limite)

        $timeLimits = $this->globalSettingService->getTimeLimits(Auth::user());
        $delayedOrders = $orders->filter(function ($order) use ($now, $timeLimits) {
            if (!$order->status_changed_at) return false;

            $minutes = abs((int) $now->diffInMinutes($order->status_changed_at));

            return match ($order->status) {
                OrderStatusEnum::PENDING->value => $minutes > $timeLimits['pending'],
                OrderStatusEnum::IN_PRODUCTION->value => $minutes > $timeLimits['in_production'],
                OrderStatusEnum::IN_TRANSIT->value => $minutes > $timeLimits['in_transit'],
                default => false
            };
        });
        $table->ordersDelayed = $delayedOrders->count();
    }

    /**
     * Define dados vazios quando não há check
     */
    protected function setEmptyData(Table $table): void
    {
        $table->checkStatus = null;

        // Define label baseado no status da mesa (não apenas "Livre")
        $table->checkStatusLabel = match ($table->status) {
            'occupied' => 'Ocupada',
            'reserved' => 'Reservada',
            'releasing' => 'Liberando',
            'close' => 'Fechada',
            default => 'Livre'
        };

        $table->checkStatusColor = match ($table->status) {
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
        $table->closedMinutes = 0;

        // Calcula tempo desde que a mesa está em RELEASING
        if ($table->status === 'releasing' && $table->updated_at) {
            $table->releasingMinutes = abs((int) now()->diffInMinutes($table->updated_at));
        } else {
            $table->releasingMinutes = 0;
        }
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
                return $this->addCalculatedProperties($table);
            });
    }

    /**
     * Adiciona propriedades calculadas à mesa (similar ao processamento em getFilteredTables)
     */
    protected function addCalculatedProperties(Table $table): Table
    {
        // Buscar o check ativo mais recente (OPEN ou CLOSED)
        $currentCheck = $table->checks
            ->whereIn('status', [
                CheckStatusEnum::OPEN->value,
                CheckStatusEnum::CLOSED->value,
            ])
            ->sortByDesc('created_at')
            ->first();
        
        // Verifica se o check é ativo (não está Pago, Cancelado ou Merged),
        // a menos que esteja Pago mas a mesa ainda não tenha sido liberada (status RELEASING)
        $checkIsActive = $currentCheck && (
            !in_array($currentCheck->status, [
                CheckStatusEnum::PAID->value,
                CheckStatusEnum::CANCELED->value,
                CheckStatusEnum::MERGED->value,
            ]) || ($currentCheck->status === CheckStatusEnum::PAID->value && $table->status === TableStatusEnum::RELEASING->value)
        );

        if ($checkIsActive) {
            $this->setCheckData($table, $currentCheck);
            $this->setOrdersData($table, $currentCheck);
        } else {
            $this->setEmptyData($table);
        }

        return $table;
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
        $canMerge = !in_array($table->status, $excludedStatuses);

        /*
        logger('🔍 canTableBeMerged', [
            'tableId' => $table->id,
            'tableName' => $table->name,
            'status' => $table->status,
            'canMerge' => $canMerge,
        ]);
        */

        return $canMerge;
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
     * 
     * @param Collection $tables
     * @return bool
     */
    public function canMergeTables(Collection $tables): bool
    {
        $mergeableTables = $this->getMergeableTables($tables);
        return $mergeableTables->count() >= 2;
    }
}