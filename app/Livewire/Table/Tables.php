<?php

namespace App\Livewire\Table;

use App\Enums\CheckStatusEnum;
use App\Services\GlobalSettingService;
use App\Services\Table\TableService;
use App\Services\UserPreferenceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Tables extends Component
{
    public $title = 'Locais';
    public $adminId;
    
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
    protected $userPreferenceService;

    public function boot(TableService $tableService, GlobalSettingService $globalSettingService,UserPreferenceService $userPreferenceService)
    {
        $this->tableService = $tableService;
        $this->globalSettingService = $globalSettingService;
        $this->userPreferenceService = $userPreferenceService;
    }

    public function mount()
    {
        $this->adminId = Auth::user()->admin_id;
        $this->timeLimits = $this->globalSettingService->getTimeLimits(Auth::user());
        
        // Carrega filtros iniciais
        $this->filterTableStatuses = $this->userPreferenceService->getPreference('table_filter_table', []);
        $this->filterCheckStatuses = $this->userPreferenceService->getPreference('table_filter_check', []);
        $this->filterOrderStatuses = $this->userPreferenceService->getPreference('table_filter_order', []);
        $this->filterDepartaments = $this->userPreferenceService->getPreference('table_filter_departament', []);
        $this->globalFilterMode = $this->userPreferenceService->getPreference('table_filter_mode', 'AND');
        
        $this->hasActiveFilters = !empty($this->filterTableStatuses) || 
                                 !empty($this->filterCheckStatuses) || 
                                 !empty($this->filterOrderStatuses) || 
                                 !empty($this->filterDepartaments);
    }

    public function getListeners()
    {
        return [
            "echo-private:global-setting-updated.{$this->adminId},.global.setting.updated" => 'refreshSetting',
            "echo-private:tables-updated.{$this->adminId},.table.updated" => 'onTableUpdated',
            "echo-private:tables-updated.{$this->adminId},.check.updated" => 'onCheckUpdated',
            'filters-updated' => 'onFiltersUpdated',
            'table-selected' => 'selectTable',
            'select-table-for-merge' => 'selectTableForMerge',
            'toggle-selection-mode' => 'toggleSelectionMode',
            'cancel-selection' => 'cancelSelection',
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
        
        // Notifica todos os TableCards sobre a mudança de modo
        $this->dispatch('selection-mode-changed', 
            selectionMode: $this->selectionMode,
            selectedTables: $this->selectedTables
        );
    }

    public function onMergeCompleted()
    {
        $this->selectionMode = false;
        $this->selectedTables = [];
        $this->canMerge = false;
    }

    public function onTableUpdated($data)
    {
        if (isset($data['adminId']) && $data['adminId'] == $this->adminId) {
            $this->dispatch('$refresh');
        }
    }

    public function onCheckUpdated($data)
    {
        if (isset($data['adminId']) && $data['adminId'] == $this->adminId) {
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
        
        // Notifica todos os TableCards sobre a mudança na seleção
        $this->dispatch('selected-tables-updated', selectedTables: $this->selectedTables);
    }

    protected function updateCanMerge()
    {
        if (count($this->selectedTables) < 2) {
            $this->canMerge = false;
            return;
        }

        $tables = \App\Models\Table::whereIn('id', $this->selectedTables)
            ->where('admin_id', $this->adminId)
            ->get();

        $this->canMerge = $this->tableService->canMergeTables($tables);
    }

    public function onFiltersUpdated($filters)
    {
        // Recebe filtros atualizados do componente TableFilters
        $this->filterTableStatuses = $filters['tableStatuses'] ?? [];
        $this->filterCheckStatuses = $filters['checkStatuses'] ?? [];
        $this->filterOrderStatuses = $filters['orderStatuses'] ?? [];
        $this->filterDepartaments = $filters['departaments'] ?? [];
        $this->globalFilterMode = $filters['mode'] ?? 'AND';
        $this->hasActiveFilters = $filters['hasActive'] ?? false;
    }

    public function openCreateModal()
    {
        $this->showCreateModal = true;
        $this->newTableName = '';
        $this->newTableNumber = '';
        
        // Despacha evento para o componente TableCreateModal abrir
        $this->dispatch('open-create-modal-component');
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
            
            // Despacha evento para o componente TableMergeModal abrir
            $this->dispatch('open-merge-modal-component', 
                selectedTables: $this->selectedTables
            );
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
            $this->adminId,
            $this->filterTableStatuses,
            $this->filterCheckStatuses,
            $this->filterOrderStatuses,
            $this->filterDepartaments,
            $this->globalFilterMode
        );
        
        $canMerge = $this->tableService->canMergeTables($tables);
        $this->canMerge = $canMerge;

        return view('livewire.table.tables', [
            'tables' => $tables,
            'canMerge' => $canMerge,
        ]);
    }
}
