<div wire:poll.{{ $pollingInterval }}s>
    <x-flash-message />

    {{-- Header --}}
    <livewire:table-header 
        :selection-mode="$selectionMode" 
        :selected-tables="$selectedTables" 
        :can-merge="$canMerge" 
        :has-active-filters="$hasActiveFilters"
        wire:key="table-header" 
    />

    <div class="p-4 relative">
        
        {{-- Filtros --}}
        <livewire:table-filters wire:key="table-filters" />
        
        {{-- Grid de Locais - Responsivo --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
            @foreach($tables as $table)
                <x-table-card 
                    :table="$table" 
                    :selectionMode="$selectionMode"
                    :selectedTables="$selectedTables"
                    :timeLimits="$timeLimits"
                />
            @endforeach
        </div>
    </div>

    {{-- Modais --}}
    <livewire:create-table-modal wire:key="create-table-modal" />
    <livewire:table-status-modal wire:key="table-status-modal" />
    
    {{-- Modal de União --}}
    @if($showMergeModal)
        <livewire:merge-tables :selected-tables="$selectedTables" wire:key="merge-tables-modal" />
    @endif
</div>
