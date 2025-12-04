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
                wire:click="backToTables"
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
        <div class="flex items-center gap-3">
            @if($currentCheck && $currentCheck->total > 0)
                <div class="text-right">
                    <p class="text-xl font-bold">R$ {{ number_format($currentCheck->total, 2, ',', '.') }}</p>
                </div>
            @endif
            <button 
                wire:click="openStatusModal"
                class="p-1.5 hover:bg-white/20 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Seção de Pedidos Ativos --}}
    <div class="bg-gray-50 p-4 space-y-3">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                PEDIDOS ATIVOS
                <span class="text-sm font-normal text-gray-600">({{ $pendingOrders->count() + $inProductionOrders->count() + $readyOrders->count() }})</span>
            </h2>
        </div>

        {{-- Card Aguardando --}}
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-yellow-400 overflow-hidden">
            <div class="bg-yellow-50 px-4 py-2 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-yellow-500 rounded-full"></span>
                    <span class="font-bold text-yellow-800">AGUARDANDO</span>
                    <span class="text-sm text-yellow-700">({{ $pendingOrders->count() }})</span>
                </div>
                @if($pendingTime > 0)
                    <span class="text-xs font-semibold text-yellow-700">{{ $pendingTime }}min</span>
                @endif
            </div>
            @if($pendingOrders->count() > 0)
                <div class="p-3 space-y-2">
                    @foreach($pendingOrders as $order)
                        <div class="flex items-center justify-between py-1 border-b border-gray-100 last:border-0">
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-semibold text-gray-700">{{ $order->quantity }}x</span>
                                <span class="text-sm text-gray-800">{{ $order->product->name }}</span>
                            </div>
                            <span class="text-sm font-bold text-orange-600">R$ {{ number_format($order->product->price * $order->quantity, 2, ',', '.') }}</span>
                        </div>
                    @endforeach
                    <div class="pt-2 flex justify-between items-center border-t-2 border-yellow-200">
                        <span class="text-xs font-semibold text-gray-600">SUBTOTAL</span>
                        <span class="text-base font-bold text-yellow-700">R$ {{ number_format($pendingTotal, 2, ',', '.') }}</span>
                    </div>
                </div>
            @else
                <div class="p-3 text-center text-sm text-gray-500">
                    Nenhum pedido aguardando
                </div>
            @endif
        </div>

        {{-- Card Em Preparo --}}
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-blue-400 overflow-hidden">
            <div class="bg-blue-50 px-4 py-2 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                    <span class="font-bold text-blue-800">EM PREPARO</span>
                    <span class="text-sm text-blue-700">({{ $inProductionOrders->count() }})</span>
                </div>
                @if($inProductionTime > 0)
                    <span class="text-xs font-semibold text-blue-700">{{ $inProductionTime }}min</span>
                @endif
            </div>
            @if($inProductionOrders->count() > 0)
                <div class="p-3 space-y-2">
                    @foreach($inProductionOrders as $order)
                        <div class="flex items-center justify-between py-1 border-b border-gray-100 last:border-0">
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-semibold text-gray-700">{{ $order->quantity }}x</span>
                                <span class="text-sm text-gray-800">{{ $order->product->name }}</span>
                            </div>
                            <span class="text-sm font-bold text-orange-600">R$ {{ number_format($order->product->price * $order->quantity, 2, ',', '.') }}</span>
                        </div>
                    @endforeach
                    <div class="pt-2 flex justify-between items-center border-t-2 border-blue-200">
                        <span class="text-xs font-semibold text-gray-600">SUBTOTAL</span>
                        <span class="text-base font-bold text-blue-700">R$ {{ number_format($inProductionTotal, 2, ',', '.') }}</span>
                    </div>
                </div>
            @else
                <div class="p-3 text-center text-sm text-gray-500">
                    Nenhum pedido em preparo
                </div>
            @endif
        </div>

        {{-- Card Pronto --}}
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-green-400 overflow-hidden">
            <div class="bg-green-50 px-4 py-2 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span>
                    <span class="font-bold text-green-800">PRONTO</span>
                    <span class="text-sm text-green-700">({{ $readyOrders->count() }})</span>
                </div>
                @if($readyTime > 0)
                    <span class="text-xs font-semibold text-green-700">{{ $readyTime }}min</span>
                @endif
            </div>
            @if($readyOrders->count() > 0)
                <div class="p-3 space-y-2">
                    @foreach($readyOrders as $order)
                        <div class="flex items-center justify-between py-1 border-b border-gray-100 last:border-0">
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-semibold text-gray-700">{{ $order->quantity }}x</span>
                                <span class="text-sm text-gray-800">{{ $order->product->name }}</span>
                            </div>
                            <span class="text-sm font-bold text-orange-600">R$ {{ number_format($order->product->price * $order->quantity, 2, ',', '.') }}</span>
                        </div>
                    @endforeach
                    <div class="pt-2 flex justify-between items-center border-t-2 border-green-200">
                        <span class="text-xs font-semibold text-gray-600">SUBTOTAL</span>
                        <span class="text-base font-bold text-green-700">R$ {{ number_format($readyTotal, 2, ',', '.') }}</span>
                    </div>
                </div>
            @else
                <div class="p-3 text-center text-sm text-gray-500">
                    Nenhum pedido pronto
                </div>
            @endif
        </div>
    </div>

    {{-- Divisor --}}
    <div class="bg-gray-200 h-2"></div>

    {{-- Seção Adicionar Novos Pedidos --}}
    <div class="bg-white">
        <div class="p-4 border-b">
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                ADICIONAR PEDIDOS
            </h2>

            {{-- Barra de Busca --}}
            <input 
                type="text" 
                wire:model.live.debounce.300ms="searchTerm"
                placeholder="Buscar produtos..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
        </div>

        {{-- Categorias --}}
        <div class="px-4 py-3 bg-gray-50 border-b overflow-x-auto">
            <div class="flex gap-2">
                <button 
                    wire:click="selectCategory(null)"
                    class="px-3 py-1.5 rounded-full whitespace-nowrap text-xs font-medium transition
                        {{ !$selectedCategoryId ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    Todos
                </button>
                @foreach($categories as $category)
                    <button 
                        wire:click="selectCategory({{ $category->id }})"
                        class="px-3 py-1.5 rounded-full whitespace-nowrap text-xs font-medium transition
                            {{ $selectedCategoryId == $category->id ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Lista de Produtos --}}
    <div class="p-4 pb-32 bg-white">
        @if($products->count() > 0)
            <div class="space-y-2">
                @foreach($products as $product)
                    <div class="bg-gray-50 rounded-lg p-3 flex items-center gap-3 border border-gray-200 hover:border-orange-300 transition">
                        <div class="flex-1">
                            <h3 class="font-semibold text-sm text-gray-800">{{ $product->name }}</h3>
                            @if($product->description)
                                <p class="text-xs text-gray-500 line-clamp-1">{{ $product->description }}</p>
                            @endif
                            <p class="text-base font-bold text-orange-600 mt-0.5">
                                R$ {{ number_format($product->price, 2, ',', '.') }}
                            </p>
                        </div>
                        
                        @if(isset($cart[$product->id]))
                            <div class="flex items-center gap-2">
                                <button 
                                    wire:click="removeFromCart({{ $product->id }})"
                                    class="w-7 h-7 bg-red-500 text-white rounded-lg flex items-center justify-center hover:bg-red-600 transition">
                                    <span class="text-base">-</span>
                                </button>
                                <span class="font-bold text-base w-6 text-center">{{ $cart[$product->id]['quantity'] }}</span>
                                <button 
                                    wire:click="addToCart({{ $product->id }})"
                                    class="w-7 h-7 bg-green-500 text-white rounded-lg flex items-center justify-center hover:bg-green-600 transition">
                                    <span class="text-base">+</span>
                                </button>
                            </div>
                        @else
                            <button 
                                wire:click="addToCart({{ $product->id }})"
                                class="bg-orange-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-orange-600 transition">
                                Adicionar
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <p class="text-sm">Nenhum produto encontrado</p>
            </div>
        @endif
    </div>

    {{-- Carrinho Fixo (Bottom Sheet) - Novos Itens --}}
    @if(count($cart) > 0)
        <div class="fixed bottom-0 left-0 right-0 bg-gradient-to-r from-orange-500 to-red-500 shadow-2xl p-3 z-50">
            <div class="flex items-center justify-between mb-2">
                <div class="text-white">
                    <p class="text-xs opacity-90">{{ $this->cartItemCount }} {{ $this->cartItemCount > 1 ? 'novos itens' : 'novo item' }}</p>
                    <p class="text-xl font-bold">R$ {{ number_format($this->cartTotal, 2, ',', '.') }}</p>
                </div>
                <button 
                    wire:click="clearCart"
                    class="text-white text-xs underline opacity-90 hover:opacity-100">
                    Limpar
                </button>
            </div>
            <button 
                wire:click="confirmOrder"
                class="w-full bg-white text-orange-600 font-bold py-3 rounded-lg hover:bg-gray-100 transition">
                Confirmar Pedido
            </button>
        </div>
    @endif

    {{-- Modal Alterar Status --}}
    @if($showStatusModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeStatusModal">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6" wire:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Alterar Status</h3>
                    <button wire:click="closeStatusModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    {{-- Status da Mesa --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status da Mesa</label>
                        <div class="flex flex-wrap gap-2">
                            <button 
                                wire:click="$set('newTableStatus', 'free')"
                                class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $newTableStatus === 'free' ? 'bg-gray-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                Livre
                            </button>
                            <button 
                                wire:click="$set('newTableStatus', 'occupied')"
                                class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $newTableStatus === 'occupied' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                Ocupada
                            </button>
                            <button 
                                wire:click="$set('newTableStatus', 'reserved')"
                                class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $newTableStatus === 'reserved' ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                Reservada
                            </button>
                        </div>
                    </div>

                    {{-- Status do Check --}}
                    @if($currentCheck)
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status do Check</label>
                            <div class="flex flex-wrap gap-2">
                                <button 
                                    wire:click="$set('newCheckStatus', 'Open')"
                                    class="px-3 py-2 rounded-lg text-sm font-medium transition
                                        {{ $newCheckStatus === 'Open' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                    Aberto
                                </button>
                                <button 
                                    wire:click="$set('newCheckStatus', 'Closing')"
                                    class="px-3 py-2 rounded-lg text-sm font-medium transition
                                        {{ $newCheckStatus === 'Closing' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                    Fechando
                                </button>
                                <button 
                                    wire:click="$set('newCheckStatus', 'Closed')"
                                    class="px-3 py-2 rounded-lg text-sm font-medium transition
                                        {{ $newCheckStatus === 'Closed' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                    Fechado
                                </button>
                                <button 
                                    wire:click="$set('newCheckStatus', 'Paid')"
                                    class="px-3 py-2 rounded-lg text-sm font-medium transition
                                        {{ $newCheckStatus === 'Paid' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                    Pago
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex gap-2 mt-6">
                    <button 
                        wire:click="closeStatusModal"
                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition">
                        Cancelar
                    </button>
                    <button 
                        wire:click="updateStatuses"
                        class="flex-1 px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg font-medium hover:shadow-lg transition">
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
