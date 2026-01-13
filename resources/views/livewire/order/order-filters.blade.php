<div>
    {{-- Backdrop escuro quando filtros estão abertos --}}
    @if($show)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40" wire:click="closeModal"></div>
    @endif

    {{-- Modal de Filtros --}}
    @if($show)
        <div class="fixed top-2 left-1/2 -translate-x-1/2 w-[95%] max-w-2xl max-h-[calc(100vh-2.5rem)] overflow-y-auto bg-white rounded-2xl shadow-2xl border-2 border-gray-300 z-50">
        
        {{-- Header do Modal --}}
        <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-red-500 text-white px-4 py-3 rounded-t-2xl shadow-lg z-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <h3 class="text-lg font-bold">Filtros de Pedidos</h3>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="resetFilters" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Limpar
                    </button>
                    <button wire:click="closeModal" class="p-1 hover:bg-white/20 rounded-lg transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="p-3 space-y-2">
            {{-- Status dos Pedidos --}}
            <div class="bg-gray-50 rounded-xl p-3 shadow-sm border-2 border-gray-300">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                    <h3 class="text-sm font-bold text-gray-800">Status dos Pedidos</h3>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button wire:click="toggleStatusFilter('pending')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            {{ (empty($statusFilters) || in_array('pending', $statusFilters)) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                        Aguardando
                    </button>
                    <button wire:click="toggleStatusFilter('in_production')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            {{ (empty($statusFilters) || in_array('in_production', $statusFilters)) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                        Em Preparo
                    </button>
                    <button wire:click="toggleStatusFilter('in_transit')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            {{ (empty($statusFilters) || in_array('in_transit', $statusFilters)) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                        Em Trânsito
                    </button>
                    <button wire:click="toggleStatusFilter('completed')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            {{ (empty($statusFilters) || in_array('completed', $statusFilters)) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                        Entregue
                    </button>
                    <button wire:click="toggleStatusFilter('canceled')"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                            {{ (empty($statusFilters) || in_array('canceled', $statusFilters)) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                        Cancelado
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
