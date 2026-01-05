<div>
@if($showModal)
    {{-- Backdrop escuro --}}
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 flex items-center justify-center p-4" wire:click="closeModal"></div>
    
    {{-- Modal --}}
    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[95%] max-w-md bg-white rounded-2xl shadow-2xl border-2 border-gray-300 z-50">
        
        {{-- Header do Modal --}}
        <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-red-500 text-white px-4 py-3 rounded-t-2xl shadow-lg z-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <h3 class="text-lg font-bold">Criar Novo Local</h3>
                </div>
                <button 
                    type="button" 
                    wire:click="closeModal" 
                    class="p-1 hover:bg-white/20 rounded-lg transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        
        {{-- Conteúdo do Modal --}}
        <div class="p-4">
            <form wire:submit.prevent="createTable">
                <div class="space-y-4">
                    <div class="bg-gray-50 rounded-xl p-3 shadow-sm border-2 border-gray-300">
                        <label class="block text-sm font-bold text-gray-800 mb-2">Número *</label>
                        <input 
                            type="number" 
                            wire:model="newTableNumber"
                            class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
                            placeholder="Ex: 1"
                            autofocus>
                        @error('newTableNumber') 
                            <span class="text-red-600 text-xs font-medium mt-1 block">{{ $message }}</span> 
                        @enderror
                    </div>
                    
                    <div class="bg-gray-50 rounded-xl p-3 shadow-sm border-2 border-gray-300">
                        <label class="block text-sm font-bold text-gray-800 mb-2">Nome (opcional)</label>
                        <input 
                            type="text" 
                            wire:model="newTableName"
                            class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
                            placeholder="Ex: Varanda">
                        @error('newTableName') 
                            <span class="text-red-600 text-xs font-medium mt-1 block">{{ $message }}</span> 
                        @enderror
                    </div>
                    
                    <div class="flex gap-3 pt-2">
                        <button 
                            type="button"
                            wire:click="closeModal"
                            class="flex-1 px-4 py-2.5 bg-white border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition">
                            Cancelar
                        </button>
                        <button 
                            type="submit"
                            class="flex-1 px-4 py-2.5 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:shadow-lg font-medium transition transform hover:scale-105">
                            Criar Local
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif
</div>
