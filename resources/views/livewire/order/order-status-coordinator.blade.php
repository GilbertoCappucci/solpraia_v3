<div>
@if($show)
<div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeModal">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 max-h-[90vh] overflow-y-auto" @click.stop>
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
        <div class="mb-6">
            <livewire:table.table-status-modal 
                :embedded="true"
                :table-id="$selectedTable?->id"
                wire:key="table-status-{{ $selectedTable?->id }}" />
        </div>

        {{-- Status do Check --}}
        @if($currentCheck)
        <div class="border-t pt-6">
            <livewire:check.check-status-modal 
                :embedded="true"
                :check-id="$currentCheck->id"
                wire:key="check-status-{{ $currentCheck->id }}" />
        </div>
        @endif

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
