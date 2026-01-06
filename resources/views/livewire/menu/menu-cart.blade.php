<div>
@if(count($cart) > 0)
<div class="fixed bottom-0 left-0 right-0 bg-gradient-to-r from-orange-500 to-red-500 shadow-2xl p-4 z-50">
    <div class="flex items-center justify-between mb-3">
        <div class="text-white">
            <p class="text-sm opacity-90">{{ $this->cartItemCount }} {{ $this->cartItemCount > 1 ? 'itens' : 'item' }}</p>
            <p class="text-2xl font-bold">R$ {{ number_format($this->cartTotal, 2, ',', '.') }}</p>
        </div>
        <button
            wire:click="clearCart"
            class="text-white text-sm underline opacity-90 hover:opacity-100 transition">
            Limpar Tudo
        </button>
    </div>
    <button
        wire:click="confirmOrder"
        class="w-full bg-white text-orange-600 font-bold py-3.5 rounded-lg hover:bg-gray-50 transition shadow-lg text-lg">
        Confirmar Pedido
    </button>
</div>
@endif
</div>
