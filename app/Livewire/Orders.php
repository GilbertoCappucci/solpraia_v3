<?php

namespace App\Livewire;

use App\Services\OrderService;
use App\Models\Table;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Orders extends Component
{
    public $title = 'Pedidos';
    public $userId;
    public $tableId;
    public $selectedTable = null;
    public $currentCheck = null;
    public $categories = [];
    public $products = [];
    public $selectedCategoryId = null;
    public $cart = [];
    public $searchTerm = '';
    public $showOrdersSection = true;
    public $showStatusModal = false;
    public $newTableStatus = null;
    public $newCheckStatus = null;
    
    protected $orderService;
    
    public function boot(OrderService $orderService)
    {
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
        
        $this->loadCartFromCheck();
        $this->loadCategories();
        $this->loadProducts();
    }

    public function loadCategories()
    {
        $this->categories = $this->orderService->getActiveCategories($this->userId);
    }

    public function loadProducts()
    {
        $this->products = $this->orderService->getFilteredProducts(
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

    public function loadCartFromCheck()
    {
        $this->cart = $this->orderService->loadCartFromCheck($this->currentCheck);
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
                'order_id' => null,
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

    public function backToTables()
    {
        return redirect()->route('tables');
    }

    public function openStatusModal()
    {
        $this->showStatusModal = true;
        $this->newTableStatus = $this->selectedTable->status;
        $this->newCheckStatus = $this->currentCheck?->status;
    }

    public function closeStatusModal()
    {
        $this->showStatusModal = false;
        $this->newTableStatus = null;
        $this->newCheckStatus = null;
    }

    public function updateStatuses()
    {
        $result = $this->orderService->updateStatuses(
            $this->selectedTable,
            $this->currentCheck,
            $this->newTableStatus,
            $this->newCheckStatus
        );
        
        if (!$result['success']) {
            session()->flash('error', implode(' ', $result['errors']));
            return;
        }
        
        session()->flash('success', 'Status atualizado com sucesso!');
        $this->closeStatusModal();
        
        // Recarrega dados
        $this->selectedTable = Table::findOrFail($this->tableId);
        $this->currentCheck = $this->orderService->findOrCreateCheck($this->tableId);
    }

    public function getCartTotalProperty()
    {
        return $this->orderService->calculateCartTotal($this->cart);
    }

    public function getCartItemCountProperty()
    {
        return $this->orderService->calculateCartItemCount($this->cart);
    }

    public function confirmOrder()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Carrinho vazio.');
            return;
        }

        $this->orderService->confirmOrder(
            $this->userId,
            $this->tableId,
            $this->selectedTable,
            $this->currentCheck,
            $this->cart,
            $this->cartTotal
        );

        session()->flash('success', 'Pedido confirmado com sucesso!');
        $this->loadCartFromCheck();
    }

    public function render()
    {
        $ordersGrouped = $this->orderService->getActiveOrdersGrouped($this->currentCheck);
        
        $pendingStats = $this->orderService->calculateOrderStats($ordersGrouped['pending']);
        $inProductionStats = $this->orderService->calculateOrderStats($ordersGrouped['inProduction']);
        $readyStats = $this->orderService->calculateOrderStats($ordersGrouped['ready']);

        return view('livewire.orders', [
            'pendingOrders' => $ordersGrouped['pending'],
            'pendingTotal' => $pendingStats['total'],
            'pendingTime' => $pendingStats['time'],
            'inProductionOrders' => $ordersGrouped['inProduction'],
            'inProductionTotal' => $inProductionStats['total'],
            'inProductionTime' => $inProductionStats['time'],
            'readyOrders' => $ordersGrouped['ready'],
            'readyTotal' => $readyStats['total'],
            'readyTime' => $readyStats['time'],
        ]);
    }
}
