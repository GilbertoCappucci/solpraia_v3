<div>
@if($activeMenuId)
    {{-- Categorias Principais (Pai) --}}
    <div class="px-4 py-3 bg-white border-b sticky top-28 z-20 overflow-x-auto">
        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Categoria Principal</p>
        <div class="flex gap-2">
            <button
                wire:click="selectParentCategory(null)"
                class="px-4 py-2 rounded-full whitespace-nowrap text-sm font-medium transition shadow-sm
                        {{ !$selectedParentCategoryId && !$showFavoritesOnly ? 'bg-orange-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300' }}">
                Todas
            </button>
            <button
                wire:click="selectFavorites"
                class="px-4 py-2 rounded-full whitespace-nowrap text-sm font-medium transition shadow-sm flex items-center gap-1
                        {{ $showFavoritesOnly ? 'bg-yellow-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300' }}">
                <svg class="w-4 h-4" fill="{{ $showFavoritesOnly ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                Favoritos
            </button>
            @foreach($parentCategories as $category)
            <button
                wire:click="selectParentCategory({{ $category->id }})"
                class="px-4 py-2 rounded-full whitespace-nowrap text-sm font-medium transition shadow-sm
                            {{ $selectedParentCategoryId == $category->id ? 'bg-orange-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300' }}">
                {{ $category->name }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Categorias Secundárias (Filhas) --}}
    @if($selectedParentCategoryId && !$showFavoritesOnly && count($childCategories) > 0)
    <div class="px-4 py-3 bg-gray-50 border-b sticky top-48 z-20 overflow-x-auto">
        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Categoria Secundária</p>
        <div class="flex gap-2">
            <button
                wire:click="selectChildCategory(null)"
                class="px-4 py-2 rounded-full whitespace-nowrap text-sm font-medium transition shadow-sm
                            {{ !$selectedChildCategoryId ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300' }}">
                Todas
            </button>
            @foreach($childCategories as $category)
            <button
                wire:click="selectChildCategory({{ $category->id }})"
                class="px-4 py-2 rounded-full whitespace-nowrap text-sm font-medium transition shadow-sm
                                {{ $selectedChildCategoryId == $category->id ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300' }}">
                {{ $category->name }}
            </button>
            @endforeach
        </div>
    </div>
    @endif
@endif
</div>
