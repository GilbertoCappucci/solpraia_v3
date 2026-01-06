<div>
    @if($activeMenuId)
    <div class="bg-white p-4 sticky top-14 z-30 shadow-sm">
        <input
            type="text"
            wire:model.live.debounce.300ms="searchTerm"
            placeholder="Buscar produtos..."
            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
    </div>
    @endif
</div>
