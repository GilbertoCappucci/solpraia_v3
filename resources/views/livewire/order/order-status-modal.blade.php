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
        <div class="space-y-4 mb-6" wire:key="table-status-section">
            <livewire:components.table-status-selector
                :key="'table-status-selector'"
                :table="$selectedTable"
                :newTableStatus="$newTableStatus"
                :hasActiveCheck="$hasActiveCheck" />
        </div>

        {{-- Status do Check --}}
        <div class="space-y-4">
            @if($currentCheck)
            @php
            $pendingCount = $orders->where('status', 'pending')->count();
            $inProductionCount = $orders->where('status', 'in_production')->count();
            $inTransitCount = $orders->where('status', 'in_transit')->count();
            @endphp
            <x-check-status-selector
                :currentCheck="$currentCheck"
                :newCheckStatus="$newCheckStatus"
                :pendingCount="$pendingCount"
                :inProductionCount="$inProductionCount"
                :inTransitCount="$inTransitCount"
                :checkStatusAllowed="$checkStatusAllowed" />
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
