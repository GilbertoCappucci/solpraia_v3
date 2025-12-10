@props([
    'title',
    'orders',
    'totalTime',
    'color',
    'showCancel' => false,
    'showPrice' => false,
    'nextStatus' => null,
    'previousStatus' => null,
    'subtotal' => 0,
    'isCheckOpen' => true,
    'delayAlarmEnabled' => true
])

@php
    $colorConfig = match($color) {
        'yellow' => [
            'border' => 'border-yellow-400',
            'bg' => 'bg-yellow-50',
            'dot' => 'bg-yellow-500',
            'text' => 'text-yellow-800',
            'textLight' => 'text-yellow-700',
            'badge' => 'bg-yellow-200',
            'subtotalBorder' => 'border-yellow-200',
            'subtotalText' => 'text-yellow-700',
        ],
        'blue' => [
            'border' => 'border-blue-400',
            'bg' => 'bg-blue-50',
            'dot' => 'bg-blue-500',
            'text' => 'text-blue-800',
            'textLight' => 'text-blue-700',
            'badge' => 'bg-blue-200',
            'subtotalBorder' => 'border-blue-200',
            'subtotalText' => 'text-blue-700',
        ],
        'purple' => [
            'border' => 'border-purple-400',
            'bg' => 'bg-purple-50',
            'dot' => 'bg-purple-500',
            'text' => 'text-purple-800',
            'textLight' => 'text-purple-700',
            'badge' => 'bg-purple-200',
            'subtotalBorder' => 'border-purple-200',
            'subtotalText' => 'text-purple-700',
        ],
        'green' => [
            'border' => 'border-green-400',
            'bg' => 'bg-green-50',
            'dot' => 'bg-green-500',
            'text' => 'text-green-800',
            'textLight' => 'text-green-700',
            'badge' => 'bg-green-200',
            'subtotalBorder' => 'border-green-200',
            'subtotalText' => 'text-green-700',
        ],
        default => [
            'border' => 'border-gray-400',
            'bg' => 'bg-gray-50',
            'dot' => 'bg-gray-500',
            'text' => 'text-gray-800',
            'textLight' => 'text-gray-700',
            'badge' => 'bg-gray-200',
            'subtotalBorder' => 'border-gray-200',
            'subtotalText' => 'text-gray-700',
        ]
    };
    
    $animate = $color === 'purple' ? 'animate-pulse' : '';
@endphp

<div class="bg-white rounded-xl shadow-sm border-l-4 {{ $colorConfig['border'] }} overflow-hidden">
    {{-- Header --}}
    <div class="{{ $colorConfig['bg'] }} px-4 py-2 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 {{ $colorConfig['dot'] }} rounded-full {{ $animate }}"></span>
            <span class="font-bold {{ $colorConfig['text'] }}">{{ $title }}</span>
            <span class="text-sm {{ $colorConfig['textLight'] }}">({{ $orders->count() }})</span>
        </div>
        @if($orders->count() > 0 && $totalTime)
            <span class="text-sm font-bold {{ $colorConfig['text'] }} {{ $colorConfig['badge'] }} px-2 py-1 rounded">{{ $totalTime }}m</span>
        @endif
    </div>
    
    {{-- Lista de Pedidos --}}
    @if($orders->count() > 0)
        <div class="p-3 space-y-2">
            @foreach($orders as $order)
                @php
                    // Verifica se o pedido está atrasado
                    $timeLimits = config('restaurant.time_limits');
                    $isDelayed = false;
                    
                    if ($order->status_changed_at) {
                        $minutes = abs((int) now()->diffInMinutes($order->status_changed_at));
                        
                        $isDelayed = match($order->status) {
                            'pending' => $minutes > $timeLimits['pending'],
                            'in_production' => $minutes > $timeLimits['in_production'],
                            'in_transit' => $minutes > $timeLimits['in_transit'],
                            default => false
                        };
                    }
                    
                    $delayAnimation = ($isDelayed && $delayAlarmEnabled) ? 'animate-pulse-warning' : '';
                @endphp
                <div class="flex items-center justify-between py-1 border-b border-gray-100 last:border-0 rounded px-2 -mx-2 {{ $delayAnimation }}">
                    <div class="flex items-center gap-2 flex-1">
                        <span class="text-lg font-semibold text-gray-700">{{ $order->quantity }}x</span>
                        <span class="text-sm text-gray-800">{{ $order->product->name }}</span>
                    </div>
                    
                    {{-- Ações ou Preço --}}
                    <div class="flex items-center gap-2">
                        @if($isCheckOpen)
                            {{-- Botão Voltar Status (disponível em qualquer status) --}}
                            @if($previousStatus)
                                @php
                                    // Define cores do botão de voltar baseado no status anterior
                                    $backButtonConfig = match($previousStatus) {
                                        'pending' => ['bg' => 'bg-yellow-50', 'hover' => 'hover:bg-yellow-100', 'icon' => 'text-yellow-600'],
                                        'in_production' => ['bg' => 'bg-blue-50', 'hover' => 'hover:bg-blue-100', 'icon' => 'text-blue-600'],
                                        'in_transit' => ['bg' => 'bg-purple-50', 'hover' => 'hover:bg-purple-100', 'icon' => 'text-purple-600'],
                                        default => ['bg' => 'bg-gray-50', 'hover' => 'hover:bg-gray-100', 'icon' => 'text-gray-600']
                                    };
                                    
                                    // Para pedidos agrupados, volta apenas o primeiro pedido individual
                                    $orderIdToGoBack = ($order->is_grouped ?? false) 
                                        ? $order->individual_orders->first()->id 
                                        : $order->id;
                                @endphp
                                @if($order->is_grouped ?? false)
                                    {{-- Botão com lógica de segurar para pedidos agrupados --}}
                                    <div x-data="{ 
                                        pressTimer: null, 
                                        longPress: false,
                                        startPress() {
                                            this.longPress = false;
                                            this.pressTimer = setTimeout(() => {
                                                this.longPress = true;
                                                @this.call('updateAllOrderStatus', {{ json_encode($order->individual_orders->pluck('id')->toArray()) }}, '{{ $previousStatus }}');
                                            }, 500);
                                        },
                                        endPress() {
                                            clearTimeout(this.pressTimer);
                                            if (!this.longPress) {
                                                @this.call('updateOrderStatus', {{ $orderIdToGoBack }}, '{{ $previousStatus }}');
                                            }
                                        },
                                        cancelPress() {
                                            clearTimeout(this.pressTimer);
                                            this.longPress = false;
                                        }
                                    }">
                                        <button 
                                            @mousedown="startPress()"
                                            @mouseup="endPress()"
                                            @mouseleave="cancelPress()"
                                            @touchstart="startPress()"
                                            @touchend="endPress()"
                                            wire:loading.attr="disabled"
                                            wire:loading.class="opacity-50 cursor-wait"
                                            class="p-3 {{ $backButtonConfig['bg'] }} {{ $backButtonConfig['hover'] }} rounded-lg transition active:scale-95"
                                            title="Clique: voltar 1 unidade | Segurar: voltar todas">
                                            <svg class="w-6 h-6 {{ $backButtonConfig['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    {{-- Botão simples para pedido único --}}
                                    <button 
                                        wire:click="updateOrderStatus({{ $orderIdToGoBack }}, '{{ $previousStatus }}')"
                                        wire:loading.attr="disabled"
                                        wire:loading.class="opacity-50 cursor-wait"
                                        class="p-3 {{ $backButtonConfig['bg'] }} {{ $backButtonConfig['hover'] }} rounded-lg transition active:scale-95"
                                        title="Voltar ao status anterior">
                                        <svg class="w-6 h-6 {{ $backButtonConfig['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                        </svg>
                                    </button>
                                @endif
                            @endif
                            @if($showCancel)
                                {{-- Botão Decrementar (-) --}}
                                @if($order->is_grouped ?? false)
                                    {{-- Para pedidos agrupados: cancela o último pedido individual e passa todos os IDs --}}
                                    <button 
                                        wire:click="openCancelModal({{ $order->individual_orders->last()->id }}, {{ json_encode($order->individual_orders->pluck('id')->toArray()) }})"
                                        class="p-3 hover:bg-red-100 rounded-lg transition group active:scale-95"
                                        title="Remover unidade">
                                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                @else
                                    {{-- Para pedido único: cancela normalmente --}}
                                    <button 
                                        wire:click="openCancelModal({{ $order->id }})"
                                        class="p-3 hover:bg-red-100 rounded-lg transition group active:scale-95"
                                        title="Cancelar pedido">
                                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                @endif
                                
                                {{-- Botão Incrementar (+) --}}
                                @php
                                    // Para adicionar, usa o primeiro pedido (tanto agrupado quanto único)
                                    $orderIdToAdd = ($order->is_grouped ?? false) 
                                        ? $order->individual_orders->first()->id 
                                        : $order->id;
                                        
                                    // Verifica disponibilidade de estoque
                                    // Se stock for null, assume disponível. Se < 0, é infinito. Se > 0, tem qtd. Se == 0, indisponível.
                                    $stock = $order->product->stock;
                                    $hasStockToAdd = !$stock || $stock->quantity != 0;
                                @endphp
                                
                                @if($hasStockToAdd)
                                    <button 
                                        wire:click="addOneMore({{ $orderIdToAdd }})"
                                        class="p-3 hover:bg-green-100 rounded-lg transition group active:scale-95"
                                        title="Adicionar 1 unidade">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                @endif
                            @endif
                            @if($nextStatus)
                                @php
                                    // Define cores do botão de avançar baseado no próximo status
                                    $buttonConfig = match($nextStatus) {
                                        'in_production' => ['bg' => 'bg-blue-50', 'hover' => 'hover:bg-blue-100', 'icon' => 'text-blue-600'],
                                        'in_transit' => ['bg' => 'bg-purple-50', 'hover' => 'hover:bg-purple-100', 'icon' => 'text-purple-600'],
                                        'completed' => ['bg' => 'bg-green-50', 'hover' => 'hover:bg-green-100', 'icon' => 'text-green-600'],
                                        default => ['bg' => 'bg-gray-50', 'hover' => 'hover:bg-gray-100', 'icon' => 'text-gray-600']
                                    };
                                    
                                    // Para pedidos agrupados, avança apenas o primeiro pedido individual
                                    $orderIdToAdvance = ($order->is_grouped ?? false) 
                                        ? $order->individual_orders->first()->id 
                                        : $order->id;
                                @endphp
                                @if($order->is_grouped ?? false)
                                    {{-- Botão com lógica de segurar para pedidos agrupados --}}
                                    <div x-data="{ 
                                        pressTimer: null, 
                                        longPress: false,
                                        startPress() {
                                            this.longPress = false;
                                            this.pressTimer = setTimeout(() => {
                                                this.longPress = true;
                                                @this.call('updateAllOrderStatus', {{ json_encode($order->individual_orders->pluck('id')->toArray()) }}, '{{ $nextStatus }}');
                                            }, 500);
                                        },
                                        endPress() {
                                            clearTimeout(this.pressTimer);
                                            if (!this.longPress) {
                                                @this.call('updateOrderStatus', {{ $orderIdToAdvance }}, '{{ $nextStatus }}');
                                            }
                                        },
                                        cancelPress() {
                                            clearTimeout(this.pressTimer);
                                            this.longPress = false;
                                        }
                                    }">
                                        <button 
                                            @mousedown="startPress()"
                                            @mouseup="endPress()"
                                            @mouseleave="cancelPress()"
                                            @touchstart="startPress()"
                                            @touchend="endPress()"
                                            wire:loading.attr="disabled"
                                            wire:loading.class="opacity-50 cursor-wait"
                                            class="p-3 {{ $buttonConfig['bg'] }} {{ $buttonConfig['hover'] }} rounded-lg transition active:scale-95"
                                            title="Clique: avançar 1 unidade | Segurar: avançar todas">
                                            @if($nextStatus === 'completed')
                                                <svg class="w-6 h-6 {{ $buttonConfig['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            @else
                                                <svg class="w-6 h-6 {{ $buttonConfig['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </div>
                                @else
                                    {{-- Botão simples para pedido único --}}
                                    <button 
                                        wire:click="updateOrderStatus({{ $orderIdToAdvance }}, '{{ $nextStatus }}')"
                                        wire:loading.attr="disabled"
                                        wire:loading.class="opacity-50 cursor-wait"
                                        class="p-3 {{ $buttonConfig['bg'] }} {{ $buttonConfig['hover'] }} rounded-lg transition active:scale-95"
                                        title="Avançar status">
                                        @if($nextStatus === 'completed')
                                            <svg class="w-6 h-6 {{ $buttonConfig['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @else
                                            <svg class="w-6 h-6 {{ $buttonConfig['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        @endif
                                    </button>
                                @endif
                            @endif
                        @endif
                        
                        {{-- Preço (quando showPrice está ativo) --}}
                        @if($showPrice)
                            <span class="text-sm font-bold text-orange-600 ml-auto">R$ {{ number_format($order->product->price * $order->quantity, 2, ',', '.') }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
            
            {{-- Subtotal (apenas para pedidos entregues) --}}
            @if($showPrice && $subtotal > 0)
                <div class="pt-2 flex justify-between items-center border-t-2 {{ $colorConfig['subtotalBorder'] }}">
                    <span class="text-xs font-semibold text-gray-600">SUBTOTAL</span>
                    <span class="text-base font-bold {{ $colorConfig['subtotalText'] }}">R$ {{ number_format($subtotal, 2, ',', '.') }}</span>
                </div>
            @endif
        </div>
    @else
        <div class="p-3 text-center text-sm text-gray-500">
            Nenhum pedido {{ strtolower(str_replace(['AGUARDANDO', 'EM PREPARO', 'EM TRÂNSITO', 'ENTREGUE'], ['aguardando', 'em preparo', 'em trânsito', 'entregue'], $title)) }}
        </div>
    @endif
</div>
