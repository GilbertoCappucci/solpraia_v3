<?php

namespace App\Services\Payment;

use App\Services\CheckService;

class PaymentService
{
    public function processOrderPayment($orderId)
    {
        $order = \App\Models\Order::find($orderId);
        if (!$order) {
            throw new \Exception('Pedido não encontrado.');
        }

        $order->is_paid = true;
        $order->save();

        CheckService::updateCheckTotalAfterOrderPayment($order->check_id);

    }

}