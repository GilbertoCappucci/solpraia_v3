<div>
@if($showModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeModal">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6" @click.stop>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900">Alterar Status da Mesa</h3>
            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="space-y-4">
            @if($hasActiveCheck)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-3">
                    <p class="text-sm text-yellow-800">
                        <span class="font-semibold">⚠️ Atenção:</span> Não é possível alterar o status da mesa enquanto houver um check em andamento.
                    </p>
                </div>
            @endif
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Status da Mesa</label>
                <div class="flex flex-wrap gap-2">
                    <button 
                        wire:click="setStatus('free')"
                        @if($hasActiveCheck) disabled @endif
                        class="px-3 py-2 rounded-lg text-sm font-medium transition
                            {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'free' ? 'bg-gray-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                        Livre
                    </button>
                    <button 
                        wire:click="setStatus('occupied')"
                        @if($hasActiveCheck) disabled @endif
                        class="px-3 py-2 rounded-lg text-sm font-medium transition
                            {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'occupied' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                        Ocupada
                    </button>
                    <button 
                        wire:click="setStatus('reserved')"
                        @if($hasActiveCheck) disabled @endif
                        class="px-3 py-2 rounded-lg text-sm font-medium transition
                            {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'reserved' ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                        Reservada
                    </button>
                    <button 
                        wire:click="setStatus('releasing')"
                        @if($hasActiveCheck) disabled @endif
                        class="px-3 py-2 rounded-lg text-sm font-medium transition
                            {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'releasing' ? 'bg-teal-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                        Liberando
                    </button>
                    <button 
                        wire:click="setStatus('closed')"
                        @if($hasActiveCheck) disabled @endif
                        class="px-3 py-2 rounded-lg text-sm font-medium transition
                            {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'closed' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                        Fechada
                    </button>
                </div>
            </div>

            <div class="flex justify-end mt-4">
                <button 
                    wire:click="closeModal"
                    class="px-4 py-3 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition">
                    Fechar
                </button>
            </div>
        </div>
    </div>
    </div>
@endif
</div>
