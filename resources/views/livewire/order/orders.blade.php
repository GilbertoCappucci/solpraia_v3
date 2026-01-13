<div>
    {{-- Header --}}
    <livewire:order.order-header 
        :selectedTable="$selectedTable" 
        :statusFiltersCount="count($statusFilters)"
        :adminId="$adminId" />

    {{-- Order List --}}
    <livewire:order.order-list 
        :currentCheckId="$currentCheck->id"
        :listOrders="$listOrders" 
        :checkTotal="$checkTotal"
        :statusFilters="$statusFilters"
        :timeLimits="$timeLimits"
        :adminId="$adminId" />

    {{-- Footer --}}
    <livewire:order.order-footer 
        :selectedTable="$selectedTable"
        :currentCheck="$currentCheck"
        :checkTotal="$checkTotal"
        :tableId="$tableId"
        :adminId="$adminId" />

    {{-- Modals --}}
    <livewire:order.order-filters />
    
    <livewire:order.order-status-manager 
        :selectedTable="$selectedTable"
        :currentCheck="$currentCheck" />
    
    <livewire:order.order-cancel-modal />
    
    
    <livewire:order.order-group-modal 
        :currentCheck="$currentCheck" />
    
    <livewire:order.order-transfer-modal 
        :currentCheck="$currentCheck"
    />
</div>
