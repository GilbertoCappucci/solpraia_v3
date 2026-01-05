<?php

namespace App\Livewire;

use App\Enums\CheckStatusEnum;
use App\Models\Check;
use App\Services\GlobalSettingService;
use App\Services\OrderService;
use App\Services\TableService;
use App\Services\UserPreferenceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

class Tables extends Component
{
    public $title = 'Locais';
    public $userId;
    
    // Estados de filtros (controlados pelo TableFilters)
    public $filterTableStatuses = [];
    public $filterCheckStatuses = [];
    public $filterOrderStatuses = [];
    public $filterDepartaments = [];
    public $globalFilterMode = 'AND';
    public $hasActiveFilters = false;

    // Estados de seleção (controlados pelo TableSelectionMode)
    public $selectionMode = false;
    public $selectedTables = [];
    public $showMergeModal = false;
    public $canMerge = false;

    // Configurações gerais
    public $timeLimits = [];
    
    // Timestamp para forçar re-renderização quando eventos via broadcasting chegam
    public $lastUpdate;

    protected $tableService;
    protected $orderService;
    protected $globalSettingService;
    protected $userPreferenceService;

    public function boot(TableService $tableService, OrderService $orderService, GlobalSettingService $globalSettingService, \App\Services\UserPreferenceService $userPreferenceService)
    {
        $this->tableService = $tableService;
        $this->orderService = $orderService;
        $this->globalSettingService = $globalSettingService;
        $this->userPreferenceService = $userPreferenceService;
    }

    public function mount()
    {
        $this->userId = Auth::user()->user_id;
        $this->timeLimits = $this->globalSettingService->getTimeLimits(Auth::user());
        
        // Inicializa os filtros a partir das preferências salvas
        $this->initializeFilters();
    }
    
    protected function initializeFilters()
    {
        // Carrega filtros das preferências do usuário
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
        $listeners = [
            "echo-private:global-setting-updated.{$this->userId},.global.setting.updated" => 'refreshSetting',
            "echo-private:tables-updated.{$this->userId},.table.updated" => 'onTableUpdated',
            "echo-private:tables-updated.{$this->userId},.check.updated" => 'onCheckUpdated',
            
            // Listeners para TableFilters
            'filters-changed' => 'onFiltersChanged',
            'toggle-filters' => 'toggleFilters',
            
            // Listeners para TableHeader
            'toggle-selection-mode' => 'toggleSelectionMode',
            'open-merge-modal' => 'openMergeModal',
            'cancel-selection' => 'cancelSelection',
            
            // Listeners para TableSelectionMode
            'selection-mode-changed' => 'onSelectionModeChanged',
            'selected-tables-changed' => 'onSelectedTablesChanged',
            'select-table-for-merge' => 'selectTableForMerge',
            
            // Listeners para modais
            'table-created' => '$refresh',
            'table-status-updated' => '$refresh',
            'merge-closed' => 'closeMergeModal',
            'merge-completed' => 'onMergeCompleted',
        ];
        
        return $listeners;
    }

    public function refreshSetting($data = null)
    {
        logger('🎯 refreshSetting CHAMADO no Livewire! Dados recebidos:', [
            'data' => $data,
            'userId' => $this->userId,
            'timestamp' => now()->format('H:i:s')
        ]);
        
        // Atualizar os time limits com as novas configurações
        $this->timeLimits = $this->globalSettingService->getTimeLimits(Auth::user());
        
        logger('✅ timeLimits atualizados:', $this->timeLimits);
    }

    public function onFiltersChanged($filters)
    {
        $this->filterTableStatuses = $filters['filterTableStatuses'] ?? [];
        $this->filterCheckStatuses = $filters['filterCheckStatuses'] ?? [];
        $this->filterOrderStatuses = $filters['filterOrderStatuses'] ?? [];
        $this->filterDepartaments = $filters['filterDepartaments'] ?? [];
        $this->globalFilterMode = $filters['globalFilterMode'] ?? 'AND';
        
        $this->hasActiveFilters = !empty($this->filterTableStatuses) || 
                                 !empty($this->filterCheckStatuses) || 
                                 !empty($this->filterOrderStatuses) || 
                                 !empty($this->filterDepartaments);
    }

    public function toggleSelectionMode()
    {
        logger('🔄 toggleSelectionMode called', [
            'before' => $this->selectionMode,
            'canMerge' => $this->canMerge,
        ]);
        
        $this->selectionMode = !$this->selectionMode;
        if (!$this->selectionMode) {
            $this->selectedTables = [];
            $this->canMerge = false;
        }
        
        logger('✅ toggleSelectionMode completed', [
            'after' => $this->selectionMode,
            'selectedTables' => $this->selectedTables,
        ]);
    }

    public function openMergeModal()
    {
        logger('🎯 openMergeModal called in Tables', [
            'selectedTables' => $this->selectedTables,
            'count' => count($this->selectedTables)
        ]);

        if (count($this->selectedTables) >= 2) {
            $this->showMergeModal = true;
            logger('✅ Modal opened', ['selectedTables' => $this->selectedTables]);
        } else {
            logger('❌ Not enough tables selected', ['count' => count($this->selectedTables)]);
        }
    }

    public function closeMergeModal()
    {
        $this->showMergeModal = false;
    }

    public function onMergeCompleted()
    {
        $this->showMergeModal = false;
        $this->selectionMode = false;
        $this->selectedTables = [];
        // O componente será atualizado automaticamente
    }

    public function onTableUpdated($data)
    {
        logger('🔄🔔 onTableUpdated invoked in Livewire Tables', [
            'data' => $data, 
            'userIdProp' => $this->userId,
            'timestamp' => now()->format('H:i:s.u')
        ]);
        
        // Só atualiza se a mesa pertencer a este usuário
        if (isset($data['userId']) && $data['userId'] == $this->userId) {
            logger('✅ Refreshing Tables component', ['userId' => $this->userId]);
            // Força refresh dos dados
            $this->dispatch('$refresh');
        } else {
            logger('❌ Skipping refresh - userId mismatch', [
                'data_userId' => $data['userId'] ?? 'not set',
                'component_userId' => $this->userId
            ]);
        }
    }

    public function onCheckUpdated($data)
    {
        logger('🔄🔔 onCheckUpdated invoked in Livewire', ['data' => $data, 'userIdProp' => $this->userId]);
        
        // Só atualiza se a mesa pertencer a este usuário
        if (isset($data['userId']) && $data['userId'] == $this->userId) {
            // Força refresh dos dados
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
            logger('⚠️ selectTableForMerge called without tableId');
            return;
        }

        if (in_array($tableId, $this->selectedTables)) {
            // Remove da seleção
            $this->selectedTables = array_values(array_filter($this->selectedTables, fn($id) => $id != $tableId));
        } else {
            // Adiciona à seleção
            $this->selectedTables[] = $tableId;
        }
        
        // Update canMerge based on selected tables
        $this->updateCanMerge();
        
        logger('📋 selectTableForMerge', [
            'tableId' => $tableId,
            'selectedTables' => $this->selectedTables,
            'canMerge' => $this->canMerge,
        ]);
    }

    public function onSelectionModeChanged($selectionMode)
    {
        $this->selectionMode = $selectionMode;
        if (!$selectionMode) {
            $this->selectedTables = [];
        }
    }

    public function onSelectedTablesChanged($selectedTables)
    {
        $this->selectedTables = $selectedTables;
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

        logger('🔍 updateCanMerge', [
            'selectedTables' => $this->selectedTables,
            'tablesFound' => $tables->count(),
            'tableIds' => $tables->pluck('id')->toArray(),
            'tableStatuses' => $tables->pluck('status')->toArray(),
        ]);

        $this->canMerge = $this->tableService->canMergeTables($tables);
        
        logger('✅ canMerge result', ['canMerge' => $this->canMerge]);
    }

    public function selectTable($tableId)
    {
        // Se estiver em modo de seleção, trata a seleção para unir
        if ($this->selectionMode) {
            $this->selectTableForMerge($tableId);
            return;
        }

        // Busca o check mais recente para decidir o redirecionamento
        // Consideramos apenas o check mais recente para esta mesa
        $latestCheck = Check::where('table_id', $tableId)
            ->whereIn('status', [
                CheckStatusEnum::OPEN->value,
                CheckStatusEnum::CLOSED->value,
                CheckStatusEnum::PAID->value
            ])
            ->orderBy('created_at', 'desc')
            ->first();

        // Se o check está Fechado ou Pago, redireciona para a rota de conferência do check
        if ($latestCheck && in_array($latestCheck->status, [
            CheckStatusEnum::CLOSED->value,
            //CheckStatusEnum::PAID->value
        ])) {
            return redirect()->route('check', ['checkId' => $latestCheck->id]);
        }

        // Comportamento padrão: redireciona para a página de pedidos
        return redirect()->route('orders', ['tableId' => $tableId]);
    }

    public function openTableStatusModal($tableId)
    {
        $this->dispatch('open-table-status-modal', tableId: $tableId);
    }

    public function render()
    {
        $tables = $this->tableService->getFilteredTables(
            $this->userId,
            $this->filterTableStatuses,
            $this->filterCheckStatuses,
            $this->filterOrderStatuses,
            $this->filterDepartaments,
            $this->globalFilterMode
        );

        // canMerge indica se há mesas suficientes disponíveis para união (independente de seleção)
        $canMerge = $this->tableService->canMergeTables($tables);
        
        // Atualiza a propriedade do componente
        $this->canMerge = $canMerge;

        /*
        logger('📊 Tables render', [
            'totalTables' => $tables->count(),
            'canMerge' => $canMerge,
            'selectionMode' => $this->selectionMode,
            'selectedCount' => count($this->selectedTables),
        ]);
        */

        return view('livewire.tables', [
            'tables' => $tables,
            'canMerge' => $canMerge,
        ]);
    }
}
