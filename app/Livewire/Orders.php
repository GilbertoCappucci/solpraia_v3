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
    public $userId; // ID do usuário dono dos dados (admin ou device)
    public $pollingInterval = 5000; // Intervalo de atualização em milissegundos (5 segundos)
    public $selectedTableId = null;
    public $selectedTable = null;
    public $currentCheck = null;
    public $categories = [];
    public $products = [];
    public $selectedCategoryId = null;
    public $cart = [];
    public $searchTerm = '';
    public $showNewTableModal = false;
    public $newTableName = '';
    public $newTableNumber = '';
    public $filterCheckStatus = null; // Filtro por status do Check
    public $filterOrderStatus = null; // Filtro por status de Orders
    public $showFilters = false; // Controla exibição do dropdown de filtros
    
    public function mount()
    {
        // Se for admin, usa o user_id do admin
        // Se for device, usa o user_id do criador (admin que criou o device)
        $this->userId = Auth::user()->isAdmin() 
            ? Auth::id() 
            : Auth::user()->user_id;
            
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

    public function openNewTableModal()
    {
        $this->showNewTableModal = true;
        $this->newTableName = '';
        $this->newTableNumber = '';
    }

    public function closeNewTableModal()
    {
        $this->showNewTableModal = false;
        $this->newTableName = '';
        $this->newTableNumber = '';
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function setCheckStatusFilter($status)
    {
        $this->filterCheckStatus = $this->filterCheckStatus === $status ? null : $status;
    }

    public function setOrderStatusFilter($status)
    {
        $this->filterOrderStatus = $this->filterOrderStatus === $status ? null : $status;
    }

    public function clearFilters()
    {
        $this->filterCheckStatus = null;
        $this->filterOrderStatus = null;
        $this->showFilters = false;
    }

    public function createNewTable()
    {
        $this->validate([
            'newTableName' => 'required|string|max:255',
            'newTableNumber' => 'required|integer|min:1',
        ], [
            'newTableName.required' => 'O nome do local é obrigatório.',
            'newTableNumber.required' => 'O número do local é obrigatório.',
            'newTableNumber.integer' => 'O número deve ser um valor numérico.',
        ]);

        Table::create([
            'user_id' => $this->userId,
            'name' => $this->newTableName,
            'number' => $this->newTableNumber,
            'active' => true,
        ]);

        session()->flash('success', 'Local criado com sucesso!');
        $this->closeNewTableModal();
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
            ->where('user_id', $this->userId)
            ->with(['checks' => function($query) {
                $query->with(['orders']);
            }])
            ->orderBy('number')
            ->get()
            ->filter(function($table) {
                // Aplica filtros
                $currentCheck = $table->checks->sortByDesc('created_at')->first();
                
                // Filtro por status do Check
                if ($this->filterCheckStatus) {
                    if (!$currentCheck || $currentCheck->status !== $this->filterCheckStatus) {
                        return false;
                    }
                }
                
                // Filtro por status de Orders
                if ($this->filterOrderStatus && $currentCheck) {
                    $hasOrderWithStatus = $currentCheck->orders->contains('status', $this->filterOrderStatus);
                    if (!$hasOrderWithStatus) {
                        return false;
                    }
                }
                
                return true;
            })
            ->map(function($table) {
                // Busca o check mais recente
                $currentCheck = $table->checks->sortByDesc('created_at')->first();
                
                // Define status do Check
                if ($currentCheck) {
                    $table->checkStatus = $currentCheck->status;
                    $table->checkStatusLabel = match($currentCheck->status) {
                        CheckStatusEnum::OPEN->value => 'Aberto',
                        CheckStatusEnum::CLOSING->value => 'Fechando',
                        CheckStatusEnum::CLOSED->value => 'Fechado',
                        CheckStatusEnum::PAID->value => 'Pago',
                        default => 'Livre'
                    };
                    $table->checkStatusColor = match($currentCheck->status) {
                        CheckStatusEnum::OPEN->value => 'green',
                        CheckStatusEnum::CLOSING->value => 'yellow',
                        CheckStatusEnum::CLOSED->value => 'red',
                        CheckStatusEnum::PAID->value => 'gray',
                        default => 'gray'
                    };
                    
                    // Conta orders por status e calcula tempo do mais antigo
                    $orders = $currentCheck->orders;
                    $now = now();
                    
                    // Pending
                    $pendingOrders = $orders->whereIn('status', [OrderStatusEnum::PENDING->value, OrderStatusEnum::CONFIRMED->value]);
                    $table->ordersPending = $pendingOrders->count();
                    $oldestPending = $pendingOrders->sortBy('created_at')->first();
                    $table->pendingMinutes = $oldestPending ? (int) ($now->diffInMinutes($oldestPending->created_at))*-1 : 0;
                    
                    // In Production
                    $productionOrders = $orders->where('status', OrderStatusEnum::IN_PRODUCTION->value);
                    $table->ordersInProduction = $productionOrders->count();
                    $oldestProduction = $productionOrders->sortBy('created_at')->first();
                    $table->productionMinutes = $oldestProduction ? (int) $now->diffInMinutes($oldestProduction->created_at) : 0;
                    
                    // Ready
                    $readyOrders = $orders->where('status', OrderStatusEnum::IN_TRANSIT->value);
                    $table->ordersReady = $readyOrders->count();
                    $oldestReady = $readyOrders->sortBy('created_at')->first();
                    $table->readyMinutes = $oldestReady ? (int) $now->diffInMinutes($oldestReady->created_at) : 0;
                    
                    $table->ordersCompleted = $orders->where('status', OrderStatusEnum::COMPLETED->value)->count();
                    $table->hasReadyOrders = $table->ordersReady > 0;
                    $table->checkTotal = $currentCheck->total ?? 0;
                } else {
                    $table->checkStatus = null;
                    $table->checkStatusLabel = 'Livre';
                    $table->checkStatusColor = 'gray';
                    $table->ordersPending = 0;
                    $table->ordersInProduction = 0;
                    $table->ordersReady = 0;
                    $table->ordersCompleted = 0;
                    $table->hasReadyOrders = false;
                    $table->checkTotal = 0;
                    $table->pendingMinutes = 0;
                    $table->productionMinutes = 0;
                    $table->readyMinutes = 0;
                }
                
                return $table;
            });

        return view('livewire.orders', [
            'tables' => $tables,
            'title' => $this->title,
        ]);
    }
}
