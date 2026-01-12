<?php

namespace App\Events;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class OrderStatusHistoryUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $orderStatusHistory;

    /**
     * Create a new event instance.
     */
    public function __construct(OrderStatusHistory $orderStatusHistory)
    {
        $this->orderStatusHistory = $orderStatusHistory;
    }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        logger('📡 Broadcasting OrderStatusHistoryUpdatedEvent', [
            'orderStatusHistoryId' => $this->orderStatusHistory->id, 'adminId' => $this->orderStatusHistory->order->admin_id,
            'checkId' => $this->orderStatusHistory->order->check->id,
        ]);

        return [
            new PrivateChannel('order-status-history-updated.admin.' . $this->orderStatusHistory->order->admin_id . '.check.' . $this->orderStatusHistory->order->check->id),
        ];
    }

    public function broadcastWith(): array
    {
        $userId = Auth::user()->id;
        $adminId = $this->orderStatusHistory->order->admin_id;
        
        return [
            
            'orderStatusHistoryId' => $this->orderStatusHistory->id,
            'orderId' => $this->orderStatusHistory->order_id,
            'tableId' => $this->orderStatusHistory->order->check->table->id,
            'adminId' => $adminId,
            'userId' => $userId,
            'checkId' => $this->orderStatusHistory->order->check->id,
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.status.history.updated';
    }
}
