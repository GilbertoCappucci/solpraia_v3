<?php

namespace App\Livewire\Menu;

use App\Services\StockService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class MenuProductCard extends Component
{
    public $product;
    public $adminId;

    #[Reactive]
    public $cart = [];

    protected $stockService;

    public function boot(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    #[Computed]
    public function stockQty()
    {
        return $this->product['stock']['quantity'] ?? 0;
    }

    #[Computed]
    public function isUnlimited()
    {
        return $this->stockQty < 0;
    }

    #[Computed]
    public function isInCart()
    {
        return isset($this->cart[$this->product['id']]);
    }

    #[Computed]
    public function cartQuantity()
    {
        return $this->cart[$this->product['id']]['quantity'] ?? 0;
    }

    #[Computed]
    public function limitReached()
    {
        return !$this->isUnlimited && ($this->cartQuantity >= $this->stockQty);
    }

    #[Computed]
    public function hasStock()
    {
        return $this->stockQty !== 0;
    }

    public function addToCart()
    {
        $this->dispatch('add-to-cart', productId: $this->product['id']);
    }

    public function removeFromCart()
    {
        $this->dispatch('remove-from-cart', productId: $this->product['id']);
    }

    public function render()
    {
        return view('livewire.menu.menu-product-card');
    }
}
