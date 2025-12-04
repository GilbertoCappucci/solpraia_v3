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

class MenuService
{
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
        } else {
            // Calcula o total real de todos os pedidos do check
            $existingTotal = Order::where('check_id', $check->id)
                ->join('products', 'orders.product_id', '=', 'products.id')
                ->selectRaw('SUM(orders.quantity * products.price) as total')
                ->value('total') ?? 0;
            
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
            
            // Recalcula o total após as modificações
            $newTotal = Order::where('check_id', $check->id)
                ->join('products', 'orders.product_id', '=', 'products.id')
                ->selectRaw('SUM(orders.quantity * products.price) as total')
                ->value('total') ?? 0;
            
            // Atualiza o total do check com o valor recalculado
            $check->update([
                'total' => $newTotal,
            ]);
        }

        // Se é um check novo, processa os items do carrinho
        if ($check->wasRecentlyCreated) {
            foreach ($cart as $productId => $item) {
                Order::create([
                    'user_id' => $userId,
                    'check_id' => $check->id,
                    'product_id' => $productId,
                    'quantity' => $item['quantity'],
                    'status' => OrderStatusEnum::PENDING->value,
                ]);
            }
        }
        
        // Atualiza status da mesa para ocupada
        if ($table->status !== TableStatusEnum::OCCUPIED->value) {
            $table->update([
                'status' => TableStatusEnum::OCCUPIED->value,
            ]);
        }
    }
}
