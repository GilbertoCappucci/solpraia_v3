<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Reactive;

class TableHeader extends Component
{
    #[Reactive]
    public $selectionMode = false;
    
    #[Reactive]
    public $selectedTables = [];
    
    #[Reactive]
    public $canMerge = false;
    
    #[Reactive]
    public $hasActiveFilters = false;
    
    public $title = 'Locais';

    public function toggleSelectionMode()
    {
        logger('🎯 TableHeader::toggleSelectionMode', [
            'selectionMode' => $this->selectionMode,
            'canMerge' => $this->canMerge,
            'selectedTables' => $this->selectedTables,
        ]);
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