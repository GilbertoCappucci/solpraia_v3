@props([
    'table',
    'delayAlarmEnabled' => true,
    'selectionMode' => false,
    'selectedTables' => []
])

@php
    $isSelected = $selectionMode && in_array($table->id, $selectedTables);

    // Condições para desabilitar a seleção (ex: mesas sem comanda ativa para unir)
    $isDisabled = $selectionMode && !$table->checkId;

    // Calcula quantidade de status ativos
    $activeStatuses = 0;
    if(isset($table->ordersPending) && $table->ordersPending > 0) $activeStatuses++;
    if(isset($table->ordersInProduction) && $table->ordersInProduction > 0) $activeStatuses++;
    if(isset($table->ordersInTransit) && $table->ordersInTransit > 0) $activeStatuses++;
    
    // Define classes dinâmicas
    $gridClass = match($activeStatuses) { 1 => 'grid-cols-1', 2 => 'grid-cols-2', 3 => 'grid-cols-3', default => 'grid-cols-1' };
    $dotSize = match($activeStatuses) { 1 => 'w-6 h-6', 2 => 'w-4 h-4', default => 'w-3 h-3' };
    $textSize = match($activeStatuses) { 1 => 'text-2xl', 2 => 'text-lg', default => 'text-sm' };
    $padding = match($activeStatuses) { 1 => 'py-4', 2 => 'py-3', default => 'py-2' };
    
    // Determina as classes de cor e estilo baseado no status
    $cardClasses = match(true) {
        $table->checkStatus === 'Open' => 'bg-white border-green-400 hover:border-green-500',
        $table->checkStatus === 'Closed' => 'bg-gradient-to-br from-orange-50 to-orange-100 border-orange-400 hover:border-orange-500',
        $table->checkStatus === 'Paid' => 'bg-gradient-to-br from-gray-50 to-gray-100 border-gray-400 hover:border-gray-500',
        $table->status === 'occupied' => 'bg-white border-green-400 hover:border-green-500',
        $table->status === 'reserved' => 'bg-gradient-to-br from-purple-50 to-purple-100 border-purple-400 hover:border-purple-500',
        $table->status === 'releasing' => 'bg-gradient-to-br from-teal-50 to-teal-100 border-teal-400 hover:border-teal-500',
        $table->status === 'close' => 'bg-gradient-to-br from-red-50 to-red-100 border-red-600 hover:border-red-700',
        default => 'bg-white border-gray-300 hover:border-gray-400'
    };
    
    $bottomBarBg = match(true) {
        $table->checkStatus === 'Open' => 'bg-white',
        $table->checkStatus === 'Closed' => 'bg-orange-100',
        $table->checkStatus === 'Paid' => 'bg-gray-100',
        $table->status === 'occupied' => 'bg-white',
        $table->status === 'reserved' => 'bg-purple-100',
        $table->status === 'releasing' => 'bg-teal-100',
        $table->status === 'close' => 'bg-red-100',
        default => 'bg-white'
    };

    // Indicadores visuais
    $showCenterLabel = $table->checkStatus === 'Paid' && $activeStatuses === 0;
    $showClosedIndicator = $table->checkStatus === 'Closed' && $activeStatuses === 0;
    $showReleasingIndicator = !$table->checkStatus && $table->status === 'releasing';
    
    // Atrasos
    $timeLimits = time_limits();
    $hasDelay = false;
    if (isset($table->pendingMinutes) && $table->pendingMinutes > $timeLimits['pending']) $hasDelay = true;
    if (isset($table->productionMinutes) && $table->productionMinutes > $timeLimits['in_production']) $hasDelay = true;
    if (isset($table->transitMinutes) && $table->transitMinutes > $timeLimits['in_transit']) $hasDelay = true;
    if (isset($table->closedMinutes) && $table->closedMinutes > $timeLimits['closed']) $hasDelay = true;
    if (isset($table->releasingMinutes) && $table->releasingMinutes > $timeLimits['releasing']) $hasDelay = true;
    
    $delayAnimation = ($hasDelay && $delayAlarmEnabled) ? 'animate-pulse-warning' : '';

    // Estilos de Seleção
    $selectionClasses = '';
    if ($selectionMode) {
        if ($isDisabled) {
            $selectionClasses = 'opacity-40 cursor-not-allowed grayscale';
        } else {
            $selectionClasses = 'cursor-pointer';
            if ($isSelected) {
                $selectionClasses .= ' ring-4 ring-offset-2 ring-blue-500';
            }
        }
    }
@endphp

<div 
    @if(!$isDisabled)
        wire:click="selectTable({{ $table->id }})"
    @endif
    {{ $attributes->merge(['class' => "relative aspect-square rounded-xl shadow-md hover:shadow-lg transition flex flex-col items-center justify-center border-2 {$cardClasses} {$delayAnimation} {$selectionClasses}"]) }}
>
    
    {{-- Indicador de Seleção (Checkbox) --}}
    @if($selectionMode && !$isDisabled)
        <div class="absolute top-2 right-2 w-6 h-6 border-2 {{ $isSelected ? 'bg-blue-500 border-white' : 'bg-white/50 border-gray-400' }} rounded-md flex items-center justify-center z-20 pointer-events-none">
            @if($isSelected)
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            @endif
        </div>
    @endif

    {{-- Badge topo esquerdo (Numero e Nome) --}}
    <div class="absolute top-2 left-2 right-2 flex items-baseline justify-between z-1 pointer-events-none">
        <span class="text-3xl font-bold text-gray-900 leading-none">{{ $table->number }}</span>
        <span class="text-xs text-gray-600 font-medium leading-none">{{ $table->name }}</span>
    </div>
             
    {{-- Indicadores de Status dos Pedidos ou Label Central --}}
    <div class="flex items-center justify-center flex-grow pointer-events-none z-1">
        @if($table->checkStatus && $activeStatuses > 0)
            <div class="grid {{ $gridClass }} gap-1 w-full px-2">
                <x-order-status-indicator status="pending" :count="$table->ordersPending ?? 0" :minutes="$table->pendingMinutes ?? 0" :dotSize="$dotSize" :textSize="$textSize" :padding="$padding" />
                <x-order-status-indicator status="production" :count="$table->ordersInProduction ?? 0" :minutes="$table->productionMinutes ?? 0" :dotSize="$dotSize" :textSize="$textSize" :padding="$padding" />
                <x-order-status-indicator status="transit" :count="$table->ordersInTransit ?? 0" :minutes="$table->transitMinutes ?? 0" :dotSize="$dotSize" :textSize="$textSize" :padding="$padding" />
            </div>
        @elseif($showClosedIndicator)
            <div class="flex flex-col items-center justify-center gap-1">
                <div class="w-6 h-6 bg-orange-500 rounded-full"></div>
                <span class="text-2xl font-bold text-orange-700">{{ $table->closedMinutes ?? 0 }}m</span>
            </div>
        @elseif($showReleasingIndicator)
            <div class="flex flex-col items-center justify-center gap-1">
                <div class="w-6 h-6 bg-teal-500 rounded-full"></div>
                <span class="text-2xl font-bold text-teal-700">{{ $table->releasingMinutes ?? 0 }}m</span>
            </div>
        @else
            <div class="text-xs font-medium italic {{ $showCenterLabel ? 'text-gray-600' : ($table->status === 'close' ? 'text-red-700 font-semibold' : ($table->checkStatusColor === 'green' ? 'text-green-600' : ($table->checkStatusColor === 'purple' ? 'text-purple-600' : 'text-gray-400'))) }}">
                {{ $table->checkStatusLabel }}
            </div>
        @endif
    </div>
    
    {{-- Barra Inferior: Valor do Check --}}
    @if(isset($table->checkTotal) && $table->checkTotal > 0)
        <div class="absolute bottom-0 left-0 right-0 flex items-center justify-center px-3 py-2 {{ $bottomBarBg }} z-1 rounded-b-xl pointer-events-none">
            <span class="text-xl font-bold text-orange-600">
                R$ {{ number_format($table->checkTotal, 2, ',', '.') }}
            </span>
        </div>
    @endif
</div>
