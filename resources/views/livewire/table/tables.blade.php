<div>
    <x-flash-message />

    {{-- Header Component --}}
    @livewire('table.table-header', [
        'selectionMode' => $selectionMode,
        'selectedTablesCount' => count($selectedTables),
        'canMerge' => $canMerge,
        'hasActiveFilters' => false,
        'title' => $title
    ])

    <div class="p-4 relative">
        
        {{-- Filters Component --}}
        @livewire('table.table-filters')
        
        {{-- Grid de Locais - Responsivo --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
            @foreach($tables as $table)
                @livewire('table.table-card', [
                    'tableId' => $table->id,
                    'selectionMode' => $selectionMode,
                    'selectedTables' => $selectedTables,
                    'timeLimits' => $timeLimits
                ], key('table-card-' . $table->id))
            @endforeach
        </div>
    </div>

    {{-- Modals --}}
    @livewire('table.table-create-modal')
    @livewire('table.table-status-modal')
    @livewire('table.table-merge-modal', ['selectedTables' => $selectedTables, 'tables' => $tables])
</div>
