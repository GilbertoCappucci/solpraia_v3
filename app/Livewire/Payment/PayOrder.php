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

    public function render()
    {
        return view('livewire.payment.pay-order');
    }
}
