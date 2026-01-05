<?php

namespace App\Services\Order;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Services\CheckService;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;

/**
 * Serviço responsável pelo cancelamento de pedidos
 * - Cancelamento individual com suporte a quantidade parcial
 * - Cancelamento em lote de múltiplos pedidos
 * - Devolução automática de estoque
 */
class OrderCancellationService
{
    public function __construct(
        protected StockService $stockService,
        protected CheckService $checkService
    ) {}

    /**
     * Cancela um pedido (completo ou parcialmente)
     * 
     * @param int $orderId ID do pedido
     * @param int $qtyToCancel Quantidade a cancelar (default 1)
     * @return array ['success' => bool, 'message' => string]
     */
    public function cancelOrder(int $orderId, int $qtyToCancel = 1): array
    {
        return DB::transaction(function () use ($orderId, $qtyToCancel) {
            $order = Order::with(['product', 'check'])->lockForUpdate()->findOrFail($orderId);

            // Valida se já está cancelado
            if ($order->status === OrderStatusEnum::CANCELED->value) {
                return ['success' => false, 'message' => 'Este pedido já está cancelado.'];
            }

            if ($qtyToCancel > $order->quantity) {
                return ['success' => false, 'message' => 'Quantidade a cancelar maior que a do pedido.'];
            }

            // Devolve estoque
            $this->stockService->increment($order->product_id, $qtyToCancel);

            // Cancela total ou parcial
            if ($qtyToCancel >= $order->quantity) {
                $this->cancelFullOrder($orderId, $order);
            } else {
                $this->cancelPartialOrder($orderId, $order, $qtyToCancel);
            }

            // Recalcula total do check
            if ($order->check) {
                $this->checkService->recalculateCheckTotal($order->check);
            }

            return ['success' => true, 'message' => 'Item removido com sucesso.'];
        });
    }

    /**
     * Cancela múltiplos pedidos de uma vez
     * Apenas pedidos em status PENDING podem ser cancelados
     * 
     * @param array $orderIds IDs dos pedidos
     * @return array ['success' => bool, 'message' => string]
     */
    public function cancelOrders(array $orderIds): array
    {
        return DB::transaction(function () use ($orderIds) {
            $count = 0;
            $check = null;

            foreach ($orderIds as $orderId) {
                $order = Order::with(['currentStatusHistory', 'check'])->find($orderId);

                if (!$order) continue;

                // Captura check do primeiro pedido para recalcular no final
                if (!$check && $order->check) {
                    $check = $order->check;
                }

                // Valida status PENDING
                if ($order->status !== OrderStatusEnum::PENDING->value) {
                    continue;
                }

                // Devolve estoque e cancela
                $this->stockService->increment($order->product_id, $order->quantity);
                $this->cancelFullOrder($orderId, $order);

                $count++;
            }

            if ($count === 0) {
                return [
                    'success' => false,
                    'message' => 'Nenhum pedido pôde ser cancelado (verifique se estão com status "Aguardando").'
                ];
            }

            // Recalcula total do check uma única vez
            if ($check) {
                $this->checkService->recalculateCheckTotal($check);
            }

            return [
                'success' => true,
                'message' => $count === 1
                    ? '1 item removido com sucesso!'
                    : "{$count} itens removidos com sucesso!"
            ];
        });
    }

    /**
     * Cancela o pedido completamente
     */
    protected function cancelFullOrder(int $orderId, Order $order): void
    {
        OrderStatusHistory::create([
            'order_id' => $orderId,
            'from_status' => $order->status,
            'to_status' => OrderStatusEnum::CANCELED->value,
            'price' => $order->price,
            'quantity' => $order->quantity,
            'changed_at' => now(),
        ]);
    }

    /**
     * Cancela apenas parte da quantidade do pedido
     */
    protected function cancelPartialOrder(int $orderId, Order $order, int $qtyToCancel): void
    {
        OrderStatusHistory::create([
            'order_id' => $orderId,
            'from_status' => $order->status,
            'to_status' => $order->status, // Mantém o mesmo status
            'price' => $order->price,
            'quantity' => $order->quantity - $qtyToCancel,
            'changed_at' => now(),
        ]);
    }
}
