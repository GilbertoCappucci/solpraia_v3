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

        {{-- Card Aguardando --}}
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-yellow-400 overflow-hidden">
            <div class="bg-yellow-50 px-4 py-2 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-yellow-500 rounded-full"></span>
                    <span class="font-bold text-yellow-800">AGUARDANDO</span>
                    <span class="text-sm text-yellow-700">({{ $pendingOrders->count() }})</span>
                </div>
                @if($pendingOrders->count() > 0)
                    <span class="text-sm font-bold text-yellow-800 bg-yellow-200 px-2 py-1 rounded">{{ $pendingTime }}m</span>
                @endif
            </div>
            @if($pendingOrders->count() > 0)
                <div class="p-3 space-y-2">
                    @foreach($pendingOrders as $order)
                        <div class="flex items-center justify-between py-1 border-b border-gray-100 last:border-0">
                            <div class="flex items-center gap-2 flex-1">
                                <span class="text-lg font-semibold text-gray-700">{{ $order->quantity }}x</span>
                                <span class="text-sm text-gray-800">{{ $order->product->name }}</span>
                            </div>
                            <button 
                                wire:click="updateOrderStatus({{ $order->id }}, 'in_production')"
                                class="p-1 hover:bg-yellow-100 rounded transition">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    @endforeach
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
                @if($inProductionOrders->count() > 0)
                    <span class="text-sm font-bold text-blue-800 bg-blue-200 px-2 py-1 rounded">{{ $inProductionTime }}m</span>
                @endif
            </div>
            @if($inProductionOrders->count() > 0)
                <div class="p-3 space-y-2">
                    @foreach($inProductionOrders as $order)
                        <div class="flex items-center justify-between py-1 border-b border-gray-100 last:border-0">
                            <div class="flex items-center gap-2 flex-1">
                                <span class="text-lg font-semibold text-gray-700">{{ $order->quantity }}x</span>
                                <span class="text-sm text-gray-800">{{ $order->product->name }}</span>
                            </div>
                            <button 
                                wire:click="updateOrderStatus({{ $order->id }}, 'in_transit')"
                                class="p-1 hover:bg-blue-100 rounded transition">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-3 text-center text-sm text-gray-500">
                    Nenhum pedido em preparo
                </div>
            @endif
        </div>

        {{-- Card Em Trânsito --}}
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-purple-400 overflow-hidden">
            <div class="bg-purple-50 px-4 py-2 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-purple-500 rounded-full animate-pulse"></span>
                    <span class="font-bold text-purple-800">EM TRÂNSITO</span>
                    <span class="text-sm text-purple-700">({{ $inTransitOrders->count() }})</span>
                </div>
                @if($inTransitOrders->count() > 0)
                    <span class="text-sm font-bold text-purple-800 bg-purple-200 px-2 py-1 rounded">{{ $inTransitTime }}m</span>
                @endif
            </div>
            @if($inTransitOrders->count() > 0)
                <div class="p-3 space-y-2">
                    @foreach($inTransitOrders as $order)
                        <div class="flex items-center justify-between py-1 border-b border-gray-100 last:border-0">
                            <div class="flex items-center gap-2 flex-1">
                                <span class="text-lg font-semibold text-gray-700">{{ $order->quantity }}x</span>
                                <span class="text-sm text-gray-800">{{ $order->product->name }}</span>
                            </div>
                            <button 
                                wire:click="updateOrderStatus({{ $order->id }}, 'completed')"
                                class="p-1 hover:bg-purple-100 rounded transition">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-3 text-center text-sm text-gray-500">
                    Nenhum pedido em trânsito
                </div>
            @endif
        </div>

        {{-- Card Entregue --}}
        <div class="bg-white rounded-xl shadow-sm border-l-4 border-green-400 overflow-hidden">
            <div class="bg-green-50 px-4 py-2 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                    <span class="font-bold text-green-800">ENTREGUE</span>
                    <span class="text-sm text-green-700">({{ $completedOrders->count() }})</span>
                </div>
                @if($completedOrders->count() > 0)
                    <span class="text-sm font-bold text-green-800 bg-green-200 px-2 py-1 rounded">{{ $completedTime }}m</span>
                @endif
            </div>
            @if($completedOrders->count() > 0)
                <div class="p-3 space-y-2">
                    @foreach($completedOrders as $order)
                        <div class="flex items-center justify-between py-1 border-b border-gray-100 last:border-0">
                            <div class="flex items-center gap-2 flex-1">
                                <span class="text-lg font-semibold text-gray-700">{{ $order->quantity }}x</span>
                                <span class="text-sm text-gray-800">{{ $order->product->name }}</span>
                            </div>
                            <span class="text-sm font-bold text-orange-600">R$ {{ number_format($order->product->price * $order->quantity, 2, ',', '.') }}</span>
                        </div>
                    @endforeach
                    <div class="pt-2 flex justify-between items-center border-t-2 border-green-200">
                        <span class="text-xs font-semibold text-gray-600">SUBTOTAL</span>
                        <span class="text-base font-bold text-green-700">R$ {{ number_format($completedTotal, 2, ',', '.') }}</span>
                    </div>
                </div>
            @else
                <div class="p-3 text-center text-sm text-gray-500">
                    Nenhum pedido entregue
                </div>
            @endif
        </div>
    </div>

    {{-- Botão Adicionar Pedidos --}}
    <div class="p-4 bg-white">
        <button 
            wire:click="goToMenu"
            class="w-full bg-gradient-to-r from-orange-500 to-red-500 text-white py-4 rounded-xl font-bold text-lg flex items-center justify-center gap-3 hover:shadow-lg transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Adicionar Pedidos
        </button>
    </div>

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
