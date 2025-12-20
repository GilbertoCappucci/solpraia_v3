<?php

namespace App\Services;

use App\Enums\CheckStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\TableStatusEnum;
use App\Models\Category;
use App\Models\Check;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalSettingService;

class MenuService
{
    protected $checkService;
    protected $stockService;
    protected $globalSettingService;

    public function __construct(CheckService $checkService, StockService $stockService, GlobalSettingService $globalSettingService)
    {
        $this->checkService = $checkService;
        $this->stockService = $stockService;
        $this->globalSettingService = $globalSettingService;
    }

    public function getActiveMenu(int $userId): ?Menu
    {
        $menuId = $this->getActiveMenuId($userId);

        if (!$menuId) {
            return null;
        }

        return Menu::find($menuId);
    }

    /**
     * Obter o ID do menu ativo para o usuário
     * Menu pai aonde menu_id é null
     */
    public function getActiveMenuId(int $userId): int
    {
        $activeMenu = $this->globalSettingService->getActiveMenu($userId);
        return $activeMenu->id;
    }

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
        $activeMenuId = $this->getActiveMenuId($userId);

        if (!$activeMenuId) {
            return collect();
        }

        $query = Product::where('products.active', true)
            ->with(['stock'])
            ->select('products.*')
            // Join com menu_items para garantir que o produto pertence ao menu e pegar o preço
            ->join('menu_items', 'products.id', '=', 'menu_items.product_id')
            ->where('menu_items.menu_id', $activeMenuId)
            ->where('menu_items.active', true)
            ->whereHas('category', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });


        // Sobrescreve o preço com o preço do menu, se existir
        $query->addSelect(DB::raw('COALESCE(menu_items.price, products.price) as price'));

        // Se está mostrando apenas favoritos
        if ($showFavoritesOnly) {
            $query->where('products.favorite', true);
        }
        // Se tem categoria filha selecionada, filtra por ela
        elseif ($childCategoryId) {
            $query->where('products.category_id', $childCategoryId);
        }
        // Senão, se tem categoria pai selecionada, busca produtos de TODAS as categorias filhas
        elseif ($parentCategoryId) {
            $childCategoryIds = Category::where('category_id', $parentCategoryId)
                ->where('active', true)
                ->pluck('id');

            if ($childCategoryIds->isNotEmpty()) {
                $query->whereIn('products.category_id', $childCategoryIds);
            } else {
                return collect();
            }
        }

        if ($searchTerm) {
            $query->where('products.name', 'like', '%' . $searchTerm . '%');
        }

        return $query->orderBy('products.name')->get();
    }

    /**
     * Busca um produto específico com o preço do menu ativo
     */
    public function getProductWithMenuPrice(int $userId, int $productId): ?Product
    {
        $activeMenuId = $this->getActiveMenuId($userId);

        if (!$activeMenuId) {
            return Product::find($productId);
        }

        return Product::where('products.id', $productId)
            ->select('products.*', DB::raw('COALESCE(menu_items.price, products.price) as price'))
            ->leftJoin('menu_items', function ($join) use ($activeMenuId) {
                $join->on('products.id', '=', 'menu_items.product_id')
                    ->where('menu_items.menu_id', '=', $activeMenuId);
            })
            ->first();
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
    public function confirmOrder(int $userId, int $tableId, Table $table, ?Check $check, array $cart, float $total): void
    {
        DB::transaction(function () use ($userId, $tableId, $table, &$check, $cart, $total) {
            // Valida Stock para todos os itens novamente antes de efetivar
            foreach ($cart as $productId => $item) {
                if (!$this->stockService->hasStock($productId, $item['quantity'])) {
                    throw new \Exception("Stock insuficiente para o produto: {$item['product']->name}");
                }
            }

            // 1. Se não houver check, cria um novo
            if (!$check) {
                $check = Check::create([
                    'table_id' => $tableId,
                    'status' => CheckStatusEnum::OPEN->value,
                    'total' => 0, // Será calculado abaixo
                ]);
                // Atualiza status da mesa para ocupada
                if ($table->status === TableStatusEnum::FREE->value) {
                    $table->update(['status' => TableStatusEnum::OCCUPIED->value]);
                }
            }

            // 2. Cria os pedidos e debita estoque
            foreach ($cart as $productId => $item) {
                // Debita o estoque total deste item
                if (!$this->stockService->decrement($productId, $item['quantity'])) {
                    throw new \Exception("Erro ao debitar estoque do produto: {$item['product']->name}");
                }

                // Cria múltiplos pedidos individuais baseados na quantidade
                for ($i = 0; $i < $item['quantity']; $i++) {
                    $order = Order::create([
                        'user_id' => $userId,
                        'check_id' => $check->id,
                        'product_id' => $productId,
                        'price' => $item['product']->price,
                        'quantity' => 1, // Ordens são individuais
                        // status padrão é PENDING
                    ]);

                    // Cria histórico inicial
                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'from_status' => null,
                        'to_status' => OrderStatusEnum::PENDING->value,
                        'changed_at' => now(),
                    ]);
                }
            }

            // 3. Recalcula o total do check
            $this->checkService->recalculateCheckTotal($check);
        });
    }
}
