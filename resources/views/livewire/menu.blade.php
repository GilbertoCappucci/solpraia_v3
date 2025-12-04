<div>
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

    {{-- Header Compacto com Info do Local --}}
    <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white p-3 flex items-center justify-between sticky top-0 z-40 shadow-md">
        <div class="flex items-center gap-2">
            <button 
                wire:click="backToOrders"
                class="p-1.5 hover:bg-white/20 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold">{{ $selectedTable->number }}</span>
                <span class="text-sm opacity-90">{{ $selectedTable->name }}</span>
            </div>
        </div>
        @if($currentCheck && $currentCheck->total > 0)
            <div class="text-right">
                <p class="text-xl font-bold">R$ {{ number_format($currentCheck->total, 2, ',', '.') }}</p>
            </div>
        @endif
    </div>

    {{-- Barra de Busca --}}
    <div class="bg-white p-4 sticky top-14 z-30 shadow-sm">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="searchTerm"
            placeholder="Buscar produtos..."
            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
    </div>

    {{-- Categorias Principais (Pai) --}}
    <div class="px-4 py-3 bg-white border-b sticky top-28 z-20 overflow-x-auto">
        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Categoria Principal</p>
        <div class="flex gap-2">
            <button 
                wire:click="selectParentCategory(null)"
                class="px-4 py-2 rounded-full whitespace-nowrap text-sm font-medium transition shadow-sm
                    {{ !$selectedParentCategoryId && !$showFavoritesOnly ? 'bg-orange-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300' }}">
                Todas
            </button>
            <button 
                wire:click="selectFavorites"
                class="px-4 py-2 rounded-full whitespace-nowrap text-sm font-medium transition shadow-sm flex items-center gap-1
                    {{ $showFavoritesOnly ? 'bg-yellow-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300' }}">
                <svg class="w-4 h-4" fill="{{ $showFavoritesOnly ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                Favoritos
            </button>
            @foreach($parentCategories as $category)
                <button 
                    wire:click="selectParentCategory({{ $category->id }})"
                    class="px-4 py-2 rounded-full whitespace-nowrap text-sm font-medium transition shadow-sm
                        {{ $selectedParentCategoryId == $category->id ? 'bg-orange-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300' }}">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Categorias Secundárias (Filhas) --}}
    @if($selectedParentCategoryId && !$showFavoritesOnly && count($childCategories) > 0)
        <div class="px-4 py-3 bg-gray-50 border-b sticky top-48 z-20 overflow-x-auto">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Categoria Secundária</p>
            <div class="flex gap-2">
                <button 
                    wire:click="selectChildCategory(null)"
                    class="px-4 py-2 rounded-full whitespace-nowrap text-sm font-medium transition shadow-sm
                        {{ !$selectedChildCategoryId ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300' }}">
                    Todas
                </button>
                @foreach($childCategories as $category)
                    <button 
                        wire:click="selectChildCategory({{ $category->id }})"
                        class="px-4 py-2 rounded-full whitespace-nowrap text-sm font-medium transition shadow-sm
                            {{ $selectedChildCategoryId == $category->id ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300' }}">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Lista de Produtos --}}
    <div class="p-4 pb-32 bg-gray-50">
        @if($products->count() > 0)
            <div class="space-y-3">
                @foreach($products as $product)
                    <div class="bg-white rounded-xl p-4 flex items-center gap-3 shadow-sm hover:shadow-md transition">
                        <div class="flex-1">
                            <h3 class="font-semibold text-base text-gray-800">{{ $product->name }}</h3>
                            @if($product->description)
                                <p class="text-sm text-gray-500 line-clamp-2 mt-1">{{ $product->description }}</p>
                            @endif
                            <p class="text-lg font-bold text-orange-600 mt-2">
                                R$ {{ number_format($product->price, 2, ',', '.') }}
                            </p>
                        </div>
                        
                        @if(isset($cart[$product->id]))
                            <div class="flex items-center gap-3">
                                <button 
                                    wire:click="removeFromCart({{ $product->id }})"
                                    class="w-9 h-9 bg-red-500 text-white rounded-lg flex items-center justify-center hover:bg-red-600 transition shadow">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                </button>
                                <span class="font-bold text-lg w-8 text-center">{{ $cart[$product->id]['quantity'] }}</span>
                                <button 
                                    wire:click="addToCart({{ $product->id }})"
                                    class="w-9 h-9 bg-green-500 text-white rounded-lg flex items-center justify-center hover:bg-green-600 transition shadow">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </button>
                            </div>
                        @else
                            <button 
                                wire:click="addToCart({{ $product->id }})"
                                class="bg-gradient-to-r from-orange-500 to-red-500 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:shadow-lg transition">
                                Adicionar
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-16 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <p class="text-base font-medium">Nenhum produto encontrado</p>
            </div>
        @endif
    </div>

    {{-- Carrinho Fixo (Bottom Sheet) --}}
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
