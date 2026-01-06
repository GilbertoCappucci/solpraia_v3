<?php

namespace App\Livewire\Menu;

use Livewire\Attributes\Reactive;
use Livewire\Component;

class MenuSearch extends Component
{
    #[Reactive]
    public $activeMenuId;

    public $searchTerm = '';

    public function updatedSearchTerm()
    {
        $this->dispatch('search-updated', searchTerm: $this->searchTerm);
    }

    public function render()
    {
        return view('livewire.menu.menu-search');
    }
}
