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
        <div class="flex items-center gap-2">
            <button 
                wire:click="openStatusModal"
                class="flex items-center gap-2 bg-white/10 hover:bg-white/20 rounded-lg px-3 py-1.5 transition-all">
                @php
                    $tableStatusConfig = match($selectedTable->status) {
                        'free' => ['label' => 'Livre', 'color' => 'bg-gray-400'],
                        'occupied' => ['label' => 'Ocupada', 'color' => 'bg-blue-400'],
                        'reserved' => ['label' => 'Reservada', 'color' => 'bg-purple-400'],
                        default => ['label' => 'Livre', 'color' => 'bg-gray-400']
                    };
                @endphp
                {{-- Status da Mesa --}}
                <div class="flex flex-col items-start">
                    <span class="text-[10px] opacity-75 uppercase tracking-wider">Mesa</span>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full {{ $tableStatusConfig['color'] }}"></span>
                        <span class="text-sm font-medium">{{ $tableStatusConfig['label'] }}</span>
                    </div>
                </div>
                
                {{-- Status do Check --}}
                @if($currentCheck)
                    @php
                        $checkStatusConfig = match($currentCheck->status) {
                            'Open' => ['label' => 'Aberto', 'color' => 'bg-green-400'],
                            'Closing' => ['label' => 'Fechando', 'color' => 'bg-yellow-400'],
                            'Closed' => ['label' => 'Fechado', 'color' => 'bg-red-400'],
                            'Paid' => ['label' => 'Pago', 'color' => 'bg-gray-400'],
                            'Canceled' => ['label' => 'Cancelado', 'color' => 'bg-orange-400'],
                            default => ['label' => 'Aberto', 'color' => 'bg-green-400']
                        };
                    @endphp
                    <div class="border-l border-white/30 pl-2 ml-2 flex flex-col items-start">
                        <span class="text-[10px] opacity-75 uppercase tracking-wider">Check</span>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $checkStatusConfig['color'] }}"></span>
                            <span class="text-sm font-medium">{{ $checkStatusConfig['label'] }}</span>
                        </div>
                    </div>
                @endif
                
                <svg class="w-4 h-4 opacity-75 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
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
                            <div class="flex items-center gap-1">
                                <button 
                                    wire:click="cancelOrder({{ $order->id }})"
                                    wire:confirm="Tem certeza que deseja cancelar este pedido?"
                                    class="p-1 hover:bg-red-100 rounded transition"
                                    title="Cancelar pedido">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                                <button 
                                    wire:click="updateOrderStatus({{ $order->id }}, 'in_production')"
                                    class="p-1 hover:bg-yellow-100 rounded transition"
                                    title="Mover para preparo">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>
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

    {{-- Total e Botão Adicionar Pedidos --}}
    <div class="p-4 bg-white space-y-3">
        @if($currentCheck && $currentCheck->total > 0)
            <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-xl p-4 border-2 border-orange-200">
                <div class="flex items-center justify-between">
                    <span class="text-gray-600 font-semibold">TOTAL</span>
                    <span class="text-3xl font-bold text-orange-600">R$ {{ number_format($currentCheck->total, 2, ',', '.') }}</span>
                </div>
            </div>
        @endif
        
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
                        @php
                            $hasActiveCheck = $currentCheck && in_array($currentCheck->status, ['Open', 'Closing', 'Closed']);
                        @endphp
                        @if($hasActiveCheck)
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-2">
                                <p class="text-sm text-yellow-800">
                                    <span class="font-semibold">⚠️ Atenção:</span> Não é possível alterar o status da mesa enquanto houver um check em andamento.
                                </p>
                            </div>
                        @endif
                        <div class="flex flex-wrap gap-2">
                            <button 
                                wire:click="$set('newTableStatus', 'free')"
                                @if($hasActiveCheck) disabled @endif
                                class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'free' ? 'bg-gray-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                                Livre
                            </button>
                            <button 
                                wire:click="$set('newTableStatus', 'occupied')"
                                @if($hasActiveCheck) disabled @endif
                                class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'occupied' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                                Ocupada
                            </button>
                            <button 
                                wire:click="$set('newTableStatus', 'reserved')"
                                @if($hasActiveCheck) disabled @endif
                                class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'reserved' ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                                Reservada
                            </button>
                        </div>
                    </div>

                    {{-- Status do Check --}}
                    @if($currentCheck)
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status do Check</label>
                            @php
                                // Verifica se há pedidos não entregues (excluindo cancelados)
                                $hasIncompleteOrders = ($pendingOrders->count() > 0 || 
                                                       $inProductionOrders->count() > 0 || 
                                                       $inTransitOrders->count() > 0);
                                
                                // Verifica se pode cancelar (total zero)
                                $canCancelCheck = $currentCheck->total == 0;
                                
                                // Se o check está Open, bloqueia apenas o botão "Fechando" se houver pedidos incompletos
                                // Se já está em Closing/Closed/Paid, permite mudanças livres
                                $blockClosingButton = ($currentCheck->status === 'Open' && $hasIncompleteOrders);
                            @endphp
                            @if($blockClosingButton)
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-2">
                                    <p class="text-sm text-yellow-800">
                                        <span class="font-semibold">⚠️ Atenção:</span> Só é possível iniciar o fechamento quando todos os pedidos estiverem entregues (Pronto).
                                    </p>
                                </div>
                            @endif
                            
                            @if($canCancelCheck)
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-2">
                                    <p class="text-sm text-blue-800">
                                        <span class="font-semibold">💡 Dica:</span> Este check está sem valor. Você pode cancelá-lo para liberar a mesa.
                                    </p>
                                </div>
                            @endif
                            
                            <div class="flex flex-wrap gap-2">
                                <button 
                                    wire:click="$set('newCheckStatus', 'Open')"
                                    class="px-3 py-2 rounded-lg text-sm font-medium transition
                                        {{ $newCheckStatus === 'Open' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                    Aberto
                                </button>
                                <button 
                                    wire:click="$set('newCheckStatus', 'Closing')"
                                    @if($blockClosingButton) disabled @endif
                                    class="px-3 py-2 rounded-lg text-sm font-medium transition
                                        {{ $blockClosingButton ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Closing' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
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
                                <button 
                                    wire:click="$set('newCheckStatus', 'Canceled')"
                                    @if(!$canCancelCheck) disabled @endif
                                    class="px-3 py-2 rounded-lg text-sm font-medium transition
                                        {{ !$canCancelCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Canceled' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                                    Cancelar
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
