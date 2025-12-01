<?php

namespace App\Livewire;

use App\Enums\CheckStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Check;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Tables extends Component
{
    public $title = 'Locais';
    public $userId;
    public $pollingInterval = 5000;
    public $filterCheckStatus = null;
    public $filterOrderStatus = null;
    public $showFilters = false;
    public $showNewTableModal = false;
    public $newTableName = '';
    public $newTableNumber = '';
    
    public function mount()
    {
        $this->userId = Auth::user()->isAdmin() 
            ? Auth::id() 
            : Auth::user()->user_id;
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

    public function selectTable($tableId)
    {
        return redirect()->route('orders', ['tableId' => $tableId]);
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
                $currentCheck = $table->checks->sortByDesc('created_at')->first();
                
                if ($this->filterCheckStatus) {
                    if (!$currentCheck || $currentCheck->status !== $this->filterCheckStatus) {
                        return false;
                    }
                }
                
                if ($this->filterOrderStatus && $currentCheck) {
                    $hasOrderWithStatus = $currentCheck->orders->contains('status', $this->filterOrderStatus);
                    if (!$hasOrderWithStatus) {
                        return false;
                    }
                }
                
                return true;
            })
            ->map(function($table) {
                $currentCheck = $table->checks->sortByDesc('created_at')->first();
                
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
                    
                    $orders = $currentCheck->orders;
                    $now = now();
                    
                    $pendingOrders = $orders->where('status', OrderStatusEnum::PENDING->value);
                    $table->ordersPending = $pendingOrders->count();
                    $oldestPending = $pendingOrders->sortBy('created_at')->first();
                    $table->pendingMinutes = $oldestPending ? abs((int) $now->diffInMinutes($oldestPending->created_at)) : 0;
                    
                    $productionOrders = $orders->where('status', OrderStatusEnum::IN_PRODUCTION->value);
                    $table->ordersInProduction = $productionOrders->count();
                    $oldestProduction = $productionOrders->sortBy('created_at')->first();
                    $table->productionMinutes = $oldestProduction ? abs((int) $now->diffInMinutes($oldestProduction->created_at)) : 0;
                    
                    $transitOrders = $orders->where('status', OrderStatusEnum::IN_TRANSIT->value);
                    $table->ordersInTransit = $transitOrders->count();
                    $oldestTransit = $transitOrders->sortBy('created_at')->first();
                    $table->transitMinutes = $oldestTransit ? abs((int) $now->diffInMinutes($oldestTransit->created_at)) : 0;
                    
                    $completedOrders = $orders->where('status', OrderStatusEnum::COMPLETED->value);
                    $table->ordersCompleted = $completedOrders->count();
                    $oldestCompleted = $completedOrders->sortBy('created_at')->first();
                    $table->completedMinutes = $oldestCompleted ? abs((int) $now->diffInMinutes($oldestCompleted->created_at)) : 0;
                    
                    $table->checkTotal = $currentCheck->total ?? 0;
                } else {
                    $table->checkStatus = null;
                    $table->checkStatusLabel = 'Livre';
                    $table->checkStatusColor = 'gray';
                    $table->ordersPending = 0;
                    $table->ordersInProduction = 0;
                    $table->ordersInTransit = 0;
                    $table->ordersCompleted = 0;
                    $table->checkTotal = 0;
                    $table->pendingMinutes = 0;
                    $table->productionMinutes = 0;
                    $table->transitMinutes = 0;
                    $table->completedMinutes = 0;
                }
                
                return $table;
            });

        return view('livewire.tables', [
            'tables' => $tables,
        ]);
    }
}
