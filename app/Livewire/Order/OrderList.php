<?php

namespace App\Livewire\Order;

use App\Models\User;
use App\Services\GlobalSettingService;
use Livewire\Component;
use Livewire\Attributes\Reactive;

class OrderList extends Component
{

    #[Reactive]
    public $listOrders;
    
    #[Reactive]
    public $checkTotal = 0;
    
    #[Reactive]
    public $statusFilters = [];
    
    #[Reactive]
    public $timeLimits = [];

    public $userId;
    public $selectedOrderIds = [];
    public $selectedMeta = null;
    public $globalSettingsService;

    public function mount($listOrders, $checkTotal = 0, $statusFilters = [], $timeLimits = [], $userId = null)
    {

        $this->listOrders = $listOrders;
        $this->checkTotal = $checkTotal;
        $this->statusFilters = $statusFilters;
        $this->userId = $userId;

    }

    public function getListeners()
    {
        $listeners = [
            'filters-updated' => 'onFiltersUpdated',
        ];

        if ($this->userId) {
            $listeners["echo-private:tables-updated.{$this->userId},.check.updated"] = 'onCheckUpdated';
        }

        return $listeners;
    }

    public function onCheckUpdated($data)
    {
        $this->dispatch('refresh-parent');
    }

    public function onFiltersUpdated()
    {
        $this->dispatch('refresh-parent');
    }

    public function toggleSelection($orderId, $status, $isPaid, $productId)
    {
        //Verifica se já está selecionado, retira da seleção
        if(in_array($orderId, $this->selectedOrderIds)) {
            $this->selectedOrderIds = array_filter($this->selectedOrderIds, fn($id) => $id !== $orderId);
            $this->dispatch('selected-order-list-orders', $this->selectedOrderIds);
            return;
        }

        $this->selectedOrderIds[] = $orderId;
        $this->dispatch('selected-order-list-orders', $this->selectedOrderIds);
    }

    public function openSelectedGroupActions()
    {
        dd('openSelectedGroupActions');
        $this->dispatch('open-group-modal', productId: $productId, status: $status);
        $this->clearSelection();
    }

    public function clearSelection()
    {
        $this->selectedOrderIds = [];
        $this->selectedMeta = null;
    }

    public function openDetailsModal($orderId)
    {
        $this->dispatch('open-details-modal', orderId: $orderId);
    }

    public function openGroupModal($productId, $status)
    {
        $this->dispatch('open-group-modal', productId: $productId, status: $status);
    }

    public function render()
    {
        return view('livewire.order.order-list');
    }
}
