<?php

namespace App\Services\Order;

use App\Enums\CheckStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Check;
use App\Models\Order;

/**
 * Serviço responsável por validações de negócio relacionadas a pedidos
 * - Verificar pedidos pendentes
 * - Validar status de comandas
 * - Verificar conclusão de pedidos
 */
class OrderValidationService
{
    /**
     * Verifica se existe algum pedido aguardando em um check
     * 
     * @param Check $check
     * @return bool
     */
    public function hasPendingOrders(Check $check): bool
    {
        return $check->orders()
            ->where(function ($query) {
                // Pedidos sem histórico são considerados 'pending'
                $query->whereDoesntHave('statusHistory')
                    // Ou pedidos cujo último histórico é 'pending'
                    ->orWhereHas('currentStatusHistory', function ($q) {
                        $q->where('to_status', OrderStatusEnum::PENDING->value);
                    });
            })
            ->exists();
    }

    /**
     * Verifica se todos os pedidos foram entregues ou cancelados
     * Retorna true se NÃO existir nenhum pedido incompleto
     * 
     * @param Check $check
     * @return bool
     */
    public function areAllOrdersCompletedOrCanceled(Check $check): bool
    {
        $hasIncompleteOrders = $check->orders()
            ->where(function ($query) {
                // Um pedido é considerado "incompleto" se:
                // 1. Não tem histórico (é PENDING por padrão)
                $query->whereDoesntHave('statusHistory')
                    // 2. OU o status atual NÃO é COMPLETED nem CANCELED
                    ->orWhereHas('currentStatusHistory', function ($q) {
                        $q->whereNotIn('to_status', [
                            OrderStatusEnum::COMPLETED->value,
                            OrderStatusEnum::CANCELED->value
                        ]);
                    });
            })
            ->exists();

        return !$hasIncompleteOrders;
    }

    /**
     * Valida se a quantidade é válida
     * 
     * @param int $quantity
     * @return array ['valid' => bool, 'message' => string|null]
     */
    public function validateQuantity(int $quantity): array
    {
        if ($quantity <= 0) {
            return ['valid' => false, 'message' => 'A quantidade deve ser maior que zero.'];
        }

        return ['valid' => true, 'message' => null];
    }

    /**
     * Valida se o status do pedido permite cancelamento
     * 
     * @param Order $order
     * @return array ['valid' => bool, 'message' => string|null]
     */
    public function canCancelOrder(Order $order): array
    {
        if ($order->status === OrderStatusEnum::CANCELED->value) {
            return ['valid' => false, 'message' => 'Este pedido já está cancelado.'];
        }

        return ['valid' => true, 'message' => null];
    }

    /**
     * Valida se o status do pedido permite duplicação
     * 
     * @param Order $order
     * @return array ['valid' => bool, 'message' => string|null]
     */
    public function canDuplicateOrder(Order $order): array
    {
        if ($order->status !== OrderStatusEnum::PENDING->value) {
            return ['valid' => false, 'message' => 'Apenas itens "Aguardando" podem ser aumentados.'];
        }

        return ['valid' => true, 'message' => null];
    }

    /**
     * Valida se o check está em um status válido para receber pedidos
     * 
     * @param Check $check
     * @return array ['valid' => bool, 'message' => string|null]
     */
    public function canAddOrdersToCheck(Check $check): array
    {
        if (!in_array($check->status, [CheckStatusEnum::OPEN->value, CheckStatusEnum::CLOSED->value])) {
            return [
                'valid' => false,
                'message' => 'A comanda não está em um status válido para receber pedidos.'
            ];
        }

        return ['valid' => true, 'message' => null];
    }
}
