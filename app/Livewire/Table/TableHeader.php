<?php

namespace App\Livewire\Table;

use Livewire\Component;
use Livewire\Attributes\Reactive;

class TableHeader extends Component
{
    #[Reactive]
    public $selectionMode = false;
    
    #[Reactive]
    public $selectedTablesCount = 0;
    
    #[Reactive]
    public $canMerge = false;
    
    #[Reactive]
    public $hasActiveFilters = false;
    
    #[Reactive]
    public $title = 'Locais';

    public function toggleSelectionMode()
    {
        $this->dispatch('toggle-selection-mode')->to(Tables::class);
    }

    public function openMergeModal()
    {
        $this->dispatch('open-merge-modal')->to(Tables::class);
    }

    public function cancelSelection()
    {
        $this->dispatch('cancel-selection')->to(Tables::class);
    }

    public function toggleFilters()
    {
        $this->dispatch('toggle-filters');
    }

    public function openCreateModal()
    {
        $this->dispatch('open-create-modal')->to(Tables::class);
    }

    public function render()
    {
        return view('livewire.table.table-header');
    }
}
