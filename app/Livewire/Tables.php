<?php

namespace App\Livewire;

use App\Services\OrderService;
use App\Services\TableService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Tables extends Component
{
    public $title = 'Locais';
    public $userId;
    public $pollingInterval = 5000;
    public $filterTableStatus = null;
    public $filterCheckStatus = null;
    public $filterOrderStatuses = [];
    public $showFilters = false;
    public $showNewTableModal = false;
    public $newTableName = '';
    public $newTableNumber = '';
    public $showTableStatusModal = false;
    public $selectedTableId = null;
    public $newTableStatus = null;
    public $hasActiveCheck = false;
    
    protected $listeners = ['table-updated' => '$refresh'];
    
    protected $tableService;
    protected $orderService;
    
    public function boot(TableService $tableService, OrderService $orderService)
    {
        $this->tableService = $tableService;
        $this->orderService = $orderService;
    }
    
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

    public function setTableStatusFilter($status)
    {
        $this->filterTableStatus = $this->filterTableStatus === $status ? null : $status;
    }

    public function setCheckStatusFilter($status)
    {
        $this->filterCheckStatus = $this->filterCheckStatus === $status ? null : $status;
    }

    public function toggleOrderStatusFilter($status)
    {
        if (in_array($status, $this->filterOrderStatuses)) {
            $this->filterOrderStatuses = array_values(array_filter($this->filterOrderStatuses, fn($s) => $s !== $status));
        } else {
            $this->filterOrderStatuses[] = $status;
        }
    }

    public function clearFilters()
    {
        $this->filterTableStatus = null;
        $this->filterCheckStatus = null;
        $this->filterOrderStatuses = [];
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
        $validation = $this->tableService->validateTableData([
            'newTableName' => $this->newTableName,
            'newTableNumber' => $this->newTableNumber,
        ]);
        
        $this->validate($validation['rules'], $validation['messages']);

        $this->tableService->createTable(
            $this->userId,
            $this->newTableName,
            $this->newTableNumber
        );

        session()->flash('success', 'Local criado com sucesso!');
        $this->closeNewTableModal();
    }

    public function selectTable($tableId)
    {
        return redirect()->route('orders', ['tableId' => $tableId]);
    }

    public function openTableStatusModal($tableId)
    {
        $table = $this->tableService->getTableById($tableId);
        $this->selectedTableId = $tableId;
        $this->newTableStatus = $table->status;
        
        // Verifica se há check ativo (não Paid nem Canceled)
        $activeCheck = $this->orderService->findOrCreateCheck($tableId);
        $this->hasActiveCheck = $activeCheck && in_array($activeCheck->status, ['Open', 'Closing', 'Closed']);
        
        $this->showTableStatusModal = true;
    }

    public function closeTableStatusModal()
    {
        $this->showTableStatusModal = false;
        $this->selectedTableId = null;
        $this->newTableStatus = null;
        $this->hasActiveCheck = false;
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
        $this->closeTableStatusModal();
        $this->dispatch('table-updated');
    }

    public function render()
    {
        // Recalcula todos os checks ativos antes de carregar a view
        $this->orderService->recalculateAllActiveChecks();
        
        $tables = $this->tableService->getFilteredTables(
            $this->userId,
            $this->filterTableStatus,
            $this->filterCheckStatus,
            $this->filterOrderStatuses
        );

        return view('livewire.tables', [
            'tables' => $tables,
        ]);
    }
}
