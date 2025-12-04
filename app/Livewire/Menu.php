<?php

namespace App\Livewire;

use App\Services\MenuService;
use App\Services\OrderService;
use App\Models\Table;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Menu extends Component
{
    public $title = 'Cardápio';
    public $userId;
    public $tableId;
    public $selectedTable = null;
    public $currentCheck = null;
    public $parentCategories = [];
    public $childCategories = [];
    public $products = [];
    public $selectedParentCategoryId = null;
    public $selectedChildCategoryId = null;
    public $showFavoritesOnly = false;
    public $cart = [];
    public $searchTerm = '';
    
    protected $menuService;
    protected $orderService;
    
    public function boot(MenuService $menuService, OrderService $orderService)
    {
        $this->menuService = $menuService;
        $this->orderService = $orderService;
    }
    
    public function mount($tableId)
    {
        $this->userId = Auth::user()->isAdmin() 
            ? Auth::id() 
            : Auth::user()->user_id;
        
        $this->tableId = $tableId;
        $this->selectedTable = Table::findOrFail($tableId);
        $this->currentCheck = $this->orderService->findOrCreateCheck($tableId);
        
        $this->loadParentCategories();
        $this->loadProducts();
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

    public function loadProducts()
    {
        $this->products = $this->menuService->getFilteredProducts(
            $this->userId,
            $this->selectedParentCategoryId,
            $this->selectedChildCategoryId,
            $this->showFavoritesOnly,
            $this->searchTerm
        );
    }

    public function selectParentCategory($categoryId)
    {
        $this->selectedParentCategoryId = $categoryId === $this->selectedParentCategoryId ? null : $categoryId;
        $this->selectedChildCategoryId = null; // Reset categoria filha
        $this->showFavoritesOnly = false; // Reset favoritos
        $this->loadChildCategories();
        $this->loadProducts();
    }

    public function selectFavorites()
    {
        $this->showFavoritesOnly = !$this->showFavoritesOnly;
        $this->selectedParentCategoryId = null;
        $this->selectedChildCategoryId = null;
        $this->childCategories = [];
        $this->loadProducts();
    }

    public function selectChildCategory($categoryId)
    {
        $this->selectedChildCategoryId = $categoryId === $this->selectedChildCategoryId ? null : $categoryId;
        $this->loadProducts();
    }

    public function updatedSearchTerm()
    {
        $this->loadProducts();
    }

    public function addToCart($productId)
    {
        $product = \App\Models\Product::find($productId);
        
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
        } else {
            $this->cart[$productId] = [
                'product' => $product,
                'quantity' => 1,
            ];
        }
    }

    public function removeFromCart($productId)
    {
        if (isset($this->cart[$productId])) {
            if ($this->cart[$productId]['quantity'] > 1) {
                $this->cart[$productId]['quantity']--;
            } else {
                unset($this->cart[$productId]);
            }
        }
    }

    public function clearCart()
    {
        $this->cart = [];
    }

    public function backToOrders()
    {
        return redirect()->route('orders', ['tableId' => $this->tableId]);
    }

    public function getCartTotalProperty()
    {
        return $this->menuService->calculateCartTotal($this->cart);
    }

    public function getCartItemCountProperty()
    {
        return $this->menuService->calculateCartItemCount($this->cart);
    }

    public function confirmOrder()
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
            $this->cartTotal
        );

        session()->flash('success', 'Pedido confirmado com sucesso!');
        return redirect()->route('orders', ['tableId' => $this->tableId]);
    }

    public function render()
    {
        return view('livewire.menu');
    }
}
