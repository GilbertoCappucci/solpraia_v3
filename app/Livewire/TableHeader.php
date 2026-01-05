<?php

namespace App\Livewire;

use Livewire\Component;

class TableHeader extends Component
{
    public $selectionMode = false;
    public $selectedTables = [];
    public $canMerge = false;
    public $hasActiveFilters = false;
    public $title = 'Locais';

    public function mount($selectionMode = false, $selectedTables = [], $canMerge = false, $hasActiveFilters = false)
    {
        $this->selectionMode = $selectionMode;
        $this->selectedTables = $selectedTables;
        $this->canMerge = $canMerge;
        $this->hasActiveFilters = $hasActiveFilters;
    }

    public function toggleSelectionMode()
    {
        $this->dispatch('toggle-selection-mode');
    }

    public function openMergeModal()
    {
        $this->dispatch('open-merge-modal');
    }

    public function toggleFilters()
    {
        $this->dispatch('toggle-filters');
    }

    public function openNewTableModal()
    {
        $this->dispatch('open-new-table-modal');
    }

    public function cancelSelection()
    {
        $this->dispatch('cancel-selection');
    }

    public function render()
    {
        return view('livewire.table-header');
    }
}