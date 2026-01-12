<?php

namespace App\Observers;

use App\Events\OrderStatusHistoryCreatedEvent;
use App\Events\OrderStatusHistoryUpdatedEvent;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\Event;

class OrderStatusHistoryObserver
{
    public function created(OrderStatusHistory $orderStatusHistory): void
    {
        logger('🚀 OrderStatusHistoryCreatedObserver: OrderStatusHistory created', ['id' => $orderStatusHistory->id, 'order_id' => $orderStatusHistory->order_id, 'check_id' => $orderStatusHistory->check_id, 'status' => $orderStatusHistory->status]);
        
        Event::dispatch(new OrderStatusHistoryCreatedEvent($orderStatusHistory));
    }

    public function updated(OrderStatusHistory $orderStatusHistory): void
    {
        logger('🚀 OrderStatusHistoryUpdatedObserver: OrderStatusHistory updated', ['id' => $orderStatusHistory->id, 'order_id' => $orderStatusHistory->order_id, 'check_id' => $orderStatusHistory->order->check->id, 'status' => $orderStatusHistory->to_status]);

        Event::dispatch(new OrderStatusHistoryUpdatedEvent($orderStatusHistory));
    }   
}
