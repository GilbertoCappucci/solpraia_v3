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
        // Verifica se o pedido está atrasado (usando valores da sessão do usuário)
        $timeLimits = [
            'pending' => session('restaurant.time_limits.pending', config('restaurant.time_limits.pending')),
            'in_production' => session('restaurant.time_limits.in_production', config('restaurant.time_limits.in_production')),
            'in_transit' => session('restaurant.time_limits.in_transit', config('restaurant.time_limits.in_transit')),
        ];
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
        <div wire:key="order-{{ $order->id }}" class="flex items-center justify-between py-1 border-b border-gray-100 last:border-0 rounded px-2 -mx-2 {{ $delayAnimation }}">
            <div class="flex items-center gap-2 flex-1">
                <span class="text-lg font-semibold text-gray-700">{{ $order->quantity }}x</span>
                <span class="text-sm text-gray-800">{{ $order->product->name }}</span>
            </div>

            {{-- Ações ou Preço --}}
            <div class="flex items-center gap-2">
                @if($isCheckOpen)
                {{-- Botão Voltar Status --}}
                @if($previousStatus)
                @php
                $backButtonConfig = match($previousStatus) {
                'pending' => ['bg' => 'bg-yellow-50', 'hover' => 'hover:bg-yellow-100', 'icon' => 'text-yellow-600'],
                'in_production' => ['bg' => 'bg-blue-50', 'hover' => 'hover:bg-blue-100', 'icon' => 'text-blue-600'],
                'in_transit' => ['bg' => 'bg-purple-50', 'hover' => 'hover:bg-purple-100', 'icon' => 'text-purple-600'],
                default => ['bg' => 'bg-gray-50', 'hover' => 'hover:bg-gray-100', 'icon' => 'text-gray-600']
                };
                @endphp

                <div class="flex gap-1">
                    {{-- Voltar Imadiatamente (TUDO) --}}
                    <button
                        wire:click="updateOrderStatus({{ $order->id }}, '{{ $previousStatus }}')"
                        wire:loading.attr="disabled"
                        class="p-2 {{ $backButtonConfig['bg'] }} {{ $backButtonConfig['hover'] }} rounded-lg transition active:scale-95"
                        title="Voltar todos">
                        <svg class="w-5 h-5 {{ $backButtonConfig['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    {{-- Voltar 1 Unidade (Se quantidade > 1) --}}
                    @if($order->quantity > 1)
                    <button
                        wire:click="updateOrderStatus({{ $order->id }}, '{{ $previousStatus }}', 1)"
                        wire:loading.attr="disabled"
                        class="p-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition active:scale-95"
                        title="Voltar apenas 1 unidade">
                        <span class="text-xs font-bold text-gray-500">1</span>
                        <svg class="w-3 h-3 text-gray-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    @endif
                </div>
                @endif

                {{-- Cancelar / Adicionar (Apenas em Pending) --}}
                @if($showCancel)
                <button
                    wire:click="openCancelModal({{ $order->id }})"
                    class="p-3 hover:bg-red-100 rounded-lg transition group active:scale-95"
                    title="Remover unidade">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                </button>

                @php
                $stock = $order->product->stock;
                $hasStockToAdd = !$stock || $stock->quantity != 0;
                @endphp

                @if($hasStockToAdd)
                <button
                    wire:click="addOneMore({{ $order->id }})"
                    class="p-3 hover:bg-green-100 rounded-lg transition group active:scale-95"
                    title="Adicionar 1 unidade">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
                @endif
                @endif

                {{-- Botão Avançar Status --}}
                @if($nextStatus)
                @php
                $buttonConfig = match($nextStatus) {
                'in_production' => ['bg' => 'bg-blue-50', 'hover' => 'hover:bg-blue-100', 'icon' => 'text-blue-600'],
                'in_transit' => ['bg' => 'bg-purple-50', 'hover' => 'hover:bg-purple-100', 'icon' => 'text-purple-600'],
                'completed' => ['bg' => 'bg-green-50', 'hover' => 'hover:bg-green-100', 'icon' => 'text-green-600'],
                default => ['bg' => 'bg-gray-50', 'hover' => 'hover:bg-gray-100', 'icon' => 'text-gray-600']
                };
                @endphp

                <div class="flex gap-1">
                    {{-- Avançar 1 Unidade (Se quantidade > 1) --}}
                    @if($order->quantity > 1)
                    <button
                        wire:click="updateOrderStatus({{ $order->id }}, '{{ $nextStatus }}', 1)"
                        wire:loading.attr="disabled"
                        class="p-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition active:scale-95"
                        title="Avançar apenas 1 unidade">
                        <span class="text-xs font-bold text-gray-500">1</span>
                        <svg class="w-3 h-3 text-gray-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    @endif

                    {{-- Avançar Tudo --}}
                    <button
                        wire:click="updateOrderStatus({{ $order->id }}, '{{ $nextStatus }}')"
                        wire:loading.attr="disabled"
                        class="p-2 {{ $buttonConfig['bg'] }} {{ $buttonConfig['hover'] }} rounded-lg transition active:scale-95"
                        title="Avançar todos">
                        @if($nextStatus === 'completed')
                        <svg class="w-5 h-5 {{ $buttonConfig['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        @else
                        <svg class="w-5 h-5 {{ $buttonConfig['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        @endif
                    </button>
                </div>
                @endif
                @endif

                {{-- Preço --}}
                @if($showPrice)
                <span class="text-sm font-bold text-orange-600 ml-auto">R$ {{ number_format($order->product->price * $order->quantity, 2, ',', '.') }}</span>
                @endif
            </div>
        </div>
        @endforeach

        {{-- Subtotal --}}
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