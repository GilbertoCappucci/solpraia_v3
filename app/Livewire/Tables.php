<?php

namespace App\Livewire;

use App\Enums\DepartamentEnum;
use App\Enums\CheckStatusEnum;
use App\Models\Check;
use App\Services\GlobalSettingService;
use App\Services\OrderService;
use App\Services\UserPreferenceService;
use App\Services\TableService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

use Livewire\Attributes\On;

class Tables extends Component
{
    public $title = 'Locais';
    public $userId;
    public $filterTableStatuses = [];
    public $filterCheckStatuses = [];
    public $filterOrderStatuses = [];
    public $filterDepartaments = [];
    public $globalFilterMode = 'AND';
    public $showFilters = false;
    public $showNewTableModal = false;
    public $newTableName = '';
    public $newTableNumber = '';
    public $showTableStatusModal = false;
    public $selectedTableId = null;
    public $newTableStatus = null;
    public $hasActiveCheck = false;

    public $selectionMode = false;
    public $selectedTables = [];
    public $showMergeModal = false;
    public $mergeDestinationTableId = null;
    public $canMerge = false;

    public $timeLimits = [];
    public $pollingInterval = 60;

    protected $tableService;
    protected $orderService;
    protected $userPreferenceService;
    protected $globalSettingService;

    public function boot(TableService $tableService, OrderService $orderService, UserPreferenceService $userPreferenceService, GlobalSettingService $globalSettingService)
    {
        $this->tableService = $tableService;
        $this->orderService = $orderService;
        $this->userPreferenceService = $userPreferenceService;
        $this->globalSettingService = $globalSettingService;
    }

    public function mount()
    {
        $this->userId = Auth::user()->user_id;

        // Carrega filtros da sessão (já foram carregados pelo UserPreferenceService no login)
        $this->filterTableStatuses = $this->userPreferenceService->getPreference('table_filter_table', []);
        $this->filterCheckStatuses = $this->userPreferenceService->getPreference('table_filter_check', []);
        $this->filterOrderStatuses = $this->userPreferenceService->getPreference('table_filter_order', []);
        $this->filterDepartaments = $this->userPreferenceService->getPreference('table_filter_departament', []);
        $this->globalFilterMode = $this->userPreferenceService->getPreference('table_filter_mode', 'AND');
        $this->showFilters = session('tables.showFilters', false);

        $this->timeLimits = $this->globalSettingService->getTimeLimits(Auth::user());
    }

    public function getListeners()
    {
        $listeners = [
            'global.setting.updated' => 'refreshSetting',
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

    public function onMergeCompleted($payload = null)
    {
        logger('🔀 Tables.onMergeCompleted called', ['payload' => $payload, 'selectedTables' => $this->selectedTables]);

        $success = false;
        $message = null;
        if (is_array($payload)) {
            $success = $payload['success'] ?? false;
            $message = $payload['message'] ?? null;
        }

        if ($success) {
            session()->flash('success', $message ?? 'Operação realizada com sucesso.');
        } else {
            session()->flash('error', $message ?? 'Erro ao realizar operação.');
        }

        $this->closeMergeModal();
        if ($this->selectionMode) {
            $this->toggleSelectionMode();
        }
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
        $this->saveFiltersToSession();
    }

    public function toggleTableStatusFilter($status)
    {
        if (in_array($status, $this->filterTableStatuses)) {
            $this->filterTableStatuses = array_values(array_filter($this->filterTableStatuses, fn($s) => $s !== $status));
        } else {
            $this->filterTableStatuses[] = $status;
        }
        $this->saveFiltersToSession();
    }

    public function toggleCheckStatusFilter($status)
    {
        if (in_array($status, $this->filterCheckStatuses)) {
            $this->filterCheckStatuses = array_values(array_filter($this->filterCheckStatuses, fn($s) => $s !== $status));
        } else {
            $this->filterCheckStatuses[] = $status;
        }
        $this->saveFiltersToSession();
    }

    public function toggleOrderStatusFilter($status)
    {
        if (in_array($status, $this->filterOrderStatuses)) {
            $this->filterOrderStatuses = array_values(array_filter($this->filterOrderStatuses, fn($s) => $s !== $status));
        } else {
            $this->filterOrderStatuses[] = $status;
        }
        $this->saveFiltersToSession();
    }

    public function toggleGlobalFilterMode()
    {
        $this->globalFilterMode = $this->globalFilterMode === 'OR' ? 'AND' : 'OR';
        $this->saveFiltersToSession();
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
            'userId' => $this->userId,
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
        // Se estiver em modo de seleção, o clique seleciona para unir.
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

    public function toggleSelectionMode()
    {
        $this->selectionMode = !$this->selectionMode;
        // Reseta a seleção ao sair do modo
        if (!$this->selectionMode) {
            $this->selectedTables = [];
            $this->showMergeModal = false;
        }
    }

    public function selectTableForMerge($tableId)
    {
        // Verifica se a mesa já está selecionada
        if (in_array($tableId, $this->selectedTables)) {
            // Remove da seleção
            $this->selectedTables = array_diff($this->selectedTables, [$tableId]);
        } else {
            // Adiciona na seleção
            $this->selectedTables[] = $tableId;
        }
    }

    public function openMergeModal()
    {
        // Garante que há pelo menos 2 mesas para unir
        if (count($this->selectedTables) < 2) {
            session()->flash('error', 'Selecione pelo menos duas mesas para unir.');
            return;
        }

        // Define um destino padrão (a primeira mesa selecionada)
        $this->mergeDestinationTableId = $this->selectedTables[0] ?? null;
        $this->showMergeModal = true;
    }

    public function closeMergeModal()
    {
        $this->showMergeModal = false;
        $this->mergeDestinationTableId = null;
    }

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
            $this->userId,
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

    public function openTableStatusModal($tableId)
    {
        $table = $this->tableService->getTableById($tableId);
        $this->selectedTableId = $tableId;
        $this->newTableStatus = $table->status;

        // Verifica se há check ativo (não Paid nem Canceled)
        $activeCheck = $this->orderService->findOrCreateCheck($tableId);
        $this->hasActiveCheck = $activeCheck && in_array($activeCheck->status, ['Open', 'Closed']);

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

        $this->canMerge = $tables->whereNotNull('checkId')->count() >= 2;

        return view('livewire.tables', [
            'tables' => $tables,
        ]);
    }
}
