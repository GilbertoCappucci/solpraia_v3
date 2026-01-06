<?php

namespace App\Livewire\Order;

use Livewire\Component;
use Livewire\Attributes\Reactive;

class OrderList extends Component
{

    public $listOrders;
    
    #[Reactive]
    public $checkTotal = 0;
    
    #[Reactive]
    public $statusFilters = [];
    
    public $timeLimits = [];
    public $userId;
    public $selectedOrderIds = [];
    public $selectedMeta = null;

    public function mount($listOrders, $checkTotal = 0, $statusFilters = [], $timeLimits = [], $userId = null)
    {
        $this->listOrders = $listOrders;
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

    public function toggleSelection($orderId, $status, $isPaid, $productId)
    {

        dd( $orderId, $status, $isPaid, $productId);

        // If already selected, unselect
        if (in_array($orderId, $this->selectedOrderIds)) {
            $this->selectedOrderIds = array_values(array_diff($this->selectedOrderIds, [$orderId]));
            if (empty($this->selectedOrderIds)) {
                $this->selectedMeta = null;
            }
            return;
        }

        // If none selected, accept and store meta
        if (empty($this->selectedOrderIds)) {
            $this->selectedOrderIds[] = $orderId;
            $this->selectedMeta = ['status' => $status, 'is_paid' => (bool) $isPaid, 'product_id' => $productId];
            return;
        }

        // Otherwise enforce same status and same is_paid
        if ($this->selectedMeta && ($status !== $this->selectedMeta['status'] || (bool)$isPaid !== (bool)$this->selectedMeta['is_paid'])) {
            session()->flash('error', 'Selecione apenas pedidos com mesmo status e mesmo estado de pagamento.');
            return;
        }

        $this->selectedOrderIds[] = $orderId;
    }

    public function openSelectedGroupActions()
    {
        if (empty($this->selectedOrderIds)) {
            session()->flash('error', 'Nenhum pedido selecionado.');
            return;
        }

        $orders = \App\Models\Order::with('product', 'currentStatusHistory')
            ->whereIn('id', $this->selectedOrderIds)
            ->get();

        $totalQuantity = $orders->sum('quantity');
        $totalPrice = $orders->sum(fn($o) => $o->quantity * $o->price);

        // Open the standard order-group-modal (shows orders for the product + status)
        $productId = $this->selectedMeta['product_id'] ?? $orders->first()?->product_id;
        $status = $this->selectedMeta['status'] ?? ($orders->first()?->status ?? 'pending');

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
