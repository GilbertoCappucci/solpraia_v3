<div wire:poll.{{ $pollingInterval }}ms>
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="bg-green-500 text-white px-4 py-3 text-center">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-500 text-white px-4 py-3 text-center">
            {{ session('error') }}
        </div>
    @endif

    <div class="p-4 relative">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold">Local</h2>
            
            <div class="flex items-center gap-2">
                {{-- Botão Filtros --}}
                <button 
                    wire:click="toggleFilters"
                    class="flex items-center gap-1 px-3 py-1.5 border-2 rounded-lg text-sm font-medium transition
                        {{ $filterCheckStatus || $filterOrderStatus ? 'border-orange-500 bg-orange-50 text-orange-700' : 'border-gray-300 hover:border-gray-400 text-gray-700' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filtros
                    @if($filterCheckStatus || $filterOrderStatus)
                        <span class="ml-1 px-1.5 py-0.5 bg-orange-500 text-white rounded-full text-xs">!</span>
                    @endif
                </button>
                
                {{-- Toggle Switch Tables Livres --}}
                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium text-gray-700 hidden sm:inline">Livres</span>
                    <button 
                        wire:click="toggleFreeTables"
                        type="button"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2
                            {{ $showFreeTables ? 'bg-orange-500' : 'bg-gray-200' }}"
                        role="switch"
                        aria-checked="{{ $showFreeTables ? 'true' : 'false' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                            {{ $showFreeTables ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>
                
                <button 
                    wire:click="openNewTableModal"
                    class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg text-sm font-medium hover:shadow-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Criar Novo
                </button>
            </div>
        </div>
        
        {{-- Dropdown de Filtros --}}
        @if($showFilters)
            <div class="absolute top-16 left-4 right-4 lg:left-4 lg:right-auto lg:w-80 bg-white rounded-lg shadow-xl border-2 border-gray-200 z-50 p-4">
                {{-- Filtro Status do Check --}}
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Status do Local</h3>
                    <div class="flex flex-wrap gap-2">
                        <button wire:click="setCheckStatusFilter('Open')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ $filterCheckStatus === 'Open' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Aberto
                        </button>
                        <button wire:click="setCheckStatusFilter('Closing')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ $filterCheckStatus === 'Closing' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Fechando
                        </button>
                        <button wire:click="setCheckStatusFilter('Closed')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ $filterCheckStatus === 'Closed' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Fechado
                        </button>
                        <button wire:click="setCheckStatusFilter('Paid')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ $filterCheckStatus === 'Paid' ? 'bg-gray-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Pago
                        </button>
                    </div>
                </div>
                
                {{-- Filtro Status dos Pedidos --}}
                <div class="mb-3">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Status dos Pedidos</h3>
                    <div class="flex flex-wrap gap-2">
                        <button wire:click="setOrderStatusFilter('pending')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ $filterOrderStatus === 'pending' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Aguardando
                        </button>
                        <button wire:click="setOrderStatusFilter('in_production')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ $filterOrderStatus === 'in_production' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Em Preparo
                        </button>
                        
                        {{-- Em transito --}}
                        <button wire:click="setOrderStatusFilter('in_transit')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ $filterOrderStatus === 'in_transit' ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Em Trânsito
                        </button>

                        <button wire:click="setOrderStatusFilter('completed')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ $filterOrderStatus === 'completed' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Pronto
                        </button>
                    </div>
                </div>
                
                {{-- Botões de Ação --}}
                <div class="flex gap-2 pt-3 border-t">
                    <button wire:click="clearFilters" class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                        Limpar
                    </button>
                    <button wire:click="toggleFilters" class="flex-1 px-3 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600 transition">
                        Aplicar
                    </button>
                </div>
            </div>
        @endif
        
        {{-- Grid de Locais - Responsivo --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
            @foreach($tables as $table)
                <button 
                    wire:click="selectTable({{ $table->id }})"
                    class="relative aspect-square bg-white rounded-xl shadow-md hover:shadow-lg transition flex flex-col items-center justify-center border-2
                        @if($table->checkStatusColor === 'green')
                            border-green-400 hover:border-green-500
                        @elseif($table->checkStatusColor === 'yellow')
                            border-yellow-400 hover:border-yellow-500
                        @elseif($table->checkStatusColor === 'red')
                            border-red-400 hover:border-red-500
                        @else
                            border-gray-300 hover:border-gray-400
                        @endif">
                    
                    {{-- Badge topo esquerdo (Numero e Nome) --}}
                    <div class="absolute top-2 left-2 right-2 flex items-baseline justify-between">
                        <span class="text-3xl font-bold text-gray-900 leading-none">{{ $table->number }}</span>
                        <span class="text-xs text-gray-600 font-medium leading-none">{{ $table->name }}</span>
                    </div>
                             
                    {{-- Indicadores de Status dos Pedidos - Grid Dinâmico --}}
                    @if($table->checkStatus)
                        @php
                            $activeStatuses = 0;
                            if($table->ordersPending > 0) $activeStatuses++;
                            if($table->ordersInProduction > 0) $activeStatuses++;
                            if($table->ordersInTransit > 0) $activeStatuses++;
                            
                            $gridClass = match($activeStatuses) {
                                1 => 'grid-cols-1',
                                2 => 'grid-cols-2',
                                3 => 'grid-cols-3',
                                default => 'grid-cols-1'
                            };
                            
                            // Tamanhos dinâmicos baseados na quantidade de status (otimizado para 2 colunas)
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
                        
                        <div class="grid {{ $gridClass }} gap-1 w-full px-2">
                            @if($table->ordersPending > 0)
                                <div class="flex flex-col items-center justify-center {{ $padding }} ">
                                    <span class="{{ $dotSize }} bg-yellow-500 rounded-full {{ $spacing }}" title="{{ $table->ordersPending }} aguardando"></span>
                                    <span class="{{ $textSize }} font-semibold text-yellow-700">{{ $table->pendingMinutes }}m</span>
                                </div>
                            @endif
                            @if($table->ordersInProduction > 0)
                                <div class="flex flex-col items-center justify-center {{ $padding }} ">
                                    <span class="{{ $dotSize }} bg-blue-500 rounded-full {{ $spacing }}" title="{{ $table->ordersInProduction }} em preparo"></span>
                                    <span class="{{ $textSize }} font-semibold text-blue-700">{{ $table->productionMinutes }}m</span>
                                </div>
                            @endif
                            @if($table->ordersInTransit > 0)
                                <div class="flex flex-col items-center justify-center {{ $padding }} ">
                                    <span class="{{ $dotSize }} bg-purple-500 rounded-full {{ $spacing }}" title="{{ $table->ordersInTransit }} em trânsito"></span>
                                    <span class="{{ $textSize }} font-semibold text-purple-700">{{ $table->transitMinutes }}m</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-xs text-gray-400 italic">Livre</div>
                    @endif
                    
                    {{-- Badge Valor Total --}}
                    @if($table->checkTotal > 0)
                        <div class="absolute bottom-2 left-2">
                            <span class="text-base font-bold text-orange-600">R$ {{ number_format($table->checkTotal, 2, ',', '.') }}</span>
                        </div>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- Modal Criar Novo Local --}}
    @if($showNewTableModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeNewTableModal">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6" wire:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Criar Novo Local</h3>
                    <button wire:click="closeNewTableModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                        <input 
                            type="number" 
                            wire:model="newTableNumber"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                            placeholder="Ex: 1">
                        @error('newTableNumber') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                        <input 
                            type="text" 
                            wire:model="newTableName"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                            placeholder="Ex: Varanda">
                        @error('newTableName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="flex gap-3 pt-2">
                        <button 
                            wire:click="closeNewTableModal"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                        <button 
                            wire:click="createNewTable"
                            class="flex-1 px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:shadow-lg transition">
                            Criar Local
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
