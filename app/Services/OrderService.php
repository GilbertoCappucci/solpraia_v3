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
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected $checkService;
    
    public function __construct(CheckService $checkService)
    {
        $this->checkService = $checkService;
    }
    
    /**
     * Recalcula o total de todos os checks ativos
     */
    public function recalculateAllActiveChecks(): void
    {
        // Busca todos os checks ativos (não Paid nem Canceled)
        $activeChecks = Check::whereNotIn('status', [
                CheckStatusEnum::PAID->value,
                CheckStatusEnum::CANCELED->value
            ])
            ->get();
        
        foreach ($activeChecks as $check) {
            $this->checkService->recalculateCheckTotal($check);
        }
    }
    
    /**
     * Busca ou cria check aberto para a mesa
     */
    public function findOrCreateCheck(int $tableId): ?Check
    {
        // Busca check ativo (não Paid nem Canceled)
        return Check::where('table_id', $tableId)
            ->whereNotIn('status', [
                CheckStatusEnum::PAID->value,
                CheckStatusEnum::CANCELED->value
            ])
            ->orderBy('created_at', 'desc')
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
            
            // Validação específica para status CLOSE: só pode fechar mesa sem check ativo
            if ($newTableStatus === TableStatusEnum::CLOSE->value && $check) {
                $errors[] = "Não é possível fechar a mesa. Finalize ou cancele o check antes de fechar a mesa fisicamente.";
            }
        }
        
        // Validação do check usando método centralizado do CheckService
        $checkWasUpdated = false;
        if ($newCheckStatus && $check && $newCheckStatus !== $check->status) {
            $checkValidation = $this->checkService->validateAndUpdateCheckStatus($check, $newCheckStatus);
            
            if (!$checkValidation['success']) {
                $errors = array_merge($errors, $checkValidation['errors']);
            } else {
                $checkWasUpdated = true;
            }
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Atualiza status da mesa
        if ($newTableStatus && $newTableStatus !== $table->status) {
            $table->update(['status' => $newTableStatus]);
        }
        
        // Se o check foi atualizado com sucesso e foi marcado como PAID, coloca mesa em RELEASING
        // Se foi CANCELED, libera direto para FREE
        if ($checkWasUpdated) {
            if ($newCheckStatus === CheckStatusEnum::PAID->value) {
                $table->update(['status' => TableStatusEnum::RELEASING->value]);
            } elseif ($newCheckStatus === CheckStatusEnum::CANCELED->value) {
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
            'pending' => $this->groupOrdersByProduct($activeOrders->get(OrderStatusEnum::PENDING->value, collect())),
            'inProduction' => $this->groupOrdersByProduct($activeOrders->get(OrderStatusEnum::IN_PRODUCTION->value, collect())),
            'inTransit' => $this->groupOrdersByProduct($activeOrders->get(OrderStatusEnum::IN_TRANSIT->value, collect())),
            'completed' => $this->groupOrdersByProduct($activeOrders->get(OrderStatusEnum::COMPLETED->value, collect())),
        ];
    }
    
    /**
     * Agrupa pedidos individuais do mesmo produto
     */
    protected function groupOrdersByProduct(Collection $orders): Collection
    {
        return $orders->groupBy('product_id')->map(function($groupedOrders) {
            // Se houver apenas 1 pedido, retorna como está
            if ($groupedOrders->count() === 1) {
                return $groupedOrders->first();
            }
            
            // Se houver múltiplos, cria um objeto "virtual" agrupado
            $firstOrder = $groupedOrders->first();
            $totalQuantity = $groupedOrders->sum('quantity'); // Soma sempre será igual ao count já que cada um tem qty 1
            
            // Cria um objeto com as informações agrupadas
            $grouped = clone $firstOrder;
            $grouped->quantity = $totalQuantity;
            $grouped->individual_orders = $groupedOrders; // Mantém referência aos pedidos individuais
            $grouped->is_grouped = true;
            
            return $grouped;
        })->values();
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
        $order = Order::with(['currentStatusHistory', 'check'])->findOrFail($orderId);
        $oldStatus = $order->status; // Busca do histórico via atributo virtual
        
        // Registra no histórico (não precisa mais atualizar coluna status)
        OrderStatusHistory::create([
            'order_id' => $orderId,
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'changed_at' => now(),
        ]);
        
        // Recalcula o total do check após mudança de status
        // Isso garante que o total reflita corretamente os pedidos que não são PENDING nem CANCELED
        if ($order->check) {
            $this->checkService->recalculateCheckTotal($order->check);
        }
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
            $this->checkService->recalculateCheckTotal($order->check);
        }
        
        return [
            'success' => true,
            'message' => 'Pedido cancelado com sucesso!'
        ];
    }

    /**
     * Duplica um pedido PENDING (adiciona mais uma unidade)
     */
    public function duplicatePendingOrder(int $orderId): array
    {
        return DB::transaction(function() use ($orderId) {
            // Busca o pedido com lock
            $order = Order::with(['currentStatusHistory', 'check', 'product'])
                ->lockForUpdate()
                ->findOrFail($orderId);
            
            // Recarrega o status mais recente
            $order->load('currentStatusHistory');
            
            // Valida se o pedido AINDA está em PENDING
            if ($order->status !== OrderStatusEnum::PENDING->value) {
                return [
                    'success' => false,
                    'message' => 'Apenas pedidos no status "Aguardando" podem ter a quantidade aumentada.'
                ];
            }
            
            // Cria um novo pedido idêntico
            $newOrder = Order::create([
                'user_id' => $order->user_id,
                'check_id' => $order->check_id,
                'product_id' => $order->product_id,
                'quantity' => 1,  // Sempre 1 unidade
            ]);
            
            // Registra o status inicial no histórico (PENDING)
            OrderStatusHistory::create([
                'order_id' => $newOrder->id,
                'from_status' => null,
                'to_status' => OrderStatusEnum::PENDING->value,
                'changed_at' => now(),
            ]);
            
            // Recalcula o total do check
            if ($order->check) {
                $this->checkService->recalculateCheckTotal($order->check);
            }
            
            return [
                'success' => true
            ];
        });
    }

    /**
     * Atualiza apenas o status do check
     */
}
