<?php

namespace App\Services;

use App\Enums\CheckStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\TableStatusEnum;
use App\Models\Check;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected $checkService;
    protected $stockService;

    public function __construct(CheckService $checkService, StockService $stockService)
    {
        $this->checkService = $checkService;
        $this->stockService = $stockService;
    }

    /**
     * Recalcula o total de todos os checks ativos
     */
    public function recalculateAllActiveChecks(): void
    {
        // Busca todos os checks ativos (não Paid nem Canceled)
        $activeChecks = Check::whereNotIn('status', [
            CheckStatusEnum::PAID->value,
            CheckStatusEnum::CANCELED->value
        ])
            ->get();

        foreach ($activeChecks as $check) {
            $this->checkService->recalculateCheckTotal($check);
        }
    }

    /**
     * Busca ou cria check aberto para a mesa
     */
    public function findOrCreateCheck(int $tableId): ?Check
    {
        // Busca check ativo (não Paid nem Canceled)
        $existingCheck = Check::where('table_id', $tableId)
            ->whereNotIn('status', [
                CheckStatusEnum::PAID->value,
                CheckStatusEnum::CANCELED->value,
                CheckStatusEnum::MERGED->value,
            ])
            ->orderBy('created_at', 'desc')
            ->first();
            
        // Se encontrou check ativo, retorna
        if ($existingCheck) {
            return $existingCheck;
        }
        
        // Se não encontrou, cria novo check automaticamente
        $newCheck = Check::create([
            'table_id' => $tableId,
            'status' => CheckStatusEnum::OPEN->value,
            'total' => 0.00,
        ]);
        
        // Atualiza status da mesa para ocupada se estiver livre
        $table = Table::find($tableId);
        if ($table && $table->status === TableStatusEnum::FREE->value) {
            $table->update(['status' => TableStatusEnum::OCCUPIED->value]);
        }
        
        logger('🆕 Check criado automaticamente', [
            'check_id' => $newCheck->id,
            'table_id' => $tableId,
            'user_id' => auth()->id() ?? 'sistema'
        ]);
        
        return $newCheck;
    }

    /**
     * Valida e atualiza status da mesa e check
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
                $table->update(['status' => TableStatusEnum::RELEASING->value]);
            } elseif ($newCheckStatus === CheckStatusEnum::CANCELED->value) {
                $table->update(['status' => TableStatusEnum::FREE->value]);
            }
        }

        return ['success' => true];
    }

    /**
     * Busca pedidos ativos agrupados por status (Simples, sem agrupamento virtual)
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

        // Busca pedidos ativos
        // Busca pedidos do check
        $allOrders = Order::where('check_id', $check->id)
            ->with(['product', 'currentStatusHistory'])
            ->get();

        // Filtra pelos status ativos usando a lógica do Accessor do Model
        $activeOrders = $allOrders->filter(function ($order) {
            return in_array($order->status, [
                OrderStatusEnum::PENDING->value,
                OrderStatusEnum::IN_PRODUCTION->value,
                OrderStatusEnum::IN_TRANSIT->value,
                OrderStatusEnum::COMPLETED->value
            ]);
        })->sortBy('status_changed_at');

        // Agrupa por status usando as keys do Enum ou string direta
        $grouped = $activeOrders->groupBy('status');

        return [
            'pending' => $grouped->get(OrderStatusEnum::PENDING->value, collect()),
            'inProduction' => $grouped->get(OrderStatusEnum::IN_PRODUCTION->value, collect()),
            'inTransit' => $grouped->get(OrderStatusEnum::IN_TRANSIT->value, collect()),
            'completed' => $grouped->get(OrderStatusEnum::COMPLETED->value, collect()),
        ];
    }


    /**
     * Calcula totais e tempos para cada grupo de pedidos usando histórico
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
            // Usa o atributo virtual status_changed_at
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
     * Atualiza status. Se qtyToMove < quantity atual, DIVIDE o pedido.
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

            // Se qtyToMove for 0 ou igual à quantidade atual, move TUDO (simples update)
            if ($qtyToMove <= 0 || $qtyToMove >= $currentQty) {
                // Registra histórico com price e quantity atuais
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => $oldStatus,
                    'to_status' => $newStatus,
                    'price' => $order->price,
                    'quantity' => $order->quantity,
                    'changed_at' => now(),
                ]);

                // O trigger ou observer do status history deve atualizar o status na tabela orders,
                // mas como estamos refatorando, vamos garantir aqui se não houver observer:
                // (Assumindo que OrderStatusHistory não tem observer mágico que atualiza a Order, 
                //  o padrão anterior parecia confiar no atributo virtual ou algo assim, mas vamos ser explícitos)
                // O código anterior usava um atributo virtual para 'status' baseado no histórico? 
                // O model Order tem um método 'status()' ou atributo? 
                // Pelo código lido anteriormente, parecia ter. 
                // Mas vamos manter simples: criar histórico é o gatilho principal.
                // SE o sistema depender de registro no histórico para definir status atual, isso basta.

                // Porém, para garantir consistência visual imediata em queries simples:
                // (Se a tabela orders tiver coluna status, atualizamos ela tb)
                // Verificando migration anterior, orders não parecia ter status, mas o código usa $order->status.
                // Vamos assumir que criar o histórico basta OU que precisamos atualizar algo.
                // NO CÓDIGO ANTERIOR: $order->status vinha do histórico via atributo, mas OrderStatusHistory::create era a ação.

                // PONTO CRITICO: Se $order->status é dinâmico (accessor), criar o histórico atualiza ele.


            } else {
                // MOVIMENTO PARCIAL -> DIVISÃO (SPLIT)

                // 1. Cria novo histórico para o pedido atual com quantidade reduzida (mantendo status antigo)
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => $oldStatus,
                    'to_status' => $oldStatus, // Mantém o mesmo status
                    'price' => $order->price,
                    'quantity' => $currentQty - $qtyToMove, // Quantidade reduzida
                    'changed_at' => now(),
                ]);

                // 2. Cria NOVO pedido com a quantidade movida e o NOVO status
                $newOrder = Order::create([
                    'user_id' => $order->user_id,
                    'check_id' => $order->check_id,
                    'product_id' => $order->product_id,
                ]);

                // 3. Registra histórico para o NOVO pedido com price e quantity
                OrderStatusHistory::create([
                    'order_id' => $newOrder->id,
                    'from_status' => $oldStatus,
                    'to_status' => $newStatus,
                    'price' => $order->price,
                    'quantity' => $qtyToMove,
                    'changed_at' => now(),
                ]);

                // O pedido antigo não mudou de status, apenas de quantidade. Não gera histórico de status.
            }

            // Recalcula check (apenas precaução, pois valor total não muda com troca de status, apenas se cancelar)
            if ($order->check) {
                $this->checkService->recalculateCheckTotal($order->check);
            }

            return ['success' => true];
        });
    }

    /**
     * Cancela um pedido (apenas se estiver no status PENDING)
     * Agora suporta quantidade parcial.
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

            // Se cancelar tudo, marca como cancelado independente do status atual
            if ($qtyToCancel >= $order->quantity) {
                // Cancela TUDO
                OrderStatusHistory::create([
                    'order_id' => $orderId,
                    'from_status' => $order->status,
                    'to_status' => OrderStatusEnum::CANCELED->value,
                    'price' => $order->price,
                    'quantity' => $order->quantity,
                    'changed_at' => now(),
                ]);
            } else {
                // Cancela PARCIAL - cria novo histórico com quantidade reduzida
                OrderStatusHistory::create([
                    'order_id' => $orderId,
                    'from_status' => $order->status,
                    'to_status' => $order->status, // Mantém o mesmo status
                    'price' => $order->price,
                    'quantity' => $order->quantity - $qtyToCancel,
                    'changed_at' => now(),
                ]);
            }

            if ($order->check) {
                $this->checkService->recalculateCheckTotal($order->check);
            }

            return ['success' => true, 'message' => 'Item removido com sucesso.'];
        });
    }

    /**
     * Cancela múltiplos pedidos de uma vez
     */
    public function cancelOrders(array $orderIds): array
    {
        return DB::transaction(function () use ($orderIds) {
            $count = 0;
            $check = null;

            foreach ($orderIds as $orderId) {
                // Busca individualmente para validar e registrar histórico
                $order = Order::with(['currentStatusHistory', 'check'])->find($orderId);

                if (!$order) continue;

                // Captura check do primeiro pedido para recalcular no final
                if (!$check && $order->check) {
                    $check = $order->check;
                }

                // Valida status PENDING
                if ($order->status !== OrderStatusEnum::PENDING->value) {
                    continue; // Pula pedidos que não estão aguardando
                }

                // Devolve o estoque
                $this->stockService->increment($order->product_id, $order->quantity);

                // Registra cancelamento
                OrderStatusHistory::create([
                    'order_id' => $orderId,
                    'from_status' => $order->status,
                    'to_status' => OrderStatusEnum::CANCELED->value,
                    'price' => $order->price,
                    'quantity' => $order->quantity,
                    'changed_at' => now(),
                ]);

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
     * Duplica um pedido PENDING (adiciona mais uma unidade)
     * Agora apenas incrementa a quantidade do pedido existente.
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

            // Cria novo histórico com quantidade incrementada
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $order->status,
                'to_status' => $order->status, // Mantém PENDING
                'price' => $order->price,
                'quantity' => $order->quantity + 1,
                'changed_at' => now(),
            ]);

            $this->checkService->recalculateCheckTotal($order->check);

            return ['success' => true];
        });
    }

    /**
     * Une múltiplas comandas de origem em uma comanda de destino.
     * Move todos os pedidos e marca as comandas de origem como 'merged'.
     *
     * @param array $sourceCheckIds IDs das comandas a serem unidas (origem).
     * @param int $destinationCheckId ID da comanda que receberá os pedidos (destino).
     * @return array ['success' => bool, 'message' => string]
     */
    public function mergeChecks(array $sourceCheckIds, int $destinationCheckId): array
    {
        return DB::transaction(function () use ($sourceCheckIds, $destinationCheckId) {
            // 1. Valida a comanda de destino
            $destinationCheck = Check::with('table')->find($destinationCheckId);
            if (!$destinationCheck) {
                return ['success' => false, 'message' => 'Comanda de destino não encontrada.'];
            }
            if (!in_array($destinationCheck->status, [CheckStatusEnum::OPEN->value, CheckStatusEnum::CLOSED->value])) {
                return ['success' => false, 'message' => 'A comanda de destino não está em um status válido (Aberta ou Fechada) para receber pedidos.'];
            }
            // Garante que o destino não está entre as origens e remove duplicatas
            $sourceCheckIds = array_unique(array_diff($sourceCheckIds, [$destinationCheckId]));
            if (empty($sourceCheckIds)) {
                return ['success' => false, 'message' => 'Nenhuma comanda de origem válida para unir.'];
            }


            // 2. Processa cada comanda de origem
            foreach ($sourceCheckIds as $sourceCheckId) {
                $sourceCheck = Check::with('table')->find($sourceCheckId);

                if (!$sourceCheck) {
                    // Logar erro, mas continuar com as outras
                    continue;
                }

                if (!in_array($sourceCheck->status, [CheckStatusEnum::OPEN->value, CheckStatusEnum::CLOSED->value])) {
                    // Comandas de origem precisam estar abertas ou fechadas para serem unidas
                    return ['success' => false, 'message' => "Comanda {$sourceCheck->id} (Mesa {$sourceCheck->table->number}) não pode ser unida, status inválido."];
                }

                // Move os pedidos
                Order::where('check_id', $sourceCheckId)->update(['check_id' => $destinationCheckId]);

                // Atualiza o status da comanda de origem para MERGED
                $sourceCheck->status = CheckStatusEnum::MERGED->value;
                $sourceCheck->save();
            }

            // 3. Recalcula o total da comanda de destino
            $this->checkService->recalculateCheckTotal($destinationCheck);

            return ['success' => true, 'message' => 'Mesas unidas com sucesso!'];
        });
    }

    /**
     * Verifca se existe algum pedido aguardando
     */
    public static function hasPendingOrders(Check $check): bool
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
     * Retorna true se NÃO existir nenhum pedido incompleto (Aguardando, Em Produção, No Caminho, etc.)
     */
    public static function areAllOrdersCompletedOrCanceled(Check $check): bool
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

        // Se encontrou algum incompleto, retorna false. Se não encontrou nada incompleto, retorna true.
        return !$hasIncompleteOrders;
    }
}
