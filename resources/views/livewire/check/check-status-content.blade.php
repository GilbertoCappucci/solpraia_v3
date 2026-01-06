@if($check)
<div class="mb-4">
    <p class="text-sm text-gray-600">Check #{{ $check->id }} - Mesa {{ $check->table->number ?? 'N/A' }}</p>
    <p class="text-lg font-semibold text-gray-900">Total: R$ {{ number_format($check->total, 2, ',', '.') }}</p>
</div>

{{-- Status Buttons --}}
<div class="space-y-4">
    <label class="block text-sm font-semibold text-gray-700 mb-2">Selecione o novo status</label>
    
    @php
    $isOpenAllowed = in_array('Open', $checkStatusAllowed) || $newCheckStatus === 'Open';
    $isClosedAllowed = in_array('Closed', $checkStatusAllowed) || $newCheckStatus === 'Closed';
    $isPaidAllowed = in_array('Paid', $checkStatusAllowed) || $newCheckStatus === 'Paid';
    $isCanceledAllowed = in_array('Canceled', $checkStatusAllowed) || $newCheckStatus === 'Canceled';
    @endphp
    
    <div class="flex flex-wrap gap-2">
        <button
            wire:click="setCheckStatus('Open')"
            type="button"
            @if(!$isOpenAllowed) disabled @endif
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                {{ !$isOpenAllowed ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Open' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
            Aberto
        </button>
        <button
            wire:click="setCheckStatus('Closed')"
            type="button"
            @if(!$isClosedAllowed) disabled @endif
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                {{ !$isClosedAllowed ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Closed' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
            Fechado
        </button>
        <button
            wire:click="setCheckStatus('Paid')"
            type="button"
            @if(!$isPaidAllowed) disabled @endif
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                {{ !$isPaidAllowed ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Paid' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
            Pago
        </button>
        <button
            wire:click="setCheckStatus('Canceled')"
            type="button"
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
@endif
