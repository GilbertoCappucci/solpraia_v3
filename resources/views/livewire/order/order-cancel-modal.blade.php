<div>
@if($show)
<div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeModal">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden" @click.stop>
        {{-- Header com icone --}}
        <div class="bg-gradient-to-r from-red-500 to-red-600 p-6 text-white">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 p-3 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold">Confirmar Cancelamento</h3>
                    <p class="text-sm text-white/80 mt-1">Esta ação não pode ser desfeita</p>
                </div>
            </div>
        </div>

        {{-- Corpo do modal --}}
        @if($orderToCancelData)
        <div class="p-6">
            @foreach ($orderToCancelData as $order)
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <h4 class="font-semibold text-gray-900 mb-2">{{ $order['product_name'] }}</h4>
                <div class="flex items-center justify-between text-sm text-gray-600">
                    <span>Quantidade: <span class="font-semibold">{{ $order['quantity'] }}</span></span>
                    <span>Valor: <span class="font-semibold">R$ {{ number_format($order['price'] * $order['quantity'], 2, ',', '.') }}</span></span>
                </div>
            </div>
            @endforeach


            <p class="text-gray-600 mb-6 text-center">
                Tem certeza que deseja remover este item?
            </p>

            {{-- Botões de Ação --}}
            <div class="space-y-3">
                {{-- Opção para única unidade --}}
                <button
                    wire:click="confirmCancelOrder()"
                    class="w-full px-4 py-3 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium transition">
                    Confirmar Remoção
                </button>

                <button
                    wire:click="closeModal"
                    class="w-full px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition">
                    Cancelar
                </button>
            </div>
        </div>
        @endif
    </div>
</div>
@endif
</div>
