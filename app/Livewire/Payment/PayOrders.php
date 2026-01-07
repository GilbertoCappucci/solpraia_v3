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

    public $checkOrders;
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

        $this->checkOrders = Order::with('product')
            ->whereIn('id', $this->orders)
            ->get()
            ->groupBy(fn ($order) => $order->product->name);

        $firstCollection = $this->checkOrders->first();
        $firstOrder = $firstCollection->first();
        $table = $firstOrder->check->table;

        $this->table = Table::find($table->id);

        $this->checkTotal = $this->checkOrders->first()->sum(function ($order) {
            return $order->price * $order->quantity;
        });

        $this->pix_enabled = $this->globalSettings->getPixEnabled($userId);
        $this->pixKey = $this->globalSettings->getPixKey($userId);
        $this->pixPayload = $this->paymentService->qrCodeOrders($this->orders);
        
    }

    public function goBack()
    {
        return redirect()->route('orders', $this->order->check->table->id);
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
        return view('livewire.payment.pay-orders');
    }
}
