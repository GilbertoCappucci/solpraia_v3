<?php

namespace App\Livewire\Menu;

use Livewire\Attributes\Reactive;
use Livewire\Component;

class MenuHeader extends Component
{
    #[Reactive]
    public $selectedTable;

    #[Reactive]
    public $currentCheck;

    #[Reactive]
    public $title;

    public function backToOrders()
    {
        $this->dispatch('back-to-orders');
    }

    public function render()
    {
        return view('livewire.menu.menu-header');
    }
}
