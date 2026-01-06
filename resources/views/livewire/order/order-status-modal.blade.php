<div>
@if($show)
<div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeModal">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6" @click.stop>
        {{-- Title and Close button --}}
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900">Alterar Status</h3>
            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Status da Mesa --}}
        <div class="space-y-4 mb-6">
            @livewire('components.table-status-selector', [
                'table' => $selectedTable,
                'newTableStatus' => $newTableStatus,
                'hasActiveCheck' => $hasActiveCheck
            ], key('table-status-selector'))
        </div>

        {{-- Status do Check --}}
        <div class="space-y-4">
            @if($currentCheck)
            @php
            $pendingCount = $orders->where('status', 'pending')->count();
            $inProductionCount = $orders->where('status', 'in_production')->count();
            $inTransitCount = $orders->where('status', 'in_transit')->count();
            
            $hasIncompleteOrders = ($pendingCount > 0 || $inProductionCount > 0 || $inTransitCount > 0);
            $isOpenAllowed = in_array('Open', $checkStatusAllowed) || $newCheckStatus === 'Open';
            $isClosedAllowed = in_array('Closed', $checkStatusAllowed) || $newCheckStatus === 'Closed';
            $isPaidAllowed = in_array('Paid', $checkStatusAllowed) || $newCheckStatus === 'Paid';
            $isCanceledAllowed = in_array('Canceled', $checkStatusAllowed) || $newCheckStatus === 'Canceled';
            @endphp
            
            <label class="block text-sm font-semibold text-gray-700 mb-2">Status do Check</label>
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
            @endif
        </div>

        <div class="flex gap-3 mt-6">
            <button
                wire:click="closeModal"
                class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition">
                Cancelar
            </button>
            <button
                wire:click="updateStatuses"
                class="flex-1 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium transition">
                Salvar
            </button>
        </div>
    </div>
</div>
@endif
</div>
