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
    
    public function mount($tableId)
    {
        $this->userId = Auth::user()->isAdmin() 
            ? Auth::id() 
            : Auth::user()->user_id;
        
        $this->tableId = $tableId;
        $this->selectedTable = Table::findOrFail($tableId);
        
        // Busca check aberto para esta mesa
        $this->currentCheck = Check::where('table_id', $tableId)
            ->where('status', CheckStatusEnum::OPEN->value)
            ->first();
        
        $this->loadCartFromCheck();
        $this->loadCategories();
        $this->loadProducts();
    }

    public function loadCategories()
    {
        $this->categories = Category::where('active', true)
            ->where('user_id', $this->userId)
            ->orderBy('name')
            ->get();
    }

    public function loadProducts()
    {
        $query = Product::where('active', true)
            ->where('user_id', $this->userId);

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
        $errors = [];
        
        // Validação: Não pode mudar mesa para FREE se houver check com valor
        if ($this->newTableStatus === \App\Enums\TableStatusEnum::FREE->value) {
            if ($this->currentCheck && $this->currentCheck->total > 0) {
                $errors[] = 'Não é possível liberar a mesa com conta em aberto.';
            }
        }
        
        // Validação: Não pode fechar conta sem pedidos
        if ($this->newCheckStatus === CheckStatusEnum::CLOSING->value) {
            if (!$this->currentCheck || $this->currentCheck->total <= 0) {
                $errors[] = 'Não é possível fechar conta sem pedidos.';
            }
        }
        
        // Validação: Não pode marcar como CLOSED sem estar em CLOSING
        if ($this->newCheckStatus === CheckStatusEnum::CLOSED->value) {
            if (!$this->currentCheck || $this->currentCheck->status !== CheckStatusEnum::CLOSING->value) {
                $errors[] = 'A conta precisa estar em "Fechando" antes de ser marcada como "Fechada".';
            }
        }
        
        // Validação: Não pode marcar como PAID sem estar CLOSED
        if ($this->newCheckStatus === CheckStatusEnum::PAID->value) {
            if (!$this->currentCheck || $this->currentCheck->status !== CheckStatusEnum::CLOSED->value) {
                $errors[] = 'A conta precisa estar "Fechada" antes de ser marcada como "Paga".';
            }
        }
        
        if (!empty($errors)) {
            session()->flash('error', implode(' ', $errors));
            return;
        }
        
        // Atualiza status da mesa
        if ($this->newTableStatus && $this->newTableStatus !== $this->selectedTable->status) {
            $this->selectedTable->update(['status' => $this->newTableStatus]);
        }
        
        // Atualiza status do check
        if ($this->newCheckStatus && $this->currentCheck && $this->newCheckStatus !== $this->currentCheck->status) {
            $this->currentCheck->update(['status' => $this->newCheckStatus]);
            
            // Se marcou como PAID, libera a mesa
            if ($this->newCheckStatus === CheckStatusEnum::PAID->value) {
                $this->selectedTable->update(['status' => \App\Enums\TableStatusEnum::FREE->value]);
            }
        }
        
        session()->flash('success', 'Status atualizado com sucesso!');
        $this->closeStatusModal();
        
        // Recarrega dados
        $this->selectedTable = Table::findOrFail($this->tableId);
        $this->currentCheck = Check::where('table_id', $this->tableId)
            ->where('status', CheckStatusEnum::OPEN->value)
            ->first();
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
                    'user_id' => $this->userId,
                    'check_id' => $this->currentCheck->id,
                    'product_id' => $productId,
                    'quantity' => $item['quantity'],
                    'status' => OrderStatusEnum::PENDING->value,
                ]);
            }
        }

        // Cria check se não existir
        if (!$this->currentCheck) {
            $this->currentCheck = Check::create([
                'table_id' => $this->tableId,
                'total' => $this->cartTotal,
                'status' => CheckStatusEnum::OPEN->value,
                'opened_at' => now(),
            ]);
        } else {
            // Atualiza total do check
            $this->currentCheck->update([
                'total' => $this->cartTotal,
            ]);
        }
        
        // Atualiza status da mesa para ocupada apenas ao confirmar pedido
        if ($this->selectedTable->status !== \App\Enums\TableStatusEnum::OCCUPIED->value) {
            $this->selectedTable->update([
                'status' => \App\Enums\TableStatusEnum::OCCUPIED->value,
            ]);
        }

        session()->flash('success', 'Pedido confirmado com sucesso!');
        $this->loadCartFromCheck();
    }

    public function render()
    {
        // Carrega pedidos ativos agrupados por status
        $activeOrders = Order::where('check_id', $this->currentCheck->id)
            ->with('product')
            ->whereIn('status', [
                OrderStatusEnum::PENDING->value,
                OrderStatusEnum::IN_PRODUCTION->value,
                OrderStatusEnum::IN_TRANSIT->value
            ])
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('status');
        
        // Calcula tempo e totais por status
        $now = now();
        
        $pendingOrders = $activeOrders->get(OrderStatusEnum::PENDING->value, collect());
        $pendingTotal = $pendingOrders->sum(fn($order) => $order->product->price * $order->quantity);
        $pendingTime = $pendingOrders->first() ? (int) $now->diffInMinutes($pendingOrders->first()->created_at) : 0;
        
        $inProductionOrders = $activeOrders->get(OrderStatusEnum::IN_PRODUCTION->value, collect());
        $inProductionTotal = $inProductionOrders->sum(fn($order) => $order->product->price * $order->quantity);
        $inProductionTime = $inProductionOrders->first() ? (int) $now->diffInMinutes($inProductionOrders->first()->created_at) : 0;
        
        $readyOrders = $activeOrders->get(OrderStatusEnum::IN_TRANSIT->value, collect());
        $readyTotal = $readyOrders->sum(fn($order) => $order->product->price * $order->quantity);
        $readyTime = $readyOrders->first() ? (int) $now->diffInMinutes($readyOrders->first()->created_at) : 0;

        return view('livewire.orders', [
            'pendingOrders' => $pendingOrders,
            'pendingTotal' => $pendingTotal,
            'pendingTime' => $pendingTime,
            'inProductionOrders' => $inProductionOrders,
            'inProductionTotal' => $inProductionTotal,
            'inProductionTime' => $inProductionTime,
            'readyOrders' => $readyOrders,
            'readyTotal' => $readyTotal,
            'readyTime' => $readyTime,
        ]);
    }
}
