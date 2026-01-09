<?php

namespace App\Services\Menu;

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
use App\Services\CheckService;
use App\Services\GlobalSettingService;
use App\Services\Order\OrderService;
use App\Services\StockService;

class MenuService
{
    protected $checkService;
    protected $stockService;
    protected $orderService;
    protected $globalSettingService;

    public function __construct(CheckService $checkService, StockService $stockService, OrderService $orderService, GlobalSettingService $globalSettingService)
    {
        $this->checkService = $checkService;
        $this->stockService = $stockService;
        $this->orderService = $orderService;
        $this->globalSettingService = $globalSettingService;
    }

    public function getMenuName(int $menuId): string
    {
        return Menu::find($menuId)->name;
    }

    public function getActiveMenu(int $adminId): ?Menu
    {
        $menuId = $this->getActiveMenuId($adminId);

        if (!$menuId) {
            return null;
        }

        return Menu::find($menuId);
    }

    /**
     * Obter o ID do menu ativo para o usuário
     * Menu pai aonde menu_id é null
     */
    public function getActiveMenuId(int $adminId): int
    {
        $activeMenu = $this->globalSettingService->getActiveMenu($adminId);
        return $activeMenu->id;
    }

    /**
     * Carrega categorias pai (category_id é null)
     */
    public function getParentCategories(int $adminId): Collection
    {
        return Category::where('active', true)
            ->where('admin_id', $adminId)
            ->whereNull('category_id')
            ->orderBy('name')
            ->get();
    }

    /**
     * Carrega categorias filhas de uma categoria pai
     */
    public function getChildCategories(int $adminId, int $parentCategoryId): Collection
    {
        return Category::where('active', true)
            ->where('admin_id', $adminId)
            ->where('category_id', $parentCategoryId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Carrega produtos filtrados por categoria pai/filha ou favoritos
     */
    public function getFilteredProducts(
        int $adminId,
        ?int $parentCategoryId = null,
        ?int $childCategoryId = null,
        bool $showFavoritesOnly = false,
        ?string $searchTerm = null
    ): Collection {
        $activeMenuId = $this->getActiveMenuId($adminId);

        if (!$activeMenuId) {
            return collect();
        }

        $query = Product::where('products.active', true)
            ->with(['stock'])
            // Join com menu_items para garantir que o produto pertence ao menu e pegar o preço
            ->join('menu_items', 'products.id', '=', 'menu_items.product_id')
            ->where('menu_items.menu_id', $activeMenuId)
            ->where('menu_items.active', true)
            ->whereHas('category', function ($q) use ($adminId) {
                $q->where('admin_id', $adminId);
            })
            ->select(
                'products.*',
                'menu_items.price as price'
            );

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

        $products = $query->orderBy('products.name')->get();
        
        // Garante que o price seja um atributo da instância do produto
        // para ser preservado durante serialização Livewire
        return $products->map(function ($product) {
            $product->setAttribute('price', $product->price);
            return $product;
        });
    }

    /**
     * Busca um produto específico com o preço do menu ativo
     * Caso não tenha menu ativo, retorna o preço normal do produto
     * Caso tenha preço no menu, retorna o preço do menu
     * Caso não tenha preço no menu, retorna o preço normal do produto
     */
    public function getProductWithMenuPrice(int $adminId, int $productId): ?Product
    {
        $activeMenuId = $this->getActiveMenuId($adminId);

        if (!$activeMenuId) {
            return Product::find($productId);
        }

        $product = Product::where('products.id', $productId)
            ->select(
                'products.id',
                'products.name',
                'products.description',
                'products.category_id',
                'products.production_local',
                'products.active',
                'products.favorite',
                'menu_items.price as price'
            )
            ->join('menu_items', function ($join) use ($activeMenuId) {
                $join->on('products.id', '=', 'menu_items.product_id')
                    ->where('menu_items.menu_id', '=', $activeMenuId);
            })
            ->first();

        return $product;
    }

    /**
     * Calcula total do carrinho
     */
    public function calculateCartTotal(array $cart): float
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['product']['price'] * $item['quantity'];
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
    public function confirmOrder(int $adminId, int $tableId, ?Check $check, array $cart): void
    {

        DB::transaction(function () use ($adminId, $tableId, &$check, $cart) {
            // Valida Stock para todos os itens novamente antes de efetivar
            foreach ($cart as $productId => $item) {
                if (!$this->stockService->hasStock($productId, $item['quantity'])) {
                    throw new \Exception("Stock insuficiente para o produto: {$item['product']->name}");
                }
            }

            // 1. Usa o OrderService para encontrar ou criar check automaticamente
            if (!$check) {
                $check = $this->orderService->findOrCreateCheck($tableId);
            }

            // 2. Cria os pedidos e debita estoque
            foreach ($cart as $productId => $item) {
                
                // Debita o estoque total deste item
                if (!$this->stockService->decrement($productId, $item['quantity'])) {
                    throw new \Exception("Erro ao debitar estoque do produto: {$item['product']['name']}");
                }

                $productWithCorrectPrice = $this->getProductWithMenuPrice($adminId, $productId);

                if (!$productWithCorrectPrice) {
                    throw new \Exception("Produto não encontrado ao confirmar o pedido: {$item['product']['name']}");
                }

                $order = Order::create([
                    'admin_id' => $adminId,
                    'check_id' => $check->id,
                    'product_id' => $productId,
                    'price' => $productWithCorrectPrice->price,
                    'quantity' => $item['quantity'],
                    'total_price' => $productWithCorrectPrice->price * $item['quantity'],
                    'status' => OrderStatusEnum::PENDING->value,
                ]);

            }

        });
    }
}
