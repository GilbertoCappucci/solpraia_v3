<?php

namespace App\Services;

use App\Enums\CheckStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\TableStatusEnum;
use App\Models\Category;
use App\Models\Check;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Support\Collection;

class MenuService
{
    /**
     * Carrega categorias pai (category_id é null)
     */
    public function getParentCategories(int $userId): Collection
    {
        return Category::where('active', true)
            ->where('user_id', $userId)
            ->whereNull('category_id')
            ->orderBy('name')
            ->get();
    }

    /**
     * Carrega categorias filhas de uma categoria pai
     */
    public function getChildCategories(int $userId, int $parentCategoryId): Collection
    {
        return Category::where('active', true)
            ->where('user_id', $userId)
            ->where('category_id', $parentCategoryId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Carrega produtos filtrados por categoria pai/filha ou favoritos
     */
    public function getFilteredProducts(
        int $userId, 
        ?int $parentCategoryId = null, 
        ?int $childCategoryId = null,
        bool $showFavoritesOnly = false,
        ?string $searchTerm = null
    ): Collection {
        $query = Product::where('active', true)
            ->where('user_id', $userId);

        // Se está mostrando apenas favoritos
        if ($showFavoritesOnly) {
            $query->where('favorite', true);
        }
        // Se tem categoria filha selecionada, filtra por ela
        elseif ($childCategoryId) {
            $query->where('category_id', $childCategoryId);
        }
        // Senão, se tem categoria pai selecionada, busca produtos de TODAS as categorias filhas
        elseif ($parentCategoryId) {
            $childCategoryIds = Category::where('category_id', $parentCategoryId)
                ->where('active', true)
                ->pluck('id');
            
            if ($childCategoryIds->isNotEmpty()) {
                $query->whereIn('category_id', $childCategoryIds);
            } else {
                // Se não tem filhas, retorna vazio (produtos não devem estar em categoria pai)
                return collect();
            }
        }

        if ($searchTerm) {
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }

        return $query->orderBy('name')->get();
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
                'total' => 0,
                'status' => CheckStatusEnum::OPEN->value,
                'opened_at' => now(),
            ]);
        }

        // Cria novos pedidos para todos os itens do carrinho
        foreach ($cart as $productId => $item) {
            $order = Order::create([
                'user_id' => $userId,
                'check_id' => $check->id,
                'product_id' => $productId,
                'quantity' => $item['quantity'],
            ]);
            
            // Registra o status inicial no histórico (PENDING)
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => OrderStatusEnum::PENDING->value,
                'changed_at' => now(),
            ]);
        }
        
        // Recalcula o total do check com base em TODOS os pedidos
        $newTotal = Order::where('check_id', $check->id)
            ->join('products', 'orders.product_id', '=', 'products.id')
            ->selectRaw('SUM(orders.quantity * products.price) as total')
            ->value('total') ?? 0;
        
        // Atualiza o total do check
        $check->update([
            'total' => $newTotal,
        ]);
        
        // Atualiza status da mesa para ocupada
        if ($table->status !== TableStatusEnum::OCCUPIED->value) {
            $table->update([
                'status' => TableStatusEnum::OCCUPIED->value,
            ]);
        }
    }
}
