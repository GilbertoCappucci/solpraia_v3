<?php

namespace App\Livewire\Order;

use App\Services\Order\OrderService;
use Livewire\Component;

class OrderCancelModal extends Component
{
    public $show = false;
    public $orderToCancel = null;
    public $orderToCancelData = null;
    
    protected $orderService;

    public function boot(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function getListeners()
    {
        return [
            'open-cancel-modal' => 'openModal',
        ];
    }

    public function openModal($orderId)
    {
        $this->orderToCancel = $orderId;

        // Busca dados do pedido para exibir no modal
        $order = \App\Models\Order::with('product')->find($orderId);
        if ($order) {
            $this->orderToCancelData = [
                'id' => $order->id,
                'product_name' => $order->product->name,
                'quantity' => $order->quantity,
                'price' => $order->price,
            ];
        }

        $this->show = true;
    }

    public function closeModal()
    {
        $this->show = false;
        $this->orderToCancel = null;
        $this->orderToCancelData = null;
    }

    public function confirmCancelOrder($qtyToCancel = 0)
    {
        if (!$this->orderToCancel) {
            return;
        }

        // Se não especificou quantidade, remove TUDO
        if ($qtyToCancel == 0 && isset($this->orderToCancelData['quantity'])) {
            $qtyToCancel = $this->orderToCancelData['quantity'];
        }

        $result = $this->orderService->cancelOrder($this->orderToCancel, $qtyToCancel);

        if (!$result['success']) {
            session()->flash('error', $result['message']);
            $this->closeModal();
            return;
        }

        session()->flash('success', $result['message']);
        $this->closeModal();
        $this->dispatch('refresh-parent');
    }

    public function render()
    {
        return view('livewire.order.order-cancel-modal');
    }
}
