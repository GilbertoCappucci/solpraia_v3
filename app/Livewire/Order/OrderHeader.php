<?php

namespace App\Livewire\Order;

use Livewire\Component;
use Livewire\Attributes\Reactive;

class OrderHeader extends Component
{
    #[Reactive]
    public $selectedTable;
    
    #[Reactive]
    public $statusFiltersCount = 5;
    
    public $userId;

    public function mount($selectedTable, $statusFiltersCount = 5, $userId = null)
    {
        $this->selectedTable = $selectedTable;
        $this->statusFiltersCount = $statusFiltersCount;
        $this->userId = $userId;
    }

    public function getListeners()
    {
        $listeners = [
            'filters-updated' => 'onFiltersUpdated',
        ];

        if ($this->userId) {
            $listeners["echo-private:tables-updated.{$this->userId},.table.updated"] = 'onTableUpdated';
        }

        return $listeners;
    }

    public function onTableUpdated($data)
    {
        if (isset($data['tableId']) && $data['tableId'] == $this->selectedTable->id) {
            $this->selectedTable->refresh();
        }
    }

    public function onFiltersUpdated($statusFiltersCount)
    {
        $this->statusFiltersCount = $statusFiltersCount;
    }

    public function backToTables()
    {
        return redirect()->route('tables');
    }

    public function openFilterModal()
    {
        $this->dispatch('open-filter-modal');
    }

    public function openStatusModal()
    {
        $this->dispatch('open-status-modal');
    }

    public function render()
    {
        return view('livewire.order.order-header');
    }
}
