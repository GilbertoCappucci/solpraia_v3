<div>
{{-- Standalone Modal Mode --}}
@if(!$embedded && $show)
<div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeModal">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6" @click.stop>
        {{-- Header --}}
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900">Alterar Status do Check</h3>
            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        @include('livewire.check.check-status-content')

        {{-- Footer --}}
        <div class="flex gap-3 mt-6">
            <button
                wire:click="closeModal"
                class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition">
                Cancelar
            </button>
            <button
                wire:click="updateStatus"
                class="flex-1 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium transition">
                Salvar
            </button>
        </div>
    </div>
</div>
@endif

{{-- Embedded Mode --}}
@if($embedded && $check)
    <div class="space-y-4">
        <h4 class="text-lg font-semibold text-gray-900">Status do Check</h4>
        
        @include('livewire.check.check-status-content')
        
        <div class="flex justify-end">
            <button
                wire:click="updateStatus"
                class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium transition">
                Salvar Status do Check
            </button>
        </div>
    </div>
@endif
</div>
