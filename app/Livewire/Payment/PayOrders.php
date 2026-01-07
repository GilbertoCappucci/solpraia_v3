<?php

namespace App\Livewire\Payment;

use App\Models\Check;
use App\Models\Order;
use App\Models\Table;
use App\Services\GlobalSettingService;
use Livewire\Component;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\Auth;

class PayOrders extends Component
{

    public $table;
    public $orders;
    public float $checkTotal = 0.0;
    public $pix_enabled;
    public $pixPayload;
    public $pixKey;
    
    protected GlobalSettingService $globalSettings;
    protected PaymentService $paymentService;

    public function mount(GlobalSettingService $globalSettings, PaymentService $paymentService)
    {
        $userId = Auth::user()->user_id;
        $this->globalSettings = $globalSettings;
        $this->paymentService = $paymentService;

        $this->orders = session('pay_orders', []);

        if (empty($this->orders)) {
            session()->flash('error', 'Nenhum pedido encontrado.');
            return redirect()->route('tables');
        }

        // Get table from first order
        $firstOrder = Order::with('check.table')->whereIn('id', $this->orders)->first();
        if (!$firstOrder) {
            session()->flash('error', 'Nenhum pedido encontrado.');
            return redirect()->route('tables');
        }
        
        $this->table = $firstOrder->check->table;

        // Calculate total using CheckService
        $checkService = app(\App\Services\CheckService::class);
        $this->checkTotal = $checkService->calculateTotalOrders($this->orders);

        $this->pix_enabled = $this->globalSettings->getPixEnabled($userId);
        $this->pixKey = $this->globalSettings->getPixKey($userId);
        $this->pixPayload = $this->paymentService->qrCodeOrders($this->orders);
        
    }

    public function goBack()
    {
        return redirect()->route('orders', $this->table->id);
    }

    public function goToOrders()
    {
        return redirect()->route('orders', $this->table->id);
    }

    public function openStatusModal()
    {
        // TODO: Implement status modal logic
        session()->flash('info', 'Status modal não implementado ainda.');
    }

    public function processPayment($orders)
    {
        // Lógica para processar o pagamento do pedido
        // Isso pode incluir integração com gateway de pagamento, atualização do status do pedido, etc.

        $order = \App\Models\Order::find($this->orderId);
        if (!$order) {
            session()->flash('error', 'Pedido não encontrado.');
            return;
        }

        $order->is_paid = true;
        $order->save();
        
        $paymentService = new PaymentService();
        $paymentService->processOrderPayment($this->orderId);

        // Exemplo simples de feedback
        session()->flash('message', 'Pagamento processado com sucesso para o pedido #' . $this->orderId);

        // Redirecionar ou atualizar a interface conforme necessário
        return redirect()->route('tables');
    }

    public function render()
    {
        // Calculate checkOrders on render to avoid Livewire serialization issues
        $checkOrders = Order::with(['product', 'currentStatusHistory'])
            ->whereIn('id', $this->orders)
            ->get()
            ->groupBy(fn ($order) => $order->product->name);

        return view('livewire.payment.pay-orders', [
            'checkOrders' => $checkOrders
        ]);
    }
}
