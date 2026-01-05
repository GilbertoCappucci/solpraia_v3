<?php

namespace App\Livewire\Order;

use App\Enums\OrderStatusEnum;
use App\Services\OrderService;
use App\Models\Table;
use App\Services\CheckService;
use App\Services\GlobalSettingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;

class Orders extends Component
{
    public $title = 'Pedidos';
    public $userId;
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

        $this->userId = $user->isAdmin()
            ? $user->id
            : ($user->user_id ?? Auth::id());

        $this->timeLimits = $this->globalSettingsService->getTimeLimits($user);
        $this->tableId = $tableId;
        $this->selectedTable = Table::findOrFail($tableId);
        $this->currentCheck = $this->orderService->findCheck($tableId);
        $this->statusFilters = session('orders.statusFilters', OrderStatusEnum::getValues());
    }

    public function getListeners()
    {
        $userId = $this->userId ?: (Auth::user()?->isAdmin() ? Auth::id() : Auth::user()?->user_id ?? Auth::id());
        
        return [
            "echo-private:global-setting-updated.{$userId},.global.setting.updated" => 'refreshSetting',
            "echo-private:tables-updated.{$userId},.table.updated" => 'refreshData',
            "echo-private:tables-updated.{$userId},.check.updated" => 'refreshData',
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

    #[Computed]
    public function groupedOrders()
    {
        if (!$this->currentCheck) {
            return collect();
        }

        $orders = \App\Models\Order::with(['product', 'currentStatusHistory'])
            ->where('check_id', $this->currentCheck->id)
            ->get();

        // Filtra por status selecionados
        $filteredOrders = $orders->filter(function ($order) {
            return in_array($order->status, $this->statusFilters);
        });

        // Agrupa por product_id e status
        $grouped = $filteredOrders->groupBy(function ($order) {
            return $order->product_id . '_' . $order->status;
        })->map(function ($group) {
            $first = $group->first();
            return (object) [
                'product_id' => $first->product_id,
                'product_name' => $first->product->name,
                'status' => $first->status,
                'total_quantity' => $group->sum('quantity'),
                'order_count' => $group->count(),
                'orders' => $group,
                'status_changed_at' => $group->min('status_changed_at'),
            ];
        });

        return $grouped->sortBy('status_changed_at')->values();
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

        return \App\Models\Order::with(['product', 'currentStatusHistory'])
            ->where('check_id', $this->currentCheck->id)
            ->get();
    }

    public function render()
    {
        return view('livewire.order.orders', [
            'groupedOrders' => $this->groupedOrders,
            'checkTotal' => $this->checkTotal,
            'orders' => $this->orders,
        ]);
    }
}
