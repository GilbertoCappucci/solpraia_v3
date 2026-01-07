<?php

namespace App\Livewire\Payment;

use App\Models\Check;
use App\Models\Order;
use App\Models\Table;
use Livewire\Component;
use App\Services\Payment\PaymentService;

class PayOrder extends Component
{

    public Table $table;
    public Order $order;
    public Check $check;
    public $checkOrders;
    public float $checkTotal = 0.0;
    public $pix_enabled;
    public $pixPayload;
    public $pixKey;

    public PaymentService $paymentService;

    public function mount($orderId)
    {
        $this->order = Order::find($orderId);
        $this->table = Table::find($this->order->check->table->id);
        
        $orders = collect([$this->order]);
        $this->checkOrders = $orders
            ->groupBy(function ($order) {
                return $order->product->name;
            });

        $this->checkTotal = $orders->sum(function ($order) {
            return $order->price * $order->quantity;
        });

        $this->pix_enabled = config('payment.pix_enabled');

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
        return view('livewire.payment.pay-order');
    }
}
