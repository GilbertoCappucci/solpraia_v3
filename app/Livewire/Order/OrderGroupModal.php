<?php

namespace App\Livewire\Order;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OrderGroupModal extends Component
{
    public $show = false;
    public $groupOrders = [];
    public $selectedOrderIds = [];
    public $currentCheck;
    public $buttonPayVisible = false;

    public function getListeners()
    {
        $adminId = Auth::user()->admin_id ?? null;

        return [
            'open-group-modal' => 'openModal',
            "echo-private:order-status-history-created.admin.{$adminId}.check.{$this->currentCheck->id},.order.status.history.updated" => 'closeModal',
            "echo-private:order-status-history-updated.admin.{$adminId}.check.{$this->currentCheck->id},.order.status.history.updated" => 'handleQuantityUpdated',
        ];
    }

    public function handleQuantityUpdated($payload)
    {
        if ($payload['userId'] !== Auth::user()->id) {
            $this->dispatch('flash', message: 'A quantidade foi alterada por outro operador.'
        ); 
            $this->closeModal();
        }
    }

    public function increaseOrderQuantity($orderId)
    {
        $orderHistory = OrderStatusHistory::where('order_id', $orderId)
            ->latest('changed_at')
            ->first();

        $orderHistory->update([
            'quantity' => $orderHistory->quantity + 1,
        ]);

        $this->openModal($this->selectedOrderIds);
        $this->dispatch('refresh-orders-list');
    }

    public function decreaseOrderQuantity($orderId)
    {
        $orderHistory = OrderStatusHistory::where('order_id', $orderId)
            ->latest('changed_at')
            ->first();
            
        if ($orderHistory->quantity > 1) {
            $orderHistory->update([
                'quantity' => $orderHistory->quantity - 1,
            ]);
        }

        $this->openModal($this->selectedOrderIds);
        $this->dispatch('refresh-orders-list');
    }

    public function openCancelOrdersConfirmationModal()
    {
        $this->show = false;
        $this->dispatch('open-cancel-modal', $this->selectedOrderIds);
    }

    public function updateGroupStatus($newStatus)
    {
        $allowedStatuses = [
            OrderStatusEnum::PENDING->value,
            OrderStatusEnum::IN_PRODUCTION->value,
            OrderStatusEnum::IN_TRANSIT->value,
            OrderStatusEnum::COMPLETED->value,
            OrderStatusEnum::CANCELED->value,
            OrderStatusEnum::DELAYED->value,
        ];

        if (!in_array($newStatus, $allowedStatuses)) {
            session()->flash('error', 'Status inválido selecionado.');
            return;
        }

        foreach ($this->selectedOrderIds as $orderId) {
            $order = Order::find($orderId);
            OrderStatusHistory::create([
                'order_id' => $orderId,
                'quantity' => $order->quantity,
                'price' => $order->price,
                'from_status' => Order::find($orderId)->status,
                'to_status' => $newStatus,
                'changed_at' => now(),
            ]);
        }

        session()->flash('success', 'Status dos pedidos atualizados com sucesso.');
        $this->closeModal();
        $this->dispatch('refresh-orders-list');
    }

    public function payOrders()
    {
        session([
            'pay_orders' => $this->selectedOrderIds
        ]);
        
        $this->closeModal();
        redirect()->route('pay.orders');
    }   

    public function openModal($selectedOrderIds)
    {

        $this->selectedOrderIds = $selectedOrderIds;
        $this->groupOrders = \App\Models\Order::with('product')
            ->whereIn('id', $this->selectedOrderIds)
            ->get()
            ->toArray();

        //Verifica se alguma orden já foi paga para esconder o botão de pagar
        $somePaid = collect($this->groupOrders)->some(fn($order) => $order['is_paid'] == true);

        if($somePaid){
            $this->buttonPayVisible = false;
        } else {
            $this->buttonPayVisible = true;
        }

        //dd( $this->groupOrders);
        $this->show = true;
    }

    public function closeModal()
    {
        logger("Closing OrderGroupModal and resetting state");
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
