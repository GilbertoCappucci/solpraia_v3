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
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <h3 class="text-lg font-bold">Filtros de Pedidos</h3>
            </div>
            <div class="flex items-center gap-2">
                <button
                    wire:click="resetFilters"
                    class="px-3 py-1 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition">
                    Limpar
                </button>
                <button
                    wire:click="closeModal"
                    class="p-1 hover:bg-white/20 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div class="p-3 space-y-2">
        {{-- Status dos Pedidos --}}
        <div class="bg-gray-50 rounded-xl p-3 shadow-sm border-2 border-gray-300">
            <h4 class="font-bold text-gray-700 mb-2 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Status dos Pedidos
            </h4>
            <div class="flex flex-wrap gap-2">
                <button
                    wire:click="toggleStatusFilter('pending')"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition border-2
                        {{ in_array('pending', $statusFilters) ? 'bg-yellow-100 text-yellow-800 border-yellow-300' : 'bg-gray-100 text-gray-400 border-gray-200' }}">
                    Aguardando
                </button>
                <button
                    wire:click="toggleStatusFilter('in_production')"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition border-2
                        {{ in_array('in_production', $statusFilters) ? 'bg-blue-100 text-blue-800 border-blue-300' : 'bg-gray-100 text-gray-400 border-gray-200' }}">
                    Em Preparo
                </button>
                <button
                    wire:click="toggleStatusFilter('in_transit')"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition border-2
                        {{ in_array('in_transit', $statusFilters) ? 'bg-purple-100 text-purple-800 border-purple-300' : 'bg-gray-100 text-gray-400 border-gray-200' }}">
                    Em Trânsito
                </button>
                <button
                    wire:click="toggleStatusFilter('completed')"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition border-2
                        {{ in_array('completed', $statusFilters) ? 'bg-green-100 text-green-800 border-green-300' : 'bg-gray-100 text-gray-400 border-gray-200' }}">
                    Entregue
                </button>
                <button
                    wire:click="toggleStatusFilter('canceled')"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition border-2
                        {{ in_array('canceled', $statusFilters) ? 'bg-red-100 text-red-800 border-red-300' : 'bg-gray-100 text-gray-400 border-gray-200' }}">
                    Cancelado
                </button>
            </div>
        </div>
    </div>
</div>
@endif
</div>
