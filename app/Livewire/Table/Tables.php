<?php

namespace App\Livewire\Table;

use App\Enums\CheckStatusEnum;
use App\Services\GlobalSettingService;
use App\Services\TableService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Tables extends Component
{
    public $title = 'Locais';
    public $userId;
    
    // Estados de seleção
    public $selectionMode = false;
    public $selectedTables = [];
    public $showMergeModal = false;
    public $mergeDestinationTableId = null;
    public $canMerge = false;

    // Configurações gerais
    public $timeLimits = [];
    
    // Filtros (para compatibilidade com view)
    public $showFilters = false;
    public $filterTableStatuses = [];
    public $filterCheckStatuses = [];
    public $filterOrderStatuses = [];
    public $filterDepartaments = [];
    public $globalFilterMode = 'AND';
    public $hasActiveFilters = false;
    
    // Modais
    public $showCreateModal = false;
    public $newTableName = '';
    public $newTableNumber = '';
    public $showStatusModal = false;
    public $selectedTableId = null;
    public $newTableStatus = null;
    public $hasActiveCheck = false;

    protected $tableService;
    protected $globalSettingService;

    public function boot(TableService $tableService, GlobalSettingService $globalSettingService)
    {
        $this->tableService = $tableService;
        $this->globalSettingService = $globalSettingService;
    }

    public function mount()
    {
        $this->userId = Auth::user()->user_id;
        $this->timeLimits = $this->globalSettingService->getTimeLimits(Auth::user());
    }

    public function getListeners()
    {
        return [
            "echo-private:global-setting-updated.{$this->userId},.global.setting.updated" => 'refreshSetting',
            "echo-private:tables-updated.{$this->userId},.table.updated" => 'onTableUpdated',
            "echo-private:tables-updated.{$this->userId},.check.updated" => 'onCheckUpdated',
            'filters-updated' => 'onFiltersUpdated',
            'table-selected' => 'selectTable',
            'select-table-for-merge' => 'selectTableForMerge',
            'toggle-selection-mode' => 'toggleSelectionMode',
            'cancel-selection' => 'cancelSelection',
            'toggle-filters' => 'toggleFilters',
            'open-create-modal' => 'openCreateModal',
            'open-merge-modal' => 'openMergeModal',
            'table-created' => '$refresh',
            'table-status-updated' => '$refresh',
            'merge-completed' => 'onMergeCompleted',
        ];
    }

    public function refreshSetting($data = null)
    {
        $this->timeLimits = $this->globalSettingService->getTimeLimits(Auth::user());
        $this->dispatch('$refresh');
    }

    public function toggleSelectionMode()
    {
        $this->selectionMode = !$this->selectionMode;
        if (!$this->selectionMode) {
            $this->selectedTables = [];
            $this->canMerge = false;
        }
    }

    public function onMergeCompleted()
    {
        $this->selectionMode = false;
        $this->selectedTables = [];
        $this->canMerge = false;
    }

    public function onTableUpdated($data)
    {
        if (isset($data['userId']) && $data['userId'] == $this->userId) {
            $this->dispatch('$refresh');
        }
    }

    public function onCheckUpdated($data)
    {
        if (isset($data['userId']) && $data['userId'] == $this->userId) {
            $this->dispatch('$refresh');
        }
    }

    public function cancelSelection()
    {
        $this->selectionMode = false;
        $this->selectedTables = [];
        $this->canMerge = false;
    }

    public function selectTableForMerge($tableId = null)
    {
        if ($tableId === null) {
            return;
        }

        if (in_array($tableId, $this->selectedTables)) {
            $this->selectedTables = array_values(array_filter($this->selectedTables, fn($id) => $id != $tableId));
        } else {
            $this->selectedTables[] = $tableId;
        }
        
        $this->updateCanMerge();
    }

    protected function updateCanMerge()
    {
        if (count($this->selectedTables) < 2) {
            $this->canMerge = false;
            return;
        }

        $tables = \App\Models\Table::whereIn('id', $this->selectedTables)
            ->where('user_id', $this->userId)
            ->get();

        $this->canMerge = $this->tableService->canMergeTables($tables);
    }

    public function onFiltersUpdated($filters)
    {
        // Recebe filtros atualizados do componente TableFilters
        // O componente será re-renderizado automaticamente
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function openCreateModal()
    {
        $this->showCreateModal = true;
        $this->newTableName = '';
        $this->newTableNumber = '';
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->newTableName = '';
        $this->newTableNumber = '';
    }

    public function openStatusModal($tableId)
    {
        $this->showStatusModal = true;
        $this->selectedTableId = $tableId;
    }

    public function closeStatusModal()
    {
        $this->showStatusModal = false;
        $this->selectedTableId = null;
    }

    public function openMergeModal()
    {
        if (count($this->selectedTables) >= 2) {
            $this->showMergeModal = true;
        }
    }

    public function closeMergeModal()
    {
        $this->showMergeModal = false;
        $this->mergeDestinationTableId = null;
    }

    public function selectTable($tableId)
    {
        if ($this->selectionMode) {
            $this->selectTableForMerge($tableId);
            return;
        }

        $latestCheck = \App\Models\Check::where('table_id', $tableId)
            ->whereIn('status', [
                CheckStatusEnum::OPEN->value,
                CheckStatusEnum::CLOSED->value,
                CheckStatusEnum::PAID->value
            ])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestCheck && $latestCheck->status === CheckStatusEnum::CLOSED->value) {
            return redirect()->route('check', ['checkId' => $latestCheck->id]);
        }

        return redirect()->route('orders', ['tableId' => $tableId]);
    }

    public function render()
    {
        $tables = $this->tableService->getFilteredTables(
            $this->userId,
            [],
            [],
            [],
            [],
            'AND'
        );
        
        $canMerge = $this->tableService->canMergeTables($tables);
        $this->canMerge = $canMerge;

        return view('livewire.table.tables', [
            'tables' => $tables,
            'canMerge' => $canMerge,
        ]);
    }
}
