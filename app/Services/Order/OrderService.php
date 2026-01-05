<?php

namespace App\Services\Order;

use App\Models\Check;
use App\Models\Table;
use App\Services\Check\CheckManagementService;
use App\Services\Order\OrderCancellationService;
use App\Services\Order\OrderOperationsService;
use App\Services\Order\OrderStatusService;
use App\Services\Order\OrderValidationService;
use Illuminate\Support\Collection;

/**
 * OrderService - Ponto de entrada unificado para operações de pedidos
 * 
 * Este serviço atua como uma Facade, delegando para serviços especializados:
 * - CheckManagementService: Gestão de comandas (checks)
 * - OrderStatusService: Atualização de status de pedidos e mesas
 * - OrderCancellationService: Cancelamento de pedidos
 * - OrderOperationsService: Operações especiais (duplicate, merge, agrupamento)
 * - OrderValidationService: Validações de negócio
 * 
 * Esta arquitetura mantém compatibilidade com código legado enquanto
 * organiza responsabilidades em serviços coesos e testáveis.
 */
class OrderService
{
    public function __construct(
        protected CheckManagementService $checkManagement,
        protected OrderStatusService $statusService,
        protected OrderCancellationService $cancellationService,
        protected OrderOperationsService $operationsService,
        protected OrderValidationService $validationService
    ) {}

    // ========== CheckManagementService ==========

    public function recalculateAllActiveChecks(): void
    {
        $this->checkManagement->recalculateAllActiveChecks();
    }

    public function findCheck(int $tableId): ?Check
    {
        return $this->checkManagement->findCheck($tableId);
    }

    public function findOrCreateCheck(int $tableId): Check
    {
        return $this->checkManagement->findOrCreateCheck($tableId);
    }

    public function validateCheckForNewOrders(?Check $check): array
    {
        return $this->checkManagement->validateCheckForNewOrders($check);
    }

    // ========== OrderStatusService ==========

    public function updateStatuses(
        Table $table,
        ?Check $check,
        ?string $newTableStatus,
        ?string $newCheckStatus
    ): array {
        return $this->statusService->updateStatuses($table, $check, $newTableStatus, $newCheckStatus);
    }

    public function updateOrderStatus(int $orderId, string $newStatus, int $qtyToMove = 0): array
    {
        return $this->statusService->updateOrderStatus($orderId, $newStatus, $qtyToMove);
    }

    // ========== OrderCancellationService ==========

    public function cancelOrder(int $orderId, int $qtyToCancel = 1): array
    {
        return $this->cancellationService->cancelOrder($orderId, $qtyToCancel);
    }

    public function cancelOrders(array $orderIds): array
    {
        return $this->cancellationService->cancelOrders($orderIds);
    }

    // ========== OrderOperationsService ==========

    public function duplicatePendingOrder(int $orderId): array
    {
        return $this->operationsService->duplicatePendingOrder($orderId);
    }

    public function getActiveOrdersGrouped(?Check $check): array
    {
        return $this->operationsService->getActiveOrdersGrouped($check);
    }

    public function calculateOrderStats(Collection $orders): array
    {
        return $this->operationsService->calculateOrderStats($orders);
    }

    public function mergeChecks(array $sourceCheckIds, int $destinationCheckId): array
    {
        return $this->operationsService->mergeChecks($sourceCheckIds, $destinationCheckId);
    }

    // ========== OrderValidationService ==========

    public static function hasPendingOrders(Check $check): bool
    {
        return app(OrderValidationService::class)->hasPendingOrders($check);
    }

    public static function areAllOrdersCompletedOrCanceled(Check $check): bool
    {
        return app(OrderValidationService::class)->areAllOrdersCompletedOrCanceled($check);
    }
}
