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
    public $categories = [];
    public $products = [];
    public $selectedCategoryId = null;
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
        
        $this->loadCategories();
        $this->loadProducts();
    }

    public function loadCategories()
    {
        $this->categories = $this->menuService->getActiveCategories($this->userId);
    }

    public function loadProducts()
    {
        $this->products = $this->menuService->getFilteredProducts(
            $this->userId,
            $this->selectedCategoryId,
            $this->searchTerm
        );
    }

    public function selectCategory($categoryId)
    {
        $this->selectedCategoryId = $categoryId === $this->selectedCategoryId ? null : $categoryId;
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
