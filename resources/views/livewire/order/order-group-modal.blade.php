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
            //dd($order);
            @endphp
            <div 
                wire:click="toggleOrderSelection({{ $orderObj->id }})"
                class="bg-gray-50 hover:bg-gray-100 rounded-lg p-4 transition cursor-pointer border-2 {{ $isSelected ? 'border-orange-500 bg-orange-50' : 'border-gray-200' }}">
                
                {{-- Informações da order --}}
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
                        
                        <div class="mt-2 flex flex-col items-center space-y-1">
                            <button
                                type="button"
                                wire:click.stop="increaseOrderQuantity({{ $orderObj->id }})"
                                class="w-9 h-9 bg-gray-100 hover:bg-gray-200 active:bg-gray-300 rounded-md flex items-center justify-center touch-manipulation"
                                title="Aumentar quantidade"
                                aria-label="Aumentar quantidade">
                                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>

                            <button
                                type="button"
                                wire:click.stop="decreaseOrderQuantity({{ $orderObj->id }})"
                                class="w-9 h-9 bg-gray-100 hover:bg-gray-200 active:bg-gray-300 rounded-md flex items-center justify-center touch-manipulation"
                                title="Diminuir quantidade"
                                aria-label="Diminuir quantidade">
                                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900">{{ $product->name }}</p>
                        <p class="text-sm text-gray-500">R$ {{ number_format($order['price'] * $order['quantity'], 2, ',', '.') }}</p>
                    </div>

                    {{-- Status atual --}}
                    <div class="flex-shrink-0">
                        @php
                            $currentStatus = $order['status'];
                            $statusClass = \App\Enums\OrderStatusEnum::colorsButton(\App\Enums\OrderStatusEnum::from($currentStatus));

                            $statusLabel = \App\Enums\OrderStatusEnum::getLabel(\App\Enums\OrderStatusEnum::from($currentStatus));
                        @endphp

                        <span class="px-2 py-1 text-sm font-medium rounded-full {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Footer --}}
        <div class="p-6 pt-0 flex-shrink-0 border-t space-y-2">

            <div class="bg-orange-50 rounded-lg p-3 mb-2">
                <p class="text-sm font-semibold text-orange-800 text-center">
                    {{ count($selectedOrderIds) }} {{ count($selectedOrderIds) === 1 ? 'pedido selecionado' : 'pedidos selecionados' }}
                </p>
            </div>

            {{-- Pagar --}}
            @if($this->buttonPayVisible)
            <button
                wire:click="payOrders"
                class="w-full px-4 py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg font-bold transition shadow-md flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Pagar
            </button>
            @endif

            {{-- Status --}}
            <div class="grid grid-cols-2 gap-2 mt-2">
                {{-- Aguardando --}}
                <button
                    wire:click="updateGroupStatus('{{ \App\Enums\OrderStatusEnum::PENDING}}')"
                    class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                        {{ \App\Enums\OrderStatusEnum::colorsButton(\App\Enums\OrderStatusEnum::PENDING) }}">
                    ⏳ {{ \App\Enums\OrderStatusEnum::getLabel(\App\Enums\OrderStatusEnum::PENDING) }}
                </button>
                {{-- Em Preparo --}}
                <button
                    wire:click="updateGroupStatus('{{ \App\Enums\OrderStatusEnum::IN_PRODUCTION}}')"
                    class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                        {{ \App\Enums\OrderStatusEnum::colorsButton(\App\Enums\OrderStatusEnum::IN_PRODUCTION) }}">
                    🍳 {{ \App\Enums\OrderStatusEnum::getLabel(\App\Enums\OrderStatusEnum::IN_PRODUCTION) }}
                </button>
                {{-- Em Trânsito --}}
                <button
                    wire:click="updateGroupStatus('{{ \App\Enums\OrderStatusEnum::IN_TRANSIT}}')"
                    class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                        {{ \App\Enums\OrderStatusEnum::colorsButton(\App\Enums\OrderStatusEnum::IN_TRANSIT) }}">
                    🚚 {{ \App\Enums\OrderStatusEnum::getLabel(\App\Enums\OrderStatusEnum::IN_TRANSIT) }}
                </button>

                {{-- Entregue --}}
                <button
                    wire:click="updateGroupStatus('{{ \App\Enums\OrderStatusEnum::COMPLETED}}')"
                    class="px-4 py-2.5 rounded-lg font-medium transition shadow-sm
                        {{ \App\Enums\OrderStatusEnum::colorsButton(\App\Enums\OrderStatusEnum::COMPLETED) }}">
                    ✅ {{ \App\Enums\OrderStatusEnum::getLabel(\App\Enums\OrderStatusEnum::COMPLETED) }}
                </button>
            </div>

            {{-- Cancel Orders --}}
            <button
                wire:click="openCancelOrdersConfirmationModal"
                class="w-full px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium transition">
                Cancelar Pedidos
            </button>

            {{-- Fechar --}}
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
