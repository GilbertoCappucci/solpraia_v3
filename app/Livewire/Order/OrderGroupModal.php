<?php

namespace App\Livewire\Order;

use Livewire\Component;

class OrderGroupModal extends Component
{
    public $show = false;
    public $groupOrders = [];
    public $selectedOrderIds = [];
    public $currentCheck;

    public function getListeners()
    {
        return [
            'open-group-modal' => 'openModal',
        ];
    }

    public function openModal($selectedOrderIds)
    {

        $this->selectedOrderIds = $selectedOrderIds;
        $this->groupOrders = \App\Models\Order::with('product')
            ->whereIn('id', $this->selectedOrderIds)
            ->get()
            ->toArray();
        //dd( $this->groupOrders);
        $this->show = true;
    }

    public function closeModal()
    {
        $this->show = false;
        $this->groupOrders = [];
        $this->selectedOrderIds = [];
    }

    public function toggleOrderSelection($orderId)
    {
        if (in_array($orderId, $this->selectedOrderIds)) {
            $this->selectedOrderIds = array_values(array_diff($this->selectedOrderIds, [$orderId]));
        } else {
            $this->selectedOrderIds[] = $orderId;
        }
    }

    public function openDetailsFromGroup($orderId)
    {
        $this->closeModal();
        $this->dispatch('open-details-modal', orderId: $orderId);
    }

    public function openGroupActionsModal()
    {
        if (empty($this->selectedOrderIds)) {
            session()->flash('error', 'Selecione ao menos um pedido.');
            return;
        }

        $selectedOrders = collect($this->groupOrders)->whereIn('id', $this->selectedOrderIds);
        $firstOrder = $selectedOrders->first();

        $groupActionData = [
            'order_ids' => $this->selectedOrderIds,
            'count' => count($this->selectedOrderIds),
            'total_quantity' => $selectedOrders->sum('quantity'),
            'product_name' => $firstOrder['product']['name'] ?? '',
            'status' => $firstOrder['status'] ?? 'pending',
            'total_price' => $selectedOrders->sum(fn($o) => $o['quantity'] * $o['price']),
        ];

        $this->dispatch('open-group-actions-modal', groupActionData: $groupActionData);
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.order.order-group-modal');
    }
}
