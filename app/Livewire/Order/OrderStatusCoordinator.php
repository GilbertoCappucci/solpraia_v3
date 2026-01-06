<?php

namespace App\Livewire\Order;

use Livewire\Component;

class OrderStatusCoordinator extends Component
{
    public $show = false;
    public $selectedTable;
    public $currentCheck;
    
    public function getListeners()
    {
        return [
            'open-status-modal' => 'openModal',
            'table-status-updated' => 'onTableStatusUpdated',
            'check-status-updated' => 'onCheckStatusUpdated',
        ];
    }

    public function openModal()
    {
        $this->show = true;
    }

    public function closeModal()
    {
        $this->show = false;
    }

    public function onTableStatusUpdated()
    {
        // Atualiza a table no parent
        $this->dispatch('refresh-parent');
    }

    public function onCheckStatusUpdated()
    {
        // Atualiza o check no parent
        $this->dispatch('refresh-parent');
    }

    public function render()
    {
        return view('livewire.order.order-status-coordinator');
    }
}
