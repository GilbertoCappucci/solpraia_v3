<div>
@if($show && $groupActionData)
<div class="fixed inset-0 bg-black/50 z-[60] flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full overflow-hidden" @click.stop>
        {{-- Header --}}
        <div class="bg-gradient-to-r from-orange-500 to-red-500 p-6 text-white">
            <h3 class="text-xl font-bold">Ações em Grupo</h3>
            <p class="text-sm text-white/80 mt-1">{{ $groupActionData['count'] }} {{ $groupActionData['count'] === 1 ? 'pedido selecionado' : 'pedidos selecionados' }}</p>
        </div>

        {{-- Corpo --}}
        <div class="p-6 space-y-4">
            {{-- Resumo da Seleção --}}
            <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Produto</span>
                    <span class="font-semibold text-gray-900">{{ $groupActionData['product_name'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Quantidade Total</span>
                    <span class="font-semibold text-gray-900">{{ $groupActionData['total_quantity'] }}</span>
                </div>
                <div class="flex items-center justify-between border-t pt-2">
                    <span class="text-sm font-semibold text-gray-700">Valor Total</span>
                    <span class="text-lg font-bold text-orange-600">R$ {{ number_format($groupActionData['total_price'], 2, ',', '.') }}</span>
                </div>
            </div>

            {{-- Alterar Status em Grupo --}}
            @if($currentCheck && $currentCheck->status === 'Open')
            <div class="space-y-2">
                <h4 class="font-semibold text-gray-900">Alterar Status</h4>
                @php
                $currentStatus = $groupActionData['status'];
                $allStatuses = ['pending', 'in_production', 'in_transit', 'completed'];
                $allowedTransitions = array_diff($allStatuses, [$currentStatus]);
                @endphp
                <div class="grid grid-cols-2 gap-2">
                    {{-- Aguardando --}}
                    @php $canGoToPending = in_array('pending', $allowedTransitions); @endphp
                    <button
                        wire:click="updateGroupStatus('pending')"
                        {{ !$canGoToPending ? 'disabled' : '' }}
                        class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                            {{ $canGoToPending ? 'bg-yellow-500 hover:bg-yellow-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
                        ⏳ Aguardando
                    </button>

                    {{-- Em Preparo --}}
                    @php $canGoToProduction = in_array('in_production', $allowedTransitions); @endphp
                    <button
                        wire:click="updateGroupStatus('in_production')"
                        {{ !$canGoToProduction ? 'disabled' : '' }}
                        class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                            {{ $canGoToProduction ? 'bg-blue-500 hover:bg-blue-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
                        🍳 Em Preparo
                    </button>

                    {{-- Em Trânsito --}}
                    @php $canGoToTransit = in_array('in_transit', $allowedTransitions); @endphp
                    <button
                        wire:click="updateGroupStatus('in_transit')"
                        {{ !$canGoToTransit ? 'disabled' : '' }}
                        class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                            {{ $canGoToTransit ? 'bg-purple-500 hover:bg-purple-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
                        🚶 Em Trânsito
                    </button>

                    {{-- Entregue --}}
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
            @endif

            {{-- Cancelar em Grupo --}}
            <div class="border-t pt-4">
                <button
                    wire:click="cancelGroupOrders"
                    class="w-full px-4 py-3 bg-red-500 hover:bg-red-600 text-white rounded-lg font-bold transition shadow-md flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Cancelar Todos
                </button>
            </div>
        </div>

        {{-- Footer --}}
        <div class="p-6 pt-0 border-t">
            <button
                wire:click="closeModal"
                class="w-full px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition">
                Fechar
            </button>
        </div>
    </div>
</div>
@endif
</div>
