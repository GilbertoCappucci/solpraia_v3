<?php

namespace App\Livewire\Menu;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class MenuCart extends Component
{
    #[Reactive]
    public $cart = [];

    #[Computed]
    public function cartTotal()
    {
        $total = 0;
        foreach ($this->cart as $item) {
            $total += $item['product']['price'] * $item['quantity'];
        }
        return $total;
    }

    #[Computed]
    public function cartItemCount()
    {
        $count = 0;
        foreach ($this->cart as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    public function clearCart()
    {
        $this->dispatch('clear-cart');
    }

    public function confirmOrder()
    {
        $this->dispatch('confirm-order');
    }

    public function render()
    {
        return view('livewire.menu.menu-cart');
    }
}
