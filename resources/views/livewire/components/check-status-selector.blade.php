<div>
    <label class="block text-sm font-semibold text-gray-700 mb-2">Status do Check</label>

    {{-- Botões de Status --}}
    <div class="flex flex-wrap gap-2">
        <button
            wire:click="selectStatus('Open')"
            @if(!$isOpenAllowed) disabled @endif
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                {{ !$isOpenAllowed ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Open' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
            Aberto
        </button>
        <button
            wire:click="selectStatus('Closed')"
            @if(!$isClosedAllowed) disabled @endif
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                {{ !$isClosedAllowed ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Closed' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
            Fechado
        </button>
        <button
            wire:click="selectStatus('Paid')"
            @if(!$isPaidAllowed) disabled @endif
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                {{ !$isPaidAllowed ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Paid' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
            Pago
        </button>
        <button
            wire:click="selectStatus('Canceled')"
            @if(!$isCanceledAllowed) disabled @endif
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                {{ !$isCanceledAllowed ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Canceled' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
            Cancelar
        </button>
    </div>
    @if(count($checkStatusAllowed) === 0)
    <p class="mt-2 text-xs text-orange-600 font-medium">
        <i class="fas fa-info-circle mr-1"></i>
        Status do check bloqueado, verifique pedidos pendentes.
    </p>
    @endif
</div>
