<div class="min-h-screen bg-gray-50">
    {{-- Header Mobile --}}
    <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white p-4 sticky top-0 z-50 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold">Pedidos</h1>
                @if($selectedTable)
                    <p class="text-sm opacity-90">Mesa {{ $selectedTable->number }}</p>
                @else
                    <p class="text-sm opacity-90">Selecione uma mesa</p>
                @endif
            </div>
            @if($selectedTable)
                <button wire:click="selectTable(null)" class="bg-white/20 px-3 py-1 rounded-lg text-sm">
                    Trocar Mesa
                </button>
            @endif
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="bg-green-500 text-white px-4 py-3 text-center">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-500 text-white px-4 py-3 text-center">
            {{ session('error') }}
        </div>
    @endif

    {{-- Seleção de Mesa --}}
    @if(!$selectedTable)
        <div class="p-4">
            <h2 class="text-lg font-semibold mb-3">Selecione a Mesa</h2>
            <div class="grid grid-cols-3 gap-3">
                @foreach($tables as $table)
                    <button 
                        wire:click="selectTable({{ $table->id }})"
                        class="aspect-square bg-white rounded-xl shadow-md hover:shadow-lg transition flex flex-col items-center justify-center border-2 border-gray-200 hover:border-orange-500">
                        <svg class="w-8 h-8 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-lg font-bold">{{ $table->number }}</span>
                        <span class="text-xs text-gray-500">{{ $table->name }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    @else
        {{-- Barra de Busca --}}
        <div class="p-4 bg-white border-b">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="searchTerm"
                placeholder="Buscar produtos..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
        </div>

        {{-- Categorias --}}
        <div class="p-4 bg-white border-b overflow-x-auto">
            <div class="flex gap-2 pb-2">
                <button 
                    wire:click="selectCategory(null)"
                    class="px-4 py-2 rounded-full whitespace-nowrap text-sm font-medium transition
                        {{ !$selectedCategoryId ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    Todos
                </button>
                @foreach($categories as $category)
                    <button 
                        wire:click="selectCategory({{ $category->id }})"
                        class="px-4 py-2 rounded-full whitespace-nowrap text-sm font-medium transition
                            {{ $selectedCategoryId == $category->id ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Lista de Produtos --}}
        <div class="p-4 pb-32">
            @if($products->count() > 0)
                <div class="grid gap-3">
                    @foreach($products as $product)
                        <div class="bg-white rounded-lg shadow-md p-4 flex items-center gap-4">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-800">{{ $product->name }}</h3>
                                @if($product->description)
                                    <p class="text-sm text-gray-500 line-clamp-2">{{ $product->description }}</p>
                                @endif
                                <p class="text-lg font-bold text-orange-600 mt-1">
                                    R$ {{ number_format($product->price, 2, ',', '.') }}
                                </p>
                            </div>
                            
                            @if(isset($cart[$product->id]))
                                <div class="flex items-center gap-3">
                                    <button 
                                        wire:click="removeFromCart({{ $product->id }})"
                                        class="w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center">
                                        <span class="text-lg">-</span>
                                    </button>
                                    <span class="font-bold text-lg w-8 text-center">{{ $cart[$product->id]['quantity'] }}</span>
                                    <button 
                                        wire:click="addToCart({{ $product->id }})"
                                        class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center">
                                        <span class="text-lg">+</span>
                                    </button>
                                </div>
                            @else
                                <button 
                                    wire:click="addToCart({{ $product->id }})"
                                    class="bg-orange-500 text-white px-6 py-2 rounded-lg font-medium hover:bg-orange-600 transition">
                                    Adicionar
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="text-lg">Nenhum produto encontrado</p>
                </div>
            @endif
        </div>

        {{-- Carrinho Fixo (Bottom Sheet) --}}
        @if(count($cart) > 0)
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t-4 border-orange-500 shadow-2xl p-4 z-50">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm text-gray-600">{{ $this->cartItemCount }} {{ $this->cartItemCount > 1 ? 'itens' : 'item' }}</p>
                        <p class="text-2xl font-bold text-gray-800">R$ {{ number_format($this->cartTotal, 2, ',', '.') }}</p>
                    </div>
                    <button 
                        wire:click="clearCart"
                        class="text-red-500 text-sm underline">
                        Limpar
                    </button>
                </div>
                <button 
                    wire:click="confirmOrder"
                    class="w-full bg-gradient-to-r from-orange-500 to-red-500 text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition">
                    Confirmar Pedido
                </button>
            </div>
        @endif
    @endif
</div>
