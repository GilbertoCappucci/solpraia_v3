<div>
    <x-flash-message />

    {{-- Header --}}
    <div class="bg-gradient-to-r from-green-500 to-emerald-500 text-white p-3 flex items-center justify-between sticky top-0 z-10 shadow-md">
        <div class="flex items-center gap-2">
            <button wire:click="cancel" class="p-1.5 hover:bg-white/20 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <div>
                <h2 class="text-xl font-bold">Pagamento</h2>
                <p class="text-sm opacity-90">Mesa {{ $table->number }} - {{ $table->name }}</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-sm opacity-90">Total</p>
            <p class="text-2xl font-bold">R$ {{ number_format($totalAmount, 2, ',', '.') }}</p>
        </div>
    </div>

    {{-- Corpo --}}
    <div class="p-4 space-y-4">
        {{-- Informações do Pedido --}}
        <div class="bg-white rounded-xl shadow-md p-4">
            <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Selecione os Itens para Pagamento
            </h3>
            
            <div class="space-y-2">
                @foreach($orders as $order)
                <div 
                    wire:click="toggleOrder({{ $order->id }})"
                    class="flex items-center justify-between py-3 px-3 border-2 rounded-lg cursor-pointer transition
                        {{ in_array($order->id, $selectedOrders) ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-green-300' }}">
                    <div class="flex items-center gap-3">
                        {{-- Checkbox --}}
                        <div class="flex-shrink-0">
                            <div class="w-6 h-6 rounded border-2 flex items-center justify-center
                                {{ in_array($order->id, $selectedOrders) ? 'border-green-500 bg-green-500' : 'border-gray-300' }}">
                                @if(in_array($order->id, $selectedOrders))
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                @endif
                            </div>
                        </div>
                        
                        {{-- Quantidade e Nome --}}
                        <div class="flex items-center gap-2">
                            <span class="text-2xl font-bold text-gray-700">{{ $order->quantity }}</span>
                            <span class="font-semibold text-gray-900">{{ $order->product->name }}</span>
                        </div>
                    </div>
                    
                    {{-- Preço --}}
                    <span class="text-lg font-bold text-gray-900">R$ {{ number_format($order->quantity * $order->price, 2, ',', '.') }}</span>
                </div>
                @endforeach
                
                {{-- Total --}}
                <div class="flex items-center justify-between pt-3 border-t-2 border-gray-200 mt-3">
                    <span class="font-bold text-gray-900">Total Selecionado ({{ count($selectedOrders) }} item{{ count($selectedOrders) != 1 ? 's' : '' }})</span>
                    <span class="text-xl font-bold text-green-600">R$ {{ number_format($totalAmount, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Métodos de Pagamento --}}
        <div class="bg-white rounded-xl shadow-md p-4">
            <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                Método de Pagamento
            </h3>
            
            <div class="space-y-2">
                <p class="text-sm text-gray-600 mb-3">Selecione como deseja receber o pagamento:</p>
                
                <div class="grid grid-cols-3 gap-2">
                    <button class="p-3 border-2 border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition text-center">
                        <div class="text-2xl mb-1">💳</div>
                        <div class="text-xs font-medium">Cartão</div>
                    </button>
                    <button class="p-3 border-2 border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition text-center">
                        <div class="text-2xl mb-1">💵</div>
                        <div class="text-xs font-medium">Dinheiro</div>
                    </button>
                    <button class="p-3 border-2 border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition text-center">
                        <div class="text-2xl mb-1">📱</div>
                        <div class="text-xs font-medium">PIX</div>
                    </button>
                </div>
                
                <p class="text-xs text-gray-500 mt-2 text-center">
                    * Botões informativos - Pagamento confirmado ao clicar em "Confirmar Pagamento"
                </p>
            </div>
        </div>

        {{-- Botão Confirmar --}}
        <button 
            wire:click="confirmPayment"
            class="w-full py-4 bg-green-500 hover:bg-green-600 text-white rounded-xl font-bold text-lg shadow-lg transition flex items-center justify-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Confirmar Pagamento
        </button>

        <button 
            wire:click="cancel"
            class="w-full py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-semibold transition">
            Cancelar
        </button>
    </div>
</div>
