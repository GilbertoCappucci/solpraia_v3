@props([
    'table',
    'onStatusClick' => null
])

@php
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
    
    // Determina as classes de cor e estilo baseado no status
    $cardClasses = match(true) {
        $table->checkStatus === 'Open' => 'bg-white border-green-400 hover:border-green-500',
        $table->checkStatus === 'Closing' => 'bg-gradient-to-br from-yellow-50 to-orange-50 border-yellow-400 hover:border-yellow-500',
        $table->checkStatus === 'Closed' => 'bg-gradient-to-br from-orange-50 to-orange-100 border-orange-400 hover:border-orange-500',
        $table->checkStatus === 'Paid' => 'bg-gradient-to-br from-gray-50 to-gray-100 border-gray-400 hover:border-gray-500',
        $table->status === 'occupied' => 'bg-white border-green-400 hover:border-green-500',
        $table->status === 'reserved' => 'bg-gradient-to-br from-purple-50 to-purple-100 border-purple-400 hover:border-purple-500',
        $table->status === 'close' => 'bg-gradient-to-br from-red-50 to-red-100 border-red-600 hover:border-red-700',
        default => 'bg-white border-gray-300 hover:border-gray-400'
    };
    
    // Determina se deve mostrar label central (checks sem pedidos ativos)
    $showCenterLabel = in_array($table->checkStatus, ['Closing', 'Closed', 'Paid']) && $activeStatuses === 0;
    
    // Define cor do label central baseado no status do check
    $labelColor = match($table->checkStatus) {
        'Closing' => 'text-yellow-700',
        'Closed' => 'text-orange-700',
        'Paid' => 'text-gray-600',
        default => 'text-gray-400'
    };
@endphp

<div {{ $attributes->merge(['class' => "relative aspect-square rounded-xl shadow-md hover:shadow-lg transition flex flex-col items-center justify-center border-2 {$cardClasses}"]) }}>
    
    {{-- Área Principal Clicável - Vai para Orders --}}
    <a href="{{ route('orders', $table->id) }}" 
       class="absolute inset-0 z-0"
       wire:navigate
       title="Ver pedidos">
    </a>

    {{-- Badge topo esquerdo (Numero e Nome) --}}
    <div class="absolute top-2 left-2 right-2 flex items-baseline justify-between z-10 pointer-events-none">
        <span class="text-3xl font-bold text-gray-900 leading-none">{{ $table->number }}</span>
        <span class="text-xs text-gray-600 font-medium leading-none">{{ $table->name }}</span>
    </div>
             
    {{-- Indicadores de Status dos Pedidos ou Label Central --}}
    @if($table->checkStatus && $activeStatuses > 0)
        {{-- Grid Dinâmico com indicadores de pedidos --}}
        <div class="grid {{ $gridClass }} gap-1 w-full px-2 z-10 pointer-events-none">
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
        {{-- Label central (mesas sem check ou checks sem pedidos ativos) --}}
        <div class="text-xs font-medium italic z-10 pointer-events-none {{ $showCenterLabel ? $labelColor : ($table->status === 'close' ? 'text-red-700 font-semibold' : ($table->checkStatusColor === 'green' ? 'text-green-600' : ($table->checkStatusColor === 'purple' ? 'text-purple-600' : 'text-gray-400'))) }}">
            {{ $table->checkStatusLabel }}
        </div>
    @endif
    
    {{-- Barra Inferior: Reservada para interações específicas --}}
    @if(isset($table->checkTotal) && $table->checkTotal > 0)
        <div class="absolute bottom-0 left-0 right-0 flex items-center justify-between px-2 pb-2 z-20">
            {{-- Link para Check (Esquerda) --}}
            <a href="{{ route('check', $table->checkId) }}" 
               class="text-base font-bold text-orange-600 hover:text-orange-700 transition-colors pointer-events-auto"
               wire:navigate
               title="Ver comanda">
                R$ {{ number_format($table->checkTotal, 2, ',', '.') }}
            </a>
            
            {{-- Botão Alterar Status da Mesa (Direita) --}}
            <button 
                wire:click.stop="openTableStatusModal({{ $table->id }})"
                class="p-1.5 bg-white/90 hover:bg-white rounded-lg shadow-sm transition-all active:scale-95 pointer-events-auto"
                title="Alterar status da mesa">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
            </button>
        </div>
    @else
        {{-- Quando não há valor, mostrar apenas o botão de status --}}
        <div class="absolute bottom-2 right-2 z-20">
            <button 
                wire:click.stop="openTableStatusModal({{ $table->id }})"
                class="p-1.5 bg-white/90 hover:bg-white rounded-lg shadow-sm transition-all active:scale-95 pointer-events-auto"
                title="Alterar status da mesa">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
            </button>
        </div>
    @endif
</div>
