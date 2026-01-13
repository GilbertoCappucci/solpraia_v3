<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Enums\CheckStatusEnum;
use App\Models\Check;
use App\Models\Order;
use App\Models\MenuItem;
use App\Services\Order\OrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CheckService
{
    /**
     * Recalcula o total de um check específico
     * Considera apenas pedidos que NÃO estão em PENDING nem CANCELED
     */
    /**
     * Calcula o total do check baseado nas regras de negócio (ignora Pending/Canceled e Pagos)
     * Não persiste no banco.
     */
    public function calculateTotal(Check $check): float
    {
        // Busca todos os pedidos do check (se já estiverem carregados, usa a coleção, senão carrega)
        $orders = $check->relationLoaded('orders') ? $check->orders : $check->orders()->with(['currentStatusHistory', 'product'])->get();
        
        // Filtra pedidos ativos (não cancelados, não aguardando e NÃO PAGOS)
        $activeOrders = $orders->filter(function ($order) {
            return $order->status !== OrderStatusEnum::CANCELED->value
                && !$order->is_paid;  // ← NOVO: Ignora pedidos já pagos
        });

        // Retorna a soma
        return $activeOrders->sum(function ($order) {
            return $order->quantity * $order->price;
        });
    }

    public function calculateTotalOrders($orders):float {

        $total = Order::whereIn('id', $orders)->get()->sum(function ($order) {
            return $order->currentStatusHistory->quantity * $order->currentStatusHistory->price;
        });

        return $total;
    }

    public static function updateCheckTotalAfterOrderPayment(int $checkId): void
    {
        $check = Check::find($checkId);
        
        if ($check) {
            $service = new CheckService();
            $service->recalculateCheckTotal($check);
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

        if(in_array($newStatus, [CheckStatusEnum::CANCELED->value, CheckStatusEnum::PAID->value, CheckStatusEnum::CLOSED->value])) {

            $check->update([
                'status' => $newStatus,
                'closed_at' => now()
            ]);

            Check::create([
                'table_id' => $check->table_id,
                'admin_id' => $check->admin_id,
                'status' => CheckStatusEnum::OPEN,
                'opened_at' => now(),
                'total' => 0,
            ]);

            Session::flash('success', 'Status do check atualizado com sucesso!');
            return ['success' => [true, [
                'check' => $check,
            ]], 'errors' => []];
        }

        $check->update(['status' => $newStatus]);

        Session::flash('success', 'Status do check atualizado com sucesso!');
        return ['success' => [true, [
            'check' => $check,
        ]], 'errors' => []];
    }

    /**
     * Retorna qual status pode ser atribuído a um check com base no status atual
     */

    public function getAllowedCheckStatuses(string $currentStatus, ?Check $check = null): array
    {
        $allowedStatuses = [];

        //Caso o Check esteja com total 0, permite cancelar diretamente
        if ($check->total == 0) {
            return [CheckStatusEnum::CANCELED->value];
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
