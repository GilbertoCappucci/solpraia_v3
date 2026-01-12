<?php

namespace App\Services\Order;

use App\Enums\CheckStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\TableStatusEnum;
use App\Models\Check;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Table;
use App\Services\CheckService;
use Illuminate\Support\Facades\DB;

/**
 * Serviço responsável pela gestão de status de pedidos e comandas
 * - Atualizar status de mesas e checks com validações
 * - Atualizar status de pedidos individuais
 * - Suportar movimentação parcial de quantidades (split)
 */
class OrderStatusService
{
    public function __construct(
        protected CheckService $checkService
    ) {}

    /**
     * Valida e atualiza status da mesa e check
     * Aplica regras de negócio para transições de status
     */
    public function updateStatuses(
        Table $table,
        ?Check $check,
        ?string $newTableStatus,
        ?string $newCheckStatus
    ): array {
        $errors = [];

        // Validação: Não pode mudar status da mesa se houver check em aberto ou em pagamento
        if ($newTableStatus && $newTableStatus !== $table->status) {
            if ($check) {
                $checkStatus = $check->status;

                // Bloqueia mudança de status se check está Open ou Closed
                if (in_array($checkStatus, [
                    CheckStatusEnum::OPEN->value,
                    CheckStatusEnum::CLOSED->value
                ])) {
                    $statusLabel = match ($checkStatus) {
                        CheckStatusEnum::OPEN->value => 'aberto',
                        CheckStatusEnum::CLOSED->value => 'fechado (aguardando pagamento)',
                        default => 'em andamento'
                    };
                    $errors[] = "Não é possível alterar o status da mesa. Há um check {$statusLabel}.";
                }
            }

            // Validação específica para status CLOSE: só pode fechar mesa sem check ativo
            if ($newTableStatus === TableStatusEnum::CLOSE->value && $check) {
                $errors[] = "Não é possível fechar a mesa. Finalize ou cancele o check antes de fechar a mesa fisicamente.";
            }
        }

        // Validação do check usando método centralizado do CheckService
        $checkWasUpdated = false;
        if ($newCheckStatus && $check && $newCheckStatus !== $check->status) {
            $checkValidation = $this->checkService->validateAndUpdateCheckStatus($check, $newCheckStatus);

            if (!$checkValidation['success']) {
                $errors = array_merge($errors, $checkValidation['errors']);
            } else {
                $checkWasUpdated = true;
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Atualiza status da mesa
        if ($newTableStatus && $newTableStatus !== $table->status) {
            $table->update(['status' => $newTableStatus]);
        }

        // Se o check foi atualizado com sucesso e foi marcado como PAID, coloca mesa em RELEASING
        // Se foi CANCELED, libera direto para FREE
        if ($checkWasUpdated) {
            if ($newCheckStatus === CheckStatusEnum::PAID->value) {
                $table->update(['status' => TableStatusEnum::RELEASING]);
            } elseif ($newCheckStatus === CheckStatusEnum::CANCELED->value) {
                $table->update(['status' => TableStatusEnum::FREE]);
            }
        }

        return ['success' => true];
    }

    /**
     * Atualiza status de um pedido com suporte a divisão (split)
     * 
     * Se qtyToMove < quantity atual, o pedido é DIVIDIDO:
     * - Pedido original mantém (quantity - qtyToMove) com status antigo
     * - Novo pedido recebe qtyToMove com novo status
     * 
     * @param int $orderId ID do pedido
     * @param string $newStatus Novo status do pedido
     * @param int $qtyToMove Quantidade a mover (0 = move tudo)
     */
    public function updateOrderStatus(int $orderId, string $newStatus, int $qtyToMove = 0): array
    {
        return DB::transaction(function () use ($orderId, $newStatus, $qtyToMove) {
            $order = Order::with(['currentStatusHistory', 'check'])->lockForUpdate()->find($orderId);

            if (!$order) {
                return ['success' => false, 'message' => 'Pedido não encontrado.'];
            }

            $currentQty = $order->quantity;
            $oldStatus = $order->status;

            // Move tudo se qtyToMove for 0 ou maior/igual à quantidade atual
            if ($qtyToMove <= 0 || $qtyToMove >= $currentQty) {
                $this->createStatusHistory($order->id, $oldStatus, $newStatus, $order->price, $order->quantity);
            } else {
                // MOVIMENTO PARCIAL -> DIVISÃO (SPLIT)
                $this->splitOrder($order, $oldStatus, $newStatus, $currentQty, $qtyToMove);
            }

            // Recalcula check
            if ($order->check) {
                $this->checkService->recalculateCheckTotal($order->check);
            }

            return ['success' => true];
        });
    }

    /**
     * Divide um pedido em dois: original com quantidade reduzida, novo com novo status
     */
    protected function splitOrder(Order $order, string $oldStatus, string $newStatus, int $currentQty, int $qtyToMove): void
    {
        // 1. Reduz quantidade do pedido original (mantém status)
        $this->createStatusHistory(
            $order->id,
            $oldStatus,
            $oldStatus,
            $order->price,
            $currentQty - $qtyToMove
        );

        // 2. Cria novo pedido com a quantidade movida
        $newOrder = Order::create([
            'admin_id' => $order->admin_id,
            'check_id' => $order->check_id,
            'product_id' => $order->product_id,
        ]);

        // 3. Registra histórico do novo pedido com novo status
        $this->createStatusHistory(
            $newOrder->id,
            $oldStatus,
            $newStatus,
            $order->price,
            $qtyToMove
        );
    }

    /**
     * Cria registro de histórico de status
     */
    protected function createStatusHistory(
        int $orderId,
        string $fromStatus,
        string $toStatus,
        float $price,
        int $quantity
    ): void {
        OrderStatusHistory::create([
            'order_id' => $orderId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'price' => $price,
            'quantity' => $quantity,
            'changed_at' => now(),
        ]);
    }
}
