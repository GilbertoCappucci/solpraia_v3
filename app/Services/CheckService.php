<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Models\Check;

class CheckService
{
    /**
     * Recalcula o total de um check específico
     * Considera apenas pedidos que NÃO estão em PENDING nem CANCELED
     */
    public function recalculateCheckTotal(Check $check): void
    {
        // Busca todos os pedidos do check que NÃO foram cancelados nem estão aguardando
        $activeOrders = $check->orders()
            ->with(['currentStatusHistory', 'product'])
            ->get()
            ->filter(function($order) {
                return $order->status !== OrderStatusEnum::CANCELED->value
                    && $order->status !== OrderStatusEnum::PENDING->value;
            });
        
        // Recalcula o total baseado em quantidade * preço do produto
        $newTotal = $activeOrders->sum(function($order) {
            return $order->quantity * $order->product->price;
        });
        
        // Atualiza o total do check se mudou
        if ($check->total != $newTotal) {
            $check->update(['total' => $newTotal]);
        }
    }
}
