<?php

namespace App\Livewire;

use App\Enums\TableStatusEnum;
use App\Services\CheckService;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Check extends Component
{
    public $checkId;
    public $check;
    public $table;
    public $title = 'Comanda';
    public $pollingInterval;

    public $showStatusModal = false;
    public $currentCheck = null;
    public $newCheckStatus = null;

    public $checkStatusAllowed = [];

    protected $checkService;
    protected $orderService;
    protected $pixService;

    public function boot(CheckService $checkService, OrderService $orderService, \App\Services\PixService $pixService)
    {
        $this->checkService = $checkService;
        $this->orderService = $orderService;
        $this->pixService = $pixService;
        $this->pollingInterval = config('restaurant.polling_interval');
    }

    public function mount($checkId)
    {
        $this->checkId = $checkId;
        $this->loadCheck();
    }

    public function loadCheck()
    {
        $this->check = \App\Models\Check::with(['table', 'orders.product', 'orders.currentStatusHistory'])
            ->findOrFail($this->checkId);

        $this->table = $this->check->table;

        // Recalcula o total do check
        $this->checkService->recalculateCheckTotal($this->check);

        $this->currentCheck = $this->check;

        // Define status permitidos para o check com base no status da mesa
        $this->checkStatusAllowed = $this->checkService->getAllowedCheckStatuses($this->currentCheck?->status ?? '', $this->check);
    }

    public function openStatusModal()
    {
        $this->newCheckStatus = $this->check->status;
        $this->showStatusModal = true;
    }

    public function closeStatusModal()
    {
        $this->showStatusModal = false;
        $this->newCheckStatus = null;
    }

    public function updateCheckStatus()
    {
        if (!$this->newCheckStatus) {
            return;
        }

        // Usa método centralizado do CheckService para validar e atualizar
        $result = $this->checkService->validateAndUpdateCheckStatus($this->check, $this->newCheckStatus);

        if (!$result['success']) {
            session()->flash('error', implode(' ', $result['errors']));
            return;
        }

        // Se o check foi marcado como PAID, coloca mesa em RELEASING e volta para tables
        if ($this->newCheckStatus === 'Paid') {
            $this->table->update(['status' => TableStatusEnum::RELEASING->value]);
            session()->flash('success', 'Pagamento finalizado!');
            return redirect()->route('tables');
        }

        // Se foi CANCELED, libera direto para FREE
        if ($this->newCheckStatus === 'Canceled') {
            $this->table->update(['status' => TableStatusEnum::FREE->value]);
        }

        // Se voltou para Open, redireciona para orders
        if ($this->newCheckStatus === 'Open') {
            session()->flash('success', 'Check reaberto!');
            return redirect()->route('orders', ['tableId' => $this->table->id]);
        }

        session()->flash('success', 'Status da comanda atualizado com sucesso!');
        $this->closeStatusModal();
        $this->loadCheck();
    }

    public function goBack()
    {
        return redirect()->route('tables');
    }

    public function goToOrders()
    {
        return redirect()->route('orders', ['tableId' => $this->table->id]);
    }

    public function render()
    {
        // Se o check estiver fechado, mostra apenas pedidos entregues
        if ($this->check->status === 'Closed' || $this->check->status === 'Paid') {
            $groupedOrders = [
                'pending' => collect([]),
                'inProduction' => collect([]),
                'inTransit' => collect([]),
                'delivered' => $this->check->orders->where('status', 'completed'),
                'canceled' => collect([]),
            ];
        } else {
            // Agrupa pedidos por status (exibição normal)
            $groupedOrders = [
                'pending' => $this->check->orders->where('status', 'pending'),
                'inProduction' => $this->check->orders->where('status', 'in_production'),
                'inTransit' => $this->check->orders->where('status', 'in_transit'),
                'delivered' => $this->check->orders->where('status', 'completed'),
                'canceled' => $this->check->orders->where('status', 'canceled'),
            ];
        }

        // Regra simplificada: só pode alterar se TODOS os pedidos (exceto cancelados) estão entregues
        $activeOrders = $this->check->orders->whereNotIn('status', ['canceled']);
        $allDelivered = $activeOrders->every(fn($order) => $order->status === 'completed');
        $hasIncompleteOrders = !$allDelivered && $activeOrders->count() > 0;

        // PIX Generation
        $pixPayload = null;
        $globalSettingService = app(\App\Services\GlobalSettingService::class);
        $pixKey = $globalSettingService->getSetting('pix_key');

        if ($pixKey) {
            // Calculate total based on the same logic as view
            if ($this->check->status === 'Closed' || $this->check->status === 'Paid') {
                $checkOrders = $this->check->orders->where('status', 'completed');
            } else {
                $checkOrders = $this->check->orders->whereNotIn('status', ['pending', 'canceled']);
            }
            $checkTotal = $checkOrders->sum(fn($order) => $order->product->price);

            if ($checkTotal > 0) {
                $pixKeyType = $globalSettingService->getSetting('pix_key_type', 'CPF');
                $pixName = $globalSettingService->getSetting('pix_name');
                $pixCity = $globalSettingService->getSetting('pix_city');


                $pixPayload = $this->pixService->generatePayload(
                    $pixKey,
                    $pixKeyType,
                    $pixName,
                    $pixCity,
                    $checkTotal,
                    $this->check->id // Use Check ID as transaction ID
                );
            }
        }

        return view('livewire.check', [
            'groupedOrders' => $groupedOrders,
            'hasIncompleteOrders' => $hasIncompleteOrders,
            'pixPayload' => $pixPayload,
        ]);
    }
}
