@props([
    'table'
])

@php
    // Determina as classes de cor e estilo baseado no status
    $cardClasses = match(true) {
        $table->checkStatus === 'Open' => 'bg-white border-green-400 hover:border-green-500',
        $table->checkStatus === 'Closing' => 'bg-gradient-to-br from-yellow-50 to-orange-50 border-yellow-400 hover:border-yellow-500',
        $table->checkStatus === 'Closed' => 'bg-gradient-to-br from-red-50 to-red-100 border-red-400 hover:border-red-500',
        $table->checkStatus === 'Paid' => 'bg-gradient-to-br from-gray-50 to-gray-100 border-gray-400 hover:border-gray-500',
        $table->status === 'occupied' => 'bg-white border-green-400 hover:border-green-500',
        $table->status === 'reserved' => 'bg-gradient-to-br from-purple-50 to-purple-100 border-purple-400 hover:border-purple-500',
        default => 'bg-white border-gray-300 hover:border-gray-400'
    };
    
    // Calcula quantidade de status ativos
    $activeStatuses = 0;
    if(isset($table->ordersPending) && $table->ordersPending > 0) $activeStatuses++;
    if(isset($table->ordersInProduction) && $table->ordersInProduction > 0) $activeStatuses++;
    if(isset($table->ordersInTransit) && $table->ordersInTransit > 0) $activeStatuses++;
    
    // Define classes dinâmicas baseado na quantidade de status ativos
    $gridClass = match($activeStatuses) {
        1 => 'grid-cols-1',
        2 => 'grid-cols-2',
        3 => 'grid-cols-3',
        default => 'grid-cols-1'
    };
    
    $dotSize = match($activeStatuses) {
        1 => 'w-6 h-6',
        2 => 'w-4 h-4',
        default => 'w-3 h-3'
    };
    
    $textSize = match($activeStatuses) {
        1 => 'text-2xl',
        2 => 'text-lg',
        default => 'text-sm'
    };
    
    $padding = match($activeStatuses) {
        1 => 'py-4',
        2 => 'py-3',
        default => 'py-2'
    };
    
    $spacing = match($activeStatuses) {
        1 => 'mb-2',
        2 => 'mb-1',
        default => 'mb-0.5'
    };
@endphp

<button {{ $attributes->merge(['class' => "relative aspect-square rounded-xl shadow-md hover:shadow-lg transition flex flex-col items-center justify-center border-2 {$cardClasses}"]) }}>
    
    {{-- Badge topo esquerdo (Numero e Nome) --}}
    <div class="absolute top-2 left-2 right-2 flex items-baseline justify-between">
        <span class="text-3xl font-bold text-gray-900 leading-none">{{ $table->number }}</span>
        <span class="text-xs text-gray-600 font-medium leading-none">{{ $table->name }}</span>
    </div>
             
    {{-- Indicadores de Status dos Pedidos - Grid Dinâmico --}}
    @if($table->checkStatus)
        <div class="grid {{ $gridClass }} gap-1 w-full px-2">
            <x-order-status-indicator 
                status="pending"
                :count="$table->ordersPending ?? 0"
                :minutes="$table->pendingMinutes ?? 0"
                :dotSize="$dotSize"
                :textSize="$textSize"
                :padding="$padding" />
            
            <x-order-status-indicator 
                status="production"
                :count="$table->ordersInProduction ?? 0"
                :minutes="$table->productionMinutes ?? 0"
                :dotSize="$dotSize"
                :textSize="$textSize"
                :padding="$padding" />
            
            <x-order-status-indicator 
                status="transit"
                :count="$table->ordersInTransit ?? 0"
                :minutes="$table->transitMinutes ?? 0"
                :dotSize="$dotSize"
                :textSize="$textSize"
                :padding="$padding" />
        </div>
    @else
        <div class="text-xs font-medium italic
            @if($table->checkStatusColor === 'green')
                text-green-600
            @elseif($table->checkStatusColor === 'purple')
                text-purple-600
            @else
                text-gray-400
            @endif">
            {{ $table->checkStatusLabel }}
        </div>
    @endif
    
    {{-- Badge Valor Total --}}
    @if(isset($table->checkTotal) && $table->checkTotal > 0)
        <div class="absolute bottom-2 left-2">
            <span class="text-base font-bold text-orange-600">R$ {{ number_format($table->checkTotal, 2, ',', '.') }}</span>
        </div>
    @endif
</button>
