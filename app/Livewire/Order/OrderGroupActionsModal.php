<?php

namespace App\Livewire\Order;

use App\Services\Order\OrderService;
use Livewire\Component;

class OrderGroupActionsModal extends Component
{
    public $show = false;
    public $groupActionData = null;
    public $currentCheck;
    
    protected $orderService;

    public function boot(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function getListeners()
    {
        return [
            'open-group-actions-modal' => 'openModal',
        ];
    }

    public function openModal($groupActionData)
    {
        $this->groupActionData = $groupActionData;
        $this->show = true;
    }

    public function closeModal()
    {
        $this->show = false;
        $this->groupActionData = null;
    }

    public function updateGroupStatus($newStatus)
    {
        if (!$this->groupActionData || !$this->currentCheck || $this->currentCheck->status !== 'Open') {
            session()->flash('error', 'Não é possível alterar o status neste momento.');
            return;
        }

        $success = 0;
        $errors = [];

        foreach ($this->groupActionData['order_ids'] as $orderId) {
            $result = $this->orderService->updateOrderStatus($orderId, $newStatus, 0);
            if ($result['success']) {
                $success++;
            } else {
                $errors[] = $result['message'];
            }
        }

        if ($success > 0) {
            session()->flash('success', "$success pedido(s) atualizado(s) com sucesso!");
        }

        if (!empty($errors)) {
            session()->flash('error', implode(' ', array_unique($errors)));
        }

        $this->closeModal();
        $this->dispatch('refresh-parent');
    }

    public function cancelGroupOrders()
    {
        if (!$this->groupActionData) {
            return;
        }

        $success = 0;
        $errors = [];

        foreach ($this->groupActionData['order_ids'] as $orderId) {
            $order = \App\Models\Order::find($orderId);
            if ($order) {
                $result = $this->orderService->cancelOrder($orderId, $order->quantity);
                if ($result['success']) {
                    $success++;
                } else {
                    $errors[] = $result['message'];
                }
            }
        }

        if ($success > 0) {
            session()->flash('success', "$success pedido(s) cancelado(s) com sucesso!");
        }

        if (!empty($errors)) {
            session()->flash('error', implode(' ', array_unique($errors)));
        }

        $this->closeModal();
        $this->dispatch('refresh-parent');
    }

    public function render()
    {
        return view('livewire.order.order-group-actions-modal');
    }
}
