<div class="bg-white">
    @if($groupedOrders->isEmpty())
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
    <div class="divide-y divide-gray-200">
        @foreach($groupedOrders as $group)
        @php
        $statusConfig = match($group->status) {
            'pending' => ['label' => 'Aguardando', 'color' => 'bg-yellow-100 text-yellow-800 border-yellow-200'],
            'in_production' => ['label' => 'Em Preparo', 'color' => 'bg-blue-100 text-blue-800 border-blue-200'],
            'in_transit' => ['label' => 'Em Trânsito', 'color' => 'bg-purple-100 text-purple-800 border-purple-200'],
            'completed' => ['label' => 'Entregue', 'color' => 'bg-green-100 text-green-800 border-green-200'],
            'canceled' => ['label' => 'Cancelado', 'color' => 'bg-red-100 text-red-800 border-red-200'],
            default => ['label' => 'Desconhecido', 'color' => 'bg-gray-100 text-gray-800 border-gray-200']
        };

        // Verifica se o grupo está atrasado
        $isDelayed = false;
        if ($group->status_changed_at) {
            $minutes = abs((int) now()->diffInMinutes($group->status_changed_at));
            $isDelayed = match($group->status) {
                'pending' => $minutes > $timeLimits['pending'],
                'in_production' => $minutes > $timeLimits['in_production'],
                'in_transit' => $minutes > $timeLimits['in_transit'],
                default => false
            };
        }

        $delayAnimation = ($isDelayed) ? 'animate-pulse-warning' : '';
        @endphp

        <div 
            wire:click="{{ $group->order_count === 1 ? 'openDetailsModal(' . $group->orders->first()->id . ')' : 'openGroupModal(' . $group->product_id . ', \'' . $group->status . '\')' }}" 
            class="p-4 hover:bg-gray-50 transition flex items-center gap-4 cursor-pointer {{ $delayAnimation }}"
            x-data="{
                minutes: {{ $group->status_changed_at ? abs((int) now()->diffInMinutes($group->status_changed_at)) : 0 }},
                timestamp: @js($group->status_changed_at ? $group->status_changed_at->toISOString() : null),
                updateMinutes() {
                    if (this.timestamp) {
                        const now = Math.floor(Date.now() / 1000);
                        const statusTime = Math.floor(new Date(this.timestamp).getTime() / 1000);
                        this.minutes = Math.floor((now - statusTime) / 60);
                    }
                }
            }"
            x-init="if (timestamp) { updateMinutes(); setInterval(() => updateMinutes(), 30000); }">
            
            <div class="flex-shrink-0">
                {{-- Checkbox de seleção --}}
                @php $orderId = $group->orders->first()->id; @endphp
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <span class="inline-block w-8 h-8 rounded border bg-white flex items-center justify-center">
                        <input type="checkbox"
                            wire:click.stop="toggleSelection({{ $orderId }}, '{{ $group->status }}', {{ $group->is_paid ? 'true' : 'false' }}, {{ $group->product_id }})"
                            @if(in_array($orderId, $selectedOrderIds)) checked @endif
                            class="w-4 h-4">
                    </span>
                </label>
            </div>

            {{-- Quantidade Total --}}
            <div class="flex-shrink-0 w-14 text-center">
                <span class="text-3xl font-bold text-gray-700">{{ $group->total_quantity }}</span>
            </div>

            {{-- Nome do Produto --}}
            <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-gray-900 truncate">{{ $group->product_name }}</h3>
                <p class="text-sm text-gray-500" x-text="minutes + ' min'"></p>
            </div>

            {{-- Status Badge --}}
            <div class="flex-shrink-0">
                @if($group->is_paid)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border bg-emerald-100 text-emerald-800 border-emerald-200">
                        ✓ Pago
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border {{ $statusConfig['color'] }}">
                        {{ $statusConfig['label'] }}
                    </span>
                @endif
            </div>

            {{-- Botão Pagar (apenas para completed e não pagos) --}}
            @if($group->status === 'completed' && !$group->is_paid)
            <button 
                wire:click.stop="payOrder({{ $group->product_id }}, '{{ $group->status }}')"
                class="flex-shrink-0 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-semibold transition shadow-md">
                💳 Pagar
            </button>
            @endif

            {{-- Ícone Indicador --}}
            <div class="flex-shrink-0 text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </div>
        @endforeach
        
        {{-- Total Geral --}}
        <div class="p-4 bg-gray-50 flex items-center justify-between">
            <span class="text-lg font-semibold text-gray-700">Total Geral:</span>
            <span class="text-2xl font-bold text-gray-700">R$ {{ number_format($checkTotal, 2, ',', '.') }}</span>
        </div>
    </div>
    @endif
    {{-- Barra de ações quando tiver seleção --}}
    @if(!empty($selectedOrderIds))
    <div class="fixed bottom-6 right-6 z-50">
        <div class="bg-white p-3 rounded-lg shadow-lg flex items-center gap-3">
            <span class="text-sm text-gray-700">{{ count($selectedOrderIds) }} selecionado(s)</span>
            <button wire:click="openSelectedGroupActions" class="px-3 py-2 bg-orange-500 text-white rounded">Ações</button>
            <button wire:click="clearSelection" class="px-3 py-2 bg-gray-200 text-gray-700 rounded">Limpar</button>
        </div>
    </div>
    @endif
</div>
