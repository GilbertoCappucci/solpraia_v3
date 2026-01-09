<?php

namespace App\Livewire\Order;

use App\Enums\OrderStatusEnum;
use App\Services\Order\OrderService;
use App\Models\Table;
use App\Services\CheckService;
use App\Services\GlobalSettingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;

class Orders extends Component
{
    public $title = 'Pedidos';
    public $adminId;
    public $tableId;
    public $selectedTable = null;
    public $currentCheck = null;
    public $statusFilters = [];
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

        $this->adminId = $user->isAdmin()
            ? $user->id
            : ($user->admin_id ?? Auth::id());

        $this->timeLimits = $this->globalSettingsService->getTimeLimits($user);
        $this->tableId = $tableId;
        $this->selectedTable = Table::findOrFail($tableId);
        $this->currentCheck = $this->orderService->findCheck($tableId);
        $this->statusFilters = session('orders.statusFilters', OrderStatusEnum::getValues());
    }

    public function getListeners()
    {
        $adminId = $this->adminId ?: (Auth::user()?->isAdmin() ? Auth::id() : Auth::user()?->admin_id ?? Auth::id());
        
        return [
            "echo-private:global-setting-updated.{$adminId},.global.setting.updated" => 'refreshSetting',
            "echo-private:tables-updated.{$adminId},.table.updated" => 'refreshData',
            "echo-private:tables-updated.{$adminId},.check.updated" => 'refreshData',
            'refresh-parent' => 'refreshData',
            'refresh-modal-data' => 'refreshModalData',

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

    public function refreshData()
    {
        // Recarrega dados atualizados do banco
        $this->selectedTable->refresh();
        $this->currentCheck = $this->orderService->findCheck($this->tableId);

        // Garante que o objeto esteja fresco (evita problemas de cache do Eloquent)
        if ($this->currentCheck) {
            $this->currentCheck->refresh();
        }

        // Atualiza filtros
        $this->statusFilters = session('orders.statusFilters', OrderStatusEnum::getValues());
    }

    public function refreshModalData()
    {
        $this->refreshData();
        
        // Dispatch para os modais que precisam de dados atualizados
        $this->dispatch('$refresh');
    }

    public function getListOrders()
    {
        if (!$this->currentCheck) {
            return collect();
        }

        $orders = \App\Models\Order::with(['product'])
            ->where('check_id', $this->currentCheck->id)
            ->get();

        // Filtra por status selecionados
        $filteredOrders = $orders->filter(function ($order) {
            return in_array($order->status, $this->statusFilters);
        });

        // Retorna cada pedido como um item individual (sem agrupamento automático),
        // preservando a estrutura esperada pela view (`product_id`, `product_name`, etc.).
        $grouped = $filteredOrders->map(function ($order) {
            return (object) [
                'id' => $order->id,
                'product_id' => $order->product_id,
                'product_name' => $order->product?->name ?? '',
                'status' => $order->status,
                'total_quantity' => $order->quantity,
                'order_count' => 1,
                'orders' => collect([$order]),
                'status_changed_at' => $order->status_changed_at,
                'is_paid' => $order->is_paid,
            ];
        });

        return $grouped->sortBy('id')->values();
    }

    #[Computed]
    public function checkTotal()
    {
        return $this->currentCheck ? $this->currentCheck->total : 0;
    }

    #[Computed]
    public function orders()
    {
        if (!$this->currentCheck) {
            return collect();
        }

        return \App\Models\Order::with(['product'])
            ->where('check_id', $this->currentCheck->id)
            ->get();
    }

    public function render()
    {
        return view('livewire.order.orders', [
            'listOrders' => $this->getListOrders(),
            'checkTotal' => $this->checkTotal,
            'orders' => $this->orders,
        ]);
    }
}
