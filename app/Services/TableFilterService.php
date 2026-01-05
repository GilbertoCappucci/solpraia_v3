<?php

namespace App\Services;

use App\Enums\CheckStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Table;

class TableFilterService
{
    public function __construct(
        protected GlobalSettingService $globalSettingService
    ) {}

    /**
     * Aplica filtros na table (lógica configurável por tipo de filtro)
     */
    public function applyFilters(
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
                    $filterDepartaments,
                    $table->user
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
        array $filterDepartaments,
        $user
    ): bool {
        $hasDelayedFilter = in_array('delayed', $filterOrderStatuses);
        $otherStatuses = array_diff($filterOrderStatuses, ['delayed']);
        $timeLimits = $this->globalSettingService->getTimeLimits($user);
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
}
