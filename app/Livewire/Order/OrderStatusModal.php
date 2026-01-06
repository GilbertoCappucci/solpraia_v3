<?php

namespace App\Livewire\Order;

use App\Services\CheckService;
use App\Services\Order\OrderService;
use Livewire\Component;

class OrderStatusModal extends Component
{
    public $show = false;
    public $selectedTable;
    public $currentCheck;
    public $orders;
    public $newTableStatus;
    public $newCheckStatus;
    public $hasActiveCheck = false;
    public $checkStatusAllowed = [];
    
    protected $orderService;
    protected $checkService;

    public function boot(OrderService $orderService, CheckService $checkService)
    {
        $this->orderService = $orderService;
        $this->checkService = $checkService;
    }

    public function getListeners()
    {
        return [
            'open-status-modal' => 'openModal',
            'set' => 'handleSet',
            'check-status-selected' => 'handleCheckStatusSelected',
        ];
    }

    public function handleSet($property, $value)
    {
        if ($property === 'newTableStatus') {
            $this->newTableStatus = $value;
        }
    }

    public function handleCheckStatusSelected($status)
    {
        $this->newCheckStatus = $status;
        // Atualiza os status permitidos
        if ($this->currentCheck) {
            $this->checkStatusAllowed = $this->checkService->getAllowedCheckStatuses($status, $this->currentCheck);
        }
    }

    public function openModal()
    {
        $this->show = true;
        
        // Inicializa o status do check se não estiver definido
        if ($this->currentCheck && !$this->newCheckStatus) {
            $this->newCheckStatus = $this->currentCheck->status;
        }
        
        // Inicializa o status da mesa se não estiver definido
        if ($this->selectedTable && !$this->newTableStatus) {
            $this->newTableStatus = $this->selectedTable->status;
        }
        
        // Inicializa os status permitidos baseado no status atual do check
        if ($this->currentCheck && $this->newCheckStatus) {
            $this->checkStatusAllowed = $this->checkService->getAllowedCheckStatuses(
                $this->newCheckStatus,
                $this->currentCheck
            );
        }
        
        // Verifica se há check ativo
        $this->hasActiveCheck = $this->currentCheck ? true : false;
        
        $this->dispatch('refresh-modal-data');
    }

    public function updatedNewCheckStatus($value)
    {
        // Atualiza os status permitidos para o check com base no novo status selecionado
        $this->checkStatusAllowed = $this->checkService->getAllowedCheckStatuses($value, $this->currentCheck);
    }

    public function closeModal()
    {
        $this->show = false;
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

        // Se alterou para Fechado, redireciona para a tela do check
        if ($this->newCheckStatus === 'Closed') {
            session()->flash('success', 'Check fechado! Finalize o pagamento.');
            return redirect()->route('check', ['checkId' => $this->currentCheck->id]);
        }

        session()->flash('success', 'Status atualizado com sucesso!');
        $this->closeModal();
        $this->dispatch('refresh-parent');
    }

    public function render()
    {
        return view('livewire.order.order-status-modal');
    }
}
