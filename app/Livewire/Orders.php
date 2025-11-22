<?php

namespace App\Livewire;

use App\Enums\CheckStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Category;
use App\Models\Check;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Orders extends Component
{
    public $selectedTableId = null;
    public $selectedTable = null;
    public $currentCheck = null;
    public $categories = [];
    public $products = [];
    public $selectedCategoryId = null;
    public $cart = [];
    public $searchTerm = '';
    
    public function mount()
    {
        $this->loadCategories();
        $this->loadProducts();
    }

    public function loadCategories()
    {
        $this->categories = Category::where('active', true)
            ->where('user_id', Auth::user()->user_id)
            ->orderBy('name')
            ->get();
    }

    public function loadProducts()
    {
        $query = Product::where('active', true)
            ->where('user_id', Auth::user()->user_id);

        if ($this->selectedCategoryId) {
            $query->where('category_id', $this->selectedCategoryId);
        }

        if ($this->searchTerm) {
            $query->where('name', 'like', '%' . $this->searchTerm . '%');
        }

        $this->products = $query->orderBy('name')->get();
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

    public function selectTable($tableId)
    {
        $this->selectedTableId = $tableId;
        $this->selectedTable = Table::find($tableId);
        
        // Busca check aberto para esta mesa
        $this->currentCheck = Check::where('table_id', $tableId)
            ->where('status', CheckStatusEnum::OPEN->value)
            ->first();
            
        // Se não existe check aberto, cria um novo
        if (!$this->currentCheck) {
            $this->currentCheck = Check::create([
                'table_id' => $tableId,
                'total' => 0,
                'status' => CheckStatusEnum::OPEN->value,
                'opened_at' => now(),
            ]);
        }
        
        // Carrega pedidos existentes no carrinho
        $this->loadCartFromCheck();
    }

    public function loadCartFromCheck()
    {
        if ($this->currentCheck) {
            $orders = Order::where('check_id', $this->currentCheck->id)
                ->with('product')
                ->get();
                
            $this->cart = [];
            foreach ($orders as $order) {
                $this->cart[$order->product_id] = [
                    'product' => $order->product,
                    'quantity' => $order->quantity,
                    'order_id' => $order->id,
                ];
            }
        }
    }

    public function addToCart($productId)
    {
        if (!$this->currentCheck) {
            session()->flash('error', 'Selecione uma mesa primeiro.');
            return;
        }

        $product = Product::find($productId);
        
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

    public function getCartTotalProperty()
    {
        $total = 0;
        foreach ($this->cart as $item) {
            $total += $item['product']->price * $item['quantity'];
        }
        return $total;
    }

    public function getCartItemCountProperty()
    {
        $count = 0;
        foreach ($this->cart as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    public function confirmOrder()
    {
        if (!$this->currentCheck || empty($this->cart)) {
            session()->flash('error', 'Carrinho vazio.');
            return;
        }

        foreach ($this->cart as $productId => $item) {
            if ($item['order_id']) {
                // Atualiza pedido existente
                Order::where('id', $item['order_id'])->update([
                    'quantity' => $item['quantity'],
                ]);
            } else {
                // Cria novo pedido
                Order::create([
                    'employee_id' => Auth::id(),
                    'check_id' => $this->currentCheck->id,
                    'product_id' => $productId,
                    'quantity' => $item['quantity'],
                    'status' => OrderStatusEnum::PENDING->value,
                ]);
            }
        }

        // Atualiza total do check
        $this->currentCheck->update([
            'total' => $this->cartTotal,
        ]);

        session()->flash('success', 'Pedido confirmado com sucesso!');
        $this->loadCartFromCheck();
    }

    public function render()
    {
        $tables = Table::where('active', true)
            ->orderBy('number')
            ->get();

        return view('livewire.orders', [
            'tables' => $tables,
        ]);
    }
}
