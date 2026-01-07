<?php

namespace App\Livewire\Payment;

use Livewire\Component;

class PayOrder extends Component
{

    public $orderId;
    
    public function mount($orderId)
    {
        $this->orderId = $orderId;
    }

    public function processPayment()
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
