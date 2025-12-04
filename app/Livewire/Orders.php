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
    }

    public function backToTables()
    {
        return redirect()->route('tables');
    }

    public function goToMenu()
    {
        return redirect()->route('menu', ['tableId' => $this->tableId]);
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

    public function updateOrderStatus($orderId, $newStatus)
    {
        $this->orderService->updateOrderStatus($orderId, $newStatus);
        session()->flash('success', 'Pedido atualizado com sucesso!');
    }

    public function render()
    {
        $ordersGrouped = $this->orderService->getActiveOrdersGrouped($this->currentCheck);
        
        $pendingStats = $this->orderService->calculateOrderStats($ordersGrouped['pending']);
        $inProductionStats = $this->orderService->calculateOrderStats($ordersGrouped['inProduction']);
        $inTransitStats = $this->orderService->calculateOrderStats($ordersGrouped['inTransit']);
        $completedStats = $this->orderService->calculateOrderStats($ordersGrouped['completed']);

        return view('livewire.orders', [
            'pendingOrders' => $ordersGrouped['pending'],
            'pendingTotal' => $pendingStats['total'],
            'pendingTime' => $pendingStats['time'],
            'inProductionOrders' => $ordersGrouped['inProduction'],
            'inProductionTotal' => $inProductionStats['total'],
            'inProductionTime' => $inProductionStats['time'],
            'inTransitOrders' => $ordersGrouped['inTransit'],
            'inTransitTotal' => $inTransitStats['total'],
            'inTransitTime' => $inTransitStats['time'],
            'completedOrders' => $ordersGrouped['completed'],
            'completedTotal' => $completedStats['total'],
            'completedTime' => $completedStats['time'],
        ]);
    }
}
