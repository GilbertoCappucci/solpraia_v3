@props([
    'check',
    'newCheckStatus',
    'pendingCount' => 0,
    'inProductionCount' => 0,
    'inTransitCount' => 0,
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
    
    {{-- Avisos baseados no contexto --}}
    @if($blockClosedButton)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-2">
            <p class="text-sm text-yellow-800">
                <span class="font-semibold">⚠️ Atenção:</span> Só é possível fechar o check quando todos os pedidos estiverem entregues.
            </p>
        </div>
    @elseif($check->status === 'Open')
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-2">
            <p class="text-sm text-blue-800">
                <span class="font-semibold">💡 Fluxo:</span> Open → Fechado → Pago
            </p>
        </div>
    @elseif($check->status === 'Closed')
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-2">
            <p class="text-sm text-blue-800">
                <span class="font-semibold">💡 Opções:</span> Marque como "Pago" para finalizar ou volte para "Aberto" para reabrir.
            </p>
        </div>
    @endif
    
    @if($canCancelCheck)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-2">
            <p class="text-sm text-blue-800">
                <span class="font-semibold">💡 Dica:</span> Este check está sem valor. Você pode cancelá-lo para liberar a mesa.
            </p>
        </div>
    @endif
    
    {{-- Botões de Status --}}
    <div class="flex flex-wrap gap-2">
        <button 
            wire:click="$set('newCheckStatus', 'Open')"
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                {{ $newCheckStatus === 'Open' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Aberto
        </button>
        <button 
            wire:click="$set('newCheckStatus', 'Closed')"
            @if($blockClosedButton) disabled @endif
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                {{ $blockClosedButton ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Closed' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
            Fechado
        </button>
        <button 
            wire:click="$set('newCheckStatus', 'Paid')"
            @if($blockPaidButton) disabled @endif
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                {{ $blockPaidButton ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Paid' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
            Pago
        </button>
        <button 
            wire:click="$set('newCheckStatus', 'Canceled')"
            @if(!$canCancelCheck) disabled @endif
            class="px-3 py-2 rounded-lg text-sm font-medium transition
                {{ !$canCancelCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === 'Canceled' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
            Cancelar
        </button>
    </div>
</div>
