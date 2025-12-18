@props([
'check',
'newCheckStatus',
'pendingCount' => 0,
'inProductionCount' => 0,
'inTransitCount' => 0,
'checkStatusAllowed' => [],
])

@php
// Verifica se há pedidos não entregues (excluindo cancelados)
$hasIncompleteOrders = ($pendingCount > 0 ||
$inProductionCount > 0 ||
$inTransitCount > 0);

// Verifica se pode cancelar (total zero)
$canCancelCheck = $check->total == 0;

// Regras de bloqueio baseadas no status atual do check
// Open: pode ir para Fechado (se pedidos entregues) ou Cancelado
// Closed: pode ir para Paid ou voltar para Open
// Paid: livre navegação
$blockClosedButton = match($check->status) {
'Open' => $hasIncompleteOrders,
default => false
};
$blockPaidButton = ($check->status === 'Open'); // Bloqueado quando Open, liberado quando Closed
@endphp

<div>
    <label class="block text-sm font-semibold text-gray-700 mb-2">Status do Check</label>

    {{-- Botões de Status --}}
    <div class="flex flex-wrap gap-2">
        @php
        $isOpenAllowed = in_array('Open', $checkStatusAllowed) || $newCheckStatus === 'Open';
        $isClosedAllowed = in_array('Closed', $checkStatusAllowed) || $newCheckStatus === 'Closed';
        $isPaidAllowed = in_array('Paid', $checkStatusAllowed) || $newCheckStatus === 'Paid';
        $isCanceledAllowed = in_array('Canceled', $checkStatusAllowed) || $newCheckStatus === 'Canceled';
        @endphp

        <button
            wire:click="$set('newCheckStatus', 'Open')"
            @if(!$isOpenAllowed) disabled @endif
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                {{ !$isOpenAllowed ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Open' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
            Aberto
        </button>
        <button
            wire:click="$set('newCheckStatus', 'Closed')"
            @if(!$isClosedAllowed) disabled @endif
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                {{ !$isClosedAllowed ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Closed' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
            Fechado
        </button>
        <button
            wire:click="$set('newCheckStatus', 'Paid')"
            @if(!$isPaidAllowed) disabled @endif
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                {{ !$isPaidAllowed ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Paid' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
            Pago
        </button>
        <button
            wire:click="$set('newCheckStatus', 'Canceled')"
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