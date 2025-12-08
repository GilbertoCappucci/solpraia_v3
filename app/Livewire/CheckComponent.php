<?php

namespace App\Livewire;

use App\Models\Check;
use App\Services\CheckService;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CheckComponent extends Component
{
    public $checkId;
    public $check;
    public $table;
    public $title = 'Comanda';
    public $pollingInterval = 5000;
    
    public $showStatusModal = false;
    public $newCheckStatus = null;
    
    protected $checkService;
    protected $orderService;
    
    public function boot(CheckService $checkService, OrderService $orderService)
    {
        $this->checkService = $checkService;
        $this->orderService = $orderService;
    }
    
    public function mount($checkId)
    {
        $this->checkId = $checkId;
        $this->loadCheck();
    }
    
    public function loadCheck()
    {
        $this->check = Check::with(['table', 'orders.product', 'orders.currentStatusHistory'])
            ->findOrFail($this->checkId);
        
        $this->table = $this->check->table;
        
        // Recalcula o total do check
        $this->checkService->recalculateCheckTotal($this->check);
    }
    
    public function openStatusModal()
    {
        $this->newCheckStatus = $this->check->status;
        $this->showStatusModal = true;
    }
    
    public function closeStatusModal()
    {
        $this->showStatusModal = false;
        $this->newCheckStatus = null;
    }
    
    public function updateCheckStatus()
    {
        if (!$this->newCheckStatus) {
            return;
        }
        
        // Validações baseadas no status
        $pendingOrders = $this->check->orders->where('status', 'Pending');
        $inProductionOrders = $this->check->orders->where('status', 'InProduction');
        $inTransitOrders = $this->check->orders->where('status', 'InTransit');
        
        $hasIncompleteOrders = $pendingOrders->count() > 0 || 
                               $inProductionOrders->count() > 0 || 
                               $inTransitOrders->count() > 0;
        
        // Não pode fechar/pagar se houver pedidos não entregues
        if (in_array($this->newCheckStatus, ['Closed', 'Paid']) && $hasIncompleteOrders) {
            session()->flash('error', 'Não é possível alterar o status. Há pedidos que ainda não foram entregues.');
            return;
        }
        
        // Atualiza o status do check
        $this->orderService->updateCheckStatus($this->check, $this->newCheckStatus);
        
        session()->flash('success', 'Status da comanda atualizado com sucesso!');
        $this->closeStatusModal();
        $this->loadCheck();
    }
    
    public function goBack()
    {
        return redirect()->route('tables');
    }
    
    public function goToOrders()
    {
        return redirect()->route('orders', ['tableId' => $this->table->id]);
    }
    
    public function render()
    {
        // Agrupa pedidos por status
        $groupedOrders = [
            'pending' => $this->check->orders->where('status', 'Pending'),
            'inProduction' => $this->check->orders->where('status', 'InProduction'),
            'inTransit' => $this->check->orders->where('status', 'InTransit'),
            'delivered' => $this->check->orders->where('status', 'Delivered'),
            'canceled' => $this->check->orders->where('status', 'Canceled'),
        ];
        
        return view('livewire.check', [
            'groupedOrders' => $groupedOrders,
        ]);
    }
}
