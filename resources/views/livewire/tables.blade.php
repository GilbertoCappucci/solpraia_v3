<div wire:poll.{{ $this->pollingInterval }}ms>
    <x-flash-message />

    {{-- Header com fundo laranja --}}
    <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white p-3 flex items-center justify-between sticky top-0 z-10 shadow-md">
        <div class="flex items-center gap-2">
            <h2 class="text-2xl font-bold">
                @if($selectionMode)
                    Selecionar Locais
                @else
                    Locais
                @endif
            </h2>
            
            {{-- Botão Toggle Alarme --}}
            @if(!$selectionMode)
                <button 
                    wire:click="toggleDelayAlarm"
                    class="flex items-center gap-1.5 px-2 py-1 border-2 rounded-lg text-sm font-medium transition
                        {{ $delayAlarmEnabled ? 'border-red-300 bg-red-500/20 text-white' : 'border-white/30 bg-white/10 text-white/60' }}"
                    title="{{ $delayAlarmEnabled ? 'Desativar' : 'Ativar' }} alarme de atraso">
                    @if($delayAlarmEnabled)
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                        </svg>
                    @else
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                            <path stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M4 4l12 12"/>
                        </svg>
                    @endif
                </button>
            @endif
        </div>
        
        <div class="flex items-center gap-2">
            @if($selectionMode)
                {{-- Ações do Modo de Seleção --}}
                <button 
                    wire:click="toggleSelectionMode"
                    class="flex items-center gap-2 px-3 py-1.5 bg-gray-600/50 hover:bg-gray-700/50 border-2 border-white/30 text-white rounded-lg text-sm font-medium transition">
                    Cancelar
                </button>
                <button 
                    wire:click="openMergeModal"
                    @if(count($selectedTables) < 2) disabled @endif
                    class="flex items-center gap-2 px-3 py-1.5 bg-white/20 hover:bg-white/30 border-2 border-white text-white rounded-lg text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Unir Selecionadas ({{ count($selectedTables) }})
                </button>
            @else
                {{-- Ações Padrão --}}
                <button 
                    wire:click="toggleSelectionMode"
                    @if(!$canMerge) disabled @endif
                    class="flex items-center gap-2 px-3 py-1.5 border-2 border-white/30 bg-white/10 text-white hover:bg-white/20 rounded-lg text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Unir Mesas
                </button>

                <button 
                    wire:click="toggleFilters"
                    class="flex items-center gap-1 px-3 py-1.5 border-2 rounded-lg text-sm font-medium transition
                        {{ !empty($filterTableStatuses) || !empty($filterCheckStatuses) || !empty($filterOrderStatuses) || !empty($filterDepartaments) ? 'border-white bg-white/20 text-white' : 'border-white/30 bg-white/10 text-white hover:bg-white/20' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filtros
                    @if(!empty($filterTableStatuses) || !empty($filterCheckStatuses) || !empty($filterOrderStatuses) || !empty($filterDepartaments))
                        <span class="ml-1 px-1.5 py-0.5 bg-white text-orange-600 rounded-full text-xs font-bold">!</span>
                    @endif
                </button>
                
                <button 
                    wire:click="openNewTableModal"
                    class="flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 border-2 border-white/30 text-white rounded-lg text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            @endif
        </div>
    </div>

    <div class="p-4 relative">
        
        {{-- Backdrop escuro quando filtros estão abertos --}}
        @if($showFilters)
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40" wire:click="toggleFilters"></div>
        @endif

        {{-- Modal de Filtros Redesenhado --}}
        @if($showFilters)
            <div class="fixed top-2 left-1/2 -translate-x-1/2 w-[95%] max-w-2xl max-h-[calc(100vh-2.5rem)] overflow-y-auto bg-white rounded-2xl shadow-2xl border-2 border-gray-300 z-50">
                
                {{-- Header do Modal --}}
                <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-red-500 text-white px-4 py-3 rounded-t-2xl shadow-lg z-10">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            <h3 class="text-lg font-bold">Filtros</h3>
                        </div>
                        <button wire:click="clearFilters" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Limpar
                        </button>
                        <button wire:click="toggleFilters" class="p-1 hover:bg-white/20 rounded-lg transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-3 space-y-2">
                    {{-- Modo de Filtragem --}}
                    <div class="bg-gray-50 rounded-xl p-3 shadow-sm border-2 border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                </svg>
                                <h3 class="text-sm font-bold text-gray-800">Modo de Filtragem</h3>
                            </div>
                            <button wire:click="toggleGlobalFilterMode" 
                                class="relative inline-flex h-8 w-16 items-center rounded-full transition-colors duration-200 focus:outline-none shadow-inner border-2
                                    {{ $globalFilterMode === 'AND' ? 'bg-gray-700 border-gray-800' : 'bg-gray-300 border-gray-400' }}">
                                <span class="inline-block h-6 w-6 transform rounded-full bg-white shadow-md transition-transform duration-200 
                                    {{ $globalFilterMode === 'AND' ? 'translate-x-9' : 'translate-x-1' }}">
                                </span>
                            </button>
                        </div>
                    </div>

                    {{-- Status da Mesa --}}
                    <div class="bg-gray-50 rounded-xl p-3 shadow-sm border-2 border-gray-300">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                            </svg>
                            <h3 class="text-sm font-bold text-gray-800">Status da Mesa</h3>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button wire:click="toggleTableStatusFilter('free')"
                                class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                    {{ in_array('free', $filterTableStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                                Livre
                            </button>
                            <button wire:click="toggleTableStatusFilter('occupied')"
                                class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                    {{ in_array('occupied', $filterTableStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                                Ocupada
                            </button>
                            <button wire:click="toggleTableStatusFilter('reserved')"
                                class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                    {{ in_array('reserved', $filterTableStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                                Reservada
                            </button>
                            <button wire:click="toggleTableStatusFilter('releasing')"
                                class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                    {{ in_array('releasing', $filterTableStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                                Liberando
                            </button>
                            <button wire:click="toggleTableStatusFilter('close')"
                                class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                    {{ in_array('close', $filterTableStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                                Fechada
                            </button>
                        </div>
                    </div>
                    
                    {{-- Status do Check --}}
                    <div class="bg-gray-100 rounded-xl p-3 shadow-sm border-2 border-gray-300">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <h3 class="text-sm font-bold text-gray-800">Status do Check</h3>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button wire:click="toggleCheckStatusFilter('Open')"
                                class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                    {{ in_array('Open', $filterCheckStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                                Aberto
                            </button>
                            <button wire:click="toggleCheckStatusFilter('Closed')"
                                class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                    {{ in_array('Closed', $filterCheckStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                                Fechado
                            </button>
                            <button wire:click="toggleCheckStatusFilter('Paid')"
                                class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                    {{ in_array('Paid', $filterCheckStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                                Pago
                            </button>
                            <button wire:click="toggleCheckStatusFilter('delayed_closed')"
                                class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                    {{ in_array('delayed_closed', $filterCheckStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                                Atrasado
                            </button>
                        </div>
                    </div>
                    
                    {{-- Status dos Pedidos --}}
                    <div class="bg-gray-50 rounded-xl p-3 shadow-sm border-2 border-gray-300">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                            </svg>
                            <h3 class="text-sm font-bold text-gray-800">Status dos Pedidos</h3>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button wire:click="toggleOrderStatusFilter('pending')"
                                class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                    {{ in_array('pending', $filterOrderStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                                Aguardando
                            </button>
                            <button wire:click="toggleOrderStatusFilter('in_production')"
                                class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                    {{ in_array('in_production', $filterOrderStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                                Em Preparo
                            </button>
                            <button wire:click="toggleOrderStatusFilter('in_transit')"
                                class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                    {{ in_array('in_transit', $filterOrderStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                                Em Trânsito
                            </button>
                            <button wire:click="toggleOrderStatusFilter('completed')"
                                class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                    {{ in_array('completed', $filterOrderStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                                Pronto
                            </button>
                            <button wire:click="toggleOrderStatusFilter('delayed')"
                                class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                    {{ in_array('delayed', $filterOrderStatuses) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                                Atrasado
                            </button>
                        </div>
                    </div>

                    {{-- Departamentos --}}
                    <div class="bg-gray-100 rounded-xl p-3 shadow-sm border-2 border-gray-300">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            <h3 class="text-sm font-bold text-gray-800">Departamentos</h3>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button wire:click="toggleDepartamentFilter('Administration')"
                                class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                    {{ in_array('Administration', $filterDepartaments) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                                Administração
                            </button>
                            <button wire:click="toggleDepartamentFilter('Expedition')"
                                class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                    {{ in_array('Expedition', $filterDepartaments) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                                Expedição
                            </button>
                            <button wire:click="toggleDepartamentFilter('Bar')"
                                class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                    {{ in_array('Bar', $filterDepartaments) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                                Bar
                            </button>
                            <button wire:click="toggleDepartamentFilter('Kitchen')"
                                class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                    {{ in_array('Kitchen', $filterDepartaments) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                                Cozinha
                            </button>
                            <button wire:click="toggleDepartamentFilter('Finance')"
                                class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                    {{ in_array('Finance', $filterDepartaments) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                                Financeiro
                            </button>
                            <button wire:click="toggleDepartamentFilter('Service')"
                                class="px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border-2 transform hover:scale-105
                                    {{ in_array('Service', $filterDepartaments) ? 'bg-gray-700 text-white border-gray-800 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500' }}">
                                Atendimento
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        {{-- Grid de Locais - Responsivo --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
            @foreach($tables as $table)
                <x-table-card 
                    :table="$table" 
                    :delayAlarmEnabled="$delayAlarmEnabled"
                    :selectionMode="$selectionMode"
                    :selectedTables="$selectedTables"
                />
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

    {{-- Modal Alterar Status da Mesa --}}
    @if($showTableStatusModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeTableStatusModal">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6" wire:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Alterar Status da Mesa</h3>
                    <button wire:click="closeTableStatusModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    {{-- Status da Mesa --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status da Mesa</label>
                        @if($hasActiveCheck)
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-2">
                                <p class="text-sm text-yellow-800">
                                    <span class="font-semibold">⚠️ Atenção:</span> Não é possível alterar o status da mesa enquanto houver um check em andamento. Para alterar o status da mesa, finalize ou cancele o check primeiro.
                                </p>
                            </div>
                        @endif
                        <div class="flex flex-wrap gap-2">
                            <button 
                                wire:click="$set('newTableStatus', 'free')"
                                @if($hasActiveCheck) disabled @endif
                                class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'free' ? 'bg-gray-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                                Livre
                            </button>
                            <button 
                                wire:click="$set('newTableStatus', 'occupied')"
                                @if($hasActiveCheck) disabled @endif
                                class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'occupied' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                                Ocupada
                            </button>
                            <button 
                                wire:click="$set('newTableStatus', 'reserved')"
                                @if($hasActiveCheck) disabled @endif
                                class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'reserved' ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                                Reservada
                            </button>
                            <button 
                                wire:click="$set('newTableStatus', 'releasing')"
                                @if($hasActiveCheck) disabled @endif
                                class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'releasing' ? 'bg-teal-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                                Liberando
                            </button>
                            <button 
                                wire:click="$set('newTableStatus', 'close')"
                                @if($hasActiveCheck) disabled @endif
                                class="px-3 py-2 rounded-lg text-sm font-medium transition
                                    {{ $hasActiveCheck ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ($newTableStatus === 'close' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }}">
                                Fechada
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            • <strong>Livre:</strong> Disponível para uso<br>
                            • <strong>Ocupada:</strong> Em uso por clientes<br>
                            • <strong>Reservada:</strong> Reservada para clientes<br>
                            • <strong>Liberando:</strong> Aguardando limpeza/preparação<br>
                            • <strong>Fechada:</strong> Não aceita novos pedidos
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button 
                            wire:click="closeTableStatusModal"
                            class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition">
                            Cancelar
                        </button>
                        <button 
                            wire:click="updateTableStatus"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white rounded-lg font-bold transition shadow-lg">
                            Salvar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Merge Tables Modal --}}
    @if($showMergeModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click="closeMergeModal">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6" wire:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Unir</h3>
                    <button wire:click="closeMergeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <p class="text-sm text-gray-600">
                        Selecione a mesa de <strong>destino</strong>. Todos os pedidos das outras mesas selecionadas serão movidos para ela.
                    </p>

                    <div class="space-y-2">
                        @foreach($tables->whereIn('id', $selectedTables) as $table)
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer transition {{ $mergeDestinationTableId == $table->id ? 'bg-orange-50 border-orange-500 ring-2 ring-orange-200' : 'border-gray-300 hover:border-orange-400' }}">
                                <input type="radio" wire:model="mergeDestinationTableId" value="{{ $table->id }}" class="h-4 w-4 text-orange-600 border-gray-300 focus:ring-orange-500">
                                <span class="ml-3 font-medium text-gray-800">
                                    Mesa {{ $table->number }}
                                    @if($table->name) ({{ $table->name }}) @endif
                                </span>
                                <span class="ml-auto text-sm text-gray-500">
                                    {{ $table->checkTotal > 0 ? 'R$ ' . number_format($table->checkTotal, 2, ',', '.') : 'Vazia' }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mt-4 rounded-r-lg">
                        <p class="text-xs text-yellow-800">
                            <strong>Atenção:</strong> Após a união, as mesas de origem serão liberadas e suas comandas atuais serão canceladas. Esta ação não pode ser desfeita.
                        </p>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button 
                            wire:click="closeMergeModal"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                        <button 
                            wire:click="mergeTables"
                            wire:loading.attr="disabled"
                            class="flex-1 px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:shadow-lg transition disabled:opacity-70">
                            Confirmar União
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
