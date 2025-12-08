<?php

namespace App\Livewire;

use App\Enums\TableStatusEnum;
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
        
        // Usa método centralizado do CheckService para validar e atualizar
        $result = $this->checkService->validateAndUpdateCheckStatus($this->check, $this->newCheckStatus);
        
        if (!$result['success']) {
            session()->flash('error', implode(' ', $result['errors']));
            return;
        }
        
        // Se o check foi marcado como PAID, coloca mesa em RELEASING
        // Se foi CANCELED, libera direto para FREE
        if ($this->newCheckStatus === 'Paid') {
            $this->table->update(['status' => TableStatusEnum::RELEASING->value]);
        } elseif ($this->newCheckStatus === 'Canceled') {
            $this->table->update(['status' => TableStatusEnum::FREE->value]);
        }
        
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
            'delivered' => $this->check->orders->where('status', 'Completed'),
            'canceled' => $this->check->orders->where('status', 'Canceled'),
        ];
        
        // Regra simplificada: só pode alterar se TODOS os pedidos (exceto cancelados) estão entregues
        $activeOrders = $this->check->orders->whereNotIn('status', ['Canceled']);
        $allDelivered = $activeOrders->every(fn($order) => $order->status === 'Completed');
        $hasIncompleteOrders = !$allDelivered && $activeOrders->count() > 0;
        
        return view('livewire.check', [
            'groupedOrders' => $groupedOrders,
            'hasIncompleteOrders' => $hasIncompleteOrders,
        ]);
    }
}
