<?php

namespace App\Livewire\Order;

use App\Services\Order\OrderService;
use Livewire\Component;

class OrderDetailsModal extends Component
{
    public $show = false;
    public $orderDetails = null;
    public $currentCheck;
    
    protected $orderService;

    public function boot(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function getListeners()
    {
        return [
            'open-details-modal' => 'openModal',
        ];
    }

    public function payOrder($orderId)
    {
        redirect()->route('pay.order', ['orderId' => $orderId]);
    }

    public function openModal($orderId)
    {
        $order = \App\Models\Order::with('product')->find($orderId);

        if (!$order) {
            session()->flash('error', 'Pedido não encontrado.');
            return;
        }

        // Busca estoque disponível
        $stock = \App\Models\Stock::where('product_id', $order->product_id)->first();
        $availableStock = $stock ? $stock->quantity : 0;

        $this->orderDetails = [
            'id' => $order->id,
            'product_id' => $order->product_id,
            'product_name' => $order->product->name,
            'quantity' => $order->quantity,
            'price' => $order->price,
            'status' => $order->status,
            'created_at' => $order->created_at,
            'total' => $order->quantity * $order->price,
            'available_stock' => $availableStock,
            'is_paid' => $order->is_paid,
        ];

        $this->show = true;
    }

    public function closeModal()
    {
        $this->show = false;
        $this->orderDetails = null;
    }

    public function incrementQuantity()
    {
        if (!$this->orderDetails || !$this->currentCheck || $this->currentCheck->status !== 'Open') {
            session()->flash('error', 'Não é possível alterar a quantidade neste momento.');
            return;
        }

        if ($this->orderDetails['status'] !== 'pending') {
            session()->flash('error', 'Só é possível alterar a quantidade de pedidos no status "Aguardando".');
            return;
        }

        $result = $this->orderService->duplicatePendingOrder($this->orderDetails['id']);

        if (!$result['success']) {
            session()->flash('error', $result['message']);
            return;
        }

        session()->flash('success', 'Quantidade aumentada!');
        $this->dispatch('refresh-parent');

        // Recarrega os detalhes do pedido atualizado se ainda existir
        if ($this->orderDetails && \App\Models\Order::find($this->orderDetails['id'])) {
            $this->openModal($this->orderDetails['id']);
        } else {
            $this->closeModal();
        }
    }

    public function decrementQuantity()
    {
        if (!$this->orderDetails || !$this->currentCheck || $this->currentCheck->status !== 'Open') {
            session()->flash('error', 'Não é possível alterar a quantidade neste momento.');
            return;
        }

        if ($this->orderDetails['status'] !== 'pending') {
            session()->flash('error', 'Só é possível alterar a quantidade de pedidos no status "Aguardando".');
            return;
        }

        if ($this->orderDetails['quantity'] <= 1) {
            session()->flash('error', 'Use o botão cancelar para remover o último item.');
            return;
        }

        $result = $this->orderService->cancelOrder($this->orderDetails['id'], 1);

        if (!$result['success']) {
            session()->flash('error', $result['message']);
            return;
        }

        session()->flash('success', 'Quantidade reduzida!');
        $this->dispatch('refresh-parent');
        // Recarrega os detalhes do pedido atualizado se ainda existir
        if ($this->orderDetails && \App\Models\Order::find($this->orderDetails['id'])) {
            $this->openModal($this->orderDetails['id']);
        } else {
            $this->closeModal();
        }
    }

    public function updateOrderStatus($newStatus)
    {
        if (!$this->orderDetails || !$this->currentCheck || $this->currentCheck->status !== 'Open') {
            session()->flash('error', 'Não é possível alterar o status neste momento.');
            return;
        }

        $result = $this->orderService->updateOrderStatus($this->orderDetails['id'], $newStatus, 0);

        if (!$result['success']) {
            session()->flash('error', $result['message']);
            return;
        }

        session()->flash('success', 'Status atualizado!');
        $this->closeModal();
        $this->dispatch('refresh-parent');
    }

    public function cancelOrder()
    {
        if (!$this->orderDetails) {
            return;
        }

        $orderId = $this->orderDetails['id'];
        $this->closeModal();
        $this->dispatch('open-cancel-modal', orderId: $orderId);
    }

    public function render()
    {
        return view('livewire.order.order-details-modal');
    }
}
