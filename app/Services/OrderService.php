<?php

namespace App\Services;

use App\Enums\CheckStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\TableStatusEnum;
use App\Models\Check;
use App\Models\Order;
use App\Models\OrderStatusHistory;
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
        
        // Validação: Não pode mudar status da mesa se houver check em aberto ou em pagamento
        if ($newTableStatus && $newTableStatus !== $table->status) {
            if ($check) {
                $checkStatus = $check->status;
                
                // Bloqueia mudança de status se check está Open, Closing ou Closed
                if (in_array($checkStatus, [
                    CheckStatusEnum::OPEN->value,
                    CheckStatusEnum::CLOSING->value,
                    CheckStatusEnum::CLOSED->value
                ])) {
                    $statusLabel = match($checkStatus) {
                        CheckStatusEnum::OPEN->value => 'aberto',
                        CheckStatusEnum::CLOSING->value => 'em fechamento',
                        CheckStatusEnum::CLOSED->value => 'fechado (aguardando pagamento)',
                        default => 'em andamento'
                    };
                    $errors[] = "Não é possível alterar o status da mesa. Há um check {$statusLabel}.";
                }
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

        // Busca pedidos com histórico de status
        $activeOrders = Order::where('check_id', $check->id)
            ->with(['product', 'currentStatusHistory'])
            ->get()
            ->filter(function($order) {
                return in_array($order->status, [
                    OrderStatusEnum::PENDING->value,
                    OrderStatusEnum::IN_PRODUCTION->value,
                    OrderStatusEnum::IN_TRANSIT->value,
                    OrderStatusEnum::COMPLETED->value
                ]);
            })
            ->sortBy(function($order) {
                return $order->status_changed_at;
            })
            ->groupBy('status');
        
        return [
            'pending' => $activeOrders->get(OrderStatusEnum::PENDING->value, collect()),
            'inProduction' => $activeOrders->get(OrderStatusEnum::IN_PRODUCTION->value, collect()),
            'inTransit' => $activeOrders->get(OrderStatusEnum::IN_TRANSIT->value, collect()),
            'completed' => $activeOrders->get(OrderStatusEnum::COMPLETED->value, collect()),
        ];
    }

    /**
     * Calcula totais e tempos para cada grupo de pedidos usando histórico
     */
    public function calculateOrderStats(Collection $orders): array
    {
        $total = $orders->sum(fn($order) => $order->product->price * $order->quantity);
        
        if ($orders->isEmpty()) {
            return ['total' => $total, 'time' => 0];
        }
        
        // Pega o tempo mais antigo do histórico de status
        $oldestTime = null;
        
        foreach ($orders as $order) {
            // Usa o atributo virtual status_changed_at
            $changedAt = $order->status_changed_at;
            
            if ($changedAt && (!$oldestTime || $changedAt < $oldestTime)) {
                $oldestTime = $changedAt;
            }
        }
        
        $time = 0;
        if ($oldestTime) {
            $time = abs((int) now()->diffInMinutes($oldestTime));
        }
        
        return [
            'total' => $total,
            'time' => $time,
        ];
    }

    /**
     * Atualiza status individual de um pedido e registra no histórico
     */
    public function updateOrderStatus(int $orderId, string $newStatus): void
    {
        $order = Order::with('currentStatusHistory')->findOrFail($orderId);
        $oldStatus = $order->status; // Busca do histórico via atributo virtual
        
        // Registra no histórico (não precisa mais atualizar coluna status)
        OrderStatusHistory::create([
            'order_id' => $orderId,
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'changed_at' => now(),
        ]);
    }

    /**
     * Cancela um pedido (apenas se estiver no status PENDING)
     */
    public function cancelOrder(int $orderId): array
    {
        $order = Order::with(['currentStatusHistory', 'check', 'product'])->findOrFail($orderId);
        
        // Valida se o pedido está em PENDING
        if ($order->status !== OrderStatusEnum::PENDING->value) {
            return [
                'success' => false,
                'message' => 'Apenas pedidos no status "Aguardando" podem ser cancelados.'
            ];
        }
        
        // Registra o cancelamento no histórico
        OrderStatusHistory::create([
            'order_id' => $orderId,
            'from_status' => $order->status,
            'to_status' => OrderStatusEnum::CANCELED->value,
            'changed_at' => now(),
        ]);
        
        // Recalcula o total do check
        if ($order->check) {
            $check = $order->check;
            
            // Busca todos os pedidos do check que NÃO foram cancelados
            $activeOrders = $check->orders()
                ->with('currentStatusHistory')
                ->get()
                ->filter(function($order) {
                    return $order->status !== OrderStatusEnum::CANCELED->value;
                });
            
            // Recalcula o total
            $newTotal = $activeOrders->sum(function($order) {
                return $order->quantity * $order->unit_price;
            });
            
            $check->update(['total' => $newTotal]);
        }
        
        return [
            'success' => true,
            'message' => 'Pedido cancelado com sucesso!'
        ];
    }
}
