<div class="bg-gradient-to-r from-orange-500 to-red-500 text-white p-3 flex items-center justify-between sticky top-0 z-10 shadow-md">
    <x-flash-message-ui />
    {{-- Lado Esquerdo --}}
    <div class="flex items-center gap-2">
        {{-- Botão Voltar --}}
        <button
            wire:click="backToTables"
            class="p-1.5 hover:bg-white/20 rounded-lg transition"
            title="Voltar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </button>

        {{-- Número da Mesa --}}
        <span class="text-2xl font-bold">{{ $selectedTable->number }}</span>
    </div>

    {{-- Lado Direito --}}
    <div class="flex items-center gap-2">
        {{-- Filtro --}}
        <button
            wire:click="openFilterModal"
            class="flex items-center gap-1 px-3 py-1.5 border-2 rounded-lg text-sm font-medium transition
                {{ $statusFiltersCount < 5 ? 'border-white bg-white/20 text-white' : 'border-white/30 bg-white/10 text-white hover:bg-white/20' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            Filtros
            @if($statusFiltersCount < 5)
                <span class="bg-white text-orange-600 rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold">{{ $statusFiltersCount }}</span>
            @endif
        </button>

        {{-- Status Mesa/Check --}}
        @if($isActiveStatusButton)
        <button
            wire:click="openStatusModal"
            class="flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 border-2 border-white/30 text-white rounded-lg text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke-width="2"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/>
            </svg>
        </button>
        @endif

        {{-- Ações em Grupo --}}
        @if($isActiveGroupButton)
        <button
            wire:click="openGroupModal"
            class="flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 border-2 border-white/30 text-white rounded-lg text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20h-5v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </button>
        @endif  
    </div>
</div>
