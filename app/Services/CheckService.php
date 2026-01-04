<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Enums\CheckStatusEnum;
use App\Models\Check;
use App\Models\Order;
use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;

class CheckService
{
    /**
     * Recalcula o total de um check específico
     * Considera apenas pedidos que NÃO estão em PENDING nem CANCELED
     */
    /**
     * Calcula o total do check baseado nas regras de negócio (ignora Pending/Canceled)
     * Não persiste no banco.
     */
    public function calculateTotal(Check $check): float
    {
        // Busca todos os pedidos do check (se já estiverem carregados, usa a coleção, senão carrega)
        $orders = $check->relationLoaded('orders') ? $check->orders : $check->orders()->with(['currentStatusHistory', 'product'])->get();

        // Filtra pedidos ativos (não cancelados nem aguardando)
        $activeOrders = $orders->filter(function ($order) {
            return $order->status !== OrderStatusEnum::CANCELED->value
                && $order->status !== OrderStatusEnum::PENDING->value;
        });

        // Retorna a soma
        return $activeOrders->sum(function ($order) {
            return $order->quantity * $order->price;
        });
    }

    /**
     * Recalcula e persiste o total de um check específico
     */
    public function recalculateCheckTotal(Check $check): void
    {
        $newTotal = $this->calculateTotal($check);

        // Atualiza o total do check se mudou (Observer vai disparar o evento)
        if ($check->total != $newTotal) {
            $check->update(['total' => $newTotal]);
        }
    }

    /**
     * Valida e atualiza o status de um check
     * Retorna array com 'success' (bool) e 'errors' (array)
     */
    public function validateAndUpdateCheckStatus(Check $check, string $newStatus): array
    {
        $errors = [];

        // Se está tentando cancelar, valida se o total é zero
        if ($newStatus === CheckStatusEnum::CANCELED->value) {
            if ($check->total > 0) {
                $errors[] = 'Não é possível cancelar o check com valor pendente. Cancele todos os pedidos primeiro.';
            }
        }
        // Valida pedidos completos ao fechar (Open → Closed)
        elseif (
            $newStatus === CheckStatusEnum::CLOSED->value &&
            $check->status === CheckStatusEnum::OPEN->value
        ) {

            $orders = $check->orders()->with('currentStatusHistory')->get();

            // Filtra pedidos ativos (não cancelados)
            $activeOrders = $orders->filter(function ($order) {
                return $order->status !== OrderStatusEnum::CANCELED->value;
            });

            // Verifica se todos os pedidos ativos estão completos
            $hasIncompleteOrders = $activeOrders->filter(function ($order) {
                return $order->status !== OrderStatusEnum::COMPLETED->value;
            })->isNotEmpty();

            if ($hasIncompleteOrders) {
                $errors[] = 'Não é possível fechar o check. Todos os pedidos precisam estar entregues (Pronto).';
            }

            // Validação: Não pode fechar conta sem pedidos
            if ($check->total <= 0) {
                $errors[] = 'Não é possível fechar conta sem pedidos.';
            }
        }

        // Validação: Não pode marcar como PAID sem estar CLOSED
        if ($newStatus === CheckStatusEnum::PAID->value) {
            if ($check->status !== CheckStatusEnum::CLOSED->value) {
                $errors[] = 'A conta precisa estar "Fechada" antes de ser marcada como "Paga".';
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Atualiza o status do check (Observer vai disparar o evento)
        $check->update(['status' => $newStatus]);

        // Se foi marcado como Closed ou Paid, atualiza closed_at
        if (in_array($newStatus, [CheckStatusEnum::CLOSED->value, CheckStatusEnum::PAID->value])) {
            $check->update(['closed_at' => now()]);
        }

        return ['success' => true, 'errors' => []];
    }

    /**
     * Retorna qual status pode ser atribuído a um check com base no status atual
     */

    public function getAllowedCheckStatuses(string $currentStatus, ?Check $check = null): array
    {
        $allowedStatuses = [];

        // Se não houver check, não há transições de status de comanda possíveis
        if (!$check) {
            return $allowedStatuses;
        }

        // Se houver check, valida se todos os pedidos estão finalizados
        if (!OrderService::areAllOrdersCompletedOrCanceled($check)) {
            return $allowedStatuses;
        }

        switch ($currentStatus) {
            case CheckStatusEnum::OPEN->value:
                $allowedStatuses = [
                    CheckStatusEnum::CLOSED->value,
                    CheckStatusEnum::CANCELED->value,
                ];
                break;
            case CheckStatusEnum::CLOSED->value:
                $allowedStatuses = [
                    CheckStatusEnum::OPEN->value,
                    CheckStatusEnum::PAID->value,
                ];
                break;
            case CheckStatusEnum::PAID->value:
            case CheckStatusEnum::CANCELED->value:
                // Nenhum status permitido
                $allowedStatuses = [];
                break;
        }

        return $allowedStatuses;
    }

    public function pixPayload(Check $check): string
    {
        return $check->pix_payload;
    }
}
