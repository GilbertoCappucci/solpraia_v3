<?php

namespace App\Livewire;

use App\Enums\OrderStatusEnum;
use App\Services\OrderService;
use App\Models\Table;
use App\Services\CheckService;
use App\Services\GlobalSettingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Orders extends Component
{
    public $title = 'Pedidos';
    public $userId;
    public $tableId;
    public $selectedTable = null;
    public $currentCheck = null;
    public $showStatusCheckModal = false;
    public $newTableStatus = null;
    public $newCheckStatus = null;
    public $showCancelModal = false;
    public $orderToCancel = null;
    public $orderIdsToCancel = [];
    public $orderToCancelData = null;
    public $hasActiveCheck = false;
    public $showDetailsModal = false;
    public $orderDetails = null;
    public $showFilterModal = false;
    public $statusFilters = [];
    public $showGroupModal = false;
    public $groupOrders = [];
    public $selectedOrderIds = [];
    public $showGroupActionsModal = false;
    public $groupActionData = null;
    public $checkStatusAllowed = [];
    public $timeLimits = [];

    protected $orderService;
    protected $checkService;
    protected $globalSettingsService;

    public function boot(OrderService $orderService, CheckService $checkService, GlobalSettingService $globalSettingsService)
    {
        $this->orderService = $orderService;
        $this->checkService = $checkService;
        $this->globalSettingsService = $globalSettingsService;
    }

    public function mount($tableId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $this->userId = $user->isAdmin()
            ? $user->id
            : $user->user_id;

        $this->timeLimits = $this->globalSettingsService->getTimeLimits($user);
        $this->tableId = $tableId;
        $this->selectedTable = Table::findOrFail($tableId);
        $this->currentCheck = $this->orderService->findOrCreateCheck($tableId);
        $this->statusFilters = session('orders.statusFilters', OrderStatusEnum::getValues());

        // Abre o modal de status automaticamente se a mesa estiver em Liberação, Fechada ou Reservada
        if (in_array($this->selectedTable->status, [\App\Enums\TableStatusEnum::RELEASING->value, \App\Enums\TableStatusEnum::CLOSE->value, \App\Enums\TableStatusEnum::RESERVED->value])) {
            $this->openStatusModal();
        }

        // Define status permitidos para o check com base no status da mesa
        $this->checkStatusAllowed = $this->checkService->getAllowedCheckStatuses($this->currentCheck?->status ?? '', $this->currentCheck);
    }

    public function getListeners()
    {
        return [
            'global.setting.updated' => 'refreshSetting',
        ];
    }

    public function refreshSetting($data = null)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Atualizar configurações globais
        $this->timeLimits = $this->globalSettingsService->getTimeLimits($user);
        
        logger('✅ Orders: Configurações atualizadas', [
            'timeLimits' => $this->timeLimits
        ]);
    }

    public function updatedCheckStatus($value)
    {
        // Atualiza os status permitidos para o check com base no novo status selecionado
        $this->checkStatusAllowed = $this->checkService->getAllowedCheckStatuses($value);
    }

    /**
     * Atualiza os status da mesa e do check com validações
     */

    public function backToTables()
    {
        return redirect()->route('tables');
    }

    public function goToMenu()
    {
        // Verifica se a mesa está fechada
        if ($this->selectedTable->status === \App\Enums\TableStatusEnum::CLOSE->value) {
            session()->flash('error', 'Não é possível adicionar pedidos em uma mesa fechada!');
            return;
        }

        // Verifica se o check está aberto (permite se check for NULL - primeiro pedido)
        if ($this->currentCheck && $this->currentCheck->status !== 'Open') {
            session()->flash('error', 'Para adicionar novos pedidos, o check precisa estar no status "Aberto". Altere o status do check primeiro.');
            return;
        }

        return redirect()->route('menu', ['tableId' => $this->tableId]);
    }

    public function openStatusModal()
    {
        // Recarrega dados do banco antes de abrir modal
        $this->refreshData();

        $this->showStatusCheckModal = true;
        $this->newTableStatus = $this->selectedTable->status;
        $this->newCheckStatus = $this->currentCheck?->status;
        $this->checkStatusAllowed = $this->checkService->getAllowedCheckStatuses($this->newCheckStatus ?? '', $this->currentCheck);
    }

    public function closeStatusModal()
    {
        $this->showStatusCheckModal = false;
        $this->newTableStatus = null;
        $this->newCheckStatus = null;
    }

    public function refreshData()
    {
        // Recarrega dados atualizados do banco
        $this->selectedTable->refresh();
        $this->currentCheck = $this->orderService->findOrCreateCheck($this->tableId);

        // Garante que o objeto esteja fresco (evita problemas de cache do Eloquent)
        if ($this->currentCheck) {
            $this->currentCheck->refresh();
        }

        // Verifica se há check ativo (Open ou Closed) que bloqueia mudança de status da mesa
        $this->hasActiveCheck = $this->currentCheck && in_array($this->currentCheck->status, ['Open', 'Closed']);
    }

    public function updateStatuses()
    {
        $result = $this->orderService->updateStatuses(
            $this->selectedTable,
            $this->currentCheck,
            $this->newTableStatus,
            $this->newCheckStatus
        );

        if (!$result['success']) {
            session()->flash('error', implode(' ', $result['errors']));
            return;
        }

        // Se alterou para Fechado, redireciona para a tela do check
        if ($this->newCheckStatus === 'Closed') {
            session()->flash('success', 'Check fechado! Finalize o pagamento.');
            return redirect()->route('check', ['checkId' => $this->currentCheck->id]);
        }

        session()->flash('success', 'Status atualizado com sucesso!');
        $this->closeStatusModal();
        $this->refreshData();
    }

    public function updateOrderStatus($orderId, $newStatus, $qtyToMove = 0)
    {
        // Verifica se o check está aberto (se houver check, precisa estar Open)
        if ($this->currentCheck && $this->currentCheck->status !== 'Open') {
            session()->flash('error', 'Para alterar o status de pedidos, o check precisa estar no status "Aberto". Altere o status do check primeiro.');
            return;
        }

        $result = $this->orderService->updateOrderStatus($orderId, $newStatus, $qtyToMove);

        if (!$result['success']) {
            session()->flash('error', $result['message']);
            return;
        }

        session()->flash('success', 'Pedido atualizado com sucesso!');
        $this->refreshData();
    }
    // updateAllOrderStatus method removed as it is now redundant.


    public function openCancelModal($orderId)
    {
        $this->orderToCancel = $orderId;

        // Busca dados do pedido para exibir no modal
        $order = \App\Models\Order::with('product')->find($orderId);
        if ($order) {
            $this->orderToCancelData = [
                'product_name' => $order->product->name,
                'quantity' => $order->quantity,
                'price' => $order->price,
            ];
        }

        $this->showCancelModal = true;
    }

    public function closeCancelModal()
    {
        $this->showCancelModal = false;
        $this->orderToCancel = null;
        $this->orderIdsToCancel = [];
        $this->orderToCancelData = null;
    }

    // confirmCancelAll removida pois agora tratamos apenas uma linha

    public function confirmCancelOrder($qtyToCancel = 0)
    {
        if (!$this->orderToCancel) {
            return;
        }

        // Se 0 ou maior que qtd total (nao tratado aqui), o service assume "tudo" se >= qtd
        // Vamos passar o que vier
        // Se vier 0, vamos assumir que é cancelar TUDO (ou 1 unidade padrão? O service assume 1 se não passar nada, ou erro?)
        // Vamos ajustar para passar 1 por padrão se for chamada direta sem args, ou passar qty especifica.
        // O service cancelOrder($orderId, $qtyToCancel).
        // Se o usuário clicar "Remover Tudo", passamos a quantidade total.
        // Se clicar "Remover 1", passamos 1.

        if ($qtyToCancel == 0 && isset($this->orderToCancelData['quantity'])) {
            // Se não especificou (ex: botão confirmar simples), remove TUDO ou 1?
            // Antes "Confirmar" removia tudo se fosse um item.
            // Vamos assumir: Sem argumento = Remover Tudo do Item Selecionado
            $qtyToCancel = $this->orderToCancelData['quantity'];
        }

        $result = $this->orderService->cancelOrder($this->orderToCancel, $qtyToCancel);

        if (!$result['success']) {
            session()->flash('error', $result['message']);
            $this->closeCancelModal();
            return;
        }

        session()->flash('success', $result['message']);
        $this->closeCancelModal();
        $this->refreshData();
    }

    public function addOneMore($orderId)
    {
        // Verifica se o check está aberto (se houver check, precisa estar Open)
        if ($this->currentCheck && $this->currentCheck->status !== 'Open') {
            session()->flash('error', 'Para adicionar mais pedidos, o check precisa estar no status "Aberto". Altere o status do check primeiro.');
            return;
        }

        $result = $this->orderService->duplicatePendingOrder($orderId);

        if (!$result['success']) {
            session()->flash('error', $result['message']);
            return;
        }

        session()->flash('success', 'Quantidade aumentada!');
        $this->refreshData();
    }

    public function openDetailsModal($orderId)
    {
        $order = \App\Models\Order::with('product')->find($orderId);

        if (!$order) {
            session()->flash('error', 'Pedido não encontrado.');
            return;
        }

        // Busca estoque disponível
        $stock = \App\Models\Stock::where('product_id', $order->product_id)->first();
        $availableStock = $stock ? $stock->quantity : 0;

        $this->orderDetails = [
            'id' => $order->id,
            'product_id' => $order->product_id,
            'product_name' => $order->product->name,
            'quantity' => $order->quantity,
            'price' => $order->price,
            'status' => $order->status,
            'created_at' => $order->created_at,
            'total' => $order->quantity * $order->price,
            'available_stock' => $availableStock,
        ];

        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->orderDetails = null;
    }

    public function openGroupModal($productId, $status)
    {
        // Busca todos os pedidos do check atual
        $allOrders = \App\Models\Order::with(['product', 'currentStatusHistory'])
            ->where('check_id', $this->currentCheck->id)
            ->where('product_id', $productId)
            ->get();

        // Filtra manualmente por status usando o atributo virtual
        $orders = $allOrders->filter(function ($order) use ($status) {
            return $order->status === $status;
        });

        $this->groupOrders = $orders->values()->toArray();

        // Seleciona todos os pedidos por padrão
        $this->selectedOrderIds = $orders->pluck('id')->toArray();

        $this->showGroupModal = true;
    }

    public function closeGroupModal()
    {
        $this->showGroupModal = false;
        $this->groupOrders = [];
        $this->selectedOrderIds = [];
    }

    public function openDetailsFromGroup($orderId)
    {
        $this->closeGroupModal();
        $this->openDetailsModal($orderId);
    }

    public function toggleOrderSelection($orderId)
    {
        if (in_array($orderId, $this->selectedOrderIds)) {
            $this->selectedOrderIds = array_values(array_diff($this->selectedOrderIds, [$orderId]));
        } else {
            $this->selectedOrderIds[] = $orderId;
        }
    }

    public function toggleSelectAll()
    {
        if (count($this->selectedOrderIds) === count($this->groupOrders)) {
            $this->selectedOrderIds = [];
        } else {
            $this->selectedOrderIds = collect($this->groupOrders)->pluck('id')->toArray();
        }
    }

    public function openGroupActionsModal()
    {
        if (empty($this->selectedOrderIds)) {
            session()->flash('error', 'Selecione ao menos um pedido.');
            return;
        }

        $selectedOrders = collect($this->groupOrders)->whereIn('id', $this->selectedOrderIds);
        $firstOrder = $selectedOrders->first();

        $this->groupActionData = [
            'order_ids' => $this->selectedOrderIds,
            'count' => count($this->selectedOrderIds),
            'total_quantity' => $selectedOrders->sum('quantity'),
            'product_name' => $firstOrder['product']['name'] ?? '',
            'status' => $firstOrder['status'] ?? 'pending',
            'total_price' => $selectedOrders->sum(fn($o) => $o['quantity'] * $o['price']),
        ];

        $this->showGroupActionsModal = true;
    }

    public function closeGroupActionsModal()
    {
        $this->showGroupActionsModal = false;
        $this->groupActionData = null;
    }

    public function updateGroupStatus($newStatus)
    {
        if (!$this->groupActionData || !$this->currentCheck || $this->currentCheck->status !== 'Open') {
            session()->flash('error', 'Não é possível alterar o status neste momento.');
            return;
        }

        $success = 0;
        $errors = [];

        foreach ($this->groupActionData['order_ids'] as $orderId) {
            $result = $this->orderService->updateOrderStatus($orderId, $newStatus, 0);
            if ($result['success']) {
                $success++;
            } else {
                $errors[] = $result['message'];
            }
        }

        if ($success > 0) {
            session()->flash('success', "$success pedido(s) atualizado(s) com sucesso!");
        }

        if (!empty($errors)) {
            session()->flash('error', implode(' ', array_unique($errors)));
        }

        $this->closeGroupActionsModal();
        $this->closeGroupModal();
        $this->selectedOrderIds = [];
        $this->refreshData();
    }

    public function cancelGroupOrders()
    {
        if (!$this->groupActionData) {
            return;
        }

        $success = 0;
        $errors = [];

        foreach ($this->groupActionData['order_ids'] as $orderId) {
            $order = \App\Models\Order::find($orderId);
            if ($order) {
                $result = $this->orderService->cancelOrder($orderId, $order->quantity);
                if ($result['success']) {
                    $success++;
                } else {
                    $errors[] = $result['message'];
                }
            }
        }

        if ($success > 0) {
            session()->flash('success', "$success pedido(s) cancelado(s) com sucesso!");
        }

        if (!empty($errors)) {
            session()->flash('error', implode(' ', array_unique($errors)));
        }

        $this->closeGroupActionsModal();
        $this->closeGroupModal();
        $this->selectedOrderIds = [];
        $this->refreshData();
    }

    public function incrementQuantity()
    {
        if (!$this->orderDetails || !$this->currentCheck || $this->currentCheck->status !== 'Open') {
            session()->flash('error', 'Não é possível alterar a quantidade neste momento.');
            return;
        }

        if ($this->orderDetails['status'] !== 'pending') {
            session()->flash('error', 'Só é possível alterar a quantidade de pedidos no status "Aguardando".');
            return;
        }

        $result = $this->orderService->duplicatePendingOrder($this->orderDetails['id']);

        if (!$result['success']) {
            session()->flash('error', $result['message']);
            return;
        }

        session()->flash('success', 'Quantidade aumentada!');
        $this->refreshData();
        // Recarrega os detalhes do pedido atualizado
        $this->openDetailsModal($this->orderDetails['id']);
    }

    public function decrementQuantity()
    {
        if (!$this->orderDetails || !$this->currentCheck || $this->currentCheck->status !== 'Open') {
            session()->flash('error', 'Não é possível alterar a quantidade neste momento.');
            return;
        }

        if ($this->orderDetails['status'] !== 'pending') {
            session()->flash('error', 'Só é possível alterar a quantidade de pedidos no status "Aguardando".');
            return;
        }

        if ($this->orderDetails['quantity'] <= 1) {
            session()->flash('error', 'Use o botão cancelar para remover o último item.');
            return;
        }

        $result = $this->orderService->cancelOrder($this->orderDetails['id'], 1);

        if (!$result['success']) {
            session()->flash('error', $result['message']);
            return;
        }

        session()->flash('success', 'Quantidade reduzida!');
        $this->refreshData();
        // Recarrega os detalhes do pedido atualizado
        $this->openDetailsModal($this->orderDetails['id']);
    }

    public function updateOrderStatusFromModal($newStatus)
    {
        if (!$this->orderDetails || !$this->currentCheck || $this->currentCheck->status !== 'Open') {
            session()->flash('error', 'Não é possível alterar o status neste momento.');
            return;
        }

        $result = $this->orderService->updateOrderStatus($this->orderDetails['id'], $newStatus, 0);

        if (!$result['success']) {
            session()->flash('error', $result['message']);
            return;
        }

        session()->flash('success', 'Status atualizado!');
        $this->closeDetailsModal();
        $this->refreshData();
    }

    public function cancelOrderFromModal()
    {
        if (!$this->orderDetails) {
            return;
        }

        $this->orderToCancel = $this->orderDetails['id'];
        $this->orderToCancelData = $this->orderDetails;
        $this->closeDetailsModal();
        $this->showCancelModal = true;
    }

    public function openFilterModal()
    {
        $this->showFilterModal = true;
    }

    public function closeFilterModal()
    {
        $this->showFilterModal = false;
    }

    public function toggleStatusFilter($status)
    {
        if (in_array($status, $this->statusFilters)) {
            $this->statusFilters = array_values(array_diff($this->statusFilters, [$status]));
        } else {
            $this->statusFilters[] = $status;
        }

        session(['orders.statusFilters' => $this->statusFilters]);
    }

    public function resetFilters()
    {
        $this->statusFilters = ['pending', 'in_production', 'in_transit', 'completed', 'canceled'];
        session(['orders.statusFilters' => $this->statusFilters]);
    }

    public function render()
    {
        // Garante que o check atual e seus pedidos estejam sempre atualizados no momento do render
        // Isso resolve problemas de cache do Eloquent ou de dados desatualizados após ações que modificam o check/orders.
        $this->refreshData();

        // Busca todos os pedidos ativos
        $allOrders = collect();
        $groupedOrders = collect();

        if ($this->currentCheck) {
            $orders = \App\Models\Order::with(['product', 'currentStatusHistory'])
                ->where('check_id', $this->currentCheck->id)
                ->where(function ($query) {
                    $query->whereHas('currentStatusHistory', function ($q) {
                        $q->whereIn('to_status', ['pending', 'in_production', 'in_transit', 'completed', 'canceled']);
                    })
                        ->orDoesntHave('statusHistory');
                })
                ->orderBy('created_at', 'desc')
                ->get();

            // Aplica filtro de status
            if (!empty($this->statusFilters)) {
                $allOrders = $orders->filter(function ($order) {
                    return in_array($order->status, $this->statusFilters);
                });
            } else {
                $allOrders = $orders;
            }

            // Agrupa por produto_id + status
            $groupedOrders = $allOrders->groupBy(function ($order) {
                return $order->product_id . '_' . $order->status;
            })->map(function ($group) {
                $firstOrder = $group->first();
                $totalQuantity = $group->sum('quantity');
                $orderCount = $group->count();

                return (object) [
                    'product_id' => $firstOrder->product_id,
                    'product_name' => $firstOrder->product->name,
                    'product_price' => $firstOrder->price,
                    'status' => $firstOrder->status,
                    'total_quantity' => $totalQuantity,
                    'order_count' => $orderCount,
                    'orders' => $group,
                    'total_price' => $group->sum(fn($o) => $o->quantity * $o->price), // Necessário para exibir preço do grupo
                    'status_changed_at' => $group->max('status_changed_at'), // Restaurado para cálculo de atraso na view
                ];
            })->values();

            // Calcula o total geral usando o Service para garantir a regra de negócio (ignora Pending/Canceled)
            $ordersTotal = $this->checkService->calculateTotal($this->currentCheck);
        }

        // Permite adicionar pedidos se não há check ainda (NULL) ou se check está Open
        $isCheckOpen = !$this->currentCheck || $this->currentCheck->status === 'Open';

        return view('livewire.orders', [
            'groupedOrders' => $groupedOrders,
            'isCheckOpen' => $isCheckOpen,
            'orders' => $orders ?? collect(),
            'checkTotal' => $ordersTotal ?? 0,
        ]);
    }
}
