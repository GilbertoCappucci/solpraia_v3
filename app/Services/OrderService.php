<?php

namespace App\Services;

use App\Enums\CheckStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\TableStatusEnum;
use App\Models\Check;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Support\Collection;

class OrderService
{
    /**
     * Busca ou cria check aberto para a mesa
     */
    public function findOrCreateCheck(int $tableId): ?Check
    {
        return Check::where('table_id', $tableId)
            ->where('status', CheckStatusEnum::OPEN->value)
            ->first();
    }

    /**
     * Valida e atualiza status da mesa e check
     */
    public function updateStatuses(
        Table $table,
        ?Check $check,
        ?string $newTableStatus,
        ?string $newCheckStatus
    ): array {
        $errors = [];
        
        // Validação: Não pode mudar mesa para FREE se houver check com valor
        if ($newTableStatus === TableStatusEnum::FREE->value) {
            if ($check && $check->total > 0) {
                $errors[] = 'Não é possível liberar a mesa com conta em aberto.';
            }
        }
        
        // Validação: Não pode fechar conta sem pedidos
        if ($newCheckStatus === CheckStatusEnum::CLOSING->value) {
            if (!$check || $check->total <= 0) {
                $errors[] = 'Não é possível fechar conta sem pedidos.';
            }
        }
        
        // Validação: Não pode marcar como CLOSED sem estar em CLOSING
        if ($newCheckStatus === CheckStatusEnum::CLOSED->value) {
            if (!$check || $check->status !== CheckStatusEnum::CLOSING->value) {
                $errors[] = 'A conta precisa estar em "Fechando" antes de ser marcada como "Fechada".';
            }
        }
        
        // Validação: Não pode marcar como PAID sem estar CLOSED
        if ($newCheckStatus === CheckStatusEnum::PAID->value) {
            if (!$check || $check->status !== CheckStatusEnum::CLOSED->value) {
                $errors[] = 'A conta precisa estar "Fechada" antes de ser marcada como "Paga".';
            }
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Atualiza status da mesa
        if ($newTableStatus && $newTableStatus !== $table->status) {
            $table->update(['status' => $newTableStatus]);
        }
        
        // Atualiza status do check
        if ($newCheckStatus && $check && $newCheckStatus !== $check->status) {
            $check->update(['status' => $newCheckStatus]);
            
            // Se marcou como PAID, libera a mesa
            if ($newCheckStatus === CheckStatusEnum::PAID->value) {
                $table->update(['status' => TableStatusEnum::FREE->value]);
            }
        }
        
        return ['success' => true];
    }

    /**
     * Busca pedidos ativos agrupados por status
     */
    public function getActiveOrdersGrouped(?Check $check): array
    {
        if (!$check) {
            return [
                'pending' => collect(),
                'inProduction' => collect(),
                'inTransit' => collect(),
                'completed' => collect(),
            ];
        }

        $activeOrders = Order::where('check_id', $check->id)
            ->with('product')
            ->whereIn('status', [
                OrderStatusEnum::PENDING->value,
                OrderStatusEnum::IN_PRODUCTION->value,
                OrderStatusEnum::IN_TRANSIT->value,
                OrderStatusEnum::COMPLETED->value
            ])
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('status');
        
        return [
            'pending' => $activeOrders->get(OrderStatusEnum::PENDING->value, collect()),
            'inProduction' => $activeOrders->get(OrderStatusEnum::IN_PRODUCTION->value, collect()),
            'inTransit' => $activeOrders->get(OrderStatusEnum::IN_TRANSIT->value, collect()),
            'completed' => $activeOrders->get(OrderStatusEnum::COMPLETED->value, collect()),
        ];
    }

    /**
     * Calcula totais e tempos para cada grupo de pedidos
     */
    public function calculateOrderStats(Collection $orders): array
    {
        $now = now();
        $total = $orders->sum(fn($order) => $order->product->price * $order->quantity);
        $time = $orders->first() ? (int) $now->diffInMinutes($orders->first()->created_at) : 0;
        
        return [
            'total' => $total,
            'time' => $time,
        ];
    }

    /**
     * Atualiza status individual de um pedido
     */
    public function updateOrderStatus(int $orderId, string $newStatus): void
    {
        Order::where('id', $orderId)->update([
            'status' => $newStatus,
        ]);
    }
}
