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
    public $showCancelModal = false;
    public $orderToCancel = null;
    public $orderToCancelData = null;
    public $hasActiveCheck = false;
    
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
        // Verifica se a mesa está fechada
        if ($this->selectedTable->status === \App\Enums\TableStatusEnum::CLOSE->value) {
            session()->flash('error', 'Não é possível adicionar pedidos em uma mesa fechada!');
            return;
        }
        
        // Verifica se o check está aberto (permite se check for NULL - primeiro pedido)
        if ($this->currentCheck && $this->currentCheck->status !== 'Open') {
            session()->flash('error', 'Para adicionar novos pedidos, o check precisa estar no status "Aberto". Altere o status do check primeiro.');
            return;
        }
        
        return redirect()->route('menu', ['tableId' => $this->tableId]);
    }

    public function openStatusModal()
    {
        // Recarrega dados do banco antes de abrir modal
        $this->refreshData();
        
        // Verifica se há check ativo (Open ou Closed)
        $this->hasActiveCheck = $this->currentCheck && in_array($this->currentCheck->status, ['Open', 'Closed']);
        
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
    
    public function refreshData()
    {
        // Recarrega dados atualizados do banco
        $this->selectedTable->refresh();
        $this->currentCheck = $this->orderService->findOrCreateCheck($this->tableId);
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
        
        // Se alterou para Fechado, redireciona para a tela do check
        if ($this->newCheckStatus === 'Closed') {
            session()->flash('success', 'Check fechado! Finalize o pagamento.');
            return redirect()->route('check', ['checkId' => $this->currentCheck->id]);
        }
        
        session()->flash('success', 'Status atualizado com sucesso!');
        $this->dispatch('table-updated'); // Dispara evento para outros componentes
        $this->closeStatusModal();
        $this->refreshData();
    }

    public function updateOrderStatus($orderId, $newStatus)
    {
        // Verifica se o check está aberto (se houver check, precisa estar Open)
        if ($this->currentCheck && $this->currentCheck->status !== 'Open') {
            session()->flash('error', 'Para alterar o status de pedidos, o check precisa estar no status "Aberto". Altere o status do check primeiro.');
            return;
        }
        
        $this->orderService->updateOrderStatus($orderId, $newStatus);
        session()->flash('success', 'Pedido atualizado com sucesso!');
        $this->refreshData();
    }

    public function updateAllOrderStatus($orderIds, $newStatus)
    {
        // Verifica se o check está aberto (se houver check, precisa estar Open)
        if ($this->currentCheck && $this->currentCheck->status !== 'Open') {
            session()->flash('error', 'Para alterar o status de pedidos, o check precisa estar no status "Aberto". Altere o status do check primeiro.');
            return;
        }
        
        // Atualiza todos os pedidos do array
        foreach ($orderIds as $orderId) {
            $this->orderService->updateOrderStatus($orderId, $newStatus);
        }
        
        $count = count($orderIds);
        session()->flash('success', "{$count} " . ($count === 1 ? 'pedido atualizado' : 'pedidos atualizados') . " com sucesso!");
        $this->refreshData();
    }

    public function openCancelModal($orderId)
    {
        // Verifica se o check está aberto (se houver check, precisa estar Open)
        if ($this->currentCheck && $this->currentCheck->status !== 'Open') {
            session()->flash('error', 'Para cancelar pedidos, o check precisa estar no status "Aberto". Altere o status do check primeiro.');
            return;
        }
        
        $this->orderToCancel = $orderId;
        
        // Busca dados do pedido para exibir no modal
        $order = \App\Models\Order::with('product')->find($orderId);
        if ($order) {
            $this->orderToCancelData = [
                'product_name' => $order->product->name,
                'quantity' => $order->quantity,
                'price' => $order->product->price,
            ];
        }
        
        $this->showCancelModal = true;
    }
    
    public function closeCancelModal()
    {
        $this->showCancelModal = false;
        $this->orderToCancel = null;
        $this->orderToCancelData = null;
    }
    
    public function confirmCancelOrder()
    {
        if (!$this->orderToCancel) {
            return;
        }
        
        $result = $this->orderService->cancelOrder($this->orderToCancel);
        
        if (!$result['success']) {
            session()->flash('error', $result['message']);
            $this->closeCancelModal();
            return;
        }
        
        session()->flash('success', $result['message']);
        $this->closeCancelModal();
        $this->refreshData();
    }

    public function addOneMore($orderId)
    {
        // Verifica se o check está aberto (se houver check, precisa estar Open)
        if ($this->currentCheck && $this->currentCheck->status !== 'Open') {
            session()->flash('error', 'Para adicionar mais pedidos, o check precisa estar no status "Aberto". Altere o status do check primeiro.');
            return;
        }
        
        $result = $this->orderService->duplicatePendingOrder($orderId);
        
        if (!$result['success']) {
            session()->flash('error', $result['message']);
            return;
        }
        
        session()->flash('success', 'Quantidade aumentada!');
        $this->refreshData();
    }

    public function render()
    {
        $ordersGrouped = $this->orderService->getActiveOrdersGrouped($this->currentCheck);
        
        $pendingStats = $this->orderService->calculateOrderStats($ordersGrouped['pending']);
        $inProductionStats = $this->orderService->calculateOrderStats($ordersGrouped['inProduction']);
        $inTransitStats = $this->orderService->calculateOrderStats($ordersGrouped['inTransit']);
        $completedStats = $this->orderService->calculateOrderStats($ordersGrouped['completed']);

        // Permite adicionar pedidos se não há check ainda (NULL) ou se check está Open
        $isCheckOpen = !$this->currentCheck || $this->currentCheck->status === 'Open';

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
            'isCheckOpen' => $isCheckOpen,
        ]);
    }
}
