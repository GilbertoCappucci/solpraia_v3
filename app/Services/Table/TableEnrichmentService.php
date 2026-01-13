<?php

namespace App\Services\Table;

use App\Enums\CheckStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\TableStatusEnum;
use App\Models\Table;
use App\Services\GlobalSettingService;
use Illuminate\Support\Facades\Auth;

class TableEnrichmentService
{
    public function __construct(
        protected GlobalSettingService $globalSettingService
    ) {}

    /**
     * Enriquece os dados da table com informações de check e orders
     */
    public function enrichTableData(Table $table): Table
    {
        $currentCheck = $table->checks->sortByDesc('created_at')->first();

        $this->setCheckData($table, $currentCheck);

        $this->setOrdersData($table, $currentCheck);

        return $table;
    }

    /**
     * Define dados do check na table
     */
    public function setCheckData(Table $table, $currentCheck): void
    {
        $table->checkId = $currentCheck->id;
        $table->checkStatus = $currentCheck->status;

        $table->checkStatusLabel = match ($currentCheck->status) {
            CheckStatusEnum::OPEN->value => CheckStatusEnum::OPEN->getLabel(CheckStatusEnum::OPEN),
            CheckStatusEnum::CLOSED->value => CheckStatusEnum::CLOSED->getLabel(CheckStatusEnum::CLOSED),
            CheckStatusEnum::PAID->value => CheckStatusEnum::PAID->getLabel(CheckStatusEnum::PAID),
            CheckStatusEnum::CANCELED->value => CheckStatusEnum::CANCELED->getLabel(CheckStatusEnum::CANCELED),
            CheckStatusEnum::MERGED->value => CheckStatusEnum::MERGED->getLabel(CheckStatusEnum::MERGED),
        };

        $table->checkStatusColor = match ($currentCheck->status) {
            CheckStatusEnum::OPEN->value => CheckStatusEnum::OPEN->getColor(CheckStatusEnum::OPEN),
            CheckStatusEnum::CLOSED->value => CheckStatusEnum::CLOSED->getColor(CheckStatusEnum::CLOSED),
            CheckStatusEnum::PAID->value => CheckStatusEnum::PAID->getColor(CheckStatusEnum::PAID),
            CheckStatusEnum::CANCELED->value => CheckStatusEnum::CANCELED->getColor(CheckStatusEnum::CANCELED),
            CheckStatusEnum::MERGED->value => CheckStatusEnum::MERGED->getColor(CheckStatusEnum::MERGED),
        };

        $table->checkTotal = $currentCheck->total ?? 0;

        if ($table->status === TableStatusEnum::RELEASING->value) {
            $table->checkStatusLabel = TableStatusEnum::RELEASING->getLabel(TableStatusEnum::RELEASING);
            $table->checkStatusColor = TableStatusEnum::RELEASING->getColor(TableStatusEnum::RELEASING);
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
    public function setOrdersData(Table $table, $currentCheck): void
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
                OrderStatusEnum::PENDING->value => $minutes > $timeLimits[OrderStatusEnum::PENDING->value],
                OrderStatusEnum::IN_PRODUCTION->value => $minutes > $timeLimits[OrderStatusEnum::IN_PRODUCTION->value],
                OrderStatusEnum::IN_TRANSIT->value => $minutes > $timeLimits[OrderStatusEnum::IN_TRANSIT->value],
                default => false
            };
        });
        
        $table->ordersDelayed = $delayedOrders->count();
    }

    /**
     * Define dados vazios quando não há check
     */
    public function setEmptyData(Table $table): void
    {
        $table->checkStatus = null;

        // Define label baseado no status da mesa (não apenas "Livre")
        $table->checkStatusLabel = match ($table->status) {
            'occupied' => 'Ocupada',
            'reserved' => 'Reservada',
            'releasing' => 'Liberando',
            'closed' => 'Fechada',
            default => 'Livre'
        };

        $table->checkStatusColor = match ($table->status) {
            'occupied' => 'green',
            'reserved' => 'purple',
            'releasing' => 'teal',
            'closed' => 'red',
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
            $table->releasingTimestamp = $table->updated_at;
        } else {
            $table->releasingMinutes = 0;
            $table->releasingTimestamp = null;
        }
        $table->completedMinutes = 0;
    }
}
