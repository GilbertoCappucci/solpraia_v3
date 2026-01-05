<div
    wire:key="table-card-{{ $tableId }}-{{ md5(json_encode($this->statusTimestamps)) }}"
    x-data="{
        hasDelay: false,
        timeLimits: @js($timeLimits),
        timestamps: @js($this->statusTimestamps),
        checkDelay() {
            const now = Math.floor(Date.now() / 1000);
            let delay = false;
            
            if (this.timestamps.pending) {
                const pendingMinutes = Math.floor((now - new Date(this.timestamps.pending).getTime() / 1000) / 60);
                if (pendingMinutes > (this.timeLimits.pending || 0)) delay = true;
            }
            
            if (this.timestamps.production) {
                const productionMinutes = Math.floor((now - new Date(this.timestamps.production).getTime() / 1000) / 60);
                if (productionMinutes > (this.timeLimits.in_production || 0)) delay = true;
            }
            
            if (this.timestamps.transit) {
                const transitMinutes = Math.floor((now - new Date(this.timestamps.transit).getTime() / 1000) / 60);
                if (transitMinutes > (this.timeLimits.in_transit || 0)) delay = true;
            }
            
            if (this.timestamps.closed) {
                const closedMinutes = Math.floor((now - new Date(this.timestamps.closed).getTime() / 1000) / 60);
                if (closedMinutes > (this.timeLimits.closed || 0)) delay = true;
            }
            
            if (this.timestamps.releasing) {
                const releasingMinutes = Math.floor((now - new Date(this.timestamps.releasing).getTime() / 1000) / 60);
                if (releasingMinutes > (this.timeLimits.releasing || 0)) delay = true;
            }
            
            this.hasDelay = delay;
        }
    }"
    x-init="checkDelay(); setInterval(() => checkDelay(), 5000)"
    @if(!$this->isDisabled) wire:click="selectTable" @endif
    :class="hasDelay ? 'animate-pulse-warning' : ''"
    class="relative aspect-square rounded-xl shadow-md hover:shadow-lg transition flex flex-col items-center justify-center border-2 {{ $this->cardClasses }} {{ $this->selectionClasses }}">

    {{-- Indicador de Seleção (Checkbox) --}}
    @if($selectionMode && !$this->isDisabled)
    <div class="absolute top-2 right-2 w-6 h-6 border-2 {{ $this->isSelected ? 'bg-blue-500 border-white' : 'bg-white/50 border-gray-400' }} rounded-md flex items-center justify-center z-20 pointer-events-none">
        @if($this->isSelected)
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
        </svg>
        @endif
    </div>
    @endif

    {{-- Badge topo esquerdo (Numero e Nome) --}}
    <div class="absolute top-2 left-2 right-2 flex items-baseline justify-between z-1 pointer-events-none">
        <span class="text-3xl font-bold text-gray-900 leading-none">{{ $this->enrichedTable->number }}</span>
        <span class="text-xs text-gray-600 font-medium leading-none">{{ $this->enrichedTable->name }}</span>
    </div>

    {{-- Indicadores de Status dos Pedidos ou Label Central --}}
    <div class="flex items-center justify-center grow pointer-events-none z-1">
        @if($this->enrichedTable->checkStatus && $this->activeStatuses > 0)
        <div class="grid {{ $this->gridClass }} gap-1 w-full px-2">
            @if($this->enrichedTable->ordersPending > 0)
            <livewire:components.order-status-indicator 
                wire:key="pending-{{ $tableId }}-{{ $this->enrichedTable->pendingTimestamp }}"
                status="pending" 
                :count="$this->enrichedTable->ordersPending ?? 0" 
                :minutes="$this->enrichedTable->pendingMinutes ?? 0" 
                :timestamp="$this->enrichedTable->pendingTimestamp"
                :dotSize="$this->dotSize" 
                :textSize="$this->textSize" 
                :padding="$this->padding" 
            />
            @endif
            @if($this->enrichedTable->ordersInProduction > 0)
            <livewire:components.order-status-indicator 
                wire:key="production-{{ $tableId }}-{{ $this->enrichedTable->productionTimestamp }}"
                status="production" 
                :count="$this->enrichedTable->ordersInProduction ?? 0" 
                :minutes="$this->enrichedTable->productionMinutes ?? 0" 
                :timestamp="$this->enrichedTable->productionTimestamp"
                :dotSize="$this->dotSize" 
                :textSize="$this->textSize" 
                :padding="$this->padding" 
            />
            @endif
            @if($this->enrichedTable->ordersInTransit > 0)
            <livewire:components.order-status-indicator 
                wire:key="transit-{{ $tableId }}-{{ $this->enrichedTable->transitTimestamp }}"
                status="transit" 
                :count="$this->enrichedTable->ordersInTransit ?? 0" 
                :minutes="$this->enrichedTable->transitMinutes ?? 0" 
                :timestamp="$this->enrichedTable->transitTimestamp"
                :dotSize="$this->dotSize" 
                :textSize="$this->textSize" 
                :padding="$this->padding" 
            />
            @endif
        </div>
        @elseif($this->showClosedIndicator)
        <div 
            x-data="{
                minutes: {{ $this->enrichedTable->closedMinutes ?? 0 }},
                timestamp: @js($this->enrichedTable->closedTimestamp),
                updateMinutes() {
                    if (this.timestamp) {
                        const now = Math.floor(Date.now() / 1000);
                        const statusTime = Math.floor(new Date(this.timestamp).getTime() / 1000);
                        this.minutes = Math.floor((now - statusTime) / 60);
                    }
                }
            }"
            x-init="if (timestamp) { updateMinutes(); setInterval(() => updateMinutes(), 5000); }"
            class="flex flex-col items-center justify-center gap-1">
            <div class="w-6 h-6 bg-orange-500 rounded-full"></div>
            <div class="flex flex-col items-center leading-tight">
                <span class="text-2xl font-bold text-orange-700" x-text="minutes + 'm'">{{ $this->enrichedTable->closedMinutes ?? 0 }}m</span>
                <span class="text-[10px] font-bold text-orange-600 uppercase tracking-wider">Fechando</span>
            </div>
        </div>
        @elseif($this->showReleasingIndicator)
        <div 
            x-data="{
                minutes: {{ $this->enrichedTable->releasingMinutes ?? 0 }},
                timestamp: @js($this->enrichedTable->releasingTimestamp),
                updateMinutes() {
                    if (this.timestamp) {
                        const now = Math.floor(Date.now() / 1000);
                        const statusTime = Math.floor(new Date(this.timestamp).getTime() / 1000);
                        this.minutes = Math.floor((now - statusTime) / 60);
                    }
                }
            }"
            x-init="if (timestamp) { updateMinutes(); setInterval(() => updateMinutes(), 5000); }"
            class="flex flex-col items-center justify-center gap-1">
            <div class="w-6 h-6 bg-teal-500 rounded-full"></div>
            <div class="flex flex-col items-center leading-tight">
                <span class="text-2xl font-bold text-teal-700" x-text="minutes + 'm'">{{ $this->enrichedTable->releasingMinutes ?? 0 }}m</span>
                <span class="text-[10px] font-bold text-teal-600 uppercase tracking-wider">Liberando</span>
            </div>
        </div>
        @else
        <div class="text-xs font-medium italic {{ $this->showCenterLabel ? 'text-gray-600' : ($this->enrichedTable->status === 'close' ? 'text-red-700 font-semibold' : 'text-gray-400') }}">
            {{ $this->enrichedTable->checkStatusLabel ?? 'Livre' }}
        </div>
        @endif
    </div>

    {{-- Barra Inferior: Valor do Check --}}
    @if(isset($this->enrichedTable->checkTotal) && $this->enrichedTable->checkTotal > 0 && $this->enrichedTable->status !== 'releasing')
    <div class="absolute bottom-0 left-0 right-0 flex items-center justify-center px-3 py-2 {{ $this->bottomBarBg }} z-1 rounded-b-xl pointer-events-none">
        <span class="text-xl font-bold text-orange-600">
            R$ {{ number_format($this->enrichedTable->checkTotal, 2, ',', '.') }}
        </span>
    </div>
    @endif
</div>
