<div class="bg-white">
    @if($listOrders->isEmpty())
    <div class="p-8 text-center text-gray-400">
        <svg class="w-16 h-16 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        @if(count($statusFilters) < 5)
            <p class="text-lg font-medium">Nenhum pedido encontrado</p>
            <p class="text-sm mt-1">Não há pedidos com os filtros selecionados</p>
        @else
            <p class="text-lg font-medium">Nenhum pedido ativo</p>
            <p class="text-sm mt-1">Clique em "Adicionar Pedidos" para começar</p>
        @endif
    </div>
    @else
    <div>
        @foreach($listOrders as $order)
        @php
        $statusConfig = match($order->status) {
            \App\Enums\OrderStatusEnum::PENDING->value => ['label' => \App\Enums\OrderStatusEnum::getLabel(\App\Enums\OrderStatusEnum::PENDING), 'color' => 'bg-yellow-100 text-yellow-800 border-yellow-200'],
            \App\Enums\OrderStatusEnum::IN_PRODUCTION->value => ['label' => \App\Enums\OrderStatusEnum::getLabel(\App\Enums\OrderStatusEnum::IN_PRODUCTION), 'color' => 'bg-blue-100 text-blue-800 border-blue-200'],
            \App\Enums\OrderStatusEnum::IN_TRANSIT->value => ['label' => \App\Enums\OrderStatusEnum::getLabel(\App\Enums\OrderStatusEnum::IN_TRANSIT), 'color' => 'bg-purple-100 text-purple-800 border-purple-200'],
            'completed' => ['label' => 'Entregue', 'color' => 'bg-green-100 text-green-800 border-green-200'],
            'canceled' => ['label' => 'Cancelado', 'color' => 'bg-red-100 text-red-800 border-red-200'],
            default => ['label' => 'Desconhecido', 'color' => 'bg-gray-100 text-gray-800 border-gray-200']
        };

        // Verifica se o grupo está atrasado
        $isDelayed = false;
        if ($order->status_changed_at) {
            $minutes = abs((int) now()->diffInMinutes($order->status_changed_at));
            $isDelayed = match($order->status) {
                \App\Enums\OrderStatusEnum::PENDING->value => $minutes > $timeLimits[\App\Enums\OrderStatusEnum::PENDING->value],
                \App\Enums\OrderStatusEnum::IN_PRODUCTION->value => $minutes > $timeLimits[\App\Enums\OrderStatusEnum::IN_PRODUCTION->value],
                \App\Enums\OrderStatusEnum::IN_TRANSIT->value => $minutes > $timeLimits[\App\Enums\OrderStatusEnum::IN_TRANSIT->value],
                default => false
            };
        }

        $delayAnimation = ($isDelayed) ? 'animate-pulse-warning' : '';
        @endphp

        {{-- Lista das orders --}}
        <div
            wire:click="rowClick({{ $order->id }}, '{{ $order->status }}', {{ $order->is_paid ? 'true' : 'false' }}, {{ $order->product_id }})"
            class="group cursor-pointer {{ $delayAnimation }} mb-4 md:mb-0 md:rounded-none md:shadow-none md:border-0 md:p-0"
        >
            <div
                class="bg-white rounded-2xl shadow-md border border-gray-200 p-4 flex flex-col gap-2 md:flex-row md:items-center md:gap-0 md:rounded-none md:shadow-none md:border-0 md:p-4 hover:bg-gray-50 transition"
                x-data="{
                    minutes: {{ $order->status_changed_at ? abs((int) now()->diffInMinutes($order->status_changed_at)) : 0 }},
                    timestamp: @js($order->status_changed_at ? $order->status_changed_at->toISOString() : null),
                    updateMinutes() {
                        if (this.timestamp) {
                            const now = Math.floor(Date.now() / 1000);
                            const statusTime = Math.floor(new Date(this.timestamp).getTime() / 1000);
                            this.minutes = Math.floor((now - statusTime) / 60);
                        }
                    }
                }"
                x-init="if (timestamp) { updateMinutes(); setInterval(() => updateMinutes(), 30000); }"
            >
                <!-- MOBILE: duas linhas -->
                <div class="flex flex-col gap-1 md:hidden cursor-pointer">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <button
                                wire:key="select-{{ $order->id }}"
                                wire:click.stop="toggleSelection({{ $order->id }}, '{{ $order->status }}', {{ $order->is_paid ? 'true' : 'false' }}, {{ $order->product_id }})"
                                class="flex items-center justify-center gap-2 p-0 m-0 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                                style="min-width: 36px; min-height: 36px; width: 36px; height: 36px;"
                                aria-label="Selecionar pedido"
                            >
                                <span class="flex items-center justify-center w-full h-full">
                                    <input type="checkbox" class="w-6 h-6 cursor-pointer accent-blue-600" style="min-width: 24px; min-height: 24px;" {{ in_array($order->id, $selectedOrderIds) ? 'checked' : '' }} readOnly>
                                </span>
                            </button>
                            <span class="text-2xl font-bold text-gray-700">{{ $order->total_quantity }}</span>
                        </div>
                        <h3 class="font-semibold text-gray-900 truncate ml-2">{{ $order->product_name }}</h3>
                    </div>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-sm text-gray-500">{{ $order->status_changed_at ? abs((int) now()->diffInMinutes($order->status_changed_at)) : 0 }} min</span>
                        <span class="flex gap-2 items-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $statusConfig['color'] }}">
                                {{ $statusConfig['label'] }}
                            </span>
                            @if($order->is_paid)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border bg-emerald-100 text-emerald-800 border-emerald-200">
                                    ✓ Pago
                                </span>
                            @endif
                        </span>
                    </div>
                </div>
                <!-- DESKTOP/TABLET: layout antigo -->
                <div class="hidden md:flex md:flex-row md:items-center md:w-full">
                    <div class="flex flex-row items-center md:w-2/3">
                        <button
                            wire:key="select-{{ $order->id }}"
                            wire:click.stop="toggleSelection({{ $order->id }}, '{{ $order->status }}', {{ $order->is_paid ? 'true' : 'false' }}, {{ $order->product_id }})"
                            class="flex items-center justify-center gap-2 p-0 m-0 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400 transition md:mr-4"
                            style="min-width: 44px; min-height: 44px; width: 44px; height: 44px;"
                            aria-label="Selecionar pedido"
                        >
                            <span class="flex items-center justify-center w-full h-full">
                                <input type="checkbox" class="w-6 h-6 cursor-pointer accent-blue-600" style="min-width: 24px; min-height: 24px;" {{ in_array($order->id, $selectedOrderIds) ? 'checked' : '' }} readOnly>
                            </span>
                        </button>
                        <div class="flex-shrink-0 w-14 text-left md:mr-4">
                            <span class="text-3xl font-bold text-gray-700">{{ $order->total_quantity }}</span>
                        </div>
                        <div class="flex-1 min-w-0 md:mr-4">
                            <h3 class="font-semibold text-gray-900 truncate">{{ $order->product_name }}</h3>
                        </div>
                        <p class="text-sm text-gray-500 ml-2 md:ml-0">{{ $order->status_changed_at ? abs((int) now()->diffInMinutes($order->status_changed_at)) : 0 }} min</p>
                    </div>
                    <div class="flex flex-row items-center md:justify-end md:w-1/3">
                        <div class="flex gap-2 items-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border {{ $statusConfig['color'] }}">
                                {{ $statusConfig['label'] }}
                            </span>
                            @if($order->is_paid)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border bg-emerald-100 text-emerald-800 border-emerald-200">
                                    ✓ Pago
                                </span>
                            @endif
                        </div>
                        <div class="flex-shrink-0 text-gray-400 hidden md:block self-center md:ml-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

    </div>
    @endif
</div>
