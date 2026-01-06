<div class="bg-white rounded-xl p-4 flex items-center gap-3 shadow-sm hover:shadow-md transition">
    <div class="flex-1">
        <h3 class="font-semibold text-base text-gray-800">{{ $product['name'] }}</h3>
        @if($product['description'])
        <p class="text-sm text-gray-500 line-clamp-2 mt-1">{{ $product['description'] }}</p>
        @endif
        <p class="text-lg font-bold text-orange-600 mt-2">
            R$ {{ number_format($product['price'], 2, ',', '.') }}
        </p>
    </div>

    @if($this->isInCart)
    <div class="flex items-center gap-3">
        <button
            wire:click="removeFromCart"
            class="w-9 h-9 bg-red-500 text-white rounded-lg flex items-center justify-center hover:bg-red-600 transition shadow">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
            </svg>
        </button>
        <span class="font-bold text-lg w-8 text-center">{{ $this->cartQuantity }}</span>
        
        @if($this->limitReached)
        <button
            disabled
            class="w-9 h-9 bg-gray-400 text-white rounded-lg flex items-center justify-center cursor-not-allowed font-bold text-sm">
            ND
        </button>
        @else
        <button
            wire:click="addToCart"
            class="w-9 h-9 bg-green-500 text-white rounded-lg flex items-center justify-center hover:bg-green-600 transition shadow">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
        </button>
        @endif
    </div>
    @else
    @if(!$this->hasStock)
    <button
        disabled
        class="bg-gray-400 text-white px-5 py-2.5 rounded-lg text-sm font-semibold cursor-not-allowed">
        Sem Estoque
    </button>
    @else
    <button
        wire:click="addToCart"
        class="bg-gradient-to-r from-orange-500 to-red-500 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:shadow-lg transition">
        Adicionar
    </button>
    @endif
    @endif
</div>
