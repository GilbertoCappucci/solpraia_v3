<?php

namespace App\Services\Order;

use App\Enums\CheckStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Check;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Services\CheckService;
use App\Services\StockService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Serviço responsável por operações especiais em pedidos
 * - Duplicar pedidos (incrementar quantidade)
 * - Agrupar pedidos por status
 * - Calcular estatísticas de grupos
 * - Merge de comandas (unir mesas)
 */
class OrderOperationsService
{
    public function __construct(
        protected StockService $stockService,
        protected CheckService $checkService
    ) {}

    /**
     * Duplica um pedido PENDING (incrementa quantidade)
     * 
     * @param int $orderId ID do pedido
     * @return array ['success' => bool, 'message' => string]
     */
    public function duplicatePendingOrder(int $orderId): array
    {
        return DB::transaction(function () use ($orderId) {
            $order = Order::lockForUpdate()->findOrFail($orderId);

            if ($order->status !== OrderStatusEnum::PENDING->value) {
                return ['success' => false, 'message' => 'Apenas itens "Aguardando" podem ser aumentados.'];
            }

            if (!$this->stockService->hasStock($order->product_id, 1)) {
                return ['success' => false, 'message' => 'Estoque insuficiente.'];
            }

            if (!$this->stockService->decrement($order->product_id, 1)) {
                return ['success' => false, 'message' => 'Erro ao atualizar estoque.'];
            }

            // Incrementa quantidade criando novo histórico
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $order->status,
                'to_status' => $order->status,
                'price' => $order->price,
                'quantity' => $order->quantity + 1,
                'changed_at' => now(),
            ]);

            $this->checkService->recalculateCheckTotal($order->check);

            return ['success' => true];
        });
    }

    /**
     * Busca pedidos ativos agrupados por status
     * 
     * @param Check|null $check
     * @return array ['pending' => Collection, 'inProduction' => Collection, 'inTransit' => Collection, 'completed' => Collection]
     */
    public function getActiveOrdersGrouped(?Check $check): array
    {
        if (!$check) {
            return [
                'pending' => collect(),
                'inProduction' => collect(),
                'inTransit' => collect(),
                'completed' => collect(),
            ];
        }

        // Busca todos os pedidos do check
        $allOrders = Order::where('check_id', $check->id)
            ->with(['product', 'currentStatusHistory'])
            ->get();

        // Filtra apenas os ativos
        $activeOrders = $allOrders->filter(function ($order) {
            return in_array($order->status, [
                OrderStatusEnum::PENDING->value,
                OrderStatusEnum::IN_PRODUCTION->value,
                OrderStatusEnum::IN_TRANSIT->value,
                OrderStatusEnum::COMPLETED->value
            ]);
        })->sortBy('status_changed_at');

        // Agrupa por status
        $grouped = $activeOrders->groupBy('status');

        return [
            'pending' => $grouped->get(OrderStatusEnum::PENDING->value, collect()),
            'inProduction' => $grouped->get(OrderStatusEnum::IN_PRODUCTION->value, collect()),
            'inTransit' => $grouped->get(OrderStatusEnum::IN_TRANSIT->value, collect()),
            'completed' => $grouped->get(OrderStatusEnum::COMPLETED->value, collect()),
        ];
    }

    /**
     * Calcula totais e tempos para cada grupo de pedidos
     * 
     * @param Collection $orders
     * @return array ['total' => float, 'time' => int] Tempo em minutos
     */
    public function calculateOrderStats(Collection $orders): array
    {
        $total = $orders->sum(fn($order) => $order->price * $order->quantity);

        if ($orders->isEmpty()) {
            return ['total' => $total, 'time' => 0];
        }

        // Pega o tempo mais antigo do histórico de status
        $oldestTime = null;

        foreach ($orders as $order) {
            $changedAt = $order->status_changed_at;

            if ($changedAt && (!$oldestTime || $changedAt < $oldestTime)) {
                $oldestTime = $changedAt;
            }
        }

        $time = 0;
        if ($oldestTime) {
            $time = abs((int) now()->diffInMinutes($oldestTime));
        }

        return [
            'total' => $total,
            'time' => $time,
        ];
    }

    /**
     * Une múltiplas comandas de origem em uma comanda de destino
     * Move todos os pedidos e marca as comandas de origem como 'merged'
     * 
     * @param array $sourceCheckIds IDs das comandas a serem unidas
     * @param int $destinationCheckId ID da comanda destino
     * @return array ['success' => bool, 'message' => string]
     */
    public function mergeChecks(array $sourceCheckIds, int $destinationCheckId): array
    {
        return DB::transaction(function () use ($sourceCheckIds, $destinationCheckId) {
            // Valida comanda de destino
            $destinationCheck = Check::with('table')->find($destinationCheckId);
            
            if (!$destinationCheck) {
                return ['success' => false, 'message' => 'Comanda de destino não encontrada.'];
            }

            if (!in_array($destinationCheck->status, [CheckStatusEnum::OPEN->value, CheckStatusEnum::CLOSED->value])) {
                return [
                    'success' => false,
                    'message' => 'A comanda de destino não está em um status válido (Aberta ou Fechada) para receber pedidos.'
                ];
            }

            // Remove duplicatas e o destino das origens
            $sourceCheckIds = array_unique(array_diff($sourceCheckIds, [$destinationCheckId]));
            
            if (empty($sourceCheckIds)) {
                return ['success' => false, 'message' => 'Nenhuma comanda de origem válida para unir.'];
            }

            // Processa cada comanda de origem
            foreach ($sourceCheckIds as $sourceCheckId) {
                $result = $this->mergeSourceCheck($sourceCheckId, $destinationCheckId);
                
                if (!$result['success']) {
                    return $result; // Retorna erro e faz rollback
                }
            }

            // Recalcula total da comanda de destino
            $this->checkService->recalculateCheckTotal($destinationCheck);

            return ['success' => true, 'message' => 'Mesas unidas com sucesso!'];
        });
    }

    /**
     * Processa a migração de uma comanda de origem para o destino
     */
    protected function mergeSourceCheck(int $sourceCheckId, int $destinationCheckId): array
    {
        $sourceCheck = Check::with('table')->find($sourceCheckId);

        if (!$sourceCheck) {
            logger()->warning("Check {$sourceCheckId} não encontrado durante merge");
            return ['success' => true]; // Continua com outros
        }

        if (!in_array($sourceCheck->status, [CheckStatusEnum::OPEN->value, CheckStatusEnum::CLOSED->value])) {
            return [
                'success' => false,
                'message' => "Comanda {$sourceCheck->id} (Mesa {$sourceCheck->table->number}) não pode ser unida, status inválido."
            ];
        }

        // Move os pedidos
        Order::where('check_id', $sourceCheckId)->update(['check_id' => $destinationCheckId]);

        // Marca como merged
        $sourceCheck->status = CheckStatusEnum::MERGED->value;
        $sourceCheck->save();

        return ['success' => true];
    }
}
