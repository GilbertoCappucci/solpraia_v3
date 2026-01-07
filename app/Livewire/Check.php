<?php

namespace App\Livewire;

use App\Enums\TableStatusEnum;
use App\Services\CheckService;
use App\Services\GlobalSettingService;
use App\Services\Order\OrderService;
use App\Services\PixService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Check extends Component
{
    public $checkId;
    public $check;
    public $table;
    public $title = 'Comanda';

    public $showStatusModal = false;
    public $currentCheck = null;
    public $newCheckStatus = null;

    public $checkStatusAllowed = [];

    public $pixPayload = null;
    public $pixEnabled = false;
    public $pixKey = 0; // Para forçar re-render do QR Code

    protected $checkService;
    protected $orderService;
    protected $pixService;
    protected $globalSettingService;

    public function boot(
        CheckService $checkService,
        OrderService $orderService,
        PixService $pixService,
        GlobalSettingService $globalSettingService)
    {
        $this->checkService = $checkService;
        $this->orderService = $orderService;
        $this->pixService = $pixService;
        $this->globalSettingService = $globalSettingService;
    }

    public function mount($checkId)
    {
        $this->checkId = $checkId;

        $this->loadCheck();
        $this->pix();
    }

    public function getListeners()
    {
        return [
            'global.setting.updated' => 'refreshSetting',
        ];
    }

    public function refreshSetting($data = null)
    {
        // Atualizar configurações globais e recarregar PIX
        $this->pix();
        
        // Incrementar chave para forçar re-render do QR Code
        $this->pixKey++;
        
        logger('✅ Check: Configurações atualizadas', [
            'pixEnabled' => $this->pixEnabled,
            'pixKey' => $this->pixKey
        ]);
    }
    //Monta o PIX
    public function pix(){
        // PIX Generation
        $globalSetting = $this->globalSettingService->loadGlobalSettings(Auth::user());
        $this->pixEnabled = $globalSetting->pix_enabled;

        if ($this->pixEnabled) {
            $pixKey = $globalSetting->pix_key;
            if ($pixKey) {
                
                if ($this->check->status === 'Closed' || $this->check->status === 'Paid') {
                    $checkOrders = $this->check->orders->where('status', 'completed');
                } else {
                    $checkOrders = $this->check->orders->whereNotIn('status', ['pending', 'canceled']);
                }

                $checkTotal = $this->checkService->calculateTotal($this->check);

                //dd($checkTotal);

                if ($checkTotal > 0) {
                    $pixKeyType = $globalSetting->pix_key_type;
                    $pixName = $globalSetting->pix_name;
                    $pixCity = $globalSetting->pix_city;


                    $this->pixPayload = $this->pixService->generatePayload(
                        $pixKey,
                        $pixKeyType,
                        $pixName,
                        $pixCity,
                        $checkTotal,
                        $this->check->id // Use Check ID as transaction ID
                    );
                }
            }
        }
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

    public function setCheckStatus($status)
    {
        $this->newCheckStatus = $status;
        $this->checkStatusAllowed = $this->checkService->getAllowedCheckStatuses($this->newCheckStatus, $this->check);
        $this->updateCheckStatus();
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
            session()->flash('success', 'Check cancelado com sucesso!');
            $this->loadCheck();
            return;
        }

        // Se voltou para Open, redireciona para orders
        if ($this->newCheckStatus === 'Open') {
            session()->flash('success', 'Check reaberto!');
            return redirect()->route('orders', ['tableId' => $this->table->id]);
        }

        // Para Closed, apenas atualiza sem fechar o modal
        if ($this->newCheckStatus === 'Closed') {
            session()->flash('success', 'Check fechado com sucesso!');
            $this->loadCheck();
            return;
        }

        session()->flash('success', 'Status atualizado com sucesso!');
        $this->loadCheck();

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

        return view('livewire.check', [
            'groupedOrders' => $groupedOrders,
            'hasIncompleteOrders' => $hasIncompleteOrders,
            'pixPayload' => $this->pixPayload,
            'pix_enabled' => $this->pixEnabled,
        ]);
    }
}
