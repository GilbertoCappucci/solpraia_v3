<?php

namespace App\Livewire;

use App\Enums\DepartamentEnum;
use App\Services\OrderService;
use App\Services\SettingService;
use App\Services\TableService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

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
    public $delayAlarmEnabled = true;
    public $showNewTableModal = false;
    public $newTableName = '';
    public $newTableNumber = '';
    public $showTableStatusModal = false;
    public $selectedTableId = null;
    public $newTableStatus = null;
    public $hasActiveCheck = false;
    public $showSettingsModal = false;
    public $settingsTab = 'alerts'; // 'alerts' ou 'display'
    public $timeLimitPending;
    public $timeLimitInProduction;
    public $timeLimitInTransit;
    public $timeLimitClosed;
    public $timeLimitReleasing;
    
    protected $listeners = ['table-updated' => '$refresh'];
    
    protected $tableService;
    protected $orderService;
    protected $settingService;
    
    public function boot(TableService $tableService, OrderService $orderService, SettingService $settingService)
    {
        $this->tableService = $tableService;
        $this->orderService = $orderService;
        $this->settingService = $settingService;
    }
    
    public function mount()
    {
        $this->userId = Auth::user()->isAdmin() 
            ? Auth::id() 
            : Auth::user()->user_id;
        
        // Carrega filtros da sessão (já foram carregados pelo SettingService no login)
        $this->filterTableStatuses = $this->settingService->getSetting('table_filter.table', []);
        $this->filterCheckStatuses = $this->settingService->getSetting('table_filter.check', []);
        $this->filterOrderStatuses = $this->settingService->getSetting('table_filter.order', []);
        $this->filterDepartaments = $this->settingService->getSetting('table_filter.departament', []);
        $this->globalFilterMode = $this->settingService->getSetting('table_filter.mode', 'AND');
        $this->showFilters = session('tables.showFilters', false);
        $this->delayAlarmEnabled = session('tables.delayAlarmEnabled', true);
        
        // Carrega time limits
        $this->timeLimitPending = $this->settingService->getSetting('time_limits.pending', 15);
        $this->timeLimitInProduction = $this->settingService->getSetting('time_limits.in_production', 30);
        $this->timeLimitInTransit = $this->settingService->getSetting('time_limits.in_transit', 10);
        $this->timeLimitClosed = $this->settingService->getSetting('time_limits.closed', 5);
        $this->timeLimitReleasing = $this->settingService->getSetting('time_limits.releasing', 10);
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
        $this->saveFiltersToSession();
    }

    public function toggleDelayAlarm()
    {
        $this->delayAlarmEnabled = !$this->delayAlarmEnabled;
        session(['tables.delayAlarmEnabled' => $this->delayAlarmEnabled]);
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

    public function toggleDepartamentFilter($departament)
    {
        if (in_array($departament, $this->filterDepartaments)) {
            $this->filterDepartaments = array_values(array_filter($this->filterDepartaments, fn($d) => $d !== $departament));
        } else {
            $this->filterDepartaments[] = $departament;
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
        $this->showFilters = false;
        
        // Limpa no banco e sessão
        $user = Auth::user();
        $this->settingService->updateSettings($user, [
            'table_filter.table' => [],
            'table_filter.check' => [],
            'table_filter.order' => [],
            'table_filter.departament' => [],
            'table_filter.mode' => 'AND',
        ]);
        
        session()->forget('tables.showFilters');
    }

    protected function saveFiltersToSession()
    {
        $user = Auth::user();
        
        // Atualiza no banco de dados e na sessão
        $this->settingService->updateSettings($user, [
            'table_filter.table' => $this->filterTableStatuses,
            'table_filter.check' => $this->filterCheckStatuses,
            'table_filter.order' => $this->filterOrderStatuses,
            'table_filter.departament' => $this->filterDepartaments,
            'table_filter.mode' => $this->globalFilterMode,
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
        return redirect()->route('orders', ['tableId' => $tableId]);
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
        $this->dispatch('table-updated');
    }

    public function openSettingsModal()
    {
        $this->settingsTab = 'alerts';
        $this->showSettingsModal = true;
    }

    public function closeSettingsModal()
    {
        $this->showSettingsModal = false;
    }

    public function saveSettings()
    {
        $this->validate([
            'timeLimitPending' => 'required|integer|min:1|max:120',
            'timeLimitInProduction' => 'required|integer|min:1|max:120',
            'timeLimitInTransit' => 'required|integer|min:1|max:120',
            'timeLimitClosed' => 'required|integer|min:1|max:120',
            'timeLimitReleasing' => 'required|integer|min:1|max:120',
        ]);

        $user = Auth::user();
        
        // Atualiza as configurações no banco e sessão
        $this->settingService->updateSettings($user, [
            'time_limits.pending' => $this->timeLimitPending,
            'time_limits.in_production' => $this->timeLimitInProduction,
            'time_limits.in_transit' => $this->timeLimitInTransit,
            'time_limits.closed' => $this->timeLimitClosed,
            'time_limits.releasing' => $this->timeLimitReleasing,
        ]);
        
        // Recarrega as configurações da sessão a partir do banco
        $settings = \App\Models\Setting::where('user_id', $user->id)->first();
        if ($settings) {
            $this->settingService->syncToSession($settings);
        }

        session()->flash('success', 'Configurações salvas com sucesso!');
        $this->closeSettingsModal();
    }

    public function getPollingIntervalProperty()
    {
        return config('restaurant.polling_interval', 5000);
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

        return view('livewire.tables', [
            'tables' => $tables,
        ]);
    }
}
