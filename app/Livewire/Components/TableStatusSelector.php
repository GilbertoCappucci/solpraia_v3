<?php

namespace App\Livewire\Components;

use Livewire\Component;

class TableStatusSelector extends Component
{
    public $table;
    public ?string $newTableStatus = null;
    public bool $hasActiveCheck = false;

    public function mount($table, ?string $newTableStatus = null, bool $hasActiveCheck = false)
    {
        $this->table = $table;
        $this->newTableStatus = $newTableStatus;
        $this->hasActiveCheck = $hasActiveCheck;
    }

    public function selectStatus($status)
    {
        $this->newTableStatus = $status;
        
        // Notifica o componente pai sobre a mudança
        $this->dispatch('set', property: 'newTableStatus', value: $status);
    }

    public function getStatuses(): array
    {
        return [
            ['value' => 'free', 'label' => 'Livre', 'activeColor' => 'bg-gray-500'],
            ['value' => 'occupied', 'label' => 'Ocupada', 'activeColor' => 'bg-green-600'],
            ['value' => 'reserved', 'label' => 'Reservada', 'activeColor' => 'bg-purple-500'],
            ['value' => 'releasing', 'label' => 'Liberando', 'activeColor' => 'bg-teal-500'],
            ['value' => 'close', 'label' => 'Fechada', 'activeColor' => 'bg-red-600'],
        ];
    }

    public function render()
    {
        return view('livewire.components.table-status-selector');
    }
}
