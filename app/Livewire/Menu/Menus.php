<?php

namespace App\Livewire\Menu;

use App\Models\GlobalSetting;
use App\Models\Table;
use App\Services\GlobalSettingService;
use App\Services\Menu\CartService;
use App\Services\Menu\MenuService;
use App\Services\Order\OrderService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;

class Menus extends Component
{
    public $title = 'Cardápio';
    public $userId;
    public $tableId;
    public $selectedTable = null;
    public $currentCheck = null;
    public $parentCategories = [];
    public $childCategories = [];
    public $selectedParentCategoryId = null;
    public $selectedChildCategoryId = null;
    public $showFavoritesOnly = false;
    public $cart = [];
    public $searchTerm = '';
    public $activeMenuId = null;

    protected $menuService;
    protected $orderService;
    protected $cartService;
    protected $globalSettingsService;

    public function boot(
        MenuService $menuService,
        OrderService $orderService,
        CartService $cartService,
        GlobalSettingService $globalSettingsService
    ) {
        $this->menuService = $menuService;
        $this->orderService = $orderService;
        $this->cartService = $cartService;
        $this->globalSettingsService = $globalSettingsService;
    }

    public function mount($tableId)
    {
        $this->userId = Auth::user()->user_id;
        $this->tableId = $tableId;
        $this->selectedTable = Table::findOrFail($tableId);
        $this->currentCheck = $this->orderService->findCheck($tableId);
        $this->activeMenuId = $this->menuService->getActiveMenuId($this->userId);
        $this->title = $this->menuService->getMenuName($this->activeMenuId);

        $this->loadParentCategories();
    }

    public function getListeners()
    {
        return [
            'global.setting.updated' => 'refreshSetting',
            'back-to-orders' => 'backToOrders',
            'search-updated' => 'handleSearchUpdated',
            'category-filter-changed' => 'handleCategoryFilterChanged',
            'add-to-cart' => 'handleAddToCart',
            'remove-from-cart' => 'handleRemoveFromCart',
            'clear-cart' => 'handleClearCart',
            'confirm-order' => 'handleConfirmOrder',
        ];
    }

    public function refreshSetting($data = null)
    {
        $this->activeMenuId = $this->menuService->getActiveMenuId($this->userId);
        $this->title = $this->menuService->getMenuName($this->activeMenuId);
        $this->loadParentCategories();
    }

    public function loadParentCategories()
    {
        $this->parentCategories = $this->menuService->getParentCategories($this->userId);
    }

    public function loadChildCategories()
    {
        if ($this->selectedParentCategoryId) {
            $this->childCategories = $this->menuService->getChildCategories($this->userId, $this->selectedParentCategoryId);
        } else {
            $this->childCategories = [];
        }
    }

    #[Computed]
    public function products()
    {
        $products = $this->menuService->getFilteredProducts(
            $this->userId,
            $this->selectedParentCategoryId,
            $this->selectedChildCategoryId,
            $this->showFavoritesOnly,
            $this->searchTerm
        );
        
        // Converte produtos para arrays simples para evitar serialização problemática do Livewire
        return $products->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'favorite' => $product->favorite,
                'active' => $product->active,
                'production_local' => $product->production_local,
                'stock' => $product->stock ? [
                    'quantity' => $product->stock->quantity
                ] : null
            ];
        });
    }

    public function backToOrders()
    {
        return redirect()->route('orders', ['tableId' => $this->tableId]);
    }

    public function handleSearchUpdated($searchTerm)
    {
        $this->searchTerm = $searchTerm;
    }

    public function handleCategoryFilterChanged($filters)
    {
        $this->selectedParentCategoryId = $filters['parentCategoryId'];
        $this->selectedChildCategoryId = $filters['childCategoryId'];
        $this->showFavoritesOnly = $filters['showFavoritesOnly'];
        
        $this->loadChildCategories();
    }

    public function handleAddToCart($productId)
    {
        $product = $this->menuService->getProductWithMenuPrice($this->userId, $productId);

        if (!$product) {
            session()->flash('error', 'Produto não disponível no menu atual.');
            return;
        }

        if ($this->cartService->addItem($this->cart, $product, $this->userId)) {
            // Success - cart updated
        } else {
            session()->flash('error', 'Estoque insuficiente.');
        }
    }

    public function handleRemoveFromCart($productId)
    {
        $this->cartService->removeItem($this->cart, $productId);
    }

    public function handleClearCart()
    {
        $this->cartService->clearCart($this->cart);
    }

    public function handleConfirmOrder()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Carrinho vazio.');
            return;
        }

        $this->menuService->confirmOrder(
            $this->userId,
            $this->tableId,
            $this->selectedTable,
            $this->currentCheck,
            $this->cart,
            $this->cartService->calculateTotal($this->cart)
        );

        session()->flash('success', 'Pedido confirmado com sucesso!');
        return redirect()->route('orders', ['tableId' => $this->tableId]);
    }

    public function render()
    {
        return view('livewire.menu.menus');
    }
}
