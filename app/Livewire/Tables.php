<?php

namespace App\Livewire;

use App\Enums\CheckStatusEnum;
use App\Enums\TableStatusEnum;
use App\Models\Check;
use App\Models\Table;
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
    public $mergeDestinationTableId = null;

    // Configurações gerais
    public $timeLimits = [];
    
    // Timestamp para forçar re-renderização quando eventos via broadcasting chegam
    public $lastUpdate;

    // TableFilters properties
    public $showFilters = false;

    // TableCreateModal properties
    public $showCreateModal = false;
    public $newTableName = '';
    public $newTableNumber = '';

    // TableStatusModal properties
    public $showStatusModal = false;
    public $selectedTableId = null;
    public $newTableStatus = null;
    public $hasActiveCheck = false;

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
        
        // TableFilters mount
        $this->showFilters = session('tables.showFilters', false);
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

    public function booted()
    {
        // Emite evento para sincronizar filtros após a inicialização completa
        $this->dispatch('filters-changed', [
            'filterTableStatuses' => $this->filterTableStatuses,
            'filterCheckStatuses' => $this->filterCheckStatuses,
            'filterOrderStatuses' => $this->filterOrderStatuses,
            'filterDepartaments' => $this->filterDepartaments,
            'globalFilterMode' => $this->globalFilterMode,
        ]);
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
            
            // TableCreateModal listeners
            'open-new-table-modal' => 'openCreateModal',
            
            // TableStatusModal listeners
            'open-table-status-modal' => 'openStatusModal',
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

        // Força refresh dos dados
        $this->dispatch('$refresh');
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

    // ============================================================================
    // MÉTODOS DE TableFilters
    // ============================================================================

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
        $this->saveFiltersToSession();
        
        // Emite evento para notificar componente pai
        $this->dispatch('filters-toggled', $this->showFilters);
    }

    public function toggleTableStatusFilter($status)
    {
        if (in_array($status, $this->filterTableStatuses)) {
            $this->filterTableStatuses = array_values(array_filter($this->filterTableStatuses, fn($s) => $s !== $status));
        } else {
            $this->filterTableStatuses[] = $status;
        }
        $this->saveFiltersToSession();
        $this->emitFiltersChanged();
    }

    public function toggleCheckStatusFilter($status)
    {
        if (in_array($status, $this->filterCheckStatuses)) {
            $this->filterCheckStatuses = array_values(array_filter($this->filterCheckStatuses, fn($s) => $s !== $status));
        } else {
            $this->filterCheckStatuses[] = $status;
        }
        $this->saveFiltersToSession();
        $this->emitFiltersChanged();
    }

    public function toggleOrderStatusFilter($status)
    {
        if (in_array($status, $this->filterOrderStatuses)) {
            $this->filterOrderStatuses = array_values(array_filter($this->filterOrderStatuses, fn($s) => $s !== $status));
        } else {
            $this->filterOrderStatuses[] = $status;
        }
        $this->saveFiltersToSession();
        $this->emitFiltersChanged();
    }

    public function toggleDepartamentFilter($departament)
    {
        if (in_array($departament, $this->filterDepartaments)) {
            $this->filterDepartaments = array_values(array_filter($this->filterDepartaments, fn($d) => $d !== $departament));
        } else {
            $this->filterDepartaments[] = $departament;
        }
        $this->saveFiltersToSession();
        $this->emitFiltersChanged();
    }

    public function toggleGlobalFilterMode()
    {
        $this->globalFilterMode = $this->globalFilterMode === 'OR' ? 'AND' : 'OR';
        $this->saveFiltersToSession();
        $this->emitFiltersChanged();
    }

    public function clearFilters()
    {
        $this->filterTableStatuses = [];
        $this->filterCheckStatuses = [];
        $this->filterOrderStatuses = [];
        $this->filterDepartaments = [];
        $this->globalFilterMode = 'AND';

        // Limpa no banco e sessão
        $user = Auth::user();
        $this->userPreferenceService->updatePreferences($user, [
            'table_filter_table' => [],
            'table_filter_check' => [],
            'table_filter_order' => [],
            'table_filter_departament' => [],
            'table_filter_mode' => 'AND',
        ]);

        session()->forget('tables.showFilters');
        $this->showFilters = false;
        
        $this->emitFiltersChanged();
        $this->dispatch('filters-toggled', false);
    }

    protected function saveFiltersToSession()
    {
        $user = Auth::user();

        // Atualiza no banco de dados e na sessão
        $this->userPreferenceService->updatePreferences($user, [
            'table_filter_table' => $this->filterTableStatuses,
            'table_filter_check' => $this->filterCheckStatuses,
            'table_filter_order' => $this->filterOrderStatuses,
            'table_filter_departament' => $this->filterDepartaments,
            'table_filter_mode' => $this->globalFilterMode,
        ]);

        // Mantém configurações locais da view
        session([
            'tables.showFilters' => $this->showFilters,
        ]);
    }

    protected function emitFiltersChanged()
    {
        $this->dispatch('filters-changed', [
            'filterTableStatuses' => $this->filterTableStatuses,
            'filterCheckStatuses' => $this->filterCheckStatuses,
            'filterOrderStatuses' => $this->filterOrderStatuses,
            'filterDepartaments' => $this->filterDepartaments,
            'globalFilterMode' => $this->globalFilterMode,
        ]);
    }

    public function getHasActiveFiltersProperty()
    {
        return !empty($this->filterTableStatuses) || 
               !empty($this->filterCheckStatuses) || 
               !empty($this->filterOrderStatuses) || 
               !empty($this->filterDepartaments);
    }

    // ============================================================================
    // MÉTODOS DE TableCreateModal
    // ============================================================================

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
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function createTable()
    {
        $this->validate([
            'newTableNumber' => [
                'required',
                'integer',
                'min:1',
                'unique:tables,number,NULL,id,user_id,' . Auth::id()
            ],
            'newTableName' => 'nullable|string|max:255',
        ], [
            'newTableNumber.required' => 'O número do local é obrigatório.',
            'newTableNumber.integer' => 'O número deve ser um valor numérico.',
            'newTableNumber.min' => 'O número deve ser maior que zero.',
            'newTableNumber.unique' => 'Já existe um local com este número.',
        ]);

        Table::create([
            'user_id' => Auth::id(),
            'name' => $this->newTableName,
            'number' => $this->newTableNumber,
            'status' => TableStatusEnum::FREE->value,
        ]);

        session()->flash('success', 'Local criado com sucesso!');
        $this->closeCreateModal();
        $this->dispatch('table-created');
    }

    // ============================================================================
    // MÉTODOS DE TableStatusModal
    // ============================================================================

    public function openStatusModal($tableId)
    {
        $table = $this->tableService->getTableById($tableId);
        $this->selectedTableId = $tableId;
        $this->newTableStatus = $table->status;

        // Verifica se há check ativo (não Paid nem Canceled)
        $activeCheck = $this->orderService->findCheck($tableId); // Apenas busca, não cria
        $this->hasActiveCheck = $activeCheck && in_array($activeCheck->status, ['Open', 'Closed']);

        $this->showStatusModal = true;
    }

    public function closeStatusModal()
    {
        $this->showStatusModal = false;
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
        $this->closeStatusModal();
        
        // Notifica o componente pai para atualizar
        $this->dispatch('table-status-updated');
    }

    // ============================================================================
    // MÉTODOS DE TableSelectionMode  
    // ============================================================================

    public function mergeTables()
    {
        // 1. Validações finais
        if (count($this->selectedTables) < 2) {
            session()->flash('error', 'Selecione pelo menos duas mesas para unir.');
            $this->closeMergeModal();
            return;
        }
        if (!$this->mergeDestinationTableId) {
            session()->flash('error', 'Selecione uma mesa de destino para a união.');
            $this->closeMergeModal();
            return;
        }

        // 2. Coletar IDs das comandas
        $allSelectedTableIds = $this->selectedTables;
        $destinationTableId = $this->mergeDestinationTableId;

        // Garante que a mesa de destino é uma das selecionadas
        if (!in_array($destinationTableId, $allSelectedTableIds)) {
            session()->flash('error', 'A mesa de destino deve ser uma das mesas selecionadas.');
            $this->closeMergeModal();
            return;
        }

        // Encontra as mesas e seus checks ativos
        $selectedTablesData = $this->tableService->getFilteredTables(
            Auth::user()->user_id,
            [],
            [],
            [],
            [],
            'OR'
        )->whereIn('id', $allSelectedTableIds);

        $destinationTable = $selectedTablesData->where('id', $destinationTableId)->first();
        if (!$destinationTable || !$destinationTable->checkId) {
            session()->flash('error', "A mesa de destino (Mesa {$destinationTable->number}) não possui uma comanda ativa para receber os pedidos.");
            $this->closeMergeModal();
            return;
        }
        $destinationCheckId = $destinationTable->checkId;

        $sourceTableIds = array_diff($allSelectedTableIds, [$destinationTableId]);
        $sourceCheckIds = [];
        $tablesToFree = [];

        foreach ($sourceTableIds as $tableId) {
            $table = $selectedTablesData->where('id', $tableId)->first();
            if ($table && $table->checkId) {
                $sourceCheckIds[] = $table->checkId;
                $tablesToFree[] = $table->id;
            } else {
                // Caso alguma mesa de origem não tenha check ativo, apenas liberamos ela
                if ($table) {
                    $tablesToFree[] = $table->id;
                }
            }
        }

        // Se não houver comandas de origem válidas para unir, mas houver mesas para liberar, liberamos.
        if (empty($sourceCheckIds) && !empty($tablesToFree)) {
            $this->tableService->releaseTables($tablesToFree);
            session()->flash('success', 'As mesas selecionadas foram liberadas.');
            $this->closeMergeModal();
            $this->toggleSelectionMode();
            return;
        } elseif (empty($sourceCheckIds)) {
            session()->flash('error', 'Nenhuma comanda de origem válida encontrada para unir.');
            $this->closeMergeModal();
            return;
        }

        // 3. Chamar o serviço de união
        $mergeResult = $this->orderService->mergeChecks($sourceCheckIds, $destinationCheckId);

        if ($mergeResult['success']) {
            // 4. Liberar mesas de origem
            $this->tableService->releaseTables($tablesToFree);
            session()->flash('success', $mergeResult['message']);
        } else {
            session()->flash('error', $mergeResult['message']);
        }

        // 5. Resetar estado e atualizar UI
        $this->closeMergeModal();
        $this->toggleSelectionMode();
    }
}
