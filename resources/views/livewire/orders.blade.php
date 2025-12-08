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
                <span class="text-sm opacity-90">{{ $selectedTable->name }}</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button 
                wire:click="openStatusModal"
                class="flex items-center gap-2 bg-white/10 hover:bg-white/20 rounded-lg px-3 py-1.5 transition-all">
                @php
                    $tableStatusConfig = match($selectedTable->status) {
                        'free' => ['label' => 'Livre', 'color' => 'gray'],
                        'occupied' => ['label' => 'Ocupada', 'color' => 'blue'],
                        'reserved' => ['label' => 'Reservada', 'color' => 'purple'],
                        'close' => ['label' => 'Fechada', 'color' => 'red'],
                        default => ['label' => 'Livre', 'color' => 'gray']
                    };
                    $checkStatusConfig = $currentCheck ? match($currentCheck->status) {
                        'Open' => ['label' => 'Aberto', 'color' => 'green'],
                        'Closing' => ['label' => 'Fechando', 'color' => 'yellow'],
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
            previousStatus="" />

        <x-order-status-card 
            title="EM PREPARO"
            :orders="$inProductionOrders"
            :totalTime="$inProductionTime"
            color="blue"
            previousStatus="pending"
            nextStatus="in_transit" />

        <x-order-status-card 
            title="EM TRÂNSITO"
            :orders="$inTransitOrders"
            :totalTime="$inTransitTime"
            color="purple"
            previousStatus="in_production"
            nextStatus="completed" />

        <x-order-status-card 
            title="ENTREGUE"
            :orders="$completedOrders"
            :totalTime="$completedTime"
            color="green"
            previousStatus="in_transit"
            :showPrice="true"
            :subtotal="$completedTotal" />
    </div>

    {{-- Total e Botão Adicionar Pedidos --}}
    <div class="p-4 bg-white space-y-3">
        @if($currentCheck)
            <x-total-display :total="$currentCheck->total" />
        @endif
        
        @if($selectedTable->status !== 'close')
            <button 
                wire:click="goToMenu"
                class="w-full bg-gradient-to-r from-orange-500 to-red-500 text-white py-4 rounded-xl font-bold text-lg flex items-center justify-center gap-3 hover:shadow-lg transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Adicionar Pedidos
            </button>
        @else
            <div class="w-full bg-red-100 border-2 border-red-300 text-red-700 py-4 rounded-xl font-bold text-center">
                Mesa Fechada - Não é possível adicionar pedidos
            </div>
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
                        @php
                            $hasActiveCheck = $currentCheck && in_array($currentCheck->status, ['Open', 'Closing', 'Closed']);
                        @endphp
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
                            <button 
                                wire:click="$set('newTableStatus', 'close')"
                                @if($hasActiveCheck) disabled @endif
                                class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'close' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                                Fechada
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
                                
                                // Regras de bloqueio baseadas no status atual do check
                                // Open: só pode ir para Fechando (se pedidos entregues), não pode pular para Fechado/Pago
                                // Closing: só pode ir para Closed (próximo passo lógico) ou voltar para Open
                                // Closed: pode ir para Paid ou voltar para Open (NÃO pode voltar para Closing)
                                // Paid: livre navegação
                                $blockClosingButton = match($currentCheck->status) {
                                    'Open' => $hasIncompleteOrders,
                                    'Closed' => true, // Bloqueado quando Closed - não pode voltar
                                    default => false
                                };
                                $blockClosedButton = ($currentCheck->status === 'Open'); // Bloqueado quando Open
                                $blockPaidButton = in_array($currentCheck->status, ['Open', 'Closing']); // Bloqueado em Open e Closing, liberado em Closed
                            @endphp
                            @if($blockClosingButton)
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-2">
                                    <p class="text-sm text-yellow-800">
                                        <span class="font-semibold">⚠️ Atenção:</span> Só é possível iniciar o fechamento quando todos os pedidos estiverem entregues.
                                    </p>
                                </div>
                            @elseif($currentCheck->status === 'Open')
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-2">
                                    <p class="text-sm text-blue-800">
                                        <span class="font-semibold">💡 Fluxo:</span> Open → Fechando → Fechado → Pago
                                    </p>
                                </div>
                            @elseif($currentCheck->status === 'Closing')
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-2">
                                    <p class="text-sm text-blue-800">
                                        <span class="font-semibold">💡 Próximo passo:</span> Mova para "Fechado" para depois poder marcar como "Pago".
                                    </p>
                                </div>
                            @elseif($currentCheck->status === 'Closed')
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-2">
                                    <p class="text-sm text-blue-800">
                                        <span class="font-semibold">💡 Opções:</span> Marque como "Pago" para finalizar ou volte para "Aberto" para reabrir.
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
                                    @if($blockClosedButton) disabled @endif
                                    class="px-3 py-2 rounded-lg text-sm font-medium transition
                                        {{ $blockClosedButton ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Closed' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                                    Fechado
                                </button>
                                <button 
                                    wire:click="$set('newCheckStatus', 'Paid')"
                                    @if($blockPaidButton) disabled @endif
                                    class="px-3 py-2 rounded-lg text-sm font-medium transition
                                        {{ $blockPaidButton ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Paid' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
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
