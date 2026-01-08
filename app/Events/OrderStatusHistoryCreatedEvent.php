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

class OrderStatusHistoryCreatedEvent implements ShouldBroadcastNow
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
        logger('📡 Broadcasting OrderStatusHistoryCreatedEvent', ['orderStatusHistoryId' => $this->orderStatusHistory->id, 'userId' => $this->orderStatusHistory->order->user_id]);

        return [
            new PrivateChannel('order-status-history-created.' . $this->orderStatusHistory->order->user_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'orderStatusHistoryId' => $this->orderStatusHistory->id,
            'orderId' => $this->orderStatusHistory->order_id,
            'tableId' => $this->orderStatusHistory->order->check->table->id,
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.status.history.created';
    }
}
