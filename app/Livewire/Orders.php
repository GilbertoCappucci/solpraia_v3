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
    public $orderIdsToCancel = [];
    public $orderToCancelData = null;
    public $hasActiveCheck = false;
    public $delayAlarmEnabled = true;
    public $pollingInterval = 5000;
    
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
        $this->delayAlarmEnabled = session('orders.delayAlarmEnabled', true);
    }

    public function toggleDelayAlarm()
    {
        $this->delayAlarmEnabled = !$this->delayAlarmEnabled;
        session(['orders.delayAlarmEnabled' => $this->delayAlarmEnabled]);
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
        
        // Garante que o objeto esteja fresco
        if ($this->currentCheck) {
            $this->currentCheck->refresh();
        }
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
        
        $result = $this->orderService->updateOrderStatus($orderId, $newStatus);
        
        if (!$result['success']) {
            session()->flash('error', $result['message']);
            return;
        }

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
        
        $successCount = 0;
        $errors = [];

        // Atualiza todos os pedidos do array
        foreach ($orderIds as $orderId) {
            $result = $this->orderService->updateOrderStatus($orderId, $newStatus);
            if ($result['success']) {
                $successCount++;
            } else {
                $errors[] = $result['message'];
            }
        }
        
        if (!empty($errors)) {
            // Mostra apenas o primeiro erro para não poluir a tela, ou um resumo
            session()->flash('error', "Erro em alguns itens: " . implode('. ', array_unique($errors)));
        }
        
        if ($successCount > 0) {
            session()->flash('success', "{$successCount} " . ($successCount === 1 ? 'pedido atualizado' : 'pedidos atualizados') . " com sucesso!");
        }
        
        $this->refreshData();
    }

    public function openCancelModal($orderId, $allGroupIds = [])
    {
        // Verifica se o check está aberto (se houver check, precisa estar Open)
        if ($this->currentCheck && $this->currentCheck->status !== 'Open') {
            session()->flash('error', 'Para cancelar pedidos, o check precisa estar no status "Aberto". Altere o status do check primeiro.');
            return;
        }
        
        $this->orderToCancel = $orderId;
        $this->orderIdsToCancel = $allGroupIds;
        
        // Busca dados do pedido para exibir no modal
        $order = \App\Models\Order::with('product')->find($orderId);
        if ($order) {
            $this->orderToCancelData = [
                'product_name' => $order->product->name,
                'quantity' => count($allGroupIds) > 0 ? count($allGroupIds) : $order->quantity,
                'single_quantity' => $order->quantity, // Quantidade do item individual (normalmente 1)
                'price' => $order->product->price,
            ];
        }
        
        $this->showCancelModal = true;
    }
    
    public function closeCancelModal()
    {
        $this->showCancelModal = false;
        $this->orderToCancel = null;
        $this->orderIdsToCancel = [];
        $this->orderToCancelData = null;
    }
    
    public function confirmCancelAll()
    {
        if (empty($this->orderIdsToCancel)) {
            return;
        }
        
        $result = $this->orderService->cancelOrders($this->orderIdsToCancel);
        
        if (!$result['success']) {
            session()->flash('error', $result['message']);
            $this->closeCancelModal();
            return;
        }
        
        session()->flash('success', $result['message']);
        $this->closeCancelModal();
        $this->refreshData();
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
