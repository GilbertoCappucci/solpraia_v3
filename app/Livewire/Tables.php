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
    public $canMerge = false;
    public $showMergeModal = false;

    // Configurações gerais
    public $timeLimits = [];
    public $pollingInterval = 60;

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
            'global.setting.updated' => 'refreshSetting',
            
            // Listeners para TableFilters
            'filters-changed' => 'onFiltersChanged',
            'toggle-filters' => 'toggleFilters',
            
            // Listeners para TableHeader
            'toggle-selection-mode' => 'toggleSelectionMode',
            'open-merge-modal' => 'openMergeModal',
            'cancel-selection' => 'cancelSelection',
            'open-new-table-modal' => 'openNewTableModal',
            
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
        
        logger('📻 Livewire getListeners configured:', $listeners);
        
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

    public function toggleFilters()
    {
        // O evento toggle-filters já foi despachado pelo TableHeader
        // e será capturado pelo listener do TableFilters
        // Não precisamos fazer nada aqui
    }

    public function toggleSelectionMode()
    {
        $this->selectionMode = !$this->selectionMode;
        if (!$this->selectionMode) {
            $this->selectedTables = [];
        }
        
        logger('🎯 toggleSelectionMode called', [
            'selectionMode' => $this->selectionMode,
            'selectedTables' => $this->selectedTables
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
        $this->dispatch('$refresh');
    }

    public function cancelSelection()
    {
        $this->selectionMode = false;
        $this->selectedTables = [];
    }

    public function openNewTableModal()
    {
        $this->dispatch('open-new-table-modal');
    }

    public function selectTableForMerge($tableId)
    {
        logger('🎯 selectTableForMerge called', [
            'tableId' => $tableId,
            'currentSelectedTables' => $this->selectedTables,
            'selectionMode' => $this->selectionMode
        ]);

        if (in_array($tableId, $this->selectedTables)) {
            // Remove da seleção
            $this->selectedTables = array_values(array_filter($this->selectedTables, fn($id) => $id != $tableId));
            logger('✅ Mesa removida da seleção', ['tableId' => $tableId, 'newSelection' => $this->selectedTables]);
        } else {
            // Adiciona à seleção
            $this->selectedTables[] = $tableId;
            logger('✅ Mesa adicionada à seleção', ['tableId' => $tableId, 'newSelection' => $this->selectedTables]);
        }
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
    }

    public function selectTable($tableId)
    {
        logger('🎯 selectTable called', [
            'tableId' => $tableId,
            'selectionMode' => $this->selectionMode
        ]);

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
        // Recalcula todos os checks ativos antes de carregar a view
        $this->orderService->recalculateAllActiveChecks();

        $tables = $this->tableService->getFilteredTables(
            $this->userId,
            $this->filterTableStatuses,
            $this->filterCheckStatuses,
            $this->filterOrderStatuses,
            $this->filterDepartaments,
            $this->globalFilterMode
        );

        // Permite unir se há pelo menos 2 mesas no total (independente de terem check ou não)
        // Isso permite unir mesas vazias com mesas ocupadas
        $this->canMerge = $tables->count() >= 2;

        return view('livewire.tables', [
            'tables' => $tables,
        ]);
    }
}
