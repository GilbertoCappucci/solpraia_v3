<div wire:poll.{{ $pollingInterval }}ms>
    
    <x-flash-message />

    {{-- Header Compacto com Info do Check --}}
    <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white p-3 flex items-center justify-between sticky top-0 z-40 shadow-md">
        <div class="flex items-center gap-2">
            <button 
                wire:click="goBack"
                class="p-1.5 hover:bg-white/20 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold">{{ $table->number }}</span>
                <span class="text-sm opacity-90">{{ $table->name }}</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button 
                wire:click="openStatusModal"
                class="flex items-center gap-2 bg-white/10 hover:bg-white/20 rounded-lg px-3 py-1.5 transition-all">
                @php
                    $checkStatusConfig = match($check->status) {
                        'Open' => ['label' => 'Aberto', 'color' => 'green'],
                        'Closed' => ['label' => 'Fechado', 'color' => 'red'],
                        'Paid' => ['label' => 'Pago', 'color' => 'gray'],
                        'Canceled' => ['label' => 'Cancelado', 'color' => 'orange'],
                        default => ['label' => 'Aberto', 'color' => 'green']
                    };
                @endphp
                
                <x-order-status-badge 
                    label="Check" 
                    :value="$checkStatusConfig['label']" 
                    :color="$checkStatusConfig['color']" />
                
                <svg class="w-4 h-4 opacity-75 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Informações do Check --}}
    <div class="bg-white border-b-2 border-gray-200 p-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Comanda #{{ $check->id }}</h2>
                <p class="text-sm text-gray-600">Aberta em {{ \Carbon\Carbon::parse($check->opened_at)->format('d/m/Y H:i') }}</p>
                @if($check->closed_at)
                    <p class="text-sm text-gray-600">Fechada em {{ \Carbon\Carbon::parse($check->closed_at)->format('d/m/Y H:i') }}</p>
                @endif
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">Total</p>
                <p class="text-2xl font-bold text-orange-600">R$ {{ number_format($check->total, 2, ',', '.') }}</p>
            </div>
        </div>
        
        {{-- Botão para gerenciar pedidos (apenas se check estiver Open) --}}
        @if($check->status === 'Open')
            <button 
                wire:click="goToOrders"
                class="w-full py-3 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white rounded-lg font-bold transition shadow-lg flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Gerenciar Pedidos
            </button>
        @endif
    </div>

    {{-- Lista de Pedidos que Compõem o Check (Exceto Aguardando) --}}
    @php
        // Filtra pedidos que compõem o valor do check (não Pending e não Canceled)
        $checkOrders = $check->orders->whereNotIn('status', ['Pending', 'Canceled'])->sortBy('created_at');
        $checkTotal = $checkOrders->sum(fn($order) => $order->product->price);
    @endphp
    
    @if($checkOrders->count() > 0)
        <div class="bg-white p-4 border-b-2 border-gray-200">
            <h3 class="text-sm font-bold text-gray-700 mb-3 uppercase">Itens da Comanda</h3>
            <div class="space-y-2">
                @foreach($checkOrders as $order)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ $order->product->name }}</p>
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <span>{{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }}</span>
                                @php
                                    $statusConfig = match($order->status) {
                                        'InProduction' => ['label' => 'Preparo', 'color' => 'blue'],
                                        'InTransit' => ['label' => 'Trânsito', 'color' => 'purple'],
                                        'Completed' => ['label' => 'Entregue', 'color' => 'green'],
                                        default => ['label' => $order->status, 'color' => 'gray']
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded text-white text-xs font-medium bg-{{ $statusConfig['color'] }}-500">
                                    {{ $statusConfig['label'] }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-gray-900">R$ {{ number_format($order->product->price, 2, ',', '.') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            
            {{-- Subtotal --}}
            <div class="mt-3 pt-3 border-t-2 border-gray-200 flex items-center justify-between">
                <span class="font-bold text-gray-900">Subtotal ({{ $checkOrders->count() }} {{ $checkOrders->count() === 1 ? 'item' : 'itens' }})</span>
                <span class="font-bold text-lg text-orange-600">R$ {{ number_format($checkTotal, 2, ',', '.') }}</span>
            </div>
        </div>
    @endif

    {{-- Resumo dos Pedidos por Status --}}
    <div class="bg-gray-50 p-4 space-y-3">
        {{-- Pedidos Aguardando --}}
        @if($groupedOrders['pending']->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-yellow-500 p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                        <h3 class="font-bold text-gray-900">AGUARDANDO</h3>
                    </div>
                    <span class="text-sm font-semibold text-gray-600">{{ $groupedOrders['pending']->count() }} {{ $groupedOrders['pending']->count() === 1 ? 'pedido' : 'pedidos' }}</span>
                </div>
                <div class="space-y-2">
                    @foreach($groupedOrders['pending'] as $order)
                        <div class="flex items-center justify-between text-sm py-2 border-b border-gray-100 last:border-0">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ $order->product->name }}</p>
                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">R$ {{ number_format($order->product->price, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Pedidos Em Preparo --}}
        @if($groupedOrders['inProduction']->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-blue-500 p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <h3 class="font-bold text-gray-900">EM PREPARO</h3>
                    </div>
                    <span class="text-sm font-semibold text-gray-600">{{ $groupedOrders['inProduction']->count() }} {{ $groupedOrders['inProduction']->count() === 1 ? 'pedido' : 'pedidos' }}</span>
                </div>
                <div class="space-y-2">
                    @foreach($groupedOrders['inProduction'] as $order)
                        <div class="flex items-center justify-between text-sm py-2 border-b border-gray-100 last:border-0">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ $order->product->name }}</p>
                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">R$ {{ number_format($order->product->price, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Pedidos Em Trânsito --}}
        @if($groupedOrders['inTransit']->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-purple-500 p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                        <h3 class="font-bold text-gray-900">EM TRÂNSITO</h3>
                    </div>
                    <span class="text-sm font-semibold text-gray-600">{{ $groupedOrders['inTransit']->count() }} {{ $groupedOrders['inTransit']->count() === 1 ? 'pedido' : 'pedidos' }}</span>
                </div>
                <div class="space-y-2">
                    @foreach($groupedOrders['inTransit'] as $order)
                        <div class="flex items-center justify-between text-sm py-2 border-b border-gray-100 last:border-0">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ $order->product->name }}</p>
                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">R$ {{ number_format($order->product->price, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Pedidos Entregues --}}
        @if($groupedOrders['delivered']->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-green-500 p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <h3 class="font-bold text-gray-900">ENTREGUE</h3>
                    </div>
                    <span class="text-sm font-semibold text-gray-600">{{ $groupedOrders['delivered']->count() }} {{ $groupedOrders['delivered']->count() === 1 ? 'pedido' : 'pedidos' }}</span>
                </div>
                <div class="space-y-2">
                    @foreach($groupedOrders['delivered'] as $order)
                        <div class="flex items-center justify-between text-sm py-2 border-b border-gray-100 last:border-0">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ $order->product->name }}</p>
                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">R$ {{ number_format($order->product->price, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Pedidos Cancelados --}}
        @if($groupedOrders['canceled']->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-red-500 p-4 opacity-60">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        <h3 class="font-bold text-gray-900">CANCELADO</h3>
                    </div>
                    <span class="text-sm font-semibold text-gray-600">{{ $groupedOrders['canceled']->count() }} {{ $groupedOrders['canceled']->count() === 1 ? 'pedido' : 'pedidos' }}</span>
                </div>
                <div class="space-y-2">
                    @foreach($groupedOrders['canceled'] as $order)
                        <div class="flex items-center justify-between text-sm py-2 border-b border-gray-100 last:border-0">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 line-through">{{ $order->product->name }}</p>
                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900 line-through">R$ {{ number_format($order->product->price, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Modal Alterar Status do Check --}}
    @if($showStatusModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeStatusModal">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6" wire:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Alterar Status do Check</h3>
                    <button wire:click="closeStatusModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <x-check-status-selector 
                        :check="$check"
                        :newCheckStatus="$newCheckStatus"
                        :pendingCount="$groupedOrders['pending']->count()"
                        :inProductionCount="$groupedOrders['inProduction']->count()"
                        :inTransitCount="$groupedOrders['inTransit']->count()" />

                    <div class="flex gap-3">
                        <button 
                            wire:click="closeStatusModal"
                            class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition">
                            Cancelar
                        </button>
                        <button 
                            wire:click="updateCheckStatus"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white rounded-lg font-bold transition shadow-lg">
                            Salvar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
