<?php

namespace App\Livewire\Order;

use App\Services\CheckService;
use App\Services\Order\OrderService;
use App\Services\Table\TableService;
use Livewire\Component;

class OrderStatusManager extends Component
{
    public $show = false;
    public $selectedTable;
    public $currentCheck;
    
    // Table
    public $newTableStatus;
    public $hasActiveCheck = false;
    
    // Check
    public $newCheckStatus;
    public $checkStatusAllowed = [];
    
    protected $tableService;
    protected $checkService;
    protected $orderService;

    public function boot(TableService $tableService, CheckService $checkService, OrderService $orderService)
    {
        $this->tableService = $tableService;
        $this->checkService = $checkService;
        $this->orderService = $orderService;
    }

    public function getListeners()
    {
        return [
            'open-status-modal' => 'openModal',
        ];
    }

    public function openModal()
    {
        $this->show = true;
        
        // Inicializa Table Status
        if ($this->selectedTable) {
            $this->newTableStatus = $this->selectedTable->status;
            
            // Verifica se há check ativo
            $activeCheck = $this->orderService->findCheck($this->selectedTable->id);
            $this->hasActiveCheck = $activeCheck && in_array($activeCheck->status, ['Open', 'Closed']);
        }
        
        // Inicializa Check Status
        if ($this->currentCheck) {
            $this->newCheckStatus = $this->currentCheck->status;
            $this->checkStatusAllowed = $this->checkService->getAllowedCheckStatuses(
                $this->newCheckStatus,
                $this->currentCheck
            );
        }
    }

    public function closeModal()
    {
        $this->show = false;
        $this->newTableStatus = null;
        $this->newCheckStatus = null;
        $this->checkStatusAllowed = [];
    }

    // Table Status Methods
    public function setTableStatus($status)
    {
        if (!$this->hasActiveCheck && $this->selectedTable) {
            $this->newTableStatus = $status;
            $this->updateTableStatus();
        }
    }

    public function updateTableStatus()
    {
        if (!$this->selectedTable || !$this->newTableStatus) {
            return;
        }

        if ($this->hasActiveCheck) {
            session()->flash('error', 'Não é possível alterar o status da mesa. Finalize ou cancele o check primeiro.');
            return;
        }

        $this->tableService->updateTableStatus($this->selectedTable->id, $this->newTableStatus);
        
        session()->flash('success', 'Status da mesa atualizado com sucesso!');
        $this->dispatch('table-status-updated');
        $this->dispatch('refresh-parent');
    }

    // Check Status Methods
    public function setCheckStatus($status)
    {
        if ($this->currentCheck) {
            $this->newCheckStatus = $status;
            
            // Atualiza status permitidos
            $this->checkStatusAllowed = $this->checkService->getAllowedCheckStatuses(
                $this->newCheckStatus,
                $this->currentCheck
            );
            
            // Atualiza automaticamente
            $this->updateCheckStatus();
        }
    }

    public function updateCheckStatus()
    {
        if (!$this->currentCheck || !$this->newCheckStatus) {
            return;
        }

        $result = $this->checkService->validateAndUpdateCheckStatus(
            $this->currentCheck,
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

        session()->flash('success', 'Status do check atualizado com sucesso!');
        $this->dispatch('check-status-updated');
        $this->dispatch('refresh-parent');
    }

    public function render()
    {
        return view('livewire.order.order-status-manager');
    }
}
