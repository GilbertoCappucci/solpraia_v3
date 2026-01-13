<?php

namespace App\Livewire\Table;

use App\Services\UserPreferenceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;

class TableFilters extends Component
{
    public $showFilters = false;
    public $filterTableStatuses = [];
    public $filterCheckStatuses = [];
    public $filterOrderStatuses = [];
    public $filterDepartaments = [];
    public $globalFilterMode = 'AND';

    protected $userPreferenceService;

    public function boot(UserPreferenceService $userPreferenceService)
    {
        $this->userPreferenceService = $userPreferenceService;
    }

    public function mount()
    {
        // Carrega filtros das preferências do usuário
        $this->filterTableStatuses = $this->userPreferenceService->getPreference('table_filter_table', []);
        $this->filterCheckStatuses = $this->userPreferenceService->getPreference('table_filter_check', []);
        $this->filterOrderStatuses = $this->userPreferenceService->getPreference('table_filter_order', []);
        $this->filterDepartaments = $this->userPreferenceService->getPreference('table_filter_departament', []);
        $this->globalFilterMode = $this->userPreferenceService->getPreference('table_filter_mode', 'AND');
        
        // Carrega visibilidade do modal da sessão
        $this->showFilters = session('tables.showFilters', false);
    }

    #[Computed]
    public function hasActiveFilters()
    {
        return !empty($this->filterTableStatuses) || 
               !empty($this->filterCheckStatuses) || 
               !empty($this->filterOrderStatuses) || 
               !empty($this->filterDepartaments);
    }

    public function getListeners()
    {
        return [
            'toggle-filters' => 'toggleFilters',
        ];
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;

        session(['tables.showFilters' => $this->showFilters]);
        
        $this->dispatch('filters-toggled', $this->showFilters);
    }

    public function toggleTableStatusFilter($status)
    {
        if (in_array($status, $this->filterTableStatuses)) {
            $this->filterTableStatuses = array_values(array_filter($this->filterTableStatuses, fn($s) => $s !== $status));
        } else {
            $this->filterTableStatuses[] = $status;
        }
        $this->saveAndEmit();
    }

    public function toggleCheckStatusFilter($status)
    {
        if (in_array($status, $this->filterCheckStatuses)) {
            $this->filterCheckStatuses = array_values(array_filter($this->filterCheckStatuses, fn($s) => $s !== $status));
        } else {
            $this->filterCheckStatuses[] = $status;
        }
        $this->saveAndEmit();
    }

    public function toggleOrderStatusFilter($status)
    {
        if (in_array($status, $this->filterOrderStatuses)) {
            $this->filterOrderStatuses = array_values(array_filter($this->filterOrderStatuses, fn($s) => $s !== $status));
        } else {
            $this->filterOrderStatuses[] = $status;
        }
        $this->saveAndEmit();
    }

    public function toggleDepartamentFilter($departament)
    {
        if (in_array($departament, $this->filterDepartaments)) {
            $this->filterDepartaments = array_values(array_filter($this->filterDepartaments, fn($d) => $d !== $departament));
        } else {
            $this->filterDepartaments[] = $departament;
        }
        $this->saveAndEmit();
    }

    public function toggleGlobalFilterMode()
    {
        $this->globalFilterMode = $this->globalFilterMode === 'OR' ? 'AND' : 'OR';
        $this->saveAndEmit();
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
        
        $this->dispatch('filters-updated', [
            'tableStatuses' => [],
            'checkStatuses' => [],
            'orderStatuses' => [],
            'departaments' => [],
            'mode' => 'AND',
            'hasActive' => false,
        ]);
        
        $this->dispatch('filters-toggled', false);
    }

    protected function saveAndEmit()
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

        // Emite evento para o componente pai atualizar
        $this->dispatch('filters-updated', [
            'tableStatuses' => $this->filterTableStatuses,
            'checkStatuses' => $this->filterCheckStatuses,
            'orderStatuses' => $this->filterOrderStatuses,
            'departaments' => $this->filterDepartaments,
            'mode' => $this->globalFilterMode,
            'hasActive' => $this->hasActiveFilters,
        ]);
    }

    public function render()
    {
        return view('livewire.table.table-filters');
    }
}
