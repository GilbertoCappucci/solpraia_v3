<div>
@if($show && $orderDetails)
<div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeModal">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full overflow-hidden" @click.stop>
        {{-- Header --}}
        <div class="bg-gradient-to-r from-orange-500 to-red-500 p-4 text-white">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold">Detalhes do Pedido</h3>
                <button wire:click="closeModal" class="text-white/80 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Corpo --}}
        <div class="p-4 space-y-4">
            {{-- Informações do Produto --}}
            <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Produto</span>
                    <span class="font-bold text-gray-900">{{ $orderDetails['product_name'] }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Preço Unitário</span>
                    <span class="font-semibold text-gray-900">R$ {{ number_format($orderDetails['price'], 2, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Quantidade</span>
                    <span class="font-semibold text-gray-900">{{ $orderDetails['quantity'] }}</span>
                </div>
                <div class="flex items-center justify-between border-t pt-3">
                    <span class="text-sm font-semibold text-gray-700">Total</span>
                    <span class="text-xl font-bold text-orange-600">R$ {{ number_format($orderDetails['total'], 2, ',', '.') }}</span>
                </div>
            </div>

            {{-- Controle de Quantidade --}}
            @if($currentCheck && $currentCheck->status === 'Open' && $orderDetails['status'] === 'pending')
            <div class="space-y-1">
                <div class="flex items-center gap-4">
                    <button
                        wire:click.stop="decrementQuantity"
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
                        wire:click.stop="incrementQuantity"
                        {{ $orderDetails['available_stock'] == 0 ? 'disabled' : '' }}
                        class="w-12 h-12 flex items-center justify-center rounded-lg font-bold text-xl transition shadow-md
                            {{ $orderDetails['available_stock'] != 0 ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}">
                        +
                    </button>
                </div>
            </div>
            @endif

            {{-- Alteração de Status --}}
            <div class="space-y-2">
                {{-- Botões para Alterar Status --}}
                @if($currentCheck && $currentCheck->status === 'Open')
                @php
                $allStatuses = ['pending', 'in_production', 'in_transit', 'completed'];
                $allowedTransitions = array_diff($allStatuses, [$orderDetails['status']]);
                @endphp
                <div>
                    <div class="grid grid-cols-2 gap-2">
                        {{-- Aguardando --}}
                        @php $canGoToPending = in_array('pending', $allowedTransitions); @endphp
                        <button
                            wire:click="updateOrderStatus('pending')"
                            {{ !$canGoToPending ? 'disabled' : '' }}
                            class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                                {{ $canGoToPending ? 'bg-yellow-500 hover:bg-yellow-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
                            ⏳ Aguardando
                        </button>

                        {{-- Em Preparo --}}
                        @php $canGoToProduction = in_array('in_production', $allowedTransitions); @endphp
                        <button
                            wire:click="updateOrderStatus('in_production')"
                            {{ !$canGoToProduction ? 'disabled' : '' }}
                            class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                                {{ $canGoToProduction ? 'bg-blue-500 hover:bg-blue-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
                            🍳 Em Preparo
                        </button>

                        {{-- Em Trânsito --}}
                        @php $canGoToTransit = in_array('in_transit', $allowedTransitions); @endphp
                        <button
                            wire:click="updateOrderStatus('in_transit')"
                            {{ !$canGoToTransit ? 'disabled' : '' }}
                            class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                                {{ $canGoToTransit ? 'bg-purple-500 hover:bg-purple-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
                            🚶 Em Trânsito
                        </button>

                        {{-- Entregue --}}
                        @php $canGoToCompleted = in_array('completed', $allowedTransitions); @endphp
                        <button
                            wire:click="updateOrderStatus('completed')"
                            {{ !$canGoToCompleted ? 'disabled' : '' }}
                            class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                                {{ $canGoToCompleted ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
                            ✓ Entregue
                        </button>
                    </div>
                </div>
                @endif
            </div>

            {{-- Botão Remover Pedido --}}
            @if($orderDetails['status'] !== 'canceled')
            <div class="pt-2 border-t">
                <button
                    wire:click="cancelOrder"
                    class="w-full px-4 py-3 bg-red-500 hover:bg-red-600 text-white rounded-lg font-bold transition shadow-md flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Remover
                </button>
            </div>
            @endif
        </div>
    </div>
</div>
@endif
</div>
