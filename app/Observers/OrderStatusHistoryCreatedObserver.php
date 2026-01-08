<?php

namespace App\Observers;

use App\Events\OrderStatusHistoryCreatedEvent;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\Event;

class OrderStatusHistoryCreatedObserver
{
    public function created(OrderStatusHistory $orderStatusHistory): void
    {
        logger('🚀 OrderStatusHistoryCreatedObserver: OrderStatusHistory created', ['id' => $orderStatusHistory->id, 'order_id' => $orderStatusHistory->order_id, 'status' => $orderStatusHistory->status]);
        
        Event::dispatch(new OrderStatusHistoryCreatedEvent($orderStatusHistory));
    }
}
