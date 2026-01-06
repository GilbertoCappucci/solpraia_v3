<div>
    <x-flash-message />

    {{-- Header --}}
    <livewire:order.order-header 
        :selectedTable="$selectedTable" 
        :statusFiltersCount="count($statusFilters)"
        :userId="$userId" />

    {{-- Order List --}}
    <livewire:order.order-list 
        :listOrders="$listOrders" 
        :checkTotal="$checkTotal"
        :statusFilters="$statusFilters"
        :timeLimits="$timeLimits"
        :userId="$userId" />

    {{-- Footer --}}
    <livewire:order.order-footer 
        :selectedTable="$selectedTable"
        :currentCheck="$currentCheck"
        :checkTotal="$checkTotal"
        :tableId="$tableId"
        :userId="$userId" />

    {{-- Modals --}}
    <livewire:order.order-filters />
    
    {{-- Modal único que gerencia Table + Check internamente --}}
    <livewire:order.order-status-manager 
        :selectedTable="$selectedTable"
        :currentCheck="$currentCheck" />
    
    <livewire:order.order-cancel-modal />
    
    <livewire:order.order-details-modal 
        :currentCheck="$currentCheck" />
    
    <livewire:order.order-group-modal 
        :currentCheck="$currentCheck" />
    
    <livewire:order.order-group-actions-modal 
        :currentCheck="$currentCheck" />
</div>
