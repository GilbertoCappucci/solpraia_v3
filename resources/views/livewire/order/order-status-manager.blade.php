<div>
@if($show)
<div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeModal">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 max-h-[90vh] overflow-y-auto" @click.stop>
        {{-- Header --}}
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900">Alterar Status</h3>
            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Table Status Selector --}}
        @if($selectedTable)
        <div class="mb-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Status da Mesa</h4>
            
            @if($hasActiveCheck)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-3">
                    <p class="text-sm text-yellow-800">
                        <span class="font-semibold">⚠️ Atenção:</span> Não é possível alterar o status da mesa enquanto houver um check em andamento.
                    </p>
                </div>
            @endif
            
            <div class="flex flex-wrap gap-2 mb-3">
                <button 
                    wire:click="setTableStatus('free')"
                    @if($hasActiveCheck) disabled @endif
                    class="px-3 py-2 rounded-lg text-sm font-medium transition
                        {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'free' ? 'bg-gray-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                    Livre
                </button>
                <button 
                    wire:click="setTableStatus('occupied')"
                    @if($hasActiveCheck) disabled @endif
                    class="px-3 py-2 rounded-lg text-sm font-medium transition
                        {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'occupied' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                    Ocupada
                </button>
                <button 
                    wire:click="setTableStatus('reserved')"
                    @if($hasActiveCheck) disabled @endif
                    class="px-3 py-2 rounded-lg text-sm font-medium transition
                        {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'reserved' ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                    Reservada
                </button>
                <button 
                    wire:click="setTableStatus('releasing')"
                    @if($hasActiveCheck) disabled @endif
                    class="px-3 py-2 rounded-lg text-sm font-medium transition
                        {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'releasing' ? 'bg-teal-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                    Liberando
                </button>
                <button 
                    wire:click="setTableStatus('closed')"
                    @if($hasActiveCheck) disabled @endif
                    class="px-3 py-2 rounded-lg text-sm font-medium transition
                        {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'closed' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                    Fechada
                </button>
            </div>
        </div>
        @endif

        {{-- Check Status Selector --}}
        @if($currentCheck)
        <div class="border-t pt-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-2">Status do Check</h4>
            
            <div class="mb-4">
                <p class="text-sm text-gray-600">Check #{{ $currentCheck->id }} - Mesa {{ $currentCheck->table->number ?? 'N/A' }}</p>
                <p class="text-lg font-semibold text-gray-900">Total: R$ {{ number_format($currentCheck->total, 2, ',', '.') }}</p>
            </div>

            @php
            $isOpenAllowed = in_array(\App\Enums\CheckStatusEnum::OPEN->value, $checkStatusAllowed) || $newCheckStatus === \App\Enums\CheckStatusEnum::OPEN->value;

            $isClosedAllowed = in_array(\App\Enums\CheckStatusEnum::CLOSED->value, $checkStatusAllowed) || $newCheckStatus === \App\Enums\CheckStatusEnum::CLOSED->value;

            $isPaidAllowed = in_array(\App\Enums\CheckStatusEnum::PAID->value, $checkStatusAllowed) || $newCheckStatus === \App\Enums\CheckStatusEnum::PAID->value;
            
            $isCanceledAllowed = in_array(\App\Enums\CheckStatusEnum::CANCELED->value, $checkStatusAllowed) || $newCheckStatus === \App\Enums\CheckStatusEnum::CANCELED->value;
            @endphp
            
            <div class="flex flex-wrap gap-2 mb-3">
                <button
                    wire:click="setCheckStatus('{{\App\Enums\CheckStatusEnum::OPEN->value}}')"
                    @if(!$isOpenAllowed) disabled @endif
                    class="px-3 py-2 rounded-lg text-sm font-medium transition
                        {{ !$isOpenAllowed ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === \App\Enums\CheckStatusEnum::OPEN->value ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                    Aberto
                </button>
                <button
                    wire:click="setCheckStatus('{{\App\Enums\CheckStatusEnum::CLOSED->value}}'  )"
                    @if(!$isClosedAllowed) disabled @endif
                    class="px-3 py-2 rounded-lg text-sm font-medium transition
                        {{ !$isClosedAllowed ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === \App\Enums\CheckStatusEnum::CLOSED->value ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                    Fechado
                </button>
                <button
                    wire:click="setCheckStatus('{{\App\Enums\CheckStatusEnum::PAID->value}}')"
                    @if(!$isPaidAllowed) disabled @endif
                    class="px-3 py-2 rounded-lg text-sm font-medium transition
                        {{ !$isPaidAllowed ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === \App\Enums\CheckStatusEnum::PAID->value ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                    Pago
                </button>
                <button
                    wire:click="setCheckStatus('{{\App\Enums\CheckStatusEnum::CANCELED->value}}')"
                    @if(!$isCanceledAllowed) disabled @endif
                    class="px-3 py-2 rounded-lg text-sm font-medium transition
                        {{ !$isCanceledAllowed ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newCheckStatus === \App\Enums\CheckStatusEnum::CANCELED->value ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                    Cancelar
                </button>
            </div>
            
            @if(count($checkStatusAllowed) === 0)
            <p class="mb-3 text-xs text-orange-600 font-medium">
                <i class="fas fa-info-circle mr-1"></i>
                Status do check bloqueado, verifique pedidos pendentes.
            </p>
            @endif
        </div>
        @endif

        {{-- Footer --}}
        <div class="flex justify-end mt-6 pt-4 border-t">
            <button
                wire:click="closeModal"
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition">
                Fechar
            </button>
        </div>
    </div>
</div>
@endif
</div>
