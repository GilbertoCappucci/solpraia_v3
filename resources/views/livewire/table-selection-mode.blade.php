<div>
    @if($showMergeModal)
        <livewire:merge-tables :selected-tables="$selectedTables" wire:key="merge-tables-selection" />
    @endif
</div>