<div wire:poll.{{ $pollingInterval }}ms>

    <x-flash-message />

    {{-- Header Compacto Mobile-Friendly --}}
    <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white p-3 flex items-center justify-between sticky top-0 z-40 shadow-md">
        {{-- Lado Esquerdo --}}
        <div class="flex items-center gap-2">
            {{-- Botão Voltar --}}
            <button
                wire:click="backToTables"
                class="p-2 hover:bg-white/20 rounded-lg transition"
                title="Voltar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            
            {{-- Número da Mesa --}}
            <span class="text-2xl font-bold">{{ $selectedTable->number }}</span>
        </div>

        {{-- Lado Direito --}}
        <div class="flex items-center gap-1">
            {{-- Toggle Alarme --}}
            <button
                wire:click="toggleDelayAlarm"
                class="p-2 hover:bg-white/20 rounded-lg transition"
                title="{{ $delayAlarmEnabled ? 'Desativar' : 'Ativar' }} alarme">
                @if($delayAlarmEnabled)
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
                </svg>
                @else
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
                    <path stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M4 4l12 12" />
                </svg>
                @endif
            </button>

            {{-- Filtro --}}
            <button
                wire:click="openFilterModal"
                class="p-2 hover:bg-white/20 rounded-lg transition relative"
                title="Filtrar pedidos">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                @if(count($statusFilters) < 5)
                <span class="absolute -top-1 -right-1 w-4 h-4 bg-yellow-400 rounded-full text-xs flex items-center justify-center text-gray-900 font-bold">
                    {{ count($statusFilters) }}
                </span>
                @endif
            </button>

            {{-- Status Mesa/Check --}}
            <button
                wire:click="openStatusModal"
                class="p-2 hover:bg-white/20 rounded-lg transition"
                title="Alterar status">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Lista de Pedidos Agrupados --}}
    <div class="bg-white">
        @if($groupedOrders->isEmpty())
        <div class="p-8 text-center text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p class="text-lg font-medium">Nenhum pedido ativo</p>
            <p class="text-sm mt-1">Clique em "Adicionar Pedidos" para começar</p>
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
                $timeLimits = config('restaurant.time_limits');
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
                
                $delayAnimation = ($isDelayed && $delayAlarmEnabled) ? 'animate-pulse-warning' : '';
            @endphp
            
            <div wire:click="{{ $group->order_count === 1 ? 'openDetailsModal(' . $group->orders->first()->id . ')' : 'openGroupModal(' . $group->product_id . ', \'' . $group->status . '\')' }}" class="p-4 hover:bg-gray-50 transition flex items-center gap-4 cursor-pointer {{ $delayAnimation }}">
                {{-- Quantidade Total --}}
                <div class="flex-shrink-0 w-14 text-center">
                    <span class="text-xl font-bold text-gray-900">{{ $group->total_quantity }}</span>
                </div>
                
                {{-- Nome do Produto --}}
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-900 truncate">{{ $group->product_name }}</h4>
                    <p class="text-sm text-gray-500">R$ {{ number_format($group->total_price, 2, ',', '.') }}</p>
                </div>
                
                {{-- Status --}}
                <div class="flex-shrink-0">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border {{ $statusConfig['color'] }}">
                        {{ $statusConfig['label'] }}
                    </span>
                </div>
                
                {{-- Ícone Indicador --}}
                <div class="flex-shrink-0 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Total e Botão Adicionar Pedidos --}}
    <div class="p-4 bg-white space-y-3">
        @if($currentCheck)
        <div wire:key="total-display-{{ $currentCheck->id }}-{{ $currentCheck->total }}">
            <x-total-display :total="$currentCheck->total" />
        </div>
        @endif

        <div wire:loading wire:target="refreshData, updateOrderStatus, updateAllOrderStatus" class="w-full text-center py-2">
            <span class="text-sm text-gray-500 animate-pulse">Atualizando totais...</span>
        </div>

        @if($selectedTable->status === 'close')
        <div class="w-full bg-red-100 border-2 border-red-300 text-red-700 py-4 rounded-xl font-bold text-center">
            Mesa Fechada - Não é possível adicionar pedidos
        </div>
        @elseif($selectedTable->status === 'releasing')
        <div class="w-full bg-teal-100 border-2 border-teal-300 text-teal-800 py-4 rounded-xl font-bold text-center">
            Mesa em Liberação - Não é possível adicionar pedidos
        </div>
        @elseif($selectedTable->status === 'reserved')
        <div class="w-full bg-purple-100 border-2 border-purple-300 text-purple-800 py-4 rounded-xl font-bold text-center">
            Mesa Reservada - Não é possível adicionar pedidos
        </div>
        @elseif(!$isCheckOpen)
        <div class="w-full bg-yellow-100 border-2 border-yellow-300 text-yellow-800 py-4 rounded-xl font-bold text-center">
            Check não está Aberto - Altere o status para adicionar pedidos
        </div>
        @else
        <button
            wire:click="goToMenu"
            class="w-full bg-gradient-to-r from-orange-500 to-red-500 text-white py-4 rounded-xl font-bold text-lg flex items-center justify-center gap-3 hover:shadow-lg transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Adicionar Pedidos
        </button>
        @endif
    </div>

    {{-- Modal Alterar Status --}}
    @if($showStatusModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeStatusModal">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6" wire:click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Alterar Status</h3>
                <button wire:click="closeStatusModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                {{-- Status da Mesa --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status da Mesa</label>
                    @if($hasActiveCheck)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-2">
                        <p class="text-sm text-yellow-800">
                            <span class="font-semibold">⚠️ Atenção:</span> Não é possível alterar o status da mesa enquanto houver um check em andamento. Para fechar a mesa fisicamente, finalize ou cancele o check primeiro.
                        </p>
                    </div>
                    @endif
                    <div class="flex flex-wrap gap-2">
                        <button
                            wire:click="$set('newTableStatus', 'free')"
                            @if($hasActiveCheck) disabled @endif
                            class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $newTableStatus === 'free' ? 'bg-gray-500 text-white ring-2 ring-gray-600' : ($hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                            Livre
                        </button>
                        <button
                            wire:click="$set('newTableStatus', 'occupied')"
                            @if($hasActiveCheck) disabled @endif
                            class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $newTableStatus === 'occupied' ? 'bg-blue-500 text-white ring-2 ring-blue-600' : ($hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                            Ocupada
                        </button>
                        <button
                            wire:click="$set('newTableStatus', 'reserved')"
                            @if($hasActiveCheck) disabled @endif
                            class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $newTableStatus === 'reserved' ? 'bg-purple-500 text-white ring-2 ring-purple-600' : ($hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                            Reservada
                        </button>
                        <button
                            wire:click="$set('newTableStatus', 'releasing')"
                            @if($hasActiveCheck) disabled @endif
                            class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $newTableStatus === 'releasing' ? 'bg-teal-500 text-white ring-2 ring-teal-600' : ($hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                            Liberando
                        </button>
                        <button
                            wire:click="$set('newTableStatus', 'close')"
                            @if($hasActiveCheck) disabled @endif
                            class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $newTableStatus === 'close' ? 'bg-red-600 text-white ring-2 ring-red-700' : ($hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                            Fechada
                        </button>
                    </div>
                </div>

                {{-- Status do Check --}}
                @if($currentCheck)
                @php
                    $pendingCount = $orders->where('status', 'pending')->count();
                    $inProductionCount = $orders->where('status', 'in_production')->count();
                    $inTransitCount = $orders->where('status', 'in_transit')->count();
                @endphp
                <x-check-status-selector
                    :check="$currentCheck"
                    :newCheckStatus="$newCheckStatus"
                    :pendingCount="$pendingCount"
                    :inProductionCount="$inProductionCount"
                    :inTransitCount="$inTransitCount" />
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

    {{-- Modal de Grupo (Lista de Pedidos) --}}
    @if($showGroupModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeGroupModal">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col" wire:click.stop>
            {{-- Header --}}
            <div class="bg-gradient-to-r from-orange-500 to-red-500 p-6 text-white flex-shrink-0">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold">Pedidos do Grupo</h3>
                        <p class="text-sm text-white/90 mt-1">
                            {{ count($groupOrders) }} pedido(s) 
                            @if(count($selectedOrderIds) > 0)
                            | <strong>{{ count($selectedOrderIds) }} selecionado(s)</strong>
                            @endif
                        </p>
                    </div>
                    <button wire:click="closeGroupModal" class="text-white/80 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                {{-- Checkbox Selecionar Todos --}}
                <label class="flex items-center gap-2 mt-4 cursor-pointer">
                    <input 
                        type="checkbox" 
                        wire:click="toggleSelectAll"
                        {{ count($selectedOrderIds) === count($groupOrders) ? 'checked' : '' }}
                        class="w-5 h-5 text-white bg-white/20 border-white/40 rounded focus:ring-white focus:ring-2">
                    <span class="text-sm font-medium">Selecionar todos</span>
                </label>
            </div>

            {{-- Lista de Pedidos Individuais --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-3">
                @foreach($groupOrders as $order)
                @php
                    $orderObj = (object) $order;
                    $product = isset($order['product']) ? (object) $order['product'] : (object)['name' => 'Produto', 'price' => 0];
                    $isSelected = in_array($orderObj->id, $selectedOrderIds);
                @endphp
                <div class="bg-gray-50 rounded-lg p-4 transition border-2 {{ $isSelected ? 'border-orange-400 bg-orange-50' : 'border-gray-200' }}">
                    <div class="flex items-start gap-3">
                        {{-- Checkbox --}}
                        <input 
                            type="checkbox" 
                            wire:click="toggleOrderSelection({{ $orderObj->id }})"
                            {{ $isSelected ? 'checked' : '' }}
                            class="mt-1 w-5 h-5 text-orange-500 border-gray-300 rounded focus:ring-orange-400 cursor-pointer">
                        
                        {{-- Conteúdo --}}
                        <div class="flex-1 cursor-pointer" wire:click.stop="openDetailsFromGroup({{ $orderObj->id }})">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-lg font-bold text-gray-900">{{ $orderObj->quantity }} {{ $product->name }}</span>
                                <span class="text-sm font-semibold text-orange-600">R$ {{ number_format($product->price * $orderObj->quantity, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>Pedido #{{ $orderObj->id }}</span>
                                <span>{{ \Carbon\Carbon::parse($orderObj->created_at)->format('H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Footer --}}
            <div class="p-6 pt-0 flex-shrink-0 border-t space-y-2">
                @if(count($selectedOrderIds) > 0)
                <button
                    wire:click="openGroupActionsModal"
                    class="w-full px-4 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg font-bold hover:shadow-lg transition">
                    Ações em Grupo ({{ count($selectedOrderIds) }})
                </button>
                @endif
                <button
                    wire:click="closeGroupModal"
                    class="w-full px-4 py-3 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition">
                    Fechar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal de Ações em Grupo --}}
    @if($showGroupActionsModal && $groupActionData)
    <div class="fixed inset-0 bg-black/50 z-[60] flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full overflow-hidden" wire:click.stop>
            {{-- Header --}}
            <div class="bg-gradient-to-r from-orange-500 to-red-500 p-6 text-white">
                <h3 class="text-2xl font-bold">Ações em Grupo</h3>
                <p class="text-sm text-white/90 mt-1">{{ $groupActionData['count'] }} pedido(s) selecionado(s)</p>
            </div>

            {{-- Corpo --}}
            <div class="p-6 space-y-4">
                {{-- Resumo --}}
                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Produto</span>
                        <span class="font-bold text-gray-900">{{ $groupActionData['product_name'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Quantidade Total</span>
                        <span class="font-bold text-gray-900">{{ $groupActionData['total_quantity'] }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t pt-2">
                        <span class="text-sm font-semibold text-gray-700">Valor Total</span>
                        <span class="text-xl font-bold text-orange-600">R$ {{ number_format($groupActionData['total_price'], 2, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Alterar Status --}}
                @if($isCheckOpen)
                @php
                    // Permite transição para qualquer status (sem restrições)
                    $allStatuses = ['pending', 'in_production', 'in_transit', 'completed'];
                    $allowedTransitions = $allStatuses; // Permite todos os status
                @endphp
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">Alterar Status de Todos</label>
                    <div class="grid grid-cols-2 gap-2">
                        @php $canGoToPending = in_array('pending', $allowedTransitions); @endphp
                        <button
                            wire:click="updateGroupStatus('pending')"
                            {{ !$canGoToPending ? 'disabled' : '' }}
                            class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                                {{ $canGoToPending ? 'bg-yellow-500 hover:bg-yellow-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
                            ⏳ Aguardando
                        </button>
                        
                        @php $canGoToProduction = in_array('in_production', $allowedTransitions); @endphp
                        <button
                            wire:click="updateGroupStatus('in_production')"
                            {{ !$canGoToProduction ? 'disabled' : '' }}
                            class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                                {{ $canGoToProduction ? 'bg-blue-500 hover:bg-blue-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
                            🍳 Em Preparo
                        </button>
                        
                        @php $canGoToTransit = in_array('in_transit', $allowedTransitions); @endphp
                        <button
                            wire:click="updateGroupStatus('in_transit')"
                            {{ !$canGoToTransit ? 'disabled' : '' }}
                            class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                                {{ $canGoToTransit ? 'bg-purple-500 hover:bg-purple-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
                            🚶 Em Trânsito
                        </button>
                        
                        @php $canGoToCompleted = in_array('completed', $allowedTransitions); @endphp
                        <button
                            wire:click="updateGroupStatus('completed')"
                            {{ !$canGoToCompleted ? 'disabled' : '' }}
                            class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                                {{ $canGoToCompleted ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
                            ✓ Entregue
                        </button>
                    </div>
                </div>

                {{-- Botão Cancelar Grupo --}}
                <div class="pt-4 border-t">
                    <button
                        wire:click="cancelGroupOrders"
                        class="w-full px-4 py-3 bg-red-500 hover:bg-red-600 text-white rounded-lg font-bold transition shadow-md flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Cancelar Todos os Selecionados
                    </button>
                </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="p-6 pt-0 border-t">
                <button
                    wire:click="closeGroupActionsModal"
                    class="w-full px-4 py-3 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition">
                    Voltar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal de Filtro --}}
    @if($showFilterModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeFilterModal">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full" wire:click.stop>
            {{-- Header --}}
            <div class="bg-gradient-to-r from-orange-500 to-red-500 p-6 text-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold">Filtrar Pedidos</h3>
                    <button wire:click="closeFilterModal" class="text-white/80 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Corpo --}}
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-600 mb-4">Selecione os status que deseja visualizar:</p>

                {{-- Opções de Filtro --}}
                <div class="space-y-3">
                    <label class="flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer transition hover:bg-gray-50
                        {{ in_array('pending', $statusFilters) ? 'border-yellow-400 bg-yellow-50' : 'border-gray-200' }}">
                        <input
                            type="checkbox"
                            wire:click="toggleStatusFilter('pending')"
                            {{ in_array('pending', $statusFilters) ? 'checked' : '' }}
                            class="w-5 h-5 text-yellow-500 rounded focus:ring-yellow-400">
                        <div class="flex-1">
                            <span class="font-semibold text-gray-900">⏳ Aguardando</span>
                            <p class="text-xs text-gray-500">Pedidos ainda não iniciados</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer transition hover:bg-gray-50
                        {{ in_array('in_production', $statusFilters) ? 'border-blue-400 bg-blue-50' : 'border-gray-200' }}">
                        <input
                            type="checkbox"
                            wire:click="toggleStatusFilter('in_production')"
                            {{ in_array('in_production', $statusFilters) ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-500 rounded focus:ring-blue-400">
                        <div class="flex-1">
                            <span class="font-semibold text-gray-900">🍳 Em Preparo</span>
                            <p class="text-xs text-gray-500">Sendo preparados na cozinha</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer transition hover:bg-gray-50
                        {{ in_array('in_transit', $statusFilters) ? 'border-purple-400 bg-purple-50' : 'border-gray-200' }}">
                        <input
                            type="checkbox"
                            wire:click="toggleStatusFilter('in_transit')"
                            {{ in_array('in_transit', $statusFilters) ? 'checked' : '' }}
                            class="w-5 h-5 text-purple-500 rounded focus:ring-purple-400">
                        <div class="flex-1">
                            <span class="font-semibold text-gray-900">🚶 Em Trânsito</span>
                            <p class="text-xs text-gray-500">A caminho da mesa</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer transition hover:bg-gray-50
                        {{ in_array('completed', $statusFilters) ? 'border-green-400 bg-green-50' : 'border-gray-200' }}">
                        <input
                            type="checkbox"
                            wire:click="toggleStatusFilter('completed')"
                            {{ in_array('completed', $statusFilters) ? 'checked' : '' }}
                            class="w-5 h-5 text-green-500 rounded focus:ring-green-400">
                        <div class="flex-1">
                            <span class="font-semibold text-gray-900">✓ Entregue</span>
                            <p class="text-xs text-gray-500">Já entregues ao cliente</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer transition hover:bg-gray-50
                        {{ in_array('canceled', $statusFilters) ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                        <input
                            type="checkbox"
                            wire:click="toggleStatusFilter('canceled')"
                            {{ in_array('canceled', $statusFilters) ? 'checked' : '' }}
                            class="w-5 h-5 text-red-500 rounded focus:ring-red-400">
                        <div class="flex-1">
                            <span class="font-semibold text-gray-900">✕ Cancelado</span>
                            <p class="text-xs text-gray-500">Pedidos cancelados</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Footer --}}
            <div class="p-6 pt-0 flex gap-2">
                <button
                    wire:click="resetFilters"
                    class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition">
                    Limpar Filtros
                </button>
                <button
                    wire:click="closeFilterModal"
                    class="flex-1 px-4 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg font-bold hover:shadow-lg transition">
                    Aplicar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal de Detalhes do Pedido --}}
    @if($showDetailsModal && $orderDetails)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeDetailsModal">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full overflow-hidden" wire:click.stop>
            {{-- Header --}}
            <div class="bg-gradient-to-r from-orange-500 to-red-500 p-6 text-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold">Detalhes do Pedido</h3>
                    <button wire:click="closeDetailsModal" class="text-white/80 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Corpo --}}
            <div class="p-6 space-y-6">
                {{-- Informações do Produto --}}
                <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Produto</span>
                        <span class="font-bold text-gray-900">{{ $orderDetails['product_name'] }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Preço Unitário</span>
                        <span class="font-semibold text-gray-900">R$ {{ number_format($orderDetails['price'], 2, ',', '.') }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between border-t pt-3">
                        <span class="text-sm font-semibold text-gray-700">Total</span>
                        <span class="text-xl font-bold text-orange-600">R$ {{ number_format($orderDetails['total'], 2, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Controle de Quantidade --}}
                @if($isCheckOpen && $orderDetails['status'] === 'pending')
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">Quantidade</label>
                    
                    @if($orderDetails['available_stock'] <= 0)
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-2">
                        <p class="text-sm text-red-800 text-center">
                            ⚠️ Sem estoque disponível. Não é possível aumentar a quantidade.
                        </p>
                    </div>
                    @endif
                    
                    <div class="flex items-center gap-4">
                        <button
                            wire:click="decrementQuantity"
                            {{ $orderDetails['quantity'] <= 1 ? 'disabled' : '' }}
                            class="w-12 h-12 flex items-center justify-center rounded-lg font-bold text-xl transition shadow-md
                                {{ $orderDetails['quantity'] > 1 ? 'bg-red-500 hover:bg-red-600 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}">
                            −
                        </button>
                        
                        <div class="flex-1 text-center">
                            <span class="text-4xl font-bold text-gray-900">{{ $orderDetails['quantity'] }}</span>
                            @if($orderDetails['available_stock'] > 0)
                            <p class="text-xs text-gray-500 mt-1">Estoque: {{ $orderDetails['available_stock'] }}</p>
                            @endif
                            @if($orderDetails['quantity'] <= 1)
                            <p class="text-xs text-gray-500 mt-1">Use "Cancelar" para remover</p>
                            @endif
                        </div>
                        
                        <button
                            wire:click="incrementQuantity"
                            {{ $orderDetails['available_stock'] <= 0 ? 'disabled' : '' }}
                            class="w-12 h-12 flex items-center justify-center rounded-lg font-bold text-xl transition shadow-md
                                {{ $orderDetails['available_stock'] > 0 ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}">
                            +
                        </button>
                    </div>
                </div>
                @elseif(!$isCheckOpen)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <p class="text-sm text-yellow-800 text-center">
                        Check não está aberto. Não é possível alterar a quantidade.
                    </p>
                </div>
                @elseif($orderDetails['status'] !== 'pending')
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <p class="text-sm text-blue-800 text-center">
                        Apenas pedidos no status "Aguardando" podem ter a quantidade alterada.
                    </p>
                    <div class="text-center mt-2">
                        <span class="text-2xl font-bold text-gray-900">Quantidade: {{ $orderDetails['quantity'] }}</span>
                    </div>
                </div>
                @endif

                {{-- Status Atual e Alteração --}}
                <div class="space-y-3">
                    <label class="block text-sm font-semibold text-gray-700">Status do Pedido</label>
                    
                    {{-- Status Atual --}}
                    @php
                        $currentStatusDisplay = match($orderDetails['status']) {
                            'pending' => ['label' => '⏳ Aguardando', 'color' => 'bg-yellow-500'],
                            'in_production' => ['label' => '🍳 Em Preparo', 'color' => 'bg-blue-500'],
                            'in_transit' => ['label' => '🚶 Em Trânsito', 'color' => 'bg-purple-500'],
                            'completed' => ['label' => '✓ Entregue', 'color' => 'bg-green-500'],
                            'canceled' => ['label' => '✕ Cancelado', 'color' => 'bg-red-500'],
                            default => ['label' => 'Desconhecido', 'color' => 'bg-gray-500']
                        };
                    @endphp
                    
                    <div class="flex items-center gap-2 bg-gray-50 rounded-lg p-3 border-2 border-gray-200">
                        <span class="text-sm text-gray-600">Status Atual:</span>
                        <span class="px-3 py-1.5 {{ $currentStatusDisplay['color'] }} text-white rounded-lg font-bold text-sm shadow-sm">
                            {{ $currentStatusDisplay['label'] }}
                        </span>
                    </div>
                    
                    {{-- Botões para Alterar Status --}}
                    @if($isCheckOpen)
                    @php
                        // Permite transição para qualquer status (sem restrições)
                        $allStatuses = ['pending', 'in_production', 'in_transit', 'completed'];
                        $allowedTransitions = array_diff($allStatuses, [$orderDetails['status']]); // Permite todos exceto o atual
                    @endphp
                    <div class="space-y-2">
                        <p class="text-xs text-gray-500">Alterar para:</p>
                        <div class="grid grid-cols-2 gap-2">
                            {{-- Aguardando --}}
                            @php $canGoToPending = in_array('pending', $allowedTransitions); @endphp
                            <button
                                wire:click="updateOrderStatusFromModal('pending')"
                                {{ !$canGoToPending ? 'disabled' : '' }}
                                class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                                    {{ $canGoToPending ? 'bg-yellow-500 hover:bg-yellow-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
                                ⏳ Aguardando
                            </button>
                            
                            {{-- Em Preparo --}}
                            @php $canGoToProduction = in_array('in_production', $allowedTransitions); @endphp
                            <button
                                wire:click="updateOrderStatusFromModal('in_production')"
                                {{ !$canGoToProduction ? 'disabled' : '' }}
                                class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                                    {{ $canGoToProduction ? 'bg-blue-500 hover:bg-blue-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
                                🍳 Em Preparo
                            </button>
                            
                            {{-- Em Trânsito --}}
                            @php $canGoToTransit = in_array('in_transit', $allowedTransitions); @endphp
                            <button
                                wire:click="updateOrderStatusFromModal('in_transit')"
                                {{ !$canGoToTransit ? 'disabled' : '' }}
                                class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                                    {{ $canGoToTransit ? 'bg-purple-500 hover:bg-purple-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
                                🚶 Em Trânsito
                            </button>
                            
                            {{-- Entregue --}}
                            @php $canGoToCompleted = in_array('completed', $allowedTransitions); @endphp
                            <button
                                wire:click="updateOrderStatusFromModal('completed')"
                                {{ !$canGoToCompleted ? 'disabled' : '' }}
                                class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                                    {{ $canGoToCompleted ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
                                ✓ Entregue
                            </button>
                        </div>
                    </div>
                    @elseif(!$isCheckOpen)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <p class="text-sm text-yellow-800 text-center">
                            Check não está aberto. Não é possível alterar o status.
                        </p>
                    </div>
                    @endif
                </div>

                {{-- Botão Cancelar Pedido --}}
                @if($orderDetails['status'] !== 'canceled')
                <div class="pt-4 border-t">
                    @if(!$isCheckOpen)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-3">
                        <p class="text-sm text-yellow-800 text-center">
                            ⚠️ O check não está aberto, mas você pode cancelar o pedido se necessário.
                        </p>
                    </div>
                    @endif
                    <button
                        wire:click="cancelOrderFromModal"
                        class="w-full px-4 py-3 bg-red-500 hover:bg-red-600 text-white rounded-lg font-bold transition shadow-md flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Cancelar Pedido
                    </button>
                </div>
                @else
                <div class="pt-4 border-t">
                    <div class="bg-gray-100 border border-gray-300 rounded-lg p-3">
                        <p class="text-sm text-gray-600 text-center">
                            Este pedido já está cancelado.
                        </p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Confirmar Cancelamento --}}
    @if($showCancelModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeCancelModal">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden" wire:click.stop>
            {{-- Header com icone --}}
            <div class="bg-gradient-to-r from-red-500 to-red-600 p-6 text-white">
                <div class="flex items-center justify-center mb-3">
                    <div class="bg-white/20 p-3 rounded-full">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-center">
                    Remover Item?
                </h3>
            </div>

            {{-- Corpo do modal --}}
            <div class="p-6">
                @if($orderToCancelData)
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-600 text-sm">Produto</span>
                        <span class="font-bold text-gray-900">{{ $orderToCancelData['product_name'] }}</span>
                    </div>

                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-600 text-sm">Quantidade Atual</span>
                        <span class="font-bold text-gray-900">{{ $orderToCancelData['quantity'] }}x</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 text-sm">Valor Unitário</span>
                        <span class="font-bold text-gray-700">R$ {{ number_format($orderToCancelData['price'], 2, ',', '.') }}</span>
                    </div>
                </div>
                @endif

                <p class="text-gray-600 text-center mb-6">
                    Deseja remover este item do pedido?
                    @if($orderToCancelData && $orderToCancelData['quantity'] > 1)
                    <br><span class="text-sm">Você pode remover apenas 1 unidade ou todas.</span>
                    @else
                    <br><span class="text-sm">Esta ação não pode ser desfeita.</span>
                    @endif
                </p>

                {{-- Botões de ação --}}
                @if($orderToCancelData && $orderToCancelData['quantity'] > 1)
                <div class="flex flex-col gap-3">
                    <div class="flex gap-3">
                        <button
                            wire:click="confirmCancelOrder({{ $orderToCancelData['quantity'] }})"
                            class="flex-1 border-2 border-red-500 text-red-600 hover:bg-red-50 font-bold py-3 px-4 rounded-lg transition">
                            Remover Todos
                        </button>
                        <button
                            wire:click="confirmCancelOrder(1)"
                            class="flex-1 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-bold py-3 px-4 rounded-lg transition shadow-md">
                            Remover 1 Unidade
                        </button>
                    </div>
                    <button
                        wire:click="closeCancelModal"
                        class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-4 rounded-lg transition">
                        Cancelar
                    </button>
                </div>
                @else
                <div class="flex gap-3">
                    <button
                        wire:click="closeCancelModal"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-4 rounded-lg transition">
                        Cancelar
                    </button>
                    <button
                        wire:click="confirmCancelOrder"
                        class="flex-1 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold py-3 px-4 rounded-lg transition shadow-lg">
                        Sim, Remover
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>