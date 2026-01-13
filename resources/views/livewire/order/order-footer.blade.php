<div class="bg-gradient-to-r from-orange-500 to-red-500 text-white p-4 mb-4 sticky bottom-0 z-10 shadow-lg">
    <div class="flex items-center justify-between gap-4">
        {{-- Total Geral (Esquerda) --}}
        <div class="flex-1">
            <div class="flex items-center justify-left gap-2">
                <span class="text-lg font-semibold">Total:</span>
                <span class="text-lg font-bold">R$ {{ number_format($this->checkTotal, 2, ',', '.') }}</span>
            </div>
        </div>

        {{-- Botão ou Mensagem de Status (Direita) --}}
        <div class="flex-shrink-0">
            @if($selectedTable->status === 'close')
            <div class="bg-red-100 border-2 border-red-300 text-red-700 py-3 px-6 rounded-xl font-bold text-center text-sm">
                Mesa Fechada
            </div>
            @elseif($selectedTable->status === 'releasing')
            <div class="bg-teal-100 border-2 border-teal-300 text-teal-800 py-3 px-6 rounded-xl font-bold text-center text-sm">
                Mesa em Liberação
            </div>
            @elseif($selectedTable->status === 'reserved')
            <div class="bg-purple-100 border-2 border-purple-300 text-purple-800 py-3 px-6 rounded-xl font-bold text-center text-sm">
                Mesa Reservada
            </div>
            @elseif(!$isCheckOpen)
            <div class="bg-yellow-100 border-2 border-yellow-300 text-yellow-800 py-3 px-6 rounded-xl font-bold text-center text-sm">
                Check não está aberto
            </div>
            @else
            <button
                wire:click="goToMenu"
                class="bg-white text-orange-600 hover:bg-orange-50 px-2 py-3 rounded-xl font-bold shadow-lg transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Adicionar
            </button>
            @endif
        </div>
    </div>

    <div wire:loading wire:target="goToMenu" class="w-full text-center py-2 mt-3">
        <span class="text-sm text-white animate-pulse">Carregando...</span>
    </div>
</div>
