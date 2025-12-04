@props([
    'title',
    'orders',
    'totalTime',
    'color',
    'showCancel' => false,
    'showPrice' => false,
    'nextStatus' => null,
    'subtotal' => 0
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
                <div class="flex items-center justify-between py-1 border-b border-gray-100 last:border-0">
                    <div class="flex items-center gap-2 flex-1">
                        <span class="text-lg font-semibold text-gray-700">{{ $order->quantity }}x</span>
                        <span class="text-sm text-gray-800">{{ $order->product->name }}</span>
                    </div>
                    
                    {{-- Ações ou Preço --}}
                    @if($showPrice)
                        <span class="text-sm font-bold text-orange-600">R$ {{ number_format($order->product->price * $order->quantity, 2, ',', '.') }}</span>
                    @else
                        <div class="flex items-center gap-1">
                            @if($showCancel)
                                <button 
                                    wire:click="cancelOrder({{ $order->id }})"
                                    wire:confirm="Tem certeza que deseja cancelar este pedido?"
                                    class="p-1 hover:bg-red-100 rounded transition"
                                    title="Cancelar pedido">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
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
                                @endphp
                                <button 
                                    wire:click="updateOrderStatus({{ $order->id }}, '{{ $nextStatus }}')"
                                    class="p-1.5 {{ $buttonConfig['bg'] }} {{ $buttonConfig['hover'] }} rounded transition"
                                    title="Avançar status">
                                    @if($nextStatus === 'completed')
                                        <svg class="w-4 h-4 {{ $buttonConfig['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 {{ $buttonConfig['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    @endif
                                </button>
                            @endif
                        </div>
                    @endif
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
