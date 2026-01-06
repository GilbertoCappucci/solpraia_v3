<?php

namespace App\Livewire\Order;

use Livewire\Component;
use Livewire\Attributes\Reactive;

class OrderList extends Component
{
    #[Reactive]
    public $groupedOrders;
    
    #[Reactive]
    public $checkTotal = 0;
    
    #[Reactive]
    public $statusFilters = [];
    
    public $timeLimits = [];
    public $userId;

    public function mount($groupedOrders, $checkTotal = 0, $statusFilters = [], $timeLimits = [], $userId = null)
    {
        $this->groupedOrders = $groupedOrders;
        $this->checkTotal = $checkTotal;
        $this->statusFilters = $statusFilters;
        $this->timeLimits = $timeLimits;
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

    public function openDetailsModal($orderId)
    {
        $this->dispatch('open-details-modal', orderId: $orderId);
    }

    public function openGroupModal($productId, $status)
    {
        $this->dispatch('open-group-modal', productId: $productId, status: $status);
    }

    public function payOrder($productId, $status)
    {
        // Redireciona para tela de pagamento com productId e status para identificar o grupo
        return redirect()->route('payment', [
            'productId' => $productId,
            'status' => $status
        ]);
    }

    public function render()
    {
        return view('livewire.order.order-list');
    }
}
