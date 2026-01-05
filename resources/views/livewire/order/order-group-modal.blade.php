<div>
@if($show)
<div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeModal">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col" @click.stop>
        {{-- Header --}}
        <div class="bg-gradient-to-r from-orange-500 to-red-500 p-6 text-white flex-shrink-0">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold">Lista de Pedidos</h3>
                    @if(!empty($groupOrders))
                        <p class="text-sm text-white/80 mt-1">
                            {{ $groupOrders[0]['product']['name'] ?? '' }} 
                            ({{ count($groupOrders) }} {{ count($groupOrders) === 1 ? 'pedido' : 'pedidos' }})
                        </p>
                    @endif
                </div>
                <button wire:click="closeModal" class="text-white/80 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Lista de Pedidos Individuais --}}
        <div class="flex-1 overflow-y-auto p-6 space-y-3">
            @foreach($groupOrders as $order)
            @php
            $orderObj = (object) $order;
            $product = isset($order['product']) ? (object) $order['product'] : (object)['name' => 'Produto', 'price' => 0];
            $isSelected = in_array($orderObj->id, $selectedOrderIds);
            @endphp
            <div 
                wire:click="toggleOrderSelection({{ $orderObj->id }})"
                class="bg-gray-50 hover:bg-gray-100 rounded-lg p-4 transition cursor-pointer border-2 {{ $isSelected ? 'border-orange-500 bg-orange-50' : 'border-gray-200' }}">
                <div class="flex items-center gap-3">
                    {{-- Checkbox --}}
                    <div class="flex-shrink-0">
                        <div class="w-5 h-5 rounded border-2 flex items-center justify-center
                            {{ $isSelected ? 'bg-orange-500 border-orange-500' : 'bg-white border-gray-300' }}">
                            @if($isSelected)
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                            @endif
                        </div>
                    </div>

                    {{-- Quantidade --}}
                    <div class="flex-shrink-0 w-12 text-center">
                        <span class="text-2xl font-bold text-gray-700">{{ $order['quantity'] }}</span>
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900">{{ $product->name }}</p>
                        <p class="text-sm text-gray-500">R$ {{ number_format($order['price'] * $order['quantity'], 2, ',', '.') }}</p>
                    </div>

                    {{-- Botão Ver Detalhes --}}
                    <button
                        wire:click.stop="openDetailsFromGroup({{ $orderObj->id }})"
                        class="flex-shrink-0 p-2 hover:bg-white rounded-lg transition">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Footer --}}
        <div class="p-6 pt-0 flex-shrink-0 border-t space-y-2">
            @if(count($selectedOrderIds) > 0)
            <div class="bg-orange-50 rounded-lg p-3 mb-2">
                <p class="text-sm font-semibold text-orange-800 text-center">
                    {{ count($selectedOrderIds) }} {{ count($selectedOrderIds) === 1 ? 'pedido selecionado' : 'pedidos selecionados' }}
                </p>
            </div>
            <button
                wire:click="openGroupActionsModal"
                class="w-full px-4 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-bold transition shadow-md">
                Ações em Grupo
            </button>
            @endif
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
