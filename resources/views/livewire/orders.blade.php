<div wire:poll.{{ $pollingInterval }}ms>
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

    {{-- Seleção de Local --}}
    @if(!$selectedTable)
        <div class="p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold">Selecione o Local</h2>
                <button 
                    wire:click="openNewTableModal"
                    class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg text-sm font-medium hover:shadow-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Criar Novo
                </button>
            </div>
            <div class="grid grid-cols-3 gap-3">
                @foreach($tables as $table)
                    <button 
                        wire:click="selectTable({{ $table->id }})"
                        class="relative aspect-square bg-white rounded-xl shadow-md hover:shadow-lg transition flex flex-col items-center justify-center border-2
                            @if($table->checkStatusColor === 'green')
                                border-green-400 hover:border-green-500
                            @elseif($table->checkStatusColor === 'yellow')
                                border-yellow-400 hover:border-yellow-500
                            @elseif($table->checkStatusColor === 'red')
                                border-red-400 hover:border-red-500
                            @else
                                border-gray-300 hover:border-gray-400
                            @endif">
                        
                        {{-- Badge Check Status (topo esquerdo) --}}
                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md text-xs font-bold uppercase
                            @if($table->checkStatusColor === 'green')
                                bg-green-100 text-green-700
                            @elseif($table->checkStatusColor === 'yellow')
                                bg-yellow-100 text-yellow-700
                            @elseif($table->checkStatusColor === 'red')
                                bg-red-100 text-red-700
                            @else
                                bg-gray-100 text-gray-600
                            @endif">
                            {{ $table->checkStatusLabel }}
                        </span>
                        
                        {{-- Badge Pedidos Prontos (topo direito) --}}
                        @if($table->hasReadyOrders)
                            <span class="absolute top-2 right-2 flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-green-500 text-white animate-pulse">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                                {{ $table->ordersReady }}
                            </span>
                        @endif
                        
                        <span class="text-3xl font-bold text-gray-900">{{ $table->number }}</span>
                        
                        {{-- Valor Total do Check --}}
                        @if($table->checkTotal > 0)
                            <div class="text-sm font-semibold text-orange-600">
                                R$ {{ number_format($table->checkTotal, 2, ',', '.') }}
                            </div>
                        @endif
                        
                        <span class="text-xs text-gray-600 font-medium mt-1">{{ $table->name }}</span>
                        
                        {{-- Indicadores de Status dos Pedidos --}}
                        @if($table->checkStatus)
                            <div class="flex items-center justify-center gap-3 mt-2">
                                @if($table->ordersPending > 0)
                                    <div class="flex items-center gap-1">
                                        <span class="w-3 h-3 bg-yellow-500 rounded-full" title="{{ $table->ordersPending }} aguardando"></span>
                                        <span class="text-xs font-semibold text-yellow-700">{{ $table->pendingMinutes }}m</span>
                                    </div>
                                @endif
                                @if($table->ordersInProduction > 0)
                                    <div class="flex items-center gap-1">
                                        <span class="w-3 h-3 bg-blue-500 rounded-full" title="{{ $table->ordersInProduction }} em preparo"></span>
                                        <span class="text-xs font-semibold text-blue-700">{{ $table->productionMinutes }}m</span>
                                    </div>
                                @endif
                                @if($table->ordersReady > 0)
                                    <div class="flex items-center gap-1">
                                        <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse" title="{{ $table->ordersReady }} pronto"></span>
                                        <span class="text-xs font-semibold text-green-700">{{ $table->readyMinutes }}m</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Modal Criar Novo Local --}}
        @if($showNewTableModal)
            <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeNewTableModal">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6" wire:click.stop>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-900">Criar Novo Local</h3>
                        <button wire:click="closeNewTableModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                            <input 
                                type="number" 
                                wire:model="newTableNumber"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                placeholder="Ex: 1">
                            @error('newTableNumber') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                            <input 
                                type="text" 
                                wire:model="newTableName"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                placeholder="Ex: Varanda">
                            @error('newTableName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="flex gap-3 pt-2">
                            <button 
                                wire:click="closeNewTableModal"
                                class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Cancelar
                            </button>
                            <button 
                                wire:click="createNewTable"
                                class="flex-1 px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:shadow-lg transition">
                                Criar Local
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
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
