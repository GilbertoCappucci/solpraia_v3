<div>
    <label class="block text-sm font-semibold text-gray-700 mb-2">Status da Mesa</label>
    @if($hasActiveCheck)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-2">
            <p class="text-sm text-yellow-800">
                <span class="font-semibold">⚠️ Atenção:</span> Não é possível alterar o status da mesa enquanto houver um check em andamento. Para alterar o status da mesa, finalize ou cancele o check primeiro.
            </p>
        </div>
    @endif
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
            wire:click="setStatus('close')"
            @if($hasActiveCheck) disabled @endif
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'close' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
            Fechada
        </button>
    </div>
    <p class="mt-2 text-xs text-gray-500">
        • <strong>Livre:</strong> Disponível para uso<br>
        • <strong>Ocupada:</strong> Em uso por clientes<br>
        • <strong>Reservada:</strong> Reservada para clientes<br>
        • <strong>Liberando:</strong> Aguardando limpeza/preparação<br>
        • <strong>Fechada:</strong> Não aceita novos pedidos
    </p>
</div>
