<div class="bg-gradient-to-r from-orange-500 to-red-500 text-white p-3 flex items-center justify-between sticky top-0 z-10 shadow-md">
    <div class="flex items-center gap-2">
        <button
            wire:click="backToOrders"
            class="p-1.5 hover:bg-white/20 rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <div class="flex items-baseline gap-2">
            <span class="text-2xl font-bold">{{ $selectedTable->number }}</span>
            <span class="text-sm opacity-90">{{ $selectedTable->name }}</span>
        </div>
        <div>
            <span class="text-sm opacity-90">{{ $title }}</span>
        </div>
    </div>
    @if($currentCheck && $currentCheck->total > 0)
    <div class="text-right">
        <p class="text-xl font-bold">R$ {{ number_format($currentCheck->total, 2, ',', '.') }}</p>
    </div>
    @endif
</div>
