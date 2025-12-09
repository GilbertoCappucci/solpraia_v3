<div x-data="{ 
    lastRefresh: Date.now(),
    handleFocus() {
        // Só atualiza se passou mais de 2 segundos desde o último refresh
        if (Date.now() - this.lastRefresh > 2000) {
            this.lastRefresh = Date.now();
            $wire.call('refreshData');
        }
    }
}" x-init="window.addEventListener('focus', () => handleFocus())">
    
    <x-flash-message />

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
                {{--
                <span class="text-sm opacity-90">{{ $selectedTable->name }}</span>
                --}}
            </div>
            
            {{-- Botão Toggle Alarme --}}
            <button 
                wire:click="toggleDelayAlarm"
                class="flex items-center gap-1.5 px-2 py-1 border-2 rounded-lg text-sm font-medium transition
                    {{ $delayAlarmEnabled ? 'border-red-300 bg-red-500/20 text-white' : 'border-white/30 bg-white/10 text-white/60' }}"
                title="{{ $delayAlarmEnabled ? 'Desativar' : 'Ativar' }} alarme de atraso">
                @if($delayAlarmEnabled)
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                    </svg>
                @else
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                        <path stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M4 4l12 12"/>
                    </svg>
                @endif
            </button>
        </div>
        <div class="flex items-center gap-2">
            <button 
                wire:click="openStatusModal"
                class="flex items-center gap-2 bg-white/10 hover:bg-white/20 rounded-lg px-3 py-1.5 transition-all">
                @php
                    $tableStatusConfig = match($selectedTable->status) {
                        'free' => ['label' => 'Livre', 'color' => 'gray'],
                        'occupied' => ['label' => 'Ocupada', 'color' => 'green'],
                        'reserved' => ['label' => 'Reservada', 'color' => 'purple'],
                        'releasing' => ['label' => 'Liberando', 'color' => 'teal'],
                        'close' => ['label' => 'Fechada', 'color' => 'red'],
                        default => ['label' => 'Livre', 'color' => 'gray']
                    };
                    $checkStatusConfig = $currentCheck ? match($currentCheck->status) {
                        'Open' => ['label' => 'Aberto', 'color' => 'green'],
                        'Closed' => ['label' => 'Fechado', 'color' => 'red'],
                        'Paid' => ['label' => 'Pago', 'color' => 'gray'],
                        'Canceled' => ['label' => 'Cancelado', 'color' => 'orange'],
                        default => ['label' => 'Aberto', 'color' => 'green']
                    } : null;
                @endphp
                
                <x-order-status-badge 
                    label="Mesa" 
                    :value="$tableStatusConfig['label']" 
                    :color="$tableStatusConfig['color']" />
                
                @if($currentCheck)
                    <x-order-status-badge 
                        label="Check" 
                        :value="$checkStatusConfig['label']" 
                        :color="$checkStatusConfig['color']" 
                        :isDivider="true" />
                @endif
                
                <svg class="w-4 h-4 opacity-75 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Seção de Pedidos Ativos --}}
    <div class="bg-gray-50 p-4 space-y-3">
        <x-order-status-card 
            title="AGUARDANDO"
            :orders="$pendingOrders"
            :totalTime="$pendingTime"
            color="yellow"
            :showCancel="true"
            nextStatus="in_production"
            previousStatus=""
            :isCheckOpen="$isCheckOpen"
            :delayAlarmEnabled="$delayAlarmEnabled" />

        <x-order-status-card 
            title="EM PREPARO"
            :orders="$inProductionOrders"
            :totalTime="$inProductionTime"
            color="blue"
            previousStatus="pending"
            nextStatus="in_transit"
            :isCheckOpen="$isCheckOpen"
            :delayAlarmEnabled="$delayAlarmEnabled" />

        <x-order-status-card 
            title="EM TRÂNSITO"
            :orders="$inTransitOrders"
            :totalTime="$inTransitTime"
            color="purple"
            previousStatus="in_production"
            nextStatus="completed"
            :isCheckOpen="$isCheckOpen"
            :delayAlarmEnabled="$delayAlarmEnabled" />

        <x-order-status-card 
            title="ENTREGUE"
            :orders="$completedOrders"
            :totalTime="$completedTime"
            color="green"
            previousStatus="in_transit"
            :showPrice="true"
            :subtotal="$completedTotal"
            :isCheckOpen="$isCheckOpen"
            :delayAlarmEnabled="$delayAlarmEnabled" />
    </div>

    {{-- Total e Botão Adicionar Pedidos --}}
    <div class="p-4 bg-white space-y-3">
        @if($currentCheck)
            <x-total-display :total="$currentCheck->total" />
        @endif
        
        @if($selectedTable->status === 'close')
            <div class="w-full bg-red-100 border-2 border-red-300 text-red-700 py-4 rounded-xl font-bold text-center">
                Mesa Fechada - Não é possível adicionar pedidos
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
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
                        <x-check-status-selector 
                            :check="$currentCheck"
                            :newCheckStatus="$newCheckStatus"
                            :pendingCount="$pendingOrders->count()"
                            :inProductionCount="$inProductionOrders->count()"
                            :inTransitCount="$inTransitOrders->count()" />
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

    {{-- Modal Confirmar Cancelamento --}}
    @if($showCancelModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeCancelModal">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden" wire:click.stop>
                {{-- Header com icone --}}
                <div class="bg-gradient-to-r from-red-500 to-red-600 p-6 text-white">
                    <div class="flex items-center justify-center mb-3">
                        <div class="bg-white/20 p-3 rounded-full">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-center">Cancelar Pedido?</h3>
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
                                <span class="text-gray-600 text-sm">Quantidade</span>
                                <span class="font-bold text-gray-900">{{ $orderToCancelData['quantity'] }}x</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 text-sm">Valor</span>
                                <span class="font-bold text-red-600">R$ {{ number_format($orderToCancelData['price'] * $orderToCancelData['quantity'], 2, ',', '.') }}</span>
                            </div>
                        </div>
                    @endif
                    
                    <p class="text-gray-600 text-center mb-6">
                        Esta ação não pode ser desfeita. O pedido será removido permanentemente.
                    </p>
                    
                    {{-- Botões de ação --}}
                    <div class="flex gap-3">
                        <button 
                            wire:click="closeCancelModal"
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-4 rounded-lg transition">
                            Voltar
                        </button>
                        <button 
                            wire:click="confirmCancelOrder"
                            class="flex-1 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold py-3 px-4 rounded-lg transition shadow-lg">
                            Sim, Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
