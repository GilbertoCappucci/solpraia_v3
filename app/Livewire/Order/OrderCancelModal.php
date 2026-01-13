<?php

namespace App\Livewire\Order;

use App\Services\Order\OrderService;
use Illuminate\Support\Facades\Auth;
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
        $adminId = Auth::user()->admin_id ?? null;

        return [
            'open-cancel-modal' => 'openModal',
            'close-cancel-modal' => 'closeModal',
        ];
    }

    public function openModal($ordersId)
    {
        
        if(is_array($ordersId) || count($ordersId) > 1) {
            $orders = \App\Models\Order::with('product')->whereIn('id', $ordersId)->get();
            $this->orderToCancelData = $orders->map(fn($order) => 
                     [
                    'id' => $order->id,
                    'product_name' => $order->product->name, 
                    'quantity' => $order->quantity,
                    'price' => $order->price,
                ]);
        }else{
        
            $this->orderToCancel = $ordersId;

            $order = \App\Models\Order::with('product')->find($ordersId);
            if ($order) {
                $this->orderToCancelData[] = [
                    'id' => $order->id,
                    'product_name' => $order->product->name,
                    'quantity' => $order->quantity,
                    'price' => $order->price,
                ];
            }
        }

        $this->show = true;
    }

    public function closeModal()
    {
        $this->show = false;
        $this->orderToCancel = null;
        $this->orderToCancelData = null;
    }

    public function confirmCancelOrder()
    {

        foreach($this->orderToCancelData as $orderData) {
            $result = $this->orderService->cancelOrder($orderData['id'], $orderData['quantity']);

            if (!$result['success']) {
                session()->flash('error', $result['message']);
                $this->closeModal();
                return;
            }
        
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
