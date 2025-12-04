<?php

namespace App\Services;

use App\Enums\CheckStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\TableStatusEnum;
use App\Models\Category;
use App\Models\Check;
use App\Models\Order;
use App\Models\Product;
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
     * Carrega categorias ativas do usuário
     */
    public function getActiveCategories(int $userId): Collection
    {
        return Category::where('active', true)
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Carrega produtos filtrados
     */
    public function getFilteredProducts(int $userId, ?int $categoryId = null, ?string $searchTerm = null): Collection
    {
        $query = Product::where('active', true)
            ->where('user_id', $userId);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($searchTerm) {
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Carrega carrinho a partir dos orders do check
     */
    public function loadCartFromCheck(?Check $check): array
    {
        if (!$check) {
            return [];
        }

        $orders = Order::where('check_id', $check->id)
            ->with('product')
            ->get();
            
        $cart = [];
        foreach ($orders as $order) {
            $cart[$order->product_id] = [
                'product' => $order->product,
                'quantity' => $order->quantity,
                'order_id' => $order->id,
            ];
        }

        return $cart;
    }

    /**
     * Calcula total do carrinho
     */
    public function calculateCartTotal(array $cart): float
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['product']->price * $item['quantity'];
        }
        return $total;
    }

    /**
     * Calcula quantidade total de itens no carrinho
     */
    public function calculateCartItemCount(array $cart): int
    {
        $count = 0;
        foreach ($cart as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    /**
     * Confirma pedido e atualiza check e mesa
     */
    public function confirmOrder(
        int $userId,
        int $tableId,
        Table $table,
        ?Check &$check,
        array $cart,
        float $cartTotal
    ): void {
        // Cria check se não existir
        if (!$check) {
            $check = Check::create([
                'table_id' => $tableId,
                'total' => $cartTotal,
                'status' => CheckStatusEnum::OPEN->value,
                'opened_at' => now(),
            ]);
        }

        // Processa items do carrinho
        foreach ($cart as $productId => $item) {
            if ($item['order_id']) {
                // Atualiza pedido existente
                Order::where('id', $item['order_id'])->update([
                    'quantity' => $item['quantity'],
                ]);
            } else {
                // Cria novo pedido
                Order::create([
                    'user_id' => $userId,
                    'check_id' => $check->id,
                    'product_id' => $productId,
                    'quantity' => $item['quantity'],
                    'status' => OrderStatusEnum::PENDING->value,
                ]);
            }
        }

        // Atualiza total do check
        $check->update([
            'total' => $cartTotal,
        ]);
        
        // Atualiza status da mesa para ocupada
        if ($table->status !== TableStatusEnum::OCCUPIED->value) {
            $table->update([
                'status' => TableStatusEnum::OCCUPIED->value,
            ]);
        }
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
                'ready' => collect(),
            ];
        }

        $activeOrders = Order::where('check_id', $check->id)
            ->with('product')
            ->whereIn('status', [
                OrderStatusEnum::PENDING->value,
                OrderStatusEnum::IN_PRODUCTION->value,
                OrderStatusEnum::IN_TRANSIT->value
            ])
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('status');
        
        return [
            'pending' => $activeOrders->get(OrderStatusEnum::PENDING->value, collect()),
            'inProduction' => $activeOrders->get(OrderStatusEnum::IN_PRODUCTION->value, collect()),
            'ready' => $activeOrders->get(OrderStatusEnum::IN_TRANSIT->value, collect()),
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
}
