<div>
{{-- Standalone Modal Mode --}}
@if(!$embedded && $showModal)
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
            @include('livewire.table.table-status-content')

            <div class="flex gap-3">
                <button 
                    wire:click="closeModal"
                    class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition">
                    Cancelar
                </button>
                <button 
                    wire:click="updateTableStatus"
                    class="flex-1 px-4 py-3 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white rounded-lg font-bold transition shadow-lg">
                    Salvar
                </button>
            </div>
        </div>
    </div>
    </div>
@endif

{{-- Embedded Mode --}}
@if($embedded && $selectedTableId)
    <div class="space-y-4">
        <h4 class="text-lg font-semibold text-gray-900">Status da Mesa</h4>
        
        @include('livewire.table.table-status-content')
        
        <div class="flex justify-end">
            <button 
                wire:click="updateTableStatus"
                class="px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white rounded-lg font-medium transition shadow-lg">
                Salvar Status da Mesa
            </button>
        </div>
    </div>
@endif
</div>
