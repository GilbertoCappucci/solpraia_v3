<?php

namespace App\Livewire\Table;

use App\Services\OrderService;
use App\Services\TableService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TableStatusModal extends Component
{
    public $showModal = false;
    public $selectedTableId = null;
    public $newTableStatus = null;
    public $hasActiveCheck = false;

    protected $tableService;
    protected $orderService;

    public function boot(TableService $tableService, OrderService $orderService)
    {
        $this->tableService = $tableService;
        $this->orderService = $orderService;
    }

    public function getListeners()
    {
        return [
            'open-status-modal' => 'openModal',
        ];
    }

    public function openModal($tableId)
    {
        $table = $this->tableService->getTableById($tableId);
        $this->selectedTableId = $tableId;
        $this->newTableStatus = $table->status;

        // Verifica se há check ativo (não Paid nem Canceled)
        $activeCheck = $this->orderService->findCheck($tableId);
        $this->hasActiveCheck = $activeCheck && in_array($activeCheck->status, ['Open', 'Closed']);

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedTableId = null;
        $this->newTableStatus = null;
        $this->hasActiveCheck = false;
    }

    public function setStatus($status)
    {
        if (!$this->hasActiveCheck) {
            $this->newTableStatus = $status;
        }
    }

    public function updateTableStatus()
    {
        if (!$this->selectedTableId || !$this->newTableStatus) {
            return;
        }

        // Validação: não pode alterar status da mesa com check ativo
        if ($this->hasActiveCheck) {
            session()->flash('error', 'Não é possível alterar o status da mesa. Finalize ou cancele o check primeiro.');
            return;
        }

        $this->tableService->updateTableStatus($this->selectedTableId, $this->newTableStatus);

        session()->flash('success', 'Status da mesa atualizado com sucesso!');
        $this->closeModal();
        
        $this->dispatch('table-status-updated');
    }

    public function render()
    {
        return view('livewire.table.table-status-modal');
    }
}
